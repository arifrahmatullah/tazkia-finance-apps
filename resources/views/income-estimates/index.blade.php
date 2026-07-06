<x-layouts.app title="Estimasi Pendapatan">

<div class="flex items-center justify-between mb-5">
    <div>
        <h2 class="text-lg font-bold text-slate-900 m-0 mb-0.5">Estimasi Pendapatan</h2>
        <p class="text-xs text-slate-400 m-0">Rencana target penerimaan pendapatan per periode anggaran</p>
    </div>
    <a href="{{ route('income-estimates.create') }}"
       class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-gradient-to-br from-orange-400 to-orange-500 text-white text-sm font-semibold shadow-sm hover:-translate-y-px transition-all no-underline">
        <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg>
        Tambah Estimasi
    </a>
</div>

@if(session('success'))
<div class="flex items-center gap-2.5 px-4 py-3 bg-green-50 border border-green-200 rounded-xl mb-4 text-sm text-green-700">
    <svg width="16" height="16" fill="#16a34a" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
    {{ session('success') }}
</div>
@endif

{{-- Periode Aktif Summary --}}
@if($periodSummaries->isNotEmpty())
<div class="mb-5">
    <div class="text-[11px] font-semibold text-slate-400 uppercase tracking-wide mb-2">Periode Aktif</div>
    <div class="flex flex-col gap-3">
        @foreach($periodSummaries as $ps)
        <div class="bg-white rounded-xl border border-slate-100 shadow-sm px-5 py-4 flex items-center gap-4">
            <div class="w-9 h-9 rounded-xl bg-green-100 flex items-center justify-center shrink-0">
                <svg width="16" height="16" fill="none" stroke="#16a34a" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
            </div>
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2 flex-wrap">
                    <span class="text-sm font-bold text-slate-800 truncate">{{ $ps->period->name }}</span>
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-green-100 text-green-700">Aktif</span>
                </div>
                <div class="text-xs text-slate-400 mt-0.5">{{ $ps->period->organization->name }}</div>
                <div class="flex items-center gap-3 mt-1.5">
                    <div>
                        <span class="text-[11px] text-slate-400">Total Estimasi Aktif</span>
                        <div class="text-sm font-bold text-orange-600 font-mono">
                            Rp {{ number_format($ps->total, 0, ',', '.') }}
                        </div>
                    </div>
                    <div class="w-px h-8 bg-slate-100"></div>
                    <div>
                        <span class="text-[11px] text-slate-400">Jumlah Estimasi</span>
                        <div class="text-sm font-bold text-slate-700">{{ $ps->count }} item</div>
                    </div>
                    <div class="ml-auto">
                        <span class="text-[11px] text-slate-400">Periode</span>
                        <div class="text-xs text-slate-600 font-medium">
                            {{ \Carbon\Carbon::parse($ps->period->period_start)->format('d M Y') }}
                            – {{ \Carbon\Carbon::parse($ps->period->period_end)->format('d M Y') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>
@else
<div class="bg-yellow-50 border border-yellow-200 rounded-xl px-4 py-3 mb-5 flex items-center gap-2.5 text-sm text-yellow-700">
    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" class="shrink-0"><path d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
    Tidak ada periode anggaran aktif. <a href="{{ route('budget-periods.index') }}" class="underline font-semibold hover:text-yellow-800">Buat periode anggaran</a> terlebih dahulu.
</div>
@endif

{{-- Filter --}}
<form method="GET" action="{{ route('income-estimates.index') }}" class="flex gap-2.5 flex-wrap items-center mb-5">
    <div class="relative flex-1 min-w-[200px]">
        <svg width="15" height="15" fill="none" stroke="#94a3b8" stroke-width="2" viewBox="0 0 24 24"
            class="absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none">
            <circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/>
        </svg>
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari deskripsi…"
            class="w-full pl-9 pr-4 py-2 border border-slate-200 rounded-xl text-sm text-slate-700 bg-white outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100 transition-colors">
    </div>

    @if($organizations->count() > 1)
    <select name="organization_id" class="no-select2 px-3 py-2 border border-slate-200 rounded-xl text-sm text-slate-700 bg-white outline-none focus:border-orange-400 min-w-[170px] cursor-pointer" onchange="this.form.submit()">
        <option value="">Semua Organisasi</option>
        @foreach($organizations as $org)
            <option value="{{ $org->id }}" {{ request('organization_id') == $org->id ? 'selected' : '' }}>{{ $org->name }}</option>
        @endforeach
    </select>
    @endif

    <select name="budget_period_id" class="no-select2 px-3 py-2 border border-slate-200 rounded-xl text-sm text-slate-700 bg-white outline-none focus:border-orange-400 min-w-[170px] cursor-pointer" onchange="this.form.submit()">
        <option value="">Semua Periode</option>
        @foreach($budgetPeriods as $bp)
            <option value="{{ $bp->id }}" {{ request('budget_period_id') == $bp->id ? 'selected' : '' }}>{{ $bp->name }}</option>
        @endforeach
    </select>

    @if(request()->hasAny(['search','organization_id','budget_period_id']))
    <a href="{{ route('income-estimates.index') }}" class="px-3 py-2 rounded-xl border border-slate-200 text-sm text-slate-500 hover:text-orange-500 hover:border-orange-300 transition-colors no-underline">Reset</a>
    @endif

    <button type="submit" class="px-4 py-2 rounded-xl bg-slate-700 text-white text-sm font-medium cursor-pointer border-0 hover:bg-slate-800 transition-colors">Cari</button>
</form>

<div class="bg-white rounded-xl shadow-sm overflow-hidden">
    @if($estimates->isEmpty())
        <div class="py-12 text-center text-slate-400">
            <svg width="40" height="40" fill="none" stroke="#cbd5e1" stroke-width="1.5" viewBox="0 0 24 24" class="mx-auto mb-2.5 block">
                <path d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
            </svg>
            <p class="text-sm m-0">Belum ada estimasi pendapatan.</p>
        </div>
    @else
    <table class="w-full border-collapse">
        <thead>
            <tr class="bg-slate-50 border-b border-slate-100">
                <th class="px-5 py-3 text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wide">Deskripsi</th>
                <th class="px-5 py-3 text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wide">Periode</th>
                <th class="px-5 py-3 text-right text-[11px] font-semibold text-slate-400 uppercase tracking-wide">Target Penerimaan</th>
                <th class="px-5 py-3 text-right text-[11px] font-semibold text-slate-400 uppercase tracking-wide">Total Estimasi</th>
                <th class="px-5 py-3 w-[120px]"></th>
            </tr>
        </thead>
        <tbody>
            @foreach($estimates as $est)
            <tr class="border-b border-slate-50 hover:bg-slate-50/60 transition-colors last:border-0">
                <td class="px-5 py-3.5 align-middle">
                    <div class="font-medium text-sm text-slate-800">{{ $est->description }}</div>
                    <div class="text-xs text-slate-400 mt-0.5">{{ $est->organization->name }}</div>
                </td>
                <td class="px-5 py-3.5 align-middle text-sm text-slate-600">{{ $est->budgetPeriod->name }}</td>
                <td class="px-5 py-3.5 align-middle text-sm text-slate-700 text-right">
                    Rp {{ number_format($est->unit_price, 0, ',', '.') }}
                </td>
                <td class="px-5 py-3.5 align-middle text-right">
                    <span class="text-sm font-semibold text-orange-600">Rp {{ number_format($est->total_amount, 0, ',', '.') }}</span>
                </td>
                <td class="px-5 py-3.5 align-middle">
                    <div class="flex items-center justify-end gap-1.5">
                        <a href="{{ route('income-estimates.show', $est) }}"
                           class="inline-flex items-center p-1.5 rounded-lg text-slate-400 hover:text-blue-500 hover:bg-blue-50 transition-colors no-underline" title="Lihat detail">
                            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        </a>
                        <a href="{{ route('income-estimates.edit', $est) }}"
                           class="inline-flex items-center p-1.5 rounded-lg text-slate-400 hover:text-orange-500 hover:bg-orange-50 transition-colors no-underline" title="Edit">
                            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        </a>
                        <form id="del-ie-{{ $est->id }}" method="POST" action="{{ route('income-estimates.destroy', $est) }}">@csrf @method('DELETE')</form>
                        <button type="button" onclick="confirmDelete('del-ie-{{ $est->id }}', '{{ addslashes($est->description) }}')"
                            class="inline-flex items-center p-1.5 rounded-lg text-slate-300 hover:text-red-500 hover:bg-red-50 transition-colors border-0 bg-transparent cursor-pointer" title="Hapus">
                            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </button>
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    @if($estimates->hasPages())
    <div class="flex items-center justify-between px-5 py-3.5 border-t border-slate-100">
        <div class="text-xs text-slate-400">
            Menampilkan {{ $estimates->firstItem() }}–{{ $estimates->lastItem() }} dari {{ $estimates->total() }} estimasi
        </div>
        <div class="flex gap-1.5">
            @if($estimates->onFirstPage())
                <span class="px-3 py-1.5 rounded-lg text-xs text-slate-300 border border-slate-200 bg-slate-50">‹</span>
            @else
                <a href="{{ $estimates->previousPageUrl() }}" class="px-3 py-1.5 rounded-lg text-xs text-slate-600 border border-slate-200 bg-white hover:bg-slate-50 no-underline">‹</a>
            @endif
            @foreach($estimates->getUrlRange(1, $estimates->lastPage()) as $page => $url)
                @if($page == $estimates->currentPage())
                    <span class="px-3 py-1.5 rounded-lg text-xs font-semibold bg-orange-500 text-white">{{ $page }}</span>
                @else
                    <a href="{{ $url }}" class="px-3 py-1.5 rounded-lg text-xs text-slate-600 border border-slate-200 bg-white hover:bg-slate-50 no-underline">{{ $page }}</a>
                @endif
            @endforeach
            @if($estimates->hasMorePages())
                <a href="{{ $estimates->nextPageUrl() }}" class="px-3 py-1.5 rounded-lg text-xs text-slate-600 border border-slate-200 bg-white hover:bg-slate-50 no-underline">›</a>
            @else
                <span class="px-3 py-1.5 rounded-lg text-xs text-slate-300 border border-slate-200 bg-slate-50">›</span>
            @endif
        </div>
    </div>
    @endif
    @endif
</div>
</x-layouts.app>
