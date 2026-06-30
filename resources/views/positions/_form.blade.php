@php $pos = $position ?? null; @endphp

<style>
    .form-group { margin-bottom: 16px; }
    .form-label { display:block; font-size:0.8rem; font-weight:600; color:#374151; margin-bottom:6px; }
    .form-label span { color:#ef4444; margin-left:2px; }
    .form-input {
        width:100%; padding:10px 13px; font-size:0.875rem; color:#111827;
        background:#fff; border:1.5px solid #e2e8f0; border-radius:9px;
        outline:none; box-sizing:border-box; font-family:'Inter',sans-serif;
        transition: border-color 0.2s, box-shadow 0.2s;
    }
    .form-input:focus { border-color:#0d2d6b; box-shadow:0 0 0 3px rgba(13,45,107,0.1); }
    .form-input.error { border-color:#ef4444; }
    .form-error { font-size:0.75rem; color:#ef4444; margin-top:4px; }
    .form-hint  { font-size:0.72rem; color:#94a3b8; margin-top:4px; }
    .form-row   { display:grid; grid-template-columns:1fr 1fr; gap:14px; }
</style>

{{-- Departemen (full width) --}}
<div class="form-group">
    <label class="form-label">Departemen <span>*</span></label>
    <select name="department_id" id="sel-dept" class="form-input {{ $errors->has('department_id') ? 'error' : '' }}"
            onfocus="this.style.borderColor='#0d2d6b'; this.style.boxShadow='0 0 0 3px rgba(13,45,107,0.1)';"
            onblur="this.style.borderColor='#e2e8f0'; this.style.boxShadow='none';">
        <option value="">-- Pilih Departemen --</option>
        @foreach($departments->groupBy(fn($d) => $d->organization->name) as $orgName => $depts)
        <optgroup label="{{ $orgName }}">
            @foreach($depts as $dept)
            <option value="{{ $dept->id }}" {{ old('department_id', $pos?->department_id) == $dept->id ? 'selected' : '' }}>
                {{ $dept->name }}
            </option>
            @endforeach
        </optgroup>
        @endforeach
    </select>
    @error('department_id')<div class="form-error">{{ $message }}</div>@enderror
</div>

{{-- Nama + Kode --}}
<div class="form-row">
    <div class="form-group">
        <label class="form-label">Nama Jabatan <span>*</span></label>
        <input type="text" name="name" value="{{ old('name', $pos?->name) }}"
               class="form-input {{ $errors->has('name') ? 'error' : '' }}"
               placeholder="contoh: Kepala Keuangan"
               onfocus="this.style.borderColor='#0d2d6b'; this.style.boxShadow='0 0 0 3px rgba(13,45,107,0.1)';"
               onblur="this.style.borderColor='#e2e8f0'; this.style.boxShadow='none';">
        @error('name')<div class="form-error">{{ $message }}</div>@enderror
    </div>
    <div class="form-group">
        <label class="form-label">Kode <span>*</span></label>
        <input type="text" name="code" value="{{ old('code', $pos?->code) }}"
               class="form-input {{ $errors->has('code') ? 'error' : '' }}"
               placeholder="contoh: KA-KEU"
               style="text-transform:uppercase;"
               oninput="this.value=this.value.toUpperCase()"
               onfocus="this.style.borderColor='#0d2d6b'; this.style.boxShadow='0 0 0 3px rgba(13,45,107,0.1)';"
               onblur="this.style.borderColor='#e2e8f0'; this.style.boxShadow='none';">
        @error('code')<div class="form-error">{{ $message }}</div>@enderror
    </div>
</div>

{{-- Deskripsi --}}
<div class="form-group">
    <label class="form-label">Deskripsi</label>
    <input type="text" name="description" value="{{ old('description', $pos?->description) }}"
           class="form-input"
           placeholder="Keterangan singkat jabatan (opsional)"
           onfocus="this.style.borderColor='#0d2d6b'; this.style.boxShadow='0 0 0 3px rgba(13,45,107,0.1)';"
           onblur="this.style.borderColor='#e2e8f0'; this.style.boxShadow='none';">
</div>

{{-- Toggle: terkait keuangan --}}
<div style="margin-top:4px;">
    <div style="font-size:0.8rem; font-weight:600; color:#374151; margin-bottom:10px;">Atribut Jabatan</div>
    <label style="display:flex; align-items:flex-start; gap:12px; padding:12px 14px; border-radius:9px; border:1.5px solid #e2e8f0; background:#fafafa; cursor:pointer;">
        <input type="hidden" name="is_finance_related" value="0">
        <input type="checkbox" name="is_finance_related" value="1"
               {{ old('is_finance_related', $pos?->is_finance_related) ? 'checked' : '' }}
               style="width:16px; height:16px; accent-color:#0d2d6b; cursor:pointer; margin-top:2px; flex-shrink:0;">
        <div>
            <div style="font-size:0.83rem; font-weight:600; color:#1e293b;">Terkait Keuangan</div>
            <div style="font-size:0.72rem; color:#64748b; margin-top:1px;">Pemegang jabatan ini terlibat dalam proses keuangan atau pengelolaan anggaran</div>
        </div>
    </label>
</div>
