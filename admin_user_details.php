<?php
session_start();
// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: admin_login.php");
    exit();
}
// Get user ID from URL
if (!isset($_GET['user_id']) || empty($_GET['user_id'])) {
    header("Location: admin_manage_users.php");
    exit();
}
$user_id = (int)$_GET['user_id'];
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

// Function to check if column exists
function columnExists($conn, $table, $column) {
    $result = $conn->query("SHOW COLUMNS FROM `$table` LIKE '$column'");
    return $result->num_rows > 0;
}

// Fetch user details
$user_query = "SELECT * FROM users WHERE id = $user_id";
$user_result = $conn->query($user_query);
if ($user_result->num_rows === 0) {
    die("<div style='padding: 30px; font-family: Arial, sans-serif; max-width: 800px; margin: 50px auto; background-color: #fff; border-radius: 10px; box-shadow: 0 5px 15px rgba(0,0,0,0.1);'>
        <h2 style='color: #d9534f;'>User Not Found</h2>
        <p><strong>Error:</strong> The requested user does not exist.</p>
        <div style='margin-top: 20px;'>
            <a href='admin_manage_users.php' style='display: inline-block; padding: 10px 20px; background-color: #5d3b66; color: white; text-decoration: none; border-radius: 5px;'>Back to Users</a>
        </div>
    </div>");
}
$user = $user_result->fetch_assoc();

// Initialize variables
$stats = [
    'total_bookings' => 0,
    'total_amount' => 0,
    'last_booking_date' => null,
    'avg_booking_value' => 0,
    'regular_customer' => false,
    'location' => 'Not provided'
];

// Get user location from users table if available
if (columnExists($conn, 'users', 'location')) {
    $stats['location'] = !empty($user['location']) ? $user['location'] : 'Not provided';
}

// Get user stats from bookings table
if (columnExists($conn, 'bookings', 'total_amount')) {
    $stats_query = "SELECT 
                      COUNT(*) as total_bookings, 
                      COALESCE(SUM(total_amount), 0) as total_amount,
                      MAX(created_at) as last_booking_date
                    FROM bookings 
                    WHERE username = '{$user['username']}'";
} else {
    $stats_query = "SELECT 
                      COUNT(*) as total_bookings, 
                      0 as total_amount,
                      MAX(created_at) as last_booking_date
                    FROM bookings 
                    WHERE username = '{$user['username']}'";
}

$stats_result = $conn->query($stats_query);
if ($stats_result) {
    $stats_data = $stats_result->fetch_assoc();
    $stats['total_bookings'] = $stats_data['total_bookings'];
    $stats['total_amount'] = $stats_data['total_amount'];
    $stats['last_booking_date'] = $stats_data['last_booking_date'];
    $stats['avg_booking_value'] = $stats['total_bookings'] > 0 ? $stats['total_amount'] / $stats['total_bookings'] : 0;
}

// Check if regular customer (more than 3 bookings in last 6 months)
$regular_query = "SELECT COUNT(*) as recent_bookings
                  FROM bookings
                  WHERE username = '{$user['username']}' 
                  AND created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)";
$regular_result = $conn->query($regular_query);
if ($regular_result) {
    $regular_data = $regular_result->fetch_assoc();
    $stats['regular_customer'] = $regular_data['recent_bookings'] >= 3;
}

// Fetch user bookings
$bookings_query = "SELECT * FROM bookings WHERE username = '{$user['username']}' ORDER BY created_at DESC";
$bookings_result = $conn->query($bookings_query);

// Store column existence info for later use
$has_total_amount_column = columnExists($conn, 'bookings', 'total_amount');
$has_service_details_column = columnExists($conn, 'bookings', 'service_details');

// Close database connection
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Details - SpitiCare Admin</title>
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
        .back-btn {
            background-color: var(--primary);
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 500;
            transition: var(--transition);
            text-decoration: none;
            display: inline-block;
        }
        .back-btn:hover {
            background-color: var(--primary-light);
        }
        /* User Details Sections */
        .user-details-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .user-details-section {
            background-color: var(--light);
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }
        .user-details-section h3 {
            color: var(--primary);
            margin-bottom: 15px;
            font-size: 1.2rem;
            display: flex;
            align-items: center;
            border-bottom: 1px solid rgba(93, 59, 102, 0.1);
            padding-bottom: 10px;
        }
        .user-details-section h3 i {
            margin-right: 10px;
        }
        .detail-group {
            margin-bottom: 15px;
        }
        .detail-label {
            font-weight: 600;
            color: var(--text-light);
            margin-bottom: 5px;
            font-size: 0.9rem;
        }
        .detail-value {
            font-size: 1rem;
            color: var(--text);
        }
        .highlight-value {
            font-weight: 600;
            color: var(--primary);
            font-size: 1.1rem;
        }
        .status-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 0.8rem;
            font-weight: 500;
        }
        .status-regular {
            background-color: rgba(16, 172, 132, 0.1);
            color: var(--success);
        }
        .status-occasional {
            background-color: rgba(255, 159, 67, 0.1);
            color: var(--accent);
        }
        /* Bookings Section */
        .bookings-section {
            margin-top: 30px;
        }
        .bookings-section h3 {
            color: var(--primary);
            margin-bottom: 15px;
            font-size: 1.3rem;
            display: flex;
            align-items: center;
        }
        .bookings-section h3 i {
            margin-right: 10px;
        }
        .bookings-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        .bookings-table th, .bookings-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        .bookings-table th {
            background-color: var(--light);
            font-weight: 600;
        }
        .bookings-table tr:hover {
            background-color: #f9f9f9;
        }
        .empty-bookings {
            text-align: center;
            padding: 30px;
            color: var(--text-light);
        }
        .empty-bookings i {
            font-size: 2.5rem;
            margin-bottom: 15px;
            color: #ddd;
        }
        /* Recommendations Section */
        .recommendations {
            background-color: rgba(93, 59, 102, 0.05);
            border-left: 4px solid var(--primary);
            padding: 15px;
            border-radius: 0 8px 8px 0;
            margin-top: 20px;
        }
        .recommendations h4 {
            color: var(--primary);
            margin-bottom: 10px;
            display: flex;
            align-items: center;
        }
        .recommendations h4 i {
            margin-right: 10px;
        }
        .recommendations ul {
            padding-left: 20px;
        }
        .recommendations li {
            margin-bottom: 8px;
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
            .user-details-container {
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
            .bookings-table {
                display: block;
                overflow-x: auto;
                white-space: nowrap;
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
                        <li><a href="admin_manage_users.php" class="active"><i class="fas fa-user"></i> Manage Users</a></li>
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
                        <li><a href="admin_manage_services.php"><i class="fas fa-concierge-bell"></i> Manage Services</a></li>
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
                    <h1>User Details</h1>
                </div>
                <div class="admin-info">
                    <img src="https://picsum.photos/seed/admin/40/40.jpg" alt="Admin">
                    <span class="admin-name"><?php echo $_SESSION['admin_username']; ?></span>
                    <a href="admin_logout.php" class="logout-btn">Logout</a>
                </div>
            </div>
            
            <div class="content-container">
                <div class="section-header">
                    <h2>User Profile</h2>
                    <a href="admin_manage_users.php" class="back-btn">
                        <i class="fas fa-arrow-left"></i> Back to Users
                    </a>
                </div>
                
                <div class="user-details-container">
                    <!-- User Information Section -->
                    <div class="user-details-section">
                        <h3><i class="fas fa-user"></i> Basic Information</h3>
                        <div class="detail-group">
                            <div class="detail-label">User ID</div>
                            <div class="detail-value"><?php echo $user['id']; ?></div>
                        </div>
                        <div class="detail-group">
                            <div class="detail-label">Username</div>
                            <div class="detail-value"><?php echo htmlspecialchars($user['username']); ?></div>
                        </div>
                        <div class="detail-group">
                            <div class="detail-label">Email</div>
                            <div class="detail-value"><?php echo htmlspecialchars($user['email']); ?></div>
                        </div>
                        <div class="detail-group">
                            <div class="detail-label">Registration Date</div>
                            <div class="detail-value"><?php echo date('M d, Y h:i A', strtotime($user['created_at'])); ?></div>
                        </div>
                        <div class="detail-group">
                            <div class="detail-label">Location</div>
                            <div class="detail-value"><?php echo $stats['location']; ?></div>
                        </div>
                    </div>
                    
                    <!-- Booking Statistics Section -->
                    <div class="user-details-section">
                        <h3><i class="fas fa-chart-bar"></i> Booking Statistics</h3>
                        <div class="detail-group">
                            <div class="detail-label">Total Bookings</div>
                            <div class="detail-value highlight-value"><?php echo $stats['total_bookings']; ?></div>
                        </div>
                        <div class="detail-group">
                            <div class="detail-label">Last Booking Date</div>
                            <div class="detail-value"><?php echo $stats['last_booking_date'] ? date('M d, Y', strtotime($stats['last_booking_date'])) : 'Never'; ?></div>
                        </div>
                        <div class="detail-group">
                            <div class="detail-label">Total Paid Amount</div>
                            <div class="detail-value highlight-value">₹<?php echo number_format($stats['total_amount'], 2); ?></div>
                        </div>
                    </div>
                    
                    <!-- Customer Analysis Section -->
                    <div class="user-details-section">
                        <h3><i class="fas fa-user-check"></i> Customer Analysis</h3>
                        <div class="detail-group">
                            <div class="detail-label">Customer Type</div>
                            <div class="detail-value">
                                <?php if ($stats['regular_customer']): ?>
                                    <span class="status-badge status-regular">Regular Customer</span>
                                <?php else: ?>
                                    <span class="status-badge status-occasional">Occasional Customer</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="detail-group">
                            <div class="detail-label">Loyalty Score</div>
                            <div class="detail-value">
                                <?php 
                                $loyalty_score = 0;
                                if ($stats['total_bookings'] > 0) {
                                    // Calculate a simple loyalty score based on bookings and amount
                                    $loyalty_score = min(100, ($stats['total_bookings'] * 10) + ($stats['total_amount'] > 1000 ? 20 : 0) + ($stats['regular_customer'] ? 30 : 0));
                                }
                                echo $loyalty_score . '/100';
                                ?>
                            </div>
                        </div>
                        <div class="detail-group">
                            <div class="detail-label">Potential Value</div>
                            <div class="detail-value">
                                <?php 
                                if ($stats['regular_customer'] && $stats['total_amount'] > 500) {
                                    echo 'High';
                                } elseif ($stats['total_bookings'] > 2) {
                                    echo 'Medium';
                                } else {
                                    echo 'Low';
                                }
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Recommendations Section -->
                <div class="recommendations">
                    <h4><i class="fas fa-lightbulb"></i> Recommendations</h4>
                    <ul>
                        <?php if ($stats['regular_customer']): ?>
                            <li>This is a regular customer. Consider offering a loyalty discount or exclusive offers.</li>
                        <?php else: ?>
                            <li>This customer hasn't booked frequently. Consider sending a promotional offer to encourage more bookings.</li>
                        <?php endif; ?>
                        
                        <?php if ($stats['total_bookings'] > 0 && $stats['last_booking_date'] && strtotime($stats['last_booking_date']) < strtotime('-30 days')): ?>
                            <li>Customer hasn't booked in over 30 days. Consider sending a "We miss you" offer.</li>
                        <?php endif; ?>
                        
                        <?php if ($stats['total_amount'] > 1000): ?>
                            <li>High-value customer. Consider offering premium services or priority support.</li>
                        <?php endif; ?>
                    </ul>
                </div>
                
                <!-- Bookings Section -->
                <div class="bookings-section">
                    <h3><i class="fas fa-calendar-check"></i> Booking History</h3>
                    
                    <?php if ($bookings_result->num_rows > 0): ?>
                    <table class="bookings-table">
                        <thead>
                            <tr>
                                <th>Booking ID</th>
                                <th>Username</th>
                                <th>Email</th>
                                <th>Registration Date</th>
                                <?php if ($has_service_details_column): ?>
                                <th>Service Details</th>
                                <?php endif; ?>
                                <?php if ($has_total_amount_column): ?>
                                <th>Amount</th>
                                <?php endif; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            // Reset the result pointer to the beginning
                            $bookings_result->data_seek(0);
                            
                            while ($booking = $bookings_result->fetch_assoc()): 
                            ?>
                            <tr>
                                <td><?php echo $booking['id']; ?></td>
                                <td><?php echo htmlspecialchars($booking['username']); ?></td>
                                <td><?php echo htmlspecialchars($booking['email']); ?></td>
                                <td><?php echo date('M d, Y h:i A', strtotime($booking['created_at'])); ?></td>
                                <?php if ($has_service_details_column): ?>
                                <td><?php echo htmlspecialchars($booking['service_details']); ?></td>
                                <?php endif; ?>
                                <?php if ($has_total_amount_column): ?>
                                <td>₹<?php echo number_format($booking['total_amount'], 2); ?></td>
                                <?php endif; ?>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                    <?php else: ?>
                    <div class="empty-bookings">
                        <i class="fas fa-calendar-times"></i>
                        <h3>No Bookings Found</h3>
                        <p>This user doesn't have any booking history.</p>
                    </div>
                    <?php endif; ?>
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
    </script>
</body>
</html>