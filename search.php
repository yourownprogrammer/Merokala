<?php
require "php/dbconnection.php";

$q = trim($_GET['q'] ?? '');

if (strlen($q) < 2) {
    echo "Search term too short";
    exit;
}

/* fetch all products */
$result = mysqli_query(
    $conn,
    "SELECT id, name, price FROM products WHERE status='approved'"
);

/* same manual algorithm */
function manualMatch($text, $pattern) {
    $text = strtolower($text);
    $pattern = strtolower($pattern);

    $n = strlen($text);
    $m = strlen($pattern);

    for ($i = 0; $i <= $n - $m; $i++) {
        $match = true;

        for ($j = 0; $j < $m; $j++) {
            if ($text[$i + $j] !== $pattern[$j]) {
                $match = false;
                break;
            }
        }

        if ($match) {
            return true;
        }
    }
    return false;
}

$matches = [];
while ($row = mysqli_fetch_assoc($result)) {
    if (manualMatch($row['name'], $q)) {
        $matches[] = $row;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Search Results</title>
</head>
<body>

<h2>Search results for "<?php echo htmlspecialchars($q); ?>"</h2>

<?php if (count($matches) > 0): ?>
    <?php foreach ($matches as $p): ?>
        <div style="margin-bottom:15px;">
            <strong><?php echo htmlspecialchars($p['name']); ?></strong><br>
            Price: Rs <?php echo htmlspecialchars($p['price']); ?>
        </div>
    <?php endforeach; ?>
<?php else: ?>
    <p>No products found</p>
<?php endif; ?>

</body>
</html>
