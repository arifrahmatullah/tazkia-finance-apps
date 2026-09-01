<x-layouts.app title="Edit Jadwal Estimasi">

<a href="{{ route('income-estimates.show', $estimate) }}" class="inline-flex items-center gap-1.5 text-sm text-slate-500 hover:text-orange-500 mb-5 no-underline">
    <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M19 12H5M12 5l-7 7 7 7"/></svg>
    Kembali ke {{ $estimate->description }}
</a>
<h1 class="text-xl font-bold text-slate-900 mb-1">Edit Jadwal Estimasi</h1>
<p class="text-sm text-slate-400 mb-5">{{ $estimate->organization->name }} · {{ $estimate->budgetPeriod->name }}</p>

<div class="bg-white rounded-xl shadow-sm p-6 max-w-xl">

    <div class="flex items-center gap-4 px-4 py-3 bg-orange-50 border border-orange-200 rounded-xl mb-5">
        <div>
            <div class="text-[11px] text-orange-400 font-semibold uppercase tracking-wide">Satuan</div>
            <div class="text-sm font-bold text-orange-700">{{ $estimate->unit }}</div>
        </div>
        <div class="w-px h-8 bg-orange-200 mx-1"></div>
        <div>
            <div class="text-[11px] text-orange-400 font-semibold uppercase tracking-wide">Harga per Satuan</div>
            <div class="text-sm font-bold text-orange-700">Rp {{ number_format($estimate->unit_price, 0, ',', '.') }}</div>
        </div>
    </div>

    <form method="POST" action="{{ route('income-estimate-details.update', $incomeEstimateDetail) }}">
    @csrf @method('PUT')

    <div class="flex flex-col gap-1.5 mb-4">
        <label class="text-xs font-semibold text-slate-600">Tanggal Estimasi <span class="text-red-500">*</span></label>
        <input type="date" name="estimate_date" value="{{ old('estimate_date', $incomeEstimateDetail->estimate_date->format('Y-m-d')) }}"
            class="w-full px-3 py-2.5 border rounded-xl text-sm text-slate-800 bg-white outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100 {{ $errors->has('estimate_date') ? 'border-red-400' : 'border-slate-200' }}" required>
        @error('estimate_date')<p class="text-xs text-red-500">{{ $message }}</p>@enderror
    </div>

    <div class="flex flex-col gap-1.5 mb-4">
        <label class="text-xs font-semibold text-slate-600">Deskripsi <span class="text-red-500">*</span></label>
        <input type="text" name="description" value="{{ old('description', $incomeEstimateDetail->description) }}"
            class="w-full px-3 py-2.5 border rounded-xl text-sm text-slate-800 bg-white outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100 {{ $errors->has('description') ? 'border-red-400' : 'border-slate-200' }}" required>
        @error('description')<p class="text-xs text-red-500">{{ $message }}</p>@enderror
    </div>

    <div class="grid grid-cols-2 gap-4 mb-5">
        <div class="flex flex-col gap-1.5">
            <label class="text-xs font-semibold text-slate-600">Jumlah (Qty) <span class="text-red-500">*</span></label>
            <input type="number" name="qty" id="qty" value="{{ old('qty', $incomeEstimateDetail->qty) }}" min="0.01" step="0.01"
                class="w-full px-3 py-2.5 border rounded-xl text-sm text-slate-800 bg-white outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100 {{ $errors->has('qty') ? 'border-red-400' : 'border-slate-200' }}" required>
            @error('qty')<p class="text-xs text-red-500">{{ $message }}</p>@enderror
        </div>
        <div class="flex flex-col gap-1.5">
            <label class="text-xs font-semibold text-slate-600">Total (Rp)</label>
            <input type="text" id="totalRupiah" inputmode="numeric"
                placeholder="{{ (float) $estimate->unit_price <= 0 ? 'Harga per satuan belum diatur' : '0' }}"
                class="w-full px-3 py-2.5 border border-slate-200 rounded-xl text-sm text-slate-800 bg-white outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100 transition-colors"
                {{ (float) $estimate->unit_price <= 0 ? 'disabled' : '' }}>
            <span class="text-[11px] text-slate-400" id="totalHint">Isi salah satu — Qty atau Total, otomatis mengisi yang lain.</span>
        </div>
    </div>

    <div class="flex gap-3 justify-end pt-5 border-t border-slate-100">
        <a href="{{ route('income-estimates.show', $estimate) }}" class="px-5 py-2.5 rounded-xl border border-slate-200 bg-white text-slate-600 text-sm font-medium no-underline inline-flex items-center">Batal</a>
        <button type="submit" class="px-6 py-2.5 rounded-xl text-sm font-semibold bg-gradient-to-br from-orange-400 to-orange-500 text-white border-0 cursor-pointer hover:-translate-y-px transition-all">Simpan Perubahan</button>
    </div>
    </form>
</div>

<script>
const unitPrice  = {{ $estimate->unit_price }};
const qtyInput   = document.getElementById('qty');
const totalInput = document.getElementById('totalRupiah');

function syncTotalFromQty() {
    const qty   = parseFloat(qtyInput.value) || 0;
    const total = qty * unitPrice;
    totalInput.value = total ? Math.round(total).toLocaleString('id-ID') : '';
}

function syncQtyFromTotal() {
    const raw = totalInput.value.replace(/\D/g, '');
    totalInput.value = raw ? parseInt(raw).toLocaleString('id-ID') : '';
    const hint = document.getElementById('totalHint');
    if (unitPrice > 0) {
        const qty = raw ? (parseInt(raw) / unitPrice) : 0;
        const qtyRounded = qty ? qty.toFixed(2) : '';
        qtyInput.value = qtyRounded;
        if (raw && parseFloat(qtyRounded) < 0.01) {
            hint.textContent = 'Nominal ini terlalu kecil dibanding Harga per Satuan — Qty minimal 0.01. Naikkan nominal atau isi Qty langsung.';
            hint.className = 'text-[11px] text-red-500 font-medium';
        } else {
            hint.textContent = 'Isi salah satu — Qty atau Total, otomatis mengisi yang lain.';
            hint.className = 'text-[11px] text-slate-400';
        }
    }
}

qtyInput.addEventListener('input', syncTotalFromQty);
totalInput.addEventListener('input', syncQtyFromTotal);
syncTotalFromQty();
</script>
</x-layouts.app>
