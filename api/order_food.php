<?php
require_once('../database/db_connection.php');
require_once('../global.php');

if (isset($_POST['user_id']) && isset($_POST['food_items']) && isset($_POST['branch_id']) && isset($_POST['method'])) {
    $userId = $_POST['user_id'];
    $foodItems = json_decode($_POST['food_items'], true);
    $branchId = $_POST['branch_id'];
    $paymentMethod = $_POST['method'];
    $date = date("Y-m-d H:i:s");

    // Check if the user exists
    $checkUserSql = "SELECT id FROM users WHERE id = '$userId'";
    $checkUserResult = mysqli_query($conn, $checkUserSql);
    if (mysqli_num_rows($checkUserResult) == 0) {
        echo json_encode(["status" => "error", "message" => "User not found"]);
        exit();
    }

    // Validate branch existence
    $checkBranchSql = "SELECT id FROM branches WHERE id = '$branchId'";
    $checkBranchResult = mysqli_query($conn, $checkBranchSql);
    if (mysqli_num_rows($checkBranchResult) == 0) {
        echo json_encode(["status" => "error", "message" => "Invalid branch ID"]);
        exit();
    }

    // Step 1: Calculate total amount
    $totalAmount = 0;
    foreach ($foodItems as $item) {
        $totalAmount += $item['rate'] * $item['quantity'];
    }

    // Step 2: Insert order into the orders table
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

            // Validate that food exists
            $checkFoodSql = "SELECT id FROM foods WHERE id = '$foodId'";
            $checkFoodResult = mysqli_query($conn, $checkFoodSql);
            if (mysqli_num_rows($checkFoodResult) == 0) {
                echo json_encode(["status" => "error", "message" => "Food item with ID $foodId not found"]);
                exit();
            }

            // Insert order details
            $insertOrderDetailSql = "INSERT INTO order_details (order_id, food_id, quantity, rate, sum_total, branch_id)
                                     VALUES ('$orderId', '$foodId', '$quantity', '$rate', '$sumTotal', '$branchId')";
            if (!mysqli_query($conn, $insertOrderDetailSql)) {
                echo json_encode(["status" => "error", "message" => "Failed to insert order details: " . mysqli_error($conn)]);
                exit();
            }

            $orderDetailId = mysqli_insert_id($conn);

            // Fetch food details
            $foodSql = "SELECT food_name, price, image, description FROM foods WHERE id = '$foodId'";
            $foodResult = mysqli_query($conn, $foodSql);
            $foodData = mysqli_fetch_assoc($foodResult);

            // Append order details
            $orderDetails[] = [
                "food_name" => $foodData['food_name'],
                "price" => $foodData['price'],
                "image" => $image_base . $foodData['image'],
                "description" => $foodData['description'],
                "quantity" => $quantity,
                "rate" => $rate,
                "sum_total" => $sumTotal
            ];

            // Step 4: Insert payment if method is not cash
            if ($paymentMethod != 'cash') {
                $insertPaymentSql = "INSERT INTO payments (order_detail_id, payment_date, amount, method)
                                     VALUES ('$orderDetailId', '$date', '$sumTotal', '$paymentMethod')";
                mysqli_query($conn, $insertPaymentSql);
            }
        }

        // Step 5: Update order status if payment is completed
        if ($paymentMethod != 'cash') {
            $updateOrderStatusSql = "UPDATE orders SET order_status = 'confirmed' WHERE id = '$orderId'";
            mysqli_query($conn, $updateOrderStatusSql);

            // Send notification
            $insertNotificationSql = "INSERT INTO notifications (message, user_id, branch_id)
                                      VALUES ('Your order has been confirmed!', '$userId', '$branchId')";
            mysqli_query($conn, $insertNotificationSql);
        }

        // Step 6: Remove food items from cart and cart_items
        foreach ($foodItems as $item) {
            $foodId = intval($item['food_id']);
            // Remove from cart_items
            $deleteCartItemSql = "DELETE FROM cart_items WHERE food_id = '$foodId' AND cart_id IN (SELECT id FROM cart WHERE user_id = '$userId' AND branch_id = '$branchId')";
            mysqli_query($conn, $deleteCartItemSql);
        }

        // Remove cart if empty after removing items
        $checkCartItemsSql = "SELECT COUNT(*) FROM cart_items WHERE cart_id IN (SELECT id FROM cart WHERE user_id = '$userId' AND branch_id = '$branchId')";
        $cartItemsCountResult = mysqli_query($conn, $checkCartItemsSql);
        $cartItemsCount = mysqli_fetch_row($cartItemsCountResult)[0];
        if ($cartItemsCount == 0) {
            $deleteCartSql = "DELETE FROM cart WHERE user_id = '$userId' AND branch_id = '$branchId'";
            mysqli_query($conn, $deleteCartSql);
        }

        // Return response with order details
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
