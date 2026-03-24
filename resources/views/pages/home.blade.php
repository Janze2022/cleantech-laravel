@extends('layouts.app')

@section('title', 'CleanTech Solutions | Professional Cleaning Services')

@php
    $heroSlides = [
        'https://www.bobvila.com/wp-content/uploads/2022/03/The-Best-Cleaning-Services-Options.jpg?w=1128&h=752',
        'https://content.app-sources.com/s/34724871351514405/uploads/Images/Commercial_and_Office_Cleaning_Services_Near_Me-5581197.jpg',
        'https://images.unsplash.com/photo-1584622650111-993a426fbf0a',
    ];

    $workflowCards = [
        ['title' => 'Book Online', 'text' => 'Select your service, date, and location in minutes.', 'image' => 'https://t3.ftcdn.net/jpg/02/98/67/88/360_F_298678837_bNtbbc5QqtNZdinHQkPKddKKVq5WKlXl.jpg'],
        ['title' => 'Get Matched', 'text' => 'We assign a verified professional to your booking.', 'image' => 'https://t4.ftcdn.net/jpg/03/05/63/55/360_F_305635573_47SjydzWbcQPCTbkcfHyfD4fUY81XW9R.jpg'],
        ['title' => 'Relax & Enjoy', 'text' => 'Come home to a clean, fresh, and peaceful space.', 'image' => 'https://images.pexels.com/photos/48889/cleaning-washing-cleanup-the-ilo-48889.jpeg?auto=compress&cs=tinysrgb&dpr=1&w=500'],
    ];

    $serviceCards = [
        ['title' => 'Deep Home Cleaning', 'text' => 'Top-to-bottom professional cleaning.', 'image' => 'https://hongkongofw.com/wp-content/uploads/2023/06/deep-cleaning.jpg'],
        ['title' => 'Office Cleaning', 'text' => 'Keep your workspace spotless.', 'image' => 'https://lirp.cdn-website.com/4403d184/dms3rep/multi/opt/AdobeStock_267548289-1920w.jpeg'],
        ['title' => 'Post Construction', 'text' => 'Detailed cleanup after renovations.', 'image' => 'https://images.unsplash.com/photo-1584622650111-993a426fbf0a'],
    ];

    $testimonials = [
        ['name' => 'Janze Salva', 'review' => 'The booking flow felt smooth from start to finish. It was easy to choose a service, confirm the schedule, and stay updated without confusion.', 'rating' => 5],
        ['name' => 'Maria Santos', 'review' => 'I liked how simple everything was to follow. The provider arrived on time, the service felt organized, and the whole experience looked professional.', 'rating' => 5],
        ['name' => 'Ronald Saballe', 'review' => 'CleanTech made it easier to arrange cleaning for our place without the usual back and forth. The site felt fast, clear, and dependable.', 'rating' => 5],
        ['name' => 'Aileen Cruz', 'review' => 'What stood out most was the clear booking process and consistent updates. It felt like a modern service website that actually guides the customer well.', 'rating' => 5],
    ];
@endphp

@push('styles')
<style>
.home-page{color:#e5e7eb;overflow:hidden}
.home-page .container{position:relative;z-index:1}
.home-hero{position:relative;min-height:calc(100vh - var(--nav-h));display:flex;align-items:center;padding:40px 0 30px;overflow:hidden}
.home-hero-slide{position:absolute;inset:0;background-size:cover;background-position:center;opacity:0;transform:scale(1.05);animation:heroFade 18s infinite}
.home-hero-slide::after{content:"";position:absolute;inset:0;background:linear-gradient(90deg,rgba(2,6,23,.92),rgba(2,6,23,.76) 45%,rgba(2,6,23,.68))}
.home-hero-slide:nth-child(1){animation-delay:0s}.home-hero-slide:nth-child(2){animation-delay:6s}.home-hero-slide:nth-child(3){animation-delay:12s}
@keyframes heroFade{0%{opacity:0;transform:scale(1.05)}8%{opacity:1}30%{opacity:1;transform:scale(1)}38%{opacity:0}100%{opacity:0}}
@keyframes fadeUp{from{opacity:0;transform:translateY(24px)}to{opacity:1;transform:translateY(0)}}
.reveal{opacity:0;transform:translateY(26px);transition:opacity .7s ease,transform .7s ease;transition-delay:var(--delay,0s)}.reveal.in-view{opacity:1;transform:translateY(0)}
.hero-grid{display:grid;grid-template-columns:minmax(0,1.08fr) minmax(320px,.92fr);gap:26px;align-items:center}
.hero-kicker{display:inline-flex;align-items:center;gap:10px;padding:8px 14px;border-radius:999px;border:1px solid rgba(56,189,248,.22);background:rgba(56,189,248,.10);color:#d6f3ff;font-size:.8rem;font-weight:900;letter-spacing:.12em;text-transform:uppercase;animation:fadeUp .7s ease both}
.hero-kicker::before{content:"";width:8px;height:8px;border-radius:999px;background:#38bdf8;box-shadow:0 0 0 6px rgba(56,189,248,.14)}
.hero-title{margin:18px 0 14px;max-width:760px;font-size:clamp(2.7rem,5vw,4.4rem);line-height:.98;font-weight:950;letter-spacing:-.05em;color:#fff;animation:fadeUp .82s ease both .08s}
.hero-title span{color:#8fdbff}
.hero-text{margin:0;max-width:620px;color:#cad7ec;font-size:1.03rem;line-height:1.75;animation:fadeUp .9s ease both .16s}
.hero-actions{display:flex;flex-wrap:wrap;gap:14px;margin-top:28px;animation:fadeUp .95s ease both .24s}
.home-btn{display:inline-flex;align-items:center;justify-content:center;min-height:50px;padding:.86rem 1.45rem;border-radius:16px;border:1px solid rgba(56,189,248,.26);background:linear-gradient(135deg,#2563eb,#6366f1);color:#fff;text-decoration:none;font-weight:900;box-shadow:0 14px 30px rgba(37,99,235,.30);transition:transform .22s ease,box-shadow .22s ease,background .22s ease,border-color .22s ease}
.home-btn:hover{color:#fff;transform:translateY(-2px);box-shadow:0 18px 38px rgba(37,99,235,.38)}
.home-btn.secondary{background:rgba(255,255,255,.04);border-color:rgba(255,255,255,.12);box-shadow:none;color:#edf3ff}
.home-btn.secondary:hover{background:rgba(56,189,248,.08);border-color:rgba(56,189,248,.22)}
.hero-panel,.strip-card,.content-card,.testimonial-shell,.promo-card,.mission-card,.cta-card{border:1px solid rgba(255,255,255,.08);background:linear-gradient(180deg,rgba(9,18,36,.95),rgba(4,11,24,.98));box-shadow:0 24px 60px rgba(0,0,0,.28)}
.hero-panel{padding:20px;border-radius:28px;backdrop-filter:blur(18px);animation:fadeUp 1s ease both .3s}
.hero-panel-top{display:flex;align-items:center;justify-content:space-between;gap:14px;margin-bottom:16px}
.panel-label{display:block;color:#8ea4c7;font-size:.76rem;font-weight:900;letter-spacing:.12em;text-transform:uppercase}.panel-value{margin-top:5px;color:#fff;font-size:1.12rem;font-weight:900}.panel-chip{padding:8px 12px;border-radius:999px;border:1px solid rgba(255,255,255,.10);background:rgba(255,255,255,.04);color:#dce7fb;font-size:.78rem;font-weight:800;white-space:nowrap}
.hero-points{display:grid;gap:12px}
.hero-point{display:grid;grid-template-columns:62px 1fr;gap:12px;align-items:center;padding:12px;border-radius:20px;border:1px solid rgba(255,255,255,.08);background:rgba(255,255,255,.03);transition:transform .22s ease,border-color .22s ease,background .22s ease}
.hero-point:hover{transform:translateY(-2px);border-color:rgba(56,189,248,.18);background:rgba(56,189,248,.06)}
.hero-point img{width:62px;height:62px;object-fit:cover;border-radius:16px}.hero-point h3{margin:0 0 4px;color:#fff;font-size:1rem;font-weight:900}.hero-point p{margin:0;color:#9eb1cf;font-size:.9rem;line-height:1.55}
.home-strip{margin-top:-34px;padding-bottom:14px}.strip-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:16px}.strip-card{padding:18px 20px;border-radius:24px}.strip-card h3{margin:0 0 6px;color:#fff;font-size:1.05rem;font-weight:900}.strip-card p{margin:0;color:#93a7c6;line-height:1.6;font-size:.92rem}
.home-section{padding:82px 0}.home-section.compact{padding:70px 0}.section-head{display:flex;align-items:flex-end;justify-content:space-between;gap:18px;flex-wrap:wrap;margin-bottom:28px}.section-head.center{justify-content:center;text-align:center}.section-title{margin:0;color:#fff;font-size:clamp(1.8rem,3vw,2.55rem);font-weight:950;letter-spacing:-.03em}.section-copy{margin:10px 0 0;max-width:720px;color:#95a8c8;font-size:1rem;line-height:1.72}
.work-grid,.services-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:22px}
.content-card{overflow:hidden;border-radius:26px;transition:transform .26s ease,box-shadow .26s ease,border-color .26s ease}.content-card:hover{transform:translateY(-7px);border-color:rgba(56,189,248,.20);box-shadow:0 28px 70px rgba(0,0,0,.36)}
.card-media{position:relative;height:300px;overflow:hidden}.card-media::after{content:"";position:absolute;inset:0;background:linear-gradient(180deg,rgba(3,7,18,.08),rgba(3,7,18,.76))}.card-media img{width:100%;height:100%;object-fit:cover;transition:transform .5s ease}.content-card:hover .card-media img{transform:scale(1.05)}
.card-overlay{position:absolute;inset:auto 0 0 0;padding:24px 22px 22px;z-index:1}.card-overlay h3{margin:0 0 6px;color:#fff;font-size:1.15rem;font-weight:900}.card-overlay p{margin:0;color:#d3def0;font-size:.94rem;line-height:1.6}
.service-card .card-media{height:220px}.service-content{padding:20px}.service-tag{display:inline-flex;align-items:center;min-height:32px;padding:7px 12px;border-radius:999px;background:rgba(56,189,248,.10);border:1px solid rgba(56,189,248,.18);color:#d8f3ff;font-size:.76rem;font-weight:900;letter-spacing:.08em;text-transform:uppercase;margin-bottom:12px}.service-content h3{margin:0 0 8px;color:#fff;font-size:1.12rem;font-weight:900}.service-content p{margin:0;color:#94a7c6;line-height:1.65}
.testimonial-shell{padding:26px;border-radius:32px;background:radial-gradient(circle at top right,rgba(56,189,248,.08),transparent 26%),linear-gradient(180deg,rgba(9,18,36,.96),rgba(4,11,24,.98))}
.testimonial-top{display:flex;align-items:flex-start;justify-content:space-between;gap:18px;flex-wrap:wrap;margin-bottom:24px}.testimonial-controls{display:flex;gap:10px}.testimonial-arrow{width:46px;height:46px;border-radius:16px;border:1px solid rgba(255,255,255,.10);background:rgba(255,255,255,.04);color:#fff;display:inline-flex;align-items:center;justify-content:center;font-size:1.2rem;transition:transform .22s ease,background .22s ease,border-color .22s ease}.testimonial-arrow:hover{transform:translateY(-1px);background:rgba(56,189,248,.10);border-color:rgba(56,189,248,.20)}
.testimonial-viewport{overflow:hidden}.testimonial-track{display:flex;transition:transform .72s cubic-bezier(.22,.61,.36,1);will-change:transform}.testimonial-slide{min-width:100%;flex:0 0 100%;padding:2px}.testimonial-card{display:grid;grid-template-columns:minmax(0,.9fr) minmax(0,1.1fr);gap:22px;padding:26px;border-radius:26px;border:1px solid rgba(255,255,255,.08);background:linear-gradient(180deg,rgba(255,255,255,.04),rgba(255,255,255,.02));backdrop-filter:blur(16px);transform:scale(.985);transition:transform .45s ease,box-shadow .45s ease,border-color .45s ease}.testimonial-slide.is-active .testimonial-card{transform:scale(1);border-color:rgba(56,189,248,.18);box-shadow:0 18px 48px rgba(0,0,0,.22)}
.testimonial-side{padding-right:10px;border-right:1px solid rgba(255,255,255,.07)}.quote-mark{width:52px;height:52px;border-radius:18px;display:grid;place-items:center;background:rgba(56,189,248,.12);border:1px solid rgba(56,189,248,.18);color:#cdefff;font-size:1.7rem;font-weight:900;margin-bottom:16px}.testimonial-name{margin:0;color:#fff;font-size:1.2rem;font-weight:900}.testimonial-role{margin-top:6px;color:#8ca1c4;font-size:.85rem;font-weight:800;letter-spacing:.08em;text-transform:uppercase}.testimonial-text{margin:0;color:#d7e2f3;font-size:1.02rem;line-height:1.85}.stars{display:flex;gap:4px;margin-top:14px}.stars span{color:#fbbf24;font-size:1rem}
.testimonial-dots{display:flex;justify-content:center;align-items:center;gap:10px;margin-top:20px}.testimonial-dot{width:10px;height:10px;border:none;border-radius:999px;background:rgba(255,255,255,.18);transition:width .22s ease,background .22s ease}.testimonial-dot.is-active{width:34px;background:linear-gradient(135deg,#38bdf8,#6366f1)}
.home-promo,.home-mission,.home-cta{padding:76px 0}.promo-card{padding:42px 44px;border-radius:32px;background:linear-gradient(90deg,rgba(15,23,42,.96) 0%,rgba(15,23,42,.86) 40%,rgba(15,23,42,.55) 100%),url('https://images.unsplash.com/photo-1581578731548-c64695cc6952') center/cover no-repeat}.mission-card{padding:42px;border-radius:32px;background:linear-gradient(180deg,rgba(6,13,24,.76),rgba(6,13,24,.9)),url('https://images.unsplash.com/photo-1600585153490-76fb20a32601') center/cover no-repeat}
.promo-card h2,.mission-card h2,.cta-card h2{margin:0 0 12px;color:#fff;font-weight:950;letter-spacing:-.03em}.promo-card p,.mission-card p,.cta-card p{margin:0;color:#d7e4f6;max-width:640px;line-height:1.75}.promo-actions,.cta-actions{display:flex;flex-wrap:wrap;gap:14px;margin-top:22px}
.mission-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:16px;margin-top:26px}.mission-item{padding:18px;border-radius:22px;border:1px solid rgba(255,255,255,.09);background:rgba(255,255,255,.05);backdrop-filter:blur(10px)}.mission-item h3{margin:0 0 6px;color:#fff;font-size:1rem;font-weight:900}.mission-item p{margin:0;color:#d2deef;font-size:.92rem;line-height:1.65}
.home-cta{padding-bottom:94px}.cta-card{text-align:center;padding:44px 30px;border-radius:30px;border:1px solid rgba(56,189,248,.16);background:linear-gradient(135deg,rgba(37,99,235,.20),rgba(79,70,229,.18));box-shadow:0 30px 74px rgba(17,24,39,.34)}.cta-card p{margin:0 auto}
@media (max-width:1199.98px){.hero-grid,.testimonial-card{grid-template-columns:1fr}.strip-grid,.work-grid,.services-grid,.mission-grid{grid-template-columns:repeat(2,minmax(0,1fr))}.testimonial-side{border-right:none;border-bottom:1px solid rgba(255,255,255,.07);padding-right:0;padding-bottom:16px}}
@media (max-width:767.98px){.home-hero{min-height:auto;padding:30px 0 28px}.home-section,.home-promo,.home-mission,.home-cta{padding:62px 0}.home-strip{margin-top:0;padding-top:6px}.strip-grid,.work-grid,.services-grid,.mission-grid,.testimonial-card{grid-template-columns:1fr}.hero-panel,.testimonial-shell,.promo-card,.mission-card,.cta-card{padding:22px}.hero-title{font-size:2.35rem}.hero-actions,.promo-actions,.cta-actions{flex-direction:column}.home-btn{width:100%}.card-media{height:270px}.service-card .card-media{height:210px}}
</style>
@endpush

@section('content')
<div class="home-page">
    <section class="home-hero">
        @foreach ($heroSlides as $slide)
            <div class="home-hero-slide" style="background-image:url('{{ $slide }}')"></div>
        @endforeach

        <div class="container">
            <div class="hero-grid">
                <div>
                    <span class="hero-kicker">Professional Cleaning Services</span>
                    <h1 class="hero-title">Clean home service that <span>feels easier to book</span> and easier to trust.</h1>
                    <p class="hero-text">Professional home and office cleaning services in Butuan City, with a smoother booking flow, verified providers, and a more guided customer experience from start to finish.</p>
                    <div class="hero-actions">
                        <a href="{{ route('customer.register') }}" class="home-btn">Book a Service</a>
                        <a href="{{ route('provider.pre_register.terms') }}" class="home-btn secondary">Become a Provider</a>
                    </div>
                </div>

                <div class="hero-panel">
                    <div class="hero-panel-top">
                        <div>
                            <span class="panel-label">Booking Flow</span>
                            <div class="panel-value">Simple, guided, and easy to follow</div>
                        </div>
                        <div class="panel-chip">Modern service experience</div>
                    </div>
                    <div class="hero-points">
                        @foreach ($workflowCards as $card)
                            <div class="hero-point">
                                <img src="{{ $card['image'] }}" alt="{{ $card['title'] }}">
                                <div>
                                    <h3>{{ $card['title'] }}</h3>
                                    <p>{{ $card['text'] }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="home-strip">
        <div class="container">
            <div class="strip-grid">
                <div class="strip-card reveal">
                    <h3>Easy booking</h3>
                    <p>Schedule service faster with a cleaner, more guided booking flow.</p>
                </div>
                <div class="strip-card reveal" style="--delay:.08s">
                    <h3>Verified providers</h3>
                    <p>Work with approved professionals through a more organized platform.</p>
                </div>
                <div class="strip-card reveal" style="--delay:.16s">
                    <h3>Clear updates</h3>
                    <p>Follow your booking progress without the usual confusion and back-and-forth.</p>
                </div>
            </div>
        </div>
    </section>

    <section class="home-section compact">
        <div class="container">
            <div class="section-head center reveal">
                <div>
                    <h2 class="section-title">How CleanTech Works</h2>
                    <p class="section-copy">From booking to spotless, made simple.</p>
                </div>
            </div>
            <div class="work-grid">
                @foreach ($workflowCards as $index => $card)
                    <article class="content-card reveal" style="--delay:{{ $index * 0.08 }}s">
                        <div class="card-media">
                            <img src="{{ $card['image'] }}" alt="{{ $card['title'] }}">
                        </div>
                        <div class="card-overlay">
                            <h3>{{ $card['title'] }}</h3>
                            <p>{{ $card['text'] }}</p>
                        </div>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    <section class="home-section">
        <div class="container">
            <div class="section-head center reveal">
                <div>
                    <h2 class="section-title">We’ve got what you need</h2>
                    <p class="section-copy">Professional services for every space.</p>
                </div>
            </div>
            <div class="services-grid">
                @foreach ($serviceCards as $index => $card)
                    <article class="content-card service-card reveal" style="--delay:{{ $index * 0.08 }}s">
                        <div class="card-media">
                            <img src="{{ $card['image'] }}" alt="{{ $card['title'] }}">
                        </div>
                        <div class="service-content">
                            <span class="service-tag">Professional Service</span>
                            <h3>{{ $card['title'] }}</h3>
                            <p>{{ $card['text'] }}</p>
                        </div>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    <section class="home-section">
        <div class="container">
            <div class="testimonial-shell reveal">
                <div class="testimonial-top">
                    <div>
                        <h2 class="section-title">Satisfied Customers</h2>
                        <p class="section-copy">From customer service to operations, we aim to provide a consistent quality of service to our valuable clients, ensuring that each of them gets to experience quality and standardized service.</p>
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
                                        <div class="quote-mark">“</div>
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

    <section class="home-promo">
        <div class="container">
            <div class="promo-card reveal">
                <h2>The best cleaners are ready for your home</h2>
                <p>Reliable. Vetted. Professional.</p>
                <div class="promo-actions">
                    <a href="{{ route('customer.register') }}" class="home-btn">Book Now</a>
                    <a href="{{ route('services') }}" class="home-btn secondary">View Services</a>
                </div>
            </div>
        </div>
    </section>

    <section class="home-mission">
        <div class="container">
            <div class="mission-card reveal">
                <h2>A healthier, cleaner home</h2>
                <p>CleanTech connects you with trusted professionals. Verified providers, transparent booking, and a service experience designed to feel more organized every step of the way.</p>
                <div class="mission-grid">
                    <div class="mission-item">
                        <h3>Trusted providers</h3>
                        <p>Work with professionals who are reviewed before they appear on the platform.</p>
                    </div>
                    <div class="mission-item">
                        <h3>Clear process</h3>
                        <p>Book, confirm, and follow updates with less guesswork and less friction.</p>
                    </div>
                    <div class="mission-item">
                        <h3>Consistent service</h3>
                        <p>Designed to help customers experience a cleaner, more reliable service journey.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="home-cta">
        <div class="container">
            <div class="cta-card reveal">
                <h2>Book. Clean. Relax.</h2>
                <p>A modern way to keep your space spotless.</p>
                <div class="cta-actions">
                    <a href="{{ route('customer.register') }}" class="home-btn">Get Started</a>
                    <a href="{{ route('provider.pre_register.terms') }}" class="home-btn secondary">Become a Provider</a>
                </div>
            </div>
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
    if (!viewport || !track || !prevButton || !nextButton || !dotsWrap) return;

    const slides = Array.from(track.querySelectorAll('.testimonial-slide'));
    if (!slides.length) return;

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

    prevButton.addEventListener('click', () => { goTo(index - 1); start(); });
    nextButton.addEventListener('click', () => { goTo(index + 1); start(); });

    dotsWrap.addEventListener('click', (event) => {
        const dot = event.target.closest('.testimonial-dot');
        if (!dot) return;
        goTo(Number(dot.dataset.index || 0));
        start();
    });

    viewport.addEventListener('mouseenter', stop);
    viewport.addEventListener('mouseleave', start);

    viewport.addEventListener('touchstart', (event) => {
        if (!event.touches.length) return;
        startX = event.touches[0].clientX;
        stop();
    }, { passive: true });

    viewport.addEventListener('touchend', (event) => {
        if (!event.changedTouches.length) return;
        const deltaX = event.changedTouches[0].clientX - startX;
        if (Math.abs(deltaX) > 40) {
            goTo(deltaX < 0 ? index + 1 : index - 1);
        }
        start();
    }, { passive: true });
})();
</script>
@endpush
