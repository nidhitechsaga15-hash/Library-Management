<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Fine;
use App\Models\Borrow;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FineController extends Controller
{
    public function index(Request $request)
    {
        $query = Fine::with(['user', 'borrow.book']);
        
        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        $fines = $query->latest()->get();
        
        // Calculate statistics
        $totalPending = Fine::where('status', 'pending')->sum('amount');
        $totalPaid = Fine::where('status', 'paid')->sum('amount');
        $totalPartial = Fine::where('status', 'pending')
            ->where('paid_amount', '>', 0)
            ->sum(DB::raw('paid_amount'));
        $totalFines = Fine::sum('amount');
        $totalWaived = Fine::where('status', 'waived')->sum('amount');
        $totalAdjusted = Fine::where('status', 'adjusted')->sum('amount');
        
        // Get fine per day from settings
        $finePerDay = $this->getFinePerDay();
        
        // Recent payments (last 24 hours)
        $recentPayments = Payment::where('payment_type', 'fine')
            ->where('status', 'completed')
            ->where('created_at', '>=', now()->subDay())
            ->with(['user', 'paymentable'])
            ->latest()
            ->take(10)
            ->get();

        return view('admin.fines.index', compact(
            'fines', 
            'totalPending', 
            'totalPaid', 
            'totalPartial',
            'totalFines', 
            'totalWaived',
            'totalAdjusted',
            'finePerDay',
            'recentPayments'
        ));
    }

    public function show(Fine $fine)
    {
        $fine->load(['user', 'borrow.book']);
        
        // Get payment history for this fine
        $payments = Payment::where('paymentable_type', 'App\Models\Fine')
            ->where('paymentable_id', $fine->id)
            ->latest()
            ->get();
        
        return view('admin.fines.show', compact('fine', 'payments'));
    }

    public function updatePaymentStatus(Request $request, Fine $fine)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,paid,waived,adjusted',
            'paid_date' => 'nullable|date',
            'notes' => 'nullable|string|max:500',
        ]);

        if ($validated['status'] === 'paid' && !$validated['paid_date']) {
            $validated['paid_date'] = now();
        }

        if ($validated['status'] === 'pending') {
            $validated['paid_date'] = null;
        }

        // Add notes if provided
        if (isset($validated['notes']) && $validated['notes']) {
            $fine->payment_notes = ($fine->payment_notes ? $fine->payment_notes . "\n" : '') . 
                date('Y-m-d H:i:s') . ' (Admin): ' . $validated['notes'];
        }

        $fine->update($validated);

        return redirect()->route('admin.fines.show', $fine)
            ->with('success', 'Fine payment status updated successfully!');
    }

    /**
     * Manual override - Waive fine
     */
    public function waiveFine(Request $request, Fine $fine)
    {
        $validated = $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        $fine->update([
            'status' => 'waived',
            'paid_date' => now(),
            'payment_notes' => ($fine->payment_notes ? $fine->payment_notes . "\n" : '') . 
                date('Y-m-d H:i:s') . ' (Admin Waived): ' . $validated['reason'],
        ]);

        return redirect()->route('admin.fines.show', $fine)
            ->with('success', 'Fine waived successfully!');
    }

    /**
     * Manual override - Adjust fine amount
     */
    public function adjustFine(Request $request, Fine $fine)
    {
        $validated = $request->validate([
            'new_amount' => 'required|numeric|min:0',
            'reason' => 'required|string|max:500',
        ]);

        $oldAmount = $fine->amount;
        $fine->update([
            'amount' => $validated['new_amount'],
            'remaining_amount' => max(0, $validated['new_amount'] - ($fine->paid_amount ?? 0)),
            'payment_notes' => ($fine->payment_notes ? $fine->payment_notes . "\n" : '') . 
                date('Y-m-d H:i:s') . ' (Admin Adjusted): Amount changed from ₹' . number_format($oldAmount, 2) . 
                ' to ₹' . number_format($validated['new_amount'], 2) . '. Reason: ' . $validated['reason'],
        ]);

        // If new amount is less than or equal to paid amount, mark as paid
        if ($fine->paid_amount >= $validated['new_amount']) {
            $fine->update([
                'status' => 'paid',
                'paid_date' => now(),
            ]);
        }

        return redirect()->route('admin.fines.show', $fine)
            ->with('success', 'Fine amount adjusted successfully!');
    }

    /**
     * Payment logs - All fine payments
     */
    public function paymentLogs(Request $request)
    {
        $query = Payment::where('payment_type', 'fine')
            ->with(['user', 'paymentable'])
            ->latest();

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $payments = $query->paginate(20);

        // Statistics
        $totalPayments = Payment::where('payment_type', 'fine')->where('status', 'completed')->count();
        $totalAmount = Payment::where('payment_type', 'fine')->where('status', 'completed')->sum('amount');
        $todayPayments = Payment::where('payment_type', 'fine')
            ->where('status', 'completed')
            ->whereDate('created_at', today())
            ->count();
        $todayAmount = Payment::where('payment_type', 'fine')
            ->where('status', 'completed')
            ->whereDate('created_at', today())
            ->sum('amount');

        return view('admin.fines.payment-logs', compact(
            'payments',
            'totalPayments',
            'totalAmount',
            'todayPayments',
            'todayAmount'
        ));
    }

    /**
     * Live payment tracking - Get recent payments (AJAX)
     */
    public function livePayments(Request $request)
    {
        $since = $request->input('since', now()->subMinutes(5));
        
        $payments = Payment::where('payment_type', 'fine')
            ->where('status', 'completed')
            ->where('created_at', '>=', $since)
            ->with(['user', 'paymentable'])
            ->latest()
            ->get()
            ->map(function($payment) {
                return [
                    'id' => $payment->id,
                    'user_name' => $payment->user->name ?? 'N/A',
                    'user_email' => $payment->user->email ?? 'N/A',
                    'amount' => $payment->amount,
                    'payment_method' => $payment->payment_method,
                    'transaction_id' => $payment->payment_id ?? 'N/A',
                    'paid_at' => $payment->paid_at ? $payment->paid_at->format('d M Y, h:i A') : 'N/A',
                    'time_ago' => $payment->paid_at ? $payment->paid_at->diffForHumans() : 'N/A',
                ];
            });

        return response()->json([
            'success' => true,
            'payments' => $payments,
            'count' => $payments->count(),
        ]);
    }

    public function settings()
    {
        $finePerDay = $this->getFinePerDay();
        return view('admin.fines.settings', compact('finePerDay'));
    }

    public function updateSettings(Request $request)
    {
        $validated = $request->validate([
            'fine_per_day' => 'required|numeric|min:0',
        ]);

        DB::table('settings')
            ->updateOrInsert(
                ['key' => 'fine_per_day'],
                [
                    'value' => $validated['fine_per_day'],
                    'type' => 'number',
                    'description' => 'Fine amount per day for overdue books (in ₹)',
                    'updated_at' => now(),
                ]
            );

        return redirect()->route('admin.fines.settings')
            ->with('success', 'Fine settings updated successfully!');
    }

    public function recordPartialPayment(Request $request, Fine $fine)
    {
        $validated = $request->validate([
            'payment_amount' => 'required|numeric|min:0.01|max:' . ($fine->amount - ($fine->paid_amount ?? 0)),
            'payment_notes' => 'nullable|string|max:500',
        ]);

        $fine->recordPayment($validated['payment_amount'], $validated['payment_notes'] ?? 'Manual payment recorded by admin');

        return redirect()->route('admin.fines.show', $fine)
            ->with('success', 'Partial payment recorded successfully!');
    }

    private function getFinePerDay()
    {
        $setting = DB::table('settings')->where('key', 'fine_per_day')->first();
        return $setting ? (float) $setting->value : 10; // Default ₹10
    }
}
