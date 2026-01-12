@extends('layouts.staff')

@section('title', 'Fine Details')
@section('page-title', 'Fine Details')

@section('content')
<div class="row">
    <div class="col-lg-8">
        <!-- Fine Information -->
        <div class="card-modern mb-4">
            <div class="card-header-modern">
                <h5>
                    <i class="fas fa-info-circle text-primary"></i>
                    Fine Information
                </h5>
            </div>
            <div class="card-body p-4">
                <div class="row g-3">
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
                    <div class="col-md-4">
                        <label class="text-muted small">Fine Amount</label>
                        <div class="fs-4 fw-bold text-danger">₹{{ number_format($fine->amount, 2) }}</div>
                    </div>
                    <div class="col-md-4">
                        <label class="text-muted small">Paid Amount</label>
                        <div class="fs-5 fw-bold text-success">₹{{ number_format($fine->paid_amount ?? 0, 2) }}</div>
                    </div>
                    <div class="col-md-4">
                        <label class="text-muted small">Remaining</label>
                        <div class="fs-5 fw-bold text-warning">₹{{ number_format($fine->remaining_amount ?? $fine->amount, 2) }}</div>
                    </div>
                    <div class="col-md-6">
                        <label class="text-muted small">Payment Status</label>
                        <div>
                            @if($fine->status === 'paid')
                                <span class="badge bg-success fs-6">✅ Paid</span>
                            @elseif(($fine->paid_amount ?? 0) > 0)
                                <span class="badge bg-info fs-6">⏳ Partial Payment</span>
                            @else
                                <span class="badge bg-warning text-dark fs-6">⏳ Pending</span>
                            @endif
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="text-muted small">Paid Date</label>
                        <div class="fw-semibold">{{ $fine->paid_date ? $fine->paid_date->format('d M Y, h:i A') : 'N/A' }}</div>
                    </div>
                    <div class="col-12">
                        <label class="text-muted small">Reason</label>
                        <div class="fw-semibold">{{ $fine->reason }}</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Payment History -->
        @if($payments->isNotEmpty())
        <div class="card-modern mb-4">
            <div class="card-header-modern">
                <h5>
                    <i class="fas fa-history text-primary"></i>
                    Payment History
                </h5>
            </div>
            <div class="card-body p-4">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Amount</th>
                                <th>Method</th>
                                <th>Transaction ID</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($payments as $payment)
                            <tr>
                                <td>{{ $payment->paid_at ? $payment->paid_at->format('d M Y, h:i A') : $payment->created_at->format('d M Y, h:i A') }}</td>
                                <td class="fw-bold">₹{{ number_format($payment->amount, 2) }}</td>
                                <td><span class="badge bg-info">{{ ucfirst($payment->payment_method) }}</span></td>
                                <td><small>{{ $payment->payment_id ?? $payment->order_id ?? 'N/A' }}</small></td>
                                <td>
                                    @if($payment->status === 'completed')
                                        <span class="badge bg-success">✅ Completed</span>
                                    @elseif($payment->status === 'pending')
                                        <span class="badge bg-warning">⏳ Pending</span>
                                    @else
                                        <span class="badge bg-danger">❌ Failed</span>
                                    @endif
                                </td>
                                <td>
                                    @if($payment->status === 'failed')
                                        <a href="{{ route('staff.fines.assist-student', $payment) }}" class="btn btn-sm btn-danger">
                                            <i class="fas fa-life-ring me-1"></i>Assist
                                        </a>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif

        <!-- Failed Payments Alert -->
        @if($failedPayments->isNotEmpty())
        <div class="alert alert-danger">
            <h6><i class="fas fa-exclamation-triangle me-2"></i>Failed Payments Detected</h6>
            <p class="mb-2">This fine has {{ $failedPayments->count() }} failed payment attempt(s).</p>
            <a href="{{ route('staff.fines.assist-student', $failedPayments->first()) }}" class="btn btn-sm btn-danger">
                <i class="fas fa-life-ring me-1"></i>Assist Student
            </a>
        </div>
        @endif
    </div>

    <div class="col-lg-4">
        <!-- Payment Verification -->
        @if($fine->status === 'paid' || ($fine->paid_amount ?? 0) > 0)
        <div class="card-modern mb-4">
            <div class="card-header-modern">
                <h5>
                    <i class="fas fa-check-circle text-success"></i>
                    Verify Payment
                </h5>
            </div>
            <div class="card-body p-4">
                <p class="text-muted small mb-3">
                    <i class="fas fa-info-circle me-1"></i>
                    View payment details and verify transaction.
                </p>
                <a href="{{ route('staff.fines.verify-payment', $fine) }}" class="btn btn-success w-100">
                    <i class="fas fa-search me-2"></i>Verify Payment Details
                </a>
            </div>
        </div>
        @endif

        <!-- Update Status (Limited) -->
        <div class="card-modern mb-4">
            <div class="card-header-modern">
                <h5>
                    <i class="fas fa-edit text-warning"></i>
                    Update Status
                </h5>
            </div>
            <div class="card-body p-4">
                <form method="POST" action="{{ route('staff.fines.update-payment', $fine) }}">
                    @csrf
                    @method('PUT')
                    
                    <div class="mb-3">
                        <label class="form-label small">Payment Status</label>
                        <select name="status" class="form-select" required>
                            <option value="pending" {{ $fine->status === 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="paid" {{ $fine->status === 'paid' ? 'selected' : '' }}>Paid</option>
                        </select>
                        <small class="text-muted d-block mt-1">
                            <i class="fas fa-shield-alt me-1"></i>
                            Can only verify if payment exists
                        </small>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label small">Paid Date</label>
                        <input type="date" name="paid_date" class="form-control" value="{{ $fine->paid_date?->format('Y-m-d') }}">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label small">Verification Notes</label>
                        <textarea name="verification_notes" class="form-control" rows="2" placeholder="Add verification notes..."></textarea>
                    </div>
                    
                    @if($fine->status === 'pending')
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="verify_offline" id="verify_offline" value="1">
                            <label class="form-check-label small" for="verify_offline">
                                Verify offline/cash payment
                            </label>
                        </div>
                    </div>
                    @endif
                    
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-save me-2"></i>Update Status
                    </button>
                </form>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="card-modern">
            <div class="card-body p-4">
                <a href="{{ route('staff.fines.index') }}" class="btn btn-outline-secondary w-100 mb-2">
                    <i class="fas fa-arrow-left me-2"></i>Back to Fines
                </a>
                <a href="{{ route('staff.fines.failed-payments') }}" class="btn btn-outline-danger w-100">
                    <i class="fas fa-exclamation-triangle me-2"></i>View Failed Payments
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
