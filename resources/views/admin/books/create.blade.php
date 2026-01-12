@extends('layouts.admin')

@section('title', 'Add New Book')
@section('page-title', 'Add New Book')

@php
use Illuminate\Support\Facades\Storage;
@endphp

@section('content')
<div class="card-modern">
    <div class="card-header-modern">
        <h5>
            <i class="fas fa-book text-primary"></i>
            Add New Book
        </h5>
    </div>
    <div class="card-body p-4">
        <form action="{{ route('admin.books.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            
            <div class="row g-3">
                <!-- Row 1: ISBN and Title -->
                <div class="col-12 col-sm-6 col-md-6 col-lg-6 col-xl-6">
                    <label for="isbn" class="form-label fw-semibold">
                        ISBN <span class="text-danger">*</span>
                        <button type="button" class="btn btn-sm btn-outline-primary ms-2" id="scan-barcode-btn" title="Scan Barcode">
                            <i class="fas fa-barcode me-1"></i>Scan
                        </button>
                    </label>
                    <div class="input-group">
                        <input type="text" class="form-control form-control-lg @error('isbn') is-invalid @enderror" 
                            name="isbn" id="isbn" value="{{ old('isbn') }}" placeholder="Enter ISBN or scan barcode" required>
                        <button type="button" class="btn btn-outline-secondary" id="stop-scan-btn" style="display: none;" title="Stop Scanner">
                            <i class="fas fa-stop"></i>
                        </button>
                    </div>
                    <small class="form-text text-muted">Use barcode scanner to auto-fill ISBN</small>
                    @error('isbn')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                    <div id="barcode-scanner" style="display: none;" class="mt-3">
                        <video id="barcode-video" width="100%" style="max-width: 400px; border-radius: 8px;" autoplay></video>
                        <canvas id="barcode-canvas" style="display: none;"></canvas>
                    </div>
                </div>

                <div class="col-12 col-sm-6 col-md-6 col-lg-6 col-xl-6">
                    <label for="title" class="form-label fw-semibold">Title <span class="text-danger">*</span></label>
                    <input type="text" class="form-control form-control-lg @error('title') is-invalid @enderror" 
                        name="title" id="title" value="{{ old('title') }}" placeholder="Enter Book Title" required>
                    @error('title')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Row 2: Author and Category -->
                <div class="col-12 col-sm-6 col-md-6 col-lg-6 col-xl-6">
                    <label for="author_id" class="form-label fw-semibold">Author <span class="text-danger">*</span></label>
                    <select class="form-select form-select-lg @error('author_id') is-invalid @enderror" 
                        name="author_id" id="author_id" required>
                        <option value="">Select Author</option>
                        @foreach($authors as $author)
                            <option value="{{ $author->id }}" {{ old('author_id') == $author->id ? 'selected' : '' }}>
                                {{ $author->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('author_id')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12 col-sm-6 col-md-6 col-lg-6 col-xl-6">
                    <label for="category_id" class="form-label fw-semibold">Category <span class="text-danger">*</span></label>
                    <select class="form-select form-select-lg @error('category_id') is-invalid @enderror" 
                        name="category_id" id="category_id" required>
                        <option value="">Select Category</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('category_id')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Row 3: Publisher and Edition -->
                <div class="col-12 col-sm-6 col-md-6 col-lg-6 col-xl-6">
                    <label for="publisher" class="form-label fw-semibold">Publisher</label>
                    <input type="text" class="form-control form-control-lg @error('publisher') is-invalid @enderror" 
                        name="publisher" id="publisher" value="{{ old('publisher') }}" placeholder="Enter Publisher Name">
                    @error('publisher')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12 col-sm-6 col-md-6 col-lg-6 col-xl-6">
                    <label for="edition" class="form-label fw-semibold">Edition</label>
                    <input type="text" class="form-control form-control-lg @error('edition') is-invalid @enderror" 
                        name="edition" id="edition" value="{{ old('edition') }}" placeholder="e.g., 1st, 2nd, 3rd">
                    @error('edition')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Row 4: Publication Year and Language -->
                <div class="col-12 col-sm-6 col-md-6 col-lg-6 col-xl-6">
                    <label for="publication_year" class="form-label fw-semibold">Publication Year</label>
                    <input type="number" class="form-control form-control-lg @error('publication_year') is-invalid @enderror" 
                        name="publication_year" id="publication_year" value="{{ old('publication_year') }}" 
                        placeholder="e.g., 2024" min="1900" max="{{ date('Y') }}">
                    @error('publication_year')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12 col-sm-6 col-md-6 col-lg-6 col-xl-6">
                    <label for="language" class="form-label fw-semibold">Language</label>
                    <input type="text" class="form-control form-control-lg @error('language') is-invalid @enderror" 
                        name="language" id="language" value="{{ old('language', 'English') }}" placeholder="Enter Language">
                    @error('language')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Row 5: Available Copies -->
                <div class="col-12 col-sm-6 col-md-6 col-lg-6 col-xl-6">
                    <label for="available_copies" class="form-label fw-semibold">Available Copies <span class="text-danger">*</span></label>
                    <input type="number" class="form-control form-control-lg @error('available_copies') is-invalid @enderror" 
                        name="available_copies" id="available_copies" value="{{ old('available_copies', 1) }}" 
                        placeholder="Enter Available Copies" required min="0">
                    <small class="form-text text-muted">Total copies will be set equal to available copies</small>
                    @error('available_copies')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Row 6: Rack Number and Pages -->
                <div class="col-12 col-sm-6 col-md-6 col-lg-6 col-xl-6">
                    <label for="rack_number" class="form-label fw-semibold">Rack Number</label>
                    <input type="text" class="form-control form-control-lg @error('rack_number') is-invalid @enderror" 
                        name="rack_number" id="rack_number" value="{{ old('rack_number') }}" placeholder="e.g., A-101, B-205">
                    @error('rack_number')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12 col-sm-6 col-md-6 col-lg-6 col-xl-6">
                    <label for="pages" class="form-label fw-semibold">Pages</label>
                    <input type="number" class="form-control form-control-lg @error('pages') is-invalid @enderror" 
                        name="pages" id="pages" value="{{ old('pages') }}" placeholder="Enter Number of Pages" min="1">
                    @error('pages')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Row 6.5: Book Location Section -->
                <div class="col-12">
                    <hr class="my-3">
                    <h6 class="fw-bold mb-3"><i class="fas fa-map-marker-alt text-primary me-2"></i>Book Location</h6>
                </div>

                <div class="col-12 col-sm-6 col-md-4 col-lg-4 col-xl-4">
                    <label for="almirah" class="form-label fw-semibold">Almirah Number</label>
                    <input type="text" class="form-control form-control-lg @error('almirah') is-invalid @enderror" 
                        name="almirah" id="almirah" value="{{ old('almirah') }}" placeholder="e.g., A-1, B-2">
                    <small class="form-text text-muted">Almirah where book is stored</small>
                    @error('almirah')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12 col-sm-6 col-md-4 col-lg-4 col-xl-4">
                    <label for="row" class="form-label fw-semibold">Row Number</label>
                    <input type="text" class="form-control form-control-lg @error('row') is-invalid @enderror" 
                        name="row" id="row" value="{{ old('row') }}" placeholder="e.g., R-5, R-10">
                    <small class="form-text text-muted">Row within almirah</small>
                    @error('row')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12 col-sm-6 col-md-4 col-lg-4 col-xl-4">
                    <label for="book_serial" class="form-label fw-semibold">Book Serial Number</label>
                    <input type="text" class="form-control form-control-lg @error('book_serial') is-invalid @enderror" 
                        name="book_serial" id="book_serial" value="{{ old('book_serial') }}" placeholder="e.g., S-223, S-001">
                    <small class="form-text text-muted">Unique serial number for this book</small>
                    @error('book_serial')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>


                <!-- Row 7: Status -->
                <div class="col-12 col-sm-6 col-md-6 col-lg-6 col-xl-6">
                    <label for="status" class="form-label fw-semibold">Status <span class="text-danger">*</span></label>
                    <select class="form-select form-select-lg @error('status') is-invalid @enderror" 
                        name="status" id="status" required>
                        <option value="available" {{ old('status') == 'available' ? 'selected' : '' }}>Available</option>
                        <option value="unavailable" {{ old('status') == 'unavailable' ? 'selected' : '' }}>Unavailable</option>
                    </select>
                    @error('status')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Row 8: Description (Full Width) -->
                <div class="col-12">
                    <label for="description" class="form-label fw-semibold">Description</label>
                    <textarea class="form-control @error('description') is-invalid @enderror" 
                        name="description" id="description" rows="5" placeholder="Enter book description...">{{ old('description') }}</textarea>
                    @error('description')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Row 9: Cover Image (Full Width) -->
                <div class="col-12">
                    <label for="cover_image" class="form-label fw-semibold">Book Cover Image <span class="text-muted">(Optional)</span></label>
                    <input type="file" class="form-control form-control-lg @error('cover_image') is-invalid @enderror" 
                        name="cover_image" id="cover_image" accept="image/*">
                    <small class="form-text text-muted d-block mt-1">
                        <i class="fas fa-info-circle"></i> Max size: 2MB. Formats: JPEG, PNG, JPG, GIF
                    </small>
                    @error('cover_image')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Row 10: Notify Students Checkbox (Full Width) -->
                <div class="col-12">
                    <div class="form-check p-3 bg-light rounded">
                        <input class="form-check-input" type="checkbox" name="notify_students" id="notify_students" value="1">
                        <label class="form-check-label fw-semibold" for="notify_students">
                            <i class="fas fa-bell text-primary me-2"></i>Notify all students about this new book arrival
                        </label>
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="row mt-4">
                <div class="col-12">
                    <div class="form-actions">
                        <a href="{{ route('admin.books.index') }}" class="btn btn-secondary btn-lg">
                            <i class="fas fa-times me-2"></i>Cancel
                        </a>
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-save me-2"></i>Create Book
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/jsqr@1.4.0/dist/jsQR.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const scanBtn = document.getElementById('scan-barcode-btn');
    const stopBtn = document.getElementById('stop-scan-btn');
    const scannerDiv = document.getElementById('barcode-scanner');
    const video = document.getElementById('barcode-video');
    const canvas = document.getElementById('barcode-canvas');
    const isbnInput = document.getElementById('isbn');
    let stream = null;
    let scanning = false;

    scanBtn.addEventListener('click', async function() {
        try {
            stream = await navigator.mediaDevices.getUserMedia({ 
                video: { facingMode: 'environment' } 
            });
            video.srcObject = stream;
            scannerDiv.style.display = 'block';
            scanBtn.style.display = 'none';
            stopBtn.style.display = 'inline-block';
            
            // Wait for video metadata to load before starting scan
            video.addEventListener('loadedmetadata', function() {
                video.play().then(() => {
                    scanning = true;
                    scanBarcode();
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
        scannerDiv.style.display = 'none';
        scanBtn.style.display = 'inline-block';
        stopBtn.style.display = 'none';
    });

    function scanBarcode() {
        if (!scanning) return;

        // Check if video has valid dimensions
        if (!video.videoWidth || !video.videoHeight || video.videoWidth === 0 || video.videoHeight === 0) {
            requestAnimationFrame(scanBarcode);
            return;
        }

        try {
            const context = canvas.getContext('2d');
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            context.drawImage(video, 0, 0, canvas.width, canvas.height);

            const imageData = context.getImageData(0, 0, canvas.width, canvas.height);
            
            // Try jsQR for QR codes
            const qrCode = jsQR(imageData.data, imageData.width, imageData.height);
            if (qrCode && qrCode.data) {
                isbnInput.value = qrCode.data;
                stopBtn.click();
                return;
            }

            // Continue scanning
            requestAnimationFrame(scanBarcode);
        } catch (e) {
            console.error('Scan error:', e);
            // Continue scanning even if there's an error
            requestAnimationFrame(scanBarcode);
        }
    }

    // Also allow manual barcode input (keyboard scanner simulation)
    let barcodeBuffer = '';
    let lastKeyTime = Date.now();
    isbnInput.addEventListener('keypress', function(e) {
        const currentTime = Date.now();
        if (currentTime - lastKeyTime > 100) {
            barcodeBuffer = '';
        }
        lastKeyTime = currentTime;
        
        if (e.key === 'Enter' && barcodeBuffer.length > 5) {
            e.preventDefault();
            isbnInput.value = barcodeBuffer;
            barcodeBuffer = '';
        } else if (e.key.length === 1) {
            barcodeBuffer += e.key;
        }
    });
});
</script>
@endpush
@endsection
