@extends('layouts.app')

@section('title', 'Pending Approval')

@section('content')
<style>
    :root{
        --ct-text:#e5e7eb;
        --ct-muted:#94a3b8;
        --ct-primary:#3b82f6;
        --ct-primary2:#2563eb;
        --ct-shadow:0 18px 60px rgba(0,0,0,.55);
        --ct-radius:22px;
    }

    /*
      IMPORTANT:
      This page is INSIDE .provider-content which already has padding-top.
      So DO NOT subtract navbar height here.
    */
    .pending-page{
        position:relative;

        /* fill the content area */
        min-height: calc(100dvh - 96px); /* matches .provider-content padding-top on desktop */

        display:grid;
        place-items:center;

        /* ensures it doesn't look tight on any device */
        padding: clamp(16px, 3vw, 36px);

        overflow:hidden;
        isolation:isolate; /* keeps overlay stacking clean */
        border-radius: 18px; /* nice inside provider-content */
    }

    /* On small screens, your provider-content uses padding-top:88px */
    @media (max-width: 991px){
        .pending-page{
            min-height: calc(100dvh - 88px);
            border-radius: 16px;
        }
    }

    /* If screen is short, don't cut — allow scroll */
    @media (max-height: 720px){
        .pending-page{
            place-items:start center;
            overflow:auto;
            -webkit-overflow-scrolling: touch;
        }
    }

    /* =========================
       BACKGROUND SLIDESHOW (UNCHANGED)
    ========================= */
    .pending-slide{
        position:absolute;
        inset:0;
        background-size:cover;
        background-position:center;
        opacity:0;
        animation:fadeSlide 18s infinite;
        z-index:0;
        transform: scale(1.03);
    }

    .pending-slide.one{ background-image:url('{{ asset('images/scene-home.svg') }}'); animation-delay:0s; }
    .pending-slide.two{ background-image:url('{{ asset('images/scene-office.svg') }}'); animation-delay:6s; }
    .pending-slide.three{ background-image:url('{{ asset('images/scene-cleaning.svg') }}'); animation-delay:12s; }

    @keyframes fadeSlide{
        0%{opacity:0}
        10%{opacity:1}
        30%{opacity:1}
        40%{opacity:0}
        100%{opacity:0}
    }

    .pending-overlay{
        position:absolute;
        inset:0;
        background:
            radial-gradient(900px 420px at 20% 0%, rgba(59,130,246,.18), transparent 60%),
            radial-gradient(800px 380px at 85% 10%, rgba(34,197,94,.12), transparent 60%),
            linear-gradient(rgba(2,6,23,.55), rgba(2,6,23,.85));
        z-index:1;
        backdrop-filter: blur(1px);
    }

    /* =========================
       CARD (SMALL + CLEAN)
    ========================= */
    .pending-card{
        position:relative;
        z-index:2;

        /* THIS is what makes it “not tight” */
        width: min(620px, 100%);     /* smaller than before */
        margin: 0 auto;

        border-radius: var(--ct-radius);
        background: linear-gradient(180deg, rgba(15,23,42,.90), rgba(15,23,42,.74));
        border: 1px solid rgba(255,255,255,.10);
        box-shadow: var(--ct-shadow);
        overflow:hidden;

        animation: cardEnter .55s ease-out;
    }

    @keyframes cardEnter{
        from{ opacity:0; transform: translateY(14px) scale(.99); }
        to{ opacity:1; transform: translateY(0) scale(1); }
    }

    .pending-card::before{
        content:'';
        position:absolute;
        inset:-60px -60px auto -60px;
        height:150px;
        background: radial-gradient(closest-side, rgba(255,255,255,.10), transparent 70%);
        pointer-events:none;
        opacity:.75;
    }

    .pending-inner{
        padding: clamp(16px, 2.2vw, 26px);
    }

    .pending-header{
        text-align:center;
        padding: 6px 6px 12px;
    }

    .pending-badge{
        display:inline-flex;
        align-items:center;
        gap:.55rem;
        padding:.48rem 1rem;
        border-radius:999px;
        background: rgba(59,130,246,.12);
        border:1px solid rgba(59,130,246,.25);
        color: #bfdbfe;
        font-weight:800;
        font-size: .82rem;
        letter-spacing:.2px;
        margin-bottom: 10px;
    }

    .pending-icon{
        width: 64px;
        height: 64px;
        margin: 0 auto 10px;
        border-radius:50%;
        background: linear-gradient(135deg, var(--ct-primary), var(--ct-primary2));
        display:flex;
        align-items:center;
        justify-content:center;
        color:#fff;
        font-size: 1.55rem;
        position:relative;
        box-shadow: 0 12px 30px rgba(37,99,235,.35);
    }

    .pending-header h3{
        font-weight:900;
        margin:0 0 6px;
        color: var(--ct-text);
        letter-spacing:.2px;
        font-size: clamp(1.1rem, 2.4vw, 1.45rem);
    }

    .pending-header p{
        color: var(--ct-muted);
        font-size: clamp(.86rem, 2.1vw, .92rem);
        margin:0;
        max-width: 46ch;
        margin-inline:auto;
        line-height:1.55;
    }

    .pending-section{
        margin-top: 12px;
        border:1px solid rgba(255,255,255,.08);
        background: rgba(2,6,23,.22);
        border-radius: 16px;
        padding: 14px;
    }

    .progress-step{
        display:flex;
        gap: 12px;
        padding: 10px 0;
        position:relative;
    }

    .progress-step:not(:last-child)::after{
        content:'';
        position:absolute;
        left: 14px;
        top: 42px;
        width: 2px;
        height: calc(100% - 42px);
        background: linear-gradient(to bottom, rgba(59,130,246,.95), rgba(148,163,184,.22));
        border-radius: 2px;
        opacity:.95;
    }

    .step-dot{
        width:28px;
        height:28px;
        border-radius:50%;
        background: rgba(148,163,184,.14);
        border:1px solid rgba(148,163,184,.25);
        display:flex;
        align-items:center;
        justify-content:center;
        font-weight:900;
        font-size:.82rem;
        color: var(--ct-muted);
        flex-shrink:0;
        margin-top: 2px;
        z-index:2;
        position:relative;
    }

    .step-dot.active{
        background: linear-gradient(135deg, var(--ct-primary), var(--ct-primary2));
        border-color: rgba(59,130,246,.40);
        color:#fff;
        box-shadow: 0 8px 22px rgba(37,99,235,.35);
    }

    .step-content h6{
        font-weight:900;
        margin: 0 0 2px;
        color: var(--ct-text);
        font-size: .98rem;
    }

    .step-content p{
        margin:0;
        color: var(--ct-muted);
        font-size: .86rem;
        line-height:1.55;
    }

    .pending-note{
        text-align:center;
        color: var(--ct-muted);
        font-size: .88rem;
        padding: 12px 8px 2px;
        line-height:1.55;
    }
    .pending-note strong{ color: var(--ct-text); }

    /* mobile: keep card visually small and centered */
    @media (max-width: 576px){
        .pending-card{ width: min(520px, 100%); border-radius: 18px; }
        .pending-section{ border-radius: 14px; padding: 12px; }
    }
</style>

<div class="pending-page">
    {{-- SLIDESHOW (UNCHANGED) --}}
    <div class="pending-slide one"></div>
    <div class="pending-slide two"></div>
    <div class="pending-slide three"></div>
    <div class="pending-overlay"></div>

    <div class="pending-card">
        <div class="pending-inner">
            <div class="pending-header">
                <div class="pending-badge">Provider Status</div>
                <div class="pending-icon">⏳</div>
                <h3>Account Under Review</h3>
                <p>Your provider account is currently being reviewed by our admin team.</p>
            </div>

            <div class="pending-section">
                <div class="progress-step">
                    <div class="step-dot active">✓</div>
                    <div class="step-content">
                        <h6>Email Verified</h6>
                        <p>Your email address has been successfully confirmed.</p>
                    </div>
                </div>

                <div class="progress-step">
                    <div class="step-dot active">✓</div>
                    <div class="step-content">
                        <h6>Documents Submitted</h6>
                        <p>Your identification documents are under validation.</p>
                    </div>
                </div>

                <div class="progress-step">
                    <div class="step-dot">3</div>
                    <div class="step-content">
                        <h6>Final Approval</h6>
                        <p>Dashboard access will unlock once approved.</p>
                    </div>
                </div>
            </div>

            <div class="pending-note">
                This process usually takes <strong>24–48 hours</strong>.
                You’ll be notified via email once approved.
            </div>
        </div>
    </div>
</div>
@endsection
    