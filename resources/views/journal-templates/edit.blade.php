<x-layouts.app title="Edit Template Jurnal">

<a href="{{ route('journal-templates.index') }}" class="inline-flex items-center gap-1.5 text-sm text-slate-500 hover:text-orange-500 mb-5 no-underline">
    <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M19 12H5M12 5l-7 7 7 7"/></svg>
    Kembali ke Template Jurnal
</a>

<h1 class="text-xl font-bold text-slate-900 m-0 mb-0.5">Edit Template Jurnal</h1>
<p class="text-sm text-slate-400 mb-5">{{ $journalTemplate->code }} — {{ $journalTemplate->organization?->name }}</p>

@if($errors->has('details'))
<div class="flex items-center gap-2.5 px-4 py-3 bg-red-50 border border-red-200 rounded-xl mb-4 text-sm text-red-600">
    {{ $errors->first('details') }}
</div>
@endif

<form method="POST" action="{{ route('journal-templates.update', $journalTemplate) }}" id="jt-form">
@csrf
@method('PUT')

{{-- Header --}}
<div class="bg-white rounded-xl shadow-sm p-6 mb-3.5">
    <div class="text-[11px] font-bold text-slate-400 uppercase tracking-widest mb-3.5 pb-2 border-b border-slate-100">Informasi Template</div>
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div class="flex flex-col gap-1.5">
            <label class="text-xs font-semibold text-slate-600">Kode Template <span class="text-red-500 ml-0.5">*</span></label>
            <input type="text" name="code" value="{{ old('code', $journalTemplate->code) }}" maxlength="50"
                class="w-full px-3 py-2.5 border border-slate-200 rounded-xl text-sm text-slate-800 bg-white outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100 transition-colors {{ $errors->has('code') ? 'border-red-400' : '' }}">
            @error('code')<div class="text-xs text-red-500 mt-0.5">{{ $message }}</div>@enderror
        </div>

        <div class="flex flex-col gap-1.5">
            <label class="text-xs font-semibold text-slate-600">Nama Template <span class="text-red-500 ml-0.5">*</span></label>
            <input type="text" name="name" value="{{ old('name', $journalTemplate->name) }}" maxlength="255"
                class="w-full px-3 py-2.5 border border-slate-200 rounded-xl text-sm text-slate-800 bg-white outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100 transition-colors {{ $errors->has('name') ? 'border-red-400' : '' }}">
            @error('name')<div class="text-xs text-red-500 mt-0.5">{{ $message }}</div>@enderror
        </div>

        <div class="flex flex-col gap-1.5">
            <label class="text-xs font-semibold text-slate-600">Kategori</label>
            <input type="text" name="category" value="{{ old('category', $journalTemplate->category) }}" maxlength="100" list="category-list" placeholder="Opsional"
                class="w-full px-3 py-2.5 border border-slate-200 rounded-xl text-sm text-slate-800 bg-white outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100 transition-colors">
            <datalist id="category-list">
                @foreach($categories as $cat)<option value="{{ $cat }}"></option>@endforeach
            </datalist>
        </div>

        <div class="flex flex-col gap-1.5">
            <label class="text-xs font-semibold text-slate-600">Status</label>
            <label class="flex items-center gap-2.5 px-3 py-2.5 border border-slate-200 rounded-xl cursor-pointer">
                <input type="checkbox" name="is_active" value="1" {{ old('is_active', $journalTemplate->is_active) ? 'checked' : '' }}
                    class="w-4 h-4 cursor-pointer" style="accent-color:#f97316;">
                <span class="text-sm text-slate-700">Aktif (muncul di pilihan template saat input jurnal)</span>
            </label>
        </div>
    </div>
</div>

{{-- Detail lines --}}
<div class="bg-white rounded-xl shadow-sm p-6 mb-3.5">
    <div class="text-[11px] font-bold text-slate-400 uppercase tracking-widest mb-3.5 pb-2 border-b border-slate-100">Baris Template (Akun &amp; Posisi)</div>
    <div class="text-xs text-slate-400 mb-3">Minimal 2 baris: satu debit dan satu kredit. Nominal diisi nanti saat template dipakai di jurnal umum.</div>

    <div class="overflow-x-auto">
        <table class="w-full border-collapse" style="min-width:640px;">
            <thead>
                <tr class="bg-slate-50 border-b border-slate-100">
                    <th class="px-3 py-2.5 text-center text-[11px] font-semibold text-slate-400 uppercase tracking-wide w-9">#</th>
                    <th class="px-3 py-2.5 text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wide" style="min-width:240px;">Akun</th>
                    <th class="px-3 py-2.5 text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wide w-[130px]">Posisi</th>
                    <th class="px-3 py-2.5 text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wide" style="min-width:160px;">Keterangan Default</th>
                    <th class="px-3 py-2.5 w-12"></th>
                </tr>
            </thead>
            <tbody id="details-body"></tbody>
        </table>
    </div>

    <button type="button" onclick="addDetailRow()"
        class="inline-flex items-center gap-1.5 px-4 py-2 mt-3 rounded-xl border border-dashed border-slate-200 bg-slate-50 text-slate-500 text-sm cursor-pointer hover:border-orange-400 hover:text-orange-500 hover:bg-orange-50 transition-colors">
        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg>
        Tambah Baris
    </button>
</div>

<div class="flex gap-3 justify-end">
    <a href="{{ route('journal-templates.index') }}" class="px-5 py-2.5 rounded-xl border border-slate-200 bg-white text-slate-600 text-sm font-medium no-underline inline-flex items-center">Batal</a>
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

$existingDetails = old('details') ? array_values(old('details')) : $journalTemplate->details->map(fn($d) => [
    'account_id'   => $d->account_id,
    'balance_type' => $d->balance_type,
    'description'  => $d->description,
])->values()->toArray();
@endphp
<script>
let accountOptions = @json($accountData);
let detailCount = 0;

const inputCls = 'w-full px-2.5 py-1.5 border border-slate-200 rounded-lg text-sm text-slate-800 bg-white outline-none transition-colors';

function buildAccountOptions(selectedId) {
    if (!accountOptions.length) return '<option value="">-- Pilih Akun --</option>';
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

function addDetailRow(data) {
    const n = ++detailCount;
    const idx = document.querySelectorAll('#details-body tr').length;
    const bt = data ? data.balance_type : (idx === 0 ? 'debit' : 'credit');
    const tr = document.createElement('tr');
    tr.id = `detail-row-${n}`;
    tr.className = 'border-b border-slate-50 hover:bg-slate-50/50';
    tr.innerHTML = `
        <td class="px-3 py-1.5 text-center text-xs text-slate-400 font-semibold align-middle detail-no">${idx + 1}</td>
        <td class="px-2 py-1.5 align-middle">
            <select name="details[${n}][account_id]" class="${inputCls}">
                ${buildAccountOptions(data ? data.account_id : null)}
            </select>
        </td>
        <td class="px-2 py-1.5 align-middle w-[130px]">
            <select name="details[${n}][balance_type]" class="${inputCls} font-semibold" onchange="styleBalanceSelect(this)">
                <option value="debit" ${bt === 'debit' ? 'selected' : ''}>Debit</option>
                <option value="credit" ${bt === 'credit' ? 'selected' : ''}>Kredit</option>
            </select>
        </td>
        <td class="px-2 py-1.5 align-middle">
            <input type="text" name="details[${n}][description]" value="${data ? (data.description || '') : ''}"
                class="${inputCls}" placeholder="Keterangan (opsional)" maxlength="255">
        </td>
        <td class="px-2 py-1.5 text-center align-middle">
            <button type="button" onclick="removeDetailRow(${n})" title="Hapus baris"
                class="px-2 py-1 rounded-lg border-0 bg-red-50 text-red-500 hover:bg-red-100 cursor-pointer text-xs font-bold">✕</button>
        </td>
    `;
    document.getElementById('details-body').appendChild(tr);
    styleBalanceSelect(tr.querySelector('select[name*="[balance_type]"]'));
    updateRowNumbers();
}

function styleBalanceSelect(sel) {
    sel.style.color = sel.value === 'debit' ? '#2563eb' : '#16a34a';
    sel.style.borderColor = sel.value === 'debit' ? '#bfdbfe' : '#bbf7d0';
}

function removeDetailRow(n) {
    const tr = document.getElementById(`detail-row-${n}`);
    if (tr) tr.remove();
    updateRowNumbers();
}

function updateRowNumbers() {
    document.querySelectorAll('#details-body tr').forEach((tr, i) => {
        const noCell = tr.querySelector('.detail-no');
        if (noCell) noCell.textContent = i + 1;
    });
}

const existingDetails = @json($existingDetails);
document.addEventListener('DOMContentLoaded', function() {
    if (existingDetails.length > 0) {
        existingDetails.forEach(d => addDetailRow(d));
    } else {
        addDetailRow({balance_type: 'debit'});
        addDetailRow({balance_type: 'credit'});
    }
});

document.getElementById('jt-form').addEventListener('submit', function(e) {
    const rows = document.querySelectorAll('#details-body tr');
    if (rows.length < 2) { alert('Template minimal memiliki 2 baris.'); e.preventDefault(); return; }
    let hasDebit = false, hasCredit = false, accountMissing = false;
    rows.forEach(tr => {
        const bt = tr.querySelector('select[name*="[balance_type]"]')?.value;
        if (bt === 'debit') hasDebit = true;
        if (bt === 'credit') hasCredit = true;
        if (!tr.querySelector('select[name*="[account_id]"]')?.value) accountMissing = true;
    });
    if (accountMissing) { alert('Semua baris harus memilih akun.'); e.preventDefault(); return; }
    if (!hasDebit || !hasCredit) { alert('Template harus memiliki minimal satu baris debit dan satu baris kredit.'); e.preventDefault(); return; }
});
</script>
</x-layouts.app>
