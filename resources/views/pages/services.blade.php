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
                    <h1 class="ct-title">Three core cleaning options, presented clearly so customers can choose faster.</h1>
                    <p class="ct-lead">This page now matches the actual main services used across the app instead of feeling like a generic service gallery with too much visual noise.</p>

                    <div class="ct-actions">
                        <a href="{{ route('customer.register') }}" class="ct-button">Create Customer Account</a>
                        <a href="{{ route('pricing') }}" class="ct-button secondary">View Pricing</a>
                    </div>
                </div>

                <div class="ct-hero-side ct-reveal delay-1">
                    <div>
                        <div class="ct-side-label">Best way to choose</div>
                        <div class="ct-side-value">Match the service to the scope of work, not just the price.</div>
                    </div>
                    <p class="ct-side-copy">Use specific area cleaning for one part of the home, general cleaning for routine maintenance, and deep cleaning when the space needs a more serious reset.</p>
                </div>
            </div>
        </div>
    </section>

    <section class="ct-section">
        <div class="container">
            <div class="ct-grid three">
                <article class="ct-card ct-reveal">
                    <div class="ct-kicker">Specific area cleaning</div>
                    <h3>Best for targeted rooms or problem spots</h3>
                    <p>Choose this when you only need selected areas handled, such as a kitchen, bathroom, bedroom, or another single zone.</p>
                    <div class="ct-list">
                        <div class="ct-list-row"><div class="ct-list-mark">A</div><p>Focused on selected spaces only</p></div>
                        <div class="ct-list-row"><div class="ct-list-mark">B</div><p>Good for quick resets and budget control</p></div>
                    </div>
                </article>

                <article class="ct-card ct-reveal delay-1">
                    <div class="ct-kicker">General cleaning</div>
                    <h3>Best for regular upkeep</h3>
                    <p>Use this for normal household maintenance when the home needs a routine clean but not a full deep reset.</p>
                    <div class="ct-list">
                        <div class="ct-list-row"><div class="ct-list-mark">A</div><p>Works well for weekly or recurring care</p></div>
                        <div class="ct-list-row"><div class="ct-list-mark">B</div><p>Fits most day-to-day residential needs</p></div>
                    </div>
                </article>

                <article class="ct-card ct-reveal delay-2">
                    <div class="ct-kicker">Deep cleaning</div>
                    <h3>Best for heavier cleaning needs</h3>
                    <p>Choose this when buildup, neglected areas, or larger reset work require a more detailed top-to-bottom service.</p>
                    <div class="ct-list">
                        <div class="ct-list-row"><div class="ct-list-mark">A</div><p>Ideal for move-ins, move-outs, or seasonal cleaning</p></div>
                        <div class="ct-list-row"><div class="ct-list-mark">B</div><p>Better when a quick routine clean is not enough</p></div>
                    </div>
                </article>
            </div>
        </div>
    </section>
</div>
@endsection
