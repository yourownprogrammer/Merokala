<?php
require "php/dbconnection.php";

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
        p.uploaded_by,
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
    die("Product not found");
}

$product = $result->fetch_assoc();

$uploaderName = ($product['uploaded_by'] === 'admin')
    ? 'merokala_default'
    : trim($product['first_name'] . ' ' . $product['last_name']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title><?= htmlspecialchars($product['name']) ?></title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<style>
body {
  font-family: Arial, sans-serif;
  background:#fafafa;
  padding:30px;
}

.product-wrapper {
  display:flex;
  gap:40px;
  max-width:900px;
  margin:auto;
}

.product-wrapper img {
  width:380px;
  height:460px;
  object-fit:cover;
  border-radius:14px;
}

.product-info h1 {
  margin:0 0 10px;
}

.uploader {
  color:#666;
  margin-bottom:10px;
}

.price {
  font-size:22px;
  font-weight:bold;
  margin-bottom:20px;
}

.description {
  line-height:1.6;
}
</style>
</head>
<body>

<div class="product-wrapper">

  <img src="uploads/<?= htmlspecialchars($product['image']) ?>" alt="">

  <div class="product-info">
    <h1><?= htmlspecialchars($product['name']) ?></h1>
    <div class="uploader"><?= htmlspecialchars($uploaderName) ?></div>
    <div class="price">Rs. <?= number_format($product['price'], 2) ?></div>
    <div class="description"><?= nl2br(htmlspecialchars($product['description'])) ?></div>
  </div>

</div>

</body>
</html>
