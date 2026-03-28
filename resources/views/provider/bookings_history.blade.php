@extends('provider.layouts.app')

@section('title', 'Past Bookings')

@section('content')

<style>
:root{
    --page-bg:#020617;
    --card-bg:#071224;
    --border-soft:rgba(255,255,255,.08);
    --text-muted:rgba(255,255,255,.62);
    --accent:#38bdf8;
    --success:#22c55e;
    --danger:#ef4444;
}

.history-shell{ display:grid; gap:1rem; }

.history-head h2{
    margin:0;
    color:#fff;
    font-size:1.8rem;
    font-weight:900;
}

.history-head p{
    margin:.35rem 0 0;
    color:var(--text-muted);
}

.panel{
    background:linear-gradient(180deg,var(--card-bg),var(--page-bg));
    border:1px solid var(--border-soft);
    border-radius:18px;
    padding:1rem;
}

.filter-grid{
    display:grid;
    grid-template-columns:2fr 1fr 1fr 1fr auto;
    gap:.75rem;
    align-items:end;
}

.field label{
    display:block;
    margin-bottom:.35rem;
    color:var(--text-muted);
    font-size:.72rem;
    text-transform:uppercase;
    letter-spacing:.05em;
    font-weight:800;
}

.field input,
.field select{
    width:100%;
    background:#020617;
    color:#fff;
    border:1px solid var(--border-soft);
    border-radius:10px;
    padding:.7rem .8rem;
}

.actions-row{
    display:flex;
    gap:.65rem;
    flex-wrap:wrap;
    margin-top:.85rem;
}

.btnx,
.btn-ghost,
.btn-view{
    display:inline-flex;
    align-items:center;
    justify-content:center;
    text-decoration:none;
    border-radius:10px;
    font-weight:900;
}

.btnx{
    border:none;
    background:linear-gradient(180deg,#0ea5e9,#38bdf8);
    color:#fff;
    padding:.72rem 1rem;
}

.btn-ghost{
    border:1px solid rgba(255,255,255,.12);
    color:#fff;
    padding:.72rem 1rem;
}

.btn-view{
    border:1px solid rgba(56,189,248,.24);
    background:rgba(56,189,248,.10);
    color:#fff;
    padding:.55rem .85rem;
    white-space:nowrap;
    min-width:132px;
}

.result-note{
    margin-bottom:.9rem;
    color:var(--text-muted);
    font-size:.86rem;
}

.flash{
    border-radius:14px;
    padding:.9rem 1rem;
    border:1px solid rgba(239,68,68,.25);
    background:rgba(239,68,68,.10);
    color:#fecaca;
    font-weight:700;
}

.empty-state{
    text-align:center;
    color:var(--text-muted);
    padding:2rem 1rem;
    font-weight:700;
}

.desktop-table{
    border:1px solid var(--border-soft);
    border-radius:16px;
    overflow:hidden;
}

.desktop-table table{
    width:100%;
    border-collapse:collapse;
}

.desktop-table thead{
    background:rgba(56,189,248,.08);
}

.desktop-table th,
.desktop-table td{
    padding:.9rem;
    border-bottom:1px solid rgba(255,255,255,.06);
    vertical-align:top;
}

.desktop-table th{
    color:rgba(255,255,255,.72);
    text-transform:uppercase;
    letter-spacing:.08em;
    font-size:.72rem;
    font-weight:900;
}

.desktop-table td{
    color:#fff;
    font-size:.9rem;
}

.desktop-table th:last-child,
.desktop-table td:last-child{
    white-space:nowrap;
    width:1%;
}

.desktop-table tbody tr:last-child td{
    border-bottom:none;
}

.desktop-table tbody tr:hover{
    background:rgba(255,255,255,.02);
}

.person-name,
.service-name{
    color:#fff;
    font-weight:900;
}

.subtext{
    color:var(--text-muted);
    font-size:.82rem;
    line-height:1.4;
}

.status-badge{
    display:inline-flex;
    align-items:center;
    justify-content:center;
    border-radius:999px;
    padding:.38rem .75rem;
    font-size:.7rem;
    font-weight:900;
    text-transform:uppercase;
    letter-spacing:.05em;
}

.status-badge.completed{ background:rgba(34,197,94,.14); color:var(--success); }
.status-badge.paid{ background:rgba(56,189,248,.14); color:var(--accent); }
.status-badge.cancelled{ background:rgba(239,68,68,.14); color:var(--danger); }

.mobile-list{
    display:none;
    gap:.85rem;
}

.history-card{
    border:1px solid var(--border-soft);
    border-radius:16px;
    background:rgba(255,255,255,.02);
    padding:1rem;
}

.history-card-head{
    display:flex;
    justify-content:space-between;
    gap:.8rem;
    align-items:flex-start;
    margin-bottom:.8rem;
}

.history-grid{
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:.7rem .9rem;
}

.label{
    color:var(--text-muted);
    font-size:.72rem;
    text-transform:uppercase;
    letter-spacing:.05em;
    margin-bottom:.15rem;
}

.value{
    color:#fff;
    font-weight:800;
    line-height:1.35;
    word-break:break-word;
}

.history-actions{
    margin-top:1rem;
    display:flex;
    justify-content:flex-end;
    gap:.65rem;
    flex-wrap:wrap;
}

.action-stack{
    display:flex;
    align-items:center;
    justify-content:flex-end;
    gap:.65rem;
    flex-wrap:wrap;
}

.btn-rate{
    border:1px solid rgba(34,197,94,.24);
    background:rgba(34,197,94,.10);
    color:#dcfce7;
    padding:.55rem .85rem;
    white-space:nowrap;
    min-width:132px;
}

.btn-rate.edit{
    border-color:rgba(251,191,36,.24);
    background:rgba(251,191,36,.10);
    color:#fde68a;
}

.btn-rate.view{
    border-color:rgba(168,85,247,.24);
    background:rgba(168,85,247,.10);
    color:#e9d5ff;
}

@media (max-width: 1100px){
    .filter-grid{ grid-template-columns:1fr 1fr; }
    .filter-grid .search-field,
    .filter-grid .apply-field{ grid-column:1 / -1; }
    .desktop-table{ display:none; }
    .mobile-list{ display:grid; }
}

@media (max-width: 640px){
    .history-head h2{ font-size:1.45rem; }
    .filter-grid{ grid-template-columns:1fr; }
    .history-grid{ grid-template-columns:1fr; }
}
</style>

<div class="history-shell">
    <div class="history-head">
        <h2>Past Bookings</h2>
        <p>Review completed, paid, and cancelled jobs.</p>
    </div>

    @if(!empty($loadError))
        <div class="flash">{{ $loadError }}</div>
    @endif

    <div class="panel">
        <form method="GET" action="{{ route('provider.bookings.history') }}">
            <div class="filter-grid">
                <div class="field search-field">
                    <label>Search</label>
                    <input type="text" name="q" value="{{ $q ?? '' }}" placeholder="Reference, customer, email, service, option...">
                </div>
                <div class="field">
                    <label>Status</label>
                    <select name="status">
                        <option value="all" {{ ($status ?? 'all') === 'all' ? 'selected' : '' }}>All</option>
                        <option value="completed" {{ ($status ?? '') === 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="paid" {{ ($status ?? '') === 'paid' ? 'selected' : '' }}>Paid</option>
                        <option value="cancelled" {{ ($status ?? '') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        <option value="not_completed" {{ ($status ?? '') === 'not_completed' ? 'selected' : '' }}>Not Completed</option>
                    </select>
                </div>
                <div class="field">
                    <label>From</label>
                    <input type="date" name="from" value="{{ $from ?? '' }}">
                </div>
                <div class="field">
                    <label>To</label>
                    <input type="date" name="to" value="{{ $to ?? '' }}">
                </div>
                <div class="field apply-field">
                    <label>&nbsp;</label>
                    <button class="btnx" type="submit">Apply</button>
                </div>
            </div>

            <div class="actions-row">
                <a class="btn-ghost" href="{{ route('provider.bookings.history') }}">Reset</a>
                <a class="btn-ghost" href="{{ route('provider.bookings') }}">Back to Active</a>
            </div>
        </form>
    </div>

    <div class="panel">
        @if($bookings->isEmpty())
            <div class="empty-state">No past bookings found.</div>
        @else
            <div class="result-note">Showing past bookings history.</div>

            <div class="desktop-table">
                <table>
                    <thead>
                        <tr>
                            <th>Customer</th>
                            <th>Service</th>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Price</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($bookings as $b)
                            @php($statusKey = data_get($b, 'status_key') ?? strtolower((string) data_get($b, 'status')))
                            @php($canOpenRating = \Illuminate\Support\Facades\Route::has('provider.customer-ratings') && !empty(data_get($b, 'id')) && in_array($statusKey, ['completed', 'paid'], true))
                            <tr>
                                <td>
                                    <div class="person-name">{{ $b->name }}</div>
                                    <div class="subtext">{{ $b->display_email }}</div>
                                    <div class="subtext">Ref: {{ $b->reference_code }}</div>
                                </td>
                                <td>
                                    <div class="service-name">{{ $b->service }}</div>
                                    <div class="subtext">{{ $b->display_option }}</div>
                                </td>
                                <td>{{ $b->display_booking_date }}</td>
                                <td>{{ $b->display_time_range }}</td>
                                <td>PHP {{ $b->display_price }}</td>
                                <td>
                                    <span class="status-badge {{ $statusKey }}">{{ strtoupper(str_replace('_', ' ', $statusKey)) }}</span>
                                </td>
                                <td>
                                    <div class="action-stack">
                                        @if(\Illuminate\Support\Facades\Route::has('provider.bookings.show'))
                                            <a class="btn-view" href="{{ route('provider.bookings.show', $b->reference_code) }}">View Details</a>
                                        @endif

                                        @if($canOpenRating)
                                            @php
                                                $hasCustomerRating = (bool) data_get($b, 'has_customer_rating', false);
                                                $canEditCustomerRating = (bool) data_get($b, 'can_edit_customer_rating', false);
                                                $rateLabel = !$hasCustomerRating
                                                    ? 'Rate Customer'
                                                    : ($canEditCustomerRating ? 'Edit Rating' : 'View Rating');
                                                $rateClass = !$hasCustomerRating
                                                    ? 'btn-rate'
                                                    : ($canEditCustomerRating ? 'btn-rate edit' : 'btn-rate view');
                                            @endphp

                                            <a class="{{ $rateClass }}" href="{{ route('provider.customer-ratings', ['booking' => data_get($b, 'id')]) }}">
                                                {{ $rateLabel }}
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mobile-list">
                @foreach($bookings as $b)
                    @php($statusKey = data_get($b, 'status_key') ?? strtolower((string) data_get($b, 'status')))
                    @php($canOpenRating = \Illuminate\Support\Facades\Route::has('provider.customer-ratings') && !empty(data_get($b, 'id')) && in_array($statusKey, ['completed', 'paid'], true))
                    <div class="history-card">
                        <div class="history-card-head">
                            <div>
                                <div class="person-name">{{ $b->name }}</div>
                                <div class="subtext">{{ $b->service }}</div>
                                <div class="subtext">Ref: {{ $b->reference_code }}</div>
                            </div>
                            <span class="status-badge {{ $statusKey }}">{{ strtoupper(str_replace('_', ' ', $statusKey)) }}</span>
                        </div>

                        <div class="history-grid">
                            <div>
                                <div class="label">Email</div>
                                <div class="value">{{ $b->display_email }}</div>
                            </div>
                            <div>
                                <div class="label">Option</div>
                                <div class="value">{{ $b->display_option }}</div>
                            </div>
                            <div>
                                <div class="label">Date</div>
                                <div class="value">{{ $b->display_booking_date }}</div>
                            </div>
                            <div>
                                <div class="label">Time</div>
                                <div class="value">{{ $b->display_time_range }}</div>
                            </div>
                            <div>
                                <div class="label">Price</div>
                                <div class="value">PHP {{ $b->display_price }}</div>
                            </div>
                        </div>

                        <div class="history-actions">
                            @if(\Illuminate\Support\Facades\Route::has('provider.bookings.show'))
                                <a class="btn-view" href="{{ route('provider.bookings.show', $b->reference_code) }}">View Details</a>
                            @endif

                            @if($canOpenRating)
                                @php
                                    $hasCustomerRating = (bool) data_get($b, 'has_customer_rating', false);
                                    $canEditCustomerRating = (bool) data_get($b, 'can_edit_customer_rating', false);
                                    $rateLabel = !$hasCustomerRating
                                        ? 'Rate Customer'
                                        : ($canEditCustomerRating ? 'Edit Rating' : 'View Rating');
                                    $rateClass = !$hasCustomerRating
                                        ? 'btn-rate'
                                        : ($canEditCustomerRating ? 'btn-rate edit' : 'btn-rate view');
                                @endphp

                                <a class="{{ $rateClass }}" href="{{ route('provider.customer-ratings', ['booking' => data_get($b, 'id')]) }}">
                                    {{ $rateLabel }}
                                </a>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>

@endsection
