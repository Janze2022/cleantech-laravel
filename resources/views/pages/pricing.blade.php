@extends('layouts.app')

@section('title', 'Pricing | CleanTech')

@push('styles')
<style>
body {
    background: #081120;
    color: #fff;
}

.navbar {
    background: rgba(5,15,35,.95) !important;
}

.page-hero {
    padding: 100px 0 70px;
    text-align: center;
    background:
        linear-gradient(rgba(8,17,32,.85), rgba(8,17,32,.95)),
        url('https://images.unsplash.com/photo-1581578731548-c64695cc6952') center/cover no-repeat;
}

.pricing-section {
    padding: 80px 0;
}

.pricing-card {
    background: #0f172a;
    border-radius: 20px;
    padding: 2rem;
    height: 100%;
    box-shadow: 0 14px 40px rgba(0,0,0,.35);
    border: 1px solid rgba(255,255,255,.06);
    transition: .25s ease;
}

.pricing-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 18px 45px rgba(0,0,0,.45);
}

.pricing-card.featured {
    border: 1px solid rgba(59,130,246,.5);
    transform: scale(1.03);
}

.pricing-card.featured:hover {
    transform: scale(1.03) translateY(-4px);
}

.pricing-card h4 {
    font-weight: 700;
    margin-bottom: .8rem;
}

.price {
    font-size: 2rem;
    font-weight: 800;
    color: #60a5fa;
    margin-bottom: .8rem;
}

.pricing-card p,
.pricing-card li {
    color: #94a3b8;
}

.pricing-card ul {
    padding-left: 1.2rem;
    margin-bottom: 0;
}

.pricing-card ul li {
    margin-bottom: .45rem;
}

.price-note {
    margin-top: 1rem;
    font-size: .95rem;
    color: #cbd5e1;
    background: rgba(255,255,255,.03);
    border: 1px solid rgba(255,255,255,.06);
    padding: .9rem 1rem;
    border-radius: 12px;
}

.badge-db {
    display: inline-block;
    padding: .4rem .8rem;
    border-radius: 999px;
    font-size: .78rem;
    font-weight: 700;
    letter-spacing: .3px;
    background: rgba(96,165,250,.14);
    color: #93c5fd;
    border: 1px solid rgba(96,165,250,.25);
    margin-bottom: 1rem;
}

@media (max-width: 991.98px) {
    .pricing-card.featured,
    .pricing-card.featured:hover {
        transform: none;
    }
}
</style>
@endpush

@section('content')

<section class="page-hero">
    <div class="container">
        <h1>CleanTech Pricing</h1>
        <p>Transparent pricing for home and property cleaning services.</p>
    </div>
</section>

<section class="pricing-section">
    <div class="container">
        <div class="row g-4 justify-content-center">

            {{-- SPECIFIC AREA CLEANING --}}
            <div class="col-lg-4 col-md-6">
                <div class="pricing-card">
                    <span class="badge-db">Service Base Price</span>
                    <h4>Specific Area Cleaning</h4>
                    <div class="price">₱800</div>
                    <p>Ideal for targeted cleaning in selected parts of your home.</p>

                    <ul>
                        <li>Kitchen + ₱400</li>
                        <li>Bathroom + ₱500</li>
                        <li>Living Room + ₱600</li>
                        <li>Bedroom + ₱450</li>
                        <li>Dining Area + ₱350</li>
                        <li>Hallway + ₱250</li>
                        <li>Laundry Area + ₱350</li>
                        <li>Balcony / Patio + ₱500</li>
                        <li>Garage + ₱700</li>
                        <li>Storage Room + ₱400</li>
                        <li>Office / Study Room + ₱450</li>
                        <li>Stairs + ₱500</li>
                        <li>Windows (Interior) + ₱400</li>
                    </ul>

                    <div class="price-note">
                        Base service starts at <strong>₱800</strong>. Final total depends on the selected area/s.
                    </div>
                </div>
            </div>

            {{-- GENERAL CLEANING --}}
            <div class="col-lg-4 col-md-6">
                <div class="pricing-card featured">
                    <span class="badge-db">Service Base Price</span>
                    <h4>General Cleaning</h4>
                    <div class="price">₱1,500</div>
                    <p>Best for routine full-home or residential cleaning.</p>

                    <ul>
                        <li>Studio / Small Apartment + ₱0</li>
                        <li>1-Bedroom Apartment + ₱500</li>
                        <li>2-Bedroom Apartment + ₱1,000</li>
                        <li>Small House + ₱1,200</li>
                        <li>Medium House + ₱1,800</li>
                        <li>House (2-Storey) + ₱2,200</li>
                        <li>Townhouse + ₱1,600</li>
                        <li>Duplex House + ₱2,000</li>
                        <li>Bungalow House + ₱1,700</li>
                        <li>Large Family House + ₱2,600</li>
                        <li>Luxury Residence + ₱3,500</li>
                        <li>Villa + ₱4,200</li>
                    </ul>

                    <div class="price-note">
                        Base service starts at <strong>₱1,500</strong>. Final total depends on the property type selected.
                    </div>
                </div>
            </div>

            {{-- DEEP CLEANING --}}
            <div class="col-lg-4 col-md-6">
                <div class="pricing-card">
                    <span class="badge-db">Service Base Price</span>
                    <h4>Deep Cleaning</h4>
                    <div class="price">₱2,500</div>
                    <p>For more intensive, top-to-bottom cleaning and detailed sanitation.</p>

                    <ul>
                        <li>Full detailed cleaning service</li>
                        <li>Ideal for heavily used spaces</li>
                        <li>Recommended for move-ins, move-outs, or seasonal deep cleaning</li>
                    </ul>

                    <div class="price-note">
                        Base price is <strong>₱2,500</strong>. 
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>

@endsection