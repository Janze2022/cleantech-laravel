@extends('layouts.app')

@section('title', 'Services | CleanTech')

@push('styles')
@include('pages._theme')
@endpush

@section('content')
<div class="ct-page">
    <section class="ct-hero">
        <div class="container">
            <div class="ct-hero-shell">
                <div class="ct-hero-main ct-reveal">
                    <span class="ct-eyebrow">Our Services</span>
                    <h1 class="ct-title">The same core services, but with the photo-based layout brought back.</h1>
                    <p class="ct-lead">A quicker view of what each service is for, without long blocks of description.</p>
                    <div class="ct-actions">
                        <a href="{{ route('customer.register') }}" class="ct-button">Create Account</a>
                        <a href="{{ route('pricing') }}" class="ct-button secondary">See Pricing</a>
                    </div>
                </div>

                <div class="ct-photo-card tall ct-reveal delay-1">
                    <img src="https://images.unsplash.com/photo-1520607162513-77705c0f0d4a?auto=format&fit=crop&w=1400&q=80" alt="Professional cleaning team">
                </div>
            </div>
        </div>
    </section>

    <section class="ct-section">
        <div class="container">
            <div class="ct-grid three">
                <article class="ct-card ct-reveal">
                    <div class="ct-card-media">
                        <img src="https://hongkongofw.com/wp-content/uploads/2023/06/deep-cleaning.jpg" alt="Specific area cleaning">
                    </div>
                    <div class="ct-kicker">Specific area cleaning</div>
                    <h3>Target one space</h3>
                    <p>Best for kitchens, bathrooms, bedrooms, and other selected zones.</p>
                </article>

                <article class="ct-card ct-reveal delay-1">
                    <div class="ct-card-media">
                        <img src="https://lirp.cdn-website.com/4403d184/dms3rep/multi/opt/AdobeStock_267548289-1920w.jpeg" alt="General cleaning">
                    </div>
                    <div class="ct-kicker">General cleaning</div>
                    <h3>Routine upkeep</h3>
                    <p>Great for normal full-home cleaning and regular maintenance.</p>
                </article>

                <article class="ct-card ct-reveal delay-2">
                    <div class="ct-card-media">
                        <img src="https://images.unsplash.com/photo-1585421514738-01798e348b17?auto=format&fit=crop&w=1400&q=80" alt="Deep cleaning">
                    </div>
                    <div class="ct-kicker">Deep cleaning</div>
                    <h3>Full reset</h3>
                    <p>For heavier buildup, seasonal refreshes, and more detailed cleaning work.</p>
                </article>
            </div>
        </div>
    </section>
</div>
@endsection
