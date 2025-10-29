<?php
$host = "localhost";
$user = "root";
$password = "Sameer@123"; // default in WAMP is blank
$database = "services_app";

$conn = new mysqli($host, $user, $password, $database);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
