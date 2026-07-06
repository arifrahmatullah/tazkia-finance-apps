<x-layouts.app title="Edit Rincian">

<div class="flex items-center justify-between mb-5">
    <div>
        <div class="flex items-center gap-2 mb-1">
            <a href="{{ route('budget-programs.show', $budgetProgramDetail->budgetProgram) }}"
               class="text-xs text-slate-400 hover:text-orange-500 transition-colors no-underline">← Rincian Program</a>
        </div>
        <h2 class="text-lg font-bold text-slate-900 m-0">Edit Rincian</h2>
        <p class="text-xs text-slate-400 m-0">{{ $budgetProgramDetail->budgetProgram->name }}</p>
    </div>
</div>

<div class="max-w-xl">
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="px-5 py-3.5 border-b border-slate-100">
            <span class="text-sm font-bold text-slate-900">Detail Rincian</span>
        </div>
        <form method="POST" action="{{ route('budget-program-details.update', $budgetProgramDetail) }}" class="px-5 py-5">
            @csrf @method('PUT')

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">Deskripsi <span class="text-red-500">*</span></label>
                    <input type="text" name="description" value="{{ old('description', $budgetProgramDetail->description) }}"
                        class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl text-sm text-slate-700 bg-white outline-none focus:border-orange-400 transition-colors @error('description') border-red-400 @enderror">
                    @error('description') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">Kuantitas <span class="text-red-500">*</span></label>
                    <input type="text" name="quantity" id="qtyInput"
                        value="{{ old('quantity', number_format((float)$budgetProgramDetail->quantity, 0, ',', '.')) }}"
                        inputmode="decimal"
                        class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl text-sm text-slate-700 bg-white outline-none focus:border-orange-400 transition-colors @error('quantity') border-red-400 @enderror"
                        oninput="calcTotal()">
                    @error('quantity') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">Satuan</label>
                    <input type="text" name="unit" value="{{ old('unit', $budgetProgramDetail->unit) }}"
                        placeholder="Orang, Paket, Hari, ..."
                        class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl text-sm text-slate-700 bg-white outline-none focus:border-orange-400 transition-colors">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">Harga Satuan (Rp) <span class="text-red-500">*</span></label>
                    <input type="text" id="unitPriceDisplay"
                        value="{{ old('unit_price') ? number_format((int)old('unit_price'), 0, ',', '.') : number_format((int)$budgetProgramDetail->unit_price, 0, ',', '.') }}"
                        class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl text-sm text-slate-700 bg-white outline-none focus:border-orange-400 transition-colors font-mono @error('unit_price') border-red-400 @enderror"
                        oninput="formatUnitPrice(this)">
                    <input type="hidden" name="unit_price" id="unitPriceHidden"
                        value="{{ old('unit_price', (int)$budgetProgramDetail->unit_price) }}">
                    @error('unit_price') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">Total</label>
                    <div id="totalPreview"
                        class="w-full px-3.5 py-2.5 border border-slate-100 rounded-xl text-sm font-mono font-bold text-violet-700 bg-violet-50">
                        Rp {{ number_format((int)$budgetProgramDetail->total_amount, 0, ',', '.') }}
                    </div>
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">Catatan</label>
                    <input type="text" name="notes" value="{{ old('notes', $budgetProgramDetail->notes) }}"
                        class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl text-sm text-slate-700 bg-white outline-none focus:border-orange-400 transition-colors">
                </div>
            </div>

            <div class="flex gap-2.5 mt-5">
                <button type="submit"
                    class="px-5 py-2.5 rounded-xl bg-gradient-to-br from-orange-400 to-orange-500 text-white text-sm font-semibold border-0 cursor-pointer hover:-translate-y-px transition-all">
                    Simpan Perubahan
                </button>
                <a href="{{ route('budget-programs.show', $budgetProgramDetail->budgetProgram) }}"
                   class="px-5 py-2.5 rounded-xl border border-slate-200 text-sm text-slate-600 no-underline hover:bg-slate-50 transition-colors">
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>

<script>
function formatUnitPrice(input) {
    let raw = input.value.replace(/[^\d]/g, '');
    document.getElementById('unitPriceHidden').value = raw || '0';
    input.value = raw ? parseInt(raw).toLocaleString('id-ID') : '';
    calcTotal();
}

function calcTotal() {
    const qty   = parseFloat(document.getElementById('qtyInput').value.replace(/[^\d.]/g, '')) || 0;
    const price = parseInt(document.getElementById('unitPriceHidden').value) || 0;
    const total = qty * price;
    document.getElementById('totalPreview').textContent = 'Rp ' + total.toLocaleString('id-ID');
}

window.addEventListener('load', calcTotal);
</script>

</x-layouts.app>
