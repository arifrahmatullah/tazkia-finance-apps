<x-layouts.app title="Edit Departemen" breadcrumb="Master Data / Departemen / Edit">

    <div style="max-width:640px;">

        <a href="{{ route('departments.index') }}" style="display:inline-flex; align-items:center; gap:6px; font-size:0.8rem; color:#64748b; text-decoration:none; margin-bottom:18px;"
           onmouseover="this.style.color='#1e293b';" onmouseout="this.style.color='#64748b';">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
            </svg>
            Kembali ke daftar
        </a>

        <div style="background:#fff; border-radius:14px; border:1px solid #f1f5f9; box-shadow:0 1px 4px rgba(0,0,0,0.04); overflow:hidden;">

            <div style="padding:20px 24px; border-bottom:1px solid #f1f5f9; background:linear-gradient(135deg,#040f2e,#0d2d6b); display:flex; align-items:center; gap:12px;">
                <div style="width:36px; height:36px; border-radius:9px; background:rgba(249,115,22,0.2); border:1px solid rgba(249,115,22,0.3); display:flex; align-items:center; justify-content:center;">
                    <svg width="17" height="17" fill="none" stroke="#fb923c" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                </div>
                <div>
                    <div style="color:#fff; font-weight:600; font-size:0.95rem;">Edit: {{ $department->name }}</div>
                    <div style="color:#93c5fd; font-size:0.72rem; margin-top:1px;">Perbarui data departemen</div>
                </div>
            </div>

            <form method="POST" action="{{ route('departments.update', $department) }}" style="padding:24px;">
                @csrf @method('PUT')

                @include('departments._form', ['organizations' => $organizations, 'department' => $department])

                {{-- Status aktif --}}
                <div style="margin-top:12px;">
                    <label style="display:flex; align-items:center; gap:10px; cursor:pointer;">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" name="is_active" value="1"
                            {{ old('is_active', $department->is_active) ? 'checked' : '' }}
                            style="width:16px; height:16px; accent-color:#0d2d6b; cursor:pointer;">
                        <span style="font-size:0.85rem; font-weight:500; color:#374151;">Departemen Aktif</span>
                    </label>
                </div>

                <div style="display:flex; gap:10px; margin-top:24px; padding-top:20px; border-top:1px solid #f1f5f9;">
                    <button type="submit" style="padding:10px 22px; border-radius:9px; border:none; cursor:pointer; background:linear-gradient(135deg,#ea580c,#f97316); color:#fff; font-size:0.85rem; font-weight:600; box-shadow:0 3px 10px rgba(234,88,12,0.3); font-family:'Inter',sans-serif;">
                        Simpan Perubahan
                    </button>
                    <a href="{{ route('departments.index') }}" style="padding:10px 20px; border-radius:9px; font-size:0.85rem; font-weight:500; color:#64748b; background:#f8fafc; border:1px solid #e2e8f0; text-decoration:none;">
                        Batal
                    </a>
                </div>
            </form>
        </div>
    </div>

</x-layouts.app>
