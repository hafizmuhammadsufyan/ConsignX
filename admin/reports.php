<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/helpers.php';

require_role('admin');

// Date Range logic
$start_date = $_GET['start_date'] ?? date('Y-m-01');
$end_date = $_GET['end_date'] ?? date('Y-m-d');

// Pivot Summary: Company-wise Shipments & Revenue
$query = "
    SELECT c.company_name, 
           COUNT(s.id) as total_shipments,
           SUM(IF(st.status_name = 'Delivered', 1, 0)) as delivered_count,
           SUM(s.price) as total_revenue
    FROM courier_companies c
    LEFT JOIN shipments s ON c.id = s.company_id AND DATE(s.created_at) BETWEEN ? AND ?
    LEFT JOIN shipment_status st ON s.current_status_id = st.id
    GROUP BY c.id
    ORDER BY total_revenue DESC
";
$stmt = $pdo->prepare($query);
$stmt->execute([$start_date, $end_date]);
$reports = $stmt->fetchAll();

// Handle Export
if (isset($_GET['export'])) {
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment; filename=ConsignX_Report_' . date('Y-m-d') . '.xls');
    ?>
    <table border="1">
        <tr>
            <th>Company Name</th>
            <th>Total Shipments</th>
            <th>Delivered</th>
            <th>Revenue</th>
        </tr>
        <?php foreach ($reports as $row): ?>
            <tr>
                <td><?php echo $row['company_name']; ?></td>
                <td><?php echo $row['total_shipments']; ?></td>
                <td><?php echo $row['delivered_count']; ?></td>
                <td><?php echo $row['total_revenue']; ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
    <?php
    exit;
}

$page_title = 'Global Reporting';
include '../includes/header.php';
?>

<div class="dashboard-layout">
    <?php include '../includes/sidebar.php'; ?>

    <main class="main-content">
        <?php include '../includes/navbar.php'; ?>

        <div style="margin-bottom: 2rem;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                <h2 style="font-weight: 700;">Performance Reports</h2>
                <a href="?export=1&start_date=<?php echo $start_date; ?>&end_date=<?php echo $end_date; ?>"
                    class="btn btn-primary">
                    <i data-feather="download" style="width: 18px; margin-right: 8px;"></i>
                    Download XLSX
                </a>
            </div>

            <!-- Date Filters -->
            <div class="glass" style="padding: 1.5rem;">
                <form method="GET" style="display: flex; gap: 1rem; align-items: flex-end;">
                    <div style="flex: 1;">
                        <label
                            style="font-size: 0.8rem; color: var(--text-muted); display: block; margin-bottom: 0.3rem;">Start
                            Date</label>
                        <input type="date" name="start_date" class="form-control" value="<?php echo $start_date; ?>">
                    </div>
                    <div style="flex: 1;">
                        <label
                            style="font-size: 0.8rem; color: var(--text-muted); display: block; margin-bottom: 0.3rem;">End
                            Date</label>
                        <input type="date" name="end_date" class="form-control" value="<?php echo $end_date; ?>">
                    </div>
                    <button type="submit" class="btn btn-primary" style="padding: 0.75rem 1.5rem;">Generate
                        Report</button>
                </form>
            </div>
        </div>

        <!-- Summary Table -->
        <div class="glass glass-card">
            <h3 style="margin-bottom: 1.5rem;">Company-wise Summary</h3>
            <div class="table-responsive">
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="text-align: left; border-bottom: 1px solid var(--glass-border);">
                            <th style="padding: 1rem; color: var(--text-muted); font-weight: 500;">Company Name</th>
                            <th style="padding: 1rem; color: var(--text-muted); font-weight: 500;">Total Shipments</th>
                            <th style="padding: 1rem; color: var(--text-muted); font-weight: 500;">Delivered</th>
                            <th style="padding: 1rem; color: var(--text-muted); font-weight: 500;">Delivery Rate</th>
                            <th style="padding: 1rem; color: var(--text-muted); font-weight: 500; text-align: right;">
                                Total Revenue</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $grand_total_rev = 0;
                        foreach ($reports as $row):
                            $grand_total_rev += $row['total_revenue'];
                            $rate = $row['total_shipments'] > 0 ? ($row['delivered_count'] / $row['total_shipments']) * 100 : 0;
                            ?>
                            <tr style="border-bottom: 1px solid var(--glass-border);">
                                <td style="padding: 1rem; font-weight: 600;"><?php echo sanitize($row['company_name']); ?>
                                </td>
                                <td style="padding: 1rem;"><?php echo $row['total_shipments']; ?></td>
                                <td style="padding: 1rem;"><?php echo $row['delivered_count']; ?></td>
                                <td style="padding: 1rem;">
                                    <div style="display: flex; align-items: center; gap: 10px;">
                                        <div
                                            style="flex: 1; height: 6px; background: rgba(0,0,0,0.05); border-radius: 3px; position: relative;">
                                            <div
                                                style="position: absolute; left: 0; top: 0; height: 100%; width: <?php echo $rate; ?>%; background: var(--success); border-radius: 3px;">
                                            </div>
                                        </div>
                                        <span
                                            style="font-size: 0.8rem; font-weight: 600;"><?php echo number_format($rate, 1); ?>%</span>
                                    </div>
                                </td>
                                <td style="padding: 1rem; text-align: right; font-weight: 700; color: var(--primary);">
                                    <?php echo format_price($row['total_revenue']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <tr style="background: rgba(79, 70, 229, 0.05);">
                            <td colspan="4" style="padding: 1.5rem 1rem; font-weight: 700;">Grand Total</td>
                            <td
                                style="padding: 1.5rem 1rem; text-align: right; font-weight: 900; font-size: 1.2rem; color: var(--primary);">
                                <?php echo format_price($grand_total_rev); ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>

<?php include '../includes/footer.php'; ?>