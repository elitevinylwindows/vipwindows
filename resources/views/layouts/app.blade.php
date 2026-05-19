<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'VIP Windows') — VIP Windows Admin</title>
    <link rel="icon" href="/favicon.ico" sizes="32x32">
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    <link rel="apple-touch-icon" href="/favicon-32x32.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        :root {
            --vip-primary: #111111;
            --vip-accent: #c9a84c;
            --vip-light: #f7f6f3;
            --sidebar-width: 240px;
        }
        body { background: var(--vip-light); font-family: 'Segoe UI', system-ui, sans-serif; margin: 0; }

        /* Sidebar */
        .admin-sidebar {
            position: fixed; top: 0; left: 0; bottom: 0;
            width: var(--sidebar-width);
            background: linear-gradient(180deg, #0a0a0a 0%, #141414 100%);
            color: #fff;
            z-index: 1040;
            display: flex; flex-direction: column;
            transition: transform .3s;
        }
        .sidebar-brand {
            padding: 1.25rem 1.25rem 1rem;
            border-bottom: 1px solid rgba(255,255,255,.08);
            text-align: center;
        }
        .sidebar-brand img { height: 70px; }
        .sidebar-brand .badge { font-size: .6rem; vertical-align: middle; }

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
        .sidebar-user .user-role { color: rgba(255,255,255,.4); font-size: .75rem; }

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
            <a href="{{ route('home') }}" target="_blank">
                <img src="/images/logo.png" alt="VIP Windows">
            </a>
        </div>

        <nav class="sidebar-nav">
            <div class="nav-label">Main</div>
            <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <i class="bi bi-speedometer2"></i> Dashboard
            </a>
            <a href="{{ route('admin.calendar.index') }}" class="{{ request()->routeIs('admin.calendar.*') ? 'active' : '' }}">
                <i class="bi bi-calendar3"></i> Calendar
            </a>
            <a href="{{ route('admin.jobs.index') }}" class="{{ request()->routeIs('admin.jobs.*') ? 'active' : '' }}">
                <i class="bi bi-tools"></i> Jobs
            </a>
            {{-- Orders and Quotes hidden for now
            <a href="{{ route('admin.orders.index') }}" class="{{ request()->routeIs('admin.orders.*') ? 'active' : '' }}">
                <i class="bi bi-clipboard-check"></i> Orders
            </a>
            <a href="{{ route('admin.quotes.index') }}" class="{{ request()->routeIs('admin.quotes.*') ? 'active' : '' }}">
                <i class="bi bi-calculator"></i> Quotes
            </a>
            --}}
            <a href="{{ route('admin.invoices.index') }}" class="{{ request()->routeIs('admin.invoices.*') ? 'active' : '' }}">
                <i class="bi bi-receipt"></i> Invoices
            </a>
            <a href="{{ route('admin.tech-measures.index') }}" class="{{ request()->routeIs('admin.tech-measures.*') ? 'active' : '' }}">
                <i class="bi bi-rulers"></i> Tech Measures
            </a>
            <a href="{{ route('admin.services.index') }}" class="{{ request()->routeIs('admin.services.*') ? 'active' : '' }}">
                <i class="bi bi-wrench"></i> Services
            </a>

            <div class="nav-label mt-3">Management</div>
            <a href="{{ route('admin.team.index') }}" class="{{ request()->routeIs('admin.team.*') ? 'active' : '' }}">
                <i class="bi bi-people-fill"></i> Team Members
            </a>
            <a href="{{ route('admin.installers.index') }}" class="{{ request()->routeIs('admin.installers.*') ? 'active' : '' }}">
                <i class="bi bi-person-badge"></i> Installers
            </a>
            <a href="{{ route('admin.crews.index') }}" class="{{ request()->routeIs('admin.crews.*') ? 'active' : '' }}">
                <i class="bi bi-diagram-3"></i> Crews
            </a>
            <a href="{{ route('admin.customers.index') }}" class="{{ request()->routeIs('admin.customers.*') ? 'active' : '' }}">
                <i class="bi bi-people"></i> Customers
            </a>
            <a href="{{ route('admin.attendance.index') }}" class="{{ request()->routeIs('admin.attendance.*') ? 'active' : '' }}">
                <i class="bi bi-clock-history"></i> Attendance
            </a>

            <div class="nav-label mt-3">Content</div>
            <a href="{{ route('admin.gallery.index') }}" class="{{ request()->routeIs('admin.gallery.*') ? 'active' : '' }}">
                <i class="bi bi-images"></i> Gallery
            </a>
            <a href="{{ route('admin.service-areas.index') }}" class="{{ request()->routeIs('admin.service-areas.*') ? 'active' : '' }}">
                <i class="bi bi-geo-alt"></i> Service Areas
            </a>

            <div class="nav-label mt-3">Communication</div>
            <a href="{{ route('admin.email.compose') }}" class="{{ request()->routeIs('admin.email.*') ? 'active' : '' }}">
                <i class="bi bi-envelope"></i> Email
            </a>
            <a href="{{ route('admin.email-templates.index') }}" class="{{ request()->routeIs('admin.email-templates.*') ? 'active' : '' }}">
                <i class="bi bi-envelope-paper"></i> Email Templates
            </a>
            <a href="{{ route('admin.consultations.index') }}" class="{{ request()->routeIs('admin.consultations.*') ? 'active' : '' }}">
                <i class="bi bi-camera-video"></i> Consultations
            </a>
            <a href="{{ route('admin.messages.index') }}" class="{{ request()->routeIs('admin.messages.*') ? 'active' : '' }}">
                <i class="bi bi-chat-dots"></i> Messages
                @php $msgUnread = \App\Models\Message::whereHas('conversation', fn($q) => $q->where('admin_id', auth('vip')->id()))->where('sender_id', '!=', auth('vip')->id())->whereNull('read_at')->count(); @endphp
                @if($msgUnread > 0)
                    <span id="msgUnreadBadge" class="badge bg-danger ms-1" style="font-size:.6rem;">{{ $msgUnread }}</span>
                @else
                    <span id="msgUnreadBadge" class="badge bg-danger ms-1" style="font-size:.6rem; display:none;">0</span>
                @endif
            </a>

            <div class="nav-label mt-3">Configuration</div>
            <a href="{{ route('admin.master.hub') }}" class="{{ request()->routeIs('admin.master.*') ? 'active' : '' }}">
                <i class="bi bi-database-gear"></i> Master Data
            </a>

            <div class="nav-label mt-3">System</div>
            <a href="{{ route('admin.settings.index') }}" class="{{ request()->routeIs('admin.settings.*') ? 'active' : '' }}">
                <i class="bi bi-gear"></i> Settings
            </a>
        </nav>

        <div class="sidebar-user">
            <div class="user-name"><i class="bi bi-person-circle me-1"></i> {{ Auth::user()->name }}</div>
            <div class="user-role">{{ ucfirst(Auth::user()->role) }}</div>
            <a href="{{ route('home') }}" target="_blank" class="btn btn-sm btn-outline-light w-100 mt-2 opacity-75">
                <i class="bi bi-globe me-1"></i> View Website
            </a>
            <form method="POST" action="{{ route('logout') }}" class="mt-1">
                @csrf
                <button class="btn btn-sm btn-outline-light w-100 opacity-50" type="submit">
                    <i class="bi bi-box-arrow-left me-1"></i> Sign Out
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
                <h6 class="mb-0 fw-semibold text-muted">@yield('title', 'Admin')</h6>
            </div>
            <div class="small text-muted">{{ now()->format('l, M d, Y') }}</div>
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
            &copy; {{ date('Y') }} VIP Windows. All rights reserved.
        </footer>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>
