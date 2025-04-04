<?php
require_once('./database/db_connection.php');
session_start();

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['user_id'], $_POST['order_id'])) {
    $user_id = $_POST['user_id'];
    $order_id = $_POST['order_id'];
    $branch_id = $_SESSION['id'];

    // Check if notification already exists for this order
    $checkSql = "SELECT * FROM notifications WHERE user_id = '$user_id' AND branch_id = '$branch_id' AND message LIKE '%Order #$order_id%'";
    $checkResult = mysqli_query($conn, $checkSql);

    if (mysqli_num_rows($checkResult) === 0) {
        $message = "Your Order #$order_id is ready for pickup!";
        $insertSql = "INSERT INTO notifications (message, user_id, branch_id) VALUES ('$message', '$user_id', '$branch_id')";
        mysqli_query($conn, $insertSql);
    }
}

header("Location: dashboard.php");
exit();
