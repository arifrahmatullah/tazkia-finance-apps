<x-layouts.app title="Detail Pengajuan Dana">

<a href="{{ route('fund-requests.index') }}" class="inline-flex items-center gap-1.5 text-sm text-slate-500 hover:text-orange-500 mb-5 no-underline">
    <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M19 12H5M12 5l-7 7 7 7"/></svg>
    Kembali ke Daftar Pengajuan
</a>

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

@php
    $statusConfig = [
        'draft'    => ['bg-slate-100 text-slate-500',   'Draft'],
        'pending'  => ['bg-yellow-100 text-yellow-700', 'Menunggu Approval'],
        'approved' => ['bg-green-100 text-green-700',   'Disetujui'],
        'rejected' => ['bg-red-100 text-red-600',       'Ditolak'],
    ];
    [$cls, $label] = $statusConfig[$fundRequest->status];
@endphp

<div class="flex items-start justify-between gap-4 mb-6 flex-wrap">
    <div>
        <h2 class="text-lg font-bold text-slate-900 m-0 mb-1.5">{{ $fundRequest->title }}</h2>
        <div class="flex items-center gap-2.5 flex-wrap">
            <div class="inline-flex items-center gap-1.5 px-3 py-1 bg-orange-50 border border-orange-200 rounded-lg font-mono text-sm font-bold text-orange-600">
                <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"/></svg>
                {{ $fundRequest->reference }}
            </div>
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold {{ $cls }}">{{ $label }}</span>
            @if($fundRequest->isPending())
                <span class="text-xs text-yellow-700">Level {{ $fundRequest->current_step }}/{{ $fundRequest->total_steps }}</span>
            @endif
        </div>
    </div>
    <div class="flex gap-2 flex-wrap">
        @if($fundRequest->isDraft())
            <a href="{{ route('fund-requests.edit', $fundRequest) }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg text-xs font-semibold bg-blue-50 text-blue-600 hover:bg-blue-100 transition-colors no-underline">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                Edit
            </a>
            <form id="submit-form" method="POST" action="{{ route('fund-requests.submit', $fundRequest) }}">@csrf</form>
            <button type="button" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg text-xs font-semibold bg-gradient-to-br from-orange-400 to-orange-500 text-white border-0 cursor-pointer hover:-translate-y-px transition-all shadow-sm"
                onclick="confirmModal('Submit Pengajuan', 'Pengajuan <strong>{{ addslashes($fundRequest->reference) }}</strong> akan dikirim untuk diproses approval. Setelah disubmit, pengajuan tidak bisa diubah.', function(){ document.getElementById(\'submit-form\').submit(); })">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M22 2L11 13M22 2l-7 20-4-9-9-4 20-7z"/></svg>
                Submit
            </button>
            <form id="del-form" method="POST" action="{{ route('fund-requests.destroy', $fundRequest) }}">@csrf @method('DELETE')</form>
            <button type="button" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg text-xs font-semibold bg-red-50 text-red-600 hover:bg-red-100 transition-colors border-0 cursor-pointer"
                onclick="confirmDelete('del-form', '{{ addslashes($fundRequest->reference) }}')">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                Hapus
            </button>
        @endif
        @if($canApprove)
            @php $currentApproval = $fundRequest->approvals->where('step', $fundRequest->current_step)->where('status', 'waiting')->first(); @endphp
            @if($currentApproval)
            <button type="button" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg text-xs font-semibold bg-gradient-to-br from-green-500 to-green-600 text-white border-0 cursor-pointer hover:-translate-y-px transition-all shadow-sm" onclick="openApproveModal({{ $currentApproval->id }})">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M20 6L9 17l-5-5"/></svg>
                Setujui
            </button>
            <button type="button" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg text-xs font-semibold bg-gradient-to-br from-red-500 to-red-600 text-white border-0 cursor-pointer hover:-translate-y-px transition-all shadow-sm" onclick="openRejectModal({{ $currentApproval->id }})">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M18 6L6 18M6 6l12 12"/></svg>
                Tolak
            </button>
            @endif
        @endif
    </div>
</div>

<div class="grid grid-cols-2 gap-5 mb-5">
    {{-- Info Pengajuan --}}
    <div class="bg-white rounded-xl shadow-sm p-6">
        <div class="text-[11px] font-bold text-slate-400 uppercase tracking-widest mb-3.5 pb-2 border-b border-slate-100">Informasi Pengajuan</div>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <div class="text-[11px] text-slate-400 mb-1">Organisasi</div>
                <div class="text-sm font-medium text-slate-900">{{ $fundRequest->organization->name }}</div>
            </div>
            <div>
                <div class="text-[11px] text-slate-400 mb-1">Departemen</div>
                <div class="text-sm font-medium text-slate-900">{{ $fundRequest->department->name }}</div>
            </div>
            <div>
                <div class="text-[11px] text-slate-400 mb-1">Tanggal Pengajuan</div>
                <div class="text-sm font-medium text-slate-900">{{ $fundRequest->created_at->format('d/m/Y H:i') }}</div>
            </div>
            <div>
                <div class="text-[11px] text-slate-400 mb-1">Periode Anggaran</div>
                <div class="text-sm font-medium text-slate-900">{{ $fundRequest->budgetPeriod?->name ?? '-' }}</div>
            </div>
            @if($fundRequest->submitted_at)
            <div>
                <div class="text-[11px] text-slate-400 mb-1">Disubmit</div>
                <div class="text-sm font-medium text-slate-900">{{ $fundRequest->submitted_at->format('d/m/Y H:i') }}</div>
            </div>
            @endif
            @if($fundRequest->approved_at)
            <div>
                <div class="text-[11px] text-slate-400 mb-1">Disetujui</div>
                <div class="text-sm font-medium text-green-700">{{ $fundRequest->approved_at->format('d/m/Y H:i') }}</div>
            </div>
            @endif
            @if($fundRequest->rejected_at)
            <div>
                <div class="text-[11px] text-slate-400 mb-1">Ditolak</div>
                <div class="text-sm font-medium text-red-500">{{ $fundRequest->rejected_at->format('d/m/Y H:i') }}</div>
            </div>
            @endif
        </div>
        @if($fundRequest->purpose)
        <div class="mt-4 pt-3.5 border-t border-slate-100">
            <div class="text-[11px] text-slate-400 mb-1.5">Tujuan / Keterangan</div>
            <p class="text-sm text-slate-700 leading-relaxed m-0">{{ $fundRequest->purpose }}</p>
        </div>
        @endif
    </div>

    {{-- Info Pengaju + Nominal --}}
    <div class="flex flex-col gap-5">
        <div class="bg-white rounded-xl shadow-sm p-6">
            <div class="text-[11px] font-bold text-slate-400 uppercase tracking-widest mb-3.5 pb-2 border-b border-slate-100">Informasi Pengaju</div>
            <div class="grid grid-cols-2 gap-3.5">
                <div>
                    <div class="text-[11px] text-slate-400 mb-1">Nama Karyawan</div>
                    <div class="text-sm font-medium text-slate-900">{{ $fundRequest->requester->name }}</div>
                </div>
                <div>
                    <div class="text-[11px] text-slate-400 mb-1">NIP</div>
                    <div class="text-sm font-medium text-slate-900 font-mono">{{ $fundRequest->requester->employee_id }}</div>
                </div>
                <div class="col-span-2">
                    <div class="text-[11px] text-slate-400 mb-1">Jabatan Saat Pengajuan</div>
                    <div class="text-sm font-medium text-slate-900">{{ $fundRequest->requesterPosition->name }}</div>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-7 text-center">
            <div class="text-[11px] text-slate-400 mb-2">Total Dana Diajukan</div>
            <div class="text-3xl font-extrabold text-slate-900 font-mono">Rp {{ number_format($fundRequest->amount, 0, ',', '.') }}</div>
        </div>
    </div>
</div>

{{-- Approval Trail --}}
<div class="bg-white rounded-xl shadow-sm p-6">
    <div class="text-[11px] font-bold text-slate-400 uppercase tracking-widest mb-3.5 pb-2 border-b border-slate-100">Riwayat Approval</div>
    @if($fundRequest->approvals->isEmpty())
        <div class="text-center py-6 text-slate-400 text-sm">
            Pengajuan masih draft. Submit pengajuan untuk memulai proses approval.
        </div>
    @else
        <div class="relative pl-7">
            <div class="absolute left-2.5 top-0 bottom-0 w-0.5 bg-slate-100 rounded"></div>

            {{-- Submitted event --}}
            <div class="relative pb-5">
                <div class="absolute -left-[18px] top-0.5 w-5 h-5 rounded-full flex items-center justify-center bg-blue-50 border-2 border-blue-500">
                    <svg width="10" height="10" fill="#3b82f6" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                </div>
                <div class="text-[11px] font-semibold text-slate-400 uppercase tracking-wide mb-0.5">Disubmit</div>
                <div class="text-sm font-semibold text-slate-800">{{ $fundRequest->requester->name }}</div>
                <div class="text-xs text-slate-400 mt-0.5">{{ $fundRequest->submitted_at?->format('d/m/Y H:i') }}</div>
            </div>

            @foreach($fundRequest->approvals->sortBy('step') as $approval)
            @php
                $isCurrentWaiting = $fundRequest->isPending() && $fundRequest->current_step === $approval->step && $approval->status === 'waiting';
                $dotClass = match($approval->status) {
                    'approved' => 'bg-green-50 border-2 border-green-500',
                    'rejected' => 'bg-red-50 border-2 border-red-500',
                    default    => $isCurrentWaiting ? 'bg-orange-50 border-2 border-orange-400' : 'bg-slate-100 border-2 border-slate-200',
                };
                $badgeCls = $approval->status === 'approved' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-600';
                $badgeTxt = $approval->status === 'approved' ? 'Disetujui' : 'Ditolak';
            @endphp
            <div class="relative pb-5 last:pb-0">
                <div class="absolute -left-[18px] top-0.5 w-5 h-5 rounded-full flex items-center justify-center {{ $dotClass }}">
                    @if($approval->status === 'approved')
                        <svg width="10" height="10" fill="#16a34a" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                    @elseif($approval->status === 'rejected')
                        <svg width="10" height="10" fill="#e11d48" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
                    @elseif($isCurrentWaiting)
                        <svg width="10" height="10" fill="#f97316" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/></svg>
                    @endif
                </div>
                <div class="text-[11px] font-semibold text-slate-400 uppercase tracking-wide mb-0.5">Level {{ $approval->step }} — {{ $approval->approverPosition->name }}</div>
                <div class="flex items-center gap-2 flex-wrap">
                    <div class="text-sm font-semibold text-slate-800">
                        @if($approval->approverUser)
                            {{ $approval->approverUser->name }}
                        @else
                            <span class="text-slate-400 font-normal italic">Menunggu approval...</span>
                        @endif
                    </div>
                    @if($approval->status !== 'waiting')
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold {{ $badgeCls }}">{{ $badgeTxt }}</span>
                    @elseif($isCurrentWaiting)
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-yellow-100 text-yellow-700">Menunggu</span>
                    @endif
                </div>
                @if($approval->acted_at)
                    <div class="text-xs text-slate-400 mt-0.5">{{ $approval->acted_at->format('d/m/Y H:i') }}</div>
                @endif
                @if($approval->notes)
                    <div class="mt-1.5 px-3 py-2 bg-slate-50 rounded-lg text-xs text-slate-600">
                        <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" class="inline align-middle mr-1"><path d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/></svg>
                        {{ $approval->notes }}
                    </div>
                @endif
            </div>
            @endforeach

            @if($fundRequest->isApproved())
            <div class="relative pb-5 last:pb-0">
                <div class="absolute -left-[18px] top-0.5 w-5 h-5 rounded-full flex items-center justify-center bg-green-50 border-2 border-green-500">
                    <svg width="10" height="10" fill="#16a34a" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                </div>
                <div class="text-[11px] font-semibold text-slate-400 uppercase tracking-wide mb-0.5">Selesai</div>
                <div class="text-sm font-semibold text-green-700">Pengajuan Disetujui</div>
                <div class="text-xs text-slate-400 mt-0.5">{{ $fundRequest->approved_at?->format('d/m/Y H:i') }}</div>
            </div>
            @endif
        </div>
    @endif
</div>

{{-- Approve Modal --}}
<div class="fixed inset-0 z-[999] bg-slate-900/50 backdrop-blur-sm items-center justify-center" id="approve-overlay" style="display:none;">
    <div class="bg-white rounded-2xl w-[420px] max-w-[90vw] shadow-2xl overflow-hidden">
        <div class="px-6 py-5 border-b border-slate-100">
            <div class="text-base font-bold text-green-700">Setujui Pengajuan</div>
            <div class="text-xs text-slate-500 mt-1">{{ $fundRequest->reference }} — {{ $fundRequest->title }}</div>
        </div>
        <form id="approve-form" method="POST" action="">
            @csrf
            <div class="px-6 py-5">
                <label class="text-xs font-semibold text-slate-600 block mb-1.5">Catatan (opsional)</label>
                <textarea name="notes" rows="3" class="w-full px-3 py-2.5 border border-slate-200 rounded-xl text-sm outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100 transition-colors resize-y" placeholder="Tambahkan catatan persetujuan..."></textarea>
            </div>
            <div class="px-6 py-4 border-t border-slate-100 flex gap-2 justify-end">
                <button type="button" class="px-4 py-2.5 rounded-xl bg-slate-100 text-slate-700 border border-slate-200 text-sm font-medium cursor-pointer" onclick="closeActionModals()">Batal</button>
                <button type="submit" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg text-xs font-semibold bg-gradient-to-br from-green-500 to-green-600 text-white border-0 cursor-pointer hover:-translate-y-px transition-all shadow-sm">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M20 6L9 17l-5-5"/></svg>
                    Ya, Setujui
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Reject Modal --}}
<div class="fixed inset-0 z-[999] bg-slate-900/50 backdrop-blur-sm items-center justify-center" id="reject-overlay" style="display:none;">
    <div class="bg-white rounded-2xl w-[420px] max-w-[90vw] shadow-2xl overflow-hidden">
        <div class="px-6 py-5 border-b border-slate-100">
            <div class="text-base font-bold text-red-500">Tolak Pengajuan</div>
            <div class="text-xs text-slate-500 mt-1">{{ $fundRequest->reference }} — {{ $fundRequest->title }}</div>
        </div>
        <form id="reject-form" method="POST" action="">
            @csrf
            <div class="px-6 py-5">
                <label class="text-xs font-semibold text-slate-600 block mb-1.5">Alasan Penolakan <span class="text-red-500">*</span></label>
                <textarea name="notes" rows="3" class="w-full px-3 py-2.5 border border-slate-200 rounded-xl text-sm outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100 transition-colors resize-y" placeholder="Jelaskan alasan penolakan..." required></textarea>
                <div class="text-[11px] text-slate-400 mt-1.5">Catatan wajib diisi untuk penolakan.</div>
            </div>
            <div class="px-6 py-4 border-t border-slate-100 flex gap-2 justify-end">
                <button type="button" class="px-4 py-2.5 rounded-xl bg-slate-100 text-slate-700 border border-slate-200 text-sm font-medium cursor-pointer" onclick="closeActionModals()">Batal</button>
                <button type="submit" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg text-xs font-semibold bg-gradient-to-br from-red-500 to-red-600 text-white border-0 cursor-pointer hover:-translate-y-px transition-all shadow-sm">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M18 6L6 18M6 6l12 12"/></svg>
                    Ya, Tolak
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openApproveModal(approvalId) {
    document.getElementById('approve-form').action = `/fund-approvals/${approvalId}/approve`;
    const ov = document.getElementById('approve-overlay');
    ov.style.display = 'flex';
}
function openRejectModal(approvalId) {
    document.getElementById('reject-form').action = `/fund-approvals/${approvalId}/reject`;
    const ov = document.getElementById('reject-overlay');
    ov.style.display = 'flex';
}
function closeActionModals() {
    document.getElementById('approve-overlay').style.display = 'none';
    document.getElementById('reject-overlay').style.display = 'none';
}
document.addEventListener('keydown', e => { if (e.key === 'Escape') closeActionModals(); });
</script>
</x-layouts.app>
