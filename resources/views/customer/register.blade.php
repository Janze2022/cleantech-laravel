@extends('layouts.app')

@section('title', 'Customer Registration')

@section('content')

<style>
.auth-page {
    position: relative;
    min-height: calc(100vh - 120px);
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    padding: 4rem 1.25rem;
}

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
    background-image: url('https://isnadservice.com/wp-content/uploads/2021/08/cleaning-06.jpg');
    animation-delay: 0s;
}

.auth-slide.slide-2 {
    background-image: url('https://media1.popsugar-assets.com/files/thumbor/xDWLBsDpfWLr5GzKWqFN4O5IR34=/fit-in/1456x600/top/filters:format_auto():quality(85):upscale()/2015/03/20/830/n/1922441/9d145d2f_edit_img_front_page_image_file_15494679_1426827560.png');
    animation-delay: 6s;
}

.auth-slide.slide-3 {
    background-image: url('https://images.ctfassets.net/ckvupp6ihg42/1VtT4o49UZnzRjAgNcEuWG/ea44857bfbafc27107e402dd2a752d8f/AdobeStock_567633621-min.jpeg');
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
    background:
        radial-gradient(circle at top left, rgba(14,165,233,.18), transparent 24%),
        linear-gradient(rgba(0,0,0,.45), rgba(0,0,0,.72));
    z-index: 1;
}

.auth-card {
    position: relative;
    z-index: 3;
    width: 100%;
    max-width: 760px;
    padding: 2.35rem;
    border-radius: 24px;
    background: linear-gradient(180deg, rgba(15,23,42,.9), rgba(2,6,23,.92));
    border: 1px solid rgba(96,165,250,.2);
    backdrop-filter: blur(15px);
    box-shadow:
        0 35px 90px rgba(2,6,23,.7),
        inset 0 1px 0 rgba(255,255,255,.06);
    color: #e5e7eb;
}

.auth-shell {
    display: grid;
    grid-template-columns: minmax(220px, .85fr) minmax(0, 1.15fr);
    gap: 1.6rem;
    align-items: start;
}

.auth-aside {
    padding-right: .35rem;
}

.auth-kicker {
    display: inline-flex;
    align-items: center;
    gap: .45rem;
    padding: .42rem .72rem;
    border-radius: 999px;
    border: 1px solid rgba(56,189,248,.25);
    background: rgba(56,189,248,.1);
    color: #d8f3ff;
    font-size: .74rem;
    font-weight: 900;
    letter-spacing: .08em;
    text-transform: uppercase;
}

.auth-card h4 {
    margin: 1rem 0 .55rem;
    font-size: 2rem;
    font-weight: 850;
    letter-spacing: -.03em;
    color: #f8fafc;
}

.auth-subtext {
    margin: 0 0 1.2rem;
    color: #cbd5f5;
    font-size: .95rem;
    line-height: 1.65;
}

.auth-points {
    display: grid;
    gap: .7rem;
}

.auth-point {
    display: grid;
    grid-template-columns: auto 1fr;
    gap: .7rem;
    align-items: start;
    padding: .78rem .85rem;
    border-radius: 16px;
    border: 1px solid rgba(255,255,255,.08);
    background: rgba(255,255,255,.04);
}

.auth-point-icon {
    width: 30px;
    height: 30px;
    display: grid;
    place-items: center;
    border-radius: 10px;
    color: #dff6ff;
    background: rgba(56,189,248,.12);
    border: 1px solid rgba(56,189,248,.2);
    font-size: .9rem;
}

.auth-point strong {
    display: block;
    color: #fff;
    font-size: .92rem;
    font-weight: 800;
}

.auth-point span {
    display: block;
    margin-top: .18rem;
    color: #a9bbd6;
    font-size: .82rem;
    line-height: 1.45;
}

.auth-form-wrap {
    padding-top: .15rem;
}

.auth-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 1rem;
}

.auth-field.full {
    grid-column: 1 / -1;
}

.auth-label {
    display: block;
    margin-bottom: .45rem;
    color: #cfe1ff;
    font-size: .78rem;
    font-weight: 800;
    letter-spacing: .08em;
    text-transform: uppercase;
}

.auth-card .form-control {
    height: 48px;
    border-radius: 12px;
    background: rgba(255,255,255,.08);
    border: 1px solid rgba(148,163,184,.28);
    color: #f8fafc;
    font-size: .95rem;
    box-shadow: none;
}

.auth-card .form-control::placeholder {
    color: #c7d2fe;
}

.auth-card .form-control:focus {
    background: rgba(255,255,255,.12);
    border-color: #60a5fa;
    box-shadow: 0 0 0 .15rem rgba(96,165,250,.22);
    color: #fff;
}

.password-wrapper {
    position: relative;
}

.password-wrapper .form-control {
    padding-right: 56px;
}

.password-toggle {
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

.password-toggle svg {
    width: 18px;
    height: 18px;
    stroke: currentColor;
    fill: none;
    stroke-width: 2;
    stroke-linecap: round;
    stroke-linejoin: round;
}

.password-toggle:hover {
    color: #fff;
    background: rgba(96,165,250,.12);
    border-color: rgba(96,165,250,.35);
}

.password-toggle:focus {
    outline: none;
    color: #fff;
    background: rgba(96,165,250,.14);
    border-color: rgba(96,165,250,.45);
    box-shadow: 0 0 0 .15rem rgba(96,165,250,.18);
}

.password-toggle .icon-hide {
    display: none;
}

.password-toggle.is-visible .icon-show {
    display: none;
}

.password-toggle.is-visible .icon-hide {
    display: block;
}

.auth-card .alert {
    margin-bottom: 1rem;
    background: rgba(239,68,68,.14);
    border: 1px solid rgba(239,68,68,.28);
    border-radius: 12px;
    color: #fecaca;
    font-size: .85rem;
}

.auth-card .btn-primary {
    height: 50px;
    border-radius: 14px;
    font-weight: 800;
    background: linear-gradient(135deg, rgba(37,99,235,.88), rgba(14,165,233,.88));
    border: 1px solid rgba(255,255,255,.2);
    box-shadow: 0 10px 28px rgba(37,99,235,.28);
}

.auth-footer {
    margin-top: 1.35rem;
    text-align: center;
    color: #cbd5f5;
    font-size: .84rem;
}

.auth-footer a {
    color: #60a5fa;
    font-weight: 600;
    text-decoration: none;
}

.invalid-feedback {
    color: #fca5a5;
    font-size: .8rem;
    margin-top: .35rem;
}

@media (max-width: 767.98px) {
    .auth-page {
        padding: 3rem 1rem;
    }

    .auth-card {
        padding: 1.25rem;
    }

    .auth-shell,
    .auth-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<div class="auth-page">
    <div class="auth-slide slide-1"></div>
    <div class="auth-slide slide-2"></div>
    <div class="auth-slide slide-3"></div>
    <div class="auth-overlay"></div>

    <div class="auth-card">
        <div class="auth-shell">
            <div class="auth-aside">
                <span class="auth-kicker">Customer Account</span>
                <h4>Create your account</h4>
                <p class="auth-subtext">Sign up once and keep your booking details, schedule updates, and reviews in one place.</p>

                <div class="auth-points">
                    <div class="auth-point">
                        <div class="auth-point-icon"><i class="bi bi-calendar-check"></i></div>
                        <div>
                            <strong>Fast booking</strong>
                            <span>Choose a service and reserve an available provider with less friction.</span>
                        </div>
                    </div>

                    <div class="auth-point">
                        <div class="auth-point-icon"><i class="bi bi-shield-check"></i></div>
                        <div>
                            <strong>Trusted providers</strong>
                            <span>See approved providers and follow cleaner status updates.</span>
                        </div>
                    </div>

                    <div class="auth-point">
                        <div class="auth-point-icon"><i class="bi bi-stars"></i></div>
                        <div>
                            <strong>Simple follow-up</strong>
                            <span>Track completed bookings and leave reviews from your dashboard.</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="auth-form-wrap">
                @if ($errors->any())
                    <div class="alert">
                        {{ $errors->first() }}
                    </div>
                @endif

                <form method="POST" action="{{ route('customer.register.submit') }}" novalidate>
                    @csrf

                    <div class="auth-grid">
                        <div class="auth-field full">
                            <label class="auth-label" for="registerName">Full name</label>
                            <input id="registerName"
                                   class="form-control @error('name') is-invalid @enderror"
                                   name="name"
                                   placeholder="Juan Dela Cruz"
                                   value="{{ old('name') }}"
                                   oninput="sanitizeName(this)">
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="auth-field full">
                            <label class="auth-label" for="registerEmail">Email address</label>
                            <input id="registerEmail"
                                   type="email"
                                   class="form-control @error('email') is-invalid @enderror"
                                   name="email"
                                   placeholder="name@example.com"
                                   value="{{ old('email') }}">
                            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="auth-field full">
                            <label class="auth-label" for="registerPhone">Mobile number</label>
                            <input id="registerPhone"
                                   type="tel"
                                   class="form-control @error('phone') is-invalid @enderror"
                                   name="phone"
                                   placeholder="09XXXXXXXXX"
                                   value="{{ old('phone') }}"
                                   maxlength="11"
                                   oninput="enforcePHMobile(this)">
                            @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="auth-field">
                            <label class="auth-label" for="registerPassword">Password</label>
                            <div class="password-wrapper">
                                <input id="registerPassword"
                                       type="password"
                                       class="form-control @error('password') is-invalid @enderror"
                                       name="password"
                                       placeholder="Create password">
                                <button type="button" class="password-toggle" data-toggle-password="registerPassword"></button>
                            </div>
                            @error('password')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>

                        <div class="auth-field">
                            <label class="auth-label" for="registerPasswordConfirm">Confirm password</label>
                            <div class="password-wrapper">
                                <input id="registerPasswordConfirm"
                                       type="password"
                                       class="form-control"
                                       name="password_confirmation"
                                       placeholder="Confirm password">
                                <button type="button" class="password-toggle" data-toggle-password="registerPasswordConfirm"></button>
                            </div>
                        </div>

                        <div class="auth-field full">
                            <button class="btn btn-primary w-100">Create Customer Account</button>
                        </div>
                    </div>
                </form>

                <div class="auth-footer">
                    Already have an account?
                    <a href="{{ route('customer.login') }}">Login</a>
                </div>
            </div>
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

document.addEventListener("DOMContentLoaded", function () {
    const toggles = Array.from(document.querySelectorAll("[data-toggle-password]"));

    if (!toggles.length) {
        return;
    }

    const icons = `
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

    toggles.forEach(function (toggle) {
        const inputId = toggle.getAttribute("data-toggle-password");
        const input = inputId ? document.getElementById(inputId) : null;

        if (!input) {
            return;
        }

        toggle.innerHTML = icons;
        toggle.setAttribute("aria-label", "Show password");
        toggle.setAttribute("aria-pressed", "false");

        toggle.addEventListener("click", function () {
            const isVisible = input.type === "text";
            input.type = isVisible ? "password" : "text";
            toggle.classList.toggle("is-visible", !isVisible);
            toggle.setAttribute("aria-label", isVisible ? "Show password" : "Hide password");
            toggle.setAttribute("aria-pressed", isVisible ? "false" : "true");
        });
    });
});
</script>

@endsection
