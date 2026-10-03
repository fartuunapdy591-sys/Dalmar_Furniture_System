@extends('layouts.app')
@section('title', 'My Profile')
@section('content')
    <div class="page-title mb-1">My Profile</div>
    <div class="page-subtitle">Manage your account email and password</div>

    <div class="row g-3">
        <div class="col-lg-6">
            <div class="card-panel">
                <div class="card-panel-title">Account Information</div>
                <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data">
                    @csrf @method('PUT')
                    <div class="mb-3 d-flex align-items-center gap-3">
                        @if($user->avatar_url)
                            <img src="{{ $user->avatar_url }}" alt="" id="avatarPreview" class="avatar" style="width:64px;height:64px;font-size:24px;object-fit:cover;">
                        @else
                            <span id="avatarPreview" class="avatar" style="width:64px;height:64px;font-size:24px;">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                        @endif
                        <div>
                            <label class="form-label small fw-semibold d-block mb-1">Profile Picture</label>
                            <input type="file" name="avatar" accept="image/*" class="form-control form-control-sm" onchange="previewAvatar(this)">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Full Name</label>
                        <input type="text" name="name" class="form-control" value="{{ old('name', $user->name) }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Email Address</label>
                        <input type="email" name="email" class="form-control" value="{{ old('email', $user->email) }}" required>
                    </div>
                    <button type="submit" class="btn btn-navy">Save Changes</button>
                </form>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card-panel">
                <div class="card-panel-title">Change Password</div>
                <form action="{{ route('profile.password') }}" method="POST">
                    @csrf @method('PUT')
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Current Password</label>
                        <div class="password-field">
                            <input type="password" name="current_password" class="form-control" required>
                            <button type="button" class="toggle-password" tabindex="-1"><i class="bi bi-eye"></i></button>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">New Password</label>
                        <div class="password-field">
                            <input type="password" name="password" class="form-control" required minlength="6">
                            <button type="button" class="toggle-password" tabindex="-1"><i class="bi bi-eye"></i></button>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Confirm New Password</label>
                        <div class="password-field">
                            <input type="password" name="password_confirmation" class="form-control" required minlength="6">
                            <button type="button" class="toggle-password" tabindex="-1"><i class="bi bi-eye"></i></button>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-navy">Update Password</button>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
<script>
    function previewAvatar(input) {
        if (!input.files || !input.files[0]) return;
        const reader = new FileReader();
        reader.onload = function (e) {
            const old = document.getElementById('avatarPreview');
            const img = document.createElement('img');
            img.id = 'avatarPreview';
            img.className = 'avatar';
            img.style.cssText = 'width:64px;height:64px;font-size:24px;object-fit:cover;';
            img.src = e.target.result;
            old.replaceWith(img);
        };
        reader.readAsDataURL(input.files[0]);
    }

    document.querySelectorAll('.toggle-password').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const input = btn.previousElementSibling;
            const icon = btn.querySelector('i');
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.replace('bi-eye', 'bi-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.replace('bi-eye-slash', 'bi-eye');
            }
        });
    });
</script>
@endsection
