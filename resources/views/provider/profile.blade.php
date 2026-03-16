@extends('provider.layouts.app')

@section('title', 'Profile Settings')

@section('content')

@php
    use Illuminate\Support\Str;

    $profileImage = null;

    if (!empty($provider->profile_image)) {
        $savedPath = str_replace('\\', '/', ltrim($provider->profile_image, '/'));
        $filename = basename($savedPath);

        $profileImage = route('provider.profile.image', ['filename' => $filename]) . '?v=' . time();
    } else {
        $profileImage = 'https://ui-avatars.com/api/?name=' .
            urlencode(trim(($provider->first_name ?? '') . ' ' . ($provider->last_name ?? '')) ?: 'Provider') .
            '&background=020617&color=38bdf8&size=256';
    }
@endphp

<style>
.profile-wrapper {
    max-width: 1000px;
    margin: auto;
    padding: 2rem 1rem 4rem;
}

.profile-card {
    background: radial-gradient(circle at top, #020b1f, #020617 70%);
    border-radius: 20px;
    padding: 2.5rem;
    border: 1px solid rgba(255,255,255,.08);
    box-shadow: 0 40px 90px rgba(0,0,0,.65);
    color: #e5e7eb;
}

.profile-title {
    font-size: 1.4rem;
    font-weight: 700;
    color: #f8fafc;
}

.profile-subtitle {
    font-size: .9rem;
    color: #94a3b8;
    margin-bottom: 2rem;
}

.avatar-wrapper {
    display: flex;
    gap: 1.25rem;
    align-items: center;
    margin-bottom: 2rem;
    flex-wrap: wrap;
}

.avatar-box {
    display: flex;
    align-items: center;
    gap: 1rem;
    flex-wrap: wrap;
}

.avatar {
    width: 110px;
    height: 110px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid rgba(56,189,248,.45);
    background: #020617;
    display: block;
}

.upload-group {
    min-width: 260px;
    flex: 1;
}

.form-label {
    font-size: .8rem;
    color: #94a3b8;
    margin-bottom: .45rem;
}

.form-control,
.form-select,
.form-control[type="file"] {
    background: rgba(2,6,23,.9);
    border: 1px solid rgba(255,255,255,.08);
    color: #e5e7eb;
    border-radius: 12px;
    min-height: 46px;
}

.form-control[type="file"] {
    padding: .6rem .85rem;
}

.form-control::placeholder {
    color: #64748b;
}

.form-control:disabled,
textarea.form-control:disabled {
    background: rgba(2,6,23,.55);
    color: #94a3b8;
    opacity: 1;
}

.form-control:focus,
.form-select:focus {
    border-color: #38bdf8;
    box-shadow: 0 0 0 0.15rem rgba(56,189,248,.12);
    background: rgba(2,6,23,.96);
    color: #e5e7eb;
}

textarea.form-control {
    resize: none;
}

.divider {
    border-top: 1px solid rgba(255,255,255,.08);
    margin: 2.5rem 0;
}

.btn-primary {
    background: linear-gradient(135deg, #0ea5e9, #2563eb);
    border: none;
    border-radius: 12px;
    min-height: 44px;
    font-weight: 600;
}

.btn-danger {
    background: linear-gradient(135deg, #ef4444, #dc2626);
    border: none;
    border-radius: 12px;
    min-height: 44px;
    font-weight: 600;
}

.alert {
    border-radius: 14px;
}

.error-text {
    color: #fca5a5;
    font-size: .82rem;
    margin-top: .35rem;
}

.file-note {
    font-size: .78rem;
    color: #94a3b8;
    margin-top: .35rem;
}

.debug-url {
    font-size: .75rem;
    color: #64748b;
    margin-top: .5rem;
    word-break: break-all;
}

@media (max-width: 576px) {
    .profile-card {
        padding: 1.25rem;
        border-radius: 16px;
    }

    .avatar-wrapper {
        align-items: flex-start;
    }

    .avatar-box {
        width: 100%;
        align-items: flex-start;
        flex-direction: column;
    }

    .upload-group {
        width: 100%;
        min-width: 100%;
    }

    .btn {
        width: 100%;
    }
}
</style>

<div class="profile-wrapper">

    @if (session('success'))
        <div class="alert alert-success mb-4">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger mb-4">
            {{ session('error') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger mb-4">
            <strong>Please fix the following:</strong>
            <ul class="mb-0 mt-2">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="profile-card">

        <div class="profile-title">Profile Settings</div>
        <div class="profile-subtitle">
            Complete overview of your service provider account.
        </div>

        <form id="profileForm"
              method="POST"
              action="{{ route('provider.profile.update') }}"
              enctype="multipart/form-data">
            @csrf

            <div class="avatar-wrapper">
                <div class="avatar-box">
                    <img
                        src="{{ $profileImage }}"
                        alt="Profile Image"
                        class="avatar"
                        onerror="this.onerror=null;this.src='https://ui-avatars.com/api/?name={{ urlencode(trim(($provider->first_name ?? '') . ' ' . ($provider->last_name ?? '')) ?: 'Provider') }}&background=020617&color=38bdf8&size=256';"
                    >

                    <div class="upload-group">
                        <label class="form-label">Profile Image</label>
                        <input
                            type="file"
                            name="profile_image"
                            class="form-control"
                            accept=".jpg,.jpeg,.png,.gif,.webp,image/*"
                        >
                        <div class="file-note">Accepted: JPG, JPEG, PNG, GIF, WEBP. Max 5MB.</div>
                        @error('profile_image')
                            <div class="error-text">{{ $message }}</div>
                        @enderror

                        <div class="debug-url">
                            Current image URL: {{ $profileImage }}
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">First Name</label>
                    <input class="form-control" value="{{ $provider->first_name }}" disabled>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Last Name</label>
                    <input class="form-control" value="{{ $provider->last_name }}" disabled>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Suffix</label>
                    <input class="form-control" value="{{ $provider->suffix }}" disabled>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Email</label>
                    <input class="form-control" value="{{ $provider->email }}" disabled>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Phone</label>
                    <input
                        name="phone"
                        class="form-control"
                        value="{{ old('phone', $provider->phone) }}"
                        placeholder="09XXXXXXXXX"
                    >
                    @error('phone')
                        <div class="error-text">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-4">
                    <label class="form-label">Country</label>
                    <input class="form-control" value="{{ $provider->country }}" disabled>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Region</label>
                    <input class="form-control" value="{{ $provider->region }}" disabled>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Province</label>
                    <input class="form-control" value="{{ $provider->province }}" disabled>
                </div>

                <div class="col-md-4">
                    <label class="form-label">City</label>
                    <input class="form-control" value="{{ $provider->city }}" disabled>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Barangay</label>
                    <input class="form-control" value="{{ $provider->barangay }}" disabled>
                </div>

                <div class="col-md-12">
                    <label class="form-label">Full Address</label>
                    <textarea class="form-control" rows="2" disabled>{{ $provider->address }}</textarea>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Account Status</label>
                    <input class="form-control" value="{{ ucfirst($provider->status) }}" disabled>
                </div>
            </div>

            <button type="submit" class="btn btn-primary mt-4 px-4">
                Save Changes
            </button>
        </form>

        <div class="divider"></div>

        <div class="profile-title mb-1">Change Password</div>
        <div class="profile-subtitle mb-3">Security settings</div>

        <form method="POST" action="{{ route('provider.password.update') }}">
            @csrf

            <div class="row g-3">
                <div class="col-md-4">
                    <input type="password" name="current_password" class="form-control" placeholder="Current Password" required>
                    @error('current_password')
                        <div class="error-text">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-4">
                    <input type="password" name="password" class="form-control" placeholder="New Password" required>
                    @error('password')
                        <div class="error-text">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-4">
                    <input type="password" name="password_confirmation" class="form-control" placeholder="Confirm Password" required>
                </div>
            </div>

            <button type="submit" class="btn btn-danger mt-3 px-4">
                Update Password
            </button>
        </form>

    </div>
</div>

@endsection