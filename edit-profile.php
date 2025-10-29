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

// Bookings fetch - Using a NEW result object
$bookingsStmt = $conn->prepare("SELECT * FROM bookings WHERE username = ? ORDER BY id DESC");
$bookingsStmt->bind_param("s", $loggedInUser);
$bookingsStmt->execute();
$bookingsResult = $bookingsStmt->get_result();

// Handle form submission for editing profile
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_profile'])) {
    $newEmail = trim($_POST['email']);
    $newPhone = trim($_POST['phone']);
    
    // Validate inputs
    $errors = [];
    
    if (empty($newEmail)) {
        $errors[] = "Email is required.";
    } elseif (!filter_var($newEmail, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Please enter a valid email address.";
    }
    
    if (empty($newPhone)) {
        $errors[] = "Phone number is required.";
    } elseif (!preg_match('/^[0-9]{10}$/', $newPhone)) {
        $errors[] = "Please enter a valid 10-digit phone number.";
    }
    
    if (empty($errors)) {
        // Update user data
        $updateQuery = "UPDATE bookings SET email = ?, phone = ? WHERE username = ?";
        $updateStmt = $conn->prepare($updateQuery);
        $updateStmt->bind_param("sss", $newEmail, $newPhone, $loggedInUser);
        
        if ($updateStmt->execute()) {
            // Refresh user data after update
            $userResult->data_seek(0);
            $userData = $userResult->fetch_assoc();
            $successMessage = "Profile updated successfully!";
        } else {
            $errors[] = "Error updating profile: " . $conn->error;
        }
    }
}

// Close connections
$userStmt->close();
$activityStmt->close();
$bookingsStmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile - SpitiCare</title>
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
        .alert {
            padding: 1rem;
            margin-bottom: 1rem;
            border-radius: 0.5rem;
        }
        .alert-success {
            background-color: #d1fae5;
            border-color: #34d399;
            color: #065f46;
        }
        .alert-error {
            background-color: #fef2f2;
            border-color: #fecaca;
            color: #b91c1c;
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
                <span class="text-gray-700">Welcome, <?= htmlspecialchars($userData['username'] ?? '') ?></span>
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
            <a href="profile.php"><i class="fas fa-user-edit"></i> My Profile</a>
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
        <!-- Success/Error Messages -->
        <?php if (isset($successMessage)): ?>
            <div class="alert alert-success mb-6">
                <div class="flex items-center">
                    <i class="fas fa-check-circle mr-2"></i>
                    <span><?= $successMessage ?></span>
                </div>
            </div>
        <?php endif; ?>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-error mb-6">
                <div class="flex items-center">
                    <i class="fas fa-exclamation-circle mr-2"></i>
                    <span>Please correct the following errors:</span>
                </div>
                <ul class="list-disc list-inside ml-6 mt-2">
                    <?php foreach ($errors as $error): ?>
                        <li><?= $error ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <!-- Edit Profile Form -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6 card-hover">
            <h2 class="text-xl font-semibold text-gray-800 mb-6 flex items-center">
                <i class="fas fa-user-edit text-red-500 mr-2"></i>
                Edit Profile
            </h2>
            
            <form method="POST" class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email Address</label>
                        <input type="email" id="email" name="email" value="<?= htmlspecialchars($userData['email'] ?? '') ?>" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent"
                               placeholder="Enter your email address" required>
                    </div>
                    
                    <div>
                        <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">Phone Number</label>
                        <input type="tel" id="phone" name="phone" value="<?= htmlspecialchars($userData['phone'] ?? '') ?>" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent"
                               placeholder="Enter your phone number" pattern="[0-9]{10}" title="Please enter a 10-digit phone number" required>
                    </div>
                </div>
                
                <div class="flex justify-end">
                    <button type="submit" name="edit_profile" 
                            class="bg-red-500 hover:bg-red-600 text-white px-6 py-2 rounded-md transition duration-300">
                        Save Changes
                    </button>
                </div>
            </form>
        </div>

        <!-- User Profile Info -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6 card-hover">
            <h2 class="text-xl font-semibold text-gray-800 mb-6 flex items-center">
                <i class="fas fa-user text-red-500 mr-2"></i>
                Profile Information
            </h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <h3 class="text-lg font-medium text-gray-800 mb-2">Personal Details</h3>
                    <div class="space-y-4">
                        <div>
                            <span class="text-sm text-gray-500">Full Name</span>
                            <p class="font-medium"><?= htmlspecialchars($userData['username']) ?></p>
                        </div>
                        <div>
                            <span class="text-sm text-gray-500">Email Address</span>
                            <p class="font-medium"><?= htmlspecialchars($userData['email'] ?? '-') ?></p>
                        </div>
                        <div>
                            <span class="text-sm text-gray-500">Phone Number</span>
                            <p class="font-medium"><?= htmlspecialchars($userData['phone'] ?? '-') ?></p>
                        </div>
                    </div>
                </div>
                
                <div>
                    <h3 class="text-lg font-medium text-gray-800 mb-2">Account Details</h3>
                    <div class="space-y-4">
                        <div>
                            <span class="text-sm text-gray-500">Member Since</span>
                            <p class="font-medium"><?= isset($userData['created_at']) ? date('F Y', strtotime($userData['created_at'])) : '-' ?></p>
                        </div>
                        <div>
                            <span class="text-sm text-gray-500">Account Status</span>
                            <p class="font-medium text-green-600">Active</p>
                        </div>
                        <div>
                            <span class="text-sm text-gray-500">Last Login</span>
                            <p class="font-medium">Just now</p>
                        </div>
                    </div>
                </div>
            </div>
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
</body>
</html>