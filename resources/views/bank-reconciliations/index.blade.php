<x-layouts.app title="Rekonsiliasi Bank">

<div class="flex items-center justify-between mb-5 flex-wrap gap-3">
    <div>
        <h2 class="text-lg font-bold text-slate-900 m-0 mb-0.5">Rekonsiliasi Bank</h2>
        <p class="text-xs text-slate-400 m-0">Cocokkan saldo kas/bank menurut aplikasi dengan rekening koran bank per periode</p>
    </div>
</div>

@if(session('success'))
<div class="flex items-center gap-2.5 px-4 py-3 bg-green-50 border border-green-200 rounded-xl mb-4 text-sm text-green-700">
    <svg width="16" height="16" fill="#16a34a" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
    {{ session('success') }}
</div>
@endif
@error('delete')
<div class="flex items-center gap-2.5 px-4 py-3 bg-red-50 border border-red-200 rounded-xl mb-4 text-sm text-red-700">{{ $message }}</div>
@enderror
@error('period')
<div class="flex items-center gap-2.5 px-4 py-3 bg-red-50 border border-red-200 rounded-xl mb-4 text-sm text-red-700">{{ $message }}</div>
@enderror

{{-- Filter organisasi --}}
<form method="GET" action="{{ route('bank-reconciliations.index') }}" class="flex gap-2.5 flex-wrap items-center mb-4">
    @if($organizations->count() > 1)
    <select name="organization_id" class="px-3 py-2 border border-slate-200 rounded-xl text-sm text-slate-800 bg-white outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100 transition-colors min-w-[180px]" onchange="this.form.submit()">
        @foreach($organizations as $org)
            <option value="{{ $org->id }}" {{ $orgId === $org->id ? 'selected' : '' }}>{{ $org->name }}</option>
        @endforeach
    </select>
    @else
    <input type="hidden" name="organization_id" value="{{ $orgId }}">
    @endif
</form>

@if(!$orgId)
<div class="bg-white rounded-xl shadow-sm py-16 px-5 text-center">
    <div class="text-sm font-semibold text-slate-600 mb-1">Belum ada organisasi</div>
    <div class="text-xs text-slate-400">Tidak ada organisasi yang bisa diakses.</div>
</div>
@else

{{-- Buat rekonsiliasi baru --}}
<div class="bg-white rounded-xl shadow-sm p-4 mb-4">
    <div class="text-xs font-bold text-slate-500 uppercase tracking-wide mb-3">Buat Rekonsiliasi Baru</div>
    @if($kasAccounts->isEmpty())
    <div class="text-sm text-slate-400 italic">Belum ada akun kas/bank (kode 1.1.01.xx) pada organisasi ini. Tambahkan dulu di menu Bagan Akun.</div>
    @else
    <form method="POST" action="{{ route('bank-reconciliations.store') }}" class="flex flex-wrap gap-3 items-end">
        @csrf
        <input type="hidden" name="organization_id" value="{{ $orgId }}">
        <div class="min-w-[220px]">
            <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block mb-1.5">Akun Kas/Bank</label>
            <select name="account_id" required class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm text-slate-700 bg-white outline-none focus:border-blue-400 transition-colors">
                <option value="">-- Pilih Akun --</option>
                @foreach($kasAccounts as $acc)
                    <option value="{{ $acc->id }}">{{ $acc->code }} — {{ $acc->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="min-w-[140px]">
            <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block mb-1.5">Periode</label>
            <input type="month" name="period" required class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm text-slate-700 bg-white outline-none focus:border-blue-400 transition-colors">
            <div class="text-[10px] text-slate-400 mt-1">Bulan yang mau dicocokkan</div>
        </div>
        <div class="min-w-[180px]">
            <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block mb-1.5">Saldo Akhir Rekening Koran (Rp)</label>
            <input type="text" id="statementBalanceDisplay" inputmode="numeric" placeholder="0" required
                oninput="formatRupiah(this, 'statementBalanceHidden')"
                class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm text-slate-700 bg-white outline-none focus:border-blue-400 transition-colors">
            <input type="hidden" name="statement_balance" id="statementBalanceHidden">
        </div>
        <button type="submit" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-sm font-semibold bg-orange-500 text-white border-0 cursor-pointer hover:bg-orange-600 transition-colors">
            Buat Rekonsiliasi
        </button>
    </form>
    <script>
    function formatRupiah(input, hiddenId) {
        const raw = input.value.replace(/\D/g, '');
        document.getElementById(hiddenId).value = raw;
        input.value = raw ? parseInt(raw).toLocaleString('id-ID') : '';
    }
    </script>
    @endif
</div>

{{-- Daftar rekonsiliasi --}}
<div class="bg-white rounded-xl shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-[11px] text-slate-400 uppercase tracking-wider bg-slate-50/70 border-b border-slate-100">
                    <th class="py-2.5 px-4 font-semibold whitespace-nowrap">Periode</th>
                    <th class="py-2.5 px-3 font-semibold min-w-[180px]">Akun</th>
                    <th class="py-2.5 px-3 font-semibold text-right whitespace-nowrap">Saldo Rekening Koran</th>
                    <th class="py-2.5 px-3 font-semibold whitespace-nowrap">Item Penyesuai</th>
                    <th class="py-2.5 px-3 font-semibold whitespace-nowrap">Status</th>
                    <th class="py-2.5 px-4 font-semibold whitespace-nowrap">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($reconciliations as $r)
                <tr class="border-b border-slate-50 last:border-0 hover:bg-slate-50/50 transition-colors">
                    <td class="py-2.5 px-4 whitespace-nowrap text-slate-700">{{ $r->period->translatedFormat('F Y') }}</td>
                    <td class="py-2.5 px-3 text-slate-700">{{ $r->account->code }} — {{ $r->account->name }}</td>
                    <td class="py-2.5 px-3 text-right font-mono text-slate-800 whitespace-nowrap">Rp {{ number_format($r->statement_balance, 0, ',', '.') }}</td>
                    <td class="py-2.5 px-3 text-slate-600 whitespace-nowrap">{{ $r->items->count() }} item</td>
                    <td class="py-2.5 px-3 whitespace-nowrap">
                        @if($r->status === 'selesai')
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-green-100 text-green-700">Selesai</span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-amber-100 text-amber-700">Draft</span>
                        @endif
                    </td>
                    <td class="py-2.5 px-4 whitespace-nowrap">
                        <a href="{{ route('bank-reconciliations.show', $r) }}" class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-xs font-medium bg-blue-50 text-blue-600 hover:bg-blue-100 transition-colors no-underline">Buka</a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="py-8 px-4 text-center text-[12px] text-slate-400 italic">Belum ada rekonsiliasi. Buat yang pertama di atas.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endif

</x-layouts.app>
