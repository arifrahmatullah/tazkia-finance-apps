<x-layouts.app title="Edit Estimasi Pendapatan">

<a href="{{ route('income-estimates.show', $incomeEstimate) }}" class="inline-flex items-center gap-1.5 text-sm text-slate-500 hover:text-orange-500 mb-5 no-underline">
    <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M19 12H5M12 5l-7 7 7 7"/></svg>
    Kembali ke Detail Estimasi
</a>
<h1 class="text-xl font-bold text-slate-900 mb-1">Edit Estimasi Pendapatan</h1>
<p class="text-sm text-slate-400 mb-5">{{ $incomeEstimate->description }}</p>

<div class="bg-white rounded-xl shadow-sm p-6 max-w-2xl">
    <form method="POST" action="{{ route('income-estimates.update', $incomeEstimate) }}">
    @csrf @method('PUT')

    <div class="grid grid-cols-2 gap-4 mb-4">
        <div class="flex flex-col gap-1.5">
            <label class="text-xs font-semibold text-slate-600">Organisasi <span class="text-red-500">*</span></label>
            <select name="organization_id" class="w-full px-3 py-2.5 border rounded-xl text-sm text-slate-800 bg-white outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100 {{ $errors->has('organization_id') ? 'border-red-400' : 'border-slate-200' }}" required>
                <option value="">-- Pilih Organisasi --</option>
                @foreach($organizations as $org)
                    <option value="{{ $org->id }}" {{ old('organization_id', $incomeEstimate->organization_id) == $org->id ? 'selected' : '' }}>{{ $org->name }}</option>
                @endforeach
            </select>
            @error('organization_id')<p class="text-xs text-red-500">{{ $message }}</p>@enderror
        </div>
        <div class="flex flex-col gap-1.5">
            <label class="text-xs font-semibold text-slate-600">Periode Anggaran <span class="text-red-500">*</span></label>
            <select name="budget_period_id" class="w-full px-3 py-2.5 border rounded-xl text-sm text-slate-800 bg-white outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100 {{ $errors->has('budget_period_id') ? 'border-red-400' : 'border-slate-200' }}" required>
                <option value="">-- Pilih Periode --</option>
                @foreach($budgetPeriods as $bp)
                    <option value="{{ $bp->id }}" {{ old('budget_period_id', $incomeEstimate->budget_period_id) == $bp->id ? 'selected' : '' }}>{{ $bp->name }}</option>
                @endforeach
            </select>
            @error('budget_period_id')<p class="text-xs text-red-500">{{ $message }}</p>@enderror
        </div>
    </div>

    <div class="flex flex-col gap-1.5 mb-4">
        <label class="text-xs font-semibold text-slate-600">Deskripsi <span class="text-red-500">*</span></label>
        <input type="text" name="description" value="{{ old('description', $incomeEstimate->description) }}"
            class="w-full px-3 py-2.5 border rounded-xl text-sm text-slate-800 bg-white outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100 {{ $errors->has('description') ? 'border-red-400' : 'border-slate-200' }}" required>
        @error('description')<p class="text-xs text-red-500">{{ $message }}</p>@enderror
    </div>

    <div class="grid grid-cols-2 gap-4 mb-6">
        <div class="flex flex-col gap-1.5">
            <label class="text-xs font-semibold text-slate-600">Satuan <span class="text-red-500">*</span></label>
            <input type="text" name="unit" value="{{ old('unit', $incomeEstimate->unit) }}"
                class="w-full px-3 py-2.5 border rounded-xl text-sm text-slate-800 bg-white outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100 {{ $errors->has('unit') ? 'border-red-400' : 'border-slate-200' }}" required>
            @error('unit')<p class="text-xs text-red-500">{{ $message }}</p>@enderror
        </div>
        <div class="flex flex-col gap-1.5">
            <label class="text-xs font-semibold text-slate-600">Harga per Satuan (Rp) <span class="text-red-500">*</span></label>
            <div class="relative">
                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-sm text-slate-400 font-medium">Rp</span>
                <input type="text" id="unit_price_display" inputmode="numeric"
                    value="{{ number_format((float) old('unit_price', $incomeEstimate->unit_price), 0, ',', '.') }}"
                    placeholder="0"
                    class="w-full pl-9 pr-3 py-2.5 border rounded-xl text-sm text-slate-800 bg-white outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100 {{ $errors->has('unit_price') ? 'border-red-400' : 'border-slate-200' }}">
                <input type="hidden" name="unit_price" id="unit_price_raw" value="{{ old('unit_price', $incomeEstimate->unit_price) }}">
            </div>
            @error('unit_price')<p class="text-xs text-red-500">{{ $message }}</p>@enderror
        </div>
    </div>

    <div class="flex gap-3 justify-end pt-5 border-t border-slate-100">
        <a href="{{ route('income-estimates.show', $incomeEstimate) }}" class="px-5 py-2.5 rounded-xl border border-slate-200 bg-white text-slate-600 text-sm font-medium no-underline inline-flex items-center">Batal</a>
        <button type="submit" class="px-6 py-2.5 rounded-xl text-sm font-semibold bg-gradient-to-br from-orange-400 to-orange-500 text-white border-0 cursor-pointer hover:-translate-y-px transition-all">Simpan Perubahan</button>
    </div>
    </form>
</div>

<script>
const display = document.getElementById('unit_price_display');
const raw     = document.getElementById('unit_price_raw');

display.addEventListener('input', function () {
    const digits = this.value.replace(/\D/g, '');
    this.value   = digits ? digits.replace(/\B(?=(\d{3})+(?!\d))/g, '.') : '';
    raw.value    = digits || '0';
});
</script>
</x-layouts.app>
