@extends('layouts.app')

@section('title', 'How It Works | CleanTech')

@push('styles')
<style>
body { background: #081120; color: #fff; }
.navbar { background: rgba(5,15,35,.95) !important; }
.page-hero {
    padding: 100px 0 70px;
    text-align: center;
    background: linear-gradient(rgba(8,17,32,.85), rgba(8,17,32,.95)),
                url('{{ asset('images/scene-cleaning.svg') }}?auto=format&fit=crop&w=1600&q=80') center/cover no-repeat;
}
.step-section { padding: 80px 0; }
.step-card {
    background: #0f172a;
    border-radius: 20px;
    padding: 2rem;
    height: 100%;
    box-shadow: 0 14px 40px rgba(0,0,0,.35);
}
.step-number {
    width: 56px;
    height: 56px;
    border-radius: 50%;
    background: linear-gradient(135deg, #2563eb, #4f46e5);
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 800;
    font-size: 1.2rem;
    margin-bottom: 1rem;
}
.step-card h4 { font-weight: 700; }
.step-card p { color: #94a3b8; }
</style>
@endpush

@section('content')

<section class="page-hero">
    <div class="container">
        <h1>How CleanTech Works</h1>
        <p>Booking your cleaning service is simple, fast, and convenient.</p>
    </div>
</section>

<section class="step-section">
    <div class="container">
        <div class="row g-4">
            <div class="col-md-4">
                <div class="step-card">
                    <div class="step-number">1</div>
                    <h4>Choose a Service</h4>
                    <p>Select the type of cleaning you need based on your home, office, or property requirements.</p>
                </div>
            </div>

            <div class="col-md-4">
                <div class="step-card">
                    <div class="step-number">2</div>
                    <h4>Book Your Schedule</h4>
                    <p>Pick your preferred date, time, and location through our booking system.</p>
                </div>
            </div>

            <div class="col-md-4">
                <div class="step-card">
                    <div class="step-number">3</div>
                    <h4>Get Matched</h4>
                    <p>Your request is assigned to a verified and qualified service provider.</p>
                </div>
            </div>

            <div class="col-md-4">
                <div class="step-card">
                    <div class="step-number">4</div>
                    <h4>Service Delivery</h4>
                    <p>The assigned cleaner arrives and performs the requested service professionally.</p>
                </div>
            </div>

            <div class="col-md-4">
                <div class="step-card">
                    <div class="step-number">5</div>
                    <h4>Review the Service</h4>
                    <p>After the job is completed, you can rate the provider and share your feedback.</p>
                </div>
            </div>

            <div class="col-md-4">
                <div class="step-card">
                    <div class="step-number">6</div>
                    <h4>Enjoy a Cleaner Space</h4>
                    <p>Relax and enjoy a fresh, neat, and healthier environment.</p>
                </div>
            </div>
        </div>
    </div>
</section>

@endsection