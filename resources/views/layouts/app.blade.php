<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Monitoring Infrastruktur Tiang') — PT Telkom Infrastruktur</title>
    <meta name="description" content="Sistem Informasi Monitoring Infrastruktur Tiang Telekomunikasi PT Telkom Infrastruktur Indonesia District Lampung">

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Tabler Icons -->
    <link href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/dist/tabler-icons.min.css" rel="stylesheet">
    <!-- DataTables -->
    <link href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <!-- Design System -->
    <link rel="stylesheet" href="{{ asset('css/design-system.css') }}">

    <style>
        /* ── LAYOUT VARIABLES ── */
        :root {
            --sidebar-w: 260px;
            --topbar-h: 56px;
        }

        /* ── LAYOUT ── */
        body { min-height: 100vh; }

        #sidebar {
            position: fixed; top: 0; left: 0; bottom: 0;
            width: var(--sidebar-w);
            display: flex; flex-direction: column; z-index: 1040;
            transition: transform .25s ease;
        }
        .sidebar-brand {
            padding: 1rem 1.25rem;
            border-bottom: 1px solid rgba(255,255,255,.08);
            text-decoration: none;
        }
        .sidebar-nav { flex: 1; overflow-y: auto; padding: .75rem 0; }
        .sidebar-link {
            display: flex; align-items: center; gap: .65rem;
            padding: .6rem 1.25rem;
            text-decoration: none; font-size: .875rem; font-weight: 500;
            border-left: 3px solid transparent; transition: all .15s;
        }
        .sidebar-link i { font-size: 1rem; width: 1.25rem; text-align: center; }
        .sidebar-label { padding: .85rem 1.25rem .35rem; font-size: .65rem; font-weight: 700; letter-spacing: .12em; text-transform: uppercase; }

        #topbar {
            position: fixed; top: 0; left: var(--sidebar-w); right: 0; height: var(--topbar-h);
            display: flex; align-items: center; padding: 0 1.5rem;
            z-index: 1030;
        }
        #main-content {
            margin-left: var(--sidebar-w);
            padding-top: calc(var(--topbar-h) + 1.5rem);
            padding-left: 1.5rem; padding-right: 1.5rem; padding-bottom: 2rem;
            min-height: 100vh;
        }

        /* Stat card layout base */
        .stat-card {
            padding: 1.25rem 1.5rem;
            display: flex; flex-direction: row; align-items: center; gap: 1rem;
        }
        .stat-icon {
            font-size: 1.4rem; flex-shrink: 0;
            display: grid; place-items: center;
        }

        /* ── MAP ── */
        #dashboard-map { height: 420px; overflow: hidden; border-radius: var(--radius-md); }

        /* ── SCROLLBAR ── */
        .sidebar-nav::-webkit-scrollbar { width: 4px; }
        .sidebar-nav::-webkit-scrollbar-thumb { background: rgba(255,255,255,.2); border-radius: 4px; }

        /* ── RESPONSIVE ── */
        @media (max-width: 991px) {
            #sidebar { transform: translateX(-100%); }
            #sidebar.show { transform: translateX(0); }
            #topbar, #main-content { left: 0; margin-left: 0; }
        }
    </style>

    @stack('styles')
</head>
<body>

<!-- ══ SIDEBAR ══════════════════════════════════════════════════════ -->
<nav id="sidebar">
    <a href="{{ route('dashboard') }}" class="sidebar-brand d-flex align-items-center gap-2">
        <div style="width:36px;height:36px;background:var(--accent);border-radius:8px;display:grid;place-items:center;flex-shrink:0">
            <i class="bi bi-broadcast-pin text-white fs-5"></i>
        </div>
        <div>
            <div class="sidebar-brand-title">PT Telkom Infrastruktur</div>
            <div class="sidebar-brand-name">Monitoring Tiang</div>
        </div>
    </a>

    <div class="sidebar-nav">
        <div class="sidebar-label">Utama</div>
        <a href="{{ route('dashboard') }}" class="sidebar-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <i class="bi bi-speedometer2"></i> Dashboard
        </a>
        <a href="{{ route('tiang.index') }}" class="sidebar-link {{ request()->routeIs('tiang.*') ? 'active' : '' }}">
            <i class="bi bi-broadcast"></i> Data Tiang
        </a>
        <a href="{{ route('export.index') }}" class="sidebar-link {{ request()->routeIs('export.*') ? 'active' : '' }}">
            <i class="bi bi-download"></i> Export Data
        </a>

        @auth
        @if(auth()->user()->isAdmin())
        <div class="sidebar-label">Administrasi</div>
        <a href="{{ route('import.index') }}" class="sidebar-link {{ request()->routeIs('import.*') ? 'active' : '' }}">
            <i class="bi bi-upload"></i> Import Excel
        </a>
        <a href="{{ route('master.districts.index') }}" class="sidebar-link {{ request()->routeIs('master.districts.*') ? 'active' : '' }}">
            <i class="bi bi-geo-alt"></i> District
        </a>
        <a href="{{ route('master.areas.index') }}" class="sidebar-link {{ request()->routeIs('master.areas.*') ? 'active' : '' }}">
            <i class="bi bi-map"></i> Area
        </a>
        <a href="{{ route('master.stos.index') }}" class="sidebar-link {{ request()->routeIs('master.stos.*') ? 'active' : '' }}">
            <i class="bi bi-building"></i> STO
        </a>
        <a href="{{ route('master.jenis-tiang.index') }}" class="sidebar-link {{ request()->routeIs('master.jenis-tiang.*') ? 'active' : '' }}">
            <i class="bi bi-layers"></i> Jenis Tiang
        </a>
        <a href="{{ route('master.kondisi-tiang.index') }}" class="sidebar-link {{ request()->routeIs('master.kondisi-tiang.*') ? 'active' : '' }}">
            <i class="bi bi-activity"></i> Kondisi Tiang
        </a>
        <a href="{{ route('master.operator-isp.index') }}" class="sidebar-link {{ request()->routeIs('master.operator-isp.*') ? 'active' : '' }}">
            <i class="bi bi-wifi"></i> Operator ISP
        </a>
        @endif
        @endauth
    </div>

    <!-- User info -->
    @auth
    <div style="padding: 1rem 1.25rem; border-top: 1px solid rgba(255,255,255,.1);">
        <div class="d-flex align-items-center gap-2">
            <div style="width:34px;height:34px;background:rgba(255,255,255,.15);border-radius:50%;display:grid;place-items:center;flex-shrink:0">
                <i class="bi bi-person text-white"></i>
            </div>
            <div style="overflow:hidden">
                <div style="font-size:.85rem;font-weight:600;color:#fff;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">{{ auth()->user()->name }}</div>
                <div style="font-size:.72rem;color:rgba(255,255,255,.5);">{{ ucfirst(auth()->user()->role) }}</div>
            </div>
        </div>
        <form method="POST" action="{{ route('logout') }}" class="mt-2">
            @csrf
            <button type="submit" class="btn btn-sm w-100" style="background:rgba(255,255,255,.08);color:rgba(255,255,255,.7);font-size:.8rem;">
                <i class="bi bi-box-arrow-left me-1"></i> Logout
            </button>
        </form>
    </div>
    @endauth
</nav>

<!-- ══ TOPBAR ══════════════════════════════════════════════════════ -->
<header id="topbar">
    <button class="btn btn-sm d-lg-none me-2" id="sidebar-toggle" style="color:var(--primary)">
        <i class="bi bi-list fs-5"></i>
    </button>
    <div class="flex-grow-1">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0" style="font-size:.82rem;">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                @yield('breadcrumb')
            </ol>
        </nav>
    </div>
    <div class="d-flex align-items-center gap-2">
        <span class="badge" style="background:var(--accent);font-size:.72rem;">District Lampung</span>
    </div>
</header>

<!-- ══ MAIN CONTENT ══════════════════════════════════════════════ -->
<main id="main-content">
    <!-- Flash Messages -->
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show d-flex align-items-center gap-2 mb-3 fade-in" role="alert">
        <i class="bi bi-check-circle-fill fs-5"></i>
        <div>{{ session('success') }}</div>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif
    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center gap-2 mb-3 fade-in" role="alert">
        <i class="bi bi-exclamation-triangle-fill fs-5"></i>
        <div>{{ session('error') }}</div>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif
    @if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show mb-3 fade-in">
        <i class="bi bi-exclamation-triangle-fill me-2"></i>
        <strong>Terdapat kesalahan:</strong>
        <ul class="mb-0 mt-1">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    @yield('content')
</main>

<!-- Bootstrap 5 JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<!-- DataTables -->
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>

<script>
// Sidebar toggle (mobile)
document.getElementById('sidebar-toggle')?.addEventListener('click', () => {
    document.getElementById('sidebar').classList.toggle('show');
});

// Auto-hide alerts after 5 seconds
setTimeout(() => {
    document.querySelectorAll('.alert:not(.alert-permanent)').forEach(el => {
        bootstrap.Alert.getOrCreateInstance(el)?.close();
    });
}, 5000);

// CSRF token for AJAX
$.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });
</script>

@stack('scripts')
</body>
</html>
