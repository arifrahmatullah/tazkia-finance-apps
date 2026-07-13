<x-layouts.app title="Detail Laporan Dana" breadcrumb="Laporan penggunaan dana">

@php
    $fr = $fundReport->fundRequest;
    $statusBg = match($fundReport->status) {
        'waiting'  => '#fef3c7',
        'approved' => '#dcfce7',
        'rejected' => '#fee2e2',
        default    => '#f1f5f9',
    };
    $statusColor = match($fundReport->status) {
        'waiting'  => '#92400e',
        'approved' => '#166534',
        'rejected' => '#991b1b',
        default    => '#475569',
    };
    $statusLabel = match($fundReport->status) {
        'waiting'  => 'Menunggu Verifikasi',
        'approved' => 'Disetujui',
        'rejected' => 'Ditolak',
        default    => '-',
    };
@endphp

<div style="max-width:760px; margin:0 auto;">

    {{-- Back --}}
    <a href="{{ route('fund-reports.index') }}"
       style="display:inline-flex; align-items:center; gap:6px; font-size:0.8rem; color:#64748b; text-decoration:none; margin-bottom:20px;">
        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
        Kembali ke Daftar Laporan
    </a>

    {{-- Alert --}}
    @if(session('success'))
    <div style="background:#f0fdf4; border:1px solid #86efac; border-radius:10px; padding:12px 16px; margin-bottom:18px; color:#166534; font-size:0.82rem; display:flex; align-items:center; gap:8px;">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        {{ session('success') }}
    </div>
    @endif

    {{-- Status Header --}}
    <div style="background:#fff; border-radius:14px; border:1px solid #e2e8f0; padding:20px 24px; margin-bottom:16px; display:flex; align-items:center; justify-content:space-between; gap:16px; flex-wrap:wrap;">
        <div>
            <div style="font-size:0.72rem; color:#94a3b8; margin-bottom:4px;">Laporan Penggunaan Dana</div>
            <div style="font-size:1.05rem; font-weight:700; color:#0f172a;">{{ $fr->title ?? '-' }}</div>
            <div style="font-size:0.78rem; color:#64748b; margin-top:2px; font-family:monospace;">{{ $fr->reference ?? '-' }}</div>
        </div>
        <span style="padding:6px 16px; border-radius:999px; font-size:0.78rem; font-weight:700; background:{{ $statusBg }}; color:{{ $statusColor }};">
            {{ $statusLabel }}
        </span>
    </div>

    {{-- Info Grid --}}
    <div style="display:grid; grid-template-columns:1fr 1fr; gap:14px; margin-bottom:16px;">
        <div style="background:#fff; border-radius:12px; border:1px solid #e2e8f0; padding:16px 20px;">
            <div style="font-size:0.68rem; font-weight:600; color:#94a3b8; text-transform:uppercase; letter-spacing:0.05em; margin-bottom:8px;">Detail Laporan</div>
            <div style="display:flex; flex-direction:column; gap:8px;">
                <div style="display:flex; justify-content:space-between; font-size:0.8rem;">
                    <span style="color:#64748b;">Tanggal Laporan</span>
                    <span style="font-weight:600; color:#0f172a;">{{ $fundReport->report_date?->format('d/m/Y') }}</span>
                </div>
                <div style="display:flex; justify-content:space-between; font-size:0.8rem;">
                    <span style="color:#64748b;">Dana Diajukan</span>
                    <span style="font-weight:600; color:#0f172a;">Rp {{ number_format($fr->amount, 0, ',', '.') }}</span>
                </div>
                <div style="display:flex; justify-content:space-between; font-size:0.8rem;">
                    <span style="color:#64748b;">Dana Digunakan</span>
                    <span style="font-weight:700; color:#0d2d6b; font-size:0.85rem;">Rp {{ number_format($fundReport->amount_used, 0, ',', '.') }}</span>
                </div>
                @php $sisa = $fr->amount - $fundReport->amount_used; @endphp
                <div style="display:flex; justify-content:space-between; font-size:0.8rem; padding-top:8px; border-top:1px solid #f1f5f9;">
                    <span style="color:#64748b;">Sisa Dana</span>
                    <span style="font-weight:700; color:{{ $sisa >= 0 ? '#166534' : '#991b1b' }};">
                        {{ $sisa < 0 ? '-' : '' }}Rp {{ number_format(abs($sisa), 0, ',', '.') }}
                    </span>
                </div>
            </div>
        </div>
        <div style="background:#fff; border-radius:12px; border:1px solid #e2e8f0; padding:16px 20px;">
            <div style="font-size:0.68rem; font-weight:600; color:#94a3b8; text-transform:uppercase; letter-spacing:0.05em; margin-bottom:8px;">Informasi Pengajuan</div>
            <div style="display:flex; flex-direction:column; gap:8px;">
                <div style="display:flex; justify-content:space-between; font-size:0.8rem;">
                    <span style="color:#64748b;">Departemen</span>
                    <span style="font-weight:600; color:#0f172a;">{{ $fr->department?->name ?? '-' }}</span>
                </div>
                <div style="display:flex; justify-content:space-between; font-size:0.8rem;">
                    <span style="color:#64748b;">Program Kerja</span>
                    <span style="font-weight:600; color:#0f172a;">{{ $fr->budgetProgram?->name ?? '-' }}</span>
                </div>
                <div style="display:flex; justify-content:space-between; font-size:0.8rem;">
                    <span style="color:#64748b;">Dicairkan</span>
                    <span style="font-weight:600; color:#0f172a;">{{ $fr->disbursed_at?->format('d/m/Y') ?? '-' }}</span>
                </div>
                <div style="display:flex; justify-content:space-between; font-size:0.8rem;">
                    <span style="color:#64748b;">Dilaporkan oleh</span>
                    <span style="font-weight:600; color:#0f172a;">{{ $fundReport->reporter?->name ?? '-' }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Keterangan --}}
    <div style="background:#fff; border-radius:12px; border:1px solid #e2e8f0; padding:16px 20px; margin-bottom:16px;">
        <div style="font-size:0.68rem; font-weight:600; color:#94a3b8; text-transform:uppercase; letter-spacing:0.05em; margin-bottom:10px;">Keterangan Penggunaan Dana</div>
        <div style="font-size:0.85rem; color:#0f172a; line-height:1.6; white-space:pre-wrap;">{{ $fundReport->description }}</div>
    </div>

    {{-- File Bukti --}}
    <div style="background:#fff; border-radius:12px; border:1px solid #e2e8f0; padding:16px 20px; margin-bottom:16px;">
        <div style="font-size:0.68rem; font-weight:600; color:#94a3b8; text-transform:uppercase; letter-spacing:0.05em; margin-bottom:12px;">
            Bukti Pengeluaran ({{ $fundReport->files->count() }} file)
        </div>
        @forelse($fundReport->files as $file)
        <div style="display:flex; align-items:center; gap:10px; padding:10px 12px; background:#f8fafc; border:1px solid #e2e8f0; border-radius:8px; margin-bottom:8px;">
            <svg width="16" height="16" fill="none" stroke="#64748b" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
            <div style="flex:1; min-width:0;">
                <div style="font-size:0.8rem; font-weight:600; color:#0f172a; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">{{ $file->file_name }}</div>
                <div style="font-size:0.7rem; color:#94a3b8;">{{ $file->file_size_label }}</div>
            </div>
            <a href="{{ $file->url }}" target="_blank"
               style="padding:5px 12px; background:#eff6ff; color:#1d4ed8; border-radius:6px; font-size:0.73rem; font-weight:600; text-decoration:none; white-space:nowrap;">
                Lihat
            </a>
            @if($fundReport->reporter?->id === auth()->id() && !$fundReport->isApproved())
            <form action="{{ route('fund-reports.files.delete', $file) }}" method="POST"
                  onsubmit="return confirm('Hapus file ini?')">
                @csrf @method('DELETE')
                <button type="submit" style="padding:5px 10px; background:#fee2e2; color:#991b1b; border:none; border-radius:6px; font-size:0.73rem; font-weight:600; cursor:pointer;">
                    Hapus
                </button>
            </form>
            @endif
        </div>
        @empty
        <div style="font-size:0.8rem; color:#94a3b8; text-align:center; padding:16px;">Tidak ada file bukti</div>
        @endforelse
    </div>

    {{-- Status Review --}}
    @if($fundReport->reviewer)
    <div style="background:{{ $statusBg }}; border:1px solid {{ $fundReport->isApproved() ? '#86efac' : '#fca5a5' }}; border-radius:12px; padding:16px 20px; margin-bottom:16px;">
        <div style="font-size:0.68rem; font-weight:600; color:{{ $statusColor }}; text-transform:uppercase; letter-spacing:0.05em; margin-bottom:8px;">Hasil Verifikasi</div>
        <div style="display:flex; gap:20px; flex-wrap:wrap; font-size:0.8rem;">
            <span><span style="color:#64748b;">Diverifikasi oleh:</span> <span style="font-weight:600;">{{ $fundReport->reviewer?->name }}</span></span>
            <span><span style="color:#64748b;">Tanggal:</span> <span style="font-weight:600;">{{ $fundReport->reviewed_at?->format('d/m/Y H:i') }}</span></span>
        </div>
        @if($fundReport->review_notes)
        <div style="margin-top:10px; font-size:0.82rem; color:{{ $statusColor }};">
            <span style="font-weight:600;">Catatan:</span> {{ $fundReport->review_notes }}
        </div>
        @endif
    </div>
    @endif

</div>

</x-layouts.app>
