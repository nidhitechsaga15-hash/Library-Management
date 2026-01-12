@extends('layouts.student')

@section('title', 'Search Books')
@section('page-title', 'Search Books')

@section('content')
<div class="card-modern">
    <div class="card-header-modern">
        <h5>
            <i class="fas fa-search text-primary"></i>
            Search Available Books
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

        <!-- Course / Semester Filter Banner -->
        @if(auth()->user()->course || auth()->user()->semester)
        <div class="alert alert-secondary mb-4">
            <div class="d-flex justify-content-between align-items-center flex-wrap">
                <div>
                    <i class="fas fa-book me-2"></i>
                    Showing books for 
                    <strong>{{ auth()->user()->course ?? 'Course not set' }}</strong>
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

        <!-- Search Form -->
        <form method="GET" action="{{ route('student.books.search') }}" class="mb-4">
            <div class="row g-3">
                <div class="col-12 col-md-8">
                    <label for="search" class="form-label fw-semibold">Search <span class="text-danger">*</span></label>
                    <input type="text" name="search" id="search" value="{{ request('search') }}" 
                        placeholder="Search by title, ISBN, author, or publisher..."
                        class="form-control form-control-lg">
                </div>
                <div class="col-12 col-md-4">
                    <label for="category_id" class="form-label fw-semibold">Category</label>
                    <select name="category_id" id="category_id" class="form-select form-select-lg">
                        <option value="">All Categories</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="row mt-4">
                <div class="col-12">
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-search me-2"></i>Search
                        </button>
                        <a href="{{ route('student.books.search') }}" class="btn btn-secondary btn-lg">
                            <i class="fas fa-times me-2"></i>Clear
                        </a>
                    </div>
                </div>
            </div>
        </form>

        <!-- Results Count -->
        @if(request()->has('search') || request()->has('category_id'))
        <div class="row mb-4">
            <div class="col-12">
                <div class="alert alert-info mb-0">
                    <i class="fas fa-info-circle me-2"></i>
                    Found <strong>{{ $books->total() }}</strong> available book(s) matching your search criteria
                </div>
            </div>
        </div>
        @endif

        <!-- Results -->
        @if($books->count() > 0)
        <div class="row g-3">
            @foreach($books as $book)
            <div class="col-12 col-sm-6 col-md-6 col-lg-4">
                <div class="card h-100 border shadow-sm hover-shadow">
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title fw-bold mb-3 text-primary">{{ $book->title }}</h5>
                        <div class="mb-3 flex-grow-1">
                            <p class="mb-2">
                                <i class="fas fa-user text-primary me-2"></i>
                                <strong>Author:</strong> <span class="text-muted">{{ $book->author->name }}</span>
                            </p>
                            <p class="mb-2">
                                <i class="fas fa-tag text-info me-2"></i>
                                <strong>Category:</strong> <span class="badge bg-info">{{ $book->category->name }}</span>
                            </p>
                            <p class="mb-2">
                                <i class="fas fa-barcode text-secondary me-2"></i>
                                <strong>ISBN:</strong> <span class="text-muted small">{{ $book->isbn }}</span>
                            </p>
                            <p class="mb-0">
                                <i class="fas fa-book text-success me-2"></i>
                                <strong>Status:</strong> 
                                @if($book->isAvailable())
                                    <span class="badge bg-success">Available</span>
                                @else
                                    <span class="badge bg-danger">Unavailable</span>
                                @endif
                            </p>
                        </div>
                        <a href="{{ route('student.books.show', $book) }}" class="btn btn-primary w-100 mt-auto">
                            <i class="fas fa-eye me-2"></i>View Details
                        </a>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @else
        <div class="row">
            <div class="col-12">
                <div class="text-center py-5">
                    <i class="fas fa-search fa-4x text-muted d-block mb-3"></i>
                    <h5 class="text-muted mb-2">
                        @if(request()->has('search') || request()->has('category_id'))
                            No books found
                        @else
                            Start searching for books
                        @endif
                    </h5>
                    <p class="text-muted mb-0">
                        @if(request()->has('search') || request()->has('category_id'))
                            No books found matching your search criteria. Try different keywords or categories.
                        @else
                            Use the search form above to find available books in the library.
                        @endif
                    </p>
                </div>
            </div>
        </div>
        @endif

        <!-- Pagination -->
        @if($books->hasPages())
        <div class="row mt-4">
            <div class="col-12">
                <div class="d-flex justify-content-center">
                    {{ $books->links() }}
                </div>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
