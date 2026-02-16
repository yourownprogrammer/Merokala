<?php
session_start();
require "../php/dbconnection.php";

if (!isset($_SESSION['provider_id'])) {
    header("Location: providersignup.php");
    exit;
}

$provider_id = $_SESSION['provider_id'];
$success = "";
$error = "";

/* ===== UPDATE (ONLY WHEN SUBMITTED) ===== */
if (isset($_POST['update_profile'])) {

    $first_name = trim($_POST['first_name']);
    $last_name  = trim($_POST['last_name']);
    $skill      = trim($_POST['primary_skill']);
    $location   = trim($_POST['location']);

    if ($first_name === "" || $last_name === "" || $skill === "" || $location === "") {
        $error = "All fields are required.";
    }
    elseif (!preg_match("/^[a-zA-Z\s]{2,50}$/", $first_name)) {
        $error = "First name must contain only letters (2–50 characters).";
    }
    elseif (!preg_match("/^[a-zA-Z\s]{2,50}$/", $last_name)) {
        $error = "Last name must contain only letters (2–50 characters).";
    }
    elseif (!preg_match("/^[a-zA-Z\s\-]{2,100}$/", $skill)) {
        $error = "Primary skill must contain only letters and spaces.";
    }
    elseif (!preg_match("/^[a-zA-Z\s,]{2,100}$/", $location)) {
        $error = "Location contains invalid characters.";
    }
    else {
        $stmt = $conn->prepare("
            UPDATE providers 
            SET first_name = ?, last_name = ?, primary_skill = ?, location = ?
            WHERE id = ?
        ");
        $stmt->bind_param("ssssi", $first_name, $last_name, $skill, $location, $provider_id);
        $stmt->execute();
        $stmt->close();

        header("Location: providerdash.php?updated=1");
        exit;
    }
}


/* ===== FETCH PROVIDER ===== */
$stmt = $conn->prepare("
    SELECT first_name, last_name, email, phone, primary_skill, location, status
    FROM providers
    WHERE id = ?
");
$stmt->bind_param("i", $provider_id);
$stmt->execute();
$provider = $stmt->get_result()->fetch_assoc();
$stmt->close();

$editMode = isset($_GET['edit']);
$status   = $provider['status'];
$fullName = htmlspecialchars($provider['first_name']." ".$provider['last_name']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Provider Dashboard</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<style>
*{box-sizing:border-box;font-family:Arial,sans-serif}
body{margin:0;background:#f6f7fb;color:#1f2937}
.header{background:#fff;border-bottom:1px solid #e5e7eb}
.header-inner{max-width:1200px;margin:auto;padding:16px 28px;display:flex;justify-content:space-between;align-items:center}
.logo{font-size:28px;font-weight:800;color:#ff7a00;text-decoration:none}
.logout{color:#ff7a00;text-decoration:none;font-size:14px}

.container{max-width:1100px;margin:60px auto;padding:0 20px}
.hero h1{font-size:32px;margin:0 0 6px}
.hero p{color:#6b7280;margin:0}

.grid{display:grid;grid-template-columns:1.2fr 1fr;gap:30px;margin-top:30px}
.card{background:#fff;border-radius:14px;padding:28px;box-shadow:0 20px 40px rgba(0,0,0,.06)}
.card h2{margin-top:0}

.detail{margin-bottom:12px;font-size:14px}
.detail span{display:block;color:#6b7280;font-size:12px}

.form-group{margin-bottom:14px}
.form-group label{font-size:12px;color:#6b7280}
.form-group input{width:100%;padding:10px;border-radius:8px;border:1px solid #d1d5db}
.form-group input:disabled{background:#f3f4f6}

.actions{margin-top:16px;display:flex;gap:12px}
.btn{padding:10px 18px;border-radius:10px;background:#111827;color:#fff;border:none;cursor:pointer;text-decoration:none;font-size:14px}
.btn.orange{background:#ff7a00}
.link{color:#2563eb;text-decoration:none;font-size:14px}

.status-box{padding:18px;border-radius:12px;font-size:14px}
.approved{background:#ecfdf5;color:#065f46}
.pending{background:#fff7ed;color:#9a3412}
.blocked{background:#fef2f2;color:#991b1b}

.alert{padding:12px 14px;border-radius:8px;margin-bottom:16px;font-size:14px}
.success{background:#ecfdf5;color:#065f46}
.error{background:#fef2f2;color:#991b1b}

@media(max-width:900px){.grid{grid-template-columns:1fr}}
</style>
</head>

<body>

<header class="header">
    <div class="header-inner">
        <a href="../homepage.php" class="logo">Merokala</a>
        <a href="providerlogout.php" class="logout">Logout</a>
    </div>
</header>

<main class="container">

<div class="hero">
    <h1>Welcome, <?= htmlspecialchars($provider['first_name']) ?></h1>
    <p>Provider Dashboard</p>
</div>

<?php if (isset($_GET['updated'])): ?>
<div class="alert success">Profile updated successfully.</div>
<?php endif; ?>

<?php if ($error): ?>
<div class="alert error"><?= $error ?></div>
<?php endif; ?>

<div class="grid">

<!-- PROFILE -->
<div class="card">
<h2>Your Profile</h2>

<?php if (!$editMode): ?>
    <div class="detail"><span>Name</span><?= $fullName ?></div>
    <div class="detail"><span>Email</span><?= htmlspecialchars($provider['email']) ?></div>
    <div class="detail"><span>Phone</span><?= htmlspecialchars($provider['phone']) ?></div>
    <div class="detail"><span>Primary Skill</span><?= htmlspecialchars($provider['primary_skill']) ?></div>
    <div class="detail"><span>Location</span><?= htmlspecialchars($provider['location']) ?></div>

    <div class="actions">
        <a href="providerdash.php?edit=1" class="btn orange">Edit Profile</a>
    </div>

<?php else: ?>
<form method="POST">
    <div class="form-group">
        <label>First Name</label>
        <input type="text" name="first_name" value="<?= htmlspecialchars($provider['first_name']) ?>" required>
    </div>

    <div class="form-group">
        <label>Last Name</label>
        <input type="text" name="last_name" value="<?= htmlspecialchars($provider['last_name']) ?>" required>
    </div>

    <div class="form-group">
        <label>Email (locked)</label>
        <input type="email" value="<?= htmlspecialchars($provider['email']) ?>" disabled>
    </div>

    <div class="form-group">
    <label>Phone (locked)</label>
    <input type="text"
        value="<?= htmlspecialchars($provider['phone']) ?>"
        disabled>
</div>


    <div class="form-group">
        <label>Primary Skill</label>
        <input type="text" name="primary_skill" value="<?= htmlspecialchars($provider['primary_skill']) ?>" required>
    </div>

<div class="form-group">
    <label>Location</label>
    <input type="text" name="location" 
        value="<?= htmlspecialchars($provider['location']) ?>" 
        required>
</div>


    <div class="actions">
        <button type="submit" name="update_profile" class="btn orange">Save</button>
        <a href="providerdash.php" class="btn">Cancel</a>
    </div>
</form>
<?php endif; ?>
</div>

<!-- STATUS -->
<div class="card">
<h2>Account Status</h2>

<?php if ($status === 'approved'): ?>

    <div class="status-box approved">
        Your account is approved. You can now list products.
    </div>

    <div class="actions" style="margin-top:15px;">
        <a href="provider_add_product.php" class="btn orange">
            List Product
        </a>
    </div>

<?php elseif ($status === 'pending'): ?>

    <div class="status-box pending">
        Your account is pending admin approval.
        <br><br>
        You will be able to list products once your account is approved.
    </div>

<?php else: ?>

    <div class="status-box blocked">
        Your account is blocked by admin.
        <br><br>
        Listing products is currently disabled.
    </div>

<?php endif; ?>

<a href="provider_dash_history.php" class="link">View Submission History</a>

</div>


</div>

</main>

<script>
document.addEventListener("DOMContentLoaded", function () {

    const firstName = document.querySelector("input[name='first_name']");
    const lastName = document.querySelector("input[name='last_name']");
    const skill = document.querySelector("input[name='primary_skill']");
    const location = document.querySelector("input[name='location']");
    const saveBtn = document.querySelector("button[name='update_profile']");

    function validateName(value) {
        return /^[A-Za-z\s]{2,50}$/.test(value);
    }

    function validateSkill(value) {
        return /^[A-Za-z\s\-]{2,100}$/.test(value);
    }

    function validateLocation(value) {
        return /^[A-Za-z\s,]{2,100}$/.test(value);
    }

    function updateFieldState(input, isValid) {
    if (isValid) {
        input.style.border = "4px solid #16a34a";
        input.style.boxShadow = "0 0 6px rgba(22,163,74,0.4)";
    } else {
        input.style.border = "4px solid #dc2626";
        input.style.boxShadow = "0 0 6px rgba(220,38,38,0.4)";
    }
}

    function checkFormValidity() {
        const valid =
            validateName(firstName.value) &&
            validateName(lastName.value) &&
            validateSkill(skill.value) &&
            validateLocation(location.value);

        saveBtn.disabled = !valid;
        saveBtn.style.opacity = valid ? "1" : "0.6";
        saveBtn.style.cursor = valid ? "pointer" : "not-allowed";
    }

    firstName.addEventListener("input", function () {
        updateFieldState(firstName, validateName(firstName.value));
        checkFormValidity();
    });

    lastName.addEventListener("input", function () {
        updateFieldState(lastName, validateName(lastName.value));
        checkFormValidity();
    });

    skill.addEventListener("input", function () {
        updateFieldState(skill, validateSkill(skill.value));
        checkFormValidity();
    });

    location.addEventListener("input", function () {
        updateFieldState(location, validateLocation(location.value));
        checkFormValidity();
    });

    checkFormValidity();
});
</script>

</body>
</html>
