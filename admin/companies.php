<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/helpers.php';

require_role('admin');

// Fetch all companies
$stmt = $pdo->query("
    SELECT c.*, (SELECT COUNT(*) FROM users u WHERE u.company_id = c.id AND u.role_id = 2) as agent_count 
    FROM courier_companies c 
    ORDER BY c.created_at DESC
");
$companies = $stmt->fetchAll();

$page_title = 'Manage Companies';
include '../includes/header.php';
?>

<div class="dashboard-layout">
    <?php include '../includes/sidebar.php'; ?>

    <main class="main-content">
        <?php include '../includes/navbar.php'; ?>

        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
            <h2 style="font-weight: 700;">Courier Companies</h2>
            <button class="btn btn-primary btn-ripple" onclick="toggleModal('addCompanyModal')">
                <i data-feather="plus" style="width: 18px; margin-right: 8px;"></i>
                Add New Company
            </button>
        </div>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="glass"
                style="padding: 1rem; border-left: 4px solid var(--success); margin-bottom: 1.5rem; color: var(--success); background: rgba(16, 185, 129, 0.1);">
                <?php echo $_SESSION['success'];
                unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="glass"
                style="padding: 1rem; border-left: 4px solid var(--danger); margin-bottom: 1.5rem; color: var(--danger); background: rgba(239, 68, 68, 0.1);">
                <?php echo $_SESSION['error'];
                unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <!-- Companies Table -->
        <div class="glass glass-card">
            <div class="table-responsive">
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="text-align: left; border-bottom: 1px solid var(--glass-border);">
                            <th style="padding: 1rem; color: var(--text-muted); font-weight: 500;">Company Name</th>
                            <th style="padding: 1rem; color: var(--text-muted); font-weight: 500;">Email</th>
                            <th style="padding: 1rem; color: var(--text-muted); font-weight: 500;">Phone</th>
                            <th style="padding: 1rem; color: var(--text-muted); font-weight: 500;">Status</th>
                            <th style="padding: 1rem; color: var(--text-muted); font-weight: 500;">Created At</th>
                            <th style="padding: 1rem; color: var(--text-muted); font-weight: 500; text-align: right;">
                                Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($companies as $company): ?>
                            <tr style="border-bottom: 1px solid var(--glass-border);">
                                <td style="padding: 1rem; font-weight: 600;">
                                    <?php echo sanitize($company['company_name']); ?></td>
                                <td style="padding: 1rem;"><?php echo sanitize($company['company_email']); ?></td>
                                <td style="padding: 1rem;"><?php echo sanitize($company['company_phone']); ?></td>
                                <td style="padding: 1rem;">
                                    <span style="background: <?php echo $company['status'] == 'active' ? 'rgba(16, 185, 129, 0.1)' : 'rgba(239, 68, 68, 0.1)'; ?>; 
                                            color: <?php echo $company['status'] == 'active' ? 'var(--success)' : 'var(--danger)'; ?>; 
                                            padding: 4px 10px; border-radius: 12px; font-size: 0.8rem;">
                                        <?php echo ucfirst($company['status']); ?>
                                    </span>
                                </td>
                                <td style="padding: 1rem; font-size: 0.9rem; color: var(--text-muted);">
                                    <?php echo date('d M, Y', strtotime($company['created_at'])); ?></td>
                                <td style="padding: 1rem; text-align: right;">
                                    <div style="display: flex; gap: 8px; justify-content: flex-end;">
                                        <form action="process_company.php" method="POST" style="display:inline;">
                                            <input type="hidden" name="action" value="toggle_status">
                                            <input type="hidden" name="company_id" value="<?php echo $company['id']; ?>">
                                            <input type="hidden" name="csrf_token"
                                                value="<?php echo generate_csrf_token(); ?>">
                                            <button type="submit" class="btn"
                                                style="padding: 6px; background: rgba(100, 116, 139, 0.1); color: var(--text-main);"
                                                title="Toggle Status">
                                                <i data-feather="<?php echo $company['status'] == 'active' ? 'eye-off' : 'eye'; ?>"
                                                    style="width: 16px;"></i>
                                            </button>
                                        </form>
                                        <button class="btn"
                                            style="padding: 6px; background: rgba(79, 70, 229, 0.1); color: var(--primary);"
                                            title="Edit"
                                            onclick="editCompany(<?php echo htmlspecialchars(json_encode($company)); ?>)">
                                            <i data-feather="edit-2" style="width: 16px;"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($companies)): ?>
                            <tr>
                                <td colspan="6" style="padding: 3rem; text-align: center; color: var(--text-muted);">
                                    <i data-feather="inbox"
                                        style="width: 48px; height: 48px; margin-bottom: 1rem; opacity: 0.5;"></i>
                                    <p>No courier companies found.</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>

<!-- Modal: Add/Edit Company -->
<div id="addCompanyModal" class="modal-overlay"
    style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); backdrop-filter: blur(4px); z-index: 1000; align-items: center; justify-content: center;">
    <div class="glass" style="width: 100%; max-width: 500px; padding: 2.5rem; position: relative;">
        <button onclick="toggleModal('addCompanyModal')"
            style="position: absolute; top: 1.5rem; right: 1.5rem; background: none; border: none; cursor: pointer; color: var(--text-muted);">
            <i data-feather="x"></i>
        </button>

        <h3 id="modalTitle" style="margin-bottom: 1.5rem;">Add New Company</h3>

        <form action="process_company.php" method="POST">
            <input type="hidden" name="action" id="formAction" value="add">
            <input type="hidden" name="company_id" id="editCompanyId" value="">
            <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">

            <div class="form-group">
                <label style="display: block; font-size: 0.9rem; margin-bottom: 0.4rem; font-weight: 500;">Company
                    Name</label>
                <input type="text" name="company_name" id="editCompanyName" class="form-control"
                    placeholder="e.g. Swift Express" required>
            </div>

            <div class="form-group">
                <label style="display: block; font-size: 0.9rem; margin-bottom: 0.4rem; font-weight: 500;">Official
                    Email</label>
                <input type="email" name="company_email" id="editCompanyEmail" class="form-control"
                    placeholder="info@swift.com" required>
            </div>

            <div class="form-group">
                <label style="display: block; font-size: 0.9rem; margin-bottom: 0.4rem; font-weight: 500;">Phone
                    Number</label>
                <input type="text" name="company_phone" id="editCompanyPhone" class="form-control"
                    placeholder="+123456789" required>
            </div>

            <div class="form-group">
                <label
                    style="display: block; font-size: 0.9rem; margin-bottom: 0.4rem; font-weight: 500;">Address</label>
                <textarea name="company_address" id="editCompanyAddress" class="form-control" rows="3"
                    placeholder="Headquarters address..."></textarea>
            </div>

            <div id="agentCredentials"
                style="background: rgba(79, 70, 229, 0.05); padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem;">
                <p
                    style="font-size: 0.8rem; font-weight: 700; color: var(--primary); text-transform: uppercase; margin-bottom: 0.5rem;">
                    Initial Agent Account</p>
                <div class="form-group" style="margin-bottom: 0.5rem;">
                    <label style="display: block; font-size: 0.8rem; margin-bottom: 0.2rem;">Agent Password</label>
                    <input type="password" name="agent_password" class="form-control" placeholder="••••••••">
                </div>
                <p style="font-size: 0.75rem; color: var(--text-muted);">The agent username will be the company email.
                </p>
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%;">Save Company</button>
        </form>
    </div>
</div>

<script>
    function toggleModal(id) {
        const modal = document.getElementById(id);
        modal.style.display = modal.style.display === 'none' ? 'flex' : 'none';

        // Reset form if opening for Add
        if (modal.style.display === 'flex' && document.getElementById('formAction').value === 'edit') {
            // Already set by editCompany, don't reset
        } else if (modal.style.display === 'flex') {
            document.getElementById('modalTitle').innerText = 'Add New Company';
            document.getElementById('formAction').value = 'add';
            document.getElementById('editCompanyId').value = '';
            document.getElementById('editCompanyName').value = '';
            document.getElementById('editCompanyEmail').value = '';
            document.getElementById('editCompanyPhone').value = '';
            document.getElementById('editCompanyAddress').value = '';
            document.getElementById('agentCredentials').style.display = 'block';
        }
    }

    function editCompany(company) {
        document.getElementById('modalTitle').innerText = 'Edit Company';
        document.getElementById('formAction').value = 'edit';
        document.getElementById('editCompanyId').value = company.id;
        document.getElementById('editCompanyName').value = company.company_name;
        document.getElementById('editCompanyEmail').value = company.company_email;
        document.getElementById('editCompanyPhone').value = company.company_phone;
        document.getElementById('editCompanyAddress').value = company.company_address;
        document.getElementById('agentCredentials').style.display = 'none';

        document.getElementById('addCompanyModal').style.display = 'flex';
    }
</script>

<?php include '../includes/footer.php'; ?>