@php $emp = $employee ?? null; @endphp

<style>
.toggle input:checked + .toggle-slider { background:#f97316; }
.toggle input:checked + .toggle-slider::before { transform:translateX(20px); }
.toggle-slider::before { content:''; position:absolute; width:16px; height:16px; left:3px; top:3px; background:#fff; border-radius:50%; transition:.2s; }
</style>

<div class="text-[11px] font-bold text-slate-400 uppercase tracking-widest mb-3.5 pb-2 border-b border-slate-100">Informasi Pribadi</div>
<div class="grid grid-cols-2 gap-4 mb-4">
    <div class="flex flex-col gap-1.5">
        <label class="text-xs font-semibold text-slate-600">NIK <span class="text-red-500 ml-0.5">*</span></label>
        <input type="text" name="nik" class="w-full px-3 py-2.5 border {{ $errors->has('nik') ? 'border-red-400' : 'border-slate-200' }} rounded-xl text-sm text-slate-800 bg-white outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100 transition-colors"
            value="{{ old('nik', $emp?->nik) }}"
            placeholder="Nomor Induk Karyawan"
            inputmode="numeric" maxlength="16"
            oninput="this.value=this.value.replace(/[^0-9]/g,'').slice(0,16)">
        @error('nik') <span class="text-xs text-red-500 mt-0.5">{{ $message }}</span> @enderror
    </div>
    <div class="flex flex-col gap-1.5">
        <label class="text-xs font-semibold text-slate-600">Nama Lengkap <span class="text-red-500 ml-0.5">*</span></label>
        <input type="text" name="name" class="w-full px-3 py-2.5 border {{ $errors->has('name') ? 'border-red-400' : 'border-slate-200' }} rounded-xl text-sm text-slate-800 bg-white outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100 transition-colors"
            value="{{ old('name', $emp?->name) }}" placeholder="Nama karyawan">
        @error('name') <span class="text-xs text-red-500 mt-0.5">{{ $message }}</span> @enderror
    </div>
    <div class="flex flex-col gap-1.5">
        <label class="text-xs font-semibold text-slate-600">Gelar</label>
        <input type="text" name="title" class="w-full px-3 py-2.5 border border-slate-200 rounded-xl text-sm text-slate-800 bg-white outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100 transition-colors"
            value="{{ old('title', $emp?->title) }}" placeholder="cth: S.T., M.Kom.">
        @error('title') <span class="text-xs text-red-500 mt-0.5">{{ $message }}</span> @enderror
    </div>
    <div class="flex flex-col gap-1.5">
        <label class="text-xs font-semibold text-slate-600">Tanggal Lahir</label>
        <input type="date" name="birth_date" class="w-full px-3 py-2.5 border border-slate-200 rounded-xl text-sm text-slate-800 bg-white outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100 transition-colors"
            value="{{ old('birth_date', $emp?->birth_date?->format('Y-m-d')) }}">
        @error('birth_date') <span class="text-xs text-red-500 mt-0.5">{{ $message }}</span> @enderror
    </div>
    <div class="flex flex-col gap-1.5 col-span-2">
        <label class="text-xs font-semibold text-slate-600">Jenis Kelamin</label>
        <div class="flex gap-3" id="genderGroup">
            <label class="flex items-center gap-2.5 cursor-pointer px-4 py-2.5 border rounded-xl transition-colors {{ old('gender', $emp?->gender) === 'L' ? 'border-orange-400 bg-orange-50 text-orange-700 font-semibold' : 'border-slate-200 text-slate-700 hover:border-orange-300 hover:bg-orange-50/50' }}">
                <input type="radio" name="gender" value="L" {{ old('gender', $emp?->gender) === 'L' ? 'checked' : '' }} onchange="updateGender()" class="accent-orange-500 w-[15px] h-[15px]"> Laki-laki
            </label>
            <label class="flex items-center gap-2.5 cursor-pointer px-4 py-2.5 border rounded-xl transition-colors {{ old('gender', $emp?->gender) === 'P' ? 'border-orange-400 bg-orange-50 text-orange-700 font-semibold' : 'border-slate-200 text-slate-700 hover:border-orange-300 hover:bg-orange-50/50' }}">
                <input type="radio" name="gender" value="P" {{ old('gender', $emp?->gender) === 'P' ? 'checked' : '' }} onchange="updateGender()" class="accent-orange-500 w-[15px] h-[15px]"> Perempuan
            </label>
        </div>
    </div>
</div>

<div class="text-[11px] font-bold text-slate-400 uppercase tracking-widest mb-3.5 pb-2 border-b border-slate-100 mt-2">Kontak & Identitas</div>
<div class="grid grid-cols-2 gap-4 mb-4">
    <div class="flex flex-col gap-1.5">
        <label class="text-xs font-semibold text-slate-600">Email</label>
        <input type="email" name="email" class="w-full px-3 py-2.5 border border-slate-200 rounded-xl text-sm text-slate-800 bg-white outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100 transition-colors"
            value="{{ old('email', $emp?->email) }}" placeholder="email@contoh.com">
        @error('email') <span class="text-xs text-red-500 mt-0.5">{{ $message }}</span> @enderror
    </div>
    <div class="flex flex-col gap-1.5">
        <label class="text-xs font-semibold text-slate-600">Nomor Telepon</label>
        <input type="text" name="phone" class="w-full px-3 py-2.5 border border-slate-200 rounded-xl text-sm text-slate-800 bg-white outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100 transition-colors"
            value="{{ old('phone', $emp?->phone) }}" placeholder="08xxxxxxxxxx">
        @error('phone') <span class="text-xs text-red-500 mt-0.5">{{ $message }}</span> @enderror
    </div>
    <div class="flex flex-col gap-1.5">
        <label class="text-xs font-semibold text-slate-600">NIDN</label>
        <input type="text" name="nidn" class="w-full px-3 py-2.5 border border-slate-200 rounded-xl text-sm text-slate-800 bg-white outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100 transition-colors"
            value="{{ old('nidn', $emp?->nidn) }}" placeholder="Nomor Induk Dosen Nasional">
        <span class="text-xs text-slate-400 mt-0.5">Kosongkan jika bukan dosen</span>
        @error('nidn') <span class="text-xs text-red-500 mt-0.5">{{ $message }}</span> @enderror
    </div>
    <div class="flex flex-col gap-1.5">
        <label class="text-xs font-semibold text-slate-600">RFID</label>
        <input type="text" name="rfid" class="w-full px-3 py-2.5 border border-slate-200 rounded-xl text-sm text-slate-800 bg-white outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100 transition-colors"
            value="{{ old('rfid', $emp?->rfid) }}" placeholder="Nomor kartu RFID">
        @error('rfid') <span class="text-xs text-red-500 mt-0.5">{{ $message }}</span> @enderror
    </div>
</div>

<div class="text-[11px] font-bold text-slate-400 uppercase tracking-widest mb-3.5 pb-2 border-b border-slate-100 mt-2">Organisasi</div>
<div class="grid grid-cols-2 gap-4 mb-4">
    <div class="flex flex-col gap-1.5">
        @if($organizations->count() === 1)
            @php $singleOrg = $organizations->first(); @endphp
            <label class="text-xs font-semibold text-slate-600">Organisasi</label>
            <div class="px-3 py-2.5 border border-slate-200 rounded-xl text-sm text-slate-500 bg-slate-50">{{ $singleOrg->name }}</div>
            <input type="hidden" name="organization_id" value="{{ $singleOrg->id }}">
        @else
            <label class="text-xs font-semibold text-slate-600">Organisasi <span class="text-red-500 ml-0.5">*</span></label>
            <select name="organization_id" class="w-full px-3 py-2.5 border border-slate-200 rounded-xl text-sm text-slate-800 bg-white outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100 transition-colors">
                <option value="">-- Pilih Organisasi --</option>
                @foreach($organizations as $org)
                    <option value="{{ $org->id }}" {{ old('organization_id', $emp?->organization_id) == $org->id ? 'selected' : '' }}>
                        {{ $org->name }}
                    </option>
                @endforeach
            </select>
        @endif
        @error('organization_id') <span class="text-xs text-red-500 mt-0.5">{{ $message }}</span> @enderror
    </div>
</div>

@if($emp)
<div class="text-[11px] font-bold text-slate-400 uppercase tracking-widest mb-3.5 pb-2 border-b border-slate-100 mt-2">Status</div>
<div class="flex items-center gap-3 px-3.5 py-2.5 border border-slate-200 rounded-xl w-fit">
    <label class="toggle relative w-[42px] h-[22px]">
        <input type="hidden" name="is_active" value="0">
        <input type="checkbox" name="is_active" value="1" {{ old('is_active', $emp?->is_active) ? 'checked' : '' }}
            class="opacity-0 w-0 h-0 absolute">
        <span class="toggle-slider absolute inset-0 bg-slate-200 rounded-full cursor-pointer transition-[.2s]"></span>
    </label>
    <span class="text-sm text-slate-700 font-medium">Karyawan Aktif</span>
</div>
@endif

<script>
function updateGender() {
    document.querySelectorAll('#genderGroup label').forEach(label => {
        const checked = label.querySelector('input').checked;
        label.className = label.className.replace(/border-orange-400|bg-orange-50|text-orange-700|font-semibold|border-slate-200|text-slate-700/g, '').trim();
        if (checked) {
            label.classList.add('border-orange-400', 'bg-orange-50', 'text-orange-700', 'font-semibold');
            label.classList.remove('border-slate-200', 'text-slate-700');
        } else {
            label.classList.add('border-slate-200', 'text-slate-700');
            label.classList.remove('border-orange-400', 'bg-orange-50', 'text-orange-700', 'font-semibold');
        }
    });
}
</script>
