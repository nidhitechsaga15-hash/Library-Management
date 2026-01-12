@extends('layouts.admin')

@section('title', 'Payment Logs')
@section('page-title', 'Payment Logs')

@section('content')
<!-- Statistics -->
<div class="row g-3 mb-4">
    <div class="col-12 col-sm-6 col-md-3">
        <div class="stat-card border-start border-primary border-4">
            <div class="stat-content">
                <div class="stat-label">Total Payments</div>
                <div class="stat-value text-primary">{{ number_format($totalPayments) }}</div>
                <div class="stat-desc">All time</div>
            </div>
            <div class="stat-icon primary">
                <i class="fas fa-list"></i>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-md-3">
        <div class="stat-card border-start border-success border-4">
            <div class="stat-content">
                <div class="stat-label">Total Amount</div>
                <div class="stat-value text-success">₹{{ number_format($totalAmount, 2) }}</div>
                <div class="stat-desc">All time</div>
            </div>
            <div class="stat-icon success">
                <i class="fas fa-dollar-sign"></i>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-md-3">
        <div class="stat-card border-start border-info border-4">
            <div class="stat-content">
                <div class="stat-label">Today's Payments</div>
                <div class="stat-value text-info">{{ number_format($todayPayments) }}</div>
                <div class="stat-desc">Transactions</div>
            </div>
            <div class="stat-icon info">
                <i class="fas fa-calendar-day"></i>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-md-3">
        <div class="stat-card border-start border-warning border-4">
            <div class="stat-content">
                <div class="stat-label">Today's Amount</div>
                <div class="stat-value text-warning">₹{{ number_format($todayAmount, 2) }}</div>
                <div class="stat-desc">Collected</div>
            </div>
            <div class="stat-icon warning">
                <i class="fas fa-money-bill-wave"></i>
            </div>
        </div>
    </div>
</div>

<div class="card-modern">
    <div class="card-header-modern">
        <h5>
            <i class="fas fa-history text-primary"></i>
            Payment Transaction Logs
        </h5>
        <a href="{{ route('admin.fines.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Back to Fines
        </a>
    </div>
    <div class="card-body p-4">
        <!-- Filters -->
        <form method="GET" action="{{ route('admin.fines.payment-logs') }}" class="row g-3 mb-4">
            <div class="col-md-3">
                <label class="form-label small">Status</label>
                <select name="status" class="form-select">
                    <option value="">All Status</option>
                    <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>Failed</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small">Date From</label>
                <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label small">Date To</label>
                <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label small">&nbsp;</label>
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-filter me-2"></i>Filter
                </button>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-modern">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Student</th>
                        <th>Amount</th>
                        <th>Payment Method</th>
                        <th>Transaction ID</th>
                        <th>Payment Time</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($payments as $index => $payment)
                    <tr>
                        <td>{{ $payments->firstItem() + $index }}</td>
                        <td>
                            <div class="fw-semibold">{{ $payment->user->name ?? 'N/A' }}</div>
                            <small class="text-muted">{{ $payment->user->email ?? 'N/A' }}</small>
                        </td>
                        <td class="fw-bold text-success">₹{{ number_format($payment->amount, 2) }}</td>
                        <td>
                            <span class="badge bg-info">{{ ucfirst($payment->payment_method) }}</span>
                        </td>
                        <td>
                            <small class="font-monospace">{{ $payment->payment_id ?? $payment->order_id ?? 'N/A' }}</small>
                        </td>
                        <td>
                            <div>{{ $payment->paid_at ? $payment->paid_at->format('d M Y') : $payment->created_at->format('d M Y') }}</div>
                            <small class="text-muted">{{ $payment->paid_at ? $payment->paid_at->format('h:i A') : $payment->created_at->format('h:i A') }}</small>
                        </td>
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
                            @if($payment->paymentable)
                                <a href="{{ route('admin.fines.show', $payment->paymentable) }}" class="btn btn-sm btn-info text-white">
                                    <i class="fas fa-eye"></i>
                                </a>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-5">
                            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                            <p class="text-muted">No payment records found.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">
            {{ $payments->links() }}
        </div>
    </div>
</div>
@endsection

