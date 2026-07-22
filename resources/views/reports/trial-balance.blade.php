<x-layouts.app title="Neraca Saldo">

{{-- Header --}}
<div class="flex items-start justify-between gap-4 mb-5 flex-wrap">
    <div>
        <h2 class="text-lg font-bold text-slate-900 m-0 mb-1">Neraca Saldo</h2>
        <p class="text-xs text-slate-400 m-0">
            Saldo awal, mutasi, dan saldo akhir seluruh akun dari jurnal yang sudah diposting
            @if($dateFrom || $dateTo)
                — periode
                {{ $dateFrom ? \Carbon\Carbon::parse($dateFrom)->translatedFormat('d M Y') : '…' }}
                s.d.
                {{ $dateTo ? \Carbon\Carbon::parse($dateTo)->translatedFormat('d M Y') : '…' }}
            @endif
        </p>
    </div>
</div>

{{-- Filter Bar --}}
<form method="GET" action="{{ route('reports.trial-balance') }}" class="bg-white rounded-xl shadow-sm p-4 mb-4 flex flex-wrap gap-3 items-end">
    @if($organizations->count() > 1)
    <div class="min-w-[180px]">
        <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block mb-1.5">Organisasi</label>
        <select name="organization_id" onchange="this.form.submit()"
            class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm text-slate-700 bg-white outline-none focus:border-blue-400 transition-colors">
            @foreach($organizations as $org)
                <option value="{{ $org->id }}" {{ $orgId === $org->id ? 'selected' : '' }}>{{ $org->name }}</option>
            @endforeach
        </select>
    </div>
    @else
    <input type="hidden" name="organization_id" value="{{ $orgId }}">
    @endif

    <div class="min-w-[150px]">
        <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block mb-1.5">Dari Tanggal</label>
        <input type="date" name="date_from" value="{{ $dateFrom }}"
            class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm text-slate-700 bg-white outline-none focus:border-blue-400 transition-colors">
    </div>
    <div class="min-w-[150px]">
        <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block mb-1.5">Sampai Tanggal</label>
        <input type="date" name="date_to" value="{{ $dateTo }}"
            class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm text-slate-700 bg-white outline-none focus:border-blue-400 transition-colors">
    </div>

    <label class="flex items-center gap-1.5 text-xs text-slate-500 pb-2.5 cursor-pointer select-none">
        <input type="checkbox" name="show_zero" value="1" {{ $showZero ? 'checked' : '' }}
            class="rounded border-slate-300 text-blue-600 focus:ring-blue-400">
        Tampilkan akun bersaldo nol
    </label>

    <div class="flex gap-2">
        <button type="submit" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-sm font-semibold bg-blue-600 text-white border-0 cursor-pointer hover:bg-blue-700 transition-colors">
            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
            Tampilkan
        </button>
        <a href="{{ route('reports.trial-balance') }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-sm font-medium bg-slate-100 text-slate-600 no-underline hover:bg-slate-200 transition-colors">
            Reset
        </a>
    </div>
</form>

@if(!$orgId)
<div class="bg-white rounded-xl shadow-sm py-16 px-5 text-center">
    <div class="text-sm font-semibold text-slate-600 mb-1">Belum ada organisasi</div>
    <div class="text-xs text-slate-400">Tidak ada organisasi yang bisa diakses untuk laporan ini.</div>
</div>
@else

@php
    $balanced = $totals && abs($totals->closing_debit - $totals->closing_credit) < 0.5;
@endphp

{{-- Ringkasan --}}
<div class="grid grid-cols-2 sm:grid-cols-3 gap-3 mb-4">
    <div class="bg-white rounded-xl shadow-sm px-4 py-3.5">
        <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-0.5">Total Saldo Akhir Debit</div>
        <div class="text-base font-extrabold text-blue-600 font-mono leading-tight mt-1">Rp {{ number_format($totals->closing_debit, 0, ',', '.') }}</div>
    </div>
    <div class="bg-white rounded-xl shadow-sm px-4 py-3.5">
        <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-0.5">Total Saldo Akhir Kredit</div>
        <div class="text-base font-extrabold text-green-600 font-mono leading-tight mt-1">Rp {{ number_format($totals->closing_credit, 0, ',', '.') }}</div>
    </div>
    <div class="col-span-2 sm:col-span-1 rounded-xl shadow-sm px-4 py-3.5 flex items-center gap-3 {{ $balanced ? 'bg-emerald-50 border border-emerald-100' : 'bg-red-50 border border-red-100' }}">
        @if($balanced)
        <svg width="24" height="24" fill="none" stroke="#059669" stroke-width="2" viewBox="0 0 24 24" class="shrink-0"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
        <div>
            <div class="text-xs font-bold text-emerald-700">Balance</div>
            <div class="text-[11px] text-emerald-600">Total debit = total kredit</div>
        </div>
        @else
        <svg width="24" height="24" fill="none" stroke="#dc2626" stroke-width="2" viewBox="0 0 24 24" class="shrink-0"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m0 3.75h.008v.008H12v-.008ZM21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
        <div>
            <div class="text-xs font-bold text-red-700">Tidak Balance</div>
            <div class="text-[11px] text-red-600">Selisih Rp {{ number_format(abs($totals->closing_debit - $totals->closing_credit), 0, ',', '.') }}</div>
        </div>
        @endif
    </div>
</div>

{{-- Tabel neraca saldo --}}
<div class="bg-white rounded-xl shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-[11px] text-slate-400 uppercase tracking-wider bg-slate-50/70 border-b border-slate-100">
                    <th rowspan="2" class="py-2.5 px-4 font-semibold whitespace-nowrap align-bottom">Kode</th>
                    <th rowspan="2" class="py-2.5 px-3 font-semibold min-w-[200px] align-bottom">Nama Akun</th>
                    <th colspan="2" class="py-1.5 px-3 font-semibold text-center border-l border-slate-100">Saldo Awal</th>
                    <th colspan="2" class="py-1.5 px-3 font-semibold text-center border-l border-slate-100">Mutasi</th>
                    <th colspan="2" class="py-1.5 px-3 font-semibold text-center border-l border-slate-100">Saldo Akhir</th>
                </tr>
                <tr class="text-left text-[11px] text-slate-400 uppercase tracking-wider bg-slate-50/70 border-b border-slate-100">
                    <th class="py-1.5 px-3 font-semibold text-right border-l border-slate-100">Debit</th>
                    <th class="py-1.5 px-3 font-semibold text-right">Kredit</th>
                    <th class="py-1.5 px-3 font-semibold text-right border-l border-slate-100">Debit</th>
                    <th class="py-1.5 px-3 font-semibold text-right">Kredit</th>
                    <th class="py-1.5 px-3 font-semibold text-right border-l border-slate-100">Debit</th>
                    <th class="py-1.5 px-4 font-semibold text-right">Kredit</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rows as $row)
                <tr class="border-b border-slate-50 last:border-0 hover:bg-slate-50/50 transition-colors">
                    <td class="py-2.5 px-4 font-mono text-[11px] text-slate-400 whitespace-nowrap">{{ $row->account->code }}</td>
                    <td class="py-2.5 px-3 text-slate-700">{{ $row->account->name }}</td>
                    <td class="py-2.5 px-3 text-right font-mono text-slate-600 whitespace-nowrap border-l border-slate-50">{{ $row->opening_debit > 0 ? number_format($row->opening_debit, 0, ',', '.') : '-' }}</td>
                    <td class="py-2.5 px-3 text-right font-mono text-slate-600 whitespace-nowrap">{{ $row->opening_credit > 0 ? number_format($row->opening_credit, 0, ',', '.') : '-' }}</td>
                    <td class="py-2.5 px-3 text-right font-mono text-blue-700 whitespace-nowrap border-l border-slate-50">{{ $row->mutation_debit > 0 ? number_format($row->mutation_debit, 0, ',', '.') : '-' }}</td>
                    <td class="py-2.5 px-3 text-right font-mono text-green-700 whitespace-nowrap">{{ $row->mutation_credit > 0 ? number_format($row->mutation_credit, 0, ',', '.') : '-' }}</td>
                    <td class="py-2.5 px-3 text-right font-mono font-semibold text-slate-800 whitespace-nowrap border-l border-slate-50">{{ $row->closing_debit > 0 ? number_format($row->closing_debit, 0, ',', '.') : '-' }}</td>
                    <td class="py-2.5 px-4 text-right font-mono font-semibold text-slate-800 whitespace-nowrap">{{ $row->closing_credit > 0 ? number_format($row->closing_credit, 0, ',', '.') : '-' }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="py-6 px-4 text-center text-[12px] text-slate-400 italic">Tidak ada akun dengan mutasi pada periode ini.</td>
                </tr>
                @endforelse
            </tbody>
            @if($totals)
            <tfoot>
                <tr class="bg-slate-50/70 border-t border-slate-200">
                    <td colspan="2" class="py-2.5 px-4 text-[11px] font-bold text-slate-500 uppercase tracking-widest">Total</td>
                    <td class="py-2.5 px-3 text-right font-mono font-bold text-slate-700 whitespace-nowrap border-l border-slate-100">Rp {{ number_format($totals->opening_debit, 0, ',', '.') }}</td>
                    <td class="py-2.5 px-3 text-right font-mono font-bold text-slate-700 whitespace-nowrap">Rp {{ number_format($totals->opening_credit, 0, ',', '.') }}</td>
                    <td class="py-2.5 px-3 text-right font-mono font-bold text-blue-700 whitespace-nowrap border-l border-slate-100">Rp {{ number_format($totals->mutation_debit, 0, ',', '.') }}</td>
                    <td class="py-2.5 px-3 text-right font-mono font-bold text-green-700 whitespace-nowrap">Rp {{ number_format($totals->mutation_credit, 0, ',', '.') }}</td>
                    <td class="py-2.5 px-3 text-right font-mono font-bold whitespace-nowrap border-l border-slate-100 {{ $balanced ? 'text-slate-900' : 'text-red-600' }}">Rp {{ number_format($totals->closing_debit, 0, ',', '.') }}</td>
                    <td class="py-2.5 px-4 text-right font-mono font-bold whitespace-nowrap {{ $balanced ? 'text-slate-900' : 'text-red-600' }}">Rp {{ number_format($totals->closing_credit, 0, ',', '.') }}</td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>
</div>

<div class="text-[11px] text-slate-400 mt-3">
    Saldo Awal dihitung dari seluruh jurnal <em>posted</em> sebelum tanggal mulai (termasuk jurnal Saldo Awal 1 Januari). Mutasi dihitung dari jurnal <em>posted</em> pada rentang tanggal yang dipilih. Akun dengan saldo nol pada seluruh kolom disembunyikan kecuali dicentang "Tampilkan akun bersaldo nol".
</div>

@endif

</x-layouts.app>
