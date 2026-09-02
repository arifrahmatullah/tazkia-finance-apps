@php $bk = $bank ?? null; @endphp

<div class="flex flex-col gap-1.5 mb-4">
    <label class="text-xs font-semibold text-slate-600">Nama Bank <span class="text-red-500">*</span></label>
    <input type="text" name="name" value="{{ old('name', $bk?->name) }}" maxlength="100" placeholder="Contoh: Bank Mandiri"
        class="w-full px-3 py-2.5 border rounded-xl text-sm text-slate-800 bg-white outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100 {{ $errors->has('name') ? 'border-red-400' : 'border-slate-200' }}">
    @error('name')<p class="text-xs text-red-500 mt-0.5">{{ $message }}</p>@enderror
</div>

@if($bk)
<div class="flex items-center gap-3 px-3.5 py-2.5 border border-slate-200 rounded-xl w-fit">
    <label class="toggle relative w-[42px] h-[22px]">
        <input type="hidden" name="is_active" value="0">
        <input type="checkbox" name="is_active" value="1" {{ old('is_active', $bk->is_active) ? 'checked' : '' }}
            class="opacity-0 w-0 h-0 absolute">
        <span class="toggle-slider absolute inset-0 bg-slate-200 rounded-full cursor-pointer transition-[.2s]"></span>
    </label>
    <span class="text-sm text-slate-700 font-medium">Bank Aktif</span>
</div>
<style>
.toggle input:checked + .toggle-slider { background:#f97316; }
.toggle input:checked + .toggle-slider::before { transform:translateX(20px); }
.toggle-slider::before { content:''; position:absolute; width:16px; height:16px; left:3px; top:3px; background:#fff; border-radius:50%; transition:.2s; }
</style>
@endif
