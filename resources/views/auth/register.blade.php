<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Register — VIP Windows</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        body {
            min-height: 100vh;
            display: flex; align-items: center; justify-content: center;
            background: linear-gradient(135deg, #1a3a5c 0%, #244e78 50%, #2d6a9f 100%);
            font-family: 'Segoe UI', system-ui, sans-serif;
        }
        .register-card {
            width: 100%; max-width: 480px;
            border-radius: .75rem; border: none;
            box-shadow: 0 8px 32px rgba(0,0,0,.25);
        }
        .brand-header { text-align: center; margin-bottom: 1.5rem; }
        .brand-header h2 { font-weight: 700; color: #1a3a5c; }
        .brand-header h2 span { color: #e8a838; }
        .btn-vip { background: #e8a838; color: #fff; border: none; }
        .btn-vip:hover { background: #d49530; color: #fff; }
    </style>
</head>
<body>
    <div class="card register-card p-4">
        <div class="brand-header">
            <h2><i class="bi bi-building"></i> <span>VIP</span> Windows</h2>
            <p class="text-muted mb-0">Create Staff Account</p>
        </div>

        @if($errors->any())
            <div class="alert alert-danger py-2">
                @foreach($errors->all() as $e)
                    <div>{{ $e }}</div>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('register') }}">
            @csrf
            <div class="mb-3">
                <label class="form-label">Full Name</label>
                <input type="text" name="name" class="form-control" value="{{ old('name') }}" required autofocus>
            </div>
            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" value="{{ old('email') }}" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Phone <span class="text-muted">(optional)</span></label>
                <input type="text" name="phone" class="form-control" value="{{ old('phone') }}">
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Confirm Password</label>
                    <input type="password" name="password_confirmation" class="form-control" required>
                </div>
            </div>
            <button type="submit" class="btn btn-vip w-100 py-2 fw-semibold">Create Account</button>
        </form>

        <div class="text-center mt-3">
            <a href="{{ route('login') }}" class="text-decoration-none">Already have an account? Sign in</a>
        </div>
    </div>
</body>
</html>
