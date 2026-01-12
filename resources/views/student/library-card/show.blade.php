@extends('layouts.student')

@section('title', 'My Library Card')
@section('page-title', 'My Library Card')

@section('content')
@if($card)
<div class="card-modern">
    <div class="card-header-modern">
        <h5>
            <i class="fas fa-id-card text-primary"></i>
            My Library Card
        </h5>
        <div>
            <a href="{{ route('student.library-card.print') }}" class="btn btn-secondary" target="_blank">
                <i class="fas fa-print me-2"></i>Print Card
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
                            <p class="mb-0 fw-bold fs-5">{{ $card->card_number }}</p>
                        </div>
                        <div class="mb-3">
                            <label class="text-muted small">Status</label>
                            <p class="mb-0">
                                @if($card->status === 'active' && $card->validity_date >= now())
                                    <span class="badge bg-success">Active</span>
                                @elseif($card->status === 'active' && $card->validity_date < now())
                                    <span class="badge bg-warning">Expired</span>
                                @elseif($card->status === 'blocked')
                                    <span class="badge bg-danger">Blocked</span>
                                @elseif($card->status === 'lost')
                                    <span class="badge bg-dark">Lost</span>
                                @endif
                            </p>
                        </div>
                        <div class="mb-3">
                            <label class="text-muted small">Issue Date</label>
                            <p class="mb-0">{{ $card->issue_date->format('M d, Y') }}</p>
                        </div>
                        <div class="mb-3">
                            <label class="text-muted small">Validity Date</label>
                            <p class="mb-0 {{ $card->validity_date < now() ? 'text-danger fw-bold' : '' }}">
                                {{ $card->validity_date->format('M d, Y') }}
                                @if($card->validity_date < now())
                                    <span class="badge bg-danger ms-2">Expired</span>
                                @elseif($card->validity_date->diffInDays(now()) <= 30)
                                    <span class="badge bg-warning ms-2">Expiring Soon</span>
                                @endif
                            </p>
                        </div>
                        @if($card->notes)
                        <div class="mb-3">
                            <label class="text-muted small">Notes</label>
                            <p class="mb-0">{{ $card->notes }}</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- QR Code -->
            <div class="col-12 col-md-6">
                <div class="card border">
                    <div class="card-header bg-info text-white">
                        <h6 class="mb-0"><i class="fas fa-qrcode me-2"></i>QR Code</h6>
                    </div>
                    <div class="card-body text-center">
                        @if($card->qr_code)
                            <div style="max-width: 250px; margin: 0 auto;">
                                {!! $card->qr_code !!}
                            </div>
                            <p class="text-muted mt-3 small">Scan this QR code at the library</p>
                        @else
                            <p class="text-muted">QR Code not available</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Actions -->
        @if($card->status !== 'lost')
        <div class="row mt-4">
            <div class="col-12">
                <div class="card border">
                    <div class="card-header bg-light">
                        <h6 class="mb-0"><i class="fas fa-cog me-2"></i>Actions</h6>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('student.library-card.report-lost') }}" method="POST" onsubmit="return confirm('Are you sure you want to report this card as lost? You will need to request a new card.')">
                            @csrf
                            <button type="submit" class="btn btn-danger">
                                <i class="fas fa-exclamation-triangle me-2"></i>Report Card as Lost
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        @endif

        @if($card->validity_date < now())
        <div class="alert alert-warning mt-3">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <strong>Card Expired!</strong> Your library card has expired. Please contact library staff to renew your card.
        </div>
        @elseif($card->validity_date->diffInDays(now()) <= 30)
        <div class="alert alert-info mt-3">
            <i class="fas fa-info-circle me-2"></i>
            <strong>Card Expiring Soon!</strong> Your library card will expire on {{ $card->validity_date->format('M d, Y') }}. Please renew it before expiration.
        </div>
        @endif
    </div>
</div>
@else
<div class="card-modern">
    <div class="card-header-modern">
        <h5>
            <i class="fas fa-id-card text-primary"></i>
            My Library Card
        </h5>
    </div>
    <div class="card-body p-4 text-center">
        <div class="py-5">
            <i class="fas fa-id-card" style="font-size: 64px; color: #ccc; margin-bottom: 20px;"></i>
            <h4 class="mb-3">No Library Card</h4>
            <p class="text-muted mb-4">You don't have a library card yet. Request one to start borrowing books.</p>
            <form action="{{ route('student.library-card.request') }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-primary btn-lg">
                    <i class="fas fa-plus me-2"></i>Request Library Card
                </button>
            </form>
        </div>
    </div>
</div>
@endif
@endsection

