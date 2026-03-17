@extends('layouts.app')

@section('title', 'Contact | CleanTech')

@push('styles')
<style>
body { background: #081120; color: #fff; }
.navbar { background: rgba(5,15,35,.95) !important; }
.page-hero {
    padding: 100px 0 70px;
    text-align: center;
    background: linear-gradient(rgba(8,17,32,.85), rgba(8,17,32,.95)),
                url('{{ asset('images/scene-cleaning.svg') }}') center/cover no-repeat;
}
.contact-section { padding: 80px 0; }
.contact-box {
    background: #0f172a;
    border-radius: 20px;
    padding: 2rem;
    box-shadow: 0 14px 40px rgba(0,0,0,.35);
    height: 100%;
}
.contact-box h4 { font-weight: 700; }
.contact-box p, .contact-box li { color: #94a3b8; }
.form-control, .form-control:focus, .form-select, .form-select:focus {
    background: #111827;
    border: 1px solid rgba(255,255,255,.08);
    color: #fff;
    box-shadow: none;
}
.form-control::placeholder {
    color: #9ca3af;
}
.btn-main {
    background: linear-gradient(135deg, #2563eb, #4f46e5);
    border: none;
    color: #fff;
    padding: 12px 24px;
    border-radius: 12px;
    text-decoration: none;
    font-weight: 600;
}
</style>
@endpush

@section('content')

<section class="page-hero">
    <div class="container">
        <h1>Contact Us</h1>
        <p>Have questions about our services or booking process? Reach out to CleanTech anytime.</p>
    </div>
</section>

<section class="contact-section">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-5">
                <div class="contact-box">
                    <h4>Get in Touch</h4>
                    <p class="mt-3">We are here to help you with service inquiries, bookings, and provider concerns.</p>

                    <ul class="list-unstyled mt-4">
                        <li class="mb-3"><strong class="text-white">Phone:</strong> 09944564055</li>
                        <li class="mb-3"><strong class="text-white">Location:</strong> Butuan City, Philippines</li>
                        <li class="mb-3"><strong class="text-white">Hours:</strong> Monday to Saturday, 8:00 AM - 6:00 PM</li>
                    </ul>
                </div>
            </div>

            <div class="col-lg-7">
                <div class="contact-box">
                    <h4>Send a Message</h4>
                    <form class="mt-4">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <input type="text" class="form-control" placeholder="Your Name">
                            </div>
                            <div class="col-md-6">
                                <input type="text" class="form-control" placeholder="Phone Number">
                            </div>
                            <div class="col-12">
                                <input type="email" class="form-control" placeholder="Email Address">
                            </div>
                            <div class="col-12">
                                <textarea class="form-control" rows="5" placeholder="Your Message"></textarea>
                            </div>
                            <div class="col-12">
                                <button type="button" class="btn-main">Send Message</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

@endsection