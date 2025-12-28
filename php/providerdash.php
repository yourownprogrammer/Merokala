<?php
session_start();

if (!isset($_SESSION['provider_id'])) {
    header("Location: providersignup.php");
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Provider Dashboard</title>
</head>
<body>

<h1>Welcome to Provider Dashboard</h1>

<p>You are logged in.</p>

<a href="providerlogout.php">Logout</a>

</body>
</html>
