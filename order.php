<?php
session_start();
header("Content-Type: application/json");

// Razorpay SDK include
require __DIR__ . '/razorpay-php/Razorpay.php';

use Razorpay\Api\Api;

// 🟢 Apne Razorpay Test Keys yahan daalo
$keyId     = "rzp_test_RER5sZtBiQx1eK";   // Key ID (frontend + backend dono me same)
$keySecret = "U8jVS4Bh8ksSbOxEgiQffGiT";           // ⚠️ Yahan apna actual Razorpay secret key daalo

try {
    // Session se total amount lo
    if (!isset($_SESSION['total_amount']) || $_SESSION['total_amount'] <= 0) {
        echo json_encode([
            "status"  => "error",
            "message" => "❌ Invalid or missing amount in session"
        ]);
        exit;
    }

    $total_amount = $_SESSION['total_amount'];

    // Razorpay API init
    $api = new Api($keyId, $keySecret);

    // Order create
    $orderData = [
        'receipt'         => uniqid("spiticare_"),
        'amount'          => $total_amount * 100, // paise me convert
        'currency'        => 'INR',
        'payment_capture' => 1
    ];

    $razorpayOrder = $api->order->create($orderData);

    echo json_encode($razorpayOrder->toArray());

} catch (Exception $e) {
    echo json_encode([
        "status"  => "error",
        "message" => $e->getMessage()
    ]);
}
