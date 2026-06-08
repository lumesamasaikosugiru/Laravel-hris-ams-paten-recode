<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>{{ config('app.name','HRIS') }} — @yield('title','Dashboard')</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
@vite(['resources/css/app.css','resources/js/app.js'])
@livewireStyles
</head>
<body class="h-full bg-gray-50">

<div class="flex h-full">

    {{-- ── SIDEBAR ── --}}
    <aside id="sidebar" class="bg-sidebar w-64 flex flex-col shrink-0 shadow-xl transition-all duration-200">

        {{-- Logo --}}
        <div class="flex items-center gap-3 px-5 py-4 border-b border-white/10">
            <div class="w-8 h-8 rounded-lg bg-green-500 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.904a48.62 48.62 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.636 50.636 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.903 59.903 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0 1 12 13.489a50.702 50.702 0 0 1 3.741-3.342M6.75 15a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Zm0 0v-3.675A55.378 55.378 0 0 1 12 8.443m-7.007 11.55A5.981 5.981 0 0 0 6.75 15.75v-1.5" />
                </svg>
            </div>
            <div class="overflow-hidden">
                <div class="text-sm font-bold text-white leading-tight truncate">HRIS Fatahillah</div>
                <div class="text-[10px] text-sb-muted leading-tight">Yayasan Pendidikan</div>
            </div>
        </div>

        {{-- User --}}
        <div class="flex items-center gap-3 px-5 py-3 border-b border-white/10">
            <div class="w-8 h-8 rounded-full bg-violet-500 flex items-center justify-center text-white text-xs font-bold shrink-0">
                {{ strtoupper(substr(auth()->user()->name,0,2)) }}
            </div>
            <div class="overflow-hidden min-w-0">
                <div class="text-xs font-medium text-white truncate">{{ auth()->user()->name }}</div>
                <div class="text-[10px] text-sb-muted truncate">{{ str_replace('_',' ',auth()->user()->getRoleNames()->first() ?? '') }}</div>
            </div>
        </div>

        {{-- Navigation --}}
        <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-0.5">

            @php
                $iconDashboard = '<svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z" /></svg>';
                $iconSchool = '<svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 12V10.332A48.36 48.36 0 0 0 12 9.75c-2.551 0-5.056.2-7.5.582V21M3 21h18M12 6.75h.008v.008H12V6.75Z" /></svg>';
                $iconDept = '<svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21" /></svg>';
                $iconPos = '<svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 14.15v4.25c0 1.094-.787 2.036-1.872 2.18-2.087.277-4.216.42-6.378.42s-4.291-.143-6.378-.42c-1.085-.144-1.872-1.086-1.872-2.18v-4.25m16.5 0a2.18 2.18 0 0 0 .75-1.661V8.706c0-1.081-.768-2.015-1.837-2.175a48.114 48.114 0 0 0-3.413-.387m4.5 8.006c-.194.165-.42.295-.673.38A23.978 23.978 0 0 1 12 15.75c-2.648 0-5.195-.429-7.577-1.22a2.016 2.016 0 0 1-.673-.38m0 0A2.18 2.18 0 0 1 3 12.489V8.706c0-1.081.768-2.015 1.837-2.175a48.111 48.111 0 0 1 3.413-.387m7.5 0V5.25A2.25 2.25 0 0 0 13.5 3h-3a2.25 2.25 0 0 0-2.25 2.25v.894m7.5 0a48.667 48.667 0 0 0-7.5 0M12 12.75h.008v.008H12v-.008Z" /></svg>';
                $iconSkill = '<svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.904a48.62 48.62 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.636 50.636 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.903 59.903 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0 1 12 13.489a50.702 50.702 0 0 1 3.741-3.342M6.75 15a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Zm0 0v-3.675A55.378 55.378 0 0 1 12 8.443m-7.007 11.55A5.981 5.981 0 0 0 6.75 15.75v-1.5" /></svg>';
                $iconLeave = '<svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" /></svg>';
            @endphp

            <x-nav-item route="dashboard" label="Dashboard" :icon="$iconDashboard" />

            @if(auth()->user()->hasAnyRole(['super_admin','admin_hr']))
                <div class="sb-section">Master Data</div>
                <x-nav-item route="admin.schools.index"    label="Sekolah"    :icon="$iconSchool" />
                <x-nav-item route="admin.departments.index" label="Departemen" :icon="$iconDept" />
                <x-nav-item route="admin.positions.index"   label="Jabatan"    :icon="$iconPos" />
                <x-nav-item route="admin.skills.index"      label="Skill"      :icon="$iconSkill" />
                <x-nav-item route="admin.leave-types.index" label="Jenis Cuti" :icon="$iconLeave" />
            @endif
        </nav>

        {{-- Logout --}}
        <div class="px-3 py-3 border-t border-white/10">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="nav-link w-full text-left">
                    <x-icons.logout class="shrink-0" />
                    <span>Keluar</span>
                </button>
            </form>
        </div>
    </aside>

    {{-- ── MAIN ── --}}
    <div class="flex-1 flex flex-col min-w-0 overflow-hidden">

        {{-- Topbar --}}
        <header class="bg-white border-b border-gray-200 px-6 py-3.5 flex items-center justify-between shrink-0">
            <div>
                <h1 class="text-base font-semibold text-gray-800">@yield('title','Dashboard')</h1>
                @hasSection('subtitle')
                    <p class="text-xs text-gray-400 mt-0.5">@yield('subtitle')</p>
                @endif
            </div>
            <div class="flex items-center gap-3">
                <span class="text-xs text-gray-400 hidden sm:block">
                    {{ now()->translatedFormat('l, j F Y') }}
                </span>
                <div class="w-8 h-8 rounded-full bg-sidebar flex items-center justify-center text-white text-xs font-bold">
                    {{ strtoupper(substr(auth()->user()->name,0,2)) }}
                </div>
            </div>
        </header>

        {{-- Alerts --}}
        @if(session('success'))
        <div class="mx-6 mt-4 flex items-center gap-2.5 px-4 py-3 bg-green-50 border border-green-200 text-green-700 rounded-lg text-sm">
            <x-icons.check class="shrink-0 w-4 h-4" />
            {{ session('success') }}
        </div>
        @endif
        @if(session('error'))
        <div class="mx-6 mt-4 flex items-center gap-2.5 px-4 py-3 bg-red-50 border border-red-200 text-red-700 rounded-lg text-sm">
            <x-icons.warning class="shrink-0 w-4 h-4" />
            {{ session('error') }}
        </div>
        @endif

        {{-- Page --}}
        <main class="flex-1 overflow-y-auto p-6">
            {{ $slot }}
        </main>
    </div>
</div>

@livewireScripts
</body>
</html>
