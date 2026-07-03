<x-layouts.app title="Tambah Departemen" breadcrumb="Master Data / Departemen / Tambah">

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
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>
                <div>
                    <div class="text-white font-semibold text-base">Tambah Departemen</div>
                    <div class="text-blue-300 text-xs mt-px">Isi data departemen baru</div>
                </div>
            </div>

            <form method="POST" action="{{ route('departments.store') }}" class="p-6">
                @csrf

                @include('departments._form', ['organizations' => $organizations])

                <div class="flex gap-3 justify-end mt-6 pt-5 border-t border-slate-100">
                    <a href="{{ route('departments.index') }}" class="px-5 py-2.5 rounded-xl border border-slate-200 bg-white text-slate-600 text-sm font-medium no-underline inline-flex items-center">
                        Batal
                    </a>
                    <button type="submit" class="px-6 py-2.5 rounded-xl text-sm font-semibold bg-gradient-to-br from-orange-400 to-orange-500 text-white border-0 cursor-pointer hover:-translate-y-px transition-all">
                        Simpan Departemen
                    </button>
                </div>
            </form>
        </div>
    </div>

</x-layouts.app>
