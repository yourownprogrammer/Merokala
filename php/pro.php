<?php
// ===============================
// DATABASE CONNECTION
// ===============================
$conn = new mysqli("localhost", "root", "", "merokalaa");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// ===============================
// ERROR VARIABLES
// ===============================
$emailErr = "";
$error = "";

// ===============================
// HANDLE FORM SUBMISSION
// ===============================
if (isset($_POST["submit"])) {

    $first_name = trim($_POST["first_name"]);
    $last_name  = trim($_POST["last_name"]);
    $email      = trim($_POST["email"]);
    $phone      = trim($_POST["number"]);
    $password   = $_POST["password"]; // TEMP (plain)
    $skill      = trim($_POST["skill_name"]);
    $location   = trim($_POST["location"]);

    // ===============================
    // EMAIL VALIDATION (CRITICAL FIX)
    // ===============================
    if ($email === "") {
        $emailErr = "Email is required";
    } else {

        // CHECK IF EMAIL ALREADY EXISTS
        $sql = "SELECT id FROM providers WHERE email = ?";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            die("Prepare failed: " . $conn->error);
        }

        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $emailErr = "Email already exists";
        } else {

            // ===============================
            // INSERT PROVIDER
            // ===============================
            $sql = "INSERT INTO providers
                (first_name, last_name, email, phone, password_plain, primary_skill, location)
                VALUES (?, ?, ?, ?, ?, ?, ?)";

            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                die("Prepare failed: " . $conn->error);
            }

            $stmt->bind_param(
                "sssssss",
                $first_name,
                $last_name,
                $email,
                $phone,
                $password,
                $skill,
                $location
            );

            if ($stmt->execute()) {
                header("Location: providersignup.php");
                exit;
            } else {
                $error = "Registration failed";
            }
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
/* ===== RESET & GLOBAL ===== */
* {
    box-sizing: border-box;
}

html, body {
    height: 100%;
    margin: 0;
    overflow-y: auto;
    font-family: Arial, sans-serif;
    background: linear-gradient(to bottom right, #ffffff, #f4f4f4);
}

/* ===== HEADER ===== */
.logo-header {
    height: 70px;
    display: flex;
    align-items: center;
    padding: 0 40px;
    border-bottom: 1px solid #eee;
    background: #fff;
}

.logo-header a {
    font-size: 32px;
    font-weight: 700;
    color: #ff7a00;
    text-decoration: none;
}

/* ===== PAGE LAYOUT ===== */
.page-wrapper {
    min-height: calc(100vh - 70px);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
}

/* ===== FORM CARD ===== */
.provider-box {
    width: 625px;
    background: #fff;
    border-radius: 22px;
    padding: 40px 45px;
    box-shadow: 0 6px 22px rgba(0,0,0,0.08);
}

.provider-box h2 {
    margin: 0 0 10px;
    text-align: center;
    font-size: 28px;
    font-weight: 700;
    color: #333;
}

.provider-box p {
    text-align: center;
    margin: 0 0 22px;
    font-size: 14.5px;
    color: #666;
}

/* ===== TWO COLUMN ROW ===== */
.row-two {
    display: flex;
    gap: 15px;
    margin-bottom: 10px;
}

.field {
    width: 100%;
}

/* ===== INPUTS ===== */
.input-field {
    width: 100%;
    padding: 13px;
    border: 1px solid #ccc;
    border-radius: 8px;
    font-size: 15.5px;
    transition: 0.2s;
}

.input-field:focus {
    border-color: #ff7a00;
    box-shadow: 0 0 5px rgba(255,122,0,0.4);
    outline: none;
}

/* ===== ERRORS ===== */
.error {
    display: block;
    color: red;
    font-size: 12px;
    margin-top: 4px;
}

/* ===== CHECKBOX ===== */
.checkbox-row {
    margin: 12px 0 18px;
    font-size: 14px;
    color: #555;
}

.checkbox-row a {
    color: #ff7a00;
    font-weight: 600;
    text-decoration: none;
}

.checkbox-row a:hover {
    text-decoration: underline;
}

/* ===== BUTTON ===== */
.signup-btn {
    width: 100%;
    padding: 14px;
    background: #ff7a00;
    color: #fff;
    border: none;
    border-radius: 30px;
    font-size: 17px;
    font-weight: 600;
    cursor: pointer;
    transition: 0.25s;
}

.signup-btn:hover {
    background: #e56d00;
    transform: translateY(-2px);
}

/* ===== FOOTER LINKS ===== */
.footer-links {
    margin-top: 14px;
    font-size: 14px;
    text-align: center;
    color: #666;
}

.footer-links a {
    color: #ff7a00;
    font-weight: 600;
    text-decoration: none;
}

.footer-links a:hover {
    text-decoration: underline;
}
</style>
</head>

<body>

<header class="logo-header">
    <a href="../hmt.html">Merokala</a>
</header>

<div class="page-wrapper">
<div class="provider-box">

<h2>Create Provider Account</h2>
<p>Create a profile and offer your skills</p>

<form action="pro.php" method="POST">

<div class="row-two">
    <div class="field">
        <input type="text" name="first_name" class="input-field" placeholder="First Name" required>
        <span class="error"></span>
    </div>
    <div class="field">
        <input type="text" name="last_name" class="input-field" placeholder="Last Name" required>
        <span class="error"></span>
    </div>
</div>

<div class="row-two">
    <div class="field">
<input type="email" name="email" class="input-field" placeholder="Email address" required>
<span class="error"><?php echo $emailErr; ?></span>

    </div>
    <div class="field">
        <input type="text" name="number" class="input-field" placeholder="Phone number" required>
        <span class="error"></span>
    </div>
</div>

<div class="row-two">
    <div class="field">
        <input type="password" name="password" class="input-field" placeholder="Password" required>
        <span class="error"></span>
    </div>
    <div class="field">
        <input type="password" name="confirm_password" class="input-field" placeholder="Confirm Password" required>
        <span class="error"></span>
    </div>
</div>

<div class="row-two">
    <div class="field">
        <input type="text" name="skill_name" class="input-field" placeholder="Primary Skill / Profession" required>
        <span class="error"></span>
    </div>
    <div class="field">
        <input type="text" name="location" class="input-field" placeholder="Enter your location" required>
        <span class="error"></span>
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


/* ===== PHONE ===== */
phone.addEventListener("input", () => {
    const value = phone.value.trim();

    // 1️⃣ Check if empty
    if (value === "") {
        showError(phone, "Phone number is required");
        return;
    }

    // 2️⃣ Check if only numbers
    if (!/^[0-9]+$/.test(value)) {
        showError(phone, "Only numbers allowed");
        return;
    }

    // 3️⃣ Check if starts with 97 or 98
    if (!/^(97|98)/.test(value)) {
        showError(phone, "Must start with 97 or 98");
        return;
    }

    // 4️⃣ Check if exactly 10 digits
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
