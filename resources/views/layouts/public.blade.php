<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Karir') — Yayasan Fatahillah</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
    @livewireStyles
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-gray-50 min-h-screen flex flex-col">

    <nav class="bg-sidebar shadow-md sticky top-0 z-40">
        <div class="max-w-5xl mx-auto px-4 py-3.5 flex items-center justify-between">
            <a href="{{ route('welcome') }}" class="flex items-center gap-2.5">
                <div class="w-9 h-9 rounded-lg overflow-hidden shrink-0">
                    <img src="{{ asset('images/logo-fatahillah.jpg') }}" alt="Logo Yayasan Fatahillah"
                        class="w-full h-full object-cover">
                </div>
                <div>
                    <span class="text-sm font-bold text-white leading-none">Yayasan Fatahillah</span>
                    <span class="text-xs text-purple-300 ml-1.5">Karir</span>
                </div>
            </a>
            @auth
                <a href="{{ route('dashboard') }}"
                    class="text-xs text-purple-200 hover:text-white transition flex items-center gap-1">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M9 15 3 9m0 0 6-6M3 9h12a6 6 0 0 1 0 12h-3" />
                    </svg>
                    Kembali ke Admin
                </a>
            @endauth
        </div>
    </nav>

    <main class="flex-1">
        @yield('content')
    </main>

    <footer class="bg-white border-t border-gray-200 py-6 mt-12">
        <p class="text-center text-xs text-gray-400">
            &copy; {{ now()->year }} Yayasan Fatahillah. Sistem Rekrutmen Online.
        </p>
    </footer>

    @livewireScripts
</body>

</html>
