<?php

/**
 * Konfigurasi Geofencing Yayasan Fatahillah
 *
 * Cara update koordinat:
 * 1. Buka Google Maps → klik lokasi → copy koordinat
 * 2. Edit nilai lat/lng di array locations di bawah
 * 3. Jalankan: php artisan config:clear
 */

return [

    /*
    |--------------------------------------------------------------------------
    | Radius Toleransi (meter)
    |--------------------------------------------------------------------------
    | Jarak maksimum dari titik lokasi agar check-in dianggap valid.
    | 200 meter = standar untuk gedung besar / area kampus.
    */
    'radius' => env('GEOFENCE_RADIUS', 100),

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
    | Lokasi Resmi Yayasan Fatahillah
    |--------------------------------------------------------------------------
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
            'latitude' => -6.010683,
            'longitude' => 106.032977,
        ],
        [
            'name' => 'SMP YP. Fatahillah Cilegon',
            'latitude' => -6.010849,
            'longitude' => 106.032935,
        ],
    ],

];