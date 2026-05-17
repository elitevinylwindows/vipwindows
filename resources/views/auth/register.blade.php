<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Create Account — VIP Windows</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        body {
            min-height: 100vh;
            display: flex; align-items: center; justify-content: center;
            background: linear-gradient(135deg, #0a0a0a 0%, #1a1a1a 50%, #2a2a2a 100%);
            font-family: 'Segoe UI', system-ui, sans-serif;
            padding: 2rem 1rem;
        }
        .register-card {
            width: 100%; max-width: 480px;
            border-radius: .75rem; border: none;
            box-shadow: 0 8px 32px rgba(0,0,0,.25);
        }
        .brand-header { text-align: center; margin-bottom: .75rem; }
        .btn-vip { background: #c9a84c; color: #fff; border: none; }
        .btn-vip:hover { background: #b8973f; color: #fff; }
        .form-label { font-size: .8rem; margin-bottom: .2rem; }
        .form-control { padding: .35rem .65rem; font-size: .85rem; }
        .form-check-label { font-size: .82rem; }
        hr { margin: .5rem 0; }
    </style>
</head>
<body>
    <div class="card register-card p-3 px-4">
        <div class="brand-header">
            <img src="/images/logo.png" alt="VIP Windows" style="height:60px;" class="mb-1">
            <p class="text-muted small mb-0">Create Your Account</p>
        </div>

        @if($errors->any())
            <div class="alert alert-danger py-1 small mb-2">
                @foreach($errors->all() as $e)
                    <div>{{ $e }}</div>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('register') }}">
            @csrf

            {{-- Account type --}}
            <div class="mb-2">
                <label class="form-label fw-semibold">I am a...</label>
                <div class="d-flex gap-3">
                    <div class="form-check">
                        <input type="radio" name="user_type" value="customer" id="typeCustomer" class="form-check-input" {{ old('user_type', 'customer') === 'customer' ? 'checked' : '' }}>
                        <label class="form-check-label" for="typeCustomer"><i class="bi bi-house-door me-1"></i> Customer</label>
                    </div>
                    <div class="form-check">
                        <input type="radio" name="user_type" value="installer" id="typeInstaller" class="form-check-input" {{ old('user_type') === 'installer' ? 'checked' : '' }}>
                        <label class="form-check-label" for="typeInstaller"><i class="bi bi-tools me-1"></i> Installer</label>
                    </div>
                </div>
            </div>

            <div class="mb-2">
                <label class="form-label">Full Name</label>
                <input type="text" name="name" class="form-control" value="{{ old('name') }}" required autofocus>
            </div>
            <div class="row g-2">
                <div class="col-md-6 mb-2">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" value="{{ old('email') }}" required>
                </div>
                <div class="col-md-6 mb-2">
                    <label class="form-label">Phone <span class="text-muted">(optional)</span></label>
                    <input type="text" name="phone" class="form-control" value="{{ old('phone') }}">
                </div>
            </div>

            <hr>
            <p class="text-muted mb-1" style="font-size:.72rem;">Address (optional — can be added later)</p>

            <div class="mb-2">
                <label class="form-label">Street Address</label>
                <input type="text" name="address" class="form-control" value="{{ old('address') }}">
            </div>
            <div class="row g-2">
                <div class="col-5 mb-2">
                    <label class="form-label">City</label>
                    <input type="text" name="city" class="form-control" value="{{ old('city') }}">
                </div>
                <div class="col-4 mb-2">
                    <label class="form-label">State</label>
                    <input type="text" name="state" class="form-control" value="{{ old('state') }}">
                </div>
                <div class="col-3 mb-2">
                    <label class="form-label">Zip</label>
                    <input type="text" name="zip" class="form-control" value="{{ old('zip') }}">
                </div>
            </div>

            <hr>

            <div class="row g-2">
                <div class="col-md-6 mb-2">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <div class="col-md-6 mb-2">
                    <label class="form-label">Confirm Password</label>
                    <input type="password" name="password_confirmation" class="form-control" required>
                </div>
            </div>
            <button type="submit" class="btn btn-vip w-100 py-2 fw-semibold mt-1">Create Account</button>
        </form>

        <div class="text-center mt-2 mb-1">
            <a href="{{ route('login') }}" class="text-decoration-none small">Already have an account? Sign in</a>
        </div>
    </div>
</body>
</html>
