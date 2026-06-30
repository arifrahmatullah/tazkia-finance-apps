<x-layouts.app title="Tambah Karyawan">
    <style>
        .back-link { display:inline-flex; align-items:center; gap:6px; color:#64748b; font-size:0.82rem; text-decoration:none; margin-bottom:20px; }
        .back-link:hover { color:#f97316; }
        .page-title { font-size:1.2rem; font-weight:700; color:#0f172a; margin:0 0 20px; }
        .card { background:#fff; border-radius:14px; box-shadow:0 1px 4px rgba(0,0,0,.07); padding:28px; }
        .form-actions { display:flex; gap:12px; justify-content:flex-end; margin-top:28px; padding-top:20px; border-top:1px solid #f1f5f9; }
        .btn-submit { padding:10px 24px; border-radius:9px; border:none; cursor:pointer; font-size:0.855rem; font-weight:600; background:linear-gradient(135deg,#f97316,#ea580c); color:#fff; transition:all .15s; }
        .btn-submit:hover { transform:translateY(-1px); box-shadow:0 4px 12px rgba(249,115,22,.35); }
        .btn-cancel { padding:10px 20px; border-radius:9px; border:1.5px solid #e2e8f0; background:#fff; color:#64748b; font-size:0.855rem; font-weight:500; text-decoration:none; cursor:pointer; transition:all .15s; }
        .btn-cancel:hover { background:#f8fafc; border-color:#cbd5e1; }
    </style>

    <a href="{{ route('employees.index') }}" class="back-link">
        <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M19 12H5M12 5l-7 7 7 7"/></svg>
        Kembali ke Daftar Karyawan
    </a>

    <h1 class="page-title">Tambah Karyawan Baru</h1>

    <div class="card">
        <form method="POST" action="{{ route('employees.store') }}">
            @csrf
            @include('employees._form')
            <div class="form-actions">
                <a href="{{ route('employees.index') }}" class="btn-cancel">Batal</a>
                <button type="submit" class="btn-submit">Simpan Karyawan</button>
            </div>
        </form>
    </div>
</x-layouts.app>
