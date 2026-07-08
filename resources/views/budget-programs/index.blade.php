<x-layouts.app title="Program Kerja">

<div class="flex items-center justify-between mb-5">
    <div>
        <h2 class="text-lg font-bold text-slate-900 m-0 mb-0.5">Program Kerja</h2>
        <p class="text-xs text-slate-400 m-0">Daftar program kerja per departemen dan periode anggaran</p>
    </div>
    <a href="{{ route('budget-programs.create') }}"
        class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-gradient-to-br from-orange-400 to-orange-500 text-white text-sm font-semibold shadow-sm hover:-translate-y-px transition-all no-underline">
        <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg>
        Tambah Program Kerja
    </a>
</div>

@if(session('success'))
<div class="flex items-center gap-2.5 px-4 py-3 bg-green-50 border border-green-200 rounded-xl mb-4 text-sm text-green-700">
    <svg width="16" height="16" fill="#16a34a" viewBox="0 0 20 20" class="shrink-0"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
    {{ session('success') }}
</div>
@endif

{{-- Ringkasan pagu per alokasi --}}
@if($allocationSummaries->isNotEmpty())
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 mb-5">
    @foreach($allocationSummaries as $s)
    @php
        $pct  = $s['pagu'] > 0 ? min(100, round($s['terpakai'] / $s['pagu'] * 100)) : 0;
        $over = $s['pagu'] > 0 && $s['terpakai'] > $s['pagu'];
    @endphp
    <div class="bg-white rounded-xl border border-slate-100 shadow-sm px-4 py-3.5">
        <div class="flex items-center justify-between mb-2">
            <div>
                <div class="text-xs font-bold text-slate-700">{{ $s['dept'] }}</div>
                <div class="text-[11px] text-slate-400">{{ $s['periode'] }}</div>
            </div>
            @if($over)
                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-red-100 text-red-600">Over Budget</span>
            @elseif($pct >= 80)
                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-orange-100 text-orange-600">{{ $pct }}%</span>
            @else
                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-green-100 text-green-600">{{ $pct }}%</span>
            @endif
        </div>
        <div class="flex items-end justify-between gap-3 mb-2">
            <div>
                <div class="text-[10px] text-slate-400 uppercase tracking-wide">Terpakai</div>
                <div class="text-sm font-bold font-mono {{ $over ? 'text-red-600' : 'text-orange-600' }}">
                    Rp {{ number_format($s['terpakai'], 0, ',', '.') }}
                </div>
            </div>
            <div class="text-right">
                <div class="text-[10px] text-slate-400 uppercase tracking-wide">Sisa</div>
                <div class="text-sm font-bold font-mono {{ $s['sisa'] < 0 ? 'text-red-600' : 'text-green-600' }}">
                    Rp {{ number_format($s['sisa'], 0, ',', '.') }}
                </div>
            </div>
        </div>
        <div class="w-full bg-slate-100 rounded-full h-1.5">
            <div class="h-1.5 rounded-full {{ $over ? 'bg-red-500' : ($pct >= 80 ? 'bg-orange-400' : 'bg-green-400') }}"
                style="width: {{ $pct }}%"></div>
        </div>
        <div class="text-[10px] text-slate-400 mt-1">Pagu: Rp {{ number_format($s['pagu'], 0, ',', '.') }}</div>
    </div>
    @endforeach
</div>
@endif

{{-- Filter --}}
<form method="GET" action="{{ route('budget-programs.index') }}" class="flex gap-2.5 flex-wrap items-center mb-5">
    <div class="relative flex-1 min-w-[200px]">
        <svg width="15" height="15" fill="none" stroke="#94a3b8" stroke-width="2" viewBox="0 0 24 24"
            class="absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none">
            <circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/>
        </svg>
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama program…"
            class="w-full pl-9 pr-4 py-2 border border-slate-200 rounded-xl text-sm text-slate-700 bg-white outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100 transition-colors">
    </div>

    <select name="budget_period_id" class="no-select2 px-3 py-2 border border-slate-200 rounded-xl text-sm text-slate-700 bg-white outline-none focus:border-orange-400 min-w-[170px] cursor-pointer" onchange="this.form.submit()">
        <option value="">Semua Periode</option>
        @foreach($budgetPeriods as $bp)
            <option value="{{ $bp->id }}" {{ request('budget_period_id') == $bp->id ? 'selected' : '' }}>{{ $bp->name }}</option>
        @endforeach
    </select>

    <select name="department_id" class="no-select2 px-3 py-2 border border-slate-200 rounded-xl text-sm text-slate-700 bg-white outline-none focus:border-orange-400 min-w-[170px] cursor-pointer" onchange="this.form.submit()">
        <option value="">Semua Departemen</option>
        @foreach($departments as $dept)
            <option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
        @endforeach
    </select>

    @if(request()->hasAny(['search', 'budget_period_id', 'department_id']))
    <a href="{{ route('budget-programs.index') }}" class="px-3 py-2 rounded-xl border border-slate-200 text-sm text-slate-500 hover:text-orange-500 hover:border-orange-300 transition-colors no-underline">Reset</a>
    @endif

    <button type="submit" class="px-4 py-2 rounded-xl bg-slate-700 text-white text-sm font-medium cursor-pointer border-0 hover:bg-slate-800 transition-colors">Cari</button>
</form>

<div class="bg-white rounded-xl shadow-sm overflow-hidden">
    @if($programs->isEmpty())
        <div class="py-12 text-center text-slate-400">
            <svg width="40" height="40" fill="none" stroke="#cbd5e1" stroke-width="1.5" viewBox="0 0 24 24" class="mx-auto mb-2.5 block">
                <path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
            </svg>
            <p class="text-sm m-0">Belum ada program kerja.</p>
            <p class="text-xs text-slate-300 mt-1">Klik "Tambah Program Kerja" untuk mulai menginput.</p>
        </div>
    @else
    <table class="w-full border-collapse">
        <thead>
            <tr class="bg-slate-50 border-b border-slate-100">
                <th class="px-5 py-3 text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wide">Nama Program</th>
                <th class="px-5 py-3 text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wide">Departemen</th>
                <th class="px-5 py-3 text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wide">Periode</th>
                <th class="px-5 py-3 text-center text-[11px] font-semibold text-slate-400 uppercase tracking-wide">Rincian</th>
                <th class="px-5 py-3 text-right text-[11px] font-semibold text-slate-400 uppercase tracking-wide">Total (Rp)</th>
                <th class="px-5 py-3 text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wide">Status</th>
                <th class="px-5 py-3 w-[100px]"></th>
            </tr>
        </thead>
        <tbody>
            @foreach($programs as $prog)
            <tr class="border-b border-slate-50 hover:bg-slate-50/60 transition-colors last:border-0">
                <td class="px-5 py-3.5 align-middle">
                    <div class="font-semibold text-sm text-slate-800">{{ $prog->name }}</div>
                    @if($prog->notes)
                        <div class="text-xs text-slate-400 mt-0.5 truncate max-w-[220px]">{{ $prog->notes }}</div>
                    @endif
                </td>
                <td class="px-5 py-3.5 align-middle text-sm text-slate-600">
                    {{ $prog->budgetAllocation->department->name }}
                </td>
                <td class="px-5 py-3.5 align-middle text-sm text-slate-600">
                    {{ $prog->budgetAllocation->budgetPeriod->name }}
                </td>
                <td class="px-5 py-3.5 text-center align-middle">
                    <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-slate-100 text-slate-600 text-xs font-semibold">{{ $prog->details->count() }}</span>
                </td>
                <td class="px-5 py-3.5 text-right font-mono text-sm font-semibold text-slate-900 align-middle">
                    Rp {{ number_format($prog->total_amount, 0, ',', '.') }}
                </td>
                <td class="px-5 py-3.5 align-middle">
                    @if($prog->is_active)
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-green-100 text-green-700">Aktif</span>
                    @else
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-slate-100 text-slate-500">Nonaktif</span>
                    @endif
                </td>
                <td class="px-5 py-3.5 align-middle">
                    <div class="flex items-center justify-end gap-1.5">
                        <a href="{{ route('budget-programs.show', $prog) }}"
                           class="inline-flex items-center p-1.5 rounded-lg text-slate-400 hover:text-blue-500 hover:bg-blue-50 transition-colors no-underline" title="Lihat rincian">
                            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        </a>
                        <a href="{{ route('budget-programs.edit', $prog) }}"
                           class="inline-flex items-center p-1.5 rounded-lg text-slate-400 hover:text-orange-500 hover:bg-orange-50 transition-colors no-underline" title="Edit">
                            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        </a>
                        <form id="del-prog-{{ $prog->id }}" method="POST" action="{{ route('budget-programs.destroy', $prog) }}">@csrf @method('DELETE')</form>
                        <button type="button" onclick="confirmDelete('del-prog-{{ $prog->id }}', '{{ addslashes($prog->name) }}')"
                            class="inline-flex items-center p-1.5 rounded-lg text-slate-300 hover:text-red-500 hover:bg-red-50 transition-colors border-0 bg-transparent cursor-pointer" title="Hapus">
                            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </button>
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    @if($programs->hasPages())
    <div class="flex items-center justify-between px-5 py-3.5 border-t border-slate-100">
        <div class="text-xs text-slate-400">
            Menampilkan {{ $programs->firstItem() }}–{{ $programs->lastItem() }} dari {{ $programs->total() }} program
        </div>
        <div class="flex gap-1.5">
            @if($programs->onFirstPage())
                <span class="px-3 py-1.5 rounded-lg text-xs text-slate-300 border border-slate-200 bg-slate-50">‹</span>
            @else
                <a href="{{ $programs->previousPageUrl() }}" class="px-3 py-1.5 rounded-lg text-xs text-slate-600 border border-slate-200 bg-white hover:bg-slate-50 no-underline">‹</a>
            @endif
            @foreach($programs->getUrlRange(1, $programs->lastPage()) as $page => $url)
                @if($page == $programs->currentPage())
                    <span class="px-3 py-1.5 rounded-lg text-xs font-semibold bg-orange-500 text-white">{{ $page }}</span>
                @else
                    <a href="{{ $url }}" class="px-3 py-1.5 rounded-lg text-xs text-slate-600 border border-slate-200 bg-white hover:bg-slate-50 no-underline">{{ $page }}</a>
                @endif
            @endforeach
            @if($programs->hasMorePages())
                <a href="{{ $programs->nextPageUrl() }}" class="px-3 py-1.5 rounded-lg text-xs text-slate-600 border border-slate-200 bg-white hover:bg-slate-50 no-underline">›</a>
            @else
                <span class="px-3 py-1.5 rounded-lg text-xs text-slate-300 border border-slate-200 bg-slate-50">›</span>
            @endif
        </div>
    </div>
    @endif
    @endif
</div>

</x-layouts.app>
