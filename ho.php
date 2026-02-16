<?php
session_start();
require "php/dbconnection.php";

/* ================= PRICE SORT (GLOBAL) ================= */
$sortOrder = 'asc';
if (isset($_GET['price_sort']) && $_GET['price_sort'] === 'desc') {
    $sortOrder = 'desc';
}

$isUser     = isset($_SESSION['user_id']);
$isProvider = isset($_SESSION['provider_id']);

/* ================= CART COUNT ================= */
$cartCount = 0;
if (!empty($_SESSION['cart'])) {
    $cartCount = count($_SESSION['cart']);
}

/* ================= FAVOURITES COUNT ================= */
$favCount = 0;
if (isset($_SESSION['user_id'])) {
    $stmt = $conn->prepare("
        SELECT COUNT(*) AS total
        FROM favourites
        WHERE user_id = ?
    ");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $favCount = $stmt->get_result()->fetch_assoc()['total'];
    $stmt->close();
}

/* ================= CATEGORIES - dynamic from DB ================= */
$categories = [];
$catRes = $conn->query("SELECT id, name FROM categories ORDER BY name");
if ($catRes && $catRes->num_rows > 0) {
    while ($row = $catRes->fetch_assoc()) {
        $categories[$row['id']] = $row['name'];
    }
}
if (empty($categories)) {
    $categories = [1 => "Canvas and Craft", 2 => "Art and Craft", 3 => "Sewing and Crochet", 4 => "Jewellery and Accessories", 5 => "Handcrafted solids"];
}

/* ================= FETCH PRODUCTS (ARRAY) ================= */
function fetchProducts($conn, $categoryId) {
    $cid = (int) $categoryId;
    $result = $conn->query("
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
        WHERE pc.category_id = $cid
          AND p.status = 'approved'
        ORDER BY p.created_at DESC
        LIMIT 6
    ");

    $products = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $products[] = $row;
        }
    }
    return $products;
}

/* ================= MERGE SORT (PRICE) ================= */
function mergeSortByPrice($items) {
    if (count($items) <= 1) return $items;

    $mid = intdiv(count($items), 2);
    $left  = array_slice($items, 0, $mid);
    $right = array_slice($items, $mid);

    return mergeByPrice(
        mergeSortByPrice($left),
        mergeSortByPrice($right)
    );
}

function mergeByPrice($left, $right) {
    $sorted = [];

    while (!empty($left) && !empty($right)) {
        if ($left[0]['price'] <= $right[0]['price']) {
            $sorted[] = array_shift($left);
        } else {
            $sorted[] = array_shift($right);
        }
    }

    return array_merge($sorted, $left, $right);
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
<link rel="stylesheet" href="css/product-actions.css">
</head>

<body>

<header>
  <div class="logo">Merokala</div>

  <nav class="nav-left">
    <div class="dropdown">
      <a href="#" class="dropbtn">🟰 Categories</a>
      <ul class="dropdown-content">
        <?php foreach ($categories as $cid => $cname): ?>
        <li><a href="search.php?category=<?= (int)$cid ?>"><?= htmlspecialchars($cname) ?></a></li>
        <?php endforeach; ?>
      </ul>
    </div>
  </nav>

  <div class="nav-right">

<form class="search-wrapper" onsubmit="return false;">
    <div class="search-input-container">
        <input type="text" id="searchInput" class="search-bar" placeholder="Search..." spellcheck="false">
        <span class="search-icon">🔍</span>
    </div>
    <div id="searchResults" class="search-results"></div>
</form>

<a href="php/profile_redirect.php" class="icon-wrapper">
  <span class="icon">👤</span>
  <div class="tooltip">Profile</div>
</a>

<a href="cart.php" class="icon-wrapper" style="position:relative;">
  <span class="icon">🛒</span>
  <?php if ($cartCount > 0): ?>
    <span style="position:absolute;top:-6px;right:-6px;background:#ff7a00;color:#fff;font-size:11px;padding:2px 6px;border-radius:999px;font-weight:600;">
        <?= $cartCount ?>
    </span>
  <?php endif; ?>
  <div class="tooltip">Cart</div>
</a>

<a href="favourites.php" class="icon-wrapper" style="position:relative;">
  <span class="icon">🩷</span>
  <?php if ($favCount > 0): ?>
    <span style="position:absolute;top:-6px;right:-6px;background:#ff7a00;color:#fff;font-size:11px;padding:2px 6px;border-radius:999px;font-weight:600;">
        <?= $favCount ?>
    </span>
  <?php endif; ?>
  <div class="tooltip">Favourites</div>
</a>

<?php if (!isset($_SESSION['user_id'])): ?>
    <a href="php/usignup.php" class="btn">Sign In</a>
    <a href="php/pro.php" class="sell-link">Sell</a>
<?php else: ?>
    <a href="php/user_dashboard.php" class="btn">My Account</a>
    <a href="php/logout.php" class="sell-link">Logout</a>
<?php endif; ?>

  </div>
</header>

<div id="overlay"></div>

<!-- ================= CAROUSEL ================= -->
<section class="carousel">
  <div class="slides">
    <div class="slide" style="background-image:url('pics/one.png')">
      <div class="slide-text">
        <h1>Find Handmade Creations & Authentic Products</h1>
        <p>Discover unique art and support independent creators from Nepal.</p>
        <a href="php/usignup.php">
        <button class="btn">Shop Now</button> </a>
      </div>
    </div>

    <div class="slide" style="background-image:url('pics/two.png')">
      <div class="slide-text">
        <h1>List Your Products</h1>
        <p>Join our community of artists and sell your handmade creations.</p>
        <a href="php/pro.php">
        <button class="btn">Start Listing</button></a>
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

<!-- ================= GLOBAL PRICE SORT ================= -->
<div style="max-width:1200px;margin:20px auto 10px;padding:0 40px;">
  <div style="display:flex;gap:14px;font-weight:600;border-bottom:1px solid #eee;padding-bottom:8px;">
      <span>Manage price by:</span>
      <a href="?price_sort=asc" style="<?= $sortOrder === 'asc' ? 'color:#ff7a00;' : '' ?>">Low → High</a>
      <a href="?price_sort=desc" style="<?= $sortOrder === 'desc' ? 'color:#ff7a00;' : '' ?>">High → Low</a>
  </div>
</div>

<!-- PRICE FILTER (UI ONLY – WORK IN PROGRESS) -->
<div style="
    max-width:1200px;
    margin:6px auto 14px;
    padding:0 40px;
">
  <div style="
      display:flex;
      align-items:center;
      gap:8px;
      font-size:14px;
      opacity:0.6;
      pointer-events:none;
  ">
      <span>Filter price:</span>

      <input type="number" placeholder="Min" disabled
          style="width:70px;padding:4px;border:1px solid #ccc;border-radius:5px;">

      <span>-</span>

      <input type="number" placeholder="Max" disabled
          style="width:70px;padding:4px;border:1px solid #ccc;border-radius:5px;">

      <button disabled
          style="padding:4px 10px;border-radius:5px;border:none;background:#ff7a00;color:#fff;font-weight:600;">
          Apply
      </button>

      <button disabled
          style="padding:4px 10px;border-radius:5px;border:1px solid #ccc;background:#f5f5f5;font-weight:600;">
          Reset
      </button>

      <span style="font-size:12px;color:#999;">
          (work in progress)
      </span>
  </div>
</div>

<!-- ================= PRODUCT SECTIONS ================= -->
<?php foreach ($categories as $catId => $catName): ?>

<?php
$products = fetchProducts($conn, $catId);
$products = mergeSortByPrice($products);
if ($sortOrder === 'desc') {
    $products = array_reverse($products);
}
?>

<section class="product-section">
  <div class="section-header">
    <h2><?= htmlspecialchars($catName) ?></h2>
    <div class="section-arrows">
      <button class="arrow-btn prev">&lt;</button>
      <button class="arrow-btn next">&gt;</button>
    </div>
  </div>

  <div class="product-container">

<?php if (!empty($products)): ?>
<?php foreach ($products as $row): ?>

<?php
$uploaderName = is_null($row['provider_id'])
    ? 'merokala_default'
    : trim($row['first_name'] . ' ' . $row['last_name']);
?>

<div class="product-card">
  <div class="product-img">
    <a href="product.php?id=<?= $row['id'] ?>">
      <img src="uploads/<?= htmlspecialchars($row['image']) ?>" alt="">
    </a>

    <form method="POST" action="toggle_favourite.php">
      <input type="hidden" name="product_id" value="<?= $row['id'] ?>">
      <button type="submit" class="fav-btn-card">♡</button>
    </form>
  </div>

  <h3><?= htmlspecialchars($row['name']) ?></h3>
  <p><?= htmlspecialchars($uploaderName) ?></p>
  <p>Rs. <?= number_format($row['price'],2) ?></p>

  <form method="POST" action="add_to_cart.php">
    <input type="hidden" name="product_id" value="<?= $row['id'] ?>">
    <button type="submit" class="add-to-cart-btn">Add to Cart</button>
  </form>
</div>

<?php endforeach; ?>
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

<!-- ================= FOOTER ================= -->
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
    <p>&copy; 2026 Merokala</p>
  </div>
</footer>

<script src="js/caurosel.js"></script>
<script src="js/dropdown.js"></script>
<script src="js/leftright.js"></script>
<script src="js/suggestion.js"></script>

</body>
</html>
