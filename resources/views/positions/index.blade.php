<x-layouts.app title="Daftar Jabatan" breadcrumb="Master Data / Jabatan">

    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:20px;">
        <div>
            <h2 style="font-size:1.1rem; font-weight:700; color:#0f172a; margin:0 0 3px 0;">Daftar Jabatan</h2>
            <p style="font-size:0.78rem; color:#94a3b8; margin:0;">Kelola jabatan di setiap departemen</p>
        </div>
        <a href="{{ route('positions.create') }}" style="
            display:inline-flex; align-items:center; gap:7px;
            padding:9px 16px; border-radius:9px;
            background: linear-gradient(135deg, #ea580c, #f97316);
            color:#fff; font-size:0.83rem; font-weight:600;
            text-decoration:none; box-shadow: 0 3px 10px rgba(234,88,12,0.3);"
            onmouseover="this.style.boxShadow='0 6px 16px rgba(234,88,12,0.4)'; this.style.transform='translateY(-1px)';"
            onmouseout="this.style.boxShadow='0 3px 10px rgba(234,88,12,0.3)'; this.style.transform='translateY(0)';">
            <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
            </svg>
            Tambah Jabatan
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
                    <th style="padding:12px 16px; text-align:left; font-size:0.72rem; font-weight:600; color:#64748b; letter-spacing:0.05em; text-transform:uppercase;">Jabatan</th>
                    <th style="padding:12px 16px; text-align:left; font-size:0.72rem; font-weight:600; color:#64748b; letter-spacing:0.05em; text-transform:uppercase;">Kode</th>
                    <th style="padding:12px 16px; text-align:left; font-size:0.72rem; font-weight:600; color:#64748b; letter-spacing:0.05em; text-transform:uppercase;">Departemen</th>
                    <th style="padding:12px 16px; text-align:left; font-size:0.72rem; font-weight:600; color:#64748b; letter-spacing:0.05em; text-transform:uppercase;">Organisasi</th>
                    <th style="padding:12px 16px; text-align:center; font-size:0.72rem; font-weight:600; color:#64748b; letter-spacing:0.05em; text-transform:uppercase;">Keuangan</th>
                    <th style="padding:12px 16px; text-align:left; font-size:0.72rem; font-weight:600; color:#64748b; letter-spacing:0.05em; text-transform:uppercase;">Status</th>
                    <th style="padding:12px 16px; text-align:center; font-size:0.72rem; font-weight:600; color:#64748b; letter-spacing:0.05em; text-transform:uppercase;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($positions as $i => $pos)
                <tr style="border-bottom:1px solid #f8fafc;"
                    onmouseover="this.style.background='#fafafa';"
                    onmouseout="this.style.background='transparent';">

                    <td style="padding:14px 20px; font-size:0.8rem; color:#94a3b8;">{{ $i + 1 }}</td>

                    <td style="padding:14px 16px;">
                        <div style="font-size:0.85rem; font-weight:600; color:#1e293b;">{{ $pos->name }}</div>
                        @if($pos->description)
                        <div style="font-size:0.72rem; color:#94a3b8; margin-top:1px;">{{ $pos->description }}</div>
                        @endif
                    </td>

                    <td style="padding:14px 16px;">
                        <span style="font-family:monospace; font-size:0.8rem; font-weight:600; color:#475569; background:#f1f5f9; padding:3px 8px; border-radius:5px;">
                            {{ $pos->code }}
                        </span>
                    </td>

                    <td style="padding:14px 16px;">
                        <span style="display:inline-flex; align-items:center; gap:5px; font-size:0.75rem; font-weight:500; color:#7c3aed; background:#f5f3ff; padding:3px 10px; border-radius:6px;">
                            {{ $pos->department->name }}
                        </span>
                    </td>

                    <td style="padding:14px 16px;">
                        <span style="display:inline-flex; align-items:center; gap:5px; font-size:0.75rem; font-weight:500; color:#1d4ed8; background:#eff6ff; padding:3px 10px; border-radius:6px;">
                            {{ $pos->department->organization->name }}
                        </span>
                    </td>

                    <td style="padding:14px 16px; text-align:center;">
                        @if($pos->is_finance_related)
                        <span style="display:inline-flex; align-items:center; gap:4px; font-size:0.72rem; font-weight:600; color:#0369a1; background:#e0f2fe; padding:3px 10px; border-radius:99px;">
                            <svg width="10" height="10" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                            Ya
                        </span>
                        @else
                        <span style="font-size:0.72rem; color:#94a3b8;">—</span>
                        @endif
                    </td>

                    <td style="padding:14px 16px;">
                        @if($pos->is_active)
                        <span style="display:inline-flex; align-items:center; gap:5px; font-size:0.72rem; font-weight:600; color:#15803d;">
                            <span style="width:6px; height:6px; border-radius:50%; background:#22c55e;"></span>Aktif
                        </span>
                        @else
                        <span style="display:inline-flex; align-items:center; gap:5px; font-size:0.72rem; font-weight:600; color:#dc2626;">
                            <span style="width:6px; height:6px; border-radius:50%; background:#ef4444;"></span>Nonaktif
                        </span>
                        @endif
                    </td>

                    <td style="padding:14px 16px; text-align:center;">
                        <div style="display:inline-flex; align-items:center; gap:6px;">
                            <a href="{{ route('positions.edit', $pos) }}"
                               style="display:inline-flex; align-items:center; gap:5px; padding:6px 12px; border-radius:7px; font-size:0.75rem; font-weight:500; background:#eff6ff; color:#1d4ed8; text-decoration:none;"
                               onmouseover="this.style.background='#dbeafe';" onmouseout="this.style.background='#eff6ff';">
                                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                                Edit
                            </a>

                            <form id="del-pos-{{ $pos->id }}" method="POST" action="{{ route('positions.destroy', $pos) }}">
                                @csrf @method('DELETE')
                            </form>
                            <button type="button"
                                onclick="confirmDelete('del-pos-{{ $pos->id }}', '{{ addslashes($pos->name) }}')"
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
                    <td colspan="8" style="padding:48px; text-align:center; color:#94a3b8;">
                        <svg width="40" height="40" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" style="margin:0 auto 10px; opacity:0.4;">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                        <p style="font-size:0.83rem; margin:0;">Belum ada jabatan</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

</x-layouts.app>
