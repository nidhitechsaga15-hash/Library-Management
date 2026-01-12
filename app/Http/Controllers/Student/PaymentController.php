<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Fine;
use App\Models\Payment;
use App\Models\EResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class PaymentController extends Controller
{
    /**
     * Show payment page for fines
     */
    public function payFine(Fine $fine)
    {
        $user = Auth::user();
        
        if ($fine->user_id !== $user->id) {
            return redirect()->route('student.fines.index')
                ->with('error', 'Unauthorized access.');
        }

        // Reload fine and borrow to get latest data
        $fine->refresh();
        $fine->load('borrow');
        
        if ($fine->borrow && $fine->borrow->isOverdue()) {
            // Recalculate remaining fine
            $fine->remaining_amount = $fine->calculateRemainingFine();
            $fine->amount = $fine->borrow->current_fine_amount;
        }

        if ($fine->isFullyPaid()) {
            return redirect()->route('student.fines.index')
                ->with('info', 'This fine is already paid.');
        }

        $remainingAmount = $fine->remaining_amount ?? $fine->amount;
        
        // Calculate fine per day and pending days
        $borrow = $fine->borrow;
        $finePerDay = $borrow ? ($borrow->fine_per_day ?? 10) : 10;
        $pendingDays = $borrow ? $borrow->pending_fine_days : 0;
        $lastPaidDate = $borrow ? $borrow->last_fine_paid_date : null;
        $totalPaid = $borrow ? ($borrow->total_fine_paid ?? 0) : 0;

        return view('student.payments.fine', compact('fine', 'remainingAmount', 'finePerDay', 'pendingDays', 'lastPaidDate', 'totalPaid'));
    }

    /**
     * Create payment order
     */
    public function createOrder(Request $request)
    {
        $validated = $request->validate([
            'payment_type' => 'required|in:fine,membership,e_resource',
            'paymentable_type' => 'required|string',
            'paymentable_id' => 'required|integer',
            'amount' => 'required|numeric|min:1',
        ]);

        $user = Auth::user();
        $amount = $validated['amount'];

        // Generate unique order ID
        $orderId = 'ORD_' . strtoupper(Str::random(10)) . '_' . time();

        // Create payment record
        $payment = Payment::create([
            'user_id' => $user->id,
            'payment_type' => $validated['payment_type'],
            'paymentable_type' => $validated['paymentable_type'],
            'paymentable_id' => $validated['paymentable_id'],
            'amount' => $amount,
            'currency' => 'INR',
            'payment_method' => 'razorpay',
            'order_id' => $orderId,
            'status' => 'pending',
        ]);

        // For Razorpay integration (you'll need to install razorpay/razorpay package)
        // For now, we'll return the order details for frontend integration
        $razorpayKey = config('services.razorpay.key', env('RAZORPAY_KEY'));
        
        return response()->json([
            'success' => true,
            'order_id' => $orderId,
            'payment_id' => $payment->id,
            'amount' => $amount * 100, // Convert to paise
            'currency' => 'INR',
            'key' => $razorpayKey,
            'name' => config('app.name', 'Library Management'),
            'description' => $this->getPaymentDescription($validated['payment_type'], $validated['paymentable_id']),
            'prefill' => [
                'name' => $user->name,
                'email' => $user->email,
                'contact' => $user->phone ?? '',
            ],
        ]);
    }

    /**
     * Handle payment success callback
     */
    public function success(Request $request)
    {
        $validated = $request->validate([
            'payment_id' => 'required|string',
            'order_id' => 'required|string',
            'razorpay_payment_id' => 'required|string',
            'razorpay_signature' => 'required|string',
        ]);

        $payment = Payment::where('order_id', $validated['order_id'])->first();

        if (!$payment) {
            return redirect()->route('student.payments.index')
                ->with('error', 'Payment not found.');
        }

        // Verify signature (implement Razorpay signature verification)
        // For now, we'll mark as completed
        $payment->update([
            'payment_id' => $validated['razorpay_payment_id'],
            'status' => 'completed',
            'paid_at' => now(),
            'gateway_response' => $request->all(),
        ]);

        // Update the related model (Fine, Membership, etc.)
        $this->updatePaymentable($payment);

        // If AJAX request, return JSON
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Payment completed successfully!',
                'payment_id' => $payment->id,
            ]);
        }

        return redirect()->route('student.payments.index')
            ->with('success', 'Payment completed successfully!');
    }

    /**
     * Handle payment failure
     */
    public function failure(Request $request)
    {
        $orderId = $request->input('order_id');
        
        if ($orderId) {
            $payment = Payment::where('order_id', $orderId)->first();
            if ($payment) {
                $payment->update([
                    'status' => 'failed',
                    'failure_reason' => $request->input('error_description', 'Payment failed'),
                    'gateway_response' => $request->all(),
                ]);
            }
        }

        return redirect()->route('student.payments.index')
            ->with('error', 'Payment failed. Please try again.');
    }

    /**
     * List user payments
     */
    public function index()
    {
        $user = Auth::user();
        $payments = Payment::where('user_id', $user->id)
            ->latest()
            ->paginate(15);

        return view('student.payments.index', compact('payments'));
    }

    /**
     * Show payment details
     */
    public function show(Payment $payment)
    {
        $user = Auth::user();
        
        if ($payment->user_id !== $user->id) {
            return redirect()->route('student.payments.index')
                ->with('error', 'Unauthorized access.');
        }

        return view('student.payments.show', compact('payment'));
    }

    /**
     * Get payment description
     */
    private function getPaymentDescription($type, $id)
    {
        switch ($type) {
            case 'fine':
                $fine = Fine::find($id);
                return $fine ? "Fine Payment - ₹{$fine->amount}" : 'Fine Payment';
            case 'membership':
                return 'Library Membership Renewal';
            case 'e_resource':
                $resource = EResource::find($id);
                return $resource ? "E-Resource: {$resource->title}" : 'E-Resource Access';
            default:
                return 'Library Payment';
        }
    }

    /**
     * Update paymentable model after successful payment
     */
    private function updatePaymentable(Payment $payment)
    {
        if ($payment->payment_type === 'fine' && $payment->paymentable_type === 'App\\Models\\Fine') {
            $fine = Fine::find($payment->paymentable_id);
            if ($fine) {
                $fine->load('borrow');
                
                // Record the payment (this will recalculate remaining fine)
                $fine->recordPayment($payment->amount, 'Online payment via Razorpay - ' . ($payment->payment_id ?? 'QR Payment'));
                
                // Reload fine and borrow to get updated status
                $fine->refresh();
                $fine->load('borrow');
                $fine->borrow->refresh();
                
                // Fine amount and remaining amount are already updated in recordPayment()
                // Just ensure status is correct
                if ($fine->borrow && $fine->borrow->isOverdue() && $fine->status !== 'paid') {
                    // Recalculate fine based on last_fine_paid_date
                    $pendingFineAmount = $fine->borrow->current_fine_amount;
                    $pendingDays = $fine->borrow->pending_fine_days;
                    
                    $fine->amount = $pendingFineAmount;
                    $fine->reason = 'Overdue book - ' . $pendingDays . ' day(s) pending from ' . 
                        ($fine->borrow->last_fine_paid_date ? $fine->borrow->last_fine_paid_date->format('Y-m-d') : $fine->borrow->due_date->format('Y-m-d'));
                    $fine->remaining_amount = $fine->calculateRemainingFine();
                    
                    if ($fine->isFullyPaid()) {
                        $fine->status = 'paid';
                        if (!$fine->paid_date) {
                            $fine->paid_date = now();
                        }
                    }
                    
                    $fine->save();
                }
            }
        }
        // Add other payment types as needed
    }
}
