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
<html>
<head>
<meta charset="UTF-8">
<title>Admin Login</title>
<style>
body{font-family:Arial;background:#f4f4f4}
.box{width:360px;margin:120px auto;background:#fff;padding:30px;border-radius:10px}
input,button{width:100%;padding:14px;margin-bottom:12px}
button{background:#ff7a00;color:#fff;border:none}
.error{color:red;font-size:14px}
</style>
</head>
<body>

<div class="box">
<h2>Admin Login</h2>

<?php if (isset($_GET['error'])): ?>
<p class="error">Wrong email or password</p>
<?php endif; ?>

<form method="POST" action="admin_auth.php" autocomplete="off">
    <input type="email" name="email" placeholder="Email" required autocomplete="off">
    <input type="password" name="password" placeholder="Password" required autocomplete="off">
    <button type="submit">Login</button>
</form>

</div>

</body>
</html>
