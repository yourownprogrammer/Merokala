<?php
// admin_users.php
require "dbconnection.php";
session_start();

// ---------- ADMIN AUTH CHECK ----------
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit;
}

// ---------- HANDLE ACTIONS ----------
if (isset($_GET['block'])) {
    $id = (int) $_GET['block'];
    $stmt = $conn->prepare("UPDATE users SET status='blocked' WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    header("Location: admin_users.php");
    exit;
}

if (isset($_GET['unblock'])) {
    $id = (int) $_GET['unblock'];
    $stmt = $conn->prepare("UPDATE users SET status='active' WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    header("Location: admin_users.php");
    exit;
}

// ---------- FETCH USERS ----------
$result = $conn->query("SELECT id, name, email, status, created_at FROM users ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin – Manage Users</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<style>
    body { font-family: Arial, sans-serif; background:#f6f7f9; margin:0; padding:20px; }
    h1 { margin-bottom:20px; }
    table { width:100%; border-collapse:collapse; background:#fff; }
    th, td { padding:12px 10px; border-bottom:1px solid #eee; text-align:left; }
    th { background:#fafafa; }
    .status-active { color:green; font-weight:bold; }
    .status-blocked { color:red; font-weight:bold; }
    a.btn { padding:6px 12px; text-decoration:none; border-radius:6px; font-size:14px; }
    .btn-block { background:#ff4d4d; color:#fff; }
    .btn-unblock { background:#28a745; color:#fff; }
</style>
</head>
<body>

<h1>Manage Users</h1>

<table>
    <tr>
        <th>ID</th>
        <th>Name</th>
        <th>Email</th>
        <th>Status</th>
        <th>Created</th>
        <th>Action</th>
    </tr>

    <?php if ($result && $result->num_rows > 0): ?>
        <?php while ($u = $result->fetch_assoc()): ?>
        <tr>
            <td><?= (int)$u['id'] ?></td>
            <td><?= htmlspecialchars($u['name']) ?></td>
            <td><?= htmlspecialchars($u['email']) ?></td>
            <td class="<?= $u['status'] === 'active' ? 'status-active' : 'status-blocked' ?>">
                <?= htmlspecialchars($u['status']) ?>
            </td>
            <td><?= htmlspecialchars($u['created_at']) ?></td>
            <td>
                <?php if ($u['status'] === 'active'): ?>
                    <a class="btn btn-block" href="?block=<?= (int)$u['id'] ?>"
                       onclick="return confirm('Block this user?');">Block</a>
                <?php else: ?>
                    <a class="btn btn-unblock" href="?unblock=<?= (int)$u['id'] ?>"
                       onclick="return confirm('Unblock this user?');">Unblock</a>
                <?php endif; ?>
            </td>
        </tr>
        <?php endwhile; ?>
    <?php else: ?>
        <tr><td colspan="6">No users found.</td></tr>
    <?php endif; ?>
</table>

</body>
</html>
