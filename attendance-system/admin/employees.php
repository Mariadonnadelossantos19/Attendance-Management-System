<?php
require_once '../init.php';
requireAdmin();

$message = '';
$error = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $employee_id = trim($_POST['employee_id']);
                $full_name = trim($_POST['full_name']);
                $email = trim($_POST['email']);
                $department = trim($_POST['department']);
                $position = trim($_POST['position']);
                $hire_date = $_POST['hire_date'];
                
                if (empty($employee_id) || empty($full_name) || empty($email)) {
                    $error = 'Employee ID, Full Name, and Email are required.';
                } else {
                    try {
                        $stmt = $pdo->prepare("INSERT INTO employees (employee_id, full_name, email, department, position, hire_date) VALUES (?, ?, ?, ?, ?, ?)");
                        $stmt->execute([$employee_id, $full_name, $email, $department, $position, $hire_date]);
                        $message = 'Employee added successfully!';
                    } catch (PDOException $e) {
                        $error = 'Error adding employee: ' . $e->getMessage();
                    }
                }
                break;
                
            case 'edit':
                $id = $_POST['id'];
                $employee_id = trim($_POST['employee_id']);
                $full_name = trim($_POST['full_name']);
                $email = trim($_POST['email']);
                $department = trim($_POST['department']);
                $position = trim($_POST['position']);
                $hire_date = $_POST['hire_date'];
                $status = $_POST['status'];
                
                try {
                    $stmt = $pdo->prepare("UPDATE employees SET employee_id = ?, full_name = ?, email = ?, department = ?, position = ?, hire_date = ?, status = ? WHERE id = ?");
                    $stmt->execute([$employee_id, $full_name, $email, $department, $position, $hire_date, $status, $id]);
                    $message = 'Employee updated successfully!';
                } catch (PDOException $e) {
                    $error = 'Error updating employee: ' . $e->getMessage();
                }
                break;
                
            case 'delete':
                $id = $_POST['id'];
                try {
                    $stmt = $pdo->prepare("DELETE FROM employees WHERE id = ?");
                    $stmt->execute([$id]);
                    $message = 'Employee deleted successfully!';
                } catch (PDOException $e) {
                    $error = 'Error deleting employee: ' . $e->getMessage();
                }
                break;
        }
    }
}

// Get all employees
$stmt = $pdo->prepare("SELECT * FROM employees ORDER BY full_name");
$stmt->execute();
$employees = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
                    <h1 class="h3 mb-0">Manage Employees</h1>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addEmployeeModal">
                        <i class="fas fa-plus me-2"></i>Add Employee
                    </button>
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

                <div class="card shadow">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Employee List</h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Employee ID</th>
                                        <th>Full Name</th>
                                        <th>Email</th>
                                        <th>Department</th>
                                        <th>Position</th>
                                        <th>Hire Date</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($employees as $employee): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($employee['employee_id']); ?></td>
                                            <td><?php echo htmlspecialchars($employee['full_name']); ?></td>
                                            <td><?php echo htmlspecialchars($employee['email']); ?></td>
                                            <td><?php echo htmlspecialchars($employee['department']); ?></td>
                                            <td><?php echo htmlspecialchars($employee['position']); ?></td>
                                            <td><?php echo $employee['hire_date'] ? formatDate($employee['hire_date']) : '-'; ?></td>
                                            <td>
                                                <span class="badge bg-<?php echo $employee['status'] === 'active' ? 'success' : 'secondary'; ?>">
                                                    <?php echo ucfirst($employee['status']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <button class="btn btn-sm btn-outline-primary" onclick="editEmployee(<?php echo htmlspecialchars(json_encode($employee)); ?>)">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button class="btn btn-sm btn-outline-danger" onclick="deleteEmployee(<?php echo $employee['id']; ?>, '<?php echo htmlspecialchars($employee['full_name']); ?>')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Employee Modal -->
    <div class="modal fade" id="addEmployeeModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Employee</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <input type="hidden" name="action" value="add">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="employee_id" class="form-label">Employee ID *</label>
                                <input type="text" class="form-control" id="employee_id" name="employee_id" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="full_name" class="form-label">Full Name *</label>
                                <input type="text" class="form-control" id="full_name" name="full_name" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email *</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="department" class="form-label">Department</label>
                                <input type="text" class="form-control" id="department" name="department">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="position" class="form-label">Position</label>
                                <input type="text" class="form-control" id="position" name="position">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="hire_date" class="form-label">Hire Date</label>
                            <input type="date" class="form-control" id="hire_date" name="hire_date">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add Employee</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Employee Modal -->
    <div class="modal fade" id="editEmployeeModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Employee</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="id" id="edit_id">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="edit_employee_id" class="form-label">Employee ID *</label>
                                <input type="text" class="form-control" id="edit_employee_id" name="employee_id" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="edit_full_name" class="form-label">Full Name *</label>
                                <input type="text" class="form-control" id="edit_full_name" name="full_name" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="edit_email" class="form-label">Email *</label>
                            <input type="email" class="form-control" id="edit_email" name="email" required>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="edit_department" class="form-label">Department</label>
                                <input type="text" class="form-control" id="edit_department" name="department">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="edit_position" class="form-label">Position</label>
                                <input type="text" class="form-control" id="edit_position" name="position">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="edit_hire_date" class="form-label">Hire Date</label>
                                <input type="date" class="form-control" id="edit_hire_date" name="hire_date">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="edit_status" class="form-label">Status</label>
                                <select class="form-select" id="edit_status" name="status">
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update Employee</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteEmployeeModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirm Delete</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete employee <strong id="delete_employee_name"></strong>?</p>
                    <p class="text-danger">This action cannot be undone.</p>
                </div>
                <form method="POST">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" id="delete_employee_id">
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Delete Employee</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function editEmployee(employee) {
            document.getElementById('edit_id').value = employee.id;
            document.getElementById('edit_employee_id').value = employee.employee_id;
            document.getElementById('edit_full_name').value = employee.full_name;
            document.getElementById('edit_email').value = employee.email;
            document.getElementById('edit_department').value = employee.department;
            document.getElementById('edit_position').value = employee.position;
            document.getElementById('edit_hire_date').value = employee.hire_date;
            document.getElementById('edit_status').value = employee.status;
            
            new bootstrap.Modal(document.getElementById('editEmployeeModal')).show();
        }
        
        function deleteEmployee(id, name) {
            document.getElementById('delete_employee_id').value = id;
            document.getElementById('delete_employee_name').textContent = name;
            
            new bootstrap.Modal(document.getElementById('deleteEmployeeModal')).show();
        }
    </script>
</body>
</html> 