@extends('layouts.admin')

@section('title', 'Fine Details')
@section('page-title', 'Fine Details')

@section('content')
<div class="row">
    <div class="col-lg-8">
        <!-- Fine Information -->
        <div class="card-modern mb-4">
            <div class="card-header-modern">
                <h5>
                    <i class="fas fa-info-circle text-primary"></i>
                    Fine Information
                </h5>
            </div>
            <div class="card-body p-4">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="text-muted small">Student</label>
                        <div class="fw-semibold">{{ $fine->user->name }}</div>
                        <small class="text-muted">{{ $fine->user->email }}</small>
                    </div>
                    <div class="col-md-6">
                        <label class="text-muted small">Book</label>
                        <div class="fw-semibold">{{ $fine->borrow->book->title }}</div>
                        <small class="text-muted">ISBN: {{ $fine->borrow->book->isbn }}</small>
                    </div>
                    <div class="col-md-4">
                        <label class="text-muted small">Fine Amount</label>
                        <div class="fs-4 fw-bold text-danger">₹{{ number_format($fine->amount, 2) }}</div>
                    </div>
                    <div class="col-md-4">
                        <label class="text-muted small">Paid Amount</label>
                        <div class="fs-5 fw-bold text-success">₹{{ number_format($fine->paid_amount ?? 0, 2) }}</div>
                    </div>
                    <div class="col-md-4">
                        <label class="text-muted small">Remaining</label>
                        <div class="fs-5 fw-bold text-warning">₹{{ number_format($fine->remaining_amount ?? $fine->amount, 2) }}</div>
                    </div>
                    <div class="col-md-6">
                        <label class="text-muted small">Payment Status</label>
                        <div>
                            @if($fine->status === 'paid')
                                <span class="badge bg-success fs-6">✅ Paid</span>
                            @elseif($fine->status === 'pending')
                                @if(($fine->paid_amount ?? 0) > 0)
                                    <span class="badge bg-warning text-dark fs-6">⏳ Partial Payment</span>
                                @else
                                    <span class="badge bg-warning text-dark fs-6">⏳ Pending</span>
                                @endif
                            @elseif($fine->status === 'waived')
                                <span class="badge bg-info fs-6">🙏 Waived</span>
                            @elseif($fine->status === 'adjusted')
                                <span class="badge bg-secondary fs-6">✏️ Adjusted</span>
                            @endif
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="text-muted small">Paid Date</label>
                        <div class="fw-semibold">{{ $fine->paid_date ? $fine->paid_date->format('d M Y, h:i A') : 'N/A' }}</div>
                    </div>
                    <div class="col-12">
                        <label class="text-muted small">Reason</label>
                        <div class="fw-semibold">{{ $fine->reason }}</div>
                    </div>
                    @if($fine->payment_notes)
                    <div class="col-12">
                        <label class="text-muted small">Payment Notes</label>
                        <div class="bg-light p-3 rounded">
                            <pre class="mb-0 small">{{ $fine->payment_notes }}</pre>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Payment History -->
        @if($payments->isNotEmpty())
        <div class="card-modern mb-4">
            <div class="card-header-modern">
                <h5>
                    <i class="fas fa-history text-primary"></i>
                    Payment History
                </h5>
            </div>
            <div class="card-body p-4">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Amount</th>
                                <th>Method</th>
                                <th>Transaction ID</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($payments as $payment)
                            <tr>
                                <td>{{ $payment->paid_at ? $payment->paid_at->format('d M Y, h:i A') : $payment->created_at->format('d M Y, h:i A') }}</td>
                                <td class="fw-bold">₹{{ number_format($payment->amount, 2) }}</td>
                                <td><span class="badge bg-info">{{ ucfirst($payment->payment_method) }}</span></td>
                                <td><small>{{ $payment->payment_id ?? 'N/A' }}</small></td>
                                <td>
                                    @if($payment->status === 'completed')
                                        <span class="badge bg-success">Completed</span>
                                    @elseif($payment->status === 'pending')
                                        <span class="badge bg-warning">Pending</span>
                                    @else
                                        <span class="badge bg-danger">Failed</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif
    </div>

    <div class="col-lg-4">
        <!-- Manual Override Actions -->
        <div class="card-modern mb-4">
            <div class="card-header-modern">
                <h5>
                    <i class="fas fa-tools text-warning"></i>
                    Manual Override
                </h5>
            </div>
            <div class="card-body p-4">
                <!-- Update Status -->
                <form method="POST" action="{{ route('admin.fines.update-payment', $fine) }}" class="mb-3">
                    @csrf
                    @method('PUT')
                    <div class="mb-3">
                        <label class="form-label small">Payment Status</label>
                        <select name="status" class="form-select" required>
                            <option value="pending" {{ $fine->status === 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="paid" {{ $fine->status === 'paid' ? 'selected' : '' }}>Paid</option>
                            <option value="waived" {{ $fine->status === 'waived' ? 'selected' : '' }}>Waived</option>
                            <option value="adjusted" {{ $fine->status === 'adjusted' ? 'selected' : '' }}>Adjusted</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small">Paid Date</label>
                        <input type="date" name="paid_date" class="form-control" value="{{ $fine->paid_date?->format('Y-m-d') }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small">Notes (Optional)</label>
                        <textarea name="notes" class="form-control" rows="2" placeholder="Add notes..."></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-save me-2"></i>Update Status
                    </button>
                </form>

                <hr>

                <!-- Waive Fine -->
                @if($fine->status !== 'waived')
                <button type="button" class="btn btn-info w-100 mb-3" data-bs-toggle="modal" data-bs-target="#waiveModal">
                    <i class="fas fa-hand-holding-heart me-2"></i>Waive Fine
                </button>
                @endif

                <!-- Adjust Fine -->
                @if($fine->status !== 'adjusted')
                <button type="button" class="btn btn-secondary w-100 mb-3" data-bs-toggle="modal" data-bs-target="#adjustModal">
                    <i class="fas fa-edit me-2"></i>Adjust Amount
                </button>
                @endif

                <!-- Record Partial Payment -->
                @if($fine->status === 'pending' && ($fine->remaining_amount ?? $fine->amount) > 0)
                <button type="button" class="btn btn-warning w-100" data-bs-toggle="modal" data-bs-target="#partialModal">
                    <i class="fas fa-money-bill me-2"></i>Record Payment
                </button>
                @endif
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="card-modern">
            <div class="card-body p-4">
                <a href="{{ route('admin.fines.index') }}" class="btn btn-outline-secondary w-100 mb-2">
                    <i class="fas fa-arrow-left me-2"></i>Back to Fines
                </a>
                <a href="{{ route('admin.fines.payment-logs') }}" class="btn btn-outline-info w-100">
                    <i class="fas fa-list me-2"></i>View Payment Logs
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Waive Fine Modal -->
<div class="modal fade" id="waiveModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.fines.waive', $fine) }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Waive Fine</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-3">Are you sure you want to waive this fine of <strong>₹{{ number_format($fine->amount, 2) }}</strong>?</p>
                    <div class="mb-3">
                        <label class="form-label">Reason for Waiving *</label>
                        <textarea name="reason" class="form-control" rows="3" required placeholder="Enter reason for waiving this fine..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-info">Waive Fine</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Adjust Fine Modal -->
<div class="modal fade" id="adjustModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.fines.adjust', $fine) }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Adjust Fine Amount</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Current Amount</label>
                        <input type="text" class="form-control" value="₹{{ number_format($fine->amount, 2) }}" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">New Amount *</label>
                        <input type="number" name="new_amount" class="form-control" step="0.01" min="0" value="{{ $fine->amount }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Reason for Adjustment *</label>
                        <textarea name="reason" class="form-control" rows="3" required placeholder="Enter reason for adjusting this fine..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-secondary">Adjust Amount</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Partial Payment Modal -->
<div class="modal fade" id="partialModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.fines.partial-payment', $fine) }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Record Partial Payment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Remaining Amount</label>
                        <input type="text" class="form-control" value="₹{{ number_format($fine->remaining_amount ?? $fine->amount, 2) }}" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Payment Amount *</label>
                        <input type="number" name="payment_amount" class="form-control" step="0.01" min="0.01" max="{{ $fine->remaining_amount ?? $fine->amount }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Notes (Optional)</label>
                        <textarea name="payment_notes" class="form-control" rows="2" placeholder="Payment notes..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">Record Payment</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
