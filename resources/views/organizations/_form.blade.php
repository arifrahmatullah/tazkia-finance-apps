@php $org = $organization ?? null; @endphp

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
    .form-hint { font-size:0.72rem; color:#94a3b8; margin-top:4px; }
    .form-row { display:grid; grid-template-columns:1fr 1fr; gap:14px; }
</style>

{{-- Nama + Kode --}}
<div class="form-row">
    <div class="form-group">
        <label class="form-label">Nama Organisasi <span>*</span></label>
        <input type="text" name="name" value="{{ old('name', $org?->name) }}"
               class="form-input {{ $errors->has('name') ? 'error' : '' }}"
               placeholder="contoh: Yayasan Tazkia"
               onfocus="this.style.borderColor='#0d2d6b'; this.style.boxShadow='0 0 0 3px rgba(13,45,107,0.1)';"
               onblur="this.style.borderColor='#e2e8f0'; this.style.boxShadow='none';">
        @error('name')<div class="form-error">{{ $message }}</div>@enderror
    </div>
    <div class="form-group">
        <label class="form-label">Kode <span>*</span></label>
        <input type="text" name="code" value="{{ old('code', $org?->code) }}"
               class="form-input {{ $errors->has('code') ? 'error' : '' }}"
               placeholder="contoh: YAYASAN"
               style="text-transform:uppercase;"
               oninput="this.value=this.value.toUpperCase()"
               onfocus="this.style.borderColor='#0d2d6b'; this.style.boxShadow='0 0 0 3px rgba(13,45,107,0.1)';"
               onblur="this.style.borderColor='#e2e8f0'; this.style.boxShadow='none';">
        <div class="form-hint">Kode unik singkatan organisasi</div>
        @error('code')<div class="form-error">{{ $message }}</div>@enderror
    </div>
</div>

{{-- Tipe + Induk --}}
<div class="form-row">
    <div class="form-group">
        <label class="form-label">Tipe <span>*</span></label>
        <select name="type" class="form-input {{ $errors->has('type') ? 'error' : '' }}"
                onfocus="this.style.borderColor='#0d2d6b'; this.style.boxShadow='0 0 0 3px rgba(13,45,107,0.1)';"
                onblur="this.style.borderColor='#e2e8f0'; this.style.boxShadow='none';">
            <option value="">-- Pilih Tipe --</option>
            @foreach(['yayasan'=>'Yayasan', 'kampus'=>'Kampus', 'unit'=>'Unit'] as $val => $label)
            <option value="{{ $val }}" {{ old('type', $org?->type) === $val ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
        @error('type')<div class="form-error">{{ $message }}</div>@enderror
    </div>
    <div class="form-group">
        <label class="form-label">Organisasi Induk</label>
        <select name="parent_id" class="form-input"
                onfocus="this.style.borderColor='#0d2d6b'; this.style.boxShadow='0 0 0 3px rgba(13,45,107,0.1)';"
                onblur="this.style.borderColor='#e2e8f0'; this.style.boxShadow='none';">
            <option value="">— Tidak ada (top-level) —</option>
            @foreach($parents as $parent)
            <option value="{{ $parent->id }}" {{ old('parent_id', $org?->parent_id) == $parent->id ? 'selected' : '' }}>
                {{ $parent->name }}
            </option>
            @endforeach
        </select>
        @error('parent_id')<div class="form-error">{{ $message }}</div>@enderror
    </div>
</div>

{{-- Email + Telepon --}}
<div class="form-row">
    <div class="form-group">
        <label class="form-label">Email</label>
        <input type="email" name="email" value="{{ old('email', $org?->email) }}"
               class="form-input {{ $errors->has('email') ? 'error' : '' }}"
               placeholder="contoh: info@tazkia.ac.id"
               onfocus="this.style.borderColor='#0d2d6b'; this.style.boxShadow='0 0 0 3px rgba(13,45,107,0.1)';"
               onblur="this.style.borderColor='#e2e8f0'; this.style.boxShadow='none';">
        @error('email')<div class="form-error">{{ $message }}</div>@enderror
    </div>
    <div class="form-group">
        <label class="form-label">Telepon</label>
        <input type="text" name="phone" value="{{ old('phone', $org?->phone) }}"
               class="form-input"
               placeholder="contoh: 0251-xxx-xxxx"
               onfocus="this.style.borderColor='#0d2d6b'; this.style.boxShadow='0 0 0 3px rgba(13,45,107,0.1)';"
               onblur="this.style.borderColor='#e2e8f0'; this.style.boxShadow='none';">
    </div>
</div>

{{-- Alamat --}}
<div class="form-group">
    <label class="form-label">Alamat</label>
    <textarea name="address" rows="2"
              class="form-input {{ $errors->has('address') ? 'error' : '' }}"
              placeholder="Alamat lengkap organisasi..."
              style="resize:vertical;"
              onfocus="this.style.borderColor='#0d2d6b'; this.style.boxShadow='0 0 0 3px rgba(13,45,107,0.1)';"
              onblur="this.style.borderColor='#e2e8f0'; this.style.boxShadow='none';">{{ old('address', $org?->address) }}</textarea>
    @error('address')<div class="form-error">{{ $message }}</div>@enderror
</div>
