<?php
/**
 * ConsignX - Global Configuration
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Error reporting - disable for production, enable for development
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Timezone
date_default_timezone_set('Asia/Karachi'); // Adjust as needed

// Base URL (Update this for your environment)
define('BASE_URL', 'http://localhost/ConsignX/');

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'consignx_db');
define('DB_USER', 'root');
define('DB_PASS', '');

// Security Salts
define('APP_SECRET', 'c0nsignX_S3cr3t_2024');

/**
 * Regenerate session ID periodically for security
 */
if (!isset($_SESSION['last_regeneration'])) {
    session_regenerate_id(true);
    $_SESSION['last_regeneration'] = time();
} else {
    $interval = 60 * 30; // 30 minutes
    if (time() - $_SESSION['last_regeneration'] > $interval) {
        session_regenerate_id(true);
        $_SESSION['last_regeneration'] = time();
    }
}
?>