<x-layouts.app title="Audit Log">

<div class="flex items-center justify-between mb-5">
    <div>
        <h2 class="text-lg font-bold text-slate-900 m-0 mb-0.5">Audit Log</h2>
        <p class="text-xs text-slate-400 m-0">Riwayat semua perubahan data di sistem</p>
    </div>
</div>

{{-- Filter --}}
<form method="GET" action="{{ route('audit-logs.index') }}" class="flex gap-2.5 flex-wrap items-center mb-5">
    <div class="relative flex-1 min-w-[180px]">
        <svg width="14" height="14" fill="none" stroke="#94a3b8" stroke-width="2" viewBox="0 0 24 24"
            class="absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none">
            <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/>
        </svg>
        <select name="user_id" class="no-select2 w-full pl-9 pr-4 py-2 border border-slate-200 rounded-xl text-sm text-slate-700 bg-white outline-none focus:border-orange-400 transition-colors" onchange="this.form.submit()">
            <option value="">Semua Pengguna</option>
            @foreach($users as $u)
                <option value="{{ $u->id }}" {{ request('user_id') == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
            @endforeach
        </select>
    </div>

    <select name="model" class="no-select2 px-3 py-2 border border-slate-200 rounded-xl text-sm text-slate-700 bg-white outline-none focus:border-orange-400 min-w-[160px] cursor-pointer" onchange="this.form.submit()">
        <option value="">Semua Modul</option>
        @foreach($modelTypes as $mt)
            <option value="{{ $mt['value'] }}" {{ request('model') == $mt['value'] ? 'selected' : '' }}>{{ $mt['label'] }}</option>
        @endforeach
    </select>

    <select name="action" class="no-select2 px-3 py-2 border border-slate-200 rounded-xl text-sm text-slate-700 bg-white outline-none focus:border-orange-400 min-w-[130px] cursor-pointer" onchange="this.form.submit()">
        <option value="">Semua Aksi</option>
        <option value="created"  {{ request('action') == 'created'  ? 'selected' : '' }}>Dibuat</option>
        <option value="updated"  {{ request('action') == 'updated'  ? 'selected' : '' }}>Diubah</option>
        <option value="deleted"  {{ request('action') == 'deleted'  ? 'selected' : '' }}>Dihapus</option>
        <option value="restored" {{ request('action') == 'restored' ? 'selected' : '' }}>Dipulihkan</option>
    </select>

    <input type="date" name="date_from" value="{{ request('date_from') }}"
        class="px-3 py-2 border border-slate-200 rounded-xl text-sm text-slate-700 bg-white outline-none focus:border-orange-400 transition-colors">
    <input type="date" name="date_to" value="{{ request('date_to') }}"
        class="px-3 py-2 border border-slate-200 rounded-xl text-sm text-slate-700 bg-white outline-none focus:border-orange-400 transition-colors">

    @if(request()->hasAny(['action','model','user_id','date_from','date_to']))
    <a href="{{ route('audit-logs.index') }}" class="px-3 py-2 rounded-xl border border-slate-200 text-sm text-slate-500 hover:text-orange-500 hover:border-orange-300 transition-colors no-underline">Reset</a>
    @endif

    <button type="submit" class="px-4 py-2 rounded-xl bg-slate-700 text-white text-sm font-medium cursor-pointer border-0 hover:bg-slate-800 transition-colors">Filter</button>
</form>

<div class="bg-white rounded-xl shadow-sm overflow-hidden">
    @if($logs->isEmpty())
        <div class="py-12 text-center text-slate-400">
            <svg width="40" height="40" fill="none" stroke="#cbd5e1" stroke-width="1.5" viewBox="0 0 24 24" class="mx-auto mb-2.5 block">
                <path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
            </svg>
            <p class="text-sm m-0">Belum ada log aktivitas.</p>
        </div>
    @else
    <table class="w-full border-collapse">
        <thead>
            <tr class="bg-slate-50 border-b border-slate-100">
                <th class="px-5 py-3 text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wide w-36">Waktu</th>
                <th class="px-5 py-3 text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wide">Pengguna</th>
                <th class="px-5 py-3 text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wide w-24">Aksi</th>
                <th class="px-5 py-3 text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wide">Modul</th>
                <th class="px-5 py-3 text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wide">IP</th>
                <th class="px-5 py-3 w-16"></th>
            </tr>
        </thead>
        <tbody>
            @foreach($logs as $log)
            @php
                $color = \App\Models\AuditLog::actionColor($log->action);
            @endphp
            <tr class="border-b border-slate-50 hover:bg-slate-50/60 transition-colors last:border-0">
                <td class="px-5 py-3 align-middle">
                    <div class="text-xs text-slate-700 font-mono">{{ $log->created_at->format('d/m/Y') }}</div>
                    <div class="text-[11px] text-slate-400">{{ $log->created_at->format('H:i:s') }}</div>
                </td>
                <td class="px-5 py-3 align-middle">
                    <div class="text-sm text-slate-700 font-semibold">{{ $log->user_name ?? '—' }}</div>
                </td>
                <td class="px-5 py-3 align-middle">
                    @php
                        $pill = match($color) {
                            'green'  => 'bg-green-100 text-green-700',
                            'blue'   => 'bg-blue-100 text-blue-700',
                            'red'    => 'bg-red-100 text-red-600',
                            'orange' => 'bg-orange-100 text-orange-700',
                            default  => 'bg-slate-100 text-slate-600',
                        };
                    @endphp
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold {{ $pill }}">
                        {{ \App\Models\AuditLog::actionLabel($log->action) }}
                    </span>
                </td>
                <td class="px-5 py-3 align-middle">
                    <div class="text-sm text-slate-700">{{ \App\Models\AuditLog::modelLabel($log->auditable_type) }}</div>
                    <div class="text-[11px] text-slate-400 font-mono truncate max-w-[200px]">{{ $log->auditable_id }}</div>
                </td>
                <td class="px-5 py-3 align-middle text-xs text-slate-400 font-mono">{{ $log->ip_address }}</td>
                <td class="px-5 py-3 align-middle text-right">
                    <a href="{{ route('audit-logs.show', $log) }}"
                       class="inline-flex items-center p-1.5 rounded-lg text-slate-400 hover:text-blue-500 hover:bg-blue-50 transition-colors no-underline" title="Lihat detail">
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    </a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    @if($logs->hasPages())
    <div class="flex items-center justify-between px-5 py-3.5 border-t border-slate-100">
        <div class="text-xs text-slate-400">
            Menampilkan {{ $logs->firstItem() }}–{{ $logs->lastItem() }} dari {{ $logs->total() }} entri
        </div>
        <div class="flex gap-1.5">
            @if($logs->onFirstPage())
                <span class="px-3 py-1.5 rounded-lg text-xs text-slate-300 border border-slate-200 bg-slate-50">‹</span>
            @else
                <a href="{{ $logs->previousPageUrl() }}" class="px-3 py-1.5 rounded-lg text-xs text-slate-600 border border-slate-200 bg-white hover:bg-slate-50 no-underline">‹</a>
            @endif
            @foreach($logs->getUrlRange(max(1, $logs->currentPage()-2), min($logs->lastPage(), $logs->currentPage()+2)) as $page => $url)
                @if($page == $logs->currentPage())
                    <span class="px-3 py-1.5 rounded-lg text-xs font-semibold bg-orange-500 text-white">{{ $page }}</span>
                @else
                    <a href="{{ $url }}" class="px-3 py-1.5 rounded-lg text-xs text-slate-600 border border-slate-200 bg-white hover:bg-slate-50 no-underline">{{ $page }}</a>
                @endif
            @endforeach
            @if($logs->hasMorePages())
                <a href="{{ $logs->nextPageUrl() }}" class="px-3 py-1.5 rounded-lg text-xs text-slate-600 border border-slate-200 bg-white hover:bg-slate-50 no-underline">›</a>
            @else
                <span class="px-3 py-1.5 rounded-lg text-xs text-slate-300 border border-slate-200 bg-slate-50">›</span>
            @endif
        </div>
    </div>
    @endif
    @endif
</div>

</x-layouts.app>
