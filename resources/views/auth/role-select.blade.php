<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pilih Role — Tazkia Finance</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans m-0 min-h-screen flex items-center justify-center bg-slate-100" style="font-family:'Inter',sans-serif;">

    <div class="w-full max-w-[420px] p-6">
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-7">

            {{-- Header --}}
            <div class="flex items-center gap-2.5 mb-6">
                <div class="w-10 h-10 rounded-[10px] flex items-center justify-center flex-shrink-0" style="background: linear-gradient(135deg, #ea580c, #f97316);">
                    <svg width="19" height="19" fill="none" stroke="white" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 11h.01M12 11h.01M15 11h.01M4 19h16a2 2 0 002-2V7a2 2 0 00-2-2H4a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                </div>
                <div>
                    <div class="font-bold text-slate-900 text-base leading-tight">Tazkia Finance</div>
                    <div class="text-slate-400 text-[0.68rem] tracking-widest uppercase font-medium">Management System</div>
                </div>
            </div>

            <h2 class="text-[1.25rem] font-bold text-slate-900 m-0 mb-1.5">Pilih Role</h2>
            <p class="text-slate-500 text-sm m-0 mb-6">
                Akun Anda ({{ auth()->user()->name }}) memiliki lebih dari satu role. Pilih role yang ingin digunakan.
            </p>

            @if ($errors->any())
            <div class="flex items-start gap-2.5 px-3.5 py-3 rounded-xl bg-red-50 border border-red-200 mb-5">
                <p class="text-red-600 text-[0.85rem] m-0">{{ $errors->first() }}</p>
            </div>
            @endif

            <form method="POST" action="{{ route('role-select.select') }}" class="flex flex-col gap-2.5">
                @csrf
                @foreach($roles as $role)
                <label class="relative flex items-center gap-3 px-4 py-3.5 border border-slate-200 rounded-xl cursor-pointer transition-all hover:border-orange-400 hover:bg-orange-50/50 has-[:checked]:border-orange-400 has-[:checked]:bg-orange-50">
                    <input type="radio" name="role_id" value="{{ $role->id }}" class="sr-only" {{ $loop->first ? 'checked' : '' }} required>
                    <span class="w-2.5 h-2.5 rounded-full shrink-0" style="background:{{ $role->color ?? '#64748b' }};"></span>
                    <span class="flex-1">
                        <span class="block text-sm font-semibold text-slate-800">{{ $role->name }}</span>
                        @if($role->description)
                            <span class="block text-xs text-slate-400 mt-0.5 leading-snug">{{ $role->description }}</span>
                        @endif
                    </span>
                </label>
                @endforeach

                <button type="submit"
                    class="w-full mt-3 py-3 text-white font-semibold text-[0.9rem] border-0 rounded-[10px] cursor-pointer tracking-[0.02em] transition-all hover:-translate-y-px hover:shadow-lg"
                    style="background: linear-gradient(135deg, #ea580c 0%, #f97316 100%); box-shadow: 0 4px 14px rgba(234,88,12,0.3);">
                    Lanjutkan
                </button>
            </form>

            <form method="POST" action="{{ route('logout') }}" class="mt-4 text-center">
                @csrf
                <button type="submit" class="bg-transparent border-0 cursor-pointer text-[0.8rem] text-slate-400 hover:text-red-500 transition-colors">
                    Bukan Anda? Keluar
                </button>
            </form>
        </div>
    </div>
</body>
</html>
