<?php
session_start();
require('razorpay-php/Razorpay.php');
use Razorpay\Api\Api;

$api = new Api("rzp_test_RER5sZtBiQx1eK", "U8jVS4Bh8ksSbOxEgiQffGiT");

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['razorpay_payment_id']) || !isset($data['razorpay_order_id']) || !isset($data['razorpay_signature'])) {
    echo json_encode(["status" => "error", "message" => "Invalid data"]);
    exit;
}

try {
    $attributes  = [
        'razorpay_order_id' => $data['razorpay_order_id'],
        'razorpay_payment_id' => $data['razorpay_payment_id'],
        'razorpay_signature' => $data['razorpay_signature']
    ];
    $api->utility->verifyPaymentSignature($attributes);

    echo json_encode(["status" => "success"]);
} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
