<?php
// ===== EMAIL GUARD =====
if (!isset($_GET['email']) || trim($_GET['email']) === "") {
    header("Location: mainlogin.php");
    exit;
}

$email = htmlspecialchars($_GET['email']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Choose Login Method</title>

    <link rel="stylesheet" href="../css/htm.css">
    <link rel="stylesheet" href="../css/mainlogin.css">

    <style>
        /* ===== LAYOUT ===== */
        .method-section {
            display: flex;
            flex-direction: column;
            gap: 18px;
            width: 100%;
            margin-top: 10px;
        }

        .email-text {
            font-size: 16px;
            margin-bottom: 15px;
            color: #444;
        }

        /* ===== PASSWORD ===== */
        .continue-final-btn {
            padding: 15px;
            background: #ff7a00;
            color: #fff;
            font-size: 17px;
            font-weight: 600;
            border: none;
            border-radius: 30px;
            cursor: pointer;
            transition: 0.25s ease-in-out;
            width: 100%;
        }

        .continue-final-btn:hover {
            background: #e56d00;
            transform: translateY(-2px);
        }

        /* ===== OR TEXT ===== */
        .small-or {
            text-align: center;
            font-size: 16px;
            font-weight: 700;
            color: #444;
        }

        .subtext {
            text-align: center;
            font-size: 15px;
            color: #666;
            margin-top: -6px;
        }

        /* ===== OTP UI ===== */
        .otp-row {
            display: flex;
            width: 100%;
        }

        .otp-input {
            flex: 1;
            padding: 15px;
            font-size: 16px;
            border: 1px solid #ccc;
            border-radius: 10px 0 0 10px;
            border-right: none;
            box-sizing: border-box;
        }

        .send-btn {
            width: 110px;
            background: #ff7a00;
            color: #fff;
            border: 1px solid #ff7a00;
            border-radius: 0 10px 10px 0;
            font-size: 15px;
            font-weight: 600;
            cursor: not-allowed;
            opacity: 0.6;
        }
    </style>
</head>

<body>

<header class="logo-header">
    <a href="../hmt.html" class="logo">Merokala</a>
</header>

<section class="login-area">
    <div class="login-container">

        <h2 class="title">Choose a login method</h2>

        <p class="email-text">
            Logging in as <b><?= $email ?></b>
        </p>

        <div class="method-section">

            <!-- ===== PASSWORD LOGIN ===== -->
            <form action="login_process.php" method="POST">
                <input type="hidden" name="email" value="<?= $email ?>">

                <input
                    type="password"
                    name="password"
                    placeholder="Enter your password"
                    class="input-field"
                    required
                >

                <button type="submit" class="continue-final-btn">
                    Login
                </button>
            </form>

            <!-- ===== OTP SECTION (UI ONLY) ===== -->
            <div class="small-or">OR</div>
            <div class="subtext">Login through OTP</div>

            <div class="otp-row">
                <input
                    type="text"
                    placeholder="Enter OTP"
                    class="otp-input"
                    disabled
                >
                <button class="send-btn" disabled>
                    Send
                </button>
            </div>

        </div>

    </div>
</section>

</body>
</html>
