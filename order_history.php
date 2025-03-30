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
    <link rel="stylesheet" href="side_bar.css">

    <style>
      :root {
            --white: #ffffff;
            --moss-dark: #2a5848;
            --moss-light: #3c7a66;
            --mint: #97c1a9;
            --mint-light: #b8d8c0;
            --light-gray: #f7f9f8;
            --gray-border: #e0e6e3;
            --dark-gray: #333;
            --accent-red: #e74c3c;
            --accent-green: #2ecc71;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background-color: var(--light-gray);
        }

        .dashboard {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar Styling */
        .sidebar {
            width: 280px;
            background: linear-gradient(to bottom, var(--moss-dark), var(--moss-light));
            color: var(--white);
            padding: 30px 0;
            box-shadow: 5px 0 15px rgba(0,0,0,0.1);
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            overflow-y: auto;
            z-index: 1000;
        }

        .sidebar .logo {
            text-align: center;
            margin-bottom: 40px;
            position: relative;
        }

        .sidebar .logo img {
            width: 180px;
            height: 180px;
            object-fit: cover;
            border-radius: 50%;
            border: 5px solid var(--mint);
            transition: transform 0.3s ease;
            cursor: pointer;
        }

        .sidebar .logo img:hover {
            transform: scale(1.05);
        }

        .sidebar .logo span {
            display: block;
            color: var(--white);
            font-size: 1.5em;
            margin-top: 10px;
        }

        .sidebar ul {
            list-style: none;
        }

        .sidebar ul li {
            margin: 15px 0;
            position: relative;
        }

        .sidebar ul li a {
            color: var(--white); 
            text-decoration: none;
            padding: 12px 25px;
            display: block;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            z-index: 1;
        }

        .sidebar ul li a::before {
            content: '';
            position: absolute;
            width: 100%;
            height: 100%;
            background-color: var(--mint);
            display: block;
            top: 0;
            left: -100%;
            transition: all 0.3s ease;
            z-index: -1;
        }

        .sidebar ul li a:hover::before,
        .sidebar ul li a.active::before {
            left: 0;
        }

        .sidebar ul li a:hover,
        .sidebar ul li a.active {
            color: var(--moss-dark);
            font-weight: bold;
        }

        /* Container Styling */
        .container {
            margin-left: 280px;
            width: calc(100% - 280px);
            padding: 30px;
            background-color: var(--light-gray);
            min-height: 100vh;
        }

        /* Header Styling */
        .header {
            background-color: var(--white);
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 30px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }

        .header input {
            padding: 10px 15px;
            border: 1px solid var(--gray-border);
            border-radius: 8px;
            width: 250px;
            transition: all 0.3s ease;
        }

        .header input:focus {
            outline: none;
            border-color: var(--moss-dark);
            box-shadow: 0 0 0 3px rgba(151,193,169,0.2);
        }

        .btn {
            background-color: var(--moss-dark);
            color: var(--white);
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .btn:hover {
            background-color: var(--mint);
            color: var(--moss-dark);
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        /* Content Styling */
        h1 {
            color: var(--moss-dark);
            margin-bottom: 20px;
            border-bottom: 2px solid var(--mint);
            padding-bottom: 10px;
        }

        /* List Section Styling */
        .list-section {
            background-color: var(--white);
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            border: 1px solid var(--gray-border);
        }

        .list-section table {
            width: 100%;
            border-collapse: collapse;
        }

        .list-section table thead {
            background-color: var(--light-gray);
        }

        .list-section table th, 
        .list-section table td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid var(--gray-border);
        }

        .list-section table th {
            background-color: var(--moss-dark);
            color: var(--white);
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .list-section table tbody tr:hover {
            background-color: var(--mint-light);
            transition: background-color 0.3s ease;
        }

        /* Responsive Design */
        @media screen and (max-width: 1200px) {
            .sidebar {
                width: 250px;
            }

            .container {
                margin-left: 250px;
                width: calc(100% - 250px);
            }
        }

        @media screen and (max-width: 768px) {
            .sidebar {
                width: 100%;
                height: auto;
                position: static;
            }

            .container {
                margin-left: 0;
                width: 100%;
                padding: 15px;
            }

            .header {
                flex-direction: column;
                align-items: flex-start;
            }

            .header input {
                width: 100%;
                margin-bottom: 10px;
            }

            .list-section table {
                font-size: 0.9em;
            }

            .list-section table th, 
            .list-section table td {
                padding: 10px;
            }
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
                <li><a href="staff.php">Staff</a></li>

                <li><a href="report.php">Report</a></li>
                
                <li><a href="profile.php">Profile</a></li>

                <li><a href="order_history.php" class="active">Order History</a></li> <!-- Active page -->
            </ul>
        </div>

        <div class="container">
            <h1>Order History</h1>

            
            

            
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
