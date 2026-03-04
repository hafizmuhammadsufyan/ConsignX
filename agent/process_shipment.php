<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/helpers.php';

require_role('agent');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('agent/manage_shipments.php');
}

$action = $_POST['action'] ?? '';
$company_id = $_SESSION['company_id'];
$user_id = $_SESSION['user_id'];

// Verify CSRF
if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
    $_SESSION['error'] = "Security validation failed.";
    redirect('agent/manage_shipments.php');
}

if ($action === 'create') {
    $tracking_number = generate_tracking_number();
    $sender_name = sanitize($_POST['sender_name']);
    $sender_phone = sanitize($_POST['sender_phone']);
    $sender_address = sanitize($_POST['sender_address']);
    $receiver_name = sanitize($_POST['receiver_name']);
    $receiver_phone = sanitize($_POST['receiver_phone']);
    $receiver_address = sanitize($_POST['receiver_address']);
    $type = $_POST['shipment_type'];
    $weight = (float) $_POST['weight'];
    $price = (float) $_POST['price'];
    $expected_date = $_POST['expected_delivery_date'];

    try {
        $pdo->beginTransaction();

        // 1. Get initial status ID (Pending)
        $stmt = $pdo->prepare("SELECT id FROM shipment_status WHERE status_name = 'Pending'");
        $stmt->execute();
        $status = $stmt->fetch();
        $status_id = $status['id'];

        // 2. Insert Shipment
        $stmt = $pdo->prepare("
            INSERT INTO shipments 
            (company_id, tracking_number, sender_name, sender_phone, sender_address, receiver_name, receiver_phone, receiver_address, created_by, shipment_type, weight, price, current_status_id, expected_delivery_date) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $company_id,
            $tracking_number,
            $sender_name,
            $sender_phone,
            $sender_address,
            $receiver_name,
            $receiver_phone,
            $receiver_address,
            $user_id,
            $type,
            $weight,
            $price,
            $status_id,
            $expected_date
        ]);
        $shipment_id = $pdo->lastInsertId();

        // 3. Add to History
        $stmt = $pdo->prepare("INSERT INTO shipment_tracking_history (shipment_id, status_id, updated_by, remarks) VALUES (?, ?, ?, ?)");
        $stmt->execute([$shipment_id, $status_id, $user_id, 'Shipment created and pending pickup.']);

        // 4. Simulate SMS
        $stmt = $pdo->prepare("INSERT INTO sms_logs (shipment_id, company_id, sent_to, message, sent_by) VALUES (?, ?, ?, ?, ?)");
        $sms_msg = "Hello $sender_name, your shipment $tracking_number has been created. Track it on ConsignX!";
        $stmt->execute([$shipment_id, $company_id, $sender_phone, $sms_msg, $user_id]);

        $pdo->commit();
        $_SESSION['success'] = "Shipment created: $tracking_number";
        redirect('agent/manage_shipments.php');
    } catch (PDOException $e) {
        $pdo->rollBack();
        $_SESSION['error'] = "Error: " . $e->getMessage();
        redirect('agent/create_shipment.php');
    }
} elseif ($action === 'update_status') {
    $shipment_id = (int) $_POST['shipment_id'];
    $status_id = (int) $_POST['status_id'];
    $remarks = sanitize($_POST['remarks']);

    try {
        $pdo->beginTransaction();

        // Update Shipment Status
        $stmt = $pdo->prepare("UPDATE shipments SET current_status_id = ? WHERE id = ? AND company_id = ?");
        $stmt->execute([$status_id, $shipment_id, $company_id]);

        // Add to History
        $stmt = $pdo->prepare("INSERT INTO shipment_tracking_history (shipment_id, status_id, updated_by, remarks) VALUES (?, ?, ?, ?)");
        $stmt->execute([$shipment_id, $status_id, $user_id, $remarks]);

        // Simulate SMS if needed (e.g. for Out for Delivery or Delivered)
        $stmt = $pdo->prepare("SELECT s.receiver_phone, st.status_name FROM shipments s JOIN shipment_status st ON st.id = ? WHERE s.id = ?");
        $stmt->execute([$status_id, $shipment_id]);
        $info = $stmt->fetch();

        $sms_msg = "Update: Your shipment status is now " . $info['status_name'];
        $stmt = $pdo->prepare("INSERT INTO sms_logs (shipment_id, company_id, sent_to, message, sent_by) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$shipment_id, $company_id, $info['receiver_phone'], $sms_msg, $user_id]);

        $pdo->commit();
        $_SESSION['success'] = "Shipment status updated!";
    } catch (PDOException $e) {
        $pdo->rollBack();
        $_SESSION['error'] = "Update failed.";
    }
    redirect('agent/manage_shipments.php');
}
?>