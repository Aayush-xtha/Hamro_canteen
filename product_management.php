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
            --mint: #A8D5BA;
            --sage: #C1DAB4;
            --moss: #6D8B74;
            --white: #FFFFFF;
            --gray: #F5F5F5;
            --dark-gray: #3A3A3A;
            --red: #FF6B6B;
            --blue: #6B9BFF;
            --shadow: rgba(0, 0, 0, 0.1);
            --border-radius: 10px;
            --transition: 0.3s ease;
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
            box-shadow: 2px 0 5px var(--shadow);
        }

        .sidebar .logo {
            font-size: 1.5rem;
            font-weight: bold;
            margin-bottom: 30px;
        }

        .sidebar ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .sidebar ul li {
            margin: 15px 0;
        }

        .sidebar ul li a {
            text-decoration: none;
            color: var(--white);
            font-size: 1rem;
            padding: 10px;
            border-radius: var(--border-radius);
            display: block;
            transition: background-color var(--transition);
        }

        .sidebar ul li a:hover,
        .sidebar ul li a.active {
            background-color: var(--mint);
            font-weight: bold;
            color: var(--dark-gray);
        }

        .main-content {
            margin-left: 270px;
            padding: 40px;
            flex: 1;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: right;
            margin-bottom: 30px;
        }

        .header h1 {
            margin: 0 ;
            font-size: 2rem;
            text-align: center;
        }

        .search-bar {
            display: flex;
            gap: 10px;
        }


        .search-bar input {
            padding: 10px;
            border: 1px solid var(--moss);
            border-radius: var(--border-radius);
            width: 250px;
        }

        .search-bar input:focus {
            outline: none;
            border-color: var(--mint);
        }

        .content {
            margin-top: 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }

        .add-product {
            background-color: var(--white);
            padding: 30px;
            border-radius: var(--border-radius);
            box-shadow: 0 4px 8px var(--shadow);
            max-width: 500px;
            width: 100%;
            margin-bottom: 30px;
        }

        .add-product h2 {
            margin-bottom: 15px;
            font-size: 1.5rem;
            text-align: center;
        }

        .add-product form {
            display: flex;
            flex-direction: column;
            gap: 15px;
            width: 100%;
        }

        .add-product form label {
            font-weight: bold;
            margin-bottom: 5px;
        }

        .add-product form input,
        .add-product form textarea,
        .add-product form select {
            padding: 10px;
            border: 1px solid var(--moss);
            border-radius: var(--border-radius);
            font-size: 1rem;
            width: 100%;
            box-sizing: border-box;
        }

        .add-product form textarea {
            resize: none;
            height: 80px;
        }

        .add-product form button {
            padding: 12px;
            border: none;
            border-radius: var(--border-radius);
            background-color: var(--moss);
            color: var(--white);
            cursor: pointer;
            font-size: 1rem;
            text-align: center;
            transition: background-color var(--transition);
        }

        .add-product form button:hover {
            background-color: var(--mint);
            color: var(--dark-gray);
        }
        

        .category-filter {
            margin-top: 30px; /* Adds space above the category filter */
            margin-bottom: 30px;
            text-align: left;
        }

        .category-filter form select {
            width: 200px; /* Set the select dropdown width */
            padding: 10px 15px;
            font-size: 16px;
            border-radius: 5px;
            border: 1px solid #ccc;
            background-color: #fff;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); 
            display: inline-block; 
            position: relative;
        }

        .category-filter form select:focus {
            border-color: #007bff; /
            outline: none; 
        }

        .category-filter form select option {
            padding: 10px;
        }
        .product-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            width: 95%; /* Adjust to take full space */
            /* max-width: 1200px; Set a max width for grid */
            margin: 0 auto; /* Center the grid on the page */
            margin-bottom: 20px;
        }

        .product-card {
            background-color: var(--white);
            border-radius: var(--border-radius);
            box-shadow: 0 4px 8px var(--shadow);
            padding: 15px;
            width: 320px;
            height: 380px;
            text-align: center;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .product-card h3 {
            margin: 10px 0;
            font-size: 1.2rem;
        }

        .product-card p {
            font-size: 1rem;
            margin: 5px 0;
        }
        .product-card img {
            width: 100%;
            height: 240px;
            object-fit: cover;
            border-radius: var(--border-radius);
            margin-bottom: 10px;
            display: block;
            margin: 0 auto;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .product-card img:hover {
            transform: scale(1.1);
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.2);
        }
        .product-info {
            display: flex;
            justify-content: space-between; 
            width: 100%;
            align-items: center; 
            margin-top: 15px;
            margin-bottom: 10px;
        }



        /* Style for the buttons */
        .btn {
            display: inline-block;
            padding: 8px 12px;
            border: none;
            cursor: pointer;
            margin-top: 10px;
            border-radius: 5px;
            transition: background-color 0.3s ease, transform 0.2s ease; /* Add transition for smooth hover effects */
        }

        /* Edit button styling */
        .edit-btn {
            background-color: blue;
            color: white;
            margin-right: 5px;
        }

        /* Delete button styling */
        .delete-btn {
            background-color: red;
            color: white;
        }

        /* Hover effect for buttons */
        .edit-btn:hover {
            background-color: darkblue;
            transform: scale(1.05); /* Slightly enlarge the button on hover */
        }

        .delete-btn:hover {
            background-color: darkred;
            transform: scale(1.05); /* Slightly enlarge the button on hover */
        }

        /* Focus effect for buttons (optional) */
        .btn:focus {
            outline: none;
            box-shadow: 0 0 5px rgba(0, 0, 255, 0.5); /* Adds a glow effect when the button is focused */
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
                            <p class="price">Price: $<?php echo number_format($product['price'], 2); ?> </p>

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
                            <p class="price">Price: $<?php echo number_format($product['price'], 2); ?> </p>

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
