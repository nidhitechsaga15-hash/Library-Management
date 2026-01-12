@extends('layouts.staff')

@section('title', 'Issue Library Card')
@section('page-title', 'Issue Library Card')

@section('content')
<div class="card-modern">
    <div class="card-header-modern">
        <h5>
            <i class="fas fa-id-card text-primary"></i>
            Issue New Library Card
        </h5>
        <a href="{{ route('staff.library-cards.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Back
        </a>
    </div>
    <div class="card-body p-4">
        <form method="POST" action="{{ route('staff.library-cards.store') }}">
            @csrf

            <div class="row g-3">
                <div class="col-12">
                    <label for="user_id" class="form-label fw-semibold">Student <span class="text-danger">*</span></label>
                    <select name="user_id" id="user_id" class="form-select form-select-lg @error('user_id') is-invalid @enderror" required>
                        <option value="">Select Student</option>
                        @foreach($students as $student)
                            <option value="{{ $student->id }}" {{ old('user_id') == $student->id ? 'selected' : '' }}>
                                {{ $student->name }} - {{ $student->student_id ?? $student->email }} 
                                ({{ $student->course ?? 'N/A' }} / {{ $student->batch ?? 'N/A' }})
                            </option>
                        @endforeach
                    </select>
                    @error('user_id')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12 col-md-6">
                    <label for="validity_date" class="form-label fw-semibold">Validity Date <span class="text-danger">*</span></label>
                    <input type="date" name="validity_date" id="validity_date" 
                        class="form-control form-control-lg @error('validity_date') is-invalid @enderror" 
                        value="{{ old('validity_date', now()->addYear()->format('Y-m-d')) }}" 
                        min="{{ now()->addDay()->format('Y-m-d') }}" required>
                    @error('validity_date')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                    <small class="form-text text-muted">Card will be valid until this date</small>
                </div>

                <div class="col-12">
                    <label for="notes" class="form-label fw-semibold">Notes</label>
                    <textarea name="notes" id="notes" rows="3" 
                        class="form-control form-control-lg @error('notes') is-invalid @enderror">{{ old('notes') }}</textarea>
                    @error('notes')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="row mt-4">
                <div class="col-12">
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-id-card me-2"></i>Issue Card
                        </button>
                        <a href="{{ route('staff.library-cards.index') }}" class="btn btn-secondary btn-lg">
                            <i class="fas fa-times me-2"></i>Cancel
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

