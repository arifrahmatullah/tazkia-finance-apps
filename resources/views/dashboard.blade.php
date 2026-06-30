<x-layouts.app title="Dashboard" breadcrumb="Tazkia Finance / Dashboard">

    {{-- Stats Cards --}}
    <div style="display:grid; grid-template-columns:repeat(4,1fr); gap:16px; margin-bottom:24px;">
        @foreach([
            ['label'=>'Total Organisasi', 'value'=>'3', 'icon'=>'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4', 'color'=>'#0d2d6b', 'bg'=>'#eff6ff', 'iconbg'=>'#dbeafe'],
            ['label'=>'Total Karyawan', 'value'=>'0', 'icon'=>'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z', 'color'=>'#065f46', 'bg'=>'#ecfdf5', 'iconbg'=>'#d1fae5'],
            ['label'=>'Pengajuan Aktif', 'value'=>'0', 'icon'=>'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z', 'color'=>'#92400e', 'bg'=>'#fffbeb', 'iconbg'=>'#fde68a'],
            ['label'=>'Total User', 'value'=>'8', 'icon'=>'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z', 'color'=>'#6b21a8', 'bg'=>'#faf5ff', 'iconbg'=>'#e9d5ff'],
        ] as $card)
        <div style="background:#fff; border-radius:14px; padding:20px; border:1px solid #f1f5f9; box-shadow:0 1px 4px rgba(0,0,0,0.04);">
            <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:14px;">
                <span style="font-size:0.78rem; font-weight:500; color:#64748b;">{{ $card['label'] }}</span>
                <div style="width:36px; height:36px; border-radius:9px; background:{{ $card['iconbg'] }}; display:flex; align-items:center; justify-content:center;">
                    <svg width="17" height="17" fill="none" stroke="{{ $card['color'] }}" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="{{ $card['icon'] }}"/>
                    </svg>
                </div>
            </div>
            <div style="font-size:1.9rem; font-weight:700; color:#0f172a; line-height:1;">{{ $card['value'] }}</div>
        </div>
        @endforeach
    </div>

    {{-- Content Row --}}
    <div style="display:grid; grid-template-columns:1fr 340px; gap:16px;">

        {{-- Aktivitas terbaru --}}
        <div style="background:#fff; border-radius:14px; border:1px solid #f1f5f9; box-shadow:0 1px 4px rgba(0,0,0,0.04); overflow:hidden;">
            <div style="padding:18px 20px; border-bottom:1px solid #f8fafc; display:flex; align-items:center; justify-content:space-between;">
                <h3 style="font-size:0.88rem; font-weight:600; color:#0f172a; margin:0;">Aktivitas Terbaru</h3>
                <span style="font-size:0.72rem; color:#94a3b8;">Hari ini</span>
            </div>
            <div style="padding:32px 20px; text-align:center; color:#94a3b8;">
                <svg width="40" height="40" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" style="margin:0 auto 10px; opacity:0.4;">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                <p style="font-size:0.8rem; margin:0;">Belum ada aktivitas</p>
            </div>
        </div>

        {{-- Info panel --}}
        <div style="display:flex; flex-direction:column; gap:16px;">

            {{-- Organisasi list --}}
            <div style="background:#fff; border-radius:14px; border:1px solid #f1f5f9; box-shadow:0 1px 4px rgba(0,0,0,0.04); overflow:hidden;">
                <div style="padding:16px 18px; border-bottom:1px solid #f8fafc;">
                    <h3 style="font-size:0.85rem; font-weight:600; color:#0f172a; margin:0;">Organisasi</h3>
                </div>
                <div style="padding:12px;">
                    @foreach(\App\Models\Organization::where('is_active', true)->get() as $org)
                    <div style="display:flex; align-items:center; gap:10px; padding:8px 8px; border-radius:8px; margin-bottom:2px;">
                        <div style="
                            width:30px; height:30px; border-radius:7px; flex-shrink:0;
                            background:{{ $org->type === 'yayasan' ? '#eff6ff' : '#fff7ed' }};
                            display:flex; align-items:center; justify-content:center;
                            font-size:0.65rem; font-weight:700;
                            color:{{ $org->type === 'yayasan' ? '#1d4ed8' : '#c2410c' }};
                        ">
                            {{ strtoupper(substr($org->code, 0, 2)) }}
                        </div>
                        <div>
                            <div style="font-size:0.8rem; font-weight:500; color:#1e293b;">{{ $org->name }}</div>
                            <div style="font-size:0.68rem; color:#94a3b8; text-transform:capitalize;">{{ $org->type }}</div>
                        </div>
                        <div style="margin-left:auto;">
                            <span style="display:inline-block; width:7px; height:7px; border-radius:50%; background:#22c55e;"></span>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Role summary --}}
            <div style="background:#fff; border-radius:14px; border:1px solid #f1f5f9; box-shadow:0 1px 4px rgba(0,0,0,0.04); overflow:hidden;">
                <div style="padding:16px 18px; border-bottom:1px solid #f8fafc;">
                    <h3 style="font-size:0.85rem; font-weight:600; color:#0f172a; margin:0;">User per Role</h3>
                </div>
                <div style="padding:12px;">
                    @foreach(\App\Models\Role::withCount('users')->get() as $role)
                    <div style="display:flex; align-items:center; justify-content:space-between; padding:7px 8px; border-radius:8px;">
                        <span style="font-size:0.8rem; color:#374151; text-transform:capitalize;">{{ $role->name }}</span>
                        <span style="
                            font-size:0.7rem; font-weight:600; padding:2px 9px; border-radius:999px;
                            background:{{ $role->slug === 'superadmin' ? '#eff6ff' : ($role->slug === 'keuangan' ? '#ecfdf5' : ($role->slug === 'akunting' ? '#faf5ff' : '#fff7ed')) }};
                            color:{{ $role->slug === 'superadmin' ? '#1d4ed8' : ($role->slug === 'keuangan' ? '#065f46' : ($role->slug === 'akunting' ? '#6b21a8' : '#c2410c')) }};
                        ">{{ $role->users_count }}</span>
                    </div>
                    @endforeach
                </div>
            </div>

        </div>
    </div>

</x-layouts.app>
