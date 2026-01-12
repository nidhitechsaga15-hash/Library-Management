@extends('layouts.student')

@section('title', 'Browse Books')
@section('page-title', 'Browse Books')

@php
use Illuminate\Support\Facades\Storage;
@endphp

@section('content')
<div class="card-modern">
    <div class="card-header-modern">
        <h5>
            <i class="fas fa-book text-primary"></i>
            Browse Books
        </h5>
    </div>
    <div class="card-body p-4">
        @php
            $user = auth()->user();
            $activeBorrows = $user->getActiveBorrowsCount();
            $maxBooks = 2;
            $canBorrow = $user->canBorrowMoreBooks($maxBooks);
        @endphp
        
        <!-- Book Limit Info Banner -->
        <div class="alert alert-{{ $activeBorrows >= $maxBooks ? 'danger' : ($activeBorrows > 0 ? 'warning' : 'success') }} mb-4">
            <div class="d-flex justify-content-between align-items-center flex-wrap">
                <div>
                    <i class="fas fa-{{ $activeBorrows >= $maxBooks ? 'exclamation-triangle' : 'info-circle' }} me-2"></i>
                    <strong>Your Book Status:</strong> 
                    <span class="badge bg-{{ $activeBorrows >= $maxBooks ? 'danger' : ($activeBorrows > 0 ? 'warning' : 'success') }} ms-2">
                        {{ $activeBorrows }} / {{ $maxBooks }} books issued
                    </span>
                    @if($activeBorrows >= $maxBooks)
                    <span class="ms-2 text-danger">
                        <i class="fas fa-ban me-1"></i>You cannot request more books until you return one.
                    </span>
                    @endif
                </div>
                @if($activeBorrows > 0)
                <a href="{{ route('student.my-books') }}" class="btn btn-sm btn-{{ $activeBorrows >= $maxBooks ? 'danger' : 'warning' }} mt-2 mt-md-0">
                    <i class="fas fa-book-open me-1"></i>View My Books
                </a>
                @endif
            </div>
        </div>

        <!-- Course / Batch / Semester / Year Filter Banner -->
        @if(auth()->user()->course || auth()->user()->semester || auth()->user()->year || auth()->user()->batch)
        <div class="alert alert-secondary mb-4">
            <div class="d-flex justify-content-between align-items-center flex-wrap">
                <div>
                    <i class="fas fa-book me-2"></i>
                    Showing books for 
                    <strong>{{ auth()->user()->course ?? 'Course not set' }}</strong>
                    @if(auth()->user()->year)
                        - <strong>Year: {{ auth()->user()->year }}</strong>
                    @endif
                    @if(auth()->user()->batch)
                        - <strong>Batch: {{ auth()->user()->batch }}</strong>
                    @endif
                    @if(auth()->user()->semester)
                        - <strong>Semester: {{ auth()->user()->semester }}</strong>
                    @endif
                </div>
                <a href="{{ route('student.profile.show') }}" class="btn btn-sm btn-outline-secondary mt-2 mt-md-0">
                    <i class="fas fa-user-edit me-1"></i>Update Profile
                </a>
            </div>
        </div>
        @endif

        <!-- Search and Filter -->
        <form method="GET" action="{{ route('student.books.index') }}" class="mb-4">
            <div class="row g-3">
                <div class="col-12 col-sm-6 col-md-4">
                    <input type="text" name="search" value="{{ request('search') }}" 
                        placeholder="Search by title, ISBN, or author..." 
                        class="form-control form-control-lg">
                </div>
                <div class="col-12 col-sm-6 col-md-4">
                    <select name="category_id" class="form-select form-select-lg">
                        <option value="">All Categories</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-sm-6 col-md-4">
                    <button type="submit" class="btn btn-primary btn-lg w-100">
                        <i class="fas fa-search me-2"></i>Search
                    </button>
                </div>
            </div>
        </form>

        <!-- Recommended Books Section -->
        @if(isset($recommendedBooks) && $recommendedBooks->isNotEmpty())
        <div class="mb-4">
                <h5 class="mb-3">
                <i class="fas fa-star text-warning me-2"></i>
                Recommended Books for {{ auth()->user()->course ?? 'Your Course' }} 
                @if(auth()->user()->year) - {{ auth()->user()->year }} @endif
                @if(auth()->user()->batch) - Batch: {{ auth()->user()->batch }} @endif
                @if(auth()->user()->semester) - {{ auth()->user()->semester }} @endif
            </h5>
            <div class="row g-3">
                @foreach($recommendedBooks->take(6) as $book)
                <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                    <div class="card border hover-shadow h-100">
                        <div class="card-body p-3">
                            <h6 class="card-title mb-2">{{ Str::limit($book->title, 40) }}</h6>
                            <p class="text-muted small mb-2">
                                <i class="fas fa-user me-1"></i>{{ $book->author->name }}
                            </p>
                            <p class="text-muted small mb-2">
                                <i class="fas fa-tag me-1"></i>{{ $book->category->name }}
                            </p>
                            <div class="d-flex justify-content-between align-items-center">
                                @if($book->isAvailable())
                                    <span class="badge bg-success">Available</span>
                                @else
                                    <span class="badge bg-danger">Unavailable</span>
                                @endif
                                <a href="{{ route('student.books.show', $book) }}" class="btn btn-sm btn-primary">
                                    View
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        <hr class="my-4">
        @endif

        <div class="row">
            <div class="col-12">
                <div class="table-responsive">
                    <table id="booksTable" class="table table-modern data-table mb-0 w-100">
                <thead>
                    <tr>
                        <th style="width: 5%;">#</th>
                        <th style="width: 8%;">Image</th>
                        <th style="width: 10%;">ISBN</th>
                        <th style="width: 20%;">Title</th>
                        <th style="width: 12%;">Author</th>
                        <th style="width: 10%;">Category</th>
                        <th style="width: 10%;">Status</th>
                        <th style="width: 15%;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($books as $index => $book)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>
                            @if($book->cover_image)
                                <img src="{{ Storage::url($book->cover_image) }}" alt="{{ $book->title }}" 
                                    class="img-thumbnail" style="width: 60px; height: 80px; object-fit: cover;">
                            @else
                                <div class="bg-light d-flex align-items-center justify-content-center" style="width: 60px; height: 80px;">
                                    <i class="fas fa-book text-muted"></i>
                                </div>
                            @endif
                        </td>
                        <td>{{ $book->isbn }}</td>
                        <td class="fw-semibold">{{ $book->title }}</td>
                        <td>{{ $book->author->name }}</td>
                        <td>
                            <span class="badge bg-info">{{ $book->category->name }}</span>
                        </td>
                        <td>
                            @if($book->status === 'available')
                                <span class="badge bg-success">Available</span>
                            @else
                                <span class="badge bg-danger">Unavailable</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('student.books.show', $book) }}" class="btn btn-sm btn-primary" title="View Details">
                                <i class="fas fa-eye"></i> View
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
