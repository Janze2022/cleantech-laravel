@extends('layouts.app')

@section('title', 'Contact | CleanTech')

@push('styles')
@include('pages._theme')
@endpush

@section('content')
<div class="ct-page">
    <section class="ct-hero">
        <div class="container">
            <div class="ct-hero-shell">
                <div class="ct-hero-main ct-reveal">
                    <span class="ct-eyebrow">Contact Us</span>
                    <h1 class="ct-title">Need help with booking, service details, or provider concerns?</h1>
                    <p class="ct-lead">Send a quick message and CleanTech will open an email draft to the actual support address.</p>
                </div>

                <div class="ct-photo-card tall ct-reveal delay-1">
                    <img src="https://images.unsplash.com/photo-1521791136064-7986c2920216?auto=format&fit=crop&w=1400&q=80" alt="Customer support conversation">
                </div>
            </div>
        </div>
    </section>

    <section class="ct-section">
        <div class="container">
            <div class="ct-contact-grid">
                <article class="ct-card ct-contact-card ct-reveal">
                    <div class="ct-kicker">Direct contact</div>
                    <h3>Reach CleanTech</h3>
                    <div class="ct-contact-list">
                        <div class="ct-contact-line">
                            <strong>Email</strong>
                            <a href="mailto:janzedoysabas@gmail.com" class="ct-inline-link">janzedoysabas@gmail.com</a>
                        </div>
                        <div class="ct-contact-line">
                            <strong>Phone</strong>
                            <span class="ct-muted">09944564055</span>
                        </div>
                        <div class="ct-contact-line">
                            <strong>Location</strong>
                            <span class="ct-muted">Butuan City, Philippines</span>
                        </div>
                    </div>
                </article>

                <article class="ct-card ct-contact-card ct-reveal delay-1">
                    <div class="ct-kicker">Email draft</div>
                    <h3>Send a message</h3>
                    <form id="contactMailForm" class="ct-form-grid">
                        <div>
                            <input type="text" class="ct-input" id="contactName" placeholder="Your name">
                        </div>
                        <div>
                            <input type="text" class="ct-input" id="contactPhone" placeholder="Phone number">
                        </div>
                        <div class="full">
                            <input type="email" class="ct-input" id="contactEmail" placeholder="Email address">
                        </div>
                        <div class="full">
                            <select class="ct-select" id="contactTopic">
                                <option value="Service inquiry">Service inquiry</option>
                                <option value="Booking support">Booking support</option>
                                <option value="Provider concern">Provider concern</option>
                                <option value="General question">General question</option>
                            </select>
                        </div>
                        <div class="full">
                            <textarea class="ct-textarea" id="contactMessage" rows="5" placeholder="Your message"></textarea>
                        </div>
                        <div class="full">
                            <button type="submit" class="ct-button">Open Email Draft</button>
                        </div>
                    </form>
                    <p class="ct-note">This opens your mail app and sends the draft to `janzedoysabas@gmail.com`.</p>
                </article>
            </div>
        </div>
    </section>
</div>
@endsection

@push('scripts')
<script>
(function () {
    const form = document.getElementById('contactMailForm');
    if (!form) {
        return;
    }

    form.addEventListener('submit', function (event) {
        event.preventDefault();

        const name = document.getElementById('contactName')?.value.trim() || 'Not provided';
        const phone = document.getElementById('contactPhone')?.value.trim() || 'Not provided';
        const email = document.getElementById('contactEmail')?.value.trim() || 'Not provided';
        const topic = document.getElementById('contactTopic')?.value.trim() || 'General inquiry';
        const message = document.getElementById('contactMessage')?.value.trim() || 'No message included.';

        const subject = encodeURIComponent('CleanTech Website: ' + topic);
        const body = encodeURIComponent(
            'Name: ' + name + '\n' +
            'Phone: ' + phone + '\n' +
            'Email: ' + email + '\n' +
            'Topic: ' + topic + '\n\n' +
            'Message:\n' + message
        );

        window.location.href = 'mailto:janzedoysabas@gmail.com?subject=' + subject + '&body=' + body;
    });
})();
</script>
@endpush
