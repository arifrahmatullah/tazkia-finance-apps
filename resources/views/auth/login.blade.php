<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login — Tazkia Finance</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans m-0 min-h-screen flex bg-slate-100">

    {{-- KIRI: Branding Panel --}}
    <div class="hidden lg:flex flex-col justify-between p-12 relative overflow-hidden" style="width: 55%; background: linear-gradient(145deg, #040f2e 0%, #0d2d6b 45%, #1a4fad 100%);">

        {{-- Grid pattern overlay --}}
        <div class="absolute inset-0" style="
            background-image: linear-gradient(rgba(255,255,255,0.04) 1px, transparent 1px),
                              linear-gradient(90deg, rgba(255,255,255,0.04) 1px, transparent 1px);
            background-size: 44px 44px;
        "></div>

        {{-- Glow blobs --}}
        <div class="absolute rounded-full" style="width:500px; height:500px; top:-150px; right:-150px; background: radial-gradient(circle, rgba(249,115,22,0.18) 0%, transparent 65%);"></div>
        <div class="absolute rounded-full" style="width:350px; height:350px; bottom:-100px; left:-100px; background: radial-gradient(circle, rgba(59,130,246,0.2) 0%, transparent 65%);"></div>
        <div class="absolute rounded-full" style="width:220px; height:220px; bottom:28%; right:8%; background: radial-gradient(circle, rgba(249,115,22,0.12) 0%, transparent 65%);"></div>

        {{-- Logo --}}
        <div class="relative z-10">
            <div class="flex items-center gap-3">
                <div class="w-11 h-11 rounded-xl flex items-center justify-center" style="background: linear-gradient(135deg, #ea580c, #f97316); box-shadow: 0 4px 14px rgba(234,88,12,0.4);">
                    <svg width="22" height="22" fill="none" stroke="white" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 11h.01M12 11h.01M15 11h.01M4 19h16a2 2 0 002-2V7a2 2 0 00-2-2H4a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                </div>
                <div>
                    <div class="text-white font-bold text-lg tracking-wide">Tazkia Finance</div>
                    <div class="text-blue-300 text-[0.7rem] font-medium tracking-widest uppercase">Management System</div>
                </div>
            </div>
        </div>

        {{-- Headline --}}
        <div class="relative z-10 flex-1 flex flex-col justify-center py-10">

            {{-- Badge --}}
            <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full w-fit mb-6" style="background: rgba(234,88,12,0.18); border: 1px solid rgba(249,115,22,0.4);">
                <span class="w-[7px] h-[7px] rounded-full bg-orange-400 inline-block" style="animation: pulse 2s infinite;"></span>
                <span class="text-orange-300 text-[0.72rem] font-semibold tracking-[0.04em]">Sistem Terintegrasi</span>
            </div>

            {{-- Title --}}
            <h1 class="text-white text-[2.3rem] font-bold leading-tight m-0 mb-5">
                Kelola Keuangan &amp;<br>
                <span class="text-orange-400">Akuntansi</span> dengan<br>
                Mudah &amp; Efisien
            </h1>

            <p class="text-blue-200 text-[0.92rem] leading-[1.7] m-0 mb-10 max-w-[360px]">
                Platform terpadu untuk <span class="text-blue-300 font-medium">Yayasan Tazkia</span>,
                <span class="text-blue-300 font-medium">Kampus Tazkia</span>, dan
                <span class="text-blue-300 font-medium">STMIK Tazkia</span>
                dalam satu sistem yang terintegrasi.
            </p>

        </div>

        {{-- Feature list --}}
        <div class="relative z-10">
            <div class="grid grid-cols-2 gap-3">
                @foreach([
                    ['M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z', 'Budgeting & Anggaran'],
                    ['M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z', 'Pengajuan Dana'],
                    ['M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z', 'Laporan Keuangan'],
                    ['M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 11h.01M12 11h.01M15 11h.01M4 19h16a2 2 0 002-2V7a2 2 0 00-2-2H4a2 2 0 00-2 2v10a2 2 0 002 2z', 'Jurnal Akuntansi'],
                ] as [$path, $label])
                <div class="flex items-center gap-2.5">
                    <div class="w-[30px] h-[30px] rounded-lg flex-shrink-0 flex items-center justify-center" style="background: rgba(234,88,12,0.18); border: 1px solid rgba(249,115,22,0.25);">
                        <svg width="15" height="15" fill="none" stroke="#fb923c" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $path }}"/>
                        </svg>
                    </div>
                    <span class="text-blue-200 text-[0.78rem]">{{ $label }}</span>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- KANAN: Login Form --}}
    <div class="flex-1 flex items-center justify-center p-10">
        <div class="w-full max-w-[360px]">

            {{-- Mobile Logo --}}
            <div class="lg:hidden flex items-center gap-2.5 mb-8">
                <div class="w-[38px] h-[38px] rounded-[10px] flex items-center justify-center" style="background: linear-gradient(135deg, #0d2d6b, #1a4fad);">
                    <svg width="18" height="18" fill="none" stroke="white" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 11h.01M12 11h.01M15 11h.01M4 19h16a2 2 0 002-2V7a2 2 0 00-2-2H4a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                </div>
                <span class="font-bold text-slate-800 text-base">Tazkia Finance</span>
            </div>

            {{-- Header --}}
            <div class="mb-8">
                <h2 class="text-[1.6rem] font-bold text-slate-900 mt-0 mb-1.5">Selamat Datang 👋</h2>
                <p class="text-slate-500 text-sm m-0">Masuk ke akun Anda untuk melanjutkan</p>
            </div>

            {{-- Error --}}
            @if ($errors->any())
            <div class="flex items-start gap-2.5 px-3.5 py-3 rounded-xl bg-red-50 border border-red-200 mb-5">
                <svg width="16" height="16" fill="#ef4444" viewBox="0 0 20 20" class="flex-shrink-0 mt-px">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                </svg>
                <p class="text-red-600 text-[0.85rem] m-0">{{ $errors->first('email') }}</p>
            </div>
            @endif

            {{-- Form --}}
            <form method="POST" action="{{ route('login.post') }}" class="flex flex-col gap-[1.1rem]">
                @csrf

                {{-- Email --}}
                <div>
                    <label for="email" class="block text-[0.83rem] font-semibold text-gray-700 mb-1.5">
                        Email
                    </label>
                    <div class="relative">
                        <div class="absolute top-1/2 left-3 -translate-y-1/2 pointer-events-none">
                            <svg width="16" height="16" fill="none" stroke="#9ca3af" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <input
                            id="email" type="email" name="email"
                            value="{{ old('email') }}"
                            required autofocus autocomplete="email"
                            placeholder="nama@tazkia.ac.id"
                            class="w-full pl-[38px] pr-3.5 py-[11px] border border-slate-200 rounded-[10px] text-sm text-gray-900 bg-white outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100 transition-colors box-border"
                        >
                    </div>
                </div>

                {{-- Password --}}
                <div>
                    <div class="flex justify-between items-center mb-1.5">
                        <label for="password" class="text-[0.83rem] font-semibold text-gray-700">Password</label>
                        <a href="#" class="text-[0.78rem] font-medium text-blue-700 no-underline">Lupa password?</a>
                    </div>
                    <div class="relative">
                        <div class="absolute top-1/2 left-3 -translate-y-1/2 pointer-events-none">
                            <svg width="16" height="16" fill="none" stroke="#9ca3af" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                        </div>
                        <input
                            id="password" type="password" name="password"
                            required autocomplete="current-password"
                            placeholder="••••••••"
                            class="w-full pl-[38px] pr-[42px] py-[11px] border border-slate-200 rounded-[10px] text-sm text-gray-900 bg-white outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100 transition-colors box-border"
                        >
                        <button type="button" onclick="togglePassword()" class="absolute top-1/2 right-3 -translate-y-1/2 bg-transparent border-0 cursor-pointer p-0 text-gray-400">
                            <svg id="eye-icon" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                        </button>
                    </div>
                </div>

                {{-- Remember --}}
                <div class="flex items-center gap-2">
                    <input id="remember" type="checkbox" name="remember" class="w-[15px] h-[15px] cursor-pointer accent-blue-700">
                    <label for="remember" class="text-[0.83rem] text-gray-600 cursor-pointer select-none">
                        Ingat saya selama 30 hari
                    </label>
                </div>

                {{-- Submit --}}
                <button type="submit"
                    class="w-full py-3 text-white font-semibold text-[0.9rem] border-0 rounded-[10px] cursor-pointer tracking-[0.02em] transition-all hover:-translate-y-px hover:shadow-lg"
                    style="background: linear-gradient(135deg, #ea580c 0%, #f97316 100%); box-shadow: 0 4px 14px rgba(234,88,12,0.3);"
                >
                    Masuk ke Sistem
                </button>
            </form>

            {{-- Footer --}}
            <div class="mt-8 pt-6 border-t border-slate-100 text-center">
                <p class="text-[0.72rem] text-slate-400 m-0 mb-0.5">&copy; {{ date('Y') }} Tazkia Finance Management System</p>
                <p class="text-[0.72rem] text-slate-400 m-0">Yayasan Tazkia · Kampus Tazkia · STMIK Tazkia</p>
            </div>
        </div>
    </div>

    <style>
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.4; }
        }
    </style>

    <script>
        function togglePassword() {
            const input = document.getElementById('password');
            const icon = document.getElementById('eye-icon');
            if (input.type === 'password') {
                input.type = 'text';
                icon.innerHTML = `
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                `;
            } else {
                input.type = 'password';
                icon.innerHTML = `
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                `;
            }
        }
    </script>
</body>
</html>
