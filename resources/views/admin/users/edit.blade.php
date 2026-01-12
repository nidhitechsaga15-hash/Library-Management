@extends('layouts.admin')

@section('title', 'Edit User')
@section('page-title', 'Edit User')

@section('content')
<div class="card-modern">
    <div class="card-header-modern">
        <h5>
            <i class="fas fa-user-edit text-primary"></i>
            Edit User
        </h5>
    </div>
    <div class="card-body p-4">
        <form method="POST" action="{{ route('admin.users.update', $user) }}">
            @csrf
            @method('PUT')

            <div class="row g-3">
                <div class="col-12 col-sm-6 col-md-6 col-lg-6 col-xl-6">
                    <label for="name" class="form-label fw-semibold">Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control form-control-lg @error('name') is-invalid @enderror" 
                        name="name" id="name" value="{{ old('name', $user->name) }}" 
                        placeholder="Enter Full Name" required>
                    @error('name')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12 col-sm-6 col-md-6 col-lg-6 col-xl-6">
                    <label for="email" class="form-label fw-semibold">Email <span class="text-danger">*</span></label>
                    <input type="email" class="form-control form-control-lg @error('email') is-invalid @enderror" 
                        name="email" id="email" value="{{ old('email', $user->email) }}" 
                        placeholder="Enter Email Address" required>
                    @error('email')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12 col-sm-6 col-md-6 col-lg-6 col-xl-6">
                    <label for="password" class="form-label fw-semibold">Password <span class="text-muted">(Leave blank to keep current)</span></label>
                    <input type="password" class="form-control form-control-lg @error('password') is-invalid @enderror" 
                        name="password" id="password" placeholder="Enter New Password">
                    @error('password')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12 col-sm-6 col-md-6 col-lg-6 col-xl-6">
                    <label for="password_confirmation" class="form-label fw-semibold">Confirm Password</label>
                    <input type="password" class="form-control form-control-lg" 
                        name="password_confirmation" id="password_confirmation" 
                        placeholder="Confirm New Password">
                </div>

                <div class="col-12 col-sm-6 col-md-6 col-lg-6 col-xl-6">
                    <label for="role" class="form-label fw-semibold">Role <span class="text-danger">*</span></label>
                    <select class="form-select form-select-lg @error('role') is-invalid @enderror" 
                        name="role" id="role" required>
                        <option value="admin" {{ old('role', $user->role) === 'admin' ? 'selected' : '' }}>Admin</option>
                        <option value="staff" {{ old('role', $user->role) === 'staff' ? 'selected' : '' }}>Staff</option>
                        <option value="student" {{ old('role', $user->role) === 'student' ? 'selected' : '' }}>Student</option>
                    </select>
                    @error('role')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12 col-sm-6 col-md-6 col-lg-6 col-xl-6">
                    <label for="phone" class="form-label fw-semibold">Phone</label>
                    <input type="text" class="form-control form-control-lg @error('phone') is-invalid @enderror" 
                        name="phone" id="phone" value="{{ old('phone', $user->phone) }}"
                        placeholder="Enter Phone Number">
                    @error('phone')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12 col-sm-6 col-md-6 col-lg-6 col-xl-6">
                    <label for="student_id" class="form-label fw-semibold">Student ID</label>
                    <input type="text" class="form-control form-control-lg @error('student_id') is-invalid @enderror" 
                        name="student_id" id="student_id" value="{{ old('student_id', $user->student_id) }}"
                        placeholder="Enter Student ID">
                    @error('student_id')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12 col-sm-6 col-md-6 col-lg-6 col-xl-6">
                    <label for="course" class="form-label fw-semibold">Course</label>
                    <input type="text" class="form-control form-control-lg @error('course') is-invalid @enderror" 
                        name="course" id="course" value="{{ old('course', $user->course) }}" 
                        placeholder="e.g., B.Tech, B.Sc, M.A">
                    @error('course')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12 col-sm-6 col-md-6 col-lg-6 col-xl-6">
                    <label for="batch" class="form-label fw-semibold">Batch</label>
                    <input type="text" class="form-control form-control-lg @error('batch') is-invalid @enderror" 
                        name="batch" id="batch" value="{{ old('batch', $user->batch) }}" 
                        placeholder="e.g., 2024, 2023-2027">
                    @error('batch')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12 col-sm-6 col-md-6 col-lg-6 col-xl-6">
                    <label for="staff_id" class="form-label fw-semibold">Staff ID</label>
                    <input type="text" class="form-control form-control-lg @error('staff_id') is-invalid @enderror" 
                        name="staff_id" id="staff_id" value="{{ old('staff_id', $user->staff_id) }}"
                        placeholder="Enter Staff ID">
                    @error('staff_id')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12 col-sm-6 col-md-6 col-lg-6 col-xl-6">
                    <label for="department" class="form-label fw-semibold">Department</label>
                    <input type="text" class="form-control form-control-lg @error('department') is-invalid @enderror" 
                        name="department" id="department" value="{{ old('department', $user->department) }}"
                        placeholder="Enter Department">
                    @error('department')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12 col-sm-6 col-md-6 col-lg-6 col-xl-6">
                    <label for="staff_role" class="form-label fw-semibold">Staff Role</label>
                    <select class="form-select form-select-lg @error('staff_role') is-invalid @enderror" 
                        name="staff_role" id="staff_role">
                        <option value="">Select Staff Role</option>
                        <option value="librarian" {{ old('staff_role', $user->staff_role) === 'librarian' ? 'selected' : '' }}>Librarian</option>
                        <option value="assistant" {{ old('staff_role', $user->staff_role) === 'assistant' ? 'selected' : '' }}>Assistant</option>
                    </select>
                    @error('staff_role')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12 col-sm-6 col-md-6 col-lg-6 col-xl-6">
                    <label for="date_of_birth" class="form-label fw-semibold">Date of Birth</label>
                    <input type="date" class="form-control form-control-lg @error('date_of_birth') is-invalid @enderror" 
                        name="date_of_birth" id="date_of_birth" value="{{ old('date_of_birth', $user->date_of_birth?->format('Y-m-d')) }}">
                    @error('date_of_birth')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12">
                    <label for="address" class="form-label fw-semibold">Address</label>
                    <textarea class="form-control @error('address') is-invalid @enderror" 
                        name="address" id="address" rows="4" placeholder="Enter Address">{{ old('address', $user->address) }}</textarea>
                    @error('address')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12">
                    <div class="form-check p-3 bg-light rounded">
                        <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', $user->is_active) ? 'checked' : '' }}>
                        <label class="form-check-label fw-semibold" for="is_active">
                            <i class="fas fa-check-circle text-success me-2"></i>Active
                        </label>
                    </div>
                </div>
            </div>

            <div class="row mt-4">
                <div class="col-12">
                    <div class="form-actions">
                        <a href="{{ route('admin.users.index') }}" class="btn btn-secondary btn-lg">
                            <i class="fas fa-times me-2"></i>Cancel
                        </a>
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-save me-2"></i>Update User
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
