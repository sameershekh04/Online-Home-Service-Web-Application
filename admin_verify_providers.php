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
// Create notifications table if it doesn't exist
$createTableSQL = "CREATE TABLE IF NOT EXISTS notifications (
    id INT(11) PRIMARY KEY AUTO_INCREMENT,
    provider_id INT(11) NOT NULL,
    type ENUM('verification', 'rejection', 'general') NOT NULL,
    message TEXT NOT NULL,
    is_read TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (provider_id) REFERENCES service_providers(id) ON DELETE CASCADE
)";
$conn->query($createTableSQL);
// Handle provider verification/rejection
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action'])) {
    $provider_id = $_POST['provider_id'];
    $action = $_POST['action'];
    
    if ($action == 'verify') {
        // Update provider status to verified using prepared statement
        $update_sql = "UPDATE service_providers SET status = 'verified' WHERE id = ?";
        $stmt = $conn->prepare($update_sql);
        $stmt->bind_param("i", $provider_id);
        
        if ($stmt->execute()) {
            // Get provider details for notification
            $select_sql = "SELECT * FROM service_providers WHERE id = ?";
            $stmt = $conn->prepare($select_sql);
            $stmt->bind_param("i", $provider_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $provider = $result->fetch_assoc();
            
            // Add notification to database
            $message = "Your account has been verified! You can now login and start receiving service requests.";
            $notification_sql = "INSERT INTO notifications (provider_id, type, message) VALUES (?, 'verification', ?)";
            $notification_stmt = $conn->prepare($notification_sql);
            $notification_stmt->bind_param("is", $provider_id, $message);
            $notification_stmt->execute();
            $notification_stmt->close();
            
            $_SESSION['message'] = "Provider verified successfully!";
        } else {
            $_SESSION['error'] = "Error verifying provider: " . $conn->error;
        }
        $stmt->close();
    } elseif ($action == 'reject') {
        // Update provider status to rejected using prepared statement
        $update_sql = "UPDATE service_providers SET status = 'rejected' WHERE id = ?";
        $stmt = $conn->prepare($update_sql);
        $stmt->bind_param("i", $provider_id);
        
        if ($stmt->execute()) {
            // Get provider details for notification
            $select_sql = "SELECT * FROM service_providers WHERE id = ?";
            $stmt = $conn->prepare($select_sql);
            $stmt->bind_param("i", $provider_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $provider = $result->fetch_assoc();
            
            // Add notification to database
            $message = "Your account has been rejected. Please contact support for more information.";
            $notification_sql = "INSERT INTO notifications (provider_id, type, message) VALUES (?, 'rejection', ?)";
            $notification_stmt = $conn->prepare($notification_sql);
            $notification_stmt->bind_param("is", $provider_id, $message);
            $notification_stmt->execute();
            $notification_stmt->close();
            
            // Send rejection email
            sendRejectionEmail($provider['email'], $provider['fullName']);
            
            $_SESSION['message'] = "Provider rejected successfully.";
        } else {
            $_SESSION['error'] = "Error rejecting provider: " . $conn->error;
        }
        $stmt->close();
    }
    
    header("Location: admin_verify_providers.php");
    exit();
}
// Get only pending providers
$sql = "SELECT * FROM service_providers WHERE status = 'pending' ORDER BY created_at DESC";
$result = $conn->query($sql);
$providers = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $providers[] = $row;
    }
}
$conn->close();
// Email notification functions
function sendRejectionEmail($email, $name) {
    $subject = "Regarding Your SpitiCare Account";
    $message = "
    <html>
    <head>
        <title>Account Status</title>
    </head>
    <body>
        <h2>Hello $name,</h2>
        <p>We regret to inform you that your SpitiCare service provider account has been rejected.</p>
        <p>If you believe this is a mistake or would like more information, please contact our support team.</p>
        <p>Thank you for your interest in SpitiCare.</p>
        <p>Best regards,<br>The SpitiCare Team</p>
    </body>
    </html>
    ";
    
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= 'From: <noreply@spiticare.com>' . "\r\n";
    
    // Send email
    if (mail($email, $subject, $message, $headers)) {
        return true;
    } else {
        error_log("Failed to send rejection email to: $email");
        return false;
    }
}
function sendSMSNotification($mobile, $name) {
    // In a real implementation, you would use an SMS service like Twilio
    // For now, this is just a placeholder
    $message = "Hello $name, your SpitiCare account has been verified! You can now login and start receiving service requests.";
    
    // Log the SMS attempt
    error_log("SMS notification would be sent to $mobile: $message");
    
    // Example using Twilio (uncomment and configure if you have Twilio):
    /*
    require_once 'vendor/autoload.php';
    use Twilio\Rest\Client;
    
    $sid = 'your_account_sid';
    $token = 'your_auth_token';
    $client = new Client($sid, $token);
    
    try {
        $client->messages->create(
            $mobile,
            [
                'from' => 'your_twilio_phone_number',
                'body' => $message
            ]
        );
        return true;
    } catch (Exception $e) {
        error_log("Failed to send SMS to $mobile: " . $e->getMessage());
        return false;
    }
    */
    
    return true;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Providers - SpitiCare</title>
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
        .badge {
            background-color: var(--accent);
            color: white;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 500;
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
        .alert-info {
            background-color: rgba(10, 189, 227, 0.1);
            color: var(--info);
            border-left: 4px solid var(--info);
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
                <li><a href="admin_manage_providers.php"><i class="fas fa-user-md"></i> Manage Providers</a></li>
                <li><a href="admin_verify_providers.php" class="active"><i class="fas fa-user-check"></i> Verify Providers</a></li>
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
                    <h1>Verify Providers</h1>
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
            
            <!-- Providers Table -->
            <div class="content-card">
                <div class="section-header">
                    <h2>Pending Provider Verifications</h2>
                    <span class="badge"><?php echo count($providers); ?> Pending</span>
                </div>
                
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
                                            <button type="button" class="action-btn verify-btn" 
                                                    onclick="openGmailCompose('<?php echo $provider['email']; ?>', '<?php echo addslashes($provider['fullName']); ?>', <?php echo $provider['id']; ?>)">
                                                Verify
                                            </button>
                                            <form method="post" style="display: inline;">
                                                <input type="hidden" name="provider_id" value="<?php echo $provider['id']; ?>">
                                                <input type="hidden" name="action" value="reject">
                                                <button type="submit" class="action-btn reject-btn">Reject</button>
                                            </form>
                                            <a href="admin_view_provider.php?id=<?php echo $provider['id']; ?>" class="action-btn view-btn">View</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" style="text-align: center;">No pending verifications</td>
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
        
        // Function to open Gmail compose with pre-filled data
        function openGmailCompose(email, name, providerId) {
            // Create the email content
            const subject = "Your SpitiCare Account Has Been Verified";
            const body = `Hello ${name},\n\n` +
                        `Good news! Your SpitiCare service provider account has been verified.\n\n` +
                        `You can now login to your account and start receiving service requests from customers.\n\n` +
                        `Login at: http://localhost/provider_login.php\n\n` +
                        `Thank you for joining SpitiCare!\n\n` +
                        `Best regards,\nThe SpitiCare Team`;
            
            // Create the Gmail compose URL
            const gmailUrl = `https://mail.google.com/mail/u/1/?view=cm&fs=1&to=${encodeURIComponent(email)}&su=${encodeURIComponent(subject)}&body=${encodeURIComponent(body)}`;
            
            // Open Gmail in a new tab
            window.open(gmailUrl, '_blank');
            
            // Submit the verification form in the background
            const form = document.createElement('form');
            form.method = 'POST';
            form.innerHTML = `
                <input type="hidden" name="provider_id" value="${providerId}">
                <input type="hidden" name="action" value="verify">
            `;
            document.body.appendChild(form);
            form.submit();
        }
    </script>
</body>
</html>