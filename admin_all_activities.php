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
// Function to get all activities
function getAllActivities($conn, $limit = 20, $offset = 0) {
    $activities = [];
    
    // Check if required tables exist
    $bookingsTable = tableExists($conn, 'bookings');
    $usersTable = tableExists($conn, 'users');
    $serviceProvidersTable = tableExists($conn, 'service_providers');
    $paymentsTable = tableExists($conn, 'payments');
    $reviewsTable = tableExists($conn, 'reviews');
    $supportTicketsTable = tableExists($conn, 'support_tickets');
    
    // Get recent bookings
    if ($bookingsTable && columnExists($conn, 'bookings', 'created_at') && columnExists($conn, 'bookings', 'username')) {
        $sql = "SELECT 'booking' as type, 'fas fa-calendar-check' as icon, 'New Booking' as title, 
                CONCAT(username, ' made a booking for ', service_details) as description, 
                created_at as timestamp
                FROM bookings ORDER BY created_at DESC LIMIT $limit OFFSET $offset";
        $result = $conn->query($sql);
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                // Add formatted time field
                $row['time'] = date('d M Y, H:i', strtotime($row['timestamp']));
                $activities[] = $row;
            }
        }
    }
    
    // Get recent user registrations - FIXED: Using 'username' instead of 'name'
    if ($usersTable && columnExists($conn, 'users', 'created_at') && columnExists($conn, 'users', 'username')) {
        $sql = "SELECT 'user' as type, 'fas fa-user-plus' as icon, 'New User Registration' as title, 
                CONCAT(username, ' registered a new account') as description, 
                created_at as timestamp
                FROM users ORDER BY created_at DESC LIMIT $limit OFFSET $offset";
        $result = $conn->query($sql);
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                // Add formatted time field
                $row['time'] = date('d M Y, H:i', strtotime($row['timestamp']));
                $activities[] = $row;
            }
        }
    }
    
    // Get recent provider registrations
    if ($serviceProvidersTable && columnExists($conn, 'service_providers', 'created_at') && columnExists($conn, 'service_providers', 'fullName')) {
        $sql = "SELECT 'provider' as type, 'fas fa-user-md' as icon, 'New Provider Registration' as title, 
                CONCAT(fullName, ' registered as a service provider') as description, 
                created_at as timestamp
                FROM service_providers ORDER BY created_at DESC LIMIT $limit OFFSET $offset";
        $result = $conn->query($sql);
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                // Add formatted time field
                $row['time'] = date('d M Y, H:i', strtotime($row['timestamp']));
                $activities[] = $row;
            }
        }
    }
    
    // Get recent payments
    if ($paymentsTable && columnExists($conn, 'payments', 'created_at') && columnExists($conn, 'payments', 'amount')) {
        $sql = "SELECT 'payment' as type, 'fas fa-money-bill-wave' as icon, 'Payment Received' as title, 
                CONCAT('A payment of ₹', FORMAT(amount, 2), ' was received') as description, 
                created_at as timestamp
                FROM payments WHERE status = 'completed' ORDER BY created_at DESC LIMIT $limit OFFSET $offset";
        $result = $conn->query($sql);
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                // Add formatted time field
                $row['time'] = date('d M Y, H:i', strtotime($row['timestamp']));
                $activities[] = $row;
            }
        }
    }
    
    // Get recent reviews
    if ($reviewsTable && columnExists($conn, 'reviews', 'created_at') && columnExists($conn, 'reviews', 'rating')) {
        $sql = "SELECT 'review' as type, 'fas fa-star' as icon, 'New Review' as title, 
                CONCAT('A user left a ', rating, '-star review') as description, 
                created_at as timestamp
                FROM reviews ORDER BY created_at DESC LIMIT $limit OFFSET $offset";
        $result = $conn->query($sql);
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                // Add formatted time field
                $row['time'] = date('d M Y, H:i', strtotime($row['timestamp']));
                $activities[] = $row;
            }
        }
    }
    
    // Get recent support tickets
    if ($supportTicketsTable && columnExists($conn, 'support_tickets', 'created_at') && columnExists($conn, 'support_tickets', 'subject')) {
        $sql = "SELECT 'support' as type, 'fas fa-headset' as icon, 'Support Ticket' as title, 
                CONCAT('A support ticket was raised regarding ', subject) as description, 
                created_at as timestamp
                FROM support_tickets ORDER BY created_at DESC LIMIT $limit OFFSET $offset";
        $result = $conn->query($sql);
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                // Add formatted time field
                $row['time'] = date('d M Y, H:i', strtotime($row['timestamp']));
                $activities[] = $row;
            }
        }
    }
    
    // Sort activities by timestamp (most recent first)
    usort($activities, function($a, $b) {
        return strtotime($b['timestamp']) - strtotime($a['timestamp']);
    });
    
    // Get total count for pagination
    $totalCount = count($activities);
    
    // Apply pagination after sorting
    return [
        'activities' => array_slice($activities, $offset, $limit),
        'total_count' => $totalCount
    ];
}
// Helper function to format time elapsed (keeping for potential future use)
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
// Pagination variables
$limit = 20; // Number of activities per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;
// Get activities data
$activitiesData = getAllActivities($conn, 100, 0); // Get more for accurate pagination
$activities = array_slice($activitiesData['activities'], $offset, $limit);
$totalCount = $activitiesData['total_count'];
$totalPages = ceil($totalCount / $limit);
// Filter by activity type if specified
$filterType = isset($_GET['type']) ? $_GET['type'] : '';
if (!empty($filterType)) {
    $filteredActivities = [];
    foreach ($activities as $activity) {
        if ($activity['type'] === $filterType) {
            $filteredActivities[] = $activity;
        }
    }
    $activities = $filteredActivities;
}
// Close database connection
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Activities - SpitiCare Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
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
        /* Content Container */
        .content-container {
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
        /* Filter Tabs */
        .filter-tabs {
            display: flex;
            margin-bottom: 20px;
            border-bottom: 1px solid #eee;
        }
        .filter-tab {
            padding: 10px 20px;
            cursor: pointer;
            font-weight: 500;
            color: var(--text-light);
            border-bottom: 2px solid transparent;
            transition: var(--transition);
        }
        .filter-tab:hover {
            color: var(--primary);
        }
        .filter-tab.active {
            color: var(--primary);
            border-bottom-color: var(--primary);
        }
        /* Activity List */
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
        /* Pagination */
        .pagination {
            display: flex;
            justify-content: center;
            list-style: none;
            margin-top: 20px;
        }
        .pagination li {
            margin: 0 5px;
        }
        .pagination a {
            display: block;
            padding: 8px 12px;
            background-color: white;
            border: 1px solid #ddd;
            color: var(--text);
            text-decoration: none;
            border-radius: 4px;
            transition: var(--transition);
        }
        .pagination a:hover {
            background-color: var(--primary);
            color: white;
            border-color: var(--primary);
        }
        .pagination .active a {
            background-color: var(--primary);
            color: white;
            border-color: var(--primary);
        }
        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: var(--text-light);
        }
        .empty-state i {
            font-size: 3rem;
            margin-bottom: 15px;
            color: #ddd;
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
            .section-header {
                flex-direction: column;
                align-items: flex-start;
            }
            .filter-tabs {
                overflow-x: auto;
                width: 100%;
                padding-bottom: 10px;
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
            .activity-item {
                flex-direction: column;
                align-items: flex-start;
            }
            .activity-icon {
                margin-bottom: 10px;
            }
            .activity-time {
                margin-top: 5px;
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
                    <h1>All Activities</h1>
                </div>
                <div class="admin-info">
                    <img src="https://picsum.photos/seed/admin/40/40.jpg" alt="Admin">
                    <span class="admin-name"><?php echo htmlspecialchars($_SESSION['admin_username']); ?></span>
                    <a href="admin_logout.php" class="logout-btn">Logout</a>
                </div>
            </div>
            
            <div class="content-container">
                <div class="section-header">
                    <h2>Recent Activities</h2>
                    <a href="admin_dashboard.php" class="view-all-btn">Back to Dashboard</a>
                </div>
                
                <!-- Filter Tabs -->
                <div class="filter-tabs">
                    <div class="filter-tab <?php echo empty($filterType) ? 'active' : ''; ?>" onclick="window.location.href='admin_all_activities.php'">
                        All Activities
                    </div>
                    <div class="filter-tab <?php echo $filterType === 'booking' ? 'active' : ''; ?>" onclick="window.location.href='admin_all_activities.php?type=booking'">
                        Bookings
                    </div>
                    <div class="filter-tab <?php echo $filterType === 'user' ? 'active' : ''; ?>" onclick="window.location.href='admin_all_activities.php?type=user'">
                        User Registrations
                    </div>
                    <div class="filter-tab <?php echo $filterType === 'provider' ? 'active' : ''; ?>" onclick="window.location.href='admin_all_activities.php?type=provider'">
                        Provider Registrations
                    </div>
                    <div class="filter-tab <?php echo $filterType === 'payment' ? 'active' : ''; ?>" onclick="window.location.href='admin_all_activities.php?type=payment'">
                        Payments
                    </div>
                    <div class="filter-tab <?php echo $filterType === 'review' ? 'active' : ''; ?>" onclick="window.location.href='admin_all_activities.php?type=review'">
                        Reviews
                    </div>
                    <div class="filter-tab <?php echo $filterType === 'support' ? 'active' : ''; ?>" onclick="window.location.href='admin_all_activities.php?type=support'">
                        Support Tickets
                    </div>
                </div>
                
                <!-- Activity List -->
                <?php if (empty($activities)): ?>
                <div class="empty-state">
                    <i class="fas fa-clipboard-list"></i>
                    <h3>No activities found</h3>
                    <p>There are no activities to display for the selected filter.</p>
                </div>
                <?php else: ?>
                <ul class="activity-list">
                    <?php foreach ($activities as $activity): ?>
                    <li class="activity-item <?php echo htmlspecialchars($activity['type']); ?>">
                        <div class="activity-icon">
                            <i class="<?php echo htmlspecialchars($activity['icon']); ?>"></i>
                        </div>
                        <div class="activity-details">
                            <h4><?php echo htmlspecialchars($activity['title']); ?></h4>
                            <p><?php echo htmlspecialchars($activity['description']); ?></p>
                        </div>
                        <div class="activity-time"><?php echo htmlspecialchars($activity['time']); ?></div>
                    </li>
                    <?php endforeach; ?>
                </ul>
                
                <!-- Pagination -->
                <?php if ($totalPages > 1): ?>
                <ul class="pagination">
                    <?php if ($page > 1): ?>
                    <li><a href="?page=<?php echo $page - 1; ?><?php echo !empty($filterType) ? '&type=' . urlencode($filterType) : ''; ?>">Previous</a></li>
                    <?php endif; ?>
                    
                    <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                    <?php if ($i == $page): ?>
                    <li class="active"><a href="#"><?php echo $i; ?></a></li>
                    <?php else: ?>
                    <li><a href="?page=<?php echo $i; ?><?php echo !empty($filterType) ? '&type=' . urlencode($filterType) : ''; ?>"><?php echo $i; ?></a></li>
                    <?php endif; ?>
                    <?php endfor; ?>
                    
                    <?php if ($page < $totalPages): ?>
                    <li><a href="?page=<?php echo $page + 1; ?><?php echo !empty($filterType) ? '&type=' . urlencode($filterType) : ''; ?>">Next</a></li>
                    <?php endif; ?>
                </ul>
                <?php endif; ?>
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
    </script>
</body>
</html>