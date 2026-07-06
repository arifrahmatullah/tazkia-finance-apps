<x-layouts.app title="Tambah Program Kerja">

<div class="flex items-center justify-between mb-5">
    <div>
        <div class="flex items-center gap-2 mb-1">
            <a href="{{ route('budget-programs.index', ['budget_allocation_id' => $allocation->id]) }}"
               class="text-xs text-slate-400 hover:text-orange-500 transition-colors no-underline">← Program Kerja</a>
        </div>
        <h2 class="text-lg font-bold text-slate-900 m-0">Tambah Program Kerja</h2>
        <p class="text-xs text-slate-400 m-0">
            {{ $allocation->department->name }}
            <span class="mx-1 text-slate-300">·</span>
            {{ $allocation->budgetPeriod->name }}
        </p>
    </div>
</div>

<div class="max-w-xl">
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="px-5 py-3.5 border-b border-slate-100">
            <span class="text-sm font-bold text-slate-900">Informasi Program</span>
        </div>
        <form method="POST" action="{{ route('budget-programs.store') }}" class="px-5 py-5 flex flex-col gap-4">
            @csrf
            <input type="hidden" name="budget_allocation_id" value="{{ $allocation->id }}">

            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1.5">Nama Program Kerja <span class="text-red-500">*</span></label>
                <input type="text" name="name" value="{{ old('name') }}"
                    placeholder="Contoh: Seminar Nasional, Pelatihan SDM, ..."
                    class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl text-sm text-slate-700 bg-white outline-none focus:border-orange-400 transition-colors @error('name') border-red-400 @enderror">
                @error('name') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1.5">Akun COA (Beban)</label>
                <select name="account_id"
                    class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl text-sm text-slate-700 bg-white outline-none focus:border-orange-400 transition-colors">
                    <option value="">— Pilih akun beban —</option>
                    @foreach($accounts as $account)
                        <option value="{{ $account->id }}" {{ old('account_id') == $account->id ? 'selected' : '' }}>
                            {{ $account->code }} — {{ $account->name }}
                        </option>
                    @endforeach
                </select>
                @error('account_id') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                <p class="text-xs text-slate-400 mt-1">Akun beban yang akan didebit saat realisasi pengajuan dana.</p>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1.5">Keterangan</label>
                <textarea name="notes" rows="3"
                    placeholder="Deskripsi singkat program kerja ini..."
                    class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl text-sm text-slate-700 bg-white outline-none focus:border-orange-400 transition-colors resize-none">{{ old('notes') }}</textarea>
                @error('notes') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="flex gap-2.5 pt-1">
                <button type="submit"
                    class="px-5 py-2.5 rounded-xl bg-gradient-to-br from-orange-400 to-orange-500 text-white text-sm font-semibold border-0 cursor-pointer hover:-translate-y-px transition-all">
                    Simpan Program
                </button>
                <a href="{{ route('budget-programs.index', ['budget_allocation_id' => $allocation->id]) }}"
                   class="px-5 py-2.5 rounded-xl border border-slate-200 text-sm text-slate-600 no-underline hover:bg-slate-50 transition-colors">
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>

</x-layouts.app>
