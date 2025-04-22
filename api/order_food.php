<?php
require_once('../database/db_connection.php');
require_once('../global.php');

if (isset($_POST['user_id']) && isset($_POST['food_items']) && isset($_POST['branch_id']) && isset($_POST['method'])) {
    $userId = $_POST['user_id'];
    $foodItems = json_decode($_POST['food_items'], true);
    $branchId = $_POST['branch_id'];
    $paymentMethod = $_POST['method'];
    $date = date("Y-m-d H:i:s");

    $checkUserSql = "SELECT id FROM users WHERE id = '$userId'";
    $checkUserResult = mysqli_query($conn, $checkUserSql);
    if (mysqli_num_rows($checkUserResult) == 0) {
        echo json_encode(["status" => "error", "message" => "User not found"]);
        exit();
    }

    $checkBranchSql = "SELECT id FROM branches WHERE id = '$branchId'";
    $checkBranchResult = mysqli_query($conn, $checkBranchSql);
    if (mysqli_num_rows($checkBranchResult) == 0) {
        echo json_encode(["status" => "error", "message" => "Invalid branch ID"]);
        exit();
    }

    $totalAmount = 0;
    foreach ($foodItems as $item) {
        $totalAmount += floatval($item['rate']) * intval($item['quantity']);

    }

    $orderStatus = ($paymentMethod == 'cash') ? 'pending' : 'confirmed';
    $insertOrderSql = "INSERT INTO orders (user_id, total_amount, order_status) VALUES ('$userId', '$totalAmount', '$orderStatus')";
    $orderResult = mysqli_query($conn, $insertOrderSql);

    if ($orderResult) {
        $orderId = mysqli_insert_id($conn);
        $orderDetails = [];

        foreach ($foodItems as $item) {
            $foodId = intval($item['food_id']);
            $quantity = intval($item['quantity']);
            $rate = floatval($item['rate']);
            $sumTotal = $rate * $quantity;

            $checkFoodSql = "SELECT id FROM foods WHERE id = '$foodId'";
            $checkFoodResult = mysqli_query($conn, $checkFoodSql);
            if (mysqli_num_rows($checkFoodResult) == 0) {
                echo json_encode(["status" => "error", "message" => "Food item with ID $foodId not found"]);
                exit();
            }

            $insertOrderDetailSql = "INSERT INTO order_details (order_id, food_id, quantity, rate, sum_total, branch_id)
                                     VALUES ('$orderId', '$foodId', '$quantity', '$rate', '$sumTotal', '$branchId')";
            if (!mysqli_query($conn, $insertOrderDetailSql)) {
                echo json_encode(["status" => "error", "message" => "Failed to insert order details: " . mysqli_error($conn)]);
                exit();
            }

            $orderDetailId = mysqli_insert_id($conn);

            $foodSql = "SELECT food_name, price, image, description FROM foods WHERE id = '$foodId'";
            $foodResult = mysqli_query($conn, $foodSql);
            $foodData = mysqli_fetch_assoc($foodResult);

            $orderDetails[] = [
                "food_name" => $foodData['food_name'],
                "price" => $foodData['price'],
                "image" => $image_base . $foodData['image'],
                "description" => $foodData['description'],
                "quantity" => $quantity,
                "rate" => $rate,
                "sum_total" => $sumTotal
            ];

            if ($paymentMethod != 'cash') {
                $insertPaymentSql = "INSERT INTO payments (order_detail_id, payment_date, amount, method, status)
                                     VALUES ('$orderDetailId', '$date', '$sumTotal', '$paymentMethod', 1)";
                mysqli_query($conn, $insertPaymentSql);
            }
        }

        if ($paymentMethod != 'cash') {
            $updateOrderStatusSql = "UPDATE orders SET order_status = 'confirmed' WHERE id = '$orderId'";
            mysqli_query($conn, $updateOrderStatusSql);

            // Send notification
            $insertNotificationSql = "INSERT INTO notifications (message, user_id, branch_id)
                                      VALUES ('Your order has been confirmed!', '$userId', '$branchId')";
            mysqli_query($conn, $insertNotificationSql);
        }

        foreach ($foodItems as $item) {
            $foodId = intval($item['food_id']);
            $deleteCartItemSql = "DELETE FROM cart_items WHERE food_id = '$foodId' AND cart_id IN (SELECT id FROM cart WHERE user_id = '$userId' AND branch_id = '$branchId')";
            mysqli_query($conn, $deleteCartItemSql);
        }

        $checkCartItemsSql = "SELECT COUNT(*) FROM cart_items WHERE cart_id IN (SELECT id FROM cart WHERE user_id = '$userId' AND branch_id = '$branchId')";
        $cartItemsCountResult = mysqli_query($conn, $checkCartItemsSql);
        $cartItemsCount = mysqli_fetch_row($cartItemsCountResult)[0];
        if ($cartItemsCount == 0) {
            $deleteCartSql = "DELETE FROM cart WHERE user_id = '$userId' AND branch_id = '$branchId'";
            mysqli_query($conn, $deleteCartSql);
        }

        echo json_encode([
            "status" => "success",
            "message" => "Order placed successfully",
            "order_details" => array_map(function($orderDetail) use ($orderId, $totalAmount, $orderStatus) {
                $orderDetail["order_id"] = $orderId;
                $orderDetail["total_amount"] = $totalAmount;
                $orderDetail["order_status"] = $orderStatus;
                return $orderDetail;
            }, $orderDetails)
        ]);
    } else {
        echo json_encode(["status" => "error", "message" => "Failed to place order: " . mysqli_error($conn)]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Fill in all required fields"]);
}
?>
