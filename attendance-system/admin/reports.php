<?php
require_once '../init.php';
requireAdmin();

// Get filter parameters
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01');
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');
$employee_id = isset($_GET['employee_id']) ? $_GET['employee_id'] : '';
$department = isset($_GET['department']) ? $_GET['department'] : '';

// Get employees for filter
$stmt = $pdo->prepare("SELECT id, employee_id, full_name FROM employees WHERE status = 'active' ORDER BY full_name");
$stmt->execute();
$employees = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get departments for filter
$stmt = $pdo->prepare("SELECT DISTINCT department FROM employees WHERE department IS NOT NULL AND department != '' ORDER BY department");
$stmt->execute();
$departments = $stmt->fetchAll(PDO::FETCH_COLUMN);

// Build query for attendance data
$where_conditions = ["a.date BETWEEN ? AND ?"];
$params = [$start_date, $end_date];

if ($employee_id) {
    $where_conditions[] = "a.employee_id = ?";
    $params[] = $employee_id;
}

if ($department) {
    $where_conditions[] = "e.department = ?";
    $params[] = $department;
}

$where_clause = implode(" AND ", $where_conditions);

// Get attendance data
$sql = "
    SELECT a.*, e.full_name, e.employee_id, e.department 
    FROM attendance a 
    JOIN employees e ON a.employee_id = e.id 
    WHERE $where_clause 
    ORDER BY a.date DESC, e.full_name
";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$attendance_records = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate statistics
$total_days = count(array_unique(array_column($attendance_records, 'date')));
$total_present = count(array_filter($attendance_records, function($record) {
    return $record['status'] === 'present';
}));
$total_late = count(array_filter($attendance_records, function($record) {
    return $record['status'] === 'late';
}));
$total_absent = count(array_filter($attendance_records, function($record) {
    return $record['status'] === 'absent';
}));
$total_hours = array_sum(array_column($attendance_records, 'total_hours'));
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
                <h1 class="h3 mb-4">Attendance Reports</h1>

                <!-- Filter Form -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Filter Options</h6>
                    </div>
                    <div class="card-body">
                        <form method="GET" class="row g-3">
                            <div class="col-md-3">
                                <label for="start_date" class="form-label">Start Date</label>
                                <input type="date" class="form-control" id="start_date" name="start_date" 
                                       value="<?php echo htmlspecialchars($start_date); ?>">
                            </div>
                            <div class="col-md-3">
                                <label for="end_date" class="form-label">End Date</label>
                                <input type="date" class="form-control" id="end_date" name="end_date" 
                                       value="<?php echo htmlspecialchars($end_date); ?>">
                            </div>
                            <div class="col-md-3">
                                <label for="employee_id" class="form-label">Employee</label>
                                <select class="form-select" id="employee_id" name="employee_id">
                                    <option value="">All Employees</option>
                                    <?php foreach ($employees as $employee): ?>
                                        <option value="<?php echo $employee['id']; ?>" 
                                                <?php echo $employee_id == $employee['id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($employee['employee_id'] . ' - ' . $employee['full_name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="department" class="form-label">Department</label>
                                <select class="form-select" id="department" name="department">
                                    <option value="">All Departments</option>
                                    <?php foreach ($departments as $dept): ?>
                                        <option value="<?php echo htmlspecialchars($dept); ?>" 
                                                <?php echo $department === $dept ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($dept); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-filter me-2"></i>Apply Filter
                                </button>
                                <a href="reports.php" class="btn btn-secondary">
                                    <i class="fas fa-times me-2"></i>Clear Filter
                                </a>
                            </div>
                        </form>
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
                                            Total Days
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $total_days; ?></div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-calendar fa-2x text-gray-300"></i>
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
                                            Present
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $total_present; ?></div>
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
                                            Late
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $total_late; ?></div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-clock fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-info shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                            Total Hours
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo number_format($total_hours, 1); ?></div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-hourglass-half fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Attendance Records Table -->
                <div class="card shadow">
                    <div class="card-header py-3 d-flex justify-content-between align-items-center">
                        <h6 class="m-0 font-weight-bold text-primary">Attendance Records</h6>
                        <span class="text-muted">
                            <?php echo count($attendance_records); ?> records found
                        </span>
                    </div>
                    <div class="card-body">
                        <?php if (empty($attendance_records)): ?>
                            <p class="text-muted text-center">No attendance records found for the selected criteria.</p>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Date</th>
                                            <th>Employee ID</th>
                                            <th>Name</th>
                                            <th>Department</th>
                                            <th>Time In</th>
                                            <th>Time Out</th>
                                            <th>Hours</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($attendance_records as $record): ?>
                                            <tr>
                                                <td><?php echo formatDate($record['date']); ?></td>
                                                <td><?php echo htmlspecialchars($record['employee_id']); ?></td>
                                                <td><?php echo htmlspecialchars($record['full_name']); ?></td>
                                                <td><?php echo htmlspecialchars($record['department']); ?></td>
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
                                                            ($record['status'] === 'late' ? 'warning' : 
                                                            ($record['status'] === 'absent' ? 'danger' : 'secondary')); 
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 