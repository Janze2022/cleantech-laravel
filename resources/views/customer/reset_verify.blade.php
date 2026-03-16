@extends('layouts.app')

@section('title', 'Verify OTP')

@section('content')

<style>
/* =========================
   THEME TOKENS (CleanTech)
========================= */
:root{
    --bg-page:#020617;
    --bg-card:#0b1220;
    --bg-card2:#0a1328;
    --border-soft:rgba(255,255,255,.08);

    --text:#e5e7eb;
    --muted:rgba(203,213,245,.62);

    --accent:#38bdf8;
    --accent2:#2563eb;

    --shadow: 0 40px 90px rgba(0,0,0,.65);
    --r: 18px;
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
   CARD
========================= */
.auth-card{
    width:100%;
    max-width: 460px;
    color: var(--text);
    background: linear-gradient(180deg, rgba(11,18,32,.92), rgba(2,6,23,.92));
    border: 1px solid var(--border-soft);
    border-radius: var(--r);
    padding: 2.4rem 2.2rem 2.1rem;
    box-shadow: var(--shadow);
    position: relative;
    overflow: hidden;
    animation: fadeUp .55s ease;
}

/* soft top glow */
.auth-card::before{
    content:'';
    position:absolute;
    inset: -2px -2px auto -2px;
    height: 140px;
    background: radial-gradient(closest-side at 30% 30%, rgba(56,189,248,.22), transparent 70%);
    pointer-events:none;
}

/* accent bar */
.auth-card::after{
    content:'';
    position:absolute;
    top:0; left:0; right:0;
    height: 2px;
    background: linear-gradient(90deg, rgba(56,189,248,.0), rgba(56,189,248,.8), rgba(37,99,235,.75), rgba(56,189,248,.0));
    opacity:.9;
}

@keyframes fadeUp{
    from{ opacity:0; transform: translateY(18px); }
    to{ opacity:1; transform: translateY(0); }
}

/* =========================
   TEXT
========================= */
.auth-card h4{
    font-weight: 900;
    letter-spacing: .2px;
    margin-bottom: .35rem;
    position: relative;
    z-index: 1;
}
.auth-subtext{
    font-size: .98rem;
    color: var(--muted);
    margin-bottom: 1.6rem;
    position: relative;
    z-index: 1;
}

/* =========================
   ALERTS (dark)
========================= */
.auth-card .alert{
    border-radius: 14px;
    border: 1px solid rgba(255,255,255,.08);
    padding: .85rem 1rem;
    font-size: .92rem;
    margin-bottom: 1rem;
    position: relative;
    z-index: 1;
}
.auth-card .alert-danger{
    background: rgba(239,68,68,.10);
    color: rgba(255,255,255,.90);
    border-color: rgba(239,68,68,.25);
}
.auth-card .alert-success{
    background: rgba(34,197,94,.12);
    color: rgba(255,255,255,.90);
    border-color: rgba(34,197,94,.25);
}

/* =========================
   INPUT
========================= */
.auth-card .form-control{
    height: 54px;
    border-radius: 14px;
    font-size: 1.08rem;
    letter-spacing: 6px;
    font-weight: 800;
    text-align: center;

    background: rgba(2,6,23,.92);
    color: var(--text);
    border: 1px solid rgba(255,255,255,.10);
    box-shadow: inset 0 1px 0 rgba(255,255,255,.04);
    position: relative;
    z-index: 1;
}
.auth-card .form-control::placeholder{
    color: rgba(203,213,245,.42);
    letter-spacing: 6px;
}
.auth-card .form-control:focus{
    outline: none !important;
    border-color: rgba(56,189,248,.55);
    box-shadow: 0 0 0 .2rem rgba(56,189,248,.10);
    background: rgba(2,6,23,.98);
}

/* =========================
   BUTTONS
========================= */
.auth-card .btn-primary{
    height: 54px;
    border-radius: 14px;
    font-weight: 900;
    letter-spacing: .2px;
    border: 1px solid rgba(56,189,248,.35);
    background: linear-gradient(135deg, rgba(56,189,248,.95), rgba(37,99,235,.95));
    box-shadow: 0 18px 40px rgba(0,0,0,.35);
    position: relative;
    z-index: 1;
}
.auth-card .btn-primary:hover{
    filter: brightness(1.05);
}

/* =========================
   RESEND
========================= */
.resend-wrapper{
    margin-top: 1.15rem;
    text-align: center;
    position: relative;
    z-index: 1;
}
.resend-wrapper button{
    background: none;
    border: none;
    color: rgba(56,189,248,.92);
    font-size: .92rem;
    font-weight: 800;
    cursor: pointer;
    padding: .35rem .25rem;
}
.resend-wrapper button:hover{
    text-decoration: underline;
}

/* =========================
   MOBILE
========================= */
@media (max-width: 576px){
    .auth-card{
        padding: 2.1rem 1.5rem 1.8rem;
        border-radius: 16px;
        max-width: 100%;
    }
    .auth-card .form-control,
    .auth-card .btn-primary{
        height: 50px;
    }
    .auth-card .form-control{
        letter-spacing: 5px;
        font-size: 1.02rem;
    }
}
</style>

<div class="auth-page">
    <div class="auth-card">

        <h4 class="text-center">Verify OTP</h4>
        <p class="auth-subtext text-center">
            Enter the 6-digit verification code sent to your email.
        </p>

        @if ($errors->any())
            <div class="alert alert-danger text-center">
                {{ $errors->first() }}
            </div>
        @endif

        @if (session('resent'))
            <div class="alert alert-success text-center">
                A new verification code has been sent.
            </div>
        @endif

        <!-- VERIFY FORM -->
        <form method="POST" action="{{ route('customer.forgot.verify.submit') }}">
            @csrf

            <input type="hidden" name="email" value="{{ $email }}">

            <div class="mb-3">
                <input
                    type="text"
                    name="otp"
                    class="form-control"
                    placeholder="• • • • • •"
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

        <!-- RESEND FORM -->
        <div class="resend-wrapper">
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
