<?php
session_start();
require "../php/dbconnection.php";

/* ===== AUTH GUARD ===== */
if (!isset($_SESSION['user_id'])) {
    header("Location: mainlogin.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$success = "";
$error   = "";

/* ===== UPDATE NAME ===== */
if (isset($_POST['save_name'])) {
    $new_name = trim($_POST['name']);

    if (empty($new_name)) {
        $error = "Name cannot be empty.";
    } elseif (!preg_match("/^[a-zA-Z\s]{3,50}$/", $new_name)) {
        $error = "Name must be 3–50 characters and contain only letters and spaces.";
    } else {
        $stmt = $conn->prepare("UPDATE users SET name = ? WHERE id = ?");
        $stmt->bind_param("si", $new_name, $user_id);
        $stmt->execute();
        $stmt->close();
        $success = "Name updated successfully.";
    }
}

/* ===== FETCH USER ===== */
$stmt = $conn->prepare("SELECT name, email FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

$userName  = htmlspecialchars($user['name'], ENT_QUOTES, 'UTF-8');
$userEmail = htmlspecialchars($user['email'], ENT_QUOTES, 'UTF-8');
$editMode  = isset($_GET['edit']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>User Dashboard</title>

<style>
* { box-sizing: border-box; font-family: Arial, sans-serif; }
body { margin: 0; background: #f4f6f9; color: #333; }

/* ===== HEADER (HOMEPAGE ALIGNED) ===== */
.header {
    background: #ffffff;
    border-bottom: 1px solid #eee;
}

.header-inner {
    max-width: 1300px;
    margin: 0 auto;
    padding: 14px 30px;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.logo {
    font-size: 28px;
    font-weight: 700;
    color: #ff7a00;
    text-decoration: none;
}

.logout {
    font-size: 15px;
    text-decoration: none;
    color: #ff7a00;
}

/* ===== CONTENT ===== */
.container {
    display: flex;
    justify-content: center;
    padding: 60px 20px;
}

.card {
    background: #fff;
    width: 100%;
    max-width: 520px;
    padding: 32px;
    border-radius: 10px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.08);
}

.card h1 {
    margin: 0 0 6px;
    font-size: 24px;
}

.subtitle {
    font-size: 14px;
    color: #666;
    margin-bottom: 24px;
}

/* Alerts */
.alert {
    padding: 12px 14px;
    border-radius: 6px;
    margin-bottom: 18px;
    font-size: 14px;
}

.success { background: #e6f6ec; color: #207544; }
.error   { background: #fdecea; color: #b3261e; }

/* Fields */
.field { margin-bottom: 22px; }

.field label {
    display: block;
    font-size: 14px;
    margin-bottom: 6px;
    font-weight: bold;
}

.field input {
    width: 100%;
    padding: 10px 12px;
    font-size: 14px;
    border-radius: 6px;
    border: 1px solid #ccc;
}

.field input:disabled {
    background: #f1f1f1;
}

.field small {
    font-size: 12px;
    color: #888;
}

/* Actions */
.actions {
    display: flex;
    gap: 12px;
    margin-top: 12px;
}

button {
    background: #111;
    color: #fff;
    border: none;
    padding: 10px 18px;
    border-radius: 6px;
    cursor: pointer;
}

.cancel {
    text-decoration: none;
    color: #555;
    padding: 10px 14px;
}

.edit-link {
    display: inline-block;
    margin-top: 8px;
    font-size: 13px;
    color: #0066cc;
    text-decoration: none;
}
</style>
</head>

<body>

<!-- HEADER -->
<header class="header">
    <div class="header-inner">
        <a href="../homepage.php" class="logo">Merokala</a>
        <a href="logout.php" class="logout">Logout</a>
    </div>
</header>

<!-- CONTENT -->
<main class="container">
    <div class="card">
        <h1>Account Settings</h1>
        <p class="subtitle">Manage your personal information</p>

        <?php if ($success): ?>
            <div class="alert success"><?= $success ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert error"><?= $error ?></div>
        <?php endif; ?>

        <div class="field">
            <label>Email Address</label>
            <input type="email" value="<?= $userEmail ?>" disabled>
            <small>Email cannot be changed.</small>
        </div>

        <div class="field">
            <label>Full Name</label>

            <?php if ($editMode): ?>
                <form method="POST" novalidate>
                    <input
                        type="text"
                        name="name"
                        value="<?= $userName ?>"
                        required
                        minlength="3"
                        maxlength="50"
                        pattern="[A-Za-z\s]+"
                    >
                    <div class="actions">
                        <button type="submit" name="save_name">Save Changes</button>
                        <a href="user_dashboard.php" class="cancel">Cancel</a>
                    </div>
                </form>
            <?php else: ?>
                <input type="text" value="<?= $userName ?>" disabled>
                <a href="?edit=1" class="edit-link">Edit Name</a>
            <?php endif; ?>
        </div>

        <hr style="margin:25px 0;">

<div class="field">
    <label>Orders</label>
    <p style="font-size:14px;color:#666;margin-bottom:10px;">
        View your past purchases and order details.
    </p>

    <a href="user_orders.php" style="
        display:inline-block;
        background:#111;
        color:#fff;
        padding:10px 18px;
        border-radius:6px;
        text-decoration:none;
        font-size:14px;
    ">
        View Order History
    </a>
</div>

    </div>

    
</main>

</body>
</html>
