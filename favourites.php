<?php
session_start();
require "php/dbconnection.php";

/* ---------- AUTH CHECK ---------- */
if (!isset($_SESSION['user_id'])) {
    header("Location: php/mainlogin.php");
    exit;
}

/* ---------- CART INIT ---------- */
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

$user_id = $_SESSION['user_id'];

/* ---------- FETCH FAVOURITES ---------- */
$stmt = $conn->prepare("
    SELECT p.id, p.name, p.price, p.image
    FROM favourites f
    JOIN products p ON f.product_id = p.id
    WHERE f.user_id = ?
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Your Favourites</title>

<link rel="stylesheet" href="css/htm.css">

<style>
.fav-page {
    max-width: 900px;
    margin: 40px auto;
    padding: 20px;
}

.fav-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.fav-header a {
    text-decoration: none;
    color: #ff7a00;
    font-weight: 600;
}

/* Table */
.fav-page table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 25px;
}

.fav-page th {
    text-align: left;
    padding: 14px;
    background: #f5f5f5;
    border-bottom: 1px solid #ddd;
}

.fav-page td {
    padding: 16px 14px;
    border-bottom: 1px solid #eee;
    vertical-align: middle;
}

/* Product cell */
.fav-product {
    display: flex;
    align-items: center;
    gap: 15px;
}

.fav-product img {
    width: 70px;
    border-radius: 6px;
}

/* Buttons */
.action-wrap {
    display: flex;
    gap: 10px;
}

.cart-btn {
    padding: 8px 16px;
    border-radius: 6px;
    border: none;
    background: #222;
    color: #fff;
    cursor: pointer;
    font-size: 14px;
}

.cart-btn:disabled {
    background: #aaa;
    cursor: not-allowed;
}

.remove-btn {
    padding: 8px 16px;
    border-radius: 6px;
    border: none;
    background: #ff7a00;
    color: #fff;
    cursor: pointer;
    font-size: 14px;
}

.remove-btn:hover {
    background: #e56d00;
}

.empty-msg {
    margin-top: 30px;
    font-size: 16px;
}
</style>
</head>

<body>

<main class="fav-page">

<div class="fav-header">
    <h1>Your Favourites</h1>
    <a href="ho.php">← Back to Homepage</a>
</div>

<?php if ($result->num_rows === 0): ?>

    <p class="empty-msg">You have no favourite items yet.</p>

<?php else: ?>

<table>
    <tr>
        <th>Product</th>
        <th>Price</th>
        <th>Action</th>
    </tr>

    <?php while ($row = $result->fetch_assoc()): ?>
    <?php $inCart = isset($_SESSION['cart'][$row['id']]); ?>

    <tr>
        <td>
            <div class="fav-product">
                <img src="uploads/<?= htmlspecialchars($row['image']) ?>" alt="">
                <span><?= htmlspecialchars($row['name']) ?></span>
            </div>
        </td>

        <td>Rs. <?= number_format($row['price'], 2) ?></td>

        <td>
            <div class="action-wrap">

                <!-- ADD TO CART -->
                <form method="POST" action="add_to_cart.php">
                    <input type="hidden" name="product_id" value="<?= $row['id'] ?>">
                    <button type="submit" class="cart-btn" <?= $inCart ? 'disabled' : '' ?>>
                        <?= $inCart ? 'In Cart' : 'Add to Cart' ?>
                    </button>
                </form>

                <!-- REMOVE FAVOURITE -->
                <form method="POST" action="toggle_favourite.php">
                    <input type="hidden" name="product_id" value="<?= $row['id'] ?>">
                    <button type="submit" class="remove-btn">Remove</button>
                </form>

            </div>
        </td>
    </tr>
    <?php endwhile; ?>
</table>

<?php endif; ?>

</main>

</body>
</html>
