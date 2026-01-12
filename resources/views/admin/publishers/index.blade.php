@extends('layouts.admin')

@section('title', 'Publishers Management')
@section('page-title', 'Publishers Management')

@section('content')
<div class="card-modern">
    <div class="card-header-modern">
        <h5>
            <i class="fas fa-book-publisher text-primary"></i>
            Publishers Section
        </h5>
        <a href="{{ route('admin.publishers.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Create
        </a>
    </div>
    <div class="card-body p-4">
        <div class="row">
            <div class="col-12">
                <div class="table-responsive">
                    <table id="publishersTable" class="table table-modern data-table mb-0 w-100">
                        <thead>
                            <tr>
                                <th style="width: 5%;">#</th>
                                <th style="width: 20%;">Name</th>
                                <th style="width: 15%;">Email</th>
                                <th style="width: 12%;">Phone</th>
                                <th style="width: 10%;">Books</th>
                                <th style="width: 8%;">Status</th>
                                <th style="width: 15%;">Created At</th>
                                <th style="width: 15%;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($publishers as $index => $publisher)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td class="fw-semibold">{{ $publisher->name }}</td>
                                <td>
                                    @if($publisher->email)
                                        <a href="mailto:{{ $publisher->email }}">{{ $publisher->email }}</a>
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </td>
                                <td>
                                    @if($publisher->phone)
                                        {{ $publisher->phone }}
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-primary">{{ $publisher->books_count }} books</span>
                                </td>
                                <td>
                                    @if($publisher->is_active)
                                        <span class="badge bg-success">Active</span>
                                    @else
                                        <span class="badge bg-secondary">Inactive</span>
                                    @endif
                                </td>
                                <td>{{ $publisher->created_at->format('M d, Y') }}</td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('admin.publishers.show', $publisher) }}" class="btn btn-sm btn-info text-white" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.publishers.edit', $publisher) }}" class="btn btn-sm btn-primary" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('admin.publishers.destroy', $publisher) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this publisher?')">
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

