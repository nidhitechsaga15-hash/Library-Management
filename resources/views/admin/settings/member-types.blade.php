@extends('layouts.admin')

@section('title', 'Member Types Settings')
@section('page-title', 'Member Types Settings')

@section('content')
<div class="card-modern">
    <div class="card-header-modern">
        <h5>
            <i class="fas fa-users-cog text-primary"></i>
            Member Types Configuration
        </h5>
        <a href="{{ route('admin.settings.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Back to Settings
        </a>
    </div>
    <div class="card-body p-4">
        <form method="POST" action="{{ route('admin.settings.member-types.update') }}">
            @csrf
            <div class="row mb-3">
                <div class="col-12">
                    <h6 class="fw-bold mb-3">Configure Issue Limits and Duration for Each Member Type</h6>
                    <p class="text-muted">Set different limits and durations for students, faculty, and staff members.</p>
                </div>
            </div>

            @php
                $memberTypes = ['student', 'faculty', 'staff'];
                $defaults = [
                    'student' => ['max_books' => 2, 'duration' => 14, 'fine' => 10.00],
                    'faculty' => ['max_books' => 5, 'duration' => 30, 'fine' => 15.00],
                    'staff' => ['max_books' => 3, 'duration' => 21, 'fine' => 12.00],
                ];
            @endphp

            @foreach($memberTypes as $type)
                @php
                    $setting = $memberTypeSettings->get($type);
                    $maxBooks = $setting ? $setting->max_books_allowed : $defaults[$type]['max_books'];
                    $duration = $setting ? $setting->issue_duration_days : $defaults[$type]['duration'];
                    $fine = $setting ? $setting->fine_per_day : $defaults[$type]['fine'];
                    $isActive = $setting ? $setting->is_active : true;
                    $description = $setting ? $setting->description : null;
                @endphp
                <div class="card mb-4">
                    <div class="card-header bg-light">
                        <h6 class="mb-0 fw-bold text-capitalize">
                            <i class="fas fa-user-tag me-2"></i>{{ ucfirst($type) }} Settings
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Max Books Allowed <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" 
                                    name="member_types[{{ $type }}][max_books_allowed]" 
                                    value="{{ old("member_types.$type.max_books_allowed", $maxBooks) }}" 
                                    min="1" max="20" required>
                                <small class="form-text text-muted">Maximum number of books this member type can borrow simultaneously</small>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Issue Duration (Days) <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" 
                                    name="member_types[{{ $type }}][issue_duration_days]" 
                                    value="{{ old("member_types.$type.issue_duration_days", $duration) }}" 
                                    min="1" max="365" required>
                                <small class="form-text text-muted">Default number of days books can be kept</small>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Fine Per Day (₹) <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" class="form-control" 
                                    name="member_types[{{ $type }}][fine_per_day]" 
                                    value="{{ old("member_types.$type.fine_per_day", $fine) }}" 
                                    min="0" required>
                                <small class="form-text text-muted">Fine amount per overdue day</small>
                            </div>
                            <div class="col-md-12">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" 
                                        name="member_types[{{ $type }}][is_active]" value="1"
                                        {{ old("member_types.$type.is_active", $isActive) ? 'checked' : '' }}>
                                    <label class="form-check-label">Active (Enable this member type)</label>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Description</label>
                                <textarea class="form-control" 
                                    name="member_types[{{ $type }}][description]" 
                                    rows="2">{{ old("member_types.$type.description", $description) }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach

            <div class="row mt-4">
                <div class="col-12">
                    <div class="form-actions">
                        <a href="{{ route('admin.settings.index') }}" class="btn btn-secondary btn-lg">
                            <i class="fas fa-times me-2"></i>Cancel
                        </a>
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-save me-2"></i>Save Member Type Settings
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection


