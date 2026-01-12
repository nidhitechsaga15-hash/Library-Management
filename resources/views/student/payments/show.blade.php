@extends('layouts.student')

@section('title', 'Payment Details')
@section('page-title', 'Payment Details')

@section('content')
<div class="card-modern">
    <div class="card-header-modern">
        <h5>
            <i class="fas fa-receipt text-primary"></i>
            Payment Details
        </h5>
    </div>
    <div class="card-body p-4">
        <div class="row">
            <div class="col-md-8 mx-auto">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <h6 class="mb-4">Payment Information</h6>
                        <table class="table table-borderless">
                            <tr>
                                <td width="40%"><strong>Payment ID:</strong></td>
                                <td>#{{ $payment->order_id ?? $payment->id }}</td>
                            </tr>
                            <tr>
                                <td><strong>Payment Type:</strong></td>
                                <td>
                                    <span class="badge bg-info">
                                        {{ ucfirst(str_replace('_', ' ', $payment->payment_type)) }}
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Amount:</strong></td>
                                <td><span class="fs-5">₹{{ number_format($payment->amount, 2) }}</span></td>
                            </tr>
                            <tr>
                                <td><strong>Status:</strong></td>
                                <td>
                                    @if($payment->status === 'completed')
                                        <span class="badge bg-success">Completed</span>
                                    @elseif($payment->status === 'pending')
                                        <span class="badge bg-warning">Pending</span>
                                    @elseif($payment->status === 'failed')
                                        <span class="badge bg-danger">Failed</span>
                                    @else
                                        <span class="badge bg-secondary">{{ ucfirst($payment->status) }}</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Payment Method:</strong></td>
                                <td>{{ ucfirst($payment->payment_method) }}</td>
                            </tr>
                            @if($payment->payment_id)
                            <tr>
                                <td><strong>Gateway Payment ID:</strong></td>
                                <td>{{ $payment->payment_id }}</td>
                            </tr>
                            @endif
                            <tr>
                                <td><strong>Date:</strong></td>
                                <td>{{ $payment->created_at->format('d M Y, h:i A') }}</td>
                            </tr>
                            @if($payment->paid_at)
                            <tr>
                                <td><strong>Paid At:</strong></td>
                                <td>{{ $payment->paid_at->format('d M Y, h:i A') }}</td>
                            </tr>
                            @endif
                            @if($payment->failure_reason)
                            <tr>
                                <td><strong>Failure Reason:</strong></td>
                                <td class="text-danger">{{ $payment->failure_reason }}</td>
                            </tr>
                            @endif
                        </table>
                    </div>
                </div>

                <div class="mt-3 text-center">
                    <a href="{{ route('student.payments.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Back to Payments
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

