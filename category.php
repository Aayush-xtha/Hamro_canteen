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
    <link rel="stylesheet" href="category.css">
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
