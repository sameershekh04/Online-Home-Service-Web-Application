<?php
// Start session
session_start();
// Database configuration
$db_host = 'localhost';
$db_user = 'root';
$db_pass = "Sameer@123";
$db_name = 'services_app';
// Create database connection
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve form data
    $fullName = $_POST['fullName'];
    $email = $_POST['email'];
    $mobile = $_POST['mobile'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Hash password
    $service_type = $_POST['service_type'];
    $experience = $_POST['experience'];
   
    $LocalAddress = $_POST['LocalAddress'];
    $pincode = $_POST['pincode'];
    
    // Handle file uploads
    $govIdPath = '';
    $profilePicPath = '';
    
    // Upload directory
    $uploadDir = 'uploads/';
    
    // Create directory if it doesn't exist
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    
    // Process government ID
    if (isset($_FILES['govId']) && $_FILES['govId']['error'] == 0) {
        $govIdTmpPath = $_FILES['govId']['tmp_name'];
        $govIdFileName = time() . '_' . basename($_FILES['govId']['name']);
        $govIdPath = $uploadDir . $govIdFileName;
        
        if (!move_uploaded_file($govIdTmpPath, $govIdPath)) {
            $uploadError = "Error uploading government ID";
        }
    }
    
    // Process profile picture (optional)
    if (isset($_FILES['profilePic']) && $_FILES['profilePic']['error'] == 0) {
        $profilePicTmpPath = $_FILES['profilePic']['tmp_name'];
        $profilePicFileName = time() . '_' . basename($_FILES['profilePic']['name']);
        $profilePicPath = $uploadDir . $profilePicFileName;
        
        if (!move_uploaded_file($profilePicTmpPath, $profilePicPath)) {
            $uploadError = "Error uploading profile picture";
        }
    }
    
    // Check if email already exists
    $checkEmail = "SELECT id FROM service_providers WHERE email = ?";
    $stmt = $conn->prepare($checkEmail);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        // Email already exists
        $_SESSION['error'] = "Email address already registered";
        header("Location: serviceproviderhome.html");
        exit();
    }
    
    // Insert data into database
    $sql = "INSERT INTO service_providers (fullName, email, mobile, password, service_type, experience, LocalAddress, pincode, govId, profilePic, verified, created_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0, NOW())";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssissss", $fullName, $email, $mobile, $password, $service_type, $experience, $LocalAddress, $pincode, $govIdPath, $profilePicPath);
    
    if ($stmt->execute()) {
        // Registration successful
        $_SESSION['success'] = "Registration successful! Your account is pending verification.";
        header("Location: welcome.html");
        exit();
    } else {
        // Error occurred
        $_SESSION['error'] = "Error: " . $conn->error;
        header("Location: serviceproviderhome.html");
        exit();
    }
    
    $stmt->close();
}
$conn->close();
?>