<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/helpers.php';

require_role('admin');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('admin/companies.php');
}

$action = $_POST['action'] ?? '';

// Verify CSRF
if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
    $_SESSION['error'] = "Security validation failed.";
    redirect('admin/companies.php');
}

if ($action === 'add') {
    $name = sanitize($_POST['company_name']);
    $email = sanitize($_POST['company_email']);
    $phone = sanitize($_POST['company_phone']);
    $address = sanitize($_POST['company_address']);
    $agent_pass = $_POST['agent_password'];

    if (empty($name) || empty($email) || empty($agent_pass)) {
        $_SESSION['error'] = "Name, Email, and Agent Password are required.";
        redirect('admin/companies.php');
    }

    try {
        $pdo->beginTransaction();

        // 1. Insert Company
        $stmt = $pdo->prepare("INSERT INTO courier_companies (company_name, company_email, company_phone, company_address) VALUES (?, ?, ?, ?)");
        $stmt->execute([$name, $email, $phone, $address]);
        $company_id = $pdo->lastInsertId();

        // 2. Create Agent Account
        $stmt = $pdo->prepare("SELECT id FROM roles WHERE role_name = 'agent'");
        $stmt->execute();
        $role = $stmt->fetch();
        $role_id = $role['id'];

        $hashed_pass = password_hash($agent_pass, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (role_id, company_id, full_name, email, phone, password) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$role_id, $company_id, $name . ' Agent', $email, $phone, $hashed_pass]);

        $pdo->commit();
        $_SESSION['success'] = "Company and Agent account created successfully.";
    } catch (PDOException $e) {
        $pdo->rollBack();
        $_SESSION['error'] = "Error: " . $e->getMessage();
    }
    redirect('admin/companies.php');
} elseif ($action === 'edit') {
    $id = (int) $_POST['company_id'];
    $name = sanitize($_POST['company_name']);
    $email = sanitize($_POST['company_email']);
    $phone = sanitize($_POST['company_phone']);
    $address = sanitize($_POST['company_address']);

    try {
        $stmt = $pdo->prepare("UPDATE courier_companies SET company_name = ?, company_email = ?, company_phone = ?, company_address = ? WHERE id = ?");
        $stmt->execute([$name, $email, $phone, $address, $id]);
        $_SESSION['success'] = "Company updated successfully.";
    } catch (PDOException $e) {
        $_SESSION['error'] = "Error updating company.";
    }
    redirect('admin/companies.php');
} elseif ($action === 'toggle_status') {
    $id = (int) $_POST['company_id'];

    try {
        $stmt = $pdo->prepare("UPDATE courier_companies SET status = IF(status='active', 'inactive', 'active') WHERE id = ?");
        $stmt->execute([$id]);
        $_SESSION['success'] = "Status updated.";
    } catch (PDOException $e) {
        $_SESSION['error'] = "Error updating status.";
    }
    redirect('admin/companies.php');
}
?>