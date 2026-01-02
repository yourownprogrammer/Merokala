<?php
session_start();

// block access if logged out
if (!isset($_SESSION['admin_id'])) {
    header("Location: adminlogin.php");
    exit;
}

// prevent reuse after logout
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Admin Dashboard</title>
<style>
body{font-family:Arial;background:#f4f4f4}
.box{width:600px;margin:80px auto;background:#fff;padding:30px;border-radius:10px}
a{text-decoration:none;color:#ff7a00}
</style>
</head>
<body>

<div class="box">
<h2>Welcome, <?php echo htmlspecialchars($_SESSION['admin_name']); ?></h2>
<hr>

<ul>
    <li>Manage Users</li>
    <li>Manage Providers</li>
    <li>Approve Listings</li>
</ul>

<br>
<a href="adminlogout.php">Logout</a>
</div>

</body>
</html>
