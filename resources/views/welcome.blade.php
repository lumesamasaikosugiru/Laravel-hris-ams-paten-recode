<!DOCTYPE html>
<html lang="id" class="h-full">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Yayasan Fatahillah — Mendidik dengan Adab dan Ilmu</title>
    <meta name="description"
        content="Yayasan Pendidikan Fatahillah menaungi enam unit sekolah di Kramatwatu dan Cilegon, membentuk generasi berilmu dan berakhlak.">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,400;9..144,500;9..144,600;9..144,700&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap"
        rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        /* ============================================================
             Token tambahan KHUSUS welcome page.
             Warna utama (--c-primary, --c-accent, dst) DIWARISI
             langsung dari app.css — tidak didefinisikan ulang di sini.
        ============================================================ */
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
            font-size: clamp(2.25rem, 6vw, 4.5rem);
            line-height: 1.05;
            font-weight: 600;
            letter-spacing: -0.01em;
        }

        .w-hero-title em {
            font-style: italic;
            color: var(--c-primary);
            font-weight: 500;
        }

        .w-stat-row {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            border-top: 1px solid var(--w-line);
        }

        .w-stat-cell {
            padding: 1.5rem 1rem;
            border-right: 1px solid var(--w-line);
        }

        .w-stat-cell:last-child {
            border-right: none;
        }

        .w-stat-num {
            font-family: 'Fraunces', serif;
            font-size: clamp(1.75rem, 3vw, 2.5rem);
            font-weight: 600;
            color: var(--c-primary);
            line-height: 1;
        }

        .w-stat-label {
            font-size: 0.75rem;
            color: var(--w-ink-soft);
            margin-top: 0.35rem;
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

        /* ============ TENTANG ============ */
        .w-pillar {
            border-left: 2px solid var(--c-accent-light);
            padding-left: 1.25rem;
        }

        .w-pillar-num {
            font-family: 'Fraunces', serif;
            font-style: italic;
            color: var(--c-accent-h);
            font-size: 0.95rem;
        }

        /* ============ DIREKTORI SEKOLAH ============ */
        .w-directory-row {
            display: grid;
            grid-template-columns: auto 1fr auto;
            gap: 1.5rem;
            align-items: center;
            padding: 1.75rem 0;
            border-bottom: 1px solid var(--w-line);
            transition: padding-left 0.25s ease;
        }

        .w-directory-row:hover {
            padding-left: 0.5rem;
        }

        .w-directory-row:first-child {
            border-top: 1px solid var(--w-line);
        }

        .w-directory-tier {
            font-family: 'Fraunces', serif;
            font-size: 0.8rem;
            font-style: italic;
            color: var(--c-accent-h);
            width: 4.5rem;
            flex-shrink: 0;
        }

        .w-directory-name {
            font-family: 'Fraunces', serif;
            font-size: clamp(1.05rem, 2vw, 1.4rem);
            font-weight: 500;
            color: var(--w-ink);
        }

        .w-directory-loc {
            font-size: 0.8rem;
            color: var(--w-ink-soft);
            margin-top: 0.15rem;
        }

        .w-directory-arrow {
            color: var(--w-ink-soft);
            transition: transform 0.25s ease, color 0.25s ease;
            flex-shrink: 0;
        }

        .w-directory-row:hover .w-directory-arrow {
            transform: translateX(4px);
            color: var(--c-primary);
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

        /* ============ ICON SIZING (fix wajib) ============ */
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
                    <div class="font-display font-semibold text-[0.95rem]" style="color: var(--c-primary)">Yayasan
                        Fatahillah</div>
                    <div class="text-[0.65rem] uppercase tracking-wider" style="color: var(--w-ink-soft)">Pendidikan
                        &middot; Cilegon</div>
                </div>
            </a>

            <nav class="hidden sm:flex items-center gap-1">
                <a href="#tentang" class="w-nav-pill">Tentang</a>
                <a href="#sekolah" class="w-nav-pill">Unit Sekolah</a>
                <a href="#kontak" class="w-nav-pill">Kontak</a>
                <a href="{{ route('careers.index') }}" class="w-nav-pill">Karir</a>
                <a href="{{ route('login') }}" class="w-nav-pill primary">Login Staf</a>
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
            <a href="#tentang" class="w-nav-pill">Tentang</a>
            <a href="#sekolah" class="w-nav-pill">Unit Sekolah</a>
            <a href="#kontak" class="w-nav-pill">Kontak</a>
            <a href="{{ route('careers.index') }}" class="w-nav-pill">Karir</a>
            <a href="{{ route('login') }}" class="w-nav-pill primary text-center">Login Staf / Portal</a>
        </div>
    </header>

    {{-- ============================================================
         HERO
    ============================================================ --}}
    <section class="w-hero">
        <div class="max-w-6xl mx-auto px-5 sm:px-8 pt-16 sm:pt-24 pb-0 relative">
            <span class="w-hero-eyebrow">Yayasan Pendidikan Fatahillah</span>

            <h1 class="w-hero-title font-display mt-5 max-w-3xl">
                Mendidik dengan <em>adab</em>,<br class="hidden sm:block">
                membekali dengan ilmu.
            </h1>

            <p class="mt-6 max-w-xl text-base sm:text-lg leading-relaxed" style="color: var(--w-ink-soft)">
                Sejak berdiri, kami menaungi enam unit sekolah di Kramatwatu dan Cilegon —
                tempat ratusan siswa setiap tahun dibentuk menjadi pribadi yang berilmu,
                terampil, dan berakhlak.
            </p>

            <div class="mt-8 flex flex-wrap gap-3">
                <a href="#sekolah" class="w-nav-pill primary px-6 py-3 text-[0.95rem]">Lihat Unit Sekolah</a>
                <a href="{{ route('careers.index') }}" class="w-nav-pill px-6 py-3 text-[0.95rem]"
                    style="border: 1px solid var(--w-line)">Lowongan Karir</a>
            </div>
        </div>

        <div class="max-w-6xl mx-auto px-5 sm:px-8 mt-16">
            <div class="w-stat-row">
                <div class="w-stat-cell">
                    <div class="w-stat-num">6</div>
                    <div class="w-stat-label">Unit sekolah aktif</div>
                </div>
                <div class="w-stat-cell">
                    <div class="w-stat-num">2</div>
                    <div class="w-stat-label">Wilayah — Kramatwatu &amp; Cilegon</div>
                </div>
                <div class="w-stat-cell">
                    <div class="w-stat-num">SMK &amp; SMP</div>
                    <div class="w-stat-label">Jenjang pendidikan</div>
                </div>
            </div>
        </div>
    </section>

    {{-- ============================================================
         TENTANG YAYASAN
    ============================================================ --}}
    <section id="tentang" class="max-w-6xl mx-auto px-5 sm:px-8 py-20 sm:py-28">
        <div class="w-section-label w-reveal">
            <span class="tag">Tentang Kami</span>
            <span class="rule"></span>
        </div>

        <div class="grid md:grid-cols-2 gap-12 md:gap-16">
            <div class="w-reveal">
                <h2 class="font-display text-3xl sm:text-4xl font-medium leading-tight" style="color: var(--w-ink)">
                    Satu yayasan,<br>enam ruang belajar.
                </h2>
                <p class="mt-5 leading-relaxed" style="color: var(--w-ink-soft)">
                    Yayasan Pendidikan Fatahillah hadir sebagai rumah belajar bagi
                    masyarakat Kramatwatu dan Cilegon. Setiap unit sekolah di bawah
                    naungan kami menjalankan kurikulum nasional dengan penekanan pada
                    pembentukan karakter — karena kami percaya ilmu yang baik selalu
                    berjalan bersama akhlak yang baik.
                </p>
            </div>

            <div class="space-y-7">
                <div class="w-pillar w-reveal">
                    <div class="w-pillar-num">Komitmen</div>
                    <p class="mt-1.5 font-display text-lg" style="color: var(--w-ink)">Pendidikan yang terjangkau</p>
                    <p class="mt-1 text-sm leading-relaxed" style="color: var(--w-ink-soft)">
                        Membuka akses pendidikan berkualitas bagi keluarga di sekitar Kramatwatu dan Cilegon.
                    </p>
                </div>
                <div class="w-pillar w-reveal">
                    <div class="w-pillar-num">Pendekatan</div>
                    <p class="mt-1.5 font-display text-lg" style="color: var(--w-ink)">Vokasi dan adab berjalan
                        bersama</p>
                    <p class="mt-1 text-sm leading-relaxed" style="color: var(--w-ink-soft)">
                        Unit SMK kami membekali keterampilan kerja nyata, tanpa melepas pembinaan karakter.
                    </p>
                </div>
                <div class="w-pillar w-reveal">
                    <div class="w-pillar-num">Jangkauan</div>
                    <p class="mt-1.5 font-display text-lg" style="color: var(--w-ink)">Hadir di dua wilayah</p>
                    <p class="mt-1 text-sm leading-relaxed" style="color: var(--w-ink-soft)">
                        Enam kampus tersebar di Kramatwatu dan Cilegon, mendekatkan sekolah ke rumah siswa.
                    </p>
                </div>
            </div>
        </div>
    </section>

    {{-- ============================================================
         DIREKTORI SEKOLAH
    ============================================================ --}}
    <section id="sekolah" class="max-w-6xl mx-auto px-5 sm:px-8 py-20 sm:py-28 border-t"
        style="border-color: var(--w-line)">
        <div class="w-section-label w-reveal">
            <span class="tag">Unit Sekolah</span>
            <span class="rule"></span>
        </div>

        <h2 class="font-display text-3xl sm:text-4xl font-medium mb-3 w-reveal" style="color: var(--w-ink)">
            Enam kampus, satu naungan.
        </h2>
        <p class="max-w-xl mb-10 w-reveal" style="color: var(--w-ink-soft)">
            Berikut unit sekolah yang berada langsung di bawah Yayasan Pendidikan Fatahillah.
        </p>

        <div class="w-reveal">
            @php
                $units = [
                    ['tier' => 'SMK · KWT', 'name' => 'SMK YP. Fatahillah 1 Kramatwatu', 'loc' => 'Kramatwatu, Serang'],
                    ['tier' => 'SMK · CLG', 'name' => 'SMK YP. Fatahillah 1 Cilegon — Kampus 1', 'loc' => 'Cilegon'],
                    ['tier' => 'SMK · CLG', 'name' => 'SMK YP. Fatahillah 1 Cilegon — Kampus 3', 'loc' => 'Cilegon'],
                    ['tier' => 'SMK · CLG', 'name' => 'SMK YP. Fatahillah 1 Cilegon — Kampus 4', 'loc' => 'Cilegon'],
                    ['tier' => 'SMK · CLG', 'name' => 'SMK YP. Fatahillah 2 Cilegon', 'loc' => 'Cilegon'],
                    ['tier' => 'SMP · CLG', 'name' => 'SMP YP. Fatahillah Cilegon', 'loc' => 'Cilegon'],
                ];
            @endphp
            @foreach ($units as $unit)
                <div class="w-directory-row">
                    <span class="w-directory-tier">{{ $unit['tier'] }}</span>
                    <div>
                        <div class="w-directory-name">{{ $unit['name'] }}</div>
                        <div class="w-directory-loc">{{ $unit['loc'] }}</div>
                    </div>
                    <svg class="w-directory-arrow w-icon-lg" fill="none" viewBox="0 0 24 24" stroke-width="1.6"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M17.25 8.25 21 12m0 0-3.75 3.75M21 12H3" />
                    </svg>
                </div>
            @endforeach
        </div>
    </section>

    {{-- ============================================================
         KONTAK
    ============================================================ --}}
    <section id="kontak" class="max-w-6xl mx-auto px-5 sm:px-8 py-20 sm:py-28 border-t"
        style="border-color: var(--w-line)">
        <div class="grid md:grid-cols-5 gap-8">
            <div class="md:col-span-2 w-reveal">
                <div class="w-section-label" style="margin-bottom: 1.5rem">
                    <span class="tag">Kontak</span>
                    <span class="rule"></span>
                </div>
                <h2 class="font-display text-3xl font-medium leading-tight" style="color: var(--w-ink)">
                    Ada yang ingin<br>ditanyakan?
                </h2>
                <p class="mt-4 text-sm leading-relaxed" style="color: var(--w-ink-soft)">
                    Tim kami siap membantu pertanyaan seputar pendaftaran,
                    informasi unit sekolah, maupun kerja sama.
                </p>
            </div>

            <div class="md:col-span-3 w-reveal">
                <div class="w-contact-card p-7 sm:p-9">
                    <a href="mailto:info@yayasanfatahillah.sch.id" class="w-contact-link">
                        <svg class="w-icon-md" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" />
                        </svg>
                        <span class="text-sm">ypfatahillahcilegon@gmail.com</span>
                    </a>
                    <a href="tel:+62254000000" class="w-contact-link">
                        <svg class="w-icon-md" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a1.5 1.5 0 0 0 1.5-1.5v-3.43a1.5 1.5 0 0 0-1.5-1.5h-3.5a1.5 1.5 0 0 0-1.4.954l-.343.857a1 1 0 0 1-1.115.625 9.75 9.75 0 0 1-5.16-5.16 1 1 0 0 1 .625-1.115l.857-.343A1.5 1.5 0 0 0 9.86 9.5V6a1.5 1.5 0 0 0-1.5-1.5H4.93a1.5 1.5 0 0 0-1.5 1.5Z" />
                        </svg>
                        <span class="text-sm">+62 89525861543</span>
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
