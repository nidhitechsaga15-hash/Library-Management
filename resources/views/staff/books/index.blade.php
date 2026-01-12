@extends('layouts.staff')

@section('title', 'Books')
@section('page-title', 'Books')

@php
use Illuminate\Support\Facades\Storage;
@endphp

@section('content')
<div class="card-modern">
    <div class="card-header-modern">
        <h5>
            <i class="fas fa-book text-primary"></i>
            Books Section
        </h5>
    </div>
    <div class="card-body p-4">
        <!-- Search and Filter -->
        <form method="GET" action="{{ route('staff.books.index') }}" class="mb-4">
            <div class="row g-3">
                <div class="col-12 col-sm-6 col-md-3">
                    <input type="text" name="search" value="{{ request('search') }}" 
                        placeholder="Search by title or ISBN..."
                        class="form-control form-control-lg">
                </div>
                <div class="col-12 col-sm-6 col-md-3">
                    <select name="category_id" class="form-select form-select-lg">
                        <option value="">All Categories</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-sm-6 col-md-3">
                    <select name="status" class="form-select form-select-lg">
                        <option value="">All Status</option>
                        <option value="available" {{ request('status') == 'available' ? 'selected' : '' }}>Available</option>
                        <option value="unavailable" {{ request('status') == 'unavailable' ? 'selected' : '' }}>Unavailable</option>
                    </select>
                </div>
                <div class="col-12 col-sm-6 col-md-3">
                    <button type="submit" class="btn btn-primary btn-lg w-100">
                        <i class="fas fa-search me-2"></i>Filter
                    </button>
                </div>
            </div>
        </form>

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
                        <th style="width: 10%;">Copies</th>
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
                            <a href="{{ route('staff.books.show', $book) }}" class="btn btn-sm btn-info text-white" title="View">
                                <i class="fas fa-eye"></i>
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
