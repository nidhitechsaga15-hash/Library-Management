@extends('layouts.admin')

@section('title', 'Library Settings')
@section('page-title')
Library Settings - {{ $library->name }}
@endsection

@section('content')
<div class="card-modern">
    <div class="card-header-modern">
        <h5>
            <i class="fas fa-cog text-primary"></i>
            Settings - {{ $library->name }}
        </h5>
        <a href="{{ route('admin.libraries.show', $library) }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Back
        </a>
    </div>
    <div class="card-body p-4">
        <form method="POST" action="{{ route('admin.libraries.settings.update', $library) }}">
            @csrf

            <div class="row g-3">
                <div class="col-12">
                    <h6 class="fw-bold mb-3">Book Management Settings</h6>
                </div>

                <div class="col-12 col-sm-6 col-md-4">
                    <label for="book_issue_duration_days" class="form-label fw-semibold">Book Issue Duration (Days) <span class="text-danger">*</span></label>
                    <input type="number" class="form-control form-control-lg @error('book_issue_duration_days') is-invalid @enderror" 
                        name="book_issue_duration_days" id="book_issue_duration_days" 
                        value="{{ old('book_issue_duration_days', $settings->book_issue_duration_days ?? 14) }}" 
                        min="1" max="365" required>
                    <small class="form-text text-muted">How many days a student can keep a book</small>
                    @error('book_issue_duration_days')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12 col-sm-6 col-md-4">
                    <label for="book_collection_deadline_days" class="form-label fw-semibold">Collection Deadline (Days) <span class="text-danger">*</span></label>
                    <input type="number" class="form-control form-control-lg @error('book_collection_deadline_days') is-invalid @enderror" 
                        name="book_collection_deadline_days" id="book_collection_deadline_days" 
                        value="{{ old('book_collection_deadline_days', $settings->book_collection_deadline_days ?? 2) }}" 
                        min="1" max="30" required>
                    <small class="form-text text-muted">Days to collect book after approval</small>
                    @error('book_collection_deadline_days')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12 col-sm-6 col-md-4">
                    <label for="fine_per_day" class="form-label fw-semibold">Fine Per Day (₹) <span class="text-danger">*</span></label>
                    <input type="number" step="0.01" class="form-control form-control-lg @error('fine_per_day') is-invalid @enderror" 
                        name="fine_per_day" id="fine_per_day" 
                        value="{{ old('fine_per_day', $settings->fine_per_day ?? 5.00) }}" 
                        min="0" required>
                    <small class="form-text text-muted">Fine amount for overdue books</small>
                    @error('fine_per_day')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12">
                    <hr class="my-3">
                    <h6 class="fw-bold mb-3">Student Limits</h6>
                </div>

                <div class="col-12 col-sm-6 col-md-4">
                    <label for="max_books_per_student" class="form-label fw-semibold">Max Books Per Student <span class="text-danger">*</span></label>
                    <input type="number" class="form-control form-control-lg @error('max_books_per_student') is-invalid @enderror" 
                        name="max_books_per_student" id="max_books_per_student" 
                        value="{{ old('max_books_per_student', $settings->max_books_per_student ?? 2) }}" 
                        min="1" required>
                    <small class="form-text text-muted">Maximum books a student can borrow</small>
                    @error('max_books_per_student')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12 col-sm-6 col-md-4">
                    <label for="max_books_per_subject" class="form-label fw-semibold">Max Books Per Subject <span class="text-danger">*</span></label>
                    <input type="number" class="form-control form-control-lg @error('max_books_per_subject') is-invalid @enderror" 
                        name="max_books_per_subject" id="max_books_per_subject" 
                        value="{{ old('max_books_per_subject', $settings->max_books_per_subject ?? 1) }}" 
                        min="1" required>
                    <small class="form-text text-muted">Maximum books per subject per student</small>
                    @error('max_books_per_subject')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="row mt-4">
                <div class="col-12">
                    <div class="form-actions">
                        <a href="{{ route('admin.libraries.show', $library) }}" class="btn btn-secondary btn-lg">
                            <i class="fas fa-times me-2"></i>Cancel
                        </a>
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-save me-2"></i>Update Settings
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

