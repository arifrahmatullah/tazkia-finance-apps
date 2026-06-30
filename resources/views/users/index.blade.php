<x-layouts.app title="Manajemen User">
<style>
    .page-header { display:flex; align-items:center; justify-content:space-between; margin-bottom:20px; }
    .page-title { font-size:1.1rem; font-weight:700; color:#0f172a; margin:0 0 3px; }
    .page-sub { font-size:0.78rem; color:#94a3b8; margin:0; }
    .btn-primary { display:inline-flex; align-items:center; gap:7px; padding:9px 16px; border-radius:9px; background:linear-gradient(135deg,#ea580c,#f97316); color:#fff; font-size:0.83rem; font-weight:600; text-decoration:none; box-shadow:0 3px 10px rgba(234,88,12,.3); transition:all .15s; }
    .btn-primary:hover { box-shadow:0 6px 16px rgba(234,88,12,.4); transform:translateY(-1px); }
    /* Filter bar */
    .filter-bar { display:flex; gap:10px; flex-wrap:wrap; align-items:center; margin-bottom:16px; }
    .search-wrap { position:relative; flex:1; min-width:200px; }
    .search-wrap svg { position:absolute; left:11px; top:50%; transform:translateY(-50%); pointer-events:none; }
    .fi { padding:8px 12px; border:1.5px solid #e2e8f0; border-radius:9px; font-size:0.845rem; color:#374151; background:#fff; outline:none; transition:border-color .15s; }
    .fi:focus { border-color:#f97316; }
    .fi-search { padding-left:34px; width:100%; }
    .btn-filter { padding:8px 18px; border-radius:9px; border:none; cursor:pointer; font-size:0.845rem; font-weight:600; background:linear-gradient(135deg,#f97316,#ea580c); color:#fff; }
    .btn-reset { padding:8px 14px; border-radius:9px; border:1.5px solid #e2e8f0; font-size:0.845rem; color:#64748b; text-decoration:none; background:#fff; }
    /* Table card */
    .card { background:#fff; border-radius:14px; border:1px solid #f1f5f9; box-shadow:0 1px 4px rgba(0,0,0,.04); overflow:hidden; }
    .card-header { padding:14px 20px; border-bottom:1px solid #f1f5f9; display:flex; align-items:center; justify-content:space-between; }
    .card-title { font-size:0.875rem; font-weight:700; color:#0f172a; }
    .count-badge { background:#f1f5f9; color:#64748b; font-size:0.72rem; font-weight:600; padding:3px 9px; border-radius:99px; }
    table { width:100%; border-collapse:collapse; }
    th { padding:11px 16px; text-align:left; font-size:0.72rem; font-weight:600; color:#64748b; text-transform:uppercase; letter-spacing:.06em; border-bottom:1px solid #f1f5f9; background:#fafbfc; white-space:nowrap; }
    td { padding:13px 16px; font-size:0.845rem; color:#334155; border-bottom:1px solid #f8fafc; vertical-align:middle; }
    tr:last-child td { border-bottom:none; }
    tr:hover td { background:#fafbff; }
    .user-avatar { width:34px; height:34px; border-radius:50%; display:inline-flex; align-items:center; justify-content:center; font-size:0.85rem; font-weight:700; color:#fff; background:linear-gradient(135deg,#f97316,#ea580c); flex-shrink:0; }
    .user-name { font-weight:600; color:#0f172a; }
    .user-email { font-size:0.77rem; color:#94a3b8; margin-top:1px; }
    .role-badge { display:inline-flex; align-items:center; gap:5px; padding:3px 10px; border-radius:99px; font-size:0.72rem; font-weight:600; }
    .org-pill { display:inline-flex; align-items:center; gap:4px; padding:2px 8px; background:#eff6ff; color:#2563eb; border-radius:6px; font-size:0.73rem; font-weight:500; margin:2px; }
    .badge-green { background:#dcfce7; color:#16a34a; }
    .badge-red { background:#fee2e2; color:#dc2626; }
    .action-btn { display:inline-flex; align-items:center; gap:4px; padding:5px 11px; border-radius:7px; font-size:0.78rem; font-weight:500; text-decoration:none; cursor:pointer; border:none; transition:all .15s; }
    .btn-edit { background:#eff6ff; color:#2563eb; }
    .btn-edit:hover { background:#dbeafe; }
    .btn-del { background:#fff1f2; color:#e11d48; }
    .btn-del:hover { background:#ffe4e6; }
    .alert-success { display:flex; align-items:center; gap:10px; padding:12px 16px; background:#f0fdf4; border:1px solid #bbf7d0; border-radius:10px; margin-bottom:18px; font-size:0.83rem; color:#15803d; }
    .empty-state { padding:56px 20px; text-align:center; color:#94a3b8; }
</style>

<div class="page-header">
    <div>
        <h2 class="page-title">Manajemen User</h2>
        <p class="page-sub">Kelola akun dan hak akses pengguna</p>
    </div>
    <a href="{{ route('users.create') }}" class="btn-primary">
        <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg>
        Tambah User
    </a>
</div>

@if(session('success'))
<div class="alert-success">
    <svg width="16" height="16" fill="#16a34a" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
    {{ session('success') }}
</div>
@endif

{{-- Filter --}}
<form method="GET" action="{{ route('users.index') }}" class="filter-bar">
    <div class="search-wrap">
        <svg width="15" height="15" fill="none" stroke="#94a3b8" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama atau email..." class="fi fi-search"
            onfocus="this.style.borderColor='#f97316'" onblur="this.style.borderColor='#e2e8f0'">
    </div>

    <select name="role_id" class="fi" style="min-width:150px;"
        onfocus="this.style.borderColor='#f97316'" onblur="this.style.borderColor='#e2e8f0'">
        <option value="">Semua Role</option>
        @foreach($roles as $role)
            <option value="{{ $role->id }}" {{ request('role_id') == $role->id ? 'selected' : '' }}>{{ $role->name }}</option>
        @endforeach
    </select>

    @if($organizations->count() > 1)
    <select name="organization_id" class="fi" style="min-width:160px;"
        onfocus="this.style.borderColor='#f97316'" onblur="this.style.borderColor='#e2e8f0'">
        <option value="">Semua Organisasi</option>
        @foreach($organizations as $org)
            <option value="{{ $org->id }}" {{ request('organization_id') == $org->id ? 'selected' : '' }}>{{ $org->name }}</option>
        @endforeach
    </select>
    @endif

    <select name="status" class="fi" style="min-width:130px;"
        onfocus="this.style.borderColor='#f97316'" onblur="this.style.borderColor='#e2e8f0'">
        <option value="">Semua Status</option>
        <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Aktif</option>
        <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Nonaktif</option>
    </select>

    <button type="submit" class="btn-filter">Cari</button>
    @if(request()->hasAny(['search','role_id','organization_id','status']))
        <a href="{{ route('users.index') }}" class="btn-reset">Reset</a>
    @endif
</form>

<div class="card">
    <div class="card-header">
        <span class="card-title">Daftar User</span>
        <span class="count-badge">{{ $users->count() }} user</span>
    </div>
    @if($users->isEmpty())
        <div class="empty-state">
            <svg width="40" height="40" fill="none" stroke="#cbd5e1" stroke-width="1.5" viewBox="0 0 24 24" style="margin:0 auto 10px;display:block;"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/></svg>
            <p style="font-size:0.83rem;margin:0;">Tidak ada user ditemukan.</p>
        </div>
    @else
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>User</th>
                <th>Role</th>
                <th>Organisasi</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @foreach($users as $i => $user)
            <tr>
                <td style="color:#94a3b8;font-size:0.78rem;">{{ $i + 1 }}</td>
                <td>
                    <div style="display:flex;align-items:center;gap:10px;">
                        <div class="user-avatar">{{ strtoupper(substr($user->name, 0, 1)) }}</div>
                        <div>
                            <div class="user-name">{{ $user->name }}</div>
                            <div class="user-email">{{ $user->email }}</div>
                        </div>
                    </div>
                </td>
                <td>
                    @if($user->role)
                        <span class="role-badge" style="background:{{ $user->role->color ? $user->role->color.'20' : '#f1f5f9' }}; color:{{ $user->role->color ?? '#64748b' }};">
                            {{ $user->role->name }}
                        </span>
                    @else
                        <span style="color:#cbd5e1;font-size:0.8rem;">—</span>
                    @endif
                </td>
                <td>
                    @php $orgs = $user->organizationRoles->filter(fn($r) => $r->organization)->unique('organization_id'); @endphp
                    @if($orgs->isEmpty())
                        <span style="color:#cbd5e1;font-size:0.8rem;">{{ $user->role?->slug === 'superadmin' ? 'Semua organisasi' : '—' }}</span>
                    @else
                        <div style="display:flex;flex-wrap:wrap;gap:2px;">
                            @foreach($orgs as $uor)
                                <span class="org-pill">{{ $uor->organization->name }}</span>
                            @endforeach
                        </div>
                    @endif
                </td>
                <td>
                    @if($user->is_active)
                        <span class="role-badge badge-green">Aktif</span>
                    @else
                        <span class="role-badge badge-red">Nonaktif</span>
                    @endif
                </td>
                <td>
                    <div style="display:flex;gap:6px;">
                        <a href="{{ route('users.edit', $user) }}" class="action-btn btn-edit">Edit</a>
                        <form id="del-user-{{ $user->id }}" method="POST" action="{{ route('users.destroy', $user) }}">
                            @csrf @method('DELETE')
                        </form>
                        <button type="button" class="action-btn btn-del"
                            onclick="confirmDelete('del-user-{{ $user->id }}', '{{ addslashes($user->name) }}')">
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
