<x-layouts.app title="Edit Program Kerja">

<div class="flex items-center justify-between mb-5">
    <div>
        <div class="flex items-center gap-2 mb-1">
            <a href="{{ route('budget-programs.show', $budgetProgram) }}"
               class="text-xs text-slate-400 hover:text-orange-500 transition-colors no-underline">← Rincian Program</a>
        </div>
        <h2 class="text-lg font-bold text-slate-900 m-0">Edit Program Kerja</h2>
        <p class="text-xs text-slate-400 m-0">
            {{ $budgetProgram->budgetAllocation->department->name }}
            <span class="mx-1 text-slate-300">·</span>
            {{ $budgetProgram->budgetAllocation->budgetPeriod->name }}
        </p>
    </div>
</div>

<div class="max-w-xl">
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="px-5 py-3.5 border-b border-slate-100">
            <span class="text-sm font-bold text-slate-900">Informasi Program</span>
        </div>
        <form method="POST" action="{{ route('budget-programs.update', $budgetProgram) }}" class="px-5 py-5 flex flex-col gap-4">
            @csrf @method('PUT')

            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1.5">Nama Program Kerja <span class="text-red-500">*</span></label>
                <input type="text" name="name" value="{{ old('name', $budgetProgram->name) }}"
                    class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl text-sm text-slate-700 bg-white outline-none focus:border-orange-400 transition-colors @error('name') border-red-400 @enderror">
                @error('name') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1.5">
                    Frekuensi <span class="text-red-500">*</span>
                    <span class="font-normal text-slate-400 ml-1">berapa kali bayar dalam periode</span>
                </label>
                <div class="flex items-center gap-2 flex-wrap">
                    <input type="number" name="frequency" id="freq-input"
                        value="{{ old('frequency', $budgetProgram->frequency) }}"
                        min="1" max="366"
                        class="w-24 px-3.5 py-2.5 border border-slate-200 rounded-xl text-sm text-slate-700 bg-white outline-none focus:border-orange-400 transition-colors font-mono @error('frequency') border-red-400 @enderror"
                        oninput="syncFreq(this.value)">
                    <div class="flex gap-1.5 flex-wrap">
                        @foreach([1,3,4,6,12] as $f)
                        <button type="button" data-val="{{ $f }}"
                            class="freq-btn px-3 py-1.5 rounded-lg text-xs font-semibold border transition-colors cursor-pointer
                                {{ old('frequency', $budgetProgram->frequency) == $f ? 'bg-orange-500 text-white border-orange-500' : 'bg-white text-slate-600 border-slate-200 hover:border-orange-300 hover:text-orange-600' }}"
                            onclick="setFreq({{ $f }})">{{ $f }}×</button>
                        @endforeach
                    </div>
                </div>
                @error('frequency') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                <p class="text-[11px] text-slate-400 mt-1.5">⚠ Mengubah frekuensi akan mengupdate jumlah semua rincian dan jadwal estimasi.</p>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1.5">Keterangan</label>
                <textarea name="notes" rows="3"
                    class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl text-sm text-slate-700 bg-white outline-none focus:border-orange-400 transition-colors resize-none">{{ old('notes', $budgetProgram->notes) }}</textarea>
                @error('notes') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="flex items-center gap-2.5 cursor-pointer">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $budgetProgram->is_active) ? 'checked' : '' }}
                        class="w-4 h-4 rounded accent-orange-500">
                    <span class="text-sm text-slate-700">Program kerja aktif</span>
                </label>
            </div>

            <div class="flex gap-2.5 pt-1">
                <button type="submit"
                    class="px-5 py-2.5 rounded-xl bg-gradient-to-br from-orange-400 to-orange-500 text-white text-sm font-semibold border-0 cursor-pointer hover:-translate-y-px transition-all">
                    Simpan Perubahan
                </button>
                <a href="{{ route('budget-programs.show', $budgetProgram) }}"
                   class="px-5 py-2.5 rounded-xl border border-slate-200 text-sm text-slate-600 no-underline hover:bg-slate-50 transition-colors">
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>

<script>
function setFreq(val) {
    document.getElementById('freq-input').value = val;
    document.querySelectorAll('.freq-btn').forEach(btn => {
        const active = parseInt(btn.dataset.val) === val;
        btn.className = btn.className
            .replace(/bg-orange-500 text-white border-orange-500|bg-white text-slate-600 border-slate-200 hover:border-orange-300 hover:text-orange-600/g, '')
            .trim();
        btn.classList.add(...(active
            ? ['bg-orange-500','text-white','border-orange-500']
            : ['bg-white','text-slate-600','border-slate-200','hover:border-orange-300','hover:text-orange-600']));
    });
}
function syncFreq(val) {
    document.querySelectorAll('.freq-btn').forEach(btn => {
        const active = parseInt(btn.dataset.val) === parseInt(val);
        btn.className = btn.className
            .replace(/bg-orange-500 text-white border-orange-500|bg-white text-slate-600 border-slate-200 hover:border-orange-300 hover:text-orange-600/g, '')
            .trim();
        btn.classList.add(...(active
            ? ['bg-orange-500','text-white','border-orange-500']
            : ['bg-white','text-slate-600','border-slate-200','hover:border-orange-300','hover:text-orange-600']));
    });
}
</script>

</x-layouts.app>
