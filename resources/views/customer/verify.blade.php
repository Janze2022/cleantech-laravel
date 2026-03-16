@extends('layouts.app')

@section('title', 'Verify OTP')

@section('content')

<style>
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
    background-image: url('https://wallpaperaccess.com/full/11606068.jpg');
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
        rgba(0,0,0,.55),
        rgba(0,0,0,.75)
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
    border-radius: 24px;
    border: 1px solid rgba(255,255,255,.08);
    box-shadow: 0 40px 90px rgba(0,0,0,.65);
    padding: 3rem 3rem 2.8rem;
    color: #e5e7eb;
    animation: fadeUp .6s ease;
}

@keyframes fadeUp {
    from { opacity: 0; transform: translateY(30px); }
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
}

/* =========================
   INPUTS
========================= */
.auth-card .form-control {
    height: 56px;
    border-radius: 14px;
    font-size: 1.05rem;
    background: rgba(2,6,23,.95);
    border: 1px solid rgba(255,255,255,.1);
    color: #e5e7eb;
    padding: 0 1.25rem;
}

.auth-card .form-control[readonly] {
    opacity: .85;
}

.auth-card .form-control::placeholder {
    color: #9ca3af;
}

.auth-card .form-control:focus {
    border-color: #38bdf8;
    box-shadow: none;
    background: rgba(2,6,23,1);
}

/* OTP */
.otp-input {
    text-align: center;
    letter-spacing: 10px;
    font-weight: 700;
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
    margin-top: 2rem;
    text-align: center;
}

.auth-footer button {
    background: none;
    border: none;
    color: #38bdf8;
    font-weight: 600;
    cursor: pointer;
}

/* =========================
   ALERTS
========================= */
.alert {
    border-radius: 12px;
    font-size: .9rem;
}

/* =========================
   MOBILE
========================= */
@media (max-width: 576px) {
    .auth-card {
        padding: 2.2rem 1.6rem;
        max-width: 100%;
    }

    .auth-card .form-control,
    .auth-card .btn-primary {
        height: 50px;
    }

    .otp-input {
        letter-spacing: 6px;
    }
}
</style>

<div class="auth-page">

    <div class="auth-slide slide-1"></div>
    <div class="auth-slide slide-2"></div>
    <div class="auth-slide slide-3"></div>
    <div class="auth-overlay"></div>

    <div class="auth-card">

        <h4 class="text-center">Verify Your Account</h4>
        <p class="auth-subtext text-center">
            Enter the 6-digit code sent to your email.
        </p>

        @if ($errors->any())
            <div class="alert alert-danger mb-3">
                {{ $errors->first() }}
            </div>
        @endif

        @if (request('resent'))
            <div class="alert alert-info mb-3">
                A new OTP has been sent to your email.
            </div>
        @endif

        <form method="POST" action="{{ route('customer.verify.submit') }}">
            @csrf
            <input type="hidden" name="email" value="{{ $email }}">

            <div class="mb-4">
                <input class="form-control" value="{{ $email }}" readonly>
            </div>

            <div class="mb-4">
                <input
                    name="otp"
                    class="form-control otp-input"
                    maxlength="6"
                    inputmode="numeric"
                    placeholder="• • • • • •"
                    required
                >
            </div>

            <button class="btn btn-primary w-100">
                Verify OTP
            </button>
        </form>

        <div class="auth-footer">
            <form method="POST" action="{{ route('customer.otp.resend') }}">
                @csrf
                <input type="hidden" name="email" value="{{ $email }}">
                <button type="submit">
                    Didn’t receive the code? Resend OTP
                </button>
            </form>
        </div>

    </div>
</div>

@endsection
