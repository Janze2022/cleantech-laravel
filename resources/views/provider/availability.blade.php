@extends('provider.layouts.app')

@section('title', 'My Availability')

@section('content')

@php
    use Carbon\Carbon;

    $selectedDate = request('date', '');
    $searchQuery = trim((string) request('q', ''));
    $todayDate = now()->toDateString();
    $now = now();

    $hours = range(1, 12);
    $minutes = range(0, 59);
    $ampmOptions = ['AM', 'PM'];

    $parseOldTimeParts = function ($time) {
        if (!$time) {
            return ['hour' => '', 'minute' => '', 'ampm' => ''];
        }

        try {
            $normalized = strlen($time) === 5 ? $time . ':00' : $time;
            $carbon = Carbon::createFromFormat('H:i:s', $normalized);
        } catch (\Throwable $e) {
            try {
                $carbon = Carbon::createFromFormat('H:i', $time);
            } catch (\Throwable $e) {
                return ['hour' => '', 'minute' => '', 'ampm' => ''];
            }
        }

        return [
            'hour'   => $carbon->format('g'),
            'minute' => $carbon->format('i'),
            'ampm'   => $carbon->format('A'),
        ];
    };

    $formatTimeLabel = function ($time) {
        if (!$time) {
            return '—';
        }

        try {
            $normalized = strlen($time) === 5 ? $time . ':00' : $time;
            return Carbon::createFromFormat('H:i:s', $normalized)->format('h:i A');
        } catch (\Throwable $e) {
            try {
                return Carbon::createFromFormat('H:i', $time)->format('h:i A');
            } catch (\Throwable $e) {
                return (string) $time;
            }
        }
    };

    $oldStart = $parseOldTimeParts(old('time_start'));
    $oldEnd   = $parseOldTimeParts(old('time_end'));

    $filteredAvailability = collect($availability ?? [])
        ->map(function ($slot) use ($now, $formatTimeLabel) {
            $slotDate = null;
            $startDateTime = null;
            $endDateTime = null;

            try {
                if (!empty($slot->date)) {
                    $slotDate = Carbon::parse($slot->date);
                }
            } catch (\Throwable $e) {
                $slotDate = null;
            }

            try {
                if ($slotDate && !empty($slot->time_start)) {
                    $startDateTime = Carbon::parse($slotDate->toDateString() . ' ' . $slot->time_start);
                }
            } catch (\Throwable $e) {
                $startDateTime = null;
            }

            try {
                if ($slotDate && !empty($slot->time_end)) {
                    $endDateTime = Carbon::parse($slotDate->toDateString() . ' ' . $slot->time_end);
                }
            } catch (\Throwable $e) {
                $endDateTime = null;
            }

            $isExpired = $endDateTime ? $endDateTime->lt($now) : false;
            $effectiveStatus = $isExpired ? 'inactive' : ((string) ($slot->status ?? 'inactive'));
            $dateLabel = $slotDate ? $slotDate->format('F d, Y') : '—';
            $startLabel = $formatTimeLabel($slot->time_start ?? null);
            $endLabel = $formatTimeLabel($slot->time_end ?? null);
            $availabilityLabel = ($startLabel !== '—' && $endLabel !== '—') ? ($startLabel . ' – ' . $endLabel) : '—';

            $searchBlob = strtolower(
                trim(
                    $dateLabel . ' ' .
                    ($slotDate ? $slotDate->format('Y-m-d') : '') . ' ' .
                    $startLabel . ' ' .
                    $endLabel . ' ' .
                    $availabilityLabel . ' ' .
                    $effectiveStatus
                )
            );

            $slot->slot_date_obj = $slotDate;
            $slot->start_datetime_obj = $startDateTime;
            $slot->end_datetime_obj = $endDateTime;
            $slot->is_expired = $isExpired;
            $slot->effective_status = $effectiveStatus;
            $slot->date_label = $dateLabel;
            $slot->availability_label = $availabilityLabel;
            $slot->search_blob = $searchBlob;

            return $slot;
        });

    if ($selectedDate) {
        $filteredAvailability = $filteredAvailability->filter(function ($slot) use ($selectedDate) {
            return optional($slot->slot_date_obj)->toDateString() === $selectedDate;
        })->values();
    }

    if ($searchQuery !== '') {
        $searchNeedle = strtolower($searchQuery);

        $filteredAvailability = $filteredAvailability->filter(function ($slot) use ($searchNeedle) {
            return str_contains($slot->search_blob, $searchNeedle);
        })->values();
    }

    $filteredAvailability = $filteredAvailability
        ->sortByDesc(function ($slot) {
            $datePart = $slot->slot_date_obj ? $slot->slot_date_obj->timestamp : 0;
            $timePart = $slot->start_datetime_obj ? $slot->start_datetime_obj->timestamp : 0;
            return sprintf('%020d-%020d', $datePart, $timePart);
        })
        ->values();
@endphp

<style>
:root {
    --border-soft: rgba(255,255,255,.08);
    --text-muted: rgba(255,255,255,.55);
    --text-strong: rgba(255,255,255,.92);
    --accent: #38bdf8;
    --success: #22c55e;
    --danger: #ef4444;
    --warning: #f59e0b;
    --bg-card: linear-gradient(180deg, #020b1f, #020617);
}

.page-header p {
    color: var(--text-muted);
}

.alert {
    border-radius: 12px;
    padding: .75rem 1rem;
    font-size: .85rem;
}

.alert-success {
    background: rgba(34,197,94,.10);
    border: 1px solid rgba(34,197,94,.20);
    color: #bbf7d0;
}

.alert-danger {
    background: rgba(239,68,68,.10);
    border: 1px solid rgba(239,68,68,.20);
    color: #fecaca;
}

.availability-card {
    background: var(--bg-card);
    border: 1px solid var(--border-soft);
    border-radius: 16px;
    padding: 1.5rem;
    overflow: hidden;
}

.availability-form input,
.availability-form select,
.filter-box input,
.filter-box .search-input {
    border: 1px solid var(--border-soft);
    border-radius: 10px;
}

.date-white,
.search-input {
    background: #111827 !important;
    color: #ffffff !important;
}

.date-white:focus,
.search-input:focus {
    background: #111827 !important;
    color: #ffffff !important;
    border-color: rgba(56,189,248,.45) !important;
    box-shadow: 0 0 0 .2rem rgba(56,189,248,.10) !important;
}

.date-white::-webkit-calendar-picker-indicator {
    opacity: 1;
    cursor: pointer;
    filter: none;
}

.dark-select {
    background: #020617 !important;
    color: #ffffff !important;
}

.dark-select:focus {
    background: #020617 !important;
    color: #ffffff !important;
    border-color: rgba(56,189,248,.45) !important;
    box-shadow: 0 0 0 .2rem rgba(56,189,248,.10) !important;
}

.dark-select option {
    background: #ffffff;
    color: #111827;
}

.availability-form label,
.filter-label {
    font-size: .72rem;
    color: var(--text-muted);
    text-transform: uppercase;
    letter-spacing: .05em;
    margin-bottom: .35rem;
    display: block;
}

.availability-form button,
.search-btn {
    background: var(--accent);
    border: none;
    font-weight: 700;
    color: #fff;
    border-radius: 10px;
    padding: .65rem 1rem;
}

.availability-form button:hover,
.search-btn:hover {
    opacity: .95;
}

.filter-row {
    display: grid;
    grid-template-columns: 1.1fr 1.3fr auto auto;
    gap: .75rem;
    align-items: end;
    margin-bottom: 1rem;
}

.filter-box {
    min-width: 0;
}

.filter-actions {
    display: flex;
    gap: .5rem;
    flex-wrap: wrap;
}

.availability-table {
    border: 1px solid var(--border-soft);
    border-radius: 16px;
    overflow: hidden;
}

.availability-table thead {
    background: rgba(56,189,248,.08);
}

.availability-table th,
.availability-table td {
    padding: .85rem;
    font-size: .88rem;
    color: #fff;
    border-bottom: 1px solid rgba(255,255,255,.05);
    vertical-align: middle;
}

.availability-table th {
    font-size: .74rem;
    text-transform: uppercase;
    letter-spacing: .06em;
    color: var(--text-muted);
}

.availability-table * {
    background: transparent !important;
}

.status {
    display: inline-flex;
    align-items: center;
    padding: .35rem .7rem;
    border-radius: 999px;
    font-size: .68rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: .04em;
}

.status.active {
    background: rgba(34,197,94,.15);
    color: var(--success);
}

.status.inactive {
    background: rgba(239,68,68,.15);
    color: var(--danger);
}

.btn-toggle {
    border: 1px solid rgba(255,255,255,.12);
    border-radius: 10px;
    padding: .45rem .8rem;
    font-size: .8rem;
    font-weight: 700;
    background: transparent;
    color: #fff;
}

.btn-toggle.end {
    border-color: rgba(239,68,68,.25);
    color: #fca5a5;
}

.btn-toggle.start {
    border-color: rgba(34,197,94,.25);
    color: #86efac;
}

.btn-toggle.disabled-btn,
.btn-toggle:disabled {
    opacity: .5;
    cursor: not-allowed;
    border-color: rgba(255,255,255,.08);
    color: rgba(255,255,255,.45);
}

.empty-state {
    padding: 3rem;
    text-align: center;
    color: var(--text-muted);
}

.help-text {
    margin-top: .4rem;
    font-size: .82rem;
    color: var(--text-muted);
}

.time-group {
    display: grid;
    grid-template-columns: 1fr 1fr .9fr;
    gap: .5rem;
}

.muted {
    color: var(--text-muted);
}

.result-summary {
    display: flex;
    justify-content: space-between;
    gap: 1rem;
    flex-wrap: wrap;
    margin-bottom: 1rem;
    color: var(--text-muted);
    font-size: .88rem;
}

.mobile-availability-list {
    display: none;
}

.mobile-slot-card {
    border: 1px solid var(--border-soft);
    border-radius: 14px;
    padding: 1rem;
    background: rgba(255,255,255,.02);
}

.mobile-slot-card + .mobile-slot-card {
    margin-top: .75rem;
}

.mobile-slot-row {
    display: flex;
    justify-content: space-between;
    gap: .75rem;
    margin-bottom: .55rem;
}

.mobile-slot-label {
    font-size: .72rem;
    color: var(--text-muted);
    text-transform: uppercase;
    letter-spacing: .05em;
    min-width: 90px;
}

.mobile-slot-value {
    color: #fff;
    text-align: right;
    flex: 1;
    word-break: break-word;
}

@media (max-width: 992px) {
    .filter-row {
        grid-template-columns: 1fr 1fr;
    }
}

@media (max-width: 768px) {
    .availability-card {
        padding: 1rem;
    }

    .time-group {
        grid-template-columns: 1fr;
    }

    .filter-row {
        grid-template-columns: 1fr;
    }

    .filter-actions {
        width: 100%;
    }

    .filter-actions .search-btn,
    .filter-actions .btn {
        width: 100%;
    }

    .availability-table {
        display: none;
    }

    .mobile-availability-list {
        display: block;
    }

    .page-header h4 {
        font-size: 1.2rem;
    }

    .page-header p {
        font-size: .9rem;
    }
}
</style>

<div class="page-header mb-4">
    <h4>My Availability</h4>
    <p>Add your schedule and review your availability history.</p>
</div>

@if(session('success'))
    <div class="alert alert-success mb-3">
        {{ session('success') }}
    </div>
@endif

@if($errors->any())
    <div class="alert alert-danger mb-3">
        {{ $errors->first() }}
    </div>
@endif

<div class="availability-card mb-4">
    <form method="POST"
          action="{{ route('provider.availability.store') }}"
          class="availability-form"
          id="availabilityForm">
        @csrf

        <input type="hidden" name="time_start" id="time_start" value="{{ old('time_start') }}">
        <input type="hidden" name="time_end" id="time_end" value="{{ old('time_end') }}">

        <div class="row g-3 align-items-end">
            <div class="col-md-4">
                <label>Date</label>
                <input
                    type="date"
                    name="date"
                    class="form-control date-white"
                    required
                    min="{{ $todayDate }}"
                    value="{{ old('date', $todayDate) }}"
                >
            </div>

            <div class="col-md-3">
                <label>Start Time</label>
                <div class="time-group">
                    <select id="start_hour" class="form-control dark-select" required>
                        <option value="">Hour</option>
                        @foreach($hours as $hour)
                            <option value="{{ $hour }}" {{ (string)$oldStart['hour'] === (string)$hour ? 'selected' : '' }}>
                                {{ $hour }}
                            </option>
                        @endforeach
                    </select>

                    <select id="start_minute" class="form-control dark-select" required>
                        <option value="">Min</option>
                        @foreach($minutes as $minute)
                            @php $minuteValue = str_pad($minute, 2, '0', STR_PAD_LEFT); @endphp
                            <option value="{{ $minuteValue }}" {{ $oldStart['minute'] === $minuteValue ? 'selected' : '' }}>
                                {{ $minuteValue }}
                            </option>
                        @endforeach
                    </select>

                    <select id="start_ampm" class="form-control dark-select" required>
                        <option value="">AM/PM</option>
                        @foreach($ampmOptions as $ampm)
                            <option value="{{ $ampm }}" {{ $oldStart['ampm'] === $ampm ? 'selected' : '' }}>
                                {{ $ampm }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="col-md-3">
                <label>End Time</label>
                <div class="time-group">
                    <select id="end_hour" class="form-control dark-select" required>
                        <option value="">Hour</option>
                        @foreach($hours as $hour)
                            <option value="{{ $hour }}" {{ (string)$oldEnd['hour'] === (string)$hour ? 'selected' : '' }}>
                                {{ $hour }}
                            </option>
                        @endforeach
                    </select>

                    <select id="end_minute" class="form-control dark-select" required>
                        <option value="">Min</option>
                        @foreach($minutes as $minute)
                            @php $minuteValue = str_pad($minute, 2, '0', STR_PAD_LEFT); @endphp
                            <option value="{{ $minuteValue }}" {{ $oldEnd['minute'] === $minuteValue ? 'selected' : '' }}>
                                {{ $minuteValue }}
                            </option>
                        @endforeach
                    </select>

                    <select id="end_ampm" class="form-control dark-select" required>
                        <option value="">AM/PM</option>
                        @foreach($ampmOptions as $ampm)
                            <option value="{{ $ampm }}" {{ $oldEnd['ampm'] === $ampm ? 'selected' : '' }}>
                                {{ $ampm }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="col-md-2">
                <button class="btn w-100" type="submit">Add</button>
            </div>
        </div>
    </form>
</div>

<div class="availability-card">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
        <div>
            <h5 class="mb-1" style="color: var(--text-strong);">Availability History</h5>
            <div class="muted" style="font-size:.9rem;">View, search, and manage your saved schedules</div>
        </div>
    </div>

    <form method="GET" class="filter-row">
        <div class="filter-box">
            <label class="filter-label">Filter by Date</label>
            <input
                type="date"
                name="date"
                class="form-control date-white"
                value="{{ $selectedDate }}"
            >
        </div>

        <div class="filter-box">
            <label class="filter-label">Search Date / Time / Status</label>
            <input
                type="text"
                name="q"
                class="form-control search-input"
                placeholder="Example: 03:00 PM, active, March 20"
                value="{{ $searchQuery }}"
            >
        </div>

        <div class="filter-actions">
            <button type="submit" class="search-btn">Search</button>
        </div>

        <div class="filter-actions">
            @if($selectedDate || $searchQuery !== '')
                <a href="{{ route('provider.availability.index') }}" class="btn btn-outline-light">
                    Clear
                </a>
            @endif
        </div>
    </form>

    <div class="result-summary">
        <div>
            Showing <strong>{{ $filteredAvailability->count() }}</strong> result{{ $filteredAvailability->count() === 1 ? '' : 's' }}
        </div>
        <div>
            Sorted by <strong>latest date first</strong>
        </div>
    </div>

    @if ($filteredAvailability->isEmpty())
        <div class="empty-state">
            No availability found.
        </div>
    @else
        <div class="availability-table">
            <div class="table-responsive">
                <table class="table mb-0 align-middle">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Availability</th>
                            <th>Status</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($filteredAvailability as $slot)
                            <tr>
                                <td>{{ $slot->date_label }}</td>
                                <td>{{ $slot->availability_label }}</td>
                                <td>
                                    <span class="status {{ $slot->effective_status }}">
                                        {{ ucfirst($slot->effective_status) }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    @if($slot->is_expired)
                                        <button class="btn-toggle disabled-btn" type="button" disabled>
                                            Expired
                                        </button>
                                    @else
                                        <form method="POST"
                                              action="{{ route('provider.availability.toggle', $slot->id) }}"
                                              class="d-inline">
                                            @csrf
                                            <button
                                                class="btn-toggle {{ $slot->effective_status === 'active' ? 'end' : 'start' }}"
                                                type="submit">
                                                {{ $slot->effective_status === 'active' ? 'End' : 'Start' }}
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

        <div class="mobile-availability-list">
            @foreach ($filteredAvailability as $slot)
                <div class="mobile-slot-card">
                    <div class="mobile-slot-row">
                        <div class="mobile-slot-label">Date</div>
                        <div class="mobile-slot-value">{{ $slot->date_label }}</div>
                    </div>

                    <div class="mobile-slot-row">
                        <div class="mobile-slot-label">Time</div>
                        <div class="mobile-slot-value">{{ $slot->availability_label }}</div>
                    </div>

                    <div class="mobile-slot-row">
                        <div class="mobile-slot-label">Status</div>
                        <div class="mobile-slot-value">
                            <span class="status {{ $slot->effective_status }}">
                                {{ ucfirst($slot->effective_status) }}
                            </span>
                        </div>
                    </div>

                    <div class="mobile-slot-row mb-0">
                        <div class="mobile-slot-label">Action</div>
                        <div class="mobile-slot-value">
                            @if($slot->is_expired)
                                <button class="btn-toggle disabled-btn" type="button" disabled>
                                    Expired
                                </button>
                            @else
                                <form method="POST"
                                      action="{{ route('provider.availability.toggle', $slot->id) }}"
                                      class="d-inline">
                                    @csrf
                                    <button
                                        class="btn-toggle {{ $slot->effective_status === 'active' ? 'end' : 'start' }}"
                                        type="submit">
                                        {{ $slot->effective_status === 'active' ? 'End' : 'Start' }}
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>

<script>
function to24Hour(hour, minute, ampm) {
    hour = parseInt(hour, 10);

    if (ampm === 'AM') {
        if (hour === 12) hour = 0;
    } else if (ampm === 'PM') {
        if (hour !== 12) hour += 12;
    }

    return String(hour).padStart(2, '0') + ':' + String(minute).padStart(2, '0');
}

document.getElementById('availabilityForm')?.addEventListener('submit', function (e) {
    const dateInput = document.querySelector('input[name="date"]')?.value || '';
    const startHour = document.getElementById('start_hour')?.value || '';
    const startMinute = document.getElementById('start_minute')?.value || '';
    const startAmpm = document.getElementById('start_ampm')?.value || '';

    const endHour = document.getElementById('end_hour')?.value || '';
    const endMinute = document.getElementById('end_minute')?.value || '';
    const endAmpm = document.getElementById('end_ampm')?.value || '';

    if (!dateInput || !startHour || !startMinute || !startAmpm || !endHour || !endMinute || !endAmpm) {
        e.preventDefault();
        alert('Please complete the date, start time, and end time.');
        return;
    }

    const start24 = to24Hour(startHour, startMinute, startAmpm);
    const end24 = to24Hour(endHour, endMinute, endAmpm);

    document.getElementById('time_start').value = start24;
    document.getElementById('time_end').value = end24;

    if (start24 >= end24) {
        e.preventDefault();
        alert('End time must be later than start time.');
        return;
    }

    const now = new Date();
    const startDateTime = new Date(dateInput + 'T' + start24 + ':00');
    const endDateTime = new Date(dateInput + 'T' + end24 + ':00');

    if (endDateTime <= now) {
        e.preventDefault();
        alert('You cannot add availability for a time that has already ended.');
        return;
    }

    if (startDateTime <= now && dateInput === new Date().toISOString().split('T')[0]) {
        e.preventDefault();
        alert('Start time must be later than the current time for today.');
    }
});
</script>

@endsection