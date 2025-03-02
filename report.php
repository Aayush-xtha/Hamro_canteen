<?php
require_once('./database/db_connection.php');
require_once('global.php');

session_start();
if (!isset($_SESSION['id'])) {
    header("Location: index.php");
    exit();
}
$branch_id = $_SESSION['id'];

// Fetch total sales amount per branch
$totalSalesSql = "
    SELECT SUM(o.total_amount) AS total_sales
    FROM orders o
    JOIN order_details od ON o.id = od.order_id
    WHERE od.branch_id = '$branch_id'
";
$totalSalesResult = mysqli_query($conn, $totalSalesSql);
$totalSales = mysqli_fetch_assoc($totalSalesResult)['total_sales'] ?? 0;

// Fetch total sold food count per branch
$totalSoldFoodSql = "
    SELECT SUM(od.quantity) AS total_sold
    FROM order_details od
    WHERE od.branch_id = '$branch_id'
";
$totalSoldFoodResult = mysqli_query($conn, $totalSoldFoodSql);
$totalSoldFood = mysqli_fetch_assoc($totalSoldFoodResult)['total_sold'] ?? 0;

// Fetch payment method statistics per branch
$paymentSql = "
    SELECT o.payment_method, COUNT(*) AS count
    FROM orders o
    JOIN order_details od ON o.id = od.order_id
    WHERE od.branch_id = '$branch_id'
    GROUP BY o.payment_method
";
$paymentResult = mysqli_query($conn, $paymentSql);

$paymentLabels = [];
$paymentData = [];

while ($row = mysqli_fetch_assoc($paymentResult)) {
    $paymentLabels[] = $row['payment_method'];
    $paymentData[] = $row['count'];
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales Report</title>
    <!-- <script src="https://cdn.jsdelivr.net/npm/chart.js"></script> -->
    <style>
        :root {
            --mint: #A8D5BA;
            --sage: #C1DAB4;
            --moss: #6D8B74;
            --white: #FFFFFF;
            --gray: #F5F5F5;
            --dark-gray: #3A3A3A;
            --shadow: rgba(0, 0, 0, 0.1);
            --border-radius: 10px;
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
            box-shadow: 2px 0 5px var(--shadow);
        }

        .sidebar .logo {
            font-size: 1.5rem;
            font-weight: bold;
            margin-bottom: 30px;
        }

        .sidebar ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .sidebar ul li {
            margin: 15px 0;
        }

        .sidebar ul li a {
            text-decoration: none;
            color: var(--white);
            font-size: 1rem;
            padding: 10px;
            border-radius: var(--border-radius);
            display: block;
        }

        .sidebar ul li a:hover,
        .sidebar ul li a.active {
            background-color: var(--mint);
            font-weight: bold;
            color: var(--dark-gray);
        }

        .main-content {
            margin-left: 270px;
            padding: 40px;
            flex: 1;
        }

        .header h1 {
            margin: 0;
            font-size: 2rem;
            text-align: center;
        }

        .stats-container {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
        }

        .stat-box {
            background-color: var(--white);
            padding: 20px;
            border-radius: var(--border-radius);
            box-shadow: 0 4px 8px var(--shadow);
            width: 48%;
            text-align: center;
        }

        canvas {
            background: var(--white);
            padding: 20px;
            border-radius: var(--border-radius);
            box-shadow: 0 4px 8px var(--shadow);
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
                <li><a href="order_history.php">Order History</a></li>
            </ul>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="header">
            <h1>Sales Report</h1>
        </div>

        <div class="stats-container">
            <div class="stat-box">
                <h2>Total Sales</h2>
                <p><strong>₹<?php echo number_format($totalSales, 2); ?></strong></p>
            </div>
            <div class="stat-box">
                <h2>Total Sold Food</h2>
                <p><strong><?php echo $totalSoldFood; ?> Items</strong></p>
            </div>
        </div>

        <h2 style="text-align: center;">Payment Method Distribution</h2>
        <canvas id="paymentChart"></canvas>
    </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            var ctx = document.getElementById('paymentChart').getContext('2d');
            var paymentChart = new Chart(ctx, {
                type: 'pie',
                data: {
                    labels: <?php echo json_encode($paymentLabels); ?>,
                    datasets: [{
                        data: <?php echo json_encode($paymentData); ?>,
                        backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56', '#4CAF50', '#F44336'],
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'top',
                        }
                    }
                }
            });
        });
    </script>
</body>
</html>
