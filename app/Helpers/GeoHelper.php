<?php

namespace App\Helpers;

class GeoHelper
{
    /**
     * Calculate the great-circle distance between two points on the Earth's surface
     * using the Haversine formula.
     *
     * @param float|string|null $lat1
     * @param float|string|null $lon1
     * @param float|string|null $lat2
     * @param float|string|null $lon2
     * @return int|null Distance in meters, or null if any coordinate is missing
     */
    public static function calculateDistance($lat1, $lon1, $lat2, $lon2): ?int
    {
        if (is_null($lat1) || is_null($lon1) || is_null($lat2) || is_null($lon2)) {
            return null;
        }

        $earthRadius = 6371000; // Earth's radius in meters

        // Convert degrees to radians
        $latFrom = deg2rad((float) $lat1);
        $lonFrom = deg2rad((float) $lon1);
        $latTo = deg2rad((float) $lat2);
        $lonTo = deg2rad((float) $lon2);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $a = sin($latDelta / 2) * sin($latDelta / 2) +
            cos($latFrom) * cos($latTo) *
            sin($lonDelta / 2) * sin($lonDelta / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        $distance = $earthRadius * $c;

        return (int) round($distance);
    }
}
