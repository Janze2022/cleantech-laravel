@extends('layouts.app')

@section('title', 'FAQ | CleanTech')

@push('styles')
@include('pages._theme')
@endpush

@section('content')
<div class="ct-page">
    <section class="ct-hero">
        <div class="container">
            <div class="ct-hero-shell">
                <div class="ct-hero-main ct-reveal">
                    <span class="ct-eyebrow">Frequently Asked Questions</span>
                    <h1 class="ct-title">Quick answers for the questions customers usually ask first.</h1>
                    <p class="ct-lead">This page is intentionally more compact than before, so visitors can scan the important booking, provider, and pricing questions without a wall of text.</p>
                </div>

                <div class="ct-hero-side ct-reveal delay-1">
                    <div>
                        <div class="ct-side-label">Best first step</div>
                        <div class="ct-side-value">Start with service type, date, and provider availability.</div>
                    </div>
                    <p class="ct-side-copy">Most confusion disappears once customers know which service fits their space and what date they actually want to book.</p>
                </div>
            </div>
        </div>
    </section>

    <section class="ct-section">
        <div class="container">
            <div class="ct-faq-stack">
                <details class="ct-faq-item ct-reveal" open>
                    <summary>How do I book a cleaning service?</summary>
                    <p>Create a customer account, choose a service, pick an available provider and date, then confirm the booking details before submitting.</p>
                </details>

                <details class="ct-faq-item ct-reveal delay-1">
                    <summary>Are providers checked before they appear on the platform?</summary>
                    <p>Yes. CleanTech is designed to work with approved providers, and admin review is part of the provider side of the system.</p>
                </details>

                <details class="ct-faq-item ct-reveal delay-2">
                    <summary>Can I select my own preferred date and time?</summary>
                    <p>Yes. The booking flow is date-aware, so customers can view provider availability based on the actual selected date rather than mixed future schedules.</p>
                </details>

                <details class="ct-faq-item ct-reveal">
                    <summary>What is the difference between general, deep, and specific area cleaning?</summary>
                    <p>General cleaning is for routine upkeep, deep cleaning is for a more intensive full-space reset, and specific area cleaning is best when you only need selected rooms or zones handled.</p>
                </details>

                <details class="ct-faq-item ct-reveal delay-1">
                    <summary>Can customers cancel a booking?</summary>
                    <p>Yes, customers can cancel eligible bookings before the job has moved into the in-progress stage. Once the service is already underway, cancellation is restricted on the customer side.</p>
                </details>

                <details class="ct-faq-item ct-reveal delay-2">
                    <summary>How do I contact CleanTech directly?</summary>
                    <p>You can use the contact page to open an email draft directly to <a href="mailto:janzedoysabas@gmail.com" class="ct-inline-link">janzedoysabas@gmail.com</a>.</p>
                </details>
            </div>
        </div>
    </section>
</div>
@endsection
