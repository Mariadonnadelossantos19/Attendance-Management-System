<?php
session_start();

// Include database connection
require_once 'config/db.php';

// Function to check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

// Function to check if user is admin
function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

// Function to check if user is employee
function isEmployee() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'employee';
}

// Function to redirect if not logged in
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: auth/login.php');
        exit();
    }
}

// Function to redirect if not admin
function requireAdmin() {
    requireLogin();
    if (!isAdmin()) {
        header('Location: employee/dashboard.php');
        exit();
    }
}

// Function to redirect if not employee
function requireEmployee() {
    requireLogin();
    if (!isEmployee()) {
        header('Location: admin/dashboard.php');
        exit();
    }
}

// Function to get current user data
function getCurrentUser($pdo) {
    if (!isLoggedIn()) {
        return null;
    }
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Function to get employee data
function getEmployeeData($pdo, $employee_id) {
    $stmt = $pdo->prepare("SELECT * FROM employees WHERE id = ?");
    $stmt->execute([$employee_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Function to format date
function formatDate($date) {
    return date('F j, Y', strtotime($date));
}

// Function to format time
function formatTime($time) {
    return date('g:i A', strtotime($time));
}

// Function to calculate hours worked
function calculateHours($time_in, $time_out) {
    if (!$time_in || !$time_out) {
        return 0;
    }
    
    $time_in_seconds = strtotime($time_in);
    $time_out_seconds = strtotime($time_out);
    
    $diff_seconds = $time_out_seconds - $time_in_seconds;
    $hours = $diff_seconds / 3600;
    
    return round($hours, 2);
}
?> 