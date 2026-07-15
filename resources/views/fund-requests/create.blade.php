<x-layouts.app title="Buat Pengajuan Dana">

<a href="{{ route('fund-requests.index') }}" class="inline-flex items-center gap-1.5 text-sm text-slate-500 hover:text-orange-500 mb-5 no-underline">
    <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M19 12H5M12 5l-7 7 7 7"/></svg>
    Kembali ke Daftar Pengajuan
</a>
<h1 class="text-xl font-bold text-slate-900 mb-5">Buat Pengajuan Dana</h1>

@if($errors->any())
<div class="flex items-start gap-2.5 px-4 py-3 bg-red-50 border border-red-200 rounded-xl mb-4 text-sm text-red-600">
    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" class="shrink-0 mt-px"><circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01"/></svg>
    {{ $errors->first() }}
</div>
@endif

<div class="bg-white rounded-xl shadow-sm p-6">
    <form method="POST" action="{{ route('fund-requests.store') }}" id="fund-form" enctype="multipart/form-data" novalidate>
    @csrf

    {{-- Info pengaju --}}
    <div class="text-[11px] font-bold text-slate-400 uppercase tracking-widest mb-3.5 pb-2 border-b border-slate-100">Informasi Pengaju</div>
    <div class="flex gap-4 items-center px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl mb-5 flex-wrap">
        <div>
            <div class="text-[11px] text-slate-400 mb-0.5">Nama</div>
            <div class="text-sm font-semibold text-slate-900">{{ $employee->name }}</div>
        </div>
        <div class="w-px h-8 bg-slate-200 hidden sm:block"></div>
        <div>
            <div class="text-[11px] text-slate-400 mb-0.5">NIK</div>
            <div class="text-sm font-semibold text-slate-900 font-mono">{{ $employee->nik }}</div>
        </div>
        <div class="w-px h-8 bg-slate-200 hidden sm:block"></div>
        <div>
            <div class="text-[11px] text-slate-400 mb-0.5">Jabatan</div>
            <div class="text-sm font-semibold text-slate-900">
                @if($activePosition) {{ $activePosition->name }}
                @else <span class="text-red-500">Belum ada jabatan aktif</span>
                @endif
            </div>
        </div>
        <div class="w-px h-8 bg-slate-200 hidden sm:block"></div>
        <div>
            <div class="text-[11px] text-slate-400 mb-0.5">Departemen</div>
            <div class="text-sm font-semibold text-slate-900">{{ $activePosition?->department?->name ?? '-' }}</div>
        </div>
        <div class="w-px h-8 bg-slate-200 hidden sm:block"></div>
        <div>
            <div class="text-[11px] text-slate-400 mb-0.5">Organisasi</div>
            <div class="text-sm font-semibold text-slate-900">{{ $employee->organization?->name ?? '-' }}</div>
        </div>
    </div>

    @unless($activePosition)
    <div class="flex items-start gap-2.5 px-4 py-3 bg-red-50 border border-red-200 rounded-xl mb-4 text-sm text-red-600">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" class="shrink-0"><circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01"/></svg>
        Anda belum memiliki jabatan aktif. Tidak dapat membuat pengajuan. Hubungi HRD.
    </div>
    @endunless

    @if($activePosition)

    {{-- Pilih Program Kerja --}}
    <div class="text-[11px] font-bold text-slate-400 uppercase tracking-widest mb-3.5 pb-2 border-b border-slate-100 mt-2">Pilih Program Kerja</div>

    <div id="programs-loading" class="flex items-center gap-2 px-4 py-3 text-sm text-slate-400 mb-4">
        <svg class="animate-spin w-4 h-4 text-orange-400" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg>
        Memuat program kerja...
    </div>

    <div id="no-program-msg" class="flex items-start gap-2.5 px-4 py-3 bg-amber-50 border border-amber-200 rounded-xl mb-4 text-sm text-amber-700" style="display:none">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" class="shrink-0 mt-px"><path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
        Departemen Anda belum memiliki program kerja aktif untuk periode anggaran yang berlaku.
    </div>

    <div id="program-row" class="flex flex-col gap-1.5 mb-4" style="display:none">
        <label class="text-xs font-semibold text-slate-600">Program Kerja <span class="text-red-500 ml-0.5">*</span></label>
        <select name="budget_program_id" id="program-select"
            class="w-full px-3 py-2.5 border border-slate-200 rounded-xl text-sm text-slate-800 bg-white outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100 transition-colors {{ $errors->has('budget_program_id') ? 'border-red-400' : '' }}"
            onchange="onProgramChange(this.value)">
            <option value="">— Pilih Program Kerja —</option>
        </select>
        @error('budget_program_id')<div class="text-xs text-red-500 mt-0.5">{{ $message }}</div>@enderror
    </div>

    {{-- Detail Program --}}
    <div id="program-detail" style="display:none" class="mb-5">
        <div class="border border-slate-200 rounded-xl overflow-hidden">
            <div class="flex items-center justify-between px-4 py-3 bg-slate-50 border-b border-slate-200 flex-wrap gap-2">
                <div class="flex items-center gap-2">
                    <div class="text-xs font-bold text-slate-600 uppercase tracking-wide">Detail Program Kerja</div>
                    <span id="detail-type" class="px-2 py-0.5 rounded-full text-[10px] font-bold" style="display:none"></span>
                </div>
                <div class="flex items-center gap-4 text-xs text-slate-500 flex-wrap">
                    <span>Frekuensi: <span id="detail-freq" class="font-semibold text-slate-700"></span></span>
                    <span>Per Termin: <span id="detail-per-termin" class="font-semibold text-slate-700"></span></span>
                    <span>Total Pagu: <span id="detail-pagu" class="font-semibold text-orange-600"></span></span>
                </div>
            </div>

            <div class="px-4 py-3">
                <div class="text-[11px] font-semibold text-slate-400 uppercase tracking-wide mb-2">Rincian Kegiatan</div>
                <div class="overflow-x-auto">
                    <table class="w-full text-xs border-collapse">
                        <thead>
                            <tr class="bg-slate-50">
                                <th class="px-3 py-2 text-left font-semibold text-slate-500 border border-slate-200">Jenis Pengeluaran</th>
                                <th class="px-3 py-2 text-left font-semibold text-slate-500 border border-slate-200">Deskripsi</th>
                                <th class="px-3 py-2 text-right font-semibold text-slate-500 border border-slate-200 w-16">Qty</th>
                                <th class="px-3 py-2 text-left font-semibold text-slate-500 border border-slate-200 w-14">Sat.</th>
                                <th class="px-3 py-2 text-right font-semibold text-slate-500 border border-slate-200 w-32">Harga Satuan</th>
                                <th class="px-3 py-2 text-right font-semibold text-slate-500 border border-slate-200 w-32">Total</th>
                            </tr>
                        </thead>
                        <tbody id="detail-tbody"></tbody>
                    </table>
                </div>
            </div>

            <div class="px-4 py-3 border-t border-slate-100">
                <div class="text-[11px] font-semibold text-slate-400 uppercase tracking-wide mb-2">Jadwal &amp; Estimasi Pencairan</div>
                <div class="flex flex-wrap gap-2" id="schedule-list"></div>
            </div>
        </div>
    </div>

    {{-- Form pengajuan --}}
    <div id="form-fields" style="display:none">
        <div class="text-[11px] font-bold text-slate-400 uppercase tracking-widest mb-3.5 pb-2 border-b border-slate-100">Detail Pengajuan</div>
        <div class="grid grid-cols-2 gap-4">
            <div class="flex flex-col gap-1.5 col-span-2">
                <label class="text-xs font-semibold text-slate-600">Judul Pengajuan <span class="text-red-500 ml-0.5">*</span></label>
                <input type="text" name="title" id="title-input" value="{{ old('title') }}" maxlength="200"
                    class="w-full px-3 py-2.5 border border-slate-200 rounded-xl text-sm text-slate-800 bg-white outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100 transition-colors {{ $errors->has('title') ? 'border-red-400' : '' }}"
                    placeholder="Judul pengajuan...">
                @error('title')<div class="text-xs text-red-500 mt-0.5">{{ $message }}</div>@enderror
            </div>

            <div class="flex flex-col gap-1.5">
                <label class="text-xs font-semibold text-slate-600">Jumlah Dana (Rp) <span class="text-red-500 ml-0.5">*</span></label>
                <div class="flex items-center">
                    <span class="px-3 py-2.5 bg-slate-100 border border-slate-200 border-r-0 rounded-l-xl text-sm text-slate-500 font-medium whitespace-nowrap">Rp</span>
                    <input type="text" id="amount-display" inputmode="numeric"
                        class="w-full px-3 py-2.5 border border-slate-200 rounded-r-xl rounded-l-none text-sm text-slate-800 bg-white outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100 transition-colors {{ $errors->has('amount') ? 'border-red-400' : '' }}"
                        placeholder="0"
                        value="{{ old('amount') ? number_format(old('amount'), 0, ',', '.') : '' }}"
                        oninput="formatAmount(this)">
                    <input type="hidden" name="amount" id="amount-input" value="{{ old('amount') }}">
                </div>
                <div id="amount-hint" class="text-[11px] text-slate-400" style="display:none">
                    Maks. pagu program: <span id="amount-max-label" class="font-semibold text-slate-600"></span>
                </div>
                @error('amount')<div class="text-xs text-red-500 mt-0.5">{{ $message }}</div>@enderror
            </div>

            <div class="flex flex-col gap-1.5">
                <label class="text-xs font-semibold text-slate-600">Tujuan / Keterangan <span class="text-red-500 ml-0.5">*</span></label>
                <textarea name="purpose" rows="2" maxlength="1000" required
                    class="w-full px-3 py-2.5 border border-slate-200 rounded-xl text-sm text-slate-800 bg-white outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100 transition-colors resize-y {{ $errors->has('purpose') ? 'border-red-400' : '' }}"
                    placeholder="Jelaskan tujuan pengajuan...">{{ old('purpose') }}</textarea>
                @error('purpose')<div class="text-xs text-red-500 mt-0.5">{{ $message }}</div>@enderror
            </div>
        </div>

        {{-- Info Rekening --}}
        <div class="text-[11px] font-bold text-slate-400 uppercase tracking-widest mb-3.5 pb-2 border-b border-slate-100 mt-5">Informasi Rekening Tujuan Transfer</div>
        <div class="grid grid-cols-3 gap-4">
            <div class="flex flex-col gap-1.5">
                <label class="text-xs font-semibold text-slate-600">Nama Bank</label>
                <input type="text" name="bank_name" value="{{ old('bank_name') }}" maxlength="100"
                    class="w-full px-3 py-2.5 border border-slate-200 rounded-xl text-sm text-slate-800 bg-white outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100 transition-colors {{ $errors->has('bank_name') ? 'border-red-400' : '' }}"
                    placeholder="Contoh: BRI, BNI, Mandiri...">
                @error('bank_name')<div class="text-xs text-red-500 mt-0.5">{{ $message }}</div>@enderror
            </div>

            <div class="flex flex-col gap-1.5">
                <label class="text-xs font-semibold text-slate-600">Nomor Rekening <span class="text-red-500 ml-0.5">*</span></label>
                <input type="text" name="bank_account_number" value="{{ old('bank_account_number') }}" maxlength="50"
                    inputmode="numeric" pattern="[0-9]+" title="Hanya boleh angka"
                    oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                    class="w-full px-3 py-2.5 border border-slate-200 rounded-xl text-sm text-slate-800 bg-white outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100 transition-colors font-mono {{ $errors->has('bank_account_number') ? 'border-red-400' : '' }}"
                    placeholder="Nomor rekening...">
                @error('bank_account_number')<div class="text-xs text-red-500 mt-0.5">{{ $message }}</div>@enderror
            </div>

            <div class="flex flex-col gap-1.5">
                <label class="text-xs font-semibold text-slate-600">Nama Pemilik Rekening <span class="text-red-500 ml-0.5">*</span></label>
                <input type="text" name="bank_account_name" value="{{ old('bank_account_name') }}" maxlength="150"
                    class="w-full px-3 py-2.5 border border-slate-200 rounded-xl text-sm text-slate-800 bg-white outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100 transition-colors {{ $errors->has('bank_account_name') ? 'border-red-400' : '' }}"
                    placeholder="Sesuai buku tabungan...">
                @error('bank_account_name')<div class="text-xs text-red-500 mt-0.5">{{ $message }}</div>@enderror
            </div>
        </div>
    </div>

        {{-- Lampiran --}}
        <div class="text-[11px] font-bold text-slate-400 uppercase tracking-widest mb-3.5 pb-2 border-b border-slate-100 mt-5">Lampiran <span class="text-red-500 ml-0.5">*</span></div>
        <div class="flex flex-col gap-2">
            <input type="file" name="attachments[]" multiple required accept=".pdf,.jpg,.jpeg,.png,.doc,.docx,.xls,.xlsx"
                class="text-xs text-slate-600 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-orange-50 file:text-orange-600 hover:file:bg-orange-100 cursor-pointer">
            @error('attachments')<div class="text-xs text-red-500 mt-0.5">{{ $message }}</div>@enderror
            @error('attachments.*')<div class="text-xs text-red-500 mt-0.5">{{ $message }}</div>@enderror
            <div class="text-[10px] text-slate-400">Format: PDF, JPG, PNG, DOC, XLS · Maks. 10 MB per file · Bisa pilih beberapa file</div>
        </div>
    </div>

    @endif {{-- end if activePosition --}}

    <div class="flex gap-3 justify-end mt-6 pt-5 border-t border-slate-100">
        <a href="{{ route('fund-requests.index') }}" class="px-5 py-2.5 rounded-xl border border-slate-200 bg-white text-slate-600 text-sm font-medium no-underline inline-flex items-center">Batal</a>
        @if($activePosition)
            <button type="submit" id="submit-btn" disabled
                class="relative px-6 py-2.5 rounded-xl text-sm font-semibold bg-gradient-to-br from-orange-400 to-orange-500 text-white border-0 cursor-pointer transition-all disabled:opacity-40 disabled:cursor-not-allowed disabled:shadow-none" id="submit-btn">
                <span id="submit-label">Simpan ke Draft</span>
            </button>
        @endif
    </div>
    </form>
</div>

@if($activePosition)
<script>
const programsUrl = '{{ route('fund-requests.programs') }}';
const orgId       = '{{ $employee->organization_id }}';
const deptId      = '{{ $activePosition->department_id }}';
let programsCache = {};

function fmt(n) {
    return 'Rp ' + Number(n).toLocaleString('id-ID');
}
function escHtml(str) {
    return String(str ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}
function show(id) { document.getElementById(id).style.display = ''; }
function hide(id) { document.getElementById(id).style.display = 'none'; }

function formatAmount(el) {
    const raw = el.value.replace(/\./g, '').replace(/[^0-9]/g, '');
    el.value = raw ? Number(raw).toLocaleString('id-ID') : '';
    document.getElementById('amount-input').value = raw;
}

document.getElementById('fund-form').addEventListener('submit', function (e) {
    // pastikan hidden field sudah terisi dari display
    const disp = document.getElementById('amount-display').value;
    const raw  = disp.replace(/\./g, '').replace(/[^0-9]/g, '');
    document.getElementById('amount-input').value = raw;

    // ---- Validasi JS: tandai kolom kosong sebelum kirim ----
    document.querySelectorAll('.js-error').forEach(el => el.remove());
    let firstBad = null;
    const bad = (el, msg) => {
        el.classList.add('border-red-400');
        const box = el.closest('.flex-col') || el.parentElement;
        const div = document.createElement('div');
        div.className = 'js-error text-xs text-red-500 mt-0.5';
        div.textContent = msg;
        box.appendChild(div);
        if (!firstBad) firstBad = el;
    };

    const prog = document.getElementById('program-select');
    if (!prog.value) bad(prog, 'Pilih program kerja terlebih dahulu.');

    const title = document.getElementById('title-input');
    if (!title.value.trim()) bad(title, 'Judul pengajuan wajib diisi.');

    const amountEl = document.getElementById('amount-display');
    const maxPagu  = parseFloat(document.getElementById('amount-input').max || 0);
    if (!raw || parseInt(raw) <= 0) bad(amountEl, 'Jumlah dana wajib diisi.');
    else if (maxPagu > 0 && parseInt(raw) > maxPagu) bad(amountEl, 'Jumlah dana melebihi pagu program.');

    const purpose = document.querySelector('textarea[name="purpose"]');
    if (!purpose.value.trim()) bad(purpose, 'Tujuan / keterangan wajib diisi.');

    const noRek = document.querySelector('input[name="bank_account_number"]');
    if (!noRek.value.trim()) bad(noRek, 'Nomor rekening wajib diisi.');

    const pemilik = document.querySelector('input[name="bank_account_name"]');
    if (!pemilik.value.trim()) bad(pemilik, 'Nama pemilik rekening wajib diisi.');

    const files = document.querySelector('input[name="attachments[]"]');
    if (!files.files.length) bad(files, 'Lampiran wajib diunggah, minimal 1 file.');

    if (firstBad) {
        e.preventDefault();
        firstBad.scrollIntoView({ behavior: 'smooth', block: 'center' });
        setTimeout(() => firstBad.focus({ preventScroll: true }), 300);
    }
});

// Hapus tanda error begitu kolomnya diisi/diubah
document.getElementById('fund-form').addEventListener('input', function (e) {
    e.target.classList.remove('border-red-400');
    (e.target.closest('.flex-col') || e.target.parentElement)
        ?.querySelectorAll('.js-error').forEach(el => el.remove());
});
document.getElementById('fund-form').addEventListener('change', function (e) {
    e.target.classList.remove('border-red-400');
    (e.target.closest('.flex-col') || e.target.parentElement)
        ?.querySelectorAll('.js-error').forEach(el => el.remove());
});

function onProgramChange(programId, keepValues = false) {
    hide('program-detail');
    hide('form-fields');
    hide('amount-hint');
    document.getElementById('submit-btn').disabled = true;

    // Kosongkan isian sebelumnya supaya tidak terbawa saat ganti program
    // (kecuali saat restore old() setelah validation error)
    if (!keepValues) {
        document.getElementById('title-input').value    = '';
        document.getElementById('amount-display').value = '';
        document.getElementById('amount-input').value   = '';
        ['textarea[name="purpose"]', 'input[name="bank_name"]', 'input[name="bank_account_number"]', 'input[name="bank_account_name"]']
            .forEach(sel => { const el = document.querySelector(sel); if (el) el.value = ''; });
    }

    if (!programId || !programsCache[programId]) return;

    const p = programsCache[programId];

    // Rincian
    let rows = '';
    if (p.details && p.details.length > 0) {
        p.details.forEach(d => {
            rows += `<tr>
                <td class="px-3 py-2 border border-slate-200 text-slate-700">${escHtml(d.account)}</td>
                <td class="px-3 py-2 border border-slate-200 text-slate-700">${escHtml(d.description)}</td>
                <td class="px-3 py-2 border border-slate-200 text-right text-slate-700">${d.quantity}</td>
                <td class="px-3 py-2 border border-slate-200 text-slate-500">${escHtml(d.unit)}</td>
                <td class="px-3 py-2 border border-slate-200 text-right font-mono text-slate-700">${fmt(d.unit_price)}</td>
                <td class="px-3 py-2 border border-slate-200 text-right font-mono font-semibold text-slate-800">${fmt(d.total_amount)}</td>
            </tr>`;
        });
        rows += `<tr class="bg-slate-50">
            <td colspan="5" class="px-3 py-2 border border-slate-200 text-right text-xs font-semibold text-slate-500">Total Program</td>
            <td class="px-3 py-2 border border-slate-200 text-right font-mono font-bold text-orange-600">${fmt(p.total_amount)}</td>
        </tr>`;
    } else {
        rows = '<tr><td colspan="6" class="px-3 py-4 text-center text-slate-400 border border-slate-200">Belum ada rincian kegiatan.</td></tr>';
    }
    document.getElementById('detail-tbody').innerHTML = rows;

    // Jadwal & estimasi
    let schedHtml = '';
    if (p.schedules && p.schedules.length > 0) {
        p.schedules.forEach(s => {
            const tgl = s.estimated_date && s.estimated_date !== '-'
                ? `<span class="text-slate-500 font-normal">${escHtml(s.estimated_date)}</span>`
                : '<span class="text-slate-300 font-normal">belum dijadwalkan</span>';
            schedHtml += `<div class="inline-flex flex-col gap-0.5 px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-xs">
                <span class="font-bold text-slate-600">Termin ${s.termin} · ${tgl}</span>
                <span class="font-mono font-semibold text-orange-600">${fmt(Math.round(p.nominal_per_termin))}</span>
                ${s.notes ? `<span class="text-slate-400 font-normal">${escHtml(s.notes)}</span>` : ''}
            </div>`;
        });
    } else {
        schedHtml = '<span class="text-xs text-slate-400">Belum ada jadwal pencairan.</span>';
    }
    document.getElementById('schedule-list').innerHTML = schedHtml;

    // Badge jenis program
    const typeBadge = document.getElementById('detail-type');
    if (p.type) {
        const typeStyles = {
            pengadaan:  'background:#e0f2fe; color:#0369a1;',
            kegiatan:   'background:#ede9fe; color:#6d28d9;',
            pembayaran: 'background:#d1fae5; color:#047857;',
        };
        typeBadge.textContent = p.type_label;
        typeBadge.style.cssText = (typeStyles[p.type] || '') + 'display:inline-block;';
    } else {
        typeBadge.style.display = 'none';
    }

    document.getElementById('detail-freq').textContent       = p.frequency + '×';
    document.getElementById('detail-per-termin').textContent = fmt(Math.round(p.nominal_per_termin));
    document.getElementById('detail-pagu').textContent     = fmt(p.total_amount);
    document.getElementById('amount-max-label').textContent = fmt(p.total_amount);
    document.getElementById('amount-input').max = p.total_amount;

    const amountDisplay = document.getElementById('amount-display');
    const amountInput   = document.getElementById('amount-input');
    if (!amountInput.value) {
        const nominal = Math.round(p.nominal_per_termin);
        amountDisplay.value = nominal.toLocaleString('id-ID');
        amountInput.value   = nominal;
    }

    const titleInput = document.getElementById('title-input');
    if (!titleInput.value) titleInput.value = p.name;

    show('program-detail');
    show('form-fields');
    show('amount-hint');
    document.getElementById('submit-btn').disabled = false;
}

document.addEventListener('DOMContentLoaded', function () {
    fetch(`${programsUrl}?organization_id=${orgId}&department_id=${deptId}`)
        .then(r => r.json())
        .then(data => {
            hide('programs-loading');

            if (!data.programs || data.programs.length === 0) {
                show('no-program-msg');
                return;
            }

            const sel = document.getElementById('program-select');
            data.programs.forEach(p => {
                programsCache[p.id] = p;
                const opt = document.createElement('option');
                opt.value = p.id;
                const jenis = p.type_label && p.type_label !== '-' ? `[${p.type_label}] ` : '';
                opt.textContent = `${jenis}${p.name} — ${fmt(p.total_amount)} (${p.frequency}× @ ${fmt(Math.round(p.nominal_per_termin))})`;
                sel.appendChild(opt);
            });

            show('program-row');

            // Restore old value setelah validation error
            const oldVal = '{{ old('budget_program_id') }}';
            if (oldVal && programsCache[oldVal]) {
                sel.value = oldVal;
                onProgramChange(oldVal, true);
            }
        })
        .catch(() => {
            hide('programs-loading');
            show('no-program-msg');
        });
});
</script>
@endif
</x-layouts.app>
