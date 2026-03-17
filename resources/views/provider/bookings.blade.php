@extends('provider.layouts.app')

@section('title', 'My Bookings')

@section('content')

<style>
:root{
    --bg-card:#020b1f;
    --bg-deep:#020617;
    --border-soft:rgba(255,255,255,.08);
    --border-row:rgba(255,255,255,.06);
    --text-muted:rgba(255,255,255,.58);
    --text-strong:rgba(255,255,255,.94);
    --accent:#38bdf8;
    --success:#22c55e;
    --warn:#facc15;
    --danger:#ef4444;
}

/* page heading */
.booking-heading h2{
    margin:0;
    font-size:2rem;
    font-weight:800;
    color:#fff;
    letter-spacing:-.02em;
}

.booking-heading p{
    margin:.35rem 0 0;
    color:var(--text-muted);
    font-size:1rem;
}

/* main card */
.booking-card{
    background:linear-gradient(180deg,var(--bg-card),var(--bg-deep));
    border:1px solid var(--border-soft);
    border-radius:20px;
    padding:1rem;
    box-shadow:0 10px 30px rgba(0,0,0,.18);
}

/* alerts */
.alert{
    border-radius:12px;
    padding:.8rem 1rem;
    margin-bottom:1rem;
    font-size:.92rem;
}
.alert-success{
    background:rgba(34,197,94,.10);
    border:1px solid rgba(34,197,94,.25);
    color:#bbf7d0;
}
.alert-danger{
    background:rgba(239,68,68,.10);
    border:1px solid rgba(239,68,68,.25);
    color:#fecaca;
}

/* table shell */
.booking-table{
    border:1px solid var(--border-soft);
    border-radius:16px;
    overflow:hidden;
    background:rgba(255,255,255,.01);
}

.service-full{
    white-space:normal;
    overflow:visible;
    display:block;
    word-break:break-word;
    overflow-wrap:anywhere;
    line-height:1.45;
}
.booking-table table{
    width:100%;
    margin:0;
    border-collapse:collapse;
    table-layout:auto;
}

.booking-table thead{
    background:rgba(56,189,248,.07);
}

.booking-table thead th{
    padding:.82rem .9rem;
    border-bottom:1px solid var(--border-soft);
    text-align:left;
    text-transform:uppercase;
    letter-spacing:.08em;
    font-size:.7rem;
    color:rgba(255,255,255,.72);
    font-weight:800;
}

.booking-table td{
    padding:.9rem;
    border-bottom:1px solid var(--border-row);
    vertical-align:top;
    color:#fff;
    font-size:.9rem;
}

.booking-table tbody tr:last-child td{
    border-bottom:none;
}

.booking-table tbody tr:hover{
    background:rgba(56,189,248,.03);
}

/* compact columns */
.customer-col{ width:39%; }
.schedule-col{ width:17%; }
.price-col{ width:10%; }
.status-col{ width:12%; }
.action-col{ width:22%; }

/* customer block */
.customer-name{
    color:#fff;
    font-weight:800;
    font-size:1rem;
    line-height:1.2;
    margin-bottom:.2rem;
}

.customer-ref{
    color:var(--text-muted);
    font-size:.78rem;
    margin-bottom:.55rem;
}

.booking-meta{
    display:grid;
    gap:.28rem;
}

.booking-meta-item{
    color:var(--text-muted);
    line-height:1.35;
    font-size:.88rem;
}

.booking-meta-item strong{
    color:rgba(255,255,255,.84);
    font-weight:700;
}

.wrap-break{
    word-break:break-word;
    overflow-wrap:anywhere;
}

.line-clamp-1,
.line-clamp-2{
    display:-webkit-box;
    -webkit-box-orient:vertical;
    overflow:hidden;
}
.line-clamp-1{ -webkit-line-clamp:1; }
.line-clamp-2{ -webkit-line-clamp:2; }

/* schedule */
.schedule-block{
    display:grid;
    gap:.5rem;
}

.schedule-item{
    line-height:1.3;
}

.schedule-label{
    display:block;
    color:var(--text-muted);
    font-size:.72rem;
    text-transform:uppercase;
    letter-spacing:.05em;
    margin-bottom:.12rem;
}

.schedule-value{
    color:#fff;
    font-weight:700;
    font-size:.9rem;
}

/* price */
.price-text{
    font-weight:800;
    white-space:nowrap;
}

/* status */
.status-badge{
    display:inline-flex;
    align-items:center;
    justify-content:center;
    padding:.4rem .78rem;
    border-radius:999px;
    font-size:.68rem;
    text-transform:uppercase;
    font-weight:800;
    letter-spacing:.04em;
    white-space:nowrap;
}
.status-badge.confirmed{ background:rgba(56,189,248,.14); color:var(--accent); }
.status-badge.in_progress{ background:rgba(250,204,21,.14); color:var(--warn); }
.status-badge.paid{ background:rgba(34,197,94,.14); color:var(--success); }
.status-badge.completed{ background:rgba(34,197,94,.10); color:#86efac; }
.status-badge.cancelled{ background:rgba(239,68,68,.14); color:var(--danger); }

/* action form */
.update-form{
    display:flex;
    flex-direction:column;
    gap:.45rem;
    max-width:180px;
}

.update-form select{
    width:100%;
    background:#020617 !important;
    color:#fff !important;
    border:1px solid var(--border-soft) !important;
    border-radius:10px;
    padding:.55rem .75rem;
    font-size:.88rem;
    outline:none;
}

.update-form select option{
    background:#020617;
    color:#fff;
}

.btn-update{
    width:100%;
    border:none;
    border-radius:10px;
    padding:.58rem .9rem;
    font-weight:800;
    color:#fff;
    background:linear-gradient(180deg,#0ea5e9,#38bdf8);
    cursor:pointer;
    transition:.2s ease;
}

.btn-update:hover{
    transform:translateY(-1px);
    box-shadow:0 10px 18px rgba(56,189,248,.15);
}

.no-action{
    color:var(--text-muted);
    font-size:.85rem;
}

/* mobile cards */
.mobile-bookings{
    display:none;
    flex-direction:column;
    gap:.85rem;
}

.booking-item{
    background:rgba(255,255,255,.02);
    border:1px solid var(--border-soft);
    border-radius:16px;
    padding:1rem;
}

.booking-item-head{
    display:flex;
    justify-content:space-between;
    align-items:flex-start;
    gap:.75rem;
    margin-bottom:.7rem;
}

.booking-item-name{
    color:#fff;
    font-weight:800;
    font-size:1rem;
    margin-bottom:.15rem;
}

.booking-item-ref{
    color:var(--text-muted);
    font-size:.8rem;
}

.booking-item-body{
    display:grid;
    gap:.6rem;
}

.booking-mobile-row{
    display:grid;
    gap:.18rem;
}

.booking-mobile-label{
    color:var(--text-muted);
    font-size:.72rem;
    text-transform:uppercase;
    letter-spacing:.05em;
}

.booking-mobile-value{
    color:#fff;
    font-weight:700;
    line-height:1.35;
    word-break:break-word;
}

.booking-item-actions{
    margin-top:.9rem;
}

.booking-item-actions .update-form{
    max-width:none;
}

.empty-state{
    text-align:center;
    color:var(--text-muted);
    padding:2rem 1rem;
}

@media (max-width: 1180px){
    .desktop-bookings{
        display:none;
    }

    .mobile-bookings{
        display:flex;
    }
}
</style>

<div class="booking-heading mb-4">
    <h2>My Bookings</h2>
    <p>Customers who booked your services</p>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

@if($errors->any())
    <div class="alert alert-danger">{{ $errors->first() }}</div>
@endif

<div class="booking-card">
    @if ($bookings->isEmpty())
        <div class="empty-state">No bookings yet.</div>
    @else
        @php
            $nextOptions = [
                'confirmed'   => ['in_progress' => 'In Progress', 'cancelled' => 'Cancelled'],
                'in_progress' => ['paid' => 'Paid', 'cancelled' => 'Cancelled'],
                'paid'        => ['completed' => 'Completed'],
                'completed'   => [],
                'cancelled'   => [],
            ];
        @endphp

        <div class="desktop-bookings">
            <div class="booking-table">
                <table>
                    <thead>
                        <tr>
                            <th class="customer-col">Customer Details</th>
                            <th class="schedule-col">Schedule</th>
                            <th class="price-col">Price</th>
                            <th class="status-col">Status</th>
                            <th class="action-col">Update</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($bookings as $b)
                            @php
                                $current = strtolower((string) $b->status);

                                $dateLabel = $b->booking_date
                                    ? \Carbon\Carbon::parse($b->booking_date)->format('M d, Y')
                                    : '—';

                                $preferredLabel = $b->requested_start_time
                                    ? \Carbon\Carbon::parse($b->requested_start_time)->format('h:i A')
                                    : '—';

                                $availabilityLabel = ($b->time_start && $b->time_end)
                                    ? \Carbon\Carbon::parse($b->time_start)->format('h:i A') . ' - ' .
                                      \Carbon\Carbon::parse($b->time_end)->format('h:i A')
                                    : '—';
                            @endphp

                            <tr>
                                <td class="customer-col">
                                    <div class="customer-name">{{ $b->name }}</div>
                                    <div class="customer-ref">Ref: {{ $b->reference_code }}</div>

                                    <div class="booking-meta">
                                        <div class="booking-meta-item wrap-break">
                                            <strong>Phone:</strong> {{ $b->contact_phone ?? $b->phone ?? '—' }}
                                        </div>
                                        <div class="booking-meta-item wrap-break line-clamp-1">
                                            <strong>Email:</strong> {{ $b->email ?? '—' }}
                                        </div>
                                        <div class="booking-meta-item service-full wrap-break">
                                            <strong>Service:</strong> {{ $b->service }} — {{ $b->option }}
                                        </div>
                                        <div class="booking-meta-item line-clamp-2">
                                            <strong>Address:</strong> {{ $b->address ?? '—' }}
                                        </div>
                                    </div>
                                </td>

                                <td class="schedule-col">
                                    <div class="schedule-block">
                                        <div class="schedule-item">
                                            <span class="schedule-label">Date</span>
                                            <div class="schedule-value">{{ $dateLabel }}</div>
                                        </div>

                                        <div class="schedule-item">
                                            <span class="schedule-label">Preferred</span>
                                            <div class="schedule-value">{{ $preferredLabel }}</div>
                                        </div>

                                        <div class="schedule-item">
                                            <span class="schedule-label">Availability</span>
                                            <div class="schedule-value">{{ $availabilityLabel }}</div>
                                        </div>
                                    </div>
                                </td>

                                <td class="price-col">
                                    <div class="price-text">₱{{ number_format($b->price ?? 0, 2) }}</div>
                                </td>

                                <td class="status-col">
                                    <span class="status-badge {{ $current }}">
                                        {{ str_replace('_', ' ', strtoupper($current)) }}
                                    </span>
                                </td>

                                <td class="action-col">
                                    @if(empty($nextOptions[$current]))
                                        <div class="no-action">No further actions.</div>
                                    @else
                                        <form method="POST"
                                              action="{{ route('provider.bookings.status', $b->reference_code) }}"
                                              class="update-form">
                                            @csrf

                                            <select name="status" required>
                                                <option value="">Select status</option>
                                                @foreach($nextOptions[$current] as $val => $label)
                                                    <option value="{{ $val }}">{{ $label }}</option>
                                                @endforeach
                                            </select>

                                            <button class="btn-update" type="submit">Update</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mobile-bookings">
            @foreach ($bookings as $b)
                @php
                    $current = strtolower((string) $b->status);

                    $dateLabel = $b->booking_date
                        ? \Carbon\Carbon::parse($b->booking_date)->format('M d, Y')
                        : '—';

                    $preferredLabel = $b->requested_start_time
                        ? \Carbon\Carbon::parse($b->requested_start_time)->format('h:i A')
                        : '—';

                    $availabilityLabel = ($b->time_start && $b->time_end)
                        ? \Carbon\Carbon::parse($b->time_start)->format('h:i A') . ' - ' .
                          \Carbon\Carbon::parse($b->time_end)->format('h:i A')
                        : '—';
                @endphp

                <div class="booking-item">
                    <div class="booking-item-head">
                        <div>
                            <div class="booking-item-name">{{ $b->name }}</div>
                            <div class="booking-item-ref">Ref: {{ $b->reference_code }}</div>
                        </div>

                        <span class="status-badge {{ $current }}">
                            {{ str_replace('_', ' ', strtoupper($current)) }}
                        </span>
                    </div>

                    <div class="booking-item-body">
                        <div class="booking-mobile-row">
                            <div class="booking-mobile-label">Phone</div>
                            <div class="booking-mobile-value">{{ $b->contact_phone ?? $b->phone ?? '—' }}</div>
                        </div>

                        <div class="booking-mobile-row">
                            <div class="booking-mobile-label">Email</div>
                            <div class="booking-mobile-value">{{ $b->email ?? '—' }}</div>
                        </div>

                        <div class="booking-mobile-row">
                            <div class="booking-mobile-label">Service</div>
                            <div class="booking-mobile-value">{{ $b->service }} — {{ $b->option }}</div>
                        </div>

                        <div class="booking-mobile-row">
                            <div class="booking-mobile-label">Address</div>
                            <div class="booking-mobile-value">{{ $b->address ?? '—' }}</div>
                        </div>

                        <div class="booking-mobile-row">
                            <div class="booking-mobile-label">Date</div>
                            <div class="booking-mobile-value">{{ $dateLabel }}</div>
                        </div>

                        <div class="booking-mobile-row">
                            <div class="booking-mobile-label">Preferred Start</div>
                            <div class="booking-mobile-value">{{ $preferredLabel }}</div>
                        </div>

                        <div class="booking-mobile-row">
                            <div class="booking-mobile-label">Availability</div>
                            <div class="booking-mobile-value">{{ $availabilityLabel }}</div>
                        </div>

                        <div class="booking-mobile-row">
                            <div class="booking-mobile-label">Price</div>
                            <div class="booking-mobile-value">₱{{ number_format($b->price ?? 0, 2) }}</div>
                        </div>
                    </div>

                    <div class="booking-item-actions">
                        @if(empty($nextOptions[$current]))
                            <div class="no-action">No further actions.</div>
                        @else
                            <form method="POST"
                                  action="{{ route('provider.bookings.status', $b->reference_code) }}"
                                  class="update-form">
                                @csrf

                                <select name="status" required>
                                    <option value="">Select status</option>
                                    @foreach($nextOptions[$current] as $val => $label)
                                        <option value="{{ $val }}">{{ $label }}</option>
                                    @endforeach
                                </select>

                                <button class="btn-update" type="submit">Update</button>
                            </form>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>

@endsection
