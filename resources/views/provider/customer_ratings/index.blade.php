@extends('provider.layouts.app')

@section('title', 'Rate Customers')

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
    $filteredSummary = $filteredSummary ?? (object) [
        'matches' => $pendingBookings->count() + $submittedRatings->count(),
        'pending_ratings' => $pendingBookings->count(),
        'submitted_ratings' => $submittedRatings->count(),
    ];
    $q = trim((string) ($q ?? request('q', '')));
    $sort = (string) ($sort ?? request('sort', 'customer_asc'));
    $focusBooking = (int) request('booking', 0);
    $focusedBookingId = $focusBooking ?: (int) old('booking_id', 0);
    $focusedEditRatingId = (int) old('edit_rating_id', 0);
    $sortOptions = [
        'customer_asc' => 'Customer name (A to Z)',
        'customer_desc' => 'Customer name (Z to A)',
        'rating_high' => 'Highest ratings first',
        'rating_low' => 'Lowest ratings first',
        'latest' => 'Newest activity first',
        'oldest' => 'Oldest activity first',
    ];
    $resetParams = $focusBooking > 0 ? ['booking' => $focusBooking] : [];

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
        'booking_details_accurate' => 'Booking details matched the job',
        'respectful' => 'Customer was respectful',
        'easy_to_communicate' => 'Easy to communicate with',
        'paid_reliably' => 'Payment was smooth and on time',
    ];

    $issueFlags = [
        'unexpected_extra_work' => 'Asked for extra work not in the booking',
        'flag_understated_area' => 'Area size was understated',
        'flag_hidden_sections' => 'Extra rooms or sections showed up onsite',
        'flag_misleading_request' => 'Service request was misleading',
        'flag_difficult_behavior' => 'Customer behavior was difficult',
        'flag_payment_issue' => 'Payment issue happened',
        'flag_last_minute_changes' => 'Too many last-minute changes',
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
.page-hero,.section-card{padding:1.15rem 1.2rem}
.hero-top,.section-head,.booking-top,.stars-row,.toolbar-top,.toolbar-actions{display:flex;align-items:flex-start;justify-content:space-between;gap:1rem;flex-wrap:wrap}
.hero-title,.section-title,.booking-reference{margin:0;font-weight:900;letter-spacing:-.03em}
.hero-title{font-size:1.42rem}.section-title{font-size:1.08rem}.booking-reference{font-size:1.14rem}
.hero-subtitle,.section-copy,.toolbar-copy,.edit-window,.field-help,.form-intro{margin:.35rem 0 0;color:var(--cr-muted);font-size:.92rem;line-height:1.55}
.hero-chip,.section-pill,.status-badge,.meta-pill,.flag-chip{display:inline-flex;align-items:center;gap:.42rem;border-radius:999px;font-weight:900}
.hero-chip{min-height:38px;padding:.5rem .82rem;border:1px solid rgba(56,189,248,.2);background:rgba(56,189,248,.1);color:#e0f2fe;font-size:.84rem}
.summary-grid{display:grid;grid-template-columns:repeat(4, minmax(0,1fr));gap:.8rem;margin-top:1rem}
.summary-card{padding:1rem}
.summary-label,.detail-label,.form-label{color:var(--cr-muted);font-size:.76rem;font-weight:800;letter-spacing:.1em;text-transform:uppercase}
.summary-value{margin-top:.5rem;font-size:1.55rem;font-weight:900;line-height:1}
.summary-note{margin-top:.4rem;color:var(--cr-muted);font-size:.82rem}
.summary-value.accent{color:var(--cr-accent)}.summary-value.success{color:#86efac}.summary-value.warn{color:#fdba74}
.section-pill{min-width:40px;min-height:40px;padding:0 .85rem;border:1px solid rgba(56,189,248,.18);background:rgba(56,189,248,.08);color:#dff7ff;font-size:.84rem}
.toolbar-grid{display:grid;grid-template-columns:minmax(0,2fr) minmax(220px,1fr) auto;gap:.85rem;align-items:end;margin-top:1rem}
.field{min-width:0}
.field-input{width:100%;min-height:48px;padding:.8rem .9rem;border-radius:14px;border:1px solid rgba(255,255,255,.08);background:rgba(2,6,23,.45);color:#fff}
.field-input::placeholder{color:rgba(255,255,255,.35)}
.field-input:focus{outline:none;border-color:rgba(56,189,248,.3);box-shadow:0 0 0 3px rgba(56,189,248,.08)}
.toolbar-actions{margin-top:.9rem;align-items:center}
.results-copy{color:var(--cr-muted);font-size:.88rem;line-height:1.5}
.cards-stack{display:flex;flex-direction:column;gap:1rem}
.rating-card{padding:1rem}
.rating-card.focused{border-color:rgba(56,189,248,.35);box-shadow:0 0 0 3px rgba(56,189,248,.08),0 22px 40px rgba(0,0,0,.3)}
.card-eyebrow{color:#7dd3fc;font-size:.78rem;font-weight:900;letter-spacing:.08em;text-transform:uppercase}
.booking-subtext{margin-top:.35rem;color:var(--cr-muted);font-size:.9rem;line-height:1.55}
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
.detail-value{margin-top:.38rem;font-size:.94rem;font-weight:800;line-height:1.55;word-break:break-word}
.detail-subvalue{margin-top:.2rem;color:var(--cr-muted);font-size:.84rem;line-height:1.5}
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
.flag-chip{padding:.38rem .68rem;border:1px solid rgba(255,255,255,.08);background:rgba(255,255,255,.03);color:#e2e8f0;font-size:.78rem;line-height:1.4}
.flag-chip.issue{border-color:rgba(239,68,68,.2);background:rgba(239,68,68,.08);color:#fecaca}
.flag-chip.good{border-color:rgba(34,197,94,.18);background:rgba(34,197,94,.08);color:#bbf7d0}
.review-comment{margin-top:1rem;color:rgba(255,255,255,.9);line-height:1.65}
.comment-body{margin-top:.45rem;font-size:.94rem}
.empty-card{text-align:center;color:var(--cr-muted);font-weight:800;padding:1.4rem}
.flash,.error-box{padding:.95rem 1rem;border-radius:16px;border:1px solid rgba(255,255,255,.08);font-weight:800}
.flash{background:rgba(34,197,94,.08);border-color:rgba(34,197,94,.18);color:#bbf7d0}
.error-box{background:rgba(239,68,68,.08);border-color:rgba(239,68,68,.18);color:#fecaca}
@media (max-width:1100px){.summary-grid{grid-template-columns:repeat(2,minmax(0,1fr))}.toolbar-grid{grid-template-columns:1fr 1fr}.toolbar-grid .search-field,.toolbar-grid .apply-field{grid-column:1 / -1}.form-grid,.details-grid,.choice-grid{grid-template-columns:1fr}}
@media (max-width:640px){.summary-grid{grid-template-columns:1fr}.hero-title{font-size:1.22rem}.toolbar-grid{grid-template-columns:1fr}.toolbar-grid .search-field,.toolbar-grid .apply-field{grid-column:auto}.toolbar-actions,.form-actions{flex-direction:column}.btn-rate{width:100%}.status-badge{white-space:normal}}
</style>

<div class="customer-ratings-page">
    @if(session('success'))
        <div class="flash">{{ session('success') }}</div>
    @endif

    @if($errors->has('customer_rating'))
        <div class="error-box">{{ $errors->first('customer_rating') }}</div>
    @endif

    @if($errors->any() && !$errors->has('customer_rating'))
        <div class="error-box">Please check the highlighted review form and try again.</div>
    @endif

    <div class="shell-card page-hero">
        <div class="hero-top">
            <div>
                <h1 class="hero-title">Rate Customers</h1>
                <p class="hero-subtitle">Find completed jobs quickly, leave a clear customer review, and keep older feedback easy to revisit.</p>
            </div>
            <div class="hero-chip"><i class="bi bi-sort-alpha-down"></i> Pending reviews always stay first</div>
        </div>

        <div class="summary-grid">
            <div class="summary-card">
                <div class="summary-label">Completed Jobs</div>
                <div class="summary-value">{{ $summary->completed_bookings }}</div>
                <div class="summary-note">Bookings eligible for review</div>
            </div>
            <div class="summary-card">
                <div class="summary-label">Ready to Review</div>
                <div class="summary-value warn">{{ $summary->pending_ratings }}</div>
                <div class="summary-note">Still waiting on your rating</div>
            </div>
            <div class="summary-card">
                <div class="summary-label">Saved Reviews</div>
                <div class="summary-value accent">{{ $summary->submitted_ratings }}</div>
                <div class="summary-note">Already stored in history</div>
            </div>
            <div class="summary-card">
                <div class="summary-label">Can Still Edit</div>
                <div class="summary-value success">{{ $summary->editable_ratings }}</div>
                <div class="summary-note">Inside the 24-hour edit window</div>
            </div>
        </div>
    </div>

    <div class="shell-card section-card">
        <div class="toolbar-top">
            <div>
                <h2 class="section-title">Find a booking fast</h2>
                <p class="toolbar-copy">Search for a customer, filter how saved reviews are ordered, and keep pending reviews at the top.</p>
            </div>
            <div class="hero-chip"><i class="bi bi-search"></i> Search by customer, email, ref, or service</div>
        </div>

        <form method="GET" action="{{ route('provider.customer-ratings') }}">
            @if($focusBooking > 0)
                <input type="hidden" name="booking" value="{{ $focusBooking }}">
            @endif

            <div class="toolbar-grid">
                <div class="field search-field">
                    <label class="form-label" for="ratings-search">Search</label>
                    <input class="field-input" id="ratings-search" type="text" name="q" value="{{ $q }}" placeholder="Customer, email, reference, service, or note">
                </div>
                <div class="field">
                    <label class="form-label" for="ratings-sort">Sort saved reviews</label>
                    <select class="field-input" id="ratings-sort" name="sort">
                        @foreach($sortOptions as $value => $label)
                            <option value="{{ $value }}" {{ $sort === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="field apply-field">
                    <label class="form-label">&nbsp;</label>
                    <button class="btn-rate primary" type="submit">Apply Filters</button>
                </div>
            </div>

            <div class="toolbar-actions">
                <a class="btn-rate secondary" href="{{ route('provider.customer-ratings', $resetParams) }}">Reset</a>
                <div class="results-copy">
                    Showing {{ $filteredSummary->matches }} bookings:
                    {{ $filteredSummary->pending_ratings }} pending and
                    {{ $filteredSummary->submitted_ratings }} saved.
                </div>
            </div>
        </form>
    </div>

    <div class="shell-card section-card">
        <div class="section-head">
            <div>
                <h2 class="section-title">Pending Reviews</h2>
                <p class="section-copy">These jobs are ready for a customer review now.</p>
            </div>
            <div class="section-pill">{{ $pendingBookings->count() }}</div>
        </div>

        @if($pendingBookings->isEmpty())
            <div class="empty-card">
                {{ $q !== '' ? 'No pending reviews matched this search.' : 'No completed bookings are waiting for a customer review.' }}
            </div>
        @else
            <div class="cards-stack">
                @foreach($pendingBookings as $booking)
                    @php
                        $isFocusedBooking = $focusedBookingId === (int) $booking->booking_id;
                        $isOldPendingForm = (int) old('booking_id') === (int) $booking->booking_id;
                        $pendingRatingValue = $isOldPendingForm ? (int) old('rating', 0) : 0;
                    @endphp
                    <div class="rating-card {{ $isFocusedBooking ? 'focused' : '' }}" id="booking-{{ $booking->booking_id }}">
                        <div class="booking-top">
                            <div>
                                <div class="card-eyebrow">Ready to review</div>
                                <h3 class="booking-reference">{{ $booking->customer_name }}</h3>
                                <div class="booking-subtext">
                                    {{ $booking->service_name }}@if(!empty($booking->option_name)) / {{ $booking->option_name }}@endif
                                    - Ref {{ $booking->reference_code }}
                                </div>
                            </div>
                            <div class="status-badge pending"><i class="bi bi-clock-history"></i> Pending Review</div>
                        </div>

                        <div class="booking-meta">
                            <div class="meta-pill"><i class="bi bi-calendar-event"></i> {{ $formatDate($booking->booking_date) }}</div>
                            <div class="meta-pill price"><i class="bi bi-cash-stack"></i> PHP {{ number_format((float) $booking->price, 2) }}</div>
                            <div class="meta-pill"><i class="bi bi-check2-circle"></i> {{ strtoupper((string) $booking->booking_status) }}</div>
                        </div>

                        <div class="details-grid">
                            <div class="detail-box">
                                <div class="detail-label">Customer Contact</div>
                                <div class="detail-value">{{ $booking->customer_name }}</div>
                                <div class="detail-subvalue">{{ $booking->customer_email ?: 'No email on file' }}</div>
                                @if(!empty($booking->customer_phone))
                                    <div class="detail-subvalue">{{ $booking->customer_phone }}</div>
                                @endif
                            </div>
                            <div class="detail-box">
                                <div class="detail-label">Booked Service</div>
                                <div class="detail-value">{{ $booking->service_name }}</div>
                                @if(!empty($booking->option_name))
                                    <div class="detail-subvalue">{{ $booking->option_name }}</div>
                                @endif
                            </div>
                        </div>

                        <form class="form-shell rating-form" method="POST" action="{{ route('provider.customer-ratings.store') }}" enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" name="booking_id" value="{{ $booking->booking_id }}">
                            <input type="hidden" name="rating" value="{{ $pendingRatingValue }}" class="rating-input">

                            <div class="form-intro">Pick a star rating, then add any quick notes that will help you remember this booking later.</div>

                            <div class="form-grid">
                                <div class="form-block full">
                                    <label class="form-label">Overall customer rating</label>
                                    <div class="stars-row">
                                        <div class="stars">
                                            @for($star = 1; $star <= 5; $star++)
                                                <button type="button" class="star-btn {{ $pendingRatingValue >= $star ? 'on' : '' }}" data-star="{{ $star }}" aria-label="Rate {{ $star }}">
                                                    <svg class="star-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2.75l2.91 5.89 6.5.95-4.7 4.58 1.11 6.47L12 17.59 6.18 20.64l1.11-6.47-4.7-4.58 6.5-.95L12 2.75z"/></svg>
                                                </button>
                                            @endfor
                                        </div>
                                        <div class="rating-selected">{{ $ratingWord($pendingRatingValue) }}</div>
                                    </div>
                                </div>

                                <div class="form-block">
                                    <label class="form-label">What went well</label>
                                    <div class="choice-grid">
                                        @foreach($positiveFlags as $field => $label)
                                            <label class="check-pill">
                                                <input type="checkbox" name="{{ $field }}" value="1" {{ $isOldPendingForm && old($field) ? 'checked' : '' }}>
                                                <span>{{ $label }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>

                                <div class="form-block">
                                    <label class="form-label">Issues to note</label>
                                    <div class="choice-grid">
                                        @foreach($issueFlags as $field => $label)
                                            <label class="check-pill">
                                                <input type="checkbox" name="{{ $field }}" value="1" {{ $isOldPendingForm && old($field) ? 'checked' : '' }}>
                                                <span>{{ $label }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>

                                <div class="form-block full">
                                    <label class="form-label" for="comment-{{ $booking->booking_id }}">Short note</label>
                                    <textarea class="text-area" id="comment-{{ $booking->booking_id }}" name="comment" placeholder="Share a short note about the customer, booking accuracy, payment, or anything that stood out.">{{ $isOldPendingForm ? old('comment') : '' }}</textarea>
                                </div>

                                <div class="form-block full">
                                    <label class="form-label" for="attachment-{{ $booking->booking_id }}">Optional proof</label>
                                    <input class="file-input" id="attachment-{{ $booking->booking_id }}" type="file" name="attachment" accept=".jpg,.jpeg,.png,.webp,.pdf">
                                    <div class="field-help">Upload an image or PDF if you want backup for a mismatch, behavior issue, or payment concern.</div>
                                </div>
                            </div>

                            <div class="form-actions">
                                <button class="btn-rate primary" type="submit">Save Review</button>
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
                <h2 class="section-title">Saved Reviews</h2>
                <p class="section-copy">Look back on past customer reviews, attached proof, and any review that is still editable.</p>
            </div>
            <div class="section-pill">{{ $submittedRatings->count() }}</div>
        </div>

        @if($submittedRatings->isEmpty())
            <div class="empty-card">
                {{ $q !== '' ? 'No saved reviews matched this search.' : 'You have not saved any customer reviews yet.' }}
            </div>
        @else
            <div class="cards-stack">
                @foreach($submittedRatings as $rating)
                    @php
                        $ratingAttachment = $attachmentUrl($rating);
                        $editableUntil = !empty($rating->editable_until) ? Carbon::parse($rating->editable_until) : null;
                        $selectedGoodFlags = collect($positiveFlags)
                            ->filter(fn ($label, $field) => !empty($rating->{$field}));
                        $selectedIssueFlags = collect($issueFlags)
                            ->filter(fn ($label, $field) => !empty($rating->{$field}));
                        $isFocusedRating = $focusedEditRatingId === (int) $rating->rating_id;
                        $isOldEditForm = $focusedEditRatingId === (int) $rating->rating_id;
                        $editRatingValue = $isOldEditForm ? (int) old('rating', (int) $rating->rating) : (int) $rating->rating;
                    @endphp
                    <div class="rating-card {{ $isFocusedRating ? 'focused' : '' }}">
                        <div class="booking-top">
                            <div>
                                <div class="card-eyebrow">Saved review</div>
                                <h3 class="booking-reference">{{ $rating->customer_name }}</h3>
                                <div class="booking-subtext">
                                    {{ $rating->service_name }}@if(!empty($rating->option_name)) / {{ $rating->option_name }}@endif
                                    - Ref {{ $rating->reference_code }}
                                </div>
                            </div>
                            <div class="status-badge submitted"><i class="bi bi-check2-circle"></i> Review Saved</div>
                        </div>

                        <div class="booking-meta">
                            <div class="meta-pill"><i class="bi bi-calendar-event"></i> {{ $formatDate($rating->booking_date) }}</div>
                            <div class="meta-pill"><i class="bi bi-star-fill"></i> {{ $editRatingValue }}/5 {{ $ratingWord($editRatingValue) }}</div>
                            @if((int) $rating->edit_count > 0)
                                <div class="meta-pill"><i class="bi bi-arrow-repeat"></i> Updated {{ (int) $rating->edit_count }} time{{ (int) $rating->edit_count === 1 ? '' : 's' }}</div>
                            @endif
                        </div>

                        @if($selectedGoodFlags->isNotEmpty() || $selectedIssueFlags->isNotEmpty())
                            <div class="details-grid">
                                @if($selectedGoodFlags->isNotEmpty())
                                    <div class="detail-box">
                                        <div class="detail-label">What went well</div>
                                        <div class="flags-row">
                                            @foreach($selectedGoodFlags as $label)
                                                <div class="flag-chip good"><i class="bi bi-check2-circle"></i> {{ $label }}</div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif

                                @if($selectedIssueFlags->isNotEmpty())
                                    <div class="detail-box">
                                        <div class="detail-label">Issues to note</div>
                                        <div class="flags-row">
                                            @foreach($selectedIssueFlags as $label)
                                                <div class="flag-chip issue"><i class="bi bi-exclamation-triangle"></i> {{ $label }}</div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @endif

                        @if(!empty($rating->comment))
                            <div class="review-comment">
                                <div class="detail-label">Short note</div>
                                <div class="comment-body">{{ $rating->comment }}</div>
                            </div>
                        @endif

                        @if($ratingAttachment)
                            <div class="review-comment">
                                <div class="detail-label">Attached proof</div>
                                <div class="attachment-row">
                                    @if(str_starts_with((string) $rating->attachment_mime, 'image/'))
                                        <img class="attachment-thumb" src="{{ $ratingAttachment }}" alt="Customer rating attachment">
                                    @endif
                                    <a class="attachment-link" href="{{ $ratingAttachment }}" target="_blank" rel="noopener">
                                        {{ $rating->attachment_name ?: 'Open attachment' }}
                                    </a>
                                </div>
                            </div>
                        @endif

                        <div class="edit-window">
                            @if($rating->can_edit && $editableUntil)
                                You can still edit this review until {{ $editableUntil->format('M d, Y h:i A') }}.
                            @elseif($editableUntil)
                                The edit window closed on {{ $editableUntil->format('M d, Y h:i A') }}.
                            @endif
                        </div>

                        @if($rating->can_edit)
                            <form class="form-shell rating-form" method="POST" action="{{ route('provider.customer-ratings.update', $rating->rating_id) }}" enctype="multipart/form-data">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="edit_rating_id" value="{{ $rating->rating_id }}">
                                <input type="hidden" name="booking_id" value="{{ $rating->booking_id }}">
                                <input type="hidden" name="rating" value="{{ $editRatingValue }}" class="rating-input">

                                <div class="form-intro">Update the score, refresh your note, or replace the proof file if you need to correct anything.</div>

                                <div class="form-grid">
                                    <div class="form-block full">
                                        <label class="form-label">Update customer rating</label>
                                        <div class="stars-row">
                                            <div class="stars">
                                                @for($star = 1; $star <= 5; $star++)
                                                    <button type="button" class="star-btn {{ $editRatingValue >= $star ? 'on' : '' }}" data-star="{{ $star }}" aria-label="Rate {{ $star }}">
                                                        <svg class="star-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2.75l2.91 5.89 6.5.95-4.7 4.58 1.11 6.47L12 17.59 6.18 20.64l1.11-6.47-4.7-4.58 6.5-.95L12 2.75z"/></svg>
                                                    </button>
                                                @endfor
                                            </div>
                                            <div class="rating-selected">{{ $ratingWord($editRatingValue) }}</div>
                                        </div>
                                    </div>

                                    <div class="form-block">
                                        <label class="form-label">What went well</label>
                                        <div class="choice-grid">
                                            @foreach($positiveFlags as $field => $label)
                                                @php
                                                    $checked = $isOldEditForm ? old($field) : !empty($rating->{$field});
                                                @endphp
                                                <label class="check-pill">
                                                    <input type="checkbox" name="{{ $field }}" value="1" {{ $checked ? 'checked' : '' }}>
                                                    <span>{{ $label }}</span>
                                                </label>
                                            @endforeach
                                        </div>
                                    </div>

                                    <div class="form-block">
                                        <label class="form-label">Issues to note</label>
                                        <div class="choice-grid">
                                            @foreach($issueFlags as $field => $label)
                                                @php
                                                    $checked = $isOldEditForm ? old($field) : !empty($rating->{$field});
                                                @endphp
                                                <label class="check-pill">
                                                    <input type="checkbox" name="{{ $field }}" value="1" {{ $checked ? 'checked' : '' }}>
                                                    <span>{{ $label }}</span>
                                                </label>
                                            @endforeach
                                        </div>
                                    </div>

                                    <div class="form-block full">
                                        <label class="form-label" for="edit-comment-{{ $rating->rating_id }}">Short note</label>
                                        <textarea class="text-area" id="edit-comment-{{ $rating->rating_id }}" name="comment" placeholder="Update your note for this customer.">{{ $isOldEditForm ? old('comment', $rating->comment) : $rating->comment }}</textarea>
                                    </div>

                                    <div class="form-block full">
                                        <label class="form-label" for="edit-attachment-{{ $rating->rating_id }}">Replace proof file</label>
                                        <input class="file-input" id="edit-attachment-{{ $rating->rating_id }}" type="file" name="attachment" accept=".jpg,.jpeg,.png,.webp,.pdf">
                                        <div class="field-help">Leave this empty if the current attachment is still fine.</div>
                                    </div>
                                </div>

                                <div class="form-actions">
                                    <button class="btn-rate primary" type="submit">Update Review</button>
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
