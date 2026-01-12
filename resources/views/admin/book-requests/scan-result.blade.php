@extends('layouts.admin')

@section('title', 'Book Request Details - QR Scan')
@section('page-title', 'Book Request Details')

@section('content')
<div class="card-modern">
    <div class="card-header-modern">
        <h5>
            <i class="fas fa-book text-primary"></i>
            Book Request Details
        </h5>
        <div>
            <a href="{{ route('admin.book-requests.scan') }}" class="btn btn-secondary">
                <i class="fas fa-qrcode me-2"></i>Scan Another
            </a>
            <a href="{{ route('admin.book-requests.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i>Back to Requests
            </a>
        </div>
    </div>
    <div class="card-body p-4">
        <div class="row g-4">
            <!-- Book Information -->
            <div class="col-12 col-md-6">
                <div class="card border">
                    <div class="card-header bg-primary text-white">
                        <h6 class="mb-0"><i class="fas fa-book me-2"></i>Book Information</h6>
                    </div>
                    <div class="card-body">
                        <table class="table table-borderless mb-0">
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
                                <td>{{ $book->author->name ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <td class="fw-semibold">Subject:</td>
                                <td>{{ $book->subject ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <td class="fw-semibold">Book Code:</td>
                                <td><span class="badge bg-info">{{ $book->isbn }}</span></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Book Location -->
            <div class="col-12 col-md-6">
                <div class="card border">
                    <div class="card-header bg-success text-white">
                        <h6 class="mb-0"><i class="fas fa-map-marker-alt me-2"></i>Book Location</h6>
                    </div>
                    <div class="card-body">
                        <div class="location-display">
                            <div class="location-item mb-3">
                                <i class="fas fa-archive text-primary me-2"></i>
                                <strong>Almirah:</strong> 
                                <span class="badge bg-primary">{{ $book->almirah ?? 'Not Set' }}</span>
                            </div>
                            <div class="location-item mb-3">
                                <i class="fas fa-layer-group text-info me-2"></i>
                                <strong>Row:</strong> 
                                <span class="badge bg-info">{{ $book->row ?? 'Not Set' }}</span>
                            </div>
                            <div class="location-item mb-3">
                                <i class="fas fa-hashtag text-warning me-2"></i>
                                <strong>Serial:</strong> 
                                <span class="badge bg-warning">{{ $book->book_serial ?? 'Not Set' }}</span>
                            </div>
                            <div class="mt-3 p-3 bg-light rounded">
                                <strong>Full Location:</strong><br>
                                <span class="text-primary">{{ $book->full_location }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Request Information -->
            <div class="col-12 col-md-6">
                <div class="card border">
                    <div class="card-header bg-info text-white">
                        <h6 class="mb-0"><i class="fas fa-user me-2"></i>Request By</h6>
                    </div>
                    <div class="card-body">
                        <table class="table table-borderless mb-0">
                            <tr>
                                <td class="fw-semibold" style="width: 40%;">Name:</td>
                                <td>{{ $user->name }}</td>
                            </tr>
                            <tr>
                                <td class="fw-semibold">Email:</td>
                                <td>{{ $user->email }}</td>
                            </tr>
                            <tr>
                                <td class="fw-semibold">Student ID:</td>
                                <td>{{ $user->student_id ?? 'N/A' }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Request Status -->
            <div class="col-12 col-md-6">
                <div class="card border">
                    <div class="card-header bg-warning text-white">
                        <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>Request Status</h6>
                    </div>
                    <div class="card-body">
                        <table class="table table-borderless mb-0">
                            <tr>
                                <td class="fw-semibold" style="width: 40%;">Status:</td>
                                <td>
                                    @if($request->status === 'hold')
                                        <span class="badge bg-primary">HOLD</span>
                                    @elseif($request->status === 'approved')
                                        <span class="badge bg-success">APPROVED</span>
                                    @else
                                        <span class="badge bg-secondary">{{ strtoupper($request->status) }}</span>
                                    @endif
                                </td>
                            </tr>
                            @if($request->hold_expires_at)
                            <tr>
                                <td class="fw-semibold">Pickup Deadline:</td>
                                <td>
                                    <span class="text-danger">
                                        <i class="fas fa-clock me-1"></i>
                                        {{ $request->hold_expires_at->format('d M Y, h:i A') }}
                                    </span>
                                    @if($request->hold_expires_at->isPast())
                                        <span class="badge bg-danger ms-2">EXPIRED</span>
                                    @else
                                        <span class="badge bg-warning ms-2">{{ $request->hold_expires_at->diffForHumans() }}</span>
                                    @endif
                                </td>
                            </tr>
                            @endif
                            <tr>
                                <td class="fw-semibold">Request Date:</td>
                                <td>{{ $request->created_at->format('d M Y, h:i A') }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="col-12">
                <div class="card border">
                    <div class="card-body">
                        <h6 class="mb-3">Actions</h6>
                        <div class="d-flex gap-2 flex-wrap">
                            @if(in_array($request->status, ['hold', 'approved']))
                                <form action="{{ route('admin.book-requests.issue', $request) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-success btn-lg" onclick="return confirm('Issue this book to {{ $user->name }}?')">
                                        <i class="fas fa-check-circle me-2"></i>Issue Book
                                    </button>
                                </form>
                            @endif

                            @if($request->status === 'hold' || $request->status === 'approved')
                                <form action="{{ route('admin.book-requests.reject', $request) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-danger btn-lg" onclick="return confirm('Cancel this request? Stock will be returned.')">
                                        <i class="fas fa-times-circle me-2"></i>Cancel Request
                                    </button>
                                </form>
                            @endif

                            <a href="{{ route('admin.book-requests.index') }}" class="btn btn-secondary btn-lg">
                                <i class="fas fa-list me-2"></i>View All Requests
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

