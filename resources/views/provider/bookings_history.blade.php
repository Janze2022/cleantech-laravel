@extends('provider.layouts.app')

@section('title', 'Past Bookings')

@section('content')

@php
    use Illuminate\Support\Facades\Route;
    use Carbon\Carbon;
@endphp

<style>
:root{
    --bg-card:#020b1f;
    --bg-deep:#020617;
    --border-soft:rgba(255,255,255,.08);
    --border-row:rgba(255,255,255,.06);
    --text-muted:rgba(255,255,255,.58);
    --text-soft:rgba(255,255,255,.84);
    --accent:#38bdf8;
    --success:#22c55e;
    --warn:#facc15;
    --danger:#ef4444;
}

.muted{ color:var(--text-muted); }
.smallx{ font-size:.78rem; }
.tiny{ font-size:.72rem; }

.page-head{
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:.75rem;
    margin-bottom:1rem;
}

.page-head h4{
    margin:0;
    font-size:1.05rem;
    font-weight:800;
    color:#fff;
}

.cardx{
    background:linear-gradient(180deg,var(--bg-card),var(--bg-deep));
    border:1px solid var(--border-soft);
    border-radius:14px;
    padding:1rem;
    box-shadow:0 8px 30px rgba(0,0,0,.18);
}

.filter-grid{
    display:grid;
    grid-template-columns:2fr 1.1fr 1fr 1fr auto;
    gap:.7rem;
    align-items:end;
}

.field label{
    display:block;
    margin-bottom:.34rem;
    font-size:.72rem;
    font-weight:700;
    color:var(--text-muted);
    letter-spacing:.02em;
}

.input,
select{
    width:100%;
    height:40px;
    background:#020617 !important;
    border:1px solid var(--border-soft) !important;
    color:#fff !important;
    border-radius:10px;
    padding:.55rem .75rem;
    font-size:.86rem;
    outline:none;
    box-shadow:none !important;
}

.input::placeholder{
    color:rgba(255,255,255,.35);
}

select option{
    background:#020617;
    color:#fff;
}

.actions-row{
    display:flex;
    flex-wrap:wrap;
    gap:.55rem;
    margin-top:.75rem;
}

.btnx,
.btn-ghost,
.btn-view{
    height:40px;
    padding:.58rem .9rem;
    border-radius:10px;
    font-size:.82rem;
    font-weight:800;
    display:inline-flex;
    align-items:center;
    justify-content:center;
    text-decoration:none;
    white-space:nowrap;
}

.btnx{
    background:linear-gradient(180deg,#0ea5e9,#38bdf8);
    border:none;
    color:#fff;
}

.btn-ghost{
    border:1px solid rgba(255,255,255,.12);
    background:transparent;
    color:#fff;
}

.btn-view{
    background:rgba(56,189,248,.10);
    border:1px solid rgba(56,189,248,.24);
    color:#fff;
    padding:.48rem .78rem;
    height:36px;
}

.btn-view:hover,
.btn-ghost:hover,
.btnx:hover{
    filter:brightness(1.04);
    color:#fff;
}

.result-note{
    margin-bottom:.85rem;
    font-size:.78rem;
    color:var(--text-muted);
}

/* DESKTOP TABLE */
.desktop-table{
    display:block;
}

.mobile-cards{
    display:none;
}

/* FORCE DARK TABLE HEADER */
.tablewrap table.table thead,
.tablewrap table.table thead tr,
.tablewrap table.table thead th{
    background:#020b1f !important;
    background-color:#020b1f !important;
    color:rgba(255,255,255,.72) !important;
}

/* remove any white bootstrap table style */
.tablewrap table.table{
    --bs-table-bg: transparent !important;
    --bs-table-striped-bg: transparent !important;
    --bs-table-hover-bg: rgba(56,189,248,.03) !important;
    --bs-table-border-color: rgba(255,255,255,.06) !important;
    background:transparent !important;
    color:#fff !important;
}

.tablewrap table.table > :not(caption) > * > *{
    background-color: transparent !important;
    box-shadow: none !important;
}

/* then re-apply dark header */
.tablewrap table.table > thead > tr > th{
    background:#071224 !important;
    color:rgba(255,255,255,.72) !important;
    border-bottom:1px solid rgba(255,255,255,.06) !important;
}
.tablewrap{
    border:1px solid var(--border-soft);
    border-radius:14px;
    overflow:hidden;
    overflow-x:auto;
    -webkit-overflow-scrolling:touch;
}

.tablewrap table{
    width:100%;
    min-width:860px;
    margin:0;
    border-collapse:collapse;
    background:transparent !important;
}

.tablewrap thead{
    background:rgba(56,189,248,.07);
}

.tablewrap th{
    color:rgba(255,255,255,.68) !important;
    font-size:.69rem;
    letter-spacing:.07em;
    text-transform:uppercase;
    font-weight:800;
    padding:.72rem .75rem;
    border-bottom:1px solid var(--border-row) !important;
    white-space:nowrap;
}

.tablewrap td{
    color:#fff !important;
    font-size:.84rem;
    padding:.7rem .75rem;
    border-bottom:1px solid var(--border-row) !important;
    vertical-align:middle;
    background:transparent !important;
}

.tablewrap tbody tr:hover td{
    background:rgba(56,189,248,.03) !important;
}

.person-name,
.service-name{
    font-size:.85rem;
    font-weight:700;
    line-height:1.2;
    color:#fff;
}

.subtext{
    font-size:.74rem;
    color:var(--text-muted);
    margin-top:.15rem;
    line-height:1.25;
}

.badge{
    display:inline-flex;
    align-items:center;
    justify-content:center;
    padding:.28rem .58rem;
    border-radius:999px;
    font-size:.63rem;
    font-weight:900;
    letter-spacing:.04em;
    text-transform:uppercase;
    line-height:1;
}

.badge.completed{ background:rgba(34,197,94,.14); color:var(--success); }
.badge.paid{ background:rgba(56,189,248,.14); color:var(--accent); }
.badge.cancelled{ background:rgba(239,68,68,.14); color:var(--danger); }
.badge.not_completed{ background:rgba(250,204,21,.14); color:var(--warn); }

.empty-state{
    text-align:center;
    padding:1.5rem 1rem;
    color:var(--text-muted);
    font-size:.9rem;
}

/* MOBILE CARDS */
@media (max-width: 991.98px){
    .filter-grid{
        grid-template-columns:1fr 1fr;
    }

    .filter-grid .field.search-field{
        grid-column:1 / -1;
    }

    .filter-grid .apply-field{
        grid-column:1 / -1;
    }

    .filter-grid .apply-field .btnx{
        width:100%;
    }
}

@media (max-width: 767.98px){
    .page-head{
        margin-bottom:.8rem;
    }

    .page-head h4{
        font-size:.96rem;
    }

    .cardx{
        padding:.8rem;
        border-radius:12px;
    }

    .filter-grid{
        grid-template-columns:1fr;
        gap:.6rem;
    }

    .field label{
        font-size:.7rem;
        margin-bottom:.28rem;
    }

    .input,
    select,
    .btnx,
    .btn-ghost{
        height:38px;
        font-size:.82rem;
    }

    .actions-row{
        display:grid;
        grid-template-columns:1fr 1fr;
        gap:.55rem;
    }

    .actions-row .btn-ghost{
        width:100%;
    }

    .desktop-table{
        display:none;
    }

    .mobile-cards{
        display:grid;
        gap:.7rem;
    }

    .booking-card{
        border:1px solid var(--border-soft);
        border-radius:12px;
        background:rgba(255,255,255,.02);
        padding:.8rem;
    }

    .booking-top{
        display:flex;
        justify-content:space-between;
        gap:.6rem;
        align-items:flex-start;
        margin-bottom:.65rem;
    }

    .booking-name{
        font-size:.88rem;
        font-weight:800;
        color:#fff;
        line-height:1.2;
        margin-bottom:.18rem;
    }

    .booking-service{
        font-size:.8rem;
        color:var(--text-soft);
        font-weight:700;
        margin-bottom:.1rem;
    }

    .booking-ref{
        font-size:.72rem;
        color:var(--text-muted);
    }

    .booking-grid{
        display:grid;
        grid-template-columns:1fr 1fr;
        gap:.55rem .75rem;
        margin-bottom:.7rem;
    }

    .meta-label{
        font-size:.66rem;
        text-transform:uppercase;
        letter-spacing:.05em;
        color:var(--text-muted);
        margin-bottom:.12rem;
        font-weight:800;
    }

    .meta-value{
        font-size:.8rem;
        color:#fff;
        font-weight:700;
        line-height:1.25;
    }

    .booking-actions{
        display:flex;
        justify-content:flex-end;
    }

    .booking-actions .btn-view{
        width:100%;
        height:38px;
    }
}

@media (max-width: 420px){
    .actions-row{
        grid-template-columns:1fr;
    }

    .booking-grid{
        grid-template-columns:1fr;
    }
}
</style>

<div class="page-head">
    <h4>Past Bookings</h4>
</div>

<div class="cardx mb-3">
    <form method="GET" action="{{ route('provider.bookings.history') }}">
        <div class="filter-grid">
            <div class="field search-field">
                <label>Search</label>
                <input
                    class="input"
                    type="text"
                    name="q"
                    value="{{ $q ?? '' }}"
                    placeholder="Reference, customer, email, service, option..."
                >
            </div>

            <div class="field">
                <label>Status</label>
                <select name="status">
                    <option value="all" {{ ($status ?? 'all')==='all' ? 'selected' : '' }}>All</option>
                    <option value="completed" {{ ($status ?? '')==='completed' ? 'selected' : '' }}>Completed</option>
                    <option value="paid" {{ ($status ?? '')==='paid' ? 'selected' : '' }}>Paid</option>
                    <option value="cancelled" {{ ($status ?? '')==='cancelled' ? 'selected' : '' }}>Cancelled</option>
                    <option value="not_completed" {{ ($status ?? '')==='not_completed' ? 'selected' : '' }}>Not Completed</option>
                </select>
            </div>

            <div class="field">
                <label>From</label>
                <input type="date" class="input" name="from" value="{{ $from ?? '' }}">
            </div>

            <div class="field">
                <label>To</label>
                <input type="date" class="input" name="to" value="{{ $to ?? '' }}">
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

<div class="cardx">

    @if($bookings->isEmpty())
        <div class="empty-state">
            No past bookings found.
        </div>
    @else

        <div class="result-note">
            Showing past bookings history
        </div>

        {{-- DESKTOP / TABLET TABLE --}}
        <div class="desktop-table">
            <div class="tablewrap">
                <table class="table mb-0">
                    <thead>
                        <tr>
                            <th>Customer</th>
                            <th>Service</th>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Price</th>
                            <th>Status</th>
                            <th>Completed?</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($bookings as $b)
                            @php
                                $st = strtolower((string)$b->status);
                                $isCompleted = $st === 'completed';
                            @endphp
                            <tr>
                                <td>
                                    <div class="person-name">{{ $b->name }}</div>
                                    <div class="subtext">{{ $b->email ?? '—' }}</div>
                                    <div class="subtext">Ref: {{ $b->reference_code }}</div>
                                </td>

                                <td>
                                    <div class="service-name">{{ $b->service }}</div>
                                    <div class="subtext">{{ $b->option }}</div>
                                </td>

                                <td>{{ $b->booking_date }}</td>
                                <td>{{ Carbon::parse($b->time_start)->format('g:i A') }} – {{ Carbon::parse($b->time_end)->format('g:i A') }}</td>
                                <td>₱{{ number_format($b->price ?? 0, 2) }}</td>

                                <td>
                                    <span class="badge {{ $st }}">
                                        {{ strtoupper(str_replace('_',' ',$st)) }}
                                    </span>
                                </td>

                                <td>
                                    @if($isCompleted)
                                        <span class="badge completed">YES</span>
                                    @else
                                        <span class="badge cancelled">NO</span>
                                    @endif
                                </td>

                                <td>
                                    @if(Route::has('provider.bookings.show'))
                                        <a class="btn-view" href="{{ route('provider.bookings.show', $b->reference_code) }}">
                                            View Details
                                        </a>
                                    @else
                                        <span class="subtext">No details route.</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- MOBILE CARDS --}}
        <div class="mobile-cards">
            @foreach($bookings as $b)
                @php
                    $st = strtolower((string)$b->status);
                    $isCompleted = $st === 'completed';
                @endphp

                <div class="booking-card">
                    <div class="booking-top">
                        <div>
                            <div class="booking-name">{{ $b->name }}</div>
                            <div class="booking-service">{{ $b->service }}</div>
                            <div class="booking-ref">Ref: {{ $b->reference_code }}</div>
                        </div>

                        <div>
                            <span class="badge {{ $st }}">
                                {{ strtoupper(str_replace('_',' ',$st)) }}
                            </span>
                        </div>
                    </div>

                    <div class="booking-grid">
                        <div>
                            <div class="meta-label">Email</div>
                            <div class="meta-value">{{ $b->email ?? '—' }}</div>
                        </div>

                        <div>
                            <div class="meta-label">Option</div>
                            <div class="meta-value">{{ $b->option ?: '—' }}</div>
                        </div>

                        <div>
                            <div class="meta-label">Date</div>
                            <div class="meta-value">{{ $b->booking_date }}</div>
                        </div>

                        <div>
                            <div class="meta-label">Time</div>
                            <div class="meta-value">{{ $b->time_start }} – {{ $b->time_end }}</div>
                        </div>

                        <div>
                            <div class="meta-label">Price</div>
                            <div class="meta-value">₱{{ number_format($b->price ?? 0, 2) }}</div>
                        </div>

                        <div>
                            <div class="meta-label">Completed?</div>
                            <div class="meta-value">
                                @if($isCompleted)
                                    <span class="badge completed">YES</span>
                                @else
                                    <span class="badge cancelled">NO</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="booking-actions">
                        @if(Route::has('provider.bookings.show'))
                            <a class="btn-view" href="{{ route('provider.bookings.show', $b->reference_code) }}">
                                View Details
                            </a>
                        @else
                            <span class="subtext">No details route.</span>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

    @endif

</div>

@endsection