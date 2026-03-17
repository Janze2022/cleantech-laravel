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
                    <h1 class="ct-title">Book a service in a few clear steps.</h1>
                    <p class="ct-lead">Choose a service, find an available provider, confirm the details, and track the booking until the job is done.</p>
                </div>

                <div class="ct-photo-card tall ct-reveal delay-1">
                    <img src="https://images.unsplash.com/photo-1603712725038-e9334ae8f39f?auto=format&fit=crop&w=1400&q=80" alt="Cleaner preparing equipment for a service visit">
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
                    <h3>Confirm the booking</h3>
                    <p>Review the details and submit the request once everything looks right.</p>
                </article>

                <article class="ct-step ct-reveal delay-1">
                    <div class="ct-step-number">5</div>
                    <h3>Track and review</h3>
                    <p>Monitor updates from your dashboard and leave a review after the job is completed.</p>
                </article>
            </div>
        </div>
    </section>
</div>
@endsection
