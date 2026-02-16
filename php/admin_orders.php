<?php
session_start();
require "dbconnection.php";

if (!isset($_SESSION['admin_id'])) {
    header("Location: adminlogin.php");
    exit;
}

$flashMsg = '';
$flashOrderId = (int)($_GET['order_id'] ?? 0);
$flashType = $_GET['msg'] ?? '';
if ($flashOrderId > 0 && in_array($flashType, ['approved', 'rejected'])) {
    $verb = ($flashType === 'approved') ? 'approved' : 'rejected';
    $flashMsg = "Order #{$flashOrderId} {$verb} successfully.";
}

$result = $conn->query("
    SELECT id, user_id, total, order_status
    FROM orders
    ORDER BY id DESC
");
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Manage Orders</title>
<style>
table { width:100%; border-collapse:collapse; }
th, td { padding:10px; border:1px solid #ddd; }
button { padding:6px 12px; margin-right:6px; cursor:pointer; }
.msg-success { background:#d4edda; color:#155724; padding:12px 16px; border-radius:6px; margin-bottom:20px; }
.msg-success span { font-weight:600; }
.action-done { color:#28a745; font-weight:600; }
.action-done-rejected { color:#dc3545; font-weight:600; }
</style>
</head>
<body>

<h2>Orders</h2>

<?php if ($flashMsg): ?>
<div class="msg-success"><span><?= htmlspecialchars($flashMsg) ?></span></div>
<?php endif; ?>

<table>
<tr>
    <th>Order ID</th>
    <th>User</th>
    <th>Total</th>
    <th>Status</th>
    <th>Action</th>
</tr>

<?php while ($row = $result->fetch_assoc()): ?>

<?php
$status = strtolower(trim($row['order_status'] ?? ''));
if ($status === '') {
    $status = 'pending';
}
?>

<tr>
    <td><?= $row['id'] ?></td>
    <td>User #<?= $row['user_id'] ?></td>
    <td>Rs. <?= number_format($row['total'], 2) ?></td>
    <td><?= ucfirst($status) ?></td>

    <td>
        <?php if ($status === 'pending'): ?>
            <form method="POST" action="update_order_status.php">
                <input type="hidden" name="order_id" value="<?= $row['id'] ?>">
                <button type="submit" name="status" value="approved">Accept</button>
                <button type="submit" name="status" value="rejected">Reject</button>
            </form>
        <?php else: ?>
            <span class="<?= $status === 'rejected' ? 'action-done-rejected' : 'action-done' ?>">Action completed</span>
        <?php endif; ?>
    </td>
</tr>

<?php endwhile; ?>
</table>

</body>
</html>
