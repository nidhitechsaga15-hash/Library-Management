@extends('layouts.admin')

@section('title', 'Edit Book')
@section('page-title', 'Edit Book')

@php
use Illuminate\Support\Facades\Storage;
@endphp

@section('content')
<div class="card-modern">
    <div class="card-header-modern">
        <h5>
            <i class="fas fa-edit text-primary"></i>
            Edit Book
        </h5>
    </div>
    <div class="card-body p-4">
        <form action="{{ route('admin.books.update', $book) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            
            <div class="row g-3">
                <div class="col-12 col-sm-6 col-md-6 col-lg-6 col-xl-6">
                    <label for="isbn" class="form-label fw-semibold">ISBN <span class="text-danger">*</span></label>
                    <input type="text" class="form-control form-control-lg @error('isbn') is-invalid @enderror" 
                        name="isbn" id="isbn" value="{{ old('isbn', $book->isbn) }}" 
                        placeholder="Enter ISBN" required>
                    @error('isbn')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12 col-sm-6 col-md-6 col-lg-6 col-xl-6">
                    <label for="title" class="form-label fw-semibold">Title <span class="text-danger">*</span></label>
                    <input type="text" class="form-control form-control-lg @error('title') is-invalid @enderror" 
                        name="title" id="title" value="{{ old('title', $book->title) }}" 
                        placeholder="Enter Book Title" required>
                    @error('title')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12 col-sm-6 col-md-6 col-lg-6 col-xl-6">
                    <label for="author_id" class="form-label fw-semibold">Author <span class="text-danger">*</span></label>
                    <select class="form-select form-select-lg @error('author_id') is-invalid @enderror" 
                        name="author_id" id="author_id" required>
                        <option value="">Select Author</option>
                        @foreach($authors as $author)
                            <option value="{{ $author->id }}" {{ old('author_id', $book->author_id) == $author->id ? 'selected' : '' }}>
                                {{ $author->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('author_id')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12 col-sm-6 col-md-6 col-lg-6 col-xl-6">
                    <label for="category_id" class="form-label fw-semibold">Category <span class="text-danger">*</span></label>
                    <select class="form-select form-select-lg @error('category_id') is-invalid @enderror" 
                        name="category_id" id="category_id" required>
                        <option value="">Select Category</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ old('category_id', $book->category_id) == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('category_id')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12 col-sm-6 col-md-6 col-lg-6 col-xl-6">
                    <label for="publisher" class="form-label fw-semibold">Publisher</label>
                    <input type="text" class="form-control form-control-lg @error('publisher') is-invalid @enderror" 
                        name="publisher" id="publisher" value="{{ old('publisher', $book->publisher) }}"
                        placeholder="Enter Publisher Name">
                    @error('publisher')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12 col-sm-6 col-md-6 col-lg-6 col-xl-6">
                    <label for="edition" class="form-label fw-semibold">Edition</label>
                    <input type="text" class="form-control form-control-lg @error('edition') is-invalid @enderror" 
                        name="edition" id="edition" value="{{ old('edition', $book->edition) }}" 
                        placeholder="e.g., 1st, 2nd, 3rd">
                    @error('edition')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12 col-sm-6 col-md-6 col-lg-6 col-xl-6">
                    <label for="publication_year" class="form-label fw-semibold">Publication Year</label>
                    <input type="number" class="form-control form-control-lg @error('publication_year') is-invalid @enderror" 
                        name="publication_year" id="publication_year" value="{{ old('publication_year', $book->publication_year) }}" 
                        placeholder="e.g., 2024" min="1900" max="{{ date('Y') }}">
                    @error('publication_year')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12 col-sm-6 col-md-6 col-lg-6 col-xl-6">
                    <label for="language" class="form-label fw-semibold">Language</label>
                    <input type="text" class="form-control form-control-lg @error('language') is-invalid @enderror" 
                        name="language" id="language" value="{{ old('language', $book->language) }}"
                        placeholder="Enter Language">
                    @error('language')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12 col-sm-6 col-md-6 col-lg-6 col-xl-6">
                    <label for="available_copies" class="form-label fw-semibold">Available Copies <span class="text-danger">*</span></label>
                    <input type="number" class="form-control form-control-lg @error('available_copies') is-invalid @enderror" 
                        name="available_copies" id="available_copies" value="{{ old('available_copies', $book->available_copies) }}" 
                        placeholder="Enter Available Copies" required min="0">
                    <small class="form-text text-muted">Total copies will be set equal to available copies</small>
                    @error('available_copies')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12 col-sm-6 col-md-6 col-lg-6 col-xl-6">
                    <label for="rack_number" class="form-label fw-semibold">Rack Number</label>
                    <input type="text" class="form-control form-control-lg @error('rack_number') is-invalid @enderror" 
                        name="rack_number" id="rack_number" value="{{ old('rack_number', $book->rack_number) }}" 
                        placeholder="e.g., A-101, B-205">
                    @error('rack_number')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12 col-sm-6 col-md-6 col-lg-6 col-xl-6">
                    <label for="pages" class="form-label fw-semibold">Pages</label>
                    <input type="number" class="form-control form-control-lg @error('pages') is-invalid @enderror" 
                        name="pages" id="pages" value="{{ old('pages', $book->pages) }}" 
                        placeholder="Enter Number of Pages" min="1">
                    @error('pages')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Book Location Section -->
                <div class="col-12">
                    <hr class="my-3">
                    <h6 class="fw-bold mb-3"><i class="fas fa-map-marker-alt text-primary me-2"></i>Book Location</h6>
                </div>

                <div class="col-12 col-sm-6 col-md-4 col-lg-4 col-xl-4">
                    <label for="almirah" class="form-label fw-semibold">Almirah Number</label>
                    <input type="text" class="form-control form-control-lg @error('almirah') is-invalid @enderror" 
                        name="almirah" id="almirah" value="{{ old('almirah', $book->almirah) }}" placeholder="e.g., A-1, B-2">
                    <small class="form-text text-muted">Almirah where book is stored</small>
                    @error('almirah')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12 col-sm-6 col-md-4 col-lg-4 col-xl-4">
                    <label for="row" class="form-label fw-semibold">Row Number</label>
                    <input type="text" class="form-control form-control-lg @error('row') is-invalid @enderror" 
                        name="row" id="row" value="{{ old('row', $book->row) }}" placeholder="e.g., R-5, R-10">
                    <small class="form-text text-muted">Row within almirah</small>
                    @error('row')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12 col-sm-6 col-md-4 col-lg-4 col-xl-4">
                    <label for="book_serial" class="form-label fw-semibold">Book Serial Number</label>
                    <input type="text" class="form-control form-control-lg @error('book_serial') is-invalid @enderror" 
                        name="book_serial" id="book_serial" value="{{ old('book_serial', $book->book_serial) }}" placeholder="e.g., S-223, S-001">
                    <small class="form-text text-muted">Unique serial number for this book</small>
                    @error('book_serial')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12 col-sm-6 col-md-6 col-lg-6 col-xl-6">
                    <label for="status" class="form-label fw-semibold">Status <span class="text-danger">*</span></label>
                    <select class="form-select form-select-lg @error('status') is-invalid @enderror" 
                        name="status" id="status" required>
                        <option value="available" {{ old('status', $book->status) == 'available' ? 'selected' : '' }}>Available</option>
                        <option value="unavailable" {{ old('status', $book->status) == 'unavailable' ? 'selected' : '' }}>Unavailable</option>
                    </select>
                    @error('status')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12">
                    <label for="description" class="form-label fw-semibold">Description</label>
                    <textarea class="form-control @error('description') is-invalid @enderror" 
                        name="description" id="description" rows="5" placeholder="Enter book description...">{{ old('description', $book->description) }}</textarea>
                    @error('description')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12">
                    <label for="cover_image" class="form-label fw-semibold">Book Cover Image <span class="text-muted">(Optional)</span></label>
                    @if($book->cover_image)
                    <div class="mb-3">
                        <img src="{{ Storage::url($book->cover_image) }}" alt="Book Cover" 
                            class="img-thumbnail" style="width: 120px; height: 160px; object-fit: cover;">
                        <p class="text-muted small mt-2">
                            <i class="fas fa-info-circle"></i> Current cover image
                        </p>
                    </div>
                    @endif
                    <input type="file" class="form-control form-control-lg @error('cover_image') is-invalid @enderror" 
                        name="cover_image" id="cover_image" accept="image/*">
                    <small class="form-text text-muted d-block mt-1">
                        <i class="fas fa-info-circle"></i> Max size: 2MB. Formats: JPEG, PNG, JPG, GIF. Leave empty to keep current image.
                    </small>
                    @error('cover_image')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="row mt-4">
                <div class="col-12">
                    <div class="form-actions">
                        <a href="{{ route('admin.books.index') }}" class="btn btn-secondary btn-lg">
                            <i class="fas fa-times me-2"></i>Cancel
                        </a>
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-save me-2"></i>Update Book
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
