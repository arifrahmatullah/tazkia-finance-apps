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

<form method="GET" action="{{ route('approval-settings.index') }}" class="flex gap-2.5 flex-wrap items-center mb-4">
    @if($organizations->count() > 1)
    <select name="organization_id" class="px-3 py-2 border border-slate-200 rounded-xl text-sm text-slate-700 bg-white outline-none focus:border-orange-400 min-w-[180px]" onchange="this.form.submit()">
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
            <svg width="40" height="40" fill="none" stroke="#cbd5e1" stroke-width="1.5" viewBox="0 0 24 24" class="mx-auto mb-2.5 block"><path d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
            <p class="text-sm m-0">Belum ada konfigurasi approval. Klik "Tambah Setting" untuk mulai mengatur.</p>
        </div>
    </div>
@else
    @foreach($grouped as $key => $chain)
    @php
        $first = $chain->first();
        $orgName = $first->organization->name;
        $posName = $first->requesterPosition->name;
    @endphp
    <div class="bg-white rounded-xl shadow-sm overflow-hidden mb-4">
        <div class="flex items-center gap-2.5 px-5 py-3.5 border-b-2 border-slate-100 bg-slate-50">
            <div>
                <div class="text-xs text-slate-400">{{ $orgName }}</div>
                <div class="text-base font-bold text-slate-900">
                    <svg width="14" height="14" fill="none" stroke="#f97316" stroke-width="2" viewBox="0 0 24 24" class="inline align-middle mr-1"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                    {{ $posName }}
                </div>
            </div>
            <span class="ml-auto text-xs text-slate-400">{{ $chain->count() }} level approval</span>
        </div>
        <table class="w-full border-collapse">
            <thead>
                <tr class="bg-slate-50 border-b border-slate-100">
                    <th class="px-4 py-3 text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wide w-[60px]">Level</th>
                    <th class="px-4 py-3 text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wide">Disetujui Oleh (Jabatan)</th>
                    <th class="px-4 py-3 text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wide w-[160px]">Batas Nominal</th>
                    <th class="px-4 py-3 text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wide w-[80px]">Status</th>
                    <th class="px-4 py-3 text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wide w-[100px]">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($chain->sortBy('step') as $setting)
                <tr class="border-b border-slate-50 hover:bg-slate-50 transition-colors last:border-0">
                    <td class="px-4 py-3 text-sm text-slate-600 align-middle">
                        <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-orange-500 text-white text-xs font-bold">{{ $setting->step }}</span>
                    </td>
                    <td class="px-4 py-3 text-sm text-slate-600 align-middle">
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-orange-50 rounded-lg text-xs text-orange-700 font-medium">
                            <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                            {{ $setting->approverPosition->name }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-sm text-slate-600 align-middle font-mono">
                        @if($setting->max_amount)
                            ≤ Rp {{ number_format($setting->max_amount, 0, ',', '.') }}
                        @else
                            <span class="text-slate-400">Tanpa batas</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-sm text-slate-600 align-middle">
                        @if($setting->is_active)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-green-100 text-green-700">Aktif</span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-slate-100 text-slate-500">Nonaktif</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-sm text-slate-600 align-middle">
                        <div class="flex gap-1.5">
                            <a href="{{ route('approval-settings.edit', $setting) }}" class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-xs font-medium bg-blue-50 text-blue-600 hover:bg-blue-100 transition-colors no-underline">Edit</a>
                            <form id="del-as-{{ $setting->id }}" method="POST" action="{{ route('approval-settings.destroy', $setting) }}">@csrf @method('DELETE')</form>
                            <button type="button" class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-xs font-medium bg-red-50 text-red-600 hover:bg-red-100 transition-colors border-0 cursor-pointer"
                                onclick="confirmDelete('del-as-{{ $setting->id }}', 'Level {{ $setting->step }} - {{ addslashes($setting->approverPosition->name) }}')">Hapus</button>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endforeach
@endif
</x-layouts.app>
