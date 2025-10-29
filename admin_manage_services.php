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

// Check if bookings table exists
if (!tableExists($conn, 'bookings')) {
    die("<div style='padding: 30px; font-family: Arial, sans-serif; max-width: 800px; margin: 50px auto; background-color: #fff; border-radius: 10px; box-shadow: 0 5px 15px rgba(0,0,0,0.1);'>
        <h2 style='color: #d9534f;'>Table Not Found</h2>
        <p><strong>Error:</strong> The 'bookings' table does not exist in the database.</p>
        <div style='margin-top: 20px;'>
            <a href='admin_dashboard.php' style='display: inline-block; padding: 10px 20px; background-color: #5d3b66; color: white; text-decoration: none; border-radius: 5px;'>Back to Dashboard</a>
        </div>
    </div>");
}

// Handle delete action
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $booking_id = (int)$_GET['id'];
    
    $delete_query = "DELETE FROM bookings WHERE id = $booking_id";
    if ($conn->query($delete_query) === TRUE) {
        $_SESSION['message'] = "Booking deleted successfully!";
        $_SESSION['msg_type'] = "success";
    } else {
        $_SESSION['message'] = "Error deleting booking: " . $conn->error;
        $_SESSION['msg_type'] = "error";
    }
    header("Location: admin_manage_services.php");
    exit();
}

// Handle search and pagination
$search = '';
if (isset($_GET['search'])) {
    $search = $conn->real_escape_string($_GET['search']);
}

// Pagination variables
$limit = 10; // Number of bookings per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Build query based on search
$where_conditions = [];
if (!empty($search)) {
    $where_conditions[] = "(username LIKE '%$search%' OR service_details LIKE '%$search%' OR detected_location LIKE '%$search%' OR phone LIKE '%$search%' OR email LIKE '%$search%' OR flat_no LIKE '%$search%' OR street LIKE '%$search%')";
}

$where_clause = '';
if (!empty($where_conditions)) {
    $where_clause = "WHERE " . implode(" OR ", $where_conditions);
}

$count_query = "SELECT COUNT(*) as total FROM bookings $where_clause";
$bookings_query = "SELECT * FROM bookings $where_clause ORDER BY created_at DESC LIMIT $limit OFFSET $offset";

// Get total bookings count
$count_result = $conn->query($count_query);
$total_bookings = $count_result->fetch_assoc()['total'];
$total_pages = ceil($total_bookings / $limit);

// Get bookings data
$bookings_result = $conn->query($bookings_query);

// Get total revenue
$revenue_query = "SELECT SUM(total_amount) as total_revenue FROM bookings";
$revenue_result = $conn->query($revenue_query);
$total_revenue = 0;
if ($revenue_result && $revenue_result->num_rows > 0) {
    $row = $revenue_result->fetch_assoc();
    $total_revenue = $row['total_revenue'] ? $row['total_revenue'] : 0;
}

// Get today's bookings count
$today = date('Y-m-d');
$today_query = "SELECT COUNT(*) as today_count FROM bookings WHERE DATE(created_at) = '$today'";
$today_result = $conn->query($today_query);
$today_count = 0;
if ($today_result && $today_result->num_rows > 0) {
    $row = $today_result->fetch_assoc();
    $today_count = $row['today_count'];
}

// Close database connection
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Service Bookings - SpitiCare Admin</title>
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
        .stat-card.bookings .stat-icon {
            background-color: rgba(93, 59, 102, 0.1);
            color: var(--primary);
        }
        .stat-card.revenue .stat-icon {
            background-color: rgba(16, 172, 132, 0.1);
            color: var(--success);
        }
        .stat-card.today .stat-icon {
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
        /* Search Bar */
        .search-bar {
            display: flex;
            margin-bottom: 20px;
        }
        .search-bar input {
            flex: 1;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 5px 0 0 5px;
            font-size: 1rem;
        }
        .search-bar button {
            background-color: var(--primary);
            color: white;
            border: none;
            padding: 0 20px;
            border-radius: 0 5px 5px 0;
            cursor: pointer;
            font-weight: 500;
            transition: var(--transition);
        }
        .search-bar button:hover {
            background-color: var(--primary-light);
        }
        /* Bookings Table */
        .bookings-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .bookings-table th, .bookings-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        .bookings-table th {
            background-color: var(--light);
            font-weight: 600;
            color: var(--text);
        }
        .bookings-table tr:hover {
            background-color: #f9f9f9;
        }
        .bookings-table .actions {
            display: flex;
            gap: 10px;
        }
        .service-details {
            max-width: 300px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .address-info {
            font-size: 0.85rem;
            color: var(--text-light);
        }
        .btn {
            padding: 6px 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.9rem;
            transition: var(--transition);
            text-decoration: none;
            display: inline-block;
        }
        .btn-view {
            background-color: var(--info);
            color: white;
        }
        .btn-view:hover {
            background-color: #0a8fc7;
        }
        .btn-delete {
            background-color: var(--secondary);
            color: white;
        }
        .btn-delete:hover {
            background-color: #e55039;
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
        /* Notification */
        .notification {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
            display: flex;
            align-items: center;
        }
        .notification.success {
            background-color: rgba(16, 172, 132, 0.1);
            color: var(--success);
            border-left: 4px solid var(--success);
        }
        .notification.error {
            background-color: rgba(255, 107, 107, 0.1);
            color: var(--secondary);
            border-left: 4px solid var(--secondary);
        }
        .notification i {
            margin-right: 10px;
            font-size: 1.2rem;
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
        /* Modal */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.6);
            animation: fadeIn 0.3s ease;
        }
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        .modal-content {
            background-color: white;
            margin: 5% auto;
            padding: 30px;
            border-radius: 15px;
            width: 90%;
            max-width: 700px;
            position: relative;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
            animation: slideIn 0.3s ease;
        }
        @keyframes slideIn {
            from { transform: translateY(-50px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        .close-btn {
            position: absolute;
            right: 20px;
            top: 15px;
            font-size: 24px;
            font-weight: bold;
            color: var(--dark-gray);
            cursor: pointer;
            transition: all 0.3s ease;
            width: 35px;
            height: 35px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
        }
        .close-btn:hover {
            color: white;
            background-color: var(--accent-color);
            transform: rotate(90deg);
        }
        .modal-content h2 {
            margin-bottom: 20px;
            color: var(--primary);
            font-size: 24px;
            font-weight: 600;
        }
        .modal-content h3 {
            margin: 15px 0 10px;
            color: var(--primary);
            font-size: 18px;
            font-weight: 600;
        }
        .modal-content p {
            margin-bottom: 10px;
            color: var(--text);
        }
        .modal-content .detail-row {
            display: flex;
            margin-bottom: 8px;
        }
        .modal-content .detail-label {
            font-weight: 600;
            min-width: 150px;
            color: var(--text-light);
        }
        .modal-content .detail-value {
            flex: 1;
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
            .bookings-table {
                font-size: 0.9rem;
            }
            .bookings-table th, .bookings-table td {
                padding: 8px 10px;
            }
            .actions {
                flex-direction: column;
                gap: 5px;
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
            .stats-container {
                grid-template-columns: repeat(2, 1fr);
            }
            .section-header {
                flex-direction: column;
                align-items: flex-start;
            }
            .search-bar {
                flex-direction: column;
            }
            .search-bar input {
                border-radius: 5px;
                margin-bottom: 10px;
            }
            .search-bar button {
                border-radius: 5px;
            }
            .bookings-table {
                display: block;
                overflow-x: auto;
                white-space: nowrap;
            }
            .modal-content {
                padding: 20px;
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
                        <li><a href="admin_manage_services.php" class="active"><i class="fas fa-calendar-check"></i> Service Bookings</a></li>
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
                    <h1>Manage Service Bookings</h1>
                </div>
                <div class="admin-info">
                    <img src="https://picsum.photos/seed/admin/40/40.jpg" alt="Admin">
                    <span class="admin-name"><?php echo $_SESSION['admin_username']; ?></span>
                    <a href="admin_logout.php" class="logout-btn">Logout</a>
                </div>
            </div>
            
            <div class="content-container">
                <?php if (isset($_SESSION['message'])): ?>
                <div class="notification <?php echo $_SESSION['msg_type']; ?>">
                    <i class="fas <?php echo $_SESSION['msg_type'] == 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'; ?>"></i>
                    <?php echo $_SESSION['message']; ?>
                </div>
                <?php unset($_SESSION['message']); unset($_SESSION['msg_type']); endif; ?>
                
                <div class="section-header">
                    <h2>Service Bookings Overview</h2>
                </div>
                
                <!-- Stats Cards -->
                <div class="stats-container">
                    <div class="stat-card bookings">
                        <div class="stat-icon">
                            <i class="fas fa-calendar-check"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo $total_bookings; ?></h3>
                            <p>Total Bookings</p>
                        </div>
                    </div>
                    
                    <div class="stat-card revenue">
                        <div class="stat-icon">
                            <i class="fas fa-dollar-sign"></i>
                        </div>
                        <div class="stat-info">
                            <h3>₹<?php echo number_format($total_revenue, 2); ?></h3>
                            <p>Total Revenue</p>
                        </div>
                    </div>
                    
                    <div class="stat-card today">
                        <div class="stat-icon">
                            <i class="fas fa-calendar-day"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo $today_count; ?></h3>
                            <p>Today's Bookings</p>
                        </div>
                    </div>
                </div>
                
                <div class="section-header">
                    <h2>Service Bookings</h2>
                </div>
                
                <!-- Search Bar -->
                <form action="admin_manage_services.php" method="GET" class="search-bar">
                    <input type="text" name="search" placeholder="Search by username, service, location, phone..." value="<?php echo htmlspecialchars($search); ?>">
                    <button type="submit"><i class="fas fa-search"></i> Search</button>
                </form>
                
                <!-- Bookings Table -->
                <?php if ($bookings_result->num_rows > 0): ?>
                <table class="bookings-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Username</th>
                            <th>Service</th>
                            <th>Location</th>
                            <th>Contact</th>
                            <th>Booking Date</th>
                            <th>Amount</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($booking = $bookings_result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $booking['id']; ?></td>
                            <td><?php echo htmlspecialchars($booking['username']); ?></td>
                            <td>
                                <div class="service-details" title="<?php echo htmlspecialchars($booking['service_details']); ?>">
                                    <?php echo htmlspecialchars(substr($booking['service_details'], 0, 50)) . (strlen($booking['service_details']) > 50 ? '...' : ''); ?>
                                </div>
                            </td>
                            <td>
                                <div class="address-info">
                                    <?php echo htmlspecialchars($booking['detected_location']); ?><br>
                                    <?php if (!empty($booking['flat_no'])) echo htmlspecialchars($booking['flat_no']) . ', '; ?>
                                    <?php if (!empty($booking['street'])) echo htmlspecialchars($booking['street']) . ', '; ?>
                                    <?php if (!empty($booking['landmark'])) echo htmlspecialchars($booking['landmark']) . ', '; ?>
                                    <?php echo htmlspecialchars($booking['pincode']); ?>
                                </div>
                            </td>
                            <td>
                                <?php echo htmlspecialchars($booking['phone']); ?><br>
                                <small><?php echo htmlspecialchars($booking['email']); ?></small>
                            </td>
                            <td>
                                <?php echo date('M d, Y', strtotime($booking['created_at'])); ?><br>
                                <small><?php echo htmlspecialchars($booking['booking_time']); ?></small>
                            </td>
                            <td>₹<?php echo number_format($booking['total_amount'], 2); ?></td>
                            <td class="actions">
                                <button class="btn btn-view" onclick="viewBookingDetails(<?php echo $booking['id']; ?>)">
                                    <i class="fas fa-eye"></i> View
                                </button>
                                <a href="admin_manage_services.php?action=delete&id=<?php echo $booking['id']; ?>" class="btn btn-delete" onclick="return confirm('Are you sure you want to delete this booking?');">
                                    <i class="fas fa-trash"></i> Delete
                                </a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                
                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                <ul class="pagination">
                    <?php if ($page > 1): ?>
                    <li><a href="?page=<?php echo $page - 1; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>">Previous</a></li>
                    <?php endif; ?>
                    
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <?php if ($i == $page): ?>
                    <li class="active"><a href="#"><?php echo $i; ?></a></li>
                    <?php else: ?>
                    <li><a href="?page=<?php echo $i; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>"><?php echo $i; ?></a></li>
                    <?php endif; ?>
                    <?php endfor; ?>
                    
                    <?php if ($page < $total_pages): ?>
                    <li><a href="?page=<?php echo $page + 1; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>">Next</a></li>
                    <?php endif; ?>
                </ul>
                <?php endif; ?>
                
                <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-calendar-check"></i>
                    <h3>No bookings found</h3>
                    <p>Try adjusting your search criteria.</p>
                </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
    
    <!-- Booking Details Modal -->
    <div id="bookingModal" class="modal">
        <div class="modal-content">
            <span class="close-btn" onclick="closeBookingModal()">&times;</span>
            <h2>Booking Details</h2>
            <div id="bookingDetailsContent"></div>
        </div>
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
        
        // View booking details
        function viewBookingDetails(bookingId) {
            // This would typically fetch data via AJAX, but for simplicity we'll use the data already available
            // In a real application, you would make an AJAX request to get full booking details
            const modal = document.getElementById('bookingModal');
            const content = document.getElementById('bookingDetailsContent');
            
            // Find the booking row data
            const rows = document.querySelectorAll('.bookings-table tbody tr');
            let bookingData = null;
            
            rows.forEach(row => {
                const idCell = row.cells[0];
                if (idCell && parseInt(idCell.textContent) === bookingId) {
                    bookingData = {
                        id: bookingId,
                        username: row.cells[1].textContent,
                        service: row.cells[2].textContent,
                        location: row.cells[3].textContent,
                        contact: row.cells[4].textContent,
                        date: row.cells[5].textContent,
                        amount: row.cells[6].textContent
                    };
                }
            });
            
            if (bookingData) {
                content.innerHTML = `
                    <div class="detail-row">
                        <div class="detail-label">Booking ID:</div>
                        <div class="detail-value">${bookingData.id}</div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Username:</div>
                        <div class="detail-value">${bookingData.username}</div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Service Details:</div>
                        <div class="detail-value">${bookingData.service}</div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Location:</div>
                        <div class="detail-value">${bookingData.location}</div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Contact:</div>
                        <div class="detail-value">${bookingData.contact}</div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Booking Date:</div>
                        <div class="detail-value">${bookingData.date}</div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Amount:</div>
                        <div class="detail-value">${bookingData.amount}</div>
                    </div>
                `;
                modal.style.display = 'block';
            }
        }
        
        function closeBookingModal() {
            document.getElementById('bookingModal').style.display = 'none';
        }
        
        // Close modal when clicking outside
        window.addEventListener('click', function(event) {
            const modal = document.getElementById('bookingModal');
            if (event.target === modal) {
                closeBookingModal();
            }
        });
    </script>
</body>
</html>