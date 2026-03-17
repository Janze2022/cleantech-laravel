<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'Provider Panel')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        :root{
            --sidebar-w:260px;
            --topbar-h:68px;
            --bg:#020617;
            --panel:#091427;
            --panel-2:#0f172a;
            --text:#e5eefc;
            --muted:#9db0cb;
            --accent:#38bdf8;
            --accent-soft:rgba(56,189,248,.12);
            --border:rgba(255,255,255,.08);
            --border-soft:rgba(255,255,255,.05);
            --danger:#ef4444;
            --shadow:0 22px 60px rgba(0,0,0,.42);
        }

        html, body{
            min-height:100%;
        }

        body{
            margin:0;
            background:
                radial-gradient(circle at top left, rgba(56,189,248,.08), transparent 22%),
                linear-gradient(180deg, #020617 0%, #030916 55%, #020617 100%);
            color:var(--text);
            overflow-x:hidden;
        }

        .provider-sidebar{
            width:var(--sidebar-w);
            min-height:100vh;
            position:fixed;
            inset:0 auto 0 0;
            padding:1.35rem 1rem 1rem;
            background:rgba(2,6,23,.96);
            border-right:1px solid var(--border-soft);
            backdrop-filter:blur(18px);
            z-index:1100;
            transition:left .28s ease;
        }

        .provider-sidebar::before{
            content:"";
            position:absolute;
            top:0;
            left:0;
            right:0;
            height:2px;
            background:linear-gradient(90deg, #38bdf8, #2563eb, #22d3ee);
        }

        .panel-brand{
            display:flex;
            align-items:center;
            gap:.85rem;
            padding:.35rem .4rem 1.15rem;
            margin-bottom:.85rem;
            border-bottom:1px solid rgba(255,255,255,.06);
        }

        .panel-brand-mark{
            width:40px;
            height:40px;
            border-radius:14px;
            display:grid;
            place-items:center;
            background:linear-gradient(135deg, rgba(56,189,248,.18), rgba(37,99,235,.2));
            border:1px solid rgba(56,189,248,.22);
            color:#d8f2ff;
            box-shadow:0 12px 26px rgba(14,165,233,.16);
        }

        .panel-brand h5{
            margin:0;
            font-size:1.22rem;
            font-weight:900;
            color:#fff;
        }

        .panel-brand p{
            margin:.2rem 0 0;
            color:var(--muted);
            font-size:.82rem;
        }

        .panel-nav{
            display:grid;
            gap:.35rem;
        }

        .panel-link{
            display:flex;
            align-items:center;
            gap:.8rem;
            min-height:52px;
            padding:.7rem .82rem;
            border-radius:16px;
            color:#d9e5f8;
            text-decoration:none;
            border:1px solid transparent;
            transition:transform .18s ease, background .18s ease, border-color .18s ease, color .18s ease;
        }

        .panel-link:hover{
            transform:translateX(3px);
            background:rgba(255,255,255,.035);
            border-color:rgba(255,255,255,.07);
            color:#fff;
        }

        .panel-link.active{
            background:linear-gradient(135deg, rgba(56,189,248,.11), rgba(37,99,235,.12));
            border-color:rgba(56,189,248,.22);
            color:#fff;
            box-shadow:0 16px 30px rgba(0,0,0,.18);
        }

        .panel-link-icon{
            width:36px;
            height:36px;
            border-radius:12px;
            display:grid;
            place-items:center;
            background:rgba(255,255,255,.04);
            border:1px solid rgba(255,255,255,.06);
            color:#9fe6ff;
            flex-shrink:0;
        }

        .panel-link.active .panel-link-icon{
            background:rgba(56,189,248,.14);
            border-color:rgba(56,189,248,.24);
            color:#e0f7ff;
        }

        .panel-link-text strong{
            display:block;
            font-size:.92rem;
            font-weight:850;
            line-height:1.15;
        }

        .panel-link-text span{
            display:block;
            margin-top:.18rem;
            color:var(--muted);
            font-size:.75rem;
        }

        .provider-topbar{
            height:var(--topbar-h);
            position:fixed;
            top:0;
            left:var(--sidebar-w);
            right:0;
            display:flex;
            align-items:center;
            justify-content:space-between;
            gap:1rem;
            padding:0 1.35rem;
            background:rgba(2,6,23,.88);
            border-bottom:1px solid var(--border-soft);
            backdrop-filter:blur(18px);
            z-index:1090;
        }

        .topbar-left,
        .topbar-right{
            display:flex;
            align-items:center;
            gap:.85rem;
        }

        .topbar-toggle{
            width:40px;
            height:40px;
            border-radius:12px;
            border:1px solid var(--border);
            background:rgba(255,255,255,.03);
            color:#fff;
            display:none;
            align-items:center;
            justify-content:center;
        }

        .brand{
            display:flex;
            align-items:center;
            gap:.75rem;
            font-weight:800;
            color:#fff;
        }

        .brand-mark{
            width:34px;
            height:34px;
            border-radius:12px;
            display:grid;
            place-items:center;
            background:rgba(56,189,248,.12);
            border:1px solid rgba(56,189,248,.22);
            color:#dff7ff;
        }

        .brand-copy small{
            display:block;
            color:var(--muted);
            font-size:.72rem;
            letter-spacing:.08em;
            text-transform:uppercase;
        }

        .brand-copy strong{
            display:block;
            margin-top:.05rem;
            font-size:.98rem;
        }

        .notif-trigger{
            width:42px;
            height:42px;
            display:inline-flex;
            align-items:center;
            justify-content:center;
            border-radius:14px;
            border:1px solid var(--border);
            background:rgba(255,255,255,.03);
            color:#fff;
            text-decoration:none;
            position:relative;
            transition:transform .18s ease, border-color .18s ease, background .18s ease;
        }

        .notif-trigger:hover{
            transform:translateY(-1px);
            border-color:rgba(56,189,248,.24);
            background:rgba(56,189,248,.08);
        }

        .notif-badge{
            position:absolute;
            top:-4px;
            right:-4px;
            border-radius:999px;
            padding:.18rem .42rem;
            font-size:.72rem;
        }

        .dropdown-menu{
            background:#06111f;
            border:1px solid rgba(255,255,255,.08);
            border-radius:18px;
            box-shadow:var(--shadow);
        }

        .notif-menu{
            width:360px;
            padding:0;
            overflow:hidden;
        }

        .notif-header{
            padding:12px 14px;
            border-bottom:1px solid var(--border-soft);
            color:#fff;
            font-weight:950;
        }

        .notif-list{
            max-height:420px;
            overflow:auto;
            padding:8px;
        }

        .notif-row{
            display:block;
            text-decoration:none;
            color:inherit;
            padding:10px 12px;
            border-radius:14px;
            margin-bottom:6px;
            border:1px solid transparent;
            background:rgba(255,255,255,.02);
        }

        .notif-row:hover{
            background:rgba(255,255,255,.05);
            border-color:rgba(255,255,255,.06);
        }

        .notif-row.unread{
            background:rgba(56,189,248,.08);
            border-color:rgba(56,189,248,.14);
        }

        .notif-msg{
            color:#fff;
            font-size:.92rem;
            font-weight:760;
            line-height:1.3;
        }

        .notif-time{
            margin-top:4px;
            color:#72d7ff;
            font-size:.8rem;
            font-weight:850;
        }

        .notif-ref{
            margin-top:4px;
            color:var(--muted);
            font-size:.78rem;
            font-weight:700;
        }

        .notif-empty{
            padding:14px;
            text-align:center;
            color:var(--muted);
            font-weight:760;
        }

        .notif-footer{
            border-top:1px solid var(--border-soft);
            padding:10px 12px;
            display:flex;
            justify-content:flex-end;
            gap:8px;
            background:rgba(255,255,255,.01);
        }

        .btn-mini{
            border-radius:12px;
            padding:.48rem .75rem;
            font-size:.82rem;
            font-weight:900;
            border:1px solid rgba(255,255,255,.10);
            background:rgba(255,255,255,.03);
            color:#fff;
        }

        .btn-mini-danger{
            border-color:rgba(239,68,68,.28);
            background:rgba(239,68,68,.1);
        }

        .provider-user-menu{
            position:relative;
        }

        .provider-user-trigger{
            display:inline-flex;
            align-items:center;
            gap:.6rem;
            min-height:42px;
            padding:.5rem .85rem;
            border-radius:999px;
            border:1px solid var(--border);
            background:rgba(255,255,255,.03);
            color:#fff;
            font-weight:800;
        }

        .provider-user-trigger:hover{
            background:#0f172a;
        }

        .provider-user-trigger i{
            font-size:.8rem;
            opacity:.78;
            transition:transform .2s ease;
        }

        .provider-user-menu.open .provider-user-trigger i{
            transform:rotate(180deg);
        }

        .provider-user-panel{
            position:absolute;
            top:calc(100% + 10px);
            right:0;
            min-width:220px;
            padding:.45rem;
            border-radius:18px;
            border:1px solid rgba(255,255,255,.08);
            background:#06111f;
            box-shadow:var(--shadow);
            display:none;
            z-index:1300;
        }

        .provider-user-menu.open .provider-user-panel{
            display:block;
        }

        .provider-user-item{
            width:100%;
            display:flex;
            align-items:center;
            gap:.65rem;
            padding:.78rem .85rem;
            border:none;
            border-radius:12px;
            background:transparent;
            color:#dce6f7;
            text-decoration:none;
            font-size:.9rem;
            text-align:left;
        }

        .provider-user-item:hover{
            background:#0f172a;
            color:#38bdf8;
        }

        .provider-user-divider{
            margin:.35rem 0;
            border-top:1px solid rgba(255,255,255,.06);
        }

        .provider-user-item.logout{
            color:#ef4444;
        }

        .provider-content{
            margin-left:var(--sidebar-w);
            min-height:100vh;
            padding:calc(var(--topbar-h) + 1.15rem) 1.35rem 1.35rem;
        }

        @media (max-width: 991px){
            .provider-sidebar{
                left:calc(-1 * var(--sidebar-w));
            }

            .provider-sidebar.show{
                left:0;
            }

            .provider-topbar{
                left:0;
                padding:0 1rem;
            }

            .topbar-toggle{
                display:inline-flex;
            }

            .provider-content{
                margin-left:0;
                padding:calc(var(--topbar-h) + 1rem) 1rem 1rem;
            }

            .brand-copy small{
                display:none;
            }
        }

        @media (max-width: 576px){
            .notif-menu{
                width:min(96vw, 380px);
            }

            .provider-user-trigger{
                padding:.5rem .72rem;
            }

            .provider-user-trigger span{
                max-width:92px;
                overflow:hidden;
                text-overflow:ellipsis;
                white-space:nowrap;
            }
        }
    </style>
</head>
<body>

@php
    $providerNotifications = collect();
    $providerUnreadCount = 0;

    if (
        session()->has('provider_id') &&
        \Illuminate\Support\Facades\Schema::hasTable('provider_notifications') &&
        \Illuminate\Support\Facades\Schema::hasColumns('provider_notifications', ['provider_id', 'is_read'])
    ) {
        $providerNotifications = DB::table('provider_notifications')
            ->where('provider_id', session('provider_id'))
            ->latest('id')
            ->limit(25)
            ->get();

        $providerUnreadCount = $providerNotifications->where('is_read', 0)->count();
    }

    $providerDisplayName = session('name') ?? session('provider_name') ?? 'Provider';
@endphp

<aside class="provider-sidebar" id="providerSidebar">
    <div class="panel-brand">
        <div class="panel-brand-mark">
            <i class="bi bi-person-badge"></i>
        </div>
        <div>
            <h5>Provider Panel</h5>
            <p>Bookings, availability, and earnings</p>
        </div>
    </div>

    <nav class="panel-nav">
        <a href="{{ route('provider.dashboard') }}" class="panel-link {{ request()->routeIs('provider.dashboard') ? 'active' : '' }}">
            <span class="panel-link-icon"><i class="bi bi-grid"></i></span>
            <span class="panel-link-text">
                <strong>Dashboard</strong>
                <span>Overview and activity</span>
            </span>
        </a>

        <a href="{{ route('provider.availability') }}" class="panel-link {{ request()->routeIs('provider.availability*') ? 'active' : '' }}">
            <span class="panel-link-icon"><i class="bi bi-calendar2-week"></i></span>
            <span class="panel-link-text">
                <strong>Availability</strong>
                <span>Manage open schedules</span>
            </span>
        </a>

        <a href="{{ route('provider.bookings') }}" class="panel-link {{ request()->routeIs('provider.bookings') ? 'active' : '' }}">
            <span class="panel-link-icon"><i class="bi bi-briefcase"></i></span>
            <span class="panel-link-text">
                <strong>Bookings</strong>
                <span>Current customer jobs</span>
            </span>
        </a>

        <a href="{{ route('provider.bookings.history') }}" class="panel-link {{ request()->routeIs('provider.bookings.history') ? 'active' : '' }}">
            <span class="panel-link-icon"><i class="bi bi-clock-history"></i></span>
            <span class="panel-link-text">
                <strong>Booking History</strong>
                <span>Completed and past work</span>
            </span>
        </a>

        <a href="{{ route('provider.analytics') }}" class="panel-link {{ request()->routeIs('provider.analytics') ? 'active' : '' }}">
            <span class="panel-link-icon"><i class="bi bi-graph-up"></i></span>
            <span class="panel-link-text">
                <strong>Analytics</strong>
                <span>Performance trends</span>
            </span>
        </a>

        <a href="{{ route('provider.earnings') }}" class="panel-link {{ request()->routeIs('provider.earnings') ? 'active' : '' }}">
            <span class="panel-link-icon"><i class="bi bi-wallet2"></i></span>
            <span class="panel-link-text">
                <strong>Earnings</strong>
                <span>Income and remittance view</span>
            </span>
        </a>

        <a href="{{ route('provider.ratings') }}" class="panel-link {{ request()->routeIs('provider.ratings') ? 'active' : '' }}">
            <span class="panel-link-icon"><i class="bi bi-stars"></i></span>
            <span class="panel-link-text">
                <strong>My Ratings</strong>
                <span>Customer feedback</span>
            </span>
        </a>
    </nav>
</aside>

<header class="provider-topbar">
    <div class="topbar-left">
        <button class="topbar-toggle" type="button" onclick="toggleProviderSidebar()" aria-label="Toggle sidebar">
            <i class="bi bi-list"></i>
        </button>

        <div class="brand">
            <div class="brand-mark">
                <i class="bi bi-droplet-half"></i>
            </div>
            <div class="brand-copy">
                <small>CleanTech</small>
                <strong>Provider Workspace</strong>
            </div>
        </div>
    </div>

    <div class="topbar-right">
        <div class="dropdown">
            <a href="#" class="notif-trigger" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Notifications">
                <i class="bi bi-bell"></i>

                @if($providerUnreadCount)
                    <span class="badge bg-danger notif-badge">{{ $providerUnreadCount }}</span>
                @endif
            </a>

            <div class="dropdown-menu dropdown-menu-end notif-menu">
                <div class="notif-header">Notifications</div>

                <div class="notif-list">
                    @forelse($providerNotifications as $n)
                        @php
                            $isUnread = (int)($n->is_read ?? 0) === 0;
                            $dt = isset($n->created_at) ? \Carbon\Carbon::parse($n->created_at) : null;
                            $timeText = $dt ? $dt->diffForHumans() : '';
                        @endphp

                        <a href="{{ route('provider.notifications.open', ['id' => $n->id], false) }}" class="notif-row {{ $isUnread ? 'unread' : '' }}">
                            <div class="notif-msg">{{ $n->message }}</div>

                            @if($timeText)
                                <div class="notif-time">{{ $timeText }}</div>
                            @endif

                            @if(!empty($n->reference_code))
                                <div class="notif-ref">Ref: {{ $n->reference_code }}</div>
                            @endif
                        </a>
                    @empty
                        <div class="notif-empty">No notifications</div>
                    @endforelse
                </div>

                <div class="notif-footer">
                    <form method="POST" action="{{ route('provider.notifications.readAll', [], false) }}">
                        @csrf
                        <button type="submit" class="btn-mini" {{ $providerUnreadCount ? '' : 'disabled' }}>
                            Mark all read
                        </button>
                    </form>

                    <form method="POST" action="{{ route('provider.notifications.clear', [], false) }}" onsubmit="return confirm('Delete all notifications?');">
                        @csrf
                        <button type="submit" class="btn-mini btn-mini-danger" {{ $providerNotifications->count() ? '' : 'disabled' }}>
                            Delete all
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="provider-user-menu" id="providerUserMenu">
            <button type="button" class="provider-user-trigger" id="providerUserTrigger" aria-haspopup="true" aria-expanded="false">
                <span>{{ $providerDisplayName }}</span>
                <i class="bi bi-chevron-down"></i>
            </button>

            <div class="provider-user-panel" id="providerUserPanel">
                <a class="provider-user-item" href="{{ route('provider.profile') }}">
                    <i class="bi bi-person"></i>
                    <span>Profile</span>
                </a>

                <a class="provider-user-item" href="{{ route('provider.dashboard') }}">
                    <i class="bi bi-grid"></i>
                    <span>Dashboard</span>
                </a>

                <div class="provider-user-divider"></div>

                <form method="POST" action="{{ route('provider.logout') }}">
                    @csrf
                    <button type="submit" class="provider-user-item logout">
                        <i class="bi bi-box-arrow-right"></i>
                        <span>Logout</span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</header>

<main class="provider-content">
    @yield('content')
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function toggleProviderSidebar() {
        document.getElementById('providerSidebar')?.classList.toggle('show');
    }

    (function () {
        const menu = document.getElementById('providerUserMenu');
        const trigger = document.getElementById('providerUserTrigger');

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
