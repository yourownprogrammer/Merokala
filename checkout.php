<?php
session_start();
require "php/dbconnection.php";

/* Must be logged in */
if (!isset($_SESSION['user_id'])) {
    header("Location: php/mainlogin.php");
    exit;
}

$cart = $_SESSION['cart'] ?? [];
$success = false;
$order_id = null;

/* HANDLE CHECKOUT */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $payment_method = $_POST['payment_method'] ?? 'cash';

    /* Block eSewa */
    if ($payment_method !== 'cash') {
        die("Online payment is under construction.");
    }

    /* Shipping info */
    $fullname = trim($_POST['fullname']);
    $phone    = trim($_POST['phone']);
    $address  = trim($_POST['address']);
    $user_id  = $_SESSION['user_id'];

    /* Calculate totals (qty fixed to 1) */
    $subtotal = 0;
    $delivery = 0;

    foreach ($cart as $item) {
        $subtotal += $item['price'];
        $delivery += 85; // Rs. 85 per product
    }

    $total = $subtotal + $delivery;

    /* INSERT ORDER */
    $stmt = $conn->prepare("
        INSERT INTO orders
        (user_id, fullname, phone, address, payment_method, subtotal, delivery, total, order_status)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending')
    ");

    $stmt->bind_param(
        "issssddd",
        $user_id,
        $fullname,
        $phone,
        $address,
        $payment_method,
        $subtotal,
        $delivery,
        $total
    );

    $stmt->execute();
    $order_id = $stmt->insert_id;
    $stmt->close();

    /* INSERT ORDER ITEMS (qty = 1) */
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

    /* Clear cart */
    unset($_SESSION['cart']);
    $success = true;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Checkout</title>
<link rel="stylesheet" href="css/htm.css">

<style>
.checkout-page{max-width:800px;margin:40px auto;padding:20px;}
.checkout-box{border:1px solid #ddd;padding:24px;border-radius:8px;}
label{font-weight:600;display:block;margin-top:12px;}
input,textarea{width:100%;padding:10px;margin-top:6px;border:1px solid #ccc;border-radius:4px;}
.checkout-btn{margin-top:20px;padding:12px 24px;background:#ff7a00;color:#fff;border:none;border-radius:6px;cursor:pointer;}
.checkout-btn:hover{background:#e56d00;}
.success-msg{background:#f0fff4;border:1px solid #b6e2c3;padding:24px;border-radius:6px;color:#2f855a;}
</style>
</head>

<body>
<main class="checkout-page">
<h1>Checkout</h1>

<?php if ($success): ?>

<div class="success-msg">
    <h2>Order Placed Successfully</h2>
    <p><strong>Order ID:</strong> <?= htmlspecialchars($order_id) ?></p>
    <p><strong>Status:</strong> Pending Approval</p>
    <p><strong>Payment Method:</strong> Cash on Delivery</p>
    <a href="homepage.php">Continue Shopping</a>
</div>

<?php elseif (empty($cart)): ?>

<p>Your cart is empty.</p>
<a href="homepage.php">← Go back</a>

<?php else: ?>

<div class="checkout-box">
<form method="POST">

<h2>Shipping Address</h2>

<label>Full Name</label>
<input type="text" name="fullname" required>

<label>Phone Number</label>
<input type="text" name="phone" required>

<label>Full Address</label>
<textarea name="address" rows="3" required></textarea>

<h2>Payment Method</h2>
<label><input type="radio" name="payment_method" value="cash" checked> Cash on Delivery</label>
<label><input type="radio" name="payment_method" value="esewa"> eSewa (Under Construction)</label>

<h2>Order Summary</h2>

<?php
$subtotal = 0;
$delivery = 0;
foreach ($cart as $item):
    $subtotal += $item['price'];
    $delivery += 85;
?>
<p><?= htmlspecialchars($item['name']) ?> — Rs. <?= number_format($item['price'],2) ?></p>
<?php endforeach; ?>

<p>Delivery Charge: Rs. <?= number_format($delivery,2) ?></p>
<p><strong>Total: Rs. <?= number_format($subtotal + $delivery,2) ?></strong></p>

<button type="submit" class="checkout-btn">Confirm Order</button>

</form>
</div>

<?php endif; ?>
</main>
</body>
</html>
