@extends('admin.layouts.app')

@section('title', 'Print Remittance')

@section('content')

@php
    $printRows = collect($printRows ?? []);
    $totals = $totals ?? [
        'providers_count' => 0,
        'entry_count' => 0,
        'gross_amount' => 0,
        'remitted_amount' => 0,
        'outstanding_amount' => 0,
        'total_bookings' => 0,
    ];
@endphp

<style>
.print-page{
    max-width: 1100px;
    margin: 0 auto;
    color: #e5e7eb;
}

.print-stack{
    display:flex;
    flex-direction:column;
    gap:1rem;
}

.print-shell,
.print-table-shell,
.print-empty{
    background: linear-gradient(180deg, rgba(7,18,37,.96), rgba(2,6,23,.98));
    border:1px solid rgba(255,255,255,.08);
    border-radius:22px;
    box-shadow: 0 20px 40px rgba(0,0,0,.28);
    padding:1.15rem;
}

.print-head{
    display:flex;
    align-items:flex-start;
    justify-content:space-between;
    gap:1rem;
    flex-wrap:wrap;
}

.print-title{
    margin:0;
    font-size:1.25rem;
    font-weight:900;
}

.print-subtitle{
    margin:.35rem 0 0;
    color:rgba(255,255,255,.58);
    font-size:.9rem;
}

.print-actions{
    display:flex;
    flex-wrap:wrap;
    gap:.7rem;
}

.print-btn,
.print-ghost{
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

.print-btn{
    background: rgba(56,189,248,.14);
    border-color: rgba(56,189,248,.25);
    color:#fff;
}

.print-ghost{
    background: rgba(255,255,255,.03);
    border-color: rgba(255,255,255,.1);
    color:#fff;
}

.print-form{
    display:grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap:.75rem;
    margin-top:1rem;
}

.print-control{
    display:flex;
    flex-direction:column;
    gap:.35rem;
}

.print-control label{
    color:rgba(255,255,255,.58);
    font-size:.75rem;
    font-weight:700;
    letter-spacing:.08em;
    text-transform:uppercase;
}

.print-control input,
.print-control select{
    min-height:42px;
    padding:.68rem .82rem;
    border-radius:12px;
    border:1px solid rgba(255,255,255,.1);
    background: rgba(2,6,23,.92);
    color:#fff;
    color-scheme:dark;
}

.print-control select option{
    background:#07111f;
    color:#f8fafc;
}

.print-summary{
    display:grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap:.75rem;
    margin-top:1rem;
}

.summary-box{
    padding:.95rem 1rem;
    border-radius:16px;
    border:1px solid rgba(255,255,255,.06);
    background: rgba(255,255,255,.03);
}

.summary-label{
    color:rgba(255,255,255,.58);
    font-size:.75rem;
    font-weight:700;
    letter-spacing:.08em;
    text-transform:uppercase;
}

.summary-value{
    margin-top:.35rem;
    font-size:1.35rem;
    font-weight:900;
}

.print-table{
    width:100%;
    margin-top:1rem;
    border-collapse: collapse;
}

.print-table th,
.print-table td{
    padding:.8rem .72rem;
    border-bottom:1px solid rgba(255,255,255,.08);
    vertical-align:top;
}

.print-table th{
    color:rgba(255,255,255,.58);
    font-size:.74rem;
    font-weight:800;
    letter-spacing:.08em;
    text-transform:uppercase;
    white-space:nowrap;
}

.provider-col strong{
    display:block;
    font-size:.92rem;
}

.provider-col span{
    color:rgba(255,255,255,.58);
    font-size:.8rem;
}

.status-text{
    font-weight:800;
}

.status-text.good{
    color:#86efac;
}

.status-text.warn{
    color:#fdba74;
}

.print-footer{
    margin-top:1rem;
    color:rgba(255,255,255,.58);
    font-size:.82rem;
}

@media (max-width: 991.98px){
    .print-form,
    .print-summary{
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}

@media (max-width: 767.98px){
    .print-form,
    .print-summary{
        grid-template-columns: 1fr;
    }

    .print-table{
        display:block;
        overflow:auto;
    }

    .print-actions .print-btn,
    .print-actions .print-ghost{
        width:100%;
    }
}

@media print{
    .admin-sidebar,
    .admin-topbar,
    .print-actions,
    .print-form{
        display:none !important;
    }

    .admin-content{
        margin-left:0 !important;
        padding:0 !important;
    }

    .print-shell,
    .print-table-shell,
    .print-empty{
        box-shadow:none;
        border-color:#cbd5e1;
        background:#fff;
        color:#0f172a;
    }

    .print-title,
    .summary-value,
    .provider-col strong,
    .status-text{
        color:#0f172a !important;
    }

    .print-subtitle,
    .summary-label,
    .provider-col span,
    .print-footer,
    .print-table th{
        color:#475569 !important;
    }

    .print-table th,
    .print-table td{
        border-bottom:1px solid #cbd5e1;
    }
}
</style>

<div class="print-page">
    <div class="print-stack">

        <section class="print-shell">
            <div class="print-head">
                <div>
                    <h1 class="print-title">{{ $title }}</h1>
                    <p class="print-subtitle">
                        {{ $subtitle }}
                        @if($selectedProvider)
                            | {{ $selectedProvider->name }}
                        @else
                            | All approved providers
                        @endif
                    </p>
                </div>

                <div class="print-actions">
                    <a href="{{ route('admin.earnings') }}" class="print-ghost">
                        <i class="fa fa-arrow-left"></i>
                        <span>Back to Earnings</span>
                    </a>

                    <button type="button" class="print-btn" onclick="window.print()">
                        <i class="fa fa-print"></i>
                        <span>Print This Page</span>
                    </button>
                </div>
            </div>

            <form method="GET" action="{{ route('admin.earnings.print') }}" class="print-form">
                <div class="print-control">
                    <label for="printPeriod">Period</label>
                    <select id="printPeriod" name="period">
                        <option value="daily" {{ $period === 'daily' ? 'selected' : '' }}>Daily</option>
                        <option value="monthly" {{ $period === 'monthly' ? 'selected' : '' }}>Monthly</option>
                    </select>
                </div>

                <div class="print-control">
                    <label for="printProviderId">Provider</label>
                    <select id="printProviderId" name="provider_id">
                        <option value="0">All approved providers</option>
                        @foreach($providerOptions as $providerOption)
                            <option value="{{ $providerOption->id }}" {{ $providerId === $providerOption->id ? 'selected' : '' }}>
                                {{ $providerOption->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="print-control" id="dailyDateControl" style="{{ $period === 'monthly' ? 'display:none;' : '' }}">
                    <label for="printDate">Date</label>
                    <input id="printDate" type="date" name="date" value="{{ $selectedDate }}">
                </div>

                <div class="print-control" id="monthlyDateControl" style="{{ $period === 'monthly' ? '' : 'display:none;' }}">
                    <label for="printMonth">Month</label>
                    <input id="printMonth" type="month" name="month" value="{{ $selectedMonth }}">
                </div>

                <div class="print-actions" style="align-items:flex-end;">
                    <button type="submit" class="print-btn">
                        <i class="fa fa-rotate"></i>
                        <span>Refresh List</span>
                    </button>
                </div>
            </form>

            <div class="print-summary">
                <div class="summary-box">
                    <div class="summary-label">Entries</div>
                    <div class="summary-value">{{ number_format((int) ($totals['entry_count'] ?? 0)) }}</div>
                </div>

                <div class="summary-box">
                    <div class="summary-label">Providers</div>
                    <div class="summary-value">{{ number_format((int) ($totals['providers_count'] ?? 0)) }}</div>
                </div>

                <div class="summary-box">
                    <div class="summary-label">Gross total</div>
                    <div class="summary-value">PHP {{ number_format((float) ($totals['gross_amount'] ?? 0), 2) }}</div>
                </div>

                <div class="summary-box">
                    <div class="summary-label">Outstanding total</div>
                    <div class="summary-value">PHP {{ number_format((float) ($totals['outstanding_amount'] ?? 0), 2) }}</div>
                </div>
            </div>
        </section>

        @if($printRows->isEmpty())
            <section class="print-empty">
                No approved provider remittance rows were found for this selection.
            </section>
        @else
            <section class="print-table-shell">
                <table class="print-table">
                    <thead>
                        <tr>
                            <th>Earned day</th>
                            <th>Provider</th>
                            <th>Bookings</th>
                            <th>Gross</th>
                            <th>Remitted</th>
                            <th>Outstanding</th>
                            <th>Recorded on</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($printRows as $row)
                            <tr>
                                <td>{{ \Carbon\Carbon::parse($row->remit_date)->format('M d, Y') }}</td>
                                <td class="provider-col">
                                    <strong>{{ $row->provider_name }}</strong>
                                    @if(!empty($row->provider_phone))
                                        <span>{{ $row->provider_phone }}</span>
                                    @endif
                                    <span>{{ $row->service_names !== '' ? $row->service_names : 'Service list unavailable' }}</span>
                                </td>
                                <td>{{ number_format((int) $row->total_bookings) }}</td>
                                <td>PHP {{ number_format((float) $row->gross_amount, 2) }}</td>
                                <td>PHP {{ number_format($row->is_remitted ? (float) $row->gross_amount : 0, 2) }}</td>
                                <td>PHP {{ number_format(!$row->is_remitted ? (float) $row->gross_amount : 0, 2) }}</td>
                                <td>
                                    @if($row->is_remitted && !empty($row->remitted_at))
                                        {{ \Carbon\Carbon::parse($row->remitted_at)->format('M d, Y h:i A') }}
                                    @else
                                        Waiting for payment
                                    @endif
                                </td>
                                <td>
                                    <span class="status-text {{ $row->is_remitted ? 'good' : 'warn' }}">
                                        {{ $row->is_remitted ? 'Remitted' : 'Outstanding' }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <th>Total</th>
                            <th>{{ number_format((int) ($totals['providers_count'] ?? 0)) }} provider{{ (int) ($totals['providers_count'] ?? 0) === 1 ? '' : 's' }}</th>
                            <th>{{ number_format((int) ($totals['total_bookings'] ?? 0)) }}</th>
                            <th>PHP {{ number_format((float) ($totals['gross_amount'] ?? 0), 2) }}</th>
                            <th>PHP {{ number_format((float) ($totals['remitted_amount'] ?? 0), 2) }}</th>
                            <th>PHP {{ number_format((float) ($totals['outstanding_amount'] ?? 0), 2) }}</th>
                            <th>{{ number_format((int) $printRows->where('is_remitted', true)->count()) }} remitted row{{ $printRows->where('is_remitted', true)->count() === 1 ? '' : 's' }}</th>
                            <th></th>
                        </tr>
                    </tfoot>
                </table>

                <div class="print-footer">
                    Approved providers only. The print list includes earnings dated inside the selected period, plus any rows actually marked remitted inside that same period.
                </div>
            </section>
        @endif

    </div>
</div>

<script>
(function(){
    const periodSelect = document.getElementById('printPeriod');
    const dailyControl = document.getElementById('dailyDateControl');
    const monthlyControl = document.getElementById('monthlyDateControl');

    function syncControls() {
        const isMonthly = periodSelect && periodSelect.value === 'monthly';
        if (dailyControl) dailyControl.style.display = isMonthly ? 'none' : '';
        if (monthlyControl) monthlyControl.style.display = isMonthly ? '' : 'none';
    }

    periodSelect?.addEventListener('change', syncControls);
    syncControls();
})();
</script>

@endsection
