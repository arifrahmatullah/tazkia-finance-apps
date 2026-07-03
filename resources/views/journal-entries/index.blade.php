<x-layouts.app title="Jurnal Umum">

<div class="flex items-center justify-between mb-5">
    <div>
        <h2 class="text-lg font-bold text-slate-900 m-0 mb-0.5">Jurnal Umum</h2>
        <p class="text-xs text-slate-400 m-0">Pencatatan transaksi keuangan</p>
    </div>
    <a href="{{ route('journal-entries.create', ['organization_id' => request('organization_id')]) }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-gradient-to-br from-orange-400 to-orange-500 text-white text-sm font-semibold shadow-sm hover:-translate-y-px transition-all no-underline">
        <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg>
        Tambah Jurnal
    </a>
</div>

@if(session('success'))
<div class="flex items-center gap-2.5 px-4 py-3 bg-green-50 border border-green-200 rounded-xl mb-4 text-sm text-green-700">
    <svg width="16" height="16" fill="#16a34a" viewBox="0 0 20 20" class="shrink-0"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
    {{ session('success') }}
</div>
@endif

<form method="GET" action="{{ route('journal-entries.index') }}" class="flex gap-2.5 flex-wrap items-center mb-4">
    @if($organizations->count() > 1)
    <select name="organization_id" class="px-3 py-2 border border-slate-200 rounded-xl text-sm text-slate-800 bg-white outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100 transition-colors min-w-[180px]"
        onchange="this.form.submit()">
        <option value="">Semua Organisasi</option>
        @foreach($organizations as $org)
            <option value="{{ $org->id }}" {{ request('organization_id') == $org->id ? 'selected' : '' }}>{{ $org->name }}</option>
        @endforeach
    </select>
    @else
        <input type="hidden" name="organization_id" value="{{ $organizations->first()?->id }}">
    @endif

    <select name="status" class="px-3 py-2 border border-slate-200 rounded-xl text-sm text-slate-800 bg-white outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100 transition-colors">
        <option value="">Semua Status</option>
        <option value="draft"  {{ request('status') === 'draft'  ? 'selected' : '' }}>Draft</option>
        <option value="posted" {{ request('status') === 'posted' ? 'selected' : '' }}>Diposting</option>
    </select>

    <input type="date" name="date_from" value="{{ request('date_from') }}"
        class="px-3 py-2 border border-slate-200 rounded-xl text-sm text-slate-800 bg-white outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100 transition-colors">
    <input type="date" name="date_to" value="{{ request('date_to') }}"
        class="px-3 py-2 border border-slate-200 rounded-xl text-sm text-slate-800 bg-white outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100 transition-colors">

    <div class="relative flex-1 min-w-[200px]">
        <svg width="15" height="15" fill="none" stroke="#94a3b8" stroke-width="2" viewBox="0 0 24 24" class="absolute left-[11px] top-1/2 -translate-y-1/2 pointer-events-none"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari no. jurnal atau keterangan..."
            class="w-full pl-[34px] px-3 py-2 border border-slate-200 rounded-xl text-sm text-slate-800 bg-white outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100 transition-colors">
    </div>

    <button type="submit" class="px-4 py-2 rounded-xl border-0 cursor-pointer text-sm font-semibold bg-gradient-to-br from-orange-400 to-orange-500 text-white">Cari</button>
    @if(request()->hasAny(['search','status','date_from','date_to']) || (request('organization_id') && $organizations->count() > 1))
        <a href="{{ route('journal-entries.index', ['organization_id' => request('organization_id')]) }}" class="px-3.5 py-2 rounded-xl border border-slate-200 text-sm text-slate-500 no-underline bg-white">Reset</a>
    @endif
</form>

<div class="bg-white rounded-xl shadow-sm overflow-hidden">
    @if($entries->isEmpty())
        <div class="py-12 px-5 text-center text-slate-400">
            <svg width="40" height="40" fill="none" stroke="#cbd5e1" stroke-width="1.5" viewBox="0 0 24 24" class="mx-auto mb-2.5 block"><path d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            <p class="text-sm m-0">Belum ada jurnal. Klik "Tambah Jurnal" untuk mencatat transaksi pertama.</p>
        </div>
    @else
        <table class="w-full border-collapse">
            <thead>
                <tr class="bg-slate-50 border-b border-slate-100">
                    <th class="px-4 py-3 text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wide w-[160px]">No. Jurnal</th>
                    <th class="px-4 py-3 text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wide w-[110px]">Tanggal</th>
                    <th class="px-4 py-3 text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wide">Keterangan</th>
                    <th class="px-4 py-3 text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wide w-[130px]">Organisasi</th>
                    <th class="px-4 py-3 text-right text-[11px] font-semibold text-slate-400 uppercase tracking-wide w-[130px]">Total Debit</th>
                    <th class="px-4 py-3 text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wide w-[90px]">Status</th>
                    <th class="px-4 py-3 text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wide w-[150px]">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($entries as $entry)
                <tr class="border-b border-slate-50 hover:bg-slate-50 transition-colors last:border-b-0">
                    <td class="px-4 py-3 align-middle">
                        <span class="font-mono text-xs font-bold text-orange-500">{{ $entry->reference }}</span>
                    </td>
                    <td class="px-4 py-3 text-xs text-slate-500 align-middle">{{ $entry->entry_date->format('d/m/Y') }}</td>
                    <td class="px-4 py-3 text-xs text-slate-600 align-middle max-w-[200px] truncate">{{ $entry->description ?: '-' }}</td>
                    <td class="px-4 py-3 text-xs text-slate-500 align-middle">{{ $entry->organization->name }}</td>
                    <td class="px-4 py-3 text-right font-mono text-sm text-slate-800 align-middle">
                        Rp {{ number_format($entry->total_debit, 0, ',', '.') }}
                    </td>
                    <td class="px-4 py-3 align-middle">
                        @if($entry->isPosted())
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-green-100 text-green-700">
                                <svg width="6" height="6" fill="#15803d" viewBox="0 0 8 8"><circle cx="4" cy="4" r="4"/></svg>
                                Diposting
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-slate-100 text-slate-500">
                                <svg width="6" height="6" fill="#94a3b8" viewBox="0 0 8 8"><circle cx="4" cy="4" r="4"/></svg>
                                Draft
                            </span>
                        @endif
                    </td>
                    <td class="px-4 py-3 align-middle">
                        <div class="flex gap-1 flex-wrap">
                            <a href="{{ route('journal-entries.show', $entry) }}" class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-xs font-medium bg-slate-100 text-slate-600 hover:bg-slate-200 transition-colors no-underline">Lihat</a>
                            @if($entry->isDraft())
                                <a href="{{ route('journal-entries.edit', $entry) }}" class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-xs font-medium bg-blue-50 text-blue-600 hover:bg-blue-100 transition-colors no-underline">Edit</a>
                                <form id="post-{{ $entry->id }}" method="POST" action="{{ route('journal-entries.post', $entry) }}">@csrf</form>
                                <button type="button" class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-xs font-medium bg-green-50 text-green-700 hover:bg-green-100 transition-colors border-0 cursor-pointer"
                                    onclick="confirmPost('post-{{ $entry->id }}', '{{ addslashes($entry->reference) }}')">Post</button>
                                <form id="del-je-{{ $entry->id }}" method="POST" action="{{ route('journal-entries.destroy', $entry) }}">@csrf @method('DELETE')</form>
                                <button type="button" class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-xs font-medium bg-red-50 text-red-600 hover:bg-red-100 transition-colors border-0 cursor-pointer"
                                    onclick="confirmDelete('del-je-{{ $entry->id }}', '{{ addslashes($entry->reference) }}')">Hapus</button>
                            @endif
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        @if($entries->hasPages())
        <div class="px-4 py-3.5 border-t border-slate-100 flex justify-end gap-1">
            @if($entries->onFirstPage())
                <span class="inline-flex items-center px-2.5 py-1.5 border border-slate-200 rounded-lg text-xs text-slate-300">&laquo;</span>
            @else
                <a href="{{ $entries->previousPageUrl() }}" class="inline-flex items-center px-2.5 py-1.5 border border-slate-200 rounded-lg text-xs text-slate-500 no-underline hover:border-orange-400 hover:text-orange-500">&laquo;</a>
            @endif

            @foreach($entries->getUrlRange(max(1,$entries->currentPage()-2), min($entries->lastPage(),$entries->currentPage()+2)) as $page => $url)
                <a href="{{ $url }}" class="inline-flex items-center px-2.5 py-1.5 border rounded-lg text-xs no-underline {{ $page == $entries->currentPage() ? 'bg-orange-500 border-orange-500 text-white font-semibold' : 'border-slate-200 text-slate-500 hover:border-orange-400 hover:text-orange-500' }}">{{ $page }}</a>
            @endforeach

            @if($entries->hasMorePages())
                <a href="{{ $entries->nextPageUrl() }}" class="inline-flex items-center px-2.5 py-1.5 border border-slate-200 rounded-lg text-xs text-slate-500 no-underline hover:border-orange-400 hover:text-orange-500">&raquo;</a>
            @else
                <span class="inline-flex items-center px-2.5 py-1.5 border border-slate-200 rounded-lg text-xs text-slate-300">&raquo;</span>
            @endif
        </div>
        @endif
    @endif
</div>

<script>
function confirmPost(formId, ref) {
    if (window.confirmModal) {
        confirmModal('Posting Jurnal', `Yakin ingin memposting jurnal <strong>${ref}</strong>? Jurnal yang sudah diposting tidak dapat diedit.`, function() {
            document.getElementById(formId).submit();
        });
    } else if (confirm(`Posting jurnal ${ref}? Data tidak bisa diedit setelah diposting.`)) {
        document.getElementById(formId).submit();
    }
}
</script>
</x-layouts.app>
