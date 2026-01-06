<?php
session_start();

$conn = new mysqli("localhost", "root", "", "merokalaa");
if ($conn->connect_error) {
    die("Connection failed");
}

/* ===== PASSWORD HASH FUNCTION ===== */
function customPasswordHash($password) {
    $hash = 0;
    for ($i = 0; $i < strlen($password); $i++) {
        $hash = ($hash * 31) + ord($password[$i]);
    }
    return $hash;
}

$emailErr = "";
$passwordErr = "";
$loginErr = "";

if (isset($_POST['login'])) {

    $email = trim($_POST['seller_email']);
    $password = $_POST['seller_password'];

    // EMAIL VALIDATION
    if ($email === "" || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $emailErr = "Enter a valid email";
    }

    // PASSWORD VALIDATION
    if ($password === "") {
        $passwordErr = "Password is required";
    }

    // IF NO BASIC ERRORS
    if ($emailErr === "" && $passwordErr === "") {

        $hashedInput = customPasswordHash($password);

        $stmt = $conn->prepare(
            "SELECT id, password_hash FROM providers WHERE email = ?"
        );
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            $loginErr = "Invalid email or password";
        } else {
            $user = $result->fetch_assoc();

if ($hashedInput != $user['password_hash']) {
                $loginErr = "Invalid email or password";
            } else {
                $_SESSION['provider_id'] = $user['id'];
                header("Location: providerdash.php");
                exit;
            }
        }
    }
}
?>

<?php
$prefillEmail = "";

if (isset($_GET['email'])) {
    $prefillEmail = htmlspecialchars($_GET['email']);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Seller Login – Merokala</title>

<style>
    body { margin: 0; font-family: Arial, sans-serif; background: linear-gradient(to bottom right, #ffffff, #f7f7f7); }
    .logo-header {   height: 70px;   display: flex;   align-items: center;   padding: 0 40px;   border-bottom: 1px solid #eee;   background: #fff;  }
    .logo-header a {  font-size: 32px;  font-weight: 700;  color: #ff7a00;  text-decoration: none; }
    .seller-box {  width: 450px;  margin: 80px auto;  background: #fff;  border-radius: 22px;  padding: 45px 50px;  box-shadow: 0 6px 22px rgba(0,0,0,0.08);  text-align: center;   }
    .seller-box h2 {   margin-bottom: 10px;   font-size: 28px;   font-weight: 700;   color: #333; }
    .seller-box p {   margin: 0 0 25px;   font-size: 15px;   color: #666;  }
    .input-field {  width: 100%;  padding: 14px;  margin: 10px 0 18px;  border: 1px solid #ccc;  border-radius: 8px;  font-size: 16px; transition: 0.2s; }
    .input-field:focus {  border-color: #ff7a00;  box-shadow: 0 0 5px rgba(255,122,0,0.4);  outline: none;  }
    .login-btn {  width: 100%;  padding: 15px;  background: #ff7a00;  border: none;  font-size: 18px;  font-weight: 600;  border-radius: 30px;  color: #fff;  cursor: pointer;  transition: 0.25s;  margin-top: 5px; }
    .login-btn:hover {  background: #e56d00;  transform: translateY(-2px); }
    .small-text {   font-size: 14px;   color: #666;   margin-top: 18px; }
    .small-text a {  color: #ff7a00;  font-weight: 600;  text-decoration: none; }
    .small-text a:hover {  text-decoration: underline; }
</style>
</head>

<body>

<header class="logo-header">
    <a href="../homepage.php">Merokala</a>
</header>

<div class="seller-box">

    <h2>Seller Login</h2>
    <p>Access your provider dashboard</p>

    <form method="POST" action="">

  <input
    type="text"
    name="seller_email"
    class="input-field"
    placeholder="Email or username"
    value="<?php echo $prefillEmail; ?>"
    required
>
    <span style="color:red;font-size:12px;"><?php echo $emailErr; ?></span>

    <input type="password" name="seller_password" class="input-field"
           placeholder="Password">
    <span style="color:red;font-size:12px;"><?php echo $passwordErr; ?></span>

    <span style="color:red;font-size:13px;"><?php echo $loginErr; ?></span>

    <button type="submit" name="login" class="login-btn">Login</button>

</form>


    <div class="small-text">
        Not a provider? <a href="mainlogin.php">Login as customer</a>
    </div>

    <div class="small-text" style="margin-top: 6px;">
        New provider? <a href="providerregister.php">Create provider account</a>
    </div>
</div>

</body>
</html>
