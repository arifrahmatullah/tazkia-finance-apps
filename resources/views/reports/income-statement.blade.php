<x-layouts.app title="Laporan Laba Rugi">

{{-- Header --}}
<div class="flex items-start justify-between gap-4 mb-5 flex-wrap">
    <div>
        <h2 class="text-lg font-bold text-slate-900 m-0 mb-1">Laporan Laba Rugi</h2>
        <p class="text-xs text-slate-400 m-0">
            Pendapatan dan beban dari jurnal yang sudah diposting
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
<form method="GET" action="{{ route('reports.income-statement') }}" class="bg-white rounded-xl shadow-sm p-4 mb-4 flex flex-wrap gap-3 items-end">
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

    <div class="flex gap-2">
        <button type="submit" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-sm font-semibold bg-blue-600 text-white border-0 cursor-pointer hover:bg-blue-700 transition-colors">
            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
            Tampilkan
        </button>
        <a href="{{ route('reports.income-statement') }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-sm font-medium bg-slate-100 text-slate-600 no-underline hover:bg-slate-200 transition-colors">
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
    $laba = $sections->labaBersih >= 0;
@endphp

{{-- Ringkasan --}}
<div class="grid grid-cols-2 sm:grid-cols-3 gap-3 mb-4">
    <div class="bg-white rounded-xl shadow-sm px-4 py-3.5">
        <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-0.5">Total Pendapatan</div>
        <div class="text-base font-extrabold text-green-600 font-mono leading-tight mt-1">Rp {{ number_format($sections->totalPendapatan, 0, ',', '.') }}</div>
    </div>
    <div class="bg-white rounded-xl shadow-sm px-4 py-3.5">
        <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-0.5">Total Beban</div>
        <div class="text-base font-extrabold text-orange-600 font-mono leading-tight mt-1">Rp {{ number_format($sections->totalBeban, 0, ',', '.') }}</div>
    </div>
    <div class="col-span-2 sm:col-span-1 rounded-xl shadow-sm px-4 py-3.5 flex items-center gap-3 {{ $laba ? 'bg-emerald-50 border border-emerald-100' : 'bg-red-50 border border-red-100' }}">
        <svg width="24" height="24" fill="none" stroke="{{ $laba ? '#059669' : '#dc2626' }}" stroke-width="2" viewBox="0 0 24 24" class="shrink-0"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $laba ? 'M3 17l6-6 4 4 8-8' : 'M3 7l6 6 4-4 8 8' }}"/></svg>
        <div>
            <div class="text-xs font-bold {{ $laba ? 'text-emerald-700' : 'text-red-700' }}">{{ $laba ? 'Laba Bersih' : 'Rugi Bersih' }}</div>
            <div class="text-[13px] font-mono font-bold {{ $laba ? 'text-emerald-700' : 'text-red-700' }}">Rp {{ number_format(abs($sections->labaBersih), 0, ',', '.') }}</div>
        </div>
    </div>
</div>

{{-- Pendapatan --}}
<div class="bg-white rounded-xl shadow-sm overflow-hidden mb-4">
    <div class="px-5 py-3 bg-slate-50 border-b border-slate-100">
        <span class="text-xs font-bold text-slate-500 uppercase tracking-wide">Pendapatan</span>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <tbody>
                @forelse($sections->pendapatan as $row)
                <tr class="border-b border-slate-50 last:border-0 hover:bg-slate-50/50 transition-colors">
                    <td class="py-2.5 px-4 font-mono text-[11px] text-slate-400 whitespace-nowrap w-24">{{ $row->account->code }}</td>
                    <td class="py-2.5 px-3 text-slate-700">{{ $row->account->name }}</td>
                    <td class="py-2.5 px-4 text-right font-mono text-slate-800 whitespace-nowrap">Rp {{ number_format($row->amount, 0, ',', '.') }}</td>
                </tr>
                @empty
                <tr><td colspan="3" class="py-5 px-4 text-center text-[12px] text-slate-400 italic">Tidak ada mutasi pendapatan pada periode ini.</td></tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr class="bg-slate-50/70 border-t border-slate-200">
                    <td colspan="2" class="py-2.5 px-4 text-[11px] font-bold text-slate-500 uppercase tracking-widest">Total Pendapatan</td>
                    <td class="py-2.5 px-4 text-right font-mono font-bold text-green-700 whitespace-nowrap">Rp {{ number_format($sections->totalPendapatan, 0, ',', '.') }}</td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

{{-- Beban --}}
<div class="bg-white rounded-xl shadow-sm overflow-hidden mb-4">
    <div class="px-5 py-3 bg-slate-50 border-b border-slate-100">
        <span class="text-xs font-bold text-slate-500 uppercase tracking-wide">Beban</span>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <tbody>
                @forelse($sections->beban as $row)
                <tr class="border-b border-slate-50 last:border-0 hover:bg-slate-50/50 transition-colors">
                    <td class="py-2.5 px-4 font-mono text-[11px] text-slate-400 whitespace-nowrap w-24">{{ $row->account->code }}</td>
                    <td class="py-2.5 px-3 text-slate-700">{{ $row->account->name }}</td>
                    <td class="py-2.5 px-4 text-right font-mono text-slate-800 whitespace-nowrap">Rp {{ number_format($row->amount, 0, ',', '.') }}</td>
                </tr>
                @empty
                <tr><td colspan="3" class="py-5 px-4 text-center text-[12px] text-slate-400 italic">Tidak ada mutasi beban pada periode ini.</td></tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr class="bg-slate-50/70 border-t border-slate-200">
                    <td colspan="2" class="py-2.5 px-4 text-[11px] font-bold text-slate-500 uppercase tracking-widest">Total Beban</td>
                    <td class="py-2.5 px-4 text-right font-mono font-bold text-orange-700 whitespace-nowrap">Rp {{ number_format($sections->totalBeban, 0, ',', '.') }}</td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

{{-- Laba/Rugi Bersih --}}
<div class="rounded-xl shadow-sm overflow-hidden {{ $laba ? 'bg-emerald-50 border border-emerald-100' : 'bg-red-50 border border-red-100' }}">
    <div class="flex items-center justify-between px-5 py-3.5">
        <span class="text-sm font-bold {{ $laba ? 'text-emerald-700' : 'text-red-700' }}">{{ $laba ? 'Laba Bersih' : 'Rugi Bersih' }}</span>
        <span class="text-base font-mono font-extrabold {{ $laba ? 'text-emerald-700' : 'text-red-700' }}">Rp {{ number_format(abs($sections->labaBersih), 0, ',', '.') }}</span>
    </div>
</div>

<div class="text-[11px] text-slate-400 mt-3">
    Dihitung dari mutasi jurnal <em>posted</em> pada rentang tanggal yang dipilih. Akun tanpa mutasi pada periode ini disembunyikan.
</div>

@endif

</x-layouts.app>
