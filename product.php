<?php
require "php/dbconnection.php";
session_start();

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid product");
}

$product_id = (int) $_GET['id'];

$stmt = $conn->prepare("
    SELECT 
        p.name,
        p.description,
        p.price,
        p.image,
        p.provider_id,
        pr.first_name,
        pr.last_name
    FROM products p
    LEFT JOIN providers pr ON p.provider_id = pr.id
    WHERE p.id = ?
      AND p.status = 'approved'
    LIMIT 1
");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die('Product not found');
}

$product = $result->fetch_assoc();

/* ---------- UPLOADER NAME LOGIC ---------- */
if ($product['provider_id'] === null) {
    // Admin-added product
    $uploaderName = 'merokala_default';
} else {
    $uploaderName = trim($product['first_name'] . ' ' . $product['last_name']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title><?= htmlspecialchars($product['name']) ?> – Merokala</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<style>
body {
  margin: 0;
  padding: 40px 0;
  font-family: Arial, sans-serif;
  background: #fafafa;
}
.product-wrapper {
  display: flex;
  gap: 40px;
  max-width: 1200px;
  margin-left: 60px;
  margin-right: auto;
  align-items: flex-start;
}
.product-wrapper img {
  width: 460px;
  height: 520px;
  object-fit: cover;
  border-radius: 22px;
  background: #fff;
  padding: 10px;
  box-shadow: 0 18px 45px rgba(0,0,0,0.10);
}
.product-info {
  width: 460px;
  background: #fff;
  padding: 34px;
  border-radius: 22px;
  border: 1px solid #f0f0f0;
  box-shadow: 0 10px 30px rgba(0,0,0,0.06);
}
.product-info h1 {
  margin: 0 0 12px;
  font-size: 30px;
  line-height: 1.3;
}
.uploader {
  font-size: 14px;
  color: #888;
  margin-bottom: 18px;
}
.price {
  font-size: 26px;
  font-weight: 700;
  margin-bottom: 18px;
}
.description {
  font-size: 15px;
  line-height: 1.7;
  color: #555;
  margin-bottom: 20px;
}
.meta {
  font-size: 13px;
  color: #666;
  margin-bottom: 4px;
}
.meta-small {
  font-size: 12px;
  color: #888;
  margin-bottom: 24px;
}
.actions {
  display: flex;
  gap: 18px;
}
.actions button {
  width: 100%;
  height: 50px;
  border-radius: 999px;
  border: none;
  cursor: pointer;
  font-size: 14px;
  font-weight: 600;
}
.add-cart {
  background: #ff7a00;
  color: #fff;
}
.add-cart:hover {
  background: #e86e00;
}
.buy-now {
  background: #111;
  color: #fff;
}
.buy-now:hover {
  background: #000;
}
.save {
  margin-top: 18px;
  font-size: 13px;
  color: #aaa;
  cursor: pointer;
}
.save:hover {
  color: #c0392b;
}
</style>
</head>

<body>

<div class="product-wrapper">

  <img src="uploads/<?= htmlspecialchars($product['image']) ?>"
       alt="<?= htmlspecialchars($product['name']) ?>">

  <div class="product-info">

    <h1><?= htmlspecialchars(ucwords($product['name'])) ?></h1>
    <div class="uploader">by <?= htmlspecialchars($uploaderName) ?></div>

    <div class="price">Rs. <?= number_format($product['price'], 2) ?></div>

    <div class="description">
      <?= nl2br(htmlspecialchars($product['description'])) ?>
    </div>

    <div class="meta">Handmade • One-of-a-kind</div>
    <div class="meta-small">Secure checkout • No mass production</div>

    <div class="actions">
      <form action="add_to_cart.php" method="POST" style="flex:1;">
        <input type="hidden" name="product_id" value="<?= $product_id ?>">
        <button type="submit" class="add-cart">Add to Cart</button>
      </form>

      <a href="checkout.php?buy_now=<?= $product_id ?>" style="flex:1; text-decoration:none;">
        <button type="button" class="buy-now">Buy Now</button>
      </a>
    </div>

    <div class="save">♡ Save to favourites</div>

  </div>

</div>

</body>
</html>
