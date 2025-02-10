<?php
require_once('./database/db_connection.php');
session_start();

// Check if the user is logged in
if (!isset($_SESSION['id'])) {
    header("Location: index.php");
    exit();
}

// Handle form submission for adding a new product
if (isset($_POST['food_name'], $_POST['price'], $_POST['description'], $_POST['category_id'], $_POST['branch_id'])) {
    $foodName = $_POST['food_name'];
    $price = $_POST['price'];
    $description = $_POST['description'];
    $categoryId = $_POST['category_id'];
    $branchId = $_POST['branch_id'];
    $filename = null;

    // Handle file upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $filename = $_FILES['image']['name'];
        $tempname = $_FILES['image']['tmp_name'];
        $folder = "./uploads/" . $filename;

        // Check if the file is successfully uploaded
        if (!move_uploaded_file($tempname, $folder)) {
            $_SESSION['flash_message'] = "Failed to upload image.";
            $_SESSION['flash_status'] = "error";
            header("Location: product_management.php");
            exit();
        }
    }

    // Prepare the SQL query to insert the product into the database
    if ($filename !== null) {
        // If image is uploaded, include the image filename in the query
        $sql = "INSERT INTO foods (food_name, price, description, image, category_id, branch_id) 
                VALUES ('$foodName', '$price', '$description', '$filename', '$categoryId', '$branchId')";
    } else {
        // If no image is uploaded, insert without the image
        $sql = "INSERT INTO foods (food_name, price, description, category_id, branch_id) 
                VALUES ('$foodName', '$price', '$description', '$categoryId', '$branchId')";
    }

    // Execute the query and check for errors
    $result = mysqli_query($conn, $sql);

    if ($result) {
        $_SESSION['flash_message'] = "Product added successfully.";
        $_SESSION['flash_status'] = "success";
        header("Location: product_management.php");
        exit();
    } else {
        // Capture any SQL errors
        $_SESSION['flash_message'] = "Failed to add product: " . mysqli_error($conn);
        $_SESSION['flash_status'] = "error";
        header("Location: product_management.php");
        exit();
    }
} else {
    // Check if any required POST fields are missing
    $_SESSION['flash_message'] = "Missing required fields.";
    $_SESSION['flash_status'] = "error";
    header("Location: product_management.php");
    exit();
}
?>
