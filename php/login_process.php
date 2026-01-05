<?php
require "dbconnection.php";
session_start();

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

$email = trim($_POST['email']);
$password = $_POST['password'];

/* ===== CHECK USER ===== */
$stmt = $conn->prepare(
    "SELECT id, name, password FROM users WHERE email = ?"
);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();

    /* ===== PASSWORD MATCH (PLAIN, FOR NOW) ===== */
    if ($user['password'] === $password) {

        $_SESSION['user_id']   = $user['id'];
        $_SESSION['user_name'] = $user['name'];

        header("Location: ../homepage.php");
        exit;
    }
}

/* ===== FAILED LOGIN ===== */
header("Location: login_method.php?email=" . urlencode($email));
exit;
