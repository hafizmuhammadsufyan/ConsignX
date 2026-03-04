<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/helpers.php';

require_role('agent');

$company_id = $_SESSION['company_id'];

// Get company specific stats
$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM shipments WHERE company_id = ?");
$stmt->execute([$company_id]);
$total_shipments = $stmt->fetch()['total'];

$stmt = $pdo->prepare("
    SELECT COUNT(*) as delivered 
    FROM shipments s 
    JOIN shipment_status st ON s.current_status_id = st.id 
    WHERE s.company_id = ? AND st.status_name = 'Delivered'
");
$stmt->execute([$company_id]);
$delivered = $stmt->fetch()['delivered'];

$stmt = $pdo->prepare("SELECT SUM(price) as revenue FROM shipments WHERE company_id = ?");
$stmt->execute([$company_id]);
$revenue = $stmt->fetch()['revenue'] ?? 0;

$page_title = 'Agent Dashboard';
include '../includes/header.php';
?>

<div class="dashboard-layout">
    <?php include '../includes/sidebar.php'; ?>

    <main class="main-content">
        <?php include '../includes/navbar.php'; ?>

        <div class="stats-grid"
            style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
            <div class="glass glass-card">
                <p style="color: var(--text-muted); font-size: 0.9rem; font-weight: 500;">Total Shipments</p>
                <h2 style="font-size: 2rem; margin: 0.5rem 0;" class="counter"
                    data-target="<?php echo $total_shipments; ?>">0</h2>
            </div>
            <div class="glass glass-card">
                <p style="color: var(--text-muted); font-size: 0.9rem; font-weight: 500;">Delivered</p>
                <h2 style="font-size: 2rem; margin: 0.5rem 0;" class="counter" data-target="<?php echo $delivered; ?>">0
                </h2>
            </div>
            <div class="glass glass-card">
                <p style="color: var(--text-muted); font-size: 0.9rem; font-weight: 500;">In Transit</p>
                <h2 style="font-size: 2rem; margin: 0.5rem 0;" class="counter"
                    data-target="<?php echo $total_shipments - $delivered; ?>">0</h2>
            </div>
            <div class="glass glass-card">
                <p style="color: var(--text-muted); font-size: 0.9rem; font-weight: 500;">Revenue</p>
                <h2 style="font-size: 2rem; margin: 0.5rem 0;">$<span class="counter"
                        data-target="<?php echo $revenue; ?>">0</span></h2>
            </div>
        </div>

        <div class="charts-row" style="display: grid; grid-template-columns: 2fr 1fr; gap: 1.5rem;">
            <div class="glass glass-card" style="height: 350px;">
                <h3 style="margin-bottom: 1.5rem;">Weekly Shipment Volume</h3>
                <canvas id="volumeChart"></canvas>
            </div>
            <div class="glass glass-card"
                style="height: 350px; display: flex; flex-direction: column; justify-content: center; align-items: center; text-align: center;">
                <h3 style="margin-bottom: 1rem;">Action Center</h3>
                <p style="color: var(--text-muted); font-size: 0.9rem; margin-bottom: 1.5rem;">Quickly manage your
                    operations.</p>
                <div style="display: flex; flex-direction: column; gap: 0.75rem; width: 100%;">
                    <a href="create_shipment.php" class="btn btn-primary" style="width: 100%;"><i
                            data-feather="plus-circle" style="margin-right:8px"></i> Create Shipment</a>
                    <a href="manage_shipments.php" class="btn"
                        style="width: 100%; background: rgba(100, 116, 139, 0.1); color: var(--text-main);"><i
                            data-feather="package" style="margin-right:8px"></i> Manage All</a>
                </div>
            </div>
        </div>
    </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Counter
    const counters = document.querySelectorAll('.counter');
    counters.forEach(counter => {
        const target = +counter.getAttribute('data-target');
        const updateCount = () => {
            const current = +counter.innerText;
            const inc = target / 100;
            if (current < target) {
                counter.innerText = Math.ceil(current + inc);
                setTimeout(updateCount, 10);
            } else {
                counter.innerText = target.toLocaleString();
            }
        }
        updateCount();
    });

    const ctxVolume = document.getElementById('volumeChart').getContext('2d');
    new Chart(ctxVolume, {
        type: 'bar',
        data: {
            labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
            datasets: [{
                label: 'Shipments',
                data: [12, 19, 3, 5, 2, 3, 9],
                backgroundColor: 'rgba(79, 70, 229, 0.6)',
                borderRadius: 8
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { grid: { borderDash: [2, 4] } },
                x: { grid: { display: false } }
            }
        }
    });
</script>

<?php include '../includes/footer.php'; ?>