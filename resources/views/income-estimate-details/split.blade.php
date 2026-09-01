<x-layouts.app title="Bagi Jadwal Estimasi">

<a href="{{ route('income-estimates.show', $estimate) }}" class="inline-flex items-center gap-1.5 text-sm text-slate-500 hover:text-orange-500 mb-5 no-underline">
    <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M19 12H5M12 5l-7 7 7 7"/></svg>
    Kembali ke {{ $estimate->description }}
</a>
<h1 class="text-xl font-bold text-slate-900 mb-1">Bagi Jadwal Otomatis</h1>
<p class="text-sm text-slate-400 mb-5">{{ $estimate->organization->name }} · {{ $estimate->budgetPeriod->name }}</p>

<div class="bg-white rounded-xl shadow-sm p-6 max-w-3xl">

    {{-- Info Total --}}
    <div class="grid grid-cols-3 gap-3 mb-5">
        <div class="px-4 py-3 bg-slate-50 rounded-xl">
            <div class="text-[11px] text-slate-400 font-semibold uppercase tracking-wide">Total Estimasi</div>
            <div class="text-sm font-bold text-slate-700">Rp {{ number_format($estimate->unit_price, 0, ',', '.') }}</div>
        </div>
        <div class="px-4 py-3 bg-slate-50 rounded-xl">
            <div class="text-[11px] text-slate-400 font-semibold uppercase tracking-wide">Sudah Terjadwal</div>
            <div class="text-sm font-bold text-slate-700">Rp {{ number_format($scheduledTotal, 0, ',', '.') }}</div>
        </div>
        <div class="px-4 py-3 bg-orange-50 rounded-xl">
            <div class="text-[11px] text-orange-400 font-semibold uppercase tracking-wide">Sisa Belum Terjadwal</div>
            <div class="text-sm font-bold text-orange-700">Rp {{ number_format(max(0, $estimate->unit_price - $scheduledTotal), 0, ',', '.') }}</div>
        </div>
    </div>

    @if($errors->any())
    <div class="px-4 py-3 bg-red-50 border border-red-200 rounded-xl mb-5 text-sm text-red-600">
        @foreach($errors->all() as $e) <div>{{ $e }}</div> @endforeach
    </div>
    @endif

    <form method="POST" action="{{ route('income-estimate-details.split.store', $estimate) }}" id="splitForm">
    @csrf

    <div class="grid grid-cols-2 gap-4 mb-4">
        <div class="flex flex-col gap-1.5">
            <label class="text-xs font-semibold text-slate-600">Total yang Dibagi (Rp) <span class="text-red-500">*</span></label>
            <input type="number" step="0.01" min="0.01" name="total_amount" id="total_amount"
                value="{{ old('total_amount', $defaultTotal) }}"
                class="w-full px-3 py-2.5 border border-slate-200 rounded-xl text-sm text-slate-800 bg-white outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100" required>
        </div>
        <div class="flex flex-col gap-1.5">
            <label class="text-xs font-semibold text-slate-600">Mulai Bulan <span class="text-red-500">*</span></label>
            <input type="month" name="start_month" id="start_month" value="{{ old('start_month', $startMonth) }}"
                class="w-full px-3 py-2.5 border border-slate-200 rounded-xl text-sm text-slate-800 bg-white outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100" required>
        </div>
        <div class="flex flex-col gap-1.5">
            <label class="text-xs font-semibold text-slate-600">Jumlah Bulan <span class="text-red-500">*</span></label>
            <input type="number" min="1" max="36" name="month_count" id="month_count" value="{{ old('month_count', 12) }}"
                class="w-full px-3 py-2.5 border border-slate-200 rounded-xl text-sm text-slate-800 bg-white outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100" required>
        </div>
        <div class="flex flex-col gap-1.5">
            <label class="text-xs font-semibold text-slate-600">Prefix Deskripsi</label>
            <input type="text" name="description_prefix" value="{{ old('description_prefix', 'OPERASIONAL') }}"
                class="w-full px-3 py-2.5 border border-slate-200 rounded-xl text-sm text-slate-800 bg-white outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100">
        </div>
    </div>

    <div class="flex flex-col gap-1.5 mb-4">
        <label class="text-xs font-semibold text-slate-600">Cara Bagi <span class="text-red-500">*</span></label>
        <div class="flex gap-4">
            <label class="inline-flex items-center gap-2 text-sm text-slate-700 select-none">
                <input type="radio" name="mode" value="even" id="mode_even" checked> Bagi Rata Otomatis
            </label>
            <label class="inline-flex items-center gap-2 text-sm text-slate-700 select-none">
                <input type="radio" name="mode" value="custom" id="mode_custom"> Custom per Bulan
            </label>
        </div>
    </div>

    <label class="inline-flex items-center gap-2 text-xs font-semibold text-slate-600 select-none mb-5">
        <input type="checkbox" name="replace_existing" value="1" {{ old('replace_existing') ? 'checked' : '' }}>
        Hapus jadwal yang sudah ada sebelum membuat jadwal baru
    </label>

    {{-- Custom breakdown --}}
    <div id="customArea" class="hidden mb-5">
        <div class="flex items-center justify-between mb-2">
            <span class="text-xs font-bold text-slate-500 uppercase tracking-wide">Nominal per Bulan</span>
            <span class="text-xs" id="customSumInfo"></span>
        </div>
        <div id="customRows" class="flex flex-col gap-2 max-h-80 overflow-y-auto pr-1"></div>
    </div>

    <div class="flex gap-3 justify-end pt-5 border-t border-slate-100">
        <a href="{{ route('income-estimates.show', $estimate) }}" class="px-5 py-2.5 rounded-xl border border-slate-200 bg-white text-slate-600 text-sm font-medium no-underline inline-flex items-center">Batal</a>
        <button type="submit" class="px-6 py-2.5 rounded-xl text-sm font-semibold bg-gradient-to-br from-orange-400 to-orange-500 text-white border-0 cursor-pointer hover:-translate-y-px transition-all">Buat Jadwal</button>
    </div>
    </form>
</div>

<script>
const monthNames = ['JANUARI','FEBRUARI','MARET','APRIL','MEI','JUNI','JULI','AGUSTUS','SEPTEMBER','OKTOBER','NOVEMBER','DESEMBER'];

function addMonths(ym, n) {
    let [y, m] = ym.split('-').map(Number);
    m += n;
    y += Math.floor((m - 1) / 12);
    m = ((m - 1) % 12 + 12) % 12 + 1;
    return { y, m };
}
function lastDayOfMonth(y, m) {
    return new Date(y, m, 0).getDate();
}
function monthLabel(y, m) {
    return '01-' + lastDayOfMonth(y, m) + ' ' + monthNames[m - 1] + ' ' + y;
}
function fmtRupiah(n) {
    return 'Rp ' + Math.round(n).toLocaleString('id-ID');
}

const modeEven   = document.getElementById('mode_even');
const modeCustom = document.getElementById('mode_custom');
const customArea = document.getElementById('customArea');
const customRows = document.getElementById('customRows');
const customSumInfo = document.getElementById('customSumInfo');
const totalInput = document.getElementById('total_amount');
const startInput = document.getElementById('start_month');
const countInput = document.getElementById('month_count');

function renderCustomRows() {
    const total = parseFloat(totalInput.value) || 0;
    const start = startInput.value;
    const count = Math.max(1, Math.min(36, parseInt(countInput.value) || 1));
    if (!start) { customRows.innerHTML = '<p class="text-xs text-slate-400 italic">Isi "Mulai Bulan" dulu.</p>'; return; }

    const base = Math.round((total / count) * 100) / 100;
    let acc = 0;
    let html = '';
    for (let i = 0; i < count; i++) {
        const { y, m } = addMonths(start, i);
        const amount = (i === count - 1) ? Math.round((total - acc) * 100) / 100 : base;
        acc += amount;
        html += `<div class="flex items-center gap-3">
            <span class="text-xs text-slate-500 w-48 shrink-0">${monthLabel(y, m)}</span>
            <input type="number" step="0.01" min="0" name="amounts[]" value="${amount}"
                class="custom-amount flex-1 px-3 py-2 border border-slate-200 rounded-lg text-sm text-slate-800 bg-white outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100">
        </div>`;
    }
    customRows.innerHTML = html;
    customRows.querySelectorAll('.custom-amount').forEach(el => el.addEventListener('input', updateCustomSum));
    updateCustomSum();
}

function updateCustomSum() {
    const total = parseFloat(totalInput.value) || 0;
    let sum = 0;
    customRows.querySelectorAll('.custom-amount').forEach(el => sum += (parseFloat(el.value) || 0));
    const diff = Math.round((total - sum) * 100) / 100;
    customSumInfo.textContent = 'Total input: ' + fmtRupiah(sum) + (Math.abs(diff) > 0.01 ? ' · Sisa: ' + fmtRupiah(diff) : ' · Cocok ✓');
    customSumInfo.className = Math.abs(diff) > 0.01 ? 'text-xs text-red-500 font-medium' : 'text-xs text-emerald-600 font-medium';
}

function toggleMode() {
    const isCustom = modeCustom.checked;
    customArea.classList.toggle('hidden', !isCustom);
    if (isCustom) renderCustomRows();
}

modeEven.addEventListener('change', toggleMode);
modeCustom.addEventListener('change', toggleMode);
countInput.addEventListener('input', () => { if (modeCustom.checked) renderCustomRows(); });
startInput.addEventListener('input', () => { if (modeCustom.checked) renderCustomRows(); });
totalInput.addEventListener('input', () => { if (modeCustom.checked) updateCustomSum(); });

toggleMode();
</script>
</x-layouts.app>
