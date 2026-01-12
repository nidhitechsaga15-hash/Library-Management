@extends('layouts.admin')

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
        <form method="POST" action="{{ route('admin.profile.update-password') }}">
            @csrf

            <div class="row g-3">
                <div class="col-12">
                    <label for="current_password" class="form-label fw-semibold">Current Password <span class="text-danger">*</span></label>
                    <input type="password" class="form-control form-control-lg @error('current_password') is-invalid @enderror" 
                        name="current_password" id="current_password" 
                        placeholder="Enter Current Password" required>
                    @error('current_password')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12 col-sm-6 col-md-6 col-lg-6 col-xl-6">
                    <label for="password" class="form-label fw-semibold">New Password <span class="text-danger">*</span></label>
                    <input type="password" class="form-control form-control-lg @error('password') is-invalid @enderror" 
                        name="password" id="password" 
                        placeholder="Enter New Password" required>
                    @error('password')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12 col-sm-6 col-md-6 col-lg-6 col-xl-6">
                    <label for="password_confirmation" class="form-label fw-semibold">Confirm New Password <span class="text-danger">*</span></label>
                    <input type="password" class="form-control form-control-lg" 
                        name="password_confirmation" id="password_confirmation" 
                        placeholder="Confirm New Password" required>
                </div>
            </div>

            <div class="row mt-4">
                <div class="col-12">
                    <div class="form-actions">
                        <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary btn-lg">
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

