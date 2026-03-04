<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/helpers.php';

require_role('agent');

$page_title = 'Create Shipment';
include '../includes/header.php';
?>

<div class="dashboard-layout">
    <?php include '../includes/sidebar.php'; ?>

    <main class="main-content">
        <?php include '../includes/navbar.php'; ?>

        <div style="max-width: 800px; margin: 0 auto;">
            <h2 style="font-weight: 700; margin-bottom: 2rem;">New Shipment</h2>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="glass"
                    style="padding: 1rem; border-left: 4px solid var(--danger); margin-bottom: 1.5rem; color: var(--danger); background: rgba(239, 68, 68, 0.1);">
                    <?php echo $_SESSION['error'];
                    unset($_SESSION['error']); ?>
                </div>
            <?php endif; ?>

            <form action="process_shipment.php" method="POST" class="glass"
                style="padding: 2.5rem; border-radius: 20px;">
                <input type="hidden" name="action" value="create">
                <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">

                <!-- Sender Information -->
                <h4
                    style="color: var(--primary); margin-bottom: 1.5rem; border-bottom: 1px solid var(--glass-border); padding-bottom: 0.5rem; text-transform: uppercase; letter-spacing: 1px; font-size: 0.8rem;">
                    Sender Details</h4>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem;">
                    <div class="form-group">
                        <label style="display:block; font-size: 0.85rem; margin-bottom: 0.4rem;">Full Name</label>
                        <input type="text" name="sender_name" class="form-control" placeholder="John Sender" required>
                    </div>
                    <div class="form-group">
                        <label style="display:block; font-size: 0.85rem; margin-bottom: 0.4rem;">Phone Number</label>
                        <input type="text" name="sender_phone" class="form-control" placeholder="+123..." required>
                    </div>
                </div>
                <div class="form-group" style="margin-bottom: 2rem;">
                    <label style="display:block; font-size: 0.85rem; margin-bottom: 0.4rem;">Address</label>
                    <textarea name="sender_address" class="form-control" rows="2" placeholder="Street, City, Country"
                        required></textarea>
                </div>

                <!-- Receiver Information -->
                <h4
                    style="color: var(--primary); margin-bottom: 1.5rem; border-bottom: 1px solid var(--glass-border); padding-bottom: 0.5rem; text-transform: uppercase; letter-spacing: 1px; font-size: 0.8rem;">
                    Receiver Details</h4>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem;">
                    <div class="form-group">
                        <label style="display:block; font-size: 0.85rem; margin-bottom: 0.4rem;">Full Name</label>
                        <input type="text" name="receiver_name" class="form-control" placeholder="Jane Receiver"
                            required>
                    </div>
                    <div class="form-group">
                        <label style="display:block; font-size: 0.85rem; margin-bottom: 0.4rem;">Phone Number</label>
                        <input type="text" name="receiver_phone" class="form-control" placeholder="+987..." required>
                    </div>
                </div>
                <div class="form-group" style="margin-bottom: 2rem;">
                    <label style="display:block; font-size: 0.85rem; margin-bottom: 0.4rem;">Address</label>
                    <textarea name="receiver_address" class="form-control" rows="2" placeholder="Destination Address"
                        required></textarea>
                </div>

                <!-- Shipment Details -->
                <h4
                    style="color: var(--primary); margin-bottom: 1.5rem; border-bottom: 1px solid var(--glass-border); padding-bottom: 0.5rem; text-transform: uppercase; letter-spacing: 1px; font-size: 0.8rem;">
                    Package Information</h4>
                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1rem; margin-bottom: 1.5rem;">
                    <div class="form-group">
                        <label style="display:block; font-size: 0.85rem; margin-bottom: 0.4rem;">Type</label>
                        <select name="shipment_type" class="form-control">
                            <option value="standard">Standard</option>
                            <option value="express">Express</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label style="display:block; font-size: 0.85rem; margin-bottom: 0.4rem;">Weight (kg)</label>
                        <input type="number" step="0.01" name="weight" class="form-control" placeholder="0.5" required>
                    </div>
                    <div class="form-group">
                        <label style="display:block; font-size: 0.85rem; margin-bottom: 0.4rem;">Price ($)</label>
                        <input type="number" step="0.01" name="price" class="form-control" placeholder="45.00" required>
                    </div>
                </div>

                <div class="form-group">
                    <label style="display:block; font-size: 0.85rem; margin-bottom: 0.4rem;">Expected Delivery
                        Date</label>
                    <input type="date" name="expected_delivery_date" class="form-control"
                        value="<?php echo date('Y-m-d', strtotime('+3 days')); ?>" required>
                </div>

                <div style="margin-top: 3rem; display: flex; gap: 1rem;">
                    <button type="submit" class="btn btn-primary btn-ripple" style="flex: 1; padding: 1rem;">
                        <i data-feather="check-circle" style="margin-right: 8px;"></i>
                        Generate Shipment
                    </button>
                    <button type="reset" class="btn" style="padding: 1rem; background: rgba(0,0,0,0.05);">Reset</button>
                </div>
            </form>
        </div>
    </main>
</div>

<?php include '../includes/footer.php'; ?>