<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/helpers.php';

require_role('user');

$tracking_no = sanitize($_GET['t'] ?? '');
$company_id = $_SESSION['company_id'];
$shipment = null;
$history = [];

if (!empty($tracking_no)) {
    // Fetch shipment (Must belong to user's company)
    $stmt = $pdo->prepare("
        SELECT s.*, st.status_name, c.company_name 
        FROM shipments s 
        JOIN shipment_status st ON s.current_status_id = st.id 
        JOIN courier_companies c ON s.company_id = c.id
        WHERE s.tracking_number = ? AND s.company_id = ?
    ");
    $stmt->execute([$tracking_no, $company_id]);
    $shipment = $stmt->fetch();

    if ($shipment) {
        // Fetch history
        $stmt = $pdo->prepare("
            SELECT h.*, st.status_name 
            FROM shipment_tracking_history h
            JOIN shipment_status st ON h.status_id = st.id
            WHERE h.shipment_id = ?
            ORDER BY h.updated_at DESC
        ");
        $stmt->execute([$shipment['id']]);
        $history = $stmt->fetchAll();
    }
}

$page_title = 'Track Shipment - ConsignX';
include '../includes/header.php';
?>

<div class="dashboard-layout no-print">
    <?php include '../includes/sidebar.php'; ?>

    <main class="main-content">
        <?php include '../includes/navbar.php'; ?>

        <div style="max-width: 800px; margin: 0 auto;">
            <div style="text-align: center; margin-bottom: 3rem;">
                <h2 style="font-weight: 700; margin-bottom: 1.5rem;">Track Your Package</h2>
                <form method="GET" class="glass"
                    style="padding: 1.5rem; display: flex; gap: 0.5rem; border-radius: 40px; max-width: 500px; margin: 0 auto;">
                    <input type="text" name="t" class="form-control"
                        placeholder="Enter Tracking Number (e.g. CX-12345678)" value="<?php echo $tracking_no; ?>"
                        style="border-radius: 30px; border: none; background: rgba(0,0,0,0.05);">
                    <button type="submit" class="btn btn-primary"
                        style="border-radius: 30px; padding: 0 1.5rem;">Track</button>
                </form>
            </div>

            <?php if ($tracking_no && !$shipment): ?>
                <div class="glass" style="padding: 2rem; text-align: center; color: var(--danger);">
                    <i data-feather="alert-circle" style="width: 48px; height: 48px; margin-bottom: 1rem;"></i>
                    <p>No shipment found with tracking number: <strong><?php echo $tracking_no; ?></strong> in your company.
                    </p>
                </div>
            <?php elseif ($shipment): ?>
                <!-- Result Section -->
                <div class="glass glass-card"
                    style="margin-bottom: 2rem; display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <p style="font-size: 0.8rem; color: var(--text-muted);">Current Status</p>
                        <h3 style="color: var(--primary);"><?php echo $shipment['status_name']; ?></h3>
                    </div>
                    <div style="text-align: right;">
                        <p style="font-size: 0.8rem; color: var(--text-muted);">Estimated Delivery</p>
                        <p style="font-weight: 700;">
                            <?php echo date('d M, Y', strtotime($shipment['expected_delivery_date'])); ?></p>
                    </div>
                </div>

                <div class="glass glass-card">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
                        <h4>Transit History</h4>
                        <button class="btn" style="background: rgba(0,0,0,0.05);" onclick="window.print()">
                            <i data-feather="printer" style="width: 16px; margin-right: 8px;"></i> Print Details
                        </button>
                    </div>

                    <div class="timeline" style="position: relative; padding-left: 2.5rem; margin-top: 1rem;">
                        <?php foreach ($history as $idx => $event): ?>
                            <div class="timeline-item" style="position: relative; padding-bottom: 2.5rem;">
                                <div
                                    style="position: absolute; left: -2.5rem; top: 0; width: 16px; height: 16px; border-radius: 50%; background: <?php echo $idx === 0 ? 'var(--primary)' : 'var(--glass-border)'; ?>; border: 4px solid var(--glass-bg); z-index: 2; box-shadow: <?php echo $idx === 0 ? '0 0 10px var(--primary)' : 'none'; ?>;">
                                </div>
                                <?php if ($idx < count($history) - 1): ?>
                                    <div
                                        style="position: absolute; left: calc(-2.5rem + 7px); top: 16px; bottom: 0; width: 2px; background: var(--glass-border); z-index: 1;">
                                    </div>
                                <?php endif; ?>

                                <div style="display: flex; justify-content: space-between;">
                                    <div>
                                        <p
                                            style="font-weight: 700; margin-bottom: 0.3rem; color: <?php echo $idx === 0 ? 'var(--primary)' : 'var(--text-main)'; ?>;">
                                            <?php echo $event['status_name']; ?>
                                        </p>
                                        <p style="font-size: 0.9rem; color: var(--text-muted);">
                                            <?php echo sanitize($event['remarks']); ?></p>
                                    </div>
                                    <div style="text-align: right; min-width: 100px;">
                                        <p style="font-size: 0.85rem; font-weight: 600;">
                                            <?php echo date('d M, Y', strtotime($event['updated_at'])); ?></p>
                                        <p style="font-size: 0.75rem; color: var(--text-muted);">
                                            <?php echo date('h:i A', strtotime($event['updated_at'])); ?></p>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </main>
</div>

<style>
    @media print {
        .no-print {
            display: none !important;
        }

        body {
            background: white !important;
        }

        .glass {
            border: 1px solid #eee !important;
            box-shadow: none !important;
            background: white !important;
        }
    }
</style>

<?php include '../includes/footer.php'; ?>