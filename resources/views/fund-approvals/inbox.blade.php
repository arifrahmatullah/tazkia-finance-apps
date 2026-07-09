<x-layouts.app title="Inbox Approval">

@if(session('success'))
<div class="flex items-center gap-2.5 px-4 py-3 bg-green-50 border border-green-200 rounded-xl mb-4 text-sm text-green-700">
    <svg width="16" height="16" fill="#16a34a" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
    {{ session('success') }}
</div>
@endif

@if(session('error'))
<div class="flex items-center gap-2.5 px-4 py-3 bg-red-50 border border-red-200 rounded-xl mb-4 text-sm text-red-700">
    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
    {{ session('error') }}
</div>
@endif

{{-- Header --}}
<div class="flex items-start justify-between gap-4 mb-5 flex-wrap">
    <div>
        <h2 class="text-lg font-bold text-slate-900 m-0 mb-1">Inbox Approval</h2>
        <p class="text-xs text-slate-400 m-0">Pengajuan yang menunggu atau sudah diproses oleh Anda</p>
    </div>
    @if(isset($positionName) && $positionName)
    <div class="inline-flex items-center gap-2 px-3.5 py-2 bg-orange-50 border border-orange-200 rounded-xl">
        <div class="w-7 h-7 rounded-lg bg-orange-100 flex items-center justify-center flex-shrink-0">
            <svg width="14" height="14" fill="none" stroke="#ea580c" stroke-width="2" viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
        </div>
        <div>
            <div class="text-[10px] text-orange-500 font-semibold uppercase tracking-wider">Jabatan Anda</div>
            <div class="text-sm font-bold text-orange-700 leading-tight">{{ $positionName }}</div>
        </div>
    </div>
    @endif
</div>

@if(!isset($positionName) || !$positionName)
<div class="flex items-center gap-2.5 px-4 py-3.5 bg-yellow-50 border border-yellow-200 rounded-xl mb-5 text-sm text-yellow-700">
    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" class="flex-shrink-0"><path d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
    Akun Anda tidak memiliki jabatan aktif. Hubungi HRD untuk mengatur jabatan.
</div>
@else

{{-- Filter Bar --}}
<form method="GET" action="{{ route('fund-approvals.inbox') }}" class="bg-white rounded-xl shadow-sm p-4 mb-4 flex flex-wrap gap-3 items-end">
    <input type="hidden" name="status" value="{{ $filterStatus }}">

    {{-- Search --}}
    <div class="flex-1 min-w-[200px]">
        <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block mb-1.5">Cari</label>
        <div class="relative">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
            <input type="text" name="search" value="{{ request('search') }}"
                placeholder="Referensi atau judul pengajuan..."
                class="w-full pl-9 pr-3 py-2 border border-slate-200 rounded-lg text-sm outline-none focus:border-orange-400 transition-colors">
        </div>
    </div>

    {{-- Org filter (multi-org users) --}}
    @if(isset($organizations) && $organizations->count() > 1)
    <div class="min-w-[200px]">
        <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block mb-1.5">Organisasi</label>
        <select name="organization_id" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm text-slate-700 bg-white outline-none focus:border-orange-400 transition-colors">
            <option value="">Semua Organisasi</option>
            @foreach($organizations as $org)
                <option value="{{ $org->id }}" {{ request('organization_id') == $org->id ? 'selected' : '' }}>{{ $org->name }}</option>
            @endforeach
        </select>
    </div>
    @endif

    <div class="flex gap-2">
        <button type="submit" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-sm font-semibold bg-orange-500 text-white border-0 cursor-pointer hover:bg-orange-600 transition-colors">
            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
            Cari
        </button>
        @if(request('search') || request('organization_id'))
        <a href="{{ route('fund-approvals.inbox', ['status' => $filterStatus]) }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-sm font-medium bg-slate-100 text-slate-600 no-underline hover:bg-slate-200 transition-colors">
            Reset
        </a>
        @endif
    </div>
</form>

{{-- Status Tabs --}}
<div class="flex gap-0.5 mb-4 bg-white rounded-xl shadow-sm p-1.5">
    @php
        $tabs = [
            'waiting'  => ['label' => 'Menunggu',  'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z', 'active' => 'bg-orange-500 text-white shadow-sm', 'inactive' => 'text-slate-500 hover:bg-slate-100'],
            'approved' => ['label' => 'Disetujui', 'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z', 'active' => 'bg-green-500 text-white shadow-sm',  'inactive' => 'text-slate-500 hover:bg-slate-100'],
            'rejected' => ['label' => 'Ditolak',   'icon' => 'M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z', 'active' => 'bg-red-500 text-white shadow-sm', 'inactive' => 'text-slate-500 hover:bg-slate-100'],
        ];
    @endphp
    @foreach($tabs as $key => $tab)
    <a href="{{ route('fund-approvals.inbox', array_filter(['status' => $key, 'search' => request('search'), 'organization_id' => request('organization_id')])) }}"
       class="flex-1 inline-flex items-center justify-center gap-1.5 px-4 py-2 rounded-lg text-sm font-semibold transition-all no-underline {{ $filterStatus === $key ? $tab['active'] : $tab['inactive'] }}">
        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $tab['icon'] }}"/></svg>
        {{ $tab['label'] }}
    </a>
    @endforeach
</div>

@if($approvals->isEmpty())
{{-- Empty state --}}
<div class="bg-white rounded-xl shadow-sm py-16 px-5 text-center">
    <div class="w-16 h-16 rounded-2xl bg-slate-50 border border-slate-100 flex items-center justify-center mx-auto mb-4">
        <svg width="28" height="28" fill="none" stroke="#94a3b8" stroke-width="1.5" viewBox="0 0 24 24">
            <path d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
        </svg>
    </div>
    @if($filterStatus === 'waiting')
    <div class="text-sm font-semibold text-slate-700 mb-1">Tidak ada pengajuan yang perlu disetujui</div>
    <div class="text-xs text-slate-400">Semua pengajuan sudah diproses atau belum ada yang masuk ke level Anda.</div>
    @elseif($filterStatus === 'approved')
    <div class="text-sm font-semibold text-slate-700 mb-1">Belum ada pengajuan yang Anda setujui</div>
    <div class="text-xs text-slate-400">Riwayat pengajuan yang Anda setujui akan muncul di sini.</div>
    @else
    <div class="text-sm font-semibold text-slate-700 mb-1">Belum ada pengajuan yang Anda tolak</div>
    <div class="text-xs text-slate-400">Riwayat pengajuan yang Anda tolak akan muncul di sini.</div>
    @endif
</div>

@else

{{-- Summary bar --}}
@if($filterStatus === 'waiting')
<div class="grid grid-cols-2 gap-3 mb-4 sm:grid-cols-4">
    <div class="bg-white rounded-xl shadow-sm px-4 py-3.5 col-span-2">
        <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-0.5">Menunggu Keputusan</div>
        <div class="text-2xl font-extrabold text-orange-500">{{ $approvals->total() }}</div>
        <div class="text-xs text-slate-400 mt-0.5">pengajuan</div>
    </div>
    <div class="bg-white rounded-xl shadow-sm px-4 py-3.5 col-span-2">
        <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-0.5">Total Dana Diajukan</div>
        <div class="text-lg font-extrabold text-slate-800 font-mono leading-tight">
            Rp {{ number_format($approvals->getCollection()->sum(fn($a) => $a->fundRequest->amount), 0, ',', '.') }}
        </div>
        <div class="text-xs text-slate-400 mt-0.5">halaman ini</div>
    </div>
</div>
@endif

{{-- List --}}
<div class="flex flex-col gap-3">
    @foreach($approvals as $approval)
    @php $fr = $approval->fundRequest; @endphp
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        {{-- Top bar --}}
        <div class="flex items-center justify-between px-5 pt-4 pb-0 gap-3 flex-wrap">
            <div class="flex items-center gap-2 flex-wrap">
                <span class="font-mono text-sm font-bold text-orange-500">{{ $fr->reference }}</span>
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-yellow-100 text-yellow-700 uppercase tracking-wide">
                    Level {{ $approval->step }}/{{ $fr->total_steps }}
                </span>
                @if($filterStatus === 'approved')
                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-green-100 text-green-700">
                    <svg width="9" height="9" fill="#16a34a" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                    Disetujui
                </span>
                @elseif($filterStatus === 'rejected')
                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-red-100 text-red-600">
                    <svg width="9" height="9" fill="#dc2626" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
                    Ditolak
                </span>
                @endif
            </div>
            <span class="text-[11px] text-slate-400">
                @if($filterStatus === 'waiting')
                    {{ $fr->submitted_at?->diffForHumans() }}
                @else
                    {{ $approval->acted_at?->format('d/m/Y H:i') }}
                @endif
            </span>
        </div>

        {{-- Content --}}
        <div class="px-5 pt-2.5 pb-4">
            <div class="text-[15px] font-bold text-slate-900 mb-3 leading-snug">{{ $fr->title }}</div>

            <div class="grid grid-cols-2 gap-x-6 gap-y-2 mb-4 sm:grid-cols-4">
                <div>
                    <div class="text-[10px] font-semibold text-slate-400 uppercase tracking-wide mb-0.5">Pengaju</div>
                    <div class="text-xs font-semibold text-slate-800">{{ $fr->requester->name }}</div>
                    <div class="text-[11px] text-slate-400">{{ $fr->requesterPosition->name }}</div>
                </div>
                <div>
                    <div class="text-[10px] font-semibold text-slate-400 uppercase tracking-wide mb-0.5">Departemen</div>
                    <div class="text-xs font-semibold text-slate-800">{{ $fr->department->name }}</div>
                </div>
                <div>
                    <div class="text-[10px] font-semibold text-slate-400 uppercase tracking-wide mb-0.5">Organisasi</div>
                    <div class="text-xs font-semibold text-slate-800">{{ $fr->organization->name }}</div>
                </div>
                <div>
                    <div class="text-[10px] font-semibold text-slate-400 uppercase tracking-wide mb-0.5">Jumlah Dana</div>
                    <div class="text-sm font-extrabold text-slate-900 font-mono">Rp {{ number_format($fr->amount, 0, ',', '.') }}</div>
                </div>
            </div>

            @if($filterStatus !== 'waiting' && $approval->notes)
            <div class="mb-3 px-3 py-2 {{ $filterStatus === 'approved' ? 'bg-green-50 border border-green-100' : 'bg-red-50 border border-red-100' }} rounded-lg text-xs text-slate-700">
                <span class="font-semibold {{ $filterStatus === 'approved' ? 'text-green-700' : 'text-red-600' }}">Catatan: </span>{{ $approval->notes }}
            </div>
            @endif

            {{-- Actions --}}
            <div class="flex items-center gap-2 pt-3 border-t border-slate-100 flex-wrap">
                <a href="{{ route('fund-requests.show', $fr) }}"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold bg-slate-100 text-slate-600 hover:bg-slate-200 transition-colors no-underline">
                    <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                    Lihat Detail
                </a>
                @if($filterStatus === 'waiting')
                <button type="button"
                    class="btn-approve inline-flex items-center gap-1.5 px-4 py-1.5 rounded-lg text-xs font-semibold bg-gradient-to-br from-green-500 to-green-600 text-white border-0 cursor-pointer hover:opacity-90 transition-opacity shadow-sm"
                    data-approve-url="{{ route('fund-approvals.approve', $approval->id) }}"
                    data-ref="{{ $fr->reference }}"
                    data-title="{{ $fr->title }}">
                    <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M20 6L9 17l-5-5"/></svg>
                    Setujui
                </button>
                <button type="button"
                    class="btn-reject inline-flex items-center gap-1.5 px-4 py-1.5 rounded-lg text-xs font-semibold bg-gradient-to-br from-red-500 to-red-600 text-white border-0 cursor-pointer hover:opacity-90 transition-opacity shadow-sm"
                    data-reject-url="{{ route('fund-approvals.reject', $approval->id) }}"
                    data-ref="{{ $fr->reference }}"
                    data-title="{{ $fr->title }}">
                    <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M18 6L6 18M6 6l12 12"/></svg>
                    Tolak
                </button>
                @endif
            </div>
        </div>
    </div>
    @endforeach
</div>

{{-- Pagination --}}
@if($approvals->hasPages())
<div class="mt-4 flex justify-end gap-1">
    @if($approvals->onFirstPage())
        <span class="inline-flex items-center px-2.5 py-1.5 border border-slate-200 rounded-lg text-xs text-slate-300 pointer-events-none">&laquo;</span>
    @else
        <a href="{{ $approvals->previousPageUrl() }}" class="inline-flex items-center px-2.5 py-1.5 border border-slate-200 rounded-lg text-xs text-slate-500 no-underline bg-white">&laquo;</a>
    @endif
    @foreach($approvals->getUrlRange(max(1,$approvals->currentPage()-2), min($approvals->lastPage(),$approvals->currentPage()+2)) as $page => $url)
        <a href="{{ $url }}" class="inline-flex items-center px-2.5 py-1.5 border rounded-lg text-xs no-underline {{ $page == $approvals->currentPage() ? 'bg-orange-500 border-orange-500 text-white' : 'bg-white border-slate-200 text-slate-500' }}">{{ $page }}</a>
    @endforeach
    @if($approvals->hasMorePages())
        <a href="{{ $approvals->nextPageUrl() }}" class="inline-flex items-center px-2.5 py-1.5 border border-slate-200 rounded-lg text-xs text-slate-500 no-underline bg-white">&raquo;</a>
    @else
        <span class="inline-flex items-center px-2.5 py-1.5 border border-slate-200 rounded-lg text-xs text-slate-300 pointer-events-none">&raquo;</span>
    @endif
</div>
@endif
@endif
@endif

{{-- Approve Modal --}}
<div class="fixed inset-0 z-[999] bg-slate-900/50 backdrop-blur-sm items-center justify-center" id="approve-overlay" style="display:none;">
    <div class="bg-white rounded-2xl w-[420px] max-w-[90vw] shadow-2xl overflow-hidden">
        <div class="px-6 py-5 border-b border-slate-100 flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-green-100 flex items-center justify-center flex-shrink-0">
                <svg width="18" height="18" fill="none" stroke="#16a34a" stroke-width="2.5" viewBox="0 0 24 24"><path d="M20 6L9 17l-5-5"/></svg>
            </div>
            <div>
                <div class="text-sm font-bold text-green-700">Setujui Pengajuan</div>
                <div id="approve-ref" class="text-[11px] text-slate-500 mt-0.5"></div>
            </div>
        </div>
        <form id="approve-form" method="POST" action="">
            @csrf
            <div class="px-6 py-5">
                <label class="text-xs font-semibold text-slate-600 block mb-1.5">Catatan <span class="text-slate-400 font-normal">(opsional)</span></label>
                <textarea name="notes" rows="3" class="w-full px-3 py-2.5 border border-slate-200 rounded-xl text-sm outline-none focus:border-green-400 focus:ring-2 focus:ring-green-100 transition-colors resize-none" placeholder="Tambahkan catatan persetujuan..."></textarea>
            </div>
            <div class="px-6 py-4 border-t border-slate-100 flex gap-2 justify-end">
                <button type="button" id="approve-cancel" class="px-4 py-2.5 rounded-xl bg-slate-100 text-slate-700 border border-slate-200 text-sm font-medium cursor-pointer hover:bg-slate-200 transition-colors">Batal</button>
                <button type="submit" class="inline-flex items-center gap-1.5 px-5 py-2.5 rounded-xl text-sm font-semibold bg-gradient-to-br from-green-500 to-green-600 text-white border-0 cursor-pointer hover:opacity-90 transition-opacity shadow-sm">
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
        <div class="px-6 py-5 border-b border-slate-100 flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-red-100 flex items-center justify-center flex-shrink-0">
                <svg width="18" height="18" fill="none" stroke="#dc2626" stroke-width="2.5" viewBox="0 0 24 24"><path d="M18 6L6 18M6 6l12 12"/></svg>
            </div>
            <div>
                <div class="text-sm font-bold text-red-600">Tolak Pengajuan</div>
                <div id="reject-ref" class="text-[11px] text-slate-500 mt-0.5"></div>
            </div>
        </div>
        <form id="reject-form" method="POST" action="">
            @csrf
            <div class="px-6 py-5">
                <label class="text-xs font-semibold text-slate-600 block mb-1.5">Alasan Penolakan <span class="text-red-500">*</span></label>
                <textarea name="notes" rows="3" class="w-full px-3 py-2.5 border border-slate-200 rounded-xl text-sm outline-none focus:border-red-400 focus:ring-2 focus:ring-red-100 transition-colors resize-none" placeholder="Jelaskan alasan penolakan..." required></textarea>
                <div class="text-[11px] text-slate-400 mt-1.5">Catatan wajib diisi untuk penolakan.</div>
            </div>
            <div class="px-6 py-4 border-t border-slate-100 flex gap-2 justify-end">
                <button type="button" id="reject-cancel" class="px-4 py-2.5 rounded-xl bg-slate-100 text-slate-700 border border-slate-200 text-sm font-medium cursor-pointer hover:bg-slate-200 transition-colors">Batal</button>
                <button type="submit" class="inline-flex items-center gap-1.5 px-5 py-2.5 rounded-xl text-sm font-semibold bg-gradient-to-br from-red-500 to-red-600 text-white border-0 cursor-pointer hover:opacity-90 transition-opacity shadow-sm">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M18 6L6 18M6 6l12 12"/></svg>
                    Ya, Tolak
                </button>
            </div>
        </form>
    </div>
</div>

<script>
(function() {
    var approveOverlay = document.getElementById('approve-overlay');
    var rejectOverlay  = document.getElementById('reject-overlay');
    var approveForm    = document.getElementById('approve-form');
    var rejectForm     = document.getElementById('reject-form');

    function closeModals() {
        approveOverlay.style.display = 'none';
        rejectOverlay.style.display  = 'none';
        approveForm.querySelector('textarea').value = '';
        rejectForm.querySelector('textarea').value  = '';
    }

    document.querySelectorAll('.btn-approve').forEach(function(btn) {
        btn.addEventListener('click', function() {
            approveForm.action = btn.dataset.approveUrl;
            document.getElementById('approve-ref').textContent = btn.dataset.ref + ' — ' + btn.dataset.title;
            approveOverlay.style.display = 'flex';
        });
    });

    document.querySelectorAll('.btn-reject').forEach(function(btn) {
        btn.addEventListener('click', function() {
            rejectForm.action = btn.dataset.rejectUrl;
            document.getElementById('reject-ref').textContent = btn.dataset.ref + ' — ' + btn.dataset.title;
            rejectOverlay.style.display = 'flex';
        });
    });

    document.getElementById('approve-cancel').addEventListener('click', closeModals);
    document.getElementById('reject-cancel').addEventListener('click', closeModals);

    approveOverlay.addEventListener('click', function(e) { if (e.target === e.currentTarget) closeModals(); });
    rejectOverlay.addEventListener('click',  function(e) { if (e.target === e.currentTarget) closeModals(); });
    document.addEventListener('keydown', function(e) { if (e.key === 'Escape') closeModals(); });
})();
</script>
</x-layouts.app>
