<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Dashboard' }} — Tazkia Finance</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.min.js"></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { font-family: 'Inter', sans-serif; }

        /* Active nav indicator bar */
        .nav-item.active::before {
            content: '';
            position: absolute; left: -10px; top: 50%; transform: translateY(-50%);
            width: 3px; height: 18px;
            background: #f97316; border-radius: 0 3px 3px 0;
        }

        /* Sub-menu dot */
        .nav-subitem::before {
            content: ''; width: 5px; height: 5px;
            border-radius: 50%; background: currentColor;
            opacity: 0.5; flex-shrink: 0;
        }

        /* Sidebar scrollbar */
        .sidebar-nav { scrollbar-width: thin; scrollbar-color: rgba(249,115,22,0.4) transparent; }
        .sidebar-nav::-webkit-scrollbar { width: 3px; }
        .sidebar-nav::-webkit-scrollbar-track { background: transparent; }
        .sidebar-nav::-webkit-scrollbar-thumb {
            background: linear-gradient(180deg, rgba(249,115,22,0.6), rgba(234,88,12,0.3));
            border-radius: 99px;
        }

        /* Mobile sidebar */
        @media (max-width: 1024px) {
            .sidebar { transform: translateX(-100%); }
            .sidebar.open { transform: translateX(0); }
            .sidebar-overlay.show { display: block; }
            .main-wrapper { margin-left: 0 !important; }
        }
    </style>
</head>
<body class="m-0 bg-slate-100">

{{-- Sidebar Overlay (mobile) --}}
<div class="sidebar-overlay hidden fixed inset-0 bg-black/50 z-[49]"
     id="sidebar-overlay" onclick="closeSidebar()"></div>

{{-- SIDEBAR --}}
<aside class="sidebar w-[260px] h-screen fixed top-0 left-0 z-50 flex flex-col overflow-hidden transition-transform duration-300"
    id="sidebar"
    style="background: linear-gradient(180deg, #040f2e 0%, #0d2d6b 100%);">

    {{-- Logo --}}
    <div class="px-5 py-5 border-b border-white/[0.07]">
        <div class="flex items-center gap-2.5">
            <div class="w-9 h-9 rounded-[9px] flex items-center justify-center flex-shrink-0 shadow-lg"
                style="background: linear-gradient(135deg, #ea580c, #f97316); box-shadow: 0 3px 10px rgba(234,88,12,0.35);">
                <svg width="18" height="18" fill="none" stroke="white" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 11h.01M12 11h.01M15 11h.01M4 19h16a2 2 0 002-2V7a2 2 0 00-2-2H4a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
            </div>
            <div>
                <div class="text-white font-bold text-[0.95rem] leading-tight">Tazkia Finance</div>
                <div class="text-blue-300 text-[0.62rem] tracking-[0.08em] uppercase font-medium">Management System</div>
            </div>
        </div>
    </div>

    {{-- Navigation --}}
    <nav class="sidebar-nav flex-1 py-3 overflow-y-auto">

        {{-- Dashboard --}}
        @if(auth()->user()->hasPermission('menu.dashboard'))
        <a href="{{ route('dashboard') }}"
           class="nav-item flex items-center gap-2.5 px-5 py-[9px] mx-2.5 rounded-lg no-underline text-[0.835rem] transition-all relative
                  {{ request()->routeIs('dashboard') ? 'active bg-orange-500/[0.15] text-white font-[550]' : 'text-slate-300/85 font-[450] hover:bg-white/10 hover:text-white' }}">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"
                class="{{ request()->routeIs('dashboard') ? 'text-orange-300' : 'opacity-80' }}">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
            </svg>
            Dashboard
        </a>
        @endif

        {{-- MASTER DATA --}}
        @php $showMaster = auth()->user()->hasPermission('menu.organisasi') || auth()->user()->hasPermission('menu.departemen') || auth()->user()->hasPermission('menu.jabatan') || auth()->user()->hasPermission('menu.karyawan') || auth()->user()->hasPermission('menu.approval-settings'); @endphp
        @if($showMaster)
        <div class="px-5 pt-4 pb-1.5 text-[0.65rem] font-semibold text-slate-400/70 tracking-[0.1em] uppercase">Master Data</div>

        @if(auth()->user()->hasPermission('menu.organisasi') || auth()->user()->hasPermission('menu.departemen') || auth()->user()->hasPermission('menu.jabatan'))
        <div>
            <div class="nav-item flex items-center gap-2.5 px-5 py-[9px] mx-2.5 rounded-lg cursor-pointer text-[0.835rem] transition-all relative
                        {{ request()->routeIs('organizations.*') || request()->routeIs('departments.*') || request()->routeIs('positions.*') ? 'active bg-orange-500/[0.15] text-white font-[550]' : 'text-slate-300/85 font-[450] hover:bg-white/10 hover:text-white' }}"
                 onclick="toggleSubmenu('sub-org')">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"
                    class="{{ request()->routeIs('organizations.*') ? 'text-orange-300' : 'opacity-80' }}">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
                Organisasi
                <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"
                    class="ml-auto transition-transform duration-200" id="arrow-org">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                </svg>
            </div>
            <div class="nav-submenu {{ request()->routeIs('organizations.*') || request()->routeIs('departments.*') || request()->routeIs('positions.*') ? 'open' : '' }} hidden" id="sub-org">
                @if(auth()->user()->hasPermission('menu.organisasi'))
                <a href="{{ route('organizations.index') }}"
                   class="nav-subitem flex items-center gap-2 py-[7px] px-4 pl-[46px] mx-2.5 rounded-lg no-underline text-[0.8rem] transition-all
                          {{ request()->routeIs('organizations.*') ? 'active text-blue-300' : 'text-slate-400/80 hover:bg-white/5 hover:text-white' }}">Daftar Organisasi</a>
                @endif
                @if(auth()->user()->hasPermission('menu.departemen'))
                <a href="{{ route('departments.index') }}"
                   class="nav-subitem flex items-center gap-2 py-[7px] px-4 pl-[46px] mx-2.5 rounded-lg no-underline text-[0.8rem] transition-all
                          {{ request()->routeIs('departments.*') ? 'active text-blue-300' : 'text-slate-400/80 hover:bg-white/5 hover:text-white' }}">Departemen</a>
                @endif
                @if(auth()->user()->hasPermission('menu.jabatan'))
                <a href="{{ route('positions.index') }}"
                   class="nav-subitem flex items-center gap-2 py-[7px] px-4 pl-[46px] mx-2.5 rounded-lg no-underline text-[0.8rem] transition-all
                          {{ request()->routeIs('positions.*') ? 'active text-blue-300' : 'text-slate-400/80 hover:bg-white/5 hover:text-white' }}">Jabatan</a>
                @endif
            </div>
        </div>
        @endif

        @if(auth()->user()->hasPermission('menu.karyawan'))
        <a href="{{ route('employees.index') }}"
           class="nav-item flex items-center gap-2.5 px-5 py-[9px] mx-2.5 rounded-lg no-underline text-[0.835rem] transition-all relative
                  {{ request()->routeIs('employees.*') ? 'active bg-orange-500/[0.15] text-white font-[550]' : 'text-slate-300/85 font-[450] hover:bg-white/10 hover:text-white' }}">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"
                class="{{ request()->routeIs('employees.*') ? 'text-orange-300' : 'opacity-80' }}">
                <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            Karyawan
        </a>
        @endif

        @if(auth()->user()->hasPermission('menu.approval-settings'))
        <div>
            <div class="nav-item flex items-center gap-2.5 px-5 py-[9px] mx-2.5 rounded-lg cursor-pointer text-[0.835rem] transition-all relative
                        {{ request()->routeIs('approval-settings.*') ? 'active bg-orange-500/[0.15] text-white font-[550]' : 'text-slate-300/85 font-[450] hover:bg-white/10 hover:text-white' }}"
                 onclick="toggleSubmenu('sub-pengaturan')">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"
                    class="{{ request()->routeIs('approval-settings.*') ? 'text-orange-300' : 'opacity-80' }}">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                Pengaturan
                <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"
                    class="ml-auto transition-transform duration-200" id="arrow-pengaturan">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                </svg>
            </div>
            <div class="nav-submenu {{ request()->routeIs('approval-settings.*') ? 'open' : '' }} hidden" id="sub-pengaturan">
                <a href="{{ route('approval-settings.index') }}"
                   class="nav-subitem flex items-center gap-2 py-[7px] px-4 pl-[46px] mx-2.5 rounded-lg no-underline text-[0.8rem] transition-all
                          {{ request()->routeIs('approval-settings.*') ? 'active text-blue-300' : 'text-slate-400/80 hover:bg-white/5 hover:text-white' }}">Setting Approval</a>
            </div>
        </div>
        @endif
        @endif

        {{-- KEUANGAN --}}
        @php $showKeuangan = auth()->user()->hasPermission('menu.periode-anggaran') || auth()->user()->hasPermission('menu.estimasi-pendapatan') || auth()->user()->hasPermission('menu.pagu-anggaran') || auth()->user()->hasPermission('menu.program-kerja') || auth()->user()->hasPermission('menu.pengajuan-dana') || auth()->user()->hasPermission('menu.inbox-approval') || auth()->user()->hasPermission('menu.pencairan-dana') || auth()->user()->employee?->activePosition; @endphp
        @if($showKeuangan)
        <div class="px-5 pt-4 pb-1.5 text-[0.65rem] font-semibold text-slate-400/70 tracking-[0.1em] uppercase">Keuangan</div>

        @if(auth()->user()->hasPermission('menu.periode-anggaran') || auth()->user()->hasPermission('menu.estimasi-pendapatan') || auth()->user()->hasPermission('menu.pagu-anggaran'))
        <div>
            <div class="nav-item flex items-center gap-2.5 px-5 py-[9px] mx-2.5 rounded-lg cursor-pointer text-[0.835rem] transition-all relative
                        {{ request()->routeIs('budget-periods.*') || request()->routeIs('budget-allocations.*') || request()->routeIs('income-estimates.*') || request()->routeIs('income-estimate-details.*') ? 'active bg-orange-500/[0.15] text-white font-[550]' : 'text-slate-300/85 font-[450] hover:bg-white/10 hover:text-white' }}"
                 onclick="toggleSubmenu('sub-anggaran')">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"
                    class="{{ request()->routeIs('budget-periods.*') || request()->routeIs('budget-allocations.*') || request()->routeIs('income-estimates.*') ? 'text-orange-300' : 'opacity-80' }}">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
                Anggaran
                <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"
                    class="ml-auto transition-transform duration-200" id="arrow-anggaran">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                </svg>
            </div>
            <div class="nav-submenu {{ request()->routeIs('budget-periods.*') || request()->routeIs('budget-allocations.*') || request()->routeIs('income-estimates.*') || request()->routeIs('income-estimate-details.*') ? 'open' : '' }} hidden" id="sub-anggaran">
                @if(auth()->user()->hasPermission('menu.periode-anggaran'))
                <a href="{{ route('budget-periods.index') }}"
                   class="nav-subitem flex items-center gap-2 py-[7px] px-4 pl-[46px] mx-2.5 rounded-lg no-underline text-[0.8rem] transition-all
                          {{ request()->routeIs('budget-periods.*') ? 'active text-blue-300' : 'text-slate-400/80 hover:bg-white/5 hover:text-white' }}">Periode Anggaran</a>
                @endif
                @if(auth()->user()->hasPermission('menu.estimasi-pendapatan'))
                <a href="{{ route('income-estimates.index') }}"
                   class="nav-subitem flex items-center gap-2 py-[7px] px-4 pl-[46px] mx-2.5 rounded-lg no-underline text-[0.8rem] transition-all
                          {{ request()->routeIs('income-estimates.*') || request()->routeIs('income-estimate-details.*') ? 'active text-blue-300' : 'text-slate-400/80 hover:bg-white/5 hover:text-white' }}">Estimasi Pendapatan</a>
                @endif
                @if(auth()->user()->hasPermission('menu.pagu-anggaran'))
                <a href="{{ route('budget-allocations.index') }}"
                   class="nav-subitem flex items-center gap-2 py-[7px] px-4 pl-[46px] mx-2.5 rounded-lg no-underline text-[0.8rem] transition-all
                          {{ request()->routeIs('budget-allocations.*') ? 'active text-blue-300' : 'text-slate-400/80 hover:bg-white/5 hover:text-white' }}">Pagu Anggaran</a>
                @endif
            </div>
        </div>
        @endif

        @if(auth()->user()->hasPermission('menu.program-kerja'))
        <a href="{{ route('budget-programs.index') }}"
           class="nav-item flex items-center gap-2.5 px-5 py-[9px] mx-2.5 rounded-lg no-underline text-[0.835rem] transition-all relative
                  {{ request()->routeIs('budget-programs.*') || request()->routeIs('budget-program-details.*') ? 'active bg-orange-500/[0.15] text-white font-[550]' : 'text-slate-300/85 font-[450] hover:bg-white/10 hover:text-white' }}">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"
                class="{{ request()->routeIs('budget-programs.*') || request()->routeIs('budget-program-details.*') ? 'text-orange-300' : 'opacity-80' }}">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
            </svg>
            Program Kerja
        </a>
        @endif

        @if(auth()->user()->hasPermission('menu.pengajuan-dana') || auth()->user()->hasPermission('menu.inbox-approval') || auth()->user()->employee?->activePosition)
        @php
            // Jumlah pengajuan yang menunggu approval user ini (untuk badge sidebar)
            $inboxWaitingCount = 0;
            $inboxPosition = auth()->user()->employee?->activePosition?->position;
            if ($inboxPosition) {
                $inboxOrgIds = auth()->user()->organizationIds();
                $inboxWaitingCount = \App\Models\FundRequestApproval::where('approver_position_id', $inboxPosition->id)
                    ->where('status', 'waiting')
                    ->whereHas('fundRequest', function ($q) use ($inboxOrgIds) {
                        $q->where('status', 'pending')
                          ->whereColumn('current_step', 'fund_request_approvals.step');
                        if ($inboxOrgIds !== null) {
                            $q->whereIn('organization_id', $inboxOrgIds);
                        }
                    })
                    ->count();
            }
        @endphp
        @php $pengajuanActive = request()->routeIs('fund-requests.*') || request()->routeIs('fund-approvals.*') || request()->routeIs('fund-reports.*') || request()->routeIs('fund-refunds.*'); @endphp
        <div>
            <div class="nav-item flex items-center gap-2.5 px-5 py-[9px] mx-2.5 rounded-lg cursor-pointer text-[0.835rem] transition-all relative
                        {{ $pengajuanActive ? 'active bg-orange-500/[0.15] text-white font-[550]' : 'text-slate-300/85 font-[450] hover:bg-white/10 hover:text-white' }}"
                 onclick="toggleSubmenu('sub-pengajuan')">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"
                    class="{{ $pengajuanActive ? 'text-orange-300' : 'opacity-80' }}">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Pengajuan Dana
                <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"
                    class="ml-auto transition-transform duration-200" id="arrow-pengajuan">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                </svg>
            </div>
            <div class="nav-submenu {{ $pengajuanActive ? 'open' : '' }} hidden" id="sub-pengajuan">
                @if(auth()->user()->hasPermission('menu.pengajuan-dana'))
                <a href="{{ route('fund-requests.index') }}"
                   class="nav-subitem flex items-center gap-2 py-[7px] px-4 pl-[46px] mx-2.5 rounded-lg no-underline text-[0.8rem] transition-all
                          {{ request()->routeIs('fund-requests.*') ? 'active text-blue-300' : 'text-slate-400/80 hover:bg-white/5 hover:text-white' }}">Pengajuan Saya</a>
                <a href="{{ route('fund-reports.index') }}"
                   class="nav-subitem flex items-center gap-2 py-[7px] px-4 pl-[46px] mx-2.5 rounded-lg no-underline text-[0.8rem] transition-all
                          {{ request()->routeIs('fund-reports.*') || request()->routeIs('fund-refunds.*') ? 'active text-blue-300' : 'text-slate-400/80 hover:bg-white/5 hover:text-white' }}">Laporan Dana</a>
                @endif
                <a href="{{ route('fund-approvals.inbox') }}"
                   class="nav-subitem flex items-center gap-2 py-[7px] px-4 pl-[46px] mx-2.5 rounded-lg no-underline text-[0.8rem] transition-all
                          {{ request()->routeIs('fund-approvals.*') ? 'active text-blue-300' : 'text-slate-400/80 hover:bg-white/5 hover:text-white' }}">
                    Inbox Approval
                    @if($inboxWaitingCount > 0)
                    <span class="ml-auto inline-flex items-center justify-center min-w-[18px] h-[18px] px-1.5 rounded-full bg-red-500 text-white text-[10px] font-bold leading-none">{{ $inboxWaitingCount }}</span>
                    @endif
                </a>
            </div>
        </div>
        @endif

        @if(auth()->user()->hasPermission('menu.pencairan-dana'))
        @php
            // Jumlah pengajuan disetujui yang belum dicairkan (untuk badge sidebar)
            $financeOrgIds = auth()->user()->organizationIds();
            $pencairanWaitingCount = \App\Models\FundRequest::where('status', 'approved')
                ->whereNull('disbursed_at')
                ->when($financeOrgIds !== null, fn($q) => $q->whereIn('organization_id', $financeOrgIds))
                ->count();
        @endphp
        <div>
            <div class="nav-item flex items-center gap-2.5 px-5 py-[9px] mx-2.5 rounded-lg cursor-pointer text-[0.835rem] transition-all relative
                        {{ request()->routeIs('finance.*') ? 'active bg-orange-500/[0.15] text-white font-[550]' : 'text-slate-300/85 font-[450] hover:bg-white/10 hover:text-white' }}"
                 onclick="toggleSubmenu('sub-finance')">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"
                    class="{{ request()->routeIs('finance.*') ? 'text-orange-300' : 'opacity-80' }}">
                    <rect x="1" y="4" width="22" height="16" rx="2" ry="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <line x1="1" y1="10" x2="23" y2="10" stroke-linecap="round"/>
                </svg>
                Keuangan
                <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"
                    class="ml-auto transition-transform duration-200" id="arrow-finance">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                </svg>
            </div>
            <div class="nav-submenu {{ request()->routeIs('finance.*') ? 'open' : '' }} hidden" id="sub-finance">
                <a href="{{ route('finance.index') }}"
                   class="nav-subitem flex items-center gap-2 py-[7px] px-4 pl-[46px] mx-2.5 rounded-lg no-underline text-[0.8rem] transition-all
                          {{ request()->routeIs('finance.index') ? 'active text-blue-300' : 'text-slate-400/80 hover:bg-white/5 hover:text-white' }}">
                    Pencairan Dana
                    @if($pencairanWaitingCount > 0)
                    <span class="ml-auto inline-flex items-center justify-center min-w-[18px] h-[18px] px-1.5 rounded-full bg-red-500 text-white text-[10px] font-bold leading-none">{{ $pencairanWaitingCount }}</span>
                    @endif
                </a>
                <a href="{{ route('finance.laporan') }}"
                   class="nav-subitem flex items-center gap-2 py-[7px] px-4 pl-[46px] mx-2.5 rounded-lg no-underline text-[0.8rem] transition-all
                          {{ request()->routeIs('finance.laporan*') ? 'active text-blue-300' : 'text-slate-400/80 hover:bg-white/5 hover:text-white' }}">Verifikasi Laporan</a>
                <a href="{{ route('finance.pengembalian') }}"
                   class="nav-subitem flex items-center gap-2 py-[7px] px-4 pl-[46px] mx-2.5 rounded-lg no-underline text-[0.8rem] transition-all
                          {{ request()->routeIs('finance.pengembalian*') ? 'active text-blue-300' : 'text-slate-400/80 hover:bg-white/5 hover:text-white' }}">Pengembalian Dana</a>
            </div>
        </div>
        @endif
        @endif

        {{-- AKUNTING --}}
        @if(auth()->user()->hasPermission('menu.jurnal-umum') || auth()->user()->hasPermission('menu.coa'))
        <div class="px-5 pt-4 pb-1.5 text-[0.65rem] font-semibold text-slate-400/70 tracking-[0.1em] uppercase">Akunting</div>

        @if(auth()->user()->hasPermission('menu.jurnal-umum'))
        <a href="{{ route('journal-entries.index') }}"
           class="nav-item flex items-center gap-2.5 px-5 py-[9px] mx-2.5 rounded-lg no-underline text-[0.835rem] transition-all relative
                  {{ request()->routeIs('journal-entries.*') ? 'active bg-orange-500/[0.15] text-white font-[550]' : 'text-slate-300/85 font-[450] hover:bg-white/10 hover:text-white' }}">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"
                class="{{ request()->routeIs('journal-entries.*') ? 'text-orange-300' : 'opacity-80' }}">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            Jurnal Umum
        </a>
        @endif

        @if(auth()->user()->hasPermission('menu.coa'))
        <a href="{{ route('accounts.index') }}"
           class="nav-item flex items-center gap-2.5 px-5 py-[9px] mx-2.5 rounded-lg no-underline text-[0.835rem] transition-all relative
                  {{ request()->routeIs('accounts.*') ? 'active bg-orange-500/[0.15] text-white font-[550]' : 'text-slate-300/85 font-[450] hover:bg-white/10 hover:text-white' }}">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"
                class="{{ request()->routeIs('accounts.*') ? 'text-orange-300' : 'opacity-80' }}">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4"/>
            </svg>
            Chart of Accounts
        </a>
        @endif
        @endif

        {{-- LAPORAN --}}
        @if(auth()->user()->hasPermission('menu.laporan'))
        <div class="px-5 pt-4 pb-1.5 text-[0.65rem] font-semibold text-slate-400/70 tracking-[0.1em] uppercase">Laporan</div>
        <div>
            <div class="nav-item flex items-center gap-2.5 px-5 py-[9px] mx-2.5 rounded-lg cursor-pointer text-[0.835rem] text-slate-300/85 font-[450] hover:bg-white/10 hover:text-white transition-all relative"
                 onclick="toggleSubmenu('sub-laporan')">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" class="opacity-80">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Laporan Keuangan
                <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"
                    class="ml-auto transition-transform duration-200" id="arrow-laporan">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                </svg>
            </div>
            <div class="nav-submenu hidden" id="sub-laporan">
                <a href="#" class="nav-subitem flex items-center gap-2 py-[7px] px-4 pl-[46px] mx-2.5 rounded-lg no-underline text-[0.8rem] text-slate-400/80 hover:bg-white/5 hover:text-white transition-all">Realisasi Anggaran</a>
                <a href="#" class="nav-subitem flex items-center gap-2 py-[7px] px-4 pl-[46px] mx-2.5 rounded-lg no-underline text-[0.8rem] text-slate-400/80 hover:bg-white/5 hover:text-white transition-all">Arus Kas</a>
                <a href="#" class="nav-subitem flex items-center gap-2 py-[7px] px-4 pl-[46px] mx-2.5 rounded-lg no-underline text-[0.8rem] text-slate-400/80 hover:bg-white/5 hover:text-white transition-all">Neraca</a>
                <a href="#" class="nav-subitem flex items-center gap-2 py-[7px] px-4 pl-[46px] mx-2.5 rounded-lg no-underline text-[0.8rem] text-slate-400/80 hover:bg-white/5 hover:text-white transition-all">Laba Rugi</a>
            </div>
        </div>
        @endif

        {{-- SISTEM --}}
        @if(auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('menu.users') || auth()->user()->hasPermission('menu.role-permissions') || auth()->user()->hasPermission('menu.audit-logs'))
        <div class="px-5 pt-4 pb-1.5 text-[0.65rem] font-semibold text-slate-400/70 tracking-[0.1em] uppercase">Sistem</div>

        @if(auth()->user()->hasPermission('menu.users'))
        <a href="{{ route('users.index') }}"
           class="nav-item flex items-center gap-2.5 px-5 py-[9px] mx-2.5 rounded-lg no-underline text-[0.835rem] transition-all relative
                  {{ request()->routeIs('users.*') ? 'active bg-orange-500/[0.15] text-white font-[550]' : 'text-slate-300/85 font-[450] hover:bg-white/10 hover:text-white' }}">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"
                class="{{ request()->routeIs('users.*') ? 'text-orange-300' : 'opacity-80' }}">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
            </svg>
            Manajemen User
        </a>
        @endif

        @if(auth()->user()->hasPermission('menu.role-permissions'))
        <a href="{{ route('role-permissions.index') }}"
           class="nav-item flex items-center gap-2.5 px-5 py-[9px] mx-2.5 rounded-lg no-underline text-[0.835rem] transition-all relative
                  {{ request()->routeIs('role-permissions.*') ? 'active bg-orange-500/[0.15] text-white font-[550]' : 'text-slate-300/85 font-[450] hover:bg-white/10 hover:text-white' }}">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"
                class="{{ request()->routeIs('role-permissions.*') ? 'text-orange-300' : 'opacity-80' }}">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
            </svg>
            Setting Permission
        </a>
        @endif

        @if(auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('menu.audit-logs'))
        <a href="{{ route('audit-logs.index') }}"
           class="nav-item flex items-center gap-2.5 px-5 py-[9px] mx-2.5 rounded-lg no-underline text-[0.835rem] transition-all relative
                  {{ request()->routeIs('audit-logs.*') ? 'active bg-orange-500/[0.15] text-white font-[550]' : 'text-slate-300/85 font-[450] hover:bg-white/10 hover:text-white' }}">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"
                class="{{ request()->routeIs('audit-logs.*') ? 'text-orange-300' : 'opacity-80' }}">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
            </svg>
            Audit Log
        </a>
        @endif
        @endif


    </nav>

    {{-- User card --}}
    <div class="p-4 border-t border-white/[0.07]">
        <div class="flex items-center gap-2.5 px-3 py-2.5 rounded-xl bg-white/[0.06] cursor-pointer hover:bg-white/10 transition-colors">
            <div class="w-[34px] h-[34px] rounded-[9px] flex items-center justify-center font-bold text-[0.8rem] text-white flex-shrink-0"
                style="background: linear-gradient(135deg, #ea580c, #f97316);">
                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
            </div>
            <div class="flex-1 min-w-0">
                <div class="text-white text-[0.8rem] font-semibold truncate">{{ auth()->user()->name }}</div>
                <div class="text-blue-300 text-[0.68rem] mt-px">{{ ucfirst(auth()->user()->role?->slug ?? '-') }}</div>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" title="Logout"
                    class="bg-transparent border-0 cursor-pointer text-slate-400/70 p-1 rounded-md flex items-center hover:text-red-400 transition-colors">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                    </svg>
                </button>
            </form>
        </div>
    </div>
</aside>

{{-- MAIN WRAPPER --}}
<div class="main-wrapper ml-[260px] min-h-screen flex flex-col">

    {{-- HEADER --}}
    <header class="bg-white border-b border-slate-200 px-6 h-[60px] flex items-center justify-between sticky top-0 z-40">
        <div class="flex items-center gap-3.5">
            {{-- Mobile hamburger --}}
            <button onclick="openSidebar()"
                class="lg:hidden bg-transparent border-0 cursor-pointer p-1.5 rounded-lg text-slate-500">
                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>

            {{-- Breadcrumb / Page title --}}
            <div>
                <h1 class="text-[0.95rem] font-semibold text-slate-900 m-0 leading-tight">{{ $title ?? 'Dashboard' }}</h1>
                @isset($breadcrumb)
                <div class="text-[0.72rem] text-slate-400 mt-px">{{ $breadcrumb }}</div>
                @endisset
            </div>
        </div>

        {{-- Header Right --}}
        <div class="flex items-center gap-2.5">
            {{-- Org badge --}}
            <div class="px-3 py-1.5 rounded-full bg-blue-50 border border-blue-200 text-[0.72rem] font-semibold text-blue-700">
                {{ auth()->user()->role?->slug === 'superadmin' ? 'Semua Organisasi' : (auth()->user()->organizationRoles->first()?->organization?->name ?? '-') }}
            </div>

            {{-- Notification --}}
            <button class="relative w-9 h-9 rounded-[9px] bg-slate-50 border border-slate-200 flex items-center justify-center cursor-pointer text-slate-500 hover:bg-slate-100 transition-colors">
                <svg width="17" height="17" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                </svg>
                <span class="absolute top-[7px] right-[7px] w-[7px] h-[7px] bg-orange-400 rounded-full border-2 border-white"></span>
            </button>

            {{-- Avatar --}}
            <div class="w-9 h-9 rounded-[9px] flex items-center justify-center font-bold text-[0.8rem] text-white cursor-pointer"
                style="background: linear-gradient(135deg, #0d2d6b, #1a4fad);">
                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
            </div>
        </div>
    </header>

    {{-- PAGE CONTENT --}}
    <main class="px-7 py-7 flex-1">
        {{ $slot }}
    </main>

</div>

{{-- CONFIRM MODAL --}}
<div id="confirm-overlay"
    class="hidden fixed inset-0 z-[999] bg-slate-900/50 backdrop-blur-sm items-center justify-center">
    <div id="confirm-box"
        class="bg-white rounded-2xl w-[380px] max-w-[90vw] shadow-2xl overflow-hidden scale-95 opacity-0 transition-all duration-[180ms]">
        {{-- Header --}}
        <div class="p-5 pb-0 flex items-start gap-3.5">
            <div id="confirm-icon" class="w-[42px] h-[42px] rounded-xl flex-shrink-0 bg-red-50 flex items-center justify-center">
                <svg width="20" height="20" fill="none" stroke="#dc2626" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
            </div>
            <div>
                <div id="confirm-title" class="text-[0.97rem] font-bold text-slate-900">Hapus Data</div>
                <div id="confirm-message" class="text-[0.82rem] text-slate-500 mt-1 leading-relaxed"></div>
            </div>
        </div>

        {{-- Warning note --}}
        <div id="confirm-warning" class="mx-5 mt-3.5 px-3 py-2.5 bg-orange-50 border border-orange-200 rounded-lg flex items-center gap-2">
            <svg width="14" height="14" fill="none" stroke="#c2410c" stroke-width="2" viewBox="0 0 24 24" class="flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
            <span id="confirm-warning-text" class="text-[0.75rem] text-orange-700 font-medium">Tindakan ini tidak dapat dibatalkan.</span>
        </div>

        {{-- Buttons --}}
        <div class="px-5 py-4 flex gap-2 justify-end">
            <button onclick="closeConfirm()"
                class="px-5 py-2.5 rounded-xl text-[0.83rem] font-medium text-slate-700 bg-slate-100 border border-slate-200 cursor-pointer hover:bg-slate-200 transition-colors">
                Batal
            </button>
            <button id="confirm-btn" onclick="submitConfirm()"
                class="px-5 py-2.5 rounded-xl text-[0.83rem] font-semibold text-white border-0 cursor-pointer shadow-sm hover:shadow-md hover:-translate-y-px transition-all flex items-center gap-1.5">
                <span id="confirm-btn-label">Ya, Hapus</span>
            </button>
        </div>
    </div>
</div>

<script>
    // ── Confirm Modal ──────────────────────────────────────
    let _confirmForm = null;
    let _confirmCallback = null;

    function confirmDelete(formId, name) {
        _confirmForm = document.getElementById(formId);
        _confirmCallback = null;
        document.getElementById('confirm-message').innerHTML =
            'Yakin ingin menghapus <strong>"' + name + '"</strong>?';
        _showConfirm('Hapus Data', '#fef2f2', '#dc2626', 'Ya, Hapus', '#ef4444', '#dc2626', 'Tindakan ini tidak dapat dibatalkan.');
    }

    function confirmModal(title, message, callback, btnLabel, warningText) {
        _confirmForm = null;
        _confirmCallback = callback;
        document.getElementById('confirm-message').innerHTML = message;
        _showConfirm(title, '#fffbeb', '#d97706', btnLabel || 'Ya, Lanjutkan', '#f97316', '#ea580c', warningText || null);
    }

    function _showConfirm(title, iconBg, iconColor, btnLabel, btnFrom, btnTo, warningText) {
        document.getElementById('confirm-title').textContent = title;
        document.getElementById('confirm-icon').style.background = iconBg;
        document.getElementById('confirm-icon').querySelector('svg').style.stroke = iconColor;
        document.getElementById('confirm-btn-label').textContent = btnLabel;
        document.getElementById('confirm-btn').style.background =
            'linear-gradient(to bottom right, ' + btnFrom + ', ' + btnTo + ')';
        const warn = document.getElementById('confirm-warning');
        if (warningText) {
            document.getElementById('confirm-warning-text').textContent = warningText;
            warn.style.display = 'flex';
        } else {
            warn.style.display = 'none';
        }
        const overlay = document.getElementById('confirm-overlay');
        overlay.style.display = 'flex';
        setTimeout(() => {
            const box = document.getElementById('confirm-box');
            box.style.transform = 'scale(1)';
            box.style.opacity   = '1';
        }, 10);
    }

    function closeConfirm() {
        const box     = document.getElementById('confirm-box');
        const overlay = document.getElementById('confirm-overlay');
        box.style.transform = 'scale(0.95)';
        box.style.opacity   = '0';
        setTimeout(() => { overlay.style.display = 'none'; }, 180);
        _confirmForm = null;
    }

    function submitConfirm() {
        if (_confirmCallback) { closeConfirm(); _confirmCallback(); }
        else if (_confirmForm) _confirmForm.submit();
    }

    document.getElementById('confirm-overlay').addEventListener('click', function(e) {
        if (e.target === this) closeConfirm();
    });
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') closeConfirm();
    });

    // ── Sidebar ────────────────────────────────────────────
    function toggleSubmenu(id) {
        const sub = document.getElementById(id);
        const arrowId = id.replace('sub-', 'arrow-');
        const arrow = document.getElementById(arrowId);
        const isOpen = !sub.classList.contains('hidden');
        sub.classList.toggle('hidden');
        if (arrow) arrow.style.transform = isOpen ? 'rotate(0deg)' : 'rotate(180deg)';
    }

    function openSidebar() {
        document.getElementById('sidebar').classList.add('open');
        document.getElementById('sidebar-overlay').classList.add('show');
        document.getElementById('sidebar-overlay').classList.remove('hidden');
    }
    function closeSidebar() {
        document.getElementById('sidebar').classList.remove('open');
        document.getElementById('sidebar-overlay').classList.remove('show');
        document.getElementById('sidebar-overlay').classList.add('hidden');
    }

    // Auto-open submenu if active
    document.querySelectorAll('.nav-submenu').forEach(sub => {
        if (sub.querySelector('.active')) {
            sub.classList.remove('hidden');
            const arrowId = sub.id.replace('sub-', 'arrow-');
            const arrow = document.getElementById(arrowId);
            if (arrow) arrow.style.transform = 'rotate(180deg)';
        }
    });
</script>

</body>
</html>
