@extends('provider.layouts.app')

@section('title', 'Booking Details')

@section('content')

<link
    rel="stylesheet"
    href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
    integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
    crossorigin=""
>

@php
    use Illuminate\Support\Facades\DB;

    // =========================
    // BOOKING DATA (from controller)
    // =========================
    $ref = $booking->reference_code ?? $booking->id ?? '—';
    $created = !empty($booking->created_at)
        ? \Carbon\Carbon::parse($booking->created_at)->format('M d, Y h:i A')
        : '—';

    $status = $booking->status ?? '—';
    $stLower = strtolower((string)$status);

    $amount = (float)($booking->price ?? 0);

    $serviceName = $booking->service_name ?? '—';
    $optionName  = $booking->option_label ?? '—';

    $dateLabel = !empty($booking->booking_date)
        ? \Carbon\Carbon::parse($booking->booking_date)->format('M d, Y')
        : '—';

    $timeLabel = (!empty($booking->time_start) && !empty($booking->time_end))
        ? ($booking->time_start.' – '.$booking->time_end)
        : '—';

    $customerName  = $booking->customer_name ?? 'Customer';
    $customerPhone = $booking->customer_phone ?? ($booking->contact_phone ?? '—');
    $customerEmail = $booking->customer_email ?? '';

    $address = $booking->address ?? '—';

    // =========================
    // PROVIDER ID (your app uses session provider_id)
    // =========================
    $providerId = (int) session('provider_id');

    // =========================
    // PROVIDER INFO (not joined in controller, so load here)
    // =========================
    $provider = null;
    if ($providerId) {
        $provider = DB::table('service_providers')
            ->where('id', $providerId)
            ->select('first_name','last_name','phone','city','province')
            ->first();
    }

    $providerName = $provider
        ? trim(($provider->first_name ?? '').' '.($provider->last_name ?? ''))
        : '—';

    if ($providerName === '') $providerName = '—';

    $providerPhone = $provider->phone ?? '—';
    $providerCity = trim((string)($provider->city ?? ''));
    $providerProvince = trim((string)($provider->province ?? ''));
    $providerLocation = trim($providerCity . ($providerProvince ? ', '.$providerProvince : ''));

    // Keep the ratings aggregates available for compatibility,
    // even though the snapshot panel is not shown here anymore.
    $ratingSummary = (object)['avg' => 0, 'count' => 0];
    $breakdown = collect([
        (object)['star'=>5,'cnt'=>0],
        (object)['star'=>4,'cnt'=>0],
        (object)['star'=>3,'cnt'=>0],
        (object)['star'=>2,'cnt'=>0],
        (object)['star'=>1,'cnt'=>0],
    ]);

    if ($providerId) {
        $ratingSummary = DB::table('reviews')
            ->where('provider_id', $providerId)
            ->selectRaw('AVG(rating) as avg, COUNT(*) as count')
            ->first() ?? $ratingSummary;

        $count = (int)($ratingSummary->count ?? 0);

        $rows = DB::table('reviews')
            ->where('provider_id', $providerId)
            ->whereNotNull('rating')
            ->selectRaw('rating as star, COUNT(*) as cnt')
            ->groupBy('rating')
            ->get()
            ->keyBy('star');

        $breakdown = collect([5,4,3,2,1])->map(function($s) use ($rows){
            $r = $rows->get($s);
            return (object)[
                'star' => $s,
                'cnt'  => (int)($r->cnt ?? 0),
            ];
        });
    }

    $avg   = (float)($ratingSummary->avg ?? 0);
    $count = (int)($ratingSummary->count ?? 0);
    $fmtAvg = $count > 0 ? number_format($avg, 1) : '0.0';

    $percent = function($n) use ($count){
        if ($count <= 0) return 0;
        $n = (int)$n;
        return (int) round(($n / $count) * 100);
    };

    $stars = function($n){
        $n = max(0, min(5, (int)$n));
        $out = '';
        for($i=1;$i<=5;$i++){
            $out .= $i <= $n ? '★' : '☆';
        }
        return $out;
    };

    $badgeClass = 'warn';
    if (in_array($stLower, ['completed','paid'])) $badgeClass = 'good';
    if (in_array($stLower, ['cancelled','canceled'])) $badgeClass = 'bad';

    $customerLatitude = is_numeric($booking->customer_latitude ?? null) ? (float) $booking->customer_latitude : null;
    $customerLongitude = is_numeric($booking->customer_longitude ?? null) ? (float) $booking->customer_longitude : null;
    $customerPinnedAddress = trim((string) ($booking->formatted_address ?? $address ?? ''));

    $providerLocationRow = $booking->provider_location ?? null;
    $providerLatitude = is_numeric($providerLocationRow->latitude ?? null) ? (float) $providerLocationRow->latitude : null;
    $providerLongitude = is_numeric($providerLocationRow->longitude ?? null) ? (float) $providerLocationRow->longitude : null;
    $providerTrackedAddress = trim((string) ($providerLocationRow->formatted_address ?? ''));

    $cancellationReason = trim((string) ($booking->cancellation_reason ?? ''));
    $cancelledByRole = trim((string) ($booking->cancelled_by_role ?? ''));
    $cancelledByLabel = $cancelledByRole !== '' ? ucfirst(str_replace('_', ' ', $cancelledByRole)) : 'System';
    $bookingAdjustmentStatus = trim((string) ($booking->adjustment_status ?? ''));
    $canReportMismatch = $stLower === 'in_progress' && (($adjustment->status_key ?? '') !== 'adjustment_accepted');
    $serviceOptionsById = collect($serviceOptions ?? [])
        ->keyBy(fn ($option) => (int) ($option->id ?? 0));
    $originalOptionIds = collect($currentOptionIds ?? [])
        ->map(fn ($value) => (int) $value)
        ->filter(fn ($value) => $value > 0 && $serviceOptionsById->has($value))
        ->values()
        ->all();
    $defaultCorrectedOptionIds = collect($requestedOptionIds ?? [])
        ->map(fn ($value) => (int) $value)
        ->filter(fn ($value) => $value > 0 && $serviceOptionsById->has($value))
        ->values()
        ->all();

    if (empty($defaultCorrectedOptionIds)) {
        $defaultCorrectedOptionIds = $originalOptionIds;
    }

    $selectedReasonCodes = collect(old('reason_codes', $adjustment->reason_codes ?? []))
        ->map(fn ($value) => trim((string) $value))
        ->filter()
        ->values()
        ->all();
    $selectedCorrectedOptionIds = $isSpecificAreaBooking
        ? collect(old('corrected_option_ids', $defaultCorrectedOptionIds))
            ->map(fn ($value) => (int) $value)
            ->filter(fn ($value) => $value > 0 && $serviceOptionsById->has($value))
            ->unique()
            ->values()
            ->all()
        : collect([(int) old('corrected_option_id', $defaultCorrectedOptionIds[0] ?? 0)])
            ->filter(fn ($value) => $value > 0 && $serviceOptionsById->has($value))
            ->values()
            ->all();

    $sumOptionPrices = function (array $optionIds) use ($serviceOptionsById) {
        return round(collect($optionIds)->sum(fn ($id) => (float) ($serviceOptionsById->get((int) $id)->price_addition ?? 0)), 2);
    };

    $formatOptionLabels = function (array $optionIds) use ($serviceOptionsById) {
        return collect($optionIds)
            ->map(fn ($id) => trim((string) ($serviceOptionsById->get((int) $id)->label ?? '')))
            ->filter()
            ->implode(', ');
    };

    $originalSelectionLabel = $originalOptionSummary ?: trim((string) ($optionName ?: $serviceName));
    $selectedCorrectedLabel = $formatOptionLabels($selectedCorrectedOptionIds) ?: $originalSelectionLabel;
    $originalPrice = round((float) $amount, 2);
    $originalOptionTotal = $sumOptionPrices($originalOptionIds);
    $selectedCorrectedOptionTotal = $sumOptionPrices($selectedCorrectedOptionIds);
    $serviceBasePrice = round($originalPrice - $originalOptionTotal, 2);
    if ($serviceBasePrice < 0) {
        $serviceBasePrice = 0.0;
    }
    $automaticReasonFee = in_array('heavy_soiling', $selectedReasonCodes, true)
        ? round(max(300, $originalPrice * 0.10), 2)
        : 0.0;
    $previewProposedTotal = round($serviceBasePrice + $selectedCorrectedOptionTotal + $automaticReasonFee, 2);
    $previewAdditionalFee = round($previewProposedTotal - $originalPrice, 2);
    $adjustmentMaxIncreasePercent = 35.0;
    $previewIncreasePercent = $originalPrice > 0
        ? round((($previewProposedTotal - $originalPrice) / $originalPrice) * 100, 2)
        : 0.0;
    $otherReasonValue = old('other_reason', $adjustment->other_reason ?? '');
    $providerNoteValue = old('provider_note', $adjustment->provider_note ?? '');
    $showOtherReason = in_array('other', $selectedReasonCodes, true);
@endphp

<style>
:root{
    --bg:#020617;
    --card:#0b1220;
    --card2:#0f172a;
    --border:rgba(255,255,255,.08);
    --text:rgba(255,255,255,.92);
    --muted:rgba(255,255,255,.55);
    --accent:#38bdf8;
    --good:#22c55e;
    --warn:#facc15;
    --bad:#ef4444;
    --r:18px;
    --shadow:0 28px 70px rgba(0,0,0,.55);
}

.page{ padding-top: 10px; padding-bottom: 20px; }

.shell{
    background:
        radial-gradient(900px 320px at 20% 0%, rgba(56,189,248,.10), transparent 62%),
        radial-gradient(900px 320px at 85% 10%, rgba(34,197,94,.08), transparent 58%),
        linear-gradient(180deg, rgba(2,11,31,.92), rgba(2,6,23,.96));
    border: 1px solid var(--border);
    border-radius: 26px;
    box-shadow: var(--shadow);
    overflow:hidden;
}

.top{
    position: sticky;
    top: 0;
    z-index: 20;
    padding: 14px 14px;
    border-bottom: 1px solid rgba(255,255,255,.06);
    background: rgba(2,6,23,.78);
    backdrop-filter: blur(10px);
    display:flex;
    align-items:flex-start;
    justify-content:space-between;
    gap: 12px;
    flex-wrap:wrap;
}

.h1{
    margin:0;
    color: rgba(255,255,255,.96);
    font-weight: 950;
    letter-spacing:.01em;
    font-size: 1.15rem;
}
.sub{
    margin:.25rem 0 0;
    color: var(--muted);
    font-weight: 700;
    font-size: .86rem;
}
.ref{ color: var(--accent); font-weight: 950; }

.pill{
    display:inline-flex;
    align-items:center;
    gap:.6rem;
    padding:.55rem .85rem;
    border-radius: 999px;
    border:1px solid rgba(255,255,255,.10);
    background: rgba(255,255,255,.04);
    color: rgba(255,255,255,.92);
    font-weight: 900;
    font-size: .82rem;
    min-height: 44px;
    white-space: nowrap;
}

.badge{
    display:inline-flex;
    align-items:center;
    padding:.32rem .65rem;
    border-radius: 999px;
    font-weight: 950;
    letter-spacing:.10em;
    text-transform: uppercase;
    font-size:.72rem;
    border:1px solid rgba(255,255,255,.12);
    background: rgba(2,6,23,.25);
}
.badge.good{ border-color: rgba(34,197,94,.35); color: rgba(34,197,94,.95); }
.badge.warn{ border-color: rgba(245,158,11,.35); color: rgba(245,158,11,.95); }
.badge.bad{ border-color: rgba(239,68,68,.35); color: rgba(239,68,68,.95); }

.content{ padding: 14px; }

.grid{
    display:grid;
    grid-template-columns: minmax(0, 1fr);
    gap: 12px;
}

.card{
    border: 1px solid var(--border);
    border-radius: 20px;
    background: rgba(255,255,255,.02);
    padding: 14px;
}

.k{
    color: var(--muted);
    font-weight: 900;
    font-size: .76rem;
    letter-spacing:.10em;
    text-transform: uppercase;
}
.v{
    margin-top:.35rem;
    color: rgba(255,255,255,.92);
    font-weight: 900;
}
.big{
    font-size: 2rem;
    font-weight: 950;
    letter-spacing:.01em;
    line-height: 1.1;
}
.starline{
    color: rgba(245, 204, 21, .95);
    font-weight: 950;
    letter-spacing:.08em;
    font-size: 1rem;
}

.kvGrid{
    margin-top: 10px;
    display:grid;
    grid-template-columns: 1fr 1fr;
    gap: 10px;
}
.kvItem{
    border: 1px solid rgba(255,255,255,.08);
    background: rgba(2,6,23,.22);
    border-radius: 16px;
    padding: 12px;
}
.kvItem .k{ font-size:.72rem; }
.kvItem .v{ font-size:.95rem; }
.kvItem .subv{ margin-top:6px; color: rgba(255,255,255,.55); font-weight: 800; font-size: .86rem; word-break: break-word; }

.breakRow{
    display:flex;
    align-items:center;
    gap: 10px;
    padding: 10px 10px;
    border-radius: 14px;
    background: rgba(2,6,23,.25);
    border: 1px solid rgba(255,255,255,.06);
    margin-top: 10px;
}
.bar{
    flex: 1;
    height: 10px;
    border-radius: 999px;
    background: rgba(255,255,255,.06);
    overflow:hidden;
}
.bar > span{
    display:block;
    height: 100%;
    width: 0%;
    background: rgba(56,189,248,.75);
}
.small{
    font-size: .85rem;
    color: rgba(255,255,255,.85);
    font-weight: 800;
}

.actions{
    margin-top: 12px;
    display:flex;
    gap: 10px;
    flex-wrap:wrap;
}
.btnx{
    display:inline-flex;
    justify-content:center;
    align-items:center;
    gap: 8px;
    padding:.75rem 1rem;
    border-radius: 12px;
    font-weight: 950;
    text-decoration:none;
    border: 1px solid rgba(255,255,255,.14);
    background: transparent;
    color:#fff;
    min-height: 44px;
}
.btnx.primary{
    border:none;
    background: linear-gradient(180deg,#0ea5e9,#38bdf8);
    color:#02101b;
}
.btnx.ghost{
    background: rgba(255,255,255,.03);
}
.btnx:hover{ filter: brightness(1.05); }

.notice{
    margin-bottom: 12px;
    padding: .9rem 1rem;
    border-radius: 14px;
    border: 1px solid rgba(255,255,255,.08);
    font-weight: 800;
}

.notice.success{
    border-color: rgba(34,197,94,.26);
    background: rgba(34,197,94,.12);
    color: #bbf7d0;
}

.notice.error{
    border-color: rgba(239,68,68,.26);
    background: rgba(239,68,68,.12);
    color: #fecaca;
}

.detail-stack{
    display:grid;
    gap: 12px;
}

.adjustment-head{
    display:flex;
    justify-content:space-between;
    align-items:flex-start;
    gap:12px;
    flex-wrap:wrap;
}

.compare-grid{
    margin-top: 12px;
    display:grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 10px;
}

.compare-box{
    border: 1px solid rgba(255,255,255,.08);
    background: rgba(2,6,23,.22);
    border-radius: 16px;
    padding: 12px;
}

.meta-pills{
    margin-top: 10px;
    display:flex;
    flex-wrap:wrap;
    gap:8px;
}

.meta-pill{
    display:inline-flex;
    align-items:center;
    gap:6px;
    padding:.4rem .7rem;
    border-radius:999px;
    border:1px solid rgba(255,255,255,.10);
    background: rgba(255,255,255,.03);
    color: rgba(255,255,255,.88);
    font-size:.82rem;
    font-weight:800;
}

.reason-list{
    margin-top: 12px;
    display:flex;
    flex-wrap:wrap;
    gap:8px;
}

.reason-chip{
    display:inline-flex;
    align-items:center;
    padding:.42rem .72rem;
    border-radius:999px;
    border:1px solid rgba(56,189,248,.20);
    background: rgba(56,189,248,.10);
    color:#bae6fd;
    font-size:.8rem;
    font-weight:800;
}

.adjustment-copy{
    margin-top: 12px;
    color: rgba(255,255,255,.74);
    font-size: .92rem;
    line-height: 1.6;
}

.adjustment-form{
    margin-top: 12px;
    display:grid;
    gap: 12px;
}

.choice-grid{
    display:grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 10px;
}

.choice-card{
    display:flex;
    align-items:flex-start;
    gap:10px;
    padding: 12px;
    border-radius: 14px;
    border: 1px solid rgba(255,255,255,.08);
    background: rgba(2,6,23,.20);
}

.choice-card input{
    margin-top: 3px;
}

.field-grid{
    display:grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 10px;
}

.field-block{
    display:grid;
    gap: 8px;
}

.field-block.full{
    grid-column: 1 / -1;
}

.field-label{
    color: var(--muted);
    font-size: .78rem;
    font-weight: 900;
    letter-spacing: .08em;
    text-transform: uppercase;
}

.field-input,
.field-textarea,
.field-file{
    width: 100%;
    border-radius: 14px;
    border: 1px solid rgba(255,255,255,.10);
    background: rgba(2,6,23,.35);
    color: #fff;
    padding: .82rem .95rem;
}

.field-textarea{
    min-height: 110px;
    resize: vertical;
}

.field-error{
    color: #fca5a5;
    font-size: .82rem;
    font-weight: 800;
}

.field-help{
    color: var(--muted);
    font-size: .82rem;
    line-height: 1.5;
}

.option-choice{
    align-items:flex-start;
}

.option-choice.is-selected{
    border-color: rgba(56,189,248,.38);
    background: rgba(56,189,248,.08);
}

.option-choice-copy{
    display:grid;
    gap: 4px;
}

.option-choice-title{
    color: rgba(255,255,255,.94);
    font-weight: 900;
}

.option-choice-price{
    color: var(--muted);
    font-size: .84rem;
    font-weight: 800;
}

.field-block[hidden]{
    display:none !important;
}

.adjustment-preview{
    border: 1px solid rgba(255,255,255,.08);
    background: rgba(2,6,23,.26);
    border-radius: 18px;
    padding: 14px;
    display:grid;
    gap: 12px;
}

.preview-grid{
    display:grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 10px;
}

.preview-stat{
    border: 1px solid rgba(255,255,255,.06);
    background: rgba(255,255,255,.02);
    border-radius: 14px;
    padding: 12px;
    display:grid;
    gap: 6px;
}

.preview-value{
    color: rgba(255,255,255,.94);
    font-weight: 900;
    line-height: 1.4;
    word-break: break-word;
}

.preview-value.accent{
    color: var(--accent);
}

.preview-note{
    color: var(--muted);
    font-size: .84rem;
    font-weight: 800;
    line-height: 1.5;
}

.tracking-card{
    margin-top: 12px;
}

.tracking-head{
    display:flex;
    justify-content:space-between;
    align-items:flex-start;
    gap:12px;
    flex-wrap:wrap;
}

.tracking-head-copy{
    display:grid;
    gap:.35rem;
}

.tracking-head-copy .small{
    color: var(--muted);
}

.tracking-controls{
    display:flex;
    gap:10px;
    flex-wrap:wrap;
}

.tracking-map{
    margin-top: 12px;
    width: 100%;
    height: 340px;
    border-radius: 18px;
    overflow: hidden;
    border: 1px solid rgba(255,255,255,.08);
}

.tracking-meta-grid{
    margin-top: 12px;
    display:grid;
    grid-template-columns: 1fr 1fr;
    gap: 10px;
}

.tracking-meta-box{
    border: 1px solid rgba(255,255,255,.08);
    background: rgba(2,6,23,.24);
    border-radius: 16px;
    padding: 12px;
}

.tracking-meta-box .k{
    font-size:.72rem;
}

.tracking-meta-value{
    margin-top: .45rem;
    color: rgba(255,255,255,.94);
    font-weight: 800;
    line-height: 1.5;
    word-break: break-word;
}

.tracking-meta-sub{
    margin-top: .45rem;
    color: var(--muted);
    font-size: .84rem;
    font-weight: 700;
}

.tracking-status{
    min-height: 1.25rem;
    color: var(--muted);
    font-size: .84rem;
    font-weight: 700;
}

.tracking-status.error{
    color: #fca5a5;
}

@media (max-width: 992px){
    .grid{ grid-template-columns: 1fr; }
}
@media (max-width: 576px){
    .container.page{ padding-left: 10px; padding-right: 10px; }
    .pill{ width:100%; justify-content:center; }
    .kvGrid{ grid-template-columns: 1fr; }
    .compare-grid,
    .choice-grid,
    .field-grid,
    .preview-grid{ grid-template-columns: 1fr; }
    .btnx{ width:100%; }
    .tracking-controls{
        width:100%;
    }
    .tracking-map{
        height: 280px;
    }
    .tracking-meta-grid{
        grid-template-columns: 1fr;
    }
}
</style>

<div class="container page">
    <div class="shell">

        <div class="top">
            <div>
                <h5 class="h1">Booking Details</h5>
                <p class="sub">
                    Reference <span class="ref">{{ $ref }}</span> • Created {{ $created }}
                </p>
            </div>

            <span class="pill">
                <span class="badge {{ $badgeClass }}">{{ strtoupper(str_replace('_',' ',$stLower ?: 'N/A')) }}</span>
                <span style="opacity:.9;">₱{{ number_format($amount, 2) }}</span>
            </span>
        </div>

        <div class="content">
            @if(session('success'))
                <div class="notice success">{{ session('success') }}</div>
            @endif

            @if($errors->has('general'))
                <div class="notice error">{{ $errors->first('general') }}</div>
            @endif

            <div class="grid">

                {{-- LEFT: BOOKING INFO --}}
                <div class="card">
                    <div class="k">Booking Info</div>

                    <div class="kvGrid">
                        <div class="kvItem">
                            <div class="k">Service</div>
                            <div class="v">{{ $serviceName }}</div>
                        </div>

                        <div class="kvItem">
                            <div class="k">Option</div>
                            <div class="v">{{ $optionName ?: '—' }}</div>
                        </div>

                        <div class="kvItem">
                            <div class="k">Schedule</div>
                            <div class="v">{{ $dateLabel }} • {{ $timeLabel }}</div>
                        </div>

                        <div class="kvItem">
                            <div class="k">Address</div>
                            <div class="v">{{ $address }}</div>
                        </div>

                        <div class="kvItem">
                            <div class="k">Customer</div>
                            <div class="v">{{ $customerName }}</div>
                            <div class="subv">
                                {{ $customerPhone }}
                                @if($customerEmail)
                                    <span style="opacity:.8;"> • {{ $customerEmail }}</span>
                                @endif
                            </div>
                        </div>

                        <div class="kvItem">
                            <div class="k">Provider</div>
                            <div class="v">{{ $providerName }}</div>
                            <div class="subv">
                                {{ $providerPhone }}
                                @if($providerLocation)
                                    <span style="opacity:.8;"> • {{ $providerLocation }}</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="actions">
                        @if(\Illuminate\Support\Facades\Route::has('provider.bookings'))
                            <a class="btnx primary" href="{{ route('provider.bookings') }}">← Back</a>
                        @else
                            <a class="btnx primary" href="{{ url()->previous() }}">← Back</a>
                        @endif
                    </div>
                </div>

            </div>

            <div class="detail-stack">
                @if($cancellationReason !== '')
                    <div class="card">
                        <div class="adjustment-head">
                            <div>
                                <div class="k">Cancellation Reason</div>
                                <div class="v">Cancelled by {{ $cancelledByLabel }}</div>
                            </div>
                            <span class="badge bad">Cancelled</span>
                        </div>
                        <div class="adjustment-copy">{{ $cancellationReason }}</div>
                    </div>
                @endif

                @if($adjustment)
                    <div class="card">
                        <div class="adjustment-head">
                            <div>
                                <div class="k">Adjustment Review</div>
                                <div class="v">{{ $adjustment->status_label ?: 'Adjustment logged' }}</div>
                            </div>
                            <span class="badge {{ ($adjustment->status_key ?? '') === 'adjustment_accepted' ? 'good' : ((($adjustment->status_key ?? '') === 'adjustment_rejected') ? 'bad' : 'warn') }}">
                                {{ strtoupper(str_replace('_', ' ', $adjustment->status_key ?: 'pending')) }}
                            </span>
                        </div>

                        <div class="compare-grid">
                            <div class="compare-box">
                                <div class="k">Original Booking</div>
                                <div class="v">{{ $optionName ?: $serviceName }}</div>
                                <div class="adjustment-copy">Original total: PHP {{ $adjustment->original_price_display }}</div>
                            </div>

                            <div class="compare-box">
                                <div class="k">Requested Update</div>
                                <div class="v">{{ $adjustment->proposed_scope_summary ?: 'No scope summary provided.' }}</div>
                                <div class="adjustment-copy">
                                    Additional fee: PHP {{ $adjustment->additional_fee_display }}<br>
                                    Proposed total: PHP {{ $adjustment->proposed_total_display }}
                                </div>
                            </div>
                        </div>

                        <div class="meta-pills">
                            <span class="meta-pill">Increase {{ $adjustment->price_increase_percent_display }}%</span>
                            @if(!empty($adjustment->resolved_at_label))
                                <span class="meta-pill">Resolved {{ $adjustment->resolved_at_label }}</span>
                            @endif
                            @if(!empty($adjustment->evidence_url))
                                <a class="meta-pill" href="{{ $adjustment->evidence_url }}" target="_blank" rel="noopener">View evidence</a>
                            @endif
                        </div>

                        @if(!empty($adjustment->reason_labels))
                            <div class="reason-list">
                                @foreach($adjustment->reason_labels as $label)
                                    <span class="reason-chip">{{ $label }}</span>
                                @endforeach
                            </div>
                        @endif

                        @if(!empty($adjustment->provider_note))
                            <div class="adjustment-copy"><strong>Provider note:</strong> {{ $adjustment->provider_note }}</div>
                        @endif

                        @if(!empty($adjustment->customer_response_note))
                            <div class="adjustment-copy"><strong>Customer response:</strong> {{ $adjustment->customer_response_note }}</div>
                        @endif
                    </div>
                @endif

                @if($canReportMismatch)
                    <div class="card">
                        <div class="adjustment-head">
                            <div>
                                <div class="k">Report Mismatch</div>
                                <div class="v">Compare the original booking with the actual work onsite.</div>
                            </div>
                            <span class="badge warn">In Progress</span>
                        </div>

                        <div class="compare-grid">
                            <div class="compare-box">
                                <div class="k">Original Details</div>
                                <div class="v">{{ $serviceName }}</div>
                                <div class="adjustment-copy">{{ $originalSelectionLabel ?: 'Selected option not available.' }}</div>
                                <div class="adjustment-copy">Original total: PHP {{ number_format($originalPrice, 2) }}</div>
                            </div>
                            <div class="compare-box">
                                <div class="k">Automatic Pricing</div>
                                <div class="adjustment-copy">Pick the mismatch reason, choose the actual onsite size or sections, and upload evidence.</div>
                                <div class="adjustment-copy">The system recalculates the added amount automatically and sends the new total to the customer for approval.</div>
                            </div>
                        </div>

                        <form method="POST" action="{{ route('provider.bookings.adjustment.submit', $booking->reference_code) }}" enctype="multipart/form-data" class="adjustment-form" data-adjustment-form>
                            @csrf

                            <div class="field-block">
                                <div class="field-label">Mismatch Reasons</div>
                                <div class="choice-grid">
                                    @foreach([
                                        'larger_area' => 'Larger area than declared',
                                        'additional_rooms' => 'Additional rooms or sections',
                                        'heavy_soiling' => 'Heavily soiled or deep cleaning needed',
                                        'other' => 'Other reason',
                                    ] as $code => $label)
                                        <label class="choice-card">
                                            <input type="checkbox" name="reason_codes[]" value="{{ $code }}" data-reason-code="{{ $code }}" @checked(in_array($code, $selectedReasonCodes, true))>
                                            <span>{{ $label }}</span>
                                        </label>
                                    @endforeach
                                </div>
                                @error('reason_codes')
                                    <div class="field-error">{{ $message }}</div>
                                @enderror
                                @error('reason_codes.*')
                                    <div class="field-error">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="field-grid">
                                <div class="field-block full">
                                    <div class="field-label">Actual Onsite Size / Scope</div>
                                    @if($isSpecificAreaBooking)
                                        <div class="choice-grid">
                                            @foreach($serviceOptions as $option)
                                                @php($optionId = (int) ($option->id ?? 0))
                                                <label class="choice-card option-choice @if(in_array($optionId, $selectedCorrectedOptionIds, true)) is-selected @endif" data-option-choice>
                                                    <input
                                                        type="checkbox"
                                                        name="corrected_option_ids[]"
                                                        value="{{ $optionId }}"
                                                        data-option-checkbox
                                                        data-option-id="{{ $optionId }}"
                                                        data-option-label="{{ $option->label }}"
                                                        data-option-price="{{ number_format((float) ($option->price_addition ?? 0), 2, '.', '') }}"
                                                        @checked(in_array($optionId, $selectedCorrectedOptionIds, true))
                                                    >
                                                    <span class="option-choice-copy">
                                                        <span class="option-choice-title">{{ $option->label }}</span>
                                                        <span class="option-choice-price">+ PHP {{ number_format((float) ($option->price_addition ?? 0), 2) }}</span>
                                                    </span>
                                                </label>
                                            @endforeach
                                        </div>
                                    @else
                                        <select class="field-input" id="corrected_option_id" name="corrected_option_id" data-option-select>
                                            <option value="">Choose corrected size or scope</option>
                                            @foreach($serviceOptions as $option)
                                                @php($optionId = (int) ($option->id ?? 0))
                                                <option
                                                    value="{{ $optionId }}"
                                                    data-option-id="{{ $optionId }}"
                                                    data-option-label="{{ $option->label }}"
                                                    data-option-price="{{ number_format((float) ($option->price_addition ?? 0), 2, '.', '') }}"
                                                    @selected(($selectedCorrectedOptionIds[0] ?? 0) === $optionId)
                                                >
                                                    {{ $option->label }} (+ PHP {{ number_format((float) ($option->price_addition ?? 0), 2) }})
                                                </option>
                                            @endforeach
                                        </select>
                                    @endif
                                    <div class="field-help" data-scope-help>Choose the real onsite size or sections. The new total updates automatically.</div>
                                    @error('corrected_option_id')
                                        <div class="field-error">{{ $message }}</div>
                                    @enderror
                                    @error('corrected_option_ids')
                                        <div class="field-error">{{ $message }}</div>
                                    @enderror
                                    @error('corrected_option_ids.*')
                                        <div class="field-error">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="field-block full" data-other-reason-wrap @if(!$showOtherReason) hidden @endif>
                                    <label class="field-label" for="other_reason">Other Reason</label>
                                    <textarea class="field-textarea" id="other_reason" name="other_reason" placeholder="Add a short reason if needed.">{{ $otherReasonValue }}</textarea>
                                    @error('other_reason')
                                        <div class="field-error">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="field-block full">
                                    <label class="field-label" for="provider_note">Provider Note</label>
                                    <textarea class="field-textarea" id="provider_note" name="provider_note" placeholder="Tell the customer what changed onsite.">{{ $providerNoteValue }}</textarea>
                                    @error('provider_note')
                                        <div class="field-error">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="field-block full">
                                    <label class="field-label" for="evidence">Photo or File Evidence</label>
                                    <input class="field-file" id="evidence" name="evidence" type="file" accept=".jpg,.jpeg,.png,.webp,.pdf">
                                    <div class="field-help">Required for every mismatch request.</div>
                                    @error('evidence')
                                        <div class="field-error">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="field-block full">
                                <div class="field-label">Customer Approval Preview</div>
                                <div
                                    class="adjustment-preview"
                                    data-adjustment-preview
                                    data-original-price="{{ number_format($originalPrice, 2, '.', '') }}"
                                    data-service-base-price="{{ number_format($serviceBasePrice, 2, '.', '') }}"
                                    data-original-option-total="{{ number_format($originalOptionTotal, 2, '.', '') }}"
                                    data-original-selection="{{ $originalSelectionLabel }}"
                                    data-original-option-ids='@json(array_values($originalOptionIds))'
                                    data-max-increase="{{ number_format($adjustmentMaxIncreasePercent, 2, '.', '') }}"
                                >
                                    <div class="preview-grid">
                                        <div class="preview-stat">
                                            <div class="field-label">Booked Selection</div>
                                            <div class="preview-value">{{ $originalSelectionLabel }}</div>
                                        </div>
                                        <div class="preview-stat">
                                            <div class="field-label">Actual Onsite Scope</div>
                                            <div class="preview-value" data-preview-selection>{{ $selectedCorrectedLabel }}</div>
                                        </div>
                                        <div class="preview-stat">
                                            <div class="field-label">Original Total</div>
                                            <div class="preview-value">PHP {{ number_format($originalPrice, 2) }}</div>
                                        </div>
                                        <div class="preview-stat">
                                            <div class="field-label">Condition Fee</div>
                                            <div class="preview-value" data-preview-auto-fee>PHP {{ number_format($automaticReasonFee, 2) }}</div>
                                        </div>
                                        <div class="preview-stat">
                                            <div class="field-label">Added Amount</div>
                                            <div class="preview-value" data-preview-additional-fee>PHP {{ number_format($previewAdditionalFee, 2) }}</div>
                                        </div>
                                        <div class="preview-stat">
                                            <div class="field-label">New Total</div>
                                            <div class="preview-value accent" data-preview-proposed-total>PHP {{ number_format($previewProposedTotal, 2) }}</div>
                                        </div>
                                    </div>
                                    <div class="preview-note" data-preview-note>
                                        The system will compare the booked scope with the actual onsite scope and calculate the new total automatically.
                                    </div>
                                    <div class="field-error" data-adjustment-warning hidden></div>
                                </div>
                            </div>

                            <div class="actions">
                                <button type="submit" class="btnx primary" data-adjustment-submit>Send for Customer Approval</button>
                            </div>
                        </form>
                    </div>
                @endif
            </div>

            @if($booking->tracking_enabled)
                <div class="card tracking-card">
                    <div class="tracking-head">
                        <div class="tracking-head-copy">
                            <div class="k">Live Tracking</div>
                            <div id="providerTrackingStatusText" class="tracking-status">
                                Start tracking when you are on the way so the customer can follow your live location.
                            </div>
                        </div>

                        <div class="tracking-controls">
                            <button type="button" class="btnx primary" id="trackingToggleBtn">Start Tracking</button>
                        </div>
                    </div>

                    <div id="providerTrackingMap" class="tracking-map"></div>

                    <div class="tracking-meta-grid">
                        <div class="tracking-meta-box">
                            <div class="k">Customer Pin</div>
                            <div class="tracking-meta-value" id="customerLocationText">
                                {{ $customerPinnedAddress !== '' ? $customerPinnedAddress : 'Customer pin is not available for this booking yet.' }}
                            </div>
                            <div class="tracking-meta-sub" id="customerCoordsText">
                                @if($customerLatitude !== null && $customerLongitude !== null)
                                    {{ number_format($customerLatitude, 6) }}, {{ number_format($customerLongitude, 6) }}
                                @else
                                    No saved customer coordinates yet.
                                @endif
                            </div>
                        </div>

                        <div class="tracking-meta-box">
                            <div class="k">Your Latest Shared Location</div>
                            <div class="tracking-meta-value" id="providerLocationText">
                                {{ $providerTrackedAddress !== '' ? $providerTrackedAddress : 'No live provider location has been shared yet.' }}
                            </div>
                            <div class="tracking-meta-sub" id="providerTrackingMeta">
                                @if($providerLatitude !== null && $providerLongitude !== null)
                                    {{ number_format($providerLatitude, 6) }}, {{ number_format($providerLongitude, 6) }}
                                @else
                                    Waiting for the first provider location update.
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>

    </div>
</div>

<script>
(() => {
    const form = document.querySelector('[data-adjustment-form]');

    if (!form) {
        return;
    }

    const preview = form.querySelector('[data-adjustment-preview]');
    const selectionText = form.querySelector('[data-preview-selection]');
    const autoFeeText = form.querySelector('[data-preview-auto-fee]');
    const additionalFeeText = form.querySelector('[data-preview-additional-fee]');
    const proposedTotalText = form.querySelector('[data-preview-proposed-total]');
    const previewNote = form.querySelector('[data-preview-note]');
    const warningEl = form.querySelector('[data-adjustment-warning]');
    const submitBtn = form.querySelector('[data-adjustment-submit]');
    const otherReasonWrap = form.querySelector('[data-other-reason-wrap]');
    const otherReasonField = form.querySelector('#other_reason');
    const scopeHelp = form.querySelector('[data-scope-help]');
    const optionSelect = form.querySelector('[data-option-select]');
    const optionCheckboxes = Array.from(form.querySelectorAll('[data-option-checkbox]'));
    const optionChoiceCards = Array.from(form.querySelectorAll('[data-option-choice]'));
    const reasonCheckboxes = Array.from(form.querySelectorAll('[data-reason-code]'));

    if (!preview || !selectionText || !autoFeeText || !additionalFeeText || !proposedTotalText || !previewNote || !warningEl || !submitBtn) {
        return;
    }

    const originalPrice = Number(preview.dataset.originalPrice || 0);
    const serviceBasePrice = Number(preview.dataset.serviceBasePrice || 0);
    const maxIncreasePercent = Number(preview.dataset.maxIncrease || 35);
    const originalSelection = preview.dataset.originalSelection || 'Original selection';
    const originalOptionIds = JSON.parse(preview.dataset.originalOptionIds || '[]').map((value) => Number(value));

    const formatCurrency = (value) => `PHP ${Number(value || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;

    const selectedReasons = () => reasonCheckboxes
        .filter((checkbox) => checkbox.checked)
        .map((checkbox) => checkbox.dataset.reasonCode || checkbox.value);

    const selectedOptionState = () => {
        if (optionSelect) {
            const selected = optionSelect.options[optionSelect.selectedIndex];
            const optionId = Number(selected?.dataset.optionId || selected?.value || 0);

            if (!optionId) {
                return {
                    ids: [],
                    total: 0,
                    label: 'Choose corrected size or scope',
                };
            }

            return {
                ids: [optionId],
                total: Number(selected?.dataset.optionPrice || 0),
                label: selected?.dataset.optionLabel || selected?.textContent?.trim() || originalSelection,
            };
        }

        const chosen = optionCheckboxes.filter((checkbox) => checkbox.checked);

        return {
            ids: chosen.map((checkbox) => Number(checkbox.dataset.optionId || checkbox.value || 0)).filter((value) => value > 0),
            total: chosen.reduce((sum, checkbox) => sum + Number(checkbox.dataset.optionPrice || 0), 0),
            label: chosen.map((checkbox) => checkbox.dataset.optionLabel || '').filter(Boolean).join(', ') || originalSelection,
        };
    };

    const matchesOriginalSelection = (ids) => {
        const normalizedLeft = [...ids].map(Number).filter((value) => value > 0).sort((a, b) => a - b);
        const normalizedRight = [...originalOptionIds].map(Number).filter((value) => value > 0).sort((a, b) => a - b);

        if (normalizedLeft.length !== normalizedRight.length) {
            return false;
        }

        return normalizedLeft.every((value, index) => value === normalizedRight[index]);
    };

    const syncChoiceCards = () => {
        optionChoiceCards.forEach((card) => {
            const checkbox = card.querySelector('[data-option-checkbox]');
            card.classList.toggle('is-selected', Boolean(checkbox?.checked));
        });
    };

    const syncOtherReason = () => {
        const showOtherReason = selectedReasons().includes('other');

        if (otherReasonWrap) {
            otherReasonWrap.hidden = !showOtherReason;
        }

        if (otherReasonField) {
            otherReasonField.required = showOtherReason;

            if (!showOtherReason) {
                otherReasonField.value = '';
            }
        }
    };

    const syncPreview = () => {
        const reasons = selectedReasons();
        const optionState = selectedOptionState();
        const hasScopeReason = reasons.includes('larger_area') || reasons.includes('additional_rooms');
        const autoConditionFee = reasons.includes('heavy_soiling')
            ? Math.max(300, originalPrice * 0.10)
            : 0;
        const proposedTotal = Number((serviceBasePrice + optionState.total + autoConditionFee).toFixed(2));
        const additionalFee = Number((proposedTotal - originalPrice).toFixed(2));
        const increasePercent = originalPrice > 0
            ? Number((((proposedTotal - originalPrice) / originalPrice) * 100).toFixed(2))
            : 0;

        if (scopeHelp) {
            scopeHelp.textContent = hasScopeReason
                ? 'Pick the real onsite size or sections so the system can calculate the correct added amount.'
                : 'Keep the original size if the scope is the same. The system only adds an automatic fee when needed.';
        }

        selectionText.textContent = optionState.label || originalSelection;
        autoFeeText.textContent = formatCurrency(autoConditionFee);
        additionalFeeText.textContent = formatCurrency(additionalFee);
        proposedTotalText.textContent = formatCurrency(proposedTotal);
        if (hasScopeReason && autoConditionFee > 0) {
            previewNote.textContent = `The new total includes the corrected size plus the automatic condition fee. Increase: ${increasePercent.toFixed(2)}%.`;
        } else if (hasScopeReason) {
            previewNote.textContent = `The new total is based on the corrected onsite size or sections. Increase: ${increasePercent.toFixed(2)}%.`;
        } else if (autoConditionFee > 0) {
            previewNote.textContent = `The original scope stays the same, and the system adds the automatic condition fee. Increase: ${increasePercent.toFixed(2)}%.`;
        } else {
            previewNote.textContent = `The new total will be checked against the actual onsite scope. Increase: ${increasePercent.toFixed(2)}%.`;
        }

        let warning = '';
        if (!optionState.ids.length) {
            warning = 'Choose the corrected size or sections before sending the mismatch request.';
        } else if (hasScopeReason && matchesOriginalSelection(optionState.ids)) {
            warning = 'Choose a different size or section set when reporting a larger area or additional rooms.';
        } else if (proposedTotal < originalPrice) {
            warning = 'The corrected selection cannot reduce the original booking total.';
        } else if (increasePercent > maxIncreasePercent) {
            warning = `This update exceeds the ${maxIncreasePercent.toFixed(0)}% safety limit.`;
        }

        warningEl.hidden = warning === '';
        warningEl.textContent = warning;
        submitBtn.disabled = warning !== '';
    };

    if (optionSelect) {
        optionSelect.addEventListener('change', syncPreview);
    }

    optionCheckboxes.forEach((checkbox) => {
        checkbox.addEventListener('change', () => {
            syncChoiceCards();
            syncPreview();
        });
    });

    reasonCheckboxes.forEach((checkbox) => {
        checkbox.addEventListener('change', () => {
            syncOtherReason();
            syncPreview();
        });
    });

    syncChoiceCards();
    syncOtherReason();
    syncPreview();

    form.addEventListener('submit', (event) => {
        if (submitBtn.disabled) {
            event.preventDefault();
            return;
        }

        const reasons = selectedReasons();
        const optionState = selectedOptionState();
        const autoConditionFee = reasons.includes('heavy_soiling')
            ? Math.max(300, originalPrice * 0.10)
            : 0;
        const proposedTotal = Number((serviceBasePrice + optionState.total + autoConditionFee).toFixed(2));
        const confirmMessage = [
            'Send this mismatch request to the customer?',
            '',
            `Booked scope: ${originalSelection}`,
            `Actual scope: ${optionState.label || originalSelection}`,
            `New total: ${formatCurrency(proposedTotal)}`,
            '',
            'The booking price will stay pending until the customer accepts this adjustment.',
        ].join('\n');

        if (!window.confirm(confirmMessage)) {
            event.preventDefault();
        }
    });
})();
</script>

<script
    src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
    integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
    crossorigin=""
></script>
<script>
(() => {
    const mapEl = document.getElementById('providerTrackingMap');

    if (!mapEl || !window.L) {
        return;
    }

    const trackingState = {
        bookingReference: @json($booking->reference_code),
        enabled: @json((bool) $booking->tracking_enabled),
        updateUrl: @json(route('provider.bookings.location.update', $booking->reference_code)),
        stopUrl: @json(route('provider.bookings.location.stop', $booking->reference_code)),
        csrf: @json(csrf_token()),
        customer: {
            latitude: @json($customerLatitude),
            longitude: @json($customerLongitude),
            address: @json($customerPinnedAddress),
        },
        provider: {
            latitude: @json($providerLatitude),
            longitude: @json($providerLongitude),
            address: @json($providerTrackedAddress),
            isTracking: @json((bool) ($providerLocationRow->is_tracking ?? false)),
            trackedAt: @json($providerLocationRow->tracked_at ?? $providerLocationRow->updated_at ?? null),
        },
    };

    const statusEl = document.getElementById('providerTrackingStatusText');
    const toggleBtn = document.getElementById('trackingToggleBtn');
    const customerLocationTextEl = document.getElementById('customerLocationText');
    const customerCoordsTextEl = document.getElementById('customerCoordsText');
    const providerLocationTextEl = document.getElementById('providerLocationText');
    const providerTrackingMetaEl = document.getElementById('providerTrackingMeta');

    const defaultCenter = [8.9475, 125.5436];
    const isAppleMobile = /iPad|iPhone|iPod/.test(navigator.userAgent || '')
        || (navigator.platform === 'MacIntel' && navigator.maxTouchPoints > 1);

    let map = null;
    let customerMarker = null;
    let providerMarker = null;
    let watchId = null;
    let lastSharedAt = 0;
    let sendingLocation = false;
    let trackingActive = false;
    let toggleBusy = false;

    function setStatus(message, isError = false) {
        if (!statusEl) {
            return;
        }

        statusEl.textContent = message;
        statusEl.classList.toggle('error', isError);
    }

    function formatCoordinatePair(latitude, longitude) {
        if (latitude === null || longitude === null || latitude === '' || longitude === '') {
            return null;
        }

        return `${Number(latitude).toFixed(6)}, ${Number(longitude).toFixed(6)}`;
    }

    // Keep a single customer marker and a single provider marker on the map.
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

    function fitToKnownPoints() {
        const points = [];

        if (customerMarker) {
            points.push(customerMarker.getLatLng());
        }

        if (providerMarker) {
            points.push(providerMarker.getLatLng());
        }

        if (points.length >= 2) {
            map.fitBounds(L.latLngBounds(points), {
                padding: [32, 32],
                maxZoom: 16,
            });
            return;
        }

        if (points.length === 1) {
            map.setView(points[0], 15);
            return;
        }

        map.setView(defaultCenter, 13);
    }

    function updateCustomerCard() {
        if (customerLocationTextEl) {
            customerLocationTextEl.textContent = trackingState.customer.address || 'Customer pin is not available for this booking yet.';
        }

        if (customerCoordsTextEl) {
            customerCoordsTextEl.textContent = formatCoordinatePair(
                trackingState.customer.latitude,
                trackingState.customer.longitude
            ) || 'No saved customer coordinates yet.';
        }
    }

    function updateProviderCard() {
        if (providerLocationTextEl) {
            providerLocationTextEl.textContent = trackingState.provider.address || 'No live provider location has been shared yet.';
        }

        if (providerTrackingMetaEl) {
            const coords = formatCoordinatePair(trackingState.provider.latitude, trackingState.provider.longitude);
            const trackedAt = trackingState.provider.trackedAt ? `Updated ${trackingState.provider.trackedAt}` : 'Waiting for the first provider location update.';
            providerTrackingMetaEl.textContent = coords ? `${coords} | ${trackedAt}` : trackedAt;
        }
    }

    function syncButtonState() {
        if (!toggleBtn) {
            return;
        }

        toggleBtn.disabled = toggleBusy || !trackingState.enabled;
        toggleBtn.textContent = trackingActive ? 'Stop Tracking' : 'Start Tracking';
        toggleBtn.classList.toggle('primary', !trackingActive);
        toggleBtn.classList.toggle('ghost', trackingActive);
    }

    function stopWatchingPosition() {
        if (watchId !== null && navigator.geolocation) {
            navigator.geolocation.clearWatch(watchId);
            watchId = null;
        }
    }

    function formatLocationError(error) {
        if (!window.isSecureContext) {
            return 'Live tracking needs a secure HTTPS page before location can be shared.';
        }

        if (!error || typeof error.code === 'undefined') {
            return 'Unable to read your current location right now.';
        }

        if (error.code === 1) {
            return isAppleMobile
                ? 'Location access is blocked on this iPhone or iPad. Check Safari and Location Services settings, reload the page, then tap Start Tracking again.'
                : 'Location access is blocked. Please allow location for this browser, then tap Start Tracking again.';
        }

        if (error.code === 2) {
            return 'Your location could not be found. Try moving to an area with better signal, then try again.';
        }

        if (error.code === 3) {
            return 'Location took too long to load. Please try again.';
        }

        return 'Unable to read your current location right now.';
    }

    async function pushProviderLocation(latitude, longitude) {
        if (sendingLocation) {
            return;
        }

        sendingLocation = true;

        try {
            const response = await fetch(trackingState.updateUrl, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': trackingState.csrf,
                },
                body: JSON.stringify({
                    latitude,
                    longitude,
                }),
            });

            const payload = await response.json();

            if (!response.ok) {
                if (response.status === 422) {
                    stopWatchingPosition();
                    trackingActive = false;
                    trackingState.provider.isTracking = false;
                    syncButtonState();
                }

                throw new Error(payload.message || 'Unable to share location right now.');
            }

            trackingState.provider.latitude = payload.location?.latitude ?? latitude;
            trackingState.provider.longitude = payload.location?.longitude ?? longitude;
            trackingState.provider.address = payload.location?.formatted_address || trackingState.provider.address;
            trackingState.provider.trackedAt = payload.location?.tracked_at || null;
            trackingState.provider.isTracking = true;

            providerMarker = ensureMarker(providerMarker, latitude, longitude, {
                radius: 9,
                color: '#38bdf8',
                fillColor: '#38bdf8',
                fillOpacity: 0.9,
                weight: 2,
            });

            updateProviderCard();
            fitToKnownPoints();
            setStatus('Live tracking is on. Your latest location was shared.');
        } finally {
            sendingLocation = false;
        }
    }

    async function sharePosition(position, forceSend = false) {
        if (!trackingActive) {
            return;
        }

        const now = Date.now();
        if (!forceSend && now - lastSharedAt < 8000) {
            return;
        }

        lastSharedAt = now;
        await pushProviderLocation(position.coords.latitude, position.coords.longitude);
    }

    function beginTrackingWatch() {
        stopWatchingPosition();

        watchId = navigator.geolocation.watchPosition((position) => {
            sharePosition(position).catch((error) => {
                setStatus(error.message || 'Unable to share location right now.', true);
            });
        }, (error) => {
            if (error?.code === 1) {
                trackingActive = false;
                trackingState.provider.isTracking = false;
                stopWatchingPosition();
                syncButtonState();
            }

            setStatus(formatLocationError(error), true);
        }, {
            enableHighAccuracy: true,
            timeout: 15000,
            maximumAge: 3000,
        });
    }

    function startTracking() {
        if (!trackingState.enabled || trackingActive || toggleBusy || !navigator.geolocation) {
            if (!navigator.geolocation) {
                setStatus('This browser does not support live location sharing.', true);
            }
            return;
        }

        if (!window.isSecureContext) {
            setStatus('Live tracking needs a secure HTTPS page before location can be shared.', true);
            return;
        }

        toggleBusy = true;
        syncButtonState();
        setStatus(isAppleMobile ? 'Requesting location access from your device...' : 'Checking location access...');

        // Request the first location directly from the tap event so Safari/iOS
        // can show its permission prompt reliably.
        navigator.geolocation.getCurrentPosition(async (firstPosition) => {
            try {
                trackingActive = true;
                trackingState.provider.isTracking = true;
                lastSharedAt = 0;
                syncButtonState();

                await sharePosition(firstPosition, true);
                beginTrackingWatch();
                setStatus('Live tracking is active. Keep this page open while you are on the way.');
            } catch (error) {
                trackingActive = false;
                trackingState.provider.isTracking = false;
                stopWatchingPosition();
                setStatus(error.message || 'Unable to start live tracking right now.', true);
            } finally {
                toggleBusy = false;
                syncButtonState();
            }
        }, (error) => {
            trackingActive = false;
            trackingState.provider.isTracking = false;
            stopWatchingPosition();
            setStatus(formatLocationError(error), true);
            toggleBusy = false;
            syncButtonState();
        }, {
            enableHighAccuracy: true,
            timeout: 15000,
            maximumAge: 0,
        });
    }

    async function stopTracking() {
        if ((!trackingActive && !trackingState.provider.isTracking) || toggleBusy) {
            return;
        }

        toggleBusy = true;
        trackingActive = false;
        trackingState.provider.isTracking = false;
        stopWatchingPosition();
        syncButtonState();

        try {
            await fetch(trackingState.stopUrl, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': trackingState.csrf,
                },
            });
        } catch (error) {
            // Keep the local stop state even if the stop request cannot be confirmed.
        } finally {
            toggleBusy = false;
            syncButtonState();
        }

        setStatus('Live tracking stopped. Tap Start Tracking again when you are ready to share your location.');
    }

    function toggleTracking() {
        if (trackingActive) {
            stopTracking();
            return;
        }

        startTracking();
    }

    map = L.map(mapEl, {
        zoomControl: true,
    }).setView(defaultCenter, 13);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; OpenStreetMap contributors',
    }).addTo(map);

    if (trackingState.customer.latitude !== null && trackingState.customer.longitude !== null) {
        customerMarker = ensureMarker(customerMarker, trackingState.customer.latitude, trackingState.customer.longitude, {
            radius: 9,
            color: '#22c55e',
            fillColor: '#22c55e',
            fillOpacity: 0.9,
            weight: 2,
        });
    }

    if (trackingState.provider.latitude !== null && trackingState.provider.longitude !== null) {
        providerMarker = ensureMarker(providerMarker, trackingState.provider.latitude, trackingState.provider.longitude, {
            radius: 9,
            color: '#38bdf8',
            fillColor: '#38bdf8',
            fillOpacity: 0.9,
            weight: 2,
        });
    }

    updateCustomerCard();
    updateProviderCard();
    fitToKnownPoints();
    syncButtonState();

    if (trackingState.enabled) {
        setStatus(
            trackingState.provider.isTracking
                ? 'Tap Start Tracking to continue sharing from this device.'
                : 'Tap Start Tracking when you are on the way so the customer can follow your live location.'
        );
    } else {
        setStatus('Live tracking is only available while this booking is still active.');
    }

    toggleBtn?.addEventListener('click', toggleTracking);

    window.addEventListener('beforeunload', () => {
        stopWatchingPosition();
    });
})();
</script>

@endsection
