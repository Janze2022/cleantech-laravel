@extends('provider.layouts.app')

@section('title', 'My Bookings')

@section('content')

@php
    $nextOptions = [
        'pending' => ['confirmed' => 'Confirmed', 'cancelled' => 'Cancelled'],
        'confirmed' => ['in_progress' => 'In Progress', 'cancelled' => 'Cancelled'],
        'in_progress' => ['paid' => 'Paid', 'cancelled' => 'Cancelled'],
        'paid' => ['completed' => 'Completed'],
        'completed' => [],
        'cancelled' => [],
    ];
@endphp

<style>
:root{
    --page-bg:#020617;
    --card-bg:#071224;
    --card-bg-2:#0b1730;
    --border-soft:rgba(255,255,255,.08);
    --text-muted:rgba(255,255,255,.62);
    --accent:#38bdf8;
    --success:#22c55e;
    --warn:#facc15;
    --danger:#ef4444;
}

.page-shell{ display:grid; gap:1rem; }

.page-head h2{
    margin:0;
    font-size:2rem;
    font-weight:900;
    color:#fff;
}

.page-head p{
    margin:.35rem 0 0;
    color:var(--text-muted);
}

.flash{
    border-radius:14px;
    padding:.9rem 1rem;
    border:1px solid transparent;
    font-weight:700;
}

.flash.success{
    background:rgba(34,197,94,.10);
    border-color:rgba(34,197,94,.25);
    color:#d1fae5;
}

.flash.error{
    background:rgba(239,68,68,.10);
    border-color:rgba(239,68,68,.25);
    color:#fecaca;
}

.panel{
    background:linear-gradient(180deg,var(--card-bg),var(--page-bg));
    border:1px solid var(--border-soft);
    border-radius:20px;
    padding:1rem;
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
    font-size:.92rem;
}

.desktop-table tbody tr:last-child td{
    border-bottom:none;
}

.desktop-table tbody tr:hover{
    background:rgba(255,255,255,.02);
}

.customer-name{
    font-size:1rem;
    font-weight:900;
    color:#fff;
}

.ref-text,
.meta-text{
    color:var(--text-muted);
    font-size:.84rem;
    line-height:1.4;
}

.service-text{
    color:#fff;
    font-weight:800;
}

.schedule-stack{
    display:grid;
    gap:.35rem;
}

.schedule-label{
    display:block;
    color:var(--text-muted);
    font-size:.72rem;
    text-transform:uppercase;
    letter-spacing:.05em;
}

.schedule-value{
    color:#fff;
    font-weight:800;
}

.price-text{
    font-weight:900;
    white-space:nowrap;
}

.status-badge{
    display:inline-flex;
    align-items:center;
    justify-content:center;
    border-radius:999px;
    padding:.4rem .8rem;
    font-size:.7rem;
    font-weight:900;
    letter-spacing:.05em;
    text-transform:uppercase;
}

.status-badge.pending{ background:rgba(148,163,184,.16); color:#cbd5e1; }
.status-badge.confirmed{ background:rgba(56,189,248,.14); color:var(--accent); }
.status-badge.in_progress{ background:rgba(250,204,21,.14); color:var(--warn); }
.status-badge.paid{ background:rgba(34,197,94,.14); color:var(--success); }
.status-badge.completed{ background:rgba(34,197,94,.10); color:#86efac; }
.status-badge.cancelled{ background:rgba(239,68,68,.14); color:var(--danger); }

.update-form{
    display:grid;
    gap:.5rem;
    max-width:190px;
}

.action-stack{
    display:grid;
    gap:.55rem;
    max-width:190px;
}

.update-form select{
    width:100%;
    background:#020617;
    color:#fff;
    border:1px solid var(--border-soft);
    border-radius:10px;
    padding:.65rem .75rem;
}

.reason-input{
    width:100%;
    min-height:76px;
    resize:vertical;
    background:#020617;
    color:#fff;
    border:1px solid var(--border-soft);
    border-radius:12px;
    padding:.75rem .85rem;
    line-height:1.45;
}

.reason-input::placeholder{
    color:rgba(255,255,255,.38);
}

.field-error{
    color:#fca5a5;
    font-size:.8rem;
    font-weight:800;
    line-height:1.4;
}

.btn-update{
    border:none;
    border-radius:10px;
    padding:.65rem .9rem;
    background:linear-gradient(180deg,#0ea5e9,#38bdf8);
    color:#fff;
    font-weight:900;
}

.btn-view{
    display:inline-flex;
    align-items:center;
    justify-content:center;
    text-decoration:none;
    border-radius:10px;
    padding:.65rem .9rem;
    border:1px solid rgba(56,189,248,.24);
    background:rgba(56,189,248,.10);
    color:#fff;
    font-weight:900;
}

.no-action{
    color:var(--text-muted);
    font-size:.86rem;
}

.mobile-list{
    display:none;
    gap:.85rem;
}

.booking-card{
    border:1px solid var(--border-soft);
    border-radius:16px;
    background:rgba(255,255,255,.02);
    padding:1rem;
}

.booking-card-head{
    display:flex;
    justify-content:space-between;
    gap:.8rem;
    align-items:flex-start;
    margin-bottom:.85rem;
}

.booking-grid{
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:.7rem .9rem;
}

.card-label{
    color:var(--text-muted);
    font-size:.72rem;
    text-transform:uppercase;
    letter-spacing:.05em;
    margin-bottom:.15rem;
}

.card-value{
    color:#fff;
    font-weight:800;
    line-height:1.35;
    word-break:break-word;
}

.card-actions{
    margin-top:1rem;
}

.card-actions .update-form{
    max-width:none;
}

@media (max-width: 1100px){
    .desktop-table{ display:none; }
    .mobile-list{ display:grid; }
}

@media (max-width: 640px){
    .page-head h2{ font-size:1.5rem; }
    .booking-grid{ grid-template-columns:1fr; }
}
</style>

<div class="page-shell">
    <div class="page-head">
        <h2>My Bookings</h2>
        <p>Customers who booked your services.</p>
    </div>

    @if(session('success'))
        <div class="flash success">{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="flash error">{{ $errors->first() }}</div>
    @endif

    @if(!empty($loadError))
        <div class="flash error">{{ $loadError }}</div>
    @endif

    <div class="panel">
        @if ($bookings->isEmpty())
            <div class="empty-state">No active bookings yet.</div>
        @else
            <div class="desktop-table">
                <table>
                    <thead>
                        <tr>
                            <th>Customer</th>
                            <th>Schedule</th>
                            <th>Price</th>
                            <th>Status</th>
                            <th>Update</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($bookings as $b)
                            @php($current = $b->status_key ?? strtolower((string) $b->status))
                            <tr>
                                <td>
                                    <div class="customer-name">{{ $b->name }}</div>
                                    <div class="ref-text">Ref: {{ $b->reference_code }}</div>
                                    <div class="meta-text">Phone: {{ $b->display_phone }}</div>
                                    <div class="meta-text">Email: {{ $b->display_email }}</div>
                                    <div class="meta-text">
                                        <span class="service-text">{{ $b->service }}</span> - {{ $b->display_option }}
                                    </div>
                                    <div class="meta-text">{{ $b->address ?? '-' }}</div>
                                </td>
                                <td>
                                    <div class="schedule-stack">
                                        <div>
                                            <span class="schedule-label">Date</span>
                                            <div class="schedule-value">{{ $b->display_booking_date }}</div>
                                        </div>
                                        <div>
                                            <span class="schedule-label">Preferred</span>
                                            <div class="schedule-value">{{ $b->display_requested_start_time }}</div>
                                        </div>
                                        <div>
                                            <span class="schedule-label">Availability</span>
                                            <div class="schedule-value">{{ $b->display_availability }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="price-text">PHP {{ $b->display_price }}</div>
                                </td>
                                <td>
                                    <span class="status-badge {{ $current }}">
                                        {{ str_replace('_', ' ', strtoupper($current)) }}
                                    </span>
                                </td>
                                <td>
                                    <div class="action-stack">
                                        @if(empty($nextOptions[$current]))
                                            <div class="no-action">No further actions.</div>
                                        @else
                                            <form method="POST" action="{{ route('provider.bookings.status', $b->reference_code) }}" class="update-form">
                                                @csrf
                                                <select name="status" required>
                                                    <option value="">Select status</option>
                                                    @foreach($nextOptions[$current] as $value => $label)
                                                        <option value="{{ $value }}">{{ $label }}</option>
                                                    @endforeach
                                                </select>
                                                <textarea name="cancellation_reason" class="reason-input" rows="3" placeholder="Reason if cancelling">{{ old('cancellation_reason') }}</textarea>
                                                @if($errors->has('cancellation_reason'))
                                                    <div class="field-error">{{ $errors->first('cancellation_reason') }}</div>
                                                @endif
                                                <button class="btn-update" type="submit">Update</button>
                                            </form>
                                        @endif

                                        @if(\Illuminate\Support\Facades\Route::has('provider.bookings.show'))
                                            <a class="btn-view" href="{{ route('provider.bookings.show', $b->reference_code) }}">Open Details</a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mobile-list">
                @foreach ($bookings as $b)
                    @php($current = $b->status_key ?? strtolower((string) $b->status))
                    <div class="booking-card">
                        <div class="booking-card-head">
                            <div>
                                <div class="customer-name">{{ $b->name }}</div>
                                <div class="ref-text">Ref: {{ $b->reference_code }}</div>
                            </div>
                            <span class="status-badge {{ $current }}">
                                {{ str_replace('_', ' ', strtoupper($current)) }}
                            </span>
                        </div>

                        <div class="booking-grid">
                            <div>
                                <div class="card-label">Phone</div>
                                <div class="card-value">{{ $b->display_phone }}</div>
                            </div>
                            <div>
                                <div class="card-label">Email</div>
                                <div class="card-value">{{ $b->display_email }}</div>
                            </div>
                            <div>
                                <div class="card-label">Service</div>
                                <div class="card-value">{{ $b->service }} - {{ $b->display_option }}</div>
                            </div>
                            <div>
                                <div class="card-label">Address</div>
                                <div class="card-value">{{ $b->address ?? '-' }}</div>
                            </div>
                            <div>
                                <div class="card-label">Date</div>
                                <div class="card-value">{{ $b->display_booking_date }}</div>
                            </div>
                            <div>
                                <div class="card-label">Preferred Start</div>
                                <div class="card-value">{{ $b->display_requested_start_time }}</div>
                            </div>
                            <div>
                                <div class="card-label">Availability</div>
                                <div class="card-value">{{ $b->display_availability }}</div>
                            </div>
                            <div>
                                <div class="card-label">Price</div>
                                <div class="card-value">PHP {{ $b->display_price }}</div>
                            </div>
                        </div>

                        <div class="card-actions">
                            <div class="action-stack" style="max-width:none;">
                                @if(empty($nextOptions[$current]))
                                    <div class="no-action">No further actions.</div>
                                @else
                                    <form method="POST" action="{{ route('provider.bookings.status', $b->reference_code) }}" class="update-form">
                                        @csrf
                                        <select name="status" required>
                                            <option value="">Select status</option>
                                            @foreach($nextOptions[$current] as $value => $label)
                                                <option value="{{ $value }}">{{ $label }}</option>
                                            @endforeach
                                        </select>
                                        <textarea name="cancellation_reason" class="reason-input" rows="3" placeholder="Reason if cancelling">{{ old('cancellation_reason') }}</textarea>
                                        @if($errors->has('cancellation_reason'))
                                            <div class="field-error">{{ $errors->first('cancellation_reason') }}</div>
                                        @endif
                                        <button class="btn-update" type="submit">Update</button>
                                    </form>
                                @endif

                                @if(\Illuminate\Support\Facades\Route::has('provider.bookings.show'))
                                    <a class="btn-view" href="{{ route('provider.bookings.show', $b->reference_code) }}">Open Details</a>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>

@endsection
