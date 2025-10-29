<?php
session_start();
// Fixed admin credentials
$admin_username = "admin";
$admin_password = "admin123";

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    // Verify credentials
    if ($username === $admin_username && $password === $admin_password) {
        // Set session variables
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_username'] = $username;
        
        // Redirect to admin dashboard
        header("Location: admin_dashboard.php");
        exit();
    } else {
        // Invalid credentials
        header("Location: admin_login.php?error=1");
        exit();
    }
} else {
    // Not a POST request, redirect to login page
    header("Location: admin_login.php");
    exit();
}
?>