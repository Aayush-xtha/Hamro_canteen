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

// Fetch total users for the specific branch
$userSql = "SELECT COUNT(*) AS total_users FROM users WHERE branch_id = '$branch_id'";
$userResult = mysqli_query($conn, $userSql);
$userData = mysqli_fetch_assoc($userResult);
$totalUsers = $userData['total_users'];

// Fetch most rated product (optional)
$mostRatedProductSql = "SELECT food_name FROM foods WHERE branch_id = '$branch_id'";
$mostRatedProductResult = mysqli_query($conn, $mostRatedProductSql);
$mostRatedProduct = mysqli_fetch_assoc($mostRatedProductResult);
$mostRatedProductName = $mostRatedProduct ? $mostRatedProduct['food_name'] : 'N/A';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Canteen Branch Dashboard</title>
    <style>
       :root {
    --mint: #A8D5BA;
    --sage: #C1DAB4;
    --moss: #6D8B74;
    --white: #FFFFFF;
    --gray: #F5F5F5;
    --dark-gray: #3A3A3A;
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
            overflow: hidden; /* Prevent any unwanted scrolling */
        }
        /* Circular Logo in Sidebar */
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

        /* Full-Screen Logo Preview */
        .full-screen-logo {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7); /* Dark transparent background */
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }

        /* Full Logo Image */
        .full-screen-logo img {
            max-width: 80%;
            max-height: 80%;
            border-radius: 10px;
        }

        /* Close Button */
        .close-btn {
            position: absolute;
            top: 20px;
            right: 30px;
            font-size: 40px;
            color: white;
            cursor: pointer;
            transition: color 0.3s ease;
        }

        .close-btn:hover {
            color: red;
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
            box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1); /* Add slight shadow for clarity */
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
            background-color: var(--mint);
            font-weight: bold;
            color: var(--dark-gray);
        }

        /* Main Content */
        .main-content {
            margin-left: 250px; /* Account for the fixed sidebar width */
            flex: 1;
            display: flex;
            flex-direction: column;
            padding: 20px;
        }

        .header {
            background-color: var(--sage);
            padding: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-radius: 10px;
        }

        .header input {
            padding: 10px;
            border-radius: 5px;
            border: 1px solid var(--moss);
            width: 300px;
        }

        .content {
            flex: 1;
            padding: 30px;
            margin-top: 20px;
            background-color: var(--white);
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        /* Cards Section */
        .dashboard-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }

        .card {
            background-color: var(--white);
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            padding: 20px;
        }

        .card h3 {
            margin-top: 0;
        }

        /* Order Table */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        table th,
        table td {
            border: 1px solid var(--gray);
            padding: 10px;
            text-align: left;
        }

        table th {
            background-color: var(--mint);
            color: var(--dark-gray);
        }

        table tbody tr:nth-child(even) {
            background-color: var(--gray);
        }

        /* Button Styles */
        .btn {
            background-color: var(--mint);
            color: var(--dark-gray);
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin-top: 10px;
            display: inline-block;
        }

        .btn:hover {
            background-color: var(--sage);
        }

        /* Responsive Adjustments */
        @media (max-width: 768px) {
            .sidebar {
                width: 200px;
            }

            .main-content {
                margin-left: 200px;
            }

            .header input {
                width: 100%;
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

        <!-- Full-Screen Logo Preview -->


            <ul>
                <li><a href="dashboard.php" class="active">Dashboard</a></li>
                <li><a href="category.php">Category Management</a></li>
                <li><a href="product_management.php">Food Management</a></li>
                <li><a href="user.php">Users</a></li>
                <li><a href="review_feedback.php">Ratings & Feedback</a></li>
                <li><a href="#">Notifications</a></li>
                <li><a href="#">Payments</a></li>
                <li><a href="order_history.php">Order History</a></li>
            </ul>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <div class="header">
                <input type="text" placeholder="Search...">
                <h3><?php echo $branchName ?></h3>
                <a href= "logout.php"><button class="btn">Log Out</button></a>
            </div>

            <div class="content">
                <h1>Welcome to Your Dashboard</h1>
                <h2><?php echo $branchName ?></h2>
                <div class="dashboard-cards">
                    <div class="card">
                        <h3>Total products</h3>
                        <p><?php echo $totalProducts; ?></p>
                    </div>

                    <div class="card">
                        <h3>Total Users</h3>
                        <p><?php echo $totalUsers; ?></p>
                    </div>

                    <div class="card">
                        <h3>Most Rated Product</h3>
                        <p><?php echo $mostRatedProductName; ?></p>
                    </div>

                    <div class="card">
                        <h3>Report</h3>
                        <button class="btn">Report</button>
                    </div>
                </div>

                <!-- Order List -->
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
                            <tr>
                                <td>001</td>
                                <td>Cheeseburger, Fries</td>
                                <td>Paid</td>
                                <td><button class="btn-ready">Food is Ready</button></td>
                            </tr>
                            <tr>
                                <td>002</td>
                                <td>Pizza</td>
                                <td>Pending</td>
                                <td><button class="btn-ready">Food is Ready</button></td>
                            </tr>
                            <tr>
                                <td>003</td>
                                <td>Pasta, Salad</td>
                                <td>Paid</td>
                                <td><button class="btn-ready">Food is Ready</button></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="overlay" id="logoOverlay">
    <span class="close-btn" onclick="closeLogo()">&times;</span>
    <img src="./uploads/<?php echo $branchLogo; ?>" alt="Full Logo">
    </div>

<script>
    function openLogo() {
        document.getElementById('logoOverlay').style.display = 'flex';
    }
    function closeLogo() {
        document.getElementById('logoOverlay').style.display = 'none';
    }
</script>
</body>
</html>
