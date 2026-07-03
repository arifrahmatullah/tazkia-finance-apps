<x-layouts.app title="Tambah Pagu Anggaran">

<a href="{{ route('budget-allocations.index', ['budget_period_id' => $selectedPeriod?->id]) }}" class="inline-flex items-center gap-1.5 text-sm text-slate-500 hover:text-orange-500 mb-5 no-underline">
    <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M19 12H5M12 5l-7 7 7 7"/></svg>
    Kembali ke Pagu Anggaran
</a>

<h1 class="text-lg font-bold text-slate-900 mb-5">Tambah Pagu Anggaran</h1>

<div class="bg-white rounded-xl shadow-sm p-6">
    <form method="POST" action="{{ route('budget-allocations.store') }}">
        @csrf

        <p class="text-[11px] font-bold text-slate-400 uppercase tracking-widest mb-3.5 pb-2 border-b border-slate-100">Periode &amp; Departemen</p>
        <div class="grid grid-cols-2 gap-4 mb-6">
            <div class="flex flex-col gap-1.5">
                <label class="text-xs font-semibold text-slate-600">Periode Anggaran <span class="text-red-500 ml-0.5">*</span></label>
                @if($periods->count() === 1 || $selectedPeriod)
                    <div class="bg-slate-50 border border-slate-200 rounded-xl p-3 text-sm text-slate-700">
                        <div class="text-[11px] text-slate-400 font-semibold uppercase tracking-wide mb-1">Periode Dipilih</div>
                        {{ $selectedPeriod ? "({$selectedPeriod->code}) {$selectedPeriod->name}" : '-' }}
                    </div>
                    <input type="hidden" name="budget_period_id" value="{{ $selectedPeriod?->id }}" id="periodSelect">
                @else
                    <select name="budget_period_id" id="periodSelect" class="w-full px-3 py-2.5 border border-slate-200 rounded-xl text-sm text-slate-800 bg-white outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100 transition-colors" required
                        onchange="loadDepartments(this.value)">
                        <option value="">-- Pilih Periode --</option>
                        @foreach($periods as $period)
                            <option value="{{ $period->id }}" {{ old('budget_period_id') == $period->id ? 'selected' : '' }}>
                                ({{ $period->code }}) {{ $period->name }}
                            </option>
                        @endforeach
                    </select>
                @endif
                @error('budget_period_id') <span class="text-xs text-red-500 mt-0.5">{{ $message }}</span> @enderror
            </div>

            <div class="flex flex-col gap-1.5">
                <label class="text-xs font-semibold text-slate-600">Departemen <span class="text-red-500 ml-0.5">*</span></label>
                <select name="department_id" id="deptSelect" class="w-full px-3 py-2.5 border border-slate-200 rounded-xl text-sm text-slate-800 bg-white outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100 transition-colors" required>
                    <option value="">-- Pilih Departemen --</option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept->id }}" {{ old('department_id') == $dept->id ? 'selected' : '' }}>
                            ({{ $dept->code }}) {{ $dept->name }}
                        </option>
                    @endforeach
                </select>
                <span class="text-xs text-slate-400 mt-0.5">Hanya departemen yang has_budget = Ya dan belum memiliki pagu di periode ini</span>
                @error('department_id') <span class="text-xs text-red-500 mt-0.5">{{ $message }}</span> @enderror
            </div>
        </div>

        <p class="text-[11px] font-bold text-slate-400 uppercase tracking-widest mb-3.5 pb-2 border-b border-slate-100">Alokasi Pagu</p>
        <div class="grid grid-cols-2 gap-4">
            <div class="flex flex-col gap-1.5">
                <label class="text-xs font-semibold text-slate-600">Jumlah Pagu (Rp) <span class="text-red-500 ml-0.5">*</span></label>
                <input type="text" name="amount" id="amountInput" class="w-full px-3 py-2.5 border border-slate-200 rounded-xl text-sm text-slate-800 bg-white outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100 transition-colors"
                    value="{{ old('amount') }}"
                    placeholder="0"
                    inputmode="numeric"
                    oninput="formatRupiah(this)">
                <input type="hidden" name="amount_raw" id="amountRaw">
                @error('amount') <span class="text-xs text-red-500 mt-0.5">{{ $message }}</span> @enderror
            </div>
            <div class="flex flex-col gap-1.5">
                <label class="text-xs font-semibold text-slate-600">Persentase (%)</label>
                <input type="number" name="percentage" class="w-full px-3 py-2.5 border border-slate-200 rounded-xl text-sm text-slate-800 bg-white outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100 transition-colors"
                    value="{{ old('percentage') }}"
                    placeholder="0.00" step="0.01" min="0" max="100">
                <span class="text-xs text-slate-400 mt-0.5">Opsional. Persentase dari total anggaran organisasi</span>
                @error('percentage') <span class="text-xs text-red-500 mt-0.5">{{ $message }}</span> @enderror
            </div>
            <div class="flex flex-col gap-1.5">
                <label class="text-xs font-semibold text-slate-600">Sumber Dana <span class="text-red-500 ml-0.5">*</span></label>
                <select name="source" class="w-full px-3 py-2.5 border border-slate-200 rounded-xl text-sm text-slate-800 bg-white outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100 transition-colors">
                    <option value="NETT" {{ old('source', 'NETT') === 'NETT' ? 'selected' : '' }}>NETT</option>
                    <option value="DEVIASI" {{ old('source') === 'DEVIASI' ? 'selected' : '' }}>DEVIASI</option>
                </select>
                @error('source') <span class="text-xs text-red-500 mt-0.5">{{ $message }}</span> @enderror
            </div>
            <div class="flex flex-col gap-1.5 col-span-2">
                <label class="text-xs font-semibold text-slate-600">Keterangan</label>
                <textarea name="notes" class="w-full px-3 py-2.5 border border-slate-200 rounded-xl text-sm text-slate-800 bg-white outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100 transition-colors" rows="3" placeholder="Opsional">{{ old('notes') }}</textarea>
                @error('notes') <span class="text-xs text-red-500 mt-0.5">{{ $message }}</span> @enderror
            </div>
            <div class="flex flex-col gap-1.5">
                <label class="text-xs font-semibold text-slate-600">Blokir Jika Anggaran Habis</label>
                <div class="flex items-center gap-3 p-2.5 border border-slate-200 rounded-xl w-fit">
                    <label class="relative w-[42px] h-[22px] cursor-pointer">
                        <input type="hidden" name="is_blocking" value="0">
                        <input type="checkbox" name="is_blocking" value="1" {{ old('is_blocking') ? 'checked' : '' }} class="sr-only peer">
                        <span class="absolute inset-0 bg-slate-200 rounded-full cursor-pointer transition-colors duration-200 peer-checked:bg-orange-500 before:content-[''] before:absolute before:w-4 before:h-4 before:left-[3px] before:top-[3px] before:bg-white before:rounded-full before:transition-transform before:duration-200 peer-checked:before:translate-x-5"></span>
                    </label>
                    <span class="text-sm text-slate-700">Aktifkan blokir</span>
                </div>
            </div>
        </div>

        <div class="flex gap-3 justify-end mt-6 pt-5 border-t border-slate-100">
            <button type="submit" class="px-6 py-2.5 rounded-xl text-sm font-semibold bg-gradient-to-br from-orange-400 to-orange-500 text-white border-0 cursor-pointer hover:-translate-y-px transition-all">Simpan Pagu</button>
            <a href="{{ route('budget-allocations.index', ['budget_period_id' => $selectedPeriod?->id]) }}" class="px-5 py-2.5 rounded-xl border border-slate-200 bg-white text-slate-600 text-sm font-medium no-underline inline-flex items-center">Batal</a>
        </div>
    </form>
</div>

<script>
function formatRupiah(input) {
    let raw = input.value.replace(/\D/g, '');
    document.getElementById('amountRaw').value = raw;
    input.value = raw ? parseInt(raw).toLocaleString('id-ID') : '';
}

function loadDepartments(periodId) {
    if (!periodId) return;
    fetch(`{{ route('budget-allocations.departments') }}?budget_period_id=${periodId}`)
        .then(r => r.json())
        .then(depts => {
            const sel = document.getElementById('deptSelect');
            sel.innerHTML = '<option value="">-- Pilih Departemen --</option>';
            depts.forEach(d => {
                sel.innerHTML += `<option value="${d.id}">(${d.code}) ${d.name}</option>`;
            });
        });
}

// Fix amount before submit
document.querySelector('form').addEventListener('submit', function() {
    const raw = document.getElementById('amountRaw').value || document.getElementById('amountInput').value.replace(/\D/g, '');
    document.getElementById('amountInput').value = raw;
});

// Format existing value on load
const amountInput = document.getElementById('amountInput');
if (amountInput.value) formatRupiah(amountInput);
</script>
</x-layouts.app>
