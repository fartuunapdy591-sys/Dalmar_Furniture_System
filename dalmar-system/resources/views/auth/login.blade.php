<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In | Dalmar Furniture</title>
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
            <h3 class="fw-bold mb-1">Welcome Back!</h3>
            <p class="text-muted mb-4">Sign in to continue</p>

            @if($errors->any())
                <div class="alert alert-danger py-2 small">{{ $errors->first() }}</div>
            @endif

            <form method="POST" action="{{ route('login.attempt') }}">
                @csrf
                <div class="mb-3">
                    <label class="form-label small fw-semibold">Email Address</label>
                    <input type="email" name="email" class="form-control" placeholder="Enter your email" value="{{ old('email') }}" required autofocus>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-semibold">Password</label>
                    <div class="password-field">
                        <input type="password" name="password" id="loginPassword" class="form-control" placeholder="Enter your password" required>
                        <button type="button" class="toggle-password" tabindex="-1"><i class="bi bi-eye"></i></button>
                    </div>
                </div>
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="remember" id="remember">
                        <label class="form-check-label small" for="remember">Remember me</label>
                    </div>
                    <a href="{{ route('password.request') }}" class="small text-decoration-none">Forgot password?</a>
                </div>
                <button type="submit" class="btn btn-navy w-100 py-2">Sign In</button>
            </form>

            <p class="small text-muted mt-4 mb-3">Demo: admin@dalmar.com / password</p>

            <a href="{{ route('home') }}" class="btn btn-outline-secondary w-100 py-2">
                <i class="bi bi-box-arrow-up-right me-1"></i> View Site
            </a>
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
