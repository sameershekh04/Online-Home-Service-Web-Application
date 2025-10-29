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
    return $result->num_rows > 0;
}
// Check if users table exists
if (!tableExists($conn, 'users')) {
    die("<div style='padding: 30px; font-family: Arial, sans-serif; max-width: 800px; margin: 50px auto; background-color: #fff; border-radius: 10px; box-shadow: 0 5px 15px rgba(0,0,0,0.1);'>
        <h2 style='color: #d9534f;'>Table Not Found</h2>
        <p><strong>Error:</strong> The 'users' table does not exist in the database.</p>
        <div style='margin-top: 20px;'>
            <a href='admin_dashboard.php' style='display: inline-block; padding: 10px 20px; background-color: #5d3b66; color: white; text-decoration: none; border-radius: 5px;'>Back to Dashboard</a>
        </div>
    </div>");
}
// Handle delete action
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $user_id = (int)$_GET['id'];
    
    // Prevent deletion of admin users (you can adjust this logic as needed)
    $check_admin = $conn->query("SELECT is_admin FROM users WHERE id = $user_id");
    if ($check_admin->num_rows > 0) {
        $user_data = $check_admin->fetch_assoc();
        if (isset($user_data['is_admin']) && $user_data['is_admin'] == 1) {
            $_SESSION['message'] = "Cannot delete admin user!";
            $_SESSION['msg_type'] = "error";
        } else {
            $delete_query = "DELETE FROM users WHERE id = $user_id";
            if ($conn->query($delete_query) === TRUE) {
                $_SESSION['message'] = "User deleted successfully!";
                $_SESSION['msg_type'] = "success";
            } else {
                $_SESSION['message'] = "Error deleting user: " . $conn->error;
                $_SESSION['msg_type'] = "error";
            }
        }
    }
    header("Location: admin_manage_users.php");
    exit();
}
// Handle edit action
if (isset($_POST['action']) && $_POST['action'] == 'edit') {
    $user_id = (int)$_POST['user_id'];
    $username = $conn->real_escape_string($_POST['username']);
    $email = $conn->real_escape_string($_POST['email']);
    
    // Check if username is already taken by another user
    $check_username = $conn->query("SELECT id FROM users WHERE username = '$username' AND id != $user_id");
    if ($check_username->num_rows > 0) {
        $_SESSION['message'] = "Username already taken by another user!";
        $_SESSION['msg_type'] = "error";
    } else {
        // Update user data
        $update_query = "UPDATE users SET username = '$username', email = '$email' WHERE id = $user_id";
        
        // If password is provided, update it too
        if (!empty($_POST['password'])) {
            $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
            $update_query = "UPDATE users SET username = '$username', email = '$email', password = '$password' WHERE id = $user_id";
        }
        
        if ($conn->query($update_query) === TRUE) {
            $_SESSION['message'] = "User updated successfully!";
            $_SESSION['msg_type'] = "success";
        } else {
            $_SESSION['message'] = "Error updating user: " . $conn->error;
            $_SESSION['msg_type'] = "error";
        }
    }
    header("Location: admin_manage_users.php");
    exit();
}
// Handle search and pagination
$search = '';
if (isset($_GET['search'])) {
    $search = $conn->real_escape_string($_GET['search']);
}
// Pagination variables
$limit = 10; // Number of users per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;
// Build query based on search
if (!empty($search)) {
    $count_query = "SELECT COUNT(*) as total FROM users WHERE username LIKE '%$search%' OR email LIKE '%$search%'";
    $users_query = "SELECT * FROM users WHERE username LIKE '%$search%' OR email LIKE '%$search%' ORDER BY created_at DESC LIMIT $limit OFFSET $offset";
} else {
    $count_query = "SELECT COUNT(*) as total FROM users";
    $users_query = "SELECT * FROM users ORDER BY created_at DESC LIMIT $limit OFFSET $offset";
}
// Get total users count
$count_result = $conn->query($count_query);
$total_users = $count_result->fetch_assoc()['total'];
$total_pages = ceil($total_users / $limit);
// Get users data
$users_result = $conn->query($users_query);
// Close database connection
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - SpitiCare Admin</title>
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
        /* Users Table */
        .users-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .users-table th, .users-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        .users-table th {
            background-color: var(--light);
            font-weight: 600;
            color: var(--text);
        }
        .users-table tr:hover {
            background-color: #f9f9f9;
        }
        .users-table .actions {
            display: flex;
            gap: 10px;
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
            .users-table {
                font-size: 0.9rem;
            }
            .users-table th, .users-table td {
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
            .section-header {
                flex-direction: column;
                align-items: flex-start;
            }
            .search-bar {
                flex-direction: column;
                margin-bottom: 15px;
            }
            .search-bar input {
                border-radius: 5px;
                margin-bottom: 10px;
            }
            .search-bar button {
                border-radius: 5px;
            }
            .users-table {
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
                    <h1>Manage Users</h1>
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
                    <h2>Registered Users</h2>
                    <div>Total Users: <?php echo $total_users; ?></div>
                </div>
                
                <!-- Search Bar -->
                <form action="admin_manage_users.php" method="GET" class="search-bar">
                    <input type="text" name="search" placeholder="Search by username or email..." value="<?php echo htmlspecialchars($search); ?>">
                    <button type="submit"><i class="fas fa-search"></i> Search</button>
                </form>
                
                <!-- Users Table -->
                <?php if ($users_result->num_rows > 0): ?>
                <table class="users-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Registered Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        // Reopen database connection for fetching user details
                        $conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
                        if ($conn->connect_error) {
                            die("Connection failed: " . $conn->connect_error);
                        }
                        
                        while ($user = $users_result->fetch_assoc()): 
                        ?>
                        <tr>
                            <td><?php echo $user['id']; ?></td>
                            <td><?php echo htmlspecialchars($user['username']); ?></td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                            <td class="actions">
                                <a href="admin_user_details.php?user_id=<?php echo $user['id']; ?>" class="btn btn-view">
                                    <i class="fas fa-eye"></i> View Details
                                </a>
                                <a href="admin_manage_users.php?action=delete&id=<?php echo $user['id']; ?>" class="btn btn-delete" onclick="return confirm('Are you sure you want to delete this user?');">
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
                    <i class="fas fa-users"></i>
                    <h3>No users found</h3>
                    <p>Try adjusting your search criteria or check back later.</p>
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
    </script>
</body>
</html>