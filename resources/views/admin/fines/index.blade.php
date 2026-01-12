@extends('layouts.admin')

@section('title', 'Fine Management Dashboard')
@section('page-title', 'Fine Management Dashboard')

@push('styles')
<style>
    .live-payment-item {
        padding: 12px;
        border-left: 4px solid #28a745;
        background: #f8f9fa;
        margin-bottom: 8px;
        border-radius: 4px;
        animation: slideIn 0.3s ease-out;
    }
    @keyframes slideIn {
        from {
            opacity: 0;
            transform: translateX(-10px);
        }
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }
    .status-partial {
        background: #fff3cd;
        color: #856404;
    }
    .status-waived {
        background: #d1ecf1;
        color: #0c5460;
    }
    .status-adjusted {
        background: #d4edda;
        color: #155724;
    }
</style>
@endpush

@section('content')
<!-- Statistics Dashboard -->
<div class="row g-3 mb-4">
    <div class="col-12 col-sm-6 col-md-4 col-lg-3">
        <div class="stat-card border-start border-primary border-4">
            <div class="stat-content">
                <div class="stat-label">Total Fines</div>
                <div class="stat-value text-primary">₹{{ number_format($totalFines, 2) }}</div>
                <div class="stat-desc">All time fines</div>
            </div>
            <div class="stat-icon primary">
                <i class="fas fa-dollar-sign"></i>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-md-4 col-lg-3">
        <div class="stat-card border-start border-warning border-4">
            <div class="stat-content">
                <div class="stat-label">Pending Fines</div>
                <div class="stat-value text-warning">₹{{ number_format($totalPending, 2) }}</div>
                <div class="stat-desc">Awaiting payment</div>
            </div>
            <div class="stat-icon warning">
                <i class="fas fa-clock"></i>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-md-4 col-lg-3">
        <div class="stat-card border-start border-success border-4">
            <div class="stat-content">
                <div class="stat-label">Paid Fines</div>
                <div class="stat-value text-success">₹{{ number_format($totalPaid, 2) }}</div>
                <div class="stat-desc">Completed payments</div>
            </div>
            <div class="stat-icon success">
                <i class="fas fa-check-circle"></i>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-md-4 col-lg-3">
        <div class="stat-card border-start border-info border-4">
            <div class="stat-content">
                <div class="stat-label">Partial Payments</div>
                <div class="stat-value text-info">₹{{ number_format($totalPartial, 2) }}</div>
                <div class="stat-desc">Partially paid</div>
            </div>
            <div class="stat-icon info">
                <i class="fas fa-percent"></i>
            </div>
        </div>
    </div>
    @if($totalWaived > 0 || $totalAdjusted > 0)
    <div class="col-12 col-sm-6 col-md-4 col-lg-3">
        <div class="stat-card border-start border-secondary border-4">
            <div class="stat-content">
                <div class="stat-label">Waived</div>
                <div class="stat-value text-secondary">₹{{ number_format($totalWaived, 2) }}</div>
                <div class="stat-desc">Waived fines</div>
            </div>
            <div class="stat-icon secondary">
                <i class="fas fa-hand-holding-heart"></i>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-md-4 col-lg-3">
        <div class="stat-card border-start border-dark border-4">
            <div class="stat-content">
                <div class="stat-label">Adjusted</div>
                <div class="stat-value text-dark">₹{{ number_format($totalAdjusted, 2) }}</div>
                <div class="stat-desc">Adjusted amounts</div>
            </div>
            <div class="stat-icon dark">
                <i class="fas fa-edit"></i>
            </div>
        </div>
    </div>
    @endif
</div>

<!-- Live Payment Tracking -->
@if($recentPayments->isNotEmpty())
<div class="card-modern mb-4">
    <div class="card-header-modern">
        <h5>
            <i class="fas fa-bolt text-warning"></i>
            Live Payment Tracking (Last 24 Hours)
        </h5>
        <span class="badge bg-success" id="livePaymentCount">{{ $recentPayments->count() }}</span>
    </div>
    <div class="card-body p-4">
        <div id="livePaymentsContainer">
            @foreach($recentPayments as $payment)
            <div class="live-payment-item">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <strong>{{ $payment->user->name ?? 'N/A' }}</strong>
                        <small class="text-muted ms-2">{{ $payment->user->email ?? 'N/A' }}</small>
                        <div class="mt-1">
                            <span class="badge bg-success">₹{{ number_format($payment->amount, 2) }}</span>
                            <span class="badge bg-info ms-2">{{ ucfirst($payment->payment_method) }}</span>
                            @if($payment->payment_id)
                            <span class="badge bg-secondary ms-2">Txn: {{ substr($payment->payment_id, 0, 10) }}...</span>
                            @endif
                        </div>
                    </div>
                    <div class="text-end">
                        <div class="text-muted small">{{ $payment->paid_at ? $payment->paid_at->format('h:i A') : 'N/A' }}</div>
                        <div class="text-muted small">{{ $payment->paid_at ? $payment->paid_at->diffForHumans() : 'N/A' }}</div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endif

<!-- Fines Table -->
<div class="card-modern">
    <div class="card-header-modern">
        <h5>
            <i class="fas fa-dollar-sign text-primary"></i>
            All Fines
        </h5>
        <div>
            <a href="{{ route('admin.fines.payment-logs') }}" class="btn btn-info me-2">
                <i class="fas fa-list me-2"></i>Payment Logs
            </a>
            <a href="{{ route('admin.fines.settings') }}" class="btn btn-secondary">
                <i class="fas fa-cog me-2"></i>Settings
            </a>
        </div>
    </div>
    <div class="card-body p-4">
        <!-- Filters -->
        <div class="row mb-3">
            <div class="col-md-4">
                <select id="statusFilter" class="form-select" onchange="filterFines()">
                    <option value="">All Status</option>
                    <option value="pending">Pending</option>
                    <option value="paid">Paid</option>
                    <option value="waived">Waived</option>
                    <option value="adjusted">Adjusted</option>
                </select>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="table-responsive">
                    <table id="finesTable" class="table table-modern data-table mb-0 w-100">
                        <thead>
                            <tr>
                                <th style="width: 5%;">#</th>
                                <th style="width: 14%;">User</th>
                                <th style="width: 16%;">Book</th>
                                <th style="width: 10%;">Amount</th>
                                <th style="width: 8%;">Paid</th>
                                <th style="width: 8%;">Remaining</th>
                                <th style="width: 20%;">Reason</th>
                                <th style="width: 10%;">Status</th>
                                <th style="width: 9%;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($fines as $index => $fine)
                            <tr data-status="{{ $fine->status }}">
                                <td>{{ $index + 1 }}</td>
                                <td>
                                    <div class="fw-semibold">{{ $fine->user->name }}</div>
                                    <small class="text-muted">{{ $fine->user->email }}</small>
                                </td>
                                <td>
                                    <div class="fw-semibold">{{ $fine->borrow->book->title }}</div>
                                    <small class="text-muted">{{ $fine->borrow->book->isbn }}</small>
                                </td>
                                <td class="fw-bold text-danger">₹{{ number_format($fine->amount, 2) }}</td>
                                <td class="text-success">₹{{ number_format($fine->paid_amount ?? 0, 2) }}</td>
                                <td class="text-warning">₹{{ number_format($fine->remaining_amount ?? $fine->amount, 2) }}</td>
                                <td>{{ \Illuminate\Support\Str::limit($fine->reason, 50) }}</td>
                                <td>
                                    @if($fine->status === 'paid')
                                        <span class="badge bg-success">✅ Paid</span>
                                    @elseif($fine->status === 'pending')
                                        @if(($fine->paid_amount ?? 0) > 0)
                                            <span class="badge status-partial">⏳ Partial</span>
                                        @else
                                            <span class="badge bg-warning text-dark">⏳ Pending</span>
                                        @endif
                                    @elseif($fine->status === 'waived')
                                        <span class="badge status-waived">🙏 Waived</span>
                                    @elseif($fine->status === 'adjusted')
                                        <span class="badge status-adjusted">✏️ Adjusted</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('admin.fines.show', $fine) }}" class="btn btn-sm btn-info text-white" title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function filterFines() {
        const status = document.getElementById('statusFilter').value;
        const rows = document.querySelectorAll('#finesTable tbody tr');
        
        rows.forEach(row => {
            if (!status || row.getAttribute('data-status') === status) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }

    // Live payment tracking (poll every 10 seconds)
    let lastCheckTime = new Date().toISOString();
    
    setInterval(() => {
        fetch(`/admin/fines/live-payments?since=${lastCheckTime}`)
            .then(response => response.json())
            .then(data => {
                if (data.success && data.payments.length > 0) {
                    const container = document.getElementById('livePaymentsContainer');
                    const countBadge = document.getElementById('livePaymentCount');
                    
                    data.payments.forEach(payment => {
                        const paymentItem = document.createElement('div');
                        paymentItem.className = 'live-payment-item';
                        paymentItem.innerHTML = `
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <strong>${payment.user_name}</strong>
                                    <small class="text-muted ms-2">${payment.user_email}</small>
                                    <div class="mt-1">
                                        <span class="badge bg-success">₹${parseFloat(payment.amount).toFixed(2)}</span>
                                        <span class="badge bg-info ms-2">${payment.payment_method}</span>
                                        ${payment.transaction_id !== 'N/A' ? `<span class="badge bg-secondary ms-2">Txn: ${payment.transaction_id.substring(0, 10)}...</span>` : ''}
                                    </div>
                                </div>
                                <div class="text-end">
                                    <div class="text-muted small">${payment.paid_at}</div>
                                    <div class="text-muted small">${payment.time_ago}</div>
                                </div>
                            </div>
                        `;
                        container.insertBefore(paymentItem, container.firstChild);
                    });
                    
                    // Update count
                    const currentCount = parseInt(countBadge.textContent) || 0;
                    countBadge.textContent = currentCount + data.payments.length;
                    
                    // Update last check time
                    lastCheckTime = new Date().toISOString();
                }
            })
            .catch(error => console.error('Error fetching live payments:', error));
    }, 10000); // Poll every 10 seconds
</script>
@endpush
