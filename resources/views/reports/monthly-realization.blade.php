<x-layouts.app title="Laporan Realisasi Bulanan">

{{-- Header --}}
<div class="flex items-start justify-between gap-4 mb-5 flex-wrap">
    <div>
        <h2 class="text-lg font-bold text-slate-900 m-0 mb-1">Laporan Realisasi Bulanan</h2>
        <p class="text-xs text-slate-400 m-0">
            Tren pengajuan dan realisasi dana per bulan dalam satu periode anggaran
            @if($period)
                — periode <span class="font-semibold text-slate-500">{{ $period->name }}</span>
            @endif
        </p>
    </div>
</div>

@if($periods->isEmpty())
<div class="bg-white rounded-xl shadow-sm py-16 px-5 text-center">
    <div class="text-sm font-semibold text-slate-600 mb-1">Belum ada periode anggaran</div>
    <div class="text-xs text-slate-400">Buat periode anggaran terlebih dahulu untuk melihat laporan ini.</div>
</div>
@else

{{-- Filter Bar --}}
<form method="GET" action="{{ route('reports.monthly-realization') }}" class="bg-white rounded-xl shadow-sm p-4 mb-4 flex flex-wrap gap-3 items-end">
    <div class="min-w-[240px]">
        <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block mb-1.5">Periode Anggaran</label>
        <select name="budget_period_id" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm text-slate-700 bg-white outline-none focus:border-blue-400 transition-colors">
            @foreach($periods as $p)
                <option value="{{ $p->id }}" {{ $period && $period->id === $p->id ? 'selected' : '' }}>
                    {{ $p->name }} — {{ $p->organization?->name }}{{ $p->is_active ? ' (aktif)' : '' }}
                </option>
            @endforeach
        </select>
    </div>

    @if($departments->count() > 1)
    <div class="min-w-[200px]">
        <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block mb-1.5">Departemen</label>
        <select name="department_id" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm text-slate-700 bg-white outline-none focus:border-blue-400 transition-colors">
            <option value="">Semua Departemen</option>
            @foreach($departments as $dept)
                <option value="{{ $dept->id }}" {{ $department && $department->id === $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
            @endforeach
        </select>
    </div>
    @endif

    <div class="flex gap-2">
        <button type="submit" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-sm font-semibold bg-blue-600 text-white border-0 cursor-pointer hover:bg-blue-700 transition-colors">
            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
            Terapkan
        </button>
        <a href="{{ route('reports.monthly-realization') }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-sm font-medium bg-slate-100 text-slate-600 no-underline hover:bg-slate-200 transition-colors">
            Reset
        </a>
    </div>
</form>

{{-- Summary --}}
@if($totals)
<div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-4">
    <div class="bg-white rounded-xl shadow-sm px-4 py-3.5">
        <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-0.5">Pagu {{ $department ? 'Departemen' : 'Total' }}</div>
        <div class="text-base font-extrabold text-slate-800 font-mono leading-tight mt-1">Rp {{ number_format($totals->pagu, 0, ',', '.') }}</div>
    </div>
    <div class="bg-white rounded-xl shadow-sm px-4 py-3.5">
        <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-0.5">Total Diajukan</div>
        <div class="text-base font-extrabold text-slate-800 font-mono leading-tight mt-1">Rp {{ number_format($totals->diajukan, 0, ',', '.') }}</div>
    </div>
    <div class="bg-white rounded-xl shadow-sm px-4 py-3.5">
        <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-0.5">Realisasi s/d Akhir Periode</div>
        <div class="text-base font-extrabold text-green-600 font-mono leading-tight mt-1">Rp {{ number_format($totals->realisasi, 0, ',', '.') }}</div>
        @if(!is_null($totals->pct))
        <div class="text-xs text-slate-400 mt-0.5">{{ number_format($totals->pct, 1, ',', '.') }}% dari pagu</div>
        @endif
    </div>
    <div class="bg-white rounded-xl shadow-sm px-4 py-3.5">
        <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-0.5">Sisa Pagu</div>
        <div class="text-base font-extrabold {{ $totals->sisa < 0 ? 'text-red-600' : 'text-blue-600' }} font-mono leading-tight mt-1">Rp {{ number_format($totals->sisa, 0, ',', '.') }}</div>
    </div>
</div>
@endif

{{-- Tabel bulanan --}}
@if($months->isEmpty())
<div class="bg-white rounded-xl shadow-sm py-16 px-5 text-center">
    <div class="text-sm font-semibold text-slate-600 mb-1">Tidak ada data</div>
    <div class="text-xs text-slate-400">Pilih departemen atau periode lain.</div>
</div>
@else
<div class="bg-white rounded-xl shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-[11px] text-slate-400 uppercase tracking-wider bg-slate-50/70 border-b border-slate-100">
                    <th class="py-2.5 px-4 font-semibold">Bulan</th>
                    <th class="py-2.5 px-3 font-semibold text-center">Jml Pengajuan</th>
                    <th class="py-2.5 px-3 font-semibold text-right">Total Diajukan</th>
                    <th class="py-2.5 px-3 font-semibold text-center">Jml Cair</th>
                    <th class="py-2.5 px-3 font-semibold text-right">Realisasi Bulan Ini</th>
                    <th class="py-2.5 px-3 font-semibold text-right">Realisasi Kumulatif</th>
                    <th class="py-2.5 px-4 font-semibold min-w-[140px]">% Kumulatif dari Pagu</th>
                </tr>
            </thead>
            <tbody>
                @foreach($months as $m)
                @php
                    $pct = $m->pct_kumulatif;
                    $barColor = is_null($pct) ? 'bg-slate-300' : ($pct > 100 ? 'bg-red-500' : ($pct >= 80 ? 'bg-amber-500' : 'bg-green-500'));
                    $isCurrentMonth = \Carbon\Carbon::now()->translatedFormat('F Y') === $m->label;
                @endphp
                <tr class="border-b border-slate-50 last:border-0 hover:bg-slate-50/50 transition-colors {{ $isCurrentMonth ? 'bg-blue-50/40' : '' }}">
                    <td class="py-2.5 px-4 font-semibold text-slate-700 whitespace-nowrap">{{ $m->label }}</td>
                    <td class="py-2.5 px-3 text-center text-slate-600">{{ $m->jumlah_ajuan ?: '-' }}</td>
                    <td class="py-2.5 px-3 text-right font-mono text-slate-700 whitespace-nowrap">
                        {{ $m->diajukan > 0 ? 'Rp ' . number_format($m->diajukan, 0, ',', '.') : '-' }}
                    </td>
                    <td class="py-2.5 px-3 text-center text-slate-600">{{ $m->jumlah_cair ?: '-' }}</td>
                    <td class="py-2.5 px-3 text-right font-mono text-green-700 whitespace-nowrap">
                        {{ $m->realisasi > 0 ? 'Rp ' . number_format($m->realisasi, 0, ',', '.') : '-' }}
                    </td>
                    <td class="py-2.5 px-3 text-right font-mono font-semibold text-slate-800 whitespace-nowrap">Rp {{ number_format($m->kumulatif, 0, ',', '.') }}</td>
                    <td class="py-2.5 px-4">
                        @if(!is_null($pct))
                        <div class="flex items-center gap-2">
                            <div class="flex-1 h-1.5 rounded-full bg-slate-100 overflow-hidden min-w-[60px]">
                                <div class="h-full rounded-full {{ $barColor }}" style="width: {{ min($pct, 100) }}%"></div>
                            </div>
                            <span class="text-[11px] font-semibold {{ $pct > 100 ? 'text-red-600' : 'text-slate-500' }} whitespace-nowrap">{{ number_format($pct, 1, ',', '.') }}%</span>
                        </div>
                        @else
                        <span class="text-[11px] text-slate-400">-</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
<div class="text-[11px] text-slate-400 mt-3">
    "Realisasi Bulan Ini" dihitung dari tanggal pencairan dana (bukan tanggal pengajuan), dikurangi pengembalian dana terkonfirmasi.
</div>
@endif
@endif

</x-layouts.app>
