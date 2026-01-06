<?php
require "php/dbconnection.php";

$q = trim($_GET['q'] ?? '');

if (strlen($q) < 2) {
    exit;
}

/* FETCH PRODUCTS */
$result = mysqli_query(
    $conn,
    "SELECT id, name FROM products WHERE status='approved'"
);

/* MANUAL STRING MATCH */
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

/* OUTPUT SUGGESTIONS */
while ($row = mysqli_fetch_assoc($result)) {
    if (manualMatch($row['name'], $q)) {
        echo "<div class=\"suggestion\" data-id=\"{$row['id']}\">"
           . htmlspecialchars($row['name'])
           . "</div>";
    }
}
