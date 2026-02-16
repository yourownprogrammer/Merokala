<?php
session_start();
require "php/dbconnection.php";

/* Must be logged in */
if (!isset($_SESSION['user_id'])) {
    header("Location: php/mainlogin.php");
    exit;
}

/* FETCH LOGGED-IN USER NAME */
$user_id = $_SESSION['user_id'];
$user_name = "";

$stmtUser = $conn->prepare("SELECT name FROM users WHERE id = ?");
$stmtUser->bind_param("i", $user_id);
$stmtUser->execute();
$resultUser = $stmtUser->get_result();

if ($resultUser && $resultUser->num_rows > 0) {
    $userData = $resultUser->fetch_assoc();
    $user_name = $userData['name'];
}
$stmtUser->close();

$cart = $_SESSION['cart'] ?? [];
$success = false;
$order_id = null;
$error_msg = "";

/* HANDLE CHECKOUT */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $payment_method = $_POST['payment_method'] ?? 'cash';
    $fullname = trim($_POST['fullname'] ?? '');
    $phone    = trim($_POST['phone'] ?? '');
    $address  = trim($_POST['address'] ?? '');

    /* Validation */
    if (strlen($fullname) < 2) {
        $error_msg = "Invalid full name.";
    } elseif (!preg_match('/^[0-9]{10}$/', $phone)) {
        $error_msg = "Invalid phone number.";
    } elseif (strlen($address) < 10) {
        $error_msg = "Invalid address.";
    } elseif (empty($cart)) {
        $error_msg = "Cart is empty.";
    } else {

        /* Calculate totals */
        $subtotal = 0;
        $delivery = 0;

        foreach ($cart as $item) {
            $subtotal += $item['price'];
            $delivery += 85;
        }

        $total = $subtotal + $delivery;

        /* ================= eSewa ================= */
        if ($payment_method === 'esewa') {

            $transaction_uuid = date("YmdHis") . "_" . $user_id . "_" . uniqid();

            $stmt = $conn->prepare("
                INSERT INTO orders
                (user_id, fullname, phone, address, payment_method, subtotal, delivery, total, order_status, transaction_uuid, payment_status)
                VALUES (?, ?, ?, ?, 'esewa', ?, ?, ?, 'pending', ?, 'Pending')
            ");

            $stmt->bind_param(
                "isssddds",
                $user_id,
                $fullname,
                $phone,
                $address,
                $subtotal,
                $delivery,
                $total,
                $transaction_uuid
            );

            $stmt->execute();
            $order_id = $stmt->insert_id;
            $stmt->close();

            /* Insert order items */
            $stmtItem = $conn->prepare("
                INSERT INTO order_items (order_id, product_id, price, quantity)
                VALUES (?, ?, ?, 1)
            ");

            foreach ($cart as $product_id => $item) {
                $pid = (int)$product_id;
                $price = $item['price'];
                $stmtItem->bind_param("iid", $order_id, $pid, $price);
                $stmtItem->execute();
            }
            $stmtItem->close();

            /* eSewa TEST Credentials */
            $product_code = "EPAYTEST";
            $secret_key = "8gBm/:&EnhH.1/q";

            $signed_field_names = "total_amount,transaction_uuid,product_code";
            $signature_string = "total_amount=$total,transaction_uuid=$transaction_uuid,product_code=$product_code";
            $signature = base64_encode(hash_hmac('sha256', $signature_string, $secret_key, true));
            ?>

            <form id="esewaForm" action="https://rc-epay.esewa.com.np/api/epay/main/v2/form" method="POST">
                <input type="hidden" name="amount" value="<?= $total ?>">
                <input type="hidden" name="tax_amount" value="0">
                <input type="hidden" name="total_amount" value="<?= $total ?>">
                <input type="hidden" name="transaction_uuid" value="<?= $transaction_uuid ?>">
                <input type="hidden" name="product_code" value="<?= $product_code ?>">
                <input type="hidden" name="product_service_charge" value="0">
                <input type="hidden" name="product_delivery_charge" value="0">
                <input type="hidden" name="success_url" value="http://localhost/merokala/esewa_success.php">
                <input type="hidden" name="failure_url" value="http://localhost/merokala/esewa_failure.php">
                <input type="hidden" name="signed_field_names" value="<?= $signed_field_names ?>">
                <input type="hidden" name="signature" value="<?= $signature ?>">
            </form>

            <script>
                document.getElementById("esewaForm").submit();
            </script>

            <?php
            exit();
        }

        /* ================= CASH ================= */
        else {

            $stmt = $conn->prepare("
                INSERT INTO orders
                (user_id, fullname, phone, address, payment_method, subtotal, delivery, total, order_status, payment_status)
                VALUES (?, ?, ?, ?, 'cash', ?, ?, ?, 'pending', 'Pending')
            ");

            $stmt->bind_param(
                "isssddd",
                $user_id,
                $fullname,
                $phone,
                $address,
                $subtotal,
                $delivery,
                $total
            );

            $stmt->execute();
            $order_id = $stmt->insert_id;
            $stmt->close();

            /* Insert order items */
            $stmtItem = $conn->prepare("
                INSERT INTO order_items (order_id, product_id, price, quantity)
                VALUES (?, ?, ?, 1)
            ");

            foreach ($cart as $product_id => $item) {
                $pid = (int)$product_id;
                $price = $item['price'];
                $stmtItem->bind_param("iid", $order_id, $pid, $price);
                $stmtItem->execute();
            }
            $stmtItem->close();

            unset($_SESSION['cart']);
            $success = true;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Checkout</title>
<link rel="stylesheet" href="checkout.css">
</head>
<body>

<main class="checkout-page">

<h1>Checkout</h1>

<?php if ($success): ?>

<div class="success-box">
    <h2>Order Placed Successfully</h2>
    <p><strong>Order ID:</strong> <?= htmlspecialchars($order_id) ?></p>
    <p>Status: Pending Approval</p>
    <a href="homepage.php">Continue Shopping</a>
</div>

<?php elseif (empty($cart)): ?>

<p>Your cart is empty.</p>
<a href="homepage.php">Go back</a>

<?php else: ?>

<div class="checkout-container">

<div class="card">
<?php if ($error_msg): ?>
<div class="global-error"><?= htmlspecialchars($error_msg) ?></div>
<?php endif; ?>

<form method="POST" id="checkoutForm" novalidate>

<h2>Shipping Information</h2>

<label>Full Name</label>
<input type="text" name="fullname" id="fullname" 
       value="<?= htmlspecialchars($_POST['fullname'] ?? $user_name) ?>" readonly>

<div class="error" id="nameError">Enter valid name</div>

<label>Phone Number</label>
<input type="text" name="phone" id="phone">
<div class="error" id="phoneError">Enter valid 10-digit phone</div>

<label>Full Address</label>
<textarea name="address" id="address" rows="3"></textarea>
<div class="error" id="addressError">Address too short</div>

<h2>Payment Method</h2>

<div class="payment-options">

    <label class="payment-card">
        <input type="radio" name="payment_method" value="cash" checked>
        <div class="payment-content">
            <strong>Cash on Delivery</strong>
        </div>
    </label>

    <label class="payment-card">
        <input type="radio" name="payment_method" value="esewa">
        
        <img src="pics/esewa.png" class="payment-logo" alt="eSewa Logo">

        <div class="payment-content">
           
        </div>
    </label>

</div>
<button type="submit" class="checkout-btn" id="submitBtn" disabled>
Confirm Order
</button>

</form>
</div>

<div class="card">
<h2>Order Summary</h2>

<?php
$subtotal = 0;
$delivery = 0;
foreach ($cart as $item):
    $subtotal += $item['price'];
    $delivery += 85;
?>
<div class="summary-item">
    <span><?= htmlspecialchars($item['name']) ?></span>
    <span>Rs. <?= number_format($item['price'],2) ?></span>
</div>
<?php endforeach; ?>

<div class="summary-item">
    <span>Delivery</span>
    <span>Rs. <?= number_format($delivery,2) ?></span>
</div>

<div class="summary-item total">
    <span>Total</span>
    <span>Rs. <?= number_format($subtotal + $delivery,2) ?></span>
</div>
</div>
</div>
<?php endif; ?>
</main>
<script src="checkout.js"></script>
</body>
</html>
