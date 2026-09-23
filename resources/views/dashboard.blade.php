<x-layouts.app title="Dashboard" breadcrumb="Tazkia Finance / Dashboard">

    {{-- Greeting + aksi cepat --}}
    <div class="flex items-end gap-4 flex-wrap mb-5">
        <div class="flex flex-col gap-1 mr-auto">
            <span class="text-[0.8rem] text-slate-400">{{ now()->locale('id')->translatedFormat('l, d F Y') }}</span>
            <h1 class="m-0 text-[1.6rem] font-bold tracking-tight text-slate-900">{{ $greeting }}, {{ explode(' ', auth()->user()->name)[0] }}</h1>
        </div>
        @if($stafStats)
        <div class="flex gap-2.5 flex-wrap">
            <a href="{{ route('fund-reports.index') }}" class="inline-flex items-center font-semibold text-sm px-4 py-2.5 rounded-[10px] whitespace-nowrap border border-slate-200 bg-white text-slate-900 no-underline hover:bg-slate-50 transition-colors">Buat Laporan</a>
            <a href="{{ route('fund-requests.create') }}" class="inline-flex items-center font-semibold text-sm px-[18px] py-2.5 rounded-[10px] whitespace-nowrap border-0 bg-orange-500 text-white no-underline hover:bg-orange-600 transition-colors shadow-sm">+ Ajukan Dana</a>
        </div>
        @endif
    </div>

    {{-- Alert defisit laba rugi --}}
    @if($labaRugi && $labaRugi['laba'] < 0)
    <div id="deficit-alert" class="flex items-center gap-3.5 px-4 py-3.5 rounded-xl bg-red-50 border border-red-200 flex-wrap mb-5">
        <div class="w-8 h-8 rounded-full bg-red-100 text-red-700 flex items-center justify-center font-extrabold flex-shrink-0">!</div>
        <div class="flex-1 min-w-[240px] text-sm leading-relaxed text-red-900">
            <strong class="text-red-800">Laba rugi {{ $labaRugi['tahun'] }} defisit Rp {{ number_format(abs($labaRugi['laba']), 0, ',', '.') }}.</strong>
            @if($labaRugi['pendapatan'] == 0)
                Belum ada pendapatan yang tercatat sejak Januari. Periksa pencatatan pendapatan di Buku Besar.
            @else
                Beban lebih besar dari pendapatan tahun ini. Periksa Laba Rugi untuk detailnya.
            @endif
        </div>
        <a href="{{ route('reports.income-statement') }}" class="text-[0.83rem] font-semibold text-red-800">Lihat Laba Rugi &rarr;</a>
        <button type="button" onclick="document.getElementById('deficit-alert').remove()" class="border-0 bg-transparent text-red-400 text-lg leading-none cursor-pointer px-1">&times;</button>
    </div>
    @endif

    {{-- Row 1: Pengajuan Saya + Laba Rugi --}}
    <div class="flex gap-5 flex-wrap mb-5">

        @if($stafStats)
        @php
            $total = max($stafStats['total_pengajuan'], 1);
            $closedPct = round($stafStats['closed'] / $total * 100, 1);
            $reportedPct = round($stafStats['sudah_laporan'] / $total * 100, 1);
            $doneOfTotal = $stafStats['total_pengajuan'] > 0 ? round(($stafStats['closed'] / $stafStats['total_pengajuan']) * 100) : 0;
            $statusTiles = [
                ['label' => 'Proses approval', 'value' => $stafStats['sedang_proses'], 'note' => $stafStats['sedang_proses'] > 0 ? 'menunggu persetujuan' : 'tidak ada antrian', 'dot' => '#f59e0b'],
                ['label' => 'Belum laporan', 'value' => $stafStats['belum_laporan'], 'note' => $stafStats['belum_laporan'] > 0 ? 'segera buat laporannya!' : 'semua sudah dilaporkan', 'dot' => '#eab308'],
                ['label' => 'Sudah laporan', 'value' => $stafStats['sudah_laporan'], 'note' => 'terkirim / disetujui', 'dot' => '#3b82f6'],
                ['label' => 'Pengembalian', 'value' => $stafStats['refund_total'], 'note' => $stafStats['refund_pending'] > 0 ? $stafStats['refund_pending'] . ' belum dikembalikan' : 'tidak ada tagihan aktif', 'dot' => '#ef4444'],
                ['label' => 'Selesai', 'value' => $stafStats['closed'], 'note' => 'cair, laporan & refund tuntas', 'dot' => '#22a05a'],
            ];
        @endphp
        <section class="flex-[3_1_520px] min-w-0 bg-white border border-slate-100 rounded-2xl p-6 flex flex-col gap-[18px] shadow-sm">
            <div class="flex items-baseline gap-2.5 flex-wrap">
                <h2 class="m-0 text-[1rem] font-bold text-slate-900">Pengajuan Saya</h2>
                <span class="text-[0.78rem] text-slate-400">Tahun anggaran {{ now()->year }}</span>
                <a href="{{ route('fund-requests.index') }}" class="ml-auto text-[0.8rem] font-semibold">Lihat semua</a>
            </div>
            <div class="flex items-end gap-6 flex-wrap">
                <div class="flex flex-col gap-0.5">
                    <span class="text-[2.5rem] font-extrabold leading-none tracking-tight text-slate-900 tabular-nums">{{ $stafStats['total_pengajuan'] }}</span>
                    <span class="text-[0.8rem] text-slate-400">total pengajuan</span>
                </div>
                <div class="flex-1 min-w-[220px] flex flex-col gap-2">
                    <div class="flex justify-between text-[0.8rem]">
                        <span class="text-slate-600">{{ $stafStats['closed'] }} dari {{ $stafStats['total_pengajuan'] }} selesai</span>
                        <span class="font-bold text-green-700">{{ $doneOfTotal }}%</span>
                    </div>
                    <div class="flex h-2.5 rounded-full overflow-hidden bg-slate-100 gap-0.5">
                        <div style="width:{{ $closedPct }}%; background:#22a05a"></div>
                        <div style="width:{{ $reportedPct }}%; background:#3b82f6"></div>
                    </div>
                    <div class="flex gap-3.5 text-[0.73rem] text-slate-400 flex-wrap">
                        <span class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-sm inline-block" style="background:#22a05a"></span>Selesai</span>
                        <span class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-sm inline-block" style="background:#3b82f6"></span>Laporan diverifikasi</span>
                        <span class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-sm inline-block bg-slate-100 border border-slate-200"></span>Lainnya</span>
                    </div>
                </div>
            </div>
            <div class="grid gap-2.5" style="grid-template-columns:repeat(auto-fit,minmax(130px,1fr))">
                @foreach($statusTiles as $t)
                <div class="flex flex-col gap-1.5 px-3.5 py-3 rounded-xl border border-slate-100 bg-slate-50">
                    <span class="flex items-center gap-1.5 text-[0.78rem] text-slate-500"><span class="w-2 h-2 rounded-full inline-block" style="background:{{ $t['dot'] }}"></span>{{ $t['label'] }}</span>
                    <span class="text-[1.35rem] font-bold tabular-nums {{ $t['value'] > 0 ? 'text-slate-900' : 'text-slate-400' }}">{{ $t['value'] }}</span>
                    <span class="text-[0.7rem] text-slate-400 leading-snug">{{ $t['note'] }}</span>
                </div>
                @endforeach
            </div>
        </section>
        @endif

        @if($labaRugi)
        <section class="flex-[2_1_340px] min-w-0 bg-white border border-slate-100 rounded-2xl p-6 flex flex-col gap-4 shadow-sm">
            <div class="flex items-baseline gap-2.5">
                <h2 class="m-0 text-[1rem] font-bold text-slate-900">Laba Rugi {{ $labaRugi['tahun'] }}</h2>
                <span class="text-[0.78rem] text-slate-400">Jan &ndash; {{ now()->translatedFormat('M') }}</span>
            </div>
            <div class="flex flex-col gap-1 p-4 rounded-xl {{ $labaRugi['laba'] >= 0 ? 'bg-blue-50' : 'bg-red-50' }}">
                <span class="text-[0.78rem] font-semibold {{ $labaRugi['laba'] >= 0 ? 'text-blue-800' : 'text-red-800' }}">Laba (Rugi) Bersih</span>
                <span class="text-[1.8rem] font-extrabold tracking-tight tabular-nums {{ $labaRugi['laba'] >= 0 ? 'text-blue-700' : 'text-red-700' }}">Rp {{ number_format($labaRugi['laba'], 0, ',', '.') }}</span>
            </div>
            <div class="flex flex-col">
                <div class="flex items-center gap-3 py-3 border-b border-slate-100">
                    <span class="w-7 h-7 rounded-lg bg-green-50 text-green-700 flex items-center justify-center font-bold flex-shrink-0">&uarr;</span>
                    <span class="flex-1 text-sm text-slate-600">Pendapatan</span>
                    <span class="font-bold tabular-nums">Rp {{ number_format($labaRugi['pendapatan'], 0, ',', '.') }}</span>
                </div>
                <div class="flex items-center gap-3 py-3">
                    <span class="w-7 h-7 rounded-lg bg-red-50 text-red-700 flex items-center justify-center font-bold flex-shrink-0">&darr;</span>
                    <span class="flex-1 text-sm text-slate-600">Beban / Pengeluaran</span>
                    <span class="font-bold tabular-nums">Rp {{ number_format($labaRugi['beban'], 0, ',', '.') }}</span>
                </div>
            </div>
            <a href="{{ route('reports.income-statement') }}" class="text-[0.8rem] font-semibold mt-auto">Buka laporan lengkap &rarr;</a>
        </section>
        @endif
    </div>

    {{-- Row 2: Beban per bulan + Organisasi --}}
    <div class="flex gap-5 flex-wrap mb-5">

        @if($labaRugi)
        @php
            $monthNames = ['', 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
            $monthlyBeban = $labaRugi['monthlyBeban'];
            $maxBeban = max(array_merge($monthlyBeban, [1]));
            $avgBeban = count($monthlyBeban) > 0 ? array_sum($monthlyBeban) / count($monthlyBeban) : 0;
        @endphp
        <section class="flex-[3_1_520px] min-w-0 bg-white border border-slate-100 rounded-2xl p-6 flex flex-col gap-[18px] shadow-sm">
            <div class="flex items-baseline gap-2.5 flex-wrap">
                <h2 class="m-0 text-[1rem] font-bold text-slate-900">Beban per Bulan</h2>
                <span class="text-[0.78rem] text-slate-400">Rata-rata Rp {{ number_format($avgBeban / 1000000, 1, ',', '.') }} jt / bulan</span>
            </div>
            <div class="flex gap-2.5 items-end border-b border-slate-100" style="height:200px">
                @foreach($monthlyBeban as $m => $val)
                <div class="flex-1 h-full flex flex-col justify-end items-center gap-1.5" title="{{ $monthNames[$m] }}: Rp {{ number_format($val, 0, ',', '.') }}">
                    <span class="text-[0.68rem] font-semibold text-slate-600 tabular-nums">{{ number_format($val / 1000000, 1, ',', '.') }} jt</span>
                    <div class="w-full rounded-t-md bg-orange-300 hover:bg-orange-500 transition-colors" style="max-width:44px; height:{{ $val > 0 ? max(round($val / $maxBeban * 150), 4) : 2 }}px; margin:0 auto"></div>
                </div>
                @endforeach
            </div>
            <div class="flex gap-2.5 -mt-2.5">
                @foreach($monthlyBeban as $m => $val)
                <span class="flex-1 text-center text-[0.75rem] text-slate-400">{{ $monthNames[$m] }}</span>
                @endforeach
            </div>
        </section>
        @endif

        <section class="flex-[2_1_340px] min-w-0 bg-white border border-slate-100 rounded-2xl p-6 flex flex-col gap-3 shadow-sm">
            @php $organizations = \App\Models\Organization::where('is_active', true)->get(); @endphp
            <div class="flex items-baseline gap-2.5">
                <h2 class="m-0 text-[1rem] font-bold text-slate-900">Organisasi</h2>
                <span class="text-[0.78rem] text-slate-400">{{ $organizations->count() }} aktif</span>
            </div>
            <div class="flex flex-col">
                @foreach($organizations as $org)
                <div class="flex items-center gap-3 py-2.5 {{ !$loop->last ? 'border-b border-slate-100' : '' }}">
                    <span class="w-9 h-9 rounded-[10px] flex-shrink-0 flex items-center justify-center text-[0.7rem] font-bold" style="background:{{ $org->type === 'yayasan' ? '#eaf0ff' : '#fff1e7' }}; color:{{ $org->type === 'yayasan' ? '#1d3a8a' : '#c2410c' }}">{{ strtoupper(substr($org->code, 0, 2)) }}</span>
                    <div class="flex-1 flex flex-col">
                        <span class="text-sm font-semibold text-slate-800">{{ $org->name }}</span>
                        <span class="text-[0.75rem] text-slate-400 capitalize">{{ $org->type }}</span>
                    </div>
                    <span class="text-[0.72rem] font-semibold text-green-700 bg-green-50 px-2 py-0.5 rounded-full">Aktif</span>
                </div>
                @endforeach
            </div>
        </section>
    </div>

    {{-- Row 3: Aktivitas Terbaru + User per Role --}}
    <div class="flex gap-5 flex-wrap">

        <section class="flex-[3_1_520px] min-w-0 bg-white border border-slate-100 rounded-2xl p-6 flex flex-col gap-3 shadow-sm">
            <div class="flex items-baseline gap-2.5">
                <h2 class="m-0 text-[1rem] font-bold text-slate-900">Aktivitas Terbaru</h2>
                <span class="text-[0.78rem] text-slate-400">Hari ini</span>
            </div>
            <div class="flex flex-col items-center gap-2 py-9 px-3 text-center">
                <div class="w-12 h-12 rounded-2xl bg-slate-50 flex items-center justify-center text-slate-400">
                    <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>
                </div>
                <span class="text-sm font-semibold text-slate-800">Belum ada aktivitas hari ini</span>
                <span class="text-[0.8rem] text-slate-400 max-w-[340px]">Pengajuan, approval, dan laporan yang Anda proses akan muncul di sini.</span>
            </div>
        </section>

        <section class="flex-[2_1_340px] min-w-0 bg-white border border-slate-100 rounded-2xl p-6 flex flex-col gap-4 shadow-sm">
            @php
                $roles = \App\Models\Role::withCount('users')->get();
                $totalUsers = max($roles->sum('users_count'), 1);
                $roleColors = ['superadmin' => '#3b82f6', 'keuangan' => '#22a05a', 'akunting' => '#8b5cf6'];
            @endphp
            <div class="flex items-baseline gap-2.5">
                <h2 class="m-0 text-[1rem] font-bold text-slate-900">Pengguna per Role</h2>
                <span class="text-[0.78rem] text-slate-400">{{ $roles->sum('users_count') }} pengguna</span>
            </div>
            <div class="flex h-2.5 rounded-full overflow-hidden gap-0.5">
                @foreach($roles as $role)
                <div style="width:{{ round($role->users_count / $totalUsers * 100, 1) }}%; background:{{ $roleColors[$role->slug] ?? '#f58a4b' }}"></div>
                @endforeach
            </div>
            <div class="flex flex-col gap-0.5">
                @foreach($roles as $role)
                <div class="flex items-center gap-2.5 py-2 text-sm">
                    <span class="w-2.5 h-2.5 rounded-[3px] flex-shrink-0" style="background:{{ $roleColors[$role->slug] ?? '#f58a4b' }}"></span>
                    <span class="flex-1 capitalize">{{ $role->name }}</span>
                    <span class="text-[0.75rem] text-slate-400">{{ round($role->users_count / $totalUsers * 100, 1) }}%</span>
                    <span class="w-10 text-right font-bold tabular-nums">{{ $role->users_count }}</span>
                </div>
                @endforeach
            </div>
        </section>
    </div>

</x-layouts.app>
