@extends('layouts.app')

@section('title', 'Verify OTP')

@section('content')

<style>
:root{
    --bg-page:#020617;
    --bg-card:#0b1220;
    --border-soft:rgba(255,255,255,.08);
    --text:#e5e7eb;
    --muted:rgba(203,213,245,.62);
    --accent:#38bdf8;
    --accent2:#2563eb;
    --shadow:0 40px 90px rgba(0,0,0,.68);
    --r:18px;
}

.auth-page{
    position:relative;
    min-height:calc(100vh - var(--nav-h));
    display:grid;
    place-items:center;
    padding:clamp(24px, 4vh, 56px) 16px;
    overflow:hidden;
}

.auth-slide{
    position:absolute;
    inset:0;
    background-size:cover;
    background-position:center;
    opacity:0;
    animation:authFade 18s infinite;
    z-index:0;
    filter:saturate(.9) contrast(.95) brightness(.55);
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

.auth-overlay{
    position:absolute;
    inset:0;
    background:linear-gradient(rgba(2,6,23,.68), rgba(2,6,23,.84));
    z-index:1;
}

.auth-card{
    position:relative;
    z-index:2;
    width:100%;
    max-width:460px;
    color:var(--text);
    background:linear-gradient(180deg, rgba(11,18,32,.94), rgba(2,6,23,.94));
    border:1px solid var(--border-soft);
    border-radius:var(--r);
    padding:2.5rem 2.25rem 2.1rem;
    box-shadow:var(--shadow);
    overflow:hidden;
    animation:cardEnter .55s ease-out;
}

.auth-card::before{
    content:'';
    position:absolute;
    inset:-2px -2px auto -2px;
    height:150px;
    background:radial-gradient(closest-side at 30% 30%, rgba(56,189,248,.18), transparent 70%);
    pointer-events:none;
}

.auth-card::after{
    content:'';
    position:absolute;
    top:0;
    left:0;
    right:0;
    height:2px;
    background:linear-gradient(90deg, rgba(56,189,248,0), rgba(56,189,248,.85), rgba(37,99,235,.75), rgba(56,189,248,0));
    opacity:.9;
    pointer-events:none;
}

@keyframes cardEnter{
    from{opacity:0; transform:translateY(18px) scale(.98);}
    to{opacity:1; transform:translateY(0) scale(1);}
}

.auth-card h4{
    font-weight:900;
    letter-spacing:.2px;
    margin-bottom:.35rem;
    position:relative;
    z-index:1;
}

.auth-subtext{
    font-size:.98rem;
    color:var(--muted);
    margin-bottom:1.6rem;
    position:relative;
    z-index:1;
}

.auth-card .alert{
    border-radius:14px;
    border:1px solid rgba(255,255,255,.08);
    padding:.85rem 1rem;
    font-size:.92rem;
    position:relative;
    z-index:1;
}

.auth-card .alert-danger{
    background:rgba(239,68,68,.10);
    color:rgba(255,255,255,.90);
    border-color:rgba(239,68,68,.25);
}

.auth-card .alert-success{
    background:rgba(34,197,94,.12);
    color:rgba(255,255,255,.90);
    border-color:rgba(34,197,94,.25);
}

.auth-card .form-control{
    height:54px;
    border-radius:14px;
    font-size:1.08rem;
    letter-spacing:6px;
    font-weight:800;
    text-align:center;
    background:rgba(2,6,23,.92);
    border:1px solid rgba(255,255,255,.10);
    color:var(--text);
    box-shadow:inset 0 1px 0 rgba(255,255,255,.04);
    position:relative;
    z-index:1;
}

.auth-card .form-control::placeholder{
    color:rgba(203,213,245,.42);
    letter-spacing:6px;
}

.auth-card .form-control:focus{
    outline:none !important;
    background:rgba(2,6,23,.98);
    border-color:rgba(56,189,248,.55);
    box-shadow:0 0 0 .2rem rgba(56,189,248,.10);
}

.auth-card .btn-primary{
    height:54px;
    border-radius:14px;
    font-weight:900;
    border:1px solid rgba(56,189,248,.35);
    background:linear-gradient(135deg, rgba(56,189,248,.95), rgba(37,99,235,.95));
    box-shadow:0 18px 40px rgba(0,0,0,.35);
    position:relative;
    z-index:1;
}

.auth-card .btn-primary:hover{
    filter:brightness(1.05);
}

.resend-wrapper{
    margin-top:1.2rem;
    text-align:center;
    position:relative;
    z-index:1;
}

.resend-wrapper button{
    background:none;
    border:none;
    color:rgba(56,189,248,.92);
    font-weight:900;
    cursor:pointer;
    padding:.25rem .2rem;
}

.resend-wrapper button:disabled{
    color:rgba(148,163,184,.65);
    cursor:not-allowed;
}

.resend-wrapper span{
    display:block;
    margin-top:.55rem;
    font-size:.86rem;
    color:rgba(203,213,245,.55);
}

@media (max-width:576px){
    .auth-card{
        padding:2.1rem 1.5rem 1.85rem;
        border-radius:16px;
        max-width:100%;
    }

    .auth-card .form-control,
    .auth-card .btn-primary{
        height:50px;
    }

    .auth-card .form-control{
        letter-spacing:5px;
        font-size:1.02rem;
    }
}
</style>

<div class="auth-page">
    <div class="auth-slide one"></div>
    <div class="auth-slide two"></div>
    <div class="auth-slide three"></div>
    <div class="auth-overlay"></div>

    <div class="auth-card">
        <h4 class="text-center">Verify OTP</h4>
        <p class="auth-subtext text-center">
            Enter the 6-digit code sent to your email.
        </p>

        @if ($errors->any())
            <div class="alert alert-danger text-center mb-3">
                {{ $errors->first() }}
            </div>
        @endif

        @if (session('resent'))
            <div class="alert alert-success text-center mb-3">
                A new verification code has been sent.
            </div>
        @endif

        <form method="POST" action="{{ route('customer.forgot.verify.submit') }}">
            @csrf
            <input type="hidden" name="email" value="{{ $email }}">

            <div class="mb-3">
                <input
                    type="text"
                    name="otp"
                    class="form-control"
                    placeholder="0 0 0 0 0 0"
                    maxlength="6"
                    inputmode="numeric"
                    autocomplete="one-time-code"
                    required
                >
            </div>

            <button class="btn btn-primary w-100">
                Verify Code
            </button>
        </form>

        <div class="resend-wrapper">
            <form method="POST" action="{{ route('customer.forgot.verify.resend') }}" id="resendForm">
                @csrf
                <input type="hidden" name="email" value="{{ $email }}">
                <button id="resendBtn" disabled type="submit">
                    Didn't receive the code? Resend OTP
                </button>
                <span id="countdown"></span>
            </form>
        </div>
    </div>
</div>

<script>
(() => {
    const resendForm = document.getElementById('resendForm');
    const resendBtn = document.getElementById('resendBtn');
    const countdown = document.getElementById('countdown');
    const email = @json($email);
    const initialCooldown = Number(@json((int) ($otpCooldown ?? 0)));
    const storageKey = `customerForgotOtpCooldown:${email}`;

    if (!resendForm || !resendBtn || !countdown) {
        return;
    }

    let timer = null;
    let activeUntil = Number(sessionStorage.getItem(storageKey) || 0);
    const serverUntil = initialCooldown > 0 ? Date.now() + (initialCooldown * 1000) : 0;

    if (serverUntil > activeUntil) {
        activeUntil = serverUntil;
        sessionStorage.setItem(storageKey, String(activeUntil));
    }

    const render = () => {
        const remaining = Math.max(0, Math.ceil((activeUntil - Date.now()) / 1000));

        if (remaining <= 0) {
            resendBtn.disabled = false;
            countdown.textContent = '';
            sessionStorage.removeItem(storageKey);

            if (timer) {
                clearInterval(timer);
                timer = null;
            }

            return;
        }

        resendBtn.disabled = true;
        countdown.textContent = `Resend available in ${remaining}s`;
    };

    if (activeUntil > Date.now()) {
        render();
        timer = setInterval(render, 1000);
    } else {
        resendBtn.disabled = false;
    }

    resendForm.addEventListener('submit', () => {
        activeUntil = Date.now() + 60000;
        sessionStorage.setItem(storageKey, String(activeUntil));
        render();

        if (!timer) {
            timer = setInterval(render, 1000);
        }
    });
})();
</script>

@endsection
