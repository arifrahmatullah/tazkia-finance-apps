@php $dept = $department ?? null; @endphp

<div class="flex flex-col gap-4">

    {{-- Organisasi (full width) --}}
    <div class="grid grid-cols-2 gap-4">
        <div class="col-span-2 flex flex-col gap-1.5">
            <label class="text-xs font-semibold text-slate-600">Organisasi <span class="text-red-500 ml-0.5">*</span></label>
            <select name="organization_id" class="w-full px-3 py-2.5 border {{ $errors->has('organization_id') ? 'border-red-400' : 'border-slate-200' }} rounded-xl text-sm text-slate-800 bg-white outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100 transition-colors">
                <option value="">-- Pilih Organisasi --</option>
                @foreach($organizations as $org)
                <option value="{{ $org->id }}" {{ old('organization_id', $dept?->organization_id) == $org->id ? 'selected' : '' }}>
                    {{ $org->name }}
                </option>
                @endforeach
            </select>
            @error('organization_id')<p class="text-xs text-red-500 mt-0.5">{{ $message }}</p>@enderror
        </div>
    </div>

    {{-- Nama + Kode --}}
    <div class="grid grid-cols-2 gap-4">
        <div class="flex flex-col gap-1.5">
            <label class="text-xs font-semibold text-slate-600">Nama Departemen <span class="text-red-500 ml-0.5">*</span></label>
            <input type="text" name="name" value="{{ old('name', $dept?->name) }}"
                   class="w-full px-3 py-2.5 border {{ $errors->has('name') ? 'border-red-400' : 'border-slate-200' }} rounded-xl text-sm text-slate-800 bg-white outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100 transition-colors"
                   placeholder="contoh: Keuangan">
            @error('name')<p class="text-xs text-red-500 mt-0.5">{{ $message }}</p>@enderror
        </div>
        <div class="flex flex-col gap-1.5">
            <label class="text-xs font-semibold text-slate-600">Kode <span class="text-red-500 ml-0.5">*</span></label>
            <input type="text" name="code" value="{{ old('code', $dept?->code) }}"
                   class="w-full px-3 py-2.5 border {{ $errors->has('code') ? 'border-red-400' : 'border-slate-200' }} rounded-xl text-sm text-slate-800 bg-white outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100 transition-colors uppercase"
                   placeholder="contoh: KEU"
                   oninput="this.value=this.value.toUpperCase()">
            <p class="text-xs text-slate-400 mt-0.5">Unik per organisasi</p>
            @error('code')<p class="text-xs text-red-500 mt-0.5">{{ $message }}</p>@enderror
        </div>
    </div>

    {{-- Deskripsi --}}
    <div class="flex flex-col gap-1.5">
        <label class="text-xs font-semibold text-slate-600">Deskripsi</label>
        <input type="text" name="description" value="{{ old('description', $dept?->description) }}"
               class="w-full px-3 py-2.5 border border-slate-200 rounded-xl text-sm text-slate-800 bg-white outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100 transition-colors"
               placeholder="Keterangan singkat departemen (opsional)">
    </div>

    {{-- Pengaturan Budget --}}
    <div>
        <div class="text-xs font-bold text-slate-600 mb-2.5">Pengaturan Anggaran</div>
        <div class="flex flex-col gap-2">

            <label class="flex items-start gap-3 px-3.5 py-3 rounded-xl border border-slate-200 bg-slate-50 cursor-pointer hover:border-slate-300 transition-colors" id="card-budget" onclick="toggleBudgetBlocking()">
                <input type="hidden" name="has_budget" value="0">
                <input type="checkbox" id="cb-has-budget" name="has_budget" value="1"
                       {{ old('has_budget', $dept?->has_budget) ? 'checked' : '' }}
                       class="w-4 h-4 cursor-pointer mt-0.5 shrink-0 accent-[#0d2d6b]">
                <div>
                    <div class="text-sm font-semibold text-slate-800">Punya Anggaran Sendiri</div>
                    <div class="text-xs text-slate-500 mt-0.5">Departemen ini memiliki pagu anggaran yang bisa dialokasikan</div>
                </div>
            </label>

            <label class="flex items-start gap-3 px-3.5 py-3 rounded-xl border border-slate-200 bg-slate-50 cursor-pointer hover:border-slate-300 transition-colors" id="card-blocking" style="{{ old('has_budget', $dept?->has_budget) ? '' : 'opacity:0.4; pointer-events:none;' }}">
                <input type="hidden" name="budget_blocking" value="0">
                <input type="checkbox" name="budget_blocking" value="1"
                       {{ old('budget_blocking', $dept?->budget_blocking) ? 'checked' : '' }}
                       class="w-4 h-4 cursor-pointer mt-0.5 shrink-0 accent-red-600">
                <div>
                    <div class="text-sm font-semibold text-slate-800">Blokir jika Anggaran Habis</div>
                    <div class="text-xs text-slate-500 mt-0.5">Pengajuan tidak bisa diproses jika saldo anggaran tidak mencukupi</div>
                </div>
            </label>
        </div>
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
