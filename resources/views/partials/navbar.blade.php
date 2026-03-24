{{-- FULL NAV + FIXED OFFSET --}}

<style>
:root{
    --nav-h: 64px;
}

html, body{
    margin: 0;
    padding: 0;
    min-height: 100%;
    background-color: #0b0f19;
}

.app-content{
    padding-top: var(--nav-h);
}

html{
    scroll-padding-top: calc(var(--nav-h) + 12px);
}

.navbar-cleantech{
    background: #0b0f19;
    border-bottom: 1px solid rgba(255,255,255,.08);
    padding: .45rem 0;
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    z-index: 1050;
}

.navbar-grid{
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:1rem;
    position:relative;
}

.navbar-brand{
    font-weight: 800;
    font-size: .95rem;
    letter-spacing: .4px;
    color: #fff !important;
    display: flex;
    align-items: center;
    gap: .5rem;
    margin: 0;
    padding: 0;
    flex-shrink:0;
}

.brand-icon{
    width: 30px;
    height: 30px;
    border-radius: 50%;
    border: 1px solid rgba(56,189,248,.45);
    display: grid;
    place-items: center;
    color: #38bdf8;
    box-shadow: 0 0 0 3px rgba(56,189,248,.10);
}

.brand-icon svg,
.nav-login-icon svg{
    width: 16px;
    height: 16px;
    stroke: currentColor;
}

.navbar-collapse{
    align-items:center;
    justify-content:space-between;
    gap:1rem;
    flex:1 1 auto;
}

.navbar-center{
    display: flex;
    justify-content: center;
    flex:1 1 auto;
    min-width:0;
}

.navbar-center-links{
    display: flex;
    gap: 2rem;
    flex-wrap: wrap;
    justify-content: center;
    align-items:center;
    margin: 0;
    padding: 0;
}

.navbar-center-links .nav-link{
    color: rgba(255,255,255,.75) !important;
    font-size: .9rem;
    font-weight: 600;
    padding: .25rem 0;
    transition: color .25s ease;
}

.navbar-center-links .nav-link:hover{
    color: #fff !important;
}

.nav-login{
    background: linear-gradient(135deg, #2563eb, #6366f1);
    color: #fff !important;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: .45rem;
    border-radius: 999px;
    padding: .35rem .95rem;
    font-size: .85rem;
    font-weight: 800;
    box-shadow: 0 8px 22px rgba(37,99,235,.4);
    transition: all .25s ease;
}

.nav-login:hover{
    transform: translateY(-1px);
    box-shadow: 0 12px 30px rgba(37,99,235,.6);
}

.nav-login-icon{
    display: inline-flex;
    align-items: center;
    justify-content: center;
    color: currentColor;
}

.dropdown-menu{
    background: #0f172a;
    border-radius: 14px;
    border: 1px solid rgba(255,255,255,.08);
    padding: .5rem;
    min-width: 190px;
    box-shadow: 0 25px 45px rgba(0,0,0,.5);
}

.dropdown-item{
    color: rgba(255,255,255,.85);
    font-size: .85rem;
    border-radius: 10px;
    padding: .45rem .75rem;
}

.dropdown-item:hover{
    background: rgba(255,255,255,.08);
    color: #fff;
}

.dropdown-divider{
    border-top: 1px solid rgba(255,255,255,.08);
}

.nav-user-link{
    min-height:42px;
    padding:.48rem .85rem !important;
    border-radius:999px;
    border:1px solid rgba(255,255,255,.08);
    background:rgba(255,255,255,.03);
    color:#fff !important;
    display:inline-flex;
    align-items:center;
    gap:.45rem;
    font-weight:800;
}

.nav-user-link:hover{
    background:rgba(56,189,248,.08);
    border-color:rgba(56,189,248,.22);
}

.navbar-actions{
    display:flex;
    align-items:center;
    justify-content:flex-end;
    flex-shrink:0;
}

.navbar-toggler{
    border: none;
    padding: .25rem .45rem;
    margin-left:auto;
}

.navbar-toggler:focus{
    box-shadow: none;
}

@media (min-width: 992px){
    .navbar-collapse{
        display:flex !important;
    }
}

@media (max-width: 991px){
    .navbar-grid{
        display:flex;
        align-items:center;
    }

    .navbar-collapse{
        flex:none;
        width:auto;
    }

    .navbar-collapse:not(.show){
        display:none !important;
    }

    .navbar-collapse.show{
        display:block !important;
    }

    .navbar-center{
        margin-top: 0;
        justify-content:center;
        width:100%;
    }

    .navbar-center-links{
        gap: 1rem;
        flex-direction:column;
        align-items:center;
        padding:.35rem 0 .2rem;
        width:100%;
        text-align:center;
    }

    .navbar-center-links .nav-item{
        width:100%;
    }

    .navbar-center-links .nav-link{
        display:block;
        width:100%;
        text-align:center;
    }

    .nav-login{
        width: 100%;
        text-align: center;
        margin-top: .75rem;
    }

    .navbar-actions{
        display:block;
        width:100%;
        margin-top:.35rem;
        text-align:center;
    }

    .navbar-actions .nav-item,
    .navbar-actions .dropdown{
        width:100%;
    }

    #mainNav{
        position:absolute;
        top:calc(100% + 10px);
        left:0;
        right:0;
        width:100%;
        margin-top:0;
        padding:.9rem;
        border-radius:22px;
        background:rgba(15,23,42,.96);
        border:1px solid rgba(255,255,255,.08);
        box-shadow:0 24px 50px rgba(0,0,0,.32);
        backdrop-filter:blur(14px);
    }

    .navbar-actions .dropdown-menu{
        position:static !important;
        inset:auto !important;
        transform:none !important;
        width:100%;
        min-width:0;
        margin-top:.6rem;
        border-radius:16px;
        box-shadow:none;
    }
}

@media (max-width: 576px){
    .navbar-cleantech{
        padding: .25rem 0;
    }

    .navbar-brand{
        font-size: .82rem;
        gap: .35rem;
    }

    .brand-icon{
        width: 24px;
        height: 24px;
    }

    .brand-icon svg,
    .nav-login-icon svg{
        width: 13px;
        height: 13px;
    }

    .navbar-toggler{
        padding: .15rem .35rem;
        font-size: .85rem;
    }

    .navbar-toggler-icon{
        width: 1.1em;
        height: 1.1em;
    }

    .navbar-center{
        margin-top: .4rem;
    }

    .navbar-center-links{
        gap: .9rem;
    }

    .navbar-center-links .nav-link{
        font-size: .82rem;
        padding: .15rem 0;
    }

    .nav-login{
        padding: .25rem .7rem;
        font-size: .78rem;
        margin-top: .5rem;
    }
}
</style>

<nav class="navbar navbar-expand-lg navbar-dark navbar-cleantech">
    <div class="container navbar-grid">
        <a class="navbar-brand" href="{{ route('home') }}">
            <div class="brand-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 2.75C12 2.75 6 9.28 6 13.25A6 6 0 0 0 18 13.25C18 9.28 12 2.75 12 2.75Z"></path>
                    <path d="M9.5 14.25A2.5 2.5 0 0 0 12 16.75"></path>
                </svg>
            </div>
            CleanTech
        </a>

        <button
            class="navbar-toggler"
            type="button"
            data-bs-toggle="collapse"
            data-bs-target="#mainNav"
            aria-controls="mainNav"
            aria-expanded="false"
            aria-label="Toggle navigation"
        >
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="mainNav">
            @if (!session()->has('user_id') && !session()->has('provider_id'))
                <div class="navbar-center">
                    <ul class="navbar-nav navbar-center-links">
                        <li class="nav-item"><a class="nav-link" href="{{ route('services') }}">Services</a></li>
                        <li class="nav-item"><a class="nav-link" href="{{ route('how.it.works') }}">How It Works</a></li>
                        <li class="nav-item"><a class="nav-link" href="{{ route('pricing') }}">Pricing</a></li>
                        <li class="nav-item"><a class="nav-link" href="{{ route('blog') }}">Blog</a></li>
                        <li class="nav-item"><a class="nav-link" href="{{ route('contact') }}">Contact</a></li>
                        <li class="nav-item"><a class="nav-link" href="{{ route('faq') }}">FAQ</a></li>
                        <li class="nav-item"><a class="nav-link" href="{{ route('about') }}">About</a></li>
                        <li class="nav-item"><a class="nav-link" href="{{ route('customer.register') }}">Register</a></li>
                    </ul>
                </div>
            @endif

            <ul class="navbar-nav navbar-actions">
                @if (!session()->has('user_id') && !session()->has('provider_id'))
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle nav-login" href="#" data-bs-toggle="dropdown" aria-expanded="false">
                            <span class="nav-login-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M20 21A8 8 0 0 0 4 21"></path>
                                    <circle cx="12" cy="8" r="4"></circle>
                                </svg>
                            </span>
                            <span>Login</span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="{{ route('customer.login') }}">Customer Login</a></li>
                            <li><a class="dropdown-item" href="{{ route('provider.login') }}">Provider Login</a></li>
                        </ul>
                    </li>
                @elseif (session()->has('user_id'))
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle nav-user-link" href="#" data-bs-toggle="dropdown" aria-expanded="false">
                            {{ session('name') ?? 'Customer' }}
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="{{ route('customer.dashboard') }}">Dashboard</a></li>
                            <li><a class="dropdown-item" href="{{ route('customer.profile') }}">Profile</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a
                                    class="dropdown-item text-danger"
                                    href="#"
                                    onclick="event.preventDefault();document.getElementById('customerLogout').submit();"
                                >
                                    Logout
                                </a>
                            </li>
                        </ul>
                        <form id="customerLogout" method="POST" action="{{ route('customer.logout') }}" class="d-none">@csrf</form>
                    </li>
                @elseif (session()->has('provider_id'))
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle nav-user-link" href="#" data-bs-toggle="dropdown" aria-expanded="false">
                            {{ session('name') ?? 'Provider' }}
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="{{ route('provider.dashboard') }}">Dashboard</a></li>
                            <li><a class="dropdown-item" href="{{ route('provider.profile') }}">Profile</a></li>
                            <li><a class="dropdown-item" href="{{ route('provider.pending') }}">Approval Status</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a
                                    class="dropdown-item text-danger"
                                    href="#"
                                    onclick="event.preventDefault();document.getElementById('providerLogout').submit();"
                                >
                                    Logout
                                </a>
                            </li>
                        </ul>
                        <form id="providerLogout" method="POST" action="{{ route('provider.logout') }}" class="d-none">@csrf</form>
                    </li>
                @endif
            </ul>
        </div>
    </div>
</nav>

<script>
(function () {
    const nav = document.querySelector('.navbar-cleantech');
    const mainNav = document.getElementById('mainNav');

    if (!nav) {
        return;
    }

    function setOffset() {
        document.documentElement.style.setProperty('--nav-h', nav.offsetHeight + 'px');
    }

    window.addEventListener('load', setOffset);
    window.addEventListener('resize', setOffset);
    document.addEventListener('shown.bs.collapse', setOffset);
    document.addEventListener('hidden.bs.collapse', setOffset);

    if (mainNav) {
        mainNav.querySelectorAll('.nav-link, .dropdown-item').forEach(function (link) {
            link.addEventListener('click', function () {
                if (link.classList.contains('dropdown-toggle')) {
                    return;
                }

                if (window.innerWidth > 991 || !mainNav.classList.contains('show')) {
                    return;
                }

                const collapse = bootstrap.Collapse.getOrCreateInstance(mainNav, { toggle: false });
                collapse.hide();
            });
        });
    }
})();
</script>
