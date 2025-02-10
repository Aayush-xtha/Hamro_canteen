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
$categorySql = "SELECT id AS category_id, category_name FROM categories";
$categoryResult = mysqli_query($conn, $categorySql);

if (!$categoryResult) {
    die("Error fetching categories: " . mysqli_error($conn));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Food Management</title>
    <!-- <link rel="stylesheet" href="styles.css"> -->
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
.show_category_products {
    background-color: var(--white);
    padding: 20px;
    border-radius: var(--border-radius);
    box-shadow: 0 4px 8px var(--shadow);
    max-width: 400px;
    width: 100%;
    margin: 20px auto;
    text-align: center;
}

.show_category_products label {
    font-size: 1.2rem;
    font-weight: bold;
    color: var(--dark-gray);
    display: block;
    margin-bottom: 10px;
}

.show_category_products select {
    padding: 10px;
    border: 1px solid var(--moss);
    border-radius: var(--border-radius);
    font-size: 1rem;
    width: 100%;
    box-sizing: border-box;
    background-color: var(--gray);
    color: var(--dark-gray);
}

.show_category_products select:focus {
    outline: none;
    border-color: var(--mint);
    background-color: var(--white);
}

.ProductDisplay {
    margin-top: 20px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
}

.product-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    width: 100%; /* Adjust to take full space */
    max-width: 1200px; /* Set a max width for grid */
    margin: 0 auto; /* Center the grid on the page */
    margin-bottom: 20px;
}

.product-card {
    background-color: var(--white);
    border-radius: var(--border-radius);
    box-shadow: 0 4px 8px var(--shadow);
    padding: 15px;
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
    height: 150px;
    object-fit: cover;
    border-radius: var(--border-radius);
    margin-bottom: 10px;
    display: block; /* Centers the image */
    margin: 0 auto; /* Ensures horizontal centering */
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

/* Hover Effect */
.product-card img:hover {
    transform: scale(1.1);
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.2);
}


.btn {
    padding: 10px 15px;
    border: none;
    border-radius: var(--border-radius);
    cursor: pointer;
    font-size: 1rem;
    margin-top: 10px;
    transition: opacity var(--transition);
}

.btn:hover {
    opacity: 0.9;
}

.edit-btn {
    background-color: var(--blue);
    color: var(--white);
}

.delete-btn {
    background-color: var(--red);
    color: var(--white);
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
                <span>canteen</span>
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
                <div class ="show_category_products">
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
                </div>

            </div>
           <!-- Fetch and Display Products -->
<div class="ProductDisplay">
    <div class="product-grid">
        <?php
        $productSql = "SELECT * FROM foods";
        $productResult = mysqli_query($conn, $productSql);

        if ($productResult && mysqli_num_rows($productResult) > 0):
            while ($product = mysqli_fetch_assoc($productResult)): ?>
                <div class="product-card">
                    <img src="./uploads/<?php echo $product['image']; ?>" alt="<?php echo $product['food_name']; ?>">
                    <h3><?php echo $product['food_name']; ?></h3>
                    <p class="price">$<?php echo number_format($product['price'], 2); ?></p>
                    <p><?php echo $product['description']; ?></p>
                    <button class="btn delete-btn" onclick="deleteProduct(<?php echo $product['id']; ?>)">Delete</button>
                    <button class="btn edit-btn" onclick="editProduct(<?php echo $product['id']; ?>)">Edit</button>
                </div>
            <?php endwhile;
        else: ?>
            <p>No products available</p>
        <?php endif; ?>
    </div>
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
