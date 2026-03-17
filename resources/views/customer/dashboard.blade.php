@extends('customer.layouts.app')

@section('title', 'Customer Dashboard')

@section('content')

@php
    use Carbon\Carbon;

    $tz = config('app.timezone') ?? 'Asia/Manila';
    $now = Carbon::now($tz);

    $hour = (int) $now->format('G');
    $greeting = $hour < 12 ? 'Good morning' : ($hour < 18 ? 'Good afternoon' : 'Good evening');

    $name = $name ?? 'Customer';

    $stats = $stats ?? [
        'total_bookings' => 0,
        'active_bookings' => 0,
        'total_spent' => 0,
        'spent_today' => 0,
        'spent_month' => 0,
        'spent_year' => 0,
        'bookings_today' => 0,
        'completed_today' => 0,
    ];

    $totalBookings = (int) ($stats['total_bookings'] ?? 0);
    $activeBookings = (int) ($stats['active_bookings'] ?? 0);
    $totalSpent = (float) ($stats['total_spent'] ?? 0);
    $spentToday = (float) ($stats['spent_today'] ?? 0);
    $spentMonth = (float) ($stats['spent_month'] ?? 0);
    $spentYear = (float) ($stats['spent_year'] ?? 0);
    $bookingsToday = (int) ($stats['bookings_today'] ?? 0);
    $completedToday = (int) ($stats['completed_today'] ?? 0);

    $recent = $recentCompleted ?? collect();
    if (!($recent instanceof \Illuminate\Support\Collection)) {
        $recent = collect($recent);
    }

    $formatMoney = fn ($value) => 'PHP ' . number_format((float) $value, 2);
    $dataGet = fn ($item, $key, $default = null) => data_get($item, $key, $default);
@endphp

<style>
    :root {
        --dash-bg: #020617;
        --dash-panel: #071225;
        --dash-panel-soft: #0b1830;
        --dash-border: rgba(148, 163, 184, 0.16);
        --dash-border-strong: rgba(56, 189, 248, 0.24);
        --dash-text: rgba(255, 255, 255, 0.96);
        --dash-muted: rgba(226, 232, 240, 0.68);
        --dash-accent: #38bdf8;
        --dash-accent-soft: rgba(56, 189, 248, 0.12);
        --dash-warm: #f59e0b;
        --dash-success: #22c55e;
        --dash-shadow: 0 24px 60px rgba(2, 6, 23, 0.4);
    }

    .customer-dashboard {
        max-width: 1180px;
        margin: 0 auto;
        color: var(--dash-text);
    }

    .dashboard-stack {
        display: flex;
        flex-direction: column;
        gap: 1.25rem;
    }

    .dashboard-hero {
        position: relative;
        overflow: hidden;
        background:
            radial-gradient(circle at top left, rgba(56, 189, 248, 0.2), transparent 34%),
            linear-gradient(135deg, rgba(8, 20, 43, 0.96), rgba(2, 6, 23, 0.98));
        border: 1px solid var(--dash-border-strong);
        border-radius: 28px;
        padding: 1.75rem;
        box-shadow: var(--dash-shadow);
    }

    .dashboard-hero::after {
        content: '';
        position: absolute;
        inset: auto -80px -90px auto;
        width: 240px;
        height: 240px;
        border-radius: 999px;
        background: radial-gradient(circle, rgba(56, 189, 248, 0.16), transparent 68%);
        pointer-events: none;
    }

    .hero-grid {
        display: grid;
        grid-template-columns: minmax(0, 1.6fr) minmax(280px, 0.9fr);
        gap: 1.25rem;
        align-items: stretch;
    }

    .hero-copy {
        position: relative;
        z-index: 1;
    }

    .hero-kicker {
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        padding: 0.45rem 0.8rem;
        border-radius: 999px;
        background: rgba(255, 255, 255, 0.06);
        border: 1px solid rgba(255, 255, 255, 0.08);
        color: rgba(255, 255, 255, 0.85);
        font-size: 0.82rem;
        font-weight: 700;
        letter-spacing: 0.04em;
        text-transform: uppercase;
    }

    .hero-title {
        margin: 1rem 0 0;
        font-size: clamp(2rem, 3vw, 3rem);
        font-weight: 900;
        line-height: 1.02;
        letter-spacing: -0.04em;
    }

    .hero-subtitle {
        max-width: 640px;
        margin: 0.85rem 0 0;
        color: var(--dash-muted);
        font-size: 1rem;
        line-height: 1.7;
    }

    .hero-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 0.85rem;
        margin-top: 1.4rem;
    }

    .hero-button,
    .hero-button-secondary {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.55rem;
        min-height: 48px;
        padding: 0.9rem 1.15rem;
        border-radius: 16px;
        text-decoration: none;
        font-weight: 800;
        transition: transform 0.18s ease, border-color 0.18s ease, background 0.18s ease;
    }

    .hero-button {
        background: linear-gradient(180deg, rgba(56, 189, 248, 0.96), rgba(14, 165, 233, 0.82));
        border: 1px solid rgba(56, 189, 248, 0.52);
        color: #04111f;
        box-shadow: 0 18px 40px rgba(14, 165, 233, 0.24);
    }

    .hero-button:hover {
        color: #04111f;
        transform: translateY(-1px);
    }

    .hero-button-secondary {
        background: rgba(255, 255, 255, 0.04);
        border: 1px solid rgba(255, 255, 255, 0.1);
        color: var(--dash-text);
    }

    .hero-button-secondary:hover {
        color: var(--dash-text);
        border-color: rgba(56, 189, 248, 0.28);
        background: rgba(56, 189, 248, 0.08);
        transform: translateY(-1px);
    }

    .hero-summary {
        position: relative;
        z-index: 1;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        gap: 1rem;
        padding: 1.2rem;
        border-radius: 22px;
        background: rgba(4, 12, 24, 0.72);
        border: 1px solid rgba(255, 255, 255, 0.08);
        backdrop-filter: blur(8px);
    }

    .hero-summary-label {
        color: rgba(255, 255, 255, 0.72);
        font-size: 0.8rem;
        font-weight: 700;
        letter-spacing: 0.08em;
        text-transform: uppercase;
    }

    .hero-summary-date {
        font-size: 1.25rem;
        font-weight: 900;
        line-height: 1.2;
    }

    .hero-summary-note {
        color: var(--dash-muted);
        font-size: 0.92rem;
        line-height: 1.6;
    }

    .hero-summary-badges {
        display: flex;
        flex-wrap: wrap;
        gap: 0.65rem;
    }

    .hero-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        padding: 0.55rem 0.8rem;
        border-radius: 999px;
        border: 1px solid rgba(255, 255, 255, 0.08);
        background: rgba(255, 255, 255, 0.04);
        font-size: 0.85rem;
        font-weight: 700;
        color: rgba(255, 255, 255, 0.92);
    }

    .dashboard-overview {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 1rem;
    }

    .overview-card,
    .panel-card,
    .recent-card {
        background: linear-gradient(180deg, rgba(6, 16, 33, 0.96), rgba(2, 6, 23, 0.98));
        border: 1px solid var(--dash-border);
        border-radius: 24px;
        box-shadow: var(--dash-shadow);
    }

    .overview-card {
        padding: 1.2rem;
    }

    .overview-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
    }

    .overview-icon {
        width: 46px;
        height: 46px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 15px;
        background: var(--dash-accent-soft);
        border: 1px solid rgba(56, 189, 248, 0.16);
        color: var(--dash-accent);
        font-size: 1.2rem;
    }

    .overview-label {
        color: var(--dash-muted);
        font-size: 0.84rem;
        font-weight: 700;
        letter-spacing: 0.08em;
        text-transform: uppercase;
    }

    .overview-value {
        margin-top: 1rem;
        font-size: clamp(1.8rem, 2vw, 2.4rem);
        font-weight: 900;
        letter-spacing: -0.04em;
        line-height: 1;
    }

    .overview-text {
        margin-top: 0.55rem;
        color: var(--dash-muted);
        font-size: 0.95rem;
        line-height: 1.55;
    }

    .dashboard-main {
        display: grid;
        grid-template-columns: minmax(0, 1.25fr) minmax(300px, 0.95fr);
        gap: 1rem;
    }

    .panel-card {
        padding: 1.35rem;
    }

    .panel-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 1rem;
        margin-bottom: 1.1rem;
    }

    .panel-title {
        margin: 0;
        font-size: 1.15rem;
        font-weight: 900;
        letter-spacing: -0.02em;
    }

    .panel-subtitle {
        margin: 0.35rem 0 0;
        color: var(--dash-muted);
        font-size: 0.92rem;
        line-height: 1.6;
    }

    .panel-tag {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        padding: 0.48rem 0.8rem;
        border-radius: 999px;
        border: 1px solid rgba(245, 158, 11, 0.22);
        background: rgba(245, 158, 11, 0.1);
        color: #fbbf24;
        font-size: 0.82rem;
        font-weight: 800;
        white-space: nowrap;
    }

    .insight-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 0.85rem;
    }

    .insight-item {
        padding: 1rem;
        border-radius: 18px;
        border: 1px solid rgba(148, 163, 184, 0.12);
        background: rgba(255, 255, 255, 0.03);
    }

    .insight-label {
        color: var(--dash-muted);
        font-size: 0.78rem;
        font-weight: 700;
        letter-spacing: 0.08em;
        text-transform: uppercase;
    }

    .insight-value {
        margin-top: 0.55rem;
        font-size: 1.1rem;
        font-weight: 900;
    }

    .insight-caption {
        margin-top: 0.35rem;
        color: var(--dash-muted);
        font-size: 0.85rem;
        line-height: 1.55;
    }

    .next-steps {
        display: flex;
        flex-direction: column;
        gap: 0.8rem;
    }

    .step-link {
        display: flex;
        align-items: flex-start;
        gap: 0.85rem;
        padding: 1rem;
        border-radius: 18px;
        text-decoration: none;
        color: inherit;
        border: 1px solid rgba(148, 163, 184, 0.12);
        background: rgba(255, 255, 255, 0.03);
        transition: transform 0.18s ease, border-color 0.18s ease, background 0.18s ease;
    }

    .step-link:hover {
        color: inherit;
        transform: translateY(-1px);
        border-color: rgba(56, 189, 248, 0.22);
        background: rgba(56, 189, 248, 0.06);
    }

    .step-icon {
        width: 44px;
        height: 44px;
        flex: 0 0 44px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 14px;
        background: rgba(56, 189, 248, 0.12);
        border: 1px solid rgba(56, 189, 248, 0.14);
        color: var(--dash-accent);
        font-size: 1rem;
    }

    .step-title {
        font-weight: 800;
        font-size: 0.98rem;
    }

    .step-copy {
        margin-top: 0.3rem;
        color: var(--dash-muted);
        font-size: 0.88rem;
        line-height: 1.55;
    }

    .recent-card {
        padding: 1.35rem;
    }

    .recent-head {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 1rem;
        margin-bottom: 1rem;
    }

    .recent-link {
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        padding: 0.68rem 0.95rem;
        border-radius: 14px;
        border: 1px solid rgba(56, 189, 248, 0.22);
        background: rgba(56, 189, 248, 0.08);
        color: var(--dash-text);
        text-decoration: none;
        font-weight: 800;
        white-space: nowrap;
    }

    .recent-link:hover {
        color: var(--dash-text);
        background: rgba(56, 189, 248, 0.12);
    }

    .recent-list {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 0.9rem;
    }

    .recent-item {
        padding: 1rem;
        border-radius: 18px;
        border: 1px solid rgba(148, 163, 184, 0.12);
        background: rgba(255, 255, 255, 0.03);
        display: flex;
        flex-direction: column;
        gap: 0.8rem;
    }

    .recent-top {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 0.75rem;
    }

    .recent-service {
        margin: 0;
        font-size: 1rem;
        font-weight: 900;
        line-height: 1.35;
    }

    .recent-provider {
        margin-top: 0.28rem;
        color: var(--dash-muted);
        font-size: 0.88rem;
        line-height: 1.55;
    }

    .recent-price {
        color: var(--dash-text);
        font-size: 1rem;
        font-weight: 900;
        white-space: nowrap;
    }

    .recent-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 0.6rem;
    }

    .recent-pill {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.45rem 0.68rem;
        border-radius: 999px;
        border: 1px solid rgba(148, 163, 184, 0.12);
        background: rgba(255, 255, 255, 0.04);
        font-size: 0.8rem;
        font-weight: 700;
        color: rgba(255, 255, 255, 0.9);
    }

    .recent-pill.success {
        border-color: rgba(34, 197, 94, 0.22);
        background: rgba(34, 197, 94, 0.1);
        color: #86efac;
    }

    .empty-state {
        padding: 2rem 1.25rem;
        border-radius: 22px;
        border: 1px dashed rgba(148, 163, 184, 0.18);
        background: rgba(255, 255, 255, 0.02);
        text-align: center;
    }

    .empty-state-icon {
        width: 58px;
        height: 58px;
        margin: 0 auto 0.9rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 18px;
        background: rgba(56, 189, 248, 0.1);
        color: var(--dash-accent);
        font-size: 1.4rem;
    }

    .empty-state-title {
        font-size: 1.05rem;
        font-weight: 900;
        margin-bottom: 0.35rem;
    }

    .empty-state-copy {
        color: var(--dash-muted);
        font-size: 0.95rem;
        line-height: 1.7;
        margin-bottom: 1rem;
    }

    @media (max-width: 1199.98px) {
        .hero-grid,
        .dashboard-main {
            grid-template-columns: 1fr;
        }

        .recent-list {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 991.98px) {
        .dashboard-overview {
            grid-template-columns: 1fr;
        }

        .insight-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 767.98px) {
        .dashboard-hero,
        .panel-card,
        .recent-card,
        .overview-card {
            border-radius: 22px;
        }

        .dashboard-hero,
        .panel-card,
        .recent-card {
            padding: 1.15rem;
        }

        .hero-actions {
            flex-direction: column;
        }

        .hero-button,
        .hero-button-secondary,
        .recent-link {
            width: 100%;
        }

        .recent-head,
        .panel-header {
            flex-direction: column;
            align-items: flex-start;
        }
    }
</style>

<div class="customer-dashboard">
    <div class="dashboard-stack">
        <section class="dashboard-hero">
            <div class="hero-grid">
                <div class="hero-copy">
                    <div class="hero-kicker">
                        <i class="bi bi-stars"></i>
                        <span>{{ $greeting }}</span>
                    </div>

                    <h1 class="hero-title">{{ $name }}, your home care plans are all in one place.</h1>

                    <p class="hero-subtitle">
                        Keep track of your bookings, review what you have spent, and jump back into
                        the services you need without digging through technical details.
                    </p>

                    <div class="hero-actions">
                        <a class="hero-button" href="{{ route('customer.services') }}">
                            <i class="bi bi-search"></i>
                            <span>Book a Service</span>
                        </a>

                        <a class="hero-button-secondary" href="{{ route('customer.bookings') }}">
                            <i class="bi bi-calendar2-check"></i>
                            <span>View My Bookings</span>
                        </a>

                        <a class="hero-button-secondary" href="{{ route('customer.bookings.history') }}">
                            <i class="bi bi-clock-history"></i>
                            <span>Open Booking History</span>
                        </a>
                    </div>
                </div>

                <aside class="hero-summary">
                    <div>
                        <div class="hero-summary-label">Today</div>
                        <div class="hero-summary-date">{{ $now->format('l, F j, Y') }}</div>
                        <div class="hero-summary-note">
                            {{ $bookingsToday }} booking{{ $bookingsToday === 1 ? '' : 's' }} on your schedule today and
                            {{ $activeBookings }} currently active booking{{ $activeBookings === 1 ? '' : 's' }} to keep an eye on.
                        </div>
                    </div>

                    <div class="hero-summary-badges">
                        <div class="hero-badge">
                            <i class="bi bi-sunrise"></i>
                            <span>{{ $now->format('g:i A') }}</span>
                        </div>

                        <div class="hero-badge">
                            <i class="bi bi-check2-circle"></i>
                            <span>{{ $completedToday }} completed today</span>
                        </div>

                        <div class="hero-badge">
                            <i class="bi bi-wallet2"></i>
                            <span>{{ $formatMoney($spentToday) }} today</span>
                        </div>
                    </div>
                </aside>
            </div>
        </section>

        <section class="dashboard-overview">
            <article class="overview-card">
                <div class="overview-head">
                    <div class="overview-label">Active bookings</div>
                    <div class="overview-icon">
                        <i class="bi bi-calendar-event"></i>
                    </div>
                </div>
                <div class="overview-value">{{ $activeBookings }}</div>
                <div class="overview-text">
                    {{ $activeBookings > 0 ? 'You still have services in progress or waiting to be completed.' : 'You are all caught up right now with no active service in progress.' }}
                </div>
            </article>

            <article class="overview-card">
                <div class="overview-head">
                    <div class="overview-label">Total bookings</div>
                    <div class="overview-icon">
                        <i class="bi bi-journal-check"></i>
                    </div>
                </div>
                <div class="overview-value">{{ $totalBookings }}</div>
                <div class="overview-text">
                    Your full booking history across every service you have requested so far.
                </div>
            </article>

            <article class="overview-card">
                <div class="overview-head">
                    <div class="overview-label">Total spent</div>
                    <div class="overview-icon">
                        <i class="bi bi-cash-stack"></i>
                    </div>
                </div>
                <div class="overview-value">{{ $formatMoney($totalSpent) }}</div>
                <div class="overview-text">
                    A simple running total of paid and completed services.
                </div>
            </article>
        </section>

        <section class="dashboard-main">
            <article class="panel-card">
                <div class="panel-header">
                    <div>
                        <h2 class="panel-title">Your spending snapshot</h2>
                        <p class="panel-subtitle">
                            A quick view of what you have booked and spent recently, without making the page feel like a report.
                        </p>
                    </div>

                    <div class="panel-tag">
                        <i class="bi bi-graph-up-arrow"></i>
                        <span>{{ $now->format('F Y') }}</span>
                    </div>
                </div>

                <div class="insight-grid">
                    <div class="insight-item">
                        <div class="insight-label">Spent today</div>
                        <div class="insight-value">{{ $formatMoney($spentToday) }}</div>
                        <div class="insight-caption">What went toward services scheduled for today.</div>
                    </div>

                    <div class="insight-item">
                        <div class="insight-label">Spent this month</div>
                        <div class="insight-value">{{ $formatMoney($spentMonth) }}</div>
                        <div class="insight-caption">Your running total for {{ $now->format('F') }}.</div>
                    </div>

                    <div class="insight-item">
                        <div class="insight-label">Spent this year</div>
                        <div class="insight-value">{{ $formatMoney($spentYear) }}</div>
                        <div class="insight-caption">A year-to-date look at household cleaning services.</div>
                    </div>

                    <div class="insight-item">
                        <div class="insight-label">Completed today</div>
                        <div class="insight-value">{{ $completedToday }}</div>
                        <div class="insight-caption">Services that have already been wrapped up today.</div>
                    </div>
                </div>
            </article>

            <aside class="panel-card">
                <div class="panel-header">
                    <div>
                        <h2 class="panel-title">What would you like to do next?</h2>
                        <p class="panel-subtitle">
                            The fastest way back into the parts of the panel customers usually need most.
                        </p>
                    </div>
                </div>

                <div class="next-steps">
                    <a class="step-link" href="{{ route('customer.services') }}">
                        <div class="step-icon">
                            <i class="bi bi-grid-1x2"></i>
                        </div>
                        <div>
                            <div class="step-title">Browse services</div>
                            <div class="step-copy">Find available providers and book the service you need.</div>
                        </div>
                    </a>

                    <a class="step-link" href="{{ route('customer.bookings') }}">
                        <div class="step-icon">
                            <i class="bi bi-calendar-week"></i>
                        </div>
                        <div>
                            <div class="step-title">Check active bookings</div>
                            <div class="step-copy">See upcoming schedules, assigned providers, and live booking details.</div>
                        </div>
                    </a>

                    <a class="step-link" href="{{ route('customer.bookings.history') }}">
                        <div class="step-icon">
                            <i class="bi bi-receipt"></i>
                        </div>
                        <div>
                            <div class="step-title">Review past bookings</div>
                            <div class="step-copy">Look back at completed jobs, payment totals, and older booking records.</div>
                        </div>
                    </a>

                    <a class="step-link" href="{{ route('customer.profile') }}">
                        <div class="step-icon">
                            <i class="bi bi-person-circle"></i>
                        </div>
                        <div>
                            <div class="step-title">Update your profile</div>
                            <div class="step-copy">Keep your contact details and profile photo ready for future bookings.</div>
                        </div>
                    </a>
                </div>
            </aside>
        </section>

        <section class="recent-card">
            <div class="recent-head">
                <div>
                    <h2 class="panel-title">Recent completed bookings</h2>
                    <p class="panel-subtitle">
                        A simple recap of the services you have already finished.
                    </p>
                </div>

                <a class="recent-link" href="{{ route('customer.bookings.history', ['status' => 'completed']) }}">
                    <i class="bi bi-arrow-right"></i>
                    <span>View completed history</span>
                </a>
            </div>

            @if($recent->isNotEmpty())
                <div class="recent-list">
                    @foreach($recent as $booking)
                        @php
                            $service = $dataGet($booking, 'service_name')
                                ?? $dataGet($booking, 'service')
                                ?? $dataGet($booking, 'service.name')
                                ?? 'Service';

                            $provider = $dataGet($booking, 'provider_name')
                                ?? $dataGet($booking, 'provider')
                                ?? $dataGet($booking, 'provider.name')
                                ?? 'Provider';

                            $dateValue = $dataGet($booking, 'completed_at')
                                ?? $dataGet($booking, 'booking_date')
                                ?? $dataGet($booking, 'created_at');

                            try {
                                $dateLabel = $dateValue ? Carbon::parse($dateValue, $tz)->format('M d, Y') : 'No date';
                            } catch (\Throwable $exception) {
                                $dateLabel = 'No date';
                            }

                            $amount = (float) (
                                $dataGet($booking, 'total_amount')
                                ?? $dataGet($booking, 'total_price')
                                ?? $dataGet($booking, 'amount')
                                ?? $dataGet($booking, 'price')
                                ?? 0
                            );
                        @endphp

                        <article class="recent-item">
                            <div class="recent-top">
                                <div>
                                    <h3 class="recent-service">{{ $service }}</h3>
                                    <div class="recent-provider">{{ $provider }}</div>
                                </div>

                                <div class="recent-price">{{ $formatMoney($amount) }}</div>
                            </div>

                            <div class="recent-meta">
                                <div class="recent-pill">
                                    <i class="bi bi-calendar3"></i>
                                    <span>{{ $dateLabel }}</span>
                                </div>

                                <div class="recent-pill success">
                                    <i class="bi bi-check2-circle"></i>
                                    <span>Completed</span>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            @else
                <div class="empty-state">
                    <div class="empty-state-icon">
                        <i class="bi bi-house-heart"></i>
                    </div>
                    <div class="empty-state-title">No completed bookings yet</div>
                    <div class="empty-state-copy">
                        Once you finish a booking, it will appear here so you can quickly look back at the service and provider.
                    </div>
                    <a class="hero-button" href="{{ route('customer.services') }}">
                        <i class="bi bi-search"></i>
                        <span>Explore Services</span>
                    </a>
                </div>
            @endif
        </section>
    </div>
</div>

@endsection
