@extends('provider.layouts.app')

@section('title', 'My Ratings')

@section('content')

@php
    use Carbon\Carbon;

    $avg = (float) ($ratingSummary->avg ?? 0);
    $count = (int) ($ratingSummary->count ?? 0);
    $fmtAvg = $count > 0 ? number_format($avg, 1) : '0.0';

    $breakdown = collect($breakdown ?? []);
    $reviews = collect($reviews ?? []);
    $fiveStarCount = (int) optional($breakdown->firstWhere('star', 5))->cnt;
    $fiveStarShare = $count > 0 ? (int) round(($fiveStarCount / $count) * 100) : 0;

    $percent = function ($value) use ($count) {
        if ($count <= 0) {
            return 0;
        }

        return (int) round(((int) $value / $count) * 100);
    };

    $ratingWord = function ($value) {
        return match ((int) round($value)) {
            5 => 'Excellent',
            4 => 'Very good',
            3 => 'Good',
            2 => 'Needs work',
            1 => 'Poor',
            default => 'No ratings yet',
        };
    };
@endphp

<style>
:root{
    --rt-bg:#020617;
    --rt-card:#071225;
    --rt-card-soft:#0b1830;
    --rt-border:rgba(255,255,255,.08);
    --rt-text:rgba(255,255,255,.95);
    --rt-muted:rgba(255,255,255,.58);
    --rt-accent:#38bdf8;
    --rt-success:#22c55e;
    --rt-warn:#fbbf24;
}

.ratings-page{
    max-width: 1080px;
    margin: 0 auto;
    color: var(--rt-text);
}

.ratings-stack{
    display:flex;
    flex-direction:column;
    gap:1rem;
}

.ratings-shell,
.ratings-card,
.review-card,
.ratings-empty{
    background: linear-gradient(180deg, rgba(7,18,37,.96), rgba(2,6,23,.98));
    border:1px solid var(--rt-border);
    border-radius:22px;
    box-shadow: 0 20px 40px rgba(0,0,0,.28);
}

.ratings-shell{
    padding:1.2rem;
}

.ratings-head{
    display:flex;
    align-items:flex-start;
    justify-content:space-between;
    gap:1rem;
    flex-wrap:wrap;
}

.ratings-title{
    margin:0;
    font-size:1.2rem;
    font-weight:900;
    letter-spacing:-.02em;
}

.ratings-subtitle{
    margin:.35rem 0 0;
    color:var(--rt-muted);
    font-size:.9rem;
    line-height:1.55;
}

.ratings-chip{
    display:inline-flex;
    align-items:center;
    gap:.45rem;
    padding:.55rem .85rem;
    border-radius:999px;
    background: rgba(56,189,248,.1);
    border:1px solid rgba(56,189,248,.2);
    color:rgba(255,255,255,.94);
    font-size:.84rem;
    font-weight:800;
    white-space:nowrap;
}

.summary-grid{
    display:grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap:.75rem;
    margin-top:1rem;
}

.summary-card{
    padding:1rem;
    border-radius:18px;
    background: rgba(255,255,255,.03);
    border:1px solid rgba(255,255,255,.06);
}

.summary-label{
    color:var(--rt-muted);
    font-size:.76rem;
    font-weight:700;
    letter-spacing:.08em;
    text-transform:uppercase;
}

.summary-value{
    margin-top:.45rem;
    font-size:1.8rem;
    font-weight:900;
    line-height:1;
}

.summary-note{
    margin-top:.35rem;
    color:var(--rt-muted);
    font-size:.82rem;
    line-height:1.45;
}

.summary-stars{
    display:inline-flex;
    align-items:center;
    gap:.16rem;
    margin-top:.55rem;
}

.star{
    width:18px;
    height:18px;
    display:block;
    fill:#334155;
}

.star.on{ fill:#fbbf24; }

.ratings-card{
    padding:1rem;
}

.card-title{
    margin:0;
    font-size:1rem;
    font-weight:900;
}

.card-subtitle{
    margin:.35rem 0 0;
    color:var(--rt-muted);
    font-size:.84rem;
    line-height:1.45;
}

.breakdown-list{
    display:flex;
    flex-direction:column;
    gap:.75rem;
    margin-top:1rem;
}

.breakdown-row{
    display:grid;
    grid-template-columns: 68px 1fr 46px;
    gap:.65rem;
    align-items:center;
}

.breakdown-label,
.breakdown-count{
    font-size:.82rem;
    font-weight:800;
    color:rgba(255,255,255,.9);
}

.breakdown-count{
    text-align:right;
    color:var(--rt-muted);
}

.breakdown-bar{
    height:10px;
    border-radius:999px;
    background: rgba(255,255,255,.06);
    overflow:hidden;
}

.breakdown-bar span{
    display:block;
    height:100%;
    border-radius:999px;
    background: linear-gradient(90deg, rgba(56,189,248,.9), rgba(14,165,233,.75));
}

.quick-notes{
    display:grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap:.75rem;
}

.note-box{
    padding:.9rem 1rem;
    border-radius:16px;
    background: rgba(255,255,255,.03);
    border:1px solid rgba(255,255,255,.06);
}

.note-label{
    color:var(--rt-muted);
    font-size:.75rem;
    font-weight:700;
    letter-spacing:.08em;
    text-transform:uppercase;
}

.note-value{
    margin-top:.35rem;
    font-size:.92rem;
    font-weight:800;
    line-height:1.5;
}

.reviews-list{
    display:flex;
    flex-direction:column;
    gap:1rem;
}

.review-card{
    padding:1rem;
}

.review-top{
    display:grid;
    grid-template-columns: minmax(0, 1fr) auto;
    gap:.85rem;
    align-items:start;
}

.reviewer{
    display:flex;
    align-items:center;
    gap:.85rem;
    min-width:0;
}

.avatar{
    width:48px;
    height:48px;
    border-radius:999px;
    border:1px solid rgba(255,255,255,.1);
    background: rgba(255,255,255,.04);
    overflow:hidden;
    flex:0 0 48px;
    display:flex;
    align-items:center;
    justify-content:center;
}

.avatar img{
    width:100%;
    height:100%;
    object-fit:cover;
    display:block;
}

.reviewer-name{
    font-size:.98rem;
    font-weight:900;
    line-height:1.3;
}

.reviewer-meta{
    margin-top:.2rem;
    color:var(--rt-muted);
    font-size:.82rem;
    line-height:1.45;
}

.rating-badge{
    display:inline-flex;
    align-items:center;
    gap:.55rem;
    padding:.45rem .75rem;
    border-radius:999px;
    border:1px solid rgba(255,255,255,.08);
    background: rgba(255,255,255,.03);
    white-space:nowrap;
}

.rating-badge .stars{
    display:inline-flex;
    align-items:center;
    gap:.14rem;
}

.rating-badge .star{
    width:15px;
    height:15px;
}

.rating-badge strong{
    font-size:.82rem;
    font-weight:900;
}

.booking-pills{
    display:flex;
    flex-wrap:wrap;
    gap:.55rem;
    margin-top:.9rem;
}

.pill{
    display:inline-flex;
    align-items:center;
    gap:.38rem;
    padding:.42rem .7rem;
    border-radius:999px;
    border:1px solid rgba(255,255,255,.08);
    background: rgba(255,255,255,.03);
    color:rgba(255,255,255,.88);
    font-size:.8rem;
    font-weight:700;
}

.pill.service{
    border-color: rgba(56,189,248,.18);
    background: rgba(56,189,248,.08);
}

.review-comment{
    margin-top:.95rem;
    padding:.95rem 1rem;
    border-radius:16px;
    border:1px solid rgba(255,255,255,.06);
    background: rgba(255,255,255,.03);
    color:rgba(255,255,255,.88);
    line-height:1.6;
}

.review-comment.empty{
    color:var(--rt-muted);
}

.ratings-empty{
    padding:1.5rem;
    text-align:center;
}

.empty-icon{
    width:56px;
    height:56px;
    margin:0 auto .85rem;
    display:inline-flex;
    align-items:center;
    justify-content:center;
    border-radius:18px;
    background: rgba(56,189,248,.1);
    color:var(--rt-accent);
    font-size:1.35rem;
}

.empty-title{
    font-size:1.02rem;
    font-weight:900;
}

.empty-copy{
    margin-top:.35rem;
    color:var(--rt-muted);
    font-size:.9rem;
    line-height:1.6;
}

@media (max-width: 991.98px){
    .summary-grid,
    .quick-notes{
        grid-template-columns: 1fr;
    }
}

@media (max-width: 767.98px){
    .summary-grid{
        grid-template-columns: 1fr;
    }

    .review-top{
        grid-template-columns: 1fr;
    }
}
</style>

<div class="ratings-page">
    <div class="ratings-stack">

        <section class="ratings-shell">
            <div class="ratings-head">
                <div>
                    <h1 class="ratings-title">Ratings and Reviews</h1>
                </div>

                <div class="ratings-chip">
                    <i class="bi bi-chat-square-quote"></i>
                    <span>{{ $count }} review{{ $count === 1 ? '' : 's' }}</span>
                </div>
            </div>

            <div class="summary-grid">
                <div class="summary-card">
                    <div class="summary-label">Average rating</div>
                    <div class="summary-value">{{ $fmtAvg }}</div>
                    <div class="summary-stars" aria-hidden="true">
                        @for($i = 1; $i <= 5; $i++)
                            <svg class="star {{ $i <= (int) round($avg) ? 'on' : '' }}" viewBox="0 0 24 24">
                                <path d="M12 17.27 18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/>
                            </svg>
                        @endfor
                    </div>
                    <div class="summary-note">{{ $ratingWord($avg) }}</div>
                </div>

                <div class="summary-card">
                    <div class="summary-label">Total reviews</div>
                    <div class="summary-value">{{ $count }}</div>
                    <div class="summary-note">All submitted ratings from customers.</div>
                </div>

                <div class="summary-card">
                    <div class="summary-label">5-star share</div>
                    <div class="summary-value">{{ $fiveStarShare }}%</div>
                    <div class="summary-note">{{ $fiveStarCount }} five-star review{{ $fiveStarCount === 1 ? '' : 's' }}.</div>
                </div>
            </div>
        </section>

        <section class="ratings-card">
            <h2 class="card-title">Rating breakdown</h2>

            <div class="breakdown-list">
                @foreach($breakdown as $row)
                    @php $rowPercent = $percent($row->cnt ?? 0); @endphp
                    <div class="breakdown-row">
                        <div class="breakdown-label">{{ $row->star }} star</div>
                        <div class="breakdown-bar">
                            <span style="width: {{ $rowPercent }}%;"></span>
                        </div>
                        <div class="breakdown-count">{{ (int) ($row->cnt ?? 0) }}</div>
                    </div>
                @endforeach
            </div>
        </section>

        <section class="ratings-card">
            <h2 class="card-title">Quick notes</h2>

            <div class="quick-notes" style="margin-top:1rem;">
                <div class="note-box">
                    <div class="note-label">Current standing</div>
                    <div class="note-value">{{ $count > 0 ? $ratingWord($avg) . ' at ' . $fmtAvg . ' / 5' : 'No customer ratings yet' }}</div>
                </div>

                <div class="note-box">
                    <div class="note-label">Strongest area</div>
                    <div class="note-value">{{ $fiveStarCount > 0 ? $fiveStarCount . ' customers left a five-star review.' : 'No five-star reviews yet.' }}</div>
                </div>

                <div class="note-box">
                    <div class="note-label">What this page shows</div>
                    <div class="note-value">Only submitted customer ratings with scores from 1 to 5 are included here.</div>
                </div>
            </div>
        </section>

        @if($reviews->isEmpty())
            <section class="ratings-empty">
                <div class="empty-icon">
                    <i class="bi bi-stars"></i>
                </div>
                <div class="empty-title">No reviews yet</div>
                <div class="empty-copy">Once customers submit feedback, their ratings and comments will show up here.</div>
            </section>
        @else
            <section class="reviews-list">
                @foreach($reviews as $review)
                    @php
                        $customerImage = $review->customer_profile_image ?? '';
                        $reviewerAvatar = asset('images/avatar-placeholder.svg');

                        if (!empty($customerImage)) {
                            $reviewerAvatar = route('customer.image.public', ['filename' => basename($customerImage)]) . '?v=' . time();
                        }

                        $dateLabel = !empty($review->created_at)
                            ? Carbon::parse($review->created_at)->format('M d, Y h:i A')
                            : 'No date';

                        $bookingDateLabel = !empty($review->booking_date)
                            ? Carbon::parse($review->booking_date)->format('M d, Y')
                            : null;

                        $serviceName = trim((string) ($review->service_name ?? 'Service'));
                        $optionName = trim((string) ($review->option_name ?? ''));
                        $score = (int) ($review->rating ?? 0);
                    @endphp

                    <article class="review-card">
                        <div class="review-top">
                            <div class="reviewer">
                                <div class="avatar">
                                    <img
                                        src="{{ $reviewerAvatar }}"
                                        alt="Customer avatar"
                                        onerror="this.onerror=null;this.src='{{ asset('images/avatar-placeholder.svg') }}';"
                                    >
                                </div>

                                <div style="min-width:0;">
                                    <div class="reviewer-name">{{ $review->customer_name ?? 'Customer' }}</div>
                                    <div class="reviewer-meta">{{ $dateLabel }}</div>
                                </div>
                            </div>

                            <div class="rating-badge">
                                <div class="stars" aria-hidden="true">
                                    @for($i = 1; $i <= 5; $i++)
                                        <svg class="star {{ $i <= $score ? 'on' : '' }}" viewBox="0 0 24 24">
                                            <path d="M12 17.27 18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/>
                                        </svg>
                                    @endfor
                                </div>
                                <strong>{{ $score }}/5</strong>
                            </div>
                        </div>

                        <div class="booking-pills">
                            <div class="pill service">
                                <i class="bi bi-bucket"></i>
                                <span>{{ $serviceName }}</span>
                            </div>

                            @if($optionName !== '')
                                <div class="pill">
                                    <i class="bi bi-grid"></i>
                                    <span>{{ $optionName }}</span>
                                </div>
                            @endif

                            @if(!empty($review->reference_code))
                                <div class="pill">
                                    <i class="bi bi-hash"></i>
                                    <span>{{ $review->reference_code }}</span>
                                </div>
                            @endif

                            @if($bookingDateLabel)
                                <div class="pill">
                                    <i class="bi bi-calendar3"></i>
                                    <span>{{ $bookingDateLabel }}</span>
                                </div>
                            @endif
                        </div>

                        <div class="review-comment {{ trim((string) ($review->comment ?? '')) === '' ? 'empty' : '' }}">
                            {{ trim((string) ($review->comment ?? '')) !== '' ? $review->comment : 'No written feedback was left for this review.' }}
                        </div>
                    </article>
                @endforeach
            </section>
        @endif

    </div>
</div>

@endsection
