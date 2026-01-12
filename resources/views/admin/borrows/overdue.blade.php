@extends('layouts.admin')

@section('title', 'Overdue Books')
@section('page-title', 'Overdue Books')

@section('content')
<div class="card-modern">
    <div class="card-header-modern">
        <h5>
            <i class="fas fa-exclamation-triangle text-danger"></i>
            Overdue Books
        </h5>
        <a href="{{ route('admin.borrows.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Back to All Borrows
        </a>
    </div>
    <div class="card-body p-4">
        @if($totalOverdue > 0)
        <div class="alert alert-danger mb-4">
            <div class="d-flex align-items-center">
                <i class="fas fa-exclamation-triangle fa-2x me-3"></i>
                <div>
                    <h6 class="mb-1 fw-bold">Action Required</h6>
                    <p class="mb-0">Total Overdue: <strong>{{ $totalOverdue }}</strong> book(s) past their due date</p>
                </div>
            </div>
        </div>
        @endif

        <div class="row">
            <div class="col-12">
                <div class="table-responsive">
                    <table id="overdueTable" class="table table-modern data-table mb-0 w-100">
                        <thead>
                            <tr>
                                <th style="width: 5%;">#</th>
                                <th style="width: 18%;">User</th>
                                <th style="width: 20%;">Book</th>
                                <th style="width: 12%;">Issue Date</th>
                                <th style="width: 12%;">Due Date</th>
                                <th style="width: 12%;">Days Overdue</th>
                                <th style="width: 12%;">Estimated Fine</th>
                                <th style="width: 9%;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($overdueBorrows as $index => $borrow)
                            @php
                                // Use Borrow model's calculated attributes for consistency
                                $daysOverdue = $borrow->days_overdue;
                                $estimatedFine = $borrow->current_fine_amount;
                            @endphp
                            <tr class="table-danger">
                                <td>{{ $index + 1 }}</td>
                                <td>
                                    <div class="fw-semibold">{{ $borrow->user->name }}</div>
                                    <small class="text-muted">{{ $borrow->user->email }}</small>
                                    @if($borrow->user->student_id)
                                    <div class="text-xs text-muted">ID: {{ $borrow->user->student_id }}</div>
                                    @elseif($borrow->user->staff_id)
                                    <div class="text-xs text-muted">Staff ID: {{ $borrow->user->staff_id }}</div>
                                    @endif
                                </td>
                                <td>
                                    <div class="fw-semibold">{{ $borrow->book->title }}</div>
                                    <small class="text-muted">ISBN: {{ $borrow->book->isbn }}</small>
                                </td>
                                <td>{{ $borrow->borrow_date->format('M d, Y') }}</td>
                                <td class="text-danger fw-bold">{{ $borrow->due_date->format('M d, Y') }}</td>
                                <td class="text-danger fw-bold">{{ $daysOverdue }} day(s)</td>
                                <td class="text-danger fw-bold">₹{{ number_format($estimatedFine, 2) }}</td>
                                <td>
                                    <a href="{{ route('admin.borrows.return.show', $borrow) }}" class="btn btn-sm btn-success">
                                        <i class="fas fa-undo me-1"></i>Return
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="text-center py-5">
                                    <div class="text-muted">
                                        <i class="fas fa-check-circle fa-4x d-block mb-3 text-success"></i>
                                        <h5 class="text-success mb-2">No Overdue Books</h5>
                                        <p class="mb-0">All books are returned on time!</p>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

