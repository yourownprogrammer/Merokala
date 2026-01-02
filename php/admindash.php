<?php
session_start();

/* Security */
if (!isset($_SESSION['admin_id'])) {
    header("Location: adminlogin.php");
    exit;
}
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");

require "dbconnection.php";

/* Dashboard metrics */
$userCount = $conn->query("SELECT COUNT(*) AS total FROM users")->fetch_assoc()['total'];
$providerCount = $conn->query("SELECT COUNT(*) AS total FROM providers")->fetch_assoc()['total'];
// $pendingProducts = $conn->query("SELECT COUNT(*) AS total FROM provider_products WHERE status='pending'")->fetch_assoc()['total'];
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Admin Dashboard – Merokala</title>

<style>
body{
    margin:0;
    font-family:Arial, sans-serif;
    background:#f4f6f9;
}
.header{
    background:#1f2937;
    color:#fff;
    padding:20px 40px;
    display:flex;
    justify-content:space-between;
    align-items:center;
}
.header h2{
    margin:0;
    font-weight:500;
}
.header a{
    color:#ffb020;
    text-decoration:none;
    font-size:14px;
}
.container{
    width:1100px;
    margin:40px auto;
}
.section{
    margin-bottom:40px;
}
.section h3{
    margin-bottom:20px;
    color:#333;
}

/* Stats */
.stats{
    display:grid;
    grid-template-columns:repeat(3, 1fr);
    gap:20px;
}
.stat-box{
    background:#fff;
    padding:25px;
    border-radius:10px;
    text-align:center;
    box-shadow:0 2px 6px rgba(0,0,0,0.08);
}
.stat-box h4{
    margin-bottom:10px;
    color:#555;
}
.stat-box p{
    font-size:32px;
    margin:0;
    font-weight:bold;
    color:#111;
}

/* Admin cards */
.cards{
    display:grid;
    grid-template-columns:repeat(2, 1fr);
    gap:25px;
}
.card{
    background:#fff;
    padding:30px;
    border-radius:12px;
    box-shadow:0 2px 6px rgba(0,0,0,0.08);
}
.card h4{
    margin-top:0;
    color:#222;
}
.card p{
    color:#555;
    font-size:14px;
    margin-bottom:20px;
}
.card a{
    display:inline-block;
    padding:10px 18px;
    background:#ff7a00;
    color:#fff;
    text-decoration:none;
    border-radius:6px;
    font-size:14px;
}
.card a:hover{
    background:#e56d00;
}

/* Footer spacing */
.footer-space{
    height:40px;
}
</style>

</head>
<body>

<!-- HEADER -->
<div class="header">
    <h2>Admin Dashboard</h2>
    <div>
        Welcome, <?php echo htmlspecialchars($_SESSION['admin_name']); ?> |
        <a href="adminlogout.php">Logout</a>
    </div>
</div>

<!-- MAIN -->
<div class="container">

    <!-- OVERVIEW -->
    <div class="section">
        <h3>Platform Overview</h3>
        <div class="stats">
            <div class="stat-box">
                <h4>Total Users</h4>
                <p><?php echo $userCount; ?></p>
            </div>
            <div class="stat-box">
                <h4>Total Providers</h4>
                <p><?php echo $providerCount; ?></p>
            </div>
            <div class="stat-box">
                <h4>Pending Product Approvals</h4>

        

            </div>
        </div>
    </div>

    <!-- ADMIN ACTIONS -->
    <div class="section">
        <h3>Admin Controls</h3>
        <div class="cards">
                                                
            <div class="card">
                <h4>Manage Users</h4>
                <p>View platform users, edit user details, and enable or disable user accounts.</p>
                <a href="admin_users.php">Manage Users</a>
            </div>

            <div class="card">
                <h4>Manage Providers</h4>
                <p>Approve or reject provider registrations and review provider details.</p>
                <a href="admin_providers.php">Manage Providers</a>
            </div>

            <div class="card">
                <h4>Review Provider added Products</h4>
                <p>Approve or reject products submitted by providers before they appear to users.</p>
                <a href="admin_review_products.php">Review Products</a>
            </div>

            <div class="card">
                <h4>Add Products</h4>
                <p>Add and manage products directly uploaded by the admin for demonstration and platform use.</p>
                <a href="admin_add_products.php">Manage Products</a>
            </div>

        </div>
    </div>

</div>

<div class="footer-space"></div>

</body>
</html>
