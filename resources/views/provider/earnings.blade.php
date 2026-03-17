@extends('provider.layouts.app')

@section('title', 'Earnings')

@section('content')

@php
    $totalEarnings = (float) ($totalEarnings ?? 0);
    $currentMonthEarnings = (float) ($currentMonthEarnings ?? 0);
    $completedJobs = (int) ($completedJobs ?? 0);
    $latestPaidBookings = $latestPaidBookings ?? collect();
@endphp

<style>
:root{
    --bg-card:#020b1f;
    --bg-deep:#020617;
    --border-soft:rgba(255,255,255,.08);
    --text-muted:rgba(255,255,255,.58);
    --accent:#38bdf8;
    --good:#22c55e;
}

.earnings-shell{
    display:grid;
    gap:1rem;
}

.hero-card,
.list-card,
.stat-card{
    background:linear-gradient(180deg,var(--bg-card),var(--bg-deep));
    border:1px solid var(--border-soft);
    border-radius:18px;
    padding:1.1rem;
}

.hero-card{
    display:flex;
    align-items:flex-start;
    justify-content:space-between;
    gap:1rem;
    flex-wrap:wrap;
}

.hero-card h4,
.list-card h5{
    margin:0;
    color:#fff;
    font-weight:900;
}

.hero-card p,
.muted{
    color:var(--text-muted);
}

.stats{
    display:grid;
    grid-template-columns:repeat(3, minmax(0, 1fr));
    gap:1rem;
}

.stat-label{
    color:var(--text-muted);
    font-size:.78rem;
    text-transform:uppercase;
    letter-spacing:.08em;
    font-weight:800;
}

.stat-value{
    margin-top:.45rem;
    color:#fff;
    font-size:1.45rem;
    font-weight:950;
}

.accent{
    color:var(--accent);
}

.good{
    color:var(--good);
}

.btnx{
    display:inline-flex;
    align-items:center;
    justify-content:center;
    min-height:42px;
    padding:.65rem 1rem;
    border-radius:12px;
    text-decoration:none;
    font-weight:900;
    color:#fff;
    background:linear-gradient(180deg,#0ea5e9,#38bdf8);
}

.booking-list{
    display:grid;
    gap:.75rem;
    margin-top:1rem;
}

.booking-row{
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:1rem;
    padding:.85rem .95rem;
    border-radius:14px;
    background:rgba(255,255,255,.03);
    border:1px solid rgba(255,255,255,.06);
}

.booking-row strong{
    color:#fff;
}

.booking-meta{
    color:var(--text-muted);
    font-size:.88rem;
}

@media (max-width: 768px){
    .stats{
        grid-template-columns:1fr;
    }

    .booking-row{
        flex-direction:column;
        align-items:flex-start;
    }
}
</style>

<div class="earnings-shell">
    <div class="hero-card">
        <div>
            <h4>Earnings Overview</h4>
            <p class="mb-0 mt-2">A quick summary of paid and completed provider bookings.</p>
        </div>

        <a href="{{ route('provider.analytics') }}" class="btnx">Open Full Analytics</a>
    </div>

    <div class="stats">
        <div class="stat-card">
            <div class="stat-label">Total Earnings</div>
            <div class="stat-value accent">PHP {{ number_format($totalEarnings, 2) }}</div>
        </div>

        <div class="stat-card">
            <div class="stat-label">This Month</div>
            <div class="stat-value">PHP {{ number_format($currentMonthEarnings, 2) }}</div>
        </div>

        <div class="stat-card">
            <div class="stat-label">Paid / Completed Jobs</div>
            <div class="stat-value good">{{ number_format($completedJobs) }}</div>
        </div>
    </div>

    <div class="list-card">
        <h5>Recent Paid Bookings</h5>
        <div class="booking-list">
            @forelse($latestPaidBookings as $booking)
                <div class="booking-row">
                    <div>
                        <strong>{{ $booking->reference_code }}</strong>
                        <div class="booking-meta">
                            {{ $booking->service_name ?: 'Service' }}
                            @if(!empty($booking->booking_date))
                                | {{ \Carbon\Carbon::parse($booking->booking_date)->format('M d, Y') }}
                            @endif
                        </div>
                    </div>

                    <div class="booking-meta">
                        {{ strtoupper((string) $booking->status) }} | PHP {{ number_format((float) $booking->price, 2) }}
                    </div>
                </div>
            @empty
                <div class="muted">No paid or completed bookings yet.</div>
            @endforelse
        </div>
    </div>
</div>

@endsection
