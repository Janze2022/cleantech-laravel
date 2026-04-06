@extends('admin.layouts.app')

@section('title', 'Provider Reputation')

@section('content')
@php
    use Carbon\Carbon;

    $providers = collect($providers ?? []);
    $topProviders = collect($topProviders ?? []);
    $attentionProviders = collect($attentionProviders ?? []);
    $history = $history ?? collect();
    $recentReviews = collect($recentReviews ?? []);
    $summary = $summary ?? (object) [
        'providers' => 0,
        'rated_providers' => 0,
        'avg_rating' => 0,
        'high_risk' => 0,
        'completed_jobs' => 0,
        'low_ratings' => 0,
    ];

    $ratingWords = function ($value) {
        $value = round((float) $value, 1);

        if ($value >= 4.5) return 'Excellent';
        if ($value >= 4.0) return 'Very Good';
        if ($value >= 3.0) return 'Good';
        if ($value > 0) return 'Needs Review';

        return 'No ratings';
    };
@endphp

<style>
:root{
    --prep-border:rgba(255,255,255,.08);
    --prep-text:#f8fafc;
    --prep-muted:#94a3b8;
}

.provider-reputation-page{display:flex;flex-direction:column;gap:1rem;color:var(--prep-text)}
.prep-card{background:linear-gradient(180deg, rgba(7,18,37,.97), rgba(2,6,23,.99));border:1px solid var(--prep-border);border-radius:24px;box-shadow:0 18px 36px rgba(0,0,0,.26)}
.prep-hero,.toolbar-card,.panel-card,.table-card{padding:1rem 1.1rem}
.prep-head,.panel-head{display:flex;align-items:flex-start;justify-content:space-between;gap:1rem;flex-wrap:wrap}
.prep-head > div:first-child,.panel-head > div:first-child{flex:1 1 320px;min-width:0}
.prep-title,.panel-title{margin:0;font-weight:900}
.prep-title{font-size:1.36rem}
.panel-title{font-size:1rem}
.prep-subtitle,.panel-sub{margin:.35rem 0 0;color:var(--prep-muted);font-size:.9rem;line-height:1.55}
.prep-chip,.rating-badge,.risk-badge,.score-pill,.view-btn,.btn-apply,.btn-clear,.meta-pill{display:inline-flex;align-items:center;justify-content:center}
.prep-chip{gap:.45rem;min-height:38px;padding:.5rem .82rem;border-radius:999px;border:1px solid rgba(56,189,248,.22);background:rgba(56,189,248,.1);color:#dff7ff;font-size:.84rem;font-weight:900}
.summary-grid{display:grid;grid-template-columns:repeat(6,minmax(0,1fr));gap:.8rem;margin-top:1rem}
.summary-box,.mini-card,.history-card{padding:.95rem 1rem;border-radius:18px;background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.06)}
.summary-label{color:var(--prep-muted);font-size:.74rem;font-weight:800;letter-spacing:.09em;text-transform:uppercase}
.summary-value{margin-top:.48rem;font-size:1.55rem;font-weight:900;line-height:1}
.summary-value.accent{color:#38bdf8}.summary-value.success{color:#86efac}.summary-value.warn{color:#fdba74}.summary-value.danger{color:#fca5a5}
.toolbar-grid{display:grid;grid-template-columns:minmax(240px,1.6fr) 180px 220px auto;gap:.8rem;align-items:end}
.field{display:flex;flex-direction:column;gap:.42rem}
.field label{color:var(--prep-muted);font-size:.75rem;font-weight:800;letter-spacing:.08em;text-transform:uppercase}
.prep-input,.prep-select{width:100%;min-height:46px;padding:.75rem .9rem;border-radius:14px;border:1px solid rgba(255,255,255,.08);background:rgba(2,6,23,.45);color:#fff}
.prep-input:focus,.prep-select:focus,.review-form textarea:focus{outline:none;border-color:rgba(56,189,248,.28);box-shadow:0 0 0 3px rgba(56,189,248,.08)}
.prep-select option{background:#071225;color:#f8fafc}
.btn-apply,.btn-clear{min-height:46px;padding:.75rem 1rem;border-radius:14px;font-weight:900;text-decoration:none}
.btn-apply{border:1px solid rgba(56,189,248,.24);background:linear-gradient(135deg,#2563eb,#38bdf8);color:#fff}
.btn-clear{border:1px solid rgba(255,255,255,.1);background:rgba(255,255,255,.03);color:#fff}
.insight-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:1rem}
.stack{display:flex;flex-direction:column;gap:.75rem}
.mini-top,.history-top{display:flex;align-items:flex-start;justify-content:space-between;gap:.8rem;flex-wrap:wrap}
.history-top > div:first-child{flex:1 1 280px;min-width:0}
.mini-name,.history-title,.provider-name{font-size:.95rem;font-weight:900;line-height:1.35}
.mini-meta,.provider-sub,.history-meta{margin-top:.28rem;color:var(--prep-muted);font-size:.82rem;line-height:1.5}
.detail-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:.72rem;margin-top:.85rem;align-items:stretch}
.detail-card{display:flex;flex-direction:column;justify-content:flex-start;min-height:92px;padding:.78rem .82rem;border-radius:14px;border:1px solid rgba(255,255,255,.06);background:rgba(2,6,23,.38)}
.detail-label{color:var(--prep-muted);font-size:.7rem;font-weight:800;letter-spacing:.09em;text-transform:uppercase}
.detail-value{margin-top:.3rem;color:#f8fafc;font-size:.88rem;font-weight:800;line-height:1.45}
.rating-badge{gap:.35rem;padding:.32rem .6rem;border-radius:999px;border:1px solid rgba(251,191,36,.2);background:rgba(251,191,36,.1);color:#fde68a;font-size:.76rem;font-weight:900;white-space:nowrap}
.meta-pill{gap:.35rem;min-height:40px;padding:.32rem .62rem;border-radius:999px;border:1px solid rgba(255,255,255,.08);background:rgba(255,255,255,.04);font-size:.76rem;font-weight:900;color:#e2e8f0;line-height:1}
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
.history-card{overflow:hidden}
.history-body{display:grid;grid-template-columns:minmax(0,1.45fr) minmax(220px,.85fr);gap:.85rem;margin-top:.85rem;align-items:stretch}
.history-body > :only-child{grid-column:1 / -1}
.history-comment{margin-top:0;min-height:88px;padding:.85rem .95rem;border-radius:14px;border:1px solid rgba(255,255,255,.06);background:rgba(255,255,255,.03);color:#e2e8f0;line-height:1.6}
.history-attachment{display:flex;flex-wrap:wrap;gap:.65rem;margin-top:0;padding:.85rem .95rem;border-radius:14px;border:1px solid rgba(255,255,255,.06);background:rgba(255,255,255,.03);align-items:flex-start}
.history-attachment.has-preview{display:grid;grid-template-columns:90px minmax(0,1fr)}
.history-attachment img{width:90px;height:90px;object-fit:cover;border-radius:14px;border:1px solid rgba(255,255,255,.08)}
.history-file{display:flex;flex-direction:column;gap:.4rem;min-width:0}
.history-file a{color:#7dd3fc;font-weight:800;text-decoration:none;word-break:break-word}
.review-strip{display:flex;flex-wrap:wrap;justify-content:space-between;gap:.75rem;align-items:center;margin-top:.95rem;padding-top:.8rem;border-top:1px solid rgba(255,255,255,.06)}
.review-strip .provider-sub{margin-top:0}
.review-state{display:inline-flex;align-items:center;min-height:40px;gap:.38rem;padding:.36rem .68rem;border-radius:999px;border:1px solid rgba(255,255,255,.08);background:rgba(255,255,255,.04);font-size:.76rem;font-weight:900;line-height:1}
.review-state i,.meta-pill i{display:inline-flex;align-items:center;justify-content:center;line-height:1}
.review-state.alert{border-color:rgba(239,68,68,.22);background:rgba(239,68,68,.1);color:#fecaca}
.review-state.note{border-color:rgba(56,189,248,.2);background:rgba(56,189,248,.1);color:#dff7ff}
.empty-note{padding:1.1rem;border-radius:18px;border:1px dashed rgba(255,255,255,.1);color:var(--prep-muted);text-align:center;font-weight:800}
@media (max-width:1200px){.summary-grid{grid-template-columns:repeat(3,minmax(0,1fr))}.toolbar-grid,.detail-grid{grid-template-columns:repeat(2,minmax(0,1fr))}.history-body{grid-template-columns:1fr}}
@media (max-width:991px){.summary-grid,.insight-grid,.toolbar-grid,.detail-grid{grid-template-columns:1fr}.history-attachment.has-preview{grid-template-columns:1fr}}
</style>

<div class="provider-reputation-page">
    <div class="prep-card prep-hero">
        <div class="prep-head">
            <div>
                <h1 class="prep-title">Provider Reputation</h1>
            </div>
            <div class="prep-chip"><i class="fa-solid fa-user-shield"></i> Customer review and provider performance</div>
        </div>

        <div class="summary-grid">
            <div class="summary-box"><div class="summary-label">Providers</div><div class="summary-value">{{ $summary->providers }}</div></div>
            <div class="summary-box"><div class="summary-label">Rated Providers</div><div class="summary-value accent">{{ $summary->rated_providers }}</div></div>
            <div class="summary-box"><div class="summary-label">Average Rating</div><div class="summary-value">{{ number_format((float) $summary->avg_rating, 1) }}</div></div>
            <div class="summary-box"><div class="summary-label">High Risk</div><div class="summary-value danger">{{ $summary->high_risk }}</div></div>
            <div class="summary-box"><div class="summary-label">Completed Jobs</div><div class="summary-value success">{{ $summary->completed_jobs }}</div></div>
            <div class="summary-box"><div class="summary-label">Low Ratings</div><div class="summary-value warn">{{ $summary->low_ratings }}</div></div>
        </div>
    </div>

    <div class="prep-card toolbar-card">
        <form method="GET" action="{{ route('admin.provider-reputation') }}">
            <div class="toolbar-grid">
                <div class="field">
                    <label>Search Provider</label>
                    <input class="prep-input" type="text" name="search" value="{{ $search }}" placeholder="Name, email, phone, or status">
                </div>
                <div class="field">
                    <label>Risk Level</label>
                    <select class="prep-select" name="risk">
                        <option value="all" {{ $risk === 'all' ? 'selected' : '' }}>All risk levels</option>
                        <option value="low" {{ $risk === 'low' ? 'selected' : '' }}>Low</option>
                        <option value="medium" {{ $risk === 'medium' ? 'selected' : '' }}>Medium</option>
                        <option value="high" {{ $risk === 'high' ? 'selected' : '' }}>High</option>
                    </select>
                </div>
                <div class="field">
                    <label>Sort</label>
                    <select class="prep-select" name="sort">
                        <option value="attention" {{ $sort === 'attention' ? 'selected' : '' }}>Needs attention first</option>
                        <option value="top" {{ $sort === 'top' ? 'selected' : '' }}>Top providers</option>
                        <option value="highest" {{ $sort === 'highest' ? 'selected' : '' }}>Highest rating</option>
                        <option value="lowest" {{ $sort === 'lowest' ? 'selected' : '' }}>Lowest rating</option>
                        <option value="completed" {{ $sort === 'completed' ? 'selected' : '' }}>Most completed</option>
                        <option value="cancelled" {{ $sort === 'cancelled' ? 'selected' : '' }}>Most cancelled</option>
                    </select>
                </div>
                <div class="d-flex gap-2 flex-wrap">
                    <button class="btn-apply" type="submit">Apply</button>
                    <a class="btn-clear" href="{{ route('admin.provider-reputation') }}">Reset</a>
                </div>
            </div>
        </form>
    </div>

    <div class="insight-grid">
        <div class="prep-card panel-card">
            <div class="panel-head">
                <div>
                    <h2 class="panel-title">Trusted Providers</h2>
                    <p class="panel-sub">Higher customer ratings, stronger completion results, and fewer reliability issues.</p>
                </div>
            </div>
            @if($topProviders->isEmpty())
                <div class="empty-note">No provider reputation data yet.</div>
            @else
                <div class="stack">
                    @foreach($topProviders as $provider)
                        <div class="mini-card">
                            <div class="mini-top">
                                <div class="mini-name">{{ $provider->name }}</div>
                                <div class="rating-badge"><i class="fa-solid fa-star"></i> {{ number_format((float) $provider->avg_rating, 1) }}</div>
                            </div>
                            <div class="mini-meta">{{ $provider->completed_bookings }} completed / {{ $provider->cancelled_bookings }} cancelled / Reputation score {{ $provider->reputation_score }}</div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="prep-card panel-card">
            <div class="panel-head">
                <div>
                    <h2 class="panel-title">Needs Attention</h2>
                    <p class="panel-sub">Low customer ratings, cancellation patterns, or weaker success rates that may need admin review.</p>
                </div>
            </div>
            @if($attentionProviders->isEmpty())
                <div class="empty-note">No risky provider pattern found right now.</div>
            @else
                <div class="stack">
                    @foreach($attentionProviders as $provider)
                        <div class="mini-card">
                            <div class="mini-top">
                                <div class="mini-name">{{ $provider->name }}</div>
                                <span class="risk-badge {{ strtolower($provider->risk_level) }}">{{ $provider->risk_level }} Risk</span>
                            </div>
                            <div class="mini-meta">{{ $provider->low_rating_count }} low ratings / {{ $provider->cancelled_bookings }} cancellations / {{ $provider->success_rate }}% success</div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    <div class="prep-card panel-card">
        <div class="panel-head">
            <div>
                <h2 class="panel-title">Recent Provider Reviews</h2>
            </div>
            <div class="d-flex gap-2 flex-wrap">
                <div class="prep-chip"><i class="fa-solid fa-triangle-exclamation"></i> {{ $recentReviews->where('rating', '<=', 2)->count() }} low ratings</div>
                <div class="prep-chip"><i class="fa-solid fa-clock"></i> {{ $recentReviews->count() }} recent</div>
            </div>
        </div>

        @if($recentReviews->isEmpty())
            <div class="empty-note">No customer reviews have been submitted for providers yet.</div>
        @else
            <div class="history-list">
                @foreach($recentReviews as $item)
                    @php
                        $providerName = trim((string) ($item->provider_name ?? '')) ?: 'Provider';
                        $customerName = trim((string) ($item->customer_name ?? '')) ?: 'Customer';
                        $customerEmail = trim((string) ($item->customer_email ?? ''));
                        $serviceName = trim((string) ($item->service_name ?? '')) ?: 'Service';
                        $optionName = trim((string) ($item->option_name ?? ''));
                        $referenceCode = trim((string) ($item->reference_code ?? '')) ?: 'No reference';
                        $providerStatus = trim((string) ($item->provider_status ?? '')) ?: 'Unknown';
                        $attachment = !empty($item->attachment_path)
                            ? route('reviews.attachment', ['filename' => basename($item->attachment_path)])
                            : null;
                    @endphp
                    <div class="history-card">
                        <div class="history-top">
                            <div>
                                <div class="history-title">Provider: {{ $providerName }}</div>
                                <div class="history-meta">Customer: {{ $customerName }}</div>
                            </div>
                            <div class="rating-badge"><i class="fa-solid fa-star"></i> {{ (int) $item->rating }}/5</div>
                        </div>

                        <div class="detail-grid">
                            <div class="detail-card">
                                <div class="detail-label">Customer Contact</div>
                                <div class="detail-value">{{ $customerEmail ?: 'No email on file' }}</div>
                            </div>
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
                        </div>

                        @if(!empty($item->comment) || $attachment)
                            <div class="history-body">
                                @if(!empty($item->comment))
                                    <div class="history-comment">{{ $item->comment }}</div>
                                @endif

                                @if($attachment)
                                    <div class="history-attachment {{ str_starts_with((string) $item->attachment_mime, 'image/') ? 'has-preview' : '' }}">
                                        @if(str_starts_with((string) $item->attachment_mime, 'image/'))
                                            <img src="{{ $attachment }}" alt="Provider review attachment">
                                        @endif
                                        <div class="history-file">
                                            <div class="detail-label">Attachment</div>
                                            <a href="{{ $attachment }}" target="_blank" rel="noopener">{{ $item->attachment_name ?: 'Open attachment' }}</a>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @endif

                        <div class="review-strip">
                            <div class="d-flex gap-2 flex-wrap align-items-center">
                                <span class="review-state {{ (int) $item->rating <= 2 ? 'alert' : 'note' }}">
                                    <i class="fa-solid {{ (int) $item->rating <= 2 ? 'fa-triangle-exclamation' : 'fa-comment-dots' }}"></i>
                                    {{ (int) $item->rating <= 2 ? 'Low customer rating' : 'Customer feedback' }}
                                </span>
                                <span class="meta-pill"><i class="fa-solid fa-badge-check"></i> Status: {{ $providerStatus }}</span>
                            </div>
                            @if(!empty($item->reviewed_at))
                                <span class="provider-sub">Submitted {{ Carbon::parse($item->reviewed_at)->format('M d, Y h:i A') }}</span>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <div class="prep-card table-card">
        <div class="panel-head">
            <div>
                <h2 class="panel-title">Provider Reputation Summary</h2>
            </div>
            <div class="prep-chip"><i class="fa-solid fa-users"></i> {{ $providers->count() }} providers</div>
        </div>

        @if($providers->isEmpty())
            <div class="empty-note">No providers matched the current filter.</div>
        @else
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Provider</th>
                            <th>Average Rating</th>
                            <th>Reviews</th>
                            <th>Completed</th>
                            <th>Cancelled</th>
                            <th>Success</th>
                            <th>Score</th>
                            <th>Risk</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($providers as $provider)
                            <tr>
                                <td>
                                    <div class="provider-name">{{ $provider->name }}</div>
                                    @if(!empty($provider->email))
                                        <div class="provider-sub">{{ $provider->email }}</div>
                                    @endif
                                    @if(!empty($provider->phone))
                                        <div class="provider-sub">{{ $provider->phone }}</div>
                                    @endif
                                    <div class="provider-sub">Status: {{ $provider->status }}</div>
                                </td>
                                <td>
                                    <div class="rating-badge"><i class="fa-solid fa-star"></i> {{ number_format((float) $provider->avg_rating, 1) }}</div>
                                    <div class="provider-sub">{{ $ratingWords($provider->avg_rating) }}</div>
                                </td>
                                <td><span class="tiny-value">{{ $provider->rating_count }}</span></td>
                                <td><span class="tiny-value">{{ $provider->completed_bookings }}</span></td>
                                <td><span class="tiny-value">{{ $provider->cancelled_bookings }}</span></td>
                                <td><span class="tiny-value">{{ $provider->success_rate }}%</span></td>
                                <td><span class="score-pill">{{ $provider->reputation_score }}</span></td>
                                <td><span class="risk-badge {{ strtolower($provider->risk_level) }}">{{ $provider->risk_level }}</span></td>
                                <td><button class="view-btn" type="button" data-bs-toggle="modal" data-bs-target="#providerHistoryModal{{ $provider->id }}">View History</button></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>

@foreach($providers as $provider)
    @php $providerHistory = collect($history->get($provider->id, collect())); @endphp
    <div class="modal fade" id="providerHistoryModal{{ $provider->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content bg-dark text-white border border-secondary-subtle" style="border-radius:22px">
                <div class="modal-header border-secondary-subtle">
                    <div>
                        <h5 class="modal-title fw-bold">{{ $provider->name }} Review History</h5>
                        <div class="text-secondary small mt-1">Average {{ number_format((float) $provider->avg_rating, 1) }} / Risk {{ $provider->risk_level }} / Success {{ $provider->success_rate }}%</div>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    @if($providerHistory->isEmpty())
                        <div class="empty-note">No customer-submitted provider reviews yet.</div>
                    @else
                        <div class="history-list">
                            @foreach($providerHistory as $item)
                                @php
                                    $customerName = trim((string) ($item->customer_name ?? '')) ?: 'Customer';
                                    $customerEmail = trim((string) ($item->customer_email ?? ''));
                                    $serviceName = trim((string) ($item->service_name ?? '')) ?: 'Service';
                                    $optionName = trim((string) ($item->option_name ?? ''));
                                    $referenceCode = trim((string) ($item->reference_code ?? '')) ?: 'No reference';
                                    $attachment = !empty($item->attachment_path)
                                        ? route('reviews.attachment', ['filename' => basename($item->attachment_path)])
                                        : null;
                                @endphp
                                <div class="history-card">
                                    <div class="history-top">
                                        <div>
                                            <div class="history-title">Customer: {{ $customerName }}</div>
                                            <div class="history-meta">Provider: {{ $provider->name }}</div>
                                        </div>
                                        <div class="rating-badge"><i class="fa-solid fa-star"></i> {{ (int) $item->rating }}/5</div>
                                    </div>

                                    <div class="detail-grid">
                                        <div class="detail-card">
                                            <div class="detail-label">Customer Contact</div>
                                            <div class="detail-value">{{ $customerEmail ?: 'No email on file' }}</div>
                                        </div>
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
                                            @if(!empty($item->reviewed_at))
                                                <div class="provider-sub">Submitted {{ Carbon::parse($item->reviewed_at)->format('M d, Y h:i A') }}</div>
                                            @endif
                                        </div>
                                    </div>

                                    @if(!empty($item->comment) || $attachment)
                                        <div class="history-body">
                                            @if(!empty($item->comment))
                                                <div class="history-comment">{{ $item->comment }}</div>
                                            @endif

                                            @if($attachment)
                                                <div class="history-attachment {{ str_starts_with((string) $item->attachment_mime, 'image/') ? 'has-preview' : '' }}">
                                                    @if(str_starts_with((string) $item->attachment_mime, 'image/'))
                                                        <img src="{{ $attachment }}" alt="Provider review attachment">
                                                    @endif
                                                    <div class="history-file">
                                                        <div class="detail-label">Attachment</div>
                                                        <a href="{{ $attachment }}" target="_blank" rel="noopener">{{ $item->attachment_name ?: 'Open attachment' }}</a>
                                                    </div>
                                                </div>
                                            @endif
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
