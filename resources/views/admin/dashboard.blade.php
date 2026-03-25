@extends('admin.layouts.app')

@section('title', 'Admin Dashboard')

@section('content')
@php
    use Carbon\Carbon;

    $tz = config('app.timezone') ?? 'Asia/Manila';
    $now = Carbon::now($tz);

    $totalCustomers = (int) ($totalCustomers ?? ($stats['customers'] ?? 0));
    $totalProviders = (int) ($totalProviders ?? ($stats['providers'] ?? 0));
    $totalBookings = (int) ($totalBookings ?? ($stats['bookings'] ?? 0));
    $monthlyRevenue = (float) ($monthlyRevenue ?? 0);
    $dailyIncome = (float) ($dailyIncome ?? 0);
    $bookingsToday = (int) ($bookingsToday ?? 0);
    $completedToday = (int) ($completedToday ?? 0);
    $statusCounts = $statusCounts ?? [
        'confirmed' => 0,
        'in_progress' => 0,
        'paid' => 0,
        'completed' => 0,
        'cancelled' => 0,
    ];

    $dailyLabels = $dailyLabels ?? [];
    $dailyBooked = $dailyBooked ?? [];
    $dailyCompleted = $dailyCompleted ?? [];
    $trendLabels = $trendLabels ?? [];
    $trendRevenue = $trendRevenue ?? [];

    $activeCards = [
        ['label' => 'Confirmed', 'count' => (int) ($statusCounts['confirmed'] ?? 0), 'class' => 'blue'],
        ['label' => 'In Progress', 'count' => (int) ($statusCounts['in_progress'] ?? 0), 'class' => 'amber'],
        ['label' => 'Paid', 'count' => (int) ($statusCounts['paid'] ?? 0), 'class' => 'violet'],
    ];

    $closedCards = [
        ['label' => 'Completed', 'count' => (int) ($statusCounts['completed'] ?? 0), 'class' => 'green'],
        ['label' => 'Cancelled', 'count' => (int) ($statusCounts['cancelled'] ?? 0), 'class' => 'red'],
    ];

    $activeNow = collect($activeCards)->sum('count');
    $closedNow = collect($closedCards)->sum('count');
    $weekBooked = collect($dailyBooked)->sum();
    $weekCompleted = collect($dailyCompleted)->sum();
    $revenue30Days = collect($trendRevenue)->sum();
@endphp

<style>
.admin-dashboard {
    padding: 18px 20px 26px;
    color: #f8fafc;
}

.dashboard-bar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    margin-bottom: 16px;
    flex-wrap: wrap;
}

.dashboard-title {
    margin: 0;
    font-size: 1.42rem;
    font-weight: 900;
    color: #f8fafc;
}

.dashboard-pill {
    display: inline-flex;
    align-items: center;
    padding: 9px 14px;
    border-radius: 999px;
    border: 1px solid rgba(148, 163, 184, 0.14);
    background: rgba(15, 23, 42, 0.9);
    color: #cbd5e1;
    font-size: 0.86rem;
    font-weight: 800;
}

.metric-grid {
    display: grid;
    grid-template-columns: repeat(5, minmax(0, 1fr));
    gap: 12px;
    margin-bottom: 14px;
}

.metric-card {
    border-radius: 16px;
    border: 1px solid rgba(148, 163, 184, 0.12);
    background: rgba(15, 23, 42, 0.92);
    padding: 14px 16px;
}

.metric-label {
    display: block;
    margin-bottom: 6px;
    color: #94a3b8;
    font-size: 0.74rem;
    font-weight: 900;
    letter-spacing: 0.1em;
    text-transform: uppercase;
}

.metric-value {
    margin: 0;
    color: #f8fafc;
    font-size: 1.46rem;
    font-weight: 900;
    line-height: 1.1;
}

.metric-value.blue { color: #38bdf8; }
.metric-value.green { color: #4ade80; }

.dashboard-grid {
    display: grid;
    grid-template-columns: minmax(0, 0.9fr) minmax(0, 1.4fr);
    gap: 14px;
    align-items: start;
}

.dashboard-grid > .panel {
    align-self: stretch;
    height: 100%;
}

.panel-queue {
    display: grid;
    grid-template-rows: auto 1fr;
    min-height: 0;
}

.panel {
    border-radius: 20px;
    border: 1px solid rgba(148, 163, 184, 0.12);
    background: rgba(15, 23, 42, 0.95);
    padding: 16px;
}

.panel + .panel {
    margin-top: 0;
}

.panel-title-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    margin-bottom: 12px;
    flex-wrap: wrap;
}

.panel-title {
    margin: 0;
    color: #f8fafc;
    font-size: 1rem;
    font-weight: 900;
}

.mini-pill {
    display: inline-flex;
    align-items: center;
    padding: 7px 11px;
    border-radius: 999px;
    background: rgba(56, 189, 248, 0.12);
    border: 1px solid rgba(56, 189, 248, 0.18);
    color: #bae6fd;
    font-size: 0.78rem;
    font-weight: 800;
}

.queue-wrap {
    display: grid;
    grid-template-rows: repeat(2, minmax(0, 1fr));
    gap: 12px;
    height: 100%;
    min-height: 0;
}

.queue-block {
    border-radius: 16px;
    border: 1px solid rgba(148, 163, 184, 0.1);
    background: rgba(255, 255, 255, 0.02);
    padding: 14px;
    display: grid;
    grid-template-rows: auto 1fr;
    gap: 10px;
    height: 100%;
    min-height: 0;
}

.queue-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
    margin-bottom: 2px;
}

.queue-label {
    color: #cbd5e1;
    font-size: 0.84rem;
    font-weight: 900;
    text-transform: uppercase;
    letter-spacing: 0.08em;
}

.queue-total {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 30px;
    height: 30px;
    padding: 0 8px;
    border-radius: 999px;
    border: 1px solid rgba(56, 189, 248, 0.16);
    background: rgba(56, 189, 248, 0.08);
    color: #f8fafc;
    font-size: 0.8rem;
    font-weight: 900;
    line-height: 1;
}

.status-grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(110px, 1fr));
    grid-auto-rows: 1fr;
    gap: 10px;
    height: 100%;
    align-items: stretch;
}

.status-grid.two {
    grid-template-columns: repeat(2, minmax(140px, 1fr));
}

.status-card {
    border-radius: 14px;
    border: 1px solid rgba(148, 163, 184, 0.08);
    background: rgba(15, 23, 42, 0.88);
    padding: 12px;
    min-height: 0;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    height: 100%;
}

.status-card label {
    display: block;
    margin-bottom: 8px;
    color: #94a3b8;
    font-size: 0.76rem;
    font-weight: 800;
    line-height: 1.35;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.status-card strong {
    display: block;
    font-size: 1.3rem;
    font-weight: 900;
}

.status-card.blue strong { color: #38bdf8; }
.status-card.amber strong { color: #f59e0b; }
.status-card.violet strong { color: #a78bfa; }
.status-card.green strong { color: #4ade80; }
.status-card.red strong { color: #f87171; }

.chart-wrap {
    position: relative;
    min-height: 230px;
}

.chart-wrap.tall {
    min-height: 250px;
}

.chart-wrap canvas {
    width: 100% !important;
    height: 100% !important;
}

.activity-metrics {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 10px;
    margin-top: 12px;
}

.activity-card {
    border-radius: 14px;
    border: 1px solid rgba(148, 163, 184, 0.08);
    background: rgba(255, 255, 255, 0.02);
    padding: 11px 12px;
}

.activity-card label {
    display: block;
    margin-bottom: 6px;
    color: #94a3b8;
    font-size: 0.73rem;
    font-weight: 900;
    text-transform: uppercase;
    letter-spacing: 0.08em;
}

.activity-card strong {
    color: #f8fafc;
    font-size: 1.2rem;
    font-weight: 900;
}

.revenue-panel {
    margin-top: 14px;
    padding-bottom: 12px;
}

.revenue-panel .chart-wrap.tall {
    min-height: 150px;
    height: 150px;
}

@media (max-width: 1220px) {
    .metric-grid {
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }
}

@media (max-width: 1024px) {
    .dashboard-grid {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 720px) {
    .admin-dashboard {
        padding: 16px 14px 24px;
    }

    .queue-wrap,
    .metric-grid,
    .status-grid,
    .status-grid.two,
    .activity-metrics {
        grid-template-columns: 1fr;
    }

    .chart-wrap,
    .chart-wrap.tall {
        min-height: 220px;
    }

    .revenue-panel .chart-wrap.tall {
        min-height: 165px;
        height: 165px;
    }
}

@media (max-width: 520px) {
    .metric-grid {
        grid-template-columns: 1fr;
    }

    .metric-card,
    .panel,
    .status-card,
    .activity-card {
        padding: 12px;
    }

    .revenue-panel .chart-wrap.tall {
        min-height: 150px;
        height: 150px;
    }
}
</style>

<div class="admin-dashboard">
    <div class="dashboard-bar">
        <h1 class="dashboard-title">Dashboard</h1>
        <div class="dashboard-pill">{{ $now->format('M d, Y') }}</div>
    </div>

    <div class="metric-grid">
        <div class="metric-card">
            <span class="metric-label">Customers</span>
            <p class="metric-value">{{ number_format($totalCustomers) }}</p>
        </div>
        <div class="metric-card">
            <span class="metric-label">Providers</span>
            <p class="metric-value">{{ number_format($totalProviders) }}</p>
        </div>
        <div class="metric-card">
            <span class="metric-label">Bookings</span>
            <p class="metric-value">{{ number_format($totalBookings) }}</p>
        </div>
        <div class="metric-card">
            <span class="metric-label">This Month</span>
            <p class="metric-value blue">PHP {{ number_format($monthlyRevenue, 2) }}</p>
        </div>
        <div class="metric-card">
            <span class="metric-label">Today</span>
            <p class="metric-value green">PHP {{ number_format($dailyIncome, 2) }}</p>
        </div>
    </div>

    <div class="dashboard-grid">
        <section class="panel panel-queue">
            <div class="panel-title-row">
                <h2 class="panel-title">Queue Now</h2>
                <div class="mini-pill">{{ number_format($activeNow + $closedNow) }} bookings</div>
            </div>

            <div class="queue-wrap">
                <div class="queue-block">
                    <div class="queue-head">
                        <span class="queue-label">Active</span>
                        <span class="queue-total">{{ number_format($activeNow) }}</span>
                    </div>
                    <div class="status-grid">
                        @foreach ($activeCards as $item)
                            <div class="status-card {{ $item['class'] }}">
                                <label>{{ $item['label'] }}</label>
                                <strong>{{ number_format($item['count']) }}</strong>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="queue-block">
                    <div class="queue-head">
                        <span class="queue-label">Closed</span>
                        <span class="queue-total">{{ number_format($closedNow) }}</span>
                    </div>
                    <div class="status-grid two">
                        @foreach ($closedCards as $item)
                            <div class="status-card {{ $item['class'] }}">
                                <label>{{ $item['label'] }}</label>
                                <strong>{{ number_format($item['count']) }}</strong>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>

        <section class="panel">
            <div class="panel-title-row">
                <h2 class="panel-title">Weekly Activity</h2>
                <div class="mini-pill">7 days</div>
            </div>

            <div class="chart-wrap">
                <canvas id="activityChart"></canvas>
            </div>

            <div class="activity-metrics">
                <div class="activity-card">
                    <label>Booked Today</label>
                    <strong>{{ number_format($bookingsToday) }}</strong>
                </div>
                <div class="activity-card">
                    <label>Completed Today</label>
                    <strong>{{ number_format($completedToday) }}</strong>
                </div>
                <div class="activity-card">
                    <label>Week Total</label>
                    <strong>{{ number_format($weekBooked + $weekCompleted) }}</strong>
                </div>
            </div>
        </section>
    </div>

    <section class="panel revenue-panel">
        <div class="panel-title-row">
            <h2 class="panel-title">Revenue</h2>
            <div class="mini-pill">30 days: PHP {{ number_format($revenue30Days, 2) }}</div>
        </div>

        <div class="chart-wrap tall">
            <canvas id="revenueChart"></canvas>
        </div>
    </section>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const axisColor = 'rgba(148, 163, 184, 0.58)';
const gridColor = 'rgba(148, 163, 184, 0.10)';

new Chart(document.getElementById('activityChart'), {
    type: 'bar',
    data: {
        labels: @json($dailyLabels),
        datasets: [
            {
                label: 'Booked',
                data: @json($dailyBooked),
                backgroundColor: '#38bdf8',
                borderRadius: 8,
                maxBarThickness: 30
            },
            {
                label: 'Completed',
                data: @json($dailyCompleted),
                backgroundColor: '#22c55e',
                borderRadius: 8,
                maxBarThickness: 30
            }
        ]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                labels: {
                    color: '#cbd5e1',
                    usePointStyle: true,
                    pointStyle: 'circle',
                    boxWidth: 8,
                    font: { weight: '700' }
                }
            }
        },
        scales: {
            x: {
                ticks: {
                    color: axisColor,
                    font: { weight: '700' }
                },
                grid: {
                    display: false
                }
            },
            y: {
                beginAtZero: true,
                ticks: {
                    precision: 0,
                    color: axisColor,
                    font: { weight: '700' }
                },
                grid: {
                    color: gridColor
                }
            }
        }
    }
});

new Chart(document.getElementById('revenueChart'), {
    type: 'line',
    data: {
        labels: @json($trendLabels),
        datasets: [{
            data: @json($trendRevenue),
            borderColor: '#60a5fa',
            backgroundColor: 'rgba(56, 189, 248, 0.16)',
            fill: true,
            tension: 0.32,
            pointRadius: 2.5,
            pointHoverRadius: 4,
            borderWidth: 2
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: false
            }
        },
        scales: {
            x: {
                ticks: {
                    color: axisColor,
                    maxTicksLimit: 8,
                    font: { weight: '700' }
                },
                grid: {
                    display: false
                }
            },
            y: {
                beginAtZero: true,
                ticks: {
                    color: axisColor,
                    callback: (value) => 'PHP ' + Number(value).toLocaleString(),
                    font: { weight: '700' }
                },
                grid: {
                    color: gridColor
                }
            }
        }
    }
});
</script>
@endsection
