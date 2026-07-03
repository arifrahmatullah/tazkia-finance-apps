<x-layouts.app title="Edit Jabatan" breadcrumb="Master Data / Jabatan / Edit">

    <div class="max-w-[640px]">

        <a href="{{ route('positions.index') }}" class="inline-flex items-center gap-1.5 text-sm text-slate-500 hover:text-orange-500 mb-5 no-underline">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
            </svg>
            Kembali ke daftar
        </a>

        <div class="bg-white rounded-xl border border-slate-100 shadow-sm overflow-hidden">

            <div class="flex items-center gap-3 px-6 py-5" style="background:linear-gradient(135deg,#040f2e,#0d2d6b)">
                <div class="w-9 h-9 rounded-[9px] bg-orange-500/20 border border-orange-400/30 flex items-center justify-center shrink-0">
                    <svg width="17" height="17" fill="none" stroke="#fb923c" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                </div>
                <div>
                    <div class="text-white font-semibold text-[0.95rem]">Edit: {{ $position->name }}</div>
                    <div class="text-blue-300 text-[0.72rem] mt-px">Perbarui data jabatan</div>
                </div>
            </div>

            <form method="POST" action="{{ route('positions.update', $position) }}" class="p-6">
                @csrf @method('PUT')

                @include('positions._form', ['departments' => $departments, 'position' => $position])

                {{-- Status --}}
                <div class="mt-3">
                    <label class="flex items-center gap-2.5 cursor-pointer">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" name="is_active" value="1"
                            {{ old('is_active', $position->is_active) ? 'checked' : '' }}
                            class="w-4 h-4 accent-[#0d2d6b] cursor-pointer">
                        <span class="text-sm font-medium text-slate-700">Jabatan Aktif</span>
                    </label>
                </div>

                <div class="flex gap-2.5 mt-6 pt-5 border-t border-slate-100">
                    <button type="submit" class="px-5 py-2.5 rounded-xl border-0 cursor-pointer bg-gradient-to-br from-orange-400 to-orange-500 text-white text-sm font-semibold hover:-translate-y-px transition-all">
                        Simpan Perubahan
                    </button>
                    <a href="{{ route('positions.index') }}" class="px-5 py-2.5 rounded-xl text-sm font-medium text-slate-600 bg-slate-50 border border-slate-200 no-underline">
                        Batal
                    </a>
                </div>
            </form>
        </div>
    </div>

</x-layouts.app>
