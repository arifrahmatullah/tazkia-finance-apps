<x-layouts.app title="Edit Realisasi Penerimaan">

<a href="{{ route('income-estimates.show', $estimate) }}" class="inline-flex items-center gap-1.5 text-sm text-slate-500 hover:text-orange-500 mb-5 no-underline">
    <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M19 12H5M12 5l-7 7 7 7"/></svg>
    Kembali ke {{ $estimate->description }}
</a>
<h1 class="text-xl font-bold text-slate-900 mb-1">Edit Realisasi Penerimaan</h1>
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

    <form method="POST" action="{{ route('income-receipts.update', $incomeReceipt) }}" enctype="multipart/form-data">
    @csrf
    @method('PUT')

    <div class="flex flex-col gap-1.5 mb-4">
        <label class="text-xs font-semibold text-slate-600">Tanggal Diterima <span class="text-red-500">*</span></label>
        <input type="date" name="receipt_date" value="{{ old('receipt_date', $incomeReceipt->receipt_date->toDateString()) }}"
            class="w-full px-3 py-2.5 border rounded-xl text-sm text-slate-800 bg-white outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100 {{ $errors->has('receipt_date') ? 'border-red-400' : 'border-slate-200' }}" required>
        @error('receipt_date')<p class="text-xs text-red-500">{{ $message }}</p>@enderror
    </div>

    <div class="flex flex-col gap-1.5 mb-4">
        <label class="text-xs font-semibold text-slate-600">Deskripsi <span class="text-red-500">*</span></label>
        <input type="text" name="description" value="{{ old('description', $incomeReceipt->description) }}"
            class="w-full px-3 py-2.5 border rounded-xl text-sm text-slate-800 bg-white outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100 {{ $errors->has('description') ? 'border-red-400' : 'border-slate-200' }}" required>
        @error('description')<p class="text-xs text-red-500">{{ $message }}</p>@enderror
    </div>

    <div class="flex flex-col gap-1.5 mb-4">
        <label class="text-xs font-semibold text-slate-600">Jumlah (Qty) <span class="text-red-500">*</span></label>
        <input type="number" name="qty" id="qty" value="{{ old('qty', $incomeReceipt->qty) }}" min="0.01" step="0.01"
            class="w-full px-3 py-2.5 border rounded-xl text-sm text-slate-800 bg-white outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100 {{ $errors->has('qty') ? 'border-red-400' : 'border-slate-200' }}" required>
        @error('qty')<p class="text-xs text-red-500">{{ $message }}</p>@enderror
    </div>

    <div class="flex flex-col gap-1.5 mb-2">
        <label class="text-xs font-semibold text-slate-600">Bukti Penerimaan <span class="text-slate-400 font-normal">(opsional)</span></label>
        @if($incomeReceipt->proof_path)
        <div class="flex items-center gap-2 px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-600 mb-1.5">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" class="shrink-0"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><path d="M14 2v6h6"/></svg>
            <a href="{{ $incomeReceipt->proof_url }}" target="_blank" class="text-blue-600 hover:underline truncate">{{ $incomeReceipt->proof_name }}</a>
        </div>
        <p class="text-[11px] text-slate-400 mb-1">Pilih file baru untuk mengganti bukti di atas.</p>
        @endif
        <input type="file" name="proof" accept=".pdf,.jpg,.jpeg,.png"
            class="w-full px-3 py-2.5 border rounded-xl text-sm text-slate-800 bg-white outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100 {{ $errors->has('proof') ? 'border-red-400' : 'border-slate-200' }}">
        <p class="text-[11px] text-slate-400">PDF/JPG/PNG, maks 10 MB.</p>
        @error('proof')<p class="text-xs text-red-500">{{ $message }}</p>@enderror
    </div>

    <div class="px-4 py-3 bg-slate-50 rounded-xl text-sm text-slate-600 mb-5">
        Total: <strong id="preview-total" class="text-orange-600">Rp 0</strong>
        <span class="text-slate-400 text-xs ml-1">({{ number_format($estimate->unit_price, 0, ',', '.') }} × qty)</span>
    </div>

    <div class="flex gap-3 justify-end pt-5 border-t border-slate-100">
        <a href="{{ route('income-estimates.show', $estimate) }}" class="px-5 py-2.5 rounded-xl border border-slate-200 bg-white text-slate-600 text-sm font-medium no-underline inline-flex items-center">Batal</a>
        <button type="submit" class="px-6 py-2.5 rounded-xl text-sm font-semibold bg-gradient-to-br from-orange-400 to-orange-500 text-white border-0 cursor-pointer hover:-translate-y-px transition-all">Simpan Perubahan</button>
    </div>
    </form>
</div>

<script>
const unitPrice = {{ $estimate->unit_price }};
const qtyInput  = document.getElementById('qty');
const preview   = document.getElementById('preview-total');

function updatePreview() {
    const qty   = parseFloat(qtyInput.value) || 0;
    const total = qty * unitPrice;
    preview.textContent = 'Rp ' + total.toLocaleString('id-ID', { minimumFractionDigits: 0 });
}
qtyInput.addEventListener('input', updatePreview);
updatePreview();
</script>
</x-layouts.app>
