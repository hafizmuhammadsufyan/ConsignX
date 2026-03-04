<?php
require_once 'includes/config.php';
require_once 'includes/helpers.php';

$page_title = 'Unauthorized - ConsignX';
include 'includes/header.php';
?>

<div
    style="height: 100vh; display: flex; align-items: center; justify-content: center; text-align: center; padding: 2rem;">
    <div class="glass glass-card" style="max-width: 500px;">
        <i data-feather="shield-off"
            style="width: 64px; height: 64px; color: var(--danger); margin-bottom: 1.5rem;"></i>
        <h1 style="font-size: 2rem; margin-bottom: 1rem;">Access Denied</h1>
        <p style="color: var(--text-muted); margin-bottom: 2rem;">You do not have permission to access this page. This
            incident has been logged for security purposes.</p>

        <div style="display: flex; gap: 1rem; justify-content: center;">
            <a href="index.php" class="btn btn-primary">Go to Dashboard</a>
            <a href="logout.php" class="btn" style="background: rgba(0,0,0,0.05);">Logout</a>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>