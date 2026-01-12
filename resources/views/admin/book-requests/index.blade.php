@extends('layouts.admin')

@section('title', 'Book Requests')
@section('page-title', 'Book Requests')

@section('content')
<div class="card-modern">
    <div class="card-header-modern">
        <h5>
            <i class="fas fa-hand-paper text-primary"></i>
            Book Requests
        </h5>
        <a href="{{ route('admin.book-requests.scan') }}" class="btn btn-primary">
            <i class="fas fa-qrcode me-2"></i>Scan QR Code
        </a>
    </div>
    <div class="card-body p-4">
        <!-- Statistics -->
        <div class="row g-3 mb-4">
            <div class="col-12 col-sm-6 col-md-3">
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
            <div class="col-12 col-sm-6 col-md-3">
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
            <div class="col-12 col-sm-6 col-md-3">
                <div class="stat-card border-start border-info border-4">
                    <div class="stat-content">
                        <div class="stat-label">Total Requests</div>
                        <div class="stat-value text-info">{{ $totalRequests }}</div>
                    </div>
                    <div class="stat-icon info">
                        <i class="fas fa-list"></i>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-md-3">
                <div class="stat-card border-start border-primary border-4">
                    <div class="stat-content">
                        <div class="stat-label">On Hold</div>
                        <div class="stat-value text-primary">{{ \App\Models\BookRequest::where('status', 'hold')->count() }}</div>
                    </div>
                    <div class="stat-icon primary">
                        <i class="fas fa-hand-paper"></i>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-md-3">
                <div class="stat-card border-start border-info border-4">
                    <div class="stat-content">
                        <div class="stat-label">Issued</div>
                        <div class="stat-value text-info">{{ \App\Models\BookRequest::where('status', 'issued')->count() }}</div>
                    </div>
                    <div class="stat-icon info">
                        <i class="fas fa-book"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Requests Table -->
        <div class="row">
            <div class="col-12">
                <div class="table-responsive" style="max-height: 600px; overflow-y: auto; overflow-x: auto;">
                    <table id="bookRequestsTable" class="table table-modern data-table mb-0" style="min-width: 1200px;">
                <thead>
                    <tr>
                        <th style="width: 5%;">#</th>
                        <th style="width: 15%;">Student</th>
                        <th style="width: 20%;">Book</th>
                        <th style="width: 10%;">Request Date</th>
                        <th style="width: 10%;">Status</th>
                        <th style="width: 12%;">Pickup Deadline</th>
                        <th style="width: 13%;">Approved By</th>
                        <th style="width: 15%;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($requests as $index => $request)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>
                            <div class="fw-semibold">{{ $request->user->name }}</div>
                            <small class="text-muted">{{ $request->user->email }}</small>
                        </td>
                        <td>
                            <div class="fw-semibold">{{ $request->book->title }}</div>
                            <small class="text-muted">ISBN: {{ $request->book->isbn }}</small>
                            @if($request->status === 'approved' && ($request->book->almirah || $request->book->row))
                            <div class="mt-1">
                                @if($request->book->almirah)
                                    <span class="badge bg-primary">Almirah: {{ $request->book->almirah }}</span>
                                @endif
                                @if($request->book->row)
                                    <span class="badge bg-success">Row: {{ $request->book->row }}</span>
                                @endif
                            </div>
                            @endif
                        </td>
                        <td>{{ $request->created_at->format('M d, Y') }}</td>
                        <td>
                            @if($request->status === 'pending')
                                <span class="badge bg-warning">Pending</span>
                            @elseif($request->status === 'hold')
                                <span class="badge bg-primary">HOLD</span>
                            @elseif($request->status === 'approved')
                                <span class="badge bg-info">Approved</span>
                            @elseif($request->status === 'rejected')
                                <span class="badge bg-danger">Rejected</span>
                            @elseif($request->status === 'cancelled')
                                <span class="badge bg-secondary">Cancelled</span>
                            @else
                                <span class="badge bg-success">Issued</span>
                            @endif
                        </td>
                        <td>
                            @if($request->hold_expires_at)
                                <div class="small">
                                    <div>{{ $request->hold_expires_at->format('M d, Y') }}</div>
                                    @if($request->hold_expires_at->isPast())
                                        <span class="badge bg-danger">Expired</span>
                                    @else
                                        <span class="badge bg-warning">{{ $request->hold_expires_at->diffForHumans() }}</span>
                                    @endif
                                </div>
                            @elseif($request->collection_deadline)
                                <div class="small">
                                    <div>{{ $request->collection_deadline->format('M d, Y') }}</div>
                                    @if($request->collection_deadline->isPast())
                                        <span class="badge bg-danger">Expired</span>
                                    @else
                                        <span class="badge bg-warning">{{ $request->collection_deadline->diffForHumans() }}</span>
                                    @endif
                                </div>
                            @else
                                <span class="text-muted">N/A</span>
                            @endif
                        </td>
                        <td>
                            {{ $request->approvedBy ? $request->approvedBy->name : 'N/A' }}
                        </td>
                        <td>
                            @if($request->status === 'pending')
                                <div class="btn-group" role="group">
                                    <form action="{{ route('admin.book-requests.approve', $request) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-success" title="Approve">
                                            <i class="fas fa-check"></i> Approve
                                        </button>
                                    </form>
                                    <form action="{{ route('admin.book-requests.reject', $request) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to reject this request?')">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-danger" title="Reject">
                                            <i class="fas fa-times"></i> Reject
                                        </button>
                                    </form>
                                </div>
                            @elseif(in_array($request->status, ['hold', 'approved']))
                                <div class="btn-group" role="group">
                                    <a href="{{ route('admin.book-requests.scan-result', $request) }}" class="btn btn-sm btn-info text-white" title="Scan QR & View Details">
                                        <i class="fas fa-qrcode me-1"></i>Scan
                                    </a>
                                    <form action="{{ route('admin.book-requests.issue', $request) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-primary" title="Issue Book">
                                            <i class="fas fa-book me-1"></i>Issue
                                        </button>
                                    </form>
                                    <form action="{{ route('admin.book-requests.reject', $request) }}" method="POST" class="d-inline" onsubmit="return confirm('Cancel this request? Stock will be returned.')">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-danger" title="Cancel">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </form>
                                </div>
                            @elseif($request->status === 'issued')
                                <span class="badge bg-success">
                                    <i class="fas fa-check-circle me-1"></i>Issued
                                </span>
                            @else
                                <span class="badge bg-danger">
                                    <i class="fas fa-times-circle me-1"></i>Rejected
                                </span>
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
