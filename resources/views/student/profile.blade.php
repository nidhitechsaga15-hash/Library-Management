@extends('layouts.student')

@section('title', 'Profile')
@section('page-title', 'My Profile')

@section('content')
<div class="card-modern">
    <div class="card-header-modern">
        <h5>
            <i class="fas fa-user text-primary"></i>
            My Profile
        </h5>
    </div>
    <div class="card-body p-4">
        <!-- User Info Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="text-center mb-4">
                    <div class="user-avatar mx-auto mb-3" style="width: 100px; height: 100px; font-size: 40px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #fff; font-weight: 600;">
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    </div>
                    <h3 class="mb-1">{{ auth()->user()->name }}</h3>
                    <p class="text-muted mb-2">{{ auth()->user()->email }}</p>
                    <span class="badge bg-primary">{{ ucfirst(auth()->user()->role) }}</span>
                </div>
            </div>
        </div>

        <form method="POST" action="{{ route('student.profile.update') }}">
            @csrf
            @method('PUT')

            <div class="row g-3">
                <div class="col-12 col-md-6">
                    <label for="name" class="form-label fw-semibold">Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" id="name" value="{{ old('name', auth()->user()->name) }}" 
                        class="form-control form-control-lg @error('name') is-invalid @enderror" required>
                    @error('name')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12 col-md-6">
                    <label for="email" class="form-label fw-semibold">Email <span class="text-danger">*</span></label>
                    <input type="email" name="email" id="email" value="{{ old('email', auth()->user()->email) }}" 
                        class="form-control form-control-lg @error('email') is-invalid @enderror" required>
                    @error('email')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12 col-md-6">
                    <label for="father_name" class="form-label fw-semibold">Father's Name <span class="text-danger">*</span></label>
                    <input type="text" name="father_name" id="father_name" value="{{ old('father_name', auth()->user()->father_name) }}" 
                        class="form-control form-control-lg @error('father_name') is-invalid @enderror" required>
                    @error('father_name')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12 col-md-6">
                    <label for="mother_name" class="form-label fw-semibold">Mother's Name <span class="text-danger">*</span></label>
                    <input type="text" name="mother_name" id="mother_name" value="{{ old('mother_name', auth()->user()->mother_name) }}" 
                        class="form-control form-control-lg @error('mother_name') is-invalid @enderror" required>
                    @error('mother_name')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12 col-md-6">
                    <label for="date_of_birth" class="form-label fw-semibold">Date of Birth <span class="text-danger">*</span></label>
                    <input type="date" name="date_of_birth" id="date_of_birth" value="{{ old('date_of_birth', auth()->user()->date_of_birth) }}" 
                        class="form-control form-control-lg @error('date_of_birth') is-invalid @enderror" required>
                    @error('date_of_birth')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12 col-md-6">
                    <label for="student_id" class="form-label fw-semibold">Student ID</label>
                    <input type="text" id="student_id" value="{{ auth()->user()->student_id ?? 'N/A' }}" 
                        class="form-control form-control-lg" disabled>
                    <small class="text-muted">Student ID cannot be changed</small>
                </div>

                <div class="col-12 col-md-6">
                    <label for="phone" class="form-label fw-semibold">Phone</label>
                    <input type="text" name="phone" id="phone" value="{{ old('phone', auth()->user()->phone) }}"
                        class="form-control form-control-lg @error('phone') is-invalid @enderror">
                    @error('phone')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12 col-md-6">
                    <label for="course" class="form-label fw-semibold">Course</label>
                    <input type="text" name="course" id="course" value="{{ old('course', auth()->user()->course) }}"
                        placeholder="e.g., B.Tech, B.Sc, M.A"
                        class="form-control form-control-lg @error('course') is-invalid @enderror" disabled>
                    @error('course')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12 col-md-6">
                    <label for="branch" class="form-label fw-semibold">Branch</label>
                    <input type="text" name="branch" id="branch" value="{{ old('branch', auth()->user()->branch) }}"
                        placeholder="e.g., Computer Science, Electrical"
                        class="form-control form-control-lg @error('branch') is-invalid @enderror" disabled>
                    @error('branch')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12 col-md-6">
                    <label for="batch" class="form-label fw-semibold">Batch</label>
                    <input type="text" name="batch" id="batch" value="{{ old('batch', auth()->user()->batch) }}"
                        placeholder="e.g., 2024, 2023-2027"
                        class="form-control form-control-lg @error('batch') is-invalid @enderror" disabled>
                    @error('batch')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12 col-md-6">
                    <label for="semester" class="form-label fw-semibold">Semester</label>
                    <input type="text" name="semester" id="semester" value="{{ old('semester', auth()->user()->semester) }}"
                        placeholder="e.g., 1st Sem, 2nd Sem"
                        class="form-control form-control-lg @error('semester') is-invalid @enderror" disabled>
                    @error('semester')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12 col-md-6">
                    <label for="year" class="form-label fw-semibold">Year</label>
                    <input type="text" name="year" id="year" value="{{ old('year', auth()->user()->year) }}"
                        placeholder="e.g., 1st Year, 2nd Year"
                        class="form-control form-control-lg @error('year') is-invalid @enderror" disabled>
                    @error('year')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12">
                    <label for="address" class="form-label fw-semibold">Address <span class="text-danger">*</span></label>
                    <textarea name="address" id="address" rows="3"
                        class="form-control form-control-lg @error('address') is-invalid @enderror" required>{{ old('address', auth()->user()->address) }}</textarea>
                    @error('address')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <!-- Statistics -->
            <div class="border-top pt-4 mt-4">
                <h5 class="mb-4">
                    <i class="fas fa-chart-bar me-2 text-primary"></i>Account Statistics
                </h5>
                <div class="row g-3">
                    <div class="col-12 col-md-6">
                        <div class="p-3 border rounded bg-light">
                            <p class="text-muted mb-1 small">Issued Books Count</p>
                            <h3 class="mb-0 fw-bold">{{ auth()->user()->borrows()->where('status', 'borrowed')->count() }}</h3>
                        </div>
                    </div>
                    <div class="col-12 col-md-6">
                        <div class="p-3 border rounded bg-light">
                            <p class="text-muted mb-1 small">Fine Pending</p>
                            <h3 class="mb-0 fw-bold text-danger">₹{{ number_format(auth()->user()->fines()->where('status', 'pending')->sum('amount'), 2) }}</h3>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Change Password -->
            <div class="border-top pt-4 mt-4">
                <h5 class="mb-4">
                    <i class="fas fa-key me-2 text-primary"></i>Change Password
                </h5>
                <div class="row g-3">
                    <div class="col-12 col-md-6">
                        <label for="password" class="form-label fw-semibold">New Password</label>
                        <input type="password" name="password" id="password"
                            class="form-control form-control-lg @error('password') is-invalid @enderror">
                        <small class="text-muted">Leave blank to keep current password</small>
                        @error('password')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12 col-md-6">
                        <label for="password_confirmation" class="form-label fw-semibold">Confirm Password</label>
                        <input type="password" name="password_confirmation" id="password_confirmation"
                            class="form-control form-control-lg">
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="row mt-4">
                <div class="col-12">
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-save me-2"></i>Update Profile
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
