<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/helpers.php';

require_role('user');

$user_email = $_SESSION['email'] ?? '';
$user_phone = $_SESSION['phone'] ?? ''; // Need to ensure phone is in session
$company_id = $_SESSION['company_id'];

// Get 'phone' if not in session (from DB)
if (empty($user_phone)) {
    $stmt = $pdo->prepare("SELECT phone, email FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $u = $stmt->fetch();
    $_SESSION['phone'] = $u['phone'];
    $_SESSION['email'] = $u['email'];
    $user_phone = $u['phone'];
}

// Fetch shipments where user is sender or receiver (within their company)
$stmt = $pdo->prepare("
    SELECT s.*, st.status_name 
    FROM shipments s 
    JOIN shipment_status st ON s.current_status_id = st.id 
    WHERE s.company_id = ? AND (s.sender_phone = ? OR s.receiver_phone = ?)
    ORDER BY s.created_at DESC
");
$stmt->execute([$company_id, $user_phone, $user_phone]);
$shipments = $stmt->fetchAll();

$page_title = 'My Shipments';
include '../includes/header.php';
?>

<div class="dashboard-layout">
    <?php include '../includes/sidebar.php'; ?>

    <main class="main-content">
        <?php include '../includes/navbar.php'; ?>

        <div style="margin-bottom: 2rem;">
            <h2 style="font-weight: 700; margin-bottom: 0.5rem;">Welcome, <?php echo $_SESSION['full_name']; ?></h2>
            <p style="color: var(--text-muted); font-size: 0.9rem;">Manage and track your packages below.</p>
        </div>

        <div class="glass glass-card" style="margin-bottom: 2rem;">
            <div style="display: flex; gap: 3rem; align-items: center;">
                <div>
                    <p style="font-size: 0.8rem; color: var(--text-muted); margin-bottom: 0.2rem;">Total Packages</p>
                    <p style="font-size: 1.5rem; font-weight: 700; color: var(--primary);">
                        <?php echo count($shipments); ?></p>
                </div>
                <div style="width: 1px; height: 40px; background: var(--glass-border);"></div>
                <div>
                    <p style="font-size: 0.8rem; color: var(--text-muted); margin-bottom: 0.2rem;">Active Tracking</p>
                    <?php
                    $active = array_filter($shipments, fn($sh) => !in_array($sh['status_name'], ['Delivered', 'Cancelled']));
                    ?>
                    <p style="font-size: 1.5rem; font-weight: 700; color: var(--info);"><?php echo count($active); ?>
                    </p>
                </div>
            </div>
        </div>

        <!-- My Shipments Table -->
        <div class="glass glass-card">
            <h3 style="margin-bottom: 1.5rem;">Shipment History</h3>
            <div class="table-responsive">
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="text-align: left; border-bottom: 1px solid var(--glass-border);">
                            <th style="padding: 1rem 0.5rem; color: var(--text-muted); font-weight: 500;">Tracking #
                            </th>
                            <th style="padding: 1rem 0.5rem; color: var(--text-muted); font-weight: 500;">
                                Sender/Receiver</th>
                            <th style="padding: 1rem 0.5rem; color: var(--text-muted); font-weight: 500;">Status</th>
                            <th style="padding: 1rem 0.5rem; color: var(--text-muted); font-weight: 500;">Est. Delivery
                            </th>
                            <th style="padding: 1rem 0.5rem; text-align: right;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($shipments as $sh): ?>
                            <tr style="border-bottom: 1px solid var(--glass-border);">
                                <td style="padding: 1rem 0.5rem; font-weight: 600;"><?php echo $sh['tracking_number']; ?>
                                </td>
                                <td style="padding: 1rem 0.5rem;">
                                    <div style="font-size: 0.9rem;">
                                        <strong>From:</strong> <?php echo sanitize($sh['sender_name']); ?><br>
                                        <strong>To:</strong> <?php echo sanitize($sh['receiver_name']); ?>
                                    </div>
                                </td>
                                <td style="padding: 1rem 0.5rem;">
                                    <span
                                        style="background: rgba(79, 70, 229, 0.1); color: var(--primary); padding: 4px 10px; border-radius: 12px; font-size: 0.8rem;">
                                        <?php echo $sh['status_name']; ?>
                                    </span>
                                </td>
                                <td style="padding: 1rem 0.5rem; font-size: 0.9rem;">
                                    <?php echo date('d M, Y', strtotime($sh['expected_delivery_date'])); ?></td>
                                <td style="padding: 1rem 0.5rem; text-align: right;">
                                    <a href="track.php?t=<?php echo $sh['tracking_number']; ?>" class="btn btn-primary"
                                        style="padding: 0.5rem 1rem; font-size: 0.8rem;">Track</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($shipments)): ?>
                            <tr>
                                <td colspan="5" style="padding: 3rem; text-align: center; color: var(--text-muted);">
                                    No shipments found associated with your phone number (<?php echo $user_phone; ?>).
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>

<?php include '../includes/footer.php'; ?>