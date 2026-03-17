@extends('layouts.app')

@section('title', 'Customer Registration')

@section('content')

<style>
/* =========================
   PAGE LAYOUT
========================= */
.auth-page {
    position: relative;
    min-height: calc(100vh - 120px);
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    padding: 4rem 1.5rem;
}

/* =========================
   BACKGROUND
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
    background-image: url('{{ asset('images/scene-cleaning.svg') }}');
    animation-delay: 0s;
}
.auth-slide.slide-2 {
    background-image: url('{{ asset('images/scene-home.svg') }}');
    animation-delay: 6s;
}
.auth-slide.slide-3 {
    background-image: url('{{ asset('images/scene-office.svg') }}');
    animation-delay: 12s;
}

@keyframes authFade {
    0% { opacity: 0 }
    10% { opacity: 1 }
    30% { opacity: 1 }
    40% { opacity: 0 }
    100% { opacity: 0 }
}

.auth-overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(rgba(0,0,0,.55), rgba(0,0,0,.75));
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
    border-radius: 22px;
    border: 1px solid rgba(255,255,255,.08);
    box-shadow: 0 40px 90px rgba(0,0,0,.65);
    padding: 3rem;
    color: #e5e7eb;
}

.auth-card h4 {
    font-weight: 800;
    color: #f8fafc;
}

.auth-subtext {
    font-size: 1rem;
    color: #94a3b8;
    margin-bottom: 2.2rem;
}

/* INPUTS */
.auth-card .form-control {
    height: 52px;
    border-radius: 12px;
    font-size: 1rem;
    background: rgba(2,6,23,.95);
    border: 1px solid rgba(255,255,255,.1);
    color: #e5e7eb;
}

.auth-card .form-control::placeholder {
    color: #9ca3af;
}

.auth-card .form-control:focus {
    border-color: #38bdf8;
    box-shadow: none;
}

/* BUTTON */
.auth-card .btn-primary {
    height: 52px;
    border-radius: 12px;
    font-weight: 700;
    background: linear-gradient(135deg, #0ea5e9, #2563eb);
    border: none;
}

/* FOOTER */
.auth-footer {
    margin-top: 2rem;
    text-align: center;
    color: #94a3b8;
}

.auth-footer a {
    color: #38bdf8;
    font-weight: 600;
}

/* ERRORS */
.invalid-feedback {
    color: #fca5a5;
}
</style>

<div class="auth-page">

    <div class="auth-slide slide-1"></div>
    <div class="auth-slide slide-2"></div>
    <div class="auth-slide slide-3"></div>
    <div class="auth-overlay"></div>

    <div class="auth-card">

        <h4 class="text-center">Create Your Account</h4>
        <p class="auth-subtext text-center">
            Book trusted cleaning services in minutes.
        </p>

        <form method="POST" action="{{ route('customer.register.submit') }}" novalidate>
            @csrf

            <div class="mb-4">
                <input class="form-control @error('name') is-invalid @enderror"
                       name="name"
                       placeholder="Full Name"
                       value="{{ old('name') }}"
                       oninput="sanitizeName(this)">
                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="mb-4">
                <input type="email"
                       class="form-control @error('email') is-invalid @enderror"
                       name="email"
                       placeholder="Email Address"
                       value="{{ old('email') }}">
                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="mb-4">
                <input type="tel"
                       class="form-control @error('phone') is-invalid @enderror"
                       name="phone"
                       placeholder="Mobile Number (09XXXXXXXXX)"
                       value="{{ old('phone') }}"
                       maxlength="11"
                       oninput="enforcePHMobile(this)">
                @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="mb-4">
                <input type="password"
                       class="form-control @error('password') is-invalid @enderror"
                       name="password"
                       placeholder="Password">
                @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <!-- ✅ CONFIRM PASSWORD -->
            <div class="mb-4">
                <input type="password"
                       class="form-control"
                       name="password_confirmation"
                       placeholder="Confirm Password">
            </div>

            <button class="btn btn-primary w-100">Register</button>
        </form>

        <div class="auth-footer">
            Already have an account?
            <a href="{{ route('customer.login') }}">Login</a>
        </div>

    </div>
</div>

<script>
function enforcePHMobile(input) {
    let v = input.value.replace(/\D/g,'');
    if (v.length >= 1 && v[0] !== '0') v = '';
    if (v.length >= 2 && !v.startsWith('09')) v = '09';
    input.value = v.slice(0,11);
}
function sanitizeName(input) {
    input.value = input.value.replace(/[^A-Za-z\s'\-]/g,'');
}
</script>

@endsection
