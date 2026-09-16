<x-layouts.app title="Saldo Awal">

{{-- Header --}}
<div class="flex items-start justify-between gap-4 mb-5 flex-wrap">
    <div>
        <h2 class="text-lg font-bold text-slate-900 m-0 mb-1">Saldo Awal</h2>
        <p class="text-xs text-slate-400 m-0">
            Input saldo awal per akun per 1 Januari — total debit dan kredit harus balance.
            Akun Pendapatan & Beban tidak ditampilkan (mulai dari nol di periode berjalan).
        </p>
    </div>
    <button type="button" id="tour-open-guide"
        class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-sm font-semibold bg-white border border-slate-200 text-slate-600 hover:bg-slate-50 transition-colors">
        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 015.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
        Panduan
    </button>
</div>

@if(session('success'))
<div class="flex items-center gap-2.5 px-4 py-3 bg-green-50 border border-green-200 rounded-xl mb-4 text-sm text-green-700">
    <svg width="16" height="16" fill="#16a34a" viewBox="0 0 20 20" class="shrink-0"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
    {{ session('success') }}
</div>
@endif

@if($errors->any())
<div class="flex items-start gap-2.5 px-4 py-3 bg-red-50 border border-red-200 rounded-xl mb-4 text-sm text-red-700">
    <svg width="16" height="16" fill="#dc2626" viewBox="0 0 20 20" class="shrink-0 mt-0.5"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
    <div>
        @foreach($errors->all() as $error)
            <div>{{ $error }}</div>
        @endforeach
    </div>
</div>
@endif

{{-- Filter Bar --}}
<form method="GET" action="{{ route('beginning-balances.index') }}" id="tour-filter" class="bg-white rounded-xl shadow-sm p-4 mb-4 flex flex-wrap gap-3 items-end">
    @if($organizations->count() > 1)
    <div class="min-w-[200px]">
        <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block mb-1.5">Organisasi</label>
        <select name="organization_id" onchange="this.form.submit()"
            class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm text-slate-700 bg-white outline-none focus:border-blue-400 transition-colors">
            @foreach($organizations as $org)
                <option value="{{ $org->id }}" {{ $orgId === $org->id ? 'selected' : '' }}>{{ $org->name }}</option>
            @endforeach
        </select>
    </div>
    @else
    <input type="hidden" name="organization_id" value="{{ $orgId }}">
    @endif

    <div class="min-w-[130px]">
        <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block mb-1.5">Tahun</label>
        <select name="year" onchange="this.form.submit()"
            class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm text-slate-700 bg-white outline-none focus:border-blue-400 transition-colors">
            @foreach($yearOptions as $y)
                <option value="{{ $y }}" {{ $year === $y ? 'selected' : '' }}>{{ $y }}</option>
            @endforeach
        </select>
    </div>

    <div class="text-xs text-slate-400 pb-2.5">
        @if($entry)
            Sudah tersimpan sebagai jurnal
            <a href="{{ route('journal-entries.show', $entry->id) }}" class="font-mono text-blue-600 no-underline hover:underline">{{ $entry->reference }}</a>
            (per {{ $entry->entry_date->translatedFormat('d M Y') }}) — ubah angka di bawah lalu simpan ulang.
        @else
            Belum ada saldo awal untuk tahun {{ $year }}.
        @endif
    </div>
</form>

@if($accounts->isEmpty())
<div class="bg-white rounded-xl shadow-sm py-16 px-5 text-center">
    <div class="text-sm font-semibold text-slate-600 mb-1">Belum ada akun</div>
    <div class="text-xs text-slate-400">Tambahkan akun pada menu <strong>Bagan Akun</strong> terlebih dahulu.</div>
</div>
@else

<form method="POST" action="{{ route('beginning-balances.save') }}" id="bb-form">
    @csrf
    <input type="hidden" name="organization_id" value="{{ $orgId }}">
    <input type="hidden" name="year" value="{{ $year }}">

    <div class="bg-white rounded-xl shadow-sm p-4 mb-4" id="tour-search">
        <div class="relative max-w-md">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
            <input type="text" id="bb-search" placeholder="Cari kode atau nama akun..."
                class="w-full pl-9 pr-3 py-2 border border-slate-200 rounded-lg text-sm outline-none focus:border-blue-400 transition-colors">
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm overflow-hidden mb-4" id="tour-table">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-[11px] text-slate-400 uppercase tracking-wider bg-slate-50/70 border-b border-slate-100">
                        <th class="py-2.5 px-4 font-semibold whitespace-nowrap">Kode</th>
                        <th class="py-2.5 px-3 font-semibold min-w-[220px]">Nama Akun</th>
                        <th class="py-2.5 px-3 font-semibold whitespace-nowrap">Saldo Normal</th>
                        <th class="py-2.5 px-3 font-semibold text-right min-w-[160px]" id="tour-debit-col">Debit</th>
                        <th class="py-2.5 px-4 font-semibold text-right min-w-[160px]" id="tour-credit-col">Kredit</th>
                    </tr>
                </thead>
                @foreach(\App\Models\Account::TYPES as $type => $info)
                    @php $group = $accounts->where('account_type', $type); @endphp
                    @if($group->isNotEmpty())
                    <tbody data-group="{{ $type }}">
                        <tr class="bg-slate-100/70 border-b border-slate-100 bb-group-header">
                            <td colspan="5" class="py-2 px-4 text-[11px] font-bold uppercase tracking-widest" style="color: {{ $info['color'] }}">{{ $info['label'] }}</td>
                        </tr>
                        @foreach($group as $account)
                        @php
                            $line = $existing->get($account->id);
                            $oldDebit  = old("balances.{$account->id}.debit",  $line && $line->debit > 0 ? (int) $line->debit : null);
                            $oldCredit = old("balances.{$account->id}.credit", $line && $line->credit > 0 ? (int) $line->credit : null);
                        @endphp
                        <tr class="bb-row border-b border-slate-50 last:border-0 hover:bg-slate-50/50 transition-colors" data-search="{{ strtolower($account->code . ' ' . $account->name) }}">
                            <td class="py-2 px-4 font-mono text-[11px] text-slate-500 whitespace-nowrap">{{ $account->code }}</td>
                            <td class="py-2 px-3 text-slate-700">{{ $account->name }}{{ $account->is_active ? '' : ' (nonaktif)' }}</td>
                            <td class="py-2 px-3 text-[11px] text-slate-400 capitalize">{{ $account->normal_balance }}</td>
                            <td class="py-2 px-3">
                                <input type="text" inputmode="numeric" autocomplete="off"
                                    value="{{ $oldDebit ? number_format($oldDebit, 0, ',', '.') : '' }}"
                                    placeholder="0" data-side="debit"
                                    class="bb-input w-full px-2.5 py-1.5 border border-blue-200 rounded-lg text-sm text-right font-mono text-slate-700 outline-none focus:border-blue-400 transition-colors">
                                <input type="hidden" name="balances[{{ $account->id }}][debit]" value="{{ $oldDebit }}" class="bb-raw">
                            </td>
                            <td class="py-2 px-4">
                                <input type="text" inputmode="numeric" autocomplete="off"
                                    value="{{ $oldCredit ? number_format($oldCredit, 0, ',', '.') : '' }}"
                                    placeholder="0" data-side="credit"
                                    class="bb-input w-full px-2.5 py-1.5 border border-green-200 rounded-lg text-sm text-right font-mono text-slate-700 outline-none focus:border-green-400 transition-colors">
                                <input type="hidden" name="balances[{{ $account->id }}][credit]" value="{{ $oldCredit }}" class="bb-raw">
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    @endif
                @endforeach
                <tbody>
                    <tr id="bb-no-match" class="hidden">
                        <td colspan="5" class="py-8 px-4 text-center text-xs text-slate-400 italic">Tidak ada akun yang cocok dengan pencarian.</td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr class="bg-slate-50/70 border-t border-slate-200">
                        <td colspan="3" class="py-3 px-4 text-[11px] font-bold text-slate-500 uppercase tracking-widest">Total</td>
                        <td class="py-3 px-3 text-right font-mono font-bold text-blue-700 whitespace-nowrap" id="total-debit">Rp 0</td>
                        <td class="py-3 px-4 text-right font-mono font-bold text-green-700 whitespace-nowrap" id="total-credit">Rp 0</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    {{-- Bar status balance + tombol simpan --}}
    <div class="bg-white rounded-xl shadow-sm p-4 flex flex-wrap items-center gap-3 sticky bottom-3 border border-slate-100" id="tour-save-bar">
        <span id="balance-badge" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-500">
            Belum ada angka
        </span>
        <span class="text-xs text-slate-400" id="balance-hint">Isi saldo tiap akun pada kolom debit atau kredit.</span>
        <button type="submit" id="save-btn" disabled
            class="ml-auto inline-flex items-center gap-1.5 px-5 py-2.5 rounded-lg text-sm font-semibold bg-blue-600 text-white border-0 cursor-pointer hover:bg-blue-700 transition-colors disabled:bg-slate-200 disabled:text-slate-400 disabled:cursor-not-allowed">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
            Simpan Saldo Awal
        </button>
    </div>
</form>

<div class="text-[11px] text-slate-400 mt-3">
    Saldo awal disimpan sebagai jurnal khusus (referensi <span class="font-mono">SA-{{ $year }}-…</span>) bertanggal 1 Januari {{ $year }} berstatus <em>posted</em>,
    sehingga otomatis terhitung di buku besar dan neraca saldo. Mengosongkan semua angka lalu menyimpan akan menghapus saldo awal tahun ini.
</div>

<script>
(function () {
    const inputs = document.querySelectorAll('.bb-input');
    const badge = document.getElementById('balance-badge');
    const hint = document.getElementById('balance-hint');
    const saveBtn = document.getElementById('save-btn');
    const hasExisting = @json((bool) $entry);

    const fmt = n => 'Rp ' + Math.round(n).toLocaleString('id-ID');
    const rawOf = el => el.nextElementSibling; // input hidden .bb-raw tepat setelah .bb-input

    function formatDisplay(el) {
        const digits = el.value.replace(/\D/g, '');
        el.value = digits ? Number(digits).toLocaleString('id-ID') : '';
        rawOf(el).value = digits;
    }

    function recalc() {
        let d = 0, c = 0;
        inputs.forEach(el => {
            const v = parseFloat(rawOf(el).value) || 0;
            if (el.dataset.side === 'debit') d += v; else c += v;
        });

        document.getElementById('total-debit').textContent = fmt(d);
        document.getElementById('total-credit').textContent = fmt(c);

        const diff = Math.abs(d - c);
        if (d === 0 && c === 0) {
            badge.className = 'inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-500';
            badge.textContent = 'Belum ada angka';
            hint.textContent = hasExisting
                ? 'Menyimpan dalam keadaan kosong akan menghapus saldo awal tahun ini.'
                : 'Isi saldo tiap akun pada kolom debit atau kredit.';
            saveBtn.disabled = !hasExisting;
        } else if (diff > 0.01) {
            badge.className = 'inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold bg-red-100 text-red-700';
            badge.textContent = 'Selisih ' + fmt(diff);
            hint.textContent = 'Total debit dan kredit belum sama — periksa kembali angkanya.';
            saveBtn.disabled = true;
        } else {
            badge.className = 'inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold bg-green-100 text-green-700';
            badge.textContent = 'Balance ✓';
            hint.textContent = 'Total debit = total kredit. Siap disimpan.';
            saveBtn.disabled = false;
        }
    }

    inputs.forEach(el => {
        el.addEventListener('input', () => {
            formatDisplay(el);
            // Satu akun hanya boleh satu sisi: mengisi debit mengosongkan kredit, dan sebaliknya
            if ((parseFloat(rawOf(el).value) || 0) > 0) {
                const other = el.closest('tr').querySelector(`.bb-input[data-side="${el.dataset.side === 'debit' ? 'credit' : 'debit'}"]`);
                if (other && rawOf(other).value) { other.value = ''; rawOf(other).value = ''; }
            }
            recalc();
        });
    });

    document.getElementById('bb-form').addEventListener('submit', function (e) {
        const form = this;
        let d = 0, c = 0;
        inputs.forEach(el => {
            const v = parseFloat(rawOf(el).value) || 0;
            if (el.dataset.side === 'debit') d += v; else c += v;
        });

        // Jaga-jaga saja: tombol simpan sudah disabled saat belum balance
        if (Math.abs(d - c) > 0.01) {
            e.preventDefault();
            return;
        }

        if (d === 0 && c === 0 && hasExisting) {
            e.preventDefault();
            if (window.confirmModal) {
                confirmModal(
                    'Hapus Saldo Awal',
                    'Semua angka kosong. Saldo awal tahun <strong>' + {{ (int) $year }} + '</strong> akan <strong>dihapus</strong>. Lanjutkan?',
                    function () { form.submit(); },
                    'Ya, Hapus',
                    'Tindakan ini tidak dapat dibatalkan.'
                );
            } else if (confirm('Semua angka kosong. Saldo awal tahun ini akan DIHAPUS. Lanjutkan?')) {
                form.submit();
            }
        }
    });

    // Pencarian kode/nama akun -- cuma filter tampilan, semua input tetap ikut submit
    const searchInput = document.getElementById('bb-search');
    const noMatchRow  = document.getElementById('bb-no-match');
    if (searchInput) {
        searchInput.addEventListener('input', function () {
            const q = this.value.trim().toLowerCase();
            let anyVisible = false;
            document.querySelectorAll('tbody[data-group]').forEach(tbody => {
                let groupVisible = false;
                tbody.querySelectorAll('tr.bb-row').forEach(row => {
                    const match = !q || row.dataset.search.indexOf(q) !== -1;
                    row.classList.toggle('hidden', !match);
                    if (match) { groupVisible = true; anyVisible = true; }
                });
                tbody.querySelector('.bb-group-header').classList.toggle('hidden', !groupVisible);
            });
            noMatchRow.classList.toggle('hidden', anyVisible);
        });
    }

    recalc();
})();
</script>

{{-- ─── Panduan: modal ringkasan + tur interaktif ─── --}}
<div class="fixed inset-0 z-[200] items-center justify-center p-4 bg-slate-900/50" id="tour-guide-modal" style="display:none;">
    <div class="bg-white rounded-2xl w-full max-w-lg max-h-[85vh] flex flex-col shadow-2xl">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between flex-shrink-0">
            <div>
                <div class="text-sm font-bold text-slate-900">Panduan Saldo Awal</div>
                <p class="text-xs text-slate-400 mt-0.5">Ringkasan cara mengisi halaman ini</p>
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
                    <span class="text-[11px] text-slate-400">Panduan Saldo Awal</span>
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
        { title: 'Selamat Datang di Saldo Awal', body: 'Halaman ini buat input saldo awal tiap akun sebelum mulai pakai aplikasi ini -- dicatat sebagai jurnal khusus per 1 Januari.', targetId: '' },
        { title: 'Organisasi & Tahun', body: 'Pilih organisasi dan tahun buku yang mau diisi saldo awalnya. Tiap organisasi & tahun punya saldo awal sendiri-sendiri.', targetId: 'tour-filter' },
        { title: 'Cari Akun', body: 'Daftar akunnya bisa panjang -- ketik kode atau nama akun di sini buat langsung ketemu yang dicari.', targetId: 'tour-search' },
        { title: 'Tabel Akun', body: 'Dikelompokkan per tipe (Aset, Kewajiban, Ekuitas). Isi salah satu kolom Debit ATAU Kredit per akun -- mengisi salah satu otomatis mengosongkan yang lain.', targetId: 'tour-table' },
        { title: 'Status Balance', body: 'Total debit dan kredit harus sama persis sebelum bisa disimpan -- badge ini langsung menunjukkan statusnya sambil mengisi angka.', targetId: 'tour-save-bar' },
        { title: 'Simpan', body: 'Tombol ini aktif begitu totalnya sudah balance. Mengosongkan semua angka lalu menyimpan akan menghapus saldo awal tahun ini.', targetId: 'save-btn' },
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

@endif

</x-layouts.app>
