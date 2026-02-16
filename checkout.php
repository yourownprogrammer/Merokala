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
$user_name = ""; // initialize to avoid undefined warning

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

    if ($payment_method !== 'cash') {
        $error_msg = "Online payment is under construction.";
    } else {

        $fullname = trim($_POST['fullname'] ?? '');
        $phone    = trim($_POST['phone'] ?? '');
        $address  = trim($_POST['address'] ?? '');
        $user_id  = $_SESSION['user_id'];

        if (strlen($fullname) < 2) {
            $error_msg = "Invalid full name.";
        } elseif (!preg_match('/^[0-9]{10}$/', $phone)) {
            $error_msg = "Invalid phone number.";
        } elseif (strlen($address) < 10) {
            $error_msg = "Invalid address.";
        } elseif (empty($cart)) {
            $error_msg = "Cart is empty.";
        } else {

            $subtotal = 0;
            $delivery = 0;

            foreach ($cart as $item) {
                $subtotal += $item['price'];
                $delivery += 85;
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

            /* INSERT ORDER ITEMS */
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
<style>

/* Apply box-sizing globally to prevent width overflow */
*, *::before, *::after {
    box-sizing: border-box;
}

body {
    background: #eaeded;
    font-family: Arial, sans-serif;
    margin: 0;
    color: #111;
    line-height: 1.4; /* Better readability */
}

.checkout-page {
    max-width: 1200px;
    margin: 15px auto;
    padding: 0 20px;
}

.checkout-container {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 20px;
    align-items: start;
}

.card {
    background: #fff;
    padding: 14px;
    border-radius: 6px;
    border: 1px solid #ddd;
    margin-bottom: 20px; /* Space between cards on mobile */
}

h1 { font-size: 20px; margin: 0 0 12px; }
h2 { font-size: 14px; margin: 10px 0 6px; }

label {
    font-size: 12px;
    margin-top: 10px;
    display: block;
    font-weight: bold; /* Helps with hierarchy */
}

input, textarea {
    width: 100%;
    padding: 7px 9px;
    margin-top: 4px;
    border: 1px solid #bbb;
    border-radius: 4px;
    font-size: 13px;
    /* Removed redundant width padding issues via global box-sizing */
}

textarea {
    min-height: 80px;
    resize: vertical;
}

input:focus, textarea:focus {
    border-color: #e77600; /* Amazon-style orange */
    outline: none;
    box-shadow: 0 0 3px 2px rgba(228, 121, 17, .5);
}

/* Error States */
.error {
    font-size: 11px;
    color: #c40000;
    margin-top: 4px;
    display: none; 
}

/* Use a helper class to show errors via JS */
.error.is-visible {
    display: block;
}

.global-error {
    background: #fff2f2;
    border: 1px solid #c40000;
    border-left-width: 4px;
    padding: 12px;
    margin-bottom: 20px;
    font-size: 13px;
    border-radius: 4px;
}

/* Order Summary Details */
.summary-item {
    display: flex;
    justify-content: space-between;
    font-size: 13px;
    margin: 6px 0;
}

.total {
    margin-top: 10px;
    padding-top: 10px;
    border-top: 1px solid #ddd;
    font-weight: bold;
    font-size: 16px;
    color: #B12704; /* Distinct price color */
}

.checkout-btn {
    width: 100%;
    padding: 10px;
    margin-top: 12px;
    background: #ffd814;
    border: 1px solid #fcd200;
    border-radius: 8px; /* Slightly rounder for modern feel */
    font-size: 14px;
    cursor: pointer;
    transition: background 0.2s;
}

.checkout-btn:hover:not(:disabled) {
    background: #f7ca00;
    border-color: #f2c200;
}

.checkout-btn:active {
    background: #f0c14b;
    box-shadow: inset 0 1px 3px rgba(0,0,0,0.1);
}

.checkout-btn:disabled {
    background: #f7f8f8;
    border-color: #e7e9ec;
    color: #8d9091;
    cursor: not-allowed;
}

/* Payment Layout */
.payment-options {
    display: flex;
    flex-direction: column;
    gap: 8px;
    margin-top: 10px;
}

.payment-card {
    display: flex;
    align-items: center; /* Vertically centers everything in the row */
    gap: 12px;
    padding: 12px;
    border: 1px solid #ddd;
    border-radius: 8px;
    background: #fff;
    cursor: pointer;
    transition: all 0.2s ease;
}

/* 1. Fix the Radio Button Alignment */
.payment-card input[type="radio"] {
    margin: 0; /* Removes default browser margins that cause offset */
    width: 18px;
    height: 18px;
    cursor: pointer;
    flex-shrink: 0; /* Prevents the radio from squishing */
}

/* 2. Make the Logo Readable */
.payment-logo {
    height: 36px; /* Increased from 24px for better visibility */
    width: auto;
    object-fit: contain;
    flex-shrink: 0;
}

/* 3. Style the Text Label next to the logo */
.payment-text {
    font-size: 14px;
    font-weight: 500;
    color: #333;
    flex-grow: 1; /* Pushes everything else if needed */
}

/* 4. Visual feedback when hovering/selecting */
.payment-card:hover {
    border-color: #f90;
    background-color: #fffaf5;
}
.payment-card:hover {
    border-color: #f90;
    background: #fef8f2;
}

/* Sticky behavior fix */
.checkout-container .card-summary {
    position: -webkit-sticky; /* Safari support */
    position: sticky;
    top: 20px;
}

/* Responsive adjustments */
@media (max-width: 900px) {
    .checkout-container {
        grid-template-columns: 1fr;
    }
    .checkout-container .card-summary {
        position: static; /* Remove sticky on mobile for better flow */
    }
}
</style>

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
<<script>
const nameInput = document.getElementById('fullname');
const phoneInput = document.getElementById('phone');
const addressInput = document.getElementById('address');
const submitBtn = document.getElementById('submitBtn');

const nameError = document.getElementById('nameError');
const phoneError = document.getElementById('phoneError');
const addressError = document.getElementById('addressError');

function validateForm(){
    let valid = true;

    // Name validation
    if(nameInput.value.trim().length < 2){
        nameError.innerText = "Enter valid name";
        nameError.style.display = "block";
        valid = false;
    } else {
        nameError.style.display = "none";
    }
    // Nepali phone validation (97/98 + 8 digits)
    const phoneValue = phoneInput.value.trim();

    if (phoneValue === "") {
        phoneError.innerText = "Phone number is required";
        phoneError.style.display = "block";
        valid = false;

    } else if (!/^[0-9]+$/.test(phoneValue)) {
        phoneError.innerText = "Only numbers allowed";
        phoneError.style.display = "block";
        valid = false;

    } else if (!/^(97|98)/.test(phoneValue)) {
        phoneError.innerText = "Must start with 97 or 98";
        phoneError.style.display = "block";
        valid = false;

    } else if (phoneValue.length !== 10) {
        phoneError.innerText = "Must be exactly 10 digits";
        phoneError.style.display = "block";
        valid = false;

    } else {
        phoneError.style.display = "none";
    }
    // Address validation
    if(addressInput.value.trim().length < 10){
        addressError.innerText = "Address must be at least 10 characters";
        addressError.style.display = "block";
        valid = false;
    } else {
        addressError.style.display = "none";
    }
    submitBtn.disabled = !valid;
}
if(nameInput){
    nameInput.addEventListener('input', validateForm);
    phoneInput.addEventListener('input', validateForm);
    addressInput.addEventListener('input', validateForm);
}
</script>
</body>
</html>
