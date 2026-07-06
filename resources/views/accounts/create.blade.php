<x-layouts.app title="Tambah Akun">

<style>
.type-card.selected { border-color:var(--tc); background:var(--bg); }
.type-card:hover { border-color:#f97316; background:#fff7ed; }
.balance-opt.selected-debit  { border-color:#2563eb; background:#eff6ff; }
.balance-opt.selected-kredit { border-color:#16a34a; background:#f0fdf4; }
.toggle input:checked + .toggle-slider { background:#f97316; }
.toggle input:checked + .toggle-slider::before { transform:translateX(20px); }
.toggle-slider::before { content:''; position:absolute; width:16px; height:16px; left:3px; top:3px; background:#fff; border-radius:50%; transition:.2s; }
</style>

<a href="{{ route('accounts.index', ['organization_id' => request('organization_id')]) }}" class="inline-flex items-center gap-1.5 text-sm text-slate-500 hover:text-orange-500 mb-5 no-underline">
    <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M19 12H5M12 5l-7 7 7 7"/></svg>
    Kembali ke Chart of Accounts
</a>

<h1 class="text-xl font-bold text-slate-900 m-0 mb-0.5">Tambah Akun Baru</h1>
<p class="text-sm text-slate-400 mb-5">Isi detail akun akuntansi sesuai standar COA</p>

<div class="bg-white rounded-xl shadow-sm p-6">
    <form method="POST" action="{{ route('accounts.store') }}">
        @csrf

        {{-- Organisasi --}}
        @if($organizations->count() > 1)
        <div class="flex flex-col gap-1.5 mb-5">
            <label class="text-xs font-semibold text-slate-600">Organisasi <span class="text-red-500 ml-0.5">*</span></label>
            <select name="organization_id" id="org_select"
                class="w-full px-3 py-2.5 border rounded-xl text-sm text-slate-800 bg-white outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100 transition-colors {{ $errors->has('organization_id') ? 'border-red-400' : 'border-slate-200' }}"
                onchange="loadParents(this.value)">
                <option value="">-- Pilih Organisasi --</option>
                @foreach($organizations as $org)
                    <option value="{{ $org->id }}" {{ old('organization_id', $selectedOrg?->id) == $org->id ? 'selected' : '' }}>{{ $org->name }}</option>
                @endforeach
            </select>
            @error('organization_id')<div class="text-xs text-red-500 mt-0.5">{{ $message }}</div>@enderror
        </div>
        @else
        <input type="hidden" name="organization_id" value="{{ $organizations->first()?->id }}">
        @endif

        {{-- Tipe Akun --}}
        <div class="text-[11px] font-bold text-slate-400 uppercase tracking-widest mb-3 pb-2 border-b border-slate-100">Tipe Akun</div>
        @error('account_type')<div class="text-xs text-red-500 mb-2">{{ $message }}</div>@enderror
        @php
        $types = [
            'aset'       => ['icon'=>'🏦','label'=>'Aset','normal'=>'Debit','color'=>'#2563eb','bg'=>'#eff6ff'],
            'kewajiban'  => ['icon'=>'💳','label'=>'Kewajiban','normal'=>'Kredit','color'=>'#dc2626','bg'=>'#fff1f2'],
            'ekuitas'    => ['icon'=>'⚖️','label'=>'Ekuitas','normal'=>'Kredit','color'=>'#7c3aed','bg'=>'#f5f3ff'],
            'pendapatan' => ['icon'=>'📈','label'=>'Pendapatan','normal'=>'Kredit','color'=>'#16a34a','bg'=>'#f0fdf4'],
            'beban'      => ['icon'=>'📉','label'=>'Beban','normal'=>'Debit','color'=>'#ea580c','bg'=>'#fff7ed'],
        ];
        $oldType = old('account_type', '');
        @endphp
        <div class="grid grid-cols-5 gap-2 mb-5" id="typeGrid">
            @foreach($types as $key => $t)
            <label class="type-card {{ $oldType === $key ? 'selected' : '' }} p-2.5 border-2 border-slate-200 rounded-xl cursor-pointer text-center transition-all"
                   style="{{ $oldType === $key ? '--tc:'.$t['color'].';--bg:'.$t['bg'] : '' }}"
                   data-color="{{ $t['color'] }}" data-bg="{{ $t['bg'] }}"
                   data-normal="{{ strtolower($t['normal'] === 'Debit' ? 'debit' : 'kredit') }}">
                <input type="radio" name="account_type" value="{{ $key }}" {{ $oldType === $key ? 'checked' : '' }}
                    onchange="selectType(this, '{{ $t['color'] }}', '{{ $t['bg'] }}', '{{ strtolower($t['normal'] === 'Debit' ? 'debit' : 'kredit') }}')"
                    class="hidden">
                <div class="text-xl mb-0.5">{{ $t['icon'] }}</div>
                <div class="text-[11px] font-bold" style="{{ $oldType === $key ? 'color:'.$t['color'] : '' }}">{{ $t['label'] }}</div>
                <div class="text-[10px] text-slate-400 mt-0.5">{{ $t['normal'] }}</div>
            </label>
            @endforeach
        </div>

        {{-- Saldo Normal --}}
        <div class="text-[11px] font-bold text-slate-400 uppercase tracking-widest mb-3 pb-2 border-b border-slate-100 mt-5">
            Saldo Normal
            <span class="ml-1.5 normal-case font-normal text-slate-300">(otomatis dari tipe akun)</span>
        </div>
        @error('normal_balance')<div class="text-xs text-red-500 mb-2">{{ $message }}</div>@enderror
        <input type="hidden" name="normal_balance" id="normalBalanceInput" value="{{ old('normal_balance', '') }}">
        <div class="grid grid-cols-2 gap-2 mb-5">
            <div class="balance-opt p-2.5 border-2 border-slate-200 rounded-xl text-center transition-all select-none" id="opt-debit">
                <div class="text-sm font-bold text-slate-400" id="opt-debit-text">Debit</div>
                <div class="text-[11px] text-slate-300">Bertambah saat debit</div>
            </div>
            <div class="balance-opt p-2.5 border-2 border-slate-200 rounded-xl text-center transition-all select-none" id="opt-kredit">
                <div class="text-sm font-bold text-slate-400" id="opt-kredit-text">Kredit</div>
                <div class="text-[11px] text-slate-300">Bertambah saat kredit</div>
            </div>
        </div>

        {{-- Detail Akun --}}
        <div class="text-[11px] font-bold text-slate-400 uppercase tracking-widest mb-3 pb-2 border-b border-slate-100">Detail Akun</div>
        <div class="grid grid-cols-2 gap-4">
            <div class="flex flex-col gap-1.5">
                <label class="text-xs font-semibold text-slate-600">Kode Akun <span class="text-red-500 ml-0.5">*</span></label>
                <input type="text" name="code" value="{{ old('code') }}" id="codeInput"
                    class="w-full px-3 py-2.5 border rounded-xl text-sm font-mono uppercase bg-white outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100 transition-colors {{ $errors->has('code') ? 'border-red-400' : 'border-slate-200' }}"
                    placeholder="contoh: 1-1100" maxlength="20"
                    oninput="this.value=this.value.toUpperCase()">
                <div class="text-xs text-slate-400 mt-0.5">Kode unik dalam satu organisasi, contoh: 1-1100, 1-1101</div>
                @error('code')<div class="text-xs text-red-500 mt-0.5">{{ $message }}</div>@enderror
            </div>
            <div class="flex flex-col gap-1.5">
                <label class="text-xs font-semibold text-slate-600">Nama Akun <span class="text-red-500 ml-0.5">*</span></label>
                <input type="text" name="name" value="{{ old('name') }}"
                    class="w-full px-3 py-2.5 border rounded-xl text-sm text-slate-800 bg-white outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100 transition-colors {{ $errors->has('name') ? 'border-red-400' : 'border-slate-200' }}"
                    placeholder="contoh: Kas Kecil">
                @error('name')<div class="text-xs text-red-500 mt-0.5">{{ $message }}</div>@enderror
            </div>
            <div class="flex flex-col gap-1.5">
                <label class="text-xs font-semibold text-slate-600">Akun Induk (Opsional)</label>
                <select name="parent_id" id="parent_select"
                    class="w-full px-3 py-2.5 border border-slate-200 rounded-xl text-sm text-slate-800 bg-white outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100 transition-colors">
                    <option value="">-- Tidak Ada (Akun Utama) --</option>
                    @foreach($parents as $p)
                        <option value="{{ $p->id }}" {{ old('parent_id') == $p->id ? 'selected' : '' }}>
                            {{ $p->code }} – {{ $p->name }}
                        </option>
                    @endforeach
                </select>
                <div class="text-xs text-slate-400 mt-0.5">Pilih jika ini adalah sub-akun</div>
                @error('parent_id')<div class="text-xs text-red-500 mt-0.5">{{ $message }}</div>@enderror
            </div>
            <div class="flex flex-col gap-1.5">
                <label class="text-xs font-semibold text-slate-600">Akun Induk?</label>
                <div class="flex items-center gap-3 px-3.5 py-2.5 border border-slate-200 rounded-xl">
                    <label class="toggle relative w-[42px] h-[22px]">
                        <input type="hidden" name="is_header" value="0">
                        <input type="checkbox" name="is_header" value="1" {{ old('is_header') ? 'checked' : '' }}
                            class="opacity-0 w-0 h-0 absolute">
                        <span class="toggle-slider absolute inset-0 bg-slate-200 rounded-full cursor-pointer transition-[.2s]"></span>
                    </label>
                    <span class="text-sm text-slate-700">Jadikan akun induk (tidak dapat diposting)</span>
                </div>
            </div>
            <div class="flex flex-col gap-1.5 col-span-2">
                <label class="text-xs font-semibold text-slate-600">Keterangan</label>
                <textarea name="description" class="w-full px-3 py-2.5 border border-slate-200 rounded-xl text-sm text-slate-800 bg-white outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100 transition-colors resize-y" rows="2"
                    placeholder="Deskripsi singkat akun ini (opsional)">{{ old('description') }}</textarea>
                @error('description')<div class="text-xs text-red-500 mt-0.5">{{ $message }}</div>@enderror
            </div>
        </div>

        <div class="flex gap-3 justify-end mt-6 pt-5 border-t border-slate-100">
            <a href="{{ route('accounts.index', ['organization_id' => request('organization_id')]) }}" class="px-5 py-2.5 rounded-xl border border-slate-200 bg-white text-slate-600 text-sm font-medium no-underline inline-flex items-center">Batal</a>
            <button type="submit" class="px-6 py-2.5 rounded-xl text-sm font-semibold bg-gradient-to-br from-orange-400 to-orange-500 text-white border-0 cursor-pointer hover:-translate-y-px transition-all">Simpan Akun</button>
        </div>
    </form>
</div>

<script>
function selectType(radio, color, bg, normal) {
    document.querySelectorAll('.type-card').forEach(c => {
        c.classList.remove('selected');
        c.style.removeProperty('--tc');
        c.style.removeProperty('--bg');
        c.querySelector('div:nth-child(2)').style.color = '';
    });
    const card = radio.closest('.type-card');
    card.classList.add('selected');
    card.style.setProperty('--tc', color);
    card.style.setProperty('--bg', bg);
    card.querySelector('div:nth-child(2)').style.color = color;
    selectBalance(normal);
    document.querySelector(`input[name="normal_balance"][value="${normal}"]`).checked = true;
}

function selectBalance(val) {
    const dOpt = document.getElementById('opt-debit');
    const kOpt = document.getElementById('opt-kredit');
    const dTxt = document.getElementById('opt-debit-text');
    const kTxt = document.getElementById('opt-kredit-text');

    dOpt.classList.remove('selected-debit', 'selected-kredit');
    kOpt.classList.remove('selected-debit', 'selected-kredit');
    dOpt.style.borderColor = '';
    kOpt.style.borderColor = '';
    dTxt.style.color = '#94a3b8';
    kTxt.style.color = '#94a3b8';

    document.getElementById('normalBalanceInput').value = val;

    if (val === 'debit') {
        dOpt.style.borderColor = '#2563eb';
        dOpt.style.background = '#eff6ff';
        kOpt.style.background = '';
        dTxt.style.color = '#2563eb';
    } else {
        kOpt.style.borderColor = '#16a34a';
        kOpt.style.background = '#f0fdf4';
        dOpt.style.background = '';
        kTxt.style.color = '#16a34a';
    }
}

function loadParents(orgId) {
    const sel = document.getElementById('parent_select');
    sel.innerHTML = '<option value="">-- Tidak Ada (Akun Utama) --</option>';
    if (!orgId) return;
    fetch(`{{ route('accounts.parents') }}?organization_id=${orgId}`)
        .then(r => r.json())
        .then(data => {
            data.forEach(p => {
                const opt = document.createElement('option');
                opt.value = p.id;
                opt.textContent = `${p.code} – ${p.name}`;
                sel.appendChild(opt);
            });
        });
}

document.addEventListener('DOMContentLoaded', function () {
    const checked = document.querySelector('input[name="account_type"]:checked');
    if (checked) {
        const card = checked.closest('.type-card');
        card.style.setProperty('--tc', card.dataset.color);
        card.style.setProperty('--bg', card.dataset.bg);
        card.querySelector('div:nth-child(2)').style.color = card.dataset.color;
    }
    const existingBal = document.getElementById('normalBalanceInput').value;
    if (existingBal) selectBalance(existingBal);
});
</script>
</x-layouts.app>
