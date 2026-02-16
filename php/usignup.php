<?php
require "dbconnection.php";

function customPasswordHash($password) {
    $hash = 0;
    for ($i = 0; $i < strlen($password); $i++) {
        $hash = ($hash * 31) + ord($password[$i]);
    }
    return $hash;
}

$firstNameErr = "";
$lastNameErr  = "";
$emailErr     = "";
$passwordErr  = "";

$hasError = false;

if (isset($_POST["submit"])) {

    $first_name = trim($_POST["first_name"]);
    $last_name  = trim($_POST["last_name"]);
    $email      = trim($_POST["email"]);
    $password   = $_POST["password"];

    /* ===== FIRST NAME ===== */
    if ($first_name === "") {
        $firstNameErr = "This field cannot be empty";
        $hasError = true;
    } elseif (!preg_match("/^[A-Za-z ]+$/", $first_name)) {
        $firstNameErr = "No numbers or symbols allowed";
        $hasError = true;
    } elseif (strlen($first_name) < 4) {
        $firstNameErr = "Name is too short";
        $hasError = true;
    }

    /* ===== LAST NAME ===== */
    if ($last_name === "") {
        $lastNameErr = "This field cannot be empty";
        $hasError = true;
    } elseif (!preg_match("/^[A-Za-z ]+$/", $last_name)) {
        $lastNameErr = "No numbers or symbols allowed";
        $hasError = true;
    } elseif (strlen($last_name) < 4) {
        $lastNameErr = "Name is too short";
        $hasError = true;
    }

    /* ===== EMAIL ===== */
    $emailPattern = "/^[A-Za-z][A-Za-z0-9]*@[A-Za-z]+[0-9]*(\.[A-Za-z]{2,})+$/";

    if ($email === "") {
        $emailErr = "Email cannot be empty";
        $hasError = true;
    } elseif (!preg_match($emailPattern, $email)) {
        $emailErr = "Invalid email format eg:example1@gmail.com";
        $hasError = true;
    }

    /* ===== PASSWORD ===== */
    if (strlen($password) < 8) {
        $passwordErr = "Minimum 8 characters required";
        $hasError = true;
    } elseif (!preg_match("/[A-Za-z]/", $password)) {
        $passwordErr = "At least one alphabet required";
        $hasError = true;
    } elseif (!preg_match("/[0-9!@#$%^&*(),.?\":{}|<>]/", $password)) {
        $passwordErr = "At least one number or symbol required";
        $hasError = true;
    }

    /* ===== DUPLICATE EMAIL ===== */
    if ($hasError === false) {
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $emailErr = "Email already exists";
            $hasError = true;
        }
        $stmt->close();
    }

    /* ===== INSERT ===== */
    if ($hasError === false) {

        $name = $first_name . " " . $last_name;

        $hashedPassword = customPasswordHash($password);

$stmt = $conn->prepare(
    "INSERT INTO users (name, email, password_hash) VALUES (?, ?, ?)"
);
$stmt->bind_param("sss", $name, $email, $hashedPassword);


        if ($stmt->execute()) {
            header("Location: mainlogin.php?email=" . urlencode($email));
            exit;
        }
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title>User Signup</title>

<style>
/* RESET + GLOBAL */
html, body {
    height: 100%;
    margin: 0;
    font-family: Arial, sans-serif;
    background: #fafafa;
    overflow: hidden;   /* removes scroll bar cleanly */
}

/* HEADER */
.top-header {
    height: 70px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0 40px;
    background: white;
    border-bottom: none;   /* FIXED */
}


.logo {
    font-size: 32px;
    font-weight: 700;
    color: #ff7a00;
    cursor: pointer;
}

.login-link {
    font-size: 15px;
    color: #444;
}

.login-link a {
    color: #ff7a00;
    text-decoration: none;
    font-weight: 600;
}

/* MAIN LAYOUT */
.wrapper {
    display: flex;
    height: calc(100vh - 70px);
    padding: 20px 40px;
    gap: 40px;
    align-items: center;        /* vertically centers form & image */
}

/* LEFT IMAGE */
.left-img {
    flex: 0.85;
    background: url('../pics/one.png') center/cover no-repeat;
    border-radius: 25px;
    position: relative;
    overflow: hidden;

    height: 580px;        /* FINAL PERFECT HEIGHT */
    max-height: 580px;
    min-height: 580px;

    align-self: center;   /* centers image inside wrapper */
    margin-top: 10px;
}


/* SOFT FADE RIGHT SIDE */
.left-img::after {
    content: "";
    position: absolute;
    top: 0;
    right: 0;
    width: 70px;
    height: 100%;
    background: linear-gradient(to right, transparent, #fafafa);
}



/* RIGHT FORM */
.right-form {
    flex: 0.55;
    background: #fff;
    padding: 40px 50px;
    border-radius: 25px;
    box-shadow: 0px 4px 18px rgba(0,0,0,0.06);
    display: flex;
    flex-direction: column;
    justify-content: center;
}

h2 {
    font-size: 30px;
    margin-bottom: 25px;
    font-weight: 700;
    color: #333;
}

/* NAME ROW */
.name-row {
    display: flex;
    gap: 15px;
    width: 100%;
}

.form-group {
    flex: 1;
    display: flex;
    flex-direction: column;
    margin-bottom: 15px;
}

label {
    font-size: 14px;
    color: #444;
    margin-bottom: 6px;
}

input {
    padding: 13px;
    font-size: 15px;
    border: 1px solid #ccc;
    border-radius: 6px;
    transition: 0.2s;
}

input:focus {
    border-color: #ff7a00;
    box-shadow: 0 0 5px rgba(255, 122, 0, 0.4);
    outline: none;
}

/* BUTTON */
button {
    margin-top: 20px;
    padding: 15px;
    width: 100%;
    background: #ff7a00;
    color: #fff;
    font-size: 17px;
    font-weight: 600;
    border: none;
    border-radius: 30px;
    cursor: pointer;
    transition: 0.25s;
}

button:hover {
    background: #e56d00;
    transform: translateY(-2px);
}

/* RESPONSIVE */
@media (max-width: 900px) {
    .wrapper {
        flex-direction: column;
        height: auto;
        overflow-y: auto;
    }
    body {
        overflow-y: auto; /* allow scroll ONLY on small screens */
    }
    .left-img {
        height: 260px;
    }
}
</style>
</head>

<body>

<div class="top-header">
    <div class="logo" onclick="window.location.href='../homepage.php'">Merokala</div>
    <div class="login-link">Already have an account? <a href="mainlogin.php">Login</a></div>
</div>

<div class="wrapper">

    <div class="left-img"></div>

    <div class="right-form">

    
        <h2>Create User Account</h2>

       <form method="POST">

<div class="name-row">
    <div class="form-group">
        <label>First Name</label>
        <input type="text" name="first_name"
               value="<?= htmlspecialchars($first_name ?? '') ?>">
        <small class="error"><?= $firstNameErr ?></small>
    </div>


    <div class="form-group">
        <label>Last Name</label>
        <input type="text" name="last_name"
               value="<?= htmlspecialchars($last_name ?? '') ?>">
        <small class="error"><?= $lastNameErr ?></small>
    </div>
</div>

<div class="form-group">
    <label>Email</label>
    <input type="email" name="email"
           value="<?= htmlspecialchars($email ?? '') ?>">
    <small class="error"><?= $emailErr ?></small>
</div>

<div class="form-group">
    <label>Password</label>
    <input type="password" name="password">
    <small class="error"><?= $passwordErr ?></small>
</div>

<button type="submit" name="submit">Sign Up</button>
</form>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {

function showError(input, message) {
    input.parentElement.querySelector(".error").textContent = message;
}
function clearError(input) {
    input.parentElement.querySelector(".error").textContent = "";
}

const firstName = document.querySelector("[name='first_name']");
const lastName  = document.querySelector("[name='last_name']");
const email     = document.querySelector("[name='email']");
const password  = document.querySelector("[name='password']");

/* ===== NAME ===== */
function validateName(input) {
    const value = input.value.trim();

    if (value === "") {
        showError(input, "This field cannot be empty"); return;
    }
    if (!/^[A-Za-z ]+$/.test(value)) {
        showError(input, "No numbers or symbols allowed"); return;
    }
    if (value.length < 4) {
        showError(input, "Name is too short"); return;
    }
    clearError(input);
}

firstName.addEventListener("input", () => validateName(firstName));
lastName.addEventListener("input", () => validateName(lastName));

/* ===== EMAIL ===== */
email.addEventListener("input", () => {
    const value = email.value.trim();
    const pattern = /^[A-Za-z][A-Za-z0-9]*@[A-Za-z]+[0-9]*(\.[A-Za-z]{2,})+$/;

    if (value === "") {
        showError(email, "Email cannot be empty"); return;
    }
    if (!pattern.test(value)) {
        showError(email, "Invalid email format eg:example1@gmail.com"); return;
    }
    clearError(email);
});

/* ===== PASSWORD ===== */
password.addEventListener("input", () => {
    const value = password.value;

    if (value.length < 8) {
        showError(password, "Minimum 8 characters required"); return;
    }
    if (!/[A-Za-z]/.test(value)) {
        showError(password, "At least one alphabet required"); return;
    }
    if (!/[0-9!@#$%^&*(),.?":{}|<>]/.test(value)) {
        showError(password, "At least one number or symbol required"); return;
    }
    clearError(password);
});
});
</script>


</body>
</html>
