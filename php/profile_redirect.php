<?php
session_start();

if (isset($_SESSION['user_id'])) {
    header("Location: user_dashboard.php");
} else {
    header("Location: mainlogin.php");
}
exit;
