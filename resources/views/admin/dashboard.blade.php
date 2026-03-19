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
    $confirmedToday = (int) ($confirmedToday ?? 0);
    $completedToday = (int) ($completedToday ?? 0);
    $statusCounts = $statusCounts ?? [
        'confirmed' => 0,
        'in_progress' => 0,
        'paid' => 0,
        'completed' => 0,
        'cancelled' => 0,
    ];

    $dailyLabels = $dailyLabels ?? [];
    $dailyConfirmed = $dailyConfirmed ?? [];
    $dailyCompleted = $dailyCompleted ?? [];
    $trendLabels = $trendLabels ?? [];
    $trendRevenue = $trendRevenue ?? [];

    $statusCards = [
        ['key' => 'confirmed', 'label' => 'Confirmed', 'color' => '#38bdf8'],
        ['key' => 'in_progress', 'label' => 'In Progress', 'color' => '#f59e0b'],
        ['key' => 'paid', 'label' => 'Paid', 'color' => '#a78bfa'],
        ['key' => 'completed', 'label' => 'Completed', 'color' => '#22c55e'],
        ['key' => 'cancelled', 'label' => 'Cancelled', 'color' => '#ef4444'],
    ];

    $statusLabels = collect($statusCards)->pluck('label')->values();
    $statusValues = collect($statusCards)->map(fn ($item) => (int) ($statusCounts[$item['key']] ?? 0))->values();
    $statusColors = collect($statusCards)->pluck('color')->values();
    $statusTotal = $statusValues->sum();

    $weekConfirmed = collect($dailyConfirmed)->sum();
    $weekCompleted = collect($dailyCompleted)->sum();
    $revenue30Days = collect($trendRevenue)->sum();
@endphp

<style>
.admin-dashboard {
    padding: 18px 20px 28px;
    color: #f8fafc;
}

.dashboard-head {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 14px;
    margin-bottom: 16px;
    flex-wrap: wrap;
}

.dashboard-title {
    margin: 0;
    font-size: 1.5rem;
    font-weight: 900;
    color: #f8fafc;
}

.dashboard-date {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 14px;
    border-radius: 999px;
    border: 1px solid rgba(148, 163, 184, 0.18);
    background: rgba(15, 23, 42, 0.92);
    color: #cbd5e1;
    font-size: 0.88rem;
    font-weight: 800;
}

.metric-grid {
    display: grid;
    grid-template-columns: repeat(5, minmax(0, 1fr));
    gap: 14px;
    margin-bottom: 16px;
}

.metric-card {
    padding: 16px 18px;
    border-radius: 18px;
    border: 1px solid rgba(148, 163, 184, 0.12);
    background: linear-gradient(180deg, rgba(15, 23, 42, 0.96), rgba(15, 23, 42, 0.86));
    box-shadow: 0 16px 36px rgba(2, 6, 23, 0.22);
}

.metric-label {
    display: block;
    margin-bottom: 8px;
    color: #94a3b8;
    font-size: 0.76rem;
    font-weight: 900;
    letter-spacing: 0.1em;
    text-transform: uppercase;
}

.metric-value {
    margin: 0;
    color: #f8fafc;
    font-size: 1.6rem;
    font-weight: 900;
    line-height: 1.1;
}

.metric-value.accent {
    color: #38bdf8;
}

.metric-value.success {
    color: #4ade80;
}

.metric-value.warning {
    color: #fbbf24;
}

.panel-grid {
    display: grid;
    grid-template-columns: minmax(0, 1.05fr) minmax(0, 1.35fr);
    gap: 16px;
}

.panel {
    border-radius: 22px;
    border: 1px solid rgba(148, 163, 184, 0.12);
    background: linear-gradient(180deg, rgba(15, 23, 42, 0.98), rgba(15, 23, 42, 0.9));
    padding: 18px;
    box-shadow: 0 18px 40px rgba(2, 6, 23, 0.24);
}

.panel-head {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 12px;
    margin-bottom: 14px;
    flex-wrap: wrap;
}

.panel-title {
    margin: 0;
    color: #f8fafc;
    font-size: 1.02rem;
    font-weight: 900;
}

.panel-chip {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 8px 12px;
    border-radius: 999px;
    border: 1px solid rgba(56, 189, 248, 0.22);
    background: rgba(14, 116, 144, 0.12);
    color: #bae6fd;
    font-size: 0.8rem;
    font-weight: 800;
}

.status-layout {
    display: grid;
    grid-template-columns: minmax(0, 240px) minmax(0, 1fr);
    gap: 18px;
    align-items: center;
}

.status-chart-wrap {
    position: relative;
    min-height: 250px;
}

.status-center {
    position: absolute;
    inset: 50% auto auto 50%;
    transform: translate(-50%, -50%);
    text-align: center;
    pointer-events: none;
}

.status-center strong {
    display: block;
    color: #f8fafc;
    font-size: 1.8rem;
    font-weight: 900;
    line-height: 1;
}

.status-center span {
    color: #94a3b8;
    font-size: 0.8rem;
    font-weight: 800;
}

.status-list {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 12px;
}

.status-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    padding: 12px 14px;
    border-radius: 16px;
    border: 1px solid rgba(148, 163, 184, 0.1);
    background: rgba(255, 255, 255, 0.02);
}

.status-item-left {
    display: flex;
    align-items: center;
    gap: 10px;
    min-width: 0;
}

.status-dot {
    width: 11px;
    height: 11px;
    border-radius: 999px;
    flex: 0 0 auto;
}

.status-name {
    color: #cbd5e1;
    font-size: 0.88rem;
    font-weight: 800;
}

.status-count {
    color: #f8fafc;
    font-size: 1rem;
    font-weight: 900;
}

.activity-strip {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 12px;
    margin-top: 14px;
}

.activity-pill {
    padding: 12px 14px;
    border-radius: 16px;
    border: 1px solid rgba(148, 163, 184, 0.1);
    background: rgba(255, 255, 255, 0.02);
}

.activity-pill label {
    display: block;
    margin-bottom: 6px;
    color: #94a3b8;
    font-size: 0.74rem;
    font-weight: 900;
    text-transform: uppercase;
    letter-spacing: 0.08em;
}

.activity-pill strong {
    color: #f8fafc;
    font-size: 1.3rem;
    font-weight: 900;
}

.revenue-panel {
    margin-top: 16px;
}

.chart-wrap {
    position: relative;
    min-height: 300px;
}

.chart-wrap canvas,
.status-chart-wrap canvas {
    width: 100% !important;
    height: 100% !important;
}

@media (max-width: 1280px) {
    .metric-grid {
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }
}

@media (max-width: 1080px) {
    .panel-grid {
        grid-template-columns: 1fr;
    }

    .status-layout {
        grid-template-columns: 1fr;
    }

    .status-chart-wrap {
        min-height: 220px;
    }
}

@media (max-width: 760px) {
    .admin-dashboard {
        padding: 16px 14px 24px;
    }

    .metric-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 12px;
    }

    .status-list,
    .activity-strip {
        grid-template-columns: 1fr;
    }

    .chart-wrap {
        min-height: 260px;
    }
}

@media (max-width: 520px) {
    .metric-grid {
        grid-template-columns: 1fr;
    }

    .metric-card,
    .panel {
        padding: 15px;
    }

    .dashboard-title {
        font-size: 1.3rem;
    }
}
</style>

<div class="admin-dashboard">
    <div class="dashboard-head">
        <div>
            <h1 class="dashboard-title">Dashboard</h1>
        </div>
        <div class="dashboard-date">{{ $now->format('M d, Y') }}</div>
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
            <p class="metric-value accent">PHP {{ number_format($monthlyRevenue, 2) }}</p>
        </div>
        <div class="metric-card">
            <span class="metric-label">Today</span>
            <p class="metric-value success">PHP {{ number_format($dailyIncome, 2) }}</p>
        </div>
    </div>

    <div class="panel-grid">
        <section class="panel">
            <div class="panel-head">
                <h2 class="panel-title">Booking Status</h2>
                <div class="panel-chip">{{ number_format($statusTotal) }} total</div>
            </div>

            <div class="status-layout">
                <div class="status-chart-wrap">
                    <canvas id="statusChart"></canvas>
                    <div class="status-center">
                        <strong>{{ number_format($statusTotal) }}</strong>
                        <span>Bookings</span>
                    </div>
                </div>

                <div class="status-list">
                    @foreach ($statusCards as $statusCard)
                        <div class="status-item">
                            <div class="status-item-left">
                                <span class="status-dot" style="background: {{ $statusCard['color'] }}"></span>
                                <span class="status-name">{{ $statusCard['label'] }}</span>
                            </div>
                            <span class="status-count">{{ number_format((int) ($statusCounts[$statusCard['key']] ?? 0)) }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <section class="panel">
            <div class="panel-head">
                <h2 class="panel-title">Weekly Activity</h2>
                <div class="panel-chip">7 days</div>
            </div>

            <div class="chart-wrap">
                <canvas id="activityChart"></canvas>
            </div>

            <div class="activity-strip">
                <div class="activity-pill">
                    <label>Confirmed Today</label>
                    <strong>{{ number_format($confirmedToday) }}</strong>
                </div>
                <div class="activity-pill">
                    <label>Completed Today</label>
                    <strong>{{ number_format($completedToday) }}</strong>
                </div>
                <div class="activity-pill">
                    <label>7-Day Total</label>
                    <strong>{{ number_format($weekConfirmed + $weekCompleted) }}</strong>
                </div>
            </div>
        </section>
    </div>

    <section class="panel revenue-panel">
        <div class="panel-head">
            <h2 class="panel-title">Revenue Trend</h2>
            <div class="panel-chip">30 days: PHP {{ number_format($revenue30Days, 2) }}</div>
        </div>

        <div class="chart-wrap">
            <canvas id="revenueChart"></canvas>
        </div>
    </section>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const axisColor = 'rgba(148, 163, 184, 0.55)';
const gridColor = 'rgba(148, 163, 184, 0.10)';

new Chart(document.getElementById('statusChart'), {
    type: 'doughnut',
    data: {
        labels: @json($statusLabels),
        datasets: [{
            data: @json($statusValues),
            backgroundColor: @json($statusColors),
            borderWidth: 0,
            hoverOffset: 6
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        cutout: '72%',
        plugins: {
            legend: {
                display: false
            }
        }
    }
});

new Chart(document.getElementById('activityChart'), {
    type: 'bar',
    data: {
        labels: @json($dailyLabels),
        datasets: [
            {
                label: 'Confirmed',
                data: @json($dailyConfirmed),
                backgroundColor: '#38bdf8',
                borderRadius: 8,
                maxBarThickness: 34
            },
            {
                label: 'Completed',
                data: @json($dailyCompleted),
                backgroundColor: '#22c55e',
                borderRadius: 8,
                maxBarThickness: 34
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
                    font: {
                        weight: '700'
                    }
                }
            }
        },
        scales: {
            x: {
                ticks: {
                    color: axisColor,
                    font: {
                        weight: '700'
                    }
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
                    font: {
                        weight: '700'
                    }
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
            label: 'Revenue',
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
                    font: {
                        weight: '700'
                    }
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
                    font: {
                        weight: '700'
                    }
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
