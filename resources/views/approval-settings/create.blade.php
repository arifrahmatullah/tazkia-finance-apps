<x-layouts.app title="Tambah Setting Approval">

<a href="{{ route('approval-settings.index') }}" class="inline-flex items-center gap-1.5 text-sm text-slate-500 hover:text-orange-500 mb-5 no-underline">
    <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M19 12H5M12 5l-7 7 7 7"/></svg>
    Kembali ke Setting Approval
</a>
<h1 class="text-xl font-bold text-slate-900 mb-5">Tambah Setting Approval</h1>

<div class="flex gap-2.5 items-start px-4 py-3 bg-blue-50 border border-blue-200 rounded-xl mb-4 text-sm text-blue-700">
    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" class="shrink-0 mt-px"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4M12 8h.01"/></svg>
    <span>Satu <strong>Jabatan Pengaju</strong> dapat memiliki beberapa level approval. Level diurutkan berdasarkan nomor <strong>Urutan</strong>. Semua level harus disetujui sebelum pengajuan dinyatakan approved.</span>
</div>

<div class="bg-white rounded-xl shadow-sm p-6">
    <form method="POST" action="{{ route('approval-settings.store') }}">
    @csrf

    <div class="text-[11px] font-bold text-slate-400 uppercase tracking-widest mb-3.5 pb-2 border-b border-slate-100">Konfigurasi Approval</div>
    <div class="grid grid-cols-2 gap-4">
        <div class="flex flex-col gap-1.5">
            <label class="text-xs font-semibold text-slate-600">Organisasi <span class="text-red-500 ml-0.5">*</span></label>
            <select name="organization_id" class="w-full px-3 py-2.5 border border-slate-200 rounded-xl text-sm text-slate-800 bg-white outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100 transition-colors {{ $errors->has('organization_id') ? 'border-red-400' : '' }}">
                <option value="">-- Pilih Organisasi --</option>
                @foreach($organizations as $org)
                    <option value="{{ $org->id }}" {{ old('organization_id') == $org->id ? 'selected' : '' }}>{{ $org->name }}</option>
                @endforeach
            </select>
            @error('organization_id')<div class="text-xs text-red-500 mt-0.5">{{ $message }}</div>@enderror
        </div>

        <div class="flex flex-col gap-1.5">
            <label class="text-xs font-semibold text-slate-600">Urutan / Level <span class="text-red-500 ml-0.5">*</span></label>
            <input type="number" name="step" value="{{ old('step', 1) }}" min="1" max="10"
                class="w-full px-3 py-2.5 border border-slate-200 rounded-xl text-sm text-slate-800 bg-white outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100 transition-colors {{ $errors->has('step') ? 'border-red-400' : '' }}"
                placeholder="1, 2, 3, ...">
            <div class="text-xs text-slate-400 mt-0.5">Level 1 = approver pertama, Level 2 = approver kedua, dst.</div>
            @error('step')<div class="text-xs text-red-500 mt-0.5">{{ $message }}</div>@enderror
        </div>

        <div class="flex flex-col gap-1.5">
            <label class="text-xs font-semibold text-slate-600">Jabatan Pengaju <span class="text-red-500 ml-0.5">*</span></label>
            <select name="requester_position_id" class="w-full px-3 py-2.5 border border-slate-200 rounded-xl text-sm text-slate-800 bg-white outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100 transition-colors {{ $errors->has('requester_position_id') ? 'border-red-400' : '' }}">
                <option value="">-- Pilih Jabatan --</option>
                @foreach($positions as $pos)
                    <option value="{{ $pos->id }}" {{ old('requester_position_id') == $pos->id ? 'selected' : '' }}>{{ $pos->name }}</option>
                @endforeach
            </select>
            <div class="text-xs text-slate-400 mt-0.5">Jabatan karyawan yang membuat pengajuan</div>
            @error('requester_position_id')<div class="text-xs text-red-500 mt-0.5">{{ $message }}</div>@enderror
        </div>

        <div class="flex flex-col gap-1.5">
            <label class="text-xs font-semibold text-slate-600">Jabatan Approver <span class="text-red-500 ml-0.5">*</span></label>
            <select name="approver_position_id" class="w-full px-3 py-2.5 border border-slate-200 rounded-xl text-sm text-slate-800 bg-white outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100 transition-colors {{ $errors->has('approver_position_id') ? 'border-red-400' : '' }}">
                <option value="">-- Pilih Jabatan --</option>
                @foreach($positions as $pos)
                    <option value="{{ $pos->id }}" {{ old('approver_position_id') == $pos->id ? 'selected' : '' }}>{{ $pos->name }}</option>
                @endforeach
            </select>
            <div class="text-xs text-slate-400 mt-0.5">Jabatan yang berwenang menyetujui di level ini</div>
            @error('approver_position_id')<div class="text-xs text-red-500 mt-0.5">{{ $message }}</div>@enderror
        </div>

        <div class="flex flex-col gap-1.5">
            <label class="text-xs font-semibold text-slate-600">Batas Nominal Maksimum</label>
            <input type="number" name="max_amount" value="{{ old('max_amount') }}" min="0" step="1000"
                class="w-full px-3 py-2.5 border border-slate-200 rounded-xl text-sm text-slate-800 bg-white outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100 transition-colors {{ $errors->has('max_amount') ? 'border-red-400' : '' }}"
                placeholder="Kosongkan = tanpa batas">
            <div class="text-xs text-slate-400 mt-0.5">Isi jika setting ini hanya berlaku untuk nominal ≤ nilai ini. Kosongkan untuk semua nominal.</div>
            @error('max_amount')<div class="text-xs text-red-500 mt-0.5">{{ $message }}</div>@enderror
        </div>

        <div class="flex flex-col gap-1.5">
            <label class="text-xs font-semibold text-slate-600">Status</label>
            <div class="flex items-center gap-3 p-2.5 border border-slate-200 rounded-xl">
                <label class="relative inline-block w-[42px] h-[22px]">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }} class="peer sr-only">
                    <span class="absolute inset-0 bg-slate-200 rounded-full cursor-pointer transition-all peer-checked:bg-orange-500 before:content-[''] before:absolute before:w-4 before:h-4 before:left-[3px] before:top-[3px] before:bg-white before:rounded-full before:transition-all peer-checked:before:translate-x-5"></span>
                </label>
                <span class="text-sm text-slate-700">Aktif</span>
            </div>
        </div>
    </div>

    <div class="flex gap-3 justify-end mt-6 pt-5 border-t border-slate-100">
        <a href="{{ route('approval-settings.index') }}" class="px-5 py-2.5 rounded-xl border border-slate-200 bg-white text-slate-600 text-sm font-medium no-underline inline-flex items-center">Batal</a>
        <button type="submit" class="px-6 py-2.5 rounded-xl text-sm font-semibold bg-gradient-to-br from-orange-400 to-orange-500 text-white border-0 cursor-pointer hover:-translate-y-px transition-all">Simpan Setting</button>
    </div>
    </form>
</div>
</x-layouts.app>
