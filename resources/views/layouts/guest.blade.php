<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'HRIS') }} — Masuk</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap"
        rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        .auth-shell {
            min-height: 100vh;
        }

        /* Panel kiri — brand */
        .auth-brand-panel {
            background: linear-gradient(160deg, var(--c-sb-from) 0%, var(--c-sb-to) 100%);
            position: relative;
            overflow: hidden;
        }

        .auth-brand-glow {
            position: absolute;
            width: 32rem;
            height: 32rem;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(16, 185, 129, 0.22), transparent 70%);
            top: -8rem;
            right: -10rem;
            pointer-events: none;
        }

        .auth-brand-glow.two {
            width: 24rem;
            height: 24rem;
            top: auto;
            bottom: -8rem;
            left: -8rem;
            right: auto;
            background: radial-gradient(circle, rgba(52, 211, 153, 0.15), transparent 70%);
        }

        /* Ilustrasi mengambang */
        @media (prefers-reduced-motion: no-preference) {
            .auth-illustration {
                animation: authFloat 7s ease-in-out infinite;
            }

            @keyframes authFloat {

                0%,
                100% {
                    transform: translateY(0);
                }

                50% {
                    transform: translateY(-10px);
                }
            }
        }

        .auth-form-panel {
            background: #FAFBFA;
        }

        @media (max-width: 1023px) {
            .auth-brand-panel-mobile {
                background: linear-gradient(135deg, var(--c-sb-from) 0%, var(--c-sb-to) 100%);
            }
        }
    </style>
</head>

<body class="font-sans text-gray-900 antialiased h-full">

    <div class="auth-shell grid lg:grid-cols-2">

        {{-- ============================================================
             PANEL KIRI — Brand & Ilustrasi (desktop only, full)
             Di mobile: jadi header ringkas di atas form
        ============================================================ --}}
        <div class="auth-brand-panel hidden lg:flex lg:flex-col lg:justify-between p-10 xl:p-14">
            <div class="auth-brand-glow"></div>
            <div class="auth-brand-glow two"></div>

            {{-- Logo + nama --}}
            <a href="/" class="relative z-10 flex items-center gap-3">
                <div class="w-11 h-11 rounded-xl overflow-hidden shrink-0 ring-2 ring-white/15">
                    <img src="{{ asset('images/logo-fatahillah.jpg') }}" alt="Logo Yayasan Fatahillah"
                        class="w-full h-full object-cover">
                </div>
                <div class="leading-tight">
                    <div class="text-white font-bold text-base">HRIS Fatahillah</div>
                    <div class="text-sb-muted text-xs">Yayasan Pendidikan</div>
                </div>
            </a>

            {{-- Ilustrasi tengah --}}
            <div class="relative z-10 flex-1 flex items-center justify-center py-12">
                <div class="auth-illustration w-full max-w-sm">
                    @include('partials.auth-illustration')
                </div>
            </div>

            {{-- Tagline bawah --}}
            <div class="relative z-10">
                <p class="text-white text-xl font-semibold leading-snug max-w-sm">
                    Satu sistem, untuk seluruh unit sekolah Yayasan Fatahillah.
                </p>
                <p class="text-sb-muted text-sm mt-2 max-w-sm">
                    Kelola kepegawaian, absensi, dan cuti dalam satu tempat.
                </p>
            </div>
        </div>

        {{-- ============================================================
             PANEL KANAN — Form (full width di mobile)
        ============================================================ --}}
        <div class="auth-form-panel flex flex-col">

            {{-- Header mobile — brand ringkas, hanya tampil di bawah lg --}}
            <div class="auth-brand-panel-mobile lg:hidden px-6 pt-8 pb-10 flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl overflow-hidden shrink-0 ring-2 ring-white/15">
                    <img src="{{ asset('images/logo-fatahillah.jpg') }}" alt="Logo Yayasan Fatahillah"
                        class="w-full h-full object-cover">
                </div>
                <div class="leading-tight">
                    <div class="text-white font-bold text-sm">HRIS Fatahillah</div>
                    <div class="text-sb-muted text-xs">Yayasan Pendidikan</div>
                </div>
            </div>

            {{-- Konten form, terpusat vertikal --}}
            <div class="flex-1 flex items-center justify-center px-6 sm:px-10 py-10">
                <div class="w-full max-w-sm">
                    {{ $slot }}
                </div>
            </div>

            {{-- Footer kecil --}}
            <div class="px-6 pb-6 text-center">
                <p class="text-xs text-gray-400">
                    &copy; {{ now()->year }} Yayasan Pendidikan Fatahillah
                </p>
            </div>
        </div>
    </div>

</body>

</html>
