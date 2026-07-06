<x-layouts.app title="Rincian Program Kerja">

<div class="flex items-center justify-between mb-5">
    <div>
        <div class="flex items-center gap-2 mb-1">
            <a href="{{ route('budget-programs.index', ['budget_allocation_id' => $budgetProgram->budgetAllocation->id]) }}"
               class="text-xs text-slate-400 hover:text-orange-500 transition-colors no-underline">← Program Kerja</a>
        </div>
        <h2 class="text-lg font-bold text-slate-900 m-0">{{ $budgetProgram->name }}</h2>
        <p class="text-xs text-slate-400 m-0">
            {{ $budgetProgram->budgetAllocation->department->name }}
            <span class="mx-1 text-slate-300">·</span>
            {{ $budgetProgram->budgetAllocation->budgetPeriod->name }}
        </p>
    </div>
    <a href="{{ route('budget-programs.edit', $budgetProgram) }}"
       class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-blue-50 text-blue-600 text-sm font-semibold hover:bg-blue-100 transition-colors no-underline">
        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
        Edit Program
    </a>
</div>

@if(session('success'))
<div class="flex items-center gap-2.5 px-4 py-3 bg-green-50 border border-green-200 rounded-xl mb-4 text-sm text-green-700">
    <svg width="16" height="16" fill="#16a34a" viewBox="0 0 20 20" class="shrink-0"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
    {{ session('success') }}
</div>
@endif

{{-- Program info card --}}
<div class="bg-white rounded-xl border border-slate-100 shadow-sm px-5 py-4 mb-4 flex flex-wrap gap-6">
    <div>
        <div class="text-[11px] text-slate-400 uppercase tracking-wide font-semibold mb-1">Akun COA</div>
        @if($budgetProgram->account)
            <div class="text-xs font-mono text-slate-500">{{ $budgetProgram->account->code }}</div>
            <div class="text-sm font-semibold text-slate-800">{{ $budgetProgram->account->name }}</div>
        @else
            <span class="text-sm text-slate-400 italic">Belum dipilih</span>
        @endif
    </div>
    <div>
        <div class="text-[11px] text-slate-400 uppercase tracking-wide font-semibold mb-1">Total Rencana</div>
        <div class="text-base font-bold text-blue-700 font-mono">Rp {{ number_format($budgetProgram->total_amount, 0, ',', '.') }}</div>
    </div>
    <div>
        <div class="text-[11px] text-slate-400 uppercase tracking-wide font-semibold mb-1">Status</div>
        @if($budgetProgram->is_active)
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-green-100 text-green-700">Aktif</span>
        @else
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-slate-100 text-slate-500">Nonaktif</span>
        @endif
    </div>
    @if($budgetProgram->notes)
    <div class="w-full">
        <div class="text-[11px] text-slate-400 uppercase tracking-wide font-semibold mb-1">Keterangan</div>
        <div class="text-sm text-slate-600">{{ $budgetProgram->notes }}</div>
    </div>
    @endif
</div>

{{-- Details table --}}
<div class="bg-white rounded-xl shadow-sm overflow-hidden mb-4">
    <div class="px-5 py-3.5 border-b border-slate-100 flex items-center justify-between">
        <span class="text-sm font-bold text-slate-900">Rincian Kegiatan</span>
        <span class="bg-slate-100 text-slate-500 text-[11px] font-semibold px-2.5 py-0.5 rounded-full">{{ $budgetProgram->details->count() }} item</span>
    </div>

    @if($budgetProgram->details->isEmpty())
        <div class="px-4 py-8 text-center text-slate-400 text-sm">
            Belum ada rincian. Tambahkan di bawah.
        </div>
    @else
    <div class="overflow-x-auto">
        <table class="w-full border-collapse">
            <thead>
                <tr class="bg-slate-50 border-b border-slate-100">
                    <th class="px-4 py-3 text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wide">#</th>
                    <th class="px-4 py-3 text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wide">Deskripsi</th>
                    <th class="px-4 py-3 text-right text-[11px] font-semibold text-slate-400 uppercase tracking-wide">Qty</th>
                    <th class="px-4 py-3 text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wide">Satuan</th>
                    <th class="px-4 py-3 text-right text-[11px] font-semibold text-slate-400 uppercase tracking-wide">Harga Satuan</th>
                    <th class="px-4 py-3 text-right text-[11px] font-semibold text-slate-400 uppercase tracking-wide">Total</th>
                    <th class="px-4 py-3 text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wide">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($budgetProgram->details as $i => $detail)
                <tr class="border-b border-slate-50 hover:bg-slate-50 transition-colors">
                    <td class="px-4 py-3 text-xs text-slate-400 align-middle">{{ $i + 1 }}</td>
                    <td class="px-4 py-3 text-sm text-slate-800 font-medium align-middle">
                        {{ $detail->description }}
                        @if($detail->notes)
                            <div class="text-xs text-slate-400 mt-0.5">{{ $detail->notes }}</div>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-right font-mono text-sm text-slate-600 align-middle">
                        {{ number_format($detail->quantity, 0, ',', '.') }}
                    </td>
                    <td class="px-4 py-3 text-sm text-slate-500 align-middle">{{ $detail->unit ?? '—' }}</td>
                    <td class="px-4 py-3 text-right font-mono text-sm text-slate-600 align-middle">
                        {{ number_format($detail->unit_price, 0, ',', '.') }}
                    </td>
                    <td class="px-4 py-3 text-right font-mono text-sm font-bold text-violet-700 align-middle">
                        {{ number_format($detail->total_amount, 0, ',', '.') }}
                    </td>
                    <td class="px-4 py-3 align-middle">
                        <div class="flex gap-1.5">
                            <a href="{{ route('budget-program-details.edit', $detail) }}"
                               class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-xs font-medium bg-blue-50 text-blue-600 hover:bg-blue-100 transition-colors no-underline">Edit</a>
                            <form id="del-dtl-{{ $detail->id }}" method="POST" action="{{ route('budget-program-details.destroy', $detail) }}">
                                @csrf @method('DELETE')
                            </form>
                            <button type="button"
                                class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-xs font-medium bg-red-50 text-red-600 hover:bg-red-100 transition-colors border-0 cursor-pointer"
                                onclick="confirmDelete('del-dtl-{{ $detail->id }}', '{{ addslashes($detail->description) }}')">
                                Hapus
                            </button>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="bg-violet-50 border-t-2 border-violet-100">
                    <td colspan="5" class="px-4 py-3 text-sm font-bold text-violet-700">Total Program</td>
                    <td class="px-4 py-3 text-right font-mono text-sm font-bold text-violet-700">
                        {{ number_format($budgetProgram->total_amount, 0, ',', '.') }}
                    </td>
                    <td></td>
                </tr>
            </tfoot>
        </table>
    </div>
    @endif
</div>

{{-- Add detail form --}}
<div class="bg-white rounded-xl shadow-sm overflow-hidden">
    <div class="px-5 py-3.5 border-b border-slate-100">
        <span class="text-sm font-bold text-slate-900">Tambah Rincian</span>
    </div>
    <form method="POST" action="{{ route('budget-program-details.store') }}" class="px-5 py-5">
        @csrf
        <input type="hidden" name="budget_program_id" value="{{ $budgetProgram->id }}">

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div class="sm:col-span-2">
                <label class="block text-xs font-semibold text-slate-600 mb-1.5">Deskripsi <span class="text-red-500">*</span></label>
                <input type="text" name="description" value="{{ old('description') }}"
                    placeholder="Contoh: Honorarium narasumber, ATK, konsumsi peserta, ..."
                    class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl text-sm text-slate-700 bg-white outline-none focus:border-orange-400 transition-colors @error('description') border-red-400 @enderror">
                @error('description') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1.5">Kuantitas <span class="text-red-500">*</span></label>
                <input type="text" name="quantity" id="qtyInput" value="{{ old('quantity', 1) }}"
                    inputmode="decimal"
                    class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl text-sm text-slate-700 bg-white outline-none focus:border-orange-400 transition-colors @error('quantity') border-red-400 @enderror"
                    oninput="calcTotal()">
                @error('quantity') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1.5">Satuan</label>
                <input type="text" name="unit" value="{{ old('unit') }}"
                    placeholder="Orang, Paket, Hari, Lembar, ..."
                    class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl text-sm text-slate-700 bg-white outline-none focus:border-orange-400 transition-colors">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1.5">Harga Satuan (Rp) <span class="text-red-500">*</span></label>
                <input type="text" id="unitPriceDisplay"
                    placeholder="0"
                    class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl text-sm text-slate-700 bg-white outline-none focus:border-orange-400 transition-colors font-mono @error('unit_price') border-red-400 @enderror"
                    oninput="formatUnitPrice(this)" value="{{ old('unit_price') ? number_format((int)old('unit_price'), 0, ',', '.') : '' }}">
                <input type="hidden" name="unit_price" id="unitPriceHidden" value="{{ old('unit_price', 0) }}">
                @error('unit_price') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1.5">Total</label>
                <div id="totalPreview"
                    class="w-full px-3.5 py-2.5 border border-slate-100 rounded-xl text-sm font-mono font-bold text-violet-700 bg-violet-50">
                    Rp 0
                </div>
            </div>

            <div class="sm:col-span-2">
                <label class="block text-xs font-semibold text-slate-600 mb-1.5">Catatan</label>
                <input type="text" name="notes" value="{{ old('notes') }}"
                    placeholder="Opsional"
                    class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl text-sm text-slate-700 bg-white outline-none focus:border-orange-400 transition-colors">
            </div>
        </div>

        <div class="flex gap-2.5 mt-4">
            <button type="submit"
                class="px-5 py-2.5 rounded-xl bg-gradient-to-br from-orange-400 to-orange-500 text-white text-sm font-semibold border-0 cursor-pointer hover:-translate-y-px transition-all">
                + Tambah Rincian
            </button>
        </div>
    </form>
</div>

<script>
function formatUnitPrice(input) {
    let raw = input.value.replace(/[^\d]/g, '');
    document.getElementById('unitPriceHidden').value = raw || '0';
    input.value = raw ? parseInt(raw).toLocaleString('id-ID') : '';
    calcTotal();
}

function calcTotal() {
    const qty   = parseFloat(document.getElementById('qtyInput').value) || 0;
    const price = parseInt(document.getElementById('unitPriceHidden').value) || 0;
    const total = qty * price;
    document.getElementById('totalPreview').textContent = 'Rp ' + total.toLocaleString('id-ID');
}

// Init on load
window.addEventListener('load', function() {
    const display = document.getElementById('unitPriceDisplay');
    if (display.value) {
        const raw = display.value.replace(/[^\d]/g, '');
        document.getElementById('unitPriceHidden').value = raw;
        calcTotal();
    }
});
</script>

</x-layouts.app>
