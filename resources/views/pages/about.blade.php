@extends('layouts.app')

@section('title', 'About | CleanTech')

@push('styles')
<style>
body { background: #081120; color: #fff; }
.navbar { background: rgba(5,15,35,.95) !important; }
.page-hero {
    padding: 100px 0 70px;
    text-align: center;
    background: linear-gradient(rgba(8,17,32,.85), rgba(8,17,32,.95)),
                url('https://images.unsplash.com/photo-1521791136064-7986c2920216?auto=format&fit=crop&w=1600&q=80') center/cover no-repeat;
}
.about-section { padding: 80px 0; }
.about-card {
    background: #0f172a;
    border-radius: 20px;
    padding: 2rem;
    box-shadow: 0 14px 40px rgba(0,0,0,.35);
    height: 100%;
}
.about-card h4 { font-weight: 700; }
.about-card p { color: #94a3b8; }
</style>
@endpush

@section('content')

<section class="page-hero">
    <div class="container">
        <h1>About CleanTech</h1>
        <p>CleanTech is a service platform designed to connect customers with trusted, efficient, and professional cleaning providers.</p>
    </div>
</section>

<section class="about-section">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-6">
                <div class="about-card">
                    <h4>Our Mission</h4>
                    <p>To provide reliable and affordable home cleaning, and maintenance services that help make everyday living easier. We aim to deliver services with honesty, convenience, and customer care at the center of what we do.</p>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="about-card">
                    <h4>Our Vision</h4>
                    <p>To be a trusted and accessible service provider in the community, known for dependable work, practical solutions, and a commitment to helping households when they need support.</p>
                </div>
            </div>

            <div class="col-12">
                <div class="about-card">
                    <h4>Why Choose CleanTech?</h4>
                    <p>We focus on convenience, verified providers, practical pricing, and an easy booking experience for customers in Butuan City.</p>
                </div>
            </div>
        </div>
    </div>
</section>

@endsection