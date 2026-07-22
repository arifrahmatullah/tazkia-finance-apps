<x-layouts.app title="Saldo Awal">

{{-- Header --}}
<div class="flex items-start justify-between gap-4 mb-5 flex-wrap">
    <div>
        <h2 class="text-lg font-bold text-slate-900 m-0 mb-1">Saldo Awal</h2>
        <p class="text-xs text-slate-400 m-0">
            Input saldo awal per akun per 1 Januari — total debit dan kredit harus balance
        </p>
    </div>
</div>

@if(session('success'))
<div class="flex items-center gap-2.5 px-4 py-3 bg-green-50 border border-green-200 rounded-xl mb-4 text-sm text-green-700">
    <svg width="16" height="16" fill="#16a34a" viewBox="0 0 20 20" class="shrink-0"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
    {{ session('success') }}
</div>
@endif

@if($errors->any())
<div class="flex items-start gap-2.5 px-4 py-3 bg-red-50 border border-red-200 rounded-xl mb-4 text-sm text-red-700">
    <svg width="16" height="16" fill="#dc2626" viewBox="0 0 20 20" class="shrink-0 mt-0.5"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
    <div>
        @foreach($errors->all() as $error)
            <div>{{ $error }}</div>
        @endforeach
    </div>
</div>
@endif

{{-- Filter Bar --}}
<form method="GET" action="{{ route('beginning-balances.index') }}" class="bg-white rounded-xl shadow-sm p-4 mb-4 flex flex-wrap gap-3 items-end">
    @if($organizations->count() > 1)
    <div class="min-w-[200px]">
        <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block mb-1.5">Organisasi</label>
        <select name="organization_id" onchange="this.form.submit()"
            class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm text-slate-700 bg-white outline-none focus:border-blue-400 transition-colors">
            @foreach($organizations as $org)
                <option value="{{ $org->id }}" {{ $orgId === $org->id ? 'selected' : '' }}>{{ $org->name }}</option>
            @endforeach
        </select>
    </div>
    @else
    <input type="hidden" name="organization_id" value="{{ $orgId }}">
    @endif

    <div class="min-w-[130px]">
        <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block mb-1.5">Tahun</label>
        <select name="year" onchange="this.form.submit()"
            class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm text-slate-700 bg-white outline-none focus:border-blue-400 transition-colors">
            @foreach($yearOptions as $y)
                <option value="{{ $y }}" {{ $year === $y ? 'selected' : '' }}>{{ $y }}</option>
            @endforeach
        </select>
    </div>

    <div class="text-xs text-slate-400 pb-2.5">
        @if($entry)
            Sudah tersimpan sebagai jurnal
            <a href="{{ route('journal-entries.show', $entry->id) }}" class="font-mono text-blue-600 no-underline hover:underline">{{ $entry->reference }}</a>
            (per {{ $entry->entry_date->translatedFormat('d M Y') }}) — ubah angka di bawah lalu simpan ulang.
        @else
            Belum ada saldo awal untuk tahun {{ $year }}.
        @endif
    </div>
</form>

@if($accounts->isEmpty())
<div class="bg-white rounded-xl shadow-sm py-16 px-5 text-center">
    <div class="text-sm font-semibold text-slate-600 mb-1">Belum ada akun</div>
    <div class="text-xs text-slate-400">Tambahkan akun pada menu <strong>Chart of Accounts</strong> terlebih dahulu.</div>
</div>
@else

<form method="POST" action="{{ route('beginning-balances.save') }}" id="bb-form">
    @csrf
    <input type="hidden" name="organization_id" value="{{ $orgId }}">
    <input type="hidden" name="year" value="{{ $year }}">

    <div class="bg-white rounded-xl shadow-sm overflow-hidden mb-4">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-[11px] text-slate-400 uppercase tracking-wider bg-slate-50/70 border-b border-slate-100">
                        <th class="py-2.5 px-4 font-semibold whitespace-nowrap">Kode</th>
                        <th class="py-2.5 px-3 font-semibold min-w-[220px]">Nama Akun</th>
                        <th class="py-2.5 px-3 font-semibold whitespace-nowrap">Saldo Normal</th>
                        <th class="py-2.5 px-3 font-semibold text-right min-w-[160px]">Debit</th>
                        <th class="py-2.5 px-4 font-semibold text-right min-w-[160px]">Kredit</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach(\App\Models\Account::TYPES as $type => $info)
                        @php $group = $accounts->where('account_type', $type); @endphp
                        @if($group->isNotEmpty())
                        <tr class="bg-slate-100/70 border-b border-slate-100">
                            <td colspan="5" class="py-2 px-4 text-[11px] font-bold uppercase tracking-widest" style="color: {{ $info['color'] }}">{{ $info['label'] }}</td>
                        </tr>
                        @foreach($group as $account)
                        @php
                            $line = $existing->get($account->id);
                            $oldDebit  = old("balances.{$account->id}.debit",  $line && $line->debit > 0 ? (int) $line->debit : null);
                            $oldCredit = old("balances.{$account->id}.credit", $line && $line->credit > 0 ? (int) $line->credit : null);
                        @endphp
                        <tr class="border-b border-slate-50 last:border-0 hover:bg-slate-50/50 transition-colors">
                            <td class="py-2 px-4 font-mono text-[11px] text-slate-500 whitespace-nowrap">{{ $account->code }}</td>
                            <td class="py-2 px-3 text-slate-700">{{ $account->name }}{{ $account->is_active ? '' : ' (nonaktif)' }}</td>
                            <td class="py-2 px-3 text-[11px] text-slate-400 capitalize">{{ $account->normal_balance }}</td>
                            <td class="py-2 px-3">
                                <input type="text" inputmode="numeric" autocomplete="off"
                                    value="{{ $oldDebit ? number_format($oldDebit, 0, ',', '.') : '' }}"
                                    placeholder="0" data-side="debit"
                                    class="bb-input w-full px-2.5 py-1.5 border border-blue-200 rounded-lg text-sm text-right font-mono text-slate-700 outline-none focus:border-blue-400 transition-colors">
                                <input type="hidden" name="balances[{{ $account->id }}][debit]" value="{{ $oldDebit }}" class="bb-raw">
                            </td>
                            <td class="py-2 px-4">
                                <input type="text" inputmode="numeric" autocomplete="off"
                                    value="{{ $oldCredit ? number_format($oldCredit, 0, ',', '.') : '' }}"
                                    placeholder="0" data-side="credit"
                                    class="bb-input w-full px-2.5 py-1.5 border border-green-200 rounded-lg text-sm text-right font-mono text-slate-700 outline-none focus:border-green-400 transition-colors">
                                <input type="hidden" name="balances[{{ $account->id }}][credit]" value="{{ $oldCredit }}" class="bb-raw">
                            </td>
                        </tr>
                        @endforeach
                        @endif
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="bg-slate-50/70 border-t border-slate-200">
                        <td colspan="3" class="py-3 px-4 text-[11px] font-bold text-slate-500 uppercase tracking-widest">Total</td>
                        <td class="py-3 px-3 text-right font-mono font-bold text-blue-700 whitespace-nowrap" id="total-debit">Rp 0</td>
                        <td class="py-3 px-4 text-right font-mono font-bold text-green-700 whitespace-nowrap" id="total-credit">Rp 0</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    {{-- Bar status balance + tombol simpan --}}
    <div class="bg-white rounded-xl shadow-sm p-4 flex flex-wrap items-center gap-3 sticky bottom-3 border border-slate-100">
        <span id="balance-badge" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-500">
            Belum ada angka
        </span>
        <span class="text-xs text-slate-400" id="balance-hint">Isi saldo tiap akun pada kolom debit atau kredit.</span>
        <button type="submit" id="save-btn" disabled
            class="ml-auto inline-flex items-center gap-1.5 px-5 py-2.5 rounded-lg text-sm font-semibold bg-blue-600 text-white border-0 cursor-pointer hover:bg-blue-700 transition-colors disabled:bg-slate-200 disabled:text-slate-400 disabled:cursor-not-allowed">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
            Simpan Saldo Awal
        </button>
    </div>
</form>

<div class="text-[11px] text-slate-400 mt-3">
    Saldo awal disimpan sebagai jurnal khusus (referensi <span class="font-mono">SA-{{ $year }}-…</span>) bertanggal 1 Januari {{ $year }} berstatus <em>posted</em>,
    sehingga otomatis terhitung di buku besar dan neraca saldo. Mengosongkan semua angka lalu menyimpan akan menghapus saldo awal tahun ini.
</div>

<script>
(function () {
    const inputs = document.querySelectorAll('.bb-input');
    const badge = document.getElementById('balance-badge');
    const hint = document.getElementById('balance-hint');
    const saveBtn = document.getElementById('save-btn');
    const hasExisting = @json((bool) $entry);

    const fmt = n => 'Rp ' + Math.round(n).toLocaleString('id-ID');
    const rawOf = el => el.nextElementSibling; // input hidden .bb-raw tepat setelah .bb-input

    function formatDisplay(el) {
        const digits = el.value.replace(/\D/g, '');
        el.value = digits ? Number(digits).toLocaleString('id-ID') : '';
        rawOf(el).value = digits;
    }

    function recalc() {
        let d = 0, c = 0;
        inputs.forEach(el => {
            const v = parseFloat(rawOf(el).value) || 0;
            if (el.dataset.side === 'debit') d += v; else c += v;
        });

        document.getElementById('total-debit').textContent = fmt(d);
        document.getElementById('total-credit').textContent = fmt(c);

        const diff = Math.abs(d - c);
        if (d === 0 && c === 0) {
            badge.className = 'inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-500';
            badge.textContent = 'Belum ada angka';
            hint.textContent = hasExisting
                ? 'Menyimpan dalam keadaan kosong akan menghapus saldo awal tahun ini.'
                : 'Isi saldo tiap akun pada kolom debit atau kredit.';
            saveBtn.disabled = !hasExisting;
        } else if (diff > 0.01) {
            badge.className = 'inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold bg-red-100 text-red-700';
            badge.textContent = 'Selisih ' + fmt(diff);
            hint.textContent = 'Total debit dan kredit belum sama — periksa kembali angkanya.';
            saveBtn.disabled = true;
        } else {
            badge.className = 'inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold bg-green-100 text-green-700';
            badge.textContent = 'Balance ✓';
            hint.textContent = 'Total debit = total kredit. Siap disimpan.';
            saveBtn.disabled = false;
        }
    }

    inputs.forEach(el => {
        el.addEventListener('input', () => {
            formatDisplay(el);
            // Satu akun hanya boleh satu sisi: mengisi debit mengosongkan kredit, dan sebaliknya
            if ((parseFloat(rawOf(el).value) || 0) > 0) {
                const other = el.closest('tr').querySelector(`.bb-input[data-side="${el.dataset.side === 'debit' ? 'credit' : 'debit'}"]`);
                if (other && rawOf(other).value) { other.value = ''; rawOf(other).value = ''; }
            }
            recalc();
        });
    });

    document.getElementById('bb-form').addEventListener('submit', function (e) {
        const form = this;
        let d = 0, c = 0;
        inputs.forEach(el => {
            const v = parseFloat(rawOf(el).value) || 0;
            if (el.dataset.side === 'debit') d += v; else c += v;
        });

        // Jaga-jaga saja: tombol simpan sudah disabled saat belum balance
        if (Math.abs(d - c) > 0.01) {
            e.preventDefault();
            return;
        }

        if (d === 0 && c === 0 && hasExisting) {
            e.preventDefault();
            if (window.confirmModal) {
                confirmModal(
                    'Hapus Saldo Awal',
                    'Semua angka kosong. Saldo awal tahun <strong>' + {{ (int) $year }} + '</strong> akan <strong>dihapus</strong>. Lanjutkan?',
                    function () { form.submit(); },
                    'Ya, Hapus',
                    'Tindakan ini tidak dapat dibatalkan.'
                );
            } else if (confirm('Semua angka kosong. Saldo awal tahun ini akan DIHAPUS. Lanjutkan?')) {
                form.submit();
            }
        }
    });

    recalc();
})();
</script>

@endif

</x-layouts.app>
