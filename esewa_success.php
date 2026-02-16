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
   2. Extract eSewa Data
--------------------------------*/
$transaction_code = $response['transaction_code'] ?? '';
$status           = $response['status'] ?? '';
$total_amount     = $response['total_amount'] ?? '';
$transaction_uuid = $response['transaction_uuid'] ?? '';
$product_code     = $response['product_code'] ?? '';
$signature        = $response['signature'] ?? '';
$signed_fields    = $response['signed_field_names'] ?? '';

/* -----------------------------
   3. Validate Product Code
--------------------------------*/
if ($product_code !== "EPAYTEST") {
    die("Invalid product code.");
}

/* -----------------------------
   4. Verify Signature
--------------------------------*/
$secret_key = "8gBm/:&EnhH.1/q";

$signature_string =
    "transaction_code=$transaction_code," .
    "status=$status," .
    "total_amount=$total_amount," .
    "transaction_uuid=$transaction_uuid," .
    "product_code=$product_code," .
    "signed_field_names=$signed_fields";

$generated_signature = base64_encode(
    hash_hmac('sha256', $signature_string, $secret_key, true)
);

if ($generated_signature !== $signature) {
    die("Signature verification failed.");
}

/* -----------------------------
   5. Check Order Exists
--------------------------------*/
$stmt = $conn->prepare("SELECT id, total, payment_status FROM orders WHERE transaction_uuid = ?");
$stmt->bind_param("s", $transaction_uuid);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Order not found.");
}

$order = $result->fetch_assoc();
$stmt->close();

/* -----------------------------
   6. Verify Amount
--------------------------------*/
if ((float)$order['total'] !== (float)$total_amount) {
    die("Amount mismatch.");
}

/* -----------------------------
   7. Prevent Double Processing
--------------------------------*/
if ($order['payment_status'] === 'Paid') {
    $success = true;
} else {

    if ($status === "COMPLETE") {

        $stmt = $conn->prepare("
            UPDATE orders
            SET payment_status = 'Paid',
                order_status   = 'Confirmed'
            WHERE transaction_uuid = ?
        ");
        $stmt->bind_param("s", $transaction_uuid);
        $stmt->execute();
        $stmt->close();

        unset($_SESSION['cart']);
        $success = true;

    } else {

        $stmt = $conn->prepare("
            UPDATE orders
            SET payment_status = 'Failed',
                order_status   = 'Cancelled'
            WHERE transaction_uuid = ?
        ");
        $stmt->bind_param("s", $transaction_uuid);
        $stmt->execute();
        $stmt->close();

        $success = false;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Payment Status</title>
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
        .success{color:green;}
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

<?php if ($success): ?>

    <h2 class="success">Payment Successful</h2>
    <p>Your payment has been completed successfully.</p>
    <p><strong>Transaction UUID:</strong> <?= htmlspecialchars($transaction_uuid) ?></p>
    <a href="homepage.php" class="btn">Continue Shopping</a>

<?php else: ?>

    <h2 class="fail">Payment Failed</h2>
    <p>Your payment was not successful.</p>
    <a href="checkout.php" class="btn">Try Again</a>

<?php endif; ?>

</div>

</body>
</html>
