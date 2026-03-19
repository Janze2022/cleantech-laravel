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
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }} - CleanTech Admin</title>
    <style>
        :root{
            color-scheme: dark;
            --bg:#040816;
            --panel:#081224;
            --panel-soft:#0d1830;
            --panel-border:rgba(148, 163, 184, 0.16);
            --text:#f8fafc;
            --muted:rgba(226, 232, 240, 0.68);
            --accent:#38bdf8;
            --accent-soft:rgba(56, 189, 248, 0.16);
            --good:#86efac;
            --warn:#fdba74;
        }

        *{
            box-sizing:border-box;
        }

        html{
            scrollbar-color:#1e3a5f #07111f;
            scrollbar-width:thin;
        }

        body{
            margin:0;
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            background:
                radial-gradient(circle at top left, rgba(56, 189, 248, 0.1), transparent 26rem),
                linear-gradient(180deg, #050914 0%, #020611 100%);
            color:var(--text);
            min-height:100vh;
        }

        body::-webkit-scrollbar{
            width:10px;
        }

        body::-webkit-scrollbar-track{
            background:#07111f;
        }

        body::-webkit-scrollbar-thumb{
            background:#1e3a5f;
            border-radius:999px;
        }

        .print-app{
            width:min(1120px, calc(100% - 2rem));
            margin:1.2rem auto;
            display:flex;
            flex-direction:column;
            gap:1rem;
        }

        .sheet,
        .table-shell,
        .empty-shell{
            background:linear-gradient(180deg, rgba(8,18,36,.97), rgba(3,8,20,.99));
            border:1px solid var(--panel-border);
            border-radius:24px;
            box-shadow:0 24px 48px rgba(0, 0, 0, 0.26);
        }

        .sheet{
            padding:1.1rem;
        }

        .sheet-head{
            display:flex;
            align-items:flex-start;
            justify-content:space-between;
            gap:1rem;
            flex-wrap:wrap;
            margin-bottom:1rem;
        }

        .sheet-title{
            margin:0;
            font-size:clamp(1.45rem, 2vw, 1.9rem);
            font-weight:900;
            letter-spacing:-0.02em;
        }

        .sheet-subtitle{
            margin:.3rem 0 0;
            color:var(--muted);
            font-size:.96rem;
        }

        .header-actions{
            display:flex;
            gap:.65rem;
            flex-wrap:wrap;
        }

        .action-btn{
            appearance:none;
            border:1px solid rgba(56, 189, 248, 0.24);
            background:var(--accent-soft);
            color:#fff;
            min-height:44px;
            padding:.7rem 1rem;
            border-radius:14px;
            font-weight:800;
            font-size:.95rem;
            text-decoration:none;
            display:inline-flex;
            align-items:center;
            justify-content:center;
            gap:.4rem;
            cursor:pointer;
        }

        .action-btn.secondary{
            background:rgba(255,255,255,.03);
            border-color:rgba(255,255,255,.1);
        }

        .toolbar{
            display:grid;
            grid-template-columns: minmax(0, 1.5fr) minmax(0, 1.5fr) minmax(0, 1.2fr) auto;
            gap:.8rem;
            align-items:end;
        }

        .field{
            display:flex;
            flex-direction:column;
            gap:.35rem;
        }

        .field label{
            color:var(--muted);
            font-size:.74rem;
            font-weight:800;
            letter-spacing:.08em;
            text-transform:uppercase;
        }

        .field input,
        .field select{
            width:100%;
            min-height:44px;
            border-radius:14px;
            border:1px solid rgba(148, 163, 184, 0.14);
            background:#050c19;
            color:var(--text);
            padding:.75rem .9rem;
            font-size:.96rem;
            color-scheme:dark;
        }

        .field select option{
            background:#081224;
            color:#f8fafc;
        }

        .toolbar-actions{
            display:flex;
            gap:.65rem;
            flex-wrap:wrap;
        }

        .toolbar-actions .action-btn{
            min-width:142px;
        }

        .summary-grid{
            margin-top:1rem;
            display:grid;
            grid-template-columns:repeat(4, minmax(0, 1fr));
            gap:.75rem;
        }

        .summary-box{
            background:rgba(255,255,255,.03);
            border:1px solid rgba(148, 163, 184, 0.1);
            border-radius:18px;
            padding:.9rem 1rem;
        }

        .summary-label{
            color:var(--muted);
            font-size:.74rem;
            font-weight:800;
            letter-spacing:.08em;
            text-transform:uppercase;
        }

        .summary-value{
            margin-top:.32rem;
            font-size:clamp(1.15rem, 1.8vw, 1.5rem);
            font-weight:900;
            line-height:1.15;
        }

        .table-shell{
            padding:1rem;
        }

        .table-head{
            display:flex;
            align-items:center;
            justify-content:space-between;
            gap:.75rem;
            margin-bottom:.8rem;
            flex-wrap:wrap;
        }

        .table-title{
            margin:0;
            font-size:1.08rem;
            font-weight:900;
        }

        .table-note{
            color:var(--muted);
            font-size:.88rem;
        }

        .print-table{
            width:100%;
            border-collapse:collapse;
            table-layout:fixed;
        }

        .print-table thead th{
            color:var(--muted);
            font-size:.72rem;
            font-weight:800;
            letter-spacing:.08em;
            text-transform:uppercase;
            padding:.62rem .55rem;
            text-align:left;
            border-bottom:1px solid rgba(148, 163, 184, 0.14);
        }

        .print-table tbody td,
        .print-table tfoot td{
            padding:.72rem .55rem;
            border-bottom:1px solid rgba(148, 163, 184, 0.1);
            vertical-align:top;
            font-size:.92rem;
        }

        .print-table tfoot td{
            font-weight:800;
        }

        .cell-compact{
            white-space:nowrap;
        }

        .provider-name{
            display:block;
            font-size:1rem;
            font-weight:900;
            margin-bottom:.18rem;
        }

        .provider-meta,
        .provider-services{
            color:var(--muted);
            font-size:.82rem;
            line-height:1.45;
        }

        .status-pill{
            display:inline-flex;
            align-items:center;
            border-radius:999px;
            padding:.38rem .7rem;
            font-size:.82rem;
            font-weight:800;
            border:1px solid transparent;
        }

        .status-pill.good{
            color:#062a15;
            background:rgba(134, 239, 172, 0.92);
            border-color:rgba(134, 239, 172, 1);
        }

        .status-pill.warn{
            color:#2d1400;
            background:rgba(253, 186, 116, 0.92);
            border-color:rgba(253, 186, 116, 1);
        }

        .mobile-cards{
            display:none;
            flex-direction:column;
            gap:.8rem;
        }

        .print-card{
            background:rgba(255,255,255,.03);
            border:1px solid rgba(148, 163, 184, 0.1);
            border-radius:18px;
            padding:.95rem;
        }

        .print-card-head{
            display:flex;
            align-items:flex-start;
            justify-content:space-between;
            gap:.7rem;
            margin-bottom:.8rem;
        }

        .print-card-grid{
            display:grid;
            grid-template-columns:repeat(2, minmax(0, 1fr));
            gap:.7rem;
        }

        .mini-label{
            color:var(--muted);
            font-size:.72rem;
            font-weight:800;
            letter-spacing:.08em;
            text-transform:uppercase;
            margin-bottom:.2rem;
        }

        .mini-value{
            font-size:.92rem;
            font-weight:700;
            line-height:1.45;
        }

        .empty-shell{
            padding:1rem 1.05rem;
            font-size:.98rem;
            color:var(--muted);
        }

        .foot-note{
            margin-top:.85rem;
            color:var(--muted);
            font-size:.82rem;
            line-height:1.45;
        }

        @media (max-width: 980px){
            .toolbar{
                grid-template-columns:repeat(2, minmax(0, 1fr));
            }

            .toolbar-actions{
                grid-column:1 / -1;
            }

            .summary-grid{
                grid-template-columns:repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 760px){
            .print-app{
                width:min(100% - 1rem, 720px);
                margin:.6rem auto 1rem;
            }

            .sheet,
            .table-shell,
            .empty-shell{
                border-radius:20px;
                padding:.9rem;
            }

            .toolbar,
            .summary-grid,
            .print-card-grid{
                grid-template-columns:1fr;
            }

            .header-actions,
            .toolbar-actions{
                width:100%;
            }

            .header-actions .action-btn,
            .toolbar-actions .action-btn{
                width:100%;
            }

            .desktop-table{
                display:none;
            }

            .mobile-cards{
                display:flex;
            }
        }

        @page{
            size:A4 landscape;
            margin:10mm;
        }

        @media print{
            body{
                background:#fff !important;
                color:#0f172a !important;
                min-height:auto;
            }

            .print-app{
                width:100%;
                margin:0;
                gap:.45rem;
            }

            .sheet,
            .table-shell,
            .empty-shell{
                background:#fff !important;
                color:#0f172a !important;
                border:1px solid #cbd5e1;
                box-shadow:none;
                border-radius:0;
                padding:.55rem .7rem;
            }

            .header-actions,
            .toolbar,
            .mobile-cards{
                display:none !important;
            }

            .sheet-head{
                margin-bottom:.45rem;
            }

            .sheet-title{
                font-size:18pt;
            }

            .sheet-subtitle,
            .summary-label,
            .table-note,
            .provider-meta,
            .provider-services,
            .foot-note,
            .mini-label{
                color:#475569 !important;
            }

            .summary-grid{
                margin-top:.55rem;
                grid-template-columns:repeat(4, minmax(0, 1fr));
                gap:.35rem;
            }

            .summary-box{
                background:#f8fafc !important;
                border:1px solid #dbe4ee;
                border-radius:10px;
                padding:.45rem .55rem;
                break-inside:avoid;
            }

            .summary-value{
                font-size:12.5pt;
                color:#0f172a !important;
            }

            .table-shell{
                padding:.55rem .7rem;
            }

            .table-head{
                margin-bottom:.35rem;
            }

            .table-title{
                font-size:12pt;
                color:#0f172a !important;
            }

            .desktop-table{
                display:block !important;
            }

            .print-table{
                font-size:9pt;
            }

            .print-table th,
            .print-table td{
                padding:.3rem .28rem !important;
                border-bottom:1px solid #dbe4ee !important;
                color:#0f172a !important;
            }

            .print-table th{
                color:#475569 !important;
                font-size:7.6pt;
            }

            .provider-name{
                font-size:9.5pt;
                color:#0f172a !important;
            }

            .provider-meta,
            .provider-services{
                font-size:7.6pt;
            }

            .status-pill{
                padding:.18rem .45rem;
                font-size:7.5pt;
                color:#0f172a !important;
                background:#e2e8f0 !important;
                border-color:#cbd5e1 !important;
            }

            .status-pill.good{
                background:#dcfce7 !important;
                border-color:#86efac !important;
            }

            .status-pill.warn{
                background:#ffedd5 !important;
                border-color:#fdba74 !important;
            }

            .foot-note{
                margin-top:.45rem;
                font-size:7.6pt;
            }
        }
    </style>
</head>
<body>
    <div class="print-app">
        <section class="sheet">
            <div class="sheet-head">
                <div>
                    <h1 class="sheet-title">{{ $title }}</h1>
                    <p class="sheet-subtitle">
                        {{ $subtitle }}
                        @if($selectedProvider)
                            | {{ $selectedProvider->name }}
                        @else
                            | All approved providers
                        @endif
                    </p>
                </div>

                <div class="header-actions">
                    <a href="{{ route('admin.earnings') }}" class="action-btn secondary">Back to Earnings</a>
                    <button type="button" class="action-btn" onclick="window.print()">Print This Page</button>
                </div>
            </div>

            <form method="GET" action="{{ route('admin.earnings.print') }}" class="toolbar">
                <div class="field">
                    <label for="printPeriod">Period</label>
                    <select id="printPeriod" name="period">
                        <option value="daily" {{ $period === 'daily' ? 'selected' : '' }}>Daily</option>
                        <option value="monthly" {{ $period === 'monthly' ? 'selected' : '' }}>Monthly</option>
                    </select>
                </div>

                <div class="field">
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

                <div class="field" id="dailyDateControl" style="{{ $period === 'monthly' ? 'display:none;' : '' }}">
                    <label for="printDate">Date</label>
                    <input id="printDate" type="date" name="date" value="{{ $selectedDate }}">
                </div>

                <div class="field" id="monthlyDateControl" style="{{ $period === 'monthly' ? '' : 'display:none;' }}">
                    <label for="printMonth">Month</label>
                    <input id="printMonth" type="month" name="month" value="{{ $selectedMonth }}">
                </div>

                <div class="toolbar-actions">
                    <button type="submit" class="action-btn">Refresh List</button>
                </div>
            </form>

            <div class="summary-grid">
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
                    <div class="summary-label">Outstanding</div>
                    <div class="summary-value">PHP {{ number_format((float) ($totals['outstanding_amount'] ?? 0), 2) }}</div>
                </div>
            </div>
        </section>

        @if($printRows->isEmpty())
            <section class="empty-shell">
                No approved provider remittance rows were found for this selection.
            </section>
        @else
            <section class="table-shell">
                <div class="table-head">
                    <div>
                        <h2 class="table-title">Provider Remittance Rows</h2>
                    </div>
                </div>

                <div class="desktop-table">
                    <table class="print-table">
                        <thead>
                            <tr>
                                <th style="width: 11%;">Earned day</th>
                                <th style="width: 27%;">Provider</th>
                                <th style="width: 8%;">Jobs</th>
                                <th style="width: 10%;">Gross</th>
                                <th style="width: 10%;">Remitted</th>
                                <th style="width: 11%;">Outstanding</th>
                                <th style="width: 13%;">Recorded on</th>
                                <th style="width: 10%;">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($printRows as $row)
                                <tr>
                                    <td class="cell-compact">{{ \Carbon\Carbon::parse($row->remit_date)->format('M d, Y') }}</td>
                                    <td>
                                        <span class="provider-name">{{ $row->provider_name }}</span>
                                        @if(!empty($row->provider_phone))
                                            <div class="provider-meta">{{ $row->provider_phone }}</div>
                                        @endif
                                        <div class="provider-services">{{ $row->service_names !== '' ? $row->service_names : 'Service list unavailable' }}</div>
                                    </td>
                                    <td class="cell-compact">{{ number_format((int) $row->total_bookings) }}</td>
                                    <td class="cell-compact">PHP {{ number_format((float) $row->gross_amount, 2) }}</td>
                                    <td class="cell-compact">PHP {{ number_format($row->is_remitted ? (float) $row->gross_amount : 0, 2) }}</td>
                                    <td class="cell-compact">PHP {{ number_format(!$row->is_remitted ? (float) $row->gross_amount : 0, 2) }}</td>
                                    <td>
                                        @if($row->is_remitted && !empty($row->remitted_at))
                                            {{ \Carbon\Carbon::parse($row->remitted_at)->format('M d, Y h:i A') }}
                                        @else
                                            Waiting for payment
                                        @endif
                                    </td>
                                    <td>
                                        <span class="status-pill {{ $row->is_remitted ? 'good' : 'warn' }}">
                                            {{ $row->is_remitted ? 'Remitted' : 'Outstanding' }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <td>Total</td>
                                <td>{{ number_format((int) ($totals['providers_count'] ?? 0)) }} provider{{ (int) ($totals['providers_count'] ?? 0) === 1 ? '' : 's' }}</td>
                                <td>{{ number_format((int) ($totals['total_bookings'] ?? 0)) }}</td>
                                <td>PHP {{ number_format((float) ($totals['gross_amount'] ?? 0), 2) }}</td>
                                <td>PHP {{ number_format((float) ($totals['remitted_amount'] ?? 0), 2) }}</td>
                                <td>PHP {{ number_format((float) ($totals['outstanding_amount'] ?? 0), 2) }}</td>
                                <td>{{ number_format((int) $printRows->where('is_remitted', true)->count()) }} remitted row{{ $printRows->where('is_remitted', true)->count() === 1 ? '' : 's' }}</td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <div class="mobile-cards">
                    @foreach($printRows as $row)
                        <article class="print-card">
                            <div class="print-card-head">
                                <div>
                                    <span class="provider-name">{{ $row->provider_name }}</span>
                                    @if(!empty($row->provider_phone))
                                        <div class="provider-meta">{{ $row->provider_phone }}</div>
                                    @endif
                                </div>
                                <span class="status-pill {{ $row->is_remitted ? 'good' : 'warn' }}">
                                    {{ $row->is_remitted ? 'Remitted' : 'Outstanding' }}
                                </span>
                            </div>

                            <div class="provider-services" style="margin-bottom:.8rem;">
                                {{ $row->service_names !== '' ? $row->service_names : 'Service list unavailable' }}
                            </div>

                            <div class="print-card-grid">
                                <div>
                                    <div class="mini-label">Earned day</div>
                                    <div class="mini-value">{{ \Carbon\Carbon::parse($row->remit_date)->format('M d, Y') }}</div>
                                </div>
                                <div>
                                    <div class="mini-label">Jobs</div>
                                    <div class="mini-value">{{ number_format((int) $row->total_bookings) }}</div>
                                </div>
                                <div>
                                    <div class="mini-label">Gross</div>
                                    <div class="mini-value">PHP {{ number_format((float) $row->gross_amount, 2) }}</div>
                                </div>
                                <div>
                                    <div class="mini-label">Outstanding</div>
                                    <div class="mini-value">PHP {{ number_format(!$row->is_remitted ? (float) $row->gross_amount : 0, 2) }}</div>
                                </div>
                                <div style="grid-column:1 / -1;">
                                    <div class="mini-label">Recorded on</div>
                                    <div class="mini-value">
                                        @if($row->is_remitted && !empty($row->remitted_at))
                                            {{ \Carbon\Carbon::parse($row->remitted_at)->format('M d, Y h:i A') }}
                                        @else
                                            Waiting for payment
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            </section>
        @endif
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
</body>
</html>
