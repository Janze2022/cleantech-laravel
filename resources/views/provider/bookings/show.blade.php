@extends('provider.layouts.app')

@section('title', 'Booking Details')

@section('content')

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

    // =========================
    // RATINGS SUMMARY + BREAKDOWN (from reviews table)
    // =========================
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
    grid-template-columns: 1.15fr .85fr;
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
.btnx:hover{ filter: brightness(1.05); }

@media (max-width: 992px){
    .grid{ grid-template-columns: 1fr; }
}
@media (max-width: 576px){
    .container.page{ padding-left: 10px; padding-right: 10px; }
    .pill{ width:100%; justify-content:center; }
    .kvGrid{ grid-template-columns: 1fr; }
    .btnx{ width:100%; }
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

                {{-- RIGHT: RATINGS SNAPSHOT --}}
                <div class="card">
                    <div class="k">My Ratings Snapshot</div>
                    <div class="v big">{{ $fmtAvg }}</div>
                    <div class="starline">{{ $stars((int) round($avg)) }}</div>
                    <div class="small" style="margin-top:6px; color:var(--muted); font-weight:800;">
                        {{ $count > 0 ? 'Based on '.$count.' review(s)' : 'No reviews yet' }}
                    </div>

                    <div class="mt-3" style="margin-top:14px;">
                        <div class="k">Rating Breakdown</div>

                        @foreach($breakdown as $b)
                            @php
                                $cnt = (int)($b->cnt ?? 0);
                                $star = (int)($b->star ?? 0);
                                $p = $percent($cnt);
                            @endphp
                            <div class="breakRow">
                                <div class="small" style="min-width:72px;">{{ $star }} star</div>
                                <div class="bar"><span style="width: {{ $p }}%;"></span></div>
                                <div class="small" style="min-width:60px; text-align:right;">{{ $cnt }}</div>
                            </div>
                        @endforeach
                    </div>

                    <div class="small" style="margin-top:12px; color:var(--muted); font-weight:800;">
                        Tip: Being on time + clear communication helps keep ratings high.
                    </div>
                </div>

            </div>
        </div>

    </div>
</div>

@endsection
