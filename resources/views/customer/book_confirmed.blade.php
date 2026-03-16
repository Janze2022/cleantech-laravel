@extends('customer.layouts.app')

@section('title', 'Booking Confirmed')

@section('content')

@php
    // ✅ Safe getters (works even if controller didn't join service/option/provider)
    $service = $booking->service
        ?? $booking->service_name
        ?? '—';

    $option = $booking->option
        ?? $booking->option_label
        ?? $booking->option_name
        ?? '—';

    $provider = $booking->provider
        ?? $booking->provider_name
        ?? trim((string)($booking->provider_first_name ?? '').' '.(string)($booking->provider_last_name ?? ''))
        ?? '—';

    $city = $booking->city ?? $booking->provider_city ?? '';
    $province = $booking->province ?? $booking->provider_province ?? '';

    $location = trim($city . ($province ? ', '.$province : ''));
@endphp

<style>
:root {
    --bg-card:#020b1f;
    --border-soft:rgba(255,255,255,.08);
    --text-muted:rgba(255,255,255,.55);
    --accent:#38bdf8;
    --success:#22c55e;
}

.confirm-wrap{ max-width:900px; margin:2rem auto; padding:0 1rem; }

.confirm-card{
    background:linear-gradient(180deg,#020b1f,#020617);
    border:1px solid var(--border-soft);
    border-radius:18px;
    padding:2rem;
}

.badge-success{
    display:inline-flex; align-items:center; gap:.5rem;
    padding:.4rem .8rem; border-radius:999px;
    background:rgba(34,197,94,.12); color:var(--success);
    font-size:.75rem; font-weight:800; letter-spacing:.06em;
    text-transform:uppercase;
}

.muted{ color:var(--text-muted); }

.summary-grid{
    margin-top:1.5rem;
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:1rem;
}

.summary-item{
    border:1px solid rgba(255,255,255,.06);
    border-radius:14px;
    padding:1rem;
    background:rgba(2,6,23,.35);
}

.summary-item .label{
    font-size:.75rem;
    letter-spacing:.08em;
    text-transform:uppercase;
    color:var(--text-muted);
}

.summary-item .value{ margin-top:.35rem; font-weight:800; }

.total{
    margin-top:1.5rem; padding-top:1.25rem;
    border-top:1px solid var(--border-soft);
    display:flex; justify-content:space-between; align-items:center;
}

.total .amount{ font-size:1.6rem; font-weight:900; color:var(--accent); }

.btn-row{ margin-top:1.25rem; display:flex; gap:.75rem; flex-wrap:wrap; }
.btn-primary{
    background:linear-gradient(180deg,#0ea5e9,#38bdf8);
    border:none; font-weight:800; padding:.65rem 1rem; border-radius:12px;
    color:#001018; text-decoration:none;
}
.btn-ghost{
    border:1px solid rgba(255,255,255,.12);
    background:transparent; color:#fff; font-weight:700;
    padding:.65rem 1rem; border-radius:12px;
    text-decoration:none;
}

.redirect-note{ margin-top:1rem; color:var(--text-muted); font-size:.9rem; }

@media (max-width: 768px){
    .confirm-card{ padding: 1.2rem; }
    .summary-grid{ grid-template-columns: 1fr; }
    .btn-primary, .btn-ghost{ width:100%; text-align:center; }
}
</style>

<div class="confirm-wrap">
    <div class="confirm-card">
        <span class="badge-success">Booking Confirmed</span>
        <h3 class="mt-2 mb-1">Your booking is confirmed</h3>
        <div class="muted">Reference: <strong>{{ $booking->reference_code }}</strong></div>

        <div class="summary-grid">
            <div class="summary-item">
                <div class="label">Service</div>
                <div class="value">{{ $service }}</div>
            </div>

            <div class="summary-item">
                <div class="label">Option</div>
                <div class="value">{{ $option }}</div>
            </div>

            <div class="summary-item">
                <div class="label">Schedule</div>
                <div class="value">{{ $booking->booking_date }} | {{ $booking->time_start }} – {{ $booking->time_end }}</div>
            </div>

            <div class="summary-item">
                <div class="label">Contact</div>
                <div class="value">{{ $booking->contact_phone ?? '—' }}</div>
            </div>

            <div class="summary-item">
                <div class="label">Provider</div>
                <div class="value">{{ trim($provider) !== '' ? $provider : '—' }}</div>
                @if($location !== '')
                    <div class="muted">{{ $location }}</div>
                @endif
            </div>

            <div class="summary-item">
                <div class="label">Address</div>
                <div class="value">{{ $booking->address ?? '—' }}</div>
            </div>
        </div>

        <div class="total">
            <div class="muted">Total Price</div>
            <div class="amount">₱{{ number_format((float)($booking->price ?? 0), 2) }}</div>
        </div>

        <div class="btn-row">
            @if(\Illuminate\Support\Facades\Route::has('customer.bookings.show'))
                <a class="btn-primary" href="{{ route('customer.bookings.show', $booking->reference_code) }}">View Details</a>
            @endif
            @if(\Illuminate\Support\Facades\Route::has('customer.bookings'))
                <a class="btn-ghost" href="{{ route('customer.bookings') }}">Go to My Bookings</a>
            @endif
        </div>

        <div class="redirect-note">
            Redirecting to <strong>My Bookings</strong> in <span id="secs">5</span> seconds…
        </div>
    </div>
</div>

<script>
let s = 5;
const el = document.getElementById('secs');
const t = setInterval(() => {
    s--;
    el.textContent = s;
    if(s <= 0){
        clearInterval(t);
        window.location.href = @json(route('customer.bookings'));
    }
}, 1000);
</script>

@endsection
