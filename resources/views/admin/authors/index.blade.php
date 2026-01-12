@extends('layouts.admin')

@section('title', 'Authors Management')
@section('page-title', 'Authors Management')

@section('content')
<div class="card-modern">
    <div class="card-header-modern">
        <h5>
            <i class="fas fa-user-edit text-primary"></i>
            Authors Section
        </h5>
        <a href="{{ route('admin.authors.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Create
        </a>
    </div>
    <div class="card-body p-4">
        <div class="row">
            <div class="col-12">
                <div class="table-responsive">
                    <table id="authorsTable" class="table table-modern data-table mb-0 w-100">
                <thead>
                    <tr>
                        <th style="width: 5%;">#</th>
                        <th style="width: 15%;">Name</th>
                        <th style="width: 40%;">Bio</th>
                        <th style="width: 10%;">Books</th>
                        <th style="width: 15%;">Created At</th>
                        <th style="width: 15%;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($authors as $index => $author)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td class="fw-semibold">{{ $author->name }}</td>
                        <td>
                            @if($author->bio)
                                {{ Str::limit($author->bio, 50) }}
                            @else
                                <span class="text-muted">N/A</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-primary">{{ $author->books_count }} books</span>
                        </td>
                        <td>{{ $author->created_at->format('M d, Y') }}</td>
                        <td>
                            <div class="btn-group" role="group">
                                <a href="{{ route('admin.authors.show', $author) }}" class="btn btn-sm btn-info text-white" title="View">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('admin.authors.edit', $author) }}" class="btn btn-sm btn-primary" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('admin.authors.destroy', $author) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this author?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
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
