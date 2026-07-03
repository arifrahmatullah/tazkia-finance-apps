<x-layouts.app title="Dashboard" breadcrumb="Tazkia Finance / Dashboard">

    {{-- Stats Cards --}}
    <div class="grid grid-cols-4 gap-4 mb-6">
        @foreach([
            ['label'=>'Total Organisasi', 'value'=>'3', 'icon'=>'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4', 'color'=>'#0d2d6b', 'bg'=>'#eff6ff', 'iconbg'=>'#dbeafe'],
            ['label'=>'Total Karyawan', 'value'=>'0', 'icon'=>'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z', 'color'=>'#065f46', 'bg'=>'#ecfdf5', 'iconbg'=>'#d1fae5'],
            ['label'=>'Pengajuan Aktif', 'value'=>'0', 'icon'=>'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z', 'color'=>'#92400e', 'bg'=>'#fffbeb', 'iconbg'=>'#fde68a'],
            ['label'=>'Total User', 'value'=>'8', 'icon'=>'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z', 'color'=>'#6b21a8', 'bg'=>'#faf5ff', 'iconbg'=>'#e9d5ff'],
        ] as $card)
        <div class="bg-white rounded-xl p-5 border border-slate-100 shadow-sm">
            <div class="flex items-center justify-between mb-3.5">
                <span class="text-[0.78rem] font-medium text-slate-500">{{ $card['label'] }}</span>
                <div class="w-9 h-9 rounded-lg flex items-center justify-center" style="background:{{ $card['iconbg'] }}">
                    <svg width="17" height="17" fill="none" stroke="{{ $card['color'] }}" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="{{ $card['icon'] }}"/>
                    </svg>
                </div>
            </div>
            <div class="text-[1.9rem] font-bold text-slate-900 leading-none">{{ $card['value'] }}</div>
        </div>
        @endforeach
    </div>

    {{-- Content Row --}}
    <div class="grid grid-cols-[1fr_340px] gap-4">

        {{-- Aktivitas terbaru --}}
        <div class="bg-white rounded-xl border border-slate-100 shadow-sm overflow-hidden">
            <div class="px-5 py-[18px] border-b border-slate-50 flex items-center justify-between">
                <h3 class="text-[0.88rem] font-semibold text-slate-900 m-0">Aktivitas Terbaru</h3>
                <span class="text-[0.72rem] text-slate-400">Hari ini</span>
            </div>
            <div class="px-5 py-8 text-center text-slate-400">
                <svg width="40" height="40" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" class="mx-auto mb-2.5 opacity-40">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                <p class="text-[0.8rem] m-0">Belum ada aktivitas</p>
            </div>
        </div>

        {{-- Info panel --}}
        <div class="flex flex-col gap-4">

            {{-- Organisasi list --}}
            <div class="bg-white rounded-xl border border-slate-100 shadow-sm overflow-hidden">
                <div class="px-[18px] py-4 border-b border-slate-50">
                    <h3 class="text-[0.85rem] font-semibold text-slate-900 m-0">Organisasi</h3>
                </div>
                <div class="p-3">
                    @foreach(\App\Models\Organization::where('is_active', true)->get() as $org)
                    <div class="flex items-center gap-2.5 px-2 py-2 rounded-lg mb-0.5">
                        <div class="w-[30px] h-[30px] rounded-[7px] flex-shrink-0 flex items-center justify-center text-[0.65rem] font-bold" style="background:{{ $org->type === 'yayasan' ? '#eff6ff' : '#fff7ed' }}; color:{{ $org->type === 'yayasan' ? '#1d4ed8' : '#c2410c' }};">
                            {{ strtoupper(substr($org->code, 0, 2)) }}
                        </div>
                        <div>
                            <div class="text-[0.8rem] font-medium text-slate-800">{{ $org->name }}</div>
                            <div class="text-[0.68rem] text-slate-400 capitalize">{{ $org->type }}</div>
                        </div>
                        <div class="ml-auto">
                            <span class="inline-block w-[7px] h-[7px] rounded-full bg-green-500"></span>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Role summary --}}
            <div class="bg-white rounded-xl border border-slate-100 shadow-sm overflow-hidden">
                <div class="px-[18px] py-4 border-b border-slate-50">
                    <h3 class="text-[0.85rem] font-semibold text-slate-900 m-0">User per Role</h3>
                </div>
                <div class="p-3">
                    @foreach(\App\Models\Role::withCount('users')->get() as $role)
                    <div class="flex items-center justify-between px-2 py-[7px] rounded-lg">
                        <span class="text-[0.8rem] text-gray-700 capitalize">{{ $role->name }}</span>
                        <span class="text-[0.7rem] font-semibold px-[9px] py-0.5 rounded-full" style="background:{{ $role->slug === 'superadmin' ? '#eff6ff' : ($role->slug === 'keuangan' ? '#ecfdf5' : ($role->slug === 'akunting' ? '#faf5ff' : '#fff7ed')) }}; color:{{ $role->slug === 'superadmin' ? '#1d4ed8' : ($role->slug === 'keuangan' ? '#065f46' : ($role->slug === 'akunting' ? '#6b21a8' : '#c2410c')) }};">{{ $role->users_count }}</span>
                    </div>
                    @endforeach
                </div>
            </div>

        </div>
    </div>

</x-layouts.app>
