<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/helpers.php';

require_role('admin');

// Filtering logic
$where = [];
$params = [];

if (!empty($_GET['status_id'])) {
    $where[] = "s.current_status_id = ?";
    $params[] = $_GET['status_id'];
}

if (!empty($_GET['company_id'])) {
    $where[] = "s.company_id = ?";
    $params[] = $_GET['company_id'];
}

if (!empty($_GET['q'])) {
    $q = "%" . $_GET['q'] . "%";
    $where[] = "(s.tracking_number LIKE ? OR s.sender_name LIKE ? OR s.receiver_name LIKE ?)";
    $params[] = $q;
    $params[] = $q;
    $params[] = $q;
}

$where_sql = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";

// Fetch shipments with JOINs
$query = "
    SELECT s.*, c.company_name, st.status_name 
    FROM shipments s 
    JOIN courier_companies c ON s.company_id = c.id 
    JOIN shipment_status st ON s.current_status_id = st.id 
    $where_sql 
    ORDER BY s.created_at DESC
";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$shipments = $stmt->fetchAll();

// Fetch statuses and companies for filters
$statuses = $pdo->query("SELECT * FROM shipment_status")->fetchAll();
$companies = $pdo->query("SELECT id, company_name FROM courier_companies")->fetchAll();

$page_title = 'Global Shipments';
include '../includes/header.php';
?>

<div class="dashboard-layout">
    <?php include '../includes/sidebar.php'; ?>

    <main class="main-content">
        <?php include '../includes/navbar.php'; ?>

        <div style="margin-bottom: 2rem;">
            <h2 style="font-weight: 700; margin-bottom: 1.5rem;">All Shipments</h2>

            <!-- Filters -->
            <div class="glass"
                style="padding: 1.5rem; display: flex; flex-wrap: wrap; gap: 1rem; align-items: flex-end;">
                <form method="GET" style="display: contents;">
                    <div style="flex: 1; min-width: 200px;">
                        <label
                            style="font-size: 0.8rem; color: var(--text-muted); display: block; margin-bottom: 0.3rem;">Search</label>
                        <input type="text" name="q" class="form-control" placeholder="Tracking #, Name..."
                            value="<?php echo $_GET['q'] ?? ''; ?>">
                    </div>
                    <div style="flex: 1; min-width: 150px;">
                        <label
                            style="font-size: 0.8rem; color: var(--text-muted); display: block; margin-bottom: 0.3rem;">Company</label>
                        <select name="company_id" class="form-control">
                            <option value="">All Companies</option>
                            <?php foreach ($companies as $comp): ?>
                                <option value="<?php echo $comp['id']; ?>" <?php echo ($_GET['company_id'] ?? '') == $comp['id'] ? 'selected' : ''; ?>>
                                    <?php echo sanitize($comp['company_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div style="flex: 1; min-width: 150px;">
                        <label
                            style="font-size: 0.8rem; color: var(--text-muted); display: block; margin-bottom: 0.3rem;">Status</label>
                        <select name="status_id" class="form-control">
                            <option value="">All Statuses</option>
                            <?php foreach ($statuses as $st): ?>
                                <option value="<?php echo $st['id']; ?>" <?php echo ($_GET['status_id'] ?? '') == $st['id'] ? 'selected' : ''; ?>>
                                    <?php echo sanitize($st['status_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div style="display: flex; gap: 0.5rem;">
                        <button type="submit" class="btn btn-primary" style="padding: 0.75rem;"><i
                                data-feather="filter"></i></button>
                        <a href="shipments.php" class="btn"
                            style="padding: 0.75rem; background: rgba(100, 116, 139, 0.1);"><i
                                data-feather="rotate-ccw"></i></a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Shipments Table -->
        <div class="glass glass-card">
            <div class="table-responsive">
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="text-align: left; border-bottom: 1px solid var(--glass-border);">
                            <th style="padding: 1rem; color: var(--text-muted); font-weight: 500;">Tracking #</th>
                            <th style="padding: 1rem; color: var(--text-muted); font-weight: 500;">Company</th>
                            <th style="padding: 1rem; color: var(--text-muted); font-weight: 500;">Sender</th>
                            <th style="padding: 1rem; color: var(--text-muted); font-weight: 500;">Receiver</th>
                            <th style="padding: 1rem; color: var(--text-muted); font-weight: 500;">Status</th>
                            <th style="padding: 1rem; color: var(--text-muted); font-weight: 500;">Price</th>
                            <th style="padding: 1rem; color: var(--text-muted); font-weight: 500;">Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($shipments as $sh): ?>
                            <tr style="border-bottom: 1px solid var(--glass-border);">
                                <td style="padding: 1rem; font-weight: 600;"><?php echo $sh['tracking_number']; ?></td>
                                <td style="padding: 1rem;"><?php echo sanitize($sh['company_name']); ?></td>
                                <td style="padding: 1rem;"><?php echo sanitize($sh['sender_name']); ?></td>
                                <td style="padding: 1rem;"><?php echo sanitize($sh['receiver_name']); ?></td>
                                <td style="padding: 1rem;">
                                    <?php
                                    $status_color = 'var(--info)';
                                    if ($sh['status_name'] == 'Delivered')
                                        $status_color = 'var(--success)';
                                    if ($sh['status_name'] == 'Cancelled')
                                        $status_color = 'var(--danger)';
                                    if ($sh['status_name'] == 'In Transit')
                                        $status_color = 'var(--primary)';
                                    ?>
                                    <span
                                        style="background: <?php echo $status_color; ?>1a; color: <?php echo $status_color; ?>; padding: 4px 10px; border-radius: 12px; font-size: 0.8rem;">
                                        <?php echo $sh['status_name']; ?>
                                    </span>
                                </td>
                                <td style="padding: 1rem; font-weight: 600;"><?php echo format_price($sh['price']); ?></td>
                                <td style="padding: 1rem; font-size: 0.9rem; color: var(--text-muted);">
                                    <?php echo date('d M, Y', strtotime($sh['created_at'])); ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($shipments)): ?>
                            <tr>
                                <td colspan="7" style="padding: 3rem; text-align: center; color: var(--text-muted);">
                                    <i data-feather="package"
                                        style="width: 48px; height: 48px; margin-bottom: 1rem; opacity: 0.5;"></i>
                                    <p>No shipments found matching the filters.</p>
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