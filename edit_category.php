<?php
require_once('./database/db_connection.php');
require_once('global.php');

session_start();
if (!isset($_SESSION['id'])) {
    header("Location: index.php");
    exit();
}

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Fetch existing category details
    $sql = "SELECT * FROM categories WHERE id = $id";
    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) == 1) {
        $row = mysqli_fetch_assoc($result);
        $category_name = $row['category_name'];
        $current_image = $row['image'];
    } else {
        echo "Category not found.";
        exit();
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == "POST" && isset($_GET['id'])) {
    $id = $_GET['id'];
    $category_name = mysqli_real_escape_string($conn, $_POST['category_name']);

    if (!empty($_FILES['image']['name'])) {
        $image = $_FILES['image']['name'];
        $target_directory = "./uploads/";
        $target_file = $target_directory . basename($image);

        if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
            if ($current_image && file_exists($target_directory . $current_image)) {
                unlink($target_directory . $current_image);
            }
        } else {
            echo "Error uploading image.";
            exit();
        }
    } else {
        $image = $current_image;
    }

    $update_sql = "UPDATE categories SET category_name='$category_name', image='$image' WHERE id=$id";

    if (mysqli_query($conn, $update_sql)) {
        echo "<script>alert('Category updated successfully!'); window.location='category.php';</script>";
    } else {
        echo "Error updating record: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Category</title>
    <style>
        :root {
            --mint: #A8D5BA;
            --moss: #6D8B74;
            --white: #FFFFFF;
            --gray: #F5F5F5;
            --dark-gray: #3A3A3A;
            --hover-color: #B4E4CA;
        }
        body {
            font-family: Arial, sans-serif;
            background-color: var(--gray);
            color: var(--dark-gray);
            text-align: center;
            padding: 50px;
        }
        .form-container {
            background-color: var(--white);
            max-width: 400px;
            margin: auto;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        h2 {
            color: var(--moss);
        }
        form {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
        }
        label {
            font-weight: bold;
            margin: 10px 0 5px;
        }
        input {
            width: calc(100% - 20px);
            padding: 10px;
            border: 1px solid var(--mint);
            border-radius: 5px;
            font-size: 1rem;
        }
        img {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 5px;
            margin: 10px 0;
        }
        button {
            background-color: var(--mint);
            color: var(--dark-gray);
            border: none;
            padding: 10px 20px;
            font-size: 1rem;
            cursor: pointer;
            border-radius: 5px;
            margin-top: 15px;
            transition: background-color 0.3s;
        }
        button:hover {
            background-color: var(--hover-color);
        }
    </style>
</head>
<body>
    <div class="form-container">
        <h2>Edit Category</h2>
        <form action="" method="POST" enctype="multipart/form-data">
            <label for="category_name">Name:</label>
            <input type="text" id="category_name" name="category_name" value="<?= $category_name ?>" required>



            <label>Current Image:</label><br>
            <img src="./uploads/<?= $current_image ?>" alt="Food Image">

            <label for="image">New Image (optional):</label>
            <input type="file" id="image" name="image" accept="image/*">

            <button type="submit">Update category Item</button>
        </form>
    </div>
</body>
</html>