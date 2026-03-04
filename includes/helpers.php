<?php
/**
 * ConsignX - Core Helpers
 */

/**
 * Sanitize input data
 */
function sanitize($data)
{
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

/**
 * CSRF Token Generation
 */
function generate_csrf_token()
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF Token
 */
function verify_csrf_token($token)
{
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Check if user is logged in
 */
function is_logged_in()
{
    return isset($_SESSION['user_id']);
}

/**
 * Get logged in user role
 */
function get_user_role()
{
    return $_SESSION['role'] ?? null;
}

/**
 * Require specific role to access page
 */
function require_role($allowed_roles)
{
    if (!is_logged_in()) {
        header("Location: " . BASE_URL . "login.php");
        exit;
    }

    if (!is_array($allowed_roles)) {
        $allowed_roles = [$allowed_roles];
    }

    if (!in_array(get_user_role(), $allowed_roles)) {
        header("Location: " . BASE_URL . "unauthorized.php");
        exit;
    }
}

/**
 * Redirect helper
 */
function redirect($path)
{
    header("Location: " . BASE_URL . $path);
    exit;
}

/**
 * Format Currency
 */
function format_price($amount)
{
    return '$' . number_format($amount, 2);
}

/**
 * Generate Tracking Number
 */
function generate_tracking_number()
{
    return 'CX-' . strtoupper(substr(uniqid(), -8));
}
?>