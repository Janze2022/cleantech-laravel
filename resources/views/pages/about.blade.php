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
                    <p class="ct-lead">CleanTech connects customers with approved local providers for practical home cleaning that is easy to book and easy to follow.</p>
                    <div class="ct-actions">
                        <a href="{{ route('services') }}" class="ct-button">View Services</a>
                        <a href="{{ route('contact') }}" class="ct-button secondary">Contact Us</a>
                    </div>
                </div>

                <div class="ct-photo-card tall ct-reveal delay-1">
                    <img src="https://images.unsplash.com/photo-1517048676732-d65bc937f952?auto=format&fit=crop&w=1400&q=80" alt="CleanTech team planning services">
                </div>
            </div>
        </div>
    </section>

    <section class="ct-section">
        <div class="container">
            <div class="ct-split">
                <div class="ct-photo-card short ct-reveal">
                    <img src="https://images.unsplash.com/photo-1527515637462-cff94eecc1ac?auto=format&fit=crop&w=1400&q=80" alt="Professional home cleaning service">
                </div>

                <div class="ct-grid two">
                    <article class="ct-card ct-reveal">
                        <div class="ct-kicker">Mission</div>
                        <h3>What drives CleanTech</h3>
                        <p>To provide reliable, affordable, and quality cleaning and maintenance services that make everyday living easier, while upholding honesty, convenience, and customer satisfaction in every service we deliver.</p>
                    </article>

                    <article class="ct-card ct-reveal delay-1">
                        <div class="ct-kicker">Vision</div>
                        <h3>Where we want to grow</h3>
                        <p>To be a trusted and accessible service provider in the community, known for dependable work, practical solutions, and a commitment to helping households when they need support.</p>
                    </article>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
