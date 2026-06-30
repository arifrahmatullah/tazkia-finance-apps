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
<body style="font-family: 'Inter', sans-serif; margin: 0; min-height: 100vh; display: flex; background: #f1f5f9;">

    {{-- KIRI: Branding Panel --}}
    <div class="hidden lg:flex" style="
        width: 55%;
        background: linear-gradient(145deg, #040f2e 0%, #0d2d6b 45%, #1a4fad 100%);
        flex-direction: column;
        justify-content: space-between;
        padding: 3rem;
        position: relative;
        overflow: hidden;
    ">
        {{-- Grid pattern overlay --}}
        <div style="
            position: absolute; inset: 0;
            background-image: linear-gradient(rgba(255,255,255,0.04) 1px, transparent 1px),
                              linear-gradient(90deg, rgba(255,255,255,0.04) 1px, transparent 1px);
            background-size: 44px 44px;
        "></div>

        {{-- Glow blobs --}}
        <div style="position:absolute; width:500px; height:500px; border-radius:50%; top:-150px; right:-150px;
            background: radial-gradient(circle, rgba(249,115,22,0.18) 0%, transparent 65%);"></div>
        <div style="position:absolute; width:350px; height:350px; border-radius:50%; bottom:-100px; left:-100px;
            background: radial-gradient(circle, rgba(59,130,246,0.2) 0%, transparent 65%);"></div>
        <div style="position:absolute; width:220px; height:220px; border-radius:50%; bottom:28%; right:8%;
            background: radial-gradient(circle, rgba(249,115,22,0.12) 0%, transparent 65%);"></div>

        {{-- Logo --}}
        <div style="position: relative; z-index: 10;">
            <div style="display:flex; align-items:center; gap:12px;">
                <div style="
                    width:44px; height:44px; border-radius:12px;
                    background: linear-gradient(135deg, #ea580c, #f97316);
                    display:flex; align-items:center; justify-content:center;
                    box-shadow: 0 4px 14px rgba(234,88,12,0.4);
                ">
                    <svg width="22" height="22" fill="none" stroke="white" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 11h.01M12 11h.01M15 11h.01M4 19h16a2 2 0 002-2V7a2 2 0 00-2-2H4a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                </div>
                <div>
                    <div style="color:#ffffff; font-weight:700; font-size:1.1rem; letter-spacing:0.02em;">Tazkia Finance</div>
                    <div style="color:#93c5fd; font-size:0.7rem; font-weight:500; letter-spacing:0.1em; text-transform:uppercase;">Management System</div>
                </div>
            </div>
        </div>

        {{-- Headline --}}
        <div style="position: relative; z-index: 10; flex:1; display:flex; flex-direction:column; justify-content:center; padding: 2.5rem 0;">

            {{-- Badge --}}
            <div style="
                display:inline-flex; align-items:center; gap:8px;
                padding: 6px 14px; border-radius:999px; width:fit-content; margin-bottom:1.5rem;
                background: rgba(234,88,12,0.18);
                border: 1px solid rgba(249,115,22,0.4);
            ">
                <span style="width:7px; height:7px; border-radius:50%; background:#f97316; display:inline-block; animation: pulse 2s infinite;"></span>
                <span style="color:#fdba74; font-size:0.72rem; font-weight:600; letter-spacing:0.04em;">Sistem Terintegrasi</span>
            </div>

            {{-- Title --}}
            <h1 style="color:#ffffff; font-size:2.3rem; font-weight:700; line-height:1.25; margin:0 0 1.2rem 0;">
                Kelola Keuangan &amp;<br>
                <span style="color:#f97316;">Akuntansi</span> dengan<br>
                Mudah &amp; Efisien
            </h1>

            <p style="color:#bfdbfe; font-size:0.92rem; line-height:1.7; margin:0 0 2.5rem 0; max-width:360px;">
                Platform terpadu untuk <span style="color:#93c5fd; font-weight:500;">Yayasan Tazkia</span>,
                <span style="color:#93c5fd; font-weight:500;">Kampus Tazkia</span>, dan
                <span style="color:#93c5fd; font-weight:500;">STMIK Tazkia</span>
                dalam satu sistem yang terintegrasi.
            </p>

        </div>

        {{-- Feature list --}}
        <div style="position: relative; z-index: 10;">
            <div style="display:grid; grid-template-columns: 1fr 1fr; gap:12px;">
                @foreach([
                    ['M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z', 'Budgeting & Anggaran'],
                    ['M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z', 'Pengajuan Dana'],
                    ['M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z', 'Laporan Keuangan'],
                    ['M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 11h.01M12 11h.01M15 11h.01M4 19h16a2 2 0 002-2V7a2 2 0 00-2-2H4a2 2 0 00-2 2v10a2 2 0 002 2z', 'Jurnal Akuntansi'],
                ] as [$path, $label])
                <div style="display:flex; align-items:center; gap:10px;">
                    <div style="
                        width:30px; height:30px; border-radius:8px; flex-shrink:0;
                        background: rgba(234,88,12,0.18);
                        border: 1px solid rgba(249,115,22,0.25);
                        display:flex; align-items:center; justify-content:center;
                    ">
                        <svg width="15" height="15" fill="none" stroke="#fb923c" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $path }}"/>
                        </svg>
                    </div>
                    <span style="color:#bfdbfe; font-size:0.78rem;">{{ $label }}</span>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- KANAN: Login Form --}}
    <div style="flex:1; display:flex; align-items:center; justify-content:center; padding:2.5rem;">
        <div style="width:100%; max-width:360px;">

            {{-- Mobile Logo --}}
            <div class="lg:hidden" style="display:flex; align-items:center; gap:10px; margin-bottom:2rem;">
                <div style="width:38px; height:38px; border-radius:10px; background:linear-gradient(135deg,#0d2d6b,#1a4fad); display:flex; align-items:center; justify-content:center;">
                    <svg width="18" height="18" fill="none" stroke="white" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 11h.01M12 11h.01M15 11h.01M4 19h16a2 2 0 002-2V7a2 2 0 00-2-2H4a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                </div>
                <span style="font-weight:700; color:#1e293b; font-size:1rem;">Tazkia Finance</span>
            </div>

            {{-- Header --}}
            <div style="margin-bottom:2rem;">
                <h2 style="font-size:1.6rem; font-weight:700; color:#0f172a; margin:0 0 6px 0;">Selamat Datang 👋</h2>
                <p style="color:#64748b; font-size:0.875rem; margin:0;">Masuk ke akun Anda untuk melanjutkan</p>
            </div>

            {{-- Error --}}
            @if ($errors->any())
            <div style="display:flex; align-items:flex-start; gap:10px; padding:12px 14px; border-radius:12px; background:#fef2f2; border:1px solid #fecaca; margin-bottom:1.25rem;">
                <svg width="16" height="16" fill="#ef4444" viewBox="0 0 20 20" style="flex-shrink:0; margin-top:1px;">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                </svg>
                <p style="color:#dc2626; font-size:0.85rem; margin:0;">{{ $errors->first('email') }}</p>
            </div>
            @endif

            {{-- Form --}}
            <form method="POST" action="{{ route('login.post') }}" style="display:flex; flex-direction:column; gap:1.1rem;">
                @csrf

                {{-- Email --}}
                <div>
                    <label for="email" style="display:block; font-size:0.83rem; font-weight:600; color:#374151; margin-bottom:6px;">
                        Email
                    </label>
                    <div style="position:relative;">
                        <div style="position:absolute; top:50%; left:12px; transform:translateY(-50%); pointer-events:none;">
                            <svg width="16" height="16" fill="none" stroke="#9ca3af" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <input
                            id="email" type="email" name="email"
                            value="{{ old('email') }}"
                            required autofocus autocomplete="email"
                            placeholder="nama@tazkia.ac.id"
                            style="
                                width:100%; padding:11px 14px 11px 38px;
                                font-size:0.875rem; color:#111827;
                                background:#ffffff; border:1.5px solid #e2e8f0;
                                border-radius:10px; outline:none; box-sizing:border-box;
                                transition: border-color 0.2s, box-shadow 0.2s;
                                font-family: 'Inter', sans-serif;
                            "
                            onfocus="this.style.borderColor='#1a4fad'; this.style.boxShadow='0 0 0 3px rgba(26,79,173,0.12)';"
                            onblur="this.style.borderColor='#e2e8f0'; this.style.boxShadow='none';"
                        >
                    </div>
                </div>

                {{-- Password --}}
                <div>
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:6px;">
                        <label for="password" style="font-size:0.83rem; font-weight:600; color:#374151;">Password</label>
                        <a href="#" style="font-size:0.78rem; font-weight:500; color:#1a4fad; text-decoration:none;">Lupa password?</a>
                    </div>
                    <div style="position:relative;">
                        <div style="position:absolute; top:50%; left:12px; transform:translateY(-50%); pointer-events:none;">
                            <svg width="16" height="16" fill="none" stroke="#9ca3af" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                        </div>
                        <input
                            id="password" type="password" name="password"
                            required autocomplete="current-password"
                            placeholder="••••••••"
                            style="
                                width:100%; padding:11px 42px 11px 38px;
                                font-size:0.875rem; color:#111827;
                                background:#ffffff; border:1.5px solid #e2e8f0;
                                border-radius:10px; outline:none; box-sizing:border-box;
                                transition: border-color 0.2s, box-shadow 0.2s;
                                font-family: 'Inter', sans-serif;
                            "
                            onfocus="this.style.borderColor='#1a4fad'; this.style.boxShadow='0 0 0 3px rgba(26,79,173,0.12)';"
                            onblur="this.style.borderColor='#e2e8f0'; this.style.boxShadow='none';"
                        >
                        <button type="button" onclick="togglePassword()" style="
                            position:absolute; top:50%; right:12px; transform:translateY(-50%);
                            background:none; border:none; cursor:pointer; padding:0; color:#9ca3af;
                        ">
                            <svg id="eye-icon" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                        </button>
                    </div>
                </div>

                {{-- Remember --}}
                <div style="display:flex; align-items:center; gap:8px;">
                    <input id="remember" type="checkbox" name="remember" style="width:15px; height:15px; accent-color:#1a4fad; cursor:pointer;">
                    <label for="remember" style="font-size:0.83rem; color:#4b5563; cursor:pointer; user-select:none;">
                        Ingat saya selama 30 hari
                    </label>
                </div>

                {{-- Submit --}}
                <button type="submit" style="
                    width:100%; padding:12px;
                    background: linear-gradient(135deg, #ea580c 0%, #f97316 100%);
                    color:#ffffff; font-weight:600; font-size:0.9rem;
                    border:none; border-radius:10px; cursor:pointer;
                    letter-spacing:0.02em;
                    box-shadow: 0 4px 14px rgba(234,88,12,0.3);
                    transition: all 0.2s;
                    font-family: 'Inter', sans-serif;
                "
                onmouseover="this.style.background='linear-gradient(135deg,#c2410c,#ea580c)'; this.style.transform='translateY(-1px)'; this.style.boxShadow='0 8px 20px rgba(234,88,12,0.4)';"
                onmouseout="this.style.background='linear-gradient(135deg,#ea580c,#f97316)'; this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 14px rgba(234,88,12,0.3)';"
                >
                    Masuk ke Sistem
                </button>
            </form>

            {{-- Footer --}}
            <div style="margin-top:2rem; padding-top:1.5rem; border-top:1px solid #f1f5f9; text-align:center;">
                <p style="font-size:0.72rem; color:#94a3b8; margin:0 0 3px 0;">&copy; {{ date('Y') }} Tazkia Finance Management System</p>
                <p style="font-size:0.72rem; color:#94a3b8; margin:0;">Yayasan Tazkia · Kampus Tazkia · STMIK Tazkia</p>
            </div>
        </div>
    </div>

    <style>
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.4; }
        }
        @media (max-width: 1024px) {
            .hidden { display: none !important; }
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
