<?php
require "dbconnection.php";
session_start();

/* ---------- ADMIN AUTH CHECK ---------- */
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit;
}

/* ---------- LOAD CATEGORIES ---------- */
$categories = $conn->query("SELECT id, name FROM categories ORDER BY name");

/* ---------- FORM SUBMIT ---------- */
$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $name        = trim($_POST["name"]);
    $description = trim($_POST["description"]);
    $price       = (float) $_POST["price"];
    $category_id = (int) $_POST["category_id"];

    /* ---------- IMAGE UPLOAD ---------- */
    $imageName = null;
    if (!empty($_FILES["image"]["name"])) {
        $ext = strtolower(pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION));
        $imageName = uniqid("prod_") . "." . $ext;
        move_uploaded_file(
            $_FILES["image"]["tmp_name"],
            "../uploads/" . $imageName
        );
    }

    /* ---------- INSERT PRODUCT ---------- */
    $stmt = $conn->prepare("
        INSERT INTO products
        (name, description, image, price, uploaded_by, provider_id, status, created_at)
        VALUES (?, ?, ?, ?, 'admin', NULL, 'approved', NOW())
    ");
    $stmt->bind_param("sssd", $name, $description, $imageName, $price);
    $stmt->execute();

    $product_id = $conn->insert_id;

    /* ---------- LINK PRODUCT → CATEGORY ---------- */
    $link = $conn->prepare("
        INSERT INTO product_categories (product_id, category_id)
        VALUES (?, ?)
    ");
    $link->bind_param("ii", $product_id, $category_id);
    $link->execute();

    $message = "Product added successfully.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Add Product – Admin</title>
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
    background:#28a745;
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

<h1>Add Product (Admin)</h1>

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
    <input type="file" name="image" accept="image/*">

    <button type="submit">Add Product</button>
</form>

</body>
</html>
