<x-layouts.app title="Tambah Pagu Anggaran">
<style>
    .back-link { display:inline-flex; align-items:center; gap:6px; color:#64748b; font-size:0.82rem; text-decoration:none; margin-bottom:20px; }
    .back-link:hover { color:#f97316; }
    .page-title { font-size:1.2rem; font-weight:700; color:#0f172a; margin:0 0 20px; }
    .card { background:#fff; border-radius:14px; box-shadow:0 1px 4px rgba(0,0,0,.07); padding:28px; }
    .form-grid { display:grid; grid-template-columns:1fr 1fr; gap:20px; }
    .form-group { display:flex; flex-direction:column; gap:6px; }
    .form-group.full { grid-column:1/-1; }
    .form-label { font-size:0.8rem; font-weight:600; color:#374151; }
    .form-label .req { color:#ef4444; margin-left:2px; }
    .form-input { padding:9px 13px; border:1.5px solid #e2e8f0; border-radius:9px; font-size:0.865rem; color:#1e293b; background:#fff; outline:none; transition:border-color .15s; width:100%; }
    .form-input:focus { border-color:#f97316; }
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
    .period-info { background:#f8fafc; border:1px solid #e2e8f0; border-radius:9px; padding:12px 16px; font-size:0.845rem; color:#334155; }
    .period-info-label { font-size:0.72rem; color:#94a3b8; font-weight:600; text-transform:uppercase; letter-spacing:.06em; margin-bottom:3px; }
</style>

<a href="{{ route('budget-allocations.index', ['budget_period_id' => $selectedPeriod?->id]) }}" class="back-link">
    <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M19 12H5M12 5l-7 7 7 7"/></svg>
    Kembali ke Pagu Anggaran
</a>

<h1 class="page-title">Tambah Pagu Anggaran</h1>

<div class="card">
    <form method="POST" action="{{ route('budget-allocations.store') }}">
        @csrf

        <p class="section-title">Periode & Departemen</p>
        <div class="form-grid">
            <div class="form-group">
                <label class="form-label">Periode Anggaran <span class="req">*</span></label>
                @if($periods->count() === 1 || $selectedPeriod)
                    <div class="period-info">
                        <div class="period-info-label">Periode Dipilih</div>
                        {{ $selectedPeriod ? "({$selectedPeriod->code}) {$selectedPeriod->name}" : '-' }}
                    </div>
                    <input type="hidden" name="budget_period_id" value="{{ $selectedPeriod?->id }}" id="periodSelect">
                @else
                    <select name="budget_period_id" id="periodSelect" class="form-input" required
                        onchange="loadDepartments(this.value)"
                        onfocus="this.style.borderColor='#f97316'" onblur="this.style.borderColor='#e2e8f0'">
                        <option value="">-- Pilih Periode --</option>
                        @foreach($periods as $period)
                            <option value="{{ $period->id }}" {{ old('budget_period_id') == $period->id ? 'selected' : '' }}>
                                ({{ $period->code }}) {{ $period->name }}
                            </option>
                        @endforeach
                    </select>
                @endif
                @error('budget_period_id') <span class="form-error">{{ $message }}</span> @enderror
            </div>

            <div class="form-group">
                <label class="form-label">Departemen <span class="req">*</span></label>
                <select name="department_id" id="deptSelect" class="form-input" required
                    onfocus="this.style.borderColor='#f97316'" onblur="this.style.borderColor='#e2e8f0'">
                    <option value="">-- Pilih Departemen --</option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept->id }}" {{ old('department_id') == $dept->id ? 'selected' : '' }}>
                            ({{ $dept->code }}) {{ $dept->name }}
                        </option>
                    @endforeach
                </select>
                <span class="form-hint">Hanya departemen yang has_budget = Ya dan belum memiliki pagu di periode ini</span>
                @error('department_id') <span class="form-error">{{ $message }}</span> @enderror
            </div>
        </div>

        <p class="section-title">Alokasi Pagu</p>
        <div class="form-grid">
            <div class="form-group">
                <label class="form-label">Jumlah Pagu (Rp) <span class="req">*</span></label>
                <input type="text" name="amount" id="amountInput" class="form-input"
                    value="{{ old('amount') }}"
                    placeholder="0"
                    inputmode="numeric"
                    oninput="formatRupiah(this)"
                    onfocus="this.style.borderColor='#f97316'" onblur="this.style.borderColor='#e2e8f0'">
                <input type="hidden" name="amount_raw" id="amountRaw">
                @error('amount') <span class="form-error">{{ $message }}</span> @enderror
            </div>
            <div class="form-group">
                <label class="form-label">Persentase (%)</label>
                <input type="number" name="percentage" class="form-input"
                    value="{{ old('percentage') }}"
                    placeholder="0.00" step="0.01" min="0" max="100"
                    onfocus="this.style.borderColor='#f97316'" onblur="this.style.borderColor='#e2e8f0'">
                <span class="form-hint">Opsional. Persentase dari total anggaran organisasi</span>
                @error('percentage') <span class="form-error">{{ $message }}</span> @enderror
            </div>
            <div class="form-group">
                <label class="form-label">Sumber Dana <span class="req">*</span></label>
                <select name="source" class="form-input"
                    onfocus="this.style.borderColor='#f97316'" onblur="this.style.borderColor='#e2e8f0'">
                    <option value="NETT" {{ old('source', 'NETT') === 'NETT' ? 'selected' : '' }}>NETT</option>
                    <option value="DEVIASI" {{ old('source') === 'DEVIASI' ? 'selected' : '' }}>DEVIASI</option>
                </select>
                @error('source') <span class="form-error">{{ $message }}</span> @enderror
            </div>
            <div class="form-group full">
                <label class="form-label">Keterangan</label>
                <textarea name="notes" class="form-input" rows="3" placeholder="Opsional"
                    onfocus="this.style.borderColor='#f97316'" onblur="this.style.borderColor='#e2e8f0'">{{ old('notes') }}</textarea>
                @error('notes') <span class="form-error">{{ $message }}</span> @enderror
            </div>
            <div class="form-group">
                <label class="form-label">Blokir Jika Anggaran Habis</label>
                <div class="toggle-wrap">
                    <label class="toggle">
                        <input type="hidden" name="is_blocking" value="0">
                        <input type="checkbox" name="is_blocking" value="1" {{ old('is_blocking') ? 'checked' : '' }}>
                        <span class="toggle-slider"></span>
                    </label>
                    <span style="font-size:0.845rem;color:#374151;">Aktifkan blokir</span>
                </div>
            </div>
        </div>

        <div class="form-actions">
            <a href="{{ route('budget-allocations.index', ['budget_period_id' => $selectedPeriod?->id]) }}" class="btn-cancel">Batal</a>
            <button type="submit" class="btn-submit">Simpan Pagu</button>
        </div>
    </form>
</div>

<script>
function formatRupiah(input) {
    let raw = input.value.replace(/\D/g, '');
    document.getElementById('amountRaw').value = raw;
    input.value = raw ? parseInt(raw).toLocaleString('id-ID') : '';
}

function loadDepartments(periodId) {
    if (!periodId) return;
    fetch(`{{ route('budget-allocations.departments') }}?budget_period_id=${periodId}`)
        .then(r => r.json())
        .then(depts => {
            const sel = document.getElementById('deptSelect');
            sel.innerHTML = '<option value="">-- Pilih Departemen --</option>';
            depts.forEach(d => {
                sel.innerHTML += `<option value="${d.id}">(${d.code}) ${d.name}</option>`;
            });
        });
}

// Fix amount before submit
document.querySelector('form').addEventListener('submit', function() {
    const raw = document.getElementById('amountRaw').value || document.getElementById('amountInput').value.replace(/\D/g, '');
    document.getElementById('amountInput').value = raw;
});

// Format existing value on load
const amountInput = document.getElementById('amountInput');
if (amountInput.value) formatRupiah(amountInput);
</script>
</x-layouts.app>
