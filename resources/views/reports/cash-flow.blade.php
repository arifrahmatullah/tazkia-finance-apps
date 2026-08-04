<x-layouts.app title="Laporan Arus Kas">

{{-- Header --}}
<div class="flex items-start justify-between gap-4 mb-5 flex-wrap">
    <div>
        <h2 class="text-lg font-bold text-slate-900 m-0 mb-1">Laporan Arus Kas</h2>
        <p class="text-xs text-slate-400 m-0">
            Mutasi kas & bank dari jurnal yang sudah diposting (metode langsung)
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
<form method="GET" action="{{ route('reports.cash-flow') }}" class="bg-white rounded-xl shadow-sm p-4 mb-4 flex flex-wrap gap-3 items-end">
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
        <a href="{{ route('reports.cash-flow') }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-sm font-medium bg-slate-100 text-slate-600 no-underline hover:bg-slate-200 transition-colors">
            Reset
        </a>
    </div>
</form>

@if(!$orgId)
<div class="bg-white rounded-xl shadow-sm py-16 px-5 text-center">
    <div class="text-sm font-semibold text-slate-600 mb-1">Belum ada organisasi</div>
    <div class="text-xs text-slate-400">Tidak ada organisasi yang bisa diakses untuk laporan ini.</div>
</div>
@elseif($noCashAccounts)
<div class="bg-white rounded-xl shadow-sm py-16 px-5 text-center">
    <div class="text-sm font-semibold text-slate-600 mb-1">Belum ada akun kas/bank</div>
    <div class="text-xs text-slate-400">Tambahkan akun kas/bank (kode 1.1.01.xx) pada menu <strong>Bagan Akun</strong> terlebih dahulu.</div>
</div>
@else

{{-- Ringkasan --}}
<div class="grid grid-cols-2 sm:grid-cols-3 gap-3 mb-4">
    <div class="bg-white rounded-xl shadow-sm px-4 py-3.5">
        <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-0.5">Saldo Kas Awal</div>
        <div class="text-base font-extrabold text-slate-700 font-mono leading-tight mt-1">Rp {{ number_format($sections->openingCash, 0, ',', '.') }}</div>
    </div>
    <div class="bg-white rounded-xl shadow-sm px-4 py-3.5">
        <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-0.5">{{ $sections->kenaikanBersih >= 0 ? 'Kenaikan' : 'Penurunan' }} Kas Bersih</div>
        <div class="text-base font-extrabold {{ $sections->kenaikanBersih >= 0 ? 'text-green-600' : 'text-red-600' }} font-mono leading-tight mt-1">Rp {{ number_format(abs($sections->kenaikanBersih), 0, ',', '.') }}</div>
    </div>
    <div class="col-span-2 sm:col-span-1 rounded-xl shadow-sm px-4 py-3.5 flex items-center gap-3 {{ $sections->isReconciled ? 'bg-emerald-50 border border-emerald-100' : 'bg-red-50 border border-red-100' }}">
        @if($sections->isReconciled)
        <svg width="24" height="24" fill="none" stroke="#059669" stroke-width="2" viewBox="0 0 24 24" class="shrink-0"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
        <div>
            <div class="text-xs font-bold text-emerald-700">Terverifikasi</div>
            <div class="text-[11px] text-emerald-600">Sesuai saldo kas aktual</div>
        </div>
        @else
        <svg width="24" height="24" fill="none" stroke="#dc2626" stroke-width="2" viewBox="0 0 24 24" class="shrink-0"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m0 3.75h.008v.008H12v-.008ZM21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
        <div>
            <div class="text-xs font-bold text-red-700">Tidak Cocok</div>
            <div class="text-[11px] text-red-600">Selisih Rp {{ number_format(abs($sections->closingCash - $sections->actualClosing), 0, ',', '.') }}</div>
        </div>
        @endif
    </div>
</div>

@php
    $activitySections = [
        ['key' => 'operasi', 'label' => 'Aktivitas Operasi', 'rows' => $sections->operasi, 'total' => $sections->totalOperasi],
        ['key' => 'investasi', 'label' => 'Aktivitas Investasi', 'rows' => $sections->investasi, 'total' => $sections->totalInvestasi],
        ['key' => 'pendanaan', 'label' => 'Aktivitas Pendanaan', 'rows' => $sections->pendanaan, 'total' => $sections->totalPendanaan],
    ];
@endphp

@foreach($activitySections as $sec)
<div class="bg-white rounded-xl shadow-sm overflow-hidden mb-4">
    <div class="px-5 py-3 bg-slate-50 border-b border-slate-100">
        <span class="text-xs font-bold text-slate-500 uppercase tracking-wide">{{ $sec['label'] }}</span>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <tbody>
                @forelse($sec['rows'] as $row)
                <tr class="border-b border-slate-50 last:border-0 hover:bg-slate-50/50 transition-colors">
                    <td class="py-2.5 px-4 font-mono text-[11px] text-slate-400 whitespace-nowrap w-24">{{ $row->account->code }}</td>
                    <td class="py-2.5 px-3 text-slate-700">{{ $row->account->name }}</td>
                    <td class="py-2.5 px-4 text-right font-mono whitespace-nowrap {{ $row->amount >= 0 ? 'text-green-700' : 'text-red-700' }}">
                        {{ $row->amount >= 0 ? '' : '-' }}Rp {{ number_format(abs($row->amount), 0, ',', '.') }}
                    </td>
                </tr>
                @empty
                <tr><td colspan="3" class="py-5 px-4 text-center text-[12px] text-slate-400 italic">Tidak ada arus kas dari {{ strtolower($sec['label']) }} pada periode ini.</td></tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr class="bg-slate-50/70 border-t border-slate-200">
                    <td colspan="2" class="py-2.5 px-4 text-[11px] font-bold text-slate-500 uppercase tracking-widest">Kas Bersih dari {{ $sec['label'] }}</td>
                    <td class="py-2.5 px-4 text-right font-mono font-bold whitespace-nowrap {{ $sec['total'] >= 0 ? 'text-green-700' : 'text-red-700' }}">
                        {{ $sec['total'] >= 0 ? '' : '-' }}Rp {{ number_format(abs($sec['total']), 0, ',', '.') }}
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
@endforeach

{{-- Rekonsiliasi kas --}}
<div class="bg-white rounded-xl shadow-sm overflow-hidden">
    <div class="px-5 py-3 bg-slate-50 border-b border-slate-100">
        <span class="text-xs font-bold text-slate-500 uppercase tracking-wide">Rekonsiliasi Saldo Kas</span>
    </div>
    <div class="divide-y divide-slate-50 text-sm">
        <div class="flex items-center justify-between px-5 py-2.5">
            <span class="text-slate-600">Saldo Kas Awal Periode</span>
            <span class="font-mono text-slate-800">Rp {{ number_format($sections->openingCash, 0, ',', '.') }}</span>
        </div>
        <div class="flex items-center justify-between px-5 py-2.5">
            <span class="text-slate-600">{{ $sections->kenaikanBersih >= 0 ? 'Kenaikan' : 'Penurunan' }} Kas Bersih</span>
            <span class="font-mono {{ $sections->kenaikanBersih >= 0 ? 'text-green-700' : 'text-red-700' }}">{{ $sections->kenaikanBersih >= 0 ? '' : '-' }}Rp {{ number_format(abs($sections->kenaikanBersih), 0, ',', '.') }}</span>
        </div>
        <div class="flex items-center justify-between px-5 py-3 bg-slate-50/70">
            <span class="font-bold text-slate-700">Saldo Kas Akhir Periode (dihitung)</span>
            <span class="font-mono font-bold text-slate-900">Rp {{ number_format($sections->closingCash, 0, ',', '.') }}</span>
        </div>
        <div class="flex items-center justify-between px-5 py-2.5">
            <span class="text-slate-600">Saldo Kas Aktual per tanggal akhir (dari buku besar)</span>
            <span class="font-mono {{ $sections->isReconciled ? 'text-slate-800' : 'text-red-600 font-bold' }}">Rp {{ number_format($sections->actualClosing, 0, ',', '.') }}</span>
        </div>
    </div>
</div>

<div class="text-[11px] text-slate-400 mt-3">
    Diklasifikasi otomatis dari akun lawan tiap jurnal: <strong>Pendanaan</strong> = ekuitas & kewajiban jangka panjang (kode 2.2.x),
    <strong>Investasi</strong> = aset tetap/tidak berwujud & investasi (kode 1.2.x, 1.1.05.x, 1.1.06.x), sisanya <strong>Operasi</strong>.
    Transfer antar rekening kas/bank sendiri tidak dihitung sebagai arus kas.
</div>

@endif

</x-layouts.app>
