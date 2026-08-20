<?php
require_once 'config/database.php';
requireAdmin();

$pdo = getConnection();

// Get statistics with error handling
$stats = [];

// Total users
try {
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM users WHERE role = 'user'");
    $stats['total_users'] = $stmt->fetch()['total'] ?? 0;
} catch (PDOException $e) {
    $stats['total_users'] = 0;
}

// Total listings
try {
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM listings WHERE status != 'removed'");
    $stats['total_listings'] = $stmt->fetch()['total'] ?? 0;
} catch (PDOException $e) {
    $stats['total_listings'] = 0;
}

// Pending listings
try {
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM listings WHERE status = 'pending'");
    $stats['pending_listings'] = $stmt->fetch()['total'] ?? 0;
} catch (PDOException $e) {
    $stats['pending_listings'] = 0;
}

// Pending reports
try {
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM reports WHERE status = 'pending'");
    $stats['pending_reports'] = $stmt->fetch()['total'] ?? 0;
} catch (PDOException $e) {
    $stats['pending_reports'] = 0;
}

// Recent users (last 5)
try {
    $stmt = $pdo->query("SELECT * FROM users WHERE role = 'user' ORDER BY created_at DESC LIMIT 5");
    $recent_users = $stmt->fetchAll();
} catch (PDOException $e) {
    $recent_users = [];
}

// Recent listings (last 5)
try {
    $stmt = $pdo->query("SELECT * FROM listings ORDER BY created_at DESC LIMIT 5");
    $recent_listings = $stmt->fetchAll();
} catch (PDOException $e) {
    $recent_listings = [];
}

// Get today's date
$today = date('d/m/Y');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - PolyGo+ Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
    <div class="d-flex" id="wrapper">
        <!-- Sidebar -->
        <?php include 'includes/sidebar.php'; ?>
        
        <!-- Page Content -->
        <div id="page-content-wrapper">
            <!-- Header -->
            <?php include 'includes/header.php'; ?>
            
            <!-- Content -->
            <div class="container-fluid px-4">
                <!-- Welcome Row -->
                <div class="row mt-3 mb-4">
                    <div class="col-12">
                        <h3 class="fw-bold">
                            <i class="bi bi-speedometer2"></i> Dashboard
                        </h3>
                        <p class="text-muted">Welcome back, <?php echo htmlspecialchars($_SESSION['full_name'] ?? 'Admin'); ?>! Today is <?php echo $today; ?></p>
                    </div>
                </div>
                
                <!-- Statistics Cards -->
                <div class="row g-4">
                    <div class="col-xl-3 col-md-6">
                        <div class="card stat-card primary h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <p class="text-muted mb-1 small">Total Users</p>
                                        <h2 class="fw-bold"><?php echo number_format($stats['total_users']); ?></h2>
                                    </div>
                                    <div class="stat-icon">
                                        <i class="bi bi-people"></i>
                                    </div>
                                </div>
                                <a href="users.php" class="text-decoration-none small">View all <i class="bi bi-arrow-right"></i></a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-xl-3 col-md-6">
                        <div class="card stat-card success h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <p class="text-muted mb-1 small">Total Listings</p>
                                        <h2 class="fw-bold"><?php echo number_format($stats['total_listings']); ?></h2>
                                    </div>
                                    <div class="stat-icon">
                                        <i class="bi bi-box"></i>
                                    </div>
                                </div>
                                <a href="listings.php" class="text-decoration-none small">View all <i class="bi bi-arrow-right"></i></a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-xl-3 col-md-6">
                        <div class="card stat-card warning h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <p class="text-muted mb-1 small">Pending Listings</p>
                                        <h2 class="fw-bold"><?php echo number_format($stats['pending_listings']); ?></h2>
                                    </div>
                                    <div class="stat-icon">
                                        <i class="bi bi-clock-history"></i>
                                    </div>
                                </div>
                                <a href="listings.php?status=pending" class="text-decoration-none small">Review <i class="bi bi-arrow-right"></i></a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-xl-3 col-md-6">
                        <div class="card stat-card danger h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <p class="text-muted mb-1 small">Pending Reports</p>
                                        <h2 class="fw-bold"><?php echo number_format($stats['pending_reports']); ?></h2>
                                    </div>
                                    <div class="stat-icon">
                                        <i class="bi bi-flag"></i>
                                    </div>
                                </div>
                                <a href="reports.php" class="text-decoration-none small">Review <i class="bi bi-arrow-right"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Recent Activity -->
                <div class="row g-4 mt-2">
                    <!-- Recent Users -->
                    <div class="col-xl-6">
                        <div class="card">
                            <div class="card-header bg-white">
                                <h5 class="mb-0 fw-bold"><i class="bi bi-person-plus"></i> Recent Users</h5>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th>User</th>
                                                <th>Email</th>
                                                <th>Verified</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (count($recent_users) > 0): ?>
                                                <?php foreach ($recent_users as $user): ?>
                                                    <tr>
                                                        <td>
                                                            <div class="d-flex align-items-center">
                                                                <div class="avatar-sm bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 32px; height: 32px;">
                                                                    <?php echo strtoupper(substr($user['username'], 0, 1)); ?>
                                                                </div>
                                                                <?php echo htmlspecialchars($user['full_name'] ?? $user['username']); ?>
                                                            </div>
                                                        </td>
                                                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                                                        <td>
                                                            <?php if ($user['verified']): ?>
                                                                <span class="badge bg-success"><i class="bi bi-check-circle"></i> Verified</span>
                                                            <?php else: ?>
                                                                <span class="badge bg-warning"><i class="bi bi-clock"></i> Pending</span>
                                                            <?php endif; ?>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <tr><td colspan="3" class="text-center text-muted py-3">No users found</td></tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Recent Listings -->
                    <div class="col-xl-6">
                        <div class="card">
                            <div class="card-header bg-white">
                                <h5 class="mb-0 fw-bold"><i class="bi bi-clock-history"></i> Recent Listings</h5>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Title</th>
                                                <th>Price</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (count($recent_listings) > 0): ?>
                                                <?php foreach ($recent_listings as $listing): ?>
                                                    <tr>
                                                        <td><?php echo htmlspecialchars($listing['title'] ?? 'Untitled'); ?></td>
                                                        <td>RM <?php echo number_format($listing['price'] ?? 0, 2); ?></td>
                                                        <td>
                                                            <?php
                                                            $statusColors = [
                                                                'active' => 'success',
                                                                'pending' => 'warning',
                                                                'sold' => 'info',
                                                                'removed' => 'danger'
                                                            ];
                                                            $color = $statusColors[$listing['status'] ?? 'active'] ?? 'secondary';
                                                            ?>
                                                            <span class="badge bg-<?php echo $color; ?>">
                                                                <?php echo ucfirst($listing['status'] ?? 'Unknown'); ?>
                                                            </span>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <tr><td colspan="3" class="text-center text-muted py-3">No listings found</td></tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Footer -->
            <?php include 'includes/footer.php'; ?>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>