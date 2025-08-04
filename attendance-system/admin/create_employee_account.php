<?php
require_once '../init.php';
requireAdmin();

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $employee_id = trim($_POST['employee_id']);
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $department = trim($_POST['department']);
    $position = trim($_POST['position']);
    $hire_date = $_POST['hire_date'];
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    
    if (empty($employee_id) || empty($full_name) || empty($email) || empty($username) || empty($password)) {
        $error = 'All fields are required.';
    } else {
        try {
            $pdo->beginTransaction();
            
            // Insert employee record
            $stmt = $pdo->prepare("INSERT INTO employees (employee_id, full_name, email, department, position, hire_date) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$employee_id, $full_name, $email, $department, $position, $hire_date]);
            $employee_db_id = $pdo->lastInsertId();
            
            // Insert user account
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (username, password, email, full_name, role) VALUES (?, ?, ?, ?, 'employee')");
            $stmt->execute([$username, $hashed_password, $email, $full_name]);
            
            $pdo->commit();
            $message = "Employee account created successfully! Employee can now login with username: $username";
            
        } catch (PDOException $e) {
            $pdo->rollBack();
            $error = 'Error creating employee account: ' . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Attendance Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/dashboard.css" rel="stylesheet">
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <h4><i class="fas fa-clock me-2"></i>AttendanceMS</h4>
        </div>
        
        <nav class="sidebar-menu">
            <a class="nav-link active" href="dashboard.php">
                <i class="fas fa-tachometer-alt"></i>
                <span>Dashboard</span>
            </a>
            <a class="nav-link" href="employees.php">
                <i class="fas fa-users"></i>
                <span>Employees</span>
            </a>
            <a class="nav-link" href="reports.php">
                <i class="fas fa-chart-bar"></i>
                <span>Reports</span>
            </a>
            <a class="nav-link" href="export.php">
                <i class="fas fa-download"></i>
                <span>Export</span>
            </a>
        </nav>
        
        <div class="user-profile">
            <div class="user-info">
                <div class="fw-bold"><?php echo htmlspecialchars($_SESSION['full_name']); ?></div>
                <small class="text-light">Administrator</small>
            </div>
            <a href="../auth/logout.php" class="logout-btn">
                <i class="fas fa-sign-out-alt me-2"></i>
                <span>Logout</span>
            </a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content" id="mainContent">
        <!-- Top Navigation Bar -->
        <div class="top-navbar d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center">
                <button class="toggle-sidebar me-3" onclick="toggleSidebar()">
                    <i class="fas fa-bars"></i>
                </button>
                <h5 class="mb-0">Dashboard</h5>
            </div>
            <div class="d-flex align-items-center">
                <span class="text-muted">Welcome back, <?php echo htmlspecialchars($_SESSION['full_name']); ?>!</span>
            </div>
        </div>

    <div class="container-fluid mt-4">
        <div class="row">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1 class="h3 mb-0">Create Employee Account</h1>
                    <a href="employees.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Back to Employees
                    </a>
                </div>

                <?php if ($message): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i><?php echo htmlspecialchars($message); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-triangle me-2"></i><?php echo htmlspecialchars($error); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <div class="row">
                    <div class="col-md-8">
                        <div class="card shadow">
                            <div class="card-header py-3">
                                <h6 class="m-0 font-weight-bold text-primary">Employee Information</h6>
                            </div>
                            <div class="card-body">
                                <form method="POST">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="employee_id" class="form-label">Employee ID *</label>
                                            <input type="text" class="form-control" id="employee_id" name="employee_id" 
                                                   value="<?php echo isset($_POST['employee_id']) ? htmlspecialchars($_POST['employee_id']) : ''; ?>" required>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="full_name" class="form-label">Full Name *</label>
                                            <input type="text" class="form-control" id="full_name" name="full_name" 
                                                   value="<?php echo isset($_POST['full_name']) ? htmlspecialchars($_POST['full_name']) : ''; ?>" required>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="email" class="form-label">Email *</label>
                                        <input type="email" class="form-control" id="email" name="email" 
                                               value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="department" class="form-label">Department</label>
                                            <input type="text" class="form-control" id="department" name="department" 
                                                   value="<?php echo isset($_POST['department']) ? htmlspecialchars($_POST['department']) : ''; ?>">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="position" class="form-label">Position</label>
                                            <input type="text" class="form-control" id="position" name="position" 
                                                   value="<?php echo isset($_POST['position']) ? htmlspecialchars($_POST['position']) : ''; ?>">
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="hire_date" class="form-label">Hire Date</label>
                                        <input type="date" class="form-control" id="hire_date" name="hire_date" 
                                               value="<?php echo isset($_POST['hire_date']) ? htmlspecialchars($_POST['hire_date']) : date('Y-m-d'); ?>">
                                    </div>
                                    
                                    <hr class="my-4">
                                    
                                    <h6 class="font-weight-bold text-primary mb-3">Login Account Details</h6>
                                    
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="username" class="form-label">Username *</label>
                                            <input type="text" class="form-control" id="username" name="username" 
                                                   value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>" required>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="password" class="form-label">Password *</label>
                                            <input type="password" class="form-control" id="password" name="password" required>
                                        </div>
                                    </div>
                                    
                                    <div class="d-flex gap-2">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-user-plus me-2"></i>Create Employee Account
                                        </button>
                                        <button type="reset" class="btn btn-secondary">
                                            <i class="fas fa-undo me-2"></i>Reset Form
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="card shadow">
                            <div class="card-header py-3">
                                <h6 class="m-0 font-weight-bold text-primary">Instructions</h6>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <h6><i class="fas fa-info-circle text-info me-2"></i>Employee ID</h6>
                                    <p class="small text-muted">Enter a unique employee identifier (e.g., EMP001, EMP002)</p>
                                </div>
                                
                                <div class="mb-3">
                                    <h6><i class="fas fa-info-circle text-info me-2"></i>Email</h6>
                                    <p class="small text-muted">This email will be used to link the employee record with the user account</p>
                                </div>
                                
                                <div class="mb-3">
                                    <h6><i class="fas fa-info-circle text-info me-2"></i>Username</h6>
                                    <p class="small text-muted">Choose a unique username for login (e.g., john.doe, jane.smith)</p>
                                </div>
                                
                                <div class="mb-3">
                                    <h6><i class="fas fa-lightbulb text-warning me-2"></i>Tips</h6>
                                    <ul class="small text-muted">
                                        <li>Use consistent naming for Employee IDs</li>
                                        <li>Make usernames easy to remember</li>
                                        <li>Use strong passwords</li>
                                        <li>Employee can login immediately after creation</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 