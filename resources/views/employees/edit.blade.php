<x-layouts.app title="Edit Karyawan">

    <a href="{{ route('employees.show', $employee) }}" class="inline-flex items-center gap-1.5 text-sm text-slate-500 hover:text-orange-500 mb-5 no-underline">
        <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M19 12H5M12 5l-7 7 7 7"/></svg>
        Kembali ke Detail Karyawan
    </a>

    <h1 class="text-xl font-bold text-slate-900 m-0 mb-0.5">Edit Karyawan</h1>
    <p class="text-sm text-slate-400 mb-5">NIK: {{ $employee->nik }}</p>

    <div class="bg-white rounded-xl shadow-sm p-6">
        <form method="POST" action="{{ route('employees.update', $employee) }}">
            @csrf @method('PUT')
            @include('employees._form')
            <div class="flex gap-3 justify-end mt-6 pt-5 border-t border-slate-100">
                <a href="{{ route('employees.show', $employee) }}" class="px-5 py-2.5 rounded-xl border border-slate-200 bg-white text-slate-600 text-sm font-medium no-underline inline-flex items-center">Batal</a>
                <button type="submit" class="px-6 py-2.5 rounded-xl text-sm font-semibold bg-gradient-to-br from-orange-400 to-orange-500 text-white border-0 cursor-pointer hover:-translate-y-px transition-all">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</x-layouts.app>
