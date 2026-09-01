<x-layouts.app title="Tambah Jadwal Estimasi">

<a href="{{ route('income-estimates.show', $estimate) }}" class="inline-flex items-center gap-1.5 text-sm text-slate-500 hover:text-orange-500 mb-5 no-underline">
    <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M19 12H5M12 5l-7 7 7 7"/></svg>
    Kembali ke {{ $estimate->description }}
</a>
<h1 class="text-xl font-bold text-slate-900 mb-1">Tambah Jadwal Estimasi</h1>
<p class="text-sm text-slate-400 mb-5">{{ $estimate->organization->name }} · {{ $estimate->budgetPeriod->name }}</p>

<div class="bg-white rounded-xl shadow-sm p-6 max-w-xl">

    {{-- Info Total --}}
    <div class="px-4 py-3 bg-orange-50 border border-orange-200 rounded-xl mb-5">
        <div class="text-[11px] text-orange-400 font-semibold uppercase tracking-wide">Total Estimasi</div>
        <div class="text-sm font-bold text-orange-700">Rp {{ number_format($estimate->unit_price, 0, ',', '.') }}</div>
    </div>

    <form method="POST" action="{{ route('income-estimate-details.store') }}">
    @csrf
    <input type="hidden" name="income_estimate_id" value="{{ $estimate->id }}">

    <div class="flex flex-col gap-1.5 mb-4">
        <label class="text-xs font-semibold text-slate-600">Tanggal Estimasi <span class="text-red-500">*</span></label>
        <input type="date" name="estimate_date" value="{{ old('estimate_date') }}"
            class="w-full px-3 py-2.5 border rounded-xl text-sm text-slate-800 bg-white outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100 {{ $errors->has('estimate_date') ? 'border-red-400' : 'border-slate-200' }}" required>
        @error('estimate_date')<p class="text-xs text-red-500">{{ $message }}</p>@enderror
    </div>

    <div class="flex flex-col gap-1.5 mb-4">
        <label class="text-xs font-semibold text-slate-600">Deskripsi <span class="text-red-500">*</span></label>
        <input type="text" name="description" value="{{ old('description') }}" placeholder="Keterangan detail estimasi"
            class="w-full px-3 py-2.5 border rounded-xl text-sm text-slate-800 bg-white outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100 {{ $errors->has('description') ? 'border-red-400' : 'border-slate-200' }}" required>
        @error('description')<p class="text-xs text-red-500">{{ $message }}</p>@enderror
    </div>

    <div class="flex flex-col gap-1.5 mb-5">
        <label class="text-xs font-semibold text-slate-600">Nominal (Rp) <span class="text-red-500">*</span></label>
        <input type="text" id="totalDisplay" inputmode="numeric" placeholder="0"
            value="{{ old('total') ? number_format((float) old('total'), 0, ',', '.') : '' }}"
            class="w-full px-3 py-2.5 border rounded-xl text-sm text-slate-800 bg-white outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100 {{ $errors->has('total') ? 'border-red-400' : 'border-slate-200' }}">
        <input type="hidden" name="total" id="totalRaw" value="{{ old('total') }}">
        @error('total')<p class="text-xs text-red-500">{{ $message }}</p>@enderror
    </div>

    <div class="flex gap-3 justify-end pt-5 border-t border-slate-100">
        <a href="{{ route('income-estimates.show', $estimate) }}" class="px-5 py-2.5 rounded-xl border border-slate-200 bg-white text-slate-600 text-sm font-medium no-underline inline-flex items-center">Batal</a>
        <button type="submit" class="px-6 py-2.5 rounded-xl text-sm font-semibold bg-gradient-to-br from-orange-400 to-orange-500 text-white border-0 cursor-pointer hover:-translate-y-px transition-all">Simpan Jadwal</button>
    </div>
    </form>
</div>

<script>
function formatRupiah(display, hidden) {
    const raw = display.value.replace(/\D/g, '');
    hidden.value = raw;
    display.value = raw ? parseInt(raw).toLocaleString('id-ID') : '';
}
const totalDisplay = document.getElementById('totalDisplay');
const totalRaw     = document.getElementById('totalRaw');
totalDisplay.addEventListener('input', () => formatRupiah(totalDisplay, totalRaw));
</script>
</x-layouts.app>
