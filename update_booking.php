<?php
session_start();

// Agar provider login nahi hai to redirect
if (!isset($_SESSION['provider_logged_in']) || $_SESSION['provider_logged_in'] !== true) {
    header("Location: provider_login.php");
    exit();
}

if (!isset($_GET['id']) || !isset($_GET['action'])) {
    header("Location: provider_bookings.php");
    exit();
}

$booking_id = intval($_GET['id']);
$action = $_GET['action'];

// Action ke hisaab se status set karna
$status = null;
if ($action === 'confirm') {
    $status = 'confirmed';
} elseif ($action === 'cancel') {
    $status = 'cancelled';
} elseif ($action === 'complete') {
    $status = 'completed';
}

if ($status) {
    // Database connection
    $conn = new mysqli("localhost", "root", "Sameer@123", "services_app");
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $stmt = $conn->prepare("UPDATE bookings SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $booking_id);
    $stmt->execute();
    $stmt->close();
    $conn->close();
}

// Update ke baad wapas bookings page pe bhej do
header("Location: provider_bookings.php");
exit();
?>
