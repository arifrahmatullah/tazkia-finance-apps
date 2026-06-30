<x-layouts.app title="Periode Anggaran" breadcrumb="Keuangan / Periode Anggaran">

    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:20px;">
        <div>
            <h2 style="font-size:1.1rem; font-weight:700; color:#0f172a; margin:0 0 3px 0;">Periode Anggaran</h2>
            <p style="font-size:0.78rem; color:#94a3b8; margin:0;">Kelola periode dan rentang waktu anggaran per organisasi</p>
        </div>
        <a href="{{ route('budget-periods.create') }}" style="
            display:inline-flex; align-items:center; gap:7px;
            padding:9px 16px; border-radius:9px;
            background:linear-gradient(135deg,#ea580c,#f97316);
            color:#fff; font-size:0.83rem; font-weight:600;
            text-decoration:none; box-shadow:0 3px 10px rgba(234,88,12,0.3);"
            onmouseover="this.style.boxShadow='0 6px 16px rgba(234,88,12,0.4)'; this.style.transform='translateY(-1px)';"
            onmouseout="this.style.boxShadow='0 3px 10px rgba(234,88,12,0.3)'; this.style.transform='translateY(0)';">
            <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
            </svg>
            Tambah Periode
        </a>
    </div>

    @if(session('success'))
    <div style="display:flex; align-items:center; gap:10px; padding:12px 16px; background:#f0fdf4; border:1px solid #bbf7d0; border-radius:10px; margin-bottom:18px;">
        <svg width="16" height="16" fill="#16a34a" viewBox="0 0 20 20" style="flex-shrink:0;">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
        </svg>
        <span style="font-size:0.83rem; color:#15803d; font-weight:500;">{{ session('success') }}</span>
    </div>
    @endif

    <div style="background:#fff; border-radius:14px; border:1px solid #f1f5f9; box-shadow:0 1px 4px rgba(0,0,0,0.04); overflow:hidden;">
        <table style="width:100%; border-collapse:collapse;">
            <thead>
                <tr style="background:#f8fafc; border-bottom:1px solid #f1f5f9;">
                    <th style="padding:12px 20px; text-align:left; font-size:0.72rem; font-weight:600; color:#64748b; letter-spacing:0.05em; text-transform:uppercase;">#</th>
                    <th style="padding:12px 16px; text-align:left; font-size:0.72rem; font-weight:600; color:#64748b; letter-spacing:0.05em; text-transform:uppercase;">Periode</th>
                    <th style="padding:12px 16px; text-align:left; font-size:0.72rem; font-weight:600; color:#64748b; letter-spacing:0.05em; text-transform:uppercase;">Organisasi</th>
                    <th style="padding:12px 16px; text-align:left; font-size:0.72rem; font-weight:600; color:#64748b; letter-spacing:0.05em; text-transform:uppercase;">Periode Anggaran</th>
                    <th style="padding:12px 16px; text-align:left; font-size:0.72rem; font-weight:600; color:#64748b; letter-spacing:0.05em; text-transform:uppercase;">Perencanaan</th>
                    <th style="padding:12px 16px; text-align:center; font-size:0.72rem; font-weight:600; color:#64748b; letter-spacing:0.05em; text-transform:uppercase;">Status</th>
                    <th style="padding:12px 16px; text-align:center; font-size:0.72rem; font-weight:600; color:#64748b; letter-spacing:0.05em; text-transform:uppercase;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($periods as $i => $period)
                @php
                    $now = now();
                    $isRunning = $period->period_start <= $now && $period->period_end >= $now;
                    $isPast    = $period->period_end < $now;
                @endphp
                <tr style="border-bottom:1px solid #f8fafc;"
                    onmouseover="this.style.background='#fafafa';"
                    onmouseout="this.style.background='transparent';">

                    <td style="padding:14px 20px; font-size:0.8rem; color:#94a3b8;">{{ $i + 1 }}</td>

                    <td style="padding:14px 16px;">
                        <div style="display:flex; align-items:center; gap:10px;">
                            <div style="
                                width:36px; height:36px; border-radius:9px; flex-shrink:0;
                                background:{{ $isRunning ? '#ecfdf5' : ($isPast ? '#f8fafc' : '#eff6ff') }};
                                display:flex; align-items:center; justify-content:center;
                            ">
                                <svg width="16" height="16" fill="none" stroke="{{ $isRunning ? '#16a34a' : ($isPast ? '#94a3b8' : '#3b82f6') }}" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                            </div>
                            <div>
                                <div style="font-size:0.85rem; font-weight:600; color:#1e293b;">{{ $period->name }}</div>
                                <span style="font-family:monospace; font-size:0.72rem; color:#64748b; background:#f1f5f9; padding:1px 6px; border-radius:4px;">{{ $period->code }}</span>
                            </div>
                        </div>
                    </td>

                    <td style="padding:14px 16px;">
                        <span style="font-size:0.75rem; font-weight:500; color:#1d4ed8; background:#eff6ff; padding:3px 10px; border-radius:6px;">
                            {{ $period->organization->name }}
                        </span>
                    </td>

                    <td style="padding:14px 16px;">
                        <div style="font-size:0.8rem; color:#1e293b; font-weight:500;">
                            {{ $period->period_start->format('d M Y') }}
                        </div>
                        <div style="display:flex; align-items:center; gap:4px; font-size:0.72rem; color:#94a3b8; margin-top:2px;">
                            <svg width="10" height="10" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                            {{ $period->period_end->format('d M Y') }}
                        </div>
                    </td>

                    <td style="padding:14px 16px;">
                        @if($period->planning_start)
                        <div style="font-size:0.8rem; color:#1e293b; font-weight:500;">
                            {{ $period->planning_start->format('d M Y') }}
                        </div>
                        <div style="display:flex; align-items:center; gap:4px; font-size:0.72rem; color:#94a3b8; margin-top:2px;">
                            <svg width="10" height="10" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                            {{ $period->planning_end?->format('d M Y') ?? '—' }}
                        </div>
                        @else
                        <span style="font-size:0.72rem; color:#94a3b8;">—</span>
                        @endif
                    </td>

                    <td style="padding:14px 16px; text-align:center;">
                        @if(!$period->is_active)
                            <span style="display:inline-flex; align-items:center; gap:4px; font-size:0.72rem; font-weight:600; color:#64748b; background:#f1f5f9; padding:3px 10px; border-radius:99px;">
                                Nonaktif
                            </span>
                        @elseif($isRunning)
                            <span style="display:inline-flex; align-items:center; gap:4px; font-size:0.72rem; font-weight:600; color:#15803d; background:#f0fdf4; padding:3px 10px; border-radius:99px;">
                                <span style="width:6px; height:6px; border-radius:50%; background:#22c55e;"></span>
                                Berjalan
                            </span>
                        @elseif($isPast)
                            <span style="display:inline-flex; align-items:center; gap:4px; font-size:0.72rem; font-weight:600; color:#64748b; background:#f1f5f9; padding:3px 10px; border-radius:99px;">
                                Selesai
                            </span>
                        @else
                            <span style="display:inline-flex; align-items:center; gap:4px; font-size:0.72rem; font-weight:600; color:#0369a1; background:#e0f2fe; padding:3px 10px; border-radius:99px;">
                                Mendatang
                            </span>
                        @endif
                    </td>

                    <td style="padding:14px 16px; text-align:center;">
                        <div style="display:inline-flex; align-items:center; gap:6px;">
                            <a href="{{ route('budget-periods.edit', $period) }}"
                               style="display:inline-flex; align-items:center; gap:5px; padding:6px 12px; border-radius:7px; font-size:0.75rem; font-weight:500; background:#eff6ff; color:#1d4ed8; text-decoration:none;"
                               onmouseover="this.style.background='#dbeafe';" onmouseout="this.style.background='#eff6ff';">
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
                                style="display:inline-flex; align-items:center; gap:5px; padding:6px 12px; border-radius:7px; font-size:0.75rem; font-weight:500; background:#fef2f2; color:#dc2626; border:none; cursor:pointer;"
                                onmouseover="this.style.background='#fee2e2';" onmouseout="this.style.background='#fef2f2';">
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
                    <td colspan="7" style="padding:48px; text-align:center; color:#94a3b8;">
                        <svg width="40" height="40" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" style="margin:0 auto 10px; opacity:0.4;">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        <p style="font-size:0.83rem; margin:0;">Belum ada periode anggaran</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

</x-layouts.app>
