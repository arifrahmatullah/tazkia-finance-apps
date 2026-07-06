<x-layouts.app title="Setting Approval Berjenjang">

<div class="flex items-center justify-between mb-5">
    <div>
        <h2 class="text-lg font-bold text-slate-900 m-0 mb-0.5">Setting Approval Berjenjang</h2>
        <p class="text-xs text-slate-400 m-0">Konfigurasi rantai persetujuan pengajuan dana per jabatan</p>
    </div>
    <a href="{{ route('approval-settings.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-gradient-to-br from-orange-400 to-orange-500 text-white text-sm font-semibold shadow-sm hover:-translate-y-px transition-all no-underline">
        <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg>
        Tambah Setting
    </a>
</div>

@if(session('success'))
<div class="flex items-center gap-2.5 px-4 py-3 bg-green-50 border border-green-200 rounded-xl mb-4 text-sm text-green-700">
    <svg width="16" height="16" fill="#16a34a" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
    {{ session('success') }}
</div>
@endif

{{-- Filter Bar --}}
<form method="GET" action="{{ route('approval-settings.index') }}" class="flex gap-2.5 flex-wrap items-center mb-5">

    <div class="relative flex-1 min-w-[220px]">
        <svg width="15" height="15" fill="none" stroke="#94a3b8" stroke-width="2" viewBox="0 0 24 24"
            class="absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none">
            <circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/>
        </svg>
        <input type="text" id="search-input" placeholder="Cari jabatan pengaju atau approver…"
            class="w-full pl-9 pr-4 py-2 border border-slate-200 rounded-xl text-sm text-slate-700 bg-white outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100 transition-colors">
    </div>

    @if($organizations->count() > 1)
    <select name="organization_id" class="no-select2 px-3 py-2 border border-slate-200 rounded-xl text-sm text-slate-700 bg-white outline-none focus:border-orange-400 min-w-[180px] cursor-pointer" onchange="this.form.submit()">
        <option value="">Semua Organisasi</option>
        @foreach($organizations as $org)
            <option value="{{ $org->id }}" {{ request('organization_id') == $org->id ? 'selected' : '' }}>{{ $org->name }}</option>
        @endforeach
    </select>
    @endif

</form>

@if($grouped->isEmpty())
    <div class="bg-white rounded-xl shadow-sm overflow-hidden mb-4">
        <div class="py-12 px-5 text-center text-slate-400">
            <svg width="40" height="40" fill="none" stroke="#cbd5e1" stroke-width="1.5" viewBox="0 0 24 24" class="mx-auto mb-2.5 block">
                <path d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
            </svg>
            <p class="text-sm m-0">Belum ada konfigurasi approval. Klik "Tambah Setting" untuk mulai.</p>
        </div>
    </div>
@else

    <div id="cards-wrapper">
    @foreach($grouped as $chain)
    @php $first = $chain->first(); @endphp
    <div class="approval-card bg-white rounded-xl shadow-sm overflow-hidden mb-4"
         data-search="{{ strtolower(($first->organization?->name ?? '') . ' ' . ($first->requesterPosition?->name ?? '') . ' ' . $chain->pluck('approverPosition.name')->implode(' ')) }}">

        <div class="flex items-center gap-3 px-5 py-3.5 border-b border-slate-100 bg-slate-50">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-lg bg-orange-100 flex items-center justify-center shrink-0">
                    <svg width="15" height="15" fill="none" stroke="#f97316" stroke-width="2" viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                </div>
                <div>
                    <div class="text-[11px] text-slate-400">{{ $first->organization?->name ?? '(organisasi dihapus)' }}</div>
                    <div class="text-sm font-bold text-slate-800">{{ $first->requesterPosition?->name ?? '(jabatan dihapus)' }}</div>
                </div>
            </div>
            <span class="ml-auto text-xs text-slate-400">{{ $chain->count() }} level</span>
            <a href="{{ route('approval-settings.edit-chain', ['organization_id' => $first->organization_id, 'requester_position_id' => $first->requester_position_id]) }}"
               class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold bg-blue-50 text-blue-600 hover:bg-blue-100 transition-colors no-underline">
                <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                Edit Rantai
            </a>
        </div>

        <table class="w-full border-collapse">
            <thead>
                <tr class="border-b border-slate-100">
                    <th class="px-5 py-2.5 text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wide w-[70px]">Level</th>
                    <th class="px-5 py-2.5 text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wide">Disetujui Oleh</th>
                    <th class="px-5 py-2.5 w-[70px]"></th>
                </tr>
            </thead>
            <tbody>
                @foreach($chain->sortBy('step') as $setting)
                <tr class="border-b border-slate-50 hover:bg-slate-50/60 transition-colors last:border-0">
                    <td class="px-5 py-3 align-middle">
                        <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-orange-500 text-white text-xs font-bold">{{ $setting->step }}</span>
                    </td>
                    <td class="px-5 py-3 align-middle">
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-orange-50 rounded-lg text-xs text-orange-700 font-medium">
                            <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                            {{ $setting->approverPosition?->name ?? '(jabatan dihapus)' }}
                        </span>
                    </td>
                    <td class="px-5 py-3 align-middle text-right">
                        <form id="del-as-{{ $setting->id }}" method="POST" action="{{ route('approval-settings.destroy', $setting) }}">@csrf @method('DELETE')</form>
                        <button type="button"
                            class="inline-flex items-center p-1.5 rounded-lg text-slate-300 hover:text-red-500 hover:bg-red-50 transition-colors border-0 bg-transparent cursor-pointer"
                            title="Hapus level ini"
                            onclick="confirmDelete('del-as-{{ $setting->id }}', 'Level {{ $setting->step }} — {{ addslashes($setting->approverPosition?->name ?? '') }}')">
                            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endforeach
    </div>

    {{-- No results --}}
    <div id="no-results" class="hidden bg-white rounded-xl shadow-sm py-10 text-center text-slate-400 text-sm">
        <svg width="36" height="36" fill="none" stroke="#cbd5e1" stroke-width="1.5" viewBox="0 0 24 24" class="mx-auto mb-2 block">
            <circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/>
        </svg>
        Tidak ada hasil yang cocok.
    </div>

    {{-- Pagination --}}
    <div id="pagination-wrapper" class="flex items-center justify-between mt-2 pt-4">
        <div id="pagination-info" class="text-xs text-slate-400"></div>
        <div id="pagination-nav" class="flex gap-1.5"></div>
    </div>

@endif

<script>
const PER_PAGE = 8;
let currentPage = 1;
let filtered = [];

const allCards   = Array.from(document.querySelectorAll('.approval-card'));
const noResults  = document.getElementById('no-results');
const info       = document.getElementById('pagination-info');
const nav        = document.getElementById('pagination-nav');

function applyFilter() {
    const q = (document.getElementById('search-input').value || '').toLowerCase().trim();
    filtered = q ? allCards.filter(c => c.dataset.search.includes(q)) : allCards.slice();
    currentPage = 1;
    render();
}

function render() {
    const total      = filtered.length;
    const totalPages = Math.max(1, Math.ceil(total / PER_PAGE));
    currentPage      = Math.min(currentPage, totalPages);
    const start      = (currentPage - 1) * PER_PAGE;
    const end        = start + PER_PAGE;

    allCards.forEach(c => c.style.display = 'none');
    filtered.slice(start, end).forEach(c => c.style.display = '');

    if (noResults) noResults.classList.toggle('hidden', total > 0);

    // Info
    if (info) {
        info.textContent = total > 0
            ? `Menampilkan ${start + 1}–${Math.min(end, total)} dari ${total} rantai`
            : '';
    }

    // Nav
    if (nav) renderNav(totalPages);
}

function renderNav(totalPages) {
    if (totalPages <= 1) { nav.innerHTML = ''; return; }

    const pages = smartPages(currentPage, totalPages);
    let html = '';

    // Prev
    html += currentPage === 1
        ? `<span class="px-3 py-1.5 rounded-lg text-xs text-slate-300 border border-slate-200 bg-slate-50 select-none">‹</span>`
        : `<button onclick="goTo(${currentPage - 1})" class="px-3 py-1.5 rounded-lg text-xs text-slate-600 border border-slate-200 bg-white hover:bg-slate-50 cursor-pointer">‹</button>`;

    pages.forEach(p => {
        if (p === '…') {
            html += `<span class="px-2 py-1.5 text-xs text-slate-400 select-none">…</span>`;
        } else if (p === currentPage) {
            html += `<span class="px-3 py-1.5 rounded-lg text-xs font-semibold bg-orange-500 text-white select-none">${p}</span>`;
        } else {
            html += `<button onclick="goTo(${p})" class="px-3 py-1.5 rounded-lg text-xs text-slate-600 border border-slate-200 bg-white hover:bg-slate-50 cursor-pointer">${p}</button>`;
        }
    });

    // Next
    html += currentPage === totalPages
        ? `<span class="px-3 py-1.5 rounded-lg text-xs text-slate-300 border border-slate-200 bg-slate-50 select-none">›</span>`
        : `<button onclick="goTo(${currentPage + 1})" class="px-3 py-1.5 rounded-lg text-xs text-slate-600 border border-slate-200 bg-white hover:bg-slate-50 cursor-pointer">›</button>`;

    nav.innerHTML = html;
}

function goTo(page) {
    currentPage = page;
    render();
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

function smartPages(cur, total) {
    if (total <= 7) return Array.from({ length: total }, (_, i) => i + 1);
    const pages = new Set([1, total, cur]);
    if (cur > 1) pages.add(cur - 1);
    if (cur < total) pages.add(cur + 1);
    const sorted = [...pages].sort((a, b) => a - b);
    const result = [];
    sorted.forEach((p, i) => {
        if (i > 0 && p - sorted[i - 1] > 1) result.push('…');
        result.push(p);
    });
    return result;
}

document.getElementById('search-input').addEventListener('input', applyFilter);

// Init
applyFilter();
</script>
</x-layouts.app>
