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
                    <span class="ct-eyebrow">FAQ</span>
                    <h1 class="ct-title">Quick answers to the things customers usually ask first.</h1>
                    <p class="ct-lead">Short, readable, and easier to scan on both desktop and mobile.</p>
                </div>

                <div class="ct-photo-card tall ct-reveal delay-1">
                    <img src="https://images.unsplash.com/photo-1450101499163-c8848c66ca85?auto=format&fit=crop&w=1400&q=80" alt="Frequently asked questions concept">
                </div>
            </div>
        </div>
    </section>

    <section class="ct-section">
        <div class="container">
            <div class="ct-faq-stack">
                <details class="ct-faq-item ct-reveal" open>
                    <summary>How do I book a service?</summary>
                    <p>Create a customer account, pick a service, choose a provider and date, then confirm the booking details.</p>
                </details>

                <details class="ct-faq-item ct-reveal delay-1">
                    <summary>Are providers approved before appearing on the platform?</summary>
                    <p>Yes. Providers go through review before they appear as approved on the platform.</p>
                </details>

                <details class="ct-faq-item ct-reveal delay-2">
                    <summary>Can I pick my own date and available time?</summary>
                    <p>Yes. The booking flow is date-based, so time slots should reflect the selected day only.</p>
                </details>

                <details class="ct-faq-item ct-reveal">
                    <summary>Can I cancel my booking?</summary>
                    <p>Customers can cancel eligible bookings before the service reaches the in-progress stage.</p>
                </details>

                <details class="ct-faq-item ct-reveal delay-1">
                    <summary>How do I contact CleanTech directly?</summary>
                    <p>You can use the contact page or email <a href="mailto:janzedoysabas@gmail.com" class="ct-inline-link">janzedoysabas@gmail.com</a>.</p>
                </details>
            </div>
        </div>
    </section>
</div>
@endsection
