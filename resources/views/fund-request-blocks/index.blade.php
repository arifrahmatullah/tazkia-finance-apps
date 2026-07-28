<x-layouts.app title="Blokir Pengajuan">

<style>
    .toggle-switch {
        position: relative; display: inline-block; width: 40px; height: 22px; flex-shrink: 0;
        border: none; border-radius: 999px; cursor: pointer; padding: 0;
        background: #cbd5e1; transition: background .15s;
    }
    .toggle-switch.is-on { background: #ef4444; }
    .toggle-switch .toggle-knob {
        position: absolute; height: 16px; width: 16px; left: 3px; top: 3px;
        background: white; border-radius: 50%; transition: transform .15s;
        box-shadow: 0 1px 2px rgba(0,0,0,0.15);
    }
    .toggle-switch.is-on .toggle-knob { transform: translateX(18px); }
</style>

<div class="flex items-center justify-between mb-5">
    <div>
        <h2 class="text-lg font-bold text-slate-900 m-0 mb-0.5">Blokir Pengajuan</h2>
        <p class="text-xs text-slate-400 m-0">Tutup sementara fitur pengajuan dana per organisasi</p>
    </div>
</div>

@if(session('success'))
<div class="flex items-center gap-2.5 px-4 py-3 bg-green-50 border border-green-200 rounded-xl mb-4 text-sm text-green-700">
    <svg width="16" height="16" fill="#16a34a" viewBox="0 0 20 20" class="shrink-0"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
    {{ session('success') }}
</div>
@endif

<div class="flex items-center gap-2.5 px-4 py-3 bg-blue-50 border border-blue-200 rounded-xl mb-5 text-sm text-blue-700">
    <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" class="shrink-0"><path d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
    Saat diblokir, karyawan di organisasi tersebut tidak bisa membuat pengajuan dana baru. Pengajuan yang sudah ada (draft/pending/dicairkan) tetap bisa diproses seperti biasa.
</div>

<div class="grid gap-4">
    @forelse($organizations as $org)
    <div class="bg-white rounded-xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="px-5 py-4 flex items-center justify-between gap-4 flex-wrap">
            <div class="min-w-0">
                <div class="text-sm font-bold text-slate-900">{{ $org->name }}</div>
                <div class="text-xs text-slate-400 mt-0.5">{{ $org->code }}</div>
                @if($org->fund_request_blocked)
                <div class="text-xs text-red-600 mt-1.5">
                    Ditutup oleh {{ $org->fundRequestBlockedBy?->name ?? '-' }}
                    @if($org->fund_request_blocked_at) pada {{ $org->fund_request_blocked_at->translatedFormat('d M Y, H:i') }} @endif
                    @if($org->fund_request_block_reason) — "{{ $org->fund_request_block_reason }}" @endif
                </div>
                @endif
            </div>

            <div class="flex items-center gap-3">
                <span class="text-xs font-semibold {{ $org->fund_request_blocked ? 'text-red-600' : 'text-green-600' }}">
                    {{ $org->fund_request_blocked ? 'Ditutup' : 'Dibuka' }}
                </span>
                <button type="button"
                    onclick="openBlockModal('{{ $org->id }}', '{{ addslashes($org->name) }}', {{ $org->fund_request_blocked ? 'true' : 'false' }})"
                    class="toggle-switch {{ $org->fund_request_blocked ? 'is-on' : '' }}" title="Toggle blokir pengajuan">
                    <span class="toggle-knob"></span>
                </button>
            </div>
        </div>
    </div>
    @empty
    <div class="bg-white rounded-xl border border-slate-100 shadow-sm py-14 px-5 text-center text-slate-400">
        <p class="text-sm m-0">Tidak ada organisasi yang bisa diatur.</p>
    </div>
    @endforelse
</div>

{{-- Modal konfirmasi --}}
<div id="block-modal" class="fixed inset-0 bg-slate-900/40 items-center justify-center z-50 hidden px-4">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6">
        <h3 id="block-modal-title" class="text-base font-bold text-slate-900 m-0 mb-1">Tutup Pengajuan Dana</h3>
        <p id="block-modal-desc" class="text-sm text-slate-500 m-0 mb-4"></p>

        <form id="block-modal-form" method="POST">
            @csrf @method('PUT')
            <input type="hidden" name="blocked" id="block-modal-blocked">

            <div id="block-modal-reason-wrap" class="mb-4">
                <label class="text-xs font-semibold text-slate-500 block mb-1.5">Alasan (opsional)</label>
                <textarea name="reason" rows="2" maxlength="255" placeholder="Contoh: Tutup buku periode Juli 2026"
                    class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm text-slate-800 outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100 transition-colors resize-none"></textarea>
            </div>

            <div class="flex justify-end gap-2">
                <button type="button" onclick="closeBlockModal()"
                    class="px-4 py-2 rounded-lg border border-slate-200 text-sm text-slate-600 bg-white hover:bg-slate-50 transition-colors">Batal</button>
                <button id="block-modal-submit" type="submit"
                    class="px-4 py-2 rounded-lg text-sm font-semibold text-white border-0 cursor-pointer transition-all">Simpan</button>
            </div>
        </form>
    </div>
</div>

<script>
function openBlockModal(orgId, orgName, isBlocked) {
    const modal = document.getElementById('block-modal');
    const form = document.getElementById('block-modal-form');
    const title = document.getElementById('block-modal-title');
    const desc = document.getElementById('block-modal-desc');
    const blockedInput = document.getElementById('block-modal-blocked');
    const reasonWrap = document.getElementById('block-modal-reason-wrap');
    const submitBtn = document.getElementById('block-modal-submit');

    form.action = '{{ url("fund-request-blocks") }}/' + orgId;

    if (isBlocked) {
        title.textContent = 'Buka Kembali Pengajuan Dana';
        desc.textContent = 'Karyawan di "' + orgName + '" akan bisa membuat pengajuan dana baru lagi.';
        blockedInput.value = '0';
        reasonWrap.classList.add('hidden');
        submitBtn.style.background = '#16a34a';
    } else {
        title.textContent = 'Tutup Pengajuan Dana Sementara';
        desc.textContent = 'Karyawan di "' + orgName + '" tidak akan bisa membuat pengajuan dana baru sampai dibuka kembali.';
        blockedInput.value = '1';
        reasonWrap.classList.remove('hidden');
        submitBtn.style.background = '#ef4444';
    }

    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function closeBlockModal() {
    const modal = document.getElementById('block-modal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}
</script>

</x-layouts.app>
