<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Fine;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class FineController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        
        // Get all existing fines
        $fines = Fine::where('user_id', $user->id)
            ->with(['borrow.book'])
            ->latest()
            ->get();
        
        // Recalculate fines for overdue books
        foreach ($fines as $fine) {
            if ($fine->borrow && $fine->borrow->isOverdue() && $fine->status !== 'paid') {
                $this->recalculateFine($fine);
            }
        }
        
        // Check for overdue books without fine records and create them
        $overdueBorrows = \App\Models\Borrow::where('user_id', $user->id)
            ->where('status', 'borrowed')
            ->where('due_date', '<', now())
            ->whereDoesntHave('fine')
            ->with('book')
            ->get();
        
        foreach ($overdueBorrows as $borrow) {
            // Calculate pending fine (from last_fine_paid_date or due_date)
            $pendingFineAmount = $borrow->current_fine_amount;
            $pendingDays = $borrow->pending_fine_days;
            
            if ($pendingFineAmount > 0) {
                $fine = Fine::create([
                    'borrow_id' => $borrow->id,
                    'user_id' => $user->id,
                    'amount' => $pendingFineAmount,
                    'reason' => 'Overdue book - ' . $pendingDays . ' day(s) pending from ' . 
                        ($borrow->last_fine_paid_date ? $borrow->last_fine_paid_date->format('Y-m-d') : $borrow->due_date->format('Y-m-d')),
                    'status' => 'pending',
                    'days_overdue_at_creation' => $borrow->days_overdue,
                ]);
                $fines->push($fine);
            }
        }
        
        // Reload fines with relationships
        $fines = Fine::where('user_id', $user->id)
            ->with(['borrow.book'])
            ->latest()
            ->get();
        
        // Calculate total pending (using remaining amounts for overdue books)
        $totalPending = 0;
        foreach ($fines as $fine) {
            if ($fine->status !== 'paid') {
                $totalPending += $fine->remaining_amount ?? $fine->amount;
            }
        }
        
        $totalPaid = Fine::where('user_id', $user->id)
            ->where('status', 'paid')
            ->sum('paid_amount');

        return view('student.fines.index', compact('fines', 'totalPending', 'totalPaid'));
    }
    
    /**
     * Recalculate fine based on last_fine_paid_date (only unpaid days)
     */
    private function recalculateFine(Fine $fine)
    {
        if (!$fine->borrow || !$fine->borrow->isOverdue()) {
            return;
        }
        
        // Use Borrow model's current_fine_amount which calculates from last_fine_paid_date
        $pendingFineAmount = $fine->borrow->current_fine_amount;
        $pendingDays = $fine->borrow->pending_fine_days;
        
        // Update fine amount and reason
        $fine->amount = $pendingFineAmount;
        $fine->reason = 'Overdue book - ' . $pendingDays . ' day(s) pending from ' . 
            ($fine->borrow->last_fine_paid_date ? $fine->borrow->last_fine_paid_date->format('Y-m-d') : $fine->borrow->due_date->format('Y-m-d'));
        
        // Recalculate remaining amount
        $fine->remaining_amount = $fine->calculateRemainingFine();
        
        // If fully paid, mark as paid
        if ($fine->isFullyPaid()) {
            $fine->status = 'paid';
            if (!$fine->paid_date) {
                $fine->paid_date = now();
            }
        } else {
            $fine->status = 'pending';
        }
        
        $fine->save();
    }

    /**
     * Generate QR code for fine payment
     */
    public function generateQR(Fine $fine)
    {
        $user = auth()->user();
        
        if ($fine->user_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Reload fine and recalculate if needed
        $fine->refresh();
        $fine->load('borrow');
        
        if ($fine->borrow && $fine->borrow->isOverdue() && $fine->status !== 'paid') {
            $this->recalculateFine($fine);
            $fine->refresh();
        }

        if ($fine->status === 'paid') {
            return response()->json(['error' => 'Fine already paid'], 400);
        }

        $remainingAmount = $fine->remaining_amount ?? $fine->amount;
        $paymentUrl = route('student.fines.pay', $fine);
        
        // Generate QR code as SVG (with dummy UPI data for testing)
        // Format: upi://pay?pa=merchant@upi&pn=Library%20Management&am=20.00&cu=INR&tn=Fine%20Payment
        $upiString = "upi://pay?pa=library@paytm&pn=Library%20Management&am=" . number_format($remainingAmount, 2) . "&cu=INR&tn=Fine%20Payment%20-%20" . $fine->id;
        
        // Generate QR code
        $qrCode = QrCode::format('svg')
            ->size(250)
            ->generate($upiString);

        return response()->json([
            'success' => true,
            'qr_code' => $qrCode,
            'payment_url' => $paymentUrl,
            'amount' => $remainingAmount,
            'upi_string' => $upiString, // For testing
        ]);
    }

    /**
     * Check payment status
     */
    public function checkPaymentStatus(Fine $fine)
    {
        $user = auth()->user();
        
        if ($fine->user_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Reload fine to get latest status
        $fine->refresh();
        $fine->load('borrow');
        
        // Recalculate fine if book is still overdue
        if ($fine->borrow && $fine->borrow->isOverdue() && $fine->status !== 'paid') {
            $this->recalculateFine($fine);
            $fine->refresh();
        }
        
        // Check if payment was completed
        $payment = Payment::where('paymentable_type', 'App\Models\Fine')
            ->where('paymentable_id', $fine->id)
            ->where('status', 'completed')
            ->latest()
            ->first();

        return response()->json([
            'status' => $fine->status,
            'is_paid' => $fine->status === 'paid',
            'remaining_amount' => $fine->remaining_amount ?? $fine->amount,
            'payment_id' => $payment ? $payment->id : null,
        ]);
    }

    /**
     * Simulate payment (for testing/dummy payment)
     */
    public function simulatePayment(Fine $fine)
    {
        $user = auth()->user();
        
        if ($fine->user_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Reload fine and recalculate if needed
        $fine->refresh();
        $fine->load('borrow');
        
        if ($fine->borrow && $fine->borrow->isOverdue() && $fine->status !== 'paid') {
            $this->recalculateFine($fine);
            $fine->refresh();
        }

        if ($fine->status === 'paid') {
            return response()->json(['error' => 'Fine already paid'], 400);
        }

        // Get remaining amount to pay
        $remainingAmount = $fine->remaining_amount ?? $fine->amount;
        
        // For testing, pay one day's fine at a time
        $finePerDay = $fine->borrow->fine_per_day ?? \App\Helpers\FineHelper::getFinePerDayByDuration($fine->borrow->issue_duration_days ?? 15);
        $paymentAmount = min($remainingAmount, $finePerDay); // Pay for one day or remaining amount, whichever is less
        
        // Reload borrow to get latest data
        $fine->borrow->refresh();

        // Create a dummy payment record
        $payment = Payment::create([
            'user_id' => $user->id,
            'payment_type' => 'fine',
            'paymentable_type' => 'App\Models\Fine',
            'paymentable_id' => $fine->id,
            'amount' => $paymentAmount,
            'currency' => 'INR',
            'payment_method' => 'qr_code',
            'payment_id' => 'DUMMY_' . strtoupper(Str::random(10)),
            'order_id' => 'ORD_' . strtoupper(Str::random(10)) . '_' . time(),
            'status' => 'completed',
            'paid_at' => now(),
            'gateway_response' => ['method' => 'dummy', 'test' => true],
        ]);

        // Record payment (this will recalculate remaining fine)
        $fine->recordPayment($paymentAmount, 'Test payment - ' . date('Y-m-d H:i:s'));
        
        // Reload and recalculate if still overdue
        $fine->refresh();
        $fine->load('borrow');
        
        if ($fine->borrow && $fine->borrow->isOverdue() && $fine->status !== 'paid') {
            $this->recalculateFine($fine);
            $fine->refresh();
        }

        return response()->json([
            'success' => true,
            'message' => 'Payment simulated successfully. ' . ($fine->status === 'paid' ? 'Fine fully paid!' : 'Remaining fine: ₹' . number_format($fine->remaining_amount, 2)),
            'payment_id' => $payment->id,
            'fine_status' => $fine->status,
            'remaining_amount' => $fine->remaining_amount ?? 0,
        ]);
    }

    /**
     * Download payment receipt
     */
    public function downloadReceipt(Fine $fine)
    {
        $user = auth()->user();
        
        if ($fine->user_id !== $user->id) {
            return redirect()->route('student.fines.index')
                ->with('error', 'Unauthorized access.');
        }

        if ($fine->status !== 'paid') {
            return redirect()->route('student.fines.index')
                ->with('error', 'Receipt available only for paid fines.');
        }

        $payment = Payment::where('paymentable_type', 'App\Models\Fine')
            ->where('paymentable_id', $fine->id)
            ->where('status', 'completed')
            ->latest()
            ->first();

        return view('student.fines.receipt', compact('fine', 'payment', 'user'));
    }
}
