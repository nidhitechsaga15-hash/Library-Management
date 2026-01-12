@extends('layouts.student')

@section('title', 'Change Password')
@section('page-title', 'Change Password')

@section('content')
<div class="card-modern">
    <div class="card-header-modern">
        <h5>
            <i class="fas fa-key text-primary"></i>
            Change Password
        </h5>
    </div>
    <div class="card-body p-4">
        <form method="POST" action="{{ route('student.profile.update-password') }}">
            @csrf
            @method('PUT')

            <div class="row g-3">
                <div class="col-12 col-md-6">
                    <label for="current_password" class="form-label fw-semibold">Current Password <span class="text-danger">*</span></label>
                    <input type="password" name="current_password" id="current_password"
                        class="form-control form-control-lg @error('current_password') is-invalid @enderror" required>
                    @error('current_password')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="row g-3 mt-3">
                <div class="col-12 col-md-6">
                    <label for="password" class="form-label fw-semibold">New Password <span class="text-danger">*</span></label>
                    <input type="password" name="password" id="password"
                        class="form-control form-control-lg @error('password') is-invalid @enderror" required>
                    @error('password')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-12 col-md-6">
                    <label for="password_confirmation" class="form-label fw-semibold">Confirm New Password <span class="text-danger">*</span></label>
                    <input type="password" name="password_confirmation" id="password_confirmation"
                        class="form-control form-control-lg" required>
                </div>
            </div>

            <div class="row mt-4">
                <div class="col-12">
                    <div class="form-actions">
                        <a href="{{ route('student.dashboard') }}" class="btn btn-secondary btn-lg">
                            <i class="fas fa-times me-2"></i>Cancel
                        </a>
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-save me-2"></i>Update Password
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection



