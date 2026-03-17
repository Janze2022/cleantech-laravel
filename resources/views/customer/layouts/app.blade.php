<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'Customer Panel')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    {{-- Bootstrap --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    {{-- Icons --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        body { background:#020617; color:#e5e7eb; overflow-x:hidden; }

        :root{
            --bg:#020617;
            --panel:#0b1220;
            --panel-2:#0f172a;
            --border-soft:rgba(255,255,255,.06);
            --border-soft-2:rgba(255,255,255,.10);
            --text:#e5e7eb;
            --text-muted:rgba(229,231,235,.65);
            --accent:#38bdf8;
            --danger:#ef4444;
            --success:#22c55e;
            --shadow: 0 24px 70px rgba(0,0,0,.60);
        }

        /* ================================
           SIDEBAR
        ================================ */
        .customer-sidebar{
            width:260px;
            min-height:100vh;
            background:var(--bg);
            position:fixed; top:0; left:0;
            padding:1.5rem;
            border-right:1px solid var(--border-soft);
            z-index:1050;
            transition:left .3s ease;
        }
        .customer-sidebar h5{
            color:var(--accent);
            margin-bottom:2rem;
            font-weight:600;
            display:flex;
            align-items:center;
            gap:.6rem;
        }
        .customer-sidebar a{
            display:block;
            padding:.75rem 1rem;
            margin-bottom:.4rem;
            border-radius:12px;
            color:#cbd5f5;
            text-decoration:none;
            font-size:.9rem;
        }
        .customer-sidebar a:hover,
        .customer-sidebar a.active{
            background:var(--panel-2);
            color:var(--accent);
        }

        /* ================================
           TOP BAR
        ================================ */
        .customer-topbar{
            height:64px;
            background:var(--bg);
            border-bottom:1px solid var(--border-soft);
            display:flex;
            align-items:center;
            justify-content:space-between;
            padding:0 2rem;
            position:fixed;
            left:260px; right:0; top:0;
            z-index:100;
        }
        .topbar-left,.topbar-right{ display:flex; align-items:center; gap:1rem; }
        .brand{ font-weight:600; display:flex; align-items:center; gap:.6rem; }
        .brand i{ color:var(--accent); }

        /* Dropdown dark (default) */
        .dropdown-menu{
            background: rgba(2,6,23,.98);
            border:1px solid var(--border-soft-2);
            border-radius:16px;
            overflow:hidden;
            box-shadow: var(--shadow);
        }
        .dropdown-item{
            color:#cbd5f5;
            font-size:.9rem;
            white-space:normal;
        }
        .dropdown-item:hover{ background:var(--panel-2); color:var(--accent); }
        .dropdown-divider{ border-color:var(--border-soft); }
        .logout{ color:var(--danger); }

        /* ================================
           CONTENT
        ================================ */
        .customer-content{
            margin-left:260px;
            padding:2rem;
            padding-top:96px;
            min-height:100vh;
        }

        /* ================================
           MOBILE (Sidebar + Content)
        ================================ */
        @media (max-width: 991px){
            .customer-sidebar{ left:-260px; }
            .customer-sidebar.show{ left:0; }
            .customer-topbar{ left:0; padding:0 1rem; }
            .customer-content{ margin-left:0; padding:1.25rem; padding-top:88px; }
            .brand span{ display:none; }
        }

        /* ================================
           FB-LIKE NOTIFICATIONS DROPDOWN
        ================================ */
        .notif-trigger{
            width:40px; height:40px;
            display:inline-flex;
            align-items:center; justify-content:center;
            border-radius:999px;
            border:1px solid var(--border-soft-2);
            background: rgba(255,255,255,.03);
            color: rgba(255,255,255,.92);
            text-decoration:none;
        }
        .notif-trigger:hover{
            border-color: rgba(56,189,248,.25);
            background: rgba(255,255,255,.04);
        }

        .notif-badge{
            position:absolute;
            top:-4px;
            right:-4px;
            border-radius:999px;
            padding:.18rem .45rem;
            font-size:.72rem;
            box-shadow: 0 10px 24px rgba(0,0,0,.35);
        }

        .notif-menu{
            width: 380px;
            max-height: 560px;
            padding: 0;
        }

        .notif-header{
            padding: 14px 14px 10px 14px;
            border-bottom: 1px solid var(--border-soft);
            background: rgba(255,255,255,.01);
        }
        .notif-header-top{
            display:flex;
            align-items:center;
            justify-content:space-between;
            gap: 10px;
        }
        .notif-title{
            font-size: 1.35rem;
            font-weight: 950;
            color: rgba(255,255,255,.95);
            margin:0;
            line-height:1.05;
        }

        .notif-header-actions{
            display:flex;
            align-items:center;
            gap:10px;
        }

        .see-all{
            color: rgba(56,189,248,.95);
            font-weight: 850;
            font-size: .92rem;
            text-decoration:none;
            padding:6px 10px;
            border-radius: 12px;
        }
        .see-all:hover{ background: rgba(56,189,248,.10); }

        .dots-btn{
            width:34px; height:34px;
            border-radius: 999px;
            border:1px solid var(--border-soft-2);
            background: rgba(255,255,255,.03);
            color: rgba(255,255,255,.88);
            display:inline-flex;
            align-items:center; justify-content:center;
        }
        .dots-btn:hover{ background: rgba(255,255,255,.05); }

        .notif-tabs{
            margin-top: 10px;
            display:flex;
            gap: 10px;
        }
        .tab-pill{
            padding: 7px 12px;
            border-radius: 999px;
            border: 1px solid var(--border-soft-2);
            background: rgba(255,255,255,.03);
            color: rgba(255,255,255,.88);
            font-weight: 900;
            font-size: .9rem;
            cursor:pointer;
            user-select:none;
        }
        .tab-pill.active{
            border-color: rgba(56,189,248,.35);
            background: rgba(56,189,248,.12);
            color: rgba(255,255,255,.95);
        }

        .notif-subhead{
            padding: 10px 14px 0 14px;
            display:flex;
            align-items:center;
            justify-content:space-between;
            color: rgba(255,255,255,.88);
        }
        .notif-subhead .label{
            font-weight: 900;
            font-size: 1rem;
            opacity:.95;
        }

        .notif-list{
            padding: 8px 8px 10px 8px;
            max-height: 440px;
            overflow:auto;
        }

        .notif-row{
            display:flex;
            align-items:flex-start;
            gap: 12px;
            padding: 10px 10px;
            border-radius: 14px;
            text-decoration:none;
            color: inherit;
        }
        .notif-row:hover{
            background: rgba(255,255,255,.04);
        }

        .notif-avatar{
            width: 52px;
            height: 52px;
            border-radius: 999px;
            background: rgba(255,255,255,.06);
            border:1px solid rgba(255,255,255,.10);
            flex: 0 0 52px;
            display:flex;
            align-items:center;
            justify-content:center;
            font-weight: 950;
            color: rgba(255,255,255,.92);
            overflow:hidden;
        }
        .notif-avatar img{
            width:100%;
            height:100%;
            object-fit:cover;
        }

        .notif-text{
            flex:1;
            min-width:0;
        }
        .notif-msg{
            color: rgba(255,255,255,.92);
            font-weight: 750;
            font-size: .95rem;
            line-height: 1.2;
            word-break: break-word;
        }
        .notif-time{
            margin-top: 4px;
            color: rgba(56,189,248,.95);
            font-weight: 850;
            font-size: .85rem;
        }
        .notif-ref{
            margin-top: 4px;
            color: var(--text-muted);
            font-size: .82rem;
            font-weight: 700;
        }

        .notif-right{
            flex:0 0 auto;
            padding-top: 8px;
            display:flex;
            align-items:center;
            justify-content:center;
            width: 22px;
        }
        .unread-dot{
            width: 10px;
            height: 10px;
            border-radius: 999px;
            background: rgba(56,189,248,.95);
            box-shadow: 0 0 0 3px rgba(56,189,248,.12);
        }

        .notif-footer{
            border-top: 1px solid var(--border-soft);
            padding: 10px 14px;
            display:flex;
            gap: 8px;
            flex-wrap:wrap;
            justify-content:flex-end;
            background: rgba(255,255,255,.01);
        }
        .btn-mini{
            border-radius: 12px;
            padding: .48rem .7rem;
            font-size: .82rem;
            font-weight: 900;
            border:1px solid var(--border-soft-2);
            background: rgba(255,255,255,.03);
            color: rgba(255,255,255,.90);
        }
        .btn-mini:disabled{ opacity:.45; cursor:not-allowed; }
        .btn-mini-accent{ border-color: rgba(56,189,248,.35); background: rgba(56,189,248,.10); }
        .btn-mini-danger{ border-color: rgba(239,68,68,.35); background: rgba(239,68,68,.10); }

        .notif-empty{
            margin: 10px 6px;
            padding: 14px;
            border-radius: 14px;
            border: 1px dashed rgba(255,255,255,.14);
            color: var(--text-muted);
            text-align:center;
            font-weight: 750;
        }

        @media (max-width: 576px){
            .notif-menu{
                width: min(96vw, 420px);
                max-height: 78vh;
            }
            .notif-list{ max-height: calc(78vh - 210px); }
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

{{-- SIDEBAR --}}
<div class="customer-sidebar">
    <h5><i class="bi bi-house-heart"></i> Customer Panel</h5>

    <a href="{{ route('customer.dashboard') }}"
       class="{{ request()->routeIs('customer.dashboard') ? 'active' : '' }}">
        Dashboard
    </a>

    <a href="{{ route('customer.services') }}"
       class="{{ request()->routeIs('customer.services') || request()->routeIs('customer.services.*') ? 'active' : '' }}">
        Services
    </a>

    <a href="{{ route('customer.bookings') }}"
       class="{{ request()->routeIs('customer.bookings') ? 'active' : '' }}">
        My Bookings
    </a>

    <a href="{{ route('customer.bookings.history') }}"
       class="{{ request()->routeIs('customer.bookings.history') ? 'active' : '' }}">
        Bookings History
    </a>

    <a href="{{ route('customer.reviews') }}"
       class="{{ request()->routeIs('customer.reviews') || request()->routeIs('customer.reviews*') ? 'active' : '' }}">
        Reviews
    </a>
</div>

{{-- TOP BAR --}}
<div class="customer-topbar">
    <div class="topbar-left">
        <button class="btn btn-sm btn-outline-light d-lg-none"
                type="button"
                onclick="toggleSidebar()">
            <i class="bi bi-list"></i>
        </button>

        <div class="brand">
            <i class="bi bi-droplet-half"></i>
            <span>CleanTech</span>
        </div>
    </div>

    <div class="topbar-right">

        {{-- Notifications --}}
        <div class="dropdown">
            <a href="#"
               class="notif-trigger position-relative"
               data-bs-toggle="dropdown"
               aria-expanded="false"
               aria-label="Notifications">
                <i class="bi bi-bell fs-5"></i>

                @if($unreadCount)
                    <span class="badge bg-danger notif-badge">
                        {{ $unreadCount }}
                    </span>
                @endif
            </a>

            <div class="dropdown-menu dropdown-menu-end notif-menu" id="notifMenu">
                <div class="notif-header">
                    <div class="notif-header-top">
                        <h5 class="notif-title">Notifications</h5>

                        <div class="notif-header-actions">
                            <button class="dots-btn" type="button" title="More">
                                <i class="bi bi-three-dots"></i>
                            </button>
                        </div>
                    </div>

                    <div class="notif-tabs">
                        <div class="tab-pill active" id="tabAll" data-mode="all">All</div>
                        <div class="tab-pill" id="tabUnread" data-mode="unread">Unread</div>
                    </div>
                </div>

                <div class="notif-subhead">
                    <div class="label">New</div>
                </div>

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
                            <div class="notif-avatar" aria-hidden="true">
                                {{ $initial }}
                            </div>

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

                    <div class="notif-empty d-none" id="notifEmptyUnread">
                        No unread notifications
                    </div>
                </div>

                <div class="notif-footer">
                    <form method="POST" action="{{ route('customer.notifications.readAll', [], false) }}">
                        @csrf
                        <button type="submit" class="btn-mini btn-mini-accent" {{ $unreadCount ? '' : 'disabled' }}>
                            Mark all read
                        </button>
                    </form>

                    <form method="POST" action="{{ route('customer.notifications.clear', [], false) }}"
                          onsubmit="return confirm('Delete all notifications?');">
                        @csrf
                        <button type="submit" class="btn-mini btn-mini-danger" {{ $notifications->count() ? '' : 'disabled' }}>
                            Delete all
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- User --}}
        <div class="dropdown">
            <a class="text-decoration-none text-light dropdown-toggle"
               href="#"
               role="button"
               data-bs-toggle="dropdown"
               aria-expanded="false">
                {{ $customerDisplayName }}
            </a>

            <ul class="dropdown-menu dropdown-menu-end mt-2">
                <li>
                    <a class="dropdown-item" href="{{ route('customer.profile') }}">
                        Profile
                    </a>
                </li>
                <li><hr class="dropdown-divider"></li>
                <li>
                    <form method="POST" action="{{ route('customer.logout') }}">
                        @csrf
                        <button type="submit" class="dropdown-item logout">Logout</button>
                    </form>
                </li>
            </ul>
        </div>

    </div>
</div>

{{-- CONTENT --}}
<div class="customer-content">
    @yield('content')
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
    function toggleSidebar() {
        document.querySelector('.customer-sidebar')?.classList.toggle('show');
    }

    (function(){
        const tabAll = document.getElementById('tabAll');
        const tabUnread = document.getElementById('tabUnread');
        const emptyUnread = document.getElementById('notifEmptyUnread');
        const rows = Array.from(document.querySelectorAll('.notif-row-item'));

        function setMode(mode){
            tabAll?.classList.toggle('active', mode === 'all');
            tabUnread?.classList.toggle('active', mode === 'unread');

            let shown = 0;
            rows.forEach(r => {
                const isRead = r.getAttribute('data-read') === '1';
                const show = (mode === 'all') ? true : !isRead;
                r.style.display = show ? '' : 'none';
                if(show) shown++;
            });

            if(emptyUnread){
                emptyUnread.classList.toggle('d-none', !(mode === 'unread' && shown === 0));
            }
        }

        tabAll?.addEventListener('click', () => setMode('all'));
        tabUnread?.addEventListener('click', () => setMode('unread'));

        setMode('all');
    })();
</script>

</body>
</html>
