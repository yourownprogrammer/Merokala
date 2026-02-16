<?php
session_start();
require "php/dbconnection.php";

$q = trim($_GET['q'] ?? '');
$categoryId = isset($_GET['category']) ? (int) $_GET['category'] : 0;

/* Category filter: show products in category */
if ($categoryId > 0) {
    $stmt = $conn->prepare("
        SELECT p.id, p.name, p.price, p.image
        FROM products p
        JOIN product_categories pc ON p.id = pc.product_id
        WHERE pc.category_id = ? AND p.status = 'approved'
        ORDER BY p.created_at DESC
    ");
    $stmt->bind_param("i", $categoryId);
    $stmt->execute();
    $result = $stmt->get_result();
    $catName = "";
    $catRes = $conn->prepare("SELECT name FROM categories WHERE id = ?");
    $catRes->bind_param("i", $categoryId);
    $catRes->execute();
    $cn = $catRes->get_result()->fetch_assoc();
    $catName = $cn ? $cn['name'] : "Category";
    $stmt->close();
    $catRes->close();
} elseif (strlen($q) >= 2) {
    /* Text search: match product names */
    $matches = [];
    $all = $conn->query("SELECT id, name, price, image FROM products WHERE status='approved'");
    while ($row = $all->fetch_assoc()) {
        if (stripos($row['name'], $q) !== false) {
            $matches[] = $row;
        }
    }
    $result = null;
} else {
    $result = null;
    $matches = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Results – Merokala</title>
    <link rel="stylesheet" href="css/htm.css">
    <link rel="stylesheet" href="css/css1.css">
    <style>
        .search-page { max-width: 1200px; margin: 40px auto; padding: 20px; }
        .search-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 24px; }
        .search-card { background: #fff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.08); }
        .search-card img { width: 100%; height: 200px; object-fit: cover; }
        .search-card .info { padding: 16px; }
        .search-card h3 { margin: 0 0 8px; font-size: 16px; }
        .search-card .price { font-weight: 700; color: #ff7a00; }
        .back-link { display: inline-block; margin-bottom: 20px; color: #ff7a00; font-weight: 600; text-decoration: none; }
        .back-link:hover { text-decoration: underline; }
    </style>
</head>
<body>

<header>
    <div class="logo"><a href="homepage.php">Merokala</a></div>
    <a href="homepage.php" class="back-link">← Back to Home</a>
</header>

<main class="search-page">
    <?php if ($categoryId > 0): ?>
        <h1>Category: <?= htmlspecialchars($catName) ?></h1>
        <?php if ($result && $result->num_rows > 0): ?>
            <div class="search-grid">
                <?php while ($row = $result->fetch_assoc()): ?>
                    <a href="product.php?id=<?= $row['id'] ?>" class="search-card">
                        <img src="uploads/<?= htmlspecialchars($row['image']) ?>" alt="<?= htmlspecialchars($row['name']) ?>">
                        <div class="info">
                            <h3><?= htmlspecialchars($row['name']) ?></h3>
                            <p class="price">Rs. <?= number_format($row['price'], 2) ?></p>
                        </div>
                    </a>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <p>No products in this category yet.</p>
        <?php endif; ?>

    <?php elseif (!empty($q)): ?>
        <h1>Search results for "<?= htmlspecialchars($q) ?>"</h1>
        <?php if (!empty($matches)): ?>
            <div class="search-grid">
                <?php foreach ($matches as $p): ?>
                    <a href="product.php?id=<?= $p['id'] ?>" class="search-card">
                        <img src="uploads/<?= htmlspecialchars($p['image']) ?>" alt="<?= htmlspecialchars($p['name']) ?>">
                        <div class="info">
                            <h3><?= htmlspecialchars($p['name']) ?></h3>
                            <p class="price">Rs. <?= number_format($p['price'], 2) ?></p>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p>No products found matching "<?= htmlspecialchars($q) ?>".</p>
        <?php endif; ?>

    <?php else: ?>
        <p>Enter a search term (at least 2 characters) or choose a category.</p>
    <?php endif; ?>
</main>

</body>
</html>
