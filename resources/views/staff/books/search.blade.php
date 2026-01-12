@extends('layouts.staff')

@section('title', 'Search Book')
@section('page-title', 'Search Book')

@section('content')
<div class="card-modern">
    <div class="card-header-modern">
        <h5>
            <i class="fas fa-search text-primary"></i>
            Search Books
        </h5>
    </div>
    <div class="card-body p-4">
        <!-- Search Form -->
        <form method="GET" action="{{ route('staff.books.search') }}" class="mb-4">
            <div class="row g-3">
                <div class="col-12 col-md-6">
                    <label for="search" class="form-label fw-semibold">Search</label>
                    <input type="text" name="search" id="search" value="{{ request('search') }}" 
                        placeholder="Search by title, ISBN, author, or publisher..."
                        class="form-control form-control-lg">
                </div>
                <div class="col-12 col-md-3">
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
                <div class="col-12 col-md-3">
                    <label for="status" class="form-label fw-semibold">Status</label>
                    <select name="status" id="status" class="form-select form-select-lg">
                        <option value="">All Status</option>
                        <option value="available" {{ request('status') == 'available' ? 'selected' : '' }}>Available</option>
                        <option value="unavailable" {{ request('status') == 'unavailable' ? 'selected' : '' }}>Unavailable</option>
                    </select>
                </div>
            </div>
            <div class="row mt-4">
                <div class="col-12">
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-search me-2"></i>Search
                        </button>
                        <a href="{{ route('staff.books.search') }}" class="btn btn-secondary btn-lg">
                            <i class="fas fa-times me-2"></i>Clear
                        </a>
                    </div>
                </div>
            </div>
        </form>

        <!-- Results Count -->
        @if(request()->has('search') || request()->has('category_id') || request()->has('status'))
        <div class="row mb-4">
            <div class="col-12">
                <div class="alert alert-info mb-0">
                    <i class="fas fa-info-circle me-2"></i>
                    Found <strong>{{ $books->total() }}</strong> book(s) matching your search criteria
                </div>
            </div>
        </div>
        @endif

        <!-- Results Table -->
        <div class="row">
            <div class="col-12">
                <div class="table-responsive">
                    <table id="searchTable" class="table table-modern data-table mb-0 w-100">
                        <thead>
                            <tr>
                                <th style="width: 12%;">ISBN</th>
                                <th style="width: 25%;">Title</th>
                                <th style="width: 18%;">Author</th>
                                <th style="width: 12%;">Category</th>
                                <th style="width: 12%;">Copies</th>
                                <th style="width: 10%;">Status</th>
                                <th style="width: 11%;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($books as $book)
                            <tr>
                                <td>{{ $book->isbn }}</td>
                                <td class="fw-semibold">{{ $book->title }}</td>
                                <td>{{ $book->author->name }}</td>
                                <td>
                                    <span class="badge bg-info">{{ $book->category->name }}</span>
                                </td>
                                <td>
                                    <span class="text-success fw-bold">{{ $book->effective_available_copies }}</span>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $book->status === 'available' ? 'success' : 'danger' }}">
                                        {{ ucfirst($book->status) }}
                                    </span>
                                </td>
                                <td>
                                    <a href="{{ route('staff.books.show', $book) }}" class="btn btn-sm btn-info text-white" title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center py-5">
                                    <div class="text-muted">
                                        <i class="fas fa-search fa-4x d-block mb-3"></i>
                                        <h5 class="mb-2">
                                            @if(request()->has('search') || request()->has('category_id') || request()->has('status'))
                                                No books found
                                            @else
                                                Start searching for books
                                            @endif
                                        </h5>
                                        <p class="mb-0">
                                            @if(request()->has('search') || request()->has('category_id') || request()->has('status'))
                                                No books found matching your search criteria. Try different keywords or filters.
                                            @else
                                                Use the search form above to find books in the library.
                                            @endif
                                        </p>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

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
