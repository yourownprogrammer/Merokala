<?php
session_start();
require "php/dbconnection.php";

/* Must be logged in */
if (!isset($_SESSION['user_id'])) {
    header("Location: php/mainlogin.php");
    exit;
}

$product_id = (int)($_POST['product_id'] ?? 0);
if ($product_id <= 0) {
    header("Location: homepage.php");
    exit;
}

/* Fetch product */
$stmt = $conn->prepare("
    SELECT id, name, price, image
    FROM products
    WHERE id = ?
");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$product) {
    header("Location: homepage.php");
    exit;
}

/* Init cart */
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

/* Add product (no quantity logic) */
$_SESSION['cart'][$product_id] = [
    'product_id' => $product['id'],   // REQUIRED for checkout
    'name'       => $product['name'],
    'price'      => $product['price'],
    'image'      => $product['image']
];

header("Location: cart.php");
exit;
