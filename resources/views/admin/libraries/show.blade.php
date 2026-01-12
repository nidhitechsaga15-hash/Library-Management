@extends('layouts.admin')

@section('title', 'Library Details')
@section('page-title', 'Library Details')

@section('content')
<div class="card-modern">
    <div class="card-header-modern">
        <h5>
            <i class="fas fa-building text-primary"></i>
            {{ $library->name }}
        </h5>
        <div>
            <a href="{{ route('admin.libraries.edit', $library) }}" class="btn btn-primary">
                <i class="fas fa-edit me-2"></i>Edit
            </a>
            <a href="{{ route('admin.libraries.settings', $library) }}" class="btn btn-warning text-white">
                <i class="fas fa-cog me-2"></i>Settings
            </a>
        </div>
    </div>
    <div class="card-body p-4">
        <div class="row g-4">
            <div class="col-12 col-md-6">
                <h6 class="fw-bold mb-3">Basic Information</h6>
                <table class="table table-borderless">
                    <tr>
                        <td class="fw-semibold" style="width: 40%;">Name:</td>
                        <td>{{ $library->name }}</td>
                    </tr>
                    <tr>
                        <td class="fw-semibold">Code:</td>
                        <td><span class="badge bg-secondary">{{ $library->code }}</span></td>
                    </tr>
                    <tr>
                        <td class="fw-semibold">Status:</td>
                        <td>
                            @if($library->is_active)
                                <span class="badge bg-success">Active</span>
                            @else
                                <span class="badge bg-danger">Inactive</span>
                            @endif
                        </td>
                    </tr>
                    @if($library->address)
                    <tr>
                        <td class="fw-semibold">Address:</td>
                        <td>{{ $library->address }}</td>
                    </tr>
                    @endif
                    @if($library->phone)
                    <tr>
                        <td class="fw-semibold">Phone:</td>
                        <td>{{ $library->phone }}</td>
                    </tr>
                    @endif
                    @if($library->email)
                    <tr>
                        <td class="fw-semibold">Email:</td>
                        <td>{{ $library->email }}</td>
                    </tr>
                    @endif
                </table>
            </div>

            <div class="col-12 col-md-6">
                <h6 class="fw-bold mb-3">Library Settings</h6>
                @if($library->settings)
                <table class="table table-borderless">
                    <tr>
                        <td class="fw-semibold" style="width: 50%;">Issue Duration:</td>
                        <td>{{ $library->settings->book_issue_duration_days }} days</td>
                    </tr>
                    <tr>
                        <td class="fw-semibold">Collection Deadline:</td>
                        <td>{{ $library->settings->book_collection_deadline_days }} days</td>
                    </tr>
                    <tr>
                        <td class="fw-semibold">Max Books/Student:</td>
                        <td>{{ $library->settings->max_books_per_student }}</td>
                    </tr>
                    <tr>
                        <td class="fw-semibold">Max Books/Subject:</td>
                        <td>{{ $library->settings->max_books_per_subject }}</td>
                    </tr>
                    <tr>
                        <td class="fw-semibold">Fine Per Day:</td>
                        <td>₹{{ number_format($library->settings->fine_per_day, 2) }}</td>
                    </tr>
                </table>
                @else
                <p class="text-muted">No settings configured yet.</p>
                @endif
            </div>

            @if($library->description)
            <div class="col-12">
                <h6 class="fw-bold mb-3">Description</h6>
                <p>{{ $library->description }}</p>
            </div>
            @endif

            <div class="col-12">
                <h6 class="fw-bold mb-3">Assigned Staff</h6>
                @if($library->staff->count() > 0)
                <div class="row g-2">
                    @foreach($library->staff as $staff)
                    <div class="col-12 col-sm-6 col-md-4">
                        <div class="card border">
                            <div class="card-body">
                                <h6 class="mb-1">{{ $staff->name }}</h6>
                                <small class="text-muted">{{ $staff->email }}</small>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <p class="text-muted">No staff assigned to this library.</p>
                @endif
            </div>

            <div class="col-12">
                <h6 class="fw-bold mb-3">Books in Library</h6>
                <p class="text-muted">Total: <strong>{{ $library->books->count() }}</strong> books</p>
                @if($library->books->count() > 0)
                <div class="table-responsive">
                    <table class="table table-modern">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Author</th>
                                <th>Category</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($library->books->take(10) as $book)
                            <tr>
                                <td>{{ $book->title }}</td>
                                <td>{{ $book->author->name }}</td>
                                <td><span class="badge bg-info">{{ $book->category->name }}</span></td>
                                <td>
                                    @if($book->status === 'available')
                                        <span class="badge bg-success">Available</span>
                                    @else
                                        <span class="badge bg-danger">Unavailable</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('admin.books.show', $book) }}" class="btn btn-sm btn-info text-white">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    @if($library->books->count() > 10)
                    <p class="text-center mt-3">
                        <a href="{{ route('admin.books.index', ['library_id' => $library->id]) }}" class="btn btn-sm btn-primary">
                            View All Books
                        </a>
                    </p>
                    @endif
                </div>
                @else
                <p class="text-muted">No books in this library yet.</p>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

