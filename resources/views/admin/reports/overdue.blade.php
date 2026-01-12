@extends('layouts.admin')

@section('title', 'Overdue Report')
@section('page-title', 'Overdue Report')

@section('content')
<div class="card-modern">
    <div class="card-header-modern">
        <h5>
            <i class="fas fa-exclamation-triangle text-warning"></i>
            Overdue Report
        </h5>
        <a href="{{ route('admin.reports') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Back to Reports
        </a>
    </div>
    <div class="card-body p-4">
        <!-- Statistics -->
        <div class="row g-3 mb-4">
            <div class="col-12 col-md-6">
                <div class="stat-card border-start border-danger border-4">
                    <div class="stat-content">
                        <div class="stat-label">Total Overdue</div>
                        <div class="stat-value text-danger">{{ $totalOverdue }}</div>
                    </div>
                    <div class="stat-icon danger">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Search Form -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border">
                    <div class="card-body">
                        <form method="GET" action="{{ route('admin.reports.overdue') }}" class="row g-3">
                            <div class="col-12 col-md-6">
                                <label for="from_date" class="form-label fw-semibold">From Date</label>
                                <input type="date" name="from_date" id="from_date" 
                                    class="form-control form-control-lg" 
                                    value="{{ request('from_date') }}">
                            </div>
                            <div class="col-12 col-md-6">
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
                                    <a href="{{ route('admin.reports.overdue') }}" class="btn btn-secondary">
                                        <i class="fas fa-redo me-2"></i>Reset
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Overdue Books Table -->
        <div class="row">
            <div class="col-12">
                <div class="table-responsive">
                    <table id="overdueTable" class="table table-modern data-table mb-0 w-100">
                        <thead>
                            <tr>
                                <th style="width: 5%;">#</th>
                                <th style="width: 20%;">User</th>
                                <th style="width: 30%;">Book</th>
                                <th style="width: 15%;">Issue Date</th>
                                <th style="width: 15%;">Due Date</th>
                                <th style="width: 15%;">Days Overdue</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($overdueBorrows as $index => $borrow)
                            @php
                                // Use Borrow model's calculated attributes for consistency
                                $daysOverdue = $borrow->days_overdue;
                            @endphp
                            <tr class="{{ $daysOverdue > 30 ? 'table-danger' : 'table-warning' }}">
                                <td>{{ $index + 1 }}</td>
                                <td>
                                    <strong>{{ $borrow->user->name }}</strong>
                                    <br>
                                    <small class="text-muted">{{ $borrow->user->student_id ?? $borrow->user->email }}</small>
                                </td>
                                <td>
                                    <strong>{{ $borrow->book->title }}</strong>
                                    <br>
                                    <small class="text-muted">ISBN: {{ $borrow->book->isbn }}</small>
                                </td>
                                <td>{{ $borrow->borrow_date->format('M d, Y') }}</td>
                                <td>
                                    <span class="text-danger fw-bold">{{ $borrow->due_date->format('M d, Y') }}</span>
                                </td>
                                <td>
                                    <span class="badge bg-danger">{{ $daysOverdue }} day(s)</span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">No overdue books found</td>
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
