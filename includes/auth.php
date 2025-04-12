<?php
session_start();

function isAdmin() {
    return isset($_SESSION['user_id']) && isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function requireAdmin() {
    if (!isAdmin()) {
        header('Location: /New%20folder/login.php');
        exit();
    }
}

function getAdminStats() {
    global $conn;
    
    $stats = [];
    
    // Get total users
    $query = "SELECT COUNT(*) as total FROM users WHERE role = 'user'";
    $result = mysqli_query($conn, $query);
    $stats['total_users'] = mysqli_fetch_assoc($result)['total'];
    
    // Get total courses
    $query = "SELECT COUNT(*) as total FROM courses";
    $result = mysqli_query($conn, $query);
    $stats['total_courses'] = mysqli_fetch_assoc($result)['total'];
    
    // Get recent enrollments (last 7 days)
    $query = "SELECT COUNT(*) as total FROM user_courses 
              WHERE enrolled_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
    $result = mysqli_query($conn, $query);
    $stats['recent_enrollments'] = mysqli_fetch_assoc($result)['total'];
    
    return $stats;
}
?> 