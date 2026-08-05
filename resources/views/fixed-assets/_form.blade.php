@php
    $isEdit = $asset->exists;
    $assetAccounts = $accounts->where('account_type', 'aset');
    $bebanAccounts = $accounts->where('account_type', 'beban');
@endphp

<div class="bg-white rounded-xl shadow-sm p-6">
    <form method="POST" action="{{ $isEdit ? route('fixed-assets.update', $asset) : route('fixed-assets.store') }}">
        @csrf
        @if($isEdit) @method('PUT') @endif

        {{-- Organisasi --}}
        @if($organizations->count() > 1)
        <div class="flex flex-col gap-1.5 mb-5">
            <label class="text-xs font-semibold text-slate-600">Organisasi <span class="text-red-500 ml-0.5">*</span></label>
            <select name="organization_id" id="org_select" onchange="filterAccounts()"
                class="w-full px-3 py-2.5 border rounded-xl text-sm text-slate-800 bg-white outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100 transition-colors {{ $errors->has('organization_id') ? 'border-red-400' : 'border-slate-200' }}">
                <option value="">-- Pilih Organisasi --</option>
                @foreach($organizations as $org)
                    <option value="{{ $org->id }}" {{ old('organization_id', $asset->organization_id) == $org->id ? 'selected' : '' }}>{{ $org->name }}</option>
                @endforeach
            </select>
            @error('organization_id')<div class="text-xs text-red-500 mt-0.5">{{ $message }}</div>@enderror
        </div>
        @else
        <input type="hidden" name="organization_id" id="org_select" value="{{ $organizations->first()?->id }}">
        @endif

        <div class="grid grid-cols-2 gap-4 mb-5">
            <div class="flex flex-col gap-1.5">
                <label class="text-xs font-semibold text-slate-600">Kode Aset <span class="text-red-500 ml-0.5">*</span></label>
                <input type="text" name="code" value="{{ old('code', $asset->code) }}"
                    class="w-full px-3 py-2.5 border rounded-xl text-sm font-mono uppercase bg-white outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100 transition-colors {{ $errors->has('code') ? 'border-red-400' : 'border-slate-200' }}"
                    placeholder="contoh: AT-001" maxlength="30" oninput="this.value=this.value.toUpperCase()">
                @error('code')<div class="text-xs text-red-500 mt-0.5">{{ $message }}</div>@enderror
            </div>
            <div class="flex flex-col gap-1.5">
                <label class="text-xs font-semibold text-slate-600">Nama Aset <span class="text-red-500 ml-0.5">*</span></label>
                <input type="text" name="name" value="{{ old('name', $asset->name) }}"
                    class="w-full px-3 py-2.5 border rounded-xl text-sm text-slate-800 bg-white outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100 transition-colors {{ $errors->has('name') ? 'border-red-400' : 'border-slate-200' }}"
                    placeholder="contoh: Kendaraan Operasional Avanza">
                @error('name')<div class="text-xs text-red-500 mt-0.5">{{ $message }}</div>@enderror
            </div>
        </div>

        <div class="text-[11px] font-bold text-slate-400 uppercase tracking-widest mb-3 pb-2 border-b border-slate-100">Akun Terkait</div>
        <div class="grid grid-cols-1 gap-4 mb-5">
            <div class="flex flex-col gap-1.5">
                <label class="text-xs font-semibold text-slate-600">Akun Aset Tetap <span class="text-red-500 ml-0.5">*</span></label>
                <select name="account_id" class="acct-select w-full px-3 py-2.5 border rounded-xl text-sm text-slate-800 bg-white outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100 transition-colors {{ $errors->has('account_id') ? 'border-red-400' : 'border-slate-200' }}">
                    <option value="">-- Pilih Akun --</option>
                    @foreach($assetAccounts as $acc)
                        <option value="{{ $acc->id }}" data-org="{{ $acc->organization_id }}" {{ old('account_id', $asset->account_id) == $acc->id ? 'selected' : '' }}>{{ $acc->code }} — {{ $acc->name }}</option>
                    @endforeach
                </select>
                <div class="text-xs text-slate-400 mt-0.5">Akun aset (mis. Kendaraan, Bangunan, Peralatan) tempat harga perolehan dicatat</div>
                @error('account_id')<div class="text-xs text-red-500 mt-0.5">{{ $message }}</div>@enderror
            </div>
            <div class="flex flex-col gap-1.5">
                <label class="text-xs font-semibold text-slate-600">Akun Akumulasi Depresiasi <span class="text-red-500 ml-0.5">*</span></label>
                <select name="accumulated_depreciation_account_id" class="acct-select w-full px-3 py-2.5 border rounded-xl text-sm text-slate-800 bg-white outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100 transition-colors {{ $errors->has('accumulated_depreciation_account_id') ? 'border-red-400' : 'border-slate-200' }}">
                    <option value="">-- Pilih Akun --</option>
                    @foreach($assetAccounts as $acc)
                        <option value="{{ $acc->id }}" data-org="{{ $acc->organization_id }}" {{ old('accumulated_depreciation_account_id', $asset->accumulated_depreciation_account_id) == $acc->id ? 'selected' : '' }}>{{ $acc->code }} — {{ $acc->name }}</option>
                    @endforeach
                </select>
                @error('accumulated_depreciation_account_id')<div class="text-xs text-red-500 mt-0.5">{{ $message }}</div>@enderror
            </div>
            <div class="flex flex-col gap-1.5">
                <label class="text-xs font-semibold text-slate-600">Akun Beban Depresiasi <span class="text-red-500 ml-0.5">*</span></label>
                <select name="depreciation_expense_account_id" class="acct-select w-full px-3 py-2.5 border rounded-xl text-sm text-slate-800 bg-white outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100 transition-colors {{ $errors->has('depreciation_expense_account_id') ? 'border-red-400' : 'border-slate-200' }}">
                    <option value="">-- Pilih Akun --</option>
                    @foreach($bebanAccounts as $acc)
                        <option value="{{ $acc->id }}" data-org="{{ $acc->organization_id }}" {{ old('depreciation_expense_account_id', $asset->depreciation_expense_account_id) == $acc->id ? 'selected' : '' }}>{{ $acc->code }} — {{ $acc->name }}</option>
                    @endforeach
                </select>
                @error('depreciation_expense_account_id')<div class="text-xs text-red-500 mt-0.5">{{ $message }}</div>@enderror
            </div>
        </div>

        <div class="text-[11px] font-bold text-slate-400 uppercase tracking-widest mb-3 pb-2 border-b border-slate-100">Perolehan & Penyusutan</div>
        <div class="grid grid-cols-2 gap-4 mb-5">
            <div class="flex flex-col gap-1.5">
                <label class="text-xs font-semibold text-slate-600">Tanggal Perolehan <span class="text-red-500 ml-0.5">*</span></label>
                <input type="date" name="acquisition_date" value="{{ old('acquisition_date', $asset->acquisition_date?->toDateString()) }}"
                    class="w-full px-3 py-2.5 border rounded-xl text-sm text-slate-800 bg-white outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100 transition-colors {{ $errors->has('acquisition_date') ? 'border-red-400' : 'border-slate-200' }}">
                @error('acquisition_date')<div class="text-xs text-red-500 mt-0.5">{{ $message }}</div>@enderror
            </div>
            <div class="flex flex-col gap-1.5">
                <label class="text-xs font-semibold text-slate-600">Umur Manfaat (bulan) <span class="text-red-500 ml-0.5">*</span></label>
                <input type="number" name="useful_life_months" min="1" value="{{ old('useful_life_months', $asset->useful_life_months) }}"
                    class="w-full px-3 py-2.5 border rounded-xl text-sm text-slate-800 bg-white outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100 transition-colors {{ $errors->has('useful_life_months') ? 'border-red-400' : 'border-slate-200' }}"
                    placeholder="contoh: 48">
                @error('useful_life_months')<div class="text-xs text-red-500 mt-0.5">{{ $message }}</div>@enderror
            </div>
            <div class="flex flex-col gap-1.5">
                <label class="text-xs font-semibold text-slate-600">Harga Perolehan (Rp) <span class="text-red-500 ml-0.5">*</span></label>
                <input type="text" id="acquisitionCostDisplay" inputmode="numeric" placeholder="0"
                    value="{{ old('acquisition_cost') ? number_format((int) old('acquisition_cost'), 0, ',', '.') : ($asset->acquisition_cost ? number_format((int) $asset->acquisition_cost, 0, ',', '.') : '') }}"
                    oninput="formatRupiah(this, 'acquisitionCostHidden')"
                    class="w-full px-3 py-2.5 border rounded-xl text-sm text-slate-800 bg-white outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100 transition-colors {{ $errors->has('acquisition_cost') ? 'border-red-400' : 'border-slate-200' }}">
                <input type="hidden" name="acquisition_cost" id="acquisitionCostHidden" value="{{ old('acquisition_cost', $asset->acquisition_cost) }}">
                @error('acquisition_cost')<div class="text-xs text-red-500 mt-0.5">{{ $message }}</div>@enderror
            </div>
            <div class="flex flex-col gap-1.5">
                <label class="text-xs font-semibold text-slate-600">Nilai Residu (Rp)</label>
                <input type="text" id="salvageValueDisplay" inputmode="numeric" placeholder="0"
                    value="{{ old('salvage_value') ? number_format((int) old('salvage_value'), 0, ',', '.') : (($asset->salvage_value ?? 0) ? number_format((int) $asset->salvage_value, 0, ',', '.') : '') }}"
                    oninput="formatRupiah(this, 'salvageValueHidden')"
                    class="w-full px-3 py-2.5 border rounded-xl text-sm text-slate-800 bg-white outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100 transition-colors {{ $errors->has('salvage_value') ? 'border-red-400' : 'border-slate-200' }}">
                <input type="hidden" name="salvage_value" id="salvageValueHidden" value="{{ old('salvage_value', $asset->salvage_value ?? 0) }}">
                <div class="text-xs text-slate-400 mt-0.5">Estimasi nilai jual aset di akhir umur manfaat (default 0)</div>
                @error('salvage_value')<div class="text-xs text-red-500 mt-0.5">{{ $message }}</div>@enderror
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4 mb-5">
            <div class="flex flex-col gap-1.5">
                <label class="text-xs font-semibold text-slate-600">Status</label>
                <select name="status" class="w-full px-3 py-2.5 border border-slate-200 rounded-xl text-sm text-slate-800 bg-white outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100 transition-colors">
                    <option value="aktif" {{ old('status', $asset->status ?? 'aktif') === 'aktif' ? 'selected' : '' }}>Aktif</option>
                    <option value="dihapusbukukan" {{ old('status', $asset->status) === 'dihapusbukukan' ? 'selected' : '' }}>Dihapusbukukan</option>
                </select>
                <div class="text-xs text-slate-400 mt-0.5">Aset "Dihapusbukukan" tidak akan disusutkan lagi</div>
            </div>
            <div class="flex flex-col gap-1.5">
                <label class="text-xs font-semibold text-slate-600">Catatan</label>
                <textarea name="notes" rows="1" class="w-full px-3 py-2.5 border border-slate-200 rounded-xl text-sm text-slate-800 bg-white outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100 transition-colors resize-y">{{ old('notes', $asset->notes) }}</textarea>
            </div>
        </div>

        <div class="flex gap-3 justify-end mt-6 pt-5 border-t border-slate-100">
            <a href="{{ route('fixed-assets.index') }}" class="px-5 py-2.5 rounded-xl border border-slate-200 bg-white text-slate-600 text-sm font-medium no-underline inline-flex items-center">Batal</a>
            <button type="submit" class="px-6 py-2.5 rounded-xl text-sm font-semibold bg-gradient-to-br from-orange-400 to-orange-500 text-white border-0 cursor-pointer hover:-translate-y-px transition-all">{{ $isEdit ? 'Simpan Perubahan' : 'Simpan Aset' }}</button>
        </div>
    </form>
</div>

<script>
function filterAccounts() {
    const orgSelect = document.getElementById('org_select');
    const orgId = orgSelect ? orgSelect.value : null;
    document.querySelectorAll('.acct-select').forEach(sel => {
        [...sel.options].forEach(opt => {
            if (!opt.value) { opt.hidden = false; return; }
            opt.hidden = orgId ? (opt.dataset.org !== orgId) : false;
        });
        if (sel.selectedOptions[0] && sel.selectedOptions[0].hidden) {
            sel.value = '';
        }
    });
}
document.addEventListener('DOMContentLoaded', filterAccounts);

function formatRupiah(input, hiddenId) {
    const raw = input.value.replace(/\D/g, '');
    document.getElementById(hiddenId).value = raw;
    input.value = raw ? parseInt(raw).toLocaleString('id-ID') : '';
}
</script>
