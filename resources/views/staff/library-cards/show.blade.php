@extends('layouts.staff')

@section('title', 'Library Card Details')
@section('page-title', 'Library Card Details')

@section('content')
<div class="card-modern">
    <div class="card-header-modern">
        <h5>
            <i class="fas fa-id-card text-primary"></i>
            Library Card Details
        </h5>
        <div>
            <a href="{{ route('staff.library-cards.print', $libraryCard) }}" class="btn btn-secondary" target="_blank">
                <i class="fas fa-print me-2"></i>Print Card
            </a>
            <a href="{{ route('staff.library-cards.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i>Back
            </a>
        </div>
    </div>
    <div class="card-body p-4">
        <div class="row g-4">
            <!-- Card Information -->
            <div class="col-12 col-md-6">
                <div class="card border">
                    <div class="card-header bg-primary text-white">
                        <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>Card Information</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="text-muted small">Card Number</label>
                            <p class="mb-0 fw-bold fs-5">{{ $libraryCard->card_number }}</p>
                        </div>
                        <div class="mb-3">
                            <label class="text-muted small">Status</label>
                            <p class="mb-0">
                                @if($libraryCard->status === 'active' && $libraryCard->validity_date >= now())
                                    <span class="badge bg-success">Active</span>
                                @elseif($libraryCard->status === 'active' && $libraryCard->validity_date < now())
                                    <span class="badge bg-warning">Expired</span>
                                @elseif($libraryCard->status === 'blocked')
                                    <span class="badge bg-danger">Blocked</span>
                                @elseif($libraryCard->status === 'lost')
                                    <span class="badge bg-dark">Lost</span>
                                @endif
                            </p>
                        </div>
                        <div class="mb-3">
                            <label class="text-muted small">Issue Date</label>
                            <p class="mb-0">{{ $libraryCard->issue_date->format('M d, Y') }}</p>
                        </div>
                        <div class="mb-3">
                            <label class="text-muted small">Validity Date</label>
                            <p class="mb-0 {{ $libraryCard->validity_date < now() ? 'text-danger fw-bold' : '' }}">
                                {{ $libraryCard->validity_date->format('M d, Y') }}
                                @if($libraryCard->validity_date < now())
                                    <span class="badge bg-danger ms-2">Expired</span>
                                @endif
                            </p>
                        </div>
                        <div class="mb-3">
                            <label class="text-muted small">Issued By</label>
                            <p class="mb-0">{{ $libraryCard->issued_by ?? 'N/A' }}</p>
                        </div>
                        @if($libraryCard->notes)
                        <div class="mb-3">
                            <label class="text-muted small">Notes</label>
                            <p class="mb-0">{{ $libraryCard->notes }}</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Student Information -->
            <div class="col-12 col-md-6">
                <div class="card border">
                    <div class="card-header bg-info text-white">
                        <h6 class="mb-0"><i class="fas fa-user me-2"></i>Student Information</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="text-muted small">Name</label>
                            <p class="mb-0 fw-bold">{{ $libraryCard->user->name }}</p>
                        </div>
                        <div class="mb-3">
                            <label class="text-muted small">Email</label>
                            <p class="mb-0">{{ $libraryCard->user->email }}</p>
                        </div>
                        <div class="mb-3">
                            <label class="text-muted small">Student ID</label>
                            <p class="mb-0">{{ $libraryCard->user->student_id ?? 'N/A' }}</p>
                        </div>
                        <div class="mb-3">
                            <label class="text-muted small">Course</label>
                            <p class="mb-0">{{ $libraryCard->user->course ?? 'N/A' }}</p>
                        </div>
                        <div class="mb-3">
                            <label class="text-muted small">Batch</label>
                            <p class="mb-0">{{ $libraryCard->user->batch ?? 'N/A' }}</p>
                        </div>
                        <div class="mb-3">
                            <label class="text-muted small">Phone</label>
                            <p class="mb-0">{{ $libraryCard->user->phone ?? 'N/A' }}</p>
                        </div>
                        <div class="mb-3">
                            <label class="text-muted small">Address</label>
                            <p class="mb-0">{{ $libraryCard->user->address ?? 'N/A' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- QR Code -->
            <div class="col-12 text-center">
                <div class="card border">
                    <div class="card-body">
                        <h6 class="mb-3">QR Code</h6>
                        @if($libraryCard->qr_code)
                            <div style="max-width: 200px; margin: 0 auto;">
                                {!! $libraryCard->qr_code !!}
                            </div>
                        @else
                            <p class="text-muted">QR Code not available</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card border">
                    <div class="card-header bg-light">
                        <h6 class="mb-0"><i class="fas fa-cog me-2"></i>Actions</h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            @if(!$libraryCard->isBlocked())
                                <div class="col-12 col-md-4">
                                    <form action="{{ route('staff.library-cards.block', $libraryCard) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn btn-danger w-100" onclick="return confirm('Are you sure you want to block this card?')">
                                            <i class="fas fa-ban me-2"></i>Block Card
                                        </button>
                                    </form>
                                </div>
                            @else
                                <div class="col-12 col-md-4">
                                    <form action="{{ route('staff.library-cards.unblock', $libraryCard) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn btn-success w-100" onclick="return confirm('Are you sure you want to unblock this card?')">
                                            <i class="fas fa-check me-2"></i>Unblock Card
                                        </button>
                                    </form>
                                </div>
                            @endif
                            <div class="col-12 col-md-4">
                                <button type="button" class="btn btn-warning w-100" data-bs-toggle="modal" data-bs-target="#renewModal">
                                    <i class="fas fa-sync me-2"></i>Renew Card
                                </button>
                            </div>
                            <div class="col-12 col-md-4">
                                <a href="{{ route('staff.library-cards.print', $libraryCard) }}" class="btn btn-primary w-100" target="_blank">
                                    <i class="fas fa-print me-2"></i>Print Card
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Renew Modal -->
<div class="modal fade" id="renewModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('staff.library-cards.renew', $libraryCard) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Renew Library Card</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="renew_validity_date" class="form-label">New Validity Date <span class="text-danger">*</span></label>
                        <input type="date" name="validity_date" id="renew_validity_date" 
                            class="form-control" 
                            value="{{ now()->addYear()->format('Y-m-d') }}" 
                            min="{{ now()->addDay()->format('Y-m-d') }}" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Renew Card</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

