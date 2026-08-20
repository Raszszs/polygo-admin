<?php
require_once 'config/database.php';

// If already logged in, redirect to dashboard
if (isAdminLoggedIn()) {
    header('Location: dashboard.php');
    exit();
}

$error = '';
$debug = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        $error = 'Please enter both email and password.';
    } else {
        try {
            $pdo = getConnection();
            
            // DEBUG: Check if connection works
            $debug .= "Connected to database.\n";
            
            // Check if user exists
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND role = 'admin'");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            if ($user) {
                $debug .= "User found: " . $user['email'] . "\n";
                $debug .= "User role: " . $user['role'] . "\n";
                $debug .= "User status: " . $user['status'] . "\n";
                $debug .= "Stored password hash: " . $user['password'] . "\n";
                
                // Check password
                if (password_verify($password, $user['password'])) {
                    $debug .= "Password verified!\n";
                    
                    // Check if status is active
                    if ($user['status'] === 'active') {
                        $_SESSION['user_id'] = $user['user_id'];
                        $_SESSION['username'] = $user['username'];
                        $_SESSION['full_name'] = $user['full_name'];
                        $_SESSION['role'] = $user['role'];
                        $_SESSION['email'] = $user['email'];
                        
                        header('Location: dashboard.php');
                        exit();
                    } else {
                        $error = 'Your account is suspended. Contact support.';
                    }
                } else {
                    $debug .= "Password verification FAILED.\n";
                    $error = 'Invalid email or password.';
                }
            } else {
                $debug .= "No admin user found with email: " . $email . "\n";
                $error = 'Invalid email or password.';
            }
        } catch (PDOException $e) {
            $error = 'Login failed: ' . $e->getMessage();
            $debug .= "PDO Error: " . $e->getMessage() . "\n";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - PolyGo+</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .login-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            padding: 40px;
            width: 100%;
            max-width: 420px;
        }
        .login-card .logo {
            text-align: center;
            margin-bottom: 30px;
        }
        .login-card .logo i {
            font-size: 48px;
            color: #667eea;
        }
        .login-card .logo h3 {
            margin-top: 10px;
            color: #333;
            font-weight: 600;
        }
        .login-card .logo p {
            color: #666;
            font-size: 14px;
        }
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            padding: 12px;
            font-weight: 600;
            width: 100%;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.4);
        }
        .debug-box {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            padding: 10px;
            font-size: 12px;
            max-height: 200px;
            overflow: auto;
            white-space: pre-wrap;
            word-wrap: break-word;
        }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="logo">
            <i class="bi bi-shop"></i>
            <h3>PolyGo+ Admin</h3>
            <p>Marketplace Management System</p>
        </div>
        
        <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <?php if ($debug): ?>
            <div class="alert alert-info">
                <strong>Debug Info:</strong>
                <div class="debug-box mt-2"><?php echo nl2br(htmlspecialchars($debug)); ?></div>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="mb-3">
                <label for="email" class="form-label">Email Address</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                    <input type="email" class="form-control" id="email" name="email" 
                           placeholder="admin@politeknik.edu.my" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
                </div>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-lock"></i></span>
                    <input type="password" class="form-control" id="password" name="password" 
                           placeholder="Enter your password" required>
                </div>
            </div>
            <div class="mb-3 form-check">
                <input type="checkbox" class="form-check-input" id="remember">
                <label class="form-check-label" for="remember">Remember me</label>
            </div>
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-box-arrow-in-right"></i> Sign In
            </button>
        </form>
        <hr>
        <p class="text-center text-muted small mb-0">
            <i class="bi bi-shield-check"></i> Secure Admin Access
        </p>
        <p class="text-center text-muted small mt-2">
            Default: admin@politeknik.edu.my / admin123
        </p>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>