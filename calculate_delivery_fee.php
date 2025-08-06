<?php
include("connection/connect.php");
include("includes/location_utils.php");

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $latitude = floatval($_POST['latitude'] ?? 0);
    $longitude = floatval($_POST['longitude'] ?? 0);
    
    if ($latitude && $longitude) {
        // Get restaurant location (assuming first restaurant for now)
        $restaurant_location = LocationUtils::getRestaurantLocation($db, 1);
        
        if ($restaurant_location) {
            $distance = LocationUtils::calculateDistance(
                $latitude, $longitude,
                $restaurant_location['latitude'], $restaurant_location['longitude']
            );
            
            // Get delivery fee breakdown using new calculation method
            $fee_breakdown = LocationUtils::getDeliveryFeeBreakdown($db, $latitude, $longitude, 1);
            
            // Calculate total (assuming cart total from session)
            session_start();
            $cart_total = 0;
            if (isset($_SESSION["cart_item"])) {
                foreach ($_SESSION["cart_item"] as $item) {
                    $cart_total += ($item["price"] * $item["quantity"]);
                }
            }
            
            $total_amount = $cart_total + $fee_breakdown['delivery_fee'];
            
            echo json_encode([
                'success' => true,
                'distance' => $fee_breakdown['distance'],
                'delivery_fee' => $fee_breakdown['delivery_fee'],
                'total_amount' => $total_amount,
                'is_free' => $fee_breakdown['is_free'],
                'zone' => $fee_breakdown['zone'],
                'free_delivery_radius' => floatval(LocationUtils::getSystemSetting($db, 'free_delivery_radius', 5.0)),
                'delivery_info' => $fee_breakdown
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Restaurant location not found'
            ]);
        }
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid coordinates'
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method'
    ]);
}
?> 