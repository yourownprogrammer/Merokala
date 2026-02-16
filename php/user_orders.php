<?php
session_start();
require "../php/dbconnection.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: mainlogin.php");
    exit;
}

$user_id = $_SESSION['user_id'];

/* FETCH USER ORDERS */
$stmt = $conn->prepare("
    SELECT id, order_status, created_at, subtotal, delivery, total
    FROM orders
    WHERE user_id = ?
    ORDER BY created_at DESC
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$orders = $stmt->get_result();
$stmt->close();
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>My Orders</title>

<style>
* { box-sizing:border-box; font-family:Arial; }
body { margin:0; background:#f4f6f9; }

.header {
    background:#fff;
    border-bottom:1px solid #eee;
}
.header-inner {
    max-width:1200px;
    margin:auto;
    padding:14px 30px;
    display:flex;
    justify-content:space-between;
}
.logo {
    font-size:26px;
    font-weight:700;
    color:#ff7a00;
    text-decoration:none;
}
.logout { color:#ff7a00; text-decoration:none; }

.container {
    max-width:800px;
    margin:60px auto;
    padding:0 20px;
}

.order-card {
    background:#fff;
    padding:20px;
    border-radius:10px;
    box-shadow:0 10px 30px rgba(0,0,0,0.08);
    margin-bottom:20px;
}

.order-item {
    display:flex;
    align-items:center;
    gap:10px;
    margin-top:10px;
}

.order-item img {
    width:50px;
    height:50px;
    object-fit:cover;
    border-radius:6px;
}

.status-delivered { color:#207544; }
.status-shipped { color:#0066cc; }
.status-cancelled { color:#b3261e; }
.status-pending { color:#ff7a00; }

.back-link {
    display:inline-block;
    margin-top:20px;
    color:#0066cc;
    text-decoration:none;
}
</style>
</head>

<body>

<header class="header">
    <div class="header-inner">
        <a href="../homepage.php" class="logo">Merokala</a>
        <a href="logout.php" class="logout">Logout</a>
    </div>
</header>

<div class="container">

<h1>My Orders</h1>

<?php if ($orders->num_rows === 0): ?>
    <p>You have not placed any orders yet.</p>
<?php else: ?>

<?php while ($order = $orders->fetch_assoc()): ?>

<div class="order-card">

<strong>Order #<?= $order['id'] ?></strong><br>
<small><?= date("d M Y, h:i A", strtotime($order['created_at'])) ?></small>

<p>
Status:
<span class="status-<?= strtolower($order['order_status']) ?>">
<?= ucfirst($order['order_status']) ?>
</span>
</p>

<p>
Subtotal: Rs. <?= number_format($order['subtotal'],2) ?><br>
Delivery: Rs. <?= number_format($order['delivery'],2) ?><br>
<strong>Total: Rs. <?= number_format($order['total'],2) ?></strong>
</p>

<?php
$item_stmt = $conn->prepare("
    SELECT p.name, p.image, oi.quantity, oi.price
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    WHERE oi.order_id = ?
");
$item_stmt->bind_param("i", $order['id']);
$item_stmt->execute();
$items = $item_stmt->get_result();
?>

<?php while ($item = $items->fetch_assoc()): ?>
<div class="order-item">
    <img src="../uploads/<?= htmlspecialchars($item['image']) ?>">
    <div>
        <?= htmlspecialchars($item['name']) ?><br>
        Qty: <?= $item['quantity'] ?> |
        Rs. <?= number_format($item['price'],2) ?>
    </div>
</div>
<?php endwhile; ?>

<?php $item_stmt->close(); ?>

</div>

<?php endwhile; ?>

<?php endif; ?>

<a href="user_dashboard.php" class="back-link">← Back to Dashboard</a>

</div>

</body>
</html>
