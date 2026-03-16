@extends('customer.layouts.app')

@section('title', 'Provider Profile')

@section('content')

<style>
.profile-card {
    position: relative;
    background: linear-gradient(180deg, #020b1f, #020617);
    border-radius: 22px;
    padding: 2.5rem;
    box-shadow: 0 35px 80px rgba(0,0,0,.6);
    border: 1px solid rgba(255,255,255,.08);
    color: #e5e7eb;
}

.profile-close {
    position: absolute;
    top: 16px;
    right: 16px;
    width: 34px;
    height: 34px;
    border-radius: 50%;
    border: 1px solid rgba(255,255,255,.15);
    background: rgba(255,255,255,.05);
    color: #e5e7eb;
    display: flex;
    align-items: center;
    justify-content: center;
    text-decoration: none;
    font-size: 18px;
}

.profile-avatar {
    width: 120px;
    height: 120px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid rgba(255,255,255,.15);
}

.profile-name {
    font-weight: 800;
    color: #f8fafc;
}

.profile-location {
    color: #94a3b8;
    font-size: .95rem;
}

.profile-info-row {
    display: flex;
    gap: 1rem;
    padding: .7rem 0;
    border-bottom: 1px solid rgba(255,255,255,.06);
}

.profile-label {
    width: 170px;
    font-size: .85rem;
    color: #94a3b8;
}

.profile-value {
    font-weight: 600;
    color: #e5e7eb;
}
</style>

<div class="container py-5">
    <div class="profile-card">

        {{-- CLOSE --}}
        <a href="{{ route('customer.services') }}" class="profile-close">×</a>

        {{-- HEADER --}}
        <div class="d-flex align-items-center mb-4">
            <img
                src="{{ $provider->profile_image
                    ? asset('storage/'.$provider->profile_image)
                    : 'https://ui-avatars.com/api/?name='.$provider->first_name.'&background=22c55e&color=fff'
                }}"
                class="profile-avatar"
            >

            <div class="ms-4">
                <h4 class="profile-name mb-1">
                    {{ $provider->first_name }} {{ $provider->last_name }}
                </h4>
                <div class="profile-location">
                    {{ $provider->city }}, {{ $provider->province }}
                </div>
            </div>
        </div>

        <hr style="border-color: rgba(255,255,255,.1)">

        {{-- DETAILS --}}
        <div class="profile-info">
            <div class="profile-info-row">
                <div class="profile-label">Contact Number</div>
                <div class="profile-value">{{ $provider->phone }}</div>
            </div>

            <div class="profile-info-row">
                <div class="profile-label">Email Address</div>
                <div class="profile-value">
                    <a href="mailto:{{ $provider->email }}" style="color:#38bdf8;">
                        {{ $provider->email }}
                    </a>
                </div>
            </div>

            <div class="profile-info-row">
                <div class="profile-label">Full Address</div>
                <div class="profile-value">
                    {{ $provider->address }},
                    {{ $provider->barangay }},
                    {{ $provider->city }},
                    {{ $provider->province }},
                    {{ $provider->region }}
                </div>
            </div>

            <div class="profile-info-row">
                <div class="profile-label">Status</div>
                <div class="profile-value">
                    {{ ucfirst(strtolower($provider->status)) }}
                </div>
            </div>
        </div>

        {{-- ✅ BOOK PROVIDER --}}
        <div class="mt-4">
            <a href="{{ route('customer.book.service', $provider->id) }}"
               class="btn btn-success">
                Book This Provider
            </a>
        </div>

    </div>
</div>

@endsection
