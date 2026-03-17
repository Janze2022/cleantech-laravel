@extends('layouts.app')

@section('title', 'Services | CleanTech')

@push('styles')
<style>
body { background: #081120; color: #fff; }
.navbar { background: rgba(5,15,35,.95) !important; }
.page-hero {
    padding: 100px 0 70px;
    text-align: center;
    background: linear-gradient(rgba(8,17,32,.82), rgba(8,17,32,.92)),
                url('https://images.unsplash.com/photo-1520607162513-77705c0f0d4a?auto=format&fit=crop&w=1600&q=80') center/cover no-repeat;
}
.page-hero h1 { font-weight: 800; }
.page-hero p { color: #cbd5e1; max-width: 700px; margin: auto; }
.section-dark { padding: 80px 0; }
.service-box {
    background: #0f172a;
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 14px 40px rgba(0,0,0,.35);
    height: 100%;
}
.service-box img {
    width: 100%;
    height: 240px;
    object-fit: cover;
}
.service-box .content { padding: 1.4rem; }
.service-box h4 { color: #fff; font-weight: 700; }
.service-box p { color: #94a3b8; }
</style>
@endpush

@section('content')

<section class="page-hero">
    <div class="container">
        <h1>Our Cleaning Services</h1>
        <p>CleanTech offers dependable residential and commercial cleaning solutions tailored for every type of space.</p>
    </div>
</section>

<section class="section-dark">
    <div class="container">
        <div class="row g-4">
            <div class="col-md-4">
                <div class="service-box">
                    <img src="https://hongkongofw.com/wp-content/uploads/2023/06/deep-cleaning.jpg" alt="">
                    <div class="content">
                        <h4>Deep Home Cleaning</h4>
                        <p>Detailed whole-house cleaning for bedrooms, kitchens, bathrooms, and living areas.</p>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="service-box">
                    <img src="https://lirp.cdn-website.com/4403d184/dms3rep/multi/opt/AdobeStock_267548289-1920w.jpeg" alt="">
                    <div class="content">
                        <h4>Office Cleaning</h4>
                        <p>Routine office sanitation, desk cleaning, common area care, and workspace maintenance.</p>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="service-box">
                    <img src="https://images.unsplash.com/photo-1584622650111-993a426fbf0a?auto=format&fit=crop&w=1400&q=80" alt="">
                    <div class="content">
                        <h4>Post Construction Cleaning</h4>
                        <p>Dust removal, surface treatment, and debris cleanup after construction or renovation.</p>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="service-box">
                    <img src="https://images.unsplash.com/photo-1603712725038-e9334ae8f39f?auto=format&fit=crop&w=1400&q=80" alt="">
                    <div class="content">
                        <h4>Move-In / Move-Out Cleaning</h4>
                        <p>Prepare a space before moving in or restore it before turnover with a complete cleaning package.</p>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="service-box">
                    <img src="https://images.unsplash.com/photo-1563453392212-326f5e854473?auto=format&fit=crop&w=1400&q=80" alt="">
                    <div class="content">
                        <h4>Bathroom Sanitizing</h4>
                        <p>Thorough disinfection of sinks, tiles, mirrors, toilets, and shower areas for a healthier space.</p>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="service-box">
                    <img src="https://images.unsplash.com/photo-1527515637462-cff94eecc1ac?auto=format&fit=crop&w=1400&q=80" alt="">
                    <div class="content">
                        <h4>Kitchen Cleaning</h4>
                        <p>Grease removal, countertop cleaning, cabinet wipe-down, and appliance surface care.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

@endsection