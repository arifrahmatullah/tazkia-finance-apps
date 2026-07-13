<x-layouts.app title="Laporan Dana" breadcrumb="Laporan penggunaan dana setelah pencairan">

    {{-- Alert --}}
    @if(session('success'))
    <div style="background:#f0fdf4; border:1px solid #86efac; border-radius:10px; padding:12px 16px; margin-bottom:18px; color:#166534; font-size:0.82rem; display:flex; align-items:center; gap:8px;">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        {{ session('success') }}
    </div>
    @endif

    {{-- BAGIAN 1: Pengajuan yang perlu dilaporkan --}}
    @if($pendingFundRequests->count() > 0)
    <div style="margin-bottom:28px;">
        <div style="display:flex; align-items:center; gap:10px; margin-bottom:14px;">
            <div style="width:8px; height:8px; border-radius:50%; background:#f59e0b; animation: pulse 1.5s infinite;"></div>
            <h3 style="font-size:0.9rem; font-weight:700; color:#0f172a; margin:0;">Perlu Dilaporkan</h3>
            <span style="padding:2px 9px; background:#fef3c7; color:#92400e; border-radius:999px; font-size:0.68rem; font-weight:700;">
                {{ $pendingFundRequests->count() }} pengajuan
            </span>
        </div>
        <div style="font-size:0.78rem; color:#64748b; margin-bottom:14px;">
            Dana pengajuan berikut sudah dicairkan. Buat laporan penggunaan dananya segera.
        </div>

        @foreach($pendingFundRequests as $fr)
        <div style="background:#fffbeb; border:1.5px solid #fcd34d; border-radius:12px; padding:16px 18px; margin-bottom:10px; display:flex; align-items:center; gap:16px; flex-wrap:wrap;">
            <div style="flex:1; min-width:0;">
                <div style="font-size:0.72rem; font-family:monospace; color:#92400e; font-weight:700; margin-bottom:3px;">{{ $fr->reference }}</div>
                <div style="font-size:0.9rem; font-weight:700; color:#0f172a; margin-bottom:5px; line-height:1.3;">{{ $fr->title }}</div>
                <div style="display:flex; gap:14px; flex-wrap:wrap; font-size:0.75rem; color:#78716c;">
                    <span>{{ $fr->department?->name ?? '-' }}</span>
                    <span>·</span>
                    <span>Cair: {{ $fr->disbursed_at?->format('d/m/Y') }}</span>
                    <span>·</span>
                    <span style="font-weight:700; color:#0f172a;">Rp {{ number_format($fr->amount, 0, ',', '.') }}</span>
                </div>
            </div>
            <a href="{{ route('fund-reports.create', ['fund_request' => $fr->id]) }}"
               style="display:inline-flex; align-items:center; gap:7px; padding:9px 20px; border-radius:9px; font-size:0.82rem; font-weight:700; color:#fff; text-decoration:none; white-space:nowrap; background:linear-gradient(135deg,#d97706,#f59e0b); box-shadow:0 2px 8px rgba(217,119,6,0.3);">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Buat Laporan
            </a>
        </div>
        @endforeach
    </div>
    @endif

    {{-- BAGIAN 2: Riwayat Laporan --}}
    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:14px;">
        <h3 style="font-size:0.9rem; font-weight:700; color:#0f172a; margin:0;">Riwayat Laporan</h3>
    </div>

    @forelse($reports as $report)
    @php
        $fr = $report->fundRequest;
        $stripe = match($report->status) {
            'waiting'  => '#f59e0b',
            'approved' => '#22c55e',
            'rejected' => '#ef4444',
            default    => '#94a3b8',
        };
        $badgeBg = match($report->status) {
            'waiting'  => '#fef3c7',
            'approved' => '#dcfce7',
            'rejected' => '#fee2e2',
            default    => '#f1f5f9',
        };
        $badgeColor = match($report->status) {
            'waiting'  => '#92400e',
            'approved' => '#166534',
            'rejected' => '#991b1b',
            default    => '#475569',
        };
        $badgeLabel = match($report->status) {
            'waiting'  => 'Menunggu Verifikasi',
            'approved' => 'Disetujui',
            'rejected' => 'Ditolak — Perlu Revisi',
            default    => '-',
        };
    @endphp
    <div style="background:#fff; border-radius:14px; border:1px solid #e2e8f0; overflow:hidden; margin-bottom:12px; display:flex;">
        <div style="width:4px; background:{{ $stripe }}; flex-shrink:0;"></div>
        <div style="flex:1; padding:16px 18px;">
            <div style="display:flex; align-items:flex-start; justify-content:space-between; gap:12px; flex-wrap:wrap;">
                <div style="flex:1; min-width:0;">
                    <div style="display:flex; align-items:center; gap:8px; flex-wrap:wrap; margin-bottom:5px;">
                        <span style="font-size:0.72rem; font-weight:600; color:#64748b; font-family:monospace;">{{ $fr->reference ?? '-' }}</span>
                        <span style="padding:2px 10px; border-radius:999px; font-size:0.68rem; font-weight:700; background:{{ $badgeBg }}; color:{{ $badgeColor }};">
                            {{ $badgeLabel }}
                        </span>
                    </div>
                    <div style="font-size:0.9rem; font-weight:700; color:#0f172a; margin-bottom:5px; line-height:1.3;">
                        {{ $fr->title ?? '-' }}
                    </div>
                    <div style="display:flex; gap:16px; flex-wrap:wrap; font-size:0.75rem; color:#64748b;">
                        <span>Tgl Laporan: <strong>{{ $report->report_date?->format('d/m/Y') }}</strong></span>
                        <span>Digunakan: <strong style="color:#0f172a;">Rp {{ number_format($report->amount_used, 0, ',', '.') }}</strong></span>
                        <span>File: <strong>{{ $report->files->count() }}</strong></span>
                    </div>
                </div>
                <div style="display:flex; gap:8px; align-items:center; flex-shrink:0;">
                    @if($report->isRejected())
                    <a href="{{ route('fund-reports.create', ['fund_request' => $fr->id]) }}"
                       style="padding:7px 14px; background:#fef2f2; color:#991b1b; border:1px solid #fecaca; border-radius:8px; font-size:0.75rem; font-weight:700; text-decoration:none;">
                        Kirim Ulang
                    </a>
                    @endif
                    <a href="{{ route('fund-reports.show', $report) }}"
                       style="padding:7px 14px; background:#f1f5f9; color:#475569; border-radius:8px; font-size:0.75rem; font-weight:600; text-decoration:none;">
                        Lihat Detail
                    </a>
                </div>
            </div>

            @if($report->isRejected() && $report->review_notes)
            <div style="margin-top:10px; padding:9px 12px; background:#fef2f2; border-radius:7px; font-size:0.78rem; color:#991b1b; border:1px solid #fecaca;">
                <span style="font-weight:700;">Catatan:</span> {{ $report->review_notes }}
            </div>
            @endif
        </div>
    </div>
    @empty
    @if($pendingFundRequests->count() === 0)
    <div style="background:#fff; border:1px solid #e2e8f0; border-radius:14px; padding:52px 24px; text-align:center;">
        <div style="width:52px; height:52px; border-radius:14px; background:#f1f5f9; display:flex; align-items:center; justify-content:center; margin:0 auto 14px;">
            <svg width="22" height="22" fill="none" stroke="#94a3b8" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
        </div>
        <div style="font-size:0.88rem; font-weight:600; color:#475569; margin-bottom:5px;">Belum ada laporan</div>
        <div style="font-size:0.78rem; color:#94a3b8;">Laporan bisa dibuat setelah pengajuan dana Anda dicairkan</div>
        <a href="{{ route('fund-requests.index') }}" style="display:inline-block; margin-top:14px; padding:8px 18px; background:#0f172a; color:#fff; border-radius:8px; font-size:0.8rem; font-weight:600; text-decoration:none;">
            Lihat Pengajuan Saya
        </a>
    </div>
    @else
    <div style="text-align:center; padding:24px; font-size:0.8rem; color:#94a3b8;">
        Belum ada riwayat laporan
    </div>
    @endif
    @endforelse

    @if($reports->hasPages())
    <div style="margin-top:20px;">{{ $reports->links() }}</div>
    @endif

<style>
@keyframes pulse { 0%,100%{opacity:1} 50%{opacity:0.4} }
</style>

</x-layouts.app>
