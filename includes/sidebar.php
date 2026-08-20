<?php
$current_page = basename($_SERVER['PHP_SELF']);

// Get pending count for listings badge
try {
    $pdo = getConnection();
    $stmt = $pdo->query("SELECT COUNT(*) as pending FROM listings WHERE status = 'pending'");
    $pending_listings = $stmt->fetch()['pending'] ?? 0;
} catch (PDOException $e) {
    $pending_listings = 0;
}

// Get total users count
try {
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM users WHERE role = 'user'");
    $total_users = $stmt->fetch()['total'] ?? 0;
} catch (PDOException $e) {
    $total_users = 0;
}

// Get pending reports count
try {
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM reports WHERE status = 'pending'");
    $pending_reports = $stmt->fetch()['total'] ?? 0;
} catch (PDOException $e) {
    $pending_reports = 0;
}
?>
<div class="bg-dark text-white" id="sidebar-wrapper" style="min-height: 100vh; width: 260px; flex-shrink: 0;">
    <div class="sidebar-heading text-center py-4">
        <i class="bi bi-shop fs-2"></i>
        <h5 class="mt-2 fw-bold">PolyGo+</h5>
        <small class="text-muted">Admin Panel</small>
    </div>
    <div class="list-group list-group-flush">
        <a href="dashboard.php" class="list-group-item list-group-item-action bg-dark text-white <?php echo $current_page == 'dashboard.php' ? 'active' : ''; ?>">
            <i class="bi bi-speedometer2"></i> Dashboard
        </a>
        <a href="users.php" class="list-group-item list-group-item-action bg-dark text-white <?php echo $current_page == 'users.php' ? 'active' : ''; ?>">
            <i class="bi bi-people"></i> Users
            <?php if ($total_users > 0): ?>
                <span class="badge bg-primary float-end"><?php echo $total_users; ?></span>
            <?php endif; ?>
        </a>
        <a href="listings.php" class="list-group-item list-group-item-action bg-dark text-white <?php echo $current_page == 'listings.php' ? 'active' : ''; ?>">
            <i class="bi bi-box"></i> Listings
            <?php if ($pending_listings > 0): ?>
                <span class="badge bg-warning float-end"><?php echo $pending_listings; ?></span>
            <?php endif; ?>
        </a>
        <a href="categories.php" class="list-group-item list-group-item-action bg-dark text-white <?php echo $current_page == 'categories.php' ? 'active' : ''; ?>">
            <i class="bi bi-tags"></i> Categories
        </a>
        <a href="reports.php" class="list-group-item list-group-item-action bg-dark text-white <?php echo $current_page == 'reports.php' ? 'active' : ''; ?>">
            <i class="bi bi-flag"></i> Reports
            <?php if ($pending_reports > 0): ?>
                <span class="badge bg-danger float-end"><?php echo $pending_reports; ?></span>
            <?php endif; ?>
        </a>
        <a href="analytics.php" class="list-group-item list-group-item-action bg-dark text-white <?php echo $current_page == 'analytics.php' ? 'active' : ''; ?>">
            <i class="bi bi-graph-up"></i> Analytics
        </a>
        <hr class="text-muted">
        <a href="logout.php" class="list-group-item list-group-item-action bg-dark text-danger">
            <i class="bi bi-box-arrow-right"></i> Logout
        </a>
    </div>
</div>

<style>
    .list-group-item {
        border: none;
        border-radius: 0;
        padding: 12px 20px;
        transition: all 0.3s ease;
    }
    .list-group-item:hover {
        background: #2c3e50 !important;
    }
    .list-group-item.active {
        background: #667eea !important;
        border: none;
    }
    .list-group-item.active:hover {
        background: #5568d3 !important;
    }
    #sidebar-wrapper {
        position: sticky;
        top: 0;
        height: 100vh;
        overflow-y: auto;
    }
    #sidebar-wrapper::-webkit-scrollbar {
        width: 4px;
    }
    #sidebar-wrapper::-webkit-scrollbar-track {
        background: #1a1a2e;
    }
    #sidebar-wrapper::-webkit-scrollbar-thumb {
        background: #667eea;
        border-radius: 2px;
    }
    .badge {
        font-size: 11px;
        padding: 4px 8px;
    }
</style>