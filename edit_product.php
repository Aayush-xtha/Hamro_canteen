<?php
require_once('./database/db_connection.php');
require_once('global.php');

session_start();
if (!isset($_SESSION['id'])) {
    header("Location: index.php");
    exit();
}

if (isset($_GET['id'])) {
    $productId = $_GET['id'];

    // Fetch product details
    $query = "SELECT * FROM foods WHERE id = '$productId'";
    $result = mysqli_query($conn, $query);
    $product = mysqli_fetch_assoc($result);

    if (!$product) {
        die("Product not found.");
    }
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $foodName = $_POST['food_name'];
    $price = $_POST['price'];
    $description = $_POST['description'];
    $categoryId = $_POST['category_id'];
    $date = date("Y-m-d H:i:s");
    $filename = $product['image']; // Keep old image by default

    // Handle image upload if a new one is provided
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $filename = $_FILES['image']['name'];
        $tempname = $_FILES['image']['tmp_name'];
        $folder = "./uploads/" . $filename;

        if (move_uploaded_file($tempname, $folder)) {
            // Delete old image if a new one is uploaded
            $oldImagePath = "./uploads/" . $product['image'];
            if (file_exists($oldImagePath) && !empty($product['image'])) {
                unlink($oldImagePath);
            }
        } else {
            echo "Failed to upload new image.";
            $filename = $product['image']; // Keep old image if upload fails
        }
    }

    // Update product details in the database
    $updateQuery = "UPDATE foods SET 
                    food_name = '$foodName', 
                    price = '$price', 
                    description = '$description', 
                    image = '$filename',
                    category_id = '$categoryId',
                    updated_at = '$date' 
                    WHERE id = '$productId'";

    if (mysqli_query($conn, $updateQuery)) {
        $_SESSION['flash_message'] = "Product updated successfully.";
        $_SESSION['flash_status'] = "success";
        header("Location: product_management.php");
        exit();
    } else {
        echo "Error updating product: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Product</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .container {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
        }

        h2 {
            text-align: center;
            color: #333;
            margin-bottom: 20px;
        }

        form {
            display: grid;
            gap: 10px;
        }

        label {
            font-weight: bold;
            color: #555;
        }

        input, select, textarea {
            width: 90%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 14px;
        }

        textarea {
            resize: vertical;
            height: 100px;
        }

        button {
            padding: 10px;
            background-color: #28a745;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            transition: 0.3s ease;
        }

        button:hover {
            background-color: #218838;
        }

        img {
            margin-top: 10px;
            border-radius: 5px;
            max-width: 100%;
        }

        a {
            display: block;
            text-align: center;
            margin-top: 15px;
            color: #007bff;
            text-decoration: none;
            font-weight: bold;
        }

        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Edit Product</h2>
        <form action="edit_product.php?id=<?php echo $productId; ?>" method="POST" enctype="multipart/form-data">
            <label for="food_name">Name:</label>
            <input type="text" name="food_name" value="<?php echo htmlspecialchars($product['food_name']); ?>" required />

            <label for="category_id">Category:</label>
            <select name="category_id" required>
                <?php
                $categorySql = "SELECT id AS category_id, category_name FROM categories";
                $categoryResult = mysqli_query($conn, $categorySql);
                while ($category = mysqli_fetch_assoc($categoryResult)) {
                    $selected = ($category['category_id'] == $product['category_id']) ? "selected" : "";
                    echo "<option value='{$category['category_id']}' $selected>{$category['category_name']}</option>";
                }
                ?>
            </select>

            <label for="price">Price:</label>
            <input type="number" step="0.01" name="price" value="<?php echo $product['price']; ?>" required />

            <label for="description">Description:</label>
            <textarea name="description" required><?php echo htmlspecialchars($product['description']); ?></textarea>

            <label for="image">Image:</label>
            <input type="file" name="image" />
            <p>Current Image:</p>
            <img src="./uploads/<?php echo $product['image']; ?>" alt="Current Image" width="100">

            <button type="submit">Update Product</button>
        </form>
        <a href="product_management.php">Back to Product Management</a>
    </div>
</body>
</html>
