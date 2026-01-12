@extends('layouts.admin')

@section('title', 'Book Location')
@section('page-title', 'Book Location')

@php
use SimpleSoftwareIO\QrCode\Facades\QrCode;
@endphp

@section('content')
<div class="card-modern">
    <div class="card-header-modern">
        <h5>
            <i class="fas fa-map-marker-alt text-primary"></i>
            Book Location Information
        </h5>
    </div>
    <div class="card-body p-4">
        <div class="row">
            <div class="col-12 col-md-6">
                <h6 class="fw-bold mb-3">Book Information</h6>
                <table class="table table-borderless">
                    <tr>
                        <td class="fw-semibold" style="width: 40%;">Title:</td>
                        <td>{{ $book->title }}</td>
                    </tr>
                    <tr>
                        <td class="fw-semibold">ISBN:</td>
                        <td>{{ $book->isbn }}</td>
                    </tr>
                    <tr>
                        <td class="fw-semibold">Author:</td>
                        <td>{{ $book->author->name }}</td>
                    </tr>
                    <tr>
                        <td class="fw-semibold">Category:</td>
                        <td><span class="badge bg-info">{{ $book->category->name }}</span></td>
                    </tr>
                </table>
            </div>

            <div class="col-12 col-md-6">
                <h6 class="fw-bold mb-3">Location Details</h6>
                <div class="alert alert-info">
                    <h5 class="mb-3"><i class="fas fa-map-marker-alt me-2"></i>Book Location</h5>
                    
                    @if($library)
                    <div class="mb-2">
                        <strong>Library:</strong> {{ $library->name }}
                        <br><small class="text-muted">Code: {{ $library->code }}</small>
                    </div>
                    @endif

                    @if($book->almirah)
                    <div class="mb-2">
                        <strong><i class="fas fa-archive me-2"></i>Almirah:</strong> 
                        <span class="badge bg-primary fs-6">{{ $book->almirah }}</span>
                    </div>
                    @endif

                    @if($book->row)
                    <div class="mb-2">
                        <strong><i class="fas fa-layer-group me-2"></i>Row:</strong> 
                        <span class="badge bg-success fs-6">{{ $book->row }}</span>
                    </div>
                    @endif

                    @if($book->rack_number)
                    <div class="mb-2">
                        <strong><i class="fas fa-bookmark me-2"></i>Rack Number:</strong> 
                        <span class="badge bg-warning text-dark fs-6">{{ $book->rack_number }}</span>
                    </div>
                    @endif

                    @if(!$book->almirah && !$book->row && !$book->rack_number)
                    <div class="text-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Location information not set for this book.
                    </div>
                    @endif
                </div>

                @if($request->collection_deadline)
                <div class="alert alert-warning">
                    <strong>Collection Deadline:</strong> 
                    {{ $request->collection_deadline->format('M d, Y h:i A') }}
                    <br>
                    <small>Please collect the book before this date.</small>
                </div>
                @endif
            </div>

            <div class="col-12 text-center mt-4">
                <h6 class="fw-bold mb-3">QR Code for This Book</h6>
                <div class="d-inline-block p-3 bg-light rounded">
                    {!! QrCode::size(250)->generate(route('book.location.qr', ['request' => $request->id])) !!}
                </div>
                <p class="text-muted mt-3">
                    <i class="fas fa-info-circle me-2"></i>
                    Scan this QR code to view book location information
                </p>
            </div>
        </div>
    </div>
</div>
@endsection

