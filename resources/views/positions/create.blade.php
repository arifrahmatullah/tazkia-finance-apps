<x-layouts.app title="Tambah Jabatan" breadcrumb="Master Data / Jabatan / Tambah">

    <div class="max-w-[640px]">

        <a href="{{ route('positions.index') }}" class="inline-flex items-center gap-1.5 text-sm text-slate-500 hover:text-orange-500 mb-5 no-underline">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
            </svg>
            Kembali ke daftar
        </a>

        <div class="bg-white rounded-xl shadow-sm overflow-hidden">

            <div class="flex items-center gap-3 px-6 py-5 bg-gradient-to-br from-[#040f2e] to-[#0d2d6b]">
                <div class="w-9 h-9 rounded-xl bg-orange-500/20 border border-orange-500/30 flex items-center justify-center">
                    <svg width="17" height="17" fill="none" stroke="#fb923c" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                </div>
                <div>
                    <div class="text-white font-semibold text-base">Tambah Jabatan</div>
                    <div class="text-blue-300 text-xs mt-px">Isi data jabatan baru</div>
                </div>
            </div>

            <form method="POST" action="{{ route('positions.store') }}" class="p-6">
                @csrf

                @include('positions._form', ['departments' => $departments])

                <div class="flex gap-3 justify-end mt-6 pt-5 border-t border-slate-100">
                    <a href="{{ route('positions.index') }}" class="px-5 py-2.5 rounded-xl border border-slate-200 bg-white text-slate-600 text-sm font-medium no-underline inline-flex items-center">
                        Batal
                    </a>
                    <button type="submit" class="px-6 py-2.5 rounded-xl text-sm font-semibold bg-gradient-to-br from-orange-400 to-orange-500 text-white border-0 cursor-pointer hover:-translate-y-px transition-all">
                        Simpan Jabatan
                    </button>
                </div>
            </form>
        </div>
    </div>

</x-layouts.app>
