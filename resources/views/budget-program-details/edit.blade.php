<x-layouts.app title="Edit Rincian">

<div class="flex items-center justify-between mb-5">
    <div>
        <a href="{{ route('budget-programs.show', $budgetProgramDetail->budgetProgram) }}"
           class="inline-flex items-center gap-1.5 text-xs text-slate-400 hover:text-orange-500 no-underline transition-colors mb-1">
            <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M19 12H5M12 5l-7 7 7 7"/></svg>
            {{ $budgetProgramDetail->budgetProgram->name }}
        </a>
        <h2 class="text-lg font-bold text-slate-900 m-0">Edit Rincian</h2>
    </div>
</div>

<div class="max-w-xl">
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="px-5 py-3.5 border-b border-slate-100">
            <span class="text-sm font-bold text-slate-900">Detail Rincian</span>
        </div>
        <form method="POST" action="{{ route('budget-program-details.update', $budgetProgramDetail) }}" class="px-5 py-5 flex flex-col gap-4">
            @csrf @method('PUT')

            @if($errors->any())
            <div class="px-4 py-3 bg-red-50 border border-red-200 rounded-xl text-sm text-red-700">{{ $errors->first() }}</div>
            @endif

            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1.5">Jenis Pengeluaran</label>
                <select name="account_id"
                    class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl text-sm text-slate-700 bg-white outline-none focus:border-orange-400 transition-colors">
                    <option value="">— Pilih jenis pengeluaran —</option>
                    @foreach($accounts as $account)
                        <option value="{{ $account->id }}"
                            {{ old('account_id', $budgetProgramDetail->account_id) == $account->id ? 'selected' : '' }}>
                            {{ $account->code }} — {{ $account->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1.5">Deskripsi <span class="text-red-500">*</span></label>
                <input type="text" name="description" value="{{ old('description', $budgetProgramDetail->description) }}"
                    class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl text-sm text-slate-700 bg-white outline-none focus:border-orange-400 transition-colors @error('description') border-red-400 @enderror">
                @error('description') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>

            @php
                $editSchedules = $budgetProgramDetail->budgetProgram->schedules;
                $editFreq = $budgetProgramDetail->budgetProgram->frequency;
                $isCustomTermin = $budgetProgramDetail->termins->isNotEmpty();
                $currentTerminIds = old('termin_ids', $budgetProgramDetail->termins->pluck('id')->all());
                $effFreq = $isCustomTermin ? count($currentTerminIds) : $editFreq;
            @endphp

            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1.5">
                    Nominal/Termin (Rp) <span class="text-red-500">*</span>
                    <span class="font-normal text-slate-400">× <span id="freqLabel">{{ $effFreq }}</span>× {{ $isCustomTermin ? '(custom termin)' : '(frekuensi program)' }}</span>
                </label>
                <input type="text" id="nominalDisplay"
                    value="{{ old('unit_price') ? number_format((int)old('unit_price'), 0, ',', '.') : number_format((int)$budgetProgramDetail->unit_price, 0, ',', '.') }}"
                    class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl text-sm text-slate-700 bg-white outline-none focus:border-orange-400 transition-colors font-mono @error('unit_price') border-red-400 @enderror"
                    oninput="fmtNominal(this)" onfocus="this.select()">
                <input type="hidden" name="unit_price" id="nominalHidden"
                    value="{{ old('unit_price', (int)$budgetProgramDetail->unit_price) }}">
                <div class="text-[11px] text-slate-400 mt-1">Total <span id="nominalTotal" class="font-semibold text-slate-600"></span> untuk <span id="freqLabel2">{{ $effFreq }}</span> termin. Ini nominal per termin, bukan total keseluruhan.</div>
                @error('unit_price') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>

            @if($editFreq > 1)
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1.5">
                    Frekuensi Rincian <span class="text-red-500">*</span>
                    <span class="font-normal text-slate-400 ml-1">maks. {{ $editFreq }}× (ikut frekuensi program)</span>
                </label>
                <div class="flex items-center gap-2 flex-wrap">
                    <input type="number" id="rincian-freq-input" min="1" max="{{ $editFreq }}" value="{{ $effFreq }}"
                        class="w-20 px-3.5 py-2.5 border border-slate-200 rounded-xl text-sm text-slate-700 bg-white outline-none focus:border-orange-400 transition-colors font-mono"
                        oninput="applyFreqToCheckboxes(parseInt(this.value) || 1)">
                    <div class="flex gap-1.5 flex-wrap">
                        @for ($f = 1; $f <= $editFreq; $f++)
                        <button type="button" data-val="{{ $f }}"
                            class="freq-btn-rincian px-3 py-1.5 rounded-lg text-xs font-semibold border transition-colors cursor-pointer
                                {{ $effFreq == $f ? 'bg-orange-500 text-white border-orange-500' : 'bg-white text-slate-600 border-slate-200 hover:border-orange-300 hover:text-orange-600' }}"
                            onclick="applyFreqToCheckboxes({{ $f }})">{{ $f }}×</button>
                        @endfor
                    </div>
                </div>
                <p class="text-[11px] text-slate-400 mt-1.5">Otomatis pakai termin pertama sejumlah ini. Mau pilih termin tertentu (bukan yang pertama)? Atur langsung di checklist di bawah.</p>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1.5">Berlaku di Termin</label>
                <div class="flex flex-wrap gap-3">
                    @foreach($editSchedules as $sch)
                    <label class="inline-flex items-center gap-1.5 text-xs text-slate-600">
                        <input type="checkbox" name="termin_ids[]" value="{{ $sch->id }}"
                            {{ (!$isCustomTermin || in_array($sch->id, $currentTerminIds)) ? 'checked' : '' }}
                            onchange="recalcFreq()" class="w-3.5 h-3.5 accent-orange-500">
                        Termin {{ $sch->termin }}{{ $sch->monthLabel() ? ' ('.$sch->monthLabel().')' : '' }}
                    </label>
                    @endforeach
                </div>
                <input type="hidden" name="termin_ids_submitted" value="1">
                @error('termin_ids') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                <div class="text-[11px] text-slate-400 mt-1">Uncek termin yang tidak dipakai rincian ini (mis. biaya sekali bayar).</div>
            </div>
            @endif

            <div class="flex gap-2.5 pt-1">
                <button type="submit"
                    class="px-5 py-2.5 rounded-xl bg-gradient-to-br from-orange-400 to-orange-500 text-white text-sm font-semibold border-0 cursor-pointer hover:-translate-y-px transition-all">
                    Simpan
                </button>
                <a href="{{ route('budget-programs.show', $budgetProgramDetail->budgetProgram) }}"
                   class="px-5 py-2.5 rounded-xl border border-slate-200 text-sm text-slate-600 no-underline hover:bg-slate-50 transition-colors">
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>

<script>
let freqCount = {{ $effFreq }};

// Isi input "Frekuensi Rincian" -- centang N termin PERTAMA (urut), sisanya diuncek.
// Dipanggil dari input angka / tombol cepat. Kalau mau termin tertentu yang bukan
// urutan pertama, staf tinggal uncek/centang manual di checklist (recalcFreq() di bawah
// yang nyambungin balik ke angka & tombolnya).
function applyFreqToCheckboxes(n) {
    n = Math.max(1, Math.min(n, {{ $editFreq }}));
    const boxes = Array.from(document.querySelectorAll('input[name="termin_ids[]"]'));
    boxes.forEach((box, idx) => { box.checked = idx < n; });
    recalcFreq();
}

// Dihitung ulang dari checklist "Berlaku di Termin" tiap kali dicentang/di-uncek --
// sekaligus sinkronkan balik ke input angka & tombol cepat "Frekuensi Rincian".
function recalcFreq() {
    const boxes = document.querySelectorAll('input[name="termin_ids[]"]');
    if (boxes.length > 0) {
        const checked = document.querySelectorAll('input[name="termin_ids[]"]:checked').length;
        freqCount = checked > 0 ? checked : {{ $editFreq }};
        document.getElementById('freqLabel').textContent = freqCount;
        document.getElementById('freqLabel2').textContent = freqCount;

        const freqInput = document.getElementById('rincian-freq-input');
        if (freqInput) freqInput.value = freqCount;
        document.querySelectorAll('.freq-btn-rincian').forEach(btn => {
            const active = parseInt(btn.dataset.val) === freqCount;
            btn.classList.toggle('bg-orange-500', active);
            btn.classList.toggle('text-white', active);
            btn.classList.toggle('border-orange-500', active);
            btn.classList.toggle('bg-white', !active);
            btn.classList.toggle('text-slate-600', !active);
            btn.classList.toggle('border-slate-200', !active);
        });
    }
    fmtNominal(document.getElementById('nominalDisplay'));
}

function fmtNominal(input) {
    const raw = input.value.replace(/[^\d]/g, '');
    document.getElementById('nominalHidden').value = raw || '0';
    input.value = raw ? parseInt(raw).toLocaleString('id-ID') : '';
    const total = (raw ? parseInt(raw) : 0) * freqCount;
    document.getElementById('nominalTotal').textContent = 'Rp ' + total.toLocaleString('id-ID');
}
document.addEventListener('DOMContentLoaded', () => fmtNominal(document.getElementById('nominalDisplay')));
</script>

</x-layouts.app>
