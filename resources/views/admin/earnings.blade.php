@extends('admin.layouts.app')

@section('title', 'Provider Earnings')

@section('content')

@php
    $ledger = $ledger ?? collect();
    $summary = $summary ?? [
        'providers_count' => 0,
        'ledger_count' => 0,
        'gross_total' => 0,
        'remitted_total' => 0,
        'outstanding_total' => 0,
    ];
    $providerOptions = $providerOptions ?? collect();
@endphp

<style>
:root{
    --earn-bg:#050d1c;
    --earn-panel:#0a162b;
    --earn-panel-2:#0d1b34;
    --earn-border:rgba(255,255,255,.08);
    --earn-border-soft:rgba(255,255,255,.05);
    --earn-text:#eef5ff;
    --earn-muted:#93a9c4;
    --earn-accent:#38bdf8;
    --earn-success:#22c55e;
    --earn-warn:#f59e0b;
    --earn-danger:#ef4444;
    --earn-radius:18px;
    --earn-shadow:0 16px 34px rgba(0,0,0,.28);
}

.earnings-shell{
    max-width:1320px;
    margin:0 auto;
    padding:8px 2px 18px;
    color:var(--earn-text);
}

.earnings-stack{
    display:flex;
    flex-direction:column;
    gap:1rem;
}

.earn-panel,
.stats-row .stat-card,
.ledger-panel{
    background:linear-gradient(180deg, rgba(10,22,43,.98), rgba(4,10,22,.98));
    border:1px solid var(--earn-border);
    border-radius:var(--earn-radius);
    box-shadow:var(--earn-shadow);
}

.earn-panel,
.ledger-panel{
    padding:1rem;
}

.hero{
    display:flex;
    align-items:flex-start;
    justify-content:space-between;
    gap:1rem;
    flex-wrap:wrap;
}

.hero-copy{
    min-width:0;
}

.hero-title{
    margin:0;
    font-size:1.35rem;
    font-weight:900;
    letter-spacing:-.02em;
}

.hero-subtitle{
    margin:.3rem 0 0;
    color:var(--earn-muted);
    font-size:.86rem;
}

.hero-tags{
    display:flex;
    flex-wrap:wrap;
    gap:.55rem;
}

.hero-tag{
    display:inline-flex;
    align-items:center;
    gap:.42rem;
    min-height:36px;
    padding:.48rem .75rem;
    border-radius:999px;
    border:1px solid var(--earn-border);
    background:rgba(255,255,255,.03);
    color:rgba(255,255,255,.92);
    font-size:.78rem;
    font-weight:800;
    white-space:nowrap;
}

.hero-tag.accent{
    border-color:rgba(56,189,248,.25);
    background:rgba(56,189,248,.1);
}

.stats-row{
    display:grid;
    grid-template-columns:repeat(4, minmax(0, 1fr));
    gap:.85rem;
}

.stat-card{
    padding:.9rem 1rem;
}

.stat-label{
    color:var(--earn-muted);
    font-size:.72rem;
    font-weight:800;
    letter-spacing:.08em;
    text-transform:uppercase;
}

.stat-value{
    margin-top:.38rem;
    font-size:1.3rem;
    font-weight:900;
    line-height:1.08;
}

.stat-value.accent{ color:var(--earn-accent); }
.stat-value.success{ color:#86efac; }
.stat-value.warn{ color:#fdba74; }

.tool-grid{
    display:grid;
    grid-template-columns:minmax(0, 1.55fr) minmax(320px, .95fr);
    gap:1rem;
}

.tool-card{
    background:rgba(255,255,255,.02);
    border:1px solid var(--earn-border-soft);
    border-radius:16px;
    padding:1rem;
}

.tool-head{
    display:flex;
    align-items:flex-start;
    justify-content:space-between;
    gap:.75rem;
    flex-wrap:wrap;
    margin-bottom:.8rem;
}

.tool-title{
    margin:0;
    font-size:.96rem;
    font-weight:900;
}

.tool-note{
    color:var(--earn-muted);
    font-size:.79rem;
    font-weight:700;
}

.mini-pill{
    display:inline-flex;
    align-items:center;
    justify-content:center;
    min-height:30px;
    padding:.35rem .62rem;
    border-radius:999px;
    border:1px solid var(--earn-border);
    background:rgba(255,255,255,.03);
    color:rgba(255,255,255,.88);
    font-size:.72rem;
    font-weight:800;
    white-space:nowrap;
}

.filters-form{
    display:grid;
    grid-template-columns:minmax(0, 1.6fr) repeat(3, minmax(0, .86fr));
    gap:.75rem;
}

.print-form{
    display:grid;
    grid-template-columns:repeat(2, minmax(0, 1fr));
    gap:.75rem;
}

.field{
    display:flex;
    flex-direction:column;
    gap:.35rem;
}

.field.full{
    grid-column:1 / -1;
}

.field label{
    color:var(--earn-muted);
    font-size:.72rem;
    font-weight:800;
    letter-spacing:.08em;
    text-transform:uppercase;
}

.field input,
.field select{
    width:100%;
    min-height:42px;
    padding:.68rem .8rem;
    border-radius:12px;
    border:1px solid rgba(255,255,255,.09);
    background:#071120;
    color:var(--earn-text);
    outline:none;
    font-weight:700;
    font-size:.9rem;
}

.field input:focus,
.field select:focus{
    border-color:rgba(56,189,248,.35);
    box-shadow:0 0 0 3px rgba(56,189,248,.08);
}

.field input::-webkit-calendar-picker-indicator{
    filter:invert(1);
    opacity:.82;
}

.action-row{
    display:flex;
    flex-wrap:wrap;
    gap:.6rem;
    margin-top:.85rem;
}

.btn-admin,
.btn-admin-soft,
.btn-admin-danger{
    display:inline-flex;
    align-items:center;
    justify-content:center;
    gap:.45rem;
    min-height:40px;
    padding:.62rem .94rem;
    border-radius:12px;
    border:1px solid transparent;
    text-decoration:none;
    font-size:.82rem;
    font-weight:800;
}

.btn-admin{
    background:rgba(56,189,248,.13);
    border-color:rgba(56,189,248,.22);
    color:rgba(255,255,255,.95);
}

.btn-admin-soft{
    background:rgba(255,255,255,.03);
    border-color:rgba(255,255,255,.1);
    color:rgba(255,255,255,.92);
}

.btn-admin-danger{
    background:rgba(239,68,68,.12);
    border-color:rgba(239,68,68,.22);
    color:#fecaca;
}

.warning-box{
    margin-top:.85rem;
    padding:.8rem .9rem;
    border-radius:14px;
    border:1px solid rgba(245,158,11,.22);
    background:rgba(245,158,11,.09);
    color:#fde68a;
    font-size:.8rem;
    font-weight:700;
}

.ledger-head{
    display:flex;
    align-items:flex-start;
    justify-content:space-between;
    gap:.8rem;
    flex-wrap:wrap;
    margin-bottom:.85rem;
}

.ledger-kicker{
    display:flex;
    align-items:center;
    gap:.55rem;
    flex-wrap:wrap;
    margin-top:.25rem;
}

.ledger-count{
    color:var(--earn-muted);
    font-size:.8rem;
    font-weight:700;
}

.ledger-table-wrap{
    overflow:auto;
}

.ledger-table{
    width:100%;
    min-width:860px;
    border-collapse:collapse;
}

.ledger-table th{
    padding:.76rem .72rem;
    border-bottom:1px solid rgba(255,255,255,.08);
    color:var(--earn-muted);
    font-size:.72rem;
    font-weight:800;
    letter-spacing:.08em;
    text-transform:uppercase;
    white-space:nowrap;
}

.ledger-table td{
    padding:.92rem .72rem;
    border-bottom:1px solid rgba(255,255,255,.05);
    vertical-align:top;
}

.ledger-row:hover{
    background:rgba(255,255,255,.02);
}

.date-line{
    font-size:.9rem;
    font-weight:900;
}

.sub-line{
    margin-top:.22rem;
    color:var(--earn-muted);
    font-size:.76rem;
    font-weight:700;
    line-height:1.4;
}

.provider-name{
    font-size:.92rem;
    font-weight:900;
    line-height:1.3;
}

.provider-phone{
    margin-top:.18rem;
    color:var(--earn-muted);
    font-size:.77rem;
    font-weight:700;
}

.service-summary{
    margin-top:.24rem;
    color:rgba(255,255,255,.74);
    font-size:.76rem;
    line-height:1.45;
    max-width:260px;
}

.amount{
    font-size:.94rem;
    font-weight:900;
}

.status-pill{
    display:inline-flex;
    align-items:center;
    gap:.36rem;
    padding:.42rem .7rem;
    border-radius:999px;
    border:1px solid rgba(255,255,255,.08);
    font-size:.76rem;
    font-weight:800;
    white-space:nowrap;
}

.status-pill.remitted{
    background:rgba(34,197,94,.11);
    border-color:rgba(34,197,94,.2);
    color:#86efac;
}

.status-pill.outstanding{
    background:rgba(245,158,11,.12);
    border-color:rgba(245,158,11,.22);
    color:#fdba74;
}

.actions{
    display:flex;
    flex-wrap:wrap;
    gap:.5rem;
}

.actions form{
    margin:0;
}

.mini-btn,
.mini-btn-danger{
    min-height:34px;
    padding:.52rem .78rem;
    border-radius:10px;
    font-size:.76rem;
    font-weight:800;
    border:1px solid transparent;
}

.mini-btn{
    background:rgba(56,189,248,.12);
    border-color:rgba(56,189,248,.22);
    color:rgba(255,255,255,.92);
}

.mini-btn-danger{
    background:rgba(239,68,68,.12);
    border-color:rgba(239,68,68,.22);
    color:#fecaca;
}

.mobile-ledger{
    display:none;
    gap:.8rem;
}

.mobile-card{
    padding:1rem;
    border-radius:16px;
    border:1px solid rgba(255,255,255,.06);
    background:rgba(255,255,255,.03);
}

.mobile-card-top{
    display:flex;
    align-items:flex-start;
    justify-content:space-between;
    gap:.7rem;
}

.mobile-card-grid{
    display:grid;
    grid-template-columns:repeat(2, minmax(0, 1fr));
    gap:.75rem;
    margin-top:.85rem;
}

.mobile-label{
    color:var(--earn-muted);
    font-size:.69rem;
    font-weight:800;
    letter-spacing:.08em;
    text-transform:uppercase;
}

.mobile-value{
    margin-top:.18rem;
    font-size:.84rem;
    font-weight:700;
    line-height:1.45;
}

.empty-state{
    padding:1rem;
    border-radius:16px;
    border:1px dashed rgba(255,255,255,.14);
    text-align:center;
    color:var(--earn-muted);
    font-weight:700;
}

.pagination-wrap{
    margin-top:.9rem;
}

@media (max-width: 1199.98px){
    .tool-grid,
    .filters-form{
        grid-template-columns:1fr;
    }

    .stats-row{
        grid-template-columns:repeat(2, minmax(0, 1fr));
    }
}

@media (max-width: 991.98px){
    .print-form,
    .mobile-card-grid{
        grid-template-columns:1fr;
    }

    .ledger-table-wrap{
        display:none;
    }

    .mobile-ledger{
        display:grid;
    }
}

@media (max-width: 767.98px){
    .stats-row{
        grid-template-columns:1fr;
    }

    .hero{
        align-items:stretch;
    }

    .hero-tags{
        width:100%;
    }

    .action-row .btn-admin,
    .action-row .btn-admin-soft,
    .action-row .btn-admin-danger{
        width:100%;
    }
}
</style>

<div class="earnings-shell">
    <div class="earnings-stack">

        <section class="earn-panel hero">
            <div class="hero-copy">
                <h1 class="hero-title">Earnings</h1>
                <p class="hero-subtitle">Track provider remittance by day.</p>
            </div>

            <div class="hero-tags">
                <div class="hero-tag accent">
                    <i class="fa fa-circle-check"></i>
                    <span>Approved providers only</span>
                </div>
                <div class="hero-tag">
                    <i class="fa fa-calendar"></i>
                    <span>{{ \Carbon\Carbon::parse($dateFrom)->format('M d') }} - {{ \Carbon\Carbon::parse($dateTo)->format('M d, Y') }}</span>
                </div>
            </div>
        </section>

        <section class="stats-row">
            <article class="stat-card">
                <div class="stat-label">Earned</div>
                <div class="stat-value accent">PHP {{ number_format((float) ($summary['gross_total'] ?? 0), 2) }}</div>
            </article>

            <article class="stat-card">
                <div class="stat-label">Outstanding</div>
                <div class="stat-value warn">PHP {{ number_format((float) ($summary['outstanding_total'] ?? 0), 2) }}</div>
            </article>

            <article class="stat-card">
                <div class="stat-label">Received</div>
                <div class="stat-value success">PHP {{ number_format((float) ($summary['remitted_total'] ?? 0), 2) }}</div>
            </article>

            <article class="stat-card">
                <div class="stat-label">Providers</div>
                <div class="stat-value">{{ number_format((int) ($summary['providers_count'] ?? 0)) }}</div>
            </article>
        </section>

        <section class="tool-grid">
            <div class="tool-card">
                <div class="tool-head">
                    <div>
                        <h2 class="tool-title">Filter Ledger</h2>
                        <div class="tool-note">Search provider, date, or remittance status.</div>
                    </div>
                    <span class="mini-pill">{{ number_format((int) ($summary['ledger_count'] ?? 0)) }} row{{ (int) ($summary['ledger_count'] ?? 0) === 1 ? '' : 's' }}</span>
                </div>

                <form method="GET" action="{{ route('admin.earnings') }}">
                    <div class="filters-form">
                        <div class="field">
                            <label for="earningsSearch">Provider</label>
                            <input id="earningsSearch" type="text" name="q" value="{{ $search }}" placeholder="Search provider name">
                        </div>

                        <div class="field">
                            <label for="earningsDateFrom">From</label>
                            <input id="earningsDateFrom" type="date" name="date_from" value="{{ $dateFrom }}">
                        </div>

                        <div class="field">
                            <label for="earningsDateTo">To</label>
                            <input id="earningsDateTo" type="date" name="date_to" value="{{ $dateTo }}">
                        </div>

                        <div class="field">
                            <label for="earningsStatus">Status</label>
                            <select id="earningsStatus" name="remittance">
                                <option value="all" {{ $remittanceFilter === 'all' ? 'selected' : '' }}>All</option>
                                <option value="outstanding" {{ $remittanceFilter === 'outstanding' ? 'selected' : '' }}>Outstanding</option>
                                <option value="remitted" {{ $remittanceFilter === 'remitted' ? 'selected' : '' }}>Remitted</option>
                            </select>
                        </div>
                    </div>

                    <div class="action-row">
                        <button type="submit" class="btn-admin">
                            <i class="fa fa-search"></i>
                            <span>Apply</span>
                        </button>

                        <a href="{{ route('admin.earnings') }}" class="btn-admin-soft">
                            <i class="fa fa-rotate-left"></i>
                            <span>Reset</span>
                        </a>
                    </div>
                </form>

                @if(!$remittanceTableReady)
                    <div class="warning-box">
                        Remittance buttons will work after the `provider_remittances` table exists in this environment.
                    </div>
                @endif
            </div>

            <div class="tool-card">
                <div class="tool-head">
                    <div>
                        <h2 class="tool-title">Print List</h2>
                        <div class="tool-note">Daily or monthly remittance summary.</div>
                    </div>
                </div>

                <form method="GET" action="{{ route('admin.earnings.print') }}" target="_blank">
                    <div class="print-form">
                        <div class="field">
                            <label for="printPeriod">Type</label>
                            <select id="printPeriod" name="period">
                                <option value="daily">Daily</option>
                                <option value="monthly">Monthly</option>
                            </select>
                        </div>

                        <div class="field">
                            <label for="printProvider">Provider</label>
                            <select id="printProvider" name="provider_id">
                                <option value="0">All approved providers</option>
                                @foreach($providerOptions as $providerOption)
                                    <option value="{{ $providerOption->id }}">{{ $providerOption->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="field full" id="printDateWrap">
                            <label for="printDate">Date</label>
                            <input id="printDate" type="date" name="date" value="{{ $dateTo }}">
                        </div>

                        <div class="field full" id="printMonthWrap" style="display:none;">
                            <label for="printMonth">Month</label>
                            <input id="printMonth" type="month" name="month" value="{{ substr($dateTo, 0, 7) }}">
                        </div>
                    </div>

                    <div class="action-row">
                        <button type="submit" class="btn-admin">
                            <i class="fa fa-print"></i>
                            <span>Open Print Page</span>
                        </button>
                    </div>
                </form>
            </div>
        </section>

        <section class="ledger-panel">
            <div class="ledger-head">
                <div>
                    <h2 class="tool-title">Daily Ledger</h2>
                    <div class="ledger-kicker">
                        <span class="mini-pill">One row per provider per day</span>
                        <span class="ledger-count">{{ number_format((int) ($summary['ledger_count'] ?? 0)) }} result{{ (int) ($summary['ledger_count'] ?? 0) === 1 ? '' : 's' }}</span>
                    </div>
                </div>
            </div>

            @if($ledger->count() === 0)
                <div class="empty-state">No provider earnings matched the current filters.</div>
            @else
                <div class="ledger-table-wrap">
                    <table class="ledger-table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Provider</th>
                                <th>Jobs</th>
                                <th>Amount</th>
                                <th>Remittance</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($ledger as $row)
                                <tr class="ledger-row">
                                    <td>
                                        <div class="date-line">{{ \Carbon\Carbon::parse($row->remit_date)->format('M d, Y') }}</div>
                                    </td>

                                    <td>
                                        <div class="provider-name">{{ $row->provider_name }}</div>
                                        @if($row->provider_phone !== '')
                                            <div class="provider-phone">{{ $row->provider_phone }}</div>
                                        @endif
                                        <div class="service-summary">{{ $row->service_names !== '' ? $row->service_names : 'Service list unavailable' }}</div>
                                    </td>

                                    <td>
                                        <div class="amount">{{ number_format((int) $row->total_bookings) }}</div>
                                        <div class="sub-line">paid/completed jobs</div>
                                    </td>

                                    <td>
                                        <div class="amount">PHP {{ number_format((float) $row->gross_amount, 2) }}</div>
                                        @if($row->amount_changed)
                                            <div class="sub-line" style="color:#fde68a;">Updated after remittance record</div>
                                        @endif
                                    </td>

                                    <td>
                                        <div class="status-pill {{ $row->is_remitted ? 'remitted' : 'outstanding' }}">
                                            <i class="fa {{ $row->is_remitted ? 'fa-check-circle' : 'fa-clock' }}"></i>
                                            <span>{{ $row->is_remitted ? 'Remitted' : 'Outstanding' }}</span>
                                        </div>
                                        <div class="sub-line">
                                            @if($row->is_remitted && !empty($row->remitted_at))
                                                {{ \Carbon\Carbon::parse($row->remitted_at)->format('M d, Y h:i A') }}
                                            @else
                                                Waiting for payment
                                            @endif
                                        </div>
                                    </td>

                                    <td>
                                        <div class="actions">
                                            @if($row->is_remitted)
                                                <form method="POST" action="{{ route('admin.earnings.outstanding') }}">
                                                    @csrf
                                                    <input type="hidden" name="provider_id" value="{{ $row->provider_id }}">
                                                    <input type="hidden" name="remit_date" value="{{ $row->remit_date }}">
                                                    <button type="submit" class="mini-btn-danger">Undo</button>
                                                </form>
                                            @else
                                                <form method="POST" action="{{ route('admin.earnings.remit') }}">
                                                    @csrf
                                                    <input type="hidden" name="provider_id" value="{{ $row->provider_id }}">
                                                    <input type="hidden" name="remit_date" value="{{ $row->remit_date }}">
                                                    <button type="submit" class="mini-btn" {{ $remittanceTableReady ? '' : 'disabled' }}>Mark Remitted</button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mobile-ledger">
                    @foreach($ledger as $row)
                        <article class="mobile-card">
                            <div class="mobile-card-top">
                                <div>
                                    <div class="provider-name">{{ $row->provider_name }}</div>
                                    <div class="sub-line">{{ \Carbon\Carbon::parse($row->remit_date)->format('M d, Y') }}</div>
                                </div>

                                <div class="status-pill {{ $row->is_remitted ? 'remitted' : 'outstanding' }}">
                                    <span>{{ $row->is_remitted ? 'Remitted' : 'Outstanding' }}</span>
                                </div>
                            </div>

                            <div class="mobile-card-grid">
                                <div>
                                    <div class="mobile-label">Amount</div>
                                    <div class="mobile-value">PHP {{ number_format((float) $row->gross_amount, 2) }}</div>
                                </div>

                                <div>
                                    <div class="mobile-label">Jobs</div>
                                    <div class="mobile-value">{{ number_format((int) $row->total_bookings) }}</div>
                                </div>

                                <div>
                                    <div class="mobile-label">Phone</div>
                                    <div class="mobile-value">{{ $row->provider_phone !== '' ? $row->provider_phone : 'Not available' }}</div>
                                </div>

                                <div>
                                    <div class="mobile-label">Services</div>
                                    <div class="mobile-value">{{ $row->service_names !== '' ? $row->service_names : 'Service list unavailable' }}</div>
                                </div>
                            </div>

                            <div class="action-row">
                                @if($row->is_remitted)
                                    <form method="POST" action="{{ route('admin.earnings.outstanding') }}">
                                        @csrf
                                        <input type="hidden" name="provider_id" value="{{ $row->provider_id }}">
                                        <input type="hidden" name="remit_date" value="{{ $row->remit_date }}">
                                        <button type="submit" class="btn-admin-danger">Undo</button>
                                    </form>
                                @else
                                    <form method="POST" action="{{ route('admin.earnings.remit') }}">
                                        @csrf
                                        <input type="hidden" name="provider_id" value="{{ $row->provider_id }}">
                                        <input type="hidden" name="remit_date" value="{{ $row->remit_date }}">
                                        <button type="submit" class="btn-admin" {{ $remittanceTableReady ? '' : 'disabled' }}>Mark Remitted</button>
                                    </form>
                                @endif
                            </div>
                        </article>
                    @endforeach
                </div>

                <div class="pagination-wrap">
                    {{ $ledger->links('pagination::bootstrap-5') }}
                </div>
            @endif
        </section>

    </div>
</div>

<script>
(function(){
    const periodSelect = document.getElementById('printPeriod');
    const dateWrap = document.getElementById('printDateWrap');
    const monthWrap = document.getElementById('printMonthWrap');

    function syncPrintFields() {
        const isMonthly = periodSelect && periodSelect.value === 'monthly';
        if (dateWrap) dateWrap.style.display = isMonthly ? 'none' : '';
        if (monthWrap) monthWrap.style.display = isMonthly ? '' : 'none';
    }

    periodSelect?.addEventListener('change', syncPrintFields);
    syncPrintFields();
})();
</script>

@endsection
