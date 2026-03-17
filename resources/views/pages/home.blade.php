@extends('layouts.app')

@section('title', 'CleanTech Solutions | Professional Cleaning Services')

@section('content')

<style>
/* =========================
   GLOBAL FIX
========================= */
html, body {
    background-color: #0b0f19;
    color: #e5e7eb;
    margin: 0;
    padding: 0;
    overflow-x: hidden;
    font-family: 'Segoe UI', sans-serif;
}

section {
    background-color: #0b0f19;
}

/* =========================
   HERO (WITH SLIDESHOW)
========================= */
.hero {
    position: relative;
    min-height: 88vh;
    display: flex;
    align-items: center;
    justify-content: center;
    text-align: center;
    overflow: hidden;
}

.hero-slide {
    position: absolute;
    inset: 0;
    background-size: cover;
    background-position: center;
    opacity: 0;
    animation: heroFade 18s infinite;
}

.slide-1 {
    background-image: url('{{ asset('images/scene-cleaning.svg') }}');
    animation-delay: 0s;
}
.slide-2 {
    background-image: url('{{ asset('images/scene-office.svg') }}');
    animation-delay: 6s;
}
.slide-3 {
    background-image: url('{{ asset('images/scene-office.svg') }}');
    animation-delay: 12s;
}

@keyframes heroFade {
    0% { opacity: 0 }
    10% { opacity: 1 }
    30% { opacity: 1 }
    40% { opacity: 0 }
    100% { opacity: 0 }
}

.hero-overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(
        rgba(11,15,25,.82),
        rgba(11,15,25,.9)
    );
    z-index: 1;
}

.hero-content {
    position: relative;
    z-index: 2;
}

.hero h1 {
    font-size: clamp(2.6rem, 5vw, 3.4rem);
    font-weight: 800;
    color: #fff;
}

.hero p {
    max-width: 520px;
    margin: 1rem auto 2.2rem;
    color: #cbd5f5;
}

/* CTA BUTTON */
.button-txt {
    background: linear-gradient(135deg, #2563eb, #6366f1);
    color: #fff !important;
    border-radius: 12px;
    padding: .8rem 1.6rem;
    font-size: .95rem;
    font-weight: 600;
    box-shadow: 0 8px 22px rgba(37,99,235,.4);
    transition: all .25s ease;
    text-decoration: none;
}

.button-txt:hover {
    transform: translateY(-2px);
    box-shadow: 0 14px 34px rgba(37,99,235,.55);
}

/* =========================
   TRUST STATS
========================= */
.stats {
    padding: 4rem 0;
}

.stat-box {
    background: #0f172a;
    border-radius: 18px;
    padding: 2rem;
    text-align: center;
    box-shadow: 0 14px 40px rgba(0,0,0,.45);
}

.stat-box h3 {
    font-weight: 800;
    color: #fff;
}

.stat-box p {
    color: #94a3b8;
    margin: 0;
}

/* =========================
   SECTION TITLE
========================= */
.section-title {
    text-align: center;
    margin-bottom: 3rem;
}

.section-title h2 {
    font-weight: 700;
    color: #fff;
}

.section-title p {
    color: #94a3b8;
}

/* =========================
   SERVICES GRID
========================= */
.services-showcase {
    padding: 5rem 0;
}

.service-tile {
    background: #0f172a;
    border-radius: 18px;
    overflow: hidden;
    transition: transform .25s ease, box-shadow .25s ease;
    box-shadow: 0 16px 40px rgba(0,0,0,.45);
}

.service-tile:hover {
    transform: translateY(-6px);
    box-shadow: 0 22px 55px rgba(0,0,0,.65);
}

.service-tile img {
    width: 100%;
    height: 220px;
    object-fit: cover;
}

.service-body {
    padding: 1.4rem;
}

.service-body h5 {
    font-weight: 600;
    color: #fff;
}

.service-body p {
    color: #94a3b8;
    font-size: .95rem;
}

/* =========================
   PROCESS SECTION
========================= */
.process {
    padding: 5rem 0;
}

.process-step {
    text-align: center;
}

.process-step span {
    display: inline-block;
    width: 44px;
    height: 44px;
    border-radius: 50%;
    background: linear-gradient(135deg, #2563eb, #6366f1);
    color: #fff;
    line-height: 44px;
    font-weight: 700;
    margin-bottom: 1rem;
}
/* =========================
   HOW IT WORKS – IMAGE STYLE
========================= */
.work-image-card {
    position: relative;
    border-radius: 18px;
    overflow: hidden;
    height: 300px;
    box-shadow: 0 18px 45px rgba(0,0,0,.45);
    transition: transform .3s ease, box-shadow .3s ease;
}

.work-image-card:hover {
    transform: translateY(-6px);
    box-shadow: 0 26px 60px rgba(0,0,0,.65);
}

.work-image-card img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

/* Overlay */
.work-overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(
        rgba(11,15,25,.25),
        rgba(11,15,25,.85)
    );
    display: flex;
    flex-direction: column;
    justify-content: flex-end;
    padding: 1.6rem;
}

.work-overlay h5 {
    color: #fff;
    font-weight: 700;
    margin-bottom: .3rem;
}

.work-overlay p {
    color: #cbd5f5;
    font-size: .95rem;
    margin: 0;
}

/* =========================
   PROMO BANNER
========================= */
.promo-banner {
    padding: 5rem 0;
    background: linear-gradient(
        to right,
        #0f172a 45%,
        rgba(15,23,42,.7)
    ),
    url('{{ asset('images/scene-cleaning.svg') }}')
    center/cover no-repeat;
}

.promo-content h2 {
    font-weight: 800;
    color: #fff;
}

.promo-content p {
    color: #cbd5f5;
}

/* =========================
   MISSION
========================= */
.mission {
    padding: 6rem 0;
    background:
        linear-gradient(
            rgba(11,15,25,.75),
            rgba(11,15,25,.88)
        ),
        url('{{ asset('images/scene-home.svg') }}')
        center/cover no-repeat;
}

.mission-content h2 {
    font-weight: 800;
    color: #fff;
}

.mission-content p {
    color: #cbd5f5;
}

/* =========================
   FINAL CTA
========================= */
.cta {
    background: linear-gradient(135deg, #2563eb, #6366f1);
    border-radius: 22px;
    padding: 3.5rem 2rem;
    box-shadow: 0 30px 70px rgba(37,99,235,.55);
}
</style>

<!-- HERO -->
<section class="hero">
    <div class="hero-slide slide-1"></div>
    <div class="hero-slide slide-2"></div>
    <div class="hero-slide slide-3"></div>
    <div class="hero-overlay"></div>

    <div class="container hero-content">
        <h1>CleanTech</h1>
        <p>Professional home and office cleaning services in Butuan City.</p>
        <div class="d-flex justify-content-center gap-3">
            <a href="{{ route('customer.register') }}" class="button-txt">Book a Cleaning</a>
            <a href="{{ route('provider.pre_register.terms') }}" class="btn btn-outline-light btn-lg">Become a Provider</a>
        </div>
    </div>
</section>

<!-- HOW IT WORKS (IMAGE-BASED) -->
<section class="container my-5">
    <div class="section-title">
        <h2>How CleanTech Works</h2>
        <p>From booking to spotless made simple</p>
    </div>

    <div class="row g-4">
        <div class="col-md-4">
            <div class="work-image-card">
                <img src="{{ asset('images/scene-verification.svg') }}" alt="Book cleaning online">
                <div class="work-overlay">
                    <h5>Book Online</h5>
                    <p>Select your service, date, and location in minutes.</p>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="work-image-card">
                <img src="{{ asset('images/scene-cleaning.svg') }}" alt="Professional cleaner">
                <div class="work-overlay">
                    <h5>Get Matched</h5>
                    <p>We assign a verified professional to your booking.</p>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="work-image-card">
                <img src="{{ asset('images/scene-home.svg') }}" alt="Clean home">
                <div class="work-overlay">
                    <h5>Relax & Enjoy</h5>
                    <p>Come home to a clean, fresh, and peaceful space.</p>
                </div>
            </div>
        </div>
    </div>
</section>




<!-- SERVICES -->
<section class="services-showcase">
    <div class="container">
        <div class="section-title">
            <h2>We’ve got what you need</h2>
            <p>Professional services for every space</p>
        </div>

        <div class="row g-4">
            <div class="col-md-4">
                <div class="service-tile">
                    <img src="{{ asset('images/service-generic.svg') }}">
                    <div class="service-body">
                        <h5>Deep Home Cleaning</h5>
                        <p>Top-to-bottom professional cleaning.</p>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="service-tile">
                    <img src="{{ asset('images/scene-office.svg') }}">
                    <div class="service-body">
                        <h5>Office Cleaning</h5>
                        <p>Keep your workspace spotless.</p>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="service-tile">
                    <img src="{{ asset('images/scene-office.svg') }}">
                    <div class="service-body">
                        <h5>Post Construction</h5>
                        <p>Detailed cleanup after renovations.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- PROMO -->
<section class="promo-banner">
    <div class="container">
        <div class="promo-content">
            <h2>The best cleaners are ready for your home</h2>
            <p>Reliable. Vetted. Professional.</p>
            <a href="{{ route('customer.register') }}" class="button-txt">Book Now</a>
        </div>
    </div>
</section>

<!-- MISSION -->
<section class="mission">
    <div class="container">
        <div class="mission-content">
            <h2>A healthier, cleaner home</h2>
            <p>CleanTech connects you with trusted professionals.</p>
            <p>Verified providers, transparent booking, consistent results.</p>
        </div>
    </div>
</section>

<!-- FINAL CTA -->
<section class="container my-5">
    <div class="cta text-center">
        <h2 class="fw-bold mb-2">Book. Clean. Relax.</h2>
        <p class="mb-4">A modern way to keep your space spotless.</p>
        <a href="{{ route('customer.register') }}" class="btn btn-light btn-lg">Get Started</a>
    </div>
</section>

@endsection
