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
            gap: 0;
            width: 100%;
            margin-top: 10px;
        }

        .method-section form {
            display: flex;
            flex-direction: column;
            gap: 12px;
            width: 100%;
        }

        .method-section .input-field {
            width: 100%;
            margin: 0;
            box-sizing: border-box;
        }

        .email-text {
            font-size: 16px;
            margin-bottom: 18px;
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

        .password-note {
            font-size: 14px;
            color: #666;
            margin: 0;
            line-height: 1.4;
        }
    </style>
</head>

<body>

<header class="logo-header">
    <a href="../hmt.html" class="logo">Merokala</a>
</header>

<section class="login-area">
    <div class="login-container">

        <h2 class="title">Login to your account</h2>

        <p class="email-text">
            Logging in as <b><?= $email ?></b>
        </p>

        <div class="method-section">

            <form action="login_process.php" method="POST">
                <input type="hidden" name="email" value="<?= $email ?>">

                <input
                    type="password"
                    name="password"
                    placeholder="Enter your password"
                    class="input-field"
                    required
                >

                <p class="password-note">Use your account password to continue.</p>

                <button type="submit" class="continue-final-btn">
                    Login
                </button>
            </form>

        </div>

    </div>
</section>

</body>
</html>