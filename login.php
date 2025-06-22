<?php
// login.php - Login System for Expense Management
session_start();

// Database connection (using the same database as your main system)
class Database {
    private $host = "localhost";
    private $db_name = "expense_management";
    private $username = "root";
    private $password = "";
    public $conn;

    public function getConnection() {
        $this->conn = null;
        try {
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name, $this->username, $this->password);
            $this->conn->exec("set names utf8");
        } catch(PDOException $exception) {
            echo "Connection error: " . $exception->getMessage();
        }
        return $this->conn;
    }
}

// Create users table if it doesn't exist
function createUsersTable() {
    try {
        $database = new Database();
        $db = $database->getConnection();
        
        $createTable = "
        CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) UNIQUE NOT NULL,
            email VARCHAR(100) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL,
            full_name VARCHAR(100) NOT NULL,
            role ENUM('admin', 'manager', 'employee') DEFAULT 'employee',
            status ENUM('active', 'inactive') DEFAULT 'active',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )";
        
        $db->exec($createTable);
        
        // Create default admin user if no users exist
        $checkQuery = "SELECT COUNT(*) FROM users";
        $stmt = $db->prepare($checkQuery);
        $stmt->execute();
        $userCount = $stmt->fetchColumn();
        
        if ($userCount == 0) {
            $insertAdmin = "INSERT INTO users (username, email, password, full_name, role) VALUES 
                          ('admin', 'admin@company.com', :password, 'System Administrator', 'admin')";
            $stmt = $db->prepare($insertAdmin);
            $hashedPassword = password_hash('admin123', PASSWORD_DEFAULT);
            $stmt->bindParam(':password', $hashedPassword);
            $stmt->execute();
        }
        
        return true;
    } catch(PDOException $e) {
        return false;
    }
}

// Initialize database
createUsersTable();

// Handle login
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login'])) {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    
    if (!empty($username) && !empty($password)) {
        $database = new Database();
        $db = $database->getConnection();
        
        $query = "SELECT id, username, email, password, full_name, role, status FROM users WHERE (username = :username OR email = :username) AND status = 'active'";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':username', $username);
        $stmt->execute();
        
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['logged_in'] = true;
            
            header("Location: index.php");
            exit();
        } else {
            $error = "Invalid username/email or password!";
        }
    } else {
        $error = "Please fill in all fields!";
    }
}

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: login.php");
    exit();
}

// Handle registration
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['register'])) {
    $username = trim($_POST['reg_username']);
    $email = trim($_POST['reg_email']);
    $password = $_POST['reg_password'];
    $confirm_password = $_POST['reg_confirm_password'];
    $full_name = trim($_POST['reg_full_name']);
    
    $reg_errors = [];
    
    // Validation
    if (empty($username)) $reg_errors[] = "Username is required";
    if (empty($email)) $reg_errors[] = "Email is required";
    if (empty($password)) $reg_errors[] = "Password is required";
    if (empty($full_name)) $reg_errors[] = "Full name is required";
    if ($password !== $confirm_password) $reg_errors[] = "Passwords do not match";
    if (strlen($password) < 6) $reg_errors[] = "Password must be at least 6 characters";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $reg_errors[] = "Invalid email format";
    
    if (empty($reg_errors)) {
        $database = new Database();
        $db = $database->getConnection();
        
        // Check if username or email already exists
        $checkQuery = "SELECT id FROM users WHERE username = :username OR email = :email";
        $stmt = $db->prepare($checkQuery);
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        
        if ($stmt->fetch()) {
            $reg_errors[] = "Username or email already exists";
        } else {
            // Insert new user
            $insertQuery = "INSERT INTO users (username, email, password, full_name, role) VALUES (:username, :email, :password, :full_name, 'employee')";
            $stmt = $db->prepare($insertQuery);
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            
            $stmt->bindParam(':username', $username);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':password', $hashedPassword);
            $stmt->bindParam(':full_name', $full_name);
            
            if ($stmt->execute()) {
                $success = "Registration successful! You can now login.";
            } else {
                $reg_errors[] = "Registration failed. Please try again.";
            }
        }
    }
}

// Redirect if already logged in
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Expense Management System</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.1.3/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
        }
        .login-container {
            max-width: 450px;
            margin: 0 auto;
        }
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        .card-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px 15px 0 0 !important;
            text-align: center;
            padding: 2rem 1rem 1rem;
        }
        .card-body {
            padding: 2rem;
        }
        .form-control {
            border-radius: 10px;
            border: 1px solid #ddd;
            padding: 12px 15px;
        }
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 10px;
            padding: 12px 30px;
            font-weight: 600;
        }
        .btn-primary:hover {
            background: linear-gradient(135deg, #5a6fd8 0%, #6a4190 100%);
        }
        .btn-outline-primary {
            border-color: #667eea;
            color: #667eea;
            border-radius: 10px;
            padding: 12px 30px;
            font-weight: 600;
        }
        .btn-outline-primary:hover {
            background: #667eea;
            border-color: #667eea;
        }
        .nav-tabs .nav-link {
            border-radius: 10px 10px 0 0;
            border: none;
            color: #667eea;
            font-weight: 600;
        }
        .nav-tabs .nav-link.active {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        .tab-content {
            border-radius: 0 0 10px 10px;
            padding: 1rem 0;
        }
        .login-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
        }
        .alert {
            border-radius: 10px;
        }
        .demo-info {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 1rem;
            margin-top: 1rem;
            border-left: 4px solid #667eea;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="login-container">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-calculator login-icon"></i>
                    <h3>Expense Management System</h3>
                    <p class="mb-0">Please login to continue</p>
                </div>
                <div class="card-body">
                    <!-- Nav tabs -->
                    <ul class="nav nav-tabs" id="authTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="login-tab" data-bs-toggle="tab" data-bs-target="#login" type="button" role="tab">
                                <i class="fas fa-sign-in-alt"></i> Login
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="register-tab" data-bs-toggle="tab" data-bs-target="#register" type="button" role="tab">
                                <i class="fas fa-user-plus"></i> Register
                            </button>
                        </li>
                    </ul>

                    <!-- Tab content -->
                    <div class="tab-content" id="authTabsContent">
                        <!-- Login Tab -->
                        <div class="tab-pane fade show active" id="login" role="tabpanel">
                            <?php if (isset($error)): ?>
                                <div class="alert alert-danger">
                                    <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                                </div>
                            <?php endif; ?>

                            <form method="POST">
                                <div class="mb-3">
                                    <label for="username" class="form-label">Username or Email</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-user"></i></span>
                                        <input type="text" class="form-control" id="username" name="username" required>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="password" class="form-label">Password</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                        <input type="password" class="form-control" id="password" name="password" required>
                                    </div>
                                </div>
                                <div class="d-grid">
                                    <button type="submit" name="login" class="btn btn-primary">
                                        <i class="fas fa-sign-in-alt"></i> Login
                                    </button>
                                </div>
                            </form>

                            <div class="demo-info">
                                <h6><i class="fas fa-info-circle"></i> Demo Login</h6>
                                <p class="mb-1"><strong>Username:</strong> admin</p>
                                <p class="mb-0"><strong>Password:</strong> admin123</p>
                            </div>
                        </div>

                        <!-- Register Tab -->
                        <div class="tab-pane fade" id="register" role="tabpanel">
                            <?php if (isset($reg_errors) && !empty($reg_errors)): ?>
                                <div class="alert alert-danger">
                                    <i class="fas fa-exclamation-circle"></i>
                                    <ul class="mb-0">
                                        <?php foreach ($reg_errors as $error): ?>
                                            <li><?php echo $error; ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            <?php endif; ?>

                            <?php if (isset($success)): ?>
                                <div class="alert alert-success">
                                    <i class="fas fa-check-circle"></i> <?php echo $success; ?>
                                </div>
                            <?php endif; ?>

                            <form method="POST">
                                <div class="mb-3">
                                    <label for="reg_full_name" class="form-label">Full Name</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-id-card"></i></span>
                                        <input type="text" class="form-control" id="reg_full_name" name="reg_full_name" value="<?php echo isset($_POST['reg_full_name']) ? htmlspecialchars($_POST['reg_full_name']) : ''; ?>" required>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="reg_username" class="form-label">Username</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-user"></i></span>
                                        <input type="text" class="form-control" id="reg_username" name="reg_username" value="<?php echo isset($_POST['reg_username']) ? htmlspecialchars($_POST['reg_username']) : ''; ?>" required>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="reg_email" class="form-label">Email</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                        <input type="email" class="form-control" id="reg_email" name="reg_email" value="<?php echo isset($_POST['reg_email']) ? htmlspecialchars($_POST['reg_email']) : ''; ?>" required>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="reg_password" class="form-label">Password</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                        <input type="password" class="form-control" id="reg_password" name="reg_password" required>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="reg_confirm_password" class="form-label">Confirm Password</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                        <input type="password" class="form-control" id="reg_confirm_password" name="reg_confirm_password" required>
                                    </div>
                                </div>
                                <div class="d-grid">
                                    <button type="submit" name="register" class="btn btn-outline-primary">
                                        <i class="fas fa-user-plus"></i> Register
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.1.3/js/bootstrap.bundle.min.js"></script>
</body>
</html>