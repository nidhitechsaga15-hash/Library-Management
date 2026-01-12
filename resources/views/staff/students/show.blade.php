@extends('layouts.staff')

@section('title', 'Student Details')
@section('page-title', 'Student Details')

@section('content')
<div class="card-modern">
    <div class="card-header-modern">
        <h5>
            <i class="fas fa-user text-primary"></i>
            Student Details
        </h5>
    </div>
    <div class="card-body p-4">
        <div class="row mb-4">
            <div class="col-12">
                <h3 class="fw-bold mb-2">{{ $user->name }}</h3>
                <p class="text-muted mb-0">{{ $user->email }}</p>
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-12 col-sm-6 col-md-6 col-lg-6 col-xl-6">
                <label class="form-label text-muted small mb-1">Student ID</label>
                <p class="fw-semibold mb-0">{{ $user->student_id ?? 'N/A' }}</p>
            </div>
            <div class="col-12 col-sm-6 col-md-6 col-lg-6 col-xl-6">
                <label class="form-label text-muted small mb-1">Phone</label>
                <p class="fw-semibold mb-0">{{ $user->phone ?? 'N/A' }}</p>
            </div>
            <div class="col-12 col-sm-6 col-md-6 col-lg-6 col-xl-6">
                <label class="form-label text-muted small mb-1">Status</label>
                <p class="mb-0">
                    <span class="badge bg-{{ $user->is_active ? 'success' : 'danger' }}">
                        {{ $user->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </p>
            </div>
            <div class="col-12 col-sm-6 col-md-6 col-lg-6 col-xl-6">
                <label class="form-label text-muted small mb-1">Total Borrows</label>
                <p class="fw-semibold mb-0">{{ $user->borrows->count() }}</p>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0">Active Borrows ({{ $activeBorrows->count() }})</h5>
                    <a href="{{ route('staff.students.issue-history', $user) }}" class="btn btn-sm btn-outline-primary">
                        View Complete History <i class="fas fa-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>
        </div>

        @if($activeBorrows->count() > 0)
        <div class="row">
            <div class="col-12">
                <div class="table-responsive">
                    <table class="table table-modern mb-0">
                        <thead>
                            <tr>
                                <th>Book</th>
                                <th>Issue Date</th>
                                <th>Due Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($activeBorrows as $borrow)
                            <tr>
                                <td class="fw-semibold">{{ $borrow->book->title }}</td>
                                <td>{{ $borrow->borrow_date->format('M d, Y') }}</td>
                                <td class="{{ $borrow->due_date < now() ? 'text-danger fw-bold' : '' }}">
                                    {{ $borrow->due_date->format('M d, Y') }}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @else
        <div class="row">
            <div class="col-12">
                <p class="text-muted mb-0">No active borrows</p>
            </div>
        </div>
        @endif

        @if($user->fines->count() > 0)
        <div class="row mt-4">
            <div class="col-12">
                <h5 class="mb-3">Fines</h5>
                <div class="table-responsive">
                    <table class="table table-modern mb-0">
                        <thead>
                            <tr>
                                <th>Amount</th>
                                <th>Reason</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($user->fines as $fine)
                            <tr>
                                <td class="fw-bold text-danger">₹{{ number_format($fine->amount, 2) }}</td>
                                <td>{{ $fine->reason }}</td>
                                <td>
                                    <span class="badge bg-{{ $fine->status === 'paid' ? 'success' : 'warning' }}">
                                        {{ ucfirst($fine->status) }}
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
