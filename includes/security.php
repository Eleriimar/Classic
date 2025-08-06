<?php
/**
 * Security Utilities for OnlineFood System
 * Enhanced security features for production hosting
 */

class Security {
    
    /**
     * Initialize security settings
     */
    public static function init() {
        // Set secure headers
        self::setSecureHeaders();
        
        // Start secure session
        self::startSecureSession();
        
        // Enable CSRF protection
        self::enableCSRF();
    }
    
    /**
     * Set secure HTTP headers
     */
    public static function setSecureHeaders() {
        // Prevent clickjacking
        header('X-Frame-Options: DENY');
        
        // Prevent MIME type sniffing
        header('X-Content-Type-Options: nosniff');
        
        // Enable XSS protection
        header('X-XSS-Protection: 1; mode=block');
        
        // Strict transport security (HTTPS only)
        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
            header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
        }
        
        // Content security policy
        header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' https://maps.googleapis.com https://code.jquery.com; style-src 'self' 'unsafe-inline'; img-src 'self' data: https:; connect-src 'self'");
    }
    
    /**
     * Start secure session
     */
    public static function startSecureSession() {
        if (session_status() === PHP_SESSION_NONE) {
            // Set secure session parameters
            ini_set('session.cookie_httponly', 1);
            ini_set('session.cookie_secure', isset($_SERVER['HTTPS']));
            ini_set('session.use_strict_mode', 1);
            ini_set('session.cookie_samesite', 'Strict');
            
            session_start();
            
            // Regenerate session ID periodically
            if (!isset($_SESSION['last_regeneration'])) {
                session_regenerate_id(true);
                $_SESSION['last_regeneration'] = time();
            } elseif (time() - $_SESSION['last_regeneration'] > 1800) { // 30 minutes
                session_regenerate_id(true);
                $_SESSION['last_regeneration'] = time();
            }
        }
    }
    
    /**
     * Enable CSRF protection
     */
    public static function enableCSRF() {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
    }
    
    /**
     * Generate CSRF token
     * @return string CSRF token
     */
    public static function getCSRFToken() {
        return $_SESSION['csrf_token'];
    }
    
    /**
     * Verify CSRF token
     * @param string $token Token to verify
     * @return bool True if valid
     */
    public static function verifyCSRFToken($token) {
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }
    
    /**
     * Sanitize input data
     * @param mixed $data Data to sanitize
     * @return mixed Sanitized data
     */
    public static function sanitizeInput($data) {
        if (is_array($data)) {
            return array_map([self::class, 'sanitizeInput'], $data);
        }
        
        return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
    }
    
    /**
     * Validate email address
     * @param string $email Email to validate
     * @return bool True if valid
     */
    public static function validateEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
    
    /**
     * Validate phone number (Kenyan format)
     * @param string $phone Phone number to validate
     * @return bool True if valid
     */
    public static function validatePhone($phone) {
        // Remove any non-digit characters
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        // Check if it's a valid Kenyan phone number
        return preg_match('/^(254|0)?[17]\d{8}$/', $phone);
    }
    
    /**
     * Hash password securely
     * @param string $password Password to hash
     * @return string Hashed password
     */
    public static function hashPassword($password) {
        return password_hash($password, PASSWORD_ARGON2ID, [
            'memory_cost' => 65536,
            'time_cost' => 4,
            'threads' => 3
        ]);
    }
    
    /**
     * Verify password
     * @param string $password Password to verify
     * @param string $hash Stored hash
     * @return bool True if valid
     */
    public static function verifyPassword($password, $hash) {
        return password_verify($password, $hash);
    }
    
    /**
     * Rate limiting
     * @param string $key Rate limit key (e.g., IP address)
     * @param int $max_attempts Maximum attempts allowed
     * @param int $time_window Time window in seconds
     * @return bool True if allowed
     */
    public static function checkRateLimit($key, $max_attempts = 5, $time_window = 300) {
        $attempts_file = sys_get_temp_dir() . '/rate_limit_' . md5($key) . '.txt';
        
        if (file_exists($attempts_file)) {
            $attempts = json_decode(file_get_contents($attempts_file), true);
            
            // Remove old attempts
            $attempts = array_filter($attempts, function($timestamp) use ($time_window) {
                return $timestamp > (time() - $time_window);
            });
            
            if (count($attempts) >= $max_attempts) {
                return false;
            }
        } else {
            $attempts = [];
        }
        
        $attempts[] = time();
        file_put_contents($attempts_file, json_encode($attempts));
        
        return true;
    }
    
    /**
     * Log security events
     * @param string $event Event description
     * @param array $data Additional data
     */
    public static function logSecurityEvent($event, $data = []) {
        $log_entry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
            'event' => $event,
            'data' => $data
        ];
        
        $log_file = __DIR__ . '/../logs/security.log';
        $log_dir = dirname($log_file);
        
        if (!is_dir($log_dir)) {
            mkdir($log_dir, 0755, true);
        }
        
        file_put_contents($log_file, json_encode($log_entry) . "\n", FILE_APPEND | LOCK_EX);
    }
    
    /**
     * Validate file upload
     * @param array $file $_FILES array element
     * @param array $allowed_types Allowed MIME types
     * @param int $max_size Maximum file size in bytes
     * @return array Validation result
     */
    public static function validateFileUpload($file, $allowed_types = ['image/jpeg', 'image/png', 'image/gif'], $max_size = 5242880) {
        $result = ['valid' => false, 'message' => ''];
        
        if (!isset($file['error']) || is_array($file['error'])) {
            $result['message'] = 'Invalid file parameter';
            return $result;
        }
        
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $result['message'] = 'File upload error: ' . $file['error'];
            return $result;
        }
        
        if ($file['size'] > $max_size) {
            $result['message'] = 'File too large';
            return $result;
        }
        
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime_type = $finfo->file($file['tmp_name']);
        
        if (!in_array($mime_type, $allowed_types)) {
            $result['message'] = 'Invalid file type';
            return $result;
        }
        
        $result['valid'] = true;
        return $result;
    }
    
    /**
     * Generate secure random string
     * @param int $length Length of string
     * @return string Random string
     */
    public static function generateRandomString($length = 32) {
        return bin2hex(random_bytes($length / 2));
    }
    
    /**
     * Encrypt sensitive data
     * @param string $data Data to encrypt
     * @param string $key Encryption key
     * @return string Encrypted data
     */
    public static function encrypt($data, $key) {
        $cipher = 'aes-256-gcm';
        $ivlen = openssl_cipher_iv_length($cipher);
        $iv = openssl_random_pseudo_bytes($ivlen);
        $tag = '';
        
        $encrypted = openssl_encrypt($data, $cipher, $key, OPENSSL_RAW_DATA, $iv, $tag);
        
        return base64_encode($iv . $tag . $encrypted);
    }
    
    /**
     * Decrypt sensitive data
     * @param string $encrypted_data Encrypted data
     * @param string $key Decryption key
     * @return string|false Decrypted data or false on failure
     */
    public static function decrypt($encrypted_data, $key) {
        $cipher = 'aes-256-gcm';
        $ivlen = openssl_cipher_iv_length($cipher);
        $taglen = 16;
        
        $data = base64_decode($encrypted_data);
        $iv = substr($data, 0, $ivlen);
        $tag = substr($data, $ivlen, $taglen);
        $encrypted = substr($data, $ivlen + $taglen);
        
        return openssl_decrypt($encrypted, $cipher, $key, OPENSSL_RAW_DATA, $iv, $tag);
    }
}

// Initialize security
Security::init();
?> 