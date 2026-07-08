<x-layouts.app title="Edit Rincian">

<div class="flex items-center justify-between mb-5">
    <div>
        <a href="{{ route('budget-programs.show', $budgetProgramDetail->budgetProgram) }}"
           class="inline-flex items-center gap-1.5 text-xs text-slate-400 hover:text-orange-500 no-underline transition-colors mb-1">
            <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M19 12H5M12 5l-7 7 7 7"/></svg>
            {{ $budgetProgramDetail->budgetProgram->name }}
        </a>
        <h2 class="text-lg font-bold text-slate-900 m-0">Edit Rincian</h2>
    </div>
</div>

<div class="max-w-xl">
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="px-5 py-3.5 border-b border-slate-100">
            <span class="text-sm font-bold text-slate-900">Detail Rincian</span>
        </div>
        <form method="POST" action="{{ route('budget-program-details.update', $budgetProgramDetail) }}" class="px-5 py-5 flex flex-col gap-4">
            @csrf @method('PUT')

            @if($errors->any())
            <div class="px-4 py-3 bg-red-50 border border-red-200 rounded-xl text-sm text-red-700">{{ $errors->first() }}</div>
            @endif

            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1.5">Deskripsi <span class="text-red-500">*</span></label>
                <input type="text" name="description" value="{{ old('description', $budgetProgramDetail->description) }}"
                    class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl text-sm text-slate-700 bg-white outline-none focus:border-orange-400 transition-colors @error('description') border-red-400 @enderror">
                @error('description') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1.5">Akun COA</label>
                <select name="account_id"
                    class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl text-sm text-slate-700 bg-white outline-none focus:border-orange-400 transition-colors">
                    <option value="">— Pilih akun (opsional) —</option>
                    @foreach($accounts as $account)
                        <option value="{{ $account->id }}"
                            {{ old('account_id', $budgetProgramDetail->account_id) == $account->id ? 'selected' : '' }}>
                            {{ $account->code }} — {{ $account->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1.5">Nominal (Rp) <span class="text-red-500">*</span></label>
                <input type="text" id="nominalDisplay"
                    value="{{ old('unit_price') ? number_format((int)old('unit_price'), 0, ',', '.') : number_format((int)$budgetProgramDetail->unit_price, 0, ',', '.') }}"
                    class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl text-sm text-slate-700 bg-white outline-none focus:border-orange-400 transition-colors font-mono @error('unit_price') border-red-400 @enderror"
                    oninput="fmtNominal(this)" onfocus="this.select()">
                <input type="hidden" name="unit_price" id="nominalHidden"
                    value="{{ old('unit_price', (int)$budgetProgramDetail->unit_price) }}">
                @error('unit_price') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="flex gap-2.5 pt-1">
                <button type="submit"
                    class="px-5 py-2.5 rounded-xl bg-gradient-to-br from-orange-400 to-orange-500 text-white text-sm font-semibold border-0 cursor-pointer hover:-translate-y-px transition-all">
                    Simpan
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
function fmtNominal(input) {
    const raw = input.value.replace(/[^\d]/g, '');
    document.getElementById('nominalHidden').value = raw || '0';
    input.value = raw ? parseInt(raw).toLocaleString('id-ID') : '';
}
</script>

</x-layouts.app>
