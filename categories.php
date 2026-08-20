<?php
require_once 'config/database.php';
requireAdmin();

$pdo = getConnection();
$message = '';
$error = '';

// Handle category actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $category_id = $_POST['category_id'] ?? 0;
    
    // Add category
    if ($action === 'add') {
        $category_name = trim($_POST['category_name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $icon = trim($_POST['icon'] ?? 'bi-tag');
        
        if (empty($category_name)) {
            $error = "Category name is required.";
        } else {
            try {
                $stmt = $pdo->prepare("INSERT INTO categories (category_name, description, icon) VALUES (?, ?, ?)");
                $stmt->execute([$category_name, $description, $icon]);
                $message = "Category added successfully!";
            } catch (PDOException $e) {
                if ($e->getCode() == 23000) {
                    $error = "Category already exists!";
                } else {
                    $error = "Failed to add category: " . $e->getMessage();
                }
            }
        }
    }
    
    // Edit category
    if ($action === 'edit') {
        $category_name = trim($_POST['category_name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $icon = trim($_POST['icon'] ?? 'bi-tag');
        
        if (empty($category_name)) {
            $error = "Category name is required.";
        } else {
            try {
                $stmt = $pdo->prepare("UPDATE categories SET category_name = ?, description = ?, icon = ? WHERE category_id = ?");
                $stmt->execute([$category_name, $description, $icon, $category_id]);
                $message = "Category updated successfully!";
            } catch (PDOException $e) {
                $error = "Failed to update category.";
            }
        }
    }
    
    // Delete category
    if ($action === 'delete' && $category_id) {
        try {
            // Check if category has listings
            $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM listings WHERE category_id = ?");
            $stmt->execute([$category_id]);
            $count = $stmt->fetch()['count'];
            
            if ($count > 0) {
                $error = "Cannot delete category. It has $count listings associated with it.";
            } else {
                $stmt = $pdo->prepare("DELETE FROM categories WHERE category_id = ?");
                $stmt->execute([$category_id]);
                $message = "Category deleted successfully!";
            }
        } catch (PDOException $e) {
            $error = "Failed to delete category.";
        }
    }
}

// Get all categories
$stmt = $pdo->query("SELECT * FROM categories ORDER BY category_name");
$categories = $stmt->fetchAll();

// Get count of listings per category
$stmt = $pdo->query("SELECT category_id, COUNT(*) as count FROM listings GROUP BY category_id");
$category_counts = [];
while ($row = $stmt->fetch()) {
    $category_counts[$row['category_id']] = $row['count'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Categories - PolyGo+ Admin</title>
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
                                    <i class="bi bi-tags"></i> Category Management
                                </h3>
                                <p class="text-muted">Manage product and service categories</p>
                            </div>
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
                                <i class="bi bi-plus-circle"></i> Add Category
                            </button>
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
                
                <!-- Categories Table -->
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>#</th>
                                        <th>Icon</th>
                                        <th>Category Name</th>
                                        <th>Description</th>
                                        <th>Listings</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (count($categories) > 0): ?>
                                        <?php $count = 1; ?>
                                        <?php foreach ($categories as $category): ?>
                                            <tr>
                                                <td><?php echo $count++; ?></td>
                                                <td>
                                                    <span class="badge bg-primary p-2">
                                                        <i class="bi <?php echo htmlspecialchars($category['icon'] ?? 'bi-tag'); ?> fs-5"></i>
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="fw-semibold"><?php echo htmlspecialchars($category['category_name']); ?></span>
                                                </td>
                                                <td><?php echo htmlspecialchars($category['description'] ?? '-'); ?></td>
                                                <td>
                                                    <span class="badge bg-secondary">
                                                        <?php echo $category_counts[$category['category_id']] ?? 0; ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="btn-group btn-group-sm">
                                                        <button type="button" class="btn btn-warning" 
                                                                data-bs-toggle="modal" 
                                                                data-bs-target="#editCategoryModal"
                                                                data-id="<?php echo $category['category_id']; ?>"
                                                                data-name="<?php echo htmlspecialchars($category['category_name']); ?>"
                                                                data-desc="<?php echo htmlspecialchars($category['description'] ?? ''); ?>"
                                                                data-icon="<?php echo htmlspecialchars($category['icon'] ?? 'bi-tag'); ?>">
                                                            <i class="bi bi-pencil"></i>
                                                        </button>
                                                        
                                                        <?php if (($category_counts[$category['category_id']] ?? 0) == 0): ?>
                                                            <form method="POST" class="d-inline">
                                                                <input type="hidden" name="category_id" value="<?php echo $category['category_id']; ?>">
                                                                <input type="hidden" name="action" value="delete">
                                                                <button type="submit" class="btn btn-danger" 
                                                                        onclick="return confirm('Delete this category?')">
                                                                    <i class="bi bi-trash"></i>
                                                                </button>
                                                            </form>
                                                        <?php else: ?>
                                                            <button type="button" class="btn btn-secondary" disabled 
                                                                    title="Cannot delete - has listings">
                                                                <i class="bi bi-trash"></i>
                                                            </button>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="6" class="text-center text-muted py-4">
                                                <i class="bi bi-tags fs-2 d-block mb-2"></i>
                                                No categories found. Click "Add Category" to create one.
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
    
    <!-- Add Category Modal -->
    <div class="modal fade" id="addCategoryModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-plus-circle"></i> Add New Category</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add">
                        
                        <div class="mb-3">
                            <label for="category_name" class="form-label">Category Name *</label>
                            <input type="text" class="form-control" id="category_name" name="category_name" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="2"></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label for="icon" class="form-label">Icon (Bootstrap Icon)</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-tag"></i></span>
                                <input type="text" class="form-control" id="icon" name="icon" value="bi-tag" placeholder="bi-tag">
                            </div>
                            <small class="text-muted">Example: bi-book, bi-laptop, bi-tshirt</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add Category</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Edit Category Modal -->
    <div class="modal fade" id="editCategoryModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-pencil"></i> Edit Category</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="edit">
                        <input type="hidden" name="category_id" id="edit_category_id">
                        
                        <div class="mb-3">
                            <label for="edit_category_name" class="form-label">Category Name *</label>
                            <input type="text" class="form-control" id="edit_category_name" name="category_name" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="edit_description" class="form-label">Description</label>
                            <textarea class="form-control" id="edit_description" name="description" rows="2"></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label for="edit_icon" class="form-label">Icon (Bootstrap Icon)</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-tag"></i></span>
                                <input type="text" class="form-control" id="edit_icon" name="icon" value="bi-tag">
                            </div>
                            <small class="text-muted">Example: bi-book, bi-laptop, bi-tshirt</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Edit modal - populate fields when opened
        document.addEventListener('DOMContentLoaded', function() {
            var editModal = document.getElementById('editCategoryModal');
            editModal.addEventListener('show.bs.modal', function(event) {
                var button = event.relatedTarget;
                document.getElementById('edit_category_id').value = button.getAttribute('data-id');
                document.getElementById('edit_category_name').value = button.getAttribute('data-name');
                document.getElementById('edit_description').value = button.getAttribute('data-desc') || '';
                document.getElementById('edit_icon').value = button.getAttribute('data-icon') || 'bi-tag';
            });
        });
    </script>
</body>
</html>