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
        }
        .register-card {
            width: 100%; max-width: 520px;
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
    <div class="card register-card p-4">
        <div class="brand-header">
            <img src="/images/logo.png" alt="VIP Windows" style="height:100px;" class="mb-2">
            <p class="text-muted mb-0">Create Your Account</p>
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

            {{-- Account type toggle --}}
            <div class="mb-3">
                <label class="form-label fw-semibold">I am a...</label>
                <div class="d-flex gap-2">
                    <div class="form-check flex-fill">
                        <input type="radio" name="user_type" value="customer" id="typeCustomer" class="form-check-input" {{ old('user_type', 'customer') === 'customer' ? 'checked' : '' }}>
                        <label class="form-check-label" for="typeCustomer">
                            <i class="bi bi-house-door me-1"></i> Homeowner / Customer
                        </label>
                    </div>
                    <div class="form-check flex-fill">
                        <input type="radio" name="user_type" value="installer" id="typeInstaller" class="form-check-input" {{ old('user_type') === 'installer' ? 'checked' : '' }}>
                        <label class="form-check-label" for="typeInstaller">
                            <i class="bi bi-tools me-1"></i> Window Installer
                        </label>
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Full Name</label>
                <input type="text" name="name" class="form-control" value="{{ old('name') }}" required autofocus>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" value="{{ old('email') }}" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Phone <span class="text-muted">(optional)</span></label>
                    <input type="text" name="phone" class="form-control" value="{{ old('phone') }}">
                </div>
            </div>

            <hr class="my-3">
            <p class="text-muted small mb-2">Address (optional — can be added later)</p>

            <div class="mb-3">
                <label class="form-label">Street Address</label>
                <input type="text" name="address" class="form-control" value="{{ old('address') }}">
            </div>
            <div class="row">
                <div class="col-md-5 mb-3">
                    <label class="form-label">City</label>
                    <input type="text" name="city" class="form-control" value="{{ old('city') }}">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">State</label>
                    <input type="text" name="state" class="form-control" value="{{ old('state') }}">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Zip</label>
                    <input type="text" name="zip" class="form-control" value="{{ old('zip') }}">
                </div>
            </div>

            <hr class="my-3">

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
