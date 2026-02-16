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
$phoneErr     = "";
$passwordErr  = "";
$confirmErr   = "";
$skillErr     = "";
$locationErr  = "";

$hasError = false;


if (isset($_POST["submit"])) {

    $first_name = trim($_POST["first_name"]);
    $last_name  = trim($_POST["last_name"]);
    $email      = trim($_POST["email"]);
    $phone      = trim($_POST["number"]);
    $password   = $_POST["password"];
    $confirm    = $_POST["confirm_password"];
    $skill      = trim($_POST["skill_name"]);
    $location   = trim($_POST["location"]);



// First name
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

/* ===== PHONE ===== */
if ($phone === "") {
    $phoneErr = "Phone number is required";
    $hasError = true;
} elseif (!preg_match("/^[0-9]+$/", $phone)) {
    $phoneErr = "Only numbers allowed";
    $hasError = true;
} elseif (!preg_match("/^(97|98)/", $phone)) {
    $phoneErr = "Must start with 97 or 98";
    $hasError = true;
} elseif (strlen($phone) !== 10) {
    $phoneErr = "Must be 10 digits";
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

/* ===== CONFIRM PASSWORD ===== */
if ($password === "") {
    $confirmErr = "Enter password first";
    $hasError = true;
} elseif ($password !== $confirm) {
    $confirmErr = "Passwords do not match";
    $hasError = true;
}

/* ===== SKILL ===== */
if ($skill === "") {
    $skillErr = "Skill is required";
    $hasError = true;
} elseif (preg_match("/[0-9]/", $skill)) {
    $skillErr = "Numbers are not allowed";
    $hasError = true;
}

/* ===== LOCATION ===== */
if ($location === "") {
    $locationErr = "Location is required";
    $hasError = true;
} elseif (!preg_match("/^[A-Za-z][A-Za-z0-9 -]*$/", $location)) {
    $locationErr = "Must start with a letter";
    $hasError = true;
}

  if ($hasError === false) {
    $stmt = $conn->prepare("SELECT id FROM providers WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $emailErr = "Email already exists";
        $hasError = true;
    }
    $stmt->close();
}

    if ($hasError === false) {

        // TEMP plain password (hash later)
     $hashedPassword = customPasswordHash($password);

$stmt = $conn->prepare(
    "INSERT INTO providers
    (first_name, last_name, email, phone, password_hash, primary_skill, location)
    VALUES (?, ?, ?, ?, ?, ?, ?)"
);

$stmt->bind_param(
    "sssssss",
    $first_name,
    $last_name,
    $email,
    $phone,
    $hashedPassword,
    $skill,
    $location
);


        if ($stmt->execute()) {
           header("Location: providersignup.php?email=" . urlencode($email));
            exit;
        } else {
            $error = "Registration failed";
        }
    }
}
?>





<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Provider Registration – Merokala</title>

<style>
* {
    box-sizing: border-box;
}

html, body {  height: 100%; margin: 0; overflow-y: auto; font-family: Arial, sans-serif; background: linear-gradient(to bottom right, #ffffff, #f4f4f4);
}

.logo-header {  height: 70px; display: flex; align-items: center; padding: 0 40px;  border-bottom: 1px solid #eee; background: #fff;
}

.logo-header a { font-size: 32px; font-weight: 700; color: #ff7a00; text-decoration: none;
}

/* ===== PAGE LAYOUT ===== */
.page-wrapper {  min-height: calc(100vh - 70px);  display: flex;  align-items: center;  justify-content: center;  padding: 20px;
}

/* ===== FORM CARD ===== */
.provider-box {  width: 625px;  background: #fff;  border-radius: 22px;  padding: 40px 45px;  box-shadow: 0 6px 22px rgba(0,0,0,0.08);
}

.provider-box h2 {  margin: 0 0 10px;  text-align: center;  font-size: 28px;  font-weight: 700;  color: #333;
}

.provider-box p {  text-align: center;  margin: 0 0 22px;  font-size: 14.5px;  color: #666;
}

/* ===== TWO COLUMN ROW ===== */
.row-two { display: flex; gap: 15px; margin-bottom: 10px;
}

.field {
    width: 100%;
}

/* ===== INPUTS ===== */
.input-field {  width: 100%;  padding: 13px;  border: 1px solid #ccc;  border-radius: 8px;  font-size: 15.5px;  transition: 0.2s;
}

.input-field:focus { border-color: #ff7a00; box-shadow: 0 0 5px rgba(255,122,0,0.4); outline: none;
}

/* ===== ERRORS ===== */
.error {  display: block;  color: red;  font-size: 12px;  margin-top: 4px;
}

/* ===== CHECKBOX ===== */
.checkbox-row {  margin: 12px 0 18px;  font-size: 14px;  color: #555;
}

.checkbox-row a {  color: #ff7a00;  font-weight: 600;  text-decoration: none;
}

.checkbox-row a:hover {  text-decoration: underline;
}

/* ===== BUTTON ===== */
.signup-btn {  width: 100%;  padding: 14px;  background: #ff7a00;  color: #fff;  border: none;  border-radius: 30px;  font-size: 17px;  font-weight: 600;  cursor: pointer;  transition: 0.25s;
}

.signup-btn:hover { background: #e56d00; transform: translateY(-2px);
}

/* ===== FOOTER LINKS ===== */
.footer-links {  margin-top: 14px; font-size: 14px; text-align: center; color: #666;
}

.footer-links a {  color: #ff7a00;  font-weight: 600;  text-decoration: none;
}

.footer-links a:hover {  text-decoration: underline;
}
</style>
</head>

<body>

<header class="logo-header">
    <a href="../homepage.php">Merokala</a>
</header>

<div class="page-wrapper">
<div class="provider-box">

<h2>Create Provider Account</h2>
<p>Create a profile and offer your skills</p>

<form action="pro.php" method="POST">

<div class="row-two">
    <div class="field">
        <input type="text" name="first_name" class="input-field" value="<?php echo htmlspecialchars($first_name ?? ''); ?>" placeholder="First Name" >
        <span class="error"><?php echo $firstNameErr; ?></span>
    </div>
    <div class="field">
        <input type="text" name="last_name" class="input-field" value="<?php echo htmlspecialchars($last_name ?? ''); ?>" placeholder="Last Name" >
        <span class="error"><?php echo $lastNameErr; ?></span>
    </div>
</div>

<div class="row-two">
    <div class="field">
<input type="email" name="email" class="input-field"  value="<?php echo htmlspecialchars($email ?? ''); ?>" placeholder="Email address">
<span class="error"><?php echo $emailErr; ?></span>

    </div>
    <div class="field">
        <input type="text" name="number" class="input-field"  value="<?php echo htmlspecialchars($phone ?? ''); ?>" placeholder="Phone number" >
        <span class="error"><?php echo $phoneErr; ?></span>
    </div>
</div>

<div class="row-two">
    <div class="field">
        <input type="password" name="password" class="input-field" placeholder="Password" >
        <span class="error"><?php echo $passwordErr; ?></span>
    </div>
    <div class="field">
        <input type="password" name="confirm_password" class="input-field" placeholder="Confirm Password" >
        <span class="error"><?php echo $confirmErr; ?></span>
    </div>
</div>

<div class="row-two">
    <div class="field">
        <input type="text" name="skill_name" class="input-field"  value="<?php echo htmlspecialchars($skill ?? ''); ?>" placeholder="Primary Skill / Profession" >
        <span class="error"><?php echo $skillErr; ?></span>
    </div>
    <div class="field">
        <input type="text" name="location" class="input-field"  value="<?php echo htmlspecialchars($location ?? ''); ?>" placeholder="Enter your location" >
        <span class="error"><?php echo $locationErr; ?></span>
    </div>
</div>

<label class="checkbox-row">
    <input type="checkbox" required>
    I agree to the <a href="#">Provider Terms & Policies</a>
</label>

<button type="submit" name="submit" class="signup-btn">
    Create Provider Account
</button>

</form>

<div class="footer-links">
    Already a provider? <a href="providersignup.php">Login</a>
</div>

<div class="footer-links" style="margin-top:6px;">
    Want to shop? <a href="mainlogin.php">Login as customer</a>
</div>

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
const lastName = document.querySelector("[name='last_name']");
const email = document.querySelector("[name='email']");
const phone = document.querySelector("[name='number']");
const password = document.querySelector("[name='password']");
const confirmPassword = document.querySelector("[name='confirm_password']");
const skill = document.querySelector("[name='skill_name']");
const location = document.querySelector("[name='location']");

/* ===== FIRST & LAST NAME ===== */
function validateName(input) {
    const value = input.value.trim();

    if (value === "") {
        showError(input, "This field cannot be empty");
        return;
    }

    if (!/^[A-Za-z ]+$/.test(value)) {
        showError(input, "No numbers or symbols allowed");
        return;
    }

    if (value.length < 4) {
        showError(input, "Name is too short");
        return;
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
        showError(email, "Email cannot be empty");
        return;
    }

    if (!pattern.test(value)) {
        showError(email, "Invalid email format eg:example1@gmail.com");
        return;
    }
    clearError(email);
});

phone.addEventListener("input", () => {
    const value = phone.value.trim();

    if (value === "") {
        showError(phone, "Phone number is required");
        return;
    }

    if (!/^[0-9]+$/.test(value)) {
        showError(phone, "Only numbers allowed");
        return;
    }

    if (!/^(97|98)/.test(value)) {
        showError(phone, "Must start with 97 or 98");
        return;
    }

   
    if (value.length !== 10) {
        showError(phone, "Must be 10 digits");
        return;
    }

    clearError(phone);
});


password.addEventListener("input", () => {
    const value = password.value;

    // 1️⃣ Minimum length
    if (value.length < 8) {
        showError(password, "Minimum 8 characters required");
        return;
    }

    // 2️⃣ Must contain at least one letter
    if (!/[A-Za-z]/.test(value)) {
        showError(password, "At least one alphabet required");
        return;
    }

    // 3️⃣ Must contain at least one number or symbol
    if (!/[0-9!@#$%^&*(),.?":{}|<>]/.test(value)) {
        showError(password, "At least one number or symbol required");
        return;
    }

    // ✅ All checks passed
    clearError(password);
});


/* ===== CONFIRM PASSWORD ===== */
confirmPassword.addEventListener("input", () => {
    const passwordValue = password.value.trim();
    const confirmValue = confirmPassword.value.trim();

    // 1️⃣ Check if main password is empty
    if (passwordValue === "") {
        showError(confirmPassword, "Enter password first");
        return;
    }

    // 2️⃣ Check if passwords match
    if (confirmValue !== passwordValue) {
        showError(confirmPassword, "Passwords do not match");
        return;
    }

    // ✅ Clear error if everything is fine
    clearError(confirmPassword);
});


/* ===== PRIMARY SKILL ===== */
skill.addEventListener("input", () => {
    const value = skill.value.trim();

    if (value === "") {
        showError(skill, "Skill is required");
        return;
    }

    if (/[0-9]/.test(value)) {
        showError(skill, "Numbers are not allowed");
        return;
    }

    clearError(skill);
});

/* ===== LOCATION ===== */
location.addEventListener("input", () => {
    const value = location.value.trim();

    if (value === "") {
        showError(location, "Location is required");
        return;
    }

    if (!/^[A-Za-z][A-Za-z0-9 -]*$/.test(value)) {
        showError(location, "Must start with a letter");
        return;
    }

    clearError(location);
});
});
</script>

</body>
</html>
