<?php
require_once('./database/db_connection.php');
session_start();

$branch_id = $_SESSION['id'];
$name = $_POST['branch_name'];
$email = $_POST['email'];
$phone = $_POST['phone_number'];
$address = $_POST['address'];

$logo = $_FILES['logo']['name'];
$target = "./uploads/" . basename($logo);

if ($logo) {
    move_uploaded_file($_FILES['logo']['tmp_name'], $target);
    $sql = "UPDATE branches SET branch_name='$name', email='$email', phone_number='$phone', address='$address', logo='$logo' WHERE id='$branch_id'";
} else {
    $sql = "UPDATE branches SET branch_name='$name', email='$email', phone_number='$phone', address='$address' WHERE id='$branch_id'";
}

mysqli_query($conn, $sql);
echo "Profile updated successfully!";