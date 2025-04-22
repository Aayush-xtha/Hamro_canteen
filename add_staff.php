<?php
require_once('./database/db_connection.php');
require_once('global.php');

session_start();

if (!isset($_SESSION['id'])) {
    header("Location: index.php");
    exit();
}

$branch_id = $_SESSION['id']; // Get the branch ID from session

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $first_name = mysqli_real_escape_string($conn, $_POST['first_name']);
    $last_name = mysqli_real_escape_string($conn, $_POST['last_name']);
    $user_name = mysqli_real_escape_string($conn, $_POST['user_name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    // $address = mysqli_real_escape_string($conn, $_POST['address']);

    $phone_number = mysqli_real_escape_string($conn, $_POST['phone_number']);
    $gender = mysqli_real_escape_string($conn, $_POST['gender']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    // $address = mysqli_real_escape_string($conn, $_POST['address']);
    $role = "Staff"; 
    
    $image = $_FILES['image']['name'];
    $image_tmp = $_FILES['image']['tmp_name'];
    $image_path = "uploads/" . basename($image);
    
    if (move_uploaded_file($image_tmp, $image_path)) {
        $query = "INSERT INTO users (first_name, last_name, user_name, email, phone_number, gender, password, image, role, branch_id)
                  VALUES ('$first_name', '$last_name', '$user_name', '$email', '$phone_number', '$gender', '$password', '$image', '$role', '$branch_id')";
        
        if (mysqli_query($conn, $query)) {
            echo "Staff added successfully!";
            header("Location: staff.php");
        } else {
            echo "Error: " . mysqli_error($conn);
        }
    } else {
        echo "Error uploading image.";
    }
}
?>