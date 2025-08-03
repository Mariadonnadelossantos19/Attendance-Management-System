<?php
require_once '../init.php';
requireAdmin();

$message = '';
$error = '';

// Handle export requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['export_type'])) {
    $export_type = $_POST['export_type'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $employee_id = $_POST['employee_id'];
    $department = $_POST['department'];
    
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
    
    if ($export_type === 'excel') {
        exportToExcel($attendance_records, $start_date, $end_date);
    } elseif ($export_type === 'pdf') {
        exportToPDF($attendance_records, $start_date, $end_date);
    }
}

// Get employees for filter
$stmt = $pdo->prepare("SELECT id, employee_id, full_name FROM employees WHERE status = 'active' ORDER BY full_name");
$stmt->execute();
$employees = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get departments for filter
$stmt = $pdo->prepare("SELECT DISTINCT department FROM employees WHERE department IS NOT NULL AND department != '' ORDER BY department");
$stmt->execute();
$departments = $stmt->fetchAll(PDO::FETCH_COLUMN);

function exportToExcel($data, $start_date, $end_date) {
    $filename = "attendance_report_" . date('Y-m-d_H-i-s') . ".csv";
    
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    
    $output = fopen('php://output', 'w');
    
    // Add headers
    fputcsv($output, ['Date', 'Employee ID', 'Name', 'Department', 'Time In', 'Time Out', 'Hours', 'Status']);
    
    // Add data
    foreach ($data as $record) {
        fputcsv($output, [
            formatDate($record['date']),
            $record['employee_id'],
            $record['full_name'],
            $record['department'],
            $record['time_in'] ? formatTime($record['time_in']) : '-',
            $record['time_out'] ? formatTime($record['time_out']) : '-',
            $record['total_hours'] . ' hrs',
            ucfirst($record['status'])
        ]);
    }
    
    fclose($output);
    exit();
}

function exportToPDF($data, $start_date, $end_date) {
    // Simple HTML to PDF conversion
    $html = '<!DOCTYPE html>
    <html>
    <head>
        <title>Attendance Report</title>
        <style>
            body { font-family: Arial, sans-serif; margin: 20px; }
            table { width: 100%; border-collapse: collapse; margin-top: 20px; }
            th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
            th { background-color: #f2f2f2; }
            .header { text-align: center; margin-bottom: 20px; }
            .summary { margin-bottom: 20px; }
        </style>
    </head>
    <body>
        <div class="header">
            <h1>Attendance Report</h1>
            <p>Period: ' . formatDate($start_date) . ' to ' . formatDate($end_date) . '</p>
        </div>
        
        <div class="summary">
            <p><strong>Total Records:</strong> ' . count($data) . '</p>
        </div>
        
        <table>
            <thead>
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
            <tbody>';
    
    foreach ($data as $record) {
        $html .= '<tr>
            <td>' . formatDate($record['date']) . '</td>
            <td>' . htmlspecialchars($record['employee_id']) . '</td>
            <td>' . htmlspecialchars($record['full_name']) . '</td>
            <td>' . htmlspecialchars($record['department']) . '</td>
            <td>' . ($record['time_in'] ? formatTime($record['time_in']) : '-') . '</td>
            <td>' . ($record['time_out'] ? formatTime($record['time_out']) : '-') . '</td>
            <td>' . $record['total_hours'] . ' hrs</td>
            <td>' . ucfirst($record['status']) . '</td>
        </tr>';
    }
    
    $html .= '</tbody></table></body></html>';
    
    $filename = "attendance_report_" . date('Y-m-d_H-i-s') . ".html";
    
    header('Content-Type: text/html');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    
    echo $html;
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Export Data - Attendance Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid">
            <a class="navbar-brand" href="dashboard.php">
                <i class="fas fa-clock me-2"></i>Attendance System
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php">
                            <i class="fas fa-tachometer-alt me-1"></i>Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="employees.php">
                            <i class="fas fa-users me-1"></i>Employees
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="reports.php">
                            <i class="fas fa-chart-bar me-1"></i>Reports
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="export.php">
                            <i class="fas fa-download me-1"></i>Export
                        </a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user me-1"></i><?php echo htmlspecialchars($_SESSION['full_name']); ?>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="../auth/logout.php">
                                <i class="fas fa-sign-out-alt me-2"></i>Logout
                            </a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container-fluid mt-4">
        <div class="row">
            <div class="col-12">
                <h1 class="h3 mb-4">Export Attendance Data</h1>

                <div class="row">
                    <div class="col-md-8">
                        <div class="card shadow">
                            <div class="card-header py-3">
                                <h6 class="m-0 font-weight-bold text-primary">Export Options</h6>
                            </div>
                            <div class="card-body">
                                <form method="POST">
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label for="start_date" class="form-label">Start Date *</label>
                                            <input type="date" class="form-control" id="start_date" name="start_date" 
                                                   value="<?php echo date('Y-m-01'); ?>" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="end_date" class="form-label">End Date *</label>
                                            <input type="date" class="form-control" id="end_date" name="end_date" 
                                                   value="<?php echo date('Y-m-d'); ?>" required>
                                        </div>
                                    </div>
                                    
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label for="employee_id" class="form-label">Employee</label>
                                            <select class="form-select" id="employee_id" name="employee_id">
                                                <option value="">All Employees</option>
                                                <?php foreach ($employees as $employee): ?>
                                                    <option value="<?php echo $employee['id']; ?>">
                                                        <?php echo htmlspecialchars($employee['employee_id'] . ' - ' . $employee['full_name']); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="department" class="form-label">Department</label>
                                            <select class="form-select" id="department" name="department">
                                                <option value="">All Departments</option>
                                                <?php foreach ($departments as $dept): ?>
                                                    <option value="<?php echo htmlspecialchars($dept); ?>">
                                                        <?php echo htmlspecialchars($dept); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                    
                                    <div class="row mb-4">
                                        <div class="col-12">
                                            <label class="form-label">Export Format</label>
                                            <div class="d-flex gap-3">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="export_type" 
                                                           id="excel" value="excel" checked>
                                                    <label class="form-check-label" for="excel">
                                                        <i class="fas fa-file-excel text-success me-2"></i>Excel (CSV)
                                                    </label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="export_type" 
                                                           id="pdf" value="pdf">
                                                    <label class="form-check-label" for="pdf">
                                                        <i class="fas fa-file-pdf text-danger me-2"></i>PDF (HTML)
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="d-flex gap-2">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-download me-2"></i>Export Data
                                        </button>
                                        <a href="reports.php" class="btn btn-secondary">
                                            <i class="fas fa-arrow-left me-2"></i>Back to Reports
                                        </a>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="card shadow">
                            <div class="card-header py-3">
                                <h6 class="m-0 font-weight-bold text-primary">Export Information</h6>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <h6><i class="fas fa-info-circle text-info me-2"></i>Excel Export</h6>
                                    <p class="small text-muted">
                                        Downloads a CSV file that can be opened in Excel, Google Sheets, or any spreadsheet application.
                                    </p>
                                </div>
                                
                                <div class="mb-3">
                                    <h6><i class="fas fa-info-circle text-info me-2"></i>PDF Export</h6>
                                    <p class="small text-muted">
                                        Downloads an HTML file that can be printed or converted to PDF using your browser's print function.
                                    </p>
                                </div>
                                
                                <div class="mb-3">
                                    <h6><i class="fas fa-lightbulb text-warning me-2"></i>Tips</h6>
                                    <ul class="small text-muted">
                                        <li>Select date range to export specific periods</li>
                                        <li>Filter by employee or department for targeted reports</li>
                                        <li>Use Excel format for data analysis</li>
                                        <li>Use PDF format for printing and sharing</li>
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