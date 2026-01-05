<?php
session_start();
require "php/dbconnection.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: php/mainlogin.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$product_id = (int)($_POST['product_id'] ?? 0);

if ($product_id <= 0) {
    header("Location: homepage.php");
    exit;
}

/* Check if exists */
$stmt = $conn->prepare("
    SELECT id FROM favourites
    WHERE user_id = ? AND product_id = ?
");
$stmt->bind_param("ii", $user_id, $product_id);
$stmt->execute();
$exists = $stmt->get_result()->num_rows;
$stmt->close();

if ($exists) {
    $stmt = $conn->prepare("
        DELETE FROM favourites
        WHERE user_id = ? AND product_id = ?
    ");
} else {
    $stmt = $conn->prepare("
        INSERT INTO favourites (user_id, product_id)
        VALUES (?, ?)
    ");
}

$stmt->bind_param("ii", $user_id, $product_id);
$stmt->execute();
$stmt->close();

header("Location: " . ($_SERVER['HTTP_REFERER'] ?? 'homepage.php'));
exit;
