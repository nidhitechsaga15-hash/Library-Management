@extends('layouts.admin')

@section('title', 'Inventory Alerts')
@section('page-title', 'Inventory Alerts')

@section('content')
<!-- Tabs Navigation -->
<ul class="nav nav-tabs mb-4" id="alertsTabs" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active" id="low-stock-tab" data-bs-toggle="tab" data-bs-target="#low-stock" type="button" role="tab">
            <i class="fas fa-exclamation-triangle me-2"></i>Low Stock ({{ $alerts['low_stock']->count() }})
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="out-of-stock-tab" data-bs-toggle="tab" data-bs-target="#out-of-stock" type="button" role="tab">
            <i class="fas fa-times-circle me-2"></i>Out of Stock ({{ $alerts['out_of_stock']->count() }})
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="missing-tab" data-bs-toggle="tab" data-bs-target="#missing" type="button" role="tab">
            <i class="fas fa-search me-2"></i>Missing ({{ $alerts['missing']->count() }})
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="damaged-tab" data-bs-toggle="tab" data-bs-target="#damaged" type="button" role="tab">
            <i class="fas fa-exclamation-circle me-2"></i>Damaged ({{ $alerts['damaged']->count() }})
        </button>
    </li>
</ul>

<!-- Tabs Content -->
<div class="tab-content" id="alertsTabsContent">
    <!-- Low Stock Tab -->
    <div class="tab-pane fade show active" id="low-stock" role="tabpanel">
        <div class="card-modern">
            <div class="card-header-modern">
                <h5>
                    <i class="fas fa-exclamation-triangle text-warning me-2"></i>
                    Low Stock Books (≤{{ $lowStockThreshold }} copies)
                </h5>
                <a href="{{ route('admin.inventory.index') }}" class="btn btn-secondary btn-sm">
                    <i class="fas fa-arrow-left me-2"></i>Back
                </a>
            </div>
            <div class="card-body p-4">
                @if($alerts['low_stock']->count() > 0)
                <div class="table-responsive">
                    <table class="table table-modern">
                        <thead>
                            <tr>
                                <th>#</th>
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
                            @foreach($alerts['low_stock'] as $index => $book)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $book->title }}</td>
                                <td>{{ $book->author->name ?? '-' }}</td>
                                <td>{{ $book->category->name ?? '-' }}</td>
                                <td><span class="badge bg-warning">{{ $book->available_copies }}</span></td>
                                <td>{{ $book->total_copies }}</td>
                                <td>{{ $book->library->name ?? '-' }}</td>
                                <td>
                                    <a href="{{ route('admin.books.show', $book) }}" class="btn btn-sm btn-primary">
                                        <i class="fas fa-eye"></i> View
                                    </a>
                                    <a href="{{ route('admin.books.edit', $book) }}" class="btn btn-sm btn-warning">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="text-center p-5">
                    <i class="fas fa-check-circle text-success fs-1 mb-3"></i>
                    <h5>No Low Stock Books</h5>
                    <p class="text-muted">All books have sufficient stock.</p>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Out of Stock Tab -->
    <div class="tab-pane fade" id="out-of-stock" role="tabpanel">
        <div class="card-modern">
            <div class="card-header-modern">
                <h5>
                    <i class="fas fa-times-circle text-danger me-2"></i>
                    Out of Stock Books
                </h5>
            </div>
            <div class="card-body p-4">
                @if($alerts['out_of_stock']->count() > 0)
                <div class="table-responsive">
                    <table class="table table-modern">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Title</th>
                                <th>Author</th>
                                <th>Category</th>
                                <th>Total Copies</th>
                                <th>Library</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($alerts['out_of_stock'] as $index => $book)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $book->title }}</td>
                                <td>{{ $book->author->name ?? '-' }}</td>
                                <td>{{ $book->category->name ?? '-' }}</td>
                                <td><span class="badge bg-danger">{{ $book->total_copies }}</span></td>
                                <td>{{ $book->library->name ?? '-' }}</td>
                                <td>
                                    <a href="{{ route('admin.books.show', $book) }}" class="btn btn-sm btn-primary">
                                        <i class="fas fa-eye"></i> View
                                    </a>
                                    <a href="{{ route('admin.books.edit', $book) }}" class="btn btn-sm btn-warning">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="text-center p-5">
                    <i class="fas fa-check-circle text-success fs-1 mb-3"></i>
                    <h5>No Out of Stock Books</h5>
                    <p class="text-muted">All books have available copies.</p>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Missing Books Tab -->
    <div class="tab-pane fade" id="missing" role="tabpanel">
        <div class="card-modern">
            <div class="card-header-modern">
                <h5>
                    <i class="fas fa-search text-danger me-2"></i>
                    Missing Books Reports
                </h5>
                <a href="{{ route('admin.book-conditions.index') }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-cog me-2"></i>Manage Reports
                </a>
            </div>
            <div class="card-body p-4">
                @if($alerts['missing']->count() > 0)
                <div class="table-responsive">
                    <table class="table table-modern">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Book</th>
                                <th>Author</th>
                                <th>Reported By</th>
                                <th>Reported Date</th>
                                <th>Description</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($alerts['missing'] as $index => $condition)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $condition->book->title ?? '-' }}</td>
                                <td>{{ $condition->book->author->name ?? '-' }}</td>
                                <td>{{ $condition->reportedBy->name ?? '-' }}</td>
                                <td>{{ $condition->created_at->format('M d, Y') }}</td>
                                <td>{{ Str::limit($condition->description ?? '-', 50) }}</td>
                                <td><span class="badge bg-warning">Pending</span></td>
                                <td>
                                    <a href="{{ route('admin.book-conditions.show', $condition) }}" class="btn btn-sm btn-primary">
                                        <i class="fas fa-eye"></i> View
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="text-center p-5">
                    <i class="fas fa-check-circle text-success fs-1 mb-3"></i>
                    <h5>No Missing Books</h5>
                    <p class="text-muted">No books reported as missing.</p>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Damaged Books Tab -->
    <div class="tab-pane fade" id="damaged" role="tabpanel">
        <div class="card-modern">
            <div class="card-header-modern">
                <h5>
                    <i class="fas fa-exclamation-circle text-warning me-2"></i>
                    Damaged Books Reports
                </h5>
                <a href="{{ route('admin.book-conditions.index') }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-cog me-2"></i>Manage Reports
                </a>
            </div>
            <div class="card-body p-4">
                @if($alerts['damaged']->count() > 0)
                <div class="table-responsive">
                    <table class="table table-modern">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Book</th>
                                <th>Author</th>
                                <th>Reported By</th>
                                <th>Reported Date</th>
                                <th>Description</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($alerts['damaged'] as $index => $condition)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $condition->book->title ?? '-' }}</td>
                                <td>{{ $condition->book->author->name ?? '-' }}</td>
                                <td>{{ $condition->reportedBy->name ?? '-' }}</td>
                                <td>{{ $condition->created_at->format('M d, Y') }}</td>
                                <td>{{ Str::limit($condition->description ?? '-', 50) }}</td>
                                <td><span class="badge bg-warning">Pending</span></td>
                                <td>
                                    <a href="{{ route('admin.book-conditions.show', $condition) }}" class="btn btn-sm btn-primary">
                                        <i class="fas fa-eye"></i> View
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="text-center p-5">
                    <i class="fas fa-check-circle text-success fs-1 mb-3"></i>
                    <h5>No Damaged Books</h5>
                    <p class="text-muted">No books reported as damaged.</p>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

