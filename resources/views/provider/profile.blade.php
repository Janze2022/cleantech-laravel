@extends('provider.layouts.app')

@section('title', 'Profile Settings')

@section('content')

@php
    $profileImageUrl = asset('images/avatar-placeholder.svg');

    if (!empty($provider->profile_image)) {
        $profileImageUrl = route('provider.profile.image', ['filename' => basename($provider->profile_image)]) . '?v=' . time();
    }
@endphp

<style>
:root{
    --bg-main:#020617;
    --bg-card-1:#020b1f;
    --bg-card-2:#020617;
    --border-soft:rgba(255,255,255,.08);
    --text-muted:rgba(255,255,255,.55);
    --accent:#38bdf8;
}

.profile-page{
    max-width: 820px;
    margin: 0 auto;
    padding: 12px;
    color:#fff;
}

.profile-card{
    background: linear-gradient(180deg, var(--bg-card-1), var(--bg-card-2));
    border-radius: 16px;
    border: 1px solid var(--border-soft);
    padding: 14px;
    box-shadow: 0 18px 40px rgba(0,0,0,.38);
}

.profile-section{
    margin-top: 18px;
    padding-top: 16px;
    border-top: 1px solid rgba(255,255,255,.06);
}

.profile-section:first-of-type{
    margin-top: 0;
    padding-top: 0;
    border-top: 0;
}

.profile-header{
    margin-bottom: 14px !important;
}

.profile-header h4{
    font-weight: 800;
    margin-bottom: 4px;
    font-size: 1.1rem;
    line-height: 1.2;
}

.profile-header p{
    color: var(--text-muted);
    font-size: .85rem;
    margin-bottom: 0;
}

.profile-section h6{
    font-weight: 800;
    margin-bottom: 3px;
    font-size: .95rem;
}

.profile-section p{
    font-size: .8rem;
    color: var(--text-muted);
    margin-bottom: .85rem;
    line-height: 1.45;
}

.avatar-row{
    display: grid;
    grid-template-columns: 72px 1fr;
    align-items: center;
    gap: .85rem;
    margin-bottom: .9rem;
}

.avatar{
    width: 72px;
    height: 72px;
    border-radius: 999px;
    object-fit: cover;
    border: 2px solid rgba(56,189,248,.35);
    background: rgba(255,255,255,.03);
}

.file-input,
.form-control{
    width: 100%;
    background: #020617;
    border: 1px solid rgba(255,255,255,.08);
    color: #fff;
    border-radius: 10px;
    padding: .62rem .78rem;
    font-size: .88rem;
    outline: none;
    box-shadow: none;
}

.file-input{
    min-height: 42px;
}

.form-control{
    height: 42px;
}

textarea.form-control{
    height: auto;
    resize: none;
}

.form-control:focus,
.file-input:focus{
    border-color: rgba(56,189,248,.45);
    box-shadow: 0 0 0 3px rgba(56,189,248,.10);
    background: #020617;
    color: #fff;
}

.form-control[readonly]{
    opacity: .9;
}

.form-label{
    font-size: .76rem;
    color: rgba(255,255,255,.72);
    font-weight: 700;
    margin-bottom: .32rem;
}

.btn-primary{
    min-height: 42px;
    border-radius: 10px;
    font-weight: 800;
    font-size: .86rem;
    padding: .62rem .95rem;
    background: linear-gradient(180deg, rgba(14,165,233,.92), rgba(14,165,233,.72));
    border: 1px solid rgba(56,189,248,.35);
    color: #fff;
    box-shadow: none;
}

.btn-primary:hover,
.btn-primary:focus{
    color: #fff;
    border-color: rgba(56,189,248,.5);
    box-shadow: 0 0 0 3px rgba(56,189,248,.10);
}

.alert{
    border-radius: 12px;
    padding: .75rem .9rem;
    font-size: .84rem;
    margin-bottom: 14px !important;
}

.alert-success{
    background: rgba(34,197,94,.15);
    border: 1px solid rgba(34,197,94,.35);
    color: #86efac;
}

.alert-danger{
    background: rgba(239,68,68,.15);
    border: 1px solid rgba(239,68,68,.35);
    color: #fca5a5;
}

.action-row{
    display:flex;
    gap:.6rem;
    flex-wrap:wrap;
    margin-top:.4rem;
}

.row.g-3{
    --bs-gutter-x: .75rem;
    --bs-gutter-y: .75rem;
}

.mb-4{
    margin-bottom: .9rem !important;
}

.readonly-grid{
    display:grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap:.75rem;
}

.readonly-card{
    border:1px solid rgba(255,255,255,.06);
    border-radius:12px;
    background: rgba(255,255,255,.02);
    padding:.8rem .9rem;
}

.readonly-card.full{
    grid-column: 1 / -1;
}

.readonly-card .k{
    color: rgba(255,255,255,.55);
    font-size: .72rem;
    font-weight: 700;
    letter-spacing: .08em;
    text-transform: uppercase;
}

.readonly-card .v{
    margin-top: .28rem;
    color: #fff;
    font-weight: 700;
    line-height: 1.45;
    word-break: break-word;
}

@media (max-width: 767.98px){
    .profile-page{
        max-width: 100%;
        padding: 10px;
    }

    .profile-card{
        padding: 12px;
        border-radius: 14px;
    }

    .profile-section{
        margin-top: 16px;
        padding-top: 14px;
    }

    .avatar-row{
        grid-template-columns: 1fr;
        gap: .7rem;
        justify-items: start;
    }

    .avatar{
        width: 68px;
        height: 68px;
    }

    .readonly-grid{
        grid-template-columns: 1fr;
    }

    .btn-primary{
        width: 100%;
    }
}

@media (max-width: 575.98px){
    .profile-page{
        padding: 8px;
    }

    .profile-card{
        padding: 10px;
        border-radius: 12px;
    }

    .profile-header h4{
        font-size: 1rem;
    }

    .profile-header p,
    .profile-section p{
        font-size: .78rem;
    }

    .profile-section h6{
        font-size: .9rem;
    }

    .form-control,
    .file-input,
    .btn-primary{
        font-size: .84rem;
    }

    .form-control,
    .btn-primary,
    .file-input{
        min-height: 40px;
        height: 40px;
    }

    textarea.form-control{
        height: auto;
    }

    .avatar{
        width: 64px;
        height: 64px;
    }
}
</style>

<div class="profile-page">
    <div class="profile-card">

        <div class="profile-header">
            <h4>Profile Settings</h4>
            <p>Manage your provider photo, contact details, and account security.</p>
        </div>

        @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger">
                {{ session('error') }}
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger">
                @foreach($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <div class="profile-section">
            <h6>Profile Picture</h6>
            <p>Upload a JPG, PNG, GIF, or WEBP image up to 5MB.</p>

            <form method="POST" action="{{ route('provider.profile.image.update') }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="avatar-row">
                    <img
                        id="providerProfilePreview"
                        src="{{ $profileImageUrl }}"
                        class="avatar"
                        alt="Provider profile photo"
                        onerror="this.onerror=null;this.src='{{ asset('images/avatar-placeholder.svg') }}';"
                    >

                    <input
                        type="file"
                        name="profile_image"
                        class="file-input"
                        accept=".jpg,.jpeg,.png,.gif,.webp,image/jpeg,image/png,image/gif,image/webp"
                        onchange="previewProviderProfileImage(event)"
                        required
                    >
                </div>

                <div class="action-row">
                    <button class="btn btn-primary" type="submit">
                        Update Profile Picture
                    </button>
                </div>
            </form>
        </div>

        <div class="profile-section">
            <h6>Profile Information</h6>
            <p>Update the contact details customers use to reach you.</p>

            <form method="POST" action="{{ route('provider.profile.update') }}">
                @csrf
                @method('PUT')

                <div class="row g-3 mb-4">
                    <div class="col-12 col-md-4">
                        <label class="form-label">First Name</label>
                        <input class="form-control" type="text" value="{{ $provider->first_name }}" readonly>
                    </div>

                    <div class="col-12 col-md-4">
                        <label class="form-label">Last Name</label>
                        <input class="form-control" type="text" value="{{ $provider->last_name }}" readonly>
                    </div>

                    <div class="col-12 col-md-4">
                        <label class="form-label">Suffix</label>
                        <input class="form-control" type="text" value="{{ $provider->suffix }}" readonly>
                    </div>

                    <div class="col-12 col-md-6">
                        <label class="form-label">Email</label>
                        <input class="form-control" type="email" value="{{ $provider->email }}" readonly>
                    </div>

                    <div class="col-12 col-md-6">
                        <label class="form-label">Mobile Number</label>
                        <input
                            class="form-control"
                            type="text"
                            name="phone"
                            value="{{ old('phone', $provider->phone) }}"
                            placeholder="09XXXXXXXXX"
                            required
                        >
                    </div>
                </div>

                <button class="btn btn-primary" type="submit">
                    Save Changes
                </button>
            </form>
        </div>

        <div class="profile-section">
            <h6>Address and Account</h6>
            <p>Your location and approval details are shown here for reference.</p>

            <div class="readonly-grid">
                <div class="readonly-card">
                    <div class="k">Country</div>
                    <div class="v">{{ $provider->country ?: 'Not set' }}</div>
                </div>

                <div class="readonly-card">
                    <div class="k">Region</div>
                    <div class="v">{{ $provider->region ?: 'Not set' }}</div>
                </div>

                <div class="readonly-card">
                    <div class="k">Province</div>
                    <div class="v">{{ $provider->province ?: 'Not set' }}</div>
                </div>

                <div class="readonly-card">
                    <div class="k">City / Municipality</div>
                    <div class="v">{{ $provider->city ?: 'Not set' }}</div>
                </div>

                <div class="readonly-card">
                    <div class="k">Barangay</div>
                    <div class="v">{{ $provider->barangay ?: 'Not set' }}</div>
                </div>

                <div class="readonly-card">
                    <div class="k">Account Status</div>
                    <div class="v">{{ ucfirst((string) ($provider->status ?? 'pending')) }}</div>
                </div>

                <div class="readonly-card full">
                    <div class="k">Full Address</div>
                    <div class="v">{{ $provider->address ?: 'Not set' }}</div>
                </div>
            </div>
        </div>

        <div class="profile-section">
            <h6>Change Password</h6>
            <p>For security, use a strong password that only you know.</p>

            <form method="POST" action="{{ route('provider.password.update') }}">
                @csrf

                <div class="row g-3 mb-4">
                    <div class="col-12 col-md-6">
                        <label class="form-label">Current Password</label>
                        <input type="password" name="current_password" class="form-control" required>
                    </div>

                    <div class="col-12 col-md-6">
                        <label class="form-label">New Password</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>

                    <div class="col-12 col-md-6">
                        <label class="form-label">Confirm Password</label>
                        <input type="password" name="password_confirmation" class="form-control" required>
                    </div>
                </div>

                <button class="btn btn-primary" type="submit">
                    Update Password
                </button>
            </form>
        </div>

    </div>
</div>

<script>
function previewProviderProfileImage(event) {
    const file = event.target.files[0];
    const preview = document.getElementById('providerProfilePreview');

    if (file && preview) {
        preview.src = URL.createObjectURL(file);
    }
}
</script>

@endsection
