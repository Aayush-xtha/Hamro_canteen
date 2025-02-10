<?php
require_once('./database/db_connection.php');
session_start();
if (!isset($_SESSION['id'])) {
    header("Location: index.php");
    exit();
}

if (isset($_POST['category_name']) && isset($_POST['branch_id'])  ) {
    $categoryName = $_POST['category_name'];
    $branchId = $_POST['branch_id'];
    
    $checkSql = "SELECT * FROM categories WHERE category_name = '$categoryName'";
    $checkResult = mysqli_query($conn, $checkSql);

    if (mysqli_num_rows($checkResult) > 0) {
        $_SESSION['flash_message'] = "Category already exists!";
        $_SESSION['flash_status'] = "error";
        header("Location: category.php");
    } else {
        $filename = null;

        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $filename = $_FILES['image']['name'];
            $tempname = $_FILES['image']['tmp_name'];
            $folder = "./uploads/" . $filename;

            if (!move_uploaded_file($tempname, $folder)) {
                echo "Failed to upload image";
            }
        }

        if ($filename !== null) {
            $sql = "INSERT INTO categories (category_name, image, branch_id) VALUES ('$categoryName', '$filename', '$branchId')";
        } else {
            $sql = "INSERT INTO category (category_name) VALUES ('$categoryName', '$branchId')";
        }

        $result = mysqli_query($conn, $sql);

        if ($result) {
            $_SESSION['flash_message'] = "Category added successfully";
            $_SESSION['flash_status'] = "success";
            header("Location: category.php");
            exit();
        } else {
            $_SESSION['flash_message'] = "Category not added";
            $_SESSION['flash_status'] = "error";
            header("Location: category.php");
            exit();
        }
    }
}
?>
