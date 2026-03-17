@extends('admin.layouts.app')

@section('title', 'Manage Providers')

@section('content')

@php
    use Carbon\Carbon;

    $providers = collect($providers ?? [])->sortByDesc(function ($p) {
        return $p->created_at ?? now();
    })->values();

    $approvedProviders = $providers->filter(function ($p) {
        return strtolower(trim((string)($p->status ?? 'pending'))) === 'approved';
    })->values();

    $countAll = $providers->count();
    $countApproved = $providers->filter(fn($p) => strtolower(trim((string)($p->status ?? ''))) === 'approved')->count();
    $countPending = $providers->filter(fn($p) => in_array(strtolower(trim((string)($p->status ?? 'pending'))), ['pending', 'unapproved']))->count();
    $countRejected = $providers->filter(fn($p) => strtolower(trim((string)($p->status ?? ''))) === 'rejected')->count();
    $countSuspended = $providers->filter(fn($p) => strtolower(trim((string)($p->status ?? ''))) === 'suspended')->count();
@endphp

<style>
:root{
    --bg:#020617;
    --panel:#071122;
    --panel-2:#0b1730;
    --panel-3:#091427;
    --card:rgba(255,255,255,.03);
    --card-2:rgba(255,255,255,.02);
    --line:rgba(255,255,255,.08);
    --line-soft:rgba(255,255,255,.05);

    --text:#f8fafc;
    --muted:#94a3b8;
    --muted-2:#cbd5e1;

    --accent:#38bdf8;
    --accent-soft:rgba(56,189,248,.14);

    --success:#22c55e;
    --success-soft:rgba(34,197,94,.14);

    --warn:#facc15;
    --warn-soft:rgba(250,204,21,.14);

    --danger:#ef4444;
    --danger-soft:rgba(239,68,68,.14);

    --radius:18px;
    --radius-sm:12px;
    --shadow:0 12px 30px rgba(0,0,0,.28);
}

.wrap{
    padding:18px 14px 24px;
}

.page-shell{
    max-width:100%;
}

.head{
    display:flex;
    justify-content:space-between;
    align-items:flex-end;
    gap:12px;
    flex-wrap:wrap;
    margin-bottom:14px;
}

.head h4{
    margin:0;
    color:var(--text);
    font-size:2rem;
    font-weight:900;
    line-height:1.05;
    letter-spacing:-.03em;
}

.head-sub{
    margin-top:6px;
    color:var(--muted);
    font-size:.98rem;
    max-width:900px;
}

.stats{
    display:grid;
    grid-template-columns:repeat(5, minmax(0, 1fr));
    gap:10px;
    margin-bottom:14px;
}

.stat-card{
    background:linear-gradient(180deg, rgba(11,23,48,.94), rgba(6,14,28,.98));
    border:1px solid var(--line);
    border-radius:16px;
    padding:14px 14px 12px;
    box-shadow:var(--shadow);
    min-height:84px;
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
    color:#fff;
    font-size:1.55rem;
    font-weight:950;
    line-height:1;
}

.toolbar{
    display:flex;
    justify-content:space-between;
    align-items:center;
    gap:12px;
    flex-wrap:wrap;
    margin-bottom:14px;
}

.toolbar-left,
.toolbar-right{
    display:flex;
    align-items:center;
    gap:10px;
    flex-wrap:wrap;
}

.search-wrap,
.filter-wrap{
    position:relative;
}

.search,
.filter-select{
    height:48px;
    background:linear-gradient(180deg, rgba(2,6,23,.92), rgba(4,10,24,.98));
    border:1px solid rgba(255,255,255,.10);
    color:var(--text);
    border-radius:14px;
    padding:.8rem 1rem;
    outline:none;
    transition:.2s ease;
    box-shadow:none;
}

.search{
    min-width:320px;
}

.filter-select{
    min-width:190px;
    appearance:none;
    -webkit-appearance:none;
    -moz-appearance:none;
    padding-right:2.8rem;
    background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='18' height='18' viewBox='0 0 24 24' fill='none' stroke='%23cbd5e1' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
    background-repeat:no-repeat;
    background-position:right .9rem center;
    background-size:16px;
}

.search:focus,
.filter-select:focus{
    border-color:rgba(56,189,248,.55);
    box-shadow:0 0 0 4px rgba(56,189,248,.10);
}

.search::placeholder{
    color:rgba(203,213,225,.48);
}

.btnx{
    height:36px;
    min-height:36px;
    display:inline-flex !important;
    align-items:center;
    justify-content:center;
    padding:.45rem .8rem !important;
    border-radius:10px !important;
    font-size:.75rem !important;
    font-weight:900 !important;
    letter-spacing:.01em;
    white-space:nowrap;
    line-height:1 !important;
    box-shadow:none !important;
}

.btn-outline-secondary.btnx{
    border-color:rgba(255,255,255,.16) !important;
    color:#e2e8f0 !important;
    background:rgba(255,255,255,.02) !important;
}
.btn-outline-secondary.btnx:hover{
    background:rgba(255,255,255,.08) !important;
    border-color:rgba(255,255,255,.22) !important;
    color:#fff !important;
}

.btn-outline-info.btnx{
    border-color:rgba(56,189,248,.45) !important;
    color:#7dd3fc !important;
    background:rgba(56,189,248,.05) !important;
}
.btn-outline-info.btnx:hover{
    background:rgba(56,189,248,.14) !important;
    color:#dff6ff !important;
}

.btn-outline-success.btnx{
    border-color:rgba(34,197,94,.45) !important;
    color:#86efac !important;
    background:rgba(34,197,94,.05) !important;
}
.btn-outline-success.btnx:hover{
    background:rgba(34,197,94,.14) !important;
    color:#ecfdf5 !important;
}

.btn-outline-danger.btnx{
    border-color:rgba(239,68,68,.45) !important;
    color:#fca5a5 !important;
    background:rgba(239,68,68,.05) !important;
}
.btn-outline-danger.btnx:hover{
    background:rgba(239,68,68,.14) !important;
    color:#fff1f2 !important;
}

.btn-outline-warning.btnx{
    border-color:rgba(250,204,21,.45) !important;
    color:#fde68a !important;
    background:rgba(250,204,21,.05) !important;
}
.btn-outline-warning.btnx:hover{
    background:rgba(250,204,21,.14) !important;
    color:#fffbea !important;
}

.panel{
    background:linear-gradient(180deg, rgba(9,20,39,.97), rgba(5,13,26,.99));
    border:1px solid var(--line);
    border-radius:var(--radius);
    overflow:hidden;
    box-shadow:var(--shadow);
}

.table-shell{
    width:100%;
    overflow-x:auto;
    overflow-y:hidden;
    scrollbar-width:thin;
    scrollbar-color:#334155 #0b1220;
}

.table-shell::-webkit-scrollbar{
    height:10px;
    width:10px;
}
.table-shell::-webkit-scrollbar-track{
    background:#0b1220;
    border-radius:999px;
}
.table-shell::-webkit-scrollbar-thumb{
    background:linear-gradient(180deg, #334155, #475569);
    border-radius:999px;
    border:2px solid #0b1220;
}
.table-shell::-webkit-scrollbar-thumb:hover{
    background:linear-gradient(180deg, #475569, #64748b);
}

.table{
    margin:0;
    min-width:1220px;
    border-collapse:separate;
    border-spacing:0;
}

.table,
.table > :not(caption) > * > *{
    background:transparent !important;
}

.table thead th{
    position:sticky;
    top:0;
    z-index:2;
    background:rgba(8,17,34,.98) !important;
    color:#8ea2c5;
    border-bottom:1px solid var(--line);
    font-size:.73rem;
    letter-spacing:.12em;
    text-transform:uppercase;
    font-weight:900;
    padding:.95rem 1rem;
    white-space:nowrap;
    vertical-align:middle;
}

.table td{
    color:rgba(248,250,252,.95);
    border-bottom:1px solid var(--line-soft);
    padding:.9rem 1rem;
    vertical-align:middle;
    white-space:nowrap;
}

.table tbody tr{
    transition:.18s ease;
}

.table tbody tr:hover td{
    background:rgba(56,189,248,.05) !important;
}

.namecell{
    min-width:220px;
}

.provider-main-name{
    color:#fff;
    font-weight:900;
    line-height:1.25;
    font-size:.96rem;
}

.small-date{
    display:block;
    margin-top:5px;
    color:#7f8da6;
    font-size:.76rem;
    font-weight:700;
}

.mutedcell{
    color:#d6deea;
}

.location-cell{
    min-width:230px;
    white-space:normal;
    line-height:1.35;
}

.id-cell{
    min-width:120px;
}

.registered-cell{
    min-width:170px;
}

.status-cell{
    min-width:130px;
}

.actions-cell{
    min-width:220px;
}

.st{
    display:inline-flex;
    align-items:center;
    justify-content:center;
    min-width:108px;
    padding:.42rem .8rem;
    border-radius:999px;
    font-size:.72rem;
    font-weight:950;
    letter-spacing:.08em;
    text-transform:uppercase;
    border:1px solid rgba(255,255,255,.10);
    background:rgba(255,255,255,.03);
}

.st.approved{
    color:#4ade80;
    border-color:rgba(34,197,94,.30);
    background:rgba(34,197,94,.10);
}
.st.pending{
    color:#facc15;
    border-color:rgba(250,204,21,.28);
    background:rgba(250,204,21,.10);
}
.st.rejected{
    color:#f87171;
    border-color:rgba(239,68,68,.28);
    background:rgba(239,68,68,.10);
}
.st.suspended{
    color:#fb7185;
    border-color:rgba(244,63,94,.28);
    background:rgba(244,63,94,.10);
}

.actions{
    display:flex;
    justify-content:flex-end;
    align-items:center;
    gap:6px;
    flex-wrap:wrap;
}

.actions form{
    margin:0;
}

.mobile-list{
    display:none;
}

.provider-item{
    padding:14px;
    border-bottom:1px solid var(--line-soft);
    background:transparent;
}

.provider-item:last-child{
    border-bottom:none;
}

.provider-top{
    display:flex;
    justify-content:space-between;
    align-items:flex-start;
    gap:10px;
}

.provider-name{
    color:#fff;
    font-weight:900;
    font-size:1rem;
    line-height:1.25;
    word-break:break-word;
}

.provider-sub{
    margin-top:4px;
    color:var(--muted);
    font-size:.9rem;
    line-height:1.4;
    word-break:break-word;
}

.provider-meta{
    margin-top:12px;
    display:grid;
    grid-template-columns:repeat(2, minmax(0,1fr));
    gap:10px;
}

.meta{
    border:1px solid var(--line);
    background:rgba(255,255,255,.02);
    border-radius:14px;
    padding:10px 11px;
}

.meta .k{
    color:#8ea2c5;
    font-size:.68rem;
    letter-spacing:.12em;
    text-transform:uppercase;
    font-weight:900;
    line-height:1.2;
}

.meta .v{
    margin-top:6px;
    color:#f8fafc;
    font-weight:800;
    font-size:.92rem;
    line-height:1.4;
    word-break:break-word;
}

.provider-actions{
    margin-top:12px;
    display:flex;
    gap:8px;
    flex-wrap:wrap;
}

.provider-actions form{
    margin:0;
}

.empty-state{
    padding:36px 20px;
    text-align:center;
    color:rgba(255,255,255,.58);
    font-weight:800;
}

.modal-content{
    background:linear-gradient(180deg, #0c172d, #06111f);
    border:1px solid rgba(255,255,255,.09);
    border-radius:18px;
    color:var(--text);
    overflow:hidden;
    box-shadow:0 18px 40px rgba(0,0,0,.4);
}

.modal-header-ct{
    position:sticky;
    top:0;
    z-index:5;
    background:linear-gradient(180deg, rgba(12,23,45,.98), rgba(6,17,31,.98));
    border-bottom:1px solid rgba(255,255,255,.08);
    padding:1rem 1.2rem;
}

.modal-body-ct{
    padding:1rem 1.2rem 1.2rem;
    max-height:78vh;
    overflow-y:auto;
    overflow-x:hidden;
    scrollbar-width:thin;
    scrollbar-color:#334155 #0b1220;
}

.modal-body-ct::-webkit-scrollbar{
    width:10px;
}
.modal-body-ct::-webkit-scrollbar-track{
    background:#0b1220;
}
.modal-body-ct::-webkit-scrollbar-thumb{
    background:#334155;
    border-radius:999px;
    border:2px solid #0b1220;
}

.modal-label{
    font-size:.69rem;
    letter-spacing:.12em;
    text-transform:uppercase;
    color:#8ea2c5;
    font-weight:900;
    margin-bottom:6px;
}

.modal-value{
    color:#fff;
    font-weight:700;
    word-break:break-word;
    line-height:1.45;
}

.detail-card{
    height:100%;
    border:1px solid rgba(255,255,255,.06);
    background:rgba(255,255,255,.025);
    border-radius:14px;
    padding:12px;
}

.section-mini-title{
    color:#dbe7f5;
    font-size:.78rem;
    font-weight:900;
    letter-spacing:.1em;
    text-transform:uppercase;
    margin:8px 0 8px;
}

.modal .btn-close{
    filter:invert(1);
    opacity:.82;
}
.modal .btn-close:hover{
    opacity:1;
}

.doc-thumb{
    display:block;
    max-width:100%;
    margin-bottom:12px;
}
.doc-thumb img{
    max-height:260px;
    border-color:rgba(255,255,255,.10)!important;
    background:#0f172a;
    object-fit:contain;
}

.doc-box{
    border:1px solid rgba(255,255,255,.08);
    background:rgba(255,255,255,.03);
    border-radius:14px;
    padding:14px;
}

.doc-actions{
    display:flex;
    align-items:center;
    gap:10px;
    flex-wrap:wrap;
}

.file-note{
    color:rgba(255,255,255,.58);
    font-size:.85rem;
}

.print-approved-wrap{
    display:none;
}

.preview-modal-body{
    min-height:240px;
}

@media (max-width: 1200px){
    .stats{
        grid-template-columns:repeat(3, minmax(0,1fr));
    }
}

@media (max-width: 992px){
    .wrap{
        padding:16px 12px 22px;
    }

    .head h4{
        font-size:1.7rem;
    }

    .stats{
        grid-template-columns:repeat(2, minmax(0,1fr));
    }

    .search{
        min-width:260px;
    }
}

@media (max-width: 768px){
    .head{
        margin-bottom:12px;
    }

    .head h4{
        font-size:1.45rem;
    }

    .head-sub{
        font-size:.92rem;
    }

    .toolbar{
        align-items:stretch;
    }

    .toolbar-left,
    .toolbar-right{
        width:100%;
    }

    .toolbar-left{
        display:grid;
        grid-template-columns:1fr;
        gap:10px;
    }

    .toolbar-right .btn{
        width:100%;
    }

    .search,
    .filter-select{
        width:100%;
        min-width:unset;
    }

    .table-shell{
        display:none;
    }

    .mobile-list{
        display:block;
    }

    .provider-meta{
        grid-template-columns:1fr;
    }

    .provider-actions .btn,
    .provider-actions form{
        width:100%;
    }

    .provider-actions form .btn{
        width:100%;
    }
}

@media (max-width: 576px){
    .stats{
        grid-template-columns:1fr;
    }

    .stat-card{
        min-height:76px;
        padding:12px;
    }

    .stat-v{
        font-size:1.35rem;
    }

    .provider-top{
        flex-direction:column;
        align-items:flex-start;
    }

    .modal-dialog{
        margin:.45rem;
    }

    .modal-header-ct{
        padding:.9rem 1rem;
    }

    .modal-body-ct{
        padding:.9rem 1rem 1rem;
        max-height:74vh;
    }

    .detail-card{
        padding:10px;
    }
}

@media print{
    body *{
        visibility:hidden !important;
    }

    .print-approved-wrap,
    .print-approved-wrap *{
        visibility:visible !important;
    }

    .print-approved-wrap{
        display:block !important;
        position:absolute;
        inset:0;
        width:100%;
        background:#fff !important;
        color:#000 !important;
        padding:20px;
    }

    .print-approved-title{
        font-size:22px;
        font-weight:800;
        margin-bottom:6px;
        color:#000 !important;
    }

    .print-approved-sub{
        font-size:13px;
        margin-bottom:18px;
        color:#333 !important;
    }

    .print-approved-table{
        width:100%;
        border-collapse:collapse;
        font-size:12px;
    }

    .print-approved-table th,
    .print-approved-table td{
        border:1px solid #999;
        padding:8px 10px;
        text-align:left;
        color:#000 !important;
        white-space:normal;
    }

    .print-approved-table th{
        background:#f1f5f9 !important;
        font-weight:700;
    }
}
</style>

<div class="wrap container-fluid">
    <div class="page-shell">

        <div class="head">
            <div>
                <h4>Service Providers</h4>
                <div class="head-sub"></div>
            </div>
        </div>

        <div class="stats">
            <div class="stat-card">
                <div class="stat-k">All Providers</div>
                <div class="stat-v">{{ $countAll }}</div>
            </div>
            <div class="stat-card">
                <div class="stat-k">Approved</div>
                <div class="stat-v">{{ $countApproved }}</div>
            </div>
            <div class="stat-card">
                <div class="stat-k">Pending</div>
                <div class="stat-v">{{ $countPending }}</div>
            </div>
            <div class="stat-card">
                <div class="stat-k">Rejected</div>
                <div class="stat-v">{{ $countRejected }}</div>
            </div>
            <div class="stat-card">
                <div class="stat-k">Suspended</div>
                <div class="stat-v">{{ $countSuspended }}</div>
            </div>
        </div>

        <div class="toolbar">
            <div class="toolbar-left">
                <div class="search-wrap">
                    <input type="text" id="providerSearch" class="search" placeholder="Search provider, email, city, province...">
                </div>

                <div class="filter-wrap">
                    <select id="statusFilter" class="filter-select">
                        <option value="all">All Status</option>
                        <option value="pending">Pending / Unapproved</option>
                        <option value="approved">Approved</option>
                        <option value="rejected">Rejected</option>
                        <option value="suspended">Suspended</option>
                    </select>
                </div>
            </div>

            <div class="toolbar-right">
                <button type="button" class="btn btn-outline-secondary btnx" id="printApprovedBtn">
                    Print Approved Providers
                </button>
            </div>
        </div>

        <div class="panel">

            <div class="table-shell">
                <table class="table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Location</th>
                            <th>ID Type</th>
                            <th>Registered</th>
                            <th class="text-center">Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>

                    <tbody id="providerTableDesktop">
                        @forelse($providers as $p)
                            @php
                                $rawStatus = strtolower(trim((string)($p->status ?? 'pending')));
                                $st = in_array($rawStatus, ['pending', 'approved', 'rejected', 'suspended', 'unapproved'])
                                    ? $rawStatus
                                    : 'pending';

                                $normalizedStatus = $st === 'unapproved' ? 'pending' : $st;

                                $stClass = match($st) {
                                    'approved' => 'approved',
                                    'rejected' => 'rejected',
                                    'suspended' => 'suspended',
                                    default => 'pending',
                                };

                                $statusLabel = match($st) {
                                    'approved' => 'Approved',
                                    'rejected' => 'Rejected',
                                    'suspended' => 'Suspended',
                                    default => 'Pending',
                                };

                                $isApproved = $st === 'approved';

                                $fullName = trim(
                                    ($p->first_name ?? '') . ' ' .
                                    ($p->middle_name ?? '') . ' ' .
                                    ($p->last_name ?? '') . ' ' .
                                    ($p->suffix ?? '')
                                );

                                $createdDisplay = $p->created_at
                                    ? Carbon::parse($p->created_at)->format('M d, Y h:i A')
                                    : '—';

                                $locationDisplay = collect([$p->city, $p->province])->filter()->implode(', ');
                            @endphp

                            <tr class="provider-row provider-row-desktop"
                                data-status="{{ $normalizedStatus }}"
                                data-search="{{ strtolower(trim($fullName.' '.$p->email.' '.$p->city.' '.$p->province.' '.$p->id_type.' '.$statusLabel)) }}">
                                <td class="namecell">
                                    <div class="provider-main-name">{{ $fullName ?: '—' }}</div>
                                    <span class="small-date">New registration: {{ $createdDisplay }}</span>
                                </td>

                                <td class="mutedcell">{{ $p->email ?: '—' }}</td>

                                <td class="location-cell">
                                    {{ $locationDisplay ?: '—' }}
                                </td>

                                <td class="id-cell">{{ $p->id_type ?: '—' }}</td>

                                <td class="registered-cell">{{ $createdDisplay }}</td>

                                <td class="text-center status-cell">
                                    <span class="st {{ $stClass }}">{{ $statusLabel }}</span>
                                </td>

                                <td class="text-end actions-cell">
                                    <div class="actions">
                                        <button class="btn btn-outline-info btnx"
                                                type="button"
                                                data-bs-toggle="modal"
                                                data-bs-target="#viewProvider{{ $p->id }}">
                                            View
                                        </button>

                                        @if($isApproved)
                                            <form method="POST" action="{{ route('admin.providers.unapprove', $p->id) }}">
                                                @csrf
                                                <button type="submit" class="btn btn-outline-warning btnx">Unapprove</button>
                                            </form>
                                        @else
                                            <form method="POST" action="{{ route('admin.providers.approve', $p->id) }}">
                                                @csrf
                                                <button type="submit" class="btn btn-outline-success btnx">Approve</button>
                                            </form>

                                            <form method="POST" action="{{ route('admin.providers.reject', $p->id) }}">
                                                @csrf
                                                <button type="submit" class="btn btn-outline-danger btnx">Reject</button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr id="noProviderDesktopRow">
                                <td colspan="7" class="text-center text-white-50 py-5">No providers found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mobile-list" id="providerListMobile">
                @forelse($providers as $p)
                    @php
                        $rawStatus = strtolower(trim((string)($p->status ?? 'pending')));
                        $st = in_array($rawStatus, ['pending', 'approved', 'rejected', 'suspended', 'unapproved'])
                            ? $rawStatus
                            : 'pending';

                        $normalizedStatus = $st === 'unapproved' ? 'pending' : $st;

                        $stClass = match($st) {
                            'approved' => 'approved',
                            'rejected' => 'rejected',
                            'suspended' => 'suspended',
                            default => 'pending',
                        };

                        $statusLabel = match($st) {
                            'approved' => 'Approved',
                            'rejected' => 'Rejected',
                            'suspended' => 'Suspended',
                            default => 'Pending',
                        };

                        $isApproved = $st === 'approved';

                        $fullName = trim(
                            ($p->first_name ?? '') . ' ' .
                            ($p->middle_name ?? '') . ' ' .
                            ($p->last_name ?? '') . ' ' .
                            ($p->suffix ?? '')
                        );

                        $createdDisplay = $p->created_at
                            ? Carbon::parse($p->created_at)->format('M d, Y h:i A')
                            : '—';

                        $locationDisplay = collect([$p->city, $p->province])->filter()->implode(', ');
                    @endphp

                    <div class="provider-item provider-row provider-row-mobile"
                         data-status="{{ $normalizedStatus }}"
                         data-search="{{ strtolower(trim($fullName.' '.$p->email.' '.$p->city.' '.$p->province.' '.$p->id_type.' '.$statusLabel)) }}">
                        <div class="provider-top">
                            <div style="min-width:0;">
                                <div class="provider-name">{{ $fullName ?: '—' }}</div>
                                <div class="provider-sub">{{ $p->email ?: '—' }}</div>
                                <div class="provider-sub">Registered: {{ $createdDisplay }}</div>
                            </div>

                            <span class="st {{ $stClass }}">{{ $statusLabel }}</span>
                        </div>

                        <div class="provider-meta">
                            <div class="meta">
                                <div class="k">Location</div>
                                <div class="v">{{ $locationDisplay ?: '—' }}</div>
                            </div>

                            <div class="meta">
                                <div class="k">ID Type</div>
                                <div class="v">{{ $p->id_type ?: '—' }}</div>
                            </div>
                        </div>

                        <div class="provider-actions">
                            <button class="btn btn-outline-info btnx"
                                    type="button"
                                    data-bs-toggle="modal"
                                    data-bs-target="#viewProvider{{ $p->id }}">
                                View
                            </button>

                            @if($isApproved)
                                <form method="POST" action="{{ route('admin.providers.unapprove', $p->id) }}">
                                    @csrf
                                    <button type="submit" class="btn btn-outline-warning btnx">Unapprove</button>
                                </form>
                            @else
                                <form method="POST" action="{{ route('admin.providers.approve', $p->id) }}">
                                    @csrf
                                    <button type="submit" class="btn btn-outline-success btnx">Approve</button>
                                </form>

                                <form method="POST" action="{{ route('admin.providers.reject', $p->id) }}">
                                    @csrf
                                    <button type="submit" class="btn btn-outline-danger btnx">Reject</button>
                                </form>
                            @endif
                        </div>
                    </div>
                @empty
                    <div id="noProviderMobileRow" class="provider-item text-center text-white-50 py-4">No providers found.</div>
                @endforelse
            </div>

            <div id="noResultsMessage" class="empty-state" style="display:none;">
                No providers match the current search or status filter.
            </div>

        </div>
    </div>
</div>

<div class="print-approved-wrap">
    <div class="print-approved-title">Approved Service Providers</div>
    <div class="print-approved-sub">
        Generated on {{ now()->format('M d, Y h:i A') }} |
        Total Approved Providers: {{ $approvedProviders->count() }}
    </div>

    <table class="print-approved-table">
        <thead>
            <tr>
                <th>#</th>
                <th>Full Name</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Location</th>
                <th>ID Type</th>
                <th>Registered At</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($approvedProviders as $index => $p)
                @php
                    $fullName = trim(
                        ($p->first_name ?? '') . ' ' .
                        ($p->middle_name ?? '') . ' ' .
                        ($p->last_name ?? '') . ' ' .
                        ($p->suffix ?? '')
                    );
                @endphp
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $fullName ?: '—' }}</td>
                    <td>{{ $p->email ?: '—' }}</td>
                    <td>{{ $p->phone ?: '—' }}</td>
                    <td>{{ collect([$p->barangay, $p->city, $p->province, $p->region])->filter()->implode(', ') ?: '—' }}</td>
                    <td>{{ $p->id_type ?: '—' }}</td>
                    <td>{{ $p->created_at ? \Carbon\Carbon::parse($p->created_at)->format('M d, Y h:i A') : '—' }}</td>
                    <td>{{ $p->status ?: 'Approved' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8">No approved providers found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@foreach($providers as $p)
    @php
        $docUrl = $p->id_image ? route('admin.providers.document', $p->id) : null;
        $rawDocName = trim((string) ($p->id_image ?? ''));
        $docExt = $rawDocName !== ''
            ? strtolower(pathinfo(parse_url($rawDocName, PHP_URL_PATH) ?? $rawDocName, PATHINFO_EXTENSION))
            : null;

        $imageExts = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'svg', 'avif'];
        $isImage = $docExt && in_array($docExt, $imageExts, true);
        $isPdf = $docExt === 'pdf';
        $isPossiblyUnsupportedImage = in_array($docExt, ['heic', 'heif'], true);

        $fullName = trim(
            ($p->first_name ?? '') . ' ' .
            ($p->middle_name ?? '') . ' ' .
            ($p->last_name ?? '') . ' ' .
            ($p->suffix ?? '')
        );
    @endphp

    <div class="modal fade" id="viewProvider{{ $p->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">

                <div class="modal-header-ct d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-1" style="font-weight:950;">Provider Details</h5>
                        <div class="text-white-50 small">Complete account and document information</div>
                    </div>
                    <button class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body-ct">
                    <div class="row g-3">

                        <div class="col-12 col-md-6 col-lg-4">
                            <div class="detail-card">
                                <div class="modal-label">Full Name</div>
                                <div class="modal-value">{{ $fullName ?: '—' }}</div>
                            </div>
                        </div>

                        <div class="col-12 col-md-6 col-lg-4">
                            <div class="detail-card">
                                <div class="modal-label">Email</div>
                                <div class="modal-value">{{ $p->email ?: '—' }}</div>
                            </div>
                        </div>

                        <div class="col-12 col-md-6 col-lg-4">
                            <div class="detail-card">
                                <div class="modal-label">Phone</div>
                                <div class="modal-value">{{ $p->phone ?: '—' }}</div>
                            </div>
                        </div>

                        <div class="col-12 col-sm-6 col-lg-3">
                            <div class="detail-card">
                                <div class="modal-label">First Name</div>
                                <div class="modal-value">{{ $p->first_name ?: '—' }}</div>
                            </div>
                        </div>

                        <div class="col-12 col-sm-6 col-lg-3">
                            <div class="detail-card">
                                <div class="modal-label">Middle Name</div>
                                <div class="modal-value">{{ $p->middle_name ?: '—' }}</div>
                            </div>
                        </div>

                        <div class="col-12 col-sm-6 col-lg-3">
                            <div class="detail-card">
                                <div class="modal-label">Last Name</div>
                                <div class="modal-value">{{ $p->last_name ?: '—' }}</div>
                            </div>
                        </div>

                        <div class="col-12 col-sm-6 col-lg-3">
                            <div class="detail-card">
                                <div class="modal-label">Suffix</div>
                                <div class="modal-value">{{ $p->suffix ?: '—' }}</div>
                            </div>
                        </div>

                        <div class="col-12 col-sm-6 col-lg-3">
                            <div class="detail-card">
                                <div class="modal-label">Citizenship</div>
                                <div class="modal-value">{{ $p->citizenship ?: '—' }}</div>
                            </div>
                        </div>

                        <div class="col-12 col-sm-6 col-lg-3">
                            <div class="detail-card">
                                <div class="modal-label">Gender</div>
                                <div class="modal-value">{{ $p->gender ?: '—' }}</div>
                            </div>
                        </div>

                        <div class="col-12 col-sm-6 col-lg-3">
                            <div class="detail-card">
                                <div class="modal-label">Civil Status</div>
                                <div class="modal-value">{{ $p->civil_status ?: '—' }}</div>
                            </div>
                        </div>

                        <div class="col-12 col-sm-6 col-lg-3">
                            <div class="detail-card">
                                <div class="modal-label">Date of Birth</div>
                                <div class="modal-value">{{ $p->date_of_birth ?: '—' }}</div>
                            </div>
                        </div>

                        <div class="col-12 col-sm-6 col-lg-3">
                            <div class="detail-card">
                                <div class="modal-label">Stateless</div>
                                <div class="modal-value">{{ (int)($p->is_stateless ?? 0) === 1 ? 'Yes' : 'No' }}</div>
                            </div>
                        </div>

                        <div class="col-12 col-sm-6 col-lg-3">
                            <div class="detail-card">
                                <div class="modal-label">Refugee</div>
                                <div class="modal-value">{{ (int)($p->is_refugee ?? 0) === 1 ? 'Yes' : 'No' }}</div>
                            </div>
                        </div>

                        <div class="col-12 col-sm-6 col-lg-3">
                            <div class="detail-card">
                                <div class="modal-label">Verified</div>
                                <div class="modal-value">{{ (int)($p->is_verified ?? 0) === 1 ? 'Yes' : 'No' }}</div>
                            </div>
                        </div>

                        <div class="col-12 col-sm-6 col-lg-3">
                            <div class="detail-card">
                                <div class="modal-label">Status</div>
                                <div class="modal-value">{{ $p->status ?: '—' }}</div>
                            </div>
                        </div>

                        <div class="col-12 col-sm-6 col-lg-3">
                            <div class="detail-card">
                                <div class="modal-label">ID Type</div>
                                <div class="modal-value">{{ $p->id_type ?: '—' }}</div>
                            </div>
                        </div>

                        <div class="col-12 col-sm-6 col-lg-3">
                            <div class="detail-card">
                                <div class="modal-label">Country</div>
                                <div class="modal-value">{{ $p->country ?: 'Philippines' }}</div>
                            </div>
                        </div>

                        <div class="col-12 col-sm-6 col-lg-3">
                            <div class="detail-card">
                                <div class="modal-label">Region</div>
                                <div class="modal-value">{{ $p->region ?: '—' }}</div>
                            </div>
                        </div>

                        <div class="col-12 col-sm-6 col-lg-3">
                            <div class="detail-card">
                                <div class="modal-label">Province</div>
                                <div class="modal-value">{{ $p->province ?: '—' }}</div>
                            </div>
                        </div>

                        <div class="col-12 col-md-6 col-lg-4">
                            <div class="detail-card">
                                <div class="modal-label">City / Municipality</div>
                                <div class="modal-value">{{ $p->city ?: '—' }}</div>
                            </div>
                        </div>

                        <div class="col-12 col-md-6 col-lg-4">
                            <div class="detail-card">
                                <div class="modal-label">Barangay</div>
                                <div class="modal-value">{{ $p->barangay ?: '—' }}</div>
                            </div>
                        </div>

                        <div class="col-12 col-md-6 col-lg-4">
                            <div class="detail-card">
                                <div class="modal-label">Emergency Contact Name</div>
                                <div class="modal-value">{{ $p->emergency_name ?: '—' }}</div>
                            </div>
                        </div>

                        <div class="col-12 col-md-6 col-lg-4">
                            <div class="detail-card">
                                <div class="modal-label">Emergency Contact Number</div>
                                <div class="modal-value">{{ $p->emergency_phone ?: '—' }}</div>
                            </div>
                        </div>

                        <div class="col-12 col-md-6 col-lg-4">
                            <div class="detail-card">
                                <div class="modal-label">Created At</div>
                                <div class="modal-value">{{ $p->created_at ?: '—' }}</div>
                            </div>
                        </div>

                        <div class="col-12 col-md-6 col-lg-4">
                            <div class="detail-card">
                                <div class="modal-label">Updated At</div>
                                <div class="modal-value">{{ $p->updated_at ?: '—' }}</div>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="detail-card">
                                <div class="modal-label">Full Address</div>
                                <div class="modal-value">
                                    {{ $p->address ?: '—' }}
                                    @if($p->barangay || $p->city || $p->province || $p->region)
                                        <div class="mt-2 text-white-50 small">
                                            {{ collect([$p->barangay, $p->city, $p->province, $p->region])->filter()->implode(', ') }}
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="section-mini-title">Uploaded ID Document</div>

                            @if($p->id_image && $docUrl)

                                @if($isImage)
                                    <div class="doc-box">
                                        <div class="doc-thumb">
                                            <img
                                                src="{{ $docUrl }}"
                                                alt="Uploaded ID Document"
                                                class="img-fluid rounded border"
                                            >
                                        </div>

                                        <div class="doc-actions">
                                            <button
                                                type="button"
                                                class="btn btn-outline-secondary btnx"
                                                data-bs-toggle="modal"
                                                data-bs-target="#documentPreviewModal"
                                                data-doc-url="{{ $docUrl }}"
                                                data-doc-type="image">
                                                Preview Image
                                            </button>

                                            <a href="{{ $docUrl }}"
                                               target="_blank"
                                               class="btn btn-outline-info btnx">
                                                Open Image
                                            </a>

                                            <span class="file-note">Image document uploaded</span>
                                        </div>
                                    </div>

                                @elseif($isPossiblyUnsupportedImage)
                                    <div class="doc-box">
                                        <div class="doc-actions">
                                            <a href="{{ $docUrl }}"
                                               target="_blank"
                                               class="btn btn-outline-info btnx">
                                                Open File
                                            </a>

                                            <span class="file-note">
                                                This image format ({{ strtoupper($docExt) }}) may not preview in-browser. Open it in a new tab or convert it to JPG/PNG on upload.
                                            </span>
                                        </div>
                                    </div>

                                @elseif($isPdf)
                                    <div class="doc-box">
                                        <div class="doc-actions">
                                            <button
                                                type="button"
                                                class="btn btn-outline-secondary btnx"
                                                data-bs-toggle="modal"
                                                data-bs-target="#documentPreviewModal"
                                                data-doc-url="{{ $docUrl }}"
                                                data-doc-type="pdf">
                                                Preview PDF
                                            </button>

                                            <a href="{{ $docUrl }}"
                                               target="_blank"
                                               class="btn btn-outline-info btnx">
                                                Open PDF
                                            </a>

                                            <span class="file-note">PDF document uploaded</span>
                                        </div>
                                    </div>

                                @else
                                    <div class="doc-box">
                                        <div class="doc-actions">
                                            <button
                                                type="button"
                                                class="btn btn-outline-secondary btnx"
                                                data-bs-toggle="modal"
                                                data-bs-target="#documentPreviewModal"
                                                data-doc-url="{{ $docUrl }}"
                                                data-doc-type="file">
                                                Try Preview
                                            </button>

                                            <a href="{{ $docUrl }}"
                                               target="_blank"
                                               class="btn btn-outline-info btnx">
                                                Open File
                                            </a>

                                            <span class="file-note">
                                                File type: {{ strtoupper($docExt ?: 'UNKNOWN') }}
                                            </span>
                                        </div>
                                    </div>
                                @endif

                            @else
                                <div class="text-white-50">No document uploaded</div>
                            @endif
                        </div>

                    </div>
                </div>

            </div>
        </div>
    </div>
@endforeach

<div class="modal fade" id="documentPreviewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content p-3">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h6 class="mb-0" style="font-weight:950;">ID Document Preview</h6>
                <button class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="text-center preview-modal-body">
                <img
                    id="previewImage"
                    class="img-fluid rounded d-none"
                    style="max-height:80vh"
                    alt="Document Preview"
                >

                <iframe
                    id="previewPdf"
                    class="w-100 d-none"
                    style="height:80vh; border:0; border-radius:12px;"
                ></iframe>

                <div id="previewFallback" class="d-none">
                    <p class="text-white-50 mb-3">Preview is not available for this file type.</p>
                    <a id="previewFallbackLink" href="#" target="_blank" class="btn btn-outline-info btnx">
                        Open File
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    const providerSearch = document.getElementById('providerSearch');
    const statusFilter = document.getElementById('statusFilter');
    const noResultsMessage = document.getElementById('noResultsMessage');
    const noProviderDesktopRow = document.getElementById('noProviderDesktopRow');
    const noProviderMobileRow = document.getElementById('noProviderMobileRow');

    function getActiveRows() {
        const isMobile = window.innerWidth <= 768;
        return document.querySelectorAll(isMobile ? '.provider-row-mobile' : '.provider-row-desktop');
    }

    function applyProviderFilters() {
        const searchValue = (providerSearch?.value || '').toLowerCase().trim();
        const selectedStatus = (statusFilter?.value || 'all').toLowerCase();

        const desktopRows = document.querySelectorAll('.provider-row-desktop');
        const mobileRows = document.querySelectorAll('.provider-row-mobile');

        let desktopVisible = 0;
        let mobileVisible = 0;

        desktopRows.forEach(function (row) {
            const rowSearch = (row.dataset.search || '').toLowerCase();
            const rowStatus = (row.dataset.status || '').toLowerCase();

            const matchSearch = !searchValue || rowSearch.includes(searchValue);
            const matchStatus = selectedStatus === 'all' || rowStatus === selectedStatus;
            const isVisible = matchSearch && matchStatus;

            row.style.display = isVisible ? '' : 'none';
            if (isVisible) desktopVisible++;
        });

        mobileRows.forEach(function (row) {
            const rowSearch = (row.dataset.search || '').toLowerCase();
            const rowStatus = (row.dataset.status || '').toLowerCase();

            const matchSearch = !searchValue || rowSearch.includes(searchValue);
            const matchStatus = selectedStatus === 'all' || rowStatus === selectedStatus;
            const isVisible = matchSearch && matchStatus;

            row.style.display = isVisible ? '' : 'none';
            if (isVisible) mobileVisible++;
        });

        if (noProviderDesktopRow) {
            noProviderDesktopRow.style.display = desktopRows.length === 0 ? '' : 'none';
        }

        if (noProviderMobileRow) {
            noProviderMobileRow.style.display = mobileRows.length === 0 ? '' : 'none';
        }

        const isMobile = window.innerWidth <= 768;
        const visibleCount = isMobile ? mobileVisible : desktopVisible;

        if (noResultsMessage) {
            const hasRealRows = isMobile ? mobileRows.length > 0 : desktopRows.length > 0;
            noResultsMessage.style.display = hasRealRows && visibleCount === 0 ? 'block' : 'none';
        }
    }

    providerSearch?.addEventListener('input', applyProviderFilters);
    statusFilter?.addEventListener('change', applyProviderFilters);
    window.addEventListener('resize', applyProviderFilters);

    document.getElementById('printApprovedBtn')?.addEventListener('click', function () {
        window.print();
    });

    const documentPreviewModal = document.getElementById('documentPreviewModal');

    if (documentPreviewModal) {
        documentPreviewModal.addEventListener('show.bs.modal', function (event) {
            const trigger = event.relatedTarget;
            const url = trigger?.getAttribute('data-doc-url') || '';
            const type = trigger?.getAttribute('data-doc-type') || '';

            const previewImage = document.getElementById('previewImage');
            const previewPdf = document.getElementById('previewPdf');
            const previewFallback = document.getElementById('previewFallback');
            const previewFallbackLink = document.getElementById('previewFallbackLink');

            previewImage.classList.add('d-none');
            previewPdf.classList.add('d-none');
            previewFallback.classList.add('d-none');

            previewImage.removeAttribute('src');
            previewPdf.removeAttribute('src');
            previewFallbackLink.setAttribute('href', url);

            if (type === 'image') {
                previewImage.setAttribute('src', url);
                previewImage.classList.remove('d-none');
            } else if (type === 'pdf') {
                previewPdf.setAttribute('src', url);
                previewPdf.classList.remove('d-none');
            } else {
                previewFallback.classList.remove('d-none');
            }
        });

        documentPreviewModal.addEventListener('hidden.bs.modal', function () {
            const previewImage = document.getElementById('previewImage');
            const previewPdf = document.getElementById('previewPdf');
            const previewFallback = document.getElementById('previewFallback');

            previewImage.classList.add('d-none');
            previewPdf.classList.add('d-none');
            previewFallback.classList.add('d-none');

            previewImage.removeAttribute('src');
            previewPdf.removeAttribute('src');
        });
    }

    applyProviderFilters();
})();
</script>

@endsection