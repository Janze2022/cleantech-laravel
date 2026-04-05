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
    $adjustmentServices = collect($adjustmentServices ?? [])->values();
    $servicesById = $adjustmentServices->keyBy(fn ($service) => (int) ($service->id ?? 0));
    $serviceOptionsByService = collect($serviceOptionsByService ?? collect())
        ->map(fn ($options) => collect($options)->values());
    $currentServiceId = (int) ($currentServiceId ?? ($booking->service_id ?? 0));
    $requestedServiceId = (int) ($requestedServiceId ?? $currentServiceId);
    if (!$servicesById->has($requestedServiceId)) {
        $requestedServiceId = $currentServiceId;
    }

    $currentServiceOptions = collect($serviceOptionsByService->get($currentServiceId, collect()));
    $currentServiceOptionsById = $currentServiceOptions
        ->keyBy(fn ($option) => (int) ($option->id ?? 0));

    $originalOptionIds = collect($currentOptionIds ?? [])
        ->map(fn ($value) => (int) $value)
        ->filter(fn ($value) => $value > 0 && $currentServiceOptionsById->has($value))
        ->values()
        ->all();
    $selectedServiceId = (int) old('corrected_service_id', $requestedServiceId);
    if (!$servicesById->has($selectedServiceId)) {
        $selectedServiceId = $currentServiceId;
    }

    $selectedService = $servicesById->get($selectedServiceId);
    $selectedServiceOptions = collect($serviceOptionsByService->get($selectedServiceId, collect()));
    $selectedServiceOptionsById = $selectedServiceOptions
        ->keyBy(fn ($option) => (int) ($option->id ?? 0));

    $originalOptionLabels = collect($originalOptionIds)
        ->map(fn ($id) => trim((string) ($currentServiceOptionsById->get((int) $id)->label ?? '')))
        ->filter()
        ->values()
        ->all();

    $defaultCorrectedOptionIds = collect($requestedOptionIds ?? [])
        ->map(fn ($value) => (int) $value)
        ->filter(fn ($value) => $value > 0 && $selectedServiceOptionsById->has($value))
        ->values()
        ->all();

    if (empty($defaultCorrectedOptionIds)) {
        if ($selectedServiceId === $currentServiceId) {
            $defaultCorrectedOptionIds = $originalOptionIds;
        } else {
            $defaultCorrectedOptionIds = $selectedServiceOptions
                ->filter(fn ($option) => in_array(trim((string) ($option->label ?? '')), $originalOptionLabels, true))
                ->map(fn ($option) => (int) ($option->id ?? 0))
                ->filter(fn ($id) => $id > 0)
                ->values()
                ->all();
        }
    }

    $selectedReasonCodes = collect(old('reason_codes', $adjustment->reason_codes ?? []))
        ->map(fn ($value) => trim((string) $value))
        ->filter()
        ->values()
        ->all();
    $selectedServiceIsSpecificArea = $selectedServiceId !== 0
        && $selectedServiceId === (int) ($specificAreaServiceId ?? 0);
    $selectedCorrectedOptionIds = $selectedServiceIsSpecificArea
        ? collect(old('corrected_option_ids', $defaultCorrectedOptionIds))
            ->map(fn ($value) => (int) $value)
            ->filter(fn ($value) => $value > 0 && $selectedServiceOptionsById->has($value))
            ->unique()
            ->values()
            ->all()
        : collect([(int) old('corrected_option_id', $defaultCorrectedOptionIds[0] ?? 0)])
            ->filter(fn ($value) => $value > 0 && $selectedServiceOptionsById->has($value))
            ->values()
            ->all();

    $sumOptionPrices = function (array $optionIds, $optionsById) {
        return round(collect($optionIds)->sum(fn ($id) => (float) ($optionsById->get((int) $id)->price_addition ?? 0)), 2);
    };

    $formatOptionLabels = function (array $optionIds, $optionsById) {
        return collect($optionIds)
            ->map(fn ($id) => trim((string) ($optionsById->get((int) $id)->label ?? '')))
            ->filter()
            ->implode(', ');
    };

    $originalServiceName = trim((string) ($servicesById->get($currentServiceId)->name ?? $serviceName));
    $selectedServiceName = trim((string) ($selectedService->name ?? $originalServiceName));
    $originalSelectionLabel = trim($originalServiceName . ($originalOptionSummary ? ' / ' . $originalOptionSummary : ''));
    $selectedCorrectedLabel = trim($selectedServiceName . ($formatOptionLabels($selectedCorrectedOptionIds, $selectedServiceOptionsById) ? ' / ' . $formatOptionLabels($selectedCorrectedOptionIds, $selectedServiceOptionsById) : ''));
    $originalPrice = round((float) $amount, 2);
    $originalOptionTotal = $sumOptionPrices($originalOptionIds, $currentServiceOptionsById);
    $selectedCorrectedOptionTotal = $sumOptionPrices($selectedCorrectedOptionIds, $selectedServiceOptionsById);
    $originalServiceBasePrice = round((float) ($servicesById->get($currentServiceId)->base_price ?? ($originalPrice - $originalOptionTotal)), 2);
    if ($originalServiceBasePrice < 0) {
        $originalServiceBasePrice = 0.0;
    }
    $selectedServiceBasePrice = round((float) ($selectedService->base_price ?? $originalServiceBasePrice), 2);
    if ($selectedServiceBasePrice <= 0 && $selectedServiceId === $currentServiceId) {
        $selectedServiceBasePrice = $originalServiceBasePrice;
    }
    $automaticReasonFee = in_array('heavy_soiling', $selectedReasonCodes, true)
        ? round(max(300, $originalPrice * 0.10), 2)
        : 0.0;
    $previewProposedTotal = round($selectedServiceBasePrice + $selectedCorrectedOptionTotal + $automaticReasonFee, 2);
    $previewAdditionalFee = round($previewProposedTotal - $originalPrice, 2);
    $adjustmentMaxIncreasePercent = 35.0;
    $previewIncreasePercent = $originalPrice > 0
        ? round((($previewProposedTotal - $originalPrice) / $originalPrice) * 100, 2)
        : 0.0;
    $otherReasonValue = old('other_reason', $adjustment->other_reason ?? '');
    $providerNoteValue = old('provider_note', $adjustment->provider_note ?? '');
    $showOtherReason = in_array('other', $selectedReasonCodes, true);
    $serviceCatalogPayload = $adjustmentServices->map(function ($service) use ($serviceOptionsByService, $specificAreaServiceId) {
        $serviceId = (int) ($service->id ?? 0);

        return [
            'id' => $serviceId,
            'name' => trim((string) ($service->name ?? '')),
            'base_price' => (float) ($service->base_price ?? 0),
            'mode' => $serviceId === (int) ($specificAreaServiceId ?? 0) ? 'multi' : 'single',
            'options' => collect($serviceOptionsByService->get($serviceId, collect()))
                ->map(fn ($option) => [
                    'id' => (int) ($option->id ?? 0),
                    'label' => trim((string) ($option->label ?? '')),
                    'price' => (float) ($option->price_addition ?? 0),
                ])
                ->values()
                ->all(),
        ];
    })->values();
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
.btnx:disabled{
    opacity: .56;
    cursor: not-allowed;
    filter: none;
    box-shadow: none;
}

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

.timeline-list{
    display:grid;
    gap:10px;
    margin-top:12px;
}

.timeline-item{
    border:1px solid rgba(255,255,255,.08);
    background:rgba(2,6,23,.20);
    border-radius:14px;
    padding:12px 14px;
}

.timeline-top{
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:10px;
    flex-wrap:wrap;
}

.timeline-title{
    color:#fff;
    font-weight:900;
}

.timeline-meta{
    display:flex;
    align-items:center;
    gap:8px;
    flex-wrap:wrap;
    margin-top:8px;
    color:rgba(255,255,255,.72);
    font-size:.84rem;
}

.timeline-actor{
    display:inline-flex;
    align-items:center;
    padding:.28rem .62rem;
    border-radius:999px;
    border:1px solid rgba(56,189,248,.18);
    background:rgba(56,189,248,.10);
    color:#bae6fd;
    font-weight:800;
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
.field-help.is-highlighted{
    color: #7dd3fc;
    font-weight: 800;
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
                                    Change: PHP {{ $adjustment->difference_display }}<br>
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

                        @if(($adjustment->status_key ?? '') === 'pending_adjustment_approval')
                            <form method="POST" action="{{ route('provider.bookings.adjustment.note', $booking->reference_code) }}" class="adjustment-form">
                                @csrf
                                <div class="field-block">
                                    <div class="field-label">Reply to Customer</div>
                                    <textarea
                                        class="field-textarea"
                                        name="provider_adjustment_note"
                                        placeholder="Add a note to explain the onsite issue or answer the customer."
                                    >{{ old('provider_adjustment_note') }}</textarea>
                                    <div class="field-help">Use this when you need to clarify the mismatch before the customer accepts or rejects it.</div>
                                    @error('provider_adjustment_note')
                                        <div class="field-error">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div>
                                    <button type="submit" class="btnx ghost">Send Note to Customer</button>
                                </div>
                            </form>
                        @endif

                        @if(!empty($adjustmentLogs) && $adjustmentLogs->isNotEmpty())
                            <div class="timeline-list">
                                @foreach($adjustmentLogs as $log)
                                    <div class="timeline-item">
                                        <div class="timeline-top">
                                            <div class="timeline-title">{{ $log->action_label }}</div>
                                            @if(!empty($log->created_at_label))
                                                <div class="adjustment-copy" style="margin-top:0;">{{ $log->created_at_label }}</div>
                                            @endif
                                        </div>
                                        <div class="timeline-meta">
                                            <span class="timeline-actor">{{ $log->actor_label }}</span>
                                            @if(!empty($log->detail))
                                                <span>{{ $log->detail }}</span>
                                            @endif
                                        </div>
                                        @if(!empty($log->note))
                                            <div class="adjustment-copy"><strong>Note:</strong> {{ $log->note }}</div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
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
                                <div class="k">Mismatch Flow</div>
                                <div class="adjustment-copy">Use this only when the actual onsite service, size, components, or cleaning condition does not match what the customer booked.</div>
                                <div class="adjustment-copy">Pick the real onsite service first, then choose the matching size or components for that service. If heavy soiling applies, the system adds that condition fee automatically.</div>
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
                                    <div class="field-label">Actual Onsite Service</div>
                                    <select
                                        class="field-input"
                                        id="corrected_service_id"
                                        name="corrected_service_id"
                                        data-service-select
                                    >
                                        <option value="">Choose actual service onsite</option>
                                        @foreach($adjustmentServices as $service)
                                            @php($adjustmentServiceId = (int) ($service->id ?? 0))
                                            <option
                                                value="{{ $adjustmentServiceId }}"
                                                data-service-id="{{ $adjustmentServiceId }}"
                                                data-service-name="{{ $service->name }}"
                                                data-service-base="{{ number_format((float) ($service->base_price ?? 0), 2, '.', '') }}"
                                                @selected($selectedServiceId === $adjustmentServiceId)
                                            >
                                                {{ $service->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <div class="field-help">First choose the real service needed onsite.</div>
                                    @error('corrected_service_id')
                                        <div class="field-error">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="field-block full">
                                    <div class="field-label">Actual Onsite Size / Components</div>
                                    <div class="field-help" data-scope-help>Then choose the real size or components for that service so the system can calculate the updated amount.</div>
                                    <div class="field-block" data-single-scope-wrap @if($selectedServiceIsSpecificArea) hidden @endif>
                                        <select class="field-input" id="corrected_option_id" name="corrected_option_id" data-option-select>
                                            <option value="">Choose corrected size or scope</option>
                                            @foreach($selectedServiceOptions as $option)
                                                @if($selectedServiceIsSpecificArea)
                                                    @continue
                                                @endif
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
                                    </div>

                                    <div class="choice-grid" data-multi-scope-wrap @if(!$selectedServiceIsSpecificArea) hidden @endif>
                                        @if($selectedServiceIsSpecificArea)
                                            @foreach($selectedServiceOptions as $option)
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
                                        @endif
                                    </div>
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
                                    <input class="field-file" id="evidence" name="evidence" type="file" accept=".jpg,.jpeg,.png,.webp,.pdf" required>
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
                                    data-original-service-id="{{ $currentServiceId }}"
                                    data-selected-service-id="{{ $selectedServiceId }}"
                                    data-original-service-base-price="{{ number_format($originalServiceBasePrice, 2, '.', '') }}"
                                    data-original-option-total="{{ number_format($originalOptionTotal, 2, '.', '') }}"
                                    data-original-selection="{{ $originalSelectionLabel }}"
                                    data-original-option-labels='@json(array_values($originalOptionLabels))'
                                    data-original-option-ids='@json(array_values($originalOptionIds))'
                                    data-selected-option-ids='@json(array_values($selectedCorrectedOptionIds))'
                                    data-service-catalog='@json($serviceCatalogPayload)'
                                    data-specific-area-service-id="{{ (int) ($specificAreaServiceId ?? 0) }}"
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
                                        The system calculates the updated total from the selected service, the matching size or components, and any automatic condition fee.
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
    const serviceSelect = form.querySelector('[data-service-select]');
    const singleScopeWrap = form.querySelector('[data-single-scope-wrap]');
    const multiScopeWrap = form.querySelector('[data-multi-scope-wrap]');
    const optionSelect = form.querySelector('[data-option-select]');
    const reasonCheckboxes = Array.from(form.querySelectorAll('[data-reason-code]'));

    if (!preview || !selectionText || !autoFeeText || !additionalFeeText || !proposedTotalText || !previewNote || !warningEl || !submitBtn || !serviceSelect || !singleScopeWrap || !multiScopeWrap || !optionSelect) {
        return;
    }

    const originalPrice = Number(preview.dataset.originalPrice || 0);
    const originalServiceId = Number(preview.dataset.originalServiceId || 0);
    const originalServiceBasePrice = Number(preview.dataset.originalServiceBasePrice || 0);
    const maxIncreasePercent = Number(preview.dataset.maxIncrease || 35);
    const originalSelection = preview.dataset.originalSelection || 'Original selection';
    const originalOptionIds = JSON.parse(preview.dataset.originalOptionIds || '[]')
        .map((value) => Number(value))
        .filter((value) => value > 0);
    const originalOptionLabels = JSON.parse(preview.dataset.originalOptionLabels || '[]')
        .map((value) => String(value || '').trim())
        .filter(Boolean);
    const initialSelectedServiceId = Number(preview.dataset.selectedServiceId || originalServiceId || 0);
    const initialSelectedOptionIds = JSON.parse(preview.dataset.selectedOptionIds || '[]')
        .map((value) => Number(value))
        .filter((value) => value > 0);
    const serviceCatalog = new Map(
        JSON.parse(preview.dataset.serviceCatalog || '[]')
            .map((service) => {
                const serviceId = Number(service?.id || 0);
                const options = Array.isArray(service?.options)
                    ? service.options.map((option) => ({
                        id: Number(option?.id || 0),
                        label: String(option?.label || '').trim(),
                        price: Number(option?.price || 0),
                    })).filter((option) => option.id > 0)
                    : [];

                return [serviceId, {
                    id: serviceId,
                    name: String(service?.name || '').trim(),
                    basePrice: Number(service?.base_price || 0),
                    mode: service?.mode === 'multi' ? 'multi' : 'single',
                    options,
                }];
            })
            .filter(([serviceId]) => serviceId > 0)
    );

    let optionCheckboxes = [];
    let optionChoiceCards = [];
    let activeServiceId = 0;
    const selectedOptionIdsByService = new Map();

    const formatCurrency = (value) => `PHP ${Number(value || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
    const escapeHtml = (value) => String(value ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');

    const selectedReasons = () => reasonCheckboxes
        .filter((checkbox) => checkbox.checked)
        .map((checkbox) => checkbox.dataset.reasonCode || checkbox.value);

    const getService = (serviceId) => serviceCatalog.get(Number(serviceId || 0)) || null;

    const normalizeOptionIds = (service, ids) => {
        if (!service) {
            return [];
        }

        const validIds = new Set(service.options.map((option) => Number(option.id || 0)));
        let normalized = [...new Set((ids || [])
            .map((value) => Number(value))
            .filter((value) => value > 0 && validIds.has(value)))];

        if (service.mode !== 'multi') {
            normalized = normalized.slice(0, 1);
        }

        return normalized;
    };

    const optionLabelSummary = (service, optionIds) => {
        if (!service) {
            return '';
        }

        const optionsById = new Map(service.options.map((option) => [Number(option.id || 0), option]));

        return optionIds
            .map((optionId) => optionsById.get(Number(optionId || 0))?.label || '')
            .filter(Boolean)
            .join(', ');
    };

    const defaultOptionIdsForService = (service) => {
        if (!service) {
            return [];
        }

        const saved = selectedOptionIdsByService.get(service.id);
        if (saved) {
            return normalizeOptionIds(service, saved);
        }

        if (service.id === initialSelectedServiceId && initialSelectedOptionIds.length) {
            return normalizeOptionIds(service, initialSelectedOptionIds);
        }

        if (service.id === originalServiceId) {
            return normalizeOptionIds(service, originalOptionIds);
        }

        const matchingIds = service.options
            .filter((option) => originalOptionLabels.includes(option.label))
            .map((option) => option.id);

        return normalizeOptionIds(service, matchingIds);
    };

    const readSelectedOptionIds = (service = getService(activeServiceId)) => {
        if (!service) {
            return [];
        }

        if (service.mode === 'multi') {
            return normalizeOptionIds(service, optionCheckboxes
                .filter((checkbox) => checkbox.checked)
                .map((checkbox) => Number(checkbox.dataset.optionId || checkbox.value || 0)));
        }

        return normalizeOptionIds(service, [Number(optionSelect.value || 0)]);
    };

    const persistSelectedOptionIds = (serviceId = activeServiceId) => {
        const service = getService(serviceId);
        if (!service) {
            return;
        }

        selectedOptionIdsByService.set(serviceId, readSelectedOptionIds(service));
    };

    const selectedOptionState = () => {
        const service = getService(activeServiceId);

        if (!service) {
            return {
                serviceId: 0,
                serviceName: '',
                ids: [],
                total: 0,
                label: 'Choose actual service onsite',
                scopeChanged: false,
            };
        }

        const ids = readSelectedOptionIds(service);
        const optionSummary = optionLabelSummary(service, ids);
        const scopeLabel = [service.name, optionSummary].filter(Boolean).join(' / ');
        const total = ids.reduce((sum, optionId) => {
            const option = service.options.find((candidate) => candidate.id === optionId);
            return sum + Number(option?.price || 0);
        }, 0);

        return {
            serviceId: service.id,
            serviceName: service.name,
            ids,
            total,
            label: scopeLabel || service.name || originalSelection,
            scopeChanged: service.id !== originalServiceId || !matchesOriginalSelection(ids),
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

    const renderOptionsForService = (serviceId) => {
        const service = getService(serviceId);

        optionCheckboxes = [];
        optionChoiceCards = [];

        if (!service) {
            optionSelect.innerHTML = '<option value="">Choose corrected size or scope</option>';
            singleScopeWrap.hidden = false;
            multiScopeWrap.hidden = true;
            multiScopeWrap.innerHTML = '';
            return;
        }

        const selectedIds = defaultOptionIdsForService(service);
        selectedOptionIdsByService.set(service.id, selectedIds);

        if (service.mode === 'multi') {
            singleScopeWrap.hidden = true;
            multiScopeWrap.hidden = false;
            optionSelect.innerHTML = '<option value="">Choose corrected size or scope</option>';
            multiScopeWrap.innerHTML = service.options.length
                ? service.options.map((option) => {
                    const checked = selectedIds.includes(option.id) ? ' checked' : '';

                    return `
                        <label class="choice-card option-choice${checked ? ' is-selected' : ''}" data-option-choice>
                            <input
                                type="checkbox"
                                name="corrected_option_ids[]"
                                value="${escapeHtml(option.id)}"
                                data-option-checkbox
                                data-option-id="${escapeHtml(option.id)}"
                                data-option-label="${escapeHtml(option.label)}"
                                data-option-price="${escapeHtml(option.price.toFixed(2))}"${checked}
                            >
                            <span class="option-choice-copy">
                                <span class="option-choice-title">${escapeHtml(option.label)}</span>
                                <span class="option-choice-price">+ ${escapeHtml(formatCurrency(option.price))}</span>
                            </span>
                        </label>
                    `;
                }).join('')
                : '<div class="field-help">No components are configured for this service right now.</div>';

            optionCheckboxes = Array.from(multiScopeWrap.querySelectorAll('[data-option-checkbox]'));
            optionChoiceCards = Array.from(multiScopeWrap.querySelectorAll('[data-option-choice]'));

            optionCheckboxes.forEach((checkbox) => {
                checkbox.addEventListener('change', () => {
                    persistSelectedOptionIds(service.id);
                    syncChoiceCards();
                    syncPreview();
                });
            });

            syncChoiceCards();
            return;
        }

        singleScopeWrap.hidden = false;
        multiScopeWrap.hidden = true;
        multiScopeWrap.innerHTML = '';
        optionSelect.innerHTML = [
            '<option value="">Choose corrected size or scope</option>',
            ...service.options.map((option) => `
                <option
                    value="${escapeHtml(option.id)}"
                    data-option-id="${escapeHtml(option.id)}"
                    data-option-label="${escapeHtml(option.label)}"
                    data-option-price="${escapeHtml(option.price.toFixed(2))}"
                >
                    ${escapeHtml(option.label)} (+ ${escapeHtml(formatCurrency(option.price))})
                </option>
            `),
        ].join('');
        optionSelect.value = selectedIds[0] ? String(selectedIds[0]) : '';
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
        const autoConditionFee = reasons.includes('heavy_soiling')
            ? Math.max(300, originalPrice * 0.10)
            : 0;
        const serviceBasePrice = optionState.serviceId === originalServiceId && originalServiceBasePrice > 0
            ? originalServiceBasePrice
            : Number(getService(optionState.serviceId)?.basePrice || 0);
        const proposedTotal = Number((serviceBasePrice + optionState.total + autoConditionFee).toFixed(2));
        const additionalFee = Number((proposedTotal - originalPrice).toFixed(2));
        const increasePercent = originalPrice > 0
            ? Number((((proposedTotal - originalPrice) / originalPrice) * 100).toFixed(2))
            : 0;
        const selectionChanged = optionState.scopeChanged;

        if (scopeHelp) {
            if (!optionState.serviceId) {
                scopeHelp.textContent = 'Choose the actual onsite service first, then select the matching size or components.';
            } else if (!optionState.ids.length) {
                scopeHelp.textContent = 'Choose the matching size or components for the selected service.';
            } else if (selectionChanged) {
                scopeHelp.textContent = 'The updated total is based on the selected service and components below.';
            } else {
                scopeHelp.textContent = 'If the scope stayed the same, keep the original service and components selected here.';
            }

            scopeHelp.classList.toggle('is-highlighted', selectionChanged);
        }

        selectionText.textContent = optionState.label || originalSelection;
        autoFeeText.textContent = formatCurrency(autoConditionFee);
        additionalFeeText.textContent = formatCurrency(additionalFee);
        proposedTotalText.textContent = formatCurrency(proposedTotal);

        if (selectionChanged && autoConditionFee > 0) {
            previewNote.textContent = `The new total uses the selected service and components plus the automatic condition fee. Increase: ${increasePercent.toFixed(2)}%.`;
        } else if (selectionChanged) {
            previewNote.textContent = `The new total uses the selected service and components. Increase: ${increasePercent.toFixed(2)}%.`;
        } else if (autoConditionFee > 0) {
            previewNote.textContent = `The original scope stays the same, and the system adds the automatic condition fee. Increase: ${increasePercent.toFixed(2)}%.`;
        } else {
            previewNote.textContent = 'The original scope and total stay the same unless you choose a different service or components, or add heavy soiling.';
        }

        let warning = '';
        if (!optionState.serviceId) {
            warning = 'Choose the actual onsite service before sending the mismatch request.';
        } else if (!optionState.ids.length) {
            warning = 'Choose the matching size or components for the selected service.';
        } else if (proposedTotal < originalPrice) {
            warning = 'The corrected selection cannot reduce the original booking total.';
        } else if (increasePercent > maxIncreasePercent) {
            warning = `This update exceeds the ${maxIncreasePercent.toFixed(0)}% safety limit.`;
        }

        warningEl.hidden = warning === '';
        warningEl.textContent = warning;
        submitBtn.disabled = warning !== '';
    };

    optionSelect.addEventListener('change', () => {
        persistSelectedOptionIds(activeServiceId);
        syncPreview();
    });

    reasonCheckboxes.forEach((checkbox) => {
        checkbox.addEventListener('change', () => {
            syncOtherReason();
            syncPreview();
        });
    });

    serviceSelect.addEventListener('change', () => {
        persistSelectedOptionIds(activeServiceId);
        activeServiceId = Number(serviceSelect.value || 0);
        renderOptionsForService(activeServiceId);
        syncPreview();
    });

    activeServiceId = serviceCatalog.has(initialSelectedServiceId)
        ? initialSelectedServiceId
        : (serviceCatalog.has(originalServiceId) ? originalServiceId : Number(serviceSelect.value || 0));

    if (activeServiceId > 0) {
        serviceSelect.value = String(activeServiceId);
    }

    if (originalServiceId > 0) {
        selectedOptionIdsByService.set(originalServiceId, normalizeOptionIds(getService(originalServiceId), originalOptionIds));
    }

    if (activeServiceId > 0 && initialSelectedOptionIds.length) {
        selectedOptionIdsByService.set(activeServiceId, normalizeOptionIds(getService(activeServiceId), initialSelectedOptionIds));
    }

    renderOptionsForService(activeServiceId);
    syncOtherReason();
    syncPreview();

    form.addEventListener('submit', (event) => {
        if (submitBtn.disabled) {
            event.preventDefault();
            warningEl.hidden = false;
            warningEl.scrollIntoView({ behavior: 'smooth', block: 'center' });
            return;
        }

        const reasons = selectedReasons();
        const optionState = selectedOptionState();
        const serviceBasePrice = optionState.serviceId === originalServiceId && originalServiceBasePrice > 0
            ? originalServiceBasePrice
            : Number(getService(optionState.serviceId)?.basePrice || 0);
        const autoConditionFee = reasons.includes('heavy_soiling')
            ? Math.max(300, originalPrice * 0.10)
            : 0;
        const proposedTotal = Number((serviceBasePrice + optionState.total + autoConditionFee).toFixed(2));
        const confirmMessage = [
            'Send this onsite mismatch request to the customer?',
            '',
            `Booked scope: ${originalSelection}`,
            `Actual scope: ${optionState.label || originalSelection}`,
            `New total: ${formatCurrency(proposedTotal)}`,
            '',
            'You are confirming that the onsite scope above is accurate.',
            'The booking total will not change until the customer accepts this adjustment.',
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
