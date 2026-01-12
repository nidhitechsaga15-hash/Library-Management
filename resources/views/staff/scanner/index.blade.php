@extends('layouts.staff')

@section('title', 'Barcode/QR Scanner')
@section('page-title', 'Barcode/QR Scanner')

@push('styles')
<style>
    #scanner-container {
        position: relative;
        width: 100%;
        max-width: 500px;
        margin: 0 auto;
    }
    #video {
        width: 100%;
        border-radius: 8px;
    }
    #scanner-overlay {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 200px;
        height: 200px;
        border: 3px solid #10b981;
        border-radius: 8px;
        pointer-events: none;
    }
</style>
@endpush

@section('content')
<div class="card-modern">
    <div class="card-header-modern">
        <h5>
            <i class="fas fa-qrcode text-primary"></i>
            Scan Book Barcode/QR Code
        </h5>
    </div>
    <div class="card-body p-4">
        <div class="row g-4">
            <!-- Scanner Section -->
            <div class="col-12 col-md-6">
                <div id="scanner-container" class="mb-4">
                    <video id="video" autoplay playsinline></video>
                    <div id="scanner-overlay"></div>
                </div>
                <div class="row g-2 mb-4">
                    <div class="col-6">
                        <button id="start-scanner" class="btn btn-success w-100">
                            <i class="fas fa-play me-2"></i>Start Scanner
                        </button>
                    </div>
                    <div class="col-6">
                        <button id="stop-scanner" class="btn btn-danger w-100" disabled>
                            <i class="fas fa-stop me-2"></i>Stop Scanner
                        </button>
                    </div>
                </div>
                <div class="mb-4">
                    <label for="manual-code" class="form-label fw-semibold">Or Enter Code Manually</label>
                    <input type="text" id="manual-code" placeholder="Enter ISBN or Book ID" 
                        class="form-control form-control-lg mb-2">
                    <button id="manual-scan" class="btn btn-primary w-100">
                        <i class="fas fa-search me-2"></i>Scan Code
                    </button>
                </div>
            </div>

            <!-- Actions Section -->
            <div class="col-12 col-md-6">
                <div id="scan-result" class="alert alert-info mb-4 d-none">
                    <h6 class="fw-bold mb-2">Scanned Book</h6>
                    <div id="book-info" class="small"></div>
                </div>

                <div class="row g-3">
                    <div class="col-12">
                        <div class="card border-primary">
                            <div class="card-header bg-primary text-white">
                                <h6 class="mb-0 fw-bold">
                                    <i class="fas fa-book me-2"></i>Issue Book
                                </h6>
                            </div>
                            <div class="card-body">
                                <form id="issue-form" action="{{ route('staff.scanner.issue') }}" method="POST">
                                    @csrf
                                    <div class="mb-3">
                                        <label for="user-code" class="form-label fw-semibold">Student ID/Email <span class="text-danger">*</span></label>
                                        <input type="text" name="user_code" id="user-code" required
                                            placeholder="Scan or enter student ID/email"
                                            class="form-control form-control-lg">
                                    </div>
                                    <input type="hidden" name="book_code" id="issue-book-code">
                                    <button type="submit" class="btn btn-primary w-100">
                                        <i class="fas fa-book me-2"></i>Issue Book
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="card border-success">
                            <div class="card-header bg-success text-white">
                                <h6 class="mb-0 fw-bold">
                                    <i class="fas fa-undo me-2"></i>Return Book
                                </h6>
                            </div>
                            <div class="card-body">
                                <form id="return-form" action="{{ route('staff.scanner.return') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="book_code" id="return-book-code">
                                    <button type="submit" class="btn btn-success w-100">
                                        <i class="fas fa-undo me-2"></i>Return Book
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/html5-qrcode@latest/dist/html5-qrcode.min.js"></script>
<script>
let html5QrCode;
let scanning = false;

document.getElementById('start-scanner').addEventListener('click', async () => {
    try {
        html5QrCode = new Html5Qrcode("video");
        await html5QrCode.start(
            { facingMode: "environment" },
            {
                fps: 10,
                qrbox: { width: 200, height: 200 }
            },
            onScanSuccess
        );
        scanning = true;
        document.getElementById('start-scanner').disabled = true;
        document.getElementById('stop-scanner').disabled = false;
    } catch (err) {
        console.error(err);
        alert('Failed to start scanner. Please check camera permissions.');
    }
});

document.getElementById('stop-scanner').addEventListener('click', () => {
    if (html5QrCode && scanning) {
        html5QrCode.stop().then(() => {
            scanning = false;
            document.getElementById('start-scanner').disabled = false;
            document.getElementById('stop-scanner').disabled = true;
        });
    }
});

function onScanSuccess(decodedText, decodedResult) {
    if (scanning) {
        handleScan(decodedText);
        // Stop after successful scan
        html5QrCode.stop();
        scanning = false;
        document.getElementById('start-scanner').disabled = false;
        document.getElementById('stop-scanner').disabled = true;
    }
}

document.getElementById('manual-scan').addEventListener('click', () => {
    const code = document.getElementById('manual-code').value;
    if (code) {
        handleScan(code);
    }
});

function handleScan(code) {
    fetch('{{ route("staff.scanner.scan") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ code: code })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById('scan-result').classList.remove('d-none');
            document.getElementById('book-info').innerHTML = `
                <p class="mb-1"><strong>Title:</strong> ${data.book.title}</p>
                <p class="mb-1"><strong>ISBN:</strong> ${data.book.isbn}</p>
                <p class="mb-0"><strong>Available:</strong> ${data.book.available_copies} copies</p>
            `;
            document.getElementById('issue-book-code').value = code;
            document.getElementById('return-book-code').value = code;
            document.getElementById('manual-code').value = code;
        } else {
            alert('Book not found!');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error scanning code. Please try again.');
    });
}
</script>
@endpush
@endsection
