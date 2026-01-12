@extends('layouts.admin')

@section('title', 'Libraries Management')
@section('page-title', 'Libraries Management')

@section('content')
<div class="card-modern">
    <div class="card-header-modern">
        <h5>
            <i class="fas fa-building text-primary"></i>
            Libraries Section
        </h5>
        <a href="{{ route('admin.libraries.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Create
        </a>
    </div>
    <div class="card-body p-4">
        <div class="row">
            <div class="col-12">
                <div class="table-responsive" style="max-height: 600px; overflow-y: auto; overflow-x: auto;">
                    <table id="librariesTable" class="table table-modern data-table mb-0" style="min-width: 1000px;">
                        <thead>
                            <tr>
                                <th style="width: 5%;">#</th>
                                <th style="width: 15%;">Name</th>
                                <th style="width: 10%;">Code</th>
                                <th style="width: 12%;">Phone</th>
                                <th style="width: 15%;">Email</th>
                                <th style="width: 12%;">Staff</th>
                                <th style="width: 8%;">Books</th>
                                <th style="width: 8%;">Status</th>
                                <th style="width: 15%;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($libraries as $index => $library)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td class="fw-semibold">{{ $library->name }}</td>
                                <td><span class="badge bg-secondary">{{ $library->code }}</span></td>
                                <td>
                                    @if($library->phone)
                                        <i class="fas fa-phone text-muted me-1"></i>{{ $library->phone }}
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </td>
                                <td>
                                    @if($library->email)
                                        <i class="fas fa-envelope text-muted me-1"></i>{{ Str::limit($library->email, 25) }}
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </td>
                                <td>
                                    @if($library->staff->count() > 0)
                                        @foreach($library->staff->take(2) as $staff)
                                            <span class="badge bg-info mb-1 d-block">{{ $staff->name }}</span>
                                        @endforeach
                                        @if($library->staff->count() > 2)
                                            <span class="badge bg-secondary">+{{ $library->staff->count() - 2 }} more</span>
                                        @endif
                                    @else
                                        <span class="text-muted">No staff</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-primary">{{ $library->books->count() }} books</span>
                                </td>
                                <td>
                                    @if($library->is_active)
                                        <span class="badge bg-success">Active</span>
                                    @else
                                        <span class="badge bg-danger">Inactive</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('admin.libraries.show', $library) }}" class="btn btn-sm btn-info text-white" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.libraries.edit', $library) }}" class="btn btn-sm btn-primary" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="{{ route('admin.libraries.settings', $library) }}" class="btn btn-sm btn-warning text-white" title="Settings">
                                            <i class="fas fa-cog"></i>
                                        </a>
                                        <form action="{{ route('admin.libraries.destroy', $library) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this library?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger" title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="9" class="text-center py-4">
                                    <p class="text-muted mb-0">No libraries found. <a href="{{ route('admin.libraries.create') }}">Create one</a></p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

