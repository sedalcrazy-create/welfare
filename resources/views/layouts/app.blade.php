<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'سامانه رفاهی') - بانک ملی ایران</title>
    <link href="{{ asset('assets/css/vazirmatn.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/bootstrap-icons.min.css') }}" rel="stylesheet">
    <style>
        :root {
            /* Vibrant Sunset Theme - Colorful & Artistic */
            --primary: #ff6b6b;          /* Coral Red */
            --primary-light: #ff8787;    /* Light Coral */
            --primary-dark: #ee5253;     /* Deep Coral */
            --secondary: #00d2d3;        /* Turquoise */
            --secondary-light: #48dbfb;  /* Light Cyan */
            --secondary-dark: #01a3a4;   /* Deep Teal */
            --accent: #feca57;           /* Golden Yellow */
            --accent-light: #fed971;     /* Light Gold */
            --accent-dark: #ff9f43;      /* Orange */
            --purple: #a55eea;           /* Vibrant Purple */
            --purple-light: #c56cf0;     /* Light Purple */
            --sidebar-dark: #2d3436;     /* Charcoal */
            --sidebar-gradient: linear-gradient(180deg, #2d3436 0%, #1e272e 100%);
            --success: #26de81;          /* Bright Green */
            --warning: #fed330;          /* Bright Yellow */
            --danger: #fc5c65;           /* Bright Red */
            --info: #45aaf2;             /* Sky Blue */
            --text-dark: #2d3436;
            --text-muted: #636e72;
            --text-light: #b2bec3;
            --bg-light: #f9f9f9;
            --bg-white: #ffffff;
            --border-color: #dfe6e9;
            --sidebar-width: 280px;
            --sidebar-collapsed: 80px;
            --header-height: 70px;
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);

            /* Graph Colors */
            --chart-1: #ff6b6b;
            --chart-2: #00d2d3;
            --chart-3: #feca57;
            --chart-4: #a55eea;
            --chart-5: #26de81;
            --chart-6: #ff9f43;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Vazirmatn', 'Tahoma', sans-serif;
            background: var(--bg-light);
            min-height: 100vh;
            color: var(--text-dark);
            direction: rtl;
            line-height: 1.6;
        }

        /* ============ SIDEBAR ============ */
        .sidebar {
            position: fixed;
            right: 0;
            top: 0;
            width: var(--sidebar-width);
            height: 100vh;
            background: var(--sidebar-gradient);
            z-index: 1000;
            display: flex;
            flex-direction: column;
            transition: var(--transition);
            box-shadow: -4px 0 30px rgba(0, 0, 0, 0.15);
        }

        .sidebar::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--primary) 0%, var(--accent) 50%, var(--secondary) 100%);
        }

        /* Logo Section */
        .sidebar-brand {
            padding: 24px;
            display: flex;
            align-items: center;
            gap: 16px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .brand-logo {
            width: 52px;
            height: 52px;
            background: linear-gradient(135deg, var(--primary) 0%, var(--accent) 100%);
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            box-shadow: 0 4px 15px rgba(255, 107, 107, 0.4);
            position: relative;
            overflow: hidden;
        }

        .brand-logo::after {
            content: '';
            position: absolute;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, transparent 0%, rgba(255,255,255,0.2) 100%);
        }

        .brand-logo img {
            width: 36px;
            height: 36px;
            object-fit: contain;
        }

        .brand-text {
            flex: 1;
            min-width: 0;
        }

        .brand-title {
            color: #fff;
            font-size: 1.1rem;
            font-weight: 700;
            margin-bottom: 2px;
        }

        .brand-subtitle {
            color: rgba(255, 255, 255, 0.6);
            font-size: 0.8rem;
        }

        /* Navigation */
        .sidebar-nav {
            flex: 1;
            padding: 16px 12px;
            overflow-y: auto;
        }

        .nav-section {
            margin-bottom: 24px;
        }

        .nav-section-title {
            color: rgba(255, 255, 255, 0.4);
            font-size: 0.7rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 0 16px;
            margin-bottom: 8px;
        }

        .nav-item {
            margin-bottom: 4px;
        }

        .nav-link {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 12px 16px;
            color: rgba(255, 255, 255, 0.7);
            text-decoration: none;
            border-radius: 10px;
            transition: var(--transition);
            position: relative;
            font-weight: 500;
            font-size: 0.92rem;
        }

        .nav-link:hover {
            color: #fff;
            background: rgba(255, 255, 255, 0.08);
        }

        .nav-link.active {
            color: #fff;
            background: linear-gradient(135deg, rgba(255, 107, 107, 0.2) 0%, rgba(254, 202, 87, 0.15) 100%);
        }

        .nav-link.active::before {
            content: '';
            position: absolute;
            right: 0;
            top: 50%;
            transform: translateY(-50%);
            width: 4px;
            height: 28px;
            background: linear-gradient(180deg, var(--primary) 0%, var(--accent) 100%);
            border-radius: 0 4px 4px 0;
        }

        .nav-link i {
            font-size: 1.2rem;
            width: 24px;
            text-align: center;
            opacity: 0.9;
        }

        .nav-link.active i {
            color: var(--primary);
            filter: drop-shadow(0 0 8px rgba(255, 107, 107, 0.5));
        }

        .nav-badge {
            margin-right: auto;
            background: rgba(255, 255, 255, 0.15);
            padding: 3px 10px;
            border-radius: 12px;
            font-size: 0.65rem;
            color: rgba(255, 255, 255, 0.7);
            font-weight: 500;
        }

        .nav-link.disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        /* User Section */
        .sidebar-footer {
            padding: 16px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            background: rgba(0, 0, 0, 0.1);
        }

        .user-card {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 12px;
            margin-bottom: 12px;
        }

        .user-avatar {
            width: 42px;
            height: 42px;
            border-radius: 10px;
            background: linear-gradient(135deg, var(--secondary) 0%, var(--secondary-dark) 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-weight: 700;
            font-size: 1rem;
            box-shadow: 0 4px 12px rgba(0, 210, 211, 0.3);
        }

        .user-info {
            flex: 1;
            min-width: 0;
        }

        .user-name {
            color: #fff;
            font-weight: 600;
            font-size: 0.9rem;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .user-role {
            color: rgba(255, 255, 255, 0.5);
            font-size: 0.75rem;
        }

        .btn-logout {
            width: 100%;
            padding: 10px 16px;
            background: rgba(239, 68, 68, 0.15);
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: #fca5a5;
            border-radius: 8px;
            cursor: pointer;
            font-family: inherit;
            font-size: 0.85rem;
            font-weight: 500;
            transition: var(--transition);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-logout:hover {
            background: rgba(239, 68, 68, 0.25);
            color: #fecaca;
        }

        /* ============ MAIN CONTENT ============ */
        .main-wrapper {
            margin-right: var(--sidebar-width);
            min-height: 100vh;
            transition: var(--transition);
        }

        /* Top Header */
        .top-header {
            height: var(--header-height);
            background: var(--bg-white);
            border-bottom: 1px solid var(--border-color);
            padding: 0 32px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .header-right {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .breadcrumb {
            display: flex;
            align-items: center;
            gap: 8px;
            color: var(--text-muted);
            font-size: 0.9rem;
        }

        .breadcrumb a {
            color: var(--text-muted);
            text-decoration: none;
            transition: var(--transition);
        }

        .breadcrumb a:hover {
            color: var(--primary);
        }

        .breadcrumb-separator {
            color: var(--text-light);
        }

        .breadcrumb-current {
            color: var(--text-dark);
            font-weight: 600;
        }

        .header-left {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .header-date {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 8px 16px;
            background: var(--bg-light);
            border-radius: 8px;
            color: var(--text-muted);
            font-size: 0.85rem;
        }

        .header-date i {
            color: var(--primary);
        }

        /* Main Content */
        .main-content {
            padding: 32px;
        }

        /* ============ PAGE HEADER ============ */
        .page-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 32px;
            flex-wrap: wrap;
            gap: 16px;
        }

        .page-header h1 {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--text-dark);
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .page-header h1 i {
            color: var(--primary);
        }

        /* ============ CARDS ============ */
        .card {
            background: var(--bg-white);
            border-radius: 16px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05), 0 1px 2px rgba(0, 0, 0, 0.1);
            margin-bottom: 24px;
            border: 1px solid var(--border-color);
            transition: var(--transition);
        }

        .card:hover {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }

        .card-header {
            padding: 20px 24px;
            border-bottom: 1px solid var(--border-color);
            font-weight: 600;
            color: var(--text-dark);
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 1rem;
        }

        .card-header i {
            color: var(--primary);
        }

        .card-body {
            padding: 24px;
        }

        /* ============ STAT CARDS ============ */
        .stat-card {
            background: var(--bg-white);
            border-radius: 16px;
            padding: 24px;
            border: 1px solid var(--border-color);
            position: relative;
            overflow: hidden;
            transition: var(--transition);
        }

        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
        }

        .stat-card .stat-icon {
            position: absolute;
            left: 20px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 3rem;
            opacity: 0.1;
        }

        .stat-card .stat-value {
            font-size: 2rem;
            font-weight: 800;
            margin-bottom: 4px;
        }

        .stat-card .stat-label {
            font-size: 0.9rem;
            color: var(--text-muted);
        }

        .stat-card.primary {
            border-right: 4px solid var(--primary);
            background: linear-gradient(135deg, #fff 0%, rgba(255, 107, 107, 0.05) 100%);
        }
        .stat-card.primary .stat-icon, .stat-card.primary .stat-value { color: var(--primary); }

        .stat-card.success {
            border-right: 4px solid var(--secondary);
            background: linear-gradient(135deg, #fff 0%, rgba(0, 210, 211, 0.05) 100%);
        }
        .stat-card.success .stat-icon, .stat-card.success .stat-value { color: var(--secondary); }

        .stat-card.warning {
            border-right: 4px solid var(--accent);
            background: linear-gradient(135deg, #fff 0%, rgba(254, 202, 87, 0.08) 100%);
        }
        .stat-card.warning .stat-icon, .stat-card.warning .stat-value { color: var(--accent-dark); }

        .stat-card.danger {
            border-right: 4px solid var(--purple);
            background: linear-gradient(135deg, #fff 0%, rgba(165, 94, 234, 0.05) 100%);
        }
        .stat-card.danger .stat-icon, .stat-card.danger .stat-value { color: var(--purple); }

        /* ============ ALERTS ============ */
        .alert {
            padding: 16px 20px;
            border-radius: 12px;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 0.9rem;
        }

        .alert-success {
            background: #ecfdf5;
            border: 1px solid #a7f3d0;
            color: #065f46;
        }

        .alert-danger {
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #991b1b;
        }

        .alert .btn-close {
            margin-right: auto;
            background: transparent;
            border: none;
            font-size: 1.2rem;
            cursor: pointer;
            opacity: 0.5;
            transition: var(--transition);
        }

        .alert .btn-close:hover {
            opacity: 1;
        }

        /* ============ BUTTONS ============ */
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            font-size: 0.9rem;
            font-family: inherit;
            font-weight: 600;
            transition: var(--transition);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: #fff;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, var(--primary-light) 0%, var(--primary) 100%);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(255, 107, 107, 0.4);
        }

        .btn-secondary {
            background: var(--bg-light);
            color: var(--text-dark);
            border: 1px solid var(--border-color);
        }

        .btn-secondary:hover {
            background: #e5e7eb;
        }

        .btn-success {
            background: var(--success);
            color: #fff;
        }

        .btn-warning {
            background: var(--warning);
            color: #fff;
        }

        .btn-danger {
            background: var(--danger);
            color: #fff;
        }

        .btn-info {
            background: var(--info);
            color: #fff;
        }

        .btn-sm {
            padding: 6px 12px;
            font-size: 0.8rem;
            border-radius: 8px;
        }

        /* ============ TABLES ============ */
        .table-container {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        table th,
        table td {
            padding: 14px 16px;
            text-align: right;
            border-bottom: 1px solid var(--border-color);
        }

        table th {
            background: var(--bg-light);
            font-weight: 600;
            color: var(--text-dark);
            font-size: 0.85rem;
        }

        table tbody tr {
            transition: var(--transition);
        }

        table tbody tr:hover {
            background: var(--bg-light);
        }

        /* ============ BADGES ============ */
        .badge {
            display: inline-flex;
            align-items: center;
            padding: 5px 12px;
            border-radius: 8px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .badge-success { background: #ecfdf5; color: #065f46; }
        .badge-warning { background: #fffbeb; color: #92400e; }
        .badge-danger { background: #fef2f2; color: #991b1b; }
        .badge-info { background: #eff6ff; color: #1e40af; }
        .badge-secondary { background: #f3f4f6; color: #4b5563; }

        /* ============ FORMS ============ */
        .form-control {
            width: 100%;
            padding: 12px 16px;
            border: 1px solid var(--border-color);
            border-radius: 10px;
            font-size: 0.9rem;
            font-family: inherit;
            transition: var(--transition);
            background: var(--bg-white);
        }

        .form-control:focus {
            outline: none;
            border-color: var(--secondary);
            box-shadow: 0 0 0 3px rgba(0, 210, 211, 0.15);
        }

        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--text-dark);
            font-size: 0.9rem;
        }

        /* ============ UTILITIES ============ */
        .text-center { text-align: center; }
        .text-muted { color: var(--text-muted); }
        .mb-4 { margin-bottom: 1.5rem; }
        .mt-3 { margin-top: 1rem; }
        .mt-4 { margin-top: 1.5rem; }
        .py-4 { padding-top: 1.5rem; padding-bottom: 1.5rem; }
        .py-5 { padding-top: 2rem; padding-bottom: 2rem; }
        .d-flex { display: flex; }
        .justify-content-between { justify-content: space-between; }
        .justify-content-center { justify-content: center; }
        .align-items-center { align-items: center; }
        .align-items-end { align-items: flex-end; }
        .gap-2 { gap: 0.5rem; }
        .gap-3 { gap: 1rem; }
        .w-100 { width: 100%; }

        .row {
            display: flex;
            flex-wrap: wrap;
            margin: -12px;
        }

        .col-md-2 { width: 16.666%; padding: 12px; }
        .col-md-3 { width: 25%; padding: 12px; }
        .col-md-4 { width: 33.333%; padding: 12px; }
        .col-md-6 { width: 50%; padding: 12px; }
        .col-md-8 { width: 66.666%; padding: 12px; }
        .col-12 { width: 100%; padding: 12px; }

        /* ============ RESPONSIVE ============ */
        @media (max-width: 1200px) {
            .col-md-3 { width: 50%; }
        }

        @media (max-width: 992px) {
            .sidebar {
                width: var(--sidebar-collapsed);
            }

            .brand-text,
            .nav-section-title,
            .nav-link span,
            .nav-badge,
            .user-info {
                display: none;
            }

            .sidebar-brand {
                padding: 16px;
                justify-content: center;
            }

            .brand-logo {
                width: 48px;
                height: 48px;
            }

            .nav-link {
                justify-content: center;
                padding: 14px;
            }

            .user-card {
                justify-content: center;
                padding: 8px;
            }

            .btn-logout span {
                display: none;
            }

            .main-wrapper {
                margin-right: var(--sidebar-collapsed);
            }
        }

        @media (max-width: 768px) {
            .col-md-2, .col-md-3, .col-md-4, .col-md-6, .col-md-8 {
                width: 100%;
            }

            .main-content {
                padding: 20px 16px;
            }

            .top-header {
                padding: 0 16px;
            }

            .page-header {
                flex-direction: column;
                align-items: flex-start;
            }
        }
    </style>
    @stack('styles')
</head>
<body>
    <!-- Sidebar -->
    <nav class="sidebar">
        <div class="sidebar-brand">
            <div class="brand-logo">
                <img src="{{ asset('logo.png') }}" alt="بانک ملی">
            </div>
            <div class="brand-text">
                <div class="brand-title">سامانه رفاهی</div>
                <div class="brand-subtitle">بانک ملی ایران</div>
            </div>
        </div>

        <div class="sidebar-nav">
            <div class="nav-section">
                <div class="nav-section-title">منوی اصلی</div>
                <div class="nav-item">
                    <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                        <i class="bi bi-grid-1x2"></i>
                        <span>داشبورد</span>
                    </a>
                </div>
            </div>

            <div class="nav-section">
                <div class="nav-section-title">فاز 1 - معرفی‌نامه</div>
                <div class="nav-item">
                    <a class="nav-link {{ request()->routeIs('personnel-requests.*') ? 'active' : '' }}" href="{{ route('personnel-requests.index') }}">
                        <i class="bi bi-person-check"></i>
                        <span>درخواست‌ها</span>
                        @php
                            $pendingCount = \App\Models\Personnel::where('status', 'pending')->count();
                        @endphp
                        @if($pendingCount > 0)
                            <span class="nav-badge bg-warning">{{ $pendingCount }}</span>
                        @endif
                    </a>
                </div>
                @role('super_admin|admin')
                <div class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.personnel-approvals.*') ? 'active' : '' }}" href="{{ route('admin.personnel-approvals.pending') }}">
                        <i class="bi bi-clipboard-check"></i>
                        <span>تأیید درخواست‌ها</span>
                        @if($pendingCount > 0)
                            <span class="nav-badge" style="background: var(--danger);">{{ $pendingCount }}</span>
                        @endif
                    </a>
                </div>
                @endrole
                <div class="nav-item">
                    <a class="nav-link {{ request()->routeIs('introduction-letters.*') ? 'active' : '' }}" href="{{ route('introduction-letters.index') }}">
                        <i class="bi bi-file-earmark-text"></i>
                        <span>معرفی‌نامه‌ها</span>
                    </a>
                </div>
                @role('super_admin|admin')
                <div class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}" href="{{ route('admin.users.index') }}">
                        <i class="bi bi-person-gear"></i>
                        <span>مدیریت کاربران</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.user-center-quota.*') ? 'active' : '' }}" href="{{ route('admin.user-center-quota.index') }}">
                        <i class="bi bi-ticket-perforated"></i>
                        <span>سهمیه (به تفکیک مرکز)</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.registration-control.*') ? 'active' : '' }}" href="{{ route('admin.registration-control.index') }}">
                        <i class="bi bi-shield-lock"></i>
                        <span>کنترل ثبت نام</span>
                    </a>
                </div>
                @endrole
            </div>

            <div class="nav-section">
                <div class="nav-section-title">مدیریت مراکز</div>
                <div class="nav-item">
                    <a class="nav-link {{ request()->routeIs('centers.*') ? 'active' : '' }}" href="{{ route('centers.index') }}">
                        <i class="bi bi-building"></i>
                        <span>مراکز رفاهی</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a class="nav-link {{ request()->routeIs('units.*') ? 'active' : '' }}" href="{{ route('units.index') }}">
                        <i class="bi bi-door-open"></i>
                        <span>واحدها</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a class="nav-link {{ request()->routeIs('periods.*') ? 'active' : '' }}" href="{{ route('periods.index') }}">
                        <i class="bi bi-calendar-range"></i>
                        <span>دوره‌ها</span>
                    </a>
                </div>
            </div>

            <div class="nav-section">
                <div class="nav-section-title">عملیات (فاز 2)</div>
                <div class="nav-item">
                    <a class="nav-link disabled" href="#">
                        <i class="bi bi-shuffle"></i>
                        <span>قرعه‌کشی</span>
                        <span class="nav-badge">فاز 2</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a class="nav-link disabled" href="#">
                        <i class="bi bi-calendar-check"></i>
                        <span>رزروها</span>
                        <span class="nav-badge">فاز 2</span>
                    </a>
                </div>
            </div>

            <div class="nav-section">
                <div class="nav-section-title">اطلاعات پایه</div>
                <div class="nav-item">
                    <a class="nav-link {{ request()->routeIs('personnel.*') ? 'active' : '' }}" href="{{ route('personnel.index') }}">
                        <i class="bi bi-people"></i>
                        <span>پرسنل</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a class="nav-link {{ request()->routeIs('provinces.*') ? 'active' : '' }}" href="{{ route('provinces.index') }}">
                        <i class="bi bi-map"></i>
                        <span>استان‌ها</span>
                    </a>
                </div>
            </div>

            <div class="nav-section">
                <div class="nav-section-title">گزارشات</div>
                <div class="nav-item">
                    <a class="nav-link disabled" href="#">
                        <i class="bi bi-bar-chart-line"></i>
                        <span>گزارش‌ها</span>
                        <span class="nav-badge">فاز 2</span>
                    </a>
                </div>
            </div>
        </div>

        <div class="sidebar-footer">
            <div class="user-card">
                <div class="user-avatar">
                    {{ mb_substr(auth()->user()->name ?? 'ک', 0, 1) }}
                </div>
                <div class="user-info">
                    <div class="user-name">{{ auth()->user()->name ?? 'کاربر' }}</div>
                    <div class="user-role">مدیر سیستم</div>
                </div>
            </div>
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="btn-logout">
                    <i class="bi bi-box-arrow-right"></i>
                    <span>خروج از حساب</span>
                </button>
            </form>
        </div>
    </nav>

    <!-- Main Wrapper -->
    <div class="main-wrapper">
        <!-- Top Header -->
        <header class="top-header">
            <div class="header-right">
                <div class="breadcrumb">
                    <a href="{{ route('dashboard') }}">
                        <i class="bi bi-house"></i>
                    </a>
                    <span class="breadcrumb-separator">/</span>
                    <span class="breadcrumb-current">@yield('title', 'داشبورد')</span>
                </div>
            </div>
            <div class="header-left">
                <div class="header-date">
                    <i class="bi bi-calendar3"></i>
                    <span>{{ jdate(now())->format('l j F Y') }}</span>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="main-content">
            @if(session('success'))
                <div class="alert alert-success">
                    <i class="bi bi-check-circle-fill"></i>
                    {{ session('success') }}
                    <button type="button" class="btn-close" onclick="this.parentElement.remove()">&times;</button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-circle-fill"></i>
                    {{ session('error') }}
                    <button type="button" class="btn-close" onclick="this.parentElement.remove()">&times;</button>
                </div>
            @endif

            @yield('content')
        </main>
    </div>

    <!-- jQuery & Persian Datepicker -->
    <script src="{{ asset('assets/js/jquery.min.js') }}"></script>
    <script src="{{ asset('assets/js/persian-date.min.js') }}"></script>
    <script src="{{ asset('assets/js/persian-datepicker.min.js') }}"></script>
    <link rel="stylesheet" href="{{ asset('assets/css/persian-datepicker.min.css') }}">

    <script>
        $(document).ready(function() {
            // Initialize Persian datepicker for all elements with class 'datepicker'
            $('.datepicker').persianDatepicker({
                format: 'YYYY/MM/DD',
                initialValue: false,
                autoClose: true,
                calendar: {
                    persian: {
                        locale: 'fa'
                    }
                },
                navigator: {
                    enabled: true
                },
                toolbox: {
                    enabled: true
                }
            });
        });
    </script>

    @stack('scripts')
</body>
</html>
