<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/helpers.php';

require_role('admin');

$page_title = 'Admin Dashboard';
include '../includes/header.php';
?>

<div class="dashboard-layout">
    <?php include '../includes/sidebar.php'; ?>

    <main class="main-content">
        <?php include '../includes/navbar.php'; ?>

        <!-- Stats Cards -->
        <div class="stats-grid"
            style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
            <div class="glass glass-card">
                <p style="color: var(--text-muted); font-size: 0.9rem; font-weight: 500;">Total Companies</p>
                <h2 style="font-size: 2rem; margin: 0.5rem 0;" class="counter" data-target="12">0</h2>
                <p style="color: var(--success); font-size: 0.8rem;"><i data-feather="trending-up"
                        style="width:12px"></i> +2 this month</p>
            </div>
            <div class="glass glass-card">
                <p style="color: var(--text-muted); font-size: 0.9rem; font-weight: 500;">Total Shipments</p>
                <h2 style="font-size: 2rem; margin: 0.5rem 0;" class="counter" data-target="1540">0</h2>
                <p style="color: var(--success); font-size: 0.8rem;"><i data-feather="trending-up"
                        style="width:12px"></i> +12% vs last month</p>
            </div>
            <div class="glass glass-card">
                <p style="color: var(--text-muted); font-size: 0.9rem; font-weight: 500;">Delivered</p>
                <h2 style="font-size: 2rem; margin: 0.5rem 0;" class="counter" data-target="1200">0</h2>
                <p style="color: var(--info); font-size: 0.8rem;">78% success rate</p>
            </div>
            <div class="glass glass-card">
                <p style="color: var(--text-muted); font-size: 0.9rem; font-weight: 500;">Total Revenue</p>
                <h2 style="font-size: 2rem; margin: 0.5rem 0;">$<span class="counter" data-target="45200">0</span></h2>
                <p style="color: var(--success); font-size: 0.8rem;"><i data-feather="trending-up"
                        style="width:12px"></i> +5.4% growth</p>
            </div>
        </div>

        <!-- Charts Row -->
        <div class="charts-row"
            style="display: grid; grid-template-columns: 2fr 1fr; gap: 1.5rem; margin-bottom: 2rem;">
            <div class="glass glass-card" style="min-height: 400px;">
                <h3 style="margin-bottom: 1.5rem;">Revenue Analytics</h3>
                <canvas id="revenueChart"></canvas>
            </div>
            <div class="glass glass-card" style="min-height: 400px;">
                <h3 style="margin-bottom: 1.5rem;">Shipment Status</h3>
                <canvas id="statusChart"></canvas>
            </div>
        </div>

        <!-- Recent Activity / Global Shipments -->
        <div class="glass glass-card">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                <h3>Recent Shipments</h3>
                <a href="shipments.php" class="btn btn-primary" style="padding: 0.5rem 1rem; font-size: 0.8rem;">View
                    All</a>
            </div>
            <div class="table-responsive">
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="text-align: left; border-bottom: 1px solid var(--glass-border);">
                            <th style="padding: 1rem 0.5rem; color: var(--text-muted); font-weight: 500;">Tracking #
                            </th>
                            <th style="padding: 1rem 0.5rem; color: var(--text-muted); font-weight: 500;">Company</th>
                            <th style="padding: 1rem 0.5rem; color: var(--text-muted); font-weight: 500;">Receiver</th>
                            <th style="padding: 1rem 0.5rem; color: var(--text-muted); font-weight: 500;">Status</th>
                            <th style="padding: 1rem 0.5rem; color: var(--text-muted); font-weight: 500;">Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Sample Row -->
                        <tr style="border-bottom: 1px solid var(--glass-border);">
                            <td style="padding: 1rem 0.5rem; font-weight: 600;">CX-78AB23</td>
                            <td style="padding: 1rem 0.5rem;">Swift Delivery</td>
                            <td style="padding: 1rem 0.5rem;">Jane Smith</td>
                            <td style="padding: 1rem 0.5rem;"><span
                                    style="background: rgba(16, 185, 129, 0.1); color: var(--success); padding: 4px 10px; border-radius: 12px; font-size: 0.8rem;">Delivered</span>
                            </td>
                            <td style="padding: 1rem 0.5rem; font-size: 0.9rem; color: var(--text-muted);">24 May, 2024
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>

<!-- Chart JS -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Counter Animation
    const counters = document.querySelectorAll('.counter');
    counters.forEach(counter => {
        const target = +counter.getAttribute('data-target');
        const count = +counter.innerText;
        const speed = 200;
        const inc = target / speed;

        const updateCount = () => {
            const current = +counter.innerText;
            if (current < target) {
                counter.innerText = Math.ceil(current + inc);
                setTimeout(updateCount, 1);
            } else {
                counter.innerText = target.toLocaleString();
            }
        }
        updateCount();
    });

    // Revenue Chart
    const ctxRevenue = document.getElementById('revenueChart').getContext('2d');
    new Chart(ctxRevenue, {
        type: 'line',
        data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
            datasets: [{
                label: 'Revenue ($)',
                data: [12000, 19000, 15000, 25000, 22000, 30000],
                borderColor: '#4f46e5',
                tension: 0.4,
                fill: true,
                backgroundColor: 'rgba(79, 70, 229, 0.1)'
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

    // Status Chart
    const ctxStatus = document.getElementById('statusChart').getContext('2d');
    new Chart(ctxStatus, {
        type: 'doughnut',
        data: {
            labels: ['Delivered', 'In Transit', 'Pending'],
            datasets: [{
                data: [1200, 300, 40],
                backgroundColor: ['#10b981', '#3b82f6', '#f59e0b'],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '70%',
            plugins: { legend: { position: 'bottom' } }
        }
    });
</script>

<?php include '../includes/footer.php'; ?>