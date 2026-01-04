<?php
session_start();
require "../php/dbconnection.php";

if (!isset($_SESSION['provider_id'])) {
    header("Location: providersignup.php");
    exit;
}

$provider_id = $_SESSION['provider_id'];

/* FETCH PROVIDER */
$stmt = $conn->prepare("
    SELECT first_name, last_name, email, phone, primary_skill, status
    FROM providers
    WHERE id = ?
");
$stmt->bind_param("i", $provider_id);
$stmt->execute();
$provider = $stmt->get_result()->fetch_assoc();
$stmt->close();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Provider Dashboard</title>
</head>
<body>

<h1>
Welcome, <?php echo htmlspecialchars($provider['first_name']); ?>
</h1>

<h3>Your Details</h3>
<p><strong>Name:</strong> <?php echo htmlspecialchars($provider['first_name']." ".$provider['last_name']); ?></p>
<p><strong>Email:</strong> <?php echo htmlspecialchars($provider['email']); ?></p>
<p><strong>Phone:</strong> <?php echo htmlspecialchars($provider['phone']); ?></p>
<p><strong>Skill:</strong> <?php echo htmlspecialchars($provider['primary_skill']); ?></p>

<hr>

<h3>Listing Status</h3>


<?php
if ($provider['status'] === 'approved') {
    echo '<p style="color:green;">You are approved to list products.</p>';
    echo '<a href="provider_add_product.php">List Your Product</a>';

} elseif ($provider['status'] === 'pending') {
    echo '<p style="color:orange;">
            Your account is pending admin approval.
            You cannot list products yet.
          </p>';

} elseif ($provider['status'] === 'blocked') {
    echo '<p style="color:red;">
            Your account has been blocked by admin.
          </p>';
}
?>


<br><br>
<a href="providerlogout.php">Logout</a>

</body>
</html>
