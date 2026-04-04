@extends('customer.layouts.app')

@section('title', 'Bookings History')

@section('content')

@php
    $q       = request('q');
    $status  = request('status');
    $from    = request('from');
    $to      = request('to');
    $min     = request('min');
    $max     = request('max');
@endphp

<style>
:root{
    --bg-deep:#020617;
    --bg-card:#020b1f;
    --border-soft:rgba(255,255,255,.08);
    --text-muted:rgba(255,255,255,.55);
    --accent:#38bdf8;
    --success:#22c55e;
    --danger:#ef4444;
    --warn:#f59e0b;
}

.card-dark{
    background: linear-gradient(180deg, var(--bg-card), var(--bg-deep));
    border: 1px solid var(--border-soft);
    border-radius: 18px;
}
.card-pad{ padding: 1.25rem; }

.page-title{ font-weight:900; letter-spacing:-.02em; }
.page-sub{ color: var(--text-muted); }

.input-dark, .select-dark{
    background: rgba(2, 6, 23, .55) !important;
    border: 1px solid rgba(255,255,255,.10) !important;
    color: rgba(255,255,255,.88) !important;
    border-radius: 12px !important;
}
.input-dark::placeholder{ color: rgba(255,255,255,.35); }

.btn-outline-accent{
    background: transparent;
    border: 1px solid rgba(56,189,248,.45);
    color: var(--accent);
    font-weight: 800;
    border-radius: 12px;
    padding: .7rem 1rem;
}
.btn-outline-accent:hover{
    background: rgba(56,189,248,.08);
    color: var(--accent);
}

.small-muted{ color: var(--text-muted); font-size:.88rem; }

.table-wrap{
    background: rgba(2, 6, 23, .30);
    border: 1px solid var(--border-soft);
    border-radius: 16px;
    padding: .35rem;
    overflow-x: auto;
    overflow-y: hidden;
    -webkit-overflow-scrolling: touch;
    scrollbar-width: thin;
    scrollbar-color: rgba(56,189,248,.30) rgba(255,255,255,.04);
}

.table-wrap::-webkit-scrollbar{
    height: 10px;
}

.table-wrap::-webkit-scrollbar-track{
    background: rgba(255,255,255,.04);
    border-radius: 999px;
}

.table-wrap::-webkit-scrollbar-thumb{
    background: rgba(56,189,248,.30);
    border-radius: 999px;
}

.table-darkish{
    width: 100%;
    min-width: 1120px;
    margin: 0;
    color: rgba(255,255,255,.86);
    background: transparent !important;
}
.table-darkish thead th{
    background: rgba(2, 6, 23, .45) !important;
    color: rgba(255,255,255,.70) !important;
    font-size:.78rem;
    letter-spacing:.10em;
    text-transform:uppercase;
    border-bottom: 1px solid rgba(255,255,255,.10) !important;
    white-space: nowrap;
}
.table-darkish tbody tr{
    background: rgba(2, 6, 23, .35) !important;
}
.table-darkish tbody tr:nth-child(even){
    background: rgba(2, 6, 23, .26) !important;
}
.table-darkish tbody tr:hover{
    background: rgba(56,189,248,.06) !important;
}
.table-darkish td{
    background: transparent !important;
    border-top: 1px solid rgba(255,255,255,.06) !important;
    vertical-align: middle;
}
.table-darkish tbody td,
.table-darkish tbody th{
    color: rgba(255,255,255,.92) !important;
}

.badge-soft{
    display:inline-flex;
    align-items:center;
    padding:.25rem .6rem;
    border-radius: 999px;
    font-size:.75rem;
    border: 1px solid rgba(255,255,255,.10);
    color: rgba(255,255,255,.78);
    background: rgba(2,6,23,.25);
    font-weight: 900;
    white-space: nowrap;
}
.badge-soft.success{ border-color: rgba(34,197,94,.35); color: rgba(34,197,94,.95); }
.badge-soft.warn{ border-color: rgba(245,158,11,.35); color: rgba(245,158,11,.95); }
.badge-soft.danger{ border-color: rgba(239,68,68,.35); color: rgba(239,68,68,.95); }
.badge-soft.muted{ border-color: rgba(255,255,255,.12); color: rgba(255,255,255,.70); }

.btn-view{
    display:inline-flex;
    align-items:center;
    gap:.45rem;
    padding:.55rem .85rem;
    border-radius: 12px;
    border: 1px solid rgba(56,189,248,.45);
    color: var(--accent);
    font-weight: 900;
    text-decoration:none;
    background: rgba(56,189,248,.06);
    white-space: nowrap;
}
.btn-view:hover{ background: rgba(56,189,248,.10); color: var(--accent); }

.mobile-list{ display:none; }
.booking-card{
    background: rgba(2,6,23,.35);
    border: 1px solid rgba(255,255,255,.10);
    border-radius: 16px;
    padding: 1rem;
}
.booking-top{
    display:flex;
    justify-content:space-between;
    gap: .75rem;
    align-items:flex-start;
}
.b-ref{
    font-weight: 900;
    color: rgba(255,255,255,.95);
    line-height: 1.15;
}
.b-sub{
    margin-top:.2rem;
    color: rgba(255,255,255,.55);
    font-size: .85rem;
}
.b-amt{
    font-weight: 900;
    color: rgba(255,255,255,.95);
    white-space: nowrap;
}
.b-grid{
    margin-top:.85rem;
    display:grid;
    grid-template-columns: 1fr 1fr;
    gap: .6rem;
}
.b-item{
    border: 1px solid rgba(255,255,255,.08);
    background: rgba(2,6,23,.25);
    border-radius: 12px;
    padding: .65rem .75rem;
}
.b-item .k{
    font-size:.72rem;
    letter-spacing:.10em;
    text-transform: uppercase;
    color: rgba(255,255,255,.55);
}
.b-item .v{
    margin-top:.2rem;
    font-weight: 900;
    color: rgba(255,255,255,.92);
    font-size: .95rem;
}
.b-bottom{
    margin-top:.85rem;
    display:flex;
    justify-content:space-between;
    gap: .5rem;
    flex-wrap: wrap;
}
.b-actions a{ width: 100%; justify-content:center; }
.b-item-full{ grid-column: 1 / -1; }
.cancel-note{
    margin-top:.35rem;
    color:#fca5a5;
    font-size:.83rem;
    line-height:1.45;
    font-weight:700;
}

@media (max-width: 767.98px){
    .desktop-table{ display:none; }
    .mobile-list{ display:block; }
    .b-grid{ grid-template-columns: 1fr; }
}

.pagination{ gap: .35rem; }
.page-link{
    background: rgba(2,6,23,.35) !important;
    border: 1px solid rgba(255,255,255,.10) !important;
    color: rgba(255,255,255,.85) !important;
    border-radius: 12px !important;
    font-weight: 800;
}
.page-link:hover{
    background: rgba(56,189,248,.08) !important;
    color: var(--accent) !important;
}
.page-item.active .page-link{
    background: rgba(56,189,248,.18) !important;
    border-color: rgba(56,189,248,.45) !important;
    color: #eaf6ff !important;
}
.page-item.disabled .page-link{ opacity: .45; }
</style>

<div class="mb-3">
    <div class="d-flex flex-wrap justify-content-between align-items-end gap-2">
        <div>
            <h3 class="page-title mb-1">Bookings History</h3>
            <div class="page-sub">Filter and review your past bookings</div>
        </div>
        <a href="{{ route('customer.dashboard') }}" class="btn btn-outline-accent">Back to Dashboard</a>
    </div>
</div>

<div class="card-dark card-pad mb-3">
    <form method="GET" action="{{ route('customer.bookings.history') }}">
        <div class="row g-2 align-items-end">

            <div class="col-12 col-md-4">
                <label class="small-muted mb-1">Search (reference/phone/address)</label>
                <input
                    type="text"
                    name="q"
                    value="{{ $q }}"
                    class="form-control input-dark"
                    placeholder="e.g. CT-1021, 0912..., Libertad"
                >
            </div>

            <div class="col-12 col-md-2">
                <label class="small-muted mb-1">Status</label>
                <select name="status" class="form-select select-dark">
                    <option value="">All</option>
                    <option value="confirmed"   @selected($status==='confirmed')>Confirmed</option>
                    <option value="in_progress" @selected($status==='in_progress')>In Progress</option>
                    <option value="paid"        @selected($status==='paid')>Paid</option>
                    <option value="completed"   @selected($status==='completed')>Completed</option>
                    <option value="cancelled"   @selected($status==='cancelled')>Cancelled</option>
                </select>
            </div>

            <div class="col-6 col-md-2">
                <label class="small-muted mb-1">From</label>
                <input type="date" name="from" value="{{ $from }}" class="form-control input-dark">
            </div>

            <div class="col-6 col-md-2">
                <label class="small-muted mb-1">To</label>
                <input type="date" name="to" value="{{ $to }}" class="form-control input-dark">
            </div>

            <div class="col-6 col-md-1">
                <label class="small-muted mb-1">Min ₱</label>
                <input type="number" step="0.01" name="min" value="{{ $min }}" class="form-control input-dark" placeholder="0">
            </div>

            <div class="col-6 col-md-1">
                <label class="small-muted mb-1">Max ₱</label>
                <input type="number" step="0.01" name="max" value="{{ $max }}" class="form-control input-dark" placeholder="9999">
            </div>

            <div class="col-12 d-flex gap-2 mt-2">
                <button class="btn btn-outline-accent" type="submit">Apply Filters</button>
                <a class="btn btn-outline-accent" href="{{ route('customer.bookings.history') }}">Reset</a>
            </div>

        </div>
    </form>
</div>

<div class="card-dark card-pad">
    @php
        $hasShowRoute = \Illuminate\Support\Facades\Route::has('customer.bookings.show');
    @endphp

    <div class="mobile-list">
        <div class="d-grid gap-2">
            @forelse($bookings as $b)
                @php
                    $refCode = $b->reference_code ?? $b->id;

                    $dateLabel = $b->booking_date
                        ? \Carbon\Carbon::parse($b->booking_date)->format('F d, Y')
                        : '—';

                    $timeLabel = ($b->time_start && $b->time_end)
                        ? \Carbon\Carbon::parse($b->time_start)->format('h:i A') . ' – ' .
                          \Carbon\Carbon::parse($b->time_end)->format('h:i A')
                        : '—';

                    $amt = (float)($b->price ?? 0);

                    $st = strtolower((string)($b->status ?? ''));
                    $pay = in_array($st, ['paid','completed']) ? 'paid' : 'unpaid';

                    $stBadge = match(true) {
                        $st === 'completed'   => 'success',
                        $st === 'paid'        => 'success',
                        $st === 'cancelled'   => 'danger',
                        $st === 'in_progress' => 'warn',
                        $st === 'confirmed'   => 'muted',
                        default               => 'muted',
                    };

                    $payBadge = $pay === 'paid' ? 'success' : 'warn';

                    $detailsUrl = $hasShowRoute ? route('customer.bookings.show', $refCode) : null;
                    $cancelReason = trim((string) ($b->cancellation_reason ?? ''));
                    $cancelledByRole = trim((string) ($b->cancelled_by_role ?? ''));
                    $cancelledByLabel = $cancelledByRole !== '' ? ucfirst(str_replace('_', ' ', $cancelledByRole)) : 'System';
                @endphp

                <div class="booking-card">
                    <div class="booking-top">
                        <div>
                            <div class="b-ref">{{ $refCode }}</div>
                            <div class="b-sub">{{ $b->address ?? '—' }}</div>
                        </div>
                        <div class="b-amt">₱{{ number_format($amt, 2) }}</div>
                    </div>

                    <div class="b-grid">
                        <div class="b-item">
                            <div class="k">Date</div>
                            <div class="v">{{ $dateLabel }}</div>
                        </div>
                        <div class="b-item">
                            <div class="k">Time</div>
                            <div class="v">{{ $timeLabel }}</div>
                        </div>
                        <div class="b-item">
                            <div class="k">Status</div>
                            <div class="v">
                                <span class="badge-soft {{ $stBadge }}">{{ strtoupper(str_replace('_',' ',$st ?: 'N/A')) }}</span>
                            </div>
                        </div>
                        <div class="b-item">
                            <div class="k">Payment</div>
                            <div class="v">
                                <span class="badge-soft {{ $payBadge }}">{{ strtoupper($pay) }}</span>
                            </div>
                        </div>
                        @if($st === 'cancelled' && $cancelReason !== '')
                            <div class="b-item b-item-full">
                                <div class="k">Cancellation</div>
                                <div class="v">{{ $cancelReason }}</div>
                                <div class="cancel-note">Cancelled by {{ $cancelledByLabel }}</div>
                            </div>
                        @endif
                    </div>

                    <div class="b-bottom">
                        <div class="b-actions" style="width:100%;">
                            @if($detailsUrl)
                                <a class="btn-view" href="{{ $detailsUrl }}">View Details</a>
                            @else
                                <span class="small-muted">No details route.</span>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center" style="color:var(--text-muted); padding:2rem;">
                    No bookings found for the selected filters.
                </div>
            @endforelse
        </div>
    </div>

    <div class="desktop-table">
        <div class="table-responsive table-wrap">
            <table class="table table-borderless table-darkish align-middle">
                <thead>
                    <tr>
                        <th style="min-width:180px;">Reference</th>
                        <th style="min-width:170px;">Date</th>
                        <th style="min-width:170px;">Time</th>
                        <th style="min-width:120px;">Amount</th>
                        <th style="min-width:140px;">Status</th>
                        <th style="min-width:140px;">Payment</th>
                        <th style="min-width:160px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($bookings as $b)
                        @php
                            $refCode = $b->reference_code ?? $b->id;

                            $dateLabel = $b->booking_date
                                ? \Carbon\Carbon::parse($b->booking_date)->format('F d, Y')
                                : '—';

                            $timeLabel = ($b->time_start && $b->time_end)
                                ? \Carbon\Carbon::parse($b->time_start)->format('h:i A') . ' – ' .
                                  \Carbon\Carbon::parse($b->time_end)->format('h:i A')
                                : '—';

                            $amt = (float)($b->price ?? 0);

                            $st = strtolower((string)($b->status ?? ''));
                            $pay = in_array($st, ['paid','completed']) ? 'paid' : 'unpaid';

                            $stBadge = match(true) {
                                $st === 'completed'   => 'success',
                                $st === 'paid'        => 'success',
                                $st === 'cancelled'   => 'danger',
                                $st === 'in_progress' => 'warn',
                                $st === 'confirmed'   => 'muted',
                                default               => 'muted',
                            };

                            $payBadge = $pay === 'paid' ? 'success' : 'warn';

                            $detailsUrl = $hasShowRoute ? route('customer.bookings.show', $refCode) : null;
                            $cancelReason = trim((string) ($b->cancellation_reason ?? ''));
                            $cancelledByRole = trim((string) ($b->cancelled_by_role ?? ''));
                            $cancelledByLabel = $cancelledByRole !== '' ? ucfirst(str_replace('_', ' ', $cancelledByRole)) : 'System';
                        @endphp

                        <tr>
                            <td>
                                <div style="font-weight:900;color:rgba(255,255,255,.92);">{{ $refCode }}</div>
                                <div class="small-muted">{{ $b->address ?? '' }}</div>
                                @if($st === 'cancelled' && $cancelReason !== '')
                                    <div class="cancel-note">Cancelled by {{ $cancelledByLabel }}: {{ $cancelReason }}</div>
                                @endif
                            </td>
                            <td>{{ $dateLabel }}</td>
                            <td>{{ $timeLabel }}</td>
                            <td style="font-weight:900;">₱{{ number_format($amt, 2) }}</td>
                            <td>
                                <span class="badge-soft {{ $stBadge }}">{{ strtoupper(str_replace('_',' ',$st ?: 'N/A')) }}</span>
                            </td>
                            <td>
                                <span class="badge-soft {{ $payBadge }}">{{ strtoupper($pay) }}</span>
                            </td>
                            <td>
                                @if($detailsUrl)
                                    <a class="btn-view" href="{{ $detailsUrl }}">View Details</a>
                                @else
                                    <span class="small-muted">No details route.</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center" style="color:var(--text-muted); padding:2rem;">
                                No bookings found for the selected filters.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if(method_exists($bookings, 'links'))
        <div class="mt-3">
            {{ $bookings->appends(request()->query())->links() }}
        </div>
    @endif
</div>

@endsection
