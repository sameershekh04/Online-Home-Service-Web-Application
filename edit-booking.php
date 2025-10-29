<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Booking</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css"></link>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');
        body {
            font-family: 'Poppins', sans-serif;
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">
    <?php
    session_start();
    $servername = "localhost";
    $username = "root";
    $password = "Sameer@123";
    $dbname = "services_app";
    $conn = new mysqli($servername, $username, $password, $dbname);
    if ($conn->connect_error) {
        die("Database Connection Failed: " . $conn->connect_error);
    }
    
    if (!isset($_SESSION['username'])) {
        header("Location: login.php");
        exit();
    }
    
    $loggedInUser = $_SESSION['username'];
    $bookingId = intval($_GET['id']);
    
    // Fetch booking details
    $stmt = $conn->prepare("SELECT * FROM bookings WHERE id = ? AND username = ?");
    $stmt->bind_param("is", $bookingId, $loggedInUser);
    $stmt->execute();
    $result = $stmt->get_result();
    $booking = $result->fetch_assoc();
    $stmt->close();
    
    if (!$booking) {
        die("Invalid booking or access denied!");
    }
    
    // Update booking if submitted
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $service = $_POST['service_details'];
        $date = $_POST['booking_date'];
        $time = $_POST['booking_time'];
        $location = $_POST['detected_location'];
        
        $updateStmt = $conn->prepare("UPDATE bookings 
            SET service_details = ?, booking_date = ?, booking_time = ?, detected_location = ?
            WHERE id = ? AND username = ?");
        $updateStmt->bind_param("ssssii", $service, $date, $time, $location, $bookingId, $loggedInUser);
        
        if ($updateStmt->execute()) {
            $_SESSION['message'] = "Booking updated successfully.";
            header("Location: profile.php");
            exit();
        } else {
            echo "<div class='alert alert-error text-white p-4 rounded-md mb-4'>Error updating booking.</div>";
        }
    }
    ?>
    
    <div class="container mx-auto px-4 py-8 max-w-2xl">
        <div class="bg-white rounded-lg shadow-lg overflow-hidden">
            <div class="bg-gradient-to-r from-blue-500 to-purple-600 text-white p-6">
                <div class="flex items-center justify-between">
                    <h1 class="text-2xl font-bold flex items-center">
                        <i class="fas fa-edit mr-2"></i>
                        Edit Booking #<?= $booking['id'] ?>
                    </h1>
                    <span class="bg-white bg-opacity-20 px-3 py-1 rounded-full text-sm">
                        <?= date('M d, Y', strtotime($booking['booking_date'])) ?>
                    </span>
                </div>
            </div>
            
            <div class="p-6">
                <form method="POST" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-tools mr-2"></i> Service
                            </label>
                            <input 
                                type="text" 
                                name="service_details" 
                                value="<?= htmlspecialchars($booking['service_details']) ?>" 
                                required
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200"
                            >
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="far fa-calendar-alt mr-2"></i> Date
                            </label>
                            <input 
                                type="date" 
                                name="booking_date" 
                                value="<?= htmlspecialchars($booking['booking_date']) ?>" 
                                required
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200"
                            >
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="far fa-clock mr-2"></i> Time
                            </label>
                            <input 
                                type="time" 
                                name="booking_time" 
                                value="<?= htmlspecialchars($booking['booking_time']) ?>" 
                                required
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200"
                            >
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-map-marker-alt mr-2"></i> Location
                            </label>
                            <input 
                                type="text" 
                                name="detected_location" 
                                value="<?= htmlspecialchars($booking['detected_location']) ?>" 
                                required
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200"
                            >
                        </div>
                    </div>
                    
                    <div class="pt-4">
                        <button 
                            type="submit" 
                            class="w-full bg-gradient-to-r from-blue-500 to-purple-600 text-white py-3 px-6 rounded-lg hover:from-blue-600 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition duration-200 transform hover:scale-105"
                        >
                            <i class="fas fa-save mr-2"></i> Update Booking
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Back button -->
        <div class="mt-6 text-center">
            <a href="profile.php" class="inline-flex items-center text-gray-600 hover:text-blue-600 transition duration-200">
                <i class="fas fa-arrow-left mr-2"></i> Back to Profile
            </a>
        </div>
    </div>
</body>
</html>