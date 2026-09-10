<x-layouts.app title="Approval Program Kerja">

<div class="mb-5">
    <h2 class="text-lg font-bold text-slate-900 m-0 mb-0.5">Approval Perubahan Program Kerja</h2>
    <p class="text-xs text-slate-400 m-0">Perubahan Program Kerja yang diajukan di luar periode perencanaan, menunggu persetujuan Anda</p>
</div>

@if(session('success'))
<div class="flex items-center gap-2.5 px-4 py-3 bg-green-50 border border-green-200 rounded-xl mb-4 text-sm text-green-700">
    <svg width="16" height="16" fill="#16a34a" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
    {{ session('success') }}
</div>
@endif

<div class="bg-white rounded-xl shadow-sm overflow-hidden">
    @if($pending->isEmpty())
    <div class="py-12 text-center text-slate-400">
        <svg width="36" height="36" fill="none" stroke="#cbd5e1" stroke-width="1.5" viewBox="0 0 24 24" class="mx-auto mb-2.5 block"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <p class="text-sm m-0">Tidak ada perubahan yang menunggu approval Anda.</p>
    </div>
    @else
    <table class="w-full border-collapse">
        <thead>
            <tr class="bg-slate-50 border-b border-slate-100">
                <th class="px-5 py-3 text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wide">Program Kerja</th>
                <th class="px-5 py-3 text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wide">Perubahan</th>
                <th class="px-5 py-3 text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wide">Diajukan Oleh</th>
                <th class="px-5 py-3 text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wide">Langkah</th>
                <th class="px-5 py-3 w-[220px]"></th>
            </tr>
        </thead>
        <tbody>
            @foreach($pending as $approval)
            @php $cr = $approval->changeRequest; @endphp
            <tr class="border-b border-slate-50 last:border-0">
                <td class="px-5 py-3.5 align-middle">
                    <a href="{{ route('budget-programs.show', $cr->budgetProgram) }}" class="text-sm font-semibold text-orange-600 hover:underline no-underline">{{ $cr->budgetProgram->name }}</a>
                    <div class="text-xs text-slate-400">{{ $cr->budgetProgram->budgetAllocation->department->name }}</div>
                </td>
                <td class="px-5 py-3.5 align-middle text-sm text-slate-700">{{ $cr->summary }}</td>
                <td class="px-5 py-3.5 align-middle text-sm text-slate-600">
                    {{ $cr->requestedBy->name }}
                    <div class="text-xs text-slate-400">{{ $cr->created_at->format('d/m/Y H:i') }}</div>
                </td>
                <td class="px-5 py-3.5 align-middle">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-yellow-100 text-yellow-700">
                        Langkah {{ $approval->step }}/{{ $cr->total_steps }} · {{ $approval->approver_role === 'keuangan' ? 'Keuangan' : 'Warek II' }}
                    </span>
                </td>
                <td class="px-5 py-3.5 align-middle">
                    <div class="flex items-center justify-end gap-2">
                        <form method="POST" action="{{ route('budget-program-change-requests.approve', $approval) }}">
                            @csrf
                            <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold bg-gradient-to-br from-green-500 to-green-600 text-white border-0 cursor-pointer hover:opacity-90 transition-opacity">
                                <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M20 6L9 17l-5-5"/></svg>
                                Setujui
                            </button>
                        </form>
                        <button type="button" onclick="openReject('{{ $approval->id }}', '{{ addslashes($cr->summary) }}')"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold bg-red-50 text-red-600 hover:bg-red-100 transition-colors border-0 cursor-pointer">
                            <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M18 6L6 18M6 6l12 12"/></svg>
                            Tolak
                        </button>
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif
</div>

{{-- Modal Tolak --}}
<div class="fixed inset-0 z-[999] bg-slate-900/50 backdrop-blur-sm items-center justify-center" id="reject-overlay" style="display:none;">
    <div class="bg-white rounded-2xl w-[420px] max-w-[90vw] shadow-2xl overflow-hidden">
        <div class="px-6 py-5 border-b border-slate-100">
            <h3 class="text-base font-bold text-red-500 m-0">Tolak Perubahan</h3>
            <div class="text-xs text-slate-500 mt-1" id="reject-summary"></div>
        </div>
        <form id="reject-form" method="POST" action="">
            @csrf
            <div class="px-6 py-5">
                <label class="text-xs font-semibold text-slate-600 block mb-1.5">Alasan Penolakan <span class="text-red-500">*</span></label>
                <textarea name="notes" rows="3" required maxlength="500"
                    class="w-full px-3 py-2.5 border border-slate-200 rounded-xl text-sm outline-none focus:border-red-400 focus:ring-2 focus:ring-red-100 transition-colors resize-y"
                    placeholder="Jelaskan alasan penolakan..."></textarea>
            </div>
            <div class="px-6 py-4 border-t border-slate-100 flex gap-2 justify-end">
                <button type="button" id="reject-cancel" class="px-4 py-2.5 rounded-xl bg-slate-100 text-slate-700 border border-slate-200 text-sm font-medium cursor-pointer hover:bg-slate-200 transition-colors">Batal</button>
                <button type="submit" class="inline-flex items-center gap-1.5 px-5 py-2.5 rounded-xl text-sm font-semibold bg-gradient-to-br from-red-500 to-red-600 text-white border-0 cursor-pointer hover:opacity-90 transition-opacity shadow-sm">
                    Kirim Penolakan
                </button>
            </div>
        </form>
    </div>
</div>

<script>
const rejectOverlay = document.getElementById('reject-overlay');
const rejectForm = document.getElementById('reject-form');
const rejectSummary = document.getElementById('reject-summary');
const rejectUrlBase = "{{ url('budget-program-change-requests') }}";

function openReject(approvalId, summary) {
    rejectForm.action = `${rejectUrlBase}/${approvalId}/reject`;
    rejectSummary.textContent = summary;
    rejectOverlay.style.display = 'flex';
}
document.getElementById('reject-cancel').addEventListener('click', () => rejectOverlay.style.display = 'none');
rejectOverlay.addEventListener('click', (e) => { if (e.target === e.currentTarget) rejectOverlay.style.display = 'none'; });
</script>

</x-layouts.app>
