@extends('layouts.admin')

@section('title', 'Books Management')
@section('page-title', 'Books Management')

@php
use Illuminate\Support\Facades\Storage;
@endphp

@section('content')
<style>
    #booksTable th:nth-child(8),
    #booksTable td:nth-child(8) {
        min-width: 100px;
        white-space: nowrap;
    }
    #booksTable td {
        padding: 0.75rem;
        vertical-align: middle;
    }
    .table-responsive {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }
    .pagination-wrapper {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }
    .pagination {
        flex-wrap: wrap;
        margin-bottom: 0;
        justify-content: center;
    }
    .pagination .page-link {
        color: #667eea;
        border-color: #dee2e6;
    }
    .pagination .page-item.active .page-link {
        background-color: #667eea;
        border-color: #667eea;
    }
    .pagination .page-link:hover {
        color: #764ba2;
        background-color: #f8f9fa;
    }
</style>
<div class="card-modern">
    <div class="card-header-modern">
        <h5>
            <i class="fas fa-book text-primary"></i>
            Books Section
        </h5>
        <a href="{{ route('admin.books.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Create
        </a>
    </div>
    <div class="card-body p-4">
        <!-- Advanced Search and Filter Form -->
        <form method="GET" action="{{ route('admin.books.index') }}" class="mb-4">
            <div class="card bg-light mb-3">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-filter me-2"></i>Advanced Search & Filters
                        <button type="button" class="btn btn-sm btn-link float-end" data-bs-toggle="collapse" data-bs-target="#filterCollapse">
                            <i class="fas fa-chevron-down"></i>
                        </button>
                    </h6>
                </div>
                <div class="collapse show" id="filterCollapse">
                    <div class="card-body">
                        <div class="row g-3">
                            <!-- Basic Search -->
                            <div class="col-12 col-md-6">
                                <label for="search" class="form-label fw-semibold">Search Books</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                                    <input type="text" name="search" id="search" value="{{ request('search') }}" 
                                        placeholder="Search by title, ISBN, publisher, author..."
                                        class="form-control">
                                </div>
                            </div>
                            
                            <!-- Author Filter -->
                            <div class="col-12 col-md-3">
                                <label for="author_id" class="form-label fw-semibold">Author</label>
                                <select name="author_id" id="author_id" class="form-select">
                                    <option value="">All Authors</option>
                                    @foreach($authors as $author)
                                        <option value="{{ $author->id }}" {{ request('author_id') == $author->id ? 'selected' : '' }}>
                                            {{ $author->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <!-- Category Filter -->
                            <div class="col-12 col-md-3">
                                <label for="category_id" class="form-label fw-semibold">Category</label>
                                <select name="category_id" id="category_id" class="form-select">
                                    <option value="">All Categories</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                                            {{ $category->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <!-- Status Filter -->
                            <div class="col-12 col-md-3">
                                <label for="status" class="form-label fw-semibold">Status</label>
                                <select name="status" id="status" class="form-select">
                                    <option value="">All Status</option>
                                    <option value="available" {{ request('status') == 'available' ? 'selected' : '' }}>Available</option>
                                    <option value="unavailable" {{ request('status') == 'unavailable' ? 'selected' : '' }}>Unavailable</option>
                                </select>
                            </div>
                            
                            <!-- Library Filter -->
                            <div class="col-12 col-md-3">
                                <label for="library_id" class="form-label fw-semibold">Library</label>
                                <select name="library_id" id="library_id" class="form-select">
                                    <option value="">All Libraries</option>
                                    @foreach($libraries as $library)
                                        <option value="{{ $library->id }}" {{ request('library_id') == $library->id ? 'selected' : '' }}>
                                            {{ $library->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <!-- Publisher Filter -->
                            <div class="col-12 col-md-3">
                                <label for="publisher" class="form-label fw-semibold">Publisher</label>
                                <select name="publisher" id="publisher" class="form-select">
                                    <option value="">All Publishers</option>
                                    @foreach($publishers as $publisher)
                                        <option value="{{ $publisher }}" {{ request('publisher') == $publisher ? 'selected' : '' }}>
                                            {{ $publisher }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <!-- Language Filter -->
                            <div class="col-12 col-md-3">
                                <label for="language" class="form-label fw-semibold">Language</label>
                                <select name="language" id="language" class="form-select">
                                    <option value="">All Languages</option>
                                    @foreach($languages as $language)
                                        <option value="{{ $language }}" {{ request('language') == $language ? 'selected' : '' }}>
                                            {{ $language }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <!-- Publication Year Range -->
                            <div class="col-12 col-md-3">
                                <label for="year_from" class="form-label fw-semibold">Year From</label>
                                <input type="number" name="year_from" id="year_from" 
                                    value="{{ request('year_from') }}" 
                                    min="1000" max="{{ date('Y') }}"
                                    placeholder="e.g., 2000"
                                    class="form-control">
                            </div>
                            
                            <div class="col-12 col-md-3">
                                <label for="year_to" class="form-label fw-semibold">Year To</label>
                                <input type="number" name="year_to" id="year_to" 
                                    value="{{ request('year_to') }}" 
                                    min="1000" max="{{ date('Y') }}"
                                    placeholder="e.g., {{ date('Y') }}"
                                    class="form-control">
                            </div>
                            
                            <!-- Stock Status Filter -->
                            <div class="col-12 col-md-3">
                                <label for="available_copies_filter" class="form-label fw-semibold">Stock Status</label>
                                <select name="available_copies_filter" id="available_copies_filter" class="form-select">
                                    <option value="">All Stock Levels</option>
                                    <option value="low_stock" {{ request('available_copies_filter') == 'low_stock' ? 'selected' : '' }}>Low Stock (≤5)</option>
                                    <option value="out_of_stock" {{ request('available_copies_filter') == 'out_of_stock' ? 'selected' : '' }}>Out of Stock</option>
                                    <option value="in_stock" {{ request('available_copies_filter') == 'in_stock' ? 'selected' : '' }}>In Stock (>5)</option>
                                </select>
                            </div>
                            
                            <!-- Action Buttons -->
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search me-2"></i>Apply Filters
                                </button>
                                @if(request()->hasAny(['search', 'author_id', 'category_id', 'status', 'library_id', 'publisher', 'language', 'year_from', 'year_to', 'available_copies_filter']))
                                    <a href="{{ route('admin.books.index') }}" class="btn btn-secondary">
                                        <i class="fas fa-times me-2"></i>Clear All
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>

        <div class="row">
            <div class="col-12">
                <div class="table-responsive">
                    <table id="booksTable" class="table table-modern mb-0 w-100">
                        <thead>
                            <tr>
                                <th style="width: 4%;">#</th>
                                <th style="width: 7%;">Image</th>
                                <th style="width: 9%;">ISBN</th>
                                <th style="width: 18%;">Title</th>
                                <th style="width: 11%;">Author</th>
                                <th style="width: 9%;">Category</th>
                                <th style="width: 9%;">Available</th>
                                <th style="width: 13%;">Status</th>
                                <th style="width: 14%;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($books as $book)
                            <tr>
                                <td>{{ ($books->currentPage() - 1) * $books->perPage() + $loop->iteration }}</td>
                                <td>
                                    @if($book->cover_image)
                                        <img src="{{ Storage::url($book->cover_image) }}" alt="{{ $book->title }}" 
                                            class="img-thumbnail" style="width: 60px; height: 80px; object-fit: cover;">
                                    @else
                                        <div class="bg-light" style="width: 60px; height: 80px; display: flex; align-items: center; justify-content: center;">
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
                                    <span class="text-success fw-bold">{{ $book->effective_available_copies }}</span>
                                </td>
                                <td>
                                    @if($book->status === 'available')
                                        <span class="badge bg-success">Available</span>
                                    @else
                                        <span class="badge bg-danger">Unavailable</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('admin.books.show', $book) }}" class="btn btn-sm btn-info text-white" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.books.edit', $book) }}" class="btn btn-sm btn-primary" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('admin.books.destroy', $book) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this book?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger" title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="9" class="text-center py-4">
                                    <p class="text-muted mb-0">No books found.</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                @if($books->hasPages())
                <div class="mt-4">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
                        <div class="text-muted small">
                            @if($books->total() > 0)
                                Showing <strong>{{ $books->firstItem() }}</strong> to <strong>{{ $books->lastItem() }}</strong> of <strong>{{ $books->total() }}</strong> books
                            @else
                                No books found
                            @endif
                        </div>
                        <div class="pagination-wrapper">
                            {{ $books->appends(request()->query())->links('pagination::simple-bootstrap-5') }}
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    $(document).ready(function() {
        // Prevent DataTables from initializing on this table
        // since we're using Laravel pagination
        if ($.fn.DataTable.isDataTable('#booksTable')) {
            $('#booksTable').DataTable().destroy();
        }
    });
</script>
@endpush
@endsection

