<x-layouts.app title="Pagu Anggaran">
<style>
    .page-header { display:flex; align-items:center; justify-content:space-between; margin-bottom:20px; }
    .page-title { font-size:1.1rem; font-weight:700; color:#0f172a; margin:0 0 3px; }
    .page-sub { font-size:0.78rem; color:#94a3b8; margin:0; }
    .btn-primary { display:inline-flex; align-items:center; gap:7px; padding:9px 16px; border-radius:9px; background:linear-gradient(135deg,#ea580c,#f97316); color:#fff; font-size:0.83rem; font-weight:600; text-decoration:none; box-shadow:0 3px 10px rgba(234,88,12,.3); transition:all .15s; }
    .btn-primary:hover { box-shadow:0 6px 16px rgba(234,88,12,.4); transform:translateY(-1px); }
    /* Period selector */
    .period-bar { background:#fff; border-radius:12px; border:1px solid #f1f5f9; box-shadow:0 1px 4px rgba(0,0,0,.04); padding:16px 20px; margin-bottom:16px; display:flex; align-items:center; gap:12px; flex-wrap:wrap; }
    .period-label { font-size:0.8rem; font-weight:600; color:#64748b; white-space:nowrap; }
    .period-select { flex:1; min-width:200px; padding:8px 12px; border:1.5px solid #e2e8f0; border-radius:9px; font-size:0.845rem; color:#1e293b; background:#fff; outline:none; }
    .period-select:focus { border-color:#f97316; }
    .btn-choose { padding:8px 18px; border-radius:9px; border:none; cursor:pointer; font-size:0.845rem; font-weight:600; background:#f1f5f9; color:#475569; }
    .btn-choose:hover { background:#e2e8f0; }
    .total-box { margin-left:auto; text-align:right; }
    .total-label { font-size:0.72rem; color:#94a3b8; text-transform:uppercase; letter-spacing:.06em; }
    .total-amount { font-size:1.1rem; font-weight:700; color:#0f172a; }
    /* Search bar */
    .search-bar { display:flex; gap:10px; margin-bottom:16px; }
    .search-wrap { position:relative; flex:1; }
    .search-wrap svg { position:absolute; left:11px; top:50%; transform:translateY(-50%); pointer-events:none; }
    .search-input { width:100%; padding:8px 12px 8px 34px; border:1.5px solid #e2e8f0; border-radius:9px; font-size:0.845rem; color:#1e293b; outline:none; }
    .search-input:focus { border-color:#f97316; }
    .btn-search { padding:8px 18px; border-radius:9px; border:none; cursor:pointer; font-size:0.845rem; font-weight:600; background:linear-gradient(135deg,#f97316,#ea580c); color:#fff; }
    /* Card table */
    .card { background:#fff; border-radius:14px; border:1px solid #f1f5f9; box-shadow:0 1px 4px rgba(0,0,0,.04); overflow:hidden; }
    .card-header { padding:14px 20px; border-bottom:1px solid #f1f5f9; display:flex; align-items:center; justify-content:space-between; }
    .card-title { font-size:0.875rem; font-weight:700; color:#0f172a; }
    .count-badge { background:#f1f5f9; color:#64748b; font-size:0.72rem; font-weight:600; padding:3px 9px; border-radius:99px; }
    table { width:100%; border-collapse:collapse; }
    th { padding:11px 16px; text-align:left; font-size:0.72rem; font-weight:600; color:#64748b; text-transform:uppercase; letter-spacing:.06em; border-bottom:1px solid #f1f5f9; background:#fafbfc; white-space:nowrap; }
    th.num { text-align:right; }
    td { padding:13px 16px; font-size:0.845rem; color:#334155; border-bottom:1px solid #f8fafc; vertical-align:middle; }
    td.num { text-align:right; font-family:monospace; }
    tr:last-child td { border-bottom:none; }
    tr:hover td { background:#fafbff; }
    tfoot td { background:#f8fafc; font-weight:700; border-top:2px solid #f1f5f9; }
    .code-pill { font-family:monospace; font-size:0.8rem; font-weight:600; color:#475569; background:#f1f5f9; padding:3px 8px; border-radius:5px; }
    .badge { display:inline-flex; align-items:center; padding:3px 9px; border-radius:99px; font-size:0.72rem; font-weight:600; }
    .badge-green { background:#dcfce7; color:#16a34a; }
    .badge-red { background:#fee2e2; color:#dc2626; }
    .badge-blue { background:#dbeafe; color:#2563eb; }
    .badge-yellow { background:#fef9c3; color:#a16207; }
    .badge-gray { background:#f1f5f9; color:#64748b; }
    .action-btn { display:inline-flex; align-items:center; gap:4px; padding:5px 11px; border-radius:7px; font-size:0.78rem; font-weight:500; text-decoration:none; cursor:pointer; border:none; transition:all .15s; }
    .btn-edit { background:#eff6ff; color:#2563eb; }
    .btn-edit:hover { background:#dbeafe; }
    .btn-del { background:#fff1f2; color:#e11d48; }
    .btn-del:hover { background:#ffe4e6; }
    .alert-success { display:flex; align-items:center; gap:10px; padding:12px 16px; background:#f0fdf4; border:1px solid #bbf7d0; border-radius:10px; margin-bottom:18px; font-size:0.83rem; color:#15803d; }
    .empty-state { padding:56px 20px; text-align:center; color:#94a3b8; }
    .no-period { padding:40px; text-align:center; }
</style>

<div class="page-header">
    <div>
        <h2 class="page-title">Pagu Anggaran</h2>
        <p class="page-sub">Alokasi anggaran per departemen dalam satu periode</p>
    </div>
    @if($selectedPeriod)
    <a href="{{ route('budget-allocations.create', ['budget_period_id' => $selectedPeriod->id]) }}" class="btn-primary">
        <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg>
        Tambah Pagu
    </a>
    @endif
</div>

@if(session('success'))
<div class="alert-success">
    <svg width="16" height="16" fill="#16a34a" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
    {{ session('success') }}
</div>
@endif

{{-- Period selector --}}
<form method="GET" action="{{ route('budget-allocations.index') }}">
    <div class="period-bar">
        <span class="period-label">Periode:</span>
        <select name="budget_period_id" class="period-select"
            onfocus="this.style.borderColor='#f97316'" onblur="this.style.borderColor='#e2e8f0'">
            @forelse($periods as $period)
                <option value="{{ $period->id }}" {{ $selectedPeriod?->id == $period->id ? 'selected' : '' }}>
                    ({{ $period->code }}) {{ $period->name }}
                    {{ $period->is_active ? '— Aktif' : '' }}
                </option>
            @empty
                <option value="">Tidak ada periode</option>
            @endforelse
        </select>
        <button type="submit" class="btn-choose">Pilih</button>

        @if($selectedPeriod)
        <div class="total-box">
            <div class="total-label">Total Pagu</div>
            <div class="total-amount">Rp {{ number_format($totalAmount, 0, ',', '.') }}</div>
        </div>
        @endif
    </div>

    {{-- Search --}}
    @if($selectedPeriod)
    <div class="search-bar">
        <div class="search-wrap">
            <svg width="15" height="15" fill="none" stroke="#94a3b8" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama atau kode departemen..." class="search-input"
                onfocus="this.style.borderColor='#f97316'" onblur="this.style.borderColor='#e2e8f0'">
            <input type="hidden" name="budget_period_id" value="{{ $selectedPeriod->id }}">
        </div>
        <button type="submit" class="btn-search">Cari</button>
        @if(request('search'))
            <a href="{{ route('budget-allocations.index', ['budget_period_id' => $selectedPeriod->id]) }}"
                style="padding:8px 14px; border-radius:9px; border:1.5px solid #e2e8f0; font-size:0.845rem; color:#64748b; text-decoration:none; background:#fff;">
                Reset
            </a>
        @endif
    </div>
    @endif
</form>

@if(!$selectedPeriod)
<div class="card">
    <div class="no-period">
        <svg width="40" height="40" fill="none" stroke="#cbd5e1" stroke-width="1.5" viewBox="0 0 24 24" style="margin:0 auto 10px;display:block;"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
        <p style="font-size:0.9rem;color:#94a3b8;margin:0;">Tidak ada periode anggaran. Buat periode terlebih dahulu di menu <a href="{{ route('budget-periods.index') }}" style="color:#f97316;">Periode Anggaran</a>.</p>
    </div>
</div>
@else
<div class="card">
    <div class="card-header">
        <div>
            <span class="card-title">Alokasi Pagu</span>
            @if($selectedPeriod->is_active)
                <span class="badge badge-green" style="margin-left:8px;">Periode Aktif</span>
            @else
                <span class="badge badge-gray" style="margin-left:8px;">Periode Tidak Aktif</span>
            @endif
        </div>
        <span class="count-badge">{{ $allocations->count() }} departemen</span>
    </div>

    @if($allocations->isEmpty())
        <div class="empty-state">
            <svg width="40" height="40" fill="none" stroke="#cbd5e1" stroke-width="1.5" viewBox="0 0 24 24" style="margin:0 auto 10px;display:block;"><path d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            <p style="font-size:0.83rem;margin:0;">Belum ada pagu untuk periode ini. Klik "Tambah Pagu" untuk mulai.</p>
        </div>
    @else
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Departemen</th>
                <th>Kode</th>
                <th>Sumber</th>
                <th class="num">Pagu (Rp)</th>
                <th class="num">Persentase</th>
                <th>Blokir</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @foreach($allocations as $i => $alloc)
            <tr>
                <td style="color:#94a3b8;font-size:0.78rem;">{{ $i + 1 }}</td>
                <td style="font-weight:600;">{{ $alloc->department->name }}</td>
                <td><span class="code-pill">{{ $alloc->department->code }}</span></td>
                <td>
                    <span class="badge {{ $alloc->source === 'NETT' ? 'badge-blue' : 'badge-yellow' }}">
                        {{ $alloc->source }}
                    </span>
                </td>
                <td class="num" style="font-weight:600;color:#0f172a;">
                    {{ number_format($alloc->amount, 0, ',', '.') }}
                </td>
                <td class="num" style="color:#64748b;">
                    {{ $alloc->percentage !== null ? number_format($alloc->percentage, 2, ',', '.') . '%' : '—' }}
                </td>
                <td>
                    @if($alloc->is_blocking)
                        <span class="badge badge-red">Ya</span>
                    @else
                        <span style="color:#cbd5e1;font-size:0.8rem;">—</span>
                    @endif
                </td>
                <td>
                    @if($alloc->is_active)
                        <span class="badge badge-green">Aktif</span>
                    @else
                        <span class="badge badge-gray">Nonaktif</span>
                    @endif
                </td>
                <td>
                    <div style="display:flex;gap:6px;">
                        <a href="{{ route('budget-allocations.edit', $alloc) }}" class="action-btn btn-edit">Edit</a>
                        <form id="del-alloc-{{ $alloc->id }}" method="POST" action="{{ route('budget-allocations.destroy', $alloc) }}">
                            @csrf @method('DELETE')
                        </form>
                        <button type="button" class="action-btn btn-del"
                            onclick="confirmDelete('del-alloc-{{ $alloc->id }}', '{{ addslashes($alloc->department->name) }}')">
                            Hapus
                        </button>
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="4" style="font-size:0.8rem;color:#64748b;">Total</td>
                <td class="num" style="color:#0f172a;">{{ number_format($totalAmount, 0, ',', '.') }}</td>
                <td colspan="4"></td>
            </tr>
        </tfoot>
    </table>
    @endif
</div>
@endif
</x-layouts.app>
