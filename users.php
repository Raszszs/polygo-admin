<?php
require_once 'config/database.php';
requireAdmin();

$pdo = getConnection();
$message = '';
$error = '';

// Handle user actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $user_id = $_POST['user_id'] ?? 0;
    
    if ($action === 'verify' && $user_id) {
        try {
            $stmt = $pdo->prepare("UPDATE users SET verified = 1 WHERE user_id = ?");
            $stmt->execute([$user_id]);
            $message = "User verified successfully!";
        } catch (PDOException $e) {
            $error = "Failed to verify user.";
        }
    }
    
    if ($action === 'suspend' && $user_id) {
        try {
            $stmt = $pdo->prepare("UPDATE users SET status = 'suspended' WHERE user_id = ?");
            $stmt->execute([$user_id]);
            $message = "User suspended successfully!";
        } catch (PDOException $e) {
            $error = "Failed to suspend user.";
        }
    }
    
    if ($action === 'activate' && $user_id) {
        try {
            $stmt = $pdo->prepare("UPDATE users SET status = 'active' WHERE user_id = ?");
            $stmt->execute([$user_id]);
            $message = "User activated successfully!";
        } catch (PDOException $e) {
            $error = "Failed to activate user.";
        }
    }
    
    if ($action === 'delete' && $user_id) {
        try {
            $stmt = $pdo->prepare("DELETE FROM users WHERE user_id = ? AND role != 'admin'");
            $stmt->execute([$user_id]);
            $message = "User deleted successfully!";
        } catch (PDOException $e) {
            $error = "Failed to delete user.";
        }
    }
}

// Get filter parameters
$search = $_GET['search'] ?? '';
$status = $_GET['status'] ?? '';
$verified = $_GET['verified'] ?? '';

// Build query
$query = "SELECT * FROM users WHERE role = 'user'";
$params = [];

if ($search) {
    $query .= " AND (username LIKE ? OR email LIKE ? OR full_name LIKE ?)";
    $searchParam = "%$search%";
    $params = array_merge($params, [$searchParam, $searchParam, $searchParam]);
}

if ($status) {
    $query .= " AND status = ?";
    $params[] = $status;
}

if ($verified !== '') {
    $query .= " AND verified = ?";
    $params[] = $verified;
}

$query .= " ORDER BY created_at DESC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$users = $stmt->fetchAll();

// Get statistics
$stmt = $pdo->query("SELECT COUNT(*) as total FROM users WHERE role = 'user'");
$total_users = $stmt->fetch()['total'];

$stmt = $pdo->query("SELECT COUNT(*) as total FROM users WHERE role = 'user' AND status = 'active'");
$active_users = $stmt->fetch()['total'];

$stmt = $pdo->query("SELECT COUNT(*) as total FROM users WHERE role = 'user' AND verified = 1");
$verified_users = $stmt->fetch()['total'];

$stmt = $pdo->query("SELECT COUNT(*) as total FROM users WHERE role = 'user' AND status = 'suspended'");
$suspended_users = $stmt->fetch()['total'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Users - PolyGo+ Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
    <div class="d-flex" id="wrapper">
        <?php include 'includes/sidebar.php'; ?>
        
        <div id="page-content-wrapper">
            <?php include 'includes/header.php'; ?>
            
            <div class="container-fluid px-4">
                <!-- Page Header -->
                <div class="row mt-3 mb-4">
                    <div class="col-12">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h3 class="fw-bold">
                                    <i class="bi bi-people"></i> User Management
                                </h3>
                                <p class="text-muted">Manage and verify campus marketplace users</p>
                            </div>
                            <a href="#" class="btn btn-primary">
                                <i class="bi bi-plus-circle"></i> Add User
                            </a>
                        </div>
                    </div>
                </div>
                
                <!-- Statistics Row -->
                <div class="row g-3 mb-4">
                    <div class="col-xl-3 col-md-6">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body">
                                <h6 class="text-muted mb-1">Total Users</h6>
                                <h3 class="fw-bold"><?php echo $total_users; ?></h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body">
                                <h6 class="text-muted mb-1">Active Users</h6>
                                <h3 class="fw-bold text-success"><?php echo $active_users; ?></h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body">
                                <h6 class="text-muted mb-1">Verified Users</h6>
                                <h3 class="fw-bold text-primary"><?php echo $verified_users; ?></h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body">
                                <h6 class="text-muted mb-1">Suspended Users</h6>
                                <h3 class="fw-bold text-danger"><?php echo $suspended_users; ?></h3>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Messages -->
                <?php if ($message): ?>
                    <div class="alert alert-success alert-dismissible fade show">
                        <i class="bi bi-check-circle"></i> <?php echo $message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <?php if ($error): ?>
                    <div class="alert alert-danger alert-dismissible fade show">
                        <i class="bi bi-exclamation-circle"></i> <?php echo $error; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <!-- Filters -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body">
                        <form method="GET" class="row g-3">
                            <div class="col-md-4">
                                <input type="text" class="form-control" name="search" 
                                       placeholder="Search by name or email..." 
                                       value="<?php echo htmlspecialchars($search); ?>">
                            </div>
                            <div class="col-md-3">
                                <select class="form-select" name="status">
                                    <option value="">All Status</option>
                                    <option value="active" <?php echo $status === 'active' ? 'selected' : ''; ?>>Active</option>
                                    <option value="suspended" <?php echo $status === 'suspended' ? 'selected' : ''; ?>>Suspended</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <select class="form-select" name="verified">
                                    <option value="">All Verification</option>
                                    <option value="1" <?php echo $verified === '1' ? 'selected' : ''; ?>>Verified</option>
                                    <option value="0" <?php echo $verified === '0' ? 'selected' : ''; ?>>Unverified</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="bi bi-search"></i> Filter
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- Users Table -->
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>#</th>
                                        <th>User</th>
                                        <th>Email</th>
                                        <th>Institution</th>
                                        <th>Status</th>
                                        <th>Verified</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (count($users) > 0): ?>
                                        <?php $count = 1; ?>
                                        <?php foreach ($users as $user): ?>
                                            <tr>
                                                <td><?php echo $count++; ?></td>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="avatar-sm bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2" 
                                                             style="width: 36px; height: 36px; font-size: 14px; font-weight: bold;">
                                                            <?php echo strtoupper(substr($user['full_name'] ?? $user['username'], 0, 1)); ?>
                                                        </div>
                                                        <div>
                                                            <div class="fw-semibold"><?php echo htmlspecialchars($user['full_name'] ?? $user['username']); ?></div>
                                                            <small class="text-muted">@<?php echo htmlspecialchars($user['username']); ?></small>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                                <td><?php echo htmlspecialchars($user['institution'] ?? 'Not set'); ?></td>
                                                <td>
                                                    <?php if ($user['status'] === 'active'): ?>
                                                        <span class="badge bg-success">Active</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-danger">Suspended</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if ($user['verified']): ?>
                                                        <span class="badge bg-primary">
                                                            <i class="bi bi-check-circle"></i> Verified
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="badge bg-warning">
                                                            <i class="bi bi-clock"></i> Pending
                                                        </span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <div class="btn-group btn-group-sm">
                                                        <?php if (!$user['verified']): ?>
                                                            <form method="POST" class="d-inline">
                                                                <input type="hidden" name="user_id" value="<?php echo $user['user_id']; ?>">
                                                                <input type="hidden" name="action" value="verify">
                                                                <button type="submit" class="btn btn-success" title="Verify User">
                                                                    <i class="bi bi-check2-circle"></i>
                                                                </button>
                                                            </form>
                                                        <?php endif; ?>
                                                        
                                                        <?php if ($user['status'] === 'active'): ?>
                                                            <form method="POST" class="d-inline">
                                                                <input type="hidden" name="user_id" value="<?php echo $user['user_id']; ?>">
                                                                <input type="hidden" name="action" value="suspend">
                                                                <button type="submit" class="btn btn-warning" title="Suspend User" 
                                                                        onclick="return confirm('Suspend this user?')">
                                                                    <i class="bi bi-pause-circle"></i>
                                                                </button>
                                                            </form>
                                                        <?php else: ?>
                                                            <form method="POST" class="d-inline">
                                                                <input type="hidden" name="user_id" value="<?php echo $user['user_id']; ?>">
                                                                <input type="hidden" name="action" value="activate">
                                                                <button type="submit" class="btn btn-success" title="Activate User">
                                                                    <i class="bi bi-play-circle"></i>
                                                                </button>
                                                            </form>
                                                        <?php endif; ?>
                                                        
                                                        <form method="POST" class="d-inline">
                                                            <input type="hidden" name="user_id" value="<?php echo $user['user_id']; ?>">
                                                            <input type="hidden" name="action" value="delete">
                                                            <button type="submit" class="btn btn-danger" title="Delete User" 
                                                                    onclick="return confirm('Delete this user permanently? This cannot be undone!')">
                                                                <i class="bi bi-trash"></i>
                                                            </button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="7" class="text-center text-muted py-4">
                                                <i class="bi bi-people fs-2 d-block mb-2"></i>
                                                No users found
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            
            <?php include 'includes/footer.php'; ?>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>