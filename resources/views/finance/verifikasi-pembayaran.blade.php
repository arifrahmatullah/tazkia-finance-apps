<x-layouts.app title="Verifikasi Pembayaran" breadcrumb="Cek program berjenis pembayaran yang dananya sudah cair">

@if(session('success'))
<div class="flex items-center gap-2.5 px-4 py-3 bg-green-50 border border-green-200 rounded-xl mb-4 text-sm text-green-700">
    <svg width="16" height="16" fill="#16a34a" viewBox="0 0 20 20" class="shrink-0"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
    {{ session('success') }}
</div>
@endif
@if(session('warning'))
<div class="flex items-center gap-2.5 px-4 py-3 bg-amber-50 border border-amber-200 rounded-xl mb-4 text-sm text-amber-700">{{ session('warning') }}</div>
@endif
@if($errors->any())
<div class="flex items-center gap-2.5 px-4 py-3 bg-red-50 border border-red-200 rounded-xl mb-4 text-sm text-red-700">{{ $errors->first() }}</div>
@endif

<div class="mb-5">
    <h2 class="text-lg font-bold text-slate-900 m-0 mb-1">Verifikasi Pembayaran</h2>
    <p class="text-xs text-slate-400 m-0">Program berjenis <strong>Pembayaran</strong> tidak perlu laporan penggunaan dana. Cek apakah memang pembayaran &mdash; kalau seharusnya Kegiatan atau Pengadaan, ubah jenisnya di sini dan pengaju wajib melapor.</p>
</div>

{{-- Ringkasan / tab --}}
<div class="grid grid-cols-2 sm:grid-cols-3 gap-3 mb-4">
    <a href="{{ route('finance.verifikasi-pembayaran', ['tab' => 'belum']) }}"
       class="rounded-xl shadow-sm px-4 py-3.5 no-underline block transition-colors {{ $belumCount > 0 ? 'bg-amber-50 border border-amber-200 hover:bg-amber-100' : 'bg-white hover:bg-slate-50' }} {{ $tab === 'belum' ? 'ring-2 ring-amber-400' : '' }}">
        <div class="text-[10px] font-bold {{ $belumCount > 0 ? 'text-amber-500' : 'text-slate-400' }} uppercase tracking-widest mb-0.5">Belum Dicek</div>
        <div class="text-2xl font-extrabold {{ $belumCount > 0 ? 'text-amber-600' : 'text-slate-300' }}">{{ $belumCount }}</div>
        <div class="text-xs text-slate-400 mt-0.5">program</div>
    </a>
    <a href="{{ route('finance.verifikasi-pembayaran', ['tab' => 'sudah']) }}"
       class="bg-white rounded-xl shadow-sm px-4 py-3.5 no-underline block transition-colors hover:bg-slate-50 {{ $tab === 'sudah' ? 'ring-2 ring-green-400' : '' }}">
        <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-0.5">Sudah Dicek</div>
        <div class="text-2xl font-extrabold text-green-500">{{ $sudahCount }}</div>
        <div class="text-xs text-slate-400 mt-0.5">memang pembayaran</div>
    </a>
    <div class="bg-white rounded-xl shadow-sm px-4 py-3.5 col-span-2 sm:col-span-1">
        <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-0.5">Dana Cair Belum Dicek</div>
        <div class="text-lg font-extrabold text-slate-800 font-mono leading-tight">Rp {{ number_format($belumReqTotal, 0, ',', '.') }}</div>
        <div class="text-xs text-slate-400 mt-0.5">{{ $belumReqCount }} pengajuan</div>
    </div>
</div>

@forelse($programs as $program)
@php
    $frs = $program->fundRequests;
    $totalCair = (float) $frs->sum('amount');
    $org = $program->budgetAllocation?->department?->organization;
@endphp
<div class="bg-white rounded-xl border border-slate-100 shadow-sm overflow-hidden mb-4">
    <div class="px-5 py-4 flex items-start justify-between gap-4 flex-wrap border-b border-slate-50">
        <div class="min-w-0">
            <div class="flex items-center gap-2 flex-wrap mb-1">
                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-sky-100 text-sky-700 uppercase">Pembayaran</span>
                @if($program->payment_verified_at)
                <span class="text-[11px] text-green-600 font-semibold">Dicek {{ $program->payment_verified_by }} &middot; {{ $program->payment_verified_at->format('d/m/Y') }}</span>
                @endif
            </div>
            <div class="text-[0.95rem] font-bold text-slate-900">{{ $program->name }}</div>
            <div class="text-xs text-slate-500 mt-0.5">
                {{ $org?->name ?? '-' }} &middot; {{ $program->budgetAllocation?->department?->name ?? '-' }}
                @if($program->account) &middot; Akun: {{ $program->account->code }} {{ $program->account->name }} @endif
            </div>
        </div>
        <div class="text-right shrink-0">
            <div class="text-[10px] text-slate-400 uppercase tracking-wide">Dana Cair</div>
            <div class="text-base font-extrabold text-slate-900 font-mono">Rp {{ number_format($totalCair, 0, ',', '.') }}</div>
            <div class="text-[11px] text-slate-400">{{ $frs->count() }} pengajuan</div>
        </div>
    </div>

    <div class="px-5 py-3 flex flex-col gap-2">
        @foreach($frs as $fr)
        <div class="flex items-center gap-3 px-3 py-2.5 bg-slate-50 rounded-lg border border-slate-100 flex-wrap">
            <div class="flex-1 min-w-[200px]">
                <div class="text-xs font-mono font-semibold text-slate-500">{{ $fr->reference }}</div>
                <div class="text-sm font-semibold text-slate-800">{{ $fr->title }}</div>
                <div class="text-[11px] text-slate-400">Pengaju: {{ $fr->requester?->name ?? '-' }} &middot; Cair {{ $fr->disbursed_at->format('d/m/Y') }}</div>
            </div>
            <div class="text-sm font-bold font-mono text-slate-700">Rp {{ number_format($fr->amount, 0, ',', '.') }}</div>
            <div class="flex items-center gap-1.5">
                @foreach($fr->disbursementProofs as $proof)
                <a href="{{ $proof->url }}" target="_blank" class="px-2 py-1 rounded text-[10px] font-semibold bg-blue-50 text-blue-600 hover:bg-blue-100 no-underline">Bukti</a>
                @endforeach
                <a href="{{ route('fund-requests.show', $fr) }}" class="px-2 py-1 rounded text-[10px] font-semibold bg-slate-100 text-slate-600 hover:bg-slate-200 no-underline">Detail</a>
            </div>
        </div>
        @endforeach
    </div>

    <div class="px-5 py-3.5 border-t border-slate-50 flex gap-2 flex-wrap items-center">
        @foreach(['kegiatan' => 'Kegiatan', 'pengadaan' => 'Pengadaan'] as $val => $label)
        <button type="button" class="btn-change-type px-3.5 py-2 rounded-lg text-xs font-semibold border border-orange-200 bg-orange-50 text-orange-700 hover:bg-orange-100 cursor-pointer transition-colors"
            data-url="{{ route('finance.verifikasi-pembayaran.change-type', $program) }}"
            data-type="{{ $val }}" data-label="{{ $label }}"
            data-program="{{ $program->name }}" data-count="{{ $frs->count() }}"
            data-total="Rp {{ number_format($totalCair, 0, ',', '.') }}">
            Ubah jadi {{ $label }}
        </button>
        @endforeach
        @unless($program->payment_verified_at)
        <form method="POST" action="{{ route('finance.verifikasi-pembayaran.verify', $program) }}" class="ml-auto">
            @csrf
            <button type="submit" class="px-3.5 py-2 rounded-lg text-xs font-semibold border-0 bg-green-500 text-white hover:bg-green-600 cursor-pointer transition-colors">Sudah dicek, memang Pembayaran</button>
        </form>
        @endunless
    </div>
</div>
@empty
<div class="bg-white rounded-xl shadow-sm py-16 px-5 text-center">
    <div class="text-sm font-semibold text-slate-600">{{ $tab === 'belum' ? 'Semua program pembayaran sudah dicek' : 'Belum ada program yang dicek' }}</div>
    <div class="text-xs text-slate-400 mt-1">{{ $tab === 'belum' ? 'Program pembayaran yang dananya sudah cair akan muncul di sini untuk dicek.' : 'Program yang dinyatakan memang pembayaran akan muncul di sini.' }}</div>
</div>
@endforelse

@if($programs->hasPages())
<div class="mt-4">{{ $programs->links() }}</div>
@endif

{{-- Modal konfirmasi ubah jenis --}}
<div id="type-overlay" class="fixed inset-0 z-[999] bg-slate-900/50 backdrop-blur-sm items-center justify-center p-4" style="display:none;">
    <div class="bg-white rounded-2xl w-[480px] max-w-full shadow-2xl flex flex-col" style="max-height:88vh;">
        <div class="px-6 py-5 border-b border-slate-100 shrink-0">
            <div class="text-sm font-bold text-orange-700">Ubah Jenis Program</div>
            <div id="type-program" class="text-[11px] text-slate-500 mt-0.5"></div>
        </div>
        <form id="type-form" method="POST" action="" class="flex flex-col overflow-hidden" style="min-height:0;">
            @csrf
            <input type="hidden" name="type" id="type-input">
            <div class="px-6 py-5 overflow-y-auto text-sm text-slate-700 flex flex-col gap-3" style="min-height:0;">
                <p class="m-0">Jenis program diubah dari <strong>Pembayaran</strong> menjadi <strong id="type-label"></strong>. Dampaknya:</p>
                <ul class="m-0 pl-5 flex flex-col gap-1.5 text-[0.82rem]">
                    <li><strong id="type-count"></strong> pengajuan yang sudah cair di program ini ikut berubah, total <strong id="type-total"></strong>.</li>
                    <li>Jurnal koreksi diposting otomatis: dana yang sudah dibebankan ke Beban dipindah ke <strong>Uang Muka</strong>. Beban baru diakui saat laporan disetujui.</li>
                    <li>Pengaju <strong>wajib membuat laporan</strong> dalam {{ \App\Models\FundRequest::REPORT_DEADLINE_DAYS }} hari sejak sekarang dan mendapat notifikasi. Lewat batas, pengaju tidak bisa membuat pengajuan baru.</li>
                    <li>Perubahan ini langsung berlaku tanpa approval.</li>
                </ul>
            </div>
            <div class="px-6 py-4 border-t border-slate-100 flex gap-2 justify-end shrink-0">
                <button type="button" id="type-cancel" class="px-4 py-2.5 rounded-xl bg-slate-100 text-slate-700 border border-slate-200 text-sm font-medium cursor-pointer hover:bg-slate-200 transition-colors">Batal</button>
                <button type="submit" class="px-5 py-2.5 rounded-xl text-sm font-semibold bg-orange-500 text-white border-0 cursor-pointer hover:bg-orange-600 transition-colors">Ya, Ubah Jenis</button>
            </div>
        </form>
    </div>
</div>

<script>
(function () {
    var overlay = document.getElementById('type-overlay');
    var form = document.getElementById('type-form');
    function close() { overlay.style.display = 'none'; }
    document.querySelectorAll('.btn-change-type').forEach(function (btn) {
        btn.addEventListener('click', function () {
            form.action = btn.dataset.url;
            document.getElementById('type-input').value = btn.dataset.type;
            document.getElementById('type-label').textContent = btn.dataset.label;
            document.getElementById('type-program').textContent = btn.dataset.program;
            document.getElementById('type-count').textContent = btn.dataset.count;
            document.getElementById('type-total').textContent = btn.dataset.total;
            overlay.style.display = 'flex';
        });
    });
    document.getElementById('type-cancel').addEventListener('click', close);
    overlay.addEventListener('click', function (e) { if (e.target === overlay) close(); });
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape') close(); });
})();
</script>

</x-layouts.app>
