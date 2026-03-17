@extends('layouts.app')

@section('title', 'Pricing | CleanTech')

@push('styles')
@include('pages._theme')
@endpush

@section('content')
<div class="ct-page">
    <section class="ct-hero">
        <div class="container">
            <div class="ct-hero-shell">
                <div class="ct-hero-main ct-reveal">
                    <span class="ct-eyebrow">Pricing</span>
                    <h1 class="ct-title">Cleaner pricing blocks with the main amounts up front.</h1>
                    <p class="ct-lead">Base prices are shown here to keep comparisons easier before the customer goes into the booking flow.</p>
                </div>

                <div class="ct-photo-card tall ct-reveal delay-1">
                    <img src="https://images.unsplash.com/photo-1581578731548-c64695cc6952?auto=format&fit=crop&w=1400&q=80" alt="Cleaning pricing and service planning">
                </div>
            </div>
        </div>
    </section>

    <section class="ct-section">
        <div class="container">
            <div class="ct-grid three">
                <article class="ct-price-card ct-reveal">
                    <div class="ct-card-media">
                        <img src="https://images.unsplash.com/photo-1563453392212-326f5e854473?auto=format&fit=crop&w=1400&q=80" alt="Specific area cleaning">
                    </div>
                    <div class="ct-price-badge">Specific area cleaning</div>
                    <h3>Targeted room cleaning</h3>
                    <div class="ct-price">PHP 800</div>
                    <div class="ct-price-list">
                        <div class="ct-price-item"><span>Kitchen</span><small>+ PHP 400</small></div>
                        <div class="ct-price-item"><span>Bathroom</span><small>+ PHP 500</small></div>
                        <div class="ct-price-item"><span>Bedroom</span><small>+ PHP 450</small></div>
                    </div>
                </article>

                <article class="ct-price-card ct-reveal delay-1">
                    <div class="ct-card-media">
                        <img src="https://images.unsplash.com/photo-1520607162513-77705c0f0d4a?auto=format&fit=crop&w=1400&q=80" alt="General cleaning">
                    </div>
                    <div class="ct-price-badge">General cleaning</div>
                    <h3>Routine whole-home upkeep</h3>
                    <div class="ct-price">PHP 1,500</div>
                    <div class="ct-price-list">
                        <div class="ct-price-item"><span>Studio / small apartment</span><small>Base</small></div>
                        <div class="ct-price-item"><span>1-bedroom apartment</span><small>+ PHP 500</small></div>
                        <div class="ct-price-item"><span>Medium house</span><small>+ PHP 1,800</small></div>
                    </div>
                </article>

                <article class="ct-price-card ct-reveal delay-2">
                    <div class="ct-card-media">
                        <img src="https://images.unsplash.com/photo-1585421514738-01798e348b17?auto=format&fit=crop&w=1400&q=80" alt="Deep cleaning">
                    </div>
                    <div class="ct-price-badge">Deep cleaning</div>
                    <h3>Detailed full-space cleaning</h3>
                    <div class="ct-price">PHP 2,500</div>
                    <div class="ct-price-list">
                        <div class="ct-price-item"><span>Move-in / move-out</span><small>Ideal</small></div>
                        <div class="ct-price-item"><span>Seasonal reset</span><small>Recommended</small></div>
                        <div class="ct-price-item"><span>Heavy-use spaces</span><small>Best fit</small></div>
                    </div>
                </article>
            </div>
        </div>
    </section>
</div>
@endsection
