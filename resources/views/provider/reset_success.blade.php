@extends('layouts.app')

@section('title', 'Password Reset Successful')

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
    --r: 22px;
}


/* =========================
   SUCCESS PAGE
========================= */
.success-page{
    min-height: calc(100vh - 120px);
    display:flex;
    align-items:center;
    justify-content:center;
    padding: 2.2rem 1rem;
    background:
        radial-gradient(1100px 520px at 20% 0%, rgba(56,189,248,.12), transparent 60%),
        radial-gradient(1000px 520px at 85% 20%, rgba(37,99,235,.10), transparent 55%),
        linear-gradient(180deg, rgba(2,6,23,1), rgba(2,6,23,.96));
}

/* =========================
   CARD (DARK)
========================= */
.success-card{
    position:relative;
    width:100%;
    max-width: 460px;
    text-align:center;

    color: var(--text);
    background: linear-gradient(180deg, rgba(11,18,32,.94), rgba(2,6,23,.94));
    border: 1px solid var(--border-soft);
    border-radius: var(--r);
    padding: 2.8rem 2.3rem 2.1rem;
    box-shadow: var(--shadow);
    overflow:hidden;

    animation: popIn .65s ease-out;
}

.success-card::before{
    content:'';
    position:absolute;
    inset:-2px -2px auto -2px;
    height: 160px;
    background: radial-gradient(closest-side at 30% 30%, rgba(56,189,248,.18), transparent 70%);
    pointer-events:none;
}
.success-card::after{
    content:'';
    position:absolute;
    top:0; left:0; right:0;
    height:2px;
    background: linear-gradient(90deg, rgba(56,189,248,0), rgba(56,189,248,.85), rgba(37,99,235,.75), rgba(56,189,248,0));
    opacity:.9;
    pointer-events:none;
}

@keyframes popIn{
    0%{ opacity:0; transform: translateY(18px) scale(.98); }
    100%{ opacity:1; transform: translateY(0) scale(1); }
}

/* =========================
   CHECKMARK
========================= */
.checkmark{
    width: 92px;
    height: 92px;
    border-radius: 50%;
    margin: 0 auto 1.35rem;

    display:flex;
    align-items:center;
    justify-content:center;

    background: linear-gradient(135deg, rgba(56,189,248,.95), rgba(37,99,235,.95));
    border: 1px solid rgba(255,255,255,.12);
    box-shadow: 0 18px 45px rgba(0,0,0,.35);

    animation: bounce .75s ease-out;
    position:relative;
    z-index:1;
}

@keyframes bounce{
    0%{ transform: scale(.75); opacity:0; }
    60%{ transform: scale(1.07); opacity:1; }
    100%{ transform: scale(1); }
}

.checkmark svg{
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
.success-card h3{
    font-weight: 900;
    letter-spacing: .2px;
    margin-bottom: .5rem;
    position:relative;
    z-index:1;
}

.success-card p{
    color: var(--muted);
    margin: 0 0 1.75rem;
    line-height: 1.6;
    position:relative;
    z-index:1;
}

/* =========================
   BUTTON
========================= */
.success-card .btn-primary{
    height: 54px;
    border-radius: 14px;
    font-weight: 900;
    border: 1px solid rgba(56,189,248,.35);
    background: linear-gradient(135deg, rgba(56,189,248,.95), rgba(37,99,235,.95));
    box-shadow: 0 18px 40px rgba(0,0,0,.35);
    position:relative;
    z-index:1;
}
.success-card .btn-primary:hover{
    filter: brightness(1.05);
}

/* =========================
   MOBILE
========================= */
@media (max-width: 576px){
    .success-card{
        padding: 2.4rem 1.6rem 1.85rem;
        border-radius: 18px;
        max-width: 100%;
    }
    .success-card .btn-primary{
        height: 50px;
    }
    .checkmark{
        width: 84px;
        height: 84px;
    }
}
</style>

<div class="success-page">
    <div class="success-card">

        <div class="checkmark">
            <svg viewBox="0 0 52 52" aria-hidden="true">
                <path d="M14 27l7 7 17-17"></path>
            </svg>
        </div>

        <h3>Password Reset Successful</h3>
        <p>
            Your provider password has been updated.<br>
            Redirecting to login…
        </p>

        <a href="{{ route('provider.login') }}" class="btn btn-primary btn-lg w-100">
            Go to Login
        </a>

    </div>
</div>

<script>
setTimeout(() => {
    window.location.href = "{{ route('provider.login') }}";
}, 5000);
</script>

@endsection
