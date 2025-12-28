<?php
$prefillEmail = "";
if (isset($_GET['email'])) {
    $prefillEmail = htmlspecialchars($_GET['email']);
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
/* ===== FORM LAYOUT FIX ===== */
.form-box {
    width: 100%;
}

/* Force vertical stacking */
.login-form {
    display: flex;
    flex-direction: column;
    gap: 14px;
}

/* Match widths */
.login-form .input-field,
.login-form .continue-btn {
    width: 100%;
    box-sizing: border-box;
}

/* Input styling safety (in case css file overrides) */
.input-field {
    padding: 14px;
    border-radius: 14px;
    border: 1px solid #ccc;
    font-size: 16px;
}

/* Button styling safety */
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
</style>


</head>
<body>
<header class="logo-header">
    <a href="../hmt.html" class="logo">Merokala</a>
</header>

<section class="login-area">
    <div class="login-container">

        <h2 class="title">Sign in to your account</h2>

        <div class="new-box">
            <span>New to our site?</span>
            <a href="usignup.php" class="create-btn">Create account</a>
        </div>
<div class="form-box">
    <form action="login_method.php" method="GET" class="login-form">
        <input
            type="text"
            name="email"
            placeholder="Email or username"
            class="input-field"
            value="<?= $prefillEmail ?>"
            required
        >

        <button type="submit" class="continue-btn">
            Continue
        </button>
    </form>
</div>

    </div>
</section>
</form>
</body>
</html>
