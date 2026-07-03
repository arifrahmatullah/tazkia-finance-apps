<x-layouts.app title="Tambah Periode Anggaran" breadcrumb="Keuangan / Periode Anggaran / Tambah">

    <div class="max-w-[680px]">

        <a href="{{ route('budget-periods.index') }}" class="inline-flex items-center gap-1.5 text-sm text-slate-500 hover:text-orange-500 mb-5 no-underline">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
            </svg>
            Kembali ke daftar
        </a>

        <div class="bg-white rounded-xl shadow-sm overflow-hidden">

            <div class="px-6 py-5 bg-gradient-to-br from-[#040f2e] to-[#0d2d6b] flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-orange-500/20 border border-orange-500/30 flex items-center justify-center shrink-0">
                    <svg width="17" height="17" fill="none" stroke="#fb923c" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
                <div>
                    <div class="text-white font-semibold text-[0.95rem]">Tambah Periode Anggaran</div>
                    <div class="text-blue-300 text-[11px] mt-px">Isi data periode anggaran baru</div>
                </div>
            </div>

            <form method="POST" action="{{ route('budget-periods.store') }}" class="p-6">
                @csrf

                @include('budget-periods._form', ['organizations' => $organizations])

                <div class="flex gap-3 justify-end mt-6 pt-5 border-t border-slate-100">
                    <button type="submit" class="px-6 py-2.5 rounded-xl text-sm font-semibold bg-gradient-to-br from-orange-400 to-orange-500 text-white border-0 cursor-pointer hover:-translate-y-px transition-all">
                        Simpan Periode
                    </button>
                    <a href="{{ route('budget-periods.index') }}" class="px-5 py-2.5 rounded-xl border border-slate-200 bg-white text-slate-600 text-sm font-medium no-underline inline-flex items-center">
                        Batal
                    </a>
                </div>
            </form>
        </div>
    </div>

</x-layouts.app>
