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
    --earn-bg:#020617;
    --earn-panel:#091427;
    --earn-panel-2:#0c1830;
    --earn-border:rgba(255,255,255,.08);
    --earn-border-soft:rgba(255,255,255,.05);
    --earn-text:#f8fafc;
    --earn-muted:#94a3b8;
    --earn-accent:#38bdf8;
    --earn-success:#22c55e;
    --earn-warn:#f59e0b;
    --earn-danger:#ef4444;
    --earn-radius:18px;
    --earn-shadow:0 12px 30px rgba(0,0,0,.28);
}

.earn-wrap{
    padding:14px 12px 22px;
    color:var(--earn-text);
}

.earn-page{
    max-width:100%;
}

.earn-head{
    display:flex;
    align-items:flex-end;
    justify-content:space-between;
    gap:12px;
    flex-wrap:wrap;
    margin-bottom:12px;
}

.earn-title{
    margin:0;
    font-size:2rem;
    font-weight:950;
    line-height:1.05;
    letter-spacing:-.03em;
}

.earn-sub{
    margin-top:6px;
    color:var(--earn-muted);
    font-size:.94rem;
}

.head-tags{
    display:flex;
    align-items:center;
    gap:10px;
    flex-wrap:wrap;
}

.head-tag{
    display:inline-flex;
    align-items:center;
    gap:.45rem;
    min-height:38px;
    padding:.5rem .85rem;
    border-radius:999px;
    border:1px solid rgba(255,255,255,.08);
    background:rgba(255,255,255,.03);
    color:#e2e8f0;
    font-size:.82rem;
    font-weight:900;
    white-space:nowrap;
}

.head-tag.accent{
    border-color:rgba(56,189,248,.26);
    background:rgba(56,189,248,.10);
    color:#e0f2fe;
}

.flash{
    margin-bottom:12px;
    padding:.9rem 1rem;
    border-radius:14px;
    border:1px solid var(--earn-border);
    font-weight:800;
}

.flash.success{
    background:rgba(34,197,94,.10);
    border-color:rgba(34,197,94,.22);
    color:#bbf7d0;
}

.flash.error{
    background:rgba(239,68,68,.10);
    border-color:rgba(239,68,68,.22);
    color:#fecaca;
}

.stats{
    display:grid;
    grid-template-columns:repeat(4, minmax(0, 1fr));
    gap:10px;
    margin-bottom:12px;
}

.stat-card{
    background:linear-gradient(180deg, rgba(12,24,48,.94), rgba(6,14,28,.98));
    border:1px solid var(--earn-border);
    border-radius:16px;
    padding:14px 14px 12px;
    box-shadow:var(--earn-shadow);
    min-height:86px;
    display:flex;
    flex-direction:column;
    justify-content:center;
}

.stat-k{
    color:#8ea2c5;
    font-size:.72rem;
    font-weight:900;
    letter-spacing:.12em;
    text-transform:uppercase;
    line-height:1.2;
}

.stat-v{
    margin-top:8px;
    font-size:1.45rem;
    font-weight:950;
    line-height:1;
    color:#fff;
}

.stat-v.accent{ color:var(--earn-accent); }
.stat-v.success{ color:#86efac; }
.stat-v.warn{ color:#fdba74; }

.panel{
    background:linear-gradient(180deg, rgba(9,20,39,.97), rgba(5,13,26,.99));
    border:1px solid var(--earn-border);
    border-radius:var(--earn-radius);
    box-shadow:var(--earn-shadow);
}

.toolbar-panel{
    padding:14px;
    margin-bottom:12px;
}

.toolbar-top{
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:10px;
    flex-wrap:wrap;
    margin-bottom:12px;
}

.toolbar-title{
    margin:0;
    font-size:1rem;
    font-weight:900;
}

.toolbar-sub{
    margin-top:4px;
    color:var(--earn-muted);
    font-size:.84rem;
}

.toolbar-pills{
    display:flex;
    align-items:center;
    gap:8px;
    flex-wrap:wrap;
}

.toolbar-pill{
    display:inline-flex;
    align-items:center;
    gap:.42rem;
    min-height:32px;
    padding:.4rem .72rem;
    border-radius:999px;
    border:1px solid rgba(255,255,255,.08);
    background:rgba(255,255,255,.03);
    color:#dbe7f5;
    font-size:.74rem;
    font-weight:800;
}

.toolbar-grid{
    display:grid;
    grid-template-columns:1fr;
    gap:10px;
}

.tool-block{
    border-top:1px solid var(--earn-border-soft);
    padding-top:10px;
}

.tool-block:first-child{
    border-top:none;
    padding-top:0;
}

.tool-block-title{
    margin:0 0 8px;
    font-size:.74rem;
    font-weight:900;
    letter-spacing:.14em;
    text-transform:uppercase;
    color:#cbd5e1;
}

.filter-grid{
    display:grid;
    grid-template-columns:minmax(230px, 1.7fr) repeat(3, minmax(145px, .9fr)) auto auto;
    gap:10px;
    align-items:end;
}

.print-grid{
    display:grid;
    grid-template-columns:minmax(140px, .72fr) minmax(240px, 1.15fr) minmax(190px, .9fr) auto;
    gap:10px;
    align-items:end;
}

.field{
    display:flex;
    flex-direction:column;
    gap:5px;
}

.field.full{
    grid-column:1 / -1;
}

.field.action{
    justify-content:flex-end;
}

.field.action label{
    opacity:0;
    pointer-events:none;
}

.field label{
    color:#8ea2c5;
    font-size:.68rem;
    letter-spacing:.12em;
    text-transform:uppercase;
    font-weight:900;
    line-height:1.2;
}

.field input,
.field select{
    width:100%;
    height:44px;
    background:linear-gradient(180deg, rgba(2,6,23,.92), rgba(4,10,24,.98));
    border:1px solid rgba(255,255,255,.10);
    color:var(--earn-text);
    color-scheme:dark;
    border-radius:12px;
    padding:.7rem .9rem;
    outline:none;
    font-size:.92rem;
    font-weight:800;
}

.field input:focus,
.field select:focus{
    border-color:rgba(56,189,248,.55);
    box-shadow:0 0 0 3px rgba(56,189,248,.10);
}

.field input::placeholder{
    color:rgba(203,213,225,.48);
}

.field select option{
    background:#07111f;
    color:#f8fafc;
}

.field input::-webkit-calendar-picker-indicator{
    filter:invert(1);
    opacity:.82;
}

.btnx{
    height:44px;
    display:inline-flex;
    align-items:center;
    justify-content:center;
    gap:.45rem;
    padding:.7rem .95rem;
    border-radius:12px;
    border:1px solid transparent;
    font-size:.84rem;
    font-weight:900;
    text-decoration:none;
    white-space:nowrap;
}

.btnx.primary{
    background:rgba(56,189,248,.12);
    border-color:rgba(56,189,248,.26);
    color:#e0f2fe;
}

.btnx.soft{
    background:rgba(255,255,255,.03);
    border-color:rgba(255,255,255,.10);
    color:#e2e8f0;
}

.btnx.danger{
    background:rgba(239,68,68,.12);
    border-color:rgba(239,68,68,.22);
    color:#fecaca;
}

.warning-box{
    margin-top:12px;
    padding:.82rem .9rem;
    border-radius:14px;
    border:1px solid rgba(245,158,11,.22);
    background:rgba(245,158,11,.10);
    color:#fde68a;
    font-size:.8rem;
    font-weight:800;
}

.ledger-panel{
    padding:14px;
}

.ledger-head{
    display:flex;
    align-items:flex-end;
    justify-content:space-between;
    gap:10px;
    flex-wrap:wrap;
    margin-bottom:12px;
}

.ledger-title{
    margin:0;
    font-size:1rem;
    font-weight:900;
}

.ledger-sub{
    margin-top:4px;
    color:var(--earn-muted);
    font-size:.84rem;
}

.table-shell{
    width:100%;
    overflow-x:auto;
    overflow-y:hidden;
}

.ledger-table{
    width:100%;
    min-width:980px;
    border-collapse:collapse;
}

.ledger-table th{
    padding:.85rem .9rem;
    background:rgba(8,17,34,.98);
    color:#8ea2c5;
    border-bottom:1px solid var(--earn-border);
    font-size:.72rem;
    letter-spacing:.12em;
    text-transform:uppercase;
    font-weight:900;
    white-space:nowrap;
    text-align:left;
}

.ledger-table td{
    color:rgba(248,250,252,.95);
    border-bottom:1px solid var(--earn-border-soft);
    padding:.9rem;
    vertical-align:middle;
}

.ledger-table tbody tr:hover td{
    background:rgba(56,189,248,.05);
}

.provider-name{
    font-size:.96rem;
    font-weight:900;
    line-height:1.2;
}

.provider-meta{
    margin-top:4px;
    color:var(--earn-muted);
    font-size:.8rem;
    line-height:1.4;
}

.service-meta{
    margin-top:4px;
    color:#d6deea;
    font-size:.82rem;
    line-height:1.4;
}

.value-strong{
    font-size:.96rem;
    font-weight:950;
    color:#fff;
}

.value-soft{
    margin-top:4px;
    color:var(--earn-muted);
    font-size:.78rem;
    font-weight:700;
}

.status-pill{
    display:inline-flex;
    align-items:center;
    gap:.38rem;
    min-height:32px;
    padding:.38rem .72rem;
    border-radius:999px;
    border:1px solid rgba(255,255,255,.10);
    font-size:.74rem;
    font-weight:900;
    white-space:nowrap;
}

.status-pill.remitted{
    color:#86efac;
    border-color:rgba(34,197,94,.28);
    background:rgba(34,197,94,.10);
}

.status-pill.outstanding{
    color:#fdba74;
    border-color:rgba(245,158,11,.28);
    background:rgba(245,158,11,.10);
}

.actions{
    display:flex;
    justify-content:flex-start;
    align-items:center;
    gap:8px;
    flex-wrap:wrap;
}

.actions form{
    margin:0;
}

.empty-state{
    padding:34px 18px;
    border:1px dashed rgba(255,255,255,.14);
    border-radius:16px;
    text-align:center;
    color:var(--earn-muted);
    font-weight:800;
}

.mobile-list{
    display:none;
}

.mobile-card{
    border:1px solid var(--earn-border-soft);
    background:rgba(255,255,255,.02);
    border-radius:16px;
    padding:12px;
}

.mobile-top{
    display:flex;
    align-items:flex-start;
    justify-content:space-between;
    gap:10px;
}

.mobile-grid{
    display:grid;
    grid-template-columns:repeat(2, minmax(0, 1fr));
    gap:10px;
    margin-top:12px;
}

.mobile-k{
    color:#8ea2c5;
    font-size:.68rem;
    letter-spacing:.12em;
    text-transform:uppercase;
    font-weight:900;
}

.mobile-v{
    margin-top:4px;
    color:#fff;
    font-size:.88rem;
    font-weight:800;
    line-height:1.4;
    word-break:break-word;
}

.mobile-actions{
    display:flex;
    gap:8px;
    flex-wrap:wrap;
    margin-top:12px;
}

.pagination-wrap{
    margin-top:12px;
}

@media (max-width: 1200px){
    .stats{
        grid-template-columns:repeat(2, minmax(0, 1fr));
    }

    .filter-grid{
        grid-template-columns:repeat(3, minmax(0, 1fr));
    }

    .print-grid{
        grid-template-columns:repeat(2, minmax(0, 1fr));
    }
}

@media (max-width: 768px){
    .earn-wrap{
        padding:16px 12px 22px;
    }

    .earn-title{
        font-size:1.55rem;
    }

    .stats{
        grid-template-columns:1fr;
    }

    .filter-grid,
    .print-grid,
    .mobile-grid{
        grid-template-columns:1fr;
    }

    .table-shell{
        display:none;
    }

    .mobile-list{
        display:grid;
        gap:10px;
    }

    .mobile-actions .btnx,
    .mobile-actions form{
        width:100%;
    }

    .mobile-actions form .btnx{
        width:100%;
    }
}
</style>

<div class="earn-wrap">
    <div class="earn-page">

        @if(session('success'))
            <div class="flash success">{{ session('success') }}</div>
        @endif

        @if($errors->any())
            <div class="flash error">
                {{ $errors->first() }}
            </div>
        @endif

        <div class="earn-head">
            <div>
                <h1 class="earn-title">Earnings</h1>
                <div class="earn-sub">Provider remittance tracking for approved providers.</div>
            </div>

            <div class="head-tags">
                <div class="head-tag accent">
                    <i class="fa fa-circle-check"></i>
                    <span>Approved providers only</span>
                </div>
                <div class="head-tag">
                    <i class="fa fa-calendar"></i>
                    <span>{{ \Carbon\Carbon::parse($dateFrom)->format('M d') }} - {{ \Carbon\Carbon::parse($dateTo)->format('M d, Y') }}</span>
                </div>
            </div>
        </div>

        <div class="stats">
            <div class="stat-card">
                <div class="stat-k">Earned</div>
                <div class="stat-v accent">PHP {{ number_format((float) ($summary['gross_total'] ?? 0), 2) }}</div>
            </div>

            <div class="stat-card">
                <div class="stat-k">Outstanding</div>
                <div class="stat-v warn">PHP {{ number_format((float) ($summary['outstanding_total'] ?? 0), 2) }}</div>
            </div>

            <div class="stat-card">
                <div class="stat-k">Received</div>
                <div class="stat-v success">PHP {{ number_format((float) ($summary['remitted_total'] ?? 0), 2) }}</div>
            </div>

            <div class="stat-card">
                <div class="stat-k">Providers</div>
                <div class="stat-v">{{ number_format((int) ($summary['providers_count'] ?? 0)) }}</div>
            </div>
        </div>

        <section class="panel toolbar-panel">
            <div class="toolbar-top">
                <div>
                    <h2 class="toolbar-title">Filter, Search, and Print</h2>
                    <div class="toolbar-sub">Filter provider remittance rows or open a print-ready list.</div>
                </div>

                <div class="toolbar-pills">
                    <span class="toolbar-pill">{{ number_format((int) ($summary['ledger_count'] ?? 0)) }} row{{ (int) ($summary['ledger_count'] ?? 0) === 1 ? '' : 's' }}</span>
                    <span class="toolbar-pill">{{ number_format((int) ($summary['providers_count'] ?? 0)) }} provider{{ (int) ($summary['providers_count'] ?? 0) === 1 ? '' : 's' }}</span>
                </div>
            </div>

            <div class="toolbar-grid">
                <div class="tool-block">
                    <div class="tool-block-title">Ledger Filters</div>

                    <form method="GET" action="{{ route('admin.earnings') }}">
                        <div class="filter-grid">
                            <div class="field">
                                <label for="earningsSearch">Search</label>
                                <input id="earningsSearch" type="text" name="q" value="{{ $search }}" placeholder="Provider, phone, service, option, or date">
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

                            <div class="field action">
                                <label>&nbsp;</label>
                                <button type="submit" class="btnx primary">
                                    <i class="fa fa-search"></i>
                                    <span>Apply</span>
                                </button>
                            </div>

                            <div class="field action">
                                <label>&nbsp;</label>
                                <a href="{{ route('admin.earnings') }}" class="btnx soft">
                                    <i class="fa fa-rotate-left"></i>
                                    <span>Reset</span>
                                </a>
                            </div>
                        </div>
                    </form>
                </div>

                <div class="tool-block">
                    <div class="tool-block-title">Print List</div>

                    <form method="GET" action="{{ route('admin.earnings.print') }}" target="_blank">
                        <div class="print-grid">
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

                            <div class="field" id="printDateWrap">
                                <label for="printDate">Date</label>
                                <input id="printDate" type="date" name="date" value="{{ $dateTo }}">
                            </div>

                            <div class="field" id="printMonthWrap" style="display:none;">
                                <label for="printMonth">Month</label>
                                <input id="printMonth" type="month" name="month" value="{{ substr($dateTo, 0, 7) }}">
                            </div>

                            <div class="field action">
                                <label>&nbsp;</label>
                                <button type="submit" class="btnx primary">
                                    <i class="fa fa-print"></i>
                                    <span>Open Print Page</span>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            @if(!$remittanceTableReady)
                <div class="warning-box">
                    Remittance actions will work after the `provider_remittances` table exists in this environment.
                </div>
            @endif
        </section>

        <section class="panel ledger-panel">
            <div class="ledger-head">
                <div>
                    <h2 class="ledger-title">Daily Ledger</h2>
                    <div class="ledger-sub">One row per provider per qualifying booking day.</div>
                </div>

                <div class="toolbar-pills">
                    <span class="toolbar-pill">{{ number_format((int) ($summary['ledger_count'] ?? 0)) }} result{{ (int) ($summary['ledger_count'] ?? 0) === 1 ? '' : 's' }}</span>
                </div>
            </div>

            @if($ledger->count() === 0)
                <div class="empty-state">No provider earnings matched the current filters.</div>
            @else
                <div class="table-shell">
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
                                <tr>
                                    <td>
                                        <div class="value-strong">{{ \Carbon\Carbon::parse($row->remit_date)->format('M d, Y') }}</div>
                                    </td>

                                    <td>
                                        <div class="provider-name">{{ $row->provider_name }}</div>
                                        @if($row->provider_phone !== '')
                                            <div class="provider-meta">{{ $row->provider_phone }}</div>
                                        @endif
                                        <div class="service-meta">{{ $row->service_names !== '' ? $row->service_names : 'Service list unavailable' }}</div>
                                    </td>

                                    <td>
                                        <div class="value-strong">{{ number_format((int) $row->total_bookings) }}</div>
                                        <div class="value-soft">eligible bookings</div>
                                    </td>

                                    <td>
                                        <div class="value-strong">PHP {{ number_format((float) $row->gross_amount, 2) }}</div>
                                        @if($row->amount_changed)
                                            <div class="value-soft" style="color:#fde68a;">Updated after remittance record</div>
                                        @endif
                                    </td>

                                    <td>
                                        <div class="status-pill {{ $row->is_remitted ? 'remitted' : 'outstanding' }}">
                                            <i class="fa {{ $row->is_remitted ? 'fa-check-circle' : 'fa-clock' }}"></i>
                                            <span>{{ $row->is_remitted ? 'Remitted' : 'Outstanding' }}</span>
                                        </div>
                                        <div class="value-soft">
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
                                                    <button type="submit" class="btnx danger">Undo</button>
                                                </form>
                                            @else
                                                <form method="POST" action="{{ route('admin.earnings.remit') }}">
                                                    @csrf
                                                    <input type="hidden" name="provider_id" value="{{ $row->provider_id }}">
                                                    <input type="hidden" name="remit_date" value="{{ $row->remit_date }}">
                                                    <button type="submit" class="btnx primary" {{ $remittanceTableReady ? '' : 'disabled' }}>Mark Remitted</button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mobile-list">
                    @foreach($ledger as $row)
                        <article class="mobile-card">
                            <div class="mobile-top">
                                <div>
                                    <div class="provider-name">{{ $row->provider_name }}</div>
                                    <div class="provider-meta">{{ \Carbon\Carbon::parse($row->remit_date)->format('M d, Y') }}</div>
                                </div>

                                <div class="status-pill {{ $row->is_remitted ? 'remitted' : 'outstanding' }}">
                                    <span>{{ $row->is_remitted ? 'Remitted' : 'Outstanding' }}</span>
                                </div>
                            </div>

                            <div class="mobile-grid">
                                <div>
                                    <div class="mobile-k">Amount</div>
                                    <div class="mobile-v">PHP {{ number_format((float) $row->gross_amount, 2) }}</div>
                                </div>

                                <div>
                                    <div class="mobile-k">Jobs</div>
                                    <div class="mobile-v">{{ number_format((int) $row->total_bookings) }}</div>
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

                            <div class="mobile-actions">
                                @if($row->is_remitted)
                                    <form method="POST" action="{{ route('admin.earnings.outstanding') }}">
                                        @csrf
                                        <input type="hidden" name="provider_id" value="{{ $row->provider_id }}">
                                        <input type="hidden" name="remit_date" value="{{ $row->remit_date }}">
                                        <button type="submit" class="btnx danger">Undo</button>
                                    </form>
                                @else
                                    <form method="POST" action="{{ route('admin.earnings.remit') }}">
                                        @csrf
                                        <input type="hidden" name="provider_id" value="{{ $row->provider_id }}">
                                        <input type="hidden" name="remit_date" value="{{ $row->remit_date }}">
                                        <button type="submit" class="btnx primary" {{ $remittanceTableReady ? '' : 'disabled' }}>Mark Remitted</button>
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
(function () {
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
