<header class="navbar glass"
    style="margin-bottom: 2rem; padding: 1rem 2rem; display: flex; align-items: center; justify-content: space-between;">
    <div class="navbar-left">
        <h3 style="font-weight: 600; font-size: 1.1rem; color: var(--text-muted);">
            <?php echo $page_title ?? 'Dashboard'; ?>
        </h3>
    </div>

    <div class="navbar-right" style="display: flex; align-items: center; gap: 1.5rem;">
        <div class="search-box glass"
            style="padding: 0.5rem 1rem; border-radius: 20px; display: flex; align-items: center; gap: 8px;">
            <i data-feather="search" style="width: 16px; color: var(--text-muted);"></i>
            <input type="text" placeholder="Search..."
                style="background: transparent; border: none; outline: none; font-size: 0.9rem; color: var(--text-main);">
        </div>

        <div class="user-profile" style="display: flex; align-items: center; gap: 10px;">
            <div class="user-info" style="text-align: right;">
                <p style="font-weight: 600; font-size: 0.9rem; margin-bottom: 0;"><?php echo $_SESSION['full_name']; ?>
                </p>
                <p style="font-size: 0.75rem; color: var(--text-muted); text-transform: capitalize;">
                    <?php echo $_SESSION['role']; ?></p>
            </div>
            <div class="user-avatar"
                style="width: 40px; height: 40px; border-radius: 50%; background: var(--primary); color: white; display: flex; align-items: center; justify-content: center; font-weight: 700;">
                <?php echo strtoupper(substr($_SESSION['full_name'], 0, 1)); ?>
            </div>
        </div>
    </div>
</header>