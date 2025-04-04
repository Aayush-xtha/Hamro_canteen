<?php
require_once('./database/db_connection.php');
require_once('global.php');

session_start();
if (!isset($_SESSION['id'])) {
    header("Location: index.php");
    exit();
}

$branch_id = $_SESSION['id'];

// Get branch details
$sql = "SELECT * FROM branches WHERE id = '$branch_id'";
$result = mysqli_query($conn, $sql);
if($result && $result->num_rows > 0){
    $branch = mysqli_fetch_assoc($result);
    $branchName = $branch['branch_name'];
    $branchLogo = $branch['logo'];
} else {
    $branchName = "Branch";
    $branchLogo = "";
}

// Set date filters
$startDate = isset($_GET['start']) ? $_GET['start'] : date('Y-m-01');
$endDate = isset($_GET['end']) ? $_GET['end'] : date('Y-m-d');

// Date filter condition
$dateFilter = " AND (o.order_date BETWEEN '$startDate' AND '$endDate 23:59:59')";

// Fetch total sales amount per branch with date filter
$totalSalesSql = "
    SELECT SUM(o.total_amount) AS total_sales
    FROM orders o
    JOIN order_details od ON o.id = od.order_id
    WHERE od.branch_id = '$branch_id'" . $dateFilter;
$totalSalesResult = mysqli_query($conn, $totalSalesSql);
$totalSales = mysqli_fetch_assoc($totalSalesResult)['total_sales'] ?? 0;

// Fetch total sold food count per branch with date filter
$totalSoldFoodSql = "
    SELECT SUM(od.quantity) AS total_sold
    FROM order_details od
    JOIN orders o ON od.order_id = o.id
    WHERE od.branch_id = '$branch_id'" . $dateFilter;
$totalSoldFoodResult = mysqli_query($conn, $totalSoldFoodSql);
$totalSoldFood = mysqli_fetch_assoc($totalSoldFoodResult)['total_sold'] ?? 0;

// Fetch payment method statistics per branch with date filter
$paymentSql = "
    SELECT p.method AS payment_method, COUNT(*) AS count
    FROM orders o
    JOIN order_details od ON o.id = od.order_id
    JOIN payments p ON p.order_detail_id = od.id
    WHERE od.branch_id = '$branch_id'" . $dateFilter . "
    GROUP BY p.method";
$paymentResult = mysqli_query($conn, $paymentSql);
$paymentLabels = [];
$paymentData = [];
while ($row = mysqli_fetch_assoc($paymentResult)) {
    $paymentLabels[] = $row['payment_method'];
    $paymentData[] = $row['count'];
}

// Fetch most sold food items with date filter
$topFoodSql = "
    SELECT f.food_name, SUM(od.quantity) AS total_quantity
    FROM order_details od
    JOIN foods f ON od.food_id = f.id
    JOIN orders o ON od.order_id = o.id
    WHERE od.branch_id = '$branch_id'" . $dateFilter . "
    GROUP BY od.food_id
    ORDER BY total_quantity DESC
    LIMIT 5";
$topFoodResult = mysqli_query($conn, $topFoodSql);
$foodLabels = [];
$foodData = [];
while ($row = mysqli_fetch_assoc($topFoodResult)) {
    $foodLabels[] = $row['food_name']; 
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
    ORDER BY month";
$monthlySalesResult = mysqli_query($conn, $monthlySalesSql);
$monthLabels = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
$salesData = array_fill(0, 12, 0);
while ($row = mysqli_fetch_assoc($monthlySalesResult)) {
    $monthIndex = (int)$row['month'] - 1;
    $salesData[$monthIndex] = (float)$row['monthly_sales'];
}

// Fetch online vs cash payment comparison with date filter
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
    WHERE od.branch_id = '$branch_id'" . $dateFilter . "
    GROUP BY payment_type";
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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <link rel="stylesheet" href="report.css">

</head>
<body>
    <div class="dashboard">
        <div class="sidebar">
            <div class="logo">
                <?php if (!empty($branchLogo)): ?>
                    <img src="./uploads/<?php echo htmlspecialchars($branchLogo); ?>" alt="Branch Logo" class="branch-logo" onclick="openFullScreenLogo('./uploads/<?php echo htmlspecialchars($branchLogo); ?>')">
                <?php else: ?>
                    <span><?php echo htmlspecialchars($branchName); ?></span>
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

        <div class="container" id="report-container">
            <h1>Sales & Performance Reports</h1>
            
            <div class="date-selector">
                <input type="date" id="start-date" value="<?php echo $startDate; ?>">
                <span>to</span>
                <input type="date" id="end-date" value="<?php echo $endDate; ?>">
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

    <!-- Loading Overlay -->
    <div class="loading-overlay" id="loading-overlay">
        <div class="spinner"></div>
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
                    },
                    animation: {
                        duration: 2000,
                        easing: 'easeOutQuart'
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
                    },
                    animation: {
                        duration: 1500,
                        easing: 'easeOutBounce'
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
                    },
                    animation: {
                        animateRotate: true,
                        animateScale: true,
                        duration: 2000
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
                    },
                    animation: {
                        duration: 1800,
                        easing: 'easeInOutQuart'
                    }
                }
            });
        });

        // Function to filter reports based on date range
        function filterReports() {
            const startDate = document.getElementById('start-date').value;
            const endDate = document.getElementById('end-date').value;
            
            if (!startDate || !endDate) {
                alert('Please select both start and end dates');
                return;
            }
            
            if (new Date(startDate) > new Date(endDate)) {
                alert('Start date cannot be after end date');
                return;
            }
            
            // Show loading overlay
            document.getElementById('loading-overlay').classList.add('active');
            
            // Redirect with date parameters
            window.location.href = `report.php?start=${startDate}&end=${endDate}`;
        }

        // Function to export reports as PDF
        function exportReports() {
            // Show loading overlay
            document.getElementById('loading-overlay').classList.add('active');
            
            // Use jsPDF and html2canvas to create PDF
            const { jsPDF } = window.jspdf;
            
            setTimeout(() => {
                const reportContainer = document.getElementById('report-container');
                const pdf = new jsPDF('p', 'mm', 'a4');
                const pdfWidth = pdf.internal.pageSize.getWidth();
                const pdfHeight = pdf.internal.pageSize.getHeight();
                const margins = 10;
                
                // Add title
                pdf.setFontSize(18);
                pdf.setTextColor(42, 88, 72); // moss-dark color
                pdf.text('Sales & Performance Report', pdfWidth/2, 20, { align: 'center' });
                
                // Add date range
                const startDate = document.getElementById('start-date').value;
                const endDate = document.getElementById('end-date').value;
                pdf.setFontSize(12);
                pdf.setTextColor(60, 122, 102); // moss-light color
                pdf.text(`Report Period: ${startDate} to ${endDate}`, pdfWidth/2, 30, { align: 'center' });
                
                // Add branch name
                pdf.setFontSize(14);
                pdf.text(`Branch: <?php echo htmlspecialchars($branchName); ?>`, pdfWidth/2, 40, { align: 'center' });
                
                // Add stats
                pdf.setFontSize(12);
                pdf.setTextColor(0, 0, 0);
                pdf.text(`Total Sales: ₹<?php echo number_format($totalSales, 2); ?>`, margins, 55);
                pdf.text(`Items Sold: <?php echo number_format($totalSoldFood); ?>`, margins, 65);
                pdf.text(`Avg. Order Value: ₹<?php echo ($totalSoldFood > 0) ? number_format($totalSales / $totalSoldFood, 2) : '0.00'; ?>`, margins, 75);
                
                // Capture and add charts
                html2canvas(document.getElementById('monthlySalesChart')).then(canvas => {
                    const imgData = canvas.toDataURL('image/png');
                    pdf.addImage(imgData, 'PNG', margins, 85, pdfWidth - margins*2, 60);
                    
                    html2canvas(document.getElementById('topFoodChart')).then(canvas => {
                        const imgData = canvas.toDataURL('image/png');
                        pdf.addImage(imgData, 'PNG', margins, 155, (pdfWidth - margins*3)/2, 60);
                        
                        html2canvas(document.getElementById('paymentChart')).then(canvas => {
                            const imgData = canvas.toDataURL('image/png');
                            pdf.addImage(imgData, 'PNG', pdfWidth/2, 155, (pdfWidth - margins*3)/2, 60);
                            
                            html2canvas(document.getElementById('paymentComparisonChart')).then(canvas => {
                                const imgData = canvas.toDataURL('image/png');
                                pdf.addPage();
                                pdf.addImage(imgData, 'PNG', margins, 20, pdfWidth - margins*2, 60);
                                
                                // Add footer
                                const today = new Date();
                                pdf.setFontSize(10);
                                pdf.setTextColor(100, 100, 100);
                                pdf.text(`Generated on: ${today.toLocaleString()}`, pdfWidth/2, pdfHeight - 10, { align: 'center' });
                                
                                // Save the PDF
                                pdf.save(`Sales_Report_${startDate}_to_${endDate}.pdf`);
                                
                                // Hide loading overlay
                                document.getElementById('loading-overlay').classList.remove('active');
                            });
                        });
                    });
                });
            }, 1000);
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
            img.style.boxShadow = '0 5px 25px rgba(0,0,0,0.5)';
            img.style.animation = 'fadeIn 0.3s ease-out';
            
            modal.appendChild(img);
            document.body.appendChild(modal);
            
            modal.addEventListener('click', function() {
                document.body.removeChild(modal);
            });
        }
    </script>
</body>
</html>