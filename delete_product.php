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

    // Fetch product to get image file
    $query = "SELECT image FROM foods WHERE id = '$productId'";
    $result = mysqli_query($conn, $query);
    $product = mysqli_fetch_assoc($result);

    if ($product) {
        // Delete image file if it exists
        $imagePath = "./uploads/" . $product['image'];
        if (file_exists($imagePath) && !empty($product['image'])) {
            unlink($imagePath); // Delete the image file
        }

        // Delete product from database
        $deleteQuery = "DELETE FROM foods WHERE id = '$productId'";
        if (mysqli_query($conn, $deleteQuery)) {
            $_SESSION['flash_message'] = "Product deleted successfully.";
            $_SESSION['flash_status'] = "success";
        } else {
            $_SESSION['flash_message'] = "Failed to delete product.";
            $_SESSION['flash_status'] = "error";
        }
    } else {
        $_SESSION['flash_message'] = "Product not found.";
        $_SESSION['flash_status'] = "error";
    }
}

header("Location: product_management.php");
exit();
