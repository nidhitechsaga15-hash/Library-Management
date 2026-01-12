@extends('layouts.admin')

@section('title', 'Edit Author')
@section('page-title', 'Edit Author')

@section('content')
<div class="card-modern">
    <div class="card-header-modern">
        <h5>
            <i class="fas fa-user-edit text-primary"></i>
            Edit Author
        </h5>
    </div>
    <div class="card-body p-4">
        <form method="POST" action="{{ route('admin.authors.update', $author) }}">
            @csrf
            @method('PUT')

            <div class="row g-3">
                <div class="col-12 col-sm-6 col-md-6 col-lg-6 col-xl-6">
                    <label for="name" class="form-label fw-semibold">Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control form-control-lg @error('name') is-invalid @enderror" 
                        name="name" id="name" value="{{ old('name', $author->name) }}" 
                        placeholder="Enter Author Name" required>
                    @error('name')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12 col-sm-6 col-md-6 col-lg-6 col-xl-6">
                    <label for="nationality" class="form-label fw-semibold">Nationality</label>
                    <input type="text" class="form-control form-control-lg @error('nationality') is-invalid @enderror" 
                        name="nationality" id="nationality" value="{{ old('nationality', $author->nationality) }}"
                        placeholder="Enter Nationality">
                    @error('nationality')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12 col-sm-6 col-md-6 col-lg-6 col-xl-6">
                    <label for="date_of_birth" class="form-label fw-semibold">Date of Birth</label>
                    <input type="date" class="form-control form-control-lg @error('date_of_birth') is-invalid @enderror" 
                        name="date_of_birth" id="date_of_birth" value="{{ old('date_of_birth', $author->date_of_birth?->format('Y-m-d')) }}">
                    @error('date_of_birth')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12">
                    <label for="bio" class="form-label fw-semibold">Biography</label>
                    <textarea class="form-control @error('bio') is-invalid @enderror" 
                        name="bio" id="bio" rows="5" placeholder="Enter author biography...">{{ old('bio', $author->bio) }}</textarea>
                    @error('bio')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="row mt-4">
                <div class="col-12">
                    <div class="form-actions">
                        <a href="{{ route('admin.authors.index') }}" class="btn btn-secondary btn-lg">
                            <i class="fas fa-times me-2"></i>Cancel
                        </a>
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-save me-2"></i>Update Author
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
