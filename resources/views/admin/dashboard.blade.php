@extends('admin.layouts.app')

@section('title', 'Admin Dashboard')

@section('content')
@php
    use Carbon\Carbon;

    $tz  = config('app.timezone') ?? 'Asia/Manila';
    $now = Carbon::now($tz);
    $today = $now->toDateString();

    // KPIs (safe fallbacks)
    $totalCustomers = (int)($totalCustomers ?? ($stats['customers'] ?? 0));
    $totalProviders = (int)($totalProviders ?? ($stats['providers'] ?? 0));
    $totalBookings  = (int)($totalBookings  ?? ($stats['bookings']  ?? 0));
    $monthlyRevenue = (float)($monthlyRevenue ?? 0);
    $dailyIncome    = (float)($dailyIncome ?? 0);
    $confirmedToday = (int)($confirmedToday ?? 0);
    $completedToday = (int)($completedToday ?? 0);
    $statusCounts   = $statusCounts ?? [
        'confirmed' => 0,
        'in_progress' => 0,
        'paid' => 0,
        'completed' => 0,
        'cancelled' => 0,
    ];

    // Chart arrays (safe)
    $dailyLabels     = $dailyLabels ?? [];
    $dailyConfirmed  = $dailyConfirmed ?? [];
    $dailyCompleted  = $dailyCompleted ?? [];

    $trendLabels     = $trendLabels ?? [];
    $trendRevenue    = $trendRevenue ?? [];

    // Booking trend summaries
    $confirmedTotal = collect($dailyConfirmed)->sum();
    $completedTotal = collect($dailyCompleted)->sum();
    $trendDays      = count($dailyLabels);

    $latestConfirmed = $trendDays > 0 ? (int)($dailyConfirmed[$trendDays - 1] ?? 0) : 0;
    $latestCompleted = $trendDays > 0 ? (int)($dailyCompleted[$trendDays - 1] ?? 0) : 0;

    $avgConfirmed = $trendDays > 0 ? round($confirmedTotal / max($trendDays, 1), 1) : 0;
    $avgCompleted = $trendDays > 0 ? round($completedTotal / max($trendDays, 1), 1) : 0;
@endphp

<style>
:root{
    --bg:#0f172a;
    --card:#111827;
    --border:rgba(255,255,255,.08);
    --text:#f1f5f9;
    --muted:#94a3b8;
    --accent:#3b82f6;
    --success:#22c55e;
    --warning:#f59e0b;
    --danger:#ef4444;
}
.admin-wrap{ padding:20px; }

.header{
    display:flex;
    justify-content:space-between;
    align-items:center;
    margin-bottom:18px;
    gap:12px;
    flex-wrap:wrap;
}
.header h2{
    margin:0;
    font-size:1.35rem;
    font-weight:900;
    color:var(--text);
}
.header .meta{
    color:var(--muted);
    font-weight:700;
    font-size:.9rem;
}

/* KPI */
.kpi-row{
    display:grid;
    grid-template-columns: repeat(5,1fr);
    gap:14px;
    margin-bottom:18px;
}
.kpi{
    background: linear-gradient(180deg, rgba(255,255,255,.04), rgba(255,255,255,.02));
    border:1px solid var(--border);
    border-radius:14px;
    padding:16px 16px 14px;
}
.kpi h4{
    margin:0;
    font-size:.82rem;
    color:var(--muted);
    font-weight:800;
}
.kpi .value{
    font-size:1.35rem;
    font-weight:900;
    margin-top:6px;
    color:var(--text);
}

/* Layout */
.main-grid{
    display:grid;
    grid-template-columns: 2fr 1fr;
    gap:16px;
}
.card{
    background: rgba(17,24,39,.92);
    border:1px solid var(--border);
    border-radius:16px;
    padding:18px;
}
.card h4{
    margin:0 0 12px;
    color:var(--text);
    font-weight:900;
    font-size:1rem;
}

/* Tabs */
.tabs{
    display:flex;
    gap:10px;
    margin-bottom:12px;
    flex-wrap:wrap;
}
.tab-btn{
    padding:8px 12px;
    border-radius:10px;
    border:1px solid var(--border);
    background:transparent;
    color:var(--text);
    cursor:pointer;
    font-size:.82rem;
    font-weight:800;
}
.tab-btn.active{
    background:var(--accent);
    border-color:var(--accent);
}
canvas{
    width:100%!important;
    height:300px!important;
}

/* Trend summary */
.trend-list{
    display:flex;
    flex-direction:column;
    gap:12px;
}
.status-grid{
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:10px;
}
.trend-item{
    border:1px solid var(--border);
    border-radius:14px;
    padding:14px;
    background:rgba(255,255,255,.02);
}
.trend-label{
    color:var(--muted);
    font-size:.82rem;
    font-weight:800;
    margin-bottom:6px;
}
.trend-value{
    color:var(--text);
    font-size:1.25rem;
    font-weight:900;
    line-height:1.2;
}
.trend-sub{
    margin-top:4px;
    color:var(--muted);
    font-size:.82rem;
    font-weight:700;
}
.trend-mini{
    margin-top:18px;
    padding-top:14px;
    border-top:1px solid var(--border);
}
.trend-mini h5{
    margin:0 0 10px;
    color:var(--text);
    font-size:.9rem;
    font-weight:900;
}
.trend-note{
    color:var(--muted);
    font-size:.83rem;
    font-weight:700;
    line-height:1.7;
}
.badge-up,
.badge-done{
    display:inline-flex;
    align-items:center;
    padding:4px 8px;
    border-radius:999px;
    font-size:.72rem;
    font-weight:900;
}
.badge-up{
    background:rgba(59,130,246,.15);
    color:#93c5fd;
}
.badge-done{
    background:rgba(34,197,94,.15);
    color:#86efac;
}

/* Responsive */
@media(max-width: 992px){
    .kpi-row{ grid-template-columns:1fr 1fr; }
    .main-grid{ grid-template-columns:1fr; }
    canvas{ height:260px!important; }
}
@media(max-width: 576px){
    .kpi-row{ grid-template-columns:1fr; }
    .status-grid{ grid-template-columns:1fr; }
}
</style>

<div class="admin-wrap">

    <div class="header">
        <div>
            <h2>Dashboard Overview</h2>
            <div class="meta">{{ $today }} • {{ $tz }}</div>
        </div>
        <button type="button" class="tab-btn" onclick="location.reload()" title="Refresh">⟳ Refresh</button>
    </div>

    {{-- KPI ROW --}}
    <div class="kpi-row">
        <div class="kpi">
            <h4>Customers</h4>
            <div class="value">{{ number_format($totalCustomers) }}</div>
        </div>
        <div class="kpi">
            <h4>Providers</h4>
            <div class="value">{{ number_format($totalProviders) }}</div>
        </div>
        <div class="kpi">
            <h4>Bookings</h4>
            <div class="value">{{ number_format($totalBookings) }}</div>
        </div>
        <div class="kpi">
            <h4>Monthly Revenue</h4>
            <div class="value">₱{{ number_format($monthlyRevenue, 2) }}</div>
        </div>
        <div class="kpi">
            <h4>Today's Income</h4>
            <div class="value">₱{{ number_format($dailyIncome, 2) }}</div>
        </div>
    </div>

    <div class="main-grid">

        {{-- MAIN CHART --}}
        <div class="card">
            <div class="tabs">
                <button class="tab-btn active" data-type="bookings" type="button">Booking Activity (7 days)</button>
                <button class="tab-btn" data-type="revenue" type="button">Revenue (30 days)</button>
            </div>

            <canvas id="mainChart"></canvas>
        </div>

        {{-- SIDE PANEL: CURRENT STATUS + DAILY SNAPSHOT --}}
        <div class="card">
            <h4>Current Booking Status</h4>

            <div class="status-grid">
                <div class="trend-item">
                    <div class="trend-label">Confirmed</div>
                    <div class="trend-value">{{ number_format((int)($statusCounts['confirmed'] ?? 0)) }}</div>
                    <div class="trend-sub">Current confirmed bookings</div>
                </div>

                <div class="trend-item">
                    <div class="trend-label">In Progress</div>
                    <div class="trend-value">{{ number_format((int)($statusCounts['in_progress'] ?? 0)) }}</div>
                    <div class="trend-sub">Jobs currently in progress</div>
                </div>

                <div class="trend-item">
                    <div class="trend-label">Paid</div>
                    <div class="trend-value">{{ number_format((int)($statusCounts['paid'] ?? 0)) }}</div>
                    <div class="trend-sub">Waiting to be wrapped up</div>
                </div>

                <div class="trend-item">
                    <div class="trend-label">Completed</div>
                    <div class="trend-value">{{ number_format((int)($statusCounts['completed'] ?? 0)) }}</div>
                    <div class="trend-sub">Finished bookings</div>
                </div>

                <div class="trend-item">
                    <div class="trend-label">Cancelled</div>
                    <div class="trend-value">{{ number_format((int)($statusCounts['cancelled'] ?? 0)) }}</div>
                    <div class="trend-sub">Bookings that were cancelled</div>
                </div>

                <div class="trend-item">
                    <div class="trend-label">Today</div>
                    <div class="trend-value">{{ number_format($confirmedToday) }} / {{ number_format($completedToday) }}</div>
                    <div class="trend-sub">Confirmed today / Completed today</div>
                </div>
            </div>

            <div class="trend-mini">
                <h5>7-Day Activity Snapshot</h5>
                <div class="trend-note">
                    Confirmed in the last 7 days: <strong>{{ number_format($confirmedTotal) }}</strong><br>
                    Completed in the last 7 days: <strong>{{ number_format($completedTotal) }}</strong><br>
                    Latest day activity: <strong>{{ number_format($latestConfirmed) }}</strong> confirmed and <strong>{{ number_format($latestCompleted) }}</strong> completed.
                </div>
            </div>
        </div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
const ctx = document.getElementById('mainChart');
let currentType = 'bookings';

// Blade -> JS data
const dailyLabels    = @json($dailyLabels);
const dailyConfirmed = @json($dailyConfirmed);
const dailyCompleted = @json($dailyCompleted);

const trendLabels  = @json($trendLabels);
const trendRevenue = @json($trendRevenue);

const chartData = {
  bookings: {
    labels: dailyLabels,
    datasets: [
      {
        label: 'Confirmed (created)',
        data: dailyConfirmed,
        backgroundColor: '#3b82f6',
        borderRadius: 8,
        maxBarThickness: 42
      },
      {
        label: 'Completed (updated)',
        data: dailyCompleted,
        backgroundColor: '#22c55e',
        borderRadius: 8,
        maxBarThickness: 42
      }
    ]
  },
  revenue: {
    labels: trendLabels,
    datasets: [
      {
        label: 'Revenue',
        data: trendRevenue,
        borderColor: '#3b82f6',
        backgroundColor: 'rgba(59,130,246,.22)',
        fill: true,
        tension: .4,
        pointRadius: 3,
        pointHoverRadius: 5,
        borderWidth: 2
      }
    ]
  }
};

function getChartOptions() {
  return {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
      legend: {
        labels: {
          color: 'rgba(255,255,255,.75)',
          font: { weight: '700' }
        }
      }
    },
    scales: {
      x: {
        ticks: { color: 'rgba(255,255,255,.55)' },
        grid: { color: 'rgba(255,255,255,.06)' }
      },
      y: {
        beginAtZero: true,
        ticks: { color: 'rgba(255,255,255,.55)' },
        grid: { color: 'rgba(255,255,255,.06)' }
      }
    }
  };
}

let mainChart = new Chart(ctx, {
  type: 'bar',
  data: chartData.bookings,
  options: getChartOptions()
});

document.querySelectorAll('.tab-btn[data-type]').forEach(btn => {
  btn.addEventListener('click', function () {
    document.querySelectorAll('.tab-btn[data-type]').forEach(b => b.classList.remove('active'));
    this.classList.add('active');

    currentType = this.dataset.type;

    mainChart.destroy();
    mainChart = new Chart(ctx, {
      type: currentType === 'revenue' ? 'line' : 'bar',
      data: chartData[currentType],
      options: getChartOptions()
    });
  });
});
</script>
@endsection
