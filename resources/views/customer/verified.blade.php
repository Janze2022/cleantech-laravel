@extends('layouts.app')

@section('title', 'Account Verified')

@section('content')

<style>
/* =========================
   PAGE LAYOUT (SAFE)
========================= */
.success-page {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 6rem 1.5rem 3rem; /* 🔥 space for navbar + mobile */
    background: linear-gradient(135deg, #0d6efd, #00b4d8);
}

/* =========================
   CARD
========================= */
.success-card {
    width: 100%;
    max-width: 520px;
    background: radial-gradient(circle at top, #020b1f, #020617 70%);
    border-radius: 26px;
    padding: 3.2rem 3rem;
    text-align: center;
    color: #e5e7eb;
    border: 1px solid rgba(255,255,255,.08);
    box-shadow: 0 40px 90px rgba(0,0,0,.6);
    animation: popIn .7s ease-out;
}

@keyframes popIn {
    0% {
        opacity: 0;
        transform: scale(.9);
    }
    100% {
        opacity: 1;
        transform: scale(1);
    }
}

/* =========================
   CHECK ICON
========================= */
.checkmark {
    width: 96px;
    height: 96px;
    border-radius: 50%;
    background: linear-gradient(135deg, #0ea5e9, #2563eb);
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1.8rem;
    animation: bounce .8s ease-out;
}

@keyframes bounce {
    0% { transform: scale(0); }
    60% { transform: scale(1.15); }
    100% { transform: scale(1); }
}

.checkmark svg {
    width: 48px;
    height: 48px;
    stroke: #fff;
    stroke-width: 4;
    fill: none;
    stroke-linecap: round;
    stroke-linejoin: round;
}

/* =========================
   TEXT
========================= */
.success-card h3 {
    font-weight: 800;
    margin-bottom: .6rem;
    color: #f8fafc;
}

.success-card p {
    color: #94a3b8;
    margin-bottom: 2.4rem;
    font-size: 1rem;
    line-height: 1.6;
}

/* =========================
   BUTTON
========================= */
.success-card .btn-primary {
    height: 56px;
    border-radius: 14px;
    font-size: 1rem;
    font-weight: 700;
    background: linear-gradient(135deg, #0ea5e9, #2563eb);
    border: none;
}

/* =========================
   MOBILE OPTIMIZATION
========================= */
@media (max-width: 576px) {
    .success-page {
        padding: 5rem 1rem 2.5rem;
    }

    .success-card {
        padding: 2.4rem 1.6rem;
        border-radius: 20px;
    }

    .checkmark {
        width: 80px;
        height: 80px;
        margin-bottom: 1.4rem;
    }

    .success-card h3 {
        font-size: 1.3rem;
    }

    .success-card p {
        font-size: .95rem;
    }

    .success-card .btn-primary {
        height: 50px;
    }
}
</style>

<div class="success-page">
    <div class="success-card">

        <div class="checkmark">
            <svg viewBox="0 0 52 52">
                <path d="M14 27l7 7 17-17"></path>
            </svg>
        </div>

        <h3>Account Created Successfully</h3>
        <p>
            Your CleanTech account has been verified.<br>
            You may now continue.
        </p>

        <a href="{{ route('home') }}" class="btn btn-primary w-100">
            Go to Home
        </a>

    </div>
</div>

<script>
setTimeout(() => {
    window.location.href = "{{ route('home') }}";
}, 5000);
</script>

@endsection
