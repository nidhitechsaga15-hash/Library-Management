@extends('layouts.admin')

@section('title', 'Issue Book')
@section('page-title', 'Issue New Book')

@section('content')
<div class="card-modern">
    <div class="card-header-modern">
        <h5>
            <i class="fas fa-exchange-alt text-primary"></i>
            Issue New Book
        </h5>
    </div>
    <div class="card-body p-4">
        <form method="POST" action="{{ route('admin.borrows.issue') }}">
            @csrf

            <div class="row g-3">
                <!-- Student/Staff ID or Email -->
                <div class="col-12">
                    <label for="user_identifier" class="form-label fw-semibold">Student/Staff ID or Email <span class="text-danger">*</span></label>
                    <input type="text" name="user_identifier" id="user_identifier" 
                        value="{{ old('user_identifier') }}" required
                        placeholder="Enter Student ID, Staff ID, or Email"
                        class="form-control form-control-lg @error('user_identifier') is-invalid @enderror">
                    <small class="form-text text-muted d-block mt-1">
                        <i class="fas fa-info-circle"></i> Enter Student ID, Staff ID, or Email address
                    </small>
                    @error('user_identifier')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Book Selection -->
                <div class="col-12">
                    <label for="book_id" class="form-label fw-semibold">Book <span class="text-danger">*</span></label>
                    <div style="position: relative; z-index: 10;">
                        <select name="book_id" id="book_id" required
                            class="form-select form-select-lg @error('book_id') is-invalid @enderror"
                            style="cursor: pointer; position: relative; z-index: 10;">
                            <option value="">Select a Book</option>
                            @foreach($books as $book)
                                <option value="{{ $book->id }}" {{ old('book_id') == $book->id ? 'selected' : '' }}>
                                    {{ $book->title }}@if($book->isbn) - {{ $book->isbn }}@endif (Available: {{ $book->available_copies }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <small class="form-text text-muted d-block mt-1">
                        <i class="fas fa-info-circle"></i> Total Books: {{ $books->count() }}
                    </small>
                    @error('book_id')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Issue Date and Duration -->
                <div class="col-12 col-sm-6 col-md-6 col-lg-6 col-xl-6">
                    <label for="borrow_date" class="form-label fw-semibold">Issue Date <span class="text-danger">*</span></label>
                    <input type="date" name="borrow_date" id="borrow_date" 
                        value="{{ old('borrow_date', date('Y-m-d')) }}" required
                        class="form-control form-control-lg @error('borrow_date') is-invalid @enderror">
                    @error('borrow_date')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12 col-sm-6 col-md-6 col-lg-6 col-xl-6">
                    <label for="issue_duration_days" class="form-label fw-semibold">Issue Duration (Days) <span class="text-danger">*</span></label>
                    <input type="number" name="issue_duration_days" id="issue_duration_days" 
                        value="{{ old('issue_duration_days', 15) }}" required min="1" max="365"
                        class="form-control form-control-lg @error('issue_duration_days') is-invalid @enderror">
                    <small class="form-text text-muted d-block mt-1">
                        <i class="fas fa-info-circle"></i> Days will be counted from the next day after issue date
                    </small>
                    <small class="form-text text-primary d-block mt-1" id="due_date_preview">
                        <i class="fas fa-calendar"></i> Due Date: <span id="calculated_due_date">-</span>
                    </small>
                    @error('issue_duration_days')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12 col-sm-6 col-md-6 col-lg-6 col-xl-6">
                    <label for="fine_per_day" class="form-label fw-semibold">Fine Per Day (₹) <span class="text-danger">*</span></label>
                    <input type="number" name="fine_per_day" id="fine_per_day" 
                        value="{{ old('fine_per_day') }}" required min="0" step="0.01"
                        class="form-control form-control-lg @error('fine_per_day') is-invalid @enderror"
                        placeholder="Enter fine per day">
                    <small class="form-text text-muted d-block mt-1">
                        <i class="fas fa-info-circle"></i> Fine amount per day if book is returned late
                    </small>
                    @error('fine_per_day')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <!-- Form Actions -->
            <div class="row mt-4">
                <div class="col-12">
                    <div class="form-actions" style="display: flex; flex-direction: column; gap: 10px; width: 100%;">
                        <button type="submit" class="btn btn-primary btn-lg" style="width: 100%; order: 1;">
                            <i class="fas fa-book me-2"></i>Issue Book
                        </button>
                        <a href="{{ route('admin.borrows.index') }}" class="btn btn-secondary btn-lg" style="width: 100%; order: 2;">
                            <i class="fas fa-times me-2"></i>Cancel
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

@push('styles')
<style>
    #book_id {
        position: relative !important;
        z-index: 10000 !important;
    }
    #book_id:focus {
        z-index: 10001 !important;
        position: relative !important;
    }
    select#book_id {
        -webkit-appearance: menulist !important;
        -moz-appearance: menulist !important;
        appearance: menulist !important;
    }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const borrowDateInput = document.getElementById('borrow_date');
    const durationInput = document.getElementById('issue_duration_days');
    const dueDatePreview = document.getElementById('calculated_due_date');
    const bookSelect = document.getElementById('book_id');

    // Ensure select is clickable
    if (bookSelect) {
        bookSelect.style.pointerEvents = 'auto';
        bookSelect.style.zIndex = '10000';
        
        // Debug: Log if books are loaded
        console.log('Total books in select:', bookSelect.options.length);
        
        // Force enable the select
        bookSelect.disabled = false;
        bookSelect.removeAttribute('disabled');
    }

    function calculateDueDate() {
        const borrowDate = borrowDateInput.value;
        const duration = parseInt(durationInput.value) || 0;

        if (borrowDate && duration > 0) {
            const date = new Date(borrowDate);
            // Add 1 day (next day) then add duration - 1 days
            date.setDate(date.getDate() + 1 + (duration - 1));
            
            const formattedDate = date.toLocaleDateString('en-US', { 
                year: 'numeric', 
                month: 'short', 
                day: 'numeric' 
            });
            dueDatePreview.textContent = formattedDate;
        } else {
            dueDatePreview.textContent = '-';
        }
    }

    borrowDateInput.addEventListener('change', calculateDueDate);
    durationInput.addEventListener('input', calculateDueDate);
    durationInput.addEventListener('change', calculateDueDate);
    
    // Calculate on page load
    calculateDueDate();
});
</script>
@endpush
@endsection
