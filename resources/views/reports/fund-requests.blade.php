<x-layouts.app title="Laporan Pengajuan Dana">

{{-- Header --}}
<div class="flex items-start justify-between gap-4 mb-5 flex-wrap">
    <div>
        <h2 class="text-lg font-bold text-slate-900 m-0 mb-1">Laporan Pengajuan Dana</h2>
        <p class="text-xs text-slate-400 m-0">
            Rekap seluruh pengajuan dana
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
<form method="GET" action="{{ route('reports.fund-requests') }}" class="bg-white rounded-xl shadow-sm p-4 mb-4 flex flex-wrap gap-3 items-end">
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
        <a href="{{ route('reports.fund-requests') }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-sm font-medium bg-slate-100 text-slate-600 no-underline hover:bg-slate-200 transition-colors">
            Reset
        </a>
    </div>
</form>

{{-- Summary --}}
<div class="grid grid-cols-2 sm:grid-cols-5 gap-3 mb-4">
    <div class="bg-white rounded-xl shadow-sm px-4 py-3.5">
        <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-0.5">Total Pengajuan</div>
        <div class="text-2xl font-extrabold text-slate-800">{{ $summary->total_count }}</div>
        <div class="text-xs text-slate-400 mt-0.5 font-mono">Rp {{ number_format($summary->total_amount, 0, ',', '.') }}</div>
    </div>
    <div class="bg-white rounded-xl shadow-sm px-4 py-3.5">
        <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-0.5">Menunggu Approval</div>
        <div class="text-2xl font-extrabold text-amber-500">{{ $summary->pending_count ?? 0 }}</div>
    </div>
    <div class="bg-white rounded-xl shadow-sm px-4 py-3.5">
        <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-0.5">Disetujui (Belum Cair)</div>
        <div class="text-2xl font-extrabold text-blue-500">{{ $summary->approved_count ?? 0 }}</div>
    </div>
    <div class="bg-white rounded-xl shadow-sm px-4 py-3.5">
        <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-0.5">Sudah Dicairkan</div>
        <div class="text-2xl font-extrabold text-green-500">{{ $summary->disbursed_count ?? 0 }}</div>
        <div class="text-xs text-slate-400 mt-0.5 font-mono">Rp {{ number_format($summary->disbursed_amount, 0, ',', '.') }}</div>
    </div>
    <div class="bg-white rounded-xl shadow-sm px-4 py-3.5">
        <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-0.5">Ditolak</div>
        <div class="text-2xl font-extrabold text-red-500">{{ $summary->rejected_count ?? 0 }}</div>
    </div>
</div>

{{-- Rekap per organisasi --}}
@if($perOrg->count() > 1)
<div class="bg-white rounded-xl shadow-sm p-4 mb-4">
    <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2.5">Rekap per Organisasi</div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-[11px] text-slate-400 uppercase tracking-wider border-b border-slate-100">
                    <th class="py-2 pr-3 font-semibold">Organisasi</th>
                    <th class="py-2 px-3 font-semibold text-center">Jumlah</th>
                    <th class="py-2 pl-3 font-semibold text-right">Total Nominal</th>
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

{{-- Tabel utama --}}
@if($fundRequests->isEmpty())
<div class="bg-white rounded-xl shadow-sm py-16 px-5 text-center">
    <div class="w-16 h-16 rounded-2xl bg-slate-50 border border-slate-100 flex items-center justify-center mx-auto mb-4">
        <svg width="28" height="28" fill="none" stroke="#94a3b8" stroke-width="1.5" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
        </svg>
    </div>
    <div class="text-sm font-semibold text-slate-600 mb-1">Tidak ada data</div>
    <div class="text-xs text-slate-400">Tidak ada pengajuan dana pada periode/filter yang dipilih.</div>
</div>
@else
<div class="bg-white rounded-xl shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-[11px] text-slate-400 uppercase tracking-wider bg-slate-50/70 border-b border-slate-100">
                    <th class="py-2.5 px-4 font-semibold">Tanggal</th>
                    <th class="py-2.5 px-3 font-semibold">Referensi / Judul</th>
                    <th class="py-2.5 px-3 font-semibold">Pengaju</th>
                    <th class="py-2.5 px-3 font-semibold">Organisasi</th>
                    <th class="py-2.5 px-3 font-semibold">Status</th>
                    <th class="py-2.5 px-4 font-semibold text-right">Jumlah</th>
                </tr>
            </thead>
            <tbody>
                @foreach($fundRequests as $fr)
                @php
                    $state = $fr->disbursed_at ? 'disbursed' : $fr->status;
                    $badge = match($state) {
                        'pending'   => ['Menunggu Approval', 'bg-amber-50 text-amber-600'],
                        'approved'  => ['Disetujui', 'bg-blue-50 text-blue-600'],
                        'disbursed' => ['Sudah Cair', 'bg-green-50 text-green-600'],
                        'rejected'  => ['Ditolak', 'bg-red-50 text-red-600'],
                        default     => [ucfirst($state), 'bg-slate-100 text-slate-500'],
                    };
                @endphp
                <tr class="border-b border-slate-50 last:border-0 hover:bg-slate-50/50 transition-colors">
                    <td class="py-2.5 px-4 text-slate-500 whitespace-nowrap">{{ $fr->submitted_at?->translatedFormat('d M Y') ?? '-' }}</td>
                    <td class="py-2.5 px-3">
                        <a href="{{ route('fund-requests.show', $fr) }}" class="no-underline">
                            <div class="font-mono text-[11px] text-blue-600">{{ $fr->reference }}</div>
                            <div class="text-slate-700 font-medium">{{ $fr->title }}</div>
                        </a>
                        @if($fr->budgetProgram)
                        <div class="text-[11px] text-slate-400">{{ $fr->budgetProgram->name }}</div>
                        @endif
                    </td>
                    <td class="py-2.5 px-3 text-slate-600">
                        {{ $fr->requester?->name ?? '-' }}
                        @if($fr->department)
                        <div class="text-[11px] text-slate-400">{{ $fr->department->name }}</div>
                        @endif
                    </td>
                    <td class="py-2.5 px-3 text-slate-600">{{ $fr->organization?->name ?? '-' }}</td>
                    <td class="py-2.5 px-3">
                        <span class="inline-flex px-2 py-0.5 rounded-md text-[11px] font-semibold {{ $badge[1] }}">{{ $badge[0] }}</span>
                    </td>
                    <td class="py-2.5 px-4 text-right font-mono text-slate-800 whitespace-nowrap">Rp {{ number_format($fr->amount, 0, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="bg-slate-50/70 border-t border-slate-100">
                    <td colspan="5" class="py-2.5 px-4 text-[11px] font-bold text-slate-500 uppercase tracking-widest">Total ({{ $summary->total_count }} pengajuan, sesuai filter)</td>
                    <td class="py-2.5 px-4 text-right font-mono font-bold text-slate-900 whitespace-nowrap">Rp {{ number_format($summary->total_amount, 0, ',', '.') }}</td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

<div class="flex items-center justify-between gap-3 mt-4 flex-wrap">
    <div class="text-xs text-slate-400">
        Menampilkan {{ $fundRequests->firstItem() }}–{{ $fundRequests->lastItem() }} dari {{ $fundRequests->total() }} pengajuan
    </div>
    @if($fundRequests->hasPages())
    <div>{{ $fundRequests->links() }}</div>
    @endif
</div>
@endif

</x-layouts.app>
