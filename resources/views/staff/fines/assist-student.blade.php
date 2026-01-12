@extends('layouts.staff')

@section('title', 'Assist Student')
@section('page-title', 'Assist Student - Failed Payment')

@section('content')
<div class="row">
    <div class="col-lg-8">
        <!-- Payment Information -->
        <div class="card-modern mb-4">
            <div class="card-header-modern">
                <h5>
                    <i class="fas fa-life-ring text-danger"></i>
                    Failed Payment Details
                </h5>
            </div>
            <div class="card-body p-4">
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Payment Failed</strong> - Student needs assistance with this transaction.
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="text-muted small">Student Name</label>
                        <div class="fw-semibold">{{ $payment->user->name ?? 'N/A' }}</div>
                    </div>
                    <div class="col-md-6">
                        <label class="text-muted small">Student Email</label>
                        <div class="fw-semibold">{{ $payment->user->email ?? 'N/A' }}</div>
                    </div>
                    <div class="col-md-6">
                        <label class="text-muted small">Payment Amount</label>
                        <div class="fs-4 fw-bold text-danger">₹{{ number_format($payment->amount, 2) }}</div>
                    </div>
                    <div class="col-md-6">
                        <label class="text-muted small">Payment Method</label>
                        <div>
                            <span class="badge bg-info fs-6">{{ ucfirst($payment->payment_method) }}</span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="text-muted small">Order ID</label>
                        <div class="font-monospace">{{ $payment->order_id ?? 'N/A' }}</div>
                    </div>
                    <div class="col-md-6">
                        <label class="text-muted small">Transaction ID</label>
                        <div class="font-monospace">{{ $payment->payment_id ?? 'N/A' }}</div>
                    </div>
                    <div class="col-md-6">
                        <label class="text-muted small">Failed Date</label>
                        <div class="fw-semibold">{{ $payment->created_at->format('d M Y, h:i A') }}</div>
                    </div>
                    <div class="col-md-6">
                        <label class="text-muted small">Failure Reason</label>
                        <div class="text-danger fw-semibold">{{ $payment->failure_reason ?? 'Payment gateway error' }}</div>
                    </div>
                </div>

                @if($payment->gateway_response)
                <div class="mt-4">
                    <label class="text-muted small">Gateway Response</label>
                    <div class="bg-light p-3 rounded">
                        <pre class="mb-0 small">{{ json_encode($payment->gateway_response, JSON_PRETTY_PRINT) }}</pre>
                    </div>
                </div>
                @endif
            </div>
        </div>

        <!-- Fine Information -->
        @if($payment->paymentable && $payment->paymentable_type === 'App\Models\Fine')
        @php
            $fine = $payment->paymentable;
            $fine->load(['borrow.book']);
        @endphp
        <div class="card-modern mb-4">
            <div class="card-header-modern">
                <h5>
                    <i class="fas fa-info-circle text-primary"></i>
                    Related Fine Information
                </h5>
            </div>
            <div class="card-body p-4">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="text-muted small">Book</label>
                        <div class="fw-semibold">{{ $fine->borrow->book->title ?? 'N/A' }}</div>
                        <small class="text-muted">ISBN: {{ $fine->borrow->book->isbn ?? 'N/A' }}</small>
                    </div>
                    <div class="col-md-6">
                        <label class="text-muted small">Fine Amount</label>
                        <div class="fs-5 fw-bold text-danger">₹{{ number_format($fine->amount ?? 0, 2) }}</div>
                    </div>
                    <div class="col-md-6">
                        <label class="text-muted small">Fine Status</label>
                        <div>
                            @if($fine->status === 'paid')
                                <span class="badge bg-success">Paid</span>
                            @else
                                <span class="badge bg-warning">Pending</span>
                            @endif
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="text-muted small">Reason</label>
                        <div class="fw-semibold">{{ $fine->reason ?? 'N/A' }}</div>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>

    <div class="col-lg-4">
        <!-- Assistance Guide -->
        <div class="card-modern mb-4">
            <div class="card-header-modern">
                <h5>
                    <i class="fas fa-question-circle text-info"></i>
                    How to Assist
                </h5>
            </div>
            <div class="card-body p-4">
                <div class="mb-3">
                    <h6 class="small fw-bold">1. Check Payment Status</h6>
                    <p class="small text-muted mb-2">
                        Verify if payment was actually processed by checking the transaction ID with the payment gateway.
                    </p>
                </div>
                <div class="mb-3">
                    <h6 class="small fw-bold">2. Common Issues</h6>
                    <ul class="small text-muted mb-0">
                        <li>Insufficient funds</li>
                        <li>Network timeout</li>
                        <li>Card declined</li>
                        <li>Gateway error</li>
                    </ul>
                </div>
                <div class="mb-3">
                    <h6 class="small fw-bold">3. Solutions</h6>
                    <ul class="small text-muted mb-0">
                        <li>Ask student to retry payment</li>
                        <li>Check if payment was successful but not recorded</li>
                        <li>Contact admin if payment needs manual verification</li>
                        <li>Guide student to use alternative payment method</li>
                    </ul>
                </div>
                <div class="alert alert-warning small mb-0">
                    <i class="fas fa-shield-alt me-1"></i>
                    <strong>Security:</strong> Staff cannot modify payments directly. Contact admin for manual adjustments.
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="card-modern">
            <div class="card-body p-4">
                @if($payment->paymentable && $payment->paymentable_type === 'App\Models\Fine')
                <a href="{{ route('staff.fines.show', $payment->paymentable) }}" class="btn btn-primary w-100 mb-2">
                    <i class="fas fa-eye me-2"></i>View Fine Details
                </a>
                @endif
                <a href="{{ route('staff.fines.failed-payments') }}" class="btn btn-outline-secondary w-100 mb-2">
                    <i class="fas fa-arrow-left me-2"></i>Back to Failed Payments
                </a>
                <a href="{{ route('staff.fines.index') }}" class="btn btn-outline-secondary w-100">
                    <i class="fas fa-list me-2"></i>All Fines
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

