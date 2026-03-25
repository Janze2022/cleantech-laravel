@extends('customer.layouts.app')

@section('title', 'Provider Profile')

@section('content')

@php
    use Carbon\Carbon;

    $avg = (float) data_get($ratingSummary, 'avg', 0);
    $count = (int) data_get($ratingSummary, 'count', 0);
    $avgText = $count > 0 ? number_format($avg, 1) : '0.0';
    $avgRounded = (int) round($avg);

    $distribution = [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0];
    foreach(($reviews ?? collect()) as $review){
        $score = (int) data_get($review, 'rating', 0);
        if ($score >= 1 && $score <= 5) {
            $distribution[$score]++;
        }
    }

    $providerAvatar = !empty($provider->profile_image)
        ? route('provider.image.public', ['filename' => basename($provider->profile_image)]) . '?v=' . time()
        : asset('images/avatar-placeholder.svg');
@endphp

<style>
:root{
    --pp-bg:#020617;
    --pp-card:#071225;
    --pp-card-soft:#0b1830;
    --pp-border:rgba(255,255,255,.08);
    --pp-text:rgba(255,255,255,.95);
    --pp-muted:rgba(255,255,255,.58);
    --pp-accent:#38bdf8;
    --pp-success:#22c55e;
    --pp-warn:#fbbf24;
}

.provider-profile-page{
    max-width: 1040px;
    margin: 0 auto;
    color: var(--pp-text);
}

.provider-profile-stack{
    display:flex;
    flex-direction:column;
    gap:1rem;
}

.profile-shell,
.section-card,
.review-card,
.empty-card{
    background: linear-gradient(180deg, rgba(7,18,37,.96), rgba(2,6,23,.98));
    border:1px solid var(--pp-border);
    border-radius:22px;
    box-shadow: 0 20px 40px rgba(0,0,0,.28);
}

.profile-shell{
    padding:1rem 1.1rem;
}

.top-actions{
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:.75rem;
    flex-wrap:wrap;
}

.back-link,
.ghost-action,
.book-action{
    display:inline-flex;
    align-items:center;
    justify-content:center;
    gap:.45rem;
    min-height:42px;
    padding:.68rem .95rem;
    border-radius:12px;
    text-decoration:none;
    font-weight:800;
}

.back-link,
.ghost-action{
    background: rgba(255,255,255,.03);
    border:1px solid rgba(255,255,255,.1);
    color: rgba(255,255,255,.92);
}

.book-action{
    background: rgba(34,197,94,.14);
    border:1px solid rgba(34,197,94,.28);
    color: rgba(255,255,255,.95);
}

.profile-hero{
    display:grid;
    grid-template-columns: 96px minmax(0, 1fr);
    gap:1.1rem;
    align-items:center;
    margin-top:.9rem;
}

.provider-avatar{
    width:96px;
    height:96px;
    border-radius:999px;
    object-fit:cover;
    border:2px solid rgba(255,255,255,.12);
    background: rgba(255,255,255,.04);
    box-shadow: 0 16px 34px rgba(0,0,0,.3);
}

.provider-name{
    margin:0;
    font-size:1.55rem;
    font-weight:900;
    line-height:1.1;
}

.provider-location{
    margin-top:.35rem;
    color:var(--pp-muted);
    font-size:.92rem;
    font-weight:700;
}

.hero-pills{
    display:flex;
    flex-wrap:wrap;
    gap:.55rem;
    margin-top:.85rem;
}

.pill{
    display:inline-flex;
    align-items:center;
    gap:.38rem;
    padding:.42rem .7rem;
    border-radius:999px;
    border:1px solid rgba(255,255,255,.08);
    background: rgba(255,255,255,.03);
    color: rgba(255,255,255,.9);
    font-size:.8rem;
    font-weight:800;
}

.pill.rating{
    border-color: rgba(251,191,36,.18);
    background: rgba(251,191,36,.1);
}

.pill.verified{
    border-color: rgba(34,197,94,.18);
    background: rgba(34,197,94,.1);
}

.stars{
    display:inline-flex;
    align-items:center;
    gap:.14rem;
}

.star{
    width:16px;
    height:16px;
    fill:#334155;
}

.star.on{ fill: var(--pp-warn); }

.contact-actions{
    display:flex;
    flex-wrap:wrap;
    gap:.75rem;
    margin-top:1rem;
}

.profile-grid{
    display:grid;
    grid-template-columns: minmax(0, 1.05fr) minmax(0, .95fr);
    gap:1rem;
    align-items:stretch;
}

.section-card{
    padding:1rem 1.05rem;
    display:flex;
    flex-direction:column;
    gap:.95rem;
}

.section-title{
    margin:0;
    font-size:1rem;
    font-weight:900;
}

.detail-grid{
    display:grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap:.75rem;
}

.detail-card{
    padding:.9rem 1rem;
    border-radius:16px;
    border:1px solid rgba(255,255,255,.06);
    background: rgba(255,255,255,.03);
    min-height:88px;
}

.detail-card.full{
    grid-column: 1 / -1;
}

.detail-label{
    color:var(--pp-muted);
    font-size:.75rem;
    font-weight:700;
    letter-spacing:.08em;
    text-transform:uppercase;
}

.detail-value{
    margin-top:.35rem;
    font-size:.92rem;
    font-weight:700;
    line-height:1.55;
    word-break:break-word;
}

.detail-value a{
    color:var(--pp-accent);
    text-decoration:none;
}

.rating-summary-grid{
    display:grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap:.75rem;
    align-items:start;
}

.summary-box{
    padding:.95rem 1rem;
    border-radius:16px;
    border:1px solid rgba(255,255,255,.06);
    background: rgba(255,255,255,.03);
    display:flex;
    flex-direction:column;
    justify-content:center;
    min-height:94px;
}

.summary-box.wide{
    grid-column: 1 / -1;
    min-height:auto;
    justify-content:flex-start;
}

.summary-label{
    color:var(--pp-muted);
    font-size:.75rem;
    font-weight:700;
    letter-spacing:.08em;
    text-transform:uppercase;
}

.summary-value{
    margin-top:.35rem;
    font-size:1.45rem;
    font-weight:900;
}

.summary-note{
    margin-top:.3rem;
    color:var(--pp-muted);
    font-size:.82rem;
}

.breakdown-list{
    display:flex;
    flex-direction:column;
    gap:.7rem;
    margin-top:.85rem;
}

.breakdown-row{
    display:grid;
    grid-template-columns: 60px 1fr 40px;
    gap:.6rem;
    align-items:center;
}

.breakdown-row span{
    font-size:.82rem;
    font-weight:800;
}

.breakdown-row .count{
    text-align:right;
    color:var(--pp-muted);
}

.bar{
    height:10px;
    border-radius:999px;
    background: rgba(255,255,255,.06);
    overflow:hidden;
}

.bar span{
    display:block;
    height:100%;
    border-radius:999px;
    background: linear-gradient(90deg, rgba(251,191,36,.9), rgba(245,158,11,.75));
}

.reviews-list{
    display:flex;
    flex-direction:column;
    gap:.85rem;
}

.reviews-toolbar{
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:.75rem;
    flex-wrap:wrap;
    margin-top:1rem;
    padding:.9rem 1rem;
    border-radius:18px;
    border:1px solid rgba(255,255,255,.06);
    background: rgba(255,255,255,.03);
}

.toolbar-copy{
    color:var(--pp-muted);
    font-size:.84rem;
    line-height:1.45;
}

.toolbar-copy strong{
    color:rgba(255,255,255,.95);
}

.toolbar-control{
    display:flex;
    flex-direction:column;
    gap:.35rem;
    min-width:220px;
    max-width:260px;
}

.toolbar-control label{
    color:var(--pp-muted);
    font-size:.74rem;
    font-weight:700;
    letter-spacing:.08em;
    text-transform:uppercase;
}

.toolbar-select{
    width:100%;
    min-height:42px;
    padding:.65rem .8rem;
    border-radius:12px;
    border:1px solid rgba(255,255,255,.1);
    background: rgba(2,6,23,.92);
    color:rgba(255,255,255,.94);
    outline:none;
}

.toolbar-select:focus{
    border-color: rgba(56,189,248,.35);
    box-shadow: 0 0 0 3px rgba(56,189,248,.1);
}

.review-card{
    padding:1rem;
}

.review-top{
    display:grid;
    grid-template-columns: minmax(0, 1fr) auto;
    gap:.75rem;
    align-items:start;
}

.reviewer{
    display:flex;
    align-items:center;
    gap:.75rem;
    min-width:0;
}

.reviewer-avatar{
    width:42px;
    height:42px;
    border-radius:999px;
    object-fit:cover;
    border:1px solid rgba(255,255,255,.1);
    background: rgba(255,255,255,.04);
    flex:0 0 42px;
}

.reviewer-name{
    font-size:.94rem;
    font-weight:900;
    line-height:1.3;
}

.review-date{
    margin-top:.2rem;
    color:var(--pp-muted);
    font-size:.8rem;
}

.score-pill{
    display:inline-flex;
    align-items:center;
    gap:.45rem;
    padding:.42rem .7rem;
    border-radius:999px;
    border:1px solid rgba(255,255,255,.08);
    background: rgba(255,255,255,.03);
    font-size:.8rem;
    font-weight:800;
}

.review-comment{
    margin-top:.85rem;
    padding:.9rem 1rem;
    border-radius:16px;
    border:1px solid rgba(255,255,255,.06);
    background: rgba(255,255,255,.03);
    color:rgba(255,255,255,.88);
    line-height:1.6;
}

.review-comment.empty{
    color:var(--pp-muted);
}

.empty-card{
    padding:1.35rem;
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
    color:var(--pp-accent);
    font-size:1.35rem;
}

.empty-title{
    font-size:1rem;
    font-weight:900;
}

.empty-copy{
    margin-top:.35rem;
    color:var(--pp-muted);
    font-size:.9rem;
    line-height:1.6;
}

@media (max-width: 991.98px){
    .profile-grid{
        grid-template-columns: 1fr;
    }
}

@media (max-width: 767.98px){
    .profile-hero{
        grid-template-columns: 1fr;
        justify-items:start;
    }

    .top-actions{
        flex-direction:column;
        align-items:stretch;
    }

    .detail-grid,
    .rating-summary-grid{
        grid-template-columns: 1fr;
    }

    .review-top{
        grid-template-columns: 1fr;
    }

    .toolbar-control{
        min-width:100%;
    }
}
</style>

<div class="provider-profile-page">
    <div class="provider-profile-stack">

        <section class="profile-shell">
            <div class="top-actions">
                <a href="{{ route('customer.services') }}" class="back-link">
                    <i class="bi bi-arrow-left"></i>
                    <span>Back to Services</span>
                </a>

                <a href="{{ route('customer.book.service', $provider->id) }}" class="book-action">
                    <i class="bi bi-calendar2-check"></i>
                    <span>Book This Provider</span>
                </a>
            </div>

            <div class="profile-hero">
                <img
                    src="{{ $providerAvatar }}"
                    class="provider-avatar"
                    alt="Provider avatar"
                    onerror="this.onerror=null;this.src='{{ asset('images/avatar-placeholder.svg') }}';"
                >

                <div>
                    <h1 class="provider-name">{{ trim(($provider->first_name ?? '') . ' ' . ($provider->last_name ?? '')) }}</h1>
                    <div class="provider-location">{{ trim(($provider->city ?? '') . ', ' . ($provider->province ?? '')) }}</div>

                    <div class="hero-pills">
                        <div class="pill rating">
                            <span>{{ $count > 0 ? $avgText : 'No ratings yet' }}</span>
                            @if($count > 0)
                                <span class="stars" aria-hidden="true">
                                    @for($i = 1; $i <= 5; $i++)
                                        <svg class="star {{ $i <= $avgRounded ? 'on' : '' }}" viewBox="0 0 24 24">
                                            <path d="M12 17.27 18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/>
                                        </svg>
                                    @endfor
                                </span>
                            @endif
                        </div>

                        <div class="pill">
                            <i class="bi bi-chat-quote"></i>
                            <span>{{ $count }} review{{ $count === 1 ? '' : 's' }}</span>
                        </div>

                        <div class="pill verified">
                            <i class="bi bi-patch-check"></i>
                            <span>{{ ucfirst(strtolower((string) ($provider->status ?? 'approved'))) }}</span>
                        </div>
                    </div>

                    <div class="contact-actions">
                        <a href="tel:{{ $provider->phone }}" class="ghost-action">
                            <i class="bi bi-telephone"></i>
                            <span>Call</span>
                        </a>

                        <a href="mailto:{{ $provider->email }}" class="ghost-action">
                            <i class="bi bi-envelope"></i>
                            <span>Email</span>
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <section class="profile-grid">
            <article class="section-card">
                <h2 class="section-title">Provider details</h2>

                <div class="detail-grid">
                    <div class="detail-card">
                        <div class="detail-label">Contact Number</div>
                        <div class="detail-value">{{ $provider->phone ?: 'Not available' }}</div>
                    </div>

                    <div class="detail-card">
                        <div class="detail-label">Email Address</div>
                        <div class="detail-value">
                            @if(!empty($provider->email))
                                <a href="mailto:{{ $provider->email }}">{{ $provider->email }}</a>
                            @else
                                Not available
                            @endif
                        </div>
                    </div>

                    <div class="detail-card">
                        <div class="detail-label">City / Municipality</div>
                        <div class="detail-value">{{ $provider->city ?: 'Not available' }}</div>
                    </div>

                    <div class="detail-card">
                        <div class="detail-label">Province</div>
                        <div class="detail-value">{{ $provider->province ?: 'Not available' }}</div>
                    </div>

                    <div class="detail-card full">
                        <div class="detail-label">Full Address</div>
                        <div class="detail-value">
                            {{ trim(implode(', ', array_filter([
                                $provider->address ?? null,
                                $provider->barangay ?? null,
                                $provider->city ?? null,
                                $provider->province ?? null,
                                $provider->region ?? null,
                            ]))) ?: 'Not available' }}
                        </div>
                    </div>
                </div>
            </article>

            <aside class="section-card">
                <h2 class="section-title">Rating summary</h2>

                <div class="rating-summary-grid">
                    <div class="summary-box">
                        <div class="summary-label">Average rating</div>
                        <div class="summary-value">{{ $avgText }}</div>
                    </div>

                    <div class="summary-box">
                        <div class="summary-label">Total reviews</div>
                        <div class="summary-value">{{ $count }}</div>
                    </div>

                    <div class="summary-box wide">
                        <div class="summary-label">Score breakdown</div>

                        <div class="breakdown-list">
                            @for($star = 5; $star >= 1; $star--)
                                @php
                                    $scoreCount = (int) ($distribution[$star] ?? 0);
                                    $scorePercent = $count > 0 ? round(($scoreCount / $count) * 100) : 0;
                                @endphp

                                <div class="breakdown-row">
                                    <span>{{ $star }} star</span>
                                    <div class="bar">
                                        <span style="width: {{ $scorePercent }}%;"></span>
                                    </div>
                                    <span class="count">{{ $scoreCount }}</span>
                                </div>
                            @endfor
                        </div>
                    </div>
                </div>
            </aside>
        </section>

        <section class="section-card">
            <h2 class="section-title">Customer feedback</h2>

            @if(($reviews ?? collect())->isEmpty())
                <div class="empty-card" style="margin-top:1rem;">
                    <div class="empty-icon">
                        <i class="bi bi-chat-left-dots"></i>
                    </div>
                    <div class="empty-title">No reviews yet</div>
                    <div class="empty-copy">This provider has not received any written feedback yet.</div>
                </div>
            @else
                <div class="reviews-toolbar">
                    <div class="toolbar-copy">
                        Showing <strong id="providerReviewCount">{{ count($reviews) }}</strong>
                        review{{ count($reviews) === 1 ? '' : 's' }}.
                    </div>

                    <div class="toolbar-control">
                        <label for="providerReviewSort">Sort reviews</label>
                        <select id="providerReviewSort" class="toolbar-select">
                            <option value="relevance">Most relevant</option>
                            <option value="recent">Newest first</option>
                            <option value="oldest">Oldest first</option>
                            <option value="highest">Highest rating</option>
                            <option value="lowest">Lowest rating</option>
                        </select>
                    </div>
                </div>

                <div class="reviews-list" style="margin-top:1rem;">
                    @foreach($reviews as $review)
                        @php
                            $reviewerAvatar = asset('images/avatar-placeholder.svg');

                            if (!empty($review->customer_profile_image)) {
                                $reviewerAvatar = route('customer.image.public', ['filename' => basename($review->customer_profile_image)]) . '?v=' . time();
                            }

                            $reviewDate = !empty($review->created_at)
                                ? Carbon::parse($review->created_at)->format('M d, Y')
                                : 'No date';

                            $reviewTimestamp = !empty($review->created_at)
                                ? Carbon::parse($review->created_at)->timestamp
                                : 0;

                            $reviewScore = (int) ($review->rating ?? 0);
                            $comment = trim((string) ($review->comment ?? ''));
                        @endphp

                        <article
                            class="review-card provider-review-card"
                            data-rating="{{ $reviewScore }}"
                            data-ts="{{ $reviewTimestamp }}"
                            data-relevance="{{ ($reviewScore * 1000000) + $reviewTimestamp + strlen($comment) }}"
                        >
                            <div class="review-top">
                                <div class="reviewer">
                                    <img
                                        src="{{ $reviewerAvatar }}"
                                        alt="Reviewer avatar"
                                        class="reviewer-avatar"
                                        onerror="this.onerror=null;this.src='{{ asset('images/avatar-placeholder.svg') }}';"
                                    >

                                    <div style="min-width:0;">
                                        <div class="reviewer-name">{{ $review->customer_name ?? 'Customer' }}</div>
                                        <div class="review-date">{{ $reviewDate }}</div>
                                    </div>
                                </div>

                                <div class="score-pill">
                                    <span class="stars" aria-hidden="true">
                                        @for($i = 1; $i <= 5; $i++)
                                            <svg class="star {{ $i <= $reviewScore ? 'on' : '' }}" viewBox="0 0 24 24">
                                                <path d="M12 17.27 18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/>
                                            </svg>
                                        @endfor
                                    </span>
                                    <span>{{ $reviewScore }}/5</span>
                                </div>
                            </div>

                            <div class="review-comment {{ $comment === '' ? 'empty' : '' }}">
                                {{ $comment !== '' ? $comment : 'No written comment was left for this review.' }}
                            </div>
                        </article>
                    @endforeach
                </div>
            @endif
        </section>

    </div>
</div>

@if(($reviews ?? collect())->isNotEmpty())
<script>
(function(){
    const list = document.querySelector('.reviews-list');
    const sortControl = document.getElementById('providerReviewSort');
    const countLabel = document.getElementById('providerReviewCount');

    if (!list || !sortControl) {
        return;
    }

    const items = Array.from(list.querySelectorAll('.provider-review-card'));
    if (countLabel) {
        countLabel.textContent = items.length;
    }

    function sortItems(mode) {
        const sorted = items.slice().sort(function(a, b) {
            const ratingA = parseInt(a.getAttribute('data-rating') || '0', 10);
            const ratingB = parseInt(b.getAttribute('data-rating') || '0', 10);
            const tsA = parseInt(a.getAttribute('data-ts') || '0', 10);
            const tsB = parseInt(b.getAttribute('data-ts') || '0', 10);
            const relevanceA = parseInt(a.getAttribute('data-relevance') || '0', 10);
            const relevanceB = parseInt(b.getAttribute('data-relevance') || '0', 10);

            if (mode === 'recent') return tsB - tsA;
            if (mode === 'oldest') return tsA - tsB;
            if (mode === 'highest') return (ratingB - ratingA) || (tsB - tsA);
            if (mode === 'lowest') return (ratingA - ratingB) || (tsB - tsA);

            return relevanceB - relevanceA;
        });

        sorted.forEach(function(item) {
            list.appendChild(item);
        });
    }

    sortControl.addEventListener('change', function() {
        sortItems(sortControl.value);
    });

    sortItems(sortControl.value);
})();
</script>
@endif

@endsection
