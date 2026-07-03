@php $bp = $budgetPeriod ?? null; @endphp

{{-- Organisasi --}}
<div class="flex flex-col gap-1.5 mb-4">
    <label class="text-xs font-semibold text-slate-600">Organisasi <span class="text-red-500 ml-0.5">*</span></label>
    <select name="organization_id" class="w-full px-3 py-2.5 border rounded-xl text-sm text-slate-800 bg-white outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100 transition-colors {{ $errors->has('organization_id') ? 'border-red-500' : 'border-slate-200' }}">
        <option value="">-- Pilih Organisasi --</option>
        @foreach($organizations as $org)
        <option value="{{ $org->id }}" {{ old('organization_id', $bp?->organization_id) == $org->id ? 'selected' : '' }}>
            {{ $org->name }}
        </option>
        @endforeach
    </select>
    @error('organization_id')<div class="text-xs text-red-500 mt-0.5">{{ $message }}</div>@enderror
</div>

{{-- Kode + Nama --}}
<div class="grid grid-cols-2 gap-4 mb-4">
    <div class="flex flex-col gap-1.5">
        <label class="text-xs font-semibold text-slate-600">Kode <span class="text-red-500 ml-0.5">*</span></label>
        <input type="text" name="code" value="{{ old('code', $bp?->code) }}"
               class="w-full px-3 py-2.5 border rounded-xl text-sm text-slate-800 bg-white outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100 transition-colors uppercase {{ $errors->has('code') ? 'border-red-500' : 'border-slate-200' }}"
               placeholder="contoh: ANG-2025"
               oninput="this.value=this.value.toUpperCase()">
        <div class="text-xs text-slate-400 mt-0.5">Kode unik periode</div>
        @error('code')<div class="text-xs text-red-500 mt-0.5">{{ $message }}</div>@enderror
    </div>
    <div class="flex flex-col gap-1.5">
        <label class="text-xs font-semibold text-slate-600">Nama Periode <span class="text-red-500 ml-0.5">*</span></label>
        <input type="text" name="name" value="{{ old('name', $bp?->name) }}"
               class="w-full px-3 py-2.5 border rounded-xl text-sm text-slate-800 bg-white outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100 transition-colors {{ $errors->has('name') ? 'border-red-500' : 'border-slate-200' }}"
               placeholder="contoh: Anggaran Tahun 2025">
        @error('name')<div class="text-xs text-red-500 mt-0.5">{{ $message }}</div>@enderror
    </div>
</div>

{{-- Periode Anggaran --}}
<p class="text-[11px] font-bold text-slate-400 uppercase tracking-widest mt-5 mb-3.5 pb-2 border-b border-slate-100">Periode Anggaran</p>
<div class="grid grid-cols-2 gap-4 mb-4">
    <div class="flex flex-col gap-1.5">
        <label class="text-xs font-semibold text-slate-600">Tanggal Mulai <span class="text-red-500 ml-0.5">*</span></label>
        <input type="date" name="period_start" id="period_start"
               value="{{ old('period_start', $bp?->period_start?->format('Y-m-d')) }}"
               class="w-full px-3 py-2.5 border rounded-xl text-sm text-slate-800 bg-white outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100 transition-colors {{ $errors->has('period_start') ? 'border-red-500' : 'border-slate-200' }}"
               onchange="validateDateRange('period_start','period_end','err_period')">
        @error('period_start')<div class="text-xs text-red-500 mt-0.5">{{ $message }}</div>@enderror
    </div>
    <div class="flex flex-col gap-1.5">
        <label class="text-xs font-semibold text-slate-600">Tanggal Selesai <span class="text-red-500 ml-0.5">*</span></label>
        <input type="date" name="period_end" id="period_end"
               value="{{ old('period_end', $bp?->period_end?->format('Y-m-d')) }}"
               class="w-full px-3 py-2.5 border rounded-xl text-sm text-slate-800 bg-white outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100 transition-colors {{ $errors->has('period_end') ? 'border-red-500' : 'border-slate-200' }}"
               onchange="validateDateRange('period_start','period_end','err_period')">
        @error('period_end')<div class="text-xs text-red-500 mt-0.5">{{ $message }}</div>@enderror
        <div class="text-xs text-red-500 mt-0.5" id="err_period" style="display:none;"></div>
    </div>
</div>

{{-- Periode Perencanaan --}}
<p class="text-[11px] font-bold text-slate-400 uppercase tracking-widest mt-5 mb-3.5 pb-2 border-b border-slate-100">Periode Perencanaan <span class="normal-case font-normal tracking-normal">(opsional)</span></p>
<div class="grid grid-cols-2 gap-4">
    <div class="flex flex-col gap-1.5">
        <label class="text-xs font-semibold text-slate-600">Mulai Perencanaan</label>
        <input type="date" name="planning_start" id="planning_start"
               value="{{ old('planning_start', $bp?->planning_start?->format('Y-m-d')) }}"
               class="w-full px-3 py-2.5 border border-slate-200 rounded-xl text-sm text-slate-800 bg-white outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100 transition-colors"
               onchange="validateDateRange('planning_start','planning_end','err_planning',true)">
        <div class="text-xs text-slate-400 mt-0.5">Kapan proses perencanaan anggaran dimulai</div>
        @error('planning_start')<div class="text-xs text-red-500 mt-0.5">{{ $message }}</div>@enderror
    </div>
    <div class="flex flex-col gap-1.5">
        <label class="text-xs font-semibold text-slate-600">Selesai Perencanaan</label>
        <input type="date" name="planning_end" id="planning_end"
               value="{{ old('planning_end', $bp?->planning_end?->format('Y-m-d')) }}"
               class="w-full px-3 py-2.5 border border-slate-200 rounded-xl text-sm text-slate-800 bg-white outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100 transition-colors"
               onchange="validateDateRange('planning_start','planning_end','err_planning',true)">
        @error('planning_end')<div class="text-xs text-red-500 mt-0.5">{{ $message }}</div>@enderror
        <div class="text-xs text-red-500 mt-0.5" id="err_planning" style="display:none;"></div>
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
