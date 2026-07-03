<x-layouts.app title="Edit Setting Approval">

<a href="{{ route('approval-settings.index') }}" class="inline-flex items-center gap-1.5 text-sm text-slate-500 hover:text-orange-500 mb-5 no-underline">
    <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M19 12H5M12 5l-7 7 7 7"/></svg>
    Kembali ke Setting Approval
</a>
<h1 class="text-xl font-bold text-slate-900 mb-5">Edit Setting Approval</h1>

<div class="bg-white rounded-xl shadow-sm p-6">
    <form method="POST" action="{{ route('approval-settings.update', $approvalSetting) }}">
    @csrf @method('PUT')

    <div class="text-[11px] font-bold text-slate-400 uppercase tracking-widest mb-3.5 pb-2 border-b border-slate-100">Konfigurasi Approval</div>
    <div class="grid grid-cols-2 gap-4">
        <div class="flex flex-col gap-1.5">
            <label class="text-xs font-semibold text-slate-600">Organisasi</label>
            <div class="flex items-center px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-600 font-medium w-full">{{ $approvalSetting->organization->name }}</div>
        </div>

        <div class="flex flex-col gap-1.5">
            <label class="text-xs font-semibold text-slate-600">Jabatan Pengaju</label>
            <div class="flex items-center px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-600 font-medium w-full">{{ $approvalSetting->requesterPosition->name }}</div>
        </div>

        <div class="flex flex-col gap-1.5">
            <label class="text-xs font-semibold text-slate-600">Urutan / Level <span class="text-red-500 ml-0.5">*</span></label>
            <input type="number" name="step" value="{{ old('step', $approvalSetting->step) }}" min="1" max="10"
                class="w-full px-3 py-2.5 border border-slate-200 rounded-xl text-sm text-slate-800 bg-white outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100 transition-colors {{ $errors->has('step') ? 'border-red-400' : '' }}">
            @error('step')<div class="text-xs text-red-500 mt-0.5">{{ $message }}</div>@enderror
        </div>

        <div class="flex flex-col gap-1.5">
            <label class="text-xs font-semibold text-slate-600">Jabatan Approver <span class="text-red-500 ml-0.5">*</span></label>
            <select name="approver_position_id" class="w-full px-3 py-2.5 border border-slate-200 rounded-xl text-sm text-slate-800 bg-white outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100 transition-colors {{ $errors->has('approver_position_id') ? 'border-red-400' : '' }}">
                @foreach($positions as $pos)
                    <option value="{{ $pos->id }}" {{ old('approver_position_id', $approvalSetting->approver_position_id) == $pos->id ? 'selected' : '' }}>{{ $pos->name }}</option>
                @endforeach
            </select>
            @error('approver_position_id')<div class="text-xs text-red-500 mt-0.5">{{ $message }}</div>@enderror
        </div>

        <div class="flex flex-col gap-1.5">
            <label class="text-xs font-semibold text-slate-600">Batas Nominal Maksimum</label>
            <input type="number" name="max_amount" value="{{ old('max_amount', $approvalSetting->max_amount) }}" min="0" step="1000"
                class="w-full px-3 py-2.5 border border-slate-200 rounded-xl text-sm text-slate-800 bg-white outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100 transition-colors"
                placeholder="Kosongkan = tanpa batas">
            @error('max_amount')<div class="text-xs text-red-500 mt-0.5">{{ $message }}</div>@enderror
        </div>

        <div class="flex flex-col gap-1.5">
            <label class="text-xs font-semibold text-slate-600">Status</label>
            <div class="flex items-center gap-3 p-2.5 border border-slate-200 rounded-xl">
                <label class="relative inline-block w-[42px] h-[22px]">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $approvalSetting->is_active) ? 'checked' : '' }} class="peer sr-only">
                    <span class="absolute inset-0 bg-slate-200 rounded-full cursor-pointer transition-all peer-checked:bg-orange-500 before:content-[''] before:absolute before:w-4 before:h-4 before:left-[3px] before:top-[3px] before:bg-white before:rounded-full before:transition-all peer-checked:before:translate-x-5"></span>
                </label>
                <span class="text-sm text-slate-700">Aktif</span>
            </div>
        </div>
    </div>

    <div class="flex gap-3 justify-end mt-6 pt-5 border-t border-slate-100">
        <a href="{{ route('approval-settings.index') }}" class="px-5 py-2.5 rounded-xl border border-slate-200 bg-white text-slate-600 text-sm font-medium no-underline inline-flex items-center">Batal</a>
        <button type="submit" class="px-6 py-2.5 rounded-xl text-sm font-semibold bg-gradient-to-br from-orange-400 to-orange-500 text-white border-0 cursor-pointer hover:-translate-y-px transition-all">Simpan Perubahan</button>
    </div>
    </form>
</div>
</x-layouts.app>
