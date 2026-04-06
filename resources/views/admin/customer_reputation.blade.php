@extends('admin.layouts.app')

@section('title', 'Customer Reputation')

@section('content')
@php
    use Carbon\Carbon;

    $customers = collect($customers ?? []);
    $topCustomers = collect($topCustomers ?? []);
    $problematicCustomers = collect($problematicCustomers ?? []);
    $history = $history ?? collect();
    $suspiciousRatings = collect($suspiciousRatings ?? []);
    $summary = $summary ?? (object) [
        'customers' => 0,
        'rated_customers' => 0,
        'avg_rating' => 0,
        'high_risk' => 0,
        'mismatches' => 0,
        'complaints' => 0,
        'suspicious_pending' => 0,
        'suspicious_reviewed' => 0,
    ];

    $ratingWords = function ($value) {
        $value = round((float) $value, 1);

        if ($value >= 4.5) return 'Excellent';
        if ($value >= 4.0) return 'Very Good';
        if ($value >= 3.0) return 'Good';
        if ($value > 0) return 'Needs Review';

        return 'No ratings';
    };

    $issueLabels = [
        'booking_details_accurate' => 'Accurate details',
        'respectful' => 'Respectful',
        'easy_to_communicate' => 'Easy communication',
        'paid_reliably' => 'Paid reliably',
        'unexpected_extra_work' => 'Unexpected extra work',
        'flag_understated_area' => 'Understated area',
        'flag_hidden_sections' => 'Hidden sections',
        'flag_misleading_request' => 'Misleading request',
        'flag_difficult_behavior' => 'Difficult behavior',
        'flag_payment_issue' => 'Payment issue',
        'flag_last_minute_changes' => 'Last-minute changes',
    ];
@endphp

<style>
:root{
    --rep-border:rgba(255,255,255,.08);
    --rep-text:#f8fafc;
    --rep-muted:#94a3b8;
    --rep-accent:#38bdf8;
}

.reputation-page{display:flex;flex-direction:column;gap:1rem;color:var(--rep-text)}
.rep-card{background:linear-gradient(180deg, rgba(7,18,37,.97), rgba(2,6,23,.99));border:1px solid var(--rep-border);border-radius:24px;box-shadow:0 18px 36px rgba(0,0,0,.26)}
.rep-hero,.toolbar-card,.panel-card,.table-card{padding:1rem 1.1rem}
.rep-head,.panel-head{display:flex;align-items:flex-start;justify-content:space-between;gap:1rem;flex-wrap:wrap}
.rep-title,.panel-title{margin:0;font-weight:900}
.rep-title{font-size:1.36rem}
.panel-title{font-size:1rem}
.rep-subtitle,.panel-sub{margin:.35rem 0 0;color:var(--rep-muted);font-size:.9rem;line-height:1.55}
.rep-chip,.rating-badge,.risk-badge,.score-pill,.view-btn,.btn-apply,.btn-clear{display:inline-flex;align-items:center;justify-content:center}
.rep-chip{gap:.45rem;min-height:38px;padding:.5rem .82rem;border-radius:999px;border:1px solid rgba(56,189,248,.22);background:rgba(56,189,248,.1);color:#dff7ff;font-size:.84rem;font-weight:900}
.summary-grid{display:grid;grid-template-columns:repeat(6,minmax(0,1fr));gap:.8rem;margin-top:1rem}
.summary-box,.mini-card,.history-card{padding:.95rem 1rem;border-radius:18px;background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.06)}
.summary-label{color:var(--rep-muted);font-size:.74rem;font-weight:800;letter-spacing:.09em;text-transform:uppercase}
.summary-value{margin-top:.48rem;font-size:1.55rem;font-weight:900;line-height:1}
.summary-value.accent{color:#38bdf8}.summary-value.success{color:#86efac}.summary-value.warn{color:#fdba74}.summary-value.danger{color:#fca5a5}
.toolbar-grid{display:grid;grid-template-columns:minmax(240px,1.6fr) 180px 220px auto;gap:.8rem;align-items:end}
.field{display:flex;flex-direction:column;gap:.42rem}
.field label{color:var(--rep-muted);font-size:.75rem;font-weight:800;letter-spacing:.08em;text-transform:uppercase}
.rep-input,.rep-select{width:100%;min-height:46px;padding:.75rem .9rem;border-radius:14px;border:1px solid rgba(255,255,255,.08);background:rgba(2,6,23,.45);color:#fff}
.rep-input:focus,.rep-select:focus{outline:none;border-color:rgba(56,189,248,.28);box-shadow:0 0 0 3px rgba(56,189,248,.08)}
.rep-select option{background:#071225;color:#f8fafc}
.btn-apply,.btn-clear{min-height:46px;padding:.75rem 1rem;border-radius:14px;font-weight:900;text-decoration:none}
.btn-apply{border:1px solid rgba(56,189,248,.24);background:linear-gradient(135deg,#2563eb,#38bdf8);color:#fff}
.btn-clear{border:1px solid rgba(255,255,255,.1);background:rgba(255,255,255,.03);color:#fff}
.insight-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:1rem}
.stack{display:flex;flex-direction:column;gap:.75rem}
.mini-top,.history-top{display:flex;align-items:flex-start;justify-content:space-between;gap:.8rem;flex-wrap:wrap}
.mini-name,.history-title,.customer-name{font-size:.95rem;font-weight:900;line-height:1.35}
.mini-meta,.customer-sub,.history-meta{margin-top:.28rem;color:var(--rep-muted);font-size:.82rem;line-height:1.5}
.detail-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:.72rem;margin-top:.85rem}
.detail-card{padding:.78rem .82rem;border-radius:14px;border:1px solid rgba(255,255,255,.06);background:rgba(2,6,23,.38)}
.detail-label{color:var(--rep-muted);font-size:.7rem;font-weight:800;letter-spacing:.09em;text-transform:uppercase}
.detail-value{margin-top:.3rem;color:#f8fafc;font-size:.88rem;font-weight:800;line-height:1.45}
.rating-badge{gap:.35rem;padding:.32rem .6rem;border-radius:999px;border:1px solid rgba(251,191,36,.2);background:rgba(251,191,36,.1);color:#fde68a;font-size:.76rem;font-weight:900;white-space:nowrap}
.risk-badge{gap:.35rem;padding:.34rem .68rem;border-radius:999px;border:1px solid rgba(255,255,255,.08);background:rgba(255,255,255,.04);font-size:.76rem;font-weight:900}
.risk-badge.low{border-color:rgba(34,197,94,.22);background:rgba(34,197,94,.1);color:#bbf7d0}
.risk-badge.medium{border-color:rgba(245,158,11,.22);background:rgba(245,158,11,.1);color:#fde68a}
.risk-badge.high{border-color:rgba(239,68,68,.22);background:rgba(239,68,68,.1);color:#fecaca}
.table-wrap{overflow:auto;border-radius:18px;border:1px solid rgba(255,255,255,.06)}
.table-wrap table{width:100%;min-width:1080px;border-collapse:collapse}
.table-wrap thead{background:rgba(56,189,248,.08)}
.table-wrap th,.table-wrap td{padding:.88rem .9rem;border-bottom:1px solid rgba(255,255,255,.06);vertical-align:top}
.table-wrap th{color:#cbd5e1;font-size:.74rem;font-weight:900;letter-spacing:.09em;text-transform:uppercase;white-space:nowrap}
.table-wrap td{color:#f8fafc;font-size:.9rem}
.tiny-value{font-size:.84rem;font-weight:800}
.score-pill{min-width:62px;min-height:34px;padding:0 .72rem;border-radius:999px;border:1px solid rgba(56,189,248,.18);background:rgba(56,189,248,.08);color:#dff7ff;font-size:.8rem;font-weight:900}
.view-btn{min-height:40px;padding:.6rem .9rem;border-radius:12px;border:1px solid rgba(56,189,248,.18);background:rgba(56,189,248,.08);color:#fff;font-size:.82rem;font-weight:900}
.history-list{display:flex;flex-direction:column;gap:.85rem}
.history-flags,.history-attachment{display:flex;flex-wrap:wrap;gap:.5rem;margin-top:.85rem}
.history-flag{display:inline-flex;align-items:center;gap:.32rem;padding:.34rem .62rem;border-radius:999px;border:1px solid rgba(255,255,255,.08);background:rgba(255,255,255,.03);font-size:.75rem;font-weight:800}
.history-flag.good{border-color:rgba(34,197,94,.18);background:rgba(34,197,94,.08);color:#bbf7d0}
.history-flag.issue{border-color:rgba(239,68,68,.18);background:rgba(239,68,68,.08);color:#fecaca}
.history-comment{margin-top:.85rem;padding:.85rem .95rem;border-radius:14px;border:1px solid rgba(255,255,255,.06);background:rgba(255,255,255,.03);color:#e2e8f0;line-height:1.6}
.history-attachment img{width:90px;height:90px;object-fit:cover;border-radius:14px;border:1px solid rgba(255,255,255,.08)}
.history-attachment a{color:#7dd3fc;font-weight:800;text-decoration:none}
.review-strip{display:flex;flex-wrap:wrap;gap:.6rem;align-items:center;margin-top:.9rem}
.review-state{display:inline-flex;align-items:center;gap:.38rem;padding:.36rem .68rem;border-radius:999px;border:1px solid rgba(255,255,255,.08);background:rgba(255,255,255,.04);font-size:.76rem;font-weight:900}
.review-state.pending{border-color:rgba(245,158,11,.22);background:rgba(245,158,11,.12);color:#fde68a}
.review-state.reviewed{border-color:rgba(34,197,94,.22);background:rgba(34,197,94,.1);color:#bbf7d0}
.review-panel{margin-top:.95rem;padding:.9rem;border-radius:16px;border:1px solid rgba(255,255,255,.06);background:rgba(255,255,255,.03)}
.review-note{margin-top:.55rem;color:#cbd5e1;font-size:.84rem;line-height:1.55}
.review-form{display:flex;flex-direction:column;gap:.7rem}
.review-form textarea{min-height:86px;padding:.8rem .9rem;border-radius:14px;border:1px solid rgba(255,255,255,.08);background:rgba(2,6,23,.45);color:#fff;resize:vertical}
.review-form textarea:focus{outline:none;border-color:rgba(56,189,248,.28);box-shadow:0 0 0 3px rgba(56,189,248,.08)}
.review-actions{display:flex;flex-wrap:wrap;gap:.65rem}
.review-btn{display:inline-flex;align-items:center;justify-content:center;min-height:42px;padding:.72rem 1rem;border-radius:12px;font-size:.82rem;font-weight:900;text-decoration:none}
.review-btn.primary{border:1px solid rgba(34,197,94,.18);background:linear-gradient(135deg,#15803d,#22c55e);color:#fff}
.review-btn.secondary{border:1px solid rgba(255,255,255,.1);background:rgba(255,255,255,.03);color:#fff}
.empty-note{padding:1.1rem;border-radius:18px;border:1px dashed rgba(255,255,255,.1);color:var(--rep-muted);text-align:center;font-weight:800}
@media (max-width:1200px){.summary-grid{grid-template-columns:repeat(3,minmax(0,1fr))}.toolbar-grid,.detail-grid{grid-template-columns:repeat(2,minmax(0,1fr))}}
@media (max-width:991px){.summary-grid,.insight-grid,.toolbar-grid,.detail-grid{grid-template-columns:1fr}}
</style>

<div class="reputation-page">
    <div class="rep-card rep-hero">
        <div class="rep-head">
            <div>
                <h1 class="rep-title">Customer Reputation</h1>
            </div>
            <div class="rep-chip"><i class="fa-solid fa-shield-halved"></i> Centralized customer risk view</div>
        </div>

        <div class="summary-grid">
            <div class="summary-box"><div class="summary-label">Customers</div><div class="summary-value">{{ $summary->customers }}</div></div>
            <div class="summary-box"><div class="summary-label">Rated Customers</div><div class="summary-value accent">{{ $summary->rated_customers }}</div></div>
            <div class="summary-box"><div class="summary-label">Average Rating</div><div class="summary-value">{{ number_format((float) $summary->avg_rating, 1) }}</div></div>
            <div class="summary-box"><div class="summary-label">High Risk</div><div class="summary-value danger">{{ $summary->high_risk }}</div></div>
            <div class="summary-box"><div class="summary-label">Mismatches</div><div class="summary-value warn">{{ $summary->mismatches }}</div></div>
            <div class="summary-box"><div class="summary-label">Complaints</div><div class="summary-value danger">{{ $summary->complaints }}</div></div>
        </div>
    </div>

    <div class="rep-card toolbar-card">
        <form method="GET" action="{{ route('admin.customer-reputation') }}">
            <div class="toolbar-grid">
                <div class="field">
                    <label>Search Customer</label>
                    <input class="rep-input" type="text" name="search" value="{{ $search }}" placeholder="Name, email, or phone">
                </div>
                <div class="field">
                    <label>Risk Level</label>
                    <select class="rep-select" name="risk">
                        <option value="all" {{ $risk === 'all' ? 'selected' : '' }}>All risk levels</option>
                        <option value="low" {{ $risk === 'low' ? 'selected' : '' }}>Low</option>
                        <option value="medium" {{ $risk === 'medium' ? 'selected' : '' }}>Medium</option>
                        <option value="high" {{ $risk === 'high' ? 'selected' : '' }}>High</option>
                    </select>
                </div>
                <div class="field">
                    <label>Sort</label>
                    <select class="rep-select" name="sort">
                        <option value="problematic" {{ $sort === 'problematic' ? 'selected' : '' }}>Problematic first</option>
                        <option value="top" {{ $sort === 'top' ? 'selected' : '' }}>Top customers</option>
                        <option value="highest" {{ $sort === 'highest' ? 'selected' : '' }}>Highest rating</option>
                        <option value="lowest" {{ $sort === 'lowest' ? 'selected' : '' }}>Lowest rating</option>
                        <option value="completed" {{ $sort === 'completed' ? 'selected' : '' }}>Most completed</option>
                        <option value="cancelled" {{ $sort === 'cancelled' ? 'selected' : '' }}>Most cancelled</option>
                    </select>
                </div>
                <div class="d-flex gap-2 flex-wrap">
                    <button class="btn-apply" type="submit">Apply</button>
                    <a class="btn-clear" href="{{ route('admin.customer-reputation') }}">Reset</a>
                </div>
            </div>
        </form>
    </div>

    <div class="insight-grid">
        <div class="rep-card panel-card">
            <div class="panel-head">
                <div>
                    <h2 class="panel-title">Top Customers</h2>
                </div>
            </div>
            @if($topCustomers->isEmpty())
                <div class="empty-note">No customer rating data yet.</div>
            @else
                <div class="stack">
                    @foreach($topCustomers as $customer)
                        <div class="mini-card">
                            <div class="mini-top">
                                <div class="mini-name">{{ $customer->name }}</div>
                                <div class="rating-badge"><i class="fa-solid fa-star"></i> {{ number_format((float) $customer->avg_rating, 1) }}</div>
                            </div>
                            <div class="mini-meta">{{ $customer->completed_bookings }} completed / {{ $customer->cancelled_bookings }} cancelled<br>Reputation score {{ $customer->reputation_score }}</div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="rep-card panel-card">
            <div class="panel-head">
                <div>
                    <h2 class="panel-title">Problematic Customers</h2>
                </div>
            </div>
            @if($problematicCustomers->isEmpty())
                <div class="empty-note">No risky customer pattern found yet.</div>
            @else
                <div class="stack">
                    @foreach($problematicCustomers as $customer)
                        <div class="mini-card">
                            <div class="mini-top">
                                <div class="mini-name">{{ $customer->name }}</div>
                                <span class="risk-badge {{ strtolower($customer->risk_level) }}">{{ $customer->risk_level }} Risk</span>
                            </div>
                            <div class="mini-meta">{{ $customer->mismatch_count }} mismatches / {{ $customer->complaint_count }} complaints / {{ $customer->cancelled_bookings }} cancellations</div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    <div class="rep-card panel-card">
        <div class="panel-head">
            <div>
                <h2 class="panel-title">Suspicious Ratings Review</h2>
            </div>
            <div class="d-flex gap-2 flex-wrap">
                <div class="rep-chip"><i class="fa-solid fa-clock"></i> {{ $summary->suspicious_pending }} pending</div>
                <div class="rep-chip"><i class="fa-solid fa-flag"></i> {{ $suspiciousRatings->count() }} recent</div>
            </div>
        </div>

        @if($suspiciousRatings->isEmpty())
            <div class="empty-note">No suspicious customer ratings found right now.</div>
        @else
            <div class="history-list">
                @foreach($suspiciousRatings as $item)
                    @php
                        $customerName = trim((string) ($item->customer_name ?? '')) ?: 'Customer';
                        $providerName = trim((string) ($item->provider_name ?? '')) ?: 'Provider';
                        $customerEmail = trim((string) ($item->customer_email ?? ''));
                        $serviceName = trim((string) ($item->service_name ?? '')) ?: 'Service';
                        $optionName = trim((string) ($item->option_name ?? ''));
                        $referenceCode = trim((string) ($item->reference_code ?? '')) ?: 'No reference';
                        $attachment = !empty($item->attachment_path)
                            ? route('customer.ratings.attachment', ['filename' => basename($item->attachment_path)])
                            : null;
                    @endphp
                    <div class="history-card">
                        <div class="history-top">
                            <div>
                                <div class="history-title">Customer: {{ $customerName }}</div>
                                <div class="history-meta">Provider: {{ $providerName }}</div>
                            </div>
                            <div class="rating-badge"><i class="fa-solid fa-star"></i> {{ (int) $item->rating }}/5</div>
                        </div>

                        <div class="detail-grid">
                            <div class="detail-card">
                                <div class="detail-label">Customer Contact</div>
                                <div class="detail-value">{{ $customerEmail ?: 'No email on file' }}</div>
                            </div>
                            <div class="detail-card">
                                <div class="detail-label">Service Performed</div>
                                <div class="detail-value">
                                    {{ $serviceName }}
                                    @if($optionName !== '')
                                        / {{ $optionName }}
                                    @endif
                                </div>
                            </div>
                            <div class="detail-card">
                                <div class="detail-label">Booking</div>
                                <div class="detail-value">
                                    {{ $referenceCode }}
                                    @if(!empty($item->booking_date))
                                        / {{ Carbon::parse($item->booking_date)->format('M d, Y') }}
                                    @endif
                                </div>
                            </div>
                        </div>

                        @if(collect($item->suspicion_flags)->isNotEmpty())
                            <div class="history-flags">
                                @foreach($item->suspicion_flags as $flag)
                                    <span class="history-flag issue">{{ $flag }}</span>
                                @endforeach
                            </div>
                        @endif

                        @if(!empty($item->comment))
                            <div class="history-comment">{{ $item->comment }}</div>
                        @endif

                        @if($attachment)
                            <div class="history-attachment">
                                @if(str_starts_with((string) $item->attachment_mime, 'image/'))
                                    <img src="{{ $attachment }}" alt="Suspicious rating attachment">
                                @endif
                                <a href="{{ $attachment }}" target="_blank" rel="noopener">{{ $item->attachment_name ?: 'Open attachment' }}</a>
                            </div>
                        @endif

                        <div class="review-strip">
                            @if(!empty($item->admin_reviewed_at))
                                <span class="review-state reviewed"><i class="fa-solid fa-circle-check"></i> Reviewed</span>
                                <span class="customer-sub">
                                    {{ $item->admin_reviewed_by_name ?: 'Admin' }}
                                    / {{ Carbon::parse($item->admin_reviewed_at)->format('M d, Y h:i A') }}
                                </span>
                            @else
                                <span class="review-state pending"><i class="fa-solid fa-clock"></i> Pending admin review</span>
                            @endif
                        </div>

                        <div class="review-panel">
                            @if(!empty($item->admin_reviewed_at))
                                @if(!empty($item->admin_review_note))
                                    <div class="review-note">{{ $item->admin_review_note }}</div>
                                @endif

                                <form method="POST" action="{{ route('admin.customer-reputation.review-rating', $item->id) }}" class="review-form">
                                    @csrf
                                    <input type="hidden" name="action" value="reopen">
                                    <div class="review-actions">
                                        <button class="review-btn secondary" type="submit">Move Back to Pending</button>
                                    </div>
                                </form>
                            @else
                                <form method="POST" action="{{ route('admin.customer-reputation.review-rating', $item->id) }}" class="review-form">
                                    @csrf
                                    <input type="hidden" name="action" value="review">
                                    <textarea name="admin_review_note" placeholder="Add a short admin review note if needed."></textarea>
                                    <div class="review-actions">
                                        <button class="review-btn primary" type="submit">Mark Reviewed</button>
                                    </div>
                                </form>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <div class="rep-card table-card">
        <div class="panel-head">
            <div>
                <h2 class="panel-title">Customer Reputation Summary</h2>
            </div>
            <div class="rep-chip"><i class="fa-solid fa-users"></i> {{ $customers->count() }} customers</div>
        </div>

        @if($customers->isEmpty())
            <div class="empty-note">No customers matched the current filter.</div>
        @else
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Customer</th>
                            <th>Average Rating</th>
                            <th>Completed</th>
                            <th>Cancelled</th>
                            <th>Mismatches</th>
                            <th>Complaints</th>
                            <th>Score</th>
                            <th>Risk</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($customers as $customer)
                            <tr>
                                <td>
                                    <div class="customer-name">{{ $customer->name }}</div>
                                    <div class="customer-sub">{{ $customer->email }}</div>
                                    @if(!empty($customer->phone))
                                        <div class="customer-sub">{{ $customer->phone }}</div>
                                    @endif
                                </td>
                                <td>
                                    <div class="rating-badge"><i class="fa-solid fa-star"></i> {{ number_format((float) $customer->avg_rating, 1) }}</div>
                                    <div class="customer-sub">{{ $ratingWords($customer->avg_rating) }} / {{ $customer->rating_count }} rating{{ $customer->rating_count === 1 ? '' : 's' }}</div>
                                </td>
                                <td><span class="tiny-value">{{ $customer->completed_bookings }}</span></td>
                                <td><span class="tiny-value">{{ $customer->cancelled_bookings }}</span></td>
                                <td><span class="tiny-value">{{ $customer->mismatch_count }}</span></td>
                                <td><span class="tiny-value">{{ $customer->complaint_count }}</span></td>
                                <td><span class="score-pill">{{ $customer->reputation_score }}</span></td>
                                <td><span class="risk-badge {{ strtolower($customer->risk_level) }}">{{ $customer->risk_level }}</span></td>
                                <td><button class="view-btn" type="button" data-bs-toggle="modal" data-bs-target="#customerHistoryModal{{ $customer->id }}">View History</button></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>

@foreach($customers as $customer)
    @php $customerHistory = collect($history->get($customer->id, collect())); @endphp
    <div class="modal fade" id="customerHistoryModal{{ $customer->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content bg-dark text-white border border-secondary-subtle" style="border-radius:22px">
                <div class="modal-header border-secondary-subtle">
                    <div>
                        <h5 class="modal-title fw-bold">{{ $customer->name }} Rating History</h5>
                        <div class="text-secondary small mt-1">Average {{ number_format((float) $customer->avg_rating, 1) }} / Risk {{ $customer->risk_level }}</div>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    @if($customerHistory->isEmpty())
                        <div class="empty-note">No provider-submitted customer ratings yet.</div>
                    @else
                        <div class="history-list">
                            @foreach($customerHistory as $item)
                                @php
                                    $providerName = trim((string) ($item->provider_name ?? '')) ?: 'Provider';
                                    $serviceName = trim((string) ($item->service_name ?? '')) ?: 'Service';
                                    $optionName = trim((string) ($item->option_name ?? ''));
                                    $referenceCode = trim((string) ($item->reference_code ?? '')) ?: 'No reference';
                                    $attachment = !empty($item->attachment_path)
                                        ? route('customer.ratings.attachment', ['filename' => basename($item->attachment_path)])
                                        : null;
                                @endphp
                                <div class="history-card">
                                    <div class="history-top">
                                        <div>
                                            <div class="history-title">Provider: {{ $providerName }}</div>
                                            <div class="history-meta">Customer: {{ $customer->name }}</div>
                                        </div>
                                        <div class="rating-badge"><i class="fa-solid fa-star"></i> {{ (int) $item->rating }}/5</div>
                                    </div>

                                    <div class="detail-grid">
                                        <div class="detail-card">
                                            <div class="detail-label">Service</div>
                                            <div class="detail-value">
                                                {{ $serviceName }}
                                                @if($optionName !== '')
                                                    / {{ $optionName }}
                                                @endif
                                            </div>
                                        </div>
                                        <div class="detail-card">
                                            <div class="detail-label">Booking</div>
                                            <div class="detail-value">
                                                {{ $referenceCode }}
                                                @if(!empty($item->booking_date))
                                                    / {{ Carbon::parse($item->booking_date)->format('M d, Y') }}
                                                @endif
                                            </div>
                                        </div>
                                        <div class="detail-card">
                                            <div class="detail-label">Submitted</div>
                                            <div class="detail-value">{{ Carbon::parse($item->created_at)->format('M d, Y h:i A') }}</div>
                                        </div>
                                    </div>

                                    <div class="history-flags">
                                        @foreach($issueLabels as $field => $label)
                                            @if(!empty($item->{$field}))
                                                <span class="history-flag {{ str_starts_with($field, 'flag_') || $field === 'unexpected_extra_work' ? 'issue' : 'good' }}">{{ $label }}</span>
                                            @endif
                                        @endforeach
                                    </div>

                                    @if(!empty($item->comment))
                                        <div class="history-comment">{{ $item->comment }}</div>
                                    @endif

                                    @if($attachment)
                                        <div class="history-attachment">
                                            @if(str_starts_with((string) $item->attachment_mime, 'image/'))
                                                <img src="{{ $attachment }}" alt="Customer rating attachment">
                                            @endif
                                            <a href="{{ $attachment }}" target="_blank" rel="noopener">{{ $item->attachment_name ?: 'Open attachment' }}</a>
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endforeach
@endsection
