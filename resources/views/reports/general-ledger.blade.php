<x-layouts.app title="Buku Besar">

{{-- Header --}}
<div class="flex items-start justify-between gap-4 mb-5 flex-wrap">
    <div>
        <h2 class="text-lg font-bold text-slate-900 m-0 mb-1">Buku Besar</h2>
        <p class="text-xs text-slate-400 m-0">
            Riwayat mutasi debit/kredit per akun dari jurnal yang sudah diposting
            @if($account && ($dateFrom || $dateTo))
                — periode
                {{ $dateFrom ? \Carbon\Carbon::parse($dateFrom)->translatedFormat('d M Y') : '…' }}
                s.d.
                {{ $dateTo ? \Carbon\Carbon::parse($dateTo)->translatedFormat('d M Y') : '…' }}
            @endif
        </p>
    </div>
</div>

{{-- Filter Bar --}}
<form method="GET" action="{{ route('reports.general-ledger') }}" class="bg-white rounded-xl shadow-sm p-4 mb-4 flex flex-wrap gap-3 items-end">
    @if($organizations->count() > 1)
    <div class="min-w-[180px]">
        <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block mb-1.5">Organisasi</label>
        {{-- Ganti organisasi memuat ulang daftar akun --}}
        <select name="organization_id" onchange="this.form.account_id.value=''; this.form.submit()"
            class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm text-slate-700 bg-white outline-none focus:border-blue-400 transition-colors">
            @foreach($organizations as $org)
                <option value="{{ $org->id }}" {{ $orgId === $org->id ? 'selected' : '' }}>{{ $org->name }}</option>
            @endforeach
        </select>
    </div>
    @else
    <input type="hidden" name="organization_id" value="{{ $orgId }}">
    @endif

    <div class="flex-1 min-w-[260px]">
        <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block mb-1.5">Akun</label>
        <select name="account_id" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm text-slate-700 bg-white outline-none focus:border-blue-400 transition-colors">
            <option value="">— Pilih akun —</option>
            @foreach($accounts as $acc)
                <option value="{{ $acc->id }}" {{ $account && $account->id === $acc->id ? 'selected' : '' }}>
                    {{ $acc->code }} — {{ $acc->name }}{{ $acc->is_active ? '' : ' (nonaktif)' }}
                </option>
            @endforeach
        </select>
    </div>

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
        <a href="{{ route('reports.general-ledger') }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-sm font-medium bg-slate-100 text-slate-600 no-underline hover:bg-slate-200 transition-colors">
            Reset
        </a>
    </div>
</form>

@if(!$account)
<div class="bg-white rounded-xl shadow-sm py-16 px-5 text-center">
    <div class="w-16 h-16 rounded-2xl bg-slate-50 border border-slate-100 flex items-center justify-center mx-auto mb-4">
        <svg width="28" height="28" fill="none" stroke="#94a3b8" stroke-width="1.5" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
        </svg>
    </div>
    <div class="text-sm font-semibold text-slate-600 mb-1">Pilih akun terlebih dahulu</div>
    <div class="text-xs text-slate-400">Pilih akun pada filter di atas lalu klik <strong>Tampilkan</strong> untuk melihat buku besar.</div>
</div>
@else

@php
    $typeInfo = \App\Models\Account::TYPES[$account->account_type] ?? null;
    $isCredit = $account->normal_balance === 'kredit';
@endphp

{{-- Info akun + ringkasan --}}
<div class="grid grid-cols-2 sm:grid-cols-5 gap-3 mb-4">
    <div class="bg-white rounded-xl shadow-sm px-4 py-3.5 col-span-2 sm:col-span-1">
        <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-0.5">Akun</div>
        <div class="font-mono text-[11px] text-slate-400">{{ $account->code }}</div>
        <div class="text-sm font-bold text-slate-800 leading-tight">{{ $account->name }}</div>
        <div class="text-[11px] text-slate-400 mt-0.5">
            @if($typeInfo)<span style="color: {{ $typeInfo['color'] }}">{{ $typeInfo['label'] }}</span> · @endif
            saldo normal {{ $isCredit ? 'kredit' : 'debit' }}
        </div>
    </div>
    <div class="bg-white rounded-xl shadow-sm px-4 py-3.5">
        <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-0.5">Saldo Awal</div>
        <div class="text-base font-extrabold text-slate-800 font-mono leading-tight mt-1 {{ $opening < 0 ? 'text-red-600' : '' }}">Rp {{ number_format($opening, 0, ',', '.') }}</div>
        @if($dateFrom)
        <div class="text-xs text-slate-400 mt-0.5">per {{ \Carbon\Carbon::parse($dateFrom)->translatedFormat('d M Y') }}</div>
        @endif
    </div>
    <div class="bg-white rounded-xl shadow-sm px-4 py-3.5">
        <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-0.5">Total Debit</div>
        <div class="text-base font-extrabold text-blue-600 font-mono leading-tight mt-1">Rp {{ number_format($totals->debit, 0, ',', '.') }}</div>
    </div>
    <div class="bg-white rounded-xl shadow-sm px-4 py-3.5">
        <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-0.5">Total Kredit</div>
        <div class="text-base font-extrabold text-green-600 font-mono leading-tight mt-1">Rp {{ number_format($totals->credit, 0, ',', '.') }}</div>
    </div>
    <div class="bg-white rounded-xl shadow-sm px-4 py-3.5">
        <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-0.5">Saldo Akhir</div>
        <div class="text-base font-extrabold font-mono leading-tight mt-1 {{ $totals->closing < 0 ? 'text-red-600' : 'text-slate-800' }}">Rp {{ number_format($totals->closing, 0, ',', '.') }}</div>
        <div class="text-xs text-slate-400 mt-0.5">{{ $lines->count() }} mutasi</div>
    </div>
</div>

{{-- Tabel mutasi --}}
<div class="bg-white rounded-xl shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-[11px] text-slate-400 uppercase tracking-wider bg-slate-50/70 border-b border-slate-100">
                    <th class="py-2.5 px-4 font-semibold whitespace-nowrap">Tanggal</th>
                    <th class="py-2.5 px-3 font-semibold whitespace-nowrap">No. Jurnal</th>
                    <th class="py-2.5 px-3 font-semibold min-w-[220px]">Keterangan</th>
                    <th class="py-2.5 px-3 font-semibold text-right">Debit</th>
                    <th class="py-2.5 px-3 font-semibold text-right">Kredit</th>
                    <th class="py-2.5 px-4 font-semibold text-right">Saldo</th>
                </tr>
            </thead>
            <tbody>
                <tr class="bg-slate-50/50 border-b border-slate-100">
                    <td class="py-2.5 px-4 text-slate-500 whitespace-nowrap" colspan="3">
                        <span class="font-semibold text-slate-600">Saldo Awal</span>
                        @if($dateFrom)<span class="text-[11px] text-slate-400"> (mutasi sebelum {{ \Carbon\Carbon::parse($dateFrom)->translatedFormat('d M Y') }})</span>@endif
                    </td>
                    <td class="py-2.5 px-3"></td>
                    <td class="py-2.5 px-3"></td>
                    <td class="py-2.5 px-4 text-right font-mono font-semibold {{ $opening < 0 ? 'text-red-600' : 'text-slate-700' }}">Rp {{ number_format($opening, 0, ',', '.') }}</td>
                </tr>

                @forelse($lines as $line)
                <tr class="border-b border-slate-50 last:border-0 hover:bg-slate-50/50 transition-colors">
                    <td class="py-2.5 px-4 text-slate-500 whitespace-nowrap">{{ $line->journalEntry->entry_date->translatedFormat('d M Y') }}</td>
                    <td class="py-2.5 px-3 whitespace-nowrap">
                        <a href="{{ route('journal-entries.show', $line->journal_entry_id) }}" class="font-mono text-[11px] text-blue-600 no-underline hover:underline">{{ $line->journalEntry->reference }}</a>
                    </td>
                    <td class="py-2.5 px-3 text-slate-600">{{ $line->description ?: $line->journalEntry->description }}</td>
                    <td class="py-2.5 px-3 text-right font-mono text-blue-700 whitespace-nowrap">{{ $line->debit > 0 ? 'Rp ' . number_format($line->debit, 0, ',', '.') : '-' }}</td>
                    <td class="py-2.5 px-3 text-right font-mono text-green-700 whitespace-nowrap">{{ $line->credit > 0 ? 'Rp ' . number_format($line->credit, 0, ',', '.') : '-' }}</td>
                    <td class="py-2.5 px-4 text-right font-mono whitespace-nowrap {{ $line->running_balance < 0 ? 'text-red-600' : 'text-slate-800' }}">Rp {{ number_format($line->running_balance, 0, ',', '.') }}</td>
                </tr>
                @empty
                <tr class="border-b border-slate-50">
                    <td colspan="6" class="py-6 px-4 text-center text-[12px] text-slate-400 italic">Tidak ada mutasi pada periode ini.</td>
                </tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr class="bg-slate-50/70 border-t border-slate-200">
                    <td colspan="3" class="py-2.5 px-4 text-[11px] font-bold text-slate-500 uppercase tracking-widest">Total &amp; Saldo Akhir</td>
                    <td class="py-2.5 px-3 text-right font-mono font-bold text-blue-700 whitespace-nowrap">Rp {{ number_format($totals->debit, 0, ',', '.') }}</td>
                    <td class="py-2.5 px-3 text-right font-mono font-bold text-green-700 whitespace-nowrap">Rp {{ number_format($totals->credit, 0, ',', '.') }}</td>
                    <td class="py-2.5 px-4 text-right font-mono font-bold whitespace-nowrap {{ $totals->closing < 0 ? 'text-red-600' : 'text-slate-900' }}">Rp {{ number_format($totals->closing, 0, ',', '.') }}</td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

<div class="text-[11px] text-slate-400 mt-3">
    Saldo dinyatakan menurut saldo normal akun ({{ $isCredit ? 'kredit − debit' : 'debit − kredit' }}). Hanya jurnal berstatus <em>posted</em> yang dihitung.
</div>

@endif

</x-layouts.app>
