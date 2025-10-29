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
// Create database connection
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
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
// Function to get user registration data by month
function getUserRegistrationData($conn, $year = null) {
    if (!tableExists($conn, 'users')) {
        return [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0]; // Table doesn't exist
    }
    
    if (!columnExists($conn, 'users', 'created_at')) {
        return [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0]; // Column doesn't exist
    }
    
    $currentYear = $year ?: date('Y');
    $months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
    $registrationData = [];
    
    foreach ($months as $index => $month) {
        $monthNum = $index + 1;
        
        $sql = "SELECT COUNT(*) as total FROM users WHERE MONTH(created_at) = $monthNum AND YEAR(created_at) = $currentYear";
        
        $result = $conn->query($sql);
        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $registrationData[] = (int)$row['total'];
        } else {
            $registrationData[] = 0;
        }
    }
    
    return $registrationData;
}
// Function to get provider registration data by month
function getProviderRegistrationData($conn, $year = null) {
    if (!tableExists($conn, 'service_providers')) {
        return [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0]; // Table doesn't exist
    }
    
    if (!columnExists($conn, 'service_providers', 'created_at')) {
        return [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0]; // Column doesn't exist
    }
    
    $currentYear = $year ?: date('Y');
    $months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
    $registrationData = [];
    
    foreach ($months as $index => $month) {
        $monthNum = $index + 1;
        
        $sql = "SELECT COUNT(*) as total FROM service_providers WHERE MONTH(created_at) = $monthNum AND YEAR(created_at) = $currentYear";
        
        $result = $conn->query($sql);
        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $registrationData[] = (int)$row['total'];
        } else {
            $registrationData[] = 0;
        }
    }
    
    return $registrationData;
}
// Function to get booking data by month
function getBookingData($conn, $year = null) {
    if (!tableExists($conn, 'bookings')) {
        return [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0]; // Table doesn't exist
    }
    
    if (!columnExists($conn, 'bookings', 'created_at')) {
        return [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0]; // Column doesn't exist
    }
    
    $currentYear = $year ?: date('Y');
    $months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
    $bookingData = [];
    
    foreach ($months as $index => $month) {
        $monthNum = $index + 1;
        
        $sql = "SELECT COUNT(*) as total FROM bookings WHERE MONTH(created_at) = $monthNum AND YEAR(created_at) = $currentYear";
        
        $result = $conn->query($sql);
        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $bookingData[] = (int)$row['total'];
        } else {
            $bookingData[] = 0;
        }
    }
    
    return $bookingData;
}
// Function to get revenue data by month
function getRevenueData($conn, $year = null) {
    if (!tableExists($conn, 'bookings')) {
        return [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0]; // Table doesn't exist
    }
    
    if (!columnExists($conn, 'bookings', 'created_at') || !columnExists($conn, 'bookings', 'total_amount')) {
        return [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0]; // Required columns don't exist
    }
    
    $currentYear = $year ?: date('Y');
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
// Function to get service category distribution
function getServiceCategoryDistribution($conn) {
    $distribution = [];
    
    if (!tableExists($conn, 'services')) {
        return $distribution; // Table doesn't exist
    }
    
    if (!columnExists($conn, 'services', 'category')) {
        return $distribution; // Column doesn't exist
    }
    
    $sql = "SELECT category, COUNT(*) as count FROM services GROUP BY category";
    $result = $conn->query($sql);
    
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $distribution[] = [
                'category' => $row['category'],
                'count' => (int)$row['count']
            ];
        }
    }
    
    return $distribution;
}
// Function to get provider status distribution
function getProviderStatusDistribution($conn) {
    $distribution = [
        'pending' => 0,
        'verified' => 0,
        'rejected' => 0
    ];
    
    if (!tableExists($conn, 'service_providers')) {
        return $distribution; // Table doesn't exist
    }
    
    if (!columnExists($conn, 'service_providers', 'status')) {
        return $distribution; // Column doesn't exist
    }
    
    $sql = "SELECT status, COUNT(*) as count FROM service_providers GROUP BY status";
    $result = $conn->query($sql);
    
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            if (isset($distribution[$row['status']])) {
                $distribution[$row['status']] = (int)$row['count'];
            }
        }
    }
    
    return $distribution;
}
// Function to get top service providers by bookings
function getTopProvidersByBookings($conn, $limit = 5) {
    $providers = [];
    
    if (!tableExists($conn, 'bookings')) {
        return $providers; // Table doesn't exist
    }
    
    if (!columnExists($conn, 'bookings', 'provider_id') || !columnExists($conn, 'bookings', 'provider_name')) {
        return $providers; // Required columns don't exist
    }
    
    $sql = "SELECT provider_id, provider_name, COUNT(*) as booking_count FROM bookings GROUP BY provider_id, provider_name ORDER BY booking_count DESC LIMIT $limit";
    $result = $conn->query($sql);
    
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $providers[] = [
                'id' => $row['provider_id'],
                'name' => $row['provider_name'],
                'bookings' => (int)$row['booking_count']
            ];
        }
    }
    
    return $providers;
}
// Function to get top services by bookings
function getTopServicesByBookings($conn, $limit = 5) {
    $services = [];
    
    if (!tableExists($conn, 'bookings')) {
        return $services; // Table doesn't exist
    }
    
    if (!columnExists($conn, 'bookings', 'service_id') || !columnExists($conn, 'bookings', 'service_details')) {
        return $services; // Required columns don't exist
    }
    
    $sql = "SELECT service_id, service_details, COUNT(*) as booking_count FROM bookings GROUP BY service_id, service_details ORDER BY booking_count DESC LIMIT $limit";
    $result = $conn->query($sql);
    
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $services[] = [
                'id' => $row['service_id'],
                'name' => $row['service_details'],
                'bookings' => (int)$row['booking_count']
            ];
        }
    }
    
    return $services;
}
// Function to get user registration distribution by month
function getUserRegistrationDistribution($conn, $year = null) {
    $distribution = [];
    
    if (!tableExists($conn, 'users')) {
        return $distribution; // Table doesn't exist
    }
    
    if (!columnExists($conn, 'users', 'created_at')) {
        return $distribution; // Column doesn't exist
    }
    
    $currentYear = $year ?: date('Y');
    $months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
    
    foreach ($months as $index => $month) {
        $monthNum = $index + 1;
        $sql = "SELECT COUNT(*) as total FROM users WHERE MONTH(created_at) = $monthNum AND YEAR(created_at) = $currentYear";
        $result = $conn->query($sql);
        
        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $distribution[] = [
                'month' => $month,
                'count' => (int)$row['total']
            ];
        } else {
            $distribution[] = [
                'month' => $month,
                'count' => 0
            ];
        }
    }
    
    return $distribution;
}
// Function to get booking distribution by month
function getBookingDistribution($conn, $year = null) {
    $distribution = [];
    
    if (!tableExists($conn, 'bookings')) {
        return $distribution; // Table doesn't exist
    }
    
    if (!columnExists($conn, 'bookings', 'created_at')) {
        return $distribution; // Column doesn't exist
    }
    
    $currentYear = $year ?: date('Y');
    $months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
    
    foreach ($months as $index => $month) {
        $monthNum = $index + 1;
        $sql = "SELECT COUNT(*) as total FROM bookings WHERE MONTH(created_at) = $monthNum AND YEAR(created_at) = $currentYear";
        $result = $conn->query($sql);
        
        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $distribution[] = [
                'month' => $month,
                'count' => (int)$row['total']
            ];
        } else {
            $distribution[] = [
                'month' => $month,
                'count' => 0
            ];
        }
    }
    
    return $distribution;
}
// Get filter parameters
$reportType = isset($_GET['type']) ? $_GET['type'] : 'overview';
$year = isset($_GET['year']) ? (int)$_GET['year'] : date('Y');
// Fetch data based on report type
$userRegistrationData = getUserRegistrationData($conn, $year);
$providerRegistrationData = getProviderRegistrationData($conn, $year);
$bookingData = getBookingData($conn, $year);
$revenueData = getRevenueData($conn, $year);
$serviceCategoryDistribution = getServiceCategoryDistribution($conn);
$providerStatusDistribution = getProviderStatusDistribution($conn);
$topProviders = getTopProvidersByBookings($conn);
$topServices = getTopServicesByBookings($conn);
$userRegistrationDistribution = getUserRegistrationDistribution($conn, $year);
$bookingDistribution = getBookingDistribution($conn, $year);
// Close database connection
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports - SpitiCare Admin</title>
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
        /* Report Filters */
        .report-filters {
            background-color: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: var(--shadow);
            margin-bottom: 20px;
        }
        .filter-row {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            align-items: center;
        }
        .filter-group {
            flex: 1;
            min-width: 200px;
        }
        .filter-group.wide {
            flex: 2;
        }
        .filter-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
            color: var(--text);
        }
        .filter-group select,
        .filter-group input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-family: 'Poppins', sans-serif;
        }
        .filter-actions {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }
        .filter-btn {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 500;
            transition: var(--transition);
        }
        .filter-btn-primary {
            background-color: var(--primary);
            color: white;
        }
        .filter-btn-primary:hover {
            background-color: var(--primary-light);
        }
        .filter-btn-secondary {
            background-color: #e0e0e0;
            color: var(--text);
        }
        .filter-btn-secondary:hover {
            background-color: #d0d0d0;
        }
        /* Report Tabs */
        .report-tabs {
            display: flex;
            background-color: white;
            border-radius: 10px 10px 0 0;
            box-shadow: var(--shadow);
            overflow: hidden;
            margin-bottom: 0;
        }
        .report-tab {
            flex: 1;
            padding: 15px 20px;
            text-align: center;
            background-color: #f5f5f5;
            border: none;
            cursor: pointer;
            font-weight: 500;
            transition: var(--transition);
            color: var(--text);
        }
        .report-tab.active {
            background-color: var(--primary);
            color: white;
        }
        .report-tab:hover:not(.active) {
            background-color: #e0e0e0;
        }
        /* Report Content */
        .report-content {
            background-color: white;
            border-radius: 0 0 10px 10px;
            padding: 25px;
            box-shadow: var(--shadow);
            margin-bottom: 20px;
        }
        .report-section {
            margin-bottom: 30px;
        }
        .report-section:last-child {
            margin-bottom: 0;
        }
        .section-title {
            font-size: 1.3rem;
            color: var(--primary);
            margin-bottom: 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .chart-container {
            position: relative;
            height: 300px;
            margin-bottom: 20px;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }
        .stat-box {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 15px;
            text-align: center;
            border-left: 4px solid var(--primary);
        }
        .stat-box.users {
            border-left-color: var(--info);
        }
        .stat-box.providers {
            border-left-color: var(--success);
        }
        .stat-box.bookings {
            border-left-color: var(--warning);
        }
        .stat-box.revenue {
            border-left-color: var(--primary);
        }
        .stat-value {
            font-size: 1.8rem;
            font-weight: 600;
            margin-bottom: 5px;
        }
        .stat-label {
            color: var(--text-light);
            font-size: 0.9rem;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        .data-table th,
        .data-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        .data-table th {
            background-color: #f8f9fa;
            font-weight: 600;
            color: var(--primary);
        }
        .data-table tr:hover {
            background-color: #f8f9fa;
        }
        .data-table .rank {
            font-weight: 600;
            color: var(--primary);
        }
        .two-column-layout {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        .three-column-layout {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 20px;
        }
        .no-data {
            text-align: center;
            padding: 30px;
            color: var(--text-light);
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
        /* Responsive Design */
        @media (max-width: 992px) {
            .sidebar {
                width: 200px;
            }
            .main-content {
                margin-left: 200px;
            }
            .two-column-layout {
                grid-template-columns: 1fr;
            }
            .three-column-layout {
                grid-template-columns: 1fr 1fr;
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
            .filter-row {
                flex-direction: column;
            }
            .filter-group {
                width: 100%;
            }
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            .three-column-layout {
                grid-template-columns: 1fr;
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
            .report-tabs {
                flex-direction: column;
            }
            .stats-grid {
                grid-template-columns: 1fr;
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
                <li><a href="admin_dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
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
                <li><a href="admin_reports.php" class="active"><i class="fas fa-chart-bar"></i> Reports</a></li>
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
                    <h1>Reports & Analytics</h1>
                </div>
                <div class="admin-info">
                    <img src="https://picsum.photos/seed/admin/40/40.jpg" alt="Admin">
                    <span class="admin-name"><?php echo htmlspecialchars($_SESSION['admin_username']); ?></span>
                    <a href="admin_logout.php" class="logout-btn">Logout</a>
                </div>
            </div>
            
            <!-- Report Filters -->
            <div class="report-filters">
                <div class="filter-row">
                    <div class="filter-group wide">
                        <label for="report-type">Report Type</label>
                        <select id="report-type" name="type">
                            <option value="overview" <?php echo $reportType === 'overview' ? 'selected' : ''; ?>>Overview</option>
                            <option value="users" <?php echo $reportType === 'users' ? 'selected' : ''; ?>>Users</option>
                            <option value="providers" <?php echo $reportType === 'providers' ? 'selected' : ''; ?>>Providers</option>
                            <option value="bookings" <?php echo $reportType === 'bookings' ? 'selected' : ''; ?>>Bookings</option>
                            <option value="revenue" <?php echo $reportType === 'revenue' ? 'selected' : ''; ?>>Revenue</option>
                            <option value="services" <?php echo $reportType === 'services' ? 'selected' : ''; ?>>Services</option>
                        </select>
                    </div>
                    <div class="filter-group wide">
                        <label for="year">Year</label>
                        <select id="year" name="year">
                            <?php for ($y = date('Y'); $y >= date('Y') - 5; $y--): ?>
                                <option value="<?php echo $y; ?>" <?php echo $year == $y ? 'selected' : ''; ?>><?php echo $y; ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                </div>
                <div class="filter-actions">
                    <button type="button" class="filter-btn filter-btn-primary" onclick="applyFilters()">Apply Filters</button>
                    <button type="button" class="filter-btn filter-btn-secondary" onclick="resetFilters()">Reset</button>
                </div>
            </div>
            
            <!-- Report Tabs -->
            <div class="report-tabs">
                <button class="report-tab <?php echo $reportType === 'overview' ? 'active' : ''; ?>" onclick="switchTab('overview')">Overview</button>
                <button class="report-tab <?php echo $reportType === 'users' ? 'active' : ''; ?>" onclick="switchTab('users')">Users</button>
                <button class="report-tab <?php echo $reportType === 'providers' ? 'active' : ''; ?>" onclick="switchTab('providers')">Providers</button>
                <button class="report-tab <?php echo $reportType === 'bookings' ? 'active' : ''; ?>" onclick="switchTab('bookings')">Bookings</button>
                <button class="report-tab <?php echo $reportType === 'revenue' ? 'active' : ''; ?>" onclick="switchTab('revenue')">Revenue</button>
                <button class="report-tab <?php echo $reportType === 'services' ? 'active' : ''; ?>" onclick="switchTab('services')">Services</button>
            </div>
            
            <!-- Report Content -->
            <div class="report-content">
                <!-- Overview Report -->
                <?php if ($reportType === 'overview'): ?>
                    <div class="report-section">
                        <h3 class="section-title">
                            Platform Overview
                        </h3>
                        <div class="stats-grid">
                            <div class="stat-box users">
                                <div class="stat-value"><?php echo array_sum($userRegistrationData); ?></div>
                                <div class="stat-label">Total Users</div>
                            </div>
                            <div class="stat-box providers">
                                <div class="stat-value"><?php echo array_sum($providerRegistrationData); ?></div>
                                <div class="stat-label">Total Providers</div>
                            </div>
                            <div class="stat-box bookings">
                                <div class="stat-value"><?php echo array_sum($bookingData); ?></div>
                                <div class="stat-label">Total Bookings</div>
                            </div>
                            <div class="stat-box revenue">
                                <div class="stat-value">₹<?php echo number_format(array_sum($revenueData), 2); ?></div>
                                <div class="stat-label">Total Revenue</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="report-section">
                        <h3 class="section-title">Registration Trends</h3>
                        <div class="chart-container">
                            <canvas id="registrationChart"></canvas>
                        </div>
                    </div>
                    
                    <div class="three-column-layout">
                        <div class="report-section">
                            <h3 class="section-title">User Distribution</h3>
                            <div class="chart-container">
                                <canvas id="userDistributionChart"></canvas>
                            </div>
                        </div>
                        
                        <div class="report-section">
                            <h3 class="section-title">Booking Distribution</h3>
                            <div class="chart-container">
                                <canvas id="bookingDistributionChart"></canvas>
                            </div>
                        </div>
                        
                        <div class="report-section">
                            <h3 class="section-title">Provider Status</h3>
                            <div class="chart-container">
                                <canvas id="providerStatusChart"></canvas>
                            </div>
                        </div>
                    </div>
                    
                    <div class="report-section">
                        <h3 class="section-title">Top Service Providers</h3>
                        <?php if (empty($topProviders)): ?>
                            <div class="no-data">No provider data available</div>
                        <?php else: ?>
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Rank</th>
                                        <th>Provider</th>
                                        <th>Bookings</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($topProviders as $index => $provider): ?>
                                        <tr>
                                            <td class="rank">#<?php echo $index + 1; ?></td>
                                            <td><?php echo htmlspecialchars($provider['name']); ?></td>
                                            <td><?php echo $provider['bookings']; ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
                
                <!-- Users Report -->
                <?php if ($reportType === 'users'): ?>
                    <div class="report-section">
                        <h3 class="section-title">
                            User Registration Analytics
                        </h3>
                        <div class="stats-grid">
                            <div class="stat-box users">
                                <div class="stat-value"><?php echo array_sum($userRegistrationData); ?></div>
                                <div class="stat-label">Total Users</div>
                            </div>
                            <div class="stat-box users">
                                <div class="stat-value"><?php echo max($userRegistrationData); ?></div>
                                <div class="stat-label">Peak Month</div>
                            </div>
                            <div class="stat-box users">
                                <div class="stat-value"><?php echo round(array_sum($userRegistrationData) / 12, 1); ?></div>
                                <div class="stat-label">Avg. Monthly</div>
                            </div>
                            <div class="stat-box users">
                                <div class="stat-value"><?php echo $userRegistrationData[date('n') - 1]; ?></div>
                                <div class="stat-label">This Month</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="report-section">
                        <h3 class="section-title">User Registration by Month (<?php echo $year; ?>)</h3>
                        <div class="chart-container">
                            <canvas id="userRegistrationChart"></canvas>
                        </div>
                    </div>
                    
                    
                    </div>
                <?php endif; ?>
                
                <!-- Providers Report -->
                <?php if ($reportType === 'providers'): ?>
                    <div class="report-section">
                        <h3 class="section-title">
                            Provider Registration Analytics
                        </h3>
                        <div class="stats-grid">
                            <div class="stat-box providers">
                                <div class="stat-value"><?php echo array_sum($providerRegistrationData); ?></div>
                                <div class="stat-label">Total Providers</div>
                            </div>
                            <div class="stat-box providers">
                                <div class="stat-value"><?php echo $providerStatusDistribution['verified']; ?></div>
                                <div class="stat-label">Verified</div>
                            </div>
                            <div class="stat-box providers">
                                <div class="stat-value"><?php echo $providerStatusDistribution['pending']; ?></div>
                                <div class="stat-label">Pending</div>
                            </div>
                            <div class="stat-box providers">
                                <div class="stat-value"><?php echo $providerStatusDistribution['rejected']; ?></div>
                                <div class="stat-label">Rejected</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="two-column-layout">
                        <div class="report-section">
                            <h3 class="section-title">Provider Registration by Month (<?php echo $year; ?>)</h3>
                            <div class="chart-container">
                                <canvas id="providerRegistrationChart"></canvas>
                            </div>
                        </div>
                        
                        <div class="report-section">
                            <h3 class="section-title">Provider Status Distribution</h3>
                            <div class="chart-container">
                                <canvas id="providerStatusPieChart"></canvas>
                            </div>
                        </div>
                    </div>
                    
                    <div class="report-section">
                        <h3 class="section-title">Top Service Providers by Bookings</h3>
                        <?php if (empty($topProviders)): ?>
                            <div class="no-data">No provider data available</div>
                        <?php else: ?>
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Rank</th>
                                        <th>Provider</th>
                                        <th>Bookings</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($topProviders as $index => $provider): ?>
                                        <tr>
                                            <td class="rank">#<?php echo $index + 1; ?></td>
                                            <td><?php echo htmlspecialchars($provider['name']); ?></td>
                                            <td><?php echo $provider['bookings']; ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
                
                <!-- Bookings Report -->
                <?php if ($reportType === 'bookings'): ?>
                    <div class="report-section">
                        <h3 class="section-title">
                            Booking Analytics
                        </h3>
                        <div class="stats-grid">
                            <div class="stat-box bookings">
                                <div class="stat-value"><?php echo array_sum($bookingData); ?></div>
                                <div class="stat-label">Total Bookings</div>
                            </div>
                            <div class="stat-box bookings">
                                <div class="stat-value"><?php echo max($bookingData); ?></div>
                                <div class="stat-label">Peak Month</div>
                            </div>
                            <div class="stat-box bookings">
                                <div class="stat-value"><?php echo round(array_sum($bookingData) / 12, 1); ?></div>
                                <div class="stat-label">Avg. Monthly</div>
                            </div>
                            <div class="stat-box bookings">
                                <div class="stat-value"><?php echo $bookingData[date('n') - 1]; ?></div>
                                <div class="stat-label">This Month</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="report-section">
                        <h3 class="section-title">Bookings by Month (<?php echo $year; ?>)</h3>
                        <div class="chart-container">
                            <canvas id="bookingChart"></canvas>
                        </div>
                    </div>
                    
                    <div class="report-section">
                        <h3 class="section-title">Booking Distribution</h3>
                        <div class="chart-container">
                            <canvas id="bookingPieChart"></canvas>
                        </div>
                    </div>
                    
                    <div class="report-section">
                        <h3 class="section-title">Top Services by Bookings</h3>
                        <?php if (empty($topServices)): ?>
                            <div class="no-data">No service data available</div>
                        <?php else: ?>
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Rank</th>
                                        <th>Service</th>
                                        <th>Bookings</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($topServices as $index => $service): ?>
                                        <tr>
                                            <td class="rank">#<?php echo $index + 1; ?></td>
                                            <td><?php echo htmlspecialchars($service['name']); ?></td>
                                            <td><?php echo $service['bookings']; ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
                
                <!-- Revenue Report -->
                <?php if ($reportType === 'revenue'): ?>
                    <div class="report-section">
                        <h3 class="section-title">
                            Revenue Analytics
                        </h3>
                        <div class="stats-grid">
                            <div class="stat-box revenue">
                                <div class="stat-value">₹<?php echo number_format(array_sum($revenueData), 2); ?></div>
                                <div class="stat-label">Total Revenue</div>
                            </div>
                            <div class="stat-box revenue">
                                <div class="stat-value">₹<?php echo number_format(max($revenueData), 2); ?></div>
                                <div class="stat-label">Peak Month</div>
                            </div>
                            <div class="stat-box revenue">
                                <div class="stat-value">₹<?php echo number_format(array_sum($revenueData) / 12, 2); ?></div>
                                <div class="stat-label">Avg. Monthly</div>
                            </div>
                            <div class="stat-box revenue">
                                <div class="stat-value">₹<?php echo number_format($revenueData[date('n') - 1], 2); ?></div>
                                <div class="stat-label">This Month</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="report-section">
                        <h3 class="section-title">Revenue by Month (<?php echo $year; ?>)</h3>
                        <div class="chart-container">
                            <canvas id="revenueChart"></canvas>
                        </div>
                    </div>
                <?php endif; ?>
                
                <!-- Services Report -->
                <?php if ($reportType === 'services'): ?>
                    <div class="report-section">
                        <h3 class="section-title">
                            Service Analytics
                        </h3>
                        <div class="stats-grid">
                            <div class="stat-box">
                                <div class="stat-value"><?php echo count($serviceCategoryDistribution); ?></div>
                                <div class="stat-label">Service Categories</div>
                            </div>
                            <div class="stat-box">
                                <div class="stat-value"><?php echo array_sum(array_column($serviceCategoryDistribution, 'count')); ?></div>
                                <div class="stat-label">Total Services</div>
                            </div>
                            <div class="stat-box">
                                <div class="stat-value"><?php echo count($topServices); ?></div>
                                <div class="stat-label">Top Services</div>
                            </div>
                            <div class="stat-box">
                                <div class="stat-value"><?php echo !empty($topServices) ? $topServices[0]['bookings'] : 0; ?></div>
                                <div class="stat-label">Highest Bookings</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="two-column-layout">
                        <div class="report-section">
                            <h3 class="section-title">Service Category Distribution</h3>
                            <div class="chart-container">
                                <canvas id="serviceCategoryChart"></canvas>
                            </div>
                        </div>
                        
                        <div class="report-section">
                            <h3 class="section-title">Top Services by Bookings</h3>
                            <?php if (empty($topServices)): ?>
                                <div class="no-data">No service data available</div>
                            <?php else: ?>
                                <table class="data-table">
                                    <thead>
                                        <tr>
                                            <th>Rank</th>
                                            <th>Service</th>
                                            <th>Bookings</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($topServices as $index => $service): ?>
                                            <tr>
                                                <td class="rank">#<?php echo $index + 1; ?></td>
                                                <td><?php echo htmlspecialchars($service['name']); ?></td>
                                                <td><?php echo $service['bookings']; ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
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
        
        // Apply filters
        function applyFilters() {
            const type = document.getElementById('report-type').value;
            const year = document.getElementById('year').value;
            
            const url = new URL(window.location.href);
            url.searchParams.set('type', type);
            url.searchParams.set('year', year);
            
            window.location.href = url.toString();
        }
        
        // Reset filters
        function resetFilters() {
            const url = new URL(window.location.href);
            url.searchParams.set('type', 'overview');
            url.searchParams.set('year', new Date().getFullYear());
            
            window.location.href = url.toString();
        }
        
        // Switch tab
        function switchTab(type) {
            const url = new URL(window.location.href);
            url.searchParams.set('type', type);
            window.location.href = url.toString();
        }
        
        // Chart colors
        const chartColors = {
            primary: '#5d3b66',
            primaryLight: 'rgba(93, 59, 102, 0.1)',
            info: '#0abde3',
            infoLight: 'rgba(10, 189, 227, 0.1)',
            success: '#10ac84',
            successLight: 'rgba(16, 172, 132, 0.1)',
            warning: '#ee5a24',
            warningLight: 'rgba(238, 90, 36, 0.1)',
            accent: '#ff9f43',
            accentLight: 'rgba(255, 159, 67, 0.1)',
            secondary: '#ff6b6b',
            secondaryLight: 'rgba(255, 107, 107, 0.1)'
        };
        
        // Common chart options
        const commonChartOptions = {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top',
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.7)',
                    padding: 10,
                    titleFont: {
                        size: 14
                    },
                    bodyFont: {
                        size: 14
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
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
        };
        
        // Overview Charts
        <?php if ($reportType === 'overview'): ?>
            // Registration Trends Chart
            const registrationCtx = document.getElementById('registrationChart').getContext('2d');
            const registrationChart = new Chart(registrationCtx, {
                type: 'line',
                data: {
                    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                    datasets: [
                        {
                            label: 'Users',
                            data: <?php echo json_encode($userRegistrationData); ?>,
                            borderColor: chartColors.info,
                            backgroundColor: chartColors.infoLight,
                            tension: 0.4,
                            fill: true
                        },
                        {
                            label: 'Providers',
                            data: <?php echo json_encode($providerRegistrationData); ?>,
                            borderColor: chartColors.success,
                            backgroundColor: chartColors.successLight,
                            tension: 0.4,
                            fill: true
                        }
                    ]
                },
                options: commonChartOptions
            });
            
            // User Distribution Chart - Changed to bar chart
            const userDistributionCtx = document.getElementById('userDistributionChart').getContext('2d');
            const userDistributionChart = new Chart(userDistributionCtx, {
                type: 'bar',
                data: {
                    labels: <?php echo json_encode(array_column($userRegistrationDistribution, 'month')); ?>,
                    datasets: [{
                        label: 'User Count',
                        data: <?php echo json_encode(array_column($userRegistrationDistribution, 'count')); ?>,
                        backgroundColor: chartColors.infoLight,
                        borderColor: chartColors.info,
                        borderWidth: 1
                    }]
                },
                options: commonChartOptions
            });
            
            // Booking Distribution Chart - Changed to bar chart
            const bookingDistributionCtx = document.getElementById('bookingDistributionChart').getContext('2d');
            const bookingDistributionChart = new Chart(bookingDistributionCtx, {
                type: 'bar',
                data: {
                    labels: <?php echo json_encode(array_column($bookingDistribution, 'month')); ?>,
                    datasets: [{
                        label: 'Booking Count',
                        data: <?php echo json_encode(array_column($bookingDistribution, 'count')); ?>,
                        backgroundColor: chartColors.warningLight,
                        borderColor: chartColors.warning,
                        borderWidth: 1
                    }]
                },
                options: commonChartOptions
            });
            
            // Provider Status Distribution Chart
            const providerStatusCtx = document.getElementById('providerStatusChart').getContext('2d');
            const providerStatusChart = new Chart(providerStatusCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Verified', 'Pending', 'Rejected'],
                    datasets: [{
                        data: [
                            <?php echo $providerStatusDistribution['verified']; ?>,
                            <?php echo $providerStatusDistribution['pending']; ?>,
                            <?php echo $providerStatusDistribution['rejected']; ?>
                        ],
                        backgroundColor: [
                            chartColors.success,
                            chartColors.warning,
                            chartColors.secondary
                        ],
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                        }
                    }
                }
            });
        <?php endif; ?>
        
        // Users Charts
        <?php if ($reportType === 'users'): ?>
            // User Registration Chart
            const userRegistrationCtx = document.getElementById('userRegistrationChart').getContext('2d');
            const userRegistrationChart = new Chart(userRegistrationCtx, {
                type: 'bar',
                data: {
                    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                    datasets: [{
                        label: 'User Registrations',
                        data: <?php echo json_encode($userRegistrationData); ?>,
                        backgroundColor: chartColors.infoLight,
                        borderColor: chartColors.info,
                        borderWidth: 1
                    }]
                },
                options: commonChartOptions
            });
            
            // User Pie Chart - Changed to bar chart
            const userPieCtx = document.getElementById('userPieChart').getContext('2d');
            const userPieChart = new Chart(userPieCtx, {
                type: 'bar',
                data: {
                    labels: <?php echo json_encode(array_column($userRegistrationDistribution, 'month')); ?>,
                    datasets: [{
                        label: 'User Count',
                        data: <?php echo json_encode(array_column($userRegistrationDistribution, 'count')); ?>,
                        backgroundColor: chartColors.infoLight,
                        borderColor: chartColors.info,
                        borderWidth: 1
                    }]
                },
                options: commonChartOptions
            });
        <?php endif; ?>
        
        // Providers Charts
        <?php if ($reportType === 'providers'): ?>
            // Provider Registration Chart
            const providerRegistrationCtx = document.getElementById('providerRegistrationChart').getContext('2d');
            const providerRegistrationChart = new Chart(providerRegistrationCtx, {
                type: 'bar',
                data: {
                    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                    datasets: [{
                        label: 'Provider Registrations',
                        data: <?php echo json_encode($providerRegistrationData); ?>,
                        backgroundColor: chartColors.successLight,
                        borderColor: chartColors.success,
                        borderWidth: 1
                    }]
                },
                options: commonChartOptions
            });
            
            // Provider Status Pie Chart
            const providerStatusPieCtx = document.getElementById('providerStatusPieChart').getContext('2d');
            const providerStatusPieChart = new Chart(providerStatusPieCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Verified', 'Pending', 'Rejected'],
                    datasets: [{
                        data: [
                            <?php echo $providerStatusDistribution['verified']; ?>,
                            <?php echo $providerStatusDistribution['pending']; ?>,
                            <?php echo $providerStatusDistribution['rejected']; ?>
                        ],
                        backgroundColor: [
                            chartColors.success,
                            chartColors.warning,
                            chartColors.secondary
                        ],
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                        }
                    }
                }
            });
        <?php endif; ?>
        
        // Bookings Charts
        <?php if ($reportType === 'bookings'): ?>
            // Booking Chart
            const bookingCtx = document.getElementById('bookingChart').getContext('2d');
            const bookingChart = new Chart(bookingCtx, {
                type: 'line',
                data: {
                    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                    datasets: [{
                        label: 'Bookings',
                        data: <?php echo json_encode($bookingData); ?>,
                        borderColor: chartColors.warning,
                        backgroundColor: chartColors.warningLight,
                        tension: 0.4,
                        fill: true
                    }]
                },
                options: commonChartOptions
            });
            
            // Booking Pie Chart - Changed to bar chart
            const bookingPieCtx = document.getElementById('bookingPieChart').getContext('2d');
            const bookingPieChart = new Chart(bookingPieCtx, {
                type: 'bar',
                data: {
                    labels: <?php echo json_encode(array_column($bookingDistribution, 'month')); ?>,
                    datasets: [{
                        label: 'Booking Count',
                        data: <?php echo json_encode(array_column($bookingDistribution, 'count')); ?>,
                        backgroundColor: chartColors.warningLight,
                        borderColor: chartColors.warning,
                        borderWidth: 1
                    }]
                },
                options: commonChartOptions
            });
        <?php endif; ?>
        
        // Revenue Charts
        <?php if ($reportType === 'revenue'): ?>
            // Revenue Chart
            const revenueCtx = document.getElementById('revenueChart').getContext('2d');
            const revenueChart = new Chart(revenueCtx, {
                type: 'line',
                data: {
                    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                    datasets: [{
                        label: 'Revenue (₹)',
                        data: <?php echo json_encode($revenueData); ?>,
                        borderColor: chartColors.primary,
                        backgroundColor: chartColors.primaryLight,
                        tension: 0.4,
                        fill: true
                    }]
                },
                options: {
                    ...commonChartOptions,
                    plugins: {
                        ...commonChartOptions.plugins,
                        tooltip: {
                            ...commonChartOptions.plugins.tooltip,
                            callbacks: {
                                label: function(context) {
                                    return '₹' + context.raw.toLocaleString();
                                }
                            }
                        }
                    },
                    scales: {
                        ...commonChartOptions.scales,
                        y: {
                            ...commonChartOptions.scales.y,
                            ticks: {
                                callback: function(value) {
                                    if (value >= 1000) {
                                        return '₹' + (value/1000) + 'k';
                                    }
                                    return '₹' + value;
                                }
                            }
                        }
                    }
                }
            });
        <?php endif; ?>
        
        // Services Charts
        <?php if ($reportType === 'services'): ?>
            // Service Category Distribution Chart
            const serviceCategoryCtx = document.getElementById('serviceCategoryChart').getContext('2d');
            const serviceCategoryChart = new Chart(serviceCategoryCtx, {
                type: 'doughnut',
                data: {
                    labels: <?php echo json_encode(array_column($serviceCategoryDistribution, 'category')); ?>,
                    datasets: [{
                        data: <?php echo json_encode(array_column($serviceCategoryDistribution, 'count')); ?>,
                        backgroundColor: [
                            chartColors.primary,
                            chartColors.info,
                            chartColors.success,
                            chartColors.warning,
                            chartColors.accent,
                            chartColors.secondary
                        ],
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                        }
                    }
                }
            });
        <?php endif; ?>
    </script>
</body>
</html>