<?php
/**
 * Production Configuration for OnlineFood System
 * Secure settings for hosting environment
 */

// Database Configuration
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');
define('DB_NAME', getenv('DB_NAME') ?: 'onlinefoodphp');

// Security Configuration
define('ENCRYPTION_KEY', getenv('ENCRYPTION_KEY') ?: 'your-secure-encryption-key-here');
define('SESSION_SECRET', getenv('SESSION_SECRET') ?: 'your-session-secret-key-here');

// M-Pesa Configuration
define('MPESA_CONSUMER_KEY', getenv('MPESA_CONSUMER_KEY') ?: '');
define('MPESA_CONSUMER_SECRET', getenv('MPESA_CONSUMER_SECRET') ?: '');
define('MPESA_PASSKEY', getenv('MPESA_PASSKEY') ?: '');
define('MPESA_ENVIRONMENT', getenv('MPESA_ENVIRONMENT') ?: 'sandbox');

// Google Maps Configuration
define('GOOGLE_MAPS_API_KEY', getenv('GOOGLE_MAPS_API_KEY') ?: '');

// Application Configuration
define('APP_NAME', 'OnlineFood');
define('APP_URL', getenv('APP_URL') ?: 'http://localhost');
define('APP_ENV', getenv('APP_ENV') ?: 'production');

// Error Reporting
if (APP_ENV === 'production') {
    error_reporting(0);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
    ini_set('error_log', __DIR__ . '/../logs/error.log');
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
}

// Security Settings
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', isset($_SERVER['HTTPS']));
ini_set('session.use_strict_mode', 1);
ini_set('session.cookie_samesite', 'Strict');

// File Upload Settings
ini_set('upload_max_filesize', '10M');
ini_set('post_max_size', '10M');
ini_set('max_execution_time', 300);
ini_set('memory_limit', '256M');

// Timezone
date_default_timezone_set('Africa/Nairobi');

// SSL/TLS Settings
if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
    // Force HTTPS
    if (!isset($_SERVER['HTTP_X_FORWARDED_PROTO']) || $_SERVER['HTTP_X_FORWARDED_PROTO'] !== 'https') {
        header('Location: https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
        exit();
    }
}

// Rate Limiting Settings
define('RATE_LIMIT_MAX_ATTEMPTS', 5);
define('RATE_LIMIT_TIME_WINDOW', 300); // 5 minutes

// Logging Configuration
define('LOG_DIR', __DIR__ . '/../logs');
define('SECURITY_LOG', LOG_DIR . '/security.log');
define('ERROR_LOG', LOG_DIR . '/error.log');
define('ACCESS_LOG', LOG_DIR . '/access.log');

// Create log directory if it doesn't exist
if (!is_dir(LOG_DIR)) {
    mkdir(LOG_DIR, 0755, true);
}

// Custom error handler for production
if (APP_ENV === 'production') {
    set_error_handler(function($severity, $message, $file, $line) {
        if (!(error_reporting() & $severity)) {
            return;
        }
        
        $error = [
            'timestamp' => date('Y-m-d H:i:s'),
            'severity' => $severity,
            'message' => $message,
            'file' => $file,
            'line' => $line,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
        ];
        
        file_put_contents(ERROR_LOG, json_encode($error) . "\n", FILE_APPEND | LOCK_EX);
        
        // Don't execute PHP internal error handler
        return true;
    });
}

// Access logging
function logAccess() {
    $access = [
        'timestamp' => date('Y-m-d H:i:s'),
        'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
        'method' => $_SERVER['REQUEST_METHOD'] ?? 'unknown',
        'uri' => $_SERVER['REQUEST_URI'] ?? 'unknown',
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
        'referer' => $_SERVER['HTTP_REFERER'] ?? 'unknown'
    ];
    
    file_put_contents(ACCESS_LOG, json_encode($access) . "\n", FILE_APPEND | LOCK_EX);
}

// Log access for all requests
logAccess();

// Include security utilities
require_once __DIR__ . '/../includes/security.php';
?> 