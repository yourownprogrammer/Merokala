<?php
session_start();

header("Cache-Control: no-store");

// If admin already logged in, go to dashboard
if (isset($_SESSION['admin_id'])) {
    header("Location: admindash.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Login</title>
<style>
*{
    box-sizing:border-box;
}
body{
    margin:0;
    min-height:100vh;
    font-family:Arial, sans-serif;
    background:linear-gradient(135deg, #fff4ea 0%, #f4f7fb 50%, #eef2ff 100%);
    display:flex;
    align-items:center;
    justify-content:center;
    padding:24px;
    color:#1f2937;
}
.login-wrap{
    width:100%;
    max-width:430px;
}
.brand{
    display:flex;
    align-items:center;
    justify-content:space-between;
    margin-bottom:14px;
}
.brand-name{
    font-size:26px;
    font-weight:700;
    color:#ff7a00;
    text-decoration:none;
}
.brand-back{
    font-size:14px;
    color:#4b5563;
    text-decoration:none;
}
.brand-back:hover{
    color:#111827;
}
.box{
    background:#fff;
    padding:34px;
    border-radius:18px;
    box-shadow:0 18px 40px rgba(17,24,39,0.12);
    border:1px solid #f0f0f0;
}
.title{
    margin:0 0 8px;
    font-size:30px;
    line-height:1.2;
}
.subtitle{
    margin:0 0 22px;
    color:#6b7280;
    font-size:14px;
}
.error{
    background:#fef2f2;
    color:#b91c1c;
    border:1px solid #fecaca;
    border-radius:10px;
    padding:10px 12px;
    font-size:14px;
    margin:0 0 14px;
}
label{
    display:block;
    margin-bottom:6px;
    font-size:13px;
    color:#4b5563;
    font-weight:600;
}
input{
    width:100%;
    padding:14px 15px;
    margin-bottom:14px;
    border:1px solid #d1d5db;
    border-radius:12px;
    font-size:15px;
    outline:none;
    background:#fff;
    transition:border-color 0.2s ease, box-shadow 0.2s ease;
}
input:focus{
    border-color:#fb923c;
    box-shadow:0 0 0 3px rgba(251,146,60,0.18);
}
button{
    width:100%;
    padding:14px;
    margin-top:4px;
    border:none;
    border-radius:999px;
    background:#ff7a00;
    color:#fff;
    font-size:16px;
    font-weight:700;
    cursor:pointer;
    transition:background 0.2s ease, transform 0.2s ease;
}
button:hover{
    background:#e56d00;
    transform:translateY(-1px);
}
@media (max-width:520px){
    .box{
        padding:26px 20px;
        border-radius:14px;
    }
    .title{
        font-size:26px;
    }
}
</style>
</head>
<body>

<div class="login-wrap">
    <div class="brand">
        <a href="../homepage.php" class="brand-name">Merokala</a>
        <a href="../homepage.php" class="brand-back">Back to site</a>
    </div>

    <div class="box">
        <h1 class="title">Admin Login</h1>
        <p class="subtitle">Sign in to access dashboard controls and analytics.</p>

        <?php if (isset($_GET['error'])): ?>
        <p class="error">Wrong email or password</p>
        <?php endif; ?>

        <form method="POST" action="admin_auth.php" autocomplete="off">
            <label for="admin-email">Email</label>
            <input id="admin-email" type="email" name="email" placeholder="Enter admin email" required autocomplete="off">

            <label for="admin-password">Password</label>
            <input id="admin-password" type="password" name="password" placeholder="Enter password" required autocomplete="off">

            <button type="submit">Login</button>
        </form>
    </div>
</div>

</body>
</html>
