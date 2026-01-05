<?php
session_start();
require "../php/dbconnection.php";

if (!isset($_SESSION['provider_id'])) {
    header("Location: providersignup.php");
    exit;
}

$provider_id = (int) $_SESSION['provider_id'];

/* FETCH PROVIDER PRODUCTS */
$stmt = $conn->prepare("
    SELECT 
        name,
        image,
        price,
        status,
        created_at
    FROM products
    WHERE provider_id = ?
    ORDER BY created_at DESC
");
$stmt->bind_param("i", $provider_id);
$stmt->execute();
$products = $stmt->get_result();
$stmt->close();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Product Submission History</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
            background: #f6f7f9;
        }
        table {
            border-collapse: collapse;
            width: 100%;
            background: #fff;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
            vertical-align: middle;
        }
        th {
            background: #f1f1f1;
        }
        img {
            width: 70px;
            height: 70px;
            object-fit: cover;
            border-radius: 8px;
        }
        .pending { color: orange; font-weight: 600; }
        .approved { color: green; font-weight: 600; }
        .rejected { color: red; font-weight: 600; }
    </style>
</head>
<body>

<h2>Your Product Submission History</h2>

<table>
    <tr>
        <th>Image</th>
        <th>Product Name</th>
        <th>Price (Rs.)</th>
        <th>Status</th>
        <th>Submitted On</th>
    </tr>

    <?php if ($products->num_rows === 0): ?>
        <tr>
            <td colspan="5">No products submitted yet.</td>
        </tr>
    <?php endif; ?>

    <?php while ($p = $products->fetch_assoc()): ?>
        <tr>
            <td>
                <img src="../uploads/<?= htmlspecialchars($p['image']); ?>" alt="Product Image">
            </td>
            <td><?= htmlspecialchars($p['name']); ?></td>
            <td><?= number_format($p['price'], 2); ?></td>
            <td class="<?= htmlspecialchars($p['status']); ?>">
                <?= ucfirst($p['status']); ?>
            </td>
            <td><?= date('d M Y, H:i', strtotime($p['created_at'])); ?></td>
        </tr>
    <?php endwhile; ?>
</table>

<br>
<a href="providerdash.php">← Back to Dashboard</a>

</body>
</html>
