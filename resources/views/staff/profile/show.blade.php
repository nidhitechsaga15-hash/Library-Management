@extends('layouts.staff')

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
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex align-items-center mb-4">
                    <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 80px; height: 80px; font-size: 32px; font-weight: bold;">
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    </div>
                    <div>
                        <h4 class="fw-bold mb-1">{{ auth()->user()->name }}</h4>
                        <p class="text-muted mb-1">{{ auth()->user()->email }}</p>
                        <span class="badge bg-success">
                            {{ ucfirst(auth()->user()->role) }}
                            @if(auth()->user()->staff_role)
                                - {{ ucfirst(auth()->user()->staff_role) }}
                            @endif
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <form method="POST" action="{{ route('staff.profile.update') }}">
            @csrf
            @method('PUT')

            <div class="row g-3">
                <div class="col-12 col-sm-6 col-md-6 col-lg-6 col-xl-6">
                    <label for="name" class="form-label fw-semibold">Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" id="name" value="{{ old('name', auth()->user()->name) }}" required
                        class="form-control form-control-lg @error('name') is-invalid @enderror">
                    @error('name')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12 col-sm-6 col-md-6 col-lg-6 col-xl-6">
                    <label for="email" class="form-label fw-semibold">Email <span class="text-danger">*</span></label>
                    <input type="email" name="email" id="email" value="{{ old('email', auth()->user()->email) }}" required
                        class="form-control form-control-lg @error('email') is-invalid @enderror">
                    @error('email')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12 col-sm-6 col-md-6 col-lg-6 col-xl-6">
                    <label for="father_name" class="form-label fw-semibold">Father's Name <span class="text-danger">*</span></label>
                    <input type="text" name="father_name" id="father_name" value="{{ old('father_name', auth()->user()->father_name) }}" required
                        class="form-control form-control-lg @error('father_name') is-invalid @enderror">
                    @error('father_name')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12 col-sm-6 col-md-6 col-lg-6 col-xl-6">
                    <label for="mother_name" class="form-label fw-semibold">Mother's Name <span class="text-danger">*</span></label>
                    <input type="text" name="mother_name" id="mother_name" value="{{ old('mother_name', auth()->user()->mother_name) }}" required
                        class="form-control form-control-lg @error('mother_name') is-invalid @enderror">
                    @error('mother_name')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12 col-sm-6 col-md-6 col-lg-6 col-xl-6">
                    <label for="date_of_birth" class="form-label fw-semibold">Date of Birth <span class="text-danger">*</span></label>
                    <input type="date" name="date_of_birth" id="date_of_birth" value="{{ old('date_of_birth', auth()->user()->date_of_birth) }}" required
                        class="form-control form-control-lg @error('date_of_birth') is-invalid @enderror">
                    @error('date_of_birth')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12 col-sm-6 col-md-6 col-lg-6 col-xl-6">
                    <label for="phone" class="form-label fw-semibold">Phone</label>
                    <input type="text" name="phone" id="phone" value="{{ old('phone', auth()->user()->phone) }}"
                        class="form-control form-control-lg @error('phone') is-invalid @enderror">
                    @error('phone')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12 col-sm-6 col-md-6 col-lg-6 col-xl-6">
                    <label for="staff_id" class="form-label fw-semibold">Staff ID</label>
                    <input type="text" id="staff_id" value="{{ auth()->user()->staff_id ?? 'N/A' }}" disabled
                        class="form-control form-control-lg bg-light">
                    <small class="form-text text-muted">Staff ID cannot be changed</small>
                </div>

                <div class="col-12 col-sm-6 col-md-6 col-lg-6 col-xl-6">
                    <label for="department" class="form-label fw-semibold">Department</label>
                    <input type="text" id="department" value="{{ auth()->user()->department ?? 'N/A' }}" disabled
                        class="form-control form-control-lg bg-light">
                    <small class="form-text text-muted">Department cannot be changed</small>
                </div>

                <div class="col-12">
                    <label for="address" class="form-label fw-semibold">Address <span class="text-danger">*</span></label>
                    <textarea name="address" id="address" rows="3" required
                        class="form-control form-control-lg @error('address') is-invalid @enderror">{{ old('address', auth()->user()->address) }}</textarea>
                    @error('address')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="row mt-4">
                <div class="col-12">
                    <div class="border-top pt-4">
                        <h5 class="fw-bold mb-3">Change Password</h5>
                        <div class="row g-3">
                            <div class="col-12 col-sm-6 col-md-6 col-lg-6 col-xl-6">
                                <label for="password" class="form-label fw-semibold">New Password</label>
                                <input type="password" name="password" id="password"
                                    class="form-control form-control-lg @error('password') is-invalid @enderror">
                                <small class="form-text text-muted">Leave blank to keep current password</small>
                                @error('password')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12 col-sm-6 col-md-6 col-lg-6 col-xl-6">
                                <label for="password_confirmation" class="form-label fw-semibold">Confirm Password</label>
                                <input type="password" name="password_confirmation" id="password_confirmation"
                                    class="form-control form-control-lg">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

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
