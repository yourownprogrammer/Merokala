<?php
session_start();

/* Security */
if (!isset($_SESSION['admin_id'])) {
    header("Location: adminlogin.php");
    exit;
}
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");

require "dbconnection.php";

function scalar_value($conn, $sql)
{
    $result = $conn->query($sql);
    if (!$result) {
        return 0;
    }
    $row = $result->fetch_row();
    return isset($row[0]) ? (int) $row[0] : 0;
}

function table_has_column($conn, $table, $column)
{
    $tableEscaped = $conn->real_escape_string($table);
    $columnEscaped = $conn->real_escape_string($column);
    $sql = "
        SELECT COUNT(*) 
        FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = '{$tableEscaped}'
          AND COLUMN_NAME = '{$columnEscaped}'
    ";
    return scalar_value($conn, $sql) > 0;
}

function last_n_month_labels($months)
{
    $labels = [];
    for ($i = $months - 1; $i >= 0; $i--) {
        $labels[] = date('Y-m', strtotime("-{$i} month"));
    }
    return $labels;
}

function monthly_counts($conn, $table, $months)
{
    if (!table_has_column($conn, $table, 'created_at')) {
        return array_fill(0, $months, 0);
    }

    $tableMap = ['users' => 'users', 'products' => 'products', 'providers' => 'providers'];
    if (!isset($tableMap[$table])) {
        return array_fill(0, $months, 0);
    }

    $tableName = $tableMap[$table];
    $startDate = date('Y-m-01', strtotime('-' . ($months - 1) . ' month'));
    $stmt = $conn->prepare("
        SELECT DATE_FORMAT(created_at, '%Y-%m') AS month_key, COUNT(*) AS total
        FROM {$tableName}
        WHERE created_at >= ?
        GROUP BY month_key
    ");
    $stmt->bind_param("s", $startDate);
    $stmt->execute();
    $result = $stmt->get_result();

    $map = [];
    while ($row = $result->fetch_assoc()) {
        $map[$row['month_key']] = (int) $row['total'];
    }

    $counts = [];
    foreach (last_n_month_labels($months) as $label) {
        $counts[] = $map[$label] ?? 0;
    }
    return $counts;
}

/* Dashboard metrics */
$userCount = $conn->query("SELECT COUNT(*) AS total FROM users")->fetch_assoc()['total'];
$providerCount = $conn->query("SELECT COUNT(*) AS total FROM providers")->fetch_assoc()['total'];
$pendingProducts = $conn->query(
    "SELECT COUNT(*) AS total FROM products WHERE status = 'pending'"
)->fetch_assoc()['total'];

$pendingOrders = $conn->query(
    "SELECT COUNT(*) AS total FROM orders WHERE order_status = 'pending'"
)->fetch_assoc()['total'];

$approvedProducts = scalar_value($conn, "SELECT COUNT(*) FROM products WHERE status = 'approved'");
$rejectedProducts = scalar_value($conn, "SELECT COUNT(*) FROM products WHERE status = 'rejected'");
$totalProducts = scalar_value($conn, "SELECT COUNT(*) FROM products");
$totalOrders = scalar_value($conn, "SELECT COUNT(*) FROM orders");
$acceptedOrders = scalar_value($conn, "SELECT COUNT(*) FROM orders WHERE order_status = 'accepted'");
$rejectedOrders = scalar_value($conn, "SELECT COUNT(*) FROM orders WHERE order_status = 'rejected'");

$usersLast7 = 0;
$providersLast7 = 0;
$productsLast7 = 0;
if (table_has_column($conn, 'users', 'created_at')) {
    $usersLast7 = scalar_value(
        $conn,
        "SELECT COUNT(*) FROM users WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)"
    );
}
if (table_has_column($conn, 'providers', 'created_at')) {
    $providersLast7 = scalar_value(
        $conn,
        "SELECT COUNT(*) FROM providers WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)"
    );
}
if (table_has_column($conn, 'products', 'created_at')) {
    $productsLast7 = scalar_value(
        $conn,
        "SELECT COUNT(*) FROM products WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)"
    );
}

$productApprovedPercent = $totalProducts > 0 ? round(($approvedProducts / $totalProducts) * 100) : 0;
$productPendingPercent = $totalProducts > 0 ? round(($pendingProducts / $totalProducts) * 100) : 0;
$productRejectedPercent = $totalProducts > 0 ? round(($rejectedProducts / $totalProducts) * 100) : 0;

$orderAcceptedPercent = $totalOrders > 0 ? round(($acceptedOrders / $totalOrders) * 100) : 0;
$orderPendingPercent = $totalOrders > 0 ? round(($pendingOrders / $totalOrders) * 100) : 0;
$orderRejectedPercent = $totalOrders > 0 ? round(($rejectedOrders / $totalOrders) * 100) : 0;

$communityTotal = $userCount + $providerCount;
$buyerPercent = $communityTotal > 0 ? round(($userCount / $communityTotal) * 100) : 0;
$providerPercent = $communityTotal > 0 ? round(($providerCount / $communityTotal) * 100) : 0;

?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Admin Dashboard – Merokala</title>

<style>
body{
    margin:0;
    font-family:Arial, sans-serif;
    background:#f4f6f9;
}
.header{
    background:#1f2937;
    color:#fff;
    padding:20px 40px;
    display:flex;
    justify-content:space-between;
    align-items:center;
}
.header h2{
    margin:0;
    font-weight:500;
}
.header a{
    color:#ffb020;
    text-decoration:none;
    font-size:14px;
}
.container{
    width:1100px;
    margin:40px auto;
}
.section{
    margin-bottom:40px;
}
.section h3{
    margin-bottom:20px;
    color:#333;
}

/* Stats */
.stats{
    display:grid;
    grid-template-columns:repeat(3, 1fr);
    gap:20px;
}
.stat-box{
    background:#fff;
    padding:25px;
    border-radius:10px;
    text-align:center;
    box-shadow:0 2px 6px rgba(0,0,0,0.08);
    transition:transform 0.2s ease, box-shadow 0.2s ease;
}
.stat-box h4{
    margin-bottom:10px;
    color:#555;
}
.stat-box p{
    font-size:32px;
    margin:0;
    font-weight:bold;
    color:#111;
}

/* Clickable stat */
.stat-link{
    text-decoration:none;
    color:inherit;
}
.stat-link .stat-box{
    cursor:pointer;
}
.stat-link .stat-box:hover{
    transform:translateY(-4px);
    box-shadow:0 10px 22px rgba(0,0,0,0.12);
}

/* Alert state */
.stat-alert p{
    color:#d32f2f;
}
.stat-alert h4::after{
    content:" •";
    color:#d32f2f;
}

/* Admin cards */
.cards{
    display:grid;
    grid-template-columns:repeat(2, 1fr);
    gap:25px;
}
.card{
    background:#fff;
    padding:30px;
    border-radius:12px;
    box-shadow:0 2px 6px rgba(0,0,0,0.08);
}
.card h4{
    margin-top:0;
    color:#222;
}
.card p{
    color:#555;
    font-size:14px;
    margin-bottom:20px;
}
.card a{
    display:inline-block;
    padding:10px 18px;
    background:#ff7a00;
    color:#fff;
    text-decoration:none;
    border-radius:6px;
    font-size:14px;
}
.card a:hover{
    background:#e56d00;
}
.analytics-grid{
    display:grid;
    grid-template-columns:repeat(2, 1fr);
    gap:20px;
}
.analytics-card{
    background:#fff;
    border-radius:12px;
    box-shadow:0 2px 6px rgba(0,0,0,0.08);
    padding:22px;
}
.analytics-card h4{
    margin:0 0 14px;
    color:#222;
}
.mini-metric{
    display:flex;
    justify-content:space-between;
    align-items:center;
    margin:8px 0;
    font-size:14px;
    color:#444;
}
.mini-metric strong{
    color:#111;
    font-size:18px;
}
.growth-note{
    margin-top:12px;
    font-size:12px;
    color:#666;
}
.pie-wrap{
    display:flex;
    align-items:center;
    gap:18px;
}
.pie-chart{
    width:130px;
    height:130px;
    border-radius:50%;
    position:relative;
    flex-shrink:0;
}
.pie-chart::after{
    content:"";
    position:absolute;
    inset:24px;
    background:#fff;
    border-radius:50%;
    box-shadow:inset 0 0 0 1px #f1f1f1;
}
.pie-center-label{
    position:absolute;
    inset:0;
    display:flex;
    justify-content:center;
    align-items:center;
    font-size:12px;
    color:#555;
    font-weight:700;
    z-index:1;
}
.legend{
    flex:1;
    display:flex;
    flex-direction:column;
    gap:8px;
}
.legend-row{
    display:grid;
    grid-template-columns:14px 1fr auto;
    gap:8px;
    align-items:center;
    font-size:13px;
    color:#444;
}
.legend-dot{
    width:12px;
    height:12px;
    border-radius:999px;
}
.dot-blue{ background:#2563eb; }
.dot-teal{ background:#0d9488; }
.dot-green{ background:#2e7d32; }
.dot-yellow{ background:#f9a825; }
.dot-red{ background:#c62828; }
.insight{
    margin-top:12px;
    font-size:13px;
    color:#555;
    line-height:1.5;
}

.footer-space{
    height:40px;
}
@media (max-width:1150px){
    .container{
        width:auto;
        margin:30px 20px;
    }
    .stats{
        grid-template-columns:1fr;
    }
    .cards{
        grid-template-columns:1fr;
    }
    .analytics-grid{
        grid-template-columns:1fr;
    }
}

</style>

</head>
<body>

<!-- HEADER -->
<div class="header">
    <h2>Admin Dashboard</h2>
    <div>
        Welcome, <?php echo htmlspecialchars($_SESSION['admin_name']); ?> |
        <a href="adminlogout.php">Logout</a>
    </div>
</div>

<!-- MAIN -->
<div class="container">

    <!-- OVERVIEW -->
    <div class="section">
        <h3>Platform Overview</h3>
        <div class="stats">
            <div class="stat-box">
                <h4>Total Users</h4>
                <p><?php echo $userCount; ?></p>
            </div>
            <div class="stat-box">
                <h4>Total Providers</h4>
                <p><?php echo $providerCount; ?></p>
            </div>
            <a href="admin_review_products.php" class="stat-link">
    <div class="stat-box <?php echo ($pendingProducts > 0) ? 'stat-alert' : ''; ?>">
        <h4>Pending Product Approvals</h4>
        <p><?php echo $pendingProducts; ?></p>
    </div>
</a>

        </div>
    </div>

    <!-- ANALYTICS -->
    <div class="section">
        <h3>Graphic Analytics</h3>
        <div class="analytics-grid">
            <div class="analytics-card">
                <h4>Growth (Last 7 Days)</h4>
                <div class="mini-metric"><span>New Users</span><strong><?php echo $usersLast7; ?></strong></div>
                <div class="mini-metric"><span>New Providers</span><strong><?php echo $providersLast7; ?></strong></div>
                <div class="mini-metric"><span>New Products</span><strong><?php echo $productsLast7; ?></strong></div>
                <p class="growth-note">Shows weekly additions when `created_at` fields are available.</p>
            </div>

            <div class="analytics-card">
                <h4>Audience Ratio (Buyers vs Providers)</h4>
                <div class="pie-wrap">
                    <div class="pie-chart" style="background: conic-gradient(#2563eb 0 <?php echo $buyerPercent; ?>%, #0d9488 <?php echo $buyerPercent; ?>% 100%);">
                        <div class="pie-center-label"><?php echo $communityTotal; ?> Total</div>
                    </div>
                    <div class="legend">
                        <div class="legend-row">
                            <span class="legend-dot dot-blue"></span>
                            <span>Buyers (Users)</span>
                            <strong><?php echo $userCount; ?> (<?php echo $buyerPercent; ?>%)</strong>
                        </div>
                        <div class="legend-row">
                            <span class="legend-dot dot-teal"></span>
                            <span>Providers</span>
                            <strong><?php echo $providerCount; ?> (<?php echo $providerPercent; ?>%)</strong>
                        </div>
                    </div>
                </div>
                <p class="insight">This chart helps you monitor whether marketplace demand (buyers) and supply (providers) are balanced.</p>
            </div>

            <div class="analytics-card">
                <h4>Product Approval Ratio</h4>
                <div class="pie-wrap">
                    <div class="pie-chart" style="background: conic-gradient(#2e7d32 0 <?php echo $productApprovedPercent; ?>%, #f9a825 <?php echo $productApprovedPercent; ?>% <?php echo $productApprovedPercent + $productPendingPercent; ?>%, #c62828 <?php echo $productApprovedPercent + $productPendingPercent; ?>% 100%);">
                        <div class="pie-center-label"><?php echo $totalProducts; ?> Products</div>
                    </div>
                    <div class="legend">
                        <div class="legend-row">
                            <span class="legend-dot dot-green"></span>
                            <span>Approved</span>
                            <strong><?php echo $approvedProducts; ?> (<?php echo $productApprovedPercent; ?>%)</strong>
                        </div>
                        <div class="legend-row">
                            <span class="legend-dot dot-yellow"></span>
                            <span>Pending</span>
                            <strong><?php echo $pendingProducts; ?> (<?php echo $productPendingPercent; ?>%)</strong>
                        </div>
                        <div class="legend-row">
                            <span class="legend-dot dot-red"></span>
                            <span>Rejected</span>
                            <strong><?php echo $rejectedProducts; ?> (<?php echo $productRejectedPercent; ?>%)</strong>
                        </div>
                    </div>
                </div>
                <p class="insight">Use this ratio to evaluate moderation load and provider submission quality.</p>
            </div>

            <div class="analytics-card">
                <h4>Order Processing Ratio</h4>
                <div class="pie-wrap">
                    <div class="pie-chart" style="background: conic-gradient(#2e7d32 0 <?php echo $orderAcceptedPercent; ?>%, #f9a825 <?php echo $orderAcceptedPercent; ?>% <?php echo $orderAcceptedPercent + $orderPendingPercent; ?>%, #c62828 <?php echo $orderAcceptedPercent + $orderPendingPercent; ?>% 100%);">
                        <div class="pie-center-label"><?php echo $totalOrders; ?> Orders</div>
                    </div>
                    <div class="legend">
                        <div class="legend-row">
                            <span class="legend-dot dot-green"></span>
                            <span>Accepted</span>
                            <strong><?php echo $acceptedOrders; ?> (<?php echo $orderAcceptedPercent; ?>%)</strong>
                        </div>
                        <div class="legend-row">
                            <span class="legend-dot dot-yellow"></span>
                            <span>Pending</span>
                            <strong><?php echo $pendingOrders; ?> (<?php echo $orderPendingPercent; ?>%)</strong>
                        </div>
                        <div class="legend-row">
                            <span class="legend-dot dot-red"></span>
                            <span>Rejected</span>
                            <strong><?php echo $rejectedOrders; ?> (<?php echo $orderRejectedPercent; ?>%)</strong>
                        </div>
                    </div>
                </div>
                <p class="insight">Tracks operational efficiency: higher accepted ratio usually means smoother fulfillment.</p>
            </div>
        </div>
    </div>

    <!-- ADMIN ACTIONS -->
    <div class="section">
        <h3>Admin Controls</h3>
        <div class="cards">
                                                
            <div class="card">
                <h4>Manage Users</h4>
                <p>View platform users, edit user details, and enable or disable user accounts.</p>
                <a href="admin_users.php">Manage Users</a>
            </div>

            <div class="card">
                <h4>Manage Providers</h4>
                <p>Approve or reject provider registrations and review provider details.</p>
                <a href="admin_providers.php">Manage Providers</a>
            </div>

            <div class="card">
                <h4>Review Provider added Products</h4>
                <p>Approve or reject products submitted by providers before they appear to users.</p>
                <a href="admin_review_products.php">Review Products</a>
            </div>

            <div class="card">
                <h4>Add Products</h4>
                <p>Add and manage products directly uploaded by the admin for demonstration and platform use.</p>
                <a href="admin_add_products.php">Manage Products</a>
            </div>
<div class="card">
    <h4>Manage Orders</h4>
    <p>Review customer orders and accept or reject them before processing.</p>
    <a href="admin_orders.php">View Orders</a>
</div>

        </div>
    </div>

</div>

<div class="footer-space"></div>

</body>
</html>
