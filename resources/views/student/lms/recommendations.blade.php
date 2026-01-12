@extends('layouts.student')

@section('title', 'Course Recommendations')
@section('page-title', 'LMS Course Recommendations')

@section('content')
<div class="card-modern mb-4">
    <div class="card-header-modern">
        <h5>
            <i class="fas fa-graduation-cap text-primary"></i>
            Course-Specific Book Recommendations
        </h5>
    </div>
    <div class="card-body p-4">
        <div class="row mb-3">
            <div class="col-md-6">
                <h6>Your Course Details</h6>
                <table class="table table-borderless table-sm">
                    @if($user->course)
                    <tr>
                        <td><strong>Course:</strong></td>
                        <td>{{ $user->course }}</td>
                    </tr>
                    @endif
                    @if($user->semester)
                    <tr>
                        <td><strong>Semester:</strong></td>
                        <td>{{ $user->semester }}</td>
                    </tr>
                    @endif
                    @if($user->year)
                    <tr>
                        <td><strong>Year:</strong></td>
                        <td>{{ $user->year }}</td>
                    </tr>
                    @endif
                    @if($user->batch)
                    <tr>
                        <td><strong>Batch:</strong></td>
                        <td>{{ $user->batch }}</td>
                    </tr>
                    @endif
                </table>
            </div>
            <div class="col-md-6">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>LMS Integration:</strong> Books are automatically matched to your course, semester, and batch based on LMS data.
                </div>
            </div>
        </div>
    </div>
</div>

@if($allBooks->isEmpty())
<div class="card-modern">
    <div class="card-body text-center py-5">
        <i class="fas fa-book-open fa-3x text-muted mb-3"></i>
        <p class="text-muted">No course-specific recommendations found.</p>
        <a href="{{ route('student.books.index') }}" class="btn btn-primary">
            <i class="fas fa-book me-2"></i>Browse All Books
        </a>
    </div>
</div>
@else
<div class="card-modern">
    <div class="card-header-modern">
        <h5>
            <i class="fas fa-book text-primary"></i>
            Recommended Books ({{ $allBooks->count() }})
        </h5>
    </div>
    <div class="card-body p-4">
        <div class="row g-3">
            @foreach($allBooks as $book)
            <div class="col-12 col-md-6 col-lg-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body">
                        <h6 class="card-title mb-2">
                            <a href="{{ route('student.books.show', $book) }}" class="text-decoration-none">
                                {{ $book->title }}
                            </a>
                        </h6>
                        <p class="text-muted small mb-2">
                            <i class="fas fa-user me-1"></i>
                            {{ $book->author->name ?? 'Unknown Author' }}
                        </p>
                        @if($book->isbn)
                        <p class="text-muted small mb-2">
                            <i class="fas fa-barcode me-1"></i>
                            ISBN: {{ $book->isbn }}
                        </p>
                        @endif
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <span class="badge bg-{{ $book->available_copies > 0 ? 'success' : 'danger' }}">
                                {{ $book->available_copies }} Available
                            </span>
                            <a href="{{ route('student.books.show', $book) }}" class="btn btn-sm btn-primary">
                                <i class="fas fa-eye me-1"></i>View
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endif
@endsection

