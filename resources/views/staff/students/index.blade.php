@extends('layouts.staff')

@section('title', 'Students')
@section('page-title', 'Students')

@section('content')
<div class="card-modern">
    <div class="card-header-modern">
        <h5>
            <i class="fas fa-users text-primary"></i>
            Students Section
        </h5>
    </div>
    <div class="card-body p-4">
        <!-- Search -->
        <form method="GET" action="{{ route('staff.students.index') }}" class="mb-4">
            <div class="row g-3">
                <div class="col-12 col-sm-8 col-md-9">
                    <input type="text" name="search" value="{{ request('search') }}" 
                        placeholder="Search by name, email, or student ID..."
                        class="form-control form-control-lg">
                </div>
                <div class="col-12 col-sm-4 col-md-3">
                    <button type="submit" class="btn btn-primary btn-lg w-100">
                        <i class="fas fa-search me-2"></i>Search
                    </button>
                </div>
            </div>
        </form>

        <div class="row">
            <div class="col-12">
                <div class="table-responsive">
                    <table id="studentsTable" class="table table-modern data-table mb-0 w-100">
                <thead>
                    <tr>
                        <th style="width: 5%;">#</th>
                        <th style="width: 20%;">Name</th>
                        <th style="width: 20%;">Email</th>
                        <th style="width: 15%;">Student ID</th>
                        <th style="width: 15%;">Phone</th>
                        <th style="width: 10%;">Status</th>
                        <th style="width: 15%;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($students as $index => $student)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td class="fw-semibold">{{ $student->name }}</td>
                        <td>{{ $student->email }}</td>
                        <td>{{ $student->student_id ?? 'N/A' }}</td>
                        <td>{{ $student->phone ?? 'N/A' }}</td>
                        <td>
                            @if($student->is_active)
                                <span class="badge bg-success">Active</span>
                            @else
                                <span class="badge bg-danger">Inactive</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('staff.students.show', $student) }}" class="btn btn-sm btn-info text-white" title="View">
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
