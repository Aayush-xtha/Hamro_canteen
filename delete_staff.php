<?php
require_once('./database/db_connection.php');
session_start();

// Check if user is logged in
if (!isset($_SESSION['id'])) {
    header("Location: index.php");
    exit();
}

$branch_id = $_SESSION['id'];

// Validate the incoming ID
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $staffId = intval($_GET['id']); // Always sanitize input!

    // Make sure the staff belongs to the current branch
    $checkSql = "SELECT * FROM users WHERE id = '$staffId' AND branch_id = '$branch_id' AND role = 'Staff'";
    $checkResult = mysqli_query($conn, $checkSql);

    if ($checkResult && mysqli_num_rows($checkResult) > 0) {
        // Staff found, proceed to delete
        $deleteSql = "DELETE FROM users WHERE id = '$staffId'";
        if (mysqli_query($conn, $deleteSql)) {
            header("Location: staff.php?message=Staff+deleted+successfully");
            exit();
        } else {
            header("Location: staff.php?error=Failed+to+delete+staff");
            exit();
        }
    } else {
        header("Location: staff.php?error=Invalid+staff+member");
        exit();
    }
} else {
    // Invalid request
    header("Location: staff.php?error=Invalid+request");
    exit();
}
?>
