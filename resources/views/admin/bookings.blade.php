@extends('admin.layouts.app')

@section('title', 'Admin Bookings')

@section('content')
@php
    use Carbon\Carbon;

    $tz = config('app.timezone') ?? 'Asia/Manila';
    $today = Carbon::now($tz)->toDateString();

    $currentBookings = $currentBookings ?? collect();
    $bookingHistory  = $bookingHistory ?? collect();
    $customers       = $customers ?? collect();
    $providers       = $providers ?? collect();
    $services        = $services ?? collect();
    $serviceOptions  = $serviceOptions ?? collect();
    $viewErrors      = isset($errors) ? $errors : new \Illuminate\Support\ViewErrorBag();
    $adjustmentSummary = $adjustmentSummary ?? [
        'pending' => 0,
        'accepted' => 0,
        'rejected' => 0,
    ];

    if (!function_exists('booking_time_12h')) {
        function booking_time_12h($time) {
            if (!$time) {
                return '-';
            }

            try {
                return Carbon::createFromFormat('H:i:s', $time)->format('g:i A');
            } catch (\Throwable $e) {
                try {
                    return Carbon::createFromFormat('H:i', $time)->format('g:i A');
                } catch (\Throwable $e) {
                    return $time;
                }
            }
        }
    }
@endphp

<style>
:root{
    --bg:#020617;
    --panel:#0f172a;
    --panel-2:#111827;
    --soft:#172033;
    --line:rgba(255,255,255,.08);
    --line-strong:rgba(255,255,255,.12);
    --text:#f8fafc;
    --muted:#94a3b8;
    --accent:#3b82f6;
    --accent-2:#2563eb;
    --success:#22c55e;
    --danger:#ef4444;
    --warn:#f59e0b;
    --shadow:0 10px 30px rgba(0,0,0,.22);
    --radius:18px;
    --radius-sm:14px;
    --panel-height:calc(100vh - 270px);
}

*{ box-sizing:border-box; }

.admin-bookings{
    padding:16px;
    color:var(--text);
}

.page-head{
    display:flex;
    justify-content:space-between;
    align-items:center;
    gap:12px;
    margin-bottom:14px;
    flex-wrap:wrap;
}
.page-head-left{
    min-width:0;
}
.page-head h2{
    margin:0;
    font-size:1.45rem;
    font-weight:900;
    letter-spacing:-.03em;
    line-height:1.1;
}
.page-head .meta{
    margin-top:4px;
    color:var(--muted);
    font-weight:700;
    font-size:.84rem;
}

.head-actions{
    display:flex;
    gap:8px;
    flex-wrap:wrap;
}

.btnx{
    appearance:none;
    border:none;
    outline:none;
    cursor:pointer;
    text-decoration:none;
    display:inline-flex;
    align-items:center;
    justify-content:center;
    gap:8px;
    padding:10px 13px;
    border-radius:12px;
    font-weight:800;
    font-size:.86rem;
    color:var(--text);
    background:rgba(255,255,255,.03);
    border:1px solid var(--line);
    transition:.18s ease;
    min-height:40px;
    white-space:nowrap;
}
.btnx:hover{
    transform:translateY(-1px);
    border-color:rgba(255,255,255,.16);
}
.btnx.primary{
    background:linear-gradient(180deg, #2563eb, #1d4ed8);
    border-color:rgba(59,130,246,.48);
}
.btnx.success{
    background:linear-gradient(180deg, rgba(34,197,94,.18), rgba(34,197,94,.1));
    border-color:rgba(34,197,94,.3);
    color:#dcfce7;
}
.btnx.danger{
    color:#fecaca;
    border-color:rgba(239,68,68,.35);
    background:rgba(239,68,68,.07);
}
.btnx.ghost{
    background:rgba(255,255,255,.02);
}
.btnx:disabled,
.status-select:disabled,
.selectx:disabled,
.inputx:disabled,
.textareax:disabled{
    opacity:.55;
    cursor:not-allowed;
    transform:none !important;
}

.kpis{
    display:grid;
    grid-template-columns:repeat(4, minmax(0,1fr));
    gap:12px;
    margin-bottom:14px;
}
.adjustment-kpis{
    display:grid;
    grid-template-columns:repeat(3, minmax(0,1fr));
    gap:12px;
    margin-bottom:14px;
}
.kpi{
    background:linear-gradient(180deg, rgba(255,255,255,.045), rgba(255,255,255,.02));
    border:1px solid var(--line);
    border-radius:16px;
    padding:14px;
    box-shadow:var(--shadow);
}
.kpi .label{
    color:var(--muted);
    font-size:.76rem;
    font-weight:800;
    margin-bottom:8px;
}
.kpi .value{
    font-size:1.45rem;
    font-weight:900;
    line-height:1;
}
.kpi.adjustment .value{
    font-size:1.15rem;
}
.kpi.adjustment.pending{
    border-color:rgba(59,130,246,.2);
}
.kpi.adjustment.accepted{
    border-color:rgba(34,197,94,.2);
}
.kpi.adjustment.rejected{
    border-color:rgba(239,68,68,.2);
}

.notice{
    border:1px solid var(--line);
    border-radius:14px;
    padding:12px 14px;
    margin-bottom:14px;
    font-weight:700;
    background:rgba(255,255,255,.03);
}
.notice.success{ border-color:rgba(34,197,94,.25); }
.notice.danger{ border-color:rgba(239,68,68,.25); }
.notice ul{
    margin:8px 0 0 18px;
    color:var(--muted);
}

.grid-2{
    display:grid;
    grid-template-columns:minmax(0,1fr) minmax(0,1fr);
    gap:14px;
    align-items:start;
}

.panel{
    background:linear-gradient(180deg, rgba(15,23,42,.98), rgba(17,24,39,.98));
    border:1px solid var(--line);
    border-radius:20px;
    min-width:0;
    overflow:hidden;
    box-shadow:var(--shadow);
    display:flex;
    flex-direction:column;
    height:var(--panel-height);
}

.panel-toolbar{
    position:sticky;
    top:0;
    z-index:5;
    padding:14px 14px 12px;
    background:linear-gradient(180deg, rgba(15,23,42,.98), rgba(15,23,42,.95));
    border-bottom:1px solid rgba(255,255,255,.05);
    backdrop-filter:blur(8px);
}

.panel-head{
    display:flex;
    justify-content:space-between;
    align-items:flex-start;
    gap:10px;
    margin-bottom:10px;
}
.panel-title{
    margin:0;
    font-size:1.02rem;
    font-weight:900;
    line-height:1.15;
}
.panel-sub{
    color:var(--muted);
    font-size:.8rem;
    font-weight:700;
    margin-top:3px;
}

.count-pill{
    min-width:32px;
    height:32px;
    padding:0 10px;
    border-radius:999px;
    border:1px solid rgba(59,130,246,.35);
    display:flex;
    align-items:center;
    justify-content:center;
    font-weight:900;
    background:rgba(59,130,246,.08);
    font-size:.82rem;
}

.search{
    width:100%;
    padding:11px 13px;
    border-radius:12px;
    border:1px solid var(--line);
    background:#0f172a;
    color:var(--text);
    font-weight:700;
    outline:none;
    font-size:.88rem;
}
.search::placeholder{ color:rgba(148,163,184,.8); }
.search:focus{ border-color:rgba(59,130,246,.45); }

.filter-toggle{
    width:100%;
    margin-top:8px;
}

.filter-box{
    display:none;
    margin-top:10px;
    padding:10px;
    border:1px solid rgba(255,255,255,.06);
    border-radius:14px;
    background:rgba(255,255,255,.02);
}
.filter-box.active{
    display:block;
}
.filter-grid{
    display:grid;
    grid-template-columns:repeat(2, minmax(0,1fr));
    gap:8px;
}
.filter-actions{
    display:flex;
    gap:8px;
    flex-wrap:wrap;
    margin-top:10px;
}

.panel-scroll{
    flex:1;
    min-height:0;
    overflow:auto;
    padding:12px 14px 14px;
    scrollbar-width:thin;
    scrollbar-color:rgba(148,163,184,.35) transparent;
    -webkit-overflow-scrolling:touch;
    overscroll-behavior:contain;
}
.panel-scroll::-webkit-scrollbar{
    width:8px;
    height:8px;
}
.panel-scroll::-webkit-scrollbar-thumb{
    background:rgba(148,163,184,.25);
    border-radius:999px;
}
.panel-scroll::-webkit-scrollbar-track{
    background:transparent;
}

.booking-list{
    display:flex;
    flex-direction:column;
    gap:10px;
}

.booking-card{
    background:linear-gradient(180deg, rgba(255,255,255,.035), rgba(255,255,255,.018));
    border:1px solid var(--line);
    border-radius:16px;
    padding:12px;
}

.booking-top{
    display:flex;
    justify-content:space-between;
    align-items:flex-start;
    gap:10px;
    margin-bottom:10px;
}
.booking-ref{
    font-size:.96rem;
    font-weight:900;
    line-height:1.25;
    min-width:0;
}
.booking-ref small{
    display:block;
    color:var(--muted);
    font-size:.74rem;
    font-weight:700;
    margin-top:3px;
}

.badge{
    display:inline-flex;
    align-items:center;
    justify-content:center;
    padding:6px 10px;
    border-radius:999px;
    border:1px solid var(--line);
    font-size:.72rem;
    font-weight:900;
    text-transform:capitalize;
    white-space:nowrap;
    flex-shrink:0;
}
.badge.info{ border-color:rgba(59,130,246,.35); background:rgba(59,130,246,.08); }
.badge.success{ border-color:rgba(34,197,94,.35); background:rgba(34,197,94,.08); }
.badge.warn{ border-color:rgba(245,158,11,.35); background:rgba(245,158,11,.08); }
.badge.danger{ border-color:rgba(239,68,68,.35); background:rgba(239,68,68,.08); }

.booking-meta{
    display:grid;
    grid-template-columns:repeat(2, minmax(0,1fr));
    gap:8px;
    margin-bottom:10px;
}
.meta-box{
    background:rgba(255,255,255,.022);
    border:1px solid rgba(255,255,255,.05);
    border-radius:12px;
    padding:9px 10px;
    min-width:0;
}
.meta-label{
    color:var(--muted);
    font-size:.7rem;
    font-weight:800;
    margin-bottom:4px;
    text-transform:uppercase;
    letter-spacing:.03em;
}
.meta-value{
    color:var(--text);
    font-weight:800;
    line-height:1.35;
    word-break:break-word;
    font-size:.88rem;
}
.meta-value.small{
    font-size:.82rem;
    color:rgba(248,250,252,.9);
}
.booking-note-stack{
    display:flex;
    flex-direction:column;
    gap:8px;
}
.booking-note-line{
    color:rgba(248,250,252,.92);
    font-size:.82rem;
    font-weight:700;
    line-height:1.5;
    word-break:break-word;
}
.booking-note-line strong{
    color:var(--text);
}
.booking-note-row{
    display:flex;
    flex-wrap:wrap;
    gap:8px;
}
.booking-note-chip{
    display:inline-flex;
    align-items:center;
    gap:6px;
    padding:6px 10px;
    border-radius:999px;
    border:1px solid rgba(59,130,246,.2);
    background:rgba(59,130,246,.08);
    color:#dbeafe;
    font-size:.75rem;
    font-weight:900;
}
.booking-note-chip.muted{
    border-color:rgba(255,255,255,.08);
    background:rgba(255,255,255,.03);
    color:var(--muted);
}

.history-summary{
    display:flex;
    justify-content:space-between;
    align-items:flex-start;
    gap:12px;
    padding:10px 12px;
    margin-bottom:10px;
    border:1px solid rgba(255,255,255,.06);
    border-radius:14px;
    background:rgba(255,255,255,.025);
}
.summary-main{
    min-width:0;
}
.summary-title{
    font-size:.96rem;
    font-weight:900;
    line-height:1.25;
    color:var(--text);
    word-break:break-word;
}
.summary-sub{
    margin-top:4px;
    font-size:.8rem;
    color:var(--muted);
    font-weight:700;
    line-height:1.35;
}
.summary-price{
    flex-shrink:0;
    font-size:1rem;
    font-weight:900;
    color:var(--text);
    white-space:nowrap;
}
.compact-meta{
    grid-template-columns:repeat(2, minmax(0,1fr));
}
.full-span{
    grid-column:1 / -1;
}

.booking-actions{
    display:flex;
    flex-wrap:wrap;
    gap:8px;
    align-items:stretch;
}
.booking-actions > .btnx,
.booking-actions > form{
    margin:0;
}

.selectx,
.inputx,
.textareax{
    width:100%;
    padding:10px 12px;
    border-radius:12px;
    border:1px solid var(--line);
    background:#0b1220;
    color:var(--text);
    outline:none;
    font-weight:800;
    font-size:.9rem;
}
.textareax{
    resize:vertical;
    min-height:95px;
}
.selectx:focus,
.inputx:focus,
.textareax:focus{
    border-color:rgba(59,130,246,.45);
}

select.selectx,
select.status-select{
    background-color:#0b1220 !important;
    color:#f8fafc !important;
}
select.selectx option,
select.status-select option{
    background:#0b1220;
    color:#f8fafc;
}

.status-wrap{
    display:grid;
    grid-template-columns:minmax(0,1fr) auto;
    gap:8px;
    align-items:start;
    flex:1 1 230px;
    min-width:210px;
}
.status-select{
    min-width:0;
    padding:10px 12px;
    border-radius:12px;
    border:1px solid var(--line);
    background:#0b1220;
    color:var(--text);
    font-weight:800;
    outline:none;
    min-height:40px;
}
.status-reason{
    grid-column:1 / -1;
    width:100%;
    min-height:78px;
    resize:vertical;
    padding:10px 12px;
    border-radius:12px;
    border:1px solid var(--line);
    background:#0b1220;
    color:var(--text);
    font-weight:700;
    line-height:1.45;
}
.status-reason::placeholder{
    color:rgba(148,163,184,.78);
}
.status-help{
    grid-column:1 / -1;
    color:var(--muted);
    font-size:.76rem;
    font-weight:700;
    margin-top:-2px;
}
.status-wrap[data-cancelled="0"] .status-reason,
.status-wrap[data-cancelled="0"] .status-help{
    display:none;
}

.lock-note{
    width:100%;
    margin-top:4px;
    color:#fca5a5;
    font-size:.76rem;
    font-weight:800;
}

.empty{
    padding:18px 14px;
    border:1px dashed rgba(255,255,255,.12);
    border-radius:16px;
    color:var(--muted);
    font-weight:700;
    text-align:center;
    background:rgba(255,255,255,.02);
}

.modalx{
    display:none;
    position:fixed;
    inset:0;
    background:rgba(0,0,0,.72);
    z-index:9999;
    overflow:auto;
    padding:16px;
}
.modal-panel{
    max-width:920px;
    margin:18px auto;
    background:linear-gradient(180deg, #0f172a, #111827);
    border:1px solid var(--line);
    border-radius:22px;
    padding:16px;
    box-shadow:var(--shadow);
}
.modal-head{
    display:flex;
    justify-content:space-between;
    align-items:center;
    gap:12px;
    margin-bottom:14px;
}
.modal-head h3{
    margin:0;
    font-size:1.05rem;
    font-weight:900;
}

.detail-grid,
.form-grid{
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:10px;
}
.detail-box{
    border:1px solid var(--line);
    background:rgba(255,255,255,.03);
    border-radius:14px;
    padding:11px;
}
.detail-box.full,
.form-group.full{
    grid-column:1 / -1;
}
.detail-label{
    color:var(--muted);
    font-size:.74rem;
    font-weight:800;
    margin-bottom:5px;
}
.detail-value{
    font-weight:800;
    line-height:1.42;
    word-break:break-word;
    font-size:.92rem;
}
.adjustment-detail{
    display:flex;
    flex-direction:column;
    gap:10px;
}
.adjustment-banner{
    display:flex;
    justify-content:space-between;
    align-items:center;
    gap:10px;
    flex-wrap:wrap;
}
.adjustment-pill{
    display:inline-flex;
    align-items:center;
    gap:8px;
    padding:7px 11px;
    border-radius:999px;
    border:1px solid rgba(59,130,246,.24);
    background:rgba(59,130,246,.08);
    font-size:.78rem;
    font-weight:900;
}
.adjustment-pill.muted{
    border-color:rgba(255,255,255,.08);
    background:rgba(255,255,255,.03);
    color:var(--muted);
}
.adjustment-compare{
    display:grid;
    grid-template-columns:repeat(2, minmax(0,1fr));
    gap:10px;
}
.adjustment-panel{
    padding:11px 12px;
    border:1px solid rgba(255,255,255,.06);
    border-radius:14px;
    background:rgba(255,255,255,.022);
}
.adjustment-panel-label{
    color:var(--muted);
    font-size:.72rem;
    font-weight:900;
    margin-bottom:6px;
    text-transform:uppercase;
    letter-spacing:.04em;
}
.adjustment-summary-grid{
    display:grid;
    grid-template-columns:repeat(3, minmax(0,1fr));
    gap:10px;
}
.adjustment-summary-box{
    padding:10px 11px;
    border:1px solid rgba(255,255,255,.06);
    border-radius:12px;
    background:rgba(255,255,255,.02);
}
.adjustment-chip-row{
    display:flex;
    flex-wrap:wrap;
    gap:8px;
}
.adjustment-chip{
    display:inline-flex;
    align-items:center;
    padding:6px 10px;
    border-radius:999px;
    border:1px solid rgba(255,255,255,.08);
    background:rgba(255,255,255,.03);
    font-size:.76rem;
    font-weight:800;
}
.adjustment-note-block{
    padding:10px 11px;
    border:1px solid rgba(255,255,255,.06);
    border-radius:12px;
    background:rgba(255,255,255,.02);
}
.adjustment-note-title{
    color:var(--muted);
    font-size:.72rem;
    font-weight:900;
    margin-bottom:4px;
    text-transform:uppercase;
    letter-spacing:.04em;
}
.adjustment-log-list{
    display:flex;
    flex-direction:column;
    gap:10px;
}
.adjustment-log-item{
    padding:11px 12px;
    border:1px solid rgba(255,255,255,.06);
    border-radius:14px;
    background:rgba(255,255,255,.022);
}
.adjustment-log-head{
    display:flex;
    justify-content:space-between;
    gap:8px;
    flex-wrap:wrap;
    margin-bottom:4px;
}
.adjustment-log-meta{
    color:var(--muted);
    font-size:.76rem;
    font-weight:700;
}
.adjustment-link{
    color:#7dd3fc;
    text-decoration:none;
    font-weight:800;
}
.adjustment-link:hover{
    text-decoration:underline;
}

.form-group{
    display:flex;
    flex-direction:column;
    gap:6px;
}
.form-group label{
    color:rgba(255,255,255,.8);
    font-size:.76rem;
    font-weight:900;
}
.form-actions{
    display:flex;
    justify-content:flex-end;
    flex-wrap:wrap;
    gap:8px;
    margin-top:14px;
}

.hidden-by-filter{
    display:none !important;
}

@media (max-width: 1180px){
    .kpis{ grid-template-columns:repeat(2, minmax(0,1fr)); }
    .adjustment-kpis{ grid-template-columns:repeat(3, minmax(0,1fr)); }
    .grid-2{ grid-template-columns:1fr; }
    .panel{
        height:auto;
        min-height:520px;
        max-height:720px;
    }
}

@media (max-width: 700px){
    :root{
        --panel-height:auto;
    }

    .admin-bookings{ padding:12px; }

    .page-head{
        align-items:stretch;
    }

    .page-head h2{
        font-size:1.2rem;
    }

    .head-actions{
        width:100%;
    }
    .head-actions .btnx{
        flex:1 1 0;
    }

    .kpis{
        grid-template-columns:1fr 1fr;
        gap:10px;
    }
    .adjustment-kpis{
        grid-template-columns:1fr;
        gap:10px;
    }
    .kpi{
        padding:12px;
    }
    .kpi .value{
        font-size:1.2rem;
    }

    .panel{
        min-height:460px;
        max-height:68vh;
        border-radius:18px;
    }

    .panel-toolbar,
    .panel-scroll{
        padding-left:12px;
        padding-right:12px;
    }

    .filter-grid,
    .booking-meta,
    .compact-meta,
    .detail-grid,
    .form-grid,
    .adjustment-compare,
    .adjustment-summary-grid{
        grid-template-columns:1fr;
    }

    .booking-top{
        flex-direction:column;
        align-items:flex-start;
    }

    .history-summary{
        flex-direction:column;
        align-items:flex-start;
    }

    .summary-price{
        white-space:normal;
    }

    .booking-actions{
        flex-direction:column;
        align-items:stretch;
    }

    .status-wrap{
        width:100%;
        min-width:0;
        flex:none;
        grid-template-columns:1fr;
        align-items:stretch;
    }

    .status-select{
        height:50px;
        min-height:50px;
        padding:0 14px;
        font-size:.92rem;
        line-height:1.2;
    }

    .status-wrap .btnx,
    .booking-actions > .btnx,
    .booking-actions > form,
    .booking-actions > form .btnx{
        width:100%;
    }

    .booking-actions > form{
        flex:none;
    }

    .filter-actions .btnx{
        flex:1 1 0;
    }

    .modalx{
        padding:10px;
    }
    .modal-panel{
        padding:14px;
        border-radius:18px;
    }
}

@media (max-width: 480px){
    .kpis{
        grid-template-columns:1fr;
    }

    .btnx{
        font-size:.82rem;
        padding:9px 12px;
    }

    .panel-title{
        font-size:.96rem;
    }

    .panel-sub{
        font-size:.76rem;
    }

    .search{
        font-size:.84rem;
    }
}
</style>

<div class="admin-bookings">

    <div class="page-head">
        <div class="page-head-left">
            <h2>Bookings Management</h2>
            <div class="meta">{{ $today }} • {{ $tz }}</div>
        </div>

        <div class="head-actions">
            <button type="button" class="btnx success" onclick="openCreateModal()">+ New Booking</button>
            <button type="button" class="btnx" onclick="location.reload()">Refresh</button>
        </div>
    </div>

    <div class="kpis">
        <div class="kpi">
            <div class="label">Current Bookings</div>
            <div class="value">{{ number_format($currentBookings->count()) }}</div>
        </div>
        <div class="kpi">
            <div class="label">Booking History</div>
            <div class="value">{{ number_format($bookingHistory->count()) }}</div>
        </div>
        <div class="kpi">
            <div class="label">Total</div>
            <div class="value">{{ number_format($currentBookings->count() + $bookingHistory->count()) }}</div>
        </div>
        <div class="kpi">
            <div class="label">Assigned Providers</div>
            <div class="value">{{ number_format($currentBookings->whereNotNull('provider_id')->count() + $bookingHistory->whereNotNull('provider_id')->count()) }}</div>
        </div>
    </div>

    <div class="adjustment-kpis">
        <div class="kpi adjustment pending">
            <div class="label">Pending Adjustments</div>
            <div class="value">{{ number_format($adjustmentSummary['pending'] ?? 0) }}</div>
        </div>
        <div class="kpi adjustment accepted">
            <div class="label">Accepted Adjustments</div>
            <div class="value">{{ number_format($adjustmentSummary['accepted'] ?? 0) }}</div>
        </div>
        <div class="kpi adjustment rejected">
            <div class="label">Rejected Adjustments</div>
            <div class="value">{{ number_format($adjustmentSummary['rejected'] ?? 0) }}</div>
        </div>
    </div>

    @if(session('success'))
        <div class="notice success">{{ session('success') }}</div>
    @endif

    @if($viewErrors->any())
        <div class="notice danger">
            Please fix:
            <ul>
                @foreach($viewErrors->all() as $e)
                    <li>{{ $e }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="grid-2">

        {{-- CURRENT BOOKINGS --}}
        <div class="panel">
            <div class="panel-toolbar">
                <div class="panel-head">
                    <div>
                        <h3 class="panel-title">Current Bookings</h3>
                        <div class="panel-sub">Confirmed • In progress • Paid</div>
                    </div>
                    <div class="count-pill">{{ $currentBookings->count() }}</div>
                </div>

                <input id="searchCurrent" class="search" placeholder="Search current bookings...">
            </div>

            <div class="panel-scroll">
                <div class="booking-list" id="listCurrent">
                    @forelse($currentBookings as $b)
                        @php
                            $status = $b->status ?? 'confirmed';
                            $isLocked = $status === 'cancelled';

                            $badgeClass = match($status){
                                'completed' => 'success',
                                'cancelled' => 'danger',
                                'paid'      => 'warn',
                                default     => 'info',
                            };

                            $ref = $b->reference_code ?: ('#'.$b->id);
                            $custName = $b->customer_name ?: ('Customer ID: '.$b->customer_id);
                            $provName = $b->provider_name ?: ($b->provider_id ? 'Provider ID: '.$b->provider_id : 'Unassigned');
                            $serviceLabel = $b->service_name ?: 'Cleaning Service';
                            $serviceOptionLabel = trim((string) ($b->service_option_name ?? '')) ?: 'Selected booking option';
                            $houseLabel = $b->house_type ? ucwords(str_replace('_', ' ', $b->house_type)) : '';
                            $serviceSummary = $serviceOptionLabel . ($houseLabel ? ' â€¢ ' . $houseLabel : '');

                            $adjustment = $b->adjustment_details ?? null;

                            $details = [
                                'id' => $b->id,
                                'reference_code' => $b->reference_code,
                                'customer_id' => $b->customer_id,
                                'customer_name' => $b->customer_name,
                                'customer_phone' => $b->customer_phone,
                                'provider_id' => $b->provider_id,
                                'provider_name' => $b->provider_name,
                                'provider_phone' => $b->provider_phone,
                                'service_id' => $b->service_id,
                                'service_name' => $b->service_name,
                                'service_option_id' => $b->service_option_id,
                                'service_option_name' => $b->service_option_name,
                                'contact_phone' => $b->contact_phone,
                                'address' => $b->address,
                                'house_type' => $b->house_type,
                                'booking_date' => $b->booking_date,
                                'time_start' => $b->time_start,
                                'time_end' => $b->time_end,
                                'price' => $b->price,
                                'status' => $b->status,
                                'cancellation_reason' => $b->cancellation_reason,
                                'cancelled_by_role' => $b->cancelled_by_role,
                                'adjustment_status' => $b->adjustment_status,
                                'adjustment_status_label' => $b->adjustment_status_label,
                                'adjustment_reason_text' => $b->adjustment_reason_text,
                                'adjustment' => $adjustment ? [
                                    'status_key' => $adjustment->status_key ?? null,
                                    'status_label' => $adjustment->status_label ?? null,
                                    'submitted_at_label' => $adjustment->submitted_at_label ?? null,
                                    'resolved_at_label' => $adjustment->resolved_at_label ?? null,
                                    'original_service_name' => $adjustment->original_service_name ?? null,
                                    'original_option_summary' => $adjustment->original_option_summary ?? null,
                                    'original_price_display' => $adjustment->original_price_display ?? null,
                                    'proposed_service_name' => $adjustment->proposed_service_name ?? null,
                                    'proposed_scope_summary' => $adjustment->proposed_scope_summary ?? null,
                                    'additional_fee_display' => $adjustment->additional_fee_display ?? null,
                                    'proposed_total_display' => $adjustment->proposed_total_display ?? null,
                                    'difference_display' => $adjustment->difference_display ?? null,
                                    'price_increase_percent_display' => $adjustment->price_increase_percent_display ?? null,
                                    'reason_labels' => $adjustment->reason_labels ?? [],
                                    'other_reason' => $adjustment->other_reason ?? null,
                                    'provider_note' => $adjustment->provider_note ?? null,
                                    'customer_response_note' => $adjustment->customer_response_note ?? null,
                                    'evidence_url' => $adjustment->evidence_url ?? null,
                                    'evidence_name' => $adjustment->evidence_name ?? null,
                                    'logs' => $adjustment->logs ?? [],
                                ] : null,
                                'created_at' => $b->created_at,
                                'updated_at' => $b->updated_at,
                            ];
                        @endphp

                        <div class="booking-card booking-item"
                             data-search="{{ strtolower(trim($ref.' '.$custName.' '.$provName.' '.$status.' '.$b->booking_date.' '.booking_time_12h($b->time_start).' '.booking_time_12h($b->time_end).' '.$b->address.' '.$b->contact_phone.' '.$serviceLabel.' '.$serviceSummary.' '.($b->adjustment_status_label ?? '').' '.($b->adjustment_reason_text ?? '').' '.($b->cancellation_reason ?? ''))) }}">

                            <div class="booking-top">
                                <div class="booking-ref">
                                    {{ $ref }}
                                    <small>{{ ucfirst(str_replace('_', ' ', $status)) }} booking</small>
                                </div>
                                <span class="badge {{ $badgeClass }}">{{ str_replace('_', ' ', $status) }}</span>
                            </div>

                            <div class="history-summary">
                                <div class="summary-main">
                                    <div class="summary-title">{{ $serviceLabel }}</div>
                                    <div class="summary-sub">
                                        {{ $serviceOptionLabel }}{{ $houseLabel ? ' / '.$houseLabel : '' }}
                                    </div>
                                </div>

                                <div class="summary-price">
                                    ₱{{ number_format((float)($b->price ?? 0), 2) }}
                                </div>
                            </div>

                            <div class="booking-meta compact-meta">
                                <div class="meta-box">
                                    <div class="meta-label">Customer</div>
                                    <div class="meta-value">{{ $custName }}</div>
                                    <div class="meta-value small">{{ $b->customer_phone ?: 'No phone' }}</div>
                                </div>

                                <div class="meta-box">
                                    <div class="meta-label">Provider</div>
                                    <div class="meta-value">{{ $provName }}</div>
                                    <div class="meta-value small">{{ $b->provider_phone ?: 'No phone' }}</div>
                                </div>

                                <div class="meta-box">
                                    <div class="meta-label">Schedule</div>
                                    <div class="meta-value">{{ $b->booking_date ?: '-' }}</div>
                                    <div class="meta-value small">{{ booking_time_12h($b->time_start) }} - {{ booking_time_12h($b->time_end) }}</div>
                                </div>

                                <div class="meta-box">
                                    <div class="meta-label">Contact</div>
                                    <div class="meta-value small">{{ $b->contact_phone ?: 'No contact phone' }}</div>
                                    <div class="meta-value small">{{ $b->address ?: 'No address provided' }}</div>
                                </div>
                            </div>

                            @if($adjustment || !empty($b->cancellation_reason))
                                <div class="meta-box full-span" style="margin-top:12px;">
                                    <div class="meta-label">Booking Notes</div>
                                    <div class="booking-note-stack">
                                        @if($adjustment)
                                            @php
                                                $latestAdjustmentLog = collect($adjustment->logs ?? [])->first();
                                            @endphp
                                            <div class="booking-note-row">
                                                <span class="booking-note-chip">{{ $b->adjustment_status_label ?: 'Adjustment' }}</span>
                                                @if(!empty($adjustment->submitted_at_label))
                                                    <span class="booking-note-chip muted">Submitted {{ $adjustment->submitted_at_label }}</span>
                                                @endif
                                                @if(!empty($adjustment->resolved_at_label))
                                                    <span class="booking-note-chip muted">Resolved {{ $adjustment->resolved_at_label }}</span>
                                                @endif
                                            </div>
                                            <div class="booking-note-line">
                                                <strong>Original:</strong>
                                                {{ $adjustment->original_service_name ?: 'Booking' }}
                                                @if(!empty($adjustment->original_option_summary))
                                                    / {{ $adjustment->original_option_summary }}
                                                @endif
                                                / PHP {{ $adjustment->original_price_display ?? '0.00' }}
                                            </div>
                                            <div class="booking-note-line">
                                                <strong>Updated:</strong>
                                                {{ $adjustment->proposed_service_name ?: ($adjustment->original_service_name ?: 'Booking') }}
                                                @if(!empty($adjustment->proposed_scope_summary))
                                                    / {{ $adjustment->proposed_scope_summary }}
                                                @endif
                                                / PHP {{ $adjustment->proposed_total_display ?? ($adjustment->original_price_display ?? '0.00') }}
                                            </div>
                                            @if(!empty($adjustment->reason_labels))
                                                <div class="booking-note-line"><strong>Reasons:</strong> {{ implode(', ', $adjustment->reason_labels) }}</div>
                                            @endif
                                            @if(!empty($adjustment->provider_note))
                                                <div class="booking-note-line"><strong>Provider note:</strong> {{ \Illuminate\Support\Str::limit($adjustment->provider_note, 140) }}</div>
                                            @endif
                                            @if(!empty($adjustment->customer_response_note))
                                                <div class="booking-note-line"><strong>Customer note:</strong> {{ \Illuminate\Support\Str::limit($adjustment->customer_response_note, 140) }}</div>
                                            @endif
                                            @if(!empty($latestAdjustmentLog['action']) || !empty($latestAdjustmentLog['detail']))
                                                <div class="booking-note-line">
                                                    <strong>Latest activity:</strong>
                                                    {{ $latestAdjustmentLog['action'] ?? 'Update' }}
                                                    @if(!empty($latestAdjustmentLog['detail']))
                                                        / {{ \Illuminate\Support\Str::limit($latestAdjustmentLog['detail'], 140) }}
                                                    @endif
                                                </div>
                                            @endif
                                            @if(!empty($adjustment->evidence_url))
                                                <div class="booking-note-line">
                                                    <a class="adjustment-link" href="{{ $adjustment->evidence_url }}" target="_blank" rel="noopener">
                                                        {{ $adjustment->evidence_name ?: 'Open evidence' }}
                                                    </a>
                                                </div>
                                            @endif
                                        @endif
                                        @if(!empty($b->cancellation_reason))
                                            @php
                                                $cancelledByLabel = !empty($b->cancelled_by_role)
                                                    ? ucwords(str_replace('_', ' ', $b->cancelled_by_role))
                                                    : 'System';
                                            @endphp
                                            <div class="booking-note-line"><strong>Cancelled by {{ $cancelledByLabel }}:</strong> {{ $b->cancellation_reason }}</div>
                                        @endif
                                    </div>
                                </div>
                            @endif

                            <div class="booking-actions">
                                <button type="button" class="btnx" onclick='openBookingCard(@json($details))'>View</button>
                                <button type="button" class="btnx primary" onclick='openEditModal(@json($details))'>Edit</button>

                                <form method="POST" action="{{ route('admin.bookings.status', $b->id) }}" class="status-wrap" data-cancelled="0">
                                    @csrf
                                    <select class="status-select js-status-select" name="status" @disabled($isLocked)>
                                        @foreach(['confirmed','in_progress','paid','completed','cancelled'] as $st)
                                            <option value="{{ $st }}" @selected($status === $st)>{{ str_replace('_', ' ', $st) }}</option>
                                        @endforeach
                                    </select>
                                    <textarea class="status-reason js-status-reason"
                                              name="cancellation_reason"
                                              rows="3"
                                              placeholder="Reason if cancelling"
                                              @disabled($isLocked)>{{ old('cancellation_reason', $status === 'cancelled' ? ($b->cancellation_reason ?? '') : '') }}</textarea>
                                    <div class="status-help js-status-help">Required when status is cancelled.</div>
                                    <button class="btnx" type="submit" @disabled($isLocked)>
                                        {{ $isLocked ? 'Locked' : 'Save' }}
                                    </button>
                                </form>

                                <form method="POST" action="{{ route('admin.bookings.destroy', $b->id) }}" onsubmit="return confirm('Delete this booking permanently?');">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btnx danger" type="submit">Delete</button>
                                </form>

                                @if($isLocked)
                                    <div class="lock-note">This booking is cancelled and can no longer be updated.</div>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="empty">No current bookings.</div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- BOOKING HISTORY --}}
        <div class="panel">
            <div class="panel-toolbar">
                <div class="panel-head">
                    <div>
                        <h3 class="panel-title">Booking History</h3>
                        <div class="panel-sub">Completed • Cancelled</div>
                    </div>
                    <div class="count-pill">{{ $bookingHistory->count() }}</div>
                </div>

                <input id="searchHistory" class="search" placeholder="Search by reference, customer, provider, phone, address...">

                <button type="button" class="btnx ghost filter-toggle" onclick="toggleHistoryFilters()">
                    Filter History
                </button>

                <div class="filter-box" id="historyFilterBox">
                    <div class="filter-grid">
                        <div>
                            <label class="meta-label" style="display:block;margin-bottom:6px;">Status</label>
                            <select id="historyStatus" class="selectx">
                                <option value="">All status</option>
                                <option value="completed">Completed</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                        </div>

                        <div>
                            <label class="meta-label" style="display:block;margin-bottom:6px;">Reference / Customer / Provider</label>
                            <input type="text" id="historyKeyword" class="inputx" placeholder="Type reference, provider, customer...">
                        </div>

                        <div>
                            <label class="meta-label" style="display:block;margin-bottom:6px;">Date from</label>
                            <input type="date" id="historyDateFrom" class="inputx">
                        </div>

                        <div>
                            <label class="meta-label" style="display:block;margin-bottom:6px;">Date to</label>
                            <input type="date" id="historyDateTo" class="inputx">
                        </div>
                    </div>

                    <div class="filter-actions">
                        <button type="button" class="btnx primary" onclick="applyHistoryFilters()">Apply Filter</button>
                        <button type="button" class="btnx" onclick="clearHistoryFilters()">Clear</button>
                    </div>
                </div>
                </div>

                <div class="panel-scroll">
                    <div class="booking-list" id="listHistory">
                    @if($bookingHistory->isEmpty())
                        <div class="empty">No booking history yet.</div>
                    @else
                        @foreach($bookingHistory as $b)
                        @php
                            $status = $b->status ?? 'completed';
                            $isLocked = $status === 'cancelled';

                            $badgeClass = match($status){
                                'completed' => 'success',
                                'cancelled' => 'danger',
                                'paid'      => 'warn',
                                default     => 'info',
                            };

                            $ref = $b->reference_code ?: ('#'.$b->id);
                            $custName = $b->customer_name ?: ('Customer ID: '.$b->customer_id);
                            $provName = $b->provider_name ?: ($b->provider_id ? 'Provider ID: '.$b->provider_id : 'Unassigned');
                            $historyDate = $b->updated_at
                                ? \Carbon\Carbon::parse($b->updated_at)->timezone($tz)->toDateString()
                                : ($b->booking_date ?: '');

                            $serviceLabel = $b->service_name ?: 'Cleaning Service';
                            $serviceOptionLabel = trim((string) ($b->service_option_name ?? '')) ?: 'Selected booking option';
                            $houseLabel = $b->house_type ? ucwords(str_replace('_', ' ', $b->house_type)) : '';

                            $details = [
                                'id' => $b->id,
                                'reference_code' => $b->reference_code,
                                'customer_id' => $b->customer_id,
                                'customer_name' => $b->customer_name,
                                'customer_phone' => $b->customer_phone,
                                'provider_id' => $b->provider_id,
                                'provider_name' => $b->provider_name,
                                'provider_phone' => $b->provider_phone,
                                'service_id' => $b->service_id,
                                'service_name' => $b->service_name,
                                'service_option_id' => $b->service_option_id,
                                'service_option_name' => $b->service_option_name,
                                'contact_phone' => $b->contact_phone,
                                'address' => $b->address,
                                'house_type' => $b->house_type,
                                'booking_date' => $b->booking_date,
                                'time_start' => $b->time_start,
                                'time_end' => $b->time_end,
                                'price' => $b->price,
                                'status' => $b->status,
                                'cancellation_reason' => $b->cancellation_reason,
                                'cancelled_by_role' => $b->cancelled_by_role,
                                'adjustment_status' => $b->adjustment_status,
                                'adjustment_status_label' => $b->adjustment_status_label,
                                'adjustment_reason_text' => $b->adjustment_reason_text,
                                'adjustment' => $adjustment ? [
                                    'status_key' => $adjustment->status_key ?? null,
                                    'status_label' => $adjustment->status_label ?? null,
                                    'submitted_at_label' => $adjustment->submitted_at_label ?? null,
                                    'resolved_at_label' => $adjustment->resolved_at_label ?? null,
                                    'original_service_name' => $adjustment->original_service_name ?? null,
                                    'original_option_summary' => $adjustment->original_option_summary ?? null,
                                    'original_price_display' => $adjustment->original_price_display ?? null,
                                    'proposed_service_name' => $adjustment->proposed_service_name ?? null,
                                    'proposed_scope_summary' => $adjustment->proposed_scope_summary ?? null,
                                    'additional_fee_display' => $adjustment->additional_fee_display ?? null,
                                    'proposed_total_display' => $adjustment->proposed_total_display ?? null,
                                    'difference_display' => $adjustment->difference_display ?? null,
                                    'price_increase_percent_display' => $adjustment->price_increase_percent_display ?? null,
                                    'reason_labels' => $adjustment->reason_labels ?? [],
                                    'other_reason' => $adjustment->other_reason ?? null,
                                    'provider_note' => $adjustment->provider_note ?? null,
                                    'customer_response_note' => $adjustment->customer_response_note ?? null,
                                    'evidence_url' => $adjustment->evidence_url ?? null,
                                    'evidence_name' => $adjustment->evidence_name ?? null,
                                    'logs' => $adjustment->logs ?? [],
                                ] : null,
                                'created_at' => $b->created_at,
                                'updated_at' => $b->updated_at,
                            ];
                        @endphp

                        <div class="booking-card booking-item history-item"
                             data-search="{{ strtolower(trim($ref.' '.$custName.' '.$provName.' '.$status.' '.$b->booking_date.' '.$historyDate.' '.booking_time_12h($b->time_start).' '.booking_time_12h($b->time_end).' '.$b->address.' '.$b->contact_phone.' '.$b->customer_phone.' '.$b->provider_phone.' '.$serviceLabel.' '.$serviceOptionLabel.' '.$houseLabel.' '.($b->adjustment_status_label ?? '').' '.($b->adjustment_reason_text ?? '').' '.($b->cancellation_reason ?? ''))) }}"
                             data-status="{{ strtolower($status) }}"
                             data-history-date="{{ $historyDate }}"
                             data-reference="{{ strtolower($ref) }}"
                             data-customer="{{ strtolower($custName) }}"
                             data-provider="{{ strtolower($provName) }}">

                            <div class="booking-top">
                                <div class="booking-ref">
                                    {{ $ref }}
                                    <small>{{ ucfirst(str_replace('_', ' ', $status)) }} booking</small>
                                </div>
                                <span class="badge {{ $badgeClass }}">{{ str_replace('_', ' ', $status) }}</span>
                            </div>

                            <div class="history-summary">
                                <div class="summary-main">
                                    <div class="summary-title">{{ $serviceLabel }}</div>
                                    <div class="summary-sub">
                                        {{ $serviceOptionLabel }}{{ $houseLabel ? ' / '.$houseLabel : '' }}
                                    </div>
                                </div>

                                <div class="summary-price">
                                    ₱{{ number_format((float)($b->price ?? 0), 2) }}
                                </div>
                            </div>

                            <div class="booking-meta compact-meta">
                                <div class="meta-box">
                                    <div class="meta-label">Customer</div>
                                    <div class="meta-value">{{ $custName }}</div>
                                    <div class="meta-value small">{{ $b->customer_phone ?: 'No phone' }}</div>
                                </div>

                                <div class="meta-box">
                                    <div class="meta-label">Provider</div>
                                    <div class="meta-value">{{ $provName }}</div>
                                    <div class="meta-value small">{{ $b->provider_phone ?: 'No phone' }}</div>
                                </div>

                                <div class="meta-box">
                                    <div class="meta-label">Schedule</div>
                                    <div class="meta-value">{{ $b->booking_date ?: '-' }}</div>
                                    <div class="meta-value small">{{ booking_time_12h($b->time_start) }} - {{ booking_time_12h($b->time_end) }}</div>
                                </div>

                                <div class="meta-box">
                                    <div class="meta-label">Tracked Date</div>
                                    <div class="meta-value">{{ $historyDate ?: '-' }}</div>
                                    <div class="meta-value small">
                                        {{ $status === 'completed' ? 'Completed/updated date' : 'Cancelled/updated date' }}
                                    </div>
                                </div>

                                <div class="meta-box full-span">
                                    <div class="meta-label">Address / Contact</div>
                                    <div class="meta-value small">{{ $b->contact_phone ?: 'No contact phone' }}</div>
                                    <div class="meta-value small">{{ $b->address ?: 'No address provided' }}</div>
                                </div>
                            </div>

                            @if($adjustment || !empty($b->cancellation_reason))
                                <div class="meta-box full-span" style="margin-top:12px;">
                                    <div class="meta-label">Booking Notes</div>
                                    <div class="booking-note-stack">
                                        @if($adjustment)
                                            @php
                                                $latestAdjustmentLog = collect($adjustment->logs ?? [])->first();
                                            @endphp
                                            <div class="booking-note-row">
                                                <span class="booking-note-chip">{{ $b->adjustment_status_label ?: 'Adjustment' }}</span>
                                                @if(!empty($adjustment->submitted_at_label))
                                                    <span class="booking-note-chip muted">Submitted {{ $adjustment->submitted_at_label }}</span>
                                                @endif
                                                @if(!empty($adjustment->resolved_at_label))
                                                    <span class="booking-note-chip muted">Resolved {{ $adjustment->resolved_at_label }}</span>
                                                @endif
                                            </div>
                                            <div class="booking-note-line">
                                                <strong>Original:</strong>
                                                {{ $adjustment->original_service_name ?: 'Booking' }}
                                                @if(!empty($adjustment->original_option_summary))
                                                    / {{ $adjustment->original_option_summary }}
                                                @endif
                                                / PHP {{ $adjustment->original_price_display ?? '0.00' }}
                                            </div>
                                            <div class="booking-note-line">
                                                <strong>Updated:</strong>
                                                {{ $adjustment->proposed_service_name ?: ($adjustment->original_service_name ?: 'Booking') }}
                                                @if(!empty($adjustment->proposed_scope_summary))
                                                    / {{ $adjustment->proposed_scope_summary }}
                                                @endif
                                                / PHP {{ $adjustment->proposed_total_display ?? ($adjustment->original_price_display ?? '0.00') }}
                                            </div>
                                            @if(!empty($adjustment->reason_labels))
                                                <div class="booking-note-line"><strong>Reasons:</strong> {{ implode(', ', $adjustment->reason_labels) }}</div>
                                            @endif
                                            @if(!empty($adjustment->provider_note))
                                                <div class="booking-note-line"><strong>Provider note:</strong> {{ \Illuminate\Support\Str::limit($adjustment->provider_note, 140) }}</div>
                                            @endif
                                            @if(!empty($adjustment->customer_response_note))
                                                <div class="booking-note-line"><strong>Customer note:</strong> {{ \Illuminate\Support\Str::limit($adjustment->customer_response_note, 140) }}</div>
                                            @endif
                                            @if(!empty($latestAdjustmentLog['action']) || !empty($latestAdjustmentLog['detail']))
                                                <div class="booking-note-line">
                                                    <strong>Latest activity:</strong>
                                                    {{ $latestAdjustmentLog['action'] ?? 'Update' }}
                                                    @if(!empty($latestAdjustmentLog['detail']))
                                                        / {{ \Illuminate\Support\Str::limit($latestAdjustmentLog['detail'], 140) }}
                                                    @endif
                                                </div>
                                            @endif
                                            @if(!empty($adjustment->evidence_url))
                                                <div class="booking-note-line">
                                                    <a class="adjustment-link" href="{{ $adjustment->evidence_url }}" target="_blank" rel="noopener">
                                                        {{ $adjustment->evidence_name ?: 'Open evidence' }}
                                                    </a>
                                                </div>
                                            @endif
                                        @endif
                                        @if(!empty($b->cancellation_reason))
                                            @php
                                                $cancelledByLabel = !empty($b->cancelled_by_role)
                                                    ? ucwords(str_replace('_', ' ', $b->cancelled_by_role))
                                                    : 'System';
                                            @endphp
                                            <div class="booking-note-line"><strong>Cancelled by {{ $cancelledByLabel }}:</strong> {{ $b->cancellation_reason }}</div>
                                        @endif
                                    </div>
                                </div>
                            @endif

                            <div class="booking-actions">
                                <button type="button" class="btnx" onclick='openBookingCard(@json($details))'>View</button>
                                <button type="button" class="btnx primary" onclick='openEditModal(@json($details))'>Edit</button>

                                <form method="POST" action="{{ route('admin.bookings.status', $b->id) }}" class="status-wrap" data-cancelled="0">
                                    @csrf
                                    <select class="status-select js-status-select" name="status" @disabled($isLocked)>
                                        @foreach(['confirmed','in_progress','paid','completed','cancelled'] as $st)
                                            <option value="{{ $st }}" @selected($status === $st)>{{ str_replace('_', ' ', $st) }}</option>
                                        @endforeach
                                    </select>
                                    <textarea class="status-reason js-status-reason"
                                              name="cancellation_reason"
                                              rows="3"
                                              placeholder="Reason if cancelling"
                                              @disabled($isLocked)>{{ old('cancellation_reason', $status === 'cancelled' ? ($b->cancellation_reason ?? '') : '') }}</textarea>
                                    <div class="status-help js-status-help">Required when status is cancelled.</div>
                                    <button class="btnx" type="submit" @disabled($isLocked)>
                                        {{ $isLocked ? 'Locked' : 'Save' }}
                                    </button>
                                </form>

                                <form method="POST" action="{{ route('admin.bookings.destroy', $b->id) }}" onsubmit="return confirm('Delete this booking permanently?');">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btnx danger" type="submit">Delete</button>
                                </form>

                                @if($isLocked)
                                    <div class="lock-note">Cancelled booking is locked and cannot be reactivated.</div>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

{{-- VIEW MODAL --}}
<div class="modalx" id="bookingModal" onclick="closeIfBackdrop(event, 'bookingModal')">
    <div class="modal-panel">
        <div class="modal-head">
            <h3 id="mTitle">Booking Details</h3>
            <button type="button" class="btnx" onclick="closeModal('bookingModal')">Close</button>
        </div>

        <div class="detail-grid">
            <div class="detail-box">
                <div class="detail-label">Reference</div>
                <div class="detail-value" id="mRef"></div>
            </div>
            <div class="detail-box">
                <div class="detail-label">Status</div>
                <div class="detail-value" id="mStatus"></div>
            </div>

            <div class="detail-box">
                <div class="detail-label">Customer</div>
                <div class="detail-value" id="mCustomer"></div>
            </div>
            <div class="detail-box">
                <div class="detail-label">Provider</div>
                <div class="detail-value" id="mProvider"></div>
            </div>

            <div class="detail-box">
                <div class="detail-label">Booking Date</div>
                <div class="detail-value" id="mDate"></div>
            </div>
            <div class="detail-box">
                <div class="detail-label">Time</div>
                <div class="detail-value" id="mTime"></div>
            </div>

            <div class="detail-box">
                <div class="detail-label">Service</div>
                <div class="detail-value" id="mService"></div>
            </div>
            <div class="detail-box">
                <div class="detail-label">Price</div>
                <div class="detail-value" id="mPrice"></div>
            </div>

            <div class="detail-box">
                <div class="detail-label">House Type</div>
                <div class="detail-value" id="mHouse"></div>
            </div>
            <div class="detail-box">
                <div class="detail-label">Contact Phone</div>
                <div class="detail-value" id="mContact"></div>
            </div>

            <div class="detail-box full">
                <div class="detail-label">Address</div>
                <div class="detail-value" id="mAddress"></div>
            </div>

            <div class="detail-box full">
                <div class="detail-label">Created / Updated</div>
                <div class="detail-value" id="mDates"></div>
            </div>

            <div class="detail-box full" id="mAdjustmentWrap" style="display:none;">
                <div class="detail-label">Adjustment Details</div>
                <div class="detail-value" id="mAdjustment"></div>
            </div>

            <div class="detail-box full" id="mAdjustmentLogsWrap" style="display:none;">
                <div class="detail-label">Adjustment Timeline</div>
                <div class="detail-value" id="mAdjustmentLogs"></div>
            </div>

            <div class="detail-box full" id="mCancellationWrap" style="display:none;">
                <div class="detail-label">Cancellation Reason</div>
                <div class="detail-value" id="mCancellation"></div>
            </div>
        </div>

        <div class="form-actions">
            <button type="button" class="btnx primary" id="viewEditBtn">Edit This Booking</button>
            <button type="button" class="btnx" onclick="closeModal('bookingModal')">Close</button>
        </div>
    </div>
</div>

{{-- CREATE / EDIT MODAL --}}
<div class="modalx" id="formModal" onclick="closeIfBackdrop(event, 'formModal')">
    <div class="modal-panel">
        <div class="modal-head">
            <h3 id="formTitle">Create Booking</h3>
            <button type="button" class="btnx" onclick="closeModal('formModal')">Close</button>
        </div>

        <form method="POST" id="bookingForm" action="{{ route('admin.bookings.store') }}">
            @csrf
            <div id="methodField"></div>

            <div class="form-grid">
                <div class="form-group">
                    <label>Reference Code</label>
                    <input type="text" class="inputx" name="reference_code" id="f_reference_code" maxlength="20" placeholder="Optional">
                </div>

                <div class="form-group">
                    <label>Status *</label>
                    <select class="selectx" name="status" id="f_status" required>
                        <option value="confirmed">confirmed</option>
                        <option value="in_progress">in progress</option>
                        <option value="paid">paid</option>
                        <option value="completed">completed</option>
                        <option value="cancelled">cancelled</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Customer *</label>
                    <select class="selectx" name="customer_id" id="f_customer_id" required>
                        <option value="">Select customer</option>
                        @foreach($customers as $customer)
                            <option value="{{ $customer->id }}">
                                {{ $customer->display_name }} @if($customer->phone) • {{ $customer->phone }} @endif
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label>Assigned Provider *</label>
                    <select class="selectx" name="provider_id" id="f_provider_id" required>
                        <option value="">Select provider</option>
                        @foreach($providers as $provider)
                            <option value="{{ $provider->id }}">
                                {{ $provider->display_name }} @if($provider->phone) • {{ $provider->phone }} @endif
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label>Service ID *</label>
                    <input type="number" class="inputx" name="service_id" id="f_service_id" min="1" required>
                </div>

                <div class="form-group">
                    <label>Service Option ID *</label>
                    <input type="number" class="inputx" name="service_option_id" id="f_service_option_id" min="1" required>
                </div>

                <div class="form-group">
                    <label>Booking Date *</label>
                    <input type="date" class="inputx" name="booking_date" id="f_booking_date" required>
                </div>

                <div class="form-group">
                    <label>Contact Phone</label>
                    <input type="text" class="inputx" name="contact_phone" id="f_contact_phone" maxlength="20">
                </div>

                <div class="form-group">
                    <label>Time Start *</label>
                    <input type="time" class="inputx" name="time_start" id="f_time_start" required>
                </div>

                <div class="form-group">
                    <label>Time End *</label>
                    <input type="time" class="inputx" name="time_end" id="f_time_end" required>
                </div>

                <div class="form-group">
                    <label>House Type</label>
                    <select class="selectx" name="house_type" id="f_house_type">
                        <option value="">Select house type</option>
                        <option value="small">small</option>
                        <option value="medium">medium</option>
                        <option value="big">big</option>
                        <option value="second_floor">second floor</option>
                        <option value="full_clean">full clean</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Price</label>
                    <input type="number" step="0.01" min="0" class="inputx" name="price" id="f_price" placeholder="0.00">
                </div>

                <div class="form-group full">
                    <label>Address</label>
                    <textarea class="textareax" name="address" id="f_address" placeholder="Full address"></textarea>
                </div>
            </div>

            <div class="form-actions">
                <button type="button" class="btnx" onclick="closeModal('formModal')">Cancel</button>
                <button type="submit" class="btnx primary" id="submitBtn">Save Booking</button>
            </div>
        </form>
    </div>
</div>

<script>
function filterList(inputId, listId){
    const q = (document.getElementById(inputId).value || '').trim().toLowerCase();
    const items = document.querySelectorAll(`#${listId} .booking-item`);
    items.forEach(item => {
        const hay = item.getAttribute('data-search') || '';
        item.style.display = hay.includes(q) ? '' : 'none';
    });
}

document.getElementById('searchCurrent')?.addEventListener('input', () => filterList('searchCurrent', 'listCurrent'));

function toggleHistoryFilters(){
    const box = document.getElementById('historyFilterBox');
    box.classList.toggle('active');
}

function applyHistoryFilters(){
    const mainSearch = (document.getElementById('searchHistory')?.value || '').trim().toLowerCase();
    const keyword = (document.getElementById('historyKeyword')?.value || '').trim().toLowerCase();
    const status = (document.getElementById('historyStatus')?.value || '').trim().toLowerCase();
    const dateFrom = (document.getElementById('historyDateFrom')?.value || '').trim();
    const dateTo = (document.getElementById('historyDateTo')?.value || '').trim();

    const items = document.querySelectorAll('#listHistory .history-item');

    items.forEach(item => {
        const searchHay = (item.getAttribute('data-search') || '').toLowerCase();
        const itemStatus = (item.getAttribute('data-status') || '').toLowerCase();
        const itemDate = (item.getAttribute('data-history-date') || '').trim();

        let show = true;

        if(mainSearch && !searchHay.includes(mainSearch)) show = false;
        if(keyword && !searchHay.includes(keyword)) show = false;
        if(status && itemStatus !== status) show = false;
        if(dateFrom && itemDate && itemDate < dateFrom) show = false;
        if(dateTo && itemDate && itemDate > dateTo) show = false;
        if((dateFrom || dateTo) && !itemDate) show = false;

        item.classList.toggle('hidden-by-filter', !show);
    });
}

function clearHistoryFilters(){
    document.getElementById('searchHistory').value = '';
    document.getElementById('historyKeyword').value = '';
    document.getElementById('historyStatus').value = '';
    document.getElementById('historyDateFrom').value = '';
    document.getElementById('historyDateTo').value = '';

    document.querySelectorAll('#listHistory .history-item').forEach(item => {
        item.classList.remove('hidden-by-filter');
    });
}

document.getElementById('searchHistory')?.addEventListener('input', applyHistoryFilters);
document.getElementById('historyKeyword')?.addEventListener('input', applyHistoryFilters);
document.getElementById('historyStatus')?.addEventListener('change', applyHistoryFilters);
document.getElementById('historyDateFrom')?.addEventListener('change', applyHistoryFilters);
document.getElementById('historyDateTo')?.addEventListener('change', applyHistoryFilters);

function syncCancellationReason(form){
    const select = form.querySelector('.js-status-select');
    const reason = form.querySelector('.js-status-reason');
    const help = form.querySelector('.js-status-help');

    if(!select || !reason || !help){
        return;
    }

    const show = String(select.value || '').trim().toLowerCase() === 'cancelled';
    form.dataset.cancelled = show ? '1' : '0';
    reason.required = show && !select.disabled;
}

document.querySelectorAll('.status-wrap').forEach((form) => {
    const select = form.querySelector('.js-status-select');
    if(!select){
        return;
    }

    syncCancellationReason(form);
    select.addEventListener('change', () => syncCancellationReason(form));
});

function openModal(id){
    document.getElementById(id).style.display = 'block';
    document.body.style.overflow = 'hidden';
}
function closeModal(id){
    document.getElementById(id).style.display = 'none';
    document.body.style.overflow = '';
}
function closeIfBackdrop(e, id){
    if(e.target && e.target.id === id){
        closeModal(id);
    }
}

function normalizeTime(value){
    if(!value) return '';
    const str = String(value);
    return str.length >= 5 ? str.substring(0,5) : str;
}

function format12Hour(value){
    if(!value) return '-';

    const raw = String(value).trim();
    const clean = raw.length >= 5 ? raw.substring(0,5) : raw;
    const parts = clean.split(':');

    if(parts.length < 2) return raw;

    let hours = parseInt(parts[0], 10);
    let minutes = parts[1];
    if (isNaN(hours)) return raw;

    const suffix = hours >= 12 ? 'PM' : 'AM';
    hours = hours % 12;
    if(hours === 0) hours = 12;

    return hours + ':' + minutes + ' ' + suffix;
}

function escapeHtml(value){
    return String(value ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

function renderAdjustmentMarkup(adjustment){
    if(!adjustment){
        return '';
    }

    const reasonLabels = Array.isArray(adjustment.reason_labels) ? adjustment.reason_labels : [];
    const chips = reasonLabels.length
        ? `<div class="adjustment-chip-row">${reasonLabels.map((label) => `<span class="adjustment-chip">${escapeHtml(label)}</span>`).join('')}</div>`
        : '';

    const providerNote = adjustment.provider_note
        ? `<div class="adjustment-note-block"><div class="adjustment-note-title">Provider note</div><div>${escapeHtml(adjustment.provider_note)}</div></div>`
        : '';

    const customerNote = adjustment.customer_response_note
        ? `<div class="adjustment-note-block"><div class="adjustment-note-title">Customer response</div><div>${escapeHtml(adjustment.customer_response_note)}</div></div>`
        : '';

    const otherReason = adjustment.other_reason
        ? `<div class="adjustment-note-block"><div class="adjustment-note-title">Other reason</div><div>${escapeHtml(adjustment.other_reason)}</div></div>`
        : '';

    const submittedMeta = adjustment.submitted_at_label
        ? `<span class="adjustment-pill muted">Submitted ${escapeHtml(adjustment.submitted_at_label)}</span>`
        : '';

    const resolvedMeta = adjustment.resolved_at_label
        ? `<span class="adjustment-pill muted">Resolved ${escapeHtml(adjustment.resolved_at_label)}</span>`
        : '';

    const evidenceLink = adjustment.evidence_url
        ? `<a class="adjustment-link" href="${escapeHtml(adjustment.evidence_url)}" target="_blank" rel="noopener">${escapeHtml(adjustment.evidence_name || 'View evidence')}</a>`
        : '';

    return `
        <div class="adjustment-detail">
            <div class="adjustment-banner">
                <span class="adjustment-pill">${escapeHtml(adjustment.status_label || 'Adjustment')}</span>
                <div class="adjustment-chip-row">${submittedMeta}${resolvedMeta}</div>
            </div>
            <div class="adjustment-compare">
                <div class="adjustment-panel">
                    <div class="adjustment-panel-label">Original booking</div>
                    <div><strong>${escapeHtml(adjustment.original_service_name || 'Original booking')}</strong></div>
                    <div>${escapeHtml(adjustment.original_option_summary || 'Original scope')}</div>
                    <div>PHP ${escapeHtml(adjustment.original_price_display || '0.00')}</div>
                </div>
                <div class="adjustment-panel">
                    <div class="adjustment-panel-label">Updated onsite scope</div>
                    <div><strong>${escapeHtml(adjustment.proposed_service_name || 'Updated booking')}</strong></div>
                    <div>${escapeHtml(adjustment.proposed_scope_summary || 'Updated scope')}</div>
                    <div>PHP ${escapeHtml(adjustment.proposed_total_display || adjustment.original_price_display || '0.00')}</div>
                </div>
            </div>
            <div class="adjustment-summary-grid">
                <div class="adjustment-summary-box">
                    <div class="adjustment-panel-label">Added fee</div>
                    <div>PHP ${escapeHtml(adjustment.additional_fee_display || '0.00')}</div>
                </div>
                <div class="adjustment-summary-box">
                    <div class="adjustment-panel-label">Price difference</div>
                    <div>PHP ${escapeHtml(adjustment.difference_display || '0.00')}</div>
                </div>
                <div class="adjustment-summary-box">
                    <div class="adjustment-panel-label">Increase</div>
                    <div>${escapeHtml(adjustment.price_increase_percent_display || '0.0')}%</div>
                </div>
            </div>
            ${chips}
            ${providerNote}
            ${customerNote}
            ${otherReason}
            ${evidenceLink ? `<div class="adjustment-chip-row">${evidenceLink}</div>` : ''}
        </div>
    `;
}

function renderAdjustmentLogs(logs){
    if(!Array.isArray(logs) || !logs.length){
        return '';
    }

    return `
        <div class="adjustment-log-list">
            ${logs.map((log) => `
                <div class="adjustment-log-item">
                    <div class="adjustment-log-head">
                        <strong>${escapeHtml(log.action || 'Update')}</strong>
                        <span class="adjustment-log-meta">${escapeHtml(log.created_at || '')}</span>
                    </div>
                    <div class="adjustment-log-meta">${escapeHtml(log.actor || 'System')}</div>
                    ${log.detail ? `<div>${escapeHtml(log.detail)}</div>` : ''}
                </div>
            `).join('')}
        </div>
    `;
}

function openBooking(b){
    const ref = b.reference_code ? b.reference_code : ('#' + b.id);

    document.getElementById('mTitle').textContent = 'Booking Details • ' + ref;
    document.getElementById('mRef').textContent = ref;
    document.getElementById('mStatus').textContent = b.status || '-';

    const cust = (b.customer_name ? b.customer_name : ('Customer ID: ' + (b.customer_id ?? '-'))) + (b.customer_phone ? (' • ' + b.customer_phone) : '');
    const prov = (b.provider_name ? b.provider_name : (b.provider_id ? ('Provider ID: ' + b.provider_id) : 'Unassigned')) + (b.provider_phone ? (' • ' + b.provider_phone) : '');

    const serviceName = b.service_name ? b.service_name : 'Cleaning Service';
    const optionName = b.service_option_name ? b.service_option_name : 'Selected booking option';
    const houseType = b.house_type ? String(b.house_type).replaceAll('_', ' ') : '';
    const serviceParts = [serviceName, optionName];

    if (houseType) {
        serviceParts.push('House Type: ' + houseType);
    }

    const serviceText = serviceParts.join(' / ');

    document.getElementById('mCustomer').textContent = cust;
    document.getElementById('mProvider').textContent = prov;
    document.getElementById('mDate').textContent = b.booking_date || '-';
    document.getElementById('mTime').textContent = format12Hour(b.time_start) + ' - ' + format12Hour(b.time_end);
    document.getElementById('mHouse').textContent = houseType;
    document.getElementById('mPrice').textContent = '₱' + Number(b.price || 0).toFixed(2);
    document.getElementById('mService').textContent = serviceText;
    document.getElementById('mAddress').textContent = b.address || '-';
    document.getElementById('mContact').textContent = b.contact_phone || '-';

    const created = b.created_at ? String(b.created_at) : '-';
    const updated = b.updated_at ? String(b.updated_at) : '-';
    document.getElementById('mDates').textContent = 'Created: ' + created + ' • Updated: ' + updated;

    document.getElementById('viewEditBtn').onclick = function(){
        closeModal('bookingModal');
        openEditModal(b);
    };

    openModal('bookingModal');
}

function openBookingCard(b){
    const ref = b.reference_code ? b.reference_code : ('#' + b.id);
    document.getElementById('mTitle').textContent = 'Booking Details - ' + ref;
    document.getElementById('mRef').textContent = ref;
    document.getElementById('mStatus').textContent = b.status || '-';

    const customerParts = [
        b.customer_name ? b.customer_name : ('Customer ID: ' + (b.customer_id ?? '-')),
        b.customer_phone ? b.customer_phone : ''
    ].filter(Boolean);

    const providerParts = [
        b.provider_name ? b.provider_name : (b.provider_id ? ('Provider ID: ' + b.provider_id) : 'Unassigned'),
        b.provider_phone ? b.provider_phone : ''
    ].filter(Boolean);

    const serviceName = b.service_name ? b.service_name : 'Cleaning Service';
    const optionName = b.service_option_name ? b.service_option_name : 'Selected booking option';
    const houseType = b.house_type ? String(b.house_type).replaceAll('_', ' ') : '';
    const serviceParts = [serviceName, optionName];

    if (houseType) {
        serviceParts.push('House Type: ' + houseType);
    }

    document.getElementById('mCustomer').textContent = customerParts.join(' / ');
    document.getElementById('mProvider').textContent = providerParts.join(' / ');
    document.getElementById('mDate').textContent = b.booking_date || '-';
    document.getElementById('mTime').textContent = format12Hour(b.time_start) + ' - ' + format12Hour(b.time_end);
    document.getElementById('mHouse').textContent = houseType || '-';
    document.getElementById('mPrice').textContent = 'PHP ' + Number(b.price || 0).toFixed(2);
    document.getElementById('mService').textContent = serviceParts.join(' / ');
    document.getElementById('mAddress').textContent = b.address || '-';
    document.getElementById('mContact').textContent = b.contact_phone || '-';

    const created = b.created_at ? String(b.created_at) : '-';
    const updated = b.updated_at ? String(b.updated_at) : '-';
    document.getElementById('mDates').textContent = 'Created: ' + created + ' / Updated: ' + updated;

    const adjustmentWrap = document.getElementById('mAdjustmentWrap');
    const adjustmentValue = document.getElementById('mAdjustment');
    const adjustmentLogsWrap = document.getElementById('mAdjustmentLogsWrap');
    const adjustmentLogsValue = document.getElementById('mAdjustmentLogs');
    const cancellationWrap = document.getElementById('mCancellationWrap');
    const cancellationValue = document.getElementById('mCancellation');

    if (b.adjustment) {
        adjustmentValue.innerHTML = renderAdjustmentMarkup(b.adjustment);
        adjustmentWrap.style.display = '';

        if (Array.isArray(b.adjustment.logs) && b.adjustment.logs.length) {
            adjustmentLogsValue.innerHTML = renderAdjustmentLogs(b.adjustment.logs);
            adjustmentLogsWrap.style.display = '';
        } else {
            adjustmentLogsValue.innerHTML = '';
            adjustmentLogsWrap.style.display = 'none';
        }
    } else {
        adjustmentValue.innerHTML = '';
        adjustmentWrap.style.display = 'none';
        adjustmentLogsValue.innerHTML = '';
        adjustmentLogsWrap.style.display = 'none';
    }

    if ((b.cancellation_reason || '').trim() !== '') {
        const cancelledBy = (b.cancelled_by_role || '').trim();
        cancellationValue.textContent = cancelledBy
            ? ('Cancelled by ' + cancelledBy.replaceAll('_', ' ') + ': ' + b.cancellation_reason)
            : b.cancellation_reason;
        cancellationWrap.style.display = '';
    } else {
        cancellationValue.textContent = '';
        cancellationWrap.style.display = 'none';
    }

    document.getElementById('viewEditBtn').onclick = function(){
        closeModal('bookingModal');
        openEditModal(b);
    };

    openModal('bookingModal');
}

function resetFormModal(){
    document.getElementById('formTitle').textContent = 'Create Booking';
    document.getElementById('bookingForm').action = @json(route('admin.bookings.store'));
    document.getElementById('methodField').innerHTML = '';
    document.getElementById('submitBtn').textContent = 'Save Booking';

    document.getElementById('f_reference_code').value = '';
    document.getElementById('f_status').value = 'confirmed';
    document.getElementById('f_customer_id').value = '';
    document.getElementById('f_provider_id').value = '';
    document.getElementById('f_service_id').value = '';
    document.getElementById('f_service_option_id').value = '';
    document.getElementById('f_booking_date').value = '';
    document.getElementById('f_contact_phone').value = '';
    document.getElementById('f_time_start').value = '';
    document.getElementById('f_time_end').value = '';
    document.getElementById('f_house_type').value = '';
    document.getElementById('f_price').value = '';
    document.getElementById('f_address').value = '';
}

function openCreateModal(){
    resetFormModal();
    openModal('formModal');
}

function openEditModal(b){
    resetFormModal();

    document.getElementById('formTitle').textContent = 'Edit Booking • ' + (b.reference_code ? b.reference_code : ('#' + b.id));
    document.getElementById('bookingForm').action = '/admin/bookings/' + b.id;
    document.getElementById('methodField').innerHTML = '<input type="hidden" name="_method" value="PUT">';
    document.getElementById('submitBtn').textContent = 'Update Booking';

    document.getElementById('f_reference_code').value = b.reference_code || '';
    document.getElementById('f_status').value = b.status || 'confirmed';
    document.getElementById('f_customer_id').value = b.customer_id || '';
    document.getElementById('f_provider_id').value = b.provider_id || '';
    document.getElementById('f_service_id').value = b.service_id || '';
    document.getElementById('f_service_option_id').value = b.service_option_id || '';
    document.getElementById('f_booking_date').value = b.booking_date || '';
    document.getElementById('f_contact_phone').value = b.contact_phone || '';
    document.getElementById('f_time_start').value = normalizeTime(b.time_start);
    document.getElementById('f_time_end').value = normalizeTime(b.time_end);
    document.getElementById('f_house_type').value = b.house_type || '';
    document.getElementById('f_price').value = b.price || '';
    document.getElementById('f_address').value = b.address || '';

    openModal('formModal');
}

document.addEventListener('keydown', function(e){
    if(e.key === 'Escape'){
        closeModal('bookingModal');
        closeModal('formModal');
    }
});
</script>
@endsection
