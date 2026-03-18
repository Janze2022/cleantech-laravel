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
    background-image: url('https://www.bobvila.com/wp-content/uploads/2022/03/The-Best-Cleaning-Services-Options.jpg?w=1128&h=752');
    animation-delay: 0s;
}
.slide-2 {
    background-image: url('https://content.app-sources.com/s/34724871351514405/uploads/Images/Commercial_and_Office_Cleaning_Services_Near_Me-5581197.jpg');
    animation-delay: 6s;
}
.slide-3 {
    background-image: url('https://images.unsplash.com/photo-1584622650111-993a426fbf0a');
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
    url('https://images.unsplash.com/photo-1581578731548-c64695cc6952')
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
        url('https://images.unsplash.com/photo-1600585153490-76fb20a32601')
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

/* =========================
   HOME ASSISTANT
========================= */
.home-assistant{
    position: fixed;
    right: 22px;
    bottom: 22px;
    z-index: 1200;
}

.assistant-launcher{
    border: none;
    border-radius: 999px;
    padding: .92rem 1.2rem;
    background: linear-gradient(135deg, #2563eb, #4f46e5);
    color: #fff;
    display: inline-flex;
    align-items: center;
    gap: .7rem;
    font-weight: 700;
    box-shadow: 0 22px 42px rgba(37,99,235,.36);
    transition: transform .2s ease, box-shadow .2s ease;
}

.assistant-launcher:hover{
    transform: translateY(-2px);
    box-shadow: 0 26px 50px rgba(37,99,235,.48);
}

.assistant-launcher-badge{
    width: 42px;
    height: 42px;
    border-radius: 50%;
    display: grid;
    place-items: center;
    background: rgba(255,255,255,.14);
    border: 1px solid rgba(255,255,255,.16);
}

.assistant-launcher-badge svg,
.assistant-panel-close svg{
    width: 18px;
    height: 18px;
    stroke: currentColor;
}

.assistant-launcher-copy{
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    line-height: 1.1;
}

.assistant-launcher-copy small{
    color: rgba(255,255,255,.75);
    font-size: .72rem;
    font-weight: 600;
}

.assistant-panel{
    position: absolute;
    right: 0;
    bottom: calc(100% + 14px);
    width: min(390px, calc(100vw - 28px));
    max-height: min(76vh, 720px);
    border-radius: 24px;
    overflow: hidden;
    border: 1px solid rgba(255,255,255,.08);
    background: linear-gradient(180deg, rgba(15,23,42,.98), rgba(2,6,23,.98));
    box-shadow: 0 30px 80px rgba(0,0,0,.58);
    display: none;
    flex-direction: column;
    transform: translateY(12px) scale(.98);
    opacity: 0;
    transform-origin: bottom right;
    transition: opacity .22s ease, transform .22s ease;
}

.assistant-panel.open{
    display: flex;
    opacity: 1;
    transform: translateY(0) scale(1);
}

.assistant-panel-top{
    padding: 1rem 1rem .95rem;
    background:
        radial-gradient(circle at top left, rgba(56,189,248,.26), transparent 45%),
        linear-gradient(135deg, rgba(37,99,235,.25), rgba(79,70,229,.18));
    border-bottom: 1px solid rgba(255,255,255,.08);
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 1rem;
}

.assistant-panel-title{
    display: flex;
    align-items: center;
    gap: .75rem;
}

.assistant-panel-icon{
    width: 44px;
    height: 44px;
    border-radius: 14px;
    display: grid;
    place-items: center;
    background: rgba(255,255,255,.09);
    border: 1px solid rgba(255,255,255,.14);
    color: #fff;
}

.assistant-panel-icon svg{
    width: 19px;
    height: 19px;
    stroke: currentColor;
}

.assistant-panel-title h3{
    margin: 0;
    font-size: 1.02rem;
    font-weight: 800;
    color: #fff;
}

.assistant-panel-title p{
    margin: .12rem 0 0;
    color: #cbd5f5;
    font-size: .83rem;
}

.assistant-panel-close{
    width: 40px;
    height: 40px;
    border-radius: 12px;
    border: 1px solid rgba(255,255,255,.08);
    background: rgba(255,255,255,.04);
    color: #e5e7eb;
    display: grid;
    place-items: center;
}

.assistant-panel-close:hover{
    background: rgba(255,255,255,.09);
}

.assistant-panel-body{
    padding: 1rem;
    display: flex;
    flex-direction: column;
    gap: .9rem;
    min-height: 0;
}

.assistant-messages{
    display: flex;
    flex-direction: column;
    gap: .8rem;
    max-height: 360px;
    overflow-y: auto;
    padding-right: .2rem;
}

.assistant-msg{
    max-width: 88%;
    padding: .85rem .95rem;
    border-radius: 18px;
    line-height: 1.55;
    font-size: .92rem;
    box-shadow: 0 12px 30px rgba(0,0,0,.18);
}

.assistant-msg a{
    color: #7dd3fc;
    font-weight: 700;
}

.assistant-msg.bot{
    align-self: flex-start;
    background: rgba(255,255,255,.05);
    border: 1px solid rgba(255,255,255,.07);
    color: #e5e7eb;
}

.assistant-msg.user{
    align-self: flex-end;
    background: linear-gradient(135deg, rgba(37,99,235,.26), rgba(79,70,229,.24));
    border: 1px solid rgba(56,189,248,.18);
    color: #fff;
}

.assistant-chip-list{
    display: flex;
    flex-wrap: wrap;
    gap: .55rem;
}

.assistant-chip{
    border: 1px solid rgba(255,255,255,.08);
    background: rgba(255,255,255,.03);
    color: #dbeafe;
    border-radius: 999px;
    padding: .48rem .8rem;
    font-size: .8rem;
    font-weight: 700;
}

.assistant-chip:hover{
    background: rgba(56,189,248,.10);
    border-color: rgba(56,189,248,.18);
    color: #fff;
}

.assistant-form{
    display: grid;
    grid-template-columns: minmax(0, 1fr) auto;
    gap: .65rem;
}

.assistant-input{
    min-height: 48px;
    border-radius: 15px;
    border: 1px solid rgba(255,255,255,.08);
    background: rgba(255,255,255,.03);
    color: #fff;
    padding: .85rem .95rem;
}

.assistant-input::placeholder{
    color: rgba(255,255,255,.38);
}

.assistant-input:focus{
    outline: none;
    border-color: rgba(56,189,248,.3);
    box-shadow: 0 0 0 .2rem rgba(56,189,248,.10);
}

.assistant-send{
    min-width: 104px;
    border: none;
    border-radius: 15px;
    background: linear-gradient(135deg, #2563eb, #4f46e5);
    color: #fff;
    font-weight: 800;
    padding: .8rem 1rem;
}

@media (max-width: 575px){
    .home-assistant{
        right: 14px;
        bottom: 14px;
        left: 14px;
    }

    .assistant-launcher{
        width: 100%;
        justify-content: center;
    }

    .assistant-panel{
        right: 0;
        left: 0;
        width: 100%;
        bottom: calc(100% + 10px);
    }

    .assistant-form{
        grid-template-columns: 1fr;
    }

    .assistant-send{
        width: 100%;
    }
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
                <img src="https://t3.ftcdn.net/jpg/02/98/67/88/360_F_298678837_bNtbbc5QqtNZdinHQkPKddKKVq5WKlXl.jpg" alt="Book cleaning online">
                <div class="work-overlay">
                    <h5>Book Online</h5>
                    <p>Select your service, date, and location in minutes.</p>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="work-image-card">
                <img src="https://t4.ftcdn.net/jpg/03/05/63/55/360_F_305635573_47SjydzWbcQPCTbkcfHyfD4fUY81XW9R.jpg" alt="Professional cleaner">
                <div class="work-overlay">
                    <h5>Get Matched</h5>
                    <p>We assign a verified professional to your booking.</p>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="work-image-card">
                <img src="https://images.pexels.com/photos/48889/cleaning-washing-cleanup-the-ilo-48889.jpeg?auto=compress&cs=tinysrgb&dpr=1&w=500" alt="Clean home">
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
                    <img src="https://hongkongofw.com/wp-content/uploads/2023/06/deep-cleaning.jpg">
                    <div class="service-body">
                        <h5>Deep Home Cleaning</h5>
                        <p>Top-to-bottom professional cleaning.</p>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="service-tile">
                    <img src="https://lirp.cdn-website.com/4403d184/dms3rep/multi/opt/AdobeStock_267548289-1920w.jpeg">
                    <div class="service-body">
                        <h5>Office Cleaning</h5>
                        <p>Keep your workspace spotless.</p>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="service-tile">
                    <img src="https://images.unsplash.com/photo-1584622650111-993a426fbf0a">
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

<div class="home-assistant" id="homeAssistant">
    <button type="button" class="assistant-launcher" id="assistantLauncher" aria-expanded="false" aria-controls="assistantPanel">
        <span class="assistant-launcher-badge" aria-hidden="true">
            <svg viewBox="0 0 24 24" fill="none" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
                <path d="M7 9h10"></path>
                <path d="M7 13h7"></path>
                <path d="M12 3C7.03 3 3 6.58 3 11c0 2.22 1.02 4.23 2.69 5.68L5 21l4.76-2.05A11.06 11.06 0 0 0 12 19c4.97 0 9-3.58 9-8s-4.03-8-9-8Z"></path>
            </svg>
        </span>
        <span class="assistant-launcher-copy">
            Need help?
            <small>Ask the CleanTech assistant</small>
        </span>
    </button>

    <div class="assistant-panel" id="assistantPanel" aria-live="polite">
        <div class="assistant-panel-top">
            <div class="assistant-panel-title">
                <div class="assistant-panel-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 8V4"></path>
                        <path d="M9 2h6"></path>
                        <rect x="4" y="8" width="16" height="11" rx="4"></rect>
                        <path d="M9 13h.01"></path>
                        <path d="M15 13h.01"></path>
                        <path d="M8 19v2"></path>
                        <path d="M16 19v2"></path>
                    </svg>
                </div>
                <div>
                    <h3>CleanTech Assistant</h3>
                    <p>Quick answers for booking, services, providers, and support.</p>
                </div>
            </div>

            <button type="button" class="assistant-panel-close" id="assistantClose" aria-label="Close assistant">
                <svg viewBox="0 0 24 24" fill="none" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M18 6L6 18"></path>
                    <path d="M6 6l12 12"></path>
                </svg>
            </button>
        </div>

        <div class="assistant-panel-body">
            <div class="assistant-messages" id="assistantMessages"></div>

            <div class="assistant-chip-list">
                <button type="button" class="assistant-chip" data-question="How do I book a service?">How to book</button>
                <button type="button" class="assistant-chip" data-question="What services do you offer?">Services</button>
                <button type="button" class="assistant-chip" data-question="How do I become a provider?">Become a provider</button>
                <button type="button" class="assistant-chip" data-question="How does pricing work?">Pricing</button>
                <button type="button" class="assistant-chip" data-question="How can I contact CleanTech?">Contact</button>
            </div>

            <form class="assistant-form" id="assistantForm">
                <input
                    type="text"
                    class="assistant-input"
                    id="assistantInput"
                    placeholder="Ask about booking, services, providers, or support"
                    autocomplete="off"
                >
                <button type="submit" class="assistant-send">Send</button>
            </form>

        </div>
    </div>
</div>

<script>
(function () {
    const launcher = document.getElementById('assistantLauncher');
    const panel = document.getElementById('assistantPanel');
    const closeButton = document.getElementById('assistantClose');
    const messages = document.getElementById('assistantMessages');
    const form = document.getElementById('assistantForm');
    const input = document.getElementById('assistantInput');
    const chips = document.querySelectorAll('.assistant-chip');

    if (!launcher || !panel || !messages || !form || !input) {
        return;
    }

    const routes = {
        register: @json(route('customer.register')),
        services: @json(route('services')),
        customerLogin: @json(route('customer.login')),
        providerSignup: @json(route('provider.pre_register.terms')),
        pricing: @json(route('pricing')),
        faq: @json(route('faq')),
        contact: @json(route('contact')),
        email: 'mailto:janzedoysabas@gmail.com',
    };

    const intents = [
        {
            keywords: ['book', 'booking', 'reserve', 'schedule', 'appointment'],
            answer: `To book a service, create a customer account, sign in, choose a service, and view the providers available for your selected date. <a href="${routes.register}">Start here</a>.`
        },
        {
            keywords: ['service', 'services', 'cleaning', 'deep', 'general', 'area'],
            answer: `CleanTech helps customers find available providers for services like general cleaning, deep cleaning, and specific-area cleaning. You can explore the full service overview on the <a href="${routes.services}">Services page</a>.`
        },
        {
            keywords: ['provider', 'become', 'signup', 'sign up', 'register provider', 'apply'],
            answer: `If you want to become a provider, use the provider sign-up flow, submit your details, and wait for approval before you start accepting bookings. <a href="${routes.providerSignup}">Become a provider</a>.`
        },
        {
            keywords: ['price', 'pricing', 'cost', 'rate', 'rates', 'fee', 'fees'],
            answer: `Pricing depends on the service, selected option, and available provider. You can check the public overview on the <a href="${routes.pricing}">Pricing page</a>, then log in to book the option that fits your schedule.`
        },
        {
            keywords: ['contact', 'email', 'support', 'help', 'phone'],
            answer: `You can reach CleanTech through the <a href="${routes.contact}">Contact page</a> or email directly at <a href="${routes.email}">janzedoysabas@gmail.com</a>.`
        },
        {
            keywords: ['faq', 'question', 'questions', 'approved', 'verified'],
            answer: `The <a href="${routes.faq}">FAQ page</a> covers common questions, including provider approval and how the platform works.`
        },
        {
            keywords: ['login', 'sign in', 'account'],
            answer: `Customers can sign in from <a href="${routes.customerLogin}">Customer Login</a>. If you are joining as a provider, start from <a href="${routes.providerSignup}">Provider Registration</a>.`
        },
    ];

    function addMessage(content, role) {
        const bubble = document.createElement('div');
        bubble.className = `assistant-msg ${role}`;
        bubble.innerHTML = content;
        messages.appendChild(bubble);
        messages.scrollTop = messages.scrollHeight;
    }

    function getReply(question) {
        const text = String(question || '').trim().toLowerCase();

        if (!text) {
            return 'Ask me about booking, services, pricing, provider sign-up, or contact details.';
        }

        if (['hi', 'hello', 'hey', 'good morning', 'good afternoon', 'good evening'].includes(text)) {
            return 'Hello. I can help with booking, services, provider sign-up, pricing, or support.';
        }

        let bestMatch = null;
        let bestScore = 0;

        intents.forEach((intent) => {
            const score = intent.keywords.reduce((total, keyword) => total + (text.includes(keyword) ? 1 : 0), 0);

            if (score > bestScore) {
                bestScore = score;
                bestMatch = intent;
            }
        });

        if (bestMatch && bestScore > 0) {
            return bestMatch.answer;
        }

        return `I can help with booking, services, provider sign-up, pricing, and contact details. You can also check the <a href="${routes.faq}">FAQ</a> or email <a href="${routes.email}">janzedoysabas@gmail.com</a>.`;
    }

    function openAssistant() {
        panel.hidden = false;
        requestAnimationFrame(() => panel.classList.add('open'));
        launcher.setAttribute('aria-expanded', 'true');
    }

    function closeAssistant() {
        panel.classList.remove('open');
        launcher.setAttribute('aria-expanded', 'false');
        setTimeout(() => {
            if (!panel.classList.contains('open')) {
                panel.hidden = true;
            }
        }, 220);
    }

    function handleQuestion(question) {
        const cleaned = String(question || '').trim();

        if (!cleaned) {
            return;
        }

        addMessage(cleaned, 'user');
        addMessage(getReply(cleaned), 'bot');
    }

    addMessage('Hello. I’m the CleanTech assistant. Ask me about booking, services, provider sign-up, pricing, or support.', 'bot');

    launcher.addEventListener('click', () => {
        if (panel.classList.contains('open')) {
            closeAssistant();
            return;
        }

        openAssistant();
        input.focus();
    });

    closeButton?.addEventListener('click', () => {
        closeAssistant();
    });

    form.addEventListener('submit', (event) => {
        event.preventDefault();
        const question = input.value;
        handleQuestion(question);
        input.value = '';
        input.focus();
    });

    chips.forEach((chip) => {
        chip.addEventListener('click', () => {
            openAssistant();
            handleQuestion(chip.dataset.question || chip.textContent || '');
        });
    });

    document.addEventListener('click', (event) => {
        if (!panel.classList.contains('open')) {
            return;
        }

        const assistantRoot = document.getElementById('homeAssistant');

        if (assistantRoot && !assistantRoot.contains(event.target)) {
            closeAssistant();
        }
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            closeAssistant();
        }
    });
})();
</script>

@endsection
