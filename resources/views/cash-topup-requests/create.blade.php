<x-layouts.app title="Ajukan Saldo ke Yayasan">

@if($errors->any())
<div class="px-4 py-3 bg-red-50 border border-red-200 rounded-xl mb-4 text-sm text-red-700">
    @foreach($errors->all() as $error)
        <div>{{ $error }}</div>
    @endforeach
</div>
@endif

<div class="flex items-start justify-between gap-4 mb-5 flex-wrap">
    <div>
        <h2 class="text-lg font-bold text-slate-900 m-0 mb-1">Ajukan Saldo ke Yayasan</h2>
        <p class="text-xs text-slate-400 m-0">Minta tambahan saldo rekening ke organisasi induk saat rekening pencairan tidak cukup.</p>
    </div>
    <a href="{{ route('cash-topup-requests.index') }}"
        class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-sm font-medium bg-slate-100 text-slate-600 no-underline hover:bg-slate-200 transition-colors">
        &larr; Kembali
    </a>
</div>

<form method="POST" action="{{ route('cash-topup-requests.store') }}" class="flex flex-col gap-4">
    @csrf

    <div class="bg-white rounded-xl shadow-sm p-5">
        @if($organizations->count() > 1)
        <div class="mb-4">
            <label class="text-xs font-semibold text-slate-600 block mb-1.5">Organisasi Pengaju <span class="text-red-500">*</span></label>
            <select name="organization_id" id="organization-select" required
                class="w-full px-3 py-2.5 border border-slate-200 rounded-xl text-sm text-slate-800 bg-white outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100 transition-colors">
                @foreach($organizations as $org)
                <option value="{{ $org->id }}" {{ $organizationId == $org->id ? 'selected' : '' }}>{{ $org->name }}</option>
                @endforeach
            </select>
            <p class="text-[11px] text-slate-400 mt-1">Ganti organisasi akan memuat ulang daftar akun & pengajuan dana.</p>
        </div>
        @else
        <input type="hidden" name="organization_id" value="{{ $organizationId }}">
        <div class="mb-4 text-xs text-slate-500">
            Organisasi pengaju: <span class="font-semibold text-slate-800">{{ $organizations->first()->name }}</span>
        </div>
        @endif

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
            <div>
                <label class="text-xs font-semibold text-slate-600 block mb-1.5">Rekening Tujuan (yang mau ditambah saldonya) <span class="text-red-500">*</span></label>
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
                <label class="text-xs font-semibold text-slate-600 block mb-1.5">Akun Lawan (sisi organisasi Anda)</label>
                @if($autoContraAccount)
                <div class="flex items-center gap-2 px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-700">
                    <svg width="14" height="14" class="text-slate-400 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M20 6L9 17l-5-5"/></svg>
                    <span class="font-mono font-semibold text-slate-500">{{ $autoContraAccount->code }}</span>
                    <span>{{ $autoContraAccount->name }}</span>
                </div>
                <input type="hidden" name="source_credit_account_id" value="{{ $autoContraAccount->id }}">
                <p class="text-[11px] text-slate-400 mt-1">Terisi otomatis dari akun Hutang Antar Entitas yang sudah ada di Bagan Akun -- tidak perlu diubah.</p>
                @else
                <select name="source_credit_account_id" required
                    class="w-full px-3 py-2.5 border border-slate-200 rounded-xl text-sm text-slate-800 bg-white outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100 transition-colors">
                    <option value="">— Pilih Akun —</option>
                    @foreach($ledgerAccounts as $acc)
                    <option value="{{ $acc->id }}">{{ $acc->code }} — {{ $acc->name }}</option>
                    @endforeach
                </select>
                <p class="text-[11px] text-slate-400 mt-1">Sistem tidak menemukan akun Hutang Antar Entitas yang cocok otomatis -- pilih akun kewajiban yang sesuai, atau hubungi Akunting kalau ragu.</p>
                @endif
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
            <div>
                <label class="text-xs font-semibold text-slate-600 block mb-1.5">Jumlah Saldo Diajukan <span class="text-red-500">*</span></label>
                <div class="relative">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm">Rp</span>
                    <input type="number" name="amount" id="amount-input" min="1" step="1" required
                        class="w-full pl-9 pr-3 py-2.5 border border-slate-200 rounded-xl text-sm text-slate-800 outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100 transition-colors font-mono"
                        placeholder="0" value="{{ old('amount') }}">
                </div>
            </div>
        </div>

        <div>
            <label class="text-xs font-semibold text-slate-600 block mb-1.5">Catatan <span class="text-slate-400 font-normal">(opsional)</span></label>
            <textarea name="notes" rows="2"
                class="w-full px-3 py-2.5 border border-slate-200 rounded-xl text-sm outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100 transition-colors resize-none"
                placeholder="Alasan/keterangan tambahan...">{{ old('notes') }}</textarea>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm p-5">
        <div class="text-sm font-bold text-slate-900 mb-1">Pengajuan Dana Terkait <span class="text-slate-400 font-normal text-xs">(opsional, sebagai konteks buat Yayasan)</span></div>
        <p class="text-xs text-slate-400 mb-3">Centang pengajuan dana yang sudah disetujui tapi belum cair karena saldo kurang. Ini hanya konteks — begitu saldo cair, bisa dipakai untuk pengajuan lain juga.</p>

        @if($candidateFundRequests->isEmpty())
        <div class="text-xs text-slate-400 italic">Tidak ada pengajuan dana berstatus disetujui & belum cair pada organisasi ini.</div>
        @else
        <div class="flex flex-col gap-2 max-h-72 overflow-y-auto" id="fund-request-checklist">
            @foreach($candidateFundRequests as $fr)
            <label class="flex items-start gap-3 px-3 py-2.5 border border-slate-100 rounded-xl hover:bg-slate-50 cursor-pointer">
                <input type="checkbox" name="fund_request_ids[]" value="{{ $fr->id }}" data-amount="{{ $fr->amount }}" class="mt-0.5 fund-request-checkbox">
                <div class="flex-1 min-w-0">
                    <div class="flex items-center justify-between gap-2 flex-wrap">
                        <span class="font-mono text-xs font-bold text-orange-500">{{ $fr->reference }}</span>
                        <span class="text-sm font-bold text-slate-900 font-mono">Rp {{ number_format($fr->amount, 0, ',', '.') }}</span>
                    </div>
                    <div class="text-xs text-slate-700 font-medium">{{ $fr->title }}</div>
                    <div class="text-[11px] text-slate-400">{{ $fr->department->name ?? '-' }} · {{ $fr->budgetProgram->name ?? '-' }}</div>
                </div>
            </label>
            @endforeach
        </div>

        <div id="fund-request-total-bar" class="hidden mt-3 flex items-center justify-between gap-3 px-3 py-2.5 bg-blue-50 border border-blue-100 rounded-xl flex-wrap">
            <div class="text-xs text-blue-700">
                <span id="fund-request-total-count" class="font-bold">0</span> pengajuan dipilih ·
                Total <span id="fund-request-total-amount" class="font-mono font-bold">Rp 0</span>
            </div>
            <button type="button" id="fund-request-fill-amount"
                class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold bg-blue-600 text-white border-0 cursor-pointer hover:bg-blue-700 transition-colors">
                Isi ke Jumlah Saldo Diajukan
            </button>
        </div>
        @endif
    </div>

    <div class="flex justify-end gap-2">
        <a href="{{ route('cash-topup-requests.index') }}"
            class="inline-flex items-center px-5 py-2.5 rounded-xl text-sm font-semibold bg-slate-100 text-slate-600 no-underline hover:bg-slate-200 transition-colors">
            Batal
        </a>
        <button type="submit"
            class="inline-flex items-center gap-1.5 px-5 py-2.5 rounded-xl text-sm font-semibold bg-gradient-to-br from-blue-500 to-blue-600 text-white border-0 cursor-pointer hover:opacity-90 transition-opacity shadow-sm">
            Kirim Pengajuan ke Yayasan
        </button>
    </div>
</form>

@if($organizations->count() > 1)
<script>
document.getElementById('organization-select').addEventListener('change', function () {
    window.location.href = '{{ route('cash-topup-requests.create') }}?organization_id=' + this.value;
});
</script>
@endif

@if($candidateFundRequests->isNotEmpty())
<script>
(function () {
    var checkboxes = document.querySelectorAll('.fund-request-checkbox');
    var totalBar    = document.getElementById('fund-request-total-bar');
    var totalCount  = document.getElementById('fund-request-total-count');
    var totalAmount = document.getElementById('fund-request-total-amount');
    var fillBtn     = document.getElementById('fund-request-fill-amount');
    var amountInput = document.getElementById('amount-input');

    function updateTotal() {
        var checked = Array.prototype.filter.call(checkboxes, function (cb) { return cb.checked; });
        var total = checked.reduce(function (sum, cb) { return sum + parseFloat(cb.dataset.amount || '0'); }, 0);

        if (checked.length === 0) {
            totalBar.classList.add('hidden');
            return;
        }
        totalBar.classList.remove('hidden');
        totalCount.textContent = checked.length;
        totalAmount.textContent = 'Rp ' + total.toLocaleString('id-ID');
        fillBtn.dataset.total = total;
    }

    checkboxes.forEach(function (cb) { cb.addEventListener('change', updateTotal); });

    fillBtn.addEventListener('click', function () {
        amountInput.value = this.dataset.total || 0;
    });

    updateTotal();
})();
</script>
@endif

</x-layouts.app>
