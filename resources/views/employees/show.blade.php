<x-layouts.app title="Detail Karyawan">

    <a href="{{ route('employees.index') }}" class="inline-flex items-center gap-1.5 text-sm text-slate-500 hover:text-orange-500 mb-5 no-underline">
        <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M19 12H5M12 5l-7 7 7 7"/></svg>
        Kembali ke Daftar Karyawan
    </a>

    @if(session('success'))
        <div class="flex items-center gap-2.5 px-4 py-3 bg-green-50 border border-green-200 rounded-xl mb-4 text-sm text-green-700">
            <svg width="16" height="16" fill="#16a34a" viewBox="0 0 20 20" class="shrink-0"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
            {{ session('success') }}
        </div>
    @endif

    <div class="grid grid-cols-[340px_1fr] gap-5 items-start">
        {{-- Kolom kiri: profil karyawan --}}
        <div>
            <div class="bg-white rounded-xl shadow-sm overflow-hidden mb-4">
                <div class="text-center px-5 py-7 border-b border-slate-100">
                    <div class="w-[72px] h-[72px] rounded-full inline-flex items-center justify-center text-2xl font-bold text-white mx-auto mb-3" style="background:linear-gradient(135deg,#f97316,#ea580c)">
                        {{ strtoupper(substr($employee->name, 0, 1)) }}
                    </div>
                    <div class="text-[1.05rem] font-bold text-slate-900">
                        {{ $employee->name }}
                        @if($employee->title), <span class="font-normal text-slate-500">{{ $employee->title }}</span>@endif
                    </div>
                    <div class="text-sm text-slate-400 mt-1">{{ $employee->organization->name ?? '-' }}</div>
                    <span class="inline-block mt-2 font-mono text-xs bg-slate-100 text-slate-500 px-2.5 py-0.5 rounded">{{ $employee->nik }}</span>
                    <div class="mt-2.5 flex items-center justify-center gap-1.5">
                        @if($employee->is_active)
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-green-100 text-green-700">Aktif</span>
                        @else
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-red-100 text-red-600">Nonaktif</span>
                        @endif
                        @if($employee->gender)
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-slate-100 text-slate-600">{{ $employee->gender === 'L' ? 'Laki-laki' : 'Perempuan' }}</span>
                        @endif
                    </div>
                </div>
                <div class="px-5 py-3">
                    <ul class="list-none p-0 m-0">
                        @if($employee->email)
                        <li class="flex items-start gap-2.5 py-2.5 border-b border-slate-50 text-sm">
                            <div class="w-[30px] h-[30px] rounded-lg flex items-center justify-center shrink-0 bg-slate-50">
                                <svg width="15" height="15" fill="none" stroke="#f97316" stroke-width="2" viewBox="0 0 24 24"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                            </div>
                            <div>
                                <div class="text-[11px] font-semibold text-slate-400 uppercase tracking-wide">Email</div>
                                <div class="text-slate-700 font-medium mt-0.5">{{ $employee->email }}</div>
                            </div>
                        </li>
                        @endif
                        @if($employee->phone)
                        <li class="flex items-start gap-2.5 py-2.5 border-b border-slate-50 text-sm">
                            <div class="w-[30px] h-[30px] rounded-lg flex items-center justify-center shrink-0 bg-slate-50">
                                <svg width="15" height="15" fill="none" stroke="#f97316" stroke-width="2" viewBox="0 0 24 24"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07A19.5 19.5 0 013.07 10.81a19.79 19.79 0 01-3.07-8.67A2 2 0 012 0h3a2 2 0 012 1.72c.127.96.361 1.903.7 2.81a2 2 0 01-.45 2.11L6.91 7.91a16 16 0 006.1 6.1l1.27-.64a2 2 0 012.11.45c.907.339 1.85.573 2.81.7A2 2 0 0122 16.92z"/></svg>
                            </div>
                            <div>
                                <div class="text-[11px] font-semibold text-slate-400 uppercase tracking-wide">Telepon</div>
                                <div class="text-slate-700 font-medium mt-0.5">{{ $employee->phone }}</div>
                            </div>
                        </li>
                        @endif
                        @if($employee->birth_date)
                        <li class="flex items-start gap-2.5 py-2.5 border-b border-slate-50 text-sm">
                            <div class="w-[30px] h-[30px] rounded-lg flex items-center justify-center shrink-0 bg-slate-50">
                                <svg width="15" height="15" fill="none" stroke="#f97316" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                            </div>
                            <div>
                                <div class="text-[11px] font-semibold text-slate-400 uppercase tracking-wide">Tanggal Lahir</div>
                                <div class="text-slate-700 font-medium mt-0.5">{{ $employee->birth_date->translatedFormat('d F Y') }}</div>
                            </div>
                        </li>
                        @endif
                        @if($employee->nidn)
                        <li class="flex items-start gap-2.5 py-2.5 border-b border-slate-50 text-sm">
                            <div class="w-[30px] h-[30px] rounded-lg flex items-center justify-center shrink-0 bg-slate-50">
                                <svg width="15" height="15" fill="none" stroke="#f97316" stroke-width="2" viewBox="0 0 24 24"><path d="M22 19a2 2 0 01-2 2H4a2 2 0 01-2-2V5a2 2 0 012-2h5l2 3h9a2 2 0 012 2z"/></svg>
                            </div>
                            <div>
                                <div class="text-[11px] font-semibold text-slate-400 uppercase tracking-wide">NIDN</div>
                                <div class="text-slate-700 font-medium mt-0.5 font-mono">{{ $employee->nidn }}</div>
                            </div>
                        </li>
                        @endif
                        @if($employee->rfid)
                        <li class="flex items-start gap-2.5 py-2.5 text-sm">
                            <div class="w-[30px] h-[30px] rounded-lg flex items-center justify-center shrink-0 bg-slate-50">
                                <svg width="15" height="15" fill="none" stroke="#f97316" stroke-width="2" viewBox="0 0 24 24"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
                            </div>
                            <div>
                                <div class="text-[11px] font-semibold text-slate-400 uppercase tracking-wide">RFID</div>
                                <div class="text-slate-700 font-medium mt-0.5 font-mono">{{ $employee->rfid }}</div>
                            </div>
                        </li>
                        @endif
                    </ul>
                </div>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('employees.edit', $employee) }}" class="flex-1 inline-flex items-center justify-center gap-1.5 px-3 py-2 rounded-lg text-xs font-semibold bg-slate-100 text-slate-600 hover:bg-slate-200 transition-colors no-underline">
                    <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                    Edit Data
                </a>
                <form id="del-emp-{{ $employee->id }}" method="POST" action="{{ route('employees.destroy', $employee) }}">
                    @csrf @method('DELETE')
                </form>
                <button type="button" class="flex-1 inline-flex items-center justify-center gap-1.5 px-3 py-2 rounded-lg text-xs font-semibold bg-red-50 text-red-600 hover:bg-red-100 transition-colors border-0 cursor-pointer"
                    onclick="confirmDelete('del-emp-{{ $employee->id }}', '{{ addslashes($employee->name) }}')">
                    <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"/><path d="M10 11v6M14 11v6"/><path d="M9 6V4a1 1 0 011-1h4a1 1 0 011 1v2"/></svg>
                    Hapus
                </button>
            </div>
        </div>

        {{-- Kolom kanan: riwayat jabatan --}}
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
                <span class="text-sm font-bold text-slate-900">Riwayat Jabatan</span>
                <button type="button" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold bg-gradient-to-br from-orange-400 to-orange-500 text-white border-0 cursor-pointer hover:-translate-y-px transition-all" onclick="toggleAssignPanel()">
                    <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg>
                    Tetapkan Jabatan
                </button>
            </div>

            {{-- Panel assign jabatan --}}
            <div id="assignPanel" class="hidden mx-4 mt-4 px-5 py-4 bg-orange-50 border border-orange-200 rounded-xl">
                <p class="text-sm font-bold text-orange-700 mb-3.5 flex items-center gap-1.5">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M12 8v4l3 3"/></svg>
                    Tetapkan Jabatan Baru
                </p>
                <form method="POST" action="{{ route('employees.positions.assign', $employee) }}">
                    @csrf
                    <div class="grid grid-cols-2 gap-3.5">
                        <div class="flex flex-col gap-1.5">
                            <label class="text-xs font-semibold text-slate-600">Jabatan <span class="text-red-500 ml-0.5">*</span></label>
                            <select name="position_id" class="w-full px-3 py-2.5 border border-slate-200 rounded-xl text-sm text-slate-800 bg-white outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100 transition-colors" required>
                                <option value="">-- Pilih Jabatan --</option>
                                @foreach($positions as $pos)
                                    <option value="{{ $pos->id }}">{{ $pos->name }} ({{ $pos->department->name ?? '' }})</option>
                                @endforeach
                            </select>
                            @error('position_id') <span class="text-xs text-red-500 mt-0.5">{{ $message }}</span> @enderror
                        </div>
                        <div class="flex flex-col gap-1.5">
                            <label class="text-xs font-semibold text-slate-600">Mulai Berlaku <span class="text-red-500 ml-0.5">*</span></label>
                            <input type="date" name="start_date" class="w-full px-3 py-2.5 border border-slate-200 rounded-xl text-sm text-slate-800 bg-white outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100 transition-colors" required
                                value="{{ old('start_date', date('Y-m-d')) }}">
                            @error('start_date') <span class="text-xs text-red-500 mt-0.5">{{ $message }}</span> @enderror
                        </div>
                        <div class="flex flex-col gap-1.5 col-span-2">
                            <label class="text-xs font-semibold text-slate-600">Keterangan</label>
                            <input type="text" name="notes" class="w-full px-3 py-2.5 border border-slate-200 rounded-xl text-sm text-slate-800 bg-white outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100 transition-colors"
                                placeholder="Opsional" value="{{ old('notes') }}">
                        </div>
                    </div>
                    <div class="flex gap-2 mt-3.5">
                        <button type="submit" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-xs font-semibold bg-gradient-to-br from-orange-400 to-orange-500 text-white border-0 cursor-pointer hover:-translate-y-px transition-all">Simpan Jabatan</button>
                        <button type="button" class="inline-flex items-center px-3.5 py-2 rounded-lg text-xs font-medium bg-white text-slate-600 border border-slate-200 cursor-pointer" onclick="toggleAssignPanel()">Batal</button>
                    </div>
                </form>
            </div>

            @if($employee->positions->isEmpty())
                <div class="py-12 px-5 text-center text-slate-400">
                    <svg width="36" height="36" fill="none" stroke="#cbd5e1" stroke-width="1.5" viewBox="0 0 24 24" class="mx-auto mb-2.5 block"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 7V5a2 2 0 00-2-2h-4a2 2 0 00-2 2v2"/></svg>
                    <p class="text-sm m-0">Belum ada riwayat jabatan. Klik "Tetapkan Jabatan" untuk mulai.</p>
                </div>
            @else
                <table class="w-full border-collapse">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-100">
                            <th class="px-4 py-3 text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wide">Jabatan</th>
                            <th class="px-4 py-3 text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wide">Departemen</th>
                            <th class="px-4 py-3 text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wide">Mulai</th>
                            <th class="px-4 py-3 text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wide">Selesai</th>
                            <th class="px-4 py-3 text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wide">Status</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($employee->positions->sortByDesc('start_date') as $ep)
                        <tr class="border-b border-slate-50 hover:bg-slate-50 transition-colors last:border-b-0">
                            <td class="px-4 py-3 text-sm font-semibold text-slate-800 align-middle">{{ $ep->position->name ?? '-' }}</td>
                            <td class="px-4 py-3 text-xs text-slate-500 align-middle">{{ $ep->position->department->name ?? '-' }}</td>
                            <td class="px-4 py-3 text-xs text-slate-600 align-middle">{{ $ep->start_date?->format('d/m/Y') }}</td>
                            <td class="px-4 py-3 text-xs text-slate-600 align-middle">{{ $ep->end_date ? $ep->end_date->format('d/m/Y') : '—' }}</td>
                            <td class="px-4 py-3 align-middle">
                                @if($ep->is_active)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-orange-100 text-orange-700">Aktif</span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-slate-100 text-slate-500">Selesai</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 align-middle">
                                <form id="del-pos-{{ $ep->id }}" method="POST"
                                    action="{{ route('employees.positions.remove', [$employee, $ep]) }}">
                                    @csrf @method('DELETE')
                                </form>
                                <button type="button" class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-xs font-medium bg-red-50 text-red-600 hover:bg-red-100 transition-colors border-0 cursor-pointer"
                                    onclick="confirmDelete('del-pos-{{ $ep->id }}', 'riwayat jabatan ini')">
                                    Hapus
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>

    <script>
        function toggleAssignPanel() {
            document.getElementById('assignPanel').classList.toggle('hidden');
        }
        @if($errors->any()) document.getElementById('assignPanel').classList.remove('hidden'); @endif
    </script>
</x-layouts.app>
