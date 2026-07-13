<x-layouts.app title="Detail Pengajuan Dana">

<a href="{{ route('fund-requests.index') }}" class="inline-flex items-center gap-1.5 text-sm text-slate-500 hover:text-orange-500 mb-5 no-underline">
    <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M19 12H5M12 5l-7 7 7 7"/></svg>
    Kembali ke Daftar Pengajuan
</a>

@if(session('success'))
<div class="flex items-center gap-2.5 px-4 py-3 bg-green-50 border border-green-200 rounded-xl mb-4 text-sm text-green-700">
    <svg width="16" height="16" fill="#16a34a" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
    {{ session('success') }}
</div>
@endif

@if($errors->any())
<div class="flex items-start gap-2.5 px-4 py-3 bg-red-50 border border-red-200 rounded-xl mb-4 text-sm text-red-600">
    {{ $errors->first() }}
</div>
@endif

@php
    $statusConfig = [
        'draft'    => ['bg-slate-100 text-slate-500',   'Draft'],
        'pending'  => ['bg-yellow-100 text-yellow-700', 'Menunggu Approval'],
        'approved' => $fundRequest->isDisbursed()
                        ? ['bg-blue-100 text-blue-700', 'Sudah Dicairkan']
                        : ['bg-green-100 text-green-700', 'Disetujui'],
        'rejected' => ['bg-red-100 text-red-600',       'Ditolak'],
    ];
    [$cls, $label] = $statusConfig[$fundRequest->status];
@endphp

<div class="flex items-start justify-between gap-4 mb-6 flex-wrap">
    <div>
        <h2 class="text-lg font-bold text-slate-900 m-0 mb-1.5">{{ $fundRequest->title }}</h2>
        <div class="flex items-center gap-2.5 flex-wrap">
            <div class="inline-flex items-center gap-1.5 px-3 py-1 bg-orange-50 border border-orange-200 rounded-lg font-mono text-sm font-bold text-orange-600">
                <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"/></svg>
                {{ $fundRequest->reference }}
            </div>
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold {{ $cls }}">{{ $label }}</span>
            @if($fundRequest->isPending())
                <span class="text-xs text-yellow-700">Level {{ $fundRequest->current_step }}/{{ $fundRequest->total_steps }}</span>
            @endif
        </div>
    </div>
    <div class="flex gap-2 flex-wrap">
        @if($fundRequest->isDraft())
            <a href="{{ route('fund-requests.edit', $fundRequest) }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg text-xs font-semibold bg-blue-50 text-blue-600 hover:bg-blue-100 transition-colors no-underline">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                Edit
            </a>
            <form id="submit-form" method="POST" action="{{ route('fund-requests.submit', $fundRequest) }}">@csrf</form>
            <button type="button" id="btn-submit-pengajuan"
                class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg text-xs font-semibold bg-gradient-to-br from-orange-400 to-orange-500 text-white border-0 cursor-pointer"
                data-ref="{{ $fundRequest->reference }}">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M22 2L11 13M22 2l-7 20-4-9-9-4 20-7z"/></svg>
                Submit
            </button>
            <form id="del-form" method="POST" action="{{ route('fund-requests.destroy', $fundRequest) }}">@csrf @method('DELETE')</form>
            <button type="button" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg text-xs font-semibold bg-red-50 text-red-600 hover:bg-red-100 transition-colors border-0 cursor-pointer"
                onclick="confirmDelete('del-form', '{{ addslashes($fundRequest->reference) }}')">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                Hapus
            </button>
        @endif
        @if($isRequester && $fundRequest->isDisbursed())
            <a href="{{ route('fund-reports.create', ['fund_request' => $fundRequest->id]) }}"
               class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg text-xs font-semibold text-white border-0 cursor-pointer no-underline"
               style="background:linear-gradient(135deg, #7c3aed, #8b5cf6);">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Buat Laporan
            </a>
        @endif
        @if($canApprove)
            @php $currentApproval = $fundRequest->approvals->where('step', $fundRequest->current_step)->where('status', 'waiting')->first(); @endphp
            @if($currentApproval)
            <button type="button" id="btn-approve"
                class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg text-xs font-semibold bg-gradient-to-br from-green-500 to-green-600 text-white border-0 cursor-pointer hover:opacity-90 transition-opacity shadow-sm"
                data-approve-url="{{ route('fund-approvals.approve', $currentApproval) }}">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M20 6L9 17l-5-5"/></svg>
                Setujui
            </button>
            <button type="button" id="btn-reject"
                class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg text-xs font-semibold bg-gradient-to-br from-red-500 to-red-600 text-white border-0 cursor-pointer hover:opacity-90 transition-opacity shadow-sm"
                data-reject-url="{{ route('fund-approvals.reject', $currentApproval) }}">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M18 6L6 18M6 6l12 12"/></svg>
                Tolak
            </button>
            @endif
        @endif
    </div>
</div>

<div class="grid grid-cols-2 gap-5 mb-5">
    {{-- Info Pengajuan --}}
    <div class="bg-white rounded-xl shadow-sm p-6">
        <div class="text-[11px] font-bold text-slate-400 uppercase tracking-widest mb-3.5 pb-2 border-b border-slate-100">Informasi Pengajuan</div>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <div class="text-[11px] text-slate-400 mb-1">Organisasi</div>
                <div class="text-sm font-medium text-slate-900">{{ $fundRequest->organization->name }}</div>
            </div>
            <div>
                <div class="text-[11px] text-slate-400 mb-1">Departemen</div>
                <div class="text-sm font-medium text-slate-900">{{ $fundRequest->department->name }}</div>
            </div>
            <div>
                <div class="text-[11px] text-slate-400 mb-1">Tanggal Pengajuan</div>
                <div class="text-sm font-medium text-slate-900">{{ $fundRequest->created_at->format('d/m/Y H:i') }}</div>
            </div>
            <div>
                <div class="text-[11px] text-slate-400 mb-1">Periode Anggaran</div>
                <div class="text-sm font-medium text-slate-900">{{ $fundRequest->budgetPeriod?->name ?? '-' }}</div>
            </div>
            @if($fundRequest->submitted_at)
            <div>
                <div class="text-[11px] text-slate-400 mb-1">Disubmit</div>
                <div class="text-sm font-medium text-slate-900">{{ $fundRequest->submitted_at->format('d/m/Y H:i') }}</div>
            </div>
            @endif
            @if($fundRequest->approved_at)
            <div>
                <div class="text-[11px] text-slate-400 mb-1">Disetujui</div>
                <div class="text-sm font-medium text-green-700">{{ $fundRequest->approved_at->format('d/m/Y H:i') }}</div>
            </div>
            @endif
            @if($fundRequest->rejected_at)
            <div>
                <div class="text-[11px] text-slate-400 mb-1">Ditolak</div>
                <div class="text-sm font-medium text-red-500">{{ $fundRequest->rejected_at->format('d/m/Y H:i') }}</div>
            </div>
            @endif
        </div>
        @if($fundRequest->purpose)
        <div class="mt-4 pt-3.5 border-t border-slate-100">
            <div class="text-[11px] text-slate-400 mb-1.5">Tujuan / Keterangan</div>
            <p class="text-sm text-slate-700 leading-relaxed m-0">{{ $fundRequest->purpose }}</p>
        </div>
        @endif
        @if($fundRequest->bank_account_number)
        <div class="mt-4 pt-3.5 border-t border-slate-100">
            <div class="text-[11px] font-semibold text-slate-400 uppercase tracking-widest mb-2.5">Rekening Tujuan Transfer</div>
            <div class="flex items-center gap-3 px-4 py-3 bg-blue-50 border border-blue-100 rounded-xl">
                <div class="w-9 h-9 rounded-lg bg-blue-100 flex items-center justify-center flex-shrink-0">
                    <svg width="16" height="16" fill="none" stroke="#2563eb" stroke-width="2" viewBox="0 0 24 24"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
                </div>
                <div class="flex-1 min-w-0">
                    @if($fundRequest->bank_name)
                    <div class="text-[10px] font-bold text-blue-500 uppercase tracking-wide mb-0.5">{{ $fundRequest->bank_name }}</div>
                    @endif
                    <div class="text-base font-bold text-slate-900 font-mono tracking-wide">{{ $fundRequest->bank_account_number }}</div>
                    <div class="text-xs text-slate-600 mt-0.5">{{ $fundRequest->bank_account_name }}</div>
                </div>
            </div>
        </div>
        @endif
        @if($fundRequest->budgetProgram)
        <div class="mt-4 pt-3.5 border-t border-slate-100">
            <div class="text-[11px] text-slate-400 mb-1.5">Program Kerja</div>
            <a href="{{ route('budget-programs.show', $fundRequest->budgetProgram) }}" class="text-sm font-medium text-orange-500 hover:underline no-underline">{{ $fundRequest->budgetProgram->name }}</a>
        </div>
        @endif
    </div>

    {{-- Info Pengaju + Nominal --}}
    <div class="flex flex-col gap-5">
        <div class="bg-white rounded-xl shadow-sm p-6">
            <div class="text-[11px] font-bold text-slate-400 uppercase tracking-widest mb-3.5 pb-2 border-b border-slate-100">Informasi Pengaju</div>
            <div class="grid grid-cols-2 gap-3.5">
                <div>
                    <div class="text-[11px] text-slate-400 mb-1">Nama Karyawan</div>
                    <div class="text-sm font-medium text-slate-900">{{ $fundRequest->requester->name }}</div>
                </div>
                <div>
                    <div class="text-[11px] text-slate-400 mb-1">NIP</div>
                    <div class="text-sm font-medium text-slate-900 font-mono">{{ $fundRequest->requester->employee_id }}</div>
                </div>
                <div class="col-span-2">
                    <div class="text-[11px] text-slate-400 mb-1">Jabatan Saat Pengajuan</div>
                    <div class="text-sm font-medium text-slate-900">{{ $fundRequest->requesterPosition->name }}</div>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-7 text-center">
            <div class="text-[11px] text-slate-400 mb-2">Total Dana Diajukan</div>
            <div class="text-3xl font-extrabold text-slate-900 font-mono">Rp {{ number_format($fundRequest->amount, 0, ',', '.') }}</div>
        </div>
    </div>
</div>

{{-- Detail Program Kerja --}}
@if($fundRequest->budgetProgram)
@php $prog = $fundRequest->budgetProgram; @endphp
<div class="bg-white rounded-xl shadow-sm p-6 mb-5">
    <div class="flex items-center justify-between mb-3.5 pb-2 border-b border-slate-100">
        <div class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">Program Kerja</div>
        <a href="{{ route('budget-programs.show', $prog) }}" class="text-xs text-orange-500 hover:underline no-underline">Lihat program →</a>
    </div>
    <div class="flex items-center gap-2 mb-3">
        <div class="text-sm font-semibold text-slate-900">{{ $prog->name }}</div>
        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-blue-100 text-blue-700">{{ $prog->frequency }}× Pencairan</span>
        <span class="text-xs text-slate-400">Pagu: <span class="font-semibold text-slate-700">Rp {{ number_format($prog->total_amount, 0, ',', '.') }}</span></span>
    </div>

    @if($prog->details->isNotEmpty())
    <div class="overflow-x-auto mb-3">
        <table class="w-full text-xs border-collapse">
            <thead>
                <tr class="bg-slate-50">
                    <th class="px-3 py-2 text-left font-semibold text-slate-500 border border-slate-200">Jenis Pengeluaran</th>
                    <th class="px-3 py-2 text-left font-semibold text-slate-500 border border-slate-200">Deskripsi</th>
                    <th class="px-3 py-2 text-right font-semibold text-slate-500 border border-slate-200 w-[70px]">Qty</th>
                    <th class="px-3 py-2 text-left font-semibold text-slate-500 border border-slate-200 w-[60px]">Sat.</th>
                    <th class="px-3 py-2 text-right font-semibold text-slate-500 border border-slate-200 w-[130px]">Harga Satuan</th>
                    <th class="px-3 py-2 text-right font-semibold text-slate-500 border border-slate-200 w-[130px]">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($prog->details as $det)
                <tr>
                    <td class="px-3 py-2 border border-slate-200 text-slate-700">{{ $det->account?->name ?? '-' }}</td>
                    <td class="px-3 py-2 border border-slate-200 text-slate-700">{{ $det->description }}</td>
                    <td class="px-3 py-2 border border-slate-200 text-right text-slate-700">{{ rtrim(rtrim(number_format($det->quantity,2,',','.'),0),',') }}</td>
                    <td class="px-3 py-2 border border-slate-200 text-slate-500">{{ $det->unit ?? '-' }}</td>
                    <td class="px-3 py-2 border border-slate-200 text-right font-mono text-slate-700">Rp {{ number_format($det->unit_price, 0, ',', '.') }}</td>
                    <td class="px-3 py-2 border border-slate-200 text-right font-mono font-semibold text-slate-800">Rp {{ number_format($det->total_amount, 0, ',', '.') }}</td>
                </tr>
                @endforeach
                <tr class="bg-slate-50">
                    <td colspan="5" class="px-3 py-2 border border-slate-200 text-right text-xs font-semibold text-slate-500">Total Program</td>
                    <td class="px-3 py-2 border border-slate-200 text-right font-mono font-bold text-orange-600">Rp {{ number_format($prog->total_amount, 0, ',', '.') }}</td>
                </tr>
            </tbody>
        </table>
    </div>
    @endif

    @if($prog->schedules->isNotEmpty())
    <div>
        <div class="text-[11px] font-semibold text-slate-400 uppercase tracking-wide mb-2">Pencairan</div>
        <div class="flex flex-wrap gap-2">
            @foreach($prog->schedules as $sch)
            <div class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-slate-50 border border-slate-200 rounded-lg text-xs font-semibold text-slate-600">
                Termin {{ $sch->termin }}
            </div>
            @endforeach
        </div>
    </div>
    @endif
</div>
@endif

{{-- Approval Trail --}}
<div class="bg-white rounded-xl shadow-sm p-6">
    <div class="text-[11px] font-bold text-slate-400 uppercase tracking-widest mb-3.5 pb-2 border-b border-slate-100">Riwayat Approval</div>
    @if($fundRequest->approvals->isEmpty())
        <div class="text-center py-6 text-slate-400 text-sm">
            Pengajuan masih draft. Submit pengajuan untuk memulai proses approval.
        </div>
    @else
        <div class="flex flex-col">

            {{-- Disubmit --}}
            <div class="flex gap-3">
                <div class="flex flex-col items-center w-5 flex-shrink-0">
                    <div class="w-5 h-5 rounded-full flex items-center justify-center bg-blue-50 border-2 border-blue-400 flex-shrink-0">
                        <svg width="9" height="9" fill="#3b82f6" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                    </div>
                    <div class="w-px bg-slate-200 flex-1 my-1.5"></div>
                </div>
                <div class="pb-5 flex-1 min-w-0">
                    <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Disubmit</div>
                    <div class="text-sm font-semibold text-slate-800">{{ $fundRequest->requester->name }}</div>
                    <div class="text-xs text-slate-400 mt-0.5">{{ $fundRequest->submitted_at?->format('d/m/Y H:i') }}</div>
                </div>
            </div>

            @foreach($fundRequest->approvals->sortBy('step') as $approval)
            @php
                $isCurrentWaiting = $fundRequest->isPending() && $fundRequest->current_step === $approval->step && $approval->status === 'waiting';
                $dotClass = match($approval->status) {
                    'approved' => 'bg-green-50 border-2 border-green-500',
                    'rejected' => 'bg-red-50 border-2 border-red-500',
                    default    => $isCurrentWaiting ? 'bg-orange-50 border-2 border-orange-400' : 'bg-slate-100 border-2 border-slate-300',
                };
                $badgeCls = $approval->status === 'approved' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-600';
                $badgeTxt = $approval->status === 'approved' ? 'Disetujui' : 'Ditolak';
                $isLastStep = $loop->last && !$fundRequest->isApproved();
            @endphp
            <div class="flex gap-3">
                <div class="flex flex-col items-center w-5 flex-shrink-0">
                    <div class="w-5 h-5 rounded-full flex items-center justify-center {{ $dotClass }} flex-shrink-0">
                        @if($approval->status === 'approved')
                            <svg width="9" height="9" fill="#16a34a" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                        @elseif($approval->status === 'rejected')
                            <svg width="9" height="9" fill="#e11d48" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
                        @elseif($isCurrentWaiting)
                            <svg width="9" height="9" fill="#f97316" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/></svg>
                        @endif
                    </div>
                    @if(!$isLastStep)
                    <div class="w-px bg-slate-200 flex-1 my-1.5"></div>
                    @endif
                </div>
                <div class="{{ $isLastStep ? '' : 'pb-5' }} flex-1 min-w-0">
                    <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Level {{ $approval->step }}</div>
                    <div class="text-xs text-slate-500 mb-1">{{ $approval->approverPosition->name }}</div>
                    <div class="flex items-center gap-2 flex-wrap">
                        <div class="text-sm font-semibold text-slate-800">
                            @if($approval->approverUser)
                                {{ $approval->approverUser->name }}
                            @else
                                <span class="text-xs text-slate-400 font-normal italic">Menunggu approval...</span>
                            @endif
                        </div>
                        @if($approval->status !== 'waiting')
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold {{ $badgeCls }}">{{ $badgeTxt }}</span>
                        @elseif($isCurrentWaiting)
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-yellow-100 text-yellow-700">Menunggu</span>
                        @endif
                    </div>
                    @if($approval->acted_at)
                        <div class="text-xs text-slate-400 mt-0.5">{{ $approval->acted_at->format('d/m/Y H:i') }}</div>
                    @endif
                    @if($approval->notes)
                        <div class="mt-2 px-3 py-2 bg-slate-50 rounded-lg text-xs text-slate-600">{{ $approval->notes }}</div>
                    @endif
                </div>
            </div>
            @endforeach

            @if($fundRequest->isApproved())
            <div class="flex gap-3">
                <div class="flex flex-col items-center w-5 flex-shrink-0">
                    <div class="w-5 h-5 rounded-full flex items-center justify-center bg-green-50 border-2 border-green-500 flex-shrink-0">
                        <svg width="9" height="9" fill="#16a34a" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                    </div>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Selesai</div>
                    <div class="text-sm font-semibold text-green-700">Pengajuan Disetujui</div>
                    <div class="text-xs text-slate-400 mt-0.5">{{ $fundRequest->approved_at?->format('d/m/Y H:i') }}</div>
                </div>
            </div>
            @endif

        </div>
    @endif
</div>

{{-- Lampiran --}}
<div class="bg-white rounded-xl shadow-sm p-6 mt-5">
    <div class="text-[11px] font-bold text-slate-400 uppercase tracking-widest mb-3.5 pb-2 border-b border-slate-100 flex items-center justify-between">
        <span>Lampiran</span>
        <span class="text-slate-300 font-normal normal-case tracking-normal text-xs">{{ $fundRequest->attachments->count() }} file</span>
    </div>

    @if($fundRequest->attachments->isEmpty())
    <div class="text-sm text-slate-400 text-center py-3">Belum ada lampiran.</div>
    @else
    <div class="flex flex-col gap-2 mb-4">
        @foreach($fundRequest->attachments as $file)
        <div class="flex items-center gap-3 px-3 py-2.5 bg-slate-50 rounded-lg border border-slate-100">
            <svg width="16" height="16" fill="none" stroke="#64748b" stroke-width="1.5" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
            <div class="flex-1 min-w-0">
                <div class="text-xs font-medium text-slate-700 truncate">{{ $file->file_name }}</div>
                <div class="text-[10px] text-slate-400">{{ $file->file_size_label }} · {{ $file->created_at->format('d/m/Y H:i') }} · {{ $file->uploader?->name ?? '-' }}</div>
            </div>
            <a href="{{ $file->url }}" target="_blank" class="inline-flex items-center gap-1 px-2.5 py-1 rounded text-[10px] font-semibold bg-blue-50 text-blue-600 hover:bg-blue-100 transition-colors no-underline flex-shrink-0">
                <svg width="10" height="10" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                Unduh
            </a>
            @if($isRequester)
            <form method="POST" action="{{ route('fund-requests.files.delete', $file) }}" class="inline-block">
                @csrf @method('DELETE')
                <button type="submit" onclick="return confirm('Hapus file ini?')"
                    class="inline-flex items-center justify-center w-7 h-7 rounded text-[10px] bg-red-50 text-red-500 hover:bg-red-100 border-0 cursor-pointer flex-shrink-0">
                    <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/></svg>
                </button>
            </form>
            @endif
        </div>
        @endforeach
    </div>
    @endif

    @if($isRequester)
    <form method="POST" action="{{ route('fund-requests.files.upload', $fundRequest) }}" enctype="multipart/form-data" class="flex items-center gap-2 flex-wrap">
        @csrf
        <input type="file" name="file" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx,.xls,.xlsx"
            class="flex-1 min-w-[200px] text-xs text-slate-600 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-orange-50 file:text-orange-600 hover:file:bg-orange-100 cursor-pointer">
        <button type="submit" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg text-xs font-semibold bg-orange-500 text-white border-0 cursor-pointer hover:bg-orange-600 transition-colors flex-shrink-0">
            <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
            Upload
        </button>
    </form>
    <div class="text-[10px] text-slate-400 mt-1.5">Format: PDF, JPG, PNG, DOC, XLS · Maks. 10 MB</div>
    @endif
</div>

{{-- Informasi Pencairan --}}
@if($fundRequest->isDisbursed())
<div class="bg-white rounded-xl shadow-sm p-6 mt-5">
    <div class="text-[11px] font-bold text-slate-400 uppercase tracking-widest mb-3.5 pb-2 border-b border-slate-100">Informasi Pencairan</div>

    <div class="flex items-start gap-3 px-4 py-3 bg-green-50 border border-green-200 rounded-xl mb-4">
        <div class="w-8 h-8 rounded-lg bg-green-100 flex items-center justify-center flex-shrink-0 mt-0.5">
            <svg width="14" height="14" fill="none" stroke="#16a34a" stroke-width="2" viewBox="0 0 24 24"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
        </div>
        <div class="flex-1 min-w-0">
            <div class="text-xs font-bold text-green-700 mb-0.5">Dicairkan {{ $fundRequest->disbursed_at->format('d/m/Y H:i') }}</div>
            <div class="text-xs text-green-600">Oleh: {{ $fundRequest->disbursed_by }}</div>
            @if($fundRequest->disburseAccount)
            <div class="text-xs text-green-600 mt-0.5">Via: <span class="font-semibold">{{ $fundRequest->disburseAccount->name }}</span> <span class="font-mono text-[10px]">({{ $fundRequest->disburseAccount->code }})</span></div>
            @endif
            @if($fundRequest->disbursement_notes)
            <div class="text-xs text-green-500 mt-1 italic">{{ $fundRequest->disbursement_notes }}</div>
            @endif
        </div>
    </div>

    {{-- Status penerimaan --}}
    @if($fundRequest->receipt_status === 'confirmed')
    <div class="flex items-center gap-2 px-4 py-2.5 bg-green-50 border border-green-200 rounded-xl mb-4 text-xs text-green-700">
        <svg width="13" height="13" fill="#16a34a" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
        <span class="font-semibold">Dana Diterima{{ $fundRequest->auto_confirmed ? ' (Auto-konfirmasi)' : '' }}</span>
        @if($fundRequest->receipt_confirmed_at)
        <span class="text-green-500">· {{ $fundRequest->receipt_confirmed_at->format('d/m/Y H:i') }}</span>
        @endif
    </div>
    @elseif($fundRequest->receipt_status === 'disputed')
    <div class="px-4 py-2.5 bg-red-50 border border-red-200 rounded-xl mb-4">
        <div class="flex items-center gap-2 text-xs text-red-700 font-semibold mb-0.5">
            <svg width="13" height="13" fill="#e11d48" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
            Ada Kendala Dilaporkan · {{ $fundRequest->receipt_confirmed_at?->format('d/m/Y H:i') }}
        </div>
        @if($fundRequest->receipt_notes)
        <div class="text-xs text-red-600 mt-1">{{ $fundRequest->receipt_notes }}</div>
        @endif
    </div>
    @else
    <div class="flex items-center gap-2 px-4 py-2.5 bg-amber-50 border border-amber-200 rounded-xl mb-4 text-xs text-amber-700">
        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
        Menunggu konfirmasi penerimaan dari pengaju. Jika belum dikonfirmasi dalam 7 hari, status otomatis dikonfirmasi.
    </div>
    @endif

    {{-- Bukti pencairan --}}
    @if($fundRequest->disbursementProofs->isNotEmpty())
    <div class="pt-3 border-t border-slate-100">
        <div class="text-[11px] font-semibold text-slate-400 uppercase tracking-wide mb-2">Bukti Pencairan</div>
        <div class="flex flex-col gap-2">
            @foreach($fundRequest->disbursementProofs as $proof)
            <div class="flex items-center gap-3 px-3 py-2.5 bg-slate-50 rounded-lg border border-slate-100">
                <svg width="16" height="16" fill="none" stroke="#64748b" stroke-width="1.5" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                <div class="flex-1 min-w-0">
                    <div class="text-xs font-medium text-slate-700 truncate">{{ $proof->file_name }}</div>
                    <div class="text-[10px] text-slate-400">{{ $proof->file_size_label }} · {{ $proof->created_at->format('d/m/Y H:i') }}</div>
                </div>
                <a href="{{ $proof->url }}" target="_blank" class="inline-flex items-center gap-1 px-2.5 py-1 rounded text-[10px] font-semibold bg-blue-50 text-blue-600 hover:bg-blue-100 transition-colors no-underline flex-shrink-0">
                    <svg width="10" height="10" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                    Lihat
                </a>
            </div>
            @endforeach
        </div>
    </div>
    @endif
</div>
@endif

{{-- Konfirmasi Penerimaan Dana (pengaju saja, setelah cair, belum dikonfirmasi) --}}
@if($isRequester && $fundRequest->isDisbursed() && is_null($fundRequest->receipt_status))
<div class="bg-white rounded-xl shadow-sm p-6 mt-5 border-2 border-blue-100">
    <div class="text-[11px] font-bold text-blue-500 uppercase tracking-widest mb-2 pb-2 border-b border-slate-100">Konfirmasi Penerimaan Dana</div>
    <p class="text-sm text-slate-600 mb-4 mt-3">Dana sebesar <span class="font-bold text-slate-900 font-mono">Rp {{ number_format($fundRequest->amount, 0, ',', '.') }}</span> telah dicairkan ke rekening Anda. Apakah dana sudah masuk?</p>
    <div class="flex gap-3 flex-wrap">
        <form method="POST" action="{{ route('fund-requests.confirm-receipt', $fundRequest) }}">
            @csrf
            <button type="submit" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-semibold bg-gradient-to-br from-green-500 to-green-600 text-white border-0 cursor-pointer hover:opacity-90 transition-opacity shadow-sm">
                <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M20 6L9 17l-5-5"/></svg>
                Uang Sudah Masuk
            </button>
        </form>
        <button type="button" id="btn-dispute"
            class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-semibold bg-gradient-to-br from-red-500 to-red-600 text-white border-0 cursor-pointer hover:opacity-90 transition-opacity shadow-sm">
            <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
            Ada Kendala
        </button>
    </div>
</div>

{{-- Dispute Modal --}}
<div class="fixed inset-0 z-[999] bg-slate-900/50 backdrop-blur-sm items-center justify-center" id="dispute-overlay" style="display:none;">
    <div class="bg-white rounded-2xl w-[420px] max-w-[90vw] shadow-2xl overflow-hidden">
        <div class="px-6 py-5 border-b border-slate-100">
            <div class="text-base font-bold text-red-500">Laporkan Kendala Penerimaan</div>
            <div class="text-xs text-slate-500 mt-1">{{ $fundRequest->reference }} — {{ $fundRequest->title }}</div>
        </div>
        <form method="POST" action="{{ route('fund-requests.dispute-receipt', $fundRequest) }}">
            @csrf
            <div class="px-6 py-5">
                <label class="text-xs font-semibold text-slate-600 block mb-1.5">Keterangan Kendala <span class="text-red-500">*</span></label>
                <textarea name="receipt_notes" rows="3" required maxlength="500"
                    class="w-full px-3 py-2.5 border border-slate-200 rounded-xl text-sm outline-none focus:border-red-400 focus:ring-2 focus:ring-red-100 transition-colors resize-y"
                    placeholder="Jelaskan kendala yang terjadi. Contoh: dana belum masuk, nominal tidak sesuai, dll."></textarea>
            </div>
            <div class="px-6 py-4 border-t border-slate-100 flex gap-2 justify-end">
                <button type="button" id="dispute-cancel" class="px-4 py-2.5 rounded-xl bg-slate-100 text-slate-700 border border-slate-200 text-sm font-medium cursor-pointer hover:bg-slate-200 transition-colors">Batal</button>
                <button type="submit" class="inline-flex items-center gap-1.5 px-5 py-2.5 rounded-xl text-sm font-semibold bg-gradient-to-br from-red-500 to-red-600 text-white border-0 cursor-pointer hover:opacity-90 transition-opacity shadow-sm">
                    Kirim Laporan
                </button>
            </div>
        </form>
    </div>
</div>
@endif

{{-- Approve Modal --}}
<div class="fixed inset-0 z-[999] bg-slate-900/50 backdrop-blur-sm items-center justify-center" id="approve-overlay" style="display:none;">
    <div class="bg-white rounded-2xl w-[420px] max-w-[90vw] shadow-2xl overflow-hidden">
        <div class="px-6 py-5 border-b border-slate-100">
            <div class="text-base font-bold text-green-700">Setujui Pengajuan</div>
            <div class="text-xs text-slate-500 mt-1">{{ $fundRequest->reference }} — {{ $fundRequest->title }}</div>
        </div>
        <form id="approve-form" method="POST" action="">
            @csrf
            <div class="px-6 py-5">
                <label class="text-xs font-semibold text-slate-600 block mb-1.5">Catatan (opsional)</label>
                <textarea name="notes" rows="3" class="w-full px-3 py-2.5 border border-slate-200 rounded-xl text-sm outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100 transition-colors resize-y" placeholder="Tambahkan catatan persetujuan..."></textarea>
            </div>
            <div class="px-6 py-4 border-t border-slate-100 flex gap-2 justify-end">
                <button type="button" class="cancel-btn px-4 py-2.5 rounded-xl bg-slate-100 text-slate-700 border border-slate-200 text-sm font-medium cursor-pointer hover:bg-slate-200 transition-colors">Batal</button>
                <button type="submit" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg text-xs font-semibold bg-gradient-to-br from-green-500 to-green-600 text-white border-0 cursor-pointer hover:opacity-90 transition-opacity shadow-sm">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M20 6L9 17l-5-5"/></svg>
                    Ya, Setujui
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Reject Modal --}}
<div class="fixed inset-0 z-[999] bg-slate-900/50 backdrop-blur-sm items-center justify-center" id="reject-overlay" style="display:none;">
    <div class="bg-white rounded-2xl w-[420px] max-w-[90vw] shadow-2xl overflow-hidden">
        <div class="px-6 py-5 border-b border-slate-100">
            <div class="text-base font-bold text-red-500">Tolak Pengajuan</div>
            <div class="text-xs text-slate-500 mt-1">{{ $fundRequest->reference }} — {{ $fundRequest->title }}</div>
        </div>
        <form id="reject-form" method="POST" action="">
            @csrf
            <div class="px-6 py-5">
                <label class="text-xs font-semibold text-slate-600 block mb-1.5">Alasan Penolakan <span class="text-red-500">*</span></label>
                <textarea name="notes" rows="3" class="w-full px-3 py-2.5 border border-slate-200 rounded-xl text-sm outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100 transition-colors resize-y" placeholder="Jelaskan alasan penolakan..." required></textarea>
                <div class="text-[11px] text-slate-400 mt-1.5">Catatan wajib diisi untuk penolakan.</div>
            </div>
            <div class="px-6 py-4 border-t border-slate-100 flex gap-2 justify-end">
                <button type="button" class="cancel-btn px-4 py-2.5 rounded-xl bg-slate-100 text-slate-700 border border-slate-200 text-sm font-medium cursor-pointer hover:bg-slate-200 transition-colors">Batal</button>
                <button type="submit" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg text-xs font-semibold bg-gradient-to-br from-red-500 to-red-600 text-white border-0 cursor-pointer hover:opacity-90 transition-opacity shadow-sm">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M18 6L6 18M6 6l12 12"/></svg>
                    Ya, Tolak
                </button>
            </div>
        </form>
    </div>
</div>

<script>
(function() {
    // Submit button
    document.getElementById('btn-submit-pengajuan')?.addEventListener('click', function() {
        var ref = this.dataset.ref;
        confirmModal(
            'Submit Pengajuan',
            'Pengajuan <strong>' + ref + '</strong> akan dikirim untuk diproses approval. Setelah disubmit, pengajuan tidak bisa diubah.',
            function() { document.getElementById('submit-form').submit(); },
            'Ya, Submit'
        );
    });

    var approveOverlay = document.getElementById('approve-overlay');
    var rejectOverlay  = document.getElementById('reject-overlay');

    function closeActionModals() {
        if (approveOverlay) approveOverlay.style.display = 'none';
        if (rejectOverlay)  rejectOverlay.style.display  = 'none';
    }

    // Approve button
    var btnApprove = document.getElementById('btn-approve');
    if (btnApprove && approveOverlay) {
        btnApprove.addEventListener('click', function() {
            document.getElementById('approve-form').action = this.dataset.approveUrl;
            approveOverlay.style.display = 'flex';
        });
    }

    // Reject button
    var btnReject = document.getElementById('btn-reject');
    if (btnReject && rejectOverlay) {
        btnReject.addEventListener('click', function() {
            document.getElementById('reject-form').action = this.dataset.rejectUrl;
            rejectOverlay.style.display = 'flex';
        });
    }

    document.querySelectorAll('#approve-overlay .cancel-btn, #reject-overlay .cancel-btn').forEach(function(btn) {
        btn.addEventListener('click', closeActionModals);
    });
    if (approveOverlay) approveOverlay.addEventListener('click', function(e) { if (e.target === e.currentTarget) closeActionModals(); });
    if (rejectOverlay)  rejectOverlay.addEventListener('click',  function(e) { if (e.target === e.currentTarget) closeActionModals(); });

    // Dispute modal
    var disputeOverlay = document.getElementById('dispute-overlay');
    var btnDispute     = document.getElementById('btn-dispute');
    var disputeCancel  = document.getElementById('dispute-cancel');
    if (btnDispute && disputeOverlay) {
        btnDispute.addEventListener('click', function() { disputeOverlay.style.display = 'flex'; });
        disputeCancel.addEventListener('click', function() { disputeOverlay.style.display = 'none'; });
        disputeOverlay.addEventListener('click', function(e) { if (e.target === e.currentTarget) disputeOverlay.style.display = 'none'; });
    }

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeActionModals();
            if (disputeOverlay) disputeOverlay.style.display = 'none';
        }
    });
})();
</script>
</x-layouts.app>
