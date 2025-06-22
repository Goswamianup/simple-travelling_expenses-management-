<?php
// auth.php - Authentication middleware to protect your main application

session_start();

// Check if user is logged in
function checkAuth() {
    if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
        header("Location: login.php");
        exit();
    }
}

// Get current user info
function getCurrentUser() {
    if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
        return [
            'id' => $_SESSION['user_id'],
            'username' => $_SESSION['username'],
            'full_name' => $_SESSION['full_name'],
            'role' => $_SESSION['role'],
            'email' => $_SESSION['email']
        ];
    }
    return null;
}

// Check if user has specific role
function hasRole($required_role) {
    $user = getCurrentUser();
    if (!$user) return false;
    
    $role_hierarchy = [
        'employee' => 1,
        'manager' => 2,
        'admin' => 3
    ];
    
    $user_level = $role_hierarchy[$user['role']] ?? 0;
    $required_level = $role_hierarchy[$required_role] ?? 0;
    
    return $user_level >= $required_level;
}

// Logout function
function logout() {
    session_destroy();
    header("Location: login.php");
    exit();
}

// Check authentication on every protected page
checkAuth();
?>