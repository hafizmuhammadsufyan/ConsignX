<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/helpers.php';

require_role('agent');

$company_id = $_SESSION['company_id'];

// Filter Logic
$where = ["s.company_id = ?"];
$params = [$company_id];

if (!empty($_GET['q'])) {
    $q = "%" . $_GET['q'] . "%";
    $where[] = "(s.tracking_number LIKE ? OR s.sender_name LIKE ? OR s.receiver_name LIKE ?)";
    $params[] = $q;
    $params[] = $q;
    $params[] = $q;
}

$where_sql = "WHERE " . implode(" AND ", $where);

$query = "
    SELECT s.*, st.status_name 
    FROM shipments s 
    JOIN shipment_status st ON s.current_status_id = st.id 
    $where_sql 
    ORDER BY s.created_at DESC
";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$shipments = $stmt->fetchAll();

$statuses = $pdo->query("SELECT * FROM shipment_status")->fetchAll();

$page_title = 'Manage Shipments';
include '../includes/header.php';
?>

<div class="dashboard-layout">
    <?php include '../includes/sidebar.php'; ?>

    <main class="main-content">
        <?php include '../includes/navbar.php'; ?>

        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
            <h2 style="font-weight: 700;">Company Shipments</h2>
            <form method="GET" style="display: flex; gap: 0.5rem;">
                <input type="text" name="q" class="form-control" placeholder="Search..."
                    value="<?php echo $_GET['q'] ?? ''; ?>" style="width: 250px;">
                <button type="submit" class="btn btn-primary"><i data-feather="search"></i></button>
            </form>
        </div>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="glass"
                style="padding: 1rem; border-left: 4px solid var(--success); margin-bottom: 1.5rem; color: var(--success); background: rgba(16, 185, 129, 0.1);">
                <?php echo $_SESSION['success'];
                unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>

        <div class="glass glass-card">
            <div class="table-responsive">
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="text-align: left; border-bottom: 1px solid var(--glass-border);">
                            <th style="padding: 1rem;">Tracking #</th>
                            <th style="padding: 1rem;">Receiver</th>
                            <th style="padding: 1rem;">Dest. Address</th>
                            <th style="padding: 1rem;">Status</th>
                            <th style="padding: 1rem;">Date</th>
                            <th style="padding: 1rem; text-align: right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($shipments as $sh): ?>
                            <tr style="border-bottom: 1px solid var(--glass-border);">
                                <td style="padding: 1rem; font-weight: 600;"><?php echo $sh['tracking_number']; ?></td>
                                <td style="padding: 1rem;"><?php echo sanitize($sh['receiver_name']); ?></td>
                                <td style="padding: 1rem; font-size: 0.85rem; max-width: 200px;">
                                    <?php echo sanitize($sh['receiver_address']); ?></td>
                                <td style="padding: 1rem;">
                                    <span
                                        style="background: rgba(79, 70, 229, 0.1); color: var(--primary); padding: 4px 10px; border-radius: 12px; font-size: 0.8rem;">
                                        <?php echo $sh['status_name']; ?>
                                    </span>
                                </td>
                                <td style="padding: 1rem; font-size: 0.9rem;">
                                    <?php echo date('d M, Y', strtotime($sh['created_at'])); ?></td>
                                <td style="padding: 1rem; text-align: right;">
                                    <div style="display: flex; gap: 8px; justify-content: flex-end;">
                                        <button class="btn"
                                            style="padding: 6px; background: rgba(59, 130, 246, 0.1); color: var(--info);"
                                            title="Update Status"
                                            onclick="showUpdateModal(<?php echo $sh['id']; ?>, '<?php echo $sh['tracking_number']; ?>')">
                                            <i data-feather="refresh-cw" style="width: 16px;"></i>
                                        </button>
                                        <a href="shipment_details.php?id=<?php echo $sh['id']; ?>" class="btn"
                                            style="padding: 6px; background: rgba(100, 116, 139, 0.1); color: var(--text-main);"
                                            title="Details">
                                            <i data-feather="eye" style="width: 16px;"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>

<!-- Status Update Modal -->
<div id="updateStatusModal" class="modal-overlay"
    style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); backdrop-filter: blur(4px); z-index: 1000; align-items: center; justify-content: center;">
    <div class="glass" style="width: 100%; max-width: 450px; padding: 2.5rem; position: relative;">
        <button onclick="hideModal()"
            style="position: absolute; top: 1.5rem; right: 1.5rem; background: none; border: none; cursor: pointer; color: var(--text-muted);">
            <i data-feather="x"></i>
        </button>
        <h3 style="margin-bottom: 1.5rem;">Update Status: <span id="modalTrackingNo"
                style="color: var(--primary);"></span></h3>
        <form action="process_shipment.php" method="POST">
            <input type="hidden" name="action" value="update_status">
            <input type="hidden" name="shipment_id" id="modalShipmentId">
            <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">

            <div class="form-group">
                <label style="display:block; margin-bottom: 0.5rem; font-size: 0.9rem;">New Status</label>
                <select name="status_id" class="form-control" required>
                    <?php foreach ($statuses as $st): ?>
                        <option value="<?php echo $st['id']; ?>"><?php echo $st['status_name']; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label style="display:block; margin-bottom: 0.5rem; font-size: 0.9rem;">Remarks / Location</label>
                <textarea name="remarks" class="form-control" rows="3" placeholder="Package reached sorting center..."
                    required></textarea>
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 1rem;">Update History</button>
        </form>
    </div>
</div>

<script>
    function showUpdateModal(id, tracking) {
        document.getElementById('modalShipmentId').value = id;
        document.getElementById('modalTrackingNo').innerText = tracking;
        document.getElementById('updateStatusModal').style.display = 'flex';
        feather.replace();
    }
    function hideModal() {
        document.getElementById('updateStatusModal').style.display = 'none';
    }
</script>

<?php include '../includes/footer.php'; ?>