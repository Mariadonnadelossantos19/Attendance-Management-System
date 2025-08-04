<?php
require_once '../init.php';
requireLogin();

$message = '';
$error = '';

// Get current employee data
$current_user = getCurrentUser($pdo);
$employee_id = null;

// Find employee record for current user
$stmt = $pdo->prepare("SELECT * FROM employees WHERE email = ?");
$stmt->execute([$current_user['email']]);
$employee = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$employee) {
    $error = 'Employee record not found. Please contact administrator.';
} else {
    $employee_id = $employee['id'];
    
    // Handle time in/out
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
        $today = date('Y-m-d');
        $current_time = date('H:i:s');
        
        if ($_POST['action'] === 'time_in') {
            // Check if already clocked in today
            $stmt = $pdo->prepare("SELECT * FROM attendance WHERE employee_id = ? AND date = ?");
            $stmt->execute([$employee_id, $today]);
            $existing_record = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($existing_record) {
                $error = 'You have already clocked in today.';
            } else {
                // Determine status based on time
                $status = 'present';
                if (strtotime($current_time) > strtotime('09:00:00')) {
                    $status = 'late';
                }
                
                $stmt = $pdo->prepare("INSERT INTO attendance (employee_id, date, time_in, status) VALUES (?, ?, ?, ?)");
                $stmt->execute([$employee_id, $today, $current_time, $status]);
                $message = 'Successfully clocked in at ' . formatTime($current_time);
            }
        } elseif ($_POST['action'] === 'time_out') {
            // Check if already clocked out today
            $stmt = $pdo->prepare("SELECT * FROM attendance WHERE employee_id = ? AND date = ?");
            $stmt->execute([$employee_id, $today]);
            $existing_record = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$existing_record) {
                $error = 'You need to clock in first.';
            } elseif ($existing_record['time_out']) {
                $error = 'You have already clocked out today.';
            } else {
                // Calculate hours worked
                $time_in = $existing_record['time_in'];
                $hours_worked = calculateHours($time_in, $current_time);
                
                $stmt = $pdo->prepare("UPDATE attendance SET time_out = ?, total_hours = ? WHERE employee_id = ? AND date = ?");
                $stmt->execute([$current_time, $hours_worked, $employee_id, $today]);
                $message = 'Successfully clocked out at ' . formatTime($current_time) . ' (Worked: ' . $hours_worked . ' hours)';
            }
        }
    }
    
    // Get today's attendance
    $today = date('Y-m-d');
    $stmt = $pdo->prepare("SELECT * FROM attendance WHERE employee_id = ? AND date = ?");
    $stmt->execute([$employee_id, $today]);
    $today_attendance = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Get recent attendance history
    $stmt = $pdo->prepare("
        SELECT * FROM attendance 
        WHERE employee_id = ? 
        ORDER BY date DESC 
        LIMIT 10
    ");
    $stmt->execute([$employee_id]);
    $recent_attendance = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Calculate monthly statistics
    $current_month = date('Y-m');
    $stmt = $pdo->prepare("
        SELECT 
            COUNT(*) as total_days,
            SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as present_days,
            SUM(CASE WHEN status = 'late' THEN 1 ELSE 0 END) as late_days,
            SUM(CASE WHEN status = 'absent' THEN 1 ELSE 0 END) as absent_days,
            SUM(total_hours) as total_hours
        FROM attendance 
        WHERE employee_id = ? AND DATE_FORMAT(date, '%Y-%m') = ?
    ");
    $stmt->execute([$employee_id, $current_month]);
    $monthly_stats = $stmt->fetch(PDO::FETCH_ASSOC);
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
        <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i><?php echo htmlspecialchars($error); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if ($message): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i><?php echo htmlspecialchars($message); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if ($employee): ?>
            <!-- Welcome Section -->
            <div class="row mb-4">
                <div class="col-12">
                    <h1 class="h3 mb-0">Welcome, <?php echo htmlspecialchars($employee['full_name']); ?>!</h1>
                    <p class="text-muted">Employee ID: <?php echo htmlspecialchars($employee['employee_id']); ?></p>
                </div>
            </div>

            <!-- Time Clock Section -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="card shadow">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Time Clock</h6>
                        </div>
                        <div class="card-body text-center">
                            <div class="mb-4">
                                <h4 id="current-time"><?php echo date('g:i:s A'); ?></h4>
                                <p class="text-muted"><?php echo formatDate(date('Y-m-d')); ?></p>
                            </div>
                            
                            <div class="row">
                                <div class="col-6">
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="action" value="time_in">
                                        <button type="submit" class="btn btn-success btn-lg w-100" 
                                                <?php echo ($today_attendance && $today_attendance['time_in']) ? 'disabled' : ''; ?>>
                                            <i class="fas fa-sign-in-alt me-2"></i>Time In
                                        </button>
                                    </form>
                                </div>
                                <div class="col-6">
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="action" value="time_out">
                                        <button type="submit" class="btn btn-danger btn-lg w-100"
                                                <?php echo (!$today_attendance || !$today_attendance['time_in'] || $today_attendance['time_out']) ? 'disabled' : ''; ?>>
                                            <i class="fas fa-sign-out-alt me-2"></i>Time Out
                                        </button>
                                    </form>
                                </div>
                            </div>
                            
                            <?php if ($today_attendance): ?>
                                <div class="mt-4">
                                    <div class="row">
                                        <div class="col-6">
                                            <strong>Time In:</strong><br>
                                            <span class="text-success">
                                                <?php echo $today_attendance['time_in'] ? formatTime($today_attendance['time_in']) : 'Not clocked in'; ?>
                                            </span>
                                        </div>
                                        <div class="col-6">
                                            <strong>Time Out:</strong><br>
                                            <span class="text-danger">
                                                <?php echo $today_attendance['time_out'] ? formatTime($today_attendance['time_out']) : 'Not clocked out'; ?>
                                            </span>
                                        </div>
                                    </div>
                                    <?php if ($today_attendance['total_hours'] > 0): ?>
                                        <div class="mt-2">
                                            <strong>Hours Worked:</strong> <?php echo $today_attendance['total_hours']; ?> hours
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Monthly Statistics -->
                <div class="col-md-6">
                    <div class="card shadow">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">This Month's Statistics</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-6 mb-3">
                                    <div class="text-center">
                                        <h4 class="text-success"><?php echo $monthly_stats['present_days']; ?></h4>
                                        <small class="text-muted">Present Days</small>
                                    </div>
                                </div>
                                <div class="col-6 mb-3">
                                    <div class="text-center">
                                        <h4 class="text-warning"><?php echo $monthly_stats['late_days']; ?></h4>
                                        <small class="text-muted">Late Days</small>
                                    </div>
                                </div>
                                <div class="col-6 mb-3">
                                    <div class="text-center">
                                        <h4 class="text-danger"><?php echo $monthly_stats['absent_days']; ?></h4>
                                        <small class="text-muted">Absent Days</small>
                                    </div>
                                </div>
                                <div class="col-6 mb-3">
                                    <div class="text-center">
                                        <h4 class="text-info"><?php echo number_format($monthly_stats['total_hours'], 1); ?></h4>
                                        <small class="text-muted">Total Hours</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Attendance History -->
            <div class="row">
                <div class="col-12">
                    <div class="card shadow">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Recent Attendance History</h6>
                        </div>
                        <div class="card-body">
                            <?php if (empty($recent_attendance)): ?>
                                <p class="text-muted text-center">No attendance records found.</p>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Date</th>
                                                <th>Time In</th>
                                                <th>Time Out</th>
                                                <th>Hours</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($recent_attendance as $record): ?>
                                                <tr>
                                                    <td><?php echo formatDate($record['date']); ?></td>
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
        <?php else: ?>
            <div class="alert alert-warning" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i>
                Employee record not found. Please contact your administrator to set up your employee profile.
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Update current time every second
        function updateTime() {
            const now = new Date();
            const timeString = now.toLocaleTimeString('en-US', { 
                hour12: true, 
                hour: 'numeric', 
                minute: '2-digit', 
                second: '2-digit' 
            });
            document.getElementById('current-time').textContent = timeString;
        }
        
        setInterval(updateTime, 1000);
    </script>
</body>
</html> 