@extends('layouts.staff')

@section('title', 'Staff Dashboard')
@section('page-title', 'Dashboard')

@section('content')
<!-- Statistics Cards -->
<div class="row g-4 mb-4">
    <!-- Active Borrows -->
    <div class="col-md-6 col-lg-3">
        <div class="stat-card">
            <div class="stat-content">
                <p class="stat-label">Active Borrows</p>
                <h2 class="stat-value">{{ $stats['active_borrows'] ?? 0 }}</h2>
                <p class="stat-desc">Currently borrowed</p>
            </div>
            <div class="stat-icon-wrapper primary">
                <i class="fas fa-book"></i>
            </div>
        </div>
    </div>

    <!-- Overdue -->
    <div class="col-md-6 col-lg-3">
        <div class="stat-card">
            <div class="stat-content">
                <p class="stat-label">Overdue</p>
                <h2 class="stat-value text-danger">{{ $stats['overdue_borrows'] ?? 0 }}</h2>
                <p class="stat-desc">Past due date</p>
            </div>
            <div class="stat-icon-wrapper danger">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
        </div>
    </div>

    <!-- Total Students -->
    <div class="col-md-6 col-lg-3">
        <div class="stat-card">
            <div class="stat-content">
                <p class="stat-label">Total Students</p>
                <h2 class="stat-value">{{ $stats['total_students'] ?? 0 }}</h2>
                <p class="stat-desc">Registered students</p>
            </div>
            <div class="stat-icon-wrapper success">
                <i class="fas fa-users"></i>
            </div>
        </div>
    </div>

    <!-- Available Books -->
    <div class="col-md-6 col-lg-3">
        <div class="stat-card">
            <div class="stat-content">
                <p class="stat-label">Available Books</p>
                <h2 class="stat-value">{{ $stats['available_books'] ?? 0 }}</h2>
                <p class="stat-desc">Ready to issue</p>
            </div>
            <div class="stat-icon-wrapper info">
                <i class="fas fa-book-open"></i>
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
                                <p class="mb-0 small">Action required</p>
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
                                <a href="{{ route('staff.borrows.index') }}" class="btn btn-sm btn-danger">
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

<!-- Recent Borrows -->
<div class="card-modern">
    <div class="card-header-modern">
        <h5>
            <i class="fas fa-history text-primary"></i>
            Recent Borrows
        </h5>
    </div>
    <div class="card-body p-4">
        <div class="row">
            <div class="col-12">
                <div class="table-responsive">
                    <table class="table table-modern data-table mb-0 w-100">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>User</th>
                        <th>Book</th>
                        <th>Due Date</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recent_borrows as $index => $borrow)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>
                            <div class="fw-semibold">{{ $borrow->user->name }}</div>
                            <small class="text-muted">{{ $borrow->user->email }}</small>
                        </td>
                        <td>{{ $borrow->book->title }}</td>
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
    </div>
</div>
@endsection
