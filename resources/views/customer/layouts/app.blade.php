<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'Customer Panel')</title>
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
            --panel-3:#12203b;
            --text:#e5eefc;
            --muted:#9db0cb;
            --accent:#38bdf8;
            --accent-soft:rgba(56,189,248,.12);
            --danger:#ef4444;
            --border:rgba(255,255,255,.08);
            --border-soft:rgba(255,255,255,.05);
            --shadow:0 22px 60px rgba(0,0,0,.42);
        }

        html, body{
            min-height:100%;
        }

        body{
            margin:0;
            background:
                radial-gradient(circle at top left, rgba(56,189,248,.09), transparent 22%),
                linear-gradient(180deg, #020617 0%, #030916 55%, #020617 100%);
            color:var(--text);
            overflow-x:hidden;
        }

        .customer-sidebar{
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

        .customer-sidebar::before{
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

        .customer-topbar{
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
            box-shadow:0 10px 22px rgba(0,0,0,.35);
        }

        .dropdown-menu{
            background:#06111f;
            border:1px solid rgba(255,255,255,.08);
            border-radius:18px;
            box-shadow:var(--shadow);
        }

        .notif-menu{
            width:380px;
            max-height:560px;
            padding:0;
            overflow:hidden;
        }

        .notif-header{
            padding:14px 14px 10px;
            border-bottom:1px solid var(--border-soft);
            background:rgba(255,255,255,.01);
        }

        .notif-header-top{
            display:flex;
            align-items:center;
            justify-content:space-between;
            gap:10px;
        }

        .notif-title{
            margin:0;
            font-size:1.28rem;
            font-weight:950;
            color:#fff;
        }

        .dots-btn{
            width:34px;
            height:34px;
            border-radius:999px;
            border:1px solid var(--border);
            background:rgba(255,255,255,.03);
            color:#fff;
            display:inline-flex;
            align-items:center;
            justify-content:center;
        }

        .notif-tabs{
            margin-top:10px;
            display:flex;
            gap:8px;
        }

        .tab-pill{
            padding:7px 12px;
            border-radius:999px;
            border:1px solid var(--border);
            background:rgba(255,255,255,.03);
            color:#dce6f7;
            font-weight:900;
            font-size:.88rem;
            cursor:pointer;
            user-select:none;
        }

        .tab-pill.active{
            border-color:rgba(56,189,248,.28);
            background:rgba(56,189,248,.10);
            color:#fff;
        }

        .notif-subhead{
            padding:10px 14px 0;
            color:#fff;
            font-weight:900;
        }

        .notif-list{
            padding:8px;
            max-height:430px;
            overflow:auto;
        }

        .notif-row{
            display:flex;
            align-items:flex-start;
            gap:12px;
            padding:10px;
            border-radius:16px;
            text-decoration:none;
            color:inherit;
        }

        .notif-row:hover{
            background:rgba(255,255,255,.04);
        }

        .notif-avatar{
            width:48px;
            height:48px;
            border-radius:14px;
            background:rgba(255,255,255,.05);
            border:1px solid rgba(255,255,255,.08);
            display:flex;
            align-items:center;
            justify-content:center;
            color:#fff;
            font-weight:950;
            flex:0 0 48px;
        }

        .notif-text{
            flex:1;
            min-width:0;
        }

        .notif-msg{
            color:#f5f9ff;
            font-size:.93rem;
            font-weight:780;
            line-height:1.28;
            word-break:break-word;
        }

        .notif-time{
            margin-top:4px;
            color:#72d7ff;
            font-size:.82rem;
            font-weight:850;
        }

        .notif-ref{
            margin-top:4px;
            color:var(--muted);
            font-size:.8rem;
            font-weight:700;
        }

        .notif-right{
            width:20px;
            padding-top:8px;
            display:flex;
            justify-content:center;
        }

        .unread-dot{
            width:9px;
            height:9px;
            border-radius:999px;
            background:#38bdf8;
            box-shadow:0 0 0 3px rgba(56,189,248,.14);
        }

        .notif-footer{
            display:flex;
            gap:8px;
            flex-wrap:wrap;
            justify-content:flex-end;
            padding:10px 14px;
            border-top:1px solid var(--border-soft);
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

        .btn-mini-accent{
            border-color:rgba(56,189,248,.28);
            background:rgba(56,189,248,.1);
        }

        .btn-mini-danger{
            border-color:rgba(239,68,68,.28);
            background:rgba(239,68,68,.1);
        }

        .notif-empty{
            margin:10px 6px;
            padding:14px;
            border-radius:16px;
            border:1px dashed rgba(255,255,255,.14);
            color:var(--muted);
            text-align:center;
            font-weight:760;
        }

        .customer-user-menu{
            position:relative;
        }

        .customer-user-trigger{
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

        .customer-user-trigger:hover{
            background:#0f172a;
        }

        .customer-user-trigger i{
            font-size:.8rem;
            opacity:.78;
            transition:transform .2s ease;
        }

        .customer-user-menu.open .customer-user-trigger i{
            transform:rotate(180deg);
        }

        .customer-user-panel{
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

        .customer-user-menu.open .customer-user-panel{
            display:block;
        }

        .customer-user-item{
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

        .customer-user-item:hover{
            background:#0f172a;
            color:#38bdf8;
        }

        .customer-user-divider{
            margin:.35rem 0;
            border-top:1px solid rgba(255,255,255,.06);
        }

        .customer-user-item.logout{
            color:#ef4444;
        }

        .customer-content{
            margin-left:var(--sidebar-w);
            min-height:100vh;
            padding:calc(var(--topbar-h) + 1.15rem) 1.35rem 1.35rem;
        }

        @media (max-width: 991px){
            .customer-sidebar{
                left:calc(-1 * var(--sidebar-w));
            }

            .customer-sidebar.show{
                left:0;
            }

            .customer-topbar{
                left:0;
                padding:0 1rem;
            }

            .topbar-toggle{
                display:inline-flex;
            }

            .customer-content{
                margin-left:0;
                padding:calc(var(--topbar-h) + 1rem) 1rem 1rem;
            }

            .brand-copy small{
                display:none;
            }
        }

        @media (max-width: 576px){
            .notif-menu{
                width:min(96vw, 392px);
                max-height:78vh;
            }

            .notif-list{
                max-height:calc(78vh - 205px);
            }

            .customer-user-trigger{
                padding:.5rem .72rem;
            }

            .customer-user-trigger span{
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
    $currentCustomer = null;
    $notifications = collect();
    $unreadCount = 0;

    if (session()->has('user_id') && \Illuminate\Support\Facades\Schema::hasTable('customers')) {
        $currentCustomer = DB::table('customers')
            ->where('id', session('user_id'))
            ->first();
    }

    if (
        session()->has('user_id') &&
        \Illuminate\Support\Facades\Schema::hasTable('notifications') &&
        \Illuminate\Support\Facades\Schema::hasColumns('notifications', ['user_id', 'is_read'])
    ) {
        $notifications = DB::table('notifications')
            ->where('user_id', session('user_id'))
            ->latest('id')
            ->limit(30)
            ->get();

        $unreadCount = $notifications->where('is_read', 0)->count();
    }

    $customerFullName = trim(implode(' ', array_filter([
        data_get($currentCustomer, 'first_name'),
        data_get($currentCustomer, 'last_name'),
    ])));

    $customerDisplayName = data_get($currentCustomer, 'name')
        ?: ($customerFullName !== '' ? $customerFullName : (session('customer_name') ?? 'Customer'));
@endphp

<aside class="customer-sidebar" id="customerSidebar">
    <div class="panel-brand">
        <div class="panel-brand-mark">
            <i class="bi bi-house-heart"></i>
        </div>
        <div>
            <h5>Customer Panel</h5>
            <p>Bookings, services, and reviews</p>
        </div>
    </div>

    <nav class="panel-nav">
        <a href="{{ route('customer.dashboard') }}" class="panel-link {{ request()->routeIs('customer.dashboard') ? 'active' : '' }}">
            <span class="panel-link-icon"><i class="bi bi-grid"></i></span>
            <span class="panel-link-text">
                <strong>Dashboard</strong>
                <span>Overview and activity</span>
            </span>
        </a>

        <a href="{{ route('customer.services') }}" class="panel-link {{ request()->routeIs('customer.services') || request()->routeIs('customer.services.*') ? 'active' : '' }}">
            <span class="panel-link-icon"><i class="bi bi-stars"></i></span>
            <span class="panel-link-text">
                <strong>Services</strong>
                <span>Pick and compare services</span>
            </span>
        </a>

        <a href="{{ route('customer.bookings') }}" class="panel-link {{ request()->routeIs('customer.bookings') ? 'active' : '' }}">
            <span class="panel-link-icon"><i class="bi bi-calendar-check"></i></span>
            <span class="panel-link-text">
                <strong>My Bookings</strong>
                <span>Track active schedules</span>
            </span>
        </a>

        <a href="{{ route('customer.bookings.history') }}" class="panel-link {{ request()->routeIs('customer.bookings.history') ? 'active' : '' }}">
            <span class="panel-link-icon"><i class="bi bi-clock-history"></i></span>
            <span class="panel-link-text">
                <strong>Booking History</strong>
                <span>Past and cancelled jobs</span>
            </span>
        </a>

        <a href="{{ route('customer.reviews') }}" class="panel-link {{ request()->routeIs('customer.reviews') || request()->routeIs('customer.reviews*') ? 'active' : '' }}">
            <span class="panel-link-icon"><i class="bi bi-chat-square-heart"></i></span>
            <span class="panel-link-text">
                <strong>Reviews</strong>
                <span>Your ratings and feedback</span>
            </span>
        </a>
    </nav>
</aside>

<header class="customer-topbar">
    <div class="topbar-left">
        <button class="topbar-toggle" type="button" onclick="toggleCustomerSidebar()" aria-label="Toggle sidebar">
            <i class="bi bi-list"></i>
        </button>

        <div class="brand">
            <div class="brand-mark">
                <i class="bi bi-droplet-half"></i>
            </div>
            <div class="brand-copy">
                <small>CleanTech</small>
                <strong>Customer Workspace</strong>
            </div>
        </div>
    </div>

    <div class="topbar-right">
        <div class="dropdown">
            <a href="#"
               class="notif-trigger"
               data-bs-toggle="dropdown"
               aria-expanded="false"
               aria-label="Notifications">
                <i class="bi bi-bell"></i>

                @if($unreadCount)
                    <span class="badge bg-danger notif-badge">{{ $unreadCount }}</span>
                @endif
            </a>

            <div class="dropdown-menu dropdown-menu-end notif-menu" id="notifMenu">
                <div class="notif-header">
                    <div class="notif-header-top">
                        <h5 class="notif-title">Notifications</h5>
                        <button class="dots-btn" type="button" title="More">
                            <i class="bi bi-three-dots"></i>
                        </button>
                    </div>

                    <div class="notif-tabs">
                        <div class="tab-pill active" id="tabAll" data-mode="all">All</div>
                        <div class="tab-pill" id="tabUnread" data-mode="unread">Unread</div>
                    </div>
                </div>

                <div class="notif-subhead">Recent</div>

                <div class="notif-list" id="notifList">
                    @forelse($notifications as $n)
                        @php
                            $isUnread = (int)($n->is_read ?? 0) === 0;
                            $msg = (string)($n->message ?? '');
                            $ref = (string)($n->reference_code ?? '');
                            $dt = isset($n->created_at) ? \Carbon\Carbon::parse($n->created_at) : null;
                            $timeText = $dt ? $dt->diffForHumans() : '';
                            $initial = strtoupper(substr($msg ?: 'N', 0, 1));
                        @endphp

                        <a class="notif-row notif-row-item"
                           data-read="{{ $isUnread ? '0' : '1' }}"
                           href="{{ route('customer.notifications.open', ['id' => $n->id], false) }}">
                            <div class="notif-avatar">{{ $initial }}</div>

                            <div class="notif-text">
                                <div class="notif-msg">{{ $msg }}</div>
                                @if($timeText !== '')
                                    <div class="notif-time">{{ $timeText }}</div>
                                @endif
                                @if($ref !== '')
                                    <div class="notif-ref">Ref: {{ $ref }}</div>
                                @endif
                            </div>

                            <div class="notif-right">
                                @if($isUnread)
                                    <div class="unread-dot" title="Unread"></div>
                                @endif
                            </div>
                        </a>
                    @empty
                        <div class="notif-empty">No notifications</div>
                    @endforelse

                    <div class="notif-empty d-none" id="notifEmptyUnread">No unread notifications</div>
                </div>

                <div class="notif-footer">
                    <form method="POST" action="{{ route('customer.notifications.readAll', [], false) }}">
                        @csrf
                        <button type="submit" class="btn-mini btn-mini-accent" {{ $unreadCount ? '' : 'disabled' }}>
                            Mark all read
                        </button>
                    </form>

                    <form method="POST" action="{{ route('customer.notifications.clear', [], false) }}" onsubmit="return confirm('Delete all notifications?');">
                        @csrf
                        <button type="submit" class="btn-mini btn-mini-danger" {{ $notifications->count() ? '' : 'disabled' }}>
                            Delete all
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="customer-user-menu" id="customerUserMenu">
            <button type="button" class="customer-user-trigger" id="customerUserTrigger" aria-haspopup="true" aria-expanded="false">
                <span>{{ $customerDisplayName }}</span>
                <i class="bi bi-chevron-down"></i>
            </button>

            <div class="customer-user-panel" id="customerUserPanel">
                <a class="customer-user-item" href="{{ route('customer.profile') }}">
                    <i class="bi bi-person"></i>
                    <span>Profile</span>
                </a>

                <a class="customer-user-item" href="{{ route('customer.dashboard') }}">
                    <i class="bi bi-grid"></i>
                    <span>Dashboard</span>
                </a>

                <div class="customer-user-divider"></div>

                <form method="POST" action="{{ route('customer.logout') }}">
                    @csrf
                    <button type="submit" class="customer-user-item logout">
                        <i class="bi bi-box-arrow-right"></i>
                        <span>Logout</span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</header>

<main class="customer-content">
    @yield('content')
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function toggleCustomerSidebar() {
        document.getElementById('customerSidebar')?.classList.toggle('show');
    }

    (function () {
        const tabAll = document.getElementById('tabAll');
        const tabUnread = document.getElementById('tabUnread');
        const emptyUnread = document.getElementById('notifEmptyUnread');
        const rows = Array.from(document.querySelectorAll('.notif-row-item'));

        function setMode(mode) {
            tabAll?.classList.toggle('active', mode === 'all');
            tabUnread?.classList.toggle('active', mode === 'unread');

            let shown = 0;

            rows.forEach(function (row) {
                const isRead = row.getAttribute('data-read') === '1';
                const show = mode === 'all' ? true : !isRead;
                row.style.display = show ? '' : 'none';
                if (show) {
                    shown += 1;
                }
            });

            if (emptyUnread) {
                emptyUnread.classList.toggle('d-none', !(mode === 'unread' && shown === 0));
            }
        }

        tabAll?.addEventListener('click', function () { setMode('all'); });
        tabUnread?.addEventListener('click', function () { setMode('unread'); });
        setMode('all');
    })();

    (function () {
        const menu = document.getElementById('customerUserMenu');
        const trigger = document.getElementById('customerUserTrigger');

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
