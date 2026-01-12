@extends('layouts.staff')

@section('title', 'Student Issue History')
@section('page-title', 'Issue History - ' . $user->name)

@section('content')
<div class="card-modern mb-4">
    <div class="card-body p-4">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h5 class="fw-bold mb-1">{{ $user->name }}</h5>
                <p class="text-muted small mb-0">{{ $user->email }} | Student ID: {{ $user->student_id ?? 'N/A' }}</p>
            </div>
            <a href="{{ route('staff.students.show', $user) }}" class="btn btn-sm btn-outline-primary">
                <i class="fas fa-arrow-left me-1"></i>Back to Student
            </a>
        </div>
    </div>
</div>

<div class="card-modern">
    <div class="card-header-modern">
        <h5>
            <i class="fas fa-history text-primary"></i>
            Complete Issue History
        </h5>
    </div>
    <div class="card-body p-4">
        <div class="row mb-3">
            <div class="col-12">
                <p class="text-muted mb-0">Total Records: <strong>{{ $allBorrows->total() }}</strong></p>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <div class="table-responsive">
                    <table id="historyTable" class="table table-modern data-table mb-0 w-100">
                        <thead>
                            <tr>
                                <th style="width: 20%;">Book</th>
                                <th style="width: 12%;">ISBN</th>
                                <th style="width: 12%;">Issue Date</th>
                                <th style="width: 12%;">Due Date</th>
                                <th style="width: 12%;">Return Date</th>
                                <th style="width: 12%;">Fine</th>
                                <th style="width: 10%;">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($allBorrows as $borrow)
                            <tr class="{{ $borrow->status === 'borrowed' && $borrow->due_date < now() ? 'table-danger' : '' }}">
                                <td class="fw-semibold">{{ $borrow->book->title }}</td>
                                <td>{{ $borrow->book->isbn }}</td>
                                <td>{{ $borrow->borrow_date->format('M d, Y') }}</td>
                                <td class="{{ $borrow->status === 'borrowed' && $borrow->due_date < now() ? 'text-danger fw-bold' : '' }}">
                                    {{ $borrow->due_date->format('M d, Y') }}
                                </td>
                                <td>
                                    {{ $borrow->return_date ? $borrow->return_date->format('M d, Y') : 'N/A' }}
                                </td>
                                <td>
                                    @if($borrow->fine)
                                        <span class="text-danger fw-bold">₹{{ number_format($borrow->fine->amount, 2) }}</span>
                                        <div class="text-xs text-muted">{{ ucfirst($borrow->fine->status) }}</div>
                                    @else
                                        <span class="text-muted">₹0.00</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-{{ $borrow->status === 'borrowed' ? 'warning' : 'success' }}">
                                        {{ ucfirst($borrow->status) }}
                                    </span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center py-5">
                                    <div class="text-muted">
                                        <i class="fas fa-inbox fa-3x d-block mb-3"></i>
                                        <p class="mb-0">No issue history found</p>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @if($allBorrows->hasPages())
        <div class="row mt-4">
            <div class="col-12">
                <div class="d-flex justify-content-center">
                    {{ $allBorrows->links() }}
                </div>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
