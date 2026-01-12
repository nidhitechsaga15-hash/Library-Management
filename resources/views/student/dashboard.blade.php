@extends('layouts.student')

@section('title', 'Student Dashboard')
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

    <!-- Total Borrows -->
    <div class="col-md-6 col-lg-3">
        <div class="stat-card">
            <div class="stat-content">
                <p class="stat-label">Total Borrows</p>
                <h2 class="stat-value">{{ $stats['total_borrows'] ?? 0 }}</h2>
                <p class="stat-desc">All time borrows</p>
            </div>
            <div class="stat-icon-wrapper success">
                <i class="fas fa-history"></i>
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

    <!-- Pending Fines -->
    <div class="col-md-6 col-lg-3">
        <div class="stat-card">
            <div class="stat-content">
                <p class="stat-label">Pending Fines</p>
                <h2 class="stat-value text-warning">₹{{ number_format($stats['pending_fines'] ?? 0, 2) }}</h2>
                <p class="stat-desc">Amount to pay</p>
            </div>
            <div class="stat-icon-wrapper warning">
                <i class="fas fa-dollar-sign"></i>
            </div>
        </div>
    </div>
</div>

<!-- Pending Fines Alert -->
@if(isset($pendingFines) && $pendingFines->count() > 0)
<div class="alert alert-danger mb-4" role="alert">
    <div class="d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center">
            <i class="fas fa-exclamation-triangle fa-2x me-3"></i>
            <div>
                <h5 class="alert-heading mb-1">Pending Fines - Payment Required</h5>
                <p class="mb-0">
                    You have <strong>{{ $pendingFines->count() }}</strong> pending fine(s) totaling 
                    <strong>₹{{ number_format($stats['pending_fines'] ?? 0, 2) }}</strong>
                </p>
                <p class="mb-0 mt-1">
                    <small>Pay via QR code to avoid restrictions on new book issues.</small>
                </p>
            </div>
        </div>
        <div>
            <a href="{{ route('student.fines.index') }}" class="btn btn-danger btn-lg">
                <i class="fas fa-qrcode me-2"></i>Pay Fines Now
            </a>
        </div>
    </div>
</div>
@endif

<!-- My Issued Books -->
<div class="card-modern">
    <div class="card-header-modern">
        <h5>
            <i class="fas fa-book-open text-primary"></i>
            My Issued Books
        </h5>
        <a href="{{ route('student.my-books') }}" class="btn btn-sm btn-primary">
            View All <i class="fas fa-arrow-right ms-2"></i>
        </a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-modern mb-0">
                <thead>
                    <tr>
                        <th>Book</th>
                        <th>ISBN</th>
                        <th>Issue Date</th>
                        <th>Due Date</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($issued_books as $borrow)
                    @php
                        $isOverdue = $borrow->due_date < now() && $borrow->status === 'borrowed';
                        $fine = $borrow->fine;
                        
                        // Use Borrow model's calculated attributes for consistency
                        if ($isOverdue) {
                            // Refresh borrow to get latest calculations
                            $borrow->refresh();
                            
                            $daysOverdue = $borrow->days_overdue;
                            $fineAmount = $borrow->current_fine_amount; // Only pending fine (from last_fine_paid_date)
                            $pendingDays = $borrow->pending_fine_days;
                            $totalPaid = $borrow->total_fine_paid ?? 0;
                            $finePerDay = $borrow->fine_per_day ?? 10;
                            $lastPaidDate = $borrow->last_fine_paid_date;
                            
                            // Update or create fine record
                            if ($fineAmount > 0) {
                                if ($fine) {
                                    // Update existing fine
                                    $fine->amount = $fineAmount;
                                    $fine->remaining_amount = $fineAmount;
                                    $fine->reason = 'Overdue book - ' . $pendingDays . ' day(s) pending from ' . 
                                        ($lastPaidDate ? $lastPaidDate->format('Y-m-d') : $borrow->due_date->format('Y-m-d'));
                                    $fine->status = 'pending';
                                    $fine->save();
                                } else {
                                    // Create new fine
                                    try {
                                        $fine = \App\Models\Fine::create([
                                            'borrow_id' => $borrow->id,
                                            'user_id' => $borrow->user_id,
                                            'amount' => $fineAmount,
                                            'remaining_amount' => $fineAmount,
                                            'reason' => 'Overdue book - ' . $pendingDays . ' day(s) pending from ' . 
                                                ($lastPaidDate ? $lastPaidDate->format('Y-m-d') : $borrow->due_date->format('Y-m-d')),
                                            'status' => 'pending',
                                            'days_overdue_at_creation' => $daysOverdue,
                                        ]);
                                        $borrow->load('fine');
                                        $fine = $borrow->fine;
                                    } catch (\Exception $e) {
                                        $fine = \App\Models\Fine::where('borrow_id', $borrow->id)->first();
                                    }
                                }
                            }
                        } else {
                            $fineAmount = 0;
                            $totalPaid = 0;
                            $pendingDays = 0;
                            $finePerDay = 0;
                            $lastPaidDate = null;
                        }
                    @endphp
                    <tr class="{{ $isOverdue ? 'table-danger' : '' }}">
                        <td class="fw-semibold">{{ $borrow->book->title }}</td>
                        <td>{{ $borrow->book->isbn }}</td>
                        <td>{{ $borrow->borrow_date->format('M d, Y') }}</td>
                        <td class="{{ $isOverdue ? 'text-danger fw-bold' : '' }}">
                            {{ $borrow->due_date->format('M d, Y') }}
                            @if($isOverdue)
                            <div class="text-xs text-danger">
                                {{ abs($borrow->days_overdue) }} day(s) overdue
                            </div>
                            @endif
                        </td>
                        <td>
                            @if($borrow->status === 'borrowed')
                                @if($isOverdue)
                                    <div>
                                        <span class="badge bg-danger mb-2">
                                            <i class="fas fa-exclamation-triangle me-1"></i>Overdue
                                        </span>
                                        
                                        @if($totalPaid > 0)
                                            <div class="text-success small mb-1">
                                                <i class="fas fa-check-circle me-1"></i>
                                                Paid: ₹{{ number_format($totalPaid, 2) }}
                                                @if($lastPaidDate)
                                                    <br><span class="text-muted">(Till: {{ $lastPaidDate->format('M d, Y') }})</span>
                                                @endif
                                            </div>
                                        @endif
                                        
                                        @if($fineAmount > 0)
                                            <div class="text-danger small mb-2">
                                                <i class="fas fa-exclamation-triangle me-1"></i>
                                                <strong>Pending: ₹{{ number_format($fineAmount, 2) }}</strong>
                                                @if($pendingDays > 0)
                                                    <br><span class="text-muted">({{ $pendingDays }} day(s) from {{ $lastPaidDate ? \Carbon\Carbon::parse($lastPaidDate)->addDay()->format('M d, Y') : \Carbon\Carbon::parse($borrow->due_date)->addDay()->format('M d, Y') }})</span>
                                                @endif
                                            </div>
                                            <div>
                                                <a href="{{ route('student.fines.index') }}" class="btn btn-sm btn-primary">
                                                    <i class="fas fa-qrcode me-1"></i>Pay ₹{{ number_format($fineAmount, 2) }}
                                                </a>
                                            </div>
                                        @else
                                            <div class="text-success small">
                                                <i class="fas fa-check-circle me-1"></i>All fines paid
                                            </div>
                                        @endif
                                    </div>
                                @else
                                    <span class="badge bg-warning text-dark">
                                        <i class="fas fa-clock me-1"></i>Borrowed
                                    </span>
                                @endif
                            @else
                                <span class="badge bg-success">
                                    <i class="fas fa-check-circle me-1"></i>{{ ucfirst($borrow->status) }}
                                </span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-5">
                            <div class="text-muted">
                                <i class="fas fa-inbox fa-3x d-block mb-3"></i>
                                <p class="mb-2">No books currently issued</p>
                                <a href="{{ route('student.books.index') }}" class="btn btn-sm btn-primary">
                                    Browse Books <i class="fas fa-arrow-right ms-2"></i>
                                </a>
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
