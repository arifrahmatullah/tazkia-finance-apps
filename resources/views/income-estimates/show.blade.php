<x-layouts.app title="Detail Estimasi Pendapatan">

<a href="{{ route('income-estimates.index') }}" class="inline-flex items-center gap-1.5 text-sm text-slate-500 hover:text-orange-500 mb-5 no-underline">
    <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M19 12H5M12 5l-7 7 7 7"/></svg>
    Kembali ke Estimasi Pendapatan
</a>

@if(session('success'))
<div class="flex items-center gap-2.5 px-4 py-3 bg-green-50 border border-green-200 rounded-xl mb-4 text-sm text-green-700">
    <svg width="16" height="16" fill="#16a34a" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
    {{ session('success') }}
</div>
@endif

{{-- Header Info --}}
<div class="bg-white rounded-xl shadow-sm p-5 mb-5">
    <div class="flex items-start justify-between gap-4">
        <div>
            <h1 class="text-lg font-bold text-slate-900 mb-1">{{ $incomeEstimate->description }}</h1>
            <div class="flex flex-wrap gap-3 text-sm text-slate-500">
                <span class="flex items-center gap-1.5">
                    <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                    {{ $incomeEstimate->organization->name }}
                </span>
                <span class="flex items-center gap-1.5">
                    <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    {{ $incomeEstimate->budgetPeriod->name }}
                </span>
                <span class="flex items-center gap-1.5">
                    <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                    Satuan: <strong class="text-slate-700">{{ $incomeEstimate->unit }}</strong>
                </span>
                <span class="flex items-center gap-1.5">
                    Harga/Satuan: <strong class="text-slate-700">Rp {{ number_format($incomeEstimate->unit_price, 0, ',', '.') }}</strong>
                </span>
            </div>
        </div>
        <div class="flex items-center gap-2 shrink-0">
            <a href="{{ route('income-estimates.edit', $incomeEstimate) }}"
               class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl border border-slate-200 text-slate-600 text-sm font-medium no-underline hover:bg-slate-50 transition-colors">
                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                Edit Header
            </a>
        </div>
    </div>

    {{-- Summary --}}
    @php
        $realizedTotal = $incomeEstimate->receipts->sum('total');
        $pct = (float) $incomeEstimate->total_amount > 0 ? round($realizedTotal / $incomeEstimate->total_amount * 100, 1) : null;
    @endphp
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mt-4 pt-4 border-t border-slate-100">
        <div class="bg-slate-50 rounded-xl p-3.5">
            <div class="text-[11px] text-slate-400 uppercase tracking-wide font-semibold mb-1">Jumlah Jadwal</div>
            <div class="text-xl font-bold text-slate-800">{{ $incomeEstimate->details->count() }}</div>
        </div>
        <div class="bg-slate-50 rounded-xl p-3.5">
            <div class="text-[11px] text-slate-400 uppercase tracking-wide font-semibold mb-1">Total Qty</div>
            <div class="text-xl font-bold text-slate-800">{{ number_format($incomeEstimate->details->sum('qty'), 2, ',', '.') }} <span class="text-sm font-normal text-slate-500">{{ $incomeEstimate->unit }}</span></div>
        </div>
        <div class="bg-orange-50 rounded-xl p-3.5">
            <div class="text-[11px] text-orange-400 uppercase tracking-wide font-semibold mb-1">Total Estimasi</div>
            <div class="text-xl font-bold text-orange-600">Rp {{ number_format($incomeEstimate->total_amount, 0, ',', '.') }}</div>
        </div>
        <div class="bg-emerald-50 rounded-xl p-3.5">
            <div class="text-[11px] text-emerald-500 uppercase tracking-wide font-semibold mb-1">Realisasi Penerimaan</div>
            <div class="text-xl font-bold text-emerald-600">Rp {{ number_format($realizedTotal, 0, ',', '.') }}</div>
            @if(!is_null($pct))
            <div class="text-[11px] text-emerald-500 mt-0.5">{{ number_format($pct, 1, ',', '.') }}% dari estimasi</div>
            @endif
        </div>
    </div>
</div>

{{-- Detail Table --}}
<div class="flex items-center justify-between mb-3">
    <h2 class="text-sm font-bold text-slate-700 m-0">Jadwal Estimasi</h2>
    <a href="{{ route('income-estimate-details.create', ['income_estimate_id' => $incomeEstimate->id]) }}"
       class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-gradient-to-br from-orange-400 to-orange-500 text-white text-xs font-semibold shadow-sm hover:-translate-y-px transition-all no-underline">
        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg>
        Tambah Jadwal
    </a>
</div>

<div class="bg-white rounded-xl shadow-sm overflow-hidden">
    @if($incomeEstimate->details->isEmpty())
        <div class="py-10 text-center text-slate-400">
            <svg width="36" height="36" fill="none" stroke="#cbd5e1" stroke-width="1.5" viewBox="0 0 24 24" class="mx-auto mb-2 block">
                <path d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
            <p class="text-sm m-0">Belum ada jadwal. Klik "Tambah Jadwal" untuk menambahkan rincian estimasi.</p>
        </div>
    @else
    <table class="w-full border-collapse">
        <thead>
            <tr class="bg-slate-50 border-b border-slate-100">
                <th class="px-5 py-3 text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wide w-[130px]">Tanggal</th>
                <th class="px-5 py-3 text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wide">Deskripsi</th>
                <th class="px-5 py-3 text-right text-[11px] font-semibold text-slate-400 uppercase tracking-wide w-[100px]">Qty</th>
                <th class="px-5 py-3 text-right text-[11px] font-semibold text-slate-400 uppercase tracking-wide w-[140px]">Harga/Satuan</th>
                <th class="px-5 py-3 text-right text-[11px] font-semibold text-slate-400 uppercase tracking-wide w-[140px]">Total</th>
                <th class="px-5 py-3 w-[80px]"></th>
            </tr>
        </thead>
        <tbody>
            @foreach($incomeEstimate->details as $detail)
            <tr class="border-b border-slate-50 hover:bg-slate-50/60 transition-colors last:border-0">
                <td class="px-5 py-3.5 align-middle text-sm text-slate-600">
                    {{ $detail->estimate_date->translatedFormat('d M Y') }}
                </td>
                <td class="px-5 py-3.5 align-middle text-sm text-slate-700">{{ $detail->description }}</td>
                <td class="px-5 py-3.5 align-middle text-sm text-slate-700 text-right">
                    {{ number_format($detail->qty, 2, ',', '.') }}
                    <span class="text-xs text-slate-400">{{ $incomeEstimate->unit }}</span>
                </td>
                <td class="px-5 py-3.5 align-middle text-sm text-slate-600 text-right">
                    Rp {{ number_format($detail->unit_price, 0, ',', '.') }}
                </td>
                <td class="px-5 py-3.5 align-middle text-right">
                    <span class="text-sm font-semibold text-orange-600">Rp {{ number_format($detail->total, 0, ',', '.') }}</span>
                </td>
                <td class="px-5 py-3.5 align-middle">
                    <div class="flex items-center justify-end gap-1">
                        <a href="{{ route('income-estimate-details.edit', $detail) }}"
                           class="inline-flex items-center p-1.5 rounded-lg text-slate-400 hover:text-orange-500 hover:bg-orange-50 transition-colors no-underline">
                            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        </a>
                        <form id="del-ied-{{ $detail->id }}" method="POST" action="{{ route('income-estimate-details.destroy', $detail) }}">@csrf @method('DELETE')</form>
                        <button type="button" onclick="confirmDelete('del-ied-{{ $detail->id }}', '{{ $detail->estimate_date->format('d/m/Y') }} – {{ addslashes($detail->description) }}')"
                            class="inline-flex items-center p-1.5 rounded-lg text-slate-300 hover:text-red-500 hover:bg-red-50 transition-colors border-0 bg-transparent cursor-pointer">
                            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </button>
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="border-t-2 border-slate-200 bg-slate-50">
                <td colspan="4" class="px-5 py-3 text-right text-xs font-bold text-slate-500 uppercase tracking-wide">Total Keseluruhan</td>
                <td class="px-5 py-3 text-right font-bold text-orange-600">Rp {{ number_format($incomeEstimate->total_amount, 0, ',', '.') }}</td>
                <td></td>
            </tr>
        </tfoot>
    </table>
    @endif
</div>

{{-- Realisasi Penerimaan --}}
<div class="flex items-center justify-between mb-3 mt-7">
    <h2 class="text-sm font-bold text-slate-700 m-0">Realisasi Penerimaan</h2>
    <a href="{{ route('income-receipts.create', ['income_estimate_id' => $incomeEstimate->id]) }}"
       class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-gradient-to-br from-emerald-500 to-emerald-600 text-white text-xs font-semibold shadow-sm hover:-translate-y-px transition-all no-underline">
        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg>
        Catat Realisasi
    </a>
</div>

<div class="bg-white rounded-xl shadow-sm overflow-hidden">
    @if($incomeEstimate->receipts->isEmpty())
        <div class="py-10 text-center text-slate-400">
            <svg width="36" height="36" fill="none" stroke="#cbd5e1" stroke-width="1.5" viewBox="0 0 24 24" class="mx-auto mb-2 block">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
            </svg>
            <p class="text-sm m-0">Belum ada realisasi. Klik "Catat Realisasi" untuk mencatat penerimaan yang benar-benar masuk.</p>
        </div>
    @else
    <table class="w-full border-collapse">
        <thead>
            <tr class="bg-slate-50 border-b border-slate-100">
                <th class="px-5 py-3 text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wide w-[130px]">Tanggal</th>
                <th class="px-5 py-3 text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wide">Deskripsi</th>
                <th class="px-5 py-3 text-right text-[11px] font-semibold text-slate-400 uppercase tracking-wide w-[100px]">Qty</th>
                <th class="px-5 py-3 text-right text-[11px] font-semibold text-slate-400 uppercase tracking-wide w-[140px]">Total</th>
                <th class="px-5 py-3 text-center text-[11px] font-semibold text-slate-400 uppercase tracking-wide w-[80px]">Bukti</th>
                <th class="px-5 py-3 w-[80px]"></th>
            </tr>
        </thead>
        <tbody>
            @foreach($incomeEstimate->receipts as $receipt)
            <tr class="border-b border-slate-50 hover:bg-slate-50/60 transition-colors last:border-0">
                <td class="px-5 py-3.5 align-middle text-sm text-slate-600">
                    {{ $receipt->receipt_date->translatedFormat('d M Y') }}
                </td>
                <td class="px-5 py-3.5 align-middle text-sm text-slate-700">{{ $receipt->description }}</td>
                <td class="px-5 py-3.5 align-middle text-sm text-slate-700 text-right">
                    {{ number_format($receipt->qty, 2, ',', '.') }}
                    <span class="text-xs text-slate-400">{{ $incomeEstimate->unit }}</span>
                </td>
                <td class="px-5 py-3.5 align-middle text-right">
                    <span class="text-sm font-semibold text-emerald-600">Rp {{ number_format($receipt->total, 0, ',', '.') }}</span>
                </td>
                <td class="px-5 py-3.5 align-middle text-center">
                    @if($receipt->proof_path)
                    <a href="{{ $receipt->proof_url }}" target="_blank" title="{{ $receipt->proof_name }}"
                       class="inline-flex items-center p-1.5 rounded-lg text-blue-500 hover:bg-blue-50 transition-colors no-underline">
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><path d="M14 2v6h6"/></svg>
                    </a>
                    @else
                    <span class="text-slate-300 text-xs">—</span>
                    @endif
                </td>
                <td class="px-5 py-3.5 align-middle">
                    <div class="flex items-center justify-end gap-1">
                        <a href="{{ route('income-receipts.edit', $receipt) }}"
                           class="inline-flex items-center p-1.5 rounded-lg text-slate-400 hover:text-emerald-500 hover:bg-emerald-50 transition-colors no-underline">
                            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        </a>
                        <form id="del-ir-{{ $receipt->id }}" method="POST" action="{{ route('income-receipts.destroy', $receipt) }}">@csrf @method('DELETE')</form>
                        <button type="button" onclick="confirmDelete('del-ir-{{ $receipt->id }}', '{{ $receipt->receipt_date->format('d/m/Y') }} – {{ addslashes($receipt->description) }}')"
                            class="inline-flex items-center p-1.5 rounded-lg text-slate-300 hover:text-red-500 hover:bg-red-50 transition-colors border-0 bg-transparent cursor-pointer">
                            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </button>
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="border-t-2 border-slate-200 bg-slate-50">
                <td colspan="3" class="px-5 py-3 text-right text-xs font-bold text-slate-500 uppercase tracking-wide">Total Realisasi</td>
                <td class="px-5 py-3 text-right font-bold text-emerald-600">Rp {{ number_format($incomeEstimate->receipts->sum('total'), 0, ',', '.') }}</td>
                <td colspan="2"></td>
            </tr>
        </tfoot>
    </table>
    @endif
</div>
</x-layouts.app>
