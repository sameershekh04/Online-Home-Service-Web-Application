<?php
session_start();
// Check if provider is logged in and verified
if (!isset($_SESSION['provider_logged_in']) || $_SESSION['provider_logged_in'] !== true) {
    header("Location: provider_login.php");
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

// Get provider details
$provider_id = $_SESSION['provider_id'];

// Check if the table exists
$tableCheck = $conn->query("SHOW TABLES LIKE 'service_providers'");
if ($tableCheck->num_rows == 0) {
    die("Error: Table 'service_providers' does not exist");
}

// Prepare statement with error handling
$stmt = $conn->prepare("SELECT * FROM service_providers WHERE id = ?");
if ($stmt === false) {
    die("Error preparing statement: " . $conn->error);
}
$stmt->bind_param("i", $provider_id);
$stmt->execute();
$result = $stmt->get_result();
$provider = $result->fetch_assoc();

// Check if provider exists
if (!$provider) {
    die("Error: Provider not found");
}

// Check if provider is still verified (in case status was changed)
if ($provider['status'] != 'verified') {
    // Destroy session and redirect to login
    session_unset();
    session_destroy();
    header("Location: provider_login.php");
    exit();
}

// Get provider's service type
$service_type = $provider['service_type'];

// Check if bookings table exists
$bookingTableCheck = $conn->query("SHOW TABLES LIKE 'bookings'");
if ($bookingTableCheck->num_rows == 0) {
    die("Error: Table 'bookings' does not exist");
}

// Fetch bookings for this provider's service type
// Using LIKE to match service type in service_details column
// Fetch bookings for this provider's service type (using service_type column)
$booking_query = "
    SELECT * 
    FROM bookings 
    WHERE service_type = ? 
    ORDER BY booking_date DESC, booking_time DESC
";
$stmt = $conn->prepare($booking_query);
if ($stmt === false) {
    die("Error preparing booking query: " . $conn->error);
}

$stmt->bind_param("s", $service_type);

$stmt->execute();
$bookings_result = $stmt->get_result();
$bookings = $bookings_result->fetch_all(MYSQLI_ASSOC);

// Calculate booking statistics
$total_bookings = count($bookings);

// Get recent bookings (last 5)
$recent_bookings = array_slice($bookings, 0, 5);

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Provider Dashboard - SpitiCare</title>
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
        .provider-info {
            display: flex;
            align-items: center;
        }
        .provider-name {
            font-weight: 500;
            margin-right: 15px;
        }
        .logout-btn {
            background-color: var(--secondary);
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 500;
            transition: var(--transition);
        }
        .logout-btn:hover {
            background-color: #e55039;
        }
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
        .status-badge {
            background-color: var(--success);
            color: white;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 500;
        }
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
        }
        .info-card {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            border-left: 4px solid var(--primary);
        }
        .info-card h3 {
            color: var(--primary);
            margin-bottom: 10px;
            font-size: 1.1rem;
        }
        .info-card p {
            color: var(--text-light);
            margin-bottom: 5px;
        }
        .info-card .value {
            font-weight: 500;
            color: var(--text);
        }
        .welcome-message {
            background-color: rgba(93, 59, 102, 0.1);
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid var(--primary);
        }
        .welcome-message h2 {
            color: var(--primary);
            margin-bottom: 10px;
        }
        .stats-grid {
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
            text-align: center;
            transition: var(--transition);
        }
        .stat-card:hover {
            transform: translateY(-5px);
        }
        .stat-icon {
            font-size: 2rem;
            margin-bottom: 10px;
        }
        .stat-card.total .stat-icon {
            color: var(--primary);
        }
        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 5px;
        }
        .stat-label {
            color: var(--text-light);
            font-size: 0.9rem;
        }
        .bookings-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .bookings-table th, .bookings-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        .bookings-table th {
            background-color: #f8f9fa;
            font-weight: 600;
            color: var(--primary);
        }
        .bookings-table tr:hover {
            background-color: #f8f9fa;
        }
        .action-btn {
            padding: 6px 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.8rem;
            font-weight: 500;
            transition: var(--transition);
            margin-right: 5px;
            text-decoration: none;
            display: inline-block;
        }
        .action-btn.view {
            background-color: var(--info);
            color: white;
        }
        .action-btn.view:hover {
            background-color: #0a8cbc;
        }
        .no-bookings {
            text-align: center;
            padding: 30px;
            color: var(--text-light);
        }
        .mobile-menu-btn {
            display: none;
            background: none;
            border: none;
            color: var(--primary);
            font-size: 1.5rem;
            cursor: pointer;
        }
        .address-display {
            font-size: 0.9rem;
            color: var(--text-light);
        }
        .service-preview {
            max-width: 200px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
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
            .info-grid {
                grid-template-columns: 1fr;
            }
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        @media (max-width: 576px) {
            .header {
                flex-direction: column;
                align-items: flex-start;
            }
            .provider-info {
                margin-top: 10px;
                width: 100%;
                justify-content: space-between;
            }
            .stats-grid {
                grid-template-columns: 1fr;
            }
            .bookings-table {
                font-size: 0.9rem;
            }
            .bookings-table th, .bookings-table td {
                padding: 8px 10px;
            }
            .action-btn {
                padding: 4px 8px;
                font-size: 0.7rem;
            }
        }
/* Recent Bookings Section Styling */
.content-card {
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
    overflow: hidden;
}

.section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px 24px;
    border-bottom: 1px solid #e5e7eb;
}

.section-header h2 {
    font-size: 1.5rem;
    font-weight: 600;
    color: #1f2937;
    margin: 0;
}

.action-btn.view {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 10px 20px;
    border-radius: 8px;
    text-decoration: none;
    font-weight: 500;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.action-btn.view:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
}

.no-bookings {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 60px 24px;
    text-align: center;
}

.no-bookings i {
    font-size: 3rem;
    color: #9ca3af;
    margin-bottom: 16px;
}

.no-bookings p {
    color: #6b7280;
    font-size: 1rem;
}

.table-responsive {
    overflow-x: auto;
}

.bookings-table {
    width: 100%;
    border-collapse: collapse;
}

.bookings-table th,
.bookings-table td {
    padding: 14px 16px;
    text-align: left;
    border-bottom: 1px solid #e5e7eb;
}

.bookings-table th {
    background-color: #f9fafb;
    font-weight: 600;
    color: #374151;
    position: sticky;
    top: 0;
}

.bookings-table tr:hover {
    background-color: #f9fafb;
}

.service-preview {
    max-width: 200px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    color: #6b7280;
}

.address-display {
    max-width: 150px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    color: #6b7280;
}

.action-btn.view {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 8px 16px;
    border-radius: 6px;
    text-decoration: none;
    font-weight: 500;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 0.875rem;
}

.action-btn.view:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
}
/* Hide all content except the View All button */
.no-bookings, .table-responsive {
    display: none !important;
}

/* Optional: Adjust spacing around the button */
.section-header {
    padding: 20px 24px;
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
                <li><a href="provider_dashboard.php" class="active"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li><a href="provider_profile.php"><i class="fas fa-user"></i> My Profile</a></li>
                <li><a href="provider_services.php"><i class="fas fa-concierge-bell"></i> My Services</a></li>
                <li><a href="provider_bookings.php"><i class="fas fa-calendar-check"></i> Bookings</a></li>
                <li><a href="provider_reviews.php"><i class="fas fa-star"></i> Reviews</a></li>
                <li><a href="provider_earnings.php"><i class="fas fa-money-bill-wave"></i> Earnings</a></li>
            </ul>
        </aside>
        
        <!-- Main Content -->
        <main class="main-content">
            <div class="header">
                <div style="display: flex; align-items: center;">
                    <button class="mobile-menu-btn" id="mobile-menu-btn">
                        <i class="fas fa-bars"></i>
                    </button>
                    <h1>Provider Dashboard</h1>
                </div>
                <div class="provider-info">
                    <span class="provider-name">Hello <?php echo $provider['fullName']; ?></span>
                    <a href="provider_login.php" class="logout-btn">Logout</a>
                </div>
            </div>
            
            <div class="welcome-message">
                <h2>Welcome, <?php echo $provider['fullName']; ?>!</h2>
                <p>Your account has been verified and you can now start receiving service requests from customers.</p>
            </div>
            
            <!-- Booking Statistics -->
            <div class="stats-grid">
                <div class="stat-card total">
                    <div class="stat-icon"><i class="fas fa-calendar-check"></i></div>
                    <div class="stat-value"><?php echo $total_bookings; ?></div>
                    <div class="stat-label">Total Bookings</div>
                </div>
            </div>
            
            <div class="content-card">
                <div class="section-header">
                    <h2>Account Information</h2>
                    <span class="status-badge">Verified</span>
                </div>
                
                <div class="info-grid">
                    <div class="info-card">
                        <h3><i class="fas fa-user"></i> Full Name</h3>
                        <p class="value"><?php echo $provider['fullName']; ?></p>
                    </div>
                    
                    <div class="info-card">
                        <h3><i class="fas fa-envelope"></i> Email</h3>
                        <p class="value"><?php echo $provider['email']; ?></p>
                    </div>
                    
                    <div class="info-card">
                        <h3><i class="fas fa-phone"></i> Mobile</h3>
                        <p class="value"><?php echo $provider['mobile']; ?></p>
                    </div>
                    
                    <div class="info-card">
                        <h3><i class="fas fa-briefcase"></i> Service Type</h3>
                        <p class="value"><?php echo $provider['service_type']; ?></p>
                    </div>
                    
                    <div class="info-card">
                        <h3><i class="fas fa-clock"></i> Experience</h3>
                        <p class="value"><?php echo $provider['experience'] . ' years'; ?></p>
                    </div>
                    
                    <div class="info-card">
                        <h3><i class="fas fa-calendar-alt"></i> Member Since</h3>
                        <p class="value"><?php echo date('M d, Y', strtotime($provider['created_at'])); ?></p>
                    </div>
                </div>
            </div>
            
            <!-- Recent Bookings -->
            <div class="content-card">
                <div class="section-header">
                    <h2>Recent Bookings</h2>
                    <a href="provider_bookings.php" class="action-btn view">View All</a>
                </div>
                
                <?php if (empty($recent_bookings)): ?>
                    <div class="no-bookings">
                        <i class="fas fa-calendar-times" style="font-size: 3rem; margin-bottom: 15px; color: var(--text-light);"></i>
                        <p>No bookings found for your service type.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="bookings-table">
                            <thead>
                                <tr>
                                    <th>Booking ID</th>
                                    <th>Customer</th>
                                    <th>Service</th>
                                    <th>Date</th>
                                    <th>Time</th>
                                    <th>Location</th>
                                    <th>Amount</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recent_bookings as $booking): ?>
                                    <tr>
                                        <td>#<?php echo $booking['id']; ?></td>
                                        <td><?php echo $booking['username']; ?></td>
                                        <td>
                                            <div class="service-preview" title="<?php echo htmlspecialchars($booking['service_details']); ?>">
                                                <?php echo substr($booking['service_details'], 0, 30) . '...'; ?>
                                            </div>
                                        </td>
                                        <td><?php echo date('d M Y', strtotime($booking['booking_date'])); ?></td>
                                        <td><?php echo date('h:i A', strtotime($booking['booking_time'])); ?></td>
                                        <td>
                                            <div class="address-display">
                                                <?php 
                                                    $address_parts = array_filter([
                                                        $booking['flat_no'],
                                                        $booking['street'],
                                                        $booking['landmark'],
                                                        $booking['detected_location']
                                                    ]);
                                                    echo implode(', ', $address_parts);
                                                ?>
                                            </div>
                                        </td>
                                        <td>₹<?php echo number_format($booking['total_amount'], 2); ?></td>
                                        <td>
                                            <a href="provider_booking_details.php?id=<?php echo $booking['id']; ?>" class="action-btn view">View</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
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