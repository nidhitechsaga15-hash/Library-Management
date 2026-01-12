@extends('layouts.admin')

@section('title', 'Edit Category')
@section('page-title', 'Edit Category')

@section('content')
<div class="card-modern">
    <div class="card-header-modern">
        <h5>
            <i class="fas fa-tag text-primary"></i>
            Edit Category
        </h5>
    </div>
    <div class="card-body p-4">
        <form method="POST" action="{{ route('admin.categories.update', $category) }}">
            @csrf
            @method('PUT')

            <div class="row g-3">
                <div class="col-12 col-sm-6 col-md-6 col-lg-6 col-xl-6">
                    <label for="name" class="form-label fw-semibold">Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control form-control-lg @error('name') is-invalid @enderror" 
                        name="name" id="name" value="{{ old('name', $category->name) }}" 
                        placeholder="Enter Category Name" required>
                    @error('name')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12">
                    <label for="description" class="form-label fw-semibold">Description</label>
                    <textarea class="form-control @error('description') is-invalid @enderror" 
                        name="description" id="description" rows="5" placeholder="Enter category description...">{{ old('description', $category->description) }}</textarea>
                    @error('description')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="row mt-4">
                <div class="col-12">
                    <div class="form-actions">
                        <a href="{{ route('admin.categories.index') }}" class="btn btn-secondary btn-lg">
                            <i class="fas fa-times me-2"></i>Cancel
                        </a>
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-save me-2"></i>Update Category
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
