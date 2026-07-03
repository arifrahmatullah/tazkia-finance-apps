<x-layouts.app title="Edit Akun">

<style>
.type-card.selected { border-color:var(--tc); background:var(--bg); }
.type-card:hover { border-color:#f97316; background:#fff7ed; }
.balance-opt.selected-debit  { border-color:#2563eb; background:#eff6ff; }
.balance-opt.selected-kredit { border-color:#16a34a; background:#f0fdf4; }
.toggle input:checked + .toggle-slider { background:#f97316; }
.toggle input:checked + .toggle-slider::before { transform:translateX(20px); }
.toggle-slider::before { content:''; position:absolute; width:16px; height:16px; left:3px; top:3px; background:#fff; border-radius:50%; transition:.2s; }
</style>

<a href="{{ route('accounts.index', ['organization_id' => $account->organization_id]) }}" class="inline-flex items-center gap-1.5 text-sm text-slate-500 hover:text-orange-500 mb-5 no-underline">
    <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M19 12H5M12 5l-7 7 7 7"/></svg>
    Kembali ke Chart of Accounts
</a>

<h1 class="text-xl font-bold text-slate-900 m-0 mb-2">Edit Akun</h1>
<div class="mb-5">
    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-slate-100 rounded-lg text-sm text-slate-600 font-medium">
        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M3 21h18M3 7v1a3 3 0 006 0V7m6 0v1a3 3 0 006 0V7M3 7l2-4h14l2 4"/></svg>
        {{ $account->organization->name }}
    </span>
</div>

<div class="bg-white rounded-xl shadow-sm p-6">
    <form method="POST" action="{{ route('accounts.update', $account) }}">
        @csrf @method('PUT')

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
        $curType = old('account_type', $account->account_type);
        @endphp
        <div class="grid grid-cols-5 gap-2 mb-5">
            @foreach($types as $key => $t)
            <label class="type-card {{ $curType === $key ? 'selected' : '' }} p-2.5 border-2 border-slate-200 rounded-xl cursor-pointer text-center transition-all"
                   style="{{ $curType === $key ? '--tc:'.$t['color'].';--bg:'.$t['bg'] : '' }}"
                   data-color="{{ $t['color'] }}" data-bg="{{ $t['bg'] }}"
                   data-normal="{{ $t['normal'] === 'Debit' ? 'debit' : 'kredit' }}">
                <input type="radio" name="account_type" value="{{ $key }}" {{ $curType === $key ? 'checked' : '' }}
                    onchange="selectType(this, '{{ $t['color'] }}', '{{ $t['bg'] }}', '{{ $t['normal'] === 'Debit' ? 'debit' : 'kredit' }}')"
                    class="hidden">
                <div class="text-xl mb-0.5">{{ $t['icon'] }}</div>
                <div class="text-[11px] font-bold" style="{{ $curType === $key ? 'color:'.$t['color'] : '' }}">{{ $t['label'] }}</div>
                <div class="text-[10px] text-slate-400 mt-0.5">{{ $t['normal'] }}</div>
            </label>
            @endforeach
        </div>

        {{-- Saldo Normal --}}
        <div class="text-[11px] font-bold text-slate-400 uppercase tracking-widest mb-3 pb-2 border-b border-slate-100 mt-5">Saldo Normal</div>
        @error('normal_balance')<div class="text-xs text-red-500 mb-2">{{ $message }}</div>@enderror
        @php $curBal = old('normal_balance', $account->normal_balance); @endphp
        <div class="grid grid-cols-2 gap-2 mb-5">
            <label class="balance-opt {{ $curBal === 'debit' ? 'selected-debit' : '' }} p-2.5 border-2 border-slate-200 rounded-xl cursor-pointer text-center transition-all hover:border-orange-400" id="opt-debit">
                <input type="radio" name="normal_balance" value="debit" {{ $curBal === 'debit' ? 'checked' : '' }}
                    onchange="selectBalance('debit')" class="hidden">
                <div class="text-sm font-bold" style="{{ $curBal === 'debit' ? 'color:#2563eb' : '' }}">Debit</div>
                <div class="text-[11px] text-slate-400">Bertambah saat debit</div>
            </label>
            <label class="balance-opt {{ $curBal === 'kredit' ? 'selected-kredit' : '' }} p-2.5 border-2 border-slate-200 rounded-xl cursor-pointer text-center transition-all hover:border-orange-400" id="opt-kredit">
                <input type="radio" name="normal_balance" value="kredit" {{ $curBal === 'kredit' ? 'checked' : '' }}
                    onchange="selectBalance('kredit')" class="hidden">
                <div class="text-sm font-bold" style="{{ $curBal === 'kredit' ? 'color:#16a34a' : '' }}">Kredit</div>
                <div class="text-[11px] text-slate-400">Bertambah saat kredit</div>
            </label>
        </div>

        {{-- Detail Akun --}}
        <div class="text-[11px] font-bold text-slate-400 uppercase tracking-widest mb-3 pb-2 border-b border-slate-100">Detail Akun</div>
        <div class="grid grid-cols-2 gap-4">
            <div class="flex flex-col gap-1.5">
                <label class="text-xs font-semibold text-slate-600">Kode Akun <span class="text-red-500 ml-0.5">*</span></label>
                <input type="text" name="code" value="{{ old('code', $account->code) }}"
                    class="w-full px-3 py-2.5 border rounded-xl text-sm font-mono uppercase bg-white outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100 transition-colors {{ $errors->has('code') ? 'border-red-400' : 'border-slate-200' }}"
                    placeholder="contoh: 1-1100" maxlength="20"
                    oninput="this.value=this.value.toUpperCase()">
                @error('code')<div class="text-xs text-red-500 mt-0.5">{{ $message }}</div>@enderror
            </div>
            <div class="flex flex-col gap-1.5">
                <label class="text-xs font-semibold text-slate-600">Nama Akun <span class="text-red-500 ml-0.5">*</span></label>
                <input type="text" name="name" value="{{ old('name', $account->name) }}"
                    class="w-full px-3 py-2.5 border rounded-xl text-sm text-slate-800 bg-white outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100 transition-colors {{ $errors->has('name') ? 'border-red-400' : 'border-slate-200' }}"
                    placeholder="contoh: Kas Kecil">
                @error('name')<div class="text-xs text-red-500 mt-0.5">{{ $message }}</div>@enderror
            </div>
            <div class="flex flex-col gap-1.5">
                <label class="text-xs font-semibold text-slate-600">Akun Induk (Opsional)</label>
                <select name="parent_id"
                    class="w-full px-3 py-2.5 border border-slate-200 rounded-xl text-sm text-slate-800 bg-white outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100 transition-colors">
                    <option value="">-- Tidak Ada (Akun Utama) --</option>
                    @foreach($parents as $p)
                        <option value="{{ $p->id }}" {{ old('parent_id', $account->parent_id) == $p->id ? 'selected' : '' }}>
                            {{ $p->code }} – {{ $p->name }}
                        </option>
                    @endforeach
                </select>
                @error('parent_id')<div class="text-xs text-red-500 mt-0.5">{{ $message }}</div>@enderror
            </div>
            <div class="flex flex-col gap-1.5">
                <label class="text-xs font-semibold text-slate-600">Status</label>
                <div class="flex items-center gap-3 px-3.5 py-2.5 border border-slate-200 rounded-xl">
                    <label class="toggle relative w-[42px] h-[22px]">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" name="is_active" value="1" {{ old('is_active', $account->is_active) ? 'checked' : '' }}
                            class="opacity-0 w-0 h-0 absolute">
                        <span class="toggle-slider absolute inset-0 bg-slate-200 rounded-full cursor-pointer transition-[.2s]"></span>
                    </label>
                    <span class="text-sm text-slate-700">Akun Aktif</span>
                </div>
            </div>
            <div class="flex flex-col gap-1.5">
                <label class="text-xs font-semibold text-slate-600">Akun Induk?</label>
                <div class="flex items-center gap-3 px-3.5 py-2.5 border border-slate-200 rounded-xl">
                    <label class="toggle relative w-[42px] h-[22px]">
                        <input type="hidden" name="is_header" value="0">
                        <input type="checkbox" name="is_header" value="1" {{ old('is_header', $account->is_header) ? 'checked' : '' }}
                            class="opacity-0 w-0 h-0 absolute">
                        <span class="toggle-slider absolute inset-0 bg-slate-200 rounded-full cursor-pointer transition-[.2s]"></span>
                    </label>
                    <span class="text-sm text-slate-700">Jadikan akun induk</span>
                </div>
            </div>
            <div class="flex flex-col gap-1.5 col-span-2">
                <label class="text-xs font-semibold text-slate-600">Keterangan</label>
                <textarea name="description" class="w-full px-3 py-2.5 border border-slate-200 rounded-xl text-sm text-slate-800 bg-white outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100 transition-colors resize-y" rows="2">{{ old('description', $account->description) }}</textarea>
                @error('description')<div class="text-xs text-red-500 mt-0.5">{{ $message }}</div>@enderror
            </div>
        </div>

        <div class="flex gap-3 justify-end mt-6 pt-5 border-t border-slate-100">
            <a href="{{ route('accounts.index', ['organization_id' => $account->organization_id]) }}" class="px-5 py-2.5 rounded-xl border border-slate-200 bg-white text-slate-600 text-sm font-medium no-underline inline-flex items-center">Batal</a>
            <button type="submit" class="px-6 py-2.5 rounded-xl text-sm font-semibold bg-gradient-to-br from-orange-400 to-orange-500 text-white border-0 cursor-pointer hover:-translate-y-px transition-all">Simpan Perubahan</button>
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
    dOpt.classList.remove('selected-debit', 'selected-kredit');
    kOpt.classList.remove('selected-debit', 'selected-kredit');
    dOpt.querySelector('div').style.color = '';
    kOpt.querySelector('div').style.color = '';
    if (val === 'debit') {
        dOpt.classList.add('selected-debit');
        dOpt.querySelector('div').style.color = '#2563eb';
    } else {
        kOpt.classList.add('selected-kredit');
        kOpt.querySelector('div').style.color = '#16a34a';
    }
}
</script>
</x-layouts.app>
