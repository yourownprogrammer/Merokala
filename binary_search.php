<?php
require "php/dbconnection.php";

$q = trim($_GET['q'] ?? '');

if ($q === '') {
    echo "Search term required";
    exit;
}

/* ---------------- FETCH PRODUCTS ---------------- */
$result = mysqli_query(
    $conn,
    "SELECT id, name, price FROM products WHERE status='approved'"
);

$products = [];
while ($row = mysqli_fetch_assoc($result)) {
    $products[] = $row;
}

/* ---------------- MERGE SORT ---------------- */
function mergeSort($arr) {
    if (count($arr) <= 1) {
        return $arr;
    }

    $mid = floor(count($arr) / 2);
    $left  = array_slice($arr, 0, $mid);
    $right = array_slice($arr, $mid);

    return merge(
        mergeSort($left),
        mergeSort($right)
    );
}

function merge($left, $right) {
    $result = [];
    $i = $j = 0;

    while ($i < count($left) && $j < count($right)) {
        if (strtolower($left[$i]['name']) <= strtolower($right[$j]['name'])) {
            $result[] = $left[$i];
            $i++;
        } else {
            $result[] = $right[$j];
            $j++;
        }
    }

    while ($i < count($left)) {
        $result[] = $left[$i++];
    }

    while ($j < count($right)) {
        $result[] = $right[$j++];
    }

    return $result;
}

/* ---------------- BINARY SEARCH ---------------- */
function binarySearch($arr, $key) {
    $low = 0;
    $high = count($arr) - 1;
    $key = strtolower($key);

    while ($low <= $high) {
        $mid = floor(($low + $high) / 2);
        $midName = strtolower($arr[$mid]['name']);

        if ($midName === $key) {
            return $arr[$mid];
        }

        if ($midName < $key) {
            $low = $mid + 1;
        } else {
            $high = $mid - 1;
        }
    }

    return null;
}

/* ---------------- PROCESS ---------------- */
$sortedProducts = mergeSort($products);

//merge sort use bhako dekhauna yo
// echo "<h3>Products after Merge Sort (A → Z)</h3>";
// echo "<pre>";
// foreach ($sortedProducts as $p) {
//     echo $p['name'] . "\n";
// }
// echo "</pre>";
// exit;


$found = binarySearch($sortedProducts, $q);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Binary Search Result</title>
</head>
<body>

<h2>Exact Search Result</h2>

<?php if ($found): ?>
    <div>
        <strong><?php echo htmlspecialchars($found['name']); ?></strong><br>
        Price: Rs <?php echo htmlspecialchars($found['price']); ?>
    </div>
<?php else: ?>
    <p>Product not found</p>
<?php endif; ?>

</body>
</html>
