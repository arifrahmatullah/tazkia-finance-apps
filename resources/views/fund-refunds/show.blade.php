<x-layouts.app title="Pengembalian Dana" breadcrumb="Pengembalian sisa dana yang tidak terpakai">

<div style="max-width:720px; margin:0 auto;">

    {{-- Alert --}}
    @if(session('success'))
    <div style="background:#f0fdf4; border:1px solid #86efac; border-radius:10px; padding:12px 16px; margin-bottom:18px; color:#166534; font-size:0.82rem;">
        {{ session('success') }}
    </div>
    @endif
    @if(session('error'))
    <div style="background:#fef2f2; border:1px solid #fecaca; border-radius:10px; padding:12px 16px; margin-bottom:18px; color:#991b1b; font-size:0.82rem;">
        {{ session('error') }}
    </div>
    @endif

    {{-- Back --}}
    <a href="{{ route('fund-reports.index') }}"
       style="display:inline-flex; align-items:center; gap:6px; font-size:0.8rem; color:#64748b; text-decoration:none; margin-bottom:20px;">
        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
        Kembali ke Laporan Dana
    </a>

    {{-- Status header --}}
    @php
        $stHead = match($fundRefund->status) {
            'pending'   => ['bg' => '#fef2f2', 'border' => '#fecaca', 'color' => '#991b1b', 'label' => 'Perlu Dikembalikan', 'desc' => 'Transfer sisa dana ke rekening institusi lalu upload bukti transfernya di bawah.'],
            'waiting'   => ['bg' => '#fffbeb', 'border' => '#fcd34d', 'color' => '#92400e', 'label' => 'Menunggu Konfirmasi', 'desc' => 'Bukti transfer sudah dikirim. Menunggu keuangan mengkonfirmasi dana diterima.'],
            'confirmed' => ['bg' => '#f0fdf4', 'border' => '#86efac', 'color' => '#166534', 'label' => 'Selesai', 'desc' => 'Keuangan sudah mengkonfirmasi dana diterima. Pengembalian selesai.'],
        };
    @endphp
    <div style="background:{{ $stHead['bg'] }}; border:1.5px solid {{ $stHead['border'] }}; border-radius:12px; padding:18px 22px; margin-bottom:22px;">
        <div style="display:flex; align-items:center; justify-content:space-between; gap:12px; flex-wrap:wrap;">
            <div>
                <div style="font-size:0.7rem; font-weight:700; color:{{ $stHead['color'] }}; text-transform:uppercase; letter-spacing:0.05em; margin-bottom:4px;">
                    {{ $stHead['label'] }}
                </div>
                <div style="font-size:0.8rem; color:{{ $stHead['color'] }};">{{ $stHead['desc'] }}</div>
            </div>
            <div style="text-align:right;">
                <div style="font-size:0.7rem; color:{{ $stHead['color'] }}; margin-bottom:2px;">Nominal Pengembalian</div>
                <div style="font-size:1.3rem; font-weight:800; color:{{ $stHead['color'] }}; font-family:monospace;">
                    Rp {{ number_format($fundRefund->amount, 0, ',', '.') }}
                </div>
            </div>
        </div>
    </div>

    {{-- Info pengajuan --}}
    <div style="background:#fff; border:1px solid #e2e8f0; border-radius:12px; padding:16px 20px; margin-bottom:18px;">
        <div style="font-size:0.7rem; font-weight:700; color:#64748b; text-transform:uppercase; letter-spacing:0.05em; margin-bottom:10px;">Detail Pengajuan</div>
        <div style="font-size:0.9rem; font-weight:700; color:#0f172a; margin-bottom:6px;">{{ $fundRefund->fundRequest->title }}</div>
        <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(160px, 1fr)); gap:10px; font-size:0.78rem; color:#475569;">
            <div><span style="color:#94a3b8;">Ref:</span> <span style="font-family:monospace;">{{ $fundRefund->fundRequest->reference }}</span></div>
            <div><span style="color:#94a3b8;">Dana dicairkan:</span> Rp {{ number_format($fundRefund->fundRequest->amount, 0, ',', '.') }}</div>
            <div><span style="color:#94a3b8;">Digunakan (laporan):</span> Rp {{ number_format($fundRefund->fundReport->amount_used, 0, ',', '.') }}</div>
        </div>
    </div>

    {{-- Alasan penolakan bukti sebelumnya --}}
    @if($fundRefund->isPending() && $fundRefund->confirmation_notes)
    <div style="background:#fef2f2; border:1px solid #fecaca; border-radius:10px; padding:12px 16px; margin-bottom:18px; font-size:0.8rem; color:#991b1b;">
        <strong>Bukti sebelumnya ditolak keuangan:</strong> {{ $fundRefund->confirmation_notes }}
    </div>
    @endif

    {{-- Form pengembalian (hanya pengaju, status pending) --}}
    @if($isRequester && $fundRefund->isPending())
    <div style="background:#fff; border-radius:14px; border:1px solid #e2e8f0; overflow:hidden;">
        <div style="padding:18px 24px; border-bottom:1px solid #f1f5f9;">
            <div style="font-size:0.95rem; font-weight:700; color:#0f172a;">Form Pengembalian Dana</div>
            <div style="font-size:0.75rem; color:#64748b; margin-top:2px;">Transfer sisa dana ke rekening institusi, lalu lampirkan bukti transfernya</div>
        </div>
        <form action="{{ route('fund-refunds.pay', $fundRefund) }}" method="POST" enctype="multipart/form-data" style="padding:24px;">
            @csrf

            <div style="margin-bottom:18px;">
                <label style="display:block; font-size:0.78rem; font-weight:600; color:#374151; margin-bottom:6px;">
                    Rekening Tujuan Transfer <span style="color:#ef4444;">*</span>
                </label>
                <select name="refund_account_id" required
                        style="width:100%; padding:9px 12px; border:1.5px solid {{ $errors->has('refund_account_id') ? '#ef4444' : '#e2e8f0' }}; border-radius:8px; font-size:0.85rem; color:#0f172a; outline:none; background:#fff;">
                    <option value="">— Pilih rekening tujuan —</option>
                    @foreach($bankAccounts as $acc)
                    <option value="{{ $acc->id }}" {{ old('refund_account_id') === $acc->id ? 'selected' : '' }}>
                        {{ $acc->code }} — {{ $acc->name }}
                    </option>
                    @endforeach
                </select>
                @error('refund_account_id')
                <div style="color:#ef4444; font-size:0.72rem; margin-top:4px;">{{ $message }}</div>
                @enderror
            </div>

            <div style="margin-bottom:18px;">
                <label style="display:block; font-size:0.78rem; font-weight:600; color:#374151; margin-bottom:6px;">Keterangan</label>
                <textarea name="payment_notes" rows="3" maxlength="1000"
                          placeholder="Catatan tambahan (opsional), misal: transfer via BSI a.n. sendiri"
                          style="width:100%; padding:10px 12px; border:1.5px solid #e2e8f0; border-radius:8px; font-size:0.85rem; color:#0f172a; resize:vertical; outline:none; box-sizing:border-box;">{{ old('payment_notes') }}</textarea>
            </div>

            <div style="margin-bottom:24px;">
                <label style="display:block; font-size:0.78rem; font-weight:600; color:#374151; margin-bottom:6px;">
                    Bukti Transfer <span style="color:#ef4444;">*</span>
                </label>
                <div style="border:2px dashed #e2e8f0; border-radius:10px; padding:20px; text-align:center; background:#fafafa; cursor:pointer;"
                     onclick="document.getElementById('proof-input').click()">
                    <svg width="28" height="28" fill="none" stroke="#94a3b8" stroke-width="1.5" viewBox="0 0 24 24" style="margin:0 auto 8px;">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                    </svg>
                    <div style="font-size:0.82rem; font-weight:600; color:#475569; margin-bottom:4px;">Klik untuk upload bukti transfer</div>
                    <div style="font-size:0.7rem; color:#94a3b8;">Format: PDF, JPG, PNG · Maks. 10 MB</div>
                </div>
                <input type="file" id="proof-input" name="proof" required accept=".pdf,.jpg,.jpeg,.png" style="display:none;"
                       onchange="showProofName(this)">
                <div id="proof-name" style="margin-top:8px; font-size:0.78rem; color:#475569;"></div>
                @error('proof')
                <div style="color:#ef4444; font-size:0.72rem; margin-top:4px;">{{ $message }}</div>
                @enderror
            </div>

            <div style="display:flex; gap:10px; justify-content:flex-end;">
                <button type="submit"
                        style="padding:9px 24px; background:linear-gradient(135deg, #dc2626, #ef4444); color:#fff; border:none; border-radius:8px; font-size:0.83rem; font-weight:600; cursor:pointer;">
                    Kirim Bukti Pengembalian
                </button>
            </div>
        </form>
    </div>

    <script>
    function showProofName(input) {
        const el = document.getElementById('proof-name');
        el.textContent = input.files.length ? '📎 ' + input.files[0].name : '';
    }
    </script>
    @endif

    {{-- Detail pembayaran (setelah dikirim) --}}
    @if(!$fundRefund->isPending())
    <div style="background:#fff; border-radius:14px; border:1px solid #e2e8f0; overflow:hidden; margin-bottom:18px;">
        <div style="padding:16px 22px; border-bottom:1px solid #f1f5f9;">
            <div style="font-size:0.9rem; font-weight:700; color:#0f172a;">Detail Pengembalian</div>
        </div>
        <div style="padding:18px 22px; display:grid; grid-template-columns:repeat(auto-fit, minmax(200px, 1fr)); gap:14px; font-size:0.82rem;">
            <div>
                <div style="color:#94a3b8; font-size:0.72rem; margin-bottom:3px;">Dikembalikan Oleh</div>
                <div style="color:#0f172a; font-weight:600;">{{ $fundRefund->payer?->name ?? '-' }}</div>
            </div>
            <div>
                <div style="color:#94a3b8; font-size:0.72rem; margin-bottom:3px;">Tanggal</div>
                <div style="color:#0f172a; font-weight:600;">{{ $fundRefund->paid_at?->format('d/m/Y H:i') }}</div>
            </div>
            <div>
                <div style="color:#94a3b8; font-size:0.72rem; margin-bottom:3px;">Rekening Tujuan</div>
                <div style="color:#0f172a; font-weight:600;">{{ $fundRefund->refundAccount?->name ?? '-' }}</div>
            </div>
            @if($fundRefund->payment_notes)
            <div style="grid-column:1/-1;">
                <div style="color:#94a3b8; font-size:0.72rem; margin-bottom:3px;">Keterangan</div>
                <div style="color:#0f172a;">{{ $fundRefund->payment_notes }}</div>
            </div>
            @endif
            @if($fundRefund->proof_path)
            <div style="grid-column:1/-1;">
                <div style="color:#94a3b8; font-size:0.72rem; margin-bottom:6px;">Bukti Transfer</div>
                <a href="{{ $fundRefund->proof_url }}" target="_blank"
                   style="display:inline-flex; align-items:center; gap:8px; padding:8px 14px; background:#f8fafc; border:1px solid #e2e8f0; border-radius:8px; font-size:0.78rem; color:#1d4ed8; text-decoration:none; font-weight:600;">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                    {{ $fundRefund->proof_name }}
                </a>
            </div>
            @endif
        </div>
    </div>
    @endif

    {{-- Hasil konfirmasi keuangan --}}
    @if($fundRefund->isConfirmed())
    <div style="background:#f0fdf4; border:1px solid #86efac; border-radius:12px; padding:16px 20px;">
        <div style="font-size:0.78rem; font-weight:700; color:#166534; margin-bottom:4px;">
            Dikonfirmasi oleh {{ $fundRefund->confirmer?->name ?? '-' }} · {{ $fundRefund->confirmed_at?->format('d/m/Y H:i') }}
        </div>
        @if($fundRefund->confirmation_notes)
        <div style="font-size:0.8rem; color:#166534;">{{ $fundRefund->confirmation_notes }}</div>
        @endif
    </div>
    @endif

</div>

</x-layouts.app>
