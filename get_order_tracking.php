<?php
include("connection/connect.php");
include("includes/location_utils.php");

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $order_id = intval($_POST['order_id'] ?? 0);
    
    if ($order_id) {
        // Get order details
        $query = "SELECT uo.*, dt.status as delivery_status, dt.driver_latitude, dt.driver_longitude, 
                         d.name as driver_name, d.phone as driver_phone
                  FROM users_orders uo 
                  LEFT JOIN delivery_tracking dt ON uo.o_id = dt.order_id
                  LEFT JOIN drivers d ON dt.driver_id = d.id
                  WHERE uo.o_id = ?";
        
        $stmt = $db->prepare($query);
        $stmt->bind_param("i", $order_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $order = $result->fetch_assoc();
        
        if ($order) {
            // Calculate estimated delivery time
            $estimated_delivery = null;
            if ($order['delivery_status'] === 'in_transit' && $order['driver_latitude'] && $order['driver_longitude']) {
                $distance = LocationUtils::calculateDistance(
                    $order['driver_latitude'], $order['driver_longitude'],
                    $order['customer_latitude'], $order['customer_longitude']
                );
                // Assume average speed of 30 km/h for delivery
                $estimated_minutes = round($distance * 2); // 2 minutes per km
                $estimated_delivery = date('H:i', strtotime("+{$estimated_minutes} minutes"));
            }
            
            echo json_encode([
                'success' => true,
                'order_id' => $order['o_id'],
                'item_title' => $order['title'],
                'delivery_status' => $order['delivery_status'] ?? 'processing',
                'customer_latitude' => $order['customer_latitude'],
                'customer_longitude' => $order['customer_longitude'],
                'driver_latitude' => $order['driver_latitude'],
                'driver_longitude' => $order['driver_longitude'],
                'driver_name' => $order['driver_name'],
                'driver_phone' => $order['driver_phone'],
                'estimated_delivery' => $estimated_delivery,
                'delivery_address' => $order['delivery_address']
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Order not found'
            ]);
        }
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid order ID'
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method'
    ]);
}
?> 