<?php
/**
 * ConsignX - Entry Point
 */
require_once 'includes/config.php';
require_once 'includes/helpers.php';

if (is_logged_in()) {
    $role = get_user_role();
    switch ($role) {
        case 'admin':
            header("Location: admin/dashboard.php");
            break;
        case 'agent':
            header("Location: agent/dashboard.php");
            break;
        case 'user':
            header("Location: user/dashboard.php");
            break;
        default:
            header("Location: login.php");
            break;
    }
    exit;
} else {
    header("Location: login.php");
    exit;
}
?>