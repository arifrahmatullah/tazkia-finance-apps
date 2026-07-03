<x-layouts.app title="Edit Pagu Anggaran">

<a href="{{ route('budget-allocations.index', ['budget_period_id' => $budgetAllocation->budget_period_id]) }}" class="inline-flex items-center gap-1.5 text-sm text-slate-500 hover:text-orange-500 mb-5 no-underline">
    <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M19 12H5M12 5l-7 7 7 7"/></svg>
    Kembali ke Pagu Anggaran
</a>

<h1 class="text-lg font-bold text-slate-900 m-0">Edit Pagu Anggaran</h1>
<div class="flex gap-2 flex-wrap mb-4 mt-1">
    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-medium bg-slate-100 text-slate-500">📅 {{ $budgetAllocation->budgetPeriod->name }}</span>
    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-medium bg-slate-100 text-slate-500">🏢 {{ $budgetAllocation->department->name }}</span>
</div>

<div class="bg-white rounded-xl shadow-sm p-6">
    <form method="POST" action="{{ route('budget-allocations.update', $budgetAllocation) }}">
        @csrf @method('PUT')

        <p class="text-[11px] font-bold text-slate-400 uppercase tracking-widest mb-3.5 pb-2 border-b border-slate-100">Alokasi Pagu</p>

        @php $sisa = $totalEstimate - $totalAllocated; @endphp
        <div class="grid grid-cols-3 gap-3 mb-5 p-4 bg-slate-50 rounded-xl border border-slate-200">
            <div>
                <div class="text-[11px] font-semibold text-slate-400 uppercase tracking-wide mb-1">Estimasi Pendapatan</div>
                <div class="text-sm font-bold text-slate-700">Rp {{ number_format($totalEstimate, 0, ',', '.') }}</div>
            </div>
            <div>
                <div class="text-[11px] font-semibold text-slate-400 uppercase tracking-wide mb-1">Pagu NETT Lain</div>
                <div class="text-sm font-bold text-slate-700">Rp {{ number_format($totalAllocated, 0, ',', '.') }}</div>
            </div>
            <div>
                <div class="text-[11px] font-semibold text-slate-400 uppercase tracking-wide mb-1">Sisa Tersedia</div>
                <div class="text-sm font-bold {{ $sisa < 0 ? 'text-red-600' : 'text-green-600' }}">
                    Rp {{ number_format($sisa, 0, ',', '.') }}
                </div>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div class="flex flex-col gap-1.5">
                <label class="text-xs font-semibold text-slate-600">Jumlah Pagu (Rp) <span class="text-red-500 ml-0.5">*</span></label>
                <input type="text" id="amountDisplay" class="w-full px-3 py-2.5 border border-slate-200 rounded-xl text-sm text-slate-800 bg-white outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100 transition-colors"
                    value="{{ old('amount') ? number_format((int)old('amount'), 0, ',', '.') : number_format($budgetAllocation->amount, 0, ',', '.') }}"
                    placeholder="0"
                    inputmode="numeric"
                    oninput="formatRupiah(this)">
                <input type="hidden" name="amount" id="amountHidden" value="{{ old('amount', (int)$budgetAllocation->amount) }}">
                @error('amount') <span class="text-xs text-red-500 mt-0.5">{{ $message }}</span> @enderror
            </div>
            <div class="flex flex-col gap-1.5">
                <label class="text-xs font-semibold text-slate-600">Sumber Dana <span class="text-red-500 ml-0.5">*</span></label>
                <select name="source" id="sourceSelect" class="w-full px-3 py-2.5 border border-slate-200 rounded-xl text-sm text-slate-800 bg-white outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100 transition-colors" onchange="onSourceChange(this.value)">
                    <option value="NETT" {{ old('source', $budgetAllocation->source) === 'NETT' ? 'selected' : '' }}>NETT – Tidak ada toleransi</option>
                    <option value="DEVIASI" {{ old('source', $budgetAllocation->source) === 'DEVIASI' ? 'selected' : '' }}>DEVIASI – Boleh melebihi pagu</option>
                </select>
                @error('source') <span class="text-xs text-red-500 mt-0.5">{{ $message }}</span> @enderror
            </div>
            <div class="flex flex-col gap-1.5">
                <label class="text-xs font-semibold text-slate-600">Toleransi Deviasi (%)</label>
                <input type="text" inputmode="decimal" name="percentage" id="percentageInput" class="w-full px-3 py-2.5 border border-slate-200 rounded-xl text-sm text-slate-800 bg-white outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100 transition-colors disabled:bg-slate-100 disabled:text-slate-400 disabled:cursor-not-allowed"
                    value="{{ old('percentage', $budgetAllocation->percentage) }}"
                    placeholder="0.00"
                    oninput="this.value=this.value.replace(/[^0-9.]/g,'').replace(/(\..*?)\..*/g,'$1');if(parseFloat(this.value)>100)this.value='100'">
                <span id="percentageHint" class="text-xs text-slate-400 mt-0.5">Isi % boleh melebihi pagu jika source DEVIASI.</span>
                @error('percentage') <span class="text-xs text-red-500 mt-0.5">{{ $message }}</span> @enderror
            </div>
            <div class="flex flex-col gap-1.5 col-span-2">
                <label class="text-xs font-semibold text-slate-600">Keterangan</label>
                <textarea name="notes" class="w-full px-3 py-2.5 border border-slate-200 rounded-xl text-sm text-slate-800 bg-white outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100 transition-colors" rows="3">{{ old('notes', $budgetAllocation->notes) }}</textarea>
                @error('notes') <span class="text-xs text-red-500 mt-0.5">{{ $message }}</span> @enderror
            </div>
            <div class="flex flex-col gap-1.5">
                <label class="text-xs font-semibold text-slate-600">Blokir Jika Anggaran Habis</label>
                <div class="flex items-center gap-3 p-2.5 border border-slate-200 rounded-xl w-fit">
                    <label class="relative w-[42px] h-[22px] cursor-pointer">
                        <input type="hidden" name="is_blocking" value="0">
                        <input type="checkbox" name="is_blocking" value="1" {{ old('is_blocking', $budgetAllocation->is_blocking) ? 'checked' : '' }} class="sr-only peer">
                        <span class="absolute inset-0 bg-slate-200 rounded-full cursor-pointer transition-colors duration-200 peer-checked:bg-orange-500 before:content-[''] before:absolute before:w-4 before:h-4 before:left-[3px] before:top-[3px] before:bg-white before:rounded-full before:transition-transform before:duration-200 peer-checked:before:translate-x-5"></span>
                    </label>
                    <span class="text-sm text-slate-700">Aktifkan blokir</span>
                </div>
            </div>
            <div class="flex flex-col gap-1.5">
                <label class="text-xs font-semibold text-slate-600">Status</label>
                <div class="flex items-center gap-3 p-2.5 border border-slate-200 rounded-xl w-fit">
                    <label class="relative w-[42px] h-[22px] cursor-pointer">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" name="is_active" value="1" {{ old('is_active', $budgetAllocation->is_active) ? 'checked' : '' }} class="sr-only peer">
                        <span class="absolute inset-0 bg-slate-200 rounded-full cursor-pointer transition-colors duration-200 peer-checked:bg-orange-500 before:content-[''] before:absolute before:w-4 before:h-4 before:left-[3px] before:top-[3px] before:bg-white before:rounded-full before:transition-transform before:duration-200 peer-checked:before:translate-x-5"></span>
                    </label>
                    <span class="text-sm text-slate-700">Pagu Aktif</span>
                </div>
            </div>
        </div>

        <div class="flex gap-3 justify-end mt-6 pt-5 border-t border-slate-100">
            <button type="submit" class="px-6 py-2.5 rounded-xl text-sm font-semibold bg-gradient-to-br from-orange-400 to-orange-500 text-white border-0 cursor-pointer hover:-translate-y-px transition-all">Simpan Perubahan</button>
            <a href="{{ route('budget-allocations.index', ['budget_period_id' => $budgetAllocation->budget_period_id]) }}" class="px-5 py-2.5 rounded-xl border border-slate-200 bg-white text-slate-600 text-sm font-medium no-underline inline-flex items-center">Batal</a>
        </div>
    </form>
</div>

<script>
function formatRupiah(input) {
    const raw = input.value.replace(/\D/g, '');
    document.getElementById('amountHidden').value = raw;
    input.value = raw ? parseInt(raw).toLocaleString('id-ID') : '';
}

function onSourceChange(val) {
    const pctInput = document.getElementById('percentageInput');
    const hint     = document.getElementById('percentageHint');
    if (val === 'NETT') {
        pctInput.value    = '0';
        pctInput.disabled = true;
        hint.textContent  = 'NETT: tidak ada toleransi, departemen tidak boleh melebihi pagu.';
    } else {
        pctInput.disabled = false;
        hint.textContent  = 'Isi % maksimal boleh melebihi pagu (contoh: 10 = boleh melebihi 10%).';
    }
}

document.querySelector('form').addEventListener('submit', function() {
    document.getElementById('percentageInput').disabled = false;
});

// Init on load
onSourceChange(document.getElementById('sourceSelect').value);
const displayInput = document.getElementById('amountDisplay');
if (displayInput.value) formatRupiah(displayInput);
</script>
</x-layouts.app>
