<x-layouts.app title="Edit Pengajuan Dana">

<a href="{{ route('fund-requests.show', $fundRequest) }}" class="inline-flex items-center gap-1.5 text-sm text-slate-500 hover:text-orange-500 mb-5 no-underline">
    <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M19 12H5M12 5l-7 7 7 7"/></svg>
    Kembali ke Detail
</a>

<h1 class="text-xl font-bold text-slate-900 mb-1.5">Edit Pengajuan Dana</h1>
<div class="inline-flex items-center gap-1.5 px-3 py-1 bg-orange-50 border border-orange-200 rounded-lg font-mono text-sm font-bold text-orange-600 mb-5">
    <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"/></svg>
    {{ $fundRequest->reference }}
</div>

<div class="bg-white rounded-xl shadow-sm p-6">
    <form method="POST" action="{{ route('fund-requests.update', $fundRequest) }}">
    @csrf @method('PUT')

    <div class="text-[11px] font-bold text-slate-400 uppercase tracking-widest mb-3.5 pb-2 border-b border-slate-100">Detail Pengajuan</div>
    <div class="grid grid-cols-2 gap-4">
        <div class="flex flex-col gap-1.5">
            <label class="text-xs font-semibold text-slate-600">Organisasi</label>
            <div class="flex items-center px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-600 font-medium w-full">{{ $fundRequest->organization->name }}</div>
        </div>

        <div class="flex flex-col gap-1.5">
            <label class="text-xs font-semibold text-slate-600">Departemen <span class="text-red-500 ml-0.5">*</span></label>
            <select name="department_id" class="w-full px-3 py-2.5 border border-slate-200 rounded-xl text-sm text-slate-800 bg-white outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100 transition-colors {{ $errors->has('department_id') ? 'border-red-400' : '' }}">
                <option value="">-- Pilih Departemen --</option>
                @foreach($departments as $d)
                    <option value="{{ $d->id }}" {{ old('department_id', $fundRequest->department_id) == $d->id ? 'selected' : '' }}>{{ $d->name }}</option>
                @endforeach
            </select>
            @error('department_id')<div class="text-xs text-red-500 mt-0.5">{{ $message }}</div>@enderror
        </div>

        <div class="flex flex-col gap-1.5">
            <label class="text-xs font-semibold text-slate-600">Periode Anggaran</label>
            <select name="budget_period_id" class="w-full px-3 py-2.5 border border-slate-200 rounded-xl text-sm text-slate-800 bg-white outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100 transition-colors {{ $errors->has('budget_period_id') ? 'border-red-400' : '' }}">
                <option value="">-- Tanpa Periode Anggaran --</option>
                @foreach($budgetPeriods as $bp)
                    <option value="{{ $bp->id }}" {{ old('budget_period_id', $fundRequest->budget_period_id) == $bp->id ? 'selected' : '' }}>{{ $bp->name }}</option>
                @endforeach
            </select>
            @error('budget_period_id')<div class="text-xs text-red-500 mt-0.5">{{ $message }}</div>@enderror
        </div>

        <div class="flex flex-col gap-1.5">
            <label class="text-xs font-semibold text-slate-600">Jumlah Dana (Rp) <span class="text-red-500 ml-0.5">*</span></label>
            <div class="flex items-center">
                <span class="px-3 py-2.5 bg-slate-100 border border-slate-200 border-r-0 rounded-l-xl text-sm text-slate-500 font-medium whitespace-nowrap">Rp</span>
                <input type="number" name="amount" value="{{ old('amount', $fundRequest->amount) }}" min="1000" step="1000"
                    class="w-full px-3 py-2.5 border border-slate-200 rounded-r-xl rounded-l-none text-sm text-slate-800 bg-white outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100 transition-colors {{ $errors->has('amount') ? 'border-red-400' : '' }}">
            </div>
            @error('amount')<div class="text-xs text-red-500 mt-0.5">{{ $message }}</div>@enderror
        </div>

        <div class="flex flex-col gap-1.5 col-span-2">
            <label class="text-xs font-semibold text-slate-600">Judul Pengajuan <span class="text-red-500 ml-0.5">*</span></label>
            <input type="text" name="title" value="{{ old('title', $fundRequest->title) }}" maxlength="200"
                class="w-full px-3 py-2.5 border border-slate-200 rounded-xl text-sm text-slate-800 bg-white outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100 transition-colors {{ $errors->has('title') ? 'border-red-400' : '' }}">
            @error('title')<div class="text-xs text-red-500 mt-0.5">{{ $message }}</div>@enderror
        </div>

        <div class="flex flex-col gap-1.5 col-span-2">
            <label class="text-xs font-semibold text-slate-600">Tujuan / Keterangan <span class="text-red-500 ml-0.5">*</span></label>
            <textarea name="purpose" rows="3" maxlength="1000" required
                class="w-full px-3 py-2.5 border border-slate-200 rounded-xl text-sm text-slate-800 bg-white outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100 transition-colors resize-y {{ $errors->has('purpose') ? 'border-red-400' : '' }}">{{ old('purpose', $fundRequest->purpose) }}</textarea>
            @error('purpose')<div class="text-xs text-red-500 mt-0.5">{{ $message }}</div>@enderror
        </div>
    </div>

    <div class="text-[11px] font-bold text-slate-400 uppercase tracking-widest mb-3.5 pb-2 border-b border-slate-100 mt-5">Informasi Rekening Tujuan Transfer</div>
    <div class="grid grid-cols-3 gap-4">
        <div class="flex flex-col gap-1.5">
            <label class="text-xs font-semibold text-slate-600">Nama Bank</label>
            <input type="text" name="bank_name" value="{{ old('bank_name', $fundRequest->bank_name) }}" maxlength="100"
                class="w-full px-3 py-2.5 border border-slate-200 rounded-xl text-sm text-slate-800 bg-white outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100 transition-colors {{ $errors->has('bank_name') ? 'border-red-400' : '' }}"
                placeholder="Contoh: BRI, BNI, Mandiri...">
            @error('bank_name')<div class="text-xs text-red-500 mt-0.5">{{ $message }}</div>@enderror
        </div>

        <div class="flex flex-col gap-1.5">
            <label class="text-xs font-semibold text-slate-600">Nomor Rekening <span class="text-red-500 ml-0.5">*</span></label>
            <input type="text" name="bank_account_number" value="{{ old('bank_account_number', $fundRequest->bank_account_number) }}" maxlength="50"
                inputmode="numeric" pattern="[0-9]+" title="Hanya boleh angka"
                oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                class="w-full px-3 py-2.5 border border-slate-200 rounded-xl text-sm text-slate-800 bg-white outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100 transition-colors font-mono {{ $errors->has('bank_account_number') ? 'border-red-400' : '' }}"
                placeholder="Nomor rekening...">
            @error('bank_account_number')<div class="text-xs text-red-500 mt-0.5">{{ $message }}</div>@enderror
        </div>

        <div class="flex flex-col gap-1.5">
            <label class="text-xs font-semibold text-slate-600">Nama Pemilik Rekening <span class="text-red-500 ml-0.5">*</span></label>
            <input type="text" name="bank_account_name" value="{{ old('bank_account_name', $fundRequest->bank_account_name) }}" maxlength="150"
                class="w-full px-3 py-2.5 border border-slate-200 rounded-xl text-sm text-slate-800 bg-white outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100 transition-colors {{ $errors->has('bank_account_name') ? 'border-red-400' : '' }}"
                placeholder="Sesuai buku tabungan...">
            @error('bank_account_name')<div class="text-xs text-red-500 mt-0.5">{{ $message }}</div>@enderror
        </div>
    </div>

    <div class="flex gap-3 justify-end mt-6 pt-5 border-t border-slate-100">
        <a href="{{ route('fund-requests.show', $fundRequest) }}" class="px-5 py-2.5 rounded-xl border border-slate-200 bg-white text-slate-600 text-sm font-medium no-underline inline-flex items-center">Batal</a>
        <button type="submit" class="px-6 py-2.5 rounded-xl text-sm font-semibold bg-gradient-to-br from-orange-400 to-orange-500 text-white border-0 cursor-pointer hover:-translate-y-px transition-all">Simpan Perubahan</button>
    </div>
    </form>
</div>
</x-layouts.app>
