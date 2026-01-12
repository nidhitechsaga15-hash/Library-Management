@extends('layouts.admin')

@section('title', 'Category Details')
@section('page-title', 'Category Details')

@section('content')
<div class="card-modern">
    <div class="card-header-modern">
        <h5>
            <i class="fas fa-tag text-primary"></i>
            Category Details
        </h5>
        <div>
            <a href="{{ route('admin.categories.edit', $category) }}" class="btn btn-primary">
                <i class="fas fa-edit me-2"></i>Edit
            </a>
            <a href="{{ route('admin.categories.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i>Back
            </a>
        </div>
    </div>
    <div class="card-body p-4">
        <!-- Category Information -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="mb-4">
                    <h3 class="mb-3 fw-bold">
                        <i class="fas fa-tag text-primary me-2"></i>{{ $category->name }}
                    </h3>
                    @if($category->description)
                    <div class="alert alert-light border-start border-primary border-4">
                        <p class="mb-0">
                            <strong><i class="fas fa-info-circle me-2"></i>Description:</strong><br>
                            {{ $category->description }}
                        </p>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Books Section -->
        <div class="border-top pt-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="mb-0">
                    <i class="fas fa-book text-primary me-2"></i>
                    Books in {{ $category->name }}
                    <span class="badge bg-primary ms-2">{{ $category->books->count() }}</span>
                </h5>
            </div>
            
            @if($category->books->count() > 0)
            <div class="table-responsive">
                <table class="table table-modern table-hover mb-0">
                    <thead>
                        <tr>
                            <th style="width: 5%;">#</th>
                            <th style="width: 35%;">Title</th>
                            <th style="width: 20%;">Author</th>
                            <th style="width: 15%;">ISBN</th>
                            <th style="width: 10%;">Copies</th>
                            <th style="width: 10%;">Status</th>
                            <th style="width: 15%;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($category->books as $index => $book)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td class="fw-semibold">{{ $book->title }}</td>
                            <td>{{ $book->author->name }}</td>
                            <td><code class="text-muted">{{ $book->isbn }}</code></td>
                            <td>
                                <span class="badge bg-success">{{ $book->effective_available_copies }}</span>
                            </td>
                            <td>
                                @if($book->status === 'available')
                                    <span class="badge bg-success">Available</span>
                                @else
                                    <span class="badge bg-danger">Unavailable</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('admin.books.show', $book) }}" class="btn btn-sm btn-info text-white" title="View">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('admin.books.edit', $book) }}" class="btn btn-sm btn-primary" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle me-2"></i>
                No books found in this category.
            </div>
            @endif
        </div>
    </div>
</div>
@endsection




