<x-layouts.app title="Daftar Karyawan">
    <style>
        .page-header { display:flex; align-items:center; justify-content:space-between; margin-bottom:24px; }
        .page-title { font-size:1.35rem; font-weight:700; color:#0f172a; margin:0; }
        .page-subtitle { font-size:0.82rem; color:#64748b; margin-top:2px; }
        .btn-primary { display:inline-flex; align-items:center; gap:7px; padding:9px 18px; border-radius:9px; border:none; cursor:pointer; font-size:0.845rem; font-weight:600; text-decoration:none; background:linear-gradient(135deg,#f97316,#ea580c); color:#fff; transition:all .15s; }
        .btn-primary:hover { background:linear-gradient(135deg,#ea580c,#c2410c); transform:translateY(-1px); box-shadow:0 4px 12px rgba(249,115,22,.35); }
        .card { background:#fff; border-radius:14px; box-shadow:0 1px 4px rgba(0,0,0,.07); overflow:hidden; }
        .card-header { padding:16px 22px; border-bottom:1px solid #f1f5f9; display:flex; align-items:center; justify-content:space-between; }
        .card-header-title { font-size:0.9rem; font-weight:600; color:#334155; }
        .badge-count { background:#f1f5f9; color:#64748b; font-size:0.72rem; font-weight:600; padding:3px 9px; border-radius:99px; }
        table { width:100%; border-collapse:collapse; }
        th { padding:11px 16px; text-align:left; font-size:0.72rem; font-weight:600; color:#94a3b8; text-transform:uppercase; letter-spacing:.06em; border-bottom:1px solid #f1f5f9; background:#fafbfc; white-space:nowrap; }
        td { padding:13px 16px; font-size:0.845rem; color:#334155; border-bottom:1px solid #f8fafc; vertical-align:middle; }
        tr:last-child td { border-bottom:none; }
        tr:hover td { background:#fafbff; }
        .badge { display:inline-flex; align-items:center; gap:4px; padding:3px 10px; border-radius:99px; font-size:0.72rem; font-weight:600; }
        .badge-green { background:#dcfce7; color:#16a34a; }
        .badge-red { background:#fee2e2; color:#dc2626; }
        .badge-blue { background:#dbeafe; color:#2563eb; }
        .badge-gray { background:#f1f5f9; color:#64748b; }
        .emp-name { font-weight:600; color:#0f172a; }
        .emp-sub { font-size:0.77rem; color:#94a3b8; margin-top:1px; }
        .action-btn { display:inline-flex; align-items:center; gap:4px; padding:5px 11px; border-radius:7px; font-size:0.78rem; font-weight:500; text-decoration:none; cursor:pointer; border:none; transition:all .15s; }
        .btn-detail { background:#eff6ff; color:#2563eb; }
        .btn-detail:hover { background:#dbeafe; }
        .btn-edit { background:#f0fdf4; color:#16a34a; }
        .btn-edit:hover { background:#dcfce7; }
        .btn-delete { background:#fff1f2; color:#e11d48; }
        .btn-delete:hover { background:#ffe4e6; }
        .empty-state { padding:60px 20px; text-align:center; }
        .empty-icon { font-size:2.5rem; margin-bottom:12px; }
        .empty-text { color:#94a3b8; font-size:0.9rem; }
        .alert-success { background:#f0fdf4; border:1px solid #bbf7d0; color:#166534; padding:12px 16px; border-radius:10px; margin-bottom:20px; font-size:0.845rem; }
    </style>

    <div class="page-header">
        <div>
            <h1 class="page-title">Daftar Karyawan</h1>
            <p class="page-subtitle">Manajemen data karyawan aktif</p>
        </div>
        <a href="{{ route('employees.create') }}" class="btn-primary">
            <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg>
            Tambah Karyawan
        </a>
    </div>

    @if(session('success'))
        <div class="alert-success">{{ session('success') }}</div>
    @endif

    <div class="card">
        <div class="card-header">
            <span class="card-header-title">Karyawan</span>
            <span class="badge-count">{{ $employees->count() }} data</span>
        </div>
        @if($employees->isEmpty())
            <div class="empty-state">
                <div class="empty-icon">👥</div>
                <p class="empty-text">Belum ada karyawan. Klik "Tambah Karyawan" untuk mulai.</p>
            </div>
        @else
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Karyawan</th>
                        <th>NIK</th>
                        <th>Jabatan Aktif</th>
                        <th>Organisasi</th>
                        <th>Kontak</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($employees as $i => $emp)
                    <tr>
                        <td style="color:#94a3b8;font-size:0.78rem;">{{ $i + 1 }}</td>
                        <td>
                            <div class="emp-name">
                                {{ $emp->name }}
                                @if($emp->title) <span style="font-weight:400;color:#64748b;font-size:0.8rem;">, {{ $emp->title }}</span> @endif
                            </div>
                            <div class="emp-sub">{{ $emp->gender === 'L' ? 'Laki-laki' : ($emp->gender === 'P' ? 'Perempuan' : '-') }}</div>
                        </td>
                        <td style="font-family:monospace;font-size:0.82rem;color:#475569;">{{ $emp->nik }}</td>
                        <td>
                            @if($emp->activePosition && $emp->activePosition->position)
                                <span class="badge badge-blue">{{ $emp->activePosition->position->name }}</span>
                                <div class="emp-sub">{{ $emp->activePosition->position->department->name ?? '' }}</div>
                            @else
                                <span style="color:#cbd5e1;font-size:0.8rem;">— belum ada</span>
                            @endif
                        </td>
                        <td style="font-size:0.82rem;">{{ $emp->organization->name ?? '-' }}</td>
                        <td>
                            <div style="font-size:0.82rem;">{{ $emp->email ?? '-' }}</div>
                            <div class="emp-sub">{{ $emp->phone ?? '' }}</div>
                        </td>
                        <td>
                            @if($emp->is_active)
                                <span class="badge badge-green">Aktif</span>
                            @else
                                <span class="badge badge-red">Nonaktif</span>
                            @endif
                        </td>
                        <td>
                            <div style="display:flex;gap:6px;flex-wrap:wrap;">
                                <a href="{{ route('employees.show', $emp) }}" class="action-btn btn-detail">Detail</a>
                                <a href="{{ route('employees.edit', $emp) }}" class="action-btn btn-edit">Edit</a>
                                <form id="del-emp-{{ $emp->id }}" method="POST" action="{{ route('employees.destroy', $emp) }}">
                                    @csrf @method('DELETE')
                                </form>
                                <button type="button" class="action-btn btn-delete"
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
</x-layouts.app>
