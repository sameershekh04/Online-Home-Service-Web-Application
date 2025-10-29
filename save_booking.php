<?php
session_start(); // Session start zaruri hai
header("Content-Type: application/json");

// DB connection
$servername = "localhost";
$db_username = "root";
$db_password = "Sameer@123";
$dbname = "services_app";

$conn = new mysqli($servername, $db_username, $db_password, $dbname);

if ($conn->connect_error) {
    echo json_encode(["status" => "error", "message" => "DB connection failed"]);
    exit;
}

// ✅ Pehle check karo user login hai ya nahi
if (!isset($_SESSION['username']) || !isset($_SESSION['email'])) {
    echo json_encode(["status" => "error", "message" => "User not logged in"]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // ✅ Insert query with service_type added
    $stmt = $conn->prepare("INSERT INTO bookings 
        (username, service_details, detected_location, flat_no, landmark, street, pincode, booking_date, booking_time, phone, email, service_type, instructions, total_amount) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    $stmt->bind_param(
        "sssssssssssssi",
        $_SESSION['username'],                  // 👈 session se username
        $_POST['service_details'], 
        $_POST['detected_location'],
        $_POST['flat_no'],
        $_POST['landmark'],
        $_POST['street'],
        $_POST['pincode'],
        $_POST['booking_date'],
        $_POST['booking_time'],
        $_POST['phone'],
        $_SESSION['email'],                     // 👈 session se email
        $_POST['service_type'],                 // ✅ new field
        $_POST['instructions'],
        $_POST['total_amount']
    );

    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Booking saved to DB"]);
    } else {
        echo json_encode(["status" => "error", "message" => $stmt->error]);
    }

    $stmt->close();
} else {
    echo json_encode(["status" => "error", "message" => "Only POST allowed"]);
}

$conn->close();
?>
