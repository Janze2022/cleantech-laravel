@extends('layouts.app')

@section('title', 'Forgot Password')

@section('content')

<style>
/* =========================
   PAGE LAYOUT (SAFE)
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
.auth-slide {
    position: absolute;
    inset: 0;
    background-size: cover;
    background-position: center;
    opacity: 0;
    animation: authFade 18s infinite;
    z-index: 0;
}

.auth-slide.slide-1 {
    background-image: url('https://images.unsplash.com/photo-1581578731548-c64695cc6952');
    animation-delay: 0s;
}
.auth-slide.slide-2 {
    background-image: url('https://images.unsplash.com/photo-1600585154340-be6161a56a0c');
    animation-delay: 6s;
}
.auth-slide.slide-3 {
    background-image: url('https://images.unsplash.com/photo-1584622650111-993a426fbf0a');
    animation-delay: 12s;
}

@keyframes authFade {
    0% { opacity: 0 }
    10% { opacity: 1 }
    30% { opacity: 1 }
    40% { opacity: 0 }
    100% { opacity: 0 }
}

/* OVERLAY */
.auth-overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(
        rgba(15,23,42,.55),
        rgba(15,23,42,.75)
    );
    z-index: 1;
}

/* =========================
   CARD
========================= */
.auth-card {
    position: relative;
    z-index: 2;
    width: 100%;
    max-width: 520px;
    background: radial-gradient(circle at top, #020b1f, #020617 70%);
    border-radius: 26px;
    padding: 3.2rem 3rem;
    color: #e5e7eb;
    border: 1px solid rgba(255,255,255,.08);
    box-shadow: 0 40px 90px rgba(0,0,0,.65);
    animation: fadeUp .6s ease;
}

@keyframes fadeUp {
    from { opacity: 0; transform: translateY(28px); }
    to { opacity: 1; transform: translateY(0); }
}

/* =========================
   TEXT
========================= */
.auth-card h4 {
    font-weight: 800;
    color: #f8fafc;
    margin-bottom: .5rem;
}

.auth-subtext {
    font-size: 1rem;
    color: #94a3b8;
    margin-bottom: 2.2rem;
    line-height: 1.6;
}

/* =========================
   INPUT
========================= */
.auth-card .form-control {
    height: 56px;
    border-radius: 14px;
    font-size: 1rem;
    background: rgba(2,6,23,.95);
    border: 1px solid rgba(255,255,255,.1);
    color: #e5e7eb;
    padding: 0 1.2rem;
}

.auth-card .form-control::placeholder {
    color: #9ca3af;
}

.auth-card .form-control:focus {
    background: rgba(2,6,23,1);
    border-color: #38bdf8;
    box-shadow: none;
    color: #fff;
}

/* =========================
   BUTTON
========================= */
.auth-card .btn-primary {
    height: 56px;
    border-radius: 14px;
    font-weight: 700;
    font-size: 1rem;
    background: linear-gradient(135deg, #0ea5e9, #2563eb);
    border: none;
}

/* =========================
   FOOTER
========================= */
.auth-footer {
    margin-top: 2.4rem;
    text-align: center;
    font-size: .95rem;
    color: #94a3b8;
}

.auth-footer a {
    color: #38bdf8;
    font-weight: 600;
    text-decoration: none;
}

.auth-footer a:hover {
    text-decoration: underline;
}

/* =========================
   ALERTS
========================= */
.auth-card .alert {
    border-radius: 14px;
    font-size: .95rem;
}

/* =========================
   MOBILE
========================= */
@media (max-width: 576px) {
    .auth-page {
        padding: 5rem 1rem 2.5rem;
    }

    .auth-card {
        padding: 2.4rem 1.6rem;
        border-radius: 20px;
        max-width: 100%;
    }

    .auth-card .form-control,
    .auth-card .btn-primary {
        height: 50px;
    }

    .auth-subtext {
        font-size: .95rem;
    }
}
</style>

<div class="auth-page">

    <div class="auth-slide slide-1"></div>
    <div class="auth-slide slide-2"></div>
    <div class="auth-slide slide-3"></div>
    <div class="auth-overlay"></div>

    <div class="auth-card">

        <h4 class="text-center">Forgot Password</h4>
        <p class="auth-subtext text-center">
            Enter your email address and we’ll send you a one-time password to reset your account.
        </p>

        @if ($errors->any())
            <div class="alert alert-danger text-center mb-4">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('customer.forgot.submit') }}">
            @csrf

            <div class="mb-4">
                <input
                    type="email"
                    name="email"
                    class="form-control"
                    placeholder="Email Address"
                    required
                >
            </div>

            <button class="btn btn-primary w-100">
                Send OTP
            </button>
        </form>

        <div class="auth-footer">
            Remembered your password?
            <a href="{{ route('customer.login') }}">Back to Login</a>
        </div>

    </div>

</div>

@endsection
