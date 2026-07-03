<x-layouts.app title="Manajemen User">

<div class="flex items-center justify-between mb-5">
    <div>
        <h2 class="text-lg font-bold text-slate-900 m-0 mb-0.5">Manajemen User</h2>
        <p class="text-xs text-slate-400 m-0">Kelola akun dan hak akses pengguna</p>
    </div>
    <a href="{{ route('users.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-gradient-to-br from-orange-400 to-orange-500 text-white text-sm font-semibold shadow-sm hover:-translate-y-px transition-all no-underline">
        <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg>
        Tambah User
    </a>
</div>

@if(session('success'))
<div class="flex items-center gap-2.5 px-4 py-3 bg-green-50 border border-green-200 rounded-xl mb-4 text-sm text-green-700">
    <svg width="16" height="16" fill="#16a34a" viewBox="0 0 20 20" class="shrink-0"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
    {{ session('success') }}
</div>
@endif

{{-- Filter --}}
<form method="GET" action="{{ route('users.index') }}" class="flex gap-2.5 flex-wrap items-center mb-4">
    <div class="relative flex-1 min-w-[200px]">
        <svg width="15" height="15" fill="none" stroke="#94a3b8" stroke-width="2" viewBox="0 0 24 24" class="absolute left-[11px] top-1/2 -translate-y-1/2 pointer-events-none"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama atau email..."
            class="w-full pl-[34px] px-3 py-2 border border-slate-200 rounded-xl text-sm text-slate-800 bg-white outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100 transition-colors">
    </div>

    <select name="role_id" class="px-3 py-2 border border-slate-200 rounded-xl text-sm text-slate-800 bg-white outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100 transition-colors min-w-[150px]">
        <option value="">Semua Role</option>
        @foreach($roles as $role)
            <option value="{{ $role->id }}" {{ request('role_id') == $role->id ? 'selected' : '' }}>{{ $role->name }}</option>
        @endforeach
    </select>

    @if($organizations->count() > 1)
    <select name="organization_id" class="px-3 py-2 border border-slate-200 rounded-xl text-sm text-slate-800 bg-white outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100 transition-colors min-w-[160px]">
        <option value="">Semua Organisasi</option>
        @foreach($organizations as $org)
            <option value="{{ $org->id }}" {{ request('organization_id') == $org->id ? 'selected' : '' }}>{{ $org->name }}</option>
        @endforeach
    </select>
    @endif

    <select name="status" class="px-3 py-2 border border-slate-200 rounded-xl text-sm text-slate-800 bg-white outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100 transition-colors min-w-[130px]">
        <option value="">Semua Status</option>
        <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Aktif</option>
        <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Nonaktif</option>
    </select>

    <button type="submit" class="px-4 py-2 rounded-xl border-0 cursor-pointer text-sm font-semibold bg-gradient-to-br from-orange-400 to-orange-500 text-white">Cari</button>
    @if(request()->hasAny(['search','role_id','organization_id','status']))
        <a href="{{ route('users.index') }}" class="px-3.5 py-2 rounded-xl border border-slate-200 text-sm text-slate-500 no-underline bg-white">Reset</a>
    @endif
</form>

<div class="bg-white rounded-xl border border-slate-100 shadow-sm overflow-hidden">
    <div class="px-5 py-3.5 border-b border-slate-100 flex items-center justify-between">
        <span class="text-sm font-bold text-slate-900">Daftar User</span>
        <span class="text-[11px] font-semibold text-slate-500 bg-slate-100 px-2.5 py-0.5 rounded-full">{{ $users->count() }} user</span>
    </div>
    @if($users->isEmpty())
        <div class="py-14 px-5 text-center text-slate-400">
            <svg width="40" height="40" fill="none" stroke="#cbd5e1" stroke-width="1.5" viewBox="0 0 24 24" class="mx-auto mb-2.5 block"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/></svg>
            <p class="text-sm m-0">Tidak ada user ditemukan.</p>
        </div>
    @else
    <table class="w-full border-collapse">
        <thead>
            <tr class="bg-slate-50 border-b border-slate-100">
                <th class="px-4 py-3 text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wide w-8">#</th>
                <th class="px-4 py-3 text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wide">User</th>
                <th class="px-4 py-3 text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wide">Role</th>
                <th class="px-4 py-3 text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wide">Organisasi</th>
                <th class="px-4 py-3 text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wide">Status</th>
                <th class="px-4 py-3 text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wide">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @foreach($users as $i => $user)
            <tr class="border-b border-slate-50 hover:bg-slate-50 transition-colors last:border-b-0">
                <td class="px-4 py-3 text-xs text-slate-400 align-middle">{{ $i + 1 }}</td>
                <td class="px-4 py-3 text-sm text-slate-600 align-middle">
                    <div class="flex items-center gap-2.5">
                        <div class="w-[34px] h-[34px] rounded-full shrink-0 inline-flex items-center justify-center text-sm font-bold text-white" style="background:linear-gradient(135deg,#f97316,#ea580c)">
                            {{ strtoupper(substr($user->name, 0, 1)) }}
                        </div>
                        <div>
                            <div class="text-sm font-semibold text-slate-900">{{ $user->name }}</div>
                            <div class="text-xs text-slate-400 mt-px">{{ $user->email }}</div>
                        </div>
                    </div>
                </td>
                <td class="px-4 py-3 text-sm text-slate-600 align-middle">
                    @if($user->role)
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-semibold"
                            style="background:{{ $user->role->color ? $user->role->color.'20' : '#f1f5f9' }};color:{{ $user->role->color ?? '#64748b' }};">
                            {{ $user->role->name }}
                        </span>
                    @else
                        <span class="text-slate-300 text-sm">—</span>
                    @endif
                </td>
                <td class="px-4 py-3 text-sm text-slate-600 align-middle">
                    @php $orgs = $user->organizationRoles->filter(fn($r) => $r->organization)->unique('organization_id'); @endphp
                    @if($orgs->isEmpty())
                        <span class="text-slate-300 text-sm">{{ $user->role?->slug === 'superadmin' ? 'Semua organisasi' : '—' }}</span>
                    @else
                        <div class="flex flex-wrap gap-1">
                            @foreach($orgs as $uor)
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-blue-50 text-blue-700 rounded text-xs font-medium">{{ $uor->organization->name }}</span>
                            @endforeach
                        </div>
                    @endif
                </td>
                <td class="px-4 py-3 text-sm text-slate-600 align-middle">
                    @if($user->is_active)
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-green-100 text-green-700">Aktif</span>
                    @else
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-red-100 text-red-600">Nonaktif</span>
                    @endif
                </td>
                <td class="px-4 py-3 text-sm text-slate-600 align-middle">
                    <div class="flex gap-1.5">
                        <a href="{{ route('users.edit', $user) }}" class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-xs font-medium bg-blue-50 text-blue-600 hover:bg-blue-100 transition-colors no-underline">Edit</a>
                        <form id="del-user-{{ $user->id }}" method="POST" action="{{ route('users.destroy', $user) }}">
                            @csrf @method('DELETE')
                        </form>
                        <button type="button" class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-xs font-medium bg-red-50 text-red-600 hover:bg-red-100 transition-colors border-0 cursor-pointer"
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
