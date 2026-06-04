<!DOCTYPE html>
<html lang="gu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
   <title>{{ \App\Models\Setting::get('farm_name', __('settings.default_farm_name')) }}</title>
   

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Hind+Vadodara:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    @php
        $primary   = \App\Models\Setting::get('primary_color','#16a34a');
        $farmName = \App\Models\Setting::get('farm_name', __('settings.default_farm_name'));
        $currency  = \App\Models\Setting::get('currency','₹');
        // Darken primary by ~20% for hover
       if (!function_exists('adjustColor')) {
    function adjustColor($hex, $percent) {
        $hex = ltrim($hex,'#');
        $r = hexdec(substr($hex,0,2));
        $g = hexdec(substr($hex,2,2));
        $b = hexdec(substr($hex,4,2));

        $r = max(0,min(255,$r + $percent));
        $g = max(0,min(255,$g + $percent));
        $b = max(0,min(255,$b + $percent));

        return sprintf('#%02x%02x%02x',$r,$g,$b);
    }
}
        $primaryDark = adjustColor($primary, -30);
    @endphp

    <style>
        :root {
            --primary: {{ $primary }};
            --primary-dark: {{ $primaryDark }};
            --primary-light: {{ $primary }}22;
        }
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family:'Hind Vadodara',sans-serif; background:#f4f6f8; color:#1a1a1a; min-height:100vh; }

        /* Sidebar */
        .sidebar { position:fixed; top:0; left:0; width:240px; height:100vh; background:var(--primary); color:#fff; display:flex; flex-direction:column; z-index:100; transition:transform .3s; }
        .sidebar-header { padding:20px 16px; border-bottom:1px solid rgba(255,255,255,.2); }
        .sidebar-header h1 { font-size:18px; font-weight:700; }
        .sidebar-header p { font-size:12px; opacity:.7; margin-top:2px; }
        .sidebar nav { flex:1; overflow-y:auto; padding:12px 0; }
        .nav-item { display:flex; align-items:center; gap:10px; padding:11px 20px; color:rgba(255,255,255,.85); text-decoration:none; font-size:14px; font-weight:500; transition:background .2s; border-left:3px solid transparent; }
        .nav-item:hover, .nav-item.active { background:rgba(255,255,255,.15); color:#fff; border-left-color:#fff; }
        .nav-item svg { width:18px; height:18px; flex-shrink:0; }
        .nav-section { padding:8px 20px 4px; font-size:10px; text-transform:uppercase; letter-spacing:.8px; opacity:.5; margin-top:8px; }
        .sidebar-footer { padding:16px; border-top:1px solid rgba(255,255,255,.2); }

        /* Main */
        .main { margin-left:240px; min-height:100vh; }
        .topbar { background:#fff; padding:0 16px; height:60px; display:flex; align-items:center; justify-content:space-between; border-bottom:1px solid #e5e7eb; position:sticky; top:0; z-index:50; }
        .topbar h2 { font-size:18px; font-weight:600; color:#1a1a1a; }
        .content { padding:16px; }

        /* Cards */
        .card { background:#fff; border-radius:12px; padding:20px; border:1px solid #e5e7eb; }
        .stat-card { background:#fff; border-radius:12px; padding:20px; border:1px solid #e5e7eb; }
        .stat-card .label { font-size:12px; color:#6b7280; font-weight:500; margin-bottom:6px; }
        .stat-card .value { font-size:28px; font-weight:700; color:var(--primary); }
        .stat-card .sub { font-size:12px; color:#9ca3af; margin-top:4px; }

        /* Grid */
        .grid-4 { display:grid; grid-template-columns:repeat(4,1fr); gap:16px; }
        .grid-3 { display:grid; grid-template-columns:repeat(3,1fr); gap:16px; }
        .grid-2 { display:grid; grid-template-columns:repeat(2,1fr); gap:16px; }

        /* Table */
        .table-wrap { overflow-x:auto; }
        table { width:100%; border-collapse:collapse; font-size:14px; }
        th { background:#f9fafb; color:#374151; font-weight:600; padding:10px 14px; text-align:left; border-bottom:2px solid #e5e7eb; }
        td { padding:10px 14px; border-bottom:1px solid #f3f4f6; color:#374151; }
        tr:hover td { background:#fafafa; }

        /* Buttons */
        .btn { display:inline-flex; align-items:center; gap:6px; padding:9px 18px; border-radius:8px; font-size:14px; font-weight:600; cursor:pointer; border:none; text-decoration:none; transition:all .2s; font-family:inherit; }
        .btn-primary { background:var(--primary); color:#fff; }
        .btn-primary:hover { background:var(--primary-dark); }
        .btn-outline { background:#fff; color:var(--primary); border:1.5px solid var(--primary); }
        .btn-outline:hover { background:var(--primary-light); }
        .btn-danger { background:#ef4444; color:#fff; }
        .btn-danger:hover { background:#dc2626; }
        .btn-sm { padding:5px 12px; font-size:12px; }
        .btn-ghost { background:transparent; color:#6b7280; border:1px solid #e5e7eb; }
        .btn-ghost:hover { background:#f3f4f6; }

        /* Form */
        .form-group { margin-bottom:16px; }
        .form-label { display:block; font-size:13px; font-weight:600; color:#374151; margin-bottom:6px; }
        .form-control { width:100%; padding:9px 12px; border:1.5px solid #d1d5db; border-radius:8px; font-size:14px; font-family:inherit; transition:border .2s; outline:none; }
        .form-control:focus { border-color:var(--primary); box-shadow:0 0 0 3px var(--primary-light); }
        select.form-control { cursor:pointer; }

        /* Badge */
        .badge { display:inline-block; padding:3px 10px; border-radius:20px; font-size:11px; font-weight:600; }
        .badge-green  { background:#dcfce7; color:#16a34a; }
        .badge-red    { background:#fee2e2; color:#dc2626; }
        .badge-yellow { background:#fef9c3; color:#ca8a04; }
        .badge-blue   { background:#dbeafe; color:#2563eb; }
        .badge-gray   { background:#f3f4f6; color:#6b7280; }

        /* Alert */
        .alert { padding:12px 16px; border-radius:8px; margin-bottom:16px; font-size:14px; }
        .alert-success { background:#dcfce7; color:#15803d; border:1px solid #86efac; }
        .alert-error   { background:#fee2e2; color:#dc2626; border:1px solid #fca5a5; }

        /* Page header */
        .page-header { display:flex; align-items:center; justify-content:space-between; margin-bottom:20px; }
        .page-header h2 { font-size:22px; font-weight:700; }

        /* Mobile */
        .hamburger { display:none; background:none; border:none; cursor:pointer; padding:8px; }
        @media(max-width:768px) {
            .sidebar { transform:translateX(-100%); }
            .sidebar.open { transform:translateX(0); }
            .main { margin-left:0; }
            .grid-4, .grid-3 { grid-template-columns:repeat(2,1fr); }
            .hamburger { display:block; }
        }

        /* Month selector */
        .month-selector { display:flex; align-items:center; gap:8px; }

        /* Summary row */
        .summary-row { background:var(--primary-light); border-radius:8px; padding:12px 16px; display:flex; gap:16px; flex-wrap:wrap; margin-bottom:16px; font-size:14px; }
        .summary-row span { color:#374151; }
        .summary-row strong { color:var(--primary); }
    </style>

    @stack('styles')
</head>
<body>

<aside class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <h1>🐃 {{ $farmName }}</h1>
        <p>{{ __('common.management') }}</p>
    </div>
    <nav>
        <a href="{{ route('dashboard') }}" class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
             📊{{ __('common.dashboard') }}
        </a>

        <div class="nav-section">{{ __('common.buffalo') }}</div>
        <a href="{{ route('buffalo.index') }}" class="nav-item {{ request()->routeIs('buffalo.*') ? 'active' : '' }}">
            🐃{{ __('common.all_buffaloes') }}
        </a>
        <a href="{{ route('buffalo.create') }}" class="nav-item {{ request()->routeIs('buffalo.create') ? 'active' : '' }}">
            🐄{{ __('common.add_buffalo') }}
        </a>

        <div class="nav-section">{{ __('common.milk') }}</div>
        <a href="{{ route('milk.index') }}" class="nav-item {{ request()->routeIs('milk.index') ? 'active' : '' }}">
            🥛{{ __('common.milk_entry') }}
        </a>
        <a href="{{ route('milk.history') }}" class="nav-item {{ request()->routeIs('milk.history') ? 'active' : '' }}">
            🕒{{ __('common.milk_history') }}
        </a>
        <a href="{{ route('sale.index') }}" class="nav-item {{ request()->routeIs('sale.*') ? 'active' : '' }}">
            🥛{{ __('common.milk_sales') }}
        </a>

        <div class="nav-section">{{ __('common.expense') }}</div>
        <a href="{{ route('kharch.index') }}" class="nav-item {{ request()->routeIs('kharch.*') ? 'active' : '' }}">
            📉{{ __('common.expense') }}
        </a>

        <div class="nav-section">{{ __('common.reports') }}</div>
        <a href="{{ route('reports.monthly') }}" class="nav-item {{ request()->routeIs('reports.monthly') ? 'active' : '' }}">
            📊{{ __('common.monthly_report') }}
        </a>
        <a href="{{ route('reports.yearly') }}" class="nav-item {{ request()->routeIs('reports.yearly') ? 'active' : '' }}">
            📊{{ __('common.yearly_report') }}
        </a>

        <div class="nav-section">{{ __('common.staff') }}</div>
        <a href="{{ route('employees.index') }}" class="nav-item {{ request()->routeIs('employees.*') ? 'active' : '' }}">
            👥{{ __('common.employees') }}
        </a>

            <div class="nav-section">{{ __('common.assets') }}</div>

            <a href="{{ route('assets.index') }}"
            class="nav-item {{ request()->routeIs('assets.*') ? 'active' : '' }}">
                🚜 {{ __('common.assets') }}
            </a>

            <div class="nav-section">{{ __('common.tasks') }}</div>

            <a href="{{ route('tasks.index') }}"
            class="nav-item {{ request()->routeIs('tasks.*') ? 'active' : '' }}">
                📋 {{ __('common.tasks') }}
            </a>
        <div class="nav-section">{{ __('common.system') }}</div>
        <a href="{{ route('settings.index') }}" class="nav-item {{ request()->routeIs('settings.*') ? 'active' : '' }}">
            🛠️{{ __('common.settings') }}
        </a>
    </nav>
    <div class="sidebar-footer">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="nav-item" style="width:100%; background:none; border:none; cursor:pointer;">
                🚪{{ __('common.logout') }}
            </button>
        </form>
    </div>
</aside>

<div class="main">
    <header class="topbar">
        <div style="display:flex; align-items:center; gap:12px;">
            <button class="hamburger" onclick="document.getElementById('sidebar').classList.toggle('open')">
                <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M4 6h16M4 12h16M4 18h16" stroke-width="2" stroke-linecap="round"/></svg>
            </button>
            <h2>@yield('title', __('common.dashboard'))</h2>
        </div>
        <div style="font-size:13px; color:#6b7280;">
            {{ now()->format('d/m/Y') }} — {{ auth()->user()->name }}
        </div>
    </header>

    <main class="content">
        @if(session('success'))
            <div class="alert alert-success">✅ {{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-error">❌ {{ session('error') }}</div>
        @endif
        @if($errors->any())
            <div class="alert alert-error">
                @foreach($errors->all() as $e)<div>• {{ $e }}</div>@endforeach
            </div>
        @endif

        @yield('content')
    </main>
</div>

@stack('scripts')
</body>
</html>