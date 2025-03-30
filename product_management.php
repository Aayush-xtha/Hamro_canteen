<?php
require_once('./database/db_connection.php');
require_once('global.php');

session_start();
if (!isset($_SESSION['id'])) {
    header("Location: index.php");
    exit();
}
$branch_id = $_SESSION['id'];

// Fetch categories from the database
$categorySql = "SELECT id AS category_id, category_name FROM categories WHERE branch_id = '$branch_id'";
$categoryResult = mysqli_query($conn, $categorySql);

if (!$categoryResult) {
    die("Error fetching categories: " . mysqli_error($conn));
}

// Filtering products by category
$categoryFilter = "";
if (isset($_GET['category_id']) && $_GET['category_id'] != '') {
    $category_id = intval($_GET['category_id']);
    $categoryFilter = " AND category_id = $category_id";
}
$sql = "SELECT * FROM branches WHERE id = '$branch_id'";
$result = mysqli_query($conn, $sql);
if($result->num_rows >0){
    $row = $result->fetch_assoc();
    $branchName = $row['branch_name'];
}

// Fetch products for the logged-in branch
$productSql = "SELECT * FROM foods WHERE branch_id = '$branch_id'" . $categoryFilter;
$productResult = mysqli_query($conn, $productSql);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Food Management</title>
</head>
<body>
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

        .sidebar ul {
            list-style: none;
        }

        .sidebar ul li {
            margin: 15px 0;
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
                
        /* Main Content */
        .main-content {
            flex-grow: 1;
            padding: 30px;
            background-color: var(--light-gray);
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            background-color: var(--white);
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .header h1 {
            color: var(--moss-dark);
            font-size: 2em;
            font-weight: 600;
        }

        .search-bar {
            display: flex;
            background-color: var(--white);
            border-radius: 25px;
            box-shadow: 0 3px 8px rgba(0, 0, 0, 0.05);
            overflow: hidden;
        }

        .search-bar input {
            border: none;
            padding: 12px 15px;
            width: 220px;
            outline: none;
        }

        .search-bar .btn {
            background-color: var(--moss-dark);
            color: var(--white);
            border: none;
            padding: 12px 18px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .search-bar .btn:hover {
            background-color: var(--mint);
            color: var(--moss-dark);
        }

        /* Forms & Inputs */
        .add-product, .category-filter {
            background-color: var(--white);
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
            border: 1px solid var(--gray-border);
            width: 80%;
            margin: 0 auto;
        }

        .add-product h2 {
            color: var(--moss-dark);
            font-size: 1.6em;
            margin-bottom: 15px;
        }

        .add-product form {
            display: grid;
            gap: 12px;
        }

        .add-product label {
            font-weight: 600;
        }

        .add-product input, 
        .add-product select, 
        .add-product textarea {
            width: 100%;
            padding: 10px;
            border: 2px solid var(--mint-light);
            border-radius: 6px;
            transition: all 0.3s ease;
        }

        .add-product input:focus, 
        .add-product select:focus, 
        .add-product textarea:focus {
            border-color: var(--moss-dark);
            outline: none;
            box-shadow: 0 0 0 3px rgba(151, 193, 169, 0.2);
        }

        /* Buttons */
        .btn {
            background-color: var(--moss-dark);
            color: var(--white);
            border: none;
            padding: 10px 20px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
            text-transform: uppercase;
        }

        .btn:hover {
            background-color: var(--mint);
            color: var(--moss-dark);
            transform: translateY(-2px);
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
        }

        /* Product Grid */
        .product-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
            gap: 25px;
        }

        /* Product Cards */
        .product-card {
            padding: 10px;
            background-color: var(--white);
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            border: 1px solid var(--gray-border);
        }

        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
        }

        .product-card img {
            width: 100%;
            height: 200px;
            object-fit: cover;
            transition: transform 0.3s ease;
        }

        .product-card:hover img {
            transform: scale(1.05);
        }

        .product-card h3 {
            padding: 15px;
            color: var(--moss-dark);
            font-size: 1.2em;
            background-color: var(--light-gray);
        }

        .product-card p {
            padding: 0 15px 15px;
            color: var(--dark-gray);
        }

        .product-info {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px;
            background-color: var(--light-gray);
            border-top: 1px solid var(--gray-border);
        }

        .price {
            font-weight: bold;
            color: var(--moss-dark);
            font-size: 1.1em;
        }

        .button-container {
            display: flex;
            gap: 8px;
        }

        .edit-btn, .delete-btn {
            padding: 8px 12px;
            font-size: 0.9em;
            border-radius: 5px;
            transition: all 0.3s ease;
        }

        .edit-btn {
            background-color: var(--accent-green);
            color: var(--white);
        }

        .delete-btn {
            
            background-color: var(--accent-red);
            color: var(--white);
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .header {
                flex-direction: column;
                text-align: center;
            }

            .search-bar {
                width: 100%;
                margin-top: 10px;
            }

            .product-grid {
                grid-template-columns: 1fr;
            }
        }


    </style>
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
                <li><a href="product_management.php" class="active">Food Management</a></li>
                
                <li><a href="staff.php">Staff</a></li>
                <li><a href="report.php">Report</a></li>

                <li><a href="profile.php">Profile</a></li>
                <li><a href="order_history.php">Order History</a></li>
            </ul>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <div class="header">
                <h1>Product Management</h1>
                <div class="search-bar">
                    <input type="text" placeholder="Search products..." />
                    <button class="btn">Search</button>
                </div>
            </div>

            <div class="content">
                <!-- Add Product Section -->
                <div class="add-product">
                    <h2>Add Product</h2>
                    <form action="add_product.php" method="POST" enctype="multipart/form-data">
                        <label for="name">Name:</label>
                        <input type="text" name="food_name" placeholder="Food Name" required />

                        <label for="category_id">Category:</label>
                        <select name="category_id" required>
                            <?php if ($categoryResult && mysqli_num_rows($categoryResult) > 0): ?>
                                <?php while ($category = mysqli_fetch_assoc($categoryResult)): ?>
                                    <option value="<?php echo $category['category_id']; ?>">
                                        <?php echo $category['category_name']; ?>
                                    </option>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <option value="">No categories available</option>
                            <?php endif; ?>
                        </select>

                        <label for="price">Price:</label>
                        <input type="number" name="price" placeholder="Price" required />

                        <label for="description">Description:</label>
                        <textarea name="description" placeholder="Description" required></textarea>

                        <label for="image">Image:</label>
                        <input type="file" name="image" required />

                        <input type="hidden" name="branch_id" value="<?php echo $_SESSION['id']; ?>" />
                        <button type="submit" class="btn">Add Product</button>
                    </form>
                </div>
                <div class="category-filter">
                    <form method="GET" action="">
                        <label for="category">Select Category:</label>
                        <select name="category_id" required onchange="this.form.submit()">
                            <option value="">All Categories</option>
                            <?php
                            $categorySql = "SELECT id AS category_id, category_name FROM categories WHERE branch_id = '$branch_id'";
                            $categoryResult = mysqli_query($conn, $categorySql);
                            while ($category = mysqli_fetch_assoc($categoryResult)) {
                                $selected = (isset($_GET['category_id']) && $_GET['category_id'] == $category['category_id']) ? 'selected' : '';
                                echo "<option value='{$category['category_id']}' $selected>{$category['category_name']}</option>";
                            }
                            ?>
                        </select>
                    </form>
                </div>
            </div>
            <div class="product-grid">
                <?php while ($product = mysqli_fetch_assoc($productResult)): ?>
                    <div class="product-card">
                        <img src="./uploads/<?php echo $product['image']; ?>" alt="<?php echo $product['food_name']; ?>">
                        <h3><?php echo $product['food_name']; ?></h3>
                        <p><?php echo $product['description']; ?></p>
                        <div class="product-info">
                            <p class="price">Price: RS<?php echo number_format($product['price'], 2); ?> </p>

                            <!-- Edit and Delete Buttons -->
                            <div class="button-container">
                                <button class="btn edit-btn" onclick="editProduct(<?php echo $product['id']; ?>)">Edit</button>
                                <button class="btn delete-btn" onclick="deleteProduct(<?php echo $product['id']; ?>)">Delete</button>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
                <?php while ($product = mysqli_fetch_assoc($productResult)): ?>
                        <div class="product-card">
                            <img src="./uploads/<?php echo $product['image']; ?>" alt="<?php echo $product['food_name']; ?>">
                            <h3><?php echo $product['food_name']; ?></h3>
                            <p><?php echo $product['description']; ?></p>
                        </div>
                        <div class="product-info">
                            <p class="price">Price: Rs<?php echo number_format($product['price'], 2); ?> </p>

                            <!-- Edit and Delete Buttons -->
                            <div class="button-container">
                                <button class="btn edit-btn" onclick="editProduct(<?php echo $product['id']; ?>)">Edit</button>
                                <button class="btn delete-btn" onclick="deleteProduct(<?php echo $product['id']; ?>)">Delete</button>
                            </div>
                    <?php endwhile; ?>
            </div>

            <script>
            function deleteProduct(id) {
                if (confirm("Are you sure you want to delete this product?")) {
                    window.location.href = "delete_product.php?id=" + id;
                }
            }

            function editProduct(id) {
                window.location.href = "edit_product.php?id=" + id;
            }
            </script>

        </div>
    </div>
</body>
</html>