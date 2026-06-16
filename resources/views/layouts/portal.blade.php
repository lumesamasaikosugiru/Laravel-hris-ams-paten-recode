<!DOCTYPE html>
<html lang="id" class="h-full">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>@yield('title', 'Portal') — HRIS Fatahillah</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
    @livewireStyles
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        .portal-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        }

        .portal-btn-primary {
            background: linear-gradient(135deg, #1a1040, #7c3aed);
            color: white;
            border-radius: 12px;
            padding: 14px 24px;
            font-weight: 600;
            font-size: 15px;
            width: 100%;
            border: none;
            cursor: pointer;
            transition: opacity 0.2s;
        }

        .portal-btn-primary:hover {
            opacity: 0.9;
        }

        .portal-btn-primary:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .portal-btn-danger {
            background: linear-gradient(135deg, #dc2626, #b91c1c);
            color: white;
            border-radius: 12px;
            padding: 14px 24px;
            font-weight: 600;
            font-size: 15px;
            width: 100%;
            border: none;
            cursor: pointer;
            transition: opacity 0.2s;
        }

        .portal-btn-ghost {
            background: #f3f4f6;
            color: #374151;
            border-radius: 12px;
            padding: 12px 24px;
            font-weight: 500;
            font-size: 14px;
            border: none;
            cursor: pointer;
        }

        .bottom-nav {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: white;
            border-top: 1px solid #e5e7eb;
            z-index: 50;
            padding: 8px 0 max(8px, env(safe-area-inset-bottom));
        }

        .bottom-nav a {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 3px;
            font-size: 11px;
            font-weight: 500;
            color: #9ca3af;
            text-decoration: none;
            flex: 1;
            padding: 4px 0;
            transition: color 0.2s;
        }

        .bottom-nav a.active {
            color: #7c3aed;
        }

        .bottom-nav a svg {
            width: 22px;
            height: 22px;
        }

        .page-content {
            padding-bottom: 80px;
        }

        .status-chip {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
    </style>
</head>

<body class="bg-gray-50 h-full">

    {{-- Top Bar --}}
    <div class="bg-white border-b border-gray-100 px-4 py-3 flex items-center justify-between">
        <div class="flex items-center gap-3">
            <img src="{{ asset('images/logo-fatahillah.jpg') }}" alt="Logo" class="w-8 h-8 rounded-lg object-cover">
            <div>
                <p class="text-xs font-bold text-gray-800 leading-tight">HRIS Fatahillah</p>
                <p class="text-[10px] text-gray-400 leading-tight">{{ auth()->user()->name }}</p>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <span class="text-xs text-gray-400">{{ now()->translatedFormat('d M Y') }}</span>
            <form method="POST" action="{{ route('logout') }}" class="inline">
                @csrf
                <button type="submit" class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center">
                    <svg class="w-4 h-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9" />
                    </svg>
                </button>
            </form>
        </div>
    </div>

    {{-- Flash Messages --}}
    @if (session('success'))
        <div class="mx-4 mt-3 p-3 bg-green-50 border border-green-200 rounded-xl text-sm text-green-700 font-medium">
            ✓ {{ session('success') }}
        </div>
    @endif
    @if (session('error'))
        <div class="mx-4 mt-3 p-3 bg-red-50 border border-red-200 rounded-xl text-sm text-red-600 font-medium">
            ✕ {{ session('error') }}
        </div>
    @endif

    {{-- Content --}}
    <main class="page-content max-w-lg mx-auto">
        @yield('content')
    </main>

    {{-- Bottom Navigation --}}
    <nav class="bottom-nav">
        <div class="flex items-center justify-around max-w-lg mx-auto">
            <a href="{{ route('portal.home') }}" class="{{ request()->routeIs('portal.home') ? 'active' : '' }}">
                <svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                </svg>
                Beranda
            </a>
            <a href="{{ route('portal.attendance') }}"
                class="{{ request()->routeIs('portal.attendance') ? 'active' : '' }}">
                <svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
                Absensi
            </a>
            <a href="{{ route('portal.leave') }}" class="{{ request()->routeIs('portal.leave*') ? 'active' : '' }}">
                <svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                </svg>
                Cuti
            </a>
            <a href="{{ route('portal.profile') }}"
                class="{{ request()->routeIs('portal.profile') ? 'active' : '' }}">
                <svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                </svg>
                Profil
            </a>
        </div>
    </nav>

    @livewireScripts
</body>

</html>
