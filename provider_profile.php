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

// Get provider services
$services_stmt = $conn->prepare("SELECT * FROM services WHERE provider_id = ?");
$services_stmt->bind_param("i", $provider_id);
$services_stmt->execute();
$services_result = $services_stmt->get_result();
$services = [];

while ($row = $services_result->fetch_assoc()) {
    $services[] = $row;
}

$services_stmt->close();
$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Services - SpitiCare</title>
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
        .provider-info {
            display: flex;
            align-items: center;
        }
        .provider-info img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            margin-right: 10px;
        }
        .provider-name {
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
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            font-weight: 500;
            cursor: pointer;
            transition: var(--transition);
            font-size: 0.9rem;
            display: inline-flex;
            align-items: center;
        }
        .btn i {
            margin-right: 8px;
        }
        .btn-primary {
            background-color: var(--primary);
            color: white;
        }
        .btn-primary:hover {
            background-color: #4a2d53;
        }
        .btn-success {
            background-color: var(--success);
            color: white;
        }
        .btn-success:hover {
            background-color: #0e9b75;
        }
        .btn-danger {
            background-color: var(--secondary);
            color: white;
        }
        .btn-danger:hover {
            background-color: #e55039;
        }
        .btn-warning {
            background-color: var(--warning);
            color: white;
        }
        .btn-warning:hover {
            background-color: #d63031;
        }
        .services-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
        }
        .service-card {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            border-left: 4px solid var(--primary);
            transition: var(--transition);
        }
        .service-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow);
        }
        .service-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 15px;
        }
        .service-title {
            font-size: 1.2rem;
            font-weight: 600;
            color: var(--primary);
        }
        .service-price {
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--success);
        }
        .service-description {
            color: var(--text-light);
            margin-bottom: 15px;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        .service-actions {
            display: flex;
            gap: 10px;
        }
        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: var(--text-light);
        }
        .empty-state i {
            font-size: 3rem;
            color: var(--primary-light);
            margin-bottom: 15px;
        }
        .empty-state h3 {
            margin-bottom: 10px;
            color: var(--text);
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
            .services-grid {
                grid-template-columns: 1fr;
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
            .section-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
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
                <li><a href="provider_dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li><a href="provider_profile.php"><i class="fas fa-user"></i> My Profile</a></li>
                <li><a href="provider_services.php" class="active"><i class="fas fa-concierge-bell"></i> My Services</a></li>
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
                    <h1>My Services</h1>
                </div>
                <div class="provider-info">
                    <img src="https://picsum.photos/seed/<?php echo $provider['id']; ?>/40/40.jpg" alt="<?php echo $provider['fullName']; ?>">
                    <span class="provider-name"><?php echo $provider['fullName']; ?></span>
                    <a href="provider_logout.php" class="logout-btn">Logout</a>
                </div>
            </div>
            
            <div class="content-card">
                <div class="section-header">
                    <h2>Service Listings</h2>
                    <a href="add_service.php" class="btn btn-primary"><i class="fas fa-plus"></i> Add New Service</a>
                </div>
                
                <?php if (count($services) > 0): ?>
                    <div class="services-grid">
                        <?php foreach ($services as $service): ?>
                            <div class="service-card">
                                <div class="service-header">
                                    <h3 class="service-title"><?php echo $service['title']; ?></h3>
                                    <span class="service-price">₹<?php echo $service['price']; ?></span>
                                </div>
                                <p class="service-description"><?php echo $service['description']; ?></p>
                                <div class="service-actions">
                                    <a href="edit_service.php?id=<?php echo $service['id']; ?>" class="btn btn-warning"><i class="fas fa-edit"></i> Edit</a>
                                    <a href="delete_service.php?id=<?php echo $service['id']; ?>" class="btn btn-danger"><i class="fas fa-trash"></i> Delete</a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-concierge-bell"></i>
                        <h3>No Services Listed</h3>
                        <p>You haven't added any services yet. Start by adding your first service.</p>
                        <a href="add_service.php" class="btn btn-primary" style="margin-top: 15px;"><i class="fas fa-plus"></i> Add Your First Service</a>
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