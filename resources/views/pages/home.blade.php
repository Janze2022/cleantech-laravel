@extends('layouts.app')

@section('title', 'CleanTech Solutions | Professional Cleaning Services')

@php
    $avatarPresets = [
        ['bg' => '#2563eb', 'accent' => '#38bdf8', 'text' => '#f8fbff'],
        ['bg' => '#7c3aed', 'accent' => '#a78bfa', 'text' => '#f8fbff'],
        ['bg' => '#0f766e', 'accent' => '#2dd4bf', 'text' => '#f4fffe'],
        ['bg' => '#b45309', 'accent' => '#fbbf24', 'text' => '#fff8eb'],
        ['bg' => '#be123c', 'accent' => '#fb7185', 'text' => '#fff4f6'],
    ];

    $buildAvatar = static function (string $name, int $index) use ($avatarPresets): string {
        $parts = preg_split('/\s+/', trim($name)) ?: [];
        $initials = collect($parts)
            ->filter()
            ->take(2)
            ->map(fn ($part) => strtoupper(mb_substr($part, 0, 1)))
            ->implode('');

        if ($initials === '') {
            $initials = 'CT';
        }

        $palette = $avatarPresets[$index % count($avatarPresets)];
        $svg = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 160 160">
  <defs>
    <linearGradient id="g" x1="0%" y1="0%" x2="100%" y2="100%">
      <stop offset="0%" stop-color="{$palette['accent']}"/>
      <stop offset="100%" stop-color="{$palette['bg']}"/>
    </linearGradient>
  </defs>
  <rect width="160" height="160" rx="44" fill="url(#g)"/>
  <circle cx="80" cy="80" r="58" fill="rgba(255,255,255,0.12)"/>
  <text x="80" y="95" text-anchor="middle" font-family="Segoe UI, Arial, sans-serif" font-size="54" font-weight="700" fill="{$palette['text']}">{$initials}</text>
</svg>
SVG;

        return 'data:image/svg+xml;charset=UTF-8,' . rawurlencode($svg);
    };

    $testimonials = [
        [
            'name' => 'Janze Salva',
            'review' => 'The booking flow felt smooth and easy to follow. From choosing a service to receiving updates, everything felt more organized and less stressful.',
            'rating' => 5,
            'role' => 'General Cleaning',
        ],
        [
            'name' => 'Maria Santos',
            'review' => 'I liked how simple the process was. The provider arrived on time, the service felt professional, and the overall experience looked clean and modern.',
            'rating' => 5,
            'role' => 'Deep Cleaning',
        ],
        [
            'name' => 'Ronald Saballe',
            'review' => 'CleanTech made it easier to arrange cleaning without the usual back and forth. It felt reliable, clear, and well guided from start to finish.',
            'rating' => 5,
            'role' => 'Specific Area Cleaning',
        ],
        [
            'name' => 'Aileen Cruz',
            'review' => 'The site was easy to understand and the updates were clear. It feels like a professional service platform that actually helps customers book with confidence.',
            'rating' => 5,
            'role' => 'General Cleaning',
        ],
        [
            'name' => 'Kyla Ramirez',
            'review' => 'I booked from my phone and it only took a few minutes. The provider arrived prepared and the house felt fresh right after the visit.',
            'rating' => 5,
            'role' => 'Deep Cleaning',
        ],
        [
            'name' => 'Lester Dela Cruz',
            'review' => 'The progress updates helped a lot. I always knew when the booking was confirmed and when the provider was already on the way.',
            'rating' => 5,
            'role' => 'Specific Area Cleaning',
        ],
        [
            'name' => 'Sheila Gomez',
            'review' => 'What I liked most was the clear schedule and professional approach. It felt safe, organized, and worth booking again.',
            'rating' => 5,
            'role' => 'General Cleaning',
        ],
        [
            'name' => 'Marco Villanueva',
            'review' => 'The provider handled the service well and the platform looked trustworthy. The steps were simple enough even for first-time users.',
            'rating' => 5,
            'role' => 'Deep Cleaning',
        ],
        [
            'name' => 'Nica Fernandez',
            'review' => 'I appreciated how clean the website looked and how easy it was to choose a service. It made the whole process feel premium.',
            'rating' => 5,
            'role' => 'Specific Area Cleaning',
        ],
        [
            'name' => 'Paolo Mendez',
            'review' => 'From booking to completion, the flow stayed clear and professional. I would recommend it to anyone looking for a reliable cleaning service.',
            'rating' => 5,
            'role' => 'General Cleaning',
        ],
    ];

    $testimonials = array_map(function (array $testimonial, int $index) use ($buildAvatar) {
        $testimonial['avatar'] = $buildAvatar($testimonial['name'], $index);
        return $testimonial;
    }, $testimonials, array_keys($testimonials));
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

.hero h1,
.hero p,
.hero-actions {
    animation: fadeUp .85s ease both;
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

.steps-section {
    padding: 4.75rem 0 2rem;
}

.steps-shell {
    padding: 2.25rem;
    border-radius: 30px;
    border: 1px solid rgba(255,255,255,.06);
    background:
        radial-gradient(circle at top center, rgba(56,189,248,.07), transparent 32%),
        linear-gradient(180deg, rgba(11,18,34,.98), rgba(7,13,25,.98));
    box-shadow: 0 24px 60px rgba(0,0,0,.28);
}

.steps-grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 28px;
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

.testimonial-viewport {
    overflow: hidden;
    padding: 4px 2px 6px;
}

.testimonial-track {
    display: flex;
    transition: transform .72s cubic-bezier(.22, .61, .36, 1);
    will-change: transform;
}

.testimonial-slide {
    min-width: 33.3333%;
    flex: 0 0 33.3333%;
    padding: 10px;
}

.testimonial-card {
    display: flex;
    flex-direction: column;
    gap: 18px;
    height: 100%;
    min-height: 100%;
    padding: 24px;
    border-radius: 26px;
    border: 1px solid rgba(255,255,255,.08);
    background: linear-gradient(180deg, rgba(255,255,255,.04), rgba(255,255,255,.02));
    backdrop-filter: blur(16px);
    transform: translateY(0) scale(.985);
    transition: transform .45s ease, box-shadow .45s ease, border-color .45s ease, opacity .45s ease;
}

.testimonial-slide.is-active .testimonial-card {
    transform: translateY(-4px) scale(1);
    border-color: rgba(56,189,248,.18);
    box-shadow: 0 18px 48px rgba(0,0,0,.22);
}

.testimonial-head {
    display: flex;
    align-items: center;
    gap: 14px;
}

.testimonial-avatar {
    width: 68px;
    height: 68px;
    flex: 0 0 68px;
    border-radius: 22px;
    object-fit: cover;
    border: 1px solid rgba(255,255,255,.10);
    box-shadow: 0 14px 28px rgba(0,0,0,.24);
}

.testimonial-meta {
    min-width: 0;
}

.testimonial-name {
    margin: 0;
    color: #fff;
    font-size: 1.05rem;
    font-weight: 900;
}

.testimonial-role {
    margin-top: 4px;
    color: #9cb0ce;
    font-size: .78rem;
    font-weight: 800;
    letter-spacing: .08em;
    text-transform: uppercase;
}

.testimonial-text {
    margin: 0;
    color: #d7e2f3;
    font-size: .97rem;
    line-height: 1.78;
    flex: 1;
}

.stars {
    display: flex;
    gap: 4px;
    margin-top: 2px;
}

.stars span {
    color: #fbbf24;
    font-size: 1rem;
}

.testimonial-foot {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    padding-top: 12px;
    border-top: 1px solid rgba(255,255,255,.06);
}

.testimonial-badge {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 8px 12px;
    border-radius: 999px;
    background: rgba(56,189,248,.10);
    border: 1px solid rgba(56,189,248,.15);
    color: #d8f1ff;
    font-size: .74rem;
    font-weight: 800;
    letter-spacing: .08em;
    text-transform: uppercase;
}

.testimonial-badge::before {
    content: "";
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: #38bdf8;
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
    .steps-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .testimonial-slide {
        min-width: 50%;
        flex-basis: 50%;
    }
}

@media (max-width: 767.98px) {
    .hero {
        min-height: 78vh;
        padding: 82px 0 34px;
    }

    .steps-section,
    .testimonials-section,
    .services-showcase,
    .promo-banner,
    .mission {
        padding: 3.2rem 0;
    }

    .hero-content {
        width: 100%;
        padding-inline: 4px;
    }

    .hero h1 {
        font-size: clamp(2.7rem, 14vw, 3.2rem);
        line-height: .98;
        margin-bottom: .75rem;
    }

    .hero p {
        max-width: 320px;
        margin: 0 auto 1.5rem;
        font-size: 1rem;
        line-height: 1.55;
    }

    .hero-actions {
        flex-direction: column;
        gap: 10px;
        width: 100%;
        max-width: 300px;
        margin-inline: auto;
    }

    .button-txt,
    .button-outline {
        width: 100%;
        min-height: 50px;
        padding: .76rem 1.15rem;
        border-radius: 16px;
        font-size: .92rem;
    }

    .section-title {
        margin-bottom: 1.45rem;
    }

    .section-title h2 {
        font-size: 2rem;
        line-height: 1.08;
    }

    .section-title p {
        font-size: .98rem;
        line-height: 1.6;
    }

    .steps-shell,
    .testimonial-shell {
        padding: 18px 14px;
        border-radius: 24px;
    }

    .steps-grid {
        grid-template-columns: 1fr;
        gap: 14px;
    }

    .work-image-card {
        height: 220px;
        border-radius: 18px;
    }

    .work-overlay {
        padding: 1.15rem;
    }

    .work-overlay h5 {
        font-size: 1.45rem;
        margin-bottom: .28rem;
    }

    .work-overlay p {
        font-size: .9rem;
        line-height: 1.5;
    }

    .testimonial-top {
        gap: 14px;
        justify-content: center;
        text-align: center;
        margin-bottom: 16px;
    }

    .testimonial-top .section-title {
        text-align: center !important;
        margin-bottom: 0;
    }

    .testimonial-top .section-title p {
        max-width: none;
    }

    .testimonial-slide {
        min-width: 100%;
        flex-basis: 100%;
        padding: 6px 0;
    }

    .testimonial-card {
        padding: 18px;
        border-radius: 22px;
        gap: 14px;
    }

    .testimonial-head {
        gap: 12px;
    }

    .testimonial-avatar {
        width: 56px;
        height: 56px;
        flex-basis: 56px;
        border-radius: 18px;
    }

    .testimonial-name {
        font-size: 1rem;
    }

    .testimonial-role {
        font-size: .72rem;
    }

    .testimonial-text {
        font-size: .93rem;
        line-height: 1.64;
    }

    .testimonial-foot {
        flex-direction: column;
        align-items: flex-start;
        gap: 8px;
    }

    .testimonial-controls {
        width: 100%;
        justify-content: center;
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
            <h1>CleanTech</h1>
            <p>Professional home and office cleaning services in Butuan City.</p>
            <div class="hero-actions d-flex justify-content-center gap-3 flex-wrap">
                <a href="{{ route('customer.register') }}" class="button-txt">Book a Service</a>
                <a href="{{ route('provider.pre_register.terms') }}" class="button-outline">Become a Provider</a>
            </div>
        </div>
    </section>

    <section class="steps-section">
        <div class="container">
            <div class="steps-shell reveal">
                <div class="section-title">
                    <h2>How CleanTech Works</h2>
                    <p>From booking to spotless made simple</p>
                </div>

                <div class="steps-grid">
                    <div class="reveal">
                        <div class="work-image-card">
                            <img src="https://t3.ftcdn.net/jpg/02/98/67/88/360_F_298678837_bNtbbc5QqtNZdinHQkPKddKKVq5WKlXl.jpg" alt="Book cleaning online">
                            <div class="work-overlay">
                                <h5>Book Online</h5>
                                <p>Select your service, date, and location in minutes.</p>
                            </div>
                        </div>
                    </div>

                    <div class="reveal" style="--delay:.08s">
                        <div class="work-image-card">
                            <img src="https://t4.ftcdn.net/jpg/03/05/63/55/360_F_305635573_47SjydzWbcQPCTbkcfHyfD4fUY81XW9R.jpg" alt="Professional cleaner">
                            <div class="work-overlay">
                                <h5>Get Matched</h5>
                                <p>We assign a verified professional to your booking.</p>
                            </div>
                        </div>
                    </div>

                    <div class="reveal" style="--delay:.16s">
                        <div class="work-image-card">
                            <img src="https://images.pexels.com/photos/48889/cleaning-washing-cleanup-the-ilo-48889.jpeg?auto=compress&cs=tinysrgb&dpr=1&w=500" alt="Clean home">
                            <div class="work-overlay">
                                <h5>Relax & Enjoy</h5>
                                <p>Come home to a clean, fresh, and peaceful space.</p>
                            </div>
                        </div>
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

                </div>

                <div class="testimonial-viewport" id="reviewViewport">
                    <div class="testimonial-track" id="reviewTrack">
                        @foreach ($testimonials as $testimonial)
                            <div class="testimonial-slide">
                                <article class="testimonial-card">
                                    <div class="testimonial-head">
                                        <img src="{{ $testimonial['avatar'] }}" alt="{{ $testimonial['name'] }} avatar" class="testimonial-avatar">
                                        <div class="testimonial-meta">
                                            <h3 class="testimonial-name">{{ $testimonial['name'] }}</h3>
                                            <div class="testimonial-role">{{ $testimonial['role'] }}</div>
                                            <div class="stars" aria-label="{{ $testimonial['rating'] }} out of 5 stars">
                                                @for ($star = 0; $star < $testimonial['rating']; $star++)
                                                    <span>&#9733;</span>
                                                @endfor
                                            </div>
                                        </div>
                                    </div>

                                    <p class="testimonial-text">&ldquo;{{ $testimonial['review'] }}&rdquo;</p>

                                    <div class="testimonial-foot">
                                        <span class="testimonial-badge">Verified Feedback</span>
                                    </div>
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
    const dotsWrap = document.getElementById('reviewDots');

    if (!viewport || !track || !dotsWrap) {
        return;
    }

    const slides = Array.from(track.querySelectorAll('.testimonial-slide'));
    if (!slides.length) {
        return;
    }

    let index = 0;
    let timer = null;
    let startX = 0;
    let slidesPerView = 1;
    let pageCount = slides.length;

    function getSlidesPerView() {
        if (window.innerWidth >= 1200) {
            return 3;
        }

        if (window.innerWidth >= 768) {
            return 2;
        }

        return 1;
    }

    function buildPages() {
        slidesPerView = getSlidesPerView();
        pageCount = Math.max(1, Math.ceil(slides.length / slidesPerView));
        index = Math.min(index, pageCount - 1);
    }

    function renderDots() {
        dotsWrap.innerHTML = Array.from({ length: pageCount }, (_, i) => `<button type="button" class="testimonial-dot${i === 0 ? ' is-active' : ''}" data-index="${i}" aria-label="Go to review page ${i + 1}"></button>`).join('');
    }

    function update() {
        const offset = (100 / slidesPerView) * index;
        track.style.transform = `translateX(-${offset}%)`;
        slides.forEach((slide, i) => {
            const pageIndex = Math.floor(i / slidesPerView);
            slide.classList.toggle('is-active', pageIndex === index);
        });
        dotsWrap.querySelectorAll('.testimonial-dot').forEach((dot, i) => dot.classList.toggle('is-active', i === index));
    }

    function goTo(nextIndex) {
        index = (nextIndex + pageCount) % pageCount;
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

    buildPages();
    renderDots();
    update();
    start();

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

    window.addEventListener('resize', () => {
        const nextSlidesPerView = getSlidesPerView();
        if (nextSlidesPerView === slidesPerView) {
            return;
        }

        buildPages();
        renderDots();
        update();
        start();
    });
})();
</script>
@endpush
