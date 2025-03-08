<?php
require_once('../database/db_connection.php');
require_once('../global.php');
if (isset($_POST['order_id']) && isset($_POST['staff_id']) && isset($_POST['payment_amount'])) {

    $orderId = $_POST['order_id'];
    $staffId = $_POST['staff_id'];
    $paymentAmount = $_POST['payment_amount'];
    $date = date("Y-m-d H:i:s");

    // Fetch the total amount from the order
    $getOrderSql = "SELECT total_amount, order_status FROM orders WHERE id = '$orderId' AND order_status = 'pending'";
    $orderResult = mysqli_query($conn, $getOrderSql);

    if (mysqli_num_rows($orderResult) > 0) {
        $orderData = mysqli_fetch_assoc($orderResult);

        // Check if payment amount matches the order total
        if ($paymentAmount == $orderData['total_amount']) {

            // Mark the order as confirmed
            $updateOrderStatusSql = "UPDATE orders SET order_status = 'confirmed' WHERE id = '$orderId'";
            mysqli_query($conn, $updateOrderStatusSql);

            // Fetch all the order details for the current order
            $getOrderDetailsSql = "SELECT id FROM order_details WHERE order_id = '$orderId'";
            $orderDetailsResult = mysqli_query($conn, $getOrderDetailsSql);

            // Insert payment for each order detail
            while ($orderDetail = mysqli_fetch_assoc($orderDetailsResult)) {
                $orderDetailId = $orderDetail['id'];

                $insertPaymentSql = "INSERT INTO payments (order_detail_id, payment_date, amount, method) 
                                     VALUES ('$orderDetailId', '$date', '$paymentAmount', 'cash')";
                mysqli_query($conn, $insertPaymentSql);
            }

            // Send notification to user
            $insertNotificationSql = "INSERT INTO notifications (message, user_id, branch_id) 
                                    VALUES ('Your order has been confirmed and payment received!', '{$userData['user_id']}', '{$userData['branch_id']}')";
            mysqli_query($conn, $insertNotificationSql);

            echo json_encode([
                "status" => "success",
                "message" => "Payment received, order confirmed!",
                "order_status" => "confirmed"
            ]);
        } else {
            echo json_encode([
                "status" => "error",
                "message" => "Payment amount does not match the order total"
            ]);
        }
    } else {
        echo json_encode([
            "status" => "error",
            "message" => "Invalid or pending order"
        ]);
    }
} else {
    echo json_encode([
        "status" => "error",
        "message" => "Please fill in the form"
    ]);
}
?>
