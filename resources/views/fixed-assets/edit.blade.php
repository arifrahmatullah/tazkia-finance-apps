<x-layouts.app title="Ubah Aset Tetap">

<a href="{{ route('fixed-assets.index') }}" class="inline-flex items-center gap-1.5 text-sm text-slate-500 hover:text-orange-500 mb-5 no-underline">
    <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M19 12H5M12 5l-7 7 7 7"/></svg>
    Kembali ke Aset Tetap
</a>

<h1 class="text-xl font-bold text-slate-900 m-0 mb-0.5">Ubah Aset Tetap</h1>
<p class="text-sm text-slate-400 mb-5">{{ $asset->code }} — {{ $asset->name }}</p>

@if($asset->depreciations()->exists())
<div class="flex items-center gap-2.5 px-4 py-3 bg-amber-50 border border-amber-200 rounded-xl mb-5 text-sm text-amber-700">
    <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" class="shrink-0"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m0 3.75h.008v.008H12v-.008ZM21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
    Aset ini sudah punya riwayat penyusutan. Mengubah akun/harga perolehan/umur manfaat tidak akan mengoreksi jurnal yang sudah diposting sebelumnya.
</div>
@endif

@include('fixed-assets._form')

</x-layouts.app>
