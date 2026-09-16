<x-layouts.app title="Pengajuan Saldo">

@if(session('success'))
<div class="flex items-center gap-2.5 px-4 py-3 bg-green-50 border border-green-200 rounded-xl mb-4 text-sm text-green-700">
    <svg width="16" height="16" fill="#16a34a" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
    {{ session('success') }}
</div>
@endif
@if(session('warning'))
<div class="flex items-center gap-2.5 px-4 py-3 bg-amber-50 border border-amber-200 rounded-xl mb-4 text-sm text-amber-700">
    <svg width="16" height="16" fill="none" stroke="#d97706" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
    {{ session('warning') }}
</div>
@endif

<div class="flex items-start justify-between gap-4 mb-5 flex-wrap">
    <div>
        <h2 class="text-lg font-bold text-slate-900 m-0 mb-1">Pengajuan Saldo</h2>
        <p class="text-xs text-slate-400 m-0">Pengajuan tambahan saldo rekening antar organisasi (Kampus/STMIK &rarr; Yayasan).</p>
    </div>
    <div class="flex gap-2">
        @if($canRequest)
        <a href="{{ route('cash-topup-requests.create') }}"
            class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-sm font-semibold bg-gradient-to-br from-blue-500 to-blue-600 text-white no-underline hover:opacity-90 transition-opacity shadow-sm">
            + Ajukan Saldo
        </a>
        @endif
        @if($canApprove)
        <a href="{{ route('cash-topup-requests.direct-create') }}"
            class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-sm font-semibold bg-gradient-to-br from-green-500 to-green-600 text-white no-underline hover:opacity-90 transition-opacity shadow-sm">
            + Isi Saldo Langsung
        </a>
        @endif
    </div>
</div>

<form method="GET" action="{{ route('cash-topup-requests.index') }}" class="bg-white rounded-xl shadow-sm p-4 mb-4 flex flex-wrap gap-3 items-end">
    <div class="min-w-[160px]">
        <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block mb-1.5">Status</label>
        <select name="status" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm text-slate-700 bg-white outline-none focus:border-blue-400 transition-colors">
            <option value="" {{ $filterStatus === '' ? 'selected' : '' }}>Semua</option>
            <option value="pending" {{ $filterStatus === 'pending' ? 'selected' : '' }}>Menunggu Approval</option>
            <option value="approved" {{ $filterStatus === 'approved' ? 'selected' : '' }}>Disetujui</option>
            <option value="rejected" {{ $filterStatus === 'rejected' ? 'selected' : '' }}>Ditolak</option>
        </select>
    </div>
    <div class="flex gap-2">
        <button type="submit" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-sm font-semibold bg-blue-600 text-white border-0 cursor-pointer hover:bg-blue-700 transition-colors">
            Filter
        </button>
        @if($filterStatus)
        <a href="{{ route('cash-topup-requests.index') }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-sm font-medium bg-slate-100 text-slate-600 no-underline hover:bg-slate-200 transition-colors">
            Reset
        </a>
        @endif
    </div>
</form>

@if($topups->isEmpty())
<div class="bg-white rounded-xl shadow-sm py-16 px-5 text-center">
    <div class="w-16 h-16 rounded-2xl bg-slate-50 border border-slate-100 flex items-center justify-center mx-auto mb-4">
        <svg width="28" height="28" fill="none" stroke="#94a3b8" stroke-width="1.5" viewBox="0 0 24 24"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
    </div>
    <div class="text-sm font-semibold text-slate-700 mb-1">Belum ada pengajuan saldo</div>
    <div class="text-xs text-slate-400">Pengajuan tambahan saldo akan tampil di sini.</div>
</div>
@else
<div class="flex flex-col gap-3">
    @foreach($topups as $topup)
    @php
        $statusStyle = [
            'pending'  => ['bg' => 'bg-orange-100', 'text' => 'text-orange-700', 'label' => 'Menunggu Approval', 'stripe' => 'from-orange-400 to-orange-500'],
            'approved' => ['bg' => 'bg-green-100', 'text' => 'text-green-700', 'label' => 'Disetujui', 'stripe' => 'from-green-400 to-green-500'],
            'rejected' => ['bg' => 'bg-red-100', 'text' => 'text-red-600', 'label' => 'Ditolak', 'stripe' => 'from-red-400 to-red-500'],
        ][$topup->status];
    @endphp
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="h-1 bg-gradient-to-r {{ $statusStyle['stripe'] }}"></div>
        <div class="px-5 pt-4 pb-4">
            <div class="flex items-start justify-between gap-3 mb-3 flex-wrap">
                <div class="flex items-center gap-2 flex-wrap">
                    <span class="font-mono text-sm font-bold text-orange-500">{{ $topup->reference }}</span>
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold {{ $statusStyle['bg'] }} {{ $statusStyle['text'] }}">
                        {{ $statusStyle['label'] }}
                    </span>
                    @if($topup->isYayasanInitiated())
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-purple-100 text-purple-700">
                        Diisi Langsung oleh Yayasan
                    </span>
                    @endif
                </div>
                <div class="text-right">
                    <div class="text-xl font-extrabold text-slate-900 font-mono">Rp {{ number_format($topup->amount, 0, ',', '.') }}</div>
                    <div class="text-[11px] text-slate-400 mt-0.5">{{ $topup->isYayasanInitiated() ? 'Diisi' : 'Diajukan' }} {{ $topup->created_at->format('d/m/Y H:i') }}</div>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-x-6 gap-y-2.5 sm:grid-cols-3 mb-3">
                <div>
                    <div class="text-[10px] font-semibold text-slate-400 uppercase tracking-wide mb-0.5">Organisasi Tujuan</div>
                    <div class="text-xs font-semibold text-slate-800">{{ $topup->requestingOrganization->name }}</div>
                </div>
                <div>
                    <div class="text-[10px] font-semibold text-slate-400 uppercase tracking-wide mb-0.5">Rekening Tujuan</div>
                    <div class="text-xs font-semibold text-slate-800">{{ $topup->targetAccount->name ?? '-' }}</div>
                </div>
                <div>
                    <div class="text-[10px] font-semibold text-slate-400 uppercase tracking-wide mb-0.5">{{ $topup->isYayasanInitiated() ? 'Diisi Oleh' : 'Diajukan Oleh' }}</div>
                    <div class="text-xs font-semibold text-slate-800">{{ $topup->requestedBy->name ?? '-' }}</div>
                </div>
            </div>

            @if($topup->notes)
            <div class="text-xs text-slate-500 mb-3">{{ \Illuminate\Support\Str::limit($topup->notes, 120) }}</div>
            @endif

            <div class="flex items-center gap-2 pt-3 border-t border-slate-100 flex-wrap">
                <a href="{{ route('cash-topup-requests.show', $topup) }}"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold bg-slate-100 text-slate-600 hover:bg-slate-200 transition-colors no-underline">
                    Lihat Detail
                </a>
            </div>
        </div>
    </div>
    @endforeach
</div>

<div class="mt-4">
    {{ $topups->links() }}
</div>
@endif

</x-layouts.app>
