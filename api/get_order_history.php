<?php
require_once('../database/db_connection.php');
require_once('../global.php');

// Get the Authorization header
$headers = apache_request_headers();
$authorizationHeader = isset($headers['Authorization']) ? $headers['Authorization'] : '';

if ($authorizationHeader) {
    // Extract Bearer token from the Authorization header
    if (preg_match('/Bearer\s(\S+)/', $authorizationHeader, $matches)) {
        $token = $matches[1];

        // Verify the token
        $sql = "SELECT u.id AS user_id, u.role, u.branch_id FROM tokens t
                INNER JOIN users u ON t.user_id = u.id
                WHERE t.token = '$token' AND t.updated_at > NOW() - INTERVAL 1 DAY";
        $result = mysqli_query($conn, $sql);
        $userData = mysqli_fetch_assoc($result);

        if ($userData) {
            $user_id = $userData['user_id'];
            $role = $userData['role'];
            $branch_id = $userData['branch_id'];

            // Fetch orders based on role
            if ($role === 'user') {
                $sql = "SELECT * FROM orders WHERE customer_id = '$user_id' ORDER BY order_date DESC";
            } elseif ($role === 'staff') {
                $sql = "SELECT * FROM orders WHERE branch_id = '$branch_id' ORDER BY order_date DESC";
            } else {
                echo json_encode(["status" => "error", "message" => "Invalid role"]);
                exit();
            }

            $result = mysqli_query($conn, $sql);
            $orders = mysqli_fetch_all($result, MYSQLI_ASSOC);

            // Process orders data
            $orderList = [];
            foreach ($orders as $order) {
                $orderList[] = [
                    "order_id" => $order['id'],
                    "order_date" => $order['order_date'],
                    "total_price" => $order['total_price'],
                    "status" => $order['status']
                ];
            }

            if (!empty($orderList)) {
                echo json_encode([
                    "status" => "success",
                    "message" => "Order history retrieved successfully",
                    "data" => $orderList
                ]);
            } else {
                echo json_encode(["status" => "error", "message" => "No orders found"]);
            }
        } else {
            echo json_encode(["status" => "error", "message" => "Invalid or expired token"]);
        }
    } else {
        echo json_encode(["status" => "error", "message" => "Bearer token missing or invalid"]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Authorization header missing"]);
}
?>
