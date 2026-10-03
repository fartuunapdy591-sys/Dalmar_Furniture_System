<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password | Dalmar Furniture</title>
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
        <div style="width: 340px;">
            <h3 class="fw-bold mb-1">Forgot Password?</h3>
            <p class="text-muted mb-4">Enter your email and we'll help you reset it</p>

            @if($errors->any())
                <div class="alert alert-danger py-2 small">{{ $errors->first() }}</div>
            @endif

            @if(session('resetUrl'))
                <div class="alert alert-success py-2 small">
                    <p class="mb-2">A password reset link has been generated for <strong>{{ session('resetEmail') }}</strong>.</p>
                    <p class="mb-2 text-muted">This system doesn't have an email server configured, so here's your link instead — normally this would be emailed to you:</p>
                    <a href="{{ session('resetUrl') }}" class="d-block text-break small">{{ session('resetUrl') }}</a>
                </div>
            @endif

            <form method="POST" action="{{ route('password.email') }}">
                @csrf
                <div class="mb-3">
                    <label class="form-label small fw-semibold">Email Address</label>
                    <input type="email" name="email" class="form-control" placeholder="Enter your email" value="{{ old('email') }}" required autofocus>
                </div>
                <button type="submit" class="btn btn-navy w-100 py-2">Send Reset Link</button>
            </form>

            <p class="small text-muted mt-4 mb-0">
                Remembered your password? <a href="{{ route('login') }}" class="text-decoration-none fw-semibold">Sign In</a>
            </p>
        </div>
    </div>
</div>
</body>
</html>
