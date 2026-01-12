@extends('layouts.staff')

@section('title', 'Return Book')
@section('page-title', 'Return Book')

@section('content')
<div class="card-modern">
    <div class="card-header-modern">
        <h5>
            <i class="fas fa-undo text-primary"></i>
            Return Book Confirmation
        </h5>
    </div>
    <div class="card-body p-4">
        <div class="row g-3 mb-4">
            <div class="col-12 col-sm-6 col-md-6 col-lg-6 col-xl-6">
                <label class="form-label text-muted small mb-1">Student</label>
                <p class="fw-semibold mb-1">{{ $borrow->user->name }}</p>
                <p class="text-muted small mb-0">{{ $borrow->user->email }}</p>
            </div>
            <div class="col-12 col-sm-6 col-md-6 col-lg-6 col-xl-6">
                <label class="form-label text-muted small mb-1">Book</label>
                <p class="fw-semibold mb-1">{{ $borrow->book->title }}</p>
                <p class="text-muted small mb-0">ISBN: {{ $borrow->book->isbn }}</p>
            </div>
            <div class="col-12 col-sm-6 col-md-6 col-lg-6 col-xl-6">
                <label class="form-label text-muted small mb-1">Issue Date</label>
                <p class="fw-semibold mb-0">{{ $borrow->borrow_date->format('M d, Y') }}</p>
            </div>
            <div class="col-12 col-sm-6 col-md-6 col-lg-6 col-xl-6">
                <label class="form-label text-muted small mb-1">Due Date</label>
                <p class="fw-semibold mb-0 {{ $borrow->due_date < now() ? 'text-danger' : '' }}">
                    {{ $borrow->due_date->format('M d, Y') }}
                </p>
            </div>
            <div class="col-12 col-sm-6 col-md-6 col-lg-6 col-xl-6">
                <label class="form-label text-muted small mb-1">Return Date</label>
                <p class="fw-semibold mb-0">{{ $returnDate->format('M d, Y') }}</p>
            </div>
            <div class="col-12 col-sm-6 col-md-6 col-lg-6 col-xl-6">
                <label class="form-label text-muted small mb-1">Days Overdue</label>
                <p class="fw-semibold mb-0 {{ $daysOverdue > 0 ? 'text-danger' : 'text-success' }}">
                    {{ $daysOverdue > 0 ? $daysOverdue . ' day(s)' : 'On Time' }}
                </p>
            </div>
        </div>

        @if($fineAmount > 0)
        <div class="alert alert-danger mb-4">
            <div class="d-flex align-items-center">
                <i class="fas fa-exclamation-triangle fa-2x me-3"></i>
                <div class="flex-grow-1">
                    <h6 class="mb-1 fw-bold">Fine Amount</h6>
                    <p class="fs-3 fw-bold mb-1">₹{{ number_format($fineAmount, 2) }}</p>
                    <p class="mb-0 small">{{ $daysOverdue }} day(s) overdue × ₹{{ number_format($fineAmount / max($daysOverdue, 1), 2) }} per day</p>
                </div>
            </div>
        </div>
        @else
        <div class="alert alert-success mb-4">
            <div class="d-flex align-items-center">
                <i class="fas fa-check-circle fa-2x me-3"></i>
                <div>
                    <h6 class="mb-1 fw-bold">Fine Amount</h6>
                    <p class="fs-3 fw-bold mb-1">₹0.00</p>
                    <p class="mb-0 small">No fine - Returned on time</p>
                </div>
            </div>
        </div>
        @endif

        <form method="POST" action="{{ route('staff.borrows.return', $borrow) }}">
            @csrf
            <div class="row mt-4">
                <div class="col-12">
                    <div class="form-actions">
                        <a href="{{ route('staff.borrows.index') }}" class="btn btn-secondary btn-lg">
                            <i class="fas fa-times me-2"></i>Cancel
                        </a>
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-check me-2"></i>Confirm Return
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
