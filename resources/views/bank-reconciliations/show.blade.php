<x-layouts.app title="Detail Rekonsiliasi Bank">

<a href="{{ route('bank-reconciliations.index', ['organization_id' => $bankReconciliation->organization_id]) }}" class="inline-flex items-center gap-1.5 text-sm text-slate-500 hover:text-orange-500 mb-5 no-underline">
    <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M19 12H5M12 5l-7 7 7 7"/></svg>
    Kembali ke Rekonsiliasi Bank
</a>

<div class="flex items-start justify-between gap-4 mb-5 flex-wrap">
    <div>
        <h2 class="text-lg font-bold text-slate-900 m-0 mb-1">
            Rekonsiliasi {{ $bankReconciliation->period->translatedFormat('F Y') }}
            @if($bankReconciliation->status === 'selesai')
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-green-100 text-green-700 align-middle ml-1">Selesai</span>
            @else
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-amber-100 text-amber-700 align-middle ml-1">Draft</span>
            @endif
        </h2>
        <p class="text-xs text-slate-400 m-0">{{ $bankReconciliation->account->code }} — {{ $bankReconciliation->account->name }}</p>
    </div>
</div>

@if(session('success'))
<div class="flex items-center gap-2.5 px-4 py-3 bg-green-50 border border-green-200 rounded-xl mb-4 text-sm text-green-700">{{ session('success') }}</div>
@endif
@if(session('info'))
<div class="flex items-center gap-2.5 px-4 py-3 bg-blue-50 border border-blue-200 rounded-xl mb-4 text-sm text-blue-700">{{ session('info') }}</div>
@endif
@error('item')<div class="flex items-center gap-2.5 px-4 py-3 bg-red-50 border border-red-200 rounded-xl mb-4 text-sm text-red-700">{{ $message }}</div>@enderror
@error('complete')<div class="flex items-center gap-2.5 px-4 py-3 bg-red-50 border border-red-200 rounded-xl mb-4 text-sm text-red-700">{{ $message }}</div>@enderror

{{-- Ringkasan status --}}
<div class="rounded-xl shadow-sm px-4 py-3.5 mb-4 flex items-center gap-3 {{ $isMatched ? 'bg-emerald-50 border border-emerald-100' : 'bg-red-50 border border-red-100' }}">
    @if($isMatched)
    <svg width="24" height="24" fill="none" stroke="#059669" stroke-width="2" viewBox="0 0 24 24" class="shrink-0"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
    <div>
        <div class="text-xs font-bold text-emerald-700">Cocok</div>
        <div class="text-[11px] text-emerald-600">Saldo buku (disesuaikan) = saldo bank (disesuaikan)</div>
    </div>
    @else
    <svg width="24" height="24" fill="none" stroke="#dc2626" stroke-width="2" viewBox="0 0 24 24" class="shrink-0"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m0 3.75h.008v.008H12v-.008ZM21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
    <div>
        <div class="text-xs font-bold text-red-700">Belum Cocok</div>
        <div class="text-[11px] text-red-600">Selisih Rp {{ number_format(abs($difference), 0, ',', '.') }} — tambahkan item penyesuai di bawah</div>
    </div>
    @endif
</div>

<div class="grid lg:grid-cols-2 gap-4 mb-4">
    {{-- Sisi Buku --}}
    <div class="bg-white rounded-xl shadow-sm overflow-hidden self-start">
        <div class="px-5 py-3 bg-slate-50 border-b border-slate-100">
            <span class="text-xs font-bold text-slate-500 uppercase tracking-wide">Saldo Menurut Buku (Aplikasi)</span>
        </div>
        <div class="divide-y divide-slate-50 text-sm">
            <div class="flex items-center justify-between px-5 py-2.5">
                <span class="text-slate-600">Saldo Buku (per {{ $bankReconciliation->periodEnd()->translatedFormat('d M Y') }})</span>
                <span class="font-mono text-slate-800">Rp {{ number_format($bookBalance, 0, ',', '.') }}</span>
            </div>
            @forelse($bukuItems as $item)
            <div class="flex items-center justify-between gap-2 px-5 py-2.5">
                <div class="min-w-0">
                    <div class="text-slate-700 truncate">{{ $item->description }}</div>
                    <div class="text-[10px] text-slate-400 mt-0.5">
                        {{ $item->counterAccount->code }} — {{ $item->counterAccount->name }}
                        @if($item->isPosted())
                            <span class="text-emerald-600 font-semibold ml-1">· sudah diposting ({{ $item->journalEntry->reference }})</span>
                        @else
                            <span class="text-amber-600 font-semibold ml-1">· belum diposting</span>
                        @endif
                    </div>
                </div>
                <div class="flex items-center gap-2 shrink-0">
                    <span class="font-mono {{ $item->amount >= 0 ? 'text-green-700' : 'text-red-700' }}">{{ $item->amount >= 0 ? '' : '-' }}Rp {{ number_format(abs($item->amount), 0, ',', '.') }}</span>
                    @if($bankReconciliation->isDraft() && !$item->isPosted())
                    <form method="POST" action="{{ route('bank-reconciliations.items.destroy', $item) }}" onsubmit="return confirm('Hapus item ini?');">
                        @csrf @method('DELETE')
                        <button type="submit" class="text-red-400 hover:text-red-600 border-0 bg-transparent cursor-pointer text-xs">✕</button>
                    </form>
                    @endif
                </div>
            </div>
            @empty
            <div class="px-5 py-3 text-[12px] text-slate-400 italic">Belum ada item penyesuai.</div>
            @endforelse
            <div class="flex items-center justify-between px-5 py-3 bg-slate-50/70">
                <span class="font-bold text-slate-700">Saldo Buku Disesuaikan</span>
                <span class="font-mono font-bold text-slate-900">Rp {{ number_format($adjustedBook, 0, ',', '.') }}</span>
            </div>
        </div>

        @if($bankReconciliation->isDraft())
        <div class="p-4 border-t border-slate-100">
            <form method="POST" action="{{ route('bank-reconciliations.items.store', $bankReconciliation) }}" class="flex flex-col gap-2">
                @csrf
                <input type="hidden" name="side" value="buku">
                <input type="text" name="description" placeholder="Deskripsi (mis. Biaya admin bank Agustus)" required
                    class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm outline-none focus:border-orange-400">
                <div class="flex gap-2">
                    <input type="number" step="0.01" name="amount" placeholder="Jumlah (+/-)" required
                        class="flex-1 px-3 py-2 border border-slate-200 rounded-lg text-sm outline-none focus:border-orange-400">
                    <select name="counter_account_id" required class="flex-1 px-3 py-2 border border-slate-200 rounded-lg text-sm outline-none focus:border-orange-400">
                        <option value="">-- Akun Lawan --</option>
                        @foreach($counterAccounts as $acc)
                            <option value="{{ $acc->id }}">{{ $acc->code }} — {{ $acc->name }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="px-3 py-2 rounded-lg text-xs font-semibold bg-slate-100 text-slate-700 border-0 cursor-pointer hover:bg-slate-200 self-start">+ Tambah Item Buku</button>
            </form>
        </div>
        @endif
    </div>

    {{-- Sisi Bank --}}
    <div class="bg-white rounded-xl shadow-sm overflow-hidden self-start">
        <div class="px-5 py-3 bg-slate-50 border-b border-slate-100">
            <span class="text-xs font-bold text-slate-500 uppercase tracking-wide">Saldo Menurut Bank (Rekening Koran)</span>
        </div>
        <div class="divide-y divide-slate-50 text-sm">
            <div class="flex items-center justify-between px-5 py-2.5">
                <span class="text-slate-600">Saldo Akhir Rekening Koran</span>
                <span class="font-mono text-slate-800">Rp {{ number_format($bankReconciliation->statement_balance, 0, ',', '.') }}</span>
            </div>
            @forelse($bankItems as $item)
            <div class="flex items-center justify-between gap-2 px-5 py-2.5">
                <div class="min-w-0 text-slate-700 truncate">{{ $item->description }}</div>
                <div class="flex items-center gap-2 shrink-0">
                    <span class="font-mono {{ $item->amount >= 0 ? 'text-green-700' : 'text-red-700' }}">{{ $item->amount >= 0 ? '' : '-' }}Rp {{ number_format(abs($item->amount), 0, ',', '.') }}</span>
                    @if($bankReconciliation->isDraft())
                    <form method="POST" action="{{ route('bank-reconciliations.items.destroy', $item) }}" onsubmit="return confirm('Hapus item ini?');">
                        @csrf @method('DELETE')
                        <button type="submit" class="text-red-400 hover:text-red-600 border-0 bg-transparent cursor-pointer text-xs">✕</button>
                    </form>
                    @endif
                </div>
            </div>
            @empty
            <div class="px-5 py-3 text-[12px] text-slate-400 italic">Belum ada item penyesuai.</div>
            @endforelse
            <div class="flex items-center justify-between px-5 py-3 bg-slate-50/70">
                <span class="font-bold text-slate-700">Saldo Bank Disesuaikan</span>
                <span class="font-mono font-bold text-slate-900">Rp {{ number_format($adjustedBank, 0, ',', '.') }}</span>
            </div>
        </div>

        @if($bankReconciliation->isDraft())
        <div class="p-4 border-t border-slate-100">
            <form method="POST" action="{{ route('bank-reconciliations.items.store', $bankReconciliation) }}" class="flex flex-col gap-2">
                @csrf
                <input type="hidden" name="side" value="bank">
                <input type="text" name="description" placeholder="Deskripsi (mis. Setoran dalam perjalanan)" required
                    class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm outline-none focus:border-orange-400">
                <input type="number" step="0.01" name="amount" placeholder="Jumlah (+/-)" required
                    class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm outline-none focus:border-orange-400">
                <button type="submit" class="px-3 py-2 rounded-lg text-xs font-semibold bg-slate-100 text-slate-700 border-0 cursor-pointer hover:bg-slate-200 self-start">+ Tambah Item Bank</button>
            </form>
        </div>
        @endif
    </div>
</div>

@if($bankReconciliation->isDraft())
<div class="bg-white rounded-xl shadow-sm p-4 flex flex-wrap gap-3 items-center justify-between">
    <div class="text-xs text-slate-400">
        Item sisi "Buku" akan dijurnal (menyesuaikan saldo aplikasi). Item sisi "Bank" hanya beda waktu — tidak perlu jurnal, akan otomatis hilang bulan depan saat transaksinya settle.
    </div>
    <div class="flex gap-2 shrink-0">
        @if($unpostedBukuCount > 0)
        <form method="POST" action="{{ route('bank-reconciliations.post-adjustments', $bankReconciliation) }}" onsubmit="return confirm('Posting jurnal penyesuaian untuk {{ $unpostedBukuCount }} item buku yang belum dijurnal?');">
            @csrf
            <button type="submit" class="px-4 py-2 rounded-xl text-sm font-semibold bg-blue-600 text-white border-0 cursor-pointer hover:bg-blue-700 transition-colors">
                Posting Jurnal Penyesuaian ({{ $unpostedBukuCount }})
            </button>
        </form>
        @endif
        <form method="POST" action="{{ route('bank-reconciliations.complete', $bankReconciliation) }}">
            @csrf
            <button type="submit" {{ $isMatched ? '' : 'disabled' }}
                class="px-4 py-2 rounded-xl text-sm font-semibold border-0 transition-colors {{ $isMatched ? 'bg-gradient-to-br from-orange-400 to-orange-500 text-white cursor-pointer hover:-translate-y-px' : 'bg-slate-100 text-slate-400 cursor-not-allowed' }}">
                Selesaikan Rekonsiliasi
            </button>
        </form>
        <form method="POST" action="{{ route('bank-reconciliations.destroy', $bankReconciliation) }}" onsubmit="return confirm('Hapus rekonsiliasi ini?');">
            @csrf @method('DELETE')
            <button type="submit" class="px-4 py-2 rounded-xl text-sm font-medium border border-red-200 text-red-600 bg-white cursor-pointer hover:bg-red-50 transition-colors">
                Hapus
            </button>
        </form>
    </div>
</div>
@else
<div class="bg-white rounded-xl shadow-sm p-4 text-sm text-slate-500">
    Diselesaikan oleh <strong>{{ $bankReconciliation->completer?->name }}</strong> pada {{ $bankReconciliation->completed_at?->translatedFormat('d M Y H:i') }}.
</div>
@endif

</x-layouts.app>
