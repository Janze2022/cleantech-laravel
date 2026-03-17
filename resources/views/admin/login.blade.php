@extends('layouts.app')

@section('title', 'Admin Login')

@section('content')

<style>
.admin-login-page{
    min-height:100vh;
    display:flex;
    align-items:center;
    justify-content:center;
    padding:1.25rem;
    background:
        radial-gradient(circle at top right, rgba(56,189,248,.16), transparent 35%),
        radial-gradient(circle at bottom left, rgba(14,165,233,.14), transparent 38%),
        linear-gradient(180deg, #08111f 0%, #020617 100%);
}

html, body{
    margin:0;
    padding:0;
    height:100%;
    background:#020617;
}

.admin-login-card{
    width:100%;
    max-width:430px;
    padding:2.4rem 2rem;
    border-radius:24px;
    background: rgba(7,18,37,.82);
    border:1px solid rgba(255,255,255,.08);
    box-shadow: 0 28px 70px rgba(0,0,0,.48);
    backdrop-filter: blur(18px);
}

.admin-brand{
    display:flex;
    align-items:center;
    gap:.95rem;
    margin-bottom:1.5rem;
}

.admin-brand-icon{
    width:64px;
    height:64px;
    border-radius:18px;
    display:flex;
    align-items:center;
    justify-content:center;
    background: linear-gradient(135deg, rgba(14,165,233,.95), rgba(37,99,235,.88));
    box-shadow: 0 18px 32px rgba(14,165,233,.22);
}

.admin-brand-icon svg{
    width:30px;
    height:30px;
    color:#fff;
}

.admin-heading{
    margin:0;
    color:#fff;
    font-size:1.3rem;
    font-weight:900;
    letter-spacing:-.02em;
}

.admin-subtext{
    margin:.35rem 0 0;
    color:#94a3b8;
    font-size:.9rem;
    line-height:1.5;
}

.form-label{
    color:rgba(255,255,255,.72);
    font-size:.76rem;
    font-weight:800;
    letter-spacing:.08em;
    text-transform:uppercase;
    margin-bottom:.4rem;
}

.form-control{
    min-height:46px;
    border-radius:14px;
    background: rgba(255,255,255,.03);
    border:1px solid rgba(255,255,255,.1);
    color:#fff;
    padding:.72rem .95rem;
}

.form-control::placeholder{
    color:rgba(255,255,255,.38);
}

.form-control:focus{
    background: rgba(255,255,255,.04);
    border-color: rgba(56,189,248,.4);
    box-shadow: 0 0 0 3px rgba(56,189,248,.1);
    color:#fff;
}

.password-wrapper{
    position:relative;
}

.password-wrapper .form-control{
    padding-right:3.2rem;
}

.password-toggle{
    position:absolute;
    top:50%;
    right:.8rem;
    transform:translateY(-50%);
    width:36px;
    height:36px;
    display:inline-flex;
    align-items:center;
    justify-content:center;
    border-radius:10px;
    border:1px solid rgba(255,255,255,.08);
    background: rgba(255,255,255,.03);
    color:#cbd5e1;
    cursor:pointer;
    transition: background .15s ease, border-color .15s ease;
}

.password-toggle:hover{
    background: rgba(56,189,248,.08);
    border-color: rgba(56,189,248,.22);
}

.password-toggle svg{
    width:18px;
    height:18px;
}

.admin-login-btn{
    width:100%;
    min-height:46px;
    border:none;
    border-radius:14px;
    background: linear-gradient(135deg, rgba(14,165,233,.96), rgba(37,99,235,.92));
    color:#fff;
    font-weight:900;
    letter-spacing:.03em;
    box-shadow: 0 14px 34px rgba(14,165,233,.22);
    transition: transform .15s ease, box-shadow .15s ease;
}

.admin-login-btn:hover{
    transform:translateY(-1px);
    box-shadow: 0 18px 40px rgba(14,165,233,.28);
}

.alert{
    border-radius:14px;
}
</style>

<div class="admin-login-page">
    <div class="admin-login-card">

        <div class="admin-brand">
            <div class="admin-brand-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none">
                    <path d="M12 3l7 3v5c0 4.5-2.8 8.2-7 10-4.2-1.8-7-5.5-7-10V6l7-3z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
                    <path d="M9.5 12.3l1.7 1.7 3.6-4.2" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </div>

            <div>
                <h1 class="admin-heading">Admin Login</h1>
                <p class="admin-subtext">Secure access for CleanTech administrators.</p>
            </div>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger py-2 mb-3">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('admin.login.submit') }}">
            @csrf

            <div class="mb-3">
                <label for="adminEmail" class="form-label">Email</label>
                <input
                    id="adminEmail"
                    type="email"
                    name="email"
                    class="form-control"
                    placeholder="Enter admin email"
                    required
                >
            </div>

            <div class="mb-4">
                <label for="adminPassword" class="form-label">Password</label>

                <div class="password-wrapper">
                    <input
                        id="adminPassword"
                        type="password"
                        name="password"
                        class="form-control"
                        placeholder="Enter password"
                        required
                    >

                    <button
                        type="button"
                        class="password-toggle"
                        id="toggleAdminPassword"
                        aria-label="Show password"
                    >
                        <svg id="eyeOpenIcon" viewBox="0 0 24 24" fill="none">
                            <path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6S2 12 2 12z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
                            <circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="1.8"/>
                        </svg>
                        <svg id="eyeClosedIcon" viewBox="0 0 24 24" fill="none" style="display:none;">
                            <path d="M3 3l18 18" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                            <path d="M10.7 5.2A10.9 10.9 0 0 1 12 5c6.5 0 10 7 10 7a17.4 17.4 0 0 1-4 4.6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M6.2 7.1C3.7 8.9 2 12 2 12s3.5 7 10 7c1.6 0 3-.3 4.3-.9" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M9.9 9.8a3 3 0 0 0 4.2 4.2" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                        </svg>
                    </button>
                </div>
            </div>

            <button type="submit" class="admin-login-btn">
                Sign In
            </button>
        </form>

    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const toggleButton = document.getElementById('toggleAdminPassword');
    const passwordField = document.getElementById('adminPassword');
    const eyeOpenIcon = document.getElementById('eyeOpenIcon');
    const eyeClosedIcon = document.getElementById('eyeClosedIcon');

    if (!toggleButton || !passwordField) {
        return;
    }

    toggleButton.addEventListener('click', function () {
        const showPassword = passwordField.type === 'password';
        passwordField.type = showPassword ? 'text' : 'password';
        toggleButton.setAttribute('aria-label', showPassword ? 'Hide password' : 'Show password');

        if (eyeOpenIcon && eyeClosedIcon) {
            eyeOpenIcon.style.display = showPassword ? 'none' : '';
            eyeClosedIcon.style.display = showPassword ? '' : 'none';
        }
    });
});
</script>

@endsection
