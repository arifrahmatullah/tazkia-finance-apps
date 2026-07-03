<x-layouts.app title="Buat Pengajuan Dana">

<a href="{{ route('fund-requests.index') }}" class="inline-flex items-center gap-1.5 text-sm text-slate-500 hover:text-orange-500 mb-5 no-underline">
    <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M19 12H5M12 5l-7 7 7 7"/></svg>
    Kembali ke Daftar Pengajuan
</a>
<h1 class="text-xl font-bold text-slate-900 mb-5">Buat Pengajuan Dana</h1>

@if($errors->has('submit'))
<div class="flex items-start gap-2.5 px-4 py-3 bg-red-50 border border-red-200 rounded-xl mb-4 text-sm text-red-600">
    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" class="shrink-0 mt-px"><circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01"/></svg>
    {{ $errors->first('submit') }}
</div>
@endif

<div class="bg-white rounded-xl shadow-sm p-6">
    <form method="POST" action="{{ route('fund-requests.store') }}">
    @csrf

    {{-- Info pengaju --}}
    <div class="text-[11px] font-bold text-slate-400 uppercase tracking-widest mb-3.5 pb-2 border-b border-slate-100">Informasi Pengaju</div>
    <div class="flex gap-4 items-center px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl mb-5">
        <div>
            <div class="text-[11px] text-slate-400 mb-0.5">Nama Karyawan</div>
            <div class="text-sm font-semibold text-slate-900">{{ $employee->name }}</div>
        </div>
        <div class="w-px h-9 bg-slate-200"></div>
        <div>
            <div class="text-[11px] text-slate-400 mb-0.5">NIP</div>
            <div class="text-sm font-semibold text-slate-900 font-mono">{{ $employee->employee_id }}</div>
        </div>
        <div class="w-px h-9 bg-slate-200"></div>
        <div>
            <div class="text-[11px] text-slate-400 mb-0.5">Jabatan Aktif</div>
            <div class="text-sm font-semibold text-slate-900">
                @if($activePosition)
                    {{ $activePosition->name }}
                @else
                    <span class="text-red-500 text-sm">Belum ada jabatan aktif</span>
                @endif
            </div>
        </div>
    </div>

    @unless($activePosition)
    <div class="flex items-start gap-2.5 px-4 py-3 bg-red-50 border border-red-200 rounded-xl mb-4 text-sm text-red-600">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" class="shrink-0"><circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01"/></svg>
        Anda belum memiliki jabatan aktif. Tidak dapat membuat pengajuan. Hubungi HRD untuk mengatur jabatan Anda.
    </div>
    @endunless

    {{-- Detail pengajuan --}}
    <div class="text-[11px] font-bold text-slate-400 uppercase tracking-widest mb-3.5 pb-2 border-b border-slate-100 mt-2">Detail Pengajuan</div>
    <div class="grid grid-cols-2 gap-4">
        <div class="flex flex-col gap-1.5">
            <label class="text-xs font-semibold text-slate-600">Organisasi <span class="text-red-500 ml-0.5">*</span></label>
            <select name="organization_id" id="org-select"
                class="w-full px-3 py-2.5 border border-slate-200 rounded-xl text-sm text-slate-800 bg-white outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100 transition-colors {{ $errors->has('organization_id') ? 'border-red-400' : '' }}"
                onchange="loadDepartments(this.value)">
                <option value="">-- Pilih Organisasi --</option>
                @foreach($organizations as $org)
                    <option value="{{ $org->id }}" {{ old('organization_id', $selectedOrgId) == $org->id ? 'selected' : '' }}>{{ $org->name }}</option>
                @endforeach
            </select>
            @error('organization_id')<div class="text-xs text-red-500 mt-0.5">{{ $message }}</div>@enderror
        </div>

        <div class="flex flex-col gap-1.5">
            <label class="text-xs font-semibold text-slate-600">Departemen <span class="text-red-500 ml-0.5">*</span></label>
            <select name="department_id" id="dept-select"
                class="w-full px-3 py-2.5 border border-slate-200 rounded-xl text-sm text-slate-800 bg-white outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100 transition-colors {{ $errors->has('department_id') ? 'border-red-400' : '' }}">
                <option value="">-- Pilih Departemen --</option>
                @foreach($departments as $d)
                    <option value="{{ $d->id }}" {{ old('department_id') == $d->id ? 'selected' : '' }}>{{ $d->name }}</option>
                @endforeach
            </select>
            @error('department_id')<div class="text-xs text-red-500 mt-0.5">{{ $message }}</div>@enderror
        </div>

        <div class="flex flex-col gap-1.5">
            <label class="text-xs font-semibold text-slate-600">Periode Anggaran</label>
            <select name="budget_period_id" id="period-select"
                class="w-full px-3 py-2.5 border border-slate-200 rounded-xl text-sm text-slate-800 bg-white outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100 transition-colors {{ $errors->has('budget_period_id') ? 'border-red-400' : '' }}">
                <option value="">-- Tanpa Periode Anggaran --</option>
                @foreach($budgetPeriods as $bp)
                    <option value="{{ $bp->id }}" {{ old('budget_period_id') == $bp->id ? 'selected' : '' }}>{{ $bp->name }}</option>
                @endforeach
            </select>
            @error('budget_period_id')<div class="text-xs text-red-500 mt-0.5">{{ $message }}</div>@enderror
        </div>

        <div class="flex flex-col gap-1.5">
            <label class="text-xs font-semibold text-slate-600">Jumlah Dana (Rp) <span class="text-red-500 ml-0.5">*</span></label>
            <div class="flex items-center">
                <span class="px-3 py-2.5 bg-slate-100 border border-slate-200 border-r-0 rounded-l-xl text-sm text-slate-500 font-medium whitespace-nowrap">Rp</span>
                <input type="number" name="amount" value="{{ old('amount') }}" min="1000" step="1000"
                    class="w-full px-3 py-2.5 border border-slate-200 rounded-r-xl rounded-l-none text-sm text-slate-800 bg-white outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100 transition-colors {{ $errors->has('amount') ? 'border-red-400' : '' }}"
                    placeholder="0">
            </div>
            @error('amount')<div class="text-xs text-red-500 mt-0.5">{{ $message }}</div>@enderror
        </div>

        <div class="flex flex-col gap-1.5 col-span-2">
            <label class="text-xs font-semibold text-slate-600">Judul Pengajuan <span class="text-red-500 ml-0.5">*</span></label>
            <input type="text" name="title" value="{{ old('title') }}" maxlength="200"
                class="w-full px-3 py-2.5 border border-slate-200 rounded-xl text-sm text-slate-800 bg-white outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100 transition-colors {{ $errors->has('title') ? 'border-red-400' : '' }}"
                placeholder="Contoh: Pembelian ATK Bulan Juli 2026">
            @error('title')<div class="text-xs text-red-500 mt-0.5">{{ $message }}</div>@enderror
        </div>

        <div class="flex flex-col gap-1.5 col-span-2">
            <label class="text-xs font-semibold text-slate-600">Tujuan / Keterangan</label>
            <textarea name="purpose" rows="3" maxlength="1000"
                class="w-full px-3 py-2.5 border border-slate-200 rounded-xl text-sm text-slate-800 bg-white outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100 transition-colors resize-y {{ $errors->has('purpose') ? 'border-red-400' : '' }}"
                placeholder="Jelaskan tujuan dan kebutuhan pengajuan dana ini...">{{ old('purpose') }}</textarea>
            @error('purpose')<div class="text-xs text-red-500 mt-0.5">{{ $message }}</div>@enderror
        </div>
    </div>

    <div class="flex gap-3 justify-end mt-6 pt-5 border-t border-slate-100">
        <a href="{{ route('fund-requests.index') }}" class="px-5 py-2.5 rounded-xl border border-slate-200 bg-white text-slate-600 text-sm font-medium no-underline inline-flex items-center">Batal</a>
        @if($activePosition)
            <button type="submit" class="px-6 py-2.5 rounded-xl text-sm font-semibold bg-gradient-to-br from-orange-400 to-orange-500 text-white border-0 cursor-pointer hover:-translate-y-px transition-all">Simpan sebagai Draft</button>
        @endif
    </div>
    </form>
</div>

<script>
function loadDepartments(orgId) {
    const deptSel   = document.getElementById('dept-select');
    const periodSel = document.getElementById('period-select');

    deptSel.innerHTML   = '<option value="">Memuat...</option>';
    periodSel.innerHTML = '<option value="">-- Tanpa Periode Anggaran --</option>';

    if (!orgId) {
        deptSel.innerHTML = '<option value="">-- Pilih Departemen --</option>';
        return;
    }

    fetch(`{{ route('fund-requests.deps') }}?organization_id=${orgId}`)
        .then(r => r.json())
        .then(data => {
            deptSel.innerHTML = '<option value="">-- Pilih Departemen --</option>';
            data.departments.forEach(d => {
                deptSel.innerHTML += `<option value="${d.id}">${d.name}</option>`;
            });
            periodSel.innerHTML = '<option value="">-- Tanpa Periode Anggaran --</option>';
            data.budget_periods.forEach(bp => {
                periodSel.innerHTML += `<option value="${bp.id}">${bp.name}</option>`;
            });
        })
        .catch(() => {
            deptSel.innerHTML = '<option value="">-- Pilih Departemen --</option>';
        });
}
</script>
</x-layouts.app>
