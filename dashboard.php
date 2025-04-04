<?php
require_once('./database/db_connection.php');
session_start();
if (!isset($_SESSION['id'])) {
    header("Location: index.php");
    exit();
}
$branch_id = $_SESSION['id'];

$sql = "SELECT * FROM branches WHERE id = '$branch_id'";
$result = mysqli_query($conn, $sql);
if($result->num_rows >0){
    $row = $result->fetch_assoc();
    $branchName = $row['branch_name'];
}

$productSql = "SELECT COUNT(*) AS total_products FROM foods WHERE branch_id = '$branch_id'";
$productResult = mysqli_query($conn, $productSql);
$productData = mysqli_fetch_assoc($productResult);
$totalProducts = $productData['total_products'];

$userSql = "SELECT COUNT(*) AS total_users FROM users WHERE branch_id = '$branch_id'";
$userResult = mysqli_query($conn, $userSql);
$userData = mysqli_fetch_assoc($userResult);
$totalUsers = $userData['total_users'];

$mostBoughtProductSql = "
    SELECT f.food_name, SUM(od.quantity) AS total_quantity
    FROM order_details od
    INNER JOIN foods f ON od.food_id = f.id
    WHERE od.branch_id = '$branch_id'
    GROUP BY od.food_id
    ORDER BY total_quantity DESC
    LIMIT 1
";
$mostBoughtProductResult = mysqli_query($conn, $mostBoughtProductSql);
$mostBoughtProduct = mysqli_fetch_assoc($mostBoughtProductResult);
$mostBoughtProductName = $mostBoughtProduct ? $mostBoughtProduct['food_name'] : 'N/A';

$orderSql = "
    SELECT 
        o.id AS order_id,
        GROUP_CONCAT(f.food_name SEPARATOR ', ') AS food_items,
        o.user_id,
        o.order_status
    FROM orders o
    INNER JOIN order_details od ON o.id = od.order_id
    INNER JOIN foods f ON od.food_id = f.id
    WHERE od.branch_id = '$branch_id'
    GROUP BY o.id
    ORDER BY o.id DESC
";
$orderResult = mysqli_query($conn, $orderSql);

// Fetch all notification-related orders for this branch
$sentNotifications = [];
$notifSql = "SELECT user_id, message FROM notifications WHERE branch_id = '$branch_id'";
$notifResult = mysqli_query($conn, $notifSql);
while ($notifRow = mysqli_fetch_assoc($notifResult)) {
    if (preg_match('/Order #(\d+)/', $notifRow['message'], $matches)) {
        $sentNotifications[$matches[1]] = true;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Canteen Branch Dashboard</title>
    <link rel="stylesheet" href="dashboard.css">
    <style>
        .btn-ready:disabled {
            background-color: #6c757d;
            cursor: not-allowed;
        }
    </style>
</head>
<body>
<div class="dashboard">
    <div class="sidebar">
        <div class="logo">
            <?php if (!empty($row['logo'])): ?>
                <img src="./uploads/<?php echo $row['logo']; ?>" alt="Branch Logo" class="branch-logo">
            <?php else: ?>
                <span><?php echo $branchName ?></span>
            <?php endif; ?>
        </div>
        <ul>
            <li><a href="dashboard.php" class="active">Dashboard</a></li>
            <li><a href="category.php">Category Management</a></li>
            <li><a href="product_management.php">Food Management</a></li>
            <li><a href="staff.php">Staff</a></li>
            <li><a href="report.php">Report</a></li>
            <li><a href="profile.php">Profile</a></li>
            <li><a href="order_history.php">Order History</a></li>
        </ul>
    </div>
    <div class="main-content">
        <div class="header">
            <input type="text" placeholder="Search...">
            <h3><?php echo $branchName ?></h3>
            <a href="logout.php"><button class="btn">Log Out</button></a>
        </div>
        <div class="content">
            <h1>Welcome to Your Dashboard</h1>
            <h2><?php echo $branchName ?></h2>
            <div class="dashboard-cards">
                <div class="card"><h3>Total products</h3><p><?php echo $totalProducts; ?></p></div>
                <div class="card"><h3>Total Users</h3><p><?php echo $totalUsers; ?></p></div>
                <div class="card"><h3>Most Bought Product</h3><p><?php echo $mostBoughtProductName; ?></p></div>
                <div class="card">
                    <h3>Report</h3>
                    <button class="btn" onclick="window.location.href='report.php'">Report</button>
                </div>

            </div>
            <div class="order-list">
                <h2>Order List</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Order No</th>
                            <th>Order Items</th>
                            <th>Payment Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($order = mysqli_fetch_assoc($orderResult)): 
                            $isSent = isset($sentNotifications[$order['order_id']]);
                        ?>
                            <tr>
                                <td><?php echo str_pad($order['order_id'], 3, '0', STR_PAD_LEFT); ?></td>
                                <td><?php echo $order['food_items']; ?></td>
                                <td><?php echo ucfirst($order['order_status']); ?></td>
                                <td>
                                    <form method="POST" action="send_notification.php" style="display:inline;">
                                        <input type="hidden" name="user_id" value="<?php echo $order['user_id']; ?>">
                                        <input type="hidden" name="order_id" value="<?php echo $order['order_id']; ?>">
                                        <button type="submit" class="btn-ready" <?php echo $isSent ? 'disabled' : ''; ?>>
                                            <?php echo $isSent ? 'Notification Sent' : 'Food is Ready'; ?>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
</body>
</html>
