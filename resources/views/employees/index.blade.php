<x-layouts.app title="Daftar Karyawan">

    <div class="flex items-center justify-between mb-5">
        <div>
            <h2 class="text-lg font-bold text-slate-900 m-0 mb-0.5">Daftar Karyawan</h2>
            <p class="text-xs text-slate-400 m-0">Manajemen data karyawan aktif</p>
        </div>
        <a href="{{ route('employees.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-gradient-to-br from-orange-400 to-orange-500 text-white text-sm font-semibold shadow-sm hover:-translate-y-px transition-all no-underline">
            <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg>
            Tambah Karyawan
        </a>
    </div>

    @if(session('success'))
        <div class="flex items-center gap-2.5 px-4 py-3 bg-green-50 border border-green-200 rounded-xl mb-4 text-sm text-green-700">{{ session('success') }}</div>
    @endif

    <form method="GET" action="{{ route('employees.index') }}" class="mb-4">
        <div class="flex gap-2">
            <div class="relative flex-1">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
                <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Cari nama, NIK, atau email..."
                    class="w-full pl-9 pr-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-orange-300 focus:border-orange-400 bg-white">
            </div>
            <button type="submit" class="px-4 py-2.5 rounded-xl bg-orange-500 text-white text-sm font-semibold border-0 cursor-pointer hover:bg-orange-600 transition-colors">Cari</button>
            @if($search)
            <a href="{{ route('employees.index') }}" class="px-4 py-2.5 rounded-xl bg-slate-100 text-slate-600 text-sm font-semibold no-underline hover:bg-slate-200 transition-colors">Reset</a>
            @endif
        </div>
    </form>

    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="px-4 py-3.5 border-b border-slate-100 flex items-center justify-between">
            <span class="text-sm font-semibold text-slate-700">
                Karyawan
                @if($search) <span class="text-slate-400 font-normal text-xs ml-1">— hasil pencarian "{{ $search }}"</span> @endif
            </span>
            <span class="bg-slate-100 text-slate-500 text-[11px] font-semibold px-2.5 py-0.5 rounded-full">{{ $employees->total() }} data</span>
        </div>
        @if($employees->isEmpty())
            <div class="py-12 px-5 text-center text-slate-400">
                <div class="text-4xl mb-3">👥</div>
                <p class="text-sm m-0">Belum ada karyawan. Klik "Tambah Karyawan" untuk mulai.</p>
            </div>
        @else
            <table class="w-full border-collapse">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-100">
                        <th class="px-4 py-3 text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wide">#</th>
                        <th class="px-4 py-3 text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wide">Karyawan</th>
                        <th class="px-4 py-3 text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wide">NIK</th>
                        <th class="px-4 py-3 text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wide">Jabatan Aktif</th>
                        <th class="px-4 py-3 text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wide">Organisasi</th>
                        <th class="px-4 py-3 text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wide">Kontak</th>
                        <th class="px-4 py-3 text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wide">Status</th>
                        <th class="px-4 py-3 text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wide">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($employees as $i => $emp)
                    <tr class="border-b border-slate-50 hover:bg-slate-50 transition-colors">
                        <td class="px-4 py-3 text-xs text-slate-400 align-middle">{{ $employees->firstItem() + $loop->index }}</td>
                        <td class="px-4 py-3 text-sm text-slate-600 align-middle">
                            <div class="font-semibold text-slate-900">
                                {{ $emp->name }}
                                @if($emp->title) <span class="font-normal text-slate-500 text-sm">, {{ $emp->title }}</span> @endif
                            </div>
                            <div class="text-xs text-slate-400 mt-0.5">{{ $emp->gender === 'L' ? 'Laki-laki' : ($emp->gender === 'P' ? 'Perempuan' : '-') }}</div>
                        </td>
                        <td class="px-4 py-3 font-mono text-xs text-slate-500 align-middle">{{ $emp->nik }}</td>
                        <td class="px-4 py-3 text-sm text-slate-600 align-middle">
                            @if($emp->activePosition && $emp->activePosition->position)
                                <a href="{{ route('positions.edit', $emp->activePosition->position) }}" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-blue-100 text-blue-700 hover:bg-blue-200 transition-colors no-underline">{{ $emp->activePosition->position->name }}</a>
                                <div class="text-xs text-slate-400 mt-0.5">{{ $emp->activePosition->position->department->name ?? '' }}</div>
                            @else
                                <span class="text-slate-300 text-xs">— belum ada</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-xs text-slate-600 align-middle">{{ $emp->organization->name ?? '-' }}</td>
                        <td class="px-4 py-3 text-sm text-slate-600 align-middle">
                            <div class="text-xs">{{ $emp->email ?? '-' }}</div>
                            <div class="text-xs text-slate-400 mt-0.5">{{ $emp->phone ?? '' }}</div>
                        </td>
                        <td class="px-4 py-3 text-sm text-slate-600 align-middle">
                            @if($emp->is_active)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-green-100 text-green-700">Aktif</span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-red-100 text-red-600">Nonaktif</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-sm text-slate-600 align-middle">
                            <div class="flex gap-1.5 flex-wrap">
                                <a href="{{ route('employees.show', $emp) }}" class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-xs font-medium bg-slate-100 text-slate-600 hover:bg-slate-200 transition-colors no-underline">Detail</a>
                                <a href="{{ route('employees.edit', $emp) }}" class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-xs font-medium bg-blue-50 text-blue-600 hover:bg-blue-100 transition-colors no-underline">Edit</a>
                                <form id="del-emp-{{ $emp->id }}" method="POST" action="{{ route('employees.destroy', $emp) }}">
                                    @csrf @method('DELETE')
                                </form>
                                <button type="button" class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-xs font-medium bg-red-50 text-red-600 hover:bg-red-100 transition-colors border-0 cursor-pointer"
                                    onclick="confirmDelete('del-emp-{{ $emp->id }}', '{{ addslashes($emp->name) }}')">
                                    Hapus
                                </button>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    @if($employees->hasPages())
    <div class="mt-4 flex items-center justify-between text-xs text-slate-400">
        <span>Menampilkan {{ $employees->firstItem() }}–{{ $employees->lastItem() }} dari {{ $employees->total() }} karyawan</span>
        {{ $employees->links() }}
    </div>
    @endif

</x-layouts.app>
