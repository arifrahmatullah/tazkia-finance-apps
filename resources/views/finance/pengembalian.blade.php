<x-layouts.app title="Konfirmasi Pengembalian Dana" breadcrumb="Konfirmasi penerimaan sisa dana yang dikembalikan pengaju">

    {{-- Header --}}
    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:24px; flex-wrap:wrap; gap:12px;">
        <div>
            <h2 style="font-size:1.2rem; font-weight:700; color:#0f172a; margin:0;">Pengembalian Dana</h2>
            <p style="font-size:0.78rem; color:#64748b; margin:4px 0 0;">Konfirmasi penerimaan sisa dana yang dikembalikan pengaju</p>
        </div>
        <a href="{{ route('finance.laporan') }}"
           style="display:inline-flex; align-items:center; gap:6px; padding:8px 16px; border:1.5px solid #e2e8f0; border-radius:8px; font-size:0.8rem; font-weight:600; color:#475569; text-decoration:none;">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
            Verifikasi Laporan
        </a>
    </div>

    {{-- Alert --}}
    @if(session('success'))
    <div style="background:#f0fdf4; border:1px solid #86efac; border-radius:10px; padding:12px 16px; margin-bottom:18px; color:#166534; font-size:0.82rem; display:flex; align-items:center; gap:8px;">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        {{ session('success') }}
    </div>
    @endif
    @if(session('warning'))
    <div style="background:#fffbeb; border:1px solid #fcd34d; border-radius:10px; padding:12px 16px; margin-bottom:18px; color:#92400e; font-size:0.82rem; display:flex; align-items:center; gap:8px;">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
        {{ session('warning') }}
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
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Belum Dikembalikan</option>
                    <option value="waiting" {{ request('status') === 'waiting' ? 'selected' : '' }}>Menunggu Konfirmasi</option>
                    <option value="confirmed" {{ request('status') === 'confirmed' ? 'selected' : '' }}>Selesai</option>
                </select>
            </div>
            <div style="flex:1; min-width:200px;">
                <label style="display:block; font-size:0.72rem; font-weight:600; color:#64748b; margin-bottom:4px;">Cari</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Ref / judul pengajuan..."
                       style="width:100%; padding:7px 12px; border:1.5px solid #e2e8f0; border-radius:7px; font-size:0.8rem; color:#374151; outline:none;">
            </div>
            <button type="submit" style="padding:8px 18px; background:#0f172a; color:#fff; border:none; border-radius:7px; font-size:0.8rem; font-weight:600; cursor:pointer;">Cari</button>
            @if(request()->hasAny(['status', 'search']))
            <a href="{{ route('finance.pengembalian') }}" style="padding:8px 14px; border:1.5px solid #e2e8f0; border-radius:7px; font-size:0.8rem; color:#64748b; text-decoration:none;">Reset</a>
            @endif
        </form>
    </div>

    {{-- Cards --}}
    @forelse($refunds as $refund)
    @php
        $fr = $refund->fundRequest;
        $stripe = match($refund->status) {
            'pending'   => '#ef4444',
            'waiting'   => '#f59e0b',
            'confirmed' => '#22c55e',
            default     => '#94a3b8',
        };
        $badgeBg = match($refund->status) {
            'pending'   => '#fee2e2',
            'waiting'   => '#fef3c7',
            'confirmed' => '#dcfce7',
            default     => '#f1f5f9',
        };
        $badgeColor = match($refund->status) {
            'pending'   => '#991b1b',
            'waiting'   => '#92400e',
            'confirmed' => '#166534',
            default     => '#475569',
        };
        $badgeLabel = match($refund->status) {
            'pending'   => 'Belum Dikembalikan',
            'waiting'   => 'Menunggu Konfirmasi',
            'confirmed' => 'Selesai',
            default     => '-',
        };
    @endphp
    <div style="background:#fff; border-radius:14px; border:1px solid #e2e8f0; overflow:hidden; margin-bottom:14px; display:flex;">
        <div style="width:4px; background:{{ $stripe }}; flex-shrink:0;"></div>
        <div style="flex:1; padding:18px 20px;">

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
                        <span><span style="font-weight:600;">Pengaju:</span> {{ $fr->requester?->name ?? '-' }}</span>
                        <span><span style="font-weight:600;">Dept:</span> {{ $fr->department?->name ?? '-' }}</span>
                        <span><span style="font-weight:600;">Digunakan:</span> Rp {{ number_format($refund->fundReport?->amount_used ?? 0, 0, ',', '.') }} dari Rp {{ number_format($fr->amount, 0, ',', '.') }}</span>
                    </div>
                </div>
                <div style="text-align:right; flex-shrink:0;">
                    <div style="font-size:0.7rem; color:#94a3b8; margin-bottom:2px;">Nominal Pengembalian</div>
                    <div style="font-size:1.05rem; font-weight:800; color:#dc2626;">Rp {{ number_format($refund->amount, 0, ',', '.') }}</div>
                </div>
            </div>

            {{-- Detail pembayaran pengaju --}}
            @if(!$refund->isPending())
            <div style="font-size:0.78rem; color:#475569; background:#f8fafc; border-radius:8px; padding:10px 12px; margin-bottom:12px; border:1px solid #f1f5f9; display:flex; gap:16px; flex-wrap:wrap; align-items:center;">
                <span>Dikembalikan {{ $refund->paid_at?->format('d/m/Y H:i') }}</span>
                <span>Rekening: <strong>{{ $refund->refundAccount?->name ?? '-' }}</strong></span>
                @if($refund->payment_notes)
                <span style="color:#64748b;">"{{ Str::limit($refund->payment_notes, 80) }}"</span>
                @endif
                @if($refund->proof_path)
                <a href="{{ $refund->proof_url }}" target="_blank"
                   style="display:inline-flex; align-items:center; gap:5px; padding:4px 10px; background:#eff6ff; border:1px solid #bfdbfe; border-radius:6px; font-size:0.72rem; font-weight:600; color:#1d4ed8; text-decoration:none;">
                    <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                    {{ Str::limit($refund->proof_name, 25) }}
                </a>
                @endif
            </div>
            @endif

            {{-- Actions --}}
            @if($refund->isWaiting())
            <div style="display:flex; gap:8px; flex-wrap:wrap; border-top:1px solid #f1f5f9; padding-top:12px;">
                <form action="{{ route('finance.pengembalian.confirm', $refund) }}" method="POST">
                    @csrf
                    <button type="submit" onclick="return confirm('Konfirmasi dana Rp {{ number_format($refund->amount, 0, ',', '.') }} sudah diterima?')"
                            style="padding:7px 18px; background:#22c55e; color:#fff; border:none; border-radius:8px; font-size:0.8rem; font-weight:600; cursor:pointer; display:flex; align-items:center; gap:6px;">
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        Konfirmasi Diterima
                    </button>
                </form>
                <button type="button"
                        onclick="openRejectModal('{{ $refund->id }}', '{{ addslashes($fr->title ?? '') }}')"
                        style="padding:7px 18px; background:#ef4444; color:#fff; border:none; border-radius:8px; font-size:0.8rem; font-weight:600; cursor:pointer; display:flex; align-items:center; gap:6px;">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    Tolak Bukti
                </button>
            </div>
            @elseif($refund->isConfirmed())
            <div style="border-top:1px solid #f1f5f9; padding-top:10px; font-size:0.75rem; color:#94a3b8;">
                Dikonfirmasi oleh <strong>{{ $refund->confirmer?->name }}</strong> pada {{ $refund->confirmed_at?->format('d/m/Y H:i') }}
                @if($refund->confirmation_notes)
                · <span style="color:#166534;">{{ $refund->confirmation_notes }}</span>
                @endif
            </div>
            @else
            <div style="border-top:1px solid #f1f5f9; padding-top:10px; font-size:0.75rem; color:#94a3b8;">
                Menunggu pengaju mentransfer dan mengupload bukti pengembalian.
                @if($refund->confirmation_notes)
                <span style="color:#991b1b;">Bukti sebelumnya ditolak: {{ $refund->confirmation_notes }}</span>
                @endif
            </div>
            @endif
        </div>
    </div>
    @empty
    <div style="background:#fff; border:1px solid #e2e8f0; border-radius:14px; padding:60px 24px; text-align:center;">
        <div style="width:56px; height:56px; border-radius:14px; background:#f1f5f9; display:flex; align-items:center; justify-content:center; margin:0 auto 16px;">
            <svg width="24" height="24" fill="none" stroke="#94a3b8" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
            </svg>
        </div>
        <div style="font-size:0.9rem; font-weight:600; color:#475569;">Belum ada pengembalian dana</div>
        <div style="font-size:0.78rem; color:#94a3b8; margin-top:4px;">Tagihan pengembalian dibuat otomatis saat laporan dengan sisa dana disetujui</div>
    </div>
    @endforelse

    {{-- Pagination --}}
    @if($refunds->hasPages())
    <div style="margin-top:20px;">
        {{ $refunds->links() }}
    </div>
    @endif

{{-- Reject Modal --}}
<div id="reject-overlay" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.45); z-index:1000; align-items:center; justify-content:center; padding:16px;">
    <div style="background:#fff; border-radius:16px; padding:28px; width:100%; max-width:480px; box-shadow:0 20px 60px rgba(0,0,0,0.25);">
        <div style="font-size:0.95rem; font-weight:700; color:#0f172a; margin-bottom:4px;">Tolak Bukti Pengembalian</div>
        <div id="reject-title" style="font-size:0.8rem; color:#64748b; margin-bottom:18px;"></div>
        <form id="reject-form" method="POST">
            @csrf
            <div style="margin-bottom:16px;">
                <label style="display:block; font-size:0.78rem; font-weight:600; color:#374151; margin-bottom:6px;">
                    Alasan Penolakan <span style="color:#ef4444;">*</span>
                </label>
                <textarea name="confirmation_notes" rows="4" required maxlength="1000"
                          placeholder="Contoh: nominal transfer tidak sesuai, bukti tidak terbaca..."
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
        form.action = '/finance/pengembalian/' + id + '/reject';
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
