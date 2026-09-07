<x-layouts.app title="Pilih Jabatan">

<a href="{{ route('budget-programs.index') }}" class="inline-flex items-center gap-1.5 text-sm text-slate-500 hover:text-orange-500 mb-5 no-underline transition-colors">
    <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M19 12H5M12 5l-7 7 7 7"/></svg>
    Kembali ke Program Kerja
</a>

<h1 class="text-xl font-bold text-slate-900 m-0 mb-0.5">Pilih Jabatan</h1>
<p class="text-sm text-slate-400 mb-5">Kamu memegang lebih dari satu jabatan. Pilih jabatan untuk menentukan departemen program kerja yang akan dibuat.</p>

<div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
    @foreach($positions as $ep)
    <a href="{{ route('budget-programs.create', ['department_id' => $ep->position->department_id]) }}"
        class="block bg-white rounded-xl shadow-sm p-5 border border-slate-100 hover:border-orange-300 hover:-translate-y-px transition-all no-underline">
        <div class="text-sm font-bold text-slate-800">{{ $ep->position->name }}</div>
        <div class="text-xs text-slate-400 mt-1">{{ $ep->position->department->name }}</div>
    </a>
    @endforeach
</div>

</x-layouts.app>
