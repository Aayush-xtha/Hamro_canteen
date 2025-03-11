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
$sql = "SELECT * FROM users";
$result = mysqli_query($conn, $sql);
$users = mysqli_fetch_all($result);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management</title>
    <style>
:root {
    --mint: #A8D5BA;
    --sage: #C1DAB4;
    --moss: #6D8B74;
    --white: #FFFFFF;
    --gray: #F5F5F5;
    --dark-gray: #3A3A3A;
    --light-gray: #E0E0E0;
    --hover-color: #B4E4CA;
    --active-color: #B0D8C0;
}

body {
    margin: 0;
    font-family: 'Arial', sans-serif;
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
    height: 100%;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: flex-start;
}

.container h1 {
    font-size: 2rem;
    color: var(--moss);
    text-align: center;
}

.form-section, .list-section {
    background-color: var(--white);
    border-radius: 10px;
    padding: 20px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    margin: 20px 0;
    width: 100%;
    max-width: 900px; /* Center content and limit width */
    transition: box-shadow 0.3s ease;
}

.form-section:hover, .list-section:hover {
    box-shadow: 0 6px 12px rgba(0, 0, 0, 0.2);
}

.form-section input {
    width: calc(100% - 20px);
    margin: 10px 0;
    padding: 10px;
    border: 1px solid var(--light-gray);
    border-radius: 5px;
    font-size: 1rem;
}

.form-section button {
    width: 50%;
    background-color: var(--mint);
    color: var(--white);
    font-weight: bold;
    cursor: pointer;
    padding: 12px;
    margin: 10px auto;
    border: none;
    border-radius: 5px;
    display: block;
    transition: background-color 0.3s ease;
}

.form-section button:hover {
    background-color: var(--hover-color);
}

.list-section table {
    width: 100%;
    border-collapse: collapse;
}

.list-section table th, 
.list-section table td {
    padding: 15px;
    border: 1px solid var(--light-gray);
    text-align: center;
    font-size: 1rem;
}

.list-section table th {
    background-color: var(--mint);
    color: var(--white);
    text-transform: uppercase;
}

.list-section table tbody tr:hover {
    background-color: var(--hover-color);
}

.list-section button {
    background-color: var(--moss);
    color: var(--white);
    padding: 8px 12px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-size: 0.9rem;
    transition: background-color 0.3s ease;
}

.list-section button:hover {
    background-color: var(--hover-color);
    color: var(--dark-gray);
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
                <li><a href="user.php" class="active">Users</a></li>
                <li><a href="review_feedback.php">Ratings & Feedback</a></li>
                <li><a href="report.php">Report</a></li>

                <li><a href="#">Payments</a></li>
                <li><a href="order_history.php">Order History</a></li>
            </ul>
        </div>

    <div class="container">
        <h1>User Management</h1>

        <!-- Form Section -->
        <div class="form-section">
            <h2>Search for the users</h2>
            <form>
                <input type="text" placeholder="Search users" required>
                <button type="submit">Search</button>
            </form>
        </div>

        <!-- List Section -->
        <div class="list-section">
            <h2>User List</h2>
            <table>
                <thead>
                    <tr>
                        
                        <th>Username</th>
                        <th>Role</th>
                        <th>Image</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user) { ?>
                        <tr>
                            <td><?php echo $user[3]; ?></td>
                            <td><?php echo $user[9]; ?></td>
                            <td><?php echo $user[8]; ?></td>
                            
                            <td>
                                <button>View</button>
                                
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
