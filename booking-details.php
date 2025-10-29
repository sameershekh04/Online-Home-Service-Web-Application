<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// DB connection
$servername = "localhost";
$username = "root";
$password = "Sameer@123";
$dbname = "services_app";
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Database Connection Failed: " . $conn->connect_error);
}

$bookingId = $_GET['id'] ?? null;
if (!$bookingId) {
    die("Invalid booking ID");
}

$stmt = $conn->prepare("SELECT * FROM bookings WHERE id = ? AND username = ?");
$stmt->bind_param("is", $bookingId, $_SESSION['username']);
$stmt->execute();
$result = $stmt->get_result();
$booking = $result->fetch_assoc();

if (!$booking) {
    die("Booking not found or not yours");
}
?>
<html>
<head>
    <meta charset="UTF-8">
    <title>Booking Details</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css"></link>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        
        :root {
            --primary-color: #FF5722;
            --secondary-color: #212121;
            --accent-color: #FF9800;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--secondary-color);
            color: white;
        }
        
        .gradient-bg {
            background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
        }
        
        .card-shadow {
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3);
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="bg-white text-black py-4 px-6">
        <div class="container mx-auto flex justify-between items-center">
            <div class="flex items-center">
                            <h1 class="text-2xl font-bold">SpitiCare</h1>
            </div>
            <nav>
                <ul class="flex space-x-6">
                    <li><a href="index.php" class="hover:text-gray-600">Home</a></li>
                    <li><a href="#" class="hover:text-gray-600">Services</a></li>
                                       <li><a href="profile.php" class="hover:text-gray-600">Profile</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <!-- Main Content -->
    <main class="container mx-auto py-8 px-4">
        <div class="max-w-4xl mx-auto">
            <!-- Booking Card -->
            <div class="bg-gray-900 rounded-xl overflow-hidden card-shadow">
                <!-- Booking Header -->
                <div class="gradient-bg p-6">
                    <div class="flex justify-between items-center">
                        <div>
                            <h2 class="text-2xl font-bold">Booking #<?= $booking['id'] ?></h2>
                            <p class="text-orange-200 mt-1">Confirmed</p>
                        </div>
                        <div class="bg-white bg-opacity-20 p-3 rounded-full">
                            <i class="fas fa-calendar-check text-2xl"></i>
                        </div>
                    </div>
                </div>

                <!-- Booking Content -->
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        
                        <!-- Left Column -->
                        <div class="space-y-6">
                            <div class="flex items-start space-x-4">
                                <div class="bg-orange-100 p-3 rounded-full">
                                    <i class="fas fa-tools text-orange-600"></i>
                                </div>
                                <div>
                                    <p class="text-gray-400 text-sm">Service</p>
                                    <p class="text-white font-medium"><?= htmlspecialchars($booking['service_details']) ?></p>
                                </div>
                            </div>
                            
                            <div class="flex items-start space-x-4">
                                <div class="bg-orange-100 p-3 rounded-full">
                                    <i class="fas fa-map-marker-alt text-orange-600"></i>
                                </div>
                                <div>
                                    <p class="text-gray-400 text-sm">Location</p>
                                    <p class="text-white font-medium"><?= htmlspecialchars($booking['detected_location']) ?></p>
                                </div>
                            </div>
                            
                            <div class="flex items-start space-x-4">
                                <div class="bg-orange-100 p-3 rounded-full">
                                    <i class="fas fa-phone text-orange-600"></i>
                                </div>
                                <div>
                                    <p class="text-gray-400 text-sm">Contact Number</p>
                                    <p class="text-white font-medium"><?= htmlspecialchars($booking['phone']) ?></p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Right Column -->
                        <div class="space-y-6">
                            <div class="flex items-start space-x-4">
                                <div class="bg-orange-100 p-3 rounded-full">
                                    <i class="fas fa-clock text-orange-600"></i>
                                </div>
                                <div>
                                    <p class="text-gray-400 text-sm">Date & Time</p>
                                    <p class="text-white font-medium"><?= htmlspecialchars($booking['booking_date']) ?> at <?= htmlspecialchars($booking['booking_time']) ?></p>
                                </div>
                            </div>
                            
                            <div class="flex items-start space-x-4">
                                <div class="bg-orange-100 p-3 rounded-full">
                                    <i class="fas fa-dollar-sign text-orange-600"></i>
                                </div>
                                <div>
                                    <p class="text-gray-400 text-sm">Total Amount</p>
                                    <p class="text-white font-medium">₹<?= number_format($booking['total_amount'], 2) ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Address Section -->
                    <div class="mt-8 pt-6 border-t border-gray-700">
                        <div class="flex items-start space-x-4">
                            <div class="bg-orange-100 p-3 rounded-full">
                                <i class="fas fa-home text-orange-600"></i>
                            </div>
                            <div>
                                <p class="text-gray-400 text-sm mb-2">Full Address</p>
                                <p class="text-white">
                                    <?= htmlspecialchars($booking['flat_no']) ?>,
                                    <?= htmlspecialchars($booking['street']) ?>,
                                    <?= htmlspecialchars($booking['landmark']) ?>,
                                    <?= htmlspecialchars($booking['pincode']) ?>
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Action Buttons -->
                    <div class="mt-8 flex flex-col sm:flex-row gap-4">
                        <a href="profile.php" class="flex items-center justify-center bg-orange-600 hover:bg-orange-700 text-white font-medium py-3 px-6 rounded-lg transition duration-200">
                            <i class="fas fa-arrow-left mr-2"></i>
                            Back to Profile
                        </a>
                        <button onclick="printReceipt()" class="flex items-center justify-center bg-gray-800 hover:bg-gray-700 text-white font-medium py-3 px-6 rounded-lg transition duration-200 border border-gray-700">
                            <i class="fas fa-print mr-2"></i>
                            Print Receipt
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Additional Info Section -->
            <div class="mt-6 bg-gray-800 rounded-xl p-6">
                <h3 class="text-xl font-semibold mb-4">Need Assistance?</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="flex items-center space-x-4">
                       <a href="https://spiticare.app.n8n.cloud/webhook/79ccd402-58ba-4b97-ad6d-032fb320c5e9/chat" 
     target="_blank" 
     class="bg-orange-100 p-3 rounded-full hover:bg-orange-200 transition duration-300 shadow-md">
    <i class="fas fa-comments text-orange-600"></i>
  </a>                        <div>
                            <p class="font-medium">Customer Support</p>
                            <p class="text-gray-400 text-sm">Available 24/7</p>
                        </div>
                    </div>
                    <div class="flex items-center space-x-4">
                        <div class="bg-orange-100 p-3 rounded-full">
                            <i class="fas fa-comments text-orange-600"></i>
                        </div>
                        <div>
                            <p class="font-medium">Live Chat</p>
                            <p class="text-gray-400 text-sm">Instant Responses</p>
                        </div>
                    </div>
                    <div class="flex items-center space-x-4">
                        <div class="bg-orange-100 p-3 rounded-full">
                            <i class="fas fa-envelope text-orange-600"></i>
                        </div>
                        <div>
                            <p class="font-medium">Email Support</p>
                            <p class="text-gray-400 text-sm">support@spiticare.com</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script>
        // Function to print receipt
        function printReceipt() {
            window.print();
        }

        // Add smooth scrolling for better UX
        document.addEventListener('DOMContentLoaded', function() {
            const links = document.querySelectorAll('a[href^="#"]');
            links.forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    const target = document.querySelector(this.getAttribute('href'));
                    if (target) {
                        target.scrollIntoView({
                            behavior: 'smooth'
                        });
                    }
                });
            });
        });
    </script>
</body>
</html>
<?php
$stmt->close();
$conn->close();
?>
