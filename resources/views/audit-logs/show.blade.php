<x-layouts.app title="Detail Audit Log">

<div class="flex items-center justify-between mb-5">
    <div>
        <a href="{{ route('audit-logs.index') }}"
           class="inline-flex items-center gap-1.5 text-xs text-slate-400 hover:text-orange-500 no-underline transition-colors mb-1">
            <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M19 12H5M12 5l-7 7 7 7"/></svg>
            Audit Log
        </a>
        <h2 class="text-lg font-bold text-slate-900 m-0">Detail Perubahan</h2>
    </div>
</div>

@php
    $color = \App\Models\AuditLog::actionColor($auditLog->action);
    $pill  = match($color) {
        'green'  => 'bg-green-100 text-green-700',
        'blue'   => 'bg-blue-100 text-blue-700',
        'red'    => 'bg-red-100 text-red-600',
        'orange' => 'bg-orange-100 text-orange-700',
        default  => 'bg-slate-100 text-slate-600',
    };
@endphp

{{-- Meta info --}}
<div class="bg-white rounded-xl shadow-sm px-5 py-4 mb-4">
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div>
            <div class="text-[11px] text-slate-400 uppercase tracking-wide font-semibold mb-0.5">Waktu</div>
            <div class="text-sm text-slate-700 font-mono">{{ $auditLog->created_at->format('d/m/Y H:i:s') }}</div>
        </div>
        <div>
            <div class="text-[11px] text-slate-400 uppercase tracking-wide font-semibold mb-0.5">Pengguna</div>
            <div class="text-sm text-slate-700 font-semibold">{{ $auditLog->user_name ?? '—' }}</div>
        </div>
        <div>
            <div class="text-[11px] text-slate-400 uppercase tracking-wide font-semibold mb-0.5">Aksi</div>
            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold {{ $pill }}">
                {{ \App\Models\AuditLog::actionLabel($auditLog->action) }}
            </span>
        </div>
        <div>
            <div class="text-[11px] text-slate-400 uppercase tracking-wide font-semibold mb-0.5">Modul</div>
            <div class="text-sm text-slate-700">{{ \App\Models\AuditLog::modelLabel($auditLog->auditable_type) }}</div>
        </div>
        <div class="sm:col-span-2">
            <div class="text-[11px] text-slate-400 uppercase tracking-wide font-semibold mb-0.5">ID Record</div>
            <div class="text-xs text-slate-500 font-mono break-all">{{ $auditLog->auditable_id }}</div>
        </div>
        <div class="sm:col-span-2">
            <div class="text-[11px] text-slate-400 uppercase tracking-wide font-semibold mb-0.5">URL</div>
            <div class="text-xs text-slate-500 truncate">{{ $auditLog->url ?? '—' }}</div>
        </div>
    </div>
</div>

{{-- Perubahan nilai --}}
@if($auditLog->action === 'updated')
<div class="bg-white rounded-xl shadow-sm overflow-hidden mb-4">
    <div class="px-5 py-3.5 border-b border-slate-100">
        <span class="text-sm font-bold text-slate-900">Perubahan Nilai</span>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full border-collapse">
            <thead>
                <tr class="bg-slate-50 border-b border-slate-100">
                    <th class="px-5 py-3 text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wide w-1/3">Field</th>
                    <th class="px-5 py-3 text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wide w-1/3">Nilai Lama</th>
                    <th class="px-5 py-3 text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wide w-1/3">Nilai Baru</th>
                </tr>
            </thead>
            <tbody>
                @forelse($auditLog->new_values ?? [] as $field => $newVal)
                @php $oldVal = $auditLog->old_values[$field] ?? null; @endphp
                <tr class="border-b border-slate-50 last:border-0">
                    <td class="px-5 py-3 align-top">
                        <span class="text-xs font-mono text-slate-600">{{ $field }}</span>
                    </td>
                    <td class="px-5 py-3 align-top">
                        <span class="text-sm text-slate-500 font-mono break-all">
                            {{ is_null($oldVal) ? '—' : (is_bool($oldVal) ? ($oldVal ? 'true' : 'false') : $oldVal) }}
                        </span>
                    </td>
                    <td class="px-5 py-3 align-top">
                        <span class="text-sm text-slate-800 font-mono break-all font-semibold">
                            {{ is_null($newVal) ? '—' : (is_bool($newVal) ? ($newVal ? 'true' : 'false') : $newVal) }}
                        </span>
                    </td>
                </tr>
                @empty
                <tr><td colspan="3" class="px-5 py-3 text-sm text-slate-400">Tidak ada perubahan tercatat.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endif

{{-- Data lengkap untuk created/deleted --}}
@if(in_array($auditLog->action, ['created', 'deleted', 'restored']))
@php $values = $auditLog->action === 'deleted' ? $auditLog->old_values : $auditLog->new_values; @endphp
<div class="bg-white rounded-xl shadow-sm overflow-hidden mb-4">
    <div class="px-5 py-3.5 border-b border-slate-100">
        <span class="text-sm font-bold text-slate-900">
            {{ $auditLog->action === 'deleted' ? 'Data yang Dihapus' : 'Data yang Dibuat' }}
        </span>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full border-collapse">
            <thead>
                <tr class="bg-slate-50 border-b border-slate-100">
                    <th class="px-5 py-3 text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wide w-1/3">Field</th>
                    <th class="px-5 py-3 text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wide">Nilai</th>
                </tr>
            </thead>
            <tbody>
                @forelse($values ?? [] as $field => $val)
                <tr class="border-b border-slate-50 last:border-0">
                    <td class="px-5 py-3 align-top">
                        <span class="text-xs font-mono text-slate-600">{{ $field }}</span>
                    </td>
                    <td class="px-5 py-3 align-top">
                        <span class="text-sm text-slate-700 font-mono break-all">
                            {{ is_null($val) ? '—' : (is_bool($val) ? ($val ? 'true' : 'false') : $val) }}
                        </span>
                    </td>
                </tr>
                @empty
                <tr><td colspan="2" class="px-5 py-3 text-sm text-slate-400">Tidak ada data tercatat.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endif

</x-layouts.app>
