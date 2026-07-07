<x-layouts.app title="Setting Permission">

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

{{-- Tabs per role --}}
<div x-data="{ activeRole: '{{ $roles->first()?->slug }}' }">

    {{-- Tab headers --}}
    <div class="flex gap-1 mb-4 bg-white rounded-xl p-1 shadow-sm border border-slate-100 w-fit">
        @foreach($roles as $role)
        <button
            @click="activeRole = '{{ $role->slug }}'"
            :class="activeRole === '{{ $role->slug }}' ? 'bg-orange-500 text-white shadow-sm' : 'text-slate-500 hover:text-slate-700'"
            class="px-4 py-2 rounded-lg text-sm font-medium transition-all cursor-pointer border-0">
            {{ $role->name }}
        </button>
        @endforeach
    </div>

    {{-- Tab content per role --}}
    @foreach($roles as $role)
    <div x-show="activeRole === '{{ $role->slug }}'" x-cloak>
        <form method="POST" action="{{ route('role-permissions.update', $role) }}">
            @csrf @method('PUT')

            <div class="grid gap-4">
                @foreach($permissions as $group => $perms)
                @php
                    $groupLabels = [
                        'umum'      => 'Umum',
                        'master'    => 'Master Data',
                        'keuangan'  => 'Keuangan',
                        'akunting'  => 'Akunting',
                        'laporan'   => 'Laporan',
                        'sistem'    => 'Sistem',
                    ];
                @endphp
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
                    <div class="px-5 py-3 grid grid-cols-2 gap-3" id="{{ $role->slug }}-{{ $group }}">
                        @foreach($perms as $perm)
                        <label class="flex items-center gap-3 cursor-pointer group">
                            <input type="checkbox"
                                name="permissions[]"
                                value="{{ $perm->slug }}"
                                {{ $role->permissions->contains('slug', $perm->slug) ? 'checked' : '' }}
                                class="w-4 h-4 rounded accent-orange-500 cursor-pointer">
                            <span class="text-sm text-slate-700 group-hover:text-slate-900 select-none">
                                {{ $perm->name }}
                                <span class="text-[10px] text-slate-400 font-mono ml-1">{{ $perm->slug }}</span>
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

</div>

<script>
function toggleGroup(btn, groupId) {
    const group = document.getElementById(groupId);
    const checkboxes = group.querySelectorAll('input[type=checkbox]');
    const allChecked = [...checkboxes].every(c => c.checked);
    checkboxes.forEach(c => c.checked = !allChecked);
    btn.textContent = allChecked ? 'Pilih Semua' : 'Hapus Semua';
}
</script>

</x-layouts.app>
