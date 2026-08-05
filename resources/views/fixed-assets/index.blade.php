<x-layouts.app title="Aset Tetap">

<div class="flex items-center justify-between mb-5 flex-wrap gap-3">
    <div>
        <h2 class="text-lg font-bold text-slate-900 m-0 mb-0.5">Aset Tetap</h2>
        <p class="text-xs text-slate-400 m-0">Daftar aset tetap & proses penyusutan bulanan (metode garis lurus)</p>
    </div>
    <a href="{{ route('fixed-assets.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-gradient-to-br from-orange-400 to-orange-500 text-white text-sm font-semibold shadow-sm hover:-translate-y-px transition-all no-underline">
        <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg>
        Tambah Aset
    </a>
</div>

@if(session('success'))
<div class="flex items-center gap-2.5 px-4 py-3 bg-green-50 border border-green-200 rounded-xl mb-4 text-sm text-green-700">
    <svg width="16" height="16" fill="#16a34a" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
    {{ session('success') }}
</div>
@endif
@if(session('info'))
<div class="flex items-center gap-2.5 px-4 py-3 bg-blue-50 border border-blue-200 rounded-xl mb-4 text-sm text-blue-700">
    <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" class="shrink-0"><path d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
    {{ session('info') }}
</div>
@endif
@error('delete')
<div class="flex items-center gap-2.5 px-4 py-3 bg-red-50 border border-red-200 rounded-xl mb-4 text-sm text-red-700">
    {{ $message }}
</div>
@enderror

{{-- Filter --}}
<form method="GET" action="{{ route('fixed-assets.index') }}" class="flex gap-2.5 flex-wrap items-center mb-4">
    @if($organizations->count() > 1)
    <select name="organization_id" class="px-3 py-2 border border-slate-200 rounded-xl text-sm text-slate-800 bg-white outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100 transition-colors min-w-[180px]" onchange="this.form.submit()">
        @foreach($organizations as $org)
            <option value="{{ $org->id }}" {{ $orgId === $org->id ? 'selected' : '' }}>{{ $org->name }}</option>
        @endforeach
    </select>
    @else
    <input type="hidden" name="organization_id" value="{{ $orgId }}">
    @endif

    <select name="status" class="px-3 py-2 border border-slate-200 rounded-xl text-sm text-slate-800 bg-white outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100 transition-colors min-w-[150px]">
        <option value="">Semua Status</option>
        <option value="aktif" {{ $status === 'aktif' ? 'selected' : '' }}>Aktif</option>
        <option value="dihapusbukukan" {{ $status === 'dihapusbukukan' ? 'selected' : '' }}>Dihapusbukukan</option>
    </select>

    <div class="relative flex-1 min-w-[200px]">
        <svg width="15" height="15" fill="none" stroke="#94a3b8" stroke-width="2" viewBox="0 0 24 24" class="absolute left-[11px] top-1/2 -translate-y-1/2 pointer-events-none"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
        <input type="text" name="search" value="{{ $search }}" placeholder="Cari kode atau nama aset..." class="w-full pl-[34px] px-3 py-2 border border-slate-200 rounded-xl text-sm text-slate-800 bg-white outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100 transition-colors">
    </div>

    <button type="submit" class="px-4 py-2 rounded-xl border-0 cursor-pointer text-sm font-semibold bg-gradient-to-br from-orange-400 to-orange-500 text-white">Cari</button>
    @if($search || $status)
        <a href="{{ route('fixed-assets.index', ['organization_id' => $orgId]) }}" class="px-3.5 py-2 rounded-xl border border-slate-200 text-sm text-slate-500 no-underline bg-white">Reset</a>
    @endif
</form>

@if(!$orgId)
<div class="bg-white rounded-xl shadow-sm py-16 px-5 text-center">
    <div class="text-sm font-semibold text-slate-600 mb-1">Belum ada organisasi</div>
    <div class="text-xs text-slate-400">Tidak ada organisasi yang bisa diakses.</div>
</div>
@else

{{-- Ringkasan --}}
<div class="grid grid-cols-2 sm:grid-cols-3 gap-3 mb-4">
    <div class="bg-white rounded-xl shadow-sm px-4 py-3.5">
        <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-0.5">Total Harga Perolehan</div>
        <div class="text-base font-extrabold text-slate-700 font-mono leading-tight mt-1">Rp {{ number_format($totals->cost, 0, ',', '.') }}</div>
    </div>
    <div class="bg-white rounded-xl shadow-sm px-4 py-3.5">
        <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-0.5">Total Akumulasi Depresiasi</div>
        <div class="text-base font-extrabold text-orange-600 font-mono leading-tight mt-1">Rp {{ number_format($totals->accumulated, 0, ',', '.') }}</div>
    </div>
    <div class="col-span-2 sm:col-span-1 bg-white rounded-xl shadow-sm px-4 py-3.5">
        <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-0.5">Total Nilai Buku</div>
        <div class="text-base font-extrabold text-blue-600 font-mono leading-tight mt-1">Rp {{ number_format($totals->book_value, 0, ',', '.') }}</div>
    </div>
</div>

{{-- Proses Penyusutan --}}
<div class="bg-white rounded-xl shadow-sm p-4 mb-4">
    <div class="text-xs font-bold text-slate-500 uppercase tracking-wide mb-3">Proses Penyusutan Bulanan</div>
    <form method="POST" action="{{ route('fixed-assets.depreciate') }}" id="depreciateForm" class="flex flex-wrap gap-3 items-end">
        @csrf
        <input type="hidden" name="organization_id" value="{{ $orgId }}">
        <div class="min-w-[150px]">
            <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block mb-1.5">Periode</label>
            <input type="month" name="period" id="depreciatePeriod" value="{{ old('period', substr($currentPeriod, 0, 7)) }}" required
                class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm text-slate-700 bg-white outline-none focus:border-blue-400 transition-colors">
        </div>
        <button type="button" onclick="confirmDepreciate()" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-sm font-semibold bg-orange-500 text-white border-0 cursor-pointer hover:bg-orange-600 transition-colors">
            Proses Penyusutan
        </button>
    </form>
    @error('period')<div class="text-xs text-red-500 mt-2">{{ $message }}</div>@enderror
    <div class="text-[11px] text-slate-400 mt-2">
        Membuat satu jurnal (debit beban depresiasi, kredit akumulasi depresiasi) untuk setiap aset aktif yang belum diproses pada periode ini. Aset yang sudah lunas disusutkan otomatis dilewati.
    </div>
</div>

{{-- Tabel aset --}}
<div class="bg-white rounded-xl shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-[11px] text-slate-400 uppercase tracking-wider bg-slate-50/70 border-b border-slate-100">
                    <th class="py-2.5 px-4 font-semibold whitespace-nowrap">Kode</th>
                    <th class="py-2.5 px-3 font-semibold min-w-[180px]">Nama Aset</th>
                    <th class="py-2.5 px-3 font-semibold whitespace-nowrap">Tgl Perolehan</th>
                    <th class="py-2.5 px-3 font-semibold text-right whitespace-nowrap">Harga Perolehan</th>
                    <th class="py-2.5 px-3 font-semibold text-right whitespace-nowrap">Akum. Depresiasi</th>
                    <th class="py-2.5 px-3 font-semibold text-right whitespace-nowrap">Nilai Buku</th>
                    <th class="py-2.5 px-3 font-semibold whitespace-nowrap">Status</th>
                    <th class="py-2.5 px-4 font-semibold whitespace-nowrap">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($assets as $asset)
                <tr class="border-b border-slate-50 last:border-0 hover:bg-slate-50/50 transition-colors">
                    <td class="py-2.5 px-4 font-mono text-[11px] text-slate-400 whitespace-nowrap">{{ $asset->code }}</td>
                    <td class="py-2.5 px-3 text-slate-700">
                        {{ $asset->name }}
                        <span class="block text-[10px] text-slate-400 mt-0.5">{{ $asset->account->code }} — {{ $asset->account->name }}</span>
                    </td>
                    <td class="py-2.5 px-3 text-slate-600 whitespace-nowrap">{{ $asset->acquisition_date->translatedFormat('d M Y') }}</td>
                    <td class="py-2.5 px-3 text-right font-mono text-slate-700 whitespace-nowrap">Rp {{ number_format($asset->acquisition_cost, 0, ',', '.') }}</td>
                    <td class="py-2.5 px-3 text-right font-mono text-orange-700 whitespace-nowrap">Rp {{ number_format($asset->accumulated_depreciation, 0, ',', '.') }}</td>
                    <td class="py-2.5 px-3 text-right font-mono font-semibold text-blue-700 whitespace-nowrap">Rp {{ number_format($asset->book_value, 0, ',', '.') }}</td>
                    <td class="py-2.5 px-3 whitespace-nowrap">
                        @if($asset->status === 'dihapusbukukan')
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-slate-100 text-slate-500">Dihapusbukukan</span>
                        @elseif($asset->is_fully_depreciated)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-purple-100 text-purple-700">Lunas Disusutkan</span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-green-100 text-green-700">Aktif</span>
                        @endif
                    </td>
                    <td class="py-2.5 px-4 whitespace-nowrap">
                        <div class="flex gap-1.5">
                            <a href="{{ route('fixed-assets.edit', $asset) }}" class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-xs font-medium bg-blue-50 text-blue-600 hover:bg-blue-100 transition-colors no-underline">Edit</a>
                            <form id="del-asset-{{ $asset->id }}" method="POST" action="{{ route('fixed-assets.destroy', $asset) }}">
                                @csrf @method('DELETE')
                            </form>
                            <button type="button" class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-xs font-medium bg-red-50 text-red-600 hover:bg-red-100 transition-colors border-0 cursor-pointer"
                                onclick="confirmDelete('del-asset-{{ $asset->id }}', '{{ addslashes($asset->name) }}')">
                                Hapus
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="py-8 px-4 text-center text-[12px] text-slate-400 italic">Belum ada aset tetap. Klik "Tambah Aset" untuk mulai.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endif

<script>
function confirmDepreciate() {
    const form = document.getElementById('depreciateForm');
    if (!form.reportValidity()) return;

    const period = document.getElementById('depreciatePeriod').value;
    const label = period
        ? new Date(period + '-01T00:00:00').toLocaleDateString('id-ID', { month: 'long', year: 'numeric' })
        : 'periode ini';
    const message = `Posting jurnal penyusutan untuk periode <strong>${label}</strong>?<br>Aset aktif yang belum diproses pada periode ini akan disusutkan otomatis.`;

    if (window.confirmModal) {
        confirmModal('Proses Penyusutan Aset', message, function () {
            form.submit();
        }, 'Ya, Proses', 'Jurnal yang sudah diposting tidak bisa dibatalkan otomatis dari sini.');
    } else if (confirm(`Posting jurnal penyusutan untuk ${label}? Aset aktif yang belum diproses akan disusutkan otomatis.`)) {
        form.submit();
    }
}
</script>

</x-layouts.app>
