<x-layouts.app title="Tambah Jurnal">

<style>
.balance-ok  { background:#f0fdf4; color:#15803d; border:1px solid #bbf7d0; }
.balance-err { background:#fff1f2; color:#e11d48; border:1px solid #fecdd3; }
.line-debit:focus  { border-color:#2563eb !important; box-shadow:0 0 0 3px rgba(37,99,235,.1); }
.line-credit:focus { border-color:#16a34a !important; box-shadow:0 0 0 3px rgba(22,163,74,.1); }
</style>

<a href="{{ route('journal-entries.index', ['organization_id' => $selectedOrgId]) }}" class="inline-flex items-center gap-1.5 text-sm text-slate-500 hover:text-orange-500 mb-5 no-underline">
    <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M19 12H5M12 5l-7 7 7 7"/></svg>
    Kembali ke Jurnal Umum
</a>

<h1 class="text-xl font-bold text-slate-900 m-0 mb-0.5">Tambah Jurnal</h1>
<p class="text-sm text-slate-400 mb-5">Catat transaksi keuangan dengan entri debit dan kredit yang seimbang</p>

@if($errors->has('lines'))
    <div class="flex items-center gap-2.5 px-4 py-3 bg-red-50 border border-red-200 rounded-xl mb-4 text-sm text-red-600">
        {{ $errors->first('lines') }}
    </div>
@endif

<form method="POST" action="{{ route('journal-entries.store') }}" id="je-form">
@csrf

{{-- Header --}}
<div class="bg-white rounded-xl shadow-sm p-6 mb-3.5">
    <div class="text-[11px] font-bold text-slate-400 uppercase tracking-widest mb-3.5 pb-2 border-b border-slate-100">Informasi Jurnal</div>
    <div class="grid grid-cols-3 gap-4">
        @if($organizations->count() > 1)
        <div class="flex flex-col gap-1.5">
            <label class="text-xs font-semibold text-slate-600">Organisasi <span class="text-red-500 ml-0.5">*</span></label>
            <select name="organization_id" id="org-select"
                class="w-full px-3 py-2.5 border border-slate-200 rounded-xl text-sm text-slate-800 bg-white outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100 transition-colors {{ $errors->has('organization_id') ? 'border-red-400' : '' }}"
                onchange="loadAccounts(this.value)">
                <option value="">-- Pilih Organisasi --</option>
                @foreach($organizations as $org)
                    <option value="{{ $org->id }}" {{ old('organization_id', $selectedOrgId) == $org->id ? 'selected' : '' }}>{{ $org->name }}</option>
                @endforeach
            </select>
            @error('organization_id')<div class="text-xs text-red-500 mt-0.5">{{ $message }}</div>@enderror
        </div>
        @else
            <input type="hidden" name="organization_id" value="{{ $organizations->first()?->id }}">
        @endif

        <div class="flex flex-col gap-1.5">
            <label class="text-xs font-semibold text-slate-600">Tanggal <span class="text-red-500 ml-0.5">*</span></label>
            <input type="date" name="entry_date" value="{{ old('entry_date', date('Y-m-d')) }}"
                class="w-full px-3 py-2.5 border border-slate-200 rounded-xl text-sm text-slate-800 bg-white outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100 transition-colors {{ $errors->has('entry_date') ? 'border-red-400' : '' }}">
            @error('entry_date')<div class="text-xs text-red-500 mt-0.5">{{ $message }}</div>@enderror
        </div>

        <div class="flex flex-col gap-1.5 {{ $organizations->count() > 1 ? 'col-span-3' : 'col-span-2' }}">
            <label class="text-xs font-semibold text-slate-600">Keterangan</label>
            <input type="text" name="description" value="{{ old('description') }}" maxlength="500"
                class="w-full px-3 py-2.5 border border-slate-200 rounded-xl text-sm text-slate-800 bg-white outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100 transition-colors"
                placeholder="Keterangan transaksi (opsional)">
            @error('description')<div class="text-xs text-red-500 mt-0.5">{{ $message }}</div>@enderror
        </div>
    </div>
</div>

{{-- Lines --}}
<div class="bg-white rounded-xl shadow-sm p-6 mb-3.5">
    <div class="text-[11px] font-bold text-slate-400 uppercase tracking-widest mb-3.5 pb-2 border-b border-slate-100">Baris Jurnal</div>

    {{-- Template picker --}}
    <div class="flex items-center gap-2.5 mb-4 flex-wrap bg-indigo-50/60 border border-indigo-100 rounded-xl px-3.5 py-2.5">
        <svg width="15" height="15" fill="none" stroke="#4f46e5" stroke-width="2" viewBox="0 0 24 24" class="flex-shrink-0"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
        <label class="text-xs font-semibold text-indigo-700 flex-shrink-0">Gunakan Template</label>
        <select id="template-select" onchange="applyTemplate(this.value)"
            class="flex-1 min-w-[220px] px-3 py-2 border border-indigo-200 rounded-lg text-sm text-slate-800 bg-white outline-none focus:border-indigo-400 transition-colors">
            <option value="">-- Tanpa template (isi manual) --</option>
        </select>
        <span class="text-[11px] text-indigo-400">Baris akun terisi otomatis, tinggal isi nominal</span>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full border-collapse" style="min-width:700px;">
            <thead>
                <tr class="bg-slate-50 border-b border-slate-100">
                    <th class="px-3 py-2.5 text-center text-[11px] font-semibold text-slate-400 uppercase tracking-wide w-9">#</th>
                    <th class="px-3 py-2.5 text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wide" style="min-width:220px;">Akun</th>
                    <th class="px-3 py-2.5 text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wide" style="min-width:160px;">Keterangan</th>
                    <th class="px-3 py-2.5 text-right text-[11px] font-semibold text-slate-400 uppercase tracking-wide w-[160px]">Debit (Rp)</th>
                    <th class="px-3 py-2.5 text-right text-[11px] font-semibold text-slate-400 uppercase tracking-wide w-[160px]">Kredit (Rp)</th>
                    <th class="px-3 py-2.5 w-12"></th>
                </tr>
            </thead>
            <tbody id="lines-body"></tbody>
            <tfoot>
                <tr class="bg-slate-50 border-t-2 border-slate-100">
                    <td colspan="3" class="px-3 py-2.5 text-right">
                        <div id="balance-status" class="balance-err inline-flex items-center gap-2 px-3 py-2 rounded-lg text-xs font-semibold">
                            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" id="balance-icon"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                            <span id="balance-text">Jurnal belum seimbang</span>
                        </div>
                    </td>
                    <td class="px-3 py-2.5 text-right">
                        <div class="text-[11px] font-semibold text-slate-400">Total Debit</div>
                        <div class="font-mono text-sm font-bold text-blue-600" id="total-debit">Rp 0</div>
                    </td>
                    <td class="px-3 py-2.5 text-right">
                        <div class="text-[11px] font-semibold text-slate-400">Total Kredit</div>
                        <div class="font-mono text-sm font-bold text-green-600" id="total-kredit">Rp 0</div>
                    </td>
                    <td></td>
                </tr>
            </tfoot>
        </table>
    </div>

    <button type="button" onclick="addLine()"
        class="inline-flex items-center gap-1.5 px-4 py-2 mt-3 rounded-xl border border-dashed border-slate-200 bg-slate-50 text-slate-500 text-sm cursor-pointer hover:border-orange-400 hover:text-orange-500 hover:bg-orange-50 transition-colors">
        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg>
        Tambah Baris
    </button>
</div>

<div class="flex gap-3 justify-end">
    <a href="{{ route('journal-entries.index', ['organization_id' => $selectedOrgId]) }}" class="px-5 py-2.5 rounded-xl border border-slate-200 bg-white text-slate-600 text-sm font-medium no-underline inline-flex items-center">Batal</a>
    <button type="submit" class="px-6 py-2.5 rounded-xl text-sm font-semibold bg-gradient-to-br from-orange-400 to-orange-500 text-white border-0 cursor-pointer hover:-translate-y-px transition-all" id="btn-submit">Simpan Jurnal</button>
</div>
</form>

@php
$accountData = $accounts->map(fn($a) => [
    'id'   => $a->id,
    'code' => $a->code,
    'name' => $a->name,
    'type' => $a->account_type,
])->values()->toArray();
@endphp
<script>
let accountOptions = @json($accountData);
let lineCount = 0;

const inputCls   = 'w-full px-2.5 py-1.5 border border-slate-200 rounded-lg text-sm text-slate-800 bg-white outline-none transition-colors';
const selectCls  = 'w-full px-2.5 py-1.5 border border-slate-200 rounded-lg text-sm text-slate-800 bg-white outline-none transition-colors';
const debitCls   = inputCls + ' border-blue-200 line-debit text-right';
const creditCls  = inputCls + ' border-green-200 line-credit text-right';

function buildAccountOptions(selectedId) {
    if (!accountOptions.length) return '<option value="">-- Pilih Organisasi dulu --</option>';
    let html = '<option value="">-- Pilih Akun --</option>';
    const groups = {};
    accountOptions.forEach(a => {
        if (!groups[a.type]) groups[a.type] = [];
        groups[a.type].push(a);
    });
    const labels = {aset:'Aset',kewajiban:'Kewajiban',ekuitas:'Ekuitas',pendapatan:'Pendapatan',beban:'Beban'};
    Object.keys(labels).forEach(type => {
        if (!groups[type]) return;
        html += `<optgroup label="${labels[type]}">`;
        groups[type].forEach(a => {
            const sel = selectedId && selectedId == a.id ? ' selected' : '';
            html += `<option value="${a.id}"${sel}>${a.code} – ${a.name}</option>`;
        });
        html += '</optgroup>';
    });
    return html;
}

function addLine(data) {
    const n = ++lineCount;
    const idx = document.querySelectorAll('#lines-body tr').length;
    const tr = document.createElement('tr');
    tr.id = `line-row-${n}`;
    tr.className = 'border-b border-slate-50 hover:bg-slate-50/50';
    tr.innerHTML = `
        <td class="px-3 py-1.5 text-center text-xs text-slate-400 font-semibold align-middle" id="line-no-${n}">${idx + 1}</td>
        <td class="px-2 py-1.5 align-middle">
            <select name="lines[${n}][account_id]" class="${selectCls}" onchange="updateTotals()">
                ${buildAccountOptions(data ? data.account_id : null)}
            </select>
        </td>
        <td class="px-2 py-1.5 align-middle">
            <input type="text" name="lines[${n}][description]" value="${data ? (data.description || '') : ''}"
                class="${inputCls}" placeholder="Keterangan (opsional)" maxlength="255">
        </td>
        <td class="px-2 py-1.5 align-middle w-[160px]">
            <input type="text" inputmode="numeric" autocomplete="off"
                value="${data && data.debit > 0 ? Number(data.debit).toLocaleString('id-ID') : ''}"
                class="${debitCls}" placeholder="0" data-line="${n}" data-field="debit"
                oninput="onDebitInput(this, ${n})" onfocus="this.select()">
            <input type="hidden" name="lines[${n}][debit]" value="${data ? data.debit : 0}">
        </td>
        <td class="px-2 py-1.5 align-middle w-[160px]">
            <input type="text" inputmode="numeric" autocomplete="off"
                value="${data && data.credit > 0 ? Number(data.credit).toLocaleString('id-ID') : ''}"
                class="${creditCls}" placeholder="0" data-line="${n}" data-field="credit"
                oninput="onCreditInput(this, ${n})" onfocus="this.select()">
            <input type="hidden" name="lines[${n}][credit]" value="${data ? data.credit : 0}">
        </td>
        <td class="px-2 py-1.5 text-center align-middle">
            <button type="button" onclick="removeLine(${n})" title="Hapus baris"
                class="px-2 py-1 rounded-lg border-0 bg-red-50 text-red-500 hover:bg-red-100 cursor-pointer text-xs font-bold">✕</button>
        </td>
    `;
    document.getElementById('lines-body').appendChild(tr);
    updateLineNumbers();
    updateTotals();
}

function removeLine(n) {
    const tr = document.getElementById(`line-row-${n}`);
    if (tr) tr.remove();
    updateLineNumbers();
    updateTotals();
}

function updateLineNumbers() {
    document.querySelectorAll('#lines-body tr').forEach((tr, i) => {
        const noCell = tr.querySelector('[id^="line-no-"]');
        if (noCell) noCell.textContent = i + 1;
    });
}

function formatLineInput(el) {
    const digits = el.value.replace(/\D/g, '');
    el.value = digits ? Number(digits).toLocaleString('id-ID') : '';
    return digits ? Number(digits) : 0;
}

function onDebitInput(el, n) {
    const val = formatLineInput(el);
    const hiddenDebit = document.querySelector(`input[type="hidden"][name="lines[${n}][debit]"]`);
    if (hiddenDebit) hiddenDebit.value = val;
    if (val > 0) {
        const crDisplay = document.querySelector(`input[data-line="${n}"][data-field="credit"]`);
        const crHidden  = document.querySelector(`input[type="hidden"][name="lines[${n}][credit]"]`);
        if (crDisplay) crDisplay.value = '';
        if (crHidden) crHidden.value = 0;
    }
    updateTotals();
}

function onCreditInput(el, n) {
    const val = formatLineInput(el);
    const hiddenCredit = document.querySelector(`input[type="hidden"][name="lines[${n}][credit]"]`);
    if (hiddenCredit) hiddenCredit.value = val;
    if (val > 0) {
        const drDisplay = document.querySelector(`input[data-line="${n}"][data-field="debit"]`);
        const drHidden  = document.querySelector(`input[type="hidden"][name="lines[${n}][debit]"]`);
        if (drDisplay) drDisplay.value = '';
        if (drHidden) drHidden.value = 0;
    }
    updateTotals();
}

function fmt(n) {
    return 'Rp ' + Math.round(n).toLocaleString('id-ID');
}

function updateTotals() {
    let totalDebit = 0, totalCredit = 0;
    document.querySelectorAll('#lines-body tr').forEach(tr => {
        totalDebit  += parseFloat(tr.querySelector('input[name*="[debit]"]')?.value)  || 0;
        totalCredit += parseFloat(tr.querySelector('input[name*="[credit]"]')?.value) || 0;
    });
    document.getElementById('total-debit').textContent  = fmt(totalDebit);
    document.getElementById('total-kredit').textContent = fmt(totalCredit);

    const balanced = totalDebit > 0 && Math.abs(totalDebit - totalCredit) < 0.01;
    const statusEl = document.getElementById('balance-status');
    const iconEl   = document.getElementById('balance-icon');
    const textEl   = document.getElementById('balance-text');
    if (balanced) {
        statusEl.className = 'balance-ok inline-flex items-center gap-2 px-3 py-2 rounded-lg text-xs font-semibold';
        iconEl.innerHTML = '<path d="M22 11.08V12a10 10 0 11-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/>';
        textEl.textContent = 'Jurnal seimbang ✓';
    } else {
        statusEl.className = 'balance-err inline-flex items-center gap-2 px-3 py-2 rounded-lg text-xs font-semibold';
        iconEl.innerHTML = '<circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>';
        const diff = Math.abs(totalDebit - totalCredit);
        textEl.textContent = totalDebit === 0 ? 'Jurnal belum seimbang' : `Selisih: ${fmt(diff)}`;
    }
}

async function loadAccounts(orgId) {
    loadTemplates(orgId);
    if (!orgId) { accountOptions = []; rebuildAllAccountSelects(); updateTotals(); return; }
    try {
        const res = await fetch(`{{ route('journal-entries.accounts') }}?organization_id=${orgId}`);
        accountOptions = await res.json();
    } catch(e) { accountOptions = []; }
    rebuildAllAccountSelects();
    updateTotals();
}

async function loadTemplates(orgId) {
    const sel = document.getElementById('template-select');
    sel.innerHTML = '<option value="">-- Tanpa template (isi manual) --</option>';
    if (!orgId) return;
    try {
        const res = await fetch(`{{ route('journal-templates.options') }}?organization_id=${orgId}`);
        const templates = await res.json();
        templates.forEach(t => {
            const opt = document.createElement('option');
            opt.value = t.id;
            opt.textContent = t.code + ' – ' + t.name + (t.category ? ' (' + t.category + ')' : '');
            sel.appendChild(opt);
        });
    } catch(e) { /* biarkan kosong */ }
}

async function applyTemplate(templateId) {
    if (!templateId) return;
    try {
        const res = await fetch(`{{ url('journal-templates') }}/${templateId}/lines`);
        const data = await res.json();

        document.getElementById('lines-body').innerHTML = '';
        data.lines.forEach(line => {
            addLine({
                account_id:  line.account_id,
                description: line.description || '',
                debit:  0,
                credit: 0,
            });
        });
        // Tandai posisi debit/kredit dari template pada atribut data untuk fokus pertama
        const rows = document.querySelectorAll('#lines-body tr');
        rows.forEach((tr, i) => {
            tr.dataset.balanceType = data.lines[i]?.balance_type || '';
        });

        const descInput = document.querySelector('input[name="description"]');
        if (descInput && !descInput.value) descInput.value = data.name;

        updateTotals();

        // Fokus ke nominal baris pertama sesuai posisinya
        const first = rows[0];
        if (first) {
            const field = first.dataset.balanceType === 'credit' ? 'credit' : 'debit';
            first.querySelector(`input[data-field="${field}"]`)?.focus();
        }
    } catch(e) {
        alert('Gagal memuat template. Coba lagi.');
    }
}

function rebuildAllAccountSelects() {
    document.querySelectorAll('#lines-body select[name*="[account_id]"]').forEach(sel => {
        const cur = sel.value;
        sel.innerHTML = buildAccountOptions(cur);
    });
}

const oldLines = @json(old('lines', []));
document.addEventListener('DOMContentLoaded', function() {
    if (oldLines && oldLines.length > 0) {
        oldLines.forEach(line => addLine(line));
    } else {
        addLine(); addLine(); addLine();
    }
    updateTotals();

    const initialOrg = document.getElementById('org-select')?.value
        || document.querySelector('input[name="organization_id"]')?.value;
    loadTemplates(initialOrg);
});

document.getElementById('je-form').addEventListener('submit', function(e) {
    const rows = document.querySelectorAll('#lines-body tr');
    if (rows.length < 2) { alert('Jurnal harus memiliki minimal 2 baris.'); e.preventDefault(); return; }
    let totalDebit = 0, totalCredit = 0;
    rows.forEach(tr => {
        totalDebit  += parseFloat(tr.querySelector('input[name*="[debit]"]')?.value)  || 0;
        totalCredit += parseFloat(tr.querySelector('input[name*="[credit]"]')?.value) || 0;
    });
    if (totalDebit <= 0) { alert('Jurnal harus memiliki nilai debit dan kredit.'); e.preventDefault(); return; }
    if (Math.abs(totalDebit - totalCredit) > 0.01) { alert('Total debit harus sama dengan total kredit sebelum menyimpan.'); e.preventDefault(); return; }
});
</script>
</x-layouts.app>
