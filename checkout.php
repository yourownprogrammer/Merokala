<?php
session_start();

/* Must be logged in */
if (!isset($_SESSION['user_id'])) {
    header("Location: php/mainlogin.php");
    exit;
}

$cart = $_SESSION['cart'] ?? [];
$success = false;
$payment_method = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $payment_method = $_POST['payment_method'] ?? 'cash';

    // Future: save order + payment method to database
    unset($_SESSION['cart']); // clear cart after order
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
.checkout-page {
    max-width: 700px;
    margin: 40px auto;
    padding: 20px;
}

.checkout-box {
    border: 1px solid #ddd;
    padding: 24px;
    border-radius: 8px;
    margin-top: 20px;
}

.payment-option {
    margin: 12px 0;
}

.checkout-summary {
    margin-top: 20px;
}

.checkout-summary p {
    margin: 6px 0;
}

.checkout-btn {
    margin-top: 25px;
    padding: 12px 24px;
    background: #ff7a00;
    color: #fff;
    border: none;
    border-radius: 6px;
    font-size: 15px;
    font-weight: 600;
    cursor: pointer;
}

.checkout-btn:hover {
    background: #e56d00;
}

.success-msg {
    background: #f0fff4;
    border: 1px solid #b6e2c3;
    padding: 24px;
    border-radius: 6px;
    color: #2f855a;
}
</style>
</head>

<body>

<main class="checkout-page">

<h1>Checkout</h1>

<?php if ($success): ?>

    <div class="success-msg">
        <h2>Order Placed Successfully</h2>
        <p>
            Payment Method:
            <strong><?= htmlspecialchars(ucfirst($payment_method)) ?></strong>
        </p>

        <?php if ($payment_method === 'esewa'): ?>
            <p>
                You selected <strong>eSewa</strong>.  
                Online payment integration will be implemented in future versions.
            </p>
        <?php else: ?>
            <p>
                You selected <strong>Cash on Delivery</strong>.
            </p>
        <?php endif; ?>

        <a href="homepage.php">Continue Shopping</a>
    </div>

<?php elseif (empty($cart)): ?>

    <p>Your cart is empty.</p>
    <a href="homepage.php">← Go back</a>

<?php else: ?>

<div class="checkout-box">

    <form method="POST">

        <h2>Payment Method</h2>

        <div class="payment-option">
            <label>
                <input type="radio" name="payment_method" value="cash" checked>
                Cash on Delivery
            </label>
        </div>

        <div class="payment-option">
            <label>
                <input type="radio" name="payment_method" value="esewa">
                eSewa (Online Payment)
            </label>
        </div>

        <div class="checkout-summary">
            <h3>Order Summary</h3>

            <?php $total = 0; ?>
            <?php foreach ($cart as $item): ?>
                <?php $total += $item['price']; ?>
                <p>
                    <?= htmlspecialchars($item['name']) ?>
                    — Rs. <?= number_format($item['price'], 2) ?>
                </p>
            <?php endforeach; ?>

            <p><strong>Total: Rs. <?= number_format($total, 2) ?></strong></p>
        </div>

        <button type="submit" class="checkout-btn">
            Confirm Order
        </button>

    </form>

</div>

<?php endif; ?>

</main>

</body>
</html>
