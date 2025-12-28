<?php
session_start();

/* ===== AUTH GUARD ===== */
if (!isset($_SESSION['user_id'])) {
    header("Location: mainlogin.php");
    exit;
}

$userName = htmlspecialchars($_SESSION['user_name']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard</title>

    <link rel="stylesheet" href="../css/htm.css">

    <style>
        body {
            font-family: Arial, sans-serif;
            background: #fafafa;
            margin: 0;
        }

        .dashboard {
            max-width: 900px;
            margin: 60px auto;
            padding: 40px;
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 6px 20px rgba(0,0,0,0.08);
        }

        h1 {
            margin-top: 0;
            font-size: 28px;
            color: #333;
        }

        p {
            font-size: 16px;
            color: #555;
        }

        .logout-btn {
            margin-top: 25px;
            padding: 14px 28px;
            background: #ff7a00;
            color: #fff;
            border: none;
            border-radius: 30px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            transition: 0.2s;
        }

        .logout-btn:hover {
            background: #e56d00;
        }
    </style>
</head>

<body>

<header class="logo-header">
    <a href="../hmt.html" class="logo">Merokala</a>
</header>

<div class="dashboard">
    <h1>Welcome, <?= $userName ?> 👋</h1>

    <p>
        You are successfully logged in as a user.
    </p>

    <p>
        This is your dashboard. From here you can explore services,
        manage your account, and place orders.
    </p>

    <a href="logout.php" class="logout-btn">
        Logout
    </a>
</div>

</body>
</html>
