@extends('layouts.app')

@section('title', 'How It Works | CleanTech')

@push('styles')
@include('pages._theme')
@endpush

@section('content')
<div class="ct-page">
    <section class="ct-hero">
        <div class="container">
            <div class="ct-hero-shell">
                <div class="ct-hero-main ct-reveal">
                    <span class="ct-eyebrow">How It Works</span>
                    <h1 class="ct-title">A simpler path from service selection to completed booking.</h1>
                    <p class="ct-lead">CleanTech is meant to feel clear at each step, not overloaded with too many screens or too much explanation.</p>
                </div>

                <div class="ct-photo-card tall ct-reveal delay-1">
                    <img src="https://images.unsplash.com/photo-1581578731548-c64695cc6952?auto=format&fit=crop&w=1400&q=80" alt="Cleaning workflow">
                </div>
            </div>
        </div>
    </section>

    <section class="ct-section">
        <div class="container">
            <div class="ct-step-grid">
                <article class="ct-step ct-reveal">
                    <div class="ct-step-number">1</div>
                    <h3>Choose a service</h3>
                    <p>Pick general, deep, or specific area cleaning.</p>
                </article>

                <article class="ct-step ct-reveal delay-1">
                    <div class="ct-step-number">2</div>
                    <h3>Select the date</h3>
                    <p>Choose when you want the booking to happen.</p>
                </article>

                <article class="ct-step ct-reveal delay-2">
                    <div class="ct-step-number">3</div>
                    <h3>Review providers</h3>
                    <p>Check who is available for that exact day.</p>
                </article>

                <article class="ct-step ct-reveal">
                    <div class="ct-step-number">4</div>
                    <h3>Confirm and track</h3>
                    <p>Submit the booking and monitor updates from your dashboard.</p>
                </article>
            </div>
        </div>
    </section>
</div>
@endsection
