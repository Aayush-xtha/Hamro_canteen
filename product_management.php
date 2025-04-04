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
$productSql = "SELECT * FROM foods WHERE branch_id = '$branch_id'" . $categoryFilter;
$productResult = mysqli_query($conn, $productSql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Food Management</title>
    <link rel="stylesheet" href="food.css">
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
                <li><a href="product_management.php" class="active">Food Management</a></li>
                <li><a href="staff.php">Staff</a></li>
                <li><a href="report.php">Report</a></li>
                <li><a href="profile.php">Profile</a></li>
                <li><a href="order_history.php">Order History</a></li>
            </ul>
        </div>
        <div class="main-content">
            <div class="header">
                <h1>Product Management</h1>
                <div class="search-bar">
                    <input type="text" placeholder="Search products..." />
                    <button class="btn">Search</button>
                </div>
            </div>
            <div class="content">
                <div class="add-product">
                    <h2>Add Product</h2>
                    <form action="add_product.php" method="POST" enctype="multipart/form-data">
                        <label for="name">Name:</label>
                        <input type="text" name="food_name" id="name" placeholder="Food Name" required />

                        <label for="category_id">Category:</label>
                        <select name="category_id" id="category_id" required>
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
                        <input type="number" name="price" id="price" placeholder="Price" required />

                        <label for="description">Description:</label>
                        <textarea name="description" id="description" placeholder="Description" rows="4" required></textarea>

                        <label for="image">Image:</label>
                        <input type="file" name="image" id="image" required />

                        <input type="hidden" name="branch_id" value="<?php echo $_SESSION['id']; ?>" />
                        <button type="submit" class="btn">Add Product</button>
                    </form>
                </div>
                <div class="category-filter">
                    <form method="GET" action="">
                        <label for="category">Select Category:</label>
                        <select name="category_id" id="category" onchange="this.form.submit()">
                            <option value="">All Categories</option>
                            <?php
                            mysqli_data_seek($categoryResult, 0);
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
                <?php if (mysqli_num_rows($productResult) > 0): ?>
                    <?php while ($product = mysqli_fetch_assoc($productResult)): ?>
                        <div class="product-card">
                            <img src="./uploads/<?php echo $product['image']; ?>" alt="<?php echo $product['food_name']; ?>">
                            <h3><?php echo $product['food_name']; ?></h3>
                            
                            <div class="product-description">
                                <div class="description-text"><?php echo $product['description']; ?></div>
                                <span class="show-more" data-product-id="<?php echo $product['id']; ?>">Show more</span>
                            </div>
                            
                            <div class="product-info">
                                <p class="price">₹<?php echo number_format($product['price'], 2); ?></p>
                                <div class="button-container">
                                    <button class="btn edit-btn" onclick="editProduct(<?php echo $product['id']; ?>)">Edit</button>
                                    <button class="btn delete-btn" onclick="deleteProduct(<?php echo $product['id']; ?>)">Delete</button>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div style="grid-column: 1 / -1; text-align: center; padding: 30px; background: var(--white); border-radius: 12px;">
                        <h3 style="color: var(--moss-dark);">No products found</h3>
                        <p style="margin-top: 10px; color: var(--dark-gray);">Add your first product using the form above or select a different category.</p>
                    </div>
                <?php endif; ?>
            </div>


            <div id="descriptionModal" class="description-modal">
                <div class="modal-content">
                    <span class="close-modal" onclick="closeDescriptionModal()">&times;</span>
                    <h3 class="modal-title" id="modalTitle"></h3>
                    <div class="modal-description" id="modalDescription"></div>
                </div>
            </div>
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


        function openFullScreenLogo(src) {
            const modal = document.createElement('div');
            modal.style.position = 'fixed';
            modal.style.top = '0';
            modal.style.left = '0';
            modal.style.width = '100%';
            modal.style.height = '100%';
            modal.style.backgroundColor = 'rgba(0,0,0,0.8)';
            modal.style.display = 'flex';
            modal.style.justifyContent = 'center';
            modal.style.alignItems = 'center';
            modal.style.zIndex = '2000';
            
            const img = document.createElement('img');
            img.src = src;
            img.style.maxWidth = '80%';
            img.style.maxHeight = '80%';
            img.style.borderRadius = '10px';
            
            modal.appendChild(img);
            document.body.appendChild(modal);
            
            modal.addEventListener('click', function() {
                document.body.removeChild(modal);
            });
        }
        document.addEventListener('DOMContentLoaded', function() {
            // Get all description elements
            const descriptions = document.querySelectorAll('.description-text');
            const showMoreButtons = document.querySelectorAll('.show-more');
            
            // Check each description to see if it needs a "show more" button
            descriptions.forEach((desc, index) => {
                // If the description is not truncated (doesn't overflow), hide the button
                if (desc.scrollHeight <= desc.clientHeight) {
                    showMoreButtons[index].style.display = 'none';
                }
            });
            
            // Add click event to all "show more" buttons
            showMoreButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const productId = this.getAttribute('data-product-id');
                    const productCard = this.closest('.product-card');
                    const title = productCard.querySelector('h3').textContent;
                    const description = productCard.querySelector('.description-text').textContent;
                    
                    // Set modal content
                    document.getElementById('modalTitle').textContent = title;
                    document.getElementById('modalDescription').textContent = description;
                    
                    // Show modal
                    document.getElementById('descriptionModal').style.display = 'block';
                });
            });
        });

        // Function to close the description modal
        function closeDescriptionModal() {
            document.getElementById('descriptionModal').style.display = 'none';
        }

        // Close modal when clicking outside of it
        window.onclick = function(event) {
            const modal = document.getElementById('descriptionModal');
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        }
    </script>
</body>
</html>