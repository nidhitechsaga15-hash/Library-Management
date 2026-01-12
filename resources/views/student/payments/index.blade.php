@extends('layouts.student')

@section('title', 'Payment History')
@section('page-title', 'Payment History')

@section('content')
<div class="card-modern">
    <div class="card-header-modern">
        <h5>
            <i class="fas fa-credit-card text-primary"></i>
            Payment History
        </h5>
    </div>
    <div class="card-body p-4">
        @if($payments->isEmpty())
        <div class="text-center py-5">
            <i class="fas fa-receipt fa-3x text-muted mb-3"></i>
            <p class="text-muted">No payment history found.</p>
        </div>
        @else
        <div class="table-responsive">
            <table class="table table-modern">
                <thead>
                    <tr>
                        <th>Payment ID</th>
                        <th>Type</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($payments as $payment)
                    <tr>
                        <td>
                            <strong>#{{ $payment->order_id ?? $payment->id }}</strong>
                        </td>
                        <td>
                            <span class="badge bg-info">
                                {{ ucfirst(str_replace('_', ' ', $payment->payment_type)) }}
                            </span>
                        </td>
                        <td>
                            <strong>₹{{ number_format($payment->amount, 2) }}</strong>
                        </td>
                        <td>
                            @if($payment->status === 'completed')
                                <span class="badge bg-success">Completed</span>
                            @elseif($payment->status === 'pending')
                                <span class="badge bg-warning">Pending</span>
                            @elseif($payment->status === 'failed')
                                <span class="badge bg-danger">Failed</span>
                            @else
                                <span class="badge bg-secondary">{{ ucfirst($payment->status) }}</span>
                            @endif
                        </td>
                        <td>
                            {{ $payment->created_at->format('d M Y, h:i A') }}
                        </td>
                        <td>
                            <a href="{{ route('student.payments.show', $payment) }}" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-eye"></i> View
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-3">
            {{ $payments->links() }}
        </div>
        @endif
    </div>
</div>
@endsection

