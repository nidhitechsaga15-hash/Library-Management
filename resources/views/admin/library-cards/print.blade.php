<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Library Card - {{ $libraryCard->card_number }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        @media print {
            body { margin: 0; }
            .no-print { display: none !important; }
        }
        .library-card {
            width: 85mm;
            height: 54mm;
            border: 2px solid #333;
            border-radius: 8px;
            padding: 10px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            position: relative;
            margin: 20px auto;
        }
        .card-header {
            text-align: center;
            border-bottom: 1px solid rgba(255,255,255,0.3);
            padding-bottom: 5px;
            margin-bottom: 8px;
        }
        .card-body {
            font-size: 10px;
        }
        .card-number {
            font-size: 14px;
            font-weight: bold;
            text-align: center;
            margin: 5px 0;
        }
        .qr-code {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            width: 50px;
            height: 50px;
        }
        .student-info {
            font-size: 9px;
            line-height: 1.4;
        }
    </style>
</head>
<body>
    <div class="container my-4">
        <div class="text-center mb-3 no-print">
            <button onclick="window.print()" class="btn btn-primary">
                <i class="fas fa-print me-2"></i>Print Card
            </button>
            <a href="{{ route('admin.library-cards.show', $libraryCard) }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i>Back
            </a>
        </div>

        <div class="library-card">
            <div class="card-header">
                <h5 class="mb-0" style="font-size: 12px;">LIBRARY CARD</h5>
            </div>
            <div class="card-body">
                <div class="card-number">{{ $libraryCard->card_number }}</div>
                <div class="student-info">
                    <div><strong>Name:</strong> {{ $libraryCard->user->name }}</div>
                    <div><strong>ID:</strong> {{ $libraryCard->user->student_id ?? 'N/A' }}</div>
                    <div><strong>Course:</strong> {{ $libraryCard->user->course ?? 'N/A' }}</div>
                    <div><strong>Batch:</strong> {{ $libraryCard->user->batch ?? 'N/A' }}</div>
                    <div><strong>Valid Until:</strong> {{ $libraryCard->validity_date->format('M d, Y') }}</div>
                </div>
                @if($libraryCard->qr_code)
                <div class="qr-code">
                    {!! $libraryCard->qr_code !!}
                </div>
                @endif
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

