<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/helpers.php';

require_role('agent');

$id = (int)$_GET['id'];
$company_id = $_SESSION['company_id'];

// Fetch shipment details
$stmt = $pdo->prepare("
    SELECT s.*, st.status_name, c.company_name 
    FROM shipments s 
    JOIN shipment_status st ON s.current_status_id = st.id 
    JOIN courier_companies c ON s.company_id = c.id
    WHERE s.id = ? AND s.company_id = ?
");
$stmt->execute([$id, $company_id]);
$shipment = $stmt->fetch();

if (!$shipment) redirect('agent/manage_shipments.php');

// Fetch history
$stmt = $pdo->prepare("
    SELECT h.*, st.status_name, u.full_name as updater_name
    FROM shipment_tracking_history h
    JOIN shipment_status st ON h.status_id = st.id
    JOIN users u ON h.updated_by = u.id
    WHERE h.shipment_id = ?
    ORDER BY h.updated_at DESC
");
$stmt->execute([$id]);
$history = $stmt->fetchAll();

$page_title = 'Shipment Details - ' . $shipment['tracking_number'];
include '../includes/header.php';
?>

<div class="dashboard-layout no-print">
    <?php include '../includes/sidebar.php'; ?>
    
    <main class="main-content">
        <?php include '../includes/navbar.php'; ?>

        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
            <h2>Shipment <span style="color: var(--primary);"><?php echo $shipment['tracking_number']; ?></span></h2>
            <button class="btn btn-primary" onclick="window.print()">
                <i data-feather="printer" style="margin-right:8px"></i> Print Bill
            </button>
        </div>

        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 2rem;">
            <!-- Details Card -->
            <div>
                <div class="glass glass-card" style="margin-bottom: 2rem;">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
                        <div>
                            <h5 style="color: var(--primary); margin-bottom: 1rem;">SENDER</h5>
                            <p style="font-weight: 700;"><?php echo sanitize($shipment['sender_name']); ?></p>
                            <p style="font-size: 0.9rem; color: var(--text-muted);"><?php echo sanitize($shipment['sender_phone']); ?></p>
                            <p style="font-size: 0.9rem;"><?php echo nl2br(sanitize($shipment['sender_address'])); ?></p>
                        </div>
                        <div>
                            <h5 style="color: var(--primary); margin-bottom: 1rem;">RECEIVER</h5>
                            <p style="font-weight: 700;"><?php echo sanitize($shipment['receiver_name']); ?></p>
                            <p style="font-size: 0.9rem; color: var(--text-muted);"><?php echo sanitize($shipment['receiver_phone']); ?></p>
                            <p style="font-size: 0.9rem;"><?php echo nl2br(sanitize($shipment['receiver_address'])); ?></p>
                        </div>
                    </div>
                    
                    <div style="border-top: 1px solid var(--glass-border); margin-top: 2rem; padding-top: 2rem; display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem;">
                        <div>
                            <p style="font-size: 0.8rem; color: var(--text-muted);">Package Weight</p>
                            <p style="font-weight: 600;"><?php echo $shipment['weight']; ?> kg</p>
                        </div>
                        <div>
                            <p style="font-size: 0.8rem; color: var(--text-muted);">Shipment Type</p>
                            <p style="font-weight: 600; text-transform: capitalize;"><?php echo $shipment['shipment_type']; ?></p>
                        </div>
                        <div>
                            <p style="font-size: 0.8rem; color: var(--text-muted);">Expected Delivery</p>
                            <p style="font-weight: 600;"><?php echo date('d M, Y', strtotime($shipment['expected_delivery_date'])); ?></p>
                        </div>
                    </div>
                </div>

                <div class="glass glass-card">
                    <h4 style="margin-bottom: 1.5rem;">Tracking History</h4>
                    <div class="timeline" style="position: relative; padding-left: 2rem;">
                        <?php foreach ($history as $idx => $event): ?>
                        <div class="timeline-item" style="position: relative; padding-bottom: 2rem;">
                            <div style="position: absolute; left: -2rem; top: 0; width: 12px; height: 12px; border-radius: 50%; background: <?php echo $idx === 0 ? 'var(--primary)' : 'var(--text-muted)'; ?>; border: 4px solid var(--glass-bg); z-index: 2;"></div>
                            <?php if ($idx < count($history) - 1): ?>
                            <div style="position: absolute; left: calc(-2rem + 5px); top: 12px; bottom: 0; width: 2px; background: var(--glass-border); z-index: 1;"></div>
                            <?php endif; ?>
                            
                            <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                                <div>
                                    <p style="font-weight: 700; color: <?php echo $idx === 0 ? 'var(--primary)' : 'var(--text-main)'; ?>;"><?php echo $event['status_name']; ?></p>
                                    <p style="font-size: 0.85rem; margin-top: 0.2rem;"><?php echo sanitize($event['remarks']); ?></p>
                                    <p style="font-size: 0.75rem; color: var(--text-muted); margin-top: 0.4rem;">Updated by: <?php echo $event['updater_name']; ?></p>
                                </div>
                                <div style="text-align: right;">
                                    <p style="font-size: 0.8rem; font-weight: 600;"><?php echo date('d M, Y', strtotime($event['updated_at'])); ?></p>
                                    <p style="font-size: 0.75rem; color: var(--text-muted);"><?php echo date('h:i A', strtotime($event['updated_at'])); ?></p>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Total/Summary Side -->
            <div>
                <div class="glass glass-card" style="text-align: center; background: var(--primary); color: white;">
                    <p style="opacity: 0.8; font-size: 0.9rem;">Total Amount</p>
                    <h1 style="font-size: 3rem; margin: 0.5rem 0; color: white;"><?php echo format_price($shipment['price']); ?></h1>
                    <p style="font-size: 0.8rem; opacity: 0.7;">Paid via Cash on Delivery</p>
                </div>
            </div>
        </div>
    </main>
</div>

<!-- PRINT LAYOUT -->
<div class="print-only" style="display: none; padding: 2rem; background: white; color: black; font-family: serif;">
    <div style="display: flex; justify-content: space-between; border-bottom: 2px solid black; padding-bottom: 1rem;">
        <div>
            <h1>ConsignX Bill</h1>
            <p><strong>Courier:</strong> <?php echo sanitize($shipment['company_name']); ?></p>
        </div>
        <div style="text-align: right;">
            <h2>#<?php echo $shipment['tracking_number']; ?></h2>
            <p><strong>Date:</strong> <?php echo date('d M, Y'); ?></p>
        </div>
    </div>
    
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; margin-top: 2rem;">
        <div>
            <h3>Sender</h3>
            <p><?php echo sanitize($shipment['sender_name']); ?><br><?php echo sanitize($shipment['sender_phone']); ?><br><?php echo nl2br(sanitize($shipment['sender_address'])); ?></p>
        </div>
        <div>
            <h3>Receiver</h3>
            <p><?php echo sanitize($shipment['receiver_name']); ?><br><?php echo sanitize($shipment['receiver_phone']); ?><br><?php echo nl2br(sanitize($shipment['receiver_address'])); ?></p>
        </div>
    </div>

    <table style="width: 100%; border-collapse: collapse; margin-top: 3rem;">
        <thead>
            <tr style="border-bottom: 1px solid #ccc;">
                <th style="padding: 1rem; text-align: left;">Description</th>
                <th style="padding: 1rem; text-align: right;">Value</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td style="padding: 1rem;">Weight: <?php echo $shipment['weight']; ?> kg (<?php echo $shipment['shipment_type']; ?>)</td>
                <td style="padding: 1rem; text-align: right;"><?php echo format_price($shipment['price']); ?></td>
            </tr>
            <tr style="border-top: 2px solid black; font-weight: bold;">
                <td style="padding: 1rem;">TOTAL AMOUNT</td>
                <td style="padding: 1rem; text-align: right;"><?php echo format_price($shipment['price']); ?></td>
            </tr>
        </tbody>
    </table>

    <div style="margin-top: 5rem; display: flex; justify-content: space-between;">
        <div style="border-top: 1px solid black; width: 200px; text-align: center; padding-top: 0.5rem;">Agent Signature</div>
        <div style="border-top: 1px solid black; width: 200px; text-align: center; padding-top: 0.5rem;">Customer Signature</div>
    </div>
</div>

<style>
@media print {
    .no-print { display: none !important; }
    .print-only { display: block !important; }
    body { background: white !important; }
}
</style>

<?php include '../includes/footer.php'; ?>
