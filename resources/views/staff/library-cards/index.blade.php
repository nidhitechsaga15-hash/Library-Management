@extends('layouts.staff')

@section('title', 'Library Cards Management')
@section('page-title', 'Library Cards Management')

@section('content')
<div class="card-modern">
    <div class="card-header-modern">
        <h5>
            <i class="fas fa-id-card text-primary"></i>
            Library Cards
        </h5>
        <a href="{{ route('staff.library-cards.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Issue New Card
        </a>
    </div>
    <div class="card-body p-4">
        <!-- Statistics -->
        <div class="row g-3 mb-4">
            <div class="col-12 col-sm-6 col-md-3">
                <div class="stat-card border-start border-primary border-4">
                    <div class="stat-content">
                        <div class="stat-label">Total Cards</div>
                        <div class="stat-value text-primary">{{ $stats['total'] }}</div>
                    </div>
                    <div class="stat-icon primary">
                        <i class="fas fa-id-card"></i>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-md-3">
                <div class="stat-card border-start border-success border-4">
                    <div class="stat-content">
                        <div class="stat-label">Active Cards</div>
                        <div class="stat-value text-success">{{ $stats['active'] }}</div>
                    </div>
                    <div class="stat-icon success">
                        <i class="fas fa-check-circle"></i>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-md-3">
                <div class="stat-card border-start border-warning border-4">
                    <div class="stat-content">
                        <div class="stat-label">Expired Cards</div>
                        <div class="stat-value text-warning">{{ $stats['expired'] }}</div>
                    </div>
                    <div class="stat-icon warning">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-md-3">
                <div class="stat-card border-start border-danger border-4">
                    <div class="stat-content">
                        <div class="stat-label">Blocked/Lost</div>
                        <div class="stat-value text-danger">{{ $stats['blocked'] }}</div>
                    </div>
                    <div class="stat-icon danger">
                        <i class="fas fa-ban"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Cards Table -->
        <div class="row">
            <div class="col-12">
                <div class="table-responsive">
                    <table id="libraryCardsTable" class="table table-modern data-table mb-0 w-100">
                        <thead>
                            <tr>
                                <th style="width: 5%;">#</th>
                                <th style="width: 12%;">Card Number</th>
                                <th style="width: 18%;">Student Name</th>
                                <th style="width: 12%;">Student ID</th>
                                <th style="width: 12%;">Issue Date</th>
                                <th style="width: 12%;">Validity Date</th>
                                <th style="width: 10%;">Status</th>
                                <th style="width: 19%;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($cards as $index => $card)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td><strong>{{ $card->card_number }}</strong></td>
                                <td>{{ $card->user->name }}</td>
                                <td>{{ $card->user->student_id ?? 'N/A' }}</td>
                                <td>{{ $card->issue_date->format('M d, Y') }}</td>
                                <td>
                                    <span class="{{ $card->validity_date < now() ? 'text-danger' : '' }}">
                                        {{ $card->validity_date->format('M d, Y') }}
                                    </span>
                                </td>
                                <td>
                                    @if($card->status === 'active' && $card->validity_date >= now())
                                        <span class="badge bg-success">Active</span>
                                    @elseif($card->status === 'active' && $card->validity_date < now())
                                        <span class="badge bg-warning">Expired</span>
                                    @elseif($card->status === 'blocked')
                                        <span class="badge bg-danger">Blocked</span>
                                    @elseif($card->status === 'lost')
                                        <span class="badge bg-dark">Lost</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('staff.library-cards.show', $card) }}" class="btn btn-sm btn-info" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('staff.library-cards.print', $card) }}" class="btn btn-sm btn-secondary" title="Print" target="_blank">
                                            <i class="fas fa-print"></i>
                                        </a>
                                        @if(!$card->isBlocked())
                                            <form action="{{ route('staff.library-cards.block', $card) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-danger" title="Block" onclick="return confirm('Are you sure you want to block this card?')">
                                                    <i class="fas fa-ban"></i>
                                                </button>
                                            </form>
                                        @else
                                            <form action="{{ route('staff.library-cards.unblock', $card) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-success" title="Unblock" onclick="return confirm('Are you sure you want to unblock this card?')">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
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

