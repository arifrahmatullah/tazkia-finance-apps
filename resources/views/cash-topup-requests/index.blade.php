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
        <button type="button" id="tour-open-guide"
            class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-sm font-semibold bg-white border border-slate-200 text-slate-600 hover:bg-slate-50 transition-colors">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 015.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
            Panduan
        </button>
        @if($canRequest)
        <a href="{{ route('cash-topup-requests.create') }}" id="tour-btn-request"
            class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-sm font-semibold bg-gradient-to-br from-blue-500 to-blue-600 text-white no-underline hover:opacity-90 transition-opacity shadow-sm">
            + Ajukan Saldo
        </a>
        @endif
        @if($canApprove)
        <a href="{{ route('cash-topup-requests.direct-create') }}" id="tour-btn-direct"
            class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-sm font-semibold bg-gradient-to-br from-green-500 to-green-600 text-white no-underline hover:opacity-90 transition-opacity shadow-sm">
            + Isi Saldo Langsung
        </a>
        @endif
    </div>
</div>

<form method="GET" action="{{ route('cash-topup-requests.index') }}" id="tour-filter" class="bg-white rounded-xl shadow-sm p-4 mb-4 flex flex-wrap gap-3 items-end">
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
<div class="flex flex-col gap-3" id="tour-list">
    @php $tourActionsMarked = false; @endphp
    @foreach($topups as $topup)
    @php
        $statusStyle = [
            'pending'  => ['bg' => 'bg-orange-100', 'text' => 'text-orange-700', 'label' => 'Menunggu Approval', 'stripe' => 'from-orange-400 to-orange-500'],
            'approved' => ['bg' => 'bg-green-100', 'text' => 'text-green-700', 'label' => 'Disetujui', 'stripe' => 'from-green-400 to-green-500'],
            'rejected' => ['bg' => 'bg-red-100', 'text' => 'text-red-600', 'label' => 'Ditolak', 'stripe' => 'from-red-400 to-red-500'],
        ][$topup->status];
        $rowParentId  = $topup->requestingOrganization->parent_id;
        $rowCanApprove = $topup->isPending() && $rowParentId
            && (auth()->user()->isSuperAdmin() || auth()->user()->canAccessOrganization($rowParentId));
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
                @if($rowCanApprove)
                <span @if(!$tourActionsMarked) id="tour-actions" @endif class="contents">
                    <a href="{{ route('cash-topup-requests.show', $topup) }}?action=reject"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold bg-gradient-to-br from-red-500 to-red-600 text-white hover:opacity-90 transition-opacity no-underline">
                        Tolak
                    </a>
                    <a href="{{ route('cash-topup-requests.show', $topup) }}?action=approve"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold bg-gradient-to-br from-green-500 to-green-600 text-white hover:opacity-90 transition-opacity no-underline">
                        Setujui
                    </a>
                </span>
                @php $tourActionsMarked = true; @endphp
                @endif
            </div>
        </div>
    </div>
    @endforeach
</div>

<div class="mt-4">
    {{ $topups->links() }}
</div>
@endif

{{-- ─── Panduan: modal ringkasan + tur interaktif (highlight elemen satu-satu) ─── --}}
<div class="fixed inset-0 z-[200] items-center justify-center p-4 bg-slate-900/50" id="tour-guide-modal" style="display:none;">
    <div class="bg-white rounded-2xl w-full max-w-lg max-h-[85vh] flex flex-col shadow-2xl">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between flex-shrink-0">
            <div>
                <div class="text-sm font-bold text-slate-900">Panduan Pengajuan Saldo</div>
                <p class="text-xs text-slate-400 mt-0.5">Ringkasan cara pakai halaman ini</p>
            </div>
            <button type="button" id="tour-guide-close" class="text-slate-400 hover:text-slate-600 border-0 bg-transparent cursor-pointer">
                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M18 6L6 18M6 6l12 12"/></svg>
            </button>
        </div>
        <div class="flex-1 overflow-y-auto px-6 py-5 flex flex-col gap-4" id="tour-guide-steps"></div>
        <div class="px-6 py-4 border-t border-slate-100 flex justify-end flex-shrink-0">
            <button type="button" id="tour-guide-start"
                class="inline-flex items-center gap-1.5 px-5 py-2.5 rounded-xl text-sm font-semibold bg-gradient-to-br from-blue-500 to-blue-600 text-white border-0 cursor-pointer hover:opacity-90 transition-opacity shadow-sm">
                Mulai Tur Interaktif
            </button>
        </div>
    </div>
</div>

<div class="fixed inset-0 z-[210] pointer-events-none" id="tour-overlay" style="display:none;">
    <div id="tour-dim-top" class="absolute inset-x-0 top-0 bg-black/50"></div>
    <div id="tour-dim-bottom" class="absolute inset-x-0 bottom-0 bg-black/50"></div>
    <div id="tour-dim-left" class="absolute left-0 bg-black/50"></div>
    <div id="tour-dim-right" class="absolute right-0 bg-black/50"></div>
    <div id="tour-highlight-ring" class="absolute rounded-xl ring-2 ring-blue-400 shadow-[0_0_0_4px_rgba(96,165,250,0.35)]" style="display:none;"></div>

    <div class="pointer-events-auto" id="tour-tooltip" style="position:fixed;">
        <div class="bg-white rounded-2xl shadow-2xl overflow-hidden w-[340px] max-w-[92vw]">
            <div class="h-1 bg-slate-100">
                <div class="h-1 bg-blue-500 transition-all duration-300" id="tour-progress-bar" style="width:0%"></div>
            </div>
            <div class="px-5 pt-4 pb-3">
                <div class="flex items-center gap-2 mb-2">
                    <span class="text-[11px] font-bold text-blue-600 bg-blue-50 px-2 py-0.5 rounded-full" id="tour-step-count">1/1</span>
                    <span class="text-[11px] text-slate-400">Panduan Pengajuan Saldo</span>
                </div>
                <h3 class="text-sm font-bold text-slate-800 mb-1.5" id="tour-step-title"></h3>
                <p class="text-xs text-slate-600 leading-relaxed" id="tour-step-body"></p>
            </div>
            <div class="px-5 pb-4 flex items-center justify-between">
                <button type="button" id="tour-skip" class="text-xs text-slate-400 hover:text-slate-600 border-0 bg-transparent cursor-pointer">Lewati</button>
                <div class="flex items-center gap-2">
                    <button type="button" id="tour-prev" class="px-3 py-1.5 border border-slate-200 text-slate-600 rounded-lg text-xs bg-white cursor-pointer hover:bg-slate-50 transition-colors">&larr; Sebelumnya</button>
                    <button type="button" id="tour-next" class="px-3 py-1.5 bg-blue-600 text-white rounded-lg text-xs font-medium border-0 cursor-pointer hover:bg-blue-700 transition-colors">Berikutnya &rarr;</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    var tourSteps = [
        { title: 'Selamat Datang di Pengajuan Saldo', body: 'Halaman ini buat mengajukan (Kampus/STMIK) atau mengisi (Yayasan) tambahan saldo rekening antar organisasi. Yuk lihat bagian-bagiannya.', targetId: '' },
        { title: 'Panduan & Aksi Utama', body: 'Tombol-tombol di sini: "Ajukan Saldo" (Kampus/STMIK minta tambahan saldo ke Yayasan) dan "Isi Saldo Langsung" (Yayasan langsung mengisi tanpa menunggu permintaan).', targetId: 'tour-btn-request' },
        { title: 'Isi Saldo Langsung', body: 'Khusus Yayasan: klik ini buat langsung mengisi saldo Kampus/STMIK tanpa perlu ada pengajuan dulu -- langsung efektif begitu disubmit.', targetId: 'tour-btn-direct' },
        { title: 'Filter Status', body: 'Saring daftar berdasarkan status: Menunggu Approval, Disetujui, atau Ditolak.', targetId: 'tour-filter' },
        { title: 'Daftar Pengajuan', body: 'Setiap kartu menampilkan referensi, nominal, organisasi tujuan, rekening tujuan, dan siapa yang mengajukan/mengisi.', targetId: 'tour-list' },
        { title: 'Setujui / Tolak Langsung', body: 'Khusus Yayasan: pengajuan yang masih menunggu approval bisa langsung disetujui atau ditolak dari sini, tanpa perlu buka halaman detail dulu.', targetId: 'tour-actions' },
        { title: 'Selesai!', body: 'Klik "Lihat Detail" kapan saja untuk melihat rincian lengkap, riwayat, dan bukti transfer tiap pengajuan.', targetId: '' },
    ].filter(function (s) { return !s.targetId || document.getElementById(s.targetId); });

    var guideModal   = document.getElementById('tour-guide-modal');
    var guideSteps   = document.getElementById('tour-guide-steps');
    var overlay      = document.getElementById('tour-overlay');
    var tooltip      = document.getElementById('tour-tooltip');
    var ring         = document.getElementById('tour-highlight-ring');
    var dimTop       = document.getElementById('tour-dim-top');
    var dimBottom    = document.getElementById('tour-dim-bottom');
    var dimLeft      = document.getElementById('tour-dim-left');
    var dimRight     = document.getElementById('tour-dim-right');
    var stepCount    = document.getElementById('tour-step-count');
    var stepTitle    = document.getElementById('tour-step-title');
    var stepBody     = document.getElementById('tour-step-body');
    var progressBar  = document.getElementById('tour-progress-bar');
    var prevBtn      = document.getElementById('tour-prev');
    var nextBtn      = document.getElementById('tour-next');
    var current      = 0;

    guideSteps.innerHTML = tourSteps.map(function (s, i) {
        return '<div class="flex gap-3">' +
            '<div class="flex-shrink-0 w-7 h-7 rounded-full bg-blue-600 text-white text-xs font-bold flex items-center justify-center">' + (i + 1) + '</div>' +
            '<div class="flex-1"><h4 class="text-sm font-semibold text-slate-800 mb-0.5">' + s.title + '</h4>' +
            '<p class="text-xs text-slate-500 leading-relaxed">' + s.body + '</p></div></div>';
    }).join('');

    function openGuideModal() { guideModal.style.display = 'flex'; }
    function closeGuideModal() { guideModal.style.display = 'none'; }

    function positionOverlay(targetId) {
        if (!targetId) {
            ring.style.display = 'none';
            dimTop.style.height = '100%'; dimTop.style.top = '0';
            dimBottom.style.height = '0'; dimLeft.style.width = '0'; dimRight.style.width = '0';
            tooltip.style.top = '50%'; tooltip.style.left = '50%'; tooltip.style.transform = 'translate(-50%, -50%)';
            return;
        }
        var el = document.getElementById(targetId);
        if (!el) { positionOverlay(''); return; }
        el.scrollIntoView({ behavior: 'smooth', block: 'center' });
        setTimeout(function () {
            var r = el.getBoundingClientRect();
            var pad = 8;
            var top = r.top - pad, left = r.left - pad, width = r.width + pad * 2, height = r.height + pad * 2;

            dimTop.style.top = '0'; dimTop.style.height = top + 'px';
            dimBottom.style.top = (top + height) + 'px'; dimBottom.style.height = 'auto'; dimBottom.style.bottom = '0';
            dimLeft.style.top = top + 'px'; dimLeft.style.height = height + 'px'; dimLeft.style.width = left + 'px';
            dimRight.style.top = top + 'px'; dimRight.style.height = height + 'px'; dimRight.style.left = (left + width) + 'px'; dimRight.style.width = 'auto'; dimRight.style.right = '0';

            ring.style.display = 'block';
            ring.style.top = top + 'px'; ring.style.left = left + 'px'; ring.style.width = width + 'px'; ring.style.height = height + 'px';

            var tw = 340;
            var below = top + height + 12;
            var above = top - 12 - 200;
            var useAbove = below + 200 > window.innerHeight - 16;
            tooltip.style.transform = 'none';
            tooltip.style.top = (useAbove ? Math.max(8, above) : below) + 'px';
            tooltip.style.left = Math.min(Math.max(8, left + width / 2 - tw / 2), window.innerWidth - tw - 8) + 'px';
        }, 350);
    }

    function renderStep() {
        var s = tourSteps[current];
        stepCount.textContent = (current + 1) + '/' + tourSteps.length;
        stepTitle.textContent = s.title;
        stepBody.textContent = s.body;
        progressBar.style.width = ((current + 1) / tourSteps.length * 100) + '%';
        prevBtn.style.display = current === 0 ? 'none' : 'inline-block';
        nextBtn.textContent = current === tourSteps.length - 1 ? 'Selesai' : 'Berikutnya →';
        positionOverlay(s.targetId);
    }

    function startTour() {
        closeGuideModal();
        current = 0;
        overlay.style.display = 'block';
        renderStep();
    }

    function closeTour() { overlay.style.display = 'none'; }

    document.getElementById('tour-open-guide').addEventListener('click', openGuideModal);
    document.getElementById('tour-guide-close').addEventListener('click', closeGuideModal);
    document.getElementById('tour-guide-start').addEventListener('click', startTour);
    document.getElementById('tour-skip').addEventListener('click', closeTour);

    nextBtn.addEventListener('click', function () {
        if (current < tourSteps.length - 1) { current++; renderStep(); }
        else { closeTour(); }
    });
    prevBtn.addEventListener('click', function () {
        if (current > 0) { current--; renderStep(); }
    });

    guideModal.addEventListener('click', function (e) { if (e.target === e.currentTarget) closeGuideModal(); });
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') { closeGuideModal(); closeTour(); }
    });
})();
</script>

</x-layouts.app>
