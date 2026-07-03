<x-layouts.app title="Chart of Accounts">
@php
$typeInfo = App\Models\Account::TYPES;
$typeOrder = ['aset','kewajiban','ekuitas','pendapatan','beban'];
@endphp

<div class="flex items-center justify-between mb-5">
    <div>
        <h2 class="text-lg font-bold text-slate-900 m-0 mb-0.5">Chart of Accounts</h2>
        <p class="text-xs text-slate-400 m-0">Bagan akun akuntansi per organisasi</p>
    </div>
    <a href="{{ route('accounts.create', ['organization_id' => request('organization_id')]) }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-gradient-to-br from-orange-400 to-orange-500 text-white text-sm font-semibold shadow-sm hover:-translate-y-px transition-all no-underline">
        <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg>
        Tambah Akun
    </a>
</div>

@if(session('success'))
<div class="flex items-center gap-2.5 px-4 py-3 bg-green-50 border border-green-200 rounded-xl mb-4 text-sm text-green-700">
    <svg width="16" height="16" fill="#16a34a" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
    {{ session('success') }}
</div>
@endif

<form method="GET" action="{{ route('accounts.index') }}" class="flex gap-2.5 flex-wrap items-center mb-4">
    @if($organizations->count() > 1)
    <select name="organization_id" class="px-3 py-2 border border-slate-200 rounded-xl text-sm text-slate-800 bg-white outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100 transition-colors min-w-[180px]"
        onchange="this.form.submit()">
        <option value="">Semua Organisasi</option>
        @foreach($organizations as $org)
            <option value="{{ $org->id }}" {{ request('organization_id') == $org->id ? 'selected' : '' }}>{{ $org->name }}</option>
        @endforeach
    </select>
    @else
        <input type="hidden" name="organization_id" value="{{ $organizations->first()?->id }}">
    @endif

    <select name="account_type" class="px-3 py-2 border border-slate-200 rounded-xl text-sm text-slate-800 bg-white outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100 transition-colors min-w-[150px]">
        <option value="">Semua Tipe</option>
        @foreach($typeOrder as $type)
            <option value="{{ $type }}" {{ request('account_type') === $type ? 'selected' : '' }}>
                {{ $typeInfo[$type]['label'] }}
            </option>
        @endforeach
    </select>

    <div class="relative flex-1 min-w-[200px]">
        <svg width="15" height="15" fill="none" stroke="#94a3b8" stroke-width="2" viewBox="0 0 24 24" class="absolute left-[11px] top-1/2 -translate-y-1/2 pointer-events-none"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari kode atau nama akun..." class="w-full pl-[34px] px-3 py-2 border border-slate-200 rounded-xl text-sm text-slate-800 bg-white outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100 transition-colors">
    </div>

    <button type="submit" class="px-4 py-2 rounded-xl border-0 cursor-pointer text-sm font-semibold bg-gradient-to-br from-orange-400 to-orange-500 text-white">Cari</button>
    @if(request()->hasAny(['search','account_type']) || (request('organization_id') && $organizations->count() > 1))
        <a href="{{ route('accounts.index', ['organization_id' => request('organization_id')]) }}" class="px-3.5 py-2 rounded-xl border border-slate-200 text-sm text-slate-500 no-underline bg-white">Reset</a>
    @endif
</form>

@if($accounts->isEmpty())
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="py-12 px-5 text-center text-slate-400">
            <svg width="40" height="40" fill="none" stroke="#cbd5e1" stroke-width="1.5" viewBox="0 0 24 24" class="mx-auto mb-2.5 block"><path d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            <p class="text-sm m-0">Belum ada akun. Klik "Tambah Akun" untuk mulai membuat chart of accounts.</p>
        </div>
    </div>
@else
    @foreach($typeOrder as $type)
        @php $typeAccounts = $grouped->get($type, collect()); @endphp
        @if($typeAccounts->isEmpty()) @continue @endif

        <div class="bg-white rounded-xl shadow-sm overflow-hidden mb-3.5">
            <div class="px-5 py-3 flex items-center gap-2.5" style="border-bottom:2px solid {{ $typeInfo[$type]['color'] }}20;">
                <div class="w-8 h-8 rounded-lg flex items-center justify-center text-sm font-bold text-white" style="background:{{ $typeInfo[$type]['color'] }};">
                    {{ strtoupper(substr($typeInfo[$type]['label'], 0, 1)) }}
                </div>
                <div>
                    <div class="text-sm font-bold" style="color:{{ $typeInfo[$type]['color'] }};">{{ $typeInfo[$type]['label'] }}</div>
                    <div class="text-[11px] text-slate-400">Normal: {{ ucfirst($typeInfo[$type]['normal']) }}</div>
                </div>
                <span class="text-[11px] text-slate-400 ml-auto bg-slate-100 px-2 py-0.5 rounded-full">{{ $typeAccounts->count() }} akun</span>
            </div>
            <table class="w-full border-collapse">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-100">
                        <th class="px-4 py-3 text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wide w-[130px]">Kode</th>
                        <th class="px-4 py-3 text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wide">Nama Akun</th>
                        <th class="px-4 py-3 text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wide w-[110px]">Saldo Normal</th>
                        <th class="px-4 py-3 text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wide w-[80px]">Tipe</th>
                        <th class="px-4 py-3 text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wide w-[80px]">Status</th>
                        <th class="px-4 py-3 text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wide w-[120px]">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($typeAccounts as $acc)
                    @php $isHeader = $acc->is_header; $hasParent = $acc->parent_id; @endphp
                    <tr class="border-b border-slate-50 hover:bg-slate-50 transition-colors last:border-b-0 {{ $isHeader ? 'bg-slate-50' : '' }}">
                        <td class="px-4 py-3 text-sm text-slate-600 align-middle">
                            <span class="font-mono text-xs font-bold px-2 py-0.5 rounded" style="background:{{ $typeInfo[$type]['color'] }}18;color:{{ $typeInfo[$type]['color'] }};">
                                {{ $acc->code }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-sm text-slate-600 align-middle {{ $hasParent ? 'pl-8' : '' }}">
                            @if($isHeader)
                                <svg width="13" height="13" fill="none" stroke="#94a3b8" stroke-width="2" viewBox="0 0 24 24" class="inline-block align-middle mr-1"><path d="M22 19a2 2 0 01-2 2H4a2 2 0 01-2-2V5a2 2 0 012-2h5l2 3h9a2 2 0 012 2z"/></svg>
                            @else
                                <svg width="13" height="13" fill="none" stroke="#cbd5e1" stroke-width="2" viewBox="0 0 24 24" class="inline-block align-middle mr-1"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                            @endif
                            <span class="{{ $isHeader ? 'font-bold text-slate-900' : 'text-slate-700' }}">{{ $acc->name }}</span>
                            @if($isHeader)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-yellow-100 text-yellow-700 ml-1.5">Induk</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-sm text-slate-600 align-middle">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold {{ $acc->normal_balance === 'debit' ? 'bg-blue-100 text-blue-700' : 'bg-green-100 text-green-700' }}">
                                {{ ucfirst($acc->normal_balance) }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-xs text-slate-500 align-middle">{{ $acc->type_label }}</td>
                        <td class="px-4 py-3 text-sm text-slate-600 align-middle">
                            @if($acc->is_active)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-green-100 text-green-700">Aktif</span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-slate-100 text-slate-500">Nonaktif</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-sm text-slate-600 align-middle">
                            <div class="flex gap-1.5">
                                <a href="{{ route('accounts.edit', $acc) }}" class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-xs font-medium bg-blue-50 text-blue-600 hover:bg-blue-100 transition-colors no-underline">Edit</a>
                                <form id="del-acc-{{ $acc->id }}" method="POST" action="{{ route('accounts.destroy', $acc) }}">
                                    @csrf @method('DELETE')
                                </form>
                                <button type="button" class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-xs font-medium bg-red-50 text-red-600 hover:bg-red-100 transition-colors border-0 cursor-pointer"
                                    onclick="confirmDelete('del-acc-{{ $acc->id }}', '{{ addslashes($acc->name) }}')">
                                    Hapus
                                </button>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endforeach
@endif
</x-layouts.app>
