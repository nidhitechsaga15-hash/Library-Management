@extends('layouts.admin')

@section('title', 'Edit Library')
@section('page-title', 'Edit Library')

@section('content')
<div class="card-modern">
    <div class="card-header-modern">
        <h5>
            <i class="fas fa-building text-primary"></i>
            Edit Library
        </h5>
    </div>
    <div class="card-body p-4">
        <form method="POST" action="{{ route('admin.libraries.update', $library) }}">
            @csrf
            @method('PUT')

            <div class="row g-3">
                <div class="col-12 col-sm-6">
                    <label for="name" class="form-label fw-semibold">Library Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control form-control-lg @error('name') is-invalid @enderror" 
                        name="name" id="name" value="{{ old('name', $library->name) }}" 
                        placeholder="Enter Library Name" required>
                    @error('name')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12 col-sm-6">
                    <label for="code" class="form-label fw-semibold">Library Code <span class="text-danger">*</span></label>
                    <input type="text" class="form-control form-control-lg @error('code') is-invalid @enderror" 
                        name="code" id="code" value="{{ old('code', $library->code) }}" 
                        placeholder="e.g., LIB001" required>
                    @error('code')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12">
                    <label for="address" class="form-label fw-semibold">Address</label>
                    <textarea class="form-control @error('address') is-invalid @enderror" 
                        name="address" id="address" rows="3" placeholder="Enter library address...">{{ old('address', $library->address) }}</textarea>
                    @error('address')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12 col-sm-6">
                    <label for="phone" class="form-label fw-semibold">Phone</label>
                    <input type="text" class="form-control form-control-lg @error('phone') is-invalid @enderror" 
                        name="phone" id="phone" value="{{ old('phone', $library->phone) }}"
                        placeholder="Enter phone number">
                    @error('phone')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12 col-sm-6">
                    <label for="email" class="form-label fw-semibold">Email</label>
                    <input type="email" class="form-control form-control-lg @error('email') is-invalid @enderror" 
                        name="email" id="email" value="{{ old('email', $library->email) }}"
                        placeholder="Enter email address">
                    @error('email')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12">
                    <label for="description" class="form-label fw-semibold">Description</label>
                    <textarea class="form-control @error('description') is-invalid @enderror" 
                        name="description" id="description" rows="4" placeholder="Enter library description...">{{ old('description', $library->description) }}</textarea>
                    @error('description')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12">
                    <label class="form-label fw-semibold">Status</label>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="is_active" id="is_active" 
                            value="1" {{ old('is_active', $library->is_active) ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_active">
                            Active Library
                        </label>
                    </div>
                </div>

                <div class="col-12 col-sm-6 col-md-6 col-lg-6 col-xl-6">
                    <label for="staff_id" class="form-label fw-semibold">Assign Staff</label>
                    <select class="form-select form-select-lg @error('staff_id') is-invalid @enderror" 
                        name="staff_id" id="staff_id">
                        <option value="">Select Staff</option>
                        @foreach($staff as $s)
                            <option value="{{ $s->id }}" {{ old('staff_id', $library->staff->first()?->id) == $s->id ? 'selected' : '' }}>
                                {{ $s->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('staff_id')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12">
                    <hr class="my-3">
                    <h6 class="fw-bold mb-3">Library Settings</h6>
                </div>

                <div class="col-12 col-sm-6 col-md-4">
                    <label for="book_issue_duration_days" class="form-label fw-semibold">Book Issue Duration (Days)</label>
                    <input type="number" class="form-control form-control-lg @error('book_issue_duration_days') is-invalid @enderror" 
                        name="book_issue_duration_days" id="book_issue_duration_days" 
                        value="{{ old('book_issue_duration_days', $library->settings->book_issue_duration_days ?? 14) }}" min="1" max="365">
                    @error('book_issue_duration_days')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12 col-sm-6 col-md-4">
                    <label for="book_collection_deadline_days" class="form-label fw-semibold">Collection Deadline (Days)</label>
                    <input type="number" class="form-control form-control-lg @error('book_collection_deadline_days') is-invalid @enderror" 
                        name="book_collection_deadline_days" id="book_collection_deadline_days" 
                        value="{{ old('book_collection_deadline_days', $library->settings->book_collection_deadline_days ?? 2) }}" min="1" max="30">
                    @error('book_collection_deadline_days')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12 col-sm-6 col-md-4">
                    <label for="fine_per_day" class="form-label fw-semibold">Fine Per Day (₹)</label>
                    <input type="number" step="0.01" class="form-control form-control-lg @error('fine_per_day') is-invalid @enderror" 
                        name="fine_per_day" id="fine_per_day" 
                        value="{{ old('fine_per_day', $library->settings->fine_per_day ?? 5.00) }}" min="0">
                    @error('fine_per_day')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12 col-sm-6 col-md-4">
                    <label for="max_books_per_student" class="form-label fw-semibold">Max Books Per Student</label>
                    <input type="number" class="form-control form-control-lg @error('max_books_per_student') is-invalid @enderror" 
                        name="max_books_per_student" id="max_books_per_student" 
                        value="{{ old('max_books_per_student', $library->settings->max_books_per_student ?? 2) }}" min="1">
                    @error('max_books_per_student')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12 col-sm-6 col-md-4">
                    <label for="max_books_per_subject" class="form-label fw-semibold">Max Books Per Subject</label>
                    <input type="number" class="form-control form-control-lg @error('max_books_per_subject') is-invalid @enderror" 
                        name="max_books_per_subject" id="max_books_per_subject" 
                        value="{{ old('max_books_per_subject', $library->settings->max_books_per_subject ?? 1) }}" min="1">
                    @error('max_books_per_subject')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="row mt-4">
                <div class="col-12">
                    <div class="form-actions">
                        <a href="{{ route('admin.libraries.index') }}" class="btn btn-secondary btn-lg">
                            <i class="fas fa-times me-2"></i>Cancel
                        </a>
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-save me-2"></i>Update Library
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

