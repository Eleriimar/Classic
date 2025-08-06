<?php
/**
 * M-Pesa Payment Integration for OnlineFood System
 * Handles mobile money transactions using Safaricom M-Pesa API
 */

class MpesaPayment {
    private $db;
    private $consumerKey;
    private $consumerSecret;
    private $passkey;
    private $environment;
    private $shortcode;
    
    public function __construct($db) {
        $this->db = $db;
        $this->loadSettings();
    }
    
    /**
     * Load M-Pesa settings from database
     */
    private function loadSettings() {
        require_once 'location_utils.php';
        
        $this->consumerKey = LocationUtils::getSystemSetting($this->db, 'mpesa_consumer_key', '');
        $this->consumerSecret = LocationUtils::getSystemSetting($this->db, 'mpesa_consumer_secret', '');
        $this->passkey = LocationUtils::getSystemSetting($this->db, 'mpesa_passkey', '');
        $this->environment = LocationUtils::getSystemSetting($this->db, 'mpesa_environment', 'sandbox');
        $this->shortcode = $this->environment === 'sandbox' ? '174379' : 'YOUR_LIVE_SHORTCODE';
    }
    
    /**
     * Get access token from M-Pesa API
     * @return string|false Access token or false on failure
     */
    private function getAccessToken() {
        $url = $this->environment === 'sandbox' 
            ? 'https://sandbox.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials'
            : 'https://api.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials';
        
        $credentials = base64_encode($this->consumerKey . ':' . $this->consumerSecret);
        
        $headers = [
            'Authorization: Basic ' . $credentials,
            'Content-Type: application/json'
        ];
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode === 200) {
            $data = json_decode($response, true);
            return $data['access_token'] ?? false;
        }
        
        return false;
    }
    
    /**
     * Initiate STK Push (Payment Request)
     * @param string $phoneNumber Customer phone number
     * @param float $amount Payment amount
     * @param int $orderId Order ID
     * @param string $reference Payment reference
     * @return array Response data
     */
    public function initiatePayment($phoneNumber, $amount, $orderId, $reference = '') {
        $accessToken = $this->getAccessToken();
        if (!$accessToken) {
            return ['success' => false, 'message' => 'Failed to get access token'];
        }
        
        $url = $this->environment === 'sandbox'
            ? 'https://sandbox.safaricom.co.ke/mpesa/stkpush/v1/processrequest'
            : 'https://api.safaricom.co.ke/mpesa/stkpush/v1/processrequest';
        
        $timestamp = date('YmdHis');
        $password = base64_encode($this->shortcode . $this->passkey . $timestamp);
        
        $phoneNumber = $this->formatPhoneNumber($phoneNumber);
        
        $data = [
            'BusinessShortCode' => $this->shortcode,
            'Password' => $password,
            'Timestamp' => $timestamp,
            'TransactionType' => 'CustomerPayBillOnline',
            'Amount' => $amount,
            'PartyA' => $phoneNumber,
            'PartyB' => $this->shortcode,
            'PhoneNumber' => $phoneNumber,
            'CallBackURL' => $this->getCallbackUrl(),
            'AccountReference' => $reference ?: 'OnlineFood_' . $orderId,
            'TransactionDesc' => 'Food Delivery Payment'
        ];
        
        $headers = [
            'Authorization: Bearer ' . $accessToken,
            'Content-Type: application/json'
        ];
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        $responseData = json_decode($response, true);
        
        // Save transaction record
        $this->saveTransaction($orderId, $responseData['CheckoutRequestID'] ?? '', $phoneNumber, $amount, 'pending');
        
        return [
            'success' => $httpCode === 200 && isset($responseData['CheckoutRequestID']),
            'data' => $responseData,
            'message' => $httpCode === 200 ? 'Payment initiated successfully' : 'Failed to initiate payment'
        ];
    }
    
    /**
     * Handle M-Pesa callback
     * @param array $callbackData Callback data from M-Pesa
     * @return array Response data
     */
    public function handleCallback($callbackData) {
        $resultCode = $callbackData['ResultCode'] ?? '';
        $checkoutRequestId = $callbackData['CheckoutRequestID'] ?? '';
        $merchantRequestId = $callbackData['MerchantRequestID'] ?? '';
        $amount = $callbackData['Amount'] ?? 0;
        
        // Update transaction status
        $status = $resultCode === '0' ? 'success' : 'failed';
        $this->updateTransaction($checkoutRequestId, $status, $callbackData);
        
        // Update order payment status
        if ($status === 'success') {
            $this->updateOrderPaymentStatus($checkoutRequestId, 'paid');
        }
        
        return [
            'success' => $status === 'success',
            'message' => $status === 'success' ? 'Payment successful' : 'Payment failed'
        ];
    }
    
    /**
     * Save transaction record
     * @param int $orderId Order ID
     * @param string $transactionId Transaction ID
     * @param string $phoneNumber Phone number
     * @param float $amount Amount
     * @param string $status Status
     * @return bool Success status
     */
    private function saveTransaction($orderId, $transactionId, $phoneNumber, $amount, $status) {
        $query = "INSERT INTO mpesa_transactions (order_id, transaction_id, phone_number, amount, status) 
                  VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("issds", $orderId, $transactionId, $phoneNumber, $amount, $status);
        return $stmt->execute();
    }
    
    /**
     * Update transaction status
     * @param string $transactionId Transaction ID
     * @param string $status New status
     * @param array $responseData Response data
     * @return bool Success status
     */
    private function updateTransaction($transactionId, $status, $responseData) {
        $query = "UPDATE mpesa_transactions SET status = ?, response_code = ?, response_message = ? 
                  WHERE transaction_id = ?";
        $responseCode = $responseData['ResultCode'] ?? '';
        $responseMessage = $responseData['ResultDesc'] ?? '';
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("ssss", $status, $responseCode, $responseMessage, $transactionId);
        return $stmt->execute();
    }
    
    /**
     * Update order payment status
     * @param string $transactionId Transaction ID
     * @param string $paymentStatus Payment status
     * @return bool Success status
     */
    private function updateOrderPaymentStatus($transactionId, $paymentStatus) {
        $query = "UPDATE users_orders SET payment_status = ?, mpesa_transaction_id = ? 
                  WHERE o_id = (SELECT order_id FROM mpesa_transactions WHERE transaction_id = ?)";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("sss", $paymentStatus, $transactionId, $transactionId);
        return $stmt->execute();
    }
    
    /**
     * Format phone number for M-Pesa
     * @param string $phoneNumber Phone number
     * @return string Formatted phone number
     */
    private function formatPhoneNumber($phoneNumber) {
        // Remove any non-digit characters
        $phoneNumber = preg_replace('/[^0-9]/', '', $phoneNumber);
        
        // If number starts with 0, replace with 254
        if (substr($phoneNumber, 0, 1) === '0') {
            $phoneNumber = '254' . substr($phoneNumber, 1);
        }
        
        // If number starts with +, remove it
        if (substr($phoneNumber, 0, 1) === '+') {
            $phoneNumber = substr($phoneNumber, 1);
        }
        
        return $phoneNumber;
    }
    
    /**
     * Get callback URL
     * @return string Callback URL
     */
    private function getCallbackUrl() {
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'];
        $path = dirname($_SERVER['REQUEST_URI']);
        return $protocol . '://' . $host . $path . '/mpesa_callback.php';
    }
    
    /**
     * Get transaction by ID
     * @param string $transactionId Transaction ID
     * @return array|false Transaction data or false if not found
     */
    public function getTransaction($transactionId) {
        $query = "SELECT * FROM mpesa_transactions WHERE transaction_id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("s", $transactionId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }
    
    /**
     * Get transactions by order ID
     * @param int $orderId Order ID
     * @return array Array of transactions
     */
    public function getTransactionsByOrder($orderId) {
        $query = "SELECT * FROM mpesa_transactions WHERE order_id = ? ORDER BY created_at DESC";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("i", $orderId);
        $stmt->execute();
        $result = $stmt->get_result();
        $transactions = [];
        while ($row = $result->fetch_assoc()) {
            $transactions[] = $row;
        }
        return $transactions;
    }
}
?> 