<?php
$role = get_user_role();
$current_page = basename($_SERVER['PHP_SELF']);
?>

<aside class="sidebar glass">
    <div class="sidebar-header"
        style="padding-bottom: 2rem; border-bottom: 1px solid var(--glass-border); margin-bottom: 2rem;">
        <h2 style="font-size: 1.5rem; letter-spacing: -1px; color: var(--primary);">ConsignX</h2>
    </div>

    <nav class="sidebar-nav" style="flex: 1;">
        <ul style="list-style: none;">
            <!-- COMMON HOME -->
            <li style="margin-bottom: 0.5rem;">
                <a href="<?php echo BASE_URL; ?>index.php"
                    class="nav-link <?php echo $current_page == 'dashboard.php' ? 'active' : ''; ?>"
                    style="display: flex; align-items: center; padding: 0.75rem 1rem; border-radius: var(--radius-md); text-decoration: none; color: var(--text-main); transition: all 0.2s;">
                    <i data-feather="grid" style="width: 18px; margin-right: 12px;"></i>
                    <span>Dashboard</span>
                </a>
            </li>

            <?php if ($role === 'admin'): ?>
                <!-- ADMIN LINKS -->
                <li style="margin-bottom: 0.5rem;">
                    <a href="<?php echo BASE_URL; ?>admin/companies.php"
                        class="nav-link <?php echo $current_page == 'companies.php' ? 'active' : ''; ?>"
                        style="display: flex; align-items: center; padding: 0.75rem 1rem; border-radius: var(--radius-md); text-decoration: none; color: var(--text-main); transition: all 0.2s;">
                        <i data-feather="briefcase" style="width: 18px; margin-right: 12px;"></i>
                        <span>Companies</span>
                    </a>
                </li>
                <li style="margin-bottom: 0.5rem;">
                    <a href="<?php echo BASE_URL; ?>admin/shipments.php"
                        class="nav-link <?php echo $current_page == 'shipments.php' ? 'active' : ''; ?>"
                        style="display: flex; align-items: center; padding: 0.75rem 1rem; border-radius: var(--radius-md); text-decoration: none; color: var(--text-main); transition: all 0.2s;">
                        <i data-feather="package" style="width: 18px; margin-right: 12px;"></i>
                        <span>All Shipments</span>
                    </a>
                </li>
                <li style="margin-bottom: 0.5rem;">
                    <a href="<?php echo BASE_URL; ?>admin/reports.php"
                        class="nav-link <?php echo $current_page == 'reports.php' ? 'active' : ''; ?>"
                        style="display: flex; align-items: center; padding: 0.75rem 1rem; border-radius: var(--radius-md); text-decoration: none; color: var(--text-main); transition: all 0.2s;">
                        <i data-feather="bar-chart-2" style="width: 18px; margin-right: 12px;"></i>
                        <span>Reports</span>
                    </a>
                </li>

            <?php elseif ($role === 'agent'): ?>
                <!-- AGENT LINKS -->
                <li style="margin-bottom: 0.5rem;">
                    <a href="<?php echo BASE_URL; ?>agent/create_shipment.php"
                        class="nav-link <?php echo $current_page == 'create_shipment.php' ? 'active' : ''; ?>"
                        style="display: flex; align-items: center; padding: 0.75rem 1rem; border-radius: var(--radius-md); text-decoration: none; color: var(--text-main); transition: all 0.2s;">
                        <i data-feather="plus-circle" style="width: 18px; margin-right: 12px;"></i>
                        <span>Create Shipment</span>
                    </a>
                </li>
                <li style="margin-bottom: 0.5rem;">
                    <a href="<?php echo BASE_URL; ?>agent/manage_shipments.php"
                        class="nav-link <?php echo $current_page == 'manage_shipments.php' ? 'active' : ''; ?>"
                        style="display: flex; align-items: center; padding: 0.75rem 1rem; border-radius: var(--radius-md); text-decoration: none; color: var(--text-main); transition: all 0.2s;">
                        <i data-feather="package" style="width: 18px; margin-right: 12px;"></i>
                        <span>Manage Shipments</span>
                    </a>
                </li>

            <?php elseif ($role === 'user'): ?>
                <!-- USER LINKS -->
                <li style="margin-bottom: 0.5rem;">
                    <a href="<?php echo BASE_URL; ?>user/track.php"
                        class="nav-link <?php echo $current_page == 'track.php' ? 'active' : ''; ?>"
                        style="display: flex; align-items: center; padding: 0.75rem 1rem; border-radius: var(--radius-md); text-decoration: none; color: var(--text-main); transition: all 0.2s;">
                        <i data-feather="search" style="width: 18px; margin-right: 12px;"></i>
                        <span>Track Shipment</span>
                    </a>
                </li>
            <?php endif; ?>
        </ul>
    </nav>

    <div class="sidebar-footer" style="padding-top: 1rem; border-top: 1px solid var(--glass-border);">
        <a href="<?php echo BASE_URL; ?>logout.php" class="nav-link"
            style="display: flex; align-items: center; padding: 0.75rem 1rem; border-radius: var(--radius-md); text-decoration: none; color: var(--danger); transition: all 0.2s;">
            <i data-feather="log-out" style="width: 18px; margin-right: 12px;"></i>
            <span>Sign Out</span>
        </a>
    </div>
</aside>

<style>
    .nav-link:hover {
        background: rgba(79, 70, 229, 0.1);
        color: var(--primary) !important;
    }

    .nav-link.active {
        background: var(--primary);
        color: white !important;
    }

    .nav-link.active i {
        color: white;
    }
</style>