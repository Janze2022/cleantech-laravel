@extends('layouts.app')

@section('title', 'About | CleanTech')

@push('styles')
@include('pages._theme')
@endpush

@section('content')
<div class="ct-page">
    <section class="ct-hero">
        <div class="container">
            <div class="ct-hero-shell">
                <div class="ct-hero-main ct-reveal">
                    <span class="ct-eyebrow">About CleanTech</span>
                    <h1 class="ct-title">Clean home service that feels easier to book and easier to trust.</h1>
                    <p class="ct-lead">CleanTech connects customers with approved local providers for practical home cleaning without the cluttered experience.</p>
                    <div class="ct-actions">
                        <a href="{{ route('services') }}" class="ct-button">View Services</a>
                        <a href="{{ route('contact') }}" class="ct-button secondary">Contact Us</a>
                    </div>
                </div>

                <div class="ct-photo-card tall ct-reveal delay-1">
                    <img src="https://images.unsplash.com/photo-1521791136064-7986c2920216?auto=format&fit=crop&w=1400&q=80" alt="CleanTech team discussion">
                </div>
            </div>
        </div>
    </section>

    <section class="ct-section">
        <div class="container">
            <div class="ct-split">
                <div class="ct-photo-card short ct-reveal">
                    <img src="https://images.unsplash.com/photo-1581578731548-c64695cc6952?auto=format&fit=crop&w=1400&q=80" alt="Professional cleaning service">
                </div>

                <div class="ct-grid three">
                    <article class="ct-card ct-reveal">
                        <div class="ct-kicker">Mission</div>
                        <h3>Make booking simple</h3>
                        <p>Clear services, cleaner layouts, and less confusion for customers.</p>
                    </article>

                    <article class="ct-card ct-reveal delay-1">
                        <div class="ct-kicker">Vision</div>
                        <h3>Be locally dependable</h3>
                        <p>A platform people use because it feels reliable and easy to understand.</p>
                    </article>

                    <article class="ct-card ct-reveal delay-2">
                        <div class="ct-kicker">Focus</div>
                        <h3>Keep it practical</h3>
                        <p>Real service details, verified providers, and a smoother booking flow.</p>
                    </article>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
