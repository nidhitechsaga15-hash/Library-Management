@extends('layouts.staff')

@section('title', 'Failed Payments')
@section('page-title', 'Failed Payments - Student Assistance')

@section('content')
<div class="card-modern mb-4">
    <div class="card-header-modern">
        <h5>
            <i class="fas fa-exclamation-triangle text-danger"></i>
            Failed Payment Transactions
        </h5>
        <a href="{{ route('staff.fines.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Back to Fines
        </a>
    </div>
    <div class="card-body p-4">
        @if($failedPayments->isEmpty())
        <div class="text-center py-5">
            <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
            <p class="text-muted">No failed payments found. All payments are successful!</p>
        </div>
        @else
        <div class="alert alert-danger">
            <i class="fas fa-info-circle me-2"></i>
            <strong>{{ $failedPayments->count() }}</strong> failed payment(s) require student assistance.
        </div>

        <div class="table-responsive">
            <table class="table table-modern">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Student</th>
                        <th>Amount</th>
                        <th>Payment Method</th>
                        <th>Transaction ID</th>
                        <th>Failed Date</th>
                        <th>Failure Reason</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($failedPayments as $index => $payment)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>
                            <div class="fw-semibold">{{ $payment->user->name ?? 'N/A' }}</div>
                            <small class="text-muted">{{ $payment->user->email ?? 'N/A' }}</small>
                        </td>
                        <td class="fw-bold text-danger">₹{{ number_format($payment->amount, 2) }}</td>
                        <td>
                            <span class="badge bg-info">{{ ucfirst($payment->payment_method) }}</span>
                        </td>
                        <td>
                            <small class="font-monospace">{{ $payment->order_id ?? 'N/A' }}</small>
                        </td>
                        <td>
                            <div>{{ $payment->created_at->format('d M Y') }}</div>
                            <small class="text-muted">{{ $payment->created_at->format('h:i A') }}</small>
                        </td>
                        <td>
                            <span class="badge bg-danger">{{ $payment->failure_reason ?? 'Payment failed' }}</span>
                        </td>
                        <td>
                            <a href="{{ route('staff.fines.assist-student', $payment) }}" class="btn btn-sm btn-danger">
                                <i class="fas fa-life-ring me-1"></i>Assist Student
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
</div>
@endsection

