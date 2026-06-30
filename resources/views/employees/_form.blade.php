@php $emp = $employee ?? null; @endphp

<style>
    .form-grid { display:grid; grid-template-columns:1fr 1fr; gap:20px; }
    .form-grid-3 { display:grid; grid-template-columns:1fr 1fr 1fr; gap:20px; }
    .form-group { display:flex; flex-direction:column; gap:6px; }
    .form-group.full { grid-column:1/-1; }
    .form-label { font-size:0.8rem; font-weight:600; color:#374151; }
    .form-label .req { color:#ef4444; margin-left:2px; }
    .form-input { padding:9px 13px; border:1.5px solid #e2e8f0; border-radius:9px; font-size:0.865rem; color:#1e293b; background:#fff; outline:none; transition:border-color .15s; width:100%; }
    .form-input:focus { border-color:#f97316; }
    .form-error { font-size:0.77rem; color:#dc2626; margin-top:2px; }
    .section-title { font-size:0.78rem; font-weight:700; color:#94a3b8; text-transform:uppercase; letter-spacing:.08em; margin:24px 0 14px; padding-bottom:6px; border-bottom:1px solid #f1f5f9; }
    .gender-group { display:flex; gap:16px; }
    .gender-label { display:flex; align-items:center; gap:8px; cursor:pointer; font-size:0.855rem; color:#374151; padding:9px 16px; border:1.5px solid #e2e8f0; border-radius:9px; transition:all .15s; }
    .gender-label:hover { border-color:#f97316; background:#fff7ed; }
    .gender-label input[type=radio] { accent-color:#f97316; width:15px; height:15px; }
    .gender-label.selected { border-color:#f97316; background:#fff7ed; color:#c2410c; font-weight:600; }
    .toggle-group { display:flex; align-items:center; gap:12px; padding:10px 14px; border:1.5px solid #e2e8f0; border-radius:9px; }
    .toggle-label { font-size:0.855rem; color:#374151; }
    .toggle { position:relative; width:42px; height:22px; }
    .toggle input { opacity:0; width:0; height:0; }
    .toggle-slider { position:absolute; inset:0; background:#e2e8f0; border-radius:99px; cursor:pointer; transition:.2s; }
    .toggle-slider::before { content:''; position:absolute; width:16px; height:16px; left:3px; top:3px; background:#fff; border-radius:50%; transition:.2s; }
    .toggle input:checked + .toggle-slider { background:#f97316; }
    .toggle input:checked + .toggle-slider::before { transform:translateX(20px); }
    .form-hint { font-size:0.75rem; color:#94a3b8; margin-top:2px; }
</style>

<p class="section-title">Informasi Pribadi</p>
<div class="form-grid">
    <div class="form-group">
        <label class="form-label">NIK <span class="req">*</span></label>
        <input type="text" name="nik" class="form-input" value="{{ old('nik', $emp?->nik) }}"
            placeholder="Nomor Induk Karyawan"
            inputmode="numeric" maxlength="16"
            onkeypress="return /[0-9]/.test(event.key)"
            oninput="this.value=this.value.replace(/[^0-9]/g,'').slice(0,16)"
            onfocus="this.style.borderColor='#f97316'" onblur="this.style.borderColor='#e2e8f0'">
        @error('nik') <span class="form-error">{{ $message }}</span> @enderror
    </div>
    <div class="form-group">
        <label class="form-label">Nama Lengkap <span class="req">*</span></label>
        <input type="text" name="name" class="form-input" value="{{ old('name', $emp?->name) }}"
            placeholder="Nama karyawan"
            onfocus="this.style.borderColor='#f97316'" onblur="this.style.borderColor='#e2e8f0'">
        @error('name') <span class="form-error">{{ $message }}</span> @enderror
    </div>
    <div class="form-group">
        <label class="form-label">Gelar</label>
        <input type="text" name="title" class="form-input" value="{{ old('title', $emp?->title) }}"
            placeholder="cth: S.T., M.Kom."
            onfocus="this.style.borderColor='#f97316'" onblur="this.style.borderColor='#e2e8f0'">
        @error('title') <span class="form-error">{{ $message }}</span> @enderror
    </div>
    <div class="form-group">
        <label class="form-label">Tanggal Lahir</label>
        <input type="date" name="birth_date" class="form-input"
            value="{{ old('birth_date', $emp?->birth_date?->format('Y-m-d')) }}"
            onfocus="this.style.borderColor='#f97316'" onblur="this.style.borderColor='#e2e8f0'">
        @error('birth_date') <span class="form-error">{{ $message }}</span> @enderror
    </div>
    <div class="form-group full">
        <label class="form-label">Jenis Kelamin</label>
        <div class="gender-group" id="genderGroup">
            <label class="gender-label {{ old('gender', $emp?->gender) === 'L' ? 'selected' : '' }}">
                <input type="radio" name="gender" value="L" {{ old('gender', $emp?->gender) === 'L' ? 'checked' : '' }} onchange="updateGender()"> Laki-laki
            </label>
            <label class="gender-label {{ old('gender', $emp?->gender) === 'P' ? 'selected' : '' }}">
                <input type="radio" name="gender" value="P" {{ old('gender', $emp?->gender) === 'P' ? 'checked' : '' }} onchange="updateGender()"> Perempuan
            </label>
        </div>
    </div>
</div>

<p class="section-title">Kontak & Identitas</p>
<div class="form-grid">
    <div class="form-group">
        <label class="form-label">Email</label>
        <input type="email" name="email" class="form-input" value="{{ old('email', $emp?->email) }}"
            placeholder="email@contoh.com"
            onfocus="this.style.borderColor='#f97316'" onblur="this.style.borderColor='#e2e8f0'">
        @error('email') <span class="form-error">{{ $message }}</span> @enderror
    </div>
    <div class="form-group">
        <label class="form-label">Nomor Telepon</label>
        <input type="text" name="phone" class="form-input" value="{{ old('phone', $emp?->phone) }}"
            placeholder="08xxxxxxxxxx"
            onfocus="this.style.borderColor='#f97316'" onblur="this.style.borderColor='#e2e8f0'">
        @error('phone') <span class="form-error">{{ $message }}</span> @enderror
    </div>
    <div class="form-group">
        <label class="form-label">NIDN</label>
        <input type="text" name="nidn" class="form-input" value="{{ old('nidn', $emp?->nidn) }}"
            placeholder="Nomor Induk Dosen Nasional"
            onfocus="this.style.borderColor='#f97316'" onblur="this.style.borderColor='#e2e8f0'">
        <span class="form-hint">Kosongkan jika bukan dosen</span>
        @error('nidn') <span class="form-error">{{ $message }}</span> @enderror
    </div>
    <div class="form-group">
        <label class="form-label">RFID</label>
        <input type="text" name="rfid" class="form-input" value="{{ old('rfid', $emp?->rfid) }}"
            placeholder="Nomor kartu RFID"
            onfocus="this.style.borderColor='#f97316'" onblur="this.style.borderColor='#e2e8f0'">
        @error('rfid') <span class="form-error">{{ $message }}</span> @enderror
    </div>
</div>

<p class="section-title">Organisasi</p>
<div class="form-grid">
    <div class="form-group">
        @if($organizations->count() === 1)
            @php $singleOrg = $organizations->first(); @endphp
            <label class="form-label">Organisasi</label>
            <div class="form-input" style="background:#f8fafc;color:#64748b;cursor:default;">{{ $singleOrg->name }}</div>
            <input type="hidden" name="organization_id" value="{{ $singleOrg->id }}">
        @else
            <label class="form-label">Organisasi <span class="req">*</span></label>
            <select name="organization_id" class="form-input"
                onfocus="this.style.borderColor='#f97316'" onblur="this.style.borderColor='#e2e8f0'">
                <option value="">-- Pilih Organisasi --</option>
                @foreach($organizations as $org)
                    <option value="{{ $org->id }}" {{ old('organization_id', $emp?->organization_id) == $org->id ? 'selected' : '' }}>
                        {{ $org->name }}
                    </option>
                @endforeach
            </select>
        @endif
        @error('organization_id') <span class="form-error">{{ $message }}</span> @enderror
    </div>
</div>

@if($emp)
<p class="section-title">Status</p>
<div class="toggle-group" style="width:fit-content;">
    <label class="toggle">
        <input type="hidden" name="is_active" value="0">
        <input type="checkbox" name="is_active" value="1" {{ old('is_active', $emp?->is_active) ? 'checked' : '' }}>
        <span class="toggle-slider"></span>
    </label>
    <span class="toggle-label">Karyawan Aktif</span>
</div>
@endif

<script>
function updateGender() {
    document.querySelectorAll('#genderGroup .gender-label').forEach(label => {
        label.classList.toggle('selected', label.querySelector('input').checked);
    });
}
</script>
