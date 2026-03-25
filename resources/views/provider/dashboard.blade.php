@extends('provider.layouts.app')

@section('title', 'Provider Dashboard')

@section('content')

<style>
:root{
    --bg-deep:#020617;
    --bg-card:#020b1f;
    --bg-card-2:#07112a;

    --border-soft: rgba(255,255,255,.09);
    --text-muted: rgba(255,255,255,.58);
    --text-soft: rgba(255,255,255,.78);

    --accent:#38bdf8;
    --good:#22c55e;
    --warn:#facc15;
    --bad:#ef4444;
    --violet:#a78bfa;
}

.muted{ color: var(--text-muted); }
.soft{ color: var(--text-soft); }

.page-head{
    display:flex;
    align-items:flex-end;
    justify-content:space-between;
    gap:1rem;
    flex-wrap:wrap;
    margin-bottom: .9rem;
}

.summary-strip-card{
    margin-bottom:.85rem;
    padding:.85rem .95rem;
}

.kpi-row .cardx{
    padding:.8rem .9rem;
    min-height:110px;
}

.kpi-row .analytics-card,
.kpi-row .analytics-card--earned{
    display:block;
    min-height:110px;
}

.kpi-row .kpi-label{
    font-size:.68rem;
}

.kpi-row .kpi-value{
    margin-top:.28rem;
    font-size:1rem;
}

.kpi-row .kpi-value.email-value{
    font-size:.92rem;
}

.cardx{
    background: radial-gradient(1200px 240px at 10% 0%, rgba(56,189,248,.12), transparent 58%),
                linear-gradient(180deg, var(--bg-card), var(--bg-deep));
    border: 1px solid var(--border-soft);
    border-radius: 18px;
    padding: 1rem;
    box-shadow: 0 16px 40px rgba(0,0,0,.35);
}

.kpi-label{
    font-size:.72rem;
    letter-spacing:.08em;
    text-transform: uppercase;
    color: var(--text-muted);
}
.kpi-value{
    margin-top:.42rem;
    font-size:1.22rem;
    font-weight: 950;
    color:#fff;
    word-break: break-word;
    line-height:1.28;
}
.kpi-sub{
    margin-top:.25rem;
    font-size:.86rem;
    color: var(--text-muted);
}
.kpi-accent{ color: var(--accent); }
.kpi-good{ color: var(--good); }
.kpi-value.email-value{
    font-size:1rem;
}

.summary-strip{
    display:grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap:.75rem;
}

.summary-item{
    border:1px solid rgba(255,255,255,.08);
    background:rgba(255,255,255,.03);
    border-radius:14px;
    padding:.72rem .82rem;
    min-width:0;
}

.summary-item-value{
    margin-top:.28rem;
    color:#fff;
    font-size:.98rem;
    font-weight:900;
    line-height:1.3;
    word-break:break-word;
}

.summary-item-value.good{ color:var(--good); }
.summary-item-value.accent{ color:var(--accent); }

@media (max-width: 1320px){
    .summary-strip{
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}

@media (max-width: 600px){
    .summary-strip{
        grid-template-columns: 1fr;
    }
}

.grid-analytics{
    display:grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: .85rem;
    margin-top: .85rem;
    margin-bottom: 1rem;
}
@media (max-width: 1200px){
    .grid-analytics{ grid-template-columns: 1fr; }
}

.analytics-card{
    display:flex;
    flex-direction:column;
    min-height: 320px;
}

.panel-title{
    display:flex;
    align-items:flex-start;
    justify-content:space-between;
    gap: 1rem;
    margin-bottom: .75rem;
}
.panel-title h6{
    margin:0;
    font-weight: 950;
    letter-spacing: .01em;
}
.panel-title .hint{
    font-size:.86rem;
    color: var(--text-muted);
    margin-top:.15rem;
}

.ring-wrap{
    display:grid;
    grid-template-columns:1fr;
    justify-items:center;
    gap: .9rem;
    align-items:start;
    flex:1;
}

.ring{
    position: relative;
    width: 140px;
    height: 140px;
    margin: .15rem auto 0;
}
.ring svg{
    width: 140px;
    height: 140px;
    transform: rotate(-90deg);
}
.ring .center{
    position:absolute;
    inset:0;
    display:flex;
    flex-direction:column;
    align-items:center;
    justify-content:center;
    text-align:center;
    padding: 0 10px;
}
.ring .center .big{
    font-weight: 950;
    font-size: 1.28rem;
    color:#fff;
    line-height: 1.05;
}
.ring .center .small{
    font-size:.78rem;
    letter-spacing:.08em;
    text-transform: uppercase;
    color: var(--text-muted);
    margin-top:.2rem;
}

.legend{
    display:grid;
    width:100%;
    grid-template-columns:repeat(2, minmax(0, 1fr));
    gap: .45rem;
    align-content:start;
}
.legend-item{
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap: .75rem;
    border: 1px solid rgba(255,255,255,.06);
    background: rgba(2,6,23,.35);
    border-radius: 12px;
    padding:.52rem .65rem;
    min-height:48px;
}
.legend-left{
    display:flex;
    align-items:center;
    gap:.6rem;
    min-width:0;
}
.dot{
    width:10px;height:10px;border-radius:50%;
    box-shadow: 0 0 0 4px rgba(255,255,255,.04);
    flex: 0 0 auto;
}
.legend-name{
    font-size:.8rem;
    color: rgba(255,255,255,.82);
    white-space:normal;
}
.legend-val{
    font-size:.8rem;
    color: rgba(255,255,255,.75);
    font-weight: 900;
}

.analytics-card--earned .legend{
    grid-template-columns:1fr;
}

@media (max-width: 900px){
    .legend{
        grid-template-columns:1fr;
    }
}

.table-wrap{
    border: 1px solid var(--border-soft);
    border-radius: 16px;
    overflow: hidden;
    background: linear-gradient(180deg, rgba(7,17,42,.35), rgba(2,6,23,.25));
}

.table-wrap thead{
    background: rgba(56,189,248,.08);
}
.table-wrap th,
.table-wrap td{
    color: #fff;
    padding: .75rem;
    border-bottom: 1px solid rgba(255,255,255,.05);
    font-size: .85rem;
}
.table-wrap *{ background: transparent !important; }

.badge-status{
    display:inline-block;
    padding:.28rem .58rem;
    border-radius:999px;
    font-size:.68rem;
    font-weight: 900;
    letter-spacing:.06em;
    text-transform: uppercase;
    border:1px solid rgba(255,255,255,.10);
    background: rgba(2,6,23,.45);
    color: rgba(255,255,255,.82);
}
.badge-status.confirmed{ color: var(--accent); background: rgba(56,189,248,.12); border-color: rgba(56,189,248,.18); }
.badge-status.in_progress{ color: var(--warn); background: rgba(250,204,21,.10); border-color: rgba(250,204,21,.18); }
.badge-status.paid{ color: var(--good); background: rgba(34,197,94,.10); border-color: rgba(34,197,94,.18); }
.badge-status.completed{ color: #86efac; background: rgba(34,197,94,.08); border-color: rgba(34,197,94,.15); }
.badge-status.cancelled{ color: var(--bad); background: rgba(239,68,68,.10); border-color: rgba(239,68,68,.18); }
</style>

@php
    $today = now()->toDateString();

    $todayBookingsTotal = (int)($todayActivityTotal ?? 0);

    $raw = collect($todayStatusBreakdown ?? []);
    $getCnt = function(string $status) use ($raw) {
        $row = $raw->firstWhere('status', $status);
        return (int)($row->cnt ?? 0);
    };

    $cntConfirmed  = $getCnt('confirmed');
    $cntProgress   = $getCnt('in_progress');
    $cntPaid       = $getCnt('paid');
    $cntCompleted  = $getCnt('completed');
    $cntCancelled  = $getCnt('cancelled');

    $earnedCount = $cntPaid + $cntCompleted;
    $otherCount  = max(0, $todayBookingsTotal - $earnedCount);
@endphp

<div class="page-head">
    <div>
        <h4 class="mb-1">Welcome back, {{ $provider->first_name ?? 'Provider' }}</h4>
    </div>
</div>

<div class="kpi-row">
    <div class="cardx analytics-card">
        <div class="kpi-label">Account Status</div>
        <div class="kpi-value kpi-good">{{ $provider->status ?? '—' }}</div>
    </div>

    <div class="cardx analytics-card">
        <div class="kpi-label">Email</div>
        <div class="kpi-value kpi-accent email-value">{{ $provider->email ?? '—' }}</div>
    </div>

    <div class="cardx analytics-card analytics-card--earned">
        <div class="kpi-label">Phone</div>
        <div class="kpi-value">{{ $provider->phone ?? '—' }}</div>
    </div>

    <div class="cardx">
        <div class="kpi-label">Today’s Earnings</div>
        <div class="kpi-value kpi-good">₱{{ number_format($todayEarnings ?? 0, 2) }}</div>
    </div>
</div>

<div class="grid-analytics">

    <div class="cardx">
        <div class="panel-title">
            <div>
                <h6>Today’s Activity</h6>
            </div>
            <div class="muted" style="font-size:.85rem;">{{ \Carbon\Carbon::parse($today)->format('F d, Y') }}</div>
        </div>

        <div class="ring-wrap">
            <div class="ring" data-total="{{ $todayBookingsTotal }}" data-values="{{ $cntConfirmed }},{{ $cntProgress }},{{ $cntCompleted }},{{ $cntCancelled }}">
                <svg viewBox="0 0 200 200">
                    <circle cx="100" cy="100" r="72" stroke="rgba(255,255,255,.08)" stroke-width="18" fill="none"></circle>
                    <circle class="seg seg-1" cx="100" cy="100" r="72" stroke="rgba(56,189,248,.85)" stroke-width="18" stroke-linecap="butt" fill="none"></circle>
                    <circle class="seg seg-2" cx="100" cy="100" r="72" stroke="rgba(250,204,21,.85)" stroke-width="18" stroke-linecap="butt" fill="none"></circle>
                    <circle class="seg seg-3" cx="100" cy="100" r="72" stroke="rgba(34,197,94,.85)" stroke-width="18" stroke-linecap="butt" fill="none"></circle>
                    <circle class="seg seg-4" cx="100" cy="100" r="72" stroke="rgba(239,68,68,.85)" stroke-width="18" stroke-linecap="butt" fill="none"></circle>
                </svg>

                <div class="center">
                    <div class="big">{{ $todayBookingsTotal }}</div>
                    <div class="small">Bookings</div>
                </div>
            </div>

            <div class="legend">
                <div class="legend-item">
                    <div class="legend-left">
                        <span class="dot" style="background: rgba(56,189,248,.85)"></span>
                        <div class="legend-name">Confirmed</div>
                    </div>
                    <div class="legend-val">{{ $cntConfirmed }}</div>
                </div>

                <div class="legend-item">
                    <div class="legend-left">
                        <span class="dot" style="background: rgba(250,204,21,.85)"></span>
                        <div class="legend-name">In Progress</div>
                    </div>
                    <div class="legend-val">{{ $cntProgress }}</div>
                </div>

                <div class="legend-item">
                    <div class="legend-left">
                        <span class="dot" style="background: rgba(34,197,94,.85)"></span>
                        <div class="legend-name">Completed</div>
                    </div>
                    <div class="legend-val">{{ $cntCompleted }}</div>
                </div>

                <div class="legend-item">
                    <div class="legend-left">
                        <span class="dot" style="background: rgba(239,68,68,.85)"></span>
                        <div class="legend-name">Cancelled</div>
                    </div>
                    <div class="legend-val">{{ $cntCancelled }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="cardx">
        <div class="panel-title">
            <div>
                <h6>Today Status Mix</h6>
            </div>
            <div class="muted" style="font-size:.85rem;">Totals</div>
        </div>

        <div class="ring-wrap">
            <div class="ring" data-total="{{ $todayBookingsTotal }}" data-values="{{ $cntConfirmed }},{{ $cntProgress }},{{ $cntPaid }},{{ $cntCompleted }},{{ $cntCancelled }}">
                <svg viewBox="0 0 200 200">
                    <circle cx="100" cy="100" r="72" stroke="rgba(255,255,255,.08)" stroke-width="18" fill="none"></circle>

                    <circle class="seg seg-1" cx="100" cy="100" r="72" stroke="rgba(56,189,248,.85)" stroke-width="18" fill="none"></circle>
                    <circle class="seg seg-2" cx="100" cy="100" r="72" stroke="rgba(250,204,21,.85)" stroke-width="18" fill="none"></circle>
                    <circle class="seg seg-3" cx="100" cy="100" r="72" stroke="rgba(34,197,94,.85)" stroke-width="18" fill="none"></circle>
                    <circle class="seg seg-4" cx="100" cy="100" r="72" stroke="rgba(167,139,250,.85)" stroke-width="18" fill="none"></circle>
                    <circle class="seg seg-5" cx="100" cy="100" r="72" stroke="rgba(239,68,68,.85)" stroke-width="18" fill="none"></circle>
                </svg>

                <div class="center">
                    <div class="big">{{ $todayBookingsTotal }}</div>
                    <div class="small">Total today</div>
                </div>
            </div>

            <div class="legend">
                <div class="legend-item">
                    <div class="legend-left">
                        <span class="dot" style="background: rgba(56,189,248,.85)"></span>
                        <div class="legend-name">Confirmed</div>
                    </div>
                    <div class="legend-val">{{ $cntConfirmed }}</div>
                </div>

                <div class="legend-item">
                    <div class="legend-left">
                        <span class="dot" style="background: rgba(250,204,21,.85)"></span>
                        <div class="legend-name">In Progress</div>
                    </div>
                    <div class="legend-val">{{ $cntProgress }}</div>
                </div>

                <div class="legend-item">
                    <div class="legend-left">
                        <span class="dot" style="background: rgba(34,197,94,.85)"></span>
                        <div class="legend-name">Paid</div>
                    </div>
                    <div class="legend-val">{{ $cntPaid }}</div>
                </div>

                <div class="legend-item">
                    <div class="legend-left">
                        <span class="dot" style="background: rgba(167,139,250,.85)"></span>
                        <div class="legend-name">Completed</div>
                    </div>
                    <div class="legend-val">{{ $cntCompleted }}</div>
                </div>

                <div class="legend-item">
                    <div class="legend-left">
                        <span class="dot" style="background: rgba(239,68,68,.85)"></span>
                        <div class="legend-name">Cancelled</div>
                    </div>
                    <div class="legend-val">{{ $cntCancelled }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="cardx">
        <div class="panel-title">
            <div>
                <h6>Today Earned</h6>
            </div>
            <div class="muted" style="font-size:.85rem;">₱</div>
        </div>

        <div class="ring-wrap">
            <div class="ring" data-total="{{ max(1, $todayBookingsTotal) }}" data-values="{{ $earnedCount }},{{ $otherCount }}">
                <svg viewBox="0 0 200 200">
                    <circle cx="100" cy="100" r="72" stroke="rgba(255,255,255,.08)" stroke-width="18" fill="none"></circle>

                    <circle class="seg seg-1" cx="100" cy="100" r="72" stroke="rgba(34,197,94,.90)" stroke-width="18" fill="none"></circle>
                    <circle class="seg seg-2" cx="100" cy="100" r="72" stroke="rgba(255,255,255,.12)" stroke-width="18" fill="none"></circle>
                </svg>

                <div class="center">
                    <div class="big">₱{{ number_format($todayEarnings ?? 0, 0) }}</div>
                    <div class="small">Earned</div>
                </div>
            </div>

            <div class="legend">
                <div class="legend-item">
                    <div class="legend-left">
                        <span class="dot" style="background: rgba(34,197,94,.90)"></span>
                        <div class="legend-name">Earned (paid+completed)</div>
                    </div>
                    <div class="legend-val">{{ $earnedCount }}</div>
                </div>

                <div class="legend-item">
                    <div class="legend-left">
                        <span class="dot" style="background: rgba(255,255,255,.18)"></span>
                        <div class="legend-name">Other (not earned)</div>
                    </div>
                    <div class="legend-val">{{ $otherCount }}</div>
                </div>
            </div>
        </div>

    </div>
</div>

<div class="soft" style="font-weight:950; margin-bottom:.65rem;">Recent Bookings</div>

<div class="table-wrap">
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead>
                <tr>
                    <th>Ref</th>
                    <th>Date</th>
                    <th>Preferred Start</th>
                    <th>Availability</th>
                    <th>Status</th>
                    <th class="text-end">Price</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($recentBookings ?? [] as $b)
                    @php
                        $st = strtolower($b->status ?? '');

                        $dateLabel = $b->booking_date
                            ? \Carbon\Carbon::parse($b->booking_date)->format('F d, Y')
                            : '—';

                        $preferredStartLabel = $b->requested_start_time
                            ? \Carbon\Carbon::parse($b->requested_start_time)->format('h:i A')
                            : '—';

                        $availabilityLabel = ($b->time_start && $b->time_end)
                            ? \Carbon\Carbon::parse($b->time_start)->format('h:i A') . ' – ' .
                              \Carbon\Carbon::parse($b->time_end)->format('h:i A')
                            : '—';
                    @endphp
                    <tr>
                        <td>{{ $b->reference_code }}</td>
                        <td>{{ $dateLabel }}</td>
                        <td>{{ $preferredStartLabel }}</td>
                        <td>{{ $availabilityLabel }}</td>
                        <td>
                            <span class="badge-status {{ $st }}">{{ str_replace('_',' ', strtoupper($st ?: '—')) }}</span>
                        </td>
                        <td class="text-end">₱{{ number_format((float)($b->price ?? 0), 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-4 muted">
                            No bookings yet
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<script>
(function(){
    const rings = document.querySelectorAll('.ring');
    rings.forEach(ring => {
        const total = Number(ring.dataset.total || 0);
        const values = (ring.dataset.values || '')
            .split(',')
            .map(x => Number(String(x).trim() || 0));

        const circles = ring.querySelectorAll('circle.seg');
        const r = 72;
        const C = 2 * Math.PI * r;

        circles.forEach(c => {
            c.style.strokeDasharray = `0 ${C}`;
            c.style.strokeDashoffset = `0`;
        });

        if(total <= 0){
            return;
        }

        let offset = 0;
        values.forEach((v, idx) => {
            const c = circles[idx];
            if(!c) return;

            const share = Math.max(0, v) / total;
            const len = Math.max(0, Math.min(C, share * C));

            c.style.strokeDasharray = `${len} ${C - len}`;
            c.style.strokeDashoffset = `${-offset}`;
            offset += len;
        });
    });
})();
</script>

@endsection
