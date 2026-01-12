<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Fine;
use App\Models\Payment;
use Illuminate\Http\Request;

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
        
        $totalPending = Fine::where('status', 'pending')->sum('amount');
        $totalPaid = Fine::where('status', 'paid')->sum('amount');
        $totalPartial = Fine::where('status', 'pending')
            ->where('paid_amount', '>', 0)
            ->sum('paid_amount');
        
        // Failed payments count
        $failedPayments = Payment::where('payment_type', 'fine')
            ->where('status', 'failed')
            ->count();

        return view('staff.fines.index', compact('fines', 'totalPending', 'totalPaid', 'totalPartial', 'failedPayments'));
    }

    public function show(Fine $fine)
    {
        $fine->load(['user', 'borrow.book']);
        
        // Get payment history for this fine
        $payments = Payment::where('paymentable_type', 'App\Models\Fine')
            ->where('paymentable_id', $fine->id)
            ->latest()
            ->get();
        
        // Get failed payments for this fine
        $failedPayments = $payments->where('status', 'failed');
        
        return view('staff.fines.show', compact('fine', 'payments', 'failedPayments'));
    }

    /**
     * Verify payment - View payment details (read-only)
     */
    public function verifyPayment(Fine $fine)
    {
        $fine->load(['user', 'borrow.book']);
        
        // Get all payments for this fine
        $payments = Payment::where('paymentable_type', 'App\Models\Fine')
            ->where('paymentable_id', $fine->id)
            ->latest()
            ->get();
        
        return view('staff.fines.verify-payment', compact('fine', 'payments'));
    }

    /**
     * View failed payments for assistance
     */
    public function failedPayments()
    {
        $failedPayments = Payment::where('payment_type', 'fine')
            ->where('status', 'failed')
            ->with(['user', 'paymentable'])
            ->latest()
            ->get();
        
        return view('staff.fines.failed-payments', compact('failedPayments'));
    }

    /**
     * Assist student with failed payment
     */
    public function assistStudent(Request $request, Payment $payment)
    {
        // Staff can only view and provide assistance, not modify payments
        $payment->load(['user']);
        
        // Load fine relationship if payment is for a fine
        if ($payment->paymentable_type === 'App\Models\Fine' && $payment->paymentable_id) {
            $payment->load(['paymentable.borrow.book']);
        }
        
        return view('staff.fines.assist-student', compact('payment'));
    }

    /**
     * Update payment status (limited - only verify paid status)
     */
    public function updatePaymentStatus(Request $request, Fine $fine)
    {
        // Staff can only verify/update status to paid if payment exists
        $validated = $request->validate([
            'status' => 'required|in:pending,paid',
            'paid_date' => 'nullable|date',
            'verification_notes' => 'nullable|string|max:500',
        ]);

        // Check if there's a completed payment for this fine
        $hasPayment = Payment::where('paymentable_type', 'App\Models\Fine')
            ->where('paymentable_id', $fine->id)
            ->where('status', 'completed')
            ->exists();

        // Staff can only mark as paid if payment exists or they're verifying offline payment
        if ($validated['status'] === 'paid') {
            if (!$hasPayment && !$request->has('verify_offline')) {
                return redirect()->back()
                    ->with('error', 'Cannot mark as paid without payment record. Please verify payment first.');
            }
            
            if (!$validated['paid_date']) {
                $validated['paid_date'] = now();
            }
            
            // Add verification notes
            if (isset($validated['verification_notes']) && $validated['verification_notes']) {
                $fine->payment_notes = ($fine->payment_notes ? $fine->payment_notes . "\n" : '') . 
                    date('Y-m-d H:i:s') . ' (Staff Verified): ' . $validated['verification_notes'];
            }
        }

        if ($validated['status'] === 'pending') {
            $validated['paid_date'] = null;
        }

        $fine->update($validated);

        return redirect()->route('staff.fines.show', $fine)
            ->with('success', 'Payment status updated successfully!');
    }
}
