<?php
include("../connection/connect.php");

header('Content-Type: application/json');

session_start();

if (!isset($_SESSION['driver_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $driver_id = $_SESSION['driver_id'];
    $latitude = floatval($_POST['latitude'] ?? 0);
    $longitude = floatval($_POST['longitude'] ?? 0);
    
    if ($latitude && $longitude) {
        // Update driver location
        $query = "UPDATE drivers SET current_latitude = ?, current_longitude = ? WHERE id = ?";
        $stmt = $db->prepare($query);
        $stmt->bind_param("ddi", $latitude, $longitude, $driver_id);
        
        if ($stmt->execute()) {
            // Update delivery tracking location for active orders
            $update_tracking = "UPDATE delivery_tracking SET driver_latitude = ?, driver_longitude = ? 
                               WHERE driver_id = ? AND status IN ('assigned', 'picked_up', 'in_transit')";
            $tracking_stmt = $db->prepare($update_tracking);
            $tracking_stmt->bind_param("ddi", $latitude, $longitude, $driver_id);
            $tracking_stmt->execute();
            
            echo json_encode(['success' => true, 'message' => 'Location updated successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update location']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid coordinates']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?> 