<x-layouts.app title="Neraca">

{{-- Header --}}
<div class="flex items-start justify-between gap-4 mb-5 flex-wrap">
    <div>
        <h2 class="text-lg font-bold text-slate-900 m-0 mb-1">Neraca (Laporan Posisi Keuangan)</h2>
        <p class="text-xs text-slate-400 m-0">
            Saldo aset, kewajiban, dan ekuitas dari jurnal yang sudah diposting
            @if($asOf)
                — per tanggal {{ \Carbon\Carbon::parse($asOf)->translatedFormat('d M Y') }}
            @endif
        </p>
    </div>
</div>

{{-- Filter Bar --}}
<form method="GET" action="{{ route('reports.balance-sheet') }}" class="bg-white rounded-xl shadow-sm p-4 mb-4 flex flex-wrap gap-3 items-end">
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
        <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block mb-1.5">Per Tanggal</label>
        <input type="date" name="as_of" value="{{ $asOf }}"
            class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm text-slate-700 bg-white outline-none focus:border-blue-400 transition-colors">
    </div>

    <div class="flex gap-2">
        <button type="submit" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-sm font-semibold bg-blue-600 text-white border-0 cursor-pointer hover:bg-blue-700 transition-colors">
            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
            Tampilkan
        </button>
        <a href="{{ route('reports.balance-sheet') }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-sm font-medium bg-slate-100 text-slate-600 no-underline hover:bg-slate-200 transition-colors">
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

{{-- Ringkasan --}}
<div class="grid grid-cols-2 sm:grid-cols-3 gap-3 mb-4">
    <div class="bg-white rounded-xl shadow-sm px-4 py-3.5">
        <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-0.5">Total Aset</div>
        <div class="text-base font-extrabold text-blue-600 font-mono leading-tight mt-1">Rp {{ number_format($sections->totalAset, 0, ',', '.') }}</div>
    </div>
    <div class="bg-white rounded-xl shadow-sm px-4 py-3.5">
        <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-0.5">Kewajiban + Ekuitas</div>
        <div class="text-base font-extrabold text-purple-600 font-mono leading-tight mt-1">Rp {{ number_format($sections->totalKewajiban + $sections->totalEkuitas, 0, ',', '.') }}</div>
    </div>
    <div class="col-span-2 sm:col-span-1 rounded-xl shadow-sm px-4 py-3.5 flex items-center gap-3 {{ $isBalanced ? 'bg-emerald-50 border border-emerald-100' : 'bg-red-50 border border-red-100' }}">
        @if($isBalanced)
        <svg width="24" height="24" fill="none" stroke="#059669" stroke-width="2" viewBox="0 0 24 24" class="shrink-0"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
        <div>
            <div class="text-xs font-bold text-emerald-700">Balance</div>
            <div class="text-[11px] text-emerald-600">Aset = Kewajiban + Ekuitas</div>
        </div>
        @else
        <svg width="24" height="24" fill="none" stroke="#dc2626" stroke-width="2" viewBox="0 0 24 24" class="shrink-0"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m0 3.75h.008v.008H12v-.008ZM21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
        <div>
            <div class="text-xs font-bold text-red-700">Tidak Balance</div>
            <div class="text-[11px] text-red-600">Selisih Rp {{ number_format(abs($sections->totalAset - ($sections->totalKewajiban + $sections->totalEkuitas)), 0, ',', '.') }}</div>
        </div>
        @endif
    </div>
</div>

<div class="grid lg:grid-cols-2 gap-4">
    {{-- Aset --}}
    <div class="bg-white rounded-xl shadow-sm overflow-hidden self-start">
        <div class="px-5 py-3 bg-slate-50 border-b border-slate-100">
            <span class="text-xs font-bold text-slate-500 uppercase tracking-wide">Aset</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <tbody>
                    @forelse($sections->aset as $row)
                    <tr class="border-b border-slate-50 last:border-0 hover:bg-slate-50/50 transition-colors">
                        <td class="py-2.5 px-4 font-mono text-[11px] text-slate-400 whitespace-nowrap w-24">{{ $row->account->code }}</td>
                        <td class="py-2.5 px-3 text-slate-700">{{ $row->account->name }}</td>
                        <td class="py-2.5 px-4 text-right font-mono text-slate-800 whitespace-nowrap">Rp {{ number_format($row->amount, 0, ',', '.') }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="3" class="py-5 px-4 text-center text-[12px] text-slate-400 italic">Tidak ada saldo aset.</td></tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr class="bg-slate-50/70 border-t border-slate-200">
                        <td colspan="2" class="py-2.5 px-4 text-[11px] font-bold text-slate-500 uppercase tracking-widest">Total Aset</td>
                        <td class="py-2.5 px-4 text-right font-mono font-bold text-blue-700 whitespace-nowrap">Rp {{ number_format($sections->totalAset, 0, ',', '.') }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    {{-- Kewajiban + Ekuitas --}}
    <div class="flex flex-col gap-4">
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="px-5 py-3 bg-slate-50 border-b border-slate-100">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wide">Kewajiban</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <tbody>
                        @forelse($sections->kewajiban as $row)
                        <tr class="border-b border-slate-50 last:border-0 hover:bg-slate-50/50 transition-colors">
                            <td class="py-2.5 px-4 font-mono text-[11px] text-slate-400 whitespace-nowrap w-24">{{ $row->account->code }}</td>
                            <td class="py-2.5 px-3 text-slate-700">{{ $row->account->name }}</td>
                            <td class="py-2.5 px-4 text-right font-mono text-slate-800 whitespace-nowrap">Rp {{ number_format($row->amount, 0, ',', '.') }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="3" class="py-5 px-4 text-center text-[12px] text-slate-400 italic">Tidak ada saldo kewajiban.</td></tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr class="bg-slate-50/70 border-t border-slate-200">
                            <td colspan="2" class="py-2.5 px-4 text-[11px] font-bold text-slate-500 uppercase tracking-widest">Total Kewajiban</td>
                            <td class="py-2.5 px-4 text-right font-mono font-bold text-red-700 whitespace-nowrap">Rp {{ number_format($sections->totalKewajiban, 0, ',', '.') }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="px-5 py-3 bg-slate-50 border-b border-slate-100">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wide">Ekuitas</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <tbody>
                        @forelse($sections->ekuitas as $row)
                        <tr class="border-b border-slate-50 last:border-0 hover:bg-slate-50/50 transition-colors">
                            <td class="py-2.5 px-4 font-mono text-[11px] text-slate-400 whitespace-nowrap w-24">{{ $row->account->code }}</td>
                            <td class="py-2.5 px-3 text-slate-700">{{ $row->account->name }}</td>
                            <td class="py-2.5 px-4 text-right font-mono text-slate-800 whitespace-nowrap">Rp {{ number_format($row->amount, 0, ',', '.') }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="3" class="py-4 px-4 text-center text-[12px] text-slate-400 italic">Tidak ada saldo ekuitas.</td></tr>
                        @endforelse
                        <tr class="border-b border-slate-50 last:border-0 hover:bg-slate-50/50 transition-colors bg-amber-50/40">
                            <td class="py-2.5 px-4 font-mono text-[11px] text-slate-400 whitespace-nowrap w-24">—</td>
                            <td class="py-2.5 px-3 text-slate-700">
                                Laba (Rugi) Berjalan
                                <span class="block text-[10px] text-slate-400 mt-0.5">Akumulasi pendapatan − beban s.d. tanggal ini (belum ada proses tutup buku)</span>
                            </td>
                            <td class="py-2.5 px-4 text-right font-mono text-slate-800 whitespace-nowrap">Rp {{ number_format($sections->labaBerjalan, 0, ',', '.') }}</td>
                        </tr>
                    </tbody>
                    <tfoot>
                        <tr class="bg-slate-50/70 border-t border-slate-200">
                            <td colspan="2" class="py-2.5 px-4 text-[11px] font-bold text-slate-500 uppercase tracking-widest">Total Ekuitas</td>
                            <td class="py-2.5 px-4 text-right font-mono font-bold text-purple-700 whitespace-nowrap">Rp {{ number_format($sections->totalEkuitas, 0, ',', '.') }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="text-[11px] text-slate-400 mt-3">
    Saldo dihitung kumulatif dari seluruh jurnal <em>posted</em> sejak awal s.d. tanggal yang dipilih. Akun bersaldo nol disembunyikan.
</div>

@endif

</x-layouts.app>
