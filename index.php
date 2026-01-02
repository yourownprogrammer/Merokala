<?php
require "php/dbconnection.php";

/* CATEGORY IDS (TEMP / ROUGH) */
$categories = [
    1 => "Canvas and Craft",
    2 => "Art and Craft",
    3 => "Sewing and Crochet",
    4 => "Jewellery and Accessories",
    5 => "Handcrafted Solids"
];

function fetchProducts($conn, $categoryId) {
    return $conn->query("
        SELECT 
            p.id,
            p.name,
            p.price,
            p.image,
            p.uploaded_by,
            pr.first_name,
            pr.last_name
        FROM products p
        JOIN product_categories pc ON p.id = pc.product_id
        LEFT JOIN providers pr ON p.provider_id = pr.id
        WHERE pc.category_id = $categoryId
          AND p.status = 'approved'
        ORDER BY p.created_at DESC
        LIMIT 6
    ");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Merokala</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<style>
body {
  font-family: Arial, sans-serif;
  background: #fafafa;
  margin: 0;
  padding: 0;
}

section {
  padding: 24px;
}

h2 {
  margin-bottom: 14px;
}

.product-grid {
  display: flex;
  gap: 18px;
  flex-wrap: wrap;
}

.product-card {
  width: 230px;
}

.img-wrap {
  position: relative;
  border-radius: 14px;
  overflow: hidden;
  background: #e0e0e0;
}

.img-wrap img {
  width: 100%;
  height: 280px;
  object-fit: cover;
  display: block;
}

.fav-btn {
  position: absolute;
  top: 10px;
  right: 10px;
  width: 34px;
  height: 34px;
  border-radius: 50%;
  border: none;
  background: white;
  font-size: 18px;
  cursor: pointer;
}

.card-body {
  padding-top: 8px;
}

.product-title {
  font-size: 14px;
  font-weight: 600;
  margin: 0;
}

.product-uploader {
  font-size: 13px;
  color: #6b6b6b;
  margin: 4px 0;
}

.product-price {
  font-size: 15px;
  font-weight: bold;
}

.placeholder {
  width: 230px;
}

.placeholder .img-wrap {
  height: 280px;
}

.placeholder .card-body div {
  height: 12px;
  background: #ddd;
  margin-top: 6px;
  border-radius: 6px;
}
</style>
</head>
<body>

<h1 style="padding:24px;">Merokala</h1>

<?php foreach ($categories as $catId => $catName): ?>
<?php $products = fetchProducts($conn, $catId); ?>

<section>
  <h2><?= htmlspecialchars($catName) ?></h2>

  <div class="product-grid">

  <?php if ($products && $products->num_rows > 0): ?>
    <?php while ($row = $products->fetch_assoc()): ?>

      <?php
        $uploaderName = ($row['uploaded_by'] === 'admin')
          ? 'merokala_default'
          : trim($row['first_name'] . ' ' . $row['last_name']);
      ?>

      <div class="product-card">
        <a href="product.php?id=<?= $row['id'] ?>" class="img-wrap">
          <img src="uploads/<?= htmlspecialchars($row['image']) ?>" alt="">
          <button class="fav-btn" type="button">♡</button>
        </a>

        <div class="card-body">
          <p class="product-title"><?= htmlspecialchars($row['name']) ?></p>
          <p class="product-uploader"><?= htmlspecialchars($uploaderName) ?></p>
          <p class="product-price">Rs. <?= number_format($row['price'], 2) ?></p>
        </div>
      </div>

    <?php endwhile; ?>
  <?php else: ?>

    <!-- placeholders -->
    <?php for ($i = 0; $i < 4; $i++): ?>
      <div class="product-card placeholder">
        <div class="img-wrap"></div>
        <div class="card-body">
          <div></div>
          <div></div>
          <div></div>
        </div>
      </div>
    <?php endfor; ?>

  <?php endif; ?>

  </div>
</section>

<?php endforeach; ?>

</body>
</html>
