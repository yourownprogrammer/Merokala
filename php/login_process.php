<?php
require "dbconnection.php";
session_start();

/* ===== PASSWORD HASH FUNCTION ===== */
function customPasswordHash($password) {
    $hash = 0;
    for ($i = 0; $i < strlen($password); $i++) {
        $hash = ($hash * 31) + ord($password[$i]);
    }
    return $hash;
}

/* ===== BASIC GUARD ===== */
if (
    !isset($_POST['email']) ||
    !isset($_POST['password']) ||
    $_POST['email'] === "" ||
    $_POST['password'] === ""
) {
    header("Location: mainlogin.php");
    exit;
}

$email    = trim($_POST['email']);
$password = $_POST['password'];

/* ===== HASH INPUT PASSWORD ===== */
$hashedInput = customPasswordHash($password);

/* ===== CHECK USER ===== */
$stmt = $conn->prepare(
    "SELECT id, name, password_hash FROM users WHERE email = ?"
);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();

    /* ===== PASSWORD MATCH (HASHED) ===== */
    if ($hashedInput == $user['password_hash']) {

        $_SESSION['user_id']   = $user['id'];
        $_SESSION['user_name'] = $user['name'];

        header("Location: ../homepage.php");
        exit;
    }
}

/* ===== FAILED LOGIN ===== */
header("Location: login_method.php?email=" . urlencode($email));
exit;
