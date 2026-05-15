<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sign In — VIP Windows</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        body {
            min-height: 100vh;
            display: flex; align-items: center; justify-content: center;
            background: linear-gradient(135deg, #0a0a0a 0%, #1a1a1a 50%, #2a2a2a 100%);
            font-family: 'Segoe UI', system-ui, sans-serif;
        }
        .login-card {
            width: 100%; max-width: 420px;
            border-radius: .75rem; border: none;
            box-shadow: 0 8px 32px rgba(0,0,0,.25);
        }
        .brand-header { text-align: center; margin-bottom: 1.5rem; }
        .brand-header h2 { font-weight: 700; color: #111; }
        .brand-header h2 span { color: #c9a84c; }
        .btn-vip { background: #c9a84c; color: #fff; border: none; }
        .btn-vip:hover { background: #b8973f; color: #fff; }
    </style>
</head>
<body>
    <div class="card login-card p-4">
        <div class="brand-header">
            <img src="/images/logo.png" alt="VIP Windows" style="height:80px;" class="mb-2">
            <p class="text-muted mb-0">Sign in to your account</p>
        </div>

        @if($errors->any())
            <div class="alert alert-danger py-2">
                @foreach($errors->all() as $e)
                    <div>{{ $e }}</div>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf
            <div class="mb-3">
                <label class="form-label">Email</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                    <input type="email" name="email" class="form-control" value="{{ old('email') }}" required autofocus>
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">Password</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-lock"></i></span>
                    <input type="password" name="password" class="form-control" required>
                </div>
            </div>
            <div class="mb-3 form-check">
                <input type="checkbox" name="remember" class="form-check-input" id="remember">
                <label class="form-check-label" for="remember">Remember me</label>
            </div>
            <button type="submit" class="btn btn-vip w-100 py-2 fw-semibold">Sign In</button>
        </form>

        <div class="text-center mt-3">
            <a href="{{ route('register') }}" class="text-decoration-none">Create an account</a>
        </div>
    </div>
</body>
</html>
