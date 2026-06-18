<!DOCTYPE html>
<html lang="id" class="h-full">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HRIS Fatahillah — Sistem Kepegawaian Yayasan</title>
    <meta name="description"
        content="Portal akses HRIS dan Portal Pegawai Yayasan Pendidikan Fatahillah — kepegawaian, absensi, dan cuti dalam satu sistem.">
    <link rel="icon" type="image/jpeg" href="{{ asset('images/logo-fatahillah.jpg') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,400;9..144,500;9..144,600;9..144,700&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap"
        rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        :root {
            --w-canvas: #FAF8F1;
            --w-canvas-raised: #F1EEE3;
            --w-ink: #1F2925;
            --w-ink-soft: #5B6660;
            --w-line: rgba(6, 95, 70, 0.12);
        }

        .welcome-page * {
            box-sizing: border-box;
        }

        .welcome-page {
            background: var(--w-canvas);
            color: var(--w-ink);
        }

        html {
            scroll-behavior: smooth;
        }

        .font-display {
            font-family: 'Fraunces', serif;
            font-optical-sizing: auto;
        }

        /* ============ HEADER ============ */
        .w-header {
            position: sticky;
            top: 0;
            z-index: 50;
            background: rgba(250, 248, 241, 0.92);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid var(--w-line);
        }

        .w-nav-pill {
            font-size: 0.875rem;
            font-weight: 500;
            color: var(--w-ink);
            padding: 0.5rem 1.1rem;
            border-radius: 999px;
            transition: background 0.2s ease, color 0.2s ease;
            text-decoration: none;
            white-space: nowrap;
        }

        .w-nav-pill:hover {
            background: var(--w-canvas-raised);
        }

        .w-nav-pill.primary {
            background: var(--c-primary);
            color: #fff;
        }

        .w-nav-pill.primary:hover {
            background: var(--c-primary-end);
        }

        /* ============ HERO ============ */
        .w-hero {
            position: relative;
            overflow: hidden;
            border-bottom: 1px solid var(--w-line);
        }

        .w-hero-eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.75rem;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            color: var(--c-accent-h);
            font-weight: 600;
        }

        .w-hero-eyebrow::before {
            content: '';
            width: 1.5rem;
            height: 1px;
            background: var(--c-accent-h);
            display: inline-block;
        }

        .w-hero-title {
            font-size: clamp(2.25rem, 5.5vw, 4rem);
            line-height: 1.08;
            font-weight: 600;
            letter-spacing: -0.01em;
        }

        .w-hero-title em {
            font-style: italic;
            color: var(--c-primary);
            font-weight: 500;
        }

        /* ============ ENTRY CARDS (CTA utama) ============ */
        .w-entry-card {
            position: relative;
            border-radius: 1.5rem;
            padding: 2rem;
            overflow: hidden;
            text-decoration: none;
            transition: transform 0.25s ease, box-shadow 0.25s ease;
            display: flex;
            flex-direction: column;
            height: 100%;
        }

        .w-entry-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 16px 32px -12px rgba(6, 95, 70, 0.25);
        }

        .w-entry-card.hris {
            background: var(--c-primary);
            color: #fff;
        }

        .w-entry-card.hris::before {
            content: '';
            position: absolute;
            top: -40%;
            right: -20%;
            width: 70%;
            height: 180%;
            background: radial-gradient(circle, rgba(16, 185, 129, 0.22), transparent 70%);
        }

        .w-entry-card.portal {
            background: #fff;
            border: 1px solid var(--w-line);
            color: var(--w-ink);
        }

        .w-entry-icon {
            width: 3rem;
            height: 3rem;
            border-radius: 0.9rem;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1.5rem;
            flex-shrink: 0;
        }

        .w-entry-card.hris .w-entry-icon {
            background: rgba(255, 255, 255, 0.15);
        }

        .w-entry-card.portal .w-entry-icon {
            background: var(--c-accent-light);
        }

        .w-entry-tag {
            font-size: 0.7rem;
            font-weight: 600;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            margin-bottom: 0.5rem;
            opacity: 0.75;
        }

        .w-entry-title {
            font-family: 'Fraunces', serif;
            font-size: 1.6rem;
            font-weight: 500;
            margin-bottom: 0.6rem;
        }

        .w-entry-desc {
            font-size: 0.9rem;
            line-height: 1.55;
            opacity: 0.85;
            flex: 1;
        }

        .w-entry-cta {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            font-size: 0.875rem;
            font-weight: 600;
            margin-top: 1.5rem;
        }

        .w-entry-card:hover .w-entry-cta svg {
            transform: translateX(3px);
        }

        .w-entry-cta svg {
            transition: transform 0.2s ease;
        }

        /* ============ SECTION LABEL ============ */
        .w-section-label {
            display: flex;
            align-items: baseline;
            gap: 1rem;
            margin-bottom: 2.5rem;
        }

        .w-section-label .tag {
            font-family: 'Fraunces', serif;
            font-style: italic;
            color: var(--c-accent-h);
            font-size: 1.05rem;
            white-space: nowrap;
        }

        .w-section-label .rule {
            flex: 1;
            height: 1px;
            background: var(--w-line);
        }

        /* ============ FITUR GRID ============ */
        .w-feature {
            padding: 1.5rem 0;
            border-bottom: 1px solid var(--w-line);
            display: grid;
            grid-template-columns: auto 1fr;
            gap: 1.1rem;
            align-items: flex-start;
        }

        .w-feature:first-child {
            border-top: 1px solid var(--w-line);
        }

        .w-feature-icon {
            width: 2.25rem;
            height: 2.25rem;
            border-radius: 0.6rem;
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--c-accent-light);
            color: var(--c-primary);
        }

        .w-feature-title {
            font-family: 'Fraunces', serif;
            font-size: 1.05rem;
            font-weight: 500;
            color: var(--w-ink);
        }

        .w-feature-desc {
            font-size: 0.85rem;
            color: var(--w-ink-soft);
            margin-top: 0.25rem;
            line-height: 1.5;
        }

        /* ============ KONTAK ============ */
        .w-contact-card {
            background: var(--c-primary);
            color: #fff;
            border-radius: 1.25rem;
            position: relative;
            overflow: hidden;
        }

        .w-contact-card::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -10%;
            width: 60%;
            height: 200%;
            background: radial-gradient(circle, rgba(16, 185, 129, 0.18), transparent 70%);
        }

        .w-contact-link {
            display: flex;
            align-items: center;
            gap: 0.85rem;
            padding: 0.9rem 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.14);
            color: #fff;
            text-decoration: none;
            transition: opacity 0.2s ease;
            position: relative;
            z-index: 1;
        }

        .w-contact-link:hover {
            opacity: 0.78;
        }

        .w-contact-link:last-child {
            border-bottom: none;
        }

        /* ============ FOOTER ============ */
        .w-footer {
            border-top: 1px solid var(--w-line);
            background: var(--w-canvas-raised);
        }

        /* ============ ICON SIZING ============ */
        .w-icon-xs {
            width: 0.75rem;
            height: 0.75rem;
            flex-shrink: 0;
        }

        .w-icon-sm {
            width: 1rem;
            height: 1rem;
            flex-shrink: 0;
        }

        .w-icon-md {
            width: 1.125rem;
            height: 1.125rem;
            flex-shrink: 0;
        }

        .w-icon-lg {
            width: 1.25rem;
            height: 1.25rem;
            flex-shrink: 0;
        }

        /* ============ MOTION ============ */
        @media (prefers-reduced-motion: no-preference) {
            .w-reveal {
                opacity: 0;
                transform: translateY(16px);
                transition: opacity 0.7s ease, transform 0.7s ease;
            }

            .w-reveal.in-view {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .welcome-page a:focus-visible,
        .welcome-page button:focus-visible {
            outline: 2px solid var(--c-primary);
            outline-offset: 2px;
        }
    </style>
</head>

<body class="welcome-page">

    {{-- ============================================================
         HEADER
    ============================================================ --}}
    <header class="w-header">
        <div class="max-w-6xl mx-auto px-5 sm:px-8 py-3.5 flex items-center justify-between">
            <a href="/" class="flex items-center gap-3">
                <img src="{{ asset('images/logo-fatahillah.jpg') }}" alt="Logo Yayasan Fatahillah"
                    class="w-9 h-9 rounded-lg object-cover">
                <div class="leading-tight">
                    <div class="font-display font-semibold text-[0.95rem]" style="color: var(--c-primary)">HRIS
                        Fatahillah</div>
                    <div class="text-[0.65rem] uppercase tracking-wider" style="color: var(--w-ink-soft)">Sistem
                        Kepegawaian</div>
                </div>
            </a>

            <nav class="hidden sm:flex items-center gap-1">
                <a href="#fitur" class="w-nav-pill">Fitur</a>
                <a href="#kontak" class="w-nav-pill">Bantuan</a>
                <a href="{{ route('careers.index') }}" class="w-nav-pill">Karir</a>
                <a href="{{ route('login') }}" class="w-nav-pill primary">Masuk</a>
            </nav>

            <button id="wMobileBtn" class="sm:hidden p-2 -mr-2" aria-label="Buka menu" aria-expanded="false">
                <svg class="w-icon-lg" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M3.75 6.75h16.5M3.75 12h16.5M3.75 17.25h16.5" />
                </svg>
            </button>
        </div>

        <div id="wMobileMenu" class="sm:hidden hidden border-t px-5 py-3 flex flex-col gap-1"
            style="border-color: var(--w-line)">
            <a href="#fitur" class="w-nav-pill">Fitur</a>
            <a href="#kontak" class="w-nav-pill">Bantuan</a>
            <a href="{{ route('careers.index') }}" class="w-nav-pill">Karir</a>
            <a href="{{ route('login') }}" class="w-nav-pill primary text-center">Masuk</a>
        </div>
    </header>

    {{-- ============================================================
         HERO
    ============================================================ --}}
    <section class="w-hero">
        <div class="max-w-6xl mx-auto px-5 sm:px-8 pt-14 sm:pt-20 pb-16 sm:pb-20 relative text-center">
            <span class="w-hero-eyebrow justify-center">Yayasan Pendidikan Fatahillah Cilegon</span>

            <h1 class="w-hero-title font-display mt-5 mx-auto max-w-2xl">
                Satu sistem, untuk <em>seluruh</em> urusan kepegawaian.
            </h1>

            <p class="mt-5 max-w-lg mx-auto text-base sm:text-lg leading-relaxed" style="color: var(--w-ink-soft)">
                Kelola data pegawai, absensi, dan cuti — atau cukup check-in harian
                dari ponsel. Pilih sesuai peran Anda di bawah.
            </p>
        </div>
    </section>

    {{-- ============================================================
         ENTRY CARDS — HRIS & Portal
    ============================================================ --}}
    <section class="max-w-6xl mx-auto px-5 sm:px-8 -mt-10 sm:-mt-12 relative z-10 pb-20 sm:pb-28">
        <div class="grid sm:grid-cols-2 gap-5">

            {{-- HRIS Admin --}}
            <a href="{{ route('login') }}" class="w-entry-card hris w-reveal">
                <div class="w-entry-icon">
                    <svg class="w-icon-lg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z" />
                    </svg>
                </div>
                <div class="w-entry-tag">Untuk HR &amp; Admin</div>
                <div class="w-entry-title font-display">Masuk ke HRIS</div>
                <p class="w-entry-desc">
                    Kelola data pegawai, rekap absensi, persetujuan cuti, dan laporan
                    seluruh unit sekolah dari satu dashboard.
                </p>
                <span class="w-entry-cta">
                    Masuk sebagai Admin
                    <svg class="w-icon-sm" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M17.25 8.25 21 12m0 0-3.75 3.75M21 12H3" />
                    </svg>
                </span>
            </a>

            {{-- Portal Staf --}}
            <a href="{{ route('login') }}" class="w-entry-card portal w-reveal">
                <div class="w-entry-icon">
                    <svg class="w-icon-lg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="var(--c-primary)">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M10.5 1.5H8.25A2.25 2.25 0 0 0 6 3.75v16.5a2.25 2.25 0 0 0 2.25 2.25h7.5A2.25 2.25 0 0 0 18 20.25V3.75a2.25 2.25 0 0 0-2.25-2.25H13.5m-3 0V3h3V1.5m-3 0h3m-3 18.75h3" />
                    </svg>
                </div>
                <div class="w-entry-tag" style="color: var(--c-primary)">Untuk Kepala Bidang &amp; Staf</div>
                <div class="w-entry-title font-display">Masuk ke Portal</div>
                <p class="w-entry-desc" style="color: var(--w-ink-soft)">
                    Check-in dan check-out harian dengan lokasi GPS, ajukan cuti,
                    dan pantau riwayat absensi langsung dari ponsel.
                </p>
                <span class="w-entry-cta" style="color: var(--c-primary)">
                    Masuk sebagai Staf
                    <svg class="w-icon-sm" fill="none" viewBox="0 0 24 24" stroke-width="2"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M17.25 8.25 21 12m0 0-3.75 3.75M21 12H3" />
                    </svg>
                </span>
            </a>

        </div>
    </section>

    {{-- ============================================================
         FITUR — apa yang bisa dilakukan di masing-masing sistem
    ============================================================ --}}
    <section id="fitur" class="max-w-6xl mx-auto px-5 sm:px-8 py-20 sm:py-24 border-t"
        style="border-color: var(--w-line)">
        <div class="w-section-label w-reveal">
            <span class="tag">Fitur</span>
            <span class="rule"></span>
        </div>

        <div class="grid md:grid-cols-2 gap-12 md:gap-16">

            {{-- Kolom HRIS --}}
            <div class="w-reveal">
                <h2 class="font-display text-2xl font-medium mb-1" style="color: var(--w-ink)">Di dalam HRIS</h2>
                <p class="text-sm mb-2" style="color: var(--w-ink-soft)">Untuk pengelola kepegawaian oleh Bidang SDM.
                </p>

                <div class="mt-6">
                    <div class="w-feature">
                        <div class="w-feature-icon">
                            <svg class="w-icon-sm" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
                            </svg>
                        </div>
                        <div>
                            <div class="w-feature-title">Data Kepegawaian</div>
                            <div class="w-feature-desc">Profil, riwayat jabatan, dan dokumen seluruh pegawai di satu
                                tempat.</div>
                        </div>
                    </div>
                    <div class="w-feature">
                        <div class="w-feature-icon">
                            <svg class="w-icon-sm" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                            </svg>
                        </div>
                        <div>
                            <div class="w-feature-title">Rekap &amp; Laporan Absensi</div>
                            <div class="w-feature-desc">Pantau kehadiran seluruh unit, termasuk persetujuan kegiatan di
                                luar lokasi.</div>
                        </div>
                    </div>
                    <div class="w-feature">
                        <div class="w-feature-icon">
                            <svg class="w-icon-sm" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                            </svg>
                        </div>
                        <div>
                            <div class="w-feature-title">Persetujuan Cuti</div>
                            <div class="w-feature-desc">Tinjau dan setujui pengajuan cuti pegawai sesuai aturan
                                yayasan.</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Kolom Portal --}}
            <div class="w-reveal">
                <h2 class="font-display text-2xl font-medium mb-1" style="color: var(--w-ink)">Di dalam Portal</h2>
                <p class="text-sm mb-2" style="color: var(--w-ink-soft)">Untuk tenaga pendidik & kependidikan.</p>

                <div class="mt-6">
                    <div class="w-feature">
                        <div class="w-feature-icon">
                            <svg class="w-icon-sm" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" />
                            </svg>
                        </div>
                        <div>
                            <div class="w-feature-title">Check-in &amp; Check-out GPS</div>
                            <div class="w-feature-desc">Absensi harian terverifikasi lokasi, langsung dari ponsel di
                                mana pun Anda bertugas.</div>
                        </div>
                    </div>
                    <div class="w-feature">
                        <div class="w-feature-icon">
                            <svg class="w-icon-sm" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                            </svg>
                        </div>
                        <div>
                            <div class="w-feature-title">Pengajuan Cuti</div>
                            <div class="w-feature-desc">Ajukan cuti langsung dari Portal, pantau status persetujuannya
                                secara real-time.</div>
                        </div>
                    </div>
                    <div class="w-feature">
                        <div class="w-feature-icon">
                            <svg class="w-icon-sm" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" />
                            </svg>
                        </div>
                        <div>
                            <div class="w-feature-title">Riwayat Absensi</div>
                            <div class="w-feature-desc">Lihat rekap hadir, terlambat, dan cuti bulan ini dalam satu
                                tampilan ringkas.</div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </section>

    {{-- ============================================================
         KONTAK / BANTUAN
    ============================================================ --}}
    <section id="kontak" class="max-w-6xl mx-auto px-5 sm:px-8 py-20 sm:py-24 border-t"
        style="border-color: var(--w-line)">
        <div class="grid md:grid-cols-5 gap-8">
            <div class="md:col-span-2 w-reveal">
                <div class="w-section-label" style="margin-bottom: 1.5rem">
                    <span class="tag">Bantuan</span>
                    <span class="rule"></span>
                </div>
                <h2 class="font-display text-3xl font-medium leading-tight" style="color: var(--w-ink)">
                    Lupa akun atau<br>butuh akses?
                </h2>
                <p class="mt-4 text-sm leading-relaxed" style="color: var(--w-ink-soft)">
                    Akun HRIS dan Portal dikelola oleh admin SDM yayasan.
                    Hubungi tim kami jika Anda belum memiliki akses.
                </p>
            </div>

            <div class="md:col-span-3 w-reveal">
                <div class="w-contact-card p-7 sm:p-9">
                    <a href="mailto:ypfatahillahcilegon@gmail.com" class="w-contact-link">
                        <svg class="w-icon-md" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" />
                        </svg>
                        <span class="text-sm">ypfatahillahcilegon@gmail.com</span>
                    </a>
                    <a href="tel:+6289525861543" class="w-contact-link">
                        <svg class="w-icon-md" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a1.5 1.5 0 0 0 1.5-1.5v-3.43a1.5 1.5 0 0 0-1.5-1.5h-3.5a1.5 1.5 0 0 0-1.4.954l-.343.857a1 1 0 0 1-1.115.625 9.75 9.75 0 0 1-5.16-5.16 1 1 0 0 1 .625-1.115l.857-.343A1.5 1.5 0 0 0 9.86 9.5V6a1.5 1.5 0 0 0-1.5-1.5H4.93a1.5 1.5 0 0 0-1.5 1.5Z" />
                        </svg>
                        <span class="text-sm">+62 895-2586-1543</span>
                    </a>
                    <div class="w-contact-link" style="cursor: default">
                        <svg class="w-icon-md" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" />
                        </svg>
                        <span class="text-sm">Kramatwatu &amp; Cilegon, Banten</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ============================================================
         FOOTER
    ============================================================ --}}
    <footer class="w-footer">
        <div class="max-w-6xl mx-auto px-5 sm:px-8 py-8 flex flex-col sm:flex-row items-center justify-between gap-3">
            <div class="flex items-center gap-2.5">
                <img src="{{ asset('images/logo-fatahillah.jpg') }}" alt=""
                    class="w-6 h-6 rounded object-cover">
                <span class="text-sm font-medium" style="color: var(--w-ink)">Yayasan Fatahillah</span>
            </div>
            <p class="text-xs" style="color: var(--w-ink-soft)">
                &copy; {{ now()->year }} Yayasan Pendidikan Fatahillah. Seluruh hak dilindungi.
            </p>
        </div>
    </footer>

    <script>
        const wBtn = document.getElementById('wMobileBtn');
        const wMenu = document.getElementById('wMobileMenu');
        wBtn?.addEventListener('click', () => {
            const isOpen = !wMenu.classList.contains('hidden');
            wMenu.classList.toggle('hidden');
            wBtn.setAttribute('aria-expanded', String(!isOpen));
        });

        if (window.matchMedia('(prefers-reduced-motion: no-preference)').matches) {
            const revealEls = document.querySelectorAll('.w-reveal');
            const observer = new IntersectionObserver((entries) => {
                entries.forEach((entry, i) => {
                    if (entry.isIntersecting) {
                        setTimeout(() => entry.target.classList.add('in-view'), i % 4 * 80);
                        observer.unobserve(entry.target);
                    }
                });
            }, {
                threshold: 0.15
            });
            revealEls.forEach(el => observer.observe(el));
        } else {
            document.querySelectorAll('.w-reveal').forEach(el => el.classList.add('in-view'));
        }
    </script>

</body>

</html>
