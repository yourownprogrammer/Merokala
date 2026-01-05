<?php
session_start();
require "../php/dbconnection.php";

/* ===== AUTH GUARD ===== */
if (!isset($_SESSION['user_id'])) {
    header("Location: mainlogin.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$message = "";

/* ===== UPDATE NAME ===== */
if (isset($_POST['save_name'])) {
    $new_name = trim($_POST['name']);

    if (!empty($new_name)) {
        $stmt = $conn->prepare("
            UPDATE users 
            SET name = ?
            WHERE id = ?
        ");
        $stmt->bind_param("si", $new_name, $user_id);
        $stmt->execute();
        $stmt->close();

        $message = "Name updated successfully.";
    }
}

/* ===== FETCH USER ===== */
$stmt = $conn->prepare("
    SELECT name, email
    FROM users
    WHERE id = ?
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

$userName  = htmlspecialchars($user['name']);
$userEmail = htmlspecialchars($user['email']);

$editMode = isset($_GET['edit']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>User Dashboard</title>

<link rel="stylesheet" href="../css/htm.css">
</head>

<body>

<header class="logo-header">
    <a href="../homepage.php" class="logo">Merokala</a>
</header>

<main class="dashboard">

    <h1>Welcome, <?= $userName ?></h1>

    <section class="profile">

        <div class="field">
            <label>Email</label>
            <input type="email" value="<?= $userEmail ?>" disabled>
            <small>Email cannot be changed once registered.</small>
        </div>

        <div class="field">
            <label>Name</label>

            <?php if ($editMode): ?>
                <form method="POST">
                    <input type="text" name="name" value="<?= $userName ?>" required>
                    <button type="submit" name="save_name">Save</button>
                </form>
            <?php else: ?>
                <input type="text" value="<?= $userName ?>" disabled>
                <a href="?edit=1">Edit Name</a>
            <?php endif; ?>
        </div>

        <?php if ($message): ?>
            <p class="success"><?= $message ?></p>
        <?php endif; ?>

    </section>

    <a href="logout.php">Logout</a>

</main>

</body>
</html>
