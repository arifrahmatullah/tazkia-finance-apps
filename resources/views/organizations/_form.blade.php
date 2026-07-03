@php $org = $organization ?? null; @endphp

<div class="flex flex-col gap-4">

    {{-- Nama + Kode --}}
    <div class="grid grid-cols-2 gap-4">
        <div class="flex flex-col gap-1.5">
            <label class="text-xs font-semibold text-slate-600">Nama Organisasi <span class="text-red-500 ml-0.5">*</span></label>
            <input type="text" name="name" value="{{ old('name', $org?->name) }}"
                   class="w-full px-3 py-2.5 border {{ $errors->has('name') ? 'border-red-400' : 'border-slate-200' }} rounded-xl text-sm text-slate-800 bg-white outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100 transition-colors"
                   placeholder="contoh: Yayasan Tazkia">
            @error('name')<p class="text-xs text-red-500 mt-0.5">{{ $message }}</p>@enderror
        </div>
        <div class="flex flex-col gap-1.5">
            <label class="text-xs font-semibold text-slate-600">Kode <span class="text-red-500 ml-0.5">*</span></label>
            <input type="text" name="code" value="{{ old('code', $org?->code) }}"
                   class="w-full px-3 py-2.5 border {{ $errors->has('code') ? 'border-red-400' : 'border-slate-200' }} rounded-xl text-sm text-slate-800 bg-white outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100 transition-colors uppercase"
                   placeholder="contoh: YAYASAN"
                   oninput="this.value=this.value.toUpperCase()">
            <p class="text-xs text-slate-400 mt-0.5">Kode unik singkatan organisasi</p>
            @error('code')<p class="text-xs text-red-500 mt-0.5">{{ $message }}</p>@enderror
        </div>
    </div>

    {{-- Tipe + Induk --}}
    <div class="grid grid-cols-2 gap-4">
        <div class="flex flex-col gap-1.5">
            <label class="text-xs font-semibold text-slate-600">Tipe <span class="text-red-500 ml-0.5">*</span></label>
            <select name="type" class="w-full px-3 py-2.5 border {{ $errors->has('type') ? 'border-red-400' : 'border-slate-200' }} rounded-xl text-sm text-slate-800 bg-white outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100 transition-colors">
                <option value="">-- Pilih Tipe --</option>
                @foreach(['yayasan'=>'Yayasan', 'kampus'=>'Kampus', 'unit'=>'Unit'] as $val => $label)
                <option value="{{ $val }}" {{ old('type', $org?->type) === $val ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
            @error('type')<p class="text-xs text-red-500 mt-0.5">{{ $message }}</p>@enderror
        </div>
        <div class="flex flex-col gap-1.5">
            <label class="text-xs font-semibold text-slate-600">Organisasi Induk</label>
            <select name="parent_id" class="w-full px-3 py-2.5 border border-slate-200 rounded-xl text-sm text-slate-800 bg-white outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100 transition-colors">
                <option value="">— Tidak ada (top-level) —</option>
                @foreach($parents as $parent)
                <option value="{{ $parent->id }}" {{ old('parent_id', $org?->parent_id) == $parent->id ? 'selected' : '' }}>
                    {{ $parent->name }}
                </option>
                @endforeach
            </select>
            @error('parent_id')<p class="text-xs text-red-500 mt-0.5">{{ $message }}</p>@enderror
        </div>
    </div>

    {{-- Email + Telepon --}}
    <div class="grid grid-cols-2 gap-4">
        <div class="flex flex-col gap-1.5">
            <label class="text-xs font-semibold text-slate-600">Email</label>
            <input type="email" name="email" value="{{ old('email', $org?->email) }}"
                   class="w-full px-3 py-2.5 border {{ $errors->has('email') ? 'border-red-400' : 'border-slate-200' }} rounded-xl text-sm text-slate-800 bg-white outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100 transition-colors"
                   placeholder="contoh: info@tazkia.ac.id">
            @error('email')<p class="text-xs text-red-500 mt-0.5">{{ $message }}</p>@enderror
        </div>
        <div class="flex flex-col gap-1.5">
            <label class="text-xs font-semibold text-slate-600">Telepon</label>
            <input type="text" name="phone" value="{{ old('phone', $org?->phone) }}"
                   class="w-full px-3 py-2.5 border border-slate-200 rounded-xl text-sm text-slate-800 bg-white outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100 transition-colors"
                   placeholder="contoh: 0251-xxx-xxxx">
        </div>
    </div>

    {{-- Alamat --}}
    <div class="flex flex-col gap-1.5">
        <label class="text-xs font-semibold text-slate-600">Alamat</label>
        <textarea name="address" rows="2"
                  class="w-full px-3 py-2.5 border {{ $errors->has('address') ? 'border-red-400' : 'border-slate-200' }} rounded-xl text-sm text-slate-800 bg-white outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100 transition-colors resize-y"
                  placeholder="Alamat lengkap organisasi...">{{ old('address', $org?->address) }}</textarea>
        @error('address')<p class="text-xs text-red-500 mt-0.5">{{ $message }}</p>@enderror
    </div>

</div>
