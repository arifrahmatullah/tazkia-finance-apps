<x-layouts.app title="Pengembalian Dana" breadcrumb="Tagihan pengembalian sisa dana yang tidak terpakai">

    {{-- Alert --}}
    @if(session('success'))
    <div style="background:#f0fdf4; border:1px solid #86efac; border-radius:10px; padding:12px 16px; margin-bottom:18px; color:#166534; font-size:0.82rem; display:flex; align-items:center; gap:8px;">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        {{ session('success') }}
    </div>
    @endif
    @if(session('error'))
    <div style="background:#fef2f2; border:1px solid #fecaca; border-radius:10px; padding:12px 16px; margin-bottom:18px; color:#991b1b; font-size:0.82rem; display:flex; align-items:center; gap:8px;">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        {{ session('error') }}
    </div>
    @endif
    @if($errors->any())
    <div style="background:#fef2f2; border:1px solid #fecaca; border-radius:10px; padding:12px 16px; margin-bottom:18px; color:#991b1b; font-size:0.82rem;">
        @foreach($errors->all() as $error)
        <div style="display:flex; align-items:center; gap:8px;">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            {{ $error }}
        </div>
        @endforeach
    </div>
    @endif

    {{-- Ringkasan --}}
    <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(170px, 1fr)); gap:12px; margin-bottom:22px;">
        <div style="background:{{ $summary['pending'] > 0 ? '#fef2f2' : '#fff' }}; border:1px solid {{ $summary['pending'] > 0 ? '#fecaca' : '#e2e8f0' }}; border-radius:12px; padding:14px 16px;">
            <div style="font-size:0.7rem; font-weight:700; color:{{ $summary['pending'] > 0 ? '#991b1b' : '#64748b' }}; text-transform:uppercase; letter-spacing:0.03em; margin-bottom:6px;">Perlu Dikembalikan</div>
            <div style="font-size:1.35rem; font-weight:800; color:{{ $summary['pending'] > 0 ? '#dc2626' : '#0f172a' }}; line-height:1;">{{ $summary['pending'] }}</div>
            <div style="font-size:0.72rem; color:{{ $summary['pending'] > 0 ? '#b91c1c' : '#94a3b8' }}; margin-top:5px;">
                @if($summary['pending'] > 0)
                Total Rp {{ number_format($summary['pending_amount'], 0, ',', '.') }}
                @else
                Tidak ada tagihan
                @endif
            </div>
        </div>
        <div style="background:#fff; border:1px solid #e2e8f0; border-radius:12px; padding:14px 16px;">
            <div style="font-size:0.7rem; font-weight:700; color:#64748b; text-transform:uppercase; letter-spacing:0.03em; margin-bottom:6px;">Menunggu Konfirmasi</div>
            <div style="font-size:1.35rem; font-weight:800; color:#d97706; line-height:1;">{{ $summary['waiting'] }}</div>
            <div style="font-size:0.72rem; color:#94a3b8; margin-top:5px;">Bukti sedang diperiksa keuangan</div>
        </div>
        <div style="background:#fff; border:1px solid #e2e8f0; border-radius:12px; padding:14px 16px;">
            <div style="font-size:0.7rem; font-weight:700; color:#64748b; text-transform:uppercase; letter-spacing:0.03em; margin-bottom:6px;">Selesai</div>
            <div style="font-size:1.35rem; font-weight:800; color:#16a34a; line-height:1;">{{ $summary['confirmed'] }}</div>
            <div style="font-size:0.72rem; color:#94a3b8; margin-top:5px;">Dana sudah diterima keuangan</div>
        </div>
    </div>

    {{-- Filter --}}
    <form method="GET" action="{{ route('fund-refunds.index') }}"
          style="display:flex; gap:10px; flex-wrap:wrap; align-items:center; margin-bottom:18px;">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari referensi / judul pengajuan..."
               style="flex:1; min-width:220px; padding:9px 14px; border:1px solid #e2e8f0; border-radius:9px; font-size:0.8rem; outline:none; background:#fff;">
        <select name="status" onchange="this.form.submit()"
                style="padding:9px 14px; border:1px solid #e2e8f0; border-radius:9px; font-size:0.8rem; outline:none; background:#fff; color:#475569;">
            <option value="">Semua Status</option>
            <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Perlu Dikembalikan</option>
            <option value="waiting" {{ request('status') === 'waiting' ? 'selected' : '' }}>Menunggu Konfirmasi</option>
            <option value="confirmed" {{ request('status') === 'confirmed' ? 'selected' : '' }}>Selesai</option>
        </select>
        <button type="submit"
                style="padding:9px 18px; background:#0f172a; color:#fff; border:none; border-radius:9px; font-size:0.8rem; font-weight:600; cursor:pointer;">
            Cari
        </button>
        @if(request('search') || request('status'))
        <a href="{{ route('fund-refunds.index') }}" style="font-size:0.78rem; color:#64748b; text-decoration:none;">Reset</a>
        @endif
    </form>

    {{-- Pilih semua (hanya jika ada tagihan pending di halaman ini) --}}
    @php $hasPendingOnPage = $refunds->getCollection()->contains(fn($r) => $r->isPending()); @endphp
    @if($hasPendingOnPage)
    <label style="display:inline-flex; align-items:center; gap:9px; margin-bottom:12px; cursor:pointer; font-size:0.8rem; color:#475569; font-weight:600;">
        <input type="checkbox" id="check-all-refunds" style="width:17px; height:17px; accent-color:#dc2626; cursor:pointer;">
        Pilih semua yang perlu dikembalikan
        <span style="font-size:0.72rem; color:#94a3b8; font-weight:400;">— ceklis beberapa tagihan untuk dikembalikan sekaligus dengan satu bukti transfer</span>
    </label>
    @endif

    {{-- Daftar Pengembalian --}}
    @forelse($refunds as $refund)
    @php
        $rfBorder = match($refund->status) {
            'pending'   => '#fca5a5',
            'waiting'   => '#fcd34d',
            'confirmed' => '#86efac',
            default     => '#e2e8f0',
        };
        $rfBg = match($refund->status) {
            'pending'   => '#fef2f2',
            'waiting'   => '#fffbeb',
            'confirmed' => '#f0fdf4',
            default     => '#fff',
        };
        $rfLabel = match($refund->status) {
            'pending'   => 'Perlu Dikembalikan',
            'waiting'   => 'Menunggu Konfirmasi Keuangan',
            'confirmed' => 'Selesai — Dana Diterima',
            default     => '-',
        };
        $rfLabelColor = match($refund->status) {
            'pending'   => '#991b1b',
            'waiting'   => '#92400e',
            'confirmed' => '#166534',
            default     => '#475569',
        };
    @endphp
    <div style="background:{{ $rfBg }}; border:1.5px solid {{ $rfBorder }}; border-radius:12px; padding:16px 18px; margin-bottom:10px; display:flex; align-items:center; gap:16px; flex-wrap:wrap;">
        @if($refund->isPending())
        <input type="checkbox" class="refund-check"
               value="{{ $refund->id }}"
               data-amount="{{ $refund->amount }}"
               data-ref="{{ $refund->fundRequest?->reference }}"
               data-title="{{ $refund->fundRequest?->title }}"
               style="width:18px; height:18px; accent-color:#dc2626; cursor:pointer; flex-shrink:0;">
        @endif
        <div style="flex:1; min-width:0;">
            <div style="display:flex; align-items:center; gap:8px; flex-wrap:wrap; margin-bottom:4px;">
                <span style="font-size:0.72rem; font-family:monospace; font-weight:700; color:#64748b;">{{ $refund->fundRequest?->reference }}</span>
                <span style="padding:2px 10px; border-radius:999px; font-size:0.68rem; font-weight:700; background:#fff; color:{{ $rfLabelColor }}; border:1px solid {{ $rfBorder }};">{{ $rfLabel }}</span>
            </div>
            <div style="font-size:0.88rem; font-weight:700; color:#0f172a; margin-bottom:4px;">{{ $refund->fundRequest?->title }}</div>
            <div style="display:flex; gap:14px; flex-wrap:wrap; font-size:0.76rem; color:#64748b;">
                <span>Sisa dana: <strong style="color:#0f172a;">Rp {{ number_format($refund->amount, 0, ',', '.') }}</strong></span>
                @if($refund->fundRequest?->department)
                <span>·</span>
                <span>{{ $refund->fundRequest->department->name }}</span>
                @endif
                @if($refund->isWaiting() && $refund->paid_at)
                <span>·</span>
                <span>Dikirim: {{ $refund->paid_at->format('d/m/Y H:i') }}</span>
                @endif
                @if($refund->isConfirmed() && $refund->confirmed_at)
                <span>·</span>
                <span>Dikonfirmasi: {{ $refund->confirmed_at->format('d/m/Y H:i') }}</span>
                @endif
            </div>
            @if($refund->isPending() && $refund->confirmation_notes)
            <div style="margin-top:8px; padding:8px 12px; background:#fff; border:1px solid #fecaca; border-radius:7px; font-size:0.75rem; color:#991b1b;">
                <strong>Bukti sebelumnya ditolak:</strong> {{ $refund->confirmation_notes }}
            </div>
            @endif
        </div>
        <a href="{{ route('fund-refunds.show', $refund) }}"
           style="display:inline-flex; align-items:center; gap:7px; padding:9px 20px; border-radius:9px; font-size:0.82rem; font-weight:700; text-decoration:none; white-space:nowrap;
                  {{ $refund->isPending()
                      ? 'color:#fff; background:linear-gradient(135deg,#dc2626,#ef4444); box-shadow:0 2px 8px rgba(220,38,38,0.3);'
                      : 'color:#475569; background:#f1f5f9;' }}">
            {{ $refund->isPending() ? 'Kembalikan Dana' : 'Lihat Detail' }}
        </a>
    </div>
    @empty
    <div style="background:#fff; border:1px solid #e2e8f0; border-radius:14px; padding:52px 24px; text-align:center;">
        <div style="width:52px; height:52px; border-radius:14px; background:#f1f5f9; display:flex; align-items:center; justify-content:center; margin:0 auto 14px;">
            <svg width="22" height="22" fill="none" stroke="#94a3b8" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M16 15v-1a4 4 0 00-4-4H8m0 0l3 3m-3-3l3-3m9 14V5a2 2 0 00-2-2H6a2 2 0 00-2 2v14a2 2 0 002 2h12a2 2 0 002-2z"/>
            </svg>
        </div>
        <div style="font-size:0.88rem; font-weight:600; color:#475569; margin-bottom:5px;">
            {{ request('search') || request('status') ? 'Tidak ada pengembalian yang cocok dengan filter' : 'Belum ada tagihan pengembalian dana' }}
        </div>
        <div style="font-size:0.78rem; color:#94a3b8;">
            Tagihan muncul otomatis jika laporan dana Anda disetujui dan ada sisa dana yang tidak terpakai
        </div>
    </div>
    @endforelse

    {{-- Pagination --}}
    <div style="margin-top:16px; display:flex; align-items:center; justify-content:space-between; gap:12px; flex-wrap:wrap;">
        <div style="font-size:0.76rem; color:#64748b;">
            Menampilkan {{ $refunds->firstItem() ?? 0 }}&ndash;{{ $refunds->lastItem() ?? 0 }} dari {{ $refunds->total() }} pengembalian
        </div>
        @if($refunds->hasPages())
        <div>{{ $refunds->links() }}</div>
        @endif
    </div>

    @if($hasPendingOnPage)
    {{-- Spacer agar konten tidak tertutup bar pilihan --}}
    <div id="bulk-spacer" style="height:84px; display:none;"></div>

    {{-- Bar total pilihan (muncul saat ada yang diceklis) --}}
    <div id="bulk-bar" style="display:none; position:fixed; bottom:0; right:0; z-index:40; background:rgba(255,255,255,0.96); backdrop-filter:blur(6px); border-top:1.5px solid #fca5a5; padding:14px 24px; align-items:center; gap:16px; flex-wrap:wrap; box-shadow:0 -4px 16px rgba(0,0,0,0.06);">
        <div style="flex:1; min-width:200px;">
            <div style="font-size:0.75rem; color:#64748b; margin-bottom:2px;"><span id="bulk-count">0</span> pengembalian dipilih</div>
            <div style="font-size:1.05rem; font-weight:800; color:#dc2626;">Total: <span id="bulk-total">Rp 0</span></div>
        </div>
        <button type="button" onclick="openBulkModal()"
                style="display:inline-flex; align-items:center; gap:8px; padding:11px 26px; border:none; border-radius:10px; font-size:0.85rem; font-weight:700; color:#fff; cursor:pointer; background:linear-gradient(135deg,#dc2626,#ef4444); box-shadow:0 3px 10px rgba(220,38,38,0.35);">
            <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 19V5m0 0l-6 6m6-6l6 6"/></svg>
            Ajukan Pengembalian
        </button>
    </div>

    {{-- Modal form pengembalian sekaligus --}}
    <div id="bulk-modal" style="display:none; position:fixed; inset:0; z-index:60; background:rgba(15,23,42,0.55); align-items:center; justify-content:center; padding:20px;">
        <div style="background:#fff; border-radius:16px; width:100%; max-width:520px; max-height:90vh; overflow-y:auto; padding:24px;">
            <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:16px;">
                <h3 style="font-size:0.95rem; font-weight:800; color:#0f172a; margin:0;">Ajukan Pengembalian Dana</h3>
                <button type="button" onclick="closeBulkModal()" style="background:none; border:none; cursor:pointer; color:#94a3b8; font-size:1.2rem; line-height:1;">&times;</button>
            </div>

            <div style="font-size:0.78rem; color:#64748b; margin-bottom:10px;">
                Transfer <strong style="color:#dc2626;" id="modal-total">Rp 0</strong> sekaligus untuk pengembalian berikut, lalu lampirkan satu bukti transfernya:
            </div>
            <div id="modal-list" style="border:1px solid #e2e8f0; border-radius:10px; overflow:hidden; margin-bottom:16px;"></div>

            <form method="POST" action="{{ route('fund-refunds.pay-bulk') }}" enctype="multipart/form-data">
                @csrf
                <div id="modal-ids"></div>

                <div style="margin-bottom:14px;">
                    <label style="display:block; font-size:0.76rem; font-weight:700; color:#334155; margin-bottom:6px;">Rekening Tujuan Transfer <span style="color:#dc2626;">*</span></label>
                    <select name="refund_account_id" required
                            style="width:100%; padding:10px 12px; border:1px solid #e2e8f0; border-radius:9px; font-size:0.8rem; outline:none; background:#fff; color:#0f172a;">
                        <option value="">-- Pilih rekening --</option>
                        @foreach($bankAccounts as $acc)
                        <option value="{{ $acc->id }}">{{ $acc->code }} — {{ $acc->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div style="margin-bottom:14px;">
                    <label style="display:block; font-size:0.76rem; font-weight:700; color:#334155; margin-bottom:6px;">Bukti Transfer <span style="color:#dc2626;">*</span></label>
                    <input type="file" name="proof" required accept=".pdf,.jpg,.jpeg,.png"
                           style="width:100%; padding:9px 12px; border:1px dashed #cbd5e1; border-radius:9px; font-size:0.78rem; background:#f8fafc;">
                    <div style="font-size:0.7rem; color:#94a3b8; margin-top:5px;">PDF/JPG/PNG, maks 10 MB. Satu bukti berlaku untuk semua pengembalian yang dipilih.</div>
                </div>

                <div style="margin-bottom:18px;">
                    <label style="display:block; font-size:0.76rem; font-weight:700; color:#334155; margin-bottom:6px;">Catatan (opsional)</label>
                    <textarea name="payment_notes" rows="2" maxlength="1000" placeholder="Misal: transfer gabungan 2 pengembalian via BSI"
                              style="width:100%; padding:10px 12px; border:1px solid #e2e8f0; border-radius:9px; font-size:0.8rem; outline:none; resize:vertical;"></textarea>
                </div>

                <div style="display:flex; gap:10px; justify-content:flex-end;">
                    <button type="button" onclick="closeBulkModal()"
                            style="padding:10px 18px; background:#f1f5f9; color:#475569; border:none; border-radius:9px; font-size:0.8rem; font-weight:600; cursor:pointer;">Batal</button>
                    <button type="submit"
                            style="padding:10px 22px; border:none; border-radius:9px; font-size:0.8rem; font-weight:700; color:#fff; cursor:pointer; background:linear-gradient(135deg,#dc2626,#ef4444);">
                        Kirim Bukti Pengembalian
                    </button>
                </div>
            </form>
        </div>
    </div>

    <style>
        #bulk-bar { left: 260px; }
        @media (max-width: 1024px) { #bulk-bar { left: 0; } }
    </style>

    <script>
        const refundChecks = Array.from(document.querySelectorAll('.refund-check'));
        const checkAll     = document.getElementById('check-all-refunds');
        const bulkBar      = document.getElementById('bulk-bar');
        const bulkSpacer   = document.getElementById('bulk-spacer');

        function formatRp(n) {
            return 'Rp ' + Math.round(n).toLocaleString('id-ID');
        }

        function selectedRefunds() {
            return refundChecks.filter(c => c.checked);
        }

        function updateBulkBar() {
            const sel   = selectedRefunds();
            const total = sel.reduce((s, c) => s + parseFloat(c.dataset.amount || 0), 0);

            document.getElementById('bulk-count').textContent = sel.length;
            document.getElementById('bulk-total').textContent = formatRp(total);

            const show = sel.length > 0;
            bulkBar.style.display    = show ? 'flex' : 'none';
            bulkSpacer.style.display = show ? 'block' : 'none';

            if (checkAll) {
                checkAll.checked = refundChecks.length > 0 && sel.length === refundChecks.length;
            }
        }

        refundChecks.forEach(c => c.addEventListener('change', updateBulkBar));
        if (checkAll) {
            checkAll.addEventListener('change', () => {
                refundChecks.forEach(c => c.checked = checkAll.checked);
                updateBulkBar();
            });
        }

        function openBulkModal() {
            const sel = selectedRefunds();
            if (sel.length === 0) return;

            const total = sel.reduce((s, c) => s + parseFloat(c.dataset.amount || 0), 0);
            document.getElementById('modal-total').textContent = formatRp(total);

            const list = document.getElementById('modal-list');
            const ids  = document.getElementById('modal-ids');
            list.innerHTML = '';
            ids.innerHTML  = '';

            sel.forEach((c, i) => {
                const row = document.createElement('div');
                row.style.cssText = 'display:flex; align-items:center; justify-content:space-between; gap:10px; padding:9px 13px; font-size:0.76rem;'
                    + (i > 0 ? ' border-top:1px solid #f1f5f9;' : '');
                const info = document.createElement('div');
                info.style.cssText = 'min-width:0;';
                const ref = document.createElement('div');
                ref.style.cssText = 'font-family:monospace; font-weight:700; color:#64748b; font-size:0.68rem;';
                ref.textContent = c.dataset.ref;
                const title = document.createElement('div');
                title.style.cssText = 'color:#0f172a; font-weight:600; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;';
                title.textContent = c.dataset.title;
                info.append(ref, title);

                const amount = document.createElement('div');
                amount.style.cssText = 'font-weight:700; color:#dc2626; white-space:nowrap;';
                amount.textContent = formatRp(parseFloat(c.dataset.amount || 0));

                row.append(info, amount);
                list.appendChild(row);

                const input = document.createElement('input');
                input.type  = 'hidden';
                input.name  = 'refund_ids[]';
                input.value = c.value;
                ids.appendChild(input);
            });

            document.getElementById('bulk-modal').style.display = 'flex';
        }

        function closeBulkModal() {
            document.getElementById('bulk-modal').style.display = 'none';
        }

        document.getElementById('bulk-modal').addEventListener('click', function (e) {
            if (e.target === this) closeBulkModal();
        });
    </script>
    @endif

</x-layouts.app>
