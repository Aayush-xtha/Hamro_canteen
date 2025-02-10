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
                <li><a href="#">Notifications</a></li>
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
                            <th>Username</th>
                            <th>Order Items</th>
                            <th>Order Status</th>
                            <th>Payment Method</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- You can replace this part with PHP code to fetch and display orders from your database -->
                        <tr>
                            <td>1</td>
                            <td>JohnDoe</td>
                            <td>Pizza, Coke</td>
                            <td>Completed</td>
                            <td>Credit Card</td>
                        </tr>
                        <tr>
                            <td>2</td>
                            <td>JaneSmith</td>
                            <td>Burger, Fries</td>
                            <td>Pending</td>
                            <td>PayPal</td>
                        </tr>
                        <!-- Add more rows as needed -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
