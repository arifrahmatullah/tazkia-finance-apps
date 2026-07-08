<x-layouts.app title="Tambah Program Kerja">

<a href="{{ route('budget-programs.index') }}" class="inline-flex items-center gap-1.5 text-sm text-slate-500 hover:text-orange-500 mb-5 no-underline transition-colors">
    <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M19 12H5M12 5l-7 7 7 7"/></svg>
    Kembali ke Program Kerja
</a>

<h1 class="text-xl font-bold text-slate-900 m-0 mb-0.5">Tambah Program Kerja</h1>
<p class="text-sm text-slate-400 mb-5">{{ $department->name }} &mdash; {{ $allocation->budgetPeriod->name }}</p>

@if($errors->any())
<div class="flex items-center gap-2.5 px-4 py-3 bg-red-50 border border-red-200 rounded-xl mb-4 text-sm text-red-600">
    {{ $errors->first() }}
</div>
@endif

<form method="POST" action="{{ route('budget-programs.store') }}" id="bp-form">
@csrf
<input type="hidden" name="budget_allocation_id" value="{{ $allocation->id }}">

@php
    $pagu        = (float) $allocation->amount;
    $sisaDisplay = $sisaAlokasi;
@endphp
<script>const paguAmount = {{ $sisaAlokasi }};</script>

{{-- Header --}}
<div class="bg-white rounded-xl shadow-sm p-6 mb-3.5">
    <div class="text-[11px] font-bold text-slate-400 uppercase tracking-widest mb-3.5 pb-2 border-b border-slate-100">Informasi Program</div>
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
        <div class="sm:col-span-2">
            <label class="block text-xs font-semibold text-slate-600 mb-1.5">Nama Program Kerja <span class="text-red-500">*</span></label>
            <input type="text" name="name" value="{{ old('name') }}" autofocus
                placeholder="Contoh: Seminar Nasional, Pelatihan SDM, ..."
                class="w-full px-3.5 py-2.5 border {{ $errors->has('name') ? 'border-red-400' : 'border-slate-200' }} rounded-xl text-sm text-slate-700 bg-white outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100 transition-colors">
            @error('name') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="block text-xs font-semibold text-slate-600 mb-1.5">
                Frekuensi <span class="text-red-500">*</span>
                <span class="font-normal text-slate-400 ml-1">— berapa kali dalam periode</span>
            </label>
            <div class="flex items-center gap-2 flex-wrap">
                <input type="number" name="frequency" id="frequency-input" value="{{ old('frequency', 1) }}"
                    min="1" max="366"
                    class="w-24 px-3.5 py-2.5 border {{ $errors->has('frequency') ? 'border-red-400' : 'border-slate-200' }} rounded-xl text-sm text-slate-700 bg-white outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100 transition-colors font-mono"
                    oninput="onFrequencyChange(this.value)">
                <span class="text-xs text-slate-400">kali</span>
                <div class="flex gap-1.5">
                    @foreach([['1','1×'],['3','3×'],['4','4×'],['6','6×'],['12','12×']] as [$v,$l])
                    <button type="button" onclick="setFrequency({{ $v }})"
                        data-val="{{ $v }}"
                        class="freq-btn px-2.5 py-1.5 rounded-lg border text-xs font-semibold transition-colors {{ old('frequency', 1) == $v ? 'bg-orange-500 text-white border-orange-500' : 'border-slate-200 text-slate-500 hover:border-orange-300 hover:text-orange-500' }}">
                        {{ $l }}
                    </button>
                    @endforeach
                </div>
            </div>
            @error('frequency') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="block text-xs font-semibold text-slate-600 mb-1.5">Keterangan</label>
            <input type="text" name="notes" value="{{ old('notes') }}"
                placeholder="Deskripsi singkat (opsional)"
                class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl text-sm text-slate-700 bg-white outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100 transition-colors">
        </div>
    </div>
</div>

{{-- Rincian --}}
<div class="bg-white rounded-xl shadow-sm p-6 mb-3.5">
    <div class="text-[11px] font-bold text-slate-400 uppercase tracking-widest mb-3.5 pb-2 border-b border-slate-100">Rincian Kegiatan</div>

    <div class="overflow-x-auto">
        <table class="w-full border-collapse" style="min-width:520px;">
            <thead>
                <tr class="bg-slate-50 border-b border-slate-100">
                    <th class="px-3 py-2.5 text-center text-[11px] font-semibold text-slate-400 uppercase tracking-wide w-9">#</th>
                    <th class="px-3 py-2.5 text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wide" style="min-width:180px;">Jenis Pengeluaran</th>
                    <th class="px-3 py-2.5 text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wide" style="min-width:200px;">Deskripsi</th>
                    <th class="px-3 py-2.5 text-right text-[11px] font-semibold text-slate-400 uppercase tracking-wide w-[150px]">Nominal/Termin</th>
                    <th class="px-3 py-2.5 text-right text-[11px] font-semibold text-slate-400 uppercase tracking-wide w-[140px]">Total <span id="freq-label" class="text-orange-500">(×1)</span></th>
                    <th class="px-3 py-2.5 w-10"></th>
                </tr>
            </thead>
            <tbody id="lines-body"></tbody>
            <tfoot>
                <tr class="bg-slate-50 border-t-2 border-slate-100">
                    <td colspan="2" class="px-3 py-2.5 text-right">
                        @if($pagu > 0)
                        <div id="pagu-status" class="inline-flex items-center gap-2 px-3 py-2 rounded-lg text-xs font-semibold bg-green-50 text-green-700 border border-green-200">
                            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" id="pagu-icon"><path d="M22 11.08V12a10 10 0 11-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                            <span id="pagu-text">Sisa pagu: Rp {{ number_format($sisaAlokasi, 0, ',', '.') }}</span>
                        </div>
                        @endif
                    </td>
                    <td class="px-3 py-2.5 text-right">
                        <div class="text-[11px] font-semibold text-slate-400">Sisa Pagu</div>
                        <div class="font-mono text-sm font-bold text-green-600" id="total-sisa">Rp {{ number_format($sisaAlokasi, 0, ',', '.') }}</div>
                    </td>
                    <td class="px-3 py-2.5 text-right">
                        <div class="text-[11px] font-semibold text-slate-400">Total Rincian</div>
                        <div class="font-mono text-sm font-bold text-orange-600" id="total-all">Rp 0</div>
                    </td>
                    <td></td>
                </tr>
            </tfoot>
        </table>
    </div>

    <button type="button" onclick="addLine()"
        class="inline-flex items-center gap-1.5 px-4 py-2 mt-3 rounded-xl bg-sky-400 text-white text-sm font-semibold cursor-pointer border-0 hover:bg-sky-500 transition-colors shadow-sm">
        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg>
        Tambah Baris
    </button>
</div>

<div class="flex gap-3 justify-end">
    <a href="{{ route('budget-programs.index') }}" class="px-5 py-2.5 rounded-xl border border-slate-200 bg-white text-slate-600 text-sm font-medium no-underline inline-flex items-center">Batal</a>
    <button type="submit" class="px-6 py-2.5 rounded-xl text-sm font-semibold bg-gradient-to-br from-orange-400 to-orange-500 text-white border-0 cursor-pointer hover:-translate-y-px transition-all">Simpan Program</button>
</div>
</form>

@php
$accountData = $accounts->map(fn($a) => ['id' => $a->id, 'code' => $a->code, 'name' => $a->name])->values()->toArray();
@endphp

<script>
const accountOptions = @json($accountData);
let lineCount = 0;
let currentFreq = parseInt(document.getElementById('frequency-input')?.value) || 1;

const inputCls  = 'w-full px-2.5 py-1.5 border border-slate-200 rounded-lg text-sm text-slate-800 bg-white outline-none focus:border-orange-400 transition-colors';
const selectCls = 'w-full px-2.5 py-1.5 border border-slate-200 rounded-lg text-sm text-slate-800 bg-white outline-none focus:border-orange-400 transition-colors';

function buildAccountOptions(selectedId) {
    let html = '<option value="">— Pilih jenis pengeluaran —</option>';
    accountOptions.forEach(a => {
        const sel = selectedId && selectedId == a.id ? ' selected' : '';
        html += `<option value="${a.id}"${sel}>${a.code} — ${a.name}</option>`;
    });
    return html;
}

function setFrequency(val) {
    currentFreq = Math.max(1, parseInt(val) || 1);
    document.getElementById('frequency-input').value = currentFreq;
    document.querySelectorAll('.freq-btn').forEach(b => {
        const isActive = parseInt(b.dataset.val) === currentFreq;
        b.className = b.className.replace(/bg-orange-500 text-white border-orange-500|border-slate-200 text-slate-500 hover:border-orange-300 hover:text-orange-500/g, '');
        b.className += isActive
            ? ' bg-orange-500 text-white border-orange-500'
            : ' border-slate-200 text-slate-500 hover:border-orange-300 hover:text-orange-500';
    });
    updateTotals();
}

function onFrequencyChange(val) {
    currentFreq = Math.max(1, parseInt(val) || 1);
    document.querySelectorAll('.freq-btn').forEach(b => {
        const isActive = parseInt(b.dataset.val) === currentFreq;
        b.className = b.className.replace(/bg-orange-500 text-white border-orange-500|border-slate-200 text-slate-500 hover:border-orange-300 hover:text-orange-500/g, '');
        b.className += isActive
            ? ' bg-orange-500 text-white border-orange-500'
            : ' border-slate-200 text-slate-500 hover:border-orange-300 hover:text-orange-500';
    });
    updateTotals();
}

function addLine(data) {
    const n   = ++lineCount;
    const idx = document.querySelectorAll('#lines-body tr').length;
    const tr  = document.createElement('tr');
    tr.id        = `line-row-${n}`;
    tr.className = 'border-b border-slate-50 hover:bg-slate-50/50';
    tr.innerHTML = `
        <td class="px-3 py-2 text-center text-xs text-slate-400 font-semibold align-middle" id="line-no-${n}">${idx + 1}</td>
        <td class="px-2 py-2 align-middle">
            <select name="lines[${n}][account_id]" class="${selectCls}">
                ${buildAccountOptions(data ? data.account_id : null)}
            </select>
        </td>
        <td class="px-2 py-2 align-middle">
            <input type="text" name="lines[${n}][description]" value="${data ? escHtml(data.description || '') : ''}"
                class="${inputCls}" placeholder="Keterangan detail kegiatan..." maxlength="255">
        </td>
        <td class="px-2 py-2 align-middle">
            <input type="text" id="nominal-disp-${n}" value="${data ? fmtNum(data.nominal || 0) : ''}"
                class="${inputCls} text-right font-mono" placeholder="0"
                oninput="fmtNominal(${n})" onfocus="this.select()">
            <input type="hidden" name="lines[${n}][nominal]" id="nominal-val-${n}" value="${data ? (data.nominal || 0) : 0}">
        </td>
        <td class="px-2 py-2 text-right align-middle">
            <span id="line-total-${n}" class="text-sm font-mono font-semibold text-slate-700">Rp 0</span>
        </td>
        <td class="px-2 py-2 text-center align-middle">
            <button type="button" onclick="removeLine(${n})" title="Hapus"
                class="px-2 py-1 rounded-lg border-0 bg-red-50 text-red-400 hover:bg-red-100 hover:text-red-600 cursor-pointer text-xs font-bold">✕</button>
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
        const cell = tr.querySelector('[id^="line-no-"]');
        if (cell) cell.textContent = i + 1;
    });
}

function fmtNominal(n) {
    const input = document.getElementById(`nominal-disp-${n}`);
    const raw   = input.value.replace(/[^\d]/g, '');
    document.getElementById(`nominal-val-${n}`).value = raw || '0';
    input.value = raw ? parseInt(raw).toLocaleString('id-ID') : '';
    updateTotals();
}

function updateTotals() {
    const freq = currentFreq || 1;
    let grandPerTermin = 0;

    document.querySelectorAll('#lines-body tr').forEach(tr => {
        const n       = tr.id.replace('line-row-', '');
        const nominal = parseInt(document.getElementById(`nominal-val-${n}`)?.value) || 0;
        const lineTotal = nominal * freq;
        const cell = document.getElementById(`line-total-${n}`);
        if (cell) cell.textContent = 'Rp ' + lineTotal.toLocaleString('id-ID');
        grandPerTermin += nominal;
    });

    const grandTotal = grandPerTermin * freq;

    document.getElementById('freq-label').textContent = `(×${freq})`;
    document.getElementById('total-all').textContent  = 'Rp ' + grandTotal.toLocaleString('id-ID');

    if (paguAmount > 0) {
        const sisa  = paguAmount - grandTotal;
        const sisat = document.getElementById('total-sisa');
        sisat.textContent = 'Rp ' + Math.round(sisa).toLocaleString('id-ID');
        sisat.className   = sisa < 0
            ? 'font-mono text-sm font-bold text-red-600'
            : 'font-mono text-sm font-bold text-green-600';

        const status = document.getElementById('pagu-status');
        if (grandTotal > paguAmount) {
            status.className = 'inline-flex items-center gap-2 px-3 py-2 rounded-lg text-xs font-semibold bg-red-50 text-red-600 border border-red-200';
            document.getElementById('pagu-icon').innerHTML = '<circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>';
            document.getElementById('pagu-text').textContent = '⚠ Melebihi pagu!';
        } else {
            status.className = 'inline-flex items-center gap-2 px-3 py-2 rounded-lg text-xs font-semibold bg-green-50 text-green-700 border border-green-200';
            document.getElementById('pagu-icon').innerHTML = '<path d="M22 11.08V12a10 10 0 11-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/>';
            document.getElementById('pagu-text').textContent = 'Pagu: Rp ' + Math.round(paguAmount).toLocaleString('id-ID');
        }
    }
}

function fmtNum(n) { return n ? parseInt(n).toLocaleString('id-ID') : ''; }
function escHtml(s) { return s.replace(/&/g,'&amp;').replace(/"/g,'&quot;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }

const oldLines = @json(old('lines', []));
document.addEventListener('DOMContentLoaded', function() {
    const lineArr = Object.values(oldLines);
    if (lineArr.length > 0) {
        lineArr.forEach(line => addLine(line));
    } else {
        addLine();
    }
});

document.getElementById('bp-form').addEventListener('submit', function(e) {
    if (paguAmount > 0) {
        const freq = currentFreq || 1;
        let grand = 0;
        document.querySelectorAll('#lines-body tr').forEach(tr => {
            const n = tr.id.replace('line-row-', '');
            grand  += (parseInt(document.getElementById(`nominal-val-${n}`)?.value) || 0) * freq;
        });
        if (grand > paguAmount) {
            alert('Total rincian melebihi pagu anggaran! Kurangi nominal sebelum menyimpan.');
            e.preventDefault();
        }
    }
});
</script>

</x-layouts.app>
