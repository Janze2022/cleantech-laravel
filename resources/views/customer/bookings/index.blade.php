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
        'scheduled',
    ];

    $cancellableStatuses = [
        'pending',
        'accepted',
        'confirmed',
        'scheduled',
    ];

    $currentBookings = $bookings->filter(function ($booking) use ($currentStatuses) {
        $status = strtolower(trim((string) ($booking->status ?? '')));

        return in_array($status, $currentStatuses, true);
    })->values();
@endphp

<style>
:root{
    --book-bg:#020617;
    --book-card:#051023;
    --book-card-soft:#09162b;
    --book-border:rgba(255,255,255,.08);
    --book-border-soft:rgba(255,255,255,.05);
    --book-text:rgba(255,255,255,.92);
    --book-muted:rgba(255,255,255,.58);
    --book-accent:#38bdf8;
    --book-danger:#ef4444;
}

.bookings-shell{
    min-height:calc(100vh - 120px);
}

.bookings-card{
    background:linear-gradient(180deg, var(--book-card), var(--book-bg));
    border:1px solid var(--book-border);
    border-radius:20px;
    padding:1.4rem;
}

.bookings-head{
    display:flex;
    align-items:flex-start;
    justify-content:space-between;
    gap:1rem;
    flex-wrap:wrap;
    margin-bottom:1rem;
}

.bookings-title{
    margin:0;
    font-weight:950;
    letter-spacing:-.02em;
}

.bookings-subtitle{
    margin:.3rem 0 0;
    color:var(--book-muted);
    font-size:.9rem;
    max-width:760px;
}

.flash{
    margin-bottom:1rem;
    padding:.9rem 1rem;
    border-radius:14px;
    border:1px solid var(--book-border);
    font-weight:800;
}

.flash.success{
    background:rgba(34,197,94,.1);
    border-color:rgba(34,197,94,.22);
    color:#bbf7d0;
}

.flash.error{
    background:rgba(239,68,68,.1);
    border-color:rgba(239,68,68,.22);
    color:#fecaca;
}

.summary-pill{
    display:inline-flex;
    align-items:center;
    gap:.45rem;
    min-height:38px;
    padding:.5rem .8rem;
    border-radius:999px;
    border:1px solid rgba(56,189,248,.22);
    background:rgba(56,189,248,.09);
    color:rgba(255,255,255,.95);
    font-size:.8rem;
    font-weight:900;
    white-space:nowrap;
}

.booking-table-wrap{
    border:1px solid var(--book-border-soft);
    border-radius:18px;
    overflow:hidden;
    background:rgba(255,255,255,.02);
}

.booking-table{
    width:100%;
    border-collapse:collapse;
}

.booking-table th{
    padding:.88rem .9rem;
    text-align:left;
    color:var(--book-muted);
    font-size:.74rem;
    letter-spacing:.08em;
    text-transform:uppercase;
    border-bottom:1px solid var(--book-border);
    white-space:nowrap;
}

.booking-table td{
    padding:1rem .9rem;
    border-bottom:1px solid rgba(255,255,255,.04);
    vertical-align:top;
    font-size:.92rem;
}

.booking-table tbody tr:hover{
    background:rgba(56,189,248,.03);
}

.booking-ref{
    font-weight:900;
    color:var(--book-text);
    line-height:1.2;
}

.booking-meta{
    margin-top:.25rem;
    color:var(--book-muted);
    font-size:.8rem;
    line-height:1.4;
}

.status-pill{
    display:inline-flex;
    align-items:center;
    padding:.36rem .75rem;
    border-radius:999px;
    font-size:.7rem;
    font-weight:900;
    letter-spacing:.04em;
    text-transform:uppercase;
    border:1px solid rgba(255,255,255,.1);
    background:rgba(2,6,23,.25);
    white-space:nowrap;
}

.status-pill.pending,
.status-pill.accepted,
.status-pill.confirmed,
.status-pill.scheduled{
    background:rgba(56,189,248,.11);
    border-color:rgba(56,189,248,.24);
    color:#7dd3fc;
}

.status-pill.in_progress,
.status-pill.ongoing,
.status-pill.active{
    background:rgba(245,158,11,.10);
    border-color:rgba(245,158,11,.24);
    color:#fcd34d;
}

.status-note{
    margin-top:.35rem;
    color:var(--book-muted);
    font-size:.78rem;
    line-height:1.35;
}

.action-group{
    display:flex;
    flex-wrap:wrap;
    gap:.55rem;
}

.btn-view,
.btn-cancel{
    display:inline-flex;
    align-items:center;
    justify-content:center;
    min-height:40px;
    padding:.58rem .88rem;
    border-radius:12px;
    font-weight:900;
    text-decoration:none;
    border:1px solid transparent;
    white-space:nowrap;
}

.btn-view{
    background:rgba(56,189,248,.12);
    border-color:rgba(56,189,248,.22);
    color:var(--book-accent);
}

.btn-view:hover{
    background:rgba(56,189,248,.18);
    color:var(--book-accent);
}

.btn-cancel{
    background:rgba(239,68,68,.10);
    border-color:rgba(239,68,68,.2);
    color:#fca5a5;
}

.btn-cancel:hover{
    background:rgba(239,68,68,.16);
    color:#fecaca;
}

.btn-cancel-inline{
    min-height:40px;
    padding:.58rem .88rem;
    border-radius:12px;
    border:1px solid rgba(239,68,68,.2);
    background:rgba(239,68,68,.10);
    color:#fca5a5;
    font-weight:900;
}

.empty-state{
    text-align:center;
    padding:3rem 1rem;
    color:var(--book-muted);
    font-size:.95rem;
    border:1px dashed rgba(255,255,255,.12);
    border-radius:16px;
}

.mobile-list{
    display:none;
    flex-direction:column;
    gap:.8rem;
}

.mobile-card{
    background:rgba(2,6,23,.38);
    border:1px solid var(--book-border-soft);
    border-radius:16px;
    padding:1rem;
}

.mobile-top{
    display:flex;
    align-items:flex-start;
    justify-content:space-between;
    gap:.75rem;
}

.mobile-grid{
    display:grid;
    grid-template-columns:repeat(2, minmax(0, 1fr));
    gap:.7rem;
    margin-top:.85rem;
}

.mobile-k{
    color:var(--book-muted);
    font-size:.7rem;
    letter-spacing:.08em;
    text-transform:uppercase;
    font-weight:800;
}

.mobile-v{
    margin-top:.22rem;
    color:var(--book-text);
    font-size:.88rem;
    font-weight:800;
    word-break:break-word;
}

.mobile-actions{
    display:flex;
    flex-wrap:wrap;
    gap:.6rem;
    margin-top:.95rem;
}

.mobile-actions > a,
.mobile-actions > form{
    flex:1 1 0;
}

.mobile-actions form button{
    width:100%;
}

@media (max-width: 768px){
    .bookings-card{
        padding:1.05rem;
        border-radius:16px;
    }

    .booking-table-wrap{
        display:none;
    }

    .mobile-list{
        display:flex;
    }

    .mobile-grid{
        grid-template-columns:1fr;
    }

    .mobile-actions > a,
    .mobile-actions > form{
        flex-basis:100%;
    }
}
</style>

<div class="bookings-shell">
    <div class="bookings-card">

        @if(session('success'))
            <div class="flash success">{{ session('success') }}</div>
        @endif

        @if($errors->has('general'))
            <div class="flash error">{{ $errors->first('general') }}</div>
        @endif

        <div class="bookings-head">
            <div>
                <h3 class="bookings-title">My Bookings</h3>
                <p class="bookings-subtitle">
                    Track your current bookings here. You can still cancel while a booking is confirmed or waiting to start, but cancellation is locked once the provider is already in progress.
                </p>
            </div>

            <div class="summary-pill">
                <i class="bi bi-calendar-check"></i>
                <span>{{ $currentBookings->count() }} active booking{{ $currentBookings->count() === 1 ? '' : 's' }}</span>
            </div>
        </div>

        @if($currentBookings->isEmpty())
            <div class="empty-state">No current bookings found.</div>
        @else
            <div class="booking-table-wrap">
                <table class="booking-table" aria-label="Bookings table">
                    <thead>
                        <tr>
                            <th>Booking</th>
                            <th>Date</th>
                            <th>Schedule</th>
                            <th>Provider</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($currentBookings as $booking)
                            @php
                                $statusClass = strtolower((string) ($booking->status ?? ''));
                                $canCancel = in_array($statusClass, $cancellableStatuses, true);

                                $dateLabel = $booking->booking_date
                                    ? \Carbon\Carbon::parse($booking->booking_date)->format('F d, Y')
                                    : '—';

                                $timeLabel = ($booking->time_start && $booking->time_end)
                                    ? \Carbon\Carbon::parse($booking->time_start)->format('h:i A') . ' – ' .
                                      \Carbon\Carbon::parse($booking->time_end)->format('h:i A')
                                    : '—';
                            @endphp

                            <tr>
                                <td>
                                    <div class="booking-ref">{{ $booking->reference_code }}</div>
                                    <div class="booking-meta">{{ $booking->service }} • {{ $booking->option }}</div>
                                </td>
                                <td>{{ $dateLabel }}</td>
                                <td>{{ $timeLabel }}</td>
                                <td>{{ $booking->provider_name ?: 'Provider not assigned' }}</td>
                                <td>PHP {{ number_format((float) $booking->price, 2) }}</td>
                                <td>
                                    <span class="status-pill {{ $statusClass }}">
                                        {{ ucfirst(str_replace('_', ' ', $booking->status)) }}
                                    </span>
                                    @if(!$canCancel)
                                        <div class="status-note">Customer cancellation is locked after the job starts.</div>
                                    @endif
                                </td>
                                <td>
                                    <div class="action-group">
                                        <a class="btn-view" href="{{ route('customer.bookings.show', $booking->reference_code) }}">
                                            View Details
                                        </a>

                                        @if($canCancel)
                                            <form method="POST"
                                                  action="{{ route('customer.bookings.cancel', $booking->reference_code) }}"
                                                  onsubmit="return confirm('Cancel this booking?');">
                                                @csrf
                                                <button type="submit" class="btn-cancel-inline">
                                                    Cancel
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

            <div class="mobile-list" aria-label="Bookings list (mobile)">
                @foreach($currentBookings as $booking)
                    @php
                        $statusClass = strtolower((string) ($booking->status ?? ''));
                        $canCancel = in_array($statusClass, $cancellableStatuses, true);

                        $dateLabel = $booking->booking_date
                            ? \Carbon\Carbon::parse($booking->booking_date)->format('F d, Y')
                            : '—';

                        $timeLabel = ($booking->time_start && $booking->time_end)
                            ? \Carbon\Carbon::parse($booking->time_start)->format('h:i A') . ' – ' .
                              \Carbon\Carbon::parse($booking->time_end)->format('h:i A')
                            : '—';
                    @endphp

                    <article class="mobile-card">
                        <div class="mobile-top">
                            <div>
                                <div class="booking-ref">{{ $booking->reference_code }}</div>
                                <div class="booking-meta">{{ $booking->service }} • {{ $booking->option }}</div>
                            </div>

                            <span class="status-pill {{ $statusClass }}">
                                {{ ucfirst(str_replace('_', ' ', $booking->status)) }}
                            </span>
                        </div>

                        <div class="mobile-grid">
                            <div>
                                <div class="mobile-k">Date</div>
                                <div class="mobile-v">{{ $dateLabel }}</div>
                            </div>

                            <div>
                                <div class="mobile-k">Schedule</div>
                                <div class="mobile-v">{{ $timeLabel }}</div>
                            </div>

                            <div>
                                <div class="mobile-k">Provider</div>
                                <div class="mobile-v">{{ $booking->provider_name ?: 'Provider not assigned' }}</div>
                            </div>

                            <div>
                                <div class="mobile-k">Amount</div>
                                <div class="mobile-v">PHP {{ number_format((float) $booking->price, 2) }}</div>
                            </div>
                        </div>

                        <div class="mobile-actions">
                            <a class="btn-view" href="{{ route('customer.bookings.show', $booking->reference_code) }}">
                                View Details
                            </a>

                            @if($canCancel)
                                <form method="POST"
                                      action="{{ route('customer.bookings.cancel', $booking->reference_code) }}"
                                      onsubmit="return confirm('Cancel this booking?');">
                                    @csrf
                                    <button type="submit" class="btn-cancel">
                                        Cancel Booking
                                    </button>
                                </form>
                            @endif
                        </div>

                        @if(!$canCancel)
                            <div class="status-note">Customer cancellation is locked after the job starts.</div>
                        @endif
                    </article>
                @endforeach
            </div>
        @endif

    </div>
</div>

@endsection
