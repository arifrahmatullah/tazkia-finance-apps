<x-layouts.app title="Periode Anggaran" breadcrumb="Keuangan / Periode Anggaran">

    <div class="flex items-center justify-between mb-5">
        <div>
            <h2 class="text-lg font-bold text-slate-900 m-0">Periode Anggaran</h2>
            <p class="text-xs text-slate-400 m-0">Kelola periode dan rentang waktu anggaran per organisasi</p>
        </div>
        <a href="{{ route('budget-periods.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-gradient-to-br from-orange-400 to-orange-500 text-white text-sm font-semibold shadow-sm hover:-translate-y-px transition-all no-underline">
            <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
            </svg>
            Tambah Periode
        </a>
    </div>

    @if(session('success'))
    <div class="flex items-center gap-2.5 px-4 py-3 bg-green-50 border border-green-200 rounded-xl mb-4 text-sm text-green-700">
        <svg width="16" height="16" fill="#16a34a" viewBox="0 0 20 20" class="shrink-0">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
        </svg>
        <span>{{ session('success') }}</span>
    </div>
    @endif

    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <table class="w-full border-collapse">
            <thead>
                <tr class="bg-slate-50 border-b border-slate-100">
                    <th class="px-4 py-3 text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wide">#</th>
                    <th class="px-4 py-3 text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wide">Periode</th>
                    <th class="px-4 py-3 text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wide">Organisasi</th>
                    <th class="px-4 py-3 text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wide">Periode Anggaran</th>
                    <th class="px-4 py-3 text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wide">Perencanaan</th>
                    <th class="px-4 py-3 text-center text-[11px] font-semibold text-slate-400 uppercase tracking-wide">Status</th>
                    <th class="px-4 py-3 text-center text-[11px] font-semibold text-slate-400 uppercase tracking-wide">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($periods as $i => $period)
                @php
                    $now = now();
                    $isRunning = $period->period_start <= $now && $period->period_end >= $now;
                    $isPast    = $period->period_end < $now;
                @endphp
                <tr class="border-b border-slate-50 hover:bg-slate-50 transition-colors">

                    <td class="px-4 py-3 text-sm text-slate-400 align-middle">{{ $i + 1 }}</td>

                    <td class="px-4 py-3 text-sm text-slate-600 align-middle">
                        <div class="flex items-center gap-2.5">
                            <div class="w-9 h-9 rounded-xl shrink-0 flex items-center justify-center {{ $isRunning ? 'bg-green-50' : ($isPast ? 'bg-slate-50' : 'bg-blue-50') }}">
                                <svg width="16" height="16" fill="none" stroke="{{ $isRunning ? '#16a34a' : ($isPast ? '#94a3b8' : '#3b82f6') }}" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                            </div>
                            <div>
                                <div class="text-sm font-semibold text-slate-800">{{ $period->name }}</div>
                                <span class="font-mono text-[11px] text-slate-500 bg-slate-100 px-1.5 py-0.5 rounded">{{ $period->code }}</span>
                            </div>
                        </div>
                    </td>

                    <td class="px-4 py-3 text-sm text-slate-600 align-middle">
                        <span class="text-xs font-medium text-blue-700 bg-blue-50 px-2.5 py-0.5 rounded-md">
                            {{ $period->organization->name }}
                        </span>
                    </td>

                    <td class="px-4 py-3 text-sm text-slate-600 align-middle">
                        <div class="text-sm font-medium text-slate-800">
                            {{ $period->period_start->format('d M Y') }}
                        </div>
                        <div class="flex items-center gap-1 text-[11px] text-slate-400 mt-0.5">
                            <svg width="10" height="10" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                            {{ $period->period_end->format('d M Y') }}
                        </div>
                    </td>

                    <td class="px-4 py-3 text-sm text-slate-600 align-middle">
                        @if($period->planning_start)
                        <div class="text-sm font-medium text-slate-800">
                            {{ $period->planning_start->format('d M Y') }}
                        </div>
                        <div class="flex items-center gap-1 text-[11px] text-slate-400 mt-0.5">
                            <svg width="10" height="10" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                            {{ $period->planning_end?->format('d M Y') ?? '—' }}
                        </div>
                        @else
                        <span class="text-[11px] text-slate-400">—</span>
                        @endif
                    </td>

                    <td class="px-4 py-3 text-sm text-slate-600 align-middle text-center">
                        @if(!$period->is_active)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-slate-100 text-slate-500">
                                Nonaktif
                            </span>
                        @elseif($isRunning)
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-green-100 text-green-700">
                                <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span>
                                Berjalan
                            </span>
                        @elseif($isPast)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-slate-100 text-slate-500">
                                Selesai
                            </span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-sky-100 text-sky-700">
                                Mendatang
                            </span>
                        @endif
                    </td>

                    <td class="px-4 py-3 text-sm text-slate-600 align-middle text-center">
                        <div class="inline-flex items-center gap-1.5">
                            <a href="{{ route('budget-periods.edit', $period) }}"
                               class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-xs font-medium bg-blue-50 text-blue-600 hover:bg-blue-100 transition-colors no-underline">
                                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                                Edit
                            </a>
                            <form id="del-bp-{{ $period->id }}" method="POST" action="{{ route('budget-periods.destroy', $period) }}">
                                @csrf @method('DELETE')
                            </form>
                            <button type="button"
                                onclick="confirmDelete('del-bp-{{ $period->id }}', '{{ addslashes($period->name) }}')"
                                class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-xs font-medium bg-red-50 text-red-600 hover:bg-red-100 transition-colors border-0 cursor-pointer">
                                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                                Hapus
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-4 py-12 text-center text-slate-400">
                        <svg width="40" height="40" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" class="mx-auto mb-2.5 opacity-40">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        <p class="text-sm m-0">Belum ada periode anggaran</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

</x-layouts.app>
