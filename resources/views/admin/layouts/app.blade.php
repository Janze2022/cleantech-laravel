<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'Admin Panel')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    {{-- Bootstrap --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    {{-- Icons --}}
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">

    {{-- Chart.js --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        body {
            background: #020617;
            color: #e5e7eb;
            overflow-x: hidden;
        }

        /* ================================
           SIDEBAR
        ================================ */
        .admin-sidebar {
            width: 260px;
            min-height: 100vh;
            background: #020617;
            position: fixed;
            top: 0;
            left: 0;
            padding: 1.5rem;
            border-right: 1px solid rgba(255,255,255,.06);
            z-index: 1050;
            transition: left .3s ease;
        }

        .admin-sidebar h5 {
            color: #38bdf8;
            margin-bottom: 2rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: .5rem;
        }

        .admin-sidebar a {
            display: block;
            padding: .75rem 1rem;
            margin-bottom: .4rem;
            border-radius: 12px;
            color: #cbd5f5;
            text-decoration: none;
            font-size: .9rem;
        }

        .admin-sidebar a:hover,
        .admin-sidebar a.active {
            background: #0f172a;
            color: #38bdf8;
        }

        /* ================================
           TOP BAR
        ================================ */
        .admin-topbar {
            height: 64px;
            background: #020617;
            border-bottom: 1px solid rgba(255,255,255,.06);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 2rem;
            position: fixed;
            left: 260px;
            right: 0;
            top: 0;
            z-index: 1200;
        }

        .brand {
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: .6rem;
        }

        .brand i {
            color: #38bdf8;
        }

        .admin-user-menu{
            position: relative;
        }

        .admin-user-trigger{
            display:inline-flex;
            align-items:center;
            gap:.5rem;
            min-height:40px;
            padding:.5rem .85rem;
            border-radius:999px;
            border:1px solid rgba(255,255,255,.08);
            background:rgba(255,255,255,.02);
            color:#e5e7eb;
            font-weight:700;
        }

        .admin-user-trigger:hover{
            background:#0f172a;
        }

        .admin-user-trigger i{
            font-size:.82rem;
            opacity:.8;
            transition:transform .2s ease;
        }

        .admin-user-menu.open .admin-user-trigger i{
            transform:rotate(180deg);
        }

        .admin-user-panel{
            position:absolute;
            top:calc(100% + 10px);
            right:0;
            min-width:220px;
            padding:.45rem;
            border-radius:16px;
            border:1px solid rgba(255,255,255,.08);
            background:#020617;
            box-shadow:0 18px 40px rgba(0,0,0,.35);
            display:none;
            z-index:1300;
        }

        .admin-user-menu.open .admin-user-panel{
            display:block;
        }

        .admin-user-item{
            width:100%;
            display:flex;
            align-items:center;
            gap:.65rem;
            padding:.75rem .85rem;
            border:none;
            border-radius:12px;
            background:transparent;
            color:#cbd5f5;
            text-decoration:none;
            font-size:.9rem;
            text-align:left;
        }

        .admin-user-item:hover{
            background:#0f172a;
            color:#38bdf8;
        }

        .admin-user-divider{
            margin:.35rem 0;
            border-top:1px solid rgba(255,255,255,.06);
        }

        .admin-user-item.logout{
            color:#ef4444;
        }

        /* ================================
           CONTENT
        ================================ */
        .admin-content {
            margin-left: 260px;
            padding: 2rem;
            padding-top: 96px;
            min-height: 100vh;
        }

        /* ================================
           DARK CARDS
        ================================ */
        .admin-card {
            background: linear-gradient(
                180deg,
                rgba(15,23,42,.95),
                rgba(2,6,23,.95)
            );
            border-radius: 16px;
            padding: 1.5rem;
            border: 1px solid rgba(255,255,255,.06);
            box-shadow:
                0 10px 30px rgba(0,0,0,.6),
                inset 0 1px 0 rgba(255,255,255,.03);
        }

        .admin-card h6 {
            font-size: .75rem;
            font-weight: 600;
            color: #94a3b8;
            letter-spacing: .05em;
            text-transform: uppercase;
            margin-bottom: .25rem;
        }

        .metric {
            font-size: 1.9rem;
            font-weight: 700;
            color: #e5e7eb;
        }

        .metric.primary {
            color: #38bdf8;
        }

        /* ================================
           METRICS GRID
        ================================ */
        .admin-metrics {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 1.25rem;
            margin-bottom: 2rem;
        }

        /* ================================
           MOBILE
        ================================ */
        @media (max-width: 991px) {

            .admin-sidebar {
                left: -260px;
            }

            .admin-sidebar.show {
                left: 0;
            }

            .admin-topbar {
                left: 0;
                padding: 0 1rem;
            }

            .admin-content {
                margin-left: 0;
                padding: 1.25rem;
                padding-top: 88px;
            }

            .brand span {
                display: none;
            }
        }
    </style>
</head>
<body>

{{-- SIDEBAR --}}
<div class="admin-sidebar">
    <h5><i class="fa-solid fa-shield-halved"></i> Admin Panel</h5>

    <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
        <i class="fa fa-chart-line me-2"></i> Dashboard
    </a>
    <a href="{{ route('admin.customers') }}" class="{{ request()->routeIs('admin.customers') ? 'active' : '' }}">
        <i class="fa fa-users me-2"></i> User Accounts
    </a>
    <a href="{{ route('admin.providers') }}" class="{{ request()->routeIs('admin.providers') ? 'active' : '' }}">
        <i class="fa fa-user-tie me-2"></i> Providers
    </a>
    <a href="{{ route('admin.bookings') }}" class="{{ request()->routeIs('admin.bookings') ? 'active' : '' }}">
        <i class="fa fa-calendar-check me-2"></i> Bookings
    </a>
    <a href="{{ route('admin.earnings') }}" class="{{ request()->routeIs('admin.earnings*') ? 'active' : '' }}">
        <i class="fa fa-wallet me-2"></i> Earnings
    </a>
    <a href="{{ route('admin.reports') }}" class="{{ request()->routeIs('admin.reports') ? 'active' : '' }}">
        <i class="fa fa-chart-pie me-2"></i> Reports
    </a>
</div>

{{-- TOP BAR --}}
<div class="admin-topbar">
    <div class="d-flex align-items-center">
        <button class="btn btn-sm btn-outline-light d-lg-none me-2" onclick="toggleAdminSidebar()">
            <i class="fa fa-bars"></i>
        </button>

        <div class="brand">
            <i class="fa-solid fa-droplet"></i>
            <span>CleanTech Admin</span>
        </div>
    </div>

    <div class="admin-user-menu" id="adminUserMenu">
        <button type="button"
                class="admin-user-trigger"
                id="adminUserTrigger"
                aria-haspopup="true"
                aria-expanded="false">
            <span>{{ session('admin_name', 'Admin') }}</span>
            <i class="fa fa-chevron-down"></i>
        </button>

        <div class="admin-user-panel" id="adminUserPanel">
            <a class="admin-user-item" href="{{ route('admin.profile') }}">
                <i class="fa fa-user"></i>
                <span>Profile</span>
            </a>

            <div class="admin-user-divider"></div>

            <form method="POST" action="{{ route('admin.logout') }}">
                @csrf
                <button type="submit" class="admin-user-item logout">
                    <i class="fa fa-right-from-bracket"></i>
                    <span>Logout</span>
                </button>
            </form>
        </div>
    </div>
</div>

{{-- CONTENT --}}
<div class="admin-content">
    @yield('content')
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
function toggleAdminSidebar() {
    document.querySelector('.admin-sidebar').classList.toggle('show');
}

(function () {
    const menu = document.getElementById('adminUserMenu');
    const trigger = document.getElementById('adminUserTrigger');

    if (!menu || !trigger) {
        return;
    }

    function closeMenu() {
        menu.classList.remove('open');
        trigger.setAttribute('aria-expanded', 'false');
    }

    function openMenu() {
        menu.classList.add('open');
        trigger.setAttribute('aria-expanded', 'true');
    }

    trigger.addEventListener('click', function (event) {
        event.preventDefault();
        event.stopPropagation();

        if (menu.classList.contains('open')) {
            closeMenu();
            return;
        }

        openMenu();
    });

    document.addEventListener('click', function (event) {
        if (!menu.contains(event.target)) {
            closeMenu();
        }
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            closeMenu();
        }
    });
})();
</script>

</body>
</html>
