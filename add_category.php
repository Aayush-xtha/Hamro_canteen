<?php
require_once('./database/db_connection.php');
session_start();

// Redirect if not logged in
if (!isset($_SESSION['id'])) {
    header("Location: index.php");
    exit();
}

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (isset($_POST['category_name']) && isset($_POST['branch_id'])) {
    $categoryName = mysqli_real_escape_string($conn, $_POST['category_name']);
    $branchId = mysqli_real_escape_string($conn, $_POST['branch_id']);
    $filename = null;

    // Image Upload Handling
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $filename = basename($_FILES['image']['name']);
        $tempname = $_FILES['image']['tmp_name'];
        $folder = "./uploads/" . $filename;

        // Check file type (Only allow JPG, PNG, GIF)
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        if (!in_array($_FILES['image']['type'], $allowedTypes)) {
            $_SESSION['flash_message'] = "Invalid image format! Only JPG, PNG, or GIF allowed.";
            $_SESSION['flash_status'] = "error";
            header("Location: category.php");
            exit();
        }

        // Move file to uploads folder
        if (!move_uploaded_file($tempname, $folder)) {
            $_SESSION['flash_message'] = "Failed to upload image.";
            $_SESSION['flash_status'] = "error";
            header("Location: category.php");
            exit();
        }
    }
    

    // Insert category into database
    if ($filename !== null) {
        $sql = "INSERT INTO categories (category_name, image, branch_id) VALUES ('$categoryName', '$filename', '$branchId')";
    } else {
        $sql = "INSERT INTO categories (category_name, branch_id) VALUES ('$categoryName', '$branchId')";
    }

    if (mysqli_query($conn, $sql)) {
        $_SESSION['flash_message'] = "Category added successfully";
        $_SESSION['flash_status'] = "success";
    } else {
        $_SESSION['flash_message'] = "Category not added: " . mysqli_error($conn);
        $_SESSION['flash_status'] = "error";
    }

    header("Location: category.php");
    exit();
    
}
?>
