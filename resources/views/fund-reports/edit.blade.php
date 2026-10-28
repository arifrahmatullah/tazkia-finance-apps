<x-layouts.app title="Edit Laporan Dana" breadcrumb="Laporan penggunaan dana setelah pencairan">

<div style="max-width:720px; margin:0 auto;">

    {{-- Back --}}
    <a href="{{ route('fund-reports.show', $fundReport) }}"
       style="display:inline-flex; align-items:center; gap:6px; font-size:0.8rem; color:#64748b; text-decoration:none; margin-bottom:20px;">
        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
        Kembali ke Detail Laporan
    </a>

    @php $fundRequest = $fundReport->fundRequest; @endphp

    {{-- Info Pengajuan --}}
    <div style="background:#eff6ff; border:1px solid #bfdbfe; border-radius:12px; padding:16px 20px; margin-bottom:24px;">
        <div style="font-size:0.7rem; font-weight:700; color:#1d4ed8; text-transform:uppercase; letter-spacing:0.05em; margin-bottom:8px;">Detail Pengajuan</div>
        <div style="font-size:0.9rem; font-weight:700; color:#0f172a; margin-bottom:4px;">{{ $fundRequest->title }}</div>
        <div style="display:flex; gap:20px; flex-wrap:wrap; font-size:0.78rem; color:#475569;">
            <span>
                <span style="font-weight:600;">Ref:</span>
                <span style="font-family:monospace;">{{ $fundRequest->reference }}</span>
            </span>
            <span>
                <span style="font-weight:600;">Jumlah Diajukan:</span>
                Rp {{ number_format($fundRequest->amount, 0, ',', '.') }}
            </span>
            <span>
                <span style="font-weight:600;">Dicairkan:</span>
                {{ $fundRequest->disbursed_at?->format('d/m/Y') }}
            </span>
        </div>
    </div>

    <div style="background:#fffbeb; border:1px solid #fcd34d; border-radius:10px; padding:12px 16px; margin-bottom:18px; font-size:0.8rem; color:#92400e;">
        <strong>Catatan:</strong> Laporan ini masih bisa diedit karena belum diverifikasi Keuangan. Begitu diverifikasi, isinya tidak bisa diubah lagi.
    </div>

    {{-- Form --}}
    <div style="background:#fff; border-radius:14px; border:1px solid #e2e8f0; overflow:hidden;">
        <div style="padding:18px 24px; border-bottom:1px solid #f1f5f9;">
            <div style="font-size:0.95rem; font-weight:700; color:#0f172a;">Edit Laporan Penggunaan Dana</div>
            <div style="font-size:0.75rem; color:#64748b; margin-top:2px;">Perbarui detail penggunaan dana atau tambah bukti pengeluaran</div>
        </div>

        <form action="{{ route('fund-reports.update', $fundReport) }}" method="POST" enctype="multipart/form-data" style="padding:24px;">
            @csrf @method('PUT')

            {{-- Tanggal Laporan --}}
            <div style="margin-bottom:18px;">
                <label style="display:block; font-size:0.78rem; font-weight:600; color:#374151; margin-bottom:6px;">
                    Tanggal Laporan <span style="color:#ef4444;">*</span>
                </label>
                <input type="date" name="report_date"
                       value="{{ old('report_date', $fundReport->report_date->format('Y-m-d')) }}"
                       required
                       style="width:100%; padding:9px 12px; border:1.5px solid {{ $errors->has('report_date') ? '#ef4444' : '#e2e8f0' }}; border-radius:8px; font-size:0.85rem; color:#0f172a; outline:none; max-width:240px;">
                @error('report_date')
                <div style="color:#ef4444; font-size:0.72rem; margin-top:4px;">{{ $message }}</div>
                @enderror
            </div>

            {{-- Jumlah Digunakan --}}
            <div style="margin-bottom:18px;">
                <label style="display:block; font-size:0.78rem; font-weight:600; color:#374151; margin-bottom:6px;">
                    Total Penggunaan Dana <span style="color:#ef4444;">*</span>
                </label>
                <div style="position:relative; max-width:320px;">
                    <span style="position:absolute; left:12px; top:50%; transform:translateY(-50%); font-size:0.82rem; color:#64748b; font-weight:600;">Rp</span>
                    <input type="text" id="amount-used-display" inputmode="numeric"
                           value="{{ number_format(old('amount_used', $fundReport->amount_used), 0, ',', '.') }}"
                           required
                           placeholder="0"
                           oninput="formatAmountUsed(this)"
                           style="width:100%; padding:9px 12px 9px 36px; border:1.5px solid {{ $errors->has('amount_used') ? '#ef4444' : '#e2e8f0' }}; border-radius:8px; font-size:0.85rem; color:#0f172a; outline:none;">
                    <input type="hidden" name="amount_used" id="amount-used-input" value="{{ old('amount_used', $fundReport->amount_used) }}">
                </div>
                <div style="font-size:0.72rem; color:#94a3b8; margin-top:4px;">Dana diajukan: Rp {{ number_format($fundRequest->amount, 0, ',', '.') }}</div>
                <div id="amount-used-warning" style="display:none; color:#ef4444; font-size:0.72rem; margin-top:4px; font-weight:600;">
                    Total penggunaan tidak boleh melebihi dana yang diajukan (Rp {{ number_format($fundRequest->amount, 0, ',', '.') }})
                </div>
                @error('amount_used')
                <div style="color:#ef4444; font-size:0.72rem; margin-top:4px;">{{ $message }}</div>
                @enderror
            </div>

            {{-- Deskripsi --}}
            <div style="margin-bottom:18px;">
                <label style="display:block; font-size:0.78rem; font-weight:600; color:#374151; margin-bottom:6px;">
                    Keterangan Penggunaan Dana <span style="color:#ef4444;">*</span>
                </label>
                <textarea name="description" rows="5" required maxlength="2000"
                          placeholder="Jelaskan secara rinci penggunaan dana: untuk apa saja, kapan, hasilnya apa..."
                          style="width:100%; padding:10px 12px; border:1.5px solid {{ $errors->has('description') ? '#ef4444' : '#e2e8f0' }}; border-radius:8px; font-size:0.85rem; color:#0f172a; resize:vertical; outline:none; box-sizing:border-box;">{{ old('description', $fundReport->description) }}</textarea>
                @error('description')
                <div style="color:#ef4444; font-size:0.72rem; margin-top:4px;">{{ $message }}</div>
                @enderror
            </div>

            {{-- File Bukti yang sudah ada --}}
            <div style="margin-bottom:18px;">
                <label style="display:block; font-size:0.78rem; font-weight:600; color:#374151; margin-bottom:6px;">
                    Bukti Pengeluaran Tersimpan ({{ $fundReport->files->count() }} file)
                </label>
                @forelse($fundReport->files as $file)
                <div style="display:flex; align-items:center; gap:10px; padding:9px 12px; background:#f8fafc; border:1px solid #e2e8f0; border-radius:8px; margin-bottom:6px;">
                    <svg width="14" height="14" fill="none" stroke="#64748b" stroke-width="2" viewBox="0 0 24 24" style="flex-shrink:0;"><path stroke-linecap="round" stroke-linejoin="round" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                    <span style="flex:1; min-width:0; font-size:0.8rem; color:#0f172a; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">{{ $file->file_name }}</span>
                    <a href="{{ route('fund-reports.files.view', $file) }}" target="_blank"
                       style="padding:5px 12px; background:#eff6ff; color:#1d4ed8; border-radius:6px; font-size:0.73rem; font-weight:600; text-decoration:none; white-space:nowrap;">
                        Lihat
                    </a>
                    <button type="button" onclick="document.getElementById('replace-input-{{ $file->id }}').click()"
                            style="padding:5px 10px; background:#fffbeb; color:#92400e; border:none; border-radius:6px; font-size:0.73rem; font-weight:600; cursor:pointer; white-space:nowrap;">
                        Ganti
                    </button>
                    <button type="submit" form="delete-file-{{ $file->id }}"
                            onclick="return confirm('Hapus file ini?')"
                            style="padding:5px 10px; background:#fee2e2; color:#991b1b; border:none; border-radius:6px; font-size:0.73rem; font-weight:600; cursor:pointer; white-space:nowrap;">
                        Hapus
                    </button>
                </div>
                @empty
                <div style="font-size:0.78rem; color:#94a3b8; padding:8px 0;">Belum ada file bukti.</div>
                @endforelse
            </div>

            {{-- Tambah File Baru --}}
            <div style="margin-bottom:24px;">
                <label style="display:block; font-size:0.78rem; font-weight:600; color:#374151; margin-bottom:6px;">
                    Tambah Bukti Pengeluaran (opsional)
                </label>
                <div style="border:2px dashed #e2e8f0; border-radius:10px; padding:20px; text-align:center; background:#fafafa; cursor:pointer;"
                     onclick="document.getElementById('file-input').click()">
                    <svg width="28" height="28" fill="none" stroke="#94a3b8" stroke-width="1.5" viewBox="0 0 24 24" style="margin:0 auto 8px;">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                    </svg>
                    <div style="font-size:0.82rem; font-weight:600; color:#475569; margin-bottom:4px;">Klik untuk upload file tambahan</div>
                    <div style="font-size:0.72rem; color:#94a3b8;">Nota, kuitansi, struk, atau dokumen pendukung lainnya</div>
                    <div style="font-size:0.7rem; color:#94a3b8; margin-top:4px;">Format: PDF, JPG, PNG, DOC, XLS · Maks. 10 MB per file</div>
                </div>
                <input type="file" id="file-input" name="files[]" multiple
                       accept=".pdf,.jpg,.jpeg,.png,.doc,.docx,.xls,.xlsx"
                       style="display:none;"
                       onchange="showFileList(this)">
                <div id="file-list" style="margin-top:10px;"></div>
                @error('files')
                <div style="color:#ef4444; font-size:0.72rem; margin-top:4px;">{{ $message }}</div>
                @enderror
                @error('files.*')
                <div style="color:#ef4444; font-size:0.72rem; margin-top:4px;">{{ $message }}</div>
                @enderror
            </div>

            {{-- Actions --}}
            <div style="display:flex; gap:10px; justify-content:flex-end;">
                <a href="{{ route('fund-reports.show', $fundReport) }}"
                   style="padding:9px 20px; border:1.5px solid #e2e8f0; border-radius:8px; font-size:0.83rem; font-weight:600; color:#475569; text-decoration:none;">
                    Batal
                </a>
                <button type="submit"
                        style="padding:9px 24px; background:linear-gradient(135deg, #0d2d6b, #1a4fad); color:#fff; border:none; border-radius:8px; font-size:0.83rem; font-weight:600; cursor:pointer;">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>

    {{-- Form hapus & ganti file -- di luar form utama (HTML tidak boleh form bersarang).
         Hapus ditembak lewat atribut form="..." pada tombolnya; Ganti dipicu klik tombol
         yang buka file picker-nya (input di sini), lalu auto-submit begitu file dipilih --
         satu langkah, file lama baru kehapus di server kalau upload barunya valid. --}}
    @foreach($fundReport->files as $file)
    <form id="delete-file-{{ $file->id }}" action="{{ route('fund-reports.files.delete', $file) }}" method="POST" style="display:none;">
        @csrf @method('DELETE')
    </form>
    <form id="replace-file-{{ $file->id }}" action="{{ route('fund-reports.files.replace', $file) }}" method="POST" enctype="multipart/form-data" style="display:none;">
        @csrf
        <input type="file" id="replace-input-{{ $file->id }}" name="file"
               accept=".pdf,.jpg,.jpeg,.png,.doc,.docx,.xls,.xlsx"
               onchange="if (this.files.length) this.form.submit()">
    </form>
    @endforeach
</div>

<script>
(function () {
    const maxAmount = {{ (float) $fundRequest->amount }};

    // Format id-ID: '.' pemisah ribuan, ',' pemisah desimal -- kalau cuma di-strip semua
    // karakter non-digit, koma desimal ikut kebuang dan angkanya jadi salah baca (mis.
    // "77.833,33" jadi 7783333, bukan 77833.33).
    function parseRupiah(str) {
        if (!str) return 0;
        const normalized = String(str).replace(/\./g, '').replace(',', '.').replace(/[^0-9.]/g, '');
        return normalized ? parseFloat(normalized) : 0;
    }

    function formatAmountUsed(el) {
        const value = parseRupiah(el.value);
        el.value = value ? value.toLocaleString('id-ID') : '';
        document.getElementById('amount-used-input').value = value;

        const warning = document.getElementById('amount-used-warning');
        const exceeds = value && value > maxAmount;
        warning.style.display = exceeds ? 'block' : 'none';
        el.style.borderColor = exceeds ? '#ef4444' : '#e2e8f0';
    }
    window.formatAmountUsed = formatAmountUsed;

    document.querySelector('form[action*="fund-reports"]').addEventListener('submit', function (e) {
        const raw = document.getElementById('amount-used-input').value;
        if (raw && Number(raw) > maxAmount) {
            e.preventDefault();
            document.getElementById('amount-used-warning').style.display = 'block';
            document.getElementById('amount-used-display').focus();
        }
    });

    function showFileList(input) {
        const container = document.getElementById('file-list');
        container.innerHTML = '';
        if (!input.files.length) return;
        Array.from(input.files).forEach(function (f) {
            const item = document.createElement('div');
            item.style.cssText = 'display:flex;align-items:center;gap:8px;padding:8px 12px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:7px;margin-top:6px;font-size:0.78rem;color:#475569;';
            item.innerHTML = '<svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>'
                + '<span style="flex:1;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">' + f.name + '</span>'
                + '<span style="color:#94a3b8;flex-shrink:0;">' + (f.size < 1048576 ? Math.round(f.size / 1024) + ' KB' : (f.size / 1048576).toFixed(1) + ' MB') + '</span>';
            container.appendChild(item);
        });
    }
    window.showFileList = showFileList;
})();
</script>

</x-layouts.app>
