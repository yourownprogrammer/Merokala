<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Seller Login – Merokala</title>

<style>

    body {
        margin: 0;
        font-family: Arial, sans-serif;
        background: linear-gradient(to bottom right, #ffffff, #f7f7f7);
    }

    /* HEADER */
    .logo-header {
        height: 70px;
        display: flex;
        align-items: center;
        padding: 0 40px;
        border-bottom: 1px solid #eee;
        background: #fff;
    }
    .logo-header a {
        font-size: 32px;
        font-weight: 700;
        color: #ff7a00;
        text-decoration: none;
    }

    /* MAIN CARD */
    .seller-box {
        width: 450px;
        margin: 80px auto;
        background: #fff;
        border-radius: 22px;
        padding: 45px 50px;
        box-shadow: 0 6px 22px rgba(0,0,0,0.08);
        text-align: center;
    }

    .seller-box h2 {
        margin-bottom: 10px;
        font-size: 28px;
        font-weight: 700;
        color: #333;
    }

    .seller-box p {
        margin: 0 0 25px;
        font-size: 15px;
        color: #666;
    }

    /* Inputs */
    .input-field {
        width: 100%;
        padding: 14px;
        margin: 10px 0 18px;
        border: 1px solid #ccc;
        border-radius: 8px;
        font-size: 16px;
        transition: 0.2s;
    }

    .input-field:focus {
        border-color: #ff7a00;
        box-shadow: 0 0 5px rgba(255,122,0,0.4);
        outline: none;
    }

    /* Button */
    .login-btn {
        width: 100%;
        padding: 15px;
        background: #ff7a00;
        border: none;
        font-size: 18px;
        font-weight: 600;
        border-radius: 30px;
        color: #fff;
        cursor: pointer;
        transition: 0.25s;
        margin-top: 5px;
    }
    .login-btn:hover {
        background: #e56d00;
        transform: translateY(-2px);
    }

    /* Links */
    .small-text {
        font-size: 14px;
        color: #666;
        margin-top: 18px;
    }
    .small-text a {
        color: #ff7a00;
        font-weight: 600;
        text-decoration: none;
    }
    .small-text a:hover {
        text-decoration: underline;
    }

</style>
</head>

<body>

<header class="logo-header">
    <a href="../hmt.html">Merokala</a>
</header>

<div class="seller-box">

    <h2>Seller Login</h2>
    <p>Access your provider dashboard</p>

    <form method="POST" action="providerdash.php">

        <input type="text" name="seller_email" class="input-field" placeholder="Email or username" required>

        <input type="password" name="seller_password" class="input-field" placeholder="Password" required>

        <button type="submit" class="login-btn">Login</button>

    </form>

    <div class="small-text">
        Not a provider? <a href="mainlogin.php">Login as customer</a>
    </div>

    <div class="small-text" style="margin-top: 6px;">
        New provider? <a href="providerregister.php">Create provider account</a>
    </div>

</div>

</body>
</html>
