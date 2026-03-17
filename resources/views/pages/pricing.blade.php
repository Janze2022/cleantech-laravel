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
                    <span class="ct-eyebrow">Pricing Guide</span>
                    <h1 class="ct-title">A simpler pricing view that shows the main starting points without overwhelming the customer.</h1>
                    <p class="ct-lead">These are base amounts and common option examples. Final totals still depend on the exact property type, area choice, or booking details selected during checkout.</p>
                </div>

                <div class="ct-hero-side ct-reveal delay-1">
                    <div>
                        <div class="ct-side-label">Quick note</div>
                        <div class="ct-side-value">Think of this as a clean estimate page, not a confusing rate sheet.</div>
                    </div>
                    <p class="ct-side-copy">Using clear PHP amounts also avoids the broken peso symbol issue that made the previous page feel rough.</p>
                </div>
            </div>
        </div>
    </section>

    <section class="ct-section">
        <div class="container">
            <div class="ct-grid three">
                <article class="ct-price-card ct-reveal">
                    <div class="ct-price-badge">Specific area cleaning</div>
                    <h3>Target one zone at a time</h3>
                    <div class="ct-price">PHP 800</div>
                    <div class="ct-price-sub">Base starting price</div>
                    <div class="ct-price-list">
                        <div class="ct-price-item"><span>Kitchen</span><small>+ PHP 400</small></div>
                        <div class="ct-price-item"><span>Bathroom</span><small>+ PHP 500</small></div>
                        <div class="ct-price-item"><span>Bedroom</span><small>+ PHP 450</small></div>
                        <div class="ct-price-item"><span>Garage</span><small>+ PHP 700</small></div>
                    </div>
                </article>

                <article class="ct-price-card ct-reveal delay-1">
                    <div class="ct-price-badge">General cleaning</div>
                    <h3>Routine whole-home upkeep</h3>
                    <div class="ct-price">PHP 1,500</div>
                    <div class="ct-price-sub">Base starting price</div>
                    <div class="ct-price-list">
                        <div class="ct-price-item"><span>Studio or small apartment</span><small>+ PHP 0</small></div>
                        <div class="ct-price-item"><span>1-bedroom apartment</span><small>+ PHP 500</small></div>
                        <div class="ct-price-item"><span>Medium house</span><small>+ PHP 1,800</small></div>
                        <div class="ct-price-item"><span>Large family house</span><small>+ PHP 2,600</small></div>
                    </div>
                </article>

                <article class="ct-price-card ct-reveal delay-2">
                    <div class="ct-price-badge">Deep cleaning</div>
                    <h3>Full reset for heavier cleaning needs</h3>
                    <div class="ct-price">PHP 2,500</div>
                    <div class="ct-price-sub">Base starting price</div>
                    <div class="ct-price-list">
                        <div class="ct-price-item"><span>Detailed full-space clean</span><small>Included</small></div>
                        <div class="ct-price-item"><span>Move-in or move-out prep</span><small>Common use</small></div>
                        <div class="ct-price-item"><span>Seasonal reset</span><small>Recommended</small></div>
                        <div class="ct-price-item"><span>Heavy-use areas</span><small>Ideal fit</small></div>
                    </div>
                </article>
            </div>
        </div>
    </section>
</div>
@endsection
