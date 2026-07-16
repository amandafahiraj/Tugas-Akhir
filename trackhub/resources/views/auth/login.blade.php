<!DOCTYPE html>
<html class="light" lang="id">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <meta name="description" content="Login ke TrackHub - Platform Monitoring dan Tracking Kendaraan Realtime.">
    <title>TrackHub - Masuk</title>

    {{-- Tailwind CSS --}}
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>

    {{-- Google Fonts --}}
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;600;700;800&family=Inter:wght@400;500&family=Geist:wght@600&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>

    {{-- Tailwind Config --}}
    <script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "on-secondary": "#ffffff",
                        "error": "#ba1a1a",
                        "surface-dim": "#d8dadc",
                        "background": "#f7f9fb",
                        "on-error-container": "#93000a",
                        "on-primary-fixed": "#0e1648",
                        "surface-container-high": "#e6e8ea",
                        "inverse-on-surface": "#eff1f3",
                        "primary-fixed": "#dfe0ff",
                        "surface": "#f7f9fb",
                        "error-container": "#ffdad6",
                        "surface-tint": "#535b8f",
                        "surface-container-highest": "#e0e3e5",
                        "tertiary-fixed-dim": "#b0cadd",
                        "inverse-surface": "#2d3133",
                        "surface-bright": "#f7f9fb",
                        "on-secondary-fixed": "#001c38",
                        "primary-fixed-dim": "#bcc3fe",
                        "tertiary-container": "#021e2c",
                        "inverse-primary": "#bcc3fe",
                        "surface-container": "#eceef0",
                        "on-error": "#ffffff",
                        "on-secondary-fixed-variant": "#0f487d",
                        "on-tertiary": "#ffffff",
                        "tertiary-fixed": "#cce6fa",
                        "on-secondary-container": "#194e84",
                        "on-primary-fixed-variant": "#3b4376",
                        "secondary": "#306096",
                        "primary-container": "#0e1648",
                        "secondary-fixed-dim": "#a2c9ff",
                        "on-tertiary-fixed": "#021e2c",
                        "outline": "#767680",
                        "on-tertiary-container": "#6e8799",
                        "on-surface-variant": "#46464f",
                        "outline-variant": "#c7c5d0",
                        "primary": "#000000",
                        "on-background": "#191c1e",
                        "tertiary": "#000000",
                        "on-tertiary-fixed-variant": "#314a59",
                        "secondary-container": "#94c1fd",
                        "secondary-fixed": "#d3e4ff",
                        "surface-variant": "#e0e3e5",
                        "on-primary": "#ffffff",
                        "surface-container-lowest": "#ffffff",
                        "on-surface": "#191c1e",
                        "on-primary-container": "#7980b7",
                        "surface-container-low": "#f2f4f6"
                    },
                    borderRadius: {
                        "DEFAULT": "0.25rem",
                        "lg": "0.5rem",
                        "xl": "0.75rem",
                        "full": "9999px"
                    },
                    spacing: {
                        "container-max": "1280px",
                        "base": "8px",
                        "margin-mobile": "16px",
                        "gutter": "24px",
                        "margin-desktop": "48px"
                    },
                    fontFamily: {
                        "headline-xl": ["Manrope"],
                        "headline-lg": ["Manrope"],
                        "body-md": ["Inter"],
                        "label-caps": ["Geist"],
                        "headline-lg-mobile": ["Manrope"],
                        "body-sm": ["Inter"]
                    },
                    fontSize: {
                        "headline-xl": ["48px", {"lineHeight": "56px", "letterSpacing": "-0.02em", "fontWeight": "700"}],
                        "headline-lg": ["32px", {"lineHeight": "40px", "letterSpacing": "-0.01em", "fontWeight": "600"}],
                        "body-md": ["16px", {"lineHeight": "24px", "fontWeight": "400"}],
                        "label-caps": ["12px", {"lineHeight": "16px", "letterSpacing": "0.05em", "fontWeight": "600"}],
                        "headline-lg-mobile": ["24px", {"lineHeight": "32px", "fontWeight": "600"}],
                        "body-sm": ["14px", {"lineHeight": "20px", "fontWeight": "400"}]
                    }
                },
            },
        }
    </script>

    <style>
        body {
            min-height: max(884px, 100dvh);
        }

        .glass-panel {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(179, 205, 224, 0.4);
        }

        .starry-gradient {
            background: radial-gradient(circle at top right, #dfe0ff 0%, #f7f9fb 40%, #f7f9fb 100%);
        }

        .input-glow:focus {
            box-shadow: 0 0 0 3px rgba(148, 193, 253, 0.2);
        }

        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
            vertical-align: middle;
        }

        /* Password toggle button */
        #toggle-password {
            cursor: pointer;
            background: none;
            border: none;
            padding: 0;
            line-height: 1;
        }
    </style>
</head>

<body class="bg-background text-on-surface font-body-md starry-gradient min-h-screen flex items-center justify-center relative overflow-hidden">

    {{-- Atmospheric Background Blobs --}}
    <div class="absolute top-[-10%] right-[-10%] w-[40rem] h-[40rem] bg-primary-fixed/20 rounded-full blur-[120px] pointer-events-none"></div>
    <div class="absolute bottom-[-5%] left-[-5%] w-[30rem] h-[30rem] bg-secondary-fixed/15 rounded-full blur-[100px] pointer-events-none"></div>

    <main class="w-full max-w-md px-margin-mobile z-10">

        {{-- Brand Header --}}
        <div class="text-center mb-10">
            <h1 class="font-headline-xl text-headline-xl text-on-primary-fixed tracking-tight mb-2">TrackHub</h1>
            <p class="font-body-md text-on-surface-variant opacity-80">Monitoring &amp; Tracking Kendaraan Realtime</p>
        </div>

        {{-- Login Card --}}
        <div class="glass-panel p-8 md:p-10 rounded-xl shadow-sm">

            {{-- Error Alert --}}
            @if ($errors->any())
                <div class="flex items-start gap-3 mb-6 px-4 py-3 rounded-lg bg-error-container border border-error/20 text-on-error-container text-body-sm">
                    <span class="material-symbols-outlined text-[18px] mt-0.5 flex-shrink-0">error</span>
                    <span>{{ $errors->first() }}</span>
                </div>
            @endif

            <form action="{{ route('login') }}" method="POST" class="space-y-6">
                @csrf

                {{-- Email Field --}}
                <div>
                    <label class="block font-label-caps text-label-caps text-on-surface-variant mb-2" for="email">
                        ALAMAT EMAIL
                    </label>
                    <div class="relative">
                        <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-outline select-none pointer-events-none">mail</span>
                        <input
                            id="email"
                            name="email"
                            type="email"
                            value="{{ old('email') }}"
                            required
                            autofocus
                            autocomplete="email"
                            placeholder="nama@email.com"
                            class="w-full bg-surface-container-low border border-outline-variant rounded-lg py-3 pl-10 pr-4 text-on-surface placeholder:text-outline focus:outline-none focus:border-on-primary-fixed-variant input-glow transition-all @error('email') border-error @enderror"
                        />
                    </div>
                    @error('email')
                        <p class="mt-1.5 text-body-sm text-error">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Password Field --}}
                <div>
                    <div class="flex justify-between items-center mb-2">
                        <label class="block font-label-caps text-label-caps text-on-surface-variant" for="password">
                            KATA SANDI
                        </label>
                    </div>
                    <div class="relative">
                        <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-outline select-none pointer-events-none">lock</span>
                        <input
                            id="password"
                            name="password"
                            type="password"
                            required
                            autocomplete="current-password"
                            placeholder="••••••••"
                            class="w-full bg-surface-container-low border border-outline-variant rounded-lg py-3 pl-10 pr-12 text-on-surface placeholder:text-outline focus:outline-none focus:border-on-primary-fixed-variant input-glow transition-all @error('password') border-error @enderror"
                        />
                        <button
                            id="toggle-password"
                            type="button"
                            aria-label="Toggle password visibility"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-outline hover:text-on-surface transition-colors"
                        >
                            <span id="eye-icon" class="material-symbols-outlined">visibility</span>
                        </button>
                    </div>
                    @error('password')
                        <p class="mt-1.5 text-body-sm text-error">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Remember Me --}}
                <div class="flex items-center gap-2">
                    <input
                        id="remember"
                        name="remember"
                        type="checkbox"
                        class="w-4 h-4 rounded border-outline-variant bg-surface-container-low text-on-primary-fixed focus:ring-on-primary-fixed-variant transition"
                    />
                    <label for="remember" class="font-body-sm text-on-surface-variant cursor-pointer select-none">
                        Ingat saya
                    </label>
                </div>

                {{-- Submit Button --}}
                <button
                    type="submit"
                    class="w-full bg-primary-container text-on-primary font-headline-lg-mobile py-4 rounded-lg hover:scale-[1.02] active:scale-95 transition-all duration-200 flex items-center justify-center gap-2 shadow-lg"
                >
                    Masuk
                    <span class="material-symbols-outlined">arrow_forward</span>
                </button>

            </form>
        </div>

        {{-- Footer --}}
        <p class="text-center mt-8 font-body-sm text-on-surface-variant">
            Belum punya akun?
            <a href="{{ route('register') }}" class="text-on-secondary-fixed-variant font-semibold hover:underline underline-offset-4">
                Daftar sekarang
            </a>
        </p>

        {{-- Terms --}}
        <div class="mt-8 text-center">
            <p class="text-[11px] font-label-caps text-outline uppercase tracking-widest leading-relaxed">
                Dengan masuk, Anda menyetujui<br/>
                <a href="#" class="hover:text-on-surface transition-colors">Syarat Layanan</a>
                &amp;
                <a href="#" class="hover:text-on-surface transition-colors">Kebijakan Privasi</a>
                kami.
            </p>
        </div>

    </main>

    {{-- Interactive Floating Dots --}}
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const container = document.body;
            for (let i = 0; i < 50; i++) {
                const star = document.createElement('div');
                star.className = 'absolute rounded-full pointer-events-none';
                star.style.background = 'rgba(14, 22, 72, 0.08)';
                const size = Math.random() * 4 + 2;
                star.style.width = `${size}px`;
                star.style.height = `${size}px`;
                star.style.left = `${Math.random() * 100}%`;
                star.style.top = `${Math.random() * 100}%`;
                star.style.opacity = Math.random() * 0.6 + 0.1;

                star.animate([
                    { transform: 'translate(0, 0)' },
                    { transform: `translate(${Math.random() * 40 - 20}px, ${Math.random() * 40 - 20}px)` }
                ], {
                    duration: 5000 + Math.random() * 5000,
                    iterations: Infinity,
                    direction: 'alternate',
                    easing: 'ease-in-out'
                });

                container.appendChild(star);
            }
        });

        // Toggle password visibility
        const toggleBtn = document.getElementById('toggle-password');
        const passwordInput = document.getElementById('password');
        const eyeIcon = document.getElementById('eye-icon');

        if (toggleBtn) {
            toggleBtn.addEventListener('click', () => {
                const isHidden = passwordInput.type === 'password';
                passwordInput.type = isHidden ? 'text' : 'password';
                eyeIcon.textContent = isHidden ? 'visibility_off' : 'visibility';
            });
        }
    </script>

</body>
</html>
