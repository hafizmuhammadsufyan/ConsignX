<?php
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/helpers.php';

if (is_logged_in()) {
    redirect('index.php');
}

// Fetch companies for dropdown
$stmt = $pdo->query("SELECT id, company_name FROM courier_companies WHERE status = 'active'");
$companies = $stmt->fetchAll();

$page_title = 'Register - ConsignX';
$body_class = 'register-page';
include 'includes/header.php';
?>

<style>
    body.register-page {
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(45deg, #10b981, #3b82f6);
        min-height: 100vh;
        padding: 2rem 0;
    }

    .register-container {
        width: 100%;
        max-width: 500px;
        padding: 1rem;
    }

    .register-card {
        padding: 2.5rem;
    }

    .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1rem;
    }

    @media (max-width: 600px) {
        .form-row {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="register-container">
    <div class="glass register-card">
        <h2 style="margin-bottom: 1.5rem; text-align: center;">Join ConsignX</h2>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger"
                style="color: #ef4444; background: rgba(239, 68, 68, 0.1); padding: 10px; border-radius: 8px; margin-bottom: 1rem; border: 1px solid rgba(239, 68, 68, 0.2); font-size: 0.9rem;">
                <?php echo $_SESSION['error'];
                unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <form action="auth_process.php" method="POST" id="registerForm">
            <input type="hidden" name="action" value="register">
            <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">

            <div class="form-group">
                <label for="full_name"
                    style="display: block; margin-bottom: 0.5rem; font-size: 0.9rem; font-weight: 500;">Full
                    Name</label>
                <input type="text" name="full_name" id="full_name" class="form-control" placeholder="John Doe" required>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="email"
                        style="display: block; margin-bottom: 0.5rem; font-size: 0.9rem; font-weight: 500;">Email
                        Address</label>
                    <input type="email" name="email" id="email" class="form-control" placeholder="john@example.com"
                        required>
                </div>
                <div class="form-group">
                    <label for="phone"
                        style="display: block; margin-bottom: 0.5rem; font-size: 0.9rem; font-weight: 500;">Phone
                        Number</label>
                    <input type="text" name="phone" id="phone" class="form-control" placeholder="+123456789" required>
                </div>
            </div>

            <div class="form-group">
                <label for="company_id"
                    style="display: block; margin-bottom: 0.5rem; font-size: 0.9rem; font-weight: 500;">Select Courier
                    Company</label>
                <select name="company_id" id="company_id" class="form-control" required
                    style="background: rgba(255, 255, 255, 0.1); color: var(--text-main);">
                    <option value="">Select a company</option>
                    <?php foreach ($companies as $company): ?>
                        <option value="<?php echo $company['id']; ?>"><?php echo sanitize($company['company_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if (empty($companies)): ?>
                    <p style="font-size: 0.8rem; color: #ef4444; margin-top: 0.5rem;">No active companies found. Admin must
                        add companies first.</p>
                <?php endif; ?>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="password"
                        style="display: block; margin-bottom: 0.5rem; font-size: 0.9rem; font-weight: 500;">Password</label>
                    <input type="password" name="password" id="password" class="form-control" placeholder="••••••••"
                        required>
                </div>
                <div class="form-group">
                    <label for="confirm_password"
                        style="display: block; margin-bottom: 0.5rem; font-size: 0.9rem; font-weight: 500;">Confirm
                        Password</label>
                    <input type="password" name="confirm_password" id="confirm_password" class="form-control"
                        placeholder="••••••••" required>
                </div>
            </div>

            <button type="submit" class="btn btn-primary btn-ripple" style="width: 100%; margin-top: 1rem;">
                Create Account
            </button>
        </form>

        <p style="text-align: center; margin-top: 1.5rem; font-size: 0.9rem; color: #cbd5e1;">
            Already have an account? <a href="login.php"
                style="color: white; font-weight: 600; text-decoration: none;">Sign In</a>
        </p>
    </div>
</div>

<?php include 'includes/footer.php'; ?>