@extends('layouts.student')

@section('title', 'Fine History')
@section('page-title', 'Fine History')

@push('styles')
<style>
    .qr-code-container {
        display: none;
        text-align: center;
        padding: 20px;
        background: #f8f9fa;
        border-radius: 8px;
        margin-top: 10px;
    }
    .qr-code-container.active {
        display: block;
    }
    .qr-code-wrapper {
        display: inline-block;
        padding: 15px;
        background: white;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    .payment-status-badge {
        display: inline-block;
        padding: 4px 12px;
        border-radius: 12px;
        font-size: 12px;
        font-weight: 600;
    }
    .status-pending {
        background: #fff3cd;
        color: #856404;
    }
    .status-processing {
        background: #cfe2ff;
        color: #084298;
        animation: pulse 2s infinite;
    }
    .status-paid {
        background: #d1e7dd;
        color: #0f5132;
    }
    @keyframes pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.7; }
    }
    .fine-row {
        transition: background-color 0.3s;
    }
    .fine-row.paid {
        background-color: #f0f9ff;
    }
</style>
@endpush

@section('content')
<!-- Statistics -->
<div class="row g-3 mb-4">
    <div class="col-12 col-sm-6 col-md-6">
        <div class="stat-card border-start border-warning border-4">
            <div class="stat-content">
                <div class="stat-label">Pending Fines</div>
                <div class="stat-value text-warning">₹{{ number_format($totalPending, 2) }}</div>
                <div class="stat-desc">Amount to be paid</div>
            </div>
            <div class="stat-icon warning">
                <i class="fas fa-clock"></i>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-md-6">
        <div class="stat-card border-start border-success border-4">
            <div class="stat-content">
                <div class="stat-label">Paid Fines</div>
                <div class="stat-value text-success">₹{{ number_format($totalPaid, 2) }}</div>
                <div class="stat-desc">Total paid amount</div>
            </div>
            <div class="stat-icon success">
                <i class="fas fa-check-circle"></i>
            </div>
        </div>
    </div>
</div>

<div class="card-modern">
    <div class="card-header-modern">
        <h5>
            <i class="fas fa-dollar-sign text-primary"></i>
            All Fines
        </h5>
    </div>
    <div class="card-body p-4">
        <div class="row">
            <div class="col-12">
                <div class="table-responsive">
                    <table id="finesTable" class="table table-modern data-table mb-0 w-100">
                        <thead>
                            <tr>
                                <th style="width: 5%;">#</th>
                                <th style="width: 18%;">Book</th>
                                <th style="width: 10%;">Amount</th>
                                <th style="width: 20%;">Reason</th>
                                <th style="width: 12%;">Payment Status</th>
                                <th style="width: 12%;">Paid Date</th>
                                <th style="width: 10%;">Date</th>
                                <th style="width: 13%;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($fines as $index => $fine)
                            <tr class="fine-row" data-fine-id="{{ $fine->id }}" id="fine-row-{{ $fine->id }}">
                                <td>{{ $index + 1 }}</td>
                                <td>
                                    <div class="fw-semibold">{{ $fine->borrow->book->title }}</div>
                                    <small class="text-muted">ISBN: {{ $fine->borrow->book->isbn }}</small>
                                </td>
                                <td class="fw-bold">
                                    @php
                                        $borrow = $fine->borrow;
                                        $remainingAmount = $fine->remaining_amount ?? $fine->amount;
                                        $pendingAmount = $borrow ? $borrow->current_fine_amount : $remainingAmount;
                                        $totalPaid = $borrow ? ($borrow->total_fine_paid ?? 0) : ($fine->paid_amount ?? 0);
                                        $finePerDay = $borrow ? ($borrow->fine_per_day ?? 10) : 10;
                                        $pendingDays = $borrow ? $borrow->pending_fine_days : 0;
                                        $lastPaidDate = $borrow ? $borrow->last_fine_paid_date : null;
                                    @endphp
                                    
                                    @if($fine->status !== 'paid' && $borrow && $borrow->isOverdue())
                                        <div class="mb-2">
                                            <div class="text-success small">
                                                <i class="fas fa-check-circle me-1"></i>
                                                <strong>Paid: ₹{{ number_format($totalPaid, 2) }}</strong>
                                                @if($lastPaidDate)
                                                    <br><span class="text-muted">(Paid till: {{ $lastPaidDate->format('M d, Y') }})</span>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="mb-2">
                                            <div class="text-danger">
                                                <i class="fas fa-exclamation-triangle me-1"></i>
                                                <strong>Pending: ₹<span class="fine-amount-{{ $fine->id }}">{{ number_format($pendingAmount, 2) }}</span></strong>
                                                @if($pendingDays > 0)
                                                    <br><span class="text-muted small">({{ $pendingDays }} day(s) from {{ $lastPaidDate ? \Carbon\Carbon::parse($lastPaidDate)->addDay()->format('M d, Y') : \Carbon\Carbon::parse($borrow->due_date)->addDay()->format('M d, Y') }})</span>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="text-info small">
                                            <i class="fas fa-info-circle me-1"></i>
                                            Rate: ₹{{ number_format($finePerDay, 2) }}/day
                                        </div>
                                    @else
                                        ₹<span class="fine-amount-{{ $fine->id }}">{{ number_format($fine->amount, 2) }}</span>
                                    @endif
                                </td>
                                <td>{{ $fine->reason }}</td>
                                <td>
                                    <span class="payment-status-badge status-{{ $fine->status }}" id="status-badge-{{ $fine->id }}">
                                        @if($fine->status === 'paid')
                                            ✅ Paid
                                        @else
                                            ⏳ Pending
                                        @endif
                                    </span>
                                </td>
                                <td>
                                    @if($fine->paid_date)
                                        {{ $fine->paid_date->format('M d, Y') }}
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </td>
                                <td>{{ $fine->created_at->format('M d, Y') }}</td>
                                <td>
                                    @if($fine->status !== 'paid')
                                        @php
                                            $borrow = $fine->borrow;
                                            $remainingAmount = $fine->remaining_amount ?? $fine->amount;
                                            // Recalculate if book is still overdue
                                            if ($borrow && $borrow->isOverdue()) {
                                                $remainingAmount = $borrow->current_fine_amount;
                                            }
                                        @endphp
                                        @if($remainingAmount > 0)
                                            <div class="btn-group" role="group">
                                                <button type="button" class="btn btn-sm btn-primary" onclick="showQRCode({{ $fine->id }})">
                                                    <i class="fas fa-qrcode me-1"></i>Pay ₹{{ number_format($remainingAmount, 2) }}
                                                </button>
                                                <button type="button" class="btn btn-sm btn-success" onclick="simulatePayment({{ $fine->id }})" title="Test Payment (Pay 1 day)">
                                                    <i class="fas fa-check me-1"></i>Test Pay
                                                </button>
                                            </div>
                                            @if($borrow && $borrow->isOverdue())
                                                <div class="mt-2">
                                                    <div class="alert alert-warning small mb-0 p-2">
                                                        <i class="fas fa-info-circle me-1"></i>
                                                        <strong>Note:</strong> Only unpaid days from {{ $borrow->last_fine_paid_date ? \Carbon\Carbon::parse($borrow->last_fine_paid_date)->addDay()->format('M d, Y') : \Carbon\Carbon::parse($borrow->due_date)->addDay()->format('M d, Y') }} will be charged.
                                                    </div>
                                                </div>
                                            @endif
                                        @endif
                                    @else
                                        <a href="{{ route('student.fines.receipt', $fine) }}" class="btn btn-sm btn-success" target="_blank">
                                            <i class="fas fa-download me-1"></i>Receipt
                                        </a>
                                    @endif
                                </td>
                            </tr>
                            @if($fine->status !== 'paid')
                            <tr id="qr-row-{{ $fine->id }}" style="display: none;">
                                <td colspan="8">
                                    <div class="qr-code-container" id="qr-container-{{ $fine->id }}">
                                        <div class="qr-code-wrapper">
                                            <div id="qr-code-{{ $fine->id }}"></div>
                                        </div>
                                        <p class="mt-3 mb-2">
                                            <strong>Amount: ₹<span id="qr-amount-{{ $fine->id }}">{{ number_format($remainingAmount ?? $fine->amount, 2) }}</span></strong>
                                        </p>
                                        <p class="text-muted small mb-2">Scan this QR code with UPI app to pay</p>
                                        <p class="text-muted small mb-2">
                                            <i class="fas fa-info-circle me-1"></i>
                                            Payment status will update automatically
                                        </p>
                                        <div class="mt-3">
                                            <button type="button" class="btn btn-sm btn-secondary me-2" onclick="hideQRCode({{ $fine->id }})">
                                                <i class="fas fa-times me-1"></i>Close
                                            </button>
                                            <button type="button" class="btn btn-sm btn-success" onclick="simulatePayment({{ $fine->id }})">
                                                <i class="fas fa-check me-1"></i>Simulate Payment (Test)
                                            </button>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    let paymentPollingIntervals = {};

    function showQRCode(fineId) {
        const qrRow = document.getElementById('qr-row-' + fineId);
        const qrContainer = document.getElementById('qr-container-' + fineId);
        const qrCodeDiv = document.getElementById('qr-code-' + fineId);
        
        if (qrRow.style.display === 'none' || !qrCodeDiv.innerHTML) {
            // Fetch QR code
            fetch(`/student/fines/${fineId}/qr`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        qrCodeDiv.innerHTML = data.qr_code;
                        document.getElementById('qr-amount-' + fineId).textContent = parseFloat(data.amount).toFixed(2);
                        qrRow.style.display = 'table-row';
                        qrContainer.classList.add('active');
                        
                        // Start polling for payment status
                        startPaymentPolling(fineId);
                    } else {
                        alert('Error generating QR code');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error loading QR code');
                });
        } else {
            qrRow.style.display = 'table-row';
            qrContainer.classList.add('active');
            startPaymentPolling(fineId);
        }
    }

    function hideQRCode(fineId) {
        const qrRow = document.getElementById('qr-row-' + fineId);
        const qrContainer = document.getElementById('qr-container-' + fineId);
        qrRow.style.display = 'none';
        qrContainer.classList.remove('active');
        stopPaymentPolling(fineId);
    }

    function startPaymentPolling(fineId) {
        // Clear existing interval if any
        if (paymentPollingIntervals[fineId]) {
            clearInterval(paymentPollingIntervals[fineId]);
        }

        // Poll every 3 seconds
        paymentPollingIntervals[fineId] = setInterval(() => {
            checkPaymentStatus(fineId);
        }, 3000);

        // Check immediately
        checkPaymentStatus(fineId);
    }

    function stopPaymentPolling(fineId) {
        if (paymentPollingIntervals[fineId]) {
            clearInterval(paymentPollingIntervals[fineId]);
            delete paymentPollingIntervals[fineId];
        }
    }

    function checkPaymentStatus(fineId) {
        fetch(`/student/fines/${fineId}/check-payment`)
            .then(response => response.json())
            .then(data => {
                if (data.is_paid) {
                    // Payment completed
                    updatePaymentStatus(fineId, data);
                    stopPaymentPolling(fineId);
                } else if (data.status === 'processing') {
                    // Payment processing
                    updatePaymentStatus(fineId, data);
                }
            })
            .catch(error => {
                console.error('Error checking payment status:', error);
            });
    }

    function updatePaymentStatus(fineId, data) {
        const statusBadge = document.getElementById('status-badge-' + fineId);
        const fineRow = document.getElementById('fine-row-' + fineId);
        const qrRow = document.getElementById('qr-row-' + fineId);
        const fineAmountSpan = document.querySelector('.fine-amount-' + fineId);
        
        // Update remaining amount if provided
        if (data.remaining_amount !== undefined && fineAmountSpan) {
            fineAmountSpan.textContent = parseFloat(data.remaining_amount).toFixed(2);
            // Update QR amount if visible
            const qrAmount = document.getElementById('qr-amount-' + fineId);
            if (qrAmount) {
                qrAmount.textContent = parseFloat(data.remaining_amount).toFixed(2);
            }
        }
        
        if (data.is_paid) {
            // Update status badge
            statusBadge.className = 'payment-status-badge status-paid';
            statusBadge.innerHTML = '✅ Paid';
            
            // Update row styling
            fineRow.classList.add('paid');
            
            // Hide QR code
            if (qrRow) {
                qrRow.style.display = 'none';
            }
            
            // Update action button
            const actionCell = fineRow.querySelector('td:last-child');
            if (actionCell) {
                actionCell.innerHTML = `
                    <a href="/student/fines/${fineId}/receipt" class="btn btn-sm btn-success" target="_blank">
                        <i class="fas fa-download me-1"></i>Receipt
                    </a>
                `;
            }
            
            // Show success message
            showSuccessMessage('Fine paid successfully! ✅');
            
            // Reload page after 2 seconds to show updated data
            setTimeout(() => {
                window.location.reload();
            }, 2000);
        } else if (data.status === 'processing') {
            statusBadge.className = 'payment-status-badge status-processing';
            statusBadge.innerHTML = '⏳ Processing...';
        } else if (data.remaining_amount !== undefined && data.remaining_amount > 0) {
            // Partial payment - update remaining amount
            const payButton = fineRow.querySelector('button[onclick*="showQRCode"]');
            if (payButton) {
                payButton.innerHTML = `<i class="fas fa-qrcode me-1"></i>Pay ₹${parseFloat(data.remaining_amount).toFixed(2)}`;
            }
        }
    }

    function showSuccessMessage(message) {
        // Create toast notification
        const toast = document.createElement('div');
        toast.className = 'alert alert-success alert-dismissible fade show position-fixed';
        toast.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
        toast.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        document.body.appendChild(toast);
        
        // Auto remove after 5 seconds
        setTimeout(() => {
            toast.remove();
        }, 5000);
    }

    function simulatePayment(fineId) {
        if (!confirm('Simulate payment for this fine? (This is for testing only)')) {
            return;
        }

        fetch(`/student/fines/${fineId}/simulate-payment`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                if (data.fine_status === 'paid') {
                    showSuccessMessage('✅ Payment simulated successfully! Fine fully paid.');
                    updatePaymentStatus(fineId, { is_paid: true, status: 'paid', remaining_amount: 0 });
                } else {
                    showSuccessMessage('✅ Payment simulated! ' + (data.message || 'Remaining fine: ₹' + parseFloat(data.remaining_amount || 0).toFixed(2)));
                    updatePaymentStatus(fineId, { 
                        is_paid: false, 
                        status: 'pending', 
                        remaining_amount: data.remaining_amount || 0 
                    });
                }
                stopPaymentPolling(fineId);
                // Reload after 2 seconds to show updated data
                setTimeout(() => {
                    window.location.reload();
                }, 2000);
            } else {
                alert(data.error || 'Error simulating payment');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error simulating payment');
        });
    }

    // Auto-show QR code for first pending fine on page load
    document.addEventListener('DOMContentLoaded', function() {
        const pendingFines = document.querySelectorAll('.fine-row[data-fine-id]');
        let firstPendingFound = false;
        
        pendingFines.forEach(row => {
            const fineId = row.getAttribute('data-fine-id');
            const statusBadge = document.getElementById('status-badge-' + fineId);
            
            if (statusBadge) {
                const statusText = statusBadge.textContent || statusBadge.innerText;
                
                if (statusText.includes('Pending') && !firstPendingFound) {
                    // Auto-show QR for first pending fine
                    setTimeout(() => {
                        showQRCode(parseInt(fineId));
                    }, 1000);
                    firstPendingFound = true;
                }
                
                // Check status every 10 seconds for all pending fines
                if (statusText.includes('Pending')) {
                    setInterval(() => {
                        checkPaymentStatus(fineId);
                    }, 10000);
                }
            }
        });
        
        // If no pending fines found, check again after a short delay (in case fines were just created)
        if (!firstPendingFound) {
            setTimeout(() => {
                const pendingFinesRetry = document.querySelectorAll('.fine-row[data-fine-id]');
                pendingFinesRetry.forEach(row => {
                    const fineId = row.getAttribute('data-fine-id');
                    const statusBadge = document.getElementById('status-badge-' + fineId);
                    if (statusBadge) {
                        const statusText = statusBadge.textContent || statusBadge.innerText;
                        if (statusText.includes('Pending')) {
                            showQRCode(parseInt(fineId));
                            return false; // Break loop
                        }
                    }
                });
            }, 2000);
        }
    });
</script>
@endpush
