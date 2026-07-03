@php $pos = $position ?? null; @endphp

{{-- Departemen (full width) --}}
<div class="flex flex-col gap-1.5 mb-4">
    <label class="text-xs font-semibold text-slate-600">Departemen <span class="text-red-500 ml-0.5">*</span></label>
    <select name="department_id" id="sel-dept"
            class="w-full px-3 py-2.5 border rounded-xl text-sm text-slate-800 bg-white outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100 transition-colors {{ $errors->has('department_id') ? 'border-red-400' : 'border-slate-200' }}">
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
    @error('department_id')<div class="text-xs text-red-500 mt-0.5">{{ $message }}</div>@enderror
</div>

{{-- Nama + Kode --}}
<div class="grid grid-cols-2 gap-4 mb-4">
    <div class="flex flex-col gap-1.5">
        <label class="text-xs font-semibold text-slate-600">Nama Jabatan <span class="text-red-500 ml-0.5">*</span></label>
        <input type="text" name="name" value="{{ old('name', $pos?->name) }}"
               class="w-full px-3 py-2.5 border rounded-xl text-sm text-slate-800 bg-white outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100 transition-colors {{ $errors->has('name') ? 'border-red-400' : 'border-slate-200' }}"
               placeholder="contoh: Kepala Keuangan">
        @error('name')<div class="text-xs text-red-500 mt-0.5">{{ $message }}</div>@enderror
    </div>
    <div class="flex flex-col gap-1.5">
        <label class="text-xs font-semibold text-slate-600">Kode <span class="text-red-500 ml-0.5">*</span></label>
        <input type="text" name="code" value="{{ old('code', $pos?->code) }}"
               class="w-full px-3 py-2.5 border rounded-xl text-sm text-slate-800 bg-white outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100 transition-colors uppercase {{ $errors->has('code') ? 'border-red-400' : 'border-slate-200' }}"
               placeholder="contoh: KA-KEU"
               oninput="this.value=this.value.toUpperCase()">
        @error('code')<div class="text-xs text-red-500 mt-0.5">{{ $message }}</div>@enderror
    </div>
</div>

{{-- Deskripsi --}}
<div class="flex flex-col gap-1.5 mb-4">
    <label class="text-xs font-semibold text-slate-600">Deskripsi</label>
    <input type="text" name="description" value="{{ old('description', $pos?->description) }}"
           class="w-full px-3 py-2.5 border border-slate-200 rounded-xl text-sm text-slate-800 bg-white outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100 transition-colors"
           placeholder="Keterangan singkat jabatan (opsional)">
</div>

{{-- Toggle: terkait keuangan --}}
<div class="mt-1">
    <div class="text-xs font-semibold text-slate-600 mb-2.5">Atribut Jabatan</div>
    <label class="flex items-start gap-3 px-3.5 py-3 rounded-xl border border-slate-200 bg-slate-50 cursor-pointer">
        <input type="hidden" name="is_finance_related" value="0">
        <input type="checkbox" name="is_finance_related" value="1"
               {{ old('is_finance_related', $pos?->is_finance_related) ? 'checked' : '' }}
               class="w-4 h-4 cursor-pointer mt-0.5 shrink-0 accent-[#0d2d6b]">
        <div>
            <div class="text-sm font-semibold text-slate-800">Terkait Keuangan</div>
            <div class="text-xs text-slate-500 mt-0.5">Pemegang jabatan ini terlibat dalam proses keuangan atau pengelolaan anggaran</div>
        </div>
    </label>
</div>
