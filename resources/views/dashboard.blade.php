<x-layouts.app title="Dashboard" breadcrumb="Tazkia Finance / Dashboard">

    {{-- Ringkasan personal staf pengaju --}}
    @if($stafStats)
    <div class="mb-6">
        <div class="flex items-center gap-2.5 mb-3.5">
            <h3 class="text-[0.9rem] font-bold text-slate-900 m-0">Ringkasan Pengajuan Saya</h3>
            <span class="text-[0.72rem] text-slate-400">{{ auth()->user()->name }}</span>
        </div>
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4">

            {{-- Total pengajuan --}}
            <a href="{{ route('fund-requests.index') }}" class="bg-white rounded-xl p-5 border border-slate-100 shadow-sm no-underline hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between mb-3.5">
                    <span class="text-[0.78rem] font-medium text-slate-500">Pengajuan Saya</span>
                    <div class="w-9 h-9 rounded-lg flex items-center justify-center" style="background:#dbeafe">
                        <svg width="17" height="17" fill="none" stroke="#1d4ed8" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                </div>
                <div class="text-[1.9rem] font-bold text-slate-900 leading-none">{{ $stafStats['total_pengajuan'] }}</div>
                <div class="text-[0.7rem] text-slate-400 mt-2">{{ $stafStats['sedang_proses'] }} sedang proses approval</div>
            </a>

            {{-- Sudah laporan --}}
            <a href="{{ route('fund-reports.index') }}" class="bg-white rounded-xl p-5 border border-slate-100 shadow-sm no-underline hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between mb-3.5">
                    <span class="text-[0.78rem] font-medium text-slate-500">Sudah Laporan</span>
                    <div class="w-9 h-9 rounded-lg flex items-center justify-center" style="background:#d1fae5">
                        <svg width="17" height="17" fill="none" stroke="#065f46" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                </div>
                <div class="text-[1.9rem] font-bold text-slate-900 leading-none">{{ $stafStats['sudah_laporan'] }}</div>
                <div class="text-[0.7rem] text-slate-400 mt-2">laporan terkirim / disetujui</div>
            </a>

            {{-- Belum laporan --}}
            <a href="{{ route('fund-reports.index') }}" class="rounded-xl p-5 border shadow-sm no-underline hover:shadow-md transition-shadow {{ $stafStats['belum_laporan'] > 0 ? 'bg-amber-50 border-amber-200' : 'bg-white border-slate-100' }}">
                <div class="flex items-center justify-between mb-3.5">
                    <span class="text-[0.78rem] font-medium {{ $stafStats['belum_laporan'] > 0 ? 'text-amber-700' : 'text-slate-500' }}">Belum Laporan</span>
                    <div class="w-9 h-9 rounded-lg flex items-center justify-center" style="background:#fde68a">
                        <svg width="17" height="17" fill="none" stroke="#92400e" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                </div>
                <div class="text-[1.9rem] font-bold leading-none {{ $stafStats['belum_laporan'] > 0 ? 'text-amber-600' : 'text-slate-900' }}">{{ $stafStats['belum_laporan'] }}</div>
                <div class="text-[0.7rem] mt-2 {{ $stafStats['belum_laporan'] > 0 ? 'text-amber-600 font-semibold' : 'text-slate-400' }}">
                    {{ $stafStats['belum_laporan'] > 0 ? 'segera buat laporannya!' : 'semua sudah dilaporkan' }}
                </div>
            </a>

            {{-- Pengembalian dana --}}
            <a href="{{ route('fund-refunds.index') }}" class="rounded-xl p-5 border shadow-sm no-underline hover:shadow-md transition-shadow {{ $stafStats['refund_pending'] > 0 ? 'bg-red-50 border-red-200' : 'bg-white border-slate-100' }}">
                <div class="flex items-center justify-between mb-3.5">
                    <span class="text-[0.78rem] font-medium {{ $stafStats['refund_pending'] > 0 ? 'text-red-700' : 'text-slate-500' }}">Pengembalian Dana</span>
                    <div class="w-9 h-9 rounded-lg flex items-center justify-center" style="background:#fecaca">
                        <svg width="17" height="17" fill="none" stroke="#991b1b" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 15v-1a4 4 0 00-4-4H8m0 0l3 3m-3-3l3-3"/></svg>
                    </div>
                </div>
                <div class="text-[1.9rem] font-bold leading-none {{ $stafStats['refund_pending'] > 0 ? 'text-red-600' : 'text-slate-900' }}">{{ $stafStats['refund_total'] }}</div>
                <div class="text-[0.7rem] mt-2 {{ $stafStats['refund_pending'] > 0 ? 'text-red-600 font-semibold' : 'text-slate-400' }}">
                    {{ $stafStats['refund_pending'] > 0 ? $stafStats['refund_pending'] . ' belum dikembalikan' : 'tidak ada tagihan aktif' }}
                </div>
            </a>

            {{-- Selesai / closed --}}
            <a href="{{ route('fund-requests.index') }}" class="bg-white rounded-xl p-5 border border-slate-100 shadow-sm no-underline hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between mb-3.5">
                    <span class="text-[0.78rem] font-medium text-slate-500">Selesai</span>
                    <div class="w-9 h-9 rounded-lg flex items-center justify-center" style="background:#e9d5ff">
                        <svg width="17" height="17" fill="none" stroke="#6b21a8" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    </div>
                </div>
                <div class="text-[1.9rem] font-bold text-slate-900 leading-none">{{ $stafStats['closed'] }}</div>
                <div class="text-[0.7rem] text-slate-400 mt-2">cair, laporan &amp; refund tuntas</div>
            </a>

        </div>
    </div>
    @endif

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
