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
        <div class="ml-auto text-right">
            <div class="text-[11px] text-slate-400 uppercase tracking-wide">Total Pagu</div>
            <div class="text-lg font-bold text-slate-900">Rp {{ number_format($totalAmount, 0, ',', '.') }}</div>
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
        <span class="bg-slate-100 text-slate-500 text-[11px] font-semibold px-2.5 py-0.5 rounded-full">{{ $allocations->count() }} departemen</span>
    </div>

    @if($allocations->isEmpty())
        <div class="px-4 py-12 text-center text-slate-400">
            <svg width="40" height="40" fill="none" stroke="#cbd5e1" stroke-width="1.5" viewBox="0 0 24 24" class="mx-auto mb-2.5 block"><path d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            <p class="text-sm m-0">Belum ada pagu untuk periode ini. Klik "Tambah Pagu" untuk mulai.</p>
        </div>
    @else
    <table class="w-full border-collapse">
        <thead>
            <tr class="bg-slate-50 border-b border-slate-100">
                <th class="px-4 py-3 text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wide">#</th>
                <th class="px-4 py-3 text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wide">Departemen</th>
                <th class="px-4 py-3 text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wide">Kode</th>
                <th class="px-4 py-3 text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wide">Sumber</th>
                <th class="px-4 py-3 text-right text-[11px] font-semibold text-slate-400 uppercase tracking-wide">Pagu (Rp)</th>
                <th class="px-4 py-3 text-right text-[11px] font-semibold text-slate-400 uppercase tracking-wide">Persentase</th>
                <th class="px-4 py-3 text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wide">Blokir</th>
                <th class="px-4 py-3 text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wide">Status</th>
                <th class="px-4 py-3 text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wide">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @foreach($allocations as $i => $alloc)
            <tr class="border-b border-slate-50 hover:bg-slate-50 transition-colors">
                <td class="px-4 py-3 text-xs text-slate-400 align-middle">{{ $i + 1 }}</td>
                <td class="px-4 py-3 text-sm font-semibold text-slate-700 align-middle">{{ $alloc->department->name }}</td>
                <td class="px-4 py-3 text-sm text-slate-600 align-middle">
                    <span class="font-mono text-xs font-semibold text-slate-500 bg-slate-100 px-2 py-0.5 rounded">{{ $alloc->department->code }}</span>
                </td>
                <td class="px-4 py-3 text-sm text-slate-600 align-middle">
                    @if($alloc->source === 'NETT')
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-blue-100 text-blue-600">{{ $alloc->source }}</span>
                    @else
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-yellow-100 text-yellow-700">{{ $alloc->source }}</span>
                    @endif
                </td>
                <td class="px-4 py-3 text-right font-mono text-sm font-semibold text-slate-900 align-middle">
                    {{ number_format($alloc->amount, 0, ',', '.') }}
                </td>
                <td class="px-4 py-3 text-right font-mono text-sm text-slate-500 align-middle">
                    {{ $alloc->percentage !== null ? number_format($alloc->percentage, 2, ',', '.') . '%' : '—' }}
                </td>
                <td class="px-4 py-3 text-sm text-slate-600 align-middle">
                    @if($alloc->is_blocking)
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-red-100 text-red-600">Ya</span>
                    @else
                        <span class="text-slate-300 text-sm">—</span>
                    @endif
                </td>
                <td class="px-4 py-3 text-sm text-slate-600 align-middle">
                    @if($alloc->is_active)
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-green-100 text-green-700">Aktif</span>
                    @else
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-slate-100 text-slate-500">Nonaktif</span>
                    @endif
                </td>
                <td class="px-4 py-3 text-sm text-slate-600 align-middle">
                    <div class="flex gap-1.5">
                        <a href="{{ route('budget-allocations.edit', $alloc) }}" class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-xs font-medium bg-blue-50 text-blue-600 hover:bg-blue-100 transition-colors no-underline">Edit</a>
                        <form id="del-alloc-{{ $alloc->id }}" method="POST" action="{{ route('budget-allocations.destroy', $alloc) }}">
                            @csrf @method('DELETE')
                        </form>
                        <button type="button" class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-xs font-medium bg-red-50 text-red-600 hover:bg-red-100 transition-colors border-0 cursor-pointer"
                            onclick="confirmDelete('del-alloc-{{ $alloc->id }}', '{{ addslashes($alloc->department->name) }}')">
                            Hapus
                        </button>
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="bg-slate-50 border-t-2 border-slate-100">
                <td colspan="4" class="px-4 py-3 text-sm text-slate-500 font-bold">Total</td>
                <td class="px-4 py-3 text-right font-mono text-sm font-bold text-slate-900">{{ number_format($totalAmount, 0, ',', '.') }}</td>
                <td colspan="4" class="px-4 py-3"></td>
            </tr>
        </tfoot>
    </table>
    @endif
</div>
@endif
</x-layouts.app>
