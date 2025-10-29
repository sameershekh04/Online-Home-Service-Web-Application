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
// Handle provider actions (verify, reject, delete)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action'])) {
    $provider_id = $_POST['provider_id'];
    $action = $_POST['action'];
    
    if ($action == 'verify') {
        // Update provider status to verified
        $sql = "UPDATE service_providers SET status = 'verified' WHERE id = $provider_id";
        
        if ($conn->query($sql) === TRUE) {
            // Get provider details for notification
            $sql = "SELECT * FROM service_providers WHERE id = $provider_id";
            $result = $conn->query($sql);
            $provider = $result->fetch_assoc();
            
            // Send notifications (placeholder functions)
            sendVerificationEmail($provider['email'], $provider['fullName']);
            sendWhatsAppNotification($provider['mobile'], $provider['fullName']);
            sendSMSNotification($provider['mobile'], $provider['fullName']);
            
            $_SESSION['message'] = "Provider verified successfully! Notifications sent.";
        } else {
            $_SESSION['error'] = "Error verifying provider: " . $conn->error;
        }
    } elseif ($action == 'reject') {
        // Update provider status to rejected
        $sql = "UPDATE service_providers SET status = 'rejected' WHERE id = $provider_id";
        
        if ($conn->query($sql) === TRUE) {
            $_SESSION['message'] = "Provider rejected successfully.";
        } else {
            $_SESSION['error'] = "Error rejecting provider: " . $conn->error;
        }
    } elseif ($action == 'delete') {
        // Delete provider
        $sql = "DELETE FROM service_providers WHERE id = $provider_id";
        
        if ($conn->query($sql) === TRUE) {
            $_SESSION['message'] = "Provider deleted successfully.";
        } else {
            $_SESSION['error'] = "Error deleting provider: " . $conn->error;
        }
    }
    
    header("Location: admin_manage_providers.php");
    exit();
}
// Get filter parameters
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Build the query with proper filtering
$sql = "SELECT * FROM service_providers WHERE 1=1";
if (!empty($status_filter)) {
    $sql .= " AND status = '" . $conn->real_escape_string($status_filter) . "'";
}
if (!empty($search)) {
    $escaped_search = $conn->real_escape_string($search);
    $sql .= " AND (fullName LIKE '%$escaped_search%' OR email LIKE '%$escaped_search%' OR mobile LIKE '%$escaped_search%' OR service_type LIKE '%$escaped_search%')";
}
$sql .= " ORDER BY created_at DESC";
$result = $conn->query($sql);
$providers = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $providers[] = $row;
    }
}
// Get counts for different statuses
$pending_count = $conn->query("SELECT COUNT(*) as count FROM service_providers WHERE status = 'pending'")->fetch_assoc()['count'];
$verified_count = $conn->query("SELECT COUNT(*) as count FROM service_providers WHERE status = 'verified'")->fetch_assoc()['count'];
$rejected_count = $conn->query("SELECT COUNT(*) as count FROM service_providers WHERE status = 'rejected'")->fetch_assoc()['count'];
$conn->close();
// Placeholder notification functions
function sendVerificationEmail($email, $name) {
    // In a real implementation, use PHPMailer or similar
    // For now, just return true
    return true;
}
function sendWhatsAppNotification($mobile, $name) {
    // In a real implementation, use WhatsApp Business API or Twilio
    // For now, just return true
    return true;
}
function sendSMSNotification($mobile, $name) {
    // In a real implementation, use Twilio or similar SMS service
    // For now, just return true
    return true;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Providers - SpitiCare</title>
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
        /* Content */
        .content-card {
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
        /* Filters */
        .filters {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        .filter-group {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }
        .filter-group label {
            font-weight: 500;
            color: var(--text);
            font-size: 0.9rem;
        }
        .filter-group select,
        .filter-group input {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 0.9rem;
            width: 200px;
        }
        .filter-btn {
            background-color: var(--primary);
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 500;
            transition: var(--transition);
            align-self: flex-end;
        }
        .filter-btn:hover {
            background-color: var(--primary-light);
        }
        .reset-btn {
            background-color: var(--secondary);
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 500;
            transition: var(--transition);
            align-self: flex-end;
        }
        .reset-btn:hover {
            background-color: #e55039;
        }
        /* Stats */
        .stats-container {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
        }
        .stat-card {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 15px;
            flex: 1;
            text-align: center;
            transition: var(--transition);
        }
        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow);
        }
        .stat-card h3 {
            font-size: 1.5rem;
            margin-bottom: 5px;
        }
        .stat-card p {
            color: var(--text-light);
            font-size: 0.9rem;
        }
        .stat-card.all {
            border-left: 4px solid var(--primary);
        }
        .stat-card.pending {
            border-left: 4px solid var(--accent);
        }
        .stat-card.verified {
            border-left: 4px solid var(--success);
        }
        .stat-card.rejected {
            border-left: 4px solid var(--secondary);
        }
        /* Table */
        .table-container {
            overflow-x: auto;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        th {
            background-color: #f8f9fa;
            font-weight: 600;
            color: var(--text);
        }
        tr:hover {
            background-color: #f8f9fa;
        }
        .status {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
            display: inline-block;
        }
        .status.pending {
            background-color: rgba(255, 159, 67, 0.1);
            color: var(--accent);
        }
        .status.verified {
            background-color: rgba(16, 172, 132, 0.1);
            color: var(--success);
        }
        .status.rejected {
            background-color: rgba(255, 107, 107, 0.1);
            color: var(--secondary);
        }
        .action-btn {
            padding: 6px 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.8rem;
            font-weight: 500;
            margin-right: 5px;
            transition: var(--transition);
        }
        .verify-btn {
            background-color: var(--success);
            color: white;
        }
        .verify-btn:hover {
            background-color: #0e9b6f;
        }
        .reject-btn {
            background-color: var(--secondary);
            color: white;
        }
        .reject-btn:hover {
            background-color: #e55039;
        }
        .delete-btn {
            background-color: var(--dark);
            color: white;
        }
        .delete-btn:hover {
            background-color: #1a252f;
        }
        .view-btn {
            background-color: var(--info);
            color: white;
        }
        .view-btn:hover {
            background-color: #0a8fc7;
        }
        /* Alert */
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
            font-size: 0.9rem;
        }
        .alert-success {
            background-color: rgba(16, 172, 132, 0.1);
            color: var(--success);
            border-left: 4px solid var(--success);
        }
        .alert-danger {
            background-color: rgba(255, 107, 107, 0.1);
            color: var(--secondary);
            border-left: 4px solid var(--secondary);
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
            .filters {
                flex-direction: column;
            }
            .filter-group {
                width: 100%;
            }
            .filter-group select,
            .filter-group input {
                width: 100%;
            }
            .stats-container {
                flex-direction: column;
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
            .table-container {
                font-size: 0.9rem;
            }
            th, td {
                padding: 8px 10px;
            }
            .action-btn {
                padding: 4px 8px;
                font-size: 0.7rem;
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
                <li><a href="admin_manage_users.php"><i class="fas fa-users"></i> Manage Users</a></li>
                <li><a href="admin_manage_providers.php" class="active"><i class="fas fa-user-md"></i> Manage Providers</a></li>
                <li><a href="admin_verify_providers.php"><i class="fas fa-user-check"></i> Verify Providers</a></li>
                <li><a href="admin_manage_services.php"><i class="fas fa-concierge-bell"></i> Manage Services</a></li>
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
                    <h1>Manage Providers</h1>
                </div>
                <div class="admin-info">
                    <img src="https://picsum.photos/seed/admin/40/40.jpg" alt="Admin">
                    <span class="admin-name"><?php echo $_SESSION['admin_username']; ?></span>
                    <a href="admin_logout.php" class="logout-btn">Logout</a>
                </div>
            </div>
            
            <!-- Alert Messages -->
            <?php if (isset($_SESSION['message'])): ?>
                <div class="alert alert-success">
                    <?php echo $_SESSION['message']; unset($_SESSION['message']); ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger">
                    <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                </div>
            <?php endif; ?>
            
            <!-- Filters -->
            <div class="content-card">
                <div class="section-header">
                    <h2>Provider Management</h2>
                    <a href="admin_verify_providers.php" class="view-btn" style="text-decoration: none; color: white; padding: 8px 15px; border-radius: 5px;">
                        <i class="fas fa-filter"></i> Pending Verifications
                    </a>
                </div>
                
                <!-- Stats -->
                <div class="stats-container">
                    <div class="stat-card all">
                        <h3><?php echo $pending_count + $verified_count + $rejected_count; ?></h3>
                        <p>Total Providers</p>
                    </div>
                    <div class="stat-card pending">
                        <h3><?php echo $pending_count; ?></h3>
                        <p>Pending Verification</p>
                    </div>
                    <div class="stat-card verified">
                        <h3><?php echo $verified_count; ?></h3>
                        <p>Verified Providers</p>
                    </div>
                    <div class="stat-card rejected">
                        <h3><?php echo $rejected_count; ?></h3>
                        <p>Rejected Providers</p>
                    </div>
                </div>
                
                <!-- Filter Form -->
                <form method="get" class="filters">
                    <div class="filter-group">
                        <label for="status">Status:</label>
                        <select name="status" id="status">
                            <option value="">All Status</option>
                            <option value="pending" <?php echo $status_filter == 'pending' ? 'selected' : ''; ?>>Pending</option>
                            <option value="verified" <?php echo $status_filter == 'verified' ? 'selected' : ''; ?>>Verified</option>
                            <option value="rejected" <?php echo $status_filter == 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label for="search">Search:</label>
                        <input type="text" id="search" name="search" placeholder="Name, Email, Mobile, Service" value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                    
                    <button type="submit" class="filter-btn">Apply Filters</button>
                    <a href="admin_manage_providers.php" class="reset-btn">Reset</a>
                </form>
                
                <!-- Providers Table -->
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Mobile</th>
                                <th>Service Type</th>
                                <th>Experience</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($providers) > 0): ?>
                                <?php foreach ($providers as $provider): ?>
                                    <tr>
                                        <td><?php echo $provider['id']; ?></td>
                                        <td><?php echo $provider['fullName']; ?></td>
                                        <td><?php echo $provider['email']; ?></td>
                                        <td><?php echo $provider['mobile']; ?></td>
                                        <td><?php echo $provider['service_type']; ?></td>
                                        <td><?php echo $provider['experience'] . ' years'; ?></td>
                                        <td>
                                            <span class="status <?php echo $provider['status']; ?>">
                                                <?php echo ucfirst($provider['status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if ($provider['status'] == 'pending'): ?>
                                                <form method="post" style="display: inline;">
                                                    <input type="hidden" name="provider_id" value="<?php echo $provider['id']; ?>">
                                                    <input type="hidden" name="action" value="verify">
                                                    <button type="submit" class="action-btn verify-btn">Verify</button>
                                                </form>
                                                <form method="post" style="display: inline;">
                                                    <input type="hidden" name="provider_id" value="<?php echo $provider['id']; ?>">
                                                    <input type="hidden" name="action" value="reject">
                                                    <button type="submit" class="action-btn reject-btn">Reject</button>
                                                </form>
                                            <?php endif; ?>
                                            <a href="admin_view_provider.php?id=<?php echo $provider['id']; ?>" class="action-btn view-btn">View</a>
                                            <form method="post" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this provider?');">
                                                <input type="hidden" name="provider_id" value="<?php echo $provider['id']; ?>">
                                                <input type="hidden" name="action" value="delete">
                                                <button type="submit" class="action-btn delete-btn">Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="9" style="text-align: center;">No providers found</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
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