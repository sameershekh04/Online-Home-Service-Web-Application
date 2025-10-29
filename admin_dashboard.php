<?php
session_start();
// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: admin_login.php");
    exit();
}
// Database configuration
$db_host = 'localhost';
$db_user = 'root';
$db_pass = 'Sameer@123';
$db_name = 'services_app';
// Function to check if MySQL is running
function isMySQLRunning() {
    // Try to connect to MySQL without specifying a database
    $conn = @new mysqli('localhost', 'root', 'Sameer@123');
    if ($conn->connect_error) {
        return false;
    }
    $conn->close();
    return true;
}
// Check if MySQL is running
if (!isMySQLRunning()) {
    die("<div style='padding: 30px; font-family: Arial, sans-serif; max-width: 800px; margin: 50px auto; background-color: #fff; border-radius: 10px; box-shadow: 0 5px 15px rgba(0,0,0,0.1);'>
        <h2 style='color: #d9534f;'>MySQL Server Not Running</h2>
        <p><strong>Error:</strong> Could not connect to MySQL server.</p>
        <p><strong>Possible solutions:</strong></p>
        <ol>
            <li>Open XAMPP Control Panel</li>
            <li>Click the 'Start' button next to MySQL</li>
            <li>Wait for MySQL to show 'Running' in green</li>
            <li>If MySQL doesn't start, check if port 3306 is being used by another application</li>
            <li>You may need to restart your computer if the issue persists</li>
        </ol>
        <div style='margin-top: 20px; padding: 15px; background-color: #f8d7da; border-radius: 5px;'>
            <p><strong>Error details:</strong> No connection could be made because the target machine actively refused it</p>
        </div>
        <div style='margin-top: 20px;'>
            <a href='javascript:history.back()' style='display: inline-block; padding: 10px 20px; background-color: #5d3b66; color: white; text-decoration: none; border-radius: 5px;'>Go Back</a>
        </div>
    </div>");
}
// Create database connection
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
// Check connection
if ($conn->connect_error) {
    // Try to create the database if it doesn't exist
    $conn_temp = new mysqli($db_host, $db_user, $db_pass);
    if ($conn_temp->connect_error) {
        die("Connection failed: " . $conn_temp->connect_error);
    }
    
    // Create database if not exists
    $sql = "CREATE DATABASE IF NOT EXISTS $db_name";
    if ($conn_temp->query($sql) === TRUE) {
        // Database created successfully
    } else {
        die("Error creating database: " . $conn_temp->error);
    }
    
    // Close connection and reconnect with database name
    $conn_temp->close();
    $conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
    
    // Check connection again
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
}
// Function to check if table exists
function tableExists($conn, $tableName) {
    $result = $conn->query("SHOW TABLES LIKE '$tableName'");
    return $result && $result->num_rows > 0;
}
// Function to check if column exists
function columnExists($conn, $tableName, $columnName) {
    $result = $conn->query("SHOW COLUMNS FROM $tableName LIKE '$columnName'");
    return $result && $result->num_rows > 0;
}
// Function to get total users
function getTotalUsers($conn) {
    if (!tableExists($conn, 'users')) {
        return 0; // Table doesn't exist
    }
    
    $sql = "SELECT COUNT(*) as total FROM users";
    $result = $conn->query($sql);
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return $row['total'];
    }
    return 0;
}
// Function to get total providers
function getTotalProviders($conn) {
    if (!tableExists($conn, 'service_providers')) {
        return 0; // Table doesn't exist
    }
    
    $sql = "SELECT COUNT(*) as total FROM service_providers";
    $result = $conn->query($sql);
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return $row['total'];
    }
    return 0;
}
// Function to get pending providers
function getPendingProviders($conn) {
    if (!tableExists($conn, 'service_providers')) {
        return 0; // Table doesn't exist
    }
    
    if (!columnExists($conn, 'service_providers', 'status')) {
        return 0; // Column doesn't exist
    }
    
    $sql = "SELECT COUNT(*) as total FROM service_providers WHERE status = 'pending'";
    $result = $conn->query($sql);
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return $row['total'];
    }
    return 0;
}
// Function to get verified providers
function getVerifiedProviders($conn) {
    if (!tableExists($conn, 'service_providers')) {
        return 0; // Table doesn't exist
    }
    
    if (!columnExists($conn, 'service_providers', 'status')) {
        return 0; // Column doesn't exist
    }
    
    $sql = "SELECT COUNT(*) as total FROM service_providers WHERE status = 'verified'";
    $result = $conn->query($sql);
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return $row['total'];
    }
    return 0;
}
// Function to get rejected providers
function getRejectedProviders($conn) {
    if (!tableExists($conn, 'service_providers')) {
        return 0; // Table doesn't exist
    }
    
    if (!columnExists($conn, 'service_providers', 'status')) {
        return 0; // Column doesn't exist
    }
    
    $sql = "SELECT COUNT(*) as total FROM service_providers WHERE status = 'rejected'";
    $result = $conn->query($sql);
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return $row['total'];
    }
    return 0;
}
// Function to get total services
function getTotalServices($conn) {
    if (!tableExists($conn, 'services')) {
        return 0; // Table doesn't exist
    }
    
    $sql = "SELECT COUNT(*) as total FROM services";
    $result = $conn->query($sql);
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return $row['total'];
    }
    return 0;
}
// Function to get monthly revenue from bookings table
function getMonthlyRevenue($conn) {
    if (!tableExists($conn, 'bookings')) {
        return '₹0.00'; // Table doesn't exist
    }
    
    if (!columnExists($conn, 'bookings', 'created_at') || !columnExists($conn, 'bookings', 'total_amount')) {
        return '₹0.00'; // Required columns don't exist
    }
    
    $currentMonth = date('Y-m');
    $sql = "SELECT SUM(total_amount) as total FROM bookings WHERE DATE_FORMAT(created_at, '%Y-%m') = '$currentMonth'";
    $result = $conn->query($sql);
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return $row['total'] ? '₹' . number_format($row['total'], 2) : '₹0.00';
    }
    return '₹0.00';
}
// Function to get total bookings
function getTotalBookings($conn) {
    if (!tableExists($conn, 'bookings')) {
        return 0; // Table doesn't exist
    }
    
    $sql = "SELECT COUNT(*) as total FROM bookings";
    $result = $conn->query($sql);
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return $row['total'];
    }
    return 0;
}
// Function to get active coupons
function getActiveCoupons($conn) {
    if (!tableExists($conn, 'coupons')) {
        return 0; // Table doesn't exist
    }
    
    if (!columnExists($conn, 'coupons', 'start_date') || !columnExists($conn, 'coupons', 'end_date') || !columnExists($conn, 'coupons', 'status')) {
        return 0; // Required columns don't exist
    }
    
    $currentDate = date('Y-m-d');
    $sql = "SELECT COUNT(*) as total FROM coupons WHERE start_date <= '$currentDate' AND end_date >= '$currentDate' AND status = 'active'";
    $result = $conn->query($sql);
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return $row['total'];
    }
    return 0;
}
// Function to get today's revenue from bookings table
function getTodaysRevenue($conn) {
    if (!tableExists($conn, 'bookings')) {
        return '₹0.00'; // Table doesn't exist
    }
    
    if (!columnExists($conn, 'bookings', 'created_at') || !columnExists($conn, 'bookings', 'total_amount')) {
        return '₹0.00'; // Required columns don't exist
    }
    
    $today = date('Y-m-d');
    $sql = "SELECT SUM(total_amount) as total FROM bookings WHERE DATE(created_at) = '$today'";
    $result = $conn->query($sql);
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return $row['total'] ? '₹' . number_format($row['total'], 2) : '₹0.00';
    }
    return '₹0.00';
}
// Function to get today's bookings
function getTodaysBookings($conn) {
    if (!tableExists($conn, 'bookings')) {
        return 0; // Table doesn't exist
    }
    
    if (!columnExists($conn, 'bookings', 'created_at')) {
        return 0; // Column doesn't exist
    }
    
    $today = date('Y-m-d');
    $sql = "SELECT COUNT(*) as total FROM bookings WHERE DATE(created_at) = '$today'";
    $result = $conn->query($sql);
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return $row['total'];
    }
    return 0;
}
// Function to get recent activities
function getRecentActivities($conn) {
    $activities = [];
    
    // Check if required tables exist
    $bookingsTable = tableExists($conn, 'bookings');
    $usersTable = tableExists($conn, 'users');
    $serviceProvidersTable = tableExists($conn, 'service_providers');
    $paymentsTable = tableExists($conn, 'payments');
    $reviewsTable = tableExists($conn, 'reviews');
    $supportTicketsTable = tableExists($conn, 'support_tickets');
    
    // Latest booking
    if ($bookingsTable && columnExists($conn, 'bookings', 'created_at') && columnExists($conn, 'bookings', 'username')) {
        $sql = "SELECT * FROM bookings ORDER BY created_at DESC LIMIT 1";
        $result = $conn->query($sql);
        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $activities[] = [
                'type' => 'booking',
                'icon' => 'fas fa-calendar-check',
                'title' => 'New Booking',
                'description' => $row['username'] . ' made a booking for ' . $row['service_details'],
                'time' => date('d M Y, H:i', strtotime($row['created_at'])), // Changed to exact time
                'timestamp' => strtotime($row['created_at'])
            ];
        }
    }
    
    // Latest user registration - FIXED: Using 'username' instead of 'name'
    if ($usersTable && columnExists($conn, 'users', 'created_at') && columnExists($conn, 'users', 'username')) {
        $sql = "SELECT * FROM users ORDER BY created_at DESC LIMIT 1";
        $result = $conn->query($sql);
        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $activities[] = [
                'type' => 'user',
                'icon' => 'fas fa-user-plus',
                'title' => 'New User Registration',
                'description' => $row['username'] . ' registered a new account',
                'time' => date('d M Y, H:i', strtotime($row['created_at'])), // Changed to exact time
                'timestamp' => strtotime($row['created_at'])
            ];
        }
    }
    
    // Latest provider registration
    if ($serviceProvidersTable && columnExists($conn, 'service_providers', 'created_at') && columnExists($conn, 'service_providers', 'fullName')) {
        $sql = "SELECT * FROM service_providers ORDER BY created_at DESC LIMIT 1";
        $result = $conn->query($sql);
        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $activities[] = [
                'type' => 'provider',
                'icon' => 'fas fa-user-md',
                'title' => 'New Provider Registration',
                'description' => $row['fullName'] . ' registered as a service provider',
                'time' => date('d M Y, H:i', strtotime($row['created_at'])), // Changed to exact time
                'timestamp' => strtotime($row['created_at'])
            ];
        }
    }
    
    // Recent payments
    if ($paymentsTable && columnExists($conn, 'payments', 'created_at') && columnExists($conn, 'payments', 'amount')) {
        $sql = "SELECT * FROM payments WHERE status = 'completed' ORDER BY created_at DESC LIMIT 1";
        $result = $conn->query($sql);
        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $activities[] = [
                'type' => 'payment',
                'icon' => 'fas fa-money-bill-wave',
                'title' => 'Payment Received',
                'description' => 'A payment of ₹' . number_format($row['amount'], 2) . ' was received',
                'time' => date('d M Y, H:i', strtotime($row['created_at'])), // Changed to exact time
                'timestamp' => strtotime($row['created_at'])
            ];
        }
    }
    
    // Recent reviews
    if ($reviewsTable && columnExists($conn, 'reviews', 'created_at') && columnExists($conn, 'reviews', 'rating')) {
        $sql = "SELECT * FROM reviews ORDER BY created_at DESC LIMIT 1";
        $result = $conn->query($sql);
        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $activities[] = [
                'type' => 'review',
                'icon' => 'fas fa-star',
                'title' => 'New Review',
                'description' => 'A user left a ' . $row['rating'] . '-star review',
                'time' => date('d M Y, H:i', strtotime($row['created_at'])), // Changed to exact time
                'timestamp' => strtotime($row['created_at'])
            ];
        }
    }
    
    // Recent support tickets
    if ($supportTicketsTable && columnExists($conn, 'support_tickets', 'created_at') && columnExists($conn, 'support_tickets', 'subject')) {
        $sql = "SELECT * FROM support_tickets ORDER BY created_at DESC LIMIT 1";
        $result = $conn->query($sql);
        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $activities[] = [
                'type' => 'support',
                'icon' => 'fas fa-headset',
                'title' => 'Support Ticket',
                'description' => 'A support ticket was raised regarding ' . $row['subject'],
                'time' => date('d M Y, H:i', strtotime($row['created_at'])), // Changed to exact time
                'timestamp' => strtotime($row['created_at'])
            ];
        }
    }
    
    // Sort activities by timestamp (most recent first)
    usort($activities, function($a, $b) {
        return $b['timestamp'] - $a['timestamp'];
    });
    
    return array_slice($activities, 0, 10);
}
// Function to get monthly revenue data from bookings table
function getMonthlyRevenueData($conn) {
    if (!tableExists($conn, 'bookings')) {
        return [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0]; // Table doesn't exist
    }
    
    if (!columnExists($conn, 'bookings', 'created_at') || !columnExists($conn, 'bookings', 'total_amount')) {
        return [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0]; // Required columns don't exist
    }
    
    $currentYear = date('Y');
    $months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
    $revenueData = [];
    
    foreach ($months as $index => $month) {
        $monthNum = $index + 1;
        $sql = "SELECT SUM(total_amount) as total FROM bookings WHERE MONTH(created_at) = $monthNum AND YEAR(created_at) = $currentYear";
        $result = $conn->query($sql);
        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $revenueData[] = $row['total'] ? (float)$row['total'] : 0;
        } else {
            $revenueData[] = 0;
        }
    }
    
    return $revenueData;
}
// Helper function to format time elapsed
function time_elapsed_string($datetime, $full = false) {
    $now = new DateTime;
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);
    
    $diff->w = floor($diff->d / 7);
    $diff->d -= $diff->w * 7;
    
    $string = array(
        'y' => 'year',
        'm' => 'month',
        'w' => 'week',
        'd' => 'day',
        'h' => 'hour',
        'i' => 'minute',
        's' => 'second',
    );
    
    foreach ($string as $k => &$v) {
        if ($diff->$k) {
            $v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? 's' : '');
        } else {
            unset($string[$k]);
        }
    }
    
    if (!$full) $string = array_slice($string, 0, 1);
    return $string ? implode(', ', $string) . ' ago' : 'just now';
}
// Fetch data
$totalUsers = getTotalUsers($conn);
$totalProviders = getTotalProviders($conn);
$pendingProviders = getPendingProviders($conn);
$verifiedProviders = getVerifiedProviders($conn);
$rejectedProviders = getRejectedProviders($conn);
$totalServices = getTotalServices($conn);
$monthlyRevenue = getMonthlyRevenue($conn);
$totalBookings = getTotalBookings($conn);
$activeCoupons = getActiveCoupons($conn);
$todaysRevenue = getTodaysRevenue($conn);
$todaysBookings = getTodaysBookings($conn);
$recentActivities = getRecentActivities($conn);
$monthlyRevenueData = getMonthlyRevenueData($conn);
// Close database connection
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - SpitiCare</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --primary: #5d3b66;
            --primary-light: #8e44ad;
            --secondary: #ff6b6b;
            --accent: #ff9f43;
            --light: #f8f9fa;
            --dark: #2c3e50;
            --success: #10ac84;
            --warning: #ee5a24;
            --info: #0abde3;
            --text: #333;
            --text-light: #666;
            --bg-light: #ffffff;
            --white: #ffffff;
            --shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            --shadow-hover: 0 15px 35px rgba(0, 0, 0, 0.15);
            --transition: all 0.3s ease;
        }
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f5f7fa;
            color: var(--text);
            line-height: 1.6;
        }
        .dashboard-container {
            display: flex;
            min-height: 100vh;
        }
        /* Sidebar */
        .sidebar {
            width: 250px;
            background-color: var(--primary);
            color: white;
            padding: 20px 0;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
            transition: var(--transition);
        }
        .sidebar-header {
            padding: 0 20px 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            margin-bottom: 20px;
        }
        .sidebar-header h2 {
            font-size: 1.5rem;
            font-weight: 600;
        }
        .sidebar-menu {
            list-style: none;
        }
        .sidebar-menu li {
            margin-bottom: 5px;
        }
        .sidebar-menu a {
            display: block;
            padding: 12px 20px;
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            transition: var(--transition);
            font-weight: 500;
        }
        .sidebar-menu a:hover, .sidebar-menu a.active {
            background-color: rgba(255, 255, 255, 0.1);
            color: white;
        }
        .sidebar-menu i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }
        .sidebar-submenu {
            list-style: none;
            padding-left: 50px;
            display: none;
        }
        .sidebar-menu li:hover .sidebar-submenu {
            display: block;
        }
        .sidebar-submenu a {
            padding: 8px 20px;
            font-size: 0.9rem;
        }
        /* Main Content */
        .main-content {
            margin-left: 250px;
            flex: 1;
            padding: 20px;
        }
        .header {
            background-color: white;
            padding: 15px 20px;
            border-radius: 8px;
            box-shadow: var(--shadow);
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .header h1 {
            font-size: 1.8rem;
            color: var(--primary);
        }
        .admin-info {
            display: flex;
            align-items: center;
        }
        .admin-info img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            margin-right: 10px;
        }
        .admin-name {
            font-weight: 500;
        }
        .logout-btn {
            background-color: var(--secondary);
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 5px;
            cursor: pointer;
            margin-left: 15px;
            font-weight: 500;
            transition: var(--transition);
        }
        .logout-btn:hover {
            background-color: #e55039;
        }
        /* Welcome Card */
        .welcome-card {
            background-color: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: var(--shadow);
            margin-bottom: 30px;
            text-align: center;
        }
        .welcome-card h2 {
            color: var(--primary);
            margin-bottom: 15px;
        }
        .welcome-card p {
            color: var(--text-light);
            max-width: 800px;
            margin: 0 auto;
        }
        /* Stats Cards */
        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .stat-card {
            background-color: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: var(--shadow);
            display: flex;
            align-items: center;
            transition: var(--transition);
        }
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-hover);
        }
        .stat-icon {
            font-size: 2rem;
            margin-right: 15px;
            width: 50px;
            height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 10px;
        }
        .stat-card.users .stat-icon {
            background-color: rgba(0, 171, 227, 0.1);
            color: var(--info);
        }
        .stat-card.providers .stat-icon {
            background-color: rgba(16, 172, 132, 0.1);
            color: var(--success);
        }
        .stat-card.services .stat-icon {
            background-color: rgba(238, 90, 36, 0.1);
            color: var(--warning);
        }
        .stat-card.revenue .stat-icon {
            background-color: rgba(93, 59, 102, 0.1);
            color: var(--primary);
        }
        .stat-card.bookings .stat-icon {
            background-color: rgba(255, 107, 107, 0.1);
            color: var(--secondary);
        }
        .stat-card.coupons .stat-icon {
            background-color: rgba(255, 159, 67, 0.1);
            color: var(--accent);
        }
        .stat-card.todays-revenue .stat-icon {
            background-color: rgba(16, 172, 132, 0.1);
            color: var(--success);
        }
        .stat-card.todays-bookings .stat-icon {
            background-color: rgba(0, 171, 227, 0.1);
            color: var(--info);
        }
        .stat-info h3 {
            font-size: 1.8rem;
            font-weight: 600;
            margin-bottom: 5px;
        }
        .stat-info p {
            color: var(--text-light);
            font-size: 0.9rem;
        }
        /* Quick Actions */
        .quick-actions {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .action-card {
            background-color: white;
            border-radius: 10px;
            padding: 25px;
            box-shadow: var(--shadow);
            text-align: center;
            transition: var(--transition);
        }
        .action-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-hover);
        }
        .action-icon {
            font-size: 2.5rem;
            margin-bottom: 15px;
        }
        .action-card.providers .action-icon {
            color: var(--success);
        }
        .action-card.users .action-icon {
            color: var(--info);
        }
        .action-card.services .action-icon {
            color: var(--warning);
        }
        .action-card.reports .action-icon {
            color: var(--primary);
        }
        .action-card.coupons .action-icon {
            color: var(--accent);
        }
        .action-card.feedback .action-icon {
            color: var(--secondary);
        }
        .action-card.support .action-icon {
            color: var(--info);
        }
        .action-card.enquiry .action-icon {
            color: var(--success);
        }
        .action-card.revenue .action-icon {
            color: var(--primary);
        }
        .action-title {
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 10px;
        }
        .action-description {
            color: var(--text-light);
            margin-bottom: 20px;
        }
        .action-btn {
            background-color: var(--primary);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 500;
            transition: var(--transition);
            text-decoration: none;
            display: inline-block;
        }
        .action-btn:hover {
            background-color: var(--primary-light);
        }
        /* Recent Activity */
        .recent-activity {
            background-color: white;
            border-radius: 10px;
            padding: 25px;
            box-shadow: var(--shadow);
            margin-bottom: 30px;
        }
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        .section-header h2 {
            color: var(--primary);
            font-size: 1.5rem;
        }
        .view-all-btn {
            color: var(--primary);
            text-decoration: none;
            font-weight: 500;
            font-size: 0.9rem;
            transition: var(--transition);
        }
        .view-all-btn:hover {
            text-decoration: underline;
        }
        .activity-list {
            list-style: none;
        }
        .activity-item {
            padding: 15px 0;
            border-bottom: 1px solid #eee;
            display: flex;
            align-items: center;
            transition: var(--transition);
        }
        .activity-item:last-child {
            border-bottom: none;
        }
        .activity-item:hover {
            background-color: rgba(93, 59, 102, 0.05);
            margin: 0 -15px;
            padding-left: 15px;
            padding-right: 15px;
            border-radius: 5px;
        }
        .activity-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            flex-shrink: 0;
        }
        .activity-item.booking .activity-icon {
            background-color: rgba(0, 171, 227, 0.1);
            color: var(--info);
        }
        .activity-item.payment .activity-icon {
            background-color: rgba(16, 172, 132, 0.1);
            color: var(--success);
        }
        .activity-item.review .activity-icon {
            background-color: rgba(255, 159, 67, 0.1);
            color: var(--accent);
        }
        .activity-item.support .activity-icon {
            background-color: rgba(255, 107, 107, 0.1);
            color: var(--secondary);
        }
        .activity-item.user .activity-icon {
            background-color: rgba(0, 171, 227, 0.1);
            color: var(--info);
        }
        .activity-item.provider .activity-icon {
            background-color: rgba(16, 172, 132, 0.1);
            color: var(--success);
        }
        .activity-details {
            flex-grow: 1;
        }
        .activity-details h4 {
            font-size: 1rem;
            margin-bottom: 5px;
            color: var(--primary);
        }
        .activity-details p {
            color: var(--text-light);
            font-size: 0.9rem;
        }
        .activity-time {
            color: var(--text-light);
            font-size: 0.8rem;
            flex-shrink: 0;
        }
        /* Revenue Chart */
        .revenue-chart {
            background-color: white;
            border-radius: 10px;
            padding: 25px;
            box-shadow: var(--shadow);
            margin-bottom: 30px;
        }
        .chart-container {
            position: relative;
            height: 300px;
        }
        /* Mobile Menu Button */
        .mobile-menu-btn {
            display: none;
            background: none;
            border: none;
            color: var(--primary);
            font-size: 1.5rem;
            cursor: pointer;
        }
        /* Notification Badge */
        .notification-badge {
            position: absolute;
            top: 0;
            right: 0;
            background-color: var(--secondary);
            color: white;
            font-size: 0.7rem;
            width: 18px;
            height: 18px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        /* Responsive Design */
        @media (max-width: 992px) {
            .sidebar {
                width: 200px;
            }
            .main-content {
                margin-left: 200px;
            }
            .stats-container {
                grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            }
        }
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
                z-index: 1000;
            }
            .sidebar.active {
                transform: translateX(0);
            }
            .main-content {
                margin-left: 0;
            }
            .mobile-menu-btn {
                display: block;
            }
            .quick-actions {
                grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            }
            .stats-container {
                grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            }
        }
        @media (max-width: 576px) {
            .header {
                flex-direction: column;
                align-items: flex-start;
            }
            .admin-info {
                margin-top: 10px;
                width: 100%;
                justify-content: space-between;
            }
            .quick-actions {
                grid-template-columns: 1fr;
            }
            .stats-container {
                grid-template-columns: repeat(2, 1fr);
            }
            .section-header {
                flex-direction: column;
                align-items: flex-start;
            }
            .view-all-btn {
                margin-top: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <h2><i class="fas fa-home"></i> SpitiCare</h2>
            </div>
            <ul class="sidebar-menu">
                <li><a href="admin_dashboard.php" class="active"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li>
                    <a href="#"><i class="fas fa-users"></i> Users <i class="fas fa-chevron-down" style="float: right;"></i></a>
                    <ul class="sidebar-submenu">
                        <li><a href="admin_manage_users.php"><i class="fas fa-user"></i> Manage Users</a></li>
                        <li><a href="admin_user_analytics.php"><i class="fas fa-chart-pie"></i> User Analytics</a></li>
                    </ul>
                </li>
                <li>
                    <a href="#"><i class="fas fa-user-md"></i> Providers <i class="fas fa-chevron-down" style="float: right;"></i></a>
                    <ul class="sidebar-submenu">
                        <li><a href="admin_manage_providers.php"><i class="fas fa-user-md"></i> Manage Providers</a></li>
                        <li><a href="admin_verify_providers.php"><i class="fas fa-user-check"></i> Verify Providers</a></li>
                        <li><a href="admin_provider_analytics.php"><i class="fas fa-chart-line"></i> Provider Analytics</a></li>
                    </ul>
                </li>
                <li>
                    <a href="#"><i class="fas fa-concierge-bell"></i> Services <i class="fas fa-chevron-down" style="float: right;"></i></a>
                    <ul class="sidebar-submenu">
                        <li><a href="admin_manage_services.php"><i class="fas fa-calendar-check"></i> Service Bookings</a></li>
                        <li><a href="admin_service_categories.php"><i class="fas fa-list"></i> Service Categories</a></li>
                    </ul>
                </li>
                <li>
                    <a href="#"><i class="fas fa-ticket-alt"></i> Coupons <i class="fas fa-chevron-down" style="float: right;"></i></a>
                    <ul class="sidebar-submenu">
                        <li><a href="admin_create_coupon.php"><i class="fas fa-plus"></i> Create Coupon</a></li>
                        <li><a href="admin_manage_coupons.php"><i class="fas fa-tags"></i> Manage Coupons</a></li>
                        <li><a href="admin_coupon_analytics.php"><i class="fas fa-chart-bar"></i> Coupon Analytics</a></li>
                    </ul>
                </li>
                <li>
                    <a href="#"><i class="fas fa-comments"></i> Feedback <i class="fas fa-chevron-down" style="float: right;"></i></a>
                    <ul class="sidebar-submenu">
                        <li><a href="admin_user_feedback.php"><i class="fas fa-user"></i> User Feedback</a></li>
                        <li><a href="admin_provider_feedback.php"><i class="fas fa-user-md"></i> Provider Feedback</a></li>
                        <li><a href="admin_feedback_analytics.php"><i class="fas fa-chart-pie"></i> Feedback Analytics</a></li>
                    </ul>
                </li>
                <li>
                    <a href="#"><i class="fas fa-life-ring"></i> Support <i class="fas fa-chevron-down" style="float: right;"></i></a>
                    <ul class="sidebar-submenu">
                        <li><a href="admin_support_tickets.php"><i class="fas fa-ticket-alt"></i> Support Tickets</a></li>
                        <li><a href="admin_faq.php"><i class="fas fa-question-circle"></i> FAQ Management</a></li>
                    </ul>
                </li>
                <li>
                    <a href="#"><i class="fas fa-question-circle"></i> Enquiries <i class="fas fa-chevron-down" style="float: right;"></i></a>
                    <ul class="sidebar-submenu">
                        <li><a href="admin_user_enquiries.php"><i class="fas fa-user"></i> User Enquiries</a></li>
                        <li><a href="admin_provider_enquiries.php"><i class="fas fa-user-md"></i> Provider Enquiries</a></li>
                    </ul>
                </li>
                <li>
                    <a href="#"><i class="fas fa-chart-line"></i> Revenue <i class="fas fa-chevron-down" style="float: right;"></i></a>
                    <ul class="sidebar-submenu">
                        <li><a href="admin_revenue_reports.php"><i class="fas fa-chart-line"></i> Revenue Reports</a></li>
                        <li><a href="admin_transactions.php"><i class="fas fa-exchange-alt"></i> Transactions</a></li>
                        <li><a href="admin_payouts.php"><i class="fas fa-hand-holding-usd"></i> Provider Payouts</a></li>
                    </ul>
                </li>
                <li><a href="admin_reports.php"><i class="fas fa-chart-bar"></i> Reports</a></li>
                <li><a href="admin_settings.php"><i class="fas fa-cog"></i> Settings</a></li>
            </ul>
        </aside>
        
        <!-- Main Content -->
        <main class="main-content">
            <div class="header">
                <div style="display: flex; align-items: center;">
                    <button class="mobile-menu-btn" id="mobile-menu-btn">
                        <i class="fas fa-bars"></i>
                    </button>
                    <h1>Admin Dashboard</h1>
                </div>
                <div class="admin-info">
                    <img src="https://picsum.photos/seed/admin/40/40.jpg" alt="Admin">
                    <span class="admin-name"><?php echo $_SESSION['admin_username']; ?></span>
                    <a href="admin_logout.php" class="logout-btn">Logout</a>
                </div>
            </div>
            
            <div class="welcome-card">
                <h2>Welcome to the SpitiCare Admin Panel</h2>
                <p>Manage your service providers, users, coupons, feedback, support, enquiries, and revenue from this central dashboard. Use the sidebar to navigate through different sections of the admin panel.</p>
            </div>
            
            <!-- Stats Cards -->
            <div class="stats-container">
                <div class="stat-card users">
                    <div class="stat-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $totalUsers; ?></h3>
                        <p>Total Users</p>
                    </div>
                </div>
                
                <div class="stat-card providers">
                    <div class="stat-icon">
                        <i class="fas fa-user-md"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $totalProviders; ?></h3>
                        <p>Total Providers</p>
                    </div>
                </div>
                
                <div class="stat-card providers">
                    <div class="stat-icon" style="background-color: rgba(255, 159, 67, 0.1); color: var(--accent);">
                        <i class="fas fa-user-clock"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $pendingProviders; ?></h3>
                        <p>Pending Verification</p>
                    </div>
                </div>
                
                <div class="stat-card providers">
                    <div class="stat-icon" style="background-color: rgba(16, 172, 132, 0.1); color: var(--success);">
                        <i class="fas fa-user-check"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $verifiedProviders; ?></h3>
                        <p>Verified Providers</p>
                    </div>
                </div>
                
                <div class="stat-card services">
                    <div class="stat-icon">
                        <i class="fas fa-concierge-bell"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $totalServices; ?></h3>
                        <p>Services</p>
                    </div>
                </div>
                
                <div class="stat-card revenue">
                    <div class="stat-icon">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $monthlyRevenue; ?></h3>
                        <p>Monthly Revenue</p>
                    </div>
                </div>
                
                <div class="stat-card bookings">
                    <div class="stat-icon">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $totalBookings; ?></h3>
                        <p>Total Bookings</p>
                    </div>
                </div>
                
                <div class="stat-card coupons">
                    <div class="stat-icon">
                        <i class="fas fa-ticket-alt"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $activeCoupons; ?></h3>
                        <p>Active Coupons</p>
                    </div>
                </div>
                
                <!-- Today's Revenue Card -->
                <div class="stat-card todays-revenue">
                    <div class="stat-icon">
                        <i class="fas fa-money-bill-wave"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $todaysRevenue; ?></h3>
                        <p>Today's Revenue</p>
                    </div>
                </div>
                
                <!-- Today's Bookings Card -->
                <div class="stat-card todays-bookings">
                    <div class="stat-icon">
                        <i class="fas fa-calendar-day"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $todaysBookings; ?></h3>
                        <p>Today's Bookings</p>
                    </div>
                </div>
            </div>
            
            <!-- Quick Actions -->
            <div class="quick-actions">
                <div class="action-card providers">
                    <div class="action-icon">
                        <i class="fas fa-user-md"></i>
                    </div>
                    <div class="action-title">Manage Providers</div>
                    <div class="action-description">View and manage all service providers</div>
                    <a href="admin_manage_providers.php" class="action-btn">Manage</a>
                </div>
                
                <div class="action-card providers">
                    <div class="action-icon" style="color: var(--accent);">
                        <i class="fas fa-user-check"></i>
                    </div>
                    <div class="action-title">Verify Providers</div>
                    <div class="action-description">Review and verify new provider applications</div>
                    <a href="admin_verify_providers.php" class="action-btn">Verify</a>
                </div>
                
                <div class="action-card users">
                    <div class="action-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="action-title">Manage Users</div>
                    <div class="action-description">View and manage registered users</div>
                    <a href="admin_manage_users.php" class="action-btn">Manage</a>
                </div>
                
                <div class="action-card services">
                    <div class="action-icon">
                        <i class="fas fa-concierge-bell"></i>
                    </div>
                    <div class="action-title">Manage Services</div>
                    <div class="action-description">Add or modify service categories</div>
                    <a href="admin_manage_services.php" class="action-btn">Manage</a>
                </div>
                
                <div class="action-card coupons">
                    <div class="action-icon">
                        <i class="fas fa-ticket-alt"></i>
                    </div>
                    <div class="action-title">Create Coupons</div>
                    <div class="action-description">Generate discount coupons for users</div>
                    <a href="admin_create_coupon.php" class="action-btn">Create</a>
                </div>
                
                <div class="action-card feedback">
                    <div class="action-icon">
                        <i class="fas fa-comments"></i>
                    </div>
                    <div class="action-title">User Feedback</div>
                    <div class="action-description">View and respond to user feedback</div>
                    <a href="admin_user_feedback.php" class="action-btn">View</a>
                </div>
                
                <div class="action-card support">
                    <div class="action-icon">
                        <i class="fas fa-life-ring"></i>
                    </div>
                    <div class="action-title">Support Tickets</div>
                    <div class="action-description">Manage and resolve support tickets</div>
                    <a href="admin_support_tickets.php" class="action-btn">Manage</a>
                </div>
                
                <div class="action-card revenue">
                    <div class="action-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <div class="action-title">Revenue Reports</div>
                    <div class="action-description">View financial reports and analytics</div>
                    <a href="admin_revenue_reports.php" class="action-btn">View</a>
                </div>
            </div>
            
            <!-- Recent Activity -->
            <div class="recent-activity">
                <div class="section-header">
                    <h2>Recent Activity</h2>
                    <a href="admin_all_activities.php" class="view-all-btn">View All</a>
                </div>
                <ul class="activity-list">
                    <?php if (empty($recentActivities)): ?>
                        <li style="text-align: center; padding: 20px; color: var(--text-light);">
                            No recent activity found
                        </li>
                    <?php else: ?>
                        <?php foreach ($recentActivities as $activity): ?>
                        <li class="activity-item <?php echo $activity['type']; ?>">
                            <div class="activity-icon">
                                <i class="<?php echo $activity['icon']; ?>"></i>
                            </div>
                            <div class="activity-details">
                                <h4><?php echo $activity['title']; ?></h4>
                                <p><?php echo $activity['description']; ?></p>
                            </div>
                            <div class="activity-time"><?php echo $activity['time']; ?></div>
                        </li>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </ul>
            </div>
            
            <!-- Revenue Chart -->
            <div class="revenue-chart">
                <div class="section-header">
                    <h2>Revenue Overview</h2>
                    <a href="admin_revenue_reports.php" class="view-all-btn">View Report</a>
                </div>
                <div class="chart-container">
                    <canvas id="revenueChart"></canvas>
                </div>
            </div>
        </main>
    </div>
    
    <script>
        // Mobile menu toggle
        const mobileMenuBtn = document.getElementById('mobile-menu-btn');
        const sidebar = document.getElementById('sidebar');
        
        mobileMenuBtn.addEventListener('click', function() {
            sidebar.classList.toggle('active');
        });
        
        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', function(event) {
            if (window.innerWidth <= 768) {
                if (!sidebar.contains(event.target) && !mobileMenuBtn.contains(event.target)) {
                    sidebar.classList.remove('active');
                }
            }
        });
        
        // Revenue Chart
        const ctx = document.getElementById('revenueChart').getContext('2d');
        const revenueChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                datasets: [{
                    label: 'Revenue',
                    data: <?php echo json_encode($monthlyRevenueData); ?>,
                    backgroundColor: 'rgba(93, 59, 102, 0.1)',
                    borderColor: '#5d3b66',
                    borderWidth: 2,
                    tension: 0.4,
                    pointBackgroundColor: '#5d3b66',
                    pointBorderColor: '#fff',
                    pointRadius: 4,
                    pointHoverRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.7)',
                        padding: 10,
                        titleFont: {
                            size: 14
                        },
                        bodyFont: {
                            size: 14
                        },
                        callbacks: {
                            label: function(context) {
                                return '₹' + context.raw.toLocaleString();
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 10000,
                            callback: function(value) {
                                if (value >= 1000) {
                                    return '₹' + (value/1000) + 'k';
                                }
                                return '₹' + value;
                            }
                        },
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });
    </script>
</body>
</html>