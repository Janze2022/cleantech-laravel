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
      --sidebar-w: 260px;
      --topbar-h: 64px;

      --bg: #020617;
      --text: #e5e7eb;
      --muted: #cbd5f5;
      --brand: #38bdf8;
      --panel: #0f172a;
      --border: rgba(255,255,255,.06);
      --border-2: rgba(255,255,255,.10);
      --danger: #ef4444;
    }

    html, body { height: 100%; }
    body{
      background: var(--bg);
      color: var(--text);
      margin: 0;
      overflow-x: hidden;
    }

    .provider-topbar{
      height: var(--topbar-h);
      background: var(--bg);
      border-bottom: 1px solid var(--border);
      display:flex;
      align-items:center;
      justify-content:space-between;
      padding: 0 2rem;
      position: fixed;
      top: 0;
      left: var(--sidebar-w);
      right: 0;
      z-index: 1200;
    }

    .provider-topbar-spacer{
      height: var(--topbar-h);
      width: 100%;
    }

    .provider-sidebar{
      width: var(--sidebar-w);
      height: 100dvh;
      background: var(--bg);
      position: fixed;
      left: 0;
      top: 0;
      padding: 1.5rem;
      border-right: 1px solid var(--border);
      z-index: 1100;
      transition: left .3s ease;
      overflow-y: auto;
    }

    .provider-sidebar h5{
      color: var(--brand);
      margin-bottom: 2rem;
    }

    .provider-sidebar a{
      display:block;
      padding:.75rem 1rem;
      margin-bottom:.4rem;
      border-radius:12px;
      color:var(--muted);
      text-decoration:none;
      font-size:.9rem;
    }

    .provider-sidebar a:hover,
    .provider-sidebar a.active{
      background: var(--panel);
      color: var(--brand);
    }

    .provider-content-wrap{
      margin-left: var(--sidebar-w);
    }

    .provider-content{
      padding: 2rem;
      min-height: calc(100dvh - var(--topbar-h));
    }

    @media (max-width: 991px){
      .provider-topbar{ left: 0; padding: 0 1rem; }

      .provider-sidebar{
        left: calc(-1 * var(--sidebar-w));
      }
      .provider-sidebar.show{ left: 0; }

      .provider-content-wrap{ margin-left: 0; }
      .provider-content{ padding: 1.25rem; }

      .brand span{ display:none; }
    }

    .brand{
      font-weight: 600;
      display:flex;
      align-items:center;
      gap:.6rem;
    }
    .brand i{ color: var(--brand); }

    .user-dropdown .dropdown-menu,
    .notif-menu{
      background: var(--bg);
      border: 1px solid rgba(255,255,255,.08);
      border-radius: 14px;
    }

    .user-dropdown .dropdown-item{ color: var(--muted); font-size: .9rem; }
    .user-dropdown .dropdown-item:hover{ background: var(--panel); color: var(--brand); }
    .user-dropdown .logout{ color:#ef4444; }

    .topbar-right{
      display:flex;
      align-items:center;
      gap:.85rem;
    }

    .notif-trigger{
      width:40px;
      height:40px;
      display:inline-flex;
      align-items:center;
      justify-content:center;
      border-radius:999px;
      border:1px solid var(--border-2);
      background: rgba(255,255,255,.03);
      color: rgba(255,255,255,.92);
      text-decoration:none;
      position: relative;
    }

    .notif-trigger:hover{
      border-color: rgba(56,189,248,.25);
      background: rgba(255,255,255,.05);
      color:#fff;
    }

    .notif-badge{
      position:absolute;
      top:-4px;
      right:-4px;
      border-radius:999px;
      padding:.18rem .42rem;
      font-size:.72rem;
    }

    .notif-menu{
      width: 360px;
      padding: 0;
      overflow: hidden;
    }

    .notif-header{
      padding: 12px 14px;
      border-bottom: 1px solid var(--border);
      font-weight: 900;
      color: #fff;
    }

    .notif-list{
      max-height: 420px;
      overflow: auto;
      padding: 8px;
    }

    .notif-row{
      display:block;
      text-decoration:none;
      color:inherit;
      padding: 10px 12px;
      border-radius: 12px;
      margin-bottom: 6px;
      border: 1px solid transparent;
      background: rgba(255,255,255,.02);
    }

    .notif-row:hover{
      background: rgba(255,255,255,.05);
      border-color: rgba(255,255,255,.06);
    }

    .notif-row.unread{
      background: rgba(56,189,248,.08);
      border-color: rgba(56,189,248,.14);
    }

    .notif-msg{
      color:#fff;
      font-size:.92rem;
      font-weight:700;
      line-height:1.3;
    }

    .notif-time{
      margin-top:4px;
      color: var(--brand);
      font-size:.8rem;
      font-weight:800;
    }

    .notif-ref{
      margin-top:4px;
      color: rgba(255,255,255,.62);
      font-size:.78rem;
      font-weight:700;
    }

    .notif-empty{
      padding: 14px;
      color: rgba(255,255,255,.60);
      text-align:center;
      font-weight:700;
    }

    .notif-footer{
      border-top: 1px solid var(--border);
      padding: 10px 12px;
      display:flex;
      justify-content:flex-end;
      gap:8px;
      background: rgba(255,255,255,.02);
    }

    .btn-mini{
      border-radius: 10px;
      padding: .45rem .7rem;
      font-size: .82rem;
      font-weight: 800;
      border:1px solid var(--border-2);
      background: rgba(255,255,255,.03);
      color: #fff;
    }

    .btn-mini-danger{
      border-color: rgba(239,68,68,.30);
      background: rgba(239,68,68,.08);
    }

    @media (max-width: 576px){
      .notif-menu{
        width: min(94vw, 380px);
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
@endphp

  {{-- TOPBAR --}}
  <div class="provider-topbar">
    <div class="d-flex align-items-center">
      <button class="btn btn-sm btn-outline-light d-lg-none me-2" onclick="toggleSidebar()">
        <i class="bi bi-list"></i>
      </button>

      <div class="brand">
        <i class="bi bi-droplet-half"></i>
        <span>CleanTech</span>
      </div>
    </div>

    <div class="topbar-right">
      <div class="dropdown">
        <a href="#"
           class="notif-trigger"
           data-bs-toggle="dropdown"
           aria-expanded="false">
          <i class="bi bi-bell fs-5"></i>

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

              <a href="{{ route('provider.notifications.open', ['id' => $n->id], false) }}"
                 class="notif-row {{ $isUnread ? 'unread' : '' }}">
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

            <form method="POST"
                  action="{{ route('provider.notifications.clear', [], false) }}"
                  onsubmit="return confirm('Delete all notifications?');">
              @csrf
              <button type="submit" class="btn-mini btn-mini-danger" {{ $providerNotifications->count() ? '' : 'disabled' }}>
                Delete all
              </button>
            </form>
          </div>
        </div>
      </div>

      <div class="dropdown user-dropdown">
        <a class="text-decoration-none text-light dropdown-toggle"
           href="#"
           role="button"
           data-bs-toggle="dropdown">
          {{ session('name') ?? session('provider_name') ?? 'Provider' }}
        </a>

        <ul class="dropdown-menu dropdown-menu-end mt-2">
          <li><a class="dropdown-item" href="{{ route('provider.profile') }}">Profile</a></li>
          <li><hr class="dropdown-divider"></li>
          <li>
            <form method="POST" action="{{ route('provider.logout') }}">
              @csrf
              <button class="dropdown-item logout">Logout</button>
            </form>
          </li>
        </ul>
      </div>
    </div>
  </div>

  {{-- SIDEBAR --}}
  <div class="provider-sidebar" id="providerSidebar">
    <h5><i class="bi bi-person-badge"></i> Provider Panel</h5>

    <a href="{{ route('provider.dashboard') }}"
       class="{{ request()->routeIs('provider.dashboard') ? 'active' : '' }}">
        Dashboard
    </a>

    <a href="{{ route('provider.availability') }}"
       class="{{ request()->routeIs('provider.availability*') ? 'active' : '' }}">
        Availability
    </a>

    <a href="{{ route('provider.bookings') }}"
       class="{{ request()->routeIs('provider.bookings') ? 'active' : '' }}">
        Bookings
    </a>

    <a href="{{ route('provider.bookings.history') }}"
       class="{{ request()->routeIs('provider.bookings.history') ? 'active' : '' }}">
        Booking History
    </a>

    <a href="{{ route('provider.analytics') }}"
       class="{{ request()->routeIs('provider.analytics') ? 'active' : '' }}">
        Analytics
    </a>

    <a href="{{ route('provider.earnings') }}"
       class="{{ request()->routeIs('provider.earnings') ? 'active' : '' }}">
        Earnings
    </a>

    <a href="{{ route('provider.ratings') }}"
       class="{{ request()->routeIs('provider.ratings') ? 'active' : '' }}">
        My Ratings
    </a>
  </div>

  {{-- CONTENT --}}
  <div class="provider-content-wrap">
    <div class="provider-topbar-spacer"></div>

    <div class="provider-content">
      @yield('content')
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    function toggleSidebar(){
      document.getElementById('providerSidebar').classList.toggle('show');
    }
  </script>

</body>
</html>
