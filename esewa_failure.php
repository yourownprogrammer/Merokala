<?php
session_start();
require "php/dbconnection.php";

/* -----------------------------
   1. Validate Access
--------------------------------*/
if (!isset($_GET['data'])) {
    die("Invalid access.");
}

$response = json_decode(base64_decode($_GET['data']), true);

if (!$response) {
    die("Invalid response.");
}

/* -----------------------------
   2. Extract Data
--------------------------------*/
$transaction_uuid = $response['transaction_uuid'] ?? '';
$status           = $response['status'] ?? '';

if (empty($transaction_uuid)) {
    die("Transaction not found.");
}

/* -----------------------------
   3. Verify Order Exists
--------------------------------*/
$stmt = $conn->prepare("SELECT id, payment_status FROM orders WHERE transaction_uuid = ?");
$stmt->bind_param("s", $transaction_uuid);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Order not found.");
}

$order = $result->fetch_assoc();
$stmt->close();

/* -----------------------------
   4. Prevent Double Processing
--------------------------------*/
if ($order['payment_status'] !== 'Paid') {

    $stmt = $conn->prepare("
        UPDATE orders
        SET payment_status = 'Failed',
            order_status   = 'Cancelled'
        WHERE transaction_uuid = ?
    ");
    $stmt->bind_param("s", $transaction_uuid);
    $stmt->execute();
    $stmt->close();
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>Payment Failed</title>
    <style>
        body{
            font-family:Arial;
            background:#f3f3f3;
            padding:40px;
        }
        .box{
            background:#fff;
            padding:30px;
            border-radius:8px;
            max-width:600px;
            margin:auto;
            box-shadow:0 0 10px rgba(0,0,0,0.08);
            text-align:center;
        }
        .fail{color:red;}
        .btn{
            display:inline-block;
            margin-top:20px;
            padding:10px 20px;
            background:#ffd814;
            text-decoration:none;
            color:#000;
            border-radius:5px;
        }
    </style>
</head>
<body>

<div class="box">

    <h2 class="fail">Payment Failed</h2>
    <p>Your payment was not successful or was cancelled.</p>
    <a href="checkout.php" class="btn">Try Again</a>

</div>

</body>
</html>
