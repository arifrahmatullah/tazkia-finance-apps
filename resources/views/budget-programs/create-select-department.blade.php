<x-layouts.app title="Pilih Departemen">

<a href="{{ route('budget-programs.index') }}" class="inline-flex items-center gap-1.5 text-sm text-slate-500 hover:text-orange-500 mb-5 no-underline transition-colors">
    <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M19 12H5M12 5l-7 7 7 7"/></svg>
    Kembali ke Program Kerja
</a>

<h1 class="text-xl font-bold text-slate-900 m-0 mb-0.5">Pilih Departemen</h1>
<p class="text-sm text-slate-400 mb-5">Sebagai superadmin, pilih departemen yang mau dibuatkan program kerja.</p>

@if($allocations->isEmpty())
<div class="flex items-start gap-2.5 px-4 py-3 bg-amber-50 border border-amber-200 rounded-xl text-sm text-amber-700">
    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" class="shrink-0 mt-px"><path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
    Belum ada departemen dengan pagu anggaran aktif.
</div>
@else
<div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
    @foreach($allocations as $alloc)
    <a href="{{ route('budget-programs.create', ['department_id' => $alloc->department_id]) }}"
        class="block bg-white rounded-xl shadow-sm p-5 border border-slate-100 hover:border-orange-300 hover:-translate-y-px transition-all no-underline">
        <div class="text-sm font-bold text-slate-800">{{ $alloc->department->name }}</div>
        <div class="text-xs text-slate-400 mt-1">{{ $alloc->budgetPeriod->name }} — Pagu Rp {{ number_format($alloc->amount, 0, ',', '.') }}</div>
    </a>
    @endforeach
</div>
@endif

</x-layouts.app>
