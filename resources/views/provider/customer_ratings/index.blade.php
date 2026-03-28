@extends('provider.layouts.app')

@section('title', 'Customer Ratings')

@section('content')
@php
    use Carbon\Carbon;

    $summary = $summary ?? (object) [
        'completed_bookings' => 0,
        'pending_ratings' => 0,
        'submitted_ratings' => 0,
        'editable_ratings' => 0,
    ];

    $pendingBookings = collect($pendingBookings ?? []);
    $submittedRatings = collect($submittedRatings ?? []);
    $focusBooking = (int) request('booking', 0);

    $attachmentUrl = function ($row) {
        if (empty($row->attachment_path)) {
            return null;
        }

        return route('customer.ratings.attachment', ['filename' => basename($row->attachment_path)]);
    };

    $ratingWord = function ($value) {
        return match ((int) $value) {
            5 => 'Excellent',
            4 => 'Very Good',
            3 => 'Good',
            2 => 'Needs Work',
            1 => 'Poor',
            default => 'Select a rating',
        };
    };

    $formatDate = function ($value) {
        if (empty($value)) {
            return '-';
        }

        return Carbon::parse($value)->format('M d, Y');
    };

    $positiveFlags = [
        'booking_details_accurate' => 'Booking details were accurate',
        'respectful' => 'Customer was respectful',
        'easy_to_communicate' => 'Easy to communicate with',
        'paid_reliably' => 'Paid properly / on time',
        'unexpected_extra_work' => 'Requested unexpected extra work',
    ];

    $issueFlags = [
        'flag_understated_area' => 'Understated area size',
        'flag_hidden_sections' => 'Hidden additional rooms / sections',
        'flag_misleading_request' => 'Misleading service request',
        'flag_difficult_behavior' => 'Difficult behavior',
        'flag_payment_issue' => 'Payment issue',
        'flag_last_minute_changes' => 'Excessive last-minute changes',
    ];
@endphp

<style>
:root{
    --cr-bg:#020617;
    --cr-card:#071225;
    --cr-border:rgba(255,255,255,.08);
    --cr-text:rgba(255,255,255,.95);
    --cr-muted:rgba(255,255,255,.6);
    --cr-accent:#38bdf8;
    --cr-success:#22c55e;
    --cr-warn:#f59e0b;
    --cr-danger:#ef4444;
}

.customer-ratings-page{max-width:1200px;margin:0 auto;color:var(--cr-text);display:flex;flex-direction:column;gap:1rem}
.shell-card,.rating-card,.summary-card,.empty-card{background:linear-gradient(180deg, rgba(7,18,37,.97), rgba(2,6,23,.99));border:1px solid var(--cr-border);border-radius:24px;box-shadow:0 20px 40px rgba(0,0,0,.28)}
.page-hero{padding:1.2rem 1.25rem}
.hero-top,.section-head,.booking-top,.stars-row{display:flex;align-items:flex-start;justify-content:space-between;gap:1rem;flex-wrap:wrap}
.hero-title,.section-title{margin:0;font-weight:900;letter-spacing:-.03em}
.hero-title{font-size:1.4rem}
.section-title{font-size:1.08rem}
.hero-subtitle,.section-copy,.edit-window,.field-help{margin:.35rem 0 0;color:var(--cr-muted);font-size:.92rem;line-height:1.55}
.hero-chip,.section-pill,.status-badge,.meta-pill,.flag-chip{display:inline-flex;align-items:center;gap:.42rem;border-radius:999px;font-weight:900}
.hero-chip{min-height:38px;padding:.5rem .82rem;border:1px solid rgba(56,189,248,.2);background:rgba(56,189,248,.1);color:#e0f2fe;font-size:.84rem}
.summary-grid{display:grid;grid-template-columns:repeat(4, minmax(0,1fr));gap:.8rem;margin-top:1rem}
.summary-card{padding:1rem}
.summary-label,.detail-label,.form-label{color:var(--cr-muted);font-size:.76rem;font-weight:800;letter-spacing:.1em;text-transform:uppercase}
.summary-value{margin-top:.5rem;font-size:1.55rem;font-weight:900;line-height:1}
.summary-note{margin-top:.4rem;color:var(--cr-muted);font-size:.82rem}
.summary-value.accent{color:var(--cr-accent)}.summary-value.success{color:#86efac}.summary-value.warn{color:#fdba74}
.section-card{padding:1.1rem}
.section-pill{min-width:40px;min-height:40px;padding:0 .85rem;border:1px solid rgba(56,189,248,.18);background:rgba(56,189,248,.08);color:#dff7ff;font-size:.84rem}
.cards-stack{display:flex;flex-direction:column;gap:1rem}
.rating-card{padding:1rem}
.rating-card.focused{border-color:rgba(56,189,248,.35);box-shadow:0 0 0 3px rgba(56,189,248,.08),0 22px 40px rgba(0,0,0,.3)}
.booking-reference{margin:0;font-size:1.05rem;font-weight:900}
.booking-subtext{margin-top:.3rem;color:var(--cr-muted);font-size:.85rem;line-height:1.5}
.status-badge{padding:.45rem .78rem;border:1px solid rgba(255,255,255,.1);background:rgba(255,255,255,.04);font-size:.78rem;white-space:nowrap}
.status-badge.pending{border-color:rgba(245,158,11,.22);background:rgba(245,158,11,.12);color:#fde68a}
.status-badge.submitted{border-color:rgba(34,197,94,.22);background:rgba(34,197,94,.12);color:#bbf7d0}
.booking-meta,.flags-row,.attachment-row,.form-actions{display:flex;flex-wrap:wrap;gap:.7rem}
.booking-meta{margin-top:.9rem}
.meta-pill{padding:.42rem .72rem;border:1px solid rgba(255,255,255,.08);background:rgba(255,255,255,.03);color:rgba(255,255,255,.9);font-size:.8rem}
.meta-pill.price{border-color:rgba(56,189,248,.18);background:rgba(56,189,248,.1)}
.details-grid,.form-grid,.choice-grid{display:grid;gap:.8rem}
.details-grid,.form-grid{grid-template-columns:repeat(2,minmax(0,1fr));margin-top:1rem}
.choice-grid{grid-template-columns:repeat(2,minmax(0,1fr));gap:.65rem .8rem}
.detail-box,.form-block,.review-comment,.check-pill{padding:.9rem 1rem;border-radius:18px;border:1px solid rgba(255,255,255,.06);background:rgba(255,255,255,.03)}
.detail-box.full,.form-block.full{grid-column:1 / -1}
.detail-value{margin-top:.38rem;font-size:.92rem;font-weight:800;line-height:1.55;word-break:break-word}
.detail-subvalue{margin-top:.18rem;color:var(--cr-muted);font-size:.82rem}
.form-shell{margin-top:1rem;padding-top:1rem;border-top:1px solid rgba(255,255,255,.06)}
.stars{display:inline-flex;align-items:center;gap:.22rem}
.star-btn{appearance:none;border:0;background:transparent;padding:.16rem;border-radius:10px;cursor:pointer;line-height:0;transition:transform .08s ease,background .12s ease}
.star-btn:hover{transform:translateY(-1px);background:rgba(255,255,255,.04)}
.star-btn:focus{outline:2px solid rgba(56,189,248,.28);outline-offset:2px}
.star-icon{width:23px;height:23px;fill:#334155;display:block;transition:fill .12s ease}
.star-btn.on .star-icon{fill:#fbbf24}
.rating-selected{color:var(--cr-muted);font-size:.84rem;font-weight:800}
.check-pill{display:flex;align-items:flex-start;gap:.6rem;background:rgba(2,6,23,.38)}
.check-pill input{margin-top:.12rem;accent-color:#38bdf8}
.check-pill span{font-size:.84rem;line-height:1.45;color:rgba(255,255,255,.9);font-weight:700}
.text-area,.file-input{width:100%;border-radius:14px;border:1px solid rgba(255,255,255,.08);background:rgba(2,6,23,.45);color:#fff}
.text-area{min-height:110px;padding:.9rem .95rem;resize:vertical}
.file-input{min-height:48px;padding:.72rem .85rem}
.text-area::placeholder{color:rgba(255,255,255,.35)}
.text-area:focus,.file-input:focus{outline:none;border-color:rgba(56,189,248,.3);box-shadow:0 0 0 3px rgba(56,189,248,.08)}
.attachment-thumb{width:88px;height:88px;object-fit:cover;border-radius:16px;border:1px solid rgba(255,255,255,.08);background:rgba(255,255,255,.03)}
.attachment-link{color:#7dd3fc;font-weight:800;text-decoration:none}
.btn-rate{display:inline-flex;align-items:center;justify-content:center;min-height:46px;padding:.75rem 1.1rem;border-radius:14px;border:1px solid transparent;text-decoration:none;font-weight:900}
.btn-rate.primary{background:linear-gradient(135deg,#2563eb,#38bdf8);color:#fff}
.btn-rate.secondary{border-color:rgba(255,255,255,.1);background:rgba(255,255,255,.03);color:#fff}
.flag-chip{padding:.38rem .68rem;border:1px solid rgba(255,255,255,.08);background:rgba(255,255,255,.03);color:#e2e8f0;font-size:.76rem}
.flag-chip.issue{border-color:rgba(239,68,68,.2);background:rgba(239,68,68,.08);color:#fecaca}
.flag-chip.good{border-color:rgba(34,197,94,.18);background:rgba(34,197,94,.08);color:#bbf7d0}
.review-comment{margin-top:.9rem;color:rgba(255,255,255,.9);line-height:1.65}
.empty-card{text-align:center;color:var(--cr-muted);font-weight:800;padding:1.4rem}
.flash,.error-box{padding:.95rem 1rem;border-radius:16px;border:1px solid rgba(255,255,255,.08);font-weight:800}
.flash{background:rgba(34,197,94,.08);border-color:rgba(34,197,94,.18);color:#bbf7d0}
.error-box{background:rgba(239,68,68,.08);border-color:rgba(239,68,68,.18);color:#fecaca}
@media (max-width:1100px){.summary-grid{grid-template-columns:repeat(2,minmax(0,1fr))}.form-grid,.details-grid,.choice-grid{grid-template-columns:1fr}}
@media (max-width:640px){.summary-grid{grid-template-columns:1fr}.booking-top{grid-template-columns:1fr}.hero-title{font-size:1.2rem}.form-actions{flex-direction:column}.btn-rate{width:100%}}
</style>

<div class="customer-ratings-page">
    @if(session('success'))
        <div class="flash">{{ session('success') }}</div>
    @endif

    @if($errors->has('customer_rating'))
        <div class="error-box">{{ $errors->first('customer_rating') }}</div>
    @endif

    @if($errors->any() && !$errors->has('customer_rating'))
        <div class="error-box">Please review the rating form and try again.</div>
    @endif

    <div class="shell-card page-hero">
        <div class="hero-top">
            <div>
                <h1 class="hero-title">Customer Ratings</h1>
                <p class="hero-subtitle">Rate customers after completed bookings, keep your notes organized, and review the feedback you have already submitted.</p>
            </div>
            <div class="hero-chip"><i class="bi bi-clipboard-check"></i> One rating per completed booking</div>
        </div>

        <div class="summary-grid">
            <div class="summary-card">
                <div class="summary-label">Completed Bookings</div>
                <div class="summary-value">{{ $summary->completed_bookings }}</div>
                <div class="summary-note">Eligible for rating</div>
            </div>
            <div class="summary-card">
                <div class="summary-label">Pending Ratings</div>
                <div class="summary-value warn">{{ $summary->pending_ratings }}</div>
                <div class="summary-note">Waiting for feedback</div>
            </div>
            <div class="summary-card">
                <div class="summary-label">Submitted Ratings</div>
                <div class="summary-value accent">{{ $summary->submitted_ratings }}</div>
                <div class="summary-note">Stored in history</div>
            </div>
            <div class="summary-card">
                <div class="summary-label">Editable Now</div>
                <div class="summary-value success">{{ $summary->editable_ratings }}</div>
                <div class="summary-note">Within 24 hours</div>
            </div>
        </div>
    </div>

    <div class="shell-card section-card">
        <div class="section-head">
            <div>
                <h2 class="section-title">Pending Customer Ratings</h2>
                <p class="section-copy">Submit provider feedback for completed jobs that do not have a customer rating yet.</p>
            </div>
            <div class="section-pill">{{ $pendingBookings->count() }}</div>
        </div>

        @if($pendingBookings->isEmpty())
            <div class="empty-card">No completed bookings are waiting for a customer rating.</div>
        @else
            <div class="cards-stack">
                @foreach($pendingBookings as $booking)
                    <div class="rating-card {{ $focusBooking === (int) $booking->booking_id ? 'focused' : '' }}" id="booking-{{ $booking->booking_id }}">
                        <div class="booking-top">
                            <div>
                                <h3 class="booking-reference">{{ $booking->reference_code }}</h3>
                                <div class="booking-subtext">{{ $booking->service_name }}@if(!empty($booking->option_name)) / {{ $booking->option_name }}@endif</div>
                            </div>
                            <div class="status-badge pending"><i class="bi bi-clock-history"></i> Pending Rating</div>
                        </div>

                        <div class="booking-meta">
                            <div class="meta-pill"><i class="bi bi-calendar-event"></i> {{ $formatDate($booking->booking_date) }}</div>
                            <div class="meta-pill price"><i class="bi bi-cash-stack"></i> PHP {{ number_format((float) $booking->price, 2) }}</div>
                            <div class="meta-pill"><i class="bi bi-check2-circle"></i> {{ strtoupper((string) $booking->booking_status) }}</div>
                        </div>

                        <div class="details-grid">
                            <div class="detail-box">
                                <div class="detail-label">Customer</div>
                                <div class="detail-value">{{ $booking->customer_name }}</div>
                                <div class="detail-subvalue">{{ $booking->customer_email }}</div>
                                @if(!empty($booking->customer_phone))
                                    <div class="detail-subvalue">{{ $booking->customer_phone }}</div>
                                @endif
                            </div>
                            <div class="detail-box">
                                <div class="detail-label">Booking Scope</div>
                                <div class="detail-value">{{ $booking->service_name }}</div>
                                @if(!empty($booking->option_name))
                                    <div class="detail-subvalue">{{ $booking->option_name }}</div>
                                @endif
                            </div>
                        </div>

                        <form class="form-shell rating-form" method="POST" action="{{ route('provider.customer-ratings.store') }}" enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" name="booking_id" value="{{ $booking->booking_id }}">
                            <input type="hidden" name="rating" value="0" class="rating-input">

                            <div class="form-grid">
                                <div class="form-block full">
                                    <label class="form-label">Overall customer rating</label>
                                    <div class="stars-row">
                                        <div class="stars">
                                            @for($star = 1; $star <= 5; $star++)
                                                <button type="button" class="star-btn" data-star="{{ $star }}" aria-label="Rate {{ $star }}">
                                                    <svg class="star-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2.75l2.91 5.89 6.5.95-4.7 4.58 1.11 6.47L12 17.59 6.18 20.64l1.11-6.47-4.7-4.58 6.5-.95L12 2.75z"/></svg>
                                                </button>
                                            @endfor
                                        </div>
                                        <div class="rating-selected">Select a rating</div>
                                    </div>
                                </div>

                                <div class="form-block">
                                    <label class="form-label">Positive assessment</label>
                                    <div class="choice-grid">
                                        @foreach($positiveFlags as $field => $label)
                                            <label class="check-pill">
                                                <input type="checkbox" name="{{ $field }}" value="1" {{ old($field) ? 'checked' : '' }}>
                                                <span>{{ $label }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>

                                <div class="form-block">
                                    <label class="form-label">Negative flags</label>
                                    <div class="choice-grid">
                                        @foreach($issueFlags as $field => $label)
                                            <label class="check-pill">
                                                <input type="checkbox" name="{{ $field }}" value="1" {{ old($field) ? 'checked' : '' }}>
                                                <span>{{ $label }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>

                                <div class="form-block full">
                                    <label class="form-label" for="comment-{{ $booking->booking_id }}">Written feedback</label>
                                    <textarea class="text-area" id="comment-{{ $booking->booking_id }}" name="comment" placeholder="Share clear notes about the customer, booking accuracy, or any issue onsite.">{{ old('comment') }}</textarea>
                                </div>

                                <div class="form-block full">
                                    <label class="form-label" for="attachment-{{ $booking->booking_id }}">Photo or file evidence</label>
                                    <input class="file-input" id="attachment-{{ $booking->booking_id }}" type="file" name="attachment" accept=".jpg,.jpeg,.png,.webp,.pdf">
                                    <div class="field-help">Optional image or PDF if you need proof for mismatch, behavior, or payment concerns.</div>
                                </div>
                            </div>

                            <div class="form-actions">
                                <button class="btn-rate primary" type="submit">Submit Customer Rating</button>
                            </div>
                        </form>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <div class="shell-card section-card">
        <div class="section-head">
            <div>
                <h2 class="section-title">Submitted Ratings</h2>
                <p class="section-copy">Review your customer ratings, attached evidence, and edit any rating that is still within the allowed update window.</p>
            </div>
            <div class="section-pill">{{ $submittedRatings->count() }}</div>
        </div>

        @if($submittedRatings->isEmpty())
            <div class="empty-card">You have not submitted any customer ratings yet.</div>
        @else
            <div class="cards-stack">
                @foreach($submittedRatings as $rating)
                    @php
                        $ratingAttachment = $attachmentUrl($rating);
                        $editableUntil = !empty($rating->editable_until) ? Carbon::parse($rating->editable_until) : null;
                    @endphp
                    <div class="rating-card">
                        <div class="booking-top">
                            <div>
                                <h3 class="booking-reference">{{ $rating->reference_code }}</h3>
                                <div class="booking-subtext">{{ $rating->customer_name }} / {{ $rating->service_name }}@if(!empty($rating->option_name)) / {{ $rating->option_name }}@endif</div>
                            </div>
                            <div class="status-badge submitted"><i class="bi bi-check2-circle"></i> Submitted</div>
                        </div>

                        <div class="booking-meta">
                            <div class="meta-pill"><i class="bi bi-calendar-event"></i> {{ $formatDate($rating->booking_date) }}</div>
                            <div class="meta-pill"><i class="bi bi-star-fill"></i> {{ $rating->rating }}/5 {{ $ratingWord($rating->rating) }}</div>
                            <div class="meta-pill"><i class="bi bi-arrow-repeat"></i> Edit count: {{ (int) $rating->edit_count }}</div>
                        </div>

                        <div class="flags-row">
                            @foreach($positiveFlags as $field => $label)
                                @if(!empty($rating->{$field}))
                                    <div class="flag-chip good"><i class="bi bi-check2-circle"></i> {{ $label }}</div>
                                @endif
                            @endforeach
                            @foreach($issueFlags as $field => $label)
                                @if(!empty($rating->{$field}))
                                    <div class="flag-chip issue"><i class="bi bi-exclamation-triangle"></i> {{ $label }}</div>
                                @endif
                            @endforeach
                        </div>

                        @if(!empty($rating->comment))
                            <div class="review-comment">{{ $rating->comment }}</div>
                        @endif

                        @if($ratingAttachment)
                            <div class="attachment-row">
                                @if(str_starts_with((string) $rating->attachment_mime, 'image/'))
                                    <img class="attachment-thumb" src="{{ $ratingAttachment }}" alt="Customer rating attachment">
                                @endif
                                <a class="attachment-link" href="{{ $ratingAttachment }}" target="_blank" rel="noopener">
                                    {{ $rating->attachment_name ?: 'Open attachment' }}
                                </a>
                            </div>
                        @endif

                        <div class="edit-window">
                            @if($rating->can_edit && $editableUntil)
                                Editable until {{ $editableUntil->format('M d, Y h:i A') }}
                            @elseif($editableUntil)
                                Edit window closed on {{ $editableUntil->format('M d, Y h:i A') }}
                            @endif
                        </div>

                        @if($rating->can_edit)
                            <form class="form-shell rating-form" method="POST" action="{{ route('provider.customer-ratings.update', $rating->rating_id) }}" enctype="multipart/form-data">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="rating" value="{{ (int) $rating->rating }}" class="rating-input">

                                <div class="form-grid">
                                    <div class="form-block full">
                                        <label class="form-label">Update overall customer rating</label>
                                        <div class="stars-row">
                                            <div class="stars">
                                                @for($star = 1; $star <= 5; $star++)
                                                    <button type="button" class="star-btn {{ (int) $rating->rating >= $star ? 'on' : '' }}" data-star="{{ $star }}" aria-label="Rate {{ $star }}">
                                                        <svg class="star-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2.75l2.91 5.89 6.5.95-4.7 4.58 1.11 6.47L12 17.59 6.18 20.64l1.11-6.47-4.7-4.58 6.5-.95L12 2.75z"/></svg>
                                                    </button>
                                                @endfor
                                            </div>
                                            <div class="rating-selected">{{ $ratingWord($rating->rating) }}</div>
                                        </div>
                                    </div>

                                    <div class="form-block">
                                        <label class="form-label">Positive assessment</label>
                                        <div class="choice-grid">
                                            @foreach($positiveFlags as $field => $label)
                                                <label class="check-pill">
                                                    <input type="checkbox" name="{{ $field }}" value="1" {{ !empty($rating->{$field}) ? 'checked' : '' }}>
                                                    <span>{{ $label }}</span>
                                                </label>
                                            @endforeach
                                        </div>
                                    </div>

                                    <div class="form-block">
                                        <label class="form-label">Negative flags</label>
                                        <div class="choice-grid">
                                            @foreach($issueFlags as $field => $label)
                                                <label class="check-pill">
                                                    <input type="checkbox" name="{{ $field }}" value="1" {{ !empty($rating->{$field}) ? 'checked' : '' }}>
                                                    <span>{{ $label }}</span>
                                                </label>
                                            @endforeach
                                        </div>
                                    </div>

                                    <div class="form-block full">
                                        <label class="form-label" for="edit-comment-{{ $rating->rating_id }}">Written feedback</label>
                                        <textarea class="text-area" id="edit-comment-{{ $rating->rating_id }}" name="comment" placeholder="Update your provider notes for this customer.">{{ $rating->comment }}</textarea>
                                    </div>

                                    <div class="form-block full">
                                        <label class="form-label" for="edit-attachment-{{ $rating->rating_id }}">Replace attachment</label>
                                        <input class="file-input" id="edit-attachment-{{ $rating->rating_id }}" type="file" name="attachment" accept=".jpg,.jpeg,.png,.webp,.pdf">
                                        <div class="field-help">Leave this empty if you want to keep the current file.</div>
                                    </div>
                                </div>

                                <div class="form-actions">
                                    <button class="btn-rate primary" type="submit">Update Rating</button>
                                </div>
                            </form>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.rating-form').forEach(function (form) {
        const input = form.querySelector('.rating-input');
        const label = form.querySelector('.rating-selected');
        const buttons = Array.from(form.querySelectorAll('.star-btn'));

        if (!input || !buttons.length) return;

        function updateStars(value) {
            buttons.forEach(function (button) {
                const star = Number(button.dataset.star || 0);
                button.classList.toggle('on', star <= value);
            });

            if (!label) return;

            const words = {1:'Poor',2:'Needs Work',3:'Good',4:'Very Good',5:'Excellent'};
            label.textContent = words[value] || 'Select a rating';
        }

        updateStars(Number(input.value || 0));

        buttons.forEach(function (button) {
            button.addEventListener('click', function () {
                const value = Number(button.dataset.star || 0);
                input.value = value;
                updateStars(value);
            });
        });
    });

    const focused = document.querySelector('.rating-card.focused');
    if (focused) {
        focused.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
});
</script>
@endsection
