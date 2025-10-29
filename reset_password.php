<?php
session_start();
header("Content-Type: application/json");

// Database connection
$servername = "localhost";
$username = "root";
$password = "Sameer@123"; // apna DB password daalo
$dbname = "services_app"; // apna DB name daalo

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    echo json_encode(["status" => "error", "message" => "DB Connection Failed"]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $email    = $_POST['email'] ?? '';
    $newPass  = $_POST['new_password'] ?? '';

    if ($username === "" || $email === "" || $newPass === "") {
        echo json_encode(["status" => "error", "message" => "All fields required"]);
        exit;
    }

    // Password ko hash karke save karna (best practice)
    $hashedPassword = password_hash($newPass, PASSWORD_DEFAULT);

    $stmt = $conn->prepare("UPDATE users SET password=? WHERE username=? AND email=?");
    $stmt->bind_param("sss", $hashedPassword, $username, $email);

    if ($stmt->execute() && $stmt->affected_rows > 0) {
        echo json_encode(["status" => "success", "message" => "Password reset successful!"]);
    } else {
        echo json_encode(["status" => "error", "message" => "User not found or update failed"]);
    }

    $stmt->close();
}
$conn->close();
?>
