<?php
session_start();

if (!isset($_POST['total'])) {
    echo "❌ No amount received.";
    exit;
}

// JS already sent final total
$_SESSION['total_amount'] = (int)$_POST['total'];

echo "ok";
