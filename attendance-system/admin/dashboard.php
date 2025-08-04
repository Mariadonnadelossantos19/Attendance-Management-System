<?php
require_once '../init.php';
requireAdmin();

// Get statistics
$today = date('Y-m-d');
$current_month = date('Y-m');

// Total employees
$stmt = $pdo->prepare("SELECT COUNT(*) FROM employees WHERE status = 'active'");
$stmt->execute();
$total_employees = $stmt->fetchColumn();

// Present today
$stmt = $pdo->prepare("SELECT COUNT(*) FROM attendance WHERE date = ? AND status = 'present'");
$stmt->execute([$today]);
$present_today = $stmt->fetchColumn();

// Absent today
$stmt = $pdo->prepare("SELECT COUNT(*) FROM attendance WHERE date = ? AND status = 'absent'");
$stmt->execute([$today]);
$absent_today = $stmt->fetchColumn();

// Late today
$stmt = $pdo->prepare("SELECT COUNT(*) FROM attendance WHERE date = ? AND status = 'late'");
$stmt->execute([$today]);
$late_today = $stmt->fetchColumn();

// Recent attendance records
$stmt = $pdo->prepare("
    SELECT a.*, e.full_name, e.employee_id 
    FROM attendance a 
    JOIN employees e ON a.employee_id = e.id 
    WHERE a.date = ? 
    ORDER BY a.time_in DESC 
    LIMIT 10
");
$stmt->execute([$today]);
$recent_attendance = $stmt->fetchAll(PDO::FETCH_ASSOC);
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

        <div class="container-fluid px-4">
            <!-- Welcome Section -->
            <div class="row mb-4">
                <div class="col-12">
                    <h1 class="h3 mb-0">Dashboard Overview</h1>
                    <p class="text-muted">Here's what's happening today</p>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="row mb-4">
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-primary shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                        Total Employees
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $total_employees; ?></div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-users fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-success shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                        Present Today
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $present_today; ?></div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-warning shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                        Late Today
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $late_today; ?></div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-clock fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-danger shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                        Absent Today
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $absent_today; ?></div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-times-circle fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card shadow">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Quick Actions</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3 mb-3">
                                    <a href="create_employee_account.php" class="btn btn-primary w-100">
                                        <i class="fas fa-user-plus me-2"></i>Create Employee Account
                                    </a>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <a href="reports.php" class="btn btn-info w-100">
                                        <i class="fas fa-chart-line me-2"></i>View Reports
                                    </a>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <a href="export.php" class="btn btn-success w-100">
                                        <i class="fas fa-file-excel me-2"></i>Export Data
                                    </a>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <a href="employees.php" class="btn btn-warning w-100">
                                        <i class="fas fa-users-cog me-2"></i>Manage Employees
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Attendance -->
            <div class="row">
                <div class="col-12">
                    <div class="card shadow">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Today's Attendance</h6>
                        </div>
                        <div class="card-body">
                            <?php if (empty($recent_attendance)): ?>
                                <p class="text-muted text-center">No attendance records for today yet.</p>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th>Employee ID</th>
                                                <th>Name</th>
                                                <th>Time In</th>
                                                <th>Time Out</th>
                                                <th>Hours</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($recent_attendance as $record): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($record['employee_id']); ?></td>
                                                    <td><?php echo htmlspecialchars($record['full_name']); ?></td>
                                                    <td>
                                                        <?php echo $record['time_in'] ? formatTime($record['time_in']) : '-'; ?>
                                                    </td>
                                                    <td>
                                                        <?php echo $record['time_out'] ? formatTime($record['time_out']) : '-'; ?>
                                                    </td>
                                                    <td><?php echo $record['total_hours']; ?> hrs</td>
                                                    <td>
                                                        <span class="badge bg-<?php 
                                                            echo $record['status'] === 'present' ? 'success' : 
                                                                ($record['status'] === 'late' ? 'warning' : 'danger'); 
                                                        ?>">
                                                            <?php echo ucfirst($record['status']); ?>
                                                        </span>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('mainContent');
            
            sidebar.classList.toggle('collapsed');
            mainContent.classList.toggle('expanded');
        }

        // Mobile menu toggle
        function toggleMobileSidebar() {
            const sidebar = document.getElementById('sidebar');
            sidebar.classList.toggle('show');
        }

        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', function(event) {
            const sidebar = document.getElementById('sidebar');
            const toggleBtn = document.querySelector('.toggle-sidebar');
            
            if (window.innerWidth <= 768) {
                if (!sidebar.contains(event.target) && !toggleBtn.contains(event.target)) {
                    sidebar.classList.remove('show');
                }
            }
        });

        // Handle window resize
        window.addEventListener('resize', function() {
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('mainContent');
            
            if (window.innerWidth <= 768) {
                sidebar.classList.remove('collapsed');
                mainContent.classList.remove('expanded');
            }
        });
    </script>
</body>
</html>