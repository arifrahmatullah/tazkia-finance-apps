@php $bp = $budgetPeriod ?? null; @endphp

<style>
    .form-group { margin-bottom:16px; }
    .form-label { display:block; font-size:0.8rem; font-weight:600; color:#374151; margin-bottom:6px; }
    .form-label span { color:#ef4444; margin-left:2px; }
    .form-input {
        width:100%; padding:10px 13px; font-size:0.875rem; color:#111827;
        background:#fff; border:1.5px solid #e2e8f0; border-radius:9px;
        outline:none; box-sizing:border-box; font-family:'Inter',sans-serif;
        transition:border-color 0.2s, box-shadow 0.2s;
    }
    .form-input:focus { border-color:#0d2d6b; box-shadow:0 0 0 3px rgba(13,45,107,0.1); }
    .form-input.error { border-color:#ef4444; }
    .form-error { font-size:0.75rem; color:#ef4444; margin-top:4px; }
    .form-hint  { font-size:0.72rem; color:#94a3b8; margin-top:4px; }
    .form-row   { display:grid; grid-template-columns:1fr 1fr; gap:14px; }
    .section-label {
        font-size:0.72rem; font-weight:700; text-transform:uppercase; letter-spacing:0.07em;
        color:#94a3b8; margin:20px 0 10px; padding-bottom:6px;
        border-bottom:1px solid #f1f5f9;
    }
</style>

{{-- Organisasi --}}
<div class="form-group">
    <label class="form-label">Organisasi <span>*</span></label>
    <select name="organization_id" class="form-input {{ $errors->has('organization_id') ? 'error' : '' }}"
            onfocus="this.style.borderColor='#0d2d6b'; this.style.boxShadow='0 0 0 3px rgba(13,45,107,0.1)';"
            onblur="this.style.borderColor='#e2e8f0'; this.style.boxShadow='none';">
        <option value="">-- Pilih Organisasi --</option>
        @foreach($organizations as $org)
        <option value="{{ $org->id }}" {{ old('organization_id', $bp?->organization_id) == $org->id ? 'selected' : '' }}>
            {{ $org->name }}
        </option>
        @endforeach
    </select>
    @error('organization_id')<div class="form-error">{{ $message }}</div>@enderror
</div>

{{-- Kode + Nama --}}
<div class="form-row">
    <div class="form-group">
        <label class="form-label">Kode <span>*</span></label>
        <input type="text" name="code" value="{{ old('code', $bp?->code) }}"
               class="form-input {{ $errors->has('code') ? 'error' : '' }}"
               placeholder="contoh: ANG-2025"
               style="text-transform:uppercase;"
               oninput="this.value=this.value.toUpperCase()"
               onfocus="this.style.borderColor='#0d2d6b'; this.style.boxShadow='0 0 0 3px rgba(13,45,107,0.1)';"
               onblur="this.style.borderColor='#e2e8f0'; this.style.boxShadow='none';">
        <div class="form-hint">Kode unik periode</div>
        @error('code')<div class="form-error">{{ $message }}</div>@enderror
    </div>
    <div class="form-group">
        <label class="form-label">Nama Periode <span>*</span></label>
        <input type="text" name="name" value="{{ old('name', $bp?->name) }}"
               class="form-input {{ $errors->has('name') ? 'error' : '' }}"
               placeholder="contoh: Anggaran Tahun 2025"
               onfocus="this.style.borderColor='#0d2d6b'; this.style.boxShadow='0 0 0 3px rgba(13,45,107,0.1)';"
               onblur="this.style.borderColor='#e2e8f0'; this.style.boxShadow='none';">
        @error('name')<div class="form-error">{{ $message }}</div>@enderror
    </div>
</div>

{{-- Periode Anggaran --}}
<div class="section-label">Periode Anggaran</div>
<div class="form-row">
    <div class="form-group">
        <label class="form-label">Tanggal Mulai <span>*</span></label>
        <input type="date" name="period_start" id="period_start"
               value="{{ old('period_start', $bp?->period_start?->format('Y-m-d')) }}"
               class="form-input {{ $errors->has('period_start') ? 'error' : '' }}"
               onfocus="this.style.borderColor='#0d2d6b'; this.style.boxShadow='0 0 0 3px rgba(13,45,107,0.1)';"
               onblur="this.style.borderColor='#e2e8f0'; this.style.boxShadow='none';"
               onchange="validateDateRange('period_start','period_end','err_period')">
        @error('period_start')<div class="form-error">{{ $message }}</div>@enderror
    </div>
    <div class="form-group">
        <label class="form-label">Tanggal Selesai <span>*</span></label>
        <input type="date" name="period_end" id="period_end"
               value="{{ old('period_end', $bp?->period_end?->format('Y-m-d')) }}"
               class="form-input {{ $errors->has('period_end') ? 'error' : '' }}"
               onfocus="this.style.borderColor='#0d2d6b'; this.style.boxShadow='0 0 0 3px rgba(13,45,107,0.1)';"
               onblur="this.style.borderColor='#e2e8f0'; this.style.boxShadow='none';"
               onchange="validateDateRange('period_start','period_end','err_period')">
        @error('period_end')<div class="form-error">{{ $message }}</div>@enderror
        <div class="form-error" id="err_period" style="display:none;"></div>
    </div>
</div>

{{-- Periode Perencanaan --}}
<div class="section-label">Periode Perencanaan <span style="font-weight:400; text-transform:none; letter-spacing:0;">(opsional)</span></div>
<div class="form-row">
    <div class="form-group">
        <label class="form-label">Mulai Perencanaan</label>
        <input type="date" name="planning_start" id="planning_start"
               value="{{ old('planning_start', $bp?->planning_start?->format('Y-m-d')) }}"
               class="form-input"
               onfocus="this.style.borderColor='#0d2d6b'; this.style.boxShadow='0 0 0 3px rgba(13,45,107,0.1)';"
               onblur="this.style.borderColor='#e2e8f0'; this.style.boxShadow='none';"
               onchange="validateDateRange('planning_start','planning_end','err_planning',true)">
        <div class="form-hint">Kapan proses perencanaan anggaran dimulai</div>
        @error('planning_start')<div class="form-error">{{ $message }}</div>@enderror
    </div>
    <div class="form-group">
        <label class="form-label">Selesai Perencanaan</label>
        <input type="date" name="planning_end" id="planning_end"
               value="{{ old('planning_end', $bp?->planning_end?->format('Y-m-d')) }}"
               class="form-input"
               onfocus="this.style.borderColor='#0d2d6b'; this.style.boxShadow='0 0 0 3px rgba(13,45,107,0.1)';"
               onblur="this.style.borderColor='#e2e8f0'; this.style.boxShadow='none';"
               onchange="validateDateRange('planning_start','planning_end','err_planning',true)">
        @error('planning_end')<div class="form-error">{{ $message }}</div>@enderror
        <div class="form-error" id="err_planning" style="display:none;"></div>
    </div>
</div>

<script>
function validateDateRange(startId, endId, errId, allowEqual = false) {
    const start  = document.getElementById(startId).value;
    const end    = document.getElementById(endId).value;
    const errEl  = document.getElementById(errId);
    const endEl  = document.getElementById(endId);

    if (!start || !end) { errEl.style.display = 'none'; endEl.classList.remove('error'); return true; }

    const invalid = allowEqual ? end < start : end <= start;
    if (invalid) {
        const msg = allowEqual
            ? 'Tanggal selesai tidak boleh sebelum tanggal mulai.'
            : 'Tanggal selesai harus setelah tanggal mulai.';
        errEl.textContent  = msg;
        errEl.style.display = 'block';
        endEl.style.borderColor  = '#ef4444';
        endEl.style.boxShadow    = '0 0 0 3px rgba(239,68,68,0.15)';
        return false;
    }

    errEl.style.display  = 'none';
    endEl.style.borderColor = '#e2e8f0';
    endEl.style.boxShadow   = 'none';
    endEl.classList.remove('error');
    return true;
}

document.addEventListener('DOMContentLoaded', function () {
    // Validasi saat submit
    const form = document.querySelector('form');
    if (form) {
        form.addEventListener('submit', function (e) {
            const v1 = validateDateRange('period_start', 'period_end', 'err_period');
            const v2 = validateDateRange('planning_start', 'planning_end', 'err_planning', true);
            if (!v1 || !v2) {
                e.preventDefault();
                if (!v1) document.getElementById('period_end').scrollIntoView({ behavior: 'smooth', block: 'center' });
                else document.getElementById('planning_end').scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        });
    }

    // Validasi nilai awal jika ada old input / edit mode
    validateDateRange('period_start', 'period_end', 'err_period');
    validateDateRange('planning_start', 'planning_end', 'err_planning', true);
});
</script>
