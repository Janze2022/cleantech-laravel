<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>@yield('title', 'CleanTech Solutions')</title>

<!-- Bootstrap -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
body {
    font-family: 'Segoe UI', sans-serif;
    background-color: #f8f9fa;
}

/* NAVBAR */
.navbar {
    background: #0d6efd;
}
.navbar-brand,
.nav-link {
    color: #fff !important;
    font-weight: 500;
}

/* HERO */
.hero {
    position: relative;
    min-height: 85vh;
    color: white;
    display: flex;
    align-items: center;
    text-align: center;
    background-image:
        linear-gradient(rgba(13,110,253,.85), rgba(0,180,216,.85)),
        url("https://images.unsplash.com/photo-1581578731548-c64695cc6952");
    background-size: cover;
    background-position: center;
}
.hero h1 {
    font-size: clamp(2.2rem, 5vw, 3.2rem);
    font-weight: 700;
}
.hero p {
    font-size: 1.2rem;
    margin-top: 15px;
}

/* COMMON SECTIONS */
.section-title {
    text-align: center;
    margin-bottom: 40px;
}
.section-title h2 {
    font-weight: 700;
}
.section-title p {
    color: #6c757d;
}

.feature-card {
    border: none;
    border-radius: 16px;
    box-shadow: 0 10px 30px rgba(0,0,0,.08);
    transition: .3s;
}
.feature-card:hover {
    transform: translateY(-6px);
}

.service-card img {
    height: 220px;
    object-fit: cover;
    border-top-left-radius: 16px;
    border-top-right-radius: 16px;
}

/* CTA */
.cta {
    background: linear-gradient(135deg,#0d6efd,#00b4d8);
    color: white;
    padding: 60px 20px;
    border-radius: 25px;
}

/* FOOTER */
footer {
    background: #212529;
    color: #bbb;
    padding: 20px;
    text-align: center;
}
</style>

@stack('styles')
</head>

<body>

@include('partials.navbar')

<main>
    @yield('content')
</main>

@if(request()->routeIs('home', 'about', 'services', 'pricing', 'blog', 'contact', 'faq', 'how.it.works'))
    @include('partials.public_assistant', ['assistantPage' => request()->route()?->getName()])
@endif

@include('partials.footer')

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
@stack('scripts')
</body>
</html>
