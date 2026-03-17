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
                    <span class="ct-eyebrow">Contact CleanTech</span>
                    <h1 class="ct-title">Reach out quickly for booking questions, provider concerns, or general help.</h1>
                    <p class="ct-lead">This contact page now opens a real email draft to your actual support address, so messages no longer feel like a dead-end form.</p>
                </div>

                <div class="ct-hero-side ct-reveal delay-1">
                    <div>
                        <div class="ct-side-label">Primary email</div>
                        <div class="ct-side-value">
                            <a href="mailto:janzedoysabas@gmail.com" class="ct-inline-link">janzedoysabas@gmail.com</a>
                        </div>
                    </div>

                    <p class="ct-side-copy">For the fastest route, use the quick email draft below or send directly through your mail app.</p>

                    <div class="ct-actions">
                        <a href="mailto:janzedoysabas@gmail.com" class="ct-button">Email CleanTech</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="ct-section">
        <div class="container">
            <div class="ct-contact-grid">
                <article class="ct-card ct-contact-card ct-reveal">
                    <div class="ct-kicker">Contact details</div>
                    <h3>Ways to reach the team</h3>

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

                        <div class="ct-contact-line">
                            <strong>Support hours</strong>
                            <span class="ct-muted">Monday to Saturday, 8:00 AM to 6:00 PM</span>
                        </div>
                    </div>
                </article>

                <article class="ct-card ct-contact-card ct-reveal delay-1">
                    <div class="ct-kicker">Open email draft</div>
                    <h3>Send a message</h3>
                    <p class="ct-muted">Fill this out and tap the button below. CleanTech will open your email app with the details already prepared for <a href="mailto:janzedoysabas@gmail.com" class="ct-inline-link">janzedoysabas@gmail.com</a>.</p>

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
                            <textarea class="ct-textarea" id="contactMessage" rows="6" placeholder="Tell us what you need help with."></textarea>
                        </div>
                        <div class="full">
                            <button type="submit" class="ct-button">Open Email Draft</button>
                        </div>
                    </form>

                    <p class="ct-note">If your mail app does not open automatically, you can still email directly using the address above.</p>
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
