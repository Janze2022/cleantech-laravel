@extends('layouts.app')

@section('title', 'CleanTech - Provider Registration')

@section('content')
<style>
    :root{
        --bg:#0b1220;
        --card:#0f172a;
        --card2:#111c33;
        --border:#1f2a44;
        --text:#e5e7eb;
        --muted:#94a3b8;
        --primary:#3b82f6;
        --primary2:#2563eb;
        --danger:#ef4444;
        --shadow:0 18px 50px rgba(0,0,0,.35);
        --r:14px;
    }

    .ct-page{
        padding-top: calc(var(--nav-h, 72px) + 16px) !important;
        min-height: calc(100vh - 70px);
        padding: 22px 14px 30px;
        background:
            radial-gradient(1000px 500px at 15% 0%, rgba(59,130,246,.18), transparent 60%),
            radial-gradient(900px 450px at 85% 10%, rgba(34,197,94,.10), transparent 60%),
            var(--bg);
    }
    .ct-wrap{ max-width:1100px; margin:0 auto; }

    /* TOP (mobile wrap) */
    .ct-top{
        display:flex;
        justify-content:space-between;
        align-items:flex-start;
        gap:10px;
        margin-bottom:14px;
        flex-wrap:wrap;
    }
    .ct-title{
        color:var(--text);
        font-weight:900;
        letter-spacing:.3px;
        display:flex;
        gap:10px;
        align-items:center;
        flex-wrap:wrap;
    }
    .ct-crumb{
        font-size:12px;
        color:var(--muted);
        background: rgba(255,255,255,.04);
        border:1px solid var(--border);
        padding:6px 10px;
        border-radius:999px;
        max-width:100%;
        white-space:nowrap;
        overflow:hidden;
        text-overflow:ellipsis;
    }

    .ct-box{
        background: rgba(15,23,42,.9);
        border:1px solid var(--border);
        border-radius: var(--r);
        box-shadow: var(--shadow);
        overflow:hidden;
    }

    .ct-bar{
        padding: 14px 14px;
        display:flex;
        justify-content:space-between;
        align-items:center;
        gap:10px;
        border-bottom:1px solid var(--border);
        background: linear-gradient(180deg, rgba(255,255,255,.03), rgba(255,255,255,.01));
        flex-wrap:wrap;
    }
    .ct-bar h4{
        margin:0;
        color:var(--text);
        font-weight:900;
        font-size:14px;
        letter-spacing:.4px;
        display:flex;
        gap:10px;
        align-items:center;
        flex-wrap:wrap;
    }
    .ct-step{
        font-size:12px;
        color:var(--muted);
        border:1px solid var(--border);
        padding:6px 10px;
        border-radius:999px;
        background: rgba(255,255,255,.03);
        flex:0 0 auto;
    }

    .ct-body{ padding:14px; }

    .ct-subbar{
        margin-top: 4px;
        padding: 12px 12px;
        border:1px solid rgba(31,42,68,.75);
        border-radius: 12px;
        background: rgba(17,28,51,.55);
        color: var(--text);
        font-weight:900;
        font-size:12.5px;
        letter-spacing:.35px;
    }

    /* GRID (mobile-first) */
    .ct-row{
        display:grid;
        gap:12px;
        margin-top:12px;
        grid-template-columns: 1fr; /* phones */
    }
    @media(min-width: 576px){
        .ct-page{ padding: 22px 16px 30px; }
        .ct-row.cols-2{ grid-template-columns: 1fr 1fr; }
        .ct-row.cols-3{ grid-template-columns: 1fr 1fr; }
        .ct-row.cols-4{ grid-template-columns: 1fr 1fr; }
        .ct-body{ padding:16px; }
        .ct-bar{ padding:14px 16px; }
    }
    @media(min-width: 992px){
        .ct-row.cols-3{ grid-template-columns: 1fr 1fr 1fr; }
        .ct-row.cols-4{ grid-template-columns: 1fr 1fr 1fr 1fr; }
    }

    .ct-label{
        font-size:12px;
        font-weight:800;
        color: var(--muted);
        margin-bottom:6px;
    }

    .ct-input{
        height:44px;
        border-radius: 12px !important;
        background: rgba(255,255,255,.04) !important;
        border: 1px solid rgba(31,42,68,.85) !important;
        color: var(--text) !important;
        box-shadow: none !important;
        padding: 10px 12px !important;
    }
    .ct-input:focus{
        border-color: rgba(59,130,246,.65) !important;
        outline:none !important;
        box-shadow:0 0 0 .2rem rgba(59,130,246,.18) !important;
    }

    /* ✅ readable dropdown list on dark UI */
    select.ct-input,
    select.form-control.ct-input{
        color-scheme: dark;
        appearance: auto;
    }
    select.ct-input option{
        background: #0f172a;
        color: #e5e7eb;
    }
    .ct-input:disabled,
    select.ct-input:disabled{
        opacity: .65 !important;
        cursor:not-allowed;
    }

    textarea.form-control{
        border-radius: 12px !important;
        background: rgba(255,255,255,.04) !important;
        border: 1px solid rgba(31,42,68,.85) !important;
        color: var(--text) !important;
        box-shadow: none !important;
        padding: 10px 12px !important;
    }
    textarea.form-control:focus{
        border-color: rgba(59,130,246,.65) !important;
        outline:none !important;
        box-shadow:0 0 0 .2rem rgba(59,130,246,.18) !important;
    }

    /* ACTIONS */
    .ct-actions{
        display:flex;
        gap:10px;
        margin-top:14px;
        justify-content:space-between;
        flex-wrap:wrap;
    }

    .btn-ct{
        border-radius: 10px;
        padding: 10px 14px;
        font-weight:800;
        font-size:12.5px;
        border:1px solid var(--border);
        background: rgba(255,255,255,.04);
        color: var(--text);
        transition: .15s ease;
        text-decoration:none;
        display:inline-flex;
        align-items:center;
        justify-content:center;
        gap:8px;
        min-height:44px; /* touch-friendly */
    }
    .btn-ct:hover{ transform: translateY(-1px); }
    .btn-ct-primary{
        background: linear-gradient(180deg, var(--primary), var(--primary2));
        border-color: rgba(59,130,246,.35);
        color:white;
    }
    .btn-ct-danger{
        background: rgba(239,68,68,.12);
        border-color: rgba(239,68,68,.35);
        color: #fecaca;
    }

    /* ✅ Mobile sticky action bar */
    .ct-actions .btn-ct{
        flex:1 1 0;
        min-width:140px;
    }
    @media (max-width: 575.98px){
        .ct-actions{
            position: sticky;
            bottom: 10px;
            padding: 10px;
            border: 1px solid var(--border);
            border-radius: 14px;
            background: rgba(2,6,23,.85);
            backdrop-filter: blur(8px);
            box-shadow: 0 12px 30px rgba(0,0,0,.45);
            z-index: 20;
        }
        .ct-actions .btn-ct{ width:100%; }
    }

    .alert{
        border-radius: 12px !important;
        border: 1px solid var(--border) !important;
        background: rgba(59,130,246,.10) !important;
        color: #bfdbfe !important;
    }

    /* FILE PICKER (mobile wrap) */
    .filebox{
        border:1px solid rgba(31,42,68,.85);
        padding:10px;
        border-radius:12px;
        display:flex;
        align-items:center;
        gap:10px;
        background: rgba(255,255,255,.03);
        flex-wrap:wrap;
    }
    .filebox span{
        flex:1 1 220px;
        font-size:12px;
        color: var(--muted);
        overflow:hidden;
        white-space:nowrap;
        text-overflow:ellipsis;
        min-width:0;
    }
    .filebox input[type="file"]{
        color: var(--muted);
        font-size:12px;
        width:100%;
    }
    @media(min-width:576px){
        .filebox input[type="file"]{ width:auto; }
    }
</style>

<div class="ct-page">
    <div class="ct-wrap">
        <div class="ct-top">
            <div class="ct-title"> Provider Onboarding</div>
            <div class="ct-crumb">Home / Provider / Registration</div>
        </div>

        <div class="ct-box">
            <div class="ct-bar">
                <h4>☰ Provider Registration</h4>
                <div class="ct-step">Step 2 of 2</div>
            </div>

            <div class="ct-body">
                @if ($errors->any())
                    <div class="alert mb-3">{{ $errors->first() }}</div>
                @endif

                @if (!empty($step1))
                    <div class="alert mb-3">
                        <b style="color:#e5e7eb;">Pre-Registration Summary:</b>
                        {{ $step1['first_name'] }} {{ $step1['last_name'] }} • {{ $step1['email'] }} • {{ $step1['citizenship'] }}
                    </div>
                @endif

                <div class="ct-subbar">CONTACT & LOCATION INFORMATION</div>

                <form method="POST" action="{{ route('provider.register.submit') }}" enctype="multipart/form-data">
                    @csrf

                    <div class="ct-row cols-3 mt-3">
                        <div>
                            <div class="ct-label">Mobile Number *</div>
                            <input class="form-control ct-input" type="tel" id="phone" name="phone" placeholder="09XXXXXXXXX" maxlength="11" required>
                        </div>

                        <div>
                            <div class="ct-label">Emergency Contact Name *</div>
                            <input class="form-control ct-input" name="emergency_name" placeholder="Full Name" required>
                        </div>

                        <div>
                            <div class="ct-label">Emergency Contact Number *</div>
                            <input class="form-control ct-input" type="tel" id="emergency_phone" name="emergency_phone" placeholder="09XXXXXXXXX" maxlength="11" required>
                        </div>
                    </div>

                    <div class="ct-row cols-4 mt-3">
                        <div>
                            <div class="ct-label">Region *</div>
                            <select id="region" class="form-control ct-input" required>
                                <option value="">Select Region</option>
                            </select>
                        </div>

                        <div>
                            <div class="ct-label">Province *</div>
                            <select id="province" class="form-control ct-input" disabled required></select>
                        </div>

                        <div>
                            <div class="ct-label">City/Municipality *</div>
                            <select id="city" class="form-control ct-input" disabled required></select>
                        </div>

                        <div>
                            <div class="ct-label">Barangay *</div>
                            <select id="barangay" class="form-control ct-input" disabled required></select>
                        </div>
                    </div>

                    <input type="hidden" name="region" id="region_text">
                    <input type="hidden" name="province" id="province_text">
                    <input type="hidden" name="city" id="city_text">
                    <input type="hidden" name="barangay" id="barangay_text">

                    <div class="ct-row cols-2 mt-3">
                        <div>
                            <div class="ct-label">Complete Address (House No / Street) *</div>
                            <textarea class="form-control" name="address" placeholder="House No / Street" required style="height:96px;"></textarea>
                        </div>

                        <div>
                            <div class="ct-label">Valid ID Type *</div>
                            <select name="id_type" class="form-control ct-input" required>
                                <option value="">Select Valid ID</option>
                                <option>Passport</option>
                                <option>Driver License</option>
                                <option>National ID</option>
                                <option>Postal ID</option>
                                <option>Voter ID</option>
                            </select>

                            <div class="ct-label mt-3">Upload Valid ID Image *</div>
                            <div class="filebox">
                                <span id="idLabel">No file selected</span>
                                <input type="file" id="id_image" name="id_image" accept=".jpg,.jpeg,.png,.gif,.webp,.pdf" required>
                        </div>
                    </div>

                    <div class="ct-subbar mt-4">SECURITY</div>

                    <div class="ct-row cols-2 mt-3">
                        <div>
                            <div class="ct-label">Password *</div>
                            <input type="password" class="form-control ct-input" name="password" placeholder="Password" required>
                        </div>

                        <div>
                            <div class="ct-label">Confirm Password *</div>
                            <input type="password" class="form-control ct-input" name="password_confirmation" placeholder="Confirm Password" required>
                        </div>
                    </div>

                    <div class="ct-actions">
                        <a class="btn-ct btn-ct-danger" href="{{ route('provider.pre_register') }}">Back</a>
                        <button class="btn-ct btn-ct-primary" type="submit">Submit Registration</button>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>

<script>
const api='https://psgc.gitlab.io/api';
const region=document.getElementById('region'),
province=document.getElementById('province'),
city=document.getElementById('city'),
barangay=document.getElementById('barangay'),
region_text=document.getElementById('region_text'),
province_text=document.getElementById('province_text'),
city_text=document.getElementById('city_text'),
barangay_text=document.getElementById('barangay_text');

fetch(`${api}/regions/`).then(r=>r.json()).then(d=>{
    region.innerHTML = `<option value="">Select Region</option>`;
    d.forEach(r=>{
        region.innerHTML+=`<option value="${r.code}">${r.name}</option>`;
    });
});

region.onchange=()=>{
    fetch(`${api}/regions/${region.value}/provinces/`).then(r=>r.json()).then(d=>{
        province.disabled=false; province.innerHTML='';
        province.innerHTML = `<option value="">Select Province</option>`;
        d.forEach(p=>province.innerHTML+=`<option value="${p.code}">${p.name}</option>`);
        city.disabled=true; city.innerHTML='<option value="">Select City/Municipality</option>';
        barangay.disabled=true; barangay.innerHTML='<option value="">Select Barangay</option>';
    });
    region_text.value=region.selectedOptions[0]?.text || '';
}

province.onchange=()=>{
    fetch(`${api}/provinces/${province.value}/cities-municipalities/`).then(r=>r.json()).then(d=>{
        city.disabled=false; city.innerHTML='';
        city.innerHTML = `<option value="">Select City/Municipality</option>`;
        d.forEach(c=>city.innerHTML+=`<option value="${c.code}">${c.name}</option>`);
        barangay.disabled=true; barangay.innerHTML='<option value="">Select Barangay</option>';
    });
    province_text.value=province.selectedOptions[0]?.text || '';
}

city.onchange=()=>{
    fetch(`${api}/cities-municipalities/${city.value}/barangays/`).then(r=>r.json()).then(d=>{
        barangay.disabled=false; barangay.innerHTML='';
        barangay.innerHTML = `<option value="">Select Barangay</option>`;
        d.forEach(b=>barangay.innerHTML+=`<option value="${b.code}">${b.name}</option>`);
    });
    city_text.value=city.selectedOptions[0]?.text || '';
}

barangay.onchange=()=>barangay_text.value=barangay.selectedOptions[0]?.text || '';

['phone','emergency_phone'].forEach(id=>{
    const i=document.getElementById(id);
    i.addEventListener('input',()=>{
        i.value=i.value.replace(/\D/g,'').slice(0,11);
    });
});

const id_image = document.getElementById('id_image');
const idLabel  = document.getElementById('idLabel');
id_image.onchange = e => idLabel.textContent = e.target.files[0]?.name || 'No file selected';
</script>
@endsection
