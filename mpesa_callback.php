<?php
include("connection/connect.php");
include("includes/mpesa_payment.php");

// Log the callback data for debugging
$callback_data = file_get_contents('php://input');
$log_file = 'mpesa_callback.log';
file_put_contents($log_file, date('Y-m-d H:i:s') . " - Callback received: " . $callback_data . "\n", FILE_APPEND);

// Parse the callback data
$data = json_decode($callback_data, true);

if ($data) {
    $mpesa = new MpesaPayment($db);
    $result = $mpesa->handleCallback($data);
    
    // Log the result
    file_put_contents($log_file, date('Y-m-d H:i:s') . " - Result: " . json_encode($result) . "\n", FILE_APPEND);
    
    // Return response to M-Pesa
    if ($result['success']) {
        http_response_code(200);
        echo json_encode(['ResultCode' => '0', 'ResultDesc' => 'Success']);
    } else {
        http_response_code(400);
        echo json_encode(['ResultCode' => '1', 'ResultDesc' => 'Failed']);
    }
} else {
    http_response_code(400);
    echo json_encode(['ResultCode' => '1', 'ResultDesc' => 'Invalid callback data']);
}
?> 