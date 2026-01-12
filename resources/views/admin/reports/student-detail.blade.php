@extends('layouts.admin')

@section('title', 'Student Detail Report')
@section('page-title', 'Student Detail Report - ' . $user->name)

@section('content')
<!-- Student Information Card -->
<div class="card-modern mb-4">
    <div class="card-header-modern">
        <h5>
            <i class="fas fa-user text-primary"></i>
            Student Information
        </h5>
        <a href="{{ route('admin.reports.student-wise') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Back
        </a>
    </div>
    <div class="card-body p-4">
        <div class="row g-3">
            <div class="col-12 col-sm-6 col-md-6 col-lg-4 col-xl-3">
                <div class="p-3 border rounded">
                    <p class="text-muted mb-1 small">Name</p>
                    <p class="mb-0 fw-bold">{{ $user->name }}</p>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-md-6 col-lg-4 col-xl-3">
                <div class="p-3 border rounded">
                    <p class="text-muted mb-1 small">Student ID</p>
                    <p class="mb-0 fw-bold">{{ $user->student_id ?? 'N/A' }}</p>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-md-6 col-lg-4 col-xl-3">
                <div class="p-3 border rounded">
                    <p class="text-muted mb-1 small">Email</p>
                    <p class="mb-0">{{ $user->email }}</p>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-md-6 col-lg-4 col-xl-3">
                <div class="p-3 border rounded">
                    <p class="text-muted mb-1 small">Phone</p>
                    <p class="mb-0">{{ $user->phone ?? 'N/A' }}</p>
                </div>
            </div>
            @if($user->course)
            <div class="col-12 col-sm-6 col-md-6 col-lg-4 col-xl-3">
                <div class="p-3 border rounded">
                    <p class="text-muted mb-1 small">Course</p>
                    <p class="mb-0">{{ $user->course }}</p>
                </div>
            </div>
            @endif
            @if($user->batch)
            <div class="col-12 col-sm-6 col-md-6 col-lg-4 col-xl-3">
                <div class="p-3 border rounded">
                    <p class="text-muted mb-1 small">Batch</p>
                    <p class="mb-0">{{ $user->batch }}</p>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Active Borrows Card -->
<div class="card-modern mb-4">
    <div class="card-header-modern">
        <h5>
            <i class="fas fa-book text-primary"></i>
            Active Borrows ({{ $activeBorrows->count() }})
        </h5>
    </div>
    <div class="card-body p-4">
        <div class="row">
            <div class="col-12">
                <div class="table-responsive">
                    <table id="activeBorrowsTable" class="table table-modern data-table mb-0 w-100">
                        <thead>
                            <tr>
                                <th style="width: 5%;">#</th>
                                <th style="width: 40%;">Book</th>
                                <th style="width: 20%;">Issue Date</th>
                                <th style="width: 20%;">Due Date</th>
                                <th style="width: 15%;">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($activeBorrows as $index => $borrow)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>
                                    <strong>{{ $borrow->book->title }}</strong>
                                    <br>
                                    <small class="text-muted">ISBN: {{ $borrow->book->isbn }}</small>
                                </td>
                                <td>{{ $borrow->borrow_date->format('M d, Y') }}</td>
                                <td>
                                    <span class="{{ $borrow->due_date < now() ? 'text-danger fw-bold' : '' }}">
                                        {{ $borrow->due_date->format('M d, Y') }}
                                    </span>
                                </td>
                                <td>
                                    @if($borrow->due_date < now())
                                        <span class="badge bg-danger">Overdue</span>
                                    @else
                                        <span class="badge bg-success">Active</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">No active borrows</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Returned Borrows Card -->
@if($returnedBorrows->count() > 0)
<div class="card-modern mb-4">
    <div class="card-header-modern">
        <h5>
            <i class="fas fa-check-circle text-success"></i>
            Returned Borrows ({{ $returnedBorrows->count() }})
        </h5>
    </div>
    <div class="card-body p-4">
        <div class="row">
            <div class="col-12">
                <div class="table-responsive">
                    <table id="returnedBorrowsTable" class="table table-modern data-table mb-0 w-100">
                        <thead>
                            <tr>
                                <th style="width: 5%;">#</th>
                                <th style="width: 40%;">Book</th>
                                <th style="width: 20%;">Issue Date</th>
                                <th style="width: 20%;">Return Date</th>
                                <th style="width: 15%;">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($returnedBorrows as $index => $borrow)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>
                                    <strong>{{ $borrow->book->title }}</strong>
                                    <br>
                                    <small class="text-muted">ISBN: {{ $borrow->book->isbn }}</small>
                                </td>
                                <td>{{ $borrow->borrow_date->format('M d, Y') }}</td>
                                <td>{{ $borrow->return_date ? $borrow->return_date->format('M d, Y') : 'N/A' }}</td>
                                <td>
                                    <span class="badge bg-secondary">Returned</span>
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
@endif

<!-- Pending Fines Card -->
@if($pendingFines->count() > 0)
<div class="card-modern mb-4">
    <div class="card-header-modern">
        <h5>
            <i class="fas fa-exclamation-triangle text-warning"></i>
            Pending Fines ({{ $pendingFines->count() }})
        </h5>
    </div>
    <div class="card-body p-4">
        <div class="row">
            <div class="col-12">
                <div class="table-responsive">
                    <table id="pendingFinesTable" class="table table-modern data-table mb-0 w-100">
                        <thead>
                            <tr>
                                <th style="width: 5%;">#</th>
                                <th style="width: 40%;">Book</th>
                                <th style="width: 30%;">Reason</th>
                                <th style="width: 15%;">Amount</th>
                                <th style="width: 10%;">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pendingFines as $index => $fine)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>
                                    @if($fine->borrow && $fine->borrow->book)
                                        <strong>{{ $fine->borrow->book->title }}</strong>
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </td>
                                <td>{{ $fine->reason }}</td>
                                <td>
                                    <span class="text-danger fw-bold">₹{{ number_format($fine->amount, 2) }}</span>
                                </td>
                                <td>
                                    <span class="badge bg-warning">Pending</span>
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
@endif

<!-- Paid Fines Card -->
@if($paidFines->count() > 0)
<div class="card-modern">
    <div class="card-header-modern">
        <h5>
            <i class="fas fa-check-circle text-success"></i>
            Paid Fines ({{ $paidFines->count() }})
        </h5>
    </div>
    <div class="card-body p-4">
        <div class="row">
            <div class="col-12">
                <div class="table-responsive">
                    <table id="paidFinesTable" class="table table-modern data-table mb-0 w-100">
                        <thead>
                            <tr>
                                <th style="width: 5%;">#</th>
                                <th style="width: 40%;">Book</th>
                                <th style="width: 30%;">Reason</th>
                                <th style="width: 15%;">Amount</th>
                                <th style="width: 10%;">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($paidFines as $index => $fine)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>
                                    @if($fine->borrow && $fine->borrow->book)
                                        <strong>{{ $fine->borrow->book->title }}</strong>
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </td>
                                <td>{{ $fine->reason }}</td>
                                <td>
                                    <span class="text-success fw-bold">₹{{ number_format($fine->amount, 2) }}</span>
                                </td>
                                <td>
                                    <span class="badge bg-success">Paid</span>
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
@endif
@endsection
