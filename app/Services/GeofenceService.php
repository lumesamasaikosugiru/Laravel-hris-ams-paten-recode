<?php

namespace App\Services;

class GeofenceService
{
    /**
     * Radius bumi dalam meter.
     */
    private const EARTH_RADIUS = 6371000;

    /**
     * Cek apakah koordinat masuk dalam salah satu lokasi resmi.
     *
     * @return array{valid: bool, location_name: string, distance: float}
     */
    public function check(float $latitude, float $longitude): array
    {
        $locations = config('geofence.locations', []);
        $radius = config('geofence.radius', 200);

        $nearest = null;
        $nearestDistance = PHP_INT_MAX;
        $nearestName = '-';

        foreach ($locations as $location) {
            $distance = $this->haversine(
                $latitude,
                $longitude,
                $location['latitude'],
                $location['longitude']
            );

            if ($distance < $nearestDistance) {
                $nearestDistance = $distance;
                $nearestName = $location['name'];
                $nearest = $location;
            }
        }

        $valid = $nearestDistance <= $radius;

        return [
            'valid' => $valid,
            'location_name' => $valid ? $nearestName : 'Di luar area yayasan',
            'distance' => round($nearestDistance),
            'nearest' => $nearestName,
        ];
    }

    /**
     * Hitung jarak dua koordinat dengan formula Haversine (meter).
     */
    public function haversine(
        float $lat1,
        float $lon1,
        float $lat2,
        float $lon2
    ): float {
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) ** 2
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2))
            * sin($dLon / 2) ** 2;

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return self::EARTH_RADIUS * $c;
    }
}