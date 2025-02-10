<?php
require_once('./database/db_connection.php');
session_start();
if (!isset($_SESSION['id'])) {
    header("Location: index.php");
    exit();
}
if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Fetch the image name to delete it from the server
    $sql = "SELECT image FROM category WHERE Id = $id";
    $result = mysqli_query($conn, $sql);

    if ($result && mysqli_num_rows($result) == 1) {
        $row = mysqli_fetch_assoc($result);
        $image = $row['image'];
        $image_path = "./uploads/" . $image;

        // Delete the image file if it exists
        if (file_exists($image_path)) {
            unlink($image_path);
        }

        // Delete the menu item from the database
        $delete_sql = "DELETE FROM categories WHERE Id = $id";

        if (mysqli_query($conn, $delete_sql)) {
            echo "<script>alert('Menu item deleted successfully!'); window.location='category.php';</script>";
        } else {
            echo "<script>alert('Error deleting menu item.'); window.location='category.php';</script>";
        }
    } else {
        echo "<script>alert('Menu item not found.'); window.location='category.php';</script>";
    }
} else {
    echo "<script>alert('Invalid request.'); window.location='category.php';</script>";
}
?>
