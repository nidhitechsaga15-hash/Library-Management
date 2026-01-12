@extends('layouts.admin')

@section('title', 'Total Books Report')
@section('page-title', 'Total Books Report')

@section('content')
<div class="card-modern">
    <div class="card-header-modern">
        <h5>
            <i class="fas fa-book text-primary"></i>
            Total Books Report
        </h5>
        <a href="{{ route('admin.reports') }}" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left me-2"></i>Back to Reports
        </a>
    </div>
    <div class="card-body p-4">
        <!-- Statistics -->
        <div class="row g-3 mb-4">
            <div class="col-12 col-sm-6 col-md-4">
                <div class="stat-card border-start border-primary border-4">
                    <div class="stat-content">
                        <div class="stat-label">Total Books</div>
                        <div class="stat-value text-primary">{{ $totalBooks }}</div>
                    </div>
                    <div class="stat-icon primary">
                        <i class="fas fa-book"></i>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-md-4">
                <div class="stat-card border-start border-success border-4">
                    <div class="stat-content">
                        <div class="stat-label">Available Books</div>
                        <div class="stat-value text-success">{{ $availableBooks }}</div>
                    </div>
                    <div class="stat-icon success">
                        <i class="fas fa-check-circle"></i>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-md-4">
                <div class="stat-card border-start border-danger border-4">
                    <div class="stat-content">
                        <div class="stat-label">Unavailable Books</div>
                        <div class="stat-value text-danger">{{ $unavailableBooks }}</div>
                    </div>
                    <div class="stat-icon danger">
                        <i class="fas fa-times-circle"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Search Form -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border">
                    <div class="card-body">
                        <form method="GET" action="{{ route('admin.reports.total-books') }}" class="row g-3">
                            <div class="col-12 col-md-6">
                                <label for="category_id" class="form-label fw-semibold">Category</label>
                                <select name="category_id" id="category_id" class="form-select form-select-lg">
                                    <option value="">All Categories</option>
                                    @foreach(\App\Models\Category::all() as $category)
                                        <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                                            {{ $category->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12 col-md-6">
                                <label for="status" class="form-label fw-semibold">Status</label>
                                <select name="status" id="status" class="form-select form-select-lg">
                                    <option value="">All Status</option>
                                    <option value="available" {{ request('status') == 'available' ? 'selected' : '' }}>Available</option>
                                    <option value="unavailable" {{ request('status') == 'unavailable' ? 'selected' : '' }}>Unavailable</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <div class="form-actions">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-search me-2"></i>Search
                                    </button>
                                    <a href="{{ route('admin.reports.total-books') }}" class="btn btn-secondary">
                                        <i class="fas fa-redo me-2"></i>Reset
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Books Table -->
        <div class="row">
            <div class="col-12">
                <div class="table-responsive">
            <table id="totalBooksTable" class="table table-modern data-table mb-0 w-100">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>ISBN</th>
                        <th>Title</th>
                        <th>Author</th>
                        <th>Category</th>
                        <th>Copies</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($books as $index => $book)
                    <tr>
                        <td>{{ $index + 1 }}</td>
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
