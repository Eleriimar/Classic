<?php
include("../connection/connect.php");

header('Content-Type: application/json');

session_start();

if (!isset($_SESSION['driver_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $order_id = intval($_POST['order_id'] ?? 0);
    $driver_id = $_SESSION['driver_id'];
    
    if ($order_id) {
        // Get order details with customer information
        $query = "SELECT uo.*, u.username as customer_name, u.phone as customer_phone,
                         dt.status as delivery_status, dt.driver_latitude, dt.driver_longitude
                  FROM users_orders uo 
                  LEFT JOIN users u ON uo.u_id = u.u_id
                  LEFT JOIN delivery_tracking dt ON uo.o_id = dt.order_id
                  WHERE uo.o_id = ? AND dt.driver_id = ?";
        
        $stmt = $db->prepare($query);
        $stmt->bind_param("ii", $order_id, $driver_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $order = $result->fetch_assoc();
        
        if ($order) {
            echo json_encode([
                'success' => true,
                'order_id' => $order['o_id'],
                'item_title' => $order['title'],
                'quantity' => $order['quantity'],
                'price' => $order['price'],
                'delivery_fee' => $order['delivery_fee'],
                'customer_name' => $order['customer_name'],
                'customer_phone' => $order['customer_phone'],
                'delivery_address' => $order['delivery_address'],
                'customer_latitude' => $order['customer_latitude'],
                'customer_longitude' => $order['customer_longitude'],
                'delivery_status' => $order['delivery_status'],
                'driver_latitude' => $order['driver_latitude'],
                'driver_longitude' => $order['driver_longitude'],
                'payment_method' => $order['payment_method'],
                'payment_status' => $order['payment_status']
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Order not found or not assigned to you'
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