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
                    <span class="ct-eyebrow">How CleanTech Works</span>
                    <h1 class="ct-title">From service selection to provider arrival, the flow is meant to stay easy to follow.</h1>
                    <p class="ct-lead">Instead of packing too much information into one place, this page keeps the process down to a few clear steps so customers understand what happens next at each stage.</p>
                </div>

                <div class="ct-hero-side ct-reveal delay-1">
                    <div>
                        <div class="ct-side-label">Booking rhythm</div>
                        <div class="ct-side-value">Choose, match, confirm, complete, review.</div>
                    </div>
                    <p class="ct-side-copy">CleanTech is structured so the booking journey stays readable on desktop and mobile, especially once provider availability is tied to a real date.</p>
                </div>
            </div>
        </div>
    </section>

    <section class="ct-section">
        <div class="container">
            <div class="ct-step-grid">
                <article class="ct-step ct-reveal">
                    <div class="ct-step-number">1</div>
                    <h3>Pick the service</h3>
                    <p>Choose between general cleaning, deep cleaning, or specific area cleaning based on what your space actually needs.</p>
                </article>

                <article class="ct-step ct-reveal delay-1">
                    <div class="ct-step-number">2</div>
                    <h3>Choose the date</h3>
                    <p>Select the day you want the service. Provider lists and booking slots should reflect that specific date only.</p>
                </article>

                <article class="ct-step ct-reveal delay-2">
                    <div class="ct-step-number">3</div>
                    <h3>Review providers</h3>
                    <p>See which approved providers are available, compare ratings, and check the service option that fits the job.</p>
                </article>

                <article class="ct-step ct-reveal">
                    <div class="ct-step-number">4</div>
                    <h3>Confirm the booking</h3>
                    <p>Submit the booking details, address, and schedule after checking that everything matches your request.</p>
                </article>

                <article class="ct-step ct-reveal delay-1">
                    <div class="ct-step-number">5</div>
                    <h3>Track progress</h3>
                    <p>Use your dashboard and bookings pages to monitor status updates, schedule details, and completed jobs.</p>
                </article>

                <article class="ct-step ct-reveal delay-2">
                    <div class="ct-step-number">6</div>
                    <h3>Leave a review</h3>
                    <p>After completion, rate the provider and leave useful feedback that helps future customers make better decisions.</p>
                </article>
            </div>
        </div>
    </section>
</div>
@endsection
