<x-layouts.app title="Edit Pagu Anggaran">
<style>
    .back-link { display:inline-flex; align-items:center; gap:6px; color:#64748b; font-size:0.82rem; text-decoration:none; margin-bottom:20px; }
    .back-link:hover { color:#f97316; }
    .page-title { font-size:1.2rem; font-weight:700; color:#0f172a; margin:0 0 4px; }
    .page-sub { font-size:0.82rem; color:#94a3b8; margin:0 0 20px; }
    .card { background:#fff; border-radius:14px; box-shadow:0 1px 4px rgba(0,0,0,.07); padding:28px; }
    .form-grid { display:grid; grid-template-columns:1fr 1fr; gap:20px; }
    .form-group { display:flex; flex-direction:column; gap:6px; }
    .form-group.full { grid-column:1/-1; }
    .form-label { font-size:0.8rem; font-weight:600; color:#374151; }
    .form-label .req { color:#ef4444; margin-left:2px; }
    .form-input { padding:9px 13px; border:1.5px solid #e2e8f0; border-radius:9px; font-size:0.865rem; color:#1e293b; background:#fff; outline:none; transition:border-color .15s; width:100%; }
    .form-input:focus { border-color:#f97316; }
    .form-input[disabled] { background:#f8fafc; color:#64748b; cursor:default; }
    .form-error { font-size:0.77rem; color:#dc2626; margin-top:2px; }
    .form-hint { font-size:0.75rem; color:#94a3b8; margin-top:2px; }
    .section-title { font-size:0.78rem; font-weight:700; color:#94a3b8; text-transform:uppercase; letter-spacing:.08em; margin:24px 0 14px; padding-bottom:6px; border-bottom:1px solid #f1f5f9; }
    .toggle-wrap { display:flex; align-items:center; gap:12px; padding:10px 14px; border:1.5px solid #e2e8f0; border-radius:9px; width:fit-content; }
    .toggle { position:relative; width:42px; height:22px; }
    .toggle input { opacity:0; width:0; height:0; }
    .toggle-slider { position:absolute; inset:0; background:#e2e8f0; border-radius:99px; cursor:pointer; transition:.2s; }
    .toggle-slider::before { content:''; position:absolute; width:16px; height:16px; left:3px; top:3px; background:#fff; border-radius:50%; transition:.2s; }
    .toggle input:checked + .toggle-slider { background:#f97316; }
    .toggle input:checked + .toggle-slider::before { transform:translateX(20px); }
    .form-actions { display:flex; gap:12px; justify-content:flex-end; margin-top:28px; padding-top:20px; border-top:1px solid #f1f5f9; }
    .btn-submit { padding:10px 24px; border-radius:9px; border:none; cursor:pointer; font-size:0.855rem; font-weight:600; background:linear-gradient(135deg,#f97316,#ea580c); color:#fff; transition:all .15s; }
    .btn-submit:hover { transform:translateY(-1px); box-shadow:0 4px 12px rgba(249,115,22,.35); }
    .btn-cancel { padding:10px 20px; border-radius:9px; border:1.5px solid #e2e8f0; background:#fff; color:#64748b; font-size:0.855rem; font-weight:500; text-decoration:none; }
    .btn-cancel:hover { background:#f8fafc; }
    .info-chips { display:flex; gap:8px; flex-wrap:wrap; margin-bottom:6px; }
    .chip { display:inline-flex; align-items:center; gap:5px; padding:4px 10px; border-radius:7px; font-size:0.78rem; font-weight:500; background:#f1f5f9; color:#475569; }
</style>

<a href="{{ route('budget-allocations.index', ['budget_period_id' => $budgetAllocation->budget_period_id]) }}" class="back-link">
    <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M19 12H5M12 5l-7 7 7 7"/></svg>
    Kembali ke Pagu Anggaran
</a>

<h1 class="page-title">Edit Pagu Anggaran</h1>
<div class="info-chips">
    <span class="chip">📅 {{ $budgetAllocation->budgetPeriod->name }}</span>
    <span class="chip">🏢 {{ $budgetAllocation->department->name }}</span>
</div>

<div class="card" style="margin-top:16px;">
    <form method="POST" action="{{ route('budget-allocations.update', $budgetAllocation) }}">
        @csrf @method('PUT')

        <p class="section-title">Alokasi Pagu</p>
        <div class="form-grid">
            <div class="form-group">
                <label class="form-label">Jumlah Pagu (Rp) <span class="req">*</span></label>
                <input type="text" name="amount" id="amountInput" class="form-input"
                    value="{{ old('amount', number_format($budgetAllocation->amount, 0, ',', '.')) }}"
                    placeholder="0"
                    inputmode="numeric"
                    oninput="formatRupiah(this)"
                    onfocus="this.style.borderColor='#f97316'" onblur="this.style.borderColor='#e2e8f0'">
                @error('amount') <span class="form-error">{{ $message }}</span> @enderror
            </div>
            <div class="form-group">
                <label class="form-label">Persentase (%)</label>
                <input type="number" name="percentage" class="form-input"
                    value="{{ old('percentage', $budgetAllocation->percentage) }}"
                    placeholder="0.00" step="0.01" min="0" max="100"
                    onfocus="this.style.borderColor='#f97316'" onblur="this.style.borderColor='#e2e8f0'">
                @error('percentage') <span class="form-error">{{ $message }}</span> @enderror
            </div>
            <div class="form-group">
                <label class="form-label">Sumber Dana <span class="req">*</span></label>
                <select name="source" class="form-input"
                    onfocus="this.style.borderColor='#f97316'" onblur="this.style.borderColor='#e2e8f0'">
                    <option value="NETT" {{ old('source', $budgetAllocation->source) === 'NETT' ? 'selected' : '' }}>NETT</option>
                    <option value="DEVIASI" {{ old('source', $budgetAllocation->source) === 'DEVIASI' ? 'selected' : '' }}>DEVIASI</option>
                </select>
                @error('source') <span class="form-error">{{ $message }}</span> @enderror
            </div>
            <div class="form-group full">
                <label class="form-label">Keterangan</label>
                <textarea name="notes" class="form-input" rows="3"
                    onfocus="this.style.borderColor='#f97316'" onblur="this.style.borderColor='#e2e8f0'">{{ old('notes', $budgetAllocation->notes) }}</textarea>
                @error('notes') <span class="form-error">{{ $message }}</span> @enderror
            </div>
            <div class="form-group">
                <label class="form-label">Blokir Jika Anggaran Habis</label>
                <div class="toggle-wrap">
                    <label class="toggle">
                        <input type="hidden" name="is_blocking" value="0">
                        <input type="checkbox" name="is_blocking" value="1" {{ old('is_blocking', $budgetAllocation->is_blocking) ? 'checked' : '' }}>
                        <span class="toggle-slider"></span>
                    </label>
                    <span style="font-size:0.845rem;color:#374151;">Aktifkan blokir</span>
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Status</label>
                <div class="toggle-wrap">
                    <label class="toggle">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" name="is_active" value="1" {{ old('is_active', $budgetAllocation->is_active) ? 'checked' : '' }}>
                        <span class="toggle-slider"></span>
                    </label>
                    <span style="font-size:0.845rem;color:#374151;">Pagu Aktif</span>
                </div>
            </div>
        </div>

        <div class="form-actions">
            <a href="{{ route('budget-allocations.index', ['budget_period_id' => $budgetAllocation->budget_period_id]) }}" class="btn-cancel">Batal</a>
            <button type="submit" class="btn-submit">Simpan Perubahan</button>
        </div>
    </form>
</div>

<script>
function formatRupiah(input) {
    let raw = input.value.replace(/\D/g, '');
    input.value = raw ? parseInt(raw).toLocaleString('id-ID') : '';
}

document.querySelector('form').addEventListener('submit', function() {
    const input = document.getElementById('amountInput');
    input.value = input.value.replace(/\./g, '').replace(/,/g, '.');
});
</script>
</x-layouts.app>
