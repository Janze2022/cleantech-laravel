@extends('provider.layouts.app')

@section('title', 'Analytics')

@section('content')

<style>
:root{
    --bg-deep:#020617;
    --bg-card:#020b1f;
    --bg-card-2:#07112a;
    --border-soft: rgba(255,255,255,.09);
    --text-muted: rgba(255,255,255,.58);
    --text-soft: rgba(255,255,255,.75);
    --accent:#38bdf8;
    --good:#22c55e;
    --warn:#facc15;
    --bad:#ef4444;
    --violet:#a78bfa;
    --rose:#fb7185;
}

*{ box-sizing:border-box; }

.muted{ color: var(--text-muted); }
.soft{ color: var(--text-soft); }

.page-head{
    display:flex;
    align-items:flex-end;
    justify-content:space-between;
    gap:.85rem;
    flex-wrap:wrap;
    margin-bottom:.85rem;
}

.page-head h4{
    margin:0;
    font-size:1.25rem;
    font-weight:900;
    line-height:1.2;
}

.cardx{
    background:
        radial-gradient(1200px 220px at 10% 0%, rgba(56,189,248,.12), transparent 55%),
        linear-gradient(180deg, var(--bg-card), var(--bg-deep));
    border:1px solid var(--border-soft);
    border-radius:16px;
    padding:1rem;
    box-shadow:0 12px 30px rgba(0,0,0,.28);
    min-width:0;
    overflow:hidden;
}

.cardx.tight{ padding:.85rem; }

.divider{
    height:1px;
    background:rgba(255,255,255,.08);
    margin:.8rem 0;
}

.filters{
    width:100%;
}

.filters form{
    display:grid;
    grid-template-columns: repeat(6, minmax(0, 1fr));
    gap:.7rem;
    align-items:end;
}

.label-sm{
    font-size:.68rem;
    letter-spacing:.08em;
    text-transform:uppercase;
    color:var(--text-muted);
    margin-bottom:.32rem;
    font-weight:700;
}

.input,
select{
    width:100%;
    background:rgba(2,6,23,.75) !important;
    border:1px solid var(--border-soft) !important;
    color:#fff !important;
    border-radius:10px;
    padding:.58rem .72rem;
    min-height:42px;
    outline:none;
    box-shadow:inset 0 0 0 1px rgba(255,255,255,.02);
    font-size:.92rem;
}

select option{ background:#020617; color:#fff; }

input[type="date"]::-webkit-calendar-picker-indicator{
    filter: invert(1);
    cursor: pointer;
}

.btnx{
    width:100%;
    min-height:42px;
    background:linear-gradient(180deg,#0ea5e9,#38bdf8);
    border:none;
    font-weight:900;
    padding:.62rem .9rem;
    border-radius:10px;
    color:#fff;
    white-space:nowrap;
    box-shadow:0 8px 18px rgba(14,165,233,.16);
    text-decoration:none;
    display:inline-flex;
    align-items:center;
    justify-content:center;
    font-size:.92rem;
    text-align:center;
}

.btnx.secondary{
    background:rgba(255,255,255,.06);
    border:1px solid rgba(255,255,255,.10);
    box-shadow:none;
}

.kpi-grid{
    display:grid;
    grid-template-columns: repeat(5, minmax(0, 1fr));
    gap:.75rem;
    margin-top:.8rem;
}

.kpi{
    background:linear-gradient(180deg, rgba(7,17,42,.62), rgba(2,6,23,.52));
    border:1px solid rgba(255,255,255,.08);
    border-radius:14px;
    padding:.9rem;
    position:relative;
    overflow:hidden;
    min-width:0;
}

.kpi:before{
    content:"";
    position:absolute;
    inset:-2px -2px auto auto;
    width:180px;
    height:180px;
    background: radial-gradient(circle at 40% 40%, rgba(56,189,248,.18), transparent 60%);
    transform: translate(40%,-45%);
}

.kpi .top{
    display:flex;
    align-items:flex-start;
    justify-content:space-between;
    gap:.6rem;
}

.kpi .title{
    font-size:.68rem;
    letter-spacing:.08em;
    text-transform:uppercase;
    color:var(--text-muted);
    line-height:1.35;
}

.kpi .pill{
    font-size:.66rem;
    font-weight:900;
    letter-spacing:.05em;
    padding:.22rem .5rem;
    border-radius:999px;
    border:1px solid rgba(255,255,255,.10);
    color:rgba(255,255,255,.82);
    background:rgba(2,6,23,.55);
    flex:0 0 auto;
}

.kpi .value{
    margin-top:.55rem;
    font-size:1.15rem;
    font-weight:950;
    color:#fff;
    line-height:1.25;
    word-break:break-word;
}

.kpi .sub{
    margin-top:.12rem;
    font-size:.8rem;
    color:var(--text-muted);
    line-height:1.35;
}

.layout{
    display:grid;
    grid-template-columns: 1.1fr .9fr;
    gap:.9rem;
    margin-top:.9rem;
    align-items:start;
}

.layout > .cardx{
    align-self:start;
}

.stack{
    display:grid;
    grid-template-rows:auto minmax(0, 1fr);
    gap:.75rem;
    min-width:0;
    align-content:start;
}

.panel-title{
    display:flex;
    align-items:flex-start;
    justify-content:space-between;
    gap:.85rem;
    margin-bottom:.65rem;
    flex-wrap:wrap;
}

.panel-title h6{
    margin:0;
    font-weight:900;
    font-size:1rem;
    line-height:1.25;
}

.panel-title .hint{
    font-size:.82rem;
    color:var(--text-muted);
    margin-top:.12rem;
    line-height:1.35;
}

.chart-wrap{
    position:relative;
    width:100%;
    height:220px;
    min-width:0;
}

.chart-wrap.tall{ height:250px; }
.chart-wrap.service{ height:240px; }
.chart-wrap.status{ height:200px; }

.status-donut-wrap{
    position:relative;
}

.status-donut-center{
    position:absolute;
    inset:50% auto auto 50%;
    transform:translate(-50%, -50%);
    text-align:center;
    pointer-events:none;
    width:min(170px, 62%);
}

.status-donut-center strong{
    display:block;
    color:#fff;
    font-size:1.45rem;
    font-weight:950;
    line-height:1;
}

.status-donut-center span{
    display:block;
    margin-top:.35rem;
    color:var(--text-muted);
    font-size:.78rem;
    font-weight:800;
    line-height:1.25;
    text-transform:uppercase;
    letter-spacing:.08em;
}

canvas{
    width:100% !important;
    height:100% !important;
    display:block;
}

.chart-stats{
    margin-top:.8rem;
    display:grid;
    grid-template-columns:repeat(3, minmax(0, 1fr));
    gap:.6rem;
}

.chart-insights{
    margin-top:.6rem;
    display:grid;
    grid-template-columns:repeat(3, minmax(0, 1fr));
    gap:.6rem;
}

.chart-stat{
    border:1px solid rgba(255,255,255,.06);
    background:rgba(255,255,255,.03);
    border-radius:12px;
    padding:.7rem .78rem;
    min-width:0;
}

.chart-stat-label{
    color:var(--text-muted);
    font-size:.68rem;
    font-weight:800;
    letter-spacing:.08em;
    text-transform:uppercase;
}

.chart-stat-value{
    margin-top:.28rem;
    color:#fff;
    font-size:1rem;
    font-weight:900;
    line-height:1.3;
    word-break:break-word;
}

.chart-insight{
    border:1px solid rgba(255,255,255,.05);
    background:rgba(2,6,23,.28);
    border-radius:12px;
    padding:.68rem .78rem;
    min-width:0;
}

.chart-insight-label{
    color:var(--text-muted);
    font-size:.68rem;
    font-weight:800;
    letter-spacing:.08em;
    text-transform:uppercase;
}

.chart-insight-value{
    margin-top:.25rem;
    color:#fff;
    font-size:.98rem;
    font-weight:900;
    line-height:1.3;
    word-break:break-word;
}

.mini-legend{
    margin-top:.7rem;
    display:grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap:.5rem .65rem;
}

.status-summary-grid{
    display:grid;
    grid-template-columns:repeat(2, minmax(0, 1fr));
    gap:.65rem;
    grid-auto-rows:minmax(76px, auto);
}

.status-summary-grid .legend-item{
    margin-top:0 !important;
    min-height:76px;
    height:auto;
    padding:.8rem .95rem;
    flex-direction:row;
    align-items:center;
    justify-content:space-between;
}

.status-summary-grid .legend-item.full{
    grid-column:1 / -1;
}

.status-summary-grid .legend-left{
    align-items:center;
}

.status-summary-grid .legend-val{
    align-self:auto;
    min-width:48px;
    text-align:right;
}

.status-summary-card{
    display:block;
}

.legend-item{
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:.75rem;
    border:1px solid rgba(255,255,255,.06);
    background:rgba(2,6,23,.35);
    border-radius:12px;
    padding:.65rem .75rem;
    min-width:0;
}

.legend-left{
    display:flex;
    align-items:center;
    gap:.5rem;
    min-width:0;
    flex:1;
}

.dot{
    width:10px;
    height:10px;
    border-radius:50%;
    box-shadow:0 0 0 4px rgba(255,255,255,.04);
    flex:0 0 auto;
}

.legend-name{
    font-size:.82rem;
    color:rgba(255,255,255,.80);
    white-space:nowrap;
    overflow:hidden;
    text-overflow:ellipsis;
}

.legend-val{
    font-size:.82rem;
    color:rgba(255,255,255,.70);
    font-weight:800;
    flex:0 0 auto;
}

.table-wrap{
    overflow-x:auto;
    border-radius:14px;
    border:1px solid rgba(255,255,255,.06);
    background:rgba(2,6,23,.30);
}

.table-darkx{
    width:100%;
    border-collapse:collapse;
    min-width:640px;
}

.table-darkx th,
.table-darkx td{
    padding:.72rem .8rem;
    border-bottom:1px solid rgba(255,255,255,.06);
    color:rgba(255,255,255,.82);
    font-size:.88rem;
    vertical-align:middle;
}

.table-darkx th{
    font-size:.72rem;
    text-transform:uppercase;
    letter-spacing:.08em;
    color:var(--text-muted);
    background:rgba(255,255,255,.03);
    white-space:nowrap;
}

.table-darkx tr:hover td{
    background:rgba(255,255,255,.025);
}

.badge-soft{
    display:inline-flex;
    align-items:center;
    justify-content:center;
    padding:.26rem .55rem;
    border-radius:999px;
    font-size:.72rem;
    font-weight:900;
    border:1px solid rgba(255,255,255,.08);
    background:rgba(255,255,255,.05);
    color:#fff;
    white-space:nowrap;
}

.empty-state{
    padding:1rem;
    text-align:center;
    color:var(--text-muted);
    font-size:.9rem;
}

/* Laptop / tablet */
@media (max-width: 1200px){
    .filters form{
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }

    .kpi-grid{
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}

/* Tablet */
@media (max-width: 992px){
    .layout{
        grid-template-columns:1fr;
    }

    .chart-wrap,
    .chart-wrap.tall,
    .chart-wrap.service,
    .chart-wrap.status{
        height:260px;
    }
}

/* Mobile */
@media (max-width: 768px){
    .page-head{
        align-items:stretch;
    }

    .page-head h4{
        font-size:1.12rem;
    }

    .filters form{
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap:.65rem;
    }

    .cardx{
        padding:.9rem;
        border-radius:14px;
    }

    .cardx.tight{
        padding:.8rem;
    }

    .panel-title{
        margin-bottom:.55rem;
        gap:.45rem;
    }

    .panel-title h6{
        font-size:.95rem;
    }

    .panel-title .hint,
    .muted,
    .soft{
        font-size:.82rem;
    }

    .kpi-grid{
        grid-template-columns:1fr 1fr;
        gap:.65rem;
    }

    .kpi{
        padding:.8rem;
        border-radius:12px;
    }

    .kpi .value{
        font-size:1rem;
    }

    .kpi .title{
        font-size:.63rem;
    }

    .kpi .sub{
        font-size:.75rem;
    }

    .chart-wrap{
        height:220px;
    }

    .chart-wrap.tall,
    .chart-wrap.service,
    .chart-wrap.status{
        height:230px;
    }

    .mini-legend{
        grid-template-columns:1fr;
    }

    .status-summary-grid{
        grid-template-columns:1fr;
    }

    .chart-stats,
    .chart-insights{
        grid-template-columns:1fr;
    }

    .legend-item{
        padding:.58rem .68rem;
    }

    .legend-name,
    .legend-val{
        font-size:.78rem;
    }

    .table-darkx{
        min-width:560px;
    }
}

/* Small mobile */
@media (max-width: 560px){
    .filters form{
        grid-template-columns:1fr;
    }

    .filters form > div:last-child{
        grid-template-columns:1fr !important;
    }

    .btnx{
        min-height:44px;
    }

    .kpi-grid{
        grid-template-columns:1fr;
    }

    .kpi .value{
        font-size:1.02rem;
    }

    .chart-wrap{
        height:205px;
    }

    .chart-wrap.tall,
    .chart-wrap.service,
    .chart-wrap.status{
        height:220px;
    }

    .table-wrap{
        overflow:visible;
        border:none;
        background:transparent;
    }

    .table-darkx{
        min-width:100%;
        border-collapse:separate;
        border-spacing:0 .7rem;
    }

    .table-darkx thead{
        display:none;
    }

    .table-darkx tbody,
    .table-darkx tr,
    .table-darkx td{
        display:block;
        width:100%;
    }

    .table-darkx tr{
        border:1px solid rgba(255,255,255,.08);
        border-radius:14px;
        overflow:hidden;
        background:linear-gradient(180deg, rgba(7,17,42,.62), rgba(2,6,23,.52));
        margin-bottom:.7rem;
    }

    .table-darkx td{
        min-width:0;
        border-bottom:1px solid rgba(255,255,255,.06);
        padding:.7rem .8rem;
        font-size:.83rem;
        display:flex;
        align-items:center;
        justify-content:space-between;
        gap:.8rem;
        text-align:right;
    }

    .table-darkx td:last-child{
        border-bottom:none;
    }

    .table-darkx td::before{
        content: attr(data-label);
        color:var(--text-muted);
        font-size:.72rem;
        font-weight:800;
        text-transform:uppercase;
        letter-spacing:.07em;
        text-align:left;
    }

    .table-darkx td[colspan]{
        display:block;
        text-align:left;
    }

    .table-darkx td[colspan]::before{
        display:none;
    }
}
</style>

@php
    $dailyCollection = collect($daily ?? []);
    $monthlyCollection = collect($monthly ?? []);
    $statusCollection = collect($statusBreakdown ?? []);
    $dateEarningsCollection = collect($dateEarnings ?? []);
    $topServicesCollection = collect($topServices ?? []);

    $dailySum   = $dailyCollection->sum(fn($x) => (float) ($x->amount ?? 0));
    $monthlySum = $monthlyCollection->sum(fn($x) => (float) ($x->amount ?? 0));
    $dailyAvg   = $dailyCollection->count() > 0 ? ($dailySum / $dailyCollection->count()) : 0;

    $statusTotal = $statusCollection->sum(fn($x) => (int) ($x->cnt ?? 0));
    $topStatus = $statusCollection->sortByDesc(fn($x) => (int) ($x->cnt ?? 0))->first();
    $topStatusLabel = $topStatus ? strtoupper(str_replace('_', ' ', (string) ($topStatus->status ?? ''))) : '—';

    $topStatusHuman = $topStatus ? \Illuminate\Support\Str::headline(str_replace('_', ' ', (string) ($topStatus->status ?? ''))) : 'No status';
    $topStatusCount = (int) ($topStatus->cnt ?? 0);

    $completedCnt = (int) (optional($statusCollection->firstWhere('status', 'completed'))->cnt ?? 0);
    $cancelCnt = (int) (optional($statusCollection->firstWhere('status', 'cancelled'))->cnt ?? 0);

    $topDateLabel = '—';
    $topDateAmount = 0;
    if (!empty($topDate?->date)) {
        $topDateLabel = \Carbon\Carbon::parse($topDate->date)->format('M d, Y');
        $topDateAmount = (float) ($topDate->amount ?? 0);
    }

    $topService = $topServicesCollection->sortByDesc(fn($x) => (int) ($x->bookings_count ?? 0))->first();
    $topServiceLabel = $topService->service_name ?? '—';
    $topServiceCount = (int) ($topService->bookings_count ?? 0);

    $selectedDateRow = null;
    if (!empty($selectedDate)) {
        $selectedDateRow = $dateEarningsCollection->first(function ($row) use ($selectedDate) {
            return (string) ($row->date ?? '') === (string) $selectedDate;
        });
    }
    $selectedDateAmount = (float) ($selectedDateRow->amount ?? 0);
    $trackedDatesCount = (int) $dateEarningsCollection->count();
    $trackedBookingsCount = (int) $dateEarningsCollection->sum(fn($row) => (int) ($row->bookings_count ?? 0));
    $topDateBookings = (int) ($topDate->bookings_count ?? 0);
@endphp

<div class="page-head">
    <div>
        <h4>Analytics</h4>
    </div>

    <div class="filters">
        <form method="GET" action="{{ route('provider.analytics') }}">
            <div>
                <div class="label-sm">Daily range</div>
                <select class="input" name="days">
                    @foreach([7,14,30,60] as $d)
                        <option value="{{ $d }}" {{ (int)($days ?? 14) === $d ? 'selected' : '' }}>
                            {{ $d }} days
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <div class="label-sm">Monthly range</div>
                <select class="input" name="months">
                    @foreach([3,6,12,24] as $m)
                        <option value="{{ $m }}" {{ (int)($months ?? 12) === $m ? 'selected' : '' }}>
                            {{ $m }} months
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <div class="label-sm">Exact date</div>
                <input type="date" class="input" name="date" value="{{ $selectedDate ?? '' }}">
            </div>

            <div>
                <div class="label-sm">From</div>
                <input type="date" class="input" name="from_date" value="{{ $fromDate ?? '' }}">
            </div>

            <div>
                <div class="label-sm">To</div>
                <input type="date" class="input" name="to_date" value="{{ $toDate ?? '' }}">
            </div>

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:.7rem;">
                <button class="btnx" type="submit">Apply</button>
                <a href="{{ route('provider.analytics') }}" class="btnx secondary">Reset</a>
            </div>
        </form>
    </div>
</div>

<div class="cardx">
    <div class="soft" style="font-weight:900;">Overview</div>

    <div class="kpi-grid">
        <div class="kpi">
            <div class="top">
                <div class="title">Annual earned ({{ now()->year }})</div>
                <span class="pill">TOTAL</span>
            </div>
            <div class="value">₱{{ number_format($annualTotal ?? 0, 2) }}</div>
        </div>

        <div class="kpi">
            <div class="top">
                <div class="title">Daily earned</div>
                <span class="pill">{{ (int)($days ?? 14) }}D</span>
            </div>
            <div class="value">₱{{ number_format($dailySum, 2) }}</div>
        </div>

        <div class="kpi">
            <div class="top">
                <div class="title">Monthly earned</div>
                <span class="pill">{{ (int)($months ?? 12) }}M</span>
            </div>
            <div class="value">₱{{ number_format($monthlySum, 2) }}</div>
        </div>

        <div class="kpi">
            <div class="top">
                <div class="title">Top performed date</div>
                <span class="pill">BEST DAY</span>
            </div>
            <div class="value">{{ $topDateLabel }}</div>
            <div class="sub">₱{{ number_format($topDateAmount, 2) }}</div>
        </div>

        <div class="kpi">
            <div class="top">
                <div class="title">Top trend service</div>
                <span class="pill">{{ $topServiceCount }} BOOKINGS</span>
            </div>
            <div class="value" style="color: var(--accent);">{{ $topServiceLabel }}</div>
        </div>
    </div>
</div>

@if(!empty($selectedDate))
    <div class="cardx tight" style="margin-top:.9rem;">
        <div class="panel-title" style="margin-bottom:.3rem;">
            <div>
                <h6>Selected Date Earnings</h6>
            </div>
        </div>

        @if($selectedDateRow)
            <div class="legend-item">
                <div class="legend-left">
                    <span class="dot" style="background: var(--accent);"></span>
                    <div class="legend-name">Earned on selected date</div>
                </div>
                <div class="legend-val">₱{{ number_format((float)($selectedDateRow->amount ?? 0), 2) }}</div>
            </div>
        @else
            <div class="empty-state">No earnings found for this date.</div>
        @endif
    </div>
@endif

<div class="layout">
    <div class="cardx">
        <div class="panel-title">
            <div>
                <h6>Daily Earnings Graph</h6>
            </div>
            <div class="muted" style="font-size:.82rem;">Line graph</div>
        </div>

        <div class="chart-wrap">
            <canvas id="dailyChart"></canvas>
        </div>

        <div class="chart-stats">
            <div class="chart-stat">
                <div class="chart-stat-label">Range total</div>
                <div class="chart-stat-value">₱{{ number_format($dailySum, 2) }}</div>
            </div>
            <div class="chart-stat">
                <div class="chart-stat-label">Average / day</div>
                <div class="chart-stat-value">₱{{ number_format($dailyAvg, 2) }}</div>
            </div>
            <div class="chart-stat">
                <div class="chart-stat-label">{{ !empty($selectedDate) ? 'Selected date' : 'Best day' }}</div>
                <div class="chart-stat-value">
                    @if(!empty($selectedDate))
                        ₱{{ number_format($selectedDateAmount, 2) }}
                    @else
                        ₱{{ number_format($topDateAmount, 2) }}
                    @endif
                </div>
            </div>
        </div>

        <div class="chart-insights">
            <div class="chart-insight">
                <div class="chart-insight-label">Tracked dates</div>
                <div class="chart-insight-value">{{ number_format($trackedDatesCount) }}</div>
            </div>
            <div class="chart-insight">
                <div class="chart-insight-label">Paid jobs</div>
                <div class="chart-insight-value">{{ number_format($trackedBookingsCount) }}</div>
            </div>
            <div class="chart-insight">
                <div class="chart-insight-label">Top day jobs</div>
                <div class="chart-insight-value">{{ number_format($topDateBookings) }}</div>
            </div>
        </div>

    </div>

    <div class="stack">
        <div class="cardx tight">
            <div class="panel-title" style="margin-bottom:.25rem;">
                <div>
                    <h6>Status Breakdown</h6>
                </div>
            </div>

            <div class="status-donut-wrap">
                <div class="chart-wrap status">
                    <canvas id="donutChart"></canvas>
                </div>
                <div class="status-donut-center">
                    <strong>{{ number_format($topStatusCount) }}</strong>
                    <span>{{ $topStatusHuman }}</span>
                </div>
            </div>
        </div>

        <div class="cardx tight status-summary-card">
            <div class="status-summary-grid">
            <div class="legend-item">
                <div class="legend-left">
                    <span class="dot" style="background: var(--accent);"></span>
                    <div class="legend-name">Completed bookings</div>
                </div>
                <div class="legend-val">{{ $completedCnt }}</div>
            </div>

            <div class="legend-item">
                <div class="legend-left">
                    <span class="dot" style="background: var(--bad);"></span>
                    <div class="legend-name">Cancelled bookings</div>
                </div>
                <div class="legend-val">{{ $cancelCnt }}</div>
            </div>

            <div class="legend-item">
                <div class="legend-left">
                    <span class="dot" style="background: var(--violet);"></span>
                    <div class="legend-name">Top status</div>
                </div>
                <div class="legend-val">{{ $topStatusLabel }}</div>
            </div>

            <div class="legend-item">
                <div class="legend-left">
                    <span class="dot" style="background: var(--warn);"></span>
                    <div class="legend-name">Total statuses</div>
                </div>
                <div class="legend-val">{{ $statusTotal }}</div>
            </div>
            </div>
        </div>
    </div>

    <div class="cardx" style="grid-column:1 / -1;">
        <div class="panel-title">
            <div>
                <h6>Monthly Earnings</h6>
            </div>
            <div class="muted" style="font-size:.82rem;">Bar chart</div>
        </div>

        <div class="chart-wrap tall">
            <canvas id="monthlyChart"></canvas>
        </div>
    </div>

    <div class="cardx" style="grid-column:1 / -1;">
        <div class="panel-title">
            <div>
                <h6>Top Trend Services</h6>
            </div>
            <div class="muted" style="font-size:.82rem;">Horizontal bar chart</div>
        </div>

        <div class="chart-wrap service">
            <canvas id="servicesChart"></canvas>
        </div>
    </div>

    <div class="cardx" style="grid-column:1 / -1;">
        <div class="panel-title">
            <div>
                <h6>Earnings by Actual Date</h6>
            </div>
            <div class="muted" style="font-size:.82rem;">Detailed breakdown</div>
        </div>

        <div class="table-wrap">
            <table class="table-darkx">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Day</th>
                        <th>Total Earned</th>
                        <th>Bookings Count</th>
                        <th>Performance</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($dateEarningsCollection as $row)
                        @php
                            $rowDate = !empty($row->date) ? \Carbon\Carbon::parse($row->date) : null;
                            $rowAmount = (float)($row->amount ?? 0);
                        @endphp
                        <tr>
                            <td data-label="Date">{{ $rowDate ? $rowDate->format('M d, Y') : '—' }}</td>
                            <td data-label="Day">{{ $rowDate ? $rowDate->format('l') : '—' }}</td>
                            <td data-label="Total Earned">₱{{ number_format($rowAmount, 2) }}</td>
                            <td data-label="Bookings Count">{{ (int)($row->bookings_count ?? 0) }}</td>
                            <td data-label="Performance">
                                @if(!empty($topDate?->date) && (string)$topDate->date === (string)($row->date ?? ''))
                                    <span class="badge-soft">TOP DATE</span>
                                @else
                                    <span class="badge-soft">NORMAL</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">
                                <div class="empty-state">No earnings data found for the selected filter.</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>

<script>
const daily = @json($daily ?? []);
const monthly = @json($monthly ?? []);
const breakdown = @json($statusBreakdown ?? []);
const topServices = @json($topServices ?? []);

const isMobile = window.innerWidth <= 768;
const isSmallMobile = window.innerWidth <= 560;

const dailyLabels = daily.map(x => {
    const raw = x.label || '';
    const d = new Date(raw);
    if (!isNaN(d)) {
        return d.toLocaleDateString('en-US', { month: 'short', day: '2-digit' });
    }
    return raw;
});
const dailyAmounts = daily.map(x => Number(x.amount || 0));

const monthlyLabels = monthly.map(x => x.label || '');
const monthlyAmounts = monthly.map(x => Number(x.amount || 0));

const donutLabels = breakdown.map(x => (x.status || 'unknown').toString().replaceAll('_', ' ').toUpperCase());
const donutCounts = breakdown.map(x => Number(x.cnt || 0));

const serviceLabels = topServices.map(x => {
    const label = x.service_name || 'Unknown';
    if (isSmallMobile && label.length > 18) {
        return label.substring(0, 18) + '…';
    }
    return label;
});
const serviceCounts = topServices.map(x => Number(x.bookings_count || 0));

const gridColor = 'rgba(255,255,255,.07)';
const tickColor = 'rgba(255,255,255,.70)';

Chart.defaults.color = tickColor;
Chart.defaults.font.family = "system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif";

// Daily line graph
new Chart(document.getElementById('dailyChart'), {
    type: 'line',
    data: {
        labels: dailyLabels,
        datasets: [{
            label: 'Earned (₱)',
            data: dailyAmounts,
            tension: 0.35,
            fill: true,
            borderWidth: isSmallMobile ? 2 : 2,
            pointRadius: isSmallMobile ? 1.5 : 3,
            pointHoverRadius: isSmallMobile ? 3 : 5,
            borderColor: 'rgba(56,189,248,1)',
            backgroundColor: 'rgba(56,189,248,.15)'
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { display: false },
            tooltip: {
                callbacks: {
                    label: (ctx) => ` ₱${Number(ctx.raw || 0).toLocaleString()}`
                }
            }
        },
        scales: {
            x: {
                grid: { color: gridColor, drawBorder:false },
                ticks: {
                    color: tickColor,
                    maxRotation: isMobile ? 0 : 0,
                    autoSkip: true,
                    maxTicksLimit: isSmallMobile ? 4 : 8,
                    font: {
                        size: isSmallMobile ? 10 : 11
                    }
                }
            },
            y: {
                beginAtZero: true,
                grid: { color: gridColor, drawBorder:false },
                ticks: {
                    color: tickColor,
                    maxTicksLimit: isSmallMobile ? 4 : 6,
                    font: {
                        size: isSmallMobile ? 10 : 11
                    },
                    callback: (value) => `₱${Number(value).toLocaleString()}`
                }
            }
        }
    }
});

// Monthly bar chart
new Chart(document.getElementById('monthlyChart'), {
    type: 'bar',
    data: {
        labels: monthlyLabels,
        datasets: [{
            label: 'Earned (₱)',
            data: monthlyAmounts,
            backgroundColor: 'rgba(56,189,248,.78)',
            borderRadius: 8,
            borderWidth: 0,
            maxBarThickness: isSmallMobile ? 22 : 34
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { display: false },
            tooltip: {
                callbacks: {
                    label: (ctx) => ` ₱${Number(ctx.raw || 0).toLocaleString()}`
                }
            }
        },
        scales: {
            x: {
                grid: { color: gridColor, drawBorder:false },
                ticks: {
                    color: tickColor,
                    autoSkip: true,
                    maxRotation: 0,
                    maxTicksLimit: isSmallMobile ? 4 : 8,
                    font: {
                        size: isSmallMobile ? 10 : 11
                    }
                }
            },
            y: {
                beginAtZero: true,
                grid: { color: gridColor, drawBorder:false },
                ticks: {
                    color: tickColor,
                    maxTicksLimit: isSmallMobile ? 4 : 6,
                    font: {
                        size: isSmallMobile ? 10 : 11
                    },
                    callback: (value) => `₱${Number(value).toLocaleString()}`
                }
            }
        }
    }
});

// Donut chart
const donutColors = [
    'rgba(56,189,248,.85)',
    'rgba(250,204,21,.85)',
    'rgba(34,197,94,.85)',
    'rgba(239,68,68,.85)',
    'rgba(167,139,250,.85)',
    'rgba(251,113,133,.85)'
];

new Chart(document.getElementById('donutChart'), {
    type: 'doughnut',
    data: {
        labels: donutLabels,
        datasets: [{
            label: 'Bookings',
            data: donutCounts,
            backgroundColor: donutLabels.map((_, i) => donutColors[i % donutColors.length]),
            borderColor: 'rgba(2,6,23,.9)',
            borderWidth: 2,
            hoverOffset: isSmallMobile ? 4 : 6,
            cutout: isSmallMobile ? '62%' : '68%'
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { display: false },
            tooltip: {
                callbacks: {
                    label: (ctx) => ` ${ctx.label}: ${ctx.raw}`
                }
            }
        }
    }
});

// Services horizontal bar
new Chart(document.getElementById('servicesChart'), {
    type: 'bar',
    data: {
        labels: serviceLabels,
        datasets: [{
            label: 'Bookings',
            data: serviceCounts,
            backgroundColor: 'rgba(167,139,250,.82)',
            borderRadius: 8,
            borderWidth: 0,
            maxBarThickness: isSmallMobile ? 18 : 24
        }]
    },
    options: {
        indexAxis: 'y',
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { display: false },
            tooltip: {
                callbacks: {
                    label: (ctx) => ` ${ctx.raw} bookings`
                }
            }
        },
        scales: {
            x: {
                beginAtZero: true,
                grid: { color: gridColor, drawBorder:false },
                ticks: {
                    color: tickColor,
                    precision: 0,
                    font: {
                        size: isSmallMobile ? 10 : 11
                    }
                }
            },
            y: {
                grid: { display: false, drawBorder:false },
                ticks: {
                    color: tickColor,
                    autoSkip: false,
                    font: {
                        size: isSmallMobile ? 10 : 11
                    }
                }
            }
        }
    }
});

// Build donut legend
(function buildLegend(){
    const wrap = document.getElementById('donutLegend');
    if(!wrap) return;

    wrap.innerHTML = '';

    donutLabels.forEach((name, i) => {
        const val = donutCounts[i] ?? 0;
        const color = donutColors[i % donutColors.length];

        const el = document.createElement('div');
        el.className = 'legend-item';
        el.innerHTML = `
            <div class="legend-left">
                <span class="dot" style="background:${color}"></span>
                <div class="legend-name">${name}</div>
            </div>
            <div class="legend-val">${val}</div>
        `;
        wrap.appendChild(el);
    });
})();
</script>

@endsection
