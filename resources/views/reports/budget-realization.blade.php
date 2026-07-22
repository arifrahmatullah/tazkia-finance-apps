<x-layouts.app title="Laporan Realisasi Anggaran">

{{-- Header --}}
<div class="flex items-start justify-between gap-4 mb-5 flex-wrap">
    <div>
        <h2 class="text-lg font-bold text-slate-900 m-0 mb-1">Laporan Realisasi Anggaran</h2>
        <p class="text-xs text-slate-400 m-0">
            Perbandingan anggaran program kerja dengan dana yang sudah dicairkan
            @if($period)
                — periode <span class="font-semibold text-slate-500">{{ $period->name }}</span>
                ({{ $period->organization?->name }})
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
<form method="GET" action="{{ route('reports.budget-realization') }}" class="bg-white rounded-xl shadow-sm p-4 mb-4 flex flex-wrap gap-3 items-end">
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
                <option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
            @endforeach
        </select>
    </div>
    @endif

    <div class="flex-1 min-w-[180px]">
        <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block mb-1.5">Cari Program</label>
        <input type="text" name="search" value="{{ request('search') }}"
            placeholder="Nama program kerja..."
            class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm outline-none focus:border-blue-400 transition-colors">
    </div>

    <div class="flex gap-2">
        <button type="submit" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-sm font-semibold bg-blue-600 text-white border-0 cursor-pointer hover:bg-blue-700 transition-colors">
            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
            Terapkan
        </button>
        <a href="{{ route('reports.budget-realization') }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-sm font-medium bg-slate-100 text-slate-600 no-underline hover:bg-slate-200 transition-colors">
            Reset
        </a>
    </div>
</form>

{{-- Summary --}}
@if($totals)
<div class="grid grid-cols-2 sm:grid-cols-5 gap-3 mb-4">
    <div class="bg-white rounded-xl shadow-sm px-4 py-3.5">
        <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-0.5">Pagu Alokasi</div>
        <div class="text-base font-extrabold text-slate-800 font-mono leading-tight mt-1">Rp {{ number_format($totals->pagu, 0, ',', '.') }}</div>
    </div>
    <div class="bg-white rounded-xl shadow-sm px-4 py-3.5">
        <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-0.5">Anggaran Program</div>
        <div class="text-base font-extrabold text-slate-800 font-mono leading-tight mt-1">Rp {{ number_format($totals->budget, 0, ',', '.') }}</div>
    </div>
    <div class="bg-white rounded-xl shadow-sm px-4 py-3.5">
        <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-0.5">Realisasi</div>
        <div class="text-base font-extrabold text-green-600 font-mono leading-tight mt-1">Rp {{ number_format($totals->realized, 0, ',', '.') }}</div>
        @if(!is_null($totals->pct))
        <div class="text-xs text-slate-400 mt-0.5">{{ number_format($totals->pct, 1, ',', '.') }}% dari anggaran</div>
        @endif
    </div>
    <div class="bg-white rounded-xl shadow-sm px-4 py-3.5">
        <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-0.5">Sedang Proses</div>
        <div class="text-base font-extrabold text-amber-500 font-mono leading-tight mt-1">Rp {{ number_format($totals->in_process, 0, ',', '.') }}</div>
        <div class="text-xs text-slate-400 mt-0.5">pengajuan belum cair</div>
    </div>
    <div class="bg-white rounded-xl shadow-sm px-4 py-3.5 col-span-2 sm:col-span-1">
        <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-0.5">Sisa Anggaran</div>
        <div class="text-base font-extrabold {{ $totals->remaining < 0 ? 'text-red-600' : 'text-blue-600' }} font-mono leading-tight mt-1">Rp {{ number_format($totals->remaining, 0, ',', '.') }}</div>
    </div>
</div>
@endif

{{-- Tabel realisasi per departemen --}}
@if($groups->isEmpty())
<div class="bg-white rounded-xl shadow-sm py-16 px-5 text-center">
    <div class="w-16 h-16 rounded-2xl bg-slate-50 border border-slate-100 flex items-center justify-center mx-auto mb-4">
        <svg width="28" height="28" fill="none" stroke="#94a3b8" stroke-width="1.5" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
        </svg>
    </div>
    <div class="text-sm font-semibold text-slate-600 mb-1">Tidak ada data</div>
    <div class="text-xs text-slate-400">Tidak ada alokasi anggaran pada periode/filter yang dipilih.</div>
</div>
@else
<div class="bg-white rounded-xl shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-[11px] text-slate-400 uppercase tracking-wider bg-slate-50/70 border-b border-slate-100">
                    <th class="py-2.5 px-4 font-semibold min-w-[220px]">Program Kerja</th>
                    <th class="py-2.5 px-3 font-semibold text-right">Anggaran</th>
                    <th class="py-2.5 px-3 font-semibold text-right">Realisasi</th>
                    <th class="py-2.5 px-3 font-semibold text-right">Proses</th>
                    <th class="py-2.5 px-3 font-semibold text-right">Sisa</th>
                    <th class="py-2.5 px-4 font-semibold min-w-[140px]">Serapan</th>
                </tr>
            </thead>
            <tbody>
                @foreach($groups as $group)
                {{-- Baris departemen --}}
                <tr class="bg-slate-100/70 border-b border-slate-200/60">
                    <td class="py-2 px-4">
                        <span class="font-bold text-slate-700 text-[13px]">{{ $group->department }}</span>
                        <span class="text-[11px] text-slate-400 ml-2">pagu alokasi: Rp {{ number_format($group->pagu, 0, ',', '.') }}</span>
                    </td>
                    <td class="py-2 px-3 text-right font-mono font-semibold text-slate-700">Rp {{ number_format($group->budget, 0, ',', '.') }}</td>
                    <td class="py-2 px-3 text-right font-mono font-semibold text-green-700">Rp {{ number_format($group->realized, 0, ',', '.') }}</td>
                    <td class="py-2 px-3 text-right font-mono font-semibold text-amber-600">Rp {{ number_format($group->in_process, 0, ',', '.') }}</td>
                    <td class="py-2 px-3 text-right font-mono font-semibold {{ $group->remaining < 0 ? 'text-red-600' : 'text-slate-700' }}">Rp {{ number_format($group->remaining, 0, ',', '.') }}</td>
                    <td class="py-2 px-4 text-[11px] font-semibold text-slate-500">
                        {{ $group->budget > 0 ? number_format($group->realized / $group->budget * 100, 1, ',', '.') . '%' : '-' }}
                    </td>
                </tr>

                {{-- Baris program --}}
                @forelse($group->programs as $prog)
                @php
                    $pct = $prog->pct;
                    $barColor = is_null($pct) ? 'bg-slate-300' : ($pct > 100 ? 'bg-red-500' : ($pct >= 80 ? 'bg-amber-500' : 'bg-green-500'));
                @endphp
                <tr class="border-b border-slate-50 last:border-0 hover:bg-slate-50/50 transition-colors">
                    <td class="py-2.5 px-4 pl-7">
                        <div class="text-slate-700 font-medium">{{ $prog->name }}</div>
                        <div class="text-[11px] text-slate-400">
                            {{ $prog->type_label }}
                            @if($prog->account) · <span class="font-mono">{{ $prog->account->code }}</span> {{ $prog->account->name }} @endif
                            @if($prog->count > 0) · {{ $prog->count }}x pencairan @endif
                        </div>
                    </td>
                    <td class="py-2.5 px-3 text-right font-mono text-slate-700 whitespace-nowrap">Rp {{ number_format($prog->budget, 0, ',', '.') }}</td>
                    <td class="py-2.5 px-3 text-right font-mono text-green-700 whitespace-nowrap">
                        Rp {{ number_format($prog->realized, 0, ',', '.') }}
                        @if($prog->refunded > 0)
                        <div class="text-[10px] text-slate-400 font-sans">cair {{ number_format($prog->disbursed, 0, ',', '.') }} − kembali {{ number_format($prog->refunded, 0, ',', '.') }}</div>
                        @endif
                    </td>
                    <td class="py-2.5 px-3 text-right font-mono text-amber-600 whitespace-nowrap">
                        {{ $prog->in_process > 0 ? 'Rp ' . number_format($prog->in_process, 0, ',', '.') : '-' }}
                    </td>
                    <td class="py-2.5 px-3 text-right font-mono whitespace-nowrap {{ $prog->remaining < 0 ? 'text-red-600 font-semibold' : 'text-slate-700' }}">Rp {{ number_format($prog->remaining, 0, ',', '.') }}</td>
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
                @empty
                <tr class="border-b border-slate-50">
                    <td colspan="6" class="py-2.5 px-4 pl-7 text-[12px] text-slate-400 italic">Belum ada program kerja pada departemen ini.</td>
                </tr>
                @endforelse
                @endforeach
            </tbody>
            @if($totals)
            <tfoot>
                <tr class="bg-slate-50/70 border-t border-slate-200">
                    <td class="py-2.5 px-4 text-[11px] font-bold text-slate-500 uppercase tracking-widest">Total</td>
                    <td class="py-2.5 px-3 text-right font-mono font-bold text-slate-900 whitespace-nowrap">Rp {{ number_format($totals->budget, 0, ',', '.') }}</td>
                    <td class="py-2.5 px-3 text-right font-mono font-bold text-green-700 whitespace-nowrap">Rp {{ number_format($totals->realized, 0, ',', '.') }}</td>
                    <td class="py-2.5 px-3 text-right font-mono font-bold text-amber-600 whitespace-nowrap">Rp {{ number_format($totals->in_process, 0, ',', '.') }}</td>
                    <td class="py-2.5 px-3 text-right font-mono font-bold whitespace-nowrap {{ $totals->remaining < 0 ? 'text-red-600' : 'text-slate-900' }}">Rp {{ number_format($totals->remaining, 0, ',', '.') }}</td>
                    <td class="py-2.5 px-4 text-[11px] font-bold text-slate-500">{{ !is_null($totals->pct) ? number_format($totals->pct, 1, ',', '.') . '%' : '-' }}</td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>
</div>

<div class="text-[11px] text-slate-400 mt-3">
    Realisasi = dana dicairkan − pengembalian dana terkonfirmasi. Kolom <em>Proses</em> = pengajuan pending/disetujui yang belum dicairkan.
</div>
@endif

@endif

</x-layouts.app>
