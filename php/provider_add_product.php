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
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $name        = trim($_POST["name"]);
    $description = trim($_POST["description"]);
    $price       = $_POST["price"];
    $category_id = (int) $_POST["category_id"];

    /* ---------- VALIDATION ---------- */

    if ($name === "" || $description === "" || $price === "" || !$category_id) {
        die("All fields are required.");
    }

    if (!preg_match("/^[a-zA-Z0-9\s\-]{3,100}$/", $name)) {
        die("Product name contains invalid characters.");
    }

    if (strlen($description) < 10 || strlen($description) > 1000) {
        die("Description must be between 10 and 1000 characters.");
    }

    if (!is_numeric($price) || $price <= 0) {
        die("Price must be a positive number.");
    }

    if (empty($_FILES["image"]["name"])) {
        die("Product image is required.");
    }

    /* ---------- IMAGE VALIDATION ---------- */

    $allowed_types = ["image/jpeg", "image/png", "image/webp"];
    $file_type = mime_content_type($_FILES["image"]["tmp_name"]);

    if (!in_array($file_type, $allowed_types)) {
        die("Only JPG, PNG or WEBP images are allowed.");
    }

    if ($_FILES["image"]["size"] > 2 * 1024 * 1024) {
        die("Image must be less than 2MB.");
    }

    /* ---------- IMAGE UPLOAD ---------- */
    $ext = pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION);
    $imageName = uniqid("prod_", true) . "." . $ext;

    move_uploaded_file(
        $_FILES["image"]["tmp_name"],
        "../uploads/" . $imageName
    );

    /* ---------- INSERT PRODUCT ---------- */
    $stmt = $conn->prepare("
        INSERT INTO products
        (name, description, image, price, provider_id, status, created_at)
        VALUES (?, ?, ?, ?, ?, 'pending', NOW())
    ");
    $stmt->bind_param("sssdi", $name, $description, $imageName, $price, $provider_id);
    $stmt->execute();
    $product_id = $conn->insert_id;
    $stmt->close();

    /* ---------- LINK CATEGORY ---------- */
    $link = $conn->prepare("
        INSERT INTO product_categories (product_id, category_id)
        VALUES (?, ?)
    ");
    $link->bind_param("ii", $product_id, $category_id);
    $link->execute();
    $link->close();

    header("Location: provider_add_product.php?success=1");
    exit;
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
    background: linear-gradient(135deg, #eef2f7, #f8fafc);
    margin: 0;
}

.container {
    max-width: 680px;
    margin: 30px auto; /* anchored from top */
    padding: 0 20px;
}

h1 {
    margin-bottom: 25px;
    font-size: 28px;
    font-weight: 700;
}

form {
    background: #ffffff;
    padding: 28px;
    border-radius: 16px;
    box-shadow: 0 20px 45px rgba(0,0,0,0.07);
}

label {
    display: block;
    margin-top: 14px;
    font-weight: 600;
    font-size: 14px;
    color: #374151;
}

input, textarea, select {
    width: 100%;
    padding: 12px 14px;
    margin-top: 6px;
    border-radius: 10px;
    border: 1px solid #d1d5db;
    font-size: 14px;
    transition: all 0.2s ease;
    background: #f9fafb;
}

input:focus, textarea:focus, select:focus {
    outline: none;
    border: 2px solid #ff7a00;
    background: #fff;
    box-shadow: 0 0 10px rgba(255,122,0,0.25);
}

textarea {
    resize: vertical;
}

button {
    margin-top: 18px;
    padding: 11px 22px;
    background: linear-gradient(135deg, #ff7a00, #ff5200);
    color: #fff;
    border: none;
    border-radius: 12px;
    cursor: pointer;
    font-size: 14px;
    font-weight: 600;
    transition: all 0.25s ease;
}

button:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 20px rgba(255,122,0,0.35);
}

button:disabled {
    background: #cfcfcf;
    cursor: not-allowed;
    box-shadow: none;
}

.back-link {
    display: inline-block;
    margin-top: 16px;
    font-size: 13px;
    color: #2563eb;
    text-decoration: none;
}


</style>
</head>
<body>

<div class="page">
    <div class="container">
<h1>List Your Product</h1>

<?php if (isset($_GET['success'])): ?>
<p class="success">Product submitted for admin review.</p>
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
  <a href="providerdash.php" class="back-link">← Back to Dashboard</a>
    </div>
</div>


<script>
document.addEventListener("DOMContentLoaded", function () {

    const name = document.querySelector("input[name='name']");
    const description = document.querySelector("textarea[name='description']");
    const price = document.querySelector("input[name='price']");
    const submitBtn = document.querySelector("button[type='submit']");

    function validateName(v) {
        return /^[A-Za-z0-9\s\-]{3,100}$/.test(v);
    }

    function validateDescription(v) {
        return v.length >= 10 && v.length <= 1000;
    }

    function validatePrice(v) {
        return !isNaN(v) && v > 0;
    }

    function updateField(input, valid) {
        if (valid) {
            input.style.border = "2px solid #16a34a";
            input.style.boxShadow = "0 0 6px rgba(22,163,74,0.4)";
        } else {
            input.style.border = "2px solid #dc2626";
            input.style.boxShadow = "0 0 6px rgba(220,38,38,0.4)";
        }
    }

    function checkForm() {
        const valid =
            validateName(name.value) &&
            validateDescription(description.value) &&
            validatePrice(price.value);

        submitBtn.disabled = !valid;
        submitBtn.style.opacity = valid ? "1" : "0.6";
        submitBtn.style.cursor = valid ? "pointer" : "not-allowed";
    }

    name.addEventListener("input", function () {
        updateField(name, validateName(name.value));
        checkForm();
    });

    description.addEventListener("input", function () {
        updateField(description, validateDescription(description.value));
        checkForm();
    });

    price.addEventListener("input", function () {
        updateField(price, validatePrice(price.value));
        checkForm();
    });

    checkForm();
});
</script>

</body>
</html>
