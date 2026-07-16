<x-layouts.app title="Template Jurnal">

<div class="flex items-center justify-between mb-5 gap-4 flex-wrap">
    <div>
        <h2 class="text-lg font-bold text-slate-900 m-0 mb-0.5">Template Jurnal</h2>
        <p class="text-xs text-slate-400 m-0">Pola jurnal siap pakai untuk mempercepat input jurnal umum</p>
    </div>
    <a href="{{ route('journal-templates.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-gradient-to-br from-orange-400 to-orange-500 text-white text-sm font-semibold shadow-sm hover:-translate-y-px transition-all no-underline">
        <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg>
        Buat Template
    </a>
</div>

@if(session('success'))
<div class="flex items-center gap-2.5 px-4 py-3 bg-green-50 border border-green-200 rounded-xl mb-4 text-sm text-green-700">
    <svg width="16" height="16" fill="#16a34a" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
    {{ session('success') }}
</div>
@endif

{{-- Filter --}}
<form method="GET" action="{{ route('journal-templates.index') }}" class="bg-white rounded-xl shadow-sm px-4 py-3 mb-4 flex gap-2.5 flex-wrap items-center">
    @if($organizations->count() > 1)
    <select name="organization_id" onchange="this.form.submit()"
        class="px-3 py-2 border border-slate-200 rounded-lg text-sm text-slate-700 bg-white outline-none focus:border-orange-400 transition-colors">
        <option value="">Semua Organisasi</option>
        @foreach($organizations as $org)
            <option value="{{ $org->id }}" {{ request('organization_id') == $org->id ? 'selected' : '' }}>{{ $org->name }}</option>
        @endforeach
    </select>
    @endif
    <div class="relative flex-1 min-w-[200px]">
        <svg width="14" height="14" fill="none" stroke="#94a3b8" stroke-width="2" viewBox="0 0 24 24" class="absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari kode, nama, atau kategori..."
            class="pl-9 w-full px-3 py-2 border border-slate-200 rounded-lg text-sm text-slate-700 bg-white outline-none focus:border-orange-400 transition-colors">
    </div>
    <button type="submit" class="px-4 py-2 rounded-lg border-0 cursor-pointer text-sm font-semibold bg-gradient-to-br from-orange-400 to-orange-500 text-white">Cari</button>
    @if(request()->hasAny(['search','organization_id']))
        <a href="{{ route('journal-templates.index') }}" class="px-3.5 py-2 rounded-lg border border-slate-200 text-sm text-slate-500 no-underline bg-white hover:bg-slate-50 transition-colors">Reset</a>
    @endif
</form>

@if($templates->isEmpty())
<div class="bg-white rounded-xl shadow-sm py-16 px-5 text-center">
    <div class="w-14 h-14 rounded-2xl bg-slate-50 border border-slate-100 flex items-center justify-center mx-auto mb-4">
        <svg width="24" height="24" fill="none" stroke="#94a3b8" stroke-width="1.5" viewBox="0 0 24 24"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
    </div>
    <div class="text-sm font-semibold text-slate-700 mb-1">Belum ada template jurnal</div>
    <div class="text-xs text-slate-400 mb-4">Buat template untuk transaksi berulang, misalnya pencairan dana atau pembayaran gaji.</div>
    <a href="{{ route('journal-templates.create') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-gradient-to-br from-orange-400 to-orange-500 text-white text-sm font-semibold no-underline">
        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg>
        Buat Template
    </a>
</div>
@else

<div class="flex flex-col gap-3">
    @foreach($templates as $template)
    <div class="bg-white rounded-xl shadow-sm overflow-hidden {{ $template->is_active ? '' : 'opacity-60' }}">
        <div class="px-5 py-4">
            <div class="flex items-start justify-between gap-3 flex-wrap">
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 flex-wrap mb-1">
                        <span class="font-mono text-sm font-bold text-orange-500">{{ $template->code }}</span>
                        @if($template->category)
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-indigo-50 text-indigo-600">{{ $template->category }}</span>
                        @endif
                        @if(!$template->is_active)
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-slate-100 text-slate-500">Nonaktif</span>
                        @endif
                        <span class="text-[11px] text-slate-400">{{ $template->organization?->name }}</span>
                    </div>
                    <div class="text-[15px] font-bold text-slate-900">{{ $template->name }}</div>
                </div>
                <div class="flex items-center gap-2 flex-shrink-0">
                    <a href="{{ route('journal-templates.edit', $template) }}"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold bg-blue-50 text-blue-600 hover:bg-blue-100 transition-colors no-underline">
                        <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                        Edit
                    </a>
                    <form id="del-jt-{{ $template->id }}" method="POST" action="{{ route('journal-templates.destroy', $template) }}">@csrf @method('DELETE')</form>
                    <button type="button"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold bg-red-50 text-red-600 hover:bg-red-100 transition-colors border-0 cursor-pointer"
                        onclick="if(confirm('Hapus template {{ addslashes($template->code) }}?')) document.getElementById('del-jt-{{ $template->id }}').submit()">
                        <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/></svg>
                        Hapus
                    </button>
                </div>
            </div>

            {{-- Baris template --}}
            <div class="mt-3 bg-slate-50 rounded-[10px] px-4 py-3">
                @foreach($template->details as $detail)
                <div class="flex items-center gap-2.5 py-1 text-[13px] {{ !$loop->last ? 'border-b border-slate-100' : '' }}">
                    <span class="inline-flex items-center justify-center w-[52px] py-0.5 rounded-md text-[10px] font-bold flex-shrink-0
                        {{ $detail->isDebit() ? 'bg-blue-100 text-blue-700' : 'bg-green-100 text-green-700' }}">
                        {{ $detail->isDebit() ? 'DEBIT' : 'KREDIT' }}
                    </span>
                    <span class="font-mono text-xs text-slate-500 flex-shrink-0 {{ $detail->isCredit() ? 'pl-6' : '' }}">{{ $detail->account?->code }}</span>
                    <span class="font-medium text-slate-800 truncate">{{ $detail->account?->name }}</span>
                    @if($detail->description)
                    <span class="text-xs text-slate-400 truncate">— {{ $detail->description }}</span>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endforeach
</div>

{{-- Pagination --}}
<div class="mt-4 flex items-center justify-between gap-3 flex-wrap">
    <span class="text-xs text-slate-400">
        Menampilkan {{ $templates->firstItem() ?? 0 }}–{{ $templates->lastItem() ?? 0 }} dari {{ $templates->total() }} template
    </span>
    @if($templates->hasPages())
    <div>{{ $templates->links() }}</div>
    @endif
</div>
@endif

</x-layouts.app>
