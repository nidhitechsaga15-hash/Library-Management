@extends('layouts.admin')

@section('title', 'Book Issue Report')
@section('page-title', 'Book Issue Report')

@section('content')
<div class="card-modern">
    <div class="card-header-modern">
        <h5>
            <i class="fas fa-exchange-alt text-primary"></i>
            Book Issue Report
        </h5>
        <a href="{{ route('admin.reports') }}" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left me-2"></i>Back to Reports
        </a>
    </div>
    <div class="card-body p-4">
        <!-- Statistics -->
        <div class="row g-3 mb-4">
            <div class="col-12 col-sm-6 col-md-6">
                <div class="stat-card border-start border-warning border-4">
                    <div class="stat-content">
                        <div class="stat-label">Total Issued</div>
                        <div class="stat-value text-warning">{{ $totalIssued }}</div>
                    </div>
                    <div class="stat-icon warning">
                        <i class="fas fa-book-open"></i>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-md-6">
                <div class="stat-card border-start border-success border-4">
                    <div class="stat-content">
                        <div class="stat-label">Total Returned</div>
                        <div class="stat-value text-success">{{ $totalReturned }}</div>
                    </div>
                    <div class="stat-icon success">
                        <i class="fas fa-check-double"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Search Form -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border">
                    <div class="card-body">
                        <form method="GET" action="{{ route('admin.reports.book-issue') }}" class="row g-3">
                            <div class="col-12 col-md-4">
                                <label for="status" class="form-label fw-semibold">Status</label>
                                <select name="status" id="status" class="form-select form-select-lg">
                                    <option value="">All Status</option>
                                    <option value="borrowed" {{ request('status') == 'borrowed' ? 'selected' : '' }}>Borrowed</option>
                                    <option value="returned" {{ request('status') == 'returned' ? 'selected' : '' }}>Returned</option>
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
                                    <a href="{{ route('admin.reports.book-issue') }}" class="btn btn-secondary">
                                        <i class="fas fa-redo me-2"></i>Reset
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Borrows Table -->
        <div class="row">
            <div class="col-12">
                <div class="table-responsive">
            <table id="bookIssueTable" class="table table-modern data-table mb-0 w-100">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>User</th>
                        <th>Book</th>
                        <th>Issue Date</th>
                        <th>Due Date</th>
                        <th>Return Date</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($borrows as $index => $borrow)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td class="fw-semibold">{{ $borrow->user->name }}</td>
                        <td>{{ $borrow->book->title }}</td>
                        <td>{{ $borrow->borrow_date->format('M d, Y') }}</td>
                        <td>{{ $borrow->due_date->format('M d, Y') }}</td>
                        <td>{{ $borrow->return_date ? $borrow->return_date->format('M d, Y') : 'N/A' }}</td>
                        <td>
                            @if($borrow->status === 'borrowed')
                                <span class="badge bg-warning">Borrowed</span>
                            @else
                                <span class="badge bg-success">Returned</span>
                            @endif
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
