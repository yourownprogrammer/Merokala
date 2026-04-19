<?php
/**
 * Shared admin strip: return to dashboard + logout.
 * Include once per page, immediately after <body>, only when admin is logged in.
 */
if (!isset($_SESSION['admin_id'])) {
    return;
}
$adminCurrentPage = basename($_SERVER['SCRIPT_NAME'] ?? '');
?>
<style>
.admin-quick-nav {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 12px;
    padding: 10px 20px;
    margin-bottom: 16px;
    background: #e5e7eb;
    border: 1px solid #d1d5db;
    border-radius: 8px;
    font-family: Arial, sans-serif;
}
.admin-quick-nav a {
    color: #1f2937;
    text-decoration: none;
    font-size: 14px;
    font-weight: 600;
}
.admin-quick-nav a:hover {
    text-decoration: underline;
    color: #ff7a00;
}
.admin-quick-nav .logout-link {
    color: #b91c1c;
}
.admin-quick-nav .here {
    color: #6b7280;
    font-size: 14px;
    font-weight: 600;
}
</style>
<nav class="admin-quick-nav" aria-label="Admin navigation">
    <div>
        <?php if ($adminCurrentPage === 'admindash.php'): ?>
            <span class="here">Dashboard (home)</span>
        <?php else: ?>
            <a href="admindash.php">← Go to Dashboard</a>
        <?php endif; ?>
    </div>
    <a class="logout-link" href="adminlogout.php">Logout</a>
</nav>
