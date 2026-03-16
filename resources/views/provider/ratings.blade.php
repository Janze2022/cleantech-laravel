@extends('provider.layouts.app')

@section('title', 'My Ratings')

@section('content')

@php
    $avg = (float)($ratingSummary->avg ?? 0);
    $count = (int)($ratingSummary->count ?? 0);

    $fmtAvg = $count > 0 ? number_format($avg, 1) : '0.0';

    $percent = function($n) use ($count){
        if ($count <= 0) return 0;
        return (int) round(($n / $count) * 100);
    };

    $stars = function($n){
        $n = (int)$n;
        $out = '';
        for($i=1;$i<=5;$i++){
            $out .= $i <= $n ? '★' : '☆';
        }
        return $out;
    };

    $avatar = function($name){
        $name = trim((string)$name);
        $parts = preg_split('/\s+/', $name);
        $a = strtoupper(substr($parts[0] ?? 'C', 0, 1));
        $b = strtoupper(substr($parts[1] ?? '', 0, 1));
        return $a . ($b ?: '');
    };
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
    --success:#22c55e;
    --warn:#facc15;
    --danger:#ef4444;
    --r:18px;
    --shadow:0 28px 70px rgba(0,0,0,.55);
}

.wrap{
    padding: 14px 0 22px;
}

.shell{
    background: radial-gradient(900px 320px at 20% 0%, rgba(56,189,248,.10), transparent 62%),
                radial-gradient(900px 320px at 85% 10%, rgba(34,197,94,.08), transparent 58%),
                linear-gradient(180deg, rgba(2,11,31,.92), rgba(2,6,23,.96));
    border: 1px solid var(--border);
    border-radius: 26px;
    box-shadow: var(--shadow);
    overflow:hidden;
}

.top{
    padding: 14px 14px;
    border-bottom: 1px solid rgba(255,255,255,.06);
    background: rgba(255,255,255,.015);
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

.pill{
    display:inline-flex;
    align-items:center;
    gap:.45rem;
    padding:.44rem .7rem;
    border-radius: 999px;
    border:1px solid rgba(255,255,255,.10);
    background: rgba(2,6,23,.35);
    color: rgba(255,255,255,.90);
    font-weight: 900;
    font-size: .82rem;
}

.content{
    padding: 14px;
}

.grid{
    display:grid;
    grid-template-columns: 1.1fr .9fr;
    gap: 12px;
}

.card{
    border: 1px solid var(--border);
    border-radius: 18px;
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

.list{
    margin-top: 12px;
    display:flex;
    flex-direction:column;
    gap: 10px;
}

.item{
    border: 1px solid rgba(255,255,255,.08);
    background: rgba(2,6,23,.22);
    border-radius: 18px;
    padding: 12px;
}

.rowTop{
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap: 10px;
}

.left{
    display:flex;
    align-items:center;
    gap: 10px;
    min-width: 0;
}

.avatar{
    width: 44px;
    height: 44px;
    border-radius: 999px;
    border: 1px solid rgba(255,255,255,.12);
    background: rgba(255,255,255,.04);
    overflow:hidden;
    display:flex;
    align-items:center;
    justify-content:center;
    font-weight: 950;
    color: rgba(255,255,255,.92);
    flex: 0 0 44px;
}
.avatar img{
    width:100%;
    height:100%;
    object-fit:cover;
    display:block;
}

.name{
    font-weight: 950;
    color: rgba(255,255,255,.94);
    white-space:nowrap;
    overflow:hidden;
    text-overflow:ellipsis;
    max-width: 220px;
}
.meta{
    margin-top: 2px;
    color: var(--muted);
    font-weight: 700;
    font-size: .82rem;
}

.ratingTag{
    display:inline-flex;
    align-items:center;
    gap: 8px;
    padding:.38rem .65rem;
    border-radius: 999px;
    border:1px solid rgba(255,255,255,.10);
    background: rgba(2,6,23,.35);
    color: rgba(255,255,255,.92);
    font-weight: 950;
    font-size: .82rem;
    white-space:nowrap;
}
.ratingTag .stars{
    color: rgba(245, 204, 21, .95);
    letter-spacing:.08em;
}

.comment{
    margin-top: 10px;
    color: rgba(255,255,255,.88);
    font-weight: 700;
    line-height: 1.45;
    word-break: break-word;
}

.muted{
    color: var(--muted);
}

@media (max-width: 992px){
    .grid{ grid-template-columns: 1fr; }
    .name{ max-width: 160px; }
}

@media (max-width: 576px){
    .top{ align-items:stretch; }
    .pill{ width:100%; justify-content:center; min-height:44px; }
    .rowTop{ flex-direction:column; align-items:flex-start; }
    .ratingTag{ align-self:flex-end; }
    .name{ max-width: 240px; }
}
</style>

<div class="container wrap">
    <div class="shell">

        <div class="top">
            <div>
                <h5 class="h1">My Ratings & Feedback</h5>
                <p class="sub">See who rated you, your current rating, and customer comments.</p>
            </div>

            <span class="pill">{{ $count }} review{{ $count === 1 ? '' : 's' }}</span>
        </div>

        <div class="content">

            <div class="grid">

                {{-- SUMMARY --}}
                <div class="card">
                    <div class="k">Current Rating</div>
                    <div class="v big">{{ $fmtAvg }}</div>
                    <div class="starline">{{ $stars((int) round($avg)) }}</div>
                    <div class="muted small mt-1">{{ $count > 0 ? 'Based on '.$count.' review(s)' : 'No reviews yet' }}</div>

                    <div class="mt-3">
                        <div class="k">Rating Breakdown</div>

                        @foreach($breakdown as $b)
                            @php $p = $percent($b->cnt); @endphp
                            <div class="breakRow">
                                <div class="small" style="min-width:72px;">{{ $b->star }} star</div>
                                <div class="bar"><span style="width: {{ $p }}%;"></span></div>
                                <div class="small" style="min-width:60px; text-align:right;">{{ $b->cnt }}</div>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- QUICK INFO --}}
                <div class="card">
                    <div class="k">Tips</div>
                    <div class="v" style="font-size:.95rem; font-weight:800; line-height:1.5;">
                        Keep your rating high by being on time, communicating clearly, and finishing the job professionally.
                        Ratings update automatically as customers submit reviews.
                    </div>
                    <div class="mt-3 muted small">
                        Only reviews with a rating (1–5) are included.
                    </div>
                </div>

            </div>

            {{-- LIST --}}
            <div class="list">
                @forelse($reviews as $r)
                    @php
                        $nm = $r->customer_name ?? 'Customer';
                        $dt = $r->created_at ? \Carbon\Carbon::parse($r->created_at)->format('M d, Y h:i A') : '';

                        $customerImage = $r->customer_profile_image ?? '';
                        $reviewerAvatar = 'https://ui-avatars.com/api/?name=' . urlencode($nm) . '&background=0f172a&color=ffffff&size=256';

                        if (!empty($customerImage) && file_exists(public_path('uploads/customers/' . $customerImage))) {
                            $reviewerAvatar = asset('uploads/customers/' . $customerImage) . '?v=' . time();
                        }
                    @endphp

                    <div class="item">
                        <div class="rowTop">
                            <div class="left">
                                <div class="avatar">
                                    <img
                                        src="{{ $reviewerAvatar }}"
                                        alt="Customer"
                                        onerror="this.onerror=null;this.src='https://ui-avatars.com/api/?name={{ urlencode($nm) }}&background=0f172a&color=ffffff&size=256';"
                                    >
                                </div>

                                <div style="min-width:0;">
                                    <div class="name">{{ $nm }}</div>
                                    <div class="meta">
                                        <span class="muted">{{ $dt }}</span>
                                        @if(!empty($r->reference_code))
                                            <span class="muted"> • Ref: {{ $r->reference_code }}</span>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="ratingTag">
                                <span class="stars">{{ $stars((int)($r->rating ?? 0)) }}</span>
                                <span>{{ (int)($r->rating ?? 0) }}/5</span>
                            </div>
                        </div>

                        @if(trim((string)($r->comment ?? '')) !== '')
                            <div class="comment">{{ $r->comment }}</div>
                        @else
                            <div class="comment muted">No written feedback.</div>
                        @endif
                    </div>
                @empty
                    <div class="card">
                        <div class="k">No Reviews Yet</div>
                        <div class="v">You don’t have any ratings at the moment.</div>
                    </div>
                @endforelse
            </div>

        </div>
    </div>
</div>

@endsection