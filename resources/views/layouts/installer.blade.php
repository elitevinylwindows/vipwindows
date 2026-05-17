<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', __('installer.installer_portal')) — VIP Windows</title>
    <link rel="icon" href="/favicon.ico" sizes="32x32">
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        :root {
            --vip-primary: #1a1a2e;
            --vip-accent: #d4a843;
            --vip-light: #fafbfd;
            --sidebar-width: 240px;
        }
        body { background: var(--vip-light); font-family: 'Segoe UI', system-ui, sans-serif; margin: 0; }

        /* Sidebar */
        .admin-sidebar {
            position: fixed; top: 0; left: 0; bottom: 0;
            width: var(--sidebar-width);
            background: linear-gradient(180deg, #1a1a2e 0%, #16213e 100%);
            color: #fff;
            z-index: 1040;
            display: flex; flex-direction: column;
            transition: transform .3s;
            border-top: 3px solid var(--vip-accent);
        }
        .sidebar-brand {
            padding: 1.25rem 1.25rem 1rem;
            border-bottom: 1px solid rgba(255,255,255,.08);
            text-align: center;
        }
        .sidebar-brand img { height: 70px; }
        .sidebar-brand .badge { font-size: .6rem; vertical-align: middle; }
        .sidebar-brand .portal-badge {
            display: inline-block;
            margin-top: .5rem;
            font-size: .65rem;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            color: var(--vip-accent);
            border: 1px solid var(--vip-accent);
            padding: .15rem .6rem;
            border-radius: .25rem;
        }

        .sidebar-nav { flex: 1; padding: 1rem 0; overflow-y: auto; }
        .sidebar-nav .nav-label {
            font-size: .65rem; text-transform: uppercase; letter-spacing: 1.5px;
            color: rgba(255,255,255,.35); padding: .75rem 1.25rem .25rem; font-weight: 600;
        }
        .sidebar-nav a {
            display: flex; align-items: center; gap: .6rem;
            padding: .65rem 1.25rem;
            color: rgba(255,255,255,.7);
            text-decoration: none; font-size: .9rem;
            transition: all .15s; border-left: 3px solid transparent;
        }
        .sidebar-nav a:hover {
            background: rgba(255,255,255,.05);
            color: #fff;
        }
        .sidebar-nav a.active {
            background: rgba(201,168,76,.1);
            color: var(--vip-accent);
            border-left-color: var(--vip-accent);
        }
        .sidebar-nav a i { font-size: 1.1rem; width: 20px; text-align: center; }

        .sidebar-user {
            padding: 1rem 1.25rem;
            border-top: 1px solid rgba(255,255,255,.08);
            font-size: .85rem;
        }
        .sidebar-user .user-name { color: #fff; font-weight: 600; }
        .sidebar-user .user-role { color: var(--vip-accent); font-size: .75rem; }

        /* Main content */
        .admin-main {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
            display: flex; flex-direction: column;
        }

        /* Top bar */
        .admin-topbar {
            background: #fff;
            border-bottom: 1px solid rgba(0,0,0,.06);
            padding: .75rem 1.5rem;
            display: flex; align-items: center; justify-content: space-between;
        }

        .page-wrapper { flex: 1; }

        /* Cards & badges */
        .card { border: none; box-shadow: 0 1px 4px rgba(0,0,0,.08); border-radius: .5rem; }
        .stat-card { transition: transform .15s; }
        .stat-card:hover { transform: translateY(-2px); }
        .btn-vip { background: var(--vip-accent); color: #fff; border: none; }
        .btn-vip:hover { background: #b8973f; color: #fff; }
        .badge-pending    { background: #ffc107; color: #333; }
        .badge-scheduled  { background: #17a2b8; color: #fff; }
        .badge-in_progress { background: #007bff; color: #fff; }
        .badge-completed  { background: #28a745; color: #fff; }
        .badge-cancelled  { background: #dc3545; color: #fff; }

        footer { color: rgba(0,0,0,.4); }

        /* Mobile: collapse sidebar */
        .sidebar-toggle { display: none; }
        @media (max-width: 991.98px) {
            .admin-sidebar { transform: translateX(-100%); }
            .admin-sidebar.show { transform: translateX(0); }
            .admin-main { margin-left: 0; }
            .sidebar-toggle { display: inline-flex; }
            .sidebar-overlay {
                display: none; position: fixed; inset: 0;
                background: rgba(0,0,0,.5); z-index: 1035;
            }
            .admin-sidebar.show ~ .sidebar-overlay { display: block; }
        }
    </style>
    @stack('styles')
</head>
<body>
    {{-- Sidebar --}}
    <aside class="admin-sidebar" id="adminSidebar">
        <div class="sidebar-brand">
            @if(Auth::user()->company_logo)
                <a href="{{ route('installer.dashboard') }}">
                    <img src="{{ asset('uploads/installer-logos/' . Auth::user()->company_logo) }}" alt="{{ Auth::user()->company_name }}" style="height:50px; max-width:180px; object-fit:contain;">
                </a>
                <div class="small text-muted mt-1">{{ __('installer.powered_by') }} VIP Windows</div>
            @else
                <a href="{{ route('home') }}" target="_blank">
                    <img src="/images/logo.png" alt="VIP Windows" style="height:70px;">
                </a>
            @endif
            <div class="portal-badge">{{ __('installer.installer_portal') }}</div>
        </div>

        <nav class="sidebar-nav">
            <div class="nav-label">{{ __('installer.main') }}</div>
            <a href="{{ route('installer.dashboard') }}" class="{{ request()->routeIs('installer.dashboard') ? 'active' : '' }}">
                <i class="bi bi-speedometer2"></i> {{ __('installer.dashboard') }}
            </a>
            <a href="{{ route('installer.quotes.index') }}" class="{{ request()->routeIs('installer.quotes.*') ? 'active' : '' }}">
                <i class="bi bi-calculator"></i> {{ __('installer.my_quotes') }}
            </a>
            <a href="{{ route('installer.jobs.index') }}" class="{{ request()->routeIs('installer.jobs.*') ? 'active' : '' }}">
                <i class="bi bi-tools"></i> {{ __('installer.my_jobs') }}
            </a>
            <a href="{{ route('installer.calendar') }}" class="{{ request()->routeIs('installer.calendar') ? 'active' : '' }}">
                <i class="bi bi-calendar3"></i> {{ __('installer.calendar') }}
            </a>
            <a href="{{ route('installer.invoices.index') }}" class="{{ request()->routeIs('installer.invoices.*') ? 'active' : '' }}">
                <i class="bi bi-receipt"></i> {{ __('installer.my_invoices') }}
            </a>

            <div class="nav-label mt-3">{{ __('installer.customers') }}</div>
            <a href="{{ route('installer.customers.index') }}" class="{{ request()->routeIs('installer.customers.*') ? 'active' : '' }}">
                <i class="bi bi-people"></i> {{ __('installer.my_customers') }}
            </a>

            <div class="nav-label mt-3">{{ __('installer.account') }}</div>
            <a href="{{ route('installer.profile') }}" class="{{ request()->routeIs('installer.profile*') ? 'active' : '' }}">
                <i class="bi bi-person-gear"></i> {{ __('installer.my_profile') }}
            </a>
            <a href="{{ route('home') }}" target="_blank">
                <i class="bi bi-globe"></i> {{ __('installer.view_website') }}
            </a>
        </nav>

        <div class="sidebar-user">
            <div class="user-name"><i class="bi bi-person-circle me-1"></i> {{ Auth::user()->name }}</div>
            <div class="user-role">{{ __('installer.installer') }}</div>
            <form method="POST" action="{{ route('logout') }}" class="mt-2">
                @csrf
                <button class="btn btn-sm btn-outline-light w-100 opacity-50" type="submit">
                    <i class="bi bi-box-arrow-left me-1"></i> {{ __('installer.sign_out') }}
                </button>
            </form>
        </div>
    </aside>

    {{-- Overlay for mobile --}}
    <div class="sidebar-overlay" onclick="document.getElementById('adminSidebar').classList.remove('show'); this.style.display='none';"></div>

    {{-- Main content --}}
    <div class="admin-main">
        {{-- Top bar --}}
        <div class="admin-topbar">
            <div class="d-flex align-items-center gap-2">
                <button class="btn btn-sm btn-outline-dark sidebar-toggle" onclick="document.getElementById('adminSidebar').classList.toggle('show')">
                    <i class="bi bi-list"></i>
                </button>
                <h6 class="mb-0 fw-semibold text-muted">@yield('title', __('installer.installer_portal'))</h6>
            </div>
            <div class="d-flex align-items-center gap-3">
                {{-- Language Switcher --}}
                <div class="dropdown">
                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" style="font-size:.8rem;">
                        <i class="bi bi-globe me-1"></i>{{ strtoupper(app()->getLocale()) }}
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item {{ app()->getLocale() === 'en' ? 'active' : '' }}" href="?lang=en"> English</a></li>
                        <li><a class="dropdown-item {{ app()->getLocale() === 'es' ? 'active' : '' }}" href="?lang=es"> Español</a></li>
                    </ul>
                </div>

                {{-- Profile Dropdown --}}
                <div class="dropdown">
                    <button class="btn btn-sm btn-outline-dark dropdown-toggle" type="button" data-bs-toggle="dropdown" style="font-size:.8rem;">
                        <i class="bi bi-person-circle me-1"></i>{{ Auth::user()->name }}
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="{{ route('installer.profile') }}"><i class="bi bi-person-gear me-2"></i>{{ __('installer.my_profile') }}</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button class="dropdown-item text-danger" type="submit"><i class="bi bi-box-arrow-left me-2"></i>{{ __('installer.sign_out') }}</button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        {{-- Flash messages --}}
        @if(session('success'))
            <div class="px-4 pt-3">
                <div class="alert alert-success alert-dismissible fade show mb-0" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            </div>
        @endif
        @if(session('error'))
            <div class="px-4 pt-3">
                <div class="alert alert-danger alert-dismissible fade show mb-0" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            </div>
        @endif

        {{-- Page content --}}
        <div class="page-wrapper">
            @yield('content')
        </div>

        {{-- Footer --}}
        <footer class="py-3 text-center small">
            <span style="color: rgba(0,0,0,.35);">{{ __('installer.powered_by') }}</span>
            <a href="{{ route('home') }}" target="_blank" style="color: var(--vip-accent); text-decoration: none; font-weight: 600;">VIP Windows</a>
            <span style="color: rgba(0,0,0,.25);">&middot; &copy; {{ date('Y') }}</span>
        </footer>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>
