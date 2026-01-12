@extends('layouts.admin')

@section('title', 'Fine Report')
@section('page-title', 'Fine Report')

@section('content')
<div class="card-modern">
    <div class="card-header-modern">
        <h5>
            <i class="fas fa-dollar-sign text-primary"></i>
            Fine Report
        </h5>
        <a href="{{ route('admin.reports') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Back to Reports
        </a>
    </div>
    <div class="card-body p-4">
        <!-- Statistics -->
        <div class="row g-3 mb-4">
            <div class="col-12 col-sm-6 col-md-4">
                <div class="stat-card border-start border-danger border-4">
                    <div class="stat-content">
                        <div class="stat-label">Total Fines</div>
                        <div class="stat-value text-danger">₹{{ number_format($totalFines, 2) }}</div>
                    </div>
                    <div class="stat-icon danger">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-md-4">
                <div class="stat-card border-start border-warning border-4">
                    <div class="stat-content">
                        <div class="stat-label">Pending Fines</div>
                        <div class="stat-value text-warning">₹{{ number_format($totalPending, 2) }}</div>
                    </div>
                    <div class="stat-icon warning">
                        <i class="fas fa-clock"></i>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-md-4">
                <div class="stat-card border-start border-success border-4">
                    <div class="stat-content">
                        <div class="stat-label">Paid Fines</div>
                        <div class="stat-value text-success">₹{{ number_format($totalPaid, 2) }}</div>
                    </div>
                    <div class="stat-icon success">
                        <i class="fas fa-check-circle"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Search Form -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border">
                    <div class="card-body">
                        <form method="GET" action="{{ route('admin.reports.fines') }}" class="row g-3">
                            <div class="col-12 col-md-4">
                                <label for="status" class="form-label fw-semibold">Status</label>
                                <select name="status" id="status" class="form-select form-select-lg">
                                    <option value="">All Status</option>
                                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>Paid</option>
                                </select>
                            </div>
                            <div class="col-12 col-md-4">
                                <label for="from_date" class="form-label fw-semibold">From Date</label>
                                <input type="date" name="from_date" id="from_date" 
                                    class="form-control form-control-lg" 
                                    value="{{ request('from_date') }}">
                            </div>
                            <div class="col-12 col-md-4">
                                <label for="to_date" class="form-label fw-semibold">To Date</label>
                                <input type="date" name="to_date" id="to_date" 
                                    class="form-control form-control-lg" 
                                    value="{{ request('to_date') }}">
                            </div>
                            <div class="col-12">
                                <div class="form-actions">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-search me-2"></i>Search
                                    </button>
                                    <a href="{{ route('admin.reports.fines') }}" class="btn btn-secondary">
                                        <i class="fas fa-redo me-2"></i>Reset
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Fines Table -->
        <div class="row">
            <div class="col-12">
                <div class="table-responsive">
                    <table id="finesTable" class="table table-modern data-table mb-0 w-100">
                        <thead>
                            <tr>
                                <th style="width: 5%;">#</th>
                                <th style="width: 18%;">User</th>
                                <th style="width: 25%;">Book</th>
                                <th style="width: 12%;">Amount</th>
                                <th style="width: 20%;">Reason</th>
                                <th style="width: 10%;">Status</th>
                                <th style="width: 10%;">Paid Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($fines as $index => $fine)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>
                                    <strong>{{ $fine->user->name }}</strong>
                                    <br>
                                    <small class="text-muted">{{ $fine->user->email }}</small>
                                </td>
                                <td>
                                    @if($fine->borrow && $fine->borrow->book)
                                        <strong>{{ $fine->borrow->book->title }}</strong>
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="fw-bold">₹{{ number_format($fine->amount, 2) }}</span>
                                </td>
                                <td>{{ $fine->reason }}</td>
                                <td>
                                    @if($fine->status === 'paid')
                                        <span class="badge bg-success">Paid</span>
                                    @else
                                        <span class="badge bg-warning">Pending</span>
                                    @endif
                                </td>
                                <td>
                                    {{ $fine->paid_date ? $fine->paid_date->format('M d, Y') : 'N/A' }}
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">No fines found</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
