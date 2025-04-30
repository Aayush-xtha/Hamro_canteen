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
    $phone_number = mysqli_real_escape_string($conn, $_POST['phone_number']);
    $gender = mysqli_real_escape_string($conn, $_POST['gender']);
    $password = $_POST['password']; // We don't escape here because we will hash it
    $role = "staff"; 
    
    $image = $_FILES['image']['name'];
    $image_tmp = $_FILES['image']['tmp_name'];
    $image_path = "uploads/" . basename($image);
    
    // Hash the password securely
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    if (move_uploaded_file($image_tmp, $image_path)) {
        $query = "INSERT INTO users (first_name, last_name, user_name, email, phone_number, gender, password, image, role, branch_id)
                  VALUES ('$first_name', '$last_name', '$user_name', '$email', '$phone_number', '$gender', '$hashedPassword', '$image', '$role', '$branch_id')";
        
        if (mysqli_query($conn, $query)) {
            header("Location: staff.php?message=Staff+added+successfully");
            exit();
        } else {
            echo "Error: " . mysqli_error($conn);
        }
    } else {
        echo "Error uploading image.";
    }
}
?>
