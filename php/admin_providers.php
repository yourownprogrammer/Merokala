<?php
require "dbconnection.php";
session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit;
}

// ---------- ACTIONS ----------
if (isset($_GET['approve'])) {
    $id = (int) $_GET['approve'];
    $conn->query("UPDATE providers SET status='approved' WHERE id=$id");
    header("Location: admin_providers.php");
    exit;
}

if (isset($_GET['block'])) {
    $id = (int) $_GET['block'];
    $conn->query("UPDATE providers SET status='blocked' WHERE id=$id");
    header("Location: admin_providers.php");
    exit;
}

if (isset($_GET['unblock'])) {
    $id = (int) $_GET['unblock'];
    $conn->query("UPDATE providers SET status='approved' WHERE id=$id");
    header("Location: admin_providers.php");
    exit;
}

// ---------- FETCH PROVIDERS ----------
$result = $conn->query("
    SELECT id, first_name, last_name, email, phone, primary_skill, location, status
    FROM providers
    ORDER BY id DESC
");
?>
<!DOCTYPE html>
<html>
<head>
<title>Manage Providers</title>
<style>
body { font-family: Arial; background:#f6f7f9; padding:20px; }
table { width:100%; background:#fff; border-collapse:collapse; }
th, td { padding:12px; border-bottom:1px solid #eee; text-align:left; }
th { background:#fafafa; }
.pending { color:orange; font-weight:bold; }
.approved { color:green; font-weight:bold; }
.blocked { color:red; font-weight:bold; }
a.btn { padding:6px 12px; border-radius:6px; text-decoration:none; color:#fff; }
.approve { background:#28a745; }
.block { background:#ff4d4d; }
.unblock { background:#007bff; }
</style>
</head>
<body>

<h1>Manage Providers</h1>

<table>
<tr>
    <th>ID</th>
    <th>Name</th>
    <th>Email</th>
    <th>Phone</th>
    <th>Skill</th>
    <th>Location</th>
    <th>Status</th>
    <th>Action</th>
</tr>

<?php while ($p = $result->fetch_assoc()): ?>
<tr>
    <td><?= $p['id'] ?></td>
    <td><?= htmlspecialchars($p['first_name'].' '.$p['last_name']) ?></td>
    <td><?= htmlspecialchars($p['email']) ?></td>
    <td><?= htmlspecialchars($p['phone']) ?></td>
    <td><?= htmlspecialchars($p['primary_skill']) ?></td>
    <td><?= htmlspecialchars($p['location']) ?></td>
    <td class="<?= $p['status'] ?>"><?= $p['status'] ?></td>
    <td>
        <?php if ($p['status'] === 'pending'): ?>
            <a class="btn approve" href="?approve=<?= $p['id'] ?>">Approve</a>
        <?php elseif ($p['status'] === 'approved'): ?>
            <a class="btn block" href="?block=<?= $p['id'] ?>">Block</a>
        <?php else: ?>
            <a class="btn unblock" href="?unblock=<?= $p['id'] ?>">Unblock</a>
        <?php endif; ?>
    </td>
</tr>
<?php endwhile; ?>
</table>

</body>
</html>
