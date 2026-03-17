@extends('layouts.app')

@section('title', 'Blog | CleanTech')

@push('styles')
@include('pages._theme')
@endpush

@section('content')
<div class="ct-page">
    <section class="ct-hero">
        <div class="container">
            <div class="ct-hero-shell">
                <div class="ct-hero-main ct-reveal">
                    <span class="ct-eyebrow">CleanTech Journal</span>
                    <h1 class="ct-title">Short, useful reads for cleaner homes and smoother booking habits.</h1>
                    <p class="ct-lead">This page is meant to feel light and readable, so the blog stays closer to practical tips than long technical articles. Think quick guidance, seasonal reminders, and easier service decisions.</p>
                </div>

                <div class="ct-hero-side ct-reveal delay-1">
                    <div>
                        <div class="ct-side-label">Focus</div>
                        <div class="ct-side-value">Helpful advice you can actually use this week.</div>
                    </div>
                    <p class="ct-side-copy">From routine upkeep to deciding when a deep clean makes sense, these topics support the actual services offered on CleanTech.</p>
                    <div class="ct-actions">
                        <a href="{{ route('services') }}" class="ct-button">View Services</a>
                        <a href="{{ route('pricing') }}" class="ct-button secondary">See Pricing</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="ct-section">
        <div class="container">
            <div class="ct-grid three">
                <article class="ct-blog-card featured ct-reveal">
                    <div class="ct-blog-meta">Featured guide</div>
                    <h3>When routine cleaning is enough and when it is time to book a deeper reset</h3>
                    <p>A simple way to decide between general cleaning, specific area cleaning, and a full deep cleaning schedule without overbooking or overspending.</p>
                    <div class="ct-badges">
                        <span class="ct-badge">General cleaning</span>
                        <span class="ct-badge">Deep cleaning</span>
                    </div>
                </article>

                <article class="ct-blog-card ct-reveal delay-1">
                    <div class="ct-blog-meta">Home care</div>
                    <h3>Small habits that keep kitchens from turning into weekend projects</h3>
                    <p>Daily wipe-downs, sink resets, and surface routines that keep grease and clutter from building up too fast.</p>
                </article>

                <article class="ct-blog-card ct-reveal delay-2">
                    <div class="ct-blog-meta">Booking tips</div>
                    <h3>How to choose the right day before you tap Book Now</h3>
                    <p>Pick service dates that line up with provider availability and give you the best chance of a smooth visit.</p>
                </article>

                <article class="ct-blog-card ct-reveal">
                    <div class="ct-blog-meta">Space planning</div>
                    <h3>Why specific area cleaning works well for high-traffic rooms</h3>
                    <p>If the whole home does not need attention, targeted cleaning is often the more practical and budget-friendly choice.</p>
                </article>

                <article class="ct-blog-card ct-reveal delay-1">
                    <div class="ct-blog-meta">Seasonal reset</div>
                    <h3>Signs your home is due for a full deep clean</h3>
                    <p>Look for recurring dust, neglected corners, and spaces that stay messy even after a quick routine pass.</p>
                </article>
            </div>
        </div>
    </section>
</div>
@endsection
