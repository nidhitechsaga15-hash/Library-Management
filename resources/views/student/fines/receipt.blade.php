<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Receipt - Fine #{{ $fine->id }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        @media print {
            .no-print {
                display: none !important;
            }
            body {
                margin: 0;
                padding: 20px;
            }
        }
        .receipt-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 40px;
            background: white;
        }
        .receipt-header {
            text-align: center;
            border-bottom: 3px solid #667eea;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .receipt-title {
            font-size: 28px;
            font-weight: 700;
            color: #667eea;
            margin-bottom: 10px;
        }
        .receipt-subtitle {
            color: #6c757d;
            font-size: 14px;
        }
        .receipt-section {
            margin-bottom: 25px;
        }
        .receipt-section-title {
            font-size: 16px;
            font-weight: 600;
            color: #333;
            margin-bottom: 15px;
            padding-bottom: 8px;
            border-bottom: 2px solid #e9ecef;
        }
        .receipt-info {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #f0f0f0;
        }
        .receipt-info:last-child {
            border-bottom: none;
        }
        .receipt-info-label {
            font-weight: 600;
            color: #6c757d;
        }
        .receipt-info-value {
            color: #333;
            text-align: right;
        }
        .receipt-amount {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            margin: 30px 0;
        }
        .receipt-amount-label {
            font-size: 14px;
            opacity: 0.9;
            margin-bottom: 5px;
        }
        .receipt-amount-value {
            font-size: 36px;
            font-weight: 700;
        }
        .receipt-footer {
            text-align: center;
            margin-top: 40px;
            padding-top: 20px;
            border-top: 2px solid #e9ecef;
            color: #6c757d;
            font-size: 12px;
        }
        .status-badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            background: #d1e7dd;
            color: #0f5132;
        }
    </style>
</head>
<body>
    <div class="receipt-container">
        <div class="receipt-header">
            <div class="receipt-title">
                <i class="fas fa-receipt me-2"></i>Payment Receipt
            </div>
            <div class="receipt-subtitle">Library Management System</div>
        </div>

        <div class="receipt-section">
            <div class="receipt-section-title">Payment Information</div>
            <div class="receipt-info">
                <span class="receipt-info-label">Receipt Number:</span>
                <span class="receipt-info-value">#{{ $payment->order_id ?? $payment->id ?? 'N/A' }}</span>
            </div>
            <div class="receipt-info">
                <span class="receipt-info-label">Payment Date:</span>
                <span class="receipt-info-value">{{ $payment->paid_at->format('d M Y, h:i A') ?? $fine->paid_date->format('d M Y') ?? 'N/A' }}</span>
            </div>
            <div class="receipt-info">
                <span class="receipt-info-label">Payment Method:</span>
                <span class="receipt-info-value">{{ ucfirst($payment->payment_method ?? 'Online') }}</span>
            </div>
            <div class="receipt-info">
                <span class="receipt-info-label">Payment Status:</span>
                <span class="receipt-info-value">
                    <span class="status-badge">✅ Paid</span>
                </span>
            </div>
            @if($payment && $payment->payment_id)
            <div class="receipt-info">
                <span class="receipt-info-label">Transaction ID:</span>
                <span class="receipt-info-value">{{ $payment->payment_id }}</span>
            </div>
            @endif
        </div>

        <div class="receipt-section">
            <div class="receipt-section-title">Fine Details</div>
            <div class="receipt-info">
                <span class="receipt-info-label">Fine ID:</span>
                <span class="receipt-info-value">#{{ $fine->id }}</span>
            </div>
            <div class="receipt-info">
                <span class="receipt-info-label">Book:</span>
                <span class="receipt-info-value">{{ $fine->borrow->book->title ?? 'N/A' }}</span>
            </div>
            <div class="receipt-info">
                <span class="receipt-info-label">ISBN:</span>
                <span class="receipt-info-value">{{ $fine->borrow->book->isbn ?? 'N/A' }}</span>
            </div>
            <div class="receipt-info">
                <span class="receipt-info-label">Reason:</span>
                <span class="receipt-info-value">{{ $fine->reason }}</span>
            </div>
            <div class="receipt-info">
                <span class="receipt-info-label">Fine Date:</span>
                <span class="receipt-info-value">{{ $fine->created_at->format('d M Y') }}</span>
            </div>
        </div>

        <div class="receipt-section">
            <div class="receipt-section-title">Student Information</div>
            <div class="receipt-info">
                <span class="receipt-info-label">Name:</span>
                <span class="receipt-info-value">{{ $user->name }}</span>
            </div>
            <div class="receipt-info">
                <span class="receipt-info-label">Email:</span>
                <span class="receipt-info-value">{{ $user->email }}</span>
            </div>
            @if($user->student_id)
            <div class="receipt-info">
                <span class="receipt-info-label">Student ID:</span>
                <span class="receipt-info-value">{{ $user->student_id }}</span>
            </div>
            @endif
        </div>

        <div class="receipt-amount">
            <div class="receipt-amount-label">Total Amount Paid</div>
            <div class="receipt-amount-value">₹{{ number_format($fine->amount, 2) }}</div>
        </div>

        <div class="receipt-footer">
            <p class="mb-2">
                <strong>This is a computer-generated receipt.</strong>
            </p>
            <p class="mb-0">
                Generated on {{ now()->format('d M Y, h:i A') }}
            </p>
            <p class="mt-2 mb-0">
                <i class="fas fa-info-circle me-1"></i>
                Please keep this receipt for your records.
            </p>
        </div>
    </div>

    <div class="text-center mt-4 mb-4 no-print">
        <button onclick="window.print()" class="btn btn-primary">
            <i class="fas fa-print me-2"></i>Print Receipt
        </button>
        <a href="{{ route('student.fines.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Back to Fines
        </a>
    </div>
</body>
</html>

