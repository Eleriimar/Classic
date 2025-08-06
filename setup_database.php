<?php
// Database setup script for enhanced OnlineFood system
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Setting up Enhanced OnlineFood Database</h1>";

try {
    include("connection/connect.php");
    echo "✅ Database connection successful<br><br>";
    
    // Create system_settings table if it doesn't exist
    $system_settings_sql = "
    CREATE TABLE IF NOT EXISTS `system_settings` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `setting_key` varchar(100) NOT NULL,
        `setting_value` text NOT NULL,
        `description` text,
        `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        UNIQUE KEY `setting_key` (`setting_key`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ";
    
    if ($db->query($system_settings_sql)) {
        echo "✅ System settings table created/verified<br>";
    } else {
        echo "❌ Error creating system settings table: " . $db->error . "<br>";
    }
    
    // Create restaurant_locations table if it doesn't exist
    $restaurant_locations_sql = "
    CREATE TABLE IF NOT EXISTS `restaurant_locations` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `restaurant_id` int(11) NOT NULL,
        `latitude` decimal(10,8) NOT NULL,
        `longitude` decimal(11,8) NOT NULL,
        `address` text,
        `delivery_radius` decimal(5,2) DEFAULT 5.00,
        `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        KEY `restaurant_id` (`restaurant_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ";
    
    if ($db->query($restaurant_locations_sql)) {
        echo "✅ Restaurant locations table created/verified<br>";
    } else {
        echo "❌ Error creating restaurant locations table: " . $db->error . "<br>";
    }
    
    // Create user_locations table if it doesn't exist
    $user_locations_sql = "
    CREATE TABLE IF NOT EXISTS `user_locations` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `user_id` int(11) NOT NULL,
        `latitude` decimal(10,8) NOT NULL,
        `longitude` decimal(11,8) NOT NULL,
        `address` text,
        `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        KEY `user_id` (`user_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ";
    
    if ($db->query($user_locations_sql)) {
        echo "✅ User locations table created/verified<br>";
    } else {
        echo "❌ Error creating user locations table: " . $db->error . "<br>";
    }
    
    // Create drivers table if it doesn't exist
    $drivers_sql = "
    CREATE TABLE IF NOT EXISTS `drivers` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `name` varchar(100) NOT NULL,
        `phone` varchar(20) NOT NULL,
        `email` varchar(100),
        `vehicle_type` varchar(50),
        `vehicle_number` varchar(20),
        `status` enum('available','busy','offline') DEFAULT 'available',
        `current_latitude` decimal(10,8) DEFAULT NULL,
        `current_longitude` decimal(11,8) DEFAULT NULL,
        `password` varchar(255) DEFAULT 'driver123',
        `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        UNIQUE KEY `unique_phone` (`phone`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ";
    
    if ($db->query($drivers_sql)) {
        echo "✅ Drivers table created/verified<br>";
    } else {
        echo "❌ Error creating drivers table: " . $db->error . "<br>";
    }
    
    // Create delivery_tracking table if it doesn't exist
    $delivery_tracking_sql = "
    CREATE TABLE IF NOT EXISTS `delivery_tracking` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `order_id` int(11) NOT NULL,
        `driver_id` int(11) NOT NULL,
        `status` enum('assigned','picked_up','in_transit','delivered') DEFAULT 'assigned',
        `driver_latitude` decimal(10,8) DEFAULT NULL,
        `driver_longitude` decimal(11,8) DEFAULT NULL,
        `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
        `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        KEY `order_id` (`order_id`),
        KEY `driver_id` (`driver_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ";
    
    if ($db->query($delivery_tracking_sql)) {
        echo "✅ Delivery tracking table created/verified<br>";
    } else {
        echo "❌ Error creating delivery tracking table: " . $db->error . "<br>";
    }
    
    // Insert default system settings
    $default_settings = [
        ['free_delivery_radius', '5.00', 'Free delivery radius in km'],
        ['default_delivery_fee', '50.00', 'Default delivery fee in KES'],
        ['per_km_rate', '10.00', 'Rate per kilometer for delivery fee calculation'],
        ['zone_standard_multiplier', '1.0', 'Multiplier for standard delivery zone'],
        ['zone_extended_multiplier', '1.5', 'Multiplier for extended delivery zone'],
        ['zone_premium_multiplier', '2.0', 'Multiplier for premium delivery zone'],
        ['max_delivery_distance', '25.00', 'Maximum delivery distance in km'],
        ['delivery_zones_enabled', '1', 'Enable delivery zones feature']
    ];
    
    foreach ($default_settings as $setting) {
        $insert_sql = "INSERT IGNORE INTO system_settings (setting_key, setting_value, description) VALUES (?, ?, ?)";
        $stmt = $db->prepare($insert_sql);
        $stmt->bind_param("sss", $setting[0], $setting[1], $setting[2]);
        $stmt->execute();
    }
    echo "✅ Default system settings inserted<br>";
    
    // Insert sample restaurant location
    $restaurant_location_sql = "INSERT IGNORE INTO restaurant_locations (restaurant_id, latitude, longitude, address) VALUES (1, -1.2921, 36.8219, 'Nairobi, Kenya')";
    if ($db->query($restaurant_location_sql)) {
        echo "✅ Sample restaurant location inserted<br>";
    }
    
    // Insert sample drivers
    $sample_drivers = [
        ['John Driver', '+254700123456', 'john@example.com', 'Motorcycle', 'KCA 123A'],
        ['Jane Driver', '+254700123457', 'jane@example.com', 'Car', 'KCA 124B']
    ];
    
    foreach ($sample_drivers as $driver) {
        $driver_sql = "INSERT IGNORE INTO drivers (name, phone, email, vehicle_type, vehicle_number) VALUES (?, ?, ?, ?, ?)";
        $stmt = $db->prepare($driver_sql);
        $stmt->bind_param("sssss", $driver[0], $driver[1], $driver[2], $driver[3], $driver[4]);
        $stmt->execute();
    }
    echo "✅ Sample drivers inserted<br>";
    
    echo "<br><h2>✅ Database setup completed successfully!</h2>";
    echo "<p>You can now test the checkout page at: <a href='test_checkout.php'>test_checkout.php</a></p>";
    
} catch (Exception $e) {
    echo "❌ Database setup failed: " . $e->getMessage() . "<br>";
}
?> 