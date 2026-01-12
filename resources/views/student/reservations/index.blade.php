@extends('layouts.student')

@section('title', 'My Reservations')
@section('page-title', 'My Reservations')

@section('content')
<div class="card-modern">
    <div class="card-header-modern">
        <h5>
            <i class="fas fa-clock text-warning"></i>
            My Book Reservations
        </h5>
    </div>
    <div class="card-body p-4">
        @if($reservations->isEmpty())
        <div class="text-center py-5">
            <i class="fas fa-inbox fa-4x text-muted mb-3"></i>
            <h5 class="text-muted">No Reservations</h5>
            <p class="text-muted">You don't have any active book reservations.</p>
        </div>
        @else
        <div class="row">
            <div class="col-12">
                <div class="table-responsive">
                    <table id="reservationsTable" class="table table-modern data-table mb-0 w-100">
                        <thead>
                            <tr>
                                <th style="width: 5%;">#</th>
                                <th style="width: 30%;">Book</th>
                                <th style="width: 15%;">Status</th>
                                <th style="width: 15%;">Reserved Date</th>
                                <th style="width: 15%;">Expires At</th>
                                <th style="width: 20%;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($reservations as $index => $reservation)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>
                                    <strong>{{ $reservation->book->title }}</strong>
                                    <br>
                                    <small class="text-muted">ISBN: {{ $reservation->book->isbn }}</small>
                                </td>
                                <td>
                                    @if($reservation->status === 'pending')
                                        <span class="badge bg-warning">Pending</span>
                                    @elseif($reservation->status === 'available')
                                        <span class="badge bg-success">Available</span>
                                    @else
                                        <span class="badge bg-secondary">{{ ucfirst($reservation->status) }}</span>
                                    @endif
                                </td>
                                <td>{{ $reservation->reserved_at->format('M d, Y') }}</td>
                                <td>
                                    @if($reservation->expires_at)
                                        {{ $reservation->expires_at->format('M d, Y') }}
                                        @if($reservation->expires_at->isPast())
                                            <br><small class="text-danger">Expired</small>
                                        @endif
                                    @else
                                        N/A
                                    @endif
                                </td>
                                <td>
                                    @if($reservation->status === 'available')
                                        <a href="{{ route('student.books.show', $reservation->book) }}" class="btn btn-sm btn-success">
                                            <i class="fas fa-book me-1"></i>Collect Now
                                        </a>
                                    @endif
                                    @if($reservation->status !== 'issued')
                                    <form action="{{ route('student.reservations.cancel', $reservation) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to cancel this reservation?')">
                                            <i class="fas fa-times me-1"></i>Cancel
                                        </button>
                                    </form>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection

