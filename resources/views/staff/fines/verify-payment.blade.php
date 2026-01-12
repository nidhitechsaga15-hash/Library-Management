@extends('layouts.staff')

@section('title', 'Verify Payment')
@section('page-title', 'Verify Payment')

@section('content')
<div class="card-modern">
    <div class="card-header-modern">
        <h5>
            <i class="fas fa-check-circle text-success"></i>
            Payment Verification
        </h5>
        <a href="{{ route('staff.fines.show', $fine) }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Back
        </a>
    </div>
    <div class="card-body p-4">
        <!-- Fine Information -->
        <div class="row g-3 mb-4">
            <div class="col-md-6">
                <label class="text-muted small">Student</label>
                <div class="fw-semibold">{{ $fine->user->name }}</div>
                <small class="text-muted">{{ $fine->user->email }}</small>
            </div>
            <div class="col-md-6">
                <label class="text-muted small">Book</label>
                <div class="fw-semibold">{{ $fine->borrow->book->title }}</div>
                <small class="text-muted">ISBN: {{ $fine->borrow->book->isbn }}</small>
            </div>
            <div class="col-md-6">
                <label class="text-muted small">Fine Amount</label>
                <div class="fs-4 fw-bold text-danger">₹{{ number_format($fine->amount, 2) }}</div>
            </div>
            <div class="col-md-6">
                <label class="text-muted small">Payment Status</label>
                <div>
                    @if($fine->status === 'paid')
                        <span class="badge bg-success fs-6">✅ Paid</span>
                    @else
                        <span class="badge bg-warning text-dark fs-6">⏳ Pending</span>
                    @endif
                </div>
            </div>
        </div>

        <hr>

        <!-- Payment Records -->
        @if($payments->isNotEmpty())
        <h6 class="mb-3">
            <i class="fas fa-list me-2"></i>Payment Transaction Records
        </h6>
        <div class="table-responsive">
            <table class="table table-modern">
                <thead>
                    <tr>
                        <th>Date & Time</th>
                        <th>Amount</th>
                        <th>Payment Method</th>
                        <th>Transaction ID</th>
                        <th>Order ID</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($payments as $payment)
                    <tr>
                        <td>
                            <div>{{ $payment->paid_at ? $payment->paid_at->format('d M Y') : $payment->created_at->format('d M Y') }}</div>
                            <small class="text-muted">{{ $payment->paid_at ? $payment->paid_at->format('h:i A') : $payment->created_at->format('h:i A') }}</small>
                        </td>
                        <td class="fw-bold text-success">₹{{ number_format($payment->amount, 2) }}</td>
                        <td>
                            <span class="badge bg-info">{{ ucfirst($payment->payment_method) }}</span>
                        </td>
                        <td>
                            <small class="font-monospace">{{ $payment->payment_id ?? 'N/A' }}</small>
                        </td>
                        <td>
                            <small class="font-monospace">{{ $payment->order_id ?? 'N/A' }}</small>
                        </td>
                        <td>
                            @if($payment->status === 'completed')
                                <span class="badge bg-success">✅ Verified</span>
                            @elseif($payment->status === 'pending')
                                <span class="badge bg-warning">⏳ Pending</span>
                            @else
                                <span class="badge bg-danger">❌ Failed</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Verification Summary -->
        <div class="alert alert-info mt-4">
            <h6><i class="fas fa-info-circle me-2"></i>Verification Summary</h6>
            <ul class="mb-0">
                <li>Total Payments: {{ $payments->count() }}</li>
                <li>Completed: {{ $payments->where('status', 'completed')->count() }}</li>
                <li>Pending: {{ $payments->where('status', 'pending')->count() }}</li>
                <li>Failed: {{ $payments->where('status', 'failed')->count() }}</li>
                <li>Total Amount Paid: ₹{{ number_format($payments->where('status', 'completed')->sum('amount'), 2) }}</li>
            </ul>
        </div>
        @else
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle me-2"></i>
            No payment records found for this fine.
        </div>
        @endif
    </div>
</div>
@endsection

