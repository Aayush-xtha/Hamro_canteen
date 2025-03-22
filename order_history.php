<?php
require_once('./database/db_connection.php');
require_once('global.php');

session_start();
if (!isset($_SESSION['id'])) {
    header("Location: index.php");
    exit();
}

$branch_id = $_SESSION['id'];
// echo("branch Id $branch_id");

$sql = "SELECT * FROM branches WHERE id = '$branch_id'";
$result = mysqli_query($conn, $sql);
if ($result && mysqli_num_rows($result) > 0) { 
    $row = mysqli_fetch_assoc($result);
    $branchName = $row['branch_name'];
}


$history_sql = "SELECT 
            o.id AS order_id, 
            u.user_name, 
            u.first_name,
            u.last_name,
            od.quantity,
            od.sum_total,
            GROUP_CONCAT(f.food_name SEPARATOR ', ') AS order_items,
            o.order_status,
            COALESCE(p.method, 'Not Paid') AS payment_method
        FROM orders o
        JOIN users u ON o.user_id = u.id
        JOIN order_details od ON o.id = od.order_id
        JOIN foods f ON od.food_id = f.id
        LEFT JOIN payments p ON od.id = p.order_detail_id
        WHERE od.branch_id = '$branch_id'
        GROUP BY o.id, u.user_name, o.order_status
        ORDER BY o.order_date DESC";

$orderResult = mysqli_query($conn, $history_sql);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order History</title>
    <style>
        :root {
            --mint: #A8D5BA;
            --sage: #C1DAB4;
            --moss: #6D8B74;
            --white: #FFFFFF;
            --gray: #F5F5F5;
            --dark-gray: #3A3A3A;
            --hover-color: #B4E4CA;
            --active-color: #B0D8C0;
        }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background-color: var(--gray);
            color: var(--dark-gray);
        }

        .dashboard {
            display: flex;
            height: 100vh;
            width: 100%;
        }

        .sidebar {
            width: 250px;
            background-color: var(--moss);
            color: var(--white);
            display: flex;
            flex-direction: column;
            padding: 20px;
            position: fixed;
            height: 100%;
        }

        .sidebar .logo {
            font-size: 1.5rem;
            font-weight: bold;
            margin-bottom: 30px;
        }

        .sidebar ul {
            list-style: none;
            padding: 0;
        }

        .sidebar ul li {
            margin: 15px 0;
        }

        .sidebar ul li a {
            text-decoration: none;
            color: var(--white);
            font-size: 1rem;
            padding: 10px;
            border-radius: 5px;
            display: block;
            transition: background-color 0.3s ease;
        }

        .sidebar ul li a:hover,
        .sidebar ul li a.active {
            background-color: var(--active-color);
            font-weight: bold;
            color: var(--dark-gray);
        }

        .container {
            padding: 20px;
            margin-left: 270px; /* Adjust for sidebar space */
            width: calc(100% - 270px);
        }

        h1 {
            color: var(--moss);
        }

        .form-section {
            display: flex;
            align-items: center;
            justify-content: flex-start;
            margin-bottom: 20px;
        }

        .form-section input {
            padding: 10px;
            border-radius: 5px;
            border: 1px solid var(--light-gray);
            margin-right: 10px;
            font-size: 1rem;
            width: 250px;
        }

        .form-section button {
            padding: 10px 20px;
            background-color: var(--mint);
            color: var(--white);
            font-weight: bold;
            cursor: pointer;
            border: none;
            border-radius: 5px;
        }

        .form-section button:hover {
            background-color: var(--hover-color);
        }

        .list-section table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .list-section table th,
        .list-section table td {
            padding: 15px;
            border: 1px solid var(--light-gray);
            text-align: left;
        }

        .list-section table th {
            background-color: var(--mint);
            color: var(--white);
        }

        .list-section table tbody tr:hover {
            background-color: var(--hover-color);
        }
        .branch-logo {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            object-fit: cover;
            display: block;
            margin: 0 auto 0px auto;
            border: 3px solid white;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            cursor: pointer;
        }

        /* Hover Effect - Slight Scale & Glow */
        .branch-logo:hover {
            transform: scale(1.1);
            box-shadow: 0 0 15px rgba(255, 255, 255, 0.5);
        }

    </style>
</head>
<body>
    <div class="dashboard">
        <!-- Sidebar -->
        <div class="sidebar">
        <div class="logo">
            <?php if (!empty($row['logo'])): ?>
                <img src="./uploads/<?php echo $row['logo']; ?>" alt="Branch Logo" class="branch-logo" onclick="openFullScreenLogo('./uploads/<?php echo $row['logo']; ?>')">
            <?php else: ?>
                <span><?php echo $branchName ?></span>
            <?php endif; ?>
        </div>
            <ul>
                <li><a href="dashboard.php">Dashboard</a></li>
                <li><a href="category.php">Category Management</a></li>
                <li><a href="product_management.php">Food Management</a></li>
                <li><a href="user.php">Users</a></li>
                <li><a href="review_feedback.php">Ratings & Feedback</a></li>
                <li><a href="report.php">Report</a></li>
                
                <li><a href="#">Payments</a></li>
                <li><a href="order_history.php" class="active">Order History</a></li> <!-- Active page -->
            </ul>
        </div>

        <div class="container">
            <h1>Order History</h1>

            <!-- Search Section -->
            <div class="form-section">
                <input type="text" placeholder="Search Order ID or Username..." id="searchInput">
                <button>Search</button>
            </div>

            <!-- Display Section -->
            <div class="list-section">
                <h2>Order List</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>First Name</th>
                            <th>Last Name</th>
                            <th>Order Items</th>
                            <th>Quantity</th>
                            <th>Order Status</th>
                            <th>Payment Method</th>
                            <th>Total Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($orderResult->num_rows > 0): ?>
                            <?php while ($row = $orderResult->fetch_assoc()): ?>

                                <tr>
                                    <td><?php echo $row['order_id']; ?></td>
                                    <td><?php echo $row['first_name']; ?></td>
                                    <td><?php echo $row['last_name']; ?></td>
                                    <td><?php echo $row['order_items']; ?></td>
                                    <td><?php echo $row['quantity']; ?></td>
                                    <td><?php echo $row['order_status']; ?></td>
                                    <td><?php echo $row['payment_method'] ? $row['payment_method'] : 'N/A'; ?></td>
                                    <td><?php echo "Rs. " . number_format($row['sum_total'], 2); ?></td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" style="text-align: center;">No orders found for this branch.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
