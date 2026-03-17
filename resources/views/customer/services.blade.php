@extends('customer.layouts.app')

@section('title', 'Services')

@section('content')

@php
    $tz = config('app.timezone') ?: 'Asia/Manila';
    $selectedDateString = $selectedDateString ?? now($tz)->toDateString();
    $selectedDateLabel = $selectedDateLabel ?? \Carbon\Carbon::parse($selectedDateString, $tz)->format('F d, Y');
    $todayDateString = $todayDateString ?? now($tz)->toDateString();

    $allProvidersArr = $providers
        ? $providers->map(function ($p) {
            return [
                'id' => $p->id,
                'first_name' => $p->first_name,
                'last_name' => $p->last_name,
                'city' => $p->city,
                'province' => $p->province,
                'profile_image' => $p->profile_image,
                'availability_date' => $p->availability_date ?? null,
            ];
        })->values()
        : collect([]);

    $svcProvidersArr = [];
    if (isset($serviceProviders) && is_array($serviceProviders)) {
        foreach ($serviceProviders as $sid => $plist) {
            $svcProvidersArr[$sid] = collect($plist)->map(function ($p) {
                return [
                    'id' => $p->id,
                    'first_name' => $p->first_name,
                    'last_name' => $p->last_name,
                    'city' => $p->city,
                    'province' => $p->province,
                    'profile_image' => $p->profile_image,
                    'availability_date' => $p->availability_date ?? null,
                ];
            })->values();
        }
    }
@endphp

<style>
:root{
    --bg-deep:#020617;
    --bg-card:#020b1f;
    --border-soft:rgba(255,255,255,.08);
    --text-muted:rgba(255,255,255,.55);
    --accent:#38bdf8;
    --success:#22c55e;
    --warn:#f59e0b;
}

.page-shell{ max-width: 1200px; margin: 0 auto; }
.card-dark{
    background: linear-gradient(180deg, var(--bg-card), var(--bg-deep));
    border: 1px solid var(--border-soft);
    border-radius: 18px;
}
.card-pad{ padding: 1.25rem; }
.page-title{ font-weight: 900; letter-spacing: -.02em; margin: 0; }
.page-sub{ color: var(--text-muted); }

.input-dark, .select-dark{
    background: rgba(2, 6, 23, .55) !important;
    border: 1px solid rgba(255,255,255,.10) !important;
    color: rgba(255,255,255,.88) !important;
    border-radius: 12px !important;
}
.input-dark::placeholder{ color: rgba(255,255,255,.35); }

.btn-outline-accent{
    background: transparent;
    border: 1px solid rgba(56,189,248,.45);
    color: var(--accent);
    font-weight: 800;
    border-radius: 12px;
    padding: .65rem .9rem;
}
.btn-outline-accent:hover{ background: rgba(56,189,248,.08); color: var(--accent); }

.btn-solid-accent{
    background: rgba(56,189,248,.12);
    border: 1px solid rgba(56,189,248,.35);
    color: rgba(255,255,255,.92);
    font-weight: 800;
    border-radius: 12px;
    padding: .65rem .9rem;
}
.btn-solid-accent:hover{
    background: rgba(56,189,248,.18);
    border-color: rgba(56,189,248,.45);
}

.btn-soft-success{
    background: rgba(34,197,94,.10);
    border: 1px solid rgba(34,197,94,.30);
    color: rgba(34,197,94,.95);
    font-weight: 800;
    border-radius: 12px;
    padding: .55rem .85rem;
}
.btn-soft-success:hover{ background: rgba(34,197,94,.16); }

.pill{
    display:inline-flex;
    align-items:center;
    gap:.35rem;
    padding:.22rem .55rem;
    border-radius:999px;
    font-size:.75rem;
    border: 1px solid rgba(255,255,255,.10);
    color: rgba(255,255,255,.72);
    background: rgba(2,6,23,.30);
}
.pill.accent{ border-color: rgba(56,189,248,.25); color: rgba(56,189,248,.95); }
.pill.success{ border-color: rgba(34,197,94,.25); color: rgba(34,197,94,.95); }
.pill.warn{ border-color: rgba(245,158,11,.25); color: rgba(245,158,11,.95); }

.date-badge{
    display:inline-flex;
    align-items:center;
    gap:.4rem;
    padding:.3rem .65rem;
    border-radius:999px;
    font-size:.8rem;
    color: rgba(255,255,255,.9);
    border: 1px solid rgba(56,189,248,.28);
    background: rgba(56,189,248,.10);
}

.filter-grid{
    display:grid;
    grid-template-columns:minmax(0,1.6fr) minmax(0,1.25fr) auto;
    gap:.75rem;
    align-items:end;
}

.date-filter-form{
    display:flex;
    gap:.65rem;
    align-items:flex-end;
}

.service-card{
    background: linear-gradient(180deg, rgba(2,11,31,.85), rgba(2,6,23,.95));
    border: 1px solid rgba(255,255,255,.08);
    border-radius: 18px;
    overflow: hidden;
    transition: transform .18s ease, border-color .18s ease, box-shadow .18s ease;
    height: 100%;
    cursor: pointer;
}
.service-card:hover{
    transform: translateY(-4px);
    border-color: rgba(56,189,248,.22);
    box-shadow: 0 18px 60px rgba(0,0,0,.55);
}
.service-card.selected{
    border-color: rgba(56,189,248,.55);
    box-shadow: 0 18px 70px rgba(0,0,0,.65);
}

.service-thumb{
    height: 150px;
    background: radial-gradient(1200px 220px at 30% 0%, rgba(56,189,248,.20), transparent 60%),
                linear-gradient(180deg, rgba(2,6,23,.1), rgba(2,6,23,.9));
    position: relative;
}
.service-thumb img{
    width: 100%;
    height: 100%;
    object-fit: cover;
    opacity: .92;
    filter: contrast(1.05) saturate(1.05);
}
.service-thumb .thumb-fallback{
    position:absolute;
    inset:0;
    display:flex;
    align-items:center;
    justify-content:center;
    font-weight: 900;
    letter-spacing: .12em;
    color: rgba(255,255,255,.10);
    font-size: 1.6rem;
    text-transform: uppercase;
}

.service-body{ padding: 1rem 1.1rem 1.1rem; }
.service-name{ font-weight: 900; color: rgba(255,255,255,.95); margin: 0 0 .35rem 0; }
.service-desc{ color: rgba(255,255,255,.60); font-size: .92rem; margin: 0 0 .75rem 0; min-height: 2.4em; }

.provider-card{
    background: rgba(2, 6, 23, .35);
    border: 1px solid rgba(255,255,255,.08);
    border-radius: 16px;
    padding: .95rem;
    transition: transform .15s ease, border-color .15s ease;
}
.provider-card:hover{
    transform: translateY(-2px);
    border-color: rgba(56,189,248,.22);
}
.provider-avatar{
    width: 52px;
    height: 52px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid rgba(255,255,255,.12);
    background: rgba(2,6,23,.8);
}
.provider-name{ font-weight: 900; color: rgba(255,255,255,.92); line-height: 1.1; }
.provider-loc{ color: rgba(255,255,255,.55); font-size: .86rem; }

.panel-title{ font-weight: 900; margin: 0; }
.panel-sub{ color: rgba(255,255,255,.55); font-size: .9rem; }

.empty-hint{
    color: rgba(255,255,255,.55);
    border: 1px dashed rgba(255,255,255,.12);
    border-radius: 16px;
    padding: 1.1rem;
    background: rgba(2,6,23,.25);
}

@media (max-width: 991px){
    .filter-grid{
        grid-template-columns:1fr;
    }
}

@media (max-width: 767px){
    .date-filter-form{
        flex-direction:column;
        align-items:stretch;
    }
}
</style>

<div class="page-shell py-4">
    <div class="d-flex flex-wrap justify-content-between align-items-end gap-2 mb-3">
        <div>
            <h3 class="page-title">Services</h3>
            <div class="page-sub">Select a service to see providers available on {{ $selectedDateLabel }}.</div>
        </div>

        <div class="d-flex gap-2">
            <a class="btn btn-outline-accent" href="{{ route('customer.dashboard') }}">Back to Dashboard</a>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-12 col-lg-8">
            <div class="card-dark card-pad mb-3">
                <div class="filter-grid">
                    <div>
                        <label class="page-sub mb-1">Search services</label>
                        <input
                            id="svcSearch"
                            type="text"
                            class="form-control input-dark"
                            placeholder="e.g. general cleaning, deep cleaning, specific area clean"
                        >
                    </div>

                    <div>
                        <label class="page-sub mb-1">Available date</label>
                        <form method="GET" action="{{ route('customer.services') }}" class="date-filter-form">
                            <input
                                type="date"
                                name="date"
                                class="form-control input-dark"
                                value="{{ $selectedDateString }}"
                                min="{{ $todayDateString }}"
                            >
                            <button class="btn btn-outline-accent" type="submit">Apply Date</button>
                        </form>
                    </div>

                    <div class="d-flex">
                        <button id="clearSelection" class="btn btn-outline-accent w-100" type="button">Clear Selection</button>
                    </div>
                </div>
            </div>

            <div class="row g-3" id="servicesGrid">
                @forelse($services as $s)
                    @php
                        $svcName = $s->name ?? $s->service_name ?? 'Service';
                        $desc = $s->description ?? $s->desc ?? null;

                        $key = strtolower($svcName);
                        $fallbackThumb = 'https://scentral.ca/wp-content/uploads/2020/01/cleaning-services-1210x723-1.jpeg';

                        if (str_contains($key, 'general')) {
                            $fallbackThumb = 'https://scentral.ca/wp-content/uploads/2020/01/cleaning-services-1210x723-1.jpeg';
                        } elseif (str_contains($key, 'deep')) {
                            $fallbackThumb = 'https://thepolishedbubbleco.com/wp-content/uploads/2025/09/deep-cleaning.jpg';
                        } elseif (str_contains($key, 'specific') || str_contains($key, 'area')) {
                            $fallbackThumb = 'https://homemaidbetter.com/wp-content/uploads/2018/07/Deep-Cleaning.jpg';
                        }

                        $img = $s->image ?? $s->service_image ?? null;
                        $thumb = null;

                        if ($img) {
                            $imgPath = ltrim(str_replace('\\', '/', $img), '/');

                            if (filter_var($img, FILTER_VALIDATE_URL)) {
                                $thumb = $img;
                            } elseif (str_starts_with($imgPath, 'storage/')) {
                                $thumb = asset($imgPath);
                            } else {
                                $thumb = asset('storage/' . $imgPath);
                            }
                        }

                        $thumb = $thumb ?: $fallbackThumb;
                        $localFallback = '/images/service-generic.svg';
                        $tag = str_contains($key, 'deep') ? 'Deep' : (str_contains($key, 'general') ? 'General' : 'Area');
                    @endphp

                    <div class="col-12 col-md-6">
                        <div
                            class="service-card"
                            data-service-id="{{ $s->id }}"
                            data-service-name="{{ e($svcName) }}"
                        >
                            <div class="service-thumb">
                                <img
                                    src="{{ $thumb }}"
                                    alt="{{ $svcName }}"
                                    onerror="this.onerror=null;this.src='{{ $localFallback }}';"
                                >
                            </div>

                            <div class="service-body">
                                <div class="d-flex align-items-start justify-content-between gap-2">
                                    <h5 class="service-name">{{ $svcName }}</h5>
                                    <span class="pill accent">SELECT</span>
                                </div>

                                <p class="service-desc">
                                    {{ $desc ?: 'Tap to view available providers for this service.' }}
                                </p>

                                <div class="d-flex flex-wrap gap-2 mb-3">
                                    <span class="pill">{{ $tag }}</span>
                                    <span class="pill warn">Fast booking</span>
                                    <span class="pill success">Verified</span>
                                </div>

                                <button class="btn btn-solid-accent w-100 btn-view-providers" type="button">
                                    View Providers ->
                                </button>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12">
                        <div class="empty-hint">No services found.</div>
                    </div>
                @endforelse
            </div>
        </div>

        <div class="col-12 col-lg-4">
            <div class="card-dark card-pad">
                <div class="d-flex align-items-start justify-content-between gap-2 mb-2">
                    <div>
                        <h5 class="panel-title">Available Providers</h5>
                        <div class="panel-sub" id="panelSub">Select a service to filter providers for {{ $selectedDateLabel }}.</div>
                    </div>
                    <span class="pill accent" id="providerCountPill">0</span>
                </div>

                <div class="mb-3">
                    <span class="date-badge">Date: {{ $selectedDateLabel }}</span>
                </div>

                <div class="mb-2">
                    <input
                        id="provSearch"
                        type="text"
                        class="form-control input-dark"
                        placeholder="Search provider name/location..."
                    >
                </div>

                <div id="providersList" class="d-grid gap-2">
                    <div class="empty-hint">
                        Pick a service on the left to show who is available on {{ $selectedDateLabel }}.
                    </div>
                </div>
            </div>
        </div>
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
        const bookUrl = `{{ url('/customer/book') }}/${provider.id}`;
        const imgUrl = avatarUrl(provider);
        const fallback = fallbackAvatar();

        return `
            <div class="provider-card">
                <div class="d-flex align-items-center gap-3">
                    <img
                        class="provider-avatar"
                        src="${imgUrl}"
                        alt="Provider"
                        onerror="this.onerror=null;this.src='${fallback}';"
                    >
                    <div class="flex-grow-1">
                        <div class="provider-name">${escapeHtml(name)}</div>
                        <div class="provider-loc">${escapeHtml(location)}</div>
                    </div>
                </div>
                <div class="d-flex gap-2 mt-3">
                    <a href="${viewUrl}" class="btn btn-outline-accent w-50" style="padding:.55rem .8rem;">View Profile</a>
                    <a href="${bookUrl}" class="btn btn-soft-success w-50">Book Now</a>
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

    function renderProviders() {
        if (!selectedServiceId) {
            providersList.innerHTML = `<div class="empty-hint">Pick a service on the left to show who is available on ${escapeHtml(selectedDateLabel)}.</div>`;
            providerCountPill.textContent = '0';
            panelSub.textContent = `Select a service to filter providers for ${selectedDateLabel}.`;
            return;
        }

        panelSub.textContent = `Showing providers for ${selectedServiceName} on ${selectedDateLabel}`;

        let list = getProvidersForSelectedService();
        list = applyProviderSearch(list);

        providerCountPill.textContent = String(list.length);

        if (!list.length) {
            providersList.innerHTML = `<div class="empty-hint">No providers are available for ${escapeHtml(selectedServiceName)} on ${escapeHtml(selectedDateLabel)}.</div>`;
            return;
        }

        providersList.innerHTML = list.map(providerCardHTML).join('');
    }

    function setSelectedService(card) {
        servicesGrid.querySelectorAll('.service-card.selected').forEach((element) => element.classList.remove('selected'));

        card.classList.add('selected');
        selectedServiceId = card.getAttribute('data-service-id');
        selectedServiceName = card.getAttribute('data-service-name') || 'Service';

        provSearch.value = '';
        renderProviders();
    }

    servicesGrid?.addEventListener('click', (event) => {
        const card = event.target.closest('.service-card');

        if (!card) {
            return;
        }

        setSelectedService(card);
    });

    svcSearch?.addEventListener('input', () => {
        const term = (svcSearch.value || '').trim().toLowerCase();

        servicesGrid.querySelectorAll('.service-card').forEach((card) => {
            const name = (card.getAttribute('data-service-name') || '').toLowerCase();
            const show = !term || name.includes(term);
            card.parentElement.style.display = show ? '' : 'none';
        });
    });

    provSearch?.addEventListener('input', () => renderProviders());

    clearSelection?.addEventListener('click', () => {
        selectedServiceId = null;
        selectedServiceName = null;
        servicesGrid.querySelectorAll('.service-card.selected').forEach((element) => element.classList.remove('selected'));
        provSearch.value = '';
        renderProviders();
    });

    renderProviders();
})();
</script>

@endsection
