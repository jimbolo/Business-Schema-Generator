<?php
/**
 * Optimized Geographic Distance Calculator
 * 
 * High-performance Haversine formula implementation with pre-calculated constants
 * for minimal overhead distance calculations between geographic coordinates.
 * 
 * Performance improvements:
 * - Pre-calculated constants (earth radius, degree-to-radian conversion)
 * - Reduced trigonometric function calls
 * - Optimized mathematical operations
 * 
 * @author Optimized GeoAPI
 * @version 1.0.0
 */
class geoCalculator {
    // Earth's radius in miles (pre-calculated constant)
    private static $earth_radius_miles = 3959;
    
    // Earth's radius in kilometers (pre-calculated constant)
    private static $earth_radius_km = 6371;
    
    // Degree to radian conversion factor (pre-calculated constant)
    private static $deg_to_rad = 0.017453292519943295; // M_PI / 180
    
    /**
     * Calculate distance between two geographic points using optimized Haversine formula
     * 
     * @param float $lat1 Latitude of first point
     * @param float $lng1 Longitude of first point
     * @param float $lat2 Latitude of second point
     * @param float $lng2 Longitude of second point
     * @param string $unit Distance unit ('miles' or 'km')
     * @return float Distance in specified unit
     */
    public static function haversineDistance($lat1, $lng1, $lat2, $lng2, $unit = 'miles') {
        // Pre-calculate radians to minimize deg2rad() calls
        $lat1_rad = $lat1 * self::$deg_to_rad;
        $lat2_rad = $lat2 * self::$deg_to_rad;
        $dlat = ($lat2 - $lat1) * self::$deg_to_rad;
        $dlng = ($lng2 - $lng1) * self::$deg_to_rad;
        
        // Optimize by calculating sin(x/2) once
        $sin_dlat_half = sin($dlat * 0.5);
        $sin_dlng_half = sin($dlng * 0.5);
        
        // Haversine formula core calculation
        $a = $sin_dlat_half * $sin_dlat_half + 
             cos($lat1_rad) * cos($lat2_rad) * $sin_dlng_half * $sin_dlng_half;
        
        // Get earth radius based on unit
        $earth_radius = ($unit === 'km') ? self::$earth_radius_km : self::$earth_radius_miles;
        
        // Calculate final distance
        return $earth_radius * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }
    
    /**
     * Fast distance calculation for sorting purposes (avoids expensive sqrt and atan2)
     * Returns squared distance - only use for relative comparisons
     * 
     * @param float $lat1 Latitude of first point
     * @param float $lng1 Longitude of first point
     * @param float $lat2 Latitude of second point
     * @param float $lng2 Longitude of second point
     * @return float Squared distance (for sorting only)
     */
    public static function fastDistanceSquared($lat1, $lng1, $lat2, $lng2) {
        $dlat = ($lat2 - $lat1) * self::$deg_to_rad;
        $dlng = ($lng2 - $lng1) * self::$deg_to_rad;
        
        // Simple Euclidean distance squared (fast approximation)
        return $dlat * $dlat + $dlng * $dlng;
    }
    
    /**
     * Calculate multiple distances in batch for better performance
     * 
     * @param float $center_lat Center latitude
     * @param float $center_lng Center longitude
     * @param array $locations Array of locations with 'latitude' and 'longitude' keys
     * @param string $unit Distance unit ('miles' or 'km')
     * @return array Locations with added 'distance' key
     */
    public static function batchDistanceCalculation($center_lat, $center_lng, $locations, $unit = 'miles') {
        $center_lat_rad = $center_lat * self::$deg_to_rad;
        $earth_radius = ($unit === 'km') ? self::$earth_radius_km : self::$earth_radius_miles;
        
        foreach ($locations as &$location) {
            $lat2_rad = $location['latitude'] * self::$deg_to_rad;
            $dlat = ($location['latitude'] - $center_lat) * self::$deg_to_rad;
            $dlng = ($location['longitude'] - $center_lng) * self::$deg_to_rad;
            
            $sin_dlat_half = sin($dlat * 0.5);
            $sin_dlng_half = sin($dlng * 0.5);
            
            $a = $sin_dlat_half * $sin_dlat_half + 
                 cos($center_lat_rad) * cos($lat2_rad) * $sin_dlng_half * $sin_dlng_half;
            
            $location['distance'] = $earth_radius * 2 * atan2(sqrt($a), sqrt(1 - $a));
        }
        
        return $locations;
    }
}