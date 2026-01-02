<?php
session_start();

require "dbconnection.php"; // your existing DB connection

$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';

$sql = "SELECT * FROM admin WHERE email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $admin = $result->fetch_assoc();

    if ($admin['password'] === $password) {
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin_name'] = $admin['name'];

        // SUCCESS → dashboard
        header("Location: admindash.php");
        exit;
    }
}

// FAILED LOGIN → back to login with error
header("Location: adminlogin.php?error=1");
exit;
