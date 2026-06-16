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
    'radius' => env('GEOFENCE_RADIUS', 200),

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
            'name' => 'Kantor 1 / SMK YP. Fatahillah 1 Kramatwatu',
            'latitude' => -6.033658338144455,
            'longitude' => 106.08295536520035,
        ],
        [
            'name' => 'Kantor 2 / SMK YP. Fatahillah 1 Cilegon Kampus 1',
            'latitude' => -6.0104385767052575,
            'longitude' => 106.0327867160515,
        ],
        [
            'name' => 'SMK YP. Fatahillah 1 Cilegon Kampus 3',
            'latitude' => -6.01133887778221,
            'longitude' => 106.03318497612834,
        ],
        [
            'name' => 'SMK YP. Fatahillah 1 Cilegon Kampus 4',
            'latitude' => -6.0274956974956035,
            'longitude' => 106.03410088308621,
        ],
        [
            'name' => 'SMK YP. Fatahillah 2 Cilegon',
            'latitude' => -6.010785227248223,
            'longitude' => 106.03288513488798,
        ],
        [
            'name' => 'SMP YP. Fatahillah Cilegon',
            'latitude' => -6.010534415407497,
            'longitude' => 106.03289743724417,
        ],
    ],

];