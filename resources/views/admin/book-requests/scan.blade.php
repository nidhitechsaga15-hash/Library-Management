@extends('layouts.admin')

@section('title', 'Scan Book QR Code')
@section('page-title', 'Scan Book QR Code')

@section('content')
<div class="card-modern">
    <div class="card-header-modern">
        <h5>
            <i class="fas fa-qrcode text-primary"></i>
            Scan Book QR Code
        </h5>
        <a href="{{ route('admin.book-requests.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Back to Requests
        </a>
    </div>
    <div class="card-body p-4">
        <div class="row">
            <div class="col-12 col-md-8 mx-auto">
                <div class="text-center mb-4">
                    <h6 class="mb-3">Scan QR Code from Book Request</h6>
                    <p class="text-muted">Use your device camera to scan QR code, or enter the Book Request ID manually</p>
                    <p class="text-muted small">
                        <i class="fas fa-info-circle me-1"></i>
                        <strong>Book Request ID</strong> is the number shown in the "#" column on the Book Requests page
                    </p>
                </div>

                <!-- QR Scanner -->
                <div class="card border mb-4">
                    <div class="card-body text-center p-5">
                        <div id="scanner-container" class="mb-3">
                            <video id="video" width="100%" style="max-width: 400px; border-radius: 8px;" autoplay></video>
                            <canvas id="canvas" style="display: none;"></canvas>
                        </div>
                        <button id="start-scanner" class="btn btn-primary btn-lg">
                            <i class="fas fa-camera me-2"></i>Start Scanner
                        </button>
                        <button id="stop-scanner" class="btn btn-secondary btn-lg" style="display: none;">
                            <i class="fas fa-stop me-2"></i>Stop Scanner
                        </button>
                    </div>
                </div>

                <!-- Manual Entry -->
                <div class="card border">
                    <div class="card-body">
                        <h6 class="mb-3">
                            <i class="fas fa-keyboard me-2"></i>Or Enter Book Request ID Manually
                        </h6>
                        <div class="alert alert-info mb-3">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>What is Book Request ID?</strong><br>
                            <small>It's the number shown in the <strong>"#"</strong> column on the Book Requests page. For example: 1, 2, 3, etc.</small>
                        </div>
                        <form action="{{ route('admin.book-requests.scan-result', ['request' => 0]) }}" method="GET" id="manual-form">
                            <div class="input-group input-group-lg">
                                <span class="input-group-text">
                                    <i class="fas fa-hashtag"></i>
                                </span>
                                <input type="number" class="form-control" name="request_id" id="request_id" 
                                    placeholder="Enter Book Request ID (e.g., 1, 2, 3...)" 
                                    min="1" required>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search me-2"></i>Search
                                </button>
                            </div>
                            <small class="form-text text-muted mt-2 d-block">
                                <i class="fas fa-lightbulb me-1"></i>
                                Don't know the ID? 
                                <a href="{{ route('admin.book-requests.index') }}" class="text-primary">
                                    View all Book Requests
                                </a>
                            </small>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/jsqr@1.4.0/dist/jsQR.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const video = document.getElementById('video');
    const canvas = document.getElementById('canvas');
    const startBtn = document.getElementById('start-scanner');
    const stopBtn = document.getElementById('stop-scanner');
    const scannerContainer = document.getElementById('scanner-container');
    let stream = null;
    let scanning = false;

    startBtn.addEventListener('click', async function() {
        try {
            stream = await navigator.mediaDevices.getUserMedia({ 
                video: { facingMode: 'environment' } 
            });
            video.srcObject = stream;
            startBtn.style.display = 'none';
            stopBtn.style.display = 'inline-block';
            
            // Wait for video metadata to load before starting scan
            video.addEventListener('loadedmetadata', function() {
                video.play().then(() => {
                    scanning = true;
                    scanQR();
                }).catch(err => {
                    console.error('Error playing video:', err);
                    alert('Error starting camera: ' + err.message);
                    stopBtn.click();
                });
            }, { once: true });
        } catch (err) {
            alert('Error accessing camera: ' + err.message);
        }
    });

    stopBtn.addEventListener('click', function() {
        scanning = false;
        if (stream) {
            stream.getTracks().forEach(track => track.stop());
            stream = null;
        }
        video.srcObject = null;
        startBtn.style.display = 'inline-block';
        stopBtn.style.display = 'none';
    });

    function scanQR() {
        if (!scanning) return;

        // Check if video has valid dimensions
        if (!video.videoWidth || !video.videoHeight || video.videoWidth === 0 || video.videoHeight === 0) {
            requestAnimationFrame(scanQR);
            return;
        }

        try {
            const context = canvas.getContext('2d');
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            context.drawImage(video, 0, 0, canvas.width, canvas.height);

            const imageData = context.getImageData(0, 0, canvas.width, canvas.height);
            const code = jsQR(imageData.data, imageData.width, imageData.height);

            if (code && code.data) {
                // QR code detected
                const requestId = extractRequestId(code.data);
                if (requestId) {
                    window.location.href = '{{ route("admin.book-requests.scan-result", ["request" => ":id"]) }}'.replace(':id', requestId);
                    return;
                } else {
                    // Invalid format, continue scanning
                    requestAnimationFrame(scanQR);
                }
            } else {
                requestAnimationFrame(scanQR);
            }
        } catch (e) {
            console.error('Scan error:', e);
            // Continue scanning even if there's an error
            requestAnimationFrame(scanQR);
        }
    }

    function extractRequestId(qrData) {
        // Try to extract request ID from QR code
        // Format could be: "book-request-123" or just "123" or URL
        const match = qrData.match(/(?:book-request-|request\/)(\d+)/i) || qrData.match(/^(\d+)$/);
        return match ? match[1] : null;
    }

    // Handle manual form submission
    document.getElementById('manual-form').addEventListener('submit', function(e) {
        e.preventDefault();
        const requestId = document.getElementById('request_id').value;
        if (requestId) {
            window.location.href = '{{ route("admin.book-requests.scan-result", ["request" => ":id"]) }}'.replace(':id', requestId);
        }
    });
});
</script>
@endpush
@endsection

