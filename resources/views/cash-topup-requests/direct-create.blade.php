<x-layouts.app title="Isi Saldo Langsung">

@if($errors->any())
<div class="px-4 py-3 bg-red-50 border border-red-200 rounded-xl mb-4 text-sm text-red-700">
    @foreach($errors->all() as $error)
        <div>{{ $error }}</div>
    @endforeach
    @if(session('insufficientBalanceOrgId'))
    <a href="{{ route('beginning-balances.index', ['organization_id' => session('insufficientBalanceOrgId')]) }}"
        class="inline-block mt-1 font-semibold underline">Isi Saldo Awal rekening ini &rarr;</a>
    @endif
</div>
@endif

<div class="flex items-start justify-between gap-4 mb-5 flex-wrap">
    <div>
        <h2 class="text-lg font-bold text-slate-900 m-0 mb-1">Isi Saldo Langsung</h2>
        <p class="text-xs text-slate-400 m-0">Yayasan menambah saldo rekening Kampus/STMIK tanpa menunggu pengajuan -- langsung efektif begitu dikirim.</p>
    </div>
    <a href="{{ route('cash-topup-requests.index') }}"
        class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-sm font-medium bg-slate-100 text-slate-600 no-underline hover:bg-slate-200 transition-colors">
        &larr; Kembali
    </a>
</div>

<form method="POST" action="{{ route('cash-topup-requests.direct-store') }}" enctype="multipart/form-data" class="flex flex-col gap-4">
    @csrf

    <div class="bg-white rounded-xl shadow-sm p-5">
        <div class="text-sm font-bold text-slate-900 mb-3">Tujuan</div>

        @if($childOrgs->count() > 1)
        <div class="mb-4">
            <label class="text-xs font-semibold text-slate-600 block mb-1.5">Organisasi Tujuan <span class="text-red-500">*</span></label>
            <select name="organization_id" id="organization-select" required
                class="w-full px-3 py-2.5 border border-slate-200 rounded-xl text-sm text-slate-800 bg-white outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100 transition-colors">
                @foreach($childOrgs as $org)
                <option value="{{ $org->id }}" {{ $organizationId == $org->id ? 'selected' : '' }}>{{ $org->name }}</option>
                @endforeach
            </select>
            <p class="text-[11px] text-slate-400 mt-1">Ganti organisasi akan memuat ulang daftar akun.</p>
        </div>
        @else
        <input type="hidden" name="organization_id" value="{{ $organizationId }}">
        <div class="mb-4 text-xs text-slate-500">
            Organisasi tujuan: <span class="font-semibold text-slate-800">{{ $childOrgs->first()->name }}</span>
        </div>
        @endif

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="text-xs font-semibold text-slate-600 block mb-1.5">Rekening Tujuan <span class="text-red-500">*</span></label>
                @if($targetAccounts->isEmpty())
                <div class="px-3 py-2.5 bg-amber-50 border border-amber-200 rounded-xl text-xs text-amber-700">
                    Tidak ada akun REKENING BANK di Bagan Akun organisasi ini.
                </div>
                @else
                <select name="target_account_id" required
                    class="w-full px-3 py-2.5 border border-slate-200 rounded-xl text-sm text-slate-800 bg-white outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100 transition-colors">
                    <option value="">— Pilih Rekening —</option>
                    @foreach($targetAccounts as $acc)
                    <option value="{{ $acc->id }}">{{ $acc->name }} (saldo saat ini: Rp {{ number_format($acc->balance, 0, ',', '.') }})</option>
                    @endforeach
                </select>
                @endif
            </div>
            <div>
                <label class="text-xs font-semibold text-slate-600 block mb-1.5">Akun Lawan (sisi Kampus/STMIK)</label>
                @if($autoContraAccount)
                <div class="flex items-center gap-2 px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-700">
                    <svg width="14" height="14" class="text-slate-400 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M20 6L9 17l-5-5"/></svg>
                    <span class="font-mono font-semibold text-slate-500">{{ $autoContraAccount->code }}</span>
                    <span>{{ $autoContraAccount->name }}</span>
                </div>
                <input type="hidden" name="source_credit_account_id" value="{{ $autoContraAccount->id }}">
                <p class="text-[11px] text-slate-400 mt-1">Terisi otomatis dari akun Hutang Antar Entitas yang sudah ada di Bagan Akun.</p>
                @else
                <select name="source_credit_account_id" required
                    class="w-full px-3 py-2.5 border border-slate-200 rounded-xl text-sm text-slate-800 bg-white outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100 transition-colors">
                    <option value="">— Pilih Akun —</option>
                    @foreach($ledgerAccounts as $acc)
                    <option value="{{ $acc->id }}">{{ $acc->code }} — {{ $acc->name }}</option>
                    @endforeach
                </select>
                <p class="text-[11px] text-slate-400 mt-1">Sistem tidak menemukan akun Hutang Antar Entitas yang cocok otomatis -- pilih akun kewajiban yang sesuai.</p>
                @endif
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-4">
            <div>
                <label class="text-xs font-semibold text-slate-600 block mb-1.5">Jumlah Saldo <span class="text-red-500">*</span></label>
                <div class="relative">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm">Rp</span>
                    <input type="text" id="amount-display" inputmode="numeric" required
                        class="w-full pl-9 pr-3 py-2.5 border border-slate-200 rounded-xl text-sm text-slate-800 outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100 transition-colors font-mono"
                        placeholder="0" value="{{ old('amount') ? number_format(old('amount'), 0, ',', '.') : '' }}">
                    <input type="hidden" name="amount" id="amount-input" value="{{ old('amount') }}">
                </div>
            </div>
        </div>

        <div class="mt-4">
            <label class="text-xs font-semibold text-slate-600 block mb-1.5">Catatan <span class="text-slate-400 font-normal">(opsional)</span></label>
            <textarea name="notes" rows="2"
                class="w-full px-3 py-2.5 border border-slate-200 rounded-xl text-sm outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100 transition-colors resize-none"
                placeholder="Alasan/keterangan tambahan...">{{ old('notes') }}</textarea>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm p-5">
        <div class="text-sm font-bold text-slate-900 mb-3">Sisi Yayasan</div>

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
                <p class="text-[11px] text-slate-400 mt-1">Contoh: akun "Piutang ke [Organisasi Tujuan]".</p>
            </div>
        </div>

        <div class="mt-4">
            <label class="text-xs font-semibold text-slate-600 block mb-1.5">Bukti Transfer <span class="text-red-500">*</span></label>
            <input type="file" name="proof" required accept=".pdf,.jpg,.jpeg,.png"
                class="w-full px-3 py-2.5 border border-slate-200 rounded-xl text-sm text-slate-800 bg-white outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100 transition-colors">
        </div>
    </div>

    <div class="flex justify-end gap-2">
        <a href="{{ route('cash-topup-requests.index') }}"
            class="inline-flex items-center px-5 py-2.5 rounded-xl text-sm font-semibold bg-slate-100 text-slate-600 no-underline hover:bg-slate-200 transition-colors">
            Batal
        </a>
        <button type="submit"
            class="inline-flex items-center gap-1.5 px-5 py-2.5 rounded-xl text-sm font-semibold bg-gradient-to-br from-green-500 to-green-600 text-white border-0 cursor-pointer hover:opacity-90 transition-opacity shadow-sm">
            Isi Saldo Sekarang
        </button>
    </div>
</form>

@if($childOrgs->count() > 1)
<script>
document.getElementById('organization-select').addEventListener('change', function () {
    window.location.href = '{{ route('cash-topup-requests.direct-create') }}?organization_id=' + this.value;
});
</script>
@endif

<script>
(function () {
    var amountInput   = document.getElementById('amount-input');
    var amountDisplay = document.getElementById('amount-display');

    function setAmount(raw) {
        amountInput.value = raw || '';
        amountDisplay.value = raw ? parseInt(raw, 10).toLocaleString('id-ID') : '';
    }

    amountDisplay.addEventListener('input', function () {
        setAmount(this.value.replace(/[^\d]/g, ''));
    });
})();
</script>

</x-layouts.app>
