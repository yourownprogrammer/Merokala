<?php
session_start();
require "../php/dbconnection.php";

/* ---------- PROVIDER AUTH CHECK ---------- */
if (!isset($_SESSION['provider_id'])) {
    header("Location: providersignup.php");
    exit;
}

$provider_id = (int) $_SESSION['provider_id'];

/* ---------- CHECK PROVIDER STATUS ---------- */
$stmt = $conn->prepare("SELECT status FROM providers WHERE id = ?");
$stmt->bind_param("i", $provider_id);
$stmt->execute();
$provider = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$provider || $provider['status'] !== 'approved') {
    die("Access denied. Your account is not approved to list products.");
}

/* ---------- LOAD CATEGORIES ---------- */
$categories = $conn->query("SELECT id, name FROM categories ORDER BY name");

/* ---------- FORM SUBMIT ---------- */
$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    /* ---------- BASIC VALIDATION ---------- */
    if (empty($_POST['category_id'])) {
        die("Category is required.");
    }

    $name        = trim($_POST["name"]);
    $description = trim($_POST["description"]);
    $price       = (float) $_POST["price"];
    $category_id = (int) $_POST["category_id"];

    /* ---------- IMAGE UPLOAD ---------- */
    if (empty($_FILES["image"]["name"])) {
        die("Product image is required.");
    }

    $ext = strtolower(pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION));
    $imageName = uniqid("prod_") . "." . $ext;

    move_uploaded_file(
        $_FILES["image"]["tmp_name"],
        "../uploads/" . $imageName
    );

    /* ---------- INSERT PRODUCT (PENDING) ---------- */
    $stmt = $conn->prepare("
        INSERT INTO products
        (name, description, image, price, provider_id, status, created_at)
        VALUES (?, ?, ?, ?, ?, 'pending', NOW())
    ");
    $stmt->bind_param(
        "sssdi",
        $name,
        $description,
        $imageName,
        $price,
        $provider_id
    );
    $stmt->execute();

    $product_id = $conn->insert_id;
    $stmt->close();

    /* ---------- LINK PRODUCT → CATEGORY ---------- */
    $link = $conn->prepare("
        INSERT INTO product_categories (product_id, category_id)
        VALUES (?, ?)
    ");
    $link->bind_param("ii", $product_id, $category_id);
    $link->execute();
    $link->close();

    $message = "Product submitted for admin review.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>List Product – Provider</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<style>
body {
    font-family: Arial, sans-serif;
    background:#f6f7f9;
    padding:20px;
}
form {
    background:#fff;
    max-width:600px;
    padding:20px;
    border-radius:8px;
}
label {
    display:block;
    margin-top:12px;
    font-weight:600;
}
input, textarea, select {
    width:100%;
    padding:10px;
    margin-top:6px;
}
button {
    margin-top:15px;
    padding:10px 20px;
    background:#ff7a00;
    color:#fff;
    border:none;
    border-radius:6px;
    cursor:pointer;
}
.success {
    color:green;
    margin-bottom:10px;
}
</style>
</head>
<body>

<h1>List Your Product</h1>

<?php if ($message): ?>
<p class="success"><?= htmlspecialchars($message) ?></p>
<?php endif; ?>

<form method="POST" enctype="multipart/form-data">

    <label>Product Name</label>
    <input type="text" name="name" required>

    <label>Description</label>
    <textarea name="description" rows="4" required></textarea>

    <label>Price (Rs.)</label>
    <input type="number" step="0.01" name="price" required>

    <label>Category</label>
    <select name="category_id" required>
        <option value="">-- Select Category --</option>
        <?php while ($c = $categories->fetch_assoc()): ?>
            <option value="<?= $c['id'] ?>">
                <?= htmlspecialchars($c['name']) ?>
            </option>
        <?php endwhile; ?>
    </select>

    <label>Product Image</label>
    <input type="file" name="image" accept="image/*" required>

    <button type="submit">Submit for Review</button>
</form>

<br>
<a href="providerdash.php">Back to Dashboard</a>

</body>
</html>
