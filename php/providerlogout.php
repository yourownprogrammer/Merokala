<?php
session_start();

/* Unset all session variables */
$_SESSION = [];

/* Destroy the session */
session_destroy();

/* Redirect to provider login page */
header("Location: providersignup.php");
exit;
