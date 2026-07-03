<x-layouts.app title="Daftar Departemen" breadcrumb="Master Data / Departemen">

    <div class="flex items-center justify-between mb-5">
        <div>
            <h2 class="text-lg font-bold text-slate-900 m-0 mb-0.5">Daftar Departemen</h2>
            <p class="text-xs text-slate-400 m-0">Kelola departemen di setiap organisasi</p>
        </div>
        <a href="{{ route('departments.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-gradient-to-br from-orange-400 to-orange-500 text-white text-sm font-semibold shadow-sm hover:-translate-y-px transition-all no-underline">
            <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
            </svg>
            Tambah Departemen
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

    {{-- Search & Filter --}}
    <form method="GET" action="{{ route('departments.index') }}" class="mb-4">
        <div class="flex gap-2.5 flex-wrap items-center">
            <div class="relative flex-1 min-w-[200px]">
                <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"
                     class="absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400">
                    <circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/>
                </svg>
                <input type="text" name="search" value="{{ request('search') }}"
                    placeholder="Cari nama atau kode..."
                    class="w-full py-2 pl-9 pr-3 border border-slate-200 rounded-xl text-sm text-slate-800 outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100 transition-colors">
            </div>

            @if($organizations->count() > 1)
            <select name="organization_id"
                class="py-2 px-3 border border-slate-200 rounded-xl text-sm text-slate-700 bg-white outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100 transition-colors min-w-[170px]">
                <option value="">Semua Organisasi</option>
                @foreach($organizations as $org)
                    <option value="{{ $org->id }}" {{ request('organization_id') == $org->id ? 'selected' : '' }}>
                        {{ $org->name }}
                    </option>
                @endforeach
            </select>
            @endif

            <select name="status"
                class="py-2 px-3 border border-slate-200 rounded-xl text-sm text-slate-700 bg-white outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100 transition-colors min-w-[140px]">
                <option value="">Semua Status</option>
                <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Aktif</option>
                <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Nonaktif</option>
            </select>

            <button type="submit"
                class="px-4 py-2 rounded-xl border-0 cursor-pointer text-sm font-semibold bg-gradient-to-br from-orange-400 to-orange-500 text-white">
                Cari
            </button>

            @if(request()->hasAny(['search','organization_id','status']))
            <a href="{{ route('departments.index') }}"
                class="px-3.5 py-2 rounded-xl border border-slate-200 text-sm text-slate-500 no-underline bg-white">
                Reset
            </a>
            @endif
        </div>
    </form>

    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <table class="w-full border-collapse">
            <thead>
                <tr class="bg-slate-50 border-b border-slate-100">
                    <th class="px-4 py-3 text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wide">#</th>
                    <th class="px-4 py-3 text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wide">Departemen</th>
                    <th class="px-4 py-3 text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wide">Kode</th>
                    <th class="px-4 py-3 text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wide">Organisasi</th>
                    <th class="px-4 py-3 text-center text-[11px] font-semibold text-slate-400 uppercase tracking-wide">Budget</th>
                    <th class="px-4 py-3 text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wide">Status</th>
                    <th class="px-4 py-3 text-center text-[11px] font-semibold text-slate-400 uppercase tracking-wide">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($departments as $i => $dept)
                <tr class="border-b border-slate-50 hover:bg-slate-50 transition-colors">

                    <td class="px-4 py-3 text-sm text-slate-400 align-middle">{{ $i + 1 }}</td>

                    <td class="px-4 py-3 text-sm text-slate-600 align-middle">
                        <div class="text-sm font-semibold text-slate-800">{{ $dept->name }}</div>
                        @if($dept->description)
                        <div class="text-[11px] text-slate-400 mt-px">{{ $dept->description }}</div>
                        @endif
                    </td>

                    <td class="px-4 py-3 text-sm text-slate-600 align-middle">
                        <span class="font-mono text-xs font-semibold text-slate-500 bg-slate-100 px-2 py-0.5 rounded">{{ $dept->code }}</span>
                    </td>

                    <td class="px-4 py-3 text-sm text-slate-600 align-middle">
                        <div class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-blue-50 rounded-lg">
                            <svg width="11" height="11" fill="none" stroke="#1d4ed8" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16"/>
                            </svg>
                            <span class="text-xs font-medium text-blue-700">{{ $dept->organization->name }}</span>
                        </div>
                    </td>

                    <td class="px-4 py-3 text-sm text-slate-600 align-middle text-center">
                        @if($dept->has_budget)
                            <div class="inline-flex flex-col items-center gap-1">
                                <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-green-700 bg-green-50 px-2 py-0.5 rounded-full">
                                    <svg width="10" height="10" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                    Punya Budget
                                </span>
                                @if($dept->budget_blocking)
                                <span class="text-[11px] text-red-600 bg-red-50 px-1.5 py-0.5 rounded-full font-medium">Blokir jika habis</span>
                                @endif
                            </div>
                        @else
                            <span class="text-[11px] text-slate-400">—</span>
                        @endif
                    </td>

                    <td class="px-4 py-3 text-sm text-slate-600 align-middle">
                        @if($dept->is_active)
                        <span class="inline-flex items-center gap-1.5 text-[11px] font-semibold text-green-700">
                            <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span>Aktif
                        </span>
                        @else
                        <span class="inline-flex items-center gap-1.5 text-[11px] font-semibold text-red-600">
                            <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span>Nonaktif
                        </span>
                        @endif
                    </td>

                    <td class="px-4 py-3 text-sm text-slate-600 align-middle text-center">
                        <div class="inline-flex items-center gap-1.5">
                            <a href="{{ route('departments.edit', $dept) }}"
                               class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-xs font-medium bg-blue-50 text-blue-600 hover:bg-blue-100 transition-colors no-underline">
                                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                                Edit
                            </a>
                            <form id="del-dept-{{ $dept->id }}" method="POST" action="{{ route('departments.destroy', $dept) }}">
                                @csrf @method('DELETE')
                            </form>
                            <button type="button"
                                onclick="confirmDelete('del-dept-{{ $dept->id }}', '{{ addslashes($dept->name) }}')"
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
                    <td colspan="7" class="px-4 py-12 text-center text-slate-400">
                        <svg width="40" height="40" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" class="mx-auto mb-2.5 opacity-40">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        <p class="text-sm m-0">Belum ada departemen</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

</x-layouts.app>
