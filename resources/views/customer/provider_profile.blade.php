@extends('customer.layouts.app')

@section('title', 'Provider Profile')

@section('content')

<style>
:root{
    --bg:#020617;
    --card:#020b1f;
    --card2:#040b22;
    --border:rgba(255,255,255,.08);
    --border2:rgba(255,255,255,.12);
    --text:rgba(255,255,255,.92);
    --muted:rgba(255,255,255,.55);
    --muted2:rgba(255,255,255,.42);
    --accent:#38bdf8;
    --success:#22c55e;
    --warning:#fbbf24;

    --r:18px;
    --shadow:0 35px 85px rgba(0,0,0,.58);
}

/* Page */
.profile-wrap{ padding: 2.25rem 0; }
.shell{
    background: linear-gradient(180deg, rgba(2,11,31,.95), rgba(2,6,23,.92));
    border: 1px solid var(--border);
    border-radius: 24px;
    box-shadow: var(--shadow);
    overflow: hidden;
}

/* Top bar */
.topbar{
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap: 1rem;
    padding: 1.2rem 1.2rem;
    border-bottom: 1px solid rgba(255,255,255,.06);
    background: radial-gradient(900px 220px at 20% 0%, rgba(56,189,248,.10), transparent 60%),
                radial-gradient(800px 220px at 90% 0%, rgba(34,197,94,.08), transparent 55%),
                rgba(255,255,255,.01);
}
.backlink{
    display:inline-flex;
    align-items:center;
    gap:.55rem;
    text-decoration:none;
    color: var(--text);
    font-weight: 900;
    letter-spacing:.02em;
    padding:.6rem .8rem;
    border-radius: 14px;
    border: 1px solid rgba(255,255,255,.10);
    background: rgba(255,255,255,.03);
    transition: transform .08s ease, filter .12s ease, border-color .12s ease;
}
.backlink:hover{ transform: translateY(-1px); filter: brightness(1.05); border-color: rgba(56,189,248,.25); }

.closebtn{
    width: 40px; height: 40px;
    border-radius: 999px;
    display:flex;
    align-items:center;
    justify-content:center;
    text-decoration:none;
    color: var(--text);
    border: 1px solid rgba(255,255,255,.12);
    background: rgba(255,255,255,.03);
    transition: transform .08s ease, filter .12s ease;
    font-size: 18px;
}
.closebtn:hover{ transform: translateY(-1px); filter: brightness(1.05); }

/* Header */
.header{
    display:grid;
    grid-template-columns: 140px 1fr;
    gap: 1.2rem;
    padding: 1.35rem 1.35rem 1.15rem;
}
.avatar{
    width: 132px; height: 132px;
    border-radius: 999px;
    object-fit: cover;
    border: 2px solid rgba(255,255,255,.14);
    box-shadow: 0 20px 55px rgba(0,0,0,.45);
    background: rgba(255,255,255,.04);
}
.name{
    margin: 0;
    font-weight: 950;
    letter-spacing: .01em;
    color: rgba(255,255,255,.95);
    line-height: 1.1;
    font-size: 1.45rem;
}
.location{
    margin-top: .35rem;
    color: var(--muted);
    font-weight: 700;
}

/* Meta pills */
.meta{
    display:flex;
    flex-wrap:wrap;
    gap: .6rem;
    margin-top: .85rem;
}
.pill{
    display:inline-flex;
    align-items:center;
    gap:.55rem;
    padding: .5rem .7rem;
    border-radius: 999px;
    background: rgba(255,255,255,.03);
    border: 1px solid var(--border);
    color: var(--text);
    font-weight: 900;
    font-size: .85rem;
}
.pill .sub{
    color: var(--muted);
    font-weight: 800;
}

/* Stars */
.stars{ display:inline-flex; gap:.18rem; align-items:center; }
.star{ width:18px; height:18px; fill:#334155; }
.star.on{ fill: var(--warning); }

/* CTA */
.ctaRow{
    display:flex;
    align-items:center;
    justify-content:flex-start;
    gap:.75rem;
    padding: 0 1.35rem 1.35rem;
}
.btnBook{
    display:inline-flex;
    align-items:center;
    justify-content:center;
    text-decoration:none;
    padding: .78rem 1.05rem;
    border-radius: 14px;
    font-weight: 950;
    color: rgba(255,255,255,.94);
    background: rgba(34,197,94,.16);
    border: 1px solid rgba(34,197,94,.35);
    transition: transform .08s ease, filter .12s ease;
}
.btnBook:hover{ transform: translateY(-1px); filter: brightness(1.05); }

.btnGhost{
    display:inline-flex;
    align-items:center;
    justify-content:center;
    text-decoration:none;
    padding: .78rem 1rem;
    border-radius: 14px;
    font-weight: 900;
    color: rgba(255,255,255,.9);
    background: rgba(255,255,255,.03);
    border: 1px solid rgba(255,255,255,.10);
}

/* Sections */
.section{
    padding: 1.25rem 1.35rem;
    border-top: 1px solid rgba(255,255,255,.06);
}
.sectionTitle{
    margin:0;
    font-weight: 950;
    color: rgba(255,255,255,.95);
    letter-spacing:.01em;
}
.sectionSub{
    margin-top: .35rem;
    color: var(--muted);
    font-weight: 700;
    font-size: .92rem;
}

/* Info table */
.infoGrid{
    display:grid;
    grid-template-columns: 1fr 1fr;
    gap: .85rem;
    margin-top: 1rem;
}
.infoItem{
    border: 1px solid var(--border);
    border-radius: var(--r);
    background: rgba(255,255,255,.02);
    padding: .9rem;
}
.infoLabel{
    color: var(--muted);
    font-weight: 900;
    font-size: .8rem;
    letter-spacing:.08em;
    text-transform: uppercase;
}
.infoValue{
    margin-top: .35rem;
    color: rgba(255,255,255,.92);
    font-weight: 750;
    word-break: break-word;
}
.infoValue a{
    color: var(--accent);
    text-decoration: none;
}
.infoValue a:hover{ text-decoration: underline; }

/* Reviews summary */
.summary{
    display:grid;
    grid-template-columns: 320px 1fr;
    gap: 1rem;
    margin-top: 1rem;
}
.summaryCard{
    border: 1px solid var(--border);
    border-radius: 20px;
    background: rgba(255,255,255,.02);
    padding: 1rem;
}
.bigAvg{
    font-weight: 950;
    font-size: 2.25rem;
    line-height: 1;
    color: rgba(255,255,255,.95);
}
.smallNote{
    margin-top: .35rem;
    color: var(--muted);
    font-weight: 750;
}
.dist{
    display:grid;
    gap: .55rem;
    margin-top: .85rem;
}
.distRow{
    display:grid;
    grid-template-columns: 48px 1fr 42px;
    gap: .6rem;
    align-items:center;
}
.distLabel{
    color: rgba(255,255,255,.75);
    font-weight: 900;
    font-size: .85rem;
}
.bar{
    height: 10px;
    border-radius: 999px;
    background: rgba(255,255,255,.06);
    border: 1px solid rgba(255,255,255,.08);
    overflow:hidden;
}
.bar > span{
    display:block;
    height: 100%;
    width: 0%;
    background: rgba(251,191,36,.65);
}
.distCount{
    color: var(--muted);
    font-weight: 900;
    text-align:right;
    font-size: .85rem;
}

/* Filter bar */
.filterBar{
    border: 1px solid var(--border);
    border-radius: 20px;
    background: rgba(255,255,255,.02);
    padding: .85rem;
    display:flex;
    gap: .75rem;
    align-items:center;
    flex-wrap:wrap;
}
.control{
    display:flex;
    flex-direction:column;
    gap: .35rem;
    min-width: 190px;
    flex: 1;
}
.control label{
    font-size: .78rem;
    color: var(--muted);
    font-weight: 900;
    letter-spacing:.06em;
    text-transform: uppercase;
}

/* Fix select visibility */
.selectDark, .searchDark{
    width:100%;
    border-radius: 14px;
    border: 1px solid rgba(255,255,255,.12);
    background: rgba(2,6,23,.95);
    color: rgba(255,255,255,.92);
    padding: .65rem .8rem;
    outline: none;
    -webkit-appearance: none;
    -moz-appearance: none;
    appearance: none;
}
.selectDark{
    background-image:
        linear-gradient(45deg, transparent 50%, rgba(255,255,255,.70) 50%),
        linear-gradient(135deg, rgba(255,255,255,.70) 50%, transparent 50%);
    background-position:
        calc(100% - 18px) calc(1.1em),
        calc(100% - 13px) calc(1.1em);
    background-size: 5px 5px, 5px 5px;
    background-repeat: no-repeat;
    padding-right: 2.2rem;
}
.selectDark option{
    background: #020617;
    color: rgba(255,255,255,.92);
}
.selectDark:focus, .searchDark:focus{
    border-color: rgba(56,189,248,.35);
    box-shadow: 0 0 0 3px rgba(56,189,248,.10);
}
.hint{
    color: var(--muted2);
    font-size: .86rem;
    font-weight: 650;
}

/* Review list */
.reviewList{
    margin-top: 1rem;
    display:grid;
    gap: .85rem;
}

/* Review card */
.reviewCard{
    border: 1px solid var(--border);
    border-radius: 18px;
    background: rgba(255,255,255,.02);
    padding: .9rem;
    transition: border-color .12s ease, transform .08s ease, filter .12s ease;
}
.reviewCard:hover{
    border-color: rgba(56,189,248,.20);
    transform: translateY(-1px);
    filter: brightness(1.03);
}

.reviewTop{
    display:grid;
    grid-template-columns: 1fr auto;
    gap: .75rem;
    align-items:start;
}
.reviewerName{
    font-weight: 950;
    color: rgba(255,255,255,.95);
}

.reviewerRow{
    display:flex;
    gap:.75rem;
    align-items:flex-start;
    min-width:0;
}
.reviewerAvatar{
    width:44px;
    height:44px;
    border-radius:999px;
    object-fit:cover;
    border:1px solid rgba(255,255,255,.14);
    background: rgba(255,255,255,.04);
    flex:0 0 44px;
    box-shadow: 0 12px 30px rgba(0,0,0,.35);
}
.reviewerBlock{
    flex:1;
    min-width:0;
}
.reviewerName{
    white-space: pre-line;
    overflow:hidden;
    text-overflow:ellipsis;
}

.reviewMeta{
    display:flex;
    align-items:center;
    gap: .5rem;
    margin-top: .25rem;
    color: var(--muted);
    font-weight: 750;
    font-size: .84rem;
    flex-wrap:wrap;
}
.badge{
    display:inline-flex;
    align-items:center;
    gap:.35rem;
    padding: .26rem .5rem;
    border-radius: 999px;
    border: 1px solid rgba(255,255,255,.12);
    background: rgba(255,255,255,.03);
    color: rgba(255,255,255,.86);
    font-weight: 900;
    font-size: .78rem;
    white-space:nowrap;
}

.comment{
    margin-top: .6rem;
    color: rgba(255,255,255,.86);
    font-weight: 650;
    white-space: pre-line;
    line-height: 1.5;
    font-size: .95rem;
}

/* Empty */
.empty{
    margin-top: 1rem;
    border: 1px dashed rgba(255,255,255,.18);
    background: rgba(255,255,255,.02);
    border-radius: 20px;
    padding: 1rem;
    color: var(--muted);
    font-weight: 700;
}

/* Mobile */
@media (max-width: 992px){
    .summary{ grid-template-columns: 1fr; }
    .control{ min-width: 160px; }
}
@media (max-width: 576px){
    .profile-wrap{ padding: 1.1rem 0; }
    .topbar{ padding: 1rem; }
    .header{
        grid-template-columns: 1fr;
        padding: 1rem;
        gap: .9rem;
    }
    .avatar{ width: 92px; height: 92px; }
    .ctaRow{ padding: 0 1rem 1rem; flex-wrap:wrap; }
    .section{ padding: 1rem; }
    .infoGrid{ grid-template-columns: 1fr; }
    .filterBar{ padding: .75rem; border-radius: 18px; }
    .control{ min-width: 100%; }

    .reviewTop{ grid-template-columns: 1fr; }
    .reviewTop > .badge{ justify-self: flex-start; }
}
</style>

@php
    $avg   = (float) data_get($ratingSummary, 'avg', 0);
    $count = (int) data_get($ratingSummary, 'count', 0);

    $avgText    = $count > 0 ? number_format($avg, 1) : '0.0';
    $avgRounded = (int) round($avg);

    $dist = [1=>0,2=>0,3=>0,4=>0,5=>0];
    if($count > 0){
        foreach($reviews as $r){
            $rv = (int) data_get($r, 'rating', 0);
            if($rv >= 1 && $rv <= 5) $dist[$rv]++;
        }
    }

    $providerAvatar = !empty($provider->profile_image)
        ? route('provider.image.public', ['filename' => basename($provider->profile_image)]) . '?v=' . time()
        : asset('images/avatar-placeholder.svg');
@endphp

<div class="container profile-wrap">
    <div class="shell">

        <div class="topbar">
            <a href="{{ route('customer.services') }}" class="backlink">
                <span style="opacity:.8;">←</span> Back
            </a>
            <a href="{{ route('customer.services') }}" class="closebtn" aria-label="Close">×</a>
        </div>

        <div class="header">
            <div>
                <img
                    src="{{ $providerAvatar }}"
                    class="avatar"
                    alt="Provider avatar"
                    onerror="this.onerror=null;this.src='{{ asset('images/avatar-placeholder.svg') }}';"
                >
            </div>

            <div>
                <h4 class="name">{{ $provider->first_name }} {{ $provider->last_name }}</h4>
                <div class="location">{{ $provider->city }}, {{ $provider->province }}</div>

                <div class="meta">
                    <div class="pill" title="Average rating">
                        <span style="color:rgba(255,255,255,.95)">
                            {{ $count > 0 ? $avgText : 'No ratings yet' }}
                        </span>

                        @if($count > 0)
                            <span class="sub">• {{ $count }} review{{ $count===1?'':'s' }}</span>
                        @endif
                    </div>

                    @if($count > 0)
                        <div class="pill" title="Star rating">
                            <span class="stars" aria-label="Average rating">
                                @for($i=1;$i<=5;$i++)
                                    <svg class="star {{ $i <= $avgRounded ? 'on' : '' }}" viewBox="0 0 24 24" aria-hidden="true">
                                        <path d="M12 17.27 18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/>
                                    </svg>
                                @endfor
                            </span>
                        </div>
                    @endif

                    <div class="pill" title="Provider status">
                        <span style="color:rgba(255,255,255,.92)">Status</span>
                        <span class="sub">• {{ ucfirst(strtolower($provider->status)) }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="ctaRow">
            <a href="{{ route('customer.book.service', $provider->id) }}" class="btnBook">
                Book this provider
            </a>
            <a href="mailto:{{ $provider->email }}" class="btnGhost">
                Email
            </a>
        </div>

        <div class="section">
            <h5 class="sectionTitle">Provider Details</h5>
            <div class="sectionSub">Contact and address information.</div>

            <div class="infoGrid">
                <div class="infoItem">
                    <div class="infoLabel">Contact Number</div>
                    <div class="infoValue">{{ $provider->phone }}</div>
                </div>

                <div class="infoItem">
                    <div class="infoLabel">Email Address</div>
                    <div class="infoValue">
                        <a href="mailto:{{ $provider->email }}">{{ $provider->email }}</a>
                    </div>
                </div>

                <div class="infoItem" style="grid-column: 1 / -1;">
                    <div class="infoLabel">Full Address</div>
                    <div class="infoValue">
                        {{ $provider->address }},
                        {{ $provider->barangay }},
                        {{ $provider->city }},
                        {{ $provider->province }},
                        {{ $provider->region }}
                    </div>
                </div>
            </div>
        </div>

        <div class="section">
            <h5 class="sectionTitle">Customer Ratings & Feedback</h5>
            <div class="sectionSub">Filter, sort, and browse customer reviews.</div>

            @if($count <= 0)
                <div class="empty">
                    <div style="font-weight:950;color:rgba(255,255,255,.92)">No ratings yet</div>
                </div>
            @else

                <div class="summary">
                    <div class="summaryCard">
                        <div class="bigAvg">{{ $avgText }}</div>
                        <div class="stars" aria-label="Average rating">
                            @for($i=1;$i<=5;$i++)
                                <svg class="star {{ $i <= $avgRounded ? 'on' : '' }}" viewBox="0 0 24 24" aria-hidden="true">
                                    <path d="M12 17.27 18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/>
                                </svg>
                            @endfor
                        </div>
                        <div class="smallNote">{{ $count }} total review{{ $count===1?'':'s' }}</div>

                        @php $total = max(1, $count); @endphp

                        <div class="dist" aria-label="Rating distribution">
                            @for($s=5;$s>=1;$s--)
                                @php $pct = ($dist[$s] / $total) * 100; @endphp
                                <div class="distRow">
                                    <div class="distLabel">{{ $s }}★</div>
                                    <div class="bar"><span style="width: {{ $pct }}%"></span></div>
                                    <div class="distCount">{{ $dist[$s] }}</div>
                                </div>
                            @endfor
                        </div>
                    </div>

                    <div class="filterBar" id="filterBar">
                        <div class="control">
                            <label for="ratingFilter">Rating</label>
                            <select id="ratingFilter" class="selectDark">
                                <option value="all">All ratings</option>
                                <option value="5">5 ★</option>
                                <option value="4">4 ★</option>
                                <option value="3">3 ★</option>
                                <option value="2">2 ★</option>
                                <option value="1">1 ★</option>
                            </select>
                        </div>

                        <div class="control">
                            <label for="sortFilter">Sort</label>
                            <select id="sortFilter" class="selectDark">
                                <option value="recent">Most recent</option>
                                <option value="oldest">Oldest</option>
                                <option value="relevance">Relevance</option>
                                <option value="highest">Highest rating</option>
                                <option value="lowest">Lowest rating</option>
                            </select>
                        </div>

                        <div class="control" style="min-width:240px; flex: 1.3;">
                            <label for="searchFilter">Search</label>
                            <input id="searchFilter" class="searchDark" type="text" placeholder="Search keywords (e.g., great, fast, clean)">
                        </div>

                        <div style="width:100%; display:flex; justify-content:space-between; gap:.75rem; align-items:center; margin-top:.25rem;">
                            <div class="hint">
                                Showing <span id="shownCount" style="color:rgba(255,255,255,.9); font-weight:900;">{{ $reviews->count() }}</span>
                                of {{ $reviews->count() }} review{{ $reviews->count()===1?'':'s' }}.
                            </div>
                            <button type="button" id="resetFilters"
                                    class="btnGhost"
                                    style="padding:.6rem .85rem; border-radius: 14px;">
                                Reset
                            </button>
                        </div>
                    </div>
                </div>

                <div class="reviewList" id="reviewList">
                    @foreach($reviews as $rev)
                        @php
                            $rv = (int)($rev->rating ?? 0);
                            $dt = isset($rev->created_at) ? \Carbon\Carbon::parse($rev->created_at) : null;
                            $ts = $dt ? $dt->timestamp : 0;

                            $comment = (string)($rev->comment ?? '');
                            $name = $rev->customer_name ?? 'Customer';

                            $customerImage = $rev->customer_profile_image ?? '';

                            $reviewerAvatar = asset('images/avatar-placeholder.svg');

                            if (!empty($customerImage) && file_exists(public_path('uploads/customers/' . $customerImage))) {
                                $reviewerAvatar = asset('uploads/customers/' . $customerImage) . '?v=' . time();
                            }
                        @endphp

                        <div class="reviewCard"
                             data-rating="{{ $rv }}"
                             data-ts="{{ $ts }}"
                             data-name="{{ strtolower($name) }}"
                             data-text="{{ strtolower($comment) }}">

                            <div class="reviewTop">
                                <div class="reviewerRow">
                                    <img
                                        src="{{ $reviewerAvatar }}"
                                        alt="Reviewer avatar"
                                        class="reviewerAvatar"
                                        onerror="this.onerror=null;this.src='{{ asset('images/avatar-placeholder.svg') }}';"
                                    >

                                    <div class="reviewerBlock">
                                        <div class="reviewerName">{{ $name }}</div>

                                        <div class="reviewMeta">
                                            <span class="stars" aria-label="Rating {{ $rv }} out of 5">
                                                @for($i=1;$i<=5;$i++)
                                                    <svg class="star {{ $i <= $rv ? 'on' : '' }}" viewBox="0 0 24 24" aria-hidden="true">
                                                        <path d="M12 17.27 18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/>
                                                    </svg>
                                                @endfor
                                            </span>

                                            <span class="badge">{{ $rv }}★</span>

                                            @if($dt)
                                                <span style="color:var(--muted); font-weight:800;">• {{ $dt->format('M d, Y') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <div class="badge" title="Review timestamp">
                                    {{ $dt ? $dt->diffForHumans() : '' }}
                                </div>
                            </div>

                            <div class="comment">
                                @php $cleanComment = trim((string)($rev->comment ?? '')); @endphp
                                {{ $cleanComment !== '' ? $cleanComment : 'No comment.' }}
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="empty" id="noResults" style="display:none;">
                    <div style="font-weight:950;color:rgba(255,255,255,.92)">No results</div>
                    <div style="margin-top:.25rem;">Try changing the rating, sort, or search keywords.</div>
                </div>

            @endif
        </div>

    </div>
</div>

@if($count > 0)
<script>
(function(){
    const list = document.getElementById('reviewList');
    if(!list) return;

    const ratingFilter = document.getElementById('ratingFilter');
    const sortFilter   = document.getElementById('sortFilter');
    const searchFilter = document.getElementById('searchFilter');
    const resetBtn     = document.getElementById('resetFilters');
    const shownCount   = document.getElementById('shownCount');
    const noResults    = document.getElementById('noResults');

    const items = Array.from(list.querySelectorAll('.reviewCard'));

    function getVisibleItems(){
        return items.filter(el => el.style.display !== 'none');
    }

    function applyFilter(){
        const ratingVal = ratingFilter ? ratingFilter.value : 'all';
        const q = (searchFilter ? searchFilter.value : '').trim().toLowerCase();

        let shown = 0;

        items.forEach(el => {
            const r = el.getAttribute('data-rating') || '0';
            const text = (el.getAttribute('data-text') || '');
            const name = (el.getAttribute('data-name') || '');
            const matchRating = (ratingVal === 'all') || (r === ratingVal);
            const matchSearch = !q || text.includes(q) || name.includes(q);

            const show = matchRating && matchSearch;
            el.style.display = show ? '' : 'none';
            if(show) shown++;
        });

        if(shownCount) shownCount.textContent = shown;

        if(noResults){
            noResults.style.display = (shown === 0) ? '' : 'none';
        }

        applySort();
    }

    function relevanceScore(el){
        const rating = parseInt(el.getAttribute('data-rating') || '0', 10);
        const ts     = parseInt(el.getAttribute('data-ts') || '0', 10);
        return (rating * 1000000000) + ts;
    }

    function applySort(){
        const mode = sortFilter ? sortFilter.value : 'recent';
        const visible = getVisibleItems();

        visible.sort((a,b) => {
            const ra = parseInt(a.getAttribute('data-rating') || '0', 10);
            const rb = parseInt(b.getAttribute('data-rating') || '0', 10);
            const ta = parseInt(a.getAttribute('data-ts') || '0', 10);
            const tb = parseInt(b.getAttribute('data-ts') || '0', 10);

            if(mode === 'recent')  return tb - ta;
            if(mode === 'oldest')  return ta - tb;
            if(mode === 'highest') return (rb - ra) || (tb - ta);
            if(mode === 'lowest')  return (ra - rb) || (tb - ta);
            if(mode === 'relevance') return relevanceScore(b) - relevanceScore(a);

            return tb - ta;
        });

        visible.forEach(el => list.appendChild(el));
    }

    function reset(){
        if(ratingFilter) ratingFilter.value = 'all';
        if(sortFilter) sortFilter.value = 'recent';
        if(searchFilter) searchFilter.value = '';
        applyFilter();
    }

    if(ratingFilter) ratingFilter.addEventListener('change', applyFilter);
    if(sortFilter) sortFilter.addEventListener('change', applySort);
    if(searchFilter) searchFilter.addEventListener('input', applyFilter);
    if(resetBtn) resetBtn.addEventListener('click', reset);

    applyFilter();
})();
</script>
@endif

@endsection
