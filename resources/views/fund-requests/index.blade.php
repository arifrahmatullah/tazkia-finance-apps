<x-layouts.app title="Pengajuan Dana Saya">

<div class="flex items-center justify-between mb-5">
    <div>
        <h2 class="text-lg font-bold text-slate-900 m-0 mb-0.5">Pengajuan Dana Saya</h2>
        <p class="text-xs text-slate-400 m-0">Riwayat pengajuan dan status persetujuan</p>
    </div>
    <a href="{{ route('fund-requests.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-gradient-to-br from-orange-400 to-orange-500 text-white text-sm font-semibold shadow-sm hover:-translate-y-px transition-all no-underline">
        <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg>
        Buat Pengajuan
    </a>
</div>

@if(session('success'))
<div class="flex items-center gap-2.5 px-4 py-3 bg-green-50 border border-green-200 rounded-xl mb-4 text-sm text-green-700">
    <svg width="16" height="16" fill="#16a34a" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
    {{ session('success') }}
</div>
@endif

@if($errors->any())
<div class="flex items-start gap-2.5 px-4 py-3 bg-red-50 border border-red-200 rounded-xl mb-4 text-sm text-red-600">
    {{ $errors->first() }}
</div>
@endif

<form method="GET" action="{{ route('fund-requests.index') }}" class="flex gap-2.5 flex-wrap items-center mb-4">
    <select name="status" class="px-3 py-2 border border-slate-200 rounded-xl text-sm text-slate-700 bg-white outline-none focus:border-orange-400">
        <option value="">Semua Status</option>
        <option value="draft"    {{ request('status') === 'draft'    ? 'selected' : '' }}>Draft</option>
        <option value="pending"  {{ request('status') === 'pending'  ? 'selected' : '' }}>Menunggu Approval</option>
        <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Disetujui</option>
        <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Ditolak</option>
    </select>
    <div class="relative flex-1 min-w-[180px]">
        <svg width="15" height="15" fill="none" stroke="#94a3b8" stroke-width="2" viewBox="0 0 24 24" class="absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari no. pengajuan atau judul..." class="pl-9 w-full px-3 py-2 border border-slate-200 rounded-xl text-sm text-slate-700 bg-white outline-none focus:border-orange-400">
    </div>
    <button type="submit" class="px-4 py-2 rounded-xl border-0 cursor-pointer text-sm font-semibold bg-gradient-to-br from-orange-400 to-orange-500 text-white">Cari</button>
    @if(request()->hasAny(['search','status']))
        <a href="{{ route('fund-requests.index') }}" class="px-3.5 py-2 rounded-xl border border-slate-200 text-sm text-slate-500 no-underline bg-white">Reset</a>
    @endif
</form>

<div class="bg-white rounded-xl shadow-sm overflow-hidden">
    @if($fundRequests->isEmpty())
        <div class="py-12 px-5 text-center text-slate-400">
            <svg width="40" height="40" fill="none" stroke="#cbd5e1" stroke-width="1.5" viewBox="0 0 24 24" class="mx-auto mb-2.5 block"><path d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <p class="text-sm m-0">Belum ada pengajuan. Klik "Buat Pengajuan" untuk mulai.</p>
        </div>
    @else
        <table class="w-full border-collapse">
            <thead>
                <tr class="bg-slate-50 border-b border-slate-100">
                    <th class="px-4 py-3 text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wide w-[150px]">No. Pengajuan</th>
                    <th class="px-4 py-3 text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wide">Judul</th>
                    <th class="px-4 py-3 text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wide w-[120px]">Departemen</th>
                    <th class="px-4 py-3 text-right text-[11px] font-semibold text-slate-400 uppercase tracking-wide w-[130px]">Jumlah (Rp)</th>
                    <th class="px-4 py-3 text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wide w-[130px]">Status</th>
                    <th class="px-4 py-3 text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wide w-[140px]">Progres Approval</th>
                    <th class="px-4 py-3 text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wide w-[130px]">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($fundRequests as $fr)
                @php
                    $statusConfig = [
                        'draft'    => ['bg-slate-100 text-slate-500',   'Draft'],
                        'pending'  => ['bg-yellow-100 text-yellow-700', 'Menunggu Approval'],
                        'approved' => ['bg-green-100 text-green-700',   'Disetujui'],
                        'rejected' => ['bg-red-100 text-red-600',       'Ditolak'],
                    ];
                    [$cls, $label] = $statusConfig[$fr->status];
                @endphp
                <tr class="border-b border-slate-50 hover:bg-slate-50 transition-colors last:border-0">
                    <td class="px-4 py-3 text-sm text-slate-600 align-middle">
                        <div class="font-mono text-sm font-bold text-orange-500">{{ $fr->reference }}</div>
                        <div class="text-[11px] text-slate-400 mt-0.5">{{ $fr->created_at->format('d/m/Y') }}</div>
                    </td>
                    <td class="px-4 py-3 text-sm text-slate-600 align-middle">
                        <div class="font-medium text-sm text-slate-800">{{ $fr->title }}</div>
                        @if($fr->purpose)
                            <div class="text-xs text-slate-400 mt-0.5 truncate max-w-[200px]">{{ $fr->purpose }}</div>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-xs text-slate-500 align-middle">{{ $fr->department->name }}</td>
                    <td class="px-4 py-3 text-right font-mono text-sm font-semibold text-slate-900 align-middle">
                        {{ number_format($fr->amount, 0, ',', '.') }}
                    </td>
                    <td class="px-4 py-3 text-sm text-slate-600 align-middle">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold {{ $cls }}">{{ $label }}</span>
                        @if($fr->isPending())
                            <div class="text-[11px] text-yellow-700 mt-0.5">Level {{ $fr->current_step }}/{{ $fr->total_steps }}</div>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-sm text-slate-600 align-middle">
                        @if($fr->total_steps > 0)
                        <div class="flex items-center gap-1">
                            @foreach($fr->approvals->sortBy('step') as $approval)
                            @php
                                $dotClass = match($approval->status) {
                                    'approved' => 'w-2.5 h-2.5 rounded-full bg-green-500',
                                    'rejected' => 'w-2.5 h-2.5 rounded-full bg-red-500',
                                    default    => $fr->current_step == $approval->step ? 'w-2.5 h-2.5 rounded-full bg-orange-400' : 'w-2.5 h-2.5 rounded-full bg-slate-200',
                                };
                            @endphp
                            <div title="Level {{ $approval->step }}: {{ ucfirst($approval->status) }}" class="{{ $dotClass }}"></div>
                            @if(!$loop->last)<div class="w-4 h-0.5 bg-slate-200 rounded"></div>@endif
                            @endforeach
                        </div>
                        @else
                            <span class="text-xs text-slate-300">-</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-sm text-slate-600 align-middle">
                        <div class="flex gap-1 flex-wrap">
                            <a href="{{ route('fund-requests.show', $fr) }}" class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-xs font-medium bg-slate-100 text-slate-600 hover:bg-slate-200 transition-colors no-underline">Lihat</a>
                            @if($fr->isDraft())
                                <a href="{{ route('fund-requests.edit', $fr) }}" class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-xs font-medium bg-blue-50 text-blue-600 hover:bg-blue-100 transition-colors no-underline">Edit</a>
                                <form id="del-fr-{{ $fr->id }}" method="POST" action="{{ route('fund-requests.destroy', $fr) }}">@csrf @method('DELETE')</form>
                                <button type="button" class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-xs font-medium bg-red-50 text-red-600 hover:bg-red-100 transition-colors border-0 cursor-pointer" onclick="confirmDelete('del-fr-{{ $fr->id }}', '{{ addslashes($fr->reference) }}')">Hapus</button>
                            @endif
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        @if($fundRequests->hasPages())
        <div class="px-4 py-3.5 border-t border-slate-100 flex justify-end gap-1">
            @if($fundRequests->onFirstPage())
                <span class="inline-flex items-center px-2.5 py-1.5 border border-slate-200 rounded-lg text-xs text-slate-300 pointer-events-none">&laquo;</span>
            @else
                <a href="{{ $fundRequests->previousPageUrl() }}" class="inline-flex items-center px-2.5 py-1.5 border border-slate-200 rounded-lg text-xs text-slate-500 no-underline">&laquo;</a>
            @endif
            @foreach($fundRequests->getUrlRange(max(1,$fundRequests->currentPage()-2), min($fundRequests->lastPage(),$fundRequests->currentPage()+2)) as $page => $url)
                <a href="{{ $url }}" class="inline-flex items-center px-2.5 py-1.5 border rounded-lg text-xs no-underline {{ $page == $fundRequests->currentPage() ? 'bg-orange-500 border-orange-500 text-white' : 'border-slate-200 text-slate-500' }}">{{ $page }}</a>
            @endforeach
            @if($fundRequests->hasMorePages())
                <a href="{{ $fundRequests->nextPageUrl() }}" class="inline-flex items-center px-2.5 py-1.5 border border-slate-200 rounded-lg text-xs text-slate-500 no-underline">&raquo;</a>
            @else
                <span class="inline-flex items-center px-2.5 py-1.5 border border-slate-200 rounded-lg text-xs text-slate-300 pointer-events-none">&raquo;</span>
            @endif
        </div>
        @endif
    @endif
</div>
</x-layouts.app>
