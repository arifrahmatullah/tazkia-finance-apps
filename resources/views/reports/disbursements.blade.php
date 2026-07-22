<x-layouts.app title="Laporan Pencairan Dana">

{{-- Header --}}
<div class="flex items-start justify-between gap-4 mb-5 flex-wrap">
    <div>
        <h2 class="text-lg font-bold text-slate-900 m-0 mb-1">Laporan Pencairan Dana</h2>
        <p class="text-xs text-slate-400 m-0">
            Rekap dana yang telah dicairkan
            @if($dateFrom || $dateTo)
                periode
                {{ $dateFrom ? \Carbon\Carbon::parse($dateFrom)->translatedFormat('d M Y') : '…' }}
                —
                {{ $dateTo ? \Carbon\Carbon::parse($dateTo)->translatedFormat('d M Y') : '…' }}
            @endif
        </p>
    </div>
</div>

{{-- Filter Bar --}}
<form method="GET" action="{{ route('reports.disbursements') }}" class="bg-white rounded-xl shadow-sm p-4 mb-4 flex flex-wrap gap-3 items-end">
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

    @if($organizations->count() > 1)
    <div class="min-w-[180px]">
        <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block mb-1.5">Organisasi</label>
        <select name="organization_id" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm text-slate-700 bg-white outline-none focus:border-blue-400 transition-colors">
            <option value="">Semua Organisasi</option>
            @foreach($organizations as $org)
                <option value="{{ $org->id }}" {{ request('organization_id') == $org->id ? 'selected' : '' }}>{{ $org->name }}</option>
            @endforeach
        </select>
    </div>
    @endif

    <div class="min-w-[200px]">
        <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block mb-1.5">Rekening Sumber</label>
        <select name="account_id" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm text-slate-700 bg-white outline-none focus:border-blue-400 transition-colors">
            <option value="">Semua Rekening</option>
            @foreach($bankAccounts as $acc)
                <option value="{{ $acc->id }}" {{ request('account_id') == $acc->id ? 'selected' : '' }}>{{ $acc->code }} — {{ $acc->name }}</option>
            @endforeach
        </select>
    </div>

    <div class="min-w-[140px]">
        <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block mb-1.5">Bukti Transfer</label>
        <select name="proof" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm text-slate-700 bg-white outline-none focus:border-blue-400 transition-colors">
            <option value="">Semua</option>
            <option value="ada" {{ request('proof') === 'ada' ? 'selected' : '' }}>Sudah Ada</option>
            <option value="belum" {{ request('proof') === 'belum' ? 'selected' : '' }}>Belum Ada</option>
        </select>
    </div>

    <div class="flex-1 min-w-[180px]">
        <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block mb-1.5">Cari</label>
        <input type="text" name="search" value="{{ request('search') }}"
            placeholder="Referensi, judul, atau nama pengaju..."
            class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm outline-none focus:border-blue-400 transition-colors">
    </div>

    <div class="flex gap-2">
        <button type="submit" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-sm font-semibold bg-blue-600 text-white border-0 cursor-pointer hover:bg-blue-700 transition-colors">
            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
            Terapkan
        </button>
        <a href="{{ route('reports.disbursements') }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-sm font-medium bg-slate-100 text-slate-600 no-underline hover:bg-slate-200 transition-colors">
            Reset
        </a>
    </div>
</form>

{{-- Summary --}}
<div class="grid grid-cols-2 sm:grid-cols-3 gap-3 mb-4">
    <div class="bg-white rounded-xl shadow-sm px-4 py-3.5">
        <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-0.5">Jumlah Pencairan</div>
        <div class="text-2xl font-extrabold text-slate-800">{{ $summary->total_count }}</div>
    </div>
    <div class="bg-white rounded-xl shadow-sm px-4 py-3.5">
        <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-0.5">Total Dana Dicairkan</div>
        <div class="text-lg font-extrabold text-green-600 font-mono leading-tight mt-1">Rp {{ number_format($summary->total_amount, 0, ',', '.') }}</div>
    </div>
    <div class="rounded-xl shadow-sm px-4 py-3.5 col-span-2 sm:col-span-1 {{ $missingProofCount > 0 ? 'bg-red-50 border border-red-200' : 'bg-white' }}">
        <div class="text-[10px] font-bold {{ $missingProofCount > 0 ? 'text-red-400' : 'text-slate-400' }} uppercase tracking-widest mb-0.5">Belum Ada Bukti</div>
        <div class="text-2xl font-extrabold {{ $missingProofCount > 0 ? 'text-red-500' : 'text-slate-300' }}">{{ $missingProofCount }}</div>
        <div class="text-xs {{ $missingProofCount > 0 ? 'text-red-400' : 'text-slate-400' }} mt-0.5">{{ $missingProofCount > 0 ? 'pencairan tanpa bukti transfer' : 'semua bukti lengkap' }}</div>
    </div>
</div>

{{-- Rekap per organisasi & per rekening --}}
@if($perOrg->count() > 1 || $perAccount->count() > 1)
<div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-4">
    @if($perOrg->count() > 1)
    <div class="bg-white rounded-xl shadow-sm p-4">
        <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2.5">Rekap per Organisasi</div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-[11px] text-slate-400 uppercase tracking-wider border-b border-slate-100">
                        <th class="py-2 pr-3 font-semibold">Organisasi</th>
                        <th class="py-2 px-3 font-semibold text-center">Jumlah</th>
                        <th class="py-2 pl-3 font-semibold text-right">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($perOrg as $row)
                    <tr class="border-b border-slate-50 last:border-0">
                        <td class="py-2 pr-3 text-slate-700">{{ $row->name }}</td>
                        <td class="py-2 px-3 text-center text-slate-600">{{ $row->jumlah }}</td>
                        <td class="py-2 pl-3 text-right font-mono text-slate-800">Rp {{ number_format($row->total, 0, ',', '.') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    @if($perAccount->count() > 1)
    <div class="bg-white rounded-xl shadow-sm p-4">
        <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2.5">Rekap per Rekening Sumber</div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-[11px] text-slate-400 uppercase tracking-wider border-b border-slate-100">
                        <th class="py-2 pr-3 font-semibold">Rekening</th>
                        <th class="py-2 px-3 font-semibold text-center">Jumlah</th>
                        <th class="py-2 pl-3 font-semibold text-right">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($perAccount as $row)
                    @php $acc = $row->disburse_account_id ? ($accountNames[$row->disburse_account_id] ?? null) : null; @endphp
                    <tr class="border-b border-slate-50 last:border-0">
                        <td class="py-2 pr-3 text-slate-700">
                            @if($acc)
                                <span class="font-mono text-[11px] text-slate-400">{{ $acc->code }}</span> {{ $acc->name }}
                            @else
                                <span class="text-slate-400">Tidak tercatat</span>
                            @endif
                        </td>
                        <td class="py-2 px-3 text-center text-slate-600">{{ $row->jumlah }}</td>
                        <td class="py-2 pl-3 text-right font-mono text-slate-800">Rp {{ number_format($row->total, 0, ',', '.') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
</div>
@endif

{{-- Tabel utama --}}
@if($fundRequests->isEmpty())
<div class="bg-white rounded-xl shadow-sm py-16 px-5 text-center">
    <div class="w-16 h-16 rounded-2xl bg-slate-50 border border-slate-100 flex items-center justify-center mx-auto mb-4">
        <svg width="28" height="28" fill="none" stroke="#94a3b8" stroke-width="1.5" viewBox="0 0 24 24">
            <rect x="1" y="4" width="22" height="16" rx="2" ry="2"/><line x1="1" y1="10" x2="23" y2="10"/>
        </svg>
    </div>
    <div class="text-sm font-semibold text-slate-600 mb-1">Tidak ada data</div>
    <div class="text-xs text-slate-400">Tidak ada pencairan dana pada periode/filter yang dipilih.</div>
</div>
@else
<div class="bg-white rounded-xl shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-[11px] text-slate-400 uppercase tracking-wider bg-slate-50/70 border-b border-slate-100">
                    <th class="py-2.5 px-4 font-semibold">Tanggal Cair</th>
                    <th class="py-2.5 px-3 font-semibold">Referensi / Judul</th>
                    <th class="py-2.5 px-3 font-semibold">Pengaju</th>
                    <th class="py-2.5 px-3 font-semibold">Organisasi</th>
                    <th class="py-2.5 px-3 font-semibold">Rekening Sumber</th>
                    <th class="py-2.5 px-3 font-semibold text-center">Bukti</th>
                    <th class="py-2.5 px-4 font-semibold text-right">Jumlah</th>
                </tr>
            </thead>
            <tbody>
                @foreach($fundRequests as $fr)
                <tr class="border-b border-slate-50 last:border-0 hover:bg-slate-50/50 transition-colors">
                    <td class="py-2.5 px-4 text-slate-500 whitespace-nowrap">{{ $fr->disbursed_at?->translatedFormat('d M Y') }}</td>
                    <td class="py-2.5 px-3">
                        <a href="{{ route('fund-requests.show', $fr) }}" class="no-underline">
                            <div class="font-mono text-[11px] text-blue-600">{{ $fr->reference }}</div>
                            <div class="text-slate-700 font-medium">{{ $fr->title }}</div>
                        </a>
                    </td>
                    <td class="py-2.5 px-3 text-slate-600">{{ $fr->requester?->name ?? '-' }}</td>
                    <td class="py-2.5 px-3 text-slate-600">{{ $fr->organization?->name ?? '-' }}</td>
                    <td class="py-2.5 px-3 text-slate-600">
                        @if($fr->disburseAccount)
                            <span class="font-mono text-[11px] text-slate-400 block">{{ $fr->disburseAccount->code }}</span>
                            {{ $fr->disburseAccount->name }}
                        @else
                            -
                        @endif
                    </td>
                    <td class="py-2.5 px-3 text-center">
                        @if($fr->disbursementProofs->isNotEmpty())
                        <span class="inline-flex px-2 py-0.5 rounded-md text-[11px] font-semibold bg-green-50 text-green-600">Ada</span>
                        @else
                        <span class="inline-flex px-2 py-0.5 rounded-md text-[11px] font-semibold bg-red-50 text-red-600">Belum</span>
                        @endif
                    </td>
                    <td class="py-2.5 px-4 text-right font-mono text-slate-800 whitespace-nowrap">Rp {{ number_format($fr->amount, 0, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="bg-slate-50/70 border-t border-slate-100">
                    <td colspan="6" class="py-2.5 px-4 text-[11px] font-bold text-slate-500 uppercase tracking-widest">Total ({{ $summary->total_count }} pencairan, sesuai filter)</td>
                    <td class="py-2.5 px-4 text-right font-mono font-bold text-slate-900 whitespace-nowrap">Rp {{ number_format($summary->total_amount, 0, ',', '.') }}</td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

<div class="flex items-center justify-between gap-3 mt-4 flex-wrap">
    <div class="text-xs text-slate-400">
        Menampilkan {{ $fundRequests->firstItem() }}–{{ $fundRequests->lastItem() }} dari {{ $fundRequests->total() }} pencairan
    </div>
    @if($fundRequests->hasPages())
    <div>{{ $fundRequests->links() }}</div>
    @endif
</div>
@endif

</x-layouts.app>
