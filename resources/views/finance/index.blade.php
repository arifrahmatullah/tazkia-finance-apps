<x-layouts.app title="Pencairan Dana">

@if(session('success'))
<div class="flex items-center gap-2.5 px-4 py-3 bg-green-50 border border-green-200 rounded-xl mb-4 text-sm text-green-700">
    <svg width="16" height="16" fill="#16a34a" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
    {{ session('success') }}
</div>
@endif
@if(session('warning'))
<div class="flex items-center gap-2.5 px-4 py-3 bg-amber-50 border border-amber-200 rounded-xl mb-4 text-sm text-amber-700">
    <svg width="16" height="16" fill="none" stroke="#d97706" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
    {{ session('warning') }}
</div>
@endif

@if($errors->any())
<div class="flex items-center gap-2.5 px-4 py-3 bg-red-50 border border-red-200 rounded-xl mb-4 text-sm text-red-700">
    <svg width="16" height="16" fill="none" stroke="#dc2626" stroke-width="2" viewBox="0 0 24 24" class="shrink-0"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
    <span>{{ $errors->first() }}</span>
</div>
@endif

@if($missingProofCount > 0)
<div class="flex items-center gap-2.5 px-4 py-3 bg-red-50 border border-red-200 rounded-xl mb-4 text-sm text-red-700">
    <svg width="16" height="16" fill="none" stroke="#dc2626" stroke-width="2" viewBox="0 0 24 24" class="shrink-0"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
    <span><strong>{{ $missingProofCount }} pencairan belum ada bukti transfer.</strong> Jangan lupa upload bukti pada kartu yang bertanda merah di bawah.</span>
</div>
@endif

{{-- Header --}}
<div class="flex items-start justify-between gap-4 mb-5 flex-wrap">
    <div>
        <h2 class="text-lg font-bold text-slate-900 m-0 mb-1">Pencairan Dana</h2>
        <p class="text-xs text-slate-400 m-0">Pengajuan yang telah disetujui dan siap dicairkan</p>
    </div>
</div>

{{-- Filter Bar --}}
<form method="GET" action="{{ route('finance.index') }}" class="bg-white rounded-xl shadow-sm p-4 mb-4 flex flex-wrap gap-3 items-end">

    {{-- Search --}}
    <div class="flex-1 min-w-[200px]">
        <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block mb-1.5">Cari</label>
        <div class="relative">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
            <input type="text" name="search" value="{{ request('search') }}"
                placeholder="Referensi atau judul pengajuan..."
                class="w-full pl-9 pr-3 py-2 border border-slate-200 rounded-lg text-sm outline-none focus:border-blue-400 transition-colors">
        </div>
    </div>

    {{-- Status filter --}}
    <div class="min-w-[160px]">
        <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block mb-1.5">Status</label>
        <select name="status" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm text-slate-700 bg-white outline-none focus:border-blue-400 transition-colors">
            <option value="" {{ $filterStatus === '' ? 'selected' : '' }}>Semua</option>
            <option value="approved" {{ $filterStatus === 'approved' ? 'selected' : '' }}>Belum Cair</option>
            <option value="disbursed" {{ $filterStatus === 'disbursed' ? 'selected' : '' }}>Sudah Cair</option>
            <option value="belum_bukti" {{ $filterStatus === 'belum_bukti' ? 'selected' : '' }}>Sudah Cair, Belum Ada Bukti</option>
        </select>
    </div>

    {{-- Org filter --}}
    @if($organizations->count() > 1)
    <div class="min-w-[200px]">
        <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block mb-1.5">Organisasi</label>
        <select name="organization_id" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm text-slate-700 bg-white outline-none focus:border-blue-400 transition-colors">
            <option value="">Semua Organisasi</option>
            @foreach($organizations as $org)
                <option value="{{ $org->id }}" {{ request('organization_id') == $org->id ? 'selected' : '' }}>{{ $org->name }}</option>
            @endforeach
        </select>
    </div>
    @endif

    <div class="flex gap-2">
        <button type="submit" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-sm font-semibold bg-blue-600 text-white border-0 cursor-pointer hover:bg-blue-700 transition-colors">
            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
            Cari
        </button>
        @if(request('search') || request('organization_id') || request('status'))
        <a href="{{ route('finance.index') }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-sm font-medium bg-slate-100 text-slate-600 no-underline hover:bg-slate-200 transition-colors">
            Reset
        </a>
        @endif
    </div>
</form>

{{-- Summary --}}
@if($totalCount > 0)
<div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-4">
    <div class="bg-white rounded-xl shadow-sm px-4 py-3.5">
        <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-0.5">Belum Cair</div>
        <div class="text-2xl font-extrabold text-orange-500">{{ $belumCair }}</div>
        <div class="text-xs text-slate-400 mt-0.5 font-mono">Rp {{ number_format($totalBelum, 0, ',', '.') }}</div>
    </div>
    <div class="bg-white rounded-xl shadow-sm px-4 py-3.5">
        <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-0.5">Sudah Cair</div>
        <div class="text-2xl font-extrabold text-green-500">{{ $sudahCair }}</div>
        <div class="text-xs text-slate-400 mt-0.5">semua data</div>
    </div>
    <a href="{{ route('finance.index', array_merge(request()->except(['status', 'page']), ['status' => 'belum_bukti'])) }}"
        class="rounded-xl shadow-sm px-4 py-3.5 no-underline block transition-colors {{ $missingProofCount > 0 ? 'bg-red-50 border border-red-200 hover:bg-red-100' : 'bg-white hover:bg-slate-50' }}">
        <div class="text-[10px] font-bold {{ $missingProofCount > 0 ? 'text-red-400' : 'text-slate-400' }} uppercase tracking-widest mb-0.5">Belum Ada Bukti</div>
        <div class="text-2xl font-extrabold {{ $missingProofCount > 0 ? 'text-red-500' : 'text-slate-300' }}">{{ $missingProofCount }}</div>
        <div class="text-xs {{ $missingProofCount > 0 ? 'text-red-400' : 'text-slate-400' }} mt-0.5">{{ $missingProofCount > 0 ? 'perlu upload bukti transfer' : 'semua bukti lengkap' }}</div>
    </a>
    <div class="bg-white rounded-xl shadow-sm px-4 py-3.5 col-span-2 sm:col-span-1">
        <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-0.5">Total Semua</div>
        <div class="text-lg font-extrabold text-slate-800 font-mono leading-tight">
            Rp {{ number_format($totalSemua, 0, ',', '.') }}
        </div>
        <div class="text-xs text-slate-400 mt-0.5">{{ $totalCount }} pengajuan</div>
    </div>
</div>
@endif

@if($fundRequests->isEmpty())
<div class="bg-white rounded-xl shadow-sm py-16 px-5 text-center">
    <div class="w-16 h-16 rounded-2xl bg-slate-50 border border-slate-100 flex items-center justify-center mx-auto mb-4">
        <svg width="28" height="28" fill="none" stroke="#94a3b8" stroke-width="1.5" viewBox="0 0 24 24">
            <rect x="1" y="4" width="22" height="16" rx="2" ry="2"/><line x1="1" y1="10" x2="23" y2="10"/>
        </svg>
    </div>
    <div class="text-sm font-semibold text-slate-700 mb-1">Tidak ada data pencairan</div>
    <div class="text-xs text-slate-400">Belum ada pengajuan yang telah disetujui.</div>
</div>

@else
<div class="flex flex-col gap-3">
    @foreach($fundRequests as $fr)
    @php $isDisbursed = !is_null($fr->disbursed_at); @endphp
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        {{-- Status stripe --}}
        <div class="h-1 {{ $isDisbursed ? 'bg-gradient-to-r from-green-400 to-green-500' : 'bg-gradient-to-r from-orange-400 to-orange-500' }}"></div>

        <div class="px-5 pt-4 pb-4">
            {{-- Top row --}}
            <div class="flex items-start justify-between gap-3 mb-3 flex-wrap">
                <div class="flex items-center gap-2 flex-wrap">
                    <span class="font-mono text-sm font-bold text-orange-500">{{ $fr->reference }}</span>
                    @if($isDisbursed)
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-green-100 text-green-700">
                            <svg width="9" height="9" fill="#16a34a" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                            Sudah Cair
                        </span>
                        @if($fr->disbursementProofs->isEmpty())
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-red-100 text-red-600 animate-pulse">
                            <svg width="9" height="9" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 4h.01"/><circle cx="12" cy="12" r="10"/></svg>
                            Bukti Belum Diupload
                        </span>
                        @endif
                    @else
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-orange-100 text-orange-700">
                            <svg width="9" height="9" fill="#ea580c" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/></svg>
                            Belum Cair
                        </span>
                    @endif
                </div>
                <div class="text-right">
                    <div class="text-xl font-extrabold text-slate-900 font-mono">Rp {{ number_format($fr->amount, 0, ',', '.') }}</div>
                    <div class="text-[11px] text-slate-400 mt-0.5">Disetujui {{ $fr->approved_at?->format('d/m/Y') }}</div>
                </div>
            </div>

            <div class="text-[15px] font-bold text-slate-900 mb-3 leading-snug">{{ $fr->title }}</div>

            {{-- Info Grid --}}
            <div class="grid grid-cols-2 gap-x-6 gap-y-2.5 sm:grid-cols-4 mb-4">
                <div>
                    <div class="text-[10px] font-semibold text-slate-400 uppercase tracking-wide mb-0.5">Pengaju</div>
                    <div class="text-xs font-semibold text-slate-800">{{ $fr->requester->name }}</div>
                    <div class="text-[11px] text-slate-400">{{ $fr->requesterPosition->name }}</div>
                </div>
                <div>
                    <div class="text-[10px] font-semibold text-slate-400 uppercase tracking-wide mb-0.5">Departemen</div>
                    <div class="text-xs font-semibold text-slate-800">{{ $fr->department->name }}</div>
                </div>
                <div>
                    <div class="text-[10px] font-semibold text-slate-400 uppercase tracking-wide mb-0.5">Program Kerja</div>
                    <div class="text-xs font-semibold text-slate-800">{{ $fr->budgetProgram?->name ?? '-' }}</div>
                </div>
                <div>
                    <div class="text-[10px] font-semibold text-slate-400 uppercase tracking-wide mb-0.5">Organisasi</div>
                    <div class="text-xs font-semibold text-slate-800">{{ $fr->organization->name }}</div>
                </div>
            </div>

            {{-- Rekening Tujuan --}}
            @if($fr->bank_account_number)
            <div class="flex items-center gap-3 px-4 py-3 bg-blue-50 border border-blue-100 rounded-xl mb-4">
                <div class="w-8 h-8 rounded-lg bg-blue-100 flex items-center justify-center flex-shrink-0">
                    <svg width="14" height="14" fill="none" stroke="#2563eb" stroke-width="2" viewBox="0 0 24 24"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
                </div>
                <div class="flex-1 min-w-0">
                    @if($fr->bank_name)
                    <div class="text-[10px] font-bold text-blue-500 uppercase tracking-wide">{{ $fr->bank_name }}</div>
                    @endif
                    <div class="text-sm font-bold text-slate-900 font-mono">{{ $fr->bank_account_number }}</div>
                    <div class="text-xs text-slate-600">{{ $fr->bank_account_name }}</div>
                </div>
            </div>
            @else
            <div class="flex items-center gap-2 px-4 py-2.5 bg-amber-50 border border-amber-200 rounded-xl mb-4 text-xs text-amber-700">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
                Informasi rekening belum diisi pada pengajuan ini.
            </div>
            @endif

            {{-- Disbursement info if already disbursed --}}
            @if($isDisbursed)
            <div class="px-4 py-3 bg-green-50 border border-green-200 rounded-xl mb-3">
                <div class="flex items-center gap-2 mb-1.5">
                    <svg width="13" height="13" fill="none" stroke="#16a34a" stroke-width="2" viewBox="0 0 24 24"><path d="M20 6L9 17l-5-5"/></svg>
                    <span class="text-xs font-bold text-green-700">Dicairkan {{ $fr->disbursed_at->format('d/m/Y H:i') }} · {{ $fr->disbursed_by }}</span>
                </div>
                @if($fr->disburseAccount)
                <div class="flex items-center gap-1.5 text-xs text-green-700">
                    <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
                    Via: <span class="font-semibold">{{ $fr->disburseAccount->name }}</span>
                    <span class="text-green-500 font-mono text-[10px]">({{ $fr->disburseAccount->code }})</span>
                </div>
                @endif
                @if($fr->isAmountCorrected())
                <div class="flex items-center gap-1.5 text-xs text-amber-700 mt-1">
                    <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
                    Dikoreksi dari Rp {{ number_format($fr->original_amount, 0, ',', '.') }} menjadi Rp {{ number_format($fr->amount, 0, ',', '.') }}
                </div>
                @endif
                @if($fr->disbursement_notes)
                <div class="text-xs text-green-600 mt-1">{{ $fr->disbursement_notes }}</div>
                @endif
            </div>

            {{-- Receipt status --}}
            @if($fr->receipt_status === 'confirmed')
            <div class="flex items-center gap-1.5 px-3 py-2 bg-green-50 border border-green-200 rounded-lg mb-3 text-xs text-green-700">
                <svg width="12" height="12" fill="#16a34a" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                <span class="font-semibold">Dana Diterima{{ $fr->auto_confirmed ? ' (Auto)' : '' }}</span>
                @if($fr->receipt_confirmed_at)<span class="text-green-500">· {{ $fr->receipt_confirmed_at->format('d/m/Y') }}</span>@endif
            </div>
            @elseif($fr->receipt_status === 'disputed')
            <div class="px-3 py-2 bg-red-50 border border-red-200 rounded-lg mb-3">
                <div class="flex items-center gap-1.5 text-xs text-red-700 font-semibold mb-0.5">
                    <svg width="12" height="12" fill="#e11d48" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                    Ada Kendala Dilaporkan
                </div>
                @if($fr->receipt_notes)<div class="text-xs text-red-600">{{ \Illuminate\Support\Str::limit($fr->receipt_notes, 80) }}</div>@endif
            </div>
            @else
            <div class="flex items-center gap-1.5 px-3 py-2 bg-amber-50 border border-amber-200 rounded-lg mb-3 text-xs text-amber-700">
                <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
                Menunggu konfirmasi penerimaan dari pengaju
            </div>
            @endif

            {{-- Bukti pencairan --}}
            @if($fr->disbursementProofs->isNotEmpty())
            <div class="mb-3">
                <div class="text-[10px] font-semibold text-slate-400 uppercase tracking-wide mb-1.5">Bukti Pencairan</div>
                <div class="flex flex-wrap gap-1.5">
                    @foreach($fr->disbursementProofs as $proof)
                    <div class="inline-flex items-center gap-1.5">
                        <a href="{{ $proof->url }}" target="_blank"
                            class="inline-flex items-center gap-1 px-2.5 py-1 rounded text-[10px] font-semibold bg-blue-50 text-blue-600 hover:bg-blue-100 no-underline">
                            <svg width="10" height="10" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                            {{ $proof->file_name }}
                        </a>
                        <form method="POST" action="{{ route('finance.delete-proof', $proof) }}" class="inline-block">
                            @csrf @method('DELETE')
                            <button type="submit" onclick="return confirm('Hapus bukti ini?')"
                                class="inline-flex items-center justify-center w-5 h-5 rounded text-[10px] bg-red-50 text-red-500 hover:bg-red-100 border-0 cursor-pointer">
                                <svg width="9" height="9" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/></svg>
                            </button>
                        </form>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
            @endif

            {{-- Actions --}}
            <div class="flex items-center gap-2 pt-3 border-t border-slate-100 flex-wrap">
                <a href="{{ route('fund-requests.show', $fr) }}"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold bg-slate-100 text-slate-600 hover:bg-slate-200 transition-colors no-underline">
                    <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                    Lihat Detail
                </a>
                @if(!$isDisbursed)
                <button type="button"
                    class="btn-disburse inline-flex items-center gap-1.5 px-4 py-1.5 rounded-lg text-xs font-semibold bg-gradient-to-br from-blue-500 to-blue-600 text-white border-0 cursor-pointer hover:opacity-90 transition-opacity shadow-sm"
                    data-disburse-url="{{ route('finance.disburse', $fr) }}"
                    data-ref="{{ $fr->reference }}"
                    data-amount="Rp {{ number_format($fr->amount, 0, ',', '.') }}"
                    data-amount-raw="{{ $fr->amount }}"
                    data-organization-id="{{ $fr->organization_id }}">
                    <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
                    Cairkan
                </button>
                @else
                <button type="button"
                    class="btn-upload-proof inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold transition-colors border-0 cursor-pointer
                        {{ $fr->disbursementProofs->isEmpty()
                            ? 'bg-gradient-to-br from-red-500 to-red-600 text-white shadow-sm hover:opacity-90'
                            : 'bg-purple-50 text-purple-600 hover:bg-purple-100' }}"
                    data-upload-url="{{ route('finance.upload-proof', $fr) }}"
                    data-ref="{{ $fr->reference }}">
                    <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                    Upload Bukti
                </button>
                @endif
            </div>
        </div>
    </div>
    @endforeach
</div>

{{-- Pagination --}}
<div class="mt-4 flex items-center justify-between gap-3 flex-wrap">
    <span class="text-xs text-slate-400">
        Menampilkan {{ $fundRequests->firstItem() ?? 0 }}–{{ $fundRequests->lastItem() ?? 0 }} dari {{ $fundRequests->total() }} pencairan
    </span>
@if($fundRequests->hasPages())
<div class="flex justify-end gap-1">
    @if($fundRequests->onFirstPage())
        <span class="inline-flex items-center px-2.5 py-1.5 border border-slate-200 rounded-lg text-xs text-slate-300 pointer-events-none">&laquo;</span>
    @else
        <a href="{{ $fundRequests->previousPageUrl() }}" class="inline-flex items-center px-2.5 py-1.5 border border-slate-200 rounded-lg text-xs text-slate-500 no-underline bg-white">&laquo;</a>
    @endif
    @foreach($fundRequests->getUrlRange(max(1,$fundRequests->currentPage()-2), min($fundRequests->lastPage(),$fundRequests->currentPage()+2)) as $page => $url)
        <a href="{{ $url }}" class="inline-flex items-center px-2.5 py-1.5 border rounded-lg text-xs no-underline {{ $page == $fundRequests->currentPage() ? 'bg-blue-600 border-blue-600 text-white' : 'bg-white border-slate-200 text-slate-500' }}">{{ $page }}</a>
    @endforeach
    @if($fundRequests->hasMorePages())
        <a href="{{ $fundRequests->nextPageUrl() }}" class="inline-flex items-center px-2.5 py-1.5 border border-slate-200 rounded-lg text-xs text-slate-500 no-underline bg-white">&raquo;</a>
    @else
        <span class="inline-flex items-center px-2.5 py-1.5 border border-slate-200 rounded-lg text-xs text-slate-300 pointer-events-none">&raquo;</span>
    @endif
</div>
@endif
</div>
@endif

{{-- Proof Upload Modal --}}
<div class="fixed inset-0 z-[999] bg-slate-900/50 backdrop-blur-sm items-center justify-center" id="proof-upload-overlay" style="display:none;">
    <div class="bg-white rounded-2xl w-[420px] max-w-[90vw] shadow-2xl overflow-hidden">
        <div class="px-6 py-5 border-b border-slate-100">
            <div class="text-sm font-bold text-purple-700">Upload Bukti Pencairan</div>
            <div id="proof-ref" class="text-xs text-slate-400 mt-0.5"></div>
        </div>
        <form id="proof-form" method="POST" action="" enctype="multipart/form-data">
            @csrf
            <div class="px-6 py-5">
                <label class="text-xs font-semibold text-slate-600 block mb-1.5">File Bukti <span class="text-red-500">*</span></label>
                <input type="file" name="file" id="proof-file-input" required accept=".pdf,.jpg,.jpeg,.png"
                    class="w-full text-sm text-slate-600 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-purple-50 file:text-purple-600 hover:file:bg-purple-100 cursor-pointer">
                <div class="text-[10px] text-slate-400 mt-1.5">Format: PDF, JPG, PNG · Maks. 10 MB</div>
            </div>
            <div class="px-6 py-4 border-t border-slate-100 flex gap-2 justify-end">
                <button type="button" id="proof-cancel" class="px-4 py-2.5 rounded-xl bg-slate-100 text-slate-700 border border-slate-200 text-sm font-medium cursor-pointer hover:bg-slate-200 transition-colors">Batal</button>
                <button type="submit" class="inline-flex items-center gap-1.5 px-5 py-2.5 rounded-xl text-sm font-semibold bg-gradient-to-br from-purple-500 to-purple-600 text-white border-0 cursor-pointer hover:opacity-90 transition-opacity shadow-sm">
                    <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                    Upload
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Disburse Modal --}}
<div class="fixed inset-0 z-[999] bg-slate-900/50 backdrop-blur-sm items-center justify-center" id="disburse-overlay" style="display:none;">
    <div class="bg-white rounded-2xl w-[480px] max-w-[90vw] shadow-2xl flex flex-col" style="max-height:88vh;">
        <div class="px-6 py-5 border-b border-slate-100 flex items-center gap-3 shrink-0">
            <div class="w-9 h-9 rounded-xl bg-blue-100 flex items-center justify-center flex-shrink-0">
                <svg width="18" height="18" fill="none" stroke="#2563eb" stroke-width="2.5" viewBox="0 0 24 24"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
            </div>
            <div>
                <div class="text-sm font-bold text-blue-700">Konfirmasi Pencairan</div>
                <div id="disburse-ref" class="text-[11px] text-slate-500 mt-0.5"></div>
            </div>
        </div>
        <form id="disburse-form" method="POST" action="" enctype="multipart/form-data" class="flex flex-col overflow-hidden" style="min-height:0;">
            @csrf
            <div class="px-6 py-5 flex flex-col gap-4 overflow-y-auto" style="min-height:0;">

                {{-- Tanggal Pencairan --}}
                <div>
                    <label class="text-xs font-semibold text-slate-600 block mb-1.5">Tanggal Pencairan <span class="text-red-500">*</span></label>
                    <input type="date" name="disbursed_at" id="disburse-date-input" required
                        class="w-full px-3 py-2.5 border border-slate-200 rounded-xl text-sm text-slate-800 outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100 transition-colors">
                </div>

                {{-- Nominal --}}
                <div>
                    <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Jumlah Pencairan</div>
                    <div class="relative">
                        <span class="absolute left-0 top-1/2 -translate-y-1/2 text-slate-400 text-xl">Rp</span>
                        <input type="text" id="disburse-amount-display" inputmode="numeric"
                            class="w-full pl-8 py-0 border-0 border-b-2 border-transparent focus:border-blue-400 text-2xl font-extrabold text-slate-900 font-mono outline-none bg-transparent transition-colors">
                        <input type="hidden" name="amount" id="disburse-amount-input">
                    </div>
                    <div class="flex items-center justify-between mt-1 gap-2">
                        <div class="text-[11px] text-slate-400">Disetujui: <span id="disburse-approved-amount" class="font-mono font-semibold text-slate-500"></span></div>
                        <div id="disburse-amount-error" class="text-[11px] text-red-500 font-semibold hidden">Tidak boleh melebihi nominal disetujui</div>
                    </div>
                    <p class="text-[11px] text-slate-400 mt-1">Boleh dikurangi kalau lampiran/kuitansi ternyata lebih kecil dari pengajuan -- tidak bisa dinaikkan.</p>
                </div>

                {{-- Pilih bank --}}
                <div>
                    <label class="text-xs font-semibold text-slate-600 block mb-1.5">
                        Rekening Bank Pengirim <span class="text-red-500">*</span>
                    </label>
                    @if($bankAccounts->isEmpty())
                    <div class="flex items-center gap-2 px-3 py-2.5 bg-amber-50 border border-amber-200 rounded-xl text-xs text-amber-700">
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
                        Tidak ada akun REKENING BANK di Bagan Akun. Tambahkan dulu di Bagan Akun.
                    </div>
                    @else
                    <select name="disburse_account_id" id="disburse-account-select" required
                        class="w-full px-3 py-2.5 border border-slate-200 rounded-xl text-sm text-slate-800 bg-white outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100 transition-colors">
                        <option value="">— Pilih Rekening Bank —</option>
                    </select>
                    <script id="disburse-account-data" type="application/json">
                        {!! $bankAccounts->map(fn($a) => ['id' => $a->id, 'code' => $a->code, 'name' => $a->name, 'balance' => $a->balance, 'organization_id' => $a->organization_id])->values()->toJson() !!}
                    </script>
                    {{-- Info akun terpilih --}}
                    <div id="disburse-account-info" class="mt-2 hidden">
                        <div class="flex items-center gap-2 px-3 py-2 bg-blue-50 border border-blue-100 rounded-lg text-xs text-blue-700">
                            <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
                            <span id="disburse-account-code" class="font-mono font-semibold"></span>
                            <span id="disburse-account-name" class="font-medium"></span>
                        </div>
                    </div>
                    {{-- Peringatan saldo kurang/kosong --}}
                    <div id="disburse-balance-warning" class="mt-2 hidden">
                        <div class="flex items-start gap-2 px-3 py-2.5 bg-red-50 border border-red-200 rounded-xl text-xs text-red-700">
                            <svg width="14" height="14" class="flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
                            <div>
                                <span id="disburse-balance-warning-text">Saldo rekening ini tidak cukup untuk pencairan ini.</span>
                                @if($canRequestTopup ?? false)
                                <a href="{{ route('cash-topup-requests.create') }}" class="font-semibold underline">Ajukan saldo ke Yayasan &rarr;</a>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endif
                </div>

                {{-- Catatan --}}
                <div>
                    <label class="text-xs font-semibold text-slate-600 block mb-1.5">Catatan Pencairan <span id="disburse-notes-optional" class="text-slate-400 font-normal">(opsional)</span></label>
                    <textarea name="disbursement_notes" id="disburse-notes" rows="2"
                        class="w-full px-3 py-2.5 border border-slate-200 rounded-xl text-sm outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100 transition-colors resize-none"
                        placeholder="Nomor bukti transfer, catatan tambahan..."></textarea>
                    <p id="disburse-notes-required-hint" class="text-[11px] text-red-500 mt-1 hidden">Wajib diisi -- jelaskan alasan koreksi nominal.</p>
                </div>

                {{-- Upload bukti transfer (opsional, sekalian pas cairkan) --}}
                <div>
                    <label class="text-xs font-semibold text-slate-600 block mb-1.5">Bukti Transfer <span class="text-slate-400 font-normal">(opsional, bisa diupload belakangan)</span></label>
                    <input type="file" name="proof_file" id="disburse-proof-input" accept=".pdf,.jpg,.jpeg,.png"
                        class="w-full text-sm text-slate-600 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-600 hover:file:bg-blue-100 cursor-pointer">
                    <div class="text-[10px] text-slate-400 mt-1.5">Kalau bukti sudah ada saat ini, langsung upload sekalian. Format: PDF, JPG, PNG · Maks. 10 MB</div>
                </div>
            </div>

            <div class="px-6 py-4 border-t border-slate-100 flex gap-2 justify-end shrink-0">
                <button type="button" id="disburse-cancel" class="px-4 py-2.5 rounded-xl bg-slate-100 text-slate-700 border border-slate-200 text-sm font-medium cursor-pointer hover:bg-slate-200 transition-colors">Batal</button>
                <button type="submit" class="inline-flex items-center gap-1.5 px-5 py-2.5 rounded-xl text-sm font-semibold bg-gradient-to-br from-blue-500 to-blue-600 text-white border-0 cursor-pointer hover:opacity-90 transition-opacity shadow-sm">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
                    Ya, Cairkan Sekarang
                </button>
            </div>
        </form>
    </div>
</div>

<script>
(function () {
    // Disburse modal
    var overlay  = document.getElementById('disburse-overlay');
    var form     = document.getElementById('disburse-form');
    var accSel   = document.getElementById('disburse-account-select');
    var accInfo  = document.getElementById('disburse-account-info');
    var accWarn  = document.getElementById('disburse-balance-warning');
    var submitBtn = form.querySelector('button[type="submit"]');
    var approvedAmount = 0; // nominal yang sudah disetujui -- batas atas, tidak boleh dilewati
    var editedAmount   = 0; // nominal yang sedang diketik/dipakai buat cek saldo & submit
    var allAccounts = [];
    try {
        allAccounts = JSON.parse(document.getElementById('disburse-account-data').textContent || '[]');
    } catch (e) { allAccounts = []; }

    var accWarnText  = document.getElementById('disburse-balance-warning-text');
    var orgAccounts  = [];
    var amountDisplay = document.getElementById('disburse-amount-display');
    var amountInput   = document.getElementById('disburse-amount-input');
    var amountError   = document.getElementById('disburse-amount-error');
    var notesTextarea = document.getElementById('disburse-notes');
    var notesOptional = document.getElementById('disburse-notes-optional');
    var notesRequiredHint = document.getElementById('disburse-notes-required-hint');

    function fmtRupiah(n) { return 'Rp ' + Number(n).toLocaleString('id-ID'); }

    function isAmountCorrected() { return Math.abs(editedAmount - approvedAmount) > 0.01; }

    function updateNotesRequirement() {
        var corrected = isAmountCorrected();
        notesTextarea.required = corrected;
        notesOptional.classList.toggle('hidden', corrected);
        var notesFilled = notesTextarea.value.trim().length > 0;
        notesRequiredHint.classList.toggle('hidden', !corrected || notesFilled);
    }

    function setAmount(raw) {
        var val = parseInt(raw || '0', 10) || 0;
        var overLimit = val > approvedAmount;
        editedAmount = overLimit ? approvedAmount : val;

        // Nilai yang di-clamp direfleksikan balik ke input supaya user lihat batasnya --
        // submit tidak pernah digantung ke kondisi ini karena nilainya selalu sudah valid
        // setelah di-clamp; checkBalance() di bawah yang jadi satu-satunya penentu disabled.
        amountDisplay.value = editedAmount ? editedAmount.toLocaleString('id-ID') : '';
        amountInput.value = editedAmount || '';
        amountError.classList.toggle('hidden', !overLimit);

        updateNotesRequirement();
        checkBalance();
    }

    function populateAccounts(organizationId) {
        if (!accSel) return;
        accSel.innerHTML = '<option value="">— Pilih Rekening Bank —</option>';
        orgAccounts = allAccounts.filter(function (a) { return a.organization_id === organizationId; });
        orgAccounts.forEach(function (a) {
            var opt = document.createElement('option');
            opt.value = a.id;
            opt.dataset.code = a.code;
            opt.dataset.balance = a.balance;
            opt.textContent = a.name + ' (' + (Number(a.balance) <= 0 ? 'Saldo kosong' : 'Saldo ' + fmtRupiah(a.balance)) + ')';
            accSel.appendChild(opt);
        });
    }

    function closeDisburse() {
        overlay.style.display = 'none';
        notesTextarea.value = '';
        notesTextarea.required = false;
        notesOptional.classList.remove('hidden');
        notesRequiredHint.classList.add('hidden');
        amountError.classList.add('hidden');
        var proofInput = document.getElementById('disburse-proof-input');
        if (proofInput) proofInput.value = '';
        if (accSel) { accSel.value = ''; if (accInfo) accInfo.classList.add('hidden'); if (accWarn) accWarn.classList.add('hidden'); }
    }

    function showWarning(text) {
        if (accWarnText) accWarnText.textContent = text;
        if (accWarn) accWarn.classList.remove('hidden');
    }

    function hideWarning() {
        if (accWarn) accWarn.classList.add('hidden');
    }

    function checkBalance() {
        if (accSel && accSel.value) {
            // Rekening tertentu sudah dipilih -- cek saldo rekening itu saja.
            var opt = accSel.options[accSel.selectedIndex];
            var balance = parseFloat(opt.dataset.balance || '0');
            var insufficient = balance < editedAmount;
            if (insufficient) {
                showWarning(balance <= 0
                    ? 'Saldo rekening ini kosong.'
                    : 'Saldo rekening ini (' + fmtRupiah(balance) + ') tidak cukup untuk pencairan ini.');
            } else {
                hideWarning();
            }
            if (submitBtn) submitBtn.disabled = insufficient;
            return;
        }

        // Belum pilih rekening -- kasih tau lebih awal kalau SEMUA rekening organisasi ini
        // saldonya tidak cukup, tanpa nunggu user pilih satu-satu dulu.
        if (submitBtn) submitBtn.disabled = false;
        if (orgAccounts.length === 0) {
            showWarning('Organisasi ini belum punya rekening bank yang terdaftar.');
            return;
        }
        var maxBalance = Math.max.apply(null, orgAccounts.map(function (a) { return parseFloat(a.balance || 0); }));
        if (maxBalance < editedAmount) {
            showWarning(maxBalance <= 0
                ? 'Semua rekening organisasi ini saldonya kosong.'
                : 'Saldo tertinggi di organisasi ini cuma ' + fmtRupiah(maxBalance) + ', belum cukup untuk pencairan ini.');
        } else {
            hideWarning();
        }
    }

    if (amountDisplay) {
        amountDisplay.addEventListener('input', function () {
            setAmount(this.value.replace(/[^\d]/g, ''));
        });
    }
    if (notesTextarea) {
        notesTextarea.addEventListener('input', updateNotesRequirement);
    }

    document.querySelectorAll('.btn-disburse').forEach(function (btn) {
        btn.addEventListener('click', function () {
            form.action = btn.dataset.disburseUrl;
            approvedAmount = parseFloat(btn.dataset.amountRaw || '0');
            document.getElementById('disburse-ref').textContent = btn.dataset.ref;
            document.getElementById('disburse-approved-amount').textContent = btn.dataset.amount;
            var dateInput = document.getElementById('disburse-date-input');
            if (dateInput) dateInput.value = new Date().toLocaleDateString('sv-SE');
            setAmount(approvedAmount);
            populateAccounts(btn.dataset.organizationId);
            overlay.style.display = 'flex';
            checkBalance();
        });
    });

    if (accSel) {
        accSel.addEventListener('change', function () {
            var opt = this.options[this.selectedIndex];
            if (this.value && accInfo) {
                document.getElementById('disburse-account-code').textContent = opt.dataset.code;
                document.getElementById('disburse-account-name').textContent = opt.text;
                accInfo.classList.remove('hidden');
            } else if (accInfo) {
                accInfo.classList.add('hidden');
            }
            checkBalance();
        });
    }

    document.getElementById('disburse-cancel').addEventListener('click', closeDisburse);
    overlay.addEventListener('click', function (e) { if (e.target === e.currentTarget) closeDisburse(); });

    // Proof upload modal
    var proofOverlay = document.getElementById('proof-upload-overlay');
    var proofForm    = document.getElementById('proof-form');

    function closeProof() {
        proofOverlay.style.display = 'none';
        proofForm.reset();
    }

    document.querySelectorAll('.btn-upload-proof').forEach(function (btn) {
        btn.addEventListener('click', function () {
            proofForm.action = btn.dataset.uploadUrl;
            document.getElementById('proof-ref').textContent = btn.dataset.ref;
            proofOverlay.style.display = 'flex';
        });
    });

    document.getElementById('proof-cancel').addEventListener('click', closeProof);
    proofOverlay.addEventListener('click', function (e) { if (e.target === e.currentTarget) closeProof(); });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') { closeDisburse(); closeProof(); }
    });
})();
</script>
</x-layouts.app>
