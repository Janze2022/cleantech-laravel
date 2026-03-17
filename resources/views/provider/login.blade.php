@extends('layouts.app')

@section('title', 'Provider Login')

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
    background-image: url('https://images.unsplash.com/photo-1600585154340-be6161a56a0c');
    animation-delay: 0s;
}
.auth-slide.slide-2 {
    background-image: url('https://images.unsplash.com/photo-1584622650111-993a426fbf0a');
    animation-delay: 6s;
}
.auth-slide.slide-3 {
    background-image: url('https://images.unsplash.com/photo-1590496793929-36417d3117fb');
    animation-delay: 12s;
}

@keyframes authFade {
    0% { opacity: 0; }
    10% { opacity: 1; }
    30% { opacity: 1; }
    40% { opacity: 0; }
    100% { opacity: 0; }
}

.auth-overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(rgba(15,23,42,.45), rgba(15,23,42,.55));
    z-index: 1;
}

/* =========================
   PASSWORD TOGGLE
========================= */
.password-wrapper{
    position: relative;
}

.password-wrapper input{
    padding-right: 56px;
}

.password-toggle{
    position: absolute;
    right: 10px;
    top: 50%;
    transform: translateY(-50%);
    width: 38px;
    height: 38px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border: 1px solid rgba(148,163,184,.28);
    background: rgba(255,255,255,.06);
    border-radius: 10px;
    cursor: pointer;
    color: #cbd5f5;
    font-size: 0;
    line-height: 0;
    transition: background .2s ease, border-color .2s ease, color .2s ease;
}

.password-toggle svg{
    width: 18px;
    height: 18px;
    stroke: currentColor;
    fill: none;
    stroke-width: 2;
    stroke-linecap: round;
    stroke-linejoin: round;
}

.password-toggle:hover{
    color: #fff;
    background: rgba(96,165,250,.12);
    border-color: rgba(96,165,250,.35);
}

.password-toggle:focus{
    outline: none;
    color: #fff;
    background: rgba(96,165,250,.14);
    border-color: rgba(96,165,250,.45);
    box-shadow: 0 0 0 .15rem rgba(96,165,250,.18);
}

.password-toggle .icon-hide{
    display: none;
}

.password-toggle.is-visible .icon-show{
    display: none;
}

.password-toggle.is-visible .icon-hide{
    display: block;
}

/* =========================
   CARD
========================= */
.auth-card {
    position: relative;
    z-index: 3;
    width: 100%;
    max-width: 420px;
    background: linear-gradient(180deg, rgba(15, 23, 42, .85), rgba(30, 41, 59, .82));
    backdrop-filter: blur(14px);
    -webkit-backdrop-filter: blur(14px);
    border-radius: 20px;
    border: 1px solid rgba(96,165,250,.25);
    box-shadow:
        0 30px 80px rgba(2,6,23,.65),
        inset 0 1px 0 rgba(255,255,255,.06);
    padding: 2.3rem 2.1rem;
}

/* =========================
   TEXT
========================= */
.auth-card h4 {
    color: #f8fafc;
    font-weight: 700;
    margin-bottom: .35rem;
}

.auth-subtext {
    color: #cbd5f5;
    font-size: .88rem;
    margin-bottom: 1.6rem;
}

/* =========================
   INPUTS
========================= */
.auth-card .form-control {
    height: 46px;
    border-radius: 10px;
    background: rgba(255,255,255,.08);
    border: 1px solid rgba(148,163,184,.35);
    color: #f8fafc;
    font-size: .9rem;
}

.auth-card .form-control::placeholder {
    color: #c7d2fe;
}

.auth-card .form-control:focus {
    background: rgba(255,255,255,.12);
    border-color: #60a5fa;
    box-shadow: 0 0 0 .15rem rgba(96,165,250,.35);
    color: #fff;
}

/* =========================
   BUTTON
========================= */
.auth-card .btn-primary {
    height: 46px;
    border-radius: 999px;
    font-size: .9rem;
    font-weight: 600;
    background: linear-gradient(135deg,#3b82f6,#0ea5e9);
    border: none;
    box-shadow:
        0 10px 30px rgba(59,130,246,.45),
        inset 0 1px 0 rgba(255,255,255,.25);
}

/* =========================
   ALERTS
========================= */
.auth-card .alert-success {
    background: rgba(34,197,94,.15);
    border: 1px solid rgba(34,197,94,.35);
    color: #bbf7d0;
}

.auth-card .alert-danger {
    background: rgba(239,68,68,.15);
    border: 1px solid rgba(239,68,68,.35);
    color: #fecaca;
}

.auth-card .alert{
    font-size:.85rem;
    border-radius:10px;
    padding:.6rem .75rem;
}

/* =========================
   FOOTER
========================= */
.auth-footer {
    margin-top: .85rem;
    text-align: center;
    font-size: .75rem;
    color: #cbd5f5;
}

.auth-footer a {
    color: #60a5fa;
    font-weight: 600;
    text-decoration: none;
}

.auth-footer a:hover {
    text-decoration: underline;
}
</style>

<div class="auth-page">

    <div class="auth-slide slide-1"></div>
    <div class="auth-slide slide-2"></div>
    <div class="auth-slide slide-3"></div>
    <div class="auth-overlay"></div>

    <div class="auth-card">

        <h4 class="text-center">Provider Login</h4>
        <p class="auth-subtext text-center">
            Manage your services and bookings.
        </p>

        @if (session('success'))
            <div class="alert alert-success text-center">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger text-center">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('provider.login.submit') }}">
            @csrf

            <div class="mb-3">
                <input
                    type="email"
                    name="email"
                    class="form-control"
                    placeholder="Email Address"
                    value="{{ old('email') }}"
                    required
                >
            </div>

            <div class="mb-4 password-wrapper">
                <input
                    type="password"
                    name="password"
                    id="password"
                    class="form-control"
                    placeholder="Password"
                    required
                >
                <button type="button" class="password-toggle" id="togglePassword"></button>
            </div>

            <button class="btn btn-primary w-100">
                Login
            </button>

        </form>

        <div class="auth-footer">

            <div class="mb-2">
            @if (Route::has('provider.forgot'))
                <a href="{{ route('provider.forgot') }}">
                    Forgot your password?
                </a>
            @endif
            </div>

            <div>
                Not yet a provider?
                <a href="{{ route('provider.pre_register.terms') }}">
                    Register here
                </a>
            </div>

        </div>

    </div>

</div>

<script>
document.addEventListener("DOMContentLoaded", function(){
    const togglePassword = document.getElementById("togglePassword");
    const passwordInput = document.getElementById("password");

    if (!togglePassword || !passwordInput) {
        return;
    }

    togglePassword.innerHTML = `
        <svg class="icon-show" viewBox="0 0 24 24" aria-hidden="true">
            <path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6S2 12 2 12Z"></path>
            <circle cx="12" cy="12" r="3"></circle>
        </svg>
        <svg class="icon-hide" viewBox="0 0 24 24" aria-hidden="true">
            <path d="M3 3l18 18"></path>
            <path d="M10.6 10.7a3 3 0 0 0 4 4"></path>
            <path d="M9.4 5.2A11.8 11.8 0 0 1 12 5c6.5 0 10 7 10 7a13.7 13.7 0 0 1-4 4.9"></path>
            <path d="M6.6 6.7C4.1 8.3 2.5 12 2.5 12a13.8 13.8 0 0 0 6 5.1"></path>
        </svg>
    `;
    togglePassword.setAttribute("aria-label", "Show password");
    togglePassword.setAttribute("aria-pressed", "false");

    togglePassword.addEventListener("click", function(){
        const isVisible = passwordInput.type === "text";
        passwordInput.type = isVisible ? "password" : "text";
        togglePassword.classList.toggle("is-visible", !isVisible);
        togglePassword.setAttribute("aria-label", isVisible ? "Show password" : "Hide password");
        togglePassword.setAttribute("aria-pressed", isVisible ? "false" : "true");
    });
});
</script>

@endsection
