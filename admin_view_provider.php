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

// Get provider ID from URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: admin_manage_providers.php");
    exit();
}

$provider_id = $_GET['id'];

// Fetch provider details
$sql = "SELECT * FROM service_providers WHERE id = $provider_id";
$result = $conn->query($sql);

if ($result->num_rows === 0) {
    $_SESSION['error'] = "Provider not found.";
    header("Location: admin_manage_providers.php");
    exit();
}

$provider = $result->fetch_assoc();

// Handle provider actions (verify, reject, delete)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action'])) {
    $action = $_POST['action'];
    
    if ($action == 'verify') {
        // Update provider status to verified
        $sql = "UPDATE service_providers SET status = 'verified' WHERE id = $provider_id";
        
        if ($conn->query($sql) === TRUE) {
            // Get updated provider details
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
            // Get updated provider details
            $sql = "SELECT * FROM service_providers WHERE id = $provider_id";
            $result = $conn->query($sql);
            $provider = $result->fetch_assoc();
            
            $_SESSION['message'] = "Provider rejected successfully.";
        } else {
            $_SESSION['error'] = "Error rejecting provider: " . $conn->error;
        }
    } elseif ($action == 'delete') {
        // Delete provider
        $sql = "DELETE FROM service_providers WHERE id = $provider_id";
        
        if ($conn->query($sql) === TRUE) {
            $_SESSION['message'] = "Provider deleted successfully.";
            header("Location: admin_manage_providers.php");
            exit();
        } else {
            $_SESSION['error'] = "Error deleting provider: " . $conn->error;
        }
    }
    
    // Refresh page to show updated status
    header("Location: admin_view_provider.php?id=$provider_id");
    exit();
}

$conn->close();

// Placeholder notification functions
function sendVerificationEmail($email, $name) {
    // In a real implementation, use PHPMailer or similar
    return true;
}
function sendWhatsAppNotification($mobile, $name) {
    // In a real implementation, use WhatsApp Business API or Twilio
    return true;
}
function sendSMSNotification($mobile, $name) {
    // In a real implementation, use Twilio or similar SMS service
    return true;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Provider Details - SpitiCare</title>
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
        /* Provider Details */
        .provider-details {
            display: flex;
            flex-wrap: wrap;
            gap: 30px;
        }
        .provider-image {
            flex: 0 0 250px;
            text-align: center;
        }
        .provider-image img {
            width: 200px;
            height: 200px;
            border-radius: 50%;
            object-fit: cover;
            border: 5px solid #f0f0f0;
            box-shadow: var(--shadow);
        }
        .no-photo-message {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            width: 200px;
            height: 200px;
            border-radius: 50%;
            border: 5px solid #f0f0f0;
            background-color: #f8f9fa;
            margin: 0 auto;
            color: var(--text-light);
        }
        .no-photo-message i {
            font-size: 3rem;
            margin-bottom: 10px;
        }
        .provider-info {
            flex: 1;
            min-width: 300px;
        }
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
        }
        .info-item {
            margin-bottom: 15px;
        }
        .info-label {
            font-weight: 600;
            color: var(--text-light);
            margin-bottom: 5px;
            font-size: 0.9rem;
        }
        .info-value {
            font-size: 1rem;
            color: var(--text);
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
        /* ID Documents */
        .document-section {
            margin-top: 30px;
        }
        .document-section h3 {
            color: var(--primary);
            margin-bottom: 15px;
            font-size: 1.2rem;
        }
        .document-container {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
        }
        .document-card {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 15px;
            flex: 1;
            min-width: 250px;
            text-align: center;
            transition: var(--transition);
        }
        .document-card:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow);
        }
        .document-card img {
            max-width: 100%;
            max-height: 200px;
            border-radius: 5px;
            margin-bottom: 10px;
        }
        .document-name {
            font-weight: 500;
            margin-bottom: 5px;
        }
        /* Actions */
        .actions {
            margin-top: 30px;
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        .action-btn {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 500;
            transition: var(--transition);
            text-decoration: none;
            display: inline-block;
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
        .back-btn {
            background-color: var(--info);
            color: white;
        }
        .back-btn:hover {
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
            .provider-details {
                flex-direction: column;
            }
            .provider-image {
                flex: 0 0 auto;
                margin: 0 auto;
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
            .info-grid {
                grid-template-columns: 1fr;
            }
            .document-container {
                flex-direction: column;
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
                    <h1>Provider Details</h1>
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
            
            <!-- Provider Details Card -->
            <div class="content-card">
                <div class="section-header">
                    <h2>Provider Information</h2>
                    <span class="status <?php echo $provider['status']; ?>">
                        <?php echo ucfirst($provider['status']); ?>
                    </span>
                </div>
                
                <div class="provider-details">
                    <div class="provider-image">
                        <?php if (!empty($provider['profilePic'])): ?>
                            <img src="<?php echo $provider['profilePic']; ?>" alt="Profile Picture">
                        <?php else: ?>
                            <div class="no-photo-message">
                                <i class="fas fa-user-slash"></i>
                                <p>No Profile Photo</p>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="provider-info">
                        <div class="info-grid">
                            <div class="info-item">
                                <div class="info-label">Full Name</div>
                                <div class="info-value"><?php echo $provider['fullName']; ?></div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">Email</div>
                                <div class="info-value"><?php echo $provider['email']; ?></div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">Mobile</div>
                                <div class="info-value"><?php echo $provider['mobile']; ?></div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">Service Type</div>
                                <div class="info-value"><?php echo ucfirst($provider['service_type']); ?></div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">Experience</div>
                                <div class="info-value"><?php echo $provider['experience'] . ' years'; ?></div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">Pincode</div>
                                <div class="info-value"><?php echo $provider['pincode']; ?></div>
                            </div>
                            <div class="info-item" style="grid-column: span 2;">
                                <div class="info-label">Address</div>
                                <div class="info-value"><?php echo nl2br($provider['localAddress']); ?></div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">Registration Date</div>
                                <div class="info-value"><?php echo date('F j, Y', strtotime($provider['created_at'])); ?></div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- ID Documents Section -->
                <div class="document-section">
                    <h3>Government ID Documents</h3>
                    <div class="document-container">
                        <div class="document-card">
                            <?php if (!empty($provider['govId'])): ?>
                                <img src="<?php echo $provider['govId']; ?>" alt="Government ID">
                                <div class="document-name">Government ID</div>
                            <?php else: ?>
                                <div style="padding: 30px; color: var(--text-light);">
                                    <i class="fas fa-exclamation-triangle" style="font-size: 3rem; margin-bottom: 10px; color: var(--warning);"></i>
                                    <p>No Government ID uploaded</p>
                                    <small style="color: var(--warning);">This is a mandatory document</small>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <!-- Actions -->
                <div class="actions">
                    <?php if ($provider['status'] == 'pending'): ?>
                        <form method="post" style="display: inline;">
                            <input type="hidden" name="action" value="verify">
                            <button type="submit" class="action-btn verify-btn">
                                <i class="fas fa-check-circle"></i> Verify Provider
                            </button>
                        </form>
                        <form method="post" style="display: inline;">
                            <input type="hidden" name="action" value="reject">
                            <button type="submit" class="action-btn reject-btn">
                                <i class="fas fa-times-circle"></i> Reject Provider
                            </button>
                        </form>
                    <?php endif; ?>
                    <form method="post" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this provider?');">
                        <input type="hidden" name="action" value="delete">
                        <button type="submit" class="action-btn delete-btn">
                            <i class="fas fa-trash-alt"></i> Delete Provider
                        </button>
                    </form>
                    <a href="admin_manage_providers.php" class="action-btn back-btn">
                        <i class="fas fa-arrow-left"></i> Back to Providers
                    </a>
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