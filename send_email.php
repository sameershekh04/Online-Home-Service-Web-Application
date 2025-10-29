<?php
session_start();
// OpenSSl एक्सटेंशन चेक
if (!extension_loaded('openssl')) {
    die(json_encode(["status" => "error", "message" => "OpenSSL extension is not loaded"]));
}
require 'phpmailer/src/PHPMailer.php';
require 'phpmailer/src/SMTP.php';
require 'phpmailer/src/Exception.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if (!isset($_SESSION['username']) || !isset($_SESSION['email'])) {
    echo json_encode(["status" => "error", "message" => "User not logged in"]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["status" => "error", "message" => "Invalid request method"]);
    exit;
}

// फॉर्म डेटा लें
$username = $_SESSION['username'];
$email = $_SESSION['email'];
$service_details = $_POST['service_details'] ?? "Not provided";
$booking_date = $_POST['booking_date'] ?? "Not provided";
$booking_time = $_POST['booking_time'] ?? "Not provided";
$flat_no = $_POST['flat_no'] ?? "";
$street = $_POST['street'] ?? "";
$landmark = $_POST['landmark'] ?? "";
$pincode = $_POST['pincode'] ?? "";
$instructions = $_POST['instructions'] ?? "";
$total_amount = $_POST['total_amount'] ?? "0";

$mail = new PHPMailer(true);
try {
    // SMTP सेटिंग्स
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'shekhsameer9340@gmail.com';
    $mail->Password = 'ktfc rfmi yggn votj'; // नया ऐप पासवर्ड
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;

    // भेजने वाला और प्राप्तकर्ता
    $mail->setFrom("shekhsameer9340@gmail.com", "SpitiCare");
    $mail->addAddress($email, $username);

    // ईमेल सामग्री
    $mail->isHTML(true);
    $mail->Subject = "✅ Booking Confirmed - SpitiCare";
    
    // Enhanced email template
    $mail->Body = '
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title>Booking Confirmation</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                line-height: 1.6;
                color: #333;
                max-width: 600px;
                margin: 0 auto;
                padding: 20px;
            }
            .header {
                text-align: center;
                padding: 20px 0;
                border-bottom: 2px solid #f0f0f0;
            }
            .header h1 {
                color: #2c3e50;
                margin: 0;
            }
            .content {
                padding: 20px 0;
            }
            .booking-details {
                background-color: #f9f9f9;
                border-radius: 8px;
                padding: 20px;
                margin: 20px 0;
            }
            .detail-row {
                margin-bottom: 12px;
                display: flex;
            }
            .detail-label {
                font-weight: bold;
                min-width: 120px;
                color: #34495e;
            }
            .detail-value {
                flex-grow: 1;
            }
            .footer {
                text-align: center;
                padding: 20px 0;
                border-top: 2px solid #f0f0f0;
                font-size: 14px;
                color: #7f8c8d;
            }
            .highlight {
                color: #27ae60;
                font-weight: bold;
            }
        </style>
    </head>
    <body>
        <div class="header">
            <h1>SpitiCare</h1>
            <p>Professional Home Services</p>
        </div>
        
        <div class="content">
            <h2>Booking Confirmed! 🎉</h2>
            <p>Dear <span class="highlight">' . htmlspecialchars($username) . '</span>,</p>
            <p>Thank you for choosing SpitiCare! Your service has been successfully booked. Here are your booking details:</p>
            
            <div class="booking-details">
                <div class="detail-row">
                    <div class="detail-label">Service:</div>
                    <div class="detail-value">' . htmlspecialchars($service_details) . '</div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Date:</div>
                    <div class="detail-value">' . htmlspecialchars($booking_date) . '</div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Time:</div>
                    <div class="detail-value">' . htmlspecialchars($booking_time) . '</div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Address:</div>
                    <div class="detail-value">
                        ' . htmlspecialchars($flat_no) . ', ' . 
                        htmlspecialchars($street) . ', ' . 
                        htmlspecialchars($landmark) . ', ' . 
                        htmlspecialchars($pincode) . '
                    </div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Total Amount:</div>
                    <div class="detail-value"><strong>₹' . htmlspecialchars($total_amount) . '</strong></div>
                </div>
                ' . (!empty($instructions) ? '
                <div class="detail-row">
                    <div class="detail-label">Instructions:</div>
                    <div class="detail-value">' . htmlspecialchars($instructions) . '</div>
                </div>' : '') . '
            </div>
            
            <p>Please ensure someone is available at the provided address during the scheduled time. Our service professional will contact you 30 minutes before arrival.</p>
            
            <p>Need to make changes? Reply to this email or call our support at <strong>+91-9340988525</strong>.</p>
        </div>
        
        <div class="footer">
            <p>SpitiCare - Professional Home Services</p>
            <p>© ' . date('Y') . ' SpitiCare. All rights reserved.</p>
            <p>This is an automated confirmation email. Please do not reply to this message.</p>
        </div>
    </body>
    </html>
    ';

    $mail->send();
    echo json_encode(["status" => "success", "message" => "Email sent successfully"]);
} catch (Exception $e) {
    error_log("Mailer Error: " . $mail->ErrorInfo);
    echo json_encode(["status" => "error", "message" => "Mailer Error: " . $mail->ErrorInfo]);
}
?>