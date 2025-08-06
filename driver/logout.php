<?php
session_start();

// Update driver status to offline before logout
if (isset($_SESSION['driver_id'])) {
    include("../connection/connect.php");
    
    $driver_id = $_SESSION['driver_id'];
    $update_status = "UPDATE drivers SET status = 'offline' WHERE id = ?";
    $status_stmt = $db->prepare($update_status);
    $status_stmt->bind_param("i", $driver_id);
    $status_stmt->execute();
}

// Destroy all session data
session_destroy();

// Redirect to login page
header("Location: login.php");
exit();
?> 