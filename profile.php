<?php
session_start();
// Database connection
$servername = "localhost";
$username = "root";
$password = "Sameer@123";
$dbname = "services_app";
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Database Connection Failed: " . $conn->connect_error);
}
// Check if user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}
$loggedInUser = $_SESSION['username'];
// Fetch user details
$userStmt = $conn->prepare("SELECT * FROM bookings WHERE username = ?");
$userStmt->bind_param("s", $loggedInUser);
$userStmt->execute();
$userResult = $userStmt->get_result();
$userData = $userResult->fetch_assoc();

// Get all bookings for activity section
$activityStmt = $conn->prepare("SELECT * FROM bookings WHERE username = ? ORDER BY created_at DESC LIMIT 3");
$activityStmt->bind_param("s", $loggedInUser);
$activityStmt->execute();
$activityResult = $activityStmt->get_result();

// Bookings fetch
$stmt = $conn->prepare("SELECT * FROM bookings WHERE username = ? ORDER BY id DESC");
$stmt->bind_param("s", $loggedInUser);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>User Profile - SpitiCare</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css"></link>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <style>
    body {
      font-family: 'Poppins', sans-serif;
      background-color: #f8fafc;
    }
    .user-profile {
      position: relative;
      cursor: pointer;
    }
    .profile-icon {
      display: flex;
      align-items: center;
      font-size: 1.8rem;
      color: #ef4444;
      transition: transform 0.2s ease, color 0.2s ease;
    }
    .profile-icon:hover {
      transform: scale(1.1);
      color: #dc2626;
    }
    .notification-dot {
      position: absolute;
      top: 0;
      right: -3px;
      width: 10px;
      height: 10px;
      background: #ef4444;
      border-radius: 50%;
      border: 2px solid white;
    }
    .profile-dropdown {
      position: absolute;
      top: 45px;
      right: 0;
      background: white;
      width: 220px;
      border-radius: 12px;
      box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
      display: none;
      flex-direction: column;
      overflow: hidden;
      z-index: 1000;
      animation: fadeIn 0.25s ease-in-out;
    }
    .user-profile.show .profile-dropdown {
      display: flex;
    }
    .profile-header {
      background: linear-gradient(to right, #ef4444, #ec4899);
      color: white;
      padding: 12px;
      text-align: center;
    }
    .profile-header h3 {
      margin: 0;
      font-size: 1rem;
      font-weight: bold;
    }
    .profile-header p {
      margin: 2px 0 0;
      font-size: 0.8rem;
      opacity: 0.9;
    }
    .profile-dropdown a {
      padding: 12px 15px;
      font-size: 0.9rem;
      color: #374151;
      text-decoration: none;
      display: flex;
      align-items: center;
      gap: 8px;
      transition: background 0.2s, color 0.2s;
    }
    .profile-dropdown a:hover {
      background: #f3f4f6;
      color: #ef4444;
    }
    .logout-btn {
      border-top: 1px solid #eee;
      font-weight: bold;
      color: #ef4444 !important;
    }
    .card-hover {
      transition: transform 0.2s;
    }
    .card-hover:hover {
      transform: translateY(-5px);
    }
  </style>
</head>
<body>
  <!-- Navigation Bar -->
  <nav class="bg-white shadow-md sticky top-0 z-50">
    <div class="container mx-auto px-4 py-3 flex justify-between items-center">
      <div class="flex items-center">
               <span class="text-xl font-bold text-gray-800">SpitiCare</span>
      </div>
           <div class="flex items-center space-x-3">
        <span class="text-gray-700">Welcome, <?= htmlspecialchars($loggedInUser) ?></span>
        <div class="user-profile" id="user-profile">
          <div class="profile-icon" id="profile-icon">
            <i class="fas fa-user-circle"></i>
            <span class="notification-dot"></span>
          </div>
          <div class="profile-dropdown" id="profile-dropdown">
            <div class="profile-header">
              <h3><?= htmlspecialchars($userData['username'] ?? '') ?></h3>
              <p><?= htmlspecialchars($userData['email'] ?? 'user@example.com') ?></p>
            </div>
            <a href="index.php"><i class="fas fa-home"></i> Home</a>
            <a href="edit-profile.php"><i class="fas fa-user-edit"></i> Edit Profile</a>
            <a href="#"><i class="fas fa-shopping-cart"></i> Orders</a>
                      <a href="about.html"><i class="fas fa-cog"></i> About Us</a>
            <a href="#" class="logout-btn" id="logout-btn">
              <i class="fas fa-sign-out-alt"></i> Logout
            </a>
          </div>
        </div>
      </div>
    </div>
  </nav>

  <!-- Main Content -->
  <div class="container mx-auto px-4 py-8 max-w-6xl">
    <!-- User Profile Header -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6 card-hover">
      <div class="flex flex-col md:flex-row items-start md:items-center">
        <div class="flex-shrink-0 mb-4 md:mb-0">
          <div class="w-24 h-24 bg-gradient-to-r from-red-400 to-pink-500 rounded-full flex items-center justify-center text-white text-4xl font-bold">
            <?= substr(htmlspecialchars($loggedInUser), 0, 1) ?>
          </div>
        </div>
        <div class="md:ml-6 flex-grow">
          <h1 class="text-2xl font-semibold text-gray-800 mb-2">Welcome back, <?= htmlspecialchars($loggedInUser) ?></h1>
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-gray-50 p-3 rounded-lg">
              <div class="text-sm text-gray-500">Email</div>
              <div class="font-medium"><?= htmlspecialchars($userData['email'] ?? '-') ?></div>
            </div>
            <div class="bg-gray-50 p-3 rounded-lg">
              <div class="text-sm text-gray-500">Phone</div>
              <div class="font-medium"><?= htmlspecialchars($userData['phone'] ?? '-') ?></div>
            </div>
            <div class="bg-gray-50 p-3 rounded-lg">
              <div class="text-sm text-gray-500">Member Since</div>
              <div class="font-medium"><?= isset($userData['created_at']) ? date('F Y', strtotime($userData['created_at'])) : '-' ?></div>
            </div>
            <div class="bg-gray-50 p-3 rounded-lg">
              <div class="text-sm text-gray-500">Status</div>
              <div class="font-medium text-green-600">Active</div>
            </div>
          </div>
        </div>
       <div class="mt-4 md:mt-0">
  <button onclick="window.location.href='edit-profile.php'" 
    class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-md transition duration-300">
    Edit Profile
  </button>
</div>
      </div>
    </div>

    <!-- Recent Activity -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
      <h2 class="text-xl font-semibold text-gray-800 mb-4 flex items-center">
        <i class="fas fa-chart-line text-red-500 mr-2"></i>
        Recent Activity
      </h2>
      <div class="space-y-4">
        <?php 
        // Reset activity result pointer
        $activityResult->data_seek(0);
        while($row = $activityResult->fetch_assoc()): ?>
          <div class="flex items-center p-3 bg-gray-50 rounded-lg">
            <div class="w-10 h-10 bg-red-100 rounded-full flex items-center justify-center mr-4">
              <i class="fas fa-box text-red-500"></i>
            </div>
            <div class="flex-grow">
              <div class="font-medium">Order Placed</div>
              <div class="text-sm text-gray-500">Service: <?= htmlspecialchars($row['service_details']) ?></div>
            </div>
            <div class="text-sm text-gray-500"><?= date('M j, Y g:i A', strtotime($row['created_at'])) ?></div>
          </div>
        <?php endwhile; ?>
        
        <?php 
        // Reset activity result pointer again
        $activityResult->data_seek(0);
        while($row = $activityResult->fetch_assoc()): ?>
          <div class="flex items-center p-3 bg-gray-50 rounded-lg">
            <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center mr-4">
              <i class="fas fa-map-marker-alt text-blue-500"></i>
            </div>
            <div class="flex-grow">
              <div class="font-medium">Address Updated</div>
              <div class="text-sm text-gray-500">New location: <?= htmlspecialchars($row['detected_location']) ?></div>
            </div>
            <div class="text-sm text-gray-500"><?= date('M j, Y g:i A', strtotime($row['created_at'])) ?></div>
          </div>
        <?php endwhile; ?>
        
        <?php 
        // Reset activity result pointer one more time
        $activityResult->data_seek(0);
        while($row = $activityResult->fetch_assoc()): ?>
          <div class="flex items-center p-3 bg-gray-50 rounded-lg">
            <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center mr-4">
              <i class="fas fa-phone text-green-500"></i>
            </div>
            <div class="flex-grow">
              <div class="font-medium">Contact Information Updated</div>
              <div class="text-sm text-gray-500">Phone: <?= htmlspecialchars($row['phone']) ?></div>
            </div>
            <div class="text-sm text-gray-500"><?= date('M j, Y g:i A', strtotime($row['created_at'])) ?></div>
          </div>
        <?php endwhile; ?>
      </div>
    </div>

    <!-- Bookings Section -->
    <div>
      <h2 class="text-xl font-semibold text-gray-800 mb-4 flex items-center">
        <i class="fas fa-calendar-check text-red-500 mr-2"></i>
        Your Bookings
      </h2>
      
      <?php 
      // Reset main result pointer
      $result->data_seek(0);
      if ($result->num_rows > 0): 
        while($row = $result->fetch_assoc()): ?>
          <div class="bg-white rounded-lg shadow-md overflow-hidden mb-4 border-l-4 border-red-500 card-hover">
            <div class="p-6">
              <div class="flex flex-col md:flex-row justify-between mb-4">
                <div>
                  <h3 class="text-lg font-semibold text-gray-800 mb-1">Booking #<?= $row['id'] ?></h3>
                  <p class="text-sm text-gray-500">Placed on <?= date('M j, Y', strtotime($row['created_at'])) ?></p>
                </div>
                <div class="mt-2 md:mt-0 text-right">
                  <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                    <i class="fas fa-check-circle mr-1"></i>
                    Confirmed
                  </span>
                </div>
              </div>
              
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                  <div class="text-sm text-gray-500 mb-1">Service</div>
                  <div class="font-medium"><?= htmlspecialchars($row['service_details']) ?></div>
                </div>
                <div>
                  <div class="text-sm text-gray-500 mb-1">Date & Time</div>
                  <div class="font-medium"><?= htmlspecialchars($row['booking_date']) ?> at <?= htmlspecialchars($row['booking_time']) ?></div>
                </div>
                <div>
                  <div class="text-sm text-gray-500 mb-1">Location</div>
                  <div class="font-medium"><?= htmlspecialchars($row['detected_location']) ?></div>
                </div>
                <div>
                  <div class="text-sm text-gray-500 mb-1">Amount</div>
                  <div class="font-medium text-green-600">₹<?= number_format(htmlspecialchars($row['total_amount']), 2) ?></div>
                </div>
              </div>
              
              <div class="border-t border-gray-200 pt-4">
                <div class="flex flex-wrap gap-2 mb-3">
                  <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                    <i class="fas fa-truck mr-1"></i>
                    Delivery
                  </span>
                  <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                    <i class="fas fa-clock mr-1"></i>
                    Scheduled
                  </span>
                  <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                    <i class="fas fa-star mr-1"></i>
                    Premium Service
                  </span>
                </div>
                
                <div class="flex justify-between items-center">
                  <div>
                    <div class="text-sm text-gray-500">Address</div>
                    <div class="text-sm"><?= htmlspecialchars($row['flat_no']) ?>, <?= htmlspecialchars($row['street']) ?><br>
                      <?= htmlspecialchars($row['landmark']) ?><br>
                      <?= htmlspecialchars($row['pincode']) ?></div>
                  </div>
                  <div class="text-right">
                  <a href="booking-details.php?id=<?= $row['id'] ?>" 
   class="text-red-500 hover:text-red-700 text-sm font-medium">
   View Details
</a>
                  </div>
<div class="text-right space-x-2">
  <!-- Edit Button -->
  <a href="edit-booking.php?id=<?= $row['id'] ?>" 
     class="text-blue-500 hover:text-blue-700 text-sm font-medium">
     Edit
  </a>

  <!-- Cancel Button -->
  <form action="cancel_booking.php" method="POST" style="display:inline;">
    <input type="hidden" name="booking_id" value="<?= $row['id'] ?>">
    <button type="submit" 
      onclick="return confirm('Are you sure you want to cancel this booking?')" 
      class="text-red-500 hover:text-red-700 text-sm font-medium">
      Cancel
    </button>
  </form>
</div>

                </div>
              </div>
            </div>
          </div>
        <?php endwhile; ?>
      <?php else: ?>
        <div class="bg-white rounded-lg shadow-md p-8 text-center">
          <i class="fas fa-inbox text-gray-300 text-5xl mb-4"></i>
          <h3 class="text-lg font-medium text-gray-700 mb-2">No Bookings Yet</h3>
          <p class="text-gray-500 mb-6">You haven't made any bookings yet. Start exploring our services!</p>
          <button onclick="window.location.href='index.php'" 
  class="bg-red-500 hover:bg-red-600 text-white px-6 py-2 rounded-md transition duration-300">
  Browse Services
</button>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <script>
    document.addEventListener("DOMContentLoaded", () => {
      // Profile dropdown functionality
      const profileIcon = document.getElementById("profile-icon");
      const userProfile = document.getElementById("user-profile");
      
      if (profileIcon && userProfile) {
        profileIcon.addEventListener("click", (e) => {
          e.stopPropagation();
          userProfile.classList.toggle("show");
        });
        
        document.addEventListener("click", () => {
          userProfile.classList.remove("show");
        });
      }
      
      // Logout functionality
      const logoutBtn = document.getElementById("logout-btn");
      if (logoutBtn) {
        logoutBtn.addEventListener("click", function(e) {
          e.preventDefault();
          if (confirm('Are you sure you want to logout?')) {
            window.location.href = 'logout.php';
          }
        });
      }
    });
  </script>

  <?php
  $userStmt->close();
  $activityStmt->close();
  $stmt->close();
  $conn->close();
  ?>