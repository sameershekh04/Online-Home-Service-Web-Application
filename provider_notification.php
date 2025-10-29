<?php
session_start();
// Check if provider is logged in
if (!isset($_SESSION['provider_logged_in']) || $_SESSION['provider_logged_in'] !== true) {
    header("Location: provider_login.php");
    exit();
}

// Database configuration
$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'spiticare';

// Create database connection
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get provider ID from session
$provider_id = $_SESSION['provider_id'];

// Get notifications for this provider
$sql = "SELECT * FROM notifications WHERE provider_id = ? ORDER BY created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $provider_id);
$stmt->execute();
$result = $stmt->get_result();

$notifications = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $notifications[] = $row;
    }
}

// Mark all notifications as read
$update_sql = "UPDATE notifications SET is_read = 1 WHERE provider_id = ?";
$update_stmt = $conn->prepare($update_sql);
$update_stmt->bind_param("i", $provider_id);
$update_stmt->execute();
$update_stmt->close();

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications - SpitiCare Provider</title>
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
            background-color: var(--bg-light);
            color: var(--text);
            line-height: 1.6;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        header {
            background: #fff;
            color: #000;
            padding: 1rem 0;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .logo {
            font-size: 1.8rem;
            font-weight: 700;
            display: flex;
            align-items: center;
        }
        .logo i {
            margin-right: 10px;
            color: #ff6600;
            font-size: 2.2rem;
        }
        nav ul {
            display: flex;
            list-style: none;
        }
        nav ul li {
            margin-left: 25px;
        }
        nav ul li a {
            color: #000;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s ease;
        }
        nav ul li a:hover {
            color: #ff6600;
        }
        .notification-badge {
            position: relative;
        }
        .badge {
            position: absolute;
            top: -8px;
            right: -8px;
            background-color: var(--secondary);
            color: white;
            border-radius: 50%;
            width: 18px;
            height: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.7rem;
            font-weight: bold;
        }
        .main-content {
            padding: 40px 0;
        }
        .page-title {
            font-size: 2rem;
            color: var(--primary);
            margin-bottom: 30px;
            text-align: center;
        }
        .notifications-container {
            background: white;
            border-radius: 10px;
            box-shadow: var(--shadow);
            padding: 30px;
        }
        .notification-item {
            padding: 20px;
            border-bottom: 1px solid #eee;
            transition: var(--transition);
        }
        .notification-item:last-child {
            border-bottom: none;
        }
        .notification-item:hover {
            background-color: var(--light);
        }
        .notification-item.unread {
            background-color: rgba(93, 59, 102, 0.05);
            border-left: 4px solid var(--primary);
        }
        .notification-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }
        .notification-time {
            color: var(--text-light);
            font-size: 0.9rem;
        }
        .notification-message {
            color: var(--text);
        }
        .empty-notifications {
            text-align: center;
            padding: 40px;
            color: var(--text-light);
        }
        .empty-notifications i {
            font-size: 3rem;
            margin-bottom: 20px;
            color: var(--text-light);
        }
        .footer {
            background: linear-gradient(135deg, #1a1a2e, #16213e);
            color: var(--white);
            padding: 70px 0 30px;
            text-align: center;
            margin-top: 60px;
        }
        .footer p {
            margin-bottom: 20px;
        }
        @media (max-width: 768px) {
            nav ul {
                flex-direction: column;
                position: absolute;
                top: 100%;
                left: 0;
                width: 100%;
                background: white;
                box-shadow: var(--shadow);
                padding: 20px;
                display: none;
            }
            nav ul.active {
                display: flex;
            }
            nav ul li {
                margin: 0 0 15px 0;
            }
            .mobile-menu-btn {
                display: block;
                background: none;
                border: none;
                font-size: 1.5rem;
                cursor: pointer;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header>
        <div class="container header-content">
            <div class="logo">
                <i class="fas fa-home"></i>
                SpitiCare
            </div>
            <nav>
                <ul>
                    <li><a href="provider_dashboard.php">Dashboard</a></li>
                    <li><a href="provider_profile.php">Profile</a></li>
                    <li><a href="provider_jobs.php">Jobs</a></li>
                    <li class="notification-badge">
                        <a href="provider_notifications.php">Notifications</a>
                        <?php 
                        // Count unread notifications
                        $unread_count = 0;
                        foreach ($notifications as $notification) {
                            if ($notification['is_read'] == 0) {
                                $unread_count++;
                            }
                        }
                        if ($unread_count > 0) {
                            echo '<span class="badge">' . $unread_count . '</span>';
                        }
                        ?>
                    </li>
                    <li><a href="provider_logout.php">Logout</a></li>
                </ul>
            </nav>
            <button class="mobile-menu-btn">
                <i class="fas fa-bars"></i>
            </button>
        </div>
    </header>

    <!-- Main Content -->
    <main class="container main-content">
        <h1 class="page-title">Notifications</h1>
        
        <div class="notifications-container">
            <?php if (count($notifications) > 0): ?>
                <?php foreach ($notifications as $notification): ?>
                    <div class="notification-item <?php echo $notification['is_read'] == 0 ? 'unread' : ''; ?>">
                        <div class="notification-header">
                            <div class="notification-time"><?php echo date('F j, Y, g:i a', strtotime($notification['created_at'])); ?></div>
                        </div>
                        <div class="notification-message">
                            <?php echo $notification['message']; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="empty-notifications">
                    <i class="fas fa-bell-slash"></i>
                    <p>You don't have any notifications.</p>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <p>&copy; 2025 SpitiCare. All rights reserved.</p>
        </div>
    </footer>

    <script>
        // Mobile menu toggle
        const mobileMenuBtn = document.querySelector('.mobile-menu-btn');
        const nav = document.querySelector('nav ul');
        
        mobileMenuBtn.addEventListener('click', function() {
            nav.classList.toggle('active');
        });
    </script>
</body>
</html>