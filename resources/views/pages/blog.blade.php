@extends('layouts.app')

@section('title', 'Blog | CleanTech')

@push('styles')
<style>
body { background: #081120; color: #fff; }
.navbar { background: rgba(5,15,35,.95) !important; }
.page-hero {
    padding: 100px 0 70px;
    text-align: center;
    background: linear-gradient(rgba(8,17,32,.85), rgba(8,17,32,.95)),
                url('{{ asset('images/scene-office.svg') }}') center/cover no-repeat;
}
.blog-section { padding: 80px 0; }
.blog-card {
    background: #0f172a;
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 14px 40px rgba(0,0,0,.35);
    height: 100%;
}
.blog-card img {
    width: 100%;
    height: 220px;
    object-fit: cover;
}
.blog-card .content { padding: 1.4rem; }
.blog-card h4 { font-weight: 700; }
.blog-card p { color: #94a3b8; }
.blog-date { color: #60a5fa; font-size: .92rem; font-weight: 600; }
</style>
@endpush

@section('content')

<section class="page-hero">
    <div class="container">
        <h1>CleanTech Blog</h1>
        <p>Helpful cleaning tips, home care ideas, and practical ways to maintain a healthier environment.</p>
    </div>
</section>

<section class="blog-section">
    <div class="container">
        <div class="row g-4">
            <div class="col-md-4">
                <div class="blog-card">
                    <img src="{{ asset('images/scene-cleaning.svg') }}" alt="">
                    <div class="content">
                        <div class="blog-date mb-2">Cleaning Tips</div>
                        <h4>5 Ways to Keep Your Home Fresh Every Day</h4>
                        <p>Simple daily habits that help reduce dust, mess, and odor without spending hours cleaning.</p>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="blog-card">
                    <img src="{{ asset('images/scene-home.svg') }}" alt="">
                    <div class="content">
                        <div class="blog-date mb-2">Home Care</div>
                        <h4>When Should You Schedule a Deep Cleaning?</h4>
                        <p>Learn when a routine clean is not enough and why deep cleaning matters for long-term upkeep.</p>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="blog-card">
                    <img src="{{ asset('images/scene-office.svg') }}" alt="">
                    <div class="content">
                        <div class="blog-date mb-2">Office Hygiene</div>
                        <h4>Why Clean Workspaces Improve Productivity</h4>
                        <p>A cleaner office supports focus, organization, and a more professional environment.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

@endsection