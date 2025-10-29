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

// Check login
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['booking_id'])) {
    $bookingId = intval($_POST['booking_id']);
    $loggedInUser = $_SESSION['username'];

    // Ensure booking belongs to this user
    $stmt = $conn->prepare("DELETE FROM bookings WHERE id = ? AND username = ?");
    $stmt->bind_param("is", $bookingId, $loggedInUser);

    if ($stmt->execute()) {
        $_SESSION['message'] = "Booking cancelled successfully.";
    } else {
        $_SESSION['message'] = "Error cancelling booking.";
    }

    $stmt->close();
}

$conn->close();
header("Location: profile.php"); // redirect back to profile page
exit();
?>
