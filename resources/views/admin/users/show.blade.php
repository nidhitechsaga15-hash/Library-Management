@extends('layouts.admin')

@section('title', 'User Details')
@section('page-title', 'User Details')

@section('content')
<div class="card-modern">
    <div class="card-header-modern">
        <h5>
            <i class="fas fa-user text-primary"></i>
            User Details
        </h5>
        <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-primary">
            <i class="fas fa-edit me-2"></i>Edit
        </a>
    </div>
    <div class="card-body p-4">
        <!-- User Info Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="text-center mb-4">
                    <div class="user-avatar mx-auto mb-3" style="width: 100px; height: 100px; font-size: 40px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #fff; font-weight: 600;">
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    </div>
                    <h3 class="mb-1">{{ $user->name }}</h3>
                    <p class="text-muted mb-2">{{ $user->email }}</p>
                </div>
            </div>
        </div>

        <!-- User Details Grid -->
        <div class="row g-3 mb-4">
            <div class="col-12 col-sm-6 col-md-6 col-lg-4 col-xl-4">
                <div class="p-3 border rounded">
                    <p class="text-muted mb-1 small">Role</p>
                    <p class="mb-0">
                        @if($user->role === 'admin')
                            <span class="badge bg-purple">Admin</span>
                        @elseif($user->role === 'staff')
                            <span class="badge bg-success">Staff</span>
                        @else
                            <span class="badge bg-info">Student</span>
                        @endif
                    </p>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-md-6 col-lg-4 col-xl-4">
                <div class="p-3 border rounded">
                    <p class="text-muted mb-1 small">Status</p>
                    <p class="mb-0">
                        @if($user->is_active)
                            <span class="badge bg-success">Active</span>
                        @else
                            <span class="badge bg-danger">Inactive</span>
                        @endif
                    </p>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-md-6 col-lg-4 col-xl-4">
                <div class="p-3 border rounded">
                    <p class="text-muted mb-1 small">Phone</p>
                    <p class="mb-0 fw-semibold">{{ $user->phone ?? 'N/A' }}</p>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-md-6 col-lg-4 col-xl-4">
                <div class="p-3 border rounded">
                    <p class="text-muted mb-1 small">Student ID</p>
                    <p class="mb-0 fw-semibold">{{ $user->student_id ?? 'N/A' }}</p>
                </div>
            </div>
            @if($user->role === 'student')
            <div class="col-12 col-sm-6 col-md-6 col-lg-4 col-xl-4">
                <div class="p-3 border rounded">
                    <p class="text-muted mb-1 small">Course</p>
                    <p class="mb-0 fw-semibold">{{ $user->course ?? 'N/A' }}</p>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-md-6 col-lg-4 col-xl-4">
                <div class="p-3 border rounded">
                    <p class="text-muted mb-1 small">Batch</p>
                    <p class="mb-0 fw-semibold">{{ $user->batch ?? 'N/A' }}</p>
                </div>
            </div>
            @endif
            <div class="col-12 col-sm-6 col-md-6 col-lg-4 col-xl-4">
                <div class="p-3 border rounded">
                    <p class="text-muted mb-1 small">Staff ID</p>
                    <p class="mb-0 fw-semibold">{{ $user->staff_id ?? 'N/A' }}</p>
                </div>
            </div>
            @if($user->role === 'staff')
            <div class="col-12 col-sm-6 col-md-6 col-lg-4 col-xl-4">
                <div class="p-3 border rounded">
                    <p class="text-muted mb-1 small">Department</p>
                    <p class="mb-0 fw-semibold">{{ $user->department ?? 'N/A' }}</p>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-md-6 col-lg-4 col-xl-4">
                <div class="p-3 border rounded">
                    <p class="text-muted mb-1 small">Staff Role</p>
                    <p class="mb-0">
                        @if($user->staff_role)
                            <span class="badge bg-{{ $user->staff_role === 'librarian' ? 'purple' : 'info' }}">
                                {{ ucfirst($user->staff_role) }}
                            </span>
                        @else
                            <span class="text-muted">N/A</span>
                        @endif
                    </p>
                </div>
            </div>
            @endif
            <div class="col-12 col-sm-6 col-md-6 col-lg-4 col-xl-4">
                <div class="p-3 border rounded">
                    <p class="text-muted mb-1 small">Date of Birth</p>
                    <p class="mb-0 fw-semibold">{{ $user->date_of_birth ? $user->date_of_birth->format('F d, Y') : 'N/A' }}</p>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-md-6 col-lg-4 col-xl-4">
                <div class="p-3 border rounded">
                    <p class="text-muted mb-1 small">Total Borrows</p>
                    <p class="mb-0 fw-semibold">{{ $user->borrows->count() }}</p>
                </div>
            </div>
            @if($user->address)
            <div class="col-12">
                <div class="p-3 border rounded">
                    <p class="text-muted mb-1 small">Address</p>
                    <p class="mb-0 fw-semibold">{{ $user->address }}</p>
                </div>
            </div>
            @endif
        </div>

        <!-- Borrow History -->
        <div class="border-top pt-4 mt-4">
            <h5 class="mb-4">
                <i class="fas fa-book me-2 text-primary"></i>Borrow History
            </h5>
            @if($user->borrows->count() > 0)
            <div class="row">
                <div class="col-12">
                    <div class="table-responsive">
                        <table id="borrowHistoryTable" class="table table-modern data-table mb-0 w-100">
                            <thead>
                                <tr>
                                    <th style="width: 5%;">#</th>
                                    <th style="width: 30%;">Book</th>
                                    <th style="width: 15%;">Borrow Date</th>
                                    <th style="width: 15%;">Due Date</th>
                                    <th style="width: 15%;">Return Date</th>
                                    <th style="width: 10%;">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($user->borrows as $index => $borrow)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td class="fw-semibold">{{ $borrow->book->title }}</td>
                                    <td>{{ $borrow->borrow_date->format('M d, Y') }}</td>
                                    <td>{{ $borrow->due_date->format('M d, Y') }}</td>
                                    <td>
                                        @if($borrow->return_date)
                                            {{ $borrow->return_date->format('M d, Y') }}
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($borrow->status === 'borrowed')
                                            <span class="badge bg-warning text-dark">Borrowed</span>
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
            @else
            <p class="text-muted text-center py-4">No borrow history.</p>
            @endif
        </div>

        <!-- Fines Section -->
        @if($user->fines->count() > 0)
        <div class="border-top pt-4 mt-4">
            <h5 class="mb-4">
                <i class="fas fa-dollar-sign me-2 text-primary"></i>Fines
            </h5>
            <div class="row">
                <div class="col-12">
                    <div class="table-responsive">
                        <table id="finesTable" class="table table-modern data-table mb-0 w-100">
                            <thead>
                                <tr>
                                    <th style="width: 5%;">#</th>
                                    <th style="width: 15%;">Amount</th>
                                    <th style="width: 30%;">Reason</th>
                                    <th style="width: 15%;">Status</th>
                                    <th style="width: 15%;">Paid Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($user->fines as $index => $fine)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td class="fw-bold text-danger">₹{{ number_format($fine->amount, 2) }}</td>
                                    <td>{{ $fine->reason ?? 'N/A' }}</td>
                                    <td>
                                        @if($fine->status === 'paid')
                                            <span class="badge bg-success">Paid</span>
                                        @else
                                            <span class="badge bg-danger">Pending</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($fine->paid_date)
                                            {{ $fine->paid_date->format('M d, Y') }}
                                        @else
                                            <span class="text-muted">N/A</span>
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
        @endif
    </div>
</div>
@endsection
