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
$stmt = $conn->prepare("SELECT * FROM service_providers WHERE id = ?");
$stmt->bind_param("i", $provider_id);
$stmt->execute();
$result = $stmt->get_result();
$provider = $result->fetch_assoc();
// Check if provider is still verified
if ($provider['status'] != 'verified') {
    session_unset();
    session_destroy();
    header("Location: provider_login.php");
    exit();
}

// Define service type mappings for better display names
$service_mappings = [
    'Plumber' => 'Plumbing Service',
    'Electrician' => 'Electrical Service',
    'Carpenter' => 'Carpentry Service',
    'Cleaner' => 'Cleaning Service'
];

// Get provider bookings with user details
$bookings_stmt = $conn->prepare("
    SELECT b.* 
    FROM bookings b
    WHERE b.service_type = ? 
    ORDER BY b.booking_date DESC, b.booking_time DESC
");
$bookings_stmt->bind_param("s", $provider['service_type']);
$bookings_stmt->execute();
$bookings_result = $bookings_stmt->get_result();
$bookings = [];
while ($row = $bookings_result->fetch_assoc()) {
    $bookings[] = $row;
}
$bookings_stmt->close();
$stmt->close();
$conn->close();
?>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SpitiCare - Booking Management</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
              --primary: #5d3b66;
            --primary-light: #8e44ad;
            --secondary: #f97316;
            --accent: #ec4899;
            --light: #f8f9fa;
            --dark: #1e293b;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --gray-100: #f1f5f9;
            --gray-200: #e2e8f0;
            --gray-300: #cbd5e1;
            --gray-400: #94a3b8;
            --gray-500: #64748b;
            --gray-600: #475569;
            --gray-700: #334155;
            --gray-800: #1e293b;
            --border-radius: 12px;
            --box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --transition: all 0.3s ease;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--gray-100);
            color: var(--dark);
            line-height: 1.6;
        }

        .dashboard-container {
            display: flex;
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
        }

        .logo {
            font-size: 1.5rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .nav-item {
            padding: 12px 20px;
            display: flex;
            align-items: center;
            gap: 12px;
            color: rgba(255, 255, 255, 0.8);
            transition: var(--transition);
        }

        .nav-item:hover, .nav-item.active {
            background-color: rgba(255, 255, 255, 0.1);
            color: white;
        }

        .nav-item i {
            width: 20px;
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
            box-shadow: var(--box-shadow);
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .section-title {
            font-size: 1.8rem;
            color: var(--primary);
        }

        .provider-info {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .provider-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
        }

        .provider-name {
            font-weight: 500;
        }

        .logout-btn {
            background-color: var(--danger);
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
            background-color: #dc2626;
        }

        /* Content Card */
        .content-card {
            background-color: white;
            border-radius: var(--border-radius);
            padding: 25px;
            box-shadow: var(--box-shadow);
            margin-bottom: 30px;
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .tab-navigation {
            display: flex;
            border-bottom: 2px solid var(--gray-200);
            margin-bottom: 20px;
        }

        .tab {
            padding: 0.75rem 1.5rem;
            background-color: transparent;
            border: none;
            border-radius: 8px 8px 0 0;
            font-weight: 500;
            color: var(--gray-600);
            cursor: pointer;
            transition: var(--transition);
        }

        .tab.active {
            background-color: var(--primary);
            color: white;
        }

        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
        }

        /* Booking Card */
        .booking-card {
            background-color: white;
            border-radius: var(--border-radius);
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            border: 1px solid var(--gray-200);
            transition: var(--transition);
        }

        .booking-card:hover {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            transform: translateY(-2px);
        }

        .booking-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }

        .booking-id {
            font-weight: 600;
            color: var(--primary);
        }

        .status-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.875rem;
            font-weight: 500;
        }

        .status-pending {
            background-color: #fef3c7;
            color: #92400e;
        }

        .status-confirmed {
            background-color: #dbeafe;
            color: #1e40af;
        }

        .status-completed {
            background-color: #dcfce7;
            color: #166534;
        }

        .status-cancelled {
            background-color: #fee2e2;
            color: #b91c1c;
        }

        .payment-status {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.875rem;
            font-weight: 500;
            margin-bottom: 1rem;
        }

        .payment-icon {
            width: 20px;
            height: 20px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .paid {
            background-color: #dcfce7;
            color: #166534;
        }

        .unpaid {
            background-color: #fef3c7;
            color: #92400e;
        }

        .booking-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .booking-detail {
            display: flex;
            flex-direction: column;
        }

        .detail-label {
            font-size: 0.875rem;
            color: var(--gray-500);
            margin-bottom: 0.25rem;
        }

        .detail-value {
            font-weight: 500;
            color: var(--dark);
        }

        .booking-actions {
            display: flex;
            gap: 0.75rem;
        }

        .btn {
            padding: 0.5rem 1rem;
            border-radius: 8px;
            font-weight: 500;
            cursor: pointer;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-primary {
            background-color: var(--primary);
            color: white;
        }

        .btn-primary:hover {
            background-color: var(--primary-dark);
        }

        .btn-success {
            background-color: var(--success);
            color: white;
        }

        .btn-success:hover {
            background-color: #059669;
        }

        .btn-danger {
            background-color: var(--danger);
            color: white;
        }

        .btn-danger:hover {
            background-color: #dc2626;
        }

        .empty-state {
            text-align: center;
            padding: 3rem 1rem;
            color: var(--gray-500);
        }

        .empty-state i {
            font-size: 3rem;
            color: var(--gray-400);
            margin-bottom: 1rem;
        }

        .empty-state h3 {
            font-size: 1.25rem;
            font-weight: 500;
            margin-bottom: 0.5rem;
        }

        /* Payment Breakdown Section */
        .payment-breakdown {
            background-color: #f8fafc;
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 1rem;
        }

        .breakdown-row {
            display: flex;
            justify-content: space-between;
            padding: 0.5rem 0;
            border-bottom: 1px solid #e2e8f0;
        }

        .breakdown-row:last-child {
            border-bottom: none;
            font-weight: 600;
            font-size: 1.1rem;
        }

        .breakdown-label {
            color: var(--gray-600);
        }

        .breakdown-value {
            color: var(--dark);
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .sidebar {
                width: 200px;
            }

            .main-content {
                margin-left: 200px;
            }

            .booking-details {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
                z-index: 1000;
            }

            .main-content {
                margin-left: 0;
            }

            .mobile-menu-btn {
                display: block;
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

            .booking-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }

            .booking-actions {
                flex-wrap: wrap;
            }
        }
.booking-details {
    display: flex;
    flex-direction: row;
    gap: 100px; /* controls space between each detail */
}

.booking-detail {
    display: flex;
    justify-content: space-between;
    padding: 8px 0; /* internal spacing */
    border-bottom: 1px solid #eee; /* optional: divider line */
}

    </style>
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <div class="logo">
                      <h3><i class="fas fa-home"></i> SpitiCare</h3>
                </div>
            </div>
            
            <nav class="nav-menu">
                <a href="provider_dashboard.php" class="nav-item active">
                    <i class="fas fa-home"></i>
                    <span>Dashboard</span>
                </a>
                <a href="#" class="nav-item">
                    <i class="fas fa-calendar-alt"></i>
                    <span>Bookings</span>
                </a>
                <a href="#" class="nav-item">
                    <i class="fas fa-user-clock"></i>
                    <span>Schedule</span>
                </a>
                <a href="#" class="nav-item">
                    <i class="fas fa-star"></i>
                    <span>Reviews</span>
                </a>
                <a href="#" class="nav-item">
                    <i class="fas fa-cog"></i>
                    <span>Settings</span>
                </a>
            </nav>
        </aside>
        
        <!-- Main Content -->
        <main class="main-content">
            <div class="header">
                <div style="display: flex; align-items: center;">
                                                               
                    <h1>Bookings</h1>
                </div>
                <div class="provider-info">
                    <img src="https://picsum.photos/seed/<?php echo $provider['id']; ?>/40/40.jpg" alt="<?php echo $provider['fullName']; ?>">
                    <span class="provider-name"><?php echo $provider['fullName']; ?></span>
                    <a href="provider_logout.php" class="logout-btn">Logout</a>
                </div>
            </div>
            
            <div class="content-card">
                <div class="section-header">
                    <h2>Booking Management</h2>
                </div>
                
                <div class="tab-navigation">
                    <button class="tab active" data-tab="all">All Bookings</button>
                    <button class="tab" data-tab="pending">Pending</button>
                    <button class="tab" data-tab="confirmed">Confirmed</button>
                    <button class="tab" data-tab="completed">Completed</button>
                    <button class="tab" data-tab="cancelled">Cancelled</button>
                </div>
                
                <!-- All Bookings -->
                <div class="tab-content active" id="all">
                    <?php 
                    if (count($bookings) > 0): 
                        foreach ($bookings as $booking): 
                            // Calculate platform fees
                            $userPlatformFee = 69;
                            $gstOnUserFee = round($userPlatformFee * 0.18, 2);
                            $providerPlatformFee = 9;
                            $totalDeductions = $userPlatformFee + $gstOnUserFee + $providerPlatformFee;
                            $netAmount = $booking['total_amount'] - $totalDeductions;
                    ?>
                        <div class="booking-card" data-status="<?php echo $booking['status']; ?>" data-id="<?php echo $booking['id']; ?>">
                            <div class="booking-header">
                                <span class="booking-id">Booking #<?php echo $booking['id']; ?></span>
                                <span class="status-badge status-<?php echo $booking['status']; ?>"><?php echo ucfirst($booking['status']); ?></span>
                            </div>
                            
                            <div class="payment-status">
                                <div class="payment-icon unpaid">
                                    <i class="fas fa-circle"></i>
                                </div>
                                                          </div>
                            
                            <div class="payment-breakdown">
                                <div class="breakdown-row">
                                    <span class="breakdown-label">Original Amount:</span>
                                    <span class="breakdown-value">₹<?php echo number_format($booking['total_amount'], 2); ?></span>
                                </div>
                                <div class="breakdown-row">
                                    <span class="breakdown-label">User Platform Fee:</span>
                                    <span class="breakdown-value">₹<?php echo number_format($userPlatformFee, 2); ?></span>
                                </div>
                                <div class="breakdown-row">
                                    <span class="breakdown-label">GST (18%):</span>
                                    <span class="breakdown-value">₹<?php echo number_format($gstOnUserFee, 2); ?></span>
                                </div>
                                <div class="breakdown-row">
                                    <span class="breakdown-label">Provider Platform Fee:</span>
                                    <span class="breakdown-value">₹<?php echo number_format($providerPlatformFee, 2); ?></span>
                                </div>
                                <div class="breakdown-row">
                                    <span class="breakdown-label">Total Deductions:</span>
                                    <span class="breakdown-value">₹<?php echo number_format($totalDeductions, 2); ?></span>
                                </div>
                                <div class="breakdown-row">
                                    <span class="breakdown-label">Net Amount (Provider):</span>
                                    <span class="breakdown-value">₹<?php echo number_format($netAmount, 2); ?></span>
                                </div>
                            </div>
                            
                            <div class="booking-details">
                                <div class="booking-detail">
                                    <div class="detail-label">Service</div>
                                    <div class="detail-value">
                                        <?php echo isset($service_mappings[$booking['service_type']]) ? $service_mappings[$booking['service_type']] : $booking['service_type']; ?>
                                    </div>
                                </div>
                                
                                <div class="booking-detail">
                                    <div class="detail-label">Customer</div>
                                    <div class="detail-value"><?php echo htmlspecialchars($booking['username']); ?></div>
                                </div>
                                
                                <div class="booking-detail">
                                    <div class="detail-label">Contact</div>
                                    <div class="detail-value"><?php echo htmlspecialchars($booking['phone']); ?></div>
                                </div>
                                
                                <div class="booking-detail">
                                    <div class="detail-label">Email</div>
                                    <div class="detail-value"><?php echo htmlspecialchars($booking['email']); ?></div>
                                </div>
                                
                                <div class="booking-detail">
                                    <div class="detail-label">Date & Time</div>
                                    <div class="detail-value">
                                        <?php echo date('M d, Y', strtotime($booking['booking_date'])); ?><br>
                                        <?php echo date('h:i A', strtotime($booking['booking_time'])); ?>
                                    </div>
                                </div>
                                
                              
                                
                                <div class="booking-detail">
                                    <div class="detail-label">Location</div>
                                    <div class="detail-value">
                                        Flat: <?php echo $booking['flat_no']; ?><br>
                                        Street: <?php echo $booking['street']; ?><br>
                                        Landmark: <?php echo $booking['landmark']; ?><br>
                                        Pincode: <?php echo $booking['pincode']; ?>
                                    </div>
                                </div>
                                
                                <div class="booking-detail">
                                    <div class="detail-label">Instructions</div>
                                    <div class="detail-value"><?php echo nl2br(htmlspecialchars($booking['instructions'])); ?></div>
                                </div>
                            </div>
                            
                            <div class="booking-actions">
                                <?php if ($booking['status'] == 'pending'): ?>
                                    <a href="update_booking.php?id=<?php echo $booking['id']; ?>&action=confirm" class="btn btn-success">Confirm</a>
                                    <a href="update_booking.php?id=<?php echo $booking['id']; ?>&action=cancel" class="btn btn-danger">Cancel</a>
                                <?php elseif ($booking['status'] == 'confirmed'): ?>
                                    <a href="update_booking.php?id=<?php echo $booking['id']; ?>&action=complete" class="btn btn-success">Mark Complete</a>
                                    <a href="update_booking.php?id=<?php echo $booking['id']; ?>&action=cancel" class="btn btn-danger">Cancel</a>
                                <?php else: ?>
                                    <a href="view_booking.php?id=<?php echo $booking['id']; ?>" class="btn btn-primary">View Details</a>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endforeach; 
                    else: ?>
                        <div class="empty-state">
                            <i class="fas fa-calendar-times"></i>
                            <h3>No Bookings Found</h3>
                            <p>You don't have any bookings at the moment.</p>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Pending Bookings -->
                <div class="tab-content" id="pending">
                    <?php 
                    $pending_bookings = array_filter($bookings, function($booking) {
                        return $booking['status'] === 'pending';
                    });
                    
                    if (count($pending_bookings) > 0): 
                        foreach ($pending_bookings as $booking): 
                            // Calculate platform fees
                            $userPlatformFee = 69;
                            $gstOnUserFee = round($userPlatformFee * 0.18, 2);
                            $providerPlatformFee = 9;
                            $totalDeductions = $userPlatformFee + $gstOnUserFee + $providerPlatformFee;
                            $netAmount = $booking['total_amount'] - $totalDeductions;
                    ?>
                        <div class="booking-card" data-status="<?php echo $booking['status']; ?>" data-id="<?php echo $booking['id']; ?>">
                            <div class="booking-header">
                                <span class="booking-id">Booking #<?php echo $booking['id']; ?></span>
                                <span class="status-badge status-pending">Pending</span>
                            </div>
                            
                            <div class="payment-status">
                                <div class="payment-icon unpaid">
                                    <i class="fas fa-circle"></i>
                                </div>
                                <span>Unpaid Payment</span>
                            </div>
                            
                            <div class="payment-breakdown">
                                <div class="breakdown-row">
                                    <span class="breakdown-label">Original Amount:</span>
                                    <span class="breakdown-value">₹<?php echo number_format($booking['total_amount'], 2); ?></span>
                                </div>
                                <div class="breakdown-row">
                                    <span class="breakdown-label">User Platform Fee:</span>
                                    <span class="breakdown-value">₹<?php echo number_format($userPlatformFee, 2); ?></span>
                                </div>
                                <div class="breakdown-row">
                                    <span class="breakdown-label">GST (18%):</span>
                                    <span class="breakdown-value">₹<?php echo number_format($gstOnUserFee, 2); ?></span>
                                </div>
                                <div class="breakdown-row">
                                    <span class="breakdown-label">Provider PlatformFee:</span>
                                    <span class="breakdown-value">₹<?php echo number_format($providerPlatformFee, 2); ?></span>
                                </div>
                                <div class="breakdown-row">
                                    <span class="breakdown-label">Total Deductions:</span>
                                    <span class="breakdown-value">₹<?php echo number_format($totalDeductions, 2); ?></span>
                                </div>
                                <div class="breakdown-row">
                                    <span class="breakdown-label">Net Amount (Provider):</span>
                                    <span class="breakdown-value">₹<?php echo number_format($netAmount, 2); ?></span>
                                </div>
                            </div>
                            
                            <div class="booking-details">
                                <div class="booking-detail">
                                    <div class="detail-label">Service</div>
                                    <div class="detail-value">
                                        <?php echo isset($service_mappings[$booking['service_type']]) ? $service_mappings[$booking['service_type']] : $booking['service_type']; ?>
                                    </div>
                                </div>
                                
                                <div class="booking-detail">
                                    <div class="detail-label">Customer</div>
                                    <div class="detail-value"><?php echo htmlspecialchars($booking['username']); ?></div>
                                </div>
                                
                                <div class="booking-detail">
                                    <div class="detail-label">Contact</div>
                                    <div class="detail-value"><?php echo htmlspecialchars($booking['phone']); ?></div>
                                </div>
                                
                                <div class="booking-detail">
                                    <div class="detail-label">Email</div>
                                    <div class="detail-value"><?php echo htmlspecialchars($booking['email']); ?></div>
                                </div>
                                
                                <div class="booking-detail">
                                    <div class="detail-label">Date & Time</div>
                                    <div class="detail-value">
                                        <?php echo date('M d, Y', strtotime($booking['booking_date'])); ?><br>
                                        <?php echo date('h:i A', strtotime($booking['booking_time'])); ?>
                                    </div>
                                </div>
                                
                                                                
                                <div class="booking-detail">
                                    <div class="detail-label">Location</div>
                                    <div class="detail-value">
                                        Flat: <?php echo $booking['flat_no']; ?><br>
                                        Street: <?php echo $booking['street']; ?><br>
                                        Landmark: <?php echo $booking['landmark']; ?><br>
                                        Pincode: <?php echo $booking['pincode']; ?>
                                    </div>
                                </div>
                                
                                <div class="booking-detail">
                                    <div class="detail-label">Instructions</div>
                                    <div class="detail-value"><?php echo nl2br(htmlspecialchars($booking['instructions'])); ?></div>
                                </div>
                            </div>
                            
                            <div class="booking-actions">
                                <a href="update_booking.php?id=<?php echo $booking['id']; ?>&action=confirm" class="btn btn-success">Confirm</a>
                                <a href="update_booking.php?id=<?php echo $booking['id']; ?>&action=cancel" class="btn btn-danger">Cancel</a>
                            </div>
                        </div>
                        <?php endforeach; 
                    else: ?>
                        <div class="empty-state">
                            <i class="fas fa-calendar-times"></i>
                            <h3>No Pending Bookings</h3>
                            <p>You don't have any pending bookings at the moment.</p>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Confirmed Bookings -->
                <div class="tab-content" id="confirmed">
                    <?php 
                    $confirmed_bookings = array_filter($bookings, function($booking) {
                        return $booking['status'] === 'confirmed';
                    });
                    
                    if (count($confirmed_bookings) > 0): 
                        foreach ($confirmed_bookings as $booking): 
                            // Calculate platform fees
                            $userPlatformFee = 69;
                            $gstOnUserFee = round($userPlatformFee * 0.18, 2);
                            $providerPlatformFee = 9;
                            $totalDeductions = $userPlatformFee + $gstOnUserFee + $providerPlatformFee;
                            $netAmount = $booking['total_amount'] - $totalDeductions;
                    ?>
                        <div class="booking-card" data-status="<?php echo $booking['status']; ?>" data-id="<?php echo $booking['id']; ?>">
                            <div class="booking-header">
                                <span class="booking-id">Booking #<?php echo $booking['id']; ?></span>
                                <span class="status-badge status-confirmed">Confirmed</span>
                            </div>
                            
                            <div class="payment-status">
                                <div class="payment-icon unpaid">
                                    <i class="fas fa-circle"></i>
                                </div>
                                <span>Unpaid Payment</span>
                            </div>
                            
                            <div class="payment-breakdown">
                                <div class="breakdown-row">
                                    <span class="breakdown-label">Original Amount:</span>
                                    <span class="breakdown-value">₹<?php echo number_format($booking['total_amount'], 2); ?></span>
                                </div>
                                <div class="breakdown-row">
                                    <span class="breakdown-label">User Platform Fee:</span>
                                    <span class="breakdown-value">₹<?php echo number_format($userPlatformFee, 2); ?></span>
                                </div>
                                <div class="breakdown-row">
                                    <span class="breakdown-label">GST (18%):</span>
                                    <span class="breakdown-value">₹<?php echo number_format($gstOnUserFee, 2); ?></span>
                                </div>
                                <div class="breakdown-row">
                                    <span class="breakdown-label">Provider Platform Fee:</span>
                                    <span class="breakdown-value">₹<?php echo number_format($providerPlatformFee, 2); ?></span>
                                </div>
                                <div class="breakdown-row">
                                    <span class="breakdown-label">Total Deductions:</span>
                                    <span class="breakdown-value">₹<?php echo number_format($totalDeductions, 2); ?></span>
                                </div>
                                <div class="breakdown-row">
                                    <span class="breakdown-label">Net Amount (Provider):</span>
                                    <span class="breakdown-value">₹<?php echo number_format($netAmount, 2); ?></span>
                                </div>
                            </div>
                            
                            <div class="booking-details">
                                <div class="booking-detail">
                                    <div class="detail-label">Service</div>
                                    <div class="detail-value">
                                        <?php echo isset($service_mappings[$booking['service_type']]) ? $service_mappings[$booking['service_type']] : $booking['service_type']; ?>
                                    </div>
                                </div>
                                
                                <div class="booking-detail">
                                    <div class="detail-label">Customer</div>
                                    <div class="detail-value"><?php echo htmlspecialchars($booking['username']); ?></div>
                                </div>
                                
                                <div class="booking-detail">
                                    <div class="detail-label">Contact</div>
                                    <div class="detail-value"><?php echo htmlspecialchars($booking['phone']); ?></div>
                                </div>
                                
                                <div class="booking-detail">
                                    <div class="detail-label">Email</div>
                                    <div class="detail-value"><?php echo htmlspecialchars($booking['email']); ?></div>
                                </div>
                                
                                <div class="booking-detail">
                                    <div class="detail-label">Date & Time</div>
                                    <div class="detail-value">
                                        <?php echo date('M d, Y', strtotime($booking['booking_date'])); ?><br>
                                        <?php echo date('h:i A', strtotime($booking['booking_time'])); ?>
                                    </div>
                                </div>
                                
                                <div class="booking-detail">
                                    <div class="detail-label">Amount</div>
                                    <div class="detail-value">₹<?php echo number_format($booking['total_amount'], 2); ?></div>
                                </div>
                                
                                <div class="booking-detail">
                                    <div class="detail-label">Location</div>
                                    <div class="detail-value">
                                        Flat: <?php echo $booking['flat_no']; ?><br>
                                        Street: <?php echo $booking['street']; ?><br>
                                        Landmark: <?php echo $booking['landmark']; ?><br>
                                        Pincode: <?php echo $booking['pincode']; ?>
                                    </div>
                                </div>
                                
                                <div class="booking-detail">
                                    <div class="detail-label">Instructions</div>
                                    <div class="detail-value"><?php echo nl2br(htmlspecialchars($booking['instructions'])); ?></div>
                                </div>
                            </div>
                            
                            <div class="booking-actions">
                                <a href="update_booking.php?id=<?php echo $booking['id']; ?>&action=complete" class="btn btn-success">Mark Complete</a>
                                <a href="update_booking.php?id=<?php echo $booking['id']; ?>&action=cancel" class="btn btn-danger">Cancel</a>
                            </div>
                        </div>
                        <?php endforeach; 
                    else: ?>
                        <div class="empty-state">
                            <i class="fas fa-calendar-check"></i>
                            <h3>No Confirmed Bookings</h3>
                            <p>You don't have any confirmed bookings at the moment.</p>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Completed Bookings -->
                <div class="tab-content" id="completed">
                    <?php 
                    $completed_bookings = array_filter($bookings, function($booking) {
                        return $booking['status'] === 'completed';
                    });
                    
                    if (count($completed_bookings) > 0): 
                        foreach ($completed_bookings as $booking): 
                            // Calculate platform fees
                            $userPlatformFee = 69;
                            $gstOnUserFee = round($userPlatformFee * 0.18, 2);
                            $providerPlatformFee = 9;
                            $totalDeductions = $userPlatformFee + $gstOnUserFee + $providerPlatformFee;
                            $netAmount = $booking['total_amount'] - $totalDeductions;
                    ?>
                        <div class="booking-card" data-status="<?php echo $booking['status']; ?>" data-id="<?php echo $booking['id']; ?>">
                            <div class="booking-header">
                                <span class="booking-id">Booking #<?php echo $booking['id']; ?></span>
                                <span class="status-badge status-completed">Completed</span>
                            </div>
                            
                            <div class="payment-status">
                                <div class="payment-icon paid">
                                    <i class="fas fa-circle"></i>
                                </div>
                                <span>Paid Payment</span>
                            </div>
                            
                            <div class="payment-breakdown">
                                <div class="breakdown-row">
                                    <span class="breakdown-label">Original Amount:</span>
                                    <span class="breakdown-value">₹<?php echo number_format($booking['total_amount'], 2); ?></span>
                                </div>
                                <div class="breakdown-row">
                                    <span class="breakdown-label">User Platform Fee:</span>
                                    <span class="breakdown-value">₹<?php echo number_format($userPlatformFee, 2); ?></span>
                                </div>
                                <div class="breakdown-row">
                                    <span class="breakdown-label">GST (18%):</span>
                                    <span class="breakdown-value">₹<?php echo number_format($gstOnUserFee, 2); ?></span>
                                </div>
                                <div class="breakdown-row">
                                    <span class="breakdown-label">Provider Platform Fee:</span>
                                    <span class="breakdown-value">₹<?php echo number_format($providerPlatformFee, 2); ?></span>
                                </div>
                                <div class="breakdown-row">
                                    <span class="breakdown-label">Total Deductions:</span>
                                    <span class="breakdown-value">₹<?php echo number_format($totalDeductions, 2); ?></span>
                                </div>
                                <div class="breakdown-row">
                                    <span class="breakdown-label">Net Amount (Provider):</span>
                                    <span class="breakdown-value">₹<?php echo number_format($netAmount, 2); ?></span>
                                </div>
                            </div>
                            
                            <div class="booking-details">
                                <div class="booking-detail">
                                    <div class="detail-label">Service</div>
                                    <div class="detail-value">
                                        <?php echo isset($service_mappings[$booking['service_type']]) ? $service_mappings[$booking['service_type']] : $booking['service_type']; ?>
                                    </div>
                                </div>
                                
                                <div class="booking-detail">
                                    <div class="detail-label">Customer</div>
                                    <div class="detail-value"><?php echo htmlspecialchars($booking['username']); ?></div>
                                </div>
                                
                                <div class="booking-detail">
                                    <div class="detail-label">Contact</div>
                                    <div class="detail-value"><?php echo htmlspecialchars($booking['phone']); ?></div>
                                </div>
                                
                                <div class="booking-detail">
                                    <div class="detail-label">Email</div>
                                    <div class="detail-value"><?php echo htmlspecialchars($booking['email']); ?></div>
                                </div>
                                
                                <div class="booking-detail">
                                    <div class="detail-label">Date & Time</div>
                                    <div class="detail-value">
                                        <?php echo date('M d, Y', strtotime($booking['booking_date'])); ?><br>
                                        <?php echo date('h:i A', strtotime($booking['booking_time'])); ?>
                                    </div>
                                </div>
                                
                                <div class="booking-detail">
                                    <div class="detail-label">Amount</div>
                                    <div class="detail-value">₹<?php echo number_format($booking['total_amount'], 2); ?></div>
                                </div>
                                
                                <div class="booking-detail">
                                    <div class="detail-label">Location</div>
                                    <div class="detail-value">
                                        Flat: <?php echo $booking['flat_no']; ?><br>
                                        Street: <?php echo $booking['street']; ?><br>
                                        Landmark: <?php echo $booking['landmark']; ?><br>
                                        Pincode: <?php echo $booking['pincode']; ?>
                                    </div>
                                </div>
                                
                                <div class="booking-detail">
                                    <div class="detail-label">Instructions</div>
                                    <div class="detail-value"><?php echo nl2br(htmlspecialchars($booking['instructions'])); ?></div>
                                </div>
                            </div>
                            
                            <div class="booking-actions">
                                <a href="view_booking.php?id=<?php echo $booking['id']; ?>" class="btn btn-primary">View Details</a>
                            </div>
                        </div>
                        <?php endforeach; 
                    else: ?>
                        <div class="empty-state">
                            <i class="fas fa-calendar-alt"></i>
                            <h3>No Completed Bookings</h3>
                            <p>You don't have any completed bookings yet.</p>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Cancelled Bookings -->
                <div class="tab-content" id="cancelled">
                    <?php 
                    $cancelled_bookings = array_filter($bookings, function($booking) {
                        return $booking['status'] === 'cancelled';
                    });
                    
                    if (count($cancelled_bookings) > 0): 
                        foreach ($cancelled_bookings as $booking): 
                            // Calculate platform fees
                            $userPlatformFee = 69;
                            $gstOnUserFee = round($userPlatformFee * 0.18, 2);
                            $providerPlatformFee = 9;
                            $totalDeductions = $userPlatformFee + $gstOnUserFee + $providerPlatformFee;
                            $netAmount = $booking['total_amount'] - $totalDeductions;
                    ?>
                        <div class="booking-card" data-status="<?php echo $booking['status']; ?>" data-id="<?php echo $booking['id']; ?>">
                            <div class="booking-header">
                                <span class="booking-id">Booking #<?php echo $booking['id']; ?></span>
                                <span class="status-badge status-cancelled">Cancelled</span>
                            </div>
                            
                            <div class="payment-status">
                                <div class="payment-icon unpaid">
                                    <i class="fas fa-circle"></i>
                                </div>
                                <span>Unpaid Payment</span>
                            </div>
                            
                            <div class="payment-breakdown">
                                <div class="breakdown-row">
                                    <span class="breakdown-label">Original Amount:</span>
                                    <span class="breakdown-value">₹<?php echo number_format($booking['total_amount'], 2); ?></span>
                                </div>
                                <div class="breakdown-row">
                                    <span class="breakdown-label">User Platform Fee:</span>
                                    <span class="breakdown-value">₹<?php echo number_format($userPlatformFee, 2); ?></span>
                                </div>
                                <div class="breakdown-row">
                                    <span class="breakdown-label">GST (18%):</span>
                                    <span class="breakdown-value">₹<?php echo number_format($gstOnUserFee, 2); ?></span>
                                </div>
                                <div class="breakdown-row">
                                    <span class="breakdown-label">Provider Platform Fee:</span>
                                    <span class="breakdown-value">₹<?php echo number_format($providerPlatformFee, 2); ?></span>
                                </div>
                                <div class="breakdown-row">
                                    <span class="breakdown-label">Total Deductions:</span>
                                    <span class="breakdown-value">₹<?php echo number_format($totalDeductions, 2); ?></span>
                                </div>
                                <div class="breakdown-row">
                                    <span class="breakdown-label">Net Amount (Provider):</span>
                                    <span class="breakdown-value">₹<?php echo number_format($netAmount, 2); ?></span>
                                </div>
                            </div>
                            
                            <div class="booking-details">
                                <div class="booking-detail">
                                    <div class="detail-label">Service</div>
                                    <div class="detail-value">
                                        <?php echo isset($service_mappings[$booking['service_type']]) ? $service_mappings[$booking['service_type']] : $booking['service_type']; ?>
                                    </div>
                                </div>
                                
                                <div class="booking-detail">
                                    <div class="detail-label">Customer</div>
                                    <div class="detail-value"><?php echo htmlspecialchars($booking['username']); ?></div>
                                </div>
                                
                                <div class="booking-detail">
                                    <div class="detail-label">Contact</div>
                                    <div class="detail-value"><?php echo htmlspecialchars($booking['phone']); ?></div>
                                </div>
                                
                                <div class="booking-detail">
                                    <div class="detail-label">Email</div>
                                    <div class="detail-value"><?php echo htmlspecialchars($booking['email']); ?></div>
                                </div>
                                
                                <div class="booking-detail">
                                    <div class="detail-label">Date & Time</div>
                                    <div class="detail-value">
                                        <?php echo date('M d, Y', strtotime($booking['booking_date'])); ?><br>
                                        <?php echo date('h:i A', strtotime($booking['booking_time'])); ?>
                                    </div>
                                </div>
                                
                                <div class="booking-detail">
                                    <div class="detail-label">Amount</div>
                                    <div class="detail-value">₹<?php echo number_format($booking['total_amount'], 2); ?></div>
                                </div>
                                
                                <div class="booking-detail">
                                    <div class="detail-label">Location</div>
                                    <div class="detail-value">
                                        Flat: <?php echo $booking['flat_no']; ?><br>
                                        Street: <?php echo $booking['street']; ?><br>
                                        Landmark: <?php echo $booking['landmark']; ?><br>
                                        Pincode: <?php echo $booking['pincode']; ?>
                                    </div>
                                </div>
                                
                                <div class="booking-detail">
                                    <div class="detail-label">Instructions</div>
                                    <div class="detail-value"><?php echo nl2br(htmlspecialchars($booking['instructions'])); ?></div>
                                </div>
                            </div>
                            
                            <div class="booking-actions">
                                <a href="view_booking.php?id=<?php echo $booking['id']; ?>" class="btn btn-primary">View Details</a>
                            </div>
                        </div>
                        <?php endforeach; 
                    else: ?>
                        <div class="empty-state">
                            <i class="fas fa-calendar-times"></i>
                            <h3>No Cancelled Bookings</h3>
                            <p>You don't have any cancelled bookings.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Tab functionality
        const tabs = document.querySelectorAll('.tab');
        const tabContents = document.querySelectorAll('.tab-content');
        
        tabs.forEach(tab => {
            tab.addEventListener('click', () => {
                const tabId = tab.getAttribute('data-tab');
                
                // Remove active class from all tabs and contents
                tabs.forEach(t => t.classList.remove('active'));
                tabContents.forEach(c => c.classList.remove('active'));
                
                // Add active class to clicked tab and corresponding content
                tab.classList.add('active');
                document.getElementById(tabId).classList.add('active');
            });
        });
        
        // Mobile menu toggle
        const mobileMenuBtn = document.getElementById('mobile-menu-btn');
        const sidebar = document.querySelector('.sidebar');
        
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