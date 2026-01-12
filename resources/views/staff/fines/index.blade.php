@extends('layouts.staff')

@section('title', 'Fines')
@section('page-title', 'Fine Management')

@section('content')
<!-- Statistics -->
<div class="row g-3 mb-4">
    <div class="col-12 col-sm-6 col-md-3">
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
    <div class="col-12 col-sm-6 col-md-3">
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
    <div class="col-12 col-sm-6 col-md-3">
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
    <div class="col-12 col-sm-6 col-md-3">
        <div class="stat-card border-start border-danger border-4">
            <div class="stat-content">
                <div class="stat-label">Failed Payments</div>
                <div class="stat-value text-danger">{{ $failedPayments }}</div>
                <div class="stat-desc">Need assistance</div>
            </div>
            <div class="stat-icon danger">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
        </div>
    </div>
</div>

<div class="card-modern">
    <div class="card-header-modern">
        <h5>
            <i class="fas fa-dollar-sign text-primary"></i>
            Fines Section
        </h5>
        <div>
            <a href="{{ route('staff.fines.failed-payments') }}" class="btn btn-danger me-2">
                <i class="fas fa-exclamation-triangle me-2"></i>Failed Payments ({{ $failedPayments }})
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
                                <th style="width: 15%;">Student</th>
                                <th style="width: 15%;">Book</th>
                                <th style="width: 10%;">Amount</th>
                                <th style="width: 8%;">Paid</th>
                                <th style="width: 8%;">Remaining</th>
                                <th style="width: 15%;">Reason</th>
                                <th style="width: 12%;">Payment Status</th>
                                <th style="width: 12%;">Action</th>
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
                                <td>{{ \Illuminate\Support\Str::limit($fine->reason, 40) }}</td>
                                <td>
                                    @if($fine->status === 'paid')
                                        <span class="badge bg-success">✅ Paid</span>
                                    @elseif(($fine->paid_amount ?? 0) > 0)
                                        <span class="badge bg-info">⏳ Partial</span>
                                    @else
                                        <span class="badge bg-warning text-dark">⏳ Pending</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('staff.fines.show', $fine) }}" class="btn btn-sm btn-info text-white" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @if($fine->status === 'paid' || ($fine->paid_amount ?? 0) > 0)
                                        <a href="{{ route('staff.fines.verify-payment', $fine) }}" class="btn btn-sm btn-success" title="Verify Payment">
                                            <i class="fas fa-check-circle"></i>
                                        </a>
                                        @endif
                                    </div>
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
</script>
@endpush
