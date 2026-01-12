@extends('layouts.student')

@section('title', 'Book Details')
@section('page-title', 'Book Details')

@php
use Illuminate\Support\Facades\Storage;
@endphp

@section('content')
<div class="card-modern">
    <div class="card-header-modern">
        <h5>
            <i class="fas fa-book text-primary"></i>
            Book Details
        </h5>
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
                    <p class="text-muted mb-1 small">Rack Number</p>
                    <p class="mb-0 fw-semibold">{{ $book->rack_number ?? 'N/A' }}</p>
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

        <!-- Request/Reserve Book Section -->
        <div class="border-top pt-4">
            @php
                $user = auth()->user();
                $activeBorrows = $user->getActiveBorrowsCount();
                $maxBooks = 2;
                $canBorrow = $user->canBorrowMoreBooks($maxBooks);
                
                // Check if user already has a request for this book (pending, hold, or approved)
                $existingRequest = \App\Models\BookRequest::where('user_id', $user->id)
                    ->where('book_id', $book->id)
                    ->whereIn('status', ['pending', 'hold', 'approved'])
                    ->first();
                $hasExistingRequest = $existingRequest !== null;
                
                // Check if user already has a book with same subject
                $hasSameSubjectBook = false;
                $sameSubjectBook = null;
                if ($book->subject) {
                    $sameSubjectBorrow = \App\Models\Borrow::where('user_id', $user->id)
                        ->where('status', 'borrowed')
                        ->whereHas('book', function($query) use ($book) {
                            $query->where('subject', $book->subject);
                        })
                        ->with('book')
                        ->first();
                    
                    if ($sameSubjectBorrow) {
                        $hasSameSubjectBook = true;
                        $sameSubjectBook = $sameSubjectBorrow->book;
                    }
                }
            @endphp
            
            <!-- Book Limit Info -->
            <div class="alert alert-info mb-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Your Book Status:</strong> 
                        <span class="badge bg-{{ $activeBorrows >= $maxBooks ? 'danger' : ($activeBorrows > 0 ? 'warning' : 'success') }}">
                            {{ $activeBorrows }} / {{ $maxBooks }} books issued
                        </span>
                    </div>
                    @if($activeBorrows >= $maxBooks)
                    <div>
                        <a href="{{ route('student.my-books') }}" class="btn btn-sm btn-warning">
                            <i class="fas fa-undo me-1"></i>Return Books
                        </a>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Same Subject Warning -->
            @if($hasSameSubjectBook && $sameSubjectBook)
            <div class="alert alert-warning mb-3">
                <div class="d-flex align-items-start">
                    <i class="fas fa-exclamation-triangle fa-2x me-3 mt-1"></i>
                    <div class="flex-grow-1">
                        <h6 class="alert-heading mb-2">Same Subject Book Already Issued!</h6>
                        <p class="mb-2">आपने पहले से ही <strong>"{{ $book->subject }}"</strong> subject की एक book issue कर रखी है:</p>
                        <p class="mb-2">
                            <strong>{{ $sameSubjectBook->title }}</strong>
                        </p>
                        <p class="mb-0">एक student एक subject की केवल <strong>1 book</strong> ही ले सकता है। कृपया पहले वह book return करें, फिर नई book request करें।</p>
                        <a href="{{ route('student.my-books') }}" class="btn btn-sm btn-warning mt-2">
                            <i class="fas fa-book-open me-1"></i>View My Books & Return
                        </a>
                    </div>
                </div>
            </div>
            @endif

            @if($book->isAvailable())
            @if($hasExistingRequest)
            <div class="alert alert-warning">
                <div class="d-flex align-items-start">
                    <i class="fas fa-exclamation-triangle fa-2x me-3 mt-1"></i>
                    <div class="flex-grow-1">
                        <h6 class="alert-heading mb-2">Already Requested!</h6>
                        <p class="mb-2">You already have a <strong>{{ ucfirst($existingRequest->status) }}</strong> request for this book.</p>
                        <p class="mb-0">Please wait for the current request to be processed. You can only request the same book once.</p>
                        <a href="{{ route('student.my-books') }}" class="btn btn-info mt-2">
                            <i class="fas fa-book-open me-2"></i>View My Requests
                        </a>
                    </div>
                </div>
            </div>
            <button type="button" class="btn btn-primary btn-lg" disabled>
                <i class="fas fa-bookmark me-2"></i>Already Requested
            </button>
            @elseif(!$canBorrow || $hasSameSubjectBook)
            <div class="alert alert-danger">
                <div class="d-flex align-items-start">
                    <i class="fas fa-exclamation-triangle fa-2x me-3 mt-1"></i>
                    <div class="flex-grow-1">
                        @if($hasSameSubjectBook)
                        <h6 class="alert-heading mb-2">Same Subject Book Already Issued!</h6>
                        <p class="mb-2">आपने पहले से ही <strong>"{{ $book->subject }}"</strong> subject की एक book issue कर रखी है।</p>
                        <p class="mb-2">एक student एक subject की केवल <strong>1 book</strong> ही ले सकता है।</p>
                        <p class="mb-0">कृपया पहले वह book return करें, फिर नई book request करें।</p>
                        @else
                        <h6 class="alert-heading mb-2">Book Limit Reached!</h6>
                        <p class="mb-2">You have already issued <strong>{{ $activeBorrows }} books</strong>. The maximum limit is <strong>{{ $maxBooks }} books</strong> per student.</p>
                        <p class="mb-0">Please return at least one book before requesting a new one.</p>
                        @endif
                        <a href="{{ route('student.my-books') }}" class="btn btn-warning mt-2">
                            <i class="fas fa-book-open me-2"></i>View My Books & Return
                        </a>
                    </div>
                </div>
            </div>
            <button type="button" class="btn btn-primary btn-lg" disabled>
                <i class="fas fa-bookmark me-2"></i>Request This Book ({{ $hasSameSubjectBook ? 'Same Subject' : 'Limit Reached' }})
            </button>
            @else
            <form action="{{ route('student.books.request', $book) }}" method="POST" id="bookRequestForm">
                @csrf
                <button type="submit" class="btn btn-primary btn-lg" id="requestBtn">
                    <i class="fas fa-bookmark me-2"></i>Request This Book
                </button>
            </form>
            @endif
            @else
            <div class="d-flex flex-column flex-sm-row gap-3">
                <div class="alert alert-warning mb-0 flex-grow-1">
                    <i class="fas fa-exclamation-triangle me-2"></i>This book is currently not available for borrowing
                </div>
                @php
                    $hasReservation = \App\Models\BookReservation::where('user_id', auth()->id())
                        ->where('book_id', $book->id)
                        ->whereIn('status', ['pending', 'available'])
                        ->exists();
                @endphp
                @if(!$hasReservation)
                @if(!$canBorrow)
                <div class="alert alert-danger mb-0">
                    <i class="fas fa-exclamation-triangle me-2"></i>You have reached the book limit. Return a book first to reserve.
                </div>
                @else
                <form action="{{ route('student.books.reserve', $book) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-warning btn-lg">
                        <i class="fas fa-clock me-2"></i>Reserve This Book
                    </button>
                </form>
                @endif
                @else
                <div class="alert alert-info mb-0">
                    <i class="fas fa-check-circle me-2"></i>You have already reserved this book
                </div>
                @endif
            </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const requestForm = document.getElementById('bookRequestForm');
    const requestBtn = document.getElementById('requestBtn');
    
    if (requestForm && requestBtn) {
        requestForm.addEventListener('submit', function(e) {
            const activeBorrows = {{ $activeBorrows }};
            const maxBooks = {{ $maxBooks }};
            const hasSameSubject = {{ $hasSameSubjectBook ? 'true' : 'false' }};
            const bookSubject = @json($book->subject ?? '');
            
            if (activeBorrows >= maxBooks || hasSameSubject) {
                e.preventDefault();
                
                let modalContent = '';
                if (hasSameSubject) {
                    modalContent = `
                        <div class="alert alert-warning">
                            <h6 class="alert-heading">Same Subject Book Already Issued!</h6>
                            <p class="mb-2">आपने पहले से ही <strong>"${bookSubject}"</strong> subject की एक book issue कर रखी है।</p>
                            <p class="mb-2">एक student एक subject की केवल <strong>1 book</strong> ही ले सकता है।</p>
                            <p class="mb-0"><strong>कृपया पहले वह book return करें, फिर नई book request करें।</strong></p>
                        </div>
                    `;
                } else {
                    modalContent = `
                        <div class="alert alert-danger">
                            <h6 class="alert-heading">You cannot request more books!</h6>
                            <p class="mb-2">You have already issued <strong>${activeBorrows} books</strong>.</p>
                            <p class="mb-2">The maximum limit is <strong>${maxBooks} books</strong> per student.</p>
                            <p class="mb-0"><strong>Please return at least one book before requesting a new one.</strong></p>
                        </div>
                    `;
                }
                
                // Show Bootstrap modal or alert
                const modal = `
                    <div class="modal fade" id="bookLimitModal" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header ${hasSameSubject ? 'bg-warning' : 'bg-danger'} text-white">
                                    <h5 class="modal-title">
                                        <i class="fas fa-exclamation-triangle me-2"></i>${hasSameSubject ? 'Same Subject Book Already Issued' : 'Book Limit Reached'}
                                    </h5>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    ${modalContent}
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                    <a href="{{ route('student.my-books') }}" class="btn btn-primary">
                                        <i class="fas fa-book-open me-2"></i>View My Books
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
                
                // Remove existing modal if any
                const existingModal = document.getElementById('bookLimitModal');
                if (existingModal) {
                    existingModal.remove();
                }
                
                // Add modal to body
                document.body.insertAdjacentHTML('beforeend', modal);
                
                // Show modal
                const bsModal = new bootstrap.Modal(document.getElementById('bookLimitModal'));
                bsModal.show();
                
                return false;
            }
        });
    }
});
</script>
@endpush
@endsection
