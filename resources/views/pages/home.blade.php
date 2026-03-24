@extends('layouts.app')

@section('title', 'CleanTech Solutions | Professional Cleaning Services')

@php
    $testimonials = [
        [
            'name' => 'Janze Salva',
            'review' => 'The booking flow felt smooth and easy to follow. From choosing a service to receiving updates, everything felt more organized and less stressful.',
            'rating' => 5,
        ],
        [
            'name' => 'Maria Santos',
            'review' => 'I liked how simple the process was. The provider arrived on time, the service felt professional, and the overall experience looked clean and modern.',
            'rating' => 5,
        ],
        [
            'name' => 'Ronald Saballe',
            'review' => 'CleanTech made it easier to arrange cleaning without the usual back and forth. It felt reliable, clear, and well guided from start to finish.',
            'rating' => 5,
        ],
        [
            'name' => 'Aileen Cruz',
            'review' => 'The site was easy to understand and the updates were clear. It feels like a professional service platform that actually helps customers book with confidence.',
            'rating' => 5,
        ],
    ];
@endphp

@push('styles')
<style>
html, body {
    background-color: #0b0f19;
    color: #e5e7eb;
    margin: 0;
    padding: 0;
    overflow-x: hidden;
    font-family: 'Segoe UI', sans-serif;
}

.home-page section {
    background-color: #0b0f19;
    position: relative;
}

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
    transform: scale(1.03);
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
    0% { opacity: 0; transform: scale(1.03); }
    10% { opacity: 1; }
    30% { opacity: 1; transform: scale(1); }
    40% { opacity: 0; }
    100% { opacity: 0; }
}

.hero-overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(rgba(11,15,25,.82), rgba(11,15,25,.92));
    z-index: 1;
}

.hero-content {
    position: relative;
    z-index: 2;
}

.hero-badge,
.hero h1,
.hero p,
.hero-actions {
    animation: fadeUp .85s ease both;
}

.hero-badge {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    padding: 8px 14px;
    border-radius: 999px;
    border: 1px solid rgba(56,189,248,.24);
    background: rgba(56,189,248,.10);
    color: #d8f3ff;
    font-size: .78rem;
    font-weight: 900;
    letter-spacing: .12em;
    text-transform: uppercase;
}

.hero-badge::before {
    content: "";
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: #38bdf8;
    box-shadow: 0 0 0 6px rgba(56,189,248,.14);
}

.hero h1 {
    animation-delay: .08s;
    font-size: clamp(2.7rem, 5vw, 3.9rem);
    font-weight: 900;
    color: #fff;
    letter-spacing: -.04em;
}

.hero p {
    animation-delay: .16s;
    max-width: 620px;
    margin: 1rem auto 2.2rem;
    color: #cbd5f5;
    line-height: 1.75;
    font-size: 1rem;
}

.hero-actions {
    animation-delay: .24s;
}

@keyframes fadeUp {
    from {
        opacity: 0;
        transform: translateY(22px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.button-txt,
.button-outline {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-height: 50px;
    border-radius: 14px;
    padding: .82rem 1.55rem;
    font-size: .95rem;
    font-weight: 800;
    text-decoration: none;
    transition: transform .25s ease, box-shadow .25s ease, background .25s ease, border-color .25s ease;
}

.button-txt {
    background: linear-gradient(135deg, #2563eb, #6366f1);
    color: #fff !important;
    box-shadow: 0 12px 28px rgba(37,99,235,.36);
}

.button-txt:hover {
    transform: translateY(-2px);
    box-shadow: 0 18px 36px rgba(37,99,235,.5);
}

.button-outline {
    color: #fff !important;
    border: 1px solid rgba(255,255,255,.14);
    background: rgba(255,255,255,.04);
}

.button-outline:hover {
    transform: translateY(-2px);
    border-color: rgba(56,189,248,.22);
    background: rgba(56,189,248,.08);
}

.section-title {
    text-align: center;
    margin-bottom: 3rem;
}

.section-title h2 {
    font-weight: 800;
    color: #fff;
    letter-spacing: -.03em;
}

.section-title p {
    color: #94a3b8;
    max-width: 760px;
    margin: .8rem auto 0;
    line-height: 1.75;
}

.reveal {
    opacity: 0;
    transform: translateY(26px);
    transition: opacity .72s ease, transform .72s ease;
    transition-delay: var(--delay, 0s);
}

.reveal.in-view {
    opacity: 1;
    transform: translateY(0);
}

.services-showcase,
.testimonials-section {
    padding: 5rem 0;
}

.service-tile {
    background: #0f172a;
    border-radius: 20px;
    overflow: hidden;
    transition: transform .28s ease, box-shadow .28s ease, border-color .28s ease;
    box-shadow: 0 16px 40px rgba(0,0,0,.45);
    border: 1px solid rgba(255,255,255,.06);
    height: 100%;
}

.service-tile:hover {
    transform: translateY(-6px);
    box-shadow: 0 22px 55px rgba(0,0,0,.62);
    border-color: rgba(56,189,248,.18);
}

.service-tile img {
    width: 100%;
    height: 220px;
    object-fit: cover;
    transition: transform .45s ease;
}

.service-tile:hover img {
    transform: scale(1.04);
}

.service-body {
    padding: 1.45rem;
}

.service-body h5 {
    font-weight: 700;
    color: #fff;
    margin-bottom: .55rem;
}

.service-body p {
    color: #94a3b8;
    font-size: .95rem;
    margin: 0;
    line-height: 1.65;
}

.work-image-card {
    position: relative;
    border-radius: 20px;
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
    transition: transform .45s ease;
}

.work-image-card:hover img {
    transform: scale(1.04);
}

.work-overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(rgba(11,15,25,.20), rgba(11,15,25,.88));
    display: flex;
    flex-direction: column;
    justify-content: flex-end;
    padding: 1.6rem;
}

.work-overlay h5 {
    color: #fff;
    font-weight: 700;
    margin-bottom: .35rem;
}

.work-overlay p {
    color: #cbd5f5;
    font-size: .95rem;
    margin: 0;
}

.testimonial-shell {
    padding: 28px;
    border-radius: 30px;
    border: 1px solid rgba(255,255,255,.08);
    background:
        radial-gradient(circle at top right, rgba(56,189,248,.08), transparent 28%),
        linear-gradient(180deg, rgba(9,18,36,.96), rgba(4,11,24,.98));
    box-shadow: 0 28px 70px rgba(0,0,0,.32);
}

.testimonial-top {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 18px;
    flex-wrap: wrap;
    margin-bottom: 24px;
}

.testimonial-controls {
    display: flex;
    gap: 10px;
}

.testimonial-arrow {
    width: 46px;
    height: 46px;
    border-radius: 16px;
    border: 1px solid rgba(255,255,255,.10);
    background: rgba(255,255,255,.04);
    color: #fff;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
    transition: transform .22s ease, background .22s ease, border-color .22s ease;
}

.testimonial-arrow:hover {
    transform: translateY(-1px);
    background: rgba(56,189,248,.10);
    border-color: rgba(56,189,248,.20);
}

.testimonial-viewport {
    overflow: hidden;
}

.testimonial-track {
    display: flex;
    transition: transform .72s cubic-bezier(.22, .61, .36, 1);
    will-change: transform;
}

.testimonial-slide {
    min-width: 100%;
    flex: 0 0 100%;
    padding: 2px;
}

.testimonial-card {
    display: grid;
    grid-template-columns: minmax(0, .9fr) minmax(0, 1.1fr);
    gap: 22px;
    padding: 26px;
    border-radius: 26px;
    border: 1px solid rgba(255,255,255,.08);
    background: linear-gradient(180deg, rgba(255,255,255,.04), rgba(255,255,255,.02));
    backdrop-filter: blur(16px);
    transform: scale(.985);
    transition: transform .45s ease, box-shadow .45s ease, border-color .45s ease;
}

.testimonial-slide.is-active .testimonial-card {
    transform: scale(1);
    border-color: rgba(56,189,248,.18);
    box-shadow: 0 18px 48px rgba(0,0,0,.22);
}

.testimonial-side {
    padding-right: 10px;
    border-right: 1px solid rgba(255,255,255,.07);
}

.quote-mark {
    width: 52px;
    height: 52px;
    border-radius: 18px;
    display: grid;
    place-items: center;
    background: rgba(56,189,248,.12);
    border: 1px solid rgba(56,189,248,.18);
    color: #cdefff;
    font-size: 1.7rem;
    font-weight: 900;
    margin-bottom: 16px;
}

.testimonial-name {
    margin: 0;
    color: #fff;
    font-size: 1.2rem;
    font-weight: 900;
}

.testimonial-role {
    margin-top: 6px;
    color: #8ca1c4;
    font-size: .85rem;
    font-weight: 800;
    letter-spacing: .08em;
    text-transform: uppercase;
}

.testimonial-text {
    margin: 0;
    color: #d7e2f3;
    font-size: 1.02rem;
    line-height: 1.85;
}

.stars {
    display: flex;
    gap: 4px;
    margin-top: 14px;
}

.stars span {
    color: #fbbf24;
    font-size: 1rem;
}

.testimonial-dots {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 10px;
    margin-top: 20px;
}

.testimonial-dot {
    width: 10px;
    height: 10px;
    border: none;
    border-radius: 999px;
    background: rgba(255,255,255,.18);
    transition: width .22s ease, background .22s ease, transform .22s ease;
}

.testimonial-dot.is-active {
    width: 34px;
    background: linear-gradient(135deg, #38bdf8, #6366f1);
}

.promo-banner {
    padding: 5rem 0;
    background:
        linear-gradient(to right, #0f172a 45%, rgba(15,23,42,.7)),
        url('https://images.unsplash.com/photo-1581578731548-c64695cc6952') center/cover no-repeat;
}

.promo-content h2,
.mission-content h2 {
    font-weight: 800;
    color: #fff;
}

.promo-content p,
.mission-content p {
    color: #cbd5f5;
    line-height: 1.75;
}

.mission {
    padding: 6rem 0;
    background:
        linear-gradient(rgba(11,15,25,.75), rgba(11,15,25,.88)),
        url('https://images.unsplash.com/photo-1600585153490-76fb20a32601') center/cover no-repeat;
}

.cta {
    background: linear-gradient(135deg, #2563eb, #6366f1);
    border-radius: 24px;
    padding: 3.5rem 2rem;
    box-shadow: 0 30px 70px rgba(37,99,235,.55);
}

@media (max-width: 1199.98px) {
    .testimonial-card {
        grid-template-columns: 1fr;
    }

    .testimonial-side {
        border-right: none;
        border-bottom: 1px solid rgba(255,255,255,.07);
        padding-right: 0;
        padding-bottom: 16px;
    }
}

@media (max-width: 767.98px) {
    .hero {
        min-height: auto;
        padding: 36px 0 28px;
    }

    .testimonials-section,
    .services-showcase,
    .promo-banner,
    .mission {
        padding: 4.2rem 0;
    }

    .hero-actions {
        flex-direction: column;
        gap: 12px;
    }

    .button-txt,
    .button-outline {
        width: 100%;
    }

    .testimonial-shell {
        padding: 22px;
    }
}
</style>
@endpush

@section('content')
<div class="home-page">
    <section class="hero">
        <div class="hero-slide slide-1"></div>
        <div class="hero-slide slide-2"></div>
        <div class="hero-slide slide-3"></div>
        <div class="hero-overlay"></div>

        <div class="container hero-content">
            <span class="hero-badge">Professional Cleaning Services</span>
            <h1>CleanTech</h1>
            <p>Professional home and office cleaning services in Butuan City.</p>
            <div class="hero-actions d-flex justify-content-center gap-3 flex-wrap">
                <a href="{{ route('customer.register') }}" class="button-txt">Book a Service</a>
                <a href="{{ route('provider.pre_register.terms') }}" class="button-outline">Become a Provider</a>
            </div>
        </div>
    </section>

    <section class="container my-5">
        <div class="section-title reveal">
            <h2>How CleanTech Works</h2>
            <p>From booking to spotless made simple</p>
        </div>

        <div class="row g-4">
            <div class="col-md-4 reveal">
                <div class="work-image-card">
                    <img src="https://t3.ftcdn.net/jpg/02/98/67/88/360_F_298678837_bNtbbc5QqtNZdinHQkPKddKKVq5WKlXl.jpg" alt="Book cleaning online">
                    <div class="work-overlay">
                        <h5>Book Online</h5>
                        <p>Select your service, date, and location in minutes.</p>
                    </div>
                </div>
            </div>

            <div class="col-md-4 reveal" style="--delay:.08s">
                <div class="work-image-card">
                    <img src="https://t4.ftcdn.net/jpg/03/05/63/55/360_F_305635573_47SjydzWbcQPCTbkcfHyfD4fUY81XW9R.jpg" alt="Professional cleaner">
                    <div class="work-overlay">
                        <h5>Get Matched</h5>
                        <p>We assign a verified professional to your booking.</p>
                    </div>
                </div>
            </div>

            <div class="col-md-4 reveal" style="--delay:.16s">
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

    <section class="services-showcase">
        <div class="container">
            <div class="section-title reveal">
                <h2>We've got what you need</h2>
                <p>Professional services for every space</p>
            </div>

            <div class="row g-4">
                <div class="col-md-4 reveal">
                    <div class="service-tile">
                        <img src="https://hongkongofw.com/wp-content/uploads/2023/06/deep-cleaning.jpg" alt="Deep home cleaning">
                        <div class="service-body">
                            <h5>Deep Home Cleaning</h5>
                            <p>Top-to-bottom professional cleaning.</p>
                        </div>
                    </div>
                </div>

                <div class="col-md-4 reveal" style="--delay:.08s">
                    <div class="service-tile">
                        <img src="https://lirp.cdn-website.com/4403d184/dms3rep/multi/opt/AdobeStock_267548289-1920w.jpeg" alt="Office cleaning">
                        <div class="service-body">
                            <h5>Office Cleaning</h5>
                            <p>Keep your workspace spotless.</p>
                        </div>
                    </div>
                </div>

                <div class="col-md-4 reveal" style="--delay:.16s">
                    <div class="service-tile">
                        <img src="https://images.unsplash.com/photo-1584622650111-993a426fbf0a" alt="Post construction">
                        <div class="service-body">
                            <h5>Post Construction</h5>
                            <p>Detailed cleanup after renovations.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="testimonials-section">
        <div class="container">
            <div class="testimonial-shell reveal">
                <div class="testimonial-top">
                    <div>
                        <div class="section-title text-start mb-0">
                            <h2>Satisfied Customers</h2>
                            <p>From customer service to operations, we aim to provide a consistent quality of service to our valuable clients, ensuring that each of them gets to experience quality and standardized service.</p>
                        </div>
                    </div>

                    <div class="testimonial-controls">
                        <button type="button" class="testimonial-arrow" id="reviewPrev" aria-label="Previous review">&#8592;</button>
                        <button type="button" class="testimonial-arrow" id="reviewNext" aria-label="Next review">&#8594;</button>
                    </div>
                </div>

                <div class="testimonial-viewport" id="reviewViewport">
                    <div class="testimonial-track" id="reviewTrack">
                        @foreach ($testimonials as $testimonial)
                            <div class="testimonial-slide">
                                <article class="testimonial-card">
                                    <div class="testimonial-side">
                                        <div class="quote-mark">&ldquo;</div>
                                        <h3 class="testimonial-name">{{ $testimonial['name'] }}</h3>
                                        <div class="testimonial-role">CleanTech Customer</div>
                                        <div class="stars" aria-label="{{ $testimonial['rating'] }} out of 5 stars">
                                            @for ($star = 0; $star < $testimonial['rating']; $star++)
                                                <span>&#9733;</span>
                                            @endfor
                                        </div>
                                    </div>

                                    <p class="testimonial-text">{{ $testimonial['review'] }}</p>
                                </article>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="testimonial-dots" id="reviewDots"></div>
            </div>
        </div>
    </section>

    <section class="promo-banner">
        <div class="container">
            <div class="promo-content reveal">
                <h2>The best cleaners are ready for your home</h2>
                <p>Reliable. Vetted. Professional.</p>
                <a href="{{ route('customer.register') }}" class="button-txt mt-3">Book Now</a>
            </div>
        </div>
    </section>

    <section class="mission">
        <div class="container">
            <div class="mission-content reveal">
                <h2>A healthier, cleaner home</h2>
                <p>CleanTech connects you with trusted professionals.</p>
                <p>Verified providers, transparent booking, consistent results.</p>
            </div>
        </div>
    </section>

    <section class="container my-5">
        <div class="cta text-center reveal">
            <h2 class="fw-bold mb-2">Book. Clean. Relax.</h2>
            <p class="mb-4">A modern way to keep your space spotless.</p>
            <a href="{{ route('customer.register') }}" class="btn btn-light btn-lg">Get Started</a>
        </div>
    </section>
</div>
@endsection

@push('scripts')
<script>
(() => {
    const reveals = document.querySelectorAll('.reveal');
    if ('IntersectionObserver' in window && reveals.length) {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('in-view');
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.14, rootMargin: '0px 0px -40px 0px' });

        reveals.forEach((item) => observer.observe(item));
    } else {
        reveals.forEach((item) => item.classList.add('in-view'));
    }
})();

(() => {
    const viewport = document.getElementById('reviewViewport');
    const track = document.getElementById('reviewTrack');
    const prevButton = document.getElementById('reviewPrev');
    const nextButton = document.getElementById('reviewNext');
    const dotsWrap = document.getElementById('reviewDots');

    if (!viewport || !track || !prevButton || !nextButton || !dotsWrap) {
        return;
    }

    const slides = Array.from(track.querySelectorAll('.testimonial-slide'));
    if (!slides.length) {
        return;
    }

    let index = 0;
    let timer = null;
    let startX = 0;

    function renderDots() {
        dotsWrap.innerHTML = slides.map((_, i) => `<button type="button" class="testimonial-dot${i === 0 ? ' is-active' : ''}" data-index="${i}" aria-label="Go to review ${i + 1}"></button>`).join('');
    }

    function update() {
        track.style.transform = `translateX(-${index * 100}%)`;
        slides.forEach((slide, i) => slide.classList.toggle('is-active', i === index));
        dotsWrap.querySelectorAll('.testimonial-dot').forEach((dot, i) => dot.classList.toggle('is-active', i === index));
    }

    function goTo(nextIndex) {
        index = (nextIndex + slides.length) % slides.length;
        update();
    }

    function stop() {
        window.clearInterval(timer);
        timer = null;
    }

    function start() {
        stop();
        timer = window.setInterval(() => goTo(index + 1), 5200);
    }

    renderDots();
    update();
    start();

    prevButton.addEventListener('click', () => {
        goTo(index - 1);
        start();
    });

    nextButton.addEventListener('click', () => {
        goTo(index + 1);
        start();
    });

    dotsWrap.addEventListener('click', (event) => {
        const dot = event.target.closest('.testimonial-dot');
        if (!dot) {
            return;
        }

        goTo(Number(dot.dataset.index || 0));
        start();
    });

    viewport.addEventListener('mouseenter', stop);
    viewport.addEventListener('mouseleave', start);

    viewport.addEventListener('touchstart', (event) => {
        if (!event.touches.length) {
            return;
        }

        startX = event.touches[0].clientX;
        stop();
    }, { passive: true });

    viewport.addEventListener('touchend', (event) => {
        if (!event.changedTouches.length) {
            return;
        }

        const deltaX = event.changedTouches[0].clientX - startX;
        if (Math.abs(deltaX) > 40) {
            goTo(deltaX < 0 ? index + 1 : index - 1);
        }

        start();
    }, { passive: true });
})();
</script>
@endpush
