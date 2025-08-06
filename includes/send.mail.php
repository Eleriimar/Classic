<?php
/**
 * Simple Email Functions for OnlineFood System
 * Note: For production, use a proper email library like PHPMailer
 */

function sendOrderConfirmation($userEmail, $userName, $orderDetails) {
    // For now, just log the order confirmation
    // In production, implement proper email sending
    error_log("Order confirmation for $userName ($userEmail): $orderDetails");
    return true;
}

function sendDriverNotification($driverPhone, $orderDetails) {
    // For now, just log the driver notification
    // In production, implement SMS or push notification
    error_log("Driver notification for $driverPhone: $orderDetails");
    return true;
}
?>
