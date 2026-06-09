<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Login — VIP Windows</title>
    <link rel="icon" href="/favicon.ico" sizes="32x32">
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        body {
            min-height: 100vh;
            display: flex; align-items: center; justify-content: center;
            background: linear-gradient(135deg, #0a0a0a 0%, #111 50%, #1a1a1a 100%);
            font-family: 'Segoe UI', system-ui, sans-serif;
        }
        .login-card {
            width: 100%; max-width: 420px;
            border-radius: .75rem; border: none;
            box-shadow: 0 8px 32px rgba(0,0,0,.35);
        }
        .brand-header { text-align: center; margin-bottom: 1rem; }
        .btn-vip { background: #c9a84c; color: #fff; border: none; font-weight: 600; }
        .btn-vip:hover { background: #b8973f; color: #fff; }
        .admin-badge {
            display: inline-block;
            font-size: .65rem;
            letter-spacing: 2px;
            text-transform: uppercase;
            color: #c9a84c;
            border: 1px solid #c9a84c;
            padding: .2rem .75rem;
            border-radius: .25rem;
            margin-top: .5rem;
        }
    </style>
</head>
<body>
    <div class="card login-card">
        <div class="card-body p-4">
            <div class="brand-header">
                <a href="{{ route('home') }}">
                    <img src="/images/logo.png" alt="VIP Windows" style="height:60px;">
                </a>
                <div class="admin-badge">Admin Panel</div>
            </div>

            <p class="text-muted text-center small mb-3">Sign in with your administrator credentials</p>

            @if($errors->any())
                <div class="alert alert-danger py-2 small">
                    @foreach($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('admin.login.submit') }}">
                @csrf
                <div class="mb-3">
                    <label class="form-label small fw-semibold">Email</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                        <input type="email" name="email" class="form-control" placeholder="admin@vipwindowsinc.com" value="{{ old('email') }}" required autofocus>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-semibold">Password</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-lock"></i></span>
                        <input type="password" name="password" class="form-control" placeholder="Enter password" required>
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="form-check">
                        <input type="checkbox" name="remember" class="form-check-input" id="remember">
                        <label class="form-check-label small" for="remember">Remember me</label>
                    </div>
                </div>

                <button type="submit" class="btn btn-vip w-100 py-2">
                    <i class="bi bi-shield-lock me-1"></i> Sign In
                </button>
            </form>

            <div class="text-center mt-3">
                <a href="{{ route('home') }}" class="text-muted small text-decoration-none">
                    <i class="bi bi-arrow-left me-1"></i> Back to website
                </a>
            </div>
        </div>
    </div>
</body>
</html>
