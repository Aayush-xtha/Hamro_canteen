<?php
require_once('./database/db_connection.php');
require_once('global.php');

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
    SELECT p.method AS payment_method, COUNT(*) AS count
    FROM orders o
    JOIN order_details od ON o.id = od.order_id
    JOIN payments p ON p.order_detail_id = od.id
    WHERE od.branch_id = '$branch_id'
    GROUP BY p.method
";
$paymentResult = mysqli_query($conn, $paymentSql);

$paymentLabels = [];
$paymentData = [];

while ($row = mysqli_fetch_assoc($paymentResult)) {
    $paymentLabels[] = $row['payment_method'];
    $paymentData[] = $row['count'];
}

// Fetch most sold food items
$topFoodSql = "
    SELECT f.food_name, SUM(od.quantity) AS total_quantity
    FROM order_details od
    JOIN foods f ON od.food_id = f.id
    WHERE od.branch_id = '$branch_id'
    GROUP BY od.food_id
    ORDER BY total_quantity DESC
    LIMIT 5
";

$topFoodResult = mysqli_query($conn, $topFoodSql);

$foodLabels = [];
$foodData = [];

while ($row = mysqli_fetch_assoc($topFoodResult)) {
    $foodLabels[] = $row['food_name'];  // Fixed 'product_name' to 'food_name'
    $foodData[] = $row['total_quantity'];
}

// Fetch monthly sales data for the current year
$currentYear = date('Y');
$monthlySalesSql = "
    SELECT MONTH(o.order_date) AS month, SUM(o.total_amount) AS monthly_sales
    FROM orders o
    JOIN order_details od ON o.id = od.order_id
    WHERE od.branch_id = '$branch_id' AND YEAR(o.order_date) = '$currentYear'
    GROUP BY MONTH(o.order_date)
    ORDER BY month
";
$monthlySalesResult = mysqli_query($conn, $monthlySalesSql);

$monthLabels = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
$salesData = array_fill(0, 12, 0);

while ($row = mysqli_fetch_assoc($monthlySalesResult)) {
    $monthIndex = (int)$row['month'] - 1;
    $salesData[$monthIndex] = (float)$row['monthly_sales'];
}

// Fetch online vs cash payment comparison
$paymentComparisonSql = "
    SELECT 
        CASE 
            WHEN p.method = 'Cash' THEN 'Cash'
            ELSE 'Online'
        END AS payment_type,
        COUNT(*) AS count,
        SUM(o.total_amount) AS total_amount
    FROM orders o
    JOIN order_details od ON o.id = od.order_id
    JOIN payments p ON p.order_detail_id = od.id
    WHERE od.branch_id = '$branch_id'
    GROUP BY payment_type
";
$paymentComparisonResult = mysqli_query($conn, $paymentComparisonSql);

$paymentTypeLabels = [];
$paymentTypeData = [];
$paymentAmountData = [];

while ($row = mysqli_fetch_assoc($paymentComparisonResult)) {
    $paymentTypeLabels[] = $row['payment_type'];
    $paymentTypeData[] = $row['count'];
    $paymentAmountData[] = $row['total_amount'];
}

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales Reports</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
    /* Root Variables */
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
        --accent-blue: #3498db;
        --accent-orange: #f39c12;
        --accent-purple: #9b59b6;
    }

    /* Reset */
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    .dashboard {
        display: flex;
        min-height: 100vh;
        background-color: var(--light-gray);
    }
 /* Sidebar Styling */
 .sidebar {
            width: 280px;
            background: linear-gradient(to bottom, var(--moss-dark), var(--moss-light));
            color: var(--white);
            padding: 30px 0;
            box-shadow: 5px 0 15px rgba(0,0,0,0.1);
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
    /* Main Content Styling */
    .container {
        flex-grow: 1;
        background-color: var(--light-gray);
        overflow-y: auto;
        padding: 30px;
    }

    h1 {
        color: var(--moss-dark);
        margin-bottom: 20px;
        border-bottom: 2px solid var(--mint);
        padding-bottom: 10px;
        text-align: center;
    }

    /* Stats Cards */
    .stats-container {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 20px;
        margin-bottom: 30px;
    }

    .stat-card {
        background-color: var(--white);
        border-radius: 15px;
        padding: 20px;
        box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        border: 1px solid var(--gray-border);
        text-align: center;
        transition: transform 0.3s ease;
    }

    .stat-card:hover {
        transform: translateY(-5px);
    }

    .stat-card h3 {
        color: var(--moss-light);
        margin-bottom: 10px;
        font-size: 1.2rem;
    }

    .stat-card .value {
        font-size: 1.8rem;
        font-weight: bold;
        color: var(--moss-dark);
    }

    .stat-card .icon {
        font-size: 2.5rem;
        margin-bottom: 15px;
        color: var(--mint);
    }

    /* Chart Sections */
    .chart-section {
        background-color: var(--white);
        border-radius: 15px;
        padding: 25px;
        margin-bottom: 30px;
        box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        border: 1px solid var(--gray-border);
    }

    .chart-section h2 {
        color: var(--moss-light);
        margin-bottom: 20px;
        border-bottom: 2px solid var(--mint);
        padding-bottom: 10px;
        text-align: center;
    }

    .chart-container {
        position: relative;
        height: 300px;
        margin: 0 auto;
    }

    .chart-row {
        display: flex;
        gap: 20px;
        margin-bottom: 30px;
    }

    .chart-col {
        flex: 1;
        background-color: var(--white);
        border-radius: 15px;
        padding: 25px;
        box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        border: 1px solid var(--gray-border);
    }

    .chart-col h2 {
        color: var(--moss-light);
        margin-bottom: 20px;
        border-bottom: 2px solid var(--mint);
        padding-bottom: 10px;
        text-align: center;
    }

    /* Date Range Selector */
    .date-selector {
        display: flex;
        justify-content: center;
        margin-bottom: 30px;
        gap: 15px;
    }

    .date-selector input {
        padding: 10px 15px;
        border: 1px solid var(--gray-border);
        border-radius: 8px;
        transition: all 0.3s ease;
    }

    .date-selector input:focus {
        outline: none;
        border-color: var(--moss-dark);
        box-shadow: 0 0 0 3px rgba(151,193,169,0.2);
    }

    .date-selector button {
        background-color: var(--moss-dark);
        color: var(--white);
        border: none;
        padding: 10px 20px;
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .date-selector button:hover {
        background-color: var(--mint);
        color: var(--moss-dark);
    }

    /* Payment Comparison */
    .payment-comparison {
        display: flex;
        justify-content: space-around;
        margin-top: 20px;
    }

    .payment-type {
        text-align: center;
        padding: 20px;
        border-radius: 10px;
        width: 45%;
    }

    .payment-type.cash {
        background-color: rgba(151, 193, 169, 0.2);
        border: 1px solid var(--mint);
    }

    .payment-type.online {
        background-color: rgba(52, 152, 219, 0.2);
        border: 1px solid var(--accent-blue);
    }

    .payment-type h3 {
        margin-bottom: 10px;
        color: var(--moss-dark);
    }

    .payment-type .amount {
        font-size: 1.8rem;
        font-weight: bold;
        margin-bottom: 5px;
    }

    .payment-type .count {
        font-size: 1.2rem;
        color: var(--dark-gray);
    }

    /* Export Button */
    .export-btn {
        background-color: var(--moss-dark);
        color: var(--white);
        border: none;
        padding: 12px 20px;
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.3s ease;
        text-transform: uppercase;
        letter-spacing: 1px;
        font-weight: bold;
        display: block;
        margin: 0 auto 30px;
    }

    .export-btn:hover {
        background-color: var(--mint);
        color: var(--moss-dark);
        transform: translateY(-3px);
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }

    /* Responsive Design */
    @media screen and (max-width: 1200px) {
        .dashboard {
            flex-direction: column;
        }

        .sidebar {
            width: 100%;
            height: auto;
        }

        .sidebar .logo img {
            width: 120px;
            height: 120px;
        }

        .stats-container {
            grid-template-columns: repeat(2, 1fr);
        }

        .chart-row {
            flex-direction: column;
        }
    }

    @media screen and (max-width: 768px) {
        .container {
            padding: 15px;
        }

        .stats-container {
            grid-template-columns: 1fr;
        }

        .payment-comparison {
            flex-direction: column;
            align-items: center;
            gap: 20px;
        }

        .payment-type {
            width: 100%;
        }
    }
    </style>
</head>
<body>
    <div class="dashboard">
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
                <li><a href="report.php" class="active">Report</a></li>
                <li><a href="profile.php">Profile</a></li>
                <li><a href="order_history.php">Order History</a></li>
            </ul>
        </div>

        <div class="container">
            <h1>Sales & Performance Reports</h1>
            
            <div class="date-selector">
                <input type="date" id="start-date" value="<?php echo date('Y-m-01'); ?>">
                <span>to</span>
                <input type="date" id="end-date" value="<?php echo date('Y-m-d'); ?>">
                <button onclick="filterReports()">Apply Filter</button>
            </div>

            <div class="stats-container">
                <div class="stat-card">
                    <div class="icon">💰</div>
                    <h3>Total Sales</h3>
                    <div class="value">₹<?php echo number_format($totalSales, 2); ?></div>
                </div>
                <div class="stat-card">
                    <div class="icon">🍔</div>
                    <h3>Items Sold</h3>
                    <div class="value"><?php echo number_format($totalSoldFood); ?></div>
                </div>
                <div class="stat-card">
                    <div class="icon">📊</div>
                    <h3>Avg. Order Value</h3>
                    <div class="value">₹<?php echo ($totalSoldFood > 0) ? number_format($totalSales / $totalSoldFood, 2) : '0.00'; ?></div>
                </div>
                <div class="stat-card">
                    <div class="icon">📅</div>
                    <h3>This Month</h3>
                    <div class="value">₹<?php echo number_format($salesData[date('n')-1], 2); ?></div>
                </div>
            </div>

            <div class="chart-section">
                <h2>Monthly Sales Performance (<?php echo $currentYear; ?>)</h2>
                <div class="chart-container">
                    <canvas id="monthlySalesChart"></canvas>
                </div>
            </div>

            <div class="chart-row">
                <div class="chart-col">
                    <h2>Top 5 Most Sold Food Items</h2>
                    <div class="chart-container">
                        <canvas id="topFoodChart"></canvas>
                    </div>
                </div>
                <div class="chart-col">
                    <h2>Payment Method Distribution</h2>
                    <div class="chart-container">
                        <canvas id="paymentChart"></canvas>
                    </div>
                </div>
            </div>

            <div class="chart-section">
                <h2>Online vs Cash Payment Comparison</h2>
                <div class="chart-container" style="height: 250px;">
                    <canvas id="paymentComparisonChart"></canvas>
                </div>
                <div class="payment-comparison">
                    <?php
                    $cashAmount = 0;
                    $cashCount = 0;
                    $onlineAmount = 0;
                    $onlineCount = 0;
                    
                    foreach ($paymentTypeLabels as $index => $label) {
                        if ($label == 'Cash') {
                            $cashAmount = $paymentAmountData[$index];
                            $cashCount = $paymentTypeData[$index];
                        } else {
                            $onlineAmount = $paymentAmountData[$index];
                            $onlineCount = $paymentTypeData[$index];
                        }
                    }
                    ?>
                    <div class="payment-type cash">
                        <h3>Cash Payments</h3>
                        <div class="amount">₹<?php echo number_format($cashAmount, 2); ?></div>
                        <div class="count"><?php echo $cashCount; ?> transactions</div>
                    </div>
                    <div class="payment-type online">
                        <h3>Online Payments</h3>
                        <div class="amount">₹<?php echo number_format($onlineAmount, 2); ?></div>
                        <div class="count"><?php echo $onlineCount; ?> transactions</div>
                    </div>
                </div>
            </div>

            <button class="export-btn" onclick="exportReports()">Export Reports</button>
        </div>
    </div>

    <script>
        // Initialize charts when DOM is loaded
        document.addEventListener("DOMContentLoaded", function () {
            // Monthly Sales Chart
            var monthlySalesCtx = document.getElementById('monthlySalesChart').getContext('2d');
            var monthlySalesChart = new Chart(monthlySalesCtx, {
                type: 'line',
                data: {
                    labels: <?php echo json_encode($monthLabels); ?>,
                    datasets: [{
                        label: 'Monthly Sales (₹)',
                        data: <?php echo json_encode($salesData); ?>,
                        backgroundColor: 'rgba(151, 193, 169, 0.2)',
                        borderColor: '#3c7a66',
                        borderWidth: 2,
                        pointBackgroundColor: '#2a5848',
                        pointBorderColor: '#ffffff',
                        pointRadius: 5,
                        tension: 0.3,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'top',
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return '₹' + context.raw.toLocaleString();
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return '₹' + value.toLocaleString();
                                }
                            }
                        }
                    }
                }
            });

            // Top Food Chart
            var topFoodCtx = document.getElementById('topFoodChart').getContext('2d');
            var topFoodChart = new Chart(topFoodCtx, {
                type: 'bar',
                data: {
                    labels: <?php echo json_encode($foodLabels); ?>,
                    datasets: [{
                        label: 'Quantity Sold',
                        data: <?php echo json_encode($foodData); ?>,
                        backgroundColor: [
                            'rgba(151, 193, 169, 0.7)',
                            'rgba(60, 122, 102, 0.7)',
                            'rgba(42, 88, 72, 0.7)',
                            'rgba(184, 216, 192, 0.7)',
                            'rgba(231, 76, 60, 0.7)'
                        ],
                        borderColor: [
                            '#97c1a9',
                            '#3c7a66',
                            '#2a5848',
                            '#b8d8c0',
                            '#e74c3c'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Quantity Sold'
                            }
                        },
                        x: {
                            ticks: {
                                maxRotation: 45,
                                minRotation: 45
                            }
                        }
                    }
                }
            });

            // Payment Method Chart
            var paymentCtx = document.getElementById('paymentChart').getContext('2d');
            var paymentChart = new Chart(paymentCtx, {
                type: 'doughnut',
                data: {
                    labels: <?php echo json_encode($paymentLabels); ?>,
                    datasets: [{
                        data: <?php echo json_encode($paymentData); ?>,
                        backgroundColor: [
                            'rgba(151, 193, 169, 0.7)',
                            'rgba(52, 152, 219, 0.7)',
                            'rgba(155, 89, 182, 0.7)',
                            'rgba(243, 156, 18, 0.7)'
                        ],
                        borderColor: [
                            '#97c1a9',
                            '#3498db',
                            '#9b59b6',
                            '#f39c12'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'right'
                        }
                    }
                }
            });

            // Payment Comparison Chart
            var paymentComparisonCtx = document.getElementById('paymentComparisonChart').getContext('2d');
            var paymentComparisonChart = new Chart(paymentComparisonCtx, {
                type: 'bar',
                data: {
                    labels: <?php echo json_encode($paymentTypeLabels); ?>,
                    datasets: [
                        {
                            label: 'Transaction Count',
                            data: <?php echo json_encode($paymentTypeData); ?>,
                            backgroundColor: 'rgba(151, 193, 169, 0.7)',
                            borderColor: '#97c1a9',
                            borderWidth: 1,
                            yAxisID: 'y'
                        },
                        {
                            label: 'Total Amount (₹)',
                            data: <?php echo json_encode($paymentAmountData); ?>,
                            backgroundColor: 'rgba(52, 152, 219, 0.7)',
                            borderColor: '#3498db',
                            borderWidth: 1,
                            yAxisID: 'y1'
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'top'
                        }
                    },
                    scales: {
                        y: {
                            type: 'linear',
                            display: true,
                            position: 'left',
                            title: {
                                display: true,
                                text: 'Transaction Count'
                            }
                        },
                        y1: {
                            type: 'linear',
                            display: true,
                            position: 'right',
                            grid: {
                                drawOnChartArea: false
                            },
                            title: {
                                display: true,
                                text: 'Amount (₹)'
                            },
                            ticks: {
                                callback: function(value) {
                                    return '₹' + value.toLocaleString();
                                }
                            }
                        }
                    }
                }
            });
        });

        // Function to filter reports based on date range
        function filterReports() {
            const startDate = document.getElementById('start-date').value;
            const endDate = document.getElementById('end-date').value;
            
            // In a real implementation, you would make an AJAX call to fetch filtered data
            alert(`Reports will be filtered from ${startDate} to ${endDate}. This would be implemented with AJAX in a real application.`);
            
            // Reload the page with query parameters
            // window.location.href = `report.php?start=${startDate}&end=${endDate}`;
        }

        // Function to export reports
        function exportReports() {
            // In a real implementation, you would generate a PDF or Excel file
            alert('Reports will be exported. This would generate a PDF or Excel file in a real application.');
        }

        // Full screen logo view
        function openFullScreenLogo(src) {
            const modal = document.createElement('div');
            modal.style.position = 'fixed';
            modal.style.top = '0';
            modal.style.left = '0';
            modal.style.width = '100%';
            modal.style.height = '100%';
            modal.style.backgroundColor = 'rgba(0,0,0,0.8)';
            modal.style.display = 'flex';
            modal.style.justifyContent = 'center';
            modal.style.alignItems = 'center';
            modal.style.zIndex = '2000';
            
            const img = document.createElement('img');
            img.src = src;
            img.style.maxWidth = '80%';
            img.style.maxHeight = '80%';
            img.style.borderRadius = '10px';
            
            modal.appendChild(img);
            document.body.appendChild(modal);
            
            modal.addEventListener('click', function() {
                document.body.removeChild(modal);
            });
        }
    </script>
</body>
</html>