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
$db_pass = '';
$db_name = 'spiticare';
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

// Get provider reviews
$reviews_stmt = $conn->prepare("
    SELECT r.*, u.fullName as customerName, s.title as serviceTitle 
    FROM reviews r 
    JOIN users u ON r.user_id = u.id 
    JOIN services s ON r.service_id = s.id 
    WHERE r.provider_id = ? 
    ORDER BY r.created_at DESC
");
$reviews_stmt->bind_param("i", $provider_id);
$reviews_stmt->execute();
$reviews_result = $reviews_stmt->get_result();
$reviews = [];

while ($row = $reviews_result->fetch_assoc()) {
    $reviews[] = $row;
}

// Calculate average rating
$avg_rating = 0;
if (count($reviews) > 0) {
    $total_rating = 0;
    foreach ($reviews as $review) {
        $total_rating += $review['rating'];
    }
    $avg_rating = $total_rating / count($reviews);
}

$reviews_stmt->close();
$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reviews - SpitiCare</title>
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
        .rating-summary {
            display: flex;
            align-items: center;
            margin-bottom: 30px;
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
        }
        .rating-average {
            font-size: 3rem;
            font-weight: 700;
            color: var(--primary);
            margin-right: 20px;
        }
        .rating-details {
            flex: 1;
        }
        .rating-details h3 {
            margin-bottom: 10px;
            color: var(--text);
        }
        .stars {
            color: var(--accent);
            margin-bottom: 10px;
        }
        .rating-count {
            color: var(--text-light);
        }
        .review-card {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 15px;
            border-left: 4px solid var(--primary);
            transition: var(--transition);
        }
        .review-card:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow);
        }
        .review-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
        }
        .reviewer-info {
            display: flex;
            align-items: center;
        }
        .reviewer-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            margin-right: 15px;
        }
        .reviewer-name {
            font-weight: 600;
        }
        .review-service {
            color: var(--text-light);
            font-size: 0.9rem;
        }
        .review-rating {
            color: var(--accent);
        }
        .review-date {
            color: var(--text-light);
            font-size: 0.9rem;
        }
        .review-comment {
            margin-bottom: 15px;
            line-height: 1.6;
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
            .rating-summary {
                flex-direction: column;
                text-align: center;
            }
            .rating-average {
                margin-right: 0;
                margin-bottom: 15px;
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
            .review-header {
                flex-direction: column;
                align-items: flex-start;
            }
            .review-date {
                margin-top: 5px;
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
                <li><a href="provider_services.php"><i class="fas fa-concierge-bell"></i> My Services</a></li>
                <li><a href="provider_bookings.php"><i class="fas fa-calendar-check"></i> Bookings</a></li>
                <li><a href="provider_reviews.php" class="active"><i class="fas fa-star"></i> Reviews</a></li>
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
                    <h1>Reviews</h1>
                </div>
                <div class="provider-info">
                    <img src="https://picsum.photos/seed/<?php echo $provider['id']; ?>/40/40.jpg" alt="<?php echo $provider['fullName']; ?>">
                    <span class="provider-name"><?php echo $provider['fullName']; ?></span>
                    <a href="provider_logout.php" class="logout-btn">Logout</a>
                </div>
            </div>
            
            <div class="content-card">
                <div class="section-header">
                    <h2>Customer Reviews</h2>
                </div>
                
                <?php if (count($reviews) > 0): ?>
                    <div class="rating-summary">
                        <div class="rating-average"><?php echo number_format($avg_rating, 1); ?></div>
                        <div class="rating-details">
                            <h3>Average Rating</h3>
                            <div class="stars">
                                <?php 
                                for ($i = 1; $i <= 5; $i++) {
                                    if ($i <= round($avg_rating)) {
                                        echo '<i class="fas fa-star"></i>';
                                    } else {
                                        echo '<i class="far fa-star"></i>';
                                    }
                                }
                                ?>
                            </div>
                            <div class="rating-count">Based on <?php echo count($reviews); ?> review<?php echo count($reviews) > 1 ? 's' : ''; ?></div>
                        </div>
                    </div>
                    
                    <?php foreach ($reviews as $review): ?>
                        <div class="review-card">
                            <div class="review-header">
                                <div class="reviewer-info">
                                    <img src="https://picsum.photos/seed/<?php echo $review['user_id']; ?>/40/40.jpg" alt="<?php echo $review['customerName']; ?>" class="reviewer-avatar">
                                    <div>
                                        <div class="reviewer-name"><?php echo $review['customerName']; ?></div>
                                        <div class="review-service"><?php echo $review['serviceTitle']; ?></div>
                                    </div>
                                </div>
                                <div>
                                    <div class="review-rating">
                                        <?php 
                                        for ($i = 1; $i <= 5; $i++) {
                                            if ($i <= $review['rating']) {
                                                echo '<i class="fas fa-star"></i>';
                                            } else {
                                                echo '<i class="far fa-star"></i>';
                                            }
                                        }
                                        ?>
                                    </div>
                                    <div class="review-date"><?php echo date('M d, Y', strtotime($review['created_at'])); ?></div>
                                </div>
                            </div>
                            <div class="review-comment">
                                <?php echo $review['comment']; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-star"></i>
                        <h3>No Reviews Yet</h3>
                        <p>You haven't received any reviews from customers yet. Complete some services to get reviews!</p>
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