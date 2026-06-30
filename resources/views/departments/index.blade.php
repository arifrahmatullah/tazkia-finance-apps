<x-layouts.app title="Daftar Departemen" breadcrumb="Master Data / Departemen">

    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:20px;">
        <div>
            <h2 style="font-size:1.1rem; font-weight:700; color:#0f172a; margin:0 0 3px 0;">Daftar Departemen</h2>
            <p style="font-size:0.78rem; color:#94a3b8; margin:0;">Kelola departemen di setiap organisasi</p>
        </div>
        <a href="{{ route('departments.create') }}" style="
            display:inline-flex; align-items:center; gap:7px;
            padding:9px 16px; border-radius:9px;
            background: linear-gradient(135deg, #ea580c, #f97316);
            color:#fff; font-size:0.83rem; font-weight:600;
            text-decoration:none;
            box-shadow: 0 3px 10px rgba(234,88,12,0.3);"
            onmouseover="this.style.boxShadow='0 6px 16px rgba(234,88,12,0.4)'; this.style.transform='translateY(-1px)';"
            onmouseout="this.style.boxShadow='0 3px 10px rgba(234,88,12,0.3)'; this.style.transform='translateY(0)';">
            <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
            </svg>
            Tambah Departemen
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

    {{-- Search & Filter --}}
    <form method="GET" action="{{ route('departments.index') }}" style="margin-bottom:16px;">
        <div style="display:flex; gap:10px; flex-wrap:wrap; align-items:center;">
            <div style="position:relative; flex:1; min-width:200px;">
                <svg width="15" height="15" fill="none" stroke="#94a3b8" stroke-width="2" viewBox="0 0 24 24"
                     style="position:absolute; left:11px; top:50%; transform:translateY(-50%); pointer-events:none;">
                    <circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/>
                </svg>
                <input type="text" name="search" value="{{ request('search') }}"
                    placeholder="Cari nama atau kode..."
                    style="width:100%; padding:8px 12px 8px 34px; border:1.5px solid #e2e8f0; border-radius:9px; font-size:0.845rem; color:#1e293b; outline:none;"
                    onfocus="this.style.borderColor='#f97316'" onblur="this.style.borderColor='#e2e8f0'">
            </div>

            @if($organizations->count() > 1)
            <select name="organization_id"
                style="padding:8px 12px; border:1.5px solid #e2e8f0; border-radius:9px; font-size:0.845rem; color:#374151; background:#fff; outline:none; min-width:170px;"
                onfocus="this.style.borderColor='#f97316'" onblur="this.style.borderColor='#e2e8f0'">
                <option value="">Semua Organisasi</option>
                @foreach($organizations as $org)
                    <option value="{{ $org->id }}" {{ request('organization_id') == $org->id ? 'selected' : '' }}>
                        {{ $org->name }}
                    </option>
                @endforeach
            </select>
            @endif

            <select name="status"
                style="padding:8px 12px; border:1.5px solid #e2e8f0; border-radius:9px; font-size:0.845rem; color:#374151; background:#fff; outline:none; min-width:140px;"
                onfocus="this.style.borderColor='#f97316'" onblur="this.style.borderColor='#e2e8f0'">
                <option value="">Semua Status</option>
                <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Aktif</option>
                <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Nonaktif</option>
            </select>

            <button type="submit"
                style="padding:8px 18px; border-radius:9px; border:none; cursor:pointer; font-size:0.845rem; font-weight:600; background:linear-gradient(135deg,#f97316,#ea580c); color:#fff;">
                Cari
            </button>

            @if(request()->hasAny(['search','organization_id','status']))
            <a href="{{ route('departments.index') }}"
                style="padding:8px 14px; border-radius:9px; border:1.5px solid #e2e8f0; font-size:0.845rem; color:#64748b; text-decoration:none; background:#fff;">
                Reset
            </a>
            @endif
        </div>
    </form>

    <div style="background:#fff; border-radius:14px; border:1px solid #f1f5f9; box-shadow:0 1px 4px rgba(0,0,0,0.04); overflow:hidden;">
        <table style="width:100%; border-collapse:collapse;">
            <thead>
                <tr style="background:#f8fafc; border-bottom:1px solid #f1f5f9;">
                    <th style="padding:12px 20px; text-align:left; font-size:0.72rem; font-weight:600; color:#64748b; letter-spacing:0.05em; text-transform:uppercase;">#</th>
                    <th style="padding:12px 16px; text-align:left; font-size:0.72rem; font-weight:600; color:#64748b; letter-spacing:0.05em; text-transform:uppercase;">Departemen</th>
                    <th style="padding:12px 16px; text-align:left; font-size:0.72rem; font-weight:600; color:#64748b; letter-spacing:0.05em; text-transform:uppercase;">Kode</th>
                    <th style="padding:12px 16px; text-align:left; font-size:0.72rem; font-weight:600; color:#64748b; letter-spacing:0.05em; text-transform:uppercase;">Organisasi</th>
                    <th style="padding:12px 16px; text-align:center; font-size:0.72rem; font-weight:600; color:#64748b; letter-spacing:0.05em; text-transform:uppercase;">Budget</th>
                    <th style="padding:12px 16px; text-align:left; font-size:0.72rem; font-weight:600; color:#64748b; letter-spacing:0.05em; text-transform:uppercase;">Status</th>
                    <th style="padding:12px 16px; text-align:center; font-size:0.72rem; font-weight:600; color:#64748b; letter-spacing:0.05em; text-transform:uppercase;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($departments as $i => $dept)
                <tr style="border-bottom:1px solid #f8fafc;"
                    onmouseover="this.style.background='#fafafa';"
                    onmouseout="this.style.background='transparent';">

                    <td style="padding:14px 20px; font-size:0.8rem; color:#94a3b8;">{{ $i + 1 }}</td>

                    <td style="padding:14px 16px;">
                        <div style="font-size:0.85rem; font-weight:600; color:#1e293b;">{{ $dept->name }}</div>
                        @if($dept->description)
                        <div style="font-size:0.72rem; color:#94a3b8; margin-top:1px;">{{ $dept->description }}</div>
                        @endif
                    </td>

                    <td style="padding:14px 16px;">
                        <span style="font-family:monospace; font-size:0.8rem; font-weight:600; color:#475569; background:#f1f5f9; padding:3px 8px; border-radius:5px;">
                            {{ $dept->code }}
                        </span>
                    </td>

                    <td style="padding:14px 16px;">
                        <div style="display:inline-flex; align-items:center; gap:6px; padding:4px 10px; background:#eff6ff; border-radius:6px;">
                            <svg width="11" height="11" fill="none" stroke="#1d4ed8" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16"/>
                            </svg>
                            <span style="font-size:0.75rem; font-weight:500; color:#1d4ed8;">{{ $dept->organization->name }}</span>
                        </div>
                    </td>

                    <td style="padding:14px 16px; text-align:center;">
                        @if($dept->has_budget)
                            <div style="display:inline-flex; flex-direction:column; align-items:center; gap:2px;">
                                <span style="display:inline-flex; align-items:center; gap:4px; font-size:0.7rem; font-weight:600; color:#15803d; background:#f0fdf4; padding:2px 8px; border-radius:99px;">
                                    <svg width="10" height="10" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                    Punya Budget
                                </span>
                                @if($dept->budget_blocking)
                                <span style="font-size:0.65rem; color:#dc2626; background:#fef2f2; padding:1px 6px; border-radius:99px; font-weight:500;">Blokir jika habis</span>
                                @endif
                            </div>
                        @else
                            <span style="font-size:0.72rem; color:#94a3b8;">—</span>
                        @endif
                    </td>

                    <td style="padding:14px 16px;">
                        @if($dept->is_active)
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
                            <a href="{{ route('departments.edit', $dept) }}"
                               style="display:inline-flex; align-items:center; gap:5px; padding:6px 12px; border-radius:7px; font-size:0.75rem; font-weight:500; background:#eff6ff; color:#1d4ed8; text-decoration:none;"
                               onmouseover="this.style.background='#dbeafe';" onmouseout="this.style.background='#eff6ff';">
                                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                                Edit
                            </a>
                            <form id="del-dept-{{ $dept->id }}" method="POST" action="{{ route('departments.destroy', $dept) }}">
                                @csrf @method('DELETE')
                            </form>
                            <button type="button"
                                onclick="confirmDelete('del-dept-{{ $dept->id }}', '{{ addslashes($dept->name) }}')"
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
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        <p style="font-size:0.83rem; margin:0;">Belum ada departemen</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

</x-layouts.app>
