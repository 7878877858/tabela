<!DOCTYPE html>
<html lang="gu">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ \App\Models\Setting::get('farm_name', __('settings.default_farm_name')) }}</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Hind+Vadodara:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/css/design-system.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/sidebar.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/mobile.css') }}">

    @php
    $primary = \App\Models\Setting::get('primary_color', '#16a34a');
    $farmName = \App\Models\Setting::get('farm_name', __('settings.default_farm_name'));
    $currency = \App\Models\Setting::get('currency', '₹');
    // Darken primary by ~20% for hover
    if (!function_exists('adjustColor')) {
    function adjustColor($hex, $percent)
    {
    $hex = ltrim($hex, '#');
    $r = hexdec(substr($hex, 0, 2));
    $g = hexdec(substr($hex, 2, 2));
    $b = hexdec(substr($hex, 4, 2));

    $r = max(0, min(255, $r + $percent));
    $g = max(0, min(255, $g + $percent));
    $b = max(0, min(255, $b + $percent));

    return sprintf('#%02x%02x%02x', $r, $g, $b);
    }
    }
    $primaryDark = adjustColor($primary, -30);
    @endphp

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Hind Vadodara', sans-serif;
            background: #f4f6f8;
            color: #1a1a1a;
            min-height: 100vh;
        }

        .hide-scrollbar {
            -ms-overflow-style: none;
            /* IE and Edge */
            scrollbar-width: none;
            /* Firefox */
        }

        .hide-scrollbar::-webkit-scrollbar {
            display: none;
            /* Chrome, Safari */
        }

        /* Main */
        .main {
            min-height: 100vh;
        }

        .topbar {
            background: #fff;
            padding: 0 16px;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 1px solid #e5e7eb;
            position: sticky;
            top: 0;
            z-index: 50;
        }

        .topbar h2 {
            font-size: 18px;
            font-weight: 600;
            color: #1a1a1a;
        }

        .content {
            padding: 16px;
        }

        /* Cards */
        .card {
            background: #fff;
            border-radius: 12px;
            padding: 20px;
            border: 1px solid #e5e7eb;
        }

        .stat-card {
            background: #fff;
            border-radius: 12px;
            padding: 20px;
            border: 1px solid #e5e7eb;
        }

        .stat-card .label {
            font-size: 12px;
            color: #6b7280;
            font-weight: 500;
            margin-bottom: 6px;
        }

        .stat-card .value {
            font-size: 28px;
            font-weight: 700;
            color: var(--primary);
        }

        .stat-card .sub {
            font-size: 12px;
            color: #9ca3af;
            margin-top: 4px;
        }

        /* Grid */
        .grid-4 {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 16px;
        }

        .grid-3 {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 16px;
        }

        .grid-2 {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 16px;
        }

        /* Table */
        .table-wrap {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
        }

        th {
            background: #f9fafb;
            color: #374151;
            font-weight: 600;
            padding: 10px 14px;
            text-align: left;
            border-bottom: 2px solid #e5e7eb;
        }

        td {
            padding: 10px 14px;
            border-bottom: 1px solid #f3f4f6;
            color: #374151;
        }

        tr:hover td {
            background: #fafafa;
        }

        /* Buttons */
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 9px 18px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            border: none;
            text-decoration: none;
            transition: all .2s;
            font-family: inherit;
        }

        .btn-primary {
            background: var(--primary);
            color: #fff;
        }

        .btn-primary:hover {
            background: var(--primary-dark);
        }

        .btn-outline {
            background: #fff;
            color: var(--primary);
            border: 1.5px solid var(--primary);
        }

        .btn-outline:hover {
            background: var(--primary-light);
        }

        .btn-danger {
            background: #ef4444;
            color: #fff;
        }

        .btn-danger:hover {
            background: #dc2626;
        }

        .btn-sm {
            padding: 5px 12px;
            font-size: 12px;
        }

        .btn-ghost {
            background: transparent;
            color: #6b7280;
            border: 1px solid #e5e7eb;
        }

        .btn-ghost:hover {
            background: #f3f4f6;
        }

        /* Form */
        .form-group {
            margin-bottom: 16px;
        }

        .form-label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: #374151;
            margin-bottom: 6px;
        }

        .form-control {
            width: 100%;
            padding: 9px 12px;
            border: 1.5px solid #d1d5db;
            border-radius: 8px;
            font-size: 14px;
            font-family: inherit;
            transition: border .2s;
            outline: none;
        }

        .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px var(--primary-light);
        }

        select.form-control {
            cursor: pointer;
        }

        input[type="date"].form-control,
        input[type="date"] {
            cursor: pointer;
        }

        /* Badge */
        .badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
        }

        .badge-green {
            background: #dcfce7;
            color: #16a34a;
        }

        .badge-red {
            background: #fee2e2;
            color: #dc2626;
        }

        .badge-yellow {
            background: #fef9c3;
            color: #ca8a04;
        }

        .badge-blue {
            background: #dbeafe;
            color: #2563eb;
        }

        .badge-gray {
            background: #f3f4f6;
            color: #6b7280;
        }

        /* Alert */
        .alert {
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 16px;
            font-size: 14px;
        }

        .alert-success {
            background: #dcfce7;
            color: #15803d;
            border: 1px solid #86efac;
        }

        .alert-error {
            background: #fee2e2;
            color: #dc2626;
            border: 1px solid #fca5a5;
        }

        /* Page header */
        .page-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 20px;
        }

        .page-header h2 {
            font-size: 22px;
            font-weight: 700;
        }

        /* Mobile */
        .hamburger {
            display: none;
            background: none;
            border: none;
            cursor: pointer;
            padding: 8px;
        }

        @media(max-width:768px) {
            .grid-4,
            .grid-3 {
                grid-template-columns: repeat(2, 1fr);
            }

            .hamburger {
                display: block;
            }
        }

        /* Month selector */
        .month-selector {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        /* Summary row */
        .summary-row {
            background: var(--primary-light);
            border-radius: 8px;
            padding: 12px 16px;
            display: flex;
            gap: 16px;
            flex-wrap: wrap;
            margin-bottom: 16px;
            font-size: 14px;
        }

        .summary-row span {
            color: #374151;
        }

        .summary-row strong {
            color: var(--primary);
        }
    </style>

    @stack('styles')
</head>

<body style="--primary: {{ $primary }};--primary-dark: {{ $primaryDark }};--primary-light: {{ $primary }}22;--ds-primary: {{ $primary }};">

    <div class="sidebar-backdrop" id="sidebarBackdrop"></div>

    @include('layouts.partials.sidebar')

    <div class="main">
        <header class="topbar">
            <div style="display:flex; align-items:center; gap:12px;">
                <button class="hamburger" type="button" onclick="toggleSidebar()" aria-label="Menu">
                    <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path d="M4 6h16M4 12h16M4 18h16" stroke-width="2" stroke-linecap="round" />
                    </svg>
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

    <script src="{{ asset('assets/js/sidebar.js') }}"></script>

    {{-- Global date picker: click/focus/tap anywhere on field opens calendar --}}
    <script>
    (function () {
        'use strict';

        var SELECTOR = 'input[type="date"]';

        function getDateInput(el) {
            if (!el || !el.closest) return null;
            var input = el.matches && el.matches(SELECTOR) ? el : el.closest(SELECTOR);
            if (!input || input.disabled || input.readOnly) return null;
            return input;
        }

        function supportsShowPicker(input) {
            return typeof input.showPicker === 'function';
        }

        function openDatePicker(input) {
            if (supportsShowPicker(input)) {
                try {
                    input.showPicker();
                    return;
                } catch (err) {
                    // InvalidStateError / not a user gesture — fall back to focus
                }
            }
            if (document.activeElement !== input) {
                input.focus();
            }
        }

        var openedFromPointer = false;

        function onPointerActivate(e) {
            var input = getDateInput(e.target);
            if (!input) return;

            openedFromPointer = true;
            window.setTimeout(function () { openedFromPointer = false; }, 0);

            if (supportsShowPicker(input)) {
                e.preventDefault();
                input.focus({ preventScroll: true });
                openDatePicker(input);
            }
        }

        if (window.PointerEvent) {
            document.addEventListener('pointerdown', onPointerActivate, true);
        } else {
            document.addEventListener('mousedown', onPointerActivate, true);
        }

        document.addEventListener('touchstart', function (e) {
            var input = getDateInput(e.target);
            if (!input) return;
            if (!supportsShowPicker(input)) return;
            openedFromPointer = true;
            window.setTimeout(function () { openedFromPointer = false; }, 0);
            e.preventDefault();
            input.focus({ preventScroll: true });
            openDatePicker(input);
        }, { capture: true, passive: false });

        document.addEventListener('focusin', function (e) {
            var input = getDateInput(e.target);
            if (!input || openedFromPointer) return;
            openDatePicker(input);
        });

        document.addEventListener('click', function (e) {
            var input = getDateInput(e.target);
            if (!input) return;
            if (supportsShowPicker(input)) return;
            openDatePicker(input);
        }, true);
    })();
    </script>

    @stack('scripts')
</body>

</html>