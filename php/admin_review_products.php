<?php
session_start();
require "../php/dbconnection.php";

/* ================= ADMIN AUTH ================= */
if (!isset($_SESSION['admin_id'])) {
    header("Location: adminlogin.php");
    exit;
}

/* ================= HANDLE ACTION ================= */
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $product_id = (int) $_POST['product_id'];
    $action = $_POST['action'];

    if ($action === 'approve') {
        $status = 'approved';
    } elseif ($action === 'reject') {
        $status = 'rejected';
    }

    if (isset($status)) {
        $stmt = $conn->prepare("UPDATE products SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $status, $product_id);
        $stmt->execute();
        $stmt->close();
    }

    header("Location: admin_review_products.php");
    exit;
}

/* ================= FETCH PENDING ================= */
$pending = $conn->query("
    SELECT p.*, pr.first_name, pr.last_name, pr.email
    FROM products p
    JOIN providers pr ON pr.id = p.provider_id
    WHERE p.status = 'pending'
    ORDER BY p.created_at DESC
");

/* ================= FETCH HISTORY ================= */
$history = $conn->query("
    SELECT p.*, pr.first_name, pr.last_name, pr.email
    FROM products p
    JOIN providers pr ON pr.id = p.provider_id
    WHERE p.status IN ('approved','rejected')
    ORDER BY p.created_at DESC
");
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Admin – Product Reviews</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<style>
body {
    font-family: Arial, sans-serif;
    background: #f4f4f4;
    margin: 0;
    padding: 30px;
}

h1 { margin-bottom: 10px; }
h2 { margin-top: 40px; }

.card {
    background: #fff;
    padding: 20px;
    margin-bottom: 20px;
    border-radius: 10px;
}

.card img {
    width: 150px;
    height: 150px;
    object-fit: cover;
    border-radius: 8px;
    border: 1px solid #ddd;
}

button {
    padding: 8px 16px;
    margin-right: 8px;
    border-radius: 6px;
    border: none;
    cursor: pointer;
    font-weight: bold;
}

.approve { background: #28a745; color: #fff; }
.reject { background: #dc3545; color: #fff; }

table {
    width: 100%;
    border-collapse: collapse;
    background: #fff;
}

th, td {
    padding: 12px;
    border-bottom: 1px solid #ddd;
    font-size: 14px;
    text-align: left;
}

th {
    background: #f8f8f8;
}

.status-approved { color: green; font-weight: bold; }
.status-rejected { color: red; font-weight: bold; }

.history-img {
    width: 70px;
    height: 70px;
    object-fit: cover;
    border-radius: 6px;
    transition: transform 0.2s;
}

.history-img:hover {
    transform: scale(2);
    z-index: 5;
}

.empty {
    background: #fff;
    padding: 30px;
    text-align: center;
    border-radius: 8px;
}
</style>
</head>

<body>

<h1>Admin Product Moderation</h1>

<!-- ================= PENDING ================= -->
<h2>Pending Product Reviews</h2>

<?php if ($pending->num_rows === 0): ?>
    <div class="empty">No pending submissions.</div>
<?php endif; ?>

<?php while ($p = $pending->fetch_assoc()): ?>
<div class="card">
    <h3><?php echo htmlspecialchars($p['name']); ?></h3>

    <p>
        <strong>Provider:</strong>
        <?php echo htmlspecialchars($p['first_name']." ".$p['last_name']); ?>
        (<?php echo htmlspecialchars($p['email']); ?>)
    </p>

    <p><strong>Price:</strong> Rs. <?php echo number_format($p['price'],2); ?></p>

    <p><?php echo nl2br(htmlspecialchars($p['description'])); ?></p>

    <img src="../uploads/<?php echo htmlspecialchars($p['image']); ?>" alt="Product Image">

    <form method="POST" style="margin-top:12px;">
        <input type="hidden" name="product_id" value="<?php echo $p['id']; ?>">
        <button class="approve" name="action" value="approve">Approve</button>
        <button class="reject" name="action" value="reject">Reject</button>
    </form>
</div>
<?php endwhile; ?>

<!-- ================= HISTORY ================= -->
<h2>Review History</h2>

<?php if ($history->num_rows === 0): ?>
    <div class="empty">No reviewed products yet.</div>
<?php else: ?>

<table>
<tr>
    <th>Image</th>
    <th>Product</th>
    <th>Provider</th>
    <th>Price</th>
    <th>Status</th>
    <th>Date</th>
</tr>

<?php while ($h = $history->fetch_assoc()): ?>
<tr>
    <td>
        <img class="history-img"
             src="../uploads/<?php echo htmlspecialchars($h['image']); ?>"
             alt="Product">
    </td>

    <td><?php echo htmlspecialchars($h['name']); ?></td>

    <td>
        <?php echo htmlspecialchars($h['first_name']." ".$h['last_name']); ?><br>
        <small><?php echo htmlspecialchars($h['email']); ?></small>
    </td>

    <td>Rs. <?php echo number_format($h['price'],2); ?></td>

    <td class="status-<?php echo $h['status']; ?>">
        <?php echo ucfirst($h['status']); ?>
    </td>

    <td><?php echo date("d M Y, h:i A", strtotime($h['created_at'])); ?></td>
</tr>
<?php endwhile; ?>
</table>

<?php endif; ?>

</body>
</html>
