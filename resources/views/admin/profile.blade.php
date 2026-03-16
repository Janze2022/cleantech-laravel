@extends('admin.layouts.app')

@section('title', 'Profile Settings')

@section('content')

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
    max-width: 680px;
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
    height: 42px;
}

.form-control:focus{
    border-color: rgba(56,189,248,.45);
    box-shadow: 0 0 0 3px rgba(56,189,248,.10);
    background: #020617;
    color: #fff;
}

.form-control::placeholder{
    color: rgba(255,255,255,.45);
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

.row.g-3{
    --bs-gutter-x: .75rem;
    --bs-gutter-y: .75rem;
}

.mb-4{
    margin-bottom: .9rem !important;
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
    .btn-primary{
        font-size: .84rem;
    }

    .form-control,
    .btn-primary{
        min-height: 40px;
        height: 40px;
    }
}
</style>

<div class="profile-page">
    <div class="profile-card">

        <div class="profile-header">
            <h4>Profile Settings</h4>
            <p>Manage your admin account details and security.</p>
        </div>

        @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
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
            <h6>Profile Information</h6>
            <p>Update your admin account details.</p>

            <form method="POST" action="{{ route('admin.profile.update') }}">
                @csrf
                @method('PUT')

                <div class="row g-3 mb-4">
                    <div class="col-12 col-md-6">
                        <label class="form-label">Full Name</label>
                        <input
                            class="form-control"
                            type="text"
                            name="name"
                            value="{{ old('name', $admin->name) }}"
                            required
                        >
                    </div>

                    <div class="col-12 col-md-6">
                        <label class="form-label">Email</label>
                        <input
                            class="form-control"
                            type="email"
                            name="email"
                            value="{{ old('email', $admin->email) }}"
                            required
                        >
                    </div>

                    <div class="col-12 col-md-6">
                        <label class="form-label">Role</label>
                        <input
                            class="form-control"
                            type="text"
                            value="{{ ucfirst(str_replace('_', ' ', $admin->role)) }}"
                            readonly
                        >
                    </div>
                </div>

                <button class="btn btn-primary" type="submit">
                    Save Changes
                </button>
            </form>
        </div>

        <div class="profile-section">
            <h6>Change Password</h6>
            <p>Use a strong password to keep your account secure.</p>

            <form method="POST" action="{{ route('admin.profile.password') }}">
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

@endsection