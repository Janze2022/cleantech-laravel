@extends('customer.layouts.app')

@section('title', 'Booking Details')

@section('content')

@php
    $ref = $booking->reference_code ?? $booking->id ?? '—';
    $created = $booking->created_at ? \Carbon\Carbon::parse($booking->created_at)->format('M d, Y h:i A') : '—';

    $status = $booking->status ?? '—';
    $stLower = strtolower((string)$status);

    $isPaid = in_array($stLower, ['paid','completed']);

    $amount = (float)($booking->price ?? 0);

    $serviceName = $booking->service_name ?? 'Service';
    $optionName  = $booking->option_name ?? '—';

    $dateLabel = $booking->booking_date
        ? \Carbon\Carbon::parse($booking->booking_date)->format('M d, Y')
        : '—';

    $preferredStart = $booking->requested_start_time
        ? \Carbon\Carbon::createFromFormat('H:i:s', $booking->requested_start_time)->format('h:i A')
        : '—';

    $availabilityLabel = ($booking->time_start && $booking->time_end)
        ? \Carbon\Carbon::createFromFormat('H:i:s', $booking->time_start)->format('h:i A') . ' – ' .
          \Carbon\Carbon::createFromFormat('H:i:s', $booking->time_end)->format('h:i A')
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

    $receiptUrl = "http://172.20.10.11:8000/customer/bookings/".$ref;
    $shortCode = $ref;
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

        <div class="actions">
            <a href="{{ route('customer.bookings') }}" class="btnx primary">Back</a>

            <button type="button" class="btnx" onclick="toggleReceiptPreview()">
                Preview Receipt
            </button>

            <button type="button" class="btnx" onclick="printReceipt()">
                Print Receipt
            </button>
        </div>

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

@endsection