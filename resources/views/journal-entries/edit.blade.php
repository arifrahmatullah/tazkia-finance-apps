<x-layouts.app title="Edit Jurnal {{ $journalEntry->reference }}">

<style>
.balance-ok  { background:#f0fdf4; color:#15803d; border:1px solid #bbf7d0; }
.balance-err { background:#fff1f2; color:#e11d48; border:1px solid #fecdd3; }
.line-debit:focus  { border-color:#2563eb !important; box-shadow:0 0 0 3px rgba(37,99,235,.1); }
.line-credit:focus { border-color:#16a34a !important; box-shadow:0 0 0 3px rgba(22,163,74,.1); }
</style>

<a href="{{ route('journal-entries.show', $journalEntry) }}" class="inline-flex items-center gap-1.5 text-sm text-slate-500 hover:text-orange-500 mb-5 no-underline">
    <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M19 12H5M12 5l-7 7 7 7"/></svg>
    Kembali ke Detail Jurnal
</a>

<h1 class="text-xl font-bold text-slate-900 m-0 mb-2">Edit Jurnal</h1>
<div class="flex items-center gap-2.5 mb-5">
    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-slate-100 rounded-lg text-sm text-slate-600 font-medium">
        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M3 21h18M3 7v1a3 3 0 006 0V7m6 0v1a3 3 0 006 0V7M3 7l2-4h14l2 4"/></svg>
        {{ $journalEntry->organization->name }}
    </span>
    <span class="font-mono text-sm text-orange-500 font-bold">{{ $journalEntry->reference }}</span>
</div>

@if($errors->has('lines'))
    <div class="flex items-center gap-2.5 px-4 py-3 bg-red-50 border border-red-200 rounded-xl mb-4 text-sm text-red-600">
        {{ $errors->first('lines') }}
    </div>
@endif

<form method="POST" action="{{ route('journal-entries.update', $journalEntry) }}" id="je-form">
@csrf @method('PUT')

{{-- Header --}}
<div class="bg-white rounded-xl shadow-sm p-6 mb-3.5">
    <div class="text-[11px] font-bold text-slate-400 uppercase tracking-widest mb-3.5 pb-2 border-b border-slate-100">Informasi Jurnal</div>
    <div class="grid grid-cols-3 gap-4">
        <div class="flex flex-col gap-1.5">
            <label class="text-xs font-semibold text-slate-600">Tanggal <span class="text-red-500 ml-0.5">*</span></label>
            <input type="date" name="entry_date" value="{{ old('entry_date', $journalEntry->entry_date->format('Y-m-d')) }}"
                class="w-full px-3 py-2.5 border border-slate-200 rounded-xl text-sm text-slate-800 bg-white outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100 transition-colors {{ $errors->has('entry_date') ? 'border-red-400' : '' }}">
            @error('entry_date')<div class="text-xs text-red-500 mt-0.5">{{ $message }}</div>@enderror
        </div>

        <div class="flex flex-col gap-1.5 col-span-2">
            <label class="text-xs font-semibold text-slate-600">Keterangan</label>
            <input type="text" name="description" value="{{ old('description', $journalEntry->description) }}" maxlength="500"
                class="w-full px-3 py-2.5 border border-slate-200 rounded-xl text-sm text-slate-800 bg-white outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100 transition-colors"
                placeholder="Keterangan transaksi (opsional)">
            @error('description')<div class="text-xs text-red-500 mt-0.5">{{ $message }}</div>@enderror
        </div>
    </div>
</div>

{{-- Lines --}}
<div class="bg-white rounded-xl shadow-sm p-6 mb-3.5">
    <div class="text-[11px] font-bold text-slate-400 uppercase tracking-widest mb-3.5 pb-2 border-b border-slate-100">Baris Jurnal</div>

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
                            <span id="balance-text">Memuat...</span>
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
    <a href="{{ route('journal-entries.show', $journalEntry) }}" class="px-5 py-2.5 rounded-xl border border-slate-200 bg-white text-slate-600 text-sm font-medium no-underline inline-flex items-center">Batal</a>
    <button type="submit" class="px-6 py-2.5 rounded-xl text-sm font-semibold bg-gradient-to-br from-orange-400 to-orange-500 text-white border-0 cursor-pointer hover:-translate-y-px transition-all">Simpan Perubahan</button>
</div>
</form>

@php
$accountData = $accounts->map(fn($a) => [
    'id'   => $a->id,
    'code' => $a->code,
    'name' => $a->name,
    'type' => $a->account_type,
])->values()->toArray();

$oldLines = old('lines');
if ($oldLines) {
    $existingLineData = collect($oldLines)->values()->toArray();
} else {
    $existingLineData = $journalEntry->lines->map(fn($l) => [
        'account_id'  => $l->account_id,
        'description' => $l->description,
        'debit'       => $l->debit,
        'credit'      => $l->credit,
    ])->values()->toArray();
}
@endphp
<script>
let accountOptions = @json($accountData);
let lineCount = 0;

const inputCls   = 'w-full px-2.5 py-1.5 border border-slate-200 rounded-lg text-sm text-slate-800 bg-white outline-none transition-colors';
const selectCls  = 'w-full px-2.5 py-1.5 border border-slate-200 rounded-lg text-sm text-slate-800 bg-white outline-none transition-colors';
const debitCls   = inputCls + ' border-blue-200 line-debit text-right';
const creditCls  = inputCls + ' border-green-200 line-credit text-right';

function buildAccountOptions(selectedId) {
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
            <input type="number" name="lines[${n}][debit]" value="${data ? data.debit : 0}"
                class="${debitCls}" min="0" step="1"
                oninput="onDebitInput(this, ${n})" onfocus="this.select()">
        </td>
        <td class="px-2 py-1.5 align-middle w-[160px]">
            <input type="number" name="lines[${n}][credit]" value="${data ? data.credit : 0}"
                class="${creditCls}" min="0" step="1"
                oninput="onCreditInput(this, ${n})" onfocus="this.select()">
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

function onDebitInput(el, n) {
    if ((parseFloat(el.value) || 0) > 0) {
        const cr = document.querySelector(`input[name="lines[${n}][credit]"]`);
        if (cr) cr.value = 0;
    }
    updateTotals();
}

function onCreditInput(el, n) {
    if ((parseFloat(el.value) || 0) > 0) {
        const dr = document.querySelector(`input[name="lines[${n}][debit]"]`);
        if (dr) dr.value = 0;
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

const existingLines = @json($existingLineData);

document.addEventListener('DOMContentLoaded', function() {
    existingLines.forEach(line => addLine(line));
    updateTotals();
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
    if (Math.abs(totalDebit - totalCredit) > 0.01) { alert('Total debit harus sama dengan total kredit.'); e.preventDefault(); return; }
});
</script>
</x-layouts.app>
