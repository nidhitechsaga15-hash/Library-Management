@extends('layouts.staff')

@section('title', 'Book Details')
@section('page-title', 'Book Details')

@php
use Illuminate\Support\Facades\Storage;
@endphp

@section('content')
<div class="card-modern">
    <div class="card-header-modern">
        <h5>
            <i class="fas fa-book text-primary"></i>
            Book Details
        </h5>
    </div>
    <div class="card-body p-4">
        <div class="row mb-4">
            <div class="col-12">
                <h3 class="fw-bold mb-2">{{ $book->title }}</h3>
                <p class="text-muted mb-0">ISBN: {{ $book->isbn }}</p>
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-12 col-sm-6 col-md-6 col-lg-6 col-xl-6">
                <label class="form-label text-muted small mb-1">Author</label>
                <p class="fw-semibold mb-0">{{ $book->author->name }}</p>
            </div>
            <div class="col-12 col-sm-6 col-md-6 col-lg-6 col-xl-6">
                <label class="form-label text-muted small mb-1">Category</label>
                <p class="mb-0">
                    <span class="badge bg-info">{{ $book->category->name }}</span>
                </p>
            </div>
            <div class="col-12 col-sm-6 col-md-6 col-lg-6 col-xl-6">
                <label class="form-label text-muted small mb-1">Publisher</label>
                <p class="fw-semibold mb-0">{{ $book->publisher ?? 'N/A' }}</p>
            </div>
            <div class="col-12 col-sm-6 col-md-6 col-lg-6 col-xl-6">
                <label class="form-label text-muted small mb-1">Edition</label>
                <p class="fw-semibold mb-0">{{ $book->edition ?? 'N/A' }}</p>
            </div>
            <div class="col-12 col-sm-6 col-md-6 col-lg-6 col-xl-6">
                <label class="form-label text-muted small mb-1">Available Copies</label>
                <p class="fw-semibold mb-0">
                    <span class="badge bg-{{ $book->effective_available_copies > 0 ? 'success' : 'danger' }}">
                        {{ $book->effective_available_copies }}
                    </span>
                </p>
            </div>
            <div class="col-12 col-sm-6 col-md-6 col-lg-6 col-xl-6">
                <label class="form-label text-muted small mb-1">Rack Number</label>
                <p class="fw-semibold mb-0">{{ $book->rack_number ?? 'N/A' }}</p>
            </div>
            <div class="col-12 col-sm-6 col-md-6 col-lg-6 col-xl-6">
                <label class="form-label text-muted small mb-1">Status</label>
                <p class="mb-0">
                    <span class="badge bg-{{ $book->status === 'available' ? 'success' : 'danger' }}">
                        {{ ucfirst($book->status) }}
                    </span>
                </p>
            </div>
        </div>

        @if($book->description)
        <div class="row mb-4">
            <div class="col-12">
                <label class="form-label text-muted small mb-2">Description</label>
                <p class="mb-0">{{ $book->description }}</p>
            </div>
        </div>
        @endif

        @if($book->cover_image)
        <div class="row">
            <div class="col-12">
                <label class="form-label text-muted small mb-2">Cover Image</label>
                <div>
                    <img src="{{ Storage::url($book->cover_image) }}" alt="{{ $book->title }}" 
                        class="img-thumbnail" style="max-width: 300px; height: auto;">
                </div>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
