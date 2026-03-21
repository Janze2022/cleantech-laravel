<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>@yield('title', 'CleanTech Solutions')</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
:root{
    --site-bg:#020617;
    --site-surface:#071120;
    --site-surface-2:#0c1729;
    --site-text:#e5eefc;
    --site-muted:#9fb2d0;
    --site-border:rgba(255,255,255,.08);
    --site-accent:#38bdf8;
}

html{
    scroll-behavior:smooth;
    scrollbar-color:rgba(56,189,248,.28) rgba(255,255,255,.04);
}

html::-webkit-scrollbar,
body::-webkit-scrollbar{
    width:10px;
    height:10px;
}

html::-webkit-scrollbar-track,
body::-webkit-scrollbar-track{
    background:rgba(255,255,255,.04);
}

html::-webkit-scrollbar-thumb,
body::-webkit-scrollbar-thumb{
    background:rgba(56,189,248,.28);
    border-radius:999px;
}

body{
    margin:0;
    min-height:100vh;
    font-family:'Segoe UI', system-ui, -apple-system, BlinkMacSystemFont, 'Helvetica Neue', sans-serif;
    background:
        radial-gradient(circle at top left, rgba(56,189,248,.10), transparent 20%),
        radial-gradient(circle at top right, rgba(79,70,229,.10), transparent 22%),
        linear-gradient(180deg, #020617 0%, #030916 55%, #020617 100%);
    color:var(--site-text);
    overflow-x:hidden;
}

a{
    transition:color .2s ease, opacity .2s ease, transform .2s ease;
}

main{
    position:relative;
    z-index:1;
}

.app-shell{
    position:relative;
}

.app-shell::before{
    content:"";
    position:fixed;
    inset:0;
    pointer-events:none;
    background:
        linear-gradient(145deg, rgba(255,255,255,.02), transparent 35%),
        linear-gradient(320deg, rgba(56,189,248,.04), transparent 32%);
}

.page-section-card{
    background:linear-gradient(180deg, rgba(9,18,36,.95), rgba(4,11,24,.98));
    border:1px solid var(--site-border);
    border-radius:24px;
    box-shadow:0 24px 60px rgba(0,0,0,.28);
}

.btn{
    border-radius:14px;
    font-weight:800;
}

.form-control,
.form-select,
textarea,
select{
    background:#071120;
    color:#f8fafc;
    border:1px solid rgba(255,255,255,.10);
    border-radius:14px;
    min-height:46px;
    box-shadow:none;
}

.form-control::placeholder,
textarea::placeholder{
    color:#6f86a8;
}

.form-control:focus,
.form-select:focus,
textarea:focus,
select:focus{
    background:#091427;
    color:#fff;
    border-color:rgba(56,189,248,.34);
    box-shadow:0 0 0 .2rem rgba(56,189,248,.12);
}

select option{
    background:#071120;
    color:#f8fafc;
}

.app-footer-space{
    padding-bottom:40px;
}

@media (max-width: 767.98px){
    .app-footer-space{
        padding-bottom:28px;
    }
}
</style>

@stack('styles')
</head>

<body>
<div class="app-shell">
    @include('partials.navbar')

    <main class="app-footer-space">
        @yield('content')
    </main>

    @if(request()->routeIs('home', 'about', 'services', 'pricing', 'blog', 'contact', 'faq', 'how.it.works'))
        @include('partials.public_assistant', ['assistantPage' => request()->route()?->getName()])
    @endif

    @include('partials.footer')
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
@stack('scripts')
</body>
</html>
