@extends('customer.layouts.app')

@section('title', 'My Reviews')

@section('content')

@php
    use Carbon\Carbon;

    $reviews = $reviews ?? collect();
    $reviewedCount = $reviews->filter(fn ($review) => (int) ($review->rating ?? 0) > 0)->count();
    $pendingCount = max($reviews->count() - $reviewedCount, 0);

    $ratingLabel = function ($value) {
        return match ((int) $value) {
            5 => 'Excellent',
            4 => 'Very good',
            3 => 'Good',
            2 => 'Needs work',
            1 => 'Poor',
            default => 'Select a rating',
        };
    };
@endphp

<style>
:root{
    --rv-bg:#020617;
    --rv-card:#071225;
    --rv-card-soft:#0b1830;
    --rv-border:rgba(255,255,255,.08);
    --rv-text:rgba(255,255,255,.94);
    --rv-muted:rgba(255,255,255,.58);
    --rv-accent:#38bdf8;
    --rv-success:#22c55e;
    --rv-warn:#fbbf24;
}

.reviews-page{
    max-width: 1040px;
    margin: 0 auto;
    color: var(--rv-text);
}

.reviews-stack{
    display:flex;
    flex-direction:column;
    gap:1rem;
}

.reviews-hero,
.reviews-card,
.reviews-empty{
    background: linear-gradient(180deg, rgba(7,18,37,.96), rgba(2,6,23,.98));
    border:1px solid var(--rv-border);
    border-radius:22px;
    box-shadow: 0 20px 40px rgba(0,0,0,.28);
}

.reviews-hero{
    padding:1.25rem;
}

.reviews-head{
    display:flex;
    align-items:flex-start;
    justify-content:space-between;
    gap:1rem;
    flex-wrap:wrap;
}

.reviews-title{
    margin:0;
    font-size:1.2rem;
    font-weight:900;
    letter-spacing:-.02em;
}

.reviews-subtitle{
    margin:.35rem 0 0;
    color:var(--rv-muted);
    font-size:.9rem;
    line-height:1.55;
}

.reviews-chip{
    display:inline-flex;
    align-items:center;
    gap:.45rem;
    padding:.55rem .85rem;
    border-radius:999px;
    background: rgba(56,189,248,.1);
    border:1px solid rgba(56,189,248,.2);
    color:rgba(255,255,255,.94);
    font-size:.85rem;
    font-weight:800;
    white-space:nowrap;
}

.reviews-summary{
    display:grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap:.75rem;
    margin-top:1rem;
}

.summary-box{
    padding:.95rem 1rem;
    border-radius:18px;
    background: rgba(255,255,255,.03);
    border:1px solid rgba(255,255,255,.06);
}

.summary-label{
    color:var(--rv-muted);
    font-size:.76rem;
    font-weight:700;
    letter-spacing:.08em;
    text-transform:uppercase;
}

.summary-value{
    margin-top:.45rem;
    font-size:1.45rem;
    font-weight:900;
    line-height:1;
}

.summary-note{
    margin-top:.35rem;
    color:var(--rv-muted);
    font-size:.8rem;
}

.reviews-list{
    display:flex;
    flex-direction:column;
    gap:1rem;
}

.reviews-card{
    padding:1rem;
}

.card-top{
    display:grid;
    grid-template-columns: minmax(0, 1fr) auto;
    gap:.85rem;
    align-items:start;
}

.provider-name{
    font-size:1rem;
    font-weight:900;
    line-height:1.3;
}

.service-line{
    margin-top:.2rem;
    color:rgba(255,255,255,.86);
    font-size:.88rem;
    font-weight:700;
    line-height:1.45;
}

.service-line .muted{
    color:var(--rv-muted);
    font-weight:600;
}

.status-pill{
    display:inline-flex;
    align-items:center;
    gap:.4rem;
    padding:.42rem .72rem;
    border-radius:999px;
    border:1px solid rgba(255,255,255,.1);
    font-size:.78rem;
    font-weight:800;
    white-space:nowrap;
}

.status-pill.reviewed{
    background: rgba(34,197,94,.1);
    border-color: rgba(34,197,94,.22);
    color:#86efac;
}

.status-pill.pending{
    background: rgba(251,191,36,.1);
    border-color: rgba(251,191,36,.22);
    color:#fde68a;
}

.booking-meta{
    display:flex;
    flex-wrap:wrap;
    gap:.55rem;
    margin-top:.9rem;
}

.meta-pill{
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

.meta-pill.price{
    border-color: rgba(56,189,248,.18);
    background: rgba(56,189,248,.08);
}

.review-panel{
    margin-top:1rem;
    padding-top:.95rem;
    border-top:1px solid rgba(255,255,255,.06);
}

.rating-row{
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:.85rem;
    flex-wrap:wrap;
}

.rating-copy{
    color:var(--rv-muted);
    font-size:.84rem;
    font-weight:700;
}

.stars{
    display:inline-flex;
    align-items:center;
    gap:.25rem;
}

.star-btn{
    appearance:none;
    border:0;
    background:transparent;
    padding:.18rem;
    border-radius:10px;
    cursor:pointer;
    line-height:0;
    transition: transform .08s ease, background .12s ease;
}

.star-btn:hover{
    transform:translateY(-1px);
    background:rgba(255,255,255,.04);
}

.star-btn:focus{
    outline:2px solid rgba(56,189,248,.35);
    outline-offset:2px;
}

.star-icon{
    width:22px;
    height:22px;
    display:block;
    fill:#334155;
    transition: fill .12s ease;
}

.star-on .star-icon{ fill:#fbbf24; }
.star-off .star-icon{ fill:#334155; }
.star-static .star-icon{ width:18px; height:18px; }

.rating-selected{
    color:rgba(255,255,255,.9);
    font-size:.82rem;
    font-weight:800;
}

.review-textarea{
    width:100%;
    min-height:100px;
    margin-top:.9rem;
    padding:.85rem .9rem;
    border-radius:14px;
    border:1px solid rgba(255,255,255,.08);
    background:rgba(255,255,255,.03);
    color:rgba(255,255,255,.94);
    resize:vertical;
}

.review-textarea::placeholder{
    color:rgba(255,255,255,.35);
}

.review-textarea:focus{
    outline:none;
    border-color: rgba(56,189,248,.35);
    box-shadow: 0 0 0 3px rgba(56,189,248,.1);
}

.review-actions{
    display:flex;
    justify-content:flex-end;
    margin-top:.8rem;
}

.btn-review{
    display:inline-flex;
    align-items:center;
    justify-content:center;
    gap:.45rem;
    min-height:42px;
    padding:.68rem 1rem;
    border-radius:12px;
    border:1px solid rgba(56,189,248,.3);
    background: rgba(56,189,248,.12);
    color:rgba(255,255,255,.94);
    font-weight:800;
}

.btn-review:hover{
    background: rgba(56,189,248,.16);
}

.review-result{
    display:grid;
    gap:.8rem;
}

.review-score{
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:.85rem;
    flex-wrap:wrap;
}

.review-score strong{
    color:rgba(255,255,255,.95);
}

.comment-box{
    padding:.95rem 1rem;
    border-radius:16px;
    border:1px solid rgba(255,255,255,.06);
    background: rgba(255,255,255,.03);
    color:rgba(255,255,255,.88);
    line-height:1.6;
}

.comment-box.empty{
    color:var(--rv-muted);
}

.reviews-empty{
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
    color:var(--rv-accent);
    font-size:1.35rem;
}

.empty-title{
    font-size:1.02rem;
    font-weight:900;
}

.empty-copy{
    margin-top:.35rem;
    color:var(--rv-muted);
    font-size:.9rem;
    line-height:1.6;
}

@media (max-width: 767.98px){
    .reviews-summary{
        grid-template-columns: 1fr;
    }

    .card-top{
        grid-template-columns: 1fr;
    }

    .review-actions .btn-review{
        width:100%;
    }
}
</style>

<div class="reviews-page">
    <div class="reviews-stack">

        <section class="reviews-hero">
            <div class="reviews-head">
                <div>
                    <h1 class="reviews-title">My Reviews</h1>
                    <p class="reviews-subtitle">Rate completed bookings and keep your feedback in one clean place.</p>
                </div>

                <div class="reviews-chip">
                    <i class="bi bi-chat-square-heart"></i>
                    <span>{{ $reviews->count() }} completed booking{{ $reviews->count() === 1 ? '' : 's' }}</span>
                </div>
            </div>

            <div class="reviews-summary">
                <div class="summary-box">
                    <div class="summary-label">Ready to review</div>
                    <div class="summary-value">{{ $pendingCount }}</div>
                    <div class="summary-note">Still waiting for your rating.</div>
                </div>

                <div class="summary-box">
                    <div class="summary-label">Already reviewed</div>
                    <div class="summary-value">{{ $reviewedCount }}</div>
                    <div class="summary-note">Ratings you have submitted.</div>
                </div>

                <div class="summary-box">
                    <div class="summary-label">Total completed</div>
                    <div class="summary-value">{{ $reviews->count() }}</div>
                    <div class="summary-note">Finished bookings in your list.</div>
                </div>
            </div>
        </section>

        @if($reviews->isEmpty())
            <section class="reviews-empty">
                <div class="empty-icon">
                    <i class="bi bi-stars"></i>
                </div>
                <div class="empty-title">No completed bookings yet</div>
                <div class="empty-copy">Once you finish a booking, it will show up here so you can leave a review.</div>
            </section>
        @else
            <section class="reviews-list">
                @foreach($reviews as $r)
                    @php
                        $bookingDate = !empty($r->booking_date)
                            ? Carbon::parse($r->booking_date)->format('M d, Y')
                            : 'No date';

                        $serviceName = $r->service_name ?? 'Service';
                        $optionName = trim((string) ($r->option_name ?? ''));
                        $amount = 'PHP ' . number_format((float) ($r->price ?? 0), 2);
                        $currentRating = (int) ($r->rating ?? 0);
                    @endphp

                    <article class="reviews-card" id="review-{{ $r->reference_code }}">
                        <div class="card-top">
                            <div>
                                <div class="provider-name">{{ $r->provider }}</div>
                                <div class="service-line">
                                    {{ $serviceName }}
                                    @if($optionName !== '')
                                        <span class="muted">• {{ $optionName }}</span>
                                    @endif
                                </div>
                            </div>

                            <div class="status-pill {{ $currentRating > 0 ? 'reviewed' : 'pending' }}">
                                <i class="bi {{ $currentRating > 0 ? 'bi-check2-circle' : 'bi-clock-history' }}"></i>
                                <span>{{ $currentRating > 0 ? 'Reviewed' : 'Pending review' }}</span>
                            </div>
                        </div>

                        <div class="booking-meta">
                            @if(!empty($r->reference_code))
                                <div class="meta-pill">
                                    <i class="bi bi-hash"></i>
                                    <span>{{ $r->reference_code }}</span>
                                </div>
                            @endif

                            <div class="meta-pill">
                                <i class="bi bi-calendar3"></i>
                                <span>{{ $bookingDate }}</span>
                            </div>

                            <div class="meta-pill price">
                                <i class="bi bi-wallet2"></i>
                                <span>{{ $amount }}</span>
                            </div>
                        </div>

                        <div class="review-panel">
                            @if($currentRating === 0)
                                <form method="POST" action="{{ route('customer.reviews.store') }}" class="review-form">
                                    @csrf
                                    <input type="hidden" name="booking_id" value="{{ $r->booking_id }}">
                                    <input type="hidden" name="rating" value="" class="rating-input" required>

                                    <div class="rating-row">
                                        <div class="rating-copy">How was this booking?</div>

                                        <div class="stars" role="radiogroup" aria-label="Star rating">
                                            @for($i = 1; $i <= 5; $i++)
                                                <button
                                                    type="button"
                                                    class="star-btn star-off"
                                                    data-value="{{ $i }}"
                                                    aria-label="Rate {{ $i }} star{{ $i > 1 ? 's' : '' }}"
                                                >
                                                    <svg class="star-icon" viewBox="0 0 24 24" aria-hidden="true">
                                                        <path d="M12 17.27 18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/>
                                                    </svg>
                                                </button>
                                            @endfor
                                        </div>

                                        <div class="rating-selected">
                                            <span class="selected-text">{{ $ratingLabel(0) }}</span>
                                        </div>
                                    </div>

                                    <textarea
                                        name="comment"
                                        class="review-textarea"
                                        placeholder="Share a short comment if you want to."
                                    ></textarea>

                                    <div class="review-actions">
                                        <button class="btn-review" type="submit">
                                            <i class="bi bi-send"></i>
                                            <span>Submit Review</span>
                                        </button>
                                    </div>
                                </form>
                            @else
                                <div class="review-result">
                                    <div class="review-score">
                                        <div class="rating-copy">Your rating</div>

                                        <div class="stars star-static" aria-label="Your rating: {{ $currentRating }} out of 5">
                                            @for($i = 1; $i <= 5; $i++)
                                                <span class="{{ $i <= $currentRating ? 'star-on' : 'star-off' }}">
                                                    <svg class="star-icon" viewBox="0 0 24 24" aria-hidden="true">
                                                        <path d="M12 17.27 18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/>
                                                    </svg>
                                                </span>
                                            @endfor
                                        </div>

                                        <div class="rating-selected">
                                            <strong>{{ $currentRating }}/5</strong> {{ $ratingLabel($currentRating) }}
                                        </div>
                                    </div>

                                    <div class="comment-box {{ trim((string) ($r->comment ?? '')) === '' ? 'empty' : '' }}">
                                        {{ trim((string) ($r->comment ?? '')) !== '' ? $r->comment : 'No written comment was added for this review.' }}
                                    </div>
                                </div>
                            @endif
                        </div>
                    </article>
                @endforeach
            </section>
        @endif

    </div>
</div>

<script>
(function(){
    document.querySelectorAll('.review-form').forEach(function(form){
        const stars = Array.from(form.querySelectorAll('.star-btn'));
        const input = form.querySelector('.rating-input');
        const label = form.querySelector('.selected-text');
        const labels = {
            0: 'Select a rating',
            1: 'Poor',
            2: 'Needs work',
            3: 'Good',
            4: 'Very good',
            5: 'Excellent'
        };

        function paint(value){
            stars.forEach(function(btn){
                const current = parseInt(btn.getAttribute('data-value'), 10);
                btn.classList.toggle('star-on', current <= value);
                btn.classList.toggle('star-off', current > value);
            });
        }

        stars.forEach(function(btn){
            btn.addEventListener('mouseenter', function(){
                const value = parseInt(btn.getAttribute('data-value'), 10);
                paint(value);
                if (label) label.textContent = labels[value] || (value + ' / 5');
            });

            btn.addEventListener('click', function(){
                const value = parseInt(btn.getAttribute('data-value'), 10);
                input.value = value;
                paint(value);
                if (label) label.textContent = labels[value] || (value + ' / 5');
            });
        });

        const starWrap = form.querySelector('.stars');
        if (starWrap) {
            starWrap.addEventListener('mouseleave', function(){
                const committed = parseInt(input.value || '0', 10);
                paint(committed);
                if (label) label.textContent = labels[committed] || (committed + ' / 5');
            });
        }

        paint(0);
    });

    const params = new URLSearchParams(window.location.search);
    const ref = params.get('ref');
    if (ref) {
        const element = document.getElementById('review-' + ref);
        if (element) {
            setTimeout(function(){
                element.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }, 150);
        }
    }
})();
</script>

@endsection
