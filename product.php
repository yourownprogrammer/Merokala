<?php
session_start();
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


if (!isset($_SESSION['recent_product_ids']) || !is_array($_SESSION['recent_product_ids'])) {
    $_SESSION['recent_product_ids'] = [];
}
$recentList = array_values(array_filter(
    $_SESSION['recent_product_ids'],
    function ($id) use ($product_id) {
        return (int) $id !== $product_id;
    }
));
array_unshift($recentList, $product_id);
$recentList = array_slice(array_values(array_unique(array_map('intval', $recentList))), 0, 12);
$_SESSION['recent_product_ids'] = $recentList;

$sidebarRecentIds = array_slice($recentList, 1, 8);
$recentSidebarProducts = [];
if (!empty($sidebarRecentIds)) {
    $ids = array_map('intval', $sidebarRecentIds);
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $types = str_repeat('i', count($ids));
    $fieldOrder = implode(',', $ids);
    $recentStmt = $conn->prepare("
        SELECT id, name, description, price, image
        FROM products
        WHERE id IN ($placeholders)
          AND status = 'approved'
        ORDER BY FIELD(id, $fieldOrder)
    ");
    $recentStmt->bind_param($types, ...$ids);
    $recentStmt->execute();
    $rr = $recentStmt->get_result();
    while ($row = $rr->fetch_assoc()) {
        $recentSidebarProducts[] = $row;
    }
    $recentStmt->close();
}

function excerpt_recent_product_desc($text, $maxLen = 88)
{
    $t = trim(preg_replace('/\s+/', ' ', (string) $text));
    if ($t === '') {
        return '';
    }
    if (strlen($t) <= $maxLen) {
        return $t;
    }
    return substr($t, 0, $maxLen - 1) . '…';
}

/* ---------- OWN-WRITTEN RECOMMENDATION ALGORITHM ---------- */
function tokenize_text($text)
{
    $text = strtolower((string) $text);
    $clean = preg_replace('/[^a-z0-9\s]/', ' ', $text);
    $parts = preg_split('/\s+/', trim($clean));
    $stopwords = [
        'the', 'and', 'for', 'with', 'this', 'that', 'from', 'your', 'you',
        'are', 'was', 'were', 'have', 'has', 'had', 'not', 'but', 'too',
        'very', 'new', 'best', 'made', 'handmade', 'one', 'kind'
    ];

    $tokens = [];
    foreach ($parts as $part) {
        if (strlen($part) < 3 || in_array($part, $stopwords, true)) {
            continue;
        }
        $tokens[] = $part;
    }

    return array_values(array_unique($tokens));
}

function get_recommendation_score($currentProduct, $candidateProduct)
{
    $currentTokens = tokenize_text($currentProduct['name'] . ' ' . $currentProduct['description']);
    $candidateTokens = tokenize_text($candidateProduct['name'] . ' ' . $candidateProduct['description']);

    $overlap = array_intersect($currentTokens, $candidateTokens);
    $keywordScore = count($overlap) * 14;

    $currentPrice = (float) $currentProduct['price'];
    $candidatePrice = (float) $candidateProduct['price'];
    $priceDiff = abs($currentPrice - $candidatePrice);
    $priceBase = max($currentPrice, 1);
    $priceRatio = $priceDiff / $priceBase;

    // Closer price range gets higher points.
    $priceScore = max(0, 30 - ($priceRatio * 30));

    $providerScore = 0;
    if ($currentProduct['provider_id'] !== null && $candidateProduct['provider_id'] !== null) {
        if ((int) $currentProduct['provider_id'] === (int) $candidateProduct['provider_id']) {
            $providerScore = 10;
        }
    }

    $descriptionLengthScore = 0;
    if (strlen((string) $candidateProduct['description']) >= 50) {
        $descriptionLengthScore = 4;
    }

    return $keywordScore + $priceScore + $providerScore + $descriptionLengthScore;
}

$recommendedProducts = [];
$recommendStmt = $conn->prepare("
    SELECT id, name, description, price, image, provider_id
    FROM products
    WHERE status = 'approved'
      AND id != ?
    LIMIT 80
");
$recommendStmt->bind_param("i", $product_id);
$recommendStmt->execute();
$recommendResult = $recommendStmt->get_result();

$scoredProducts = [];
while ($candidate = $recommendResult->fetch_assoc()) {
    $candidate['score'] = get_recommendation_score($product, $candidate);
    $scoredProducts[] = $candidate;
}

usort($scoredProducts, function ($a, $b) {
    if ($a['score'] === $b['score']) {
        return (float) $a['price'] <=> (float) $b['price'];
    }
    return $b['score'] <=> $a['score'];
});

$recommendedProducts = array_slice($scoredProducts, 0, 4);
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
  padding: 0 0 32px;
  font-family: Arial, sans-serif;
  background: #fafafa;
}
.product-header {
  display: flex;
  align-items: center;
  padding: 8px 40px;
  background: #fff;
  box-shadow: 0 1px 0 rgba(0,0,0,0.06);
  margin-bottom: 12px;
}
.product-header .logo-link {
  font-size: 20px;
  font-weight: 700;
  color: #ff7a00;
  text-decoration: none;
  line-height: 1.2;
}
.product-page {
  display: flex;
  gap: 28px;
  max-width: 1320px;
  margin: 0 40px 0 60px;
  align-items: flex-start;
}
.product-main {
  flex: 1;
  min-width: 0;
}
.product-wrapper {
  display: flex;
  gap: 40px;
  max-width: 100%;
  margin: 0;
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
.save-form {
  margin-top: 18px;
}
.save {
  background: none;
  border: none;
  font-size: 13px;
  color: #aaa;
  cursor: pointer;
  padding: 0;
}
.save:hover {
  color: #c0392b;
}
.recent-sidebar {
  width: 300px;
  flex-shrink: 0;
  position: sticky;
  top: 24px;
  align-self: flex-start;
}
.recent-sidebar-inner {
  background: #fff;
  border: 1px solid #eee;
  border-radius: 16px;
  box-shadow: 0 8px 24px rgba(0,0,0,0.06);
  padding: 18px 16px;
}
.recent-sidebar h3 {
  margin: 0 0 4px;
  font-size: 16px;
  font-weight: 700;
  color: #111;
}
.recent-sidebar .recent-hint {
  margin: 0 0 14px;
  font-size: 12px;
  color: #666;
  line-height: 1.5;
}
.recent-item-desc {
  font-size: 11px;
  color: #888;
  line-height: 1.35;
  margin-top: 4px;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
.recent-list {
  list-style: none;
  margin: 0;
  padding: 0;
}
.recent-item {
  border-top: 1px solid #f0f0f0;
}
.recent-item:first-of-type {
  border-top: none;
}
.recent-item a {
  display: flex;
  gap: 12px;
  padding: 12px 0;
  text-decoration: none;
  color: inherit;
  border-radius: 8px;
  margin: 0 -6px;
  padding-left: 6px;
  padding-right: 6px;
  transition: background 0.15s ease;
}
.recent-item a:hover {
  background: #fafafa;
}
.recent-item img {
  width: 56px;
  height: 56px;
  object-fit: cover;
  border-radius: 8px;
  background: #f3f3f3;
  flex-shrink: 0;
}
.recent-item-text {
  min-width: 0;
}
.recent-item-title {
  font-size: 13px;
  font-weight: 600;
  line-height: 1.35;
  color: #222;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
.recent-item-price {
  font-size: 12px;
  color: #666;
  margin-top: 4px;
}
.recent-empty {
  font-size: 13px;
  color: #888;
  line-height: 1.5;
  margin: 0;
}
.recommended-section {
  max-width: 1320px;
  margin: 38px 40px 0 60px;
}
.recommended-title {
  font-size: 24px;
  margin: 0 0 16px;
}
.recommended-subtitle {
  margin: 0 0 20px;
  color: #666;
  font-size: 14px;
}
.recommended-grid {
  display: grid;
  grid-template-columns: repeat(4, minmax(0, 1fr));
  gap: 16px;
}
.recommended-card {
  display: block;
  text-decoration: none;
  color: inherit;
  background: #fff;
  border: 1px solid #efefef;
  border-radius: 16px;
  padding: 12px;
  box-shadow: 0 8px 20px rgba(0,0,0,0.05);
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.recommended-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 12px 24px rgba(0,0,0,0.09);
}
.recommended-card img {
  width: 100%;
  height: 170px;
  object-fit: cover;
  border-radius: 12px;
  background: #f3f3f3;
}
.recommended-name {
  margin: 10px 0 6px;
  font-size: 15px;
  font-weight: 700;
  line-height: 1.4;
}
.recommended-price {
  margin: 0;
  color: #222;
  font-size: 14px;
  font-weight: 600;
}
@media (max-width: 1080px) {
  .product-header {
    padding-left: 24px;
    padding-right: 24px;
  }
  .product-page {
    margin-left: 24px;
    margin-right: 24px;
    flex-direction: column;
  }
  .recent-sidebar {
    width: 100%;
    position: static;
    order: 2;
  }
  .product-main {
    order: 1;
  }
  .recommended-section {
    margin: 32px 24px 0;
  }
  .recommended-grid {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }
}
@media (max-width: 680px) {
  body {
    padding: 0 0 24px;
  }
  .product-header {
    padding: 8px 16px;
    margin-bottom: 10px;
  }
  .product-page {
    margin-left: 16px;
    margin-right: 16px;
  }
  .product-wrapper {
    flex-direction: column;
    gap: 20px;
  }
  .product-wrapper img,
  .product-info {
    width: 100%;
  }
  .recommended-section {
    margin-left: 16px;
    margin-right: 16px;
  }
  .recommended-grid {
    grid-template-columns: 1fr;
  }
}
</style>
</head>

<body>

<header class="product-header">
    <a href="homepage.php" class="logo-link">Merokala</a>
</header>
<div class="product-page">
  <div class="product-main">
    <div class="product-wrapper">

      <img src="<?= !empty($product['image']) ? 'uploads/' . htmlspecialchars($product['image']) : 'pics/one.png' ?>"
           alt="<?= htmlspecialchars($product['name']) ?>"
           onerror="this.src='pics/one.png'">

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
            <input type="hidden" name="return_url" value="product.php?id=<?= $product_id ?>">
            <button type="submit" class="add-cart">Add to Cart</button>
          </form>

          <form action="add_to_cart.php" method="POST" style="flex:1;">
            <input type="hidden" name="product_id" value="<?= $product_id ?>">
            <input type="hidden" name="buy_now" value="1">
            <button type="submit" class="buy-now">Buy Now</button>
          </form>
        </div>

        <form method="POST" action="toggle_favourite.php" class="save-form">
          <input type="hidden" name="product_id" value="<?= $product_id ?>">
          <button type="submit" class="save">♡ Save to favourites</button>
        </form>

      </div>

    </div>
  </div>

  <aside class="recent-sidebar" aria-label="Recently viewed products">
    <div class="recent-sidebar-inner">
      <h3>Recent products</h3>
      <p class="recent-hint">
        
      </p>
      <?php if (!empty($recentSidebarProducts)): ?>
        <ul class="recent-list">
          <?php foreach ($recentSidebarProducts as $rp): ?>
            <?php $recentDesc = excerpt_recent_product_desc($rp['description'] ?? ''); ?>
            <li class="recent-item">
              <a href="product.php?id=<?= (int) $rp['id'] ?>">
                <img
                  src="<?= !empty($rp['image']) ? 'uploads/' . htmlspecialchars($rp['image']) : 'pics/one.png' ?>"
                  alt=""
                  onerror="this.src='pics/one.png'"
                >
                <div class="recent-item-text">
                  <div class="recent-item-title"><?= htmlspecialchars(ucwords($rp['name'])) ?></div>
                  <?php if ($recentDesc !== ''): ?>
                    <div class="recent-item-desc"><?= htmlspecialchars($recentDesc) ?></div>
                  <?php endif; ?>
                  <div class="recent-item-price">Rs. <?= number_format((float) $rp['price'], 2) ?></div>
                </div>
              </a>
            </li>
          <?php endforeach; ?>
        </ul>
      <?php else: ?>
        <p class="recent-empty"></p>
      <?php endif; ?>
    </div>
  </aside>
</div>

<?php if (!empty($recommendedProducts)): ?>
<section class="recommended-section">
  <h2 class="recommended-title">Recommended Products</h2>
  <p class="recommended-subtitle"></p>

  <div class="recommended-grid">
    <?php foreach ($recommendedProducts as $recommended): ?>
      <a class="recommended-card" href="product.php?id=<?= (int) $recommended['id'] ?>">
        <img
          src="<?= !empty($recommended['image']) ? 'uploads/' . htmlspecialchars($recommended['image']) : 'pics/one.png' ?>"
          alt="<?= htmlspecialchars($recommended['name']) ?>"
          onerror="this.src='pics/one.png'"
        >
        <p class="recommended-name"><?= htmlspecialchars(ucwords($recommended['name'])) ?></p>
        <p class="recommended-price">Rs. <?= number_format((float) $recommended['price'], 2) ?></p>
      </a>
    <?php endforeach; ?>
  </div>
</section>
<?php endif; ?>

</body>
</html>
