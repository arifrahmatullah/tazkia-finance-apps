<x-layouts.app title="Rincian Program Kerja">

@php
    $pagu       = (float) $budgetProgram->budgetAllocation->amount;
    $totalUsed  = (float) $budgetProgram->total_amount;
    $sisa       = $pagu - $totalUsed;
    $paguPct    = $pagu > 0 ? min(100, round($totalUsed / $pagu * 100)) : 0;
    $overBudget = $pagu > 0 && $totalUsed > $pagu;
    $freq       = max(1, (int) $budgetProgram->frequency);
    $schedules  = $budgetProgram->schedules;
    $filledCount = $schedules->whereNotNull('estimated_date')->count();
@endphp

<div class="flex items-center justify-between mb-5">
    <div>
        <a href="{{ route('budget-programs.index') }}" class="inline-flex items-center gap-1.5 text-xs text-slate-400 hover:text-orange-500 no-underline transition-colors mb-1">
            <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M19 12H5M12 5l-7 7 7 7"/></svg>
            Program Kerja
        </a>
        <h2 class="text-lg font-bold text-slate-900 m-0">{{ $budgetProgram->name }}</h2>
        <p class="text-xs text-slate-400 m-0">
            {{ $budgetProgram->budgetAllocation->department->name }}
            <span class="mx-1 text-slate-300">·</span>
            {{ $budgetProgram->budgetAllocation->budgetPeriod->name }}
            <span class="mx-1 text-slate-300">·</span>
            <span class="font-semibold text-slate-500">{{ $freq }}× per periode</span>
        </p>
    </div>
    <a href="{{ route('budget-programs.edit', $budgetProgram) }}"
       class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-slate-100 text-slate-600 text-sm font-semibold hover:bg-slate-200 transition-colors no-underline">
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

{{-- Pagu summary --}}
<div class="bg-white rounded-xl border border-slate-100 shadow-sm px-5 py-4 mb-4">
    <div class="flex items-center gap-5 flex-wrap">
        <div>
            <div class="text-[11px] text-slate-400 uppercase tracking-wide font-semibold">Pagu Alokasi</div>
            <div class="text-base font-bold text-slate-800 font-mono">Rp {{ number_format($pagu, 0, ',', '.') }}</div>
        </div>
        <div class="w-px h-8 bg-slate-100"></div>
        <div>
            <div class="text-[11px] text-slate-400 uppercase tracking-wide font-semibold">Total Program</div>
            <div class="text-base font-bold {{ $overBudget ? 'text-red-600' : 'text-orange-600' }} font-mono">Rp {{ number_format($totalUsed, 0, ',', '.') }}</div>
        </div>
        <div class="w-px h-8 bg-slate-100"></div>
        <div>
            <div class="text-[11px] text-slate-400 uppercase tracking-wide font-semibold">Sisa</div>
            <div class="text-base font-bold {{ $sisa < 0 ? 'text-red-600' : 'text-green-600' }} font-mono">Rp {{ number_format($sisa, 0, ',', '.') }}</div>
        </div>
        <div class="w-px h-8 bg-slate-100"></div>
        <div>
            <div class="text-[11px] text-slate-400 uppercase tracking-wide font-semibold">Per Termin</div>
            <div class="text-base font-bold text-slate-700 font-mono">Rp {{ number_format($freq > 0 ? $totalUsed / $freq : 0, 0, ',', '.') }}</div>
        </div>
    </div>
    @if($pagu > 0)
    <div class="w-full bg-slate-100 rounded-full h-2 mt-3">
        <div class="h-2 rounded-full transition-all {{ $overBudget ? 'bg-red-500' : ($paguPct > 80 ? 'bg-orange-500' : 'bg-green-500') }}"
            style="width: {{ $paguPct }}%"></div>
    </div>
    <div class="text-xs text-slate-400 mt-1">{{ $paguPct }}% terpakai dari pagu alokasi</div>
    @endif
    @if($overBudget)
    <div class="mt-2 text-xs font-semibold text-red-600">⚠ Total rincian melebihi pagu anggaran!</div>
    @endif
</div>

{{-- Rincian table --}}
<div class="bg-white rounded-xl shadow-sm overflow-hidden mb-4">
    <div class="px-5 py-3.5 border-b border-slate-100 flex items-center justify-between">
        <span class="text-sm font-bold text-slate-900">Rincian Kegiatan</span>
        <span class="bg-slate-100 text-slate-500 text-[11px] font-semibold px-2.5 py-0.5 rounded-full">{{ $budgetProgram->details->count() }} item</span>
    </div>

    @if($budgetProgram->details->isEmpty())
        <div class="px-4 py-8 text-center text-slate-400 text-sm">Belum ada rincian.</div>
    @else
    <div class="overflow-x-auto">
        <table class="w-full border-collapse">
            <thead>
                <tr class="bg-slate-50 border-b border-slate-100">
                    <th class="px-4 py-3 text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wide w-8">#</th>
                    <th class="px-4 py-3 text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wide">Jenis Pengeluaran</th>
                    <th class="px-4 py-3 text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wide">Deskripsi</th>
                    <th class="px-4 py-3 text-right text-[11px] font-semibold text-slate-400 uppercase tracking-wide">Nominal/Termin</th>
                    <th class="px-4 py-3 text-right text-[11px] font-semibold text-slate-400 uppercase tracking-wide">Total (×{{ $freq }})</th>
                    <th class="px-4 py-3 w-[80px]"></th>
                </tr>
            </thead>
            <tbody>
                @foreach($budgetProgram->details as $i => $detail)
                <tr class="border-b border-slate-50 hover:bg-slate-50 transition-colors">
                    <td class="px-4 py-3 text-xs text-slate-400 align-middle">{{ $i + 1 }}</td>
                    <td class="px-4 py-3 align-middle">
                        @if($detail->account)
                            <div class="text-[11px] font-mono text-slate-400">{{ $detail->account->code }}</div>
                            <div class="text-xs text-slate-600">{{ $detail->account->name }}</div>
                        @else
                            <span class="text-slate-300 text-xs">—</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-sm text-slate-800 font-medium align-middle">{{ $detail->description }}</td>
                    <td class="px-4 py-3 text-right font-mono text-sm text-slate-600 align-middle">
                        Rp {{ number_format($detail->unit_price, 0, ',', '.') }}
                    </td>
                    <td class="px-4 py-3 text-right font-mono text-sm font-bold text-orange-700 align-middle">
                        Rp {{ number_format($detail->total_amount, 0, ',', '.') }}
                    </td>
                    <td class="px-4 py-3 align-middle">
                        <div class="flex gap-1.5 justify-end">
                            <a href="{{ route('budget-program-details.edit', $detail) }}"
                               class="inline-flex items-center p-1.5 rounded-lg text-slate-400 hover:text-blue-500 hover:bg-blue-50 transition-colors no-underline" title="Edit">
                                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            </a>
                            <form id="del-dtl-{{ $detail->id }}" method="POST" action="{{ route('budget-program-details.destroy', $detail) }}">@csrf @method('DELETE')</form>
                            <button type="button" onclick="confirmDelete('del-dtl-{{ $detail->id }}', '{{ addslashes($detail->description) }}')"
                                class="inline-flex items-center p-1.5 rounded-lg text-slate-300 hover:text-red-500 hover:bg-red-50 transition-colors border-0 bg-transparent cursor-pointer" title="Hapus">
                                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="bg-orange-50 border-t-2 border-orange-100">
                    <td colspan="4" class="px-4 py-3 text-sm font-bold text-orange-700">Total Program</td>
                    <td class="px-4 py-3 text-right font-mono text-sm font-bold text-orange-700">Rp {{ number_format($totalUsed, 0, ',', '.') }}</td>
                    <td></td>
                </tr>
            </tfoot>
        </table>
    </div>
    @endif
</div>

{{-- Estimasi Jadwal --}}
<div class="bg-white rounded-xl shadow-sm overflow-hidden mb-4">
    <div class="px-5 py-3.5 border-b border-slate-100 flex items-center justify-between flex-wrap gap-2">
        <div class="flex items-center gap-2.5">
            <span class="text-sm font-bold text-slate-900">Estimasi Jadwal Pengeluaran</span>
            <span class="text-[11px] font-semibold px-2 py-0.5 rounded-full {{ $filledCount === $freq ? 'bg-green-100 text-green-700' : 'bg-orange-100 text-orange-600' }}">
                {{ $filledCount }}/{{ $freq }} terisi
            </span>
        </div>
        <button type="button" onclick="openAutoFill()"
            class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl border border-orange-200 bg-orange-50 text-orange-600 text-xs font-semibold hover:bg-orange-100 transition-colors cursor-pointer">
            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            Isi Otomatis
        </button>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full border-collapse">
            <thead>
                <tr class="bg-slate-50 border-b border-slate-100">
                    <th class="px-4 py-3 text-center text-[11px] font-semibold text-slate-400 uppercase tracking-wide w-16">Termin</th>
                    <th class="px-4 py-3 text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wide w-[160px]"></th>
                    <th class="px-4 py-3 text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wide">Tanggal Estimasi</th>
                    <th class="px-4 py-3 text-right text-[11px] font-semibold text-slate-400 uppercase tracking-wide">Nominal</th>
                    <th class="px-4 py-3 text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wide">Catatan</th>
                </tr>
            </thead>
            <tbody>
                @php $nominalPerTermin = $freq > 0 ? $totalUsed / $freq : 0; @endphp
                @forelse($schedules as $sch)
                <tr class="border-b border-slate-50 hover:bg-slate-50/60 transition-colors" id="row-{{ $sch->id }}">
                    <td class="px-4 py-3 text-center align-middle">
                        <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-slate-100 text-slate-600 text-xs font-bold">{{ $sch->termin }}</span>
                    </td>
                    <td class="px-4 py-3 align-middle">
                        <button type="button" onclick="openEdit('{{ $sch->id }}', '{{ $sch->estimated_date?->format('Y-m-d') ?? '' }}', '{{ addslashes($sch->notes ?? '') }}', {{ $sch->termin }})"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold border-0 cursor-pointer transition-colors {{ $sch->estimated_date ? 'bg-blue-50 text-blue-600 hover:bg-blue-100' : 'bg-orange-50 text-orange-500 hover:bg-orange-100' }}">
                            <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            {{ $sch->estimated_date ? 'Edit tanggal' : 'Isi tanggal' }}
                        </button>
                    </td>
                    <td class="px-4 py-3 align-middle">
                        @if($sch->estimated_date)
                            <span class="text-sm text-slate-700 font-semibold">{{ $sch->estimated_date->format('d M Y') }}</span>
                        @else
                            <span class="text-xs text-slate-300 italic">Belum diisi</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-right font-mono text-sm text-slate-700 align-middle">
                        Rp {{ number_format($nominalPerTermin, 0, ',', '.') }}
                    </td>
                    <td class="px-4 py-3 align-middle text-sm text-slate-500">{{ $sch->notes ?? '—' }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-4 py-6 text-center text-slate-400 text-sm">Belum ada jadwal. Coba simpan ulang program.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Tambah rincian --}}
@if(!$overBudget)
<div class="bg-white rounded-xl shadow-sm overflow-hidden">
    <div class="px-5 py-3.5 border-b border-slate-100 flex items-center justify-between">
        <span class="text-sm font-bold text-slate-900">Tambah Rincian</span>
        @if($pagu > 0)
        <span class="text-xs text-slate-400">Sisa: <span class="font-semibold text-green-600 font-mono">Rp {{ number_format($sisa, 0, ',', '.') }}</span></span>
        @endif
    </div>
    <form method="POST" action="{{ route('budget-program-details.store') }}" class="px-5 py-5">
        @csrf
        <input type="hidden" name="budget_program_id" value="{{ $budgetProgram->id }}">

        @if($errors->any())
        <div class="mb-4 px-4 py-3 bg-red-50 border border-red-200 rounded-xl text-sm text-red-700">{{ $errors->first() }}</div>
        @endif

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1.5">Jenis Pengeluaran</label>
                <select name="account_id"
                    class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl text-sm text-slate-700 bg-white outline-none focus:border-orange-400 transition-colors">
                    <option value="">— Pilih jenis pengeluaran —</option>
                    @foreach($accounts as $account)
                        <option value="{{ $account->id }}" {{ old('account_id') == $account->id ? 'selected' : '' }}>
                            {{ $account->code }} — {{ $account->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1.5">Deskripsi <span class="text-red-500">*</span></label>
                <input type="text" name="description" value="{{ old('description') }}"
                    placeholder="Transportasi, Konsumsi, ..."
                    class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl text-sm text-slate-700 bg-white outline-none focus:border-orange-400 transition-colors @error('description') border-red-400 @enderror">
                @error('description') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1.5">
                    Nominal/Termin (Rp) <span class="text-red-500">*</span>
                    <span class="font-normal text-slate-400">× {{ $freq }}×</span>
                </label>
                <input type="text" id="nominalDisplay" placeholder="0"
                    class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl text-sm text-slate-700 bg-white outline-none focus:border-orange-400 transition-colors font-mono @error('unit_price') border-red-400 @enderror"
                    oninput="fmtNominal(this)" value="{{ old('unit_price') ? number_format((int)old('unit_price'), 0, ',', '.') : '' }}">
                <input type="hidden" name="unit_price" id="nominalHidden" value="{{ old('unit_price', 0) }}">
                @error('unit_price') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="flex items-center gap-3 mt-4">
            <button type="submit"
                class="px-5 py-2.5 rounded-xl bg-gradient-to-br from-orange-400 to-orange-500 text-white text-sm font-semibold border-0 cursor-pointer hover:-translate-y-px transition-all">
                + Tambah
            </button>
            @if($pagu > 0)
            <span id="budgetWarning" class="text-xs text-red-500 hidden">⚠ Melebihi sisa pagu!</span>
            @endif
        </div>
    </form>
</div>
@endif

{{-- Modal: Edit tanggal per termin --}}
<div id="modal-edit" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4" style="background:rgba(0,0,0,.35)">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-sm p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-sm font-bold text-slate-900" id="modal-edit-title">Edit Termin</h3>
            <button type="button" onclick="closeEdit()" class="text-slate-400 hover:text-slate-600 border-0 bg-transparent cursor-pointer text-lg leading-none">×</button>
        </div>
        <div class="flex flex-col gap-3">
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1.5">Tanggal Estimasi</label>
                <input type="date" id="edit-date" class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl text-sm outline-none focus:border-orange-400 transition-colors">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1.5">Catatan (opsional)</label>
                <input type="text" id="edit-notes" placeholder="Pembayaran 1" class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl text-sm outline-none focus:border-orange-400 transition-colors">
            </div>
        </div>
        <div class="flex gap-2.5 mt-5">
            <button type="button" onclick="saveEdit()"
                class="flex-1 px-4 py-2.5 rounded-xl bg-gradient-to-br from-orange-400 to-orange-500 text-white text-sm font-semibold border-0 cursor-pointer hover:-translate-y-px transition-all">
                Simpan
            </button>
            <button type="button" onclick="closeEdit()"
                class="px-4 py-2.5 rounded-xl border border-slate-200 text-sm text-slate-600 bg-white cursor-pointer hover:bg-slate-50 transition-colors">
                Batal
            </button>
        </div>
    </div>
</div>

{{-- Modal: Isi Otomatis --}}
<div id="modal-autofill" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4" style="background:rgba(0,0,0,.35)">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-sm p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-sm font-bold text-slate-900">Isi Otomatis Jadwal</h3>
            <button type="button" onclick="closeAutoFill()" class="text-slate-400 hover:text-slate-600 border-0 bg-transparent cursor-pointer text-lg leading-none">×</button>
        </div>
        <form method="POST" action="{{ route('budget-program-schedules.bulk', $budgetProgram) }}" id="form-autofill">
            @csrf
            <div class="flex flex-col gap-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">Tanggal Termin Pertama <span class="text-red-500">*</span></label>
                    <input type="date" name="start_date" id="af-start" required
                        class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl text-sm outline-none focus:border-orange-400 transition-colors">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">Pola Pengulangan</label>
                    <select name="pattern" id="af-pattern"
                        class="no-select2 w-full px-3.5 py-2.5 border border-slate-200 rounded-xl text-sm text-slate-700 bg-white outline-none focus:border-orange-400 transition-colors"
                        onchange="toggleCustomInterval(this.value)">
                        <option value="monthly">Bulanan (tiap 1 bulan)</option>
                        <option value="weekly">Mingguan (tiap 1 minggu)</option>
                        <option value="quarterly">Triwulan (tiap 3 bulan)</option>
                        <option value="custom">Kustom (interval hari)</option>
                    </select>
                </div>
                <div id="custom-interval-row" class="hidden">
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">Interval (hari)</label>
                    <input type="number" name="interval" id="af-interval" value="30" min="1"
                        class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl text-sm outline-none focus:border-orange-400 transition-colors font-mono">
                </div>
                <p class="text-xs text-slate-400 mt-0.5">
                    Akan mengisi {{ $freq }} termin mulai dari tanggal yang dipilih. Kamu masih bisa edit manual setelahnya.
                </p>
            </div>
            <div class="flex gap-2.5 mt-5">
                <button type="submit"
                    class="flex-1 px-4 py-2.5 rounded-xl bg-gradient-to-br from-orange-400 to-orange-500 text-white text-sm font-semibold border-0 cursor-pointer hover:-translate-y-px transition-all">
                    Isi Sekarang
                </button>
                <button type="button" onclick="closeAutoFill()"
                    class="px-4 py-2.5 rounded-xl border border-slate-200 text-sm text-slate-600 bg-white cursor-pointer hover:bg-slate-50 transition-colors">
                    Batal
                </button>
            </div>
        </form>
    </div>
</div>

<script>
const sisaPagu = {{ $sisa }};
const hasPagu  = {{ $pagu > 0 ? 'true' : 'false' }};

function fmtNominal(input) {
    const raw = input.value.replace(/[^\d]/g, '');
    document.getElementById('nominalHidden').value = raw || '0';
    input.value = raw ? parseInt(raw).toLocaleString('id-ID') : '';
    if (hasPagu) {
        const val  = parseInt(raw) || 0;
        const warn = document.getElementById('budgetWarning');
        if (warn) warn.classList.toggle('hidden', val <= sisaPagu);
    }
}

// ----- Edit modal -----
let editingId = null;

function openEdit(id, date, notes, termin) {
    editingId = id;
    document.getElementById('edit-date').value  = date || '';
    document.getElementById('edit-notes').value = notes || '';
    document.getElementById('edit-notes').placeholder = 'Pembayaran ' + termin;
    document.getElementById('modal-edit').classList.remove('hidden');
}

function closeEdit() {
    document.getElementById('modal-edit').classList.add('hidden');
    editingId = null;
}

function saveEdit() {
    if (!editingId) return;
    const date  = document.getElementById('edit-date').value;
    const notes = document.getElementById('edit-notes').value;

    fetch(`/budget-program-schedules/${editingId}`, {
        method: 'PATCH',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
                         || '{{ csrf_token() }}',
        },
        body: JSON.stringify({ estimated_date: date || null, notes: notes || null }),
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            closeEdit();
            window.location.reload();
        }
    })
    .catch(() => alert('Gagal menyimpan. Coba lagi.'));
}

document.getElementById('modal-edit').addEventListener('click', function(e) {
    if (e.target === this) closeEdit();
});

// ----- Auto-fill modal -----
function openAutoFill() {
    document.getElementById('modal-autofill').classList.remove('hidden');
}

function closeAutoFill() {
    document.getElementById('modal-autofill').classList.add('hidden');
}

function toggleCustomInterval(val) {
    document.getElementById('custom-interval-row').classList.toggle('hidden', val !== 'custom');
}

document.getElementById('modal-autofill').addEventListener('click', function(e) {
    if (e.target === this) closeAutoFill();
});
</script>

</x-layouts.app>
