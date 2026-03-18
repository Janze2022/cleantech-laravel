@extends('customer.layouts.app')

@section('title', 'Booking Details')

@section('content')

<link
    rel="stylesheet"
    href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
    integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
    crossorigin=""
>

@php
    $ref = $booking->reference_code ?? $booking->id ?? '—';
    $created = $booking->created_at ? \Carbon\Carbon::parse($booking->created_at)->format('M d, Y h:i A') : '—';

    $status = $booking->status ?? '—';
    $stLower = strtolower((string)$status);

    $isPaid = in_array($stLower, ['paid','completed']);
    $canCancel = in_array($stLower, ['pending', 'accepted', 'confirmed', 'scheduled'], true);
    $cancelLocked = in_array($stLower, ['in_progress', 'ongoing', 'active', 'paid', 'completed'], true);

    $amount = (float)($booking->price ?? 0);

    $serviceName = $booking->service_name ?? 'Service';
    $optionName  = $booking->option_name ?? '—';

    $dateLabel = $booking->booking_date
        ? \Carbon\Carbon::parse($booking->booking_date)->format('M d, Y')
        : '—';

    $preferredStart = $booking->requested_start_time
        ? \Carbon\Carbon::parse($booking->requested_start_time)->format('h:i A')
        : '—';

    $availabilityLabel = ($booking->time_start && $booking->time_end)
        ? \Carbon\Carbon::parse($booking->time_start)->format('h:i A') . ' – ' .
          \Carbon\Carbon::parse($booking->time_end)->format('h:i A')
        : '—';

    $contactPhone = $booking->contact_phone ?? '—';
    $address = $booking->address ?? '—';

    $providerName = trim((string)($booking->provider_name ?? ''));
    if ($providerName === '') $providerName = '—';

    $providerPhone = $booking->provider_phone ?? '—';

    $providerCity = trim((string)($booking->provider_city ?? ''));
    $providerProvince = trim((string)($booking->provider_province ?? ''));

    $providerLocation = trim($providerCity . ($providerProvince ? ', '.$providerProvince : ''));
    if ($providerLocation === '') $providerLocation = '';

    $issued = now()->format('M d, Y h:i A');

    $receiptUrl = route('customer.bookings.show', $ref);
    $shortCode = $ref;
    $customerLatitude = is_numeric($booking->customer_latitude ?? null) ? (float) $booking->customer_latitude : null;
    $customerLongitude = is_numeric($booking->customer_longitude ?? null) ? (float) $booking->customer_longitude : null;
    $customerPinnedAddress = trim((string) ($booking->formatted_address ?? $address ?? ''));
@endphp

<style>
:root{
    --bg-card:#020b1f;
    --bg-deep:#020617;
    --border-soft:rgba(255,255,255,.08);
    --text-muted:rgba(255,255,255,.55);
    --text-strong:rgba(255,255,255,.92);
    --accent:#38bdf8;
    --good:#22c55e;
    --warn:#f59e0b;
    --bad:#ef4444;
}

.wrap{ max-width: 980px; margin: 2rem auto; padding: 0 1rem; }

.cardx{
    background: linear-gradient(180deg, var(--bg-card), var(--bg-deep));
    border: 1px solid var(--border-soft);
    border-radius: 18px;
    padding: 2rem;
}

.header{
    display:flex;
    justify-content:space-between;
    align-items:flex-start;
    gap:1rem;
    flex-wrap:wrap;
}
.title{
    font-weight: 950;
    margin: 0;
    letter-spacing:-.02em;
    color: var(--text-strong);
}
.muted{ color: var(--text-muted); }
.ref{ color: var(--accent); font-weight: 950; }

.summary-right{
    text-align:right;
    min-width: 220px;
}
.summary-right .total{
    color: var(--accent);
    font-weight: 950;
    font-size: 1.15rem;
}

.pill{
    display:inline-flex;
    align-items:center;
    padding:.35rem .75rem;
    border-radius:999px;
    font-size:.75rem;
    font-weight:950;
    letter-spacing:.10em;
    text-transform:uppercase;
    border:1px solid rgba(255,255,255,.12);
    background: rgba(2,6,23,.25);
}
.pill.paid{ color: var(--good); border-color: rgba(34,197,94,.35); }
.pill.unpaid{ color: var(--warn); border-color: rgba(245,158,11,.35); }
.pill.bad{ color: var(--bad); border-color: rgba(239,68,68,.35); }

.grid{
    display:grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
    margin-top: 1.25rem;
}
@media (max-width: 768px){
    .grid{ grid-template-columns: 1fr; }
}

.item{
    background: rgba(2,6,23,.35);
    border: 1px solid rgba(255,255,255,.06);
    border-radius: 14px;
    padding: 1rem;
}
.item .label{
    font-size: .75rem;
    text-transform: uppercase;
    letter-spacing: .08em;
    color: var(--text-muted);
}
.item .value{
    margin-top: .35rem;
    font-weight: 950;
    color: rgba(255,255,255,.92);
    word-break: break-word;
}
.item .sub{
    margin-top: .25rem;
    color: rgba(255,255,255,.55);
    font-weight: 800;
    font-size: .88rem;
    word-break: break-word;
}

.actions{
    margin-top: 1.25rem;
    display:flex;
    gap:.75rem;
    flex-wrap:wrap;
}

.notice{
    margin-bottom:1rem;
    padding:.9rem 1rem;
    border-radius:14px;
    border:1px solid rgba(255,255,255,.08);
    font-weight:800;
}
.notice.success{
    border-color:rgba(34,197,94,.22);
    background:rgba(34,197,94,.10);
    color:#bbf7d0;
}
.notice.error{
    border-color:rgba(239,68,68,.22);
    background:rgba(239,68,68,.10);
    color:#fecaca;
}

.action-note{
    margin-top:.35rem;
    color:var(--text-muted);
    font-size:.86rem;
    line-height:1.5;
}

.tracking-card{
    margin-top:1.25rem;
    padding:1rem;
    border-radius:16px;
    border:1px solid rgba(255,255,255,.08);
    background:rgba(2,6,23,.35);
}

.tracking-head{
    display:flex;
    justify-content:space-between;
    gap:.75rem;
    align-items:flex-start;
    flex-wrap:wrap;
}

.tracking-head h4{
    margin:0;
    color:var(--text-strong);
    font-weight:950;
}

.tracking-copy{
    color:var(--text-muted);
    font-size:.9rem;
    line-height:1.5;
}

.tracking-status{
    min-height:1.2rem;
    color:var(--text-muted);
    font-size:.84rem;
    font-weight:800;
}

.tracking-status.error{
    color:#fca5a5;
}

.tracking-map{
    width:100%;
    height:320px;
    margin-top:1rem;
    border-radius:16px;
    overflow:hidden;
    border:1px solid rgba(255,255,255,.08);
}

.tracking-grid{
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:.85rem;
    margin-top:1rem;
}

.tracking-box{
    border:1px solid rgba(255,255,255,.08);
    background:rgba(2,6,23,.32);
    border-radius:14px;
    padding:.9rem;
}

.tracking-box .label{
    font-size:.72rem;
}

.tracking-box .value{
    font-size:.98rem;
    line-height:1.45;
}

.tracking-box .sub{
    margin-top:.35rem;
    color:var(--text-muted);
    font-size:.84rem;
    font-weight:800;
    line-height:1.4;
}

.btnx{
    padding:.75rem 1rem;
    border-radius:12px;
    font-weight:950;
    text-decoration:none;
    border: 1px solid rgba(255,255,255,.14);
    background: transparent;
    color:#fff;
    cursor:pointer;
}
.btnx.primary{
    border:none;
    background: linear-gradient(180deg,#0ea5e9,#38bdf8);
}
.btnx.danger{
    border-color:rgba(239,68,68,.24);
    background:rgba(239,68,68,.10);
    color:#fecaca;
}
.btnx:hover{ filter: brightness(1.05); }

.preview-wrap{ margin-top: 1rem; display:none; }
.preview-wrap.show{ display:block; }

.preview-shell{
    margin-top: 1rem;
    border: 1px solid rgba(255,255,255,.10);
    border-radius: 18px;
    background: rgba(2,6,23,.35);
    padding: 1rem;
}
.preview-title{
    display:flex;
    justify-content:space-between;
    gap:.75rem;
    flex-wrap:wrap;
    align-items:center;
    margin-bottom: .75rem;
}
.preview-title .t{ font-weight: 950; color: var(--text-strong); }
.preview-title .hint{ color: var(--text-muted); font-size: .88rem; }

.receipt-scroll{
    overflow-x:auto;
    -webkit-overflow-scrolling: touch;
    padding-bottom: .25rem;
}
.receipt-paper{
    width: 360px;
    max-width: 360px;
    margin: 0 auto;
    background: #ffffff;
    color: #0f172a;
    border-radius: 14px;
    border: 1px solid #e5e7eb;
    padding: 14px 14px;
    font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
}

.r-center{ text-align:center; }
.r-brand{ display:flex; align-items:center; justify-content:center; gap:8px; }
.r-name{ font-weight: 900; font-size: 14px; margin:0; }
.r-sub{ font-size: 10px; margin: 2px 0 0; color:#475569; }
.r-badge{
    display:inline-flex;
    margin-top: 6px;
    padding: 3px 8px;
    border-radius: 999px;
    font-size: 10px;
    font-weight: 900;
    letter-spacing: .12em;
    text-transform: uppercase;
    border: 1px solid #cbd5e1;
}
.r-badge.paid{ border-color: rgba(34,197,94,.55); color:#16a34a; }
.r-badge.unpaid{ border-color: rgba(245,158,11,.55); color:#b45309; }
.r-hr{ border-top: 1px dashed #cbd5e1; margin: 10px 0; }
.r-row{
    display:flex; justify-content:space-between; gap:10px;
    font-size: 11px; line-height: 1.35; margin: 4px 0;
}
.r-row .k{ color:#334155; }
.r-row .v{ font-weight: 800; text-align:right; }
.r-block{ margin-top: 8px; font-size: 11px; line-height:1.35; }
.r-block .k{ color:#334155; }
.r-block .v{ margin-top: 2px; font-weight: 800; word-break: break-word; }
.r-total{
    display:flex; justify-content:space-between; align-items:flex-end; margin-top: 8px;
}
.r-total .k{ font-size: 11px; color:#334155; font-weight: 800; }
.r-total .v{ font-size: 16px; font-weight: 900; }
.r-qr{ margin-top: 10px; text-align:center; }
.r-qr svg{ width: 38mm !important; height: 38mm !important; display:block; margin:0 auto; }
.r-code{ margin-top: 4px; font-size: 10px; color:#64748b; word-break: break-word; }

#printArea{ display:none; }

@media (max-width: 576px){
    .wrap{ margin: 1rem auto; }
    .cardx{ padding: 1.1rem; border-radius: 16px; }

    .summary-right{
        width: 100%;
        text-align:left;
        min-width: 0;
        margin-top:.5rem;
        padding-top:.75rem;
        border-top: 1px solid rgba(255,255,255,.08);
    }

    .actions{ gap:.6rem; }
    .btnx{ width: 100%; text-align:center; }
    .preview-shell{ padding: .85rem; }
    .tracking-map{ height:280px; }
    .tracking-grid{ grid-template-columns:1fr; }
}

@media print{
    body *{ visibility:hidden !important; }
    #printArea, #printArea *{ visibility:visible !important; }

    html, body{
        background:#fff !important;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }

    @page{ margin: 0mm; size: auto; }

    #printArea{
        display:block !important;
        position: fixed !important;
        left:0; top:0;
        width:100%;
        background:#fff !important;
        padding: 0 !important;
    }

    .receipt-paper{
        width: 80mm !important;
        max-width: 80mm !important;
        margin: 0 auto !important;
        border: none !important;
        border-radius: 0 !important;
        padding: 6mm 5mm !important;
        box-shadow: none !important;
        page-break-inside: avoid !important;
        break-inside: avoid !important;
    }

    .r-code{ font-size: 9px !important; word-break: break-word !important; }
    .r-qr svg{ width: 36mm !important; height: 36mm !important; }
}
</style>

<div class="wrap">
    <div class="cardx">

        @if(session('success'))
            <div class="notice success">{{ session('success') }}</div>
        @endif

        @if($errors->has('general'))
            <div class="notice error">{{ $errors->first('general') }}</div>
        @endif

        <div class="header">
            <div>
                <h3 class="title">Booking Details</h3>
                <div class="muted">Reference: <span class="ref">{{ $ref }}</span></div>
                <div class="muted">Created: {{ $created }}</div>
            </div>

            <div class="summary-right">
                <div class="muted">Status</div>
                @php
                    $pillClass = $isPaid ? 'paid' : (in_array($stLower, ['cancelled','canceled']) ? 'bad' : 'unpaid');
                @endphp
                <div class="pill {{ $pillClass }}">{{ strtoupper(str_replace('_',' ',$stLower ?: 'N/A')) }}</div>

                <div class="muted" style="margin-top:10px;">Total</div>
                <div class="total">₱{{ number_format($amount,2) }}</div>
            </div>
        </div>

        <div class="grid">
            <div class="item">
                <div class="label">Service</div>
                <div class="value">{{ $serviceName }}</div>
            </div>

            <div class="item">
                <div class="label">Option</div>
                <div class="value">{{ $optionName }}</div>
            </div>

            <div class="item">
                <div class="label">Date</div>
                <div class="value">{{ $dateLabel }}</div>
            </div>

            <div class="item">
                <div class="label">Preferred Start</div>
                <div class="value">{{ $preferredStart }}</div>
            </div>

            <div class="item">
                <div class="label">Availability</div>
                <div class="value">{{ $availabilityLabel }}</div>
            </div>

            <div class="item">
                <div class="label">Customer Contact</div>
                <div class="value">{{ $contactPhone }}</div>
            </div>

            <div class="item">
                <div class="label">Provider</div>
                <div class="value">{{ $providerName }}</div>
                @if($providerLocation)
                    <div class="sub">{{ $providerLocation }}</div>
                @endif
            </div>

            <div class="item">
                <div class="label">Provider Phone</div>
                <div class="value">{{ $providerPhone }}</div>
            </div>

            <div class="item" style="grid-column: 1 / -1;">
                <div class="label">Service Address</div>
                <div class="value">{{ $address }}</div>
            </div>
        </div>

        <div class="tracking-card">
            <div class="tracking-head">
                <div>
                    <h4>Live Provider Tracking</h4>
                    <div class="tracking-copy">
                        Follow the provider's current location, compare it with your pinned service address,
                        and watch the route refresh automatically.
                    </div>
                </div>
                <div id="customerTrackingStatus" class="tracking-status">
                    Loading latest provider location...
                </div>
            </div>

            <div id="customerTrackingMap" class="tracking-map"></div>

            <div class="tracking-grid">
                <div class="tracking-box">
                    <div class="label">Your pinned location</div>
                    <div class="value" id="customerPinnedAddressText">
                        {{ $customerPinnedAddress !== '' ? $customerPinnedAddress : 'This booking does not have a saved map pin yet.' }}
                    </div>
                    <div class="sub" id="customerPinnedCoordsText">
                        @if($customerLatitude !== null && $customerLongitude !== null)
                            {{ number_format($customerLatitude, 6) }}, {{ number_format($customerLongitude, 6) }}
                        @else
                            No saved customer coordinates yet.
                        @endif
                    </div>
                </div>

                <div class="tracking-box">
                    <div class="label">Provider current location</div>
                    <div class="value" id="providerLiveAddressText">Waiting for the provider to start live tracking.</div>
                    <div class="sub" id="providerLiveMetaText">No live provider coordinates yet.</div>
                </div>
            </div>
        </div>

        <div class="actions">
            <a href="{{ route('customer.bookings') }}" class="btnx primary">Back</a>

            @if($canCancel)
                <form method="POST"
                      action="{{ route('customer.bookings.cancel', $ref) }}"
                      onsubmit="return confirm('Cancel this booking?');">
                    @csrf
                    <button type="submit" class="btnx danger">Cancel Booking</button>
                </form>
            @endif

            <button type="button" class="btnx" onclick="toggleReceiptPreview()">
                Preview Receipt
            </button>

            <button type="button" class="btnx" onclick="printReceipt()">
                Print Receipt
            </button>
        </div>

        @if($canCancel)
            <div class="action-note">You can still cancel this booking while it is confirmed and waiting to start.</div>
        @elseif($cancelLocked)
            <div class="action-note">Customer cancellation is locked once the provider has already started the booking.</div>
        @endif

        <div id="receiptPreview" class="preview-wrap">
            <div class="preview-shell">
                <div class="preview-title">
                    <div class="t">Receipt Preview</div>
                    <div class="hint">This is exactly what will be printed.</div>
                </div>

                <div class="receipt-scroll">
                    <div class="receipt-paper">
                        <div class="r-center">
                            <div class="r-brand">
                                <svg width="20" height="20" viewBox="0 0 64 64" aria-label="CleanTech logo">
                                    <path d="M32 4C24 16 14 26 14 38c0 10 8 18 18 18s18-8 18-18C50 26 40 16 32 4z" fill="#0ea5e9"/>
                                </svg>
                                <h1 class="r-name">CleanTech</h1>
                            </div>
                            <div class="r-sub">Booking Receipt</div>

                            <span class="r-badge {{ $isPaid ? 'paid' : 'unpaid' }}">
                                {{ $isPaid ? 'PAID' : strtoupper(str_replace('_',' ',$stLower)) }}
                            </span>
                        </div>

                        <div class="r-hr"></div>

                        <div class="r-row"><div class="k">Receipt Ref</div><div class="v">{{ $ref }}</div></div>
                        <div class="r-row"><div class="k">Issued</div><div class="v">{{ $issued }}</div></div>

                        <div class="r-hr"></div>

                        <div class="r-block">
                            <div class="k">Service</div>
                            <div class="v">{{ $serviceName }}</div>
                        </div>

                        <div class="r-block">
                            <div class="k">Option</div>
                            <div class="v">{{ $optionName }}</div>
                        </div>

                        <div class="r-block">
                            <div class="k">Date</div>
                            <div class="v">{{ $dateLabel }}</div>
                        </div>

                        <div class="r-block">
                            <div class="k">Preferred Start</div>
                            <div class="v">{{ $preferredStart }}</div>
                        </div>

                        <div class="r-block">
                            <div class="k">Availability</div>
                            <div class="v">{{ $availabilityLabel }}</div>
                        </div>

                        <div class="r-hr"></div>

                        <div class="r-block">
                            <div class="k">Provider</div>
                            <div class="v">{{ $providerName }}</div>
                            @if($providerLocation)
                                <div class="r-sub" style="margin-top:4px;">{{ $providerLocation }}</div>
                            @endif
                        </div>

                        <div class="r-block">
                            <div class="k">Provider Phone</div>
                            <div class="v">{{ $providerPhone }}</div>
                        </div>

                        <div class="r-block">
                            <div class="k">Service Address</div>
                            <div class="v">{{ $address }}</div>
                        </div>

                        <div class="r-hr"></div>

                        <div class="r-total">
                            <div class="k">TOTAL</div>
                            <div class="v">₱{{ number_format($amount,2) }}</div>
                        </div>

                        <div class="r-hr"></div>

                        <div class="r-qr">
                            {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::format('svg')->size(150)->margin(1)->generate($receiptUrl) !!}
                            <div class="r-code">Code: {{ $shortCode }}</div>
                        </div>

                        <div class="r-hr"></div>

                        <div style="font-size:10px;color:#64748b;line-height:1.35;">
                            Notes:<br>
                            - Present this receipt if requested.<br>
                            - For changes/cancellations, refer to app policies.<br>
                            Thank you for choosing CleanTech.
                        </div>

                        <div class="r-center" style="margin-top:10px; font-size:10px; color:#64748b;">
                            — END —
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<div id="printArea" aria-hidden="true">
    <div id="printMount"></div>
</div>

<script>
function toggleReceiptPreview(){
    const el = document.getElementById('receiptPreview');
    el.classList.toggle('show');
}

function printReceipt(){
    const preview = document.querySelector('#receiptPreview .receipt-paper');
    const mount = document.getElementById('printMount');

    if (!preview || !mount) {
        window.print();
        return;
    }

    mount.innerHTML = '';
    mount.appendChild(preview.cloneNode(true));
    window.print();
}
</script>

<script
    src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
    integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
    crossorigin=""
></script>
<script>
(() => {
    const mapEl = document.getElementById('customerTrackingMap');

    if (!mapEl || !window.L) {
        return;
    }

    const trackingConfig = {
        pollUrl: @json(route('customer.bookings.tracking', $ref)),
        customer: {
            latitude: @json($customerLatitude),
            longitude: @json($customerLongitude),
            address: @json($customerPinnedAddress),
        },
    };

    const statusEl = document.getElementById('customerTrackingStatus');
    const customerAddressEl = document.getElementById('customerPinnedAddressText');
    const customerCoordsEl = document.getElementById('customerPinnedCoordsText');
    const providerAddressEl = document.getElementById('providerLiveAddressText');
    const providerMetaEl = document.getElementById('providerLiveMetaText');

    const defaultCenter = [8.9475, 125.5436];

    let map = null;
    let customerMarker = null;
    let providerMarker = null;
    let routeLine = null;
    let pollTimer = null;

    function setStatus(message, isError = false) {
        if (!statusEl) {
            return;
        }

        statusEl.textContent = message;
        statusEl.classList.toggle('error', isError);
    }

    function formatCoords(latitude, longitude) {
        if (latitude === null || longitude === null || latitude === '' || longitude === '') {
            return null;
        }

        return `${Number(latitude).toFixed(6)}, ${Number(longitude).toFixed(6)}`;
    }

    function ensureMarker(existingMarker, latitude, longitude, options) {
        if (latitude === null || longitude === null || latitude === '' || longitude === '') {
            return existingMarker;
        }

        if (!existingMarker) {
            return L.circleMarker([latitude, longitude], options).addTo(map);
        }

        existingMarker.setLatLng([latitude, longitude]);
        return existingMarker;
    }

    // Geoapify returns GeoJSON coordinates in longitude,latitude order.
    function geometryToLatLngs(route) {
        const geometry = route?.geometry;

        if (!geometry || !Array.isArray(geometry.coordinates)) {
            return [];
        }

        if (geometry.type === 'LineString') {
            return geometry.coordinates
                .filter((pair) => Array.isArray(pair) && pair.length >= 2)
                .map((pair) => [pair[1], pair[0]]);
        }

        if (geometry.type === 'MultiLineString') {
            return geometry.coordinates
                .flat()
                .filter((pair) => Array.isArray(pair) && pair.length >= 2)
                .map((pair) => [pair[1], pair[0]]);
        }

        return [];
    }

    function updateCustomerCard() {
        if (customerAddressEl) {
            customerAddressEl.textContent = trackingConfig.customer.address || 'This booking does not have a saved map pin yet.';
        }

        if (customerCoordsEl) {
            customerCoordsEl.textContent = formatCoords(
                trackingConfig.customer.latitude,
                trackingConfig.customer.longitude
            ) || 'No saved customer coordinates yet.';
        }
    }

    function updateProviderCard(location) {
        if (!location) {
            providerAddressEl.textContent = 'Waiting for the provider to start live tracking.';
            providerMetaEl.textContent = 'No live provider coordinates yet.';
            return;
        }

        const coords = formatCoords(location.latitude, location.longitude);
        const trackedAt = location.tracked_at ? `Updated ${location.tracked_at}` : 'Latest provider pin available.';

        providerAddressEl.textContent = location.formatted_address || 'Provider location shared without a readable address.';
        providerMetaEl.textContent = coords ? `${coords} | ${trackedAt}` : trackedAt;
    }

    function fitToVisibleData(routeLatLngs = []) {
        const boundsPoints = [];

        if (customerMarker) {
            boundsPoints.push(customerMarker.getLatLng());
        }

        if (providerMarker) {
            boundsPoints.push(providerMarker.getLatLng());
        }

        routeLatLngs.forEach((point) => boundsPoints.push(L.latLng(point[0], point[1])));

        if (boundsPoints.length >= 2) {
            map.fitBounds(L.latLngBounds(boundsPoints), {
                padding: [32, 32],
                maxZoom: 16,
            });
            return;
        }

        if (boundsPoints.length === 1) {
            map.setView(boundsPoints[0], 15);
            return;
        }

        map.setView(defaultCenter, 13);
    }

    function updateRouteLine(route) {
        const latLngs = geometryToLatLngs(route);

        if (!latLngs.length) {
            if (routeLine) {
                map.removeLayer(routeLine);
                routeLine = null;
            }

            fitToVisibleData();
            return;
        }

        if (!routeLine) {
            routeLine = L.polyline(latLngs, {
                color: '#38bdf8',
                weight: 4,
                opacity: 0.75,
            }).addTo(map);
        } else {
            routeLine.setLatLngs(latLngs);
        }

        fitToVisibleData(latLngs);
    }

    // Poll the latest provider location instead of relying on websockets.
    async function refreshTracking() {
        try {
            const response = await fetch(trackingConfig.pollUrl, {
                headers: {
                    Accept: 'application/json',
                },
            });

            const payload = await response.json();

            if (!response.ok) {
                throw new Error(payload.message || 'Unable to refresh tracking right now.');
            }

            const booking = payload.booking || {};
            const providerLocation = payload.provider_location;

            trackingConfig.customer.latitude = booking.customer_latitude ?? trackingConfig.customer.latitude;
            trackingConfig.customer.longitude = booking.customer_longitude ?? trackingConfig.customer.longitude;
            trackingConfig.customer.address = booking.formatted_address || booking.address || trackingConfig.customer.address;

            customerMarker = ensureMarker(
                customerMarker,
                trackingConfig.customer.latitude,
                trackingConfig.customer.longitude,
                {
                    radius: 9,
                    color: '#22c55e',
                    fillColor: '#22c55e',
                    fillOpacity: 0.9,
                    weight: 2,
                }
            );

            if (providerLocation?.latitude !== null && providerLocation?.longitude !== null) {
                providerMarker = ensureMarker(
                    providerMarker,
                    Number(providerLocation.latitude),
                    Number(providerLocation.longitude),
                    {
                        radius: 9,
                        color: '#38bdf8',
                        fillColor: '#38bdf8',
                        fillOpacity: 0.9,
                        weight: 2,
                    }
                );
            } else if (providerMarker) {
                map.removeLayer(providerMarker);
                providerMarker = null;
            }

            updateCustomerCard();
            updateProviderCard(providerLocation);
            updateRouteLine(payload.route || null);

            if (!providerLocation) {
                setStatus('Waiting for the provider to start live tracking.');
                return;
            }

            if (providerLocation.is_tracking) {
                setStatus('Provider location updated live.');
                return;
            }

            setStatus('Showing the provider\'s last shared location.');
        } catch (error) {
            setStatus(error.message || 'Unable to refresh tracking right now.', true);
        }
    }

    map = L.map(mapEl, {
        zoomControl: true,
    }).setView(defaultCenter, 13);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; OpenStreetMap contributors',
    }).addTo(map);

    updateCustomerCard();

    if (trackingConfig.customer.latitude !== null && trackingConfig.customer.longitude !== null) {
        customerMarker = ensureMarker(customerMarker, trackingConfig.customer.latitude, trackingConfig.customer.longitude, {
            radius: 9,
            color: '#22c55e',
            fillColor: '#22c55e',
            fillOpacity: 0.9,
            weight: 2,
        });
    }

    fitToVisibleData();
    refreshTracking();

    pollTimer = window.setInterval(refreshTracking, 8000);

    document.addEventListener('visibilitychange', () => {
        if (document.hidden) {
            if (pollTimer) {
                window.clearInterval(pollTimer);
                pollTimer = null;
            }
            return;
        }

        refreshTracking();

        if (!pollTimer) {
            pollTimer = window.setInterval(refreshTracking, 8000);
        }
    });
})();
</script>

@endsection
