<x-layouts.app title="Tambah Estimasi Pendapatan">

<a href="{{ route('income-estimates.index') }}" class="inline-flex items-center gap-1.5 text-sm text-slate-500 hover:text-orange-500 mb-5 no-underline">
    <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M19 12H5M12 5l-7 7 7 7"/></svg>
    Kembali ke Estimasi Pendapatan
</a>
<h1 class="text-xl font-bold text-slate-900 mb-1">Tambah Estimasi Pendapatan</h1>
<p class="text-sm text-slate-400 mb-5">Buat rencana target penerimaan baru.</p>

<div class="bg-white rounded-xl shadow-sm p-6 max-w-2xl">
    <form method="POST" action="{{ route('income-estimates.store') }}">
    @csrf

    {{-- Organisasi --}}
    <div class="flex flex-col gap-1.5 mb-4">
        <label class="text-xs font-semibold text-slate-600">Organisasi <span class="text-red-500">*</span></label>
        <select name="organization_id" id="organization_id"
            class="w-full px-3 py-2.5 border rounded-xl text-sm text-slate-800 bg-white outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100 {{ $errors->has('organization_id') ? 'border-red-400' : 'border-slate-200' }}" required>
            <option value="">-- Pilih Organisasi --</option>
            @foreach($organizations as $org)
                <option value="{{ $org->id }}" {{ old('organization_id', $defaultOrgId) == $org->id ? 'selected' : '' }}>{{ $org->name }}</option>
            @endforeach
        </select>
        @error('organization_id')<p class="text-xs text-red-500">{{ $message }}</p>@enderror
    </div>

    {{-- Periode Aktif (info, bukan input) --}}
    <div class="flex flex-col gap-1.5 mb-4">
        <label class="text-xs font-semibold text-slate-600">Periode Anggaran Aktif</label>
        <div id="period-info" class="flex items-center gap-2 px-3 py-2.5 border border-slate-200 rounded-xl bg-slate-50 text-sm text-slate-400 min-h-[42px]">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" class="shrink-0 opacity-40"><path d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            <span id="period-text">Pilih organisasi terlebih dahulu</span>
        </div>
    </div>

    {{-- Deskripsi --}}
    <div class="flex flex-col gap-1.5 mb-4">
        <label class="text-xs font-semibold text-slate-600">Deskripsi <span class="text-red-500">*</span></label>
        <input type="text" name="description" value="{{ old('description') }}" placeholder="Contoh: SPP Semester Ganjil 2025"
            class="w-full px-3 py-2.5 border rounded-xl text-sm text-slate-800 bg-white outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100 {{ $errors->has('description') ? 'border-red-400' : 'border-slate-200' }}" required>
        @error('description')<p class="text-xs text-red-500">{{ $message }}</p>@enderror
    </div>

    <div class="grid grid-cols-2 gap-4 mb-6">
        <div class="flex flex-col gap-1.5">
            <label class="text-xs font-semibold text-slate-600">Satuan <span class="text-red-500">*</span></label>
            <input type="text" name="unit" value="{{ old('unit') }}" placeholder="Contoh: Mahasiswa, Orang, Paket"
                class="w-full px-3 py-2.5 border rounded-xl text-sm text-slate-800 bg-white outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100 {{ $errors->has('unit') ? 'border-red-400' : 'border-slate-200' }}" required>
            @error('unit')<p class="text-xs text-red-500">{{ $message }}</p>@enderror
        </div>
        <div class="flex flex-col gap-1.5">
            <label class="text-xs font-semibold text-slate-600">Harga per Satuan (Rp) <span class="text-red-500">*</span></label>
            <div class="relative">
                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-sm text-slate-400 font-medium">Rp</span>
                <input type="text" id="unit_price_display" inputmode="numeric"
                    value="{{ old('unit_price') ? number_format((float)old('unit_price'), 0, ',', '.') : '' }}"
                    placeholder="0"
                    class="w-full pl-9 pr-3 py-2.5 border rounded-xl text-sm text-slate-800 bg-white outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100 {{ $errors->has('unit_price') ? 'border-red-400' : 'border-slate-200' }}">
                <input type="hidden" name="unit_price" id="unit_price_raw" value="{{ old('unit_price', 0) }}">
            </div>
            @error('unit_price')<p class="text-xs text-red-500">{{ $message }}</p>@enderror
        </div>
    </div>

    <div class="flex gap-3 justify-end pt-5 border-t border-slate-100">
        <a href="{{ route('income-estimates.index') }}" class="px-5 py-2.5 rounded-xl border border-slate-200 bg-white text-slate-600 text-sm font-medium no-underline inline-flex items-center">Batal</a>
        <button type="submit" class="px-6 py-2.5 rounded-xl text-sm font-semibold bg-gradient-to-br from-orange-400 to-orange-500 text-white border-0 cursor-pointer hover:-translate-y-px transition-all">Simpan</button>
    </div>
    </form>
</div>

<script>
// Format ribuan
const display = document.getElementById('unit_price_display');
const raw     = document.getElementById('unit_price_raw');
display.addEventListener('input', function () {
    const digits = this.value.replace(/\D/g, '');
    this.value = digits ? digits.replace(/\B(?=(\d{3})+(?!\d))/g, '.') : '';
    raw.value  = digits || '0';
});

// Periode aktif — pakai jQuery supaya Select2 change event terdeteksi
function loadActivePeriod(orgId) {
    const periodText = document.getElementById('period-text');
    const periodInfo = document.getElementById('period-info');

    if (!orgId) {
        periodText.textContent       = 'Pilih organisasi terlebih dahulu';
        periodInfo.style.borderColor = '';
        periodInfo.style.background  = '';
        periodText.style.color       = '';
        periodText.style.fontWeight  = '';
        return;
    }

    periodText.textContent = 'Memuat…';

    $.get('{{ route('budget-periods.active') }}', { organization_id: orgId })
        .done(function (data) {
            if (data && data.id) {
                periodText.textContent       = data.name;
                periodInfo.style.borderColor = '#bbf7d0';
                periodInfo.style.background  = '#f0fdf4';
                periodText.style.color       = '#15803d';
                periodText.style.fontWeight  = '600';
            } else {
                periodText.textContent       = 'Tidak ada periode aktif — buat periode anggaran dulu';
                periodInfo.style.borderColor = '#fca5a5';
                periodInfo.style.background  = '#fef2f2';
                periodText.style.color       = '#dc2626';
                periodText.style.fontWeight  = '500';
            }
        })
        .fail(function () {
            periodText.textContent = 'Gagal memuat periode';
        });
}

$(document).ready(function () {
    $('#organization_id').on('change', function () {
        loadActivePeriod($(this).val());
    });

    // setTimeout(0) ensures this runs AFTER initSelect2() (which is also a $(document).ready callback)
    // Without this, Select2 could reset the displayed value after we already triggered the load
    setTimeout(function () {
        var v = $('#organization_id').val();
        if (v) loadActivePeriod(v);
    }, 0);
});
</script>
</x-layouts.app>
