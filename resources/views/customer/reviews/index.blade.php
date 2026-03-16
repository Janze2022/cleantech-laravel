@extends('customer.layouts.app')

@section('title','My Reviews')

@section('content')

<style>
:root{
    --bg-card:#020b1f;
    --bg-deep:#020617;
    --border-soft:rgba(255,255,255,.08);
    --text-muted:rgba(255,255,255,.55);
    --text:rgba(255,255,255,.92);
    --accent:#38bdf8;
    --warning:#fbbf24;
    --success:#22c55e;
    --danger:#ef4444;
}

/* Page header */
.page-title{
    display:flex;
    align-items:flex-end;
    justify-content:space-between;
    gap:1rem;
    margin-bottom:1.25rem;
}
.page-title h4{
    margin:0;
    font-weight:800;
    letter-spacing:.2px;
    color:var(--text);
}
.page-sub{
    color:var(--text-muted);
    font-size:.9rem;
}

/* Alerts (dark) */
.alert-dark-success{
    background: rgba(34,197,94,.10);
    border:1px solid rgba(34,197,94,.25);
    color: rgba(255,255,255,.90);
    border-radius:14px;
    padding:.9rem 1rem;
    margin-bottom:1rem;
}

/* Cards */
.review-card{
    background: linear-gradient(180deg, var(--bg-card), var(--bg-deep));
    border:1px solid var(--border-soft);
    border-radius:18px;
    padding:1.15rem;
    margin-bottom:1rem;
    box-shadow: 0 10px 30px rgba(0,0,0,.25);
}
.review-top{
    display:flex;
    justify-content:space-between;
    gap:1rem;
    align-items:flex-start;
    flex-wrap:wrap;
}
.provider-name{
    font-weight:800;
    color:var(--text);
    font-size:1rem;
    line-height:1.2;
}
.ref{
    color:var(--text-muted);
    font-size:.85rem;
    margin-top:.25rem;
}

/* Badges */
.badge-pill{
    padding:.35rem .6rem;
    border-radius:999px;
    font-size:.75rem;
    font-weight:800;
    letter-spacing:.02em;
    border:1px solid transparent;
    white-space:nowrap;
}
.badge-reviewed{
    background: rgba(34,197,94,.10);
    border-color: rgba(34,197,94,.25);
    color: rgba(255,255,255,.92);
}
.badge-pending{
    background: rgba(251,191,36,.12);
    border-color: rgba(251,191,36,.28);
    color: rgba(255,255,255,.92);
}

/* Layout */
.review-body{
    margin-top: .95rem;
    display:grid;
    grid-template-columns: 1fr;
    gap:.85rem;
}

/* Rating row */
.rating-row{
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:1rem;
    flex-wrap:wrap;
}
.rating-label{
    color: var(--text-muted);
    font-size:.85rem;
    font-weight:700;
}

/* Stars */
.stars{
    display:inline-flex;
    gap:.25rem;
    align-items:center;
}
.star-btn{
    appearance:none;
    background:transparent;
    border:0;
    padding:.2rem;
    border-radius:10px;
    cursor:pointer;
    line-height:0;
    transition: transform .08s ease, background .12s ease;
}
.star-btn:focus{
    outline:2px solid rgba(56,189,248,.35);
    outline-offset:2px;
}
.star-btn:hover{
    transform: translateY(-1px);
    background: rgba(255,255,255,.04);
}
.star-icon{
    width:22px;
    height:22px;
    display:block;
    fill: #334155;             /* slate */
    transition: fill .12s ease;
}
.star-on .star-icon{ fill: #fbbf24; }  /* amber */
.star-off .star-icon{ fill: #334155; }
.star-static .star-icon{ cursor:default; }

/* Helper text next to stars */
.rating-hint{
    color: var(--text-muted);
    font-size:.85rem;
    white-space:nowrap;
}

/* Textarea + button (dark) */
.review-textarea{
    width:100%;
    background: rgba(255,255,255,.03);
    border: 1px solid var(--border-soft);
    border-radius: 14px;
    padding: .85rem .9rem;
    color: rgba(255,255,255,.92);
    min-height: 110px;
    resize: vertical;
}
.review-textarea::placeholder{ color: rgba(255,255,255,.35); }
.review-textarea:focus{
    outline:none;
    border-color: rgba(56,189,248,.35);
    box-shadow: 0 0 0 3px rgba(56,189,248,.10);
}

/* Buttons */
.btn-dark-accent{
    background: rgba(56,189,248,.16);
    border:1px solid rgba(56,189,248,.35);
    color: rgba(255,255,255,.92);
    font-weight:800;
    border-radius: 14px;
    padding: .7rem 1rem;
    transition: transform .08s ease, filter .12s ease;
}
.btn-dark-accent:hover{
    filter: brightness(1.05);
    transform: translateY(-1px);
}
.btn-dark-accent:active{ transform: translateY(0); }

.review-meta{
    color: var(--text-muted);
    font-size:.9rem;
}

/* Mobile tweaks */
@media (max-width: 576px){
    .review-card{ padding: 1rem; border-radius:16px; }
    .provider-name{ font-size:.98rem; }
    .star-icon{ width:20px; height:20px; }
    .rating-row{ align-items:flex-start; }
    .rating-hint{ width:100%; }
}
</style>

<div class="page-title">
    <div>
        <h4>My Reviews</h4>
        <div class="page-sub">Leave feedback for completed services.</div>
    </div>
</div>

@if(session('success'))
    <div class="alert-dark-success">{{ session('success') }}</div>
@endif

@if(($reviews ?? collect())->count() === 0)
    <div class="review-card">
        <div class="review-meta">No completed bookings available for review yet.</div>
    </div>
@else

@foreach($reviews as $r)
<div class="review-card" id="review-{{ $r->reference_code }}">

    <div class="review-top">
        <div>
            <div class="provider-name">{{ $r->provider }}</div>
            <div class="ref">Ref: {{ $r->reference_code }}</div>
        </div>

        <div>
            @if($r->rating)
                <span class="badge-pill badge-reviewed">Reviewed</span>
            @else
                <span class="badge-pill badge-pending">Pending Review</span>
            @endif
        </div>
    </div>

    <div class="review-body">

        @if(!$r->rating)
            <form method="POST" action="{{ route('customer.reviews.store') }}" class="review-form">
                @csrf
                <input type="hidden" name="booking_id" value="{{ $r->booking_id }}">
                <input type="hidden" name="rating" value="" class="rating-input" required>

                <div class="rating-row">
                    <div class="rating-label">Rating</div>

                    <div class="stars" role="radiogroup" aria-label="Star rating">
                        @for($i=1;$i<=5;$i++)
                            <button type="button"
                                    class="star-btn star-off"
                                    data-value="{{ $i }}"
                                    aria-label="Rate {{ $i }} star{{ $i>1 ? 's' : '' }}">
                                <svg class="star-icon" viewBox="0 0 24 24" aria-hidden="true">
                                    <path d="M12 17.27 18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/>
                                </svg>
                            </button>
                        @endfor
                    </div>

                    <div class="rating-hint">
                        <span class="selected-text">Select a rating</span>
                    </div>
                </div>

                <textarea name="comment"
                          class="review-textarea"
                          placeholder="Write your review (optional)..."></textarea>

                <button class="btn-dark-accent">Submit Review</button>
            </form>

        @else
            {{-- Reviewed display --}}
            <div class="rating-row">
                <div class="rating-label">Your rating</div>

                <div class="stars star-static" aria-label="Your rating: {{ (int)$r->rating }} out of 5">
                    @for($i=1;$i<=5;$i++)
                        <span class="{{ $i <= (int)$r->rating ? 'star-on' : 'star-off' }}">
                            <svg class="star-icon" viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M12 17.27 18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/>
                            </svg>
                        </span>
                    @endfor
                </div>

                <div class="rating-hint">
                    <strong style="color:rgba(255,255,255,.92)">{{ (int)$r->rating }}</strong>
                    <span style="color:var(--text-muted)">/ 5</span>
                </div>
            </div>

            <div class="review-meta">
                {{ $r->comment ? $r->comment : 'No comment.' }}
            </div>
        @endif

    </div>

</div>
@endforeach

@endif

<script>
(function(){
    // For each review form: handle hover + click star rating
    document.querySelectorAll('.review-form').forEach(function(form){
        const stars = Array.from(form.querySelectorAll('.star-btn'));
        const input = form.querySelector('.rating-input');
        const label = form.querySelector('.selected-text');

        function paint(value){
            stars.forEach(btn => {
                const v = parseInt(btn.getAttribute('data-value'), 10);
                btn.classList.toggle('star-on', v <= value);
                btn.classList.toggle('star-off', v > value);
            });
        }

        // Hover preview (doesn't commit)
        stars.forEach(btn => {
            btn.addEventListener('mouseenter', function(){
                const v = parseInt(btn.getAttribute('data-value'), 10);
                paint(v);
                if (label) label.textContent = v + ' / 5';
            });
        });

        // Restore committed value on leaving the star row
        const starWrap = form.querySelector('.stars');
        if (starWrap){
            starWrap.addEventListener('mouseleave', function(){
                const committed = parseInt(input.value || '0', 10);
                paint(committed);
                if (label) label.textContent = committed ? (committed + ' / 5') : 'Select a rating';
            });
        }

        // Click to commit rating
        stars.forEach(btn => {
            btn.addEventListener('click', function(){
                const v = parseInt(btn.getAttribute('data-value'), 10);
                input.value = v;
                paint(v);
                if (label) label.textContent = v + ' / 5';
            });
        });

        // If browser restores form state
        const committed = parseInt(input.value || '0', 10);
        paint(committed);
        if (label) label.textContent = committed ? (committed + ' / 5') : 'Select a rating';
    });

    // Auto-scroll when coming from notification: /customer/reviews?ref=XXXX
    const params = new URLSearchParams(window.location.search);
    const ref = params.get('ref');
    if (ref) {
        const el = document.getElementById('review-' + ref);
        if (el) {
            setTimeout(() => {
                el.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }, 150);
        }
    }
})();
</script>

@endsection
