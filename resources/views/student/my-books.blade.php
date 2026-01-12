@extends('layouts.student')

@section('title', 'My Books')
@section('page-title', 'My Books')

@section('content')
<div class="card-modern">
    <div class="card-header-modern">
        <h5>
            <i class="fas fa-book-open text-primary"></i>
            Currently Issued Books
        </h5>
    </div>
    <div class="card-body p-4">
        <div class="row">
            <div class="col-12">
                <div class="table-responsive">
                    <table id="myBooksTable" class="table table-modern data-table mb-0 w-100">
                        <thead>
                            <tr>
                                <th style="width: 4%;">#</th>
                                <th style="width: 18%;">Book</th>
                                <th style="width: 10%;">ISBN</th>
                                <th style="width: 10%;">Author</th>
                                <th style="width: 10%;">Issue Date</th>
                                <th style="width: 10%;">Due Date</th>
                                <th style="width: 12%;">Days Left/Overdue</th>
                                <th style="width: 12%;">Fine</th>
                                <th style="width: 14%;">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($borrows as $index => $borrow)
                            <tr class="{{ $borrow->isOverdue() ? 'table-danger' : ($borrow->isDueToday() ? 'table-warning' : '') }}">
                                <td>{{ $index + 1 }}</td>
                                <td class="fw-semibold">{{ $borrow->book->title }}</td>
                                <td>{{ $borrow->book->isbn }}</td>
                                <td>{{ $borrow->book->author->name }}</td>
                                <td>{{ $borrow->borrow_date->format('M d, Y') }}</td>
                                <td class="{{ $borrow->isOverdue() ? 'text-danger fw-bold' : ($borrow->isDueToday() ? 'text-warning fw-bold' : '') }}">
                                    {{ $borrow->due_date->format('M d, Y') }}
                                </td>
                                <td>
                                    @if($borrow->isOverdue())
                                        <span class="text-danger fw-bold">
                                            <i class="fas fa-exclamation-triangle me-1"></i>
                                            {{ abs($borrow->days_overdue) }} day(s) overdue
                                        </span>
                                    @elseif($borrow->isDueToday())
                                        <span class="text-warning fw-bold">
                                            <i class="fas fa-clock me-1"></i>
                                            Due today
                                        </span>
                                    @else
                                        <span class="text-success">
                                            <i class="fas fa-calendar-check me-1"></i>
                                            {{ $borrow->days_left }} day(s) left
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    @if($borrow->isOverdue())
                                        @php
                                            // Refresh borrow to get latest data
                                            $borrow->refresh();
                                            
                                            // Use the model's calculated values
                                            $daysOverdue = $borrow->days_overdue;
                                            $pendingFineAmount = $borrow->current_fine_amount; // Only unpaid days
                                            $totalFineAmount = $borrow->total_fine_amount; // Total including paid
                                            $totalPaid = $borrow->total_fine_paid ?? 0;
                                            $finePerDay = $borrow->fine_per_day ?? 10;
                                            $pendingDays = $borrow->pending_fine_days;
                                            $lastPaidDate = $borrow->last_fine_paid_date;
                                            
                                            // Calculate fine start date for display
                                            if ($lastPaidDate) {
                                                $fineStartDate = \Carbon\Carbon::parse($lastPaidDate)->addDay();
                                            } else {
                                                $fineStartDate = \Carbon\Carbon::parse($borrow->due_date)->addDay();
                                            }
                                            
                                            // Check if fine record exists
                                            $fine = $borrow->fine;
                                            
                                            // If fine exists but book is still overdue, update it
                                            if ($fine && $borrow->isOverdue()) {
                                                // Update fine amount to reflect current pending fine
                                                $fine->amount = $pendingFineAmount;
                                                $fine->remaining_amount = $pendingFineAmount;
                                                $fine->reason = 'Overdue book - ' . $pendingDays . ' day(s) pending from ' . 
                                                    ($lastPaidDate ? $lastPaidDate->format('Y-m-d') : $borrow->due_date->format('Y-m-d'));
                                                if ($pendingFineAmount <= 0) {
                                                    $fine->status = 'paid';
                                                } else {
                                                    $fine->status = 'pending';
                                                }
                                                $fine->save();
                                            }
                                            
                                            // If fine doesn't exist but book is overdue, create it
                                            if (!$fine && $pendingFineAmount > 0) {
                                                try {
                                                    $fine = \App\Models\Fine::create([
                                                        'borrow_id' => $borrow->id,
                                                        'user_id' => $borrow->user_id,
                                                        'amount' => $pendingFineAmount,
                                                        'remaining_amount' => $pendingFineAmount,
                                                        'reason' => 'Overdue book - ' . $pendingDays . ' day(s) pending from ' . 
                                                            ($lastPaidDate ? $lastPaidDate->format('Y-m-d') : $borrow->due_date->format('Y-m-d')),
                                                        'status' => 'pending',
                                                        'days_overdue_at_creation' => $daysOverdue,
                                                    ]);
                                                    // Reload the relationship
                                                    $borrow->load('fine');
                                                    $fine = $borrow->fine;
                                                } catch (\Exception $e) {
                                                    // Fine might already exist, try to load it
                                                    $fine = \App\Models\Fine::where('borrow_id', $borrow->id)->first();
                                                }
                                            }
                                            
                                            // Get remaining fine
                                            $remainingFine = $pendingFineAmount;
                                        @endphp
                                        <div>
                                            @if($totalPaid > 0)
                                                <div class="text-success small mb-1">
                                                    <i class="fas fa-check-circle me-1"></i>
                                                    <strong>Paid: ₹{{ number_format($totalPaid, 2) }}</strong>
                                                    @if($lastPaidDate)
                                                        <br><span class="text-muted">(Paid till: {{ $lastPaidDate->format('M d, Y') }})</span>
                                                    @endif
                                                </div>
                                            @endif
                                            @if($remainingFine > 0)
                                                <div class="text-danger fw-bold mb-1">
                                                    <i class="fas fa-exclamation-triangle me-1"></i>
                                                    <strong>Pending: ₹{{ number_format($remainingFine, 2) }}</strong>
                                                    @if($pendingDays > 0)
                                                        <br><span class="text-muted small">({{ $pendingDays }} day(s) from {{ $fineStartDate->format('M d, Y') }})</span>
                                                    @endif
                                                </div>
                                            @endif
                                            <div class="text-info small">
                                                <i class="fas fa-info-circle me-1"></i>
                                                Rate: ₹{{ number_format($finePerDay, 2) }}/day
                                            </div>
                                        </div>
                                        @if($remainingFine > 0)
                                            @if($fine && $fine->status === 'paid')
                                                <div class="mt-2">
                                                    <span class="badge bg-success">Paid</span>
                                                </div>
                                            @else
                                                <div class="mt-2">
                                                    <a href="{{ route('student.fines.index') }}" class="btn btn-sm btn-primary">
                                                        <i class="fas fa-qrcode me-1"></i>Pay ₹{{ number_format($remainingFine, 2) }}
                                                    </a>
                                                </div>
                                            @endif
                                        @elseif($fine && $fine->status === 'paid')
                                            <div class="mt-2">
                                                <span class="badge bg-success">Paid</span>
                                            </div>
                                        @endif
                                    @else
                                        <span class="text-success">₹0.00</span>
                                    @endif
                                </td>
                                <td>
                                    @if($borrow->status === 'borrowed')
                                        @if($borrow->isOverdue())
                                            <span class="badge bg-danger">Overdue</span>
                                        @elseif($borrow->isDueToday())
                                            <span class="badge bg-warning text-dark">Due Today</span>
                                        @else
                                            <span class="badge bg-warning text-dark">Borrowed</span>
                                        @endif
                                    @else
                                        <span class="badge bg-success">{{ ucfirst($borrow->status) }}</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="text-center py-5">
                                    <div class="text-muted">
                                        <i class="fas fa-inbox fa-3x d-block mb-3"></i>
                                        <p class="mb-2">No Books Issued</p>
                                        <p class="text-muted mb-3">You don't have any books issued at the moment</p>
                                        <a href="{{ route('student.books.index') }}" class="btn btn-primary">
                                            Browse Available Books <i class="fas fa-arrow-right ms-2"></i>
                                        </a>
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
