<x-layouts.app title="Edit Rantai Approval">

<a href="{{ route('approval-settings.index') }}" class="inline-flex items-center gap-1.5 text-sm text-slate-500 hover:text-orange-500 mb-5 no-underline">
    <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M19 12H5M12 5l-7 7 7 7"/></svg>
    Kembali ke Setting Approval
</a>

@php $first = $chain->first(); @endphp
<h1 class="text-xl font-bold text-slate-900 mb-0.5">Edit Rantai Approval</h1>
<p class="text-sm text-slate-400 mb-5">{{ $first->organization->name }} — {{ $first->requesterPosition->name }}</p>

<div class="bg-white rounded-xl shadow-sm p-6">
    <form method="POST" action="{{ route('approval-settings.update-chain') }}" id="chain-form">
    @csrf
    <input type="hidden" name="organization_id" value="{{ $first->organization_id }}">
    <input type="hidden" name="requester_position_id" value="{{ $first->requester_position_id }}">

    {{-- Info --}}
    <div class="flex items-center gap-3 px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl mb-6">
        <div class="flex flex-col gap-0.5">
            <span class="text-[11px] text-slate-400 uppercase tracking-wide font-semibold">Organisasi</span>
            <span class="text-sm font-semibold text-slate-700">{{ $first->organization->name }}</span>
        </div>
        <div class="w-px h-8 bg-slate-200 mx-2"></div>
        <div class="flex flex-col gap-0.5">
            <span class="text-[11px] text-slate-400 uppercase tracking-wide font-semibold">Jabatan Pengaju</span>
            <span class="text-sm font-semibold text-slate-700">{{ $first->requesterPosition->name }}</span>
        </div>
    </div>

    {{-- Rantai Approval --}}
    <div class="text-[11px] font-bold text-slate-400 uppercase tracking-widest mb-3 pb-2 border-b border-slate-100">Rantai Approval</div>

    <table class="w-full border-collapse" id="chain-table">
        <thead>
            <tr class="bg-slate-50 border border-slate-100">
                <th class="px-4 py-2.5 text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wide w-[70px]">Level</th>
                <th class="px-4 py-2.5 text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wide">Disetujui Oleh (Jabatan)</th>
                <th class="px-4 py-2.5 w-[60px]"></th>
            </tr>
        </thead>
        <tbody id="chain-body">
            @foreach($chain as $i => $setting)
            <tr class="border-b border-slate-100 chain-row">
                <td class="px-4 py-3 align-middle">
                    <span class="step-badge inline-flex items-center justify-center w-7 h-7 rounded-full bg-orange-500 text-white text-xs font-bold">{{ $loop->iteration }}</span>
                </td>
                <td class="px-4 py-3 align-middle">
                    <select name="steps[{{ $i }}][approver_position_id]" class="w-full px-3 py-2 border border-slate-200 rounded-xl text-sm text-slate-800 bg-white outline-none focus:border-orange-400 transition-colors" required>
                        <option value="">-- Pilih Jabatan Approver --</option>
                        @foreach($positions as $pos)
                            <option value="{{ $pos->id }}" {{ $setting->approver_position_id == $pos->id ? 'selected' : '' }}>{{ $pos->name }}</option>
                        @endforeach
                    </select>
                </td>
                <td class="px-4 py-3 align-middle text-center">
                    <button type="button" onclick="removeRow(this)" class="text-slate-300 hover:text-red-400 transition-colors p-1 border-0 bg-transparent cursor-pointer" title="Hapus baris">
                        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M18 6L6 18M6 6l12 12"/></svg>
                    </button>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <button type="button" onclick="addRow()"
        class="mt-3 inline-flex items-center gap-1.5 px-4 py-2 rounded-xl border border-dashed border-slate-200 bg-slate-50 text-slate-500 text-sm cursor-pointer hover:border-orange-400 hover:text-orange-500 hover:bg-orange-50 transition-colors">
        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg>
        Tambah Level
    </button>

    <div class="flex gap-3 justify-end mt-6 pt-5 border-t border-slate-100">
        <a href="{{ route('approval-settings.index') }}" class="px-5 py-2.5 rounded-xl border border-slate-200 bg-white text-slate-600 text-sm font-medium no-underline inline-flex items-center">Batal</a>
        <button type="submit" class="px-6 py-2.5 rounded-xl text-sm font-semibold bg-gradient-to-br from-orange-400 to-orange-500 text-white border-0 cursor-pointer hover:-translate-y-px transition-all">Simpan Perubahan</button>
    </div>
    </form>
</div>

<script>
const positions = @json($positions->map(fn($p) => ['id' => $p->id, 'name' => $p->name]));
let rowIdx = {{ $chain->count() }};

function buildOptions(selectedId = null) {
    let html = '<option value="">-- Pilih Jabatan Approver --</option>';
    positions.forEach(p => {
        html += `<option value="${p.id}" ${selectedId == p.id ? 'selected' : ''}>${p.name}</option>`;
    });
    return html;
}

function addRow() {
    const idx = rowIdx++;
    const tr = document.createElement('tr');
    tr.className = 'border-b border-slate-100 chain-row';
    tr.innerHTML = `
        <td class="px-4 py-3 align-middle">
            <span class="step-badge inline-flex items-center justify-center w-7 h-7 rounded-full bg-orange-500 text-white text-xs font-bold"></span>
        </td>
        <td class="px-4 py-3 align-middle">
            <select name="steps[${idx}][approver_position_id]" class="w-full px-3 py-2 border border-slate-200 rounded-xl text-sm text-slate-800 bg-white outline-none focus:border-orange-400 transition-colors" required>
                ${buildOptions()}
            </select>
        </td>
        <td class="px-4 py-3 align-middle text-center">
            <button type="button" onclick="removeRow(this)" class="text-slate-300 hover:text-red-400 transition-colors p-1 border-0 bg-transparent cursor-pointer" title="Hapus baris">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M18 6L6 18M6 6l12 12"/></svg>
            </button>
        </td>`;
    document.getElementById('chain-body').appendChild(tr);
    renumber();
    if (window.initSelect2) window.initSelect2(tr);
}

function removeRow(btn) {
    const rows = document.querySelectorAll('#chain-body .chain-row');
    if (rows.length <= 1) return;
    btn.closest('tr').remove();
    renumber();
}

function renumber() {
    document.querySelectorAll('#chain-body .step-badge').forEach((badge, i) => {
        badge.textContent = i + 1;
    });
}
</script>
</x-layouts.app>
