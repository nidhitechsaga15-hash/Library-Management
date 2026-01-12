@extends('layouts.staff')

@section('title', 'Manage Borrows')
@section('page-title', 'Manage Borrows')

@section('content')
<div class="card-modern">
    <div class="card-header-modern">
        <h5>
            <i class="fas fa-exchange-alt text-primary"></i>
            Borrows Section
        </h5>
        <div class="d-flex gap-2">
            <a href="{{ route('staff.borrows.overdue') }}" class="btn btn-danger">
                <i class="fas fa-exclamation-triangle me-2"></i>Overdue Books
            </a>
            <a href="{{ route('staff.borrows.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i>Issue New Book
            </a>
        </div>
    </div>
    <div class="card-body p-4">
        <div class="row">
            <div class="col-12">
                <div class="table-responsive">
                    <table id="borrowsTable" class="table table-modern data-table mb-0 w-100">
                <thead>
                    <tr>
                        <th style="width: 4%;">#</th>
                        <th style="width: 12%;">User</th>
                        <th style="width: 15%;">Book</th>
                        <th style="width: 10%;">Borrow Date</th>
                        <th style="width: 10%;">Due Date</th>
                        <th style="width: 10%;">Return Date</th>
                        <th style="width: 12%;">Fine Amount</th>
                        <th style="width: 10%;">Status</th>
                        <th style="width: 17%;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($borrows as $index => $borrow)
                    <tr class="{{ $borrow->status === 'borrowed' && $borrow->due_date < now() ? 'table-danger' : '' }}">
                        <td>{{ $index + 1 }}</td>
                        <td>
                            <div class="fw-semibold">{{ $borrow->user->name }}</div>
                            <small class="text-muted">{{ $borrow->user->email }}</small>
                        </td>
                        <td>
                            <div class="fw-semibold">{{ $borrow->book->title }}</div>
                            <small class="text-muted">{{ $borrow->book->isbn }}</small>
                        </td>
                        <td>{{ $borrow->borrow_date->format('M d, Y') }}</td>
                        <td class="{{ $borrow->status === 'borrowed' && $borrow->due_date < now() ? 'text-danger fw-bold' : '' }}">
                            {{ $borrow->due_date->format('M d, Y') }}
                            @if($borrow->status === 'borrowed' && $borrow->due_date < now())
                            <div class="text-xs text-danger">Overdue</div>
                            @endif
                        </td>
                        <td>
                            @if($borrow->return_date)
                                {{ $borrow->return_date->format('M d, Y') }}
                            @else
                                <span class="text-muted">N/A</span>
                            @endif
                        </td>
                        <td>
                            @if($borrow->fine)
                                <span class="text-danger fw-bold">₹{{ number_format($borrow->fine->amount, 2) }}</span>
                                <div class="text-xs">
                                    <span class="badge bg-{{ $borrow->fine->status === 'paid' ? 'success' : 'warning' }}">
                                        {{ ucfirst($borrow->fine->status) }}
                                    </span>
                                </div>
                            @elseif($borrow->status === 'borrowed' && $borrow->due_date < now())
                                @php
                                    // Use Borrow model's calculated attributes for consistency
                                    $daysOverdue = $borrow->days_overdue;
                                    $estimatedFine = $borrow->current_fine_amount;
                                    $finePerDay = $borrow->fine_per_day ?? \App\Helpers\FineHelper::getFinePerDayByDuration($borrow->issue_duration_days ?? 15);
                                @endphp
                                <span class="text-warning fw-bold">₹{{ number_format($estimatedFine, 2) }}</span>
                                <div class="text-xs text-warning">Estimated ({{ $daysOverdue }} days × ₹{{ number_format($finePerDay, 2) }})</div>
                            @else
                                <span class="text-muted">₹0.00</span>
                            @endif
                        </td>
                        <td>
                            @if($borrow->status === 'borrowed')
                                <span class="badge bg-warning text-dark">Borrowed</span>
                            @elseif($borrow->status === 'returned')
                                <span class="badge bg-success">Returned</span>
                            @else
                                <span class="badge bg-danger">{{ ucfirst($borrow->status) }}</span>
                            @endif
                        </td>
                        <td>
                            @if($borrow->status === 'borrowed')
                                <div class="btn-group" role="group">
                                    <a href="{{ route('staff.borrows.return.show', $borrow) }}" class="btn btn-sm btn-success">
                                        <i class="fas fa-undo me-1"></i>Return
                                    </a>
                                    <button type="button" class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#extendModal{{ $borrow->id }}">
                                        <i class="fas fa-calendar-plus me-1"></i>Extend
                                    </button>
                                </div>
                                
                                <!-- Extend Modal -->
                                <div class="modal fade" id="extendModal{{ $borrow->id }}" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <form action="{{ route('staff.borrows.extend', $borrow) }}" method="POST">
                                                @csrf
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Extend Due Date</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <p><strong>Book:</strong> {{ $borrow->book->title }}</p>
                                                    <p><strong>Student:</strong> {{ $borrow->user->name }}</p>
                                                    <p><strong>Current Due Date:</strong> {{ $borrow->due_date->format('M d, Y') }}</p>
                                                    <div class="mb-3">
                                                        <label for="additional_days{{ $borrow->id }}" class="form-label">Additional Days (1-30)</label>
                                                        <input type="number" name="additional_days" id="additional_days{{ $borrow->id }}" 
                                                            class="form-control" min="1" max="30" value="5" required>
                                                        <small class="text-muted">New due date will be: <span id="newDueDate{{ $borrow->id }}"></span></small>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                    <button type="submit" class="btn btn-primary">Extend</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                
                                <script>
                                document.addEventListener('DOMContentLoaded', function() {
                                    const input = document.getElementById('additional_days{{ $borrow->id }}');
                                    const preview = document.getElementById('newDueDate{{ $borrow->id }}');
                                    const currentDueDate = new Date('{{ $borrow->due_date->format('Y-m-d') }}');
                                    
                                    function updatePreview() {
                                        const days = parseInt(input.value) || 0;
                                        const newDate = new Date(currentDueDate);
                                        newDate.setDate(newDate.getDate() + days);
                                        preview.textContent = newDate.toLocaleDateString('en-US', { 
                                            year: 'numeric', 
                                            month: 'short', 
                                            day: 'numeric' 
                                        });
                                    }
                                    
                                    input.addEventListener('input', updatePreview);
                                    updatePreview();
                                });
                                </script>
                            @else
                                <span class="text-muted">Returned</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
