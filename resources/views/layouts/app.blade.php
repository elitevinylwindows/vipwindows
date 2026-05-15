<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'VIP Windows') — VIP Windows Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        :root {
            --vip-primary: #1a3a5c;
            --vip-accent: #e8a838;
            --vip-light: #f4f6f9;
        }
        body { background: var(--vip-light); font-family: 'Segoe UI', system-ui, sans-serif; }
        .navbar-vip {
            background: linear-gradient(135deg, var(--vip-primary) 0%, #244e78 100%);
        }
        .navbar-vip .navbar-brand { color: #fff; font-weight: 700; letter-spacing: .5px; }
        .navbar-vip .navbar-brand span { color: var(--vip-accent); }
        .navbar-vip .nav-link { color: rgba(255,255,255,.85); }
        .navbar-vip .nav-link:hover, .navbar-vip .nav-link.active { color: #fff; }
        .btn-vip { background: var(--vip-accent); color: #fff; border: none; }
        .btn-vip:hover { background: #d49530; color: #fff; }
        .card { border: none; box-shadow: 0 1px 4px rgba(0,0,0,.08); border-radius: .5rem; }
        .stat-card { transition: transform .15s; }
        .stat-card:hover { transform: translateY(-2px); }
        .badge-pending    { background: #ffc107; color: #333; }
        .badge-scheduled  { background: #17a2b8; color: #fff; }
        .badge-in_progress { background: #007bff; color: #fff; }
        .badge-completed  { background: #28a745; color: #fff; }
        .badge-cancelled  { background: #dc3545; color: #fff; }
        .page-wrapper { min-height: calc(100vh - 56px - 60px); }
        footer { background: var(--vip-primary); color: rgba(255,255,255,.7); }
    </style>
    @stack('styles')
</head>
<body>
    {{-- Navbar --}}
    <nav class="navbar navbar-expand-lg navbar-vip">
        <div class="container">
            <a class="navbar-brand" href="{{ route('dashboard') }}">
                <i class="bi bi-building me-1"></i> <span>VIP</span> Windows
            </a>
            <button class="navbar-toggler border-0 text-white" type="button" data-bs-toggle="collapse" data-bs-target="#navMain">
                <i class="bi bi-list fs-4"></i>
            </button>
            <div class="collapse navbar-collapse" id="navMain">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                            <i class="bi bi-speedometer2 me-1"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('orders.*') ? 'active' : '' }}" href="{{ route('orders.index') }}">
                            <i class="bi bi-clipboard-check me-1"></i> Orders
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('calendar.*') ? 'active' : '' }}" href="{{ route('calendar.index') }}">
                            <i class="bi bi-calendar3 me-1"></i> Calendar
                        </a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle me-1"></i> {{ Auth::user()->name }}
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button class="dropdown-item" type="submit"><i class="bi bi-box-arrow-right me-1"></i> Logout</button>
                                </form>
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    {{-- Flash messages --}}
    <div class="container mt-3">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
    </div>

    {{-- Content --}}
    <div class="page-wrapper">
        @yield('content')
    </div>

    {{-- Footer --}}
    <footer class="py-3 mt-4">
        <div class="container text-center small">
            &copy; {{ date('Y') }} VIP Windows. All rights reserved.
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>
