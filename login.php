<?php
require_once 'includes/config.php';
require_once 'includes/helpers.php';

if (is_logged_in()) {
    redirect('index.php');
}

$page_title = 'Login - ConsignX';
$body_class = 'login-page';
include 'includes/header.php';
?>

<style>
    body.login-page {
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(45deg, #4f46e5, #0ea5e9);
        height: 100vh;
        overflow: hidden;
    }

    .login-container {
        width: 100%;
        max-width: 400px;
        padding: 2rem;
        position: relative;
        z-index: 1;
    }

    .brand-logo {
        text-align: center;
        margin-bottom: 2rem;
        color: white;
    }

    .brand-logo h1 {
        font-size: 2.5rem;
        letter-spacing: -1px;
        margin-bottom: 0.5rem;
        color: white;
    }

    .login-card {
        padding: 2.5rem;
    }

    .login-card h2 {
        margin-bottom: 1.5rem;
        text-align: center;
        font-size: 1.5rem;
    }

    .floating-bg {
        position: absolute;
        width: 500px;
        height: 500px;
        background: rgba(255, 255, 255, 0.1);
        border-radius: 50%;
        filter: blur(80px);
        z-index: -1;
    }

    .blob-1 {
        top: -250px;
        left: -250px;
        background: #818cf8;
    }

    .blob-2 {
        bottom: -250px;
        right: -250px;
        background: #38bdf8;
    }
</style>

<div class="blob-1 floating-bg"></div>
<div class="blob-2 floating-bg"></div>

<div class="login-container">
    <div class="brand-logo">
        <h1>ConsignX</h1>
        <p>Premium Courier Solutions</p>
    </div>

    <div class="glass login-card">
        <h2>Welcome Back</h2>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger"
                style="color: #ef4444; background: rgba(239, 68, 68, 0.1); padding: 10px; border-radius: 8px; margin-bottom: 1rem; border: 1px solid rgba(239, 68, 68, 0.2); font-size: 0.9rem;">
                <?php echo $_SESSION['error'];
                unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <form action="auth_process.php" method="POST" id="loginForm">
            <input type="hidden" name="action" value="login">
            <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">

            <div class="form-group">
                <label for="email"
                    style="display: block; margin-bottom: 0.5rem; font-size: 0.9rem; font-weight: 500;">Email
                    Address</label>
                <input type="email" name="email" id="email" class="form-control" placeholder="admin@consignx.com"
                    required>
            </div>

            <div class="form-group">
                <label for="password"
                    style="display: block; margin-bottom: 0.5rem; font-size: 0.9rem; font-weight: 500;">Password</label>
                <input type="password" name="password" id="password" class="form-control" placeholder="••••••••"
                    required>
            </div>

            <button type="submit" class="btn btn-primary btn-ripple" style="width: 100%; margin-top: 1rem;">
                Sign In
            </button>
        </form>

        <p style="text-align: center; margin-top: 1.5rem; font-size: 0.9rem; color: var(--text-muted);">
            New here? <a href="register.php"
                style="color: var(--primary); font-weight: 600; text-decoration: none;">Create an account</a>
        </p>
    </div>
</div>

<?php include 'includes/footer.php'; ?>