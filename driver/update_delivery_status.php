<?php
include("../connection/connect.php");
include("../includes/location_utils.php");

header('Content-Type: application/json');

session_start();

if (!isset($_SESSION['driver_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $driver_id = $_SESSION['driver_id'];
    $order_id = intval($_POST['order_id'] ?? 0);
    $status = $_POST['status'] ?? '';
    $notes = $_POST['notes'] ?? '';
    
    if ($order_id && $status) {
        // Verify driver is assigned to this order
        $verify_query = "SELECT dt.*, uo.payment_method, uo.payment_status, uo.customer_latitude, uo.customer_longitude
                        FROM delivery_tracking dt 
                        JOIN users_orders uo ON dt.order_id = uo.o_id
                        WHERE dt.order_id = ? AND dt.driver_id = ?";
        $verify_stmt = $db->prepare($verify_query);
        $verify_stmt->bind_param("ii", $order_id, $driver_id);
        $verify_stmt->execute();
        $order = $verify_stmt->get_result()->fetch_assoc();
        
        if ($order) {
            // Get current driver location
            $driver_query = "SELECT current_latitude, current_longitude FROM drivers WHERE id = ?";
            $driver_stmt = $db->prepare($driver_query);
            $driver_stmt->bind_param("i", $driver_id);
            $driver_stmt->execute();
            $driver_location = $driver_stmt->get_result()->fetch_assoc();
            
            // Update delivery tracking status
            $update_query = "UPDATE delivery_tracking SET status = ?, notes = ?, driver_latitude = ?, driver_longitude = ? 
                           WHERE order_id = ? AND driver_id = ?";
            $update_stmt = $db->prepare($update_query);
            $update_stmt->bind_param("ssddii", $status, $notes, 
                                   $driver_location['current_latitude'], $driver_location['current_longitude'],
                                   $order_id, $driver_id);
            
            if ($update_stmt->execute()) {
                // Update order status if delivered
                if ($status === 'delivered') {
                    $order_status_query = "UPDATE users_orders SET status = 'closed' WHERE o_id = ?";
                    $order_status_stmt = $db->prepare($order_status_query);
                    $order_status_stmt->bind_param("i", $order_id);
                    $order_status_stmt->execute();
                    
                    // Update driver status to available
                    $driver_status_query = "UPDATE drivers SET status = 'available' WHERE id = ?";
                    $driver_status_stmt = $db->prepare($driver_status_query);
                    $driver_status_stmt->bind_param("i", $driver_id);
                    $driver_status_stmt->execute();
                }
                
                // If payment is pending and status is picked_up, prompt for payment
                if ($status === 'picked_up' && $order['payment_method'] === 'cash' && $order['payment_status'] === 'pending') {
                    echo json_encode([
                        'success' => true,
                        'message' => 'Status updated successfully. Please collect payment from customer.',
                        'payment_required' => true,
                        'payment_method' => $order['payment_method']
                    ]);
                } else {
                    echo json_encode([
                        'success' => true,
                        'message' => 'Status updated successfully'
                    ]);
                }
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Failed to update status'
                ]);
            }
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Order not found or not assigned to you'
            ]);
        }
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid order ID or status'
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method'
    ]);
}
?> 