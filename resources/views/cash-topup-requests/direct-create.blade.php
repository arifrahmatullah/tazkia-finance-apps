<x-layouts.app title="Isi Saldo Langsung">

@if($errors->any())
<div class="px-4 py-3 bg-red-50 border border-red-200 rounded-xl mb-4 text-sm text-red-700">
    @foreach($errors->all() as $error)
        <div>{{ $error }}</div>
    @endforeach
    @if(session('insufficientBalanceOrgId'))
    <a href="{{ route('beginning-balances.index', ['organization_id' => session('insufficientBalanceOrgId')]) }}"
        class="inline-block mt-1 font-semibold underline">Isi Saldo Awal rekening ini &rarr;</a>
    @endif
</div>
@endif

<div class="flex items-start justify-between gap-4 mb-5 flex-wrap">
    <div>
        <h2 class="text-lg font-bold text-slate-900 m-0 mb-1">Isi Saldo Langsung</h2>
        <p class="text-xs text-slate-400 m-0">Yayasan menambah saldo rekening Kampus/STMIK tanpa menunggu pengajuan -- langsung efektif begitu dikirim.</p>
    </div>
    <div class="flex gap-2">
        <button type="button" id="tour-open-guide"
            class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-sm font-semibold bg-white border border-slate-200 text-slate-600 hover:bg-slate-50 transition-colors">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 015.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
            Panduan
        </button>
        <a href="{{ route('cash-topup-requests.index') }}"
            class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-sm font-medium bg-slate-100 text-slate-600 no-underline hover:bg-slate-200 transition-colors">
            &larr; Kembali
        </a>
    </div>
</div>

<form method="POST" action="{{ route('cash-topup-requests.direct-store') }}" enctype="multipart/form-data" class="flex flex-col gap-4">
    @csrf

    <div class="bg-white rounded-xl shadow-sm p-5" id="tour-tujuan">
        <div class="text-sm font-bold text-slate-900 mb-3">Tujuan</div>

        @if($childOrgs->count() > 1)
        <div class="mb-4">
            <label class="text-xs font-semibold text-slate-600 block mb-1.5">Organisasi Tujuan <span class="text-red-500">*</span></label>
            <select name="organization_id" id="organization-select" required
                class="w-full px-3 py-2.5 border border-slate-200 rounded-xl text-sm text-slate-800 bg-white outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100 transition-colors">
                @foreach($childOrgs as $org)
                <option value="{{ $org->id }}" {{ $organizationId == $org->id ? 'selected' : '' }}>{{ $org->name }}</option>
                @endforeach
            </select>
            <p class="text-[11px] text-slate-400 mt-1">Ganti organisasi akan memuat ulang daftar akun.</p>
        </div>
        @else
        <input type="hidden" name="organization_id" value="{{ $organizationId }}">
        <div class="mb-4 text-xs text-slate-500">
            Organisasi tujuan: <span class="font-semibold text-slate-800">{{ $childOrgs->first()->name }}</span>
        </div>
        @endif

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div id="tour-target-account">
                <label class="text-xs font-semibold text-slate-600 block mb-1.5">Rekening Tujuan <span class="text-red-500">*</span></label>
                @if($targetAccounts->isEmpty())
                <div class="px-3 py-2.5 bg-amber-50 border border-amber-200 rounded-xl text-xs text-amber-700">
                    Tidak ada akun REKENING BANK di Bagan Akun organisasi ini.
                </div>
                @else
                <select name="target_account_id" required
                    class="w-full px-3 py-2.5 border border-slate-200 rounded-xl text-sm text-slate-800 bg-white outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100 transition-colors">
                    <option value="">— Pilih Rekening —</option>
                    @foreach($targetAccounts as $acc)
                    <option value="{{ $acc->id }}">{{ $acc->name }} (saldo saat ini: Rp {{ number_format($acc->balance, 0, ',', '.') }})</option>
                    @endforeach
                </select>
                @endif
            </div>
            <div id="tour-contra-account">
                <label class="text-xs font-semibold text-slate-600 block mb-1.5">Akun Lawan (sisi Kampus/STMIK)</label>
                @if($autoContraAccount)
                <div class="flex items-center gap-2 px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-700">
                    <svg width="14" height="14" class="text-slate-400 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M20 6L9 17l-5-5"/></svg>
                    <span class="font-mono font-semibold text-slate-500">{{ $autoContraAccount->code }}</span>
                    <span>{{ $autoContraAccount->name }}</span>
                </div>
                <input type="hidden" name="source_credit_account_id" value="{{ $autoContraAccount->id }}">
                <p class="text-[11px] text-slate-400 mt-1">Terisi otomatis dari akun Hutang Antar Entitas yang sudah ada di Bagan Akun.</p>
                @else
                <select name="source_credit_account_id" required
                    class="w-full px-3 py-2.5 border border-slate-200 rounded-xl text-sm text-slate-800 bg-white outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100 transition-colors">
                    <option value="">— Pilih Akun —</option>
                    @foreach($ledgerAccounts as $acc)
                    <option value="{{ $acc->id }}">{{ $acc->code }} — {{ $acc->name }}</option>
                    @endforeach
                </select>
                <p class="text-[11px] text-slate-400 mt-1">Sistem tidak menemukan akun Hutang Antar Entitas yang cocok otomatis -- pilih akun kewajiban yang sesuai.</p>
                @endif
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-4">
            <div id="tour-amount">
                <label class="text-xs font-semibold text-slate-600 block mb-1.5">Jumlah Saldo <span class="text-red-500">*</span></label>
                <div class="relative">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm">Rp</span>
                    <input type="text" id="amount-display" inputmode="numeric" required
                        class="w-full pl-9 pr-3 py-2.5 border border-slate-200 rounded-xl text-sm text-slate-800 outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100 transition-colors font-mono"
                        placeholder="0" value="{{ old('amount') ? number_format(old('amount'), 0, ',', '.') : '' }}">
                    <input type="hidden" name="amount" id="amount-input" value="{{ old('amount') }}">
                </div>
            </div>
        </div>

        <div class="mt-4">
            <label class="text-xs font-semibold text-slate-600 block mb-1.5">Catatan <span class="text-slate-400 font-normal">(opsional)</span></label>
            <textarea name="notes" rows="2"
                class="w-full px-3 py-2.5 border border-slate-200 rounded-xl text-sm outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100 transition-colors resize-none"
                placeholder="Alasan/keterangan tambahan...">{{ old('notes') }}</textarea>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm p-5" id="tour-yayasan">
        <div class="text-sm font-bold text-slate-900 mb-3">Sisi Yayasan</div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="text-xs font-semibold text-slate-600 block mb-1.5">Rekening Sumber Yayasan <span class="text-red-500">*</span></label>
                <select name="yayasan_source_account_id" required
                    class="w-full px-3 py-2.5 border border-slate-200 rounded-xl text-sm text-slate-800 bg-white outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100 transition-colors">
                    <option value="">— Pilih Rekening —</option>
                    @foreach($yayasanAccounts as $acc)
                    <option value="{{ $acc->id }}">{{ $acc->name }} (saldo: Rp {{ number_format($acc->balance, 0, ',', '.') }})</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="text-xs font-semibold text-slate-600 block mb-1.5">Akun Lawan Yayasan <span class="text-red-500">*</span></label>
                <select name="yayasan_debit_account_id" required
                    class="w-full px-3 py-2.5 border border-slate-200 rounded-xl text-sm text-slate-800 bg-white outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100 transition-colors">
                    <option value="">— Pilih Akun —</option>
                    @foreach($yayasanAccounts as $acc)
                    <option value="{{ $acc->id }}">{{ $acc->code }} — {{ $acc->name }}</option>
                    @endforeach
                </select>
                <p class="text-[11px] text-slate-400 mt-1">Contoh: akun "Piutang ke [Organisasi Tujuan]".</p>
            </div>
        </div>

        <div class="mt-4" id="tour-proof">
            <label class="text-xs font-semibold text-slate-600 block mb-1.5">Bukti Transfer <span class="text-red-500">*</span></label>
            <input type="file" name="proof" required accept=".pdf,.jpg,.jpeg,.png"
                class="w-full px-3 py-2.5 border border-slate-200 rounded-xl text-sm text-slate-800 bg-white outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100 transition-colors">
        </div>
    </div>

    <div class="flex justify-end gap-2">
        <a href="{{ route('cash-topup-requests.index') }}"
            class="inline-flex items-center px-5 py-2.5 rounded-xl text-sm font-semibold bg-slate-100 text-slate-600 no-underline hover:bg-slate-200 transition-colors">
            Batal
        </a>
        <button type="submit" id="tour-submit"
            class="inline-flex items-center gap-1.5 px-5 py-2.5 rounded-xl text-sm font-semibold bg-gradient-to-br from-green-500 to-green-600 text-white border-0 cursor-pointer hover:opacity-90 transition-opacity shadow-sm">
            Isi Saldo Sekarang
        </button>
    </div>
</form>

@if($childOrgs->count() > 1)
<script>
document.getElementById('organization-select').addEventListener('change', function () {
    window.location.href = '{{ route('cash-topup-requests.direct-create') }}?organization_id=' + this.value;
});
</script>
@endif

<script>
(function () {
    var amountInput   = document.getElementById('amount-input');
    var amountDisplay = document.getElementById('amount-display');

    function setAmount(raw) {
        amountInput.value = raw || '';
        amountDisplay.value = raw ? parseInt(raw, 10).toLocaleString('id-ID') : '';
    }

    amountDisplay.addEventListener('input', function () {
        setAmount(this.value.replace(/[^\d]/g, ''));
    });
})();
</script>

{{-- ─── Panduan: modal ringkasan + tur interaktif ─── --}}
<div class="fixed inset-0 z-[200] items-center justify-center p-4 bg-slate-900/50" id="tour-guide-modal" style="display:none;">
    <div class="bg-white rounded-2xl w-full max-w-lg max-h-[85vh] flex flex-col shadow-2xl">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between flex-shrink-0">
            <div>
                <div class="text-sm font-bold text-slate-900">Panduan Isi Saldo Langsung</div>
                <p class="text-xs text-slate-400 mt-0.5">Ringkasan cara mengisi form ini</p>
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
                    <span class="text-[11px] text-slate-400">Panduan Isi Saldo Langsung</span>
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
        { title: 'Isi Saldo Langsung', body: 'Yayasan bisa langsung mengisi saldo rekening Kampus/STMIK tanpa menunggu ada pengajuan dulu -- begitu form ini disubmit, langsung efektif dan jurnalnya langsung diposting.', targetId: '' },
        { title: 'Organisasi & Rekening Tujuan', body: 'Pilih organisasi (Kampus/STMIK) yang mau ditambah saldonya, lalu pilih rekening bank tujuannya. Saldo rekening saat ini ditampilkan di tiap pilihan.', targetId: 'tour-tujuan' },
        { title: 'Rekening Tujuan', body: 'Rekening bank milik organisasi tujuan yang akan bertambah saldonya.', targetId: 'tour-target-account' },
        { title: 'Akun Lawan (sisi Kampus/STMIK)', body: 'Biasanya terisi otomatis dari akun Hutang Antar Entitas yang sudah ada. Kalau tidak ketemu otomatis, pilih manual akun kewajiban yang sesuai.', targetId: 'tour-contra-account' },
        { title: 'Jumlah Saldo', body: 'Nominal yang mau diisikan ke rekening tujuan.', targetId: 'tour-amount' },
        { title: 'Sisi Yayasan', body: 'Pilih rekening sumber dana Yayasan (uang beneran keluar dari sini) dan akun lawan Yayasan (mis. "Piutang ke [organisasi tujuan]").', targetId: 'tour-yayasan' },
        { title: 'Bukti Transfer', body: 'Wajib unggah bukti transfer sebagai dokumentasi bahwa dana benar-benar sudah dikirim.', targetId: 'tour-proof' },
        { title: 'Isi Saldo Sekarang', body: 'Klik ini untuk langsung memproses -- tidak ada langkah approval tambahan setelah ini, jadi pastikan semua data sudah benar sebelum submit.', targetId: 'tour-submit' },
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
            if (r.width === 0 && r.height === 0) { positionOverlay(''); return; }
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
