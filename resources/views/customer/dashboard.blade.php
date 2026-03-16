@extends('customer.layouts.app')

@section('title', 'Customer Dashboard')

@section('content')

@php
    use Carbon\Carbon;

    $tz = config('app.timezone') ?? 'Asia/Manila';
    $now = Carbon::now($tz);

    $name = $name ?? 'Customer';

    $stats = $stats ?? [
        'total_bookings'  => 0,
        'active_bookings' => 0,
        'total_spent'     => 0,
        'spent_today'     => 0,
        'spent_month'     => 0,
        'spent_year'      => 0,
    ];

    $totalBookings  = (int) ($stats['total_bookings'] ?? 0);
    $activeBookings = (int) ($stats['active_bookings'] ?? 0);
    $totalSpent     = (float) ($stats['total_spent'] ?? 0);
    $spentToday     = (float) ($stats['spent_today'] ?? 0);
    $spentMonth     = (float) ($stats['spent_month'] ?? 0);
    $spentYear      = (float) ($stats['spent_year'] ?? 0);

    $recent = $recentCompleted ?? collect();
    if (!($recent instanceof \Illuminate\Support\Collection)) $recent = collect($recent);

    $dg = fn($obj, $key, $default=null) => data_get($obj, $key, $default);
@endphp

<style>
:root{
    --bg:#020617;
    --card:#020b1f;
    --card2:#0b1220;
    --border:rgba(255,255,255,.08);

    --text:rgba(255,255,255,.92);
    --muted:rgba(255,255,255,.55);

    --accent:#38bdf8;
    --success:#22c55e;

    --r:18px;

    /* ✅ ONE spacing system (no inconsistency) */
    --pad: 16px;
    --gap: 12px;
}

.db-wrap{ padding: 0; }

/* Header */
.db-header{
    background: linear-gradient(180deg, rgba(56,189,248,.10), rgba(2,6,23,0));
    border:1px solid var(--border);
    border-radius: var(--r);
    padding: var(--pad);
    margin-bottom: var(--gap);
}
.db-title{
    margin:0;
    font-weight: 900;
    letter-spacing:-.02em;
    color: var(--text);
    font-size: 1.25rem;
}
.db-sub{
    margin:.25rem 0 0;
    color: var(--muted);
    font-size: .92rem;
}
.db-date{
    color: var(--muted);
    font-size: .9rem;
    text-align:right;
}
@media(max-width:576px){
    .db-date{ text-align:left; margin-top: var(--gap); padding-top: var(--gap); border-top:1px solid rgba(255,255,255,.08); }
}

/* Cards */
.db-card{
    background: linear-gradient(180deg, var(--card), var(--bg));
    border:1px solid var(--border);
    border-radius: var(--r);
    padding: var(--pad);
}
.db-card + .db-card{ margin-top: var(--gap); }

.section-title{
    margin:0 0 10px 0;
    font-weight: 900;
    color: var(--text);
    font-size: 1rem;
}

/* KPI grid */
.kpi-grid{
    display:grid;
    grid-template-columns: repeat(3, 1fr);
    gap: var(--gap);
}
@media(max-width:992px){ .kpi-grid{ grid-template-columns: repeat(2, 1fr); } }
@media(max-width:576px){ .kpi-grid{ grid-template-columns: 1fr; } }

.kpi{
    background: rgba(2,6,23,.45);
    border:1px solid rgba(255,255,255,.07);
    border-radius: 16px;
    padding: var(--pad);
}
.kpi-label{
    color: var(--muted);
    font-size:.78rem;
    letter-spacing:.08em;
    text-transform: uppercase;
}
.kpi-value{
    margin-top:6px;
    color: var(--text);
    font-weight: 950;
    font-size: 1.35rem;
}
.kpi-note{
    margin-top:4px;
    color: var(--muted);
    font-size:.86rem;
}

/* Actions */
.action-grid{
    display:grid;
    grid-template-columns: repeat(2, 1fr);
    gap: var(--gap);
}
@media(max-width:576px){ .action-grid{ grid-template-columns: 1fr; } }

.action{
    background: rgba(2,6,23,.45);
    border:1px solid rgba(255,255,255,.07);
    border-radius: 16px;
    padding: var(--pad);
}
.action h6{
    margin:0 0 6px 0;
    color: var(--text);
    font-weight: 900;
}
.action p{
    margin:0 0 12px 0;
    color: var(--muted);
    font-size:.9rem;
}
.btn-accent{
    display:inline-flex;
    justify-content:center;
    align-items:center;
    width:100%;
    gap:.5rem;
    border-radius: 12px;
    padding: .7rem 1rem;
    font-weight: 800;
    border:1px solid rgba(56,189,248,.45);
    color: var(--accent);
    background: transparent;
    text-decoration:none;
}
.btn-accent:hover{ background: rgba(56,189,248,.08); color: var(--accent); }

/* Recent list */
.list{
    display:flex;
    flex-direction:column;
    gap: var(--gap);
}
.item{
    background: rgba(2,6,23,.45);
    border:1px solid rgba(255,255,255,.07);
    border-radius: 16px;
    padding: var(--pad);
    display:flex;
    justify-content:space-between;
    gap: var(--gap);
    flex-wrap:wrap;
}
.item-left{ min-width:0; flex:1 1 220px; }
.item-title{
    margin:0;
    color: var(--text);
    font-weight: 900;
    font-size:.95rem;
    white-space:nowrap;
    overflow:hidden;
    text-overflow:ellipsis;
}
.item-sub{
    margin:.25rem 0 0;
    color: var(--muted);
    font-size:.85rem;
}
.item-right{
    text-align:right;
    display:flex;
    flex-direction:column;
    align-items:flex-end;
    gap:6px;
}
@media(max-width:576px){
    .item-right{
        width:100%;
        flex-direction:row;
        justify-content:space-between;
        align-items:center;
        text-align:left;
        padding-top: var(--gap);
        border-top:1px solid rgba(255,255,255,.06);
    }
}
.badge-ok{
    display:inline-flex;
    align-items:center;
    padding:.22rem .6rem;
    border-radius:999px;
    font-size:.75rem;
    font-weight:800;
    border:1px solid rgba(34,197,94,.35);
    color: rgba(34,197,94,.95);
}
.empty{
    text-align:center;
    color: var(--muted);
    padding: var(--pad);
}
</style>

<div class="db-wrap">

    {{-- HEADER --}}
    <div class="db-header">
        <div class="d-flex flex-wrap justify-content-between align-items-start" style="gap:var(--gap);">
            <div>
                <h3 class="db-title">Dashboard</h3>
                <p class="db-sub mb-0">
                    Hello, <span style="color:var(--text);font-weight:900;">{{ $name }}</span>.
                    Here’s your booking summary.
                </p>
            </div>
            <div class="db-date">
                <div>{{ $now->format('l, M d, Y') }}</div>
                <div style="font-size:.85rem;">Timezone: {{ $tz }}</div>
            </div>
        </div>
    </div>

    {{-- KPI --}}
    <div class="db-card">
        <div class="section-title">Quick Stats</div>

        <div class="kpi-grid">
            <div class="kpi">
                <div class="kpi-label">Total bookings</div>
                <div class="kpi-value">{{ $totalBookings }}</div>
                <div class="kpi-note">All-time</div>
            </div>

            <div class="kpi">
                <div class="kpi-label">Active bookings</div>
                <div class="kpi-value">{{ $activeBookings }}</div>
                <div class="kpi-note">Pending / ongoing</div>
            </div>

            <div class="kpi">
                <div class="kpi-label">Total spent</div>
                <div class="kpi-value">₱{{ number_format($totalSpent, 2) }}</div>
                <div class="kpi-note">Paid + completed</div>
            </div>

            <div class="kpi">
                <div class="kpi-label">Spent today</div>
                <div class="kpi-value">₱{{ number_format($spentToday, 2) }}</div>
                <div class="kpi-note">Today</div>
            </div>

            <div class="kpi">
                <div class="kpi-label">Spent this month</div>
                <div class="kpi-value">₱{{ number_format($spentMonth, 2) }}</div>
                <div class="kpi-note">{{ $now->format('F Y') }}</div>
            </div>

            <div class="kpi">
                <div class="kpi-label">Spent this year</div>
                <div class="kpi-value">₱{{ number_format($spentYear, 2) }}</div>
                <div class="kpi-note">{{ $now->format('Y') }}</div>
            </div>
        </div>
    </div>

    {{-- ACTIONS --}}
    <div class="db-card">
        <div class="section-title">Quick Actions</div>

        <div class="action-grid">
            <div class="action">
                <h6>Browse Services</h6>
                <p>Explore services and providers.</p>
                <a class="btn-accent" href="{{ route('customer.services') }}">View Services</a>
            </div>

            <div class="action">
                <h6>Bookings History</h6>
                <p>Review your past bookings and payments.</p>
                <a class="btn-accent" href="{{ route('customer.bookings.history') }}">Open History</a>
            </div>
        </div>
    </div>

    {{-- RECENT --}}
    <div class="db-card">
        <div class="d-flex justify-content-between align-items-center" style="gap:var(--gap);">
            <div class="section-title" style="margin:0;">Recent Completed</div>
            <a class="btn-accent" style="width:auto; padding:.55rem .85rem;"
               href="{{ route('customer.bookings.history', ['status' => 'completed']) }}">
                View all
            </a>
        </div>

        <div class="list" style="margin-top:10px;">
            @forelse($recent as $b)
                @php
                    $svc = $dg($b,'service_name')
                        ?? $dg($b,'service')
                        ?? $dg($b,'service.name')
                        ?? 'Service';

                    $prov = $dg($b,'provider_name')
                        ?? $dg($b,'provider')
                        ?? $dg($b,'provider.name')
                        ?? 'Provider';

                    $dt = $dg($b,'completed_at') ?? $dg($b,'booking_date') ?? $dg($b,'created_at') ?? null;
                    $dtLabel = $dt ? Carbon::parse($dt, $tz)->format('M d, Y') : '—';

                    $amt = (float)($dg($b,'total_amount') ?? $dg($b,'total_price') ?? $dg($b,'amount') ?? $dg($b,'price') ?? 0);
                @endphp

                <div class="item">
                    <div class="item-left">
                        <p class="item-title">{{ $svc }}</p>
                        <p class="item-sub">{{ $prov }} • {{ $dtLabel }}</p>
                    </div>
                    <div class="item-right">
                        <div style="font-weight:950; color:var(--text);">₱{{ number_format($amt, 2) }}</div>
                        <span class="badge-ok">Completed</span>
                    </div>
                </div>
            @empty
                <div class="empty">No completed bookings yet.</div>
            @endforelse
        </div>
    </div>

</div>
@endsection
