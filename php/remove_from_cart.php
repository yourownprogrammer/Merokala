<?php
session_start();

$product_id = (int)($_POST['product_id'] ?? 0);

if ($product_id > 0 && isset($_SESSION['cart'][$product_id])) {
    unset($_SESSION['cart'][$product_id]);
}

header("Location: cart.php");
exit;
