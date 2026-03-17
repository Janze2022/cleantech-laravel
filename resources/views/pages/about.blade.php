@extends('layouts.app')

@section('title', 'About | CleanTech')

@push('styles')
@include('pages._theme')
@endpush

@section('content')
<div class="ct-page">
    <section class="ct-hero">
        <div class="container">
            <div class="ct-hero-shell">
                <div class="ct-hero-main ct-reveal">
                    <span class="ct-eyebrow">About CleanTech</span>
                    <h1 class="ct-title">A cleaner home experience built around trust, clarity, and real local service.</h1>
                    <p class="ct-lead">CleanTech connects households in Butuan City with verified cleaning providers through a booking flow that feels simple on both sides. We focus on practical service, dependable schedules, and cleaner spaces without the usual hassle.</p>

                    <div class="ct-badges">
                        <span class="ct-badge">Verified providers</span>
                        <span class="ct-badge">Straightforward booking</span>
                        <span class="ct-badge">Built for local households</span>
                    </div>
                </div>

                <div class="ct-hero-side ct-reveal delay-1">
                    <div>
                        <div class="ct-side-label">What matters most</div>
                        <div class="ct-side-value">Reliable service you can understand at a glance.</div>
                    </div>

                    <p class="ct-side-copy">Instead of turning home cleaning into something overly technical, CleanTech keeps the experience readable, compact, and easier to trust from booking up to completion.</p>

                    <div class="ct-actions">
                        <a href="{{ route('services') }}" class="ct-button">Explore Services</a>
                        <a href="{{ route('contact') }}" class="ct-button secondary">Talk to Us</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="ct-section">
        <div class="container">
            <div class="ct-grid three">
                <article class="ct-card ct-reveal">
                    <div class="ct-kicker">Mission</div>
                    <h3>Make household cleaning easier to book and easier to trust.</h3>
                    <p>We aim to remove friction from everyday cleaning by combining clear service options, visible provider availability, and a smoother customer experience.</p>
                </article>

                <article class="ct-card ct-reveal delay-1">
                    <div class="ct-kicker">Vision</div>
                    <h3>Be the most dependable local platform for home cleaning support.</h3>
                    <p>We want CleanTech to feel like the service people recommend because it is practical, responsive, and easy to navigate from any device.</p>
                </article>

                <article class="ct-card ct-reveal delay-2">
                    <div class="ct-kicker">Promise</div>
                    <h3>Keep the process simple without losing the important details.</h3>
                    <p>Customers should understand what they are booking, who is available, and what happens next without digging through cluttered screens.</p>
                </article>
            </div>
        </div>
    </section>

    <section class="ct-section">
        <div class="container">
            <div class="ct-section-head">
                <div>
                    <h2 class="ct-section-title">Why people choose CleanTech</h2>
                    <p class="ct-section-copy">The platform is designed to feel organized and friendly for both first-time customers and repeat bookings.</p>
                </div>
            </div>

            <div class="ct-grid two">
                <article class="ct-card ct-reveal">
                    <h3>Designed around real booking steps</h3>
                    <div class="ct-list">
                        <div class="ct-list-row">
                            <div class="ct-list-mark">1</div>
                            <p>Choose a service with pricing and scope that are easier to compare.</p>
                        </div>
                        <div class="ct-list-row">
                            <div class="ct-list-mark">2</div>
                            <p>See available providers by date instead of guessing who is really free.</p>
                        </div>
                        <div class="ct-list-row">
                            <div class="ct-list-mark">3</div>
                            <p>Track bookings, reviews, and provider details in one place.</p>
                        </div>
                    </div>
                </article>

                <article class="ct-card ct-reveal delay-1">
                    <h3>Built for confidence</h3>
                    <div class="ct-metric-grid">
                        <div class="ct-metric">
                            <div class="ct-metric-label">Booking flow</div>
                            <div class="ct-metric-value">Clear</div>
                        </div>
                        <div class="ct-metric">
                            <div class="ct-metric-label">Providers</div>
                            <div class="ct-metric-value">Verified</div>
                        </div>
                        <div class="ct-metric">
                            <div class="ct-metric-label">Support</div>
                            <div class="ct-metric-value">Accessible</div>
                        </div>
                    </div>

                    <div class="ct-divider"></div>

                    <p class="ct-muted">Every design decision here is aimed at helping customers make a decision faster while still feeling informed.</p>
                </article>
            </div>
        </div>
    </section>
</div>
@endsection
