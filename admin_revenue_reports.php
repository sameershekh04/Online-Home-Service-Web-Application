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
    return $result && $result->num_rows > 0;
}
// Function to check if column exists
function columnExists($conn, $tableName, $columnName) {
    $result = $conn->query("SHOW COLUMNS FROM $tableName LIKE '$columnName'");
    return $result && $result->num_rows > 0;
}
// Function to get revenue data by month
function getRevenueData($conn, $year = null) {
    if (!tableExists($conn, 'bookings')) {
        return [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0]; // Table doesn't exist
    }
    
    if (!columnExists($conn, 'bookings', 'created_at') || !columnExists($conn, 'bookings', 'total_amount')) {
        return [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0]; // Required columns don't exist
    }
    
    $currentYear = $year ?: date('Y');
    $months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
    $revenueData = [];
    
    foreach ($months as $index => $month) {
        $monthNum = $index + 1;
        
        $sql = "SELECT SUM(total_amount) as total FROM bookings WHERE MONTH(created_at) = $monthNum AND YEAR(created_at) = $currentYear";
        
        $result = $conn->query($sql);
        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $revenueData[] = $row['total'] ? (float)$row['total'] : 0;
        } else {
            $revenueData[] = 0;
        }
    }
    
    return $revenueData;
}
// Function to export report as CSV
function exportReportToCSV($reportType, $data) {
    $filename = $reportType . '_report_' . date('Y-m-d') . '.csv';
    
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=' . $filename);
    
    $output = fopen('php://output', 'w');
    
    // Add UTF-8 BOM to fix encoding issues in Excel
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
    
    switch ($reportType) {
        case 'revenue':
            fputcsv($output, ['Month', 'Revenue']);
            foreach ($data as $index => $revenue) {
                $month = date('F', mktime(0, 0, 0, $index + 1, 1));
                fputcsv($output, [$month, number_format($revenue, 2)]);
            }
            break;
            
        default:
            fputcsv($output, ['Report Data']);
            fputcsv($output, ['No data available for this report type']);
            break;
    }
    
    fclose($output);
    exit;
}
// Get filter parameters
$year = isset($_GET['year']) ? (int)$_GET['year'] : date('Y');
// Fetch data
$revenueData = getRevenueData($conn, $year);
// Handle export request
if (isset($_GET['export']) && $_GET['export'] == 'csv') {
    exportReportToCSV('revenue', $revenueData);
}
// Close database connection
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Revenue Reports - SpitiCare Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
        /* Report Filters */
        .report-filters {
            background-color: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: var(--shadow);
            margin-bottom: 20px;
        }
        .filter-row {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            align-items: center;
        }
        .filter-group {
            flex: 1;
            min-width: 200px;
        }
        .filter-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
            color: var(--text);
        }
        .filter-group select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-family: 'Poppins', sans-serif;
        }
        .filter-actions {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }
        .filter-btn {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 500;
            transition: var(--transition);
        }
        .filter-btn-primary {
            background-color: var(--primary);
            color: white;
        }
        .filter-btn-primary:hover {
            background-color: var(--primary-light);
        }
        .filter-btn-secondary {
            background-color: #e0e0e0;
            color: var(--text);
        }
        .filter-btn-secondary:hover {
            background-color: #d0d0d0;
        }
        .export-btn {
            background-color: var(--success);
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 500;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        .export-btn:hover {
            background-color: #0e9b75;
        }
        /* Report Content */
        .report-content {
            background-color: white;
            border-radius: 10px;
            padding: 25px;
            box-shadow: var(--shadow);
            margin-bottom: 20px;
        }
        .report-section {
            margin-bottom: 30px;
        }
        .report-section:last-child {
            margin-bottom: 0;
        }
        .section-title {
            font-size: 1.3rem;
            color: var(--primary);
            margin-bottom: 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .chart-container {
            position: relative;
            height: 400px;
            margin-bottom: 20px;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .stat-box {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            border-left: 4px solid var(--primary);
            transition: var(--transition);
        }
        .stat-box:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow);
        }
        .stat-box.revenue {
            border-left-color: var(--primary);
        }
        .stat-value {
            font-size: 2rem;
            font-weight: 600;
            margin-bottom: 5px;
        }
        .stat-label {
            color: var(--text-light);
            font-size: 0.9rem;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        .data-table th,
        .data-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        .data-table th {
            background-color: #f8f9fa;
            font-weight: 600;
            color: var(--primary);
        }
        .data-table tr:hover {
            background-color: #f8f9fa;
        }
        .no-data {
            text-align: center;
            padding: 30px;
            color: var(--text-light);
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
            .filter-row {
                flex-direction: column;
            }
            .filter-group {
                width: 100%;
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
            .admin-info {
                margin-top: 10px;
                width: 100%;
                justify-content: space-between;
            }
            .stats-grid {
                grid-template-columns: 1fr;
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
                        <li><a href="admin_manage_users.php"><i class="fas fa-user"></i> Manage Users</a></li>
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
                        <li><a href="admin_manage_services.php"><i class="fas fa-calendar-check"></i> Service Bookings</a></li>
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
                        <li><a href="admin_revenue_reports.php" class="active"><i class="fas fa-chart-line"></i> Revenue Reports</a></li>
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
                    <h1>Revenue Reports</h1>
                </div>
                <div class="admin-info">
                    <img src="https://picsum.photos/seed/admin/40/40.jpg" alt="Admin">
                    <span class="admin-name"><?php echo htmlspecialchars($_SESSION['admin_username']); ?></span>
                    <a href="admin_logout.php" class="logout-btn">Logout</a>
                </div>
            </div>
            
            <!-- Report Filters -->
            <div class="report-filters">
                <div class="filter-row">
                    <div class="filter-group">
                        <label for="year">Year</label>
                        <select id="year" name="year">
                            <?php for ($y = date('Y'); $y >= date('Y') - 5; $y--): ?>
                                <option value="<?php echo $y; ?>" <?php echo $year == $y ? 'selected' : ''; ?>><?php echo $y; ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                </div>
                <div class="filter-actions">
                    <button type="button" class="filter-btn filter-btn-primary" onclick="applyFilters()">Apply Filters</button>
                    <button type="button" class="filter-btn filter-btn-secondary" onclick="resetFilters()">Reset</button>
                    <button class="export-btn" onclick="exportReport()">
                        <i class="fas fa-file-export"></i> Export Report
                    </button>
                </div>
            </div>
            
            <!-- Report Content -->
            <div class="report-content">
                <div class="report-section">
                    <h3 class="section-title">
                        Revenue Analytics
                    </h3>
                    <div class="stats-grid">
                        <div class="stat-box revenue">
                            <div class="stat-value">₹<?php echo number_format(array_sum($revenueData), 2); ?></div>
                            <div class="stat-label">Total Revenue</div>
                        </div>
                        <div class="stat-box revenue">
                            <div class="stat-value">₹<?php echo number_format(max($revenueData), 2); ?></div>
                            <div class="stat-label">Peak Month</div>
                        </div>
                        <div class="stat-box revenue">
                            <div class="stat-value">₹<?php echo number_format(array_sum($revenueData) / 12, 2); ?></div>
                            <div class="stat-label">Avg. Monthly</div>
                        </div>
                        <div class="stat-box revenue">
                            <div class="stat-value">₹<?php echo number_format($revenueData[date('n') - 1], 2); ?></div>
                            <div class="stat-label">This Month</div>
                        </div>
                    </div>
                </div>
                
                <div class="report-section">
                    <h3 class="section-title">Revenue by Month (<?php echo $year; ?>)</h3>
                    <div class="chart-container">
                        <canvas id="revenueChart"></canvas>
                    </div>
                </div>
                
                <div class="report-section">
                    <h3 class="section-title">Revenue Details</h3>
                    <?php if (empty(array_filter($revenueData))): ?>
                        <div class="no-data">No revenue data available</div>
                    <?php else: ?>
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Month</th>
                                    <th>Revenue</th>
                                    <th>Percentage of Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $totalRevenue = array_sum($revenueData);
                                $months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
                                foreach ($revenueData as $index => $revenue): 
                                    $monthName = $months[$index];
                                    $percentage = $totalRevenue > 0 ? ($revenue / $totalRevenue) * 100 : 0;
                                ?>
                                    <tr>
                                        <td><?php echo $monthName; ?></td>
                                        <td>₹<?php echo number_format($revenue, 2); ?></td>
                                        <td><?php echo number_format($percentage, 2); ?>%</td>
                                    </tr>
                                <?php endforeach; ?>
                                <tr style="font-weight: bold; background-color: #f8f9fa;">
                                    <td>Total</td>
                                    <td>₹<?php echo number_format($totalRevenue, 2); ?></td>
                                    <td>100%</td>
                                </tr>
                            </tbody>
                        </table>
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
        
        // Apply filters
        function applyFilters() {
            const year = document.getElementById('year').value;
            
            const url = new URL(window.location.href);
            url.searchParams.set('year', year);
            
            window.location.href = url.toString();
        }
        
        // Reset filters
        function resetFilters() {
            const url = new URL(window.location.href);
            url.searchParams.set('year', new Date().getFullYear());
            
            window.location.href = url.toString();
        }
        
        // Export report
        function exportReport() {
            const year = document.getElementById('year').value;
            
            const url = new URL(window.location.href);
            url.searchParams.set('export', 'csv');
            url.searchParams.set('year', year);
            
            window.location.href = url.toString();
        }
        
        // Chart colors
        const chartColors = {
            primary: '#5d3b66',
            primaryLight: 'rgba(93, 59, 102, 0.1)',
            info: '#0abde3',
            infoLight: 'rgba(10, 189, 227, 0.1)',
            success: '#10ac84',
            successLight: 'rgba(16, 172, 132, 0.1)',
            warning: '#ee5a24',
            warningLight: 'rgba(238, 90, 36, 0.1)',
            accent: '#ff9f43',
            accentLight: 'rgba(255, 159, 67, 0.1)',
            secondary: '#ff6b6b',
            secondaryLight: 'rgba(255, 107, 107, 0.1)'
        };
        
        // Revenue Chart
        const revenueCtx = document.getElementById('revenueChart').getContext('2d');
        const revenueChart = new Chart(revenueCtx, {
            type: 'line',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                datasets: [{
                    label: 'Revenue (₹)',
                    data: <?php echo json_encode($revenueData); ?>,
                    borderColor: chartColors.primary,
                    backgroundColor: chartColors.primaryLight,
                    tension: 0.4,
                    fill: true,
                    pointBackgroundColor: chartColors.primary,
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointRadius: 5,
                    pointHoverRadius: 7
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.7)',
                        padding: 10,
                        titleFont: {
                            size: 14
                        },
                        bodyFont: {
                            size: 14
                        },
                        callbacks: {
                            label: function(context) {
                                return '₹' + context.raw.toLocaleString();
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        },
                        ticks: {
                            callback: function(value) {
                                if (value >= 1000) {
                                    return '₹' + (value/1000) + 'k';
                                }
                                return '₹' + value;
                            }
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });
    </script>
</body>
</html>