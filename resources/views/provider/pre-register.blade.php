@extends('layouts.app')

@section('title', 'CleanTech - Provider Pre-Registration')

@section('content')
<style>
    :root{
        --ct-bg:#0b1220;
        --ct-surface:#0f172a;
        --ct-border:#1f2a44;
        --ct-text:#e5e7eb;
        --ct-muted:#94a3b8;
        --ct-primary:#3b82f6;
        --ct-primary2:#2563eb;
        --ct-danger:#ef4444;
        --ct-radius:14px;
        --ct-shadow:0 12px 30px rgba(0,0,0,.35);
    }

    .ct-page{
        padding-top: calc(var(--nav-h, 72px) + 16px) !important;
        min-height:100vh;
        padding:22px 14px 28px;
        background:
            radial-gradient(1000px 500px at 15% 0%, rgba(59,130,246,.18), transparent 60%),
            radial-gradient(900px 450px at 85% 10%, rgba(34,197,94,.10), transparent 60%),
            var(--ct-bg);
    }

    .ct-wrap{ max-width:1100px; margin:0 auto; }

    /* TOP */
    .ct-top{
        display:flex; justify-content:space-between; align-items:flex-start;
        gap:10px;
        margin-bottom:14px; color:var(--ct-text);
        flex-wrap:wrap;
    }
    .ct-top-left{
        font-weight:900; display:flex; gap:10px; align-items:center;
        flex-wrap:wrap;
    }
    .ct-chip,.ct-breadcrumb{
        font-size:12px; color:var(--ct-muted);
        border:1px solid var(--ct-border);
        background:rgba(255,255,255,.03);
        padding:6px 10px; border-radius:999px;
        max-width:100%;
        white-space:nowrap;
        overflow:hidden;
        text-overflow:ellipsis;
    }

    /* CARD */
    .ct-box{
        border:1px solid var(--ct-border);
        border-radius:var(--ct-radius);
        background:linear-gradient(180deg, rgba(255,255,255,.03), rgba(255,255,255,.01));
        box-shadow:var(--ct-shadow);
        overflow:hidden;
    }
    .ct-bar{
        padding:14px 14px;
        display:flex; align-items:center; justify-content:space-between;
        gap:10px;
        background:rgba(255,255,255,.03);
        border-bottom:1px solid var(--ct-border);
        color:var(--ct-text);
        font-weight:900;
        letter-spacing:.3px;
        flex-wrap:wrap;
    }
    .ct-step{
        font-weight:800; font-size:12px; color:var(--ct-muted);
        border:1px solid var(--ct-border);
        background:rgba(0,0,0,.15);
        padding:6px 10px; border-radius:999px;
        flex:0 0 auto;
    }

    .ct-body{ padding:14px; color:var(--ct-text); }

    .ct-subbar{
        margin-top:4px;
        padding:12px 12px;
        border:1px solid var(--ct-border);
        border-radius:12px;
        background:rgba(255,255,255,.02);
        font-weight:900;
        letter-spacing:.3px;
    }

    /* FORM */
    .ct-label{
        font-size:12px; font-weight:900; color:var(--ct-text);
        margin-bottom:6px;
    }

    .ct-input{
        height:44px;
        border-radius:10px !important;
        border:1px solid var(--ct-border) !important;
        background:rgba(255,255,255,.03) !important;
        color:var(--ct-text) !important;
        padding: 10px 12px !important;
    }
    textarea.ct-input{ height:auto; min-height:96px; }

    .ct-input:focus{
        outline:none !important;
        box-shadow:0 0 0 .2rem rgba(59,130,246,.18) !important;
        border-color:rgba(59,130,246,.45) !important;
    }

    /* ✅ dropdown readable */
    select.ct-input,
    select.form-control.ct-input{
        color-scheme: dark;
        appearance: auto;
    }
    select.ct-input option{
        background:#0f172a;
        color:#e5e7eb;
    }

    .ct-input:disabled,
    select.ct-input:disabled{
        opacity: .65 !important;
        cursor: not-allowed;
    }

    /* RADIO */
    .ct-radio{
        display:flex; gap:10px; padding-top:8px;
        color:var(--ct-muted); font-size:13px;
        flex-wrap:wrap;
    }
    .ct-radio label{
        display:flex; align-items:center; gap:8px;
        cursor:pointer;
        padding:8px 10px;
        border:1px solid var(--ct-border);
        border-radius:12px;
        background:rgba(255,255,255,.02);
    }
    .ct-radio input{ transform: translateY(1px); }

    /* GRID: mobile-first */
    .ct-row{
        display:grid;
        gap:12px;
        margin-top:12px;
        grid-template-columns: 1fr; /* phones */
    }
    @media (min-width: 576px){
        .ct-page{ padding:22px 16px 28px; }
        .ct-row.cols-2{ grid-template-columns: 1fr 1fr; }
        .ct-row.cols-3{ grid-template-columns: 1fr 1fr; } /* better for small tablets */
        .ct-row.cols-4{ grid-template-columns: 1fr 1fr; }
    }
    @media (min-width: 992px){
        .ct-page{ padding:22px 18px 28px; }
        .ct-row.cols-3{ grid-template-columns: 1fr 1fr 1fr; }
        .ct-row.cols-4{ grid-template-columns: 1fr 1fr 1fr 1fr; }
        .ct-body{ padding:16px; }
        .ct-bar{ padding:14px 16px; }
    }

    .ct-help{
        font-size:12px; color:var(--ct-muted);
        margin-top:6px;
    }

    /* ACTIONS */
    .ct-actions{
        display:flex;
        gap:10px;
        margin-top:14px;
    }
    .btn{
        border-radius:10px !important;
        font-weight:900 !important;
        letter-spacing:.2px;
        padding:10px 14px !important;
        border:1px solid transparent !important;
    }
    .btn-danger{
        background:rgba(239,68,68,.12) !important;
        border-color:rgba(239,68,68,.35) !important;
        color:#fecaca !important;
    }
    .btn-primary{
        background:linear-gradient(180deg, var(--ct-primary), var(--ct-primary2)) !important;
        border-color:rgba(59,130,246,.35) !important;
        color:#fff !important;
    }

    /* ✅ MOBILE sticky bottom bar */
    .ct-actions{
        justify-content:space-between;
        flex-wrap:wrap;
    }
    .ct-actions .btn{
        flex:1 1 0;
        min-width:140px;
    }
    @media (max-width: 575.98px){
        .ct-actions{
            position: sticky;
            bottom: 10px;
            padding: 10px;
            border: 1px solid var(--ct-border);
            border-radius: 14px;
            background: rgba(2,6,23,.85);
            backdrop-filter: blur(8px);
            box-shadow: 0 12px 30px rgba(0,0,0,.45);
            z-index: 20;
        }
        .ct-actions .btn{
            width:100%;
        }
    }

    .alert{ border-radius:12px !important; }
</style>

<div class="ct-page">
    <div class="ct-wrap">
        <div class="ct-top">
            <div class="ct-top-left">
                <span> Provider Onboarding</span>
                <span class="ct-chip">Owner Info</span>
            </div>
            <div class="ct-breadcrumb">Home / Provider / Pre-Register</div>
        </div>

        <div class="ct-box">
            <div class="ct-bar">
                <div>☰ CLEAN TECH PROVIDER PRE-REGISTRATION</div>
                <div class="ct-step">Step 1 of 2</div>
            </div>

            <div class="ct-body">
                @if ($errors->any())
                    <div class="alert alert-danger mb-3">{{ $errors->first() }}</div>
                @endif

                <div class="ct-subbar">OWNER'S INFORMATION</div>

                <form method="POST" action="{{ route('provider.pre_register.submit') }}">
                    @csrf

                    <div class="ct-row cols-3">
                        <div>
                            <div class="ct-label">Are you a stateless person? *</div>
                            <div class="ct-radio">
                                <label><input type="radio" name="is_stateless" value="1" required> Yes</label>
                                <label><input type="radio" name="is_stateless" value="0" required> No</label>
                            </div>
                        </div>

                        <div>
                            <div class="ct-label">Are you a refugee person? *</div>
                            <div class="ct-radio">
                                <label><input type="radio" name="is_refugee" value="1" required> Yes</label>
                                <label><input type="radio" name="is_refugee" value="0" required> No</label>
                            </div>
                        </div>

                        <div>
                            <div class="ct-label">Citizenship *</div>
                            <select name="citizenship" class="form-control ct-input" required>
                                <option value="">Select</option>
                                <option value="Filipino">Filipino</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                    </div>

                    <div class="ct-row cols-4">
                        <div>
                            <div class="ct-label">First Name *</div>
                            <input class="form-control ct-input" name="first_name" value="{{ old('first_name') }}" required>
                        </div>

                        <div>
                            <div class="ct-label">Middle Name</div>
                            <input class="form-control ct-input" name="middle_name" id="middle_name" value="{{ old('middle_name') }}">
                            <div class="form-check mt-2">
                                <input class="form-check-input" type="checkbox" name="no_middle" value="1" id="noMiddle">
                                <label class="form-check-label" for="noMiddle" style="font-size:12px; color:var(--ct-muted);">
                                    Check if no middle name
                                </label>
                            </div>
                        </div>

                        <div>
                            <div class="ct-label">Last Name *</div>
                            <input class="form-control ct-input" name="last_name" value="{{ old('last_name') }}" required>
                        </div>

                        <div>
                            <div class="ct-label">Suffix</div>
                            <select name="suffix" class="form-control ct-input">
                                <option value="">-- N/A --</option>
                                <option>Jr.</option>
                                <option>Sr.</option>
                                <option>III</option>
                            </select>
                        </div>
                    </div>

                    <div class="ct-row cols-4">
                        <div>
                            <div class="ct-label">Date of Birth (Month) *</div>
                            <select name="dob_month" class="form-control ct-input" required>
                                <option value="">MONTH</option>
                                @for($m=1;$m<=12;$m++)
                                    <option value="{{ $m }}">{{ $m }}</option>
                                @endfor
                            </select>
                        </div>

                        <div>
                            <div class="ct-label">Day *</div>
                            <select name="dob_day" class="form-control ct-input" required>
                                <option value="">DAY</option>
                                @for($d=1;$d<=31;$d++)
                                    <option value="{{ $d }}">{{ $d }}</option>
                                @endfor
                            </select>
                        </div>

                        <div>
                            <div class="ct-label">Year *</div>
                            <select name="dob_year" class="form-control ct-input" required>
                                <option value="">YEAR</option>
                                @for($y=date('Y'); $y>=1900; $y--)
                                    <option value="{{ $y }}">{{ $y }}</option>
                                @endfor
                            </select>
                        </div>

                        <div>
                            <div class="ct-label">Civil Status *</div>
                            <select name="civil_status" class="form-control ct-input" required>
                                <option value="">-- CIVIL STATUS --</option>
                                <option>Single</option>
                                <option>Married</option>
                                <option>Widowed</option>
                                <option>Separated</option>
                            </select>
                        </div>
                    </div>

                    <div class="ct-row cols-3">
                        <div>
                            <div class="ct-label">Email Address *</div>
                            <input type="email" class="form-control ct-input" name="email" value="{{ old('email') }}" required>
                            <div class="ct-help">We’ll use this for verification and login.</div>
                        </div>

                        <div>
                            <div class="ct-label">Gender *</div>
                            <div class="ct-radio">
                                <label><input type="radio" name="gender" value="Male" required> Male</label>
                                <label><input type="radio" name="gender" value="Female" required> Female</label>
                            </div>
                        </div>

                        <div></div>
                    </div>

                    <div class="ct-actions">
                        <a class="btn btn-danger btn-sm" href="{{ route('home') }}">Cancel ✖</a>
                        <button class="btn btn-primary btn-sm" type="submit">Next ➜</button>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>

<script>
    const noMiddle = document.getElementById('noMiddle');
    const middle = document.getElementById('middle_name');
    noMiddle?.addEventListener('change', () => {
        middle.disabled = noMiddle.checked;
        if(noMiddle.checked) middle.value = '';
    });
</script>
@endsection
