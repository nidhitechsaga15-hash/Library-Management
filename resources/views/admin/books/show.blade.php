@extends('layouts.admin')

@section('title', 'Book Details')
@section('page-title', 'Book Details')

@php
use Illuminate\Support\Facades\Storage;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
@endphp

@section('content')
<div class="card-modern">
    <div class="card-header-modern">
        <h5>
            <i class="fas fa-book text-primary"></i>
            Book Details
        </h5>
        <a href="{{ route('admin.books.edit', $book) }}" class="btn btn-primary">
            <i class="fas fa-edit me-2"></i>Edit
        </a>
    </div>
    <div class="card-body p-4">
        <!-- Book Header Section -->
        <div class="row mb-4">
            <div class="col-12 col-md-3 col-lg-2 mb-3 mb-md-0">
                @if($book->cover_image)
                <img src="{{ Storage::url($book->cover_image) }}" alt="Book Cover" 
                    class="img-fluid rounded shadow-sm border" style="max-height: 300px; width: 100%; object-fit: cover;">
                @else
                <div class="bg-light rounded shadow-sm border d-flex align-items-center justify-content-center" 
                    style="height: 300px; width: 100%;">
                    <i class="fas fa-book fa-4x text-muted"></i>
                </div>
                @endif
            </div>
            <div class="col-12 col-md-9 col-lg-10">
                <h3 class="mb-2">{{ $book->title }}</h3>
                <p class="text-muted mb-3">
                    <i class="fas fa-barcode me-2"></i>ISBN: {{ $book->isbn }}
                </p>
                @if($book->description)
                <p class="text-muted">{{ Str::limit($book->description, 200) }}</p>
                @endif
            </div>
        </div>

        <!-- Book Details Grid -->
        <div class="row g-3 mb-4">
            <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                <div class="p-3 border rounded">
                    <p class="text-muted mb-1 small">Author</p>
                    <p class="mb-0 fw-semibold">{{ $book->author->name }}</p>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                <div class="p-3 border rounded">
                    <p class="text-muted mb-1 small">Category</p>
                    <p class="mb-0 fw-semibold">{{ $book->category->name }}</p>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                <div class="p-3 border rounded">
                    <p class="text-muted mb-1 small">Publisher</p>
                    <p class="mb-0 fw-semibold">{{ $book->publisher ?? 'N/A' }}</p>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                <div class="p-3 border rounded">
                    <p class="text-muted mb-1 small">Edition</p>
                    <p class="mb-0 fw-semibold">{{ $book->edition ?? 'N/A' }}</p>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                <div class="p-3 border rounded">
                    <p class="text-muted mb-1 small">Publication Year</p>
                    <p class="mb-0 fw-semibold">{{ $book->publication_year ?? 'N/A' }}</p>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                <div class="p-3 border rounded">
                    <p class="text-muted mb-1 small">Rack Number</p>
                    <p class="mb-0 fw-semibold">{{ $book->rack_number ?? 'N/A' }}</p>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                <div class="p-3 border rounded">
                    <p class="text-muted mb-1 small">Language</p>
                    <p class="mb-0 fw-semibold">{{ $book->language ?? 'N/A' }}</p>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                <div class="p-3 border rounded">
                    <p class="text-muted mb-1 small">Pages</p>
                    <p class="mb-0 fw-semibold">{{ $book->pages ?? 'N/A' }}</p>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                <div class="p-3 border rounded">
                    <p class="text-muted mb-1 small">Available Copies</p>
                    <p class="mb-0 fw-semibold text-{{ $book->effective_available_copies > 0 ? 'success' : 'danger' }}">
                        {{ $book->effective_available_copies }}
                    </p>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                <div class="p-3 border rounded">
                    <p class="text-muted mb-1 small">Status</p>
                    <p class="mb-0">
                        @if($book->status === 'available')
                            <span class="badge bg-success">Available</span>
                        @else
                            <span class="badge bg-danger">Unavailable</span>
                        @endif
                    </p>
                </div>
            </div>
        </div>

        <!-- Description Section -->
        @if($book->description)
        <div class="border-top pt-4 mb-4">
            <h5 class="mb-3">
                <i class="fas fa-align-left me-2 text-primary"></i>Description
            </h5>
            <p class="text-muted">{{ $book->description }}</p>
        </div>
        @endif

        <!-- QR Code Section -->
        <div class="border-top pt-4 mb-4">
            <h5 class="mb-4">
                <i class="fas fa-qrcode me-2 text-primary"></i>QR Code / Barcode
            </h5>
            <div class="row align-items-center">
                <div class="col-12 col-md-4 col-lg-3 mb-3 mb-md-0">
                    <div class="bg-white p-3 border rounded text-center">
                        {!! QrCode::size(150)->generate(route('admin.books.show', $book)) !!}
                    </div>
                </div>
                <div class="col-12 col-md-8 col-lg-9">
                    <p class="text-muted mb-2">
                        <i class="fas fa-info-circle me-2"></i>Scan this QR code to quickly access book details
                    </p>
                    <p class="mb-1"><strong>ISBN:</strong> {{ $book->isbn }}</p>
                    <p class="mb-0"><strong>Book ID:</strong> {{ $book->id }}</p>
                </div>
            </div>
        </div>

        <!-- Borrow History Section -->
        <div class="border-top pt-4">
            <h5 class="mb-4">
                <i class="fas fa-history me-2 text-primary"></i>Borrow History
            </h5>
            @if($book->borrows->count() > 0)
            <div class="row">
                <div class="col-12">
                    <div class="table-responsive">
                        <table id="borrowHistoryTable" class="table table-modern data-table mb-0 w-100">
                            <thead>
                                <tr>
                                    <th style="width: 5%;">#</th>
                                    <th style="width: 25%;">User</th>
                                    <th style="width: 15%;">Borrow Date</th>
                                    <th style="width: 15%;">Due Date</th>
                                    <th style="width: 15%;">Return Date</th>
                                    <th style="width: 10%;">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($book->borrows as $index => $borrow)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td class="fw-semibold">{{ $borrow->user->name }}</td>
                                    <td>{{ $borrow->borrow_date->format('M d, Y') }}</td>
                                    <td>{{ $borrow->due_date->format('M d, Y') }}</td>
                                    <td>
                                        @if($borrow->return_date)
                                            {{ $borrow->return_date->format('M d, Y') }}
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($borrow->status === 'borrowed')
                                            <span class="badge bg-warning text-dark">Borrowed</span>
                                        @else
                                            <span class="badge bg-success">Returned</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @else
            <p class="text-muted text-center py-4">No borrow history for this book.</p>
            @endif
        </div>
    </div>
</div>
@endsection
