<x-layouts.app title="Edit Bank" breadcrumb="Master Data / Bank / Edit">

    <div class="max-w-[680px]">

        <a href="{{ route('banks.index') }}" class="inline-flex items-center gap-1.5 text-sm text-slate-500 hover:text-orange-500 mb-5 no-underline">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
            </svg>
            Kembali ke daftar
        </a>

        <div class="bg-white rounded-xl shadow-sm overflow-hidden">

            <div class="px-6 py-5 bg-gradient-to-br from-[#040f2e] to-[#0d2d6b] flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-orange-500/20 border border-orange-500/30 flex items-center justify-center shrink-0">
                    <svg width="17" height="17" fill="none" stroke="#fb923c" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h16a1 1 0 001-1V6a1 1 0 00-1-1H4a1 1 0 00-1 1v12a1 1 0 001 1z"/>
                    </svg>
                </div>
                <div>
                    <div class="text-white font-semibold text-[0.95rem]">Edit Bank</div>
                    <div class="text-blue-300 text-[11px] mt-px">{{ $bank->name }}</div>
                </div>
            </div>

            <form method="POST" action="{{ route('banks.update', $bank) }}" class="p-6">
                @csrf @method('PUT')

                @include('banks._form', ['bank' => $bank])

                <div class="flex gap-3 justify-end mt-6 pt-5 border-t border-slate-100">
                    <button type="submit" class="px-6 py-2.5 rounded-xl text-sm font-semibold bg-gradient-to-br from-orange-400 to-orange-500 text-white border-0 cursor-pointer hover:-translate-y-px transition-all">
                        Simpan Perubahan
                    </button>
                    <a href="{{ route('banks.index') }}" class="px-5 py-2.5 rounded-xl border border-slate-200 bg-white text-slate-600 text-sm font-medium no-underline inline-flex items-center">
                        Batal
                    </a>
                </div>
            </form>
        </div>
    </div>

</x-layouts.app>
