<?php
require_once('../database/db_connection.php');

if (isset($_POST['payment_id']) && isset($_POST['status'])) {
    $paymentId = $_POST['payment_id'];
    $status = $_POST['status'];

    // Validate status as 0 or 1 only
    if ($status !== "0" && $status !== "1") {
        echo json_encode([
            "status" => "error",
            "message" => "Invalid status value"
        ]);
        exit;
    }

    $updateSql = "UPDATE payments SET status = $status WHERE id = $paymentId";
    if (mysqli_query($conn, $updateSql)) {
        echo json_encode([
            "status" => "success",
            "message" => "Payment status updated successfully"
        ]);
    } else {
        echo json_encode([
            "status" => "error",
            "message" => "Failed to update payment status"
        ]);
    }
} else {
    echo json_encode([
        "status" => "error",
        "message" => "Missing required parameters"
    ]);
}
?>
