<x-layouts.app title="Detail Pengajuan Saldo">

@if(session('success'))
<div class="flex items-center gap-2.5 px-4 py-3 bg-green-50 border border-green-200 rounded-xl mb-4 text-sm text-green-700">
    {{ session('success') }}
</div>
@endif
@if(session('warning'))
<div class="flex items-center gap-2.5 px-4 py-3 bg-amber-50 border border-amber-200 rounded-xl mb-4 text-sm text-amber-700">
    {{ session('warning') }}
</div>
@endif
@if($errors->any())
<div class="px-4 py-3 bg-red-50 border border-red-200 rounded-xl mb-4 text-sm text-red-700">
    @foreach($errors->all() as $error)
        <div>{{ $error }}</div>
    @endforeach
</div>
@endif

@php
    $statusStyle = [
        'pending'  => ['bg' => 'bg-orange-100', 'text' => 'text-orange-700', 'label' => 'Menunggu Approval'],
        'approved' => ['bg' => 'bg-green-100', 'text' => 'text-green-700', 'label' => 'Disetujui'],
        'rejected' => ['bg' => 'bg-red-100', 'text' => 'text-red-600', 'label' => 'Ditolak'],
    ][$cashTopupRequest->status];
@endphp

<div class="flex items-start justify-between gap-4 mb-5 flex-wrap">
    <div>
        <div class="flex items-center gap-2 mb-1">
            <h2 class="text-lg font-bold text-slate-900 m-0">{{ $cashTopupRequest->reference }}</h2>
            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold {{ $statusStyle['bg'] }} {{ $statusStyle['text'] }}">
                {{ $statusStyle['label'] }}
            </span>
            @if($cashTopupRequest->isYayasanInitiated())
            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-purple-100 text-purple-700">
                Diisi Langsung oleh Yayasan
            </span>
            @endif
        </div>
        <p class="text-xs text-slate-400 m-0">
            {{ $cashTopupRequest->isYayasanInitiated() ? 'Saldo diisi langsung ke' : 'Pengajuan saldo dari' }}
            {{ $cashTopupRequest->requestingOrganization->name }}
        </p>
    </div>
    <a href="{{ route('cash-topup-requests.index') }}"
        class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-sm font-medium bg-slate-100 text-slate-600 no-underline hover:bg-slate-200 transition-colors">
        &larr; Kembali
    </a>
</div>

<div class="bg-white rounded-xl shadow-sm p-5 mb-4">
    <div class="grid grid-cols-2 gap-x-6 gap-y-4 sm:grid-cols-3">
        <div>
            <div class="text-[10px] font-semibold text-slate-400 uppercase tracking-wide mb-0.5">Jumlah Diajukan</div>
            <div class="text-xl font-extrabold text-slate-900 font-mono">Rp {{ number_format($cashTopupRequest->amount, 0, ',', '.') }}</div>
        </div>
        <div>
            <div class="text-[10px] font-semibold text-slate-400 uppercase tracking-wide mb-0.5">Rekening Tujuan</div>
            <div class="text-sm font-semibold text-slate-800">{{ $cashTopupRequest->targetAccount->name ?? '-' }}</div>
        </div>
        <div>
            <div class="text-[10px] font-semibold text-slate-400 uppercase tracking-wide mb-0.5">Akun Lawan ({{ $cashTopupRequest->requestingOrganization->name }})</div>
            <div class="text-sm font-semibold text-slate-800">{{ $cashTopupRequest->sourceCreditAccount->name ?? '-' }}</div>
        </div>
        <div>
            <div class="text-[10px] font-semibold text-slate-400 uppercase tracking-wide mb-0.5">{{ $cashTopupRequest->isYayasanInitiated() ? 'Diisi Oleh' : 'Diajukan Oleh' }}</div>
            <div class="text-sm font-semibold text-slate-800">{{ $cashTopupRequest->requestedBy->name ?? '-' }}</div>
        </div>
        <div>
            <div class="text-[10px] font-semibold text-slate-400 uppercase tracking-wide mb-0.5">{{ $cashTopupRequest->isYayasanInitiated() ? 'Tanggal Diisi' : 'Tanggal Diajukan' }}</div>
            <div class="text-sm font-semibold text-slate-800">{{ $cashTopupRequest->created_at->format('d/m/Y H:i') }}</div>
        </div>
        @if($cashTopupRequest->notes)
        <div class="col-span-2 sm:col-span-3">
            <div class="text-[10px] font-semibold text-slate-400 uppercase tracking-wide mb-0.5">Catatan</div>
            <div class="text-sm text-slate-700">{{ $cashTopupRequest->notes }}</div>
        </div>
        @endif
    </div>
</div>

@if($cashTopupRequest->fundRequests->isNotEmpty())
<div class="bg-white rounded-xl shadow-sm p-5 mb-4">
    <div class="text-sm font-bold text-slate-900 mb-3">Pengajuan Dana Terkait</div>
    <div class="flex flex-col gap-2">
        @foreach($cashTopupRequest->fundRequests as $fr)
        <div class="flex items-center justify-between gap-3 px-3 py-2.5 border border-slate-100 rounded-xl flex-wrap">
            <div class="min-w-0">
                <span class="font-mono text-xs font-bold text-orange-500">{{ $fr->reference }}</span>
                <span class="text-xs text-slate-700 ml-2">{{ $fr->title }}</span>
                <div class="text-[11px] text-slate-400">{{ $fr->department->name ?? '-' }} · {{ $fr->budgetProgram->name ?? '-' }}</div>
            </div>
            <span class="text-sm font-bold text-slate-900 font-mono">Rp {{ number_format($fr->amount, 0, ',', '.') }}</span>
        </div>
        @endforeach
    </div>
</div>
@endif

@if($cashTopupRequest->isApproved())
<div class="bg-white rounded-xl shadow-sm p-5 mb-4">
    <div class="text-sm font-bold text-green-700 mb-3 flex items-center gap-1.5">
        <svg width="14" height="14" fill="none" stroke="#16a34a" stroke-width="2" viewBox="0 0 24 24"><path d="M20 6L9 17l-5-5"/></svg>
        Disetujui Yayasan
    </div>
    <div class="grid grid-cols-2 gap-x-6 gap-y-4 sm:grid-cols-3 mb-3">
        <div>
            <div class="text-[10px] font-semibold text-slate-400 uppercase tracking-wide mb-0.5">Rekening Sumber Yayasan</div>
            <div class="text-sm font-semibold text-slate-800">{{ $cashTopupRequest->yayasanSourceAccount->name ?? '-' }}</div>
        </div>
        <div>
            <div class="text-[10px] font-semibold text-slate-400 uppercase tracking-wide mb-0.5">Akun Lawan Yayasan</div>
            <div class="text-sm font-semibold text-slate-800">{{ $cashTopupRequest->yayasanDebitAccount->name ?? '-' }}</div>
        </div>
        <div>
            <div class="text-[10px] font-semibold text-slate-400 uppercase tracking-wide mb-0.5">Disetujui Oleh</div>
            <div class="text-sm font-semibold text-slate-800">{{ $cashTopupRequest->reviewer->name ?? $cashTopupRequest->reviewer->email ?? '-' }}</div>
            <div class="text-[11px] text-slate-400">{{ $cashTopupRequest->reviewed_at?->format('d/m/Y H:i') }}</div>
        </div>
    </div>
    @if($cashTopupRequest->review_notes)
    <div class="text-xs text-slate-500 mb-3">{{ $cashTopupRequest->review_notes }}</div>
    @endif
    @if($cashTopupRequest->proof_path)
    <a href="{{ $cashTopupRequest->proof_url }}" target="_blank"
        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold bg-blue-50 text-blue-600 hover:bg-blue-100 no-underline">
        <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
        {{ $cashTopupRequest->proof_name }}
    </a>
    @endif
</div>
@endif

@if($cashTopupRequest->isRejected())
<div class="bg-white rounded-xl shadow-sm p-5 mb-4">
    <div class="text-sm font-bold text-red-600 mb-2">Ditolak Yayasan</div>
    <div class="text-xs text-slate-500 mb-1">{{ $cashTopupRequest->review_notes }}</div>
    <div class="text-[11px] text-slate-400">
        oleh {{ $cashTopupRequest->reviewer->name ?? $cashTopupRequest->reviewer->email ?? '-' }} · {{ $cashTopupRequest->reviewed_at?->format('d/m/Y H:i') }}
    </div>
</div>
@endif

@if($canApprove)
<div class="bg-white rounded-xl shadow-sm p-5">
    <div class="text-sm font-bold text-slate-900 mb-3">Approval Yayasan</div>

    <form method="POST" action="{{ route('cash-topup-requests.approve', $cashTopupRequest) }}" enctype="multipart/form-data" class="flex flex-col gap-4 mb-5">
        @csrf
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="text-xs font-semibold text-slate-600 block mb-1.5">Rekening Sumber Yayasan <span class="text-red-500">*</span></label>
                <select name="yayasan_source_account_id" required
                    class="w-full px-3 py-2.5 border border-slate-200 rounded-xl text-sm text-slate-800 bg-white outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100 transition-colors">
                    <option value="">— Pilih Rekening —</option>
                    @foreach($yayasanAccounts as $acc)
                    <option value="{{ $acc->id }}">{{ $acc->name }} (saldo: Rp {{ number_format($acc->balance, 0, ',', '.') }})</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="text-xs font-semibold text-slate-600 block mb-1.5">Akun Lawan Yayasan <span class="text-red-500">*</span></label>
                <select name="yayasan_debit_account_id" required
                    class="w-full px-3 py-2.5 border border-slate-200 rounded-xl text-sm text-slate-800 bg-white outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100 transition-colors">
                    <option value="">— Pilih Akun —</option>
                    @foreach($yayasanAccounts as $acc)
                    <option value="{{ $acc->id }}">{{ $acc->code }} — {{ $acc->name }}</option>
                    @endforeach
                </select>
                <p class="text-[11px] text-slate-400 mt-1">Contoh: akun "Piutang ke {{ $cashTopupRequest->requestingOrganization->name }}".</p>
            </div>
        </div>
        <div>
            <label class="text-xs font-semibold text-slate-600 block mb-1.5">Bukti Transfer <span class="text-red-500">*</span></label>
            <input type="file" name="proof" required accept=".pdf,.jpg,.jpeg,.png"
                class="w-full px-3 py-2.5 border border-slate-200 rounded-xl text-sm text-slate-800 bg-white outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100 transition-colors">
        </div>
        <div>
            <label class="text-xs font-semibold text-slate-600 block mb-1.5">Catatan <span class="text-slate-400 font-normal">(opsional)</span></label>
            <textarea name="review_notes" rows="2"
                class="w-full px-3 py-2.5 border border-slate-200 rounded-xl text-sm outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100 transition-colors resize-none"></textarea>
        </div>
        <div class="flex justify-end">
            <button type="submit"
                class="inline-flex items-center gap-1.5 px-5 py-2.5 rounded-xl text-sm font-semibold bg-gradient-to-br from-green-500 to-green-600 text-white border-0 cursor-pointer hover:opacity-90 transition-opacity shadow-sm">
                Setujui & Transfer
            </button>
        </div>
    </form>

    <form method="POST" action="{{ route('cash-topup-requests.reject', $cashTopupRequest) }}" class="flex flex-col gap-3 pt-4 border-t border-slate-100">
        @csrf
        <div>
            <label class="text-xs font-semibold text-slate-600 block mb-1.5">Alasan Penolakan <span class="text-red-500">*</span></label>
            <textarea name="review_notes" rows="2" required
                class="w-full px-3 py-2.5 border border-slate-200 rounded-xl text-sm outline-none focus:border-red-400 focus:ring-2 focus:ring-red-100 transition-colors resize-none"></textarea>
        </div>
        <div class="flex justify-end">
            <button type="submit"
                class="inline-flex items-center gap-1.5 px-5 py-2.5 rounded-xl text-sm font-semibold bg-red-50 text-red-600 border-0 cursor-pointer hover:bg-red-100 transition-colors">
                Tolak Pengajuan
            </button>
        </div>
    </form>
</div>
@endif

</x-layouts.app>
