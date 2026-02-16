<?php
session_start();
require "dbconnection.php";

/* ===== IF ALREADY LOGGED IN ===== */
if (isset($_SESSION['user_id'])) {
    header("Location: user_dashboard.php");
    exit;
}

$prefillEmail = "";
$error = "";

if (isset($_GET['email'])) {
    $prefillEmail = htmlspecialchars($_GET['email']);

    $email = trim($_GET['email']);

    /* ===== CHECK IF EMAIL EXISTS ===== */
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 0) {
        $error = "Account not found. Please create an account.";
    } else {
        header("Location: login_method.php?email=" . urlencode($email));
        exit;
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Sign In</title>

<link rel="stylesheet" href="../css/htm.css">
<link rel="stylesheet" href="../css/mainlogin.css">

<style>
.form-box { width: 100%; }

.login-form {
    display: flex;
    flex-direction: column;
    gap: 14px;
}

.login-form .input-field,
.login-form .continue-btn {
    width: 100%;
    box-sizing: border-box;
}

.input-field {
    padding: 14px;
    border-radius: 14px;
    border: 1px solid #ccc;
    font-size: 16px;
}

.continue-btn {
    padding: 16px;
    border-radius: 999px;
    border: none;
    background: #ff7a00;
    color: #fff;
    font-size: 18px;
    font-weight: 600;
    cursor: pointer;
    transition: 0.2s ease;
}

.continue-btn:hover {
    background: #e56d00;
}

.error-msg {
    color: #d63031;
    font-size: 14px;
    margin-top: 6px;
}
</style>
</head>

<body>

<header class="logo-header">
    <a href="../homepage.php" class="logo">Merokala</a>
</header>

<section class="login-area">
    <div class="login-container">

        <h2 class="title">Sign in to your account</h2>

        <div class="new-box">
            <span>New to our site?</span>
            <a href="usignup.php" class="create-btn">Create account</a>
        </div>

        <div class="form-box">
            <form method="GET" class="login-form">
                <input
                    type="text"
                    name="email"
                    placeholder="Email or username"
                    class="input-field"
                    value="<?= $prefillEmail ?>"
                    required
                >

                <?php if ($error !== ""): ?>
                    <div class="error-msg"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>

                <button type="submit" class="continue-btn">
                    Continue
                </button>
            </form>
        </div>

    </div>
</section>

</body>
</html>
