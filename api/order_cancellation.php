<?php
require_once('../database/db_connection.php');

if (isset($_POST['order_id']) && isset($_POST['user_id'])) {
    $orderId = intval($_POST['order_id']);
    $userId = intval($_POST['user_id']);

    // Fetch order details
    $sql = "SELECT order_date, order_status, id 
            FROM orders 
            WHERE id = '$orderId' AND user_id = '$userId' AND order_status = 'pending'";
    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) == 0) {
        echo json_encode(["status" => "error", "message" => "Order not found or cannot be canceled"]);
        exit();
    }

    $order = mysqli_fetch_assoc($result);
    $orderTime = new DateTime($order['order_date']); // Format is 'Y-m-d H:i:s' – matches database
    $now = new DateTime();
    $interval = $now->getTimestamp() - $orderTime->getTimestamp();

    // Check if within 15 minutes
    if ($interval > 900) {
        echo json_encode(["status" => "error", "message" => "Cancellation time exceeded. You can cancel only within 15 minutes."]);
        exit();
    }

    // Update order status to canceled
    $updateSql = "UPDATE orders SET order_status = 'canceled' WHERE id = '$orderId'";
    if (mysqli_query($conn, $updateSql)) {
        echo json_encode(["status" => "success", "message" => "Order canceled successfully."]);
    } else {
        echo json_encode(["status" => "error", "message" => "Failed to cancel order: " . mysqli_error($conn)]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Order ID and User ID required"]);
}
?>
