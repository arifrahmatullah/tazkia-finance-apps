<x-layouts.app title="Edit Departemen" breadcrumb="Master Data / Departemen / Edit">

    <div class="max-w-[640px]">

        <a href="{{ route('departments.index') }}" class="inline-flex items-center gap-1.5 text-sm text-slate-500 hover:text-orange-500 mb-5 no-underline">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
            </svg>
            Kembali ke daftar
        </a>

        <div class="bg-white rounded-xl shadow-sm overflow-hidden">

            <div class="flex items-center gap-3 px-6 py-5 border-b border-slate-100 bg-gradient-to-br from-[#040f2e] to-[#0d2d6b]">
                <div class="w-9 h-9 rounded-xl bg-orange-500/20 border border-orange-500/30 flex items-center justify-center">
                    <svg width="17" height="17" fill="none" stroke="#fb923c" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                </div>
                <div>
                    <div class="text-white font-semibold text-base">Edit: {{ $department->name }}</div>
                    <div class="text-blue-300 text-xs mt-px">Perbarui data departemen</div>
                </div>
            </div>

            <form method="POST" action="{{ route('departments.update', $department) }}" class="p-6">
                @csrf @method('PUT')

                @include('departments._form', ['organizations' => $organizations, 'department' => $department])

                {{-- Status aktif --}}
                <div class="mt-4">
                    <label class="flex items-center gap-2.5 cursor-pointer">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" name="is_active" value="1"
                            {{ old('is_active', $department->is_active) ? 'checked' : '' }}
                            class="w-4 h-4 cursor-pointer accent-[#0d2d6b]">
                        <span class="text-sm font-medium text-slate-700">Departemen Aktif</span>
                    </label>
                </div>

                <div class="flex gap-3 justify-end mt-6 pt-5 border-t border-slate-100">
                    <a href="{{ route('departments.index') }}" class="px-5 py-2.5 rounded-xl border border-slate-200 bg-white text-slate-600 text-sm font-medium no-underline inline-flex items-center">
                        Batal
                    </a>
                    <button type="submit" class="px-6 py-2.5 rounded-xl text-sm font-semibold bg-gradient-to-br from-orange-400 to-orange-500 text-white border-0 cursor-pointer hover:-translate-y-px transition-all">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>

</x-layouts.app>
