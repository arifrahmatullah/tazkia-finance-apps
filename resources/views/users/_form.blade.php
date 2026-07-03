@php $usr = $user ?? null; @endphp

<style>
.toggle input:checked + .toggle-slider { background:#f97316; }
.toggle input:checked + .toggle-slider::before { transform:translateX(20px); }
.toggle-slider::before { content:''; position:absolute; width:16px; height:16px; left:3px; top:3px; background:#fff; border-radius:50%; transition:.2s; }
</style>

{{-- Info Akun --}}
<div class="mb-6">
    <div class="text-[11px] font-bold text-slate-400 uppercase tracking-widest mb-3.5 pb-2 border-b border-slate-100">Informasi Akun</div>
    <div class="grid grid-cols-2 gap-4">
        <div class="flex flex-col gap-1.5">
            <label class="text-xs font-semibold text-slate-600">Nama Lengkap <span class="text-red-500 ml-0.5">*</span></label>
            <input type="text" name="name" class="w-full px-3 py-2.5 border border-slate-200 rounded-xl text-sm text-slate-800 bg-white outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100 transition-colors"
                value="{{ old('name', $usr?->name) }}" placeholder="Nama user">
            @error('name') <span class="text-xs text-red-500 mt-0.5">{{ $message }}</span> @enderror
        </div>
        <div class="flex flex-col gap-1.5">
            <label class="text-xs font-semibold text-slate-600">Email <span class="text-red-500 ml-0.5">*</span></label>
            <input type="email" name="email" class="w-full px-3 py-2.5 border border-slate-200 rounded-xl text-sm text-slate-800 bg-white outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100 transition-colors"
                value="{{ old('email', $usr?->email) }}" placeholder="email@contoh.com">
            @error('email') <span class="text-xs text-red-500 mt-0.5">{{ $message }}</span> @enderror
        </div>
        <div class="flex flex-col gap-1.5">
            <label class="text-xs font-semibold text-slate-600">Password {{ $usr ? '' : '*' }}</label>
            <input type="password" name="password" class="w-full px-3 py-2.5 border border-slate-200 rounded-xl text-sm text-slate-800 bg-white outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100 transition-colors"
                placeholder="{{ $usr ? 'Kosongkan jika tidak diubah' : 'Min. 8 karakter' }}">
            @error('password') <span class="text-xs text-red-500 mt-0.5">{{ $message }}</span> @enderror
        </div>
        <div class="flex flex-col gap-1.5">
            <label class="text-xs font-semibold text-slate-600">Konfirmasi Password {{ $usr ? '' : '*' }}</label>
            <input type="password" name="password_confirmation" class="w-full px-3 py-2.5 border border-slate-200 rounded-xl text-sm text-slate-800 bg-white outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100 transition-colors"
                placeholder="Ulangi password">
        </div>
    </div>
</div>

{{-- Role --}}
<div class="mb-6">
    <div class="text-[11px] font-bold text-slate-400 uppercase tracking-widest mb-3.5 pb-2 border-b border-slate-100">Role</div>
    <div class="grid grid-cols-[repeat(auto-fill,minmax(170px,1fr))] gap-2.5" id="roleGrid">
        @foreach($roles as $role)
        <label class="relative flex flex-col gap-1 px-3.5 py-3 border rounded-xl cursor-pointer transition-all hover:border-orange-400 hover:bg-orange-50/50 has-[:checked]:border-orange-400 has-[:checked]:bg-orange-50">
            <input type="radio" name="role_id" id="role-{{ $role->id }}" value="{{ $role->id }}"
                {{ old('role_id', $usr?->role_id) == $role->id ? 'checked' : '' }}
                onchange="handleRoleChange(this)" class="sr-only">
            <span class="text-sm font-semibold text-slate-800 flex items-center gap-1.5">
                <span class="inline-block w-2 h-2 rounded-full shrink-0" style="background:{{ $role->color ?? '#64748b' }};"></span>
                {{ $role->name }}
            </span>
            @if($role->description)
                <span class="text-xs text-slate-400 leading-snug">{{ $role->description }}</span>
            @endif
        </label>
        @endforeach
    </div>
    @error('role_id') <span class="text-xs text-red-500 mt-1.5 block">{{ $message }}</span> @enderror
</div>

{{-- Organisasi --}}
<div class="mb-6" id="org-section">
    <div class="text-[11px] font-bold text-slate-400 uppercase tracking-widest mb-3.5 pb-2 border-b border-slate-100">Akses Organisasi</div>
    @if($organizations->count() === 0)
        <p class="text-sm text-slate-400">Tidak ada organisasi tersedia.</p>
    @else
    <div class="grid grid-cols-[repeat(auto-fill,minmax(200px,1fr))] gap-2.5">
        @foreach($organizations as $org)
        @php $checked = in_array($org->id, old('organization_ids', $assignedOrgIds ?? [])); @endphp
        <label class="flex items-center gap-2.5 px-3.5 py-2.5 border rounded-xl cursor-pointer transition-all hover:border-orange-400 hover:bg-orange-50/50 {{ $checked ? 'border-orange-400 bg-orange-50' : 'border-slate-200' }}">
            <input type="checkbox" name="organization_ids[]" value="{{ $org->id }}"
                {{ $checked ? 'checked' : '' }}
                onchange="toggleOrgLabel(this)"
                class="w-4 h-4 accent-orange-500 shrink-0">
            <span class="text-sm font-medium text-slate-700">{{ $org->name }}</span>
        </label>
        @endforeach
    </div>
    @error('organization_ids') <span class="text-xs text-red-500 mt-1.5 block">{{ $message }}</span> @enderror
    @endif
</div>

@if($usr)
<div class="mb-2">
    <div class="text-[11px] font-bold text-slate-400 uppercase tracking-widest mb-3.5 pb-2 border-b border-slate-100">Status</div>
    <div class="flex items-center gap-3 px-3.5 py-2.5 border border-slate-200 rounded-xl w-fit">
        <label class="toggle relative w-[42px] h-[22px]">
            <input type="hidden" name="is_active" value="0">
            <input type="checkbox" name="is_active" value="1" {{ old('is_active', $usr?->is_active) ? 'checked' : '' }}
                class="opacity-0 w-0 h-0 absolute">
            <span class="toggle-slider absolute inset-0 bg-slate-200 rounded-full cursor-pointer transition-[.2s]"></span>
        </label>
        <span class="text-sm text-slate-700 font-medium">User Aktif</span>
    </div>
</div>
@endif

<script>
const superadminRoleId = '{{ $roles->firstWhere('slug', 'superadmin')?->id }}';

function handleRoleChange(radio) {
    const orgSection = document.getElementById('org-section');
    if (radio.value === superadminRoleId) {
        orgSection.style.opacity = '0.4';
        orgSection.style.pointerEvents = 'none';
    } else {
        orgSection.style.opacity = '1';
        orgSection.style.pointerEvents = '';
    }
}

function toggleOrgLabel(cb) {
    const label = cb.closest('label');
    if (cb.checked) {
        label.classList.add('border-orange-400', 'bg-orange-50');
        label.classList.remove('border-slate-200');
    } else {
        label.classList.remove('border-orange-400', 'bg-orange-50');
        label.classList.add('border-slate-200');
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const checked = document.querySelector('input[name=role_id]:checked');
    if (checked) handleRoleChange(checked);
});
</script>
