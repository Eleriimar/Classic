<?php
/**
 * Location Utilities for OnlineFood System
 * Handles distance calculations, delivery fee computation, and location services
 */

class LocationUtils {
    
    /**
     * Calculate distance between two points using Haversine formula
     * @param float $lat1 Latitude of first point
     * @param float $lon1 Longitude of first point
     * @param float $lat2 Latitude of second point
     * @param float $lon2 Longitude of second point
     * @return float Distance in kilometers
     */
    public static function calculateDistance($lat1, $lon1, $lat2, $lon2) {
        $earthRadius = 6371; // Earth's radius in kilometers
        
        $latDelta = deg2rad($lat2 - $lat1);
        $lonDelta = deg2rad($lon2 - $lon1);
        
        $a = sin($latDelta / 2) * sin($latDelta / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($lonDelta / 2) * sin($lonDelta / 2);
        
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        
        return $earthRadius * $c;
    }
    
    /**
     * Calculate delivery fee based on distance and location zones
     * @param float $distance Distance in kilometers
     * @param float $freeRadius Free delivery radius in kilometers
     * @param float $baseFee Base delivery fee
     * @param float $perKmRate Rate per additional kilometer
     * @return array Delivery fee information
     */
    public static function calculateDeliveryFee($distance, $freeRadius = 5.0, $baseFee = 50.0, $perKmRate = 10.0) {
        $result = [
            'distance' => $distance,
            'free_radius' => $freeRadius,
            'delivery_fee' => 0.0,
            'is_free' => false,
            'zone' => 'unknown'
        ];
        
        if ($distance <= $freeRadius) {
            $result['delivery_fee'] = 0.0;
            $result['is_free'] = true;
            $result['zone'] = 'free';
        } elseif ($distance <= 10.0) {
            // Zone 1: 5-10km - Standard delivery
            $extraDistance = $distance - $freeRadius;
            $result['delivery_fee'] = $baseFee + ($extraDistance * $perKmRate);
            $result['zone'] = 'standard';
        } elseif ($distance <= 15.0) {
            // Zone 2: 10-15km - Extended delivery
            $extraDistance = $distance - $freeRadius;
            $result['delivery_fee'] = $baseFee + ($extraDistance * ($perKmRate * 1.5));
            $result['zone'] = 'extended';
        } else {
            // Zone 3: 15km+ - Premium delivery
            $extraDistance = $distance - $freeRadius;
            $result['delivery_fee'] = $baseFee + ($extraDistance * ($perKmRate * 2.0));
            $result['zone'] = 'premium';
        }
        
        return $result;
    }
    
    /**
     * Get restaurant location by restaurant ID
     * @param mysqli $db Database connection
     * @param int $restaurantId Restaurant ID
     * @return array|false Restaurant location data or false if not found
     */
    public static function getRestaurantLocation($db, $restaurantId) {
        $query = "SELECT * FROM restaurant_locations WHERE restaurant_id = ?";
        $stmt = $db->prepare($query);
        $stmt->bind_param("i", $restaurantId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }
    
    /**
     * Save user location
     * @param mysqli $db Database connection
     * @param int $userId User ID
     * @param float $latitude Latitude
     * @param float $longitude Longitude
     * @param string $address Address
     * @return bool Success status
     */
    public static function saveUserLocation($db, $userId, $latitude, $longitude, $address = '') {
        // Delete existing location for this user
        $deleteQuery = "DELETE FROM user_locations WHERE user_id = ?";
        $deleteStmt = $db->prepare($deleteQuery);
        $deleteStmt->bind_param("i", $userId);
        $deleteStmt->execute();
        
        // Insert new location
        $insertQuery = "INSERT INTO user_locations (user_id, latitude, longitude, address) VALUES (?, ?, ?, ?)";
        $insertStmt = $db->prepare($insertQuery);
        $insertStmt->bind_param("idds", $userId, $latitude, $longitude, $address);
        return $insertStmt->execute();
    }
    
    /**
     * Get user location
     * @param mysqli $db Database connection
     * @param int $userId User ID
     * @return array|false User location data or false if not found
     */
    public static function getUserLocation($db, $userId) {
        $query = "SELECT * FROM user_locations WHERE user_id = ? ORDER BY created_at DESC LIMIT 1";
        $stmt = $db->prepare($query);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }
    
    /**
     * Get system setting value
     * @param mysqli $db Database connection
     * @param string $key Setting key
     * @param mixed $default Default value if setting not found
     * @return mixed Setting value
     */
    public static function getSystemSetting($db, $key, $default = null) {
        $query = "SELECT setting_value FROM system_settings WHERE setting_key = ?";
        $stmt = $db->prepare($query);
        $stmt->bind_param("s", $key);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        
        return $row ? $row['setting_value'] : $default;
    }
    
    /**
     * Get delivery zones and pricing information
     * @param mysqli $db Database connection
     * @return array Delivery zones configuration
     */
    public static function getDeliveryZones($db) {
        return [
            'free' => [
                'name' => 'Free Delivery Zone',
                'distance' => floatval(self::getSystemSetting($db, 'free_delivery_radius', 5.0)),
                'fee' => 0.0,
                'description' => 'Free delivery within this radius'
            ],
            'standard' => [
                'name' => 'Standard Delivery Zone',
                'distance' => 10.0,
                'fee_multiplier' => 1.0,
                'description' => 'Standard delivery rates apply'
            ],
            'extended' => [
                'name' => 'Extended Delivery Zone',
                'distance' => 15.0,
                'fee_multiplier' => 1.5,
                'description' => 'Extended delivery with premium rates'
            ],
            'premium' => [
                'name' => 'Premium Delivery Zone',
                'distance' => null,
                'fee_multiplier' => 2.0,
                'description' => 'Premium delivery for distant locations'
            ]
        ];
    }
    
    /**
     * Get delivery fee breakdown for a specific location
     * @param mysqli $db Database connection
     * @param float $userLat User latitude
     * @param float $userLon User longitude
     * @param int $restaurantId Restaurant ID
     * @return array Delivery fee breakdown
     */
    public static function getDeliveryFeeBreakdown($db, $userLat, $userLon, $restaurantId = 1) {
        $restaurant = self::getRestaurantLocation($db, $restaurantId);
        
        if (!$restaurant) {
            return [
                'error' => 'Restaurant location not found',
                'delivery_fee' => 0.0
            ];
        }
        
        $distance = self::calculateDistance(
            $userLat, $userLon,
            $restaurant['latitude'], $restaurant['longitude']
        );
        
        $freeRadius = floatval(self::getSystemSetting($db, 'free_delivery_radius', 5.0));
        $baseFee = floatval(self::getSystemSetting($db, 'default_delivery_fee', 50.0));
        $perKmRate = floatval(self::getSystemSetting($db, 'per_km_rate', 10.0));
        
        $feeInfo = self::calculateDeliveryFee($distance, $freeRadius, $baseFee, $perKmRate);
        
        return [
            'distance' => round($distance, 2),
            'delivery_fee' => round($feeInfo['delivery_fee'], 2),
            'is_free' => $feeInfo['is_free'],
            'zone' => $feeInfo['zone'],
            'restaurant_location' => [
                'latitude' => $restaurant['latitude'],
                'longitude' => $restaurant['longitude'],
                'address' => $restaurant['address']
            ],
            'user_location' => [
                'latitude' => $userLat,
                'longitude' => $userLon
            ]
        ];
    }
    
    /**
     * Update system setting
     * @param mysqli $db Database connection
     * @param string $key Setting key
     * @param string $value Setting value
     * @return bool Success status
     */
    public static function updateSystemSetting($db, $key, $value) {
        $query = "INSERT INTO system_settings (setting_key, setting_value) VALUES (?, ?) 
                  ON DUPLICATE KEY UPDATE setting_value = ?";
        $stmt = $db->prepare($query);
        $stmt->bind_param("sss", $key, $value, $value);
        return $stmt->execute();
    }
    
    /**
     * Get available drivers
     * @param mysqli $db Database connection
     * @return array Array of available drivers
     */
    public static function getAvailableDrivers($db) {
        $query = "SELECT * FROM drivers WHERE status = 'available'";
        $result = $db->query($query);
        $drivers = [];
        while ($row = $result->fetch_assoc()) {
            $drivers[] = $row;
        }
        return $drivers;
    }
    
    /**
     * Assign driver to order
     * @param mysqli $db Database connection
     * @param int $orderId Order ID
     * @param int $driverId Driver ID
     * @return bool Success status
     */
    public static function assignDriverToOrder($db, $orderId, $driverId) {
        // Update order with driver
        $updateOrder = "UPDATE users_orders SET driver_id = ? WHERE o_id = ?";
        $stmt1 = $db->prepare($updateOrder);
        $stmt1->bind_param("ii", $driverId, $orderId);
        $stmt1->execute();
        
        // Create delivery tracking record
        $insertTracking = "INSERT INTO delivery_tracking (order_id, driver_id, status) VALUES (?, ?, 'assigned')";
        $stmt2 = $db->prepare($insertTracking);
        $stmt2->bind_param("ii", $orderId, $driverId);
        $stmt2->execute();
        
        // Update driver status
        $updateDriver = "UPDATE drivers SET status = 'busy' WHERE id = ?";
        $stmt3 = $db->prepare($updateDriver);
        $stmt3->bind_param("i", $driverId);
        $stmt3->execute();
        
        return true;
    }
    
    /**
     * Update delivery tracking status
     * @param mysqli $db Database connection
     * @param int $orderId Order ID
     * @param string $status New status
     * @param float $latitude Driver latitude (optional)
     * @param float $longitude Driver longitude (optional)
     * @return bool Success status
     */
    public static function updateDeliveryStatus($db, $orderId, $status, $latitude = null, $longitude = null) {
        $query = "UPDATE delivery_tracking SET status = ?, driver_latitude = ?, driver_longitude = ? WHERE order_id = ?";
        $stmt = $db->prepare($query);
        $stmt->bind_param("sddi", $status, $latitude, $longitude, $orderId);
        return $stmt->execute();
    }
}
?> 