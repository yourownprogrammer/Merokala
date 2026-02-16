<?php
session_start();
require "dbconnection.php";

if (!isset($_SESSION['admin_id'])) {
    exit;
}

$order_id = (int)($_POST['order_id'] ?? 0);
$status   = strtolower(trim($_POST['status'] ?? ''));

if ($order_id <= 0) {
    exit;
}

if ($status !== 'approved' && $status !== 'rejected') {
    exit;
}

$stmt = $conn->prepare("UPDATE orders SET order_status = ? WHERE id = ?");
$stmt->bind_param("si", $status, $order_id);
$stmt->execute();
$stmt->close();

$msg = ($status === 'approved') ? 'approved' : 'rejected';
header("Location: admin_orders.php?msg={$msg}&order_id={$order_id}");
exit;
