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
    <title>Menu Management</title>
    <style>
        :root {
            --mint: #A8D5BA;
            --sage: #C1DAB4;
            --moss: #6D8B74;
            --white: #FFFFFF;
            --gray: #F5F5F5;
            --dark-gray: #3A3A3A;
            --hover-color: #B4E4CA;
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
            overflow: hidden;
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
            box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1);
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

        .container {
            margin-left: 270px;
            padding: 40px;
            flex: 1;
            text-align: center;
        }

        h1 {
            font-size: 2rem;
            margin-bottom: 30px;
            color: var(--moss);
        }

        .form-section {
            background-color: var(--white);
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            max-width: 500px;
            margin: 0 auto;
        }

        .form-section h2 {
            margin-bottom: 20px;
            color: var(--moss);
        }

        .form-section form {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
        }

        .form-section form label {
            margin-bottom: 5px;
            font-weight: bold;
        }

        .form-section form input,
        .form-section form button {
            width: calc(100% - 20px);
            margin-bottom: 15px;
            padding: 10px;
            border: 1px solid var(--sage);
            border-radius: 5px;
            font-size: 1rem;
        }

        .form-section form button {
            background-color: var(--mint);
            color: var(--dark-gray);
            border: none;
            cursor: pointer;
            transition: background-color 0.3s ease;
            align-self: center;
            padding: 10px 20px;
            font-size: 1rem;
        }

        .form-section form button:hover {
            background-color: var(--hover-color);
        }

        .list-section table {
            width: 100%;
            border-collapse: collapse;
        }

        .list-section table th, .list-section table td {
            padding: 10px;
            border: 1px solid var(--gray);
            text-align: left;
        }

        .list-section table th {
            background-color: var(--mint);
            color: var(--white);
        }

        .list-section img {
            width: 50px;
            height: 50px;
            border-radius: 5px;
            object-fit: cover;
        }

        .edit-btn, .delete-btn {
            padding: 5px 10px;
            text-decoration: none;
            color: var(--white);
            border-radius: 5px;
            font-size: 0.9rem;
            margin: 2px;
            display: inline-block;
        }

        .edit-btn {
            background-color: #4CAF50;
        }

        .delete-btn {
            background-color: #f44336;
        }

        .edit-btn:hover {
            background-color: #45a049;
        }

        .delete-btn:hover {
            background-color: #d32f2f;
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
                <li><a href="category.php" class="active">Category Management</a></li>
                <li><a href="product_management.php">Food Management</a></li>
                <li><a href="user.php">Users</a></li>
                <li><a href="review_feedback.php">Ratings & Feedback</a></li>
                <li><a href="#">Notifications</a></li>
                <li><a href="#">Payments</a></li>
                <li><a href="order_history.php">Order History</a></li>
            </ul>
        </div>

        <div class="container">
            <h1>Add Categories</h1>

            <div class="form-section">
                <h2>Add new Category to menu</h2>
                <form action="add_category.php" method="POST" enctype="multipart/form-data">
                    <label for="category_name">Name:</label>
                    <input type="text" id="category_name" name="category_name" placeholder="Enter Category Name" required>
                    
                    <label for="image">Image:</label>
                    <input type="file" id="image" name="image" accept="image/*" required>
                    <input type="hidden" name="branch_id" value="<?php echo $_SESSION['id']; ?>" />
                    <button type="submit">Add Category</button>
                </form>
            </div>

            <div class="list-section">
                <h2>Category List</h2>
                <table>
                    <thead>
                        <tr>
                            
                            <th>Name</th>
                            
                            <th>Image</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // require "./database/db_connection.php";
                        $sql = "SELECT * FROM categories WHERE branch_id = '$branch_id'";
                        $result = mysqli_query($conn, $sql);

                        if (mysqli_num_rows($result) > 0) {
                            while ($row = mysqli_fetch_assoc($result)) {
                                echo "<tr>
                                    
                                    <td>{$row['category_name']}</td>
                                    
                                    <td><img src='./uploads/{$row['image']}' alt='Food Image'></td>
                                    <td>
                                        <a href='edit_category.php?id={$row['id']}' class='edit-btn'>Edit</a>
                                        <a href='delete_category.php?id={$row['id']}' class='delete-btn' onclick='return confirm(\"Are you sure?\")'>Delete</a>
                                    </td>
                                </tr>";
                            }
                        } else {
                            echo "<tr><td colspan='5'>No category available</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
