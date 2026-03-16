@extends('admin.layouts.app')

@section('title', 'Reports')

@section('content')
@php
    use Carbon\Carbon;

    $tz  = config('app.timezone') ?? 'Asia/Manila';
    $now = Carbon::now($tz);

    $startStr = $start ?? $now->copy()->subDays(29)->toDateString();
    $endStr   = $end ?? $now->toDateString();

    $rangeBookings = (int)($rangeBookings ?? 0);
    $rangeIncome   = (float)($rangeIncome ?? 0);
    $cancelledLoss = (float)($cancelledLoss ?? 0);
    $netReport     = (float)($netReport ?? 0);

    $statusCounts = $statusCounts ?? [
        'confirmed'   => 0,
        'in_progress' => 0,
        'paid'        => 0,
        'completed'   => 0,
        'cancelled'   => 0,
    ];

    $bookings = $bookings ?? collect();
    $providerPerformance = $providerPerformance ?? collect();
    $serviceClassification = $serviceClassification ?? collect();
    $positives = $positives ?? collect();
    $negatives = $negatives ?? collect();

    $topPerformer = $topPerformer ?? null;

    $confirmedCnt = (int)($statusCounts['confirmed'] ?? 0);
    $progressCnt  = (int)($statusCounts['in_progress'] ?? 0);
    $paidCnt      = (int)($statusCounts['paid'] ?? 0);
    $completedCnt = (int)($statusCounts['completed'] ?? 0);
    $cancelledCnt = (int)($statusCounts['cancelled'] ?? 0);

    $acceptedCount    = $confirmedCnt + $progressCnt + $paidCnt;
    $finishedCount    = $completedCnt;
    $allStatusesTotal = $confirmedCnt + $progressCnt + $paidCnt + $completedCnt + $cancelledCnt;

    $completionRate = $allStatusesTotal > 0 ? round(($finishedCount / $allStatusesTotal) * 100, 1) : 0;
    $cancelRate     = $allStatusesTotal > 0 ? round(($cancelledCnt / $allStatusesTotal) * 100, 1) : 0;
@endphp


<style>
:root{
    --bg:#020617;
    --bg-2:#020617;
    --panel:#0c1a2b;
    --panel-2:#0f2034;
    --line:rgba(255,255,255,.08);
    --line-soft:rgba(255,255,255,.05);
    --text:#eef6ff;
    --muted:#97a9bf;
    --blue:#38bdf8;
    --green:#22c55e;
    --yellow:#f59e0b;
    --red:#ef4444;
    --violet:#8b5cf6;
    --shadow:0 14px 34px rgba(0,0,0,.32);
    --radius:18px;
    --radius-sm:12px;
}

/* PAGE + GLOBAL BACKGROUND */
html{
    scrollbar-width:thin;
    scrollbar-color:rgba(148,163,184,.55) rgba(255,255,255,.06);
    background:var(--bg) !important;
}

body{
    background:var(--bg) !important;
    margin:0;
    padding:0;
}

/* force common layout wrappers to match navbar */
.wrapper,
.main-wrapper,
.layout-wrapper,
.app,
.app-body,
.content-wrapper,
.main-content,
.page-content,
.container-fluid,
.container,
.content,
.main-panel,
.right-panel,
.dashboard-content,
.admin-content{
    background:var(--bg) !important;
}

/* whole page scrollbar */
::-webkit-scrollbar{
    width:10px;
    height:10px;
}
::-webkit-scrollbar-track{
    background:rgba(255,255,255,.04);
    border-radius:999px;
}
::-webkit-scrollbar-thumb{
    background:linear-gradient(180deg, rgba(148,163,184,.62), rgba(100,116,139,.82));
    border-radius:999px;
    border:2px solid rgba(7,16,30,.9);
}
::-webkit-scrollbar-thumb:hover{
    background:linear-gradient(180deg, rgba(165,180,252,.7), rgba(148,163,184,.95));
}

/* inner table scrollbars */
.table-wrap::-webkit-scrollbar{
    width:8px;
    height:8px;
}
.table-wrap::-webkit-scrollbar-track{
    background:rgba(255,255,255,.03);
    border-radius:999px;
}
.table-wrap::-webkit-scrollbar-thumb{
    background:rgba(148,163,184,.55);
    border-radius:999px;
}
.table-wrap::-webkit-scrollbar-thumb:hover{
    background:rgba(148,163,184,.8);
}

/* REPORT PAGE SHELL */
.report-shell{
    min-height:100vh;
    padding:20px;
    background:var(--bg) !important;
}

.report-wrap{
    display:flex;
    flex-direction:column;
    gap:16px;
    width:100%;
}

.topbar{
    display:flex;
    align-items:flex-start;
    justify-content:space-between;
    gap:14px;
    flex-wrap:wrap;
}

.title-wrap{
    min-width:0;
}

.page-title{
    margin:0;
    color:var(--text);
    font-size:2rem;
    font-weight:900;
    letter-spacing:.01em;
    line-height:1.08;
}

.page-subtitle{
    margin-top:6px;
    color:var(--muted);
    font-size:1rem;
    line-height:1.5;
    max-width:860px;
}

/* CARDS */
.filter-card,
.cardx,
.panel{
    background:linear-gradient(180deg, rgba(12,26,43,.98), rgba(15,32,52,.98));
    border:1px solid var(--line);
    border-radius:var(--radius);
    box-shadow:var(--shadow);
    min-width:0;
}

.filter-card{
    padding:16px;
    width:100%;
}

.filter-form{
    display:flex;
    align-items:flex-end;
    justify-content:space-between;
    gap:16px;
    width:100%;
    flex-wrap:wrap;
}

.filter-group{
    display:flex;
    gap:12px;
    align-items:flex-end;
    flex-wrap:wrap;
    min-width:0;
}

.filter-group-left{
    flex:1 1 560px;
}

.filter-group-right{
    flex:0 0 auto;
    justify-content:flex-end;
}

.field-block{
    min-width:220px;
    flex:1 1 220px;
}

.filter-label{
    display:block;
    margin-bottom:6px;
    color:var(--muted);
    font-size:.76rem;
    font-weight:800;
    letter-spacing:.08em;
    text-transform:uppercase;
}

/* FORM */
.form-dark{
    width:100%;
    background:rgba(255,255,255,.04) !important;
    border:1px solid var(--line) !important;
    color:var(--text) !important;
    border-radius:12px !important;
    padding:.72rem .9rem !important;
    font-size:.95rem !important;
    min-height:48px;
}
.form-dark:focus{
    box-shadow:none !important;
    border-color:rgba(56,189,248,.55) !important;
    background:rgba(255,255,255,.05) !important;
}
.form-dark::-webkit-calendar-picker-indicator{
    filter:invert(1);
    opacity:.85;
    cursor:pointer;
}

/* BUTTONS */
.btn-sky{
    border:none !important;
    border-radius:12px !important;
    padding:.72rem 1rem !important;
    min-height:48px;
    font-weight:800 !important;
    color:#052235 !important;
    background:linear-gradient(180deg, rgba(56,189,248,1), rgba(56,189,248,.82)) !important;
    white-space:nowrap;
}

.btn-soft{
    border:1px solid var(--line) !important;
    color:var(--text) !important;
    background:rgba(255,255,255,.04) !important;
    border-radius:12px !important;
    padding:.72rem 1rem !important;
    min-height:48px;
    font-weight:700 !important;
    text-decoration:none !important;
    display:inline-flex;
    align-items:center;
    justify-content:center;
    white-space:nowrap;
}
.btn-soft:hover{
    background:rgba(255,255,255,.07) !important;
    color:var(--text) !important;
}

/* KPI GRID */
.kpi-grid{
    display:grid;
    grid-template-columns:repeat(4, minmax(0, 1fr));
    gap:14px;
    width:100%;
}

.cardx{
    overflow:hidden;
    position:relative;
}

.cardx::before{
    content:"";
    position:absolute;
    left:0;
    top:0;
    width:100%;
    height:2px;
    background:linear-gradient(90deg, transparent, rgba(56,189,248,.95), transparent);
}

.card-head{
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:10px;
    padding:12px 14px;
    border-bottom:1px solid var(--line-soft);
}

.card-title{
    margin:0;
    font-size:.76rem;
    letter-spacing:.12em;
    text-transform:uppercase;
    color:rgba(255,255,255,.72);
    font-weight:800;
}

.card-body{
    padding:14px;
}

.metric{
    color:var(--text);
    font-size:1.8rem;
    font-weight:900;
    line-height:1.12;
    word-break:break-word;
}

.metric-sm{
    font-size:1.06rem;
    line-height:1.35;
    white-space:normal;
}

.meta{
    margin-top:7px;
    color:var(--muted);
    font-size:.88rem;
    line-height:1.48;
}

.badgex{
    display:inline-flex;
    align-items:center;
    justify-content:center;
    padding:.32rem .68rem;
    border-radius:999px;
    background:rgba(255,255,255,.05);
    border:1px solid var(--line);
    color:rgba(255,255,255,.84);
    font-size:.72rem;
    font-weight:800;
    white-space:nowrap;
}

/* LAYOUT */
.layout{
    display:grid;
    grid-template-columns:minmax(0,1.65fr) minmax(0,1fr);
    gap:16px;
    width:100%;
    align-items:start;
}

.stack{
    display:grid;
    gap:16px;
    min-width:0;
}

.panel{
    overflow:hidden;
}

.panel-head{
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:8px;
    flex-wrap:wrap;
    padding:12px 14px;
    border-bottom:1px solid var(--line-soft);
}

.panel-head h4{
    margin:0;
    font-size:1rem;
    color:var(--text);
    font-weight:900;
    letter-spacing:.01em;
}

.panel-body{
    padding:14px;
    min-width:0;
}

.panel-body.no-pad{
    padding:0;
}

/* CHARTS */
.chart-box{
    height:260px;
}
.chart-box.sm{
    height:220px;
}

/* SUMMARY */
.summary-grid{
    display:grid;
    grid-template-columns:repeat(2, minmax(0,1fr));
    gap:10px;
}

.summary-item{
    border:1px solid var(--line);
    background:rgba(255,255,255,.03);
    border-radius:12px;
    padding:12px;
}

.summary-label{
    color:var(--muted);
    font-size:.76rem;
    font-weight:800;
    margin-bottom:5px;
    text-transform:uppercase;
    letter-spacing:.04em;
}

.summary-value{
    color:var(--text);
    font-size:1.12rem;
    font-weight:900;
    line-height:1.15;
}

.summary-sub{
    margin-top:4px;
    color:var(--muted);
    font-size:.79rem;
    line-height:1.4;
}

/* INSIGHTS */
.insight-list{
    list-style:none;
    margin:0;
    padding:0;
    display:grid;
    gap:8px;
}

.insight-list li{
    border:1px solid var(--line);
    background:rgba(255,255,255,.03);
    border-radius:12px;
    padding:10px 11px;
    color:var(--text);
}

.insight-tag{
    font-size:.76rem;
    font-weight:900;
    display:block;
    margin-bottom:4px;
}

.insight-text{
    color:var(--muted);
    font-size:.84rem;
    line-height:1.45;
}

/* TABLE */
.table-wrap{
    overflow:auto;
    -webkit-overflow-scrolling:touch;
    width:100%;
}

.table-darkx{
    width:100%;
    min-width:680px;
    border-collapse:collapse;
}

.table-darkx th,
.table-darkx td{
    padding:.8rem .8rem;
    border-bottom:1px solid var(--line-soft);
    color:rgba(255,255,255,.92);
    vertical-align:middle;
    font-size:.87rem;
}

.table-darkx th{
    background:#0d1b2c;
    color:rgba(255,255,255,.6);
    text-transform:uppercase;
    letter-spacing:.08em;
    font-size:.73rem;
    font-weight:800;
}

.table-darkx tr:hover td{
    background:rgba(56,189,248,.04);
}

.table-darkx td.text-end,
.table-darkx th.text-end{
    text-align:right;
}

/* STATUS PILL */
.pill{
    display:inline-flex;
    align-items:center;
    gap:.4rem;
    padding:.34rem .58rem;
    border-radius:999px;
    border:1px solid var(--line);
    background:rgba(255,255,255,.04);
    font-size:.74rem;
    font-weight:800;
    white-space:nowrap;
}

.dot{
    width:8px;
    height:8px;
    border-radius:50%;
    background:var(--blue);
    flex:0 0 8px;
}
.dot.green{ background:var(--green); }
.dot.yellow{ background:var(--yellow); }
.dot.red{ background:var(--red); }

.print-only{
    display:none;
}

/* RESPONSIVE */
@media (max-width:1200px){
    .kpi-grid{
        grid-template-columns:repeat(2, minmax(0,1fr));
    }

    .layout{
        grid-template-columns:1fr;
    }
}

@media (max-width:768px){
    .report-shell{
        padding:12px;
    }

    .page-title{
        font-size:1.45rem;
    }

    .page-subtitle{
        font-size:.92rem;
    }

    .filter-form{
        flex-direction:column;
        align-items:stretch;
    }

    .filter-group-left,
    .filter-group-right{
        width:100%;
        flex:1 1 100%;
    }

    .filter-group-right{
        justify-content:stretch;
    }

    .filter-group-right > *,
    .filter-group-left > *{
        width:100%;
    }

    .field-block{
        min-width:100%;
    }

    .btn-sky,
    .btn-soft{
        width:100%;
    }

    .kpi-grid{
        grid-template-columns:1fr;
    }

    .summary-grid{
        grid-template-columns:1fr;
    }

    .chart-box{
        height:220px;
    }

    .chart-box.sm{
        height:190px;
    }
}

/* PRINT */
@media print{
    body *{
        visibility:hidden !important;
    }

    #printProviders,
    #printProviders *{
        visibility:visible !important;
    }

    #printProviders{
        display:block !important;
        position:absolute;
        left:0;
        top:0;
        width:100%;
        background:#fff;
        color:#000;
        padding:20px;
    }

    .print-only{
        display:block !important;
    }
}
</style>

<div class="report-shell">
    <div class="report-wrap">

        <div class="topbar">
            <div class="title-wrap">
                <h2 class="page-title">Reports Dashboard</h2>
                <div class="page-subtitle">
                    CleanTech admin analytics, income reports, service insights, and provider performance overview.
                </div>
            </div>
        </div>

        <div class="filter-card">
            <form method="GET" class="filter-form">
                <div class="filter-group filter-group-left">
                    <div class="field-block">
                        <label class="filter-label">Start Date</label>
                        <input type="date" name="start" value="{{ $startStr }}" class="form-control form-control-sm form-dark">
                    </div>

                    <div class="field-block">
                        <label class="filter-label">End Date</label>
                        <input type="date" name="end" value="{{ $endStr }}" class="form-control form-control-sm form-dark">
                    </div>
                </div>

                <div class="filter-group filter-group-right">
                    <button class="btn btn-sm btn-sky" type="submit">Apply Filter</button>

                    @if(Route::has('admin.reports.export'))
                        <a href="{{ route('admin.reports.export', ['start' => $startStr, 'end' => $endStr]) }}" class="btn-soft">
                            Export CSV
                        </a>
                    @endif

                    <button type="button" class="btn-soft" onclick="window.print()">
                        Print Providers
                    </button>
                </div>
            </form>
        </div>

        <div class="kpi-grid">
            <div class="cardx">
                <div class="card-head">
                    <h5 class="card-title">Total Income</h5>
                    <span class="badgex">Paid + Completed</span>
                </div>
                <div class="card-body">
                    <div class="metric">₱{{ number_format($rangeIncome, 2) }}</div>
                    <div class="meta">Revenue within selected range.</div>
                </div>
            </div>

            <div class="cardx">
                <div class="card-head">
                    <h5 class="card-title">Estimated Loss</h5>
                    <span class="badgex">Cancelled</span>
                </div>
                <div class="card-body">
                    <div class="metric">₱{{ number_format($cancelledLoss, 2) }}</div>
                    <div class="meta">Potential loss from cancelled bookings.</div>
                </div>
            </div>

            <div class="cardx">
                <div class="card-head">
                    <h5 class="card-title">Net Report</h5>
                    <span class="badgex">Income - Loss</span>
                </div>
                <div class="card-body">
                    <div class="metric">₱{{ number_format($netReport, 2) }}</div>
                    <div class="meta">Net value based on selected records.</div>
                </div>
            </div>

            <div class="cardx">
                <div class="card-head">
                    <h5 class="card-title">Top Performer</h5>
                    <span class="badgex">Provider</span>
                </div>
                <div class="card-body">
                    <div class="metric metric-sm">
                        {{ $topPerformer?->provider_name ?? 'No data yet' }}
                    </div>
                    <div class="meta">
                        @if($topPerformer)
                            Revenue: ₱{{ number_format((float)$topPerformer->revenue, 2) }} · Completion: {{ number_format((float)$topPerformer->completion_rate, 1) }}%
                        @else
                            No provider performance data found.
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="layout">
            <div class="stack">
                <div class="panel">
                    <div class="panel-head">
                        <h4>Accepted / Finished / Cancelled</h4>
                        <span class="badgex">Last 7 days</span>
                    </div>
                    <div class="panel-body">
                        <div class="chart-box">
                            <canvas id="statusTrendChart"></canvas>
                        </div>
                    </div>
                </div>

                <div class="panel">
                    <div class="panel-head">
                        <h4>Income Trend</h4>
                        <span class="badgex">Last 6 months</span>
                    </div>
                    <div class="panel-body">
                        <div class="chart-box">
                            <canvas id="incomeChart"></canvas>
                        </div>
                    </div>
                </div>

                <div class="panel">
                    <div class="panel-head">
                        <h4>Top Provider Revenue</h4>
                        <span class="badgex">Top 5 providers</span>
                    </div>
                    <div class="panel-body">
                        <div class="chart-box sm">
                            <canvas id="topProviderChart"></canvas>
                        </div>
                    </div>
                </div>

                <div class="panel">
                    <div class="panel-head">
                        <h4>CleanTech Service Classification</h4>
                        <span class="badgex">By booking volume</span>
                    </div>
                    <div class="panel-body">
                        <div class="chart-box sm">
                            <canvas id="serviceChart"></canvas>
                        </div>
                    </div>
                </div>

                <div class="panel">
                    <div class="panel-head">
                        <h4>Latest Bookings</h4>
                        <span class="badgex">Recent activity</span>
                    </div>
                    <div class="panel-body no-pad">
                        <div class="table-wrap">
                            <table class="table-darkx">
                                <thead>
                                    <tr>
                                        <th>Reference</th>
                                        <th>Date</th>
                                        <th>Provider</th>
                                        <th>Service</th>
                                        <th>Status</th>
                                        <th class="text-end">Amount</th>
                                    </tr>
                                </thead>
                                <tbody>
                                @forelse($bookings->take(15) as $b)
                                    @php
                                        $st = strtolower((string)($b->status ?? 'unknown'));
                                        $dotClass = '';
                                        if (in_array($st, ['paid','completed'])) $dotClass = 'green';
                                        elseif (in_array($st, ['confirmed','in_progress'])) $dotClass = 'yellow';
                                        elseif ($st === 'cancelled') $dotClass = 'red';
                                    @endphp
                                    <tr>
                                        <td>{{ $b->reference_code ?? ('#'.$b->id) }}</td>
                                        <td>{{ $b->booking_date ?? '-' }}</td>
                                        <td>{{ $b->provider_name ?: 'No provider' }}</td>
                                        <td>{{ $b->service_name ?: 'No service' }}</td>
                                        <td>
                                            <span class="pill">
                                                <span class="dot {{ $dotClass }}"></span>
                                                {{ strtoupper(str_replace('_', ' ', $st)) }}
                                            </span>
                                        </td>
                                        <td class="text-end">₱{{ number_format((float)($b->price ?? 0), 2) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" style="color:var(--muted);">No bookings found in this range.</td>
                                    </tr>
                                @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="stack">
                <div class="panel">
                    <div class="panel-head">
                        <h4>Report Summary</h4>
                        <span class="badgex">
                            {{ Carbon::parse($startStr, $tz)->format('M d') }} - {{ Carbon::parse($endStr, $tz)->format('M d, Y') }}
                        </span>
                    </div>
                    <div class="panel-body">
                        <div class="summary-grid">
                            <div class="summary-item">
                                <div class="summary-label">Total Bookings</div>
                                <div class="summary-value">{{ number_format($rangeBookings) }}</div>
                                <div class="summary-sub">All bookings in selected period</div>
                            </div>

                            <div class="summary-item">
                                <div class="summary-label">Completion Rate</div>
                                <div class="summary-value">{{ number_format($completionRate, 1) }}%</div>
                                <div class="summary-sub">Finished out of all statuses</div>
                            </div>

                            <div class="summary-item">
                                <div class="summary-label">Cancel Rate</div>
                                <div class="summary-value">{{ number_format($cancelRate, 1) }}%</div>
                                <div class="summary-sub">Cancelled out of all statuses</div>
                            </div>

                            <div class="summary-item">
                                <div class="summary-label">Accepted Bookings</div>
                                <div class="summary-value">{{ number_format($acceptedCount) }}</div>
                                <div class="summary-sub">Confirmed + in progress + paid</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="panel">
                    <div class="panel-head">
                        <h4>Positives</h4>
                        <span class="badgex">System Insights</span>
                    </div>
                    <div class="panel-body">
                        <ul class="insight-list">
                            @forelse($positives as $item)
                                <li>
                                    <span class="insight-tag" style="color:#86efac;">Positive</span>
                                    <div class="insight-text">{{ $item }}</div>
                                </li>
                            @empty
                                <li>
                                    <span class="insight-tag" style="color:#86efac;">Positive</span>
                                    <div class="insight-text">No strong positive insight found for the selected range yet.</div>
                                </li>
                            @endforelse
                        </ul>
                    </div>
                </div>

                <div class="panel">
                    <div class="panel-head">
                        <h4>Negatives</h4>
                        <span class="badgex">System Insights</span>
                    </div>
                    <div class="panel-body">
                        <ul class="insight-list">
                            @forelse($negatives as $item)
                                <li>
                                    <span class="insight-tag" style="color:#fca5a5;">Negative</span>
                                    <div class="insight-text">{{ $item }}</div>
                                </li>
                            @empty
                                <li>
                                    <span class="insight-tag" style="color:#fca5a5;">Negative</span>
                                    <div class="insight-text">No strong negative insight found for the selected range yet.</div>
                                </li>
                            @endforelse
                        </ul>
                    </div>
                </div>

                <div class="panel">
                    <div class="panel-head">
                        <h4>Provider Performance</h4>
                        <span class="badgex">Printable list</span>
                    </div>
                    <div class="panel-body no-pad">
                        <div class="table-wrap">
                            <table class="table-darkx">
                                <thead>
                                    <tr>
                                        <th>Provider</th>
                                        <th>Total</th>
                                        <th>Success</th>
                                        <th>Cancelled</th>
                                        <th>Rate</th>
                                        <th class="text-end">Revenue</th>
                                    </tr>
                                </thead>
                                <tbody>
                                @forelse($providerPerformance as $p)
                                    <tr>
                                        <td>{{ $p->provider_name }}</td>
                                        <td>{{ (int)$p->total_bookings }}</td>
                                        <td>{{ (int)$p->success_count }}</td>
                                        <td>{{ (int)$p->cancelled_count }}</td>
                                        <td>{{ number_format((float)$p->completion_rate, 1) }}%</td>
                                        <td class="text-end">₱{{ number_format((float)$p->revenue, 2) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" style="color:var(--muted);">No provider performance data found.</td>
                                    </tr>
                                @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="panel">
                    <div class="panel-head">
                        <h4>Service Classification Details</h4>
                        <span class="badgex">CleanTech report</span>
                    </div>
                    <div class="panel-body no-pad">
                        <div class="table-wrap">
                            <table class="table-darkx">
                                <thead>
                                    <tr>
                                        <th>Service</th>
                                        <th>Total Bookings</th>
                                        <th>Cancelled</th>
                                        <th class="text-end">Revenue</th>
                                    </tr>
                                </thead>
                                <tbody>
                                @forelse($serviceClassification as $s)
                                    <tr>
                                        <td>{{ $s->service_name }}</td>
                                        <td>{{ (int)$s->total_bookings }}</td>
                                        <td>{{ (int)$s->cancelled_count }}</td>
                                        <td class="text-end">₱{{ number_format((float)$s->revenue, 2) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" style="color:var(--muted);">No service classification data found.</td>
                                    </tr>
                                @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- PRINT AREA --}}
        <div id="printProviders" class="print-only">
            <h2>CleanTech Provider Report</h2>
            <p>Date Range: {{ $startStr }} to {{ $endStr }}</p>

            <table style="width:100%; border-collapse:collapse; margin-top:18px;" border="1" cellpadding="8">
                <thead>
                    <tr>
                        <th>Provider</th>
                        <th>Total Bookings</th>
                        <th>Successful</th>
                        <th>Cancelled</th>
                        <th>Completion Rate</th>
                        <th>Revenue</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($providerPerformance as $p)
                    <tr>
                        <td>{{ $p->provider_name }}</td>
                        <td>{{ (int)$p->total_bookings }}</td>
                        <td>{{ (int)$p->success_count }}</td>
                        <td>{{ (int)$p->cancelled_count }}</td>
                        <td>{{ number_format((float)$p->completion_rate, 1) }}%</td>
                        <td>₱{{ number_format((float)$p->revenue, 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6">No provider data found.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
(function () {
    Chart.defaults.color = 'rgba(255,255,255,.72)';
    Chart.defaults.borderColor = 'rgba(255,255,255,.08)';

    const labels7 = @json($last7Labels ?? []);
    const confirmed = @json($dailyConfirmed ?? []);
    const progress = @json($dailyProgress ?? []);
    const paid = @json($dailyPaid ?? []);
    const completed = @json($dailyCompleted ?? []);
    const cancelled = @json($dailyCancelled ?? []);

    const accepted = confirmed.map((v, i) => {
        return Number(v || 0) + Number(progress[i] || 0) + Number(paid[i] || 0);
    });

    const finished = completed.map(v => Number(v || 0));
    const cancelledOnly = cancelled.map(v => Number(v || 0));

    const monthLabels = @json($monthLabels ?? []);
    const monthRevenue = @json($monthRevenue ?? []);

    const providerLabels = @json($providerLabels ?? []);
    const providerRevenue = @json($providerRevenue ?? []);

    const serviceLabels = @json($serviceLabels ?? []);
    const serviceBookings = @json($serviceBookings ?? []);

    new Chart(document.getElementById('statusTrendChart'), {
        type: 'bar',
        data: {
            labels: labels7,
            datasets: [
                {
                    label: 'Accepted',
                    data: accepted,
                    backgroundColor: 'rgba(56,189,248,.85)',
                    borderRadius: 7,
                    maxBarThickness: 34
                },
                {
                    label: 'Finished',
                    data: finished,
                    backgroundColor: 'rgba(34,197,94,.85)',
                    borderRadius: 7,
                    maxBarThickness: 34
                },
                {
                    label: 'Cancelled',
                    data: cancelledOnly,
                    backgroundColor: 'rgba(239,68,68,.85)',
                    borderRadius: 7,
                    maxBarThickness: 34
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        boxWidth: 10,
                        usePointStyle: true,
                        pointStyle: 'circle'
                    }
                }
            },
            scales: {
                x: { grid: { display: false } },
                y: { beginAtZero: true, ticks: { precision: 0 } }
            }
        }
    });

    new Chart(document.getElementById('incomeChart'), {
        type: 'line',
        data: {
            labels: monthLabels,
            datasets: [{
                label: 'Income',
                data: monthRevenue,
                fill: true,
                tension: .35,
                borderWidth: 2,
                pointRadius: 3,
                pointHoverRadius: 5,
                backgroundColor: 'rgba(56,189,248,.12)',
                borderColor: 'rgba(56,189,248,.95)'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                x: { grid: { display: false } },
                y: { beginAtZero: true }
            }
        }
    });

    new Chart(document.getElementById('topProviderChart'), {
        type: 'bar',
        data: {
            labels: providerLabels,
            datasets: [{
                label: 'Revenue',
                data: providerRevenue,
                backgroundColor: 'rgba(139,92,246,.85)',
                borderRadius: 7,
                maxBarThickness: 26
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            indexAxis: 'y',
            plugins: {
                legend: { display: false }
            },
            scales: {
                x: { beginAtZero: true },
                y: { grid: { display: false } }
            }
        }
    });

    new Chart(document.getElementById('serviceChart'), {
        type: 'bar',
        data: {
            labels: serviceLabels,
            datasets: [{
                label: 'Bookings',
                data: serviceBookings,
                backgroundColor: 'rgba(245,158,11,.85)',
                borderRadius: 7,
                maxBarThickness: 34
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                x: { grid: { display: false } },
                y: { beginAtZero: true, ticks: { precision: 0 } }
            }
        }
    });
})();
</script>
@endsection