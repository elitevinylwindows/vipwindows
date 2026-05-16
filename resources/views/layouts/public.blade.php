<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'VIP Windows') — Professional Window Installation</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        :root {
            --vip-primary: #111111;
            --vip-accent: #c9a84c;
            --vip-light: #f7f6f3;
            --vip-dark: #0a0a0a;
        }
        body { font-family: 'Segoe UI', system-ui, sans-serif; color: #333; }

        /* Navbar */
        .navbar-public {
            background: rgba(10, 10, 10, 0.97);
            backdrop-filter: blur(10px);
            padding: .75rem 0;
        }
        .navbar-public .navbar-brand { color: #fff; font-weight: 700; font-size: 1.4rem; letter-spacing: .5px; }
        .navbar-public .navbar-brand span { color: var(--vip-accent); }
        .navbar-public .nav-link { color: rgba(255,255,255,.85); font-weight: 500; padding: .5rem 1rem; }
        .navbar-public .nav-link:hover { color: var(--vip-accent); }
        .navbar-public .nav-link.active { color: var(--vip-accent); }

        /* Buttons */
        .btn-vip { background: var(--vip-accent); color: #fff; border: none; font-weight: 600; }
        .btn-vip:hover { background: #b8973f; color: #fff; }
        .btn-outline-vip { border: 2px solid var(--vip-accent); color: var(--vip-accent); font-weight: 600; }
        .btn-outline-vip:hover { background: var(--vip-accent); color: #fff; }

        /* Hero */
        .hero-section {
            background: linear-gradient(135deg, #0a0a0a 0%, #1a1a1a 50%, #2a2a2a 100%);
            color: #fff;
            position: relative;
            overflow: hidden;
        }
        .hero-section::after {
            content: '';
            position: absolute; top: 0; right: 0; bottom: 0; left: 0;
            background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.03'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
            pointer-events: none;
        }

        /* Sections */
        .section-heading { font-weight: 700; color: var(--vip-primary); margin-bottom: 1rem; }
        .section-subtext { color: #666; max-width: 600px; margin: 0 auto 2rem; }

        /* Service cards */
        .service-card {
            border: none; border-radius: .75rem;
            box-shadow: 0 2px 12px rgba(0,0,0,.08);
            transition: transform .2s, box-shadow .2s;
        }
        .service-card:hover { transform: translateY(-4px); box-shadow: 0 8px 24px rgba(0,0,0,.12); }
        .service-icon {
            width: 64px; height: 64px;
            background: linear-gradient(135deg, #1a1a1a, #333);
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            color: #fff; font-size: 1.5rem;
            margin: 0 auto 1rem;
        }

        /* Stats bar */
        .stats-bar { background: var(--vip-dark); color: #fff; }
        .stat-number { font-size: 2rem; font-weight: 700; color: var(--vip-accent); }

        /* Testimonial */
        .testimonial-card {
            border: none; border-left: 4px solid var(--vip-accent);
            border-radius: 0 .5rem .5rem 0;
            box-shadow: 0 2px 8px rgba(0,0,0,.06);
        }

        /* CTA */
        .cta-section {
            background: linear-gradient(135deg, var(--vip-accent), #b8973f);
            color: #fff;
        }

        /* Footer */
        .site-footer {
            background: var(--vip-dark);
            color: rgba(255,255,255,.7);
        }
        .site-footer a { color: rgba(255,255,255,.7); text-decoration: none; }
        .site-footer a:hover { color: var(--vip-accent); }
        .footer-heading { color: #fff; font-weight: 600; margin-bottom: 1rem; }
    </style>
    @stack('styles')
</head>
<body>
    {{-- Navbar --}}
    <nav class="navbar navbar-expand-lg navbar-public fixed-top">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="{{ route('home') }}">
                <img src="/images/logo.png" alt="VIP Windows" style="height:55px; margin-right:8px;">
            </a>
            <button class="navbar-toggler border-0 text-white" type="button" data-bs-toggle="collapse" data-bs-target="#navPublic">
                <i class="bi bi-list fs-4"></i>
            </button>
            <div class="collapse navbar-collapse" id="navPublic">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}" href="{{ route('home') }}">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('services') ? 'active' : '' }}" href="{{ route('services') }}">Services</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('gallery') ? 'active' : '' }}" href="{{ route('gallery') }}">Gallery</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('service-areas') ? 'active' : '' }}" href="{{ route('service-areas') }}">Service Areas</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('about') ? 'active' : '' }}" href="{{ route('about') }}">About Us</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('contact') ? 'active' : '' }}" href="{{ route('contact') }}">Contact</a>
                    </li>
                </ul>
                <div class="d-flex gap-2 align-items-center">
                    @auth('vip')
                        @if(Auth::guard('vip')->user()->isAdmin())
                            <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-vip btn-sm">Admin Panel</a>
                        @elseif(Auth::guard('vip')->user()->isInstaller())
                            <a href="{{ route('installer.dashboard') }}" class="btn btn-outline-vip btn-sm">Installer Portal</a>
                        @else
                            <a href="{{ route('customer.dashboard') }}" class="btn btn-outline-vip btn-sm">My Account</a>
                            <a href="{{ route('customer.book') }}" class="btn btn-vip btn-sm">Book Installation</a>
                        @endif
                        <form method="POST" action="{{ route('logout') }}" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-outline-light opacity-75">Logout</button>
                        </form>
                    @else
                        <a href="{{ route('login') }}" class="btn btn-outline-vip btn-sm">Sign In</a>
                        <a href="{{ route('register') }}" class="btn btn-vip btn-sm">Sign Up</a>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    {{-- Flash --}}
    @if(session('success'))
        <div class="container mt-3" style="padding-top:70px;">
            <div class="alert alert-success alert-dismissible fade show">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        </div>
    @endif

    {{-- Page content --}}
    @yield('content')

    {{-- Footer --}}
    <footer class="site-footer py-5">
        <div class="container">
            <div class="row g-4">
                <div class="col-lg-4">
                    <h5 class="footer-heading"><i class="bi bi-building me-1"></i> <span style="color:var(--vip-accent)">VIP</span> Windows</h5>
                    <p class="small">Professional window installation services. Licensed, insured, and committed to quality craftsmanship on every project.</p>
                </div>
                <div class="col-lg-2">
                    <h6 class="footer-heading">Quick Links</h6>
                    <ul class="list-unstyled small">
                        <li class="mb-1"><a href="{{ route('home') }}">Home</a></li>
                        <li class="mb-1"><a href="{{ route('services') }}">Services</a></li>
                        <li class="mb-1"><a href="{{ route('gallery') }}">Gallery</a></li>
                        <li class="mb-1"><a href="{{ route('about') }}">About Us</a></li>
                    </ul>
                </div>
                <div class="col-lg-3">
                    <h6 class="footer-heading">Services</h6>
                    <ul class="list-unstyled small">
                        <li class="mb-1">Window Installation</li>
                        <li class="mb-1">Window Replacement</li>
                        <li class="mb-1">Sliding Doors</li>
                        <li class="mb-1">Commercial Projects</li>
                    </ul>
                </div>
                <div class="col-lg-3">
                    <h6 class="footer-heading">Contact</h6>
                    <ul class="list-unstyled small">
                        <li class="mb-1"><i class="bi bi-telephone me-1"></i> (562) 368-0313</li>
                        <li class="mb-1"><i class="bi bi-envelope me-1"></i> info@vipwindows.net</li>
                        <li class="mb-1"><i class="bi bi-clock me-1"></i> Mon-Fri 8am - 5pm</li>
                    </ul>
                </div>
            </div>
            <hr class="my-4" style="border-color:rgba(255,255,255,.1);">
            <div class="text-center small">&copy; {{ date('Y') }} VIP Windows. All rights reserved.</div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>
