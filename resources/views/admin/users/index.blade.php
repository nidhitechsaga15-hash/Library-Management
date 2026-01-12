@extends('layouts.admin')

@section('title', 'Users Management')
@section('page-title', 'Users Management')

@section('content')
<div class="card-modern">
    <div class="card-header-modern">
        <h5>
            <i class="fas fa-users text-primary"></i>
            Users Section
        </h5>
        <a href="{{ route('admin.users.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Create
        </a>
    </div>
    <div class="card-body p-4">
        <div class="row">
            <div class="col-12">
                <div class="table-responsive">
                    <table id="usersTable" class="table table-modern data-table mb-0 w-100">
                <thead>
                    <tr>
                        <th style="width: 5%;">#</th>
                        <th style="width: 12%;">Name</th>
                        <th style="width: 18%;">Email</th>
                        <th style="width: 10%;">Role</th>
                        <th style="width: 25%;">ID/Course/Batch</th>
                        <th style="width: 10%;">Status</th>
                        <th style="width: 20%;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $index => $user)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td class="fw-semibold">{{ $user->name }}</td>
                        <td>{{ $user->email }}</td>
                        <td>
                            @if($user->role === 'admin')
                                <span class="badge bg-purple">Admin</span>
                            @elseif($user->role === 'staff')
                                <span class="badge bg-success">Staff</span>
                                @if($user->staff_role)
                                <div class="text-xs text-muted mt-1">{{ ucfirst($user->staff_role) }}</div>
                                @endif
                            @else
                                <span class="badge bg-info">Student</span>
                            @endif
                        </td>
                        <td>
                            @if($user->student_id)
                                <div><strong>ID:</strong> {{ $user->student_id }}</div>
                                @if($user->course)
                                <div class="text-xs text-muted">Course: {{ $user->course }}</div>
                                @endif
                                @if($user->batch)
                                <div class="text-xs text-muted">Batch: {{ $user->batch }}</div>
                                @endif
                            @elseif($user->staff_id)
                                <div><strong>Staff:</strong> {{ $user->staff_id }}</div>
                                @if($user->department)
                                <div class="text-xs text-muted">{{ $user->department }}</div>
                                @endif
                            @else
                                <span class="text-muted">N/A</span>
                            @endif
                        </td>
                        <td>
                            @if($user->is_active)
                                <span class="badge bg-success">Active</span>
                            @else
                                <span class="badge bg-danger">Inactive</span>
                            @endif
                        </td>
                        <td>
                            <div class="btn-group" role="group">
                                <a href="{{ route('admin.users.show', $user) }}" class="btn btn-sm btn-info text-white" title="View">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-sm btn-primary" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('admin.users.destroy', $user) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this user?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
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
