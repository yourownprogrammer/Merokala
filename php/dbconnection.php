<?php
$host = "localhost";
$user = "root";       // XAMPP default
$pass = "";           // XAMPP default (empty)
$db   = "merokalaa";   // Your database name

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}
?>
