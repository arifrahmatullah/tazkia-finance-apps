@php $dept = $department ?? null; @endphp

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
    .toggle-card {
        display:flex; align-items:flex-start; gap:12px;
        padding:12px 14px; border-radius:9px;
        border:1.5px solid #e2e8f0; background:#fafafa;
        cursor:pointer; transition:border-color 0.15s;
    }
    .toggle-card:hover { border-color:#cbd5e1; }
</style>

{{-- Organisasi + Kode --}}
<div class="form-row">
    <div class="form-group" style="grid-column:span 2;">
        <label class="form-label">Organisasi <span>*</span></label>
        <select name="organization_id" class="form-input {{ $errors->has('organization_id') ? 'error' : '' }}"
                onfocus="this.style.borderColor='#0d2d6b'; this.style.boxShadow='0 0 0 3px rgba(13,45,107,0.1)';"
                onblur="this.style.borderColor='#e2e8f0'; this.style.boxShadow='none';">
            <option value="">-- Pilih Organisasi --</option>
            @foreach($organizations as $org)
            <option value="{{ $org->id }}" {{ old('organization_id', $dept?->organization_id) == $org->id ? 'selected' : '' }}>
                {{ $org->name }}
            </option>
            @endforeach
        </select>
        @error('organization_id')<div class="form-error">{{ $message }}</div>@enderror
    </div>
</div>

<div class="form-row">
    <div class="form-group">
        <label class="form-label">Nama Departemen <span>*</span></label>
        <input type="text" name="name" value="{{ old('name', $dept?->name) }}"
               class="form-input {{ $errors->has('name') ? 'error' : '' }}"
               placeholder="contoh: Keuangan"
               onfocus="this.style.borderColor='#0d2d6b'; this.style.boxShadow='0 0 0 3px rgba(13,45,107,0.1)';"
               onblur="this.style.borderColor='#e2e8f0'; this.style.boxShadow='none';">
        @error('name')<div class="form-error">{{ $message }}</div>@enderror
    </div>
    <div class="form-group">
        <label class="form-label">Kode <span>*</span></label>
        <input type="text" name="code" value="{{ old('code', $dept?->code) }}"
               class="form-input {{ $errors->has('code') ? 'error' : '' }}"
               placeholder="contoh: KEU"
               style="text-transform:uppercase;"
               oninput="this.value=this.value.toUpperCase()"
               onfocus="this.style.borderColor='#0d2d6b'; this.style.boxShadow='0 0 0 3px rgba(13,45,107,0.1)';"
               onblur="this.style.borderColor='#e2e8f0'; this.style.boxShadow='none';">
        <div class="form-hint">Unik per organisasi</div>
        @error('code')<div class="form-error">{{ $message }}</div>@enderror
    </div>
</div>

{{-- Deskripsi --}}
<div class="form-group">
    <label class="form-label">Deskripsi</label>
    <input type="text" name="description" value="{{ old('description', $dept?->description) }}"
           class="form-input"
           placeholder="Keterangan singkat departemen (opsional)"
           onfocus="this.style.borderColor='#0d2d6b'; this.style.boxShadow='0 0 0 3px rgba(13,45,107,0.1)';"
           onblur="this.style.borderColor='#e2e8f0'; this.style.boxShadow='none';">
</div>

{{-- Pengaturan Budget --}}
<div style="margin-top:4px; margin-bottom:4px;">
    <div style="font-size:0.8rem; font-weight:600; color:#374151; margin-bottom:10px;">Pengaturan Anggaran</div>
    <div style="display:flex; flex-direction:column; gap:8px;">

        <label class="toggle-card" id="card-budget" onclick="toggleBudgetBlocking()">
            <input type="hidden" name="has_budget" value="0">
            <input type="checkbox" id="cb-has-budget" name="has_budget" value="1"
                   {{ old('has_budget', $dept?->has_budget) ? 'checked' : '' }}
                   style="width:16px; height:16px; accent-color:#0d2d6b; cursor:pointer; margin-top:2px; flex-shrink:0;">
            <div>
                <div style="font-size:0.83rem; font-weight:600; color:#1e293b;">Punya Anggaran Sendiri</div>
                <div style="font-size:0.72rem; color:#64748b; margin-top:1px;">Departemen ini memiliki pagu anggaran yang bisa dialokasikan</div>
            </div>
        </label>

        <label class="toggle-card" id="card-blocking" style="{{ old('has_budget', $dept?->has_budget) ? '' : 'opacity:0.4; pointer-events:none;' }}">
            <input type="hidden" name="budget_blocking" value="0">
            <input type="checkbox" name="budget_blocking" value="1"
                   {{ old('budget_blocking', $dept?->budget_blocking) ? 'checked' : '' }}
                   style="width:16px; height:16px; accent-color:#dc2626; cursor:pointer; margin-top:2px; flex-shrink:0;">
            <div>
                <div style="font-size:0.83rem; font-weight:600; color:#1e293b;">Blokir jika Anggaran Habis</div>
                <div style="font-size:0.72rem; color:#64748b; margin-top:1px;">Pengajuan tidak bisa diproses jika saldo anggaran tidak mencukupi</div>
            </div>
        </label>
    </div>
</div>

<script>
function toggleBudgetBlocking() {
    const hasBudget = document.getElementById('cb-has-budget');
    const blocking  = document.getElementById('card-blocking');
    // small delay so checkbox state updates first
    setTimeout(() => {
        blocking.style.opacity = hasBudget.checked ? '1' : '0.4';
        blocking.style.pointerEvents = hasBudget.checked ? 'auto' : 'none';
    }, 10);
}
</script>
