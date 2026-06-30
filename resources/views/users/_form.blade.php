@php $usr = $user ?? null; @endphp

<style>
    .form-section { margin-bottom:28px; }
    .section-title { font-size:0.78rem; font-weight:700; color:#94a3b8; text-transform:uppercase; letter-spacing:.08em; margin:0 0 14px; padding-bottom:8px; border-bottom:1px solid #f1f5f9; }
    .form-grid { display:grid; grid-template-columns:1fr 1fr; gap:18px; }
    .form-group { display:flex; flex-direction:column; gap:6px; }
    .form-group.full { grid-column:1/-1; }
    .form-label { font-size:0.8rem; font-weight:600; color:#374151; }
    .form-label .req { color:#ef4444; margin-left:2px; }
    .form-input { padding:9px 13px; border:1.5px solid #e2e8f0; border-radius:9px; font-size:0.865rem; color:#1e293b; background:#fff; outline:none; transition:border-color .15s; width:100%; }
    .form-input:focus { border-color:#f97316; }
    .form-error { font-size:0.77rem; color:#dc2626; margin-top:2px; }
    .form-hint { font-size:0.75rem; color:#94a3b8; margin-top:2px; }
    /* Org checkbox grid */
    .org-grid { display:grid; grid-template-columns:repeat(auto-fill, minmax(200px, 1fr)); gap:10px; }
    .org-check-label { display:flex; align-items:center; gap:10px; padding:10px 14px; border:1.5px solid #e2e8f0; border-radius:9px; cursor:pointer; transition:all .15s; }
    .org-check-label:hover { border-color:#f97316; background:#fff7ed; }
    .org-check-label input[type=checkbox] { accent-color:#f97316; width:15px; height:15px; flex-shrink:0; }
    .org-check-label.checked { border-color:#f97316; background:#fff7ed; }
    .org-check-name { font-size:0.845rem; font-weight:500; color:#374151; }
    /* Toggle */
    .toggle-wrap { display:flex; align-items:center; gap:12px; padding:10px 14px; border:1.5px solid #e2e8f0; border-radius:9px; width:fit-content; }
    .toggle { position:relative; width:42px; height:22px; }
    .toggle input { opacity:0; width:0; height:0; }
    .toggle-slider { position:absolute; inset:0; background:#e2e8f0; border-radius:99px; cursor:pointer; transition:.2s; }
    .toggle-slider::before { content:''; position:absolute; width:16px; height:16px; left:3px; top:3px; background:#fff; border-radius:50%; transition:.2s; }
    .toggle input:checked + .toggle-slider { background:#f97316; }
    .toggle input:checked + .toggle-slider::before { transform:translateX(20px); }
    /* Role card select */
    .role-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(170px,1fr)); gap:10px; }
    .role-card { position:relative; }
    .role-card input[type=radio] { position:absolute; opacity:0; }
    .role-card-label { display:flex; flex-direction:column; gap:4px; padding:12px 14px; border:1.5px solid #e2e8f0; border-radius:10px; cursor:pointer; transition:all .15s; }
    .role-card-label:hover { border-color:#f97316; background:#fff7ed; }
    .role-card input:checked + .role-card-label { border-color:#f97316; background:#fff7ed; box-shadow:0 0 0 3px rgba(249,115,22,.12); }
    .role-card-name { font-size:0.855rem; font-weight:600; color:#1e293b; }
    .role-card-desc { font-size:0.73rem; color:#94a3b8; line-height:1.4; }
    .role-dot { width:8px; height:8px; border-radius:50%; display:inline-block; margin-right:6px; }
    #org-section { transition:all .2s; }
</style>

{{-- Info Akun --}}
<div class="form-section">
    <p class="section-title">Informasi Akun</p>
    <div class="form-grid">
        <div class="form-group">
            <label class="form-label">Nama Lengkap <span class="req">*</span></label>
            <input type="text" name="name" class="form-input" value="{{ old('name', $usr?->name) }}"
                placeholder="Nama user"
                onfocus="this.style.borderColor='#f97316'" onblur="this.style.borderColor='#e2e8f0'">
            @error('name') <span class="form-error">{{ $message }}</span> @enderror
        </div>
        <div class="form-group">
            <label class="form-label">Email <span class="req">*</span></label>
            <input type="email" name="email" class="form-input" value="{{ old('email', $usr?->email) }}"
                placeholder="email@contoh.com"
                onfocus="this.style.borderColor='#f97316'" onblur="this.style.borderColor='#e2e8f0'">
            @error('email') <span class="form-error">{{ $message }}</span> @enderror
        </div>
        <div class="form-group">
            <label class="form-label">Password {{ $usr ? '' : '*' }}</label>
            <input type="password" name="password" class="form-input"
                placeholder="{{ $usr ? 'Kosongkan jika tidak diubah' : 'Min. 8 karakter' }}"
                onfocus="this.style.borderColor='#f97316'" onblur="this.style.borderColor='#e2e8f0'">
            @error('password') <span class="form-error">{{ $message }}</span> @enderror
        </div>
        <div class="form-group">
            <label class="form-label">Konfirmasi Password {{ $usr ? '' : '*' }}</label>
            <input type="password" name="password_confirmation" class="form-input"
                placeholder="Ulangi password"
                onfocus="this.style.borderColor='#f97316'" onblur="this.style.borderColor='#e2e8f0'">
        </div>
    </div>
</div>

{{-- Role --}}
<div class="form-section">
    <p class="section-title">Role</p>
    <div class="role-grid" id="roleGrid">
        @foreach($roles as $role)
        <div class="role-card">
            <input type="radio" name="role_id" id="role-{{ $role->id }}" value="{{ $role->id }}"
                {{ old('role_id', $usr?->role_id) == $role->id ? 'checked' : '' }}
                onchange="handleRoleChange(this)">
            <label class="role-card-label" for="role-{{ $role->id }}">
                <span class="role-card-name">
                    <span class="role-dot" style="background:{{ $role->color ?? '#64748b' }};"></span>
                    {{ $role->name }}
                </span>
                @if($role->description)
                    <span class="role-card-desc">{{ $role->description }}</span>
                @endif
            </label>
        </div>
        @endforeach
    </div>
    @error('role_id') <span class="form-error" style="margin-top:6px;display:block;">{{ $message }}</span> @enderror
</div>

{{-- Organisasi (disembunyikan kalau superadmin) --}}
<div class="form-section" id="org-section">
    <p class="section-title">Akses Organisasi</p>
    @if($organizations->count() === 0)
        <p style="font-size:0.845rem;color:#94a3b8;">Tidak ada organisasi tersedia.</p>
    @else
    <div class="org-grid">
        @foreach($organizations as $org)
        @php $checked = in_array($org->id, old('organization_ids', $assignedOrgIds ?? [])); @endphp
        <label class="org-check-label {{ $checked ? 'checked' : '' }}" id="org-label-{{ $org->id }}">
            <input type="checkbox" name="organization_ids[]" value="{{ $org->id }}"
                {{ $checked ? 'checked' : '' }}
                onchange="this.parentElement.classList.toggle('checked', this.checked)">
            <span class="org-check-name">{{ $org->name }}</span>
        </label>
        @endforeach
    </div>
    @error('organization_ids') <span class="form-error" style="margin-top:6px;display:block;">{{ $message }}</span> @enderror
    @endif
</div>

@if($usr)
<div class="form-section">
    <p class="section-title">Status</p>
    <div class="toggle-wrap">
        <label class="toggle">
            <input type="hidden" name="is_active" value="0">
            <input type="checkbox" name="is_active" value="1" {{ old('is_active', $usr?->is_active) ? 'checked' : '' }}>
            <span class="toggle-slider"></span>
        </label>
        <span style="font-size:0.855rem;color:#374151;">User Aktif</span>
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

// Init on load
document.addEventListener('DOMContentLoaded', function() {
    const checked = document.querySelector('input[name=role_id]:checked');
    if (checked) handleRoleChange(checked);
});
</script>
