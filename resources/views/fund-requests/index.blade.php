<x-layouts.app title="Pengajuan Dana Saya">

<div class="flex items-start justify-between mb-6 gap-5 flex-wrap">
    <div>
        <h2 class="text-[1.6rem] font-extrabold text-slate-900 m-0 tracking-tight">Pengajuan Dana Saya</h2>
        <p class="text-sm text-slate-500 m-0 mt-1">Riwayat pengajuan dan status persetujuan</p>
    </div>
    <a href="{{ route('fund-requests.create') }}"
       class="inline-flex items-center gap-2 px-5 py-3 rounded-[10px] bg-orange-500 text-white text-sm font-semibold no-underline hover:bg-orange-600 transition-colors flex-shrink-0"
       style="box-shadow:0 4px 12px rgba(249,115,22,0.3);">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.4" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg>
        Buat Pengajuan
    </a>
</div>

@if(session('success'))
<div class="flex items-center gap-2.5 px-4 py-3 bg-green-50 border border-green-200 rounded-xl mb-4 text-sm text-green-700">
    <svg width="16" height="16" fill="#16a34a" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
    {{ session('success') }}
</div>
@endif

@if($errors->any())
<div class="flex items-start gap-2.5 px-4 py-3 bg-red-50 border border-red-200 rounded-xl mb-4 text-sm text-red-600">
    {{ $errors->first() }}
</div>
@endif

{{-- Stats --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-5">
    <div class="bg-white rounded-[14px] p-5 border border-slate-100">
        <div class="flex items-center justify-between">
            <div class="text-[12.5px] font-semibold text-slate-500">Total Pengajuan</div>
            <div class="w-8 h-8 rounded-lg flex items-center justify-center" style="background:#eef2ff;">
                <svg width="16" height="16" fill="none" stroke="#4f46e5" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><rect x="4" y="3" width="16" height="18" rx="2"/><path d="M8 7h8M8 11h8M8 15h5"/></svg>
            </div>
        </div>
        <div class="text-[26px] font-extrabold text-slate-900 mt-2.5 leading-none">{{ $stats['total'] }}</div>
    </div>
    <div class="bg-white rounded-[14px] p-5 border border-slate-100">
        <div class="flex items-center justify-between">
            <div class="text-[12.5px] font-semibold text-slate-500">Total Dana Diajukan</div>
            <div class="w-8 h-8 rounded-lg flex items-center justify-center" style="background:#fff7ed;">
                <svg width="16" height="16" fill="none" stroke="#f97316" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M12 7v10M15 9.5c0-1.4-1.3-2.5-3-2.5s-3 1.1-3 2.5 1.3 2 3 2.5 3 1.1 3 2.5-1.3 2.5-3 2.5-3-1.1-3-2.5"/></svg>
            </div>
        </div>
        <div class="text-[22px] font-extrabold text-slate-900 mt-2.5 leading-none">Rp {{ number_format($stats['total_amount'], 0, ',', '.') }}</div>
    </div>
    <div class="bg-white rounded-[14px] p-5 border border-slate-100">
        <div class="flex items-center justify-between">
            <div class="text-[12.5px] font-semibold text-slate-500">Sudah Cair</div>
            <div class="w-8 h-8 rounded-lg flex items-center justify-center" style="background:#eff6ff;">
                <svg width="16" height="16" fill="none" stroke="#2563eb" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><rect x="2" y="6" width="20" height="14" rx="2"/><path d="M2 10h20"/></svg>
            </div>
        </div>
        <div class="text-[26px] font-extrabold text-slate-900 mt-2.5 leading-none">{{ $stats['cair'] }} <span class="text-[13px] font-semibold text-slate-400">pengajuan</span></div>
    </div>
    <div class="bg-white rounded-[14px] p-5 border border-slate-100">
        <div class="flex items-center justify-between">
            <div class="text-[12.5px] font-semibold text-slate-500">Menunggu Approval</div>
            <div class="w-8 h-8 rounded-lg flex items-center justify-center" style="background:#fef3c7;">
                <svg width="16" height="16" fill="none" stroke="#b45309" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 3"/></svg>
            </div>
        </div>
        <div class="text-[26px] font-extrabold text-slate-900 mt-2.5 leading-none">{{ $stats['pending'] }}</div>
    </div>
</div>

{{-- Filter --}}
<form method="GET" action="{{ route('fund-requests.index') }}" class="bg-white rounded-[14px] p-4 border border-slate-100 flex gap-3 mb-5 flex-wrap items-center">
    <select name="status" class="w-full sm:w-[220px] flex-shrink-0 px-3.5 py-2.5 border border-slate-200 rounded-[9px] text-sm font-medium text-slate-900 bg-white outline-none focus:border-orange-400 transition-colors cursor-pointer">
        <option value="">Semua Status</option>
        <option value="draft"               {{ request('status') === 'draft'               ? 'selected' : '' }}>Draft</option>
        <option value="pending"             {{ request('status') === 'pending'             ? 'selected' : '' }}>Menunggu Approval</option>
        <option value="diproses"            {{ request('status') === 'diproses'            ? 'selected' : '' }}>Diproses</option>
        <option value="rejected"            {{ request('status') === 'rejected'            ? 'selected' : '' }}>Ditolak</option>
        <option value="menunggu_konfirmasi" {{ request('status') === 'menunggu_konfirmasi' ? 'selected' : '' }}>Menunggu Konfirmasi</option>
        <option value="sudah_cair"          {{ request('status') === 'sudah_cair'          ? 'selected' : '' }}>Sudah Cair</option>
    </select>
    <div class="relative flex-1 min-w-[200px] flex items-center">
        <svg width="16" height="16" fill="none" stroke="#94a3b8" stroke-width="2" viewBox="0 0 24 24" class="absolute left-3.5 pointer-events-none"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari no. pengajuan atau judul..."
            class="w-full pl-10 pr-3.5 py-2.5 border border-slate-200 rounded-[9px] text-sm text-slate-900 bg-white outline-none focus:border-orange-400 transition-colors">
    </div>
    <button type="submit" class="px-6 py-2.5 rounded-[9px] border-0 cursor-pointer text-sm font-semibold bg-orange-500 text-white hover:bg-orange-600 transition-colors">Cari</button>
    @if(request()->hasAny(['search','status']))
        <a href="{{ route('fund-requests.index') }}" class="px-3.5 py-2.5 rounded-[9px] border border-slate-200 text-sm text-slate-500 no-underline bg-white hover:bg-slate-50 transition-colors">Reset</a>
    @endif
</form>

@if($fundRequests->isEmpty())
<div class="bg-white rounded-[14px] py-14 px-6 text-center" style="border:1px dashed #dfe3ec;">
    @if(request()->hasAny(['search','status']))
    <div class="text-[15px] font-semibold text-slate-700">Tidak ada pengajuan yang cocok</div>
    <div class="text-[13.5px] text-slate-400 mt-1.5">Coba ubah filter status atau kata kunci pencarian.</div>
    @else
    <div class="w-14 h-14 rounded-2xl bg-slate-50 border border-slate-100 flex items-center justify-center mx-auto mb-4">
        <svg width="24" height="24" fill="none" stroke="#94a3b8" stroke-width="1.5" viewBox="0 0 24 24"><path d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
    </div>
    <div class="text-[15px] font-semibold text-slate-700 mb-1">Belum ada pengajuan</div>
    <div class="text-[13.5px] text-slate-400 mb-4">Klik "Buat Pengajuan" untuk memulai pengajuan dana.</div>
    <a href="{{ route('fund-requests.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-[10px] bg-orange-500 text-white text-sm font-semibold no-underline hover:bg-orange-600 transition-colors">
        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg>
        Buat Pengajuan
    </a>
    @endif
</div>

@else
<div class="flex flex-col gap-4">
    @foreach($fundRequests as $fr)
    @php
        $isDisbursed = $fr->isDisbursed();
        $state = match(true) {
            $fr->status === 'draft'                        => 'draft',
            $fr->status === 'pending'                      => 'pending',
            $fr->status === 'rejected'                     => 'rejected',
            $isDisbursed && is_null($fr->receipt_status)   => 'menunggu_konfirmasi',
            $isDisbursed                                   => 'sudah_cair',
            default                                        => 'diproses',
        };
        $cfg = [
            'draft'               => ['label' => 'Draft',               'badge' => 'bg-slate-100 text-slate-500', 'bar' => '#94a3b8'],
            'pending'             => ['label' => 'Menunggu Approval',   'badge' => 'bg-amber-100 text-amber-800', 'bar' => '#f59e0b'],
            'diproses'            => ['label' => 'Diproses',            'badge' => 'bg-blue-100 text-blue-800',   'bar' => '#3b82f6'],
            'rejected'            => ['label' => 'Ditolak',             'badge' => 'bg-red-100 text-red-700',     'bar' => '#ef4444'],
            'menunggu_konfirmasi' => ['label' => 'Menunggu Konfirmasi', 'badge' => 'bg-amber-100 text-amber-800', 'bar' => '#3b82f6'],
            'sudah_cair'          => ['label' => 'Sudah Cair',          'badge' => 'bg-blue-100 text-blue-800',   'bar' => '#3b82f6'],
        ][$state];
        $rejectedApproval = $fr->status === 'rejected' ? $fr->approvals->where('status', 'rejected')->first() : null;
        $approvalJson = $fr->approvals->sortBy('step')->map(fn($a) => [
            'step'        => $a->step,
            'status'      => $a->status,
            'position'    => $a->approverPosition->name,
            'user'        => $a->approverUser?->name,
            'holder_name' => $a->approverPosition->activeHolder?->employee?->name,
            'acted_at'    => $a->acted_at?->format('d/m/Y H:i'),
            'notes'       => $a->notes,
        ])->values()->toJson();
    @endphp

    <div class="bg-white rounded-[14px] border border-slate-100 overflow-hidden">
        {{-- Status stripe --}}
        <div class="h-1" style="background:{{ $cfg['bar'] }};"></div>

        <div class="px-6 pt-5 pb-5">
            {{-- Top row: ref + badge / nominal --}}
            <div class="flex items-start justify-between gap-4">
                <div class="flex items-center gap-2.5 flex-wrap">
                    <span class="font-mono text-[13px] font-semibold text-orange-500">{{ $fr->reference }}</span>
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold {{ $cfg['badge'] }}">{{ $cfg['label'] }}</span>
                    @if($fr->status === 'pending' && $fr->total_steps > 0)
                        <span class="text-[11px] text-slate-400">Level {{ $fr->current_step }}/{{ $fr->total_steps }}</span>
                    @endif
                </div>
                <div class="text-right flex-shrink-0">
                    <div class="text-xl font-extrabold text-slate-900 leading-tight">Rp {{ number_format($fr->amount, 0, ',', '.') }}</div>
                    <div class="text-[12.5px] text-slate-400 mt-0.5">{{ $fr->created_at->format('d/m/Y') }}</div>
                </div>
            </div>

            {{-- Judul --}}
            <div class="text-[17px] font-bold text-slate-900 mt-3.5 leading-snug">{{ $fr->title }}</div>

            {{-- Info grid --}}
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 mt-4">
                <div>
                    <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Departemen</div>
                    <div class="text-sm font-medium text-slate-800 mt-0.5">{{ $fr->department->name }}</div>
                </div>
                @if($fr->budgetProgram)
                <div>
                    <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Program Kerja</div>
                    <div class="text-sm font-medium text-slate-800 mt-0.5 truncate">{{ $fr->budgetProgram->name }}</div>
                </div>
                @endif
                @if($fr->submitted_at)
                <div>
                    <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Disubmit</div>
                    <div class="text-sm font-medium text-slate-800 mt-0.5">{{ $fr->submitted_at->format('d/m/Y H:i') }}</div>
                </div>
                @endif
            </div>

            {{-- Approval steps (nama inline, klik untuk detail) --}}
            @if($fr->total_steps > 0 && !$isDisbursed && $fr->status !== 'rejected' && $fr->status !== 'draft')
            <button type="button" onclick="openApprovalModal('{{ addslashes($fr->reference) }}', '{{ addslashes($fr->title) }}', {{ $fr->current_step }}, {{ $fr->total_steps }}, {{ $approvalJson }})"
                class="flex items-center gap-2 flex-wrap w-full mt-4 px-3.5 py-3 bg-slate-50 rounded-[10px] border-0 cursor-pointer text-left hover:bg-slate-100 transition-colors">
                @foreach($fr->approvals->sortBy('step') as $approval)
                @php
                    $isActive = $approval->status === 'waiting' && $fr->current_step == $approval->step && $fr->status === 'pending';
                @endphp
                <span class="inline-flex items-center gap-1.5">
                    @if($approval->status === 'approved')
                    <svg width="15" height="15" fill="none" stroke="#16a34a" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M20 6L9 17l-5-5"/></svg>
                    <span class="text-[13px] font-semibold text-green-600">{{ $approval->approverPosition->name }}</span>
                    @elseif($approval->status === 'rejected')
                    <svg width="15" height="15" fill="none" stroke="#dc2626" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M18 6L6 18M6 6l12 12"/></svg>
                    <span class="text-[13px] font-semibold text-red-600">{{ $approval->approverPosition->name }}</span>
                    @elseif($isActive)
                    <span class="w-[9px] h-[9px] rounded-full flex-shrink-0" style="background:#f59e0b;"></span>
                    <span class="text-[13px] font-semibold text-amber-800">{{ $approval->approverPosition->name }}</span>
                    @else
                    <span class="w-[9px] h-[9px] rounded-full flex-shrink-0" style="background:#cbd5e1;"></span>
                    <span class="text-[13px] font-semibold text-slate-400">{{ $approval->approverPosition->name }}</span>
                    @endif
                </span>
                @if(!$loop->last || $state === 'diproses')
                <svg width="13" height="13" fill="none" stroke="#cbd5e1" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" class="flex-shrink-0"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                @endif
                @endforeach
                @if($state === 'diproses')
                <span class="inline-flex items-center gap-1.5">
                    <span class="w-[9px] h-[9px] rounded-full flex-shrink-0" style="background:#f59e0b;"></span>
                    <span class="text-[13px] font-semibold text-amber-800">Pencairan Dana</span>
                </span>
                @else
                <span class="text-[11px] text-slate-400 ml-auto">Lihat detail →</span>
                @endif
            </button>
            @endif

            {{-- Ditolak: catatan --}}
            @if($rejectedApproval)
            <div class="flex items-start gap-2.5 mt-4 p-3.5 bg-red-50 rounded-[10px]">
                <svg width="16" height="16" fill="none" stroke="#dc2626" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" class="flex-shrink-0 mt-0.5"><circle cx="12" cy="12" r="9"/><path d="M15 9l-6 6M9 9l6 6"/></svg>
                <div>
                    <div class="text-[13px] font-bold text-red-700">Ditolak oleh {{ $rejectedApproval->approverPosition->name }}</div>
                    @if($rejectedApproval->notes)
                    <div class="text-[13px] text-red-900/80 mt-0.5">{{ $rejectedApproval->notes }}</div>
                    @endif
                </div>
            </div>
            @endif

            {{-- Pencairan info --}}
            @if($isDisbursed)
            @if(is_null($fr->receipt_status))
            <button type="button"
                class="btn-receipt-confirm w-full flex items-center justify-between gap-3 mt-4 p-3.5 bg-amber-50 border border-amber-200 rounded-[10px] transition-colors hover:bg-amber-100 cursor-pointer text-left group"
                data-confirm-url="{{ route('fund-requests.confirm-receipt', $fr) }}"
                data-dispute-url="{{ route('fund-requests.dispute-receipt', $fr) }}"
                data-ref="{{ $fr->reference }}"
                data-amount="Rp {{ number_format($fr->amount, 0, ',', '.') }}">
                <span class="flex items-center gap-3">
                    <svg width="18" height="18" fill="none" stroke="#b45309" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" class="flex-shrink-0"><rect x="2" y="6" width="20" height="14" rx="2"/><path d="M2 10h20"/></svg>
                    <span>
                        <span class="block text-[13.5px] font-bold text-amber-800">Dicairkan {{ $fr->disbursed_at->format('d/m/Y') }}</span>
                        <span class="block text-[13px] font-semibold text-amber-700 mt-0.5">Klik untuk konfirmasi penerimaan dana →</span>
                    </span>
                </span>
                <svg width="16" height="16" fill="none" stroke="#b45309" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" class="flex-shrink-0 group-hover:translate-x-0.5 transition-transform"><path d="M9 18l6-6-6-6"/></svg>
            </button>
            @else
            <div class="flex items-center gap-3 mt-4 p-3.5 bg-blue-50 rounded-[10px]">
                <svg width="18" height="18" fill="none" stroke="#2563eb" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" class="flex-shrink-0"><rect x="2" y="6" width="20" height="14" rx="2"/><path d="M2 10h20"/></svg>
                <div>
                    <div class="text-[13.5px] font-bold text-blue-800">Dicairkan {{ $fr->disbursed_at->format('d/m/Y') }}</div>
                    @if($fr->receipt_status === 'confirmed')
                    <div class="text-[13px] font-semibold text-green-600 mt-0.5 flex items-center gap-1.5">
                        <svg width="13" height="13" fill="none" stroke="#16a34a" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M20 6L9 17l-5-5"/></svg>
                        Dana diterima{{ $fr->auto_confirmed ? ' (auto-konfirmasi)' : '' }}
                    </div>
                    @elseif($fr->receipt_status === 'disputed')
                    <div class="text-[13px] font-semibold text-red-500 mt-0.5">⚠ Ada kendala dilaporkan</div>
                    @endif
                </div>
            </div>
            @endif
            @endif

            {{-- Actions --}}
            <div class="flex items-center gap-2.5 mt-[18px] flex-wrap">
                <a href="{{ route('fund-requests.show', $fr) }}"
                    class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-[13px] font-semibold bg-slate-50 border border-slate-200 text-slate-700 hover:bg-slate-100 transition-colors no-underline">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                    Lihat Detail
                </a>
                @if($isDisbursed && $fr->needsReport())
                <a href="{{ route('fund-reports.create', ['fund_request' => $fr->id]) }}"
                   class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-[13px] font-semibold text-white no-underline hover:opacity-90 transition-opacity"
                   style="background:#7c3aed;">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><path d="M14 2v6h6"/></svg>
                    Buat Laporan
                </a>
                @endif
                @if($fr->isDraft())
                <a href="{{ route('fund-requests.edit', $fr) }}"
                    class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-[13px] font-semibold bg-blue-50 text-blue-600 hover:bg-blue-100 transition-colors no-underline">
                    <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                    Edit
                </a>
                <form id="del-fr-{{ $fr->id }}" method="POST" action="{{ route('fund-requests.destroy', $fr) }}">@csrf @method('DELETE')</form>
                <button type="button"
                    class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-[13px] font-semibold bg-red-50 text-red-600 hover:bg-red-100 transition-colors border-0 cursor-pointer"
                    onclick="confirmDelete('del-fr-{{ $fr->id }}', '{{ addslashes($fr->reference) }}')">
                    <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/></svg>
                    Hapus
                </button>
                @endif
                <div class="flex-1"></div>
                @if($fr->total_steps > 0)
                <button type="button"
                    onclick="openApprovalModal('{{ addslashes($fr->reference) }}', '{{ addslashes($fr->title) }}', {{ $fr->current_step }}, {{ $fr->total_steps }}, {{ $approvalJson }})"
                    class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-[13px] font-semibold border border-orange-200 text-orange-700 cursor-pointer transition-colors hover:bg-orange-100"
                    style="background:#fff7ed;">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/></svg>
                    Approval
                </button>
                @endif
            </div>
        </div>
    </div>
    @endforeach
</div>

{{-- Pagination --}}
<div class="mt-5 flex items-center justify-between gap-3 flex-wrap">
    <span class="text-[13px] text-slate-400">
        Menampilkan {{ $fundRequests->firstItem() ?? 0 }}–{{ $fundRequests->lastItem() ?? 0 }} dari {{ $fundRequests->total() }} pengajuan
    </span>
@if($fundRequests->hasPages())
<div class="flex justify-end gap-1">
    @if($fundRequests->onFirstPage())
        <span class="inline-flex items-center px-2.5 py-1.5 border border-slate-200 rounded-lg text-xs text-slate-300 pointer-events-none">&laquo;</span>
    @else
        <a href="{{ $fundRequests->previousPageUrl() }}" class="inline-flex items-center px-2.5 py-1.5 border border-slate-200 rounded-lg text-xs text-slate-500 no-underline bg-white">&laquo;</a>
    @endif
    @foreach($fundRequests->getUrlRange(max(1,$fundRequests->currentPage()-2), min($fundRequests->lastPage(),$fundRequests->currentPage()+2)) as $page => $url)
        <a href="{{ $url }}" class="inline-flex items-center px-2.5 py-1.5 border rounded-lg text-xs no-underline {{ $page == $fundRequests->currentPage() ? 'bg-orange-500 border-orange-500 text-white' : 'bg-white border-slate-200 text-slate-500' }}">{{ $page }}</a>
    @endforeach
    @if($fundRequests->hasMorePages())
        <a href="{{ $fundRequests->nextPageUrl() }}" class="inline-flex items-center px-2.5 py-1.5 border border-slate-200 rounded-lg text-xs text-slate-500 no-underline bg-white">&raquo;</a>
    @else
        <span class="inline-flex items-center px-2.5 py-1.5 border border-slate-200 rounded-lg text-xs text-slate-300 pointer-events-none">&raquo;</span>
    @endif
</div>
@endif
</div>
@endif

{{-- Receipt Confirmation Modal --}}
<div class="fixed inset-0 z-[999] bg-slate-900/50 backdrop-blur-sm items-center justify-center" id="receipt-overlay" style="display:none;">
    <div class="bg-white rounded-2xl w-[440px] max-w-[90vw] shadow-2xl overflow-hidden">
        <div class="px-6 py-5 border-b border-slate-100">
            <div class="text-sm font-bold text-slate-800 mb-0.5">Konfirmasi Penerimaan Dana</div>
            <div id="receipt-ref" class="font-mono text-xs font-bold text-orange-500"></div>
        </div>

        {{-- Step 1: pilihan --}}
        <div id="receipt-step-1" class="px-6 py-5">
            <div class="text-center mb-5">
                <div class="text-[11px] text-slate-400 mb-1">Dana yang dicairkan</div>
                <div id="receipt-amount" class="text-2xl font-extrabold text-slate-900 font-mono"></div>
            </div>
            <div class="text-sm text-slate-600 text-center mb-5">Apakah dana sudah masuk ke rekening Anda?</div>
            <div class="flex gap-3">
                <form id="confirm-form" method="POST" action="" class="flex-1">
                    @csrf
                    <button type="submit" class="w-full inline-flex items-center justify-center gap-2 px-4 py-3 rounded-xl text-sm font-semibold bg-gradient-to-br from-green-500 to-green-600 text-white border-0 cursor-pointer hover:opacity-90 transition-opacity shadow-sm">
                        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M20 6L9 17l-5-5"/></svg>
                        Uang Sudah Masuk
                    </button>
                </form>
                <button type="button" id="btn-show-dispute"
                    class="flex-1 inline-flex items-center justify-center gap-2 px-4 py-3 rounded-xl text-sm font-semibold bg-gradient-to-br from-red-500 to-red-600 text-white border-0 cursor-pointer hover:opacity-90 transition-opacity shadow-sm">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
                    Ada Kendala
                </button>
            </div>
            <div class="mt-4 text-center">
                <button type="button" id="receipt-cancel" class="text-xs text-slate-400 hover:text-slate-600 border-0 bg-transparent cursor-pointer">Batal</button>
            </div>
        </div>

        {{-- Step 2: kendala --}}
        <form id="dispute-form" method="POST" action="" style="display:none;">
            @csrf
            <div class="px-6 py-5">
                <button type="button" id="btn-back-dispute" class="flex items-center gap-1 text-xs text-slate-400 hover:text-slate-600 border-0 bg-transparent cursor-pointer mb-4 p-0">
                    <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M19 12H5M12 5l-7 7 7 7"/></svg>
                    Kembali
                </button>
                <label class="text-xs font-semibold text-slate-600 block mb-1.5">Keterangan Kendala <span class="text-red-500">*</span></label>
                <textarea name="receipt_notes" rows="3" required maxlength="500"
                    class="w-full px-3 py-2.5 border border-slate-200 rounded-xl text-sm outline-none focus:border-red-400 focus:ring-2 focus:ring-red-100 transition-colors resize-none"
                    placeholder="Jelaskan kendala yang terjadi. Contoh: dana belum masuk, nominal tidak sesuai..."></textarea>
            </div>
            <div class="px-6 py-4 border-t border-slate-100 flex gap-2 justify-end">
                <button type="button" id="receipt-cancel-2" class="px-4 py-2.5 rounded-xl bg-slate-100 text-slate-700 text-sm font-medium cursor-pointer hover:bg-slate-200 transition-colors border-0">Batal</button>
                <button type="submit" class="inline-flex items-center gap-1.5 px-5 py-2.5 rounded-xl text-sm font-semibold bg-gradient-to-br from-red-500 to-red-600 text-white border-0 cursor-pointer hover:opacity-90 shadow-sm">
                    Kirim Laporan
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Approval Progress Modal --}}
<div id="approval-modal-overlay" class="hidden fixed inset-0 z-[999] bg-slate-900/50 backdrop-blur-sm items-center justify-center">
    <div class="bg-white rounded-2xl w-[420px] max-w-[90vw] shadow-2xl overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100 flex items-start justify-between gap-3">
            <div>
                <div id="am-reference" class="font-mono text-xs font-bold text-orange-500 mb-0.5"></div>
                <div id="am-title" class="text-sm font-semibold text-slate-800 leading-snug"></div>
            </div>
            <button onclick="closeApprovalModal()" class="text-slate-400 hover:text-slate-600 bg-transparent border-0 cursor-pointer p-1 mt-0.5 flex-shrink-0">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M18 6L6 18M6 6l12 12"/></svg>
            </button>
        </div>
        <div id="am-body" class="px-5 py-4 flex flex-col"></div>
    </div>
</div>

<script>
(function () {
    var overlay      = document.getElementById('receipt-overlay');
    var step1        = document.getElementById('receipt-step-1');
    var disputeForm  = document.getElementById('dispute-form');
    var confirmForm  = document.getElementById('confirm-form');

    function openReceipt(btn) {
        document.getElementById('receipt-ref').textContent    = btn.dataset.ref;
        document.getElementById('receipt-amount').textContent = btn.dataset.amount;
        confirmForm.action  = btn.dataset.confirmUrl;
        disputeForm.action  = btn.dataset.disputeUrl;
        step1.style.display = '';
        disputeForm.style.display = 'none';
        disputeForm.querySelector('textarea').value = '';
        overlay.style.display = 'flex';
    }

    function closeReceipt() {
        overlay.style.display = 'none';
    }

    document.querySelectorAll('.btn-receipt-confirm').forEach(function (btn) {
        btn.addEventListener('click', function () { openReceipt(btn); });
    });

    document.getElementById('btn-show-dispute').addEventListener('click', function () {
        step1.style.display = 'none';
        disputeForm.style.display = '';
    });

    document.getElementById('btn-back-dispute').addEventListener('click', function () {
        disputeForm.style.display = 'none';
        step1.style.display = '';
    });

    document.getElementById('receipt-cancel').addEventListener('click', closeReceipt);
    document.getElementById('receipt-cancel-2').addEventListener('click', closeReceipt);
    overlay.addEventListener('click', function (e) { if (e.target === e.currentTarget) closeReceipt(); });
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape') { closeReceipt(); closeApprovalModal(); } });
})();

function openApprovalModal(ref, title, currentStep, totalSteps, approvals) {
    document.getElementById('am-reference').textContent = ref;
    document.getElementById('am-title').textContent = title;

    const body = document.getElementById('am-body');
    body.innerHTML = '';

    approvals.forEach(function(a, i) {
        const isLast   = i === approvals.length - 1;
        const isActive = a.status === 'waiting' && a.step === currentStep;

        const dotColor  = a.status === 'approved' ? '#16a34a' : a.status === 'rejected' ? '#e11d48' : isActive ? '#f97316' : '#cbd5e1';
        const dotBg     = a.status === 'approved' ? '#f0fdf4' : a.status === 'rejected' ? '#fff1f2' : isActive ? '#fff7ed' : '#f8fafc';
        const dotBorder = a.status === 'approved' ? '#16a34a' : a.status === 'rejected' ? '#e11d48' : isActive ? '#f97316' : '#e2e8f0';

        const badgeHtml = a.status === 'approved'
            ? '<span style="background:#dcfce7;color:#15803d;font-size:10px;font-weight:600;padding:1px 7px;border-radius:999px;margin-left:6px;">Disetujui</span>'
            : a.status === 'rejected'
            ? '<span style="background:#fee2e2;color:#dc2626;font-size:10px;font-weight:600;padding:1px 7px;border-radius:999px;margin-left:6px;">Ditolak</span>'
            : isActive
            ? '<span style="background:#fef9c3;color:#a16207;font-size:10px;font-weight:600;padding:1px 7px;border-radius:999px;margin-left:6px;">Menunggu</span>'
            : '';

        const iconSvg = a.status === 'approved'
            ? '<svg width="9" height="9" fill="'+dotColor+'" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>'
            : a.status === 'rejected'
            ? '<svg width="9" height="9" fill="'+dotColor+'" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>'
            : isActive
            ? '<svg width="9" height="9" fill="'+dotColor+'" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/></svg>'
            : '';

        const nameHtml = a.user
            ? '<span style="font-size:13px;font-weight:600;color:#1e293b;">' + a.user + '</span>'
            : a.holder_name
            ? '<span style="font-size:13px;font-weight:600;color:#1e293b;">' + a.holder_name + '</span>'
            : '<span style="font-size:12px;color:#94a3b8;font-style:italic;">Menunggu approval...</span>';

        const dateHtml  = a.acted_at ? '<div style="font-size:11px;color:#94a3b8;margin-top:3px;">' + a.acted_at + '</div>' : '';
        const notesHtml = a.notes    ? '<div style="margin-top:6px;padding:6px 10px;background:#f8fafc;border-radius:8px;font-size:11px;color:#64748b;">' + a.notes + '</div>' : '';

        const row = document.createElement('div');
        row.style.cssText = 'display:flex;gap:12px;';
        row.innerHTML =
            '<div style="display:flex;flex-direction:column;align-items:center;width:20px;flex-shrink:0;">' +
                '<div style="width:20px;height:20px;border-radius:50%;background:'+dotBg+';border:2px solid '+dotBorder+';display:flex;align-items:center;justify-content:center;flex-shrink:0;">' + iconSvg + '</div>' +
                (!isLast ? '<div style="width:1px;background:#e2e8f0;flex:1;margin:5px 0;min-height:16px;"></div>' : '') +
            '</div>' +
            '<div style="flex:1;min-width:0;' + (!isLast ? 'padding-bottom:14px;' : '') + '">' +
                '<div style="font-size:10px;font-weight:700;color:#94a3b8;letter-spacing:.08em;text-transform:uppercase;">Level ' + a.step + '</div>' +
                '<div style="font-size:11px;color:#64748b;margin-bottom:3px;">' + a.position + '</div>' +
                '<div style="display:flex;align-items:center;flex-wrap:wrap;">' + nameHtml + badgeHtml + '</div>' +
                dateHtml + notesHtml +
            '</div>';
        body.appendChild(row);
    });

    document.getElementById('approval-modal-overlay').style.display = 'flex';
}

function closeApprovalModal() {
    document.getElementById('approval-modal-overlay').style.display = 'none';
}

document.getElementById('approval-modal-overlay').addEventListener('click', function(e) {
    if (e.target === this) closeApprovalModal();
});
</script>
</x-layouts.app>
