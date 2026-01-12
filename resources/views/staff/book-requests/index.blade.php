@extends('layouts.staff')

@section('title', 'Book Requests')
@section('page-title', 'Book Requests')

@section('content')
<div class="card-modern">
    <div class="card-header-modern">
        <h5>
            <i class="fas fa-hand-paper text-primary"></i>
            Book Requests
        </h5>
    </div>
    <div class="card-body p-4">
        <!-- Statistics -->
        <div class="row g-3 mb-4">
            <div class="col-12 col-sm-6 col-md-4">
                <div class="stat-card border-start border-warning border-4">
                    <div class="stat-content">
                        <div class="stat-label">Pending Requests</div>
                        <div class="stat-value text-warning">{{ $pendingCount }}</div>
                    </div>
                    <div class="stat-icon warning">
                        <i class="fas fa-clock"></i>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-md-4">
                <div class="stat-card border-start border-success border-4">
                    <div class="stat-content">
                        <div class="stat-label">Approved Requests</div>
                        <div class="stat-value text-success">{{ $approvedCount }}</div>
                    </div>
                    <div class="stat-icon success">
                        <i class="fas fa-check-circle"></i>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-md-4">
                <div class="stat-card border-start border-info border-4">
                    <div class="stat-content">
                        <div class="stat-label">Total Requests</div>
                        <div class="stat-value text-info">{{ $requests->count() }}</div>
                    </div>
                    <div class="stat-icon info">
                        <i class="fas fa-list"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Requests Table -->
        <div class="row">
            <div class="col-12">
                <div class="table-responsive">
                    <table id="bookRequestsTable" class="table table-modern data-table mb-0 w-100">
                <thead>
                    <tr>
                        <th style="width: 5%;">#</th>
                        <th style="width: 18%;">Student</th>
                        <th style="width: 20%;">Book</th>
                        <th style="width: 12%;">Request Date</th>
                        <th style="width: 10%;">Status</th>
                        <th style="width: 35%;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($requests as $index => $request)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>
                            <div class="fw-semibold">{{ $request->user->name }}</div>
                            <small class="text-muted">{{ $request->user->email }}</small>
                            @if($request->user->student_id)
                            <div class="text-xs text-muted">ID: {{ $request->user->student_id }}</div>
                            @endif
                        </td>
                        <td>
                            <div class="fw-semibold">{{ $request->book->title }}</div>
                            <small class="text-muted">ISBN: {{ $request->book->isbn }}</small>
                            <div class="text-xs text-muted">Available: {{ $request->book->available_copies }} copies</div>
                        </td>
                        <td>
                            <div>{{ $request->created_at->format('M d, Y') }}</div>
                            <small class="text-muted">{{ $request->created_at->format('h:i A') }}</small>
                        </td>
                        <td>
                            @if($request->status === 'pending')
                                <span class="badge bg-warning">Pending</span>
                            @elseif($request->status === 'approved')
                                <span class="badge bg-info">Approved</span>
                            @elseif($request->status === 'rejected')
                                <span class="badge bg-danger">Rejected</span>
                            @else
                                <span class="badge bg-success">Issued</span>
                            @endif
                        </td>
                        <td>
                            @if($request->status === 'pending')
                                <div class="btn-group" role="group">
                                    <form action="{{ route('staff.book-requests.approve', $request) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-success" title="Approve">
                                            <i class="fas fa-check"></i> Approve
                                        </button>
                                    </form>
                                    <form action="{{ route('staff.book-requests.reject', $request) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure?')">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-danger" title="Reject">
                                            <i class="fas fa-times"></i> Reject
                                        </button>
                                    </form>
                                </div>
                            @elseif($request->status === 'approved')
                                <form action="{{ route('staff.book-requests.issue', $request) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-primary">
                                        <i class="fas fa-book me-1"></i>Issue Book
                                    </button>
                                </form>
                            @elseif($request->status === 'issued')
                                <span class="text-muted">Book Issued</span>
                            @else
                                <span class="text-muted">Rejected</span>
                            @endif
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
