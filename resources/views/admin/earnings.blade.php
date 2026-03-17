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
    --ae-bg:#020617;
    --ae-card:#071225;
    --ae-card-soft:#0b1830;
    --ae-border:rgba(255,255,255,.08);
    --ae-text:rgba(255,255,255,.95);
    --ae-muted:rgba(255,255,255,.58);
    --ae-accent:#38bdf8;
    --ae-success:#22c55e;
    --ae-warn:#f59e0b;
    --ae-danger:#ef4444;
}

.earnings-page{
    max-width: 1240px;
    margin: 0 auto;
    color: var(--ae-text);
}

.earnings-stack{
    display:flex;
    flex-direction:column;
    gap:1rem;
}

.hero-card,
.metric-card,
.panel-card,
.ledger-card{
    background: linear-gradient(180deg, rgba(7,18,37,.96), rgba(2,6,23,.98));
    border:1px solid var(--ae-border);
    border-radius:22px;
    box-shadow: 0 20px 40px rgba(0,0,0,.28);
}

.hero-card,
.panel-card,
.ledger-card{
    padding:1.15rem;
}

.hero-card{
    display:grid;
    grid-template-columns: minmax(0, 1.4fr) minmax(320px, .9fr);
    gap:1rem;
    align-items:start;
}

.hero-title{
    margin:0;
    font-size:1.28rem;
    font-weight:900;
    letter-spacing:-.02em;
}

.hero-subtitle{
    margin:.45rem 0 0;
    color:var(--ae-muted);
    font-size:.92rem;
    line-height:1.6;
}

.hero-note{
    padding:1rem;
    border-radius:18px;
    border:1px solid rgba(56,189,248,.14);
    background: rgba(56,189,248,.08);
}

.hero-note h6{
    margin:0;
    font-size:.82rem;
    font-weight:900;
    letter-spacing:.08em;
    text-transform:uppercase;
}

.hero-note p{
    margin:.45rem 0 0;
    color:rgba(255,255,255,.88);
    font-size:.88rem;
    line-height:1.55;
}

.metrics{
    display:grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap:1rem;
}

.metric-card{
    padding:1rem;
}

.metric-label{
    color:var(--ae-muted);
    font-size:.76rem;
    font-weight:700;
    letter-spacing:.08em;
    text-transform:uppercase;
}

.metric-value{
    margin-top:.45rem;
    font-size:1.5rem;
    font-weight:900;
    line-height:1.1;
}

.metric-note{
    margin-top:.35rem;
    color:var(--ae-muted);
    font-size:.82rem;
}

.metric-value.accent{ color:var(--ae-accent); }
.metric-value.success{ color:#86efac; }
.metric-value.warn{ color:#fdba74; }

.panels{
    display:grid;
    grid-template-columns: minmax(0, 1.35fr) minmax(0, .95fr);
    gap:1rem;
    align-items:start;
}

.panel-title{
    margin:0;
    font-size:1rem;
    font-weight:900;
}

.panel-subtitle{
    margin:.35rem 0 0;
    color:var(--ae-muted);
    font-size:.84rem;
    line-height:1.5;
}

.filters-grid,
.print-grid{
    display:grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap:.75rem;
    margin-top:1rem;
}

.print-grid{
    grid-template-columns: repeat(2, minmax(0, 1fr));
}

.control{
    display:flex;
    flex-direction:column;
    gap:.35rem;
}

.control.full{
    grid-column: 1 / -1;
}

.control label{
    color:var(--ae-muted);
    font-size:.75rem;
    font-weight:700;
    letter-spacing:.08em;
    text-transform:uppercase;
}

.control input,
.control select{
    min-height:42px;
    padding:.68rem .82rem;
    border-radius:12px;
    border:1px solid rgba(255,255,255,.1);
    background: rgba(2,6,23,.92);
    color:rgba(255,255,255,.95);
    outline:none;
}

.control input:focus,
.control select:focus{
    border-color: rgba(56,189,248,.35);
    box-shadow: 0 0 0 3px rgba(56,189,248,.1);
}

.action-row{
    display:flex;
    flex-wrap:wrap;
    gap:.7rem;
    margin-top:1rem;
}

.btn-admin,
.btn-admin-secondary,
.btn-admin-danger{
    display:inline-flex;
    align-items:center;
    justify-content:center;
    gap:.45rem;
    min-height:42px;
    padding:.68rem 1rem;
    border-radius:12px;
    text-decoration:none;
    font-weight:800;
    border:1px solid transparent;
}

.btn-admin{
    background: rgba(56,189,248,.14);
    border-color: rgba(56,189,248,.28);
    color:rgba(255,255,255,.95);
}

.btn-admin-secondary{
    background: rgba(255,255,255,.03);
    border-color: rgba(255,255,255,.1);
    color:rgba(255,255,255,.92);
}

.btn-admin-danger{
    background: rgba(239,68,68,.12);
    border-color: rgba(239,68,68,.24);
    color:#fecaca;
}

.warning-banner{
    margin-top:1rem;
    padding:.95rem 1rem;
    border-radius:16px;
    border:1px solid rgba(245,158,11,.24);
    background: rgba(245,158,11,.1);
    color:#fde68a;
    font-size:.88rem;
    line-height:1.55;
}

.ledger-header{
    display:flex;
    align-items:flex-start;
    justify-content:space-between;
    gap:1rem;
    flex-wrap:wrap;
}

.ledger-table{
    width:100%;
    margin-top:1rem;
    border-collapse: collapse;
}

.ledger-table th{
    color:var(--ae-muted);
    font-size:.74rem;
    font-weight:800;
    letter-spacing:.08em;
    text-transform:uppercase;
    padding:.85rem .75rem;
    border-bottom:1px solid rgba(255,255,255,.08);
    white-space:nowrap;
}

.ledger-table td{
    padding:.95rem .75rem;
    border-bottom:1px solid rgba(255,255,255,.05);
    vertical-align:top;
}

.ledger-row:hover{
    background: rgba(255,255,255,.02);
}

.provider-cell strong{
    display:block;
    font-size:.94rem;
}

.provider-cell span{
    color:var(--ae-muted);
    font-size:.82rem;
}

.service-list{
    color:var(--ae-muted);
    font-size:.82rem;
    line-height:1.45;
    max-width:260px;
}

.status-pill{
    display:inline-flex;
    align-items:center;
    gap:.38rem;
    padding:.42rem .7rem;
    border-radius:999px;
    border:1px solid rgba(255,255,255,.08);
    font-size:.78rem;
    font-weight:800;
    white-space:nowrap;
}

.status-pill.remitted{
    background: rgba(34,197,94,.12);
    border-color: rgba(34,197,94,.22);
    color:#86efac;
}

.status-pill.outstanding{
    background: rgba(245,158,11,.12);
    border-color: rgba(245,158,11,.22);
    color:#fdba74;
}

.small-meta{
    margin-top:.3rem;
    color:var(--ae-muted);
    font-size:.78rem;
}

.amount-change{
    color:#fde68a;
}

.row-actions{
    display:flex;
    flex-wrap:wrap;
    gap:.55rem;
}

.row-actions form{
    margin:0;
}

.mini-btn,
.mini-btn-danger{
    min-height:36px;
    padding:.55rem .8rem;
    border-radius:10px;
    font-size:.78rem;
    font-weight:800;
    border:1px solid transparent;
    background: transparent;
}

.mini-btn{
    background: rgba(56,189,248,.12);
    border-color: rgba(56,189,248,.22);
    color:rgba(255,255,255,.92);
}

.mini-btn-danger{
    background: rgba(239,68,68,.12);
    border-color: rgba(239,68,68,.2);
    color:#fecaca;
}

.mobile-ledger{
    display:none;
    gap:.85rem;
    margin-top:1rem;
}

.mobile-card{
    padding:1rem;
    border-radius:18px;
    border:1px solid rgba(255,255,255,.06);
    background: rgba(255,255,255,.03);
}

.mobile-top{
    display:flex;
    align-items:flex-start;
    justify-content:space-between;
    gap:.75rem;
}

.mobile-grid{
    display:grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap:.7rem;
    margin-top:.9rem;
}

.mobile-k{
    color:var(--ae-muted);
    font-size:.72rem;
    font-weight:700;
    letter-spacing:.08em;
    text-transform:uppercase;
}

.mobile-v{
    margin-top:.22rem;
    font-size:.88rem;
    font-weight:700;
    line-height:1.45;
    word-break:break-word;
}

.empty-state{
    margin-top:1rem;
    padding:1.2rem;
    border-radius:18px;
    border:1px dashed rgba(255,255,255,.16);
    text-align:center;
    color:var(--ae-muted);
}

.pagination-wrap{
    margin-top:1rem;
}

@media (max-width: 1199.98px){
    .hero-card,
    .panels{
        grid-template-columns: 1fr;
    }

    .metrics{
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}

@media (max-width: 991.98px){
    .filters-grid{
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .print-grid{
        grid-template-columns: 1fr;
    }

    .ledger-table{
        display:none;
    }

    .mobile-ledger{
        display:grid;
    }
}

@media (max-width: 767.98px){
    .metrics,
    .filters-grid,
    .mobile-grid{
        grid-template-columns: 1fr;
    }

    .action-row .btn-admin,
    .action-row .btn-admin-secondary,
    .action-row .btn-admin-danger{
        width:100%;
    }
}
</style>

<div class="earnings-page">
    <div class="earnings-stack">

        <section class="hero-card">
            <div>
                <h1 class="hero-title">Provider Earnings and Remittance</h1>
                <p class="hero-subtitle">
                    Track daily provider earnings, search by provider, and mark remittances once earnings have been turned over.
                    Only approved providers are included here.
                </p>
            </div>

            <div class="hero-note">
                <h6>How This Works</h6>
                <p>
                    Each row represents one approved provider’s earned bookings for a specific day.
                    Use the remittance action when that day’s earnings have been remitted.
                </p>
            </div>
        </section>

        <section class="metrics">
            <div class="metric-card">
                <div class="metric-label">Gross in range</div>
                <div class="metric-value accent">PHP {{ number_format((float) ($summary['gross_total'] ?? 0), 2) }}</div>
                <div class="metric-note">All paid and completed bookings in the current filter.</div>
            </div>

            <div class="metric-card">
                <div class="metric-label">Outstanding</div>
                <div class="metric-value warn">PHP {{ number_format((float) ($summary['outstanding_total'] ?? 0), 2) }}</div>
                <div class="metric-note">Still waiting to be marked as remitted.</div>
            </div>

            <div class="metric-card">
                <div class="metric-label">Remitted</div>
                <div class="metric-value success">PHP {{ number_format((float) ($summary['remitted_total'] ?? 0), 2) }}</div>
                <div class="metric-note">Already marked as received/remitted.</div>
            </div>

            <div class="metric-card">
                <div class="metric-label">Approved providers</div>
                <div class="metric-value">{{ number_format((int) ($summary['providers_count'] ?? 0)) }}</div>
                <div class="metric-note">{{ number_format((int) ($summary['ledger_count'] ?? 0)) }} earning day entries in this view.</div>
            </div>
        </section>

        <section class="panels">
            <article class="panel-card">
                <h2 class="panel-title">Search and filter</h2>
                <p class="panel-subtitle">Look up a provider, narrow the date range, or show only remitted or outstanding rows.</p>

                <form method="GET" action="{{ route('admin.earnings') }}">
                    <div class="filters-grid">
                        <div class="control">
                            <label for="earningsSearch">Search provider</label>
                            <input id="earningsSearch" type="text" name="q" value="{{ $search }}" placeholder="Type provider name">
                        </div>

                        <div class="control">
                            <label for="earningsDateFrom">From</label>
                            <input id="earningsDateFrom" type="date" name="date_from" value="{{ $dateFrom }}">
                        </div>

                        <div class="control">
                            <label for="earningsDateTo">To</label>
                            <input id="earningsDateTo" type="date" name="date_to" value="{{ $dateTo }}">
                        </div>

                        <div class="control">
                            <label for="earningsStatus">Remittance status</label>
                            <select id="earningsStatus" name="remittance">
                                <option value="all" {{ $remittanceFilter === 'all' ? 'selected' : '' }}>All rows</option>
                                <option value="outstanding" {{ $remittanceFilter === 'outstanding' ? 'selected' : '' }}>Outstanding only</option>
                                <option value="remitted" {{ $remittanceFilter === 'remitted' ? 'selected' : '' }}>Remitted only</option>
                            </select>
                        </div>
                    </div>

                    <div class="action-row">
                        <button type="submit" class="btn-admin">
                            <i class="fa fa-search"></i>
                            <span>Apply Filters</span>
                        </button>

                        <a href="{{ route('admin.earnings') }}" class="btn-admin-secondary">
                            <i class="fa fa-rotate-left"></i>
                            <span>Reset</span>
                        </a>
                    </div>
                </form>

                @if(!$remittanceTableReady)
                    <div class="warning-banner">
                        Remittance tracking is not fully active yet because the `provider_remittances` table has not been created in this environment.
                        Run your migrations, then reload this page to enable mark-remitted actions.
                    </div>
                @endif
            </article>

            <aside class="panel-card">
                <h2 class="panel-title">Printable remittance list</h2>
                <p class="panel-subtitle">Open a print-friendly list for one day or one month, with totals per approved provider.</p>

                <form method="GET" action="{{ route('admin.earnings.print') }}" target="_blank" id="printRemittanceForm">
                    <div class="print-grid">
                        <div class="control">
                            <label for="printPeriod">Print type</label>
                            <select id="printPeriod" name="period">
                                <option value="daily">Daily remittance</option>
                                <option value="monthly">Monthly remittance</option>
                            </select>
                        </div>

                        <div class="control">
                            <label for="printProvider">Provider</label>
                            <select id="printProvider" name="provider_id">
                                <option value="0">All approved providers</option>
                                @foreach($providerOptions as $providerOption)
                                    <option value="{{ $providerOption->id }}">{{ $providerOption->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="control" id="printDateWrap">
                            <label for="printDate">Date</label>
                            <input id="printDate" type="date" name="date" value="{{ $dateTo }}">
                        </div>

                        <div class="control" id="printMonthWrap" style="display:none;">
                            <label for="printMonth">Month</label>
                            <input id="printMonth" type="month" name="month" value="{{ substr($dateTo, 0, 7) }}">
                        </div>
                    </div>

                    <div class="action-row">
                        <button type="submit" class="btn-admin">
                            <i class="fa fa-print"></i>
                            <span>Open Printable Page</span>
                        </button>
                    </div>
                </form>
            </aside>
        </section>

        <section class="ledger-card">
            <div class="ledger-header">
                <div>
                    <h2 class="panel-title">Daily provider ledger</h2>
                    <p class="panel-subtitle">Daily earned totals for approved providers based on paid and completed bookings.</p>
                </div>
            </div>

            @if($ledger->count() === 0)
                <div class="empty-state">No provider earnings matched the current filters.</div>
            @else
                <table class="ledger-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Provider</th>
                            <th>Bookings</th>
                            <th>Services</th>
                            <th>Gross</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($ledger as $row)
                            <tr class="ledger-row">
                                <td>
                                    <strong>{{ \Carbon\Carbon::parse($row->remit_date)->format('M d, Y') }}</strong>
                                </td>
                                <td class="provider-cell">
                                    <strong>{{ $row->provider_name }}</strong>
                                    @if($row->provider_phone !== '')
                                        <span>{{ $row->provider_phone }}</span>
                                    @endif
                                </td>
                                <td>
                                    <strong>{{ number_format((int) $row->total_bookings) }}</strong>
                                    <div class="small-meta">paid/completed jobs</div>
                                </td>
                                <td>
                                    <div class="service-list">{{ $row->service_names !== '' ? $row->service_names : 'Service list unavailable' }}</div>
                                </td>
                                <td>
                                    <strong>PHP {{ number_format((float) $row->gross_amount, 2) }}</strong>
                                    @if($row->amount_changed)
                                        <div class="small-meta amount-change">Updated after remittance record</div>
                                    @endif
                                </td>
                                <td>
                                    <div class="status-pill {{ $row->is_remitted ? 'remitted' : 'outstanding' }}">
                                        <i class="fa {{ $row->is_remitted ? 'fa-check-circle' : 'fa-clock' }}"></i>
                                        <span>{{ $row->is_remitted ? 'Remitted' : 'Outstanding' }}</span>
                                    </div>

                                    <div class="small-meta">
                                        @if($row->is_remitted && !empty($row->remitted_at))
                                            Marked {{ \Carbon\Carbon::parse($row->remitted_at)->format('M d, Y h:i A') }}
                                        @else
                                            Waiting for remittance confirmation
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <div class="row-actions">
                                        @if($row->is_remitted)
                                            <form method="POST" action="{{ route('admin.earnings.outstanding') }}">
                                                @csrf
                                                <input type="hidden" name="provider_id" value="{{ $row->provider_id }}">
                                                <input type="hidden" name="remit_date" value="{{ $row->remit_date }}">
                                                <button type="submit" class="mini-btn-danger">
                                                    Mark Outstanding
                                                </button>
                                            </form>
                                        @else
                                            <form method="POST" action="{{ route('admin.earnings.remit') }}">
                                                @csrf
                                                <input type="hidden" name="provider_id" value="{{ $row->provider_id }}">
                                                <input type="hidden" name="remit_date" value="{{ $row->remit_date }}">
                                                <button type="submit" class="mini-btn" {{ $remittanceTableReady ? '' : 'disabled' }}>
                                                    Mark Remitted
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <div class="mobile-ledger">
                    @foreach($ledger as $row)
                        <article class="mobile-card">
                            <div class="mobile-top">
                                <div>
                                    <strong>{{ $row->provider_name }}</strong>
                                    <div class="small-meta">{{ \Carbon\Carbon::parse($row->remit_date)->format('M d, Y') }}</div>
                                </div>

                                <div class="status-pill {{ $row->is_remitted ? 'remitted' : 'outstanding' }}">
                                    <span>{{ $row->is_remitted ? 'Remitted' : 'Outstanding' }}</span>
                                </div>
                            </div>

                            <div class="mobile-grid">
                                <div>
                                    <div class="mobile-k">Bookings</div>
                                    <div class="mobile-v">{{ number_format((int) $row->total_bookings) }}</div>
                                </div>

                                <div>
                                    <div class="mobile-k">Gross</div>
                                    <div class="mobile-v">PHP {{ number_format((float) $row->gross_amount, 2) }}</div>
                                </div>

                                <div>
                                    <div class="mobile-k">Phone</div>
                                    <div class="mobile-v">{{ $row->provider_phone !== '' ? $row->provider_phone : 'Not available' }}</div>
                                </div>

                                <div>
                                    <div class="mobile-k">Services</div>
                                    <div class="mobile-v">{{ $row->service_names !== '' ? $row->service_names : 'Service list unavailable' }}</div>
                                </div>
                            </div>

                            <div class="row-actions" style="margin-top:.9rem;">
                                @if($row->is_remitted)
                                    <form method="POST" action="{{ route('admin.earnings.outstanding') }}">
                                        @csrf
                                        <input type="hidden" name="provider_id" value="{{ $row->provider_id }}">
                                        <input type="hidden" name="remit_date" value="{{ $row->remit_date }}">
                                        <button type="submit" class="mini-btn-danger">Mark Outstanding</button>
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

        if (dateWrap) {
            dateWrap.style.display = isMonthly ? 'none' : '';
        }

        if (monthWrap) {
            monthWrap.style.display = isMonthly ? '' : 'none';
        }
    }

    periodSelect?.addEventListener('change', syncPrintFields);
    syncPrintFields();
})();
</script>

@endsection
