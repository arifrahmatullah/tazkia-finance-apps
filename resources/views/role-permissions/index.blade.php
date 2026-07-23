<x-layouts.app title="Setting Permission">

<style>
    .toggle-switch { position: relative; display: inline-block; width: 40px; height: 22px; flex-shrink: 0; }
    .toggle-switch input { opacity: 0; width: 0; height: 0; position: absolute; }
    .toggle-slider {
        position: absolute; inset: 0; cursor: pointer;
        background: #cbd5e1; border-radius: 999px; transition: background .15s;
    }
    .toggle-slider::before {
        content: ''; position: absolute; height: 16px; width: 16px; left: 3px; top: 3px;
        background: white; border-radius: 50%; transition: transform .15s;
        box-shadow: 0 1px 2px rgba(0,0,0,0.15);
    }
    .toggle-switch input:checked + .toggle-slider { background: #f97316; }
    .toggle-switch input:checked + .toggle-slider::before { transform: translateX(18px); }
    .role-panel { display: none; }
    .role-panel.active { display: block; }
</style>

<div class="flex items-center justify-between mb-5">
    <div>
        <h2 class="text-lg font-bold text-slate-900 m-0 mb-0.5">Setting Permission</h2>
        <p class="text-xs text-slate-400 m-0">Atur menu dan fitur yang dapat diakses per role</p>
    </div>
</div>

@if(session('success'))
<div class="flex items-center gap-2.5 px-4 py-3 bg-green-50 border border-green-200 rounded-xl mb-4 text-sm text-green-700">
    <svg width="16" height="16" fill="#16a34a" viewBox="0 0 20 20" class="shrink-0"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
    {{ session('success') }}
</div>
@endif

{{-- Info superadmin --}}
<div class="flex items-center gap-2.5 px-4 py-3 bg-blue-50 border border-blue-200 rounded-xl mb-5 text-sm text-blue-700">
    <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" class="shrink-0"><path d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
    Role <strong>Superadmin</strong> selalu memiliki akses ke semua menu secara otomatis.
</div>

{{-- Pilih role --}}
<div class="flex items-center gap-3 mb-5 bg-white rounded-xl p-3 shadow-sm border border-slate-100 w-fit">
    <label for="role-select" class="text-sm font-semibold text-slate-600">Atur permission untuk role:</label>
    <select id="role-select" onchange="switchRolePanel(this.value)"
        class="px-3 py-2 rounded-lg border border-slate-200 text-sm font-medium text-slate-800 outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100 min-w-[180px]">
        @foreach($roles as $role)
        <option value="{{ $role->slug }}">{{ $role->name }}</option>
        @endforeach
    </select>
</div>

@php
    $groupLabels = [
        'umum'      => 'Umum',
        'master'    => 'Master Data',
        'keuangan'  => 'Keuangan',
        'akunting'  => 'Akunting',
        'laporan'   => 'Laporan',
        'sistem'    => 'Sistem',
    ];

    $permDescriptions = [
        'menu.dashboard'           => 'Halaman ringkasan utama setelah login',
        'menu.organisasi'         => 'Kelola data organisasi/unit kerja',
        'menu.departemen'         => 'Kelola data departemen',
        'menu.jabatan'            => 'Kelola data jabatan',
        'menu.karyawan'           => 'Kelola data karyawan',
        'menu.approval-settings'  => 'Atur alur & pemberi persetujuan pengajuan dana',
        'menu.periode-anggaran'   => 'Kelola periode/tahun anggaran',
        'menu.estimasi-pendapatan' => 'Kelola rencana & realisasi pendapatan',
        'menu.pagu-anggaran'      => 'Kelola alokasi/pagu anggaran per akun',
        'menu.program-kerja'      => 'Kelola program kerja & jadwalnya',
        'menu.pengajuan-dana'     => 'Ajukan & kelola pengajuan dana',
        'menu.inbox-approval'     => 'Menyetujui/menolak pengajuan dana yang masuk',
        'menu.pencairan-dana'     => 'Proses pencairan dana yang sudah disetujui',
        'menu.jurnal-umum'        => 'Kelola jurnal umum & posting jurnal',
        'menu.coa'                => 'Kelola Chart of Accounts (daftar akun)',
        'menu.laporan'            => 'Akses seluruh laporan keuangan',
        'menu.users'              => 'Kelola akun pengguna aplikasi',
        'menu.role-permissions'   => 'Atur hak akses tiap role (halaman ini)',
        'menu.audit-logs'         => 'Lihat riwayat aktivitas seluruh pengguna',
    ];
@endphp

{{-- Panel per role --}}
@foreach($roles as $role)
<div class="role-panel {{ $loop->first ? 'active' : '' }}" id="role-panel-{{ $role->slug }}">
    <form method="POST" action="{{ route('role-permissions.update', $role) }}">
        @csrf @method('PUT')

        <div class="grid gap-4">
            @foreach($permissions as $group => $perms)
            <div class="bg-white rounded-xl shadow-sm border border-slate-100 overflow-hidden">
                <div class="px-5 py-3 bg-slate-50 border-b border-slate-100 flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-500 uppercase tracking-wide">
                        {{ $groupLabels[$group] ?? ucfirst($group) }}
                    </span>
                    <button type="button"
                        onclick="toggleGroup(this, '{{ $role->slug }}-{{ $group }}')"
                        class="text-xs text-orange-500 hover:text-orange-600 bg-transparent border-0 cursor-pointer font-medium">
                        Pilih Semua
                    </button>
                </div>
                <div class="divide-y divide-slate-100" id="{{ $role->slug }}-{{ $group }}">
                    @foreach($perms as $perm)
                    <label class="flex items-center justify-between gap-4 px-5 py-3 cursor-pointer hover:bg-slate-50">
                        <span class="min-w-0">
                            <span class="block text-sm font-medium text-slate-800">{{ $perm->name }}</span>
                            <span class="block text-xs text-slate-400 mt-0.5">{{ $permDescriptions[$perm->slug] ?? '' }}</span>
                        </span>
                        <span class="toggle-switch" title="{{ $perm->slug }}">
                            <input type="checkbox"
                                name="permissions[]"
                                value="{{ $perm->slug }}"
                                {{ $role->permissions->contains('slug', $perm->slug) ? 'checked' : '' }}>
                            <span class="toggle-slider"></span>
                        </span>
                    </label>
                    @endforeach
                </div>
            </div>
            @endforeach
        </div>

        <div class="mt-4 flex justify-end">
            <button type="submit"
                class="px-6 py-2.5 rounded-xl text-sm font-semibold bg-gradient-to-br from-orange-400 to-orange-500 text-white border-0 cursor-pointer shadow-sm hover:-translate-y-px transition-all">
                Simpan Permission — {{ $role->name }}
            </button>
        </div>
    </form>
</div>
@endforeach

<script>
function switchRolePanel(slug) {
    document.querySelectorAll('.role-panel').forEach(panel => {
        panel.classList.toggle('active', panel.id === 'role-panel-' + slug);
    });
}

function toggleGroup(btn, groupId) {
    const group = document.getElementById(groupId);
    const checkboxes = group.querySelectorAll('input[type=checkbox]');
    const allChecked = [...checkboxes].every(c => c.checked);
    checkboxes.forEach(c => c.checked = !allChecked);
    btn.textContent = allChecked ? 'Pilih Semua' : 'Hapus Semua';
}
</script>

</x-layouts.app>
