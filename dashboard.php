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
    <!-- <link rel="stylesheet" href="side_bar.css"> -->

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
        .main-content {
            flex-grow: 1;
            background-color: var(--light-gray);
            overflow-y: auto;
        }

        .header {
            background-color: var(--white);
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 30px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
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

        .content {
            padding: 30px;
        }

        .content h1 {
            color: var(--moss-dark);
            margin-bottom: 10px;
            border-bottom: 2px solid var(--mint);
            padding-bottom: 10px;
        }

        .content h2 {
            color: var(--moss-light);
            margin-bottom: 20px;
        }

        .dashboard-cards {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }

        .card {
            background-color: var(--white);
            border-radius: 15px;
            padding: 25px;
            text-align: center;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            border: 1px solid var(--gray-border);
            transition: all 0.3s ease;
        }

        .card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.15);
        }

        .card h3 {
            color: var(--moss-dark);
            margin-bottom: 15px;
            font-size: 1.2em;
        }

        .card p {
            font-size: 1.8em;
            font-weight: bold;
            color: var(--moss-light);
        }

        .order-list {
            background-color: var(--white);
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            border: 1px solid var(--gray-border);
        }

        .order-list h2 {
            color: var(--moss-dark);
            margin-bottom: 20px;
            border-bottom: 2px solid var(--mint);
            padding-bottom: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        table thead {
            background-color: var(--light-gray);
        }

        table th, table td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid var(--gray-border);
        }

        .btn-ready {
            background-color: var(--accent-green);
            color: var(--white);
            border: none;
            padding: 10px 15px;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-ready:hover {
            background-color: var(--moss-light);
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        /* Responsive Design */
        @media screen and (max-width: 1200px) {
            .dashboard-cards {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media screen and (max-width: 768px) {
            .sidebar {
                width: 100%;
                height: auto;
            }

            .dashboard-cards {
                grid-template-columns: 1fr;
            }

            .header {
                flex-direction: column;
                align-items: flex-start;
            }

            .header input {
                width: 100%;
                margin-bottom: 10px;
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
                <li><a href="dashboard.php" class="active">Dashboard</a></li>
                <li><a href="category.php">Category Management</a></li>
                <li><a href="product_management.php">Food Management</a></li>
                <li><a href="staff.php">Staff</a></li>
                
                <li><a href="review_feedback.php">Ratings & Feedback</a></li>
                <li><a href="report.php">Report</a></li>
                <li><a href="profile.php">Profile</a></li>
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

</script>
</body>
</html>
