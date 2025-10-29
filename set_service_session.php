<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $serviceType = $_POST['service_type'];
    $_SESSION['service_type'] = $serviceType;
    echo json_encode(['status' => 'success']);
} else {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
}
exit;
?>