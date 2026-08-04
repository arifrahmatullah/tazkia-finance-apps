<x-layouts.app title="Tambah Aset Tetap">

<a href="{{ route('fixed-assets.index') }}" class="inline-flex items-center gap-1.5 text-sm text-slate-500 hover:text-orange-500 mb-5 no-underline">
    <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M19 12H5M12 5l-7 7 7 7"/></svg>
    Kembali ke Aset Tetap
</a>

<h1 class="text-xl font-bold text-slate-900 m-0 mb-0.5">Tambah Aset Tetap</h1>
<p class="text-sm text-slate-400 mb-5">Aset akan disusutkan otomatis dengan metode garis lurus saat proses penyusutan bulanan dijalankan</p>

@include('fixed-assets._form')

</x-layouts.app>
