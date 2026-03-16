@extends('layouts.app')

@section('title', 'Reset Password')

@section('content')

<style>
/* =========================
   THEME TOKENS (CleanTech Dark)
========================= */
:root{
    --bg-page:#020617;
    --bg-card:#0b1220;
    --bg-deep:#020b1f;
    --border-soft:rgba(255,255,255,.08);

    --text:#e5e7eb;
    --muted:rgba(203,213,245,.62);

    --accent:#38bdf8;
    --accent2:#2563eb;

    --shadow: 0 40px 90px rgba(0,0,0,.68);
    --r: 20px;
}

/* =========================
   PAGE LAYOUT
========================= */
.auth-page{
    position: relative;
    min-height: calc(100vh - var(--nav-h));
    display: grid;
    place-items: center;
    padding: clamp(24px, 4vh, 56px) 16px;
    overflow: hidden;
}

/* =========================
   BACKGROUND SLIDESHOW
========================= */
.auth-slide{
    position:absolute;
    inset:0;
    background-size:cover;
    background-position:center;
    opacity:0;
    animation: authFade 18s infinite;
    z-index:0;
    filter: saturate(.9) contrast(.95) brightness(.55);
}
.auth-slide.one{
    background-image:url('https://images.unsplash.com/photo-1581578731548-c64695cc6952');
    animation-delay:0s;
}
.auth-slide.two{
    background-image:url('https://images.unsplash.com/photo-1600585154340-be6161a56a0c');
    animation-delay:6s;
}
.auth-slide.three{
    background-image:url('https://images.unsplash.com/photo-1584622650111-993a426fbf0a');
    animation-delay:12s;
}

@keyframes authFade{
    0%{opacity:0}
    10%{opacity:1}
    30%{opacity:1}
    40%{opacity:0}
    100%{opacity:0}
}

/* Overlay (darker) */
.auth-overlay{
    position:absolute;
    inset:0;
    background: linear-gradient(rgba(2,6,23,.68), rgba(2,6,23,.84));
    z-index:1;
}

/* =========================
   CARD (DARK)
========================= */
.auth-card{
    position: relative;
    z-index: 2;
    width: 100%;
    max-width: 460px;

    color: var(--text);
    background: linear-gradient(180deg, rgba(11,18,32,.94), rgba(2,6,23,.94));
    border: 1px solid var(--border-soft);
    border-radius: var(--r);
    box-shadow: var(--shadow);
    padding: 2.5rem 2.25rem 2.1rem;
    overflow:hidden;

    animation: cardEnter .55s ease-out;
}

.auth-card::before{
    content:'';
    position:absolute;
    inset:-2px -2px auto -2px;
    height: 150px;
    background: radial-gradient(closest-side at 30% 30%, rgba(56,189,248,.18), transparent 70%);
    pointer-events:none;
}
.auth-card::after{
    content:'';
    position:absolute;
    top:0; left:0; right:0;
    height:2px;
    background: linear-gradient(90deg, rgba(56,189,248,0), rgba(56,189,248,.85), rgba(37,99,235,.75), rgba(56,189,248,0));
    opacity:.9;
    pointer-events:none;
}

@keyframes cardEnter{
    from{ opacity:0; transform: translateY(18px) scale(.98); }
    to{ opacity:1; transform: translateY(0) scale(1); }
}

/* =========================
   TEXT
========================= */
.auth-card h4{
    font-weight: 900;
    letter-spacing: .2px;
    margin-bottom: .35rem;
    position:relative;
    z-index:1;
}
.auth-subtext{
    color: var(--muted);
    font-size: .98rem;
    margin-bottom: 1.6rem;
    position:relative;
    z-index:1;
}

/* =========================
   ALERTS (dark)
========================= */
.auth-card .alert{
    border-radius: 14px;
    border: 1px solid rgba(255,255,255,.08);
    padding: .85rem 1rem;
    font-size: .92rem;
    position:relative;
    z-index:1;
}
.auth-card .alert-danger{
    background: rgba(239,68,68,.10);
    color: rgba(255,255,255,.90);
    border-color: rgba(239,68,68,.25);
}

/* =========================
   INPUTS
========================= */
.auth-card .form-control{
    height: 54px;
    border-radius: 14px;
    font-size: 1rem;
    background: rgba(2,6,23,.92);
    border: 1px solid rgba(255,255,255,.10);
    color: var(--text);
    padding: 0 1.15rem;
    box-shadow: inset 0 1px 0 rgba(255,255,255,.04);
    position:relative;
    z-index:1;
}
.auth-card .form-control::placeholder{
    color: rgba(203,213,245,.42);
}
.auth-card .form-control:focus{
    outline:none !important;
    background: rgba(2,6,23,.98);
    border-color: rgba(56,189,248,.55);
    box-shadow: 0 0 0 .2rem rgba(56,189,248,.10);
}

/* =========================
   BUTTON
========================= */
.auth-card .btn-primary{
    height: 54px;
    border-radius: 14px;
    font-weight: 900;
    border: 1px solid rgba(56,189,248,.35);
    background: linear-gradient(135deg, rgba(56,189,248,.95), rgba(37,99,235,.95));
    box-shadow: 0 18px 40px rgba(0,0,0,.35);
    position:relative;
    z-index:1;
}
.auth-card .btn-primary:hover{
    filter: brightness(1.05);
}

/* =========================
   MOBILE
========================= */
@media(max-width:576px){
    .auth-card{
        padding: 2.1rem 1.5rem 1.85rem;
        border-radius: 16px;
        max-width: 100%;
    }
    .auth-card .form-control,
    .auth-card .btn-primary{
        height: 50px;
    }
}
</style>

<div class="auth-page">

    <!-- SLIDES -->
    <div class="auth-slide one"></div>
    <div class="auth-slide two"></div>
    <div class="auth-slide three"></div>
    <div class="auth-overlay"></div>

    <!-- CARD -->
    <div class="auth-card">

        <h4 class="text-center">Create New Password</h4>
        <p class="auth-subtext text-center">
            Choose a strong password for your provider account.
        </p>

        @if ($errors->any())
            <div class="alert alert-danger text-center">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('provider.reset.password.submit') }}">
            @csrf

            <div class="mb-3">
                <input
                    type="password"
                    name="password"
                    class="form-control"
                    placeholder="New Password"
                    required
                >
            </div>

            <div class="mb-4">
                <input
                    type="password"
                    name="password_confirmation"
                    class="form-control"
                    placeholder="Confirm New Password"
                    required
                >
            </div>

            <button class="btn btn-primary w-100">
                Reset Password
            </button>
        </form>

    </div>
</div>

@endsection
