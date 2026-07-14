<?php
/**
 * Konfigurasi Geofencing Yayasan Fatahillah
 *
 * Cara update koordinat:
 * 1. Buka Google Maps, klik lokasi, copy koordinat
 * 2. Edit nilai lat/lng di array locations di bawah
 * 3. Jalankan: php artisan config:clear
 */
return [
    /*
    |--------------------------------------------------------------------------
    | Radius Toleransi (meter)
    |--------------------------------------------------------------------------
    | Jarak maksimum dari titik lokasi agar check-in dianggap valid.
    |
    | KEPUTUSAN FINAL (14 Juli 2026): 30 meter, SERAGAM untuk semua unit.
    | Riwayat nilai: 500m (pernah tertulis di PRD v1.4, tidak pernah jadi
    | nilai aktual di kode) -> 200m (default awal saat fitur GPS pertama
    | dibuat) -> 100m (sempat dikukuhkan 19 Juni 2026) -> 30m (nilai final
    | saat ini, diturunkan setelah testing lapangan). Opsi radius berbeda
    | per unit sudah dipertimbangkan dan SENGAJA tidak dipakai -- yayasan
    | memilih satu nilai seragam untuk semua lokasi demi kesederhanaan.
    */
    'radius' => env('GEOFENCE_RADIUS', 30),

    /*
    |--------------------------------------------------------------------------
    | Wajib Lokasi Valid
    |--------------------------------------------------------------------------
    | true  = check-in DITOLAK jika di luar radius (strict)
    | false = check-in DIIZINKAN tapi diberi flag peringatan (lenient)
    */
    'strict' => env('GEOFENCE_STRICT', true),

    /*
    |--------------------------------------------------------------------------
    | Lokasi Resmi Yayasan Fatahillah (8 titik)
    |--------------------------------------------------------------------------
    | Koordinat mengacu pada Google Maps. Untuk update: buka Maps, klik
    | titik lokasi, copy koordinat lat/lng, edit di sini, lalu config:clear.
    |
    | CATATAN: 'Kantor 2 YPF Cilegon' dan 'SMK YP. Fatahillah 1 Cilegon
    | Kampus 1' berada di area yang sangat berdekatan (koordinat berbeda
    | tipis) -- ini kondisi fisik lapangan, bukan duplikasi data.
    |
    | Semua koordinat dikonfirmasi akurat per 14 Juli 2026. Jangan ubah
    | tanpa verifikasi ulang ke lapangan atau Google Maps terbaru.
    */
    'locations' => [
        [
            'name' => 'Kantor 1 YPF Serdang',
            'latitude' => -6.038220,
            'longitude' => 106.083024,
        ],
        [
            'name' => 'SMK YP. Fatahillah 1 Kramatwatu',
            'latitude' => -6.037921,
            'longitude' => 106.082952,
        ],
        [
            'name' => 'Kantor 2 YPF Cilegon',
            'latitude' => -6.010507,
            'longitude' => 106.032824,
        ],
        [
            'name' => 'SMK YP. Fatahillah 1 Cilegon Kampus 1',
            'latitude' => -6.010683,
            'longitude' => 106.032977,
        ],
        [
            'name' => 'SMK YP. Fatahillah 1 Cilegon Kampus 3',
            'latitude' => -6.011431,
            'longitude' => 106.033159,
        ],
        [
            'name' => 'SMK YP. Fatahillah 1 Cilegon Kampus 4',
            'latitude' => -6.027610,
            'longitude' => 106.034145,
        ],
        [
            'name' => 'SMK YP. Fatahillah 2 Cilegon',
            'latitude' => -6.0107650,
            'longitude' => 106.0328082,
        ],
        [
            'name' => 'SMP YP. Fatahillah Cilegon',
            'latitude' => -6.0110060,
            'longitude' => 106.0328913,
        ],
    ],
];