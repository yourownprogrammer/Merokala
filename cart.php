<?php
session_start();

/* Must be logged in */
if (!isset($_SESSION['user_id'])) {
    header("Location: php/mainlogin.php");
    exit;
}

$cart = $_SESSION['cart'] ?? [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Your Cart</title>

<link rel="stylesheet" href="css/htm.css">

<style>
/* Page wrapper */
.cart-page {
    max-width: 900px;
    margin: 40px auto;
    padding: 20px;
}

/* Header */
.cart-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.cart-header a {
    text-decoration: none;
    color: #ff7a00;
    font-weight: 600;
}

/* Table */
.cart-page table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 25px;
}

/* Table headers */
.cart-page th {
    text-align: left;
    padding: 14px;
    background: #f5f5f5;
    font-weight: 600;
    border-bottom: 1px solid #ddd;
}

/* Table cells */
.cart-page td {
    padding: 16px 14px;
    border-bottom: 1px solid #eee;
    vertical-align: middle;
}

/* Product cell */
.cart-product {
    display: flex;
    align-items: center;
    gap: 15px;
}

.cart-product img {
    width: 70px;
    height: auto;
    border-radius: 6px;
}

/* Remove button */
.cart-page button {
    padding: 8px 16px;
    border-radius: 6px;
    border: none;
    background: #ff7a00;
    color: #fff;
    cursor: pointer;
    font-size: 14px;
}

.cart-page button:hover {
    background: #e56d00;
}

/* Total row */
.cart-total td {
    font-size: 16px;
    font-weight: 700;
    background: #fafafa;
}

/* Empty cart */
.empty-cart {
    margin-top: 30px;
    font-size: 16px;
}
</style>
</head>

<body>

<header class="cart-header">
  <div class="logo">Merokala</div>
  <a href="ho.php">← Continue Shopping</a>
</header>

<main class="cart-page">

<h1>Your Cart</h1>

<?php if (empty($cart)): ?>
    <p class="empty-cart">Your cart is empty.</p>
<?php else: ?>

<table>
    <tr>
        <th>Product</th>
        <th>Price</th>
        <th>Action</th>
    </tr>

    <?php $total = 0; ?>
    <?php foreach ($cart as $product_id => $item): ?>
        <?php $total += $item['price']; ?>
        <tr>
            <td>
                <div class="cart-product">
                    <img src="uploads/<?= htmlspecialchars($item['image']) ?>" alt="">
                    <span><?= htmlspecialchars($item['name']) ?></span>
                </div>
            </td>
            <td>Rs. <?= number_format($item['price'], 2) ?></td>
            <td>
                <form method="POST" action="remove_from_cart.php">
                    <input type="hidden" name="product_id" value="<?= (int)$product_id ?>">
                    <button type="submit">Remove</button>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>

    <tr class="cart-total">
        <td>Total</td>
        <td colspan="2">Rs. <?= number_format($total, 2) ?></td>
    </tr>
</table>



<div style="margin-top: 30px; text-align: right;">
    <a href="checkout.php" 
       style="
           display: inline-block;
           padding: 12px 24px;
           background: #ff7a00;
           color: #fff;
           text-decoration: none;
           border-radius: 6px;
           font-weight: 600;
       ">
        Proceed to Checkout
    </a>
</div>


<?php endif; ?>

</main>

</body>
</html>
