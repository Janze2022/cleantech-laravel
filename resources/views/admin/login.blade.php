@extends('layouts.app')

@section('title', 'Admin Login')

@section('content')

<style>
.admin-login-page {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    background:
        radial-gradient(circle at top right, rgba(99,102,241,.15), transparent 40%),
        radial-gradient(circle at bottom left, rgba(37,99,235,.15), transparent 40%),
        #0b0f19;
}

html, body {
    margin: 0;
    padding: 0;
    height: 100%;
    background-color: #0b0f19;
}

.admin-login-card {
    width: 100%;
    max-width: 420px;
    padding: 3rem 2.5rem;
    border-radius: 22px;
    background: rgba(15,23,42,.75);
    backdrop-filter: blur(18px);
    box-shadow: 0 40px 90px rgba(0,0,0,.55);
    text-align: center;
}

.admin-avatar {
    width: 86px;
    height: 86px;
    margin: 0 auto 1.2rem;
    border-radius: 50%;
    background: linear-gradient(135deg, #2563eb, #6366f1);
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-size: 2.2rem;
    box-shadow: 0 0 0 6px rgba(99,102,241,.15);
}

.admin-login-card h4 {
    font-weight: 700;
    color: #fff;
    margin-bottom: .3rem;
}

.admin-subtext {
    font-size: .9rem;
    color: #94a3b8;
    margin-bottom: 2rem;
}

.admin-login-card .form-control {
    background: transparent;
    border: none;
    border-bottom: 1px solid rgba(255,255,255,.25);
    border-radius: 0;
    padding: .7rem .25rem;
    color: #fff;
    font-size: .95rem;
}

.admin-login-card .form-control::placeholder {
    color: rgba(255,255,255,.5);
}

.admin-login-card .form-control:focus {
    background: transparent;
    border-color: #6366f1;
    box-shadow: none;
}

/* PASSWORD FIELD */
.password-wrapper {
    position: relative;
}

.password-wrapper input {
    padding-right: 45px;
}

.password-toggle {
    position: absolute;
    top: 50%;
    right: 8px;
    transform: translateY(-50%);
    background: none;
    border: none;
    color: #94a3b8;
    font-size: 1.1rem;
    cursor: pointer;
    padding: 0;
    line-height: 1;
}

.password-toggle:hover {
    color: #fff;
}

.admin-login-btn {
    width: 100%;
    height: 46px;
    border: none;
    border-radius: 999px;
    background: linear-gradient(135deg, #2563eb, #6366f1);
    color: #fff;
    font-weight: 600;
    letter-spacing: .4px;
    box-shadow: 0 10px 30px rgba(37,99,235,.45);
    transition: all .25s ease;
}

.admin-login-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 16px 40px rgba(37,99,235,.6);
}
</style>

<div class="admin-login-page">
    <div class="admin-login-card">

        <div class="admin-avatar">👤</div>

        <h4>Admin Login</h4>
        <p class="admin-subtext">Secure access for administrators</p>

        @if ($errors->any())
            <div class="alert alert-danger py-2 mb-3">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('admin.login.submit') }}">
            @csrf

            <div class="mb-3 text-start">
                <input type="email"
                       name="email"
                       class="form-control"
                       placeholder="Email ID"
                       required>
            </div>

            <div class="mb-4 text-start password-wrapper">
                <input type="password"
                       name="password"
                       id="password"
                       class="form-control"
                       placeholder="Password"
                       required>

                <button type="button" class="password-toggle" id="togglePassword">
                    👁
                </button>
            </div>

            <button type="submit" class="admin-login-btn">
                LOGIN
            </button>
        </form>

    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const togglePassword = document.getElementById('togglePassword');
    const password = document.getElementById('password');

    togglePassword.addEventListener('click', function () {
        const isPassword = password.type === 'password';
        password.type = isPassword ? 'text' : 'password';
        togglePassword.textContent = isPassword ? '🙈' : '👁';
    });
});
</script>

@endsection