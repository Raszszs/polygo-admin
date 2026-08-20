<?php
require_once 'config/database.php';
requireAdmin();

$pdo = getConnection();
$message = '';
$error = '';

// Handle listing actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $listing_id = $_POST['listing_id'] ?? 0;
    
    if ($action === 'approve' && $listing_id) {
        try {
            $stmt = $pdo->prepare("UPDATE listings SET status = 'active' WHERE listing_id = ?");
            $stmt->execute([$listing_id]);
            $message = "Listing approved successfully!";
        } catch (PDOException $e) {
            $error = "Failed to approve listing.";
        }
    }
    
    if ($action === 'reject' && $listing_id) {
        try {
            $stmt = $pdo->prepare("UPDATE listings SET status = 'removed' WHERE listing_id = ?");
            $stmt->execute([$listing_id]);
            $message = "Listing removed successfully!";
        } catch (PDOException $e) {
            $error = "Failed to remove listing.";
        }
    }
    
    if ($action === 'delete' && $listing_id) {
        try {
            $stmt = $pdo->prepare("DELETE FROM listings WHERE listing_id = ?");
            $stmt->execute([$listing_id]);
            $message = "Listing deleted successfully!";
        } catch (PDOException $e) {
            $error = "Failed to delete listing.";
        }
    }
}

// Get filter parameters
$search = $_GET['search'] ?? '';
$status = $_GET['status'] ?? '';
$type = $_GET['type'] ?? '';

// Build query
$query = "SELECT l.*, u.username, u.full_name, c.category_name 
          FROM listings l 
          LEFT JOIN users u ON l.user_id = u.user_id 
          LEFT JOIN categories c ON l.category_id = c.category_id 
          WHERE 1=1";
$params = [];

if ($search) {
    $query .= " AND (l.title LIKE ? OR l.description LIKE ?)";
    $searchParam = "%$search%";
    $params = array_merge($params, [$searchParam, $searchParam]);
}

if ($status) {
    $query .= " AND l.status = ?";
    $params[] = $status;
}

if ($type) {
    $query .= " AND l.listing_type = ?";
    $params[] = $type;
}

$query .= " ORDER BY l.created_at DESC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$listings = $stmt->fetchAll();

// Get statistics
$stmt = $pdo->query("SELECT COUNT(*) as total FROM listings");
$total_listings = $stmt->fetch()['total'];

$stmt = $pdo->query("SELECT COUNT(*) as total FROM listings WHERE status = 'pending'");
$pending_listings = $stmt->fetch()['total'];

$stmt = $pdo->query("SELECT COUNT(*) as total FROM listings WHERE status = 'active'");
$active_listings = $stmt->fetch()['total'];

$stmt = $pdo->query("SELECT COUNT(*) as total FROM listings WHERE status = 'sold'");
$sold_listings = $stmt->fetch()['total'];

$stmt = $pdo->query("SELECT COUNT(*) as total FROM listings WHERE status = 'removed'");
$removed_listings = $stmt->fetch()['total'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Listings - PolyGo+ Admin</title>
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
                                    <i class="bi bi-box"></i> Listing Management
                                </h3>
                                <p class="text-muted">Manage all product and service listings</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Statistics Row -->
                <div class="row g-3 mb-4">
                    <div class="col-xl-2 col-md-4">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body">
                                <h6 class="text-muted mb-1">Total</h6>
                                <h3 class="fw-bold"><?php echo $total_listings; ?></h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-2 col-md-4">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body">
                                <h6 class="text-muted mb-1">Pending</h6>
                                <h3 class="fw-bold text-warning"><?php echo $pending_listings; ?></h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-2 col-md-4">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body">
                                <h6 class="text-muted mb-1">Active</h6>
                                <h3 class="fw-bold text-success"><?php echo $active_listings; ?></h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-2 col-md-4">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body">
                                <h6 class="text-muted mb-1">Sold</h6>
                                <h3 class="fw-bold text-info"><?php echo $sold_listings; ?></h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-2 col-md-4">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body">
                                <h6 class="text-muted mb-1">Removed</h6>
                                <h3 class="fw-bold text-danger"><?php echo $removed_listings; ?></h3>
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
                                       placeholder="Search listings..." 
                                       value="<?php echo htmlspecialchars($search); ?>">
                            </div>
                            <div class="col-md-3">
                                <select class="form-select" name="status">
                                    <option value="">All Status</option>
                                    <option value="pending" <?php echo $status === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                    <option value="active" <?php echo $status === 'active' ? 'selected' : ''; ?>>Active</option>
                                    <option value="sold" <?php echo $status === 'sold' ? 'selected' : ''; ?>>Sold</option>
                                    <option value="removed" <?php echo $status === 'removed' ? 'selected' : ''; ?>>Removed</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <select class="form-select" name="type">
                                    <option value="">All Types</option>
                                    <option value="product" <?php echo $type === 'product' ? 'selected' : ''; ?>>Product</option>
                                    <option value="service" <?php echo $type === 'service' ? 'selected' : ''; ?>>Service</option>
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
                
                <!-- Listings Table -->
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>#</th>
                                        <th>Title</th>
                                        <th>Seller</th>
                                        <th>Category</th>
                                        <th>Price</th>
                                        <th>Type</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (count($listings) > 0): ?>
                                        <?php $count = 1; ?>
                                        <?php foreach ($listings as $listing): ?>
                                            <tr>
                                                <td><?php echo $count++; ?></td>
                                                <td>
                                                    <div>
                                                        <div class="fw-semibold"><?php echo htmlspecialchars($listing['title']); ?></div>
                                                        <small class="text-muted">
                                                            <?php echo date('d/m/Y', strtotime($listing['created_at'])); ?>
                                                        </small>
                                                    </div>
                                                </td>
                                                <td>
                                                    <?php echo htmlspecialchars($listing['full_name'] ?? $listing['username']); ?>
                                                    <small class="d-block text-muted">@<?php echo htmlspecialchars($listing['username']); ?></small>
                                                </td>
                                                <td><?php echo htmlspecialchars($listing['category_name'] ?? 'Uncategorized'); ?></td>
                                                <td>
                                                    <?php if ($listing['price']): ?>
                                                        <span class="fw-bold">RM <?php echo number_format($listing['price'], 2); ?></span>
                                                    <?php else: ?>
                                                        <span class="text-muted">Free</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if ($listing['listing_type'] === 'product'): ?>
                                                        <span class="badge bg-primary">Product</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-info">Service</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php
                                                    $statusColors = [
                                                        'pending' => 'warning',
                                                        'active' => 'success',
                                                        'sold' => 'info',
                                                        'removed' => 'danger'
                                                    ];
                                                    $color = $statusColors[$listing['status']] ?? 'secondary';
                                                    ?>
                                                    <span class="badge bg-<?php echo $color; ?>">
                                                        <?php echo ucfirst($listing['status']); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="btn-group btn-group-sm">
                                                        <?php if ($listing['status'] === 'pending'): ?>
                                                            <form method="POST" class="d-inline">
                                                                <input type="hidden" name="listing_id" value="<?php echo $listing['listing_id']; ?>">
                                                                <input type="hidden" name="action" value="approve">
                                                                <button type="submit" class="btn btn-success" title="Approve Listing">
                                                                    <i class="bi bi-check2"></i>
                                                                </button>
                                                            </form>
                                                            <form method="POST" class="d-inline">
                                                                <input type="hidden" name="listing_id" value="<?php echo $listing['listing_id']; ?>">
                                                                <input type="hidden" name="action" value="reject">
                                                                <button type="submit" class="btn btn-danger" title="Reject Listing"
                                                                        onclick="return confirm('Reject this listing?')">
                                                                    <i class="bi bi-x"></i>
                                                                </button>
                                                            </form>
                                                        <?php endif; ?>
                                                        
                                                        <?php if ($listing['status'] === 'active'): ?>
                                                            <a href="#" class="btn btn-primary" title="View Listing">
                                                                <i class="bi bi-eye"></i>
                                                            </a>
                                                        <?php endif; ?>
                                                        
                                                        <form method="POST" class="d-inline">
                                                            <input type="hidden" name="listing_id" value="<?php echo $listing['listing_id']; ?>">
                                                            <input type="hidden" name="action" value="delete">
                                                            <button type="submit" class="btn btn-danger" title="Delete Permanently"
                                                                    onclick="return confirm('Delete this listing permanently? This cannot be undone!')">
                                                                <i class="bi bi-trash"></i>
                                                            </button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="8" class="text-center text-muted py-4">
                                                <i class="bi bi-box fs-2 d-block mb-2"></i>
                                                No listings found
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