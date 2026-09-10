<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Dashboard' }} — Tazkia Finance</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        * { box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; margin: 0; background: #f1f5f9; }

        /* Sidebar */
        .sidebar {
            width: 260px; min-height: 100vh;
            background: linear-gradient(180deg, #040f2e 0%, #0d2d6b 100%);
            position: fixed; top: 0; left: 0; z-index: 50;
            display: flex; flex-direction: column;
            transition: transform 0.3s ease;
        }
        .sidebar-logo {
            padding: 20px 20px 16px;
            border-bottom: 1px solid rgba(255,255,255,0.07);
        }
        .sidebar-nav { flex: 1; padding: 12px 0; overflow-y: auto; }
        .nav-section-label {
            padding: 16px 20px 6px;
            font-size: 0.65rem; font-weight: 600;
            color: rgba(148,163,184,0.7);
            letter-spacing: 0.1em; text-transform: uppercase;
        }
        .nav-item {
            display: flex; align-items: center; gap: 10px;
            padding: 9px 16px 9px 20px; margin: 1px 10px;
            border-radius: 8px; cursor: pointer;
            text-decoration: none;
            color: rgba(203,213,225,0.85);
            font-size: 0.835rem; font-weight: 450;
            transition: all 0.15s;
            position: relative;
        }
        .nav-item:hover {
            background: rgba(255,255,255,0.07);
            color: #ffffff;
        }
        .nav-item.active {
            background: rgba(249,115,22,0.15);
            color: #ffffff;
            font-weight: 550;
        }
        .nav-item.active::before {
            content: '';
            position: absolute; left: -10px; top: 50%; transform: translateY(-50%);
            width: 3px; height: 18px;
            background: #f97316; border-radius: 0 3px 3px 0;
        }
        .nav-item svg { flex-shrink: 0; opacity: 0.8; }
        .nav-item.active svg { opacity: 1; color: #fb923c; }
        .nav-item:hover svg { opacity: 1; }

        /* Badge di nav */
        .nav-badge {
            margin-left: auto;
            background: rgba(249,115,22,0.2);
            color: #fb923c;
            font-size: 0.65rem; font-weight: 600;
            padding: 2px 7px; border-radius: 999px;
        }

        /* Sub menu */
        .nav-submenu { display: none; }
        .nav-submenu.open { display: block; }
        .nav-subitem {
            display: flex; align-items: center; gap: 8px;
            padding: 7px 16px 7px 46px; margin: 1px 10px;
            border-radius: 7px; cursor: pointer;
            text-decoration: none;
            color: rgba(148,163,184,0.8);
            font-size: 0.8rem;
            transition: all 0.15s;
        }
        .nav-subitem:hover { background: rgba(255,255,255,0.05); color: #ffffff; }
        .nav-subitem.active { color: #93c5fd; }
        .nav-subitem::before {
            content: ''; width: 5px; height: 5px;
            border-radius: 50%; background: currentColor;
            opacity: 0.5; flex-shrink: 0;
        }

        /* Sidebar footer */
        .sidebar-footer {
            padding: 16px;
            border-top: 1px solid rgba(255,255,255,0.07);
        }
        .user-card {
            display: flex; align-items: center; gap: 10px;
            padding: 10px 12px; border-radius: 10px;
            background: rgba(255,255,255,0.06);
            cursor: pointer; transition: background 0.15s;
        }
        .user-card:hover { background: rgba(255,255,255,0.1); }
        .user-avatar {
            width: 34px; height: 34px; border-radius: 9px;
            background: linear-gradient(135deg, #ea580c, #f97316);
            display: flex; align-items: center; justify-content: center;
            font-weight: 700; font-size: 0.8rem; color: white;
            flex-shrink: 0;
        }

        /* Main */
        .main-wrapper { margin-left: 260px; min-height: 100vh; display: flex; flex-direction: column; }

        /* Header */
        .header {
            background: #ffffff;
            border-bottom: 1px solid #e2e8f0;
            padding: 0 24px;
            height: 60px;
            display: flex; align-items: center; justify-content: space-between;
            position: sticky; top: 0; z-index: 40;
        }

        /* Content */
        .page-content { padding: 28px 28px; flex: 1; }

        /* Scrollbar sidebar */
        .sidebar-nav::-webkit-scrollbar { width: 4px; }
        .sidebar-nav::-webkit-scrollbar-track { background: transparent; }
        .sidebar-nav::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.1); border-radius: 4px; }

        /* Mobile overlay */
        .sidebar-overlay {
            display: none; position: fixed; inset: 0;
            background: rgba(0,0,0,0.5); z-index: 49;
        }
        @media (max-width: 1024px) {
            .sidebar { transform: translateX(-100%); }
            .sidebar.open { transform: translateX(0); }
            .sidebar-overlay.show { display: block; }
            .main-wrapper { margin-left: 0; }
        }
    </style>
</head>
<body>

{{-- Sidebar Overlay (mobile) --}}
<div class="sidebar-overlay" id="sidebar-overlay" onclick="closeSidebar()"></div>

{{-- SIDEBAR --}}
<aside class="sidebar" id="sidebar">

    {{-- Logo --}}
    <div class="sidebar-logo">
        <div style="display:flex; align-items:center; gap:10px;">
            <div style="
                width:36px; height:36px; border-radius:9px;
                background: linear-gradient(135deg, #ea580c, #f97316);
                display:flex; align-items:center; justify-content:center;
                box-shadow: 0 3px 10px rgba(234,88,12,0.35);
                flex-shrink: 0;
            ">
                <svg width="18" height="18" fill="none" stroke="white" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 11h.01M12 11h.01M15 11h.01M4 19h16a2 2 0 002-2V7a2 2 0 00-2-2H4a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
            </div>
            <div>
                <div style="color:#fff; font-weight:700; font-size:0.95rem; line-height:1.2;">Tazkia Finance</div>
                <div style="color:#93c5fd; font-size:0.62rem; letter-spacing:0.08em; text-transform:uppercase; font-weight:500;">Management System</div>
            </div>
        </div>
    </div>

    {{-- Navigation --}}
    <nav class="sidebar-nav">

        {{-- Dashboard --}}
        <a href="{{ route('dashboard') }}"
           class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
            </svg>
            Dashboard
        </a>

        {{-- PENGAJUAN --}}
        <div class="nav-section-label">Pengajuan Dana</div>

        <a href="{{ route('fund-requests.index') }}"
           class="nav-item {{ request()->routeIs('fund-requests.*') ? 'active' : '' }}">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            Pengajuan Dana
        </a>

        <a href="{{ route('fund-reports.index') }}"
           class="nav-item {{ request()->routeIs('fund-reports.*') ? 'active' : '' }}">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            Laporan Dana
        </a>

        <a href="{{ route('fund-approvals.inbox') }}"
           class="nav-item {{ request()->routeIs('fund-approvals.*') ? 'active' : '' }}">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
            </svg>
            Inbox Approval
        </a>

        @if(auth()->user()?->hasPermission('menu.pencairan-dana'))
        <a href="{{ route('finance.index') }}"
           class="nav-item {{ request()->routeIs('finance.index') ? 'active' : '' }}">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
            </svg>
            Pencairan Dana
        </a>
        <a href="{{ route('finance.laporan') }}"
           class="nav-item {{ request()->routeIs('finance.laporan*') ? 'active' : '' }}">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            Verifikasi Laporan
        </a>
        @endif

        {{-- ANGGARAN --}}
        <div class="nav-section-label">Anggaran</div>

        <a href="{{ route('budget-periods.index') }}"
           class="nav-item {{ request()->routeIs('budget-periods.*') ? 'active' : '' }}">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
            Periode Anggaran
        </a>

        <a href="{{ route('budget-allocations.index') }}"
           class="nav-item {{ request()->routeIs('budget-allocations.*') ? 'active' : '' }}">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
            </svg>
            Alokasi Anggaran
        </a>

        @if(auth()->user()?->hasPermission('menu.program-kerja'))
        <a href="{{ route('budget-programs.index') }}"
           class="nav-item {{ request()->routeIs('budget-programs.*') || request()->routeIs('budget-program-details.*') ? 'active' : '' }}">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2M15 11h.01M12 11h.01M9 11h.01M15 15h.01M12 15h.01M9 15h.01"/>
            </svg>
            Program Kerja
        </a>
        @endif

        <a href="{{ route('income-estimates.index') }}"
           class="nav-item {{ request()->routeIs('income-estimates.*') ? 'active' : '' }}">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
            </svg>
            Estimasi Pendapatan
        </a>

        {{-- AKUNTING --}}
        <div class="nav-section-label">Akunting</div>

        <a href="{{ route('journal-entries.index') }}"
           class="nav-item {{ request()->routeIs('journal-entries.*') ? 'active' : '' }}">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            Jurnal Umum
        </a>

        <a href="{{ route('accounts.index') }}"
           class="nav-item {{ request()->routeIs('accounts.*') ? 'active' : '' }}">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4"/>
            </svg>
            Chart of Accounts
        </a>

        {{-- MASTER DATA --}}
        <div class="nav-section-label">Master Data</div>

        <a href="{{ route('organizations.index') }}"
           class="nav-item {{ request()->routeIs('organizations.*') ? 'active' : '' }}">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
            </svg>
            Organisasi
        </a>

        <a href="{{ route('departments.index') }}"
           class="nav-item {{ request()->routeIs('departments.*') ? 'active' : '' }}">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
            </svg>
            Departemen
        </a>

        <a href="{{ route('positions.index') }}"
           class="nav-item {{ request()->routeIs('positions.*') ? 'active' : '' }}">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
            </svg>
            Jabatan
        </a>

        <a href="{{ route('employees.index') }}"
           class="nav-item {{ request()->routeIs('employees.*') ? 'active' : '' }}">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            Karyawan
        </a>

        {{-- SISTEM --}}
        <div class="nav-section-label">Sistem</div>

        <a href="{{ route('users.index') }}"
           class="nav-item {{ request()->routeIs('users.*') ? 'active' : '' }}">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
            </svg>
            Manajemen User
        </a>

        <a href="{{ route('approval-settings.index') }}"
           class="nav-item {{ request()->routeIs('approval-settings.*') ? 'active' : '' }}">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/>
            </svg>
            Setting Approval
        </a>

        @if(auth()->user()?->hasPermission('menu.role-permissions'))
        <a href="{{ route('role-permissions.index') }}"
           class="nav-item {{ request()->routeIs('role-permissions.*') ? 'active' : '' }}">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
            </svg>
            Role & Permission
        </a>
        @endif

        <a href="{{ route('audit-logs.index') }}"
           class="nav-item {{ request()->routeIs('audit-logs.*') ? 'active' : '' }}">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
            </svg>
            Audit Log
        </a>

    </nav>

    {{-- User card --}}
    <div class="sidebar-footer">
        <div class="user-card">
            <div class="user-avatar">
                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
            </div>
            <div style="flex:1; min-width:0;">
                <div style="color:#fff; font-size:0.8rem; font-weight:600; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                    {{ auth()->user()->name }}
                </div>
                <div style="color:#93c5fd; font-size:0.68rem; margin-top:1px;">
                    {{ ucfirst(auth()->user()->role?->slug ?? '-') }}
                </div>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" title="Logout" style="background:none; border:none; cursor:pointer; color:rgba(148,163,184,0.7); padding:4px; border-radius:6px; display:flex; align-items:center; transition:color 0.15s;"
                    onmouseover="this.style.color='#f87171';" onmouseout="this.style.color='rgba(148,163,184,0.7)';">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                    </svg>
                </button>
            </form>
        </div>
    </div>
</aside>

{{-- MAIN WRAPPER --}}
<div class="main-wrapper">

    {{-- HEADER --}}
    <header class="header">
        <div style="display:flex; align-items:center; gap:14px;">
            {{-- Mobile hamburger --}}
            <button onclick="openSidebar()" class="lg:hidden" style="background:none; border:none; cursor:pointer; padding:6px; border-radius:8px; color:#64748b; display:none;">
                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>

            {{-- Breadcrumb / Page title --}}
            <div>
                <h1 style="font-size:0.95rem; font-weight:600; color:#0f172a; margin:0; line-height:1.3;">
                    {{ $title ?? 'Dashboard' }}
                </h1>
                @isset($breadcrumb)
                <div style="font-size:0.72rem; color:#94a3b8; margin-top:1px;">{{ $breadcrumb }}</div>
                @endisset
            </div>
        </div>

        {{-- Header Right --}}
        <div style="display:flex; align-items:center; gap:10px;">

            {{-- Org badge --}}
            <div style="
                padding: 5px 12px; border-radius:999px;
                background: #eff6ff; border: 1px solid #bfdbfe;
                font-size: 0.72rem; font-weight: 600; color: #1d4ed8;
            ">
                {{ auth()->user()->role?->slug === 'superadmin' ? 'Semua Organisasi' : (auth()->user()->organizationRoles->first()?->organization?->name ?? '-') }}
            </div>

            {{-- Notification --}}
            <button style="
                width:36px; height:36px; border-radius:9px;
                background:#f8fafc; border:1px solid #e2e8f0;
                display:flex; align-items:center; justify-content:center;
                cursor:pointer; color:#64748b; position:relative;
            ">
                <svg width="17" height="17" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                </svg>
                <span style="position:absolute; top:7px; right:7px; width:7px; height:7px; background:#f97316; border-radius:50%; border:1.5px solid #fff;"></span>
            </button>

            {{-- Avatar --}}
            <div style="
                width:36px; height:36px; border-radius:9px;
                background: linear-gradient(135deg, #0d2d6b, #1a4fad);
                display:flex; align-items:center; justify-content:center;
                font-weight:700; font-size:0.8rem; color:white; cursor:pointer;
            ">
                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
            </div>
        </div>
    </header>

    {{-- PAGE CONTENT --}}
    <main class="page-content">
        {{ $slot }}
    </main>

</div>

<script>
    function toggleSubmenu(id) {
        const sub = document.getElementById(id);
        const arrowId = id.replace('sub-', 'arrow-');
        const arrow = document.getElementById(arrowId);
        const isOpen = sub.classList.contains('open');
        sub.classList.toggle('open');
        if (arrow) arrow.style.transform = isOpen ? 'rotate(0deg)' : 'rotate(180deg)';
    }

    function openSidebar() {
        document.getElementById('sidebar').classList.add('open');
        document.getElementById('sidebar-overlay').classList.add('show');
    }
    function closeSidebar() {
        document.getElementById('sidebar').classList.remove('open');
        document.getElementById('sidebar-overlay').classList.remove('show');
    }

    // Auto-open submenu if active
    document.querySelectorAll('.nav-submenu').forEach(sub => {
        if (sub.querySelector('.nav-subitem.active')) {
            sub.classList.add('open');
            const arrowId = sub.id.replace('sub-', 'arrow-');
            const arrow = document.getElementById(arrowId);
            if (arrow) arrow.style.transform = 'rotate(180deg)';
        }
    });
</script>

</body>
</html>
