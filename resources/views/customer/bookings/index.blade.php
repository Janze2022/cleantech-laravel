@extends('customer.layouts.app')

@section('title', 'My Bookings')

@section('content')

@php
    $currentStatuses = [
        'pending',
        'accepted',
        'confirmed',
        'in_progress',
        'ongoing',
        'active',
        'scheduled'
    ];

    $currentBookings = $bookings->filter(function($b) use ($currentStatuses){
        $st = strtolower(trim((string)($b->status ?? '')));
        return in_array($st, $currentStatuses, true);
    })->values();
@endphp

<style>
:root {
    --bg-card: #020b1f;
    --bg-deep: #020617;
    --border-soft: rgba(255,255,255,.08);
    --border-softer: rgba(255,255,255,.05);
    --text-muted: rgba(255,255,255,.55);
    --text-strong: rgba(255,255,255,.90);
    --accent: #38bdf8;
}

.bookings-page { min-height: calc(100vh - 120px); }

.bookings-card {
    background: linear-gradient(180deg, var(--bg-card), var(--bg-deep));
    border: 1px solid var(--border-soft);
    border-radius: 18px;
    padding: 2rem;
}

.bookings-header h3 { font-weight: 900; margin-bottom: .25rem; letter-spacing: -.02em; }
.bookings-header p { color: var(--text-muted); font-size: .92rem; margin: 0; }

.bookings-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 1.25rem;
}

.bookings-table thead th {
    text-align: left;
    font-size: .75rem;
    letter-spacing: .08em;
    text-transform: uppercase;
    color: var(--text-muted);
    border-bottom: 1px solid var(--border-soft);
    padding: .9rem;
    white-space: nowrap;
}

.bookings-table tbody td {
    padding: 1rem .9rem;
    border-bottom: 1px solid rgba(255,255,255,.04);
    font-size: .92rem;
    vertical-align: middle;
}
.bookings-table tbody tr:hover { background: rgba(56,189,248,.03); }

.status {
    display:inline-flex;
    align-items:center;
    padding: .35rem .75rem;
    border-radius: 999px;
    font-size: .7rem;
    font-weight: 800;
    letter-spacing: .04em;
    text-transform: uppercase;
    border: 1px solid rgba(255,255,255,.10);
    background: rgba(2,6,23,.25);
    white-space: nowrap;
}
.status.pending,
.status.accepted,
.status.confirmed,
.status.in_progress,
.status.ongoing,
.status.active,
.status.scheduled {
    background: rgba(56,189,248,.12);
    color: #38bdf8;
    border-color: rgba(56,189,248,.25);
}

.btn-view {
    display:inline-block;
    padding:.5rem .8rem;
    border-radius:12px;
    background:rgba(56,189,248,.12);
    color:var(--accent);
    font-weight:900;
    text-decoration:none;
    border:1px solid rgba(56,189,248,.25);
    white-space: nowrap;
}
.btn-view:hover { background:rgba(56,189,248,.18); color:var(--accent); }

.empty-state { text-align: center; padding: 3rem 1rem; color: var(--text-muted); font-size: .95rem; }

.mobile-list{ display:none; margin-top: 1rem; }
.booking-card{
    background: rgba(2,6,23,.35);
    border: 1px solid var(--border-softer);
    border-radius: 16px;
    padding: 1rem;
}
.booking-top{
    display:flex;
    justify-content:space-between;
    gap:.75rem;
    align-items:flex-start;
}
.booking-ref{
    font-weight: 950;
    color: var(--text-strong);
    letter-spacing: -.01em;
    font-size: .98rem;
}
.booking-meta{
    margin-top:.2rem;
    color: var(--text-muted);
    font-size: .86rem;
    line-height: 1.35;
}
.booking-grid{
    margin-top:.85rem;
    display:grid;
    grid-template-columns: 1fr 1fr;
    gap:.65rem .75rem;
}
.kv .k{
    color: var(--text-muted);
    font-size: .72rem;
    text-transform: uppercase;
    letter-spacing: .08em;
}
.kv .v{
    margin-top:.2rem;
    color: var(--text-strong);
    font-weight: 800;
    font-size: .9rem;
    word-break: break-word;
}
.booking-actions{
    margin-top: .9rem;
    display:flex;
    gap:.6rem;
}
.booking-actions .btn-view{ width: 100%; text-align:center; }

@media (max-width: 768px){
    .bookings-card{ padding: 1.1rem; border-radius: 16px; }
    .bookings-table{ display:none; }
    .mobile-list{ display:flex; flex-direction:column; gap:.75rem; }
    .booking-grid{ grid-template-columns: 1fr; }
}
</style>

<div class="bookings-page">
    <div class="bookings-card">

        <div class="bookings-header mb-3">
            <h3>My Bookings</h3>
            <p>Current / ongoing bookings only</p>
        </div>

        @if($currentBookings->isEmpty())
            <div class="empty-state">No current bookings found.</div>
        @else

            <table class="bookings-table" aria-label="Bookings table">
                <thead>
                    <tr>
                        <th>Reference</th>
                        <th>Service</th>
                        <th>Date</th>
                        <th>Schedule</th>
                        <th>Price</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($currentBookings as $b)
                        @php
                            $statusClass = strtolower((string)($b->status ?? ''));

                            $dateLabel = $b->booking_date
                                ? \Carbon\Carbon::parse($b->booking_date)->format('F d, Y')
                                : '—';

                            $timeLabel = ($b->time_start && $b->time_end)
                                ? \Carbon\Carbon::createFromFormat('H:i:s', $b->time_start)->format('h:i A') . ' – ' .
                                  \Carbon\Carbon::createFromFormat('H:i:s', $b->time_end)->format('h:i A')
                                : '—';
                        @endphp
                        <tr>
                            <td>{{ $b->reference_code }}</td>
                            <td>
                                <div>{{ $b->service }}</div>
                                <small style="color:rgba(255,255,255,.55);">{{ $b->option }}</small>
                            </td>
                            <td>{{ $dateLabel }}</td>
                            <td>{{ $timeLabel }}</td>
                            <td>₱{{ number_format($b->price, 2) }}</td>
                            <td>
                                <span class="status {{ $statusClass }}">
                                    {{ ucfirst(str_replace('_',' ',$b->status)) }}
                                </span>
                            </td>
                            <td>
                                <a class="btn-view" href="{{ route('customer.bookings.show', $b->reference_code) }}">
                                    View Details
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="mobile-list" aria-label="Bookings list (mobile)">
                @foreach($currentBookings as $b)
                    @php
                        $statusClass = strtolower((string)($b->status ?? ''));

                        $dateLabel = $b->booking_date
                            ? \Carbon\Carbon::parse($b->booking_date)->format('F d, Y')
                            : '—';

                        $timeLabel = ($b->time_start && $b->time_end)
                            ? \Carbon\Carbon::createFromFormat('H:i:s', $b->time_start)->format('h:i A') . ' – ' .
                              \Carbon\Carbon::createFromFormat('H:i:s', $b->time_end)->format('h:i A')
                            : '—';
                    @endphp
                    <div class="booking-card">
                        <div class="booking-top">
                            <div style="min-width:0;">
                                <div class="booking-ref">#{{ $b->reference_code }}</div>
                                <div class="booking-meta">
                                    {{ $b->service }} • {{ $b->option }}
                                </div>
                            </div>
                            <span class="status {{ $statusClass }}">{{ ucfirst(str_replace('_',' ',$b->status)) }}</span>
                        </div>

                        <div class="booking-grid">
                            <div class="kv">
                                <div class="k">Date</div>
                                <div class="v">{{ $dateLabel }}</div>
                            </div>
                            <div class="kv">
                                <div class="k">Schedule</div>
                                <div class="v">{{ $timeLabel }}</div>
                            </div>
                            <div class="kv">
                                <div class="k">Price</div>
                                <div class="v">₱{{ number_format($b->price, 2) }}</div>
                            </div>
                        </div>

                        <div class="booking-actions">
                            <a class="btn-view" href="{{ route('customer.bookings.show', $b->reference_code) }}">
                                View Details
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>

        @endif

    </div>
</div>

@endsection