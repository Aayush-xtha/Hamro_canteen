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

    /* Sidebar Styling (Same as dashboard) */
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

    h1 {
        color: var(--moss-dark);
        margin-bottom: 10px;
        border-bottom: 2px solid var(--mint);
        padding-bottom: 10px;
    }

    .form-section,
    .list-section {
        background-color: var(--white);
        border-radius: 15px;
        padding: 25px;
        margin-bottom: 20px;
        box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        border: 1px solid var(--gray-border);
    }

    .form-section h2,
    .list-section h2 {
        color: var(--moss-light);
        margin-bottom: 20px;
        border-bottom: 2px solid var(--mint);
        padding-bottom: 10px;
    }

    .form-section form {
        display: grid;
        gap: 15px;
    }

    .form-section label {
        font-weight: bold;
        color: var(--moss-dark);
    }

    .form-section input[type="text"],
    .form-section input[type="file"] {
        width: 100%;
        padding: 10px 15px;
        border: 1px solid var(--gray-border);
        border-radius: 8px;
        transition: all 0.3s ease;
    }

    .form-section input[type="text"]:focus,
    .form-section input[type="file"]:focus {
        outline: none;
        border-color: var(--moss-dark);
        box-shadow: 0 0 0 3px rgba(151,193,169,0.2);
    }

    .form-section button {
        background-color: var(--moss-dark);
        color: var(--white);
        border: none;
        padding: 12px 20px;
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.3s ease;
        text-transform: uppercase;
        letter-spacing: 1px;
        justify-self: start;
    }

    .form-section button:hover {
        background-color: var(--mint);
        color: var(--moss-dark);
        transform: translateY(-3px);
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
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

    .list-section table td img {
        max-width: 100px;
        max-height: 100px;
        object-fit: cover;
        border-radius: 8px;
        transition: transform 0.3s ease;
    }

    .list-section table td img:hover {
        transform: scale(1.1);
    }

    .edit-btn,
    .delete-btn {
        display: inline-block;
        padding: 8px 15px;
        margin-right: 5px;
        text-decoration: none;
        border-radius: 8px;
        transition: all 0.3s ease;
        text-transform: uppercase;
        font-size: 0.9em;
        letter-spacing: 1px;
    }

    .edit-btn {
        background-color: var(--moss-light);
        color: var(--white);
    }

    .delete-btn {
        background-color: var(--accent-red);
        color: var(--white);
    }

    .edit-btn:hover {
        background-color: var(--mint);
        color: var(--moss-dark);
        transform: translateY(-3px);
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }

    .delete-btn:hover {
        background-color: #c0392b;
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
    }

    @media screen and (max-width: 768px) {
        .container {
            padding: 15px;
        }

        .form-section form {
            grid-template-columns: 1fr;
        }

        .list-section table {
            font-size: 0.9em;
        }

        .list-section table td img {
            max-width: 50px;
            max-height: 50px;
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
                <li><a href="category.php" class="active">Category Management</a></li>
                <li><a href="product_management.php">Food Management</a></li>
                <li><a href="staff.php">Staff</a></li>
                
                <li><a href="review_feedback.php">Ratings & Feedback</a></li>
                <li><a href="report.php">Report</a></li>

                <li><a href="profile.php">Profile</a></li>
                
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
