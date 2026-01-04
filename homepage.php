<?php
require "php/dbconnection.php";

/* CATEGORY IDS */
$categories = [
    1 => "Canvas and Craft",
    2 => "Art and Craft",
    3 => "Sewing and Crochet",
    4 => "Jewellery and Accessories",
    5 => "Handcrafted solids"
];

function fetchProducts($conn, $categoryId) {
    return $conn->query("
        SELECT 
            p.id,
            p.name,
            p.price,
            p.image,
            p.provider_id,
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
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Merokala</title>

<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="css/htm.css">
<link rel="stylesheet" href="css/css1.css">

</head>

<body>

<header>
  <div class="logo">Merokala</div>

  <nav class="nav-left">
    <div class="dropdown">
      <a href="#" class="dropbtn">🟰 Categories</a>
      <ul class="dropdown-content">
        <li><a href="#">Accessories</a></li>
        <li><a href="#">Art & Collectibles</a></li>
        <li><a href="#">Baby</a></li>
        <li><a href="#">Bags & Purses</a></li>
        <li><a href="#">Bath & Beauty</a></li>
      </ul>
    </div>
  </nav>

  <div class="nav-right">
    <div class="search-wrapper">
      <input type="text" class="search-bar" placeholder="Search...">
      <span class="search-icon">🔍</span>
    </div>

  <a href="profile.php" class="icon-wrapper">
  <span class="icon">👤</span>
  <div class="tooltip">Profile</div>
</a>

<a href="cart.php" class="icon-wrapper">
  <span class="icon">🛒</span>
  <div class="tooltip">Cart</div>
</a>

<a href="favourites.php" class="icon-wrapper">
  <span class="icon">🩷</span>
  <div class="tooltip">Favourites</div>
</a>


    <a href="../merokala/php/usignup.php" class="btn">Sign In</a>
    <a href="../merokala/php/pro.php" class="sell-link">Sell</a>
  </div>
</header>

<div id="overlay"></div>

<!-- CAROUSEL (UNCHANGED) -->
<section class="carousel">
  <div class="slides">
    <div class="slide" style="background-image:url('pics/one.png')">
      <div class="slide-text">
        <h1>Find Handmade Creations & Authentic Products</h1>
        <p>Discover unique art and support independent creators from Nepal.</p>
        <button class="btn">Shop Now</button>
      </div>
    </div>
    <div class="slide" style="background-image:url('pics/two.png')">
      <div class="slide-text">
        <h1>List Your Products</h1>
        <p>Join our community of artists and sell your handmade creations.</p>
        <button class="btn">Start Listing</button>
      </div>
    </div>
    <div class="slide" style="background-image:url('pics/three.png')">
      <div class="slide-text">
        <h1>Connecting Creators & Admirers</h1>
        <p>Support local artists and explore authentic products.</p>
      </div>
    </div>
  </div>

  <div class="carousel-controls">
    <button class="nav-btn prev">&#10094;</button>
    <button class="pause-btn">⏸</button>
    <button class="nav-btn next">&#10095;</button>
  </div>
  <div class="dots"></div>
</section>

<!-- PRODUCT SECTIONS -->
<?php foreach ($categories as $catId => $catName): ?>
<?php $products = fetchProducts($conn, $catId); ?>

<section class="product-section">
  <div class="section-header">
    <h2><?= htmlspecialchars($catName) ?></h2>
    <div class="section-arrows">
      <button class="arrow-btn prev">&lt;</button>
      <button class="arrow-btn next">&gt;</button>
    </div>
  </div>

  <div class="product-container">

    <?php if ($products && $products->num_rows > 0): ?>
      <?php while ($row = $products->fetch_assoc()): ?>
        <?php
         $uploaderName = is_null($row['provider_id'])
    ? 'merokala_default'
    : trim($row['first_name'] . ' ' . $row['last_name']);

        ?>

        <div class="product-card">
          <a href="product.php?id=<?= $row['id'] ?>">
            <div class="product-img">
              <img src="uploads/<?= htmlspecialchars($row['image']) ?>" alt="">
              <button class="fav-btn-card" type="button">♡</button>
            </div>
          </a>
          <h3><?= htmlspecialchars($row['name']) ?></h3>
          <p><?= htmlspecialchars($uploaderName) ?></p>
          <p>Rs. <?= number_format($row['price'],2) ?></p>
        </div>

      <?php endwhile; ?>
    <?php else: ?>
      <?php for ($i=0;$i<6;$i++): ?>
        <div class="product-card placeholder">
          <div class="product-img placeholder"></div>
          <h3>Product Name</h3>
          <p>Rs. 0</p>
        </div>
      <?php endfor; ?>
    <?php endif; ?>

  </div>
</section>

<?php endforeach; ?>

<!-- FOOTER (UNCHANGED) -->
<footer class="site-footer">
  <div class="footer-top">
    <div class="footer-brand">
      <h2>Merokala</h2>
      <p>Discover. Create. Collect.</p>
    </div>

    <div class="footer-section">
      <h3>Explore</h3>
      <ul>
        <li><a href="#">Marketplace</a></li>
        <li><a href="#">Categories</a></li>
        <li><a href="#">New Arrivals</a></li>
      </ul>
    </div>

    <div class="footer-section">
      <h3>Support</h3>
      <ul>
        <li><a href="#">FAQ</a></li>
        <li><a href="#">Contact</a></li>
      </ul>
    </div>
  </div>

  <div class="footer-bottom">
    <p>&copy; 2025 Merokala</p>
  </div>
</footer>

<script src="js/caurosel.js"></script>
<script src="js/dropdown.js"></script>
<script src="js/leftright.js"></script>

</body>
</html>

