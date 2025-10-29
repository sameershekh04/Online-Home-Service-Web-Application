<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// DB credentials
$host = "localhost";
$user = "root";
$pass = "Sameer@123";
$db = "services_app";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
  die("Database connection failed: " . $conn->connect_error);
}

// Sanitize inputs
$action = $_POST['action'] ?? '';
$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';
$email = trim($_POST['email'] ?? '');

if ($action === "login") {
  $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
  $stmt->bind_param("s", $username);
  $stmt->execute();
  $res = $stmt->get_result();

  if ($row = $res->fetch_assoc()) {
   if (password_verify($password, $row['password'])) {
    $_SESSION['username'] = $row['username']; 
    $_SESSION['email'] = $row['email'];   // ✅ Add this line
    header("Location: welcome.php");
    exit();
}
 else {
      echo "<script>alert('Incorrect password'); window.history.back();</script>";
    }
  } else {
    echo "<script>alert('User not found'); window.history.back();</script>";
  }

} elseif ($action === "signup") {
  $check = $conn->prepare("SELECT id FROM users WHERE username = ?");
  $check->bind_param("s", $username);
  $check->execute();
  $checkResult = $check->get_result();

  if ($checkResult->num_rows > 0) {
    echo "<script>alert('Username already exists. Please choose another.'); window.history.back();</script>";
  } else {
    $hashed = password_hash($password, PASSWORD_BCRYPT);
    
    // ✅ Correct query with email included
    $stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $username, $email, $hashed);

    if ($stmt->execute()) {
      echo "<script>alert('Signup successful! Please login.'); window.location.href='user-login1.html';</script>";
    } else {
      echo "<script>alert('Signup failed. Try again.'); window.history.back();</script>";
    }
  }

} elseif ($action === "forgot") {
  $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
  $stmt->bind_param("s", $username);
  $stmt->execute();
  $res = $stmt->get_result();

  if ($res->num_rows > 0) {
    echo "<script>alert('Password recovery not implemented. User found.'); window.history.back();</script>";
  } else {
    echo "<script>alert('Username not found.'); window.history.back();</script>";
  }

} else {
  echo "<script>alert('Invalid form action.'); window.history.back();</script>";
}

$conn->close();
?>
