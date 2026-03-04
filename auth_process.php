<?php
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('login.php');
}

$action = $_POST['action'] ?? '';

// Verify CSRF Token
if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
    $_SESSION['error'] = "Invalid CSRF token. Please try again.";
    redirect($action === 'register' ? 'register.php' : 'login.php');
}

if ($action === 'login') {
    $email = sanitize($_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $_SESSION['error'] = "All fields are required.";
        redirect('login.php');
    }

    try {
        $stmt = $pdo->prepare("
            SELECT u.*, r.role_name 
            FROM users u 
            JOIN roles r ON u.role_id = r.id 
            WHERE u.email = ? AND u.status = 'active'
        ");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            // Login successful
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role_name'];
            $_SESSION['company_id'] = $user['company_id'];
            $_SESSION['full_name'] = $user['full_name'];

            // Redirect based on role
            switch ($user['role_name']) {
                case 'admin':
                    redirect('admin/dashboard.php');
                    break;
                case 'agent':
                    redirect('agent/dashboard.php');
                    break;
                case 'user':
                    redirect('user/dashboard.php');
                    break;
                default:
                    redirect('login.php');
                    break;
            }
        } else {
            $_SESSION['error'] = "Invalid email or password.";
            redirect('login.php');
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = "Authentication error. Please try again later.";
        redirect('login.php');
    }
} elseif ($action === 'register') {
    $full_name = sanitize($_POST['full_name']);
    $email = sanitize($_POST['email']);
    $phone = sanitize($_POST['phone']);
    $company_id = (int) $_POST['company_id'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Validations
    if (empty($full_name) || empty($email) || empty($phone) || empty($company_id) || empty($password)) {
        $_SESSION['error'] = "All fields are required.";
        redirect('register.php');
    }

    if ($password !== $confirm_password) {
        $_SESSION['error'] = "Passwords do not match.";
        redirect('register.php');
    }

    if (strlen($password) < 8) {
        $_SESSION['error'] = "Password must be at least 8 characters.";
        redirect('register.php');
    }

    try {
        // Check if email exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $_SESSION['error'] = "Email already registered.";
            redirect('register.php');
        }

        // Get 'user' role id
        $stmt = $pdo->prepare("SELECT id FROM roles WHERE role_name = 'user'");
        $stmt->execute();
        $role = $stmt->fetch();
        $role_id = $role['id'];

        // Hash password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Insert user
        $stmt = $pdo->prepare("
            INSERT INTO users (role_id, company_id, full_name, email, phone, password, status) 
            VALUES (?, ?, ?, ?, ?, ?, 'active')
        ");
        $stmt->execute([$role_id, $company_id, $full_name, $email, $phone, $hashed_password]);

        $_SESSION['success'] = "Registration successful! Please login.";
        redirect('login.php');
    } catch (PDOException $e) {
        $_SESSION['error'] = "Registration failed. Please try again.";
        redirect('register.php');
    }
}
?>