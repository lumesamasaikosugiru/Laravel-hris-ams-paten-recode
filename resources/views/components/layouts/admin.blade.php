<!DOCTYPE html>
<html lang="id" class="h-full">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name', 'HRIS') }} — @yield('title', 'Dashboard')</title>
    <link rel="icon" type="image/jpeg" href="{{ asset('images/logo-fatahillah.jpg') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.min.css">

    <style>
        /* ============================================================
             SIDEBAR RESPONSIVE
             Desktop (lg+): sidebar statis, selalu terlihat, BISA
             di-collapse jadi mini (hanya ikon) lewat tombol toggle.
             Mobile (<lg): sidebar off-canvas, disembunyikan di luar
             layar, muncul lewat tombol hamburger + overlay gelap.
        ============================================================ */
        #sidebar {
            position: fixed;
            inset-y: 0;
            left: 0;
            z-index: 60;
            transform: translateX(-100%);
            transition: transform 0.25s ease, width 0.2s ease;
            height: 100vh;
            max-height: 100vh;
            overflow: hidden;
            /* aside sendiri tidak scroll vertikal maupun horizontal */
        }

        #sidebar.sidebar-open {
            transform: translateX(0);
        }

        @media (min-width: 1024px) {
            #sidebar {
                position: relative;
                transform: translateX(0);
                height: 100%;
                max-height: 100%;
            }
        }

        #sidebarOverlay {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 55;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.25s ease;
        }

        #sidebarOverlay.overlay-visible {
            opacity: 1;
            pointer-events: auto;
        }

        @media (min-width: 1024px) {
            #sidebarOverlay {
                display: none;
            }
        }

        /* ============================================================
             MINI SIDEBAR MODE (desktop only)
             Toggle via tombol collapse di footer sidebar.
             Saat aktif: lebar mengecil, teks/section disembunyikan,
             logo & avatar mengecil, hover nav-link tampilkan tooltip.
        ============================================================ */
        @media (min-width: 1024px) {
            #sidebar.sidebar-mini {
                width: 4.5rem;
            }

            #sidebar.sidebar-mini .sidebar-text,
            #sidebar.sidebar-mini .sb-section {
                display: none;
            }

            #sidebar.sidebar-mini .nav-link {
                justify-content: center;
                padding-left: 0;
                padding-right: 0;
            }

            #sidebar.sidebar-mini .sidebar-brand-row,
            #sidebar.sidebar-mini .sidebar-user-row {
                justify-content: center;
            }

            #sidebar.sidebar-mini .sidebar-collapse-btn svg {
                transform: rotate(180deg);
            }

            /* Tooltip saat hover nav-link dalam mode mini */
            #sidebar.sidebar-mini .nav-link {
                position: relative;
            }

            #sidebar.sidebar-mini .nav-link .nav-tooltip {
                position: absolute;
                left: calc(100% + 0.75rem);
                top: 50%;
                transform: translateY(-50%);
                background: #1F2925;
                color: #fff;
                font-size: 0.75rem;
                font-weight: 500;
                padding: 0.4rem 0.75rem;
                border-radius: 0.4rem;
                white-space: nowrap;
                opacity: 0;
                pointer-events: none;
                transition: opacity 0.15s ease;
                z-index: 70;
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
            }

            #sidebar.sidebar-mini .nav-link:hover .nav-tooltip {
                opacity: 1;
            }
        }

        /* Tooltip default SELALU hidden — hanya elemen di atas (scoped
           ke #sidebar.sidebar-mini .nav-link .nav-tooltip) yang boleh
           menampilkannya, dan itu pun hanya saat hover. */
        .nav-tooltip {
            display: none;
        }

        @media (min-width: 1024px) {
            #sidebar.sidebar-mini .nav-tooltip {
                display: block;
            }
        }
    </style>
</head>

<body class="h-full bg-gray-50">

    <div class="flex h-full">

        {{-- ── OVERLAY (mobile only) ── --}}
        <div id="sidebarOverlay" onclick="window.toggleSidebar(false)"></div>

        {{-- ── SIDEBAR ── --}}
        <aside id="sidebar" class="bg-sidebar w-64 flex flex-col shrink-0 shadow-xl">

            {{-- Logo + tombol close (mobile) --}}
            <div class="sidebar-brand-row flex items-center gap-3 px-5 py-4 border-b border-white/10">
                <img src="{{ asset('images/logo-fatahillah.jpg') }}" alt="Logo Yayasan Fatahillah"
                    class="w-9 h-9 rounded-lg object-cover shrink-0">
                <div class="overflow-hidden flex-1 sidebar-text">
                    <div class="text-sm font-bold text-white leading-tight truncate">HRIS Fatahillah</div>
                    <div class="text-[10px] text-sb-muted leading-tight">Yayasan Pendidikan</div>
                </div>
                <button onclick="window.toggleSidebar(false)"
                    class="lg:hidden sidebar-text p-1.5 -mr-1 text-white/70 hover:text-white shrink-0"
                    aria-label="Tutup menu">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            {{-- User --}}
            <div class="sidebar-user-row flex items-center gap-3 px-5 py-3 border-b border-white/10">
                <div
                    class="w-8 h-8 rounded-full bg-violet-500 flex items-center justify-center text-white text-xs font-bold shrink-0">
                    {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                </div>
                <div class="overflow-hidden min-w-0 sidebar-text">
                    <div class="text-xs font-medium text-white truncate">{{ auth()->user()->name }}</div>
                    <div class="text-[10px] text-sb-muted truncate">
                        {{ str_replace('_', ' ', auth()->user()->getRoleNames()->first() ?? '') }}</div>
                </div>
            </div>

            {{-- Navigation --}}
            <nav class="flex-1 overflow-y-auto overflow-x-hidden px-3 py-4 space-y-0.5">

                @can('dashboard.view')
                    <a href="{{ route('dashboard') }}"
                        class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                        <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z" />
                        </svg>
                        <span class="sidebar-text">Dashboard</span>
                        <span class="nav-tooltip">Dashboard</span>
                    </a>
                @endcan

                @can('master.view')
                    <div class="sb-section">Master Data</div>
                    <a href="{{ route('admin.schools.index') }}"
                        class="nav-link {{ request()->routeIs('admin.schools.*') ? 'active' : '' }}">
                        <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 12V10.332A48.36 48.36 0 0 0 12 9.75c-2.551 0-5.056.2-7.5.582V21M3 21h18M12 6.75h.008v.008H12V6.75Z" />
                        </svg>
                        <span class="sidebar-text">Sekolah</span>
                        <span class="nav-tooltip">Sekolah</span>
                    </a>
                    <a href="{{ route('admin.departments.index') }}"
                        class="nav-link {{ request()->routeIs('admin.departments.*') ? 'active' : '' }}">
                        <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21" />
                        </svg>
                        <span class="sidebar-text">Departemen</span>
                        <span class="nav-tooltip">Departemen</span>
                    </a>
                    <a href="{{ route('admin.positions.index') }}"
                        class="nav-link {{ request()->routeIs('admin.positions.*') ? 'active' : '' }}">
                        <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M20.25 14.15v4.25c0 1.094-.787 2.036-1.872 2.18-2.087.277-4.216.42-6.378.42s-4.291-.143-6.378-.42c-1.085-.144-1.872-1.086-1.872-2.18v-4.25m16.5 0a2.18 2.18 0 0 0 .75-1.661V8.706c0-1.081-.768-2.015-1.837-2.175a48.114 48.114 0 0 0-3.413-.387m4.5 8.006c-.194.165-.42.295-.673.38A23.978 23.978 0 0 1 12 15.75c-2.648 0-5.195-.429-7.577-1.22a2.016 2.016 0 0 1-.673-.38m0 0A2.18 2.18 0 0 1 3 12.489V8.706c0-1.081.768-2.015 1.837-2.175a48.111 48.111 0 0 1 3.413-.387m7.5 0V5.25A2.25 2.25 0 0 0 13.5 3h-3a2.25 2.25 0 0 0-2.25 2.25v.894m7.5 0a48.667 48.667 0 0 0-7.5 0M12 12.75h.008v.008H12v-.008Z" />
                        </svg>
                        <span class="sidebar-text">Jabatan</span>
                        <span class="nav-tooltip">Jabatan</span>
                    </a>
                    <a href="{{ route('admin.skills.index') }}"
                        class="nav-link {{ request()->routeIs('admin.skills.*') ? 'active' : '' }}">
                        <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.904a48.62 48.62 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.636 50.636 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.903 59.903 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0 1 12 13.489a50.702 50.702 0 0 1 3.741-3.342M6.75 15a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Zm0 0v-3.675A55.378 55.378 0 0 1 12 8.443m-7.007 11.55A5.981 5.981 0 0 0 6.75 15.75v-1.5" />
                        </svg>
                        <span class="sidebar-text">Skill</span>
                        <span class="nav-tooltip">Skill</span>
                    </a>
                    <a href="{{ route('admin.leave-types.index') }}"
                        class="nav-link {{ request()->routeIs('admin.leave-types.*') ? 'active' : '' }}">
                        <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                        </svg>
                        <span class="sidebar-text">Jenis Cuti</span>
                        <span class="nav-tooltip">Jenis Cuti</span>
                    </a>
                @endcan

                @can('recruitment.view')
                    <div class="sb-section">Rekrutmen</div>
                    <a href="{{ route('admin.jobs.index') }}"
                        class="nav-link {{ request()->routeIs('admin.jobs.*') ? 'active' : '' }}">
                        <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                        </svg>
                        <span class="sidebar-text">Lowongan Kerja</span>
                        <span class="nav-tooltip">Lowongan Kerja</span>
                    </a>
                    <a href="{{ route('admin.applicants.index') }}"
                        class="nav-link {{ request()->routeIs('admin.applicants.*') ? 'active' : '' }}">
                        <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
                        </svg>
                        <span class="sidebar-text">Data Pelamar</span>
                        <span class="nav-tooltip">Data Pelamar</span>
                    </a>
                @endcan

                @can('employee.view')
                    <div class="sb-section">Kepegawaian</div>
                    <a href="{{ route('admin.employees.index') }}"
                        class="nav-link {{ request()->routeIs('admin.employees.*') ? 'active' : '' }}">
                        <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
                        </svg>
                        <span class="sidebar-text">Data Pegawai</span>
                        <span class="nav-tooltip">Data Pegawai</span>
                    </a>
                @endcan

                @can('attendance.view')
                    <div class="sb-section">Absensi</div>
                    <a href="{{ route('admin.attendance.index') }}"
                        class="nav-link {{ request()->routeIs('admin.attendance.index') ? 'active' : '' }}">
                        <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                        </svg>
                        <span class="sidebar-text">Absensi Harian</span>
                        <span class="nav-tooltip">Absensi Harian</span>
                    </a>
                    <a href="{{ route('admin.offsite-approvals') }}"
                        class="nav-link {{ request()->routeIs('admin.offsite-approvals') ? 'active' : '' }}">
                        <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" />
                        </svg>
                        <span class="sidebar-text">Kegiatan Luar</span>
                        <span class="nav-tooltip">Kegiatan Luar</span>
                    </a>
                @endcan
                @can('attendance.report')
                    <a href="{{ route('admin.attendance.report') }}"
                        class="nav-link {{ request()->routeIs('admin.attendance.report') ? 'active' : '' }}">
                        <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" />
                        </svg>
                        <span class="sidebar-text">Laporan Absensi</span>
                        <span class="nav-tooltip">Laporan Absensi</span>
                    </a>
                @endcan

                @can('leave.view')
                    <div class="sb-section">Cuti & Izin</div>
                    <a href="{{ route('admin.leaves.index') }}"
                        class="nav-link {{ request()->routeIs('admin.leaves.index') ? 'active' : '' }}">
                        <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                        </svg>
                        <span class="sidebar-text">Pengajuan Cuti</span>
                        <span class="nav-tooltip">Pengajuan Cuti</span>
                    </a>
                @endcan
                @can('leave.balance')
                    <a href="{{ route('admin.leaves.balance') }}"
                        class="nav-link {{ request()->routeIs('admin.leaves.balance') ? 'active' : '' }}">
                        <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" />
                        </svg>
                        <span class="sidebar-text">Saldo Cuti</span>
                        <span class="nav-tooltip">Saldo Cuti</span>
                    </a>
                @endcan

                @can('report.view')
                    <div class="sb-section">Laporan</div>
                    <a href="{{ route('admin.reports.index') }}"
                        class="nav-link {{ request()->routeIs('admin.reports.*') ? 'active' : '' }}">
                        <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                        </svg>
                        <span class="sidebar-text">Laporan SDM</span>
                        <span class="nav-tooltip">Laporan SDM</span>
                    </a>
                @endcan

            </nav>


            {{-- Collapse toggle (desktop only) --}}
            <button onclick="window.toggleSidebarMini()"
                class="sidebar-collapse-btn nav-link hidden lg:flex items-center gap-3 mx-3 mb-1"
                style="color: var(--c-sb-muted)">
                <svg class="w-4 h-4 shrink-0 transition-transform" fill="none" viewBox="0 0 24 24"
                    stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M18.75 19.5l-7.5-7.5 7.5-7.5m-6 15L5.25 12l7.5-7.5" />
                </svg>
                <span class="sidebar-text">Sembunyikan menu</span>
                <span class="nav-tooltip">Tampilkan menu</span>
            </button>

            {{-- Logout --}}
            <div class="px-3 py-3 border-t border-white/10">
                @can('user.manage')
                    <div class="sb-section">Sistem</div>
                    <a href="{{ route('admin.users.index') }}"
                        class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                        <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                        </svg>
                        <span class="sidebar-text">Manajemen User</span>
                        <span class="nav-tooltip">Manajemen User</span>
                    </a>
                @endcan
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="nav-link w-full text-left">
                        <x-icons.logout class="shrink-0" />
                        <span class="sidebar-text">Keluar</span>
                        <span class="nav-tooltip">Keluar</span>
                    </button>
                </form>
            </div>
        </aside>

        {{-- ── MAIN ── --}}
        <div class="flex-1 flex flex-col min-w-0 overflow-hidden">

            {{-- Topbar --}}
            <header
                class="bg-white border-b border-gray-200 px-4 sm:px-6 py-3.5 flex items-center justify-between shrink-0">
                <div class="flex items-center gap-3 min-w-0">
                    {{-- Hamburger (mobile only) --}}
                    <button onclick="window.toggleSidebar(true)"
                        class="lg:hidden p-1.5 -ml-1.5 text-gray-500 hover:text-gray-700 shrink-0"
                        aria-label="Buka menu">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.6"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M3.75 6.75h16.5M3.75 12h16.5M3.75 17.25h16.5" />
                        </svg>
                    </button>
                    <div class="min-w-0">
                        <h1 class="text-base font-semibold text-gray-800 truncate">@yield('title', 'Dashboard')</h1>
                        @hasSection('subtitle')
                            <p class="text-xs text-gray-400 mt-0.5 truncate">@yield('subtitle')</p>
                        @endif
                    </div>
                </div>
                <div class="flex items-center gap-3 shrink-0">
                    <span class="text-xs text-gray-400 hidden sm:block">
                        {{ now()->translatedFormat('l, j F Y') }}
                    </span>
                    <div
                        class="w-8 h-8 rounded-full bg-sidebar flex items-center justify-center text-white text-xs font-bold">
                        {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                    </div>
                </div>
            </header>

            {{-- Alerts --}}
            @if (session('success'))
                <div
                    class="mx-4 sm:mx-6 mt-4 flex items-center gap-2.5 px-4 py-3 bg-green-50 border border-green-200 text-green-700 rounded-lg text-sm">
                    <x-icons.check class="shrink-0 w-4 h-4" />
                    {{ session('success') }}
                </div>
            @endif
            @if (session('error'))
                <div
                    class="mx-4 sm:mx-6 mt-4 flex items-center gap-2.5 px-4 py-3 bg-red-50 border border-red-200 text-red-700 rounded-lg text-sm">
                    <x-icons.warning class="shrink-0 w-4 h-4" />
                    {{ session('error') }}
                </div>
            @endif

            {{-- Page --}}
            <main class="flex-1 overflow-y-auto p-4 sm:p-6">
                @hasSection('content')
                    @yield('content')
                @else
                    {{ $slot }}
                @endif
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>
    <script>
        // ============================================================
        // SIDEBAR TOGGLE (mobile off-canvas)
        // ============================================================
        window.toggleSidebar = function(open) {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebarOverlay');
            if (open) {
                sidebar.classList.add('sidebar-open');
                overlay.classList.add('overlay-visible');
                document.body.style.overflow = 'hidden';
            } else {
                sidebar.classList.remove('sidebar-open');
                overlay.classList.remove('overlay-visible');
                document.body.style.overflow = '';
            }
        };

        // ============================================================
        // SIDEBAR MINI TOGGLE (desktop collapse)
        // Preferensi disimpan di localStorage agar tetap mini
        // setelah reload/pindah halaman.
        // ============================================================
        window.toggleSidebarMini = function() {
            const sidebar = document.getElementById('sidebar');
            const isMini = sidebar.classList.toggle('sidebar-mini');
            localStorage.setItem('sidebarMini', isMini ? '1' : '0');

            const btn = sidebar.querySelector('.sidebar-collapse-btn');
            const label = btn?.querySelector('.sidebar-text');
            const tooltip = btn?.querySelector('.nav-tooltip');
            if (label) label.textContent = isMini ? 'Tampilkan menu' : 'Sembunyikan menu';
            if (tooltip) tooltip.textContent = isMini ? 'Tampilkan menu' : 'Sembunyikan menu';
        };

        // Terapkan preferensi tersimpan sebelum render terlihat,
        // supaya tidak ada "flash" sidebar lebar lalu mengecil.
        (function() {
            if (localStorage.getItem('sidebarMini') === '1' && window.innerWidth >= 1024) {
                document.getElementById('sidebar')?.classList.add('sidebar-mini');
            }
        })();

        // Tutup otomatis saat resize ke desktop (hindari overlay nyangkut)
        window.addEventListener('resize', () => {
            if (window.innerWidth >= 1024) {
                window.toggleSidebar(false);
            }
        });

        // Tutup sidebar otomatis setelah klik link nav (mobile)
        document.addEventListener('click', (e) => {
            if (window.innerWidth < 1024 && e.target.closest('#sidebar a.nav-link')) {
                window.toggleSidebar(false);
            }
        });

        document.addEventListener('livewire:initialized', () => {
            Livewire.hook('morph.updated', () => {
                setTimeout(() => {
                    const el = document.getElementById('ts-pegawai');
                    if (!el || el.tomselect) return;
                    new TomSelect(el, {
                        placeholder: 'Ketik nama atau NIK...',
                        maxOptions: 200,
                        onChange: function(val) {
                            el.dispatchEvent(new CustomEvent('change', {
                                bubbles: true
                            }));
                            Livewire.dispatch('setEmployee', {
                                id: val ? parseInt(val) : null
                            });
                        }
                    });
                }, 150);
            });
        });
    </script>

    {{-- Close Modals by ESC keyboards --}}
    <script>
        document.addEventListener('keydown', function(e) {
            if (e.key !== 'Escape') return;

            Livewire.all().forEach(function(comp) {
                try {
                    const data = comp.snapshot.data;
                    if (!data) return;
                    Object.keys(data).forEach(function(key) {
                        if (key.startsWith('show') && key.includes('Modal')) {
                            if (data[key] === true) {
                                comp.$wire.set(key, false);
                            }
                        }
                    });
                } catch (err) {}
            });
        });
    </script>

    @livewireScripts

</body>

</html>
