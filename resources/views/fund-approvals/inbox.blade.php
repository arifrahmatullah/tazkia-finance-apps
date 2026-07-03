<x-layouts.app title="Inbox Approval">

<div class="flex items-center justify-between mb-5">
    <div>
        <h2 class="text-lg font-bold text-slate-900 m-0 mb-0.5">Inbox Approval</h2>
        <p class="text-xs text-slate-400 m-0">Pengajuan dana yang menunggu persetujuan Anda</p>
    </div>
    @if(isset($positionName) && $positionName)
        <div class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-orange-50 border border-orange-200 rounded-lg text-sm font-semibold text-orange-700">
            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
            {{ $positionName }}
        </div>
    @endif
</div>

@if(session('success'))
<div class="flex items-center gap-2.5 px-4 py-3 bg-green-50 border border-green-200 rounded-xl mb-4 text-sm text-green-700">
    <svg width="16" height="16" fill="#16a34a" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
    {{ session('success') }}
</div>
@endif

@if(!isset($positionName) || !$positionName)
<div class="flex items-center gap-2.5 px-4 py-3 bg-yellow-50 border border-yellow-200 rounded-xl mb-4 text-sm text-yellow-700">
    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
    Akun Anda tidak memiliki jabatan aktif. Tidak ada pengajuan yang bisa diproses.
</div>
@endif

@if(isset($organizations) && $organizations->count() > 1)
<form method="GET" action="{{ route('fund-approvals.inbox') }}" class="flex gap-2.5 flex-wrap items-center mb-4">
    <select name="organization_id" class="px-3 py-2 border border-slate-200 rounded-xl text-sm text-slate-700 bg-white outline-none focus:border-orange-400 min-w-[200px]" onchange="this.form.submit()">
        <option value="">Semua Organisasi</option>
        @foreach($organizations as $org)
            <option value="{{ $org->id }}" {{ request('organization_id') == $org->id ? 'selected' : '' }}>{{ $org->name }}</option>
        @endforeach
    </select>
</form>
@endif

<div class="bg-white rounded-xl shadow-sm overflow-hidden">
    @if($pendingApprovals->isEmpty())
        <div class="py-14 px-5 text-center text-slate-400">
            <svg width="48" height="48" fill="none" stroke="#cbd5e1" stroke-width="1.5" viewBox="0 0 24 24" class="mx-auto mb-3 block">
                <path d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
            </svg>
            <p class="text-sm font-semibold text-slate-600 m-0 mb-1">Tidak ada pengajuan yang perlu disetujui</p>
            <p class="text-sm m-0">Semua pengajuan sudah diproses atau belum ada yang masuk ke level Anda.</p>
        </div>
    @else
        @foreach($pendingApprovals as $approval)
        @php $fr = $approval->fundRequest; @endphp
        <div class="px-6 py-5 border-b border-slate-50 last:border-0 hover:bg-slate-50 transition-colors">
            <div class="flex items-start justify-between gap-3 flex-wrap">
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="font-mono text-sm font-bold text-orange-500">{{ $fr->reference }}</span>
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-orange-50 border border-orange-200 rounded-lg text-xs font-semibold text-orange-700">
                            <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                            Level {{ $approval->step }}/{{ $fr->total_steps }}
                        </span>
                    </div>
                    <div class="text-base font-semibold text-slate-900 mt-1">{{ $fr->title }}</div>
                    <div class="flex gap-4 flex-wrap mt-2">
                        <div class="flex items-center gap-1.5 text-xs text-slate-500">
                            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                            {{ $fr->organization->name }}
                        </div>
                        <div class="flex items-center gap-1.5 text-xs text-slate-500">
                            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            {{ $fr->requester->name }}
                            <span class="text-slate-400">({{ $fr->requesterPosition->name }})</span>
                        </div>
                        <div class="flex items-center gap-1.5 text-xs text-slate-500">
                            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            {{ $fr->department->name }}
                        </div>
                        <div class="flex items-center gap-1.5 text-xs text-slate-500">
                            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                            Disubmit {{ $fr->submitted_at?->diffForHumans() }}
                        </div>
                    </div>
                </div>
                <div class="flex flex-col items-end gap-2 shrink-0">
                    <div class="inline-flex items-center px-3 py-1 bg-slate-50 border border-slate-200 rounded-lg font-mono text-sm font-bold text-slate-800">Rp {{ number_format($fr->amount, 0, ',', '.') }}</div>
                    <div class="flex gap-1.5">
                        <a href="{{ route('fund-requests.show', $fr) }}" class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-xs font-medium bg-slate-100 text-slate-600 hover:bg-slate-200 transition-colors no-underline">
                            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                            Detail
                        </a>
                        <button type="button" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg text-xs font-semibold bg-gradient-to-br from-green-500 to-green-600 text-white border-0 cursor-pointer hover:-translate-y-px transition-all shadow-sm" onclick="openApproveModal({{ $approval->id }}, '{{ addslashes($fr->reference) }}', '{{ addslashes($fr->title) }}')">
                            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M20 6L9 17l-5-5"/></svg>
                            Setujui
                        </button>
                        <button type="button" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg text-xs font-semibold bg-gradient-to-br from-red-500 to-red-600 text-white border-0 cursor-pointer hover:-translate-y-px transition-all shadow-sm" onclick="openRejectModal({{ $approval->id }}, '{{ addslashes($fr->reference) }}', '{{ addslashes($fr->title) }}')">
                            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M18 6L6 18M6 6l12 12"/></svg>
                            Tolak
                        </button>
                    </div>
                </div>
            </div>
        </div>
        @endforeach

        @if($pendingApprovals->hasPages())
        <div class="px-4 py-3.5 border-t border-slate-100 flex justify-end gap-1">
            @if($pendingApprovals->onFirstPage())
                <span class="inline-flex items-center px-2.5 py-1.5 border border-slate-200 rounded-lg text-xs text-slate-300 pointer-events-none">&laquo;</span>
            @else
                <a href="{{ $pendingApprovals->previousPageUrl() }}" class="inline-flex items-center px-2.5 py-1.5 border border-slate-200 rounded-lg text-xs text-slate-500 no-underline">&laquo;</a>
            @endif
            @foreach($pendingApprovals->getUrlRange(max(1,$pendingApprovals->currentPage()-2), min($pendingApprovals->lastPage(),$pendingApprovals->currentPage()+2)) as $page => $url)
                <a href="{{ $url }}" class="inline-flex items-center px-2.5 py-1.5 border rounded-lg text-xs no-underline {{ $page == $pendingApprovals->currentPage() ? 'bg-orange-500 border-orange-500 text-white' : 'border-slate-200 text-slate-500' }}">{{ $page }}</a>
            @endforeach
            @if($pendingApprovals->hasMorePages())
                <a href="{{ $pendingApprovals->nextPageUrl() }}" class="inline-flex items-center px-2.5 py-1.5 border border-slate-200 rounded-lg text-xs text-slate-500 no-underline">&raquo;</a>
            @else
                <span class="inline-flex items-center px-2.5 py-1.5 border border-slate-200 rounded-lg text-xs text-slate-300 pointer-events-none">&raquo;</span>
            @endif
        </div>
        @endif
    @endif
</div>

{{-- Approve Modal --}}
<div class="fixed inset-0 z-[999] bg-slate-900/50 backdrop-blur-sm items-center justify-center" id="approve-overlay" style="display:none;">
    <div class="bg-white rounded-2xl w-[420px] max-w-[90vw] shadow-2xl overflow-hidden">
        <div class="px-6 py-5 border-b border-slate-100">
            <div class="text-base font-bold text-green-700">Setujui Pengajuan</div>
            <div id="approve-ref" class="text-xs text-slate-500 mt-1"></div>
        </div>
        <form id="approve-form" method="POST" action="">
            @csrf
            <div class="px-6 py-5">
                <label class="text-xs font-semibold text-slate-600 block mb-1.5">Catatan (opsional)</label>
                <textarea name="notes" rows="3" class="w-full px-3 py-2.5 border border-slate-200 rounded-xl text-sm outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100 transition-colors resize-y" placeholder="Tambahkan catatan persetujuan..."></textarea>
            </div>
            <div class="px-6 py-4 border-t border-slate-100 flex gap-2 justify-end">
                <button type="button" class="px-4 py-2.5 rounded-xl bg-slate-100 text-slate-700 border border-slate-200 text-sm font-medium cursor-pointer" onclick="closeModals()">Batal</button>
                <button type="submit" class="inline-flex items-center gap-1.5 px-5 py-2.5 rounded-xl text-sm font-semibold bg-gradient-to-br from-green-500 to-green-600 text-white border-0 cursor-pointer hover:-translate-y-px transition-all shadow-sm">
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
            <div id="reject-ref" class="text-xs text-slate-500 mt-1"></div>
        </div>
        <form id="reject-form" method="POST" action="">
            @csrf
            <div class="px-6 py-5">
                <label class="text-xs font-semibold text-slate-600 block mb-1.5">Alasan Penolakan <span class="text-red-500">*</span></label>
                <textarea name="notes" rows="3" class="w-full px-3 py-2.5 border border-slate-200 rounded-xl text-sm outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100 transition-colors resize-y" placeholder="Jelaskan alasan penolakan..." required></textarea>
                <div class="text-[11px] text-slate-400 mt-1.5">Wajib diisi untuk penolakan.</div>
            </div>
            <div class="px-6 py-4 border-t border-slate-100 flex gap-2 justify-end">
                <button type="button" class="px-4 py-2.5 rounded-xl bg-slate-100 text-slate-700 border border-slate-200 text-sm font-medium cursor-pointer" onclick="closeModals()">Batal</button>
                <button type="submit" class="inline-flex items-center gap-1.5 px-5 py-2.5 rounded-xl text-sm font-semibold bg-gradient-to-br from-red-500 to-red-600 text-white border-0 cursor-pointer hover:-translate-y-px transition-all shadow-sm">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M18 6L6 18M6 6l12 12"/></svg>
                    Ya, Tolak
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openApproveModal(approvalId, ref, title) {
    document.getElementById('approve-form').action = `/fund-approvals/${approvalId}/approve`;
    document.getElementById('approve-ref').textContent = ref + ' — ' + title;
    const ov = document.getElementById('approve-overlay');
    ov.style.display = 'flex';
}
function openRejectModal(approvalId, ref, title) {
    document.getElementById('reject-form').action = `/fund-approvals/${approvalId}/reject`;
    document.getElementById('reject-ref').textContent = ref + ' — ' + title;
    const ov = document.getElementById('reject-overlay');
    ov.style.display = 'flex';
}
function closeModals() {
    document.getElementById('approve-overlay').style.display = 'none';
    document.getElementById('reject-overlay').style.display = 'none';
}
document.querySelectorAll('.modal-overlay').forEach(ov => {
    ov.addEventListener('click', e => { if (e.target === ov) closeModals(); });
});
document.addEventListener('keydown', e => { if (e.key === 'Escape') closeModals(); });
</script>
</x-layouts.app>
