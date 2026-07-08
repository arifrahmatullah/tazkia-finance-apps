<x-layouts.app title="Pagu Anggaran">

<div class="flex items-center justify-between mb-5">
    <div>
        <h2 class="text-lg font-bold text-slate-900 m-0">Pagu Anggaran</h2>
        <p class="text-xs text-slate-400 m-0">Alokasi anggaran per departemen dalam satu periode</p>
    </div>
    @if($selectedPeriod)
    <a href="{{ route('budget-allocations.create', ['budget_period_id' => $selectedPeriod->id]) }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-gradient-to-br from-orange-400 to-orange-500 text-white text-sm font-semibold shadow-sm hover:-translate-y-px transition-all no-underline">
        <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg>
        Tambah Pagu
    </a>
    @endif
</div>

@if(session('success'))
<div class="flex items-center gap-2.5 px-4 py-3 bg-green-50 border border-green-200 rounded-xl mb-4 text-sm text-green-700">
    <svg width="16" height="16" fill="#16a34a" viewBox="0 0 20 20" class="shrink-0"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
    {{ session('success') }}
</div>
@endif

{{-- Period selector --}}
<form method="GET" action="{{ route('budget-allocations.index') }}">
    <div class="bg-white rounded-xl shadow-sm border border-slate-100 px-5 py-4 mb-4 flex items-center gap-3 flex-wrap">
        <span class="text-sm font-semibold text-slate-500 whitespace-nowrap">Periode:</span>
        <select name="budget_period_id" class="flex-1 min-w-[200px] px-3 py-2 border border-slate-200 rounded-xl text-sm text-slate-700 bg-white outline-none focus:border-orange-400 transition-colors">
            @forelse($periods as $period)
                <option value="{{ $period->id }}" {{ $selectedPeriod?->id == $period->id ? 'selected' : '' }}>
                    ({{ $period->code }}) {{ $period->name }}
                    {{ $period->is_active ? '— Aktif' : '' }}
                </option>
            @empty
                <option value="">Tidak ada periode</option>
            @endforelse
        </select>
        <button type="submit" class="px-4 py-2 rounded-xl text-sm font-medium bg-slate-100 text-slate-500 border-0 cursor-pointer hover:bg-slate-200 transition-colors">Pilih</button>

        @if($selectedPeriod)
        @php $sisa = $totalEstimate - $totalNett; @endphp
        <div class="ml-auto flex gap-5">
            <div class="text-right">
                <div class="text-[11px] text-slate-400 uppercase tracking-wide">Estimasi Pendapatan</div>
                <div class="text-base font-bold text-slate-700">Rp {{ number_format($totalEstimate, 0, ',', '.') }}</div>
            </div>
            <div class="text-right">
                <div class="text-[11px] text-slate-400 uppercase tracking-wide">Total NETT Aktif</div>
                <div class="text-base font-bold text-slate-900">Rp {{ number_format($totalNett, 0, ',', '.') }}</div>
            </div>
            <div class="text-right">
                <div class="text-[11px] text-slate-400 uppercase tracking-wide">Sisa</div>
                <div class="text-base font-bold {{ $sisa < 0 ? 'text-red-600' : 'text-green-600' }}">
                    Rp {{ number_format($sisa, 0, ',', '.') }}
                </div>
            </div>
        </div>
        @endif
    </div>

    {{-- Search --}}
    @if($selectedPeriod)
    <div class="flex gap-2.5 flex-wrap items-center mb-4">
        <div class="relative flex-1">
            <svg width="15" height="15" fill="none" stroke="#94a3b8" stroke-width="2" viewBox="0 0 24 24" class="absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama atau kode departemen..."
                class="w-full pl-9 pr-3 py-2 border border-slate-200 rounded-xl text-sm text-slate-700 bg-white outline-none focus:border-orange-400 transition-colors">
            <input type="hidden" name="budget_period_id" value="{{ $selectedPeriod->id }}">
        </div>
        <button type="submit" class="px-4 py-2 rounded-xl text-sm font-semibold bg-gradient-to-br from-orange-400 to-orange-500 text-white border-0 cursor-pointer">Cari</button>
        @if(request('search'))
            <a href="{{ route('budget-allocations.index', ['budget_period_id' => $selectedPeriod->id]) }}"
                class="px-3.5 py-2 rounded-xl border border-slate-200 text-sm text-slate-500 no-underline bg-white">
                Reset
            </a>
        @endif
    </div>
    @endif
</form>

@if(!$selectedPeriod)
<div class="bg-white rounded-xl shadow-sm overflow-hidden">
    <div class="px-4 py-12 text-center">
        <svg width="40" height="40" fill="none" stroke="#cbd5e1" stroke-width="1.5" viewBox="0 0 24 24" class="mx-auto mb-2.5 block"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
        <p class="text-sm text-slate-400 m-0">Tidak ada periode anggaran. Buat periode terlebih dahulu di menu <a href="{{ route('budget-periods.index') }}" class="text-orange-500">Periode Anggaran</a>.</p>
    </div>
</div>
@else
<div class="bg-white rounded-xl shadow-sm overflow-hidden">
    <div class="px-5 py-3.5 border-b border-slate-100 flex items-center justify-between">
        <div class="flex items-center gap-2">
            <span class="text-sm font-bold text-slate-900">Alokasi Pagu</span>
            @if($selectedPeriod->is_active)
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-green-100 text-green-700">Periode Aktif</span>
            @else
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-slate-100 text-slate-500">Periode Tidak Aktif</span>
            @endif
        </div>
        <span class="bg-slate-100 text-slate-500 text-[11px] font-semibold px-2.5 py-0.5 rounded-full">{{ $allocations->count() }} departemen aktif</span>
    </div>

    @if($allocations->isEmpty())
        <div class="px-4 py-12 text-center text-slate-400">
            <svg width="40" height="40" fill="none" stroke="#cbd5e1" stroke-width="1.5" viewBox="0 0 24 24" class="mx-auto mb-2.5 block"><path d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            <p class="text-sm m-0">Belum ada pagu aktif untuk periode ini.</p>
        </div>
    @else
    <table class="w-full border-collapse">
        <thead>
            <tr class="bg-slate-50 border-b border-slate-100">
                <th class="px-4 py-3 w-8"></th>
                <th class="px-4 py-3 text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wide">#</th>
                <th class="px-4 py-3 text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wide">Departemen</th>
                <th class="px-4 py-3 text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wide">Kode</th>
                <th class="px-4 py-3 text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wide">Sumber</th>
                <th class="px-4 py-3 text-right text-[11px] font-semibold text-slate-400 uppercase tracking-wide">Pagu (Rp)</th>
                <th class="px-4 py-3 text-right text-[11px] font-semibold text-slate-400 uppercase tracking-wide">Toleransi</th>
                <th class="px-4 py-3 text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wide">Blokir</th>
                <th class="px-4 py-3 text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wide">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @foreach($allocations as $i => $alloc)
            @php $hasPrograms = $alloc->programs->isNotEmpty(); @endphp

            {{-- Allocation row --}}
            <tr class="border-b border-slate-100 hover:bg-slate-50/70 transition-colors cursor-pointer"
                onclick="togglePrograms('{{ $alloc->id }}')">
                <td class="px-3 py-3 text-center align-middle">
                    <svg id="arrow-{{ $alloc->id }}" width="14" height="14" fill="none" stroke="#94a3b8" stroke-width="2.5" viewBox="0 0 24 24"
                        class="transition-transform duration-200 mx-auto">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                    </svg>
                </td>
                <td class="px-4 py-3 text-xs text-slate-400 align-middle">{{ $i + 1 }}</td>
                <td class="px-4 py-3 text-sm font-semibold text-slate-700 align-middle">
                    {{ $alloc->department?->name ?? '(departemen dihapus)' }}
                    @if($hasPrograms)
                        <span class="ml-1.5 inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold bg-violet-100 text-violet-600">
                            {{ $alloc->programs->count() }} proker
                        </span>
                    @endif
                </td>
                <td class="px-4 py-3 text-sm text-slate-600 align-middle">
                    <span class="font-mono text-xs font-semibold text-slate-500 bg-slate-100 px-2 py-0.5 rounded">{{ $alloc->department?->code ?? '-' }}</span>
                </td>
                <td class="px-4 py-3 text-sm text-slate-600 align-middle">
                    @if($alloc->source === 'NETT')
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-blue-100 text-blue-600">NETT</span>
                    @else
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-yellow-100 text-yellow-700">DEVIASI</span>
                    @endif
                </td>
                <td class="px-4 py-3 text-right font-mono text-sm font-semibold text-slate-900 align-middle">
                    {{ number_format($alloc->amount, 0, ',', '.') }}
                </td>
                <td class="px-4 py-3 text-right font-mono text-sm text-slate-500 align-middle">
                    @if($alloc->source === 'NETT')
                        <span class="text-slate-300">—</span>
                    @else
                        {{ $alloc->percentage ? number_format($alloc->percentage, 2, ',', '.') . '%' : '0%' }}
                    @endif
                </td>
                <td class="px-4 py-3 text-sm text-slate-600 align-middle">
                    @if($alloc->is_blocking)
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-red-100 text-red-600">Ya</span>
                    @else
                        <span class="text-slate-300 text-sm">—</span>
                    @endif
                </td>
                <td class="px-4 py-3 text-sm text-slate-600 align-middle" onclick="event.stopPropagation()">
                    <div class="flex gap-1.5">
                        <a href="{{ route('budget-allocations.edit', $alloc) }}" class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-xs font-medium bg-blue-50 text-blue-600 hover:bg-blue-100 transition-colors no-underline">Edit</a>
                        <form id="del-alloc-{{ $alloc->id }}" method="POST" action="{{ route('budget-allocations.destroy', $alloc) }}">
                            @csrf @method('DELETE')
                        </form>
                        <button type="button" class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-xs font-medium bg-red-50 text-red-600 hover:bg-red-100 transition-colors border-0 cursor-pointer"
                            onclick="confirmDelete('del-alloc-{{ $alloc->id }}', '{{ addslashes($alloc->department?->name ?? '') }}')">
                            Hapus
                        </button>
                    </div>
                </td>
            </tr>

            {{-- Programs expandable panel --}}
            <tr id="panel-{{ $alloc->id }}" class="hidden">
                <td colspan="9" class="px-0 py-0 bg-slate-50/60 border-b border-slate-100">
                    <div class="px-8 py-4">

                        {{-- Programs header --}}
                        <div class="mb-3">
                            <span class="text-xs font-bold text-slate-500 uppercase tracking-wide">Program Kerja — {{ $alloc->department?->name }}</span>
                        </div>

                        @if($alloc->programs->isEmpty())
                            <div class="py-5 text-center text-xs text-slate-400 bg-white rounded-xl border border-slate-100">
                                Belum ada program kerja.
                            </div>
                        @else
                            @php $allocTotal = 0; @endphp
                            <div class="rounded-xl overflow-hidden border border-slate-200">
                            @foreach($alloc->programs as $pi => $prog)
                                @php
                                    $progTotal = $prog->details->sum('total_amount');
                                    $allocTotal += $progTotal;
                                @endphp
                                <div class="bg-white {{ !$loop->last ? 'border-b border-slate-100' : '' }}">
                                    {{-- Program row --}}
                                    <div class="flex items-center gap-3 px-4 py-2.5 hover:bg-slate-50 transition-colors cursor-pointer"
                                         onclick="toggleDetails('dtl-{{ $prog->id }}', 'dtlarrow-{{ $prog->id }}')">
                                        <svg id="dtlarrow-{{ $prog->id }}" width="12" height="12" fill="none" stroke="#94a3b8" stroke-width="2.5" viewBox="0 0 24 24" class="transition-transform duration-200 shrink-0">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                                        </svg>
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center gap-2 flex-wrap">
                                                <span class="text-sm font-semibold text-slate-800">{{ $prog->name }}</span>
                                                @if($prog->account)
                                                    <span class="font-mono text-[10px] text-green-700 bg-green-50 border border-green-100 px-2 py-0.5 rounded-full">
                                                        {{ $prog->account->code }} — {{ $prog->account->name }}
                                                    </span>
                                                @else
                                                    <span class="text-[10px] text-slate-400 italic">Akun belum dipilih</span>
                                                @endif
                                            </div>
                                            <div class="text-[11px] text-slate-400 mt-0.5">{{ $prog->details->count() }} rincian</div>
                                        </div>
                                        <div class="text-right shrink-0">
                                            <div class="font-mono text-sm font-bold text-violet-700">Rp {{ number_format($progTotal, 0, ',', '.') }}</div>
                                        </div>
                                        <div class="flex gap-1 shrink-0" onclick="event.stopPropagation()">
                                            <a href="{{ route('budget-programs.edit', $prog) }}"
                                               class="inline-flex items-center px-2 py-1 rounded text-[11px] font-medium bg-blue-50 text-blue-600 hover:bg-blue-100 transition-colors no-underline">Edit</a>
                                            <form id="del-prog-{{ $prog->id }}" method="POST" action="{{ route('budget-programs.destroy', $prog) }}">
                                                @csrf @method('DELETE')
                                            </form>
                                            <button type="button"
                                                class="inline-flex items-center px-2 py-1 rounded text-[11px] font-medium bg-red-50 text-red-600 hover:bg-red-100 transition-colors border-0 cursor-pointer"
                                                onclick="confirmDelete('del-prog-{{ $prog->id }}', '{{ addslashes($prog->name) }}')">Hapus</button>
                                        </div>
                                    </div>

                                    {{-- Details sub-table --}}
                                    <div id="dtl-{{ $prog->id }}" class="hidden border-t border-slate-100">
                                        @if($prog->details->isEmpty())
                                            <div class="px-10 py-3 text-xs text-slate-400 italic">Belum ada rincian.</div>
                                        @else
                                        <div class="overflow-x-auto">
                                            <table class="w-full border-collapse">
                                                <thead>
                                                    <tr class="bg-slate-50">
                                                        <th class="px-10 py-2 text-left text-[10px] font-semibold text-slate-400 uppercase tracking-wide">Deskripsi</th>
                                                        <th class="px-4 py-2 text-right text-[10px] font-semibold text-slate-400 uppercase tracking-wide">Qty</th>
                                                        <th class="px-4 py-2 text-left text-[10px] font-semibold text-slate-400 uppercase tracking-wide">Satuan</th>
                                                        <th class="px-4 py-2 text-right text-[10px] font-semibold text-slate-400 uppercase tracking-wide">Harga Satuan</th>
                                                        <th class="px-4 py-2 text-right text-[10px] font-semibold text-slate-400 uppercase tracking-wide">Total</th>
                                                        <th class="px-4 py-2"></th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($prog->details as $di => $dtl)
                                                    <tr class="border-t border-slate-100 hover:bg-slate-50/50">
                                                        <td class="px-10 py-2 text-xs text-slate-700 font-medium">{{ $dtl->description }}</td>
                                                        <td class="px-4 py-2 text-right text-xs font-mono text-slate-600">{{ number_format($dtl->quantity, 0, ',', '.') }}</td>
                                                        <td class="px-4 py-2 text-xs text-slate-500">{{ $dtl->unit ?? '—' }}</td>
                                                        <td class="px-4 py-2 text-right text-xs font-mono text-slate-600">{{ number_format($dtl->unit_price, 0, ',', '.') }}</td>
                                                        <td class="px-4 py-2 text-right text-xs font-mono font-bold text-violet-700">{{ number_format($dtl->total_amount, 0, ',', '.') }}</td>
                                                        <td class="px-4 py-2">
                                                            <div class="flex gap-1">
                                                                <a href="{{ route('budget-program-details.edit', $dtl) }}"
                                                                   class="inline-flex items-center px-2 py-1 rounded text-[10px] font-medium bg-blue-50 text-blue-600 hover:bg-blue-100 no-underline">Edit</a>
                                                                <form id="del-dtl-{{ $dtl->id }}" method="POST" action="{{ route('budget-program-details.destroy', $dtl) }}">
                                                                    @csrf @method('DELETE')
                                                                </form>
                                                                <button type="button"
                                                                    class="inline-flex items-center px-2 py-1 rounded text-[10px] font-medium bg-red-50 text-red-600 hover:bg-red-100 border-0 cursor-pointer"
                                                                    onclick="confirmDelete('del-dtl-{{ $dtl->id }}', '{{ addslashes($dtl->description) }}')">Hapus</button>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                        @endif
                                        <div class="px-4 py-2 border-t border-slate-100 text-right">
                                            <a href="{{ route('budget-programs.show', $prog) }}"
                                               class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-[11px] font-semibold bg-violet-50 text-violet-600 hover:bg-violet-100 transition-colors no-underline">
                                                <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg>
                                                Tambah Rincian
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                            </div>

                            {{-- Programs summary --}}
                            @php $progSisa = $alloc->amount - $allocTotal; @endphp
                            <div class="mt-2 flex items-center justify-end gap-6 text-xs text-slate-500 px-1">
                                <span>Total rencana: <span class="font-mono font-bold text-violet-700">Rp {{ number_format($allocTotal, 0, ',', '.') }}</span></span>
                                <span>Sisa pagu: <span class="font-mono font-bold {{ $progSisa < 0 ? 'text-red-600' : 'text-green-600' }}">Rp {{ number_format($progSisa, 0, ',', '.') }}</span></span>
                            </div>
                        @endif
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="bg-slate-50 border-t-2 border-slate-100">
                <td colspan="5" class="px-4 py-3 text-sm text-slate-500 font-bold">Total Semua</td>
                <td class="px-4 py-3 text-right font-mono text-sm font-bold text-slate-900">{{ number_format($totalAmount, 0, ',', '.') }}</td>
                <td colspan="3" class="px-4 py-3 text-xs text-slate-400">NETT Aktif: Rp {{ number_format($totalNett, 0, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>
    @endif
</div>
@endif

<script>
function togglePrograms(id) {
    const panel = document.getElementById('panel-' + id);
    const arrow = document.getElementById('arrow-' + id);
    const isHidden = panel.classList.contains('hidden');
    panel.classList.toggle('hidden', !isHidden);
    arrow.style.transform = isHidden ? 'rotate(90deg)' : '';
}

function toggleDetails(panelId, arrowId) {
    const panel = document.getElementById(panelId);
    const arrow = document.getElementById(arrowId);
    const isHidden = panel.classList.contains('hidden');
    panel.classList.toggle('hidden', !isHidden);
    arrow.style.transform = isHidden ? 'rotate(90deg)' : '';
}
</script>

</x-layouts.app>
