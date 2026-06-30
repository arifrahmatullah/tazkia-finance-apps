<x-layouts.app title="Detail Karyawan">
    <style>
        .back-link { display:inline-flex; align-items:center; gap:6px; color:#64748b; font-size:0.82rem; text-decoration:none; margin-bottom:20px; }
        .back-link:hover { color:#f97316; }
        .layout-cols { display:grid; grid-template-columns:340px 1fr; gap:22px; align-items:start; }
        .card { background:#fff; border-radius:14px; box-shadow:0 1px 4px rgba(0,0,0,.07); overflow:hidden; }
        .card-header { padding:16px 22px; border-bottom:1px solid #f1f5f9; display:flex; align-items:center; justify-content:space-between; }
        .card-header-title { font-size:0.875rem; font-weight:700; color:#0f172a; }
        .card-body { padding:22px; }
        /* Profile card */
        .profile-top { text-align:center; padding:28px 22px 20px; border-bottom:1px solid #f1f5f9; }
        .avatar { width:72px; height:72px; border-radius:50%; display:inline-flex; align-items:center; justify-content:center; font-size:1.6rem; font-weight:700; color:#fff; background:linear-gradient(135deg,#f97316,#ea580c); margin-bottom:12px; }
        .profile-name { font-size:1.05rem; font-weight:700; color:#0f172a; }
        .profile-title { font-size:0.82rem; color:#94a3b8; margin-top:3px; }
        .profile-nik { display:inline-block; margin-top:8px; font-family:monospace; font-size:0.8rem; background:#f1f5f9; color:#475569; padding:3px 10px; border-radius:6px; }
        .info-list { list-style:none; padding:0; margin:0; }
        .info-item { display:flex; align-items:flex-start; gap:10px; padding:11px 0; border-bottom:1px solid #f8fafc; font-size:0.845rem; }
        .info-item:last-child { border-bottom:none; }
        .info-icon { width:30px; height:30px; border-radius:8px; display:flex; align-items:center; justify-content:center; flex-shrink:0; background:#f8fafc; }
        .info-label { font-size:0.73rem; color:#94a3b8; font-weight:600; text-transform:uppercase; letter-spacing:.05em; }
        .info-value { color:#334155; font-weight:500; margin-top:1px; }
        .badge { display:inline-flex; align-items:center; gap:4px; padding:3px 10px; border-radius:99px; font-size:0.72rem; font-weight:600; }
        .badge-green { background:#dcfce7; color:#16a34a; }
        .badge-red { background:#fee2e2; color:#dc2626; }
        .badge-blue { background:#dbeafe; color:#2563eb; }
        .badge-gray { background:#f1f5f9; color:#64748b; }
        .badge-orange { background:#fff7ed; color:#ea580c; }
        /* Jabatan section */
        .action-row { display:flex; align-items:center; gap:10px; }
        .btn-sm { display:inline-flex; align-items:center; gap:5px; padding:6px 13px; border-radius:8px; border:none; cursor:pointer; font-size:0.78rem; font-weight:600; text-decoration:none; transition:all .15s; }
        .btn-orange { background:linear-gradient(135deg,#f97316,#ea580c); color:#fff; }
        .btn-orange:hover { transform:translateY(-1px); box-shadow:0 3px 10px rgba(249,115,22,.3); }
        .btn-link { background:#f1f5f9; color:#475569; }
        .btn-link:hover { background:#e2e8f0; }
        .btn-danger-sm { background:#fff1f2; color:#e11d48; }
        .btn-danger-sm:hover { background:#ffe4e6; }
        /* Position history table */
        table { width:100%; border-collapse:collapse; }
        th { padding:10px 16px; text-align:left; font-size:0.72rem; font-weight:600; color:#94a3b8; text-transform:uppercase; letter-spacing:.06em; border-bottom:1px solid #f1f5f9; background:#fafbfc; }
        td { padding:12px 16px; font-size:0.845rem; color:#334155; border-bottom:1px solid #f8fafc; vertical-align:middle; }
        tr:last-child td { border-bottom:none; }
        /* Assign form panel */
        .assign-panel { background:#fffbf7; border:1.5px solid #fed7aa; border-radius:12px; padding:20px; margin-bottom:20px; }
        .assign-title { font-size:0.87rem; font-weight:700; color:#c2410c; margin:0 0 14px; display:flex; align-items:center; gap:7px; }
        .form-row { display:grid; grid-template-columns:1fr 1fr; gap:14px; }
        .form-group { display:flex; flex-direction:column; gap:5px; }
        .form-group.full { grid-column:1/-1; }
        .form-label { font-size:0.78rem; font-weight:600; color:#374151; }
        .form-input { padding:8px 12px; border:1.5px solid #e2e8f0; border-radius:8px; font-size:0.845rem; color:#1e293b; background:#fff; outline:none; transition:border-color .15s; }
        .form-input:focus { border-color:#f97316; }
        .alert-success { background:#f0fdf4; border:1px solid #bbf7d0; color:#166534; padding:12px 16px; border-radius:10px; margin-bottom:18px; font-size:0.845rem; }
        .edit-actions { display:flex; gap:8px; }
    </style>

    <a href="{{ route('employees.index') }}" class="back-link">
        <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M19 12H5M12 5l-7 7 7 7"/></svg>
        Kembali ke Daftar Karyawan
    </a>

    @if(session('success'))
        <div class="alert-success">{{ session('success') }}</div>
    @endif

    <div class="layout-cols">
        {{-- Kolom kiri: profil karyawan --}}
        <div>
            <div class="card" style="margin-bottom:16px;">
                <div class="profile-top">
                    <div class="avatar">{{ strtoupper(substr($employee->name, 0, 1)) }}</div>
                    <div class="profile-name">
                        {{ $employee->name }}
                        @if($employee->title), <span style="font-weight:400;color:#64748b;">{{ $employee->title }}</span>@endif
                    </div>
                    <div class="profile-title">{{ $employee->organization->name ?? '-' }}</div>
                    <span class="profile-nik">{{ $employee->nik }}</span>
                    <div style="margin-top:10px;">
                        @if($employee->is_active)
                            <span class="badge badge-green">Aktif</span>
                        @else
                            <span class="badge badge-red">Nonaktif</span>
                        @endif
                        @if($employee->gender)
                            <span class="badge badge-gray" style="margin-left:4px;">{{ $employee->gender === 'L' ? 'Laki-laki' : 'Perempuan' }}</span>
                        @endif
                    </div>
                </div>
                <div class="card-body">
                    <ul class="info-list">
                        @if($employee->email)
                        <li class="info-item">
                            <div class="info-icon">
                                <svg width="15" height="15" fill="none" stroke="#f97316" stroke-width="2" viewBox="0 0 24 24"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                            </div>
                            <div>
                                <div class="info-label">Email</div>
                                <div class="info-value">{{ $employee->email }}</div>
                            </div>
                        </li>
                        @endif
                        @if($employee->phone)
                        <li class="info-item">
                            <div class="info-icon">
                                <svg width="15" height="15" fill="none" stroke="#f97316" stroke-width="2" viewBox="0 0 24 24"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07A19.5 19.5 0 013.07 10.81a19.79 19.79 0 01-3.07-8.67A2 2 0 012 0h3a2 2 0 012 1.72c.127.96.361 1.903.7 2.81a2 2 0 01-.45 2.11L6.91 7.91a16 16 0 006.1 6.1l1.27-.64a2 2 0 012.11.45c.907.339 1.85.573 2.81.7A2 2 0 0122 16.92z"/></svg>
                            </div>
                            <div>
                                <div class="info-label">Telepon</div>
                                <div class="info-value">{{ $employee->phone }}</div>
                            </div>
                        </li>
                        @endif
                        @if($employee->birth_date)
                        <li class="info-item">
                            <div class="info-icon">
                                <svg width="15" height="15" fill="none" stroke="#f97316" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                            </div>
                            <div>
                                <div class="info-label">Tanggal Lahir</div>
                                <div class="info-value">{{ $employee->birth_date->translatedFormat('d F Y') }}</div>
                            </div>
                        </li>
                        @endif
                        @if($employee->nidn)
                        <li class="info-item">
                            <div class="info-icon">
                                <svg width="15" height="15" fill="none" stroke="#f97316" stroke-width="2" viewBox="0 0 24 24"><path d="M22 19a2 2 0 01-2 2H4a2 2 0 01-2-2V5a2 2 0 012-2h5l2 3h9a2 2 0 012 2z"/></svg>
                            </div>
                            <div>
                                <div class="info-label">NIDN</div>
                                <div class="info-value" style="font-family:monospace;">{{ $employee->nidn }}</div>
                            </div>
                        </li>
                        @endif
                        @if($employee->rfid)
                        <li class="info-item">
                            <div class="info-icon">
                                <svg width="15" height="15" fill="none" stroke="#f97316" stroke-width="2" viewBox="0 0 24 24"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
                            </div>
                            <div>
                                <div class="info-label">RFID</div>
                                <div class="info-value" style="font-family:monospace;">{{ $employee->rfid }}</div>
                            </div>
                        </li>
                        @endif
                    </ul>
                </div>
            </div>
            <div class="edit-actions">
                <a href="{{ route('employees.edit', $employee) }}" class="btn-sm btn-link" style="flex:1;justify-content:center;">
                    <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                    Edit Data
                </a>
                <form id="del-emp-{{ $employee->id }}" method="POST" action="{{ route('employees.destroy', $employee) }}">
                    @csrf @method('DELETE')
                </form>
                <button type="button" class="btn-sm btn-danger-sm" style="flex:1;justify-content:center;"
                    onclick="confirmDelete('del-emp-{{ $employee->id }}', '{{ addslashes($employee->name) }}')">
                    <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"/><path d="M10 11v6M14 11v6"/><path d="M9 6V4a1 1 0 011-1h4a1 1 0 011 1v2"/></svg>
                    Hapus
                </button>
            </div>
        </div>

        {{-- Kolom kanan: riwayat jabatan --}}
        <div class="card">
            <div class="card-header">
                <span class="card-header-title">Riwayat Jabatan</span>
                <button type="button" class="btn-sm btn-orange" onclick="toggleAssignPanel()">
                    <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg>
                    Tetapkan Jabatan
                </button>
            </div>

            {{-- Panel assign jabatan --}}
            <div id="assignPanel" class="assign-panel" style="display:none; margin:16px 16px 0;">
                <p class="assign-title">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M12 8v4l3 3"/></svg>
                    Tetapkan Jabatan Baru
                </p>
                <form method="POST" action="{{ route('employees.positions.assign', $employee) }}">
                    @csrf
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Jabatan <span style="color:#ef4444">*</span></label>
                            <select name="position_id" class="form-input" required
                                onfocus="this.style.borderColor='#f97316'" onblur="this.style.borderColor='#e2e8f0'">
                                <option value="">-- Pilih Jabatan --</option>
                                @foreach($positions as $pos)
                                    <option value="{{ $pos->id }}">{{ $pos->name }} ({{ $pos->department->name ?? '' }})</option>
                                @endforeach
                            </select>
                            @error('position_id') <span style="font-size:.75rem;color:#dc2626;">{{ $message }}</span> @enderror
                        </div>
                        <div class="form-group">
                            <label class="form-label">Mulai Berlaku <span style="color:#ef4444">*</span></label>
                            <input type="date" name="start_date" class="form-input" required
                                value="{{ old('start_date', date('Y-m-d')) }}"
                                onfocus="this.style.borderColor='#f97316'" onblur="this.style.borderColor='#e2e8f0'">
                            @error('start_date') <span style="font-size:.75rem;color:#dc2626;">{{ $message }}</span> @enderror
                        </div>
                        <div class="form-group full">
                            <label class="form-label">Keterangan</label>
                            <input type="text" name="notes" class="form-input" placeholder="Opsional"
                                value="{{ old('notes') }}"
                                onfocus="this.style.borderColor='#f97316'" onblur="this.style.borderColor='#e2e8f0'">
                        </div>
                    </div>
                    <div style="display:flex;gap:8px;margin-top:14px;">
                        <button type="submit" class="btn-sm btn-orange">Simpan Jabatan</button>
                        <button type="button" class="btn-sm btn-link" onclick="toggleAssignPanel()">Batal</button>
                    </div>
                </form>
            </div>

            @if($employee->positions->isEmpty())
                <div style="padding:50px 20px;text-align:center;color:#94a3b8;">
                    <svg width="36" height="36" fill="none" stroke="#cbd5e1" stroke-width="1.5" viewBox="0 0 24 24" style="margin:0 auto 10px;display:block;"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 7V5a2 2 0 00-2-2h-4a2 2 0 00-2 2v2"/></svg>
                    Belum ada riwayat jabatan. Klik "Tetapkan Jabatan" untuk mulai.
                </div>
            @else
                <table>
                    <thead>
                        <tr>
                            <th>Jabatan</th>
                            <th>Departemen</th>
                            <th>Mulai</th>
                            <th>Selesai</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($employee->positions->sortByDesc('start_date') as $ep)
                        <tr>
                            <td style="font-weight:600;">{{ $ep->position->name ?? '-' }}</td>
                            <td style="font-size:0.82rem;color:#64748b;">{{ $ep->position->department->name ?? '-' }}</td>
                            <td style="font-size:0.82rem;">{{ $ep->start_date?->format('d/m/Y') }}</td>
                            <td style="font-size:0.82rem;">{{ $ep->end_date ? $ep->end_date->format('d/m/Y') : '—' }}</td>
                            <td>
                                @if($ep->is_active)
                                    <span class="badge badge-orange">Aktif</span>
                                @else
                                    <span class="badge badge-gray">Selesai</span>
                                @endif
                            </td>
                            <td>
                                <form id="del-pos-{{ $ep->id }}" method="POST"
                                    action="{{ route('employees.positions.remove', [$employee, $ep]) }}">
                                    @csrf @method('DELETE')
                                </form>
                                <button type="button" class="btn-sm btn-danger-sm"
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
            const panel = document.getElementById('assignPanel');
            panel.style.display = panel.style.display === 'none' ? 'block' : 'none';
        }
        @if($errors->any()) document.getElementById('assignPanel').style.display = 'block'; @endif
    </script>
</x-layouts.app>
