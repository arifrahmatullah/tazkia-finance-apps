<x-layouts.app title="Master Bank" breadcrumb="Master Data / Bank">

    <div class="flex items-center justify-between mb-5">
        <div>
            <h2 class="text-lg font-bold text-slate-900 m-0">Master Bank</h2>
            <p class="text-xs text-slate-400 m-0">Kelola daftar bank yang bisa dipilih saat pengajuan dana</p>
        </div>
        <a href="{{ route('banks.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-gradient-to-br from-orange-400 to-orange-500 text-white text-sm font-semibold shadow-sm hover:-translate-y-px transition-all no-underline">
            <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
            </svg>
            Tambah Bank
        </a>
    </div>

    @if(session('success'))
    <div class="flex items-center gap-2.5 px-4 py-3 bg-green-50 border border-green-200 rounded-xl mb-4 text-sm text-green-700">
        <svg width="16" height="16" fill="#16a34a" viewBox="0 0 20 20" class="shrink-0">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
        </svg>
        <span>{{ session('success') }}</span>
    </div>
    @endif

    <form method="GET" action="{{ route('banks.index') }}" class="bg-white rounded-xl shadow-sm p-4 mb-4 flex gap-3 items-end">
        <div class="flex-1 min-w-[200px]">
            <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block mb-1.5">Cari</label>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Nama bank..."
                class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm outline-none focus:border-orange-400 transition-colors">
        </div>
        <button type="submit" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-sm font-semibold bg-orange-500 text-white border-0 cursor-pointer hover:bg-orange-600 transition-colors">
            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
            Cari
        </button>
        @if(request('search'))
        <a href="{{ route('banks.index') }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-sm font-medium bg-slate-100 text-slate-600 no-underline hover:bg-slate-200 transition-colors">Reset</a>
        @endif
    </form>

    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <table class="w-full border-collapse">
            <thead>
                <tr class="bg-slate-50 border-b border-slate-100">
                    <th class="px-4 py-3 text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wide">#</th>
                    <th class="px-4 py-3 text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wide">Nama Bank</th>
                    <th class="px-4 py-3 text-center text-[11px] font-semibold text-slate-400 uppercase tracking-wide">Status</th>
                    <th class="px-4 py-3 text-center text-[11px] font-semibold text-slate-400 uppercase tracking-wide">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($banks as $i => $bank)
                <tr class="border-b border-slate-50 hover:bg-slate-50 transition-colors">
                    <td class="px-4 py-3 text-sm text-slate-400 align-middle">{{ $banks->firstItem() + $i }}</td>
                    <td class="px-4 py-3 text-sm font-semibold text-slate-800 align-middle">{{ $bank->name }}</td>
                    <td class="px-4 py-3 text-sm align-middle text-center">
                        @if($bank->is_active)
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-green-100 text-green-700">
                                <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span>
                                Aktif
                            </span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-slate-100 text-slate-500">Nonaktif</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-sm text-slate-600 align-middle text-center">
                        <div class="inline-flex items-center gap-1.5">
                            <a href="{{ route('banks.edit', $bank) }}"
                               class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-xs font-medium bg-blue-50 text-blue-600 hover:bg-blue-100 transition-colors no-underline">
                                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                                Edit
                            </a>
                            <form id="del-bank-{{ $bank->id }}" method="POST" action="{{ route('banks.destroy', $bank) }}">
                                @csrf @method('DELETE')
                            </form>
                            <button type="button"
                                onclick="confirmDelete('del-bank-{{ $bank->id }}', '{{ addslashes($bank->name) }}')"
                                class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-xs font-medium bg-red-50 text-red-600 hover:bg-red-100 transition-colors border-0 cursor-pointer">
                                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                                Hapus
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="px-4 py-12 text-center text-slate-400">
                        <p class="text-sm m-0">Belum ada bank. Tambahkan bank pertama.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($banks->hasPages())
    <div class="mt-4 flex justify-end gap-1">
        @if($banks->onFirstPage())
            <span class="inline-flex items-center px-2.5 py-1.5 border border-slate-200 rounded-lg text-xs text-slate-300 pointer-events-none">&laquo;</span>
        @else
            <a href="{{ $banks->previousPageUrl() }}" class="inline-flex items-center px-2.5 py-1.5 border border-slate-200 rounded-lg text-xs text-slate-500 no-underline bg-white">&laquo;</a>
        @endif
        @foreach($banks->getUrlRange(max(1,$banks->currentPage()-2), min($banks->lastPage(),$banks->currentPage()+2)) as $page => $url)
            <a href="{{ $url }}" class="inline-flex items-center px-2.5 py-1.5 border rounded-lg text-xs no-underline {{ $page == $banks->currentPage() ? 'bg-orange-500 border-orange-500 text-white' : 'bg-white border-slate-200 text-slate-500' }}">{{ $page }}</a>
        @endforeach
        @if($banks->hasMorePages())
            <a href="{{ $banks->nextPageUrl() }}" class="inline-flex items-center px-2.5 py-1.5 border border-slate-200 rounded-lg text-xs text-slate-500 no-underline bg-white">&raquo;</a>
        @else
            <span class="inline-flex items-center px-2.5 py-1.5 border border-slate-200 rounded-lg text-xs text-slate-300 pointer-events-none">&raquo;</span>
        @endif
    </div>
    @endif

</x-layouts.app>
