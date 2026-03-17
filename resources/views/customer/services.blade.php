@extends('customer.layouts.app')

@section('title', 'Services')

@section('content')

@php
    $tz = config('app.timezone') ?: 'Asia/Manila';
    $selectedDateString = $selectedDateString ?? now($tz)->toDateString();
    $selectedDateLabel = $selectedDateLabel ?? \Carbon\Carbon::parse($selectedDateString, $tz)->format('F d, Y');
    $todayDateString = $todayDateString ?? now($tz)->toDateString();

    $allProvidersArr = $providers
        ? $providers->map(function ($provider) {
            return [
                'id' => $provider->id,
                'first_name' => $provider->first_name,
                'last_name' => $provider->last_name,
                'city' => $provider->city,
                'province' => $provider->province,
                'profile_image' => $provider->profile_image,
            ];
        })->values()
        : collect([]);

    $svcProvidersArr = [];

    if (isset($serviceProviders) && is_array($serviceProviders)) {
        foreach ($serviceProviders as $serviceId => $providerList) {
            $svcProvidersArr[$serviceId] = collect($providerList)->map(function ($provider) {
                return [
                    'id' => $provider->id,
                    'first_name' => $provider->first_name,
                    'last_name' => $provider->last_name,
                    'city' => $provider->city,
                    'province' => $provider->province,
                    'profile_image' => $provider->profile_image,
                ];
            })->values();
        }
    }
@endphp

<style>
:root{
    --services-bg:#020617;
    --services-panel:#050d1d;
    --services-panel-2:#081327;
    --services-border:rgba(255,255,255,.08);
    --services-border-strong:rgba(56,189,248,.22);
    --services-text:rgba(255,255,255,.94);
    --services-muted:rgba(255,255,255,.62);
    --services-accent:#38bdf8;
    --services-success:#22c55e;
    --services-warn:#f59e0b;
}

.services-page{
    width: 100%;
    max-width: 1480px;
    margin: 0 auto;
}

.services-topbar{
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 1rem;
    margin-bottom: 1rem;
}

.services-title{
    margin: 0;
    font-size: clamp(1.9rem, 2.4vw, 2.6rem);
    font-weight: 950;
    letter-spacing: -.03em;
    color: var(--services-text);
}

.services-subtitle{
    margin-top: .35rem;
    color: var(--services-muted);
    max-width: 740px;
    font-size: 1rem;
}

.services-chip-row{
    display: flex;
    flex-wrap: wrap;
    gap: .55rem;
    margin-top: .9rem;
}

.services-chip{
    display: inline-flex;
    align-items: center;
    gap: .4rem;
    border-radius: 999px;
    padding: .36rem .72rem;
    border: 1px solid rgba(255,255,255,.10);
    background: rgba(255,255,255,.03);
    color: rgba(255,255,255,.9);
    font-size: .82rem;
    font-weight: 800;
}

.services-chip.accent{
    border-color: rgba(56,189,248,.24);
    background: rgba(56,189,248,.10);
    color: rgba(56,189,248,.96);
}

.services-back{
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-height: 44px;
    border-radius: 14px;
    padding: .7rem 1rem;
    border: 1px solid rgba(56,189,248,.34);
    color: var(--services-accent);
    text-decoration: none;
    font-weight: 900;
    background: rgba(56,189,248,.05);
}

.services-back:hover{
    color: var(--services-accent);
    background: rgba(56,189,248,.10);
}

.services-layout{
    display: grid;
    grid-template-columns: minmax(0, 1.55fr) minmax(320px, 420px);
    gap: 1rem;
    align-items: start;
}

.services-main{
    min-width: 0;
}

.panel-shell{
    border-radius: 24px;
    border: 1px solid var(--services-border);
    background:
        radial-gradient(1000px 260px at 10% 0%, rgba(56,189,248,.10), transparent 60%),
        linear-gradient(180deg, rgba(8,19,39,.98), rgba(2,6,23,.98));
    box-shadow: 0 30px 90px rgba(0,0,0,.45);
}

.panel-pad{
    padding: 1.2rem;
}

.toolbar-grid{
    display: grid;
    grid-template-columns: minmax(0, 1.45fr) minmax(220px, .82fr) 138px 178px;
    gap: .8rem;
    align-items: end;
}

.toolbar-field{
    min-width: 0;
}

.toolbar-label{
    display: block;
    margin-bottom: .45rem;
    color: var(--services-muted);
    font-size: .88rem;
    font-weight: 700;
}

.toolbar-label-hidden{
    opacity: 0;
    pointer-events: none;
}

.services-input{
    min-height: 48px;
    border-radius: 14px;
    border: 1px solid rgba(255,255,255,.10);
    background: rgba(2,6,23,.55);
    color: rgba(255,255,255,.92);
    box-shadow: none;
}

.services-input::placeholder{
    color: rgba(255,255,255,.38);
}

.services-input:focus{
    background: rgba(2,6,23,.7);
    color: rgba(255,255,255,.94);
    border-color: rgba(56,189,248,.38);
    box-shadow: 0 0 0 .2rem rgba(56,189,248,.12);
}

.date-form{
    display: block;
}

.btn-accent-line,
.btn-accent-fill,
.btn-soft-line{
    min-height: 48px;
    border-radius: 14px;
    font-weight: 900;
    padding: .8rem 1rem;
}

.btn-accent-line{
    border: 1px solid rgba(56,189,248,.36);
    background: transparent;
    color: var(--services-accent);
}

.btn-accent-line:hover{
    background: rgba(56,189,248,.08);
    color: var(--services-accent);
}

.btn-accent-fill{
    border: 1px solid rgba(56,189,248,.34);
    background: rgba(56,189,248,.14);
    color: rgba(255,255,255,.95);
}

.btn-accent-fill:hover{
    background: rgba(56,189,248,.20);
    color: rgba(255,255,255,.96);
}

.btn-soft-line{
    border: 1px solid rgba(255,255,255,.10);
    background: rgba(255,255,255,.03);
    color: rgba(255,255,255,.88);
}

.btn-soft-line:hover{
    background: rgba(255,255,255,.06);
    color: rgba(255,255,255,.94);
}

.services-grid{
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 1rem;
    margin-top: 1rem;
}

.service-card{
    border-radius: 22px;
    border: 1px solid var(--services-border);
    background: linear-gradient(180deg, rgba(4,12,28,.92), rgba(2,6,23,.98));
    overflow: hidden;
    transition: transform .18s ease, border-color .18s ease, box-shadow .18s ease;
    height: 100%;
    display: flex;
    flex-direction: column;
}

.service-card:hover{
    transform: translateY(-4px);
    border-color: rgba(56,189,248,.20);
    box-shadow: 0 24px 60px rgba(0,0,0,.45);
}

.service-card.selected{
    border-color: rgba(56,189,248,.5);
    box-shadow: 0 24px 70px rgba(0,0,0,.52);
}

.service-thumb{
    position: relative;
    height: 188px;
    overflow: hidden;
    background:
        radial-gradient(900px 220px at 20% 0%, rgba(56,189,248,.22), transparent 60%),
        linear-gradient(180deg, rgba(2,6,23,.1), rgba(2,6,23,.92));
}

.service-thumb img{
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
}

.service-thumb::after{
    content: '';
    position: absolute;
    inset: auto 0 0;
    height: 45%;
    background: linear-gradient(180deg, transparent, rgba(2,6,23,.92));
}

.service-body{
    display: flex;
    flex-direction: column;
    gap: .95rem;
    padding: 1rem 1rem 1.1rem;
    flex: 1;
}

.service-head{
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: .75rem;
}

.service-name{
    margin: 0;
    font-size: 1.18rem;
    font-weight: 950;
    color: var(--services-text);
}

.service-desc{
    margin: 0;
    color: var(--services-muted);
    min-height: 3em;
    line-height: 1.5;
}

.service-meta{
    display: flex;
    flex-wrap: wrap;
    gap: .45rem;
}

.service-pill{
    display: inline-flex;
    align-items: center;
    gap: .35rem;
    border-radius: 999px;
    padding: .28rem .62rem;
    border: 1px solid rgba(255,255,255,.10);
    background: rgba(255,255,255,.03);
    color: rgba(255,255,255,.74);
    font-size: .74rem;
    font-weight: 800;
}

.service-pill.accent{
    color: rgba(56,189,248,.96);
    border-color: rgba(56,189,248,.24);
}

.service-pill.success{
    color: rgba(34,197,94,.98);
    border-color: rgba(34,197,94,.24);
}

.service-pill.warn{
    color: rgba(245,158,11,.98);
    border-color: rgba(245,158,11,.24);
}

.service-actions{
    display: flex;
    gap: .7rem;
    margin-top: auto;
}

.service-select-btn,
.service-view-btn{
    min-height: 46px;
    border-radius: 14px;
    font-weight: 900;
    flex: 1;
}

.service-select-btn{
    border: 1px solid rgba(255,255,255,.10);
    background: rgba(255,255,255,.03);
    color: rgba(255,255,255,.88);
}

.service-select-btn:hover{
    background: rgba(255,255,255,.06);
    color: rgba(255,255,255,.94);
}

.service-view-btn{
    border: 1px solid rgba(56,189,248,.32);
    background: rgba(56,189,248,.14);
    color: rgba(255,255,255,.95);
}

.service-view-btn:hover{
    background: rgba(56,189,248,.20);
    color: rgba(255,255,255,.98);
}

.providers-panel{
    position: sticky;
    top: 96px;
}

.providers-header{
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: .75rem;
}

.providers-title{
    margin: 0;
    font-size: 1.55rem;
    font-weight: 950;
    color: var(--services-text);
}

.providers-subtitle{
    margin-top: .28rem;
    color: var(--services-muted);
    line-height: 1.45;
}

.providers-counter{
    min-width: 42px;
    min-height: 42px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 999px;
    border: 1px solid rgba(56,189,248,.22);
    background: rgba(56,189,248,.08);
    color: rgba(56,189,248,.96);
    font-weight: 950;
}

.providers-search{
    margin-top: 1rem;
}

.providers-list{
    display: grid;
    gap: .85rem;
    margin-top: 1rem;
}

.provider-card{
    border-radius: 18px;
    border: 1px solid rgba(255,255,255,.08);
    background: rgba(255,255,255,.025);
    padding: 1rem;
}

.provider-card:hover{
    border-color: rgba(56,189,248,.2);
    transform: translateY(-2px);
}

.provider-top{
    display: flex;
    align-items: center;
    gap: .85rem;
}

.provider-avatar{
    width: 58px;
    height: 58px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid rgba(255,255,255,.12);
    background: rgba(2,6,23,.8);
    flex-shrink: 0;
}

.provider-name{
    color: var(--services-text);
    font-size: 1rem;
    font-weight: 900;
    line-height: 1.2;
}

.provider-location{
    color: var(--services-muted);
    font-size: .88rem;
    margin-top: .18rem;
}

.provider-actions{
    display: flex;
    gap: .65rem;
    margin-top: .9rem;
}

.provider-actions a{
    flex: 1;
    text-align: center;
    text-decoration: none;
}

.selected-summary{
    margin-top: .9rem;
    padding: .9rem 1rem;
    border-radius: 18px;
    border: 1px solid rgba(56,189,248,.16);
    background: rgba(56,189,248,.06);
}

.selected-summary-label{
    color: rgba(56,189,248,.92);
    font-size: .76rem;
    font-weight: 900;
    letter-spacing: .08em;
    text-transform: uppercase;
}

.selected-summary-value{
    margin-top: .3rem;
    font-size: 1.02rem;
    font-weight: 900;
    color: rgba(255,255,255,.95);
}

.empty-hint{
    border-radius: 18px;
    border: 1px dashed rgba(255,255,255,.12);
    background: rgba(255,255,255,.02);
    color: var(--services-muted);
    padding: 1rem;
    line-height: 1.6;
}

.services-empty{
    grid-column: 1 / -1;
}

@media (max-width: 1399px){
    .services-page{
        max-width: 100%;
    }
}

@media (max-width: 1199px){
    .services-layout{
        grid-template-columns: minmax(0, 1fr) 360px;
    }
}

@media (max-width: 991px){
    .services-topbar{
        flex-direction: column;
        align-items: stretch;
    }

    .services-back{
        width: 100%;
    }

    .services-layout{
        grid-template-columns: 1fr;
    }

    .providers-panel{
        position: static;
    }

    .toolbar-grid{
        grid-template-columns: 1fr;
    }

    .service-actions,
    .provider-actions{
        flex-direction: column;
    }

    .toolbar-label-hidden{
        display: none;
    }

    .services-grid{
        grid-template-columns: 1fr;
    }
}

@media (max-width: 575px){
    .panel-pad{
        padding: 1rem;
    }

    .service-thumb{
        height: 174px;
    }

    .providers-title{
        font-size: 1.35rem;
    }
}
</style>

<div class="services-page py-3">
    <div class="services-topbar">
        <div>
            <h1 class="services-title">Services</h1>
            <div class="services-subtitle">
                Choose a service, then review the providers available on {{ $selectedDateLabel }}. The provider list updates instantly for the selected service and date.
            </div>
            <div class="services-chip-row">
                <span class="services-chip accent">Available date: {{ $selectedDateLabel }}</span>
                <span class="services-chip">{{ $services->count() }} service{{ $services->count() === 1 ? '' : 's' }}</span>
                <span class="services-chip">{{ $providers->count() }} provider{{ $providers->count() === 1 ? '' : 's' }} available</span>
            </div>
        </div>

        <a class="services-back" href="{{ route('customer.dashboard') }}">Back to Dashboard</a>
    </div>

    <div class="services-layout">
        <section class="services-main">
            <div class="panel-shell panel-pad">
                <div class="toolbar-grid">
                    <div class="toolbar-field">
                        <label class="toolbar-label" for="svcSearch">Search services</label>
                        <input
                            id="svcSearch"
                            type="text"
                            class="form-control services-input"
                            placeholder="Try general cleaning, deep cleaning, or specific area cleaning"
                        >
                    </div>

                    <div class="toolbar-field">
                        <label class="toolbar-label" for="serviceDate">Available date</label>
                        <form id="servicesDateForm" method="GET" action="{{ route('customer.services') }}" class="date-form">
                            <input
                                id="serviceDate"
                                type="date"
                                name="date"
                                class="form-control services-input"
                                value="{{ $selectedDateString }}"
                                min="{{ $todayDateString }}"
                            >
                        </form>
                    </div>

                    <div class="toolbar-field">
                        <label class="toolbar-label toolbar-label-hidden" for="servicesApplyBtn">Apply</label>
                        <button id="servicesApplyBtn" form="servicesDateForm" class="btn btn-accent-fill w-100" type="submit">Apply Date</button>
                    </div>

                    <div class="toolbar-field">
                        <label class="toolbar-label toolbar-label-hidden" for="clearSelection">Clear</label>
                        <button id="clearSelection" class="btn btn-soft-line w-100" type="button">Clear Selection</button>
                    </div>
                </div>
            </div>

            <div class="services-grid" id="servicesGrid">
                @forelse($services as $service)
                    @php
                        $serviceName = $service->name ?? $service->service_name ?? 'Service';
                        $description = $service->description ?? $service->desc ?? null;
                        $nameKey = strtolower($serviceName);
                        $fallbackThumb = 'https://scentral.ca/wp-content/uploads/2020/01/cleaning-services-1210x723-1.jpeg';

                        if (str_contains($nameKey, 'general')) {
                            $fallbackThumb = 'https://scentral.ca/wp-content/uploads/2020/01/cleaning-services-1210x723-1.jpeg';
                        } elseif (str_contains($nameKey, 'deep')) {
                            $fallbackThumb = 'https://thepolishedbubbleco.com/wp-content/uploads/2025/09/deep-cleaning.jpg';
                        } elseif (str_contains($nameKey, 'specific') || str_contains($nameKey, 'area')) {
                            $fallbackThumb = 'https://homemaidbetter.com/wp-content/uploads/2018/07/Deep-Cleaning.jpg';
                        }

                        $image = $service->image ?? $service->service_image ?? null;
                        $thumb = null;

                        if ($image) {
                            $imagePath = ltrim(str_replace('\\', '/', $image), '/');

                            if (filter_var($image, FILTER_VALIDATE_URL)) {
                                $thumb = $image;
                            } elseif (str_starts_with($imagePath, 'storage/')) {
                                $thumb = asset($imagePath);
                            } else {
                                $thumb = asset('storage/' . $imagePath);
                            }
                        }

                        $thumb = $thumb ?: $fallbackThumb;
                        $localFallback = asset('images/service-generic.svg');
                        $serviceTag = str_contains($nameKey, 'deep') ? 'Deep' : (str_contains($nameKey, 'general') ? 'General' : 'Area');
                    @endphp

                    <article class="service-card" data-service-id="{{ $service->id }}" data-service-name="{{ e($serviceName) }}">
                        <div class="service-thumb">
                            <img
                                src="{{ $thumb }}"
                                alt="{{ $serviceName }}"
                                onerror="this.onerror=null;this.src='{{ $localFallback }}';"
                            >
                        </div>

                        <div class="service-body">
                            <div class="service-head">
                                <div>
                                    <h2 class="service-name">{{ $serviceName }}</h2>
                                </div>
                                <span class="service-pill accent">Select</span>
                            </div>

                            <p class="service-desc">
                                {{ $description ?: 'Tap below to explore the providers currently available for this service.' }}
                            </p>

                            <div class="service-meta">
                                <span class="service-pill">{{ $serviceTag }}</span>
                                <span class="service-pill warn">Fast booking</span>
                                <span class="service-pill success">Verified providers</span>
                            </div>

                            <div class="service-actions">
                                <button class="btn service-select-btn btn-select-service" type="button">Select Service</button>
                                <button class="btn service-view-btn btn-view-providers" type="button">View Providers</button>
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="services-empty">
                        <div class="empty-hint">No services found for this date.</div>
                    </div>
                @endforelse
            </div>
        </section>

        <aside id="providersPanel" class="providers-panel panel-shell panel-pad">
            <div class="providers-header">
                <div>
                    <h2 class="providers-title">Available Providers</h2>
                    <div class="providers-subtitle" id="panelSub">
                        Select a service to filter providers for {{ $selectedDateLabel }}.
                    </div>
                </div>
                <span class="providers-counter" id="providerCountPill">0</span>
            </div>

            <div id="selectedSummary" class="selected-summary d-none">
                <div class="selected-summary-label">Selected Service</div>
                <div class="selected-summary-value" id="selectedServiceValue">-</div>
            </div>

            <div class="providers-search">
                <label class="toolbar-label" for="provSearch">Search available providers</label>
                <input
                    id="provSearch"
                    type="text"
                    class="form-control services-input"
                    placeholder="Search provider name or location"
                >
            </div>

            <div id="providersList" class="providers-list">
                <div class="empty-hint">
                    Pick a service first, and the available providers for {{ $selectedDateLabel }} will appear here.
                </div>
            </div>
        </aside>
    </div>
</div>

<script>
(() => {
    const allProviders = @json($allProvidersArr);
    const serviceProviders = @json($svcProvidersArr);
    const selectedDateLabel = @json($selectedDateLabel);

    const servicesGrid = document.getElementById('servicesGrid');
    const svcSearch = document.getElementById('svcSearch');
    const provSearch = document.getElementById('provSearch');
    const providersList = document.getElementById('providersList');
    const panelSub = document.getElementById('panelSub');
    const providerCountPill = document.getElementById('providerCountPill');
    const clearSelection = document.getElementById('clearSelection');
    const providersPanel = document.getElementById('providersPanel');
    const selectedSummary = document.getElementById('selectedSummary');
    const selectedServiceValue = document.getElementById('selectedServiceValue');

    let selectedServiceId = null;
    let selectedServiceName = null;

    function escapeHtml(str) {
        return (str ?? '').toString()
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function fallbackAvatar() {
        return `{{ asset('images/avatar-placeholder.svg') }}`;
    }

    function avatarUrl(provider) {
        if (provider.profile_image) {
            const filename = String(provider.profile_image).split('/').pop();

            return `{{ route('provider.image.public', ['filename' => '__FILENAME__']) }}`
                .replace('__FILENAME__', filename) + `?v=${Date.now()}`;
        }

        return fallbackAvatar();
    }

    function providerCardHTML(provider) {
        const name = `${provider.first_name ?? ''} ${provider.last_name ?? ''}`.trim() || 'Provider';
        const location = `${provider.city ?? ''}${provider.province ? ', ' + provider.province : ''}`.trim() || '-';
        const viewUrl = `{{ url('/customer/providers') }}/${provider.id}`;
        const bookUrl = `{{ url('/customer/book') }}/${provider.id}?date={{ $selectedDateString }}`;
        const imageUrl = avatarUrl(provider);
        const fallback = fallbackAvatar();

        return `
            <div class="provider-card">
                <div class="provider-top">
                    <img
                        class="provider-avatar"
                        src="${imageUrl}"
                        alt="Provider"
                        onerror="this.onerror=null;this.src='${fallback}';"
                    >
                    <div>
                        <div class="provider-name">${escapeHtml(name)}</div>
                        <div class="provider-location">${escapeHtml(location)}</div>
                    </div>
                </div>
                <div class="provider-actions">
                    <a href="${viewUrl}" class="btn btn-accent-line">View Profile</a>
                    <a href="${bookUrl}" class="btn btn-accent-fill">Book Now</a>
                </div>
            </div>
        `;
    }

    function getProvidersForSelectedService() {
        if (!selectedServiceId) {
            return [];
        }

        if (serviceProviders && serviceProviders[selectedServiceId]) {
            return serviceProviders[selectedServiceId];
        }

        return allProviders;
    }

    function applyProviderSearch(list) {
        const term = (provSearch.value || '').trim().toLowerCase();

        if (!term) {
            return list;
        }

        return list.filter((provider) => {
            const name = `${provider.first_name ?? ''} ${provider.last_name ?? ''}`.toLowerCase();
            const location = `${provider.city ?? ''} ${provider.province ?? ''}`.toLowerCase();

            return name.includes(term) || location.includes(term);
        });
    }

    function scrollToProviders() {
        if (!providersPanel) {
            return;
        }

        const shouldForceScroll = window.matchMedia('(max-width: 991px)').matches;

        if (!shouldForceScroll) {
            return;
        }

        providersPanel.scrollIntoView({
            behavior: 'smooth',
            block: 'start',
        });
    }

    function renderProviders() {
        if (!selectedServiceId) {
            providersList.innerHTML = `<div class="empty-hint">Pick a service first, and the available providers for ${escapeHtml(selectedDateLabel)} will appear here.</div>`;
            providerCountPill.textContent = '0';
            panelSub.textContent = `Select a service to filter providers for ${selectedDateLabel}.`;
            selectedSummary.classList.add('d-none');
            selectedServiceValue.textContent = '-';
            return;
        }

        panelSub.textContent = `Showing providers for ${selectedServiceName} on ${selectedDateLabel}.`;
        selectedSummary.classList.remove('d-none');
        selectedServiceValue.textContent = selectedServiceName;

        let list = getProvidersForSelectedService();
        list = applyProviderSearch(list);

        providerCountPill.textContent = String(list.length);

        if (!list.length) {
            providersList.innerHTML = `<div class="empty-hint">No providers are currently available for ${escapeHtml(selectedServiceName)} on ${escapeHtml(selectedDateLabel)}.</div>`;
            return;
        }

        providersList.innerHTML = list.map(providerCardHTML).join('');
    }

    function setSelectedService(card, shouldScroll) {
        servicesGrid.querySelectorAll('.service-card.selected').forEach((element) => {
            element.classList.remove('selected');
        });

        card.classList.add('selected');
        selectedServiceId = card.getAttribute('data-service-id');
        selectedServiceName = card.getAttribute('data-service-name') || 'Service';
        provSearch.value = '';
        renderProviders();

        if (shouldScroll) {
            scrollToProviders();
        }
    }

    servicesGrid?.addEventListener('click', (event) => {
        const card = event.target.closest('.service-card');

        if (!card) {
            return;
        }

        const viewButton = event.target.closest('.btn-view-providers');
        const selectButton = event.target.closest('.btn-select-service');
        const shouldScroll = Boolean(viewButton);

        if (viewButton || selectButton || event.target === card || !event.target.closest('button')) {
            setSelectedService(card, shouldScroll);
        }
    });

    svcSearch?.addEventListener('input', () => {
        const term = (svcSearch.value || '').trim().toLowerCase();

        servicesGrid.querySelectorAll('.service-card').forEach((card) => {
            const name = (card.getAttribute('data-service-name') || '').toLowerCase();
            const visible = !term || name.includes(term);
            card.style.display = visible ? '' : 'none';
        });
    });

    provSearch?.addEventListener('input', () => {
        renderProviders();
    });

    clearSelection?.addEventListener('click', () => {
        selectedServiceId = null;
        selectedServiceName = null;
        servicesGrid.querySelectorAll('.service-card.selected').forEach((element) => {
            element.classList.remove('selected');
        });
        provSearch.value = '';
        renderProviders();
    });

    renderProviders();
})();
</script>

@endsection
