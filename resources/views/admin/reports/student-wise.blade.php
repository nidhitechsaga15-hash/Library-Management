@extends('layouts.admin')

@section('title', 'Student-wise Report')
@section('page-title', 'Student-wise Report')

@section('content')
<div class="card-modern">
    <div class="card-header-modern">
        <h5>
            <i class="fas fa-users text-primary"></i>
            Student-wise Report
        </h5>
    </div>
    <div class="card-body p-4">
        <!-- Search Form -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border">
                    <div class="card-body">
                        <form method="GET" action="{{ route('admin.reports.student-wise') }}" class="row g-3">
                            <div class="col-12 col-md-6">
                                <label for="name" class="form-label fw-semibold">Student Name</label>
                                <input type="text" name="name" id="name" 
                                    class="form-control form-control-lg" 
                                    value="{{ request('name') }}" 
                                    placeholder="Search by name">
                            </div>
                            <div class="col-12 col-md-6">
                                <label for="student_id" class="form-label fw-semibold">Student ID</label>
                                <input type="text" name="student_id" id="student_id" 
                                    class="form-control form-control-lg" 
                                    value="{{ request('student_id') }}" 
                                    placeholder="Search by Student ID">
                            </div>
                            <div class="col-12">
                                <div class="form-actions">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-search me-2"></i>Search
                                    </button>
                                    <a href="{{ route('admin.reports.student-wise') }}" class="btn btn-secondary">
                                        <i class="fas fa-redo me-2"></i>Reset
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Students Table -->
        <div class="row">
            <div class="col-12">
                <div class="table-responsive">
                    <table id="studentsTable" class="table table-modern data-table mb-0 w-100">
                        <thead>
                            <tr>
                                <th style="width: 5%;">#</th>
                                <th style="width: 20%;">Student</th>
                                <th style="width: 12%;">Student ID</th>
                                <th style="width: 12%;">Active Borrows</th>
                                <th style="width: 12%;">Total Borrows</th>
                                <th style="width: 15%;">Pending Fines</th>
                                <th style="width: 15%;">Total Fines</th>
                                <th style="width: 9%;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($students as $index => $student)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>
                                    <strong>{{ $student->name }}</strong>
                                    <br>
                                    <small class="text-muted">{{ $student->email }}</small>
                                </td>
                                <td>{{ $student->student_id ?? 'N/A' }}</td>
                                <td>
                                    <span class="badge bg-warning">{{ $student->active_borrows }}</span>
                                </td>
                                <td>{{ $student->total_borrows }}</td>
                                <td>
                                    <span class="text-danger fw-bold">₹{{ number_format($student->pending_fines, 2) }}</span>
                                </td>
                                <td>₹{{ number_format($student->total_fines, 2) }}</td>
                                <td>
                                    <a href="{{ route('admin.reports.student-detail', $student) }}" class="btn btn-sm btn-info" title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </a>
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
