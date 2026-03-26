@extends('layouts.app')

@section('title', 'Verify Provider Account')

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
   PAGE
========================= */
.auth-page{
    position: relative;
    min-height: calc(100vh - 64px);
    display:flex;
    align-items:center;
    justify-content:center;
    overflow:hidden;
    padding: 2.2rem 1rem;
    background:
        radial-gradient(1100px 520px at 20% 0%, rgba(56,189,248,.12), transparent 60%),
        radial-gradient(1000px 520px at 85% 20%, rgba(37,99,235,.10), transparent 55%),
        linear-gradient(180deg, rgba(2,6,23,1), rgba(2,6,23,.96));
}

/* =========================
   BACKGROUND SLIDES
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
.auth-slide.slide-1{
    background-image:url('https://wallpaperaccess.com/full/11606068.jpg');
    animation-delay:0s;
}
.auth-slide.slide-2{
    background-image:url('https://images.unsplash.com/photo-1600585154340-be6161a56a0c');
    animation-delay:6s;
}
.auth-slide.slide-3{
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

/* Dark overlay */
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
    padding: 2.6rem 2.35rem 2.15rem;
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
.auth-card .text-muted{
    color: var(--muted) !important;
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
.auth-card .alert-info{
    background: rgba(56,189,248,.10);
    color: rgba(255,255,255,.90);
    border-color: rgba(56,189,248,.22);
}

/* =========================
   INPUT
========================= */
.auth-card .form-control{
    height: 54px;
    border-radius: 14px;
    text-align:center;
    letter-spacing: 6px;
    font-weight: 800;

    background: rgba(2,6,23,.92);
    border: 1px solid rgba(255,255,255,.10);
    color: var(--text);
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
   FOOTER / RESEND
========================= */
.auth-footer{
    margin-top: 1.2rem;
    text-align:center;
    position:relative;
    z-index:1;
}
.auth-footer button{
    background:none;
    border:none;
    color: rgba(56,189,248,.92);
    font-weight: 900;
    cursor:pointer;
    padding: .25rem .15rem;
}
.auth-footer button[disabled]{
    opacity: .55;
    cursor:not-allowed;
}

/* MOBILE */
@media (max-width:576px){
    .auth-card{
        padding: 2.2rem 1.6rem 1.9rem;
        border-radius: 16px;
        max-width: 100%;
    }
    .auth-card .form-control,
    .auth-card .btn-primary{
        height: 50px;
    }
    .auth-card .form-control{
        letter-spacing: 5px;
    }
}
</style>

<div class="auth-page">

    <div class="auth-slide slide-1"></div>
    <div class="auth-slide slide-2"></div>
    <div class="auth-slide slide-3"></div>
    <div class="auth-overlay"></div>

    <div class="auth-card">

        <h4 class="text-center">Verify Provider Account</h4>
        <p class="text-center text-muted mb-4">
            Enter the 6-digit verification code sent to your email.
        </p>

        @if ($errors->any())
            <div class="alert alert-danger text-center mb-3">
                {{ $errors->first() }}
            </div>
        @endif

        @if (request('resent'))
            <div class="alert alert-info text-center mb-3">
                A new OTP has been sent to your email.
            </div>
        @endif

        <form method="POST" action="{{ route('provider.verify.submit') }}">
            @csrf
            <input type="hidden" name="email" value="{{ $email }}">

            <div class="mb-3">
                <input
                    name="otp"
                    class="form-control"
                    maxlength="6"
                    inputmode="numeric"
                    autocomplete="one-time-code"
                    placeholder="0 0 0 0 0 0"
                    required
                    autofocus
                >
            </div>

            <button class="btn btn-primary w-100">
                Verify Provider Account
            </button>
        </form>

        <div class="auth-footer">
            <form method="POST" action="{{ route('provider.otp.resend') }}" id="resendForm">
                @csrf
                <input type="hidden" name="email" value="{{ $email }}">
                <button type="submit" id="resendBtn">
                    Didn't receive the code? Resend OTP
                </button>
                <div id="cooldownText" class="text-muted mt-1" style="font-size:.85rem;"></div>
            </form>
        </div>

    </div>
</div>

<script>
(() => {
    const resendForm = document.getElementById('resendForm');
    const resendBtn = document.getElementById('resendBtn');
    const cooldownText = document.getElementById('cooldownText');
    const email = @json($email);
    const initialCooldown = Number(@json((int) ($otpCooldown ?? 0)));
    const storageKey = `providerOtpCooldown:${email}`;

    if (!resendForm || !resendBtn || !cooldownText) {
        return;
    }

    let countdownTimer = null;
    let activeUntil = Number(sessionStorage.getItem(storageKey) || 0);
    const serverUntil = initialCooldown > 0 ? Date.now() + (initialCooldown * 1000) : 0;

    if (serverUntil > activeUntil) {
        activeUntil = serverUntil;
        sessionStorage.setItem(storageKey, String(activeUntil));
    }

    const formatCooldown = (seconds) => {
        if (seconds < 60) {
            return `${seconds}s`;
        }

        const minutes = Math.floor(seconds / 60);
        const remaining = seconds % 60;

        if (remaining === 0) {
            return `${minutes}m`;
        }

        return `${minutes}m ${remaining}s`;
    };

    const renderCountdown = () => {
        const remaining = Math.max(0, Math.ceil((activeUntil - Date.now()) / 1000));

        if (remaining <= 0) {
            resendBtn.disabled = false;
            cooldownText.textContent = '';
            sessionStorage.removeItem(storageKey);

            if (countdownTimer) {
                clearInterval(countdownTimer);
                countdownTimer = null;
            }

            return;
        }

        resendBtn.disabled = true;
        cooldownText.textContent = `Resend available in ${formatCooldown(remaining)}`;
    };

    if (activeUntil > Date.now()) {
        renderCountdown();
        countdownTimer = window.setInterval(renderCountdown, 1000);
    }

    resendForm.addEventListener('submit', () => {
        const nextUntil = Date.now() + (60 * 1000);

        sessionStorage.setItem(storageKey, String(nextUntil));
    });
})();
</script>

@endsection
