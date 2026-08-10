<x-layouts.app title="Laporan Detail Transaksi">

{{-- Header --}}
<div class="flex items-start justify-between gap-4 mb-5 flex-wrap">
    <div>
        <h2 class="text-lg font-bold text-slate-900 m-0 mb-1">Laporan Detail Transaksi</h2>
        <p class="text-xs text-slate-400 m-0">
            Rincian seluruh pengajuan dana lintas departemen dalam satu periode
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
<form method="GET" action="{{ route('reports.detail-transactions') }}" class="bg-white rounded-xl shadow-sm p-4 mb-4 flex flex-wrap gap-3 items-end">
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

    <div class="min-w-[160px]">
        <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block mb-1.5">Status</label>
        <select name="status" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm text-slate-700 bg-white outline-none focus:border-blue-400 transition-colors">
            <option value="">Semua Status</option>
            <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Menunggu Approval</option>
            <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Disetujui (Belum Cair)</option>
            <option value="disbursed" {{ request('status') === 'disbursed' ? 'selected' : '' }}>Sudah Dicairkan</option>
            <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Ditolak</option>
        </select>
    </div>

    <div class="flex gap-2">
        <button type="submit" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-sm font-semibold bg-blue-600 text-white border-0 cursor-pointer hover:bg-blue-700 transition-colors">
            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
            Terapkan
        </button>
        <a href="{{ route('reports.detail-transactions') }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-sm font-medium bg-slate-100 text-slate-600 no-underline hover:bg-slate-200 transition-colors">
            Reset
        </a>
    </div>
</form>

{{-- Summary --}}
@if($totals)
<div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-4">
    <div class="bg-white rounded-xl shadow-sm px-4 py-3.5">
        <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-0.5">Total Transaksi</div>
        <div class="text-2xl font-extrabold text-slate-800">{{ $totals->count }}</div>
    </div>
    <div class="bg-white rounded-xl shadow-sm px-4 py-3.5">
        <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-0.5">Total Diajukan</div>
        <div class="text-base font-extrabold text-slate-800 font-mono leading-tight mt-1">Rp {{ number_format($totals->diajukan, 0, ',', '.') }}</div>
    </div>
    <div class="bg-white rounded-xl shadow-sm px-4 py-3.5">
        <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-0.5">Total Realisasi</div>
        <div class="text-base font-extrabold text-green-600 font-mono leading-tight mt-1">Rp {{ number_format($totals->realisasi, 0, ',', '.') }}</div>
    </div>
    <div class="bg-white rounded-xl shadow-sm px-4 py-3.5">
        <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-0.5">Selisih (Belum Cair)</div>
        <div class="text-base font-extrabold {{ $totals->selisih > 0 ? 'text-amber-500' : 'text-slate-800' }} font-mono leading-tight mt-1">Rp {{ number_format($totals->selisih, 0, ',', '.') }}</div>
    </div>
</div>
@endif

{{-- Tabel utama --}}
@if($transactions->isEmpty())
<div class="bg-white rounded-xl shadow-sm py-16 px-5 text-center">
    <div class="w-16 h-16 rounded-2xl bg-slate-50 border border-slate-100 flex items-center justify-center mx-auto mb-4">
        <svg width="28" height="28" fill="none" stroke="#94a3b8" stroke-width="1.5" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
        </svg>
    </div>
    <div class="text-sm font-semibold text-slate-600 mb-1">Tidak ada data</div>
    <div class="text-xs text-slate-400">Tidak ada transaksi pada periode/filter yang dipilih.</div>
</div>
@else
<div class="bg-white rounded-xl shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-[11px] text-slate-400 uppercase tracking-wider bg-slate-50/70 border-b border-slate-100">
                    <th class="py-2.5 px-4 font-semibold">Tanggal</th>
                    <th class="py-2.5 px-3 font-semibold">Departemen</th>
                    <th class="py-2.5 px-3 font-semibold">Referensi / Judul</th>
                    <th class="py-2.5 px-3 font-semibold">Pengaju</th>
                    <th class="py-2.5 px-3 font-semibold text-right">Diajukan</th>
                    <th class="py-2.5 px-3 font-semibold text-right">Realisasi</th>
                    <th class="py-2.5 px-4 font-semibold text-right">Selisih</th>
                </tr>
            </thead>
            <tbody>
                @foreach($transactions as $fr)
                @php $belumCair = is_null($fr->disbursed_at) && $fr->status !== 'rejected'; @endphp
                <tr class="border-b border-slate-50 last:border-0 hover:bg-slate-50/50 transition-colors {{ $belumCair ? 'bg-red-50/40' : '' }}">
                    <td class="py-2.5 px-4 text-slate-500 whitespace-nowrap">{{ $fr->submitted_at?->translatedFormat('d M Y') ?? '-' }}</td>
                    <td class="py-2.5 px-3 text-slate-600">{{ $fr->department?->name ?? '-' }}</td>
                    <td class="py-2.5 px-3">
                        <a href="{{ route('fund-requests.show', $fr) }}" class="no-underline">
                            <div class="font-mono text-[11px] text-blue-600">{{ $fr->reference }}</div>
                            <div class="text-slate-700 font-medium">{{ $fr->title }}</div>
                        </a>
                        @if($fr->budgetProgram)
                        <div class="text-[11px] text-slate-400">{{ $fr->budgetProgram->name }}</div>
                        @endif
                    </td>
                    <td class="py-2.5 px-3 text-slate-600">{{ $fr->requester?->name ?? '-' }}</td>
                    <td class="py-2.5 px-3 text-right font-mono text-slate-800 whitespace-nowrap">Rp {{ number_format($fr->amount, 0, ',', '.') }}</td>
                    <td class="py-2.5 px-3 text-right font-mono whitespace-nowrap {{ $fr->realized > 0 ? 'text-green-700' : 'text-slate-300' }}">
                        {{ $fr->realized > 0 ? 'Rp ' . number_format($fr->realized, 0, ',', '.') : '-' }}
                    </td>
                    <td class="py-2.5 px-4 text-right font-mono whitespace-nowrap {{ $belumCair ? 'text-red-600 font-semibold' : 'text-slate-400' }}">
                        {{ $fr->selisih > 0 ? 'Rp ' . number_format($fr->selisih, 0, ',', '.') : '-' }}
                    </td>
                </tr>
                @endforeach
            </tbody>
            @if($totals)
            <tfoot>
                <tr class="bg-slate-50/70 border-t border-slate-100">
                    <td colspan="4" class="py-2.5 px-4 text-[11px] font-bold text-slate-500 uppercase tracking-widest">Total ({{ $totals->count }} transaksi, sesuai filter)</td>
                    <td class="py-2.5 px-3 text-right font-mono font-bold text-slate-900 whitespace-nowrap">Rp {{ number_format($totals->diajukan, 0, ',', '.') }}</td>
                    <td class="py-2.5 px-3 text-right font-mono font-bold text-green-700 whitespace-nowrap">Rp {{ number_format($totals->realisasi, 0, ',', '.') }}</td>
                    <td class="py-2.5 px-4 text-right font-mono font-bold text-amber-600 whitespace-nowrap">Rp {{ number_format($totals->selisih, 0, ',', '.') }}</td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>
</div>

<div class="flex items-center justify-between gap-3 mt-4 flex-wrap">
    <div class="text-xs text-slate-400">
        Menampilkan {{ $transactions->firstItem() }}–{{ $transactions->lastItem() }} dari {{ $transactions->total() }} transaksi
        <span class="inline-flex items-center gap-1 ml-2"><span class="w-2 h-2 rounded-sm bg-red-100 inline-block"></span> baris merah = belum dicairkan</span>
    </div>
    @if($transactions->hasPages())
    <div>{{ $transactions->links() }}</div>
    @endif
</div>
@endif
@endif

</x-layouts.app>
