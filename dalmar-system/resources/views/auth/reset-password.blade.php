<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password | Dalmar Furniture</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="{{ asset('css/style.css') }}" rel="stylesheet">
</head>
<body>
<div class="auth-wrapper">
    <div class="auth-visual">
        <img src="{{ asset('images/login-furniture.jpg') }}" alt="" class="auth-visual-img">
        <div class="auth-visual-overlay"></div>
        <div class="auth-visual-content">
            <div class="brand-icon"><i class="bi bi-house-door-fill"></i></div>
            <h2 class="fw-bold mb-1" style="text-shadow: 0 2px 12px rgba(0,0,0,.6);">DALMAR</h2>
            <p class="text-uppercase small mb-0" style="letter-spacing:2px; color:#f2b134; text-shadow: 0 2px 8px rgba(0,0,0,.6);">Furniture &amp; House Interior</p>
        </div>
    </div>

    <div class="auth-form-side">
        <div style="width: 320px;">
            <h3 class="fw-bold mb-1">Reset Password</h3>
            <p class="text-muted mb-4">Choose a new password for your account</p>

            @if($errors->any())
                <div class="alert alert-danger py-2 small">{{ $errors->first() }}</div>
            @endif

            <form method="POST" action="{{ route('password.update') }}">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">
                <div class="mb-3">
                    <label class="form-label small fw-semibold">Email Address</label>
                    <input type="email" name="email" class="form-control" value="{{ old('email', $email) }}" required autofocus>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-semibold">New Password</label>
                    <div class="password-field">
                        <input type="password" name="password" class="form-control" placeholder="Enter a new password" required minlength="6">
                        <button type="button" class="toggle-password" tabindex="-1"><i class="bi bi-eye"></i></button>
                    </div>
                </div>
                <div class="mb-4">
                    <label class="form-label small fw-semibold">Confirm Password</label>
                    <div class="password-field">
                        <input type="password" name="password_confirmation" class="form-control" placeholder="Re-enter the new password" required minlength="6">
                        <button type="button" class="toggle-password" tabindex="-1"><i class="bi bi-eye"></i></button>
                    </div>
                </div>
                <button type="submit" class="btn btn-navy w-100 py-2">Reset Password</button>
            </form>

            <p class="small text-muted mt-4 mb-0">
                <a href="{{ route('login') }}" class="text-decoration-none fw-semibold">Back to Sign In</a>
            </p>
        </div>
    </div>
</div>

<script>
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
</body>
</html>
