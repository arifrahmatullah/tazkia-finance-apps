<x-layouts.app title="Detail Jurnal {{ $journalEntry->reference }}">

<a href="{{ route('journal-entries.index', ['organization_id' => $journalEntry->organization_id]) }}" class="inline-flex items-center gap-1.5 text-sm text-slate-500 hover:text-orange-500 mb-5 no-underline">
    <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M19 12H5M12 5l-7 7 7 7"/></svg>
    Kembali ke Jurnal Umum
</a>

@if(session('success'))
<div class="flex items-center gap-2.5 px-4 py-3 bg-green-50 border border-green-200 rounded-xl mb-4 text-sm text-green-700">
    <svg width="16" height="16" fill="#16a34a" viewBox="0 0 20 20" class="shrink-0"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
    {{ session('success') }}
</div>
@endif

<div class="flex items-start justify-between flex-wrap gap-3 mb-5">
    <div>
        <h1 class="font-mono text-xl font-bold text-slate-900 m-0 mb-1.5">{{ $journalEntry->reference }}</h1>
        @if($journalEntry->isPosted())
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
    </div>
    <div class="flex items-center gap-2.5 flex-wrap">
        @if($journalEntry->isDraft())
            <a href="{{ route('journal-entries.edit', $journalEntry) }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-sm font-medium bg-slate-100 text-slate-600 border border-slate-200 hover:bg-slate-200 transition-colors no-underline">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                Edit
            </a>
            <form id="post-form" method="POST" action="{{ route('journal-entries.post', $journalEntry) }}" class="hidden">@csrf</form>
            <button type="button" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-sm font-medium bg-green-50 text-green-700 border border-green-200 hover:bg-green-100 transition-colors cursor-pointer" onclick="confirmPost()">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 11-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                Posting
            </button>
            <form id="del-form" method="POST" action="{{ route('journal-entries.destroy', $journalEntry) }}" class="hidden">@csrf @method('DELETE')</form>
            <button type="button" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-sm font-medium bg-red-50 text-red-600 border border-red-200 hover:bg-red-100 transition-colors cursor-pointer" onclick="confirmDelete('del-form', '{{ addslashes($journalEntry->reference) }}')">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"/><path d="M10 11v6M14 11v6"/></svg>
                Hapus
            </button>
        @endif
        <button type="button" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-sm font-medium bg-slate-100 text-slate-600 border border-slate-200 hover:bg-slate-200 transition-colors cursor-pointer" onclick="window.print()">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
            Print
        </button>
    </div>
</div>

{{-- Meta info --}}
<div class="bg-white rounded-xl shadow-sm p-6 mb-3.5">
    <div class="grid grid-cols-[repeat(auto-fill,minmax(180px,1fr))] gap-4">
        <div class="flex flex-col gap-0.5">
            <div class="text-[11px] font-semibold text-slate-400 uppercase tracking-wide">Organisasi</div>
            <div class="text-sm font-medium text-slate-900 mt-0.5">{{ $journalEntry->organization->name }}</div>
        </div>
        <div class="flex flex-col gap-0.5">
            <div class="text-[11px] font-semibold text-slate-400 uppercase tracking-wide">Tanggal</div>
            <div class="text-sm font-medium text-slate-900 mt-0.5">{{ $journalEntry->entry_date->format('d F Y') }}</div>
        </div>
        <div class="flex flex-col gap-0.5">
            <div class="text-[11px] font-semibold text-slate-400 uppercase tracking-wide">Dibuat oleh</div>
            <div class="text-sm font-medium text-slate-900 mt-0.5">{{ $journalEntry->creator->name ?? '-' }}</div>
        </div>
        @if($journalEntry->isPosted())
        <div class="flex flex-col gap-0.5">
            <div class="text-[11px] font-semibold text-slate-400 uppercase tracking-wide">Diposting oleh</div>
            <div class="text-sm font-medium text-slate-900 mt-0.5">{{ $journalEntry->poster->name ?? '-' }}</div>
        </div>
        <div class="flex flex-col gap-0.5">
            <div class="text-[11px] font-semibold text-slate-400 uppercase tracking-wide">Waktu Posting</div>
            <div class="text-sm font-medium text-slate-900 mt-0.5">{{ $journalEntry->posted_at?->format('d/m/Y H:i') }}</div>
        </div>
        @endif
        @if($journalEntry->description)
        <div class="flex flex-col gap-0.5 col-span-full">
            <div class="text-[11px] font-semibold text-slate-400 uppercase tracking-wide">Keterangan</div>
            <div class="text-sm font-medium text-slate-900 mt-0.5">{{ $journalEntry->description }}</div>
        </div>
        @endif
    </div>
</div>

{{-- Lines --}}
<div class="bg-white rounded-xl shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full border-collapse">
            <thead>
                <tr class="bg-slate-50 border-b border-slate-100">
                    <th class="px-4 py-3 text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wide w-9">#</th>
                    <th class="px-4 py-3 text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wide w-[140px]">Kode Akun</th>
                    <th class="px-4 py-3 text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wide">Nama Akun</th>
                    <th class="px-4 py-3 text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wide">Keterangan</th>
                    <th class="px-4 py-3 text-right text-[11px] font-semibold text-slate-400 uppercase tracking-wide w-[160px]">Debit (Rp)</th>
                    <th class="px-4 py-3 text-right text-[11px] font-semibold text-slate-400 uppercase tracking-wide w-[160px]">Kredit (Rp)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($journalEntry->lines as $i => $line)
                <tr class="border-b border-slate-50 hover:bg-slate-50 transition-colors last:border-b-0">
                    <td class="px-4 py-3 text-xs text-slate-400 align-middle">{{ $i + 1 }}</td>
                    <td class="px-4 py-3 align-middle">
                        <span class="font-mono text-xs font-bold bg-slate-100 text-slate-500 px-2 py-0.5 rounded">{{ $line->account->code }}</span>
                    </td>
                    <td class="px-4 py-3 text-sm font-medium text-slate-800 align-middle">{{ $line->account->name }}</td>
                    <td class="px-4 py-3 text-xs text-slate-500 align-middle">{{ $line->description ?: '-' }}</td>
                    <td class="px-4 py-3 text-right font-mono text-sm text-blue-600 align-middle">
                        @if($line->debit > 0)
                            {{ number_format($line->debit, 0, ',', '.') }}
                        @else
                            <span class="text-slate-200">–</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-right font-mono text-sm text-green-600 align-middle">
                        @if($line->credit > 0)
                            {{ number_format($line->credit, 0, ',', '.') }}
                        @else
                            <span class="text-slate-200">–</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="bg-slate-50 border-t-2 border-slate-100">
                    <td colspan="4" class="px-4 py-3 text-right text-[11px] font-bold text-slate-400 uppercase tracking-wide">Total</td>
                    <td class="px-4 py-3 text-right font-mono text-sm font-bold text-blue-700">{{ number_format($journalEntry->total_debit, 0, ',', '.') }}</td>
                    <td class="px-4 py-3 text-right font-mono text-sm font-bold text-green-700">{{ number_format($journalEntry->total_credit, 0, ',', '.') }}</td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

<script>
function confirmPost() {
    if (window.confirmModal) {
        confirmModal('Posting Jurnal', 'Yakin ingin memposting jurnal <strong>{{ addslashes($journalEntry->reference) }}</strong>?<br>Jurnal yang sudah diposting tidak dapat diedit atau dihapus.', function() {
            document.getElementById('post-form').submit();
        });
    } else if (confirm('Posting jurnal ini? Data tidak dapat diedit setelah diposting.')) {
        document.getElementById('post-form').submit();
    }
}
</script>
</x-layouts.app>
