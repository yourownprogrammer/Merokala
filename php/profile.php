<?php
session_start();

/* If user not logged in, send to login */
if (!isset($_SESSION['user_id'])) {
    header("Location: php/mainlogin.php");
    exit;
}

/* If logged in, send to dashboard */
header("Location: user_dashboard.php");
exit;
