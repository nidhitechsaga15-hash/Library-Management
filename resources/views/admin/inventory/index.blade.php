@extends('layouts.admin')

@section('title', 'Inventory Management')
@section('page-title', 'Inventory Management')

@section('content')
<!-- Statistics Cards -->
<div class="row g-4 mb-4">
    <div class="col-md-6 col-lg-3">
        <div class="stat-card">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div class="flex-grow-1">
                    <p class="text-muted mb-1 small fw-semibold">Total Books</p>
                    <h2 class="mb-0 fw-bold stat-value">{{ number_format($stats['total_books']) }}</h2>
                    <p class="text-muted mb-0 small stat-desc">All copies in library</p>
                </div>
                <div class="stat-icon primary">
                    <i class="fas fa-book"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-6 col-lg-3">
        <div class="stat-card">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div class="flex-grow-1">
                    <p class="text-muted mb-1 small fw-semibold">Available Books</p>
                    <h2 class="mb-0 fw-bold stat-value">{{ number_format($stats['available_books']) }}</h2>
                    <p class="text-muted mb-0 small stat-desc">Ready to issue</p>
                </div>
                <div class="stat-icon success">
                    <i class="fas fa-check-circle"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-6 col-lg-3">
        <div class="stat-card">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div class="flex-grow-1">
                    <p class="text-muted mb-1 small fw-semibold">Issued Books</p>
                    <h2 class="mb-0 fw-bold stat-value">{{ number_format($stats['issued_books']) }}</h2>
                    <p class="text-muted mb-0 small stat-desc">Currently borrowed</p>
                </div>
                <div class="stat-icon warning">
                    <i class="fas fa-clock"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-6 col-lg-3">
        <div class="stat-card">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div class="flex-grow-1">
                    <p class="text-muted mb-1 small fw-semibold">Low Stock</p>
                    <h2 class="mb-0 fw-bold stat-value text-warning">{{ $stats['low_stock_count'] }}</h2>
                    <p class="text-muted mb-0 small stat-desc">≤{{ $lowStockThreshold }} copies</p>
                </div>
                <div class="stat-icon warning">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Alerts Section -->
<div class="row g-4 mb-4">
    @if($stats['low_stock_count'] > 0)
    <div class="col-12">
        <div class="alert alert-warning d-flex align-items-center">
            <i class="fas fa-exclamation-triangle me-3 fs-4"></i>
            <div>
                <strong>Low Stock Alert:</strong> {{ $stats['low_stock_count'] }} book(s) have low stock (≤{{ $lowStockThreshold }} copies available).
                <a href="{{ route('admin.inventory.alerts') }}" class="alert-link ms-2">View Details</a>
            </div>
        </div>
    </div>
    @endif
    
    @if($stats['out_of_stock_count'] > 0)
    <div class="col-12">
        <div class="alert alert-danger d-flex align-items-center">
            <i class="fas fa-times-circle me-3 fs-4"></i>
            <div>
                <strong>Out of Stock:</strong> {{ $stats['out_of_stock_count'] }} book(s) are out of stock.
                <a href="{{ route('admin.inventory.alerts') }}" class="alert-link ms-2">View Details</a>
            </div>
        </div>
    </div>
    @endif
    
    @if($stats['missing_books_count'] > 0)
    <div class="col-12">
        <div class="alert alert-danger d-flex align-items-center">
            <i class="fas fa-search me-3 fs-4"></i>
            <div>
                <strong>Missing Books:</strong> {{ $stats['missing_books_count'] }} book(s) reported as missing.
                <a href="{{ route('admin.inventory.alerts') }}" class="alert-link ms-2">View Details</a>
            </div>
        </div>
    </div>
    @endif
</div>

<!-- Low Stock Books -->
@if($lowStockBooks->count() > 0)
<div class="card-modern mb-4">
    <div class="card-header-modern">
        <h5>
            <i class="fas fa-exclamation-triangle text-warning me-2"></i>
            Low Stock Books (≤{{ $lowStockThreshold }} copies)
        </h5>
        <a href="{{ route('admin.inventory.alerts') }}" class="btn btn-sm btn-warning">
            View All Alerts
        </a>
    </div>
    <div class="card-body p-4">
        <div class="table-responsive">
            <table class="table table-modern">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Author</th>
                        <th>Category</th>
                        <th>Available</th>
                        <th>Total</th>
                        <th>Library</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($lowStockBooks->take(10) as $book)
                    <tr>
                        <td>{{ $book->title }}</td>
                        <td>{{ $book->author->name ?? '-' }}</td>
                        <td>{{ $book->category->name ?? '-' }}</td>
                        <td><span class="badge bg-warning">{{ $book->available_copies }}</span></td>
                        <td>{{ $book->total_copies }}</td>
                        <td>{{ $book->library->name ?? '-' }}</td>
                        <td>
                            <a href="{{ route('admin.books.show', $book) }}" class="btn btn-sm btn-primary">
                                <i class="fas fa-eye"></i>
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif

<!-- Out of Stock Books -->
@if($outOfStockBooks->count() > 0)
<div class="card-modern mb-4">
    <div class="card-header-modern">
        <h5>
            <i class="fas fa-times-circle text-danger me-2"></i>
            Out of Stock Books
        </h5>
    </div>
    <div class="card-body p-4">
        <div class="table-responsive">
            <table class="table table-modern">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Author</th>
                        <th>Category</th>
                        <th>Total Copies</th>
                        <th>Library</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($outOfStockBooks->take(10) as $book)
                    <tr>
                        <td>{{ $book->title }}</td>
                        <td>{{ $book->author->name ?? '-' }}</td>
                        <td>{{ $book->category->name ?? '-' }}</td>
                        <td>{{ $book->total_copies }}</td>
                        <td>{{ $book->library->name ?? '-' }}</td>
                        <td>
                            <a href="{{ route('admin.books.show', $book) }}" class="btn btn-sm btn-primary">
                                <i class="fas fa-eye"></i>
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif

<!-- Missing Books -->
@if($missingBooks->count() > 0)
<div class="card-modern mb-4">
    <div class="card-header-modern">
        <h5>
            <i class="fas fa-search text-danger me-2"></i>
            Missing Books Reports
        </h5>
        <a href="{{ route('admin.book-conditions.index') }}" class="btn btn-sm btn-primary">
            Manage Reports
        </a>
    </div>
    <div class="card-body p-4">
        <div class="table-responsive">
            <table class="table table-modern">
                <thead>
                    <tr>
                        <th>Book</th>
                        <th>Author</th>
                        <th>Reported By</th>
                        <th>Reported Date</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($missingBooks->take(10) as $condition)
                    <tr>
                        <td>{{ $condition->book->title ?? '-' }}</td>
                        <td>{{ $condition->book->author->name ?? '-' }}</td>
                        <td>{{ $condition->reportedBy->name ?? '-' }}</td>
                        <td>{{ $condition->created_at->format('M d, Y') }}</td>
                        <td><span class="badge bg-warning">Pending</span></td>
                        <td>
                            <a href="{{ route('admin.book-conditions.show', $condition) }}" class="btn btn-sm btn-primary">
                                <i class="fas fa-eye"></i>
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif

<!-- Damaged Books -->
@if($damagedBooks->count() > 0)
<div class="card-modern mb-4">
    <div class="card-header-modern">
        <h5>
            <i class="fas fa-exclamation-circle text-warning me-2"></i>
            Damaged Books Reports
        </h5>
        <a href="{{ route('admin.book-conditions.index') }}" class="btn btn-sm btn-primary">
            Manage Reports
        </a>
    </div>
    <div class="card-body p-4">
        <div class="table-responsive">
            <table class="table table-modern">
                <thead>
                    <tr>
                        <th>Book</th>
                        <th>Author</th>
                        <th>Reported By</th>
                        <th>Reported Date</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($damagedBooks->take(10) as $condition)
                    <tr>
                        <td>{{ $condition->book->title ?? '-' }}</td>
                        <td>{{ $condition->book->author->name ?? '-' }}</td>
                        <td>{{ $condition->reportedBy->name ?? '-' }}</td>
                        <td>{{ $condition->created_at->format('M d, Y') }}</td>
                        <td><span class="badge bg-warning">Pending</span></td>
                        <td>
                            <a href="{{ route('admin.book-conditions.show', $condition) }}" class="btn btn-sm btn-primary">
                                <i class="fas fa-eye"></i>
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif

@if($lowStockBooks->count() == 0 && $outOfStockBooks->count() == 0 && $missingBooks->count() == 0 && $damagedBooks->count() == 0)
<div class="card-modern">
    <div class="card-body p-5 text-center">
        <i class="fas fa-check-circle text-success fs-1 mb-3"></i>
        <h5>All Good!</h5>
        <p class="text-muted">No inventory alerts at this time. All books are in good stock.</p>
    </div>
</div>
@endif
@endsection

