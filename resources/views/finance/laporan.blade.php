<x-layouts.app title="Verifikasi Laporan Dana" breadcrumb="Periksa dan setujui laporan penggunaan dana dari pengaju">

    {{-- Header --}}
    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:24px; flex-wrap:wrap; gap:12px;">
        <div>
            <h2 style="font-size:1.2rem; font-weight:700; color:#0f172a; margin:0;">Verifikasi Laporan Dana</h2>
            <p style="font-size:0.78rem; color:#64748b; margin:4px 0 0;">Tinjau dan setujui laporan penggunaan dana dari pengaju</p>
        </div>
        <a href="{{ route('finance.index') }}"
           style="display:inline-flex; align-items:center; gap:6px; padding:8px 16px; border:1.5px solid #e2e8f0; border-radius:8px; font-size:0.8rem; font-weight:600; color:#475569; text-decoration:none;">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
            Pencairan Dana
        </a>
    </div>

    {{-- Alert --}}
    @if(session('success'))
    <div style="background:#f0fdf4; border:1px solid #86efac; border-radius:10px; padding:12px 16px; margin-bottom:18px; color:#166534; font-size:0.82rem; display:flex; align-items:center; gap:8px;">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        {{ session('success') }}
    </div>
    @endif
    @if(session('error'))
    <div style="background:#fef2f2; border:1px solid #fca5a5; border-radius:10px; padding:12px 16px; margin-bottom:18px; color:#991b1b; font-size:0.82rem; display:flex; align-items:center; gap:8px;">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        {{ session('error') }}
    </div>
    @endif

    {{-- Filter --}}
    <div style="background:#fff; border-radius:12px; border:1px solid #e2e8f0; padding:14px 18px; margin-bottom:20px;">
        <form method="GET" style="display:flex; gap:10px; flex-wrap:wrap; align-items:flex-end;">
            <div>
                <label style="display:block; font-size:0.72rem; font-weight:600; color:#64748b; margin-bottom:4px;">Status</label>
                <select name="status"
                        style="padding:7px 10px; border:1.5px solid #e2e8f0; border-radius:7px; font-size:0.8rem; color:#374151; outline:none;"
                        onchange="this.form.submit()">
                    <option value="">Semua Status</option>
                    <option value="waiting" {{ request('status') === 'waiting' ? 'selected' : '' }}>Menunggu</option>
                    <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Disetujui</option>
                    <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Ditolak</option>
                </select>
            </div>
            <div style="flex:1; min-width:200px;">
                <label style="display:block; font-size:0.72rem; font-weight:600; color:#64748b; margin-bottom:4px;">Cari</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Ref / judul pengajuan..."
                       style="width:100%; padding:7px 12px; border:1.5px solid #e2e8f0; border-radius:7px; font-size:0.8rem; color:#374151; outline:none;">
            </div>
            <button type="submit" style="padding:8px 18px; background:#0f172a; color:#fff; border:none; border-radius:7px; font-size:0.8rem; font-weight:600; cursor:pointer;">Cari</button>
            @if(request()->hasAny(['status', 'search']))
            <a href="{{ route('finance.laporan') }}" style="padding:8px 14px; border:1.5px solid #e2e8f0; border-radius:7px; font-size:0.8rem; color:#64748b; text-decoration:none;">Reset</a>
            @endif
        </form>
    </div>

    {{-- Cards --}}
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
            'rejected' => 'Ditolak',
            default    => '-',
        };
    @endphp
    <div style="background:#fff; border-radius:14px; border:1px solid #e2e8f0; overflow:hidden; margin-bottom:14px; display:flex;">
        <div style="width:4px; background:{{ $stripe }}; flex-shrink:0;"></div>
        <div style="flex:1; padding:18px 20px;">

            {{-- Top Row --}}
            <div style="display:flex; align-items:flex-start; justify-content:space-between; gap:12px; flex-wrap:wrap; margin-bottom:12px;">
                <div style="flex:1; min-width:0;">
                    <div style="display:flex; align-items:center; gap:8px; flex-wrap:wrap; margin-bottom:5px;">
                        <span style="font-size:0.72rem; font-weight:600; color:#64748b; font-family:monospace;">{{ $fr->reference ?? '-' }}</span>
                        <span style="padding:2px 10px; border-radius:999px; font-size:0.68rem; font-weight:600; background:{{ $badgeBg }}; color:{{ $badgeColor }};">
                            {{ $badgeLabel }}
                        </span>
                    </div>
                    <div style="font-size:0.95rem; font-weight:700; color:#0f172a; line-height:1.3; margin-bottom:5px;">
                        {{ $fr->title ?? '-' }}
                    </div>
                    <div style="display:flex; gap:14px; flex-wrap:wrap; font-size:0.75rem; color:#64748b;">
                        <span><span style="font-weight:600;">Pengaju:</span> {{ $report->reporter?->name ?? '-' }}</span>
                        <span><span style="font-weight:600;">Dept:</span> {{ $fr->department?->name ?? '-' }}</span>
                        <span><span style="font-weight:600;">Tgl Laporan:</span> {{ $report->report_date?->format('d/m/Y') }}</span>
                    </div>
                </div>
                <div style="text-align:right; flex-shrink:0;">
                    <div style="font-size:0.7rem; color:#94a3b8; margin-bottom:2px;">Dana Diajukan</div>
                    <div style="font-size:0.85rem; font-weight:700; color:#475569;">Rp {{ number_format($fr->amount, 0, ',', '.') }}</div>
                    <div style="font-size:0.7rem; color:#94a3b8; margin-top:6px; margin-bottom:2px;">Digunakan</div>
                    <div style="font-size:0.95rem; font-weight:800; color:#0d2d6b;">Rp {{ number_format($report->amount_used, 0, ',', '.') }}</div>
                </div>
            </div>

            {{-- Keterangan --}}
            <div style="font-size:0.8rem; color:#475569; background:#f8fafc; border-radius:8px; padding:10px 12px; margin-bottom:12px; line-height:1.5; border:1px solid #f1f5f9;">
                {{ Str::limit($report->description, 200) }}
            </div>

            {{-- Files --}}
            <div style="display:flex; gap:8px; flex-wrap:wrap; margin-bottom:14px;">
                @foreach($report->files as $file)
                <a href="{{ $file->url }}" target="_blank"
                   style="display:inline-flex; align-items:center; gap:5px; padding:5px 11px; background:#eff6ff; border:1px solid #bfdbfe; border-radius:6px; font-size:0.73rem; font-weight:600; color:#1d4ed8; text-decoration:none;">
                    <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                    {{ Str::limit($file->file_name, 25) }}
                </a>
                @endforeach
            </div>

            {{-- Actions --}}
            @if($report->isWaiting())
            <div style="display:flex; gap:8px; flex-wrap:wrap; border-top:1px solid #f1f5f9; padding-top:12px;">
                <form action="{{ route('finance.laporan.approve', $report) }}" method="POST">
                    @csrf
                    <button type="submit" onclick="return confirm('Setujui laporan ini?')"
                            style="padding:7px 18px; background:#22c55e; color:#fff; border:none; border-radius:8px; font-size:0.8rem; font-weight:600; cursor:pointer; display:flex; align-items:center; gap:6px;">
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        Setujui
                    </button>
                </form>
                <button type="button"
                        onclick="openRejectModal('{{ $report->id }}', '{{ addslashes($fr->title ?? '') }}')"
                        style="padding:7px 18px; background:#ef4444; color:#fff; border:none; border-radius:8px; font-size:0.8rem; font-weight:600; cursor:pointer; display:flex; align-items:center; gap:6px;">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    Tolak
                </button>
            </div>
            @elseif($report->reviewer)
            <div style="border-top:1px solid #f1f5f9; padding-top:10px; font-size:0.75rem; color:#94a3b8;">
                Diverifikasi oleh <strong>{{ $report->reviewer?->name }}</strong> pada {{ $report->reviewed_at?->format('d/m/Y H:i') }}
                @if($report->review_notes)
                · <span style="color:{{ $report->isRejected() ? '#991b1b' : '#166534' }};">{{ $report->review_notes }}</span>
                @endif
            </div>
            @endif
        </div>
    </div>
    @empty
    <div style="background:#fff; border:1px solid #e2e8f0; border-radius:14px; padding:60px 24px; text-align:center;">
        <div style="width:56px; height:56px; border-radius:14px; background:#f1f5f9; display:flex; align-items:center; justify-content:center; margin:0 auto 16px;">
            <svg width="24" height="24" fill="none" stroke="#94a3b8" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
        </div>
        <div style="font-size:0.9rem; font-weight:600; color:#475569;">Belum ada laporan</div>
        <div style="font-size:0.78rem; color:#94a3b8; margin-top:4px;">Laporan penggunaan dana akan muncul di sini setelah pengaju mengirimkan laporan</div>
    </div>
    @endforelse

    {{-- Pagination --}}
    @if($reports->hasPages())
    <div style="margin-top:20px;">
        {{ $reports->links() }}
    </div>
    @endif

{{-- Reject Modal --}}
<div id="reject-overlay" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.45); z-index:1000; align-items:center; justify-content:center; padding:16px;">
    <div style="background:#fff; border-radius:16px; padding:28px; width:100%; max-width:480px; box-shadow:0 20px 60px rgba(0,0,0,0.25);">
        <div style="font-size:0.95rem; font-weight:700; color:#0f172a; margin-bottom:4px;">Tolak Laporan</div>
        <div id="reject-title" style="font-size:0.8rem; color:#64748b; margin-bottom:18px;"></div>
        <form id="reject-form" method="POST">
            @csrf
            <div style="margin-bottom:16px;">
                <label style="display:block; font-size:0.78rem; font-weight:600; color:#374151; margin-bottom:6px;">
                    Alasan Penolakan <span style="color:#ef4444;">*</span>
                </label>
                <textarea name="review_notes" rows="4" required maxlength="1000"
                          placeholder="Tuliskan alasan penolakan atau hal yang perlu diperbaiki..."
                          style="width:100%; padding:10px 12px; border:1.5px solid #e2e8f0; border-radius:8px; font-size:0.85rem; resize:vertical; outline:none; box-sizing:border-box;"></textarea>
            </div>
            <div style="display:flex; gap:8px; justify-content:flex-end;">
                <button type="button" onclick="closeRejectModal()"
                        style="padding:8px 18px; border:1.5px solid #e2e8f0; border-radius:8px; font-size:0.82rem; font-weight:600; color:#475569; background:#fff; cursor:pointer;">
                    Batal
                </button>
                <button type="submit"
                        style="padding:8px 20px; background:#ef4444; color:#fff; border:none; border-radius:8px; font-size:0.82rem; font-weight:600; cursor:pointer;">
                    Kirim Penolakan
                </button>
            </div>
        </form>
    </div>
</div>

<script>
(function () {
    var overlay = document.getElementById('reject-overlay');
    var form    = document.getElementById('reject-form');
    var title   = document.getElementById('reject-title');

    window.openRejectModal = function (id, label) {
        title.textContent = label;
        form.action = '/finance/laporan/' + id + '/reject';
        overlay.style.display = 'flex';
    };

    window.closeRejectModal = function () {
        overlay.style.display = 'none';
        form.querySelector('textarea').value = '';
    };

    overlay.addEventListener('click', function (e) {
        if (e.target === overlay) closeRejectModal();
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') closeRejectModal();
    });
})();
</script>

</x-layouts.app>
