<?php
session_start();

/* Clear all session data */
session_unset();
session_destroy();

/* Redirect to login */
header("Location: ../homepage.php");
exit;
