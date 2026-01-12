@extends('layouts.admin')

@section('title', 'Admin Dashboard')
@section('page-title', 'Dashboard')

@section('content')
<!-- Statistics Cards -->
<div class="row g-4 mb-4">
    <!-- Total Books -->
    <div class="col-md-6 col-lg-4">
        <div class="stat-card">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div class="flex-grow-1">
                    <p class="text-muted mb-1 small fw-semibold">Total Books</p>
                    <h2 class="mb-0 fw-bold stat-value">{{ $stats['total_books'] ?? 0 }}</h2>
                    <p class="text-muted mb-0 small stat-desc">All books in library</p>
                </div>
                <div class="stat-icon primary">
                    <i class="fas fa-book"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Issued Books -->
    <div class="col-md-6 col-lg-4">
        <div class="stat-card">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div class="flex-grow-1">
                    <p class="text-muted mb-1 small fw-semibold">Issued Books</p>
                    <h2 class="mb-0 fw-bold stat-value">{{ $stats['issued_books'] ?? 0 }}</h2>
                    <p class="text-muted mb-0 small stat-desc">Currently borrowed</p>
                </div>
                <div class="stat-icon warning">
                    <i class="fas fa-clock"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Returned Books -->
    <div class="col-md-6 col-lg-4">
        <div class="stat-card">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div class="flex-grow-1">
                    <p class="text-muted mb-1 small fw-semibold">Returned Books</p>
                    <h2 class="mb-0 fw-bold stat-value">{{ $stats['returned_books'] ?? 0 }}</h2>
                    <p class="text-muted mb-0 small stat-desc">Successfully returned</p>
                </div>
                <div class="stat-icon success">
                    <i class="fas fa-check-circle"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Overdue Books -->
    <div class="col-md-6 col-lg-4">
        <div class="stat-card">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div class="flex-grow-1">
                    <p class="text-muted mb-1 small fw-semibold">Overdue Books</p>
                    <h2 class="mb-0 fw-bold stat-value text-danger">{{ $stats['overdue_books'] ?? 0 }}</h2>
                    <p class="text-muted mb-0 small stat-desc">Past due date</p>
                </div>
                <div class="stat-icon danger">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Total Students -->
    <div class="col-md-6 col-lg-4">
        <div class="stat-card">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div class="flex-grow-1">
                    <p class="text-muted mb-1 small fw-semibold">Total Students</p>
                    <h2 class="mb-0 fw-bold stat-value">{{ $stats['total_students'] ?? 0 }}</h2>
                    <p class="text-muted mb-0 small stat-desc">Registered students</p>
                </div>
                <div class="stat-icon info">
                    <i class="fas fa-users"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Total Staff -->
    <div class="col-md-6 col-lg-4">
        <div class="stat-card">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div class="flex-grow-1">
                    <p class="text-muted mb-1 small fw-semibold">Total Staff</p>
                    <h2 class="mb-0 fw-bold stat-value">{{ $stats['total_staff'] ?? 0 }}</h2>
                    <p class="text-muted mb-0 small stat-desc">Library staff members</p>
                </div>
                <div class="stat-icon secondary">
                    <i class="fas fa-user-tie"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Alerts Section -->
@if(isset($alerts) && ($alerts['books_due_today'] > 0 || $alerts['books_due_tomorrow'] > 0 || $alerts['overdue_books'] > 0))
<div class="row g-4 mb-4">
    <div class="col-12">
        <div class="card-modern border-warning">
            <div class="card-header-modern bg-warning bg-opacity-10">
                <h5 class="mb-0">
                    <i class="fas fa-bell text-warning me-2"></i>
                    Important Alerts
                </h5>
            </div>
            <div class="card-body p-4">
                <div class="row g-3">
                    @if($alerts['books_due_today'] > 0)
                    <div class="col-md-4">
                        <div class="alert alert-warning mb-0 d-flex align-items-center">
                            <i class="fas fa-exclamation-circle fa-2x me-3"></i>
                            <div>
                                <strong>{{ $alerts['books_due_today'] }} Book(s) Due Today</strong>
                                <p class="mb-0 small">Return deadline is today!</p>
                            </div>
                        </div>
                    </div>
                    @endif
                    
                    @if($alerts['books_due_tomorrow'] > 0)
                    <div class="col-md-4">
                        <div class="alert alert-info mb-0 d-flex align-items-center">
                            <i class="fas fa-info-circle fa-2x me-3"></i>
                            <div>
                                <strong>{{ $alerts['books_due_tomorrow'] }} Book(s) Due Tomorrow</strong>
                                <p class="mb-0 small">Reminder: Due date tomorrow</p>
                            </div>
                        </div>
                    </div>
                    @endif
                    
                    @if($alerts['overdue_books'] > 0)
                    <div class="col-md-4">
                        <div class="alert alert-danger mb-0 d-flex align-items-center">
                            <i class="fas fa-times-circle fa-2x me-3"></i>
                            <div>
                                <strong>{{ $alerts['overdue_books'] }} Overdue Book(s)</strong>
                                <p class="mb-0 small">Fine ₹{{ number_format($alerts['pending_fines_amount'], 2) }} pending</p>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
                
                @if($overdueBorrows && $overdueBorrows->count() > 0)
                <div class="mt-3">
                    <h6 class="fw-semibold mb-2">Recent Overdue Books:</h6>
                    <div class="list-group">
                        @foreach($overdueBorrows as $borrow)
                        <div class="list-group-item list-group-item-danger">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <strong>{{ $borrow->user->name }}</strong> - 
                                    <span>{{ $borrow->book->title }}</span>
                                    <br>
                                    <small class="text-muted">
                                        Due: {{ $borrow->due_date->format('M d, Y') }} | 
                                        Overdue: {{ $borrow->days_overdue }} day(s) | 
                                        Fine: ₹{{ number_format($borrow->current_fine_amount, 2) }}
                                    </small>
                                </div>
                                <a href="{{ route('admin.borrows.index') }}" class="btn btn-sm btn-danger">
                                    <i class="fas fa-eye me-1"></i>View
                                </a>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endif

<!-- Recent Borrows Table -->
<div class="card-modern">
    <div class="card-header-modern">
        <h5>
            <i class="fas fa-history text-primary"></i>
            Recent Borrows
        </h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-modern data-table mb-0">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Book</th>
                        <th>Borrow Date</th>
                        <th>Due Date</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recent_borrows as $borrow)
                    <tr>
                        <td>
                            <div class="fw-semibold">{{ $borrow->user->name }}</div>
                            <small class="text-muted">{{ $borrow->user->email }}</small>
                        </td>
                        <td>{{ $borrow->book->title }}</td>
                        <td>{{ $borrow->borrow_date->format('M d, Y') }}</td>
                        <td>{{ $borrow->due_date->format('M d, Y') }}</td>
                        <td>
                            @if($borrow->status === 'borrowed')
                                <span class="badge bg-warning text-dark">
                                    <i class="fas fa-clock me-1"></i>Borrowed
                                </span>
                            @elseif($borrow->status === 'returned')
                                <span class="badge bg-success">
                                    <i class="fas fa-check-circle me-1"></i>Returned
                                </span>
                            @else
                                <span class="badge bg-danger">
                                    <i class="fas fa-times-circle me-1"></i>{{ ucfirst($borrow->status) }}
                                </span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-5">
                            <div class="text-muted">
                                <i class="fas fa-inbox fa-3x d-block mb-3"></i>
                                <p class="mb-0">No recent borrows</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
