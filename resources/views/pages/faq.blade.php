@extends('layouts.app')

@section('title', 'FAQ | CleanTech')

@push('styles')
<style>
body { background: #081120; color: #fff; }
.navbar { background: rgba(5,15,35,.95) !important; }
.page-hero {
    padding: 100px 0 70px;
    text-align: center;
    background: linear-gradient(rgba(8,17,32,.85), rgba(8,17,32,.95)),
                url('https://images.unsplash.com/photo-1450101499163-c8848c66ca85?auto=format&fit=crop&w=1600&q=80') center/cover no-repeat;
}
.faq-section { padding: 80px 0; }
.faq-card {
    background: #0f172a;
    border-radius: 18px;
    padding: 1.5rem;
    box-shadow: 0 14px 40px rgba(0,0,0,.35);
    margin-bottom: 1rem;
}
.faq-card h5 { font-weight: 700; }
.faq-card p { color: #94a3b8; margin-bottom: 0; }
</style>
@endpush

@section('content')

<section class="page-hero">
    <div class="container">
        <h1>Frequently Asked Questions</h1>
        <p>Answers to common questions about booking, services, payments, and providers.</p>
    </div>
</section>

<section class="faq-section">
    <div class="container">
        <div class="faq-card">
            <h5>How do I book a cleaning service?</h5>
            <p>You can create a customer account, choose a service, select your preferred schedule, and confirm your booking online.</p>
        </div>

        <div class="faq-card">
            <h5>Are CleanTech providers verified?</h5>
            <p>Yes. Providers go through registration and verification before they are approved on the platform.</p>
        </div>

        <div class="faq-card">
            <h5>Can I choose the date and time of service?</h5>
            <p>Yes. During booking, you can select your available date and preferred time based on the schedule offered.</p>
        </div>

        <div class="faq-card">
            <h5>Do you offer office cleaning?</h5>
            <p>Yes. CleanTech supports both residential and office cleaning services.</p>
        </div>

        <div class="faq-card">
            <h5>How much does cleaning cost?</h5>
            <p>Pricing depends on the service type and scope of work. You can check the Pricing page for sample rates.</p>
        </div>
    </div>
</section>

@endsection