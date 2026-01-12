@extends('layouts.admin')

@section('title', 'System Settings')
@section('page-title', 'System Settings')

@section('content')
<div class="card-modern">
    <div class="card-header-modern">
        <h5>
            <i class="fas fa-cog text-primary"></i>
            System Settings
        </h5>
    </div>
    <div class="card-body p-4">
        <!-- Tabs Navigation -->
        <ul class="nav nav-tabs mb-4" id="settingsTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="fine-rules-tab" data-bs-toggle="tab" data-bs-target="#fine-rules" type="button" role="tab">
                    <i class="fas fa-money-bill-wave me-2"></i>Fine Rules
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="opening-hours-tab" data-bs-toggle="tab" data-bs-target="#opening-hours" type="button" role="tab">
                    <i class="fas fa-clock me-2"></i>Opening Hours
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="holidays-tab" data-bs-toggle="tab" data-bs-target="#holidays" type="button" role="tab">
                    <i class="fas fa-calendar-alt me-2"></i>Holidays
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <a class="nav-link" href="{{ route('admin.settings.member-types') }}">
                    <i class="fas fa-users-cog me-2"></i>Member Types
                </a>
            </li>
        </ul>

        <!-- Tabs Content -->
        <div class="tab-content" id="settingsTabsContent">
            <!-- Fine Rules Tab -->
            <div class="tab-pane fade show active" id="fine-rules" role="tabpanel">
                <form method="POST" action="{{ route('admin.settings.fine-rules.update') }}" id="fineRulesForm">
                    @csrf
                    <div class="row mb-3">
                        <div class="col-12">
                            <h6 class="fw-bold mb-3">Fine Rules Configuration</h6>
                            <p class="text-muted">Configure fine rates based on issue duration. Fine increases with longer issue periods.</p>
                        </div>
                    </div>
                    <div id="fineRulesContainer">
                        @php
                            $index = 0;
                        @endphp
                        @foreach($fineMapping as $duration => $finePerDay)
                            <div class="row mb-3 fine-rule-row" data-index="{{ $index }}">
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">Issue Duration (Days) <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" name="fine_mapping[{{ $index }}][duration]" 
                                        value="{{ $duration }}" min="1" max="365" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">Fine Per Day (₹) <span class="text-danger">*</span></label>
                                    <input type="number" step="0.01" class="form-control" name="fine_mapping[{{ $index }}][fine_per_day]" 
                                        value="{{ $finePerDay }}" min="0" required>
                                </div>
                                <div class="col-md-4 d-flex align-items-end">
                                    @if($index > 0)
                                        <button type="button" class="btn btn-danger btn-sm remove-rule">
                                            <i class="fas fa-trash me-1"></i>Remove
                                        </button>
                                    @endif
                                </div>
                            </div>
                            @php $index++; @endphp
                        @endforeach
                    </div>
                    <div class="row mb-3">
                        <div class="col-12">
                            <button type="button" class="btn btn-secondary btn-sm" id="addFineRule">
                                <i class="fas fa-plus me-1"></i>Add Rule
                            </button>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Save Fine Rules
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Opening Hours Tab -->
            <div class="tab-pane fade" id="opening-hours" role="tabpanel">
                <form method="POST" action="{{ route('admin.settings.opening-hours.update') }}">
                    @csrf
                    <div class="row mb-3">
                        <div class="col-12">
                            <h6 class="fw-bold mb-3">Library Opening Hours</h6>
                            <p class="text-muted">Set opening and closing times for each day of the week.</p>
                        </div>
                    </div>
                    @php
                        $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
                    @endphp
                    @foreach($days as $day)
                        @php
                            $openingHour = $openingHours->firstWhere('day_of_week', $day);
                        @endphp
                        <div class="row mb-3">
                            <div class="col-md-2">
                                <label class="form-label fw-semibold">{{ ucfirst($day) }}</label>
                            </div>
                            <div class="col-md-3">
                                <input type="time" class="form-control" 
                                    name="opening_hours[{{ $day }}][opening_time]" 
                                    value="{{ $openingHour->opening_time ? \Carbon\Carbon::parse($openingHour->opening_time)->format('H:i') : '' }}">
                                <input type="hidden" name="opening_hours[{{ $day }}][day_of_week]" value="{{ $day }}">
                            </div>
                            <div class="col-md-3">
                                <input type="time" class="form-control" 
                                    name="opening_hours[{{ $day }}][closing_time]" 
                                    value="{{ $openingHour->closing_time ? \Carbon\Carbon::parse($openingHour->closing_time)->format('H:i') : '' }}">
                            </div>
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" 
                                        name="opening_hours[{{ $day }}][is_closed]" value="1"
                                        {{ $openingHour && $openingHour->is_closed ? 'checked' : '' }}>
                                    <label class="form-check-label">Closed</label>
                                </div>
                            </div>
                        </div>
                    @endforeach
                    <div class="row">
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Save Opening Hours
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Holidays Tab -->
            <div class="tab-pane fade" id="holidays" role="tabpanel">
                <div class="row mb-3">
                    <div class="col-12">
                        <h6 class="fw-bold mb-3">Library Holidays</h6>
                        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addHolidayModal">
                            <i class="fas fa-plus me-1"></i>Add Holiday
                        </button>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-modern">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Date</th>
                                <th>Recurring</th>
                                <th>Description</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($holidays as $holiday)
                                <tr>
                                    <td>{{ $holiday->name }}</td>
                                    <td>{{ $holiday->date->format('M d, Y') }}</td>
                                    <td>
                                        @if($holiday->is_recurring)
                                            <span class="badge bg-success">Yes</span>
                                        @else
                                            <span class="badge bg-secondary">No</span>
                                        @endif
                                    </td>
                                    <td>{{ $holiday->description ?? '-' }}</td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-warning edit-holiday" 
                                            data-id="{{ $holiday->id }}"
                                            data-name="{{ $holiday->name }}"
                                            data-date="{{ $holiday->date->format('Y-m-d') }}"
                                            data-recurring="{{ $holiday->is_recurring }}"
                                            data-description="{{ $holiday->description }}">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <form action="{{ route('admin.settings.holidays.delete', $holiday) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted">No holidays added yet</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Holiday Modal -->
<div class="modal fade" id="addHolidayModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.settings.holidays.store') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Add Holiday</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Date <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" name="date" required>
                    </div>
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="is_recurring" value="1" id="isRecurring">
                            <label class="form-check-label" for="isRecurring">Recurring (Annual)</label>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="description" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Holiday</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Holiday Modal -->
<div class="modal fade" id="editHolidayModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" id="editHolidayForm">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title">Edit Holiday</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="name" id="editHolidayName" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Date <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" name="date" id="editHolidayDate" required>
                    </div>
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="is_recurring" value="1" id="editIsRecurring">
                            <label class="form-check-label" for="editIsRecurring">Recurring (Annual)</label>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="description" id="editHolidayDescription" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Holiday</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Fine Rules Management
    let fineRuleIndex = {{ count($fineMapping) }};
    document.getElementById('addFineRule')?.addEventListener('click', function() {
        const container = document.getElementById('fineRulesContainer');
        const newRow = document.createElement('div');
        newRow.className = 'row mb-3 fine-rule-row';
        newRow.setAttribute('data-index', fineRuleIndex);
        newRow.innerHTML = `
            <div class="col-md-4">
                <label class="form-label fw-semibold">Issue Duration (Days) <span class="text-danger">*</span></label>
                <input type="number" class="form-control" name="fine_mapping[${fineRuleIndex}][duration]" min="1" max="365" required>
            </div>
            <div class="col-md-4">
                <label class="form-label fw-semibold">Fine Per Day (₹) <span class="text-danger">*</span></label>
                <input type="number" step="0.01" class="form-control" name="fine_mapping[${fineRuleIndex}][fine_per_day]" min="0" required>
            </div>
            <div class="col-md-4 d-flex align-items-end">
                <button type="button" class="btn btn-danger btn-sm remove-rule">
                    <i class="fas fa-trash me-1"></i>Remove
                </button>
            </div>
        `;
        container.appendChild(newRow);
        fineRuleIndex++;
    });

    document.addEventListener('click', function(e) {
        if (e.target.closest('.remove-rule')) {
            e.target.closest('.fine-rule-row').remove();
        }
    });

    // Edit Holiday
    document.querySelectorAll('.edit-holiday').forEach(btn => {
        btn.addEventListener('click', function() {
            const form = document.getElementById('editHolidayForm');
            form.action = '{{ route("admin.settings.holidays.update", ":id") }}'.replace(':id', this.dataset.id);
            document.getElementById('editHolidayName').value = this.dataset.name;
            document.getElementById('editHolidayDate').value = this.dataset.date;
            document.getElementById('editIsRecurring').checked = this.dataset.recurring === '1';
            document.getElementById('editHolidayDescription').value = this.dataset.description || '';
            new bootstrap.Modal(document.getElementById('editHolidayModal')).show();
        });
    });
</script>
@endpush
@endsection


